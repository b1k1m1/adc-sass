<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Member Attendance Crosstab Report</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; }
        .text-center { text-align: center; }
        .text-start { text-align: left; }
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
        <h4 class="mb-1" style="margin-top: 8px;">Member Attendance Crosstab Report</h4>
        <div><strong>Member:</strong> {{ $member->ParticipantId }} - {{ $member->LastName }}, {{ $member->FirstName }}</div>
        <div><strong>Range:</strong> {{ $startYearMonth }} to {{ $endYearMonth }}</div>
        <div><strong>Grand Total:</strong> {{ $grandTotal }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Month</th>
                @for($day = 1; $day <= 31; $day++)
                    <th>{{ $day }}</th>
                @endfor
                <th style="width: 5%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($months as $month)
                <tr>
                    <th class="text-start">{{ $month['month_name'] }}</th>
                    @for($day = 1; $day <= 31; $day++)
                        <td>{{ $month['days'][$day] }}</td>
                    @endfor
                    <th style="text-align: right;">{{ $month['row_total'] }}</th>
                </tr>
            @endforeach
            <tr>
                <th class="text-start">Total</th>
                @for($day = 1; $day <= 31; $day++)
                    <th>{{ $dayTotals[$day] }}</th>
                @endfor
                <th style="text-align: right;">{{ $grandTotal }}</th>
            </tr>
        </tbody>
    </table>

</body>
</html>
