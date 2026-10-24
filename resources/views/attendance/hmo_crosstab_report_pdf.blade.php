<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>HMO Attendance Crosstab Report</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; }
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .text-end { text-align: right; }
        .mb-1 { margin-bottom: 4px; }
        .mb-3 { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 3px; text-align: center; }
    </style>
</head>
<body>

    <div class="text-center mb-3">
        <h3 class="mb-1">{{ config('company.company_name', 'Company Name') }}</h3>
        <div>{{ config('company.company_address', '') }}</div>
        <div>{{ config('company.company_city_state_zip', '') }}</div>
        <div>{{ config('company.company_phone', '') }}</div>
        <h4 class="mb-1" style="margin-top: 8px;">HMO Attendance Crosstab Report</h4>
        <div><strong>HMO:</strong> {{ $selectedHmo }}</div>
        <div><strong>Date Range:</strong> {{ $startDateInput }} - {{ $endDateInput }}</div>
        <div><strong>Grand Total:</strong> {{ $grandTotal }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">ParticipantId</th>
                <th style="width: 12%;">LastName</th>
                <th style="width: 12%;">FirstName</th>
                @foreach($dateHeaders as $hdr)
                    <th>{{ $hdr['day_no'] }}</th>
                @endforeach
                <th style="width: 5%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td class="text-start">{{ $row['ParticipantId'] }}</td>
                    <td class="text-start">{{ $row['LastName'] }}</td>
                    <td class="text-start">{{ $row['FirstName'] }}</td>
                    @foreach($row['days'] as $val)
                        <td>{{ $val }}</td>
                    @endforeach
                    <th style="text-align: right;">{{ $row['row_total'] }}</th>
                </tr>
            @endforeach
            <tr>
                <th colspan="3" style="text-align: right;">Total</th>
                @foreach($columnTotals as $total)
                    <th>{{ $total }}</th>
                @endforeach
                <th style="text-align: right;">{{ $grandTotal }}</th>
            </tr>
        </tbody>
    </table>

</body>
</html>
