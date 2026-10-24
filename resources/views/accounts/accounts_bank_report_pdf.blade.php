<!DOCTYPE html>
<html>

<head>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: none;
            padding: 4px 8px;
        }

        .amount {
            text-align: right;
        }

        .total-row th {
            border-top: 1px solid #333;
            border-bottom: 3px double #333;
            padding-top: 6px;
            padding-bottom: 6px;
        }
    </style>

</head>

<body>

    <table>

        <thead>

            <tr>
                <td colspan="6">
                    <hr style="margin:8px 0 4px 0; border:1px solid #333;">
                </td>
            </tr>

            <tr>
                <th>Date</th>
                <th>Particulars</th>
                <th>Ref No</th>
                <th>Check #</th>
                <th style="text-align:right;">Debit Amt</th>
                <th style="text-align:right;">Credit Amt</th>
            </tr>

            <tr>
                <td colspan="6">
                    <hr style="margin:0 0 4px 0; border:1px solid #333;">
                </td>
            </tr>

        </thead>

        <tbody>

            {{-- Opening Balance --}}
            <tr>

                <td>
                    {{ \Carbon\Carbon::parse($start_date)->format('m/d/Y') }}
                </td>

                <td>
                    Opening Balance
                </td>

                <td></td>

                <td></td>

                <td class="amount">
                    @if ($op_debit_amt > 0)
                        {{ number_format($op_debit_amt, 2) }}
                    @endif
                </td>

                <td class="amount">
                    @if ($op_credit_amt > 0)
                        {{ number_format($op_credit_amt, 2) }}
                    @endif
                </td>

            </tr>


            {{-- Detail Transactions --}}
            @foreach ($rows as $row)
                <tr>

                    <td>
                        {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
                    </td>

                    <td>
                        {{ $row->payto_name }}
                    </td>

                    <td>
                        {{ $row->ref_no ?? '' }}
                    </td>

                    <td>
                        {{ $row->check_no }}
                    </td>

                    <td class="amount">

                        @if ($row->debit_amt > 0)
                            {{ number_format($row->debit_amt, 2) }}
                        @endif

                    </td>

                    <td class="amount">

                        @if ($row->credit_amt > 0)
                            {{ number_format($row->credit_amt, 2) }}
                        @endif

                    </td>

                </tr>
            @endforeach


            {{-- Subtotal before Balance C/F --}}
            <tr>

                <td colspan="4"></td>

                <td class="amount" style="border-top:1px solid #333; font-weight:bold;">
                    {{ number_format($subtotal_debit, 2) }}
                </td>

                <td class="amount" style="border-top:1px solid #333; font-weight:bold;">
                    {{ number_format($subtotal_credit, 2) }}
                </td>

            </tr>


            {{-- Balance C/F --}}
            <tr>

                <td>
                    {{ \Carbon\Carbon::parse($end_date)->format('m/d/Y') }}
                </td>

                <td>
                    Balance C/f
                </td>

                <td></td>

                <td></td>

                <td class="amount" style="font-weight:bold">

                    @if ($cl_debit_amt > 0)
                        {{ number_format($cl_debit_amt, 2) }}
                    @endif

                </td>

                <td class="amount" style="font-weight:bold">

                    @if ($cl_credit_amt > 0)
                        {{ number_format($cl_credit_amt, 2) }}
                    @endif

                </td>

            </tr>

        </tbody>


        <tfoot>

            <tr class="total-row">

                <th colspan="4" style="text-align:right;">
                    Totals
                </th>

                <th class="amount">
                    {{ number_format($grand_debit, 2) }}
                </th>

                <th class="amount">
                    {{ number_format($grand_credit, 2) }}
                </th>

            </tr>

        </tfoot>

    </table>

</body>

</html>
