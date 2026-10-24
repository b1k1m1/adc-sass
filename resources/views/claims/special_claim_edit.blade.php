@extends('admin.admin_dashboard')

@section('title', 'Special / Resubmit Claim')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="card shadow-sm">

            <div class="card-header d-flex justify-content-between align-items-center">

                <h5 class="mb-0">
                    Special / Resubmit Claim
                </h5>

                <a href="{{ route('claims.special.index') }}"
                   class="btn btn-secondary btn-sm">
                    Back
                </a>

            </div>

            <div class="card-body">

                @if(session('success'))

                    <div class="alert alert-success py-2">
                        {{ session('success') }}
                    </div>

                @endif


                @if($errors->any())

                    <div class="alert alert-danger py-2">

                        <strong>Please correct the following:</strong>

                        <ul class="mb-0 mt-1">

                            @foreach($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif


                {{-- ============================================================
                     ORIGINAL CLAIM INFORMATION
                     ============================================================ --}}

                <div class="alert alert-secondary py-2">
                    <strong>Original Claim:</strong>
                    {{ $claim->claim_no }}

                    &nbsp;&nbsp;

                    <strong>Participant:</strong>
                    {{ $claim->ParticipantId }}

                    &nbsp;&nbsp;

                    <strong>Status:</strong>
                    {{ $claim->status ?? '' }}
                </div>


                <form method="POST"
                      action="{{ route('claims.special.store') }}"
                      id="specialClaimForm"
                      novalidate
                      onsubmit="alert('Form is submitting');">

                    @csrf


                    {{-- ============================================================
                         SPECIAL CLAIM TYPE
                         ============================================================ --}}

                    <div class="row mb-3">

                        <div class="col-md-4">

                            <label class="form-label">
                                Special Claim Type
                            </label>

                            <select name="special_claim_type"
                                    class="form-select form-select-sm"
                                    required>

                                <option value="">
                                    -- Select --
                                </option>

                                <option value="resubmission">
                                    Resubmission
                                </option>

                                <option value="demographic_correction">
                                    Demographic Correction
                                </option>

                                <option value="expired_pa">
                                    Old / Expired PA
                                </option>

                                <option value="other">
                                    Other
                                </option>

                            </select>

                        </div>


                        <div class="col-md-8">

                            <label class="form-label">
                                Reason
                            </label>

                            <input type="text"
                                   name="special_claim_reason"
                                   class="form-control form-control-sm"
                                   maxlength="255">

                        </div>

                    </div>


                    <hr>

                    {{-- ============================================================
                        CLAIM INFORMATION
                        ============================================================ --}}

                    <h6 class="mb-3">
                        Claim Information
                    </h6>

                    <div class="row g-2 mb-2">

                        <div class="col-md-2">
                            <label class="form-label">
                                Participant ID
                            </label>

                            <input type="text"
                                name="ParticipantId"
                                class="form-control form-control-sm"
                                value="{{ $claim->ParticipantId }}"
                                readonly>
                        </div>


                        <div class="col-md-2">
                            <label class="form-label">
                                Service From
                            </label>

                            <input type="date"
                                name="service_from"
                                class="form-control form-control-sm"
                                value="{{ $claim->service_from }}">
                        </div>


                        <div class="col-md-2">
                            <label class="form-label">
                                Service To
                            </label>

                            <input type="date"
                                name="service_to"
                                class="form-control form-control-sm"
                                value="{{ $claim->service_to }}">
                        </div>


                        <div class="col-md-2">
                            <label class="form-label">
                                Procedure
                            </label>

                            <input type="text"
                                name="proc_code"
                                class="form-control form-control-sm"
                                value="{{ $claim->proc_code }}">
                        </div>


                        <div class="col-md-1">
                            <label class="form-label">
                                Modifier
                            </label>

                            <input type="text"
                                name="m1"
                                class="form-control form-control-sm"
                                value="{{ $claim->m1 ?? '' }}">
                        </div>


                        <div class="col-md-1">
                            <label class="form-label">
                                Units
                            </label>

                            <input type="number"
                                name="units"
                                class="form-control form-control-sm"
                                value="{{ $claim->units }}">
                        </div>


                        <div class="col-md-2">
                            <label class="form-label">
                                Rate
                            </label>

                            <input type="number"
                                step="0.01"
                                name="amount_per_unit"
                                class="form-control form-control-sm"
                                value="{{ $claim->amount_per_unit }}">
                        </div>

                    </div>


                    <div class="row g-2 mb-3">

                        <div class="col-md-2">
                            <label class="form-label">
                                Total Amount
                            </label>

                            <input type="number"
                                step="0.01"
                                name="total_amount"
                                class="form-control form-control-sm"
                                value="{{ $claim->total_amount }}">
                        </div>


                        <div class="col-md-4">
                            <label class="form-label">
                                Insurance ID
                            </label>

                            <input type="text"
                                name="insurance_id"
                                class="form-control form-control-sm"
                                value="{{ $claim->insurance_id ?? '' }}">
                        </div>


                        <div class="col-md-3">
                            <label class="form-label">
                                Primary ICD-10
                            </label>

                            <select name="diag1"
                                    id="special_diag1"
                                    class="form-select form-select-sm"
                                    style="width:100%;">

                                @if(!empty($claim->diag1))
                                    <option value="{{ $claim->diag1 }}" selected>
                                        {{ $claim->diag1 }}
                                    </option>
                                @endif

                            </select>
                        </div>


                        <div class="col-md-3">
                            <label class="form-label">
                                Secondary ICD-10
                            </label>

                            <select name="diag2"
                                    id="special_diag2"
                                    class="form-select form-select-sm"
                                    style="width:100%;">

                                @if(!empty($claim->diag2))
                                    <option value="{{ $claim->diag2 }}" selected>
                                        {{ $claim->diag2 }}
                                    </option>
                                @endif

                            </select>
                        </div>

                    </div>

                    <hr>


                    {{-- ============================================================
                         PRIOR AUTHORIZATION OVERRIDE
                         ============================================================ --}}

                    <h6 class="mb-3">
                        Prior Authorization Override
                    </h6>


                    <div class="row g-2 mb-3">

                        <div class="col-md-4">

                            <label class="form-label">
                                PA Number for this Claim
                            </label>

                            <input type="text"
                                   name="override_prior_auth_no"
                                   class="form-control form-control-sm"
                                   value="{{ $claim->prior_auth_no ?? '' }}">

                        </div>


                        <div class="col-md-4 d-flex align-items-end">

                            <div class="form-check">

                                <input class="form-check-input"
                                       type="checkbox"
                                       name="override_pa_validation"
                                       value="1"
                                       id="override_pa_validation">

                                <label class="form-check-label"
                                       for="override_pa_validation">

                                    Allow PA validation override

                                </label>

                            </div>

                        </div>

                    </div>


                    <hr>


                    {{-- ============================================================
                         MEMBER / DEMOGRAPHIC OVERRIDE
                         ============================================================ --}}

                    <h6 class="mb-3">
                        Demographic Information
                    </h6>


                    <div class="form-check mb-3">

                        <input class="form-check-input"
                               type="checkbox"
                               name="override_member_data"
                               value="1"
                               id="override_member_data">

                        <label class="form-check-label"
                               for="override_member_data">

                            Use special demographic information for this claim

                        </label>

                    </div>


                    <div id="demographicOverrideSection">

                        <div class="row g-2 mb-2">

                            <div class="col-md-4">

                                <label class="form-label">
                                    First Name
                                </label>

                                <input type="text"
                                       name="override_first_name"
                                       class="form-control form-control-sm"
                                       value="{{ $member->FirstName ?? '' }}">

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Middle Name
                                </label>

                                <input type="text"
                                       name="override_middle_name"
                                       class="form-control form-control-sm"
                                       value="{{ $member->MiddleName ?? '' }}">

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Last Name
                                </label>

                                <input type="text"
                                       name="override_last_name"
                                       class="form-control form-control-sm"
                                       value="{{ $member->LastName ?? '' }}">

                            </div>

                        </div>


                        <div class="row g-2 mb-2">

                            <div class="col-md-3">

                                <label class="form-label">
                                    DOB
                                </label>

                                <input type="date"

                                name="override_dob"
                                class="form-control form-control-sm"
                                value="{{ !empty($member->DateOfBirth)
                                                ? \Carbon\Carbon::parse($member->DateOfBirth)->format('Y-m-d')
                                                : '' }}">

                            </div>


                            <div class="col-md-5">

                                <label class="form-label">
                                    Address
                                </label>

                                <input type="text"
                                       name="override_address1"
                                       class="form-control form-control-sm"
                                       value="{{ $member->Address1 ?? '' }}">

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Address 2
                                </label>

                                <input type="text"
                                       name="override_address2"
                                       class="form-control form-control-sm"
                                       value="{{ $member->Address2 ?? '' }}">

                            </div>

                        </div>


                        <div class="row g-2 mb-3">

                            <div class="col-md-4">

                                <label class="form-label">
                                    City
                                </label>

                                <input type="text"
                                       name="override_city"
                                       class="form-control form-control-sm"
                                       value="{{ $member->City ?? '' }}">

                            </div>


                            <div class="col-md-2">

                                <label class="form-label">
                                    State
                                </label>

                                <input type="text"
                                       name="override_state"
                                       class="form-control form-control-sm"
                                       value="{{ $member->State ?? '' }}">

                            </div>


                            <div class="col-md-2">

                                <label class="form-label">
                                    ZIP
                                </label>

                                <input type="text"
                                       name="override_zip"
                                       class="form-control form-control-sm"
                                       value="{{ $member->ZipCode ?? '' }}">

                            </div>

                        </div>

                    </div>


                    <input type="hidden"
                           name="original_claim_id"
                           value="{{ $claim->id }}">

                    <button type="submit" class="btn btn-success btn-sm">
                            Save Special Claim
                    </button>

                </form>

            </div>

        </div>

    </div>
</div>

@endsection
@section('scripts')

<script>

$(document).ready(function () {

    function initIcd10(selector) {

        $(selector).each(function () {

            const $el = $(this);

            if ($el.data('select2')) {
                return;
            }

            const $parent = $el
                .closest('.modal, .offcanvas, .card, .card-body, .page-content')
                .first();

            $el.select2({

                width: '100%',

                placeholder: 'Type ICD-10 code or description...',

                allowClear: true,

                minimumInputLength: 2,

                dropdownParent: (
                    $parent.length
                        ? $parent
                        : $(document.body)
                ),

                ajax: {

                    url: "{{ route('lookup.icd10') }}",

                    dataType: 'json',

                    delay: 300,

                    data: function (params) {

                        return {
                            q: params.term || '',
                            limit: 25
                        };
                    },

                    processResults: function (data) {
                        return data;
                    },

                    cache: true
                }

            });

        });

    }

    initIcd10('#special_diag1');
    initIcd10('#special_diag2');

});

</script>

@endsection
