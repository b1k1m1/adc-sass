<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            color: #000;
        }

        .report-header {
            width: 100%;
            margin-bottom: 8px;
            text-align: center;
        }

        .company-name {
            font-size: 15pt;
            font-weight: bold;
        }

        .report-title {
            margin-top: 3px;
            font-size: 12pt;
            font-weight: bold;
        }

        .shift-name {
            margin-top: 3px;
            font-size: 10pt;
            font-weight: bold;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.report-table thead {
            display: table-header-group;
        }

        table.report-table tr {
            page-break-inside: avoid;
        }

        table.report-table th {
            border: 1px solid #000;
            padding: 5px 3px;
            background-color: #e9ecef;
            font-size: 8.5pt;
            text-align: center;
        }

        table.report-table td {
            border: 1px solid #555;
            padding: 4px 3px;
            font-size: 8.5pt;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="report-header">

        <div class="company-name">
            {{ $companyName }}
        </div>

        <div class="report-title">
            Allergy Report for the month of
            {{ $reportMonthYear }}
        </div>

        <div class="shift-name">
            {{ $shiftName }}
        </div>

    </div>

    <table class="report-table">

        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:10%;">Participant ID</th>
                <th style="width:15%;">Last Name</th>
                <th style="width:15%;">First Name</th>
                <th style="width:5%;">MI</th>
                <th style="width:12%;">Date of Birth</th>
                <th style="width:38%;">Allergy</th>
            </tr>
        </thead>

        <tbody>

            @forelse($members as $member)

                @php
                    $middleInitial = !empty($member->MiddleName)
                        ? strtoupper(
                            substr(
                                trim($member->MiddleName),
                                0,
                                1
                            )
                        )
                        : '';

                    $dateOfBirth = '';

                    if (!empty($member->DateOfBirth)) {
                        try {
                            $dateOfBirth =
                                \Carbon\Carbon::parse(
                                    $member->DateOfBirth
                                )->format('m/d/Y');
                        } catch (\Throwable $e) {
                            $dateOfBirth = '';
                        }
                    }

                    $participantId = str_pad(
                        (string) $member->ParticipantId,
                        3,
                        '0',
                        STR_PAD_LEFT
                    );
                @endphp

                <tr>
                    <td class="text-center">
                        {{ $loop->iteration }}
                    </td>

                    <td class="text-center">
                        {{ $participantId }}
                    </td>

                    <td>
                        {{ $member->LastName }}
                    </td>

                    <td>
                        {{ $member->FirstName }}
                    </td>

                    <td class="text-center">
                        {{ $middleInitial }}
                    </td>

                    <td class="text-center">
                        {{ $dateOfBirth }}
                    </td>

                    <td>
                        {{ $member->allergy }}
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="7" class="text-center">
                        No allergy records found for
                        {{ $shiftName }}.
                    </td>
                </tr>

            @endforelse

        </tbody>
    </table>

</body>
</html>
