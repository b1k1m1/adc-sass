<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
        }
        .page {
            position: relative;
            width: 816px;   /* ≈ 8.5" at 96dpi */
            height: 1056px; /* ≈ 11" at 96dpi */
            background-image: url("{{ public_path('images/cms1500-blank.png') }}");
            background-repeat: no-repeat;
            background-position: top left;
            background-size: 100% 100%;
        }
        .field {
        position: absolute;
        font-size: 14px;
        font-weight: bold;
        font-family: Arial, sans-serif;
        white-space: nowrap;
        line-height: 1.2;
    }
    </style>
</head>
<body>
<div class="page">

    {{-- Top-right: plan + payer id --}}
    <div class="field" style="top: 100px; left: 510px;">
        {{ $insurance->name ?? $claim->insurance_name }}
        @if(!empty($insurance?->payer_id))
            [{{ $insurance->payer_id }}]
        @endif
    </div>

    {{-- Box 1: Medicaid X --}}
    <div class="field" style="top: 180px; left: 458px;">X</div>

    {{-- Box 1a: Insured ID --}}
    <div class="field" style="top: 180px; left: 515px;">
        {{ $claim->insurance_id ?? ($member->MedicaidID ?? '') }}
    </div>

    {{-- Box 2: Patient name --}}
    <div class="field" style="top: 210px; left: 35px;">
        {{ trim($member->LastName . ', ' . $member->FirstName . ' ' . ($member->MiddleName ?? '')) }}
    </div>

    <div class="field" style="top: 210px; left: 515px;">
        {{ trim($member->LastName . ', ' . $member->FirstName . ' ' . ($member->MiddleName ?? '')) }}
    </div>

    {{-- Box 3: DOB --}}
    <div class="field" style="top: 210px; left: 329px;">
        @if($member->DateOfBirth)
            {{ \Carbon\Carbon::parse($member->DateOfBirth)->format('m d Y') }}
        @endif
    </div>

    {{-- Box 3: Sex M/F --}}
    <div class="field" style="top: 210px; left: 430px;">
        @if(strtoupper($member->Sex ?? '') === 'M') X @endif
    </div>
    <div class="field" style="top: 210px; left: 479px;">
        @if(strtoupper($member->Sex ?? '') === 'F') X @endif
    </div>

    {{-- Box 5: Address --}}
    <div class="field" style="top: 240px; left: 35px;">
        {{ trim($member->Address1 . ' ' . ($member->Address2 ?? '')) }}
    </div>

    <div class="field" style="top: 240px; left: 515px;">
        {{ trim($member->Address1 . ' ' . ($member->Address2 ?? '')) }}
    </div>

    <div class="field" style="top: 270px; left: 35px;">
        {{ $member->City }}
    </div>
    <div class="field" style="top: 270px; left: 515px;">
        {{ $member->City }}
    </div>
    <div class="field" style="top: 270px; left: 280px;">
        {{ $member->State }}
    </div>
     <div class="field" style="top: 270px; left: 740px;">
        {{ $member->State }}
    </div>
    <div class="field" style="top: 300px; left: 35px;">
        {{ $member->ZipCode }}
    </div>
    <div class="field" style="top: 300px; left: 515px;">
        {{ $member->ZipCode }}
    </div>
    <div class="field" style="top: 300px; left: 172px;">
        {{ $member->Phone1 }}
    </div>
    <div class="field" style="top: 300px; left: 655px;">
        {{ $member->Phone1 }}
    </div>
    <div class="field" style="top: 365px; left: 420px;">X</div>

    <div class="field" style="top: 365px; left: 540px;">
        @if($member->DateOfBirth)
            {{ \Carbon\Carbon::parse($member->DateOfBirth)->format('m d Y') }}
        @endif
    </div>

    {{-- Box 3: Sex M/F --}}
    <div class="field" style="top: 365px; left: 680px;">
        @if(strtoupper($member->Sex ?? '') === 'M') X @endif
    </div>
    <div class="field" style="top: 365px; left: 750px;">
        @if(strtoupper($member->Sex ?? '') === 'F') X @endif
    </div>
    <div class="field" style="top: 395px; left: 420px;">X</div>
    <div class="field" style="top: 425px; left: 420px;">X</div>

    <div class="field" style="top: 457px; left: 573px;">X</div>

    <div class="field" style="top: 510px; left: 100px;">
        SIGNATURE ON FILE
    </div>
    <div class="field" style="top: 510px; left: 565px;">
        SIGNATURE ON FILE
    </div>
    <div class="field" style="top: 627px; left: 426px;">
        0
    </div>
    {{-- Box 21: Dx A/B --}}
    <div class="field" style="top: 640px; left: 50px;">
        {{ $claim->diag1 }}
    </div>
    <div class="field" style="top: 640px; left: 210px;">
        {{ $claim->diag2 }}
    </div>

    <div class="field" style="top: 672px; left: 515px;">
        {{ $claim->prior_auth_no }}
    </div>
    <!--
    {{-- Box 19: Additional info --}}
    <div class="field" style="top: 717px; left: 35px;">
        DAY CARE SERVICE ADULT
    </div>

     <div class="field" style="top: 717px; left: 650px;">
        G2
    </div>

    {{-- Taxonomy --}}
    <div class="field" style="top: 717px; left: 682px;">
        {{ $company->taxonomy_code ?? '' }}
    </div>
    -->
    @php
        // Starting vertical position for first service line row
        $lineStartTop = 731;  // this was your original '731px'
        $lineSpacing  = 32;   // distance between rows; tweak if needed
        $lineforDayCareTop = 717;
    @endphp

    @foreach($lines as $i => $line)
    @if($i >= 6)
        @break  {{-- CMS-1500 only has 6 service lines --}}
    @endif

    @php
        $top = $lineStartTop + ($i * $lineSpacing);

        $lineDay = $lineforDayCareTop + ($i * $lineSpacing) ;

        $from = $line->service_from
            ? \Carbon\Carbon::parse($line->service_from)->format('m d y')
            : '';

        $to   = $line->service_to
            ? \Carbon\Carbon::parse($line->service_to)->format('m d y')
            : '';

        $SignDt   = $line->service_to
            ? \Carbon\Carbon::parse($line->service_to)->format('m/d/y')
            : '';

        // For now, all lines point to diagnosis A (claim->diag1).
        // Later you can make this dynamic per line.
        $dxPointer = 'A';
    @endphp

    <div class="field" style="top: {{ $lineDay }}px; left: 35px;">
        DAY CARE SERVICE ADULT
    </div>

     <div class="field" style="top: {{ $lineDay }}px; left: 650px;">
        G2
    </div>

    {{-- Taxonomy --}}
    <div class="field" style="top: {{ $lineDay }}px; left: 682px;">
        {{ $company->taxonomy_code ?? '' }}
    </div>

    {{-- Box 24A: From / To --}}
    <div class="field" style="top: {{ $top }}px; left: 35px;">{{ $from }}</div>
    <div class="field" style="top: {{ $top }}px; left: 118px;">{{ $to }}</div>

    {{-- Box 24B: POS --}}
    <div class="field" style="top: {{ $top }}px; left: 205px;">
        {{ $line->pos }}
    </div>

    {{-- Box 24D: CPT + modifier --}}
    <div class="field" style="top: {{ $top }}px; left: 275px;">
        {{ $line->proc_code }}
    </div>
    <div class="field" style="top: {{ $top }}px; left: 337px;">
        {{ $line->m1 }}
    </div>

    {{-- Box 24E: Dx pointer --}}
    <div class="field" style="top: {{ $top }}px; left: 465px;">
        {{ $dxPointer }}
    </div>

    {{-- Box 24F: Charge --}}
    <div class="field" style="top: {{ $top }}px; left: 537px;">
        {{ number_format($line->total_amount, 2) }}
    </div>

    {{-- Box 24G: Units --}}
    <div class="field" style="top: {{ $top }}px; left: 632px;">
        {{ $line->units }}
    </div>

    <div class="field" style="top: {{ $top }}px; left: 682px;">
        {{ $company->npi ?? '' }}
    </div>
