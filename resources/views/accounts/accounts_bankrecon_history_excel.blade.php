<table>

    {{-- =============================================================
         HEADER
    ============================================================== --}}

    <tr>
        <td colspan="7"
            style="
                font-size:16px;
                font-weight:bold;
                text-align:center;
            ">
            Bank Reconciliation History Report
        </td>
    </tr>


    <tr>

        <td colspan="7"
            style="
                font-size:12px;
                font-weight:bold;
                text-align:center;
            ">

            {{ $bank->account_name ?? trim((string) $recon->bank_id) }}

            -

            {{ trim((string) $recon->bank_id) }}

        </td>

    </tr>


    <tr>

        <td colspan="7"
            style="text-align:center;">

            Period:

            {{ \Carbon\Carbon::create(
                (int) $recon->period_year,
                (int) $recon->period_month,
                1
            )->format('F Y') }}

        </td>

    </tr>


    <tr>

        <td colspan="7"
            style="text-align:center;">

            Statement Period:

            {{ \Carbon\Carbon::parse($recon->opening_dt)->format('m/d/Y') }}

            through

            {{ \Carbon\Carbon::parse($recon->closing_dt)->format('m/d/Y') }}

        </td>

    </tr>


    <tr>

        <td colspan="7"
            style="text-align:center;">

            Status:
            {{ $recon->status }}

            @if($recon->closed_dt)

                &nbsp;&nbsp;

                Closed:
                {{ \Carbon\Carbon::parse($recon->closed_dt)->format('m/d/Y h:i A') }}

            @endif

        </td>

    </tr>


    <tr>
        <td colspan="7"></td>
    </tr>


    {{-- =============================================================
         RECONCILIATION SUMMARY
    ============================================================== --}}

    <tr>

        <td colspan="7"
            style="
                font-weight:bold;
                background-color:#D9EAF7;
            ">
            Reconciliation Summary
        </td>

    </tr>


    <tr>

        <td style="font-weight:bold;">
            Beginning Balance
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $recon->opening_balance, 2, '.', '') }}
        </td>


        <td style="font-weight:bold;">
            Cleared Deposits
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $totals['cleared_deposits'], 2, '.', '') }}
        </td>


        <td style="font-weight:bold;">
            Cleared Payments
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $totals['cleared_payments'], 2, '.', '') }}
        </td>

        <td></td>

    </tr>


    <tr>

        <td style="font-weight:bold;">
            Cleared Balance
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $totals['cleared_balance'], 2, '.', '') }}
        </td>


        <td style="font-weight:bold;">
            Statement Ending Balance
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $recon->closing_balance, 2, '.', '') }}
        </td>


        <td style="font-weight:bold;">
            Difference
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $totals['difference'], 2, '.', '') }}
        </td>

        <td></td>

    </tr>


    <tr>
        <td colspan="7"></td>
    </tr>


    {{-- =============================================================
         CLEARED DEPOSITS
    ============================================================== --}}

    <tr>

        <td colspan="7"
            style="
                font-weight:bold;
                background-color:#E2F0D9;
            ">

            Cleared Deposits
            -
            {{ $totals['cleared_deposit_count'] }}
            Transactions

        </td>

    </tr>


    <tr style="font-weight:bold;background-color:#EEEEEE;">

        <td>Date</td>
        <td>Trans #</td>
        <td>Ref #</td>
        <td>Check #</td>
        <td>Description / Payee</td>
        <td>Remarks</td>
        <td>Amount</td>

    </tr>


    @foreach($clearedDeposits as $row)

        <tr>

            <td>
                {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
            </td>

            <td>
                {{ $row->trans_no }}
            </td>

            <td>
                {{ $row->ref_no }}
            </td>

            <td>
                {{ $row->check_no }}
            </td>

            <td>
                {{ $row->payto_name }}
            </td>

            <td>
                {{ $row->remarks }}
            </td>

            <td style="text-align:right;">
                {{ number_format((float) $row->credit_amt, 2, '.', '') }}
            </td>

        </tr>

    @endforeach


    <tr style="font-weight:bold;">

        <td colspan="6"
            style="text-align:right;">
            Total Cleared Deposits
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $totals['cleared_deposits'], 2, '.', '') }}
        </td>

    </tr>


    <tr>
        <td colspan="7"></td>
    </tr>


    {{-- =============================================================
         OUTSTANDING DEPOSITS
    ============================================================== --}}

    <tr>

        <td colspan="7"
            style="
                font-weight:bold;
                background-color:#FFF2CC;
            ">

            Outstanding Deposits
            -
            {{ $totals['uncleared_deposit_count'] }}
            Transactions

        </td>

    </tr>


    <tr style="font-weight:bold;background-color:#EEEEEE;">

        <td>Date</td>
        <td>Trans #</td>
        <td>Ref #</td>
        <td>Check #</td>
        <td>Description / Payee</td>
        <td>Remarks</td>
        <td>Amount</td>

    </tr>


    @foreach($unclearedDeposits as $row)

        <tr>

            <td>
                {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
            </td>

            <td>
                {{ $row->trans_no }}
            </td>

            <td>
                {{ $row->ref_no }}
            </td>

            <td>
                {{ $row->check_no }}
            </td>

            <td>
                {{ $row->payto_name }}
            </td>

            <td>
                {{ $row->remarks }}
            </td>

            <td style="text-align:right;">
                {{ number_format((float) $row->credit_amt, 2, '.', '') }}
            </td>

        </tr>

    @endforeach


    <tr style="font-weight:bold;">

        <td colspan="6"
            style="text-align:right;">
            Total Outstanding Deposits
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $totals['uncleared_deposits'], 2, '.', '') }}
        </td>

    </tr>


    <tr>
        <td colspan="7"></td>
    </tr>


    {{-- =============================================================
         CLEARED PAYMENTS
    ============================================================== --}}

    <tr>

        <td colspan="7"
            style="
                font-weight:bold;
                background-color:#E2F0D9;
            ">

            Cleared Payments
            -
            {{ $totals['cleared_payment_count'] }}
            Transactions

        </td>

    </tr>


    <tr style="font-weight:bold;background-color:#EEEEEE;">

        <td>Date</td>
        <td>Trans #</td>
        <td>Ref #</td>
        <td>Check #</td>
        <td>Payee</td>
        <td>Remarks</td>
        <td>Amount</td>

    </tr>


    @foreach($clearedPayments as $row)

        <tr>

            <td>
                {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
            </td>

            <td>
                {{ $row->trans_no }}
            </td>

            <td>
                {{ $row->ref_no }}
            </td>

            <td>
                {{ $row->check_no }}
            </td>

            <td>
                {{ $row->payto_name }}
            </td>

            <td>
                {{ $row->remarks }}
            </td>

            <td style="text-align:right;">
                {{ number_format((float) $row->debit_amt, 2, '.', '') }}
            </td>

        </tr>

    @endforeach


    <tr style="font-weight:bold;">

        <td colspan="6"
            style="text-align:right;">
            Total Cleared Payments
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $totals['cleared_payments'], 2, '.', '') }}
        </td>

    </tr>


    <tr>
        <td colspan="7"></td>
    </tr>


    {{-- =============================================================
         OUTSTANDING PAYMENTS
    ============================================================== --}}

    <tr>

        <td colspan="7"
            style="
                font-weight:bold;
                background-color:#FFF2CC;
            ">

            Outstanding Payments
            -
            {{ $totals['uncleared_payment_count'] }}
            Transactions

        </td>

    </tr>


    <tr style="font-weight:bold;background-color:#EEEEEE;">

        <td>Date</td>
        <td>Trans #</td>
        <td>Ref #</td>
        <td>Check #</td>
        <td>Payee</td>
        <td>Remarks</td>
        <td>Amount</td>

    </tr>


    @foreach($unclearedPayments as $row)

        <tr>

            <td>
                {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
            </td>

            <td>
                {{ $row->trans_no }}
            </td>

            <td>
                {{ $row->ref_no }}
            </td>

            <td>
                {{ $row->check_no }}
            </td>

            <td>
                {{ $row->payto_name }}
            </td>

            <td>
                {{ $row->remarks }}
            </td>

            <td style="text-align:right;">
                {{ number_format((float) $row->debit_amt, 2, '.', '') }}
            </td>

        </tr>

    @endforeach


    <tr style="font-weight:bold;">

        <td colspan="6"
            style="text-align:right;">
            Total Outstanding Payments
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $totals['uncleared_payments'], 2, '.', '') }}
        </td>

    </tr>


    <tr>
        <td colspan="7"></td>
    </tr>


    {{-- =============================================================
         FINAL SUMMARY
    ============================================================== --}}

    <tr>

        <td colspan="5"></td>

        <td style="font-weight:bold;background-color:#EEEEEE;">
            Beginning Balance
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $recon->opening_balance, 2, '.', '') }}
        </td>

    </tr>


    <tr>

        <td colspan="5"></td>

        <td style="font-weight:bold;">
            Add: Cleared Deposits
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $totals['cleared_deposits'], 2, '.', '') }}
        </td>

    </tr>


    <tr>

        <td colspan="5"></td>

        <td style="font-weight:bold;">
            Less: Cleared Payments
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $totals['cleared_payments'], 2, '.', '') }}
        </td>

    </tr>


    <tr>

        <td colspan="5"></td>

        <td style="font-weight:bold;background-color:#D9EAF7;">
            Cleared Balance
        </td>

        <td style="font-weight:bold;text-align:right;background-color:#D9EAF7;">
            {{ number_format((float) $totals['cleared_balance'], 2, '.', '') }}
        </td>

    </tr>


    <tr>

        <td colspan="5"></td>

        <td style="font-weight:bold;">
            Statement Ending Balance
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $recon->closing_balance, 2, '.', '') }}
        </td>

    </tr>


    <tr>

        <td colspan="5"></td>

        <td style="font-weight:bold;background-color:#D9EAF7;">
            Difference
        </td>

        <td style="font-weight:bold;text-align:right;background-color:#D9EAF7;">
            {{ number_format((float) $totals['difference'], 2, '.', '') }}
        </td>

    </tr>


    <tr>

        <td colspan="5"></td>

        <td style="font-weight:bold;">
            Outstanding Deposits
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $totals['uncleared_deposits'], 2, '.', '') }}
        </td>

    </tr>


    <tr>

        <td colspan="5"></td>

        <td style="font-weight:bold;">
            Outstanding Payments
        </td>

        <td style="text-align:right;">
            {{ number_format((float) $totals['uncleared_payments'], 2, '.', '') }}
        </td>

    </tr>


    <tr>

        <td colspan="5"></td>

        <td style="font-weight:bold;background-color:#D9EAD3;">
            Adjusted Book Balance
        </td>

        <td style="font-weight:bold;text-align:right;background-color:#D9EAD3;">
            {{ number_format((float) $totals['adjusted_book_balance'], 2, '.', '') }}
        </td>

    </tr>

</table>