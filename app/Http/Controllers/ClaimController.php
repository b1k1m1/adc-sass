<?php

namespace App\Http\Controllers;

use App\Helpers\IcdHelper;
use App\Models\ClaimBatch;
use App\Models\ClaimBatchItem;
use App\Services\ClaimDateSplitter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\Tag\Q;
use Illuminate\Support\Facades\Http;
use App\Exports\MemberStatementExport;

class ClaimController extends Controller
{
    public function ClaimsDashboard()
    {
        return view('claims.claims_index', ['title' => 'Claims Management', 'module' => 'claims']);
    } // End Method

    public function create(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Shift
        |--------------------------------------------------------------------------
        |
        | A = Morning
        | N = Noon
        | P = Afternoon
        |--------------------------------------------------------------------------
        */

        $type = strtoupper(
            trim(
                (string) $request->query(
                    'type',
                    session('claim_session_type', 'A')
                )
            )
        );

        if (!in_array($type, ['A', 'N', 'P', 'D'], true)) {
            $type = 'A';
        }

        session([
            'claim_session_type' => $type
        ]);


        /*
        |--------------------------------------------------------------------------
        | Selected Member
        |--------------------------------------------------------------------------
        */

        $participantId = trim(
            (string) $request->query(
                'participantId',
                ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Billing Members
        |--------------------------------------------------------------------------
        */

        $membersQuery = DB::table('billing_members')
            ->select(
                DB::raw('TRIM(ParticipantId) as ParticipantId'),
                'LastName',
                'FirstName',
                'PrimaryDiagnosis1',
                'SecondaryDiag1',
                'source_shift',
                'source_system'
            )
            ->where('source_shift', $type);

        /*
        |--------------------------------------------------------------------------
        | Active / Discharged
        |--------------------------------------------------------------------------
        |
        | Morning / Noon / Afternoon:
        |     Show active members only.
        |
        | Discharged:
        |     Do not require active = 1 because discharged members may have
        |     active = 0 but we still need access to historical claims/payments.
        |
        */

        if ($type !== 'D') {
            $membersQuery->where('active', 1);
        }

        $members = $membersQuery
            ->orderBy('ParticipantId')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Claim History / Payments
        |--------------------------------------------------------------------------
        */

        $claimsHistory = collect();

        $payments = collect();


        if ($participantId !== '') {

            $claimsHistory = DB::table('claims')
                ->select(
                    'id',
                    DB::raw('TRIM(ParticipantId) as ParticipantId'),
                    'claim_no',
                    'original_claim_no',
                    'service_from',
                    'service_to',
                    'status',
                    'precheck_result',
                    'total_amount'
                )
                ->whereRaw(
                    'TRIM(ParticipantId) = ?',
                    [$participantId]
                )
                ->orderByDesc('service_from')
                ->limit(25)
                ->get();


            /*
            |--------------------------------------------------------------------------
            | Claim Numbers For ERA Payment History
            |--------------------------------------------------------------------------
            */

            $claimNos = DB::table('claims')
                ->whereRaw(
                    'TRIM(ParticipantId) = ?',
                    [$participantId]
                )
                ->whereNotNull('claim_no')
                ->pluck('claim_no')
                ->filter()
                ->unique()
                ->values();


            if ($claimNos->count()) {

                $eraClaims = DB::table('era_claims')
                    ->whereIn(
                        'patient_control_number',
                        $claimNos
                    )
                    ->orderByDesc('id')
                    ->get([
                        'id',
                        'era_file_id',
                        'patient_control_number',
                        'total_charge',
                        'total_paid',
                        'claim_status_code',
                    ]);


                if ($eraClaims->count()) {

                    $fileIds = $eraClaims
                        ->pluck('era_file_id')
                        ->filter()
                        ->unique()
                        ->values();


                    $eraFiles = DB::table('era_files')
                        ->whereIn('id', $fileIds)
                        ->get([
                            'id',
                            'payment_date',
                            'payer_name',
                            'trace_number'
                        ])
                        ->keyBy('id');


                    foreach ($eraClaims as $ec) {

                        $file =
                            $eraFiles->get(
                                $ec->era_file_id
                            );


                        $payments->push([

                            'claim_no' =>
                            $ec->patient_control_number,

                            'total_charge' =>
                            $ec->total_charge,

                            'total_paid' =>
                            $ec->total_paid,

                            'claim_status' =>
                            $ec->claim_status_code,

                            'payment_date' =>
                            $file->payment_date
                                ?? null,

                            'payer_name' =>
                            $file->payer_name
                                ?? null,

                            'trace_number' =>
                            $file->trace_number
                                ?? null,

                        ]);
                    }
                }
            }
        }


        return view(
            'claims.claims_create_new',
            [
                'members' =>
                $members,

                'participantId' =>
                $participantId,

                'claimsHistory' =>
                $claimsHistory,

                'payments' =>
                $payments,

                'type' =>
                $type,
            ]
        );
    } // End Method

    public function memberSummary($participantId)
    {
        /*
        |--------------------------------------------------------------------------
        | Normalize Participant ID
        |--------------------------------------------------------------------------
        */

        $participantId =
            trim(
                (string) $participantId
            );


        /*
        |--------------------------------------------------------------------------
        | Financial Summary
        |--------------------------------------------------------------------------
        |
        | Total Billed = SUM(total_amount)
        |
        | Total Paid   = SUM(ins1_paid)
        |
        | Balance      = SUM(balance_amount)
        |
        | We intentionally use balance_amount from the claims table instead of
        | calculating Billed - Paid because ERA adjustments/write-offs can cause
        | those two figures to be different.
        |--------------------------------------------------------------------------
        */

        $summary = DB::table('claims')
            ->whereRaw(
                'TRIM(ParticipantId) = ?',
                [$participantId]
            )
            ->selectRaw('
                COALESCE(SUM(total_amount), 0) AS billed,
                COALESCE(SUM(ins1_paid), 0) AS paid,
                COALESCE(SUM(balance_amount), 0) AS balance
            ')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Return JSON
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'billed' =>
            number_format(
                (float) (
                    $summary->billed
                    ?? 0
                ),
                2,
                '.',
                ''
            ),

            'paid' =>
            number_format(
                (float) (
                    $summary->paid
                    ?? 0
                ),
                2,
                '.',
                ''
            ),

            'balance' =>
            number_format(
                (float) (
                    $summary->balance
                    ?? 0
                ),
                2,
                '.',
                ''
            ),

        ]);
    } // End Method

    public function getClaimServiceRate(Request $request)
    {
        $request->validate([
            'service_date' => [
                'required',
                'date',
            ],
        ]);

        $serviceDate = $request->service_date;

        /*
        |--------------------------------------------------------------------------
        | Find Active S5102 / U1 Rate For Service Date
        |--------------------------------------------------------------------------
        */

        $rateRecord = DB::table('claim_service_rates')
            ->where('proc_code', 'S5102')
            ->where('modifier', 'U1')
            ->where('active', 1)
            ->whereDate(
                'effective_from',
                '<=',
                $serviceDate
            )
            ->whereDate(
                'effective_to',
                '>=',
                $serviceDate
            )
            ->orderByDesc('effective_from')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | No Rate Found
        |--------------------------------------------------------------------------
        */

        if (!$rateRecord) {

            return response()->json([
                'success' => false,
                'rate'    => null,
                'message' =>
                'No active claim service rate was found for '
                    . $serviceDate
                    . '.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Return Rate
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'rate' => number_format(
                (float) $rateRecord->amount_per_unit,
                2,
                '.',
                ''
            ),

            'effective_from' =>
            $rateRecord->effective_from,

            'effective_to' =>
            $rateRecord->effective_to,
        ]);
    } // End Method

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Validation
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'ParticipantId'            => 'required|string',
            'lines'                    => 'required|array|min:1',
            'lines.*.id'               => 'nullable|integer',

            'lines.*.service_from'     => 'nullable|date|before_or_equal:today',
            'lines.*.service_to'       => 'nullable|date|after_or_equal:lines.*.service_from|before_or_equal:today',

            'lines.*.amount_per_unit'  => 'nullable|numeric|min:0',
            'lines.*.units'            => 'nullable|integer|min:0|max:7',
            'lines.*.proc_code'        => 'nullable|string|max:6',
            'lines.*.m1'               => 'nullable|string|max:3',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Participant
        |--------------------------------------------------------------------------
        */

        $participantId = trim(
            (string) $request->input('ParticipantId')
        );


        /*
        |--------------------------------------------------------------------------
        | 2. Billing Member
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Load BOTH the original Medicaid PA and renewal PA.
        |
        */

        $member = DB::table('billing_members')
            ->select(
                'ParticipantId',

                'PrimaryDiagnosis1',
                'SecondaryDiag1',

                'HMO_Enrolled',
                'medicaid_HMO_id',

                'medicaid_PA_no',
                'medicaid_PA_Start',
                'medicaid_PA_End',

                'renew1_PA_no',
                'renew1_PA_Start',
                'renew1_PA_End',

                'source_system'
            )
            ->whereRaw(
                'TRIM(ParticipantId) = ?',
                [$participantId]
            )
            ->where('active', 1)
            ->first();


        if (!$member) {

            return back()
                ->withErrors([
                    'ParticipantId' =>
                    'Billing Member not found for this ParticipantId.'
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Member Information
        |--------------------------------------------------------------------------
        */

        $paStart =
            $member->medicaid_PA_Start
            ?? null;

        $paEnd =
            $member->medicaid_PA_End
            ?? null;


        $P_diag =
            $member->PrimaryDiagnosis1
            ?? null;

        $S_diag =
            $member->SecondaryDiag1
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | Insurance Information
        |--------------------------------------------------------------------------
        */

        $hmo_name =
            $member->HMO_Enrolled
            ?? null;

        $hmo_id =
            $member->medicaid_HMO_id
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | Insurance Company
        |--------------------------------------------------------------------------
        */

        $insurance = null;

        if ($hmo_name) {

            $insurance = DB::table('insurance_companies')
                ->where('code', $hmo_name)
                ->first();
        }


        $payer_fullname =
            $insurance->full_name
            ?? null;

        $payer_payerid =
            $insurance->payer_id
            ?? null;

        $payer_address1 =
            $insurance->address1
            ?? null;

        $payer_address2 =
            $insurance->address2
            ?? null;

        $payer_city =
            $insurance->city
            ?? null;

        $payer_state =
            $insurance->state
            ?? null;

        $payer_zip =
            $insurance->zip
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | Posted Lines
        |--------------------------------------------------------------------------
        */

        $lines =
            $request->input(
                'lines',
                []
            );

        $now =
            now();


        /*
        |--------------------------------------------------------------------------
        | 3. Existing Draft Rows
        |--------------------------------------------------------------------------
        |
        | Used to determine if an unsaved/deleted draft line should be removed.
        |--------------------------------------------------------------------------
        */

        $existingDraftIds = DB::table('claims')
            ->whereRaw(
                'TRIM(ParticipantId) = ?',
                [$participantId]
            )
            ->whereNull('claim_no')
            ->where('status', 'draft')
            ->pluck('id')
            ->all();


        $keepIds = [];


        /*
        |--------------------------------------------------------------------------
        | Next Line Number
        |--------------------------------------------------------------------------
        */

        $maxLineNo = DB::table('claims')
            ->whereRaw(
                'TRIM(ParticipantId) = ?',
                [$participantId]
            )
            ->max('line_no');


        $lineNo =
            ($maxLineNo ?? 0) + 1;


        /*
        |--------------------------------------------------------------------------
        | Insert / Update Counters
        |--------------------------------------------------------------------------
        */

        $newInserts = [];

        $updatedCount = 0;


        /*
        |--------------------------------------------------------------------------
        | Process Lines
        |--------------------------------------------------------------------------
        */

        foreach ($lines as $line) {

            $lineId =
                $line['id']
                ?? null;


            /*
            |--------------------------------------------------------------------------
            | Normalize Raw Values
            |--------------------------------------------------------------------------
            */

            $fromRaw =
                trim(
                    $line['service_from']
                        ?? ''
                );

            $toRaw =
                trim(
                    $line['service_to']
                        ?? ''
                );

            $unitsRaw =
                trim(
                    (string) (
                        $line['units']
                        ?? ''
                    )
                );

            $amtRaw =
                trim(
                    (string) (
                        $line['amount_per_unit']
                        ?? ''
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Blank Row
            |--------------------------------------------------------------------------
            */

            $isEmpty =
                (
                    $fromRaw === ''
                    &&
                    $toRaw === ''
                    &&
                    $unitsRaw === ''
                    &&
                    $amtRaw === ''
                );


            /*
            |--------------------------------------------------------------------------
            | 3A. EXISTING CLAIM ROW
            |--------------------------------------------------------------------------
            */

            if ($lineId) {

                if ($isEmpty) {

                    /*
                    |--------------------------------------------------------------
                    | Cleared draft row.
                    | Do not add it to $keepIds.
                    |--------------------------------------------------------------
                    */

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Dates
                |--------------------------------------------------------------------------
                */

                try {

                    $serviceFrom =
                        Carbon::parse(
                            $fromRaw
                        )->startOfDay();

                    $serviceTo =
                        Carbon::parse(
                            $toRaw
                        )->startOfDay();
                } catch (\Exception $e) {

                    continue;
                }


                if (
                    $serviceFrom->gt(
                        $serviceTo
                    )
                ) {

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Resolve Prior Authorization
                |--------------------------------------------------------------------------
                |
                | Do NOT automatically use the original PA.
                |
                | resolvePriorAuthorization() checks:
                |
                |   1. Original Medicaid PA
                |   2. Renewal PA
                |
                | and returns the PA that completely covers these service dates.
                |
                */

                $claimForPa =
                    (object) [

                        'ParticipantId' =>
                        $participantId,

                        'service_from' =>
                        $serviceFrom->toDateString(),

                        'service_to' =>
                        $serviceTo->toDateString(),

                        'is_special_claim' =>
                        0,

                        'override_pa_validation' =>
                        0,
                    ];


                $resolvedPa =
                    $this->resolvePriorAuthorization(
                        $claimForPa,
                        $member
                    );


                /*
                |--------------------------------------------------------------------------
                | PA To Store
                |--------------------------------------------------------------------------
                |
                | If one PA completely covers the service period, store it.
                |
                | Otherwise leave PA null. checkClaims() will later provide the
                | proper validation message, including PA crossover situations.
                |
                */

                $claimPaNo = null;
                $claimPaEnd = null;


                if (
                    ($resolvedPa['valid'] ?? false) === true
                ) {

                    $claimPaNo =
                        $resolvedPa['pa_no']
                        ?? null;

                    $claimPaEnd =
                        $resolvedPa['pa_end']
                        ?? null;
                }


                /*
                |--------------------------------------------------------------------------
                | Units
                |--------------------------------------------------------------------------
                */

                $units =
                    (int) (
                        $line['units']
                        ?? 0
                    );


                if ($units < 0) {
                    $units = 0;
                }

                if ($units > 7) {
                    $units = 7;
                }


                /*
                |--------------------------------------------------------------------------
                | Amount Per Unit
                |--------------------------------------------------------------------------
                */

                $amountPerUnit =
                    round(
                        (float) (
                            $line['amount_per_unit']
                            ?? 0
                        ),
                        2
                    );


                if ($amountPerUnit < 0) {
                    $amountPerUnit = 0;
                }


                /*
                |--------------------------------------------------------------------------
                | Total Amount
                |--------------------------------------------------------------------------
                */

                $totalAmount =
                    round(
                        $units
                            *
                            $amountPerUnit,
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Allowed
                |--------------------------------------------------------------------------
                */

                $allowed =
                    isset($line['allowed'])
                    &&
                    $line['allowed'] !== ''

                    ? round(
                        (float) $line['allowed'],
                        2
                    )

                    : $totalAmount;


                /*
                |--------------------------------------------------------------------------
                | Provider / POS / Payment
                |--------------------------------------------------------------------------
                */

                $provider =
                    $line['provider']
                    ?? 'DM';

                $pos =
                    $line['pos']
                    ?? '99';

                $ins1Paid =
                    isset($line['ins1_paid'])

                    ? round(
                        (float) $line['ins1_paid'],
                        2
                    )

                    : 0.00;


                /*
                |--------------------------------------------------------------------------
                | Existing Financial Values
                |--------------------------------------------------------------------------
                |
                | Important:
                | Do NOT erase existing ERA adjustments, deductions or write-offs
                | merely because the claim was opened in manual claim entry.
                |--------------------------------------------------------------------------
                */

                $currentClaim = DB::table('claims')
                    ->where('id', $lineId)
                    ->first([
                        'adjustment_amount',
                        'patient_responsibility',
                        'deduction_amount',
                        'writeoff_amount',
                    ]);


                $adjustmentAmount =
                    round(
                        (float) (
                            $currentClaim->adjustment_amount
                            ?? 0
                        ),
                        2
                    );

                $patientResponsibility =
                    round(
                        (float) (
                            $currentClaim->patient_responsibility
                            ?? 0
                        ),
                        2
                    );

                $deductionAmount =
                    round(
                        (float) (
                            $currentClaim->deduction_amount
                            ?? 0
                        ),
                        2
                    );

                $writeoffAmount =
                    round(
                        (float) (
                            $currentClaim->writeoff_amount
                            ?? 0
                        ),
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | BALANCE AMOUNT
                |--------------------------------------------------------------------------
                |
                | Patient responsibility is NOT subtracted here.
                |
                | It remains part of the outstanding receivable unless separately
                | collected or written off.
                |--------------------------------------------------------------------------
                */

                $balanceAmount =
                    round(
                        $totalAmount
                            -
                            $ins1Paid
                            -
                            $adjustmentAmount
                            -
                            $deductionAmount
                            -
                            $writeoffAmount,
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Do Not Allow Negative Balance
                |--------------------------------------------------------------------------
                */

                $balanceAmount =
                    max(
                        $balanceAmount,
                        0.00
                    );


                /*
                |--------------------------------------------------------------------------
                | Update Existing Claim
                |--------------------------------------------------------------------------
                */

                DB::table('claims')
                    ->where('id', $lineId)
                    ->update([

                        'service_from' =>
                        $serviceFrom->toDateString(),

                        'service_to' =>
                        $serviceTo->toDateString(),

                        'proc_code' =>
                        $line['proc_code']
                            ?? 'S5102',

                        'm1' =>
                        $line['m1']
                            ?? 'U1',

                        'units' =>
                        $units,

                        'amount_per_unit' =>
                        $amountPerUnit,

                        'total_amount' =>
                        $totalAmount,

                        'diag1' =>
                        $P_diag,

                        'diag2' =>
                        $S_diag,

                        'provider' =>
                        $provider,

                        'pos' =>
                        $pos,

                        'allowed' =>
                        $allowed,

                        'ins1_paid' =>
                        $ins1Paid,

                        /*
                        |--------------------------------------------------------------------------
                        | Financial Balance
                        |--------------------------------------------------------------------------
                        */

                        'balance_amount' =>
                        $balanceAmount,

                        /*
                        |--------------------------------------------------------------------------
                        | Insurance
                        |--------------------------------------------------------------------------
                        */

                        'insurance_name' =>
                        $hmo_name,

                        'payer_fullname' =>
                        $payer_fullname,

                        'payer_id' =>
                        $payer_payerid,

                        'payer_address1' =>
                        $payer_address1,

                        'payer_address2' =>
                        $payer_address2,

                        'payer_city' =>
                        $payer_city,

                        'payer_state' =>
                        $payer_state,

                        'payer_zip' =>
                        $payer_zip,

                        'insurance_id' =>
                        $hmo_id,

                        /*
                        |--------------------------------------------------------------------------
                        | Prior Authorization
                        |--------------------------------------------------------------------------
                        |
                        | Correct PA based on service dates.
                        |--------------------------------------------------------------------------
                        */

                        'prior_auth_no' =>
                        $claimPaNo,

                        'prior_auth_expiry' =>
                        $claimPaEnd,

                        /*
                        |--------------------------------------------------------------------------
                        | Force Revalidation
                        |--------------------------------------------------------------------------
                        |
                        | Service dates or PA may have changed.
                        |--------------------------------------------------------------------------
                        */

                        'precheck_result' =>
                        null,

                        'precheck_message' =>
                        null,

                        'status' =>
                        'draft',

                        'updated_at' =>
                        $now,

                    ]);


                $keepIds[] =
                    (int) $lineId;

                $updatedCount++;

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | 3B. NEW CLAIM ROW
            |--------------------------------------------------------------------------
            */

            if ($isEmpty) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            try {

                $serviceFrom =
                    Carbon::parse(
                        $fromRaw
                    )->startOfDay();

                $serviceTo =
                    Carbon::parse(
                        $toRaw
                    )->startOfDay();
            } catch (\Exception $e) {

                continue;
            }


            if (
                $serviceFrom->gt(
                    $serviceTo
                )
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Units
            |--------------------------------------------------------------------------
            */

            $units =
                (int) (
                    $line['units']
                    ?? 0
                );


            if ($units < 0) {
                $units = 0;
            }

            if ($units > 7) {
                $units = 7;
            }


            /*
            |--------------------------------------------------------------------------
            | Amount Per Unit
            |--------------------------------------------------------------------------
            */

            $amountPerUnit =
                round(
                    (float) (
                        $line['amount_per_unit']
                        ?? 0
                    ),
                    2
                );


            if ($amountPerUnit < 0) {
                $amountPerUnit = 0;
            }


            /*
            |--------------------------------------------------------------------------
            | Split By Month / Original PA Boundary
            |--------------------------------------------------------------------------
            |
            | Keep the existing ClaimDateSplitter behavior.
            |
            | After each segment is created we resolve the correct PA using the
            | centralized PA resolver. Therefore a segment after the original PA
            | expires can automatically receive the renewal PA.
            |--------------------------------------------------------------------------
            */

            $segments = ClaimDateSplitter::split(

                $serviceFrom->toDateString(),

                $serviceTo->toDateString(),

                $paStart,

                $paEnd
            );


            /*
            |--------------------------------------------------------------------------
            | Distribute Units Across Split Segments
            |--------------------------------------------------------------------------
            */

            $totalUnits =
                max(
                    0,
                    $units
                );


            $totalDays =
                collect(
                    $segments
                )->sum(
                    'days'
                );


            $segmentUnitsList = [];

            $assignedUnits = 0;


            if (
                $totalUnits === 0
                ||
                $totalDays === 0
            ) {

                foreach (
                    $segments
                    as $idx => $segment
                ) {

                    $segmentUnitsList[$idx] =
                        0;
                }
            } else {

                /*
                |--------------------------------------------------------------------------
                | Proportional Floor
                |--------------------------------------------------------------------------
                */

                foreach (
                    $segments
                    as $idx => $segment
                ) {

                    $segDays =
                        $segment['days'];


                    $u =
                        intdiv(
                            $totalUnits
                                *
                                $segDays,
                            $totalDays
                        );


                    $segmentUnitsList[$idx] =
                        $u;

                    $assignedUnits +=
                        $u;
                }


                /*
                |--------------------------------------------------------------------------
                | Distribute Remaining Units
                |--------------------------------------------------------------------------
                */

                $remainder =
                    $totalUnits
                    -
                    $assignedUnits;


                $idx = 0;


                while (
                    $remainder > 0
                    &&
                    count($segments) > 0
                ) {

                    $segmentUnitsList[$idx]++;

                    $remainder--;

                    $idx++;


                    if (
                        $idx >= count($segments)
                    ) {

                        $idx = 0;
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Create Each Split Segment
            |--------------------------------------------------------------------------
            */

            foreach (
                $segments
                as $idx => $segment
            ) {

                /** @var Carbon $segFrom */
                /** @var Carbon $segTo */

                $segFrom =
                    $segment['from'];

                $segTo =
                    $segment['to'];


                $segmentUnits =
                    $segmentUnitsList[$idx]
                    ?? 0;


                /*
                |--------------------------------------------------------------------------
                | Resolve Correct PA For THIS Segment
                |--------------------------------------------------------------------------
                |
                | This is the important change.
                |
                | Example:
                |
                | Original PA:
                | 01/01/2026 - 09/30/2026
                |
                | Renewal PA:
                | 10/01/2026 - 03/31/2027
                |
                | Segment:
                | 10/05/2026 - 10/09/2026
                |
                | The resolver returns the renewal PA automatically.
                |--------------------------------------------------------------------------
                */

                $claimForPa =
                    (object) [

                        'ParticipantId' =>
                        $participantId,

                        'service_from' =>
                        $segFrom->toDateString(),

                        'service_to' =>
                        $segTo->toDateString(),

                        'is_special_claim' =>
                        0,

                        'override_pa_validation' =>
                        0,
                    ];


                $resolvedPa =
                    $this->resolvePriorAuthorization(
                        $claimForPa,
                        $member
                    );


                $claimPaNo = null;
                $claimPaEnd = null;


                if (
                    ($resolvedPa['valid'] ?? false) === true
                ) {

                    $claimPaNo =
                        $resolvedPa['pa_no']
                        ?? null;

                    $claimPaEnd =
                        $resolvedPa['pa_end']
                        ?? null;
                }


                /*
                |--------------------------------------------------------------------------
                | Segment Total
                |--------------------------------------------------------------------------
                */

                $totalAmount =
                    round(
                        $segmentUnits
                            *
                            $amountPerUnit,
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Allowed
                |--------------------------------------------------------------------------
                */

                $allowed =
                    $totalAmount;


                /*
                |--------------------------------------------------------------------------
                | Provider / POS
                |--------------------------------------------------------------------------
                */

                $provider =
                    $line['provider']
                    ?? 'DM';

                $pos =
                    $line['pos']
                    ?? '99';


                /*
                |--------------------------------------------------------------------------
                | New Manual Claim Financial Values
                |--------------------------------------------------------------------------
                |
                | A brand-new claim has no ERA adjustments yet.
                |--------------------------------------------------------------------------
                */

                $ins1Paid =
                    isset($line['ins1_paid'])

                    ? round(
                        (float) $line['ins1_paid'],
                        2
                    )

                    : 0.00;


                $adjustmentAmount =
                    0.00;

                $patientResponsibility =
                    0.00;

                $deductionAmount =
                    0.00;

                $writeoffAmount =
                    0.00;


                /*
                |--------------------------------------------------------------------------
                | BALANCE AMOUNT
                |--------------------------------------------------------------------------
                |
                | New claim:
                |
                | Balance = Total Amount - Insurance Paid
                |
                | Normally Insurance Paid will be 0.00, therefore:
                |
                | Balance = Total Amount
                |--------------------------------------------------------------------------
                */

                $balanceAmount =
                    round(
                        $totalAmount
                            -
                            $ins1Paid,
                        2
                    );


                $balanceAmount =
                    max(
                        $balanceAmount,
                        0.00
                    );


                /*
                |--------------------------------------------------------------------------
                | New Claim Insert
                |--------------------------------------------------------------------------
                */

                $newInserts[] = [

                    'claim_no' =>
                    null,

                    'line_no' =>
                    $lineNo++,

                    'ParticipantId' =>
                    $participantId,


                    /*
                    |--------------------------------------------------------------------------
                    | Insurance
                    |--------------------------------------------------------------------------
                    */

                    'insurance_name' =>
                    $hmo_name,

                    'payer_fullname' =>
                    $payer_fullname,

                    'payer_id' =>
                    $payer_payerid,

                    'payer_address1' =>
                    $payer_address1,

                    'payer_address2' =>
                    $payer_address2,

                    'payer_city' =>
                    $payer_city,

                    'payer_state' =>
                    $payer_state,

                    'payer_zip' =>
                    $payer_zip,

                    'insurance_id' =>
                    $hmo_id,


                    /*
                    |--------------------------------------------------------------------------
                    | Prior Authorization
                    |--------------------------------------------------------------------------
                    |
                    | PA resolved from this segment's actual service dates.
                    |--------------------------------------------------------------------------
                    */

                    'prior_auth_no' =>
                    $claimPaNo,

                    'prior_auth_expiry' =>
                    $claimPaEnd,


                    /*
                    |--------------------------------------------------------------------------
                    | Service
                    |--------------------------------------------------------------------------
                    */

                    'service_from' =>
                    $segFrom->toDateString(),

                    'service_to' =>
                    $segTo->toDateString(),

                    'proc_code' =>
                    $line['proc_code']
                        ?? 'S5102',

                    'm1' =>
                    $line['m1']
                        ?? 'U1',

                    'units' =>
                    $segmentUnits,

                    'amount_per_unit' =>
                    $amountPerUnit,

                    'total_amount' =>
                    $totalAmount,


                    /*
                    |--------------------------------------------------------------------------
                    | Diagnosis
                    |--------------------------------------------------------------------------
                    */

                    'diag1' =>
                    $P_diag,

                    'diag2' =>
                    $S_diag,


                    /*
                    |--------------------------------------------------------------------------
                    | Provider
                    |--------------------------------------------------------------------------
                    */

                    'provider' =>
                    $provider,

                    'pos' =>
                    $pos,


                    /*
                    |--------------------------------------------------------------------------
                    | Financial
                    |--------------------------------------------------------------------------
                    */

                    'allowed' =>
                    $allowed,

                    'ins1_paid' =>
                    $ins1Paid,

                    'adjustment_amount' =>
                    $adjustmentAmount,

                    'patient_responsibility' =>
                    $patientResponsibility,

                    'deduction_amount' =>
                    $deductionAmount,

                    'writeoff_amount' =>
                    $writeoffAmount,


                    /*
                    |--------------------------------------------------------------------------
                    | IMPORTANT - Initial Claim Balance
                    |--------------------------------------------------------------------------
                    */

                    'balance_amount' =>
                    $balanceAmount,


                    /*
                    |--------------------------------------------------------------------------
                    | Claim Status
                    |--------------------------------------------------------------------------
                    */

                    'original_claim_no' =>
                    null,

                    'status' =>
                    'draft',

                    'precheck_result' =>
                    null,

                    'precheck_message' =>
                    null,


                    /*
                    |--------------------------------------------------------------------------
                    | Source
                    |--------------------------------------------------------------------------
                    */

                    'source_system' =>
                    $member->source_system
                        ?? null,


                    /*
                    |--------------------------------------------------------------------------
                    | Timestamps
                    |--------------------------------------------------------------------------
                    */

                    'created_at' =>
                    $now,

                    'updated_at' =>
                    $now,
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 4. Delete Removed Draft Rows
        |--------------------------------------------------------------------------
        */

        $idsToDelete =
            array_diff(
                $existingDraftIds,
                $keepIds
            );


        if (!empty($idsToDelete)) {

            DB::table('claims')
                ->whereIn(
                    'id',
                    $idsToDelete
                )
                ->delete();
        }


        /*
        |--------------------------------------------------------------------------
        | 5. Insert New Rows
        |--------------------------------------------------------------------------
        */

        if (!empty($newInserts)) {

            DB::table('claims')
                ->insert(
                    $newInserts
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Nothing Saved
        |--------------------------------------------------------------------------
        */

        if (
            empty($keepIds)
            &&
            empty($newInserts)
        ) {

            return back()
                ->withErrors([
                    'lines' =>
                    'No valid lines to save.'
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        $newCount =
            count(
                $newInserts
            );


        return back()->with(
            'success',
            "{$updatedCount} line(s) updated, {$newCount} new line(s) added (with month/PA splitting)."
        );
    } // End Method

    public function memberLines($participantId)
    {
        $participantId =
            trim(
                (string) $participantId
            );


        $rows =
            DB::table('claims')
            ->select(
                'id',
                DB::raw(
                    'TRIM(ParticipantId) as ParticipantId'
                ),
                'service_from',
                'service_to',
                'proc_code',
                'm1',
                'units',
                'amount_per_unit',
                'total_amount',
                'diag1',
                'diag2',
                'provider',
                'pos',
                'allowed',
                'claim_no',
                'ins1_paid',

                /*
                    |--------------------------------------------------------------------------
                    | Required For Claim Row Color
                    |--------------------------------------------------------------------------
                    */

                'status',
                /*
                    |--------------------------------------------------------------------------
                    | Special / Resubmitted Claim Indicator
                    |--------------------------------------------------------------------------
                    */

                'is_special_claim',
                'original_claim_id',
                'original_claim_no',
                'special_claim_type',
                'special_claim_reason'
            )
            ->whereRaw(
                'TRIM(ParticipantId) = ?',
                [$participantId]
            )
            ->orderBy('service_from')
            ->orderBy('service_to')
            ->orderBy('id')
            ->get();


        return response()
            ->json(
                $rows
            );
    } // End Method


    //Show list of all the claims in draft status and has no claim number assign
    public function draftIndex(Request $request)
    {

        $query = DB::table('claims')
            ->whereNull('claim_no')        // not yet assigned
            ->whereIn('status', ['draft', 'ready'])  // READY must also show!
            ->orderBy('ParticipantId')
            ->orderBy('line_no');     // only draft claims

        $filter = $request->get('filter', 'all');   // all | invalid | valid | not_checked

        if ($filter === 'invalid') {
            $query->where('precheck_result', 'invalid');
        } elseif ($filter === 'valid') {
            $query->where('precheck_result', 'valid');
        } elseif ($filter === 'not_checked') {
            $query->whereNull('precheck_result');
        }

        $claims = $query->orderBy('service_from')->get();

        return view('claims.claims_drafts', compact('claims', 'filter'));
    } // End Method

    public function validateClaims()
    {

        return view('claims.claims_check_claims');
    } // End Method

    public function edit($id)
    {

        $claim = DB::table('claims')->where('id', $id)->first();

        if (!$claim) {
            abort(404);
        }

        return view('claims.claims_edit', compact('claim'));
    } // End Method

    public function update(Request $request, $id)
    {
        $request->validate([
            'ParticipantId' => 'required|string|max:20',
            'service_from'  => 'required|date',
            'service_to'    => 'required|date|after_or_equal:service_from',
            'diag1'    => 'nullable|string|max:10',
            'diag2'    => 'nullable|string|max:10',

        ]);

        $total_amt = ($request->amount_per_unit * $request->units);

        DB::table('claims')
            ->where('id', $id)
            ->update([
                'ParticipantId'      => $request->ParticipantId,
                'service_from'       => $request->service_from,
                'service_to'         => $request->service_to,
                'amount_per_unit'    => $request->amount_per_unit,
                'units'              => $request->units,
                'total_amount'       => $total_amt,
                'allowed'            => $total_amt,
                'diag1'              => $request->diag1,
                'diag2'              => $request->diag2,
                'status'             => 'draft',               // reset status
                'updated_at'         => now(),
            ]);

        return redirect()->route('claims.draft.index')
            ->with('success', 'Claim updated successfully.');
    } // End Method

    public function destroy($id)
    {
        DB::table('claims')->where('id', $id)->delete();

        return redirect()->route('claims.draft.index')
            ->with('success', 'Claim deleted successfully.');
    } // End Method

    // public function checkClaims(Request $request)
    // {
    //      // 1) Get all claims that are still draft and have NO claim number
    // $claims = DB::table('claims')
    //     ->whereNull('claim_no')              // not sent yet
    //     ->where(function ($q) {
    //         $q->whereNull('status')
    //           ->orWhere('status', 'draft');  // treat NULL as draft too
    //     })
    //     ->orderBy('service_from')
    //     ->get();

    // if ($claims->isEmpty()) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'No draft claims found to check.',
    //         'results' => [],
    //     ]);
    // }

    // $results    = [];
    // $validCount = 0;
    // $invalidCount = 0;

    // foreach ($claims as $claim) {
    //     [$isValid, $message] = $this->validateClaim($claim);

    //     $resultValue = $isValid ? 'valid' : 'invalid';

    //     // status: valid → ready, invalid → stay draft
    //     $statusValue = $isValid ? 'ready' : 'draft';

    //     if ($isValid) {
    //         $validCount++;
    //     } else {
    //         $invalidCount++;
    //     }

    //     // 🔹 Save the result in the DB
    //     DB::table('claims')
    //         ->where('id', $claim->id)
    //         ->update([
    //             'precheck_result'  => $resultValue,                        // ENUM: 'valid' / 'invalid'
    //             'precheck_message' => Str::limit($message, 255, ''),       // fit in VARCHAR(255)
    //             'status'           => $statusValue,                        // ENUM: 'draft' / 'ready' / 'sent'
    //             'updated_at'       => now(),
    //         ]);

    //     $results[] = [
    //         'id'             => $claim->id,
    //         'ParticipantId'  => $claim->ParticipantId,
    //         'service_from'   => Carbon::parse($claim->service_from)->format('m/d/Y'),
    //         'service_to'     => Carbon::parse($claim->service_to)->format('m/d/Y'),
    //         'units'          => $claim->units,
    //         'amount_per_unit'=> number_format($claim->amount_per_unit, 2),
    //         'total_amount'   => number_format($claim->total_amount, 2),
    //         'result'         => $resultValue,
    //         'message'        => $message,
    //         'status'         => $statusValue,      // 'ready' or 'draft'
    //     ];
    // }

    // return response()->json([
    //     'success' => true,
    //     'summary' => [
    //         'total'   => $claims->count(),
    //         'valid'   => $validCount,
    //         'invalid' => $invalidCount,
    //     ],
    //     'results' => $results,
    // ]);

    // } // End Method

    public function checkClaims(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Get all unsent draft claims
        |--------------------------------------------------------------------------
        */

        $claims = DB::table('claims')
            ->whereNull('claim_no')
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', 'draft')
                    ->orWhere('status', 'ready');
            })
            ->orderBy('service_from')
            ->get();

        if ($claims->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No draft claims found to check.',
                'results' => [],
            ]);
        }

        $results = [];
        $validCount = 0;
        $invalidCount = 0;

        foreach ($claims as $claim) {

            /*
            |--------------------------------------------------------------------------
            | Refresh PA information from member before validation
            |--------------------------------------------------------------------------
            */

            $this->syncClaimPriorAuthorization($claim);

            /*
            |--------------------------------------------------------------------------
            | Reload claim because PA information may have changed
            |--------------------------------------------------------------------------
            */

            $claim = DB::table('claims')
                ->where('id', $claim->id)
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Validate
            |--------------------------------------------------------------------------
            */

            [$isValid, $message] = $this->validateClaim($claim);

            $resultValue = $isValid ? 'valid' : 'invalid';
            $statusValue = $isValid ? 'ready' : 'draft';

            if ($isValid) {
                $validCount++;
            } else {
                $invalidCount++;
            }

            DB::table('claims')
                ->where('id', $claim->id)
                ->update([
                    'precheck_result'  => $resultValue,
                    'precheck_message' => Str::limit($message, 255, ''),
                    'status'           => $statusValue,
                    'updated_at'       => now(),
                ]);

            $results[] = [
                'id'               => $claim->id,
                'ParticipantId'    => $claim->ParticipantId,
                'service_from'     => Carbon::parse($claim->service_from)->format('m/d/Y'),
                'service_to'       => Carbon::parse($claim->service_to)->format('m/d/Y'),
                'units'            => $claim->units,
                'amount_per_unit'  => number_format($claim->amount_per_unit, 2),
                'total_amount'     => number_format($claim->total_amount, 2),
                'result'           => $resultValue,
                'message'          => $message,
                'status'           => $statusValue,
            ];
        }

        return response()->json([
            'success' => true,

            'summary' => [
                'total'   => $claims->count(),
                'valid'   => $validCount,
                'invalid' => $invalidCount,
            ],

            'results' => $results,
        ]);
    } // End Method

    private function syncClaimPriorAuthorization($claim): void
    {
        if (empty($claim->ParticipantId)) {
            return;
        }

        $member = DB::table('billing_members')
            ->whereRaw(
                'TRIM(ParticipantId) = ?',
                [trim($claim->ParticipantId)]
            )
            ->first();

        if (!$member) {
            return;
        }

        $pa = $this->resolvePriorAuthorization($claim, $member);

        /*
        |--------------------------------------------------------------------------
        | Only update claim when ONE PA completely covers the claim
        |--------------------------------------------------------------------------
        |
        | If it crosses PA periods, do not put the wrong PA on the claim.
        |
        */

        if ($pa['valid'] === true) {

            $updateData = [
                'prior_auth_no' => $pa['pa_no'],
                'updated_at'    => now(),
            ];


            /*
            |--------------------------------------------------------------------------
            | Normal PA
            |--------------------------------------------------------------------------
            */

            if (($pa['source'] ?? '') !== 'special_override') {

                $updateData['prior_auth_expiry'] =
                    $pa['pa_end'] ?? null;
            } else {

                /*
                |--------------------------------------------------------------------------
                | Special PA Override
                |--------------------------------------------------------------------------
                |
                | Do not fabricate an expiration date.
                |
                */

                $updateData['prior_auth_expiry'] = null;
            }


            DB::table('claims')
                ->where('id', $claim->id)
                ->update($updateData);
        }
    } // End Method

    private function resolvePriorAuthorization($claim, $member): array
    {

        /*
        |--------------------------------------------------------------------------
        | Special Claim - Manual PA Override
        |--------------------------------------------------------------------------
        |
        | Special/resubmission claims may intentionally use an old PA number.
        | In that case:
        |
        | - Do not check billing_members PA periods
        | - Do not check PA expiration
        | - Use the manually entered override_prior_auth_no
        |
        */

        $isSpecialClaim =
            (int) ($claim->is_special_claim ?? 0) === 1;

        $overridePaValidation =
            (int) ($claim->override_pa_validation ?? 0) === 1;


        if (
            $isSpecialClaim
            &&
            $overridePaValidation
        ) {

            $overridePaNo = trim(
                (string) (
                    $claim->override_prior_auth_no
                    ?? ''
                )
            );


            /*
            |--------------------------------------------------------------------------
            | Override Was Requested But No PA Number Was Entered
            |--------------------------------------------------------------------------
            */

            if ($overridePaNo === '') {

                return [
                    'valid'          => false,
                    'split_required' => false,
                    'source'         => 'special_override',
                    'pa_no'          => null,
                    'pa_start'       => null,
                    'pa_end'         => null,
                    'message'        =>
                    'Special claim PA override is enabled, but no PA number was entered.',
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | Valid Special PA Override
            |--------------------------------------------------------------------------
            |
            | No date validation is performed.
            |
            */

            return [
                'valid'          => true,
                'split_required' => false,
                'source'         => 'special_override',
                'pa_no'          => $overridePaNo,
                'pa_start'       => null,
                'pa_end'         => null,
                'message'        => 'Special claim PA override accepted.',
            ];
        }

        try {

            $serviceFrom = Carbon::parse($claim->service_from)->startOfDay();
            $serviceTo   = Carbon::parse($claim->service_to)->startOfDay();
        } catch (\Exception $e) {

            return [
                'valid'          => false,
                'split_required' => false,
                'message'        => 'Invalid service dates.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Build available PA periods
        |--------------------------------------------------------------------------
        */

        $paPeriods = [];

        /*
        |--------------------------------------------------------------------------
        | Original Medicaid PA
        |--------------------------------------------------------------------------
        */

        if (
            !empty($member->medicaid_PA_no) &&
            !empty($member->medicaid_PA_Start) &&
            !empty($member->medicaid_PA_End)
        ) {

            try {

                $paPeriods[] = [
                    'source' => 'medicaid',
                    'pa_no'  => trim($member->medicaid_PA_no),

                    'start' => Carbon::parse(
                        $member->medicaid_PA_Start
                    )->startOfDay(),

                    'end' => Carbon::parse(
                        $member->medicaid_PA_End
                    )->startOfDay(),
                ];
            } catch (\Exception $e) {
                // Invalid PA dates will simply be ignored here.
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Renewal PA
        |--------------------------------------------------------------------------
        */

        if (
            !empty($member->renew1_PA_no) &&
            !empty($member->renew1_PA_Start) &&
            !empty($member->renew1_PA_End)
        ) {

            try {

                $paPeriods[] = [
                    'source' => 'renew1',
                    'pa_no'  => trim($member->renew1_PA_no),

                    'start' => Carbon::parse(
                        $member->renew1_PA_Start
                    )->startOfDay(),

                    'end' => Carbon::parse(
                        $member->renew1_PA_End
                    )->startOfDay(),
                ];
            } catch (\Exception $e) {
                // Ignore malformed PA period.
            }
        }

        /*
        |--------------------------------------------------------------------------
        | No usable PA
        |--------------------------------------------------------------------------
        */

        if (empty($paPeriods)) {

            return [
                'valid'          => false,
                'split_required' => false,
                'message'        => 'No valid prior authorization information found for member.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | FIRST:
        | See whether one PA completely covers service range
        |--------------------------------------------------------------------------
        */

        foreach ($paPeriods as $pa) {

            if (
                $serviceFrom->gte($pa['start']) &&
                $serviceTo->lte($pa['end'])
            ) {

                return [
                    'valid'          => true,
                    'split_required' => false,

                    'source'   => $pa['source'],
                    'pa_no'    => $pa['pa_no'],
                    'pa_start' => $pa['start']->toDateString(),
                    'pa_end'   => $pa['end']->toDateString(),

                    'message' => 'OK',
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SECOND:
        | Determine whether service range crosses TWO valid PA periods
        |--------------------------------------------------------------------------
        */

        usort($paPeriods, function ($a, $b) {
            return $a['start']->timestamp <=> $b['start']->timestamp;
        });

        $segments = [];

        foreach ($paPeriods as $pa) {

            /*
            | Find overlap between claim range and this PA.
            */

            $segmentStart = $serviceFrom->copy()->max($pa['start']);
            $segmentEnd   = $serviceTo->copy()->min($pa['end']);

            if ($segmentStart->lte($segmentEnd)) {

                $segments[] = [
                    'source'       => $pa['source'],
                    'pa_no'        => $pa['pa_no'],
                    'pa_start'     => $pa['start']->toDateString(),
                    'pa_end'       => $pa['end']->toDateString(),
                    'service_from' => $segmentStart->toDateString(),
                    'service_to'   => $segmentEnd->toDateString(),
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Two PA periods cover portions of this claim
        |--------------------------------------------------------------------------
        */

        if (count($segments) >= 2) {

            return [
                'valid'          => false,
                'split_required' => true,
                'segments'       => $segments,

                'message' =>
                'Service dates cross two prior authorization periods. '
                    . 'Claim must be split by PA.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | No PA covers entire claim and no complete crossover
        |--------------------------------------------------------------------------
        */

        return [
            'valid'          => false,
            'split_required' => false,

            'message' =>
            'No prior authorization completely covers the service date range.',
        ];
    } // End Method

    // private function validateClaim($claim): array
    // {
    //         $errors = [];

    //         /*
    //     |--------------------------------------------------------------------------
    //     | 1) Claim-level checks (dates, units, amounts)
    //     |--------------------------------------------------------------------------
    //     */
    //     if (empty($claim->ParticipantId)) {
    //         $errors[] = 'ParticipantId is required.';
    //     }

    //     if (empty($claim->service_from) || empty($claim->service_to)) {
    //         $errors[] = 'Service dates are required.';
    //     } else {
    //         try {
    //             $from  = Carbon::parse($claim->service_from)->startOfDay();
    //             $to    = Carbon::parse($claim->service_to)->startOfDay();
    //             $today = Carbon::today();

    //             // 1) To >= From
    //             if ($to->lt($from)) {
    //                 $errors[] = 'Service To cannot be before Service From.';
    //             }

    //             // 2) No future dates
    //             if ($from->gt($today) || $to->gt($today)) {
    //                 $errors[] = 'Service dates cannot be in the future.';
    //             }

    //         } catch (\Exception $e) {
    //             $errors[] = 'Invalid service dates.';
    //         }
    //     }

    //     if (empty($claim->units) || $claim->units <= 0) {
    //         $errors[] = 'Units must be greater than zero.';
    //     }

    //     if (empty($claim->amount_per_unit) || $claim->amount_per_unit <= 0) {
    //         $errors[] = 'Amount per unit must be greater than zero.';
    //     }

    //     $calculated = round($claim->units * $claim->amount_per_unit, 2);

    //     if ($calculated != (float)$claim->total_amount) {
    //         $errors[] = 'Total amount not equal to units × amount per unit.';
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 2) Diagnosis / ICD-10 checks
    //     |--------------------------------------------------------------------------
    //     | Assumes claim has diag1, diag2, diag3, diag4.
    //     | Assumes icd10_codes table has column "code".
    //     */
    //     $diagFields = ['diag1', 'diag2']; // adjust if you have fewer/more
    //     $hasDiag = false;
    //     $invalidDiags = [];

    //     foreach ($diagFields as $field) {
    //         if (!empty($claim->$field)) {
    //             $hasDiag = true;
    //             $code = trim($claim->$field);

    //             $exists = DB::table('icd10_codes')
    //                 ->where('code', $code)
    //                 ->exists();

    //             if (!$exists) {
    //                 $invalidDiags[] = $code;
    //             }
    //         }
    //     }

    //     if (!$hasDiag) {
    //         $errors[] = 'At least one diagnosis code (ICD-10) is required.';
    //     }

    //     if (!empty($invalidDiags)) {
    //         $errors[] = 'Invalid ICD-10 code(s): ' . implode(', ', $invalidDiags) . '.';
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 3) Member-level checks (address, DOB)
    //     |--------------------------------------------------------------------------
    //     | Adjust field names to match your members table.
    //     */
    //     if (!empty($claim->ParticipantId)) {
    //         $member = DB::table('members')
    //             ->whereRaw('TRIM(ParticipantId) = ?', [trim($claim->ParticipantId)])
    //             ->first();

    //         if (!$member) {
    //             $errors[] = 'Member not found in members table.';
    //         } else {
    //             // Address fields – adjust field names as per your schema
    //             $street = isset($member->Address1) ? trim($member->Address1) : '';
    //             $city   = isset($member->City)     ? trim($member->City)     : '';
    //             $state  = isset($member->State)    ? trim($member->State)    : '';
    //             $zip    = isset($member->ZipCode)      ? trim($member->ZipCode)   : '';

    //             if ($street === '' || $city === '' || $state === '' || $zip === '') {
    //                 $errors[] = 'Member address, city, state, and ZIP are required.';
    //             }

    //             // DOB – adjust to your actual column (common ones: DOB, BirthDate, DateOfBirth)
    //             $dob = $member->DateOfBirth ?? $member->DateOfBirth ?? null;

    //             if (empty($dob)) {
    //                 $errors[] = 'Member date of birth is required.';
    //             } else {
    //                 try {
    //                     $dobDate = Carbon::parse($dob);
    //                     if ($dobDate->isFuture()) {
    //                         $errors[] = 'Member date of birth cannot be in the future.';
    //                     }
    //                 } catch (\Exception $e) {
    //                     $errors[] = 'Member date of birth is invalid.';
    //                 }
    //             }
    //         }
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 4) Prior Authorization checks
    //     |--------------------------------------------------------------------------
    //     | Adjust field names:
    //     |  - prior_auth_no           (string)
    //     |  - prior_auth_expiry_date  (date/datetime)
    //     */
    //     if (empty($claim->prior_auth_no)) {
    //         $errors[] = 'Prior authorization number is required.';
    //     }

    //     if (empty($claim->prior_auth_expiry)) {
    //         $errors[] = 'Prior authorization expiry date is required.';
    //     } else {
    //         try {
    //             $paExpiry = Carbon::parse($claim->prior_auth_expiry)->startOfDay();

    //             if (!empty($claim->service_from) && !empty($claim->service_to)) {
    //                 $serviceFrom = Carbon::parse($claim->service_from)->startOfDay();
    //                 $serviceTo   = Carbon::parse($claim->service_to)->startOfDay();

    //                 // PA must be valid THROUGH the end of service range
    //                 if ($paExpiry->lt($serviceTo)) {
    //                     $errors[] = 'Prior authorization has expired for the service dates.';
    //                 }
    //             }
    //         } catch (\Exception $e) {
    //             $errors[] = 'Prior authorization expiry date is invalid.';
    //         }
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | 5) Insurance ID check
    //     |--------------------------------------------------------------------------
    //     | Adjust field name: insurance_company_id / insurance_id / payer_id etc.
    //     */
    //     if (empty($claim->insurance_id)) {
    //         $errors[] = 'Insurance ID is required.';
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Final result
    //     |--------------------------------------------------------------------------
    //     */
    //     if ($errors) {
    //         return [false, implode(' ', $errors)];
    //     }

    //     return [true, 'OK'];

    // } // End MEthod

    private function validateClaim($claim): array
    {
        $errors = [];

        /*
        |--------------------------------------------------------------------------
        | 1. Claim-level validation
        |--------------------------------------------------------------------------
        */

        if (empty($claim->ParticipantId)) {
            $errors[] = 'Participant ID is required.';
        }

        if (empty($claim->service_from) || empty($claim->service_to)) {

            $errors[] = 'Service dates are required.';
        } else {

            try {

                $from  = Carbon::parse($claim->service_from)->startOfDay();
                $to    = Carbon::parse($claim->service_to)->startOfDay();
                $today = Carbon::today();

                if ($to->lt($from)) {
                    $errors[] = 'Service To cannot be before Service From.';
                }

                if ($from->gt($today) || $to->gt($today)) {
                    $errors[] = 'Service dates cannot be in the future.';
                }
            } catch (\Exception $e) {

                $errors[] = 'Invalid service dates.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Units
        |--------------------------------------------------------------------------
        */

        if (empty($claim->units) || $claim->units <= 0) {
            $errors[] = 'Units must be greater than zero.';
        }

        /*
        |--------------------------------------------------------------------------
        | Amount
        |--------------------------------------------------------------------------
        */

        if (
            empty($claim->amount_per_unit) ||
            $claim->amount_per_unit <= 0
        ) {
            $errors[] = 'Amount per unit must be greater than zero.';
        }

        $calculated = round(
            (float)$claim->units *
                (float)$claim->amount_per_unit,
            2
        );

        if (
            round((float)$claim->total_amount, 2)
            != $calculated
        ) {
            $errors[] =
                'Total amount not equal to units × amount per unit.';
        }

        /*
        |--------------------------------------------------------------------------
        | 2. ICD-10 validation
        |--------------------------------------------------------------------------
        */

        $diagFields = [
            'diag1',
            'diag2',
        ];

        $hasDiag = false;
        $invalidDiags = [];

        foreach ($diagFields as $field) {

            if (!empty($claim->$field)) {

                $hasDiag = true;

                $code = trim($claim->$field);

                $exists = DB::table('icd10_codes')
                    ->where('code', $code)
                    ->exists();

                if (!$exists) {
                    $invalidDiags[] = $code;
                }
            }
        }

        if (!$hasDiag) {

            $errors[] =
                'At least one diagnosis code (ICD-10) is required.';
        }

        if (!empty($invalidDiags)) {

            $errors[] =
                'Invalid ICD-10 code(s): '
                . implode(', ', $invalidDiags)
                . '.';
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Member validation
        |--------------------------------------------------------------------------
        */

        $member = null;

        if (!empty($claim->ParticipantId)) {

            $member = DB::table('billing_members')
                ->whereRaw(
                    'TRIM(ParticipantId) = ?',
                    [trim($claim->ParticipantId)]
                )
                ->first();

            if (!$member) {

                $errors[] =
                    'Member not found in billing_members table.';
            } else {

                /*
                |--------------------------------------------------------------------------
                | Member address
                |--------------------------------------------------------------------------
                */

                $street = trim(
                    (string)($member->Address1 ?? '')
                );

                $city = trim(
                    (string)($member->City ?? '')
                );

                $state = trim(
                    (string)($member->State ?? '')
                );

                $zip = trim(
                    (string)($member->ZipCode ?? '')
                );

                if (
                    $street === '' ||
                    $city === '' ||
                    $state === '' ||
                    $zip === ''
                ) {

                    $errors[] =
                        'Member address, city, state, and ZIP are required.';
                }

                /*
                |--------------------------------------------------------------------------
                | DOB
                |--------------------------------------------------------------------------
                */

                $dob = $member->DateOfBirth ?? null;

                if (empty($dob)) {

                    $errors[] =
                        'Member date of birth is required.';
                } else {

                    try {

                        $dobDate = Carbon::parse($dob);

                        if ($dobDate->isFuture()) {
                            $errors[] =
                                'Member date of birth cannot be in the future.';
                        }
                    } catch (\Exception $e) {

                        $errors[] =
                            'Member date of birth is invalid.';
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | 4. Prior Authorization
                |--------------------------------------------------------------------------
                */

                $pa = $this->resolvePriorAuthorization(
                    $claim,
                    $member
                );

                if (!$pa['valid']) {

                    if (!empty($pa['split_required'])) {

                        /*
                        | Very specific message so user understands
                        | that this is not simply an expired PA.
                        */

                        $errors[] =
                            'Service dates cross two prior authorization periods. '
                            . 'Claim must be split by PA.';
                    } else {

                        $errors[] = $pa['message'];
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Insurance ID
        |--------------------------------------------------------------------------
        */

        if (empty($claim->insurance_id)) {
            $errors[] = 'Insurance ID is required.';
        }

        /*
        |--------------------------------------------------------------------------
        | Final
        |--------------------------------------------------------------------------
        */

        if (!empty($errors)) {

            return [
                false,
                implode(' ', $errors),
            ];
        }

        return [
            true,
            'OK',
        ];
    } // End Method

    public function sendClaims(Request $request)
    {
        $claimNos = $request->input('claim_nos', []);

        if (empty($claimNos)) {
            return back()->with('warning', 'No claims selected to send.');
        }

        // Mark selected claims as submitted (for now)
        DB::table('claims')
            ->whereIn('claim_no', $claimNos)
            ->update([
                'status'     => 'submitted',
                'updated_at' => now(),
            ]);

        // Here you will plug in your 837P generator using $claimNos
        // For now we just show a message.
        return back()->with('success', 'Selected claims marked as ready to send (837P generation to be implemented).');
    } // End Method

    public function previewCms1500($id)
    {
        $claim = DB::table('claims')
            ->where('id', $id)
            ->first();

        if (!$claim) {
            abort(404, 'Claim not found');
        }

        // 2) Get member by ParticipantId (no join)
        // If your ParticipantId has old CHAR padding, we can TRIM it:
        $member = DB::table('billing_members')
            ->whereRaw('TRIM(ParticipantId) = ?', [$claim->ParticipantId])
            ->first();

        if (!$member) {
            abort(404, 'Member not found for ParticipantId ' . $claim->ParticipantId);
        }


        // Company info (billing provider)
        $company = DB::table('gnmscomp')->first();

        // 3) Insurance lookup with explicit COLLATE (separate query)
        $insurance = null;
        if (!empty($claim->insurance_name)) {
            $insurance = DB::table('insurance_companies')
                ->where('code', $claim->insurance_name)
                ->first();
        }

        return view('claims.claims_cms_preview', compact('claim', 'company', 'insurance', 'member'));
    } // End Method

    public function cms1500Pdf($id)
    {
        // 1) Claim
        $claim = DB::table('claims')->where('id', $id)->first();
        if (!$claim) {
            abort(404, 'Claim not found');
        }

        // 2) Member
        $member = DB::table('billing_members')
            ->whereRaw('TRIM(ParticipantId) = ?', [$claim->ParticipantId])
            ->first();
        if (!$member) {
            abort(404, 'Member not found for ParticipantId ' . $claim->ParticipantId);
        }

        // 3) Insurance (short name stored in claims.insurance_name)
        $insurance = null;
        if (!empty($claim->insurance_name)) {
            $insurance = DB::table('insurance_companies')
                ->where('code', $claim->insurance_name)
                ->first();
        }

        // 4) Company info (billing/rendering provider)
        $company = DB::table('gnmscomp')->first();

        // 5) Build PDF
        $mpdf = new Mpdf([
            'tempDir' => storage_path('app/mpdf'),
            'format'        => 'Letter',   // CMS-1500 is US Letter
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'margin_left'   => 0,
            'margin_right'  => 0,
        ]);

        // Use your blank CMS-1500 as background template
        $mpdf->SetDocTemplate(public_path('forms/cms1500_blank.pdf'), true);

        // Our overlay HTML with positioned fields
        $html = view('claims.claims_cms1500_pdf_overlay', compact('claim', 'member', 'insurance', 'company'))->render();

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'I'))
            ->header('Content-Type', 'application/pdf');
    } // End Method


    public function cms1500Dompdf($id)
    {
        // 1) Claim
        $claim = DB::table('claims')->where('id', $id)->first();
        if (!$claim) {
            abort(404, 'Claim not found');
        }

        // 2) Member
        $member = DB::table('billing_members')
            ->whereRaw('TRIM(ParticipantId) = ?', [$claim->ParticipantId])
            ->first();
        if (!$member) {
            abort(404, 'Member not found for ParticipantId ' . $claim->ParticipantId);
        }

        // 3) Insurance (short code is in claims.insurance_name)
        $insurance = null;
        if (!empty($claim->insurance_name)) {
            $insurance = DB::table('insurance_companies')
                ->where('code', $claim->insurance_name)
                ->first();
        }

        // 4) Company (billing/rendering provider)
        $company = DB::table('gnmscomp')->first();

        // 5) Get ALL service lines for this claim

        if (!empty($claim->claim_no)) {
            // Finalized claim → group by claim_no
            $lines = DB::table('claims')
                ->where('claim_no', $claim->claim_no)
                ->orderBy('line_no')
                ->get();
        } elseif (!empty($claim->claim_group)) {
            // Draft claim → group by internal claim_group
            $lines = DB::table('claims')
                ->where('claim_group', $claim->claim_group)
                ->orderBy('line_no')
                ->get();
        } else {
            // Fallback (no group info) → single-line claim
            $lines = collect([$claim]);
        }

        // Total charge = sum of all lines
        $totalCharge = $lines->sum('total_amount');

        // 5) Generate PDF
        $pdf = Pdf::loadView('claims.claims_cms1500_dompdf', [
            'claim'     => $claim,
            'member'    => $member,
            'insurance' => $insurance,
            'company'   => $company,
            'lines'        => $lines,        // all service lines
            'totalCharge'  => $totalCharge,  // for box 28/30
        ])
            ->setPaper('letter', 'portrait');

        $filename = 'cms1500_claim_' . ($claim->claim_no ?? $claim->id) . '.pdf';

        return $pdf->stream($filename);
        // or ->download($filename);

    } // End Method

    public function showAttendanceGenerateForm(Request $request, $type)
    {
        // $type will be "A" or "N"
        return view('claims.claims_attendance_generate', [
            'type' => $type, // pass shift type to blade
            'startDate' => $request->query('start_date'),
            'endDate'   => $request->query('end_date'),
        ]);
    } // End Method

    // public function generateClaimsFromAttendance(Request $request, $type)
    // {
    //     // type = A (Morning/Active) or N (Noon/Inactive)
    //     $memberStatus = $type;

    //     $request->validate([
    //         'start_date' => ['required', 'date'],
    //         'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
    //     ]);

    //     $startDate = $request->input('start_date');
    //     $endDate   = $request->input('end_date');
    //     $force     = $request->input('force') == '1';

    //     // Get participant IDs
    //     $participantIds = DB::table('members')
    //         ->where('Trans_flag', $memberStatus)
    //         ->pluck('ParticipantId')
    //         ->toArray();

    //     if (empty($participantIds)) {
    //         return back()->with('message', 'No members found for this shift.');
    //     }

    //     // 0b) Load members info once, keyed by ParticipantId (no join, no collation issue)
    //     $members = DB::table('members')
    //         ->whereIn('ParticipantId', $participantIds)
    //         ->get()
    //         ->keyBy('ParticipantId');   // $members['005'], $members['138'], etc.

    //     // Check for existing claims
    //     $existingCount = DB::table('claims')
    //         ->where(function ($q) use ($startDate, $endDate) {
    //             $q->whereBetween('service_from', [$startDate, $endDate])
    //             ->orWhereBetween('service_to', [$startDate, $endDate]);
    //         })
    //         ->whereIn('ParticipantId', $participantIds)
    //         ->count();

    //     if ($existingCount > 0 && !$force) {
    //         return redirect()
    //             ->back()
    //             ->withInput()
    //             ->with('confirm_generate', true)
    //             ->with('existing_count', $existingCount)
    //             ->with('start_date', $startDate)
    //             ->with('end_date', $endDate)
    //             ->with('message', "There are already {$existingCount} claim line(s) for this range. Continue?");
    //     }
    //     // FETCH ATTENDANCE + GENERATE CLAIMS (existing logic)...
    //     // 2) Fetch attendance for those members & date range
    //     $attendanceByParticipant = DB::table('partattendence')
    //         ->select('ParticipantId', 'CalenderDate')
    //         ->whereBetween('CalenderDate', [$startDate, $endDate])
    //         ->where('AttendanceYN', 'Y')
    //         ->whereIn('ParticipantId', $participantIds)
    //         ->orderBy('ParticipantId')
    //         ->orderBy('CalenderDate')
    //         ->get()
    //         ->groupBy('ParticipantId');

    //     // Defaults
    //     $defaultProcCode      = 'S5102';
    //     $defaultModifier      = 'U1';
    //     $defaultAmountPerUnit = 94.66;

    //     $insertedCount = 0;

    //     DB::beginTransaction();

    //     try {
    //         foreach ($attendanceByParticipant as $participantId => $rows) {

    //             $member = $members->get($participantId);   // may be null if mismatch

    //             $insuranceName = $member->HMO_Enrolled ?? null; // <-- adjust names if needed
    //             $insuranceId   = $member->medicaid_HMO_id ?? null;
    //             $priorAuthNo   = $member->medicaid_PA_no ?? null;
    //             $diag1 = $member->PrimaryDiagnosis1 ?? null;
    //             $diag2 = $member->SecondaryDiag1 ?? null;

    //             $insurance = DB::table('insurance_companies')
    //                         ->where('code',$insuranceName)
    //                         ->first();

    //             //dd($insurance);

    //             $payer_fullname = $insurance->full_name;
    //             $payer_payerid = $insurance->payer_id;
    //             $payer_address1 = $insurance->address1;
    //             $payer_address2 = $insurance->address2;
    //             $payer_city = $insurance->city;
    //             $payer_state = $insurance->state;
    //             $payer_zip = $insurance->zip;

    //             // If stored as datetime/string like "10/6/2026 12:00:00 AM"
    //             if (!empty($member->medicaid_PA_End)) {
    //                 try {
    //                     $priorAuthExpiry = Carbon::parse($member->medicaid_PA_End)->toDateString();
    //                 } catch (\Throwable $e) {
    //                     $priorAuthExpiry = null; // bad date → ignore
    //                 }
    //             } else {
    //                 $priorAuthExpiry = null;
    //             }

    //             // One claim_group per participant per run
    //             $claimGroup = $participantId . '-' . now()->format('YmdHis');
    //             $lineNo     = 1;

    //             $segmentStart = null;
    //             $prevDate     = null;
    //             $units        = 0;

    //             foreach ($rows as $row) {
    //                 $current = Carbon::parse($row->CalenderDate)->startOfDay();

    //                 if (is_null($segmentStart)) {
    //                     // start first segment
    //                     $segmentStart = $current;
    //                     $prevDate     = $current;
    //                     $units        = 1;
    //                     continue;
    //                 }

    //                 $expectedNext = (clone $prevDate)->addDay();

    //                 if ($current->equalTo($expectedNext)) {
    //                     // still same continuous block
    //                     $units++;
    //                     $prevDate = $current;
    //                 } else {
    //                     // close previous block and insert claim line
    //                     $this->insertClaimLine(
    //                         $participantId,
    //                         $claimGroup,
    //                         $lineNo++,
    //                         $segmentStart,
    //                         $prevDate,
    //                         $units,
    //                         $defaultProcCode,
    //                         $defaultModifier,
    //                         $defaultAmountPerUnit,
    //                         $insuranceName,
    //                         $payer_fullname,
    //                         $payer_payerid,
    //                         $payer_address1,
    //                         $payer_address2,
    //                         $payer_city,
    //                         $payer_state,
    //                         $payer_zip,
    //                         $insuranceId,
    //                         $priorAuthNo,
    //                         $priorAuthExpiry,
    //                         $diag1,
    //                         $diag2
    //                     );
    //                     $insertedCount++;

    //                     // start new segment
    //                     $segmentStart = $current;
    //                     $prevDate     = $current;
    //                     $units        = 1;
    //                 }
    //             }

    //             // flush final segment
    //             if (!is_null($segmentStart)) {
    //                 $this->insertClaimLine(
    //                     $participantId,
    //                     $claimGroup,
    //                     $lineNo++,
    //                     $segmentStart,
    //                     $prevDate,
    //                     $units,
    //                     $defaultProcCode,
    //                     $defaultModifier,
    //                     $defaultAmountPerUnit,
    //                     $insuranceName,
    //                     $payer_fullname,
    //                     $payer_payerid,
    //                     $payer_address1,
    //                     $payer_address2,
    //                     $payer_city,
    //                     $payer_state,
    //                     $payer_zip,
    //                     $insuranceId,
    //                     $priorAuthNo,
    //                     $priorAuthExpiry,
    //                     $diag1,
    //                     $diag2
    //                 );
    //                 $insertedCount++;
    //             }
    //         }

    //         DB::commit();

    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         return back()
    //             ->withInput()
    //             ->with('message', 'Error generating claims: ' . $e->getMessage())
    //             ->with('alert-type', 'error');
    //     }

    //     return redirect()
    //     ->route('claims.attendance.form', $type)
    //     ->with('message', "Generated {$insertedCount} claim line(s) from attendance.")
    //     ->with('alert-type', 'success');

    // } // End Method

    public function generateClaimsFromAttendance(Request $request, $type)
    {
        /*
        |--------------------------------------------------------------------------
        | Shift / Member Status
        |--------------------------------------------------------------------------
        |
        | A = Morning
        | N = Noon
        |
        */

        $memberStatus = strtoupper($type);

        if (!in_array($memberStatus, ['A', 'N'], true)) {
            return back()
                ->with('message', 'Invalid shift selected.')
                ->with('alert-type', 'error');
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Date Range
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->toDateString();
        $endDate   = Carbon::parse($request->input('end_date'))->toDateString();

        $force = $request->input('force') == '1';

        /*
        |--------------------------------------------------------------------------
        | Claim Defaults
        |--------------------------------------------------------------------------
        */

        $defaultProcCode = 'S5102';
        $defaultModifier = 'U1';

        /*
        |--------------------------------------------------------------------------
        | Get Members For Selected Shift
        |--------------------------------------------------------------------------
        */

        $membersCollection = DB::table('billing_members')
            ->where('Trans_flag', $memberStatus)
            ->get();

        if ($membersCollection->isEmpty()) {
            return back()
                ->with('message', 'No billing members found for this shift.')
                ->with('alert-type', 'warning');
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize ParticipantId Keys
        |--------------------------------------------------------------------------
        */

        $members = $membersCollection->keyBy(function ($member) {
            return trim((string) $member->ParticipantId);
        });

        $participantIds = $membersCollection
            ->pluck('ParticipantId')
            ->map(function ($id) {
                return trim((string) $id);
            })
            ->filter()
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Check Existing Claims
        |--------------------------------------------------------------------------
        |
        | Proper overlap check:
        |
        | Existing From <= Requested End
        | AND
        | Existing To >= Requested Start
        |
        */

        $existingCount = DB::table('claims')
            ->whereIn('ParticipantId', $participantIds)
            ->whereDate('service_from', '<=', $endDate)
            ->whereDate('service_to', '>=', $startDate)
            ->count();

        if ($existingCount > 0 && !$force) {
            return redirect()
                ->back()
                ->withInput()
                ->with('confirm_generate', true)
                ->with('existing_count', $existingCount)
                ->with('start_date', $startDate)
                ->with('end_date', $endDate)
                ->with(
                    'message',
                    "There are already {$existingCount} claim line(s) overlapping this date range. Continue?"
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Attendance
        |--------------------------------------------------------------------------
        */

        $attendanceByParticipant = DB::table('partattendence')
            ->select(
                'ParticipantId',
                'CalenderDate'
            )
            ->whereBetween('CalenderDate', [
                $startDate,
                $endDate
            ])
            ->where('AttendanceYN', 'Y')
            ->whereIn('ParticipantId', $participantIds)
            ->orderBy('ParticipantId')
            ->orderBy('CalenderDate')
            ->get()
            ->groupBy(function ($row) {
                return trim((string) $row->ParticipantId);
            });

        if ($attendanceByParticipant->isEmpty()) {
            return back()
                ->withInput()
                ->with('message', 'No attendance records found for the selected date range.')
                ->with('alert-type', 'warning');
        }

        /*
        |--------------------------------------------------------------------------
        | Process Claims
        |--------------------------------------------------------------------------
        */

        $insertedCount = 0;

        DB::beginTransaction();

        try {

            foreach ($attendanceByParticipant as $participantId => $rows) {

                /*
                |--------------------------------------------------------------------------
                | Member
                |--------------------------------------------------------------------------
                */

                $member = $members->get(trim((string) $participantId));

                if (!$member) {
                    throw new \Exception(
                        "Member {$participantId} was found in attendance but not in billing_members table."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Insurance Information
                |--------------------------------------------------------------------------
                */

                $insuranceName = $member->HMO_Enrolled ?? null;
                $insuranceId   = $member->medicaid_HMO_id ?? null;

                $diag1 = !empty($member->PrimaryDiagnosis1)
                    ? trim($member->PrimaryDiagnosis1)
                    : null;

                $diag2 = !empty($member->SecondaryDiag1)
                    ? trim($member->SecondaryDiag1)
                    : null;

                /*
                |--------------------------------------------------------------------------
                | Insurance Company / Payer
                |--------------------------------------------------------------------------
                */

                $insurance = null;

                if (!empty($insuranceName)) {
                    $insurance = DB::table('insurance_companies')
                        ->where('code', $insuranceName)
                        ->first();
                }

                $payer_fullname = $insurance->full_name ?? null;
                $payer_payerid  = $insurance->payer_id ?? null;
                $payer_address1 = $insurance->address1 ?? null;
                $payer_address2 = $insurance->address2 ?? null;
                $payer_city     = $insurance->city ?? null;
                $payer_state    = $insurance->state ?? null;
                $payer_zip      = $insurance->zip ?? null;

                /*
                |--------------------------------------------------------------------------
                | One Claim Group Per Participant Per Run
                |--------------------------------------------------------------------------
                */

                $claimGroup =
                    trim((string) $participantId)
                    . '-'
                    . now()->format('YmdHis');

                $lineNo = 1;

                /*
                |--------------------------------------------------------------------------
                | Segment Variables
                |--------------------------------------------------------------------------
                |
                | A new segment starts when:
                |
                | 1. Attendance date is not consecutive
                | 2. PA number changes
                | 3. PA expiration changes
                | 4. Rate changes
                |
                */

                $segmentStart = null;
                $segmentEnd   = null;
                $prevDate     = null;

                $segmentUnits = 0;

                $segmentPaNo     = null;
                $segmentPaExpiry = null;

                $segmentRate = null;

                foreach ($rows as $row) {

                    $currentDate = Carbon::parse(
                        $row->CalenderDate
                    )->startOfDay();

                    /*
                    |--------------------------------------------------------------------------
                    | Find PA Valid For THIS Attendance Date
                    |--------------------------------------------------------------------------
                    */

                    $pa = $this->getPriorAuthorizationForDate(
                        $member,
                        $currentDate
                    );

                    if (!$pa) {
                        throw new \Exception(
                            'No valid PA found for Participant '
                                . $participantId
                                . ' on '
                                . $currentDate->format('m/d/Y')
                                . '.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Find Rate Valid For THIS Attendance Date
                    |--------------------------------------------------------------------------
                    */

                    $rate = $this->getClaimRateForDate(
                        $currentDate,
                        $defaultProcCode,
                        $defaultModifier
                    );

                    if (!$rate) {
                        throw new \Exception(
                            'No service rate found for Participant '
                                . $participantId
                                . ' on '
                                . $currentDate->format('m/d/Y')
                                . '.'
                        );
                    }

                    $currentRate = (float) $rate->amount_per_unit;

                    /*
                    |--------------------------------------------------------------------------
                    | First Attendance Date For Participant
                    |--------------------------------------------------------------------------
                    */

                    if ($segmentStart === null) {

                        $segmentStart = $currentDate->copy();
                        $segmentEnd   = $currentDate->copy();
                        $prevDate     = $currentDate->copy();

                        $segmentUnits = 1;

                        $segmentPaNo     = $pa['pa_no'];
                        $segmentPaExpiry = $pa['pa_end'];

                        $segmentRate = $currentRate;

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Is Current Date Consecutive?
                    |--------------------------------------------------------------------------
                    */

                    $expectedNext = $prevDate
                        ->copy()
                        ->addDay();

                    $isConsecutive =
                        $currentDate->equalTo($expectedNext);

                    /*
                    |--------------------------------------------------------------------------
                    | Same PA?
                    |--------------------------------------------------------------------------
                    */

                    $samePa =
                        trim((string) $segmentPaNo)
                        ===
                        trim((string) $pa['pa_no']);

                    $samePaExpiry =
                        (string) $segmentPaExpiry
                        ===
                        (string) $pa['pa_end'];

                    /*
                    |--------------------------------------------------------------------------
                    | Same Service Rate?
                    |--------------------------------------------------------------------------
                    */

                    $sameRate =
                        round((float) $segmentRate, 2)
                        ===
                        round((float) $currentRate, 2);

                    /*
                    |--------------------------------------------------------------------------
                    | Continue Existing Claim Segment
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $isConsecutive &&
                        $samePa &&
                        $samePaExpiry &&
                        $sameRate
                    ) {

                        $segmentEnd = $currentDate->copy();

                        $prevDate = $currentDate->copy();

                        $segmentUnits++;

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Something Changed
                    |--------------------------------------------------------------------------
                    |
                    | Close the previous claim segment.
                    |
                    */

                    $this->insertClaimLine(
                        $participantId,
                        $claimGroup,
                        $lineNo++,

                        $segmentStart,
                        $segmentEnd,

                        $segmentUnits,

                        $defaultProcCode,
                        $defaultModifier,

                        $segmentRate,

                        $insuranceName,

                        $payer_fullname,
                        $payer_payerid,
                        $payer_address1,
                        $payer_address2,
                        $payer_city,
                        $payer_state,
                        $payer_zip,

                        $insuranceId,

                        $segmentPaNo,
                        $segmentPaExpiry,

                        $diag1,
                        $diag2
                    );

                    $insertedCount++;

                    /*
                    |--------------------------------------------------------------------------
                    | Start New Segment
                    |--------------------------------------------------------------------------
                    */

                    $segmentStart = $currentDate->copy();
                    $segmentEnd   = $currentDate->copy();
                    $prevDate     = $currentDate->copy();

                    $segmentUnits = 1;

                    $segmentPaNo     = $pa['pa_no'];
                    $segmentPaExpiry = $pa['pa_end'];

                    $segmentRate = $currentRate;
                }

                /*
                |--------------------------------------------------------------------------
                | Flush Final Segment
                |--------------------------------------------------------------------------
                */

                if ($segmentStart !== null) {

                    $this->insertClaimLine(
                        $participantId,
                        $claimGroup,
                        $lineNo++,

                        $segmentStart,
                        $segmentEnd,

                        $segmentUnits,

                        $defaultProcCode,
                        $defaultModifier,

                        $segmentRate,

                        $insuranceName,

                        $payer_fullname,
                        $payer_payerid,
                        $payer_address1,
                        $payer_address2,
                        $payer_city,
                        $payer_state,
                        $payer_zip,

                        $insuranceId,

                        $segmentPaNo,
                        $segmentPaExpiry,

                        $diag1,
                        $diag2
                    );

                    $insertedCount++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'message',
                    'Error generating claims: '
                        . $e->getMessage()
                )
                ->with('alert-type', 'error');
        }

        /*
        |--------------------------------------------------------------------------
        | Finished
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'claims.attendance.form',
                $type
            )
            ->with(
                'message',
                "Generated {$insertedCount} claim line(s) from attendance."
            )
            ->with(
                'alert-type',
                'success'
            );
    } // End Method

    // public function excelImportGenerate(Request $request)
    // {
    //     $request->validate([
    //         'token' => [
    //             'required',
    //             'string',
    //         ],

    //         'selected_rows' => [
    //             'required',
    //             'array',
    //             'min:1',
    //         ],

    //         'selected_rows.*' => [
    //             'string',
    //         ],
    //     ]);

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Import State
    //     |--------------------------------------------------------------------------
    //     */

    //     $imports =
    //         session(
    //             'claims_excel_imports',
    //             []
    //         );

    //     if (
    //         !isset(
    //             $imports[$request->token]
    //         )
    //     ) {

    //         return redirect()
    //             ->route('claims.excel.form')
    //             ->with(
    //                 'message',
    //                 'Excel import session expired. Please upload again.'
    //             )
    //             ->with(
    //                 'alert-type',
    //                 'error'
    //             );
    //     }

    //     $state =
    //         $imports[$request->token];

    //     $storedPath =
    //         $state['stored_path'];

    //     if (
    //         !Storage::disk('local')
    //             ->exists(
    //                 $storedPath
    //             )
    //     ) {

    //         return redirect()
    //             ->route('claims.excel.form')
    //             ->with(
    //                 'message',
    //                 'Uploaded file was not found. Please upload again.'
    //             )
    //             ->with(
    //                 'alert-type',
    //                 'error'
    //             );
    //     }

    //     $startDate =
    //         Carbon::parse(
    //             $state['start_date']
    //         )->startOfDay();

    //     $endDate =
    //         Carbon::parse(
    //             $state['end_date']
    //         )->startOfDay();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Rebuild Current Preview
    //     |--------------------------------------------------------------------------
    //     */

    //     $fullPath =
    //         Storage::disk('local')
    //         ->path(
    //             $storedPath
    //         );

    //     $sheets =
    //         Excel::toArray(
    //             [],
    //             $fullPath
    //         );

    //     $previewRows =
    //         $this->buildExcelClaimPreview(
    //             $sheets[0],
    //             $startDate,
    //             $endDate,
    //             $state['icd_overrides']
    //                 ?? []
    //         );

    //     $selectedKeys =
    //         collect(
    //             $request->selected_rows
    //         );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Selected Valid Rows
    //     |--------------------------------------------------------------------------
    //     */

    //     $selectedRows =
    //         collect(
    //             $previewRows
    //         )
    //         ->filter(
    //             function ($row)
    //             use ($selectedKeys) {

    //                 return
    //                     $row['valid'] === true
    //                     &&
    //                     $selectedKeys
    //                     ->contains(
    //                         $row['key']
    //                     );
    //             }
    //         );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Invalid PA Rows To Preserve For Rebilling
    //     |--------------------------------------------------------------------------
    //     |
    //     | These rows are NOT inserted into claims yet.
    //     |
    //     | They are saved in claim_pending_rebill so they can be reviewed and
    //     | revalidated after a new PA is received from the insurance company.
    //     |--------------------------------------------------------------------------
    //     */

    //     $pendingPaRows =
    //         collect(
    //             $previewRows
    //         )
    //         ->filter(
    //             function ($row) {

    //                 if (
    //                     !isset($row['valid'])
    //                     ||
    //                     $row['valid'] === true
    //                 ) {
    //                     return false;
    //                 }

    //                 if (
    //                     empty($row['member_found'])
    //                     ||
    //                     empty($row['service_from'])
    //                     ||
    //                     empty($row['service_to'])
    //                 ) {
    //                     return false;
    //                 }

    //                 $message =
    //                     trim(
    //                         (string) (
    //                             $row['message']
    //                             ?? ''
    //                         )
    //                     );

    //                 return
    //                     stripos(
    //                         $message,
    //                         'No valid PA'
    //                     ) !== false;
    //             }
    //         )
    //         ->values();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Nothing To Process
    //     |--------------------------------------------------------------------------
    //     */

    //     if (
    //         $selectedRows->isEmpty()
    //         &&
    //         $pendingPaRows->isEmpty()
    //     ) {

    //         return back()
    //             ->with(
    //                 'message',
    //                 'No valid claim rows were selected and no PA-pending rows were found.'
    //             )
    //             ->with(
    //                 'alert-type',
    //                 'warning'
    //             );
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Load Members
    //     |--------------------------------------------------------------------------
    //     */

    //     $members = [];

    //     DB::table('billing_members')
    //         ->get()
    //         ->each(
    //             function ($member)
    //             use (&$members) {

    //                 $key =
    //                     $this
    //                     ->canonicalParticipantId(
    //                         $member
    //                             ->ParticipantId
    //                     );

    //                 $members[$key] =
    //                     $member;
    //             }
    //         );

    //     $insertedCount =
    //         0;

    //     $skippedCount =
    //         0;

    //     $pendingSavedCount =
    //         0;

    //     $pendingExistingCount =
    //         0;

    //     DB::beginTransaction();

    //     try {

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Save PA-Invalid Rows Into Pending Rebill Queue
    //         |--------------------------------------------------------------------------
    //         */

    //         foreach (
    //             $pendingPaRows
    //             as $pendingRow
    //         ) {

    //             $canonicalId =
    //                 $this
    //                 ->canonicalParticipantId(
    //                     $pendingRow['participant_id']
    //                 );

    //             $member =
    //                 $members[$canonicalId]
    //                 ?? null;

    //             if (!$member) {
    //                 continue;
    //             }

    //             $insuranceName =
    //                 $member
    //                 ->HMO_Enrolled
    //                 ?? null;

    //             $insuranceId =
    //                 $member
    //                 ->medicaid_HMO_id
    //                 ?? null;

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Snapshot Current PA Information
    //             |--------------------------------------------------------------------------
    //             |
    //             | For an expired PA row buildExcelClaimSegments() correctly returns
    //             | prior_auth_no = NULL because no PA covers the service date.
    //             |
    //             | For audit/reference, preserve the member's most recent PA number
    //             | and expiration date when available.
    //             |--------------------------------------------------------------------------
    //             */

    //             $snapshotPaNo =
    //                 null;

    //             $snapshotPaExpiry =
    //                 null;

    //             $paCandidates =
    //                 [];

    //             if (
    //                 !empty($member
    //                     ->medicaid_PA_no)
    //                 &&
    //                 !empty($member
    //                     ->medicaid_PA_End)
    //             ) {

    //                 try {

    //                     $paCandidates[] = [
    //                         'pa_no' =>
    //                         trim(
    //                             (string)
    //                             $member
    //                                 ->medicaid_PA_no
    //                         ),

    //                         'pa_end' =>
    //                         Carbon::parse(
    //                             $member
    //                                 ->medicaid_PA_End
    //                         )
    //                             ->toDateString(),
    //                     ];
    //                 } catch (\Throwable $e) {
    //                     // Ignore malformed stored PA date.
    //                 }
    //             }

    //             if (
    //                 !empty($member
    //                     ->renew1_PA_no)
    //                 &&
    //                 !empty($member
    //                     ->renew1_PA_End)
    //             ) {

    //                 try {

    //                     $paCandidates[] = [
    //                         'pa_no' =>
    //                         trim(
    //                             (string)
    //                             $member
    //                                 ->renew1_PA_no
    //                         ),

    //                         'pa_end' =>
    //                         Carbon::parse(
    //                             $member
    //                                 ->renew1_PA_End
    //                         )
    //                             ->toDateString(),
    //                     ];
    //                 } catch (\Throwable $e) {
    //                     // Ignore malformed stored PA date.
    //                 }
    //             }

    //             if (!empty($paCandidates)) {

    //                 usort(
    //                     $paCandidates,
    //                     function ($a, $b) {

    //                         return strcmp(
    //                             (string) $b['pa_end'],
    //                             (string) $a['pa_end']
    //                         );
    //                     }
    //                 );

    //                 $snapshotPaNo =
    //                     $paCandidates[0]['pa_no']
    //                     ?? null;

    //                 $snapshotPaExpiry =
    //                     $paCandidates[0]['pa_end']
    //                     ?? null;
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Do Not Duplicate An Existing Pending PA Row
    //             |--------------------------------------------------------------------------
    //             |
    //             | Re-uploading the same Excel file should not create another pending
    //             | record for the same member/date/procedure/modifier.
    //             |--------------------------------------------------------------------------
    //             */

    //             $existingPending =
    //                 DB::table(
    //                     'claim_pending_rebill'
    //                 )
    //                 ->where(
    //                     'ParticipantId',
    //                     $member
    //                         ->ParticipantId
    //                 )
    //                 ->whereDate(
    //                     'service_from',
    //                     $pendingRow['service_from']
    //                 )
    //                 ->whereDate(
    //                     'service_to',
    //                     $pendingRow['service_to']
    //                 )
    //                 ->where(
    //                     'proc_code',
    //                     $pendingRow['proc_code']
    //                 )
    //                 ->where(
    //                     'modifier',
    //                     $pendingRow['modifier']
    //                 )
    //                 ->where(
    //                     'error_type',
    //                     'PA'
    //                 )
    //                 ->where(
    //                     'status',
    //                     'pending'
    //                 )
    //                 ->first();

    //             $pendingData = [

    //                 'source_type' =>
    //                 'excel',

    //                 'source_token' =>
    //                 $request
    //                     ->token,

    //                 'source_row_key' =>
    //                 (string) (
    //                     $pendingRow['key']
    //                     ?? ''
    //                 ),

    //                 'ParticipantId' =>
    //                 $member
    //                     ->ParticipantId,

    //                 'insurance_name' =>
    //                 $insuranceName,

    //                 'insurance_id' =>
    //                 $insuranceId,

    //                 'service_from' =>
    //                 $pendingRow['service_from'],

    //                 'service_to' =>
    //                 $pendingRow['service_to'],

    //                 'proc_code' =>
    //                 $pendingRow['proc_code'],

    //                 'modifier' =>
    //                 $pendingRow['modifier'],

    //                 'units' =>
    //                 (int) (
    //                     $pendingRow['units']
    //                     ?? 0
    //                 ),

    //                 'amount_per_unit' =>
    //                 $pendingRow['amount_per_unit'] !== null

    //                     ? round(
    //                         (float)
    //                         $pendingRow['amount_per_unit'],
    //                         2
    //                     )

    //                     : null,

    //                 'total_amount' =>
    //                 $pendingRow['total_amount'] !== null

    //                     ? round(
    //                         (float)
    //                         $pendingRow['total_amount'],
    //                         2
    //                     )

    //                     : null,

    //                 'diag1' =>
    //                 $pendingRow['diag1']
    //                     ?? null,

    //                 'diag2' =>
    //                 $pendingRow['diag2']
    //                     ?? null,

    //                 'prior_auth_no' =>
    //                 $pendingRow['prior_auth_no']
    //                     ?? $snapshotPaNo,

    //                 'prior_auth_expiry' =>
    //                 $pendingRow['prior_auth_expiry']
    //                     ?? $snapshotPaExpiry,

    //                 'error_type' =>
    //                 'PA',

    //                 'error_message' =>
    //                 Str::limit(
    //                     trim(
    //                         (string) (
    //                             $pendingRow['message']
    //                             ?? 'No valid PA.'
    //                         )
    //                     ),
    //                     255,
    //                     ''
    //                 ),

    //                 'updated_at' =>
    //                 now(),
    //             ];

    //             if ($existingPending) {

    //                 DB::table(
    //                     'claim_pending_rebill'
    //                 )
    //                     ->where(
    //                         'id',
    //                         $existingPending
    //                             ->id
    //                     )
    //                     ->update(
    //                         $pendingData
    //                     );

    //                 $pendingExistingCount++;
    //             } else {

    //                 $pendingData['status'] =
    //                     'pending';

    //                 $pendingData['resolved_claim_id'] =
    //                     null;

    //                 $pendingData['resolved_at'] =
    //                     null;

    //                 $pendingData['created_at'] =
    //                     now();

    //                 DB::table(
    //                     'claim_pending_rebill'
    //                 )
    //                     ->insert(
    //                         $pendingData
    //                     );

    //                 $pendingSavedCount++;
    //             }
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Generate Selected Valid Claims
    //         |--------------------------------------------------------------------------
    //         */

    //         $grouped =
    //             $selectedRows
    //             ->groupBy(
    //                 'participant_id'
    //             );

    //         foreach (
    //             $grouped as
    //             $participantId => $claimRows
    //         ) {

    //             $canonicalId =
    //                 $this
    //                 ->canonicalParticipantId(
    //                     $participantId
    //                 );

    //             $member =
    //                 $members[$canonicalId]
    //                 ?? null;

    //             if (!$member) {
    //                 continue;
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Insurance
    //             |--------------------------------------------------------------------------
    //             */

    //             $insuranceName =
    //                 $member
    //                 ->HMO_Enrolled
    //                 ?? null;

    //             $insuranceId =
    //                 $member
    //                 ->medicaid_HMO_id
    //                 ?? null;

    //             $insurance =
    //                 null;

    //             if (
    //                 !empty($insuranceName)
    //             ) {

    //                 $insurance =
    //                     DB::table(
    //                         'insurance_companies'
    //                     )
    //                     ->where(
    //                         'code',
    //                         $insuranceName
    //                     )
    //                     ->first();
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Claim Group
    //             |--------------------------------------------------------------------------
    //             */

    //             $claimGroup =
    //                 $member
    //                 ->ParticipantId
    //                 . '-XL-'
    //                 . now()
    //                 ->format(
    //                     'YmdHis'
    //                 );

    //             $lineNo =
    //                 1;

    //             foreach (
    //                 $claimRows as $claimRow
    //             ) {

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Duplicate Guard
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $duplicate =
    //                     DB::table('claims')
    //                     ->where(
    //                         'ParticipantId',
    //                         $member
    //                             ->ParticipantId
    //                     )
    //                     ->whereDate(
    //                         'service_from',
    //                         $claimRow['service_from']
    //                     )
    //                     ->whereDate(
    //                         'service_to',
    //                         $claimRow['service_to']
    //                     )
    //                     ->where(
    //                         'proc_code',
    //                         $claimRow['proc_code']
    //                     )
    //                     ->where(
    //                         'm1',
    //                         $claimRow['modifier']
    //                     )
    //                     ->whereNull(
    //                         'claim_no'
    //                     )
    //                     ->exists();

    //                 if ($duplicate) {

    //                     $skippedCount++;
    //                     continue;
    //                 }

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Insert Claim
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $this->insertClaimLine(

    //                     $member
    //                         ->ParticipantId,

    //                     $claimGroup,

    //                     $lineNo++,

    //                     Carbon::parse(
    //                         $claimRow['service_from']
    //                     ),

    //                     Carbon::parse(
    //                         $claimRow['service_to']
    //                     ),

    //                     (int)
    //                     $claimRow['units'],

    //                     $claimRow['proc_code'],

    //                     $claimRow['modifier'],

    //                     (float)
    //                     $claimRow['amount_per_unit'],

    //                     $insuranceName,

    //                     $insurance
    //                         ->full_name
    //                         ?? null,

    //                     $insurance
    //                         ->payer_id
    //                         ?? null,

    //                     $insurance
    //                         ->address1
    //                         ?? null,

    //                     $insurance
    //                         ->address2
    //                         ?? null,

    //                     $insurance
    //                         ->city
    //                         ?? null,

    //                     $insurance
    //                         ->state
    //                         ?? null,

    //                     $insurance
    //                         ->zip
    //                         ?? null,

    //                     $insuranceId,

    //                     $claimRow['prior_auth_no'],

    //                     $claimRow['prior_auth_expiry'],

    //                     $claimRow['diag1'],

    //                     $claimRow['diag2']
    //                 );

    //                 $insertedCount++;
    //             }
    //         }

    //         DB::commit();
    //     } catch (\Throwable $e) {

    //         DB::rollBack();

    //         return back()
    //             ->with(
    //                 'message',
    //                 'Error generating claims: '
    //                     . $e->getMessage()
    //             )
    //             ->with(
    //                 'alert-type',
    //                 'error'
    //             );
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Cleanup
    //     |--------------------------------------------------------------------------
    //     */

    //     Storage::disk('local')
    //         ->delete(
    //             $storedPath
    //         );

    //     unset(
    //         $imports[$request->token]
    //     );

    //     session([
    //         'claims_excel_imports'
    //         =>
    //         $imports
    //     ]);

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Success Message
    //     |--------------------------------------------------------------------------
    //     */

    //     $message =
    //         "Generated {$insertedCount} claim line(s). "
    //         . "{$skippedCount} duplicate line(s) skipped. "
    //         . "{$pendingSavedCount} PA-pending row(s) saved for rebilling.";

    //     if ($pendingExistingCount > 0) {

    //         $message .=
    //             " {$pendingExistingCount} existing PA-pending row(s) were refreshed.";
    //     }

    //     return redirect()
    //         ->route(
    //             'claims.draft.index'
    //         )
    //         ->with(
    //             'message',
    //             $message
    //         )
    //         ->with(
    //             'alert-type',
    //             'success'
    //         );
    // } // End Method

    public function excelImportGenerate(Request $request)
    {
        $request->validate([
            'token' => [
                'required',
                'string',
            ],

            'selected_rows' => [
                'nullable',
                'array',
            ],

            'selected_rows.*' => [
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Import State
        |--------------------------------------------------------------------------
        */

        $imports = session(
            'claims_excel_imports',
            []
        );

        if (
            !isset(
                $imports[$request->token]
            )
        ) {

            return redirect()
                ->route('claims.excel.form')
                ->with(
                    'message',
                    'Excel import session expired. Please upload again.'
                )
                ->with(
                    'alert-type',
                    'error'
                );
        }

        $state =
            $imports[$request->token];

        $storedPath =
            $state['stored_path'];

        if (
            !Storage::disk('local')
                ->exists(
                    $storedPath
                )
        ) {

            return redirect()
                ->route('claims.excel.form')
                ->with(
                    'message',
                    'Uploaded file was not found. Please upload again.'
                )
                ->with(
                    'alert-type',
                    'error'
                );
        }

        $startDate =
            Carbon::parse(
                $state['start_date']
            )->startOfDay();

        $endDate =
            Carbon::parse(
                $state['end_date']
            )->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Rebuild Current Preview
        |--------------------------------------------------------------------------
        */

        $fullPath =
            Storage::disk('local')
            ->path(
                $storedPath
            );

        $sheets =
            Excel::toArray(
                [],
                $fullPath
            );

        $previewRows =
            $this->buildExcelClaimPreview(
                $sheets[0],
                $startDate,
                $endDate,
                $state['icd_overrides']
                    ?? []
            );

        $selectedKeys =
            collect(
                $request->input(
                    'selected_rows',
                    []
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Selected Valid Rows
        |--------------------------------------------------------------------------
        |
        | Only valid rows selected by the user are inserted into claims.
        |
        */

        $selectedRows =
            collect(
                $previewRows
            )
            ->filter(
                function ($row)
                use ($selectedKeys) {

                    return
                        isset($row['valid'])
                        &&
                        $row['valid'] === true
                        &&
                        $selectedKeys
                        ->contains(
                            $row['key']
                        );
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Invalid Rows To Preserve For Rebilling
        |--------------------------------------------------------------------------
        |
        | Previously only "No valid PA" rows were preserved.
        |
        | We now preserve all recoverable invalid rows where:
        |
        | 1. The row is invalid.
        | 2. The member was found.
        | 3. Service From exists.
        | 4. Service To exists.
        |
        | These rows are NOT inserted into claims yet.
        |
        | They are saved into claim_pending_rebill and can later be revalidated
        | after PA, diagnosis, member, insurance or other information is fixed.
        |
        |--------------------------------------------------------------------------
        */

        $pendingInvalidRows =
            collect(
                $previewRows
            )
            ->filter(
                function ($row) {

                    /*
                        |--------------------------------------------------------------------------
                        | Ignore Valid Rows
                        |--------------------------------------------------------------------------
                        */

                    if (
                        !isset($row['valid'])
                        ||
                        $row['valid'] === true
                    ) {
                        return false;
                    }

                    /*
                        |--------------------------------------------------------------------------
                        | Minimum Information Required For Pending Rebill
                        |--------------------------------------------------------------------------
                        |
                        | claim_pending_rebill requires a known member and valid
                        | service dates.
                        |
                        */

                    if (
                        empty($row['member_found'])
                        ||
                        empty($row['service_from'])
                        ||
                        empty($row['service_to'])
                    ) {
                        return false;
                    }

                    return true;
                }
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Nothing To Process
        |--------------------------------------------------------------------------
        */

        if (
            $selectedRows->isEmpty()
            &&
            $pendingInvalidRows->isEmpty()
        ) {

            return back()
                ->with(
                    'message',
                    'No valid claim rows were selected and no invalid rows were available for pending rebill.'
                )
                ->with(
                    'alert-type',
                    'warning'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Load Members
        |--------------------------------------------------------------------------
        */

        $members = [];

        DB::table('billing_members')
            ->get()
            ->each(
                function ($member)
                use (&$members) {

                    $key =
                        $this
                        ->canonicalParticipantId(
                            $member->ParticipantId
                        );

                    $members[$key] =
                        $member;
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Counters
        |--------------------------------------------------------------------------
        */

        $insertedCount = 0;

        $skippedCount = 0;

        $pendingSavedCount = 0;

        $pendingExistingCount = 0;

        /*
        |--------------------------------------------------------------------------
        | Error Type Classifier
        |--------------------------------------------------------------------------
        |
        | Convert the validation message into a simple error category.
        |
        | The full original validation message is still stored in error_message.
        |
        |--------------------------------------------------------------------------
        */

        $classifyPendingError =
            function ($message) {

                $message =
                    strtolower(
                        trim(
                            (string) $message
                        )
                    );

                $types = [];

                /*
            |--------------------------------------------------------------------------
            | Diagnosis / ICD
            |--------------------------------------------------------------------------
            */

                if (
                    str_contains($message, 'diagnosis')
                    ||
                    str_contains($message, 'diag')
                    ||
                    str_contains($message, 'icd')
                ) {
                    $types[] = 'DIAGNOSIS';
                }

                /*
            |--------------------------------------------------------------------------
            | Prior Authorization
            |--------------------------------------------------------------------------
            */

                if (
                    str_contains($message, 'no valid pa')
                    ||
                    str_contains($message, 'prior auth')
                    ||
                    str_contains($message, 'prior authorization')
                    ||
                    str_contains($message, 'pa expired')
                    ||
                    str_contains($message, 'authorization expired')
                ) {
                    $types[] = 'PA';
                }

                /*
            |--------------------------------------------------------------------------
            | Insurance / Payer
            |--------------------------------------------------------------------------
            */

                if (
                    str_contains($message, 'insurance')
                    ||
                    str_contains($message, 'payer')
                    ||
                    str_contains($message, 'hmo')
                ) {
                    $types[] = 'INSURANCE';
                }

                /*
            |--------------------------------------------------------------------------
            | Member Information
            |--------------------------------------------------------------------------
            */

                if (
                    str_contains($message, 'address')
                    ||
                    str_contains($message, 'city')
                    ||
                    str_contains($message, 'state')
                    ||
                    str_contains($message, 'zip')
                    ||
                    str_contains($message, 'date of birth')
                    ||
                    str_contains($message, 'dob')
                    ||
                    str_contains($message, 'member')
                    ||
                    str_contains($message, 'participant')
                ) {
                    $types[] = 'MEMBER';
                }

                /*
            |--------------------------------------------------------------------------
            | Units / Amount
            |--------------------------------------------------------------------------
            */

                if (
                    str_contains($message, 'unit')
                    ||
                    str_contains($message, 'amount')
                    ||
                    str_contains($message, 'charge')
                ) {
                    $types[] = 'AMOUNT';
                }

                /*
            |--------------------------------------------------------------------------
            | Procedure
            |--------------------------------------------------------------------------
            */

                if (
                    str_contains($message, 'procedure')
                    ||
                    str_contains($message, 'proc code')
                    ||
                    str_contains($message, 'cpt')
                ) {
                    $types[] = 'PROCEDURE';
                }

                /*
            |--------------------------------------------------------------------------
            | Service Date
            |--------------------------------------------------------------------------
            */

                if (
                    str_contains($message, 'service date')
                    ||
                    str_contains($message, 'future date')
                    ||
                    str_contains($message, 'service from')
                    ||
                    str_contains($message, 'service to')
                    ||
                    str_contains($message, 'from date')
                    ||
                    str_contains($message, 'to date')
                ) {
                    $types[] = 'SERVICE_DATE';
                }

                /*
            |--------------------------------------------------------------------------
            | Remove Duplicates
            |--------------------------------------------------------------------------
            */

                $types =
                    array_values(
                        array_unique(
                            $types
                        )
                    );

                /*
            |--------------------------------------------------------------------------
            | Unknown Validation Error
            |--------------------------------------------------------------------------
            */

                if (empty($types)) {
                    return 'OTHER';
                }

                /*
            |--------------------------------------------------------------------------
            | One Or Multiple Validation Errors
            |--------------------------------------------------------------------------
            */

                return implode(
                    '+',
                    $types
                );
            };
        /*
        |--------------------------------------------------------------------------
        | Begin Transaction
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Save Invalid Rows Into Pending Rebill Queue
            |--------------------------------------------------------------------------
            */

            foreach (
                $pendingInvalidRows
                as $pendingRow
            ) {

                $canonicalId =
                    $this
                    ->canonicalParticipantId(
                        $pendingRow['participant_id']
                    );

                $member =
                    $members[$canonicalId]
                    ?? null;

                /*
                |--------------------------------------------------------------------------
                | Safety Guard
                |--------------------------------------------------------------------------
                */

                if (!$member) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Insurance Information
                |--------------------------------------------------------------------------
                */

                $insuranceName =
                    $member->HMO_Enrolled
                    ?? null;

                $insuranceId =
                    $member->medicaid_HMO_id
                    ?? null;

                /*
                |--------------------------------------------------------------------------
                | Determine Error Type
                |--------------------------------------------------------------------------
                */

                $validationMessage =
                    trim(
                        (string) (
                            $pendingRow['message']
                            ?? 'Claim validation failed.'
                        )
                    );

                $errorType =
                    $classifyPendingError(
                        $validationMessage
                    );

                /*
                |--------------------------------------------------------------------------
                | Snapshot Current PA Information
                |--------------------------------------------------------------------------
                |
                | For an expired PA row buildExcelClaimSegments() can return
                | prior_auth_no = NULL because no PA covers the service date.
                |
                | Preserve the member's most recent PA number and expiration date
                | for audit/reference when available.
                |
                | This snapshot is also harmless for non-PA errors and gives us
                | complete claim information when the row is reviewed later.
                |
                |--------------------------------------------------------------------------
                */

                $snapshotPaNo = null;

                $snapshotPaExpiry = null;

                $paCandidates = [];

                /*
                |--------------------------------------------------------------------------
                | Original Medicaid PA
                |--------------------------------------------------------------------------
                */

                if (
                    !empty($member->medicaid_PA_no)
                    &&
                    !empty($member->medicaid_PA_End)
                ) {

                    try {

                        $paCandidates[] = [

                            'pa_no' =>
                            trim(
                                (string)
                                $member->medicaid_PA_no
                            ),

                            'pa_end' =>
                            Carbon::parse(
                                $member->medicaid_PA_End
                            )->toDateString(),
                        ];
                    } catch (\Throwable $e) {

                        /*
                        | Ignore malformed stored PA date.
                        */
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Renewal PA
                |--------------------------------------------------------------------------
                */

                if (
                    !empty($member->renew1_PA_no)
                    &&
                    !empty($member->renew1_PA_End)
                ) {

                    try {

                        $paCandidates[] = [

                            'pa_no' =>
                            trim(
                                (string)
                                $member->renew1_PA_no
                            ),

                            'pa_end' =>
                            Carbon::parse(
                                $member->renew1_PA_End
                            )->toDateString(),
                        ];
                    } catch (\Throwable $e) {

                        /*
                        | Ignore malformed stored PA date.
                        */
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Select Most Recent PA
                |--------------------------------------------------------------------------
                */

                if (
                    !empty($paCandidates)
                ) {

                    usort(
                        $paCandidates,
                        function ($a, $b) {

                            return strcmp(
                                (string) $b['pa_end'],
                                (string) $a['pa_end']
                            );
                        }
                    );

                    $snapshotPaNo =
                        $paCandidates[0]['pa_no']
                        ?? null;

                    $snapshotPaExpiry =
                        $paCandidates[0]['pa_end']
                        ?? null;
                }

                /*
                |--------------------------------------------------------------------------
                | Do Not Duplicate Existing Pending Claim
                |--------------------------------------------------------------------------
                |
                | We intentionally DO NOT use error_type as part of the duplicate
                | test anymore.
                |
                | Example:
                |
                | First import:
                |   Claim fails because PA expired.
                |
                | Later import:
                |   PA is fixed but diagnosis is now invalid.
                |
                | That should update the SAME pending claim instead of creating
                | another pending record for the same service line.
                |
                |--------------------------------------------------------------------------
                */

                $existingPending =
                    DB::table(
                        'claim_pending_rebill'
                    )
                    ->where(
                        'ParticipantId',
                        $member->ParticipantId
                    )
                    ->whereDate(
                        'service_from',
                        $pendingRow['service_from']
                    )
                    ->whereDate(
                        'service_to',
                        $pendingRow['service_to']
                    )
                    ->where(
                        'proc_code',
                        $pendingRow['proc_code']
                    )
                    ->where(
                        'modifier',
                        $pendingRow['modifier']
                    )
                    ->where(
                        'status',
                        'pending'
                    )
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | Pending Claim Data
                |--------------------------------------------------------------------------
                */

                $pendingData = [

                    'source_type' =>
                    'excel',

                    'source_token' =>
                    $request->token,

                    'source_row_key' =>
                    (string) (
                        $pendingRow['key']
                        ?? ''
                    ),

                    'ParticipantId' =>
                    $member->ParticipantId,

                    'insurance_name' =>
                    $insuranceName,

                    'insurance_id' =>
                    $insuranceId,

                    'service_from' =>
                    $pendingRow['service_from'],

                    'service_to' =>
                    $pendingRow['service_to'],

                    'proc_code' =>
                    $pendingRow['proc_code']
                        ?? null,

                    'modifier' =>
                    $pendingRow['modifier']
                        ?? null,

                    'units' =>
                    (int) (
                        $pendingRow['units']
                        ?? 0
                    ),

                    'amount_per_unit' =>
                    isset(
                        $pendingRow['amount_per_unit']
                    )
                        &&
                        $pendingRow['amount_per_unit'] !== null

                        ? round(
                            (float)
                            $pendingRow['amount_per_unit'],
                            2
                        )

                        : null,

                    'total_amount' =>
                    isset(
                        $pendingRow['total_amount']
                    )
                        &&
                        $pendingRow['total_amount'] !== null

                        ? round(
                            (float)
                            $pendingRow['total_amount'],
                            2
                        )

                        : null,

                    'diag1' =>
                    $pendingRow['diag1']
                        ?? null,

                    'diag2' =>
                    $pendingRow['diag2']
                        ?? null,

                    'prior_auth_no' =>
                    $pendingRow['prior_auth_no']
                        ?? $snapshotPaNo,

                    'prior_auth_expiry' =>
                    $pendingRow['prior_auth_expiry']
                        ?? $snapshotPaExpiry,

                    'error_type' =>
                    $errorType,

                    'error_message' =>
                    Str::limit(
                        $validationMessage,
                        255,
                        ''
                    ),

                    'updated_at' =>
                    now(),
                ];

                /*
                |--------------------------------------------------------------------------
                | Refresh Existing Pending Row
                |--------------------------------------------------------------------------
                */

                if ($existingPending) {

                    DB::table(
                        'claim_pending_rebill'
                    )
                        ->where(
                            'id',
                            $existingPending->id
                        )
                        ->update(
                            $pendingData
                        );

                    $pendingExistingCount++;
                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Insert New Pending Row
                    |--------------------------------------------------------------------------
                    */

                    $pendingData['status'] =
                        'pending';

                    $pendingData['resolved_claim_id'] =
                        null;

                    $pendingData['resolved_at'] =
                        null;

                    $pendingData['created_at'] =
                        now();

                    DB::table(
                        'claim_pending_rebill'
                    )
                        ->insert(
                            $pendingData
                        );

                    $pendingSavedCount++;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Generate Selected Valid Claims
            |--------------------------------------------------------------------------
            |
            | Existing working claim generation remains unchanged.
            |
            |--------------------------------------------------------------------------
            */

            $grouped =
                $selectedRows
                ->groupBy(
                    'participant_id'
                );

            foreach (
                $grouped as
                $participantId => $claimRows
            ) {

                $canonicalId =
                    $this
                    ->canonicalParticipantId(
                        $participantId
                    );

                $member =
                    $members[$canonicalId]
                    ?? null;

                if (!$member) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Insurance
                |--------------------------------------------------------------------------
                */

                $insuranceName =
                    $member->HMO_Enrolled
                    ?? null;

                $insuranceId =
                    $member->medicaid_HMO_id
                    ?? null;

                $insurance = null;

                if (
                    !empty($insuranceName)
                ) {

                    $insurance =
                        DB::table(
                            'insurance_companies'
                        )
                        ->where(
                            'code',
                            $insuranceName
                        )
                        ->first();
                }

                /*
                |--------------------------------------------------------------------------
                | Claim Group
                |--------------------------------------------------------------------------
                */

                $claimGroup =
                    $member->ParticipantId
                    . '-XL-'
                    . now()->format(
                        'YmdHis'
                    );

                $lineNo = 1;

                foreach (
                    $claimRows as $claimRow
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Duplicate Guard
                    |--------------------------------------------------------------------------
                    */

                    $duplicate =
                        DB::table('claims')
                        ->where(
                            'ParticipantId',
                            $member->ParticipantId
                        )
                        ->whereDate(
                            'service_from',
                            $claimRow['service_from']
                        )
                        ->whereDate(
                            'service_to',
                            $claimRow['service_to']
                        )
                        ->where(
                            'proc_code',
                            $claimRow['proc_code']
                        )
                        ->where(
                            'm1',
                            $claimRow['modifier']
                        )
                        ->whereNull(
                            'claim_no'
                        )
                        ->exists();

                    if ($duplicate) {

                        $skippedCount++;

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Insert Claim
                    |--------------------------------------------------------------------------
                    */

                    $this->insertClaimLine(

                        $member->ParticipantId,

                        $claimGroup,

                        $lineNo++,

                        Carbon::parse(
                            $claimRow['service_from']
                        ),

                        Carbon::parse(
                            $claimRow['service_to']
                        ),

                        (int)
                        $claimRow['units'],

                        $claimRow['proc_code'],

                        $claimRow['modifier'],

                        (float)
                        $claimRow['amount_per_unit'],

                        $insuranceName,

                        $insurance->full_name
                            ?? null,

                        $insurance->payer_id
                            ?? null,

                        $insurance->address1
                            ?? null,

                        $insurance->address2
                            ?? null,

                        $insurance->city
                            ?? null,

                        $insurance->state
                            ?? null,

                        $insurance->zip
                            ?? null,

                        $insuranceId,

                        $claimRow['prior_auth_no'],

                        $claimRow['prior_auth_expiry'],

                        $claimRow['diag1'],

                        $claimRow['diag2']
                    );

                    $insertedCount++;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            DB::commit();
        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Rollback
            |--------------------------------------------------------------------------
            */

            DB::rollBack();

            return back()
                ->with(
                    'message',
                    'Error generating claims: '
                        . $e->getMessage()
                )
                ->with(
                    'alert-type',
                    'error'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Cleanup
        |--------------------------------------------------------------------------
        */

        Storage::disk('local')
            ->delete(
                $storedPath
            );

        unset(
            $imports[$request->token]
        );

        session([
            'claims_excel_imports'
            =>
            $imports
        ]);

        /*
        |--------------------------------------------------------------------------
        | Success Message
        |--------------------------------------------------------------------------
        */

        $message =
            "Generated {$insertedCount} claim line(s). "
            . "{$skippedCount} duplicate line(s) skipped. "
            . "{$pendingSavedCount} invalid row(s) saved for pending rebill.";

        if (
            $pendingExistingCount > 0
        ) {

            $message .=
                " {$pendingExistingCount} existing pending row(s) were refreshed.";
        }

        return redirect()
            ->route(
                'claims.draft.index'
            )
            ->with(
                'message',
                $message
            )
            ->with(
                'alert-type',
                'success'
            );
    } // End Method


    private function getPriorAuthorizationForDate($member, $serviceDate): ?array
    {
        $date = Carbon::parse(
            $serviceDate
        )->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | 1. Renewal PA
        |--------------------------------------------------------------------------
        */

        if (
            !empty($member->renew1_PA_no) &&
            !empty($member->renew1_PA_Start) &&
            !empty($member->renew1_PA_End)
        ) {

            try {

                $start = Carbon::parse(
                    $member->renew1_PA_Start
                )->startOfDay();

                $end = Carbon::parse(
                    $member->renew1_PA_End
                )->startOfDay();

                if (
                    $date->gte($start) &&
                    $date->lte($end)
                ) {

                    return [
                        'source'   => 'renew1',
                        'pa_no'    => trim(
                            $member->renew1_PA_no
                        ),
                        'pa_start' => $start->toDateString(),
                        'pa_end'   => $end->toDateString(),
                    ];
                }
            } catch (\Throwable $e) {
                // Ignore bad renewal dates and try original PA.
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Original Medicaid PA
        |--------------------------------------------------------------------------
        */

        if (
            !empty($member->medicaid_PA_no) &&
            !empty($member->medicaid_PA_Start) &&
            !empty($member->medicaid_PA_End)
        ) {

            try {

                $start = Carbon::parse(
                    $member->medicaid_PA_Start
                )->startOfDay();

                $end = Carbon::parse(
                    $member->medicaid_PA_End
                )->startOfDay();

                if (
                    $date->gte($start) &&
                    $date->lte($end)
                ) {

                    return [
                        'source'   => 'medicaid',
                        'pa_no'    => trim(
                            $member->medicaid_PA_no
                        ),
                        'pa_start' => $start->toDateString(),
                        'pa_end'   => $end->toDateString(),
                    ];
                }
            } catch (\Throwable $e) {
                // Invalid original PA date.
            }
        }

        /*
        |--------------------------------------------------------------------------
        | No PA Covers This Date
        |--------------------------------------------------------------------------
        */

        return null;
    } // End Method

    private function getClaimRateForDate($serviceDate, string $procCode = 'S5102', string $modifier = 'U1'): ?object
    {
        $date = Carbon::parse($serviceDate)->toDateString();

        return DB::table('claim_service_rates')
            ->where('proc_code', $procCode)
            ->where('modifier', $modifier)
            ->where('active', 1)
            ->whereDate('effective_from', '<=', $date)
            ->whereDate('effective_to', '>=', $date)
            ->orderByDesc('effective_from')
            ->first();
    } // End Method

    private function splitDatesByClaimRate(array $attendanceDates, string $procCode = 'S5102', string $modifier = 'U1'): array
    {
        $segments = [];

        $currentSegment = null;

        foreach ($attendanceDates as $attendanceDate) {

            $date = Carbon::parse($attendanceDate)->startOfDay();

            $rate = $this->getClaimRateForDate(
                $date,
                $procCode,
                $modifier
            );

            if (!$rate) {
                throw new \Exception(
                    'No claim rate found for service date '
                        . $date->format('m/d/Y')
                );
            }

            /*
            |--------------------------------------------------------------------------
            | First attendance date
            |--------------------------------------------------------------------------
            */

            if ($currentSegment === null) {

                $currentSegment = [
                    'service_from'   => $date->copy(),
                    'service_to'     => $date->copy(),
                    'dates'          => [$date->copy()],
                    'units'          => 1,
                    'rate_id'        => $rate->id,
                    'amount_per_unit' => (float) $rate->amount_per_unit,
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Continue same segment only if rate is identical
            |--------------------------------------------------------------------------
            */

            if (
                (int) $currentSegment['rate_id']
                ===
                (int) $rate->id
            ) {

                $currentSegment['service_to'] = $date->copy();
                $currentSegment['dates'][] = $date->copy();
                $currentSegment['units']++;
            } else {

                /*
                |--------------------------------------------------------------------------
                | Rate changed — close previous segment
                |--------------------------------------------------------------------------
                */

                $segments[] = $currentSegment;

                /*
                |--------------------------------------------------------------------------
                | Start new rate segment
                |--------------------------------------------------------------------------
                */

                $currentSegment = [
                    'service_from'   => $date->copy(),
                    'service_to'     => $date->copy(),
                    'dates'          => [$date->copy()],
                    'units'          => 1,
                    'rate_id'        => $rate->id,
                    'amount_per_unit' => (float) $rate->amount_per_unit,
                ];
            }
        }

        if ($currentSegment !== null) {
            $segments[] = $currentSegment;
        }

        return $segments;
    } // End Method

    /**
     * Small helper to insert one claim line.
     */
    protected function insertClaimLine(
        string $participantId,
        string $claimGroup,
        int $lineNo,
        Carbon $serviceFrom,
        Carbon $serviceTo,
        int $units,
        string $procCode,
        string $modifier,
        float $amountPerUnit,
        ?string $insuranceName,
        ?string $payer_fullname,
        ?string $payer_payerid,
        ?string $payer_address1,
        ?string $payer_address2,
        ?string $payer_city,
        ?string $payer_state,
        ?string $payer_zip,
        ?string $insuranceId,
        ?string $priorAuthNo,
        ?string $priorAuthExpiry,
        ?string $diag1,
        ?string $diag2
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Calculate amounts
        |--------------------------------------------------------------------------
        */

        $totalAmount = round(
            $amountPerUnit * $units,
            2
        );

        DB::table('claims')->insert([

            'claim_no'            => null,
            'claim_group'         => $claimGroup,
            'line_no'             => $lineNo,

            'ParticipantId'       => $participantId,

            /*
            |--------------------------------------------------------------------------
            | Snapshot From Member / Insurance
            |--------------------------------------------------------------------------
            */

            'insurance_name'      => $insuranceName,

            'payer_fullname'      => $payer_fullname,
            'payer_id'            => $payer_payerid,

            'payer_address1'      => $payer_address1,
            'payer_address2'      => $payer_address2,
            'payer_city'          => $payer_city,
            'payer_state'         => $payer_state,
            'payer_zip'           => $payer_zip,

            'insurance_id'        => $insuranceId,

            /*
            |--------------------------------------------------------------------------
            | Prior Authorization
            |--------------------------------------------------------------------------
            */

            'prior_auth_no'       => $priorAuthNo,
            'prior_auth_expiry'   => $priorAuthExpiry,

            /*
            |--------------------------------------------------------------------------
            | Service Information
            |--------------------------------------------------------------------------
            */

            'service_from'        => $serviceFrom->toDateString(),
            'service_to'          => $serviceTo->toDateString(),

            'proc_code'           => $procCode,
            'm1'                  => $modifier,

            'units'               => $units,

            /*
            |--------------------------------------------------------------------------
            | Rate / Amounts
            |--------------------------------------------------------------------------
            */

            'amount_per_unit'     => round($amountPerUnit, 2),

            'total_amount'        => $totalAmount,

            'allowed'             => $totalAmount,

            'balance_amount'      => $totalAmount,

            /*
            |--------------------------------------------------------------------------
            | Diagnosis
            |--------------------------------------------------------------------------
            */

            'diag1'               => $diag1,
            'diag2'               => $diag2,

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'status'              => 'draft',

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
    } // End Method

    public function excelImportForm()
    {
        return view('claims.claims_excel_import');
    } // End Method.

    public function excelImportPreview(Request $request)
    {
        $request->validate([
            'billing_file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:10240',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
        ]);

        $startDate = Carbon::parse($request->start_date)->toDateString();
        $endDate   = Carbon::parse($request->end_date)->toDateString();

        $token = Str::uuid()->toString();

        $storedPath = $request->file('billing_file')->storeAs(
            'claims-imports',
            $token . '.xlsx',
            'local'
        );

        /*
        |--------------------------------------------------------------------------
        | Store import information in session
        |--------------------------------------------------------------------------
        */

        $imports = session('claims_excel_imports', []);

        $imports[$token] = [
            'stored_path'  => $storedPath,
            'start_date'   => $startDate,
            'end_date'     => $endDate,

            // Any ICD fixes made on Preview screen
            'icd_overrides' => [],
        ];

        session([
            'claims_excel_imports' => $imports
        ]);

        return redirect()->route(
            'claims.excel.preview.show',
            $token
        );
    } // End Method

    public function excelImportPreviewShow($token)
    {
        $imports = session('claims_excel_imports', []);

        if (!isset($imports[$token])) {

            return redirect()
                ->route('claims.excel.form')
                ->with(
                    'message',
                    'Excel import session was not found. Please upload the file again.'
                )
                ->with('alert-type', 'error');
        }

        $state = $imports[$token];

        $storedPath = $state['stored_path'];

        if (!Storage::disk('local')->exists($storedPath)) {

            return redirect()
                ->route('claims.excel.form')
                ->with(
                    'message',
                    'Uploaded Excel file was not found. Please upload it again.'
                )
                ->with('alert-type', 'error');
        }

        $startDate = Carbon::parse(
            $state['start_date']
        )->startOfDay();

        $endDate = Carbon::parse(
            $state['end_date']
        )->startOfDay();

        $fullPath = Storage::disk('local')->path(
            $storedPath
        );

        try {

            $sheets = Excel::toArray([], $fullPath);

            if (
                empty($sheets) ||
                empty($sheets[0]) ||
                count($sheets[0]) < 2
            ) {
                throw new \Exception(
                    'Excel file does not contain any data.'
                );
            }

            $previewRows = $this->buildExcelClaimPreview(
                $sheets[0],
                $startDate,
                $endDate,
                $state['icd_overrides'] ?? []
            );
        } catch (\Throwable $e) {

            return redirect()
                ->route('claims.excel.form')
                ->with(
                    'message',
                    'Unable to read billing Excel file: '
                        . $e->getMessage()
                )
                ->with('alert-type', 'error');
        }

        $readyCount = collect($previewRows)
            ->where('valid', true)
            ->count();

        $invalidCount = collect($previewRows)
            ->where('valid', false)
            ->count();

        return view(
            'claims.claims_excel_import_preview',
            [
                'token'        => $token,
                'startDate'    => $state['start_date'],
                'endDate'      => $state['end_date'],
                'previewRows'  => $previewRows,
                'readyCount'   => $readyCount,
                'invalidCount' => $invalidCount,
            ]
        );
    } // End Method

    private function buildExcelClaimPreview(array $sheet, Carbon $startDate, Carbon $endDate, array $icdOverrides = []): array

    {

        $headers = $sheet[0];

        $headerMap = [];

        foreach ($headers as $index => $header) {

            if ($header === null) {
                continue;
            }

            $headerMap[trim((string) $header)] = $index;
        }

        if (!array_key_exists('ID #', $headerMap)) {

            throw new \Exception(
                'Column "ID #" was not found.'
            );
        }

        $primaryDiagIndex =
            $headerMap['Primary ICD']
            ?? null;

        $secondaryDiagIndex =
            $headerMap['Secondary ICD']
            ?? $headerMap['Scondary ICD']
            ?? null;

        // /*
        // |--------------------------------------------------------------------------
        // | Build actual date mapping
        // |--------------------------------------------------------------------------
        // */

        // $dateColumns = [];

        // $cursor = $startDate->copy();

        // while ($cursor->lte($endDate)) {

        //     $dayWithZero =
        //         $cursor->format('d');

        //     $dayWithoutZero =
        //         (string) ((int) $cursor->format('d'));

        //     $columnIndex = null;

        //     foreach ($headers as $index => $header) {

        //         $headerValue =
        //             trim((string) $header);

        //         if (
        //             $headerValue === $dayWithZero ||
        //             $headerValue === $dayWithoutZero
        //         ) {

        //             $columnIndex = $index;
        //             break;
        //         }
        //     }

        //     if ($columnIndex === null) {

        //         throw new \Exception(
        //             'Attendance column for '
        //                 . $cursor->format('m/d/Y')
        //                 . ' was not found.'
        //         );
        //     }

        //     $dateColumns[$columnIndex] =
        //         $cursor->copy();

        //     $cursor->addDay();
        // }

        /*
        |--------------------------------------------------------------------------
        | Build Actual Attendance Date Mapping
        |--------------------------------------------------------------------------
        |
        | Attendance service-date columns do not have to be consecutive.
        |
        | Example:
        |
        | 14 | 15 | 17 | 18 | 19
        |
        | The facility may be closed on the 16th and use Saturday the 19th
        | as a replacement service day.
        |
        */

        $dateColumns = [];

        /*
        |--------------------------------------------------------------------------
        | Validate Selected Attendance Week
        |--------------------------------------------------------------------------
        |
        | Attendance week must always be:
        |
        | Sunday -> Saturday
        |
        */

        if (!$startDate->isSunday()) {

            throw new \Exception(
                'Attendance Start Date must be a Sunday.'
            );
        }

        $expectedEndDate = $startDate
            ->copy()
            ->addDays(6);

        if (!$endDate->isSameDay($expectedEndDate)) {

            throw new \Exception(
                'Attendance End Date must be the Saturday of the selected week.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Build Valid Dates For Selected Week
        |--------------------------------------------------------------------------
        |
        | Map each day number to its actual date.
        |
        | Example:
        |
        | 14 => 09/14/2026
        | 15 => 09/15/2026
        | 16 => 09/16/2026
        | etc.
        |
        */

        $weekDatesByDay = [];

        for ($offset = 0; $offset < 7; $offset++) {

            $date = $startDate
                ->copy()
                ->addDays($offset);

            $dayNumber = (int) $date->format('d');

            $weekDatesByDay[$dayNumber] =
                $date->copy();
        }

        /*
        |--------------------------------------------------------------------------
        | Locate Grand Total
        |--------------------------------------------------------------------------
        */

        $grandTotalIndex =
            $headerMap['Grand Total']
            ?? $headerMap['Total']
            ?? null;

        if ($grandTotalIndex === null) {

            throw new \Exception(
                'Column "Total / Grand Total" was not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Locate Beginning Of Attendance Columns
        |--------------------------------------------------------------------------
        |
        | Final ADC file structure:
        |
        | ID #
        | Primary ICD
        | Secondary ICD
        | Attendance dates...
        | Grand Total
        |
        */

        if ($secondaryDiagIndex !== null) {

            $attendanceStartIndex =
                $secondaryDiagIndex + 1;
        } elseif ($primaryDiagIndex !== null) {

            $attendanceStartIndex =
                $primaryDiagIndex + 1;
        } else {

            throw new \Exception(
                'Unable to determine attendance columns.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Read Actual Service-Date Columns From Excel
        |--------------------------------------------------------------------------
        */

        $usedAttendanceDays = [];

        for (
            $columnIndex = $attendanceStartIndex;
            $columnIndex < $grandTotalIndex;
            $columnIndex++
        ) {

            $headerValue = trim(
                (string) (
                    $headers[$columnIndex]
                    ?? ''
                )
            );

            if ($headerValue === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Attendance Heading Must Be Numeric
            |--------------------------------------------------------------------------
            */

            if (!ctype_digit($headerValue)) {

                throw new \Exception(
                    'Invalid attendance column heading "'
                        . $headerValue
                        . '".'
                );
            }

            $dayNumber = (int) $headerValue;

            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Attendance Dates
            |--------------------------------------------------------------------------
            */

            if (isset($usedAttendanceDays[$dayNumber])) {

                throw new \Exception(
                    'Duplicate attendance column '
                        . $headerValue
                        . ' was found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Date Must Belong To Selected Sunday-Saturday Week
            |--------------------------------------------------------------------------
            */

            if (!isset($weekDatesByDay[$dayNumber])) {

                throw new \Exception(
                    'Attendance column '
                        . $headerValue
                        . ' is outside the selected attendance week '
                        . $startDate->format('m/d/Y')
                        . ' through '
                        . $endDate->format('m/d/Y')
                        . '.'
                );
            }

            $usedAttendanceDays[$dayNumber] = true;

            $dateColumns[$columnIndex] =
                $weekDatesByDay[$dayNumber]
                ->copy();
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Number Of Facility Service Days
        |--------------------------------------------------------------------------
        */

        $serviceDayCount =
            count($dateColumns);

        if ($serviceDayCount < 5) {

            throw new \Exception(
                'Attendance file contains only '
                    . $serviceDayCount
                    . ' service-date columns. '
                    . 'Minimum required is 5.'
            );
        }

        if ($serviceDayCount > 7) {

            throw new \Exception(
                'Attendance file contains '
                    . $serviceDayCount
                    . ' service-date columns. '
                    . 'Maximum allowed is 7.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sort Attendance Columns By Actual Date
        |--------------------------------------------------------------------------
        */

        uasort(
            $dateColumns,
            function ($dateA, $dateB) {

                return $dateA->timestamp
                    <=> $dateB->timestamp;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Load Members Once
        |--------------------------------------------------------------------------
        */

        $members = [];

        DB::table('billing_members')
            ->get()
            ->each(function ($member) use (&$members) {

                $key =
                    $this->canonicalParticipantId(
                        $member->ParticipantId
                    );

                $members[$key] =
                    $member;
            });

        $previewRows = [];

        /*
        |--------------------------------------------------------------------------
        | Process Excel Rows
        |--------------------------------------------------------------------------
        */

        for (
            $rowIndex = 1;
            $rowIndex < count($sheet);
            $rowIndex++
        ) {

            $row = $sheet[$rowIndex];

            $rawId =
                $row[$headerMap['ID #']]
                ?? null;

            if (
                $rawId === null ||
                trim((string) $rawId) === ''
            ) {
                continue;
            }

            $canonicalId =
                $this->canonicalParticipantId(
                    $rawId
                );

            $member =
                $members[$canonicalId]
                ?? null;

            /*
            |--------------------------------------------------------------------------
            | Missing Member
            |--------------------------------------------------------------------------
            */

            if (!$member) {

                $previewRows[] = [

                    'key' =>
                    $rowIndex . '-member',

                    'excel_row_key' =>
                    $rowIndex,

                    'excel_row' =>
                    $rowIndex + 1,

                    'participant_id' =>
                    trim((string) $rawId),

                    'member_found' =>
                    false,

                    'service_from' =>
                    null,

                    'service_to' =>
                    null,

                    'units' =>
                    0,

                    'proc_code' =>
                    null,

                    'modifier' =>
                    null,

                    'amount_per_unit' =>
                    null,

                    'total_amount' =>
                    null,

                    'prior_auth_no' =>
                    null,

                    'prior_auth_expiry' =>
                    null,

                    'diag1' =>
                    $primaryDiagIndex !== null
                        ? ($row[$primaryDiagIndex] ?? null)
                        : null,

                    'diag2' =>
                    $secondaryDiagIndex !== null
                        ? ($row[$secondaryDiagIndex] ?? null)
                        : null,

                    'valid' =>
                    false,

                    'message' =>
                    'Participant ID not found in billing_members table.',
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Diagnosis From Excel / Member
            |--------------------------------------------------------------------------
            */

            $diag1 = '';

            if ($primaryDiagIndex !== null) {

                $diag1 = trim(
                    (string) (
                        $row[$primaryDiagIndex]
                        ?? ''
                    )
                );
            }

            if ($diag1 === '') {

                $diag1 = trim(
                    (string) (
                        $member->PrimaryDiagnosis1
                        ?? ''
                    )
                );
            }

            $diag2 = '';

            if ($secondaryDiagIndex !== null) {

                $diag2 = trim(
                    (string) (
                        $row[$secondaryDiagIndex]
                        ?? ''
                    )
                );
            }

            if ($diag2 === '') {

                $diag2 = trim(
                    (string) (
                        $member->SecondaryDiag1
                        ?? ''
                    )
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Apply Preview ICD Corrections
            |--------------------------------------------------------------------------
            |
            | One correction applies to all generated segments
            | originating from this Excel row.
            |
            */

            if (isset($icdOverrides[$rowIndex])) {

                if (
                    array_key_exists(
                        'diag1',
                        $icdOverrides[$rowIndex]
                    )
                ) {

                    $diag1 = trim(
                        (string)
                        $icdOverrides[$rowIndex]['diag1']
                    );
                }

                if (
                    array_key_exists(
                        'diag2',
                        $icdOverrides[$rowIndex]
                    )
                ) {

                    $diag2 = trim(
                        (string)
                        $icdOverrides[$rowIndex]['diag2']
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Attendance Dates
            |--------------------------------------------------------------------------
            */

            $attendanceDates = [];

            foreach (
                $dateColumns as
                $columnIndex => $date
            ) {

                $value =
                    $row[$columnIndex]
                    ?? null;

                if (
                    $value !== null &&
                    trim((string) $value) !== ''
                ) {

                    $attendanceDates[] =
                        $date->copy();
                }
            }

            if (empty($attendanceDates)) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Build Segments
            |--------------------------------------------------------------------------
            */

            $segments =
                $this->buildExcelClaimSegments(
                    $member,
                    $attendanceDates
                );

            foreach (
                $segments as
                $segmentIndex => $segment
            ) {

                $errors = [];

                /*
                |--------------------------------------------------------------------------
                | Primary ICD
                |--------------------------------------------------------------------------
                */

                if ($diag1 === '') {

                    $errors[] =
                        'Primary diagnosis is missing.';
                } else {

                    $exists =
                        DB::table('icd10_codes')
                        ->where('code', $diag1)
                        ->exists();

                    if (!$exists) {

                        $errors[] =
                            'Invalid Primary ICD: '
                            . $diag1
                            . '.';
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Secondary ICD
                |--------------------------------------------------------------------------
                */

                if ($diag2 !== '') {

                    $exists =
                        DB::table('icd10_codes')
                        ->where('code', $diag2)
                        ->exists();

                    if (!$exists) {

                        $errors[] =
                            'Invalid Secondary ICD: '
                            . $diag2
                            . '.';
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Segment Error
                |--------------------------------------------------------------------------
                */

                if (!empty($segment['error'])) {

                    $errors[] =
                        $segment['error'];
                }

                /*
                |--------------------------------------------------------------------------
                | Duplicate Draft Check
                |--------------------------------------------------------------------------
                */

                if (empty($errors)) {

                    $duplicate =
                        DB::table('claims')
                        ->where(
                            'ParticipantId',
                            $member->ParticipantId
                        )
                        ->whereDate(
                            'service_from',
                            $segment['service_from']
                        )
                        ->whereDate(
                            'service_to',
                            $segment['service_to']
                        )
                        ->where(
                            'proc_code',
                            $segment['proc_code']
                        )
                        ->where(
                            'm1',
                            $segment['modifier']
                        )
                        ->whereNull('claim_no')
                        ->exists();

                    if ($duplicate) {

                        $errors[] =
                            'Matching draft claim already exists.';
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Total Amount
                |--------------------------------------------------------------------------
                */

                $totalAmount = null;

                if (
                    $segment['amount_per_unit']
                    !== null
                ) {

                    $totalAmount =
                        round(
                            (float)
                            $segment['amount_per_unit']
                                *
                                (int)
                                $segment['units'],
                            2
                        );
                }

                $previewRows[] = [

                    'key' =>
                    $rowIndex
                        . '-'
                        . $segmentIndex,

                    'excel_row_key' =>
                    $rowIndex,

                    'excel_row' =>
                    $rowIndex + 1,

                    'participant_id' =>
                    $member->ParticipantId,

                    'member_found' =>
                    true,

                    'service_from' =>
                    $segment['service_from'],

                    'service_to' =>
                    $segment['service_to'],

                    'units' =>
                    $segment['units'],

                    'proc_code' =>
                    $segment['proc_code'],

                    'modifier' =>
                    $segment['modifier'],

                    'amount_per_unit' =>
                    $segment['amount_per_unit'],

                    'total_amount' =>
                    $totalAmount,

                    'prior_auth_no' =>
                    $segment['prior_auth_no'],

                    'prior_auth_expiry' =>
                    $segment['prior_auth_expiry'],

                    'diag1' =>
                    $diag1 ?: null,

                    'diag2' =>
                    $diag2 ?: null,

                    /*
                    |--------------------------------------------------------------------------
                    | Current Member PA Information
                    |--------------------------------------------------------------------------
                    */

                    'medicaid_PA_no' =>
                    $member->medicaid_PA_no
                        ?? null,

                    'medicaid_PA_Start' =>
                    !empty($member->medicaid_PA_Start)
                        ? Carbon::parse(
                            $member->medicaid_PA_Start
                        )->format('Y-m-d')
                        : null,

                    'medicaid_PA_End' =>
                    !empty($member->medicaid_PA_End)
                        ? Carbon::parse(
                            $member->medicaid_PA_End
                        )->format('Y-m-d')
                        : null,

                    'renew1_PA_no' =>
                    $member->renew1_PA_no
                        ?? null,

                    'renew1_PA_Start' =>
                    !empty($member->renew1_PA_Start)
                        ? Carbon::parse(
                            $member->renew1_PA_Start
                        )->format('Y-m-d')
                        : null,

                    'renew1_PA_End' =>
                    !empty($member->renew1_PA_End)
                        ? Carbon::parse(
                            $member->renew1_PA_End
                        )->format('Y-m-d')
                        : null,

                    'valid' =>
                    empty($errors),

                    'message' =>
                    empty($errors)
                        ? 'OK'
                        : implode(
                            ' ',
                            $errors
                        ),
                ];
            }
        }

        return $previewRows;
    } // End Method

    private function canonicalParticipantId($participantId): string
    {
        $id = trim(
            (string) $participantId
        );

        /*
        |--------------------------------------------------------------------------
        | Excel sometimes returns numeric IDs like 1075.0
        |--------------------------------------------------------------------------
        */

        if (
            is_numeric($id) &&
            strpos($id, '.') !== false
        ) {
            $id = (string) ((int) $id);
        }

        /*
        |--------------------------------------------------------------------------
        | Remove leading zeros for comparison only
        |--------------------------------------------------------------------------
        */

        $canonical =
            ltrim($id, '0');

        return $canonical === ''
            ? '0'
            : $canonical;
    } // End Method

    public function excelImportFixIcd(Request $request)
    {
        $request->validate([
            'token' => [
                'required',
                'string',
            ],

            'excel_row_key' => [
                'required',
                'integer',
            ],

            'diag1' => [
                'required',
                'string',
                'max:10',
            ],

            'diag2' => [
                'nullable',
                'string',
                'max:10',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validate Diag1
        |--------------------------------------------------------------------------
        */

        $diag1 = trim(
            (string) $request->diag1
        );

        $diag2 = trim(
            (string) $request->diag2
        );

        $diag1Exists =
            DB::table('icd10_codes')
            ->where('code', $diag1)
            ->exists();

        if (!$diag1Exists) {

            return back()
                ->with(
                    'message',
                    'Primary ICD code is not valid.'
                )
                ->with(
                    'alert-type',
                    'error'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Diag2 If Entered
        |--------------------------------------------------------------------------
        */

        if ($diag2 !== '') {

            $diag2Exists =
                DB::table('icd10_codes')
                ->where('code', $diag2)
                ->exists();

            if (!$diag2Exists) {

                return back()
                    ->with(
                        'message',
                        'Secondary ICD code is not valid.'
                    )
                    ->with(
                        'alert-type',
                        'error'
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update Import Session
        |--------------------------------------------------------------------------
        */

        $imports =
            session(
                'claims_excel_imports',
                []
            );

        if (
            !isset(
                $imports[$request->token]
            )
        ) {

            return redirect()
                ->route('claims.excel.form')
                ->with(
                    'message',
                    'Excel import session expired. Please upload again.'
                )
                ->with(
                    'alert-type',
                    'error'
                );
        }

        $rowKey =
            (int)
            $request->excel_row_key;

        $imports[$request->token]['icd_overrides'][$rowKey] = [

            'diag1' =>
            $diag1,

            'diag2' =>
            $diag2 !== ''
                ? $diag2
                : null,
        ];

        session([
            'claims_excel_imports' =>
            $imports
        ]);

        return redirect()
            ->route(
                'claims.excel.preview.show',
                $request->token
            )
            ->with(
                'message',
                'Diagnosis codes updated and claims revalidated.'
            )
            ->with(
                'alert-type',
                'success'
            );
    } // End Method

    public function excelImportFixPa(Request $request)
    {
        $request->validate([
            'token' => [
                'required',
                'string',
            ],

            'participant_id' => [
                'required',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | Existing Medicaid PA
            |--------------------------------------------------------------------------
            */

            'medicaid_PA_no' => [
                'nullable',
                'string',
                'max:20',
            ],

            'medicaid_PA_Start' => [
                'nullable',
                'date',
            ],

            'medicaid_PA_End' => [
                'nullable',
                'date',
                'after_or_equal:medicaid_PA_Start',
            ],

            /*
            |--------------------------------------------------------------------------
            | Renewal PA
            |--------------------------------------------------------------------------
            */

            'renew1_PA_no' => [
                'nullable',
                'string',
                'max:20',
            ],

            'renew1_PA_Start' => [
                'nullable',
                'date',
            ],

            'renew1_PA_End' => [
                'nullable',
                'date',
                'after_or_equal:renew1_PA_Start',
            ],
        ]);

        $imports =
            session(
                'claims_excel_imports',
                []
            );

        if (
            !isset(
                $imports[$request->token]
            )
        ) {

            return redirect()
                ->route('claims.excel.form')
                ->with(
                    'message',
                    'Excel import session expired. Please upload again.'
                )
                ->with(
                    'alert-type',
                    'error'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Find Member
        |--------------------------------------------------------------------------
        */

        $member =
            DB::table('billing_members')
            ->whereRaw(
                'TRIM(ParticipantId) = ?',
                [
                    trim(
                        $request->participant_id
                    )
                ]
            )
            ->first();

        if (!$member) {

            return back()
                ->with(
                    'message',
                    'Member could not be found.'
                )
                ->with(
                    'alert-type',
                    'error'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Update Member PA Information
        |--------------------------------------------------------------------------
        */

        DB::table('billing_members')
            ->where(
                'ParticipantId',
                $member->ParticipantId
            )
            ->update([

                'medicaid_PA_no' =>
                $request->filled(
                    'medicaid_PA_no'
                )
                    ? trim(
                        $request->medicaid_PA_no
                    )
                    : null,

                'medicaid_PA_Start' =>
                $request->filled(
                    'medicaid_PA_Start'
                )
                    ? Carbon::parse(
                        $request->medicaid_PA_Start
                    )
                    : null,

                'medicaid_PA_End' =>
                $request->filled(
                    'medicaid_PA_End'
                )
                    ? Carbon::parse(
                        $request->medicaid_PA_End
                    )
                    : null,

                'renew1_PA_no' =>
                $request->filled(
                    'renew1_PA_no'
                )
                    ? trim(
                        $request->renew1_PA_no
                    )
                    : null,

                'renew1_PA_Start' =>
                $request->filled(
                    'renew1_PA_Start'
                )
                    ? Carbon::parse(
                        $request->renew1_PA_Start
                    )
                    : null,

                'renew1_PA_End' =>
                $request->filled(
                    'renew1_PA_End'
                )
                    ? Carbon::parse(
                        $request->renew1_PA_End
                    )
                    : null,

                'updated_at' =>
                now(),
            ]);

        return redirect()
            ->route(
                'claims.excel.preview.show',
                $request->token
            )
            ->with(
                'message',
                'Prior authorization updated and claims revalidated.'
            )
            ->with(
                'alert-type',
                'success'
            );
    } // End Method

    private function buildExcelClaimSegments($member, array $attendanceDates): array
    {

        $segments = [];

        $currentSegment = null;

        foreach ($attendanceDates as $attendanceDate) {

            $date = Carbon::parse(
                $attendanceDate
            )->startOfDay();

            /*
            |--------------------------------------------------------------------------
            | Find PA
            |--------------------------------------------------------------------------
            */

            $pa = $this->getPriorAuthorizationForDate(
                $member,
                $date
            );

            /*
            |--------------------------------------------------------------------------
            | Find rate/procedure/modifier
            |--------------------------------------------------------------------------
            */

            $rate = $this->getClaimRateForDate(
                $date
            );

            /*
            |--------------------------------------------------------------------------
            | Missing PA or rate
            |--------------------------------------------------------------------------
            */

            if (!$pa || !$rate) {

                if ($currentSegment !== null) {
                    $segments[] = $currentSegment;
                    $currentSegment = null;
                }

                $messages = [];

                if (!$pa) {
                    $messages[] =
                        'No valid PA for '
                        . $date->format('m/d/Y')
                        . '.';
                }

                if (!$rate) {
                    $messages[] =
                        'No service rate for '
                        . $date->format('m/d/Y')
                        . '.';
                }

                $segments[] = [
                    'service_from'      => $date->toDateString(),
                    'service_to'        => $date->toDateString(),
                    'units'             => 1,
                    'proc_code'         => $rate->proc_code ?? null,
                    'modifier'          => $rate->modifier ?? null,
                    'amount_per_unit'   => $rate
                        ? (float) $rate->amount_per_unit
                        : null,
                    'prior_auth_no'     => $pa['pa_no'] ?? null,
                    'prior_auth_expiry' => $pa['pa_end'] ?? null,
                    'error'             => implode(' ', $messages),
                ];

                continue;
            }

            $currentRate = round(
                (float) $rate->amount_per_unit,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | First segment
            |--------------------------------------------------------------------------
            */

            if ($currentSegment === null) {

                $currentSegment = [
                    'service_from'      => $date->toDateString(),
                    'service_to'        => $date->toDateString(),
                    'last_date'         => $date->copy(),
                    'units'             => 1,
                    'proc_code'         => $rate->proc_code,
                    'modifier'          => $rate->modifier,
                    'amount_per_unit'   => $currentRate,
                    'prior_auth_no'     => $pa['pa_no'],
                    'prior_auth_expiry' => $pa['pa_end'],
                    'error'             => null,
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Compare
            |--------------------------------------------------------------------------
            */

            $expectedNext = $currentSegment['last_date']
                ->copy()
                ->addDay();

            $isConsecutive = $date->equalTo(
                $expectedNext
            );

            $samePa =
                trim((string) $currentSegment['prior_auth_no'])
                ===
                trim((string) $pa['pa_no']);

            $samePaExpiry =
                (string) $currentSegment['prior_auth_expiry']
                ===
                (string) $pa['pa_end'];

            $sameProcCode =
                (string) $currentSegment['proc_code']
                ===
                (string) $rate->proc_code;

            $sameModifier =
                (string) $currentSegment['modifier']
                ===
                (string) $rate->modifier;

            $sameRate =
                round(
                    (float) $currentSegment['amount_per_unit'],
                    2
                )
                ===
                $currentRate;

            /*
            |--------------------------------------------------------------------------
            | Continue same claim
            |--------------------------------------------------------------------------
            */

            if (
                $isConsecutive &&
                $samePa &&
                $samePaExpiry &&
                $sameProcCode &&
                $sameModifier &&
                $sameRate
            ) {

                $currentSegment['service_to'] =
                    $date->toDateString();

                $currentSegment['last_date'] =
                    $date->copy();

                $currentSegment['units']++;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Close current segment
            |--------------------------------------------------------------------------
            */

            $segments[] = $currentSegment;

            /*
            |--------------------------------------------------------------------------
            | New segment
            |--------------------------------------------------------------------------
            */

            $currentSegment = [
                'service_from'      => $date->toDateString(),
                'service_to'        => $date->toDateString(),
                'last_date'         => $date->copy(),
                'units'             => 1,
                'proc_code'         => $rate->proc_code,
                'modifier'          => $rate->modifier,
                'amount_per_unit'   => $currentRate,
                'prior_auth_no'     => $pa['pa_no'],
                'prior_auth_expiry' => $pa['pa_end'],
                'error'             => null,
            ];
        }

        if ($currentSegment !== null) {
            $segments[] = $currentSegment;
        }

        foreach ($segments as &$segment) {
            unset($segment['last_date']);
        }

        return $segments;
    } // End Method

    public function show837Form(Request $request, $type)
    {
        // $type: 'A' (Morning/Active) or 'N' (Noon/Inactive)
        return view('claims.claims_837_form', [
            'type'      => $type,
            'startDate' => $request->input('start_date'),
            'endDate'   => $request->input('end_date'),
        ]);
    } // End Method




    /*
    public function download837(Request $request, $type)
    {
        $memberStatus = $type; // 'A' or 'N'

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        // 1) Participants for this shift/status
        $participantIds = DB::table('members')
            ->where('Trans_flag', $memberStatus)
            ->pluck('ParticipantId')
            ->toArray();

        if (empty($participantIds)) {
            return back()
                ->withInput()
                ->with('message', 'No members found for the selected shift/status.')
                ->with('alert-type', 'info');
        }

        // 2) Claims in date range for these participants
        $claims = DB::table('claims')
            ->whereBetween('service_from', [$startDate, $endDate])
            ->whereIn('ParticipantId', $participantIds)
            ->orderBy('ParticipantId')
            ->orderBy('claim_group')
            ->orderBy('line_no')
            ->get();

        if ($claims->isEmpty()) {
            return back()
                ->withInput()
                ->with('message', 'No claims found for this period / shift.')
                ->with('alert-type', 'info');
        }

        // 3) Member details
        $members = DB::table('members')
            ->whereIn('ParticipantId', $claims->pluck('ParticipantId')->unique()->values())
            ->get()
            ->keyBy('ParticipantId');

        // 4) Company / billing provider (from config('company'))
        $company      = config('company', []);
        $companyName  = trim($company['comp_nm'] ?? 'MY HOME ADULT DAY CARE');
        $companyAddr1 = trim($company['complc_addr1'] ?? 'ADDRESS LINE 1');
        $companyCity  = trim($company['complc_city'] ?? 'CITY');
        $companyState = trim($company['complc_state'] ?? 'NJ');
        $companyZip   = preg_replace('/[^0-9]/', '', $company['complc_pin'] ?? '07000');
        $companyTxCode = preg_replace('/[^0-9]/', '', $company['tax_id'] ?? '26-0662853');
        $companyNpi   = preg_replace('/[^0-9]/', '', $company['npi'] ?? '1053597096');
        $providerContactName ='DAY CARE CENTER MY HOME ADULT';
        $providerPhone = $company['complc_ph'];
        $providerFax = $company['complc_fax'];


        // 🔹 IMPORTANT CHANGE: keep full taxonomy like Medisoft: 261QA0600X
        $companyTaxonomy = strtoupper(str_replace([' ', '-'], '', $company['taxonomy_code'] ?? '261QA0600X'));

        // 5) Envelope values
        $now           = now();
        $controlNumber = str_pad((string) random_int(1, 999999999), 9, '0', STR_PAD_LEFT);
        $senderId      = 'P62442100000000';   // replace with your submitter ID
        $receiverId    = 'CLAIMSCH0000000';   // replace with payer/clearinghouse ID
        $senderCode    = 'P624421';          // GS02
        $receiverCode  = 'ECGCLAIMS';        // GS03
        $receiverTxCode = '4300';

        $segments = [];

        // ISA – Interchange Control Header
        $segments[] = sprintf(
            'ISA*00*          *01*CYCTRANS00*ZZ*% -15s*ZZ*% -15s*%s*%s*^*00501*%s*0*T*:',
            $senderId,
            $receiverId,
            $now->format('ymd'),
            $now->format('Hi'),
            $controlNumber
        );

        // GS – Functional Group Header
        $segments[] = sprintf(
            'GS*HC*%s*%s*%s*%s*1*X*005010X222A1',
            $senderCode,
            $receiverCode,
            $now->format('Ymd'),
            $now->format('Hi')
        );

        // Transaction set segments collected separately for SE count
        $tx = [];

        // ST – Transaction Set Header
        $tx[] = 'ST*837*0001*005010X222A1';

        // BHT – Beginning of Hierarchical Transaction
        $tx[] = sprintf(
            'BHT*0019*00*%s*%s*%s*CH',
            $controlNumber,
            $now->format('Ymd'),
            $now->format('Hi')
        );

        // Submitter (NM1*41) and Receiver (NM1*40)
        $tx[] = sprintf('NM1*41*2*%s*****46*%s', $this->sanitizeX12($companyName), $companyTxCode);
        $tx[] = 'PER*IC*BHARAT MEHTA*TE*9089061085';
        $tx[] = sprintf('NM1*40*2*%s*****46*%s', 'CHANGE HEALTHCARE', $receiverTxCode);

        // Billing Provider Loop (2000A/2010AA)
        $tx[] = 'HL*1*1*20*1';
        $tx[] = sprintf('NM1*85*2*%s*****XX*%s', $this->sanitizeX12($companyName), $companyNpi);
        $tx[] = sprintf('N3*%s', $this->sanitizeX12($companyAddr1));
        $tx[] = sprintf('N4*%s*%s*%s', $this->sanitizeX12($companyCity), $companyState, $companyZip);
        $tx[] = sprintf('REF*EI*%s',$companyTxCode); // Tax ID placeholder

        // ⭐ CHANGED: Add PER immediately after REF*EI (like Medisoft)
        $tx[] = sprintf(
            'PER*IC*%s*TE*%s*FX*%s',
            $providerContactName,
            $this->sanitizeX12($providerPhone),
            $this->sanitizeX12($providerFax)
        );

        $hlCounter   = 1;
        $claimSeq    = 1;

        // Group claims by payer (insurance)
        $claimsByPayer = $claims->groupBy(function ($c) {
            return $c->insurance_name ?? 'ZZZ_UNKNOWN';
        })->sortKeys();

        foreach ($claimsByPayer as $payerNameKey => $payerClaims) {

            $claimsByGroup = $payerClaims->groupBy('claim_group');

            foreach ($claimsByGroup as $claimGroup => $lines) {

                $firstLine     = $lines->first();
                $participantId = $firstLine->ParticipantId;
                $member        = $members->get($participantId);

                if (!$member) {
                    continue;
                }

                $hlCounter++;
                $subscriberHl = $hlCounter;

                // ⭐ CHANGED: force HL like Medisoft → HL*1*1*22*1
                // (X12 prefers unique HL01, but Medisoft sample uses 1.)
                $tx[] = 'HL*1*1*22*1';

                // SBR – Subscriber info
                $tx[] = 'SBR*P*18*******MC';

                $lastName  = $this->sanitizeX12($member->LastName ?? 'LAST');
                $firstName = $this->sanitizeX12($member->FirstName ?? 'FIRST');

                $dobRaw = $member->DateOfBirth ?? null;
                try {
                    $dob = $dobRaw ? Carbon::parse($dobRaw)->format('Ymd') : '19000101';
                } catch (\Throwable $e) {
                    $dob = '19000101';
                }

                $sex = strtoupper(substr($member->Sex ?? 'U', 0, 1)); // M/F/U

                // 2010BA – Subscriber Name
                $tx[] = sprintf(
                    'NM1*IL*1*%s*%s****MI*%s',
                    $lastName,
                    $firstName,
                    $firstLine->insurance_id ?? 'UNKNOWN'
                );
                $tx[] = 'N3*' . $this->sanitizeX12($member->Address1 ?? 'ADDRESS');
                $tx[] = sprintf(
                    'N4*%s*%s*%s',
                    $this->sanitizeX12($member->City ?? 'CITY'),
                    $member->State ?? 'NJ',
                    preg_replace('/[^0-9]/', '', $member->ZipCode ?? '07000')
                );
                $tx[] = sprintf('DMG*D8*%s*%s', $dob, $sex);

                // 2010BB – Payer Name
                $payerName = $this->sanitizeX12($firstLine->insurance_name ?? 'PAYER NAME');
                $payerId   = $firstLine->payer_id ?? '99999';

                $tx[] = sprintf('NM1*PR*2*%s*****PI*%s', $payerName, $payerId);

                if (!empty($firstLine->payer_address1)) {
                    $tx[] = 'N3*' . $this->sanitizeX12($firstLine->payer_address1);
                }

                if (!empty($firstLine->payer_city) || !empty($firstLine->payer_state) || !empty($firstLine->payer_zip)) {
                    $tx[] = sprintf(
                        'N4*%s*%s*%s',
                        $this->sanitizeX12($firstLine->payer_city ?? ''),
                        $firstLine->payer_state ?? '',
                        preg_replace('/[^0-9]/', '', $firstLine->payer_zip ?? '')
                    );
                }

                // taxonomy REF on payer – matches Medisoft REF*G2*261QA0600X
                $tx[] = sprintf('REF*G2*%s', $companyTaxonomy);

                // 2300 – Claim level
                $totalCharge = $lines->sum('total_amount');
                $claimId     = str_pad((string)$claimSeq, 7, '0', STR_PAD_LEFT);
                $claimSeq++;

                // Claim-level service range (for reference; we’ll use it on the line)
                $serviceFrom = Carbon::parse($lines->min('service_from'))->format('Ymd');
                $serviceTo   = Carbon::parse($lines->max('service_to'))->format('Ymd');

                // 🔹 CLM amount formatted like Medisoft: 473.3 instead of 473.30
                $amountStr = rtrim(rtrim(number_format($totalCharge, 2, '.', ''), '0'), '.');

                $tx[] = sprintf(
                    'CLM*%s*%s***99:B:1*N*A*Y*Y',
                    $claimId,
                    $amountStr
                );

                // ❌ Medisoft file has NO DTP*431 at claim level – so we REMOVE it
                // ❌ Also Medisoft puts DTP*472 at 2400 level, not here
                // (So we do not add DTP here.)

                // Prior auth if present → REF*G1
                if (!empty($firstLine->prior_auth_no)) {
                    $tx[] = sprintf('REF*G1*%s', $this->sanitizeX12($firstLine->prior_auth_no));
                }


                // Diagnosis code goes here
                // Build HI segment
                $hiParts = ['HI'];  // segment ID

                if (!empty($firstLine->diag1)) {
                    $diag1 = $this->sanitizeX12($firstLine->diag1 ?? 'Z0000');
                    $hiParts[] = 'ABK:' . $diag1;
                }

                // 2) Secondary diagnosis
                if (!empty($firstLine->diag2)) {
                    $diag2 = $this->sanitizeX12($firstLine->diag2 ?? 'Z0000');
                    $hiParts[] = 'ABF:' . $diag2;
                }

                if (count($hiParts) > 1) {
                $tx[] = implode('*', $hiParts) . '~';
                }

                // 2310B – Rendering Provider (similar to Medisoft NM1*82)
                $tx[] = sprintf(
                    'NM1*82*2*%s*****XX*%s',
                    $this->sanitizeX12($companyName),
                    $companyNpi
                );

                // Rendering provider taxonomy – keep full 261QA0600X
                $tx[] = sprintf('PRV*PE*PXC*%s', $companyTaxonomy);

                // Extra REF with taxonomy like Medisoft
                $tx[] = sprintf('REF*G2*%s', $companyTaxonomy);

                // 2310C – Service Facility Location
                $tx[] = sprintf(
                    'NM1*77*2*%s*****XX*%s',
                    $this->sanitizeX12($companyName),
                    $companyNpi
                );

                // ⭐ CHANGED: Add facility N3/N4 after NM1*77 (company address)
                $tx[] = sprintf('N3*%s', $this->sanitizeX12($companyAddr1));
                $tx[] = sprintf('N4*%s*%s*%s', $this->sanitizeX12($companyCity), $companyState, $companyZip);

                // 2400 – Service lines from each claim line
                foreach ($lines as $line) {
                    $tx[] = sprintf('LX*%d', $line->line_no);

                    $lineCharge = number_format($line->total_amount, 2, '.', '');
                    $units      = (int) $line->units;

                    // Procedure and modifier
                    $procCode = $this->sanitizeX12($line->proc_code ?? 'S5102');
                    $modifier = $this->sanitizeX12($line->m1 ?? 'U1');

                    // ⭐ Hard-coded Medisoft-style description
                    $svcDesc = 'DAY CARE SERVICE ADULT';

                    // ⭐ FINAL Medisoft-style SV1:
                    // SVC1*HC:S5102:U1::::DAY CARE SERVICE ADULT*473.3*UN*5***1
                    $tx[] = sprintf(
                        'SV1*HC:%s:%s::::%s*%s*UN*%d***1',
                        $procCode,
                        $modifier,
                        $svcDesc,
                        $lineCharge,
                        $units
                    );

                    // 🔹 DTP at line-level like Medisoft:
                    // If range: RD8*from-to, else D8*date
                    $lineFrom = Carbon::parse($line->service_from)->format('Ymd');
                    $lineTo   = $line->service_to
                        ? Carbon::parse($line->service_to)->format('Ymd')
                        : $lineFrom;

                    if ($lineFrom === $lineTo) {
                        $tx[] = sprintf('DTP*472*D8*%s', $lineFrom);
                    } else {
                        $tx[] = sprintf('DTP*472*RD8*%s-%s', $lineFrom, $lineTo);
                    }

                    // OPTIONAL – if you have some internal reference column,
                    // you can mimic Medisoft's REF*6R*8638 here.
                    // Example (uncomment when you decide the source column):
                    // if (!empty($line->internal_ref)) {
                    //     $tx[] = 'REF*6R*' . $this->sanitizeX12($line->internal_ref);
                    // }

                    $tx[] = sprintf('REF*6R*%d', $line->line_no);
                }
            }
        }

        // Close transaction set – SE/GE/IEA
        $segmentCount = count($tx) + 1; // SE includes ST + all in tx

        $tx[] = sprintf('SE*%d*0001', $segmentCount);

        // Add TX segments after GS
        $segments = array_merge($segments, $tx);

        // One functional group & one interchange
        $segments[] = 'GE*1*1';
        $segments[] = sprintf('IEA*1*%s', $controlNumber);

        // Build final file content
        $content = implode("~\n", $segments) . "~\n";

        $fileName = '837P_' . $now->format('Ymd_His') . '.txt';

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    } */

    /**
     * Small helper: remove * and ~ from strings, trim length a bit.
     */

    /*
    protected function sanitizeX12(?string $value, int $maxLen = 60): string
    {
        $value = $value ?? '';
        $value = str_replace(['*', '~', ':', '^', '|', '#', '-'], ' ', $value);
        $value = trim(preg_replace('/\s+/', ' ', $value));
        return substr($value, 0, $maxLen);
    } */

    public function download837(Request $request, $type)
    {
        $memberStatus = $type; // 'A' or 'N'

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        // 1) Participants for this shift/status
        $participantIds = DB::table('billing_members')
            ->where('Trans_flag', $memberStatus)
            ->pluck('ParticipantId')
            ->toArray();

        if (empty($participantIds)) {
            return back()
                ->withInput()
                ->with('message', 'No members found for the selected shift/status.')
                ->with('alert-type', 'info');
        }

        // 2) Claims in date range for these participants
        $claims = DB::table('claims')
            ->whereBetween('service_from', [$startDate, $endDate])
            ->whereIn('ParticipantId', $participantIds)
            ->orderBy('ParticipantId')
            ->orderBy('claim_group')
            ->orderBy('line_no')
            ->get();

        if ($claims->isEmpty()) {
            return back()
                ->withInput()
                ->with('message', 'No claims found for this period / shift.')
                ->with('alert-type', 'info');
        }

        // 3) Member details
        $members = DB::table('billing_members')
            ->whereIn('ParticipantId', $claims->pluck('ParticipantId')->unique()->values())
            ->get()
            ->keyBy('ParticipantId');

        // 4) Company / billing provider (from config('company'))
        $company      = config('company', []);
        $companyName  = trim($company['comp_nm'] ?? 'MY HOME ADULT DAY CARE');
        $companyAddr1 = trim($company['complc_addr1'] ?? 'ADDRESS LINE 1');
        $companyCity  = trim($company['complc_city'] ?? 'CITY');
        $companyState = trim($company['complc_state'] ?? 'NJ');
        $companyZip   = preg_replace('/[^0-9]/', '', $company['complc_pin'] ?? '07000');
        $companyTxCode = preg_replace('/[^0-9]/', '', $company['tax_id'] ?? '26-0662853');
        $companyNpi   = preg_replace('/[^0-9]/', '', $company['npi'] ?? '1053597096');

        $providerContactName = 'DAY CARE CENTER MY HOME ADULT';

        $providerPhone = preg_replace('/[^0-9]/', '', $company['complc_ph'] ?? '2012918811');
        $providerFax   = preg_replace('/[^0-9]/', '', $company['complc_fax'] ?? '2012918833');

        // Taxonomy (keep full like Medisoft: 261QA0600X)
        $companyTaxonomy = strtoupper(str_replace([' ', '-'], '', $company['taxonomy_code'] ?? '261QA0600X'));

        // 5) Envelope values
        $now           = now();
        $controlNumber = str_pad((string) random_int(1, 999999999), 9, '0', STR_PAD_LEFT);
        $senderId      = 'P62442100000000';   // replace with your submitter ID
        $receiverId    = 'CLAIMSCH0000000';   // replace with payer/clearinghouse ID
        $senderCode    = 'P624421';          // GS02
        $receiverCode  = 'ECGCLAIMS';        // GS03
        $receiverTxCode = '4300';



        $segments = [];

        // ISA – Interchange Control Header
        $segments[] = sprintf(
            'ISA*00*          *01*CYCTRANS00*ZZ*% -15s*ZZ*% -15s*%s*%s*^*00501*%s*0*T*:',
            $senderId,
            $receiverId,
            $now->format('ymd'),
            $now->format('Hi'),
            $controlNumber
        );

        // GS – Functional Group Header
        $segments[] = sprintf(
            'GS*HC*%s*%s*%s*%s*1*X*005010X222A1',
            $senderCode,
            $receiverCode,
            $now->format('Ymd'),
            $now->format('Hi')
        );

        // Group claims by payer (insurance)
        $claimsByPayer = $claims->groupBy(function ($c) {
            return $c->insurance_name ?? 'ZZZ_UNKNOWN';
        })->sortKeys();

        $transactionSetCount = 0;  // how many ST/SE we output

        foreach ($claimsByPayer as $payerNameKey => $payerClaims) {

            // 🔹 New Transaction Set for each payer
            $transactionSetCount++;
            $stControlNumber = str_pad((string)$transactionSetCount, 4, '0', STR_PAD_LEFT);

            $tx = [];

            // ST – Transaction Set Header
            $tx[] = sprintf('ST*837*%s*005010X222A1', $stControlNumber);

            // BHT – Beginning of Hierarchical Transaction
            // You can vary ref number per ST; for now append TS#
            $tx[] = sprintf(
                'BHT*0019*00*%s%s*%s*%s*CH',
                $controlNumber,
                $stControlNumber,
                $now->format('Ymd'),
                $now->format('Hi')
            );

            // Submitter (NM1*41) and Receiver (NM1*40) + contact (PER*IC)
            $tx[] = sprintf('NM1*41*2*%s*****46*%s', $this->sanitizeX12($companyName), $companyTxCode);
            $tx[] = 'PER*IC*BHARAT MEHTA*TE*9089061085';
            $tx[] = sprintf('NM1*40*2*%s*****46*%s', 'CHANGE HEALTHCARE', $receiverTxCode);

            // Billing Provider Loop (2000A/2010AA)
            $tx[] = 'HL*1*1*20*1';
            // ➜ PRV*BI belongs right after HL in 2000A
            $tx[] = sprintf('PRV*BI*PXC*%s', $companyTaxonomy);
            $tx[] = sprintf('NM1*85*2*%s*****XX*%s', $this->sanitizeX12($companyName), $companyNpi);
            $tx[] = sprintf('N3*%s', $this->sanitizeX12($companyAddr1));
            $tx[] = sprintf('N4*%s*%s*%s', $this->sanitizeX12($companyCity), $companyState, $companyZip);
            $tx[] = sprintf('REF*EI*%s', $companyTxCode);

            // PER*IC block after REF*EI (like Medisoft)
            $tx[] = sprintf(
                'PER*IC*%s*TE*%s*FX*%s',
                $providerContactName,
                $this->sanitizeX12($providerPhone),
                $this->sanitizeX12($providerFax)
            );

            // Now handle all claims for this payer
            $claimsByGroup = $payerClaims->groupBy('claim_group');

            foreach ($claimsByGroup as $claimGroup => $lines) {

                $firstLine     = $lines->first();
                $participantId = $firstLine->ParticipantId;
                $member        = $members->get($participantId);

                if (!$member) {
                    continue;
                }

                // Subscriber HL – you were mimicking Medisoft's HL*1*1*22*1
                $tx[] = 'HL*1*1*22*1';

                // SBR – Subscriber info
                $tx[] = 'SBR*P*18*******MC';

                $lastName  = $this->sanitizeX12($member->LastName ?? 'LAST');
                $firstName = $this->sanitizeX12($member->FirstName ?? 'FIRST');

                $dobRaw = $member->DateOfBirth ?? null;
                try {
                    $dob = $dobRaw ? Carbon::parse($dobRaw)->format('Ymd') : '19000101';
                } catch (\Throwable $e) {
                    $dob = '19000101';
                }

                $sex = strtoupper(substr($member->Sex ?? 'U', 0, 1)); // M/F/U

                // 2010BA – Subscriber Name
                $tx[] = sprintf(
                    'NM1*IL*1*%s*%s****MI*%s',
                    $lastName,
                    $firstName,
                    $firstLine->insurance_id ?? 'UNKNOWN'
                );
                $tx[] = 'N3*' . $this->sanitizeX12($member->Address1 ?? 'ADDRESS');
                $tx[] = sprintf(
                    'N4*%s*%s*%s',
                    $this->sanitizeX12($member->City ?? 'CITY'),
                    $member->State ?? 'NJ',
                    preg_replace('/[^0-9]/', '', $member->ZipCode ?? '07000')
                );
                $tx[] = sprintf('DMG*D8*%s*%s', $dob, $sex);

                // 2010BB – Payer Name (this payer)
                $payerName = $this->sanitizeX12($firstLine->payer_fullname ?? 'PAYER NAME');
                $payerId   = $firstLine->payer_id ?? '99999';

                $tx[] = sprintf('NM1*PR*2*%s*****PI*%s', $payerName, $payerId);

                if (!empty($firstLine->payer_address1)) {
                    $tx[] = 'N3*' . $this->sanitizeX12($firstLine->payer_address1);
                }

                if (!empty($firstLine->payer_city) || !empty($firstLine->payer_state) || !empty($firstLine->payer_zip)) {
                    $tx[] = sprintf(
                        'N4*%s*%s*%s',
                        $this->sanitizeX12($firstLine->payer_city ?? ''),
                        $firstLine->payer_state ?? '',
                        preg_replace('/[^0-9]/', '', $firstLine->payer_zip ?? '')
                    );
                }

                // taxonomy REF on payer – matches Medisoft REF*G2*261QA0600X
                $tx[] = sprintf('REF*G2*%s', $companyTaxonomy);

                // 2300 – Claim level
                $totalCharge = $lines->sum('total_amount');

                /*
                |--------------------------------------------------------------------------
                | CLM01 - Permanent Patient Account / Claim Number
                |--------------------------------------------------------------------------
                */

                $claimId = trim(
                    (string) (
                        $firstLine->claim_no
                        ?? ''
                    )
                );

                if ($claimId === '') {

                    throw new \RuntimeException(
                        'Cannot generate 837P: claim_no is missing for claim ID '
                            . (
                                $firstLine->id
                                ?? 'unknown'
                            )
                            . '.'
                    );
                }

                $serviceFrom = Carbon::parse($lines->min('service_from'))->format('Ymd');
                $serviceTo   = Carbon::parse($lines->max('service_to'))->format('Ymd');

                // Amount like Medisoft: 473.3 instead of 473.30
                $amountStr = rtrim(rtrim(number_format($totalCharge, 2, '.', ''), '0'), '.');

                $tx[] = sprintf(
                    'CLM*%s*%s***99:B:1*N*A*Y*Y',
                    $claimId,
                    $amountStr
                );

                // Prior auth if present → REF*G1
                if (!empty($firstLine->prior_auth_no)) {
                    $tx[] = sprintf('REF*G1*%s', $this->sanitizeX12($firstLine->prior_auth_no));
                }

                // Diagnosis HI segment (primary + secondary)
                $hiParts = ['HI'];

                if (!empty($firstLine->diag1)) {
                    $diag1 = $this->sanitizeX12($firstLine->diag1 ?? 'Z0000');
                    $hiParts[] = 'ABK:' . $diag1;
                }

                if (!empty($firstLine->diag2)) {
                    $diag2 = $this->sanitizeX12($firstLine->diag2 ?? 'Z0000');
                    $hiParts[] = 'ABF:' . $diag2;
                }

                if (count($hiParts) > 1) {
                    // ❗ no "~" here – it will be added in implode at the very end
                    $tx[] = implode('*', $hiParts);
                }

                // 2310B – Rendering Provider
                $tx[] = sprintf(
                    'NM1*82*2*%s*****XX*%s',
                    $this->sanitizeX12($companyName),
                    $companyNpi
                );
                $tx[] = sprintf('PRV*PE*PXC*%s', $companyTaxonomy);
                $tx[] = sprintf('REF*G2*%s', $companyTaxonomy);

                // 2310C – Service Facility Location
                $tx[] = sprintf(
                    'NM1*77*2*%s*****XX*%s',
                    $this->sanitizeX12($companyName),
                    $companyNpi
                );
                $tx[] = sprintf('N3*%s', $this->sanitizeX12($companyAddr1));
                $tx[] = sprintf('N4*%s*%s*%s', $this->sanitizeX12($companyCity), $companyState, $companyZip);

                // 2400 – Service lines
                foreach ($lines as $line) {
                    //$tx[] = sprintf('LX*%d', $line->line_no);
                    $tx[] = 'LX*1';

                    $lineCharge = number_format($line->total_amount, 2, '.', '');
                    $units      = (int) $line->units;

                    $procCode = $this->sanitizeX12($line->proc_code ?? 'S5102');
                    $modifier = $this->sanitizeX12($line->m1 ?? 'U1');

                    // Build diagnosis pointers (1 for HI01, 2 for HI02, etc.)
                    $diagPointers = [];
                    $svcDesc = 'DAY CARE SERVICE ADULT';

                    if (!empty($firstLine->diag1)) {
                        $diagPointers[] = '1';
                    }
                    if (!empty($firstLine->diag2)) {
                        $diagPointers[] = '2';
                    }

                    if (empty($diagPointers) && !empty($firstLine->diag1)) {
                        $diagPointers[] = '1';
                    }

                    $diagPointerStr = implode(':', $diagPointers);

                    // SV1 with dynamic diagnosis pointer(s)
                    $tx[] = sprintf(
                        'SV1*HC:%s:%s::::%s*%s*UN*%d***%s',
                        $procCode,
                        $modifier,
                        $svcDesc,
                        $lineCharge,
                        $units,
                        $diagPointerStr
                    );
                    /*
                    $svcDesc = 'DAY CARE SERVICE ADULT';

                    // SV1 like Medisoft
                    $tx[] = sprintf(
                        'SV1*HC:%s:%s::::%s*%s*UN*%d***1',
                        $procCode,
                        $modifier,
                        $svcDesc,
                        $lineCharge,
                        $units
                    ); */

                    $lineFrom = Carbon::parse($line->service_from)->format('Ymd');
                    $lineTo   = $line->service_to
                        ? Carbon::parse($line->service_to)->format('Ymd')
                        : $lineFrom;

                    if ($lineFrom === $lineTo) {
                        $tx[] = sprintf('DTP*472*D8*%s', $lineFrom);
                    } else {
                        $tx[] = sprintf('DTP*472*RD8*%s-%s', $lineFrom, $lineTo);
                    }

                    // Medisoft-like REF*6R* (using line_no as placeholder)
                    $tx[] = sprintf('REF*6R*%d', $line->line_no);
                }
            }

            // Close this payer's transaction set – SE
            $segmentCount = count($tx) + 1; // SE counts ST + all in tx (but not itself)
            $tx[] = sprintf('SE*%d*%s', $segmentCount, $stControlNumber);

            // Append to main segments list (after GS)
            $segments = array_merge($segments, $tx);
        }

        // One functional group, but multiple transaction sets
        $segments[] = sprintf('GE*%d*1', $transactionSetCount);
        $segments[] = sprintf('IEA*1*%s', $controlNumber);

        // Build final file content
        $content = implode("~\n", $segments) . "~\n";

        $fileName = '837P_' . $now->format('Ymd_His') . '.txt';

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    } // End Method

    /**
     * Small helper: remove * and ~ from strings, trim length a bit.
     */
    protected function sanitizeX12(?string $value, int $maxLen = 60): string
    {
        $value = $value ?? '';
        $value = str_replace(['*', '~', ':', '^', '|', '#', '-'], ' ', $value);
        $value = trim(preg_replace('/\s+/', ' ', $value));
        return substr($value, 0, $maxLen);
    } // End Method

    public function sendPreview()
    {
        // 1) Get base claims only (no joins here)
        $claims = DB::table('claims')
            ->where('status', 'ready')
            ->where('precheck_result', 'valid')
            ->whereNull('claim_no')
            ->orderBy('ParticipantId')   // rough order, we'll refine in PHP
            ->orderBy('service_from')
            ->get();

        if ($claims->isEmpty()) {
            return redirect()
                ->route('claims.draft.index', ['filter' => 'valid'])
                ->with([
                    'message'    => 'No READY claims found to send. Run Check Claims first and fix any invalid claims.',
                    'alert-type' => 'warning',
                ]);
        }

        // Global totals
        $totalClaims       = $claims->count();
        $grandTotalAmount  = $claims->sum('total_amount');

        /*
        * 2) Build lookup for MEMBERS
        *    - Use trimmed ParticipantId as the key.
        */
        $participantIds = $claims->pluck('ParticipantId')
            ->map(function ($id) {
                return trim($id ?? '');
            })
            ->filter()       // remove empty
            ->unique()
            ->values();

        $members = DB::table('billing_members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->whereIn('ParticipantId', $participantIds)
            ->get()
            ->keyBy(function ($m) {
                return trim($m->ParticipantId ?? '');
            });

        /*
        * 3) Build lookup for INSURANCE COMPANIES
        */
        $insuranceIds = $claims->pluck('insurance_name')
            ->filter() // remove nulls
            ->unique()
            ->values();

        $insurances = DB::table('insurance_companies')
            ->select('code', 'full_name')
            ->whereIn('code', $insuranceIds)
            ->get()
            ->keyBy('code');



        /*
        * 4) Attach member + insurance info on each claim
        */
        $claims = $claims->map(function ($c) use ($members, $insurances) {

            $pid = trim($c->ParticipantId ?? '');
            $member    = $members->get($pid);
            $insurance = $insurances->get($c->insurance_name);

            // normalize fields for Blade
            $c->ParticipantId     = $pid;  // trimmed
            $c->member_lastname   = $member->LastName  ?? '';
            $c->member_firstname  = $member->FirstName ?? '';
            $c->code    = $insurance->name   ?? '';

            return $c;
        });

        /*
        * 5) Sort in PHP (no DB collation issues)
        *    sortBy is stable, so we can chain if needed.
        */
        $claims = $claims
            ->sortBy('service_from')
            ->sortBy('ParticipantId')
            ->sortBy('insurance_name')
            ->values(); // reset keys
        /*
        |--------------------------------------------------------------------------
        | Suggested 837P File Name
        |--------------------------------------------------------------------------
        |
        | Example:
        | Claims_08.31.26_to_09.04.26
        |
        | User can change it to:
        | Morning_08.31.26_to_09.04.26
        | Noon_08.31.26_to_09.04.26
        | Morning_BackBilling_08.31.26_to_09.04.26
        |
        */

        $firstServiceDate = $claims
            ->pluck('service_from')
            ->filter()
            ->min();

        $lastServiceDate = $claims
            ->pluck('service_to')
            ->filter()
            ->max();

        $suggestedFilename = '';

        if ($firstServiceDate && $lastServiceDate) {
            $fromDate = \Carbon\Carbon::parse($firstServiceDate)->format('m.d.y');
            $toDate   = \Carbon\Carbon::parse($lastServiceDate)->format('m.d.y');

            $suggestedFilename =
                'Claims_' . $fromDate . '_to_' . $toDate;
        }

        // Group in controller or Blade – here we pass flat and group in Blade
        return view('claims.claims_send_preview', compact(
            'claims',
            'totalClaims',
            'grandTotalAmount',
            'suggestedFilename'
        ));
    } // End Method

    public function sendGenerate(Request $request)
    {
        $claimIds = $request->input('selected_claims', []);

        if (empty($claimIds)) {
            return redirect()
                ->route('claims.send.preview')
                ->with([
                    'message'    => 'No claims selected to send.',
                    'alert-type' => 'warning',
                ]);
        }

        // Reload the selected claims to be safe
        $claims = DB::table('claims')
            ->whereIn('id', $claimIds)
            ->where('status', 'ready')
            ->where('precheck_result', 'valid')
            ->whereNull('claim_no')
            ->orderBy('service_from')
            ->get();

        if ($claims->isEmpty()) {
            return redirect()
                ->route('claims.send.preview')
                ->with([
                    'message'    => 'No eligible claims found (they might have been sent already).',
                    'alert-type' => 'warning',
                ]);
        }

        DB::beginTransaction();
        try {
            /*
            |--------------------------------------------------------------------------
            | Reserve Permanent Claim Numbers
            |--------------------------------------------------------------------------
            */

            $nextNumber =
                $this->reserveClaimNumberRange(
                    $claims->count()
                );


            $updatedClaims = [];

            foreach ($claims as $claim) {
                $claimNo = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
                $nextNumber++;

                // original_claim_no: first time we send, set it = claim_no
                $originalClaimNo = $claim->original_claim_no ?? $claimNo;

                DB::table('claims')
                    ->where('id', $claim->id)
                    ->update([
                        'claim_no'          => $claimNo,
                        'original_claim_no' => $originalClaimNo,
                        'status'            => 'sent',
                        // A/R fields
                        'submitted_date'    => now()->toDateString(),
                        'balance_amount'    => $claim->total_amount,
                        'updated_at'        => now(),
                    ]);

                $existingCharge = DB::table('claim_transactions')
                    ->where('claim_id', $claim->id)
                    ->where('transaction_type', 'charge')
                    ->exists();
                $userCd = Auth::check() ? Auth::user()->user_cd : null;
                if (!$existingCharge) {
                    DB::table('claim_transactions')->insert([
                        'claim_id'         => $claim->id,
                        'claim_no'         => $claimNo,
                        'ParticipantId'    => $claim->ParticipantId,
                        'transaction_date' => now()->toDateString(),
                        'transaction_type' => 'charge',
                        'amount'           => $claim->total_amount,
                        'description'      => 'Claim submitted / 837P generated',
                        'created_by'       => $userCd,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }

                // Keep a copy for EDI generator
                $claim->claim_no = $claimNo;
                $claim->original_claim_no = $originalClaimNo;
                $updatedClaims[] = $claim;
            }

            // 🔹 Generate the actual 837P content here using your existing logic
            //    e.g. $ediContent = app(\App\Services\Generate837PService::class)->buildFile($updatedClaims);
            $ediContent = $this->build837P($updatedClaims); // <-- implement or plug in your existing code

            DB::commit();

            // Return EDI file to download
            $fileName = 'claims_' . now()->format('Ymd_His') . '.txt';

            return response($ediContent)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        } catch (\Throwable $e) {
            DB::rollBack();

            // You might log the error too
            Log::error('Error generating 837P: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()
                ->route('claims.send.preview')
                ->with([
                    'message'    => 'Error generating 837P file: ' . $e->getMessage(),
                    'alert-type' => 'error',
                ]);
        }
    } // End Method


    protected function build837P($claims, ?string $type = null): string
    {
        if (! $claims instanceof \Illuminate\Support\Collection) {
            $claims = collect($claims);
        }

        // 3) Member details
        $members = DB::table('billing_members')
            ->whereIn('ParticipantId', $claims->pluck('ParticipantId')->unique()->values())
            ->get()
            ->keyBy('ParticipantId');

        // 4) Company / billing provider (from config('company'))
        $company      = config('company', []);
        $companyName  = trim($company['comp_nm'] ?? 'MY HOME ADULT DAY CARE');
        $companyAddr1 = trim($company['complc_addr1'] ?? 'ADDRESS LINE 1');
        $companyCity  = trim($company['complc_city'] ?? 'CITY');
        $companyState = trim($company['complc_state'] ?? 'NJ');
        $companyZip   = preg_replace('/[^0-9]/', '', $company['complc_pin'] ?? '07000');
        $companyTxCode = preg_replace('/[^0-9]/', '', $company['tax_id'] ?? '26-0662853');
        $companyNpi   = preg_replace('/[^0-9]/', '', $company['npi'] ?? '1053597096');

        $providerContactName = 'DAY CARE CENTER MY HOME ADULT';

        $providerPhone = preg_replace('/[^0-9]/', '', $company['complc_ph'] ?? '2012918811');
        $providerFax   = preg_replace('/[^0-9]/', '', $company['complc_fax'] ?? '2012918833');

        // Taxonomy (keep full like Medisoft: 261QA0600X)
        $companyTaxonomy = strtoupper(str_replace([' ', '-'], '', $company['taxonomy_code'] ?? '261QA0600X'));

        // 5) Envelope values
        $now           = now();
        $controlNumber = str_pad((string) random_int(1, 999999999), 9, '0', STR_PAD_LEFT);
        $senderId      = 'P62442100000000';   // replace with your submitter ID
        $receiverId    = 'CLAIMSCH0000000';   // replace with payer/clearinghouse ID
        $senderCode    = 'P624421';          // GS02
        $receiverCode  = 'ECGCLAIMS';        // GS03
        $receiverTxCode = '4300';

        $segments = [];

        // Set up required variables like $senderId, $receiverId, etc.
        $now = now();
        $controlNumber = str_pad(rand(1, 999999999), 9, '0', STR_PAD_LEFT);

        $segments = [];

        // ISA – Interchange Control Header
        $segments[] = sprintf(
            'ISA*00*          *01*CYCTRANS00*ZZ*% -15s*ZZ*% -15s*%s*%s*^*00501*%s*0*T*:',
            $senderId,
            $receiverId,
            $now->format('ymd'),
            $now->format('Hi'),
            $controlNumber
        );

        // GS – Functional Group Header
        $segments[] = sprintf(
            'GS*HC*%s*%s*%s*%s*1*X*005010X222A1',
            $senderCode,
            $receiverCode,
            $now->format('Ymd'),
            $now->format('Hi')
        );

        // Group claims by payer (insurance)
        $claimsByPayer = $claims->groupBy(function ($c) {
            return $c->insurance_name ?? 'ZZZ_UNKNOWN';
        })->sortKeys();

        $transactionSetCount = 0;  // how many ST/SE we output
        $claimSeq            = 1;  // CLM id sequence across the whole file

        foreach ($claimsByPayer as $payerNameKey => $payerClaims) {

            // 🔹 New Transaction Set for each payer
            $transactionSetCount++;
            $stControlNumber = str_pad((string)$transactionSetCount, 4, '0', STR_PAD_LEFT);

            $tx = [];

            // ST – Transaction Set Header
            $tx[] = sprintf('ST*837*%s*005010X222A1', $stControlNumber);

            // BHT – Beginning of Hierarchical Transaction
            // You can vary ref number per ST; for now append TS#
            $tx[] = sprintf(
                'BHT*0019*00*%s%s*%s*%s*CH',
                $controlNumber,
                $stControlNumber,
                $now->format('Ymd'),
                $now->format('Hi')
            );

            // Submitter (NM1*41) and Receiver (NM1*40) + contact (PER*IC)
            $tx[] = sprintf('NM1*41*2*%s*****46*%s', $this->sanitizeX12($companyName), $companyTxCode);
            $tx[] = 'PER*IC*BHARAT MEHTA*TE*9089061085';
            $tx[] = sprintf('NM1*40*2*%s*****46*%s', 'CHANGE HEALTHCARE', $receiverTxCode);

            // Billing Provider Loop (2000A/2010AA)
            $tx[] = 'HL*1*1*20*1';
            // ➜ PRV*BI belongs right after HL in 2000A
            $tx[] = sprintf('PRV*BI*PXC*%s', $companyTaxonomy);
            $tx[] = sprintf('NM1*85*2*%s*****XX*%s', $this->sanitizeX12($companyName), $companyNpi);
            $tx[] = sprintf('N3*%s', $this->sanitizeX12($companyAddr1));
            $tx[] = sprintf('N4*%s*%s*%s', $this->sanitizeX12($companyCity), $companyState, $companyZip);
            $tx[] = sprintf('REF*EI*%s', $companyTxCode);

            // PER*IC block after REF*EI (like Medisoft)
            $tx[] = sprintf(
                'PER*IC*%s*TE*%s*FX*%s',
                $providerContactName,
                $this->sanitizeX12($providerPhone),
                $this->sanitizeX12($providerFax)
            );

            // Now handle all claims for this payer
            $claimsByGroup = $payerClaims->groupBy('claim_group');

            foreach ($claimsByGroup as $claimGroup => $lines) {

                $firstLine     = $lines->first();
                $participantId = $firstLine->ParticipantId;
                $member        = $members->get($participantId);

                if (!$member) {
                    continue;
                }

                // Subscriber HL – you were mimicking Medisoft's HL*1*1*22*1
                $tx[] = 'HL*1*1*22*1';

                // SBR – Subscriber info
                $tx[] = 'SBR*P*18*******MC';

                $lastName  = $this->sanitizeX12($member->LastName ?? 'LAST');
                $firstName = $this->sanitizeX12($member->FirstName ?? 'FIRST');

                $dobRaw = $member->DateOfBirth ?? null;
                try {
                    $dob = $dobRaw ? Carbon::parse($dobRaw)->format('Ymd') : '19000101';
                } catch (\Throwable $e) {
                    $dob = '19000101';
                }

                $sex = strtoupper(substr($member->Sex ?? 'U', 0, 1)); // M/F/U

                // 2010BA – Subscriber Name
                $tx[] = sprintf(
                    'NM1*IL*1*%s*%s****MI*%s',
                    $lastName,
                    $firstName,
                    $firstLine->insurance_id ?? 'UNKNOWN'
                );
                $tx[] = 'N3*' . $this->sanitizeX12($member->Address1 ?? 'ADDRESS');
                $tx[] = sprintf(
                    'N4*%s*%s*%s',
                    $this->sanitizeX12($member->City ?? 'CITY'),
                    $member->State ?? 'NJ',
                    preg_replace('/[^0-9]/', '', $member->ZipCode ?? '07000')
                );
                $tx[] = sprintf('DMG*D8*%s*%s', $dob, $sex);

                // 2010BB – Payer Name (this payer)
                $payerName = $this->sanitizeX12($firstLine->payer_fullname ?? 'PAYER NAME');
                $payerId   = $firstLine->payer_id ?? '99999';

                $tx[] = sprintf('NM1*PR*2*%s*****PI*%s', $payerName, $payerId);

                if (!empty($firstLine->payer_address1)) {
                    $tx[] = 'N3*' . $this->sanitizeX12($firstLine->payer_address1);
                }

                if (!empty($firstLine->payer_city) || !empty($firstLine->payer_state) || !empty($firstLine->payer_zip)) {
                    $tx[] = sprintf(
                        'N4*%s*%s*%s',
                        $this->sanitizeX12($firstLine->payer_city ?? ''),
                        $firstLine->payer_state ?? '',
                        preg_replace('/[^0-9]/', '', $firstLine->payer_zip ?? '')
                    );
                }

                // taxonomy REF on payer – matches Medisoft REF*G2*261QA0600X
                $tx[] = sprintf('REF*G2*%s', $companyTaxonomy);

                // 2300 – Claim level
                $totalCharge = $lines->sum('total_amount');
                $claimId     = str_pad((string)$claimSeq, 7, '0', STR_PAD_LEFT);
                $claimSeq++;

                $serviceFrom = Carbon::parse($lines->min('service_from'))->format('Ymd');
                $serviceTo   = Carbon::parse($lines->max('service_to'))->format('Ymd');

                // Amount like Medisoft: 473.3 instead of 473.30
                $amountStr = rtrim(rtrim(number_format($totalCharge, 2, '.', ''), '0'), '.');

                $tx[] = sprintf(
                    'CLM*%s*%s***99:B:1*N*A*Y*Y',
                    $claimId,
                    $amountStr
                );

                // Prior auth if present → REF*G1
                if (!empty($firstLine->prior_auth_no)) {
                    $tx[] = sprintf('REF*G1*%s', $this->sanitizeX12($firstLine->prior_auth_no));
                }

                // Diagnosis HI segment (primary + secondary)
                $hiParts = ['HI'];

                if (!empty($firstLine->diag1)) {
                    $diag1 = $this->sanitizeX12($firstLine->diag1 ?? 'Z0000');
                    $hiParts[] = 'ABK:' . $diag1;
                }

                if (!empty($firstLine->diag2)) {
                    $diag2 = $this->sanitizeX12($firstLine->diag2 ?? 'Z0000');
                    $hiParts[] = 'ABF:' . $diag2;
                }

                if (count($hiParts) > 1) {
                    // ❗ no "~" here – it will be added in implode at the very end
                    $tx[] = implode('*', $hiParts);
                }

                // 2310B – Rendering Provider
                $tx[] = sprintf(
                    'NM1*82*2*%s*****XX*%s',
                    $this->sanitizeX12($companyName),
                    $companyNpi
                );
                $tx[] = sprintf('PRV*PE*PXC*%s', $companyTaxonomy);
                $tx[] = sprintf('REF*G2*%s', $companyTaxonomy);

                // 2310C – Service Facility Location
                $tx[] = sprintf(
                    'NM1*77*2*%s*****XX*%s',
                    $this->sanitizeX12($companyName),
                    $companyNpi
                );
                $tx[] = sprintf('N3*%s', $this->sanitizeX12($companyAddr1));
                $tx[] = sprintf('N4*%s*%s*%s', $this->sanitizeX12($companyCity), $companyState, $companyZip);

                // 2400 – Service lines
                foreach ($lines as $line) {
                    //$tx[] = sprintf('LX*%d', $line->line_no);
                    $tx[] = 'LX*1';

                    $lineCharge = number_format($line->total_amount, 2, '.', '');
                    $units      = (int) $line->units;

                    $procCode = $this->sanitizeX12($line->proc_code ?? 'S5102');
                    $modifier = $this->sanitizeX12($line->m1 ?? 'U1');

                    // Build diagnosis pointers (1 for HI01, 2 for HI02, etc.)
                    $diagPointers = [];
                    $svcDesc = 'DAY CARE SERVICE ADULT';

                    if (!empty($firstLine->diag1)) {
                        $diagPointers[] = '1';
                    }
                    if (!empty($firstLine->diag2)) {
                        $diagPointers[] = '2';
                    }

                    if (empty($diagPointers) && !empty($firstLine->diag1)) {
                        $diagPointers[] = '1';
                    }

                    $diagPointerStr = implode(':', $diagPointers);

                    // SV1 with dynamic diagnosis pointer(s)
                    $tx[] = sprintf(
                        'SV1*HC:%s:%s::::%s*%s*UN*%d***%s',
                        $procCode,
                        $modifier,
                        $svcDesc,
                        $lineCharge,
                        $units,
                        $diagPointerStr
                    );
                    /*
                    $svcDesc = 'DAY CARE SERVICE ADULT';

                    // SV1 like Medisoft
                    $tx[] = sprintf(
                        'SV1*HC:%s:%s::::%s*%s*UN*%d***1',
                        $procCode,
                        $modifier,
                        $svcDesc,
                        $lineCharge,
                        $units
                    ); */

                    $lineFrom = Carbon::parse($line->service_from)->format('Ymd');
                    $lineTo   = $line->service_to
                        ? Carbon::parse($line->service_to)->format('Ymd')
                        : $lineFrom;

                    if ($lineFrom === $lineTo) {
                        $tx[] = sprintf('DTP*472*D8*%s', $lineFrom);
                    } else {
                        $tx[] = sprintf('DTP*472*RD8*%s-%s', $lineFrom, $lineTo);
                    }

                    // Medisoft-like REF*6R* (using line_no as placeholder)
                    $tx[] = sprintf('REF*6R*%d', $line->line_no);
                }
            }

            // Close this payer's transaction set – SE
            $segmentCount = count($tx) + 1; // SE counts ST + all in tx (but not itself)
            $tx[] = sprintf('SE*%d*%s', $segmentCount, $stControlNumber);

            // Append to main segments list (after GS)
            $segments = array_merge($segments, $tx);
        }

        // One functional group, but multiple transaction sets
        $segments[] = sprintf('GE*%d*1', $transactionSetCount);
        $segments[] = sprintf('IEA*1*%s', $controlNumber);

        return implode("~\n", $segments) . "~\n";
    } // End Method

    /* * READY + VALID claims with no claim_no, joined with member + insurance info.
    *  If $ids is provided, filter to those ids only (from preview checkboxes).
    */

    protected function getReadyClaimsForSend(array $ids = []): Collection
    {
        $query = DB::table('claims')
            ->where('status', 'ready')
            ->where('precheck_result', 'valid')
            ->whereNull('claim_no');

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $claims = $query
            ->orderBy('ParticipantId')
            ->orderBy('service_from')
            ->get();

        if ($claims->isEmpty()) {
            return $claims;
        }

        // Member lookup
        $participantIds = $claims->pluck('ParticipantId')
            ->map(fn($id) => trim($id ?? ''))
            ->filter()
            ->unique()
            ->values();

        $members = DB::table('billing_members')
            ->select('ParticipantId', 'lastname', 'firstname')
            ->whereIn('ParticipantId', $participantIds)
            ->get()
            ->keyBy(fn($m) => trim($m->ParticipantId ?? ''));

        // Insurance lookup
        $insuranceIds = $claims->pluck('insurance_name')
            ->filter() // remove nulls
            ->unique()
            ->values();

        $insurances = DB::table('insurance_companies')
            ->select('code', 'full_name')
            ->whereIn('code', $insuranceIds)
            ->get()
            ->keyBy('code');


        // Attach fields
        $claims = $claims->map(function ($c) use ($members, $insurances) {
            $pid       = trim($c->ParticipantId ?? '');
            $member    = $members->get($pid);
            $insurance = $insurances->get($c->insurance_name);

            $c->ParticipantId    = $pid;
            $c->member_lastname  = $member->lastname  ?? '';
            $c->member_firstname = $member->firstname ?? '';
            $c->insurance_name   = $insurance->name   ?? '';

            return $c;
        });

        // Final sort: insurance → Participant → service_from
        return $claims
            ->sortBy('service_from')
            ->sortBy('ParticipantId')
            ->sortBy('insurance_name')
            ->values();
    }

    public function sendPreview_new()
    {
        $claims = $this->getReadyClaimsForSend();

        if ($claims->isEmpty()) {
            return redirect()
                ->route('claims.draft.index', ['filter' => 'valid'])
                ->with([
                    'message'    => 'No READY claims found to send. Run Check Claims first and fix any invalid claims.',
                    'alert-type' => 'warning',
                ]);
        }

        $totalClaims      = $claims->count();
        $grandTotalAmount = $claims->sum('total_amount');

        return view('claims.claims_send_preview', compact(
            'claims',
            'totalClaims',
            'grandTotalAmount'
        ));
    } // End Method

    public function sendPreviewPdf(Request $request)
    {
        // claim_ids[] comes from checkboxes on the preview screen.
        // If you’re not using checkboxes yet, we can use all ready claims.
        $ids = $request->input('claim_ids', []);

        $claims = $this->getReadyClaimsForSend($ids);

        if ($claims->isEmpty()) {
            return back()->with([
                'message'    => 'No claims found to generate PDF.',
                'alert-type' => 'warning',
            ]);
        }

        $totalClaims      = $claims->count();
        $grandTotalAmount = $claims->sum('total_amount');

        // Group in controller, or you can group in Blade as well
        $claimsByInsurance = $claims->groupBy('insurance_name');

        $pdf = Pdf::loadView('claims.claims_send_pdf', [
            'claimsByInsurance' => $claimsByInsurance,
            'totalClaims'       => $totalClaims,
            'grandTotalAmount'  => $grandTotalAmount,
        ])->setPaper('letter', 'portrait');

        $fileName = 'Claims_Batch_Summary_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    } // End Method

    public function sendConfirmed(Request $request)
    {


        // 1) Selected claims from the preview screen (if you have checkboxes)
        $ids = $request->input('claim_ids', []);  // or [] = all ready claims

        // If you have Morning/Noon type here (A/N), grab it too
        $type = $request->input('type'); // optional

        // 2) Load claims to be sent (with member + insurance info)
        $claims = $this->getReadyClaimsForSend($ids);

        if ($claims->isEmpty()) {
            return redirect()
                ->route('claims.draft.index', ['filter' => 'valid'])
                ->with([
                    'message'    => 'No READY claims found to send.',
                    'alert-type' => 'warning',
                ]);
        }

        // 3) Assign claim_no and update status inside a transaction
        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | Reserve Permanent Claim Numbers
            |--------------------------------------------------------------------------
            */

            $nextNumber =
                $this->reserveClaimNumberRange(
                    $claims->count()
                );


            $claimIdsToUpdate = $claims->pluck('id')->all();
            $updatedClaims    = collect();


            foreach ($claims as $claim) {

                $newClaimNo = str_pad(
                    $nextNumber,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

                $nextNumber++;

                // Update DB
                DB::table('claims')
                    ->where('id', $claim->id)
                    ->update([
                        'claim_no'        => $newClaimNo,
                        'original_claim_no' => $claim->original_claim_no ?: $newClaimNo,
                        'status'          => 'sent',     // or whatever enum value you're using
                        'submitted_date' => now()->toDateString(),
                        'balance_amount' => $claim->total_amount,
                        'updated_at'      => now(),
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Create A/R charge transaction
                |--------------------------------------------------------------------------
                | This creates the open receivable when claim is submitted/sent.
                */
                $existingCharge = DB::table('claim_transactions')
                    ->where('claim_id', $claim->id)
                    ->where('transaction_type', 'charge')
                    ->exists();
                $userCd = Auth::check() ? Auth::user()->user_cd : null;

                if (!$existingCharge) {
                    DB::table('claim_transactions')->insert([
                        'claim_id'         => $claim->id,
                        'claim_no'         => $newClaimNo,
                        'ParticipantId'    => $claim->ParticipantId,
                        'transaction_date' => now()->toDateString(),
                        'transaction_type' => 'charge',
                        'amount'           => $claim->total_amount,
                        'description'      => 'Claim submitted / 837P generated',
                        'created_by'       => $userCd,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }

                // Also update the in-memory object so PDF & 837 see the new claim_no
                $claim->claim_no         = $newClaimNo;
                $claim->original_claim_no = $claim->original_claim_no ?: $newClaimNo;
                $claim->submitted_date    = now()->toDateString();
                $claim->balance_amount    = $claim->total_amount;
                $claim->status           = 'sent';

                $updatedClaims->push($claim);
            }

            // 4) Build 837P using the updated claims (with claim_no)
            $ediContent = $this->build837P($updatedClaims, $type);

            // 5) Build PDF (grouped by insurance) using the same updated claims
            $totalClaims      = $updatedClaims->count();
            $grandTotalAmount = $updatedClaims->sum('total_amount');
            $claimsByInsurance = $updatedClaims->groupBy('insurance_name');

            $pdf = Pdf::loadView('claims.claims_send_pdf', [
                'claimsByInsurance' => $claimsByInsurance,
                'totalClaims'       => $totalClaims,
                'grandTotalAmount'  => $grandTotalAmount,
            ])->setPaper('letter', 'portrait');

            // 6) Save both files in storage (no manual step)
            $timestamp = now()->format('Ymd_His');
            $baseName  = "claims_batch_{$timestamp}";

            // EDI file
            Storage::put("claims_batches/{$baseName}.txt", $ediContent);

            // PDF file
            Storage::put("claims_batches/{$baseName}.pdf", $pdf->output());

            DB::commit();

            // 7) Finally, return 837P to user as download (PDF is already stored)
            return response($ediContent)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', "attachment; filename=\"{$baseName}.txt\"");
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('claims.draft.index', ['filter' => 'valid'])
                ->with([
                    'message'    => 'Error while sending claims: ' . $e->getMessage(),
                    'alert-type' => 'error',
                ]);
        }
    } //End Method

    public function sendGenerateWithPdf(Request $request)
    {
        // 1) Get selected claims
        $claimIds = $request->input('selected_claims', []);

        if (empty($claimIds)) {
            return redirect()
                ->route('claims.send.preview')
                ->with([
                    'message'    => 'No claims selected to send.',
                    'alert-type' => 'warning',
                ]);
        }

        // 2) Reload selected claims with same filters as old sendGenerate
        $claims = DB::table('claims')
            ->whereIn('id', $claimIds)
            ->where('status', 'ready')
            ->where('precheck_result', 'valid')
            ->whereNull('claim_no')
            ->orderBy('service_from')
            ->get();

        if ($claims->isEmpty()) {
            return redirect()
                ->route('claims.send.preview')
                ->with([
                    'message'    => 'No eligible claims found (they might have been sent already).',
                    'alert-type' => 'warning',
                ]);
        }

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | 3) Reserve Permanent Claim Numbers
            |--------------------------------------------------------------------------
            */

            $nextNumber =
                $this->reserveClaimNumberRange(
                    $claims->count()
                );


            $updatedClaims = [];

            // 4) Assign claim_no / original_claim_no / status = sent
            foreach ($claims as $claim) {
                $claimNo = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
                $nextNumber++;

                // first time: original_claim_no = claim_no
                $originalClaimNo = $claim->original_claim_no ?? $claimNo;

                DB::table('claims')
                    ->where('id', $claim->id)
                    ->update([
                        'claim_no'          => $claimNo,
                        'original_claim_no' => $originalClaimNo,
                        'status'            => 'sent',
                        'updated_at'        => now(),
                    ]);

                // keep these on the in-memory object for EDI + PDF
                $claim->claim_no          = $claimNo;
                $claim->original_claim_no = $originalClaimNo;

                $updatedClaims[] = $claim;
            }

            // 5) Generate 837P content using UPDATED claims
            $claimsForEdi = collect($updatedClaims);

            /* --------------------------------------------------------
           5) Attach member names (NO JOINS → NO COLLATION ERRORS)
        ---------------------------------------------------------*/
            $participantIds = $claimsForEdi->pluck('ParticipantId')
                ->filter()
                ->map(fn($id) => trim($id))
                ->unique()
                ->values()
                ->all();

            $members = DB::table('billing_members')
                ->select('ParticipantId', 'LastName', 'FirstName')
                ->whereIn(DB::raw('TRIM(ParticipantId)'), $participantIds)
                ->get();

            $membersById = $members->keyBy(fn($m) => trim($m->ParticipantId));

            $claimsForEdi = $claimsForEdi->map(function ($claim) use ($membersById) {
                $member = $membersById[trim($claim->ParticipantId)] ?? null;

                $claim->member_lastname  = $member->LastName  ?? '';
                $claim->member_firstname = $member->FirstName ?? '';

                return $claim;
            });

            $ediContent   = $this->build837P($claimsForEdi);

            // totals
            $totalClaims          = $claimsForEdi->count();
            $totalAmount          = $claimsForEdi->sum('total_amount');
            $batchTimestampCarbon = now();
            $batchTimestamp       = $batchTimestampCarbon->format('Ymd_His');

            // 6) Save files (TXT + PDF) on public disk
            $ediDir      = 'claims_batches';
            $ediFilename = "837P_batch_{$batchTimestamp}.txt";
            $pdfFilename = "ClaimsBatch_{$batchTimestamp}.pdf";

            Storage::disk('public')->makeDirectory($ediDir);
            Storage::disk('public')->put($ediDir . '/' . $ediFilename, $ediContent);

            $pdf = Pdf::loadView('claims.claims_send_batch_pdf', [
                'claims'         => $claimsForEdi,
                'batchTimestamp' => $batchTimestamp,
                'ediFilename'    => $ediFilename,
            ])->setPaper('letter', 'portrait');

            Storage::disk('public')->put($ediDir . '/' . $pdfFilename, $pdf->output());

            $userCd = Auth::check() ? Auth::user()->user_cd : null;

            // 7) Save batch record
            $batch = ClaimBatch::create([
                'batch_timestamp' => $batchTimestampCarbon,
                'edi_filename'    => $ediFilename,
                'pdf_filename'    => $pdfFilename,
                'total_claims'    => $totalClaims,
                'total_amount'    => $totalAmount,
                'created_by'      => $userCd,
            ]);

            // 8) Link all claims to this batch
            foreach ($claimsForEdi as $claim) {
                ClaimBatchItem::create([
                    'claim_batch_id' => $batch->id,
                    'claim_id'       => $claim->id,
                ]);
            }

            DB::commit();

            // 9) Redirect back with info
            return redirect()
                ->route('claims.send.preview')
                ->with([
                    'message'     => '837P + PDF generated and claims marked as SENT.',
                    'alert-type'  => 'success',
                    'edi_file'    => $ediFilename,
                    'pdf_file'    => $pdfFilename,
                ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('claims.send.preview')
                ->with([
                    'message'    => 'Error while generating 837P/PDF: ' . $e->getMessage(),
                    'alert-type' => 'error',
                ]);
        }
    } // End Method

    public function batchHistory()
    {
        $batches = ClaimBatch::orderBy('batch_timestamp', 'desc')
            ->paginate(20);

        return view('claims.claims_batches_index', compact('batches'));
    } // End MEthod

    public function batchShow($batchId)
    {
        $batch = ClaimBatch::findOrFail($batchId);

        $claimIds = ClaimBatchItem::where('claim_batch_id', $batch->id)
            ->pluck('claim_id');

        if ($claimIds->isEmpty()) {
            return redirect()
                ->route('claims.batches.index')
                ->with([
                    'message'    => 'No claims linked to this batch.',
                    'alert-type' => 'warning',
                ]);
        }

        // Get current claims data (live from claims table)
        $claims = DB::table('claims')
            ->whereIn('id', $claimIds)
            ->orderBy('service_from')
            ->get();

        //         dd([
        //     'batch_id' => $batch->id,

        //     'claim_ids' => $claimIds->all(),

        //     'claims' => $claims->map(function ($claim) {
        //         return [
        //             'id'            => $claim->id,
        //             'claim_no'      => $claim->claim_no,
        //             'ParticipantId' => $claim->ParticipantId,
        //             'service_from'  => $claim->service_from,
        //             'service_to'    => $claim->service_to,
        //         ];
        //     })->all(),
        // ]);

        // 2) Collect ParticipantIds from claims (trimmed & unique)
        $participantIds = $claims->pluck('ParticipantId')
            ->filter()
            ->map(function ($id) {
                return trim($id);
            })
            ->unique()
            ->values()
            ->all();

        // 3) Load members in a separate query – still NO joins, so no collation issues
        $members = DB::table('billing_members')
            ->select('ParticipantId', 'LastName', 'FirstName')
            // TRIM on column is safe here because it's only comparing to PHP strings,
            // not to another column with different collation.
            ->whereIn(DB::raw('TRIM(ParticipantId)'), $participantIds)
            ->get();

        // 4) Build a map: trimmed ParticipantId => member record
        $membersById = $members->keyBy(function ($m) {
            return trim($m->ParticipantId);
        });

        // 5) Attach member_lastname / member_firstname to each claim in PHP
        $claims = $claims->map(function ($claim) use ($membersById) {
            $key    = trim($claim->ParticipantId ?? '');
            $member = $membersById[$key] ?? null;

            $claim->member_lastname  = $member->LastName  ?? null;
            $claim->member_firstname = $member->FirstName ?? null;

            return $claim;
        });

        $totalClaims = $claims->count();
        $totalAmount = $claims->sum('total_amount');

        return view('claims.claims_batch_show', compact(
            'batch',
            'claims',
            'totalClaims',
            'totalAmount'
        ));
    } // End Method

    public function memberPayments($participantId)
    {
        try {

            $participantId =
                trim(
                    (string) $participantId
                );


            $payments =
                collect();


            if ($participantId === '') {

                return response()
                    ->json([]);
            }


            /*
            |--------------------------------------------------------------------------
            | 1. Historical Medisoft Payment / Adjustment History
            |--------------------------------------------------------------------------
            |
            | Keep the historical Medisoft display exactly as before.
            |--------------------------------------------------------------------------
            */

            $medisoftPayments =
                DB::table('medisoft_payment_history')
                ->whereRaw(
                    'TRIM(ParticipantId) = ?',
                    [$participantId]
                )
                ->orderByDesc('payment_date')
                ->orderByDesc('medisoft_item_no')
                ->get([
                    'medisoft_item_no',
                    'payment_date',
                    'transaction_type',
                    'description',
                    'attending_provider',
                    'original_amount',
                    'payment_amount',
                    'check_number',
                    'unapplied_amount',
                ]);


            foreach (
                $medisoftPayments as $payment
            ) {

                $description =
                    trim(
                        (string) (
                            $payment->description
                            ?? ''
                        )
                    );


                $checkNumber =
                    trim(
                        (string) (
                            $payment->check_number
                            ?? ''
                        )
                    );


                $payerName =
                    $description;


                /*
                |--------------------------------------------------------------------------
                | Remove Check Number From Beginning Of Description
                |--------------------------------------------------------------------------
                */

                if (
                    $checkNumber !== ''
                    &&
                    strpos(
                        $payerName,
                        $checkNumber
                    ) === 0
                ) {

                    $payerName =
                        trim(
                            substr(
                                $payerName,
                                strlen(
                                    $checkNumber
                                )
                            )
                        );
                }


                if ($payerName === '') {

                    $payerName =
                        'MEDISOFT';
                }


                $payments->push([

                    'payment_date' =>
                    $payment->payment_date,

                    'transaction_type' =>
                    $payment->transaction_type
                        ?? '',

                    'pay_adj_code' =>
                    $payment->transaction_type
                        ?? '',

                    'code_description' =>
                    '',

                    'claim_no' =>
                    '',

                    'payer_name' =>
                    $payerName,

                    'description' =>
                    $description,

                    'provider' =>
                    $payment->attending_provider
                        ?? '',

                    'amount' =>
                    round(
                        (float) (
                            $payment->original_amount
                            ?? 0
                        ),
                        2
                    ),

                    'payment_amount' =>
                    round(
                        (float) (
                            $payment->payment_amount
                            ?? 0
                        ),
                        2
                    ),

                    'trace_number' =>
                    $checkNumber,

                    'unapplied_amount' =>
                    round(
                        (float) (
                            $payment->unapplied_amount
                            ?? 0
                        ),
                        2
                    ),

                    'source' =>
                    'MEDISOFT',

                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | 2. Current / Future ERA Transaction History
            |--------------------------------------------------------------------------
            |
            | Read from claim_transactions.
            |
            | Include:
            |
            | 1. Normal ERA transactions:
            |       era_file_id IS NOT NULL
            |
            | 2. Legacy ERA CSV recovery payments:
            |       description LIKE 'Legacy ERA CSV payment%'
            |--------------------------------------------------------------------------
            */

            $claimIds =
                DB::table('claims')
                ->whereRaw(
                    'TRIM(ParticipantId) = ?',
                    [$participantId]
                )
                ->pluck('id')
                ->filter()
                ->values();


            if (
                $claimIds->isNotEmpty()
            ) {

                $eraTransactions =
                    DB::table(
                        'claim_transactions as ct'
                    )
                    ->leftJoin(
                        'era_files as ef',
                        'ef.id',
                        '=',
                        'ct.era_file_id'
                    )
                    ->leftJoin(
                        'claims as c',
                        'c.id',
                        '=',
                        'ct.claim_id'
                    )
                    ->whereIn(
                        'ct.claim_id',
                        $claimIds
                    )

                    /*
                    |--------------------------------------------------------------------------
                    | Normal ERA + Legacy CSV ERA
                    |--------------------------------------------------------------------------
                    */

                    ->where(
                        function ($q) {

                            $q->whereNotNull(
                                'ct.era_file_id'
                            )
                                ->orWhere(
                                    'ct.description',
                                    'like',
                                    'Legacy ERA CSV payment%'
                                );
                        }
                    )

                    ->orderByDesc(
                        'ct.transaction_date'
                    )
                    ->orderByDesc(
                        'ct.id'
                    )
                    ->get([

                        'ct.id',
                        'ct.claim_id',
                        'ct.era_file_id',
                        'ct.era_claim_id',
                        'ct.claim_no',

                        /*
                        |--------------------------------------------------------------------------
                        | Legacy CSV payer ICN is stored here
                        |--------------------------------------------------------------------------
                        */

                        'ct.payer_claim_number',

                        'ct.transaction_date',
                        'ct.transaction_type',
                        'ct.amount',
                        'ct.group_code',
                        'ct.reason_code',
                        'ct.description',

                        /*
                        |--------------------------------------------------------------------------
                        | Normal ERA Information
                        |--------------------------------------------------------------------------
                        */

                        'ef.payment_date as era_payment_date',
                        'ef.payer_name',
                        'ef.trace_number',

                        /*
                        |--------------------------------------------------------------------------
                        | Claim Information
                        |--------------------------------------------------------------------------
                        |
                        | Used as payer-name fallback for legacy CSV payments because
                        | they do not have an era_files record.
                        |--------------------------------------------------------------------------
                        */

                        'c.payer_fullname as claim_payer_name',
                        'c.provider',
                        'c.line_no',

                    ]);


                foreach (
                    $eraTransactions
                    as $transaction
                ) {

                    $transactionType =
                        strtolower(
                            trim(
                                (string) (
                                    $transaction
                                    ->transaction_type
                                    ?? ''
                                )
                            )
                        );


                    $groupCode =
                        strtoupper(
                            trim(
                                (string) (
                                    $transaction
                                    ->group_code
                                    ?? ''
                                )
                            )
                        );


                    $reasonCode =
                        trim(
                            (string) (
                                $transaction
                                ->reason_code
                                ?? ''
                            )
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Determine Legacy CSV Payment
                    |--------------------------------------------------------------------------
                    */

                    $isLegacyCsvPayment =
                        str_starts_with(
                            (string) (
                                $transaction->description
                                ?? ''
                            ),
                            'Legacy ERA CSV payment'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Display Pay / Adjustment Code
                    |--------------------------------------------------------------------------
                    */

                    $displayCode =
                        'ERA';


                    $codeDescription =
                        'ERA Payment';


                    if (
                        $transactionType
                        ===
                        'contractual_adjustment'
                    ) {

                        $displayCode =
                            $groupCode !== ''
                            ? $groupCode
                            : 'CO';


                        if ($reasonCode !== '') {

                            $displayCode .=
                                '-'
                                .
                                $reasonCode;
                        }


                        $codeDescription =
                            'Contractual Adjustment';
                    } elseif (
                        $transactionType
                        ===
                        'patient_responsibility'
                    ) {

                        $displayCode =
                            $groupCode !== ''
                            ? $groupCode
                            : 'PR';


                        if ($reasonCode !== '') {

                            $displayCode .=
                                '-'
                                .
                                $reasonCode;
                        }


                        $codeDescription =
                            'Patient Responsibility';
                    } elseif (
                        $transactionType
                        ===
                        'deduction'
                    ) {

                        $displayCode =
                            $groupCode !== ''
                            ? $groupCode
                            : 'ADJ';


                        if ($reasonCode !== '') {

                            $displayCode .=
                                '-'
                                .
                                $reasonCode;
                        }


                        $codeDescription =
                            'Insurance Adjustment';
                    } elseif (
                        $transactionType
                        ===
                        'payment'
                    ) {

                        $displayCode =
                            'ERA';


                        $codeDescription =
                            $isLegacyCsvPayment
                            ? 'Legacy ERA Payment'
                            : 'ERA Payment';
                    } else {

                        $displayCode =
                            strtoupper(
                                $transactionType
                            );


                        $codeDescription =
                            ucwords(
                                str_replace(
                                    '_',
                                    ' ',
                                    $transactionType
                                )
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Description
                    |--------------------------------------------------------------------------
                    */

                    $description =
                        trim(
                            (string) (
                                $transaction
                                ->description
                                ?? ''
                            )
                        );


                    if ($description === '') {

                        $description =
                            $codeDescription;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Payer Name
                    |--------------------------------------------------------------------------
                    |
                    | Normal ERA:
                    |     era_files.payer_name
                    |
                    | Legacy CSV:
                    |     claims.payer_fullname
                    |--------------------------------------------------------------------------
                    */

                    $payerName =
                        trim(
                            (string) (
                                $transaction
                                ->payer_name
                                ??
                                $transaction
                                ->claim_payer_name
                                ??
                                ''
                            )
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Check / Trace Number
                    |--------------------------------------------------------------------------
                    |
                    | Normal ERA:
                    |     era_files.trace_number
                    |
                    | Legacy CSV:
                    |     claim_transactions.payer_claim_number
                    |--------------------------------------------------------------------------
                    */

                    $traceNumber =
                        trim(
                            (string) (
                                $transaction
                                ->trace_number
                                ??
                                $transaction
                                ->payer_claim_number
                                ??
                                ''
                            )
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Add ERA Transaction Row
                    |--------------------------------------------------------------------------
                    */

                    $payments->push([

                        'payment_date' =>
                        $transaction
                            ->transaction_date
                            ??
                            $transaction
                            ->era_payment_date,

                        'transaction_type' =>
                        $displayCode,

                        'pay_adj_code' =>
                        $displayCode,

                        'code_description' =>
                        $codeDescription,

                        'claim_no' =>
                        $transaction->claim_no
                            ?? '',

                        'payer_name' =>
                        $payerName,

                        'description' =>
                        $description,

                        'provider' =>
                        $transaction->provider
                            ?? '',

                        'amount' =>
                        round(
                            (float) (
                                $transaction->amount
                                ?? 0
                            ),
                            2
                        ),

                        'payment_amount' =>
                        $transactionType === 'payment'
                            ? round(
                                (float) (
                                    $transaction->amount
                                    ?? 0
                                ),
                                2
                            )
                            : 0.00,

                        'trace_number' =>
                        $traceNumber,

                        'unapplied_amount' =>
                        0.00,

                        'source' =>
                        $isLegacyCsvPayment
                            ? 'LEGACY ERA CSV'
                            : 'ERA',

                    ]);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | 3. Sort Medisoft + ERA Together
            |--------------------------------------------------------------------------
            */

            $payments =
                $payments
                ->sortByDesc(
                    function ($row) {

                        return
                            $row['payment_date']
                            ?? '1900-01-01';
                    }
                )
                ->values();


            return response()
                ->json(
                    $payments
                );
        } catch (\Throwable $e) {

            Log::error(
                'memberPayments error',
                [

                    'participantId' =>
                    $participantId,

                    'message' =>
                    $e->getMessage(),

                ]
            );


            return response()
                ->json(
                    [

                        'error' =>
                        true,

                        'message' =>
                        'Server error: '
                            .
                            $e->getMessage(),

                    ],
                    500
                );
        }
    } // End Method


    // protected function build837P_new($claims, ?string $type = null): string
    // {
    //     if (! $claims instanceof \Illuminate\Support\Collection) {
    //         $claims = collect($claims);
    //     }

    //     if ($claims->isEmpty()) {
    //         throw new \RuntimeException('No claims found for 837P generation.');
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | X12 Clearinghouse Settings
    //     |--------------------------------------------------------------------------
    //     */

    //     $clearinghouse = DB::table('x12_clearinghouse_settings')
    //         ->where('status', 'A')
    //         ->where('is_default', 1)
    //         ->first();

    //     if (! $clearinghouse) {
    //         $clearinghouse = DB::table('x12_clearinghouse_settings')
    //             ->where('status', 'A')
    //             ->orderBy('id')
    //             ->first();
    //     }

    //     if (! $clearinghouse) {
    //         throw new \RuntimeException(
    //             'No active clearinghouse setup found in X12 Settings.'
    //         );
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | X12 Provider Settings
    //     |--------------------------------------------------------------------------
    //     */

    //     $provider = DB::table('x12_provider_settings')
    //         ->where('status', 'A')
    //         ->where('is_default', 1)
    //         ->first();

    //     if (! $provider) {
    //         $provider = DB::table('x12_provider_settings')
    //             ->where('status', 'A')
    //             ->orderBy('id')
    //             ->first();
    //     }

    //     if (! $provider) {
    //         throw new \RuntimeException(
    //             'No active provider setup found in X12 Settings.'
    //         );
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Reserve X12 control numbers
    //     |--------------------------------------------------------------------------
    //     */

    //     $controlNumbers = $this->reserveX12ControlNumbers(
    //         (int) $clearinghouse->id
    //     );

    //     $controlNumber = $controlNumbers['isa'];
    //     $gsControlNumber = $controlNumbers['gs'];

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Member details
    //     |--------------------------------------------------------------------------
    //     */

    //     $participantIds = $claims
    //         ->pluck('ParticipantId')
    //         ->map(fn ($id) => trim((string) $id))
    //         ->filter()
    //         ->unique()
    //         ->values();

    //     $members = DB::table('billing_members')
    //         ->whereIn('ParticipantId', $participantIds)
    //         ->get()
    //         ->keyBy(
    //             fn ($member) => trim((string) $member->ParticipantId)
    //         );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Recover insurance codes from claims table
    //     |--------------------------------------------------------------------------
    //     |
    //     | This is important because getReadyClaimsForSend() may attach display names.
    //     | For payer lookup, we want the original claims.insurance_name code.
    //     |
    //     */

    //     $claimIds = $claims
    //         ->pluck('id')
    //         ->filter()
    //         ->unique()
    //         ->values();

    //     $claimInsuranceCodes = DB::table('claims')
    //         ->whereIn('id', $claimIds)
    //         ->pluck('insurance_name', 'id');

    //     $insuranceCodes = $claimInsuranceCodes
    //         ->values()
    //         ->map(fn ($code) => trim((string) $code))
    //         ->filter()
    //         ->unique()
    //         ->values();

    //     $insuranceCompanies = DB::table('insurance_companies')
    //         ->whereIn('code', $insuranceCodes)
    //         ->where('active', 1)
    //         ->get()
    //         ->keyBy(
    //             fn ($insurance) => trim((string) $insurance->code)
    //         );

    //     foreach ($insuranceCodes as $insuranceCode) {
    //         $payer = $insuranceCompanies->get($insuranceCode);

    //         if (! $payer) {
    //             throw new \RuntimeException(
    //                 "Active insurance/payer setup missing for {$insuranceCode}."
    //             );
    //         }

    //         if (empty(trim((string) $payer->payer_id))) {
    //             throw new \RuntimeException(
    //                 "Electronic payer ID is missing for {$insuranceCode}."
    //             );
    //         }
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Provider values from x12_provider_settings
    //     |--------------------------------------------------------------------------
    //     */

    //     $companyName = trim(
    //         (string) ($provider->provider_name ?? 'ABC ADULT DAY CARE')
    //     );

    //     $companyAddr1 = trim(
    //         (string) ($provider->address1 ?? 'ADDRESS LINE 1')
    //     );

    //     $companyCity = trim(
    //         (string) ($provider->city ?? 'CITY')
    //     );

    //     $companyState = strtoupper(
    //         trim((string) ($provider->state ?? 'NJ'))
    //     );

    //     $companyZip = preg_replace(
    //         '/[^0-9]/',
    //         '',
    //         (string) ($provider->zip_code ?? $provider->zip ?? '07000')
    //     );

    //     $companyTxCode = preg_replace(
    //         '/[^0-9]/',
    //         '',
    //         (string) ($provider->tax_id ?? '123456789')
    //     );

    //     $companyNpi = preg_replace(
    //         '/[^0-9]/',
    //         '',
    //         (string) ($provider->npi ?? '1000000000')
    //     );

    //     $providerContactName = trim(
    //         (string) (
    //             $provider->contact_name
    //             ?? $provider->provider_name
    //             ?? $companyName
    //         )
    //     );

    //     $providerPhone = preg_replace(
    //         '/[^0-9]/',
    //         '',
    //         (string) ($provider->phone ?? '')
    //     );

    //     $providerFax = preg_replace(
    //         '/[^0-9]/',
    //         '',
    //         (string) ($provider->fax ?? '')
    //     );

    //     $companyTaxonomy = strtoupper(
    //         str_replace(
    //             [' ', '-'],
    //             '',
    //             (string) ($provider->taxonomy_code ?? '261QA0600X')
    //         )
    //     );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Clearinghouse / envelope values from x12_clearinghouse_settings
    //     |--------------------------------------------------------------------------
    //     */

    //     $now = now();

    //     $senderId = trim(
    //         (string) ($clearinghouse->isa_sender_id ?? '')
    //     );

    //     $receiverId = trim(
    //         (string) ($clearinghouse->isa_receiver_id ?? '')
    //     );

    //     $senderCode = trim(
    //         (string) ($clearinghouse->gs_sender_code ?? $senderId)
    //     );

    //     $receiverCode = trim(
    //         (string) ($clearinghouse->gs_receiver_code ?? $receiverId)
    //     );

    //     $receiverTxCode = trim(
    //         (string) (
    //             $clearinghouse->receiver_transaction_id
    //             ?? $clearinghouse->receiver_id
    //             ?? '4300'
    //         )
    //     );

    //     $submitterName = trim(
    //         (string) (
    //             $clearinghouse->submitter_name
    //             ?? $companyName
    //         )
    //     );

    //     $submitterId = trim(
    //         (string) (
    //             $clearinghouse->submitter_id
    //             ?? $companyTxCode
    //         )
    //     );

    //     $submitterContactName = trim(
    //         (string) (
    //             $clearinghouse->submitter_contact_name
    //             ?? $submitterName
    //         )
    //     );

    //     $submitterPhone = preg_replace(
    //         '/[^0-9]/',
    //         '',
    //         (string) ($clearinghouse->submitter_phone ?? '')
    //     );

    //     $receiverName = trim(
    //         (string) (
    //             $clearinghouse->receiver_name
    //             ?? 'CHANGE HEALTHCARE'
    //         )
    //     );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | ISA fixed-width values
    //     |--------------------------------------------------------------------------
    //     */

    //     $isaAuthQualifier = $clearinghouse->isa_authorization_qualifier ?? '00';

    //     $isaAuthInfo = str_pad(
    //         substr(
    //             (string) ($clearinghouse->isa_authorization_information ?? ''),
    //             0,
    //             10
    //         ),
    //         10,
    //         ' ',
    //         STR_PAD_RIGHT
    //     );

    //     $isaSecurityQualifier = $clearinghouse->isa_security_qualifier ?? '01';

    //     $isaSecurityInfo = str_pad(
    //         substr(
    //             (string) ($clearinghouse->isa_security_information ?? 'CYCTRANS00'),
    //             0,
    //             10
    //         ),
    //         10,
    //         ' ',
    //         STR_PAD_RIGHT
    //     );

    //     $isaSenderQualifier = $clearinghouse->isa_sender_qualifier ?? 'ZZ';
    //     $isaReceiverQualifier = $clearinghouse->isa_receiver_qualifier ?? 'ZZ';

    //     $isaSenderId = str_pad(
    //         substr($senderId, 0, 15),
    //         15,
    //         ' ',
    //         STR_PAD_RIGHT
    //     );

    //     $isaReceiverId = str_pad(
    //         substr($receiverId, 0, 15),
    //         15,
    //         ' ',
    //         STR_PAD_RIGHT
    //     );

    //     $isaRepetitionSeparator = $clearinghouse->isa_repetition_separator ?? '^';
    //     $isaVersion = $clearinghouse->isa_version ?? '00501';
    //     $isaAckRequested = $clearinghouse->isa_ack_requested ?? '0';
    //     $isaUsageIndicator = $clearinghouse->isa_usage_indicator ?? 'T';
    //     $isaComponentSeparator = $clearinghouse->isa_component_separator ?? ':';

    //     $implementationVersion =
    //         $clearinghouse->implementation_version ?? '005010X222A1';

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Start segments - keep old method format
    //     |--------------------------------------------------------------------------
    //     */

    //     $segments = [];

    //     $segments[] = sprintf(
    //         'ISA*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s',
    //         $isaAuthQualifier,
    //         $isaAuthInfo,
    //         $isaSecurityQualifier,
    //         $isaSecurityInfo,
    //         $isaSenderQualifier,
    //         $isaSenderId,
    //         $isaReceiverQualifier,
    //         $isaReceiverId,
    //         $now->format('ymd'),
    //         $now->format('Hi'),
    //         $isaRepetitionSeparator,
    //         $isaVersion,
    //         $controlNumber,
    //         $isaAckRequested,
    //         $isaUsageIndicator,
    //         $isaComponentSeparator
    //     );
    //     $segments[] = sprintf(
    //         'GS*HC*%s*%s*%s*%s*%s*X*%s',
    //         $this->sanitizeX12($senderCode),
    //         $this->sanitizeX12($receiverCode),
    //         $now->format('Ymd'),
    //         $now->format('Hi'),
    //         $gsControlNumber,
    //         $implementationVersion
    //     );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Group claims by payer - same as old design
    //     |--------------------------------------------------------------------------
    //     */

    //     $claimsByPayer = $claims
    //         ->groupBy(function ($claim) use ($claimInsuranceCodes) {
    //             return trim(
    //                 (string) (
    //                     $claimInsuranceCodes->get($claim->id)
    //                     ?? $claim->insurance_code
    //                     ?? $claim->insurance_name
    //                     ?? 'ZZZ_UNKNOWN'
    //                 )
    //             );
    //         })
    //         ->sortKeys();

    //     $transactionSetCount = 0;
    //     $claimSeq = 1;

    //     foreach ($claimsByPayer as $insuranceCode => $payerClaims) {

    //         $payer = $insuranceCompanies->get($insuranceCode);

    //         if (! $payer) {
    //             throw new \RuntimeException(
    //                 "Payer setup missing for {$insuranceCode}."
    //             );
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | New ST/SE per payer - same as old method
    //         |--------------------------------------------------------------------------
    //         */

    //         $transactionSetCount++;

    //         $stControlNumber = str_pad(
    //             (string) $transactionSetCount,
    //             4,
    //             '0',
    //             STR_PAD_LEFT
    //         );

    //         $tx = [];

    //         $tx[] = sprintf(
    //             'ST*837*%s*%s',
    //             $stControlNumber,
    //             $implementationVersion
    //         );

    //         $tx[] = sprintf(
    //             'BHT*0019*00*%s%s*%s*%s*CH',
    //             $controlNumber,
    //             $stControlNumber,
    //             $now->format('Ymd'),
    //             $now->format('Hi')
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 1000A Submitter - table driven
    //         |--------------------------------------------------------------------------
    //         */

    //         $tx[] = sprintf(
    //             'NM1*41*2*%s*****46*%s',
    //             $this->sanitizeX12($submitterName),
    //             $this->sanitizeX12($submitterId)
    //         );

    //         if ($submitterPhone !== '') {
    //             $tx[] = sprintf(
    //                 'PER*IC*%s*TE*%s',
    //                 $this->sanitizeX12($submitterContactName),
    //                 $submitterPhone
    //             );
    //         } else {
    //             $tx[] = sprintf(
    //                 'PER*IC*%s',
    //                 $this->sanitizeX12($submitterContactName)
    //             );
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 1000B Receiver - table driven
    //         |--------------------------------------------------------------------------
    //         */

    //         $tx[] = sprintf(
    //             'NM1*40*2*%s*****46*%s',
    //             $this->sanitizeX12($receiverName),
    //             $this->sanitizeX12($receiverTxCode)
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Billing Provider Loop - same segment structure as old method
    //         |--------------------------------------------------------------------------
    //         */

    //         $tx[] = 'HL*1*1*20*1';

    //         $tx[] = sprintf(
    //             'PRV*BI*PXC*%s',
    //             $companyTaxonomy
    //         );

    //         $tx[] = sprintf(
    //             'NM1*85*2*%s*****XX*%s',
    //             $this->sanitizeX12($companyName),
    //             $companyNpi
    //         );

    //         $tx[] = sprintf(
    //             'N3*%s',
    //             $this->sanitizeX12($companyAddr1)
    //         );

    //         $tx[] = sprintf(
    //             'N4*%s*%s*%s',
    //             $this->sanitizeX12($companyCity),
    //             $companyState,
    //             $companyZip
    //         );

    //         $tx[] = sprintf(
    //             'REF*EI*%s',
    //             $companyTxCode
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Billing Provider PER - now table driven
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($providerPhone !== '' && $providerFax !== '') {
    //             $tx[] = sprintf(
    //                 'PER*IC*%s*TE*%s*FX*%s',
    //                 $this->sanitizeX12($providerContactName),
    //                 $providerPhone,
    //                 $providerFax
    //             );
    //         } elseif ($providerPhone !== '') {
    //             $tx[] = sprintf(
    //                 'PER*IC*%s*TE*%s',
    //                 $this->sanitizeX12($providerContactName),
    //                 $providerPhone
    //             );
    //         } else {
    //             $tx[] = sprintf(
    //                 'PER*IC*%s',
    //                 $this->sanitizeX12($providerContactName)
    //             );
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Claims grouped same way as old method
    //         |--------------------------------------------------------------------------
    //         */

    //         $claimsByGroup = $payerClaims->groupBy('claim_group');

    //         foreach ($claimsByGroup as $claimGroup => $lines) {

    //             $firstLine = $lines->first();

    //             if (! $firstLine) {
    //                 continue;
    //             }

    //             $participantId = trim(
    //                 (string) $firstLine->ParticipantId
    //             );

    //             $member = $members->get($participantId);

    //             if (! $member) {
    //                 continue;
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Subscriber HL - intentionally preserved from old method
    //             |--------------------------------------------------------------------------
    //             */

    //             $tx[] = 'HL*1*1*22*1';

    //             /*
    //             |--------------------------------------------------------------------------
    //             | SBR - payer defaults from insurance_companies if available
    //             |--------------------------------------------------------------------------
    //             */

    //             $subscriberRelationshipCode =
    //                 $payer->subscriber_relationship_code ?? '18';

    //             $claimFilingIndicatorCode =
    //                 $payer->claim_filing_indicator_code ?? 'MC';

    //             $tx[] = sprintf(
    //                 'SBR*P*%s*******%s',
    //                 $subscriberRelationshipCode,
    //                 $claimFilingIndicatorCode
    //             );

    //             $lastName = $this->sanitizeX12(
    //                 $member->LastName ?? 'LAST'
    //             );

    //             $firstName = $this->sanitizeX12(
    //                 $member->FirstName ?? 'FIRST'
    //             );

    //             $dobRaw = $member->DateOfBirth ?? null;

    //             try {
    //                 $dob = $dobRaw
    //                     ? Carbon::parse($dobRaw)->format('Ymd')
    //                     : '19000101';
    //             } catch (\Throwable $e) {
    //                 $dob = '19000101';
    //             }

    //             $sex = strtoupper(
    //                 substr((string) ($member->Sex ?? 'U'), 0, 1)
    //             );

    //             /*
    //             |--------------------------------------------------------------------------
    //             | 2010BA Subscriber
    //             |--------------------------------------------------------------------------
    //             */

    //             $tx[] = sprintf(
    //                 'NM1*IL*1*%s*%s****MI*%s',
    //                 $lastName,
    //                 $firstName,
    //                 $this->sanitizeX12(
    //                     $firstLine->insurance_id ?? 'UNKNOWN'
    //                 )
    //             );

    //             $tx[] = 'N3*' . $this->sanitizeX12(
    //                 $member->Address1 ?? 'ADDRESS'
    //             );

    //             $tx[] = sprintf(
    //                 'N4*%s*%s*%s',
    //                 $this->sanitizeX12($member->City ?? 'CITY'),
    //                 $member->State ?? 'NJ',
    //                 preg_replace(
    //                     '/[^0-9]/',
    //                     '',
    //                     (string) ($member->ZipCode ?? '07000')
    //                 )
    //             );

    //             $tx[] = sprintf(
    //                 'DMG*D8*%s*%s',
    //                 $dob,
    //                 $sex
    //             );

    //             /*
    //             |--------------------------------------------------------------------------
    //             | 2010BB Payer - now from insurance_companies
    //             |--------------------------------------------------------------------------
    //             */

    //             $payerName = $this->sanitizeX12(
    //                 $payer->full_name
    //                     ?: ($payer->name ?? 'PAYER NAME')
    //             );

    //             $payerId = trim(
    //                 (string) ($payer->payer_id ?? '')
    //             );

    //             $payerIdQualifier =
    //                 $payer->payer_identifier_qualifier ?? 'PI';

    //             $tx[] = sprintf(
    //                 'NM1*PR*2*%s*****%s*%s',
    //                 $payerName,
    //                 $payerIdQualifier,
    //                 $payerId
    //             );

    //             if (! empty($payer->address1)) {
    //                 $tx[] = 'N3*' . $this->sanitizeX12(
    //                     $payer->address1
    //                 );
    //             }

    //             if (
    //                 ! empty($payer->city) ||
    //                 ! empty($payer->state) ||
    //                 ! empty($payer->zip)
    //             ) {
    //                 $tx[] = sprintf(
    //                     'N4*%s*%s*%s',
    //                     $this->sanitizeX12($payer->city ?? ''),
    //                     $payer->state ?? '',
    //                     preg_replace(
    //                         '/[^0-9]/',
    //                         '',
    //                         (string) ($payer->zip ?? '')
    //                     )
    //                 );
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | REF*G2 after payer - same as old method
    //             |--------------------------------------------------------------------------
    //             */

    //             $tx[] = sprintf(
    //                 'REF*G2*%s',
    //                 $companyTaxonomy
    //             );

    //             /*
    //             |--------------------------------------------------------------------------
    //             | 2300 Claim Level - same as old method
    //             |--------------------------------------------------------------------------
    //             */

    //             $totalCharge = $lines->sum('total_amount');

    //             $claimId = str_pad(
    //                 (string) $claimSeq,
    //                 7,
    //                 '0',
    //                 STR_PAD_LEFT
    //             );

    //             $claimSeq++;

    //             $amountStr = rtrim(
    //                 rtrim(
    //                     number_format(
    //                         (float) $totalCharge,
    //                         2,
    //                         '.',
    //                         ''
    //                     ),
    //                     '0'
    //                 ),
    //                 '.'
    //             );

    //             $placeOfServiceCode =
    //                 $payer->place_of_service_code ?? '99';

    //             $claimFrequencyCode =
    //                 $payer->claim_frequency_code ?? '1';

    //             $providerSignatureIndicator =
    //                 $payer->provider_signature_indicator ?? 'N';

    //             $assignmentParticipationCode =
    //                 $payer->assignment_participation_code ?? 'A';

    //             $benefitsAssignmentIndicator =
    //                 $payer->benefits_assignment_indicator ?? 'Y';

    //             $releaseInformationCode =
    //                 $payer->release_information_code ?? 'Y';

    //             $tx[] = sprintf(
    //                 'CLM*%s*%s***%s:B:%s*%s*%s*%s*%s',
    //                 $claimId,
    //                 $amountStr,
    //                 $placeOfServiceCode,
    //                 $claimFrequencyCode,
    //                 $providerSignatureIndicator,
    //                 $assignmentParticipationCode,
    //                 $benefitsAssignmentIndicator,
    //                 $releaseInformationCode
    //             );

    //             if (! empty($firstLine->prior_auth_no)) {
    //                 $tx[] = sprintf(
    //                     'REF*G1*%s',
    //                     $this->sanitizeX12($firstLine->prior_auth_no)
    //                 );
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Diagnosis HI - same as old method
    //             |--------------------------------------------------------------------------
    //             */

    //             $hiParts = ['HI'];

    //             if (! empty($firstLine->diag1)) {
    //                 $hiParts[] = 'ABK:' . $this->sanitizeX12(
    //                     $firstLine->diag1
    //                 );
    //             }

    //             if (! empty($firstLine->diag2)) {
    //                 $hiParts[] = 'ABF:' . $this->sanitizeX12(
    //                     $firstLine->diag2
    //                 );
    //             }

    //             if (count($hiParts) > 1) {
    //                 $tx[] = implode('*', $hiParts);
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | 2310B Rendering Provider - same as old method
    //             |--------------------------------------------------------------------------
    //             */

    //             $tx[] = sprintf(
    //                 'NM1*82*2*%s*****XX*%s',
    //                 $this->sanitizeX12($companyName),
    //                 $companyNpi
    //             );

    //             $tx[] = sprintf(
    //                 'PRV*PE*PXC*%s',
    //                 $companyTaxonomy
    //             );

    //             $tx[] = sprintf(
    //                 'REF*G2*%s',
    //                 $companyTaxonomy
    //             );

    //             /*
    //             |--------------------------------------------------------------------------
    //             | 2310C Service Facility - same as old method
    //             |--------------------------------------------------------------------------
    //             */

    //             $tx[] = sprintf(
    //                 'NM1*77*2*%s*****XX*%s',
    //                 $this->sanitizeX12($companyName),
    //                 $companyNpi
    //             );

    //             $tx[] = sprintf(
    //                 'N3*%s',
    //                 $this->sanitizeX12($companyAddr1)
    //             );

    //             $tx[] = sprintf(
    //                 'N4*%s*%s*%s',
    //                 $this->sanitizeX12($companyCity),
    //                 $companyState,
    //                 $companyZip
    //             );

    //             /*
    //             |--------------------------------------------------------------------------
    //             | 2400 Service Lines - same as old method
    //             |--------------------------------------------------------------------------
    //             */

    //             foreach ($lines as $line) {

    //                 $tx[] = 'LX*1';

    //                 $lineCharge = number_format(
    //                     (float) $line->total_amount,
    //                     2,
    //                     '.',
    //                     ''
    //                 );

    //                 $units = (int) ($line->units ?? 1);

    //                 $procCode = $this->sanitizeX12(
    //                     $line->proc_code ?? 'S5102'
    //                 );

    //                 $modifier = $this->sanitizeX12(
    //                     $line->m1 ?? 'U1'
    //                 );

    //                 $diagPointers = [];

    //                 if (! empty($firstLine->diag1)) {
    //                     $diagPointers[] = '1';
    //                 }

    //                 if (! empty($firstLine->diag2)) {
    //                     $diagPointers[] = '2';
    //                 }

    //                 if (empty($diagPointers)) {
    //                     $diagPointers[] = '1';
    //                 }

    //                 $diagPointerStr = implode(':', $diagPointers);

    //                 $svcDesc = 'DAY CARE SERVICE ADULT';

    //                 $tx[] = sprintf(
    //                     'SV1*HC:%s:%s::::%s*%s*UN*%d***%s',
    //                     $procCode,
    //                     $modifier,
    //                     $svcDesc,
    //                     $lineCharge,
    //                     $units,
    //                     $diagPointerStr
    //                 );

    //                 $lineFrom = Carbon::parse(
    //                     $line->service_from
    //                 )->format('Ymd');

    //                 $lineTo = $line->service_to
    //                     ? Carbon::parse($line->service_to)->format('Ymd')
    //                     : $lineFrom;

    //                 if ($lineFrom === $lineTo) {
    //                     $tx[] = sprintf(
    //                         'DTP*472*D8*%s',
    //                         $lineFrom
    //                     );
    //                 } else {
    //                     $tx[] = sprintf(
    //                         'DTP*472*RD8*%s-%s',
    //                         $lineFrom,
    //                         $lineTo
    //                     );
    //                 }

    //                 $tx[] = sprintf(
    //                     'REF*6R*%d',
    //                     (int) ($line->line_no ?? 1)
    //                 );
    //             }
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Close this transaction set
    //         |--------------------------------------------------------------------------
    //         */

    //         $segmentCount = count($tx) + 1;

    //         $tx[] = sprintf(
    //             'SE*%d*%s',
    //             $segmentCount,
    //             $stControlNumber
    //         );

    //         $segments = array_merge($segments, $tx);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Close functional group and interchange
    //     |--------------------------------------------------------------------------
    //     */

    //     $segments[] = sprintf(
    //         'GE*%d*%s',
    //         $transactionSetCount,
    //         $gsControlNumber
    //     );

    //     $segments[] = sprintf(
    //         'IEA*1*%s',
    //         $controlNumber
    //     );

    //     return implode("~\n", $segments) . "~\n";

    // } // End method

    protected function build837P_new($claims, ?string $type = null): string
    {
        if (! $claims instanceof \Illuminate\Support\Collection) {
            $claims = collect($claims);
        }

        if ($claims->isEmpty()) {
            throw new \RuntimeException(
                'No claims found for 837P generation.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | X12 Clearinghouse Settings
        |--------------------------------------------------------------------------
        */

        $clearinghouse = DB::table('x12_clearinghouse_settings')
            ->where('status', 'A')
            ->where('is_default', 1)
            ->first();

        if (! $clearinghouse) {
            $clearinghouse = DB::table('x12_clearinghouse_settings')
                ->where('status', 'A')
                ->orderBy('id')
                ->first();
        }

        if (! $clearinghouse) {
            throw new \RuntimeException(
                'No active clearinghouse setup found in X12 Settings.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | X12 Provider Settings
        |--------------------------------------------------------------------------
        */

        $provider = DB::table('x12_provider_settings')
            ->where('status', 'A')
            ->where('is_default', 1)
            ->first();

        if (! $provider) {
            $provider = DB::table('x12_provider_settings')
                ->where('status', 'A')
                ->orderBy('id')
                ->first();
        }

        if (! $provider) {
            throw new \RuntimeException(
                'No active provider setup found in X12 Settings.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Reserve X12 Control Numbers
        |--------------------------------------------------------------------------
        */

        $controlNumbers = $this->reserveX12ControlNumbers(
            (int) $clearinghouse->id
        );

        $controlNumber   = $controlNumbers['isa'];
        $gsControlNumber = $controlNumbers['gs'];


        /*
        |--------------------------------------------------------------------------
        | Member Details
        |--------------------------------------------------------------------------
        */

        $participantIds = $claims
            ->pluck('ParticipantId')
            ->map(fn($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();


        $members = DB::table('billing_members')
            ->whereIn('ParticipantId', $participantIds)
            ->get()
            ->keyBy(
                fn($member) =>
                trim((string) $member->ParticipantId)
            );


        /*
        |--------------------------------------------------------------------------
        | Recover Insurance Codes From Claims Table
        |--------------------------------------------------------------------------
        |
        | This is important because getReadyClaimsForSend() may attach display
        | names. For payer lookup, we want the original claims.insurance_name code.
        |
        */

        $claimIds = $claims
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();


        $claimInsuranceCodes = DB::table('claims')
            ->whereIn('id', $claimIds)
            ->pluck('insurance_name', 'id');


        $insuranceCodes = $claimInsuranceCodes
            ->values()
            ->map(fn($code) => trim((string) $code))
            ->filter()
            ->unique()
            ->values();


        $insuranceCompanies = DB::table('insurance_companies')
            ->whereIn('code', $insuranceCodes)
            ->where('active', 1)
            ->get()
            ->keyBy(
                fn($insurance) =>
                trim((string) $insurance->code)
            );


        foreach ($insuranceCodes as $insuranceCode) {

            $payer =
                $insuranceCompanies->get($insuranceCode);

            if (! $payer) {

                throw new \RuntimeException(
                    "Active insurance/payer setup missing for {$insuranceCode}."
                );
            }


            if (
                empty(trim(
                    (string) $payer->payer_id
                ))
            ) {

                throw new \RuntimeException(
                    "Electronic payer ID is missing for {$insuranceCode}."
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Provider Values From x12_provider_settings
        |--------------------------------------------------------------------------
        */

        $companyName = trim(
            (string) (
                $provider->provider_name
                ?? 'ABC ADULT DAY CARE'
            )
        );


        $companyAddr1 = trim(
            (string) (
                $provider->address1
                ?? 'ADDRESS LINE 1'
            )
        );


        $companyCity = trim(
            (string) (
                $provider->city
                ?? 'CITY'
            )
        );


        $companyState = strtoupper(
            trim(
                (string) (
                    $provider->state
                    ?? 'NJ'
                )
            )
        );


        $companyZip = preg_replace(
            '/[^0-9]/',
            '',
            (string) (
                $provider->zip_code
                ?? $provider->zip
                ?? '07000'
            )
        );


        $companyTxCode = preg_replace(
            '/[^0-9]/',
            '',
            (string) (
                $provider->tax_id
                ?? '123456789'
            )
        );


        $companyNpi = preg_replace(
            '/[^0-9]/',
            '',
            (string) (
                $provider->npi
                ?? '1000000000'
            )
        );


        $providerContactName = trim(
            (string) (
                $provider->contact_name
                ?? $provider->provider_name
                ?? $companyName
            )
        );


        $providerPhone = preg_replace(
            '/[^0-9]/',
            '',
            (string) (
                $provider->phone
                ?? ''
            )
        );


        $providerFax = preg_replace(
            '/[^0-9]/',
            '',
            (string) (
                $provider->fax
                ?? ''
            )
        );


        $companyTaxonomy = strtoupper(
            str_replace(
                [' ', '-'],
                '',
                (string) (
                    $provider->taxonomy_code
                    ?? '261QA0600X'
                )
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Clearinghouse / Envelope Values
        |--------------------------------------------------------------------------
        */

        $now = now();


        $senderId = trim(
            (string) (
                $clearinghouse->isa_sender_id
                ?? ''
            )
        );


        $receiverId = trim(
            (string) (
                $clearinghouse->isa_receiver_id
                ?? ''
            )
        );


        $senderCode = trim(
            (string) (
                $clearinghouse->gs_sender_code
                ?? $senderId
            )
        );


        $receiverCode = trim(
            (string) (
                $clearinghouse->gs_receiver_code
                ?? $receiverId
            )
        );


        $receiverTxCode = trim(
            (string) (
                $clearinghouse->receiver_transaction_id
                ?? $clearinghouse->receiver_id
                ?? '4300'
            )
        );


        $submitterName = trim(
            (string) (
                $clearinghouse->submitter_name
                ?? $companyName
            )
        );


        $submitterId = trim(
            (string) (
                $clearinghouse->submitter_id
                ?? $companyTxCode
            )
        );


        $submitterContactName = trim(
            (string) (
                $clearinghouse->submitter_contact_name
                ?? $submitterName
            )
        );


        $submitterPhone = preg_replace(
            '/[^0-9]/',
            '',
            (string) (
                $clearinghouse->submitter_phone
                ?? ''
            )
        );


        $receiverName = trim(
            (string) (
                $clearinghouse->receiver_name
                ?? 'CHANGE HEALTHCARE'
            )
        );


        /*
        |--------------------------------------------------------------------------
        | ISA Fixed Width Values
        |--------------------------------------------------------------------------
        */

        $isaAuthQualifier =
            $clearinghouse->isa_authorization_qualifier
            ?? '00';


        $isaAuthInfo = str_pad(
            substr(
                (string) (
                    $clearinghouse->isa_authorization_information
                    ?? ''
                ),
                0,
                10
            ),
            10,
            ' ',
            STR_PAD_RIGHT
        );


        $isaSecurityQualifier =
            $clearinghouse->isa_security_qualifier
            ?? '01';


        $isaSecurityInfo = str_pad(
            substr(
                (string) (
                    $clearinghouse->isa_security_information
                    ?? 'CYCTRANS00'
                ),
                0,
                10
            ),
            10,
            ' ',
            STR_PAD_RIGHT
        );


        $isaSenderQualifier =
            $clearinghouse->isa_sender_qualifier
            ?? 'ZZ';


        $isaReceiverQualifier =
            $clearinghouse->isa_receiver_qualifier
            ?? 'ZZ';


        $isaSenderId = str_pad(
            substr(
                $senderId,
                0,
                15
            ),
            15,
            ' ',
            STR_PAD_RIGHT
        );


        $isaReceiverId = str_pad(
            substr(
                $receiverId,
                0,
                15
            ),
            15,
            ' ',
            STR_PAD_RIGHT
        );


        $isaRepetitionSeparator =
            $clearinghouse->isa_repetition_separator
            ?? '^';


        $isaVersion =
            $clearinghouse->isa_version
            ?? '00501';


        $isaAckRequested =
            $clearinghouse->isa_ack_requested
            ?? '0';


        $isaUsageIndicator =
            $clearinghouse->isa_usage_indicator
            ?? 'T';


        $isaComponentSeparator =
            $clearinghouse->isa_component_separator
            ?? ':';


        $implementationVersion =
            $clearinghouse->implementation_version
            ?? '005010X222A1';


        /*
        |--------------------------------------------------------------------------
        | Start Segments
        |--------------------------------------------------------------------------
        */

        $segments = [];


        $segments[] = sprintf(
            'ISA*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s',
            $isaAuthQualifier,
            $isaAuthInfo,
            $isaSecurityQualifier,
            $isaSecurityInfo,
            $isaSenderQualifier,
            $isaSenderId,
            $isaReceiverQualifier,
            $isaReceiverId,
            $now->format('ymd'),
            $now->format('Hi'),
            $isaRepetitionSeparator,
            $isaVersion,
            $controlNumber,
            $isaAckRequested,
            $isaUsageIndicator,
            $isaComponentSeparator
        );


        $segments[] = sprintf(
            'GS*HC*%s*%s*%s*%s*%s*X*%s',
            $this->sanitizeX12($senderCode),
            $this->sanitizeX12($receiverCode),
            $now->format('Ymd'),
            $now->format('Hi'),
            $gsControlNumber,
            $implementationVersion
        );


        /*
        |--------------------------------------------------------------------------
        | Group Claims By Payer
        |--------------------------------------------------------------------------
        */

        $claimsByPayer = $claims
            ->groupBy(
                function ($claim) use ($claimInsuranceCodes) {

                    return trim(
                        (string) (
                            $claimInsuranceCodes->get(
                                $claim->id
                            )
                            ?? $claim->insurance_code
                            ?? $claim->insurance_name
                            ?? 'ZZZ_UNKNOWN'
                        )
                    );
                }
            )
            ->sortKeys();


        $transactionSetCount = 0;


        foreach (
            $claimsByPayer
            as $insuranceCode => $payerClaims
        ) {

            $payer =
                $insuranceCompanies->get(
                    $insuranceCode
                );


            if (! $payer) {

                throw new \RuntimeException(
                    "Payer setup missing for {$insuranceCode}."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | New ST / SE Per Payer
            |--------------------------------------------------------------------------
            */

            $transactionSetCount++;


            $stControlNumber = str_pad(
                (string) $transactionSetCount,
                4,
                '0',
                STR_PAD_LEFT
            );


            $tx = [];


            $tx[] = sprintf(
                'ST*837*%s*%s',
                $stControlNumber,
                $implementationVersion
            );


            $tx[] = sprintf(
                'BHT*0019*00*%s%s*%s*%s*CH',
                $controlNumber,
                $stControlNumber,
                $now->format('Ymd'),
                $now->format('Hi')
            );


            /*
            |--------------------------------------------------------------------------
            | 1000A Submitter
            |--------------------------------------------------------------------------
            */

            $tx[] = sprintf(
                'NM1*41*2*%s*****46*%s',
                $this->sanitizeX12(
                    $submitterName
                ),
                $this->sanitizeX12(
                    $submitterId
                )
            );


            if ($submitterPhone !== '') {

                $tx[] = sprintf(
                    'PER*IC*%s*TE*%s',
                    $this->sanitizeX12(
                        $submitterContactName
                    ),
                    $submitterPhone
                );
            } else {

                $tx[] = sprintf(
                    'PER*IC*%s',
                    $this->sanitizeX12(
                        $submitterContactName
                    )
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 1000B Receiver
            |--------------------------------------------------------------------------
            */

            $tx[] = sprintf(
                'NM1*40*2*%s*****46*%s',
                $this->sanitizeX12(
                    $receiverName
                ),
                $this->sanitizeX12(
                    $receiverTxCode
                )
            );


            /*
            |--------------------------------------------------------------------------
            | Billing Provider Loop
            |--------------------------------------------------------------------------
            */

            $tx[] = 'HL*1*1*20*1';


            $tx[] = sprintf(
                'PRV*BI*PXC*%s',
                $companyTaxonomy
            );


            $tx[] = sprintf(
                'NM1*85*2*%s*****XX*%s',
                $this->sanitizeX12(
                    $companyName
                ),
                $companyNpi
            );


            $tx[] = sprintf(
                'N3*%s',
                $this->sanitizeX12(
                    $companyAddr1
                )
            );


            $tx[] = sprintf(
                'N4*%s*%s*%s',
                $this->sanitizeX12(
                    $companyCity
                ),
                $companyState,
                $companyZip
            );


            $tx[] = sprintf(
                'REF*EI*%s',
                $companyTxCode
            );


            /*
            |--------------------------------------------------------------------------
            | Billing Provider PER
            |--------------------------------------------------------------------------
            */

            if (
                $providerPhone !== ''
                &&
                $providerFax !== ''
            ) {

                $tx[] = sprintf(
                    'PER*IC*%s*TE*%s*FX*%s',
                    $this->sanitizeX12(
                        $providerContactName
                    ),
                    $providerPhone,
                    $providerFax
                );
            } elseif ($providerPhone !== '') {

                $tx[] = sprintf(
                    'PER*IC*%s*TE*%s',
                    $this->sanitizeX12(
                        $providerContactName
                    ),
                    $providerPhone
                );
            } else {

                $tx[] = sprintf(
                    'PER*IC*%s',
                    $this->sanitizeX12(
                        $providerContactName
                    )
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Claims Grouping
            |--------------------------------------------------------------------------
            |
            | Normal claims continue using claim_group.
            |
            | Special claims may have claim_group = NULL.
            | In that case use claim_no so two different special claims are never
            | combined accidentally into one 837P claim.
            |
            */

            $claimsByGroup = $payerClaims->groupBy(
                function ($claim) {

                    if (
                        ! empty(trim(
                            (string) (
                                $claim->claim_group
                                ?? ''
                            )
                        ))
                    ) {

                        return trim(
                            (string) $claim->claim_group
                        );
                    }


                    return 'CLAIM-'
                        . (
                            $claim->claim_no
                            ?? $claim->id
                        );
                }
            );


            foreach (
                $claimsByGroup
                as $claimGroup => $lines
            ) {

                $firstLine =
                    $lines->first();


                if (! $firstLine) {
                    continue;
                }


                $participantId = trim(
                    (string) $firstLine->ParticipantId
                );


                $member =
                    $members->get(
                        $participantId
                    );


                if (! $member) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Subscriber HL
                |--------------------------------------------------------------------------
                */

                $tx[] =
                    'HL*1*1*22*1';


                /*
                |--------------------------------------------------------------------------
                | SBR
                |--------------------------------------------------------------------------
                */

                $subscriberRelationshipCode =
                    $payer->subscriber_relationship_code
                    ?? '18';


                $claimFilingIndicatorCode =
                    $payer->claim_filing_indicator_code
                    ?? 'MC';


                $tx[] = sprintf(
                    'SBR*P*%s*******%s',
                    $subscriberRelationshipCode,
                    $claimFilingIndicatorCode
                );


                /*
                |--------------------------------------------------------------------------
                | Determine Whether Special Demographic Override Is Active
                |--------------------------------------------------------------------------
                */

                $useDemographicOverride =
                    (int) (
                        $firstLine->is_special_claim
                        ?? 0
                    ) === 1
                    &&
                    (int) (
                        $firstLine->override_member_data
                        ?? 0
                    ) === 1;


                /*
                |--------------------------------------------------------------------------
                | Subscriber Name
                |--------------------------------------------------------------------------
                */

                if ($useDemographicOverride) {

                    $lastName =
                        $this->sanitizeX12(
                            trim(
                                (string) (
                                    $firstLine->override_last_name
                                    ?? $member->LastName
                                    ?? 'LAST'
                                )
                            )
                        );


                    $firstName =
                        $this->sanitizeX12(
                            trim(
                                (string) (
                                    $firstLine->override_first_name
                                    ?? $member->FirstName
                                    ?? 'FIRST'
                                )
                            )
                        );


                    $middleName =
                        $this->sanitizeX12(
                            trim(
                                (string) (
                                    $firstLine->override_middle_name
                                    ?? ''
                                )
                            )
                        );
                } else {

                    $lastName =
                        $this->sanitizeX12(
                            $member->LastName
                                ?? 'LAST'
                        );


                    $firstName =
                        $this->sanitizeX12(
                            $member->FirstName
                                ?? 'FIRST'
                        );


                    $middleName =
                        $this->sanitizeX12(
                            trim(
                                (string) (
                                    $member->MiddleName
                                    ?? ''
                                )
                            )
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | DOB
                |--------------------------------------------------------------------------
                */

                $dobRaw = $useDemographicOverride
                    ? (
                        $firstLine->override_dob
                        ?? $member->DateOfBirth
                        ?? null
                    )
                    : (
                        $member->DateOfBirth
                        ?? null
                    );


                try {

                    $dob = $dobRaw
                        ? Carbon::parse(
                            $dobRaw
                        )->format('Ymd')
                        : '19000101';
                } catch (\Throwable $e) {

                    $dob =
                        '19000101';
                }


                /*
                |--------------------------------------------------------------------------
                | Subscriber Address
                |--------------------------------------------------------------------------
                */

                if ($useDemographicOverride) {

                    $subscriberAddress1 = trim(
                        (string) (
                            $firstLine->override_address1
                            ?? $member->Address1
                            ?? 'ADDRESS'
                        )
                    );


                    $subscriberAddress2 = trim(
                        (string) (
                            $firstLine->override_address2
                            ?? ''
                        )
                    );


                    $subscriberCity = trim(
                        (string) (
                            $firstLine->override_city
                            ?? $member->City
                            ?? 'CITY'
                        )
                    );


                    $subscriberState = strtoupper(
                        trim(
                            (string) (
                                $firstLine->override_state
                                ?? $member->State
                                ?? 'NJ'
                            )
                        )
                    );


                    $subscriberZip = preg_replace(
                        '/[^0-9]/',
                        '',
                        (string) (
                            $firstLine->override_zip
                            ?? $member->ZipCode
                            ?? '07000'
                        )
                    );
                } else {

                    $subscriberAddress1 = trim(
                        (string) (
                            $member->Address1
                            ?? 'ADDRESS'
                        )
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Preserve Current Normal Claim Behavior
                    |--------------------------------------------------------------------------
                    |
                    | Existing 837P generation only used Address1.
                    |
                    */

                    $subscriberAddress2 = '';


                    $subscriberCity = trim(
                        (string) (
                            $member->City
                            ?? 'CITY'
                        )
                    );


                    $subscriberState = strtoupper(
                        trim(
                            (string) (
                                $member->State
                                ?? 'NJ'
                            )
                        )
                    );


                    $subscriberZip = preg_replace(
                        '/[^0-9]/',
                        '',
                        (string) (
                            $member->ZipCode
                            ?? '07000'
                        )
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Sex
                |--------------------------------------------------------------------------
                |
                | Sex is not currently overridden on Special Claim screen.
                |
                */

                $sex = strtoupper(
                    substr(
                        (string) (
                            $member->Sex
                            ?? 'U'
                        ),
                        0,
                        1
                    )
                );


                /*
                |--------------------------------------------------------------------------
                | 2010BA Subscriber - Insurance ID
                |--------------------------------------------------------------------------
                |
                | Insurance ID belongs to the claim.
                |
                | Normal claims:
                |     existing claims.insurance_id
                |
                | Special claims:
                |     manually editable claims.insurance_id
                |
                | billing_members is never changed.
                |
                */

                $insuranceId =
                    $this->sanitizeX12(
                        $firstLine->insurance_id
                            ?? 'UNKNOWN'
                    );


                if ($middleName !== '') {

                    $tx[] = sprintf(
                        'NM1*IL*1*%s*%s*%s***MI*%s',
                        $lastName,
                        $firstName,
                        $middleName,
                        $insuranceId
                    );
                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Preserve Existing Format When Middle Name Is Blank
                    |--------------------------------------------------------------------------
                    */

                    $tx[] = sprintf(
                        'NM1*IL*1*%s*%s****MI*%s',
                        $lastName,
                        $firstName,
                        $insuranceId
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Subscriber Address N3
                |--------------------------------------------------------------------------
                */

                if ($subscriberAddress2 !== '') {

                    $tx[] = sprintf(
                        'N3*%s*%s',
                        $this->sanitizeX12(
                            $subscriberAddress1
                        ),
                        $this->sanitizeX12(
                            $subscriberAddress2
                        )
                    );
                } else {

                    $tx[] =
                        'N3*'
                        . $this->sanitizeX12(
                            $subscriberAddress1
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | Subscriber City / State / ZIP
                |--------------------------------------------------------------------------
                */

                $tx[] = sprintf(
                    'N4*%s*%s*%s',
                    $this->sanitizeX12(
                        $subscriberCity
                    ),
                    $subscriberState,
                    $subscriberZip
                );


                /*
                |--------------------------------------------------------------------------
                | Subscriber DOB / Sex
                |--------------------------------------------------------------------------
                */

                $tx[] = sprintf(
                    'DMG*D8*%s*%s',
                    $dob,
                    $sex
                );


                /*
                |--------------------------------------------------------------------------
                | 2010BB Payer
                |--------------------------------------------------------------------------
                */

                $payerName =
                    $this->sanitizeX12(
                        $payer->full_name
                            ?: (
                                $payer->name
                                ?? 'PAYER NAME'
                            )
                    );


                $payerId = trim(
                    (string) (
                        $payer->payer_id
                        ?? ''
                    )
                );


                $payerIdQualifier =
                    $payer->payer_identifier_qualifier
                    ?? 'PI';


                $tx[] = sprintf(
                    'NM1*PR*2*%s*****%s*%s',
                    $payerName,
                    $payerIdQualifier,
                    $payerId
                );


                if (
                    ! empty($payer->address1)
                ) {

                    $tx[] =
                        'N3*'
                        . $this->sanitizeX12(
                            $payer->address1
                        );
                }


                if (
                    ! empty($payer->city)
                    ||
                    ! empty($payer->state)
                    ||
                    ! empty($payer->zip)
                ) {

                    $tx[] = sprintf(
                        'N4*%s*%s*%s',
                        $this->sanitizeX12(
                            $payer->city
                                ?? ''
                        ),
                        $payer->state
                            ?? '',
                        preg_replace(
                            '/[^0-9]/',
                            '',
                            (string) (
                                $payer->zip
                                ?? ''
                            )
                        )
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | REF*G2 After Payer
                |--------------------------------------------------------------------------
                */

                $tx[] = sprintf(
                    'REF*G2*%s',
                    $companyTaxonomy
                );


                /*
                |--------------------------------------------------------------------------
                | 2300 Claim Level
                |--------------------------------------------------------------------------
                */

                $totalCharge =
                    $lines->sum(
                        'total_amount'
                    );


                /*
                |--------------------------------------------------------------------------
                | CLM01 - Permanent Patient Account / Claim Number
                |--------------------------------------------------------------------------
                |
                | Never restart at 0000001 for each file.
                |
                | CLM01 must be the same permanent claim_no stored in claims.
                | This is also the number expected back from the payer/ERA.
                |--------------------------------------------------------------------------
                */

                // $claimId = trim(
                //     (string) (
                //         $firstLine->claim_no
                //         ?? ''
                //     )
                // );


                // if ($claimId === '') {

                //     throw new \RuntimeException(
                //         'Cannot generate 837P: claim_no is missing for claim ID '
                //             . (
                //                 $firstLine->id
                //                 ?? 'unknown'
                //             )
                //             . '.'
                //     );
                // }


                // $amountStr = rtrim(
                //     rtrim(
                //         number_format(
                //             (float) $totalCharge,
                //             2,
                //             '.',
                //             ''
                //         ),
                //         '0'
                //     ),
                //     '.'
                // );


                // $placeOfServiceCode =
                //     $payer->place_of_service_code
                //     ?? '99';


                // $claimFrequencyCode =
                //     $payer->claim_frequency_code
                //     ?? '1';


                // $providerSignatureIndicator =
                //     $payer->provider_signature_indicator
                //     ?? 'N';


                // $assignmentParticipationCode =
                //     $payer->assignment_participation_code
                //     ?? 'A';


                // $benefitsAssignmentIndicator =
                //     $payer->benefits_assignment_indicator
                //     ?? 'Y';


                // $releaseInformationCode =
                //     $payer->release_information_code
                //     ?? 'Y';


                // $tx[] = sprintf(
                //     'CLM*%s*%s***%s:B:%s*%s*%s*%s*%s',
                //     $claimId,
                //     $amountStr,
                //     $placeOfServiceCode,
                //     $claimFrequencyCode,
                //     $providerSignatureIndicator,
                //     $assignmentParticipationCode,
                //     $benefitsAssignmentIndicator,
                //     $releaseInformationCode
                // );

                /*
                |--------------------------------------------------------------------------
                | CLM01 - Claim Number Used In EDI
                |--------------------------------------------------------------------------
                |
                | Fresh claim:
                |     edi_claim_no is not supplied
                |     use claims.claim_no
                |
                | Corrected / Void:
                |     generateResubmission837P() supplies edi_claim_no
                |     which contains the ORIGINAL payer-facing claim number.
                |
                */

                $claimId = trim(
                    (string) (
                        $firstLine->edi_claim_no
                        ?? $firstLine->claim_no
                        ?? ''
                    )
                );


                if ($claimId === '') {

                    throw new \RuntimeException(
                        'Cannot generate 837P: claim number is missing for claim ID '
                            . (
                                $firstLine->id
                                ?? 'unknown'
                            )
                            . '.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Claim Amount
                |--------------------------------------------------------------------------
                */

                $amountStr = rtrim(
                    rtrim(
                        number_format(
                            (float) $totalCharge,
                            2,
                            '.',
                            ''
                        ),
                        '0'
                    ),
                    '.'
                );


                /*
                |--------------------------------------------------------------------------
                | Place Of Service
                |--------------------------------------------------------------------------
                */

                $placeOfServiceCode =
                    $payer->place_of_service_code
                    ?? '99';


                /*
                |--------------------------------------------------------------------------
                | Claim Frequency Code
                |--------------------------------------------------------------------------
                |
                | Fresh claim:
                |     normally payer setting = 1
                |     fallback = 1
                |
                | Corrected:
                |     generateResubmission837P() supplies 7
                |
                | Void:
                |     generateResubmission837P() supplies 8
                |
                */

                $claimFrequencyCode = trim(
                    (string) (
                        $firstLine->frequency_code
                        ?? $payer->claim_frequency_code
                        ?? '1'
                    )
                );


                /*
                |--------------------------------------------------------------------------
                | Validate Frequency Code
                |--------------------------------------------------------------------------
                */

                if (
                    ! in_array(
                        $claimFrequencyCode,
                        ['1', '7', '8'],
                        true
                    )
                ) {

                    throw new \RuntimeException(
                        'Invalid claim frequency code '
                            . $claimFrequencyCode
                            . ' for claim '
                            . $claimId
                            . '.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Other CLM Values
                |--------------------------------------------------------------------------
                */

                $providerSignatureIndicator =
                    $payer->provider_signature_indicator
                    ?? 'N';


                $assignmentParticipationCode =
                    $payer->assignment_participation_code
                    ?? 'A';


                $benefitsAssignmentIndicator =
                    $payer->benefits_assignment_indicator
                    ?? 'Y';


                $releaseInformationCode =
                    $payer->release_information_code
                    ?? 'Y';


                /*
                |--------------------------------------------------------------------------
                | CLM Segment
                |--------------------------------------------------------------------------
                */

                $tx[] = sprintf(
                    'CLM*%s*%s***%s:B:%s*%s*%s*%s*%s',
                    $this->sanitizeX12($claimId),
                    $amountStr,
                    $placeOfServiceCode,
                    $claimFrequencyCode,
                    $providerSignatureIndicator,
                    $assignmentParticipationCode,
                    $benefitsAssignmentIndicator,
                    $releaseInformationCode
                );


                /*
                |--------------------------------------------------------------------------
                | REF*F8 - Original Payer ICN
                |--------------------------------------------------------------------------
                |
                | Required only for:
                |
                |     Frequency 7 = Corrected / Replacement
                |     Frequency 8 = Void
                |
                | Fresh claim frequency 1 does NOT generate REF*F8.
                |
                */

                if (
                    in_array(
                        $claimFrequencyCode,
                        ['7', '8'],
                        true
                    )
                ) {

                    $payerIcn = trim(
                        (string) (
                            $firstLine->payer_icn
                            ?? $firstLine->payer_claim_number
                            ?? ''
                        )
                    );


                    if ($payerIcn === '') {

                        throw new \RuntimeException(
                            'Payer ICN is required for '
                                . (
                                    $claimFrequencyCode === '7'
                                    ? 'corrected'
                                    : 'void'
                                )
                                . ' claim '
                                . $claimId
                                . '.'
                        );
                    }


                    $tx[] = sprintf(
                        'REF*F8*%s',
                        $this->sanitizeX12(
                            $payerIcn
                        )
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Prior Authorization
                |--------------------------------------------------------------------------
                |
                | Normal claim:
                |     Use claims.prior_auth_no
                |
                | Special claim:
                |     Prefer override_prior_auth_no
                |
                */

                $priorAuthNo = '';


                if (
                    (int) (
                        $firstLine->is_special_claim
                        ?? 0
                    ) === 1
                    &&
                    ! empty(trim(
                        (string) (
                            $firstLine->override_prior_auth_no
                            ?? ''
                        )
                    ))
                ) {

                    $priorAuthNo = trim(
                        (string)
                        $firstLine->override_prior_auth_no
                    );
                } else {

                    $priorAuthNo = trim(
                        (string) (
                            $firstLine->prior_auth_no
                            ?? ''
                        )
                    );
                }


                if ($priorAuthNo !== '') {

                    $tx[] = sprintf(
                        'REF*G1*%s',
                        $this->sanitizeX12(
                            $priorAuthNo
                        )
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Diagnosis HI
                |--------------------------------------------------------------------------
                */

                $hiParts = ['HI'];


                if (
                    ! empty($firstLine->diag1)
                ) {

                    $hiParts[] =
                        'ABK:'
                        . $this->sanitizeX12(
                            $firstLine->diag1
                        );
                }


                if (
                    ! empty($firstLine->diag2)
                ) {

                    $hiParts[] =
                        'ABF:'
                        . $this->sanitizeX12(
                            $firstLine->diag2
                        );
                }


                if (
                    count($hiParts) > 1
                ) {

                    $tx[] =
                        implode(
                            '*',
                            $hiParts
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | 2310B Rendering Provider
                |--------------------------------------------------------------------------
                */

                $tx[] = sprintf(
                    'NM1*82*2*%s*****XX*%s',
                    $this->sanitizeX12(
                        $companyName
                    ),
                    $companyNpi
                );


                $tx[] = sprintf(
                    'PRV*PE*PXC*%s',
                    $companyTaxonomy
                );


                $tx[] = sprintf(
                    'REF*G2*%s',
                    $companyTaxonomy
                );


                /*
                |--------------------------------------------------------------------------
                | 2310C Service Facility
                |--------------------------------------------------------------------------
                */

                $tx[] = sprintf(
                    'NM1*77*2*%s*****XX*%s',
                    $this->sanitizeX12(
                        $companyName
                    ),
                    $companyNpi
                );


                $tx[] = sprintf(
                    'N3*%s',
                    $this->sanitizeX12(
                        $companyAddr1
                    )
                );


                $tx[] = sprintf(
                    'N4*%s*%s*%s',
                    $this->sanitizeX12(
                        $companyCity
                    ),
                    $companyState,
                    $companyZip
                );


                /*
                |--------------------------------------------------------------------------
                | 2400 Service Lines
                |--------------------------------------------------------------------------
                */

                foreach ($lines as $line) {

                    $tx[] = 'LX*1';


                    $lineCharge =
                        number_format(
                            (float) $line->total_amount,
                            2,
                            '.',
                            ''
                        );


                    $units =
                        (int) (
                            $line->units
                            ?? 1
                        );


                    $procCode =
                        $this->sanitizeX12(
                            $line->proc_code
                                ?? 'S5102'
                        );


                    $modifier =
                        $this->sanitizeX12(
                            $line->m1
                                ?? 'U1'
                        );


                    $diagPointers = [];


                    if (
                        ! empty($firstLine->diag1)
                    ) {

                        $diagPointers[] =
                            '1';
                    }


                    if (
                        ! empty($firstLine->diag2)
                    ) {

                        $diagPointers[] =
                            '2';
                    }


                    if (
                        empty($diagPointers)
                    ) {

                        $diagPointers[] =
                            '1';
                    }


                    $diagPointerStr =
                        implode(
                            ':',
                            $diagPointers
                        );


                    $svcDesc =
                        'DAY CARE SERVICE ADULT';


                    $tx[] = sprintf(
                        'SV1*HC:%s:%s::::%s*%s*UN*%d***%s',
                        $procCode,
                        $modifier,
                        $svcDesc,
                        $lineCharge,
                        $units,
                        $diagPointerStr
                    );


                    $lineFrom =
                        Carbon::parse(
                            $line->service_from
                        )->format('Ymd');


                    $lineTo =
                        $line->service_to
                        ? Carbon::parse(
                            $line->service_to
                        )->format('Ymd')
                        : $lineFrom;


                    if (
                        $lineFrom === $lineTo
                    ) {

                        $tx[] = sprintf(
                            'DTP*472*D8*%s',
                            $lineFrom
                        );
                    } else {

                        $tx[] = sprintf(
                            'DTP*472*RD8*%s-%s',
                            $lineFrom,
                            $lineTo
                        );
                    }


                    $tx[] = sprintf(
                        'REF*6R*%d',
                        (int) (
                            $line->line_no
                            ?? 1
                        )
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Close Transaction Set
            |--------------------------------------------------------------------------
            */

            $segmentCount =
                count($tx) + 1;


            $tx[] = sprintf(
                'SE*%d*%s',
                $segmentCount,
                $stControlNumber
            );


            $segments =
                array_merge(
                    $segments,
                    $tx
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Close Functional Group and Interchange
        |--------------------------------------------------------------------------
        */

        $segments[] = sprintf(
            'GE*%d*%s',
            $transactionSetCount,
            $gsControlNumber
        );


        $segments[] = sprintf(
            'IEA*1*%s',
            $controlNumber
        );


        return
            implode(
                "~\n",
                $segments
            )
            . "~\n";
    } // End Method

    /*
    |--------------------------------------------------------------------------
    | Reserve Permanent Claim Numbers
    |--------------------------------------------------------------------------
    |
    | All NEW claims, regardless of how they were created, receive their
    | permanent Patient Account / Claim Number only when they are submitted.
    |
    | Sources that converge here include:
    |   - Manual claim entry
    |   - Excel import
    |   - Attendance generation
    |   - Special / corrected claim creation
    |
    | Historical Medisoft imports that already contain a claim_no are preserved
    | and are not renumbered by the normal send workflow.
    |
    | The sequence row is locked inside the caller's DB transaction so two
    | batches generated at nearly the same time cannot receive the same numbers.
    |--------------------------------------------------------------------------
    */
    private function reserveClaimNumberRange(int $count): int
    {
        if ($count <= 0) {
            throw new \InvalidArgumentException(
                'Claim number reservation count must be greater than zero.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Lock Sequence Row
        |--------------------------------------------------------------------------
        */

        $sequence = DB::table('claim_number_sequences')
            ->where('id', 1)
            ->lockForUpdate()
            ->first();


        if (!$sequence) {

            throw new \RuntimeException(
                'Claim number sequence record was not found. '
                    . 'Create claim_number_sequences row ID 1 before generating claims.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Safety Check Against Existing Claims
        |--------------------------------------------------------------------------
        |
        | If an older/imported claim number is ever higher than the stored
        | sequence, automatically move forward rather than risk reusing a number.
        |--------------------------------------------------------------------------
        */

        $maxExistingClaimNo = DB::table('claims')
            ->whereNotNull('claim_no')
            ->whereRaw('CAST(claim_no AS UNSIGNED) < 900000')
            ->selectRaw(
                'MAX(CAST(claim_no AS UNSIGNED)) AS max_claim_no'
            )
            ->value('max_claim_no');


        $sequenceNext =
            max(
                1,
                (int) $sequence->next_claim_number
            );


        $existingNext =
            $maxExistingClaimNo !== null
            ? ((int) $maxExistingClaimNo + 1)
            : 1;


        $startNumber =
            max(
                $sequenceNext,
                $existingNext
            );


        $nextAvailableNumber =
            $startNumber + $count;


        /*
        |--------------------------------------------------------------------------
        | Advance Sequence
        |--------------------------------------------------------------------------
        */

        DB::table('claim_number_sequences')
            ->where('id', 1)
            ->update([

                'next_claim_number' =>
                $nextAvailableNumber,

                'updated_at' =>
                now(),

            ]);


        return $startNumber;
    } // End Method


    private function reserveX12ControlNumbers(int $clearinghouseId): array
    {
        return DB::transaction(
            function () use ($clearinghouseId) {

                $control = DB::table('x12_control_numbers')
                    ->where(
                        'clearinghouse_setting_id',
                        $clearinghouseId
                    )
                    ->lockForUpdate()
                    ->first();

                if (! $control) {
                    DB::table('x12_control_numbers')->insert([
                        'clearinghouse_setting_id' =>
                        $clearinghouseId,

                        'next_isa_number' => 2,
                        'next_gs_number' => 2,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return [
                        'isa' => '000000001',
                        'gs' => '1',
                    ];
                }

                $isaNumber = max(
                    1,
                    (int) $control->next_isa_number
                );

                $gsNumber = max(
                    1,
                    (int) $control->next_gs_number
                );

                DB::table('x12_control_numbers')
                    ->where('id', $control->id)
                    ->update([
                        'next_isa_number' =>
                        $isaNumber + 1,

                        'next_gs_number' =>
                        $gsNumber + 1,

                        'updated_at' => now(),
                    ]);

                return [
                    'isa' => str_pad(
                        (string) $isaNumber,
                        9,
                        '0',
                        STR_PAD_LEFT
                    ),

                    'gs' => (string) $gsNumber,
                ];
            }
        );
    } // End Method

    // public function sendGenerateWithPdfDb(Request $request)
    // {
    //     // 1) Get selected claims
    //     $claimIds = $request->input('selected_claims', []);

    //     if (empty($claimIds)) {
    //         return redirect()
    //             ->route('claims.send.preview')
    //             ->with([
    //                 'message' => 'No claims selected to send.',
    //                 'alert-type' => 'warning',
    //             ]);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Validate User Entered 837P File Name
    //     |--------------------------------------------------------------------------
    //     */
    //     $requestedFilename = trim((string) $request->input('edi_filename', ''));

    //     if ($requestedFilename === '') {
    //         return redirect()
    //             ->route('claims.send.preview')
    //             ->withInput()
    //             ->with([
    //                 'message' => 'Please enter a file name for the 837P batch.',
    //                 'alert-type' => 'warning',
    //             ]);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Remove .txt first if user entered it
    //     |--------------------------------------------------------------------------
    //     */
    //     if (str_ends_with(strtolower($requestedFilename), '.txt')) {
    //         $requestedFilename = substr($requestedFilename, 0, -4);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Replace Invalid Windows Filename Characters
    //     |--------------------------------------------------------------------------
    //     |
    //     | Invalid:
    //     | \ / : * ? " < > |
    //     |
    //     */
    //     $requestedFilename = preg_replace(
    //         '/[\\\\\/:*?"<>|]+/',
    //         '_',
    //         $requestedFilename
    //     );

    //     // Remove leading/trailing spaces and periods
    //     $requestedFilename = trim($requestedFilename, " .\t\n\r\0\x0B");

    //     // Prevent an empty filename after cleaning
    //     if ($requestedFilename === '') {
    //         return redirect()
    //             ->route('claims.send.preview')
    //             ->withInput()
    //             ->with([
    //                 'message' => 'The entered 837P file name is not valid.',
    //                 'alert-type' => 'warning',
    //             ]);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Final TXT / PDF File Names
    //     |--------------------------------------------------------------------------
    //     |
    //     | Example user enters:
    //     | Morning_08.31.26_to_09.04.26
    //     |
    //     | Saves:
    //     | Morning_08.31.26_to_09.04.26.txt
    //     | Morning_08.31.26_to_09.04.26.pdf
    //     |
    //     */
    //     $ediFilename = $requestedFilename . '.txt';
    //     $pdfFilename = $requestedFilename . '.pdf';

    //     $ediDir = 'claims';

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Prevent Accidental Overwrite
    //     |--------------------------------------------------------------------------
    //     */
    //     if (
    //         Storage::disk('adc_private')->exists($ediDir . '/' . $ediFilename) ||
    //         Storage::disk('adc_private')->exists($ediDir . '/' . $pdfFilename)
    //     ) {
    //         return redirect()
    //             ->route('claims.send.preview')
    //             ->withInput()
    //             ->with([
    //                 'message' => 'A batch with this file name already exists. Please enter a different file name.',
    //                 'alert-type' => 'warning',
    //             ]);
    //     }

    //     // 2) Reload selected claims
    //     $claims = DB::table('claims')
    //         ->whereIn('id', $claimIds)
    //         ->where('status', 'ready')
    //         ->where('precheck_result', 'valid')
    //         ->whereNull('claim_no')
    //         ->orderBy('service_from')
    //         ->get();

    //     if ($claims->isEmpty()) {
    //         return redirect()
    //             ->route('claims.send.preview')
    //             ->with([
    //                 'message' => 'No eligible claims found (they might have been sent already).',
    //                 'alert-type' => 'warning',
    //             ]);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         /*
    //         |--------------------------------------------------------------------------
    //         | 3) Reserve Permanent Claim Numbers
    //         |--------------------------------------------------------------------------
    //         |
    //         | This is the only number source for new claims.
    //         |
    //         | With next_claim_number = 17921:
    //         |
    //         |     first claim  -> 017921
    //         |     second claim -> 017922
    //         |     third claim  -> 017923
    //         |
    //         |--------------------------------------------------------------------------
    //         */

    //         $nextNumber =
    //             $this->reserveClaimNumberRange(
    //                 $claims->count()
    //             );


    //         $updatedClaims = [];

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 4) Assign Claim Numbers and Mark as Sent
    //         |--------------------------------------------------------------------------
    //         */
    //         foreach ($claims as $claim) {
    //             $claimNo = str_pad(
    //                 $nextNumber,
    //                 6,
    //                 '0',
    //                 STR_PAD_LEFT
    //             );

    //             $nextNumber++;

    //             $originalClaimNo =
    //                 $claim->original_claim_no ?? $claimNo;

    //             DB::table('claims')
    //                 ->where('id', $claim->id)
    //                 ->update([
    //                     'claim_no' => $claimNo,
    //                     'original_claim_no' => $originalClaimNo,
    //                     'status' => 'sent',
    //                     'updated_at' => now(),
    //                 ]);

    //             // Keep updated values in memory for EDI + PDF
    //             $claim->claim_no = $claimNo;
    //             $claim->original_claim_no = $originalClaimNo;

    //             $updatedClaims[] = $claim;
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 5) Generate 837P Using Updated Claims
    //         |--------------------------------------------------------------------------
    //         */
    //         $claimsForEdi = collect($updatedClaims);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Attach Member Names
    //         |--------------------------------------------------------------------------
    //         */
    //         $participantIds = $claimsForEdi
    //             ->pluck('ParticipantId')
    //             ->filter()
    //             ->map(fn($id) => trim($id))
    //             ->unique()
    //             ->values()
    //             ->all();

    //         $members = DB::table('billing_members')
    //             ->select(
    //                 'ParticipantId',
    //                 'LastName',
    //                 'FirstName'
    //             )
    //             ->whereIn(
    //                 DB::raw('TRIM(ParticipantId)'),
    //                 $participantIds
    //             )
    //             ->get();

    //         $membersById = $members->keyBy(
    //             fn($m) => trim($m->ParticipantId)
    //         );

    //         $claimsForEdi = $claimsForEdi->map(
    //             function ($claim) use ($membersById) {
    //                 $member =
    //                     $membersById[trim($claim->ParticipantId)] ?? null;

    //                 $claim->member_lastname =
    //                     $member->LastName ?? '';

    //                 $claim->member_firstname =
    //                     $member->FirstName ?? '';

    //                 return $claim;
    //             }
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Build 837P
    //         |--------------------------------------------------------------------------
    //         */
    //         $ediContent = $this->build837P_new(
    //             $claimsForEdi
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Batch Totals
    //         |--------------------------------------------------------------------------
    //         */
    //         $totalClaims = $claimsForEdi->count();
    //         $totalAmount = $claimsForEdi->sum('total_amount');

    //         $batchTimestampCarbon = now();
    //         $batchTimestamp =
    //             $batchTimestampCarbon->format('Ymd_His');

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 6) Save TXT + PDF
    //         |--------------------------------------------------------------------------
    //         |
    //         | For now this continues using PUBLIC storage.
    //         | Later we will change only this section to adc_private.
    //         |
    //         */
    //         Storage::disk('adc_private')->makeDirectory($ediDir);

    //         Storage::disk('adc_private')->put(
    //             $ediDir . '/' . $ediFilename,
    //             $ediContent
    //         );

    //         $pdf = Pdf::loadView(
    //             'claims.claims_send_batch_pdf',
    //             [
    //                 'claims' => $claimsForEdi,
    //                 'batchTimestamp' => $batchTimestamp,
    //                 'ediFilename' => $ediFilename,
    //             ]
    //         )->setPaper('letter', 'portrait');

    //         Storage::disk('adc_private')->put(
    //             $ediDir . '/' . $pdfFilename,
    //             $pdf->output()
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | User
    //         |--------------------------------------------------------------------------
    //         */
    //         $userCd = Auth::check()
    //             ? Auth::user()->user_cd
    //             : null;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 7) Save Batch Record
    //         |--------------------------------------------------------------------------
    //         */
    //         $batch = ClaimBatch::create([
    //             'batch_timestamp' => $batchTimestampCarbon,
    //             'edi_filename' => $ediFilename,
    //             'pdf_filename' => $pdfFilename,
    //             'total_claims' => $totalClaims,
    //             'total_amount' => $totalAmount,
    //             'created_by' => $userCd,
    //         ]);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 8) Create Biller Compensation Groups
    //         |--------------------------------------------------------------------------
    //         |
    //         | One billing group is created per ParticipantId within this submitted
    //         | 837P batch. If attendance gaps caused the ADC system to create two or
    //         | more separate claim numbers for the same member, all of those claims
    //         | receive the SAME billing_group_id.
    //         |
    //         | This keeps:
    //         |
    //         |     Actual submitted claims = separate claim_no values
    //         |     Biller compensation     = one group per member / submitted batch
    //         |
    //         | Example:
    //         |
    //         |     017550  09/14/2026 - 09/15/2026
    //         |     017551  09/17/2026 - 09/19/2026
    //         |
    //         | Both claims receive the same billing_group_id.
    //         |--------------------------------------------------------------------------
    //         */

    //         $claimsByParticipantForBilling =
    //             $claimsForEdi->groupBy(
    //                 function ($claim) {

    //                     return trim(
    //                         (string) (
    //                             $claim->ParticipantId
    //                             ?? ''
    //                         )
    //                     );
    //                 }
    //             );


    //         foreach (
    //             $claimsByParticipantForBilling
    //             as $billingParticipantId => $participantClaims
    //         ) {

    //             /*
    //             |--------------------------------------------------------------------------
    //             | Participant Safety Check
    //             |--------------------------------------------------------------------------
    //             */

    //             if ($billingParticipantId === '') {
    //                 continue;
    //             }


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Billing Period For This Member In This Batch
    //             |--------------------------------------------------------------------------
    //             |
    //             | Using MIN(service_from) and MAX(service_to) intentionally covers
    //             | attendance-gap split claims as one billing period.
    //             |--------------------------------------------------------------------------
    //             */

    //             $billingFrom =
    //                 $participantClaims
    //                 ->pluck('service_from')
    //                 ->filter()
    //                 ->min();


    //             $billingTo =
    //                 $participantClaims
    //                 ->pluck('service_to')
    //                 ->filter()
    //                 ->max();


    //             if (!$billingFrom || !$billingTo) {
    //                 continue;
    //             }


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Create Billing Group
    //             |--------------------------------------------------------------------------
    //             */

    //             $billingGroupId =
    //                 DB::table('claims_billing_groups')
    //                 ->insertGetId([

    //                     'ParticipantId' =>
    //                     $billingParticipantId,

    //                     'billing_from' =>
    //                     $billingFrom,

    //                     'billing_to' =>
    //                     $billingTo,

    //                     'submitted_by' =>
    //                     $userCd,

    //                     'submitted_at' =>
    //                     now(),

    //                     'batch_id' =>
    //                     $batch->id,

    //                     'created_at' =>
    //                     now(),

    //                     'updated_at' =>
    //                     now(),

    //                 ]);


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Assign Same Billing Group To All Claims For This Member
    //             |--------------------------------------------------------------------------
    //             */

    //             $participantClaimIds =
    //                 $participantClaims
    //                 ->pluck('id')
    //                 ->filter()
    //                 ->values()
    //                 ->all();


    //             if (!empty($participantClaimIds)) {

    //                 DB::table('claims')
    //                     ->whereIn(
    //                         'id',
    //                         $participantClaimIds
    //                     )
    //                     ->update([

    //                         'billing_group_id' =>
    //                         $billingGroupId,

    //                         'updated_at' =>
    //                         now(),

    //                     ]);


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Keep In-Memory Claims In Sync
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 foreach ($participantClaims as $participantClaim) {

    //                     $participantClaim->billing_group_id =
    //                         $billingGroupId;
    //                 }
    //             }
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | 9) Link Claims to Batch
    //         |--------------------------------------------------------------------------
    //         */
    //         foreach ($claimsForEdi as $claim) {
    //             ClaimBatchItem::create([
    //                 'claim_batch_id' => $batch->id,
    //                 'claim_id' => $claim->id,
    //             ]);
    //         }

    //         DB::commit();

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 10) Success
    //         |--------------------------------------------------------------------------
    //         */
    //         return redirect()
    //             ->route(
    //                 'claims.batches.show',
    //                 ['batchId' => $batch->id]
    //             )
    //             ->with([
    //                 'message' => '837P + PDF generated and claims marked as SENT.',
    //                 'alert-type' => 'success',
    //                 'edi_file' => $ediFilename,
    //                 'pdf_file' => $pdfFilename,
    //             ]);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();

    //         return redirect()
    //             ->route('claims.send.preview')
    //             ->withInput()
    //             ->with([
    //                 'message' => 'Error while generating 837P/PDF: ' . $e->getMessage(),
    //                 'alert-type' => 'error',
    //             ]);
    //     }
    // } // End Method


    public function sendGenerateWithPdfDb(Request $request)
    {
        // 1) Get selected claims
        $claimIds = $request->input('selected_claims', []);

        if (empty($claimIds)) {
            return redirect()
                ->route('claims.send.preview')
                ->with([
                    'message' => 'No claims selected to send.',
                    'alert-type' => 'warning',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate User Entered 837P File Name
        |--------------------------------------------------------------------------
        */
        $requestedFilename = trim((string) $request->input('edi_filename', ''));

        if ($requestedFilename === '') {
            return redirect()
                ->route('claims.send.preview')
                ->withInput()
                ->with([
                    'message' => 'Please enter a file name for the 837P batch.',
                    'alert-type' => 'warning',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Remove .txt first if user entered it
        |--------------------------------------------------------------------------
        */
        if (str_ends_with(strtolower($requestedFilename), '.txt')) {
            $requestedFilename = substr($requestedFilename, 0, -4);
        }

        /*
        |--------------------------------------------------------------------------
        | Replace Invalid Windows Filename Characters
        |--------------------------------------------------------------------------
        |
        | Invalid:
        | \ / : * ? " < > |
        |
        */
        $requestedFilename = preg_replace(
            '/[\\\\\/:*?"<>|]+/',
            '_',
            $requestedFilename
        );

        // Remove leading/trailing spaces and periods
        $requestedFilename = trim($requestedFilename, " .\t\n\r\0\x0B");

        // Prevent an empty filename after cleaning
        if ($requestedFilename === '') {
            return redirect()
                ->route('claims.send.preview')
                ->withInput()
                ->with([
                    'message' => 'The entered 837P file name is not valid.',
                    'alert-type' => 'warning',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Final TXT / PDF File Names
        |--------------------------------------------------------------------------
        |
        | Example user enters:
        | Morning_08.31.26_to_09.04.26
        |
        | Saves:
        | Morning_08.31.26_to_09.04.26.txt
        | Morning_08.31.26_to_09.04.26.pdf
        |
        */
        $ediFilename = $requestedFilename . '.txt';
        $pdfFilename = $requestedFilename . '.pdf';

        $ediDir = 'claims';

        /*
        |--------------------------------------------------------------------------
        | Prevent Accidental Overwrite
        |--------------------------------------------------------------------------
        */
        if (
            Storage::disk('adc_private')->exists($ediDir . '/' . $ediFilename) ||
            Storage::disk('adc_private')->exists($ediDir . '/' . $pdfFilename)
        ) {
            return redirect()
                ->route('claims.send.preview')
                ->withInput()
                ->with([
                    'message' => 'A batch with this file name already exists. Please enter a different file name.',
                    'alert-type' => 'warning',
                ]);
        }

        // 2) Reload selected claims
        $claims = DB::table('claims')
            ->whereIn('id', $claimIds)
            ->where('status', 'ready')
            ->where('precheck_result', 'valid')
            ->whereNull('claim_no')
            ->orderBy('service_from')
            ->get();

        if ($claims->isEmpty()) {
            return redirect()
                ->route('claims.send.preview')
                ->with([
                    'message' => 'No eligible claims found (they might have been sent already).',
                    'alert-type' => 'warning',
                ]);
        }

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | 3) Group Claims For Permanent Claim Number Assignment
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | build837P_new() generates ONE CLM segment for all rows sharing the
            | same claim_group. Therefore all rows in that claim_group must share
            | the SAME permanent claim_no.
            |
            | Claims without claim_group remain independent and receive their own
            | permanent claim number.
            |--------------------------------------------------------------------------
            */

            $claimsForNumbering =
                $claims->groupBy(
                    function ($claim) {

                        $claimGroup = trim(
                            (string) (
                                $claim->claim_group
                                ?? ''
                            )
                        );

                        if ($claimGroup !== '') {

                            return 'GROUP-' . $claimGroup;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | No Claim Group
                        |--------------------------------------------------------------------------
                        |
                        | Never combine unrelated rows merely because claim_group
                        | is blank.
                        |--------------------------------------------------------------------------
                        */

                        return 'CLAIM-' . $claim->id;
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | Reserve One Permanent Number Per Actual 837P Claim
            |--------------------------------------------------------------------------
            */

            $nextNumber =
                $this->reserveClaimNumberRange(
                    $claimsForNumbering->count()
                );


            $updatedClaims = [];


            /*
            |--------------------------------------------------------------------------
            | 4) Assign Same Claim Number To Every Line In The Group
            |--------------------------------------------------------------------------
            */

            foreach (
                $claimsForNumbering
                as $groupClaims
            ) {

                $claimNo = str_pad(
                    $nextNumber,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

                $nextNumber++;


                foreach ($groupClaims as $claim) {

                    $originalClaimNo =
                        $claim->original_claim_no
                        ?? $claimNo;


                    DB::table('claims')
                        ->where(
                            'id',
                            $claim->id
                        )
                        ->update([

                            'claim_no' =>
                            $claimNo,

                            'original_claim_no' =>
                            $originalClaimNo,

                            'status' =>
                            'sent',

                            'updated_at' =>
                            now(),

                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Keep Updated Values In Memory For EDI + PDF
                    |--------------------------------------------------------------------------
                    */

                    $claim->claim_no =
                        $claimNo;

                    $claim->original_claim_no =
                        $originalClaimNo;


                    $updatedClaims[] =
                        $claim;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | 5) Generate 837P Using Updated Claims
            |--------------------------------------------------------------------------
            */
            $claimsForEdi = collect($updatedClaims);

            /*
            |--------------------------------------------------------------------------
            | Attach Member Names
            |--------------------------------------------------------------------------
            */
            $participantIds = $claimsForEdi
                ->pluck('ParticipantId')
                ->filter()
                ->map(fn($id) => trim($id))
                ->unique()
                ->values()
                ->all();

            $members = DB::table('billing_members')
                ->select(
                    'ParticipantId',
                    'LastName',
                    'FirstName'
                )
                ->whereIn(
                    DB::raw('TRIM(ParticipantId)'),
                    $participantIds
                )
                ->get();

            $membersById = $members->keyBy(
                fn($m) => trim($m->ParticipantId)
            );

            $claimsForEdi = $claimsForEdi->map(
                function ($claim) use ($membersById) {
                    $member =
                        $membersById[trim($claim->ParticipantId)] ?? null;

                    $claim->member_lastname =
                        $member->LastName ?? '';

                    $claim->member_firstname =
                        $member->FirstName ?? '';

                    return $claim;
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Build 837P
            |--------------------------------------------------------------------------
            */
            $ediContent = $this->build837P_new(
                $claimsForEdi
            );

            /*
            |--------------------------------------------------------------------------
            | Batch Totals
            |--------------------------------------------------------------------------
            */
            $totalClaims = $claimsForEdi->count();
            $totalAmount = $claimsForEdi->sum('total_amount');

            $batchTimestampCarbon = now();
            $batchTimestamp =
                $batchTimestampCarbon->format('Ymd_His');

            /*
            |--------------------------------------------------------------------------
            | 6) Save TXT + PDF
            |--------------------------------------------------------------------------
            |
            | For now this continues using PUBLIC storage.
            | Later we will change only this section to adc_private.
            |
            */
            Storage::disk('adc_private')->makeDirectory($ediDir);

            Storage::disk('adc_private')->put(
                $ediDir . '/' . $ediFilename,
                $ediContent
            );

            $pdf = Pdf::loadView(
                'claims.claims_send_batch_pdf',
                [
                    'claims' => $claimsForEdi,
                    'batchTimestamp' => $batchTimestamp,
                    'ediFilename' => $ediFilename,
                ]
            )->setPaper('letter', 'portrait');

            Storage::disk('adc_private')->put(
                $ediDir . '/' . $pdfFilename,
                $pdf->output()
            );

            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */
            $userCd = Auth::check()
                ? Auth::user()->user_cd
                : null;

            /*
            |--------------------------------------------------------------------------
            | 7) Save Batch Record
            |--------------------------------------------------------------------------
            */
            $batch = ClaimBatch::create([
                'batch_timestamp' => $batchTimestampCarbon,
                'edi_filename' => $ediFilename,
                'pdf_filename' => $pdfFilename,
                'total_claims' => $totalClaims,
                'total_amount' => $totalAmount,
                'created_by' => $userCd,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 8) Create Biller Compensation Groups
            |--------------------------------------------------------------------------
            |
            | One billing group is created per ParticipantId within this submitted
            | 837P batch. If attendance gaps caused the ADC system to create two or
            | more separate claim numbers for the same member, all of those claims
            | receive the SAME billing_group_id.
            |
            | This keeps:
            |
            |     Actual submitted claims = separate claim_no values
            |     Biller compensation     = one group per member / submitted batch
            |
            | Example:
            |
            |     017550  09/14/2026 - 09/15/2026
            |     017551  09/17/2026 - 09/19/2026
            |
            | Both claims receive the same billing_group_id.
            |--------------------------------------------------------------------------
            */

            $claimsByParticipantForBilling =
                $claimsForEdi->groupBy(
                    function ($claim) {

                        return trim(
                            (string) (
                                $claim->ParticipantId
                                ?? ''
                            )
                        );
                    }
                );


            foreach (
                $claimsByParticipantForBilling
                as $billingParticipantId => $participantClaims
            ) {

                /*
                |--------------------------------------------------------------------------
                | Participant Safety Check
                |--------------------------------------------------------------------------
                */

                if ($billingParticipantId === '') {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Billing Period For This Member In This Batch
                |--------------------------------------------------------------------------
                |
                | Using MIN(service_from) and MAX(service_to) intentionally covers
                | attendance-gap split claims as one billing period.
                |--------------------------------------------------------------------------
                */

                $billingFrom =
                    $participantClaims
                    ->pluck('service_from')
                    ->filter()
                    ->min();


                $billingTo =
                    $participantClaims
                    ->pluck('service_to')
                    ->filter()
                    ->max();


                if (!$billingFrom || !$billingTo) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Create Billing Group
                |--------------------------------------------------------------------------
                */

                $billingGroupId =
                    DB::table('claims_billing_groups')
                    ->insertGetId([

                        'ParticipantId' =>
                        $billingParticipantId,

                        'billing_from' =>
                        $billingFrom,

                        'billing_to' =>
                        $billingTo,

                        'submitted_by' =>
                        $userCd,

                        'submitted_at' =>
                        now(),

                        'batch_id' =>
                        $batch->id,

                        'created_at' =>
                        now(),

                        'updated_at' =>
                        now(),

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Assign Same Billing Group To All Claims For This Member
                |--------------------------------------------------------------------------
                */

                $participantClaimIds =
                    $participantClaims
                    ->pluck('id')
                    ->filter()
                    ->values()
                    ->all();


                if (!empty($participantClaimIds)) {

                    DB::table('claims')
                        ->whereIn(
                            'id',
                            $participantClaimIds
                        )
                        ->update([

                            'billing_group_id' =>
                            $billingGroupId,

                            'updated_at' =>
                            now(),

                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Keep In-Memory Claims In Sync
                    |--------------------------------------------------------------------------
                    */

                    foreach ($participantClaims as $participantClaim) {

                        $participantClaim->billing_group_id =
                            $billingGroupId;
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | 9) Link Claims to Batch
            |--------------------------------------------------------------------------
            */
            foreach ($claimsForEdi as $claim) {
                ClaimBatchItem::create([
                    'claim_batch_id' => $batch->id,
                    'claim_id' => $claim->id,
                ]);
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | 10) Success
            |--------------------------------------------------------------------------
            */
            return redirect()
                ->route(
                    'claims.batches.show',
                    ['batchId' => $batch->id]
                )
                ->with([
                    'message' => '837P + PDF generated and claims marked as SENT.',
                    'alert-type' => 'success',
                    'edi_file' => $ediFilename,
                    'pdf_file' => $pdfFilename,
                ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('claims.send.preview')
                ->withInput()
                ->with([
                    'message' => 'Error while generating 837P/PDF: ' . $e->getMessage(),
                    'alert-type' => 'error',
                ]);
        }
    } // End Method


    public function medisoftImportForm()
    {
        return view('claims.claims_medisoft_import');
    } // End Method

    // public function medisoftImportPreview(Request $request)
    // {
    //     $request->validate([
    //         'csv_file' => [
    //             'required',
    //             'file',
    //             'mimes:csv,txt',
    //             'max:10240',
    //         ],
    //     ]);

    //     $file = $request->file('csv_file');

    //     if (!$file || !$file->isValid()) {
    //         return redirect()
    //             ->route('claims.medisoft.import.form')
    //             ->with('error', 'Invalid CSV file.');
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Open CSV
    //     |--------------------------------------------------------------------------
    //     */

    //     $handle = fopen(
    //         $file->getRealPath(),
    //         'r'
    //     );

    //     if ($handle === false) {
    //         return redirect()
    //             ->route('claims.medisoft.import.form')
    //             ->with('error', 'Unable to open CSV file.');
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Read Header
    //     |--------------------------------------------------------------------------
    //     */

    //     $header = fgetcsv($handle);

    //     if (!$header) {
    //         fclose($handle);

    //         return redirect()
    //             ->route('claims.medisoft.import.form')
    //             ->with('error', 'CSV file does not contain a header row.');
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Normalize Column Names
    //     |--------------------------------------------------------------------------
    //     |
    //     | Example:
    //     |
    //     | "ID"              -> id
    //     | "Claim Number"    -> claim_number
    //     | "insurance paid"  -> insurance_paid
    //     |
    //     */

    //     $header = array_map(function ($column) {

    //         $column = trim((string) $column);

    //         $column = strtolower($column);

    //         $column = str_replace(
    //             [' ', '-', '/', '\\'],
    //             '_',
    //             $column
    //         );

    //         $column = preg_replace(
    //             '/[^a-z0-9_]/',
    //             '',
    //             $column
    //         );

    //         $column = preg_replace(
    //             '/_+/',
    //             '_',
    //             $column
    //         );

    //         return trim(
    //             $column,
    //             '_'
    //         );

    //     }, $header);


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Required Columns
    //     |--------------------------------------------------------------------------
    //     */

    //     $requiredColumns = [
    //         'id',
    //         'start_date',
    //         'end_date',
    //         'units',
    //         'total',
    //         'claim_number',
    //     ];

    //     $missingColumns = [];

    //     foreach ($requiredColumns as $column) {

    //         if (!in_array($column, $header, true)) {
    //             $missingColumns[] = $column;
    //         }

    //     }

    //     if (!empty($missingColumns)) {

    //         fclose($handle);

    //         return redirect()
    //             ->route('claims.medisoft.import.form')
    //             ->with(
    //                 'error',
    //                 'Missing required CSV column(s): ' .
    //                 implode(', ', $missingColumns)
    //             );
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Read CSV Rows
    //     |--------------------------------------------------------------------------
    //     */

    //     $csvRows = [];

    //     $csvRowNumber = 1;

    //     while (
    //         ($data = fgetcsv($handle)) !== false
    //     ) {

    //         $csvRowNumber++;

    //         /*
    //         | Skip completely blank lines
    //         */

    //         $hasValue = false;

    //         foreach ($data as $value) {
    //             if (trim((string) $value) !== '') {
    //                 $hasValue = true;
    //                 break;
    //             }
    //         }

    //         if (!$hasValue) {
    //             continue;
    //         }


    //         /*
    //         | Make row length match header
    //         */

    //         if (count($data) < count($header)) {

    //             $data = array_pad(
    //                 $data,
    //                 count($header),
    //                 null
    //             );

    //         } elseif (
    //             count($data) > count($header)
    //         ) {

    //             $data = array_slice(
    //                 $data,
    //                 0,
    //                 count($header)
    //             );

    //         }


    //         $row = array_combine(
    //             $header,
    //             $data
    //         );

    //         if (!$row) {
    //             continue;
    //         }


    //         $row['_csv_row'] =
    //             $csvRowNumber;

    //         $csvRows[] =
    //             $row;
    //     }

    //     fclose($handle);


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Build Participant Keys
    //     |--------------------------------------------------------------------------
    //     */

    //     $participantKeys = collect($csvRows)
    //         ->map(function ($row) {

    //             $rawId = trim(
    //                 (string) (
    //                     $row['id'] ?? ''
    //                 )
    //             );

    //             if ($rawId === '') {
    //                 return null;
    //             }

    //             if (is_numeric($rawId)) {
    //                 return (string) (
    //                     (int) $rawId
    //                 );
    //             }

    //             return ltrim(
    //                 $rawId,
    //                 '0'
    //             );

    //         })
    //         ->filter()
    //         ->unique()
    //         ->values();


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Load Members
    //     |--------------------------------------------------------------------------
    //     |
    //     | We load all active/relevant members and key them using
    //     | a canonical participant ID:
    //     |
    //     | 005 -> 5
    //     | 012 -> 12
    //     |
    //     */

    //     $members = DB::table('members')
    //         ->get()
    //         ->keyBy(function ($member) {

    //             $id = trim(
    //                 (string) $member->ParticipantId
    //             );

    //             if (
    //                 is_numeric($id)
    //             ) {
    //                 return (string) (
    //                     (int) $id
    //                 );
    //             }

    //             $canonical = ltrim(
    //                 $id,
    //                 '0'
    //             );

    //             return $canonical === ''
    //                 ? '0'
    //                 : $canonical;

    //         });


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Build Preview Rows
    //     |--------------------------------------------------------------------------
    //     */

    //     $previewRows = [];

    //     foreach ($csvRows as $index => $row) {

    //         $errors = [];


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Participant
    //         |--------------------------------------------------------------------------
    //         */

    //         $rawParticipantId =
    //             trim(
    //                 (string) (
    //                     $row['id'] ?? ''
    //                 )
    //             );

    //         if ($rawParticipantId === '') {

    //             $canonicalParticipantId = '';

    //         } elseif (
    //             is_numeric($rawParticipantId)
    //         ) {

    //             $canonicalParticipantId =
    //                 (string) (
    //                     (int) $rawParticipantId
    //                 );

    //         } else {

    //             $canonicalParticipantId =
    //                 ltrim(
    //                     $rawParticipantId,
    //                     '0'
    //                 );

    //         }

    //         $member =
    //             $canonicalParticipantId !== ''
    //                 ? $members->get(
    //                     $canonicalParticipantId
    //                 )
    //                 : null;


    //         if (!$member) {

    //             $errors[] =
    //                 'Participant not found';

    //             $participantId =
    //                 $rawParticipantId;

    //         } else {

    //             /*
    //             | Always use real ADC ParticipantId
    //             | Example: Medisoft 5 -> ADC 005
    //             */

    //             $participantId =
    //                 trim(
    //                     (string)
    //                     $member->ParticipantId
    //                 );
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Dates
    //         |--------------------------------------------------------------------------
    //         */

    //         $serviceFrom = null;
    //         $serviceTo   = null;

    //         try {

    //             if (
    //                 !empty(
    //                     trim(
    //                         (string) (
    //                             $row['start_date'] ?? ''
    //                         )
    //                     )
    //                 )
    //             ) {

    //                 $serviceFrom =
    //                     \Carbon\Carbon::parse(
    //                         $row['start_date']
    //                     )->toDateString();

    //             } else {

    //                 $errors[] =
    //                     'Start date missing';
    //             }

    //         } catch (\Throwable $e) {

    //             $errors[] =
    //                 'Invalid start date';
    //         }


    //         try {

    //             if (
    //                 !empty(
    //                     trim(
    //                         (string) (
    //                             $row['end_date'] ?? ''
    //                         )
    //                     )
    //                 )
    //             ) {

    //                 $serviceTo =
    //                     \Carbon\Carbon::parse(
    //                         $row['end_date']
    //                     )->toDateString();

    //             } else {

    //                 $errors[] =
    //                     'End date missing';
    //             }

    //         } catch (\Throwable $e) {

    //             $errors[] =
    //                 'Invalid end date';
    //         }


    //         if (
    //             $serviceFrom &&
    //             $serviceTo &&
    //             $serviceTo < $serviceFrom
    //         ) {

    //             $errors[] =
    //                 'End date is before start date';
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Units
    //         |--------------------------------------------------------------------------
    //         */

    //         $units =
    //             (int) (
    //                 $row['units'] ?? 0
    //             );

    //         if ($units <= 0) {
    //             $errors[] =
    //                 'Units must be greater than 0';
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Total Amount
    //         |--------------------------------------------------------------------------
    //         */

    //         $totalAmount =
    //             $this->medisoftCsvNumber(
    //                 $row['total'] ?? 0
    //             );

    //         if ($totalAmount <= 0) {
    //             $errors[] =
    //                 'Total amount must be greater than 0';
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Allowed
    //         |--------------------------------------------------------------------------
    //         */

    //         $allowed =
    //             $this->medisoftCsvNumber(
    //                 $row['allowed']
    //                     ?? $totalAmount
    //             );


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Insurance Paid
    //         |--------------------------------------------------------------------------
    //         |
    //         | Medisoft accounting may export receipts as negative.
    //         | ADC stores amount received as positive.
    //         |--------------------------------------------------------------------------
    //         */

    //         $insurancePaidRaw =
    //             $row['insurace_paid']
    //             ?? $row['insurance_paid']
    //             ?? 0;

    //         $insurancePaid =
    //             abs(
    //                 $this->medisoftCsvNumber(
    //                     $insurancePaidRaw
    //                 )
    //             );


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Claim Number
    //         |--------------------------------------------------------------------------
    //         */

    //         $claimNoRaw =
    //             trim(
    //                 (string) (
    //                     $row['claim_number'] ?? ''
    //                 )
    //             );

    //         $claimNo = null;

    //         if ($claimNoRaw === '') {

    //             $errors[] =
    //                 'Claim number missing';

    //         } else {

    //             if (is_numeric($claimNoRaw)) {

    //                 $claimNo =
    //                     str_pad(
    //                         (string) (
    //                             (int) $claimNoRaw
    //                         ),
    //                         6,
    //                         '0',
    //                         STR_PAD_LEFT
    //                     );

    //             } else {

    //                 $claimNo =
    //                     str_pad(
    //                         $claimNoRaw,
    //                         6,
    //                         '0',
    //                         STR_PAD_LEFT
    //                     );
    //             }
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Duplicate Claim Check
    //         |--------------------------------------------------------------------------
    //         */

    //         if (
    //             $claimNo &&
    //             $member
    //         ) {

    //             $duplicateExists =
    //                 DB::table('claims')
    //                     ->where(
    //                         'claim_no',
    //                         $claimNo
    //                     )
    //                     ->where(
    //                         'ParticipantId',
    //                         $participantId
    //                     )
    //                     ->exists();

    //             if ($duplicateExists) {

    //                 $errors[] =
    //                     'Claim already exists';
    //             }
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Diagnosis
    //         |--------------------------------------------------------------------------
    //         */

    //         $diag1 =
    //             strtoupper(
    //                 trim(
    //                     (string) (
    //                         $row['diag1']
    //                         ?? ''
    //                     )
    //                 )
    //             );

    //         $diag2 =
    //             strtoupper(
    //                 trim(
    //                     (string) (
    //                         $row['diag2']
    //                         ?? ''
    //                     )
    //                 )
    //             );


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Procedure / Modifier
    //         |--------------------------------------------------------------------------
    //         |
    //         | For ADC:
    //         | Procedure = S5102
    //         | Modifier  = U1 unless CSV contains modifier_1
    //         |--------------------------------------------------------------------------
    //         */

    //         $procCode =
    //             'S5102';

    //         $modifier =
    //             strtoupper(
    //                 trim(
    //                     (string) (
    //                         $row['modifier_1']
    //                         ?? 'U1'
    //                     )
    //                 )
    //             );

    //         if ($modifier === '') {
    //             $modifier =
    //                 'U1';
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Amount Per Unit
    //         |--------------------------------------------------------------------------
    //         */

    //         $amountPerUnit =
    //             $units > 0
    //                 ? round(
    //                     $totalAmount / $units,
    //                     2
    //                 )
    //                 : 0;


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Member / Insurance Information
    //         |--------------------------------------------------------------------------
    //         */

    //         $insuranceName = null;
    //         $insuranceId   = null;

    //         $priorAuthNo     = null;
    //         $priorAuthExpiry = null;

    //         if ($member) {

    //             $insuranceName =
    //                 trim(
    //                     (string) (
    //                         $member->HMO_Enrolled
    //                         ?? ''
    //                     )
    //                 );

    //             $insuranceId =
    //                 trim(
    //                     (string) (
    //                         $member->medicaid_HMO_id
    //                         ?? ''
    //                     )
    //                 );


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Determine PA Based on Service Date
    //             |--------------------------------------------------------------------------
    //             */

    //             $warnings = [];

    //             if ($serviceFrom) {

    //                 $pa =
    //                     $this->getPriorAuthorizationForDate(
    //                         $member,
    //                         $serviceFrom
    //                     );

    //                 if ($pa) {

    //                     $priorAuthNo =
    //                         $pa['pa_no'];

    //                     $priorAuthExpiry =
    //                         $pa['pa_end'];

    //                 } else {

    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | Historical Medisoft Import
    //                     |--------------------------------------------------------------------------
    //                     |
    //                     | Do not reject the claim only because no currently valid PA
    //                     | can be found. Try to retain the available PA information
    //                     | from the member master and mark the row with a warning.
    //                     |
    //                     */

    //                     $priorAuthNo =
    //                         trim(
    //                             (string) (
    //                                 $member->renew1_PA_no
    //                                 ?? $member->medicaid_PA_no
    //                                 ?? ''
    //                             )
    //                         );

    //                     $priorAuthExpiry =
    //                         $member->renew1_PA_End
    //                         ?? $member->medicaid_PA_End
    //                         ?? null;

    //                     $warnings[] =
    //                         'Historical claim: no PA covering service date found; imported using available member PA information.';
    //                 }
    //             }

    //             if ($insuranceName === '') {

    //                 $errors[] =
    //                     'Insurance code missing in member master';
    //             }

    //             if ($insuranceId === '') {

    //                 $errors[] =
    //                     'Insurance ID missing in member master';
    //             }

    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Payer Information
    //         |--------------------------------------------------------------------------
    //         */

    //         $payerFullname = null;
    //         $payerId       = null;

    //         if (
    //             $insuranceName
    //         ) {

    //             $payer =
    //                 DB::table(
    //                     'insurance_companies'
    //                 )
    //                 ->where(
    //                     'code',
    //                     $insuranceName
    //                 )
    //                 ->where(
    //                     'active',
    //                     1
    //                 )
    //                 ->first();

    //             if ($payer) {

    //                 $payerFullname =
    //                     $payer->full_name
    //                     ?? $payer->name
    //                     ?? null;

    //                 $payerId =
    //                     $payer->payer_id
    //                     ?? null;

    //             } else {

    //                 $errors[] =
    //                     'Payer setup not found';
    //             }
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Status
    //         |--------------------------------------------------------------------------
    //         */

    //         $status =
    //             $insurancePaid > 0
    //                 ? 'paid'
    //                 : 'sent';


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Preview Record
    //         |--------------------------------------------------------------------------
    //         */

    //         $previewRows[] = [

    //             'key' =>
    //                 (string) $index,

    //             'csv_row' =>
    //                 $row['_csv_row'],

    //             'participant_raw' =>
    //                 $rawParticipantId,

    //             'ParticipantId' =>
    //                 $participantId,

    //             'member_found' =>
    //                 (bool) $member,

    //             'member_name' =>
    //                 $member
    //                     ? trim(
    //                         (
    //                             $member->LastName
    //                             ?? ''
    //                         ) .
    //                         ', ' .
    //                         (
    //                             $member->FirstName
    //                             ?? ''
    //                         ),
    //                         ', '
    //                     )
    //                     : '',

    //             'service_from' =>
    //                 $serviceFrom,

    //             'service_to' =>
    //                 $serviceTo,

    //             'proc_code' =>
    //                 $procCode,

    //             'm1' =>
    //                 $modifier,

    //             'units' =>
    //                 $units,

    //             'amount_per_unit' =>
    //                 $amountPerUnit,

    //             'total_amount' =>
    //                 $totalAmount,

    //             'allowed' =>
    //                 $allowed,

    //             'diag1' =>
    //                 $diag1,

    //             'diag2' =>
    //                 $diag2,

    //             'claim_no' =>
    //                 $claimNo,

    //             'ins1_paid' =>
    //                 $insurancePaid,

    //             'insurance_name' =>
    //                 $insuranceName,

    //             'insurance_id' =>
    //                 $insuranceId,

    //             'payer_fullname' =>
    //                 $payerFullname,

    //             'payer_id' =>
    //                 $payerId,

    //             'prior_auth_no' =>
    //                 $priorAuthNo,

    //             'prior_auth_expiry' =>
    //                 $priorAuthExpiry,

    //             'status' =>
    //                 $status,

    //             'valid' =>
    //                 empty($errors),

    //             'message' =>
    //                 !empty($errors)
    //                     ? implode(
    //                         '; ',
    //                         array_unique($errors)
    //                     )
    //                     : (
    //                         !empty($warnings)
    //                             ? implode(
    //                                 '; ',
    //                                 array_unique($warnings)
    //                             )
    //                             : 'OK'
    //                     ),

    //             'warning' =>
    //                 !empty($warnings),
    //         ];
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Store Preview in Session
    //     |--------------------------------------------------------------------------
    //     */

    //     $token =
    //         (string)
    //         \Illuminate\Support\Str::uuid();

    //     $imports =
    //         session(
    //             'medisoft_claim_imports',
    //             []
    //         );

    //     $imports[$token] = [
    //         'rows' =>
    //             $previewRows,

    //         'created_at' =>
    //             now()->toDateTimeString(),
    //     ];

    //     session([
    //         'medisoft_claim_imports' =>
    //             $imports
    //     ]);


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Counts
    //     |--------------------------------------------------------------------------
    //     */

    //     $readyCount =
    //         collect($previewRows)
    //             ->where(
    //                 'valid',
    //                 true
    //             )
    //             ->count();

    //     $invalidCount =
    //         count($previewRows)
    //         -
    //         $readyCount;


    //     return view(
    //         'claims.claims_medisoft_import_preview',
    //         compact(
    //             'token',
    //             'previewRows',
    //             'readyCount',
    //             'invalidCount'
    //         )
    //     );

    // } // End Method

    private function medisoftCsvNumber($value): float
    {
        if ($value === null) {
            return 0.00;
        }
        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return 0.00;
        }

        /*
         |--------------------------------------------------------------------------
         | Remove commas and dollar signs
         |--------------------------------------------------------------------------
         */

        $value = str_replace(
            [
                ',',
                '$',
                ' '
            ],
            '',
            $value
        );


        /*
         |--------------------------------------------------------------------------
         | Handle accounting format
         |
         | Example:
         | (473.30) -> -473.30
         |--------------------------------------------------------------------------
         */

        if (
            str_starts_with(
                $value,
                '('
            ) &&
            str_ends_with(
                $value,
                ')'
            )
        ) {

            $value =
                '-' .
                trim(
                    $value,
                    '()'
                );
        }


        return is_numeric($value)
            ? round(
                (float) $value,
                2
            )
            : 0.00;
    } // End Method

    // public function medisoftImportProcess(Request $request)
    // {
    //     $request->validate([
    //         'token' => [
    //             'required',
    //             'string',
    //         ],

    //         'selected_rows' => [
    //             'required',
    //             'array',
    //             'min:1',
    //         ],

    //         'selected_rows.*' => [
    //             'string',
    //         ],
    //     ]);


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Retrieve Preview Session
    //     |--------------------------------------------------------------------------
    //     */

    //     $token = $request->input('token');

    //     $imports = session(
    //         'medisoft_claim_imports',
    //         []
    //     );

    //     if (!isset($imports[$token])) {

    //         return redirect()
    //             ->route('claims.medisoft.import.form')
    //             ->with(
    //                 'error',
    //                 'The Medisoft import preview has expired. Please upload the CSV again.'
    //             );
    //     }

    //     $previewRows =
    //         $imports[$token]['rows']
    //         ?? [];


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Selected Preview Keys
    //     |--------------------------------------------------------------------------
    //     */

    //     $selectedKeys = collect(
    //         $request->input(
    //             'selected_rows',
    //             []
    //         )
    //     )
    //     ->map(
    //         fn ($value) =>
    //             (string) $value
    //     )
    //     ->unique()
    //     ->values();


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Only Selected + Valid Rows
    //     |--------------------------------------------------------------------------
    //     */

    //     $rowsToImport = collect(
    //         $previewRows
    //     )
    //     ->filter(
    //         function ($row) use ($selectedKeys) {

    //             return
    //                 !empty($row['valid'])
    //                 &&
    //                 $selectedKeys->contains(
    //                     (string) $row['key']
    //                 );
    //         }
    //     )
    //     ->values();


    //     if ($rowsToImport->isEmpty()) {

    //         return redirect()
    //             ->route('claims.medisoft.import.form')
    //             ->with(
    //                 'error',
    //                 'No valid Medisoft claim rows were selected.'
    //             );
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Result Tracking
    //     |--------------------------------------------------------------------------
    //     */

    //     $resultRows = [];

    //     $importedCount = 0;
    //     $skippedCount  = 0;
    //     $failedCount   = 0;


    //     DB::beginTransaction();

    //     try {

    //         foreach ($rowsToImport as $row) {

    //             try {

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Basic Values
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $participantId =
    //                     trim(
    //                         (string) (
    //                             $row['ParticipantId']
    //                             ?? ''
    //                         )
    //                     );

    //                 $claimNo =
    //                     trim(
    //                         (string) (
    //                             $row['claim_no']
    //                             ?? ''
    //                         )
    //                     );

    //                 $serviceFrom =
    //                     $row['service_from']
    //                     ?? null;

    //                 $serviceTo =
    //                     $row['service_to']
    //                     ?? null;


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Basic Safety Check
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 if (
    //                     $participantId === ''
    //                     ||
    //                     $claimNo === ''
    //                     ||
    //                     !$serviceFrom
    //                     ||
    //                     !$serviceTo
    //                 ) {

    //                     $failedCount++;

    //                     $resultRows[] = [

    //                         'csv_row' =>
    //                             $row['csv_row']
    //                             ?? '',

    //                         'participant_id' =>
    //                             $participantId,

    //                         'claim_no' =>
    //                             $claimNo,

    //                         'service_from' =>
    //                             $serviceFrom,

    //                         'service_to' =>
    //                             $serviceTo,

    //                         'total_amount' =>
    //                             $row['total_amount']
    //                             ?? 0,

    //                         'ins1_paid' =>
    //                             $row['ins1_paid']
    //                             ?? 0,

    //                         'result' =>
    //                             'Failed',

    //                         'message' =>
    //                             'Required claim information is missing.',
    //                     ];

    //                     continue;
    //                 }


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Duplicate Protection
    //                 |--------------------------------------------------------------------------
    //                 |
    //                 | IMPORTANT:
    //                 |
    //                 | Same claim number is allowed for multiple service lines.
    //                 |
    //                 | Duplicate only when:
    //                 |
    //                 | ParticipantId
    //                 | + claim_no
    //                 | + service_from
    //                 | + service_to
    //                 |
    //                 | all match.
    //                 |
    //                 */

    //                 $duplicate =
    //                     DB::table('claims')
    //                         ->where(
    //                             'claim_no',
    //                             $claimNo
    //                         )
    //                         ->where(
    //                             'ParticipantId',
    //                             $participantId
    //                         )
    //                         ->whereDate(
    //                             'service_from',
    //                             $serviceFrom
    //                         )
    //                         ->whereDate(
    //                             'service_to',
    //                             $serviceTo
    //                         )
    //                         ->exists();


    //                 if ($duplicate) {

    //                     $skippedCount++;

    //                     $resultRows[] = [

    //                         'csv_row' =>
    //                             $row['csv_row']
    //                             ?? '',

    //                         'participant_id' =>
    //                             $participantId,

    //                         'claim_no' =>
    //                             $claimNo,

    //                         'service_from' =>
    //                             $serviceFrom,

    //                         'service_to' =>
    //                             $serviceTo,

    //                         'total_amount' =>
    //                             $row['total_amount']
    //                             ?? 0,

    //                         'ins1_paid' =>
    //                             $row['ins1_paid']
    //                             ?? 0,

    //                         'result' =>
    //                             'Skipped',

    //                         'message' =>
    //                             'Same claim line already exists.',
    //                     ];

    //                     continue;
    //                 }


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Get Member
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $member =
    //                     DB::table('members')
    //                         ->whereRaw(
    //                             'TRIM(ParticipantId) = ?',
    //                             [
    //                                 $participantId
    //                             ]
    //                         )
    //                         ->first();


    //                 if (!$member) {

    //                     $failedCount++;

    //                     $resultRows[] = [

    //                         'csv_row' =>
    //                             $row['csv_row']
    //                             ?? '',

    //                         'participant_id' =>
    //                             $participantId,

    //                         'claim_no' =>
    //                             $claimNo,

    //                         'service_from' =>
    //                             $serviceFrom,

    //                         'service_to' =>
    //                             $serviceTo,

    //                         'total_amount' =>
    //                             $row['total_amount']
    //                             ?? 0,

    //                         'ins1_paid' =>
    //                             $row['ins1_paid']
    //                             ?? 0,

    //                         'result' =>
    //                             'Failed',

    //                         'message' =>
    //                             'Participant not found in ADC member master.',
    //                     ];

    //                     continue;
    //                 }


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Claim Group
    //                 |--------------------------------------------------------------------------
    //                 |
    //                 | All lines having the same Medisoft claim number
    //                 | for the same participant share the same claim_group.
    //                 |
    //                 */

    //                 $claimGroup =
    //                     'MEDISOFT-'
    //                     .
    //                     $participantId
    //                     .
    //                     '-'
    //                     .
    //                     $claimNo;


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Determine Next Line Number
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $maxLineNo =
    //                     DB::table('claims')
    //                         ->where(
    //                             'claim_group',
    //                             $claimGroup
    //                         )
    //                         ->max('line_no');

    //                 $nextLineNo =
    //                     ((int) ($maxLineNo ?? 0))
    //                     + 1;


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Insurance Code
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $insuranceName =
    //                     trim(
    //                         (string) (
    //                             $row['insurance_name']
    //                             ??
    //                             $member->HMO_Enrolled
    //                             ??
    //                             ''
    //                         )
    //                     );


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Insurance ID
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $insuranceId =
    //                     trim(
    //                         (string) (
    //                             $row['insurance_id']
    //                             ??
    //                             $member->medicaid_HMO_id
    //                             ??
    //                             ''
    //                         )
    //                     );


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Payer Information
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $payer = null;

    //                 if ($insuranceName !== '') {

    //                     $payer =
    //                         DB::table(
    //                             'insurance_companies'
    //                         )
    //                         ->where(
    //                             'code',
    //                             $insuranceName
    //                         )
    //                         ->first();
    //                 }


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Monetary Values
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $units =
    //                     (int) (
    //                         $row['units']
    //                         ?? 0
    //                     );


    //                 $totalAmount =
    //                     round(
    //                         (float) (
    //                             $row['total_amount']
    //                             ?? 0
    //                         ),
    //                         2
    //                     );


    //                 $allowed =
    //                     round(
    //                         (float) (
    //                             $row['allowed']
    //                             ?? $totalAmount
    //                         ),
    //                         2
    //                     );


    //                 $insurancePaid =
    //                     abs(
    //                         round(
    //                             (float) (
    //                                 $row['ins1_paid']
    //                                 ?? 0
    //                             ),
    //                             2
    //                         )
    //                     );


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Amount Per Unit
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $amountPerUnit =
    //                     $units > 0
    //                         ? round(
    //                             $totalAmount / $units,
    //                             2
    //                         )
    //                         : 0;


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Balance
    //                 |--------------------------------------------------------------------------
    //                 |
    //                 | Temporary historical balance until detailed
    //                 | Medisoft payments/adjustments are imported.
    //                 |
    //                 */

    //                 $balanceAmount =
    //                     round(
    //                         $totalAmount
    //                         -
    //                         $insurancePaid,
    //                         2
    //                     );

    //                 if ($balanceAmount < 0) {
    //                     $balanceAmount = 0;
    //                 }


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Claim Status
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $status =
    //                     $insurancePaid > 0
    //                         ? 'paid'
    //                         : 'sent';


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Historical PA
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $priorAuthNo =
    //                     !empty(
    //                         $row['prior_auth_no']
    //                     )
    //                         ? trim(
    //                             (string)
    //                             $row['prior_auth_no']
    //                         )
    //                         : null;


    //                 $priorAuthExpiry =
    //                     !empty(
    //                         $row['prior_auth_expiry']
    //                     )
    //                         ? $row['prior_auth_expiry']
    //                         : null;


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Import Message
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $importMessage =
    //                     $row['message']
    //                     ?? 'Imported from Medisoft CSV';


    //                 if ($importMessage === 'OK') {

    //                     $importMessage =
    //                         'Imported from Medisoft CSV';

    //                 } else {

    //                     $importMessage =
    //                         'Imported from Medisoft CSV. '
    //                         .
    //                         $importMessage;
    //                 }


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Insert Claim Line
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 DB::table('claims')
    //                     ->insert([

    //                         'claim_no' =>
    //                             $claimNo,

    //                         'claim_group' =>
    //                             $claimGroup,

    //                         'line_no' =>
    //                             $nextLineNo,

    //                         'ParticipantId' =>
    //                             $participantId,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Insurance / Payer
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'insurance_name' =>
    //                             $insuranceName
    //                                 ?: null,

    //                         'payer_fullname' =>
    //                             $payer->full_name
    //                             ??
    //                             $payer->name
    //                             ??
    //                             $row['payer_fullname']
    //                             ??
    //                             null,

    //                         'payer_id' =>
    //                             $payer->payer_id
    //                             ??
    //                             $row['payer_id']
    //                             ??
    //                             null,

    //                         'payer_address1' =>
    //                             $payer->address1
    //                             ?? null,

    //                         'payer_address2' =>
    //                             $payer->address2
    //                             ?? null,

    //                         'payer_city' =>
    //                             $payer->city
    //                             ?? null,

    //                         'payer_state' =>
    //                             $payer->state
    //                             ?? null,

    //                         'payer_zip' =>
    //                             $payer->zip
    //                             ?? null,

    //                         'insurance_id' =>
    //                             $insuranceId
    //                                 ?: null,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Prior Authorization
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'prior_auth_no' =>
    //                             $priorAuthNo,

    //                         'prior_auth_expiry' =>
    //                             $priorAuthExpiry,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Service
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'service_from' =>
    //                             $serviceFrom,

    //                         'service_to' =>
    //                             $serviceTo,

    //                         'proc_code' =>
    //                             'S5102',

    //                         'm1' =>
    //                             !empty(
    //                                 $row['m1']
    //                             )
    //                                 ? $row['m1']
    //                                 : 'U1',

    //                         'units' =>
    //                             $units,

    //                         'amount_per_unit' =>
    //                             $amountPerUnit,

    //                         'total_amount' =>
    //                             $totalAmount,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Diagnosis
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'diag1' =>
    //                             !empty(
    //                                 $row['diag1']
    //                             )
    //                                 ? $row['diag1']
    //                                 : null,

    //                         'diag2' =>
    //                             !empty(
    //                                 $row['diag2']
    //                             )
    //                                 ? $row['diag2']
    //                                 : null,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Provider / POS
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'provider' =>
    //                             'DM',

    //                         'pos' =>
    //                             '99',

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Financial
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'allowed' =>
    //                             $allowed,

    //                         'ins1_paid' =>
    //                             $insurancePaid,

    //                         'adjustment_amount' =>
    //                             0.00,

    //                         'patient_responsibility' =>
    //                             0.00,

    //                         'deduction_amount' =>
    //                             0.00,

    //                         'writeoff_amount' =>
    //                             0.00,

    //                         'balance_amount' =>
    //                             $balanceAmount,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Original Claim
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'original_claim_no' =>
    //                             $claimNo,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Status
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'status' =>
    //                             $status,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Historical Dates
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'submitted_date' =>
    //                             null,

    //                         'paid_date' =>
    //                             null,

    //                         'era_posted_at' =>
    //                             null,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Precheck
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'precheck_result' =>
    //                             'valid',

    //                         'precheck_message' =>
    //                             substr(
    //                                 $importMessage,
    //                                 0,
    //                                 255
    //                             ),

    //                         'created_at' =>
    //                             now(),

    //                         'updated_at' =>
    //                             now(),

    //                     ]);


    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Successful Result
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $importedCount++;

    //                 $resultRows[] = [

    //                     'csv_row' =>
    //                         $row['csv_row']
    //                         ?? '',

    //                     'participant_id' =>
    //                         $participantId,

    //                     'claim_no' =>
    //                         $claimNo,

    //                     'line_no' =>
    //                         $nextLineNo,

    //                     'service_from' =>
    //                         $serviceFrom,

    //                     'service_to' =>
    //                         $serviceTo,

    //                     'total_amount' =>
    //                         $totalAmount,

    //                     'ins1_paid' =>
    //                         $insurancePaid,

    //                     'result' =>
    //                         'Imported',

    //                     'message' =>
    //                         $importMessage,
    //                 ];


    //             } catch (\Throwable $rowException) {

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Individual Row Failure
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $failedCount++;

    //                 $resultRows[] = [

    //                     'csv_row' =>
    //                         $row['csv_row']
    //                         ?? '',

    //                     'participant_id' =>
    //                         $row['ParticipantId']
    //                         ?? '',

    //                     'claim_no' =>
    //                         $row['claim_no']
    //                         ?? '',

    //                     'line_no' =>
    //                         '',

    //                     'service_from' =>
    //                         $row['service_from']
    //                         ?? null,

    //                     'service_to' =>
    //                         $row['service_to']
    //                         ?? null,

    //                     'total_amount' =>
    //                         $row['total_amount']
    //                         ?? 0,

    //                     'ins1_paid' =>
    //                         $row['ins1_paid']
    //                         ?? 0,

    //                     'result' =>
    //                         'Failed',

    //                     'message' =>
    //                         $rowException
    //                             ->getMessage(),
    //                 ];
    //             }
    //         }


    //         /*
    //         |--------------------------------------------------------------------------
    //         | Commit
    //         |--------------------------------------------------------------------------
    //         */

    //         DB::commit();


    //     } catch (\Throwable $e) {

    //         DB::rollBack();

    //         return redirect()
    //             ->route(
    //                 'claims.medisoft.import.form'
    //             )
    //             ->with(
    //                 'error',
    //                 'Medisoft import failed: '
    //                 .
    //                 $e->getMessage()
    //             );
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Remove Used Preview Session
    //     |--------------------------------------------------------------------------
    //     */

    //     unset(
    //         $imports[$token]
    //     );

    //     session([
    //         'medisoft_claim_imports' =>
    //             $imports
    //     ]);


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Store Result
    //     |--------------------------------------------------------------------------
    //     */

    //     session()->flash(
    //         'medisoft_import_result',
    //         [

    //             'imported' =>
    //                 $importedCount,

    //             'skipped' =>
    //                 $skippedCount,

    //             'failed' =>
    //                 $failedCount,

    //             'total' =>
    //                 $rowsToImport
    //                     ->count(),

    //             'rows' =>
    //                 $resultRows,

    //         ]
    //     );


    //     return redirect()
    //         ->route(
    //             'claims.medisoft.import.result'
    //         );

    // } // End Method

    public function medisoftImportPreview(Request $request)
    {
        $request->validate([
            'csv_file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:20480',
            ],
        ]);

        $file = $request->file('csv_file');

        if (!$file || !$file->isValid()) {
            return redirect()
                ->route('claims.medisoft.import.form')
                ->with('error', 'Invalid CSV file.');
        }

        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return redirect()
                ->route('claims.medisoft.import.form')
                ->with('error', 'Unable to open CSV file.');
        }

        /*
        |--------------------------------------------------------------------------
        | Read / Normalize Header
        |--------------------------------------------------------------------------
        */

        $header = fgetcsv($handle);

        if (!$header) {
            fclose($handle);

            return redirect()
                ->route('claims.medisoft.import.form')
                ->with('error', 'CSV file does not contain a header row.');
        }

        $header = array_map(function ($column) {

            $column = strtolower(trim((string) $column));

            $column = str_replace(
                [' ', '-', '/', '\\'],
                '_',
                $column
            );

            $column = preg_replace(
                '/[^a-z0-9_]/',
                '',
                $column
            );

            $column = preg_replace(
                '/_+/',
                '_',
                $column
            );

            return trim($column, '_');
        }, $header);

        $requiredColumns = [
            'id',
            'start_date',
            'end_date',
            'units',
            'total',
            'claim_number',
        ];

        $missingColumns = [];

        foreach ($requiredColumns as $column) {
            if (!in_array($column, $header, true)) {
                $missingColumns[] = $column;
            }
        }

        if (!empty($missingColumns)) {

            fclose($handle);

            return redirect()
                ->route('claims.medisoft.import.form')
                ->with(
                    'error',
                    'Missing required CSV column(s): '
                        . implode(', ', $missingColumns)
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Preload Members
        |--------------------------------------------------------------------------
        |
        | Canonical key:
        | 005 -> 5
        | 051 -> 51
        |--------------------------------------------------------------------------
        */

        $members = DB::table('billing_members')
            ->where('active', 1)
            ->get()
            ->keyBy(function ($member) {

                $id = trim(
                    (string) $member->ParticipantId
                );

                if ($id === '') {
                    return '';
                }

                if (is_numeric($id)) {
                    return (string) ((int) $id);
                }

                $id = ltrim($id, '0');

                return $id === '' ? '0' : $id;
            });

        /*
        |--------------------------------------------------------------------------
        | Preload Insurance Companies
        |--------------------------------------------------------------------------
        */

        $payers = DB::table('insurance_companies')
            ->where('active', 1)
            ->get()
            ->keyBy(function ($payer) {

                return strtoupper(
                    trim(
                        (string) ($payer->code ?? '')
                    )
                );
            });

        /*
        |--------------------------------------------------------------------------
        | Preload Existing Claim Lines
        |--------------------------------------------------------------------------
        |
        | Duplicate key:
        |
        | ParticipantId + claim_no + service_from + service_to
        |--------------------------------------------------------------------------
        */

        $existingClaimKeys = [];

        DB::table('claims')
            ->select(
                'ParticipantId',
                'claim_no',
                'service_from',
                'service_to'
            )
            ->orderBy('id')
            ->chunk(2000, function ($claims) use (&$existingClaimKeys) {

                foreach ($claims as $claim) {

                    $participantId = trim(
                        (string) $claim->ParticipantId
                    );

                    $claimNo = trim(
                        (string) $claim->claim_no
                    );

                    $serviceFrom = $claim->service_from
                        ? \Carbon\Carbon::parse(
                            $claim->service_from
                        )->toDateString()
                        : '';

                    $serviceTo = $claim->service_to
                        ? \Carbon\Carbon::parse(
                            $claim->service_to
                        )->toDateString()
                        : '';

                    $key =
                        $participantId
                        . '|'
                        . $claimNo
                        . '|'
                        . $serviceFrom
                        . '|'
                        . $serviceTo;

                    $existingClaimKeys[$key] = true;
                }
            });

        /*
        |--------------------------------------------------------------------------
        | Read CSV and Build Preview
        |--------------------------------------------------------------------------
        */

        $previewRows = [];

        $csvRowNumber = 1;

        /*
        |--------------------------------------------------------------------------
        | Track rows inside same CSV
        |--------------------------------------------------------------------------
        |
        | Prevents same exact claim line appearing twice in the uploaded CSV.
        |--------------------------------------------------------------------------
        */

        $csvClaimKeys = [];

        while (($data = fgetcsv($handle)) !== false) {

            $csvRowNumber++;

            /*
            |--------------------------------------------------------------------------
            | Skip Blank Row
            |--------------------------------------------------------------------------
            */

            $hasValue = false;

            foreach ($data as $value) {

                if (trim((string) $value) !== '') {
                    $hasValue = true;
                    break;
                }
            }

            if (!$hasValue) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Match CSV Row Length
            |--------------------------------------------------------------------------
            */

            if (count($data) < count($header)) {

                $data = array_pad(
                    $data,
                    count($header),
                    null
                );
            } elseif (count($data) > count($header)) {

                $data = array_slice(
                    $data,
                    0,
                    count($header)
                );
            }

            $row = array_combine(
                $header,
                $data
            );

            if (!$row) {
                continue;
            }

            $errors   = [];
            $warnings = [];

            /*
            |--------------------------------------------------------------------------
            | Participant
            |--------------------------------------------------------------------------
            */

            $rawParticipantId =
                trim(
                    (string) ($row['id'] ?? '')
                );

            if ($rawParticipantId === '') {

                $canonicalParticipantId = '';
            } elseif (is_numeric($rawParticipantId)) {

                $canonicalParticipantId =
                    (string) ((int) $rawParticipantId);
            } else {

                $canonicalParticipantId =
                    ltrim(
                        $rawParticipantId,
                        '0'
                    );

                if ($canonicalParticipantId === '') {
                    $canonicalParticipantId = '0';
                }
            }

            $member =
                $canonicalParticipantId !== ''
                ? $members->get(
                    $canonicalParticipantId
                )
                : null;

            if (!$member) {

                $errors[] =
                    'Participant not found';

                $participantId =
                    $rawParticipantId;
            } else {

                $participantId =
                    trim(
                        (string) $member->ParticipantId
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Service Dates
            |--------------------------------------------------------------------------
            */

            $serviceFrom = null;
            $serviceTo   = null;

            try {

                $value =
                    trim(
                        (string) (
                            $row['start_date']
                            ?? ''
                        )
                    );

                if ($value !== '') {

                    $serviceFrom =
                        \Carbon\Carbon::parse(
                            $value
                        )->toDateString();
                } else {

                    $errors[] =
                        'Start date missing';
                }
            } catch (\Throwable $e) {

                $errors[] =
                    'Invalid start date';
            }

            try {

                $value =
                    trim(
                        (string) (
                            $row['end_date']
                            ?? ''
                        )
                    );

                if ($value !== '') {

                    $serviceTo =
                        \Carbon\Carbon::parse(
                            $value
                        )->toDateString();
                } else {

                    $errors[] =
                        'End date missing';
                }
            } catch (\Throwable $e) {

                $errors[] =
                    'Invalid end date';
            }

            if (
                $serviceFrom
                &&
                $serviceTo
                &&
                $serviceTo < $serviceFrom
            ) {

                $errors[] =
                    'End date is before start date';
            }

            /*
            |--------------------------------------------------------------------------
            | Units
            |--------------------------------------------------------------------------
            */

            $units =
                (int) ($row['units'] ?? 0);

            if ($units <= 0) {

                $errors[] =
                    'Units must be greater than 0';
            }

            /*
            |--------------------------------------------------------------------------
            | Total
            |--------------------------------------------------------------------------
            */

            $totalAmount =
                $this->medisoftCsvNumber(
                    $row['total'] ?? 0
                );

            if ($totalAmount <= 0) {

                $errors[] =
                    'Total amount must be greater than 0';
            }

            /*
            |--------------------------------------------------------------------------
            | Allowed
            |--------------------------------------------------------------------------
            */

            $allowed =
                $this->medisoftCsvNumber(
                    $row['allowed']
                        ?? $totalAmount
                );

            /*
            |--------------------------------------------------------------------------
            | Insurance Paid
            |--------------------------------------------------------------------------
            */

            $insurancePaidRaw =
                $row['insurace_paid']
                ?? $row['insurance_paid']
                ?? 0;

            $insurancePaid =
                abs(
                    $this->medisoftCsvNumber(
                        $insurancePaidRaw
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | Claim Number
            |--------------------------------------------------------------------------
            */

            $claimNoRaw =
                trim(
                    (string) (
                        $row['claim_number']
                        ?? ''
                    )
                );

            $claimNo = null;

            if ($claimNoRaw === '') {

                $errors[] =
                    'Claim number missing';
            } else {

                if (is_numeric($claimNoRaw)) {

                    $claimNo =
                        str_pad(
                            (string) ((int) $claimNoRaw),
                            6,
                            '0',
                            STR_PAD_LEFT
                        );
                } else {

                    $claimNo =
                        str_pad(
                            $claimNoRaw,
                            6,
                            '0',
                            STR_PAD_LEFT
                        );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Correct Duplicate Check
            |--------------------------------------------------------------------------
            |
            | Same claim number can have multiple service lines.
            |--------------------------------------------------------------------------
            */

            if (
                $claimNo
                &&
                $participantId !== ''
                &&
                $serviceFrom
                &&
                $serviceTo
            ) {

                $duplicateKey =
                    $participantId
                    . '|'
                    . $claimNo
                    . '|'
                    . $serviceFrom
                    . '|'
                    . $serviceTo;

                if (isset($existingClaimKeys[$duplicateKey])) {

                    $errors[] =
                        'Same claim line already exists';
                } elseif (isset($csvClaimKeys[$duplicateKey])) {

                    $errors[] =
                        'Duplicate claim line appears more than once in CSV';
                } else {

                    $csvClaimKeys[$duplicateKey] = true;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Diagnosis
            |--------------------------------------------------------------------------
            */

            $diag1 =
                strtoupper(
                    trim(
                        (string) (
                            $row['diag1']
                            ?? ''
                        )
                    )
                );

            $diag2 =
                strtoupper(
                    trim(
                        (string) (
                            $row['diag2']
                            ?? ''
                        )
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | Procedure / Modifier
            |--------------------------------------------------------------------------
            */

            $procCode = 'S5102';

            $modifier =
                strtoupper(
                    trim(
                        (string) (
                            $row['modifier_1']
                            ?? 'U1'
                        )
                    )
                );

            if ($modifier === '') {
                $modifier = 'U1';
            }

            /*
            |--------------------------------------------------------------------------
            | Amount Per Unit
            |--------------------------------------------------------------------------
            */

            $amountPerUnit =
                $units > 0
                ? round(
                    $totalAmount / $units,
                    2
                )
                : 0;

            /*
            |--------------------------------------------------------------------------
            | Member / Insurance
            |--------------------------------------------------------------------------
            */

            $insuranceName  = null;
            $insuranceId    = null;

            $priorAuthNo     = null;
            $priorAuthExpiry = null;

            if ($member) {

                $insuranceName =
                    trim(
                        (string) (
                            $member->HMO_Enrolled
                            ?? ''
                        )
                    );

                $insuranceId =
                    trim(
                        (string) (
                            $member->medicaid_HMO_id
                            ?? ''
                        )
                    );

                /*
                |--------------------------------------------------------------------------
                | Historical PA
                |--------------------------------------------------------------------------
                */

                if ($serviceFrom) {

                    $pa =
                        $this->getPriorAuthorizationForDate(
                            $member,
                            $serviceFrom
                        );

                    if ($pa) {

                        $priorAuthNo =
                            $pa['pa_no'];

                        $priorAuthExpiry =
                            $pa['pa_end'];
                    } else {

                        /*
                        | Historical record:
                        | PA mismatch is warning only.
                        */

                        $priorAuthNo =
                            trim(
                                (string) (
                                    $member->renew1_PA_no
                                    ?? $member->medicaid_PA_no
                                    ?? ''
                                )
                            );

                        $priorAuthExpiry =
                            $member->renew1_PA_End
                            ?? $member->medicaid_PA_End
                            ?? null;

                        $warnings[] =
                            'Historical claim: no PA covering service date found; imported using available member PA information.';
                    }
                }

                if ($insuranceName === '') {

                    $errors[] =
                        'Insurance code missing in member master';
                }

                if ($insuranceId === '') {

                    $errors[] =
                        'Insurance ID missing in member master';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Payer - In Memory Lookup
            |--------------------------------------------------------------------------
            */

            $payerFullname = null;
            $payerId       = null;

            $payerAddress1 = null;
            $payerAddress2 = null;
            $payerCity     = null;
            $payerState    = null;
            $payerZip      = null;

            if ($insuranceName) {

                $payer =
                    $payers->get(
                        strtoupper($insuranceName)
                    );

                if ($payer) {

                    $payerFullname =
                        $payer->full_name
                        ?? $payer->name
                        ?? null;

                    $payerId =
                        $payer->payer_id
                        ?? null;

                    $payerAddress1 =
                        $payer->address1
                        ?? null;

                    $payerAddress2 =
                        $payer->address2
                        ?? null;

                    $payerCity =
                        $payer->city
                        ?? null;

                    $payerState =
                        $payer->state
                        ?? null;

                    $payerZip =
                        $payer->zip
                        ?? null;
                } else {

                    $errors[] =
                        'Payer setup not found';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Historical Status
            |--------------------------------------------------------------------------
            */

            $status =
                $insurancePaid > 0
                ? 'paid'
                : 'sent';

            /*
            |--------------------------------------------------------------------------
            | Preview Row
            |--------------------------------------------------------------------------
            */

            $previewRows[] = [

                'key' =>
                (string) (count($previewRows)),

                'csv_row' =>
                $csvRowNumber,

                'participant_raw' =>
                $rawParticipantId,

                'ParticipantId' =>
                $participantId,

                'member_found' =>
                (bool) $member,

                'member_name' =>
                $member
                    ? trim(
                        ($member->LastName ?? '')
                            . ', '
                            . ($member->FirstName ?? ''),
                        ', '
                    )
                    : '',

                'service_from' =>
                $serviceFrom,

                'service_to' =>
                $serviceTo,

                'proc_code' =>
                $procCode,

                'm1' =>
                $modifier,

                'units' =>
                $units,

                'amount_per_unit' =>
                $amountPerUnit,

                'total_amount' =>
                $totalAmount,

                'allowed' =>
                $allowed,

                'diag1' =>
                $diag1,

                'diag2' =>
                $diag2,

                'claim_no' =>
                $claimNo,

                'ins1_paid' =>
                $insurancePaid,

                'insurance_name' =>
                $insuranceName,

                'insurance_id' =>
                $insuranceId,

                'payer_fullname' =>
                $payerFullname,

                'payer_id' =>
                $payerId,

                'payer_address1' =>
                $payerAddress1,

                'payer_address2' =>
                $payerAddress2,

                'payer_city' =>
                $payerCity,

                'payer_state' =>
                $payerState,

                'payer_zip' =>
                $payerZip,

                'prior_auth_no' =>
                $priorAuthNo,

                'prior_auth_expiry' =>
                $priorAuthExpiry,

                'status' =>
                $status,

                'valid' =>
                empty($errors),

                'warning' =>
                !empty($warnings),

                'message' =>
                !empty($errors)
                    ? implode(
                        '; ',
                        array_unique($errors)
                    )
                    : (
                        !empty($warnings)
                        ? implode(
                            '; ',
                            array_unique($warnings)
                        )
                        : 'OK'
                    ),
            ];
        }

        fclose($handle);

        /*
        |--------------------------------------------------------------------------
        | Store Preview
        |--------------------------------------------------------------------------
        */

        $token =
            (string)
            \Illuminate\Support\Str::uuid();

        $imports =
            session(
                'medisoft_claim_imports',
                []
            );

        $imports[$token] = [

            'rows' =>
            $previewRows,

            'created_at' =>
            now()->toDateTimeString(),
        ];

        session([
            'medisoft_claim_imports' =>
            $imports,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Counts
        |--------------------------------------------------------------------------
        */

        $readyCount =
            collect($previewRows)
            ->where(
                'valid',
                true
            )
            ->count();

        $invalidCount =
            count($previewRows)
            -
            $readyCount;

        return view(
            'claims.claims_medisoft_import_preview',
            compact(
                'token',
                'previewRows',
                'readyCount',
                'invalidCount'
            )
        );
    } // End Method

    public function medisoftImportProcess(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        |
        | Two modes:
        |
        | 1. import_all_valid = 1
        |    Import every valid preview row.
        |
        | 2. selected_rows[]
        |    Import manually selected rows.
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'token' => [
                'required',
                'string',
            ],

            'import_all_valid' => [
                'nullable',
                'boolean',
            ],

            'selected_rows' => [
                'nullable',
                'array',
            ],

            'selected_rows.*' => [
                'string',
            ],
        ]);

        $token =
            $request->input('token');

        $imports =
            session(
                'medisoft_claim_imports',
                []
            );

        if (!isset($imports[$token])) {

            return redirect()
                ->route('claims.medisoft.import.form')
                ->with(
                    'error',
                    'The Medisoft import preview has expired. Please upload the CSV again.'
                );
        }

        $previewRows =
            $imports[$token]['rows']
            ?? [];

        /*
        |--------------------------------------------------------------------------
        | Determine Import Mode
        |--------------------------------------------------------------------------
        */

        $importAllValid =
            $request->boolean(
                'import_all_valid'
            );

        if ($importAllValid) {

            $rowsToImport =
                collect($previewRows)
                ->filter(
                    fn($row) =>
                    !empty($row['valid'])
                )
                ->values();
        } else {

            $selectedKeys =
                collect(
                    $request->input(
                        'selected_rows',
                        []
                    )
                )
                ->map(
                    fn($value) =>
                    (string) $value
                )
                ->unique()
                ->values();

            if ($selectedKeys->isEmpty()) {

                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Please select at least one valid claim row.'
                    );
            }

            $selectedLookup =
                array_fill_keys(
                    $selectedKeys->all(),
                    true
                );

            $rowsToImport =
                collect($previewRows)
                ->filter(
                    function ($row) use ($selectedLookup) {

                        return
                            !empty($row['valid'])
                            &&
                            isset(
                                $selectedLookup[(string) $row['key']]
                            );
                    }
                )
                ->values();
        }

        if ($rowsToImport->isEmpty()) {

            return redirect()
                ->route('claims.medisoft.import.form')
                ->with(
                    'error',
                    'No valid Medisoft claim rows were selected.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Existing Claim Lines
        |--------------------------------------------------------------------------
        |
        | Load once.
        |--------------------------------------------------------------------------
        */

        $existingClaimKeys = [];

        /*
        |--------------------------------------------------------------------------
        | Existing Maximum Line Number Per Claim Group
        |--------------------------------------------------------------------------
        */

        $claimGroupLineNumbers = [];

        DB::table('claims')
            ->select(
                'ParticipantId',
                'claim_no',
                'service_from',
                'service_to',
                'claim_group',
                'line_no'
            )
            ->orderBy('id')
            ->chunk(
                2000,
                function ($claims) use (
                    &$existingClaimKeys,
                    &$claimGroupLineNumbers
                ) {

                    foreach ($claims as $claim) {

                        $participantId =
                            trim(
                                (string) $claim->ParticipantId
                            );

                        $claimNo =
                            trim(
                                (string) $claim->claim_no
                            );

                        $serviceFrom =
                            $claim->service_from
                            ? \Carbon\Carbon::parse(
                                $claim->service_from
                            )->toDateString()
                            : '';

                        $serviceTo =
                            $claim->service_to
                            ? \Carbon\Carbon::parse(
                                $claim->service_to
                            )->toDateString()
                            : '';

                        $duplicateKey =
                            $participantId
                            . '|'
                            . $claimNo
                            . '|'
                            . $serviceFrom
                            . '|'
                            . $serviceTo;

                        $existingClaimKeys[$duplicateKey] = true;

                        $claimGroup =
                            trim(
                                (string) (
                                    $claim->claim_group
                                    ?? ''
                                )
                            );

                        if ($claimGroup !== '') {

                            $currentLine =
                                (int) (
                                    $claim->line_no
                                    ?? 0
                                );

                            if (
                                !isset(
                                    $claimGroupLineNumbers[$claimGroup]
                                )
                                ||
                                $currentLine >
                                $claimGroupLineNumbers[$claimGroup]
                            ) {

                                $claimGroupLineNumbers[$claimGroup] = $currentLine;
                            }
                        }
                    }
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Result Tracking
        |--------------------------------------------------------------------------
        */

        $resultRows = [];

        $importedCount = 0;
        $skippedCount  = 0;
        $failedCount   = 0;

        /*
        |--------------------------------------------------------------------------
        | Build Insert Rows In Memory
        |--------------------------------------------------------------------------
        */

        $insertRows = [];

        $now = now();

        foreach ($rowsToImport as $row) {

            try {

                $participantId =
                    trim(
                        (string) (
                            $row['ParticipantId']
                            ?? ''
                        )
                    );

                $claimNo =
                    trim(
                        (string) (
                            $row['claim_no']
                            ?? ''
                        )
                    );

                $serviceFrom =
                    $row['service_from']
                    ?? null;

                $serviceTo =
                    $row['service_to']
                    ?? null;

                /*
                |--------------------------------------------------------------------------
                | Safety Check
                |--------------------------------------------------------------------------
                */

                if (
                    $participantId === ''
                    ||
                    $claimNo === ''
                    ||
                    !$serviceFrom
                    ||
                    !$serviceTo
                ) {

                    $failedCount++;

                    $resultRows[] = [

                        'csv_row' =>
                        $row['csv_row'] ?? '',

                        'participant_id' =>
                        $participantId,

                        'claim_no' =>
                        $claimNo,

                        'line_no' =>
                        '',

                        'service_from' =>
                        $serviceFrom,

                        'service_to' =>
                        $serviceTo,

                        'total_amount' =>
                        $row['total_amount']
                            ?? 0,

                        'ins1_paid' =>
                        $row['ins1_paid']
                            ?? 0,

                        'result' =>
                        'Failed',

                        'message' =>
                        'Required claim information is missing.',
                    ];

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Duplicate Check - Memory Only
                |--------------------------------------------------------------------------
                */

                $duplicateKey =
                    $participantId
                    . '|'
                    . $claimNo
                    . '|'
                    . $serviceFrom
                    . '|'
                    . $serviceTo;

                if (
                    isset(
                        $existingClaimKeys[$duplicateKey]
                    )
                ) {

                    $skippedCount++;

                    $resultRows[] = [

                        'csv_row' =>
                        $row['csv_row']
                            ?? '',

                        'participant_id' =>
                        $participantId,

                        'claim_no' =>
                        $claimNo,

                        'line_no' =>
                        '',

                        'service_from' =>
                        $serviceFrom,

                        'service_to' =>
                        $serviceTo,

                        'total_amount' =>
                        $row['total_amount']
                            ?? 0,

                        'ins1_paid' =>
                        $row['ins1_paid']
                            ?? 0,

                        'result' =>
                        'Skipped',

                        'message' =>
                        'Same claim line already exists.',
                    ];

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Immediately reserve duplicate key
                |--------------------------------------------------------------------------
                |
                | This prevents duplicate rows inside this same import batch.
                |--------------------------------------------------------------------------
                */

                $existingClaimKeys[$duplicateKey] = true;

                /*
                |--------------------------------------------------------------------------
                | Claim Group
                |--------------------------------------------------------------------------
                */

                $claimGroup =
                    'MEDISOFT-'
                    . $participantId
                    . '-'
                    . $claimNo;

                /*
                |--------------------------------------------------------------------------
                | Next Line Number - Memory Only
                |--------------------------------------------------------------------------
                */

                $currentLineNo =
                    $claimGroupLineNumbers[$claimGroup]
                    ?? 0;

                $nextLineNo =
                    $currentLineNo + 1;

                $claimGroupLineNumbers[$claimGroup] = $nextLineNo;

                /*
                |--------------------------------------------------------------------------
                | Monetary Values
                |--------------------------------------------------------------------------
                */

                $units =
                    (int) (
                        $row['units']
                        ?? 0
                    );

                $totalAmount =
                    round(
                        (float) (
                            $row['total_amount']
                            ?? 0
                        ),
                        2
                    );

                $allowed =
                    round(
                        (float) (
                            $row['allowed']
                            ?? $totalAmount
                        ),
                        2
                    );

                $insurancePaid =
                    abs(
                        round(
                            (float) (
                                $row['ins1_paid']
                                ?? 0
                            ),
                            2
                        )
                    );

                $amountPerUnit =
                    $units > 0
                    ? round(
                        $totalAmount / $units,
                        2
                    )
                    : 0;

                /*
                |--------------------------------------------------------------------------
                | Historical Balance
                |--------------------------------------------------------------------------
                */

                $balanceAmount =
                    round(
                        $totalAmount
                            -
                            $insurancePaid,
                        2
                    );

                if ($balanceAmount < 0) {
                    $balanceAmount = 0;
                }

                /*
                |--------------------------------------------------------------------------
                | Historical Status
                |--------------------------------------------------------------------------
                */

                $status =
                    $insurancePaid > 0
                    ? 'paid'
                    : 'sent';

                /*
                |--------------------------------------------------------------------------
                | Import Message
                |--------------------------------------------------------------------------
                */

                $importMessage =
                    $row['message']
                    ?? 'Imported from Medisoft CSV';

                if ($importMessage === 'OK') {

                    $importMessage =
                        'Imported from Medisoft CSV';
                } else {

                    $importMessage =
                        'Imported from Medisoft CSV. '
                        . $importMessage;
                }

                /*
                |--------------------------------------------------------------------------
                | Build Insert Record
                |--------------------------------------------------------------------------
                */

                $insertRows[] = [

                    'claim_no' =>
                    $claimNo,

                    'claim_group' =>
                    $claimGroup,

                    'line_no' =>
                    $nextLineNo,

                    'ParticipantId' =>
                    $participantId,

                    /*
                    |--------------------------------------------------------------------------
                    | Insurance / Payer
                    |--------------------------------------------------------------------------
                    */

                    'insurance_name' =>
                    !empty($row['insurance_name'])
                        ? $row['insurance_name']
                        : null,

                    'payer_fullname' =>
                    $row['payer_fullname']
                        ?? null,

                    'payer_id' =>
                    $row['payer_id']
                        ?? null,

                    'payer_address1' =>
                    $row['payer_address1']
                        ?? null,

                    'payer_address2' =>
                    $row['payer_address2']
                        ?? null,

                    'payer_city' =>
                    $row['payer_city']
                        ?? null,

                    'payer_state' =>
                    $row['payer_state']
                        ?? null,

                    'payer_zip' =>
                    $row['payer_zip']
                        ?? null,

                    'insurance_id' =>
                    !empty($row['insurance_id'])
                        ? $row['insurance_id']
                        : null,

                    /*
                    |--------------------------------------------------------------------------
                    | Prior Authorization
                    |--------------------------------------------------------------------------
                    */

                    'prior_auth_no' =>
                    !empty($row['prior_auth_no'])
                        ? $row['prior_auth_no']
                        : null,

                    'prior_auth_expiry' =>
                    !empty($row['prior_auth_expiry'])
                        ? $row['prior_auth_expiry']
                        : null,

                    /*
                    |--------------------------------------------------------------------------
                    | Service
                    |--------------------------------------------------------------------------
                    */

                    'service_from' =>
                    $serviceFrom,

                    'service_to' =>
                    $serviceTo,

                    'proc_code' =>
                    'S5102',

                    'm1' =>
                    !empty($row['m1'])
                        ? $row['m1']
                        : 'U1',

                    'units' =>
                    $units,

                    'amount_per_unit' =>
                    $amountPerUnit,

                    'total_amount' =>
                    $totalAmount,

                    /*
                    |--------------------------------------------------------------------------
                    | Diagnosis
                    |--------------------------------------------------------------------------
                    */

                    'diag1' =>
                    !empty($row['diag1'])
                        ? $row['diag1']
                        : null,

                    'diag2' =>
                    !empty($row['diag2'])
                        ? $row['diag2']
                        : null,

                    /*
                    |--------------------------------------------------------------------------
                    | Provider / POS
                    |--------------------------------------------------------------------------
                    */

                    'provider' =>
                    'DM',

                    'pos' =>
                    '99',

                    /*
                    |--------------------------------------------------------------------------
                    | Financial
                    |--------------------------------------------------------------------------
                    */

                    'allowed' =>
                    $allowed,

                    'ins1_paid' =>
                    $insurancePaid,

                    'adjustment_amount' =>
                    0.00,

                    'patient_responsibility' =>
                    0.00,

                    'deduction_amount' =>
                    0.00,

                    'writeoff_amount' =>
                    0.00,

                    'balance_amount' =>
                    $balanceAmount,

                    /*
                    |--------------------------------------------------------------------------
                    | Original Claim
                    |--------------------------------------------------------------------------
                    */

                    'original_claim_no' =>
                    $claimNo,

                    /*
                    |--------------------------------------------------------------------------
                    | Status
                    |--------------------------------------------------------------------------
                    */

                    'status' =>
                    $status,

                    'source_system'     => 'MEDISOFT',

                    /*
                    |--------------------------------------------------------------------------
                    | Historical Dates
                    |--------------------------------------------------------------------------
                    */

                    'submitted_date' =>
                    null,

                    'paid_date' =>
                    null,

                    'era_posted_at' =>
                    null,

                    /*
                    |--------------------------------------------------------------------------
                    | Precheck
                    |--------------------------------------------------------------------------
                    */

                    'precheck_result' =>
                    'valid',

                    'precheck_message' =>
                    substr(
                        $importMessage,
                        0,
                        255
                    ),

                    'created_at' =>
                    $now,

                    'updated_at' =>
                    $now,
                ];

                /*
                |--------------------------------------------------------------------------
                | Result Row
                |--------------------------------------------------------------------------
                */

                $resultRows[] = [

                    'csv_row' =>
                    $row['csv_row']
                        ?? '',

                    'participant_id' =>
                    $participantId,

                    'claim_no' =>
                    $claimNo,

                    'line_no' =>
                    $nextLineNo,

                    'service_from' =>
                    $serviceFrom,

                    'service_to' =>
                    $serviceTo,

                    'total_amount' =>
                    $totalAmount,

                    'ins1_paid' =>
                    $insurancePaid,

                    'result' =>
                    'Imported',

                    'message' =>
                    $importMessage,
                ];

                $importedCount++;
            } catch (\Throwable $rowException) {

                $failedCount++;

                $resultRows[] = [

                    'csv_row' =>
                    $row['csv_row']
                        ?? '',

                    'participant_id' =>
                    $row['ParticipantId']
                        ?? '',

                    'claim_no' =>
                    $row['claim_no']
                        ?? '',

                    'line_no' =>
                    '',

                    'service_from' =>
                    $row['service_from']
                        ?? null,

                    'service_to' =>
                    $row['service_to']
                        ?? null,

                    'total_amount' =>
                    $row['total_amount']
                        ?? 0,

                    'ins1_paid' =>
                    $row['ins1_paid']
                        ?? 0,

                    'result' =>
                    'Failed',

                    'message' =>
                    $rowException->getMessage(),
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Bulk Insert
        |--------------------------------------------------------------------------
        |
        | 500 claims per SQL INSERT.
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();

        try {

            foreach (
                array_chunk(
                    $insertRows,
                    500
                )
                as $chunk
            ) {

                DB::table('claims')
                    ->insert($chunk);
            }

            DB::commit();
        } catch (\Throwable $e) {

            DB::rollBack();

            return redirect()
                ->route(
                    'claims.medisoft.import.form'
                )
                ->with(
                    'error',
                    'Medisoft import failed: '
                        . $e->getMessage()
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Remove Used Preview
        |--------------------------------------------------------------------------
        */

        unset(
            $imports[$token]
        );

        session([
            'medisoft_claim_imports' =>
            $imports,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Store Result
        |--------------------------------------------------------------------------
        */

        session()->flash(
            'medisoft_import_result',
            [

                'imported' =>
                $importedCount,

                'skipped' =>
                $skippedCount,

                'failed' =>
                $failedCount,

                'total' =>
                $rowsToImport->count(),

                'rows' =>
                $resultRows,
            ]
        );

        return redirect()
            ->route(
                'claims.medisoft.import.result'
            );
    } // End Method


    public function medisoftImportResult()
    {
        $result =
            session(
                'medisoft_import_result'
            );

        if (!$result) {

            return redirect()
                ->route(
                    'claims.medisoft.import.form'
                );
        }

        return view(
            'claims.claims_medisoft_import_result',
            compact(
                'result'
            )
        );
    } // End Method


    public function medisoftDownloadInvalid($token)
    {
        /*
        |--------------------------------------------------------------------------
        | Retrieve Preview Session
        |--------------------------------------------------------------------------
        */

        $token = trim(
            (string) $token
        );


        $imports = session(
            'medisoft_claim_imports',
            []
        );


        /*
        |--------------------------------------------------------------------------
        | Preview Must Still Exist
        |--------------------------------------------------------------------------
        */

        if (
            $token === ''
            ||
            !isset($imports[$token])
        ) {

            return redirect()
                ->route(
                    'claims.medisoft.import.form'
                )
                ->with(
                    'error',
                    'The Medisoft import preview has expired. Please upload the CSV again.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Get Preview Rows
        |--------------------------------------------------------------------------
        */

        $previewRows =
            $imports[$token]['rows']
            ?? [];


        /*
        |--------------------------------------------------------------------------
        | Keep Invalid Rows Only
        |--------------------------------------------------------------------------
        */

        $invalidRows = collect(
            $previewRows
        )
            ->filter(
                function ($row) {

                    return empty($row['valid']);
                }
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Nothing Invalid
        |--------------------------------------------------------------------------
        */

        if ($invalidRows->isEmpty()) {

            return back()->with(
                'success',
                'There are no invalid Medisoft rows in this preview.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Download Filename
        |--------------------------------------------------------------------------
        */

        $fileName =
            'Medisoft_Invalid_Claims_'
            .
            now()->format(
                'Ymd_His'
            )
            .
            '.csv';


        /*
        |--------------------------------------------------------------------------
        | Stream CSV
        |--------------------------------------------------------------------------
        */

        return response()->streamDownload(

            function () use ($invalidRows) {

                $handle = fopen(
                    'php://output',
                    'w'
                );


                /*
                |--------------------------------------------------------------------------
                | UTF-8 BOM
                |--------------------------------------------------------------------------
                |
                | Helps Excel open the CSV correctly.
                |--------------------------------------------------------------------------
                */

                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );


                /*
                |--------------------------------------------------------------------------
                | Header Row
                |--------------------------------------------------------------------------
                */

                fputcsv(
                    $handle,
                    [
                        'CSV Row',
                        'Medisoft ID',
                        'Participant ID',
                        'Member Name',
                        'Service From',
                        'Service To',
                        'Procedure',
                        'Modifier',
                        'Units',
                        'Rate',
                        'Total',
                        'Allowed',
                        'Diagnosis 1',
                        'Diagnosis 2',
                        'Claim Number',
                        'Insurance Paid',
                        'Insurance',
                        'Insurance ID',
                        'Payer',
                        'Payer ID',
                        'PA Number',
                        'PA Expiry',
                        'Status',
                        'Error Message',
                    ]
                );


                /*
                |--------------------------------------------------------------------------
                | Invalid Rows
                |--------------------------------------------------------------------------
                */

                foreach (
                    $invalidRows
                    as $row
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Protect CSV From Formula Interpretation
                    |--------------------------------------------------------------------------
                    */

                    $safeText =
                        function ($value) {

                            $value =
                                (string) (
                                    $value
                                    ?? ''
                                );


                            if (
                                $value !== ''
                                &&
                                in_array(
                                    substr(
                                        $value,
                                        0,
                                        1
                                    ),
                                    [
                                        '=',
                                        '+',
                                        '-',
                                        '@',
                                    ],
                                    true
                                )
                            ) {

                                $value =
                                    "'"
                                    .
                                    $value;
                            }


                            return $value;
                        };


                    fputcsv(
                        $handle,
                        [
                            $safeText(
                                $row['csv_row']
                                    ?? ''
                            ),

                            $safeText(
                                $row['participant_raw']
                                    ?? ''
                            ),

                            $safeText(
                                $row['ParticipantId']
                                    ?? ''
                            ),

                            $safeText(
                                $row['member_name']
                                    ?? ''
                            ),

                            $safeText(
                                $row['service_from']
                                    ?? ''
                            ),

                            $safeText(
                                $row['service_to']
                                    ?? ''
                            ),

                            $safeText(
                                $row['proc_code']
                                    ?? ''
                            ),

                            $safeText(
                                $row['m1']
                                    ?? ''
                            ),

                            $row['units']
                                ?? 0,

                            number_format(
                                (float) (
                                    $row['amount_per_unit']
                                    ?? 0
                                ),
                                2,
                                '.',
                                ''
                            ),

                            number_format(
                                (float) (
                                    $row['total_amount']
                                    ?? 0
                                ),
                                2,
                                '.',
                                ''
                            ),

                            number_format(
                                (float) (
                                    $row['allowed']
                                    ?? 0
                                ),
                                2,
                                '.',
                                ''
                            ),

                            $safeText(
                                $row['diag1']
                                    ?? ''
                            ),

                            $safeText(
                                $row['diag2']
                                    ?? ''
                            ),

                            $safeText(
                                $row['claim_no']
                                    ?? ''
                            ),

                            number_format(
                                (float) (
                                    $row['ins1_paid']
                                    ?? 0
                                ),
                                2,
                                '.',
                                ''
                            ),

                            $safeText(
                                $row['insurance_name']
                                    ?? ''
                            ),

                            $safeText(
                                $row['insurance_id']
                                    ?? ''
                            ),

                            $safeText(
                                $row['payer_fullname']
                                    ?? ''
                            ),

                            $safeText(
                                $row['payer_id']
                                    ?? ''
                            ),

                            $safeText(
                                $row['prior_auth_no']
                                    ?? ''
                            ),

                            $safeText(
                                $row['prior_auth_expiry']
                                    ?? ''
                            ),

                            $safeText(
                                $row['status']
                                    ?? ''
                            ),

                            $safeText(
                                $row['message']
                                    ?? 'Invalid row'
                            ),
                        ]
                    );
                }


                fclose(
                    $handle
                );
            },

            $fileName,

            [
                'Content-Type' =>
                'text/csv; charset=UTF-8',

                'Cache-Control' =>
                'no-store, no-cache, must-revalidate',
            ]

        );
    } // End Method

    public function TestClaimMdEraList()
    {
        try {

            $response = Http::asForm()
                ->acceptJson()
                ->post(
                    rtrim(config('services.claimmd.url'), '/') . '/services/eralist/',
                    [
                        'AccountKey' => config('services.claimmd.account_key'),
                        'NewOnly'    => '1',
                    ]
                );

            if (!$response->successful()) {
                return response()->json([
                    'success'     => false,
                    'http_status' => $response->status(),
                    'response'    => $response->body(),
                ], 500);
            }

            $data = $response->json();

            if (isset($data['error'])) {
                return response()->json([
                    'success' => false,
                    'error'   => $data['error'],
                ]);
            }

            $eras = $data['eras']
                ?? $data['era']
                ?? $data['records']
                ?? $data['data']
                ?? [];

            $summary = collect($eras)->map(function ($era) {

                return [
                    'ERAID'          => $era['eraid'] ?? null,
                    'PayerID'        => $era['payerid'] ?? null,
                    'Payer'          => $era['payer_name'] ?? null,
                    'ReceivedDate'   => $era['received_time'] ?? null,
                    'PaymentDate'    => $era['paid_date'] ?? null,
                    'Amount'         => $era['paid_amount'] ?? null,
                    'PaymentMethod'  => $era['check_type'] ?? null,
                    'CheckEFT'       => $era['check_number'] ?? null,
                    'ProviderName'   => $era['prov_name'] ?? null,
                    'ClaimMdProvider' => $era['claimmd_prov_name'] ?? null,
                    'ProviderNPI'    => $era['prov_npi'] ?? null,
                    'DownloadTime'   => $era['download_time'] ?? null,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'count'   => $summary->count(),
                'eras'    => $summary,
            ]);

            return response()->json([
                'success' => true,
                'count'   => $summary->count(),
                'eras'    => $summary,
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    } // End Method

    public function uploadBatchToClaimMd($batchId)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Load Batch
            |--------------------------------------------------------------------------
            */

            $batch = ClaimBatch::find($batchId);

            if (!$batch) {
                return redirect()
                    ->route('claims.batches.index')
                    ->with([
                        'message' => 'Claim batch not found.',
                        'alert-type' => 'danger',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Make Sure 837 File Exists
            |--------------------------------------------------------------------------
            */

            if (!$batch->edi_filename) {
                return redirect()
                    ->route(
                        'claims.batches.show',
                        ['batchId' => $batch->id]
                    )
                    ->with([
                        'message' => '837P file is not available for this batch.',
                        'alert-type' => 'warning',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Private Claims Storage
            |--------------------------------------------------------------------------
            |
            | File is stored under:
            |
            | C:\ADCData\claims
            |
            */

            $relativePath =
                'claims/' .
                $batch->edi_filename;

            if (
                !Storage::disk('adc_private')
                    ->exists($relativePath)
            ) {
                return redirect()
                    ->route(
                        'claims.batches.show',
                        ['batchId' => $batch->id]
                    )
                    ->with([
                        'message' =>
                        '837P file was not found on the server.',
                        'alert-type' =>
                        'danger',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Read Existing 837 File
            |--------------------------------------------------------------------------
            */

            $ediContent =
                Storage::disk('adc_private')
                ->get($relativePath);

            if (trim($ediContent) === '') {
                return redirect()
                    ->route(
                        'claims.batches.show',
                        ['batchId' => $batch->id]
                    )
                    ->with([
                        'message' =>
                        '837P file is empty.',
                        'alert-type' =>
                        'danger',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Basic X12 Check
            |--------------------------------------------------------------------------
            */

            if (!str_contains($ediContent, 'ISA*')) {
                return redirect()
                    ->route(
                        'claims.batches.show',
                        ['batchId' => $batch->id]
                    )
                    ->with([
                        'message' =>
                        'The selected file does not appear to be a valid 837 X12 file.',
                        'alert-type' =>
                        'danger',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Upload To Claim.MD
            |--------------------------------------------------------------------------
            |
            | Claim.MD requires multipart/form-data:
            |
            | AccountKey
            | File
            |
            */

            $response = Http::acceptJson()
                ->timeout(120)
                ->attach(
                    'File',
                    $ediContent,
                    $batch->edi_filename
                )
                ->post(
                    rtrim(
                        config('services.claimmd.url'),
                        '/'
                    ) . '/services/upload/',
                    [
                        'AccountKey' =>
                        config(
                            'services.claimmd.account_key'
                        ),
                    ]
                );

            /*
            |--------------------------------------------------------------------------
            | HTTP Failure
            |--------------------------------------------------------------------------
            */

            if (!$response->successful()) {

                Log::error(
                    'Claim.MD 837 Upload HTTP Error',
                    [
                        'batch_id' =>
                        $batch->id,

                        'filename' =>
                        $batch->edi_filename,

                        'http_status' =>
                        $response->status(),

                        'response' =>
                        substr(
                            $response->body(),
                            0,
                            2000
                        ),
                    ]
                );

                return redirect()
                    ->route(
                        'claims.batches.show',
                        ['batchId' => $batch->id]
                    )
                    ->with([
                        'message' =>
                        'Claim.MD upload failed. HTTP status: ' .
                            $response->status(),

                        'alert-type' =>
                        'danger',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Claim.MD Response
            |--------------------------------------------------------------------------
            */

            $data = $response->json();

            /*
            |--------------------------------------------------------------------------
            | Handle JSON Error
            |--------------------------------------------------------------------------
            */

            if (
                is_array($data)
                &&
                isset($data['error'])
            ) {

                $errorMessage =
                    $data['error']['error_mesg']
                    ?? $data['error']['message']
                    ?? 'Claim.MD returned an upload error.';

                Log::warning(
                    'Claim.MD 837 Upload Rejected',
                    [
                        'batch_id' =>
                        $batch->id,

                        'response' =>
                        $data,
                    ]
                );

                return redirect()
                    ->route(
                        'claims.batches.show',
                        ['batchId' => $batch->id]
                    )
                    ->with([
                        'message' =>
                        $errorMessage,

                        'alert-type' =>
                        'danger',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Log Successful Response
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Claim.MD 837 Upload Successful',
                [
                    'batch_id' =>
                    $batch->id,

                    'filename' =>
                    $batch->edi_filename,

                    'response' =>
                    $data
                        ?? $response->body(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'claims.batches.show',
                    ['batchId' => $batch->id]
                )
                ->with([
                    'message' =>
                    '837P batch uploaded successfully to Claim.MD. ' .
                        'Please review the claims in Claim.MD before transmitting.',

                    'alert-type' =>
                    'success',
                ]);
        } catch (\Throwable $e) {

            Log::error(
                'Claim.MD 837 Upload Exception',
                [
                    'batch_id' =>
                    $batchId,

                    'message' =>
                    $e->getMessage(),

                    'trace' =>
                    $e->getTraceAsString(),
                ]
            );

            return redirect()
                ->route(
                    'claims.batches.show',
                    ['batchId' => $batchId]
                )
                ->with([
                    'message' =>
                    'Claim.MD upload error: ' .
                        $e->getMessage(),

                    'alert-type' =>
                    'danger',
                ]);
        }
    } // End Method

    public function batchOpenPdf($batchId)
    {
        $batch = ClaimBatch::findOrFail($batchId);

        if (empty($batch->pdf_filename)) {
            abort(404, 'PDF file not found.');
        }

        $relativePath = 'claims/' . $batch->pdf_filename;

        if (!Storage::disk('adc_private')->exists($relativePath)) {
            abort(404, 'PDF file does not exist.');
        }

        $fullPath = Storage::disk('adc_private')->path($relativePath);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
        ]);
    } // End Method

    public function batchDownloadEdi($batchId)
    {
        $batch = ClaimBatch::findOrFail($batchId);

        if (empty($batch->edi_filename)) {
            abort(404, '837P file not found.');
        }

        $relativePath = 'claims/' . $batch->edi_filename;

        if (!Storage::disk('adc_private')->exists($relativePath)) {
            abort(404, '837P file does not exist.');
        }

        $fullPath = Storage::disk('adc_private')->path($relativePath);

        return response()->download(
            $fullPath,
            $batch->edi_filename,
            [
                'Content-Type' => 'text/plain',
            ]
        );
    } // End Method

    public function specialClaimIndex()
    {
        return view('claims.special_claim');
    } // End Method

    public function specialClaimSearch(Request $request)
    {
        $claimNo = trim((string) $request->get('claim_no'));
        $participantId = trim((string) $request->get('participant_id'));

        if ($claimNo === '' && $participantId === '') {

            return response()->json([
                'success' => false,
                'message' => 'Enter Claim Number or Participant ID.'
            ]);
        }


        $query = DB::table('claims')
            ->select(
                'id',
                'claim_no',
                'ParticipantId',
                'service_from',
                'service_to',
                'proc_code',
                'units',
                'total_amount',
                'status'
            );


        /*
        |--------------------------------------------------------------------------
        | Search by Claim Number
        |--------------------------------------------------------------------------
        */

        if ($claimNo !== '') {

            $claimNo = str_pad(
                $claimNo,
                6,
                '0',
                STR_PAD_LEFT
            );

            $query->where(
                'claim_no',
                $claimNo
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Search by Participant ID
        |--------------------------------------------------------------------------
        */

        if ($participantId !== '') {

            $query->where(
                'ParticipantId',
                $participantId
            );
        }


        $claims = $query
            ->orderByDesc('service_from')
            ->orderByDesc('id')
            ->limit(100)
            ->get();


        return response()->json([
            'success' => true,
            'claims' => $claims
        ]);
    }

    public function specialClaimLoad($id)
    {
        $claim = DB::table('claims')
            ->where('id', $id)
            ->first();

        if (!$claim) {
            abort(404);
        }

        $member = DB::table('billing_members')
            ->where('ParticipantId', trim($claim->ParticipantId))
            ->first();

        return view(
            'claims.special_claim_edit',
            compact('claim', 'member')
        );
    } // End Method

    public function specialClaimStore(Request $request)
    {
        $request->validate([

            'original_claim_id' => [
                'required',
                'integer',
                'exists:claims,id',
            ],

            'special_claim_type' => [
                'required',
                'string',
                'max:50',
            ],

            'special_claim_reason' => [
                'required',
                'string',
                'max:255',
            ],

            'ParticipantId' => [
                'required',
                'string',
                'max:20',
            ],

            'service_from' => [
                'required',
                'date',
            ],

            'service_to' => [
                'required',
                'date',
                'after_or_equal:service_from',
            ],

            'proc_code' => [
                'required',
                'string',
                'max:20',
            ],

            'm1' => [
                'nullable',
                'string',
                'max:10',
            ],

            'units' => [
                'required',
                'numeric',
                'min:1',
            ],

            'amount_per_unit' => [
                'required',
                'numeric',
                'min:0',
            ],

            'total_amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'diag1' => [
                'required',
                'string',
                'max:15',
            ],

            'diag2' => [
                'nullable',
                'string',
                'max:15',
            ],

            'insurance_id' => [
                'nullable',
                'string',
                'max:100',
            ],

            'override_prior_auth_no' => [
                'nullable',
                'string',
                'max:50',
            ],

            'override_dob' => [
                'nullable',
                'date',
            ],
        ]);


        $originalClaim = DB::table('claims')
            ->where('id', $request->original_claim_id)
            ->first();

        if (!$originalClaim) {
            return back()
                ->withInput()
                ->withErrors([
                    'claim' => 'Original claim could not be found.'
                ]);
        }


        try {

            DB::beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Copy Original Claim
            |--------------------------------------------------------------------------
            |
            | We begin with the original row so payer/provider/source information
            | remains intact.
            |
            */

            $newClaim = (array) $originalClaim;


            /*
            |--------------------------------------------------------------------------
            | Remove Auto Increment ID
            |--------------------------------------------------------------------------
            */

            unset($newClaim['id']);


            /*
            |--------------------------------------------------------------------------
            | New Claim Number
            |--------------------------------------------------------------------------
            |
            | Leave NULL.
            | Your normal 837P generation process will assign the next claim number.
            |
            */

            $newClaim['claim_no'] = null;


            /*
            |--------------------------------------------------------------------------
            | New Claim Group
            |--------------------------------------------------------------------------
            */

            $newClaim['claim_group'] = null;

            $newClaim['line_no'] = 1;


            /*
            |--------------------------------------------------------------------------
            | Edited Claim Information
            |--------------------------------------------------------------------------
            */

            $newClaim['ParticipantId'] =
                trim($request->ParticipantId);

            $newClaim['service_from'] =
                $request->service_from;

            $newClaim['service_to'] =
                $request->service_to;

            $newClaim['proc_code'] =
                trim($request->proc_code);

            $newClaim['m1'] =
                trim((string) $request->m1);

            $newClaim['units'] =
                $request->units;

            $newClaim['amount_per_unit'] =
                $request->amount_per_unit;

            $newClaim['total_amount'] =
                $request->total_amount;

            $newClaim['diag1'] =
                trim($request->diag1);

            $newClaim['diag2'] =
                $request->filled('diag2')
                ? trim($request->diag2)
                : null;

            $newClaim['insurance_id'] =
                $request->filled('insurance_id')
                ? trim($request->insurance_id)
                : null;


            /*
            |--------------------------------------------------------------------------
            | Special Claim Information
            |--------------------------------------------------------------------------
            */

            $newClaim['is_special_claim'] = 1;

            $newClaim['original_claim_id'] =
                $originalClaim->id;

            $newClaim['special_claim_type'] =
                $request->special_claim_type;

            $newClaim['special_claim_reason'] =
                trim($request->special_claim_reason);

            $newClaim['override_prior_auth_no'] =
                $request->filled('override_prior_auth_no')
                ? trim($request->override_prior_auth_no)
                : null;

            $newClaim['override_pa_validation'] =
                $request->boolean('override_pa_validation')
                ? 1
                : 0;

            $newClaim['override_member_data'] =
                $request->boolean('override_member_data')
                ? 1
                : 0;


            /*
            |--------------------------------------------------------------------------
            | Demographic Overrides
            |--------------------------------------------------------------------------
            */

            if ($request->boolean('override_member_data')) {

                $newClaim['override_first_name'] =
                    trim((string) $request->override_first_name);

                $newClaim['override_middle_name'] =
                    $request->filled('override_middle_name')
                    ? trim($request->override_middle_name)
                    : null;

                $newClaim['override_last_name'] =
                    trim((string) $request->override_last_name);

                $newClaim['override_dob'] =
                    $request->override_dob ?: null;

                $newClaim['override_address1'] =
                    trim((string) $request->override_address1);

                $newClaim['override_address2'] =
                    $request->filled('override_address2')
                    ? trim($request->override_address2)
                    : null;

                $newClaim['override_city'] =
                    trim((string) $request->override_city);

                $newClaim['override_state'] =
                    trim((string) $request->override_state);

                $newClaim['override_zip'] =
                    trim((string) $request->override_zip);
            } else {

                $newClaim['override_first_name'] = null;
                $newClaim['override_middle_name'] = null;
                $newClaim['override_last_name'] = null;
                $newClaim['override_dob'] = null;
                $newClaim['override_address1'] = null;
                $newClaim['override_address2'] = null;
                $newClaim['override_city'] = null;
                $newClaim['override_state'] = null;
                $newClaim['override_zip'] = null;
            }


            /*
            |--------------------------------------------------------------------------
            | Reset Adjudication / Payment Information
            |--------------------------------------------------------------------------
            |
            | This is a NEW claim. Never copy payment/ERA information from the
            | original claim.
            |
            */

            $newClaim['allowed'] = 0;

            $newClaim['ins1_paid'] = 0;

            $newClaim['adjustment_amount'] = 0;

            $newClaim['patient_responsibility'] = 0;

            $newClaim['deduction_amount'] = 0;

            $newClaim['writeoff_amount'] = 0;

            $newClaim['balance_amount'] =
                $request->total_amount;

            $newClaim['payer_claim_number'] = null;

            $newClaim['submitted_date'] = null;

            $newClaim['paid_date'] = null;

            $newClaim['era_posted_at'] = null;


            /*
            |--------------------------------------------------------------------------
            | Reset Validation / Status
            |--------------------------------------------------------------------------
            */

            $newClaim['status'] = 'draft';

            $newClaim['precheck_result'] = null;

            $newClaim['precheck_message'] = null;


            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            if (array_key_exists('created_at', $newClaim)) {
                $newClaim['created_at'] = now();
            }

            if (array_key_exists('updated_at', $newClaim)) {
                $newClaim['updated_at'] = now();
            }


            /*
            |--------------------------------------------------------------------------
            | Insert New Claim
            |--------------------------------------------------------------------------
            */

            $newClaimId = DB::table('claims')
                ->insertGetId($newClaim);

            DB::table('claims')
                ->where('id', $newClaimId)
                ->update([
                    'claim_group' => 'SPECIAL-'
                        . trim($request->ParticipantId)
                        . '-'
                        . $newClaimId,

                    'source_system' => 'ADC',

                    'updated_at' => now(),
                ]);


            DB::commit();


            return redirect()
                ->route('claims.special.index')
                ->with(
                    'success',
                    'Special claim created successfully. '
                        . 'New draft claim ID: '
                        . $newClaimId
                );
        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'claim' =>
                    'Unable to create special claim: '
                        . $e->getMessage()
                ]);
        }
    } // End Method

    public function billerBatchReport($batchId)
    {
        /*
        |--------------------------------------------------------------------------
        | Claim Batch
        |--------------------------------------------------------------------------
        */

        $batch = ClaimBatch::findOrFail($batchId);


        /*
        |--------------------------------------------------------------------------
        | Billing Groups For This Batch
        |--------------------------------------------------------------------------
        |
        | One row = one biller-compensation claim.
        |
        | Multiple actual claim numbers caused by attendance gaps remain grouped
        | under one billing_group_id.
        |--------------------------------------------------------------------------
        */

        $rows = DB::table('claims_billing_groups as bg')

            ->join(
                'claims as c',
                'c.billing_group_id',
                '=',
                'bg.id'
            )

            ->leftJoin(
                'billing_members as bm',
                DB::raw('TRIM(bm.ParticipantId)'),
                '=',
                DB::raw('TRIM(bg.ParticipantId)')
            )

            ->where(
                'bg.batch_id',
                $batchId
            )

            ->select(

                'bg.id as billing_group_id',

                'bg.ParticipantId',

                'bg.billing_from',

                'bg.billing_to',

                'bg.submitted_by',

                'bg.submitted_at',

                DB::raw("
                    CONCAT(
                        COALESCE(bm.LastName, ''),
                        CASE
                            WHEN bm.LastName IS NOT NULL
                            AND bm.FirstName IS NOT NULL
                            THEN ', '
                            ELSE ''
                        END,
                        COALESCE(bm.FirstName, '')
                    ) AS member_name
                "),

                /*
                |--------------------------------------------------------------------------
                | Insurance
                |--------------------------------------------------------------------------
                */

                DB::raw("
                    COALESCE(
                        MAX(c.payer_fullname),
                        MAX(c.insurance_name),
                        'Unknown'
                    ) AS insurance_name
                "),

                /*
                |--------------------------------------------------------------------------
                | Biller Group Totals
                |--------------------------------------------------------------------------
                */

                DB::raw("
                    SUM(
                        COALESCE(c.units, 0)
                    ) AS total_units
                "),

                DB::raw("
                    SUM(
                        COALESCE(c.total_amount, 0)
                    ) AS total_amount
                "),

                /*
                |--------------------------------------------------------------------------
                | Actual Claim Numbers
                |--------------------------------------------------------------------------
                */

                DB::raw("
                    COUNT(
                        DISTINCT c.claim_no
                    ) AS actual_claim_count
                "),

                DB::raw("
                    GROUP_CONCAT(
                        DISTINCT c.claim_no
                        ORDER BY c.claim_no
                        SEPARATOR ', '
                    ) AS claim_numbers
                ")
            )

            ->groupBy(
                'bg.id',
                'bg.ParticipantId',
                'bg.billing_from',
                'bg.billing_to',
                'bg.submitted_by',
                'bg.submitted_at',
                'bm.LastName',
                'bm.FirstName'
            )

            ->orderBy('insurance_name')
            ->orderBy('bm.LastName')
            ->orderBy('bm.FirstName')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Group By Insurance Company
        |--------------------------------------------------------------------------
        */

        $insuranceGroups =
            $rows->groupBy(
                function ($row) {

                    return
                        trim(
                            (string) (
                                $row->insurance_name
                                ?? 'Unknown'
                            )
                        )
                        ?: 'Unknown';
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Overall Totals
        |--------------------------------------------------------------------------
        */

        $summary = [

            /*
            | One billing group = one biller claim.
            */

            'biller_claims' =>
            $rows->count(),

            /*
            | Actual electronic claim numbers submitted.
            */

            'actual_claims' =>
            $rows->sum(
                function ($row) {

                    return
                        (int) (
                            $row->actual_claim_count
                            ?? 0
                        );
                }
            ),

            'units' =>
            $rows->sum(
                function ($row) {

                    return
                        (int) (
                            $row->total_units
                            ?? 0
                        );
                }
            ),

            'amount' =>
            $rows->sum(
                function ($row) {

                    return
                        (float) (
                            $row->total_amount
                            ?? 0
                        );
                }
            ),
        ];


        return view(
            'claims.claims_biller_batch_report',
            compact(
                'batch',
                'insuranceGroups',
                'summary'
            )
        );
    } // End Method

    public function pendingRebillIndex(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Filter
        |--------------------------------------------------------------------------
        */

        $status =
            trim(
                (string)
                $request->get(
                    'status',
                    'pending'
                )
            );


        if (
            !in_array(
                $status,
                [
                    'pending',
                    'resolved',
                    'archived',
                    'all',
                ],
                true
            )
        ) {

            $status =
                'pending';
        }


        /*
        |--------------------------------------------------------------------------
        | Pending Rebill Rows
        |--------------------------------------------------------------------------
        */

        $query =
            DB::table(
                'claim_pending_rebill'
            );


        if ($status !== 'all') {

            $query->where(
                'status',
                $status
            );
        }


        $rows =
            $query
            ->orderBy(
                'insurance_name'
            )
            ->orderBy(
                'ParticipantId'
            )
            ->orderBy(
                'service_from'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Member Names
        |--------------------------------------------------------------------------
        */

        $participantIds =
            $rows
            ->pluck(
                'ParticipantId'
            )
            ->filter()
            ->map(
                function ($id) {

                    return trim(
                        (string) $id
                    );
                }
            )
            ->unique()
            ->values()
            ->all();


        $membersById =
            collect();


        if (!empty($participantIds)) {

            $members =
                DB::table(
                    'billing_members'
                )
                ->select(
                    'ParticipantId',
                    'LastName',
                    'FirstName',
                    'medicaid_PA_no',
                    'medicaid_PA_Start',
                    'medicaid_PA_End',
                    'renew1_PA_no',
                    'renew1_PA_Start',
                    'renew1_PA_End'
                )
                ->whereIn(
                    DB::raw(
                        'TRIM(ParticipantId)'
                    ),
                    $participantIds
                )
                ->get();


            $membersById =
                $members
                ->keyBy(
                    function ($member) {

                        return trim(
                            (string)
                            $member
                                ->ParticipantId
                        );
                    }
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Attach Member / Current PA Information
        |--------------------------------------------------------------------------
        */

        $rows =
            $rows
            ->map(
                function ($row)
                use ($membersById) {

                    $member =
                        $membersById->get(
                            trim(
                                (string)
                                $row
                                    ->ParticipantId
                            )
                        );


                    $row->member_name =
                        $member

                        ? trim(
                            (
                                $member
                                ->LastName
                                ?? ''
                            )
                                .
                                (
                                    !empty($member
                                        ->FirstName)
                                    ? ', '
                                    . $member
                                    ->FirstName
                                    : ''
                                )
                        )

                        : '';


                    /*
                    |--------------------------------------------------------------------------
                    | Current PA Display
                    |--------------------------------------------------------------------------
                    */

                    $row->current_pa_no =
                        null;

                    $row->current_pa_start =
                        null;

                    $row->current_pa_end =
                        null;


                    if ($member) {

                        /*
                        |--------------------------------------------------------------------------
                        | Prefer Renewal PA
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !empty($member
                                ->renew1_PA_no)
                        ) {

                            $row->current_pa_no =
                                $member
                                ->renew1_PA_no;

                            $row->current_pa_start =
                                $member
                                ->renew1_PA_Start;

                            $row->current_pa_end =
                                $member
                                ->renew1_PA_End;
                        } else {

                            $row->current_pa_no =
                                $member
                                ->medicaid_PA_no;

                            $row->current_pa_start =
                                $member
                                ->medicaid_PA_Start;

                            $row->current_pa_end =
                                $member
                                ->medicaid_PA_End;
                        }
                    }


                    return $row;
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [

            'pending' =>
            DB::table(
                'claim_pending_rebill'
            )
                ->where(
                    'status',
                    'pending'
                )
                ->count(),

            'resolved' =>
            DB::table(
                'claim_pending_rebill'
            )
                ->where(
                    'status',
                    'resolved'
                )
                ->count(),

            'archived' =>
            DB::table(
                'claim_pending_rebill'
            )
                ->where(
                    'status',
                    'archived'
                )
                ->count(),

        ];

        return view(
            'claims.claims_pending_rebill_index',
            compact(
                'rows',
                'status',
                'summary'
            )
        );
    } // End Method

    // public function pendingRebillReprocess($id)
    // {
    //     DB::beginTransaction();


    //     try {

    //         $result =
    //             $this
    //             ->processPendingRebillRow(
    //                 (int) $id
    //             );


    //         if (
    //             $result['success'] !== true
    //         ) {

    //             DB::rollBack();


    //             return back()
    //                 ->with(
    //                     'message',
    //                     $result['message']
    //                 )
    //                 ->with(
    //                     'alert-type',
    //                     'warning'
    //                 );
    //         }


    //         DB::commit();


    //         return back()
    //             ->with(
    //                 'message',
    //                 $result['message']
    //             )
    //             ->with(
    //                 'alert-type',
    //                 'success'
    //             );
    //     } catch (\Throwable $e) {

    //         DB::rollBack();


    //         return back()
    //             ->with(
    //                 'message',
    //                 'Pending claim reprocess failed: '
    //                     . $e->getMessage()
    //             )
    //             ->with(
    //                 'alert-type',
    //                 'error'
    //             );
    //     }
    // } // End Method

    public function pendingRebillReprocess(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Save User Edits Before Reprocessing
            |--------------------------------------------------------------------------
            */

            $edit =
                $request->input(
                    'edit',
                    []
                );

            if (!empty($edit)) {

                DB::table('claim_pending_rebill')
                    ->where('id', (int) $id)
                    ->where('status', 'pending')
                    ->update([

                        'service_from' =>
                        !empty($edit['service_from'])
                            ? Carbon::parse($edit['service_from'])->toDateString()
                            : null,

                        'service_to' =>
                        !empty($edit['service_to'])
                            ? Carbon::parse($edit['service_to'])->toDateString()
                            : null,

                        'proc_code' =>
                        strtoupper(
                            trim(
                                (string) (
                                    $edit['proc_code']
                                    ?? ''
                                )
                            )
                        ),

                        'modifier' =>
                        strtoupper(
                            trim(
                                (string) (
                                    $edit['modifier']
                                    ?? ''
                                )
                            )
                        ),

                        'units' =>
                        isset($edit['units'])
                            ? (int) $edit['units']
                            : null,

                        'amount_per_unit' =>
                        isset($edit['amount_per_unit'])
                            &&
                            $edit['amount_per_unit'] !== ''

                            ? round(
                                (float) $edit['amount_per_unit'],
                                2
                            )

                            : null,

                        'diag1' =>
                        !empty($edit['diag1'])

                            ? strtoupper(
                                trim(
                                    (string) $edit['diag1']
                                )
                            )

                            : null,

                        'diag2' =>
                        !empty($edit['diag2'])

                            ? strtoupper(
                                trim(
                                    (string) $edit['diag2']
                                )
                            )

                            : null,

                        'updated_at' =>
                        now(),

                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Existing Reprocess Logic
            |--------------------------------------------------------------------------
            */

            $result =
                $this
                ->processPendingRebillRow(
                    (int) $id
                );

            if (
                $result['success'] !== true
            ) {

                if (
                    $result['pending_updated']
                    ?? false
                ) {

                    DB::commit();
                } else {

                    DB::rollBack();
                }

                return back()
                    ->with(
                        'message',
                        $result['message']
                    )
                    ->with(
                        'alert-type',
                        'warning'
                    );
            }

            DB::commit();

            return back()
                ->with(
                    'message',
                    $result['message']
                )
                ->with(
                    'alert-type',
                    'success'
                );
        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->with(
                    'message',
                    'Pending claim reprocess failed: '
                        . $e->getMessage()
                )
                ->with(
                    'alert-type',
                    'error'
                );
        }
    } // End Method

    // public function pendingRebillReprocessSelected(Request $request)
    // {

    //     $request->validate([

    //         'selected_rows' =>
    //         [
    //             'required',
    //             'array',
    //             'min:1',
    //         ],

    //         'selected_rows.*' =>
    //         [
    //             'integer',
    //         ],
    //     ]);


    //     $successCount =
    //         0;

    //     $failedCount =
    //         0;

    //     $messages =
    //         [];


    //     foreach (
    //         $request->selected_rows
    //         as $id
    //     ) {

    //         DB::beginTransaction();


    //         try {

    //             $result =
    //                 $this
    //                 ->processPendingRebillRow(
    //                     (int) $id
    //                 );


    //             if (
    //                 $result['success'] === true
    //             ) {

    //                 DB::commit();

    //                 $successCount++;
    //             } else {

    //                 DB::rollBack();

    //                 $failedCount++;

    //                 $messages[] =
    //                     $result['message'];
    //             }
    //         } catch (\Throwable $e) {

    //             DB::rollBack();

    //             $failedCount++;

    //             $messages[] =
    //                 'Pending ID '
    //                 . $id
    //                 . ': '
    //                 . $e
    //                 ->getMessage();
    //         }
    //     }


    //     $message =
    //         "{$successCount} pending claim(s) successfully reprocessed. "
    //         . "{$failedCount} claim(s) still require attention.";


    //     if (!empty($messages)) {

    //         $message .=
    //             ' '
    //             . implode(
    //                 ' | ',
    //                 array_slice(
    //                     $messages,
    //                     0,
    //                     5
    //                 )
    //             );
    //     }


    //     return back()
    //         ->with(
    //             'message',
    //             $message
    //         )
    //         ->with(
    //             'alert-type',
    //             $failedCount > 0
    //                 ? 'warning'
    //                 : 'success'
    //         );
    // } // End Method

    public function pendingRebillReprocessSelected(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'selected_rows' => [
                'required',
                'array',
                'min:1',
            ],

            'selected_rows.*' => [
                'integer',
            ],

            'edits' => [
                'nullable',
                'array',
            ],

        ]);


        $successCount = 0;

        $failedCount = 0;

        $messages = [];


        /*
        |--------------------------------------------------------------------------
        | Process Selected Rows
        |--------------------------------------------------------------------------
        */

        foreach (
            $request->selected_rows
            as $id
        ) {

            DB::beginTransaction();


            try {

                /*
                |--------------------------------------------------------------------------
                | Get Edited Values For This Row
                |--------------------------------------------------------------------------
                */

                $edit =
                    $request->input(
                        'edits.' . $id,
                        []
                    );


                /*
                |--------------------------------------------------------------------------
                | Save User Edits Before Reprocessing
                |--------------------------------------------------------------------------
                */

                if (!empty($edit)) {

                    $updateData = [];


                    /*
                    |--------------------------------------------------------------------------
                    | Service From
                    |--------------------------------------------------------------------------
                    */

                    if (
                        array_key_exists(
                            'service_from',
                            $edit
                        )
                    ) {

                        $updateData['service_from'] =
                            !empty($edit['service_from'])

                            ? Carbon::parse(
                                $edit['service_from']
                            )->toDateString()

                            : null;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Service To
                    |--------------------------------------------------------------------------
                    */

                    if (
                        array_key_exists(
                            'service_to',
                            $edit
                        )
                    ) {

                        $updateData['service_to'] =
                            !empty($edit['service_to'])

                            ? Carbon::parse(
                                $edit['service_to']
                            )->toDateString()

                            : null;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Procedure
                    |--------------------------------------------------------------------------
                    */

                    if (
                        array_key_exists(
                            'proc_code',
                            $edit
                        )
                    ) {

                        $updateData['proc_code'] =
                            strtoupper(
                                trim(
                                    (string)
                                    $edit['proc_code']
                                )
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Modifier
                    |--------------------------------------------------------------------------
                    */

                    if (
                        array_key_exists(
                            'modifier',
                            $edit
                        )
                    ) {

                        $updateData['modifier'] =
                            strtoupper(
                                trim(
                                    (string)
                                    $edit['modifier']
                                )
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Units
                    |--------------------------------------------------------------------------
                    */

                    if (
                        array_key_exists(
                            'units',
                            $edit
                        )
                    ) {

                        $updateData['units'] =
                            $edit['units'] !== ''

                            ? (int)
                            $edit['units']

                            : null;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Amount Per Unit
                    |--------------------------------------------------------------------------
                    */

                    if (
                        array_key_exists(
                            'amount_per_unit',
                            $edit
                        )
                    ) {

                        $updateData['amount_per_unit'] =
                            $edit['amount_per_unit'] !== ''

                            ? round(
                                (float)
                                $edit['amount_per_unit'],
                                2
                            )

                            : null;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Primary Diagnosis
                    |--------------------------------------------------------------------------
                    */

                    if (
                        array_key_exists(
                            'diag1',
                            $edit
                        )
                    ) {

                        $diag1 =
                            strtoupper(
                                trim(
                                    (string)
                                    $edit['diag1']
                                )
                            );

                        $updateData['diag1'] =
                            $diag1 !== ''
                            ? $diag1
                            : null;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Secondary Diagnosis
                    |--------------------------------------------------------------------------
                    */

                    if (
                        array_key_exists(
                            'diag2',
                            $edit
                        )
                    ) {

                        $diag2 =
                            strtoupper(
                                trim(
                                    (string)
                                    $edit['diag2']
                                )
                            );

                        $updateData['diag2'] =
                            $diag2 !== ''
                            ? $diag2
                            : null;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Update Pending Row
                    |--------------------------------------------------------------------------
                    */

                    if (!empty($updateData)) {

                        $updateData['updated_at'] =
                            now();


                        DB::table(
                            'claim_pending_rebill'
                        )
                            ->where(
                                'id',
                                (int) $id
                            )
                            ->where(
                                'status',
                                'pending'
                            )
                            ->update(
                                $updateData
                            );
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Reprocess Current Row
                |--------------------------------------------------------------------------
                */

                $result =
                    $this
                    ->processPendingRebillRow(
                        (int) $id
                    );


                /*
                |--------------------------------------------------------------------------
                | Successfully Reprocessed
                |--------------------------------------------------------------------------
                */

                if (
                    $result['success'] === true
                ) {

                    DB::commit();

                    $successCount++;
                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Keep Updated Validation Result
                    |--------------------------------------------------------------------------
                    |
                    | If processPendingRebillRow refreshed error_type / error_message,
                    | keep those changes instead of rolling them back.
                    |
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $result['pending_updated']
                        ?? false
                    ) {

                        DB::commit();
                    } else {

                        DB::rollBack();
                    }


                    $failedCount++;


                    $messages[] =
                        $result['message'];
                }
            } catch (\Throwable $e) {

                DB::rollBack();


                $failedCount++;


                $messages[] =
                    'Pending ID '
                    . $id
                    . ': '
                    . $e->getMessage();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Final Message
        |--------------------------------------------------------------------------
        */

        $message =
            "{$successCount} pending claim(s) successfully reprocessed. "
            . "{$failedCount} claim(s) still require attention.";


        if (!empty($messages)) {

            $message .=
                ' '
                . implode(
                    ' | ',
                    array_slice(
                        $messages,
                        0,
                        5
                    )
                );
        }


        return back()
            ->with(
                'message',
                $message
            )
            ->with(
                'alert-type',
                $failedCount > 0
                    ? 'warning'
                    : 'success'
            );
    } // End Method

    // private function processPendingRebillRow(int $pendingId): array
    // {

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Pending Record
    //     |--------------------------------------------------------------------------
    //     */

    //     $pending =
    //         DB::table(
    //             'claim_pending_rebill'
    //         )
    //         ->where(
    //             'id',
    //             $pendingId
    //         )
    //         ->lockForUpdate()
    //         ->first();


    //     if (!$pending) {

    //         return [

    //             'success' =>
    //             false,

    //             'message' =>
    //             "Pending rebill #{$pendingId} was not found.",

    //         ];
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Already Resolved
    //     |--------------------------------------------------------------------------
    //     */

    //     if (
    //         $pending
    //         ->status ===
    //         'resolved'
    //     ) {

    //         return [

    //             'success' =>
    //             false,

    //             'message' =>
    //             "Pending rebill #{$pendingId} is already resolved.",

    //         ];
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Billing Member
    //     |--------------------------------------------------------------------------
    //     */

    //     $member =
    //         DB::table(
    //             'billing_members'
    //         )
    //         ->whereRaw(
    //             'TRIM(ParticipantId) = ?',
    //             [
    //                 trim(
    //                     (string)
    //                     $pending
    //                         ->ParticipantId
    //                 )
    //             ]
    //         )
    //         ->first();


    //     if (!$member) {

    //         return [

    //             'success' =>
    //             false,

    //             'message' =>
    //             'Member '
    //                 . $pending
    //                 ->ParticipantId
    //                 . ' could not be found.',

    //         ];
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Revalidate Current PA
    //     |--------------------------------------------------------------------------
    //     |
    //     | Use the same full-range PA validation rules already used by claims.
    //     |--------------------------------------------------------------------------
    //     */

    //     $claimForValidation =
    //         (object) [

    //             'ParticipantId' =>
    //             $pending
    //                 ->ParticipantId,

    //             'service_from' =>
    //             $pending
    //                 ->service_from,

    //             'service_to' =>
    //             $pending
    //                 ->service_to,

    //             'is_special_claim' =>
    //             0,

    //             'override_pa_validation' =>
    //             0,

    //         ];


    //     $pa =
    //         $this
    //         ->resolvePriorAuthorization(
    //             $claimForValidation,
    //             $member
    //         );


    //     if (
    //         (
    //             $pa['valid']
    //             ?? false
    //         ) !== true
    //     ) {

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Keep Pending - Update Reason
    //         |--------------------------------------------------------------------------
    //         */

    //         DB::table(
    //             'claim_pending_rebill'
    //         )
    //             ->where(
    //                 'id',
    //                 $pendingId
    //             )
    //             ->update([

    //                 'error_type' =>
    //                 'PA',

    //                 'error_message' =>
    //                 Str::limit(
    //                     $pa['message']
    //                         ??
    //                         'No valid PA covers this service period.',
    //                     255,
    //                     ''
    //                 ),

    //                 'updated_at' =>
    //                 now(),

    //             ]);


    //         return [

    //             'success' =>
    //             false,

    //             'message' =>
    //             'Member '
    //                 . $pending
    //                 ->ParticipantId
    //                 . ': '
    //                 . (
    //                     $pa['message']
    //                     ??
    //                     'PA is still invalid.'
    //                 ),

    //         ];
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | New Valid PA
    //     |--------------------------------------------------------------------------
    //     */

    //     $priorAuthNo =
    //         $pa['pa_no']
    //         ?? null;


    //     $priorAuthExpiry =
    //         $pa['pa_end']
    //         ?? null;


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Insurance
    //     |--------------------------------------------------------------------------
    //     */

    //     $insuranceName =
    //         $member
    //         ->HMO_Enrolled
    //         ?? $pending
    //         ->insurance_name;


    //     $insuranceId =
    //         $member
    //         ->medicaid_HMO_id
    //         ?? $pending
    //         ->insurance_id;


    //     $insurance =
    //         null;


    //     if (
    //         !empty($insuranceName)
    //     ) {

    //         $insurance =
    //             DB::table(
    //                 'insurance_companies'
    //             )
    //             ->where(
    //                 'code',
    //                 $insuranceName
    //             )
    //             ->first();
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Duplicate Claim Protection
    //     |--------------------------------------------------------------------------
    //     */

    //     $existingClaim =
    //         DB::table(
    //             'claims'
    //         )
    //         ->whereRaw(
    //             'TRIM(ParticipantId) = ?',
    //             [
    //                 trim(
    //                     (string)
    //                     $pending
    //                         ->ParticipantId
    //                 )
    //             ]
    //         )
    //         ->whereDate(
    //             'service_from',
    //             $pending
    //                 ->service_from
    //         )
    //         ->whereDate(
    //             'service_to',
    //             $pending
    //                 ->service_to
    //         )
    //         ->where(
    //             'proc_code',
    //             $pending
    //                 ->proc_code
    //         )
    //         ->where(
    //             'm1',
    //             $pending
    //                 ->modifier
    //         )
    //         ->where(
    //             'units',
    //             $pending
    //                 ->units
    //         )
    //         ->where(
    //             'total_amount',
    //             $pending
    //                 ->total_amount
    //         )
    //         ->first();


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Matching Claim Already Exists
    //     |--------------------------------------------------------------------------
    //     |
    //     | Do not create another claim.
    //     |--------------------------------------------------------------------------
    //     */

    //     if ($existingClaim) {

    //         DB::table(
    //             'claim_pending_rebill'
    //         )
    //             ->where(
    //                 'id',
    //                 $pendingId
    //             )
    //             ->update([

    //                 'status' =>
    //                 'resolved',

    //                 'resolved_claim_id' =>
    //                 $existingClaim
    //                     ->id,

    //                 'resolved_at' =>
    //                 now(),

    //                 'error_message' =>
    //                 'Resolved: matching claim already exists.',

    //                 'updated_at' =>
    //                 now(),

    //             ]);


    //         return [

    //             'success' =>
    //             true,

    //             'message' =>
    //             'Pending rebill #'
    //                 . $pendingId
    //                 . ' matched existing Claim ID '
    //                 . $existingClaim
    //                 ->id
    //                 . '.',

    //         ];
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Claim Rate
    //     |--------------------------------------------------------------------------
    //     */

    //     $amountPerUnit =
    //         round(
    //             (float) (
    //                 $pending
    //                 ->amount_per_unit
    //                 ?? 0
    //             ),
    //             2
    //         );


    //     if ($amountPerUnit <= 0) {

    //         $rate =
    //             $this
    //             ->getClaimRateForDate(

    //                 $pending
    //                     ->service_from,

    //                 $pending
    //                     ->proc_code
    //                     ?? 'S5102',

    //                 $pending
    //                     ->modifier
    //                     ?? 'U1'
    //             );


    //         if (!$rate) {

    //             return [

    //                 'success' =>
    //                 false,

    //                 'message' =>
    //                 'No claim rate found for '
    //                     . Carbon::parse(
    //                         $pending
    //                             ->service_from
    //                     )
    //                     ->format(
    //                         'm/d/Y'
    //                     )
    //                     . '.',

    //             ];
    //         }


    //         $amountPerUnit =
    //             round(
    //                 (float)
    //                 $rate
    //                     ->amount_per_unit,
    //                 2
    //             );
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Units / Total
    //     |--------------------------------------------------------------------------
    //     */

    //     $units =
    //         (int) (
    //             $pending
    //             ->units
    //             ?? 0
    //         );


    //     if ($units <= 0) {

    //         return [

    //             'success' =>
    //             false,

    //             'message' =>
    //             'Pending rebill #'
    //                 . $pendingId
    //                 . ' has invalid units.',

    //         ];
    //     }


    //     $totalAmount =
    //         round(
    //             $units
    //                 *
    //                 $amountPerUnit,
    //             2
    //         );


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Diagnosis
    //     |--------------------------------------------------------------------------
    //     |
    //     | Prefer the current member diagnosis, but retain the pending snapshot as
    //     | fallback.
    //     |--------------------------------------------------------------------------
    //     */

    //     $diag1 =
    //         trim(
    //             (string) (
    //                 $member
    //                 ->PrimaryDiagnosis1
    //                 ??
    //                 $pending
    //                 ->diag1
    //                 ??
    //                 ''
    //             )
    //         );


    //     $diag2 =
    //         trim(
    //             (string) (
    //                 $member
    //                 ->SecondaryDiag1
    //                 ??
    //                 $pending
    //                 ->diag2
    //                 ??
    //                 ''
    //             )
    //         );


    //     if ($diag1 === '') {

    //         return [

    //             'success' =>
    //             false,

    //             'message' =>
    //             'Member '
    //                 . $pending
    //                 ->ParticipantId
    //                 . ' does not have a primary diagnosis.',

    //         ];
    //     }


    //     /*
    //     |--------------------------------------------------------------------------
    //     | New Claim Group
    //     |--------------------------------------------------------------------------
    //     */

    //     $claimGroup =
    //         trim(
    //             (string)
    //             $pending
    //                 ->ParticipantId
    //         )
    //         . '-RB-'
    //         . now()
    //         ->format(
    //             'YmdHis'
    //         )
    //         . '-'
    //         . $pendingId;


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Next Line Number
    //     |--------------------------------------------------------------------------
    //     */

    //     $maxLineNo =
    //         DB::table(
    //             'claims'
    //         )
    //         ->whereRaw(
    //             'TRIM(ParticipantId) = ?',
    //             [
    //                 trim(
    //                     (string)
    //                     $pending
    //                         ->ParticipantId
    //                 )
    //             ]
    //         )
    //         ->max(
    //             'line_no'
    //         );


    //     $lineNo =
    //         ((int) $maxLineNo)
    //         +
    //         1;


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Insert New Draft Claim
    //     |--------------------------------------------------------------------------
    //     */

    //     $claimId =
    //         DB::table(
    //             'claims'
    //         )
    //         ->insertGetId([

    //             'claim_no' =>
    //             null,

    //             'claim_group' =>
    //             $claimGroup,

    //             'line_no' =>
    //             $lineNo,

    //             'ParticipantId' =>
    //             $pending
    //                 ->ParticipantId,


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Insurance
    //             |--------------------------------------------------------------------------
    //             */

    //             'insurance_name' =>
    //             $insuranceName,

    //             'payer_fullname' =>
    //             $insurance
    //                 ->full_name
    //                 ?? null,

    //             'payer_id' =>
    //             $insurance
    //                 ->payer_id
    //                 ?? null,

    //             'payer_address1' =>
    //             $insurance
    //                 ->address1
    //                 ?? null,

    //             'payer_address2' =>
    //             $insurance
    //                 ->address2
    //                 ?? null,

    //             'payer_city' =>
    //             $insurance
    //                 ->city
    //                 ?? null,

    //             'payer_state' =>
    //             $insurance
    //                 ->state
    //                 ?? null,

    //             'payer_zip' =>
    //             $insurance
    //                 ->zip
    //                 ?? null,

    //             'insurance_id' =>
    //             $insuranceId,


    //             /*
    //             |--------------------------------------------------------------------------
    //             | PA
    //             |--------------------------------------------------------------------------
    //             */

    //             'prior_auth_no' =>
    //             $priorAuthNo,

    //             'prior_auth_expiry' =>
    //             $priorAuthExpiry,


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Service
    //             |--------------------------------------------------------------------------
    //             */

    //             'service_from' =>
    //             Carbon::parse(
    //                 $pending
    //                     ->service_from
    //             )
    //                 ->toDateString(),

    //             'service_to' =>
    //             Carbon::parse(
    //                 $pending
    //                     ->service_to
    //             )
    //                 ->toDateString(),

    //             'proc_code' =>
    //             $pending
    //                 ->proc_code
    //                 ?? 'S5102',

    //             'm1' =>
    //             $pending
    //                 ->modifier
    //                 ?? 'U1',

    //             'units' =>
    //             $units,

    //             'amount_per_unit' =>
    //             $amountPerUnit,

    //             'total_amount' =>
    //             $totalAmount,

    //             'allowed' =>
    //             $totalAmount,

    //             'balance_amount' =>
    //             $totalAmount,


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Diagnosis
    //             |--------------------------------------------------------------------------
    //             */

    //             'diag1' =>
    //             $diag1,

    //             'diag2' =>
    //             $diag2 !== ''
    //                 ? $diag2
    //                 : null,


    //             /*
    //             |--------------------------------------------------------------------------
    //             | Status
    //             |--------------------------------------------------------------------------
    //             |
    //             | Keep it draft. Your normal Check Claims process will validate it
    //             | before it becomes READY.
    //             |--------------------------------------------------------------------------
    //             */

    //             'status' =>
    //             'draft',

    //             'precheck_result' =>
    //             null,

    //             'precheck_message' =>
    //             null,

    //             'created_at' =>
    //             now(),

    //             'updated_at' =>
    //             now(),

    //         ]);


    //     /*
    //     |--------------------------------------------------------------------------
    //     | Mark Pending Record Resolved
    //     |--------------------------------------------------------------------------
    //     */

    //     DB::table(
    //         'claim_pending_rebill'
    //     )
    //         ->where(
    //             'id',
    //             $pendingId
    //         )
    //         ->update([

    //             'prior_auth_no' =>
    //             $priorAuthNo,

    //             'prior_auth_expiry' =>
    //             $priorAuthExpiry,

    //             'status' =>
    //             'resolved',

    //             'resolved_claim_id' =>
    //             $claimId,

    //             'resolved_at' =>
    //             now(),

    //             'error_message' =>
    //             'Revalidated successfully and converted to draft claim.',

    //             'updated_at' =>
    //             now(),

    //         ]);


    //     return [

    //         'success' =>
    //         true,

    //         'claim_id' =>
    //         $claimId,

    //         'message' =>
    //         'Pending rebill #'
    //             . $pendingId
    //             . ' successfully created as draft Claim ID '
    //             . $claimId
    //             . '.',

    //     ];
    // } // End Method
    private function processPendingRebillRow(int $pendingId): array
    {
        /*
        |--------------------------------------------------------------------------
        | Pending Record
        |--------------------------------------------------------------------------
        */

        $pending =
            DB::table(
                'claim_pending_rebill'
            )
            ->where(
                'id',
                $pendingId
            )
            ->lockForUpdate()
            ->first();


        if (!$pending) {

            return [

                'success' =>
                false,

                'message' =>
                "Pending rebill #{$pendingId} was not found.",

                'pending_updated' =>
                false,

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Already Resolved
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $pending->status,
                [
                    'resolved',
                    'archived',
                ],
                true
            )
        ) {

            return [

                'success' =>
                false,

                'message' =>
                "Pending rebill #{$pendingId} is already "
                    . $pending->status
                    . ".",

                'pending_updated' =>
                false,

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Billing Member
        |--------------------------------------------------------------------------
        */

        $member =
            DB::table(
                'billing_members'
            )
            ->whereRaw(
                'TRIM(ParticipantId) = ?',
                [
                    trim(
                        (string)
                        $pending->ParticipantId
                    )
                ]
            )
            ->first();


        if (!$member) {

            DB::table(
                'claim_pending_rebill'
            )
                ->where(
                    'id',
                    $pendingId
                )
                ->update([

                    'error_type' =>
                    'MEMBER',

                    'error_message' =>
                    Str::limit(
                        'Member '
                            . $pending->ParticipantId
                            . ' could not be found.',
                        255,
                        ''
                    ),

                    'updated_at' =>
                    now(),

                ]);


            return [

                'success' =>
                false,

                'message' =>
                'Member '
                    . $pending->ParticipantId
                    . ' could not be found.',

                'pending_updated' =>
                true,

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Error Collection
        |--------------------------------------------------------------------------
        */

        $errors = [];

        $errorTypes = [];


        /*
        |--------------------------------------------------------------------------
        | Service Dates
        |--------------------------------------------------------------------------
        */

        $serviceFrom = null;

        $serviceTo = null;


        try {

            $serviceFrom =
                Carbon::parse(
                    $pending->service_from
                )->startOfDay();


            $serviceTo =
                Carbon::parse(
                    $pending->service_to
                )->startOfDay();


            if (
                $serviceTo->lt(
                    $serviceFrom
                )
            ) {

                $errors[] =
                    'Service To date cannot be before Service From date.';

                $errorTypes[] =
                    'SERVICE_DATE';
            }


            if (
                $serviceFrom->isFuture()
                ||
                $serviceTo->isFuture()
            ) {

                $errors[] =
                    'Service dates cannot be in the future.';

                $errorTypes[] =
                    'SERVICE_DATE';
            }
        } catch (\Throwable $e) {

            $errors[] =
                'Invalid service date.';

            $errorTypes[] =
                'SERVICE_DATE';
        }


        /*
        |--------------------------------------------------------------------------
        | Units
        |--------------------------------------------------------------------------
        */

        $units =
            (int) (
                $pending->units
                ?? 0
            );


        if ($units <= 0) {

            $errors[] =
                'Invalid units.';

            $errorTypes[] =
                'AMOUNT';
        }


        /*
        |--------------------------------------------------------------------------
        | Procedure / Modifier
        |--------------------------------------------------------------------------
        */

        $procCode =
            trim(
                (string) (
                    $pending->proc_code
                    ?? ''
                )
            );


        $modifier =
            trim(
                (string) (
                    $pending->modifier
                    ?? ''
                )
            );


        if ($procCode === '') {

            $errors[] =
                'Procedure code is missing.';

            $errorTypes[] =
                'PROCEDURE';
        }


        /*
        |--------------------------------------------------------------------------
        | Claim Rate
        |--------------------------------------------------------------------------
        */

        $amountPerUnit =
            round(
                (float) (
                    $pending->amount_per_unit
                    ?? 0
                ),
                2
            );


        if (
            $amountPerUnit <= 0
            &&
            $serviceFrom !== null
        ) {

            $rate =
                $this
                ->getClaimRateForDate(

                    $pending->service_from,

                    $procCode !== ''
                        ? $procCode
                        : 'S5102',

                    $modifier !== ''
                        ? $modifier
                        : 'U1'

                );


            if (!$rate) {

                $errors[] =
                    'No claim rate found for '
                    . $serviceFrom->format(
                        'm/d/Y'
                    )
                    . '.';

                $errorTypes[] =
                    'AMOUNT';
            } else {

                $amountPerUnit =
                    round(
                        (float)
                        $rate->amount_per_unit,
                        2
                    );
            }
        }


        if ($amountPerUnit <= 0) {

            $errors[] =
                'Amount per unit must be greater than zero.';

            $errorTypes[] =
                'AMOUNT';
        }


        /*
        |--------------------------------------------------------------------------
        | Total Amount
        |--------------------------------------------------------------------------
        */

        $totalAmount =
            round(
                $units
                    *
                    $amountPerUnit,
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Diagnosis
        |--------------------------------------------------------------------------
        |
        | Prefer the CURRENT member diagnosis.
        |
        | If member diagnosis is blank, use the pending snapshot as fallback.
        |
        |--------------------------------------------------------------------------
        */

        // $diag1 =
        //     strtoupper(
        //         trim(
        //             (string) (
        //                 $member->PrimaryDiagnosis1
        //                 ??
        //                 $pending->diag1
        //                 ??
        //                 ''
        //             )
        //         )
        //     );


        // $diag2 =
        //     strtoupper(
        //         trim(
        //             (string) (
        //                 $member->SecondaryDiag1
        //                 ??
        //                 $pending->diag2
        //                 ??
        //                 ''
        //             )
        //         )
        //     );

        $diag1 =
            strtoupper(
                trim(
                    (string) (
                        $pending->diag1
                        ??
                        $member->PrimaryDiagnosis1
                        ??
                        ''
                    )
                )
            );

        $diag2 =
            strtoupper(
                trim(
                    (string) (
                        $pending->diag2
                        ??
                        $member->SecondaryDiag1
                        ??
                        ''
                    )
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Primary Diagnosis Required
        |--------------------------------------------------------------------------
        */

        if ($diag1 === '') {

            $errors[] =
                'Member '
                . $pending->ParticipantId
                . ' does not have a primary diagnosis.';

            $errorTypes[] =
                'DIAGNOSIS';
        } else {

            $diag1Exists =
                DB::table(
                    'icd10_codes'
                )
                ->where(
                    'code',
                    $diag1
                )
                ->exists();


            if (!$diag1Exists) {

                $errors[] =
                    'Invalid Primary ICD: '
                    . $diag1
                    . '.';

                $errorTypes[] =
                    'DIAGNOSIS';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Secondary Diagnosis - Optional But Must Be Valid If Present
        |--------------------------------------------------------------------------
        */

        if ($diag2 !== '') {

            $diag2Exists =
                DB::table(
                    'icd10_codes'
                )
                ->where(
                    'code',
                    $diag2
                )
                ->exists();


            if (!$diag2Exists) {

                $errors[] =
                    'Invalid Secondary ICD: '
                    . $diag2
                    . '.';

                $errorTypes[] =
                    'DIAGNOSIS';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Member Information
        |--------------------------------------------------------------------------
        */

        if (
            empty($member->Address1)
            ||
            empty($member->City)
            ||
            empty($member->State)
            ||
            empty($member->ZipCode)
        ) {

            $errors[] =
                'Member address information is incomplete.';

            $errorTypes[] =
                'MEMBER';
        }


        /*
        |--------------------------------------------------------------------------
        | Date Of Birth
        |--------------------------------------------------------------------------
        */

        if (
            empty($member->DateOfBirth)
        ) {

            $errors[] =
                'Member Date of Birth is missing.';

            $errorTypes[] =
                'MEMBER';
        } else {

            try {

                $dob =
                    Carbon::parse(
                        $member->DateOfBirth
                    );


                if ($dob->isFuture()) {

                    $errors[] =
                        'Member Date of Birth cannot be in the future.';

                    $errorTypes[] =
                        'MEMBER';
                }
            } catch (\Throwable $e) {

                $errors[] =
                    'Member Date of Birth is invalid.';

                $errorTypes[] =
                    'MEMBER';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Insurance
        |--------------------------------------------------------------------------
        */

        $insuranceName =
            $member->HMO_Enrolled
            ??
            $pending->insurance_name;


        $insuranceId =
            $member->medicaid_HMO_id
            ??
            $pending->insurance_id;


        $insurance =
            null;


        if (
            !empty($insuranceName)
        ) {

            $insurance =
                DB::table(
                    'insurance_companies'
                )
                ->where(
                    'code',
                    $insuranceName
                )
                ->first();


            if (!$insurance) {

                $errors[] =
                    'Insurance company '
                    . $insuranceName
                    . ' could not be found.';

                $errorTypes[] =
                    'INSURANCE';
            }
        } else {

            $errors[] =
                'Insurance/HMO is missing.';

            $errorTypes[] =
                'INSURANCE';
        }


        /*
        |--------------------------------------------------------------------------
        | Prior Authorization
        |--------------------------------------------------------------------------
        |
        | Use the SAME existing PA resolver already used by the claims system.
        |
        |--------------------------------------------------------------------------
        */

        $priorAuthNo =
            null;


        $priorAuthExpiry =
            null;


        if (
            $serviceFrom !== null
            &&
            $serviceTo !== null
        ) {

            $claimForValidation =
                (object) [

                    'ParticipantId' =>
                    $pending->ParticipantId,

                    'service_from' =>
                    $pending->service_from,

                    'service_to' =>
                    $pending->service_to,

                    'is_special_claim' =>
                    0,

                    'override_pa_validation' =>
                    0,

                ];


            $pa =
                $this
                ->resolvePriorAuthorization(
                    $claimForValidation,
                    $member
                );


            if (
                (
                    $pa['valid']
                    ?? false
                ) !== true
            ) {

                $errors[] =
                    $pa['message']
                    ??
                    'No valid PA covers this service period.';

                $errorTypes[] =
                    'PA';
            } else {

                $priorAuthNo =
                    $pa['pa_no']
                    ?? null;


                $priorAuthExpiry =
                    $pa['pa_end']
                    ?? null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Error Types
        |--------------------------------------------------------------------------
        */

        $errorTypes =
            array_values(
                array_unique(
                    $errorTypes
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Still Invalid
        |--------------------------------------------------------------------------
        |
        | Keep this record in claim_pending_rebill.
        |
        | Refresh diagnosis/current PA/error information so the screen reflects
        | exactly what still needs attention.
        |
        |--------------------------------------------------------------------------
        */

        if (!empty($errors)) {

            $errorType =
                !empty($errorTypes)

                ? implode(
                    '+',
                    $errorTypes
                )

                : 'OTHER';


            $errorMessage =
                implode(
                    ' ',
                    array_unique(
                        $errors
                    )
                );


            $updateData = [

                'insurance_name' =>
                $insuranceName,

                'insurance_id' =>
                $insuranceId,

                'diag1' =>
                $diag1 !== ''
                    ? $diag1
                    : null,

                'diag2' =>
                $diag2 !== ''
                    ? $diag2
                    : null,

                'error_type' =>
                $errorType,

                'error_message' =>
                Str::limit(
                    $errorMessage,
                    255,
                    ''
                ),

                'updated_at' =>
                now(),

            ];


            /*
            |--------------------------------------------------------------------------
            | Only Replace PA Snapshot If We Found A Currently Valid PA
            |--------------------------------------------------------------------------
            */

            if ($priorAuthNo !== null) {

                $updateData['prior_auth_no'] =
                    $priorAuthNo;


                $updateData['prior_auth_expiry'] =
                    $priorAuthExpiry;
            }


            DB::table(
                'claim_pending_rebill'
            )
                ->where(
                    'id',
                    $pendingId
                )
                ->update(
                    $updateData
                );


            return [

                'success' =>
                false,

                'message' =>
                'Member '
                    . $pending->ParticipantId
                    . ': '
                    . $errorMessage,

                'pending_updated' =>
                true,

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Duplicate Claim Protection
        |--------------------------------------------------------------------------
        */

        $existingClaim =
            DB::table(
                'claims'
            )
            ->whereRaw(
                'TRIM(ParticipantId) = ?',
                [
                    trim(
                        (string)
                        $pending->ParticipantId
                    )
                ]
            )
            ->whereDate(
                'service_from',
                $pending->service_from
            )
            ->whereDate(
                'service_to',
                $pending->service_to
            )
            ->where(
                'proc_code',
                $procCode
            )
            ->where(
                'm1',
                $modifier
            )
            ->where(
                'units',
                $units
            )
            ->where(
                'total_amount',
                $totalAmount
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Matching Claim Already Exists
        |--------------------------------------------------------------------------
        */

        if ($existingClaim) {

            DB::table(
                'claim_pending_rebill'
            )
                ->where(
                    'id',
                    $pendingId
                )
                ->update([

                    'insurance_name' =>
                    $insuranceName,

                    'insurance_id' =>
                    $insuranceId,

                    'diag1' =>
                    $diag1,

                    'diag2' =>
                    $diag2 !== ''
                        ? $diag2
                        : null,

                    'prior_auth_no' =>
                    $priorAuthNo,

                    'prior_auth_expiry' =>
                    $priorAuthExpiry,

                    'status' =>
                    'resolved',

                    'resolved_claim_id' =>
                    $existingClaim->id,

                    'resolved_at' =>
                    now(),

                    'error_type' =>
                    null,

                    'error_message' =>
                    'Resolved: matching claim already exists.',

                    'updated_at' =>
                    now(),

                ]);


            return [

                'success' =>
                true,

                'claim_id' =>
                $existingClaim->id,

                'message' =>
                'Pending rebill #'
                    . $pendingId
                    . ' matched existing Claim ID '
                    . $existingClaim->id
                    . '.',

                'pending_updated' =>
                true,

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | New Claim Group
        |--------------------------------------------------------------------------
        */

        $claimGroup =
            trim(
                (string)
                $pending->ParticipantId
            )
            . '-RB-'
            . now()->format(
                'YmdHis'
            )
            . '-'
            . $pendingId;


        /*
        |--------------------------------------------------------------------------
        | Next Line Number
        |--------------------------------------------------------------------------
        */

        $maxLineNo =
            DB::table(
                'claims'
            )
            ->whereRaw(
                'TRIM(ParticipantId) = ?',
                [
                    trim(
                        (string)
                        $pending->ParticipantId
                    )
                ]
            )
            ->max(
                'line_no'
            );


        $lineNo =
            ((int) $maxLineNo)
            +
            1;


        /*
        |--------------------------------------------------------------------------
        | Insert New Draft Claim
        |--------------------------------------------------------------------------
        */

        $claimId =
            DB::table(
                'claims'
            )
            ->insertGetId([

                'claim_no' =>
                null,

                'claim_group' =>
                $claimGroup,

                'line_no' =>
                $lineNo,

                'ParticipantId' =>
                $pending->ParticipantId,


                /*
                |--------------------------------------------------------------------------
                | Insurance
                |--------------------------------------------------------------------------
                */

                'insurance_name' =>
                $insuranceName,

                'payer_fullname' =>
                $insurance->full_name
                    ?? null,

                'payer_id' =>
                $insurance->payer_id
                    ?? null,

                'payer_address1' =>
                $insurance->address1
                    ?? null,

                'payer_address2' =>
                $insurance->address2
                    ?? null,

                'payer_city' =>
                $insurance->city
                    ?? null,

                'payer_state' =>
                $insurance->state
                    ?? null,

                'payer_zip' =>
                $insurance->zip
                    ?? null,

                'insurance_id' =>
                $insuranceId,


                /*
                |--------------------------------------------------------------------------
                | PA
                |--------------------------------------------------------------------------
                */

                'prior_auth_no' =>
                $priorAuthNo,

                'prior_auth_expiry' =>
                $priorAuthExpiry,


                /*
                |--------------------------------------------------------------------------
                | Service
                |--------------------------------------------------------------------------
                */

                'service_from' =>
                $serviceFrom
                    ->toDateString(),

                'service_to' =>
                $serviceTo
                    ->toDateString(),

                'proc_code' =>
                $procCode !== ''
                    ? $procCode
                    : 'S5102',

                'm1' =>
                $modifier !== ''
                    ? $modifier
                    : 'U1',

                'units' =>
                $units,

                'amount_per_unit' =>
                $amountPerUnit,

                'total_amount' =>
                $totalAmount,

                'allowed' =>
                $totalAmount,

                'balance_amount' =>
                $totalAmount,


                /*
                |--------------------------------------------------------------------------
                | Diagnosis
                |--------------------------------------------------------------------------
                */

                'diag1' =>
                $diag1,

                'diag2' =>
                $diag2 !== ''
                    ? $diag2
                    : null,


                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                |
                | Keep it draft.
                |
                | Your normal Check Claims process will validate it again before
                | making it READY.
                |
                |--------------------------------------------------------------------------
                */

                'status' =>
                'draft',

                'precheck_result' =>
                null,

                'precheck_message' =>
                null,

                'created_at' =>
                now(),

                'updated_at' =>
                now(),

            ]);


        /*
        |--------------------------------------------------------------------------
        | Mark Pending Record Resolved
        |--------------------------------------------------------------------------
        */

        DB::table(
            'claim_pending_rebill'
        )
            ->where(
                'id',
                $pendingId
            )
            ->update([

                'insurance_name' =>
                $insuranceName,

                'insurance_id' =>
                $insuranceId,

                'diag1' =>
                $diag1,

                'diag2' =>
                $diag2 !== ''
                    ? $diag2
                    : null,

                'prior_auth_no' =>
                $priorAuthNo,

                'prior_auth_expiry' =>
                $priorAuthExpiry,

                'status' =>
                'resolved',

                'resolved_claim_id' =>
                $claimId,

                'resolved_at' =>
                now(),

                'error_type' =>
                null,

                'error_message' =>
                'Revalidated successfully and converted to draft claim.',

                'updated_at' =>
                now(),

            ]);


        return [

            'success' =>
            true,

            'claim_id' =>
            $claimId,

            'message' =>
            'Pending rebill #'
                . $pendingId
                . ' successfully created as draft Claim ID '
                . $claimId
                . '.',

            'pending_updated' =>
            true,

        ];
    } // End Method


    public function claimInquiry(Request $request)
    {
        $claim = null;

        if ($request->filled('claim_no')) {

            $claimNo = trim($request->claim_no);

            $claim = DB::table('claims')
                ->where('claim_no', $claimNo)
                ->first();
        }

        return view('claims.claims_claim_inquiry', compact('claim'));
    } // End Method




    /*
    |--------------------------------------------------------------------------
    | Resubmission Logical Group Key
    |--------------------------------------------------------------------------
    |
    | This method is ONLY used by resubmission generation.
    |
    | Normal claims are unaffected.
    |
    */

    private function resubmissionGroupKey($claim): string
    {
        $participantId =
            trim(
                (string)
                (
                    $claim->ParticipantId
                    ?? ''
                )
            );


        $payer =
            trim(
                (string)
                (
                    $claim->insurance_name
                    ?? ''
                )
            );


        $originalClaimNo =
            trim(
                (string)
                (
                    $claim->original_claim_no
                    ?? ''
                )
            );


        $claimGroup =
            trim(
                (string)
                (
                    $claim->claim_group
                    ?? ''
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Preferred Key
        |--------------------------------------------------------------------------
        */

        if ($claimGroup !== '') {

            return
                $participantId
                . '|'
                . $payer
                . '|'
                . $originalClaimNo
                . '|'
                . $claimGroup;
        }


        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        */

        return
            $participantId
            . '|'
            . $payer
            . '|'
            . $originalClaimNo;
    } // End Method

    public function resubmissionPreview()
    {
        /*
        |--------------------------------------------------------------------------
        | Load Only Pending Resubmission Rows
        |--------------------------------------------------------------------------
        */

        $claims = DB::table('claims')
            ->whereNotNull('resubmission_of_claim_id')
            ->where('status', 'ready')
            ->where('precheck_result', 'valid')
            ->whereNull('claim_no')
            ->orderBy('ParticipantId')
            ->orderBy('service_from')
            ->get();


        if ($claims->isEmpty()) {

            return redirect()
                ->route('claims.rejections.ready')
                ->with([
                    'message' =>
                    'No resubmission claims are currently ready.',
                    'alert-type' =>
                    'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Member Lookup
        |--------------------------------------------------------------------------
        */

        $participantIds = $claims
            ->pluck('ParticipantId')
            ->filter()
            ->map(function ($id) {
                return trim((string) $id);
            })
            ->unique()
            ->values();


        $members = collect();


        if ($participantIds->isNotEmpty()) {

            $members = DB::table('billing_members')
                ->whereIn(
                    'ParticipantId',
                    $participantIds
                )
                ->select(
                    'ParticipantId',
                    'LastName',
                    'FirstName'
                )
                ->get()
                ->keyBy(function ($member) {

                    return trim(
                        (string)
                        $member->ParticipantId
                    );
                });
        }


        /*
        |--------------------------------------------------------------------------
        | Attach Member Name
        |--------------------------------------------------------------------------
        */

        foreach ($claims as $claim) {

            $participantId =
                trim(
                    (string)
                    ($claim->ParticipantId ?? '')
                );


            $member =
                $members->get($participantId);


            $claim->member_lastname =
                $member->LastName ?? '';


            $claim->member_firstname =
                $member->FirstName ?? '';
        }


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $totalRows =
            $claims->count();


        $totalAmount =
            $claims->sum(function ($claim) {

                return (float)
                ($claim->total_amount ?? 0);
            });


        /*
        |--------------------------------------------------------------------------
        | Suggested File Name
        |--------------------------------------------------------------------------
        */

        $firstServiceDate =
            $claims
            ->pluck('service_from')
            ->filter()
            ->min();


        $lastServiceDate =
            $claims
            ->pluck('service_to')
            ->filter()
            ->max();


        $suggestedFilename =
            'Resubmission';


        if (
            $firstServiceDate
            &&
            $lastServiceDate
        ) {

            $fromDate =
                Carbon::parse(
                    $firstServiceDate
                )
                ->format('m.d.y');


            $toDate =
                Carbon::parse(
                    $lastServiceDate
                )
                ->format('m.d.y');


            $suggestedFilename =
                'Resubmission_'
                . $fromDate
                . '_to_'
                . $toDate;
        }


        return view(
            'claims.claims_resubmission_preview',
            compact(
                'claims',
                'totalRows',
                'totalAmount',
                'suggestedFilename'
            )
        );
    } // End Method

    public function pendingRebillArchive(Request $request, $id)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'archive_reason' => [
                'required',
                'string',
                'max:255',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Pending Record
        |--------------------------------------------------------------------------
        */

        $pending =
            DB::table(
                'claim_pending_rebill'
            )
            ->where(
                'id',
                (int) $id
            )
            ->first();


        if (!$pending) {

            return back()
                ->with(
                    'message',
                    'Pending rebill record was not found.'
                )
                ->with(
                    'alert-type',
                    'error'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Only Pending Records Can Be Archived
        |--------------------------------------------------------------------------
        */

        if (
            $pending->status !== 'pending'
        ) {

            return back()
                ->with(
                    'message',
                    'Only pending rebill records can be archived.'
                )
                ->with(
                    'alert-type',
                    'warning'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Archive
        |--------------------------------------------------------------------------
        */

        DB::table(
            'claim_pending_rebill'
        )
            ->where(
                'id',
                (int) $id
            )
            ->where(
                'status',
                'pending'
            )
            ->update([

                'status' =>
                'archived',

                'archived_at' =>
                now(),

                'archived_by' =>
                Auth::id(),

                'archive_reason' =>
                trim(
                    (string)
                    $request->archive_reason
                ),

                'updated_at' =>
                now(),

            ]);


        return back()
            ->with(
                'message',
                'Pending rebill #'
                    . $id
                    . ' was archived successfully.'
            )
            ->with(
                'alert-type',
                'success'
            );
    } // End Method

    public function legacy837ImportIndex()
    {
        $rows = DB::table('legacy_837_claim_map')
            ->orderByDesc('id')
            ->get();

        $summary = [
            'total'     => $rows->count(),
            'matched'   => $rows->whereNotNull('ParticipantId')->count(),
            'unmatched' => $rows->whereNull('ParticipantId')->count(),
        ];

        return view('claims.claims_legacy_837_import', compact('rows', 'summary'));
    } // End Method

    public function legacy837ImportProcess(Request $request)
    {
        $request->validate([
            '837_file' => 'required|file|max:10240',
        ]);

        $file = $request->file('837_file');

        $fileName = $file->getClientOriginalName();

        $content = file_get_contents($file->getRealPath());

        if (!$content) {
            return back()->with('error', 'Unable to read the selected 837 file.');
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize X12 file
        |--------------------------------------------------------------------------
        |
        | Claims are separated by ~ in your existing 837 files.
        |
        */

        $content = str_replace(["\r", "\n"], '', $content);

        $segments = explode('~', $content);

        $currentSubscriber = [
            'insurance_member_id' => null,
            'last_name'           => null,
            'first_name'          => null,
            'dob'                 => null,
        ];

        $currentPayer = [
            'payer_id'   => null,
            'payer_name' => null,
        ];

        $currentClaim = null;

        $claims = [];

        foreach ($segments as $segment) {

            $segment = trim($segment);

            if ($segment === '') {
                continue;
            }

            $elements = explode('*', $segment);

            $segmentId = $elements[0] ?? '';

            /*
            |--------------------------------------------------------------------------
            | Subscriber / Member
            |--------------------------------------------------------------------------
            |
            | Example:
            | NM1*IL*1*LEE*INJA*S***MI*15358651
            |
            */

            if (
                $segmentId === 'NM1' &&
                isset($elements[1]) &&
                $elements[1] === 'IL'
            ) {

                $currentSubscriber = [
                    'insurance_member_id' => trim($elements[9] ?? ''),
                    'last_name'           => trim($elements[3] ?? ''),
                    'first_name'          => trim($elements[4] ?? ''),
                    'dob'                 => null,
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Date of Birth
            |--------------------------------------------------------------------------
            |
            | Example:
            | DMG*D8*19461009*F
            |
            */

            if (
                $segmentId === 'DMG' &&
                isset($elements[1]) &&
                $elements[1] === 'D8'
            ) {

                $dobRaw = trim($elements[2] ?? '');

                if (preg_match('/^\d{8}$/', $dobRaw)) {

                    try {
                        $currentSubscriber['dob'] =
                            \Carbon\Carbon::createFromFormat('Ymd', $dobRaw)
                            ->format('Y-m-d');
                    } catch (\Exception $e) {
                        $currentSubscriber['dob'] = null;
                    }
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Payer
            |--------------------------------------------------------------------------
            |
            | Example:
            | NM1*PR*2*WELLCARE HEALTH PLANS OF NJ*****PI*14163
            |
            */

            if (
                $segmentId === 'NM1' &&
                isset($elements[1]) &&
                $elements[1] === 'PR'
            ) {

                $currentPayer = [
                    'payer_name' => trim($elements[3] ?? ''),
                    'payer_id'   => trim($elements[9] ?? ''),
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Claim
            |--------------------------------------------------------------------------
            |
            | Example:
            | CLM*0000104*479.8...
            |
            */

            if ($segmentId === 'CLM') {

                /*
                * Save previous claim before starting the next one.
                */

                if ($currentClaim !== null) {
                    $claims[] = $currentClaim;
                }

                $clm01 = trim($elements[1] ?? '');

                $chargeAmount = (float) ($elements[2] ?? 0);

                $currentClaim = [
                    'batch_file_name'    => $fileName,
                    'clm01'              => $clm01,

                    'ParticipantId'      => null,

                    'insurance_member_id' => $currentSubscriber['insurance_member_id'],
                    'last_name'          => $currentSubscriber['last_name'],
                    'first_name'         => $currentSubscriber['first_name'],
                    'dob'                => $currentSubscriber['dob'],

                    'service_from'       => null,
                    'service_to'         => null,

                    'charge_amount'      => $chargeAmount,

                    'payer_id'           => $currentPayer['payer_id'],
                    'payer_name'         => $currentPayer['payer_name'],
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Service Dates
            |--------------------------------------------------------------------------
            |
            | Single date:
            | DTP*472*D8*20260914
            |
            | Date range:
            | DTP*472*RD8*20260914-20260918
            |
            */

            if (
                $currentClaim !== null &&
                $segmentId === 'DTP' &&
                isset($elements[1]) &&
                $elements[1] === '472'
            ) {

                $dateType  = trim($elements[2] ?? '');
                $dateValue = trim($elements[3] ?? '');

                if ($dateType === 'D8') {

                    if (preg_match('/^\d{8}$/', $dateValue)) {

                        try {

                            $date = \Carbon\Carbon::createFromFormat(
                                'Ymd',
                                $dateValue
                            )->format('Y-m-d');

                            /*
                            * A claim can contain multiple service lines.
                            * Keep earliest From and latest To.
                            */

                            if (
                                !$currentClaim['service_from'] ||
                                $date < $currentClaim['service_from']
                            ) {
                                $currentClaim['service_from'] = $date;
                            }

                            if (
                                !$currentClaim['service_to'] ||
                                $date > $currentClaim['service_to']
                            ) {
                                $currentClaim['service_to'] = $date;
                            }
                        } catch (\Exception $e) {
                            //
                        }
                    }
                }

                if ($dateType === 'RD8') {

                    $parts = explode('-', $dateValue);

                    if (count($parts) === 2) {

                        try {

                            $from = \Carbon\Carbon::createFromFormat(
                                'Ymd',
                                $parts[0]
                            )->format('Y-m-d');

                            $to = \Carbon\Carbon::createFromFormat(
                                'Ymd',
                                $parts[1]
                            )->format('Y-m-d');

                            if (
                                !$currentClaim['service_from'] ||
                                $from < $currentClaim['service_from']
                            ) {
                                $currentClaim['service_from'] = $from;
                            }

                            if (
                                !$currentClaim['service_to'] ||
                                $to > $currentClaim['service_to']
                            ) {
                                $currentClaim['service_to'] = $to;
                            }
                        } catch (\Exception $e) {
                            //
                        }
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Save last claim
        |--------------------------------------------------------------------------
        */

        if ($currentClaim !== null) {
            $claims[] = $currentClaim;
        }

        if (empty($claims)) {

            return back()->with(
                'error',
                'No CLM claims were found in the selected 837 file.'
            );
        }

        $inserted = 0;
        $matched = 0;
        $unmatched = 0;
        $skipped = 0;

        foreach ($claims as $claim) {

            /*
            |--------------------------------------------------------------------------
            | Only process legacy sequential CLM01 values 1 - 169
            |--------------------------------------------------------------------------
            */

            $numericClm = (int) ltrim($claim['clm01'], '0');

            if ($numericClm < 1 || $numericClm > 169) {
                $skipped++;
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Resolve ParticipantId
            |--------------------------------------------------------------------------
            |
            | Matching priority:
            |
            | 1. medicaid_HMO_id = insurance member ID
            | 2. LastName + DOB
            | 3. LastName + FirstName
            |
            | No JOIN.
            |
            */

            $member = null;

            /*
            |--------------------------------------------------------------------------
            | 1. Best Match: Insurance Member ID -> medicaid_HMO_id
            |--------------------------------------------------------------------------
            */

            if (!empty($claim['insurance_member_id'])) {

                $member = DB::table('billing_members')
                    ->whereRaw(
                        'TRIM(medicaid_HMO_id) = ?',
                        [trim($claim['insurance_member_id'])]
                    )
                    ->first();
            }


            /*
            |--------------------------------------------------------------------------
            | 2. Fallback: Last Name + DOB
            |--------------------------------------------------------------------------
            |
            | This handles cases such as:
            |
            | 837 First Name: BOON
            | billing_members: BOON BHAI
            |
            */

            if (!$member && !empty($claim['last_name']) && !empty($claim['dob'])) {

                $member = DB::table('billing_members')
                    ->whereRaw(
                        'UPPER(TRIM(LastName)) = ?',
                        [strtoupper(trim($claim['last_name']))]
                    )
                    ->whereDate(
                        'DateOfBirth',
                        $claim['dob']
                    )
                    ->first();
            }


            /*
            |--------------------------------------------------------------------------
            | 3. Fallback: Exact Last Name + First Name
            |--------------------------------------------------------------------------
            */

            if (
                !$member &&
                !empty($claim['last_name']) &&
                !empty($claim['first_name'])
            ) {

                $member = DB::table('billing_members')
                    ->whereRaw(
                        'UPPER(TRIM(LastName)) = ?',
                        [strtoupper(trim($claim['last_name']))]
                    )
                    ->whereRaw(
                        'UPPER(TRIM(FirstName)) = ?',
                        [strtoupper(trim($claim['first_name']))]
                    )
                    ->first();
            }


            /*
            |--------------------------------------------------------------------------
            | ParticipantId
            |--------------------------------------------------------------------------
            */

            $participantId = $member
                ? trim($member->ParticipantId)
                : null;
            if (!$member) {

                $lastName = strtoupper(
                    trim(
                        (string) ($claim['last_name'] ?? '')
                    )
                );

                $firstName = strtoupper(
                    trim(
                        (string) ($claim['first_name'] ?? '')
                    )
                );

                if ($lastName !== '' && $firstName !== '') {

                    $member = DB::table('billing_members')
                        ->whereRaw(
                            'UPPER(TRIM(LastName)) = ?',
                            [$lastName]
                        )
                        ->whereRaw(
                            'UPPER(TRIM(FirstName)) = ?',
                            [$firstName]
                        )
                        ->first();
                }
            }
            $participantId = $member
                ? trim($member->ParticipantId)
                : null;

            if ($participantId) {
                $matched++;
            } else {
                $unmatched++;
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Import
            |--------------------------------------------------------------------------
            */
            $existing = DB::table('legacy_837_claim_map')
                ->where('batch_file_name', $claim['batch_file_name'])
                ->where('clm01', $claim['clm01'])
                ->first();

            if ($existing) {

                DB::table('legacy_837_claim_map')
                    ->where('id', $existing->id)
                    ->update([
                        'ParticipantId'       => $participantId,
                        'insurance_member_id' => $claim['insurance_member_id'],
                        'last_name'           => $claim['last_name'],
                        'first_name'          => $claim['first_name'],
                        'service_from'        => $claim['service_from'],
                        'service_to'          => $claim['service_to'],
                        'charge_amount'       => $claim['charge_amount'],
                        'payer_id'            => $claim['payer_id'],
                        'payer_name'          => $claim['payer_name'],
                        'updated_at'          => now(),
                    ]);
            } else {

                DB::table('legacy_837_claim_map')
                    ->insert([
                        'batch_file_name'     => $claim['batch_file_name'],
                        'clm01'               => $claim['clm01'],
                        'ParticipantId'       => $participantId,
                        'insurance_member_id' => $claim['insurance_member_id'],
                        'last_name'           => $claim['last_name'],
                        'first_name'          => $claim['first_name'],
                        'service_from'        => $claim['service_from'],
                        'service_to'          => $claim['service_to'],
                        'charge_amount'       => $claim['charge_amount'],
                        'payer_id'            => $claim['payer_id'],
                        'payer_name'          => $claim['payer_name'],
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);
            }

            $inserted++;
        }

        return redirect()
            ->route('claims.legacy837.import.index')
            ->with(
                'success',
                '837 file processed successfully. '
                    . 'Claims processed: ' . $inserted
                    . ', Matched: ' . $matched
                    . ', Unmatched: ' . $unmatched
                    . ', Skipped outside legacy range: ' . $skipped
            );
    } // End Method

    public function resubmitBatchIndex()
    {
        $batches = DB::table('claim_batches')
            ->orderByDesc('id')
            ->get();

        return view(
            'claims.claims_resubmit_batches_index',
            compact('batches')
        );
    } // End Method

    public function resubmitBatchShow($batchId)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Get Batch Header
        |--------------------------------------------------------------------------
        */

        $batch = DB::table('claim_batches')
            ->where('id', $batchId)
            ->first();

        if (!$batch) {
            return redirect()
                ->route('claims.resubmit.batches.index')
                ->with([
                    'message' => 'Claim batch not found.',
                    'alert-type' => 'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 2. Get Claim IDs Originally Submitted In This Batch
        |--------------------------------------------------------------------------
        */

        $claimIds = DB::table('claim_batch_items')
            ->where('claim_batch_id', $batchId)
            ->pluck('claim_id')
            ->filter()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | 3. Load Claims
        |--------------------------------------------------------------------------
        */

        $claims = collect();

        if ($claimIds->isNotEmpty()) {

            $claims = DB::table('claims')
                ->whereIn('id', $claimIds->all())
                ->orderBy('ParticipantId')
                ->orderBy('service_from')
                ->orderBy('id')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | 4. Get Member Names Separately
        |--------------------------------------------------------------------------
        |
        | We are intentionally not using JOINs.
        |
        */

        $participantIds = $claims
            ->pluck('ParticipantId')
            ->filter()
            ->map(function ($id) {
                return trim((string) $id);
            })
            ->unique()
            ->values()
            ->all();


        $membersById = collect();

        if (!empty($participantIds)) {

            $members = DB::table('billing_members')
                ->select(
                    'ParticipantId',
                    'LastName',
                    'FirstName'
                )
                ->whereIn(
                    DB::raw('TRIM(ParticipantId)'),
                    $participantIds
                )
                ->get();

            $membersById = $members->keyBy(function ($member) {
                return trim((string) $member->ParticipantId);
            });
        }


        /*
        |--------------------------------------------------------------------------
        | 5. Attach Member Names To Claims
        |--------------------------------------------------------------------------
        */

        $claims = $claims->map(function ($claim) use ($membersById) {

            $participantId = trim(
                (string) ($claim->ParticipantId ?? '')
            );

            $member = $membersById[$participantId] ?? null;

            $claim->member_lastname =
                $member->LastName ?? '';

            $claim->member_firstname =
                $member->FirstName ?? '';

            return $claim;
        });


        /*
        |--------------------------------------------------------------------------
        | 6. Return Batch Detail Screen
        |--------------------------------------------------------------------------
        */

        return view(
            'claims.claims_resubmit_batches_show',
            compact(
                'batch',
                'claims'
            )
        );
    } // End Method

    public function resubmitBatchSelected(Request $request, $batchId)
    {

        /*
        |--------------------------------------------------------------------------
        | Get Batch
        |--------------------------------------------------------------------------
        */

        $batch = DB::table('claim_batches')
            ->where('id', $batchId)
            ->first();


        if (!$batch) {

            return redirect()
                ->route('claims.resubmit.batches.index')
                ->with([
                    'message' =>
                    'Claim batch not found.',

                    'alert-type' =>
                    'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Selected Claim IDs
        |--------------------------------------------------------------------------
        */

        $selectedClaimIds = collect(
            $request->input(
                'selected_claims',
                []
            )
        )
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();


        if (empty($selectedClaimIds)) {

            return redirect()
                ->route(
                    'claims.resubmit.batches.show',
                    $batchId
                )
                ->with([
                    'message' =>
                    'Please select at least one claim.',

                    'alert-type' =>
                    'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Confirm Claims Belong To This Original Batch
        |--------------------------------------------------------------------------
        */

        $batchClaimIds = DB::table(
            'claim_batch_items'
        )
            ->where(
                'claim_batch_id',
                $batchId
            )
            ->whereIn(
                'claim_id',
                $selectedClaimIds
            )
            ->pluck('claim_id')
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();


        if (
            count($batchClaimIds)
            !==
            count($selectedClaimIds)
        ) {

            return redirect()
                ->route(
                    'claims.resubmit.batches.show',
                    $batchId
                )
                ->with([
                    'message' =>
                    'One or more selected claims do not belong to this batch.',

                    'alert-type' =>
                    'error',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Prepare Claims
        |--------------------------------------------------------------------------
        */

        try {

            $count =
                $this->prepareClaimsForResubmission(

                    $batchClaimIds,

                    (int) $batchId,

                    'selected',

                    'Selected claim resubmission'
                );


            return redirect()
                ->route(
                    'claims.resubmit.batches.show',
                    $batchId
                )
                ->with([
                    'message' =>
                    $count .
                        ' claim(s) assigned new claim numbers and prepared for resubmission.',

                    'alert-type' =>
                    'success',
                ]);
        } catch (\Throwable $e) {

            return redirect()
                ->route(
                    'claims.resubmit.batches.show',
                    $batchId
                )
                ->with([
                    'message' =>
                    'Resubmission failed: ' .
                        $e->getMessage(),

                    'alert-type' =>
                    'error',
                ]);
        }
    } // End Method

    public function resubmitBatchAll($batchId)
    {
        /*
        |--------------------------------------------------------------------------
        | Get Original Batch
        |--------------------------------------------------------------------------
        */

        $batch = DB::table('claim_batches')
            ->where('id', $batchId)
            ->first();


        if (!$batch) {

            return redirect()
                ->route('claims.resubmit.batches.index')
                ->with([
                    'message' =>
                    'Claim batch not found.',

                    'alert-type' =>
                    'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Get Every Claim Originally Submitted In This Batch
        |--------------------------------------------------------------------------
        */

        $claimIds = DB::table(
            'claim_batch_items'
        )
            ->where(
                'claim_batch_id',
                $batchId
            )
            ->pluck('claim_id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();


        if (empty($claimIds)) {

            return redirect()
                ->route(
                    'claims.resubmit.batches.show',
                    $batchId
                )
                ->with([
                    'message' =>
                    'No claims were found in this batch.',

                    'alert-type' =>
                    'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Prepare Complete Batch
        |--------------------------------------------------------------------------
        */

        try {

            $count =
                $this->prepareClaimsForResubmission(

                    $claimIds,

                    (int) $batchId,

                    'full_batch',

                    'Complete batch resubmission'
                );


            return redirect()
                ->route(
                    'claims.resubmit.batches.show',
                    $batchId
                )
                ->with([
                    'message' =>
                    $count .
                        ' claim(s) from Batch ' .
                        $batchId .
                        ' assigned new claim numbers and prepared for resubmission.',

                    'alert-type' =>
                    'success',
                ]);
        } catch (\Throwable $e) {

            return redirect()
                ->route(
                    'claims.resubmit.batches.show',
                    $batchId
                )
                ->with([
                    'message' =>
                    'Full batch resubmission failed: ' .
                        $e->getMessage(),

                    'alert-type' =>
                    'error',
                ]);
        }
    } // End Method

    private function prepareClaimsForResubmission(
        array $claimIds,
        int $oldBatchId,
        string $resubmitType,
        string $reason
    ): int {

        /*
        |--------------------------------------------------------------------------
        | Basic Validation
        |--------------------------------------------------------------------------
        */

        $claimIds = collect($claimIds)
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();


        if (empty($claimIds)) {
            throw new \RuntimeException(
                'No claims were selected for resubmission.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $userCd = Auth::check()
            ? Auth::user()->user_cd
            : null;


        /*
        |--------------------------------------------------------------------------
        | Begin Transaction
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Reload And Lock Claims
            |--------------------------------------------------------------------------
            */

            $claims = DB::table('claims')
                ->whereIn('id', $claimIds)
                ->orderBy('service_from')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();


            /*
            |--------------------------------------------------------------------------
            | Make Sure Every Requested Claim Was Found
            |--------------------------------------------------------------------------
            */

            if ($claims->count() !== count($claimIds)) {

                throw new \RuntimeException(
                    'One or more claims could not be found.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Verify Claim Eligibility
            |--------------------------------------------------------------------------
            */

            foreach ($claims as $claim) {

                $status = strtolower(
                    trim((string) ($claim->status ?? ''))
                );

                $precheck = strtolower(
                    trim((string) ($claim->precheck_result ?? ''))
                );


                /*
                * SENT:
                * Clearinghouse rejected the batch after ADC already sent it.
                *
                * REJECTED:
                * Individual claim was rejected and needs another submission.
                */

                if (!in_array(
                    $status,
                    ['sent', 'rejected'],
                    true
                )) {

                    throw new \RuntimeException(
                        'Claim ' .
                            ($claim->claim_no ?? $claim->id) .
                            ' cannot be resubmitted because its status is "' .
                            ($claim->status ?? 'blank') .
                            '".'
                    );
                }


                if ($precheck !== 'valid') {

                    throw new \RuntimeException(
                        'Claim ' .
                            ($claim->claim_no ?? $claim->id) .
                            ' cannot be resubmitted because precheck is not valid.'
                    );
                }


                if (empty($claim->claim_no)) {

                    throw new \RuntimeException(
                        'Claim ID ' .
                            $claim->id .
                            ' does not have an existing claim number.'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Pending Resubmission
            |--------------------------------------------------------------------------
            |
            | new_batch_id remains NULL until the new 837P batch is generated.
            |
            | Therefore:
            |
            | new_batch_id = NULL
            |
            | means this claim has already been prepared and is still waiting
            | for its new 837P submission.
            |--------------------------------------------------------------------------
            */

            $pendingClaimIds = DB::table(
                'claim_resubmissions'
            )
                ->whereIn(
                    'claim_id',
                    $claimIds
                )
                ->whereNull('new_batch_id')
                ->pluck('claim_id')
                ->map(fn($id) => (int) $id)
                ->all();


            if (!empty($pendingClaimIds)) {

                throw new \RuntimeException(
                    'One or more claims are already waiting for resubmission.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Reserve Permanent Claim Number Range
            |--------------------------------------------------------------------------
            */

            $nextNumber = $this->reserveClaimNumberRange(
                $claims->count()
            );


            /*
            |--------------------------------------------------------------------------
            | Assign New Claim Numbers
            |--------------------------------------------------------------------------
            */

            foreach ($claims as $claim) {

                $oldClaimNo = $claim->claim_no;


                $newClaimNo = str_pad(
                    $nextNumber,
                    6,
                    '0',
                    STR_PAD_LEFT
                );


                $nextNumber++;


                /*
                |--------------------------------------------------------------------------
                | Preserve The FIRST Original Claim Number
                |--------------------------------------------------------------------------
                */

                $originalClaimNo =
                    $claim->original_claim_no
                    ?: $oldClaimNo;


                /*
                |--------------------------------------------------------------------------
                | Create Resubmission History
                |--------------------------------------------------------------------------
                */

                DB::table('claim_resubmissions')
                    ->insert([

                        'claim_id' =>
                        $claim->id,

                        'old_claim_no' =>
                        $oldClaimNo,

                        'new_claim_no' =>
                        $newClaimNo,

                        'old_batch_id' =>
                        $oldBatchId,

                        'new_batch_id' =>
                        null,

                        'old_batch_no' =>
                        null,

                        'new_batch_no' =>
                        null,

                        'resubmit_type' =>
                        $resubmitType,

                        'reason' =>
                        $reason,

                        'created_by' =>
                        $userCd,

                        'created_at' =>
                        now(),

                        'updated_at' =>
                        now(),

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Update Current Claim
                |--------------------------------------------------------------------------
                */

                DB::table('claims')
                    ->where('id', $claim->id)
                    ->update([

                        'claim_no' =>
                        $newClaimNo,

                        'original_claim_no' =>
                        $originalClaimNo,

                        'status' =>
                        'ready',

                        'updated_at' =>
                        now(),

                    ]);
            }


            DB::commit();


            return $claims->count();
        } catch (\Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    } // End Method

    public function resubmissionReady()
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Get Pending Resubmission Records
        |--------------------------------------------------------------------------
        |
        | Only records that have not yet been assigned to a new batch.
        |
        */

        $pending = DB::table('claim_resubmissions')
            ->whereNull('new_batch_id')
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 2. Get Claim IDs
        |--------------------------------------------------------------------------
        */

        $claimIds = $pending
            ->pluck('claim_id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();


        /*
        |--------------------------------------------------------------------------
        | 3. Load Current Claims
        |--------------------------------------------------------------------------
        |
        | Only claims that are READY and VALID should appear.
        |
        */

        $claims = collect();

        if (!empty($claimIds)) {

            $claims = DB::table('claims')
                ->whereIn('id', $claimIds)
                ->where('status', 'ready')
                ->where('precheck_result', 'valid')
                ->orderBy('ParticipantId')
                ->orderBy('service_from')
                ->orderBy('id')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | 4. Build Resubmission History Lookup
        |--------------------------------------------------------------------------
        */

        $pendingByClaimId = $pending->keyBy(
            fn($row) => (int) $row->claim_id
        );


        /*
        |--------------------------------------------------------------------------
        | 5. Attach Old Claim / Batch Information
        |--------------------------------------------------------------------------
        */

        $claims = $claims->map(
            function ($claim) use ($pendingByClaimId) {

                $history =
                    $pendingByClaimId[(int) $claim->id]
                    ?? null;

                $claim->old_claim_no =
                    $history->old_claim_no ?? null;

                $claim->new_claim_no =
                    $history->new_claim_no ?? $claim->claim_no;

                $claim->old_batch_id =
                    $history->old_batch_id ?? null;

                $claim->resubmit_type =
                    $history->resubmit_type ?? null;

                $claim->resubmission_id =
                    $history->id ?? null;

                return $claim;
            }
        );


        /*
        |--------------------------------------------------------------------------
        | 6. Load Member Names Separately
        |--------------------------------------------------------------------------
        */

        $participantIds = $claims
            ->pluck('ParticipantId')
            ->filter()
            ->map(fn($id) => trim((string) $id))
            ->unique()
            ->values()
            ->all();


        $membersById = collect();

        if (!empty($participantIds)) {

            $members = DB::table('billing_members')
                ->select(
                    'ParticipantId',
                    'LastName',
                    'FirstName'
                )
                ->whereIn(
                    DB::raw('TRIM(ParticipantId)'),
                    $participantIds
                )
                ->get();


            $membersById = $members->keyBy(
                fn($member) =>
                trim((string) $member->ParticipantId)
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 7. Attach Member Names
        |--------------------------------------------------------------------------
        */

        $claims = $claims->map(
            function ($claim) use ($membersById) {

                $participantId =
                    trim((string) ($claim->ParticipantId ?? ''));

                $member =
                    $membersById[$participantId]
                    ?? null;

                $claim->member_lastname =
                    $member->LastName ?? '';

                $claim->member_firstname =
                    $member->FirstName ?? '';

                return $claim;
            }
        );


        /*
        |--------------------------------------------------------------------------
        | 8. Return Preview Screen
        |--------------------------------------------------------------------------
        */

        return view(
            'claims.claims_resubmission_ready',
            compact('claims')
        );
    } // End Method

    public function generateResubmission837P(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Get selected claim row IDs
        |--------------------------------------------------------------------------
        */

        $selectedIds = collect(
            $request->input('selected_claims', [])
        )
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();


        if (empty($selectedIds)) {

            return redirect()
                ->route('claims.resubmission.ready')
                ->with([
                    'message' =>
                    'Please select at least one claim for resubmission.',

                    'alert-type' =>
                    'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 2. Validate requested file name
        |--------------------------------------------------------------------------
        */

        $requestedFilename = trim(
            (string) $request->input('edi_filename', '')
        );


        if ($requestedFilename === '') {

            return redirect()
                ->route('claims.resubmission.ready')
                ->withInput()
                ->with([
                    'message' =>
                    'Please enter a file name for the resubmission 837P batch.',

                    'alert-type' =>
                    'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 3. Remove .txt
        |--------------------------------------------------------------------------
        */

        if (
            str_ends_with(
                strtolower($requestedFilename),
                '.txt'
            )
        ) {

            $requestedFilename =
                substr(
                    $requestedFilename,
                    0,
                    -4
                );
        }


        /*
        |--------------------------------------------------------------------------
        | 4. Clean Windows filename
        |--------------------------------------------------------------------------
        */

        $requestedFilename = preg_replace(
            '/[\\\\\/:*?"<>|]+/',
            '_',
            $requestedFilename
        );


        $requestedFilename = trim(
            $requestedFilename,
            " .\t\n\r\0\x0B"
        );


        if ($requestedFilename === '') {

            return redirect()
                ->route('claims.resubmission.ready')
                ->withInput()
                ->with([
                    'message' =>
                    'The entered 837P file name is not valid.',

                    'alert-type' =>
                    'warning',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 5. File names
        |--------------------------------------------------------------------------
        */

        $ediFilename =
            $requestedFilename . '.txt';

        $pdfFilename =
            $requestedFilename . '.pdf';

        $ediDir = 'claims';


        /*
        |--------------------------------------------------------------------------
        | 6. Prevent accidental overwrite
        |--------------------------------------------------------------------------
        */

        if (
            Storage::disk('adc_private')
            ->exists(
                $ediDir . '/' . $ediFilename
            )
            ||
            Storage::disk('adc_private')
            ->exists(
                $ediDir . '/' . $pdfFilename
            )
        ) {

            return redirect()
                ->route('claims.resubmission.ready')
                ->withInput()
                ->with([
                    'message' =>
                    'A batch with this file name already exists. '
                        . 'Please enter a different file name.',

                    'alert-type' =>
                    'warning',
                ]);
        }


        DB::beginTransaction();


        try {

            /*
            |--------------------------------------------------------------------------
            | 7. Load selected READY rows
            |--------------------------------------------------------------------------
            |
            | These IDs are used only to determine which internal claim numbers
            | the user selected.
            |
            */

            $selectedRows = DB::table('claims')
                ->whereIn(
                    'id',
                    $selectedIds
                )
                ->where(
                    'status',
                    'ready'
                )
                ->where(
                    'precheck_result',
                    'valid'
                )
                ->lockForUpdate()
                ->get();


            if (
                $selectedRows->count()
                !==
                count($selectedIds)
            ) {

                throw new \RuntimeException(
                    'One or more selected claims are no longer READY and VALID for resubmission.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 8. Determine INTERNAL claim numbers selected
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | Original payer claim:
            |     000008
            |
            | Internal corrected copy:
            |     000009
            |
            */

            $selectedClaimNos = $selectedRows
                ->pluck('claim_no')
                ->filter()
                ->map(
                    fn($claimNo) =>
                    trim((string) $claimNo)
                )
                ->unique()
                ->values()
                ->all();


            if (empty($selectedClaimNos)) {

                throw new \RuntimeException(
                    'Unable to determine selected resubmission claim numbers.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 9. Get pending resubmission histories by NEW INTERNAL claim number
            |--------------------------------------------------------------------------
            */

            $pendingResubmissions =
                DB::table('claim_resubmissions')
                ->whereIn(
                    'new_claim_no',
                    $selectedClaimNos
                )
                ->whereNull(
                    'new_batch_id'
                )
                ->lockForUpdate()
                ->get();


            /*
            |--------------------------------------------------------------------------
            | Build lookup:
            |
            | new internal claim number => history record
            |--------------------------------------------------------------------------
            */

            $pendingByClaimNo =
                $pendingResubmissions
                ->keyBy(
                    fn($row) =>
                    trim(
                        (string) $row->new_claim_no
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | 10. Every selected claim must have pending history
            |--------------------------------------------------------------------------
            */

            foreach ($selectedClaimNos as $selectedClaimNo) {

                if (
                    !$pendingByClaimNo->has(
                        $selectedClaimNo
                    )
                ) {

                    throw new \RuntimeException(
                        'Claim '
                            . $selectedClaimNo
                            . ' is not currently pending resubmission.'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | 11. Load ALL service rows for selected claim numbers
            |--------------------------------------------------------------------------
            |
            | Very important for corrected claims.
            |
            | Example:
            |
            | Internal claim 000009
            |
            |     10/05 - 10/06
            |     10/08 - 10/09
            |
            | Selecting 000009 must send BOTH rows.
            |
            */

            $claims = DB::table('claims')
                ->whereIn(
                    'claim_no',
                    $selectedClaimNos
                )
                ->where(
                    'status',
                    'ready'
                )
                ->where(
                    'precheck_result',
                    'valid'
                )
                ->orderBy(
                    'ParticipantId'
                )
                ->orderBy(
                    'claim_no'
                )
                ->orderBy(
                    'service_from'
                )
                ->orderBy(
                    'line_no'
                )
                ->orderBy(
                    'id'
                )
                ->lockForUpdate()
                ->get();


            if ($claims->isEmpty()) {

                throw new \RuntimeException(
                    'No READY claim rows were found for the selected resubmission.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 12. Validate current internal claim numbers
            |--------------------------------------------------------------------------
            */

            foreach ($claims as $claim) {

                $currentClaimNo = trim(
                    (string) (
                        $claim->claim_no ?? ''
                    )
                );


                $history =
                    $pendingByClaimNo[$currentClaimNo] ?? null;


                if (!$history) {

                    throw new \RuntimeException(
                        'Resubmission history was not found for claim '
                            . $currentClaimNo
                            . '.'
                    );
                }


                $pendingNewClaimNo = trim(
                    (string) (
                        $history->new_claim_no
                        ?? ''
                    )
                );


                if (
                    $currentClaimNo === ''
                    ||
                    $pendingNewClaimNo === ''
                    ||
                    $currentClaimNo !== $pendingNewClaimNo
                ) {

                    throw new \RuntimeException(
                        'Internal claim number mismatch for claim '
                            . $currentClaimNo
                            . '.'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | 13. Member Names
            |--------------------------------------------------------------------------
            */

            $participantIds = $claims
                ->pluck('ParticipantId')
                ->filter()
                ->map(
                    fn($id) =>
                    trim((string) $id)
                )
                ->unique()
                ->values()
                ->all();


            $membersById = collect();


            if (!empty($participantIds)) {

                $members = DB::table(
                    'billing_members'
                )
                    ->select(
                        'ParticipantId',
                        'LastName',
                        'FirstName'
                    )
                    ->whereIn(
                        DB::raw(
                            'TRIM(ParticipantId)'
                        ),
                        $participantIds
                    )
                    ->get();


                $membersById =
                    $members->keyBy(
                        fn($member) =>
                        trim(
                            (string)
                            $member->ParticipantId
                        )
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | 14. Prepare claims for EDI
            |--------------------------------------------------------------------------
            |
            | We add TEMPORARY properties to the query objects.
            |
            | Nothing is written to these fields in the claims table.
            |
            */

            $claimsForEdi = $claims->map(
                function ($claim) use (
                    $membersById,
                    $pendingByClaimNo
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Member
                    |--------------------------------------------------------------------------
                    */

                    $participantId = trim(
                        (string) (
                            $claim->ParticipantId ?? ''
                        )
                    );


                    $member =
                        $membersById[$participantId] ?? null;


                    $claim->member_lastname =
                        $member->LastName ?? '';


                    $claim->member_firstname =
                        $member->FirstName ?? '';


                    /*
                    |--------------------------------------------------------------------------
                    | Resubmission History
                    |--------------------------------------------------------------------------
                    */

                    $internalClaimNo = trim(
                        (string) (
                            $claim->claim_no ?? ''
                        )
                    );


                    $history =
                        $pendingByClaimNo[$internalClaimNo] ?? null;


                    if (!$history) {

                        throw new \RuntimeException(
                            'Pending resubmission history was not found for claim '
                                . $internalClaimNo
                                . '.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Type
                    |--------------------------------------------------------------------------
                    */

                    $resubmitType = strtolower(
                        trim(
                            (string) (
                                $history->resubmit_type
                                ?? ''
                            )
                        )
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | NORMAL existing resubmission
                    |--------------------------------------------------------------------------
                    |
                    | Existing behavior stays frequency 1.
                    |
                    */

                    $ediClaimNo =
                        $internalClaimNo;

                    $frequencyCode =
                        '1';

                    $payerIcn =
                        null;


                    /*
                    |--------------------------------------------------------------------------
                    | CORRECTED / REPLACEMENT
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $resubmitType ===
                        'corrected'
                    ) {

                        $ediClaimNo = trim(
                            (string) (
                                $history->old_claim_no
                                ?? ''
                            )
                        );


                        $frequencyCode =
                            '7';


                        $payerIcn = trim(
                            (string) (
                                $claim->payer_claim_number
                                ?? ''
                            )
                        );


                        if ($ediClaimNo === '') {

                            throw new \RuntimeException(
                                'Original claim number is missing for corrected claim '
                                    . $internalClaimNo
                                    . '.'
                            );
                        }


                        if ($payerIcn === '') {

                            throw new \RuntimeException(
                                'Payer ICN is missing for corrected claim '
                                    . $internalClaimNo
                                    . '.'
                            );
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | VOID
                    |--------------------------------------------------------------------------
                    */ elseif (
                        $resubmitType ===
                        'void'
                    ) {

                        $ediClaimNo = trim(
                            (string) (
                                $history->old_claim_no
                                ?? ''
                            )
                        );


                        $frequencyCode =
                            '8';


                        $payerIcn = trim(
                            (string) (
                                $claim->payer_claim_number
                                ?? ''
                            )
                        );


                        if ($ediClaimNo === '') {

                            throw new \RuntimeException(
                                'Original claim number is missing for void claim '
                                    . $internalClaimNo
                                    . '.'
                            );
                        }


                        if ($payerIcn === '') {

                            throw new \RuntimeException(
                                'Payer ICN is missing for void claim '
                                    . $internalClaimNo
                                    . '.'
                            );
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Temporary EDI properties
                    |--------------------------------------------------------------------------
                    |
                    | build837P_new() will use these.
                    |
                    */

                    $claim->internal_claim_no =
                        $internalClaimNo;


                    $claim->edi_claim_no =
                        $ediClaimNo;


                    $claim->resubmit_type =
                        $resubmitType;


                    $claim->frequency_code =
                        $frequencyCode;


                    $claim->payer_icn =
                        $payerIcn;


                    return $claim;
                }
            );


            /*
            |--------------------------------------------------------------------------
            | 15. Build 837P
            |--------------------------------------------------------------------------
            |
            | NEXT STEP:
            |
            | build837P_new() must be modified to read:
            |
            | $claim->edi_claim_no
            | $claim->frequency_code
            | $claim->payer_icn
            |
            */

            $ediContent =
                $this->build837P_new(
                    $claimsForEdi
                );


            /*
            |--------------------------------------------------------------------------
            | 16. Batch Totals
            |--------------------------------------------------------------------------
            */

            $totalClaims =
                $claimsForEdi->count();


            $totalAmount =
                $claimsForEdi->sum(
                    'total_amount'
                );


            $batchTimestampCarbon =
                now();


            $batchTimestamp =
                $batchTimestampCarbon
                ->format(
                    'Ymd_His'
                );


            /*
            |--------------------------------------------------------------------------
            | 17. Storage Directory
            |--------------------------------------------------------------------------
            */

            Storage::disk(
                'adc_private'
            )->makeDirectory(
                $ediDir
            );


            /*
            |--------------------------------------------------------------------------
            | 18. Save EDI
            |--------------------------------------------------------------------------
            */

            Storage::disk(
                'adc_private'
            )->put(
                $ediDir . '/' . $ediFilename,
                $ediContent
            );


            /*
            |--------------------------------------------------------------------------
            | 19. PDF
            |--------------------------------------------------------------------------
            */

            $pdf = Pdf::loadView(
                'claims.claims_send_batch_pdf',
                [
                    'claims' =>
                    $claimsForEdi,

                    'batchTimestamp' =>
                    $batchTimestamp,

                    'ediFilename' =>
                    $ediFilename,
                ]
            )->setPaper(
                'letter',
                'portrait'
            );


            Storage::disk(
                'adc_private'
            )->put(
                $ediDir . '/' . $pdfFilename,
                $pdf->output()
            );


            /*
            |--------------------------------------------------------------------------
            | 20. Current User
            |--------------------------------------------------------------------------
            */

            $userCd =
                Auth::check()
                ? Auth::user()->user_cd
                : null;


            /*
            |--------------------------------------------------------------------------
            | 21. Create Batch - QUERY BUILDER ONLY
            |--------------------------------------------------------------------------
            */

            $batchId = DB::table(
                'claim_batches'
            )->insertGetId([
                'batch_timestamp' =>
                $batchTimestampCarbon,

                'edi_filename' =>
                $ediFilename,

                'pdf_filename' =>
                $pdfFilename,

                'total_claims' =>
                $totalClaims,

                'total_amount' =>
                $totalAmount,

                'created_by' =>
                $userCd,

                'created_at' =>
                now(),

                'updated_at' =>
                now(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | 22. Link EVERY service row to batch
            |--------------------------------------------------------------------------
            */

            foreach ($claimsForEdi as $claim) {

                DB::table(
                    'claim_batch_items'
                )->insert([
                    'claim_batch_id' =>
                    $batchId,

                    'claim_id' =>
                    $claim->id,

                    'created_at' =>
                    now(),

                    'updated_at' =>
                    now(),
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | 23. Update each resubmission history ONCE
            |--------------------------------------------------------------------------
            */

            foreach ($pendingResubmissions as $history) {

                $updated = DB::table(
                    'claim_resubmissions'
                )
                    ->where(
                        'id',
                        $history->id
                    )
                    ->whereNull(
                        'new_batch_id'
                    )
                    ->update([
                        'new_batch_id' =>
                        $batchId,

                        'updated_at' =>
                        now(),
                    ]);


                if ($updated !== 1) {

                    throw new \RuntimeException(
                        'Unable to update resubmission history for claim '
                            . $history->new_claim_no
                            . '.'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | 24. Mark ALL generated rows SENT
            |--------------------------------------------------------------------------
            */

            $generatedClaimIds =
                $claimsForEdi
                ->pluck('id')
                ->map(
                    fn($id) => (int) $id
                )
                ->values()
                ->all();


            DB::table('claims')
                ->whereIn(
                    'id',
                    $generatedClaimIds
                )
                ->update([
                    'status' =>
                    'sent',

                    'updated_at' =>
                    now(),
                ]);


            /*
            |--------------------------------------------------------------------------
            | 25. Commit
            |--------------------------------------------------------------------------
            */

            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | 26. Success
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'claims.batches.show',
                    [
                        'batchId' =>
                        $batchId,
                    ]
                )
                ->with([
                    'message' =>
                    'Resubmission 837P + PDF generated successfully. '
                        . $totalClaims
                        . ' claim row(s) marked as SENT.',

                    'alert-type' =>
                    'success',

                    'edi_file' =>
                    $ediFilename,

                    'pdf_file' =>
                    $pdfFilename,
                ]);
        } catch (\Throwable $e) {

            DB::rollBack();


            /*
            |--------------------------------------------------------------------------
            | Delete EDI if transaction failed
            |--------------------------------------------------------------------------
            */

            if (
                isset($ediFilename)
                &&
                Storage::disk('adc_private')
                ->exists(
                    $ediDir . '/' . $ediFilename
                )
            ) {

                Storage::disk(
                    'adc_private'
                )->delete(
                    $ediDir . '/' . $ediFilename
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Delete PDF if transaction failed
            |--------------------------------------------------------------------------
            */

            if (
                isset($pdfFilename)
                &&
                Storage::disk('adc_private')
                ->exists(
                    $ediDir . '/' . $pdfFilename
                )
            ) {

                Storage::disk(
                    'adc_private'
                )->delete(
                    $ediDir . '/' . $pdfFilename
                );
            }


            return redirect()
                ->route(
                    'claims.resubmission.ready'
                )
                ->withInput()
                ->with([
                    'message' =>
                    'Error while generating resubmission 837P/PDF: '
                        . $e->getMessage(),

                    'alert-type' =>
                    'error',
                ]);
        }
    } // End Method

    public function resubmissionHistory()
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Load Resubmission History
        |--------------------------------------------------------------------------
        */

        $history = DB::table('claim_resubmissions')
            ->orderByDesc('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 2. Get Claim IDs
        |--------------------------------------------------------------------------
        */

        $claimIds = $history
            ->pluck('claim_id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();


        /*
        |--------------------------------------------------------------------------
        | 3. Load Claims Separately
        |--------------------------------------------------------------------------
        |
        | No JOINs.
        |--------------------------------------------------------------------------
        */

        $claimsById = collect();

        if (!empty($claimIds)) {

            $claims = DB::table('claims')
                ->whereIn('id', $claimIds)
                ->get();

            $claimsById = $claims->keyBy(
                fn($claim) => (int) $claim->id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 4. Load Member Names
        |--------------------------------------------------------------------------
        */

        $participantIds = $claimsById
            ->pluck('ParticipantId')
            ->filter()
            ->map(fn($id) => trim((string) $id))
            ->unique()
            ->values()
            ->all();


        $membersById = collect();

        if (!empty($participantIds)) {

            $members = DB::table('billing_members')
                ->select(
                    'ParticipantId',
                    'LastName',
                    'FirstName'
                )
                ->whereIn(
                    DB::raw('TRIM(ParticipantId)'),
                    $participantIds
                )
                ->get();

            $membersById = $members->keyBy(
                fn($member) =>
                trim((string) $member->ParticipantId)
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 5. Load Batch Information
        |--------------------------------------------------------------------------
        */

        $batchIds = $history
            ->pluck('old_batch_id')
            ->merge(
                $history->pluck('new_batch_id')
            )
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();


        $batchesById = collect();

        if (!empty($batchIds)) {

            $batches = DB::table('claim_batches')
                ->whereIn('id', $batchIds)
                ->get();

            $batchesById = $batches->keyBy(
                fn($batch) => (int) $batch->id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 6. Attach Display Information
        |--------------------------------------------------------------------------
        */

        $history = $history->map(
            function ($row) use (
                $claimsById,
                $membersById,
                $batchesById
            ) {

                $claim =
                    $claimsById[(int) $row->claim_id]
                    ?? null;


                $participantId = trim(
                    (string) (
                        $claim->ParticipantId ?? ''
                    )
                );


                $member =
                    $participantId !== ''
                    ? ($membersById[$participantId] ?? null)
                    : null;


                $row->ParticipantId =
                    $claim->ParticipantId ?? null;

                $row->service_from =
                    $claim->service_from ?? null;

                $row->service_to =
                    $claim->service_to ?? null;

                $row->current_status =
                    $claim->status ?? null;

                $row->current_claim_no =
                    $claim->claim_no ?? null;


                $row->member_lastname =
                    $member->LastName ?? '';

                $row->member_firstname =
                    $member->FirstName ?? '';


                $oldBatch =
                    !empty($row->old_batch_id)
                    ? ($batchesById[(int) $row->old_batch_id] ?? null)
                    : null;


                $newBatch =
                    !empty($row->new_batch_id)
                    ? ($batchesById[(int) $row->new_batch_id] ?? null)
                    : null;


                $row->old_batch_filename =
                    $oldBatch->edi_filename ?? null;

                $row->new_batch_filename =
                    $newBatch->edi_filename ?? null;


                return $row;
            }
        );


        /*
        |--------------------------------------------------------------------------
        | 7. Return Screen
        |--------------------------------------------------------------------------
        */

        return view(
            'claims.claims_resubmission_history',
            compact('history')
        );
    } // End Method

    public function claimCorrectionIndex()
    {
        /*
        |--------------------------------------------------------------------------
        | Previously submitted claims
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | We select only status = sent.
        |
        | A corrected/void copy may have the SAME claim_no but status = ready.
        | Therefore it will not appear as another original claim here.
        |
        */

        $claims = DB::table('claims')
            ->select(
                'claim_no',
                'ParticipantId',
                'payer_fullname',
                'payer_id',
                'payer_claim_number',
                DB::raw('MIN(service_from) as service_from'),
                DB::raw('MAX(service_to) as service_to'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('COUNT(*) as line_count')
            )
            ->whereNotNull('claim_no')
            ->where('claim_no', '<>', '')
            ->where('status', 'sent')
            ->groupBy(
                'claim_no',
                'ParticipantId',
                'payer_fullname',
                'payer_id',
                'payer_claim_number'
            )
            ->orderByDesc(DB::raw('MAX(service_to)'))
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Member names
        |--------------------------------------------------------------------------
        |
        | Separate query - no JOIN because of our collation issue.
        |
        */

        $participantIds = $claims
            ->pluck('ParticipantId')
            ->filter()
            ->unique()
            ->values();


        $members = DB::table('billing_members')
            ->whereIn('ParticipantId', $participantIds)
            ->select(
                'ParticipantId',
                'FirstName',
                'LastName'
            )
            ->get()
            ->keyBy('ParticipantId');


        /*
        |--------------------------------------------------------------------------
        | Already waiting for corrected/void transmission
        |--------------------------------------------------------------------------
        */

        $pending = DB::table('claim_resubmissions')
            ->whereNull('new_batch_id')
            ->pluck('old_claim_no')
            ->filter()
            ->unique()
            ->toArray();

        $voided = DB::table('claim_resubmissions')
            ->where('resubmit_type', 'void')
            ->whereNotNull('new_batch_id')
            ->pluck('old_claim_no')
            ->filter()
            ->unique()
            ->toArray();


        return view(
            'claims.claims_corrections_index',
            compact(
                'claims',
                'members',
                'pending',
                'voided'
            )
        );
    } // End Method

    public function claimCorrectionEdit($claimNo)
    {
        $claims = DB::table('claims')
            ->where('claim_no', $claimNo)
            ->where('status', 'sent')
            ->orderBy('service_from')
            ->orderBy('line_no')
            ->get();

        if ($claims->isEmpty()) {
            return redirect()
                ->route('claims.corrections.index')
                ->with(
                    'error',
                    'Original submitted claim could not be found.'
                );
        }

        $firstClaim = $claims->first();

        $member = DB::table('billing_members')
            ->where(
                'ParticipantId',
                $firstClaim->ParticipantId
            )
            ->first();

        $alreadyPending = DB::table('claim_resubmissions')
            ->where('old_claim_no', $claimNo)
            ->whereNull('new_batch_id')
            ->exists();

        if ($alreadyPending) {
            return redirect()
                ->route('claims.corrections.index')
                ->with(
                    'error',
                    "Claim {$claimNo} already has a pending resubmission."
                );
        }

        return view(
            'claims.claims_corrections_correct',
            compact(
                'claims',
                'firstClaim',
                'member',
                'claimNo'
            )
        );
    } // End Method

    public function claimCorrectionPrepare(Request $request, $claimNo)
    {
        $request->validate([
            'payer_icn' => 'required|string|max:100',

            'service_lines' => 'required|array|min:1',

            'service_lines.*.source_id' => 'nullable|integer',

            'service_lines.*.service_from' =>
            'required|date',

            'service_lines.*.service_to' =>
            'required|date',

            'service_lines.*.units' =>
            'required|numeric|min:1',

            'service_lines.*.amount_per_unit' =>
            'required|numeric|min:0',

            'service_lines.*.diag1' =>
            'nullable|string|max:20',

            'service_lines.*.diag2' =>
            'nullable|string|max:20',
        ]);


        DB::beginTransaction();


        try {

            /*
            |--------------------------------------------------------------------------
            | 1. Load Original SENT Claim
            |--------------------------------------------------------------------------
            */

            $originalClaims = DB::table('claims')
                ->where(
                    'claim_no',
                    $claimNo
                )
                ->where(
                    'status',
                    'sent'
                )
                ->orderBy(
                    'line_no'
                )
                ->lockForUpdate()
                ->get();


            if ($originalClaims->isEmpty()) {

                throw new \Exception(
                    "Original claim {$claimNo} could not be found."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 2. Prevent Duplicate Pending Resubmission
            |--------------------------------------------------------------------------
            */

            $alreadyPending = DB::table(
                'claim_resubmissions'
            )
                ->where(
                    'old_claim_no',
                    $claimNo
                )
                ->whereNull(
                    'new_batch_id'
                )
                ->exists();


            if ($alreadyPending) {

                throw new \Exception(
                    "Claim {$claimNo} already has a pending resubmission."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | 3. Payer ICN
            |--------------------------------------------------------------------------
            */

            $payerIcn = trim(
                $request->payer_icn
            );


            /*
            |--------------------------------------------------------------------------
            | 4. Reserve NEW INTERNAL Claim Number
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | Original payer claim = 000011
            | New internal claim    = 017779
            |
            | Later build837P_new() will still send:
            |
            | CLM*000011*...***99:B:7...
            |
            */

            $reservedNumbers =
                $this->reserveClaimNumberRange(1);


            if (
                $reservedNumbers
                instanceof
                \Illuminate\Support\Collection
            ) {

                $newClaimNo =
                    $reservedNumbers->first();
            } elseif (
                is_array($reservedNumbers)
            ) {

                $newClaimNo =
                    reset($reservedNumbers);
            } else {

                $newClaimNo =
                    $reservedNumbers;
            }


            $newClaimNo = str_pad(
                (string) $newClaimNo,
                6,
                '0',
                STR_PAD_LEFT
            );


            /*
            |--------------------------------------------------------------------------
            | 5. Template Claim
            |--------------------------------------------------------------------------
            */

            $templateClaim =
                $originalClaims->first();


            $newClaimIds = [];

            $lineNo = 1;


            /*
            |--------------------------------------------------------------------------
            | 6. Create Corrected Service Lines
            |--------------------------------------------------------------------------
            */

            foreach (
                $request->service_lines as $line
            ) {

                /*
                |--------------------------------------------------------------------------
                | Find Source Claim Row
                |--------------------------------------------------------------------------
                */

                $sourceClaim = null;


                if (
                    !empty($line['source_id'])
                ) {

                    $sourceClaim =
                        $originalClaims
                        ->firstWhere(
                            'id',
                            (int) $line['source_id']
                        );
                }


                /*
                * New lines added by user may not have
                * an original source row.
                */

                if (!$sourceClaim) {

                    $sourceClaim =
                        $templateClaim;
                }


                /*
                |--------------------------------------------------------------------------
                | Validate Dates
                |--------------------------------------------------------------------------
                */

                $serviceFrom =
                    $line['service_from'];

                $serviceTo =
                    $line['service_to'];


                if (
                    $serviceTo
                    <
                    $serviceFrom
                ) {

                    throw new \Exception(
                        'Service To date cannot be before Service From date.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Clone Original Row
                |--------------------------------------------------------------------------
                */

                $newRow =
                    (array) $sourceClaim;


                unset(
                    $newRow['id']
                );


                /*
                |--------------------------------------------------------------------------
                | IMPORTANT - NEW INTERNAL CLAIM NUMBER
                |--------------------------------------------------------------------------
                */

                $newRow['claim_no'] =
                    $newClaimNo;


                /*
                |--------------------------------------------------------------------------
                | Payer ICN
                |--------------------------------------------------------------------------
                */

                $newRow['payer_claim_number'] =
                    $payerIcn;


                /*
                |--------------------------------------------------------------------------
                | Corrected Service Line
                |--------------------------------------------------------------------------
                */

                $newRow['line_no'] =
                    $lineNo;


                $newRow['service_from'] =
                    $serviceFrom;


                $newRow['service_to'] =
                    $serviceTo;


                $newRow['units'] =
                    $line['units'];


                $newRow['amount_per_unit'] =
                    $line['amount_per_unit'];


                $newRow['total_amount'] =
                    round(
                        (float) $line['units']
                            *
                            (float) $line['amount_per_unit'],
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Diagnosis
                |--------------------------------------------------------------------------
                */

                if (
                    array_key_exists(
                        'diag1',
                        $line
                    )
                ) {

                    $newRow['diag1'] =
                        trim(
                            $line['diag1']
                                ?? ''
                        );
                }


                if (
                    array_key_exists(
                        'diag2',
                        $line
                    )
                ) {

                    $newRow['diag2'] =
                        trim(
                            $line['diag2']
                                ?? ''
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | Ready For 837P
                |--------------------------------------------------------------------------
                */

                $newRow['status'] =
                    'ready';


                $newRow['precheck_result'] =
                    'valid';


                $newRow['precheck_message'] =
                    null;


                /*
                |--------------------------------------------------------------------------
                | Timestamps
                |--------------------------------------------------------------------------
                */

                if (
                    array_key_exists(
                        'created_at',
                        $newRow
                    )
                ) {

                    $newRow['created_at'] =
                        now();
                }


                if (
                    array_key_exists(
                        'updated_at',
                        $newRow
                    )
                ) {

                    $newRow['updated_at'] =
                        now();
                }


                /*
                |--------------------------------------------------------------------------
                | Insert Corrected Row
                |--------------------------------------------------------------------------
                */

                $newId = DB::table(
                    'claims'
                )->insertGetId(
                    $newRow
                );


                $newClaimIds[] =
                    $newId;


                $lineNo++;
            }


            /*
            |--------------------------------------------------------------------------
            | 7. Original Batch
            |--------------------------------------------------------------------------
            */

            $oldClaimIds =
                $originalClaims
                ->pluck('id')
                ->toArray();


            $oldBatchId =
                DB::table(
                    'claim_batch_items'
                )
                ->whereIn(
                    'claim_id',
                    $oldClaimIds
                )
                ->orderByDesc(
                    'claim_batch_id'
                )
                ->value(
                    'claim_batch_id'
                );


            /*
            |--------------------------------------------------------------------------
            | 8. Create Resubmission History
            |--------------------------------------------------------------------------
            |
            | old_claim_no = payer-facing original
            | new_claim_no = internal corrected record
            |
            */

            DB::table(
                'claim_resubmissions'
            )->insert([

                'claim_id' =>
                $newClaimIds[0],

                'old_claim_no' =>
                $claimNo,

                'new_claim_no' =>
                $newClaimNo,

                'old_batch_id' =>
                $oldBatchId,

                'new_batch_id' =>
                null,

                'resubmit_type' =>
                'corrected',

                'reason' =>
                'Corrected / Replacement Claim. Payer ICN: '
                    . $payerIcn,

                //'created_by' =>
                //    auth()->id(),

                'created_at' =>
                now(),

                'updated_at' =>
                now(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | 9. Commit
            |--------------------------------------------------------------------------
            */

            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | 10. Success
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'claims.resubmission.ready'
                )
                ->with(
                    'success',

                    "Corrected claim {$claimNo} has been prepared. "
                        . "Internal resubmission claim number: {$newClaimNo}."
                );
        } catch (\Throwable $e) {

            DB::rollBack();


            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    } // End Method

    public function claimVoidEdit($claimNo)
    {
        $claims = DB::table('claims')
            ->where('claim_no', $claimNo)
            ->where('status', 'sent')
            ->orderBy('service_from')
            ->orderBy('line_no')
            ->get();

        if ($claims->isEmpty()) {
            return redirect()
                ->route('claims.corrections.index')
                ->with(
                    'error',
                    'Original submitted claim could not be found.'
                );
        }

        $firstClaim = $claims->first();

        $member = DB::table('billing_members')
            ->where(
                'ParticipantId',
                $firstClaim->ParticipantId
            )
            ->first();

        $alreadyPending = DB::table('claim_resubmissions')
            ->where('old_claim_no', $claimNo)
            ->whereNull('new_batch_id')
            ->exists();

        if ($alreadyPending) {
            return redirect()
                ->route('claims.corrections.index')
                ->with(
                    'error',
                    "Claim {$claimNo} already has a pending resubmission."
                );
        }

        $totalAmount =
            $claims->sum('total_amount');

        return view(
            'claims.claims_corrections_void',
            compact(
                'claims',
                'firstClaim',
                'member',
                'claimNo',
                'totalAmount'
            )
        );
    } // End Method

    public function claimVoidPrepare(Request $request, $claimNo)
    {
        $request->validate([
            'payer_icn' => 'required|string|max:100',
        ]);

        DB::beginTransaction();

        try {

            // ---------------------------------------------------------
            // 1. Original submitted claim
            // ---------------------------------------------------------
            $originalClaims = DB::table('claims')
                ->where('claim_no', $claimNo)
                ->where('status', 'sent')
                ->orderBy('line_no')
                ->lockForUpdate()
                ->get();

            if ($originalClaims->isEmpty()) {
                throw new \Exception(
                    "Original claim {$claimNo} could not be found."
                );
            }


            // ---------------------------------------------------------
            // 2. Make sure it is not already pending
            // ---------------------------------------------------------
            $alreadyPending = DB::table('claim_resubmissions')
                ->where('old_claim_no', $claimNo)
                ->whereNull('new_batch_id')
                ->exists();

            if ($alreadyPending) {
                throw new \Exception(
                    "Claim {$claimNo} already has a pending resubmission."
                );
            }


            // ---------------------------------------------------------
            // 3. Manual Payer ICN
            // ---------------------------------------------------------
            $payerIcn = trim($request->payer_icn);


            // ---------------------------------------------------------
            // 4. Reserve NEW INTERNAL claim number
            // ---------------------------------------------------------
            $reservedNumbers = $this->reserveClaimNumberRange(1);

            if ($reservedNumbers instanceof \Illuminate\Support\Collection) {

                $newClaimNo = $reservedNumbers->first();
            } elseif (is_array($reservedNumbers)) {

                $newClaimNo = reset($reservedNumbers);
            } else {

                $newClaimNo = $reservedNumbers;
            }


            $newClaimNo = str_pad(
                (string) $newClaimNo,
                6,
                '0',
                STR_PAD_LEFT
            );


            // ---------------------------------------------------------
            // 5. Clone all original service lines
            // ---------------------------------------------------------
            $newClaimIds = [];

            foreach ($originalClaims as $originalClaim) {

                $newRow = (array) $originalClaim;

                // New database ID
                unset($newRow['id']);


                /*
                * IMPORTANT:
                *
                * Database uses NEW internal claim number.
                *
                * Later the 837P generator will use the ORIGINAL
                * claim number for CLM01 on corrected/void claims.
                */
                $newRow['claim_no'] = $newClaimNo;


                // Payer ICN
                $newRow['payer_claim_number'] = $payerIcn;


                // Ready for resubmission
                $newRow['status'] = 'ready';

                $newRow['precheck_result'] = 'valid';

                $newRow['precheck_message'] = null;


                if (array_key_exists('created_at', $newRow)) {
                    $newRow['created_at'] = now();
                }

                if (array_key_exists('updated_at', $newRow)) {
                    $newRow['updated_at'] = now();
                }


                $newId = DB::table('claims')
                    ->insertGetId($newRow);


                $newClaimIds[] = $newId;
            }


            // ---------------------------------------------------------
            // 6. Find original batch
            // ---------------------------------------------------------
            $oldClaimIds = $originalClaims
                ->pluck('id')
                ->toArray();


            $oldBatchId = DB::table('claim_batch_items')
                ->whereIn('claim_id', $oldClaimIds)
                ->orderByDesc('claim_batch_id')
                ->value('claim_batch_id');


            // ---------------------------------------------------------
            // 7. Resubmission history
            // ---------------------------------------------------------
            DB::table('claim_resubmissions')->insert([

                'claim_id' =>
                $newClaimIds[0],

                'old_claim_no' =>
                $claimNo,

                'new_claim_no' =>
                $newClaimNo,

                'old_batch_id' =>
                $oldBatchId,

                'new_batch_id' =>
                null,

                'resubmit_type' =>
                'void',

                'reason' =>
                'Void entire claim. Payer ICN: ' . $payerIcn,

                //'created_by' =>
                //    auth()->id(),

                'created_at' =>
                now(),

                'updated_at' =>
                now(),
            ]);


            DB::commit();


            return redirect()
                ->route('claims.resubmission.ready')
                ->with(
                    'success',
                    "Claim {$claimNo} has been prepared for complete void. Internal resubmission claim number: {$newClaimNo}."
                );
        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Member Statement - Filter Screen
    |--------------------------------------------------------------------------
    */
    public function memberStatement()
    {
        $members = DB::table('billing_members')
            ->select(
                'ParticipantId',
                'FirstName',
                'LastName',
                'MedicaidNo'
            )
            ->whereNotNull('ParticipantId')
            ->where('ParticipantId', '<>', '')
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->get();

        return view('claims.claims_member_statement', compact('members'));
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Member Statement - View
    |--------------------------------------------------------------------------
    */
    public function memberStatementView(Request $request)
    {
        $request->validate([
            'participant_id' => 'required',
            'date_from'      => 'required|date',
            'date_to'        => 'required|date|after_or_equal:date_from',
        ]);

        $data = $this->getMemberStatementData(
            $request->participant_id,
            $request->date_from,
            $request->date_to
        );

        return view('claims.claims_member_statement_view', $data);
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Member Statement - PDF
    |--------------------------------------------------------------------------
    */
    public function memberStatementPdf(Request $request)
    {
        $request->validate([
            'participant_id' => 'required',
            'date_from'      => 'required|date',
            'date_to'        => 'required|date|after_or_equal:date_from',
        ]);

        $data = $this->getMemberStatementData(
            $request->participant_id,
            $request->date_from,
            $request->date_to
        );

        $html = view('claims.claims_member_statement_pdf', $data)->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'orientation' => 'L',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
        ]);

        $mpdf->WriteHTML($html);

        $fileName = 'Member_Statement_'
            . $data['member']->ParticipantId
            . '_'
            . Carbon::parse($request->date_from)->format('Ymd')
            . '_'
            . Carbon::parse($request->date_to)->format('Ymd')
            . '.pdf';

        return response(
            $mpdf->Output($fileName, 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            ]
        );
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Member Statement - Excel
    |--------------------------------------------------------------------------
    */
    public function memberStatementExcel(Request $request)
    {
        $request->validate([
            'participant_id' => 'required',
            'date_from'      => 'required|date',
            'date_to'        => 'required|date|after_or_equal:date_from',
        ]);

        $data = $this->getMemberStatementData(
            $request->participant_id,
            $request->date_from,
            $request->date_to
        );

        $fileName = 'Member_Statement_'
            . $data['member']->ParticipantId
            . '_'
            . Carbon::parse($request->date_from)->format('Ymd')
            . '_'
            . Carbon::parse($request->date_to)->format('Ymd')
            . '.xlsx';

        return Excel::download(
            new MemberStatementExport($data),
            $fileName
        );
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Common Member Statement Data
    |--------------------------------------------------------------------------
    | Used by View, PDF and Excel.
    |--------------------------------------------------------------------------
    */
    private function getMemberStatementData($participantId, $dateFrom, $dateTo)
    {
        /*
        |--------------------------------------------------------------------------
        | Member
        |--------------------------------------------------------------------------
        */
        $member = DB::table('billing_members')
            ->where('ParticipantId', $participantId)
            ->first();

        if (!$member) {
            abort(404, 'Member not found.');
        }

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to   = Carbon::parse($dateTo)->endOfDay();


        /*
        |--------------------------------------------------------------------------
        | Opening Balance
        |--------------------------------------------------------------------------
        |
        | Opening balance =
        | Previous Charges - Previous Payments
        |
        | Patient responsibility is NOT subtracted because it is still an
        | outstanding receivable.
        |--------------------------------------------------------------------------
        */
        $opening = DB::table('claims')
            ->where('ParticipantId', $participantId)
            ->whereDate('service_from', '<', $from->toDateString())
            ->selectRaw('
                COALESCE(SUM(balance_amount), 0) AS balance
            ')
            ->first();

        $openingBalance = (float) ($opening->balance ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Transactions Within Selected Period
        |--------------------------------------------------------------------------
        */
        $claims = DB::table('claims')
            ->where('ParticipantId', $participantId)
            ->whereDate('service_from', '>=', $from->toDateString())
            ->whereDate('service_from', '<=', $to->toDateString())
            ->orderBy('service_from')
            ->orderBy('claim_no')
            ->orderBy('line_no')
            ->get([
                'id',
                'claim_no',
                'line_no',
                'ParticipantId',
                'service_from',
                'service_to',
                'insurance_name',
                'payer_claim_number',
                'total_amount',
                'ins1_paid',
                'patient_responsibility',
                'adjustment_amount',
                'deduction_amount',
                'writeoff_amount',
                'balance_amount',
                'status',
                'paid_date',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Running Balance
        |--------------------------------------------------------------------------
        */
        $runningBalance = $openingBalance;

        $transactions = $claims->map(function ($claim) {

            return (object) [
                'id'                     => $claim->id,
                'claim_no'               => $claim->claim_no,
                'line_no'                => $claim->line_no,
                'ParticipantId'          => $claim->ParticipantId,
                'service_from'           => $claim->service_from,
                'service_to'             => $claim->service_to,
                'insurance_name'         => $claim->insurance_name ?? '',
                'payer_claim_number'     => $claim->payer_claim_number,

                'charge_amount'          => (float) ($claim->total_amount ?? 0),
                'paid_amount'            => (float) ($claim->ins1_paid ?? 0),
                'patient_responsibility' => (float) ($claim->patient_responsibility ?? 0),
                'balance'                => (float) ($claim->balance_amount ?? 0),

                'adjustment_amount'      => (float) ($claim->adjustment_amount ?? 0),
                'deduction_amount'       => (float) ($claim->deduction_amount ?? 0),
                'writeoff_amount'        => (float) ($claim->writeoff_amount ?? 0),

                'status'                 => $claim->status,
                'paid_date'              => $claim->paid_date,
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */
        $totalCharge = $transactions->sum('charge_amount');

        $totalPayment = $transactions->sum('paid_amount');

        $totalPatientResp = $transactions->sum('patient_responsibility');

        $totalBalance = $transactions->sum('balance');

        /*
        |--------------------------------------------------------------------------
        | Closing Balance
        |--------------------------------------------------------------------------
        |
        | DO NOT sum the Balance column because that is a running balance.
        | We display the last running balance as the total/ending balance.
        |--------------------------------------------------------------------------
        */
        $closingBalance = $runningBalance;


        /*
        |--------------------------------------------------------------------------
        | Statement Data
        |--------------------------------------------------------------------------
        */
        return [
            'member'               => $member,

            'dateFrom'             => $from->toDateString(),
            'dateTo'               => $to->toDateString(),

            'dateFromFormatted'    => $from->format('m/d/Y'),
            'dateToFormatted'      => $to->format('m/d/Y'),

            'openingBalance'       => $openingBalance,

            'transactions'         => $transactions,

            'totalCharge'          => $totalCharge,
            'totalPayment'         => $totalPayment,
            'totalPatientResp'     => $totalPatientResp,

            'closingBalance'       => $totalBalance,
        ];
    } // End method
}
