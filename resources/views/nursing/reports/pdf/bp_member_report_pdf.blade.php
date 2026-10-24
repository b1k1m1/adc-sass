<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Member BP / Pulse / Weight Report</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .meta {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
        }

        th {
            background: #f0f0f0;
        }

        .summary td {
            border: none;
            padding: 2px 6px 6px 0;
        }
    </style>
</head>

<body>

    <div class="title">
        Member BP / Pulse / Weight Report
    </div>

    <div class="meta">
        <strong>Member:</strong>
        {{ $member->ParticipantId ?? '' }} -
        {{ $member->LastName ?? '' }},
        {{ $member->FirstName ?? '' }}
        <br>

        <strong>From:</strong> {{ $startDate }}
        &nbsp;&nbsp;&nbsp;

        <strong>To:</strong> {{ $endDate }}
    </div>

    <table class="summary">
        <tr>
            <td>
                <strong>Avg LowBP:</strong>
                {{ $avg->avg_lowbp ?? '' }}
            </td>

            <td>
                <strong>Avg HighBP:</strong>
                {{ $avg->avg_highbp ?? '' }}
            </td>

            <td>
                <strong>Avg Pulse:</strong>
                {{ $avg->avg_pulse ?? '' }}
            </td>

            <td>
                <strong>Avg Weight:</strong>
                {{ isset($avg->avg_weight)
                    ? number_format((float) $avg->avg_weight, 1)
                    : '' }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>LowBP</th>
                <th>HighBP</th>
                <th>Pulse</th>
                <th>Weight</th>
            </tr>
        </thead>

        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>
                        {{ \Carbon\Carbon::parse($row->CalenderDate)->format('m/d/Y') }}
                    </td>

                    <td>{{ $row->LowBP }}</td>

                    <td>{{ $row->HighBP }}</td>

                    <td>{{ $row->Pulse }}</td>

                    <td>
                        {{ $row->Weight !== null
                            ? number_format((float) $row->Weight, 1)
                            : '' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">
                        No records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
