<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bank Payment Register</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #000;
        }

        h2, h4 {
            text-align: center;
            margin: 0;
            padding: 0;
        }

        h2 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        h4 {
            font-size: 12px;
            margin-bottom: 10px;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: left;
            border: 1px solid #000;
            padding: 5px;
        }

        td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary {
            margin-bottom: 8px;
            font-size: 11px;
        }

        .total-row td {
            font-weight: bold;
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>

    <h2>Bank Payment Register</h2>

    <h4>
        Bank:
        {{ $bank->account_name ?? $bankId }}
        &nbsp; | &nbsp;
        Period:
        {{ $startDate }}
        to
        {{ $endDate }}
    </h4>

    <div class="summary">
        <strong>Total Records:</strong> {{ $rows->count() }}
        &nbsp;&nbsp;&nbsp;
        <strong>Total Payment:</strong> {{ number_format($rows->sum('debit_amt'), 2) }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Trans No</th>
                <th style="width: 8%;">Trans Date</th>
                <th style="width: 8%;">Party Code</th>
                <th style="width: 17%;">Party Name</th>
                <th style="width: 17%;">Pay To Name</th>
                <th style="width: 9%;">Check No</th>
                <th style="width: 8%;">Check Date</th>
                <th style="width: 9%;">Ref No</th>
                <th style="width: 10%;">Remarks</th>
                <th style="width: 10%;" class="text-end">Debit Amount</th>
            </tr>
        </thead>

        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->trans_no }}</td>

                    <td>
                        {{ $row->trans_dt ? \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') : '' }}
                    </td>

                    <td>{{ $row->party_cd }}</td>
                    <td>{{ $row->party_nm }}</td>
                    <td>{{ $row->payto_name }}</td>
                    <td>{{ $row->check_no }}</td>

                    <td>
                        {{ $row->check_dt ? \Carbon\Carbon::parse($row->check_dt)->format('m/d/Y') : '' }}
                    </td>

                    <td>{{ $row->ref_no }}</td>
                    <td>{{ $row->remarks }}</td>

                    <td class="text-end">
                        {{ number_format($row->debit_amt, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">
                        No payment transactions found for selected criteria.
                    </td>
                </tr>
            @endforelse

            @if($rows->count() > 0)
                <tr class="total-row">
                    <td colspan="9" class="text-end">Total</td>
                    <td class="text-end">
                        {{ number_format($rows->sum('debit_amt'), 2) }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

</body>
</html>
