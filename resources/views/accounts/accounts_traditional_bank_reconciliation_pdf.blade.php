<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <title>
        Traditional Bank Reconciliation
    </title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #000;
        }

        .title {
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitle {
            text-align: center;
            font-size: 11px;
            margin-bottom: 15px;
        }

        .bank-info {
            width: 100%;
            margin-bottom: 15px;
        }

        .bank-info td {
            padding: 2px 3px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            background: #eeeeee;
            border: 1px solid #555;
            padding: 5px;
            margin-top: 12px;
        }

        table.detail {
            width: 100%;
            border-collapse: collapse;
        }

        table.detail th {
            border: 1px solid #777;
            background: #f3f3f3;
            padding: 4px;
            font-weight: bold;
        }

        table.detail td {
            border: 1px solid #999;
            padding: 4px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row td {
            font-weight: bold;
            border-top: 1px solid #000 !important;
        }

        .summary {
            width: 65%;
            margin-left: auto;
            margin-top: 18px;
            border-collapse: collapse;
        }

        .summary td {
            padding: 5px;
        }

        .summary .line td {
            border-top: 1px solid #000;
        }

        .summary .strong td {
            font-weight: bold;
        }

        .summary .difference td {
            border-top: 2px solid #000;
            border-bottom: 3px double #000;
            font-weight: bold;
        }
    </style>

</head>

<body>

    <div class="title">
        Traditional Bank Reconciliation
    </div>

    <div class="subtitle">
        As of
        {{ \Carbon\Carbon::parse($recon_date)->format('F d, Y') }}
    </div>


    <table class="bank-info">

        <tr>

            <td width="15%">
                <strong>Bank:</strong>
            </td>

            <td width="55%">
                {{ $bank->account_name }}
            </td>

            <td width="15%">
                <strong>Account:</strong>
            </td>

            <td width="15%" class="text-right">
                {{ $bank->bank_acct_no ?? '' }}
            </td>

        </tr>

    </table>

    <table style="
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    ">

        <tr>

            <td
                style="
                font-weight: bold;
                padding: 6px 4px;
                border: 1px solid #999;
            ">
                Balance as per Bank Book
            </td>

            <td
                style="
                width: 15%;
                text-align: right;
                font-weight: bold;
                padding: 6px 4px;
                border: 1px solid #999;
            ">
                {{ number_format($book_balance, 2) }}
            </td>

        </tr>

    </table>


    {{-- ==============================================================
         DEPOSITS IN TRANSIT
    ============================================================== --}}

    <div class="section-title">
        Deposits in Transit
    </div>

    <table class="detail">

        <thead>

            <tr>
                <th width="12%">Date</th>
                <th width="10%">Trans #</th>
                <th width="14%">Reference</th>
                <th width="24%">Description / Payee</th>
                <th width="25%">Remarks</th>
                <th width="15%">Amount</th>
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
                        {{ $row->payto_name }}
                    </td>

                    <td>
                        {{ $row->remarks }}
                    </td>

                    <td class="text-right">
                        {{ number_format($row->credit_amt, 2) }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6" class="text-center">
                        No deposits in transit.
                    </td>

                </tr>
            @endforelse

            <tr class="total-row">

                <td colspan="5" class="text-right">
                    Total Deposits in Transit
                </td>

                <td class="text-right">
                    {{ number_format($totalDepositsInTransit, 2) }}
                </td>

            </tr>

        </tbody>

    </table>


    {{-- ==============================================================
         OUTSTANDING PAYMENTS
    ============================================================== --}}

    <div class="section-title">
        Outstanding Payments / Checks
    </div>

    <table class="detail">

        <thead>

            <tr>
                <th width="11%">Date</th>
                <th width="9%">Trans #</th>
                <th width="10%">Check #</th>
                <th width="12%">Reference</th>
                <th width="21%">Payee</th>
                <th width="22%">Remarks</th>
                <th width="15%">Amount</th>
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
                        {{ $row->check_no }}
                    </td>

                    <td>
                        {{ $row->ref_no }}
                    </td>

                    <td>
                        {{ $row->payto_name }}
                    </td>

                    <td>
                        {{ $row->remarks }}
                    </td>

                    <td class="text-right">
                        {{ number_format($row->debit_amt, 2) }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="7" class="text-center">
                        No outstanding payments.
                    </td>

                </tr>
            @endforelse

            <tr class="total-row">

                <td colspan="6" class="text-right">
                    Total Outstanding Payments
                </td>

                <td class="text-right">
                    {{ number_format($totalOutstandingPayments, 2) }}
                </td>

            </tr>

        </tbody>

    </table>


    {{-- ==============================================================
         RECONCILIATION SUMMARY
    ============================================================== --}}

    <div class="section-title">
        Reconciliation Summary
    </div>

    <table class="summary">

        <tr>

            <td>
                Balance as per Bank Book
            </td>

            <td class="text-right">
                {{ number_format($book_balance, 2) }}
            </td>

        </tr>

        <tr>

            <td>
                Add: Outstanding Payments / Checks
            </td>

            <td class="text-right">
                {{ number_format($totalOutstandingPayments, 2) }}
            </td>

        </tr>

        <tr>

            <td>
                Less: Deposits in Transit
            </td>

            <td class="text-right">
                ({{ number_format($totalDepositsInTransit, 2) }})
            </td>

        </tr>

        <tr class="line strong">

            <td>
                Calculated Bank Statement Balance
            </td>

            <td class="text-right">
                {{ number_format($calculatedStatementBalance, 2) }}
            </td>

        </tr>

        <tr>

            <td>
                Actual Bank Statement Closing Balance
            </td>

            <td class="text-right">
                {{ number_format($statement_closing_balance, 2) }}
            </td>

        </tr>

        <tr class="difference">

            <td>
                Difference
            </td>

            <td class="text-right">
                {{ number_format($difference, 2) }}
            </td>

        </tr>

    </table>

</body>

</html>
