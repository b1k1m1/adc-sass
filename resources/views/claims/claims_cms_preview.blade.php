@extends('admin.admin_dashboard')
@section('title', 'Claims')

@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h4>CMS-1500 Preview – Claim #{{ $claim->claim_no ?? '' }}</h4>
            <div>
                <a href="javascript:window.history.back()" class="btn btn-secondary btn-sm">{{ t('common.back','Back') }}</a>
                <button class="btn btn-primary btn-sm" onclick="window.print()">{{ t('common.print', 'Print') }}</button>
            </div>
        </div>



            <div class="cms1500-wrapper" style="
            position: relative;
            width: 774px;
            height: 1050px;
            margin: 0 auto;
            border: 1px solid #ccc;
            background: url('{{ asset('images/cms1500-blank.png') }}') no-repeat center top;
            background-size: cover;
        ">
        <style>
            .cms-field {
                position: absolute;
                font-size: 11px;
                line-height: 1.1;
                font-family: Arial, sans-serif;
                white-space: nowrap;
            }
        </style>

        {{-- ========== EXAMPLES (YOU WILL ADD MORE BOXES LATER) ========== --}}

        {{-- Box 1: Insurance Program (Medicaid, etc.) --}}
        <div class="cms-field" style="top: 65px; left: 120px;">
            X {{-- you can put an X in the Medicaid checkbox area later --}}
        </div>

        {{-- Box 1a: Insured ID Number (Member ID / Medicaid ID) --}}
        <div class="cms-field" style="top: 65px; left: 630px;">
            {{ $claim->insurance_id }}
        </div>

        {{-- Box 2: PATIENT'S NAME (Last, First) --}}
        <div class="cms-field" style="top: 105px; left: 100px;">
            {{ $member->LastName }}, {{ $member->FirstName }}
        </div>

        {{-- Box 3: PATIENT'S BIRTH DATE & SEX --}}
        <div class="cms-field" style="top: 135px; left: 110px;">
            @if($member->DateOfBirth)
                {{ \Carbon\Carbon::parse($member->DateOfBirth)->format('m d Y') }}
            @endif
        </div>
        <div class="cms-field" style="top: 135px; left: 260px;">
            @if($member->Sex === 'M') X @endif
        </div>
        <div class="cms-field" style="top: 135px; left: 290px;">
            @if($member->Sex === 'F') X @endif
        </div>

        {{-- Box 5: PATIENT ADDRESS --}}
        <div class="cms-field" style="top: 165px; left: 100px;">
            {{ $member->Address1 }} {{ $member->Address2 }}
        </div>
        <div class="cms-field" style="top: 180px; left: 100px;">
            {{ $member->City }}, {{ $member->State }} {{ $member->ZipCode }}
        </div>
        <div class="cms-field" style="top: 195px; left: 100px;">
            {{ $member->Phone1 }}
        </div>

        {{-- Box 11c / 23: Prior Authorization (we’ll show in Box 23) --}}

        {{-- Insurance plan name (Box 11c or top right, etc.) --}}
        <div class="cms-field" style="top: ...; left: ...;">
            {{ $insurance->name ?? $claim->insurance_name }}
        </div>



        {{-- Box 21: Diagnosis Codes (A–L, but we’ll start with A–D) --}}
        <div class="cms-field" style="top: 420px; left: 70px;">{{ $claim->diag1 }}</div> {{-- A --}}
        <div class="cms-field" style="top: 420px; left: 200px;">{{ $claim->diag2 }}</div> {{-- B --}}
        <div class="cms-field" style="top: 420px; left: 330px;"></div> {{-- C --}}
        <div class="cms-field" style="top: 420px; left: 460px;"></div> {{-- D --}}

        {{-- Box 24A: Date(s) of Service (From – To) Line 1 --}}
        <div class="cms-field" style="top: 480px; left: 50px;">
            @if($claim->service_from)
                {{ \Carbon\Carbon::parse($claim->service_from)->format('m d Y') }}
            @endif
        </div>
        <div class="cms-field" style="top: 480px; left: 140px;">
            @if($claim->service_to)
                {{ \Carbon\Carbon::parse($claim->service_to)->format('m d Y') }}
            @endif
        </div>

        {{-- Box 24D: Procedures, Services, or Supplies (CPT/HCPCS) --}}
        <div class="cms-field" style="top: 480px; left: 240px;">
            {{ $claim->proc_code }}
            @if($claim->m1) {{ ' ' . $claim->m1 }} @endif

        </div>

        {{-- Box 24G: Days or Units --}}
        <div class="cms-field" style="top: 480px; left: 435px;">
            {{ $claim->units }}
        </div>

        {{-- Box 24F: Charges --}}
        <div class="cms-field" style="top: 480px; left: 520px;">
            {{ number_format($claim->total_amount, 2) }}
        </div>

        {{-- Box 28: TOTAL CHARGE --}}
        <div class="cms-field" style="top: 730px; left: 520px;">
            {{ number_format($claim->total_amount, 2) }}
        </div>

        {{-- Box 33: BILLING PROVIDER INFO & PH # --}}
        <div class="cms-field" style="top: 820px; left: 80px;">
            {{ $company->comp_nm }}
        </div>
        <div class="cms-field" style="top: 835px; left: 80px;">
            {{ $company->complc_addr1 }}, {{ $company->complc_city }} {{ $company->complc_st }} {{ $company->complc_pin }}
        </div>

        {{-- Box 33a: NPI --}}
        <div class="cms-field" style="top: 820px; left: 540px;">
            {{ $company->npi }}
        </div>

        {{-- Box 33b: Tax ID --}}
        <div class="cms-field" style="top: 835px; left: 540px;">
            {{ $company->tax_id }}
        </div>

        {{-- You will keep adding / adjusting more boxes over time --}}
        </div>

    </div>
</div>
@endsection
