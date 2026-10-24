<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monitoring Report for Members</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        .title { font-size: 16px; font-weight: bold; margin-bottom: 6px; text-align:center; }
        .meta { margin-bottom: 10px; text-align:center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px 6px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>

    <div class="meta">
        <div style="font-size:16px; font-weight:bold;">{{ $company['name'] ?? '' }}</div>
        <div>{{ $company['address'] ?? '' }}</div>
        <div>
            @if(!empty($company['phone'])) Phone: {{ $company['phone'] }} @endif
            @if(!empty($company['fax'])) &nbsp;&nbsp; Fax: {{ $company['fax'] }} @endif
        </div>
    </div>

    <div class="title">Monitoring Report for Members</div>

    <div style="margin-bottom:10px;">
        <strong>Shift:</strong> {{ $shift == 'A' ? 'Morning' : 'Noon' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>ParticipantId</th>
                <th>LastName</th>
                <th>FirstName</th>
                <th>Blood Sugar</th>
                <th>Blood Pressure</th>
                <th>Weight</th>
                <th>Others</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->ParticipantId }}</td>
                    <td>{{ $row->LastName }}</td>
                    <td>{{ $row->FirstName }}</td>
                    <td>{{ $row->bloodsugar }}</td>
                    <td>{{ $row->bloodpressure }}</td>
                    <td>{{ $row->weight }}</td>
                    <td>{{ $row->others }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
