@extends('admin.admin_dashboard')
@section('admin')

<style>
    input[type="text"]{
        text-transform: uppercase;
    }

    .img-thumbnail {
    max-width: 125px; /* Adjust as needed */
    height: auto;
    }

    /* Make Select2 look like Bootstrap form-control */
    .select2-container { width: 100% !important; }

    .select2-container--default .select2-selection--single{
    height: 38px;                 /* Bootstrap input height */
    border: 1px solid #ced4da;    /* Bootstrap border */
    border-radius: .375rem;       /* Bootstrap radius */
    padding: .375rem .75rem;      /* Bootstrap padding */
    display: flex;
    align-items: center;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered{
    padding-left: 0 !important;
    line-height: 1.5 !important;
    color: #212529;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow{
    height: 38px;
    top: 0;
    right: 8px;
    }

    /* Clear (x) alignment */
    .select2-container--default .select2-selection--single .select2-selection__clear{
    margin-right: 28px;
    font-size: 18px;
    line-height: 1;
    }

    /* Focus style like Bootstrap */
    .select2-container--default.select2-container--focus .select2-selection--single{
    border-color: #86b7fe;
    box-shadow: 0 0 0 .25rem rgba(13,110,253,.25);
    }

    /* Optional: disabled look */
    .select2-container--default.select2-container--disabled .select2-selection--single{
    background-color: #e9ecef;
    }

    .select2-container--open { z-index: 99999; }
    .select2-dropdown { z-index: 99999; }

</style>
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Add Member</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('members.dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="font-size-20 mb-4 text-primary"><i class="mdi mdi-arrow-right text-primary me-1"></i>General Information</h5>
                        <form class="needs-validation" novalidate action="{{ route('store.member') }}" method="POST">
                            @csrf
                            <div class="row d-flex align-items-center">
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label class="form-label" for="ID">ID</label>
                                        <input type="text" class="form-control" id="ParticipantId" name="ParticipantId"
                                               value="{{ old('ParticipantId', session('members_data.ParticipantId', $nextid )) }}" required>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <x-dynamic-dropdown
                                        name="Trans_flag"
                                        table="statusdropdown"
                                        id="Trans_flag"
                                        label="Status"
                                        :selected="old('Trans_flag') ?? null"
                                        required />
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="Date">Date</label>
                                        <input class="form-control" name="hold_date" type="date" value="" id="hold_date">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Reason for Discharge</label>
                                        <select name="reasonfordischarge" id="reasonfordischarge" class="form-select">
                                            <option value="">--- Select ---</option>
                                            <option value="DDD PROGRAM">DDD PROGRAM</option>
                                            <option value="DECEASED">DECEASED</option>
                                            <option value="HOME">HOME</option>
                                            <option value="LONG TERM CARE FACILITY">LONG TERM CARE FACILITY</option>
                                            <option value="N/A">N/A</option>
                                            <option value="PARTIAL CARE PROGRAM">PARTIAL CARE PROGRAM</option>
                                            <option value="REHABILATION/SUB-ACTIVE">REHABILATION/SUB-ACTIVE</option>
                                            <option value="SUBSTANCE ABUSE PROGRAM">SUBSTANCE ABUSE PROGRAM</option>
                                            <option value="TRANSFER TO ANOTHER">TRANSFER TO ANOTHER AMDC</option>
                                        </select>

                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Medicaid #</label>
                                        <input type="text" name="MedicaidNo" class="form-control" id="MedicaidNo" value="">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex justify-content-center align-items-center">
                                    <div>
                                        <img id="participant_photo" src="{{ asset('members/images/no_image.jpg') }}" alt="Participant Photo" width="150" height="150" style="border:1px solid #ccc; padding:5px;">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="row">
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom01">SS #</label>
                                        <!-- <input type="text" name="SSNo" class="form-control" id="secure-mask" placeholder="SS #" value="" pattern="\d{3}-?\d{2}-?\d{4}" required> -->
                                        <input type="text" name="SSNo" class="form-control" id="SSNo" value="" data-mask="000-00-0000" required>
                                        <div class="valid-feedback">
                                                Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom01">Last name</label>
                                        <input type="text" name="LastName" class="form-control" id="LastName" value="" maxlength="15" required>
                                    <div class="valid-feedback">
                                            Looks good!
                                    </div>
                                </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">First name</label>
                                        <input type="text" name="FirstName" class="form-control" id="FirstName" value="" maxlength="15" required>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom01">Mi</label>
                                        <input type="text" name="MiddleName" class="form-control" id="MiddleName" value="" maxlength="1">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom03">Sex</label>
                                        <select name="Sex" id="Sex" class="form-select" required>
                                            <option value="">--- Select ---</option>
                                            <option value="M">Male</option>
                                            <option value="F">Female</option>
                                        </select>
                                    </div>
                                    <div class="invalid-feedback">
                                            Please provide a valid sex.
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom03">Marital Staus</label>
                                        <select name="MaritalStatus" id="MaritalStatus" class="form-select" required>
                                            <option value="">--- Select ---</option>
                                            <option value="M">Married</option>
                                            <option value="S">Single</option>
                                            <option value="W">Widow</option>
                                            <option value="D">Divorcee</option>
                                        </select>
                                    </div>
                                    <div class="invalid-feedback">
                                            Please provide a valid sex.
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Address1</label>
                                        <input type="text" class="form-control" name="Address1" id="Address1" maxlength="15" value="" required>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Address2</label>
                                        <input type="text" class="form-control" name="Address2" id="Address2" maxlength="15" value="">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom01">City</label>
                                        <input type="text" class="form-control" name="City" id="City" maxlength="15" value="" required>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">State</label>
                                        <input type="text" class="form-control" name="State" id="State" value="NJ"
                                            readonly>
                                        </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Zip Code</label>
                                        <input type="text" class="form-control" name="ZipCode" id="ZipCode" value="" required maxlength="5" data-mask="00000">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom01">County</label>
                                        <input type="text" class="form-control" name="county" id="county" value="" maxlength="15">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Home Phone</label>
                                        <input type="text" class="form-control" name="Phone1" id="Phone1" data-mask="(000)000-0000">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Cell Phone</label>
                                        <input type="text" class="form-control" name="Phone2" id="Phone2" value="" data-mask="(000)000-0000">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Contact # 1</label>
                                        <input type="text" class="form-control" name="emergency_nm" id="emergency_nm" maxlength="25" value="">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Contact # 1 Phone</label>
                                        <input type="text" class="form-control" name="emergency_no" id="emergency_no" data-mask="(000)000-0000" maxlength="10">
                                    </div>
                                </div>
                            <div class=row>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Contact # 2</label>
                                        <input type="text" class="form-control" name="emegency_nm1" id="emegency_nm1" value="">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Contact # 2 Phone</label>
                                        <input type="text" class="form-control" name="emergency_ext" id="emergency_ext" data-mask="(000)000-0000" value="">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Contact Relation</label>
                                        <input type="text" class="form-control" name="emergency_relation" id="emergency_relation" maxlength="25" value="">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Living with</label>
                                        <input type="text" class="form-control" name="LivingWith" id="LivingWith" maxlength="15" value="">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Emergency Address1</label>
                                        <input type="text" class="form-control" name="emergency_add1" id="emergency_add1" maxlength="25" value="">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Emergency Address2</label>
                                        <input type="text" class="form-control" name="emergency_add2" id="emergency_add2" maxlength="25" value="">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Emergency City</label>
                                        <input type="text" class="form-control" name="emergency_city" id="emergency_city" maxlength="20" value="">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">State</label>
                                        <input type="text" class="form-control" name="emergency_state" id="emergency_state" maxlength="2" value="NJ"
                                            readonly>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Emergency Zip</label>
                                        <input type="text" class="form-control" name="emergency_zip" id="emergency_zip" maxlength="10" value="">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="Date">Birth Date</label>
                                        <input class="form-control" name="DateOfBirth" type="date" value="" id="DateOfBirth" required>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom03">Place of Birth</label>
                                        <input type="text" class="form-control" name="BirthPlace" id="BirthPlace" maxlength="25">
                                        <div class="invalid-feedback">
                                            Please provide a valid city.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Country of Birth</label>
                                        <input type="text" class="form-control" name="BirthCountry" id="BirthCountry" maxlength="25">
                                        <div class="invalid-feedback">
                                            Please provide a valid state.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label" for="validationCustom05">US Citizen</label>
                                        <br>
                                        <input type="hidden" name="USCitizen" value="N">
                                        <input type="checkbox" class="form-check-input" name="USCitizen" id="USCitizen" value="Y" {{ old('USCitizen') == 'Y' ? 'checked' : '' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Religion</label>
                                        <input type="text" class="form-control" name="Religion" id="Religion" maxlength="15">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Language Spoken</label>
                                        <input type="text" class="form-control" name="LanguageSpoken" id="LanguageSpoken" maxlength="15">
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Comments</label>
                                        <input type="text" class="form-control" name="Comments" id="Comments" maxlength="99">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Insulin/Medication</label>
                                        <select name="insulin_yn" id="insulin_yn" class="form-select">
                                            <option value="Y">{{ t('common.yes', 'Yes') }}</option>
                                            <option value="N">{{ t('common.no', 'No') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Driver</label>
                                        <select name="driverid" id="driverid" class="form-select">
                                            <option value="">-- Select Driver --</option>
                                            @foreach($employees as $e)
                                                <option value="{{ $e->staffid }}">
                                                    {{ $e->stafflastname }}, {{ $e->stafffirstname }} ({{ $e->staffid }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Route</label>
                                        <select name="route" id="route" class="form-select">
                                            <option value="">-- Select Route --</option>
                                            @foreach($routes as $r)
                                                <option value="{{ $r->Route_id }}">
                                                    {{ $r->Route_name }} ({{ $r->Route_id }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04"># Days Allowed</label>
                                        <input type="text" class="form-control" name="numberofdays" id="numberofdays" maxlength="2">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Admitted from</label>
                                        <input type="text" class="form-control" name="AdmittedFrom" id="AdmittedFrom" maxlength="25">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Refered By</label>
                                        <input type="text" class="form-control" name="ReferedBy" id="ReferedBy" maxlength="25">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Discharged Date</label>
                                        <input class="form-control" name="DischargeDate" type="date" value="" id="DischargeDate">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Discharged To</label>
                                        <input type="text" class="form-control" name="DischargeTo" id="DischargeTo" maxlength="25">
                                    </div>
                                </div>
                           </div>
                            <hr color="blue" size="5" noshade>
                            <h5 class="font-size-20 mb-4 text-warning"><i class="mdi mdi-arrow-right text-primary me-1"></i>Shift & Medicare Information</h5>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Admission Date</label>
                                        <input class="form-control" name="AdmissionDate" type="date" value="" id="AdmissionDate" required>
                                        <div class="invalid-feedback">
                                            Please provide a valid state.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">Shift</label>
                                        <select name="Shift" id="Shift" class="form-select">
                                            <option value="Morning">Morning</option>
                                            <option value="Afternoon">Afternoon</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="validationCustom05">Sun</label>
                                        <br>
                                        <input type="hidden" name="Sun" value="N">
                                        <input type="checkbox" class="form-check-input" name="Sun" id="Sun" value="Y" {{ old('Sun') == 'Y' ? 'checked' : ''}}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="validationCustom05">Mon</label>
                                        <br>
                                        <input type="hidden" name="Mon" value="N">
                                        <input type="checkbox" class="form-check-input" name="Mon" id="Mon" value="Y" {{ old('Mon') == 'Y' ? 'checked' : '' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="validationCustom05">Tue</label>
                                        <br>
                                        <input type="hidden" name="Tue" value="N">
                                        <input type="checkbox" class="form-check-input" name="Tue" id="Tue" value="Y" {{ old('Tue') == 'Y' ? 'checked' : '' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="validationCustom05">Wed</label>
                                        <br>
                                        <input type="hidden" name="Wed" value="N">
                                        <input type="checkbox" class="form-check-input" name="Wed" id="Wed" value="Y" {{ old('Wed') == 'Y' ? 'checked' : '' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="validationCustom05">Thu</label>
                                        <br>
                                        <input type="hidden" name="Thu" value="N">
                                        <input type="checkbox" class="form-check-input" name="Thu" id="Thu" value="Y" {{ old('Thu') == 'Y' ? 'checked' : '' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="validationCustom05">Fri</label>
                                        <br>
                                        <input type="hidden" name="Fri" value="N">
                                        <input type="checkbox" class="form-check-input" name="Fri" id="Fri" value="Y" {{ old('Fri') == 'Y' ? 'checked' : '' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="validationCustom05">Sat</label>
                                        <br>
                                        <input type="hidden" name="Sat" value="N">
                                        <input type="checkbox" class="form-check-input" name="Sat" id="Sat" value="Y" {{ old('Sat') == 'Y' ? 'checked' : '' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">Old Medicare #</label>
                                        <input type="text" class="form-control" name="medicare_no" id="medicare_no" data-mask="AAA-AA-AAAA-A">

                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">New Medicare #</label>
                                        <input type="text" class="form-control" name="new_medicare_no" id="new_medicare_no" data-mask="AAAA-AAA-AAAA" maxlength="25">

                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="validationCustom05">Hospital Part A</label>
                                        <br>
                                        <input type="hidden" name="parta_yn" value="N">
                                        <input type="checkbox" class="form-check-input" name="parta_yn" id="parta_yn" value="Y" {{ old('parta_yn') == 'Y' ? 'checked' : '' }}>

                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="validationCustom05">Medical Part B</label>
                                        <br>
                                        <input type="hidden" name="partb_yn" value="N">
                                        <input type="checkbox" class="form-check-input" name="partb_yn" id="partb_yn" value="Y" {{ old('partb_yn') == 'Y' ? 'checked' : '' }}>

                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom03">Supplimentry Insurance</label>
                                        <select name="suppliment_insu" id="suppliment_insu" class="form-select">
                                            <option value="">--- Select --</option>
                                            <option value="AARP">AARP</option>
                                            <option value="BCBS">BCBS</option>
                                            <option value="AETNA">AETNA</option>
                                            <option value="AMERI GRP">AMERI GRP</option>
                                            <option value="OTHERS">OTHERS</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <hr color="yellow" size="5" noshade>
                            <h5 class="font-size-20 mb-4 text-success"><i class="mdi mdi-arrow-right text-primary me-1"></i>Medical History</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom03">Admitting Diagnosis</label>
                                        <input type="text" class="form-control" name="AdmittingDiagnosis1" id="AdmittingDiagnosis1" maxlength="15">

                                    </div>
                                </div>
                                <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Primary Diagnosis (ICD-10)</label>
                                    <select id="PrimaryDiagnosis1" name="PrimaryDiagnosis1" class="form-control icd10-select">
                                    @if(old('PrimaryDiagnosis1'))
                                        <option value="{{ old('PrimaryDiagnosis1') }}" selected>
                                        {{ old('PrimaryDiagnosis1') }}
                                        </option>
                                    @endif
                                    </select>
                                </div>
                                </div>

                                <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Secondary Diagnosis (ICD-10)</label>
                                    <select id="SecondaryDiag1" name="SecondaryDiag1" class="form-control icd10-select">
                                    @if(old('SecondaryDiag1'))
                                        <option value="{{ old('SecondaryDiag1') }}" selected>
                                        {{ old('SecondaryDiag1') }}
                                        </option>
                                    @endif
                                    </select>
                                </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">Other Diagnosis</label>
                                        <input type="text" class="form-control" name="OtherDiag1" id="OtherDiag1" maxlength="15">

                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom03">Allergies 1</label>
                                        <input type="text" class="form-control" name="AdmittingDiagnosis2" id="AdmittingDiagnosis2" maxlength="15">

                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Allergies 2</label>
                                        <input type="text" class="form-control" name="PrimaryDiagnosis2" id="PrimaryDiagnosis2" maxlength="15">

                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">Allergies 3</label>
                                        <input type="text" class="form-control" name="SecondaryDiag2" id="SecondaryDiag2" maxlength="15">

                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">Allergies 4</label>
                                        <input type="text" class="form-control" name="OtherDiag2" id="OtherDiag2" maxlength="15">

                                    </div>
                                </div>
                            </div>
                            <div class="row d-flex align-items-stretch">
                                <div class="col-xl-8">
                                    <div class="card border-primary border h-100">
                                        <div class="card-body">
                                            <h4 class="text-center text-primary">Monitoring & Parameters</h4>

                                            <div class="row">
                                                <div class="col-xl-6">
                                                    <!-- Blood Pressure -->
                                                    <div class="mb-2 d-flex align-items-center">
                                                        <x-dynamic-dropdown
                                                        name="bloodpressure"
                                                        table="monitordropdown"
                                                        id="bloodpressure"
                                                        label="Blood Pressure"
                                                        :selected="old('bloodpressure') ?? null" />
                                                        <input type="text" class="form-control w-75 ms-2" name="Allergies1" id="Allergies1" maxlength="20">
                                                    </div>

                                                    <!-- Blood Sugar -->
                                                    <div class="mb-2 d-flex align-items-center">
                                                       <x-dynamic-dropdown
                                                        name="bloodsugar"
                                                        table="monitordropdown"
                                                        id="bloodsugar"
                                                        label="Blood Sugar"
                                                        :selected="old('bloodsugar') ?? null"/>
                                                        <input type="text" class="form-control w-75 ms-2" name="Allergies2" id="Allergies2" maxlength="20">
                                                    </div>

                                                    <!-- Weight -->
                                                    <div class="mb-2 d-flex align-items-center">
                                                        <x-dynamic-dropdown
                                                        name="weight"
                                                        table="monitordropdown"
                                                        id="weight"
                                                        label="Weight"
                                                        :selected="old('weight') ?? null"/>
                                                        <input type="text" class="form-control w-75 ms-2" name="Allergies3" id="Allergies3" maxlength="20">
                                                    </div>

                                                    <!-- Others -->
                                                    <div class="mb-2 d-flex align-items-center">
                                                        <label for="Allergies4" class="me-2" style="min-width: 120px;">Others</label>
                                                        <input type="text" class="form-control w-75" name="others" id="others" maxlength="20">
                                                    </div>
                                                    <div class="mb-1 d-flex align-items-center">
                                                        <label for="Allergies4" class="me-2" style="min-width: 120px;"></label>
                                                        <input type="text" class="form-control w-75" name="" id="" readonly >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ADL Section -->
                                <div class="col-xl-4">
                                    <div class="card border-success border h-100">
                                        <div class="card-body">
                                            <h4 class="text-center text-success">ADL</h4>
                                            <div class="form-check mb-3">
                                                <input type="hidden" name="bathing_yn" value="N">
                                                <input class="form-check-input" type="checkbox" name="bathing_yn" id="bathing_yn" value="Y" {{ old('bathing_yn') == 'Y' ? 'checked' : '' }}>&nbsp;
                                                <label class="form-check-label" for="formCheck1">
                                                    Bathing
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="hidden" name="toilet_yn" value="N">
                                                <input class="form-check-input" type="checkbox" name="toilet_yn" id="toilet_yn" value="Y" {{ old('toilet_yn') == 'Y' ? 'checked' : '' }}>&nbsp;
                                                <label class="form-check-label" for="formCheck1">
                                                    Toilet
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="hidden" name="transfer_yn" value="N">
                                                <input class="form-check-input" type="checkbox" name="transfer_yn" id="transfer_yn" value="Y" {{ old('transfer_yn') == 'Y' ? 'checked' : '' }}>&nbsp;
                                                <label class="form-check-label" for="formCheck1">
                                                    Transfer
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="hidden" name="locomotion_yn" value="N">
                                                <input class="form-check-input" type="checkbox" name="locomotion_yn" id="locomotion_yn" value="Y" {{ old('locomotion_yn') == 'Y' ? 'checked' : '' }}>&nbsp;
                                                <label class="form-check-label" for="formCheck1">
                                                    Locomotion
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="hidden" name="bed_mobility_yn" value="N">
                                                <input class="form-check-input" type="checkbox" name="bed_mobility_yn" id="bed_mobility_yn" value="Y" {{ old('bed_mobility_yn') == 'Y' ? 'checked' : '' }}>&nbsp;
                                                <label class="form-check-label" for="formCheck1">
                                                    Bed Mobility
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="hidden" name="eating_yn" value="N">
                                                <input class="form-check-input" type="checkbox" name="eating_yn" id="eating_yn" value="Y" {{ old('eating_yn') == 'Y' ? 'checked' : '' }}>&nbsp;
                                                <label class="form-check-label" for="formCheck1">
                                                    Eating
                                                </label>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>


                            </div>
                            <!-- end row -->
                            <hr>
                            <hr color="green" size="5" noshade>
                            <h5 class="font-size-20 mb-4 text-danger"><i class="mdi mdi-arrow-right text-primary me-1"></i>Special Instructions</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom03">Special Needs</label>
                                        <input type="text" class="form-control" name="SpecialNeeds1" id="SpecialNeeds1" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid city.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">Impairment</label>
                                        <input type="text" class="form-control" name="Impairement1" id="Impairement1" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom03">Nurse</label>
                                        <input type="text" class="form-control" name="Nurse1" id="Nurse1" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid city.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="SpecialNeeds2"id="SpecialNeeds2" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid state.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="Impairement2"id="Impairement2" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="Nurse2" id="Nurse2" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom03">Dietery Restrictions 1</label>
                                        <input type="text" class="form-control" name="DiateryRestriction1" id="DiateryRestriction1" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid city.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Dietery Restrictions 2</label>
                                        <input type="text" class="form-control" name="DiateryRestriction2" id="DiateryRestriction2" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid state.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">Dietery Restrictions 3</label>
                                        <input type="text" class="form-control" name="DiateryRestriction3" id="DiateryRestriction3" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr color="red" size="5" noshade>
                            <h5 class="font-size-20 mb-4 text-info"><i class="mdi mdi-arrow-right text-primary me-1"></i>Insurance/Providers information</h5>
                            <div class="row">
                                <div class="col-xl-12">
                                    <!-- Blood Pressure -->
                                    <div class="mb-2 d-flex align-items-center">
                                        <x-dynamic-dropdown
                                        name="HMO_Enrolled"
                                        table="hmodropdown"
                                        id="HMO_Enrolled"
                                        label="MediCAID HMO"
                                        :selected="old('HMO_Enrolled') ?? null"/>

                                        <label class="form-label mt-2 ms-4" for="Date">Date</label>&nbsp;&nbsp;
                                        <input class="form-control" name="HMO_Enrolled_date" type="date" value="" id="HMO_Enrolled_date">

                                        <label class="form-label mt-2 ms-4" for="Date">Id#</label>&nbsp;&nbsp;
                                        <input type="text" class="form-control" name="medicaid_HMO_id" id="medicaid_HMO_id">
                                    </div>

                                    <!-- Blood Sugar -->
                                    <div class="mb-2 d-flex align-items-center">
                                        <x-dynamic-dropdown
                                        name="medicare_HMO_Enrolled"
                                        table="hmodropdown"
                                        id="medicare_HMO_Enrolled"
                                        label="MediCARE HMO"
                                        :selected="old('medicare_HMO_Enrolled') ?? null"/>
                                        <label class="form-label mt-2 ms-4" for="Date">Date</label>&nbsp;&nbsp;
                                        <input class="form-control" name="medicare_HMO_En_date" type="date" value="" id="medicare_HMO_En_date">

                                        <label class="form-label mt-2 ms-4" for="Date">Id#</label>&nbsp;&nbsp;
                                        <input type="text" class="form-control" name="medicare_HMO_id" id="medicare_HMO_id">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                    <div class="col-lg-4 col-sm-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">MediCAID Details</h5>
                                                <div class="mb-3">
                                                    <x-dynamic-dropdown
                                                    name="HMO_Enrolled"
                                                    table="hmodropdown"
                                                    id="HMO_Enrolled"
                                                    label="HMO"
                                                    :selected="old('HMO_Enrolled') ?? null"/>
                                                    <div class="invalid-feedback">Please provide a valid city.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="state1">ID#</label>
                                                    <input type="text" class="form-control" name="medicaid_HMO_id" id="medicaid_HMO_id">
                                                    <div class="invalid-feedback">Please provide a valid state.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="zip1">Group</label>
                                                    <input type="text" class="form-control" name="medicaid_HMO_grp" id="medicaid_HMO_grp">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="zip1">PA#</label>
                                                    <input type="text" class="form-control" name="medicaid_PA_no" id="medicaid_PA_no">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label" for="phone1">Start Date</label>
                                                        <input class="form-control" name="medicaid_PA_Start" type="date" value="" id="medicaid_PA_Start">
                                                        <div class="invalid-feedback">Please provide a valid zip.</div>
                                                    </div>


                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label" for="zip1">End Date</label>
                                                        <input class="form-control" name="medicaid_PA_End" type="date" value="" id="medicaid_PA_End">
                                                        <div class="invalid-feedback">Please provide a valid zip.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-sm-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Renewal 1</h5>
                                                <div class="mb-3">
                                                    <x-dynamic-dropdown
                                                    name="renew1_HMO_Enrolled"
                                                    table="hmodropdown"
                                                    id="renew1_HMO_Enrolled"
                                                    label="HMO"
                                                    :selected="old('renew1_HMO_enrolled') ?? null"/>
                                                    <div class="invalid-feedback">Please provide a valid city.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="state1">ID#</label>
                                                    <input type="text" class="form-control" name="renew1_HMO_id" id="renew1_HMO_id">
                                                    <div class="invalid-feedback">Please provide a valid state.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="zip1">Group</label>
                                                    <input type="text" class="form-control" name="renew1_HMO_grp" id="renew1_HMO_grp">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="zip1">PA#</label>
                                                    <input type="text" class="form-control" name="renew1_PA_no" id="renew1_PA_no">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label" for="phone1">Start Date</label>
                                                        <input class="form-control" name="renew1_PA_Start" type="date" value="" id="renew1_PA_Start">
                                                        <div class="invalid-feedback">Please provide a valid zip.</div>
                                                    </div>


                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label" for="zip1">End Date</label>
                                                        <input class="form-control" name="renew1_PA_End" type="date" value="" id="renew1_PA_End">
                                                        <div class="invalid-feedback">Please provide a valid zip.</div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-sm-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">HNP Information</h5>
                                                <div class="mb-3">
                                                    <textarea class='form-control' id="hnpinfo" rows="8"></textarea>
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="zip1">HNP Expiry Date</label>
                                                    <input class="form-control" name="HNP_Expiry_date" type="date" value="" id="HNP_Expiry_date">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>

                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="zip1">HNP Renewal Date</label>
                                                    <input class="form-control" name="HNP_Renewal_date" type="date" value="HNP_Renewal_date" id="HNP_Renewal_date">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <hr color="yellow" size="5" noshade>
                            <h5 class="font-size-20 mb-4 text-primary">
                                <i class="mdi mdi-arrow-right text-primary me-1"></i>HMO/HNP/Pharmacy Information
                            </h5>

                            <div class="row">
                                <div class="col-lg-4 col-sm-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Primary Doctor Details</h5>
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="city1">Name</label>
                                                    <input type="text" class="form-control" name="primary_doctor_nm" id="primary_doctor_nm">
                                                    <div class="invalid-feedback">Please provide a valid city.</div>
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="city1">NPI#</label>
                                                    <input type="text" class="form-control" name="primary_doctor_NPI" id="primary_doctor_NPI">
                                                    <div class="invalid-feedback">Please provide a valid city.</div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="state1">Address</label>
                                                <input type="text" class="form-control" name="primary_doctor_add" id="primary_doctor_add">
                                                <div class="invalid-feedback">Please provide a valid state.</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="zip1">City/Zip</label>
                                                <input type="text" class="form-control" name="primary_doctor_city" id="primary_doctor_city">
                                                <div class="invalid-feedback">Please provide a valid zip.</div>
                                            </div>
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="phone1">Phone</label>
                                                    <input type="text" class="form-control" name="primary_doctor_ph" id="primary_doctor_ph" data-mask="(000)000-0000">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>


                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="zip1">Fax</label>
                                                    <input type="text" class="form-control" name="primary_doctor_fax" id="primary_doctor_fax" data-mask="(000)000-0000">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-sm-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4">Primary Doctors HMO Details</h5>
                                             <div class="mb-4 mt-4">
                                                <x-dynamic-dropdown
                                                name="primary_doctor_hmo1"
                                                table="hmodropdown"
                                                id="primary_doctor_hmo1"
                                                label=""
                                                :selected="old('primary_doctor_hmo1') ?? null"/>

                                            </div>
                                            <div class="mb-4 mt-4">
                                                <x-dynamic-dropdown
                                                name="primary_doctor_hmo2"
                                                table="hmodropdown"
                                                id="primary_doctor_hmo2"
                                                label=""
                                                :selected="old('primary_doctor_hmo2') ?? null"/>
                                            </div>
                                            <div class="mb-4 mt-4">
                                                <x-dynamic-dropdown
                                                name="primary_doctor_hmo3"
                                                table="hmodropdown"
                                                id="primary_doctor_hmo3"
                                                label=""
                                                :selected="old('primary_doctor_hmo3') ?? null"/>
                                            </div>
                                            <div class="mb-4 mt-4">
                                                <x-dynamic-dropdown
                                                name="primary_doctor_hmo4"
                                                table="hmodropdown"
                                                id="primary_doctor_hmo4"
                                                label=""
                                                :selected="old('primary_doctor_hmo4') ?? null"/>

                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-sm-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Pharmacy Details</h5>
                                            <div class="mb-3">
                                                <label class="form-label" for="city3">Name</label>
                                                <input type="text" class="form-control" name="pharmacy_nm" id="pharmacy_nm">
                                                <div class="invalid-feedback">Please provide a valid city.</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="state3">Address</label>
                                                <input type="text" class="form-control" name="pharmacy_add" id="pharmacy_add">
                                                <div class="invalid-feedback">Please provide a valid state.</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="zip3">City/Zip</label>
                                                <input type="text" class="form-control" name="pharmacy_city" id="pharmacy_city">
                                                <div class="invalid-feedback">Please provide a valid zip.</div>
                                            </div>
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="phone1">Phone</label>
                                                    <input type="text" class="form-control" name="pharmacy_phone" id="pharmacy_phone" data-mask="(000)000-0000">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>


                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="zip1">Fax</label>
                                                    <input type="text" class="form-control" name="pharmacy_fax" id="pharmacy_fax" data-mask="(000)000-0000">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr color="yellow" size="5" noshade>
                            <h5 class="font-size-20 mb-4 text-success"><i class="mdi mdi-arrow-right text-primary me-1"></i>General Information</h5>
                            <div class="row">
                                <button class="btn btn-primary" type="submit">Submit form</button>
                            </div>

                        </form>
                    </div>
                </div>
                <!-- end card -->
            </div> <!-- end col -->


        </div>
        <!-- end row -->
    </div> <!-- container-fluid -->
</div>
<!-- End Page-content -->
</div>
<!-- end main content-->
@section('scripts')

<script>
$(function () {

  function initIcd10(selector) {
    $(selector).each(function () {
      const $el = $(this);

      // if already initialized, skip
      if ($el.data('select2')) return;

      // ✅ choose a stable parent, fallback to body
      const $parent =
        $el.closest('.modal, .offcanvas, .card, .card-body, .page-content').first();

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
          data: function (params) {
            return { q: params.term || '', limit: 25 };
          },
          processResults: function (data) {
            return data;
          }
        }
      });
    });
  }

  initIcd10('#PrimaryDiagnosis1');
  initIcd10('#SecondaryDiag1');

});
</script>
@endsection


<script>
document.getElementById('ParticipantId').addEventListener('input', function () {

    let id = this.value.trim();
    // Remove leading zeros (e.g. "001" → "1")

    id = parseInt(id, 10);
    if (isNaN(id)) id = '';

    const imageElement = document.getElementById('participant_photo');

    if (id) {
        // Try to load the entered image dynamically
        const imageUrl = `/members/images/${id}.jpg`;

        // Create a temp image to check if file exists
        const testImage = new Image();
        testImage.onload = function () {
            imageElement.src = imageUrl;
        };
        testImage.onerror = function () {
            imageElement.src = "{{ asset('members/images/no_image.jpg') }}";
        };
        testImage.src = imageUrl;
    } else {
        imageElement.src = "{{ asset('members/images/no_image.jpg') }}";
    }
});

</script>
@endsection

