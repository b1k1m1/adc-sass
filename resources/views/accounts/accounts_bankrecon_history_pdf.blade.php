<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <title>Bank Reconciliation History</title>

    <style>

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8pt;
            color: #000;
        }

        .report-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .report-title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .bank-name {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .period-line {
            font-size: 9pt;
            margin-bottom: 2px;
        }

        .status-closed {
            font-weight: bold;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        .summary-table td {
            border: 1px solid #777;
            padding: 5px;
        }

        .summary-label {
            font-weight: bold;
            background-color: #eeeeee;
        }

        .section-title {
            font-size: 10pt;
            font-weight: bold;
            background-color: #e9ecef;
            border: 1px solid #777;
            padding: 5px;
            margin-top: 10px;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table.report-table th {
            border: 1px solid #777;
            background-color: #eeeeee;
            padding: 4px;
            font-weight: bold;
            text-align: left;
        }

        table.report-table td {
            border: 1px solid #999;
            padding: 4px;
            vertical-align: top;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
        }

        .center {
            text-align: center;
        }

        .total-row td {
            font-weight: bold;
            background-color: #f4f4f4;
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #666;
        }

        .final-summary {
            width: 55%;
            margin-left: 45%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .final-summary td {
            border: 1px solid #777;
            padding: 5px;
        }

        .final-summary .label {
            font-weight: bold;
            background-color: #eeeeee;
        }

        .final-summary .grand {
            font-weight: bold;
            background-color: #dddddd;
        }

        .difference-zero {
            font-weight: bold;
        }

    </style>

</head>

<body>

@php

    $monthName = \Carbon\Carbon::create(
        (int) $recon->period_year,
        (int) $recon->period_month,
        1
    )->format('F Y');

    $bankName = $bank->account_name
        ?? trim((string) $recon->bank_id);

@endphp


{{-- ================================================================
     REPORT HEADER
================================================================ --}}

<div class="report-header">

    <div class="report-title">
        Bank Reconciliation History Report
    </div>

    <div class="bank-name">
        {{ $bankName }}
        -
        {{ trim((string) $recon->bank_id) }}
    </div>

    <div class="period-line">
        Reconciliation Period:
        <strong>{{ $monthName }}</strong>
    </div>

    <div class="period-line">

        Statement Period:

        {{ \Carbon\Carbon::parse($recon->opening_dt)->format('m/d/Y') }}

        through

        {{ \Carbon\Carbon::parse($recon->closing_dt)->format('m/d/Y') }}

    </div>

    <div class="period-line">

        Status:
        <span class="status-closed">
            {{ $recon->status }}
        </span>

        @if($recon->closed_dt)

            &nbsp;&nbsp;&nbsp;

            Closed:
            {{ \Carbon\Carbon::parse($recon->closed_dt)->format('m/d/Y h:i A') }}

        @endif

    </div>

</div>


{{-- ================================================================
     SUMMARY
================================================================ --}}

<table class="summary-table">

    <tr>

        <td class="summary-label">
            Beginning Balance
        </td>

        <td class="amount">
            {{ number_format((float) $recon->opening_balance, 2) }}
        </td>


        <td class="summary-label">
            Cleared Deposits
        </td>

        <td class="amount">
            {{ number_format((float) $totals['cleared_deposits'], 2) }}
        </td>


        <td class="summary-label">
            Cleared Payments
        </td>

        <td class="amount">
            {{ number_format((float) $totals['cleared_payments'], 2) }}
        </td>

    </tr>


    <tr>

        <td class="summary-label">
            Cleared Balance
        </td>

        <td class="amount">
            {{ number_format((float) $totals['cleared_balance'], 2) }}
        </td>


        <td class="summary-label">
            Statement Ending Balance
        </td>

        <td class="amount">
            {{ number_format((float) $recon->closing_balance, 2) }}
        </td>


        <td class="summary-label">
            Difference
        </td>

        <td class="amount">
            {{ number_format((float) $totals['difference'], 2) }}
        </td>

    </tr>

</table>


{{-- ================================================================
     CLEARED DEPOSITS
================================================================ --}}

<div class="section-title">

    Cleared Deposits

    &nbsp;&nbsp;

    ({{ $totals['cleared_deposit_count'] }} Transactions)

</div>

<table class="report-table">

    <thead>

        <tr>
            <th style="width:9%;">Date</th>
            <th style="width:8%;">Trans #</th>
            <th style="width:9%;">Ref #</th>
            <th style="width:9%;">Check #</th>
            <th style="width:23%;">Description / Payee</th>
            <th style="width:30%;">Remarks</th>
            <th style="width:12%; text-align:right;">Amount</th>
        </tr>

    </thead>

    <tbody>

        @forelse($clearedDeposits as $row)

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

                <td class="amount">
                    {{ number_format((float) $row->credit_amt, 2) }}
                </td>

            </tr>

        @empty

            <tr>
                <td colspan="7" class="no-data">
                    No cleared deposits.
                </td>
            </tr>

        @endforelse


        <tr class="total-row">

            <td colspan="6" class="amount">
                Total Cleared Deposits
            </td>

            <td class="amount">
                {{ number_format((float) $totals['cleared_deposits'], 2) }}
            </td>

        </tr>

    </tbody>

</table>


{{-- ================================================================
     UNCLEARED / OUTSTANDING DEPOSITS
================================================================ --}}

<div class="section-title">

    Outstanding Deposits

    &nbsp;&nbsp;

    ({{ $totals['uncleared_deposit_count'] }} Transactions)

</div>

<table class="report-table">

    <thead>

        <tr>
            <th style="width:9%;">Date</th>
            <th style="width:8%;">Trans #</th>
            <th style="width:9%;">Ref #</th>
            <th style="width:9%;">Check #</th>
            <th style="width:23%;">Description / Payee</th>
            <th style="width:30%;">Remarks</th>
            <th style="width:12%; text-align:right;">Amount</th>
        </tr>

    </thead>

    <tbody>

        @forelse($unclearedDeposits as $row)

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

                <td class="amount">
                    {{ number_format((float) $row->credit_amt, 2) }}
                </td>

            </tr>

        @empty

            <tr>
                <td colspan="7" class="no-data">
                    No outstanding deposits.
                </td>
            </tr>

        @endforelse


        <tr class="total-row">

            <td colspan="6" class="amount">
                Total Outstanding Deposits
            </td>

            <td class="amount">
                {{ number_format((float) $totals['uncleared_deposits'], 2) }}
            </td>

        </tr>

    </tbody>

</table>


{{-- ================================================================
     CLEARED PAYMENTS
================================================================ --}}

<div class="section-title">

    Cleared Payments

    &nbsp;&nbsp;

    ({{ $totals['cleared_payment_count'] }} Transactions)

</div>

<table class="report-table">

    <thead>

        <tr>
            <th style="width:9%;">Date</th>
            <th style="width:8%;">Trans #</th>
            <th style="width:9%;">Ref #</th>
            <th style="width:9%;">Check #</th>
            <th style="width:23%;">Payee</th>
            <th style="width:30%;">Remarks</th>
            <th style="width:12%; text-align:right;">Amount</th>
        </tr>

    </thead>

    <tbody>

        @forelse($clearedPayments as $row)

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

                <td class="amount">
                    {{ number_format((float) $row->debit_amt, 2) }}
                </td>

            </tr>

        @empty

            <tr>
                <td colspan="7" class="no-data">
                    No cleared payments.
                </td>
            </tr>

        @endforelse


        <tr class="total-row">

            <td colspan="6" class="amount">
                Total Cleared Payments
            </td>

            <td class="amount">
                {{ number_format((float) $totals['cleared_payments'], 2) }}
            </td>

        </tr>

    </tbody>

</table>


{{-- ================================================================
     UNCLEARED / OUTSTANDING PAYMENTS
================================================================ --}}

<div class="section-title">

    Outstanding Payments

    &nbsp;&nbsp;

    ({{ $totals['uncleared_payment_count'] }} Transactions)

</div>

<table class="report-table">

    <thead>

        <tr>
            <th style="width:9%;">Date</th>
            <th style="width:8%;">Trans #</th>
            <th style="width:9%;">Ref #</th>
            <th style="width:9%;">Check #</th>
            <th style="width:23%;">Payee</th>
            <th style="width:30%;">Remarks</th>
            <th style="width:12%; text-align:right;">Amount</th>
        </tr>

    </thead>

    <tbody>

        @forelse($unclearedPayments as $row)

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

                <td class="amount">
                    {{ number_format((float) $row->debit_amt, 2) }}
                </td>

            </tr>

        @empty

            <tr>
                <td colspan="7" class="no-data">
                    No outstanding payments.
                </td>
            </tr>

        @endforelse


        <tr class="total-row">

            <td colspan="6" class="amount">
                Total Outstanding Payments
            </td>

            <td class="amount">
                {{ number_format((float) $totals['uncleared_payments'], 2) }}
            </td>

        </tr>

    </tbody>

</table>


{{-- ================================================================
     FINAL RECONCILIATION SUMMARY
================================================================ --}}

<table class="final-summary">

    <tr>

        <td class="label">
            Beginning Balance
        </td>

        <td class="amount">
            {{ number_format((float) $recon->opening_balance, 2) }}
        </td>

    </tr>


    <tr>

        <td class="label">
            Add: Cleared Deposits
        </td>

        <td class="amount">
            {{ number_format((float) $totals['cleared_deposits'], 2) }}
        </td>

    </tr>


    <tr>

        <td class="label">
            Less: Cleared Payments
        </td>

        <td class="amount">
            {{ number_format((float) $totals['cleared_payments'], 2) }}
        </td>

    </tr>


    <tr class="grand">

        <td>
            Cleared Balance
        </td>

        <td class="amount">
            {{ number_format((float) $totals['cleared_balance'], 2) }}
        </td>

    </tr>


    <tr>

        <td class="label">
            Statement Ending Balance
        </td>

        <td class="amount">
            {{ number_format((float) $recon->closing_balance, 2) }}
        </td>

    </tr>


    <tr class="grand">

        <td>
            Difference
        </td>

        <td class="amount">
            {{ number_format((float) $totals['difference'], 2) }}
        </td>

    </tr>


    <tr>

        <td class="label">
            Outstanding Deposits
        </td>

        <td class="amount">
            {{ number_format((float) $totals['uncleared_deposits'], 2) }}
        </td>

    </tr>


    <tr>

        <td class="label">
            Outstanding Payments
        </td>

        <td class="amount">
            {{ number_format((float) $totals['uncleared_payments'], 2) }}
        </td>

    </tr>


    <tr class="grand">

        <td>
            Adjusted Book Balance
        </td>

        <td class="amount">
            {{ number_format((float) $totals['adjusted_book_balance'], 2) }}
        </td>

    </tr>

</table>


</body>
</html>