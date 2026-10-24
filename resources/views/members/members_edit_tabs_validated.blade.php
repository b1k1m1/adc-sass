@extends('admin.admin_dashboard')

@section('admin')

    <style>
        input[type="text"] {
            text-transform: uppercase;
        }

        .img-thumbnail {
            max-width: 125px;
            height: auto;
        }

        .member-form-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        }

        .member-header-box {
            background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
            border: 1px solid #e4ecf7;
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 18px;
        }

        .member-tabs {
            border-bottom: 0;
            gap: 6px;
            flex-wrap: wrap;
        }

        .member-tabs .nav-link {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #f8f9fa;
            color: #495057;
            font-weight: 600;
            padding: 10px 14px;
            transition: all .2s ease-in-out;
        }

        .member-tabs .nav-link:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        }

        .member-tabs .nav-link.active {
            color: #fff;
            border-color: transparent;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.16);
        }

        .member-tabs .nav-link.basic.active {
            background: #0d6efd;
        }

        .member-tabs .nav-link.contact.active {
            background: #198754;
        }

        .member-tabs .nav-link.shift.active {
            background: #6f42c1;
        }

        .member-tabs .nav-link.medical.active {
            background: #dc3545;
        }

        .member-tabs .nav-link.special.active {
            background: #fd7e14;
        }

        .member-tabs .nav-link.insurance.active {
            background: #20c997;
        }

        .member-tabs .nav-link.provider.active {
            background: #0b5ed7;
        }

        .tab-pane {
            padding-top: 18px;
        }

        .section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #edf2f7;
            margin-bottom: 18px;
            padding-bottom: 10px;
        }

        .section-title h5 {
            margin: 0;
            font-weight: 700;
        }

        .section-badge {
            font-size: 12px;
            border-radius: 999px;
            padding: 5px 10px;
            background: #f1f5f9;
            color: #475569;
        }

        .required-star {
            color: #dc3545;
            font-weight: 700;
        }

        .field-error-highlight {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .15) !important;
        }

        .sticky-action-bar {
            position: sticky;
            bottom: 0;
            z-index: 50;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(6px);
            border-top: 1px solid #e5e7eb;
            padding: 14px 0 4px 0;
            margin-top: 20px;
        }

        .mini-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: none;
            height: 100%;
        }

        .mini-card .card-title {
            font-weight: 700;
            color: #334155;
        }

        .day-check {
            text-align: center;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 4px;
            background: #f8fafc;
        }

        .day-check label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }

        /* Make Select2 look like Bootstrap form-control */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: .375rem;
            padding: .375rem .75rem;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 0 !important;
            line-height: 1.5 !important;
            color: #212529;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
            top: 0;
            right: 8px;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-right: 28px;
            font-size: 18px;
            line-height: 1;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #86b7fe;
            box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25);
        }

        .select2-container--default.select2-container--disabled .select2-selection--single {
            background-color: #e9ecef;
        }

        .select2-container--open {
            z-index: 99999;
        }

        .select2-dropdown {
            z-index: 99999;
        }

        /* Keep optional fields neutral; show red only when invalid */
        .was-validated .form-control:valid,
        .was-validated .form-select:valid,
        .form-control.is-valid,
        .form-select.is-valid {
            border-color: #ced4da !important;
            padding-right: .75rem !important;
            background-image: none !important;
            box-shadow: none !important;
        }

        .was-validated .form-check-input:valid {
            border-color: #adb5bd !important;
        }

        .tab-content {
            overflow-x: hidden;
        }

        @media (max-width: 768px) {
            .member-tabs .nav-link {
                width: 100%;
                text-align: left;
            }
        }

        /* Validation modal styles */
        .validation-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 999999;
            align-items: center;
            justify-content: center;
        }

        .validation-modal {
            background: white;
            border-radius: 12px;
            padding: 24px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .validation-modal-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .validation-modal-header i {
            font-size: 24px;
            color: #dc3545;
            margin-right: 12px;
        }

        .validation-modal-header h4 {
            margin: 0;
            color: #212529;
        }

        .validation-modal-body {
            margin-bottom: 20px;
        }

        .validation-modal-body p {
            margin-bottom: 12px;
            color: #495057;
        }

        .validation-error-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .validation-error-list li {
            padding: 8px 12px;
            margin-bottom: 6px;
            background: #f8f9fa;
            border-left: 3px solid #dc3545;
            border-radius: 4px;
            font-size: 14px;
        }

        .validation-error-list li i {
            color: #dc3545;
            margin-right: 8px;
        }

        .validation-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .tab-badge-error {
            position: relative;
        }

        .tab-badge-error::after {
            content: '!';
            position: absolute;
            top: -6px;
            right: -6px;
            width: 18px;
            height: 18px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            font-size: 11px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-sm-0 font-size-18">Edit Member -
                                {{ str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT) }} - {{ $member->LastName }},
                                {{ $member->FirstName }}</h4>
                            <p class="text-muted mb-0 mt-1">Update member information by section. Required fields are marked
                                with <span class="required-star">*</span>.</p>
                        </div>

                        <div class="page-title-right">
                            <a href="{{ route('all.members') }}" class="btn btn-primary waves-effect waves-light">
                                {{ t('common.back', 'Back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-12">
                    <div class="alert alert-light border d-flex flex-wrap gap-4 align-items-center py-2 mb-2">
                        <div>
                            <strong>Updated By:</strong>
                            @if (!empty($member->user_cd))
                                {{ $updatedByName ?? ($member->user_cd ?? '—') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                        <div>
                            <strong>Last Updated:</strong>
                            @if (!empty($member->update_dt))
                                {{ \Carbon\Carbon::parse($member->update_dt)->format('m/d/Y') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please correct the following:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card member-form-card">
                        <div class="card-body">

                            <form id="memberForm" class="needs-validation" novalidate action="{{ route('member.update') }}"
                                method="POST">
                                @csrf

                                @php
                                    $mv = function ($field, $default = '') use ($member) {
                                        return old($field, $member->{$field} ?? $default);
                                    };

                                    $mdate = function ($field) use ($member) {
                                        $value = old($field, $member->{$field} ?? '');
                                        return !empty($value) ? \Carbon\Carbon::parse($value)->format('Y-m-d') : '';
                                    };
                                @endphp

                                <div class="member-header-box">
                                    <div class="row align-items-center">
                                        <div class="col-md-9">
                                            <h5 class="mb-1 text-primary">Edit Member Form</h5>
                                            <p class="mb-0 text-muted">
                                                Use the colored tabs below to review/update each section. You may save from
                                                any tab.
                                            </p>
                                        </div>
                                        <div class="col-md-3 text-md-end mt-3 mt-md-0">
                                            <button class="btn btn-success px-4" type="submit">
                                                <i class="mdi mdi-content-save me-1"></i>
                                                {{ t('common.save_changes', 'Save Changes') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <ul class="nav nav-tabs member-tabs mb-3" id="memberTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link basic active" id="basic-tab" data-bs-toggle="tab"
                                            data-bs-target="#tab-basic" type="button" role="tab">
                                            <i class="mdi mdi-account me-1"></i> Basic Info
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link contact" id="contact-tab" data-bs-toggle="tab"
                                            data-bs-target="#tab-contact" type="button" role="tab">
                                            <i class="mdi mdi-map-marker me-1"></i> Address & Contacts
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link shift" id="shift-tab" data-bs-toggle="tab"
                                            data-bs-target="#tab-shift" type="button" role="tab">
                                            <i class="mdi mdi-calendar-clock me-1"></i> Admission & Shift
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link medical" id="medical-tab" data-bs-toggle="tab"
                                            data-bs-target="#tab-medical" type="button" role="tab">
                                            <i class="mdi mdi-medical-bag me-1"></i> Medical
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link special" id="special-tab" data-bs-toggle="tab"
                                            data-bs-target="#tab-special" type="button" role="tab">
                                            <i class="mdi mdi-clipboard-text me-1"></i> Special Instructions
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link insurance" id="insurance-tab" data-bs-toggle="tab"
                                            data-bs-target="#tab-insurance" type="button" role="tab">
                                            <i class="mdi mdi-shield-check me-1"></i> Insurance / PA / HNP
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link provider" id="provider-tab" data-bs-toggle="tab"
                                            data-bs-target="#tab-provider" type="button" role="tab">
                                            <i class="mdi mdi-doctor me-1"></i> Doctors / Pharmacy
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="memberTabsContent">

                                    {{-- TAB 1: BASIC INFO --}}
                                    <div class="tab-pane fade show active" id="tab-basic" role="tabpanel"
                                        aria-labelledby="basic-tab">
                                        <div class="section-title">
                                            <h5 class="text-primary"><i class="mdi mdi-account me-1"></i> Basic
                                                Information</h5>
                                            <span class="section-badge">Required identity fields</span>
                                        </div>

                                        <div class="row align-items-start">
                                            <div class="col-md-9">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="ParticipantId">ID <span
                                                                    class="required-star">*</span></label>
                                                            <input type="text" class="form-control" id="ParticipantId"
                                                                name="ParticipantId"
                                                                value="{{ str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT) }}"
                                                                readonly data-field-label="ID">
                                                            <div class="invalid-feedback">ID is required.</div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <x-dynamic-dropdown name="Trans_flag" table="statusdropdown"
                                                                id="Trans_flag" label="Status" :selected="$mv('Trans_flag') ?? null"
                                                                required />
                                                            <div class="invalid-feedback">Status is required.</div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="hold_date">Date</label>
                                                            <input class="form-control" name="hold_date" type="date"
                                                                value="{{ $mdate('hold_date') }}" id="hold_date">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="reasonfordischarge">Reason for
                                                                Discharge</label>
                                                            <select name="reasonfordischarge" id="reasonfordischarge"
                                                                class="form-select">
                                                                <option value="">--- Select ---</option>
                                                                <option value="DDD PROGRAM"
                                                                    {{ $mv('reasonfordischarge') == 'DDD PROGRAM' ? 'selected' : '' }}>
                                                                    DDD PROGRAM</option>
                                                                <option value="DECEASED"
                                                                    {{ $mv('reasonfordischarge') == 'DECEASED' ? 'selected' : '' }}>
                                                                    DECEASED</option>
                                                                <option value="HOME"
                                                                    {{ $mv('reasonfordischarge') == 'HOME' ? 'selected' : '' }}>
                                                                    HOME</option>
                                                                <option value="LONG TERM CARE FACILITY"
                                                                    {{ $mv('reasonfordischarge') == 'LONG TERM CARE FACILITY' ? 'selected' : '' }}>
                                                                    LONG TERM CARE FACILITY</option>
                                                                <option value="N/A"
                                                                    {{ $mv('reasonfordischarge') == 'N/A' ? 'selected' : '' }}>
                                                                    N/A</option>
                                                                <option value="PARTIAL CARE PROGRAM"
                                                                    {{ $mv('reasonfordischarge') == 'PARTIAL CARE PROGRAM' ? 'selected' : '' }}>
                                                                    PARTIAL CARE PROGRAM</option>
                                                                <option value="REHABILITATION/SUB-ACTIVE"
                                                                    {{ $mv('reasonfordischarge') == 'REHABILITATION/SUB-ACTIVE' ? 'selected' : '' }}>
                                                                    REHABILITATION/SUB-ACTIVE</option>
                                                                <option value="SUBSTANCE ABUSE PROGRAM"
                                                                    {{ $mv('reasonfordischarge') == 'SUBSTANCE ABUSE PROGRAM' ? 'selected' : '' }}>
                                                                    SUBSTANCE ABUSE PROGRAM</option>
                                                                <option value="TRANSFER TO ANOTHER"
                                                                    {{ $mv('reasonfordischarge') == 'TRANSFER TO ANOTHER' ? 'selected' : '' }}>
                                                                    TRANSFER TO ANOTHER AMDC</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="MedicaidNo">Medicaid #</label>
                                                            <input type="text" name="MedicaidNo" class="form-control"
                                                                id="MedicaidNo" value="{{ $mv('MedicaidNo') }}">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="SSNo">SS # <span
                                                                    class="required-star">*</span></label>
                                                            <input type="text" name="SSNo" class="form-control"
                                                                id="SSNo" value="{{ $mv('SSNo') }}"
                                                                data-mask="000-00-0000" required data-field-label="SS #">
                                                            <div class="invalid-feedback">SS # is required.</div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="LastName">Last Name <span
                                                                    class="required-star">*</span></label>
                                                            <input type="text" name="LastName" class="form-control"
                                                                id="LastName" value="{{ $mv('LastName') }}"
                                                                maxlength="15" required data-field-label="Last Name">
                                                            <div class="invalid-feedback">Last name is required.</div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="FirstName">First Name <span
                                                                    class="required-star">*</span></label>
                                                            <input type="text" name="FirstName" class="form-control"
                                                                id="FirstName" value="{{ $mv('FirstName') }}"
                                                                maxlength="15" required data-field-label="First Name">
                                                            <div class="invalid-feedback">First name is required.</div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-1">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="MiddleName">MI</label>
                                                            <input type="text" name="MiddleName" class="form-control"
                                                                id="MiddleName" value="{{ $mv('MiddleName') }}"
                                                                maxlength="1">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="Sex">Sex <span
                                                                    class="required-star">*</span></label>
                                                            <select name="Sex" id="Sex" class="form-select"
                                                                required data-field-label="Sex">
                                                                <option value="">--- Select ---</option>
                                                                <option value="M"
                                                                    {{ $mv('Sex') == 'M' ? 'selected' : '' }}>Male</option>
                                                                <option value="F"
                                                                    {{ $mv('Sex') == 'F' ? 'selected' : '' }}>Female
                                                                </option>
                                                            </select>
                                                            <div class="invalid-feedback">Sex is required.</div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="MaritalStatus">Marital Status
                                                                <span class="required-star">*</span></label>
                                                            <select name="MaritalStatus" id="MaritalStatus"
                                                                class="form-select" required
                                                                data-field-label="Marital Status">
                                                                <option value="">--- Select ---</option>
                                                                <option value="M"
                                                                    {{ $mv('MaritalStatus') == 'M' ? 'selected' : '' }}>
                                                                    Married</option>
                                                                <option value="S"
                                                                    {{ $mv('MaritalStatus') == 'S' ? 'selected' : '' }}>
                                                                    Single</option>
                                                                <option value="W"
                                                                    {{ $mv('MaritalStatus') == 'W' ? 'selected' : '' }}>
                                                                    Widow</option>
                                                                <option value="D"
                                                                    {{ $mv('MaritalStatus') == 'D' ? 'selected' : '' }}>
                                                                    Divorcee</option>
                                                            </select>
                                                            <div class="invalid-feedback">Marital status is required.</div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="DateOfBirth">Birth Date <span
                                                                    class="required-star">*</span></label>
                                                            <input class="form-control" name="DateOfBirth" type="date"
                                                                value="{{ $mdate('DateOfBirth') }}" id="DateOfBirth"
                                                                required data-field-label="Birth Date">
                                                            <div class="invalid-feedback">Birth date is required.</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr>

                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="BirthPlace">Place of
                                                                Birth</label>
                                                            <input type="text" class="form-control" name="BirthPlace"
                                                                id="BirthPlace" value="{{ $mv('BirthPlace') }}"
                                                                maxlength="25">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="BirthCountry">Country of
                                                                Birth</label>
                                                            <input type="text" class="form-control"
                                                                name="BirthCountry" id="BirthCountry"
                                                                value="{{ $mv('BirthCountry') }}" maxlength="25">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <div class="form-group mb-3">
                                                            <label class="form-label" for="USCitizen">US
                                                                Citizen</label><br>
                                                            <input type="hidden" name="USCitizen" value="N">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="USCitizen" id="USCitizen" value="Y"
                                                                {{ $mv('USCitizen') == 'Y' ? 'checked' : '' }}>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="Religion">Religion</label>
                                                            <input type="text" class="form-control" name="Religion"
                                                                id="Religion" value="{{ $mv('Religion') }}"
                                                                maxlength="15">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="LanguageSpoken">Language
                                                                Spoken</label>
                                                            <input type="text" class="form-control"
                                                                name="LanguageSpoken" id="LanguageSpoken"
                                                                value="{{ $mv('LanguageSpoken') }}" maxlength="15">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <label class="form-label" for="Comments">Comments</label>
                                                            <input type="text" class="form-control" name="Comments"
                                                                id="Comments" value="{{ $mv('Comments') }}"
                                                                maxlength="99">
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="col-md-3 text-center">
                                                <div class="mini-card card">
                                                    <div class="card-body">
                                                        @php
                                                            $mediaFolder = session('media_folder');

                                                            if (empty($mediaFolder)) {
                                                                abort(
                                                                    403,
                                                                    'Organization media folder is not configured.',
                                                                );
                                                            }

                                                            $participantId = trim((string) $member->ParticipantId);
                                                            $imageFilename = $participantId . '.jpg';

                                                            $imagePath = "uploads/{$mediaFolder}/members/images/{$imageFilename}";
                                                            $fullImagePath = public_path($imagePath);

                                                            $displayPath = file_exists($fullImagePath)
                                                                ? asset($imagePath)
                                                                : asset('members/images/no_image.jpg');
                                                        @endphp

                                                        <img id="participant_photo" src="{{ $displayPath }}"
                                                            alt="Participant Photo" width="150" height="150"
                                                            style="border:1px solid #ccc; padding:5px; border-radius:10px; object-fit:cover;">

                                                        <p class="text-muted mb-0 mt-2">
                                                            Photo preview by member ID
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- TAB 2: CONTACT --}}
                                    <div class="tab-pane fade" id="tab-contact" role="tabpanel"
                                        aria-labelledby="contact-tab">
                                        <div class="section-title">
                                            <h5 class="text-success"><i class="mdi mdi-map-marker me-1"></i> Address &
                                                Contacts</h5>
                                            <span class="section-badge">Address, phone and emergency contacts</span>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="Address1">Address1 <span
                                                            class="required-star">*</span></label>
                                                    <input type="text" class="form-control" name="Address1"
                                                        id="Address1" maxlength="24" value="{{ $mv('Address1') }}"
                                                        required data-field-label="Address1">
                                                    <div class="invalid-feedback">Address1 is required.</div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="Address2">Address2</label>
                                                    <input type="text" class="form-control" name="Address2"
                                                        id="Address2" maxlength="20" value="{{ $mv('Address2') }}">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label" for="City">City <span
                                                            class="required-star">*</span></label>
                                                    <input type="text" class="form-control" name="City"
                                                        id="City" maxlength="15" value="{{ $mv('City') }}"
                                                        required data-field-label="City">
                                                    <div class="invalid-feedback">City is required.</div>
                                                </div>
                                            </div>

                                            <div class="col-md-1">
                                                <div class="mb-3">
                                                    <label class="form-label" for="State">State</label>
                                                    <input type="text" class="form-control" name="State"
                                                        id="State" value="{{ $mv('State', 'NJ') }}" readonly>
                                                </div>
                                            </div>

                                            <div class="col-md-1">
                                                <div class="mb-3">
                                                    <label class="form-label" for="ZipCode">Zip <span
                                                            class="required-star">*</span></label>
                                                    <input type="text" class="form-control" name="ZipCode"
                                                        id="ZipCode" value="{{ $mv('ZipCode') }}" required
                                                        maxlength="5" data-mask="00000" data-field-label="Zip Code">
                                                    <div class="invalid-feedback">Zip code is required.</div>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label" for="county">County</label>
                                                    <input type="text" class="form-control" name="county"
                                                        id="county" value="{{ $mv('county') }}" maxlength="15">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label" for="Phone1">Home Phone</label>
                                                    <input type="text" class="form-control" name="Phone1"
                                                        id="Phone1" value="{{ $mv('Phone1') }}"
                                                        data-mask="(000)000-0000">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label" for="Phone2">Cell Phone</label>
                                                    <input type="text" class="form-control" name="Phone2"
                                                        id="Phone2" value="{{ $mv('Phone2') }}"
                                                        data-mask="(000)000-0000">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="emergency_nm">Contact #1</label>
                                                    <input type="text" class="form-control" name="emergency_nm"
                                                        id="emergency_nm" maxlength="25"
                                                        value="{{ $mv('emergency_nm') }}">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label" for="emergency_no">Contact #1 Phone</label>
                                                    <input type="text" class="form-control" name="emergency_no"
                                                        id="emergency_no" data-mask="(000)000-0000"
                                                        value="{{ $mv('emergency_no') }}">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="emegency_nm1">Contact #2</label>
                                                    <input type="text" class="form-control" name="emegency_nm1"
                                                        id="emegency_nm1" value="{{ $mv('emegency_nm1') }}">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label" for="emergency_ext">Contact #2 Phone</label>
                                                    <input type="text" class="form-control" name="emergency_ext"
                                                        id="emergency_ext" data-mask="(000)000-0000"
                                                        value="{{ $mv('emergency_ext') }}">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="emergency_relation">Contact
                                                        Relation</label>
                                                    <input type="text" class="form-control" name="emergency_relation"
                                                        id="emergency_relation" maxlength="25"
                                                        value="{{ $mv('emergency_relation') }}">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="LivingWith">Living With</label>
                                                    <input type="text" class="form-control" name="LivingWith"
                                                        id="LivingWith" maxlength="15" value="{{ $mv('LivingWith') }}">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="emergency_add1">Emergency
                                                        Address1</label>
                                                    <input type="text" class="form-control" name="emergency_add1"
                                                        id="emergency_add1" maxlength="25"
                                                        value="{{ $mv('emergency_add1') }}">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="emergency_add2">Emergency
                                                        Address2</label>
                                                    <input type="text" class="form-control" name="emergency_add2"
                                                        id="emergency_add2" maxlength="25"
                                                        value="{{ $mv('emergency_add2') }}">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="emergency_city">Emergency City</label>
                                                    <input type="text" class="form-control" name="emergency_city"
                                                        id="emergency_city" maxlength="20"
                                                        value="{{ $mv('emergency_city') }}">
                                                </div>
                                            </div>

                                            <div class="col-md-1">
                                                <div class="mb-3">
                                                    <label class="form-label" for="emergency_state">State</label>
                                                    <input type="text" class="form-control" name="emergency_state"
                                                        id="emergency_state" maxlength="2"
                                                        value="{{ $mv('emergency_state', 'NJ') }}" readonly>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label" for="emergency_zip">Emergency Zip</label>
                                                    <input type="text" class="form-control" name="emergency_zip"
                                                        id="emergency_zip" maxlength="10"
                                                        value="{{ $mv('emergency_zip') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- TAB 3: ADMISSION & SHIFT --}}
                                    <div class="tab-pane fade" id="tab-shift" role="tabpanel"
                                        aria-labelledby="shift-tab">
                                        <div class="section-title">
                                            <h5 style="color:#6f42c1;"><i class="mdi mdi-calendar-clock me-1"></i>
                                                Admission & Shift</h5>
                                            <span class="section-badge">Admission, route, driver and weekly schedule</span>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="AdmissionDate">Admission Date <span
                                                            class="required-star">*</span></label>
                                                    <input class="form-control" name="AdmissionDate" type="date"
                                                        value="{{ $mdate('AdmissionDate') }}" id="AdmissionDate" required
                                                        data-field-label="Admission Date">
                                                    <div class="invalid-feedback">Admission date is required.</div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="Shift">Shift</label>
                                                    <select name="Shift" id="Shift" class="form-select">
                                                        <option value="Morning"
                                                            {{ $mv('Shift') == 'Morning' ? 'selected' : '' }}>Morning
                                                        </option>
                                                        <option value="Afternoon"
                                                            {{ $mv('Shift') == 'Afternoon' ? 'selected' : '' }}>Afternoon
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label" for="numberofdays"># Days Allowed</label>
                                                    <input type="text" class="form-control" name="numberofdays"
                                                        id="numberofdays" value="{{ $mv('numberofdays') }}"
                                                        maxlength="2">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label" for="insulin_yn">Insulin/Medication</label>
                                                    <select name="insulin_yn" id="insulin_yn" class="form-select">
                                                        <option value="Y"
                                                            {{ $mv('insulin_yn') == 'Y' ? 'selected' : '' }}>
                                                            {{ t('common.yes', 'Yes') }}</option>
                                                        <option value="N"
                                                            {{ $mv('insulin_yn') == 'N' ? 'selected' : '' }}>
                                                            {{ t('common.no', 'No') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label" for="driverid">Driver</label>
                                                    <select name="driverid" id="driverid" class="form-select">
                                                        <option value="">-- Select Driver --</option>
                                                        @foreach ($employees as $e)
                                                            <option value="{{ $e->staffid }}"
                                                                {{ $mv('driverid') == $e->staffid ? 'selected' : '' }}>
                                                                {{ $e->stafflastname }}, {{ $e->stafffirstname }}
                                                                ({{ $e->staffid }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label" for="Route">Route</label>
                                                    <select name="Route" id="Route" class="form-select">
                                                        <option value="">-- Select Route --</option>
                                                        @foreach ($routes as $r)
                                                            <option value="{{ $r->Route_id }}"
                                                                {{ $mv('Route') == $r->Route_id ? 'selected' : '' }}>
                                                                {{ $r->Route_name }} ({{ $r->Route_id }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label" for="AdmittedFrom">Admitted From</label>
                                                    <input type="text" class="form-control" name="AdmittedFrom"
                                                        id="AdmittedFrom" value="{{ $mv('AdmittedFrom') }}"
                                                        maxlength="25">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label" for="ReferedBy">Referred By</label>
                                                    <input type="text" class="form-control" name="ReferedBy"
                                                        id="ReferedBy" value="{{ $mv('ReferedBy') }}" maxlength="25">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label" for="DischargeDate">Discharged Date</label>
                                                    <input class="form-control" name="DischargeDate" type="date"
                                                        value="{{ $mdate('DischargeDate') }}" id="DischargeDate">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label" for="DischargeTo">Discharged To</label>
                                                    <input type="text" class="form-control" name="DischargeTo"
                                                        id="DischargeTo" value="{{ $mv('DischargeTo') }}"
                                                        maxlength="25">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                                                <div class="col-md-1 col-6 mb-2">
                                                    <div class="day-check">
                                                        <label for="{{ $day }}">{{ $day }}</label>
                                                        <input type="hidden" name="{{ $day }}" value="N">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="{{ $day }}" id="{{ $day }}"
                                                            value="Y" {{ $mv($day) == 'Y' ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <hr>

                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="medicare_no">Old Medicare #</label>
                                                    <input type="text" class="form-control" name="medicare_no"
                                                        id="medicare_no" value="{{ $mv('medicare_no') }}"
                                                        data-mask="AAA-AA-AAAA-A">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="new_medicare_no">New Medicare #</label>
                                                    <input type="text" class="form-control" name="new_medicare_no"
                                                        id="new_medicare_no" value="{{ $mv('new_medicare_no') }}"
                                                        data-mask="AAAA-AAA-AAAA" maxlength="25">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="day-check">
                                                    <label for="parta_yn">Hospital Part A</label>
                                                    <input type="hidden" name="parta_yn" value="N">
                                                    <input type="checkbox" class="form-check-input" name="parta_yn"
                                                        id="parta_yn" value="Y"
                                                        {{ $mv('parta_yn') == 'Y' ? 'checked' : '' }}>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="day-check">
                                                    <label for="partb_yn">Medical Part B</label>
                                                    <input type="hidden" name="partb_yn" value="N">
                                                    <input type="checkbox" class="form-check-input" name="partb_yn"
                                                        id="partb_yn" value="Y"
                                                        {{ $mv('partb_yn') == 'Y' ? 'checked' : '' }}>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label class="form-label" for="suppliment_insu">Supplementary
                                                        Insurance</label>
                                                    <select name="suppliment_insu" id="suppliment_insu"
                                                        class="form-select">
                                                        <option value="">--- Select --</option>
                                                        @foreach (['AARP', 'BCBS', 'AETNA', 'AMERI GRP', 'OTHERS'] as $insu)
                                                            <option value="{{ $insu }}"
                                                                {{ $mv('suppliment_insu') == $insu ? 'selected' : '' }}>
                                                                {{ $insu }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- TAB 4: MEDICAL --}}
                                    <div class="tab-pane fade" id="tab-medical" role="tabpanel"
                                        aria-labelledby="medical-tab">
                                        <div class="section-title">
                                            <h5 class="text-danger"><i class="mdi mdi-medical-bag me-1"></i> Medical
                                                History</h5>
                                            <span class="section-badge">Diagnosis, allergies, monitoring and ADL</span>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="AdmittingDiagnosis1">Admitting
                                                        Diagnosis</label>
                                                    <input type="text" class="form-control" name="AdmittingDiagnosis1"
                                                        id="AdmittingDiagnosis1" value="{{ $mv('AdmittingDiagnosis1') }}"
                                                        maxlength="15">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Primary Diagnosis (ICD-10)</label>
                                                    <select id="PrimaryDiagnosis1" name="PrimaryDiagnosis1"
                                                        class="form-control icd10-select">
                                                        @if ($mv('PrimaryDiagnosis1'))
                                                            <option value="{{ $mv('PrimaryDiagnosis1') }}" selected>
                                                                {{ $mv('PrimaryDiagnosis1') }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Secondary Diagnosis (ICD-10)</label>
                                                    <select id="SecondaryDiag1" name="SecondaryDiag1"
                                                        class="form-control icd10-select">
                                                        @if ($mv('SecondaryDiag1'))
                                                            <option value="{{ $mv('SecondaryDiag1') }}" selected>
                                                                {{ $mv('SecondaryDiag1') }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="OtherDiag1">Other Diagnosis</label>
                                                    <input type="text" class="form-control" name="OtherDiag1"
                                                        id="OtherDiag1" value="{{ $mv('OtherDiag1') }}" maxlength="15">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="AdmittingDiagnosis2">Allergies
                                                        1</label>
                                                    <input type="text" class="form-control" name="AdmittingDiagnosis2"
                                                        id="AdmittingDiagnosis2" value="{{ $mv('AdmittingDiagnosis2') }}"
                                                        maxlength="15">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="PrimaryDiagnosis2">Allergies 2</label>
                                                    <input type="text" class="form-control" name="PrimaryDiagnosis2"
                                                        id="PrimaryDiagnosis2" value="{{ $mv('PrimaryDiagnosis2') }}"
                                                        maxlength="15">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="SecondaryDiag2">Allergies 3</label>
                                                    <input type="text" class="form-control" name="SecondaryDiag2"
                                                        id="SecondaryDiag2" value="{{ $mv('SecondaryDiag2') }}"
                                                        maxlength="15">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="OtherDiag2">Allergies 4</label>
                                                    <input type="text" class="form-control" name="OtherDiag2"
                                                        id="OtherDiag2" value="{{ $mv('OtherDiag2') }}" maxlength="15">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row d-flex align-items-stretch mt-2">
                                            <div class="col-xl-8 mb-3">
                                                <div class="card mini-card border-primary h-100">
                                                    <div class="card-body">
                                                        <h5 class="card-title text-primary">Monitoring & Parameters</h5>

                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <x-dynamic-dropdown name="bloodpressure"
                                                                        table="monitordropdown" id="bloodpressure"
                                                                        label="Blood Pressure" :selected="$mv('bloodpressure') ?? null" />
                                                                    <input type="text" class="form-control mt-2"
                                                                        name="Allergies1" id="Allergies1"
                                                                        value="{{ $mv('Allergies1') }}" maxlength="20">
                                                                </div>

                                                                <div class="mb-3">
                                                                    <x-dynamic-dropdown name="bloodsugar"
                                                                        table="monitordropdown" id="bloodsugar"
                                                                        label="Blood Sugar" :selected="$mv('bloodsugar') ?? null" />
                                                                    <input type="text" class="form-control mt-2"
                                                                        name="Allergies2" id="Allergies2"
                                                                        value="{{ $mv('Allergies2') }}" maxlength="20">
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <x-dynamic-dropdown name="weight"
                                                                        table="monitordropdown" id="weight"
                                                                        label="Weight" :selected="$mv('weight') ?? null" />
                                                                    <input type="text" class="form-control mt-2"
                                                                        name="Allergies3" id="Allergies3"
                                                                        value="{{ $mv('Allergies3') }}" maxlength="20">
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="others"
                                                                        class="form-label">Others</label>
                                                                    <input type="text" class="form-control"
                                                                        name="others" id="others"
                                                                        value="{{ $mv('others') }}" maxlength="20">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-4 mb-3">
                                                <div class="card mini-card border-success h-100">
                                                    <div class="card-body">
                                                        <h5 class="card-title text-success">ADL</h5>

                                                        @php
                                                            $adlItems = [
                                                                'bathing_yn' => 'Bathing',
                                                                'toilet_yn' => 'Toilet',
                                                                'transfer_yn' => 'Transfer',
                                                                'locomotion_yn' => 'Locomotion',
                                                                'bed_mobility_yn' => 'Bed Mobility',
                                                                'eating_yn' => 'Eating',
                                                            ];
                                                        @endphp

                                                        <div class="row">
                                                            @foreach ($adlItems as $field => $label)
                                                                <div class="col-md-6">
                                                                    <div class="form-check mb-3">
                                                                        <input type="hidden" name="{{ $field }}"
                                                                            value="N">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="{{ $field }}"
                                                                            id="{{ $field }}" value="Y"
                                                                            {{ $mv($field) == 'Y' ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="{{ $field }}">{{ $label }}</label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    {{-- TAB 5: SPECIAL --}}
                                    <div class="tab-pane fade" id="tab-special" role="tabpanel"
                                        aria-labelledby="special-tab">
                                        <div class="section-title">
                                            <h5 style="color:#fd7e14;"><i class="mdi mdi-clipboard-text me-1"></i> Special
                                                Instructions</h5>
                                            <span class="section-badge">Needs, impairment, nurse and dietary notes</span>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label" for="SpecialNeeds1">Special Needs</label>
                                                    <input type="text" class="form-control" name="SpecialNeeds1"
                                                        id="SpecialNeeds1" value="{{ $mv('SpecialNeeds1') }}"
                                                        maxlength="20">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label" for="Impairement1">Impairment</label>
                                                    <input type="text" class="form-control" name="Impairement1"
                                                        id="Impairement1" value="{{ $mv('Impairement1') }}"
                                                        maxlength="20">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label" for="Nurse1">Nurse</label>
                                                    <input type="text" class="form-control" name="Nurse1"
                                                        id="Nurse1" value="{{ $mv('Nurse1') }}" maxlength="20">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <input type="text" class="form-control" name="SpecialNeeds2"
                                                        id="SpecialNeeds2" value="{{ $mv('SpecialNeeds2') }}"
                                                        maxlength="20" placeholder="Special Needs 2">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <input type="text" class="form-control" name="Impairement2"
                                                        id="Impairement2" value="{{ $mv('Impairement2') }}"
                                                        maxlength="20" placeholder="Impairment 2">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <input type="text" class="form-control" name="Nurse2"
                                                        id="Nurse2" value="{{ $mv('Nurse2') }}" maxlength="20"
                                                        placeholder="Nurse 2">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label" for="DiateryRestriction1">Dietary
                                                        Restrictions 1</label>
                                                    <input type="text" class="form-control" name="DiateryRestriction1"
                                                        id="DiateryRestriction1"
                                                        value="{{ $mv('DiateryRestriction1') }}" maxlength="20">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label" for="DiateryRestriction2">Dietary
                                                        Restrictions 2</label>
                                                    <input type="text" class="form-control" name="DiateryRestriction2"
                                                        id="DiateryRestriction2"
                                                        value="{{ $mv('DiateryRestriction2') }}" maxlength="20">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label" for="DiateryRestriction3">Dietary
                                                        Restrictions 3</label>
                                                    <input type="text" class="form-control" name="DiateryRestriction3"
                                                        id="DiateryRestriction3"
                                                        value="{{ $mv('DiateryRestriction3') }}" maxlength="20">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- TAB 6: INSURANCE --}}
                                    <div class="tab-pane fade" id="tab-insurance" role="tabpanel"
                                        aria-labelledby="insurance-tab">
                                        <div class="section-title">
                                            <h5 style="color:#20c997;"><i class="mdi mdi-shield-check me-1"></i> Insurance
                                                / PA / HNP Information</h5>
                                            <span class="section-badge">Medicaid, Medicare, renewals and HNP</span>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-4 col-sm-6 mb-3">
                                                <div class="card mini-card">
                                                    <div class="card-body">
                                                        <h5 class="card-title">Medicaid Details</h5>

                                                        <div class="mb-3">
                                                            <x-dynamic-dropdown name="HMO_Enrolled" table="hmodropdown"
                                                                id="HMO_Enrolled" label="HMO" :selected="$mv('HMO_Enrolled') ?? null" />
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label" for="HMO_Enrolled_date">Date</label>
                                                            <input class="form-control" name="HMO_Enrolled_date"
                                                                type="date" value="{{ $mdate('HMO_Enrolled_date') }}"
                                                                id="HMO_Enrolled_date">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label" for="medicaid_HMO_id">ID#</label>
                                                            <input type="text" class="form-control"
                                                                name="medicaid_HMO_id" id="medicaid_HMO_id"
                                                                value="{{ $mv('medicaid_HMO_id') }}">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label" for="medicaid_HMO_grp">Group</label>
                                                            <input type="text" class="form-control"
                                                                name="medicaid_HMO_grp" id="medicaid_HMO_grp"
                                                                value="{{ $mv('medicaid_HMO_grp') }}">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label" for="medicaid_PA_no">PA#</label>
                                                            <input type="text" class="form-control"
                                                                name="medicaid_PA_no" id="medicaid_PA_no"
                                                                value="{{ $mv('medicaid_PA_no') }}">
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-6">
                                                                <label class="form-label" for="medicaid_PA_Start">Start
                                                                    Date</label>
                                                                <input class="form-control" name="medicaid_PA_Start"
                                                                    type="date"
                                                                    value="{{ $mdate('medicaid_PA_Start') }}"
                                                                    id="medicaid_PA_Start">
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label class="form-label" for="medicaid_PA_End">End
                                                                    Date</label>
                                                                <input class="form-control" name="medicaid_PA_End"
                                                                    type="date"
                                                                    value="{{ $mdate('medicaid_PA_End') }}"
                                                                    id="medicaid_PA_End">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4 col-sm-6 mb-3">
                                                <div class="card mini-card">
                                                    <div class="card-body">
                                                        <h5 class="card-title">Medicare HMO / Renewal</h5>

                                                        <div class="mb-3">
                                                            <x-dynamic-dropdown name="medicare_HMO_Enrolled"
                                                                table="hmodropdown" id="medicare_HMO_Enrolled"
                                                                label="Medicare HMO" :selected="$mv('medicare_HMO_Enrolled') ?? null" />
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label"
                                                                for="medicare_HMO_En_date">Date</label>
                                                            <input class="form-control" name="medicare_HMO_En_date"
                                                                type="date"
                                                                value="{{ $mdate('medicare_HMO_En_date') }}"
                                                                id="medicare_HMO_En_date">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label" for="medicare_HMO_id">ID#</label>
                                                            <input type="text" class="form-control"
                                                                name="medicare_HMO_id" id="medicare_HMO_id"
                                                                value="{{ $mv('medicare_HMO_id') }}">
                                                        </div>

                                                        <hr>

                                                        <h6 class="fw-bold">Renewal 1</h6>

                                                        <div class="mb-3">
                                                            <x-dynamic-dropdown name="renew1_HMO_Enrolled"
                                                                table="hmodropdown" id="renew1_HMO_Enrolled"
                                                                label="HMO" :selected="$mv('renew1_HMO_Enrolled') ?? null" />
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label" for="renew1_HMO_id">ID#</label>
                                                            <input type="text" class="form-control"
                                                                name="renew1_HMO_id" id="renew1_HMO_id"
                                                                value="{{ $mv('renew1_HMO_id') }}">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label" for="renew1_HMO_grp">Group</label>
                                                            <input type="text" class="form-control"
                                                                name="renew1_HMO_grp" id="renew1_HMO_grp"
                                                                value="{{ $mv('renew1_HMO_grp') }}">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label" for="renew1_PA_no">PA#</label>
                                                            <input type="text" class="form-control"
                                                                name="renew1_PA_no" id="renew1_PA_no"
                                                                value="{{ $mv('renew1_PA_no') }}">
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-6">
                                                                <label class="form-label" for="renew1_PA_Start">Start
                                                                    Date</label>
                                                                <input class="form-control" name="renew1_PA_Start"
                                                                    type="date"
                                                                    value="{{ $mdate('renew1_PA_Start') }}"
                                                                    id="renew1_PA_Start">
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label class="form-label" for="renew1_PA_End">End
                                                                    Date</label>
                                                                <input class="form-control" name="renew1_PA_End"
                                                                    type="date"
                                                                    value="{{ $mdate('renew1_PA_End') }}"
                                                                    id="renew1_PA_End">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4 col-sm-6 mb-3">
                                                <div class="card mini-card">
                                                    <div class="card-body">
                                                        <h5 class="card-title">HNP Information</h5>
                                                        <!--
                                                        <div class="mb-3">
                                                            <label class="form-label" for="hnpinfo">HNP Notes</label>
                                                            <textarea class="form-control" name="" id="hnpinfo" rows="8">{{ $mv('hnpinfo') }}</textarea>
                                                        </div>
                                                        -->
                                                        <div class="mb-3">
                                                            <label class="form-label" for="HNP_Expiry_date">HNP Expiry
                                                                Date</label>
                                                            <input class="form-control" name="HNP_Expiry_date"
                                                                type="date" value="{{ $mdate('HNP_Expiry_date') }}"
                                                                id="HNP_Expiry_date">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label" for="HNP_Renewal_date">HNP Renewal
                                                                Date</label>
                                                            <input class="form-control" name="HNP_Renewal_date"
                                                                type="date"
                                                                value="{{ $mdate('HNP_Renewal_date') }}"
                                                                id="HNP_Renewal_date">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if (isset($coverageHistory))
                                            <hr>
                                            <div class="section-title mt-3">
                                                <h5 class="text-info"><i class="mdi mdi-history me-1"></i> Coverage
                                                    History</h5>
                                                <span class="section-badge">Previous and current coverage records</span>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Effective From</th>
                                                            <th>Effective To</th>
                                                            <th>HMO</th>
                                                            <th>Insurance ID</th>
                                                            <th>Primary Dx</th>
                                                            <th>Secondary Dx</th>
                                                            <th>PA No</th>
                                                            <th>PA Start</th>
                                                            <th>PA End</th>
                                                            <th>Current</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($coverageHistory as $row)
                                                            <tr>
                                                                <td>{{ $row->effective_from ? \Carbon\Carbon::parse($row->effective_from)->format('m/d/Y') : '' }}
                                                                </td>
                                                                <td>{{ $row->effective_to ? \Carbon\Carbon::parse($row->effective_to)->format('m/d/Y') : '' }}
                                                                </td>
                                                                <td>{{ $row->HMO_Enrolled }}</td>
                                                                <td>{{ $row->medicaid_HMO_id }}</td>
                                                                <td>{{ $row->PrimaryDiagnosis1 }}</td>
                                                                <td>{{ $row->SecondaryDiag1 }}</td>
                                                                <td>{{ $row->medicaid_PA_no }}</td>
                                                                <td>{{ $row->medicaid_PA_Start ? \Carbon\Carbon::parse($row->medicaid_PA_Start)->format('m/d/Y') : '' }}
                                                                </td>
                                                                <td>{{ $row->medicaid_PA_End ? \Carbon\Carbon::parse($row->medicaid_PA_End)->format('m/d/Y') : '' }}
                                                                </td>
                                                                <td class="text-center">
                                                                    @if ($row->is_current === 'Y')
                                                                        <span class="badge bg-success">Current</span>
                                                                    @else
                                                                        <span class="badge bg-secondary">History</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="10" class="text-center text-muted">No
                                                                    coverage history found.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- TAB 7: PROVIDER --}}
                                    <div class="tab-pane fade" id="tab-provider" role="tabpanel"
                                        aria-labelledby="provider-tab">
                                        <div class="section-title">
                                            <h5 class="text-primary"><i class="mdi mdi-doctor me-1"></i> Doctors /
                                                Pharmacy</h5>
                                            <span class="section-badge">Primary doctor, accepted HMOs and pharmacy</span>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-4 col-sm-6 mb-3">
                                                <div class="card mini-card">
                                                    <div class="card-body">
                                                        <h5 class="card-title">Primary Doctor Details</h5>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-6">
                                                                <label class="form-label"
                                                                    for="primary_doctor_nm">Name</label>
                                                                <input type="text" class="form-control"
                                                                    name="primary_doctor_nm" id="primary_doctor_nm"
                                                                    value="{{ $mv('primary_doctor_nm') }}">
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label class="form-label"
                                                                    for="primary_doctor_NPI">NPI#</label>
                                                                <input type="text" class="form-control"
                                                                    name="primary_doctor_NPI" id="primary_doctor_NPI"
                                                                    value="{{ $mv('primary_doctor_NPI') }}">
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label"
                                                                for="primary_doctor_add">Address</label>
                                                            <input type="text" class="form-control"
                                                                name="primary_doctor_add" id="primary_doctor_add"
                                                                value="{{ $mv('primary_doctor_add') }}">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label"
                                                                for="primary_doctor_city">City/Zip</label>
                                                            <input type="text" class="form-control"
                                                                name="primary_doctor_city" id="primary_doctor_city"
                                                                value="{{ $mv('primary_doctor_city') }}">
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-6">
                                                                <label class="form-label"
                                                                    for="primary_doctor_ph">Phone</label>
                                                                <input type="text" class="form-control"
                                                                    name="primary_doctor_ph" id="primary_doctor_ph"
                                                                    value="{{ $mv('primary_doctor_ph') }}"
                                                                    data-mask="(000)000-0000">
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label class="form-label"
                                                                    for="primary_doctor_fax">Fax</label>
                                                                <input type="text" class="form-control"
                                                                    name="primary_doctor_fax" id="primary_doctor_fax"
                                                                    value="{{ $mv('primary_doctor_fax') }}"
                                                                    data-mask="(000)000-0000">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4 col-sm-6 mb-3">
                                                <div class="card mini-card">
                                                    <div class="card-body">
                                                        <h5 class="card-title mb-4">Primary Doctor HMO Details</h5>

                                                        <div class="mb-4">
                                                            <x-dynamic-dropdown name="primary_doctor_hmo1"
                                                                table="hmodropdown" id="primary_doctor_hmo1"
                                                                label="HMO 1" :selected="$mv('primary_doctor_hmo1') ?? null" />
                                                        </div>

                                                        <div class="mb-4">
                                                            <x-dynamic-dropdown name="primary_doctor_hmo2"
                                                                table="hmodropdown" id="primary_doctor_hmo2"
                                                                label="HMO 2" :selected="$mv('primary_doctor_hmo2') ?? null" />
                                                        </div>

                                                        <div class="mb-4">
                                                            <x-dynamic-dropdown name="primary_doctor_hmo3"
                                                                table="hmodropdown" id="primary_doctor_hmo3"
                                                                label="HMO 3" :selected="$mv('primary_doctor_hmo3') ?? null" />
                                                        </div>

                                                        <div class="mb-4">
                                                            <x-dynamic-dropdown name="primary_doctor_hmo4"
                                                                table="hmodropdown" id="primary_doctor_hmo4"
                                                                label="HMO 4" :selected="$mv('primary_doctor_hmo4') ?? null" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4 col-sm-6 mb-3">
                                                <div class="card mini-card">
                                                    <div class="card-body">
                                                        <h5 class="card-title">Pharmacy Details</h5>

                                                        <div class="mb-3">
                                                            <label class="form-label" for="pharmacy_nm">Name</label>
                                                            <input type="text" class="form-control"
                                                                name="pharmacy_nm" id="pharmacy_nm"
                                                                value="{{ $mv('pharmacy_nm') }}">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label" for="pharmacy_add">Address</label>
                                                            <input type="text" class="form-control"
                                                                name="pharmacy_add" id="pharmacy_add"
                                                                value="{{ $mv('pharmacy_add') }}">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label"
                                                                for="pharmacy_city">City/Zip</label>
                                                            <input type="text" class="form-control"
                                                                name="pharmacy_city" id="pharmacy_city"
                                                                value="{{ $mv('pharmacy_city') }}">
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-6">
                                                                <label class="form-label"
                                                                    for="pharmacy_phone">Phone</label>
                                                                <input type="text" class="form-control"
                                                                    name="pharmacy_phone" id="pharmacy_phone"
                                                                    value="{{ $mv('pharmacy_phone') }}"
                                                                    data-mask="(000)000-0000">
                                                            </div>

                                                            <div class="mb-3 col-md-6">
                                                                <label class="form-label" for="pharmacy_fax">Fax</label>
                                                                <input type="text" class="form-control"
                                                                    name="pharmacy_fax" id="pharmacy_fax"
                                                                    value="{{ $mv('pharmacy_fax') }}"
                                                                    data-mask="(000)000-0000">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="sticky-action-bar">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <span class="required-star">*</span> Required fields. Missing fields will open
                                            the correct tab automatically.
                                        </small>

                                        <div>
                                            <a href="{{ route('all.members') }}" class="btn btn-light me-2">
                                                {{ t('common.back', 'Back') }}
                                            </a>
                                            <button class="btn btn-success px-4" type="submit">
                                                <i class="mdi mdi-content-save me-1"></i>
                                                {{ t('common.save_changes', 'Save Changes') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Validation Modal -->
    <div class="validation-modal-overlay" id="validationModal">
        <div class="validation-modal">
            <div class="validation-modal-header">
                <i class="mdi mdi-alert-circle"></i>
                <h4>Required Fields Missing</h4>
            </div>
            <div class="validation-modal-body">
                <p>Please complete the following required fields:</p>
                <ul class="validation-error-list" id="validationErrorList">
                </ul>
            </div>
            <div class="validation-modal-footer">
                <button class="btn btn-secondary" onclick="closeValidationModal()">Close</button>
                <button class="btn btn-primary" onclick="goToFirstError()">Go to First Field</button>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(function() {

            // Tab configuration mapping
            const tabConfig = {
                'tab-basic': {
                    button: '#basic-tab',
                    name: 'Basic Info',
                    color: '#0d6efd'
                },
                'tab-contact': {
                    button: '#contact-tab',
                    name: 'Address & Contacts',
                    color: '#198754'
                },
                'tab-shift': {
                    button: '#shift-tab',
                    name: 'Admission & Shift',
                    color: '#6f42c1'
                },
                'tab-medical': {
                    button: '#medical-tab',
                    name: 'Medical',
                    color: '#dc3545'
                },
                'tab-special': {
                    button: '#special-tab',
                    name: 'Special Instructions',
                    color: '#fd7e14'
                },
                'tab-insurance': {
                    button: '#insurance-tab',
                    name: 'Insurance / PA / HNP',
                    color: '#20c997'
                },
                'tab-provider': {
                    button: '#provider-tab',
                    name: 'Doctors / Pharmacy',
                    color: '#0b5ed7'
                }
            };

            // Store all invalid fields for navigation
            let invalidFields = [];
            let currentErrorIndex = 0;

            function initIcd10(selector) {
                $(selector).each(function() {
                    const $el = $(this);

                    if ($el.data('select2')) return;

                    const $parent = $el.closest('.modal, .offcanvas, .card, .card-body, .page-content')
                        .first();

                    $el.select2({
                        width: '100%',
                        placeholder: 'Type ICD-10 code or description...',
                        allowClear: true,
                        minimumInputLength: 2,
                        dropdownParent: ($parent.length ? $parent : $(document.body)),
                        ajax: {
                            url: "{{ route('lookup.icd10') }}",
                            dataType: 'json',
                            delay: 300,
                            data: function(params) {
                                return {
                                    q: params.term || '',
                                    limit: 25
                                };
                            },
                            processResults: function(data) {
                                return data;
                            }
                        }
                    });
                });
            }

            initIcd10('#PrimaryDiagnosis1');
            initIcd10('#SecondaryDiag1');

            // Photo preview by Participant ID
            const participantInput = document.getElementById('ParticipantId');
            if (participantInput) {
                participantInput.addEventListener('input', function() {
                    let id = this.value.trim();
                    id = parseInt(id, 10);
                    if (isNaN(id)) id = '';

                    const imageElement = document.getElementById('participant_photo');

                    if (id) {
                        const imageUrl = `/members/images/${id}.jpg`;
                        const testImage = new Image();

                        testImage.onload = function() {
                            imageElement.src = imageUrl;
                        };

                        testImage.onerror = function() {
                            imageElement.src = "{{ asset('members/images/no_image.jpg') }}";
                        };

                        testImage.src = imageUrl;
                    } else {
                        imageElement.src = "{{ asset('members/images/no_image.jpg') }}";
                    }
                });
            }

            // Enhanced validation on form submit
            const form = document.getElementById('memberForm');

            if (form) {
                form.addEventListener('submit', function(event) {
                    // Clear previous validation state
                    $('.field-error-highlight').removeClass('field-error-highlight');
                    $('.tab-badge-error').removeClass('tab-badge-error');
                    form.classList.remove('was-validated');

                    // Check form validity
                    if (form.checkValidity()) {
                        return true;
                    }

                    event.preventDefault();
                    event.stopPropagation();

                    // Collect all invalid fields
                    invalidFields = [];
                    const requiredFields = form.querySelectorAll('[required]');

                    requiredFields.forEach(function(field) {
                        if (!field.checkValidity()) {
                            const $field = $(field);
                            const fieldLabel = $field.data('field-label') ||
                                $field.closest('.mb-3, .form-group, .form-check').find('label')
                                .first().text().replace('*', '').replace(':', '').trim() ||
                                field.name ||
                                'Required field';

                            const tabPane = $field.closest('.tab-pane');
                            let tabId = '';
                            let tabName = '';

                            if (tabPane.length) {
                                tabId = tabPane.attr('id');
                                if (tabConfig[tabId]) {
                                    tabName = tabConfig[tabId].name;
                                }
                            }

                            invalidFields.push({
                                field: $field,
                                label: fieldLabel,
                                tabId: tabId,
                                tabName: tabName
                            });
                        }
                    });

                    if (invalidFields.length === 0) {
                        return false;
                    }

                    // Mark tabs with errors
                    const tabsWithErrors = new Set();
                    invalidFields.forEach(function(item) {
                        if (item.tabId) {
                            tabsWithErrors.add(item.tabId);
                        }
                    });

                    tabsWithErrors.forEach(function(tabId) {
                        if (tabConfig[tabId]) {
                            $(tabConfig[tabId].button).addClass('tab-badge-error');
                        }
                    });

                    // Show validation modal with error list
                    showValidationModal();

                    return false;
                }, false);
            }

            function showValidationModal() {
                const modal = document.getElementById('validationModal');
                const errorList = document.getElementById('validationErrorList');

                // Clear previous errors
                errorList.innerHTML = '';

                // Group errors by tab
                const errorsByTab = {};
                invalidFields.forEach(function(item) {
                    const tab = item.tabName || 'Other';
                    if (!errorsByTab[tab]) {
                        errorsByTab[tab] = [];
                    }
                    errorsByTab[tab].push(item.label);
                });

                // Build error list HTML
                Object.keys(errorsByTab).forEach(function(tabName) {
                    const tabHeader = document.createElement('li');
                    tabHeader.innerHTML = `<strong><i class="mdi mdi-tag"></i> ${tabName}:</strong>`;
                    errorList.appendChild(tabHeader);

                    errorsByTab[tabName].forEach(function(fieldLabel) {
                        const li = document.createElement('li');
                        li.innerHTML = `<i class="mdi mdi-close-circle"></i> ${fieldLabel}`;
                        errorList.appendChild(li);
                    });
                });

                // Show modal
                modal.style.display = 'flex';
                currentErrorIndex = 0;
            }

            window.closeValidationModal = function() {
                const modal = document.getElementById('validationModal');
                modal.style.display = 'none';
            };

            window.goToFirstError = function() {
                closeValidationModal();

                if (invalidFields.length === 0) return;

                const firstError = invalidFields[0];
                const tabId = firstError.tabId;

                // Switch to the tab containing the error
                if (tabId && tabConfig[tabId]) {
                    const tabButton = $(tabConfig[tabId].button);

                    if (tabButton.length) {
                        if (window.bootstrap && bootstrap.Tab) {
                            bootstrap.Tab.getOrCreateInstance(tabButton[0]).show();
                        } else {
                            tabButton.trigger('click');
                        }
                    }
                }

                // Wait for tab transition, then highlight and focus the field
                setTimeout(function() {
                    const field = firstError.field;
                    field.addClass('field-error-highlight');

                    $('html, body').animate({
                        scrollTop: field.offset().top - 160
                    }, 350);

                    try {
                        field[0].focus({
                            preventScroll: true
                        });
                    } catch (e) {
                        field.trigger('focus');
                    }

                    // Handle Select2 fields
                    if (field.hasClass('select2-hidden-accessible')) {
                        field.select2('open');
                    }
                }, 300);
            };

            // Close modal when clicking outside
            document.getElementById('validationModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeValidationModal();
                }
            });

            // Clear error styling when field is corrected
            $(form).on('input change', '.form-control, .form-select', function() {
                const $field = $(this);
                if ($field[0].checkValidity()) {
                    $field.removeClass('field-error-highlight');
                }
            });

            // Remove tab error badge when tab is clicked
            $('.member-tabs .nav-link').on('click', function() {
                $(this).removeClass('tab-badge-error');
            });

        });
    </script>
@endsection
