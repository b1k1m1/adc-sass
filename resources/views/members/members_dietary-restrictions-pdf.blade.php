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
            font-size: 8pt;
            text-align: center;
        }

        table.report-table td {
            border: 1px solid #555;
            padding: 4px 3px;
            font-size: 8pt;
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
            Dietary Restriction Report for the month of
            {{ $reportMonthYear }}
        </div>

        <div class="shift-name">
            {{ $shiftName }}
        </div>

    </div>

    <table class="report-table">

        <thead>
            <tr>
                <th style="width:4%;">#</th>
                <th style="width:9%;">Participant ID</th>
                <th style="width:13%;">Last Name</th>
                <th style="width:13%;">First Name</th>
                <th style="width:5%;">MI</th>
                <th style="width:10%;">Date of Birth</th>
                <th style="width:23%;">
                    Diet Order
                </th>
                <th style="width:23%;">
                    Food Preference
                </th>
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
                        {{ $member->dietary_restriction_1 }}
                    </td>

                    <td>
                        {{ $member->dietary_restriction_3 }}
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="8" class="text-center">
                        No members found.
                    </td>
                </tr>

            @endforelse

        </tbody>
    </table>

</body>
</html>
