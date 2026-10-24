<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: dejavusans; font-size: 11px; color: #111; }

        .hdr { text-align: center; }
        .cname { font-size: 16px; font-weight: bold; }
        .title { font-size: 13px; font-weight: bold; margin-top: 4px; }

        hr { border: none; border-top: 1px solid #000; margin: 6px 0 10px 0; }

        .box { border: 1px solid #000; padding: 8px; border-radius: 4px; }
        .row { width: 100%; }
        .col { display: inline-block; vertical-align: top; }

        .w-50 { width: 49%; }
        .w-100 { width: 100%; }

        .label { font-weight: bold; }
        .muted { color: #444; }

        table { border-collapse: collapse; width: 100%; margin-top: 6px; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background: #f2f2f2; font-weight: bold; }

        .pre { white-space: pre-wrap; }
        .mt-8 { margin-top: 8px; }
        .mt-12 { margin-top: 12px; }
    </style>
</head>
<body>

    {{-- ===== HEADER (repeats naturally on page break in mPDF) ===== --}}
    <div class="hdr">
        <div class="cname">{{ $company->comp_nm ?? 'Company Name' }}</div>
        <div class="title">PHARMACIST REVIEW</div>
    </div>
    <hr>

    {{-- Meta line --}}
    <div class="muted" style="text-align:center; margin-bottom:8px;">
        Shift: {{ $type === 'A' ? 'Morning (A)' : 'Noon (N)' }}
        &nbsp; | &nbsp;
        Period: {{ $review->period_key }}
        &nbsp; | &nbsp;
        Review Date: {{ \Carbon\Carbon::parse($review->review_date)->format('m/d/Y') }}
    </div>

    {{-- ===== HEADER DETAILS ===== --}}
    <div class="box">
        <div class="row">
            <div class="col w-50">
                <div>
                    <span class="label">Member:</span>
                    {{ $review->participant_id }}
                    @if($member)
                        - {{ $member->LastName }}, {{ $member->FirstName }}
                    @endif
                </div>
                <div class="mt-8">
                    <span class="label">Medical Record No:</span>
                    {{ $review->medical_record_no }}
                </div>
            </div>

            <div class="col w-50">
                <div>
                    <span class="label">Status:</span>
                    {{ ucfirst($review->status ?? 'draft') }}
                </div>
                <div class="mt-8">
                    <span class="label">Period Type:</span>
                    {{ $review->period_type === 'M' ? 'Monthly' : 'Quarterly' }}
                </div>
            </div>
        </div>
    </div>

    {{-- ===== PHARMACIST COMMENTS ===== --}}
    <div class="box mt-12">
        <div class="label">Pharmacist Comments</div>
        <div class="pre mt-8">{{ $review->comments }}</div>
    </div>



</body>
</html>
