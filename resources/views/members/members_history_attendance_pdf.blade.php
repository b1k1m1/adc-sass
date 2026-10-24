@php
    use Carbon\Carbon;

    $companyName = $company['comp_name'] ?? $company['company_name'] ?? config('company.comp_name') ?? '';
    $companyAddress = $company['address'] ?? $company['comp_address'] ?? config('company.address') ?? '';
    $companyPhone = $company['phone'] ?? $company['comp_phone'] ?? config('company.phone') ?? '';
    $companyFax = $company['fax'] ?? $company['comp_fax'] ?? config('company.fax') ?? '';

    $statusLabel = $status === 'H'
        ? 'Hold'
        : ($status === 'D' ? 'Discharged' : 'Hold / Discharged');

    $totalRecords = isset($records) ? $records->count() : 0;
    $uniqueMembers = isset($records) ? $records->pluck('ParticipantId')->unique()->count() : 0;

    function memberStatusLabelPdf($value) {
        return $value === 'H' ? 'Hold' : ($value === 'D' ? 'Discharged' : $value);
    }

    function attendanceShiftLabelPdf($value) {
        return $value === 'A' ? 'Morning' : ($value === 'N' ? 'Noon' : $value);
    }
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance History PDF</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #222;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }

        .company-info {
            font-size: 10px;
            text-align: center;
            line-height: 1.4;
        }

        .report-title {
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        .report-subtitle {
            text-align: center;
            font-size: 10px;
            margin-bottom: 8px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .summary-table td {
            border: 1px solid #999;
            padding: 5px;
            font-size: 10px;
        }

        .summary-label {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table th {
            border: 1px solid #777;
            padding: 5px;
            background-color: #e9ecef;
            font-weight: bold;
            text-align: left;
        }

        .report-table td {
            border: 1px solid #999;
            padding: 4px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .footer-note {
            margin-top: 8px;
            font-size: 9px;
            color: #555;
        }
    </style>
</head>

<body>

<table class="header-table">
    <tr>
        <td>
            <div class="company-name">{{ $companyName }}</div>

            @if(!empty($companyAddress))
                <div class="company-info">{{ $companyAddress }}</div>
            @endif

            @if(!empty($companyPhone) || !empty($companyFax))
                <div class="company-info">
                    @if(!empty($companyPhone))
                        Phone: {{ $companyPhone }}
                    @endif

                    @if(!empty($companyFax))
                        &nbsp;&nbsp; Fax: {{ $companyFax }}
                    @endif
                </div>
            @endif
        </td>
    </tr>
</table>

<div class="report-title">Attendance History Report</div>

<div class="report-subtitle">
    Date Range:
    <strong>{{ $displayStartDate }}</strong>
    to
    <strong>{{ $displayEndDate }}</strong>
</div>

<table class="summary-table">
    <tr>
        <td class="summary-label">Member Status</td>
        <td>{{ $statusLabel }}</td>

        <td class="summary-label">Total Present Days</td>
        <td>{{ $totalRecords }}</td>

        <td class="summary-label">Members Found</td>
        <td>{{ $uniqueMembers }}</td>
    </tr>
</table>

<table class="report-table">
    <thead>
        <tr>
            <th style="width: 9%;">ID</th>
            <th style="width: 18%;">Last Name</th>
            <th style="width: 18%;">First Name</th>
            <th style="width: 13%;">Status</th>
            <th style="width: 13%;">Day/Date</th>
            <th style="width: 12%;" class="text-center">Present</th>
            <th style="width: 17%;">Attendance Shift</th>
        </tr>
    </thead>

    <tbody>
        @forelse($records ?? [] as $row)
            <tr>
                <td>{{ $row->ParticipantId }}</td>
                <td>{{ $row->LastName ?? '' }}</td>
                <td>{{ $row->FirstName ?? '' }}</td>
                <td>{{ memberStatusLabelPdf($row->member_status ?? '') }}</td>
                <td data-order="{{ !empty($row->CalenderDate) ? Carbon::parse($row->CalenderDate)->format('Y-m-d') : '' }}">
                    {{ !empty($row->CalenderDate)
                        ? Carbon::parse($row->CalenderDate)->format('D, m/d/Y')
                        : '' }}
                </td>
                <td class="text-center">{{ ($row->AttendanceYN ?? '') === 'Y' ? 'Yes' : ($row->AttendanceYN ?? '') }}</td>
                <td>{{ attendanceShiftLabelPdf($row->attendance_shift ?? '') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">
                    No attendance records found for selected criteria.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer-note">
    Printed on {{ now()->format('m/d/Y h:i A') }}
</div>

</body>
</html>
