<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>835 ERA - {{ $file->payer_name ?? 'ERA' }}</title>
    <style>
        @page {
            margin: 20px 25px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }
        h1, h2, h3, h4 { margin: 0; padding: 0; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .small { font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 3px 4px; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <h2 class="mb-1">835 ERA Payment Summary</h2>
    <div class="mb-3 small">
        Generated: {{ now()->format('m/d/Y h:i A') }}
    </div>

    {{-- FILE / PAYMENT INFO --}}
    <table>
        <tr>
            <th style="width: 25%;">Payer</th>
            <td>{{ $file->payer_name }}</td>
        </tr>
        <tr>
            <th>File Name</th>
            <td>{{ $file->file_name }}</td>
        </tr>
        <tr>
            <th>Payment Date</th>
            <td>
                @if ($file->payment_date)
                    {{ \Carbon\Carbon::parse($file->payment_date)->format('m/d/Y') }}
                @endif
            </td>
        </tr>
        <tr>
            <th>Payment Amount</th>
            <td>${{ number_format($file->payment_amount ?? 0, 2) }}</td>
        </tr>
        <tr>
            <th>Trace / EFT #</th>
            <td>{{ $file->trace_number }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                {{ ucfirst($file->status ?? 'ready') }}
                @if (!empty($file->status_message))
                    - {{ $file->status_message }}
                @endif
            </td>
        </tr>
    </table>

    {{-- CLAIM-LEVEL DETAILS --}}
    <h3 class="mt-3 mb-1">Claim-Level Details</h3>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 10%;">Member ID</th>
                <th style="width: 14%;">Last Name</th>
                <th style="width: 14%;">First Name</th>
                <th style="width: 12%;">Patient Control #</th>
                <th style="width: 14%;">Payer Claim #</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 8%;" class="text-right">Charge</th>
                <th style="width: 8%;" class="text-right">Paid</th>
                <th style="width: 8%;" class="text-right">Pt Resp</th>
            </tr>
        </thead>
        <tbody>
        @php
            $sumCharge = 0;
            $sumPaid   = 0;
            $sumPR     = 0;
        @endphp

        @foreach ($claims as $i => $c)
            @php
                $charge = $c->total_charge ?? 0;
                $paid   = $c->total_paid ?? 0;
                $pr     = $c->patient_responsibility ?? 0;

                $sumCharge += $charge;
                $sumPaid   += $paid;
                $sumPR     += $pr;

                // Look up member name using the same canonical logic
                $rawId   = $c->member_id ?? '';
                $key     = $canonical ? $canonical($rawId) : trim((string) $rawId);
                $name    = $memberMap[$key] ?? ['last' => '', 'first' => ''];
            @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ trim((string) $c->member_id) }}</td>
                <td>{{ $name['last'] }}</td>
                <td>{{ $name['first'] }}</td>
                <td>{{ $c->patient_control_number }}</td>
                <td>{{ $c->payer_claim_number }}</td>
                <td>{{ $c->claim_status_code }}</td>
                <td class="text-right">{{ number_format($charge, 2) }}</td>
                <td class="text-right">{{ number_format($paid, 2) }}</td>
                <td class="text-right">{{ number_format($pr, 2) }}</td>
            </tr>
        @endforeach

        {{-- TOTAL ROW --}}
        <tr>
            <th colspan="7" class="text-right">Totals</th>
            <th class="text-right">{{ number_format($sumCharge, 2) }}</th>
            <th class="text-right">{{ number_format($sumPaid, 2) }}</th>
            <th class="text-right">{{ number_format($sumPR, 2) }}</th>
        </tr>
        </tbody>
    </table>

    <div class="mt-3 small">
        Note: This report is generated from stored 835 ERA data
        (tables <strong>era_files</strong> and <strong>era_claims</strong>).
    </div>

</body>
</html>
