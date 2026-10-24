<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SpecialReportController extends Controller
{
    public function SpecialReportDashboard()
    {

        return view('specialreports.specialreports_index');
    } // End Method

    public function pendingRebillReport(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Get Report Data
        |--------------------------------------------------------------------------
        */

        $data =
            $this->buildPendingRebillReportData(
                $request
            );


        /*
        |--------------------------------------------------------------------------
        | Screen
        |--------------------------------------------------------------------------
        */

        return view(
            'specialreports.pending_rebill_report',
            $data
        );
    } // End Method

    public function pendingRebillReportPdf(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Get Report Data
        |--------------------------------------------------------------------------
        */

        $data =
            $this->buildPendingRebillReportData(
                $request
            );


        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdf =
            Pdf::loadView(
                'specialreports.pending_rebill_report_pdf',
                $data
            );


        $pdf->setPaper(
            'a4',
            'landscape'
        );


        $pdf->setOptions([
            'isRemoteEnabled' =>
            false,

            'isHtml5ParserEnabled' =>
            true,

            'isPhpEnabled' =>
            false,

            'defaultFont' =>
            'Arial',

            'dpi' =>
            96,

            'fontHeightRatio' =>
            1.0,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Filename
        |--------------------------------------------------------------------------
        */

        $filename =
            'pending_rebill_report_' .
            now()->format(
                'Ymd_His'
            ) .
            '.pdf';


        return $pdf->stream(
            $filename
        );
    } // End Method

    private function buildPendingRebillReportData(Request $request): array
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
        | Member IDs
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


        /*
        |--------------------------------------------------------------------------
        | Member Information
        |--------------------------------------------------------------------------
        */

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


                    /*
                    |--------------------------------------------------------------------------
                    | Member Name
                    |--------------------------------------------------------------------------
                    */

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
                    | Current PA
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

                            /*
                            |--------------------------------------------------------------------------
                            | Original Medicaid PA
                            |--------------------------------------------------------------------------
                            */

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


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [
            'rows' =>
            $rows,

            'status' =>
            $status,

            'summary' =>
            $summary,
        ];
    } // End Method


}
