@php

    $isEdit = !empty($member);

@endphp


<div class="card shadow-sm">

    <div class="card-header">

        <strong>

            {{ $isEdit ? 'Billing Member Information' : 'New Billing Member' }}

        </strong>

    </div>


    <div class="card-body">

        {{-- =========================================================
             BASIC INFORMATION
        ========================================================== --}}

        <h5 class="mb-3">
            Member Information
        </h5>


        <div class="row">

            {{-- Participant ID --}}
            <div class="col-md-2 mb-3">

                <label class="form-label">
                    Participant ID
                    <span class="text-danger">*</span>
                </label>

                <input type="text"
                       name="ParticipantId"
                       maxlength="4"
                       class="form-control"
                       value="{{ old('ParticipantId', $member->ParticipantId ?? '') }}"
                       required>

            </div>


            {{-- Last Name --}}
            <div class="col-md-3 mb-3">

                <label class="form-label">
                    Last Name
                    <span class="text-danger">*</span>
                </label>

                <input type="text"
                       name="LastName"
                       maxlength="15"
                       class="form-control"
                       value="{{ old('LastName', $member->LastName ?? '') }}"
                       required>

            </div>


            {{-- First Name --}}
            <div class="col-md-3 mb-3">

                <label class="form-label">
                    First Name
                    <span class="text-danger">*</span>
                </label>

                <input type="text"
                       name="FirstName"
                       maxlength="15"
                       class="form-control"
                       value="{{ old('FirstName', $member->FirstName ?? '') }}"
                       required>

            </div>


            {{-- Middle Initial --}}
            <div class="col-md-1 mb-3">

                <label class="form-label">
                    MI
                </label>

                <input type="text"
                       name="MiddleName"
                       maxlength="1"
                       class="form-control text-uppercase"
                       value="{{ old('MiddleName', $member->MiddleName ?? '') }}">

            </div>


            {{-- DOB --}}
            <div class="col-md-2 mb-3">

                <label class="form-label">
                    DOB
                    <span class="text-danger">*</span>
                </label>

                <input type="date"
                       name="DateOfBirth"
                       class="form-control"
                       value="{{ old(
                           'DateOfBirth',
                           !empty($member?->DateOfBirth)
                               ? \Carbon\Carbon::parse($member->DateOfBirth)->format('Y-m-d')
                               : ''
                       ) }}"
                       required>

            </div>


            {{-- Sex --}}
            <div class="col-md-1 mb-3">

                <label class="form-label">
                    Sex
                </label>

                <select name="Sex"
                        class="form-select">

                    <option value="">
                    </option>

                    <option value="M"
                        {{ old('Sex', $member->Sex ?? '') == 'M' ? 'selected' : '' }}>
                        M
                    </option>

                    <option value="F"
                        {{ old('Sex', $member->Sex ?? '') == 'F' ? 'selected' : '' }}>
                        F
                    </option>

                </select>

            </div>

        </div>


        {{-- =========================================================
             ADDRESS
        ========================================================== --}}

        <hr>

        <h5 class="mb-3">
            Address
        </h5>


        <div class="row">

            <div class="col-md-3 mb-3">

                <label class="form-label">
                    Address 1
                    <span class="text-danger">*</span>
                </label>

                <input type="text"
                       name="Address1"
                       maxlength="25"
                       class="form-control"
                       value="{{ old('Address1', $member->Address1 ?? '') }}"
                       required>

            </div>


            <div class="col-md-3 mb-3">

                <label class="form-label">
                    Address 2
                </label>

                <input type="text"
                       name="Address2"
                       maxlength="25"
                       class="form-control"
                       value="{{ old('Address2', $member->Address2 ?? '') }}">

            </div>


            <div class="col-md-2 mb-3">

                <label class="form-label">
                    City
                    <span class="text-danger">*</span>
                </label>

                <input type="text"
                       name="City"
                       maxlength="15"
                       class="form-control"
                       value="{{ old('City', $member->City ?? '') }}"
                       required>

            </div>


            <div class="col-md-2 mb-3">

                <label class="form-label">
                    State
                    <span class="text-danger">*</span>
                </label>

                <input type="text"
                       name="State"
                       maxlength="2"
                       class="form-control text-uppercase"
                       value="{{ old('State', $member->State ?? '') }}"
                       required>

            </div>


            <div class="col-md-2 mb-3">

                <label class="form-label">
                    ZIP
                    <span class="text-danger">*</span>
                </label>

                <input type="text"
                       name="ZipCode"
                       maxlength="10"
                       class="form-control"
                       value="{{ old('ZipCode', $member->ZipCode ?? '') }}"
                       required>

            </div>

        </div>


        {{-- =========================================================
             INSURANCE INFORMATION
        ========================================================== --}}

        <hr>

        <h5 class="mb-3">
            Insurance Information
        </h5>


        <div class="row">

            {{-- Current HMO --}}
            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Current HMO / Insurance
                </label>

                <select name="HMO_Enrolled"
                        class="form-select">

                    <option value="">
                        -- Select Insurance --
                    </option>

                    @foreach($insuranceCompanies as $insurance)

                        @php

                            $insuranceCode =
                                $insurance->code
                                ?? '';

                            $insuranceName =
                                $insurance->full_name
                                ?? $insurance->name
                                ?? '';

                        @endphp

                        <option value="{{ $insuranceCode }}"
                            {{
                                old(
                                    'HMO_Enrolled',
                                    $member->HMO_Enrolled ?? ''
                                ) == $insuranceCode
                                    ? 'selected'
                                    : ''
                            }}>

                            {{ $insuranceCode }}

                            @if($insuranceName)

                                - {{ $insuranceName }}

                            @endif

                        </option>

                    @endforeach

                </select>

            </div>


            <div class="col-md-3 mb-3">

                <label class="form-label">
                    Medicaid HMO ID
                </label>

                <input type="text"
                       name="medicaid_HMO_id"
                       maxlength="20"
                       class="form-control"
                       value="{{ old('medicaid_HMO_id', $member->medicaid_HMO_id ?? '') }}">

            </div>


            <div class="col-md-2 mb-3">

                <label class="form-label">
                    Group #
                </label>

                <input type="text"
                       name="medicaid_HMO_grp"
                       maxlength="20"
                       class="form-control"
                       value="{{ old('medicaid_HMO_grp', $member->medicaid_HMO_grp ?? '') }}">

            </div>


            <div class="col-md-3 mb-3">

                <label class="form-label">
                    Medicaid #
                </label>

                <input type="text"
                       name="MedicaidNo"
                       maxlength="15"
                       class="form-control"
                       value="{{ old('MedicaidNo', $member->MedicaidNo ?? '') }}">

            </div>

        </div>


        <div class="row">

            <div class="col-md-3 mb-3">

                <label class="form-label">
                    Medicare #
                </label>

                <input type="text"
                       name="medicare_no"
                       maxlength="20"
                       class="form-control"
                       value="{{ old('medicare_no', $member->medicare_no ?? '') }}">

            </div>


            <div class="col-md-3 mb-3">

                <label class="form-label">
                    New Medicare #
                </label>

                <input type="text"
                       name="new_medicare_no"
                       maxlength="25"
                       class="form-control"
                       value="{{ old('new_medicare_no', $member->new_medicare_no ?? '') }}">

            </div>

        </div>


        {{-- =========================================================
             MEDICAID PA
        ========================================================== --}}

        <hr>

        <h5 class="mb-3">
            Medicaid Prior Authorization
        </h5>


        <div class="row">

            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Medicaid PA #
                </label>

                <input type="text"
                       name="medicaid_PA_no"
                       maxlength="20"
                       class="form-control"
                       value="{{ old('medicaid_PA_no', $member->medicaid_PA_no ?? '') }}">

            </div>


            <div class="col-md-4 mb-3">

                <label class="form-label">
                    PA Start
                </label>

                <input type="date"
                       name="medicaid_PA_Start"
                       class="form-control"
                       value="{{ old(
                           'medicaid_PA_Start',
                           !empty($member?->medicaid_PA_Start)
                               ? \Carbon\Carbon::parse($member->medicaid_PA_Start)->format('Y-m-d')
                               : ''
                       ) }}">

            </div>


            <div class="col-md-4 mb-3">

                <label class="form-label">
                    PA End
                </label>

                <input type="date"
                       name="medicaid_PA_End"
                       class="form-control"
                       value="{{ old(
                           'medicaid_PA_End',
                           !empty($member?->medicaid_PA_End)
                               ? \Carbon\Carbon::parse($member->medicaid_PA_End)->format('Y-m-d')
                               : ''
                       ) }}">

            </div>

        </div>


        {{-- =========================================================
             RENEWAL 1
        ========================================================== --}}

        <hr>

        <h5 class="mb-3">
            Renewal Authorization 1
        </h5>


        <div class="row">

            <div class="col-md-3 mb-3">

                <label class="form-label">
                    Renewal HMO
                </label>

                <select name="renew1_HMO_enrolled"
                        class="form-select">

                    <option value="">
                        -- Select --
                    </option>

                    @foreach($insuranceCompanies as $insurance)

                        @php

                            $insuranceCode =
                                $insurance->code
                                ?? '';

                            $insuranceName =
                                $insurance->full_name
                                ?? $insurance->name
                                ?? '';

                        @endphp

                        <option value="{{ $insuranceCode }}"
                            {{
                                old(
                                    'renew1_HMO_enrolled',
                                    $member->renew1_HMO_enrolled ?? ''
                                ) == $insuranceCode
                                    ? 'selected'
                                    : ''
                            }}>

                            {{ $insuranceCode }}

                            @if($insuranceName)

                                - {{ $insuranceName }}

                            @endif

                        </option>

                    @endforeach

                </select>

            </div>


            <div class="col-md-2 mb-3">

                <label class="form-label">
                    Member ID
                </label>

                <input type="text"
                       name="renew1_HMO_id"
                       maxlength="20"
                       class="form-control"
                       value="{{ old('renew1_HMO_id', $member->renew1_HMO_id ?? '') }}">

            </div>


            <div class="col-md-2 mb-3">

                <label class="form-label">
                    Group #
                </label>

                <input type="text"
                       name="renew1_HMO_grp"
                       maxlength="20"
                       class="form-control"
                       value="{{ old('renew1_HMO_grp', $member->renew1_HMO_grp ?? '') }}">

            </div>


            <div class="col-md-2 mb-3">

                <label class="form-label">
                    PA #
                </label>

                <input type="text"
                       name="renew1_PA_no"
                       maxlength="20"
                       class="form-control"
                       value="{{ old('renew1_PA_no', $member->renew1_PA_no ?? '') }}">

            </div>


            <div class="col-md-1 mb-3">

                <label class="form-label">
                    Start
                </label>

                <input type="date"
                       name="renew1_PA_Start"
                       class="form-control"
                       value="{{ old(
                           'renew1_PA_Start',
                           !empty($member?->renew1_PA_Start)
                               ? \Carbon\Carbon::parse($member->renew1_PA_Start)->format('Y-m-d')
                               : ''
                       ) }}">

            </div>


            <div class="col-md-2 mb-3">

                <label class="form-label">
                    End
                </label>

                <input type="date"
                       name="renew1_PA_End"
                       class="form-control"
                       value="{{ old(
                           'renew1_PA_End',
                           !empty($member?->renew1_PA_End)
                               ? \Carbon\Carbon::parse($member->renew1_PA_End)->format('Y-m-d')
                               : ''
                       ) }}">

            </div>

        </div>


        {{-- =========================================================
             RENEWAL 2
        ========================================================== --}}

        <h5 class="mb-3">
            Renewal Authorization 2
        </h5>


        <div class="row">

            <div class="col-md-3 mb-3">

                <label class="form-label">
                    Renewal HMO
                </label>

                <select name="renew2_HMO_enrolled"
                        class="form-select">

                    <option value="">
                        -- Select --
                    </option>

                    @foreach($insuranceCompanies as $insurance)

                        @php

                            $insuranceCode =
                                $insurance->code
                                ?? '';

                            $insuranceName =
                                $insurance->full_name
                                ?? $insurance->name
                                ?? '';

                        @endphp

                        <option value="{{ $insuranceCode }}"
                            {{
                                old(
                                    'renew2_HMO_enrolled',
                                    $member->renew2_HMO_enrolled ?? ''
                                ) == $insuranceCode
                                    ? 'selected'
                                    : ''
                            }}>

                            {{ $insuranceCode }}

                            @if($insuranceName)

                                - {{ $insuranceName }}

                            @endif

                        </option>

                    @endforeach

                </select>

            </div>


            <div class="col-md-2 mb-3">

                <label class="form-label">
                    Member ID
                </label>

                <input type="text"
                       name="renew2_HMO_id"
                       maxlength="20"
                       class="form-control"
                       value="{{ old('renew2_HMO_id', $member->renew2_HMO_id ?? '') }}">

            </div>


            <div class="col-md-2 mb-3">

                <label class="form-label">
                    Group #
                </label>

                <input type="text"
                       name="renew2_HMO_grp"
                       maxlength="20"
                       class="form-control"
                       value="{{ old('renew2_HMO_grp', $member->renew2_HMO_grp ?? '') }}">

            </div>


            <div class="col-md-2 mb-3">

                <label class="form-label">
                    PA #
                </label>

                <input type="text"
                       name="renew2_PA_no"
                       maxlength="20"
                       class="form-control"
                       value="{{ old('renew2_PA_no', $member->renew2_PA_no ?? '') }}">

            </div>


            <div class="col-md-1 mb-3">

                <label class="form-label">
                    Start
                </label>

                <input type="date"
                       name="renew2_PA_Start"
                       class="form-control"
                       value="{{ old(
                           'renew2_PA_Start',
                           !empty($member?->renew2_PA_Start)
                               ? \Carbon\Carbon::parse($member->renew2_PA_Start)->format('Y-m-d')
                               : ''
                       ) }}">

            </div>


            <div class="col-md-2 mb-3">

                <label class="form-label">
                    End
                </label>

                <input type="date"
                       name="renew2_PA_End"
                       class="form-control"
                       value="{{ old(
                           'renew2_PA_End',
                           !empty($member?->renew2_PA_End)
                               ? \Carbon\Carbon::parse($member->renew2_PA_End)->format('Y-m-d')
                               : ''
                       ) }}">

            </div>

        </div>

        {{-- =========================================================
            DIAGNOSIS
        ========================================================== --}}

        <hr>

        <h5 class="mb-3">
            Diagnosis
        </h5>

        <div class="row">

            {{-- Primary Diagnosis --}}
            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Primary Diagnosis
                </label>

                @php
                    $primaryDiagnosis = old(
                        'PrimaryDiagnosis1',
                        $member->PrimaryDiagnosis1 ?? ''
                    );
                @endphp

                <select name="PrimaryDiagnosis1"
                        id="PrimaryDiagnosis1"
                        class="form-select icd10-select">

                    @if(!empty($primaryDiagnosis))

                        <option value="{{ $primaryDiagnosis }}"
                                selected>

                            {{ $primaryDiagnosis }}

                            @if(
                                isset($diagnosisDescriptions)
                                &&
                                !empty($diagnosisDescriptions[$primaryDiagnosis])
                            )
                                - {{ $diagnosisDescriptions[$primaryDiagnosis] }}
                            @endif

                        </option>

                    @endif

                </select>

            </div>


            {{-- Secondary Diagnosis --}}
            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Secondary Diagnosis
                </label>

                @php
                    $secondaryDiagnosis = old(
                        'SecondaryDiag1',
                        $member->SecondaryDiag1 ?? ''
                    );
                @endphp

                <select name="SecondaryDiag1"
                        id="SecondaryDiag1"
                        class="form-select icd10-select">

                    @if(!empty($secondaryDiagnosis))

                        <option value="{{ $secondaryDiagnosis }}"
                                selected>

                            {{ $secondaryDiagnosis }}

                            @if(
                                isset($diagnosisDescriptions)
                                &&
                                !empty($diagnosisDescriptions[$secondaryDiagnosis])
                            )
                                - {{ $diagnosisDescriptions[$secondaryDiagnosis] }}
                            @endif

                        </option>

                    @endif

                </select>

            </div>

        </div>

        {{-- =========================================================
             PROVIDER
        ========================================================== --}}

        <hr>

        <h5 class="mb-3">
            Physician
        </h5>


        <div class="row">

            <div class="col-md-5 mb-3">

                <label class="form-label">
                    Primary Doctor
                </label>

                <input type="text"
                       name="primary_doctor_nm"
                       maxlength="35"
                       class="form-control"
                       value="{{ old('primary_doctor_nm', $member->primary_doctor_nm ?? '') }}">

            </div>


            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Doctor NPI
                </label>

                <input type="text"
                       name="primary_doctor_NPI"
                       maxlength="35"
                       class="form-control"
                       value="{{ old('primary_doctor_NPI', $member->primary_doctor_NPI ?? '') }}">

            </div>

        </div>


        {{-- =========================================================
             SOURCE / STATUS
        ========================================================== --}}

        <hr>

        <h5 class="mb-3">
            Billing Source
        </h5>


        <div class="row">

            <div class="col-md-3 mb-3">

                <label class="form-label">
                    Shift
                    <span class="text-danger">*</span>
                </label>

                <select name="source_shift"
                        class="form-select"
                        required>

                    <option value="">
                        -- Select --
                    </option>

                    <option value="A"
                        {{ old('source_shift', $member->source_shift ?? '') == 'A' ? 'selected' : '' }}>

                        Morning

                    </option>

                    <option value="N"
                        {{ old('source_shift', $member->source_shift ?? '') == 'N' ? 'selected' : '' }}>

                        Noon

                    </option>

                    <option value="P"
                        {{ old('source_shift', $member->source_shift ?? '') == 'P' ? 'selected' : '' }}>

                        Afternoon

                    </option>

                </select>

            </div>


            <div class="col-md-3 mb-3">

                <label class="form-label">
                    Source System
                </label>

                <select name="source_system"
                        class="form-select">

                    <option value="ADC"
                        {{ old('source_system', $member->source_system ?? 'ADC') == 'ADC' ? 'selected' : '' }}>

                        ADC

                    </option>

                    <option value="ADC_KOR"
                        {{ old('source_system', $member->source_system ?? '') == 'ADC_KOR' ? 'selected' : '' }}>

                        ADC_KOR

                    </option>

                    <option value="MANUAL"
                        {{ old('source_system', $member->source_system ?? '') == 'MANUAL' ? 'selected' : '' }}>

                        Manual

                    </option>

                </select>

            </div>


            <div class="col-md-2 mb-3">

                <label class="form-label d-block">
                    Status
                </label>

                <div class="form-check form-switch mt-2">

                    <input type="checkbox"
                           class="form-check-input"
                           name="active"
                           value="1"
                           {{
                               old(
                                   'active',
                                   $isEdit
                                       ? (int) ($member->active ?? 0)
                                       : 1
                               )
                                   ? 'checked'
                                   : ''
                           }}>

                    <label class="form-check-label">
                        Active
                    </label>

                </div>

            </div>


            <div class="col-md-4 mb-3">

                <label class="form-label">
                    Comments
                </label>

                <input type="text"
                       name="Comments"
                       maxlength="255"
                       class="form-control"
                       value="{{ old('Comments', $member->Comments ?? '') }}">

            </div>

        </div>

    </div>


    {{-- =========================================================
         FOOTER BUTTONS
    ========================================================== --}}

    <div class="card-footer">

        <div class="d-flex justify-content-between">

            <a href="{{ route('billing.members.index') }}"
               class="btn btn-secondary">

                Cancel

            </a>


            <button type="submit"
                    class="btn btn-primary">

                @if($isEdit)

                    Update Billing Member

                @else

                    Save Billing Member

                @endif

            </button>

        </div>

    </div>

</div>
