<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        /* Whole page area – Letter size */
        .page {
            position: relative;
            width: 216mm;
            height: 279mm;
        }
        .field {
            position: absolute;
            font-size: 9pt;
            font-family: "Courier New", monospace;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- TOP RIGHT: plan + payer id (you can tweak) --}}
    <div class="field" style="top: 12mm; left: 125mm;">
        {{ $insurance->name ?? $claim->insurance_name }}
        @if(!empty($insurance?->payer_id))
            [{{ $insurance->payer_id }}]
        @endif
    </div>

    {{-- Box 1: Medicaid X --}}
    <div class="field" style="top: 23mm; left: 33mm;">
        X
    </div>

    {{-- Box 1a: Insured ID --}}
    <div class="field" style="top: 23mm; left: 165mm;">
        {{ $claim->insurance_id ?? ($member->MedicaidID ?? '') }}
    </div>

    {{-- Box 2: Patient name --}}
    <div class="field" style="top: 34mm; left: 28mm;">
        {{ trim($member->LastName . ', ' . $member->FirstName . ' ' . ($member->MiddleName ?? '')) }}
    </div>

    {{-- Box 3: DOB --}}
    <div class="field" style="top: 34mm; left: 120mm;">
        @if($member->DateOfBirth)
            {{ \Carbon\Carbon::parse($member->DateOfBirth)->format('m d Y') }}
        @endif
    </div>

    {{-- Box 3: Sex M/F --}}
    <div class="field" style="top: 34mm; left: 150mm;">
        @if(strtoupper($member->Sex ?? '') === 'M') X @endif
    </div>
    <div class="field" style="top: 34mm; left: 160mm;">
        @if(strtoupper($member->Sex ?? '') === 'F') X @endif
    </div>

    {{-- Box 5: Address --}}
    <div class="field" style="top: 42mm; left: 28mm;">
        {{ trim($member->Address1 . ' ' . ($member->Address2 ?? '')) }}
    </div>
    <div class="field" style="top: 47mm; left: 28mm;">
        {{ $member->City }}, {{ $member->State }} {{ $member->ZipCode }}
    </div>
    <div class="field" style="top: 52mm; left: 28mm;">
        {{ $member->Phone1 }}
    </div>

    {{-- Box 21: Diagnosis A/B (simple) --}}
    <div class="field" style="top: 98mm; left: 26mm;">
        {{ $claim->diag1 }}
    </div>
    <div class="field" style="top: 98mm; left: 58mm;">
        {{ $claim->diag2 }}
    </div>

    {{-- Optional Box 19 text --}}
    <div class="field" style="top: 112mm; left: 28mm;">
        DAY CARE SERVICE ADULT
    </div>

    @php
        $from = $claim->service_from ? \Carbon\Carbon::parse($claim->service_from)->format('m d y') : '';
        $to   = $claim->service_to   ? \Carbon\Carbon::parse($claim->service_to)->format('m d y')   : '';
    @endphp

    {{-- Box 24A: From / To (line 1) --}}
    <div class="field" style="top: 128mm; left: 22mm;">
        {{ $from }}
    </div>
    <div class="field" style="top: 128mm; left: 44mm;">
        {{ $to }}
    </div>

    {{-- Box 24B: POS --}}
    <div class="field" style="top: 128mm; left: 64mm;">
        {{ $claim->pos }}
    </div>

    {{-- Box 24D: CPT + modifier --}}
    <div class="field" style="top: 128mm; left: 80mm;">
        {{ $claim->proc_code }}@if($claim->m1) {{ ' ' . $claim->m1 }}@endif
    </div>

    {{-- Box 24E: Dx pointer --}}
    <div class="field" style="top: 128mm; left: 120mm;">
        A
    </div>

    {{-- Box 24F: Charge --}}
    <div class="field" style="top: 128mm; left: 140mm;">
        {{ number_format($claim->total_amount, 2) }}
    </div>

    {{-- Box 24G: Units --}}
    <div class="field" style="top: 128mm; left: 165mm;">
        {{ $claim->units }}
    </div>

    {{-- Box 28: Total charge --}}
    <div class="field" style="top: 163mm; left: 140mm;">
        {{ number_format($claim->total_amount, 2) }}
    </div>

    {{-- Box 29: Amount paid --}}
    <div class="field" style="top: 163mm; left: 165mm;">
        0.00
    </div>

    {{-- Box 30: Balance due --}}
    <div class="field" style="top: 163mm; left: 185mm;">
        {{ number_format($claim->total_amount, 2) }}
    </div>

    {{-- Box 33: Billing provider (company_info) --}}
    <div class="field" style="top: 190mm; left: 28mm;">
        {{ $company->comp_nm ?? '' }}
    </div>
    <div class="field" style="top: 195mm; left: 28mm;">
        {{ $company->complc_addr1 ?? '' }}
    </div>
    <div class="field" style="top: 200mm; left: 28mm;">
        {{ ($company->complc_city ?? '') }} {{ ($company->complc_st ?? '') }} {{ ($company->complc_pin ?? '') }}
    </div>

    {{-- 33a NPI --}}
    <div class="field" style="top: 190mm; left: 170mm;">
        {{ $company->npi ?? '' }}
    </div>

    {{-- 33b Tax ID --}}
    <div class="field" style="top: 200mm; left: 170mm;">
        {{ $company->tax_id ?? '' }}
    </div>

    {{-- Taxonomy code --}}
    <div class="field" style="top: 205mm; left: 170mm;">
        {{ $company->taxonomy_code ?? '' }}
    </div>

</div>
</body>
</html>
