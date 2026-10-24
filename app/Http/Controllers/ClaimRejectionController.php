<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClaimRejectionController extends Controller
{
    /**
     * Rejection Work Queue
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Filter
        |--------------------------------------------------------------------------
        */

        $filter = $request->get('filter', 'all');


        /*
        |--------------------------------------------------------------------------
        | Load Rejected / Invalid Claims
        |--------------------------------------------------------------------------
        |
        | No JOIN is used.
        |
        */

        $claims = DB::table('claims')
            ->where(function ($q) {
                $q->where('status', 'rejected')
                    ->orWhere('precheck_result', 'invalid');
            })
            ->orderBy('service_from', 'asc')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Load Member Names Separately
        |--------------------------------------------------------------------------
        */

        $participantIds = $claims
            ->pluck('ParticipantId')
            ->filter()
            ->map(function ($id) {
                return trim($id);
            })
            ->unique()
            ->values();


        $members = collect();


        if ($participantIds->isNotEmpty()) {

            $members = DB::table('billing_members')
                ->whereIn('ParticipantId', $participantIds)
                ->select(
                    'ParticipantId',
                    'FirstName',
                    'LastName'
                )
                ->get()
                ->keyBy(function ($member) {
                    return trim($member->ParticipantId);
                });
        }


        /*
        |--------------------------------------------------------------------------
        | Attach Member Name + Filing Information
        |--------------------------------------------------------------------------
        */

        $today = Carbon::today();


        foreach ($claims as $claim) {

            /*
            |--------------------------------------------------------------------------
            | Member Information
            |--------------------------------------------------------------------------
            */

            $participantId = trim($claim->ParticipantId ?? '');

            $member = $members->get($participantId);

            $claim->FirstName = $member->FirstName ?? '';
            $claim->LastName  = $member->LastName ?? '';


            /*
            |--------------------------------------------------------------------------
            | Default Filing Values
            |--------------------------------------------------------------------------
            */

            $claim->filing_deadline = null;
            $claim->days_left = null;
            $claim->filing_status = 'unknown';


            /*
            |--------------------------------------------------------------------------
            | Calculate 180-Day Filing Deadline
            |--------------------------------------------------------------------------
            |
            | Business rule:
            | Claim must be submitted/resubmitted within 180 days
            | from service_from.
            |
            */

            if (!empty($claim->service_from)) {

                $serviceFrom = Carbon::parse($claim->service_from);

                $deadline = $serviceFrom
                    ->copy()
                    ->addDays(180);

                $daysLeft = $today->diffInDays(
                    $deadline,
                    false
                );

                $claim->filing_deadline =
                    $deadline->format('m/d/Y');

                $claim->days_left =
                    (int) $daysLeft;


                /*
                |--------------------------------------------------------------------------
                | Determine Filing Status
                |--------------------------------------------------------------------------
                */

                if ($daysLeft < 0) {

                    $claim->filing_status = 'expired';
                } elseif ($daysLeft <= 15) {

                    $claim->filing_status = 'critical';
                } elseif ($daysLeft <= 30) {

                    $claim->filing_status = 'warning';
                } else {

                    $claim->filing_status = 'eligible';
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Build Summary
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | This must be OUTSIDE the foreach above.
        |
        */

        $summary = [
            'total'    => $claims->count(),
            'eligible' => 0,
            'days30'   => 0,
            'days15'   => 0,
            'expired'  => 0,
        ];


        foreach ($claims as $claim) {

            /*
            |--------------------------------------------------------------------------
            | Expired
            |--------------------------------------------------------------------------
            */

            if ($claim->filing_status === 'expired') {

                $summary['expired']++;
            }


            /*
            |--------------------------------------------------------------------------
            | Eligible
            |--------------------------------------------------------------------------
            */

            if (
                in_array(
                    $claim->filing_status,
                    [
                        'eligible',
                        'warning',
                        'critical'
                    ],
                    true
                )
            ) {

                $summary['eligible']++;
            }


            /*
            |--------------------------------------------------------------------------
            | 30 Days or Less
            |--------------------------------------------------------------------------
            */

            if (
                $claim->days_left !== null &&
                $claim->days_left >= 0 &&
                $claim->days_left <= 30
            ) {

                $summary['days30']++;
            }


            /*
            |--------------------------------------------------------------------------
            | 15 Days or Less
            |--------------------------------------------------------------------------
            */

            if (
                $claim->days_left !== null &&
                $claim->days_left >= 0 &&
                $claim->days_left <= 15
            ) {

                $summary['days15']++;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Apply Screen Filters
        |--------------------------------------------------------------------------
        */

        if ($filter === 'eligible') {

            $claims = $claims
                ->filter(function ($claim) {

                    return in_array(
                        $claim->filing_status,
                        [
                            'eligible',
                            'warning',
                            'critical'
                        ],
                        true
                    );
                })
                ->values();
        }


        if ($filter === '30days') {

            $claims = $claims
                ->filter(function ($claim) {

                    return
                        $claim->days_left !== null &&
                        $claim->days_left >= 0 &&
                        $claim->days_left <= 30;
                })
                ->values();
        }


        if ($filter === '15days') {

            $claims = $claims
                ->filter(function ($claim) {

                    return
                        $claim->days_left !== null &&
                        $claim->days_left >= 0 &&
                        $claim->days_left <= 15;
                })
                ->values();
        }


        if ($filter === 'expired') {

            $claims = $claims
                ->filter(function ($claim) {

                    return
                        $claim->filing_status === 'expired';
                })
                ->values();
        }


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view(
            'claims.rejections.index',
            compact(
                'claims',
                'summary',
                'filter'
            )
        );
    } // End Method

    public function show($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Load Claim
        |--------------------------------------------------------------------------
        */

        $claim = DB::table('claims')
            ->where('id', $id)
            ->first();

        if (!$claim) {
            abort(404, 'Claim not found.');
        }


        /*
        |--------------------------------------------------------------------------
        | Load Member Separately
        |--------------------------------------------------------------------------
        */

        $member = null;

        if (!empty($claim->ParticipantId)) {

            $member = DB::table('billing_members')
                ->where('ParticipantId', trim($claim->ParticipantId))
                ->first();
        }


        /*
        |--------------------------------------------------------------------------
        | Filing Information
        |--------------------------------------------------------------------------
        |
        | Filing deadline = service_from + 180 days
        |
        */

        $claim->filing_deadline = null;
        $claim->days_left = null;
        $claim->filing_status = 'unknown';

        if (!empty($claim->service_from)) {

            $today = Carbon::today();

            $serviceFrom = Carbon::parse($claim->service_from);

            $deadline = $serviceFrom
                ->copy()
                ->addDays(180);

            $daysLeft = $today->diffInDays(
                $deadline,
                false
            );

            $claim->filing_deadline =
                $deadline->format('m/d/Y');

            $claim->days_left =
                (int) $daysLeft;

            if ($daysLeft < 0) {

                $claim->filing_status = 'expired';
            } elseif ($daysLeft <= 15) {

                $claim->filing_status = 'critical';
            } elseif ($daysLeft <= 30) {

                $claim->filing_status = 'warning';
            } else {

                $claim->filing_status = 'eligible';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Load Rejection History
        |--------------------------------------------------------------------------
        */

        $history = DB::table('claim_rejection_history')
            ->where('claim_id', $claim->id)
            ->orderByDesc('action_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view(
            'claims.rejections.show',
            compact(
                'claim',
                'member',
                'history'
            )
        );
    } // End Method

    public function edit($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Load Claim
        |--------------------------------------------------------------------------
        */

        $claim = DB::table('claims')
            ->where('id', $id)
            ->first();


        if (!$claim) {

            abort(404, 'Claim not found.');
        }


        /*
        |--------------------------------------------------------------------------
        | Filing Deadline Check
        |--------------------------------------------------------------------------
        |
        | Filing period = service_from + 180 days
        |
        */

        $claim->filing_deadline = null;
        $claim->days_left = null;
        $claim->filing_status = 'unknown';


        if (!empty($claim->service_from)) {

            $today = Carbon::today();

            $serviceFrom = Carbon::parse(
                $claim->service_from
            );

            $deadline = $serviceFrom
                ->copy()
                ->addDays(180);


            $daysLeft = $today->diffInDays(
                $deadline,
                false
            );


            $claim->filing_deadline =
                $deadline->format('m/d/Y');

            $claim->days_left =
                (int) $daysLeft;


            if ($daysLeft < 0) {

                $claim->filing_status = 'expired';
            } elseif ($daysLeft <= 15) {

                $claim->filing_status = 'critical';
            } elseif ($daysLeft <= 30) {

                $claim->filing_status = 'warning';
            } else {

                $claim->filing_status = 'eligible';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Do Not Allow Editing Expired Claim
        |--------------------------------------------------------------------------
        */

        if ($claim->filing_status === 'expired') {

            return redirect()
                ->route(
                    'claims.rejections.show',
                    $claim->id
                )
                ->with(
                    'error',
                    'This claim cannot be corrected for resubmission because the 180-day filing period has expired.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Load Member Separately
        |--------------------------------------------------------------------------
        */

        $member = null;


        if (!empty($claim->ParticipantId)) {

            $member = DB::table('billing_members')
                ->where(
                    'ParticipantId',
                    trim($claim->ParticipantId)
                )
                ->first();
        }


        return view(
            'claims.rejections.edit',
            compact(
                'claim',
                'member'
            )
        );
    } // End Method

    public function update(Request $request, $id)
    {
        /*
        |--------------------------------------------------------------------------
        | Load Claim
        |--------------------------------------------------------------------------
        */

        $claim = DB::table('claims')
            ->where('id', $id)
            ->first();


        if (!$claim) {

            abort(404, 'Claim not found.');
        }


        /*
        |--------------------------------------------------------------------------
        | Check 180-Day Filing Deadline Again
        |--------------------------------------------------------------------------
        |
        | Never depend only on the Blade screen.
        |
        */

        if (!empty($claim->service_from)) {

            $deadline = Carbon::parse(
                $claim->service_from
            )
                ->addDays(180);


            if (Carbon::today()->gt($deadline)) {

                return redirect()
                    ->route(
                        'claims.rejections.show',
                        $claim->id
                    )
                    ->with(
                        'error',
                        'This claim cannot be updated for resubmission because the 180-day filing period expired on '
                            . $deadline->format('m/d/Y')
                            . '.'
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Input
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'Address1' => [
                'nullable',
                'string',
                'max:255'
            ],

            'City' => [
                'nullable',
                'string',
                'max:100'
            ],

            'State' => [
                'nullable',
                'string',
                'max:2'
            ],

            'ZipCode' => [
                'nullable',
                'string',
                'max:20'
            ],

            'diag1' => [
                'nullable',
                'string',
                'max:20'
            ],

            'diag2' => [
                'nullable',
                'string',
                'max:20'
            ],

            'prior_auth_no' => [
                'nullable',
                'string',
                'max:100'
            ],

            'prior_auth_expiry' => [
                'nullable',
                'date'
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize Diagnosis Codes
        |--------------------------------------------------------------------------
        */

        $diag1 = !empty($validated['diag1'])
            ? strtoupper(trim($validated['diag1']))
            : null;


        $diag2 = !empty($validated['diag2'])
            ? strtoupper(trim($validated['diag2']))
            : null;


        /*
        |--------------------------------------------------------------------------
        | Update Claim
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | We are intentionally NOT changing:
        |
        | status
        | precheck_result
        | precheck_message
        |
        | Those will only change when we build the Revalidate action.
        |
        */

        DB::table('claims')
            ->where('id', $id)
            ->update([

                'diag1' =>
                $diag1,

                'diag2' =>
                $diag2,

                'prior_auth_no' =>
                $validated['prior_auth_no'] ?? null,

                'prior_auth_expiry' =>
                $validated['prior_auth_expiry'] ?? null,

                'updated_at' =>
                now(),

            ]);


        DB::table('billing_members')
            ->where(
                'ParticipantId',
                trim($claim->ParticipantId)
            )
            ->update([
                'Address1' => $validated['Address1'] ?? null,
                'City'     => $validated['City'] ?? null,
                'State'    => strtoupper(
                    $validated['State'] ?? ''
                ),
                'ZipCode'  => $validated['ZipCode'] ?? null,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Log Correction History
        |--------------------------------------------------------------------------
        */

        $updatedClaim = DB::table('claims')
            ->where('id', $id)
            ->first();

        $this->logRejectionHistory(
            $updatedClaim,
            'CORRECTED',
            $claim->status ?? null,
            $updatedClaim->status ?? null,
            'Claim and/or member information corrected from Rejection Work Queue.',
            'REJECTION_QUEUE'
        );

        /*
        |--------------------------------------------------------------------------
        | Return to Review Screen
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'claims.rejections.show',
                $id
            )
            ->with(
                'success',
                'Claim corrections were saved successfully. The claim has not yet been revalidated.'
            );
    } // End Method

    public function revalidate($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Load Claim
        |--------------------------------------------------------------------------
        */

        $claim = DB::table('claims')
            ->where('id', $id)
            ->first();

        if (!$claim) {
            abort(404, 'Claim not found.');
        }


        /*
        |--------------------------------------------------------------------------
        | 180-Day Filing Check
        |--------------------------------------------------------------------------
        */

        if (!empty($claim->service_from)) {

            $deadline = Carbon::parse($claim->service_from)
                ->addDays(180);

            if (Carbon::today()->gt($deadline)) {

                return redirect()
                    ->route('claims.rejections.show', $id)
                    ->with(
                        'error',
                        'Claim cannot be revalidated for resubmission because the 180-day filing period expired on '
                            . $deadline->format('m/d/Y')
                            . '.'
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Run Validation
        |--------------------------------------------------------------------------
        */

        $validation = $this->validateRejectedClaim($claim);


        /*
        |--------------------------------------------------------------------------
        | Claim Passed
        |--------------------------------------------------------------------------
        */

        if ($validation['valid']) {

            DB::table('claims')
                ->where('id', $id)
                ->update([
                    'precheck_result'  => 'valid',
                    'precheck_message' => null,
                    'status'           => 'ready',
                    'updated_at'       => now(),
                ]);

            /*
        |--------------------------------------------------------------------------
        | Reload Claim After Successful Revalidation
        |--------------------------------------------------------------------------
        */

            $updatedClaim = DB::table('claims')
                ->where('id', $id)
                ->first();


            /*
        |--------------------------------------------------------------------------
        | Log Successful Revalidation
        |--------------------------------------------------------------------------
        */

            $this->logRejectionHistory(
                $updatedClaim,
                'REVALIDATED',
                $claim->status ?? null,
                $updatedClaim->status ?? null,
                'Claim passed revalidation and moved to READY for resubmission.',
                'REJECTION_QUEUE'
            );


            return redirect()
                ->route('claims.rejections.index')
                ->with(
                    'success',
                    'Claim '
                        . ($claim->claim_no ?? '')
                        . ' passed validation and moved to READY for resubmission.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Claim Still Invalid
        |--------------------------------------------------------------------------
        */

        DB::table('claims')
            ->where('id', $id)
            ->update([
                'precheck_result'  => 'invalid',
                'precheck_message' => implode(' | ', $validation['errors']),
                'status'           => 'rejected',
                'updated_at'       => now(),
            ]);

        $updatedClaim = DB::table('claims')
            ->where('id', $id)
            ->first();


        $this->logRejectionHistory(
            $updatedClaim,
            'STILL_INVALID',
            $claim->status ?? null,
            $updatedClaim->status ?? null,
            implode(' | ', $validation['errors']),
            'REJECTION_QUEUE'
        );


        return redirect()
            ->route('claims.rejections.show', $id)
            ->with(
                'error',
                'Claim is still invalid. Please review the validation errors.'
            );
    } // End Method

    private function validateRejectedClaim($claim)
    {
        $errors = [];


        /*
        |--------------------------------------------------------------------------
        | Participant ID
        |--------------------------------------------------------------------------
        */

        if (empty(trim($claim->ParticipantId ?? ''))) {

            $errors[] = 'Participant ID is missing.';
        }


        /*
        |--------------------------------------------------------------------------
        | Service Dates
        |--------------------------------------------------------------------------
        */

        if (empty($claim->service_from)) {

            $errors[] = 'Service From date is missing.';
        }


        if (empty($claim->service_to)) {

            $errors[] = 'Service To date is missing.';
        }


        if (
            !empty($claim->service_from) &&
            !empty($claim->service_to)
        ) {

            $serviceFrom = Carbon::parse($claim->service_from);
            $serviceTo   = Carbon::parse($claim->service_to);


            if ($serviceTo->lt($serviceFrom)) {

                $errors[] = 'Service To date cannot be before Service From date.';
            }


            if ($serviceFrom->gt(Carbon::today())) {

                $errors[] = 'Service From date cannot be in the future.';
            }


            if ($serviceTo->gt(Carbon::today())) {

                $errors[] = 'Service To date cannot be in the future.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Units
        |--------------------------------------------------------------------------
        */

        if (
            !isset($claim->units) ||
            (float) $claim->units <= 0
        ) {

            $errors[] = 'Units must be greater than zero.';
        }


        /*
        |--------------------------------------------------------------------------
        | Amount Per Unit
        |--------------------------------------------------------------------------
        */

        if (
            !isset($claim->amount_per_unit) ||
            (float) $claim->amount_per_unit <= 0
        ) {

            $errors[] = 'Amount per unit must be greater than zero.';
        }


        /*
        |--------------------------------------------------------------------------
        | Total Amount
        |--------------------------------------------------------------------------
        */

        if (
            isset($claim->units) &&
            isset($claim->amount_per_unit) &&
            isset($claim->total_amount)
        ) {

            $expectedTotal =
                (float) $claim->units *
                (float) $claim->amount_per_unit;


            if (
                abs(
                    $expectedTotal -
                        (float) $claim->total_amount
                ) > 0.01
            ) {

                $errors[] =
                    'Total amount does not equal Units × Amount Per Unit.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Diagnosis
        |--------------------------------------------------------------------------
        */

        $diag1 = trim($claim->diag1 ?? '');
        $diag2 = trim($claim->diag2 ?? '');


        if ($diag1 === '' && $diag2 === '') {

            $errors[] = 'At least one diagnosis code is required.';
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Diagnosis 1
        |--------------------------------------------------------------------------
        */

        if ($diag1 !== '') {

            $exists = DB::table('icd10_codes')
                ->where('code', $diag1)
                ->exists();


            if (!$exists) {

                $errors[] =
                    'Diagnosis 1 code '
                    . $diag1
                    . ' was not found in ICD-10 table.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Diagnosis 2
        |--------------------------------------------------------------------------
        */

        if ($diag2 !== '') {

            $exists = DB::table('icd10_codes')
                ->where('code', $diag2)
                ->exists();


            if (!$exists) {

                $errors[] =
                    'Diagnosis 2 code '
                    . $diag2
                    . ' was not found in ICD-10 table.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Member Validation
        |--------------------------------------------------------------------------
        |
        | Separate query — no JOIN.
        |
        */

        $member = null;


        if (!empty($claim->ParticipantId)) {

            $member = DB::table('billing_members')
                ->where(
                    'ParticipantId',
                    trim($claim->ParticipantId)
                )
                ->first();
        }


        if (!$member) {

            $errors[] = 'Member was not found in billing_members.';
        } else {

            if (empty(trim($member->Address1 ?? ''))) {

                $errors[] = 'Member address is missing.';
            }


            if (empty(trim($member->City ?? ''))) {

                $errors[] = 'Member city is missing.';
            }


            if (empty(trim($member->State ?? ''))) {

                $errors[] = 'Member state is missing.';
            }


            if (empty(trim($member->ZipCode ?? ''))) {

                $errors[] = 'Member ZIP code is missing.';
            }


            if (!empty($member->DateOfBirth)) {

                if (
                    Carbon::parse($member->DateOfBirth)
                    ->gt(Carbon::today())
                ) {

                    $errors[] =
                        'Member date of birth cannot be in the future.';
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Prior Authorization
        |--------------------------------------------------------------------------
        */

        if (empty(trim($claim->prior_auth_no ?? ''))) {

            $errors[] = 'Prior Authorization number is missing.';
        }


        if (empty($claim->prior_auth_expiry)) {

            $errors[] = 'Prior Authorization expiration date is missing.';
        } elseif (!empty($claim->service_to)) {

            $paExpiry = Carbon::parse(
                $claim->prior_auth_expiry
            );

            $serviceTo = Carbon::parse(
                $claim->service_to
            );


            if ($paExpiry->lt($serviceTo)) {

                $errors[] =
                    'Prior Authorization expired before the Service To date.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Result
        |--------------------------------------------------------------------------
        */

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    } // End Method

    public function ready(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Load Ready Claims
        |--------------------------------------------------------------------------
        |
        | These are claims that successfully passed revalidation
        | and are waiting to be included in the next 837P batch.
        |
        */

        $claims = DB::table('claims')
            ->where('status', 'ready')
            ->where('precheck_result', 'valid')
            ->whereNull('claim_no')
            ->orderBy('service_from', 'asc')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Load Member Names Separately
        |--------------------------------------------------------------------------
        */

        $participantIds = $claims
            ->pluck('ParticipantId')
            ->filter()
            ->map(function ($id) {
                return trim($id);
            })
            ->unique()
            ->values();


        $members = collect();


        if ($participantIds->isNotEmpty()) {

            $members = DB::table('billing_members')
                ->whereIn('ParticipantId', $participantIds)
                ->select(
                    'ParticipantId',
                    'FirstName',
                    'LastName'
                )
                ->get()
                ->keyBy(function ($member) {
                    return trim($member->ParticipantId);
                });
        }


        /*
        |--------------------------------------------------------------------------
        | Attach Member + Filing Information
        |--------------------------------------------------------------------------
        */

        $today = Carbon::today();


        foreach ($claims as $claim) {

            /*
            |--------------------------------------------------------------------------
            | Member
            |--------------------------------------------------------------------------
            */

            $participantId =
                trim($claim->ParticipantId ?? '');

            $member =
                $members->get($participantId);

            $claim->FirstName =
                $member->FirstName ?? '';

            $claim->LastName =
                $member->LastName ?? '';


            /*
            |--------------------------------------------------------------------------
            | 180-Day Filing Deadline
            |--------------------------------------------------------------------------
            */

            $claim->filing_deadline = null;
            $claim->days_left = null;
            $claim->filing_status = 'unknown';


            if (!empty($claim->service_from)) {

                $deadline = Carbon::parse(
                    $claim->service_from
                )
                    ->addDays(180);


                $daysLeft = $today->diffInDays(
                    $deadline,
                    false
                );


                $claim->filing_deadline =
                    $deadline->format('m/d/Y');


                $claim->days_left =
                    (int) $daysLeft;


                if ($daysLeft < 0) {

                    $claim->filing_status = 'expired';
                } elseif ($daysLeft <= 15) {

                    $claim->filing_status = 'critical';
                } elseif ($daysLeft <= 30) {

                    $claim->filing_status = 'warning';
                } else {

                    $claim->filing_status = 'eligible';
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [

            'total' => $claims->count(),

            'amount' => $claims->sum(function ($claim) {
                return (float) ($claim->total_amount ?? 0);
            }),

            'critical' => $claims
                ->where('filing_status', 'critical')
                ->count(),

            'warning' => $claims
                ->where('filing_status', 'warning')
                ->count(),

        ];


        return view(
            'claims.rejections.ready',
            compact(
                'claims',
                'summary'
            )
        );
    } // End Method

    private function logRejectionHistory(
        $claim,
        $actionType,
        $oldStatus = null,
        $newStatus = null,
        $message = null,
        $source = null,
        $code = null
    ) {

        DB::table('claim_rejection_history')->insert([

            'claim_id' => $claim->id,

            'claim_no' => $claim->claim_no ?? null,

            'ParticipantId' => $claim->ParticipantId ?? null,

            'rejection_source' => $source,

            'rejection_code' => $code,

            'rejection_message' => $message,

            'old_status' => $oldStatus,

            'new_status' => $newStatus,

            'action_type' => $actionType,

            //'action_by' => auth()->id(),

            'action_at' => now(),

            'created_at' => now(),

            'updated_at' => now(),

        ]);
    } // End Method

    public function createResubmission($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Load Original Claim
        |--------------------------------------------------------------------------
        */

        $originalClaim = DB::table('claims')
            ->where('id', $id)
            ->first();


        if (!$originalClaim) {

            abort(404, 'Claim not found.');
        }


        /*
        |--------------------------------------------------------------------------
        | Original Claim Must Already Have A Claim Number
        |--------------------------------------------------------------------------
        */

        if (empty(trim($originalClaim->claim_no ?? ''))) {

            return redirect()
                ->route(
                    'claims.rejections.show',
                    $id
                )
                ->with(
                    'error',
                    'This claim does not have an original claim number. It should use the normal READY claim workflow.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | 180-Day Filing Check
        |--------------------------------------------------------------------------
        */

        if (!empty($originalClaim->service_from)) {

            $deadline = Carbon::parse(
                $originalClaim->service_from
            )
            ->addDays(180);


            if (Carbon::today()->gt($deadline)) {

                return redirect()
                    ->route(
                        'claims.rejections.show',
                        $id
                    )
                    ->with(
                        'error',
                        'A new claim cannot be created because the 180-day filing period expired on '
                        . $deadline->format('m/d/Y')
                        . '.'
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Resubmission Row
        |--------------------------------------------------------------------------
        |
        | Do not create another READY copy if one already exists.
        |
        */

        $existingResubmission = DB::table('claims')
            ->where(
                'resubmission_of_claim_id',
                $originalClaim->id
            )
            ->whereNull('claim_no')
            ->whereIn(
                'status',
                [
                    'draft',
                    'ready'
                ]
            )
            ->first();


        if ($existingResubmission) {

            return redirect()
                ->route(
                    'claims.rejections.show',
                    $existingResubmission->id
                )
                ->with(
                    'error',
                    'A resubmission claim has already been created for this claim.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Copy Original Claim
        |--------------------------------------------------------------------------
        */

        $newClaim = (array) $originalClaim;


        /*
        |--------------------------------------------------------------------------
        | Remove Primary Key
        |--------------------------------------------------------------------------
        */

        unset($newClaim['id']);


        /*
        |--------------------------------------------------------------------------
        | Reset Submission-Specific Fields
        |--------------------------------------------------------------------------
        */

        $newClaim['claim_no'] = null;

        $newClaim['original_claim_no'] =
            $originalClaim->claim_no;

        $newClaim['resubmission_of_claim_id'] =
            $originalClaim->id;

        $newClaim['status'] =
            'ready';

        $newClaim['precheck_result'] =
            'valid';

        $newClaim['precheck_message'] =
            null;


        /*
        |--------------------------------------------------------------------------
        | New Claim Must Not Belong To Old Billing Group
        |--------------------------------------------------------------------------
        */

        if (array_key_exists(
            'billing_group_id',
            $newClaim
        )) {

            $newClaim['billing_group_id'] = null;
        }


        /*
        |--------------------------------------------------------------------------
        | Reset Timestamps
        |--------------------------------------------------------------------------
        */

        $newClaim['created_at'] = now();
        $newClaim['updated_at'] = now();


        /*
        |--------------------------------------------------------------------------
        | Insert New Claim
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();


        try {

            $newClaimId = DB::table('claims')
                ->insertGetId(
                    $newClaim
                );


            /*
            |--------------------------------------------------------------------------
            | Load Newly Created Claim
            |--------------------------------------------------------------------------
            */

            $createdClaim = DB::table('claims')
                ->where('id', $newClaimId)
                ->first();


            /*
            |--------------------------------------------------------------------------
            | Log History
            |--------------------------------------------------------------------------
            */

            $this->logRejectionHistory(
                $createdClaim,
                'RESUBMISSION_CREATED',
                $originalClaim->status ?? null,
                'ready',
                'New claim created for resubmission from original claim '
                    . $originalClaim->claim_no
                    . '.',
                'REJECTION_QUEUE'
            );


            DB::commit();


            return redirect()
                ->route(
                    'claims.rejections.ready'
                )
                ->with(
                    'success',
                    'New claim created for resubmission from Claim #'
                    . $originalClaim->claim_no
                    . '. It is now READY for the next 837P batch.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();


            return redirect()
                ->route(
                    'claims.rejections.show',
                    $id
                )
                ->with(
                    'error',
                    'Unable to create resubmission claim: '
                    . $e->getMessage()
                );
        }

    } // End Method

}
