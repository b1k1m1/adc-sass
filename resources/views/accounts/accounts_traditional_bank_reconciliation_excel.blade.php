<table>

    {{-- ============================================================
         REPORT HEADER
    ============================================================= --}}

    <tr>
        <td colspan="7"
            style="
                font-size: 16px;
                font-weight: bold;
                text-align: center;
            ">
            Traditional Bank Reconciliation
        </td>
    </tr>

    <tr>
        <td colspan="7"
            style="
                font-size: 12px;
                font-weight: bold;
                text-align: center;
            ">
            As of
            {{ \Carbon\Carbon::parse($recon_date)->format('F d, Y') }}
        </td>
    </tr>

    <tr>
        <td colspan="7"></td>
    </tr>


    {{-- ============================================================
         BANK INFORMATION
    ============================================================= --}}

    <tr>

        <td style="font-weight:bold;">
            Bank:
        </td>

        <td colspan="3">
            {{ $bank->account_name }}
        </td>

        <td style="font-weight:bold;">
            Account:
        </td>

        <td colspan="2">
            {{ $bank->bank_acct_no ?? '' }}
        </td>

    </tr>


    <tr>
        <td colspan="7"></td>
    </tr>


    {{-- ============================================================
         BANK BOOK BALANCE
         
         IMPORTANT:
         Amount is in Column G.
    ============================================================= --}}

    <tr>

        <td colspan="6"
            style="
                font-weight: bold;
                border: 1px solid #000000;
            ">
            Balance as per Bank Book
        </td>

        <td
            style="
                font-weight: bold;
                text-align: right;
                border: 1px solid #000000;
            ">
            {{ $book_balance }}
        </td>

    </tr>


    <tr>
        <td colspan="7"></td>
    </tr>


    {{-- ============================================================
         DEPOSITS IN TRANSIT
    ============================================================= --}}

    <tr>

        <td colspan="7"
            style="
                font-weight: bold;
                background-color: #E7E6E6;
                border: 1px solid #000000;
            ">
            Deposits in Transit
        </td>

    </tr>


    <tr>

        <th style="font-weight:bold;border:1px solid #000000;">
            Date
        </th>

        <th style="font-weight:bold;border:1px solid #000000;">
            Trans #
        </th>

        <th style="font-weight:bold;border:1px solid #000000;">
            Reference
        </th>

        <th style="font-weight:bold;border:1px solid #000000;">
            Check #
        </th>

        <th style="font-weight:bold;border:1px solid #000000;">
            Description / Payee
        </th>

        <th style="font-weight:bold;border:1px solid #000000;">
            Remarks
        </th>

        <th
            style="
                font-weight:bold;
                text-align:right;
                border:1px solid #000000;
            ">
            Amount
        </th>

    </tr>


    @forelse($unclearedDeposits as $row)
        <tr>

            <td style="border:1px solid #CCCCCC;">
                {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
            </td>

            <td style="border:1px solid #CCCCCC;">
                {{ $row->trans_no }}
            </td>

            <td style="border:1px solid #CCCCCC;">
                {{ $row->ref_no }}
            </td>

            <td style="border:1px solid #CCCCCC;">
                {{ $row->check_no }}
            </td>

            <td style="border:1px solid #CCCCCC;">
                {{ $row->payto_name }}
            </td>

            <td style="border:1px solid #CCCCCC;">
                {{ $row->remarks }}
            </td>

            <td
                style="
                    text-align:right;
                    border:1px solid #CCCCCC;
                ">
                {{ $row->credit_amt }}
            </td>

        </tr>

    @empty

        <tr>

            <td colspan="7"
                style="
                    text-align:center;
                    border:1px solid #CCCCCC;
                ">
                No deposits in transit.
            </td>

        </tr>
    @endforelse


    <tr>

        <td colspan="6"
            style="
                font-weight:bold;
                text-align:right;
                border-top:1px solid #000000;
            ">
            Total Deposits in Transit
        </td>

        <td
            style="
                font-weight:bold;
                text-align:right;
                border-top:1px solid #000000;
            ">
            {{ $totalDepositsInTransit }}
        </td>

    </tr>


    <tr>
        <td colspan="7"></td>
    </tr>


    {{-- ============================================================
         OUTSTANDING PAYMENTS
    ============================================================= --}}

    <tr>

        <td colspan="7"
            style="
                font-weight:bold;
                background-color:#E7E6E6;
                border:1px solid #000000;
            ">
            Outstanding Payments / Checks
        </td>

    </tr>


    <tr>

        <th style="font-weight:bold;border:1px solid #000000;">
            Date
        </th>

        <th style="font-weight:bold;border:1px solid #000000;">
            Trans #
        </th>

        <th style="font-weight:bold;border:1px solid #000000;">
            Reference
        </th>

        <th style="font-weight:bold;border:1px solid #000000;">
            Check #
        </th>

        <th style="font-weight:bold;border:1px solid #000000;">
            Payee
        </th>

        <th style="font-weight:bold;border:1px solid #000000;">
            Remarks
        </th>

        <th
            style="
                font-weight:bold;
                text-align:right;
                border:1px solid #000000;
            ">
            Amount
        </th>

    </tr>


    @forelse($unclearedPayments as $row)
        <tr>

            <td style="border:1px solid #CCCCCC;">
                {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
            </td>

            <td style="border:1px solid #CCCCCC;">
                {{ $row->trans_no }}
            </td>

            <td style="border:1px solid #CCCCCC;">
                {{ $row->ref_no }}
            </td>

            <td style="border:1px solid #CCCCCC;">
                {{ $row->check_no }}
            </td>

            <td style="border:1px solid #CCCCCC;">
                {{ $row->payto_name }}
            </td>

            <td style="border:1px solid #CCCCCC;">
                {{ $row->remarks }}
            </td>

            <td
                style="
                    text-align:right;
                    border:1px solid #CCCCCC;
                ">
                {{ $row->debit_amt }}
            </td>

        </tr>

    @empty

        <tr>

            <td colspan="7"
                style="
                    text-align:center;
                    border:1px solid #CCCCCC;
                ">
                No outstanding payments.
            </td>

        </tr>
    @endforelse


    <tr>

        <td colspan="6"
            style="
                font-weight:bold;
                text-align:right;
                border-top:1px solid #000000;
            ">
            Total Outstanding Payments
        </td>

        <td
            style="
                font-weight:bold;
                text-align:right;
                border-top:1px solid #000000;
            ">
            {{ $totalOutstandingPayments }}
        </td>

    </tr>


    <tr>
        <td colspan="7"></td>
    </tr>


    {{-- ============================================================
         RECONCILIATION SUMMARY
    ============================================================= --}}

    <tr>

        <td colspan="7"
            style="
                font-weight:bold;
                background-color:#E7E6E6;
                border:1px solid #000000;
            ">
            Reconciliation Summary
        </td>

    </tr>


    <tr>

        <td colspan="6" style="font-weight:bold;">
            Balance as per Bank Book
        </td>

        <td style="
                font-weight:bold;
                text-align:right;
            ">
            {{ $book_balance }}
        </td>

    </tr>


    <tr>

        <td colspan="6">
            Add: Outstanding Payments / Checks
        </td>

        <td style="text-align:right;">
            {{ $totalOutstandingPayments }}
        </td>

    </tr>


    <tr>

        <td colspan="6">
            Less: Deposits in Transit
        </td>

        <td style="text-align:right;">
            {{ -1 * $totalDepositsInTransit }}
        </td>

    </tr>


    <tr>

        <td colspan="6"
            style="
                font-weight:bold;
                border-top:1px solid #000000;
            ">
            Calculated Bank Statement Balance
        </td>

        <td
            style="
                font-weight:bold;
                text-align:right;
                border-top:1px solid #000000;
            ">
            {{ $calculatedStatementBalance }}
        </td>

    </tr>


    <tr>

        <td colspan="6">
            Actual Bank Statement Closing Balance
        </td>

        <td style="text-align:right;">
            {{ $statement_closing_balance }}
        </td>

    </tr>


    <tr>

        <td colspan="6"
            style="
                font-weight:bold;
                border-top:2px solid #000000;
                border-bottom:3px double #000000;
            ">
            Difference
        </td>

        <td
            style="
                font-weight:bold;
                text-align:right;
                border-top:2px solid #000000;
                border-bottom:3px double #000000;
            ">
            {{ $difference }}
        </td>

    </tr>

</table>
