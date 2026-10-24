<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class EraReportsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Processed ERA Report - Index
    |--------------------------------------------------------------------------
    |
    | Read-only list of processed ERA files within selected date range.
    |
    */

    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Date Range
        |--------------------------------------------------------------------------
        */

        $fromDate = $request->input(
            'from_date',
            now()->startOfMonth()->format('Y-m-d')
        );

        $toDate = $request->input(
            'to_date',
            now()->format('Y-m-d')
        );


        /*
        |--------------------------------------------------------------------------
        | Load ERA Files
        |--------------------------------------------------------------------------
        |
        | Use payment_date for reporting because this represents the ERA/check
        | payment date.
        |
        */

        $eras = DB::table('era_files')
            ->whereDate(
                'payment_date',
                '>=',
                $fromDate
            )
            ->whereDate(
                'payment_date',
                '<=',
                $toDate
            )
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Claim Count Per ERA
        |--------------------------------------------------------------------------
        |
        | No joins.
        |
        */

        $eraIds = $eras
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();


        $claimCounts = collect();


        if (!empty($eraIds)) {

            $claimCounts = DB::table('era_claims')
                ->select(
                    'era_file_id',
                    DB::raw('COUNT(*) AS claim_count')
                )
                ->whereIn(
                    'era_file_id',
                    $eraIds
                )
                ->groupBy(
                    'era_file_id'
                )
                ->get()
                ->keyBy(
                    fn ($row) =>
                        (int) $row->era_file_id
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Attach Claim Count
        |--------------------------------------------------------------------------
        */

        $eras = $eras->map(
            function ($era) use ($claimCounts) {

                $countRow =
                    $claimCounts[(int) $era->id]
                    ?? null;


                $era->claim_count =
                    $countRow
                    ? (int) $countRow->claim_count
                    : 0;


                return $era;
            }
        );


        return view(
            'reports.processed_era_index',
            compact(
                'eras',
                'fromDate',
                'toDate'
            )
        );
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Processed ERA Report - Show
    |--------------------------------------------------------------------------
    */

    public function show($eraId)
    {
        $data =
            $this->getProcessedEraReportData(
                $eraId
            );


        if (!$data) {

            return redirect()
                ->route(
                    'reports.processed.era.index'
                )
                ->with([
                    'message' =>
                        'Processed ERA record not found.',

                    'alert-type' =>
                        'warning',
                ]);
        }


        return view(
            'reports.processed_era_show',
            $data
        );
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Processed ERA Report - PDF
    |--------------------------------------------------------------------------
    */

    public function pdf($eraId)
    {
        $data =
            $this->getProcessedEraReportData(
                $eraId
            );


        if (!$data) {

            return redirect()
                ->route(
                    'reports.processed.era.index'
                )
                ->with([
                    'message' =>
                        'Processed ERA record not found.',

                    'alert-type' =>
                        'warning',
                ]);
        }


        $era =
            $data['era'];


        $pdf = Pdf::loadView(
            'reports.processed_era_pdf',
            $data
        )
            ->setPaper(
                'letter',
                'landscape'
            );


        $filename =
            'Processed_ERA_' .
            $era->id .
            '.pdf';


        /*
        |--------------------------------------------------------------------------
        | Stream PDF
        |--------------------------------------------------------------------------
        |
        | Since Blade will use target="_blank", stream() opens the PDF
        | in the browser instead of forcing download.
        |
        */

        return $pdf->stream(
            $filename
        );
    } // End Method


    /*
    |--------------------------------------------------------------------------
    | Private Helper
    |--------------------------------------------------------------------------
    |
    | Both screen and PDF use this same method.
    |
    */

    private function getProcessedEraReportData($eraId)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. ERA Header
        |--------------------------------------------------------------------------
        */

        $era = DB::table('era_files')
            ->where(
                'id',
                $eraId
            )
            ->first();


        if (!$era) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | 2. ERA Claims
        |--------------------------------------------------------------------------
        */

        $claims = DB::table('era_claims')
            ->where(
                'era_file_id',
                $eraId
            )
            ->orderBy('member_id')
            ->orderBy('patient_control_number')
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 3. Member IDs
        |--------------------------------------------------------------------------
        |
        | era_claims.member_id stores ParticipantId in your ERA workflow.
        |
        */

        $memberIds = $claims
            ->pluck('member_id')
            ->filter()
            ->map(
                fn ($id) =>
                    trim((string) $id)
            )
            ->unique()
            ->values()
            ->all();


        /*
        |--------------------------------------------------------------------------
        | 4. Load Members Separately
        |--------------------------------------------------------------------------
        */

        $membersById = collect();


        if (!empty($memberIds)) {

            $members = DB::table(
                'billing_members'
            )
                ->select(
                    'ParticipantId',
                    'FirstName',
                    'LastName',
                    
                )
                ->whereIn(
                    DB::raw(
                        'TRIM(ParticipantId)'
                    ),
                    $memberIds
                )
                ->get();


            $membersById =
                $members->keyBy(
                    fn ($member) =>
                        trim(
                            (string)
                            $member->ParticipantId
                        )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | 5. Attach Member Information
        |--------------------------------------------------------------------------
        */

        $claims = $claims->map(
    function ($claim) use ($membersById) {

        $memberId = trim(
            (string) (
                $claim->member_id ?? ''
            )
        );

        $member =
            $membersById[$memberId]
            ?? null;

        $claim->member_firstname =
            $member->FirstName ?? '';

        $claim->member_lastname =
            $member->LastName ?? '';


        /*
        |--------------------------------------------------------------------------
        | Human Readable ERA Claim Status
        |--------------------------------------------------------------------------
        */

        $claim->status_description =
                    match ((string) ($claim->claim_status_code ?? '')) {

                        '1' =>
                            'Processed as Primary',

                        '2' =>
                            'Processed as Secondary',

                        '3' =>
                            'Processed as Tertiary',

                        '4' =>
                            'Denied',

                        '19' =>
                            'Processed as Primary / Forwarded',

                        '20' =>
                            'Processed as Secondary / Forwarded',

                        '21' =>
                            'Processed as Tertiary / Forwarded',

                        '22' =>
                            'Reversal of Previous Payment',

                        '23' =>
                            'Not Our Claim / Forwarded',

                        '25' =>
                            'Predetermination - No Payment',

                        default =>
                            'Status ' .
                            ($claim->claim_status_code ?? 'N/A'),
                    };


                return $claim;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | 6. Report Totals
        |--------------------------------------------------------------------------
        */

        $totalCharge =
            $claims->sum(
                fn ($claim) =>
                    (float) (
                        $claim->total_charge
                        ?? 0
                    )
            );


        $totalPaid =
            $claims->sum(
                fn ($claim) =>
                    (float) (
                        $claim->total_paid
                        ?? 0
                    )
            );


        $totalPatientResponsibility =
            $claims->sum(
                fn ($claim) =>
                    (float) (
                        $claim->patient_responsibility
                        ?? 0
                    )
            );


        /*
        |--------------------------------------------------------------------------
        | 7. Return Shared Report Data
        |--------------------------------------------------------------------------
        */

        return [

            'era' =>
                $era,

            'claims' =>
                $claims,

            'totalCharge' =>
                $totalCharge,

            'totalPaid' =>
                $totalPaid,

            'totalPatientResponsibility' =>
                $totalPatientResponsibility,

        ];

    } //End Method

}