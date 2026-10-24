@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <style>
        /* ---- Base ---- */
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 11px; }
        @media print { .no-print { display: none !important; } }

        /* ---- Table ---- */
        .table-wrap { margin-top: 6px; }
        table.report {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 10.5px;
        }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        .report th, .report td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .report th { font-weight: 700; }

        /* Column widths (optimized for A4 landscape via mPDF) */
        .col-id     { width: 42px; }
        .col-num    { width: 34px; }
        .col-last   { width: 140px; text-align: left; }
        .col-first  { width: 140px; text-align: left; }
        .col-street { width: 260px; text-align: left; }
        .col-city   { width: 120px; text-align: left; }
        .col-tele   { width: 120px; }
        .col-time   { width: 120px; }
        .col-init   { width: 80px; }
        .text-left { text-align: left !important; }
        /* Optional zebra for readability (safe for PDF) */
        tbody tr:nth-child(even) { background: #fafafa; }

        /* Header (HTML view only; PDF header is set via mPDF in controller) */
        .header-html { text-align: center; margin-bottom: 8px; }
        .header-html .comp { font-weight: 700; font-size: 16px; }
        .header-html .title { font-weight: 600; font-size: 13px; margin-top: 2px; }
        .header-html .meta { font-size: 11px; margin-top: 2px; }

        /* Controls row */
        .controls { display: flex; gap: 8px; margin-bottom: 10px; }

        /* Compact mode helper (uncomment to make rows tighter)
        .report th, .report td { padding: 3px 5px; font-size: 10px; }
        */
        </style>

        {{-- Controls for browser only --}}
        @if(empty($forPdf) || $forPdf === false)
            <div class="controls no-print">
                <a href="{{ route('driver-pickup.select') }}" class="btn btn-outline-secondary">← Back to Driver Selection</a>
                @isset($pdfUrl)
                    <a href="{{ $pdfUrl }}" class="btn btn-secondary">Export to PDF</a>
                @endisset
                <button onclick="window.print()" class="btn btn-primary">Print</button>
            </div>

            {{-- On-screen header (PDF header is handled by mPDF per your spec) --}}
            <div class="header-html">
                <div class="comp">{{ $company->comp_nm ?? 'Company Name' }}</div>
                <div class="title">{{ $title ?? 'Driver Schedule Pickup' }}</div>
                <div class="meta">
                    @if(!empty($driver))
                        Driver: <strong>{{ $driver->stafflastname ?? '' }}, {{ $driver->stafffirstname ?? '' }}</strong>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                    @endif
                    Date: <strong>{{ \Carbon\Carbon::parse($serviceDate ?? now())->format('m/d/Y') }}</strong>
                </div>
            </div>
        @endif

        <div class="table-wrap">
            <table class="report">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-num">#</th>
                        <th class="col-last">Last</th>
                        <th class="col-first">First</th>
                        <th class="col-street">Street</th>
                        <th class="col-city">City</th>
                        <th class="col-tele">Tele #</th>
                        <th class="col-time">Pick up time<br>at home</th>
                        <th class="col-time">Arrival time<br>at center</th>
                        <th class="col-time">Departure time<br>from center</th>
                        <th class="col-time">Drop off time<br>at home</th>
                        <th class="col-init">Initials</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($rows as $i => $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-left">{{ $r->last }}</td>
                            <td class="text-left">{{ $r->first }}</td>
                            <td class="text-left">{{ $r->street }}</td>
                            <td class="text-left">{{ $r->city }}</td>

                             {{-- US phone format --}}
                        <td>
                            @php
                            $digits = preg_replace('/\D/', '', $r->tele); // strip non-digits
                                if(strlen($digits) === 10){
                                    $formatted = sprintf("(%s) %s %s",
                                    substr($digits,0,3),
                                    substr($digits,3,3),
                                    substr($digits,6));
                                } else {
                                $formatted = $r->tele; // fallback as-is
                                }
                            @endphp
                            {{ $formatted }}
                        </td>

                            <td></td> {{-- Pickup at home --}}
                            <td></td> {{-- Arrival at center --}}
                            <td></td> {{-- Departure from center --}}
                            <td></td> {{-- Drop off at home --}}
                            <td></td> {{-- Initials --}}
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" style="text-align:center;">No records found</td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="12" style="text-align:right; font-size:10px; padding:6px;">
                            Printed: {{ now()->format('m/d/Y h:i A') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
