<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ClaimsReportsController extends Controller
{
    //

    public function index(Request $request)
    {
        $fromDate = $request->input(
            'from_date',
            now()->startOfMonth()->format('Y-m-d')
        );

        $toDate = $request->input(
            'to_date',
            now()->format('Y-m-d')
        );

        $batches = DB::table('claim_batches')
            ->whereDate('batch_timestamp', '>=', $fromDate)
            ->whereDate('batch_timestamp', '<=', $toDate)
            ->orderByDesc('batch_timestamp')
            ->get();

        return view(
            'reports.submitted_claims_index',
            compact(
                'batches',
                'fromDate',
                'toDate'
            )
        );
    } // End Method

    private function getSubmittedClaimsBatchData($batchId)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Batch Header
        |--------------------------------------------------------------------------
        */

        $batch = DB::table('claim_batches')
            ->where('id', $batchId)
            ->first();

        if (!$batch) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | 2. Claims Originally Linked To This Batch
        |--------------------------------------------------------------------------
        */

        $claimIds = DB::table('claim_batch_items')
            ->where('claim_batch_id', $batchId)
            ->pluck('claim_id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();


        $claims = collect();

        if (!empty($claimIds)) {

            $claims = DB::table('claims')
                ->whereIn('id', $claimIds)
                ->orderBy('ParticipantId')
                ->orderBy('service_from')
                ->orderBy('id')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | 3. Resubmission History For This Batch
        |--------------------------------------------------------------------------
        |
        | Important:
        |
        | claims.claim_no may have changed after a resubmission.
        |
        | If this report is for an OLD batch:
        |     show old_claim_no
        |
        | If this report is for the NEW resubmission batch:
        |     show new_claim_no
        |
        |--------------------------------------------------------------------------
        */

        $resubmissions = collect();

        if (!empty($claimIds)) {

            $resubmissions = DB::table('claim_resubmissions')
                ->whereIn('claim_id', $claimIds)
                ->where(function ($query) use ($batchId) {

                    $query
                        ->where('old_batch_id', $batchId)
                        ->orWhere('new_batch_id', $batchId);
                })
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | 4. Build Submitted Claim Number Lookup
        |--------------------------------------------------------------------------
        */

        $submittedClaimNumbers = [];

        foreach ($resubmissions as $history) {

            $claimId = (int) $history->claim_id;

            if ((int) $history->old_batch_id === (int) $batchId) {

                $submittedClaimNumbers[$claimId] =
                    $history->old_claim_no;
            } elseif ((int) $history->new_batch_id === (int) $batchId) {

                $submittedClaimNumbers[$claimId] =
                    $history->new_claim_no;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 5. Member Information
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
        | 6. Attach Display Information
        |--------------------------------------------------------------------------
        */

        $claims = $claims->map(
            function ($claim) use (
                $membersById,
                $submittedClaimNumbers
            ) {

                $participantId = trim(
                    (string) ($claim->ParticipantId ?? '')
                );


                $member =
                    $membersById[$participantId] ?? null;


                $claim->member_lastname =
                    $member->LastName ?? '';

                $claim->member_firstname =
                    $member->FirstName ?? '';


                /*
                * Claim number actually submitted in this batch.
                */

                $claim->submitted_claim_no =
                    $submittedClaimNumbers[(int) $claim->id]
                    ?? $claim->claim_no;


                return $claim;
            }
        );


        return [
            'batch'  => $batch,
            'claims' => $claims,
        ];
    } // End Method

    public function show($batchId)
    {
        $data =
            $this->getSubmittedClaimsBatchData(
                $batchId
            );


        if (!$data) {

            return redirect()
                ->route(
                    'reports.submitted.claims.index'
                )
                ->with([
                    'message' =>
                    'Submitted claim batch not found.',

                    'alert-type' =>
                    'warning',
                ]);
        }


        return view(
            'reports.submitted_claims_show',
            $data
        );
    } // End Method

    public function pdf($batchId)
    {
        $data =
            $this->getSubmittedClaimsBatchData(
                $batchId
            );


        if (!$data) {

            return redirect()
                ->route(
                    'reports.submitted.claims.index'
                )
                ->with([
                    'message' =>
                    'Submitted claim batch not found.',

                    'alert-type' =>
                    'warning',
                ]);
        }


        $batch =
            $data['batch'];


        $pdf = Pdf::loadView(
            'reports.submitted_claims_pdf',
            $data
        )
            ->setPaper(
                'letter',
                'landscape'
            );


        $filename =
            'Submitted_Claims_Batch_' .
            $batch->id .
            '.pdf';

        return $pdf->stream($filename);
    } // End Method

}
