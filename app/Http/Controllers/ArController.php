<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ArController extends Controller
{
    public function agingSummary(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | A/R Aging Summary
        |--------------------------------------------------------------------------
        | Uses:
        | - claims.balance_amount
        | - claims.submitted_date if available
        | - otherwise claims.service_from
        */

        $asOfInput = $request->input('as_of_date');

        try {
            $asOfDate = $asOfInput
                ? Carbon::createFromFormat('m/d/Y', $asOfInput)->format('Y-m-d')
                : now()->format('Y-m-d');
        } catch (\Throwable $e) {
            $asOfDate = now()->format('Y-m-d');
        }

        $asOfDisplay = Carbon::parse($asOfDate)->format('m/d/Y');

                $agingBase = DB::table('claims')
            ->selectRaw("
                COALESCE(NULLIF(insurance_name, ''), 'Unknown') as insurance_name,
                balance_amount,
                COALESCE(submitted_date, service_from) as aging_date
            ")
            ->where('balance_amount', '>', 0);

        $rows = DB::query()
            ->fromSub($agingBase, 'ar')
            ->select(
                'insurance_name',
                DB::raw("SUM(CASE WHEN DATEDIFF('$asOfDate', aging_date) BETWEEN 0 AND 30 THEN balance_amount ELSE 0 END) as bucket_0_30"),
                DB::raw("SUM(CASE WHEN DATEDIFF('$asOfDate', aging_date) BETWEEN 31 AND 60 THEN balance_amount ELSE 0 END) as bucket_31_60"),
                DB::raw("SUM(CASE WHEN DATEDIFF('$asOfDate', aging_date) BETWEEN 61 AND 90 THEN balance_amount ELSE 0 END) as bucket_61_90"),
                DB::raw("SUM(CASE WHEN DATEDIFF('$asOfDate', aging_date) BETWEEN 91 AND 120 THEN balance_amount ELSE 0 END) as bucket_91_120"),
                DB::raw("SUM(CASE WHEN DATEDIFF('$asOfDate', aging_date) > 120 THEN balance_amount ELSE 0 END) as bucket_120_plus"),
                DB::raw("SUM(balance_amount) as total_balance"),
                DB::raw("COUNT(*) as claim_count")
            )
            ->groupBy('insurance_name')
            ->orderBy('insurance_name')
            ->get();

        $totals = [
            'bucket_0_30'     => $rows->sum('bucket_0_30'),
            'bucket_31_60'    => $rows->sum('bucket_31_60'),
            'bucket_61_90'    => $rows->sum('bucket_61_90'),
            'bucket_91_120'   => $rows->sum('bucket_91_120'),
            'bucket_120_plus' => $rows->sum('bucket_120_plus'),
            'total_balance'   => $rows->sum('total_balance'),
            'claim_count'     => $rows->sum('claim_count'),
        ];

        return view('ar.aging_summary', compact(
            'rows',
            'totals',
            'asOfDate',
            'asOfDisplay'
        ));

    } // End Method


    public function agingDetail(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | A/R Aging Detail
        |--------------------------------------------------------------------------
        | Shows individual open claims for selected insurance.
        */

        $insurance = $request->query('insurance', 'Unknown');
        $asOfInput = $request->input('as_of_date');

        try {
            $asOfDate = $asOfInput
                ? Carbon::createFromFormat('m/d/Y', $asOfInput)->format('Y-m-d')
                : now()->format('Y-m-d');
        } catch (\Throwable $e) {
            $asOfDate = now()->format('Y-m-d');
        }

        $asOfDisplay = Carbon::parse($asOfDate)->format('m/d/Y');

        /*
        * Base query: only open A/R claims.
        * For Unknown insurance, include NULL or blank insurance_name.
        */
        $query = DB::table('claims')
            ->select(
                'id',
                'claim_no',
                'ParticipantId',
                'insurance_name',
                'service_from',
                'service_to',
                'submitted_date',
                'total_amount',
                'allowed',
                'ins1_paid',
                'adjustment_amount',
                'patient_responsibility',
                'deduction_amount',
                'writeoff_amount',
                'balance_amount',
                'status',
                DB::raw("DATEDIFF('$asOfDate', COALESCE(submitted_date, service_from)) as aging_days")
            )
            ->where('balance_amount', '>', 0);

        if ($insurance === 'Unknown') {
            $query->where(function ($q) {
                $q->whereNull('insurance_name')
                ->orWhere('insurance_name', '');
            });
        } else {
            $query->where('insurance_name', $insurance);
        }

        $claims = $query
            ->orderByRaw("DATEDIFF('$asOfDate', COALESCE(submitted_date, service_from)) DESC")
            ->orderBy('claim_no')
            ->get();

        /*
        * Load member names separately to avoid joins/collation issues.
        */
        $participantIds = $claims
            ->pluck('ParticipantId')
            ->filter()
            ->unique()
            ->values();

        $members = DB::table('members')
            ->whereIn('ParticipantId', $participantIds)
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->get()
            ->keyBy('ParticipantId');

        $totals = [
            'total_amount'             => $claims->sum('total_amount'),
            'allowed'                  => $claims->sum('allowed'),
            'ins1_paid'                => $claims->sum('ins1_paid'),
            'adjustment_amount'        => $claims->sum('adjustment_amount'),
            'patient_responsibility'   => $claims->sum('patient_responsibility'),
            'deduction_amount'         => $claims->sum('deduction_amount'),
            'writeoff_amount'          => $claims->sum('writeoff_amount'),
            'balance_amount'           => $claims->sum('balance_amount'),
            'claim_count'              => $claims->count(),
        ];

        return view('ar.aging_detail', compact(
            'claims',
            'members',
            'totals',
            'insurance',
            'asOfDate',
            'asOfDisplay'
        ));

    } // End Method

    public function transactionHistory($claimId)
    {
        /*
        |--------------------------------------------------------------------------
        | Claim Transaction History
        |--------------------------------------------------------------------------
        | Shows all A/R transactions for one claim.
        */

        $claim = DB::table('claims')
            ->where('id', $claimId)
            ->first();

        if (!$claim) {
            abort(404, 'Claim not found.');
        }

        /*
        * Load member separately to avoid joins/collation issues.
        */
        $member = DB::table('members')
            ->where('ParticipantId', $claim->ParticipantId)
            ->select('ParticipantId', 'LastName', 'FirstName')
            ->first();

        /*
        * Load transactions.
        */
        $transactions = DB::table('claim_transactions')
            ->where('claim_id', $claim->id)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $totals = [
            'charge' => $transactions
                ->where('transaction_type', 'charge')
                ->sum('amount'),

            'payment' => $transactions
                ->where('transaction_type', 'payment')
                ->sum('amount'),

            'contractual_adjustment' => $transactions
                ->where('transaction_type', 'contractual_adjustment')
                ->sum('amount'),

            'patient_responsibility' => $transactions
                ->where('transaction_type', 'patient_responsibility')
                ->sum('amount'),

            'deduction' => $transactions
                ->where('transaction_type', 'deduction')
                ->sum('amount'),

            'writeoff' => $transactions
                ->where('transaction_type', 'writeoff')
                ->sum('amount'),

            'manual_adjustment' => $transactions
                ->where('transaction_type', 'manual_adjustment')
                ->sum('amount'),
        ];

        return view('ar.transaction_history', compact(
            'claim',
            'member',
            'transactions',
            'totals'
        ));

    } // End Method

}
