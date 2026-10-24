<table>
    <tr>
        <td colspan="5" style="text-align:center; font-weight:bold; font-size:16px;">
            {{ config('company.comp_nm', '') }}
        </td>
    </tr>
    <tr>
        <td colspan="5" style="text-align:center;">
            {{ config('company.complc_addr1', '') }}
        </td>
    </tr>
    <tr>
        <td colspan="5" style="text-align:center;">
            {{ config('company.complc_addr2', '') }}
        </td>
    </tr>
    <tr>
        <td colspan="5" style="text-align:center;">
            {{ config('company.complc_ph', '') }}
        </td>
    </tr>
    <tr>
        <td colspan="5" style="text-align:center; font-weight:bold;">
            Member Wise Attendance Report
        </td>
    </tr>
    <tr>
        <td colspan="5">
            <strong>Member:</strong> {{ $member->ParticipantId }} - {{ $member->LastName }}, {{ $member->FirstName }}
        </td>
    </tr>
    <tr>
        <td colspan="5">
            <strong>Date Range:</strong> {{ $dateRangeText }}
        </td>
    </tr>
    <tr>
        <td colspan="6"><strong>Total Days Present:</strong> {{ $totalDaysPresent }}</td>
    </tr>
    <tr>
        <td colspan="6"><strong>Total Days in Range:</strong> {{ $totalDaysInRange }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th>Date</th>
        <th>Day</th>
        <th>Attendance</th>
        <th>Insurance Name</th>
        <th>Insurance ID#</th>
        <th>Prior Authorization No.</th>
    </tr>

    @forelse($rows as $row)
        <tr>
            <td>{{ $row->report_date }}</td>
            <td>{{ $row->day_name }}</td>
            <td>{{ $row->attendance_mark }}</td>
            <td>{{ $row->insurance_name }}</td>
            <td>{{ $row->insurance_id }}</td>
            <td>{{ $row->prior_auth_no }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6">No attendance records found for the selected date range.</td>
        </tr>
    @endforelse
</table>
