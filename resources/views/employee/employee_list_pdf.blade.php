<!DOCTYPE html>
<html>
<head>
    <title>Employees Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @php
        $statusText = match($status) {
            'A' => 'Active',
            'D' => 'Discharge',
            'G' => 'Guest',
            'H' => 'Hold',
            'N' => 'Noon',
            default => 'All',
        };
    @endphp
    <h3>{{ $company->comp_nm ?? '' }}</h3>
    <h3></h3>
    <hr>
    <h3>Employees - Status: {{ $statusText ?: 'All' }}</h3>
    <table>
        <thead>
            <tr>
                <th>Sr#</th>
                <th>ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>City</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                function statusLabel($code) {
                return match($code) {
                    'A' => 'Active',
                    'D' => 'Discharge',
                    'G' => 'Guest',
                    'H' => 'Hold',
                    'N' => 'Noon',
                    default => 'No Status',
                };
                }
            @endphp
            @foreach($employees as $key => $e)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ str_pad($e->staffid, 3, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $e->stafflastname }}</td>
                <td>{{ $e->stafffirstname }}</td>
                <td>{{ $e->staffcity }}</td>
                <td>{{  statusLabel($e->trans_flag) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
