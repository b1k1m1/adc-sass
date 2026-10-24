<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Member Wise Attendance Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .text-center { text-align: center; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>

    <div class="text-center mb-3">
        <h3 class="mb-1">{{ config('company.comp_nm', '') }}</h3>
        <div>{{ config('company.complc_addr1', '') }}</div>
        <div>{{ config('company.complc_addr2', '') }}</div>
        <div>{{ config('company.complc_ph', '') }}</div>
        <h4 class="mb-1" style="margin-top: 10px;">Member Wise Attendance Report</h4>
        <div>
            <strong>Member:</strong>
            {{ $member->ParticipantId }} - {{ $member->LastName }}, {{ $member->FirstName }}
            <div><strong>Total Days Present:</strong> {{ $totalDaysPresent }}</div>
            <div><strong>Total Days in Range:</strong> {{ $totalDaysInRange }}</div>
        </div>
        <div><strong>Date Range:</strong> {{ $dateRangeText }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 8%;">Day</th>
                <th style="width: 10%;">Attendance</th>
                <th style="width: 25%;">Insurance Name</th>
                <th style="width: 18%;">Insurance ID#</th>
                <th style="width: 27%;">Prior Authorization No.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->report_date }}</td>
                    <td>{{ $row->day_name }}</td>
                    <td class="text-center">{{ $row->attendance_mark }}</td>
                    <td>{{ $row->insurance_name }}</td>
                    <td>{{ $row->insurance_id }}</td>
                    <td>{{ $row->prior_auth_no }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No attendance records found for the selected date range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
