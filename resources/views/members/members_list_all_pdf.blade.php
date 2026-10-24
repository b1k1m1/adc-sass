<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>All Members List</title>

    <style>
        @page {
            margin: 18px 16px;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8px;
            color: #000;
        }

        .report-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .report-title {
            font-size: 12px;
            font-weight: bold;
        }

        .report-filter {
            font-size: 9px;
            margin-top: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        th {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>

<body>

    <div class="report-header">
        <div class="company-name">
            {{ $company->comp_nm ?? '' }}
        </div>

        <div class="report-title">
            All Members List
        </div>

        <div class="report-filter">
            Status: {{ $statusLabel }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:28px;" class="text-center">
                    Sr#
                </th>

                @foreach($selectedColumns as $field)
                    <th>
                        {{ $availableColumns[$field] ?? $field }}
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse($members as $index => $member)
                <tr>
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    @foreach($selectedColumns as $field)
                        @php
                            $value = $member->{$field} ?? '';

                            $dateFields = [
                                'DateOfBirth',
                                'AdmissionDate',
                                'medicaid_PA_Start',
                                'medicaid_PA_End',
                                'HNP_Expiry_date',
                            ];

                            $phoneFields = [
                                'Phone1',
                                'Phone2',
                            ];

                            $statusNames = [
                                'A' => 'Active',
                                'N' => 'Noon',
                                'H' => 'Hold',
                                'G' => 'Guest',
                                'D' => 'Discharged',
                            ];
                        @endphp

                        <td
                            @if(
                                $field === 'ParticipantId'
                                || in_array($field, $dateFields, true)
                            )
                                class="nowrap"
                            @endif
                        >
                            @if($field === 'ParticipantId')

                                {{ str_pad(
                                    trim((string) $value),
                                    3,
                                    '0',
                                    STR_PAD_LEFT
                                ) }}

                            @elseif($field === 'Trans_flag')

                                {{ $statusNames[trim((string) $value)] ?? $value }}

                            @elseif(
                                in_array($field, $dateFields, true)
                                && !empty($value)
                            )

                                {{ \Carbon\Carbon::parse($value)->format('m/d/Y') }}

                            @elseif(in_array($field, $phoneFields, true))

                                @php
                                    $phone = preg_replace(
                                        '/\D/',
                                        '',
                                        (string) $value
                                    );
                                @endphp

                                @if(strlen($phone) === 10)
                                    {{ '('
                                        . substr($phone, 0, 3)
                                        . ') '
                                        . substr($phone, 3, 3)
                                        . '-'
                                        . substr($phone, 6, 4)
                                    }}
                                @else
                                    {{ $value }}
                                @endif

                            @else

                                {{ $value }}

                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td
                        colspan="{{ count($selectedColumns) + 1 }}"
                        class="text-center"
                    >
                        No members found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
