<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Shift BP / Pulse / Weight Average Report</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
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
    </style>
</head>

<body>

    <div class="title">
        Shift BP / Pulse / Weight Average Report
    </div>

    <div class="meta">
        <strong>Shift:</strong>
        {{ $shift == 'A' ? 'Morning' : 'Noon' }}

        &nbsp;&nbsp;&nbsp;

        <strong>From:</strong>
        {{ $startDate }}

        &nbsp;&nbsp;&nbsp;

        <strong>To:</strong>
        {{ $endDate }}
    </div>

    <table>
        <thead>
            <tr>
                <th>ParticipantId</th>
                <th>LastName</th>
                <th>FirstName</th>
                <th>Avg LowBP</th>
                <th>Avg HighBP</th>
                <th>Avg Pulse</th>
                <th>Avg Weight</th>
            </tr>
        </thead>

        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->ParticipantId }}</td>
                    <td>{{ $row->LastName }}</td>
                    <td>{{ $row->FirstName }}</td>
                    <td>{{ $row->avg_lowbp }}</td>
                    <td>{{ $row->avg_highbp }}</td>
                    <td>{{ $row->avg_pulse }}</td>

                    <td>
                        {{ $row->avg_weight !== null
                            ? number_format((float) $row->avg_weight, 1)
                            : '' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">
                        No records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
