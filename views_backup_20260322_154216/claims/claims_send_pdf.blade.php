<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Claims Batch Summary</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h2, h3 { margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #000; padding: 3px 4px; }
        th { background-color: #f0f0f0; }
        .right { text-align: right; }
        .center { text-align: center; }
        .mt-10 { margin-top: 10px; }
        .mb-5 { margin-bottom: 5px; }
        .border-top { border-top: 2px solid #000; margin-top: 5px; padding-top: 5px; }
    </style>
</head>
<body>

    <h2>Claims Batch Summary</h2>
    <p class="mb-5">
        Batch Date: {{ now()->format('m/d/Y') }}<br>
        Total Claims: {{ $totalClaims }}<br>
        Grand Total Amount: ${{ number_format($grandTotalAmount, 2) }}
    </p>

    @foreach($claimsByInsurance as $insuranceName => $insClaims)
        <div class="mt-10">
            <h3>
                Insurance: {{ $insuranceName ?: 'Unknown / Not Set' }}
                ({{ $insClaims->count() }} claims |
                ${{ number_format($insClaims->sum('total_amount'), 2) }})
            </h3>

            <table>
                <thead>
                <tr>
                    <th>Claim No</th>
                    <th>Participant ID</th>
                    <th>Member Name</th>
                    <th>Service From</th>
                    <th>Service To</th>
                    <th class="right">Units</th>
                    <th class="right">Amount</th>
                </tr>
                </thead>
                <tbody>
                @foreach($insClaims as $claim)
                    <tr>
                        <td>{{ $claim->claim_no }}</td>
                        <td>{{ $claim->ParticipantId }}</td>
                        <td>{{ $claim->member_lastname }}, {{ $claim->member_firstname }}</td>
                        <td>
                            @if($claim->service_from)
                                {{ \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') }}
                            @endif
                        </td>
                        <td>
                            @if($claim->service_to)
                                {{ \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') }}
                            @endif
                        </td>
                        <td class="right">{{ $claim->units ?? 1 }}</td>
                        <td class="right">${{ number_format($claim->total_amount, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <p class="right border-top">
                Subtotal for {{ $insuranceName ?: 'Unknown / Not Set' }}:
                ${{ number_format($insClaims->sum('total_amount'), 2) }}
            </p>
        </div>
    @endforeach

    <hr>
    <p class="right">
        <strong>Grand Total Claims: {{ $totalClaims }}</strong><br>
        <strong>Grand Total Amount: ${{ number_format($grandTotalAmount, 2) }}</strong>
    </p>

</body>
</html>

