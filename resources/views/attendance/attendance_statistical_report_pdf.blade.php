<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 9px;
        color: #000;
    }

    .report-title {
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        margin-bottom: 4px;
    }

    .report-subtitle {
        text-align: center;
        font-size: 10px;
        margin-bottom: 10px;
    }

    .summary-table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 10px;
    }

    .summary-table td {
        border: 1px solid #999;
        padding: 6px;
        text-align: center;
        background-color: #f5f5f5;
        font-size: 9px;
    }

    .report-table {
        border-collapse: collapse;
        width: 100%;
    }

    .report-table th {
        border: 1px solid #777;
        padding: 4px;
        background-color: #eeeeee;
        font-weight: bold;
        text-align: center;
        font-size: 8px;
    }

    .report-table td {
        border: 1px solid #999;
        padding: 3px;
        font-size: 8px;
        vertical-align: top;
        word-wrap: break-word;
    }
</style>
</head>
<body>

    <div class="report-title">
        Attendance Statistical Report
    </div>

    <div class="report-subtitle">
        {{ $startDateDisplay }} to {{ $endDateDisplay }} |
        {{ $shiftLabel }}
    </div>

    <table class="summary-table">
        <tr>
            <td class="summary-label">Date Range</td>
            <td>{{ $startDateDisplay }} to {{ $endDateDisplay }}</td>

            <td class="summary-label">Shift</td>
            <td>{{ $shiftLabel }}</td>
        </tr>
        <tr>
            <td class="summary-label">Selected Days</td>
            <td>{{ $totalSelectedDays }}</td>

            <td class="summary-label">Members Present</td>
            <td>{{ $totalMembersPresent }}</td>
        </tr>
    </table>

    <div class="section-title">
        Attendance Frequency Summary
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 50%;">Days Present</th>
                <th style="width: 50%;">Members Count</th>
            </tr>
        </thead>
        <tbody>
            @forelse($distribution as $row)
                <tr>
                    <td class="text-center">
                        {{ $row['days_present'] }}
                        {{ $row['days_present'] == 1 ? 'Day' : 'Days' }}
                    </td>
                    <td class="text-center fw-bold">
                        {{ $row['member_count'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center">
                        No attendance found for selected criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>

        @if(count($distribution) > 0)
            <tfoot>
                <tr>
                    <th class="text-center">Total</th>
                    <th class="text-center">{{ $totalMembersPresent }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
    <table class="summary-table" style="width:100%; margin-bottom:10px;">
        <tr>
            <td style="width:25%;">
                <strong>Billable Members:</strong><br>
                {{ $billableMemberCount ?? 0 }}
            </td>
            <td style="width:25%;">
                <strong>Non-Billable Members:</strong><br>
                {{ $nonBillableMemberCount ?? 0 }}
            </td>
            <td style="width:25%;">
                <strong>Billable Attendance Days:</strong><br>
                {{ $billableAttendanceDays ?? 0 }}
            </td>
            <td style="width:25%;">
                <strong>Non-Billable Attendance Days:</strong><br>
                {{ $nonBillableAttendanceDays ?? 0 }}
            </td>
        </tr>
    </table>

    <div class="section-title">
        Member Detail
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 7%;">ID</th>
                <th style="width: 10%;">Last Name</th>
                <th style="width: 10%;">First Name</th>
                <th style="width: 7%;">Shift</th>
                <th style="width: 6%;">Days</th>
                <th style="width: 9%;">Status</th>
                <th style="width: 20%;">Reason</th>
                <th style="width: 10%;">HMO</th>
                <th style="width: 7%;">HMO ID</th>
                <th style="width: 7%;">PA No</th>
                <th style="width: 7%;">PA End</th>
            </tr>
        </thead>
        <tbody>
            @forelse($details as $row)
                 <tr>
                <td>{{ $row['ParticipantId'] }}</td>
                <td>{{ $row['LastName'] }}</td>
                <td>{{ $row['FirstName'] }}</td>

                <td>
                    @if(($row['MemberShift'] ?? '') == 'A')
                        Morning
                    @elseif(($row['MemberShift'] ?? '') == 'N')
                        Noon
                    @else
                        {{ $row['MemberShift'] ?? '' }}
                    @endif
                </td>

                <td style="text-align:center;">
                    {{ $row['days_present'] }}
                </td>

                <td>
                    {{ $row['billing_status'] ?? '' }}
                </td>

                <td>
                    {{ $row['non_billable_reasons'] ?? '' }}
                </td>

                <td>{{ $row['HMO_enrolled'] ?? '' }}</td>
                <td>{{ $row['medicaid_HMO_id'] ?? '' }}</td>
                <td>{{ $row['medicaid_PA_no'] ?? '' }}</td>
                <td>{{ $row['medicaid_PA_End'] ?? '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" style="text-align:center;">
                    No attendance found for selected criteria.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        Printed: {{ now()->format('m/d/Y h:i A') }}
    </div>

</body>
</html>
