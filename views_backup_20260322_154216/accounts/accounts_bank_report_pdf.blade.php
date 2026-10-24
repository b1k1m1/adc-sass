<!DOCTYPE html>
<html>
<head>

    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: none; padding: 4px 8px; }
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
        <tr>
            <td>{{ \Carbon\Carbon::parse($start_date)->format('m/d/Y') }}</td>
            <td>Opening Balance</td>
            <td></td>

            <td></td>
            <td style="text-align:right;">{{ number_format($op_debit_amt, 2) }}</td>
            <td style="text-align:right;">{{ number_format($op_credit_amt, 2) }}</td>
        </tr>
        @foreach($rows as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}</td>
                <td>{{ $row->payto_name }}</td>

                <td>{{ $row->ref_no ?? '' }}</td>
                <td>{{ $row->check_no }}</td>
                @if($row->debit_amt > 0)
                <td style="text-align:right;">{{ number_format($row->debit_amt, 2) }}</td>
                @else
                <td></td>
                @endif
                @if($row->credit_amt > 0)
               <td style="text-align:right;">{{ number_format($row->credit_amt, 2) }}</td>
                @else
                <td></td>
                @endif

            </tr>
        @endforeach
        <tr>
            <td>{{ \Carbon\Carbon::parse($end_date)->format('m/d/Y') }}</td>
            <td>Balance C/f</td>
            <td></td>

            <td></td>
            <td style="text-align:right;">{{ number_format($cl_debit_amt, 2) }}</td>
            <td style="text-align:right;">{{ number_format($cl_credit_amt, 2) }}</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4" class="text-end">Totals</th>

            <th style="text-align:right;">{{ number_format($totdr, 2) }}</th>
            <th style="text-align:right;">{{ number_format($totcr, 2) }}</th>
        </tr>
    </tfoot>
</table>
</body>
</html>
