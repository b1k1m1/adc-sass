<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Quarterly Assessment Members</title>

    <style>
        @page {
            margin: 24px 20px 28px 20px;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            color: #000;
        }

        .report-header {
            text-align: center;
            margin-bottom: 12px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .report-subtitle {
            font-size: 10px;
            margin-bottom: 2px;
        }

        .report-summary {
            margin-bottom: 8px;
            font-size: 9px;
        }

        .summary-left {
            float: left;
            width: 75%;
        }

        .summary-right {
            float: right;
            width: 25%;
            text-align: right;
        }

        .clear {
            clear: both;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th {
            border: 1px solid #000;
            padding: 5px 4px;
            background-color: #e8e8e8;
            text-align: left;
            font-weight: bold;
        }

        td {
            border: 1px solid #000;
            padding: 5px 4px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .participant-id {
            width: 10%;
        }

        .last-name {
            width: 16%;
        }

        .first-name {
            width: 15%;
        }

        .middle-name {
            width: 13%;
        }

        .admission-date {
            width: 13%;
            white-space: nowrap;
        }

        .remarks {
            width: 33%;
            height: 20px;
        }

        .footer {
            position: fixed;
            bottom: -18px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
        }

        .page-number:after {
            content: counter(page);
        }
    </style>
</head>

<body>

    <div class="footer">
        Page <span class="page-number"></span>
    </div>

    <div class="report-header">

        @if ($company)
            <div class="company-name">
                {{ $company->comp_nm ?? $company->CompanyName ?? $company->organization_name ?? ''}}
            </div>
        @endif

        <div class="report-title">
            Quarterly Assessment Members
        </div>

        <div class="report-subtitle">
            {{ $shiftLabel }} Shift
        </div>

        <div class="report-subtitle">
            Assessment Month: {{ $selectedMonthName }}
        </div>

        <div class="report-subtitle">
            Admission Months Included:
            {{ implode(', ', $quarterMonthNames) }}
        </div>

    </div>

    <div class="report-summary">
        <div class="summary-left">
            Generated:
            {{ now()->format('m/d/Y h:i A') }}
        </div>

        <div class="summary-right">
            Total Members:
            {{ number_format($rows->count()) }}
        </div>

        <div class="clear"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="participant-id">Participant ID</th>
                <th class="last-name">Last Name</th>
                <th class="first-name">First Name</th>
                <th class="middle-name">Middle Name</th>
                <th class="admission-date">Admission Date</th>
                <th class="remarks">Remarks</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>
                        {{ $row->ParticipantId }}
                    </td>

                    <td>
                        {{ $row->LastName }}
                    </td>

                    <td>
                        {{ $row->FirstName }}
                    </td>

                    <td>
                        {{ $row->MiddleName }}
                    </td>

                    <td>
                        {{ $row->AdmissionDate
                            ? \Carbon\Carbon::parse($row->AdmissionDate)->format('m/d/Y')
                            : ''
                        }}
                    </td>

                    <td class="remarks">
                        {{-- Intentionally blank --}}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:15px;">
                        No members were found for the selected criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
