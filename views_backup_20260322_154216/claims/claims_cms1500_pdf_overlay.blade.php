<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="margin:0; padding:0;">

{{-- This wrapper is the whole Letter page --}}
<div style="position:relative; width:216mm; height:279mm;">

    {{-- TOP RIGHT: plan + payer id --}}
    <div style="
        position:absolute;
        top:12mm;
        left:125mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
        white-space:nowrap;
    ">
        {{ $insurance->name ?? $claim->insurance_name }}
        @if(!empty($insurance?->payer_id))
            [{{ $insurance->payer_id }}]
        @endif
    </div>

    {{-- Box 1: Medicaid X --}}
    <div style="
        position:absolute;
        top:23mm;
        left:33mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        X
    </div>

    {{-- Box 1a: Insured ID --}}
    <div style="
        position:absolute;
        top:23mm;
        left:165mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
        white-space:nowrap;
    ">
        {{ $claim->insurance_id ?? ($member->MedicaidID ?? '') }}
    </div>

    {{-- Box 2: Patient name --}}
    <div style="
        position:absolute;
        top:34mm;
        left:28mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
        white-space:nowrap;
    ">
        {{ trim($member->LastName . ', ' . $member->FirstName . ' ' . ($member->MiddleName ?? '')) }}
    </div>

    {{-- Box 3: DOB --}}
    <div style="
        position:absolute;
        top:34mm;
        left:120mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
        white-space:nowrap;
    ">
        @if($member->DateOfBirth)
            {{ \Carbon\Carbon::parse($member->DateOfBirth)->format('m d Y') }}
        @endif
    </div>

    {{-- Box 3: Sex M --}}
    <div style="
        position:absolute;
        top:34mm;
        left:150mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        @if(strtoupper($member->Sex ?? '') === 'M') X @endif
    </div>

    {{-- Box 3: Sex F --}}
    <div style="
        position:absolute;
        top:34mm;
        left:160mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        @if(strtoupper($member->Sex ?? '') === 'F') X @endif
    </div>

    {{-- Box 5: Address line 1 --}}
    <div style="
        position:absolute;
        top:42mm;
        left:28mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
        white-space:nowrap;
    ">
        {{ trim($member->Address1 . ' ' . ($member->Address2 ?? '')) }}
    </div>

    {{-- Box 5: City/State/Zip --}}
    <div style="
        position:absolute;
        top:47mm;
        left:28mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
        white-space:nowrap;
    ">
        {{ $member->City }}, {{ $member->State }} {{ $member->ZipCode }}
    </div>

    {{-- Box 5: Phone --}}
    <div style="
        position:absolute;
        top:52mm;
        left:28mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
        white-space:nowrap;
    ">
        {{ $member->Phone1 }}
    </div>

    {{-- Box 21: Diagnosis A --}}
    <div style="
        position:absolute;
        top:98mm;
        left:26mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        {{ $claim->diag1 }}
    </div>

    {{-- Box 21: Diagnosis B --}}
    <div style="
        position:absolute;
        top:98mm;
        left:58mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        {{ $claim->diag2 }}
    </div>

    {{-- Box 19: Additional info --}}
    <div style="
        position:absolute;
        top:112mm;
        left:28mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
        white-space:nowrap;
    ">
        DAY CARE SERVICE ADULT
    </div>

    @php
        $from = $claim->service_from ? \Carbon\Carbon::parse($claim->service_from)->format('m d y') : '';
        $to   = $claim->service_to   ? \Carbon\Carbon::parse($claim->service_to)->format('m d y')   : '';
    @endphp

    {{-- Box 24A: From --}}
    <div style="
        position:absolute;
        top:128mm;
        left:22mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        {{ $from }}
    </div>

    {{-- Box 24A: To --}}
    <div style="
        position:absolute;
        top:128mm;
        left:44mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        {{ $to }}
    </div>

    {{-- Box 24B: POS --}}
    <div style="
        position:absolute;
        top:128mm;
        left:64mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        {{ $claim->pos }}
    </div>

    {{-- Box 24D: CPT + modifier --}}
    <div style="
        position:absolute;
        top:128mm;
        left:80mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
        white-space:nowrap;
    ">
        {{ $claim->proc_code }}@if($claim->m1) {{ ' ' . $claim->m1 }}@endif
    </div>

    {{-- Box 24E: Dx pointer --}}
    <div style="
        position:absolute;
        top:128mm;
        left:120mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        A
    </div>

    {{-- Box 24F: Charge --}}
    <div style="
        position:absolute;
        top:128mm;
        left:140mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        {{ number_format($claim->total_amount, 2) }}
    </div>

    {{-- Box 24G: Units --}}
    <div style="
        position:absolute;
        top:128mm;
        left:165mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        {{ $claim->units }}
    </div>

    {{-- Box 28: Total charge --}}
    <div style="
        position:absolute;
        top:163mm;
        left:140mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        {{ number_format($claim->total_amount, 2) }}
    </div>

    {{-- Box 29: Amount paid --}}
    <div style="
        position:absolute;
        top:163mm;
        left:165mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        0.00
    </div>

    {{-- Box 30: Balance due --}}
    <div style="
        position:absolute;
        top:163mm;
        left:185mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        {{ number_format($claim->total_amount, 2) }}
    </div>

    {{-- Box 33: Billing provider name --}}
    <div style="
        position:absolute;
        top:190mm;
        left:28mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
        white-space:nowrap;
    ">
        {{ $company->comp_nm ?? '' }}
    </div>

    {{-- Box 33: Billing provider address --}}
    <div style="
        position:absolute;
        top:195mm;
        left:28mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
        white-space:nowrap;
    ">
        {{ $company->complc_addr1 ?? '' }}
    </div>
    <div style="
        position:absolute;
        top:200mm;
        left:28mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
        white-space:nowrap;
    ">
        {{ ($company->complc_city ?? '') }} {{ ($company->complc_st ?? '') }} {{ ($company->complc_pin ?? '') }}
    </div>

    {{-- Box 33a: NPI --}}
    <div style="
        position:absolute;
        top:190mm;
        left:170mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        {{ $company->npi ?? '' }}
    </div>

    {{-- Box 33b: Tax ID --}}
    <div style="
        position:absolute;
        top:200mm;
        left:170mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        {{ $company->tax_id ?? '' }}
    </div>

    {{-- Taxonomy --}}
    <div style="
        position:absolute;
        top:205mm;
        left:170mm;
        font-size:9pt;
        font-family:'Courier New', monospace;
    ">
        {{ $company->taxonomy_code ?? '' }}
    </div>

</div> {{-- /page --}}

</body>
</html>