@endforeach

    {{-- @php
        $from = $claim->service_from ? \Carbon\Carbon::parse($claim->service_from)->format('m d y') : '';
        $to   = $claim->service_to   ? \Carbon\Carbon::parse($claim->service_to)->format('m d y')   : '';
    @endphp--}}
    <!--
    {{-- Box 24A: From / To --}}
    <div class="field" style="top: 731px; left: 35px;">{{ $from }}</div>
    <div class="field" style="top: 731px; left: 118px;">{{ $to }}</div>

    {{-- Box 24B: POS --}}
    <div class="field" style="top: 731px; left: 205px;">
        {{ $claim->pos }}
    </div>

    {{-- Box 24D: CPT + modifier --}}
    <div class="field" style="top: 731px; left: 275px;">
        {{ $claim->proc_code }}
    </div>
    <div class="field" style="top: 731px; left: 337px;">
        {{ $claim->m1 }}
    </div>

    {{-- Box 24E: Dx pointer --}}
    <div class="field" style="top: 731px; left: 465px;">A</div>

    {{-- Box 24F: Charge --}}
    <div class="field" style="top: 731px; left: 538px;">
        {{ number_format($claim->total_amount, 2) }}
    </div>

    {{-- Box 24G: Units --}}
    <div class="field" style="top: 731px; left: 632px;">
        {{ $claim->units }}
    </div>


    <div class="field" style="top: 731px; left: 682px;">
        {{ $company->npi ?? '' }}
    </div>
    -->
    {{-- Box 33b: Tax ID --}}
    <div class="field" style="top: 915px; left: 35px;">
        {{ $company->tax_id ?? '' }}
    </div>
    <div class="field" style="top: 915px; left: 203px;">
        X
    </div>
    <div class="field" style="top: 915px; left: 238px;">
        {{ $claim->claim_no ?? '000001' }}
    </div>
    <div class="field" style="top: 915px; left: 386px">
        X
    </div>

    {{-- Box 28: Total charge --}}
    <div class="field" style="top: 915px; left: 557px;">
        {{ number_format($totalCharge, 2) }}
    </div>

    {{-- Box 29: Amount paid --}}
    <div class="field" style="top: 915px; left: 665px;">0.00</div>

    <div class="field" style="top: 933px;left: 660px;">
        {{ $company->complc_ph ?? '' }}
    </div>

    {{-- Box 33: Billing provider --}}

    <div class="field" style="top: 943px;left: 240px;">
        {{ $company->comp_nm ?? '' }}
    </div>
    <div class="field" style="top: 958px; left: 240px;">
        {{ $company->complc_addr1 ?? '' }}
    </div>
    <div class="field" style="top: 973px; left: 240px;">
        {{ ($company->complc_city ?? '') }},{{ ($company->complc_st ?? '') }} {{ ($company->complc_pin ?? '') }}
    </div>
    <div class="field" style="top: 993px; left: 240px;">
        {{ $company->npi ?? '' }}
    </div>
    // ----------------
    <div class="field" style="top: 945px;left: 515px;">
        {{ $company->comp_nm ?? '' }}
    </div>
    <div class="field" style="top: 960px; left: 515px;">
        {{ $company->complc_addr1 ?? '' }}
    </div>

    <div class="field" style="top: 968px; left: 30px;">
        {{ Str::limit($company->comp_nm ?? '', 22, '') }}
    </div>

    <div class="field" style="top: 993px; left: 150px;">
        {{ $SignDt }}
    </div>

    <div class="field" style="top: 975px; left: 515px;">
        {{ ($company->complc_city ?? '') }},{{ ($company->complc_st ?? '') }} {{ ($company->complc_pin ?? '') }}
    </div>

    {{-- Box 33a: NPI --}}
    <div class="field" style="top: 993px; left: 515px;">
        {{ $company->npi ?? '' }}
    </div>

    {{-- Taxonomy --}}
    <div class="field" style="top: 993px; left: 617px;">
        {{ $company->taxonomy_code ?? '' }}
    </div>

</div>
</body>
</html>
