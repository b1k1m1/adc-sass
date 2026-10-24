<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Claims Batch Summary</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1, h2, h3 { margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #000; padding: 3px 4px; }
        th { background-color: #eee; }
        .right { text-align: right; }
        .ins-block { margin-top: 10px; }
        .ins-header { font-weight: bold; padding: 3px 0; }
    </style>
</head>
<body>
    <h2>Claims Batch Summary</h2>

    <p>
        Batch Time: {{ $batchTimestamp }}<br>
        837P File: {{ $ediFilename }}
    </p>

    @php
        $grouped = collect($claims)->groupBy('insurance_name');
        $grandTotalAmount = 0;
        $grandTotalClaims = 0;
    @endphp

    @foreach($grouped as $insuranceName => $group)
        @php
            $insName   = $insuranceName ?: 'Unknown Insurance';
            $count     = $group->count();
            $sum       = $group->sum('total_amount');
            $grandTotalAmount += $sum;
            $grandTotalClaims += $count;
        @endphp

        <div class="ins-block">
            {{-- Insurance header (like your screen) --}}
            <div class="ins-header">
                {{ $insName }}
                ({{ $count }} claim{{ $count == 1 ? '' : 's' }})
                &nbsp;|&nbsp;
                Total: ${{ number_format($sum, 2) }}
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Claim ID</th>
                        <th>Participant</th>
                        <th>Member Name</th>
                        <th>Insurance</th>
                        <th>Service From</th>
                        <th>Service To</th>
                        <th class="right">Units</th>
                        <th class="right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group as $claim)
                        <tr>
                            <td>{{ $claim->id }}</td>
                            <td>{{ $claim->ParticipantId }}</td>
                            <td>
                                {{ trim(($claim->member_lastname ?? '') . ', ' . ($claim->member_firstname ?? ''), ', ') }}
                            </td>
                            <td>{{ $claim->insurance_name ?? '' }}</td>
                            <td>{{ \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') }}</td>
                            <td class="right">{{ $claim->units }}</td>
                            <td class="right">{{ number_format($claim->total_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <br>

    {{-- Overall grand totals --}}
    <table>
        <tr>
            <th class="right">Grand Total Claims</th>
            <td class="right">{{ $grandTotalClaims }}</td>
        </tr>
        <tr>
            <th class="right">Grand Total Amount</th>
            <td class="right">${{ number_format($grandTotalAmount, 2) }}</td>
        </tr>
    </table>
</body>
</html>
