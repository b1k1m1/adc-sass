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

    .select2-container--open { z-index: 99999; }
    .select2-dropdown { z-index: 99999; }

</style>

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Edit Member - {{ str_pad( $member->ParticipantId ,3, '0',STR_PAD_LEFT) }} - {{ $member->LastName }}, {{ $member->FirstName }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('all.members') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <!-- AUDIT STRIP (Legacy fields: user_cd, update_dt, company_id) -->
        <div class="row mb-2">
            <div class="col-12">
                <div class="alert alert-light border d-flex flex-wrap gap-4 align-items-center py-2 mb-0">
                    <div>
                        <strong>Updated By:</strong>
                        @if(!empty($member->user_cd))
                            {{ $updatedByName ?? ($member->user_cd ?? '—') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    <div>
                        <strong>Last Updated:</strong>
                        @if(!empty($member->update_dt))
                            {{ \Carbon\Carbon::parse($member->update_dt)->format('m/d/Y') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="font-size-20 mb-4 text-primary"><i class="mdi mdi-arrow-right text-primary me-1"></i>General Information</h5>
                        <form class="needs-validation" novalidate action="{{ route('member.update') }}" method="POST">
                            @csrf
                            <div class="row d-flex align-items-center">
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label class="form-label" for="ID">ID</label>
                                        <input type="text" class="form-control" id="ParticipantId" name="ParticipantId"
                                               value="{{ str_pad( $member->ParticipantId ,3, '0',STR_PAD_LEFT) }}" readonly>
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
                                        :selected="$member->Trans_flag ?? null"
                                        required />
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="hold_date">Date</label>
                                        <input class="form-control flatpickr" name="hold_date" type="text" id="hold_date"
                                        value="{{ $member->hold_date ? \Carbon\Carbon::parse($member->hold_date)->format('m/d/Y') :''}}">
                                       <!-- <small id="date_help" class="text-muted">MM/DD/YYYY format</small> -->
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="reasonfordischarge">Reason for Discharge</label>
                                        <select name="reasonfordischarge" id="reasonfordischarge" class="form-select">
                                            <option value="" {{ old('reasonfordischarge', trim($member->reasonfordischarge)) === '' ? 'selected' : '' }}> --- Select --- </option>
                                            <option value="DDD PROGRAM" {{ old('reasonfordischarge', trim($member->reasonfordischarge)) === 'DDD PROGRAM' ? 'selected' : '' }}>DDD PROGRAM</option>
                                            <option value="DECEASED" {{ old('reasonfordischarge', trim($member->reasonfordischarge)) === 'DECEASED' ? 'selected' : '' }}>DECEASED</option>
                                            <option value="HOME" {{ old('reasonfordischarge', trim($member->reasonfordischarge)) === 'HOME' ? 'selected' : '' }}>HOME</option>
                                            <option value="LONG TERM CARE FACILITY" {{ old('reasonfordischarge', trim($member->reasonfordischarge)) === 'LONG TERM CARE FACILITY' ? 'selected' : '' }}>LONG TERM CARE FACILITY</option>
                                            <option value="N/A" {{ old('reasonfordischarge', trim($member->reasonfordischarge)) === 'N/A' ? 'selected' : '' }}>N/A</option>
                                            <option value="PARTIAL CARE PROGRAM" {{ old('reasonfordischarge', trim($member->reasonfordischarge)) === 'PARTIAL CARE PROGRAM' ? 'selected' : '' }}>PARTIAL CARE PROGRAM</option>
                                            <option value="REHABILATION/SUB-ACTIVE" {{ old('reasonfordischarge', trim($member->reasonfordischarge)) === 'PARTIAL CARE PROGRAM' ? 'selected' : '' }}>REHABILATION/SUB-ACTIVE</option>
                                            <option value="SUBSTANCE ABUSE PROGRAM" {{ old('reasonfordischarge', trim($member->reasonfordischarge)) === 'SUBSTANCE ABUSE PROGRAM' ? 'selected' : '' }}>SUBSTANCE ABUSE PROGRAM</option>
                                            <option value="TRANSFER TO ANOTHER AMDC" {{ old('reasonfordischarge', trim($member->reasonfordischarge)) === 'TRANSFER TO ANOTHER AMDC' ? 'selected' : '' }}>TRANSFER TO ANOTHER AMDC</option>
                                        </select>

                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Medicaid #</label>
                                        <input type="text" name="MedicaidNo" class="form-control" id="MedicaidNo" value="{{$member->MedicaidNo }}">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex justify-content-center align-items-center">
                                    <div>
                                        @php
                                            $numericId = (int) trim($member->ParticipantId); // '005 ' → 5
                                            $imageFilename = $numericId . '.jpg';
                                            $imagePath = 'members/images/' . $imageFilename;
                                            $fullImagePath = public_path($imagePath);
                                            $displayPath = file_exists($fullImagePath) ? asset($imagePath) : asset('members/images/no_image.jpg');
                                        @endphp
                                    <img src="{{ $displayPath }}" alt="Participant Photo" width="150" height="150">
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="row">
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom01">SS #</label>
                                        <!-- <input type="text" name="SSNo" class="form-control" id="secure-mask" placeholder="SS #" value="" pattern="\d{3}-?\d{2}-?\d{4}" required> -->
                                        <input type="text" name="SSNo" class="form-control" id="SSNo" value="{{ $member->SSNo }}" data-mask="000-00-0000" required>
                                        <div class="valid-feedback">
                                                Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom01">Last name</label>
                                        <input type="text" name="LastName" class="form-control" id="LastName" value="{{ $member->LastName }}" maxlength="15" required>
                                    <div class="valid-feedback">
                                            Looks good!
                                    </div>
                                </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">First name</label>
                                        <input type="text" name="FirstName" class="form-control" id="FirstName" value="{{ $member->FirstName }}" maxlength="15" required>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom01">Mi</label>
                                        <input type="text" name="MiddleName" class="form-control" id="MiddleName" value="{{ $member->MiddleName }}" maxlength="1">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="Sex">Sex</label>
                                        <select name="Sex" id="Sex" class="form-select">
                                            <option value=""> --- Select ---</option>
                                            <option value="M" {{ old('Sex', trim($member->Sex)) === 'M' ? 'selected' : '' }}>Male</option>
                                            <option value="F" {{ old('Sex', trim($member->Sex)) === 'F' ? 'selected' : '' }}>Female</option>
                                        </select>
                                    </div>
                                    <div class="invalid-feedback">
                                        Please provide a valid sex.
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="MaritalStatus">Marital Staus</label>
                                        <select name="MaritalStatus" id="MaritalStatus" class="form-select">
                                            <option value=""> --- Select --- </option>
                                            <option value="M" {{ old('MaritalStatus', trim($member->MaritalStatus)) === 'M' ? 'selected' : '' }}>Married</option>
                                            <option value="S" {{ old('MaritalStatus', trim($member->MaritalStatus)) === 'S' ? 'selected' : '' }}>Single</option>
                                            <option value="W" {{ old('MaritalStatus', trim($member->MaritalStatus)) === 'W' ? 'selected' : '' }}>Widow</option>
                                            <option value="D" {{ old('MaritalStatus', trim($member->MaritalStatus)) === 'D' ? 'selected' : '' }}>Divorcee</option>
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
                                        <input type="text" class="form-control" name="Address1" id="Address1" maxlength="15" value="{{ $member->Address1 }}" required>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Address2</label>
                                        <input type="text" class="form-control" name="Address2" id="Address2" maxlength="15" value="{{ $member->Address2 }}">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom01">City</label>
                                        <input type="text" class="form-control" name="City" id="City" maxlength="15" value="{{ $member->City }}" required>
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
                                        <input type="text" class="form-control" name="ZipCode" id="ZipCode" value="{{ $member->ZipCode }}" required maxlength="5" data-mask="00000">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom01">County</label>
                                        <input type="text" class="form-control" name="county" id="county" value="{{ $member->county }}" maxlength="15">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Home Phone</label>
                                        <input type="text" class="phone-mask form-control" name="Phone1" id="Phone1" value="{{ $member->Phone1 }}" data-mask="(000)000-0000">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Cell Phone</label>
                                        <input type="text" class="phone-mask form-control" name="Phone2" id="Phone2" value="{{ $member->Phone2 }}" data-mask="(000)000-0000">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Contact # 1</label>
                                        <input type="text" class="form-control" name="emergency_nm" id="emergency_nm" maxlength="25"
                                         value="{{$member->emergency_nm }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Contact # 1 Phone</label>
                                        <input type="text" class="phone-mask form-control" name="emergency_no" id="emergency_no" data-mask="(000)000-0000" maxlength="10"
                                        value="{{ $member->emergency_no }}">
                                    </div>
                                </div>
                            <div class=row>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Contact # 2</label>
                                        <input type="text" class="form-control" name="emegency_nm1" id="emegency_nm1" value="{{ $member->emegency_nm1 }}">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Contact # 2 Phone</label>
                                        <input type="text" class="phone-mask form-control" name="emergency_ext" id="emergency_ext" data-mask="(000)000-0000"
                                         value="{{ $member->emergency_ext }}">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Contact Relation</label>
                                        <input type="text" class="form-control" name="emergency_relation" id="emergency_relation" maxlength="25"
                                         value="{{ $member->emergency_relation }}">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Living with</label>
                                        <input type="text" class="form-control" name="LivingWith" id="LivingWith" maxlength="15" value="{{ $member->LivingWith }}">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Emergency Address1</label>
                                        <input type="text" class="form-control" name="emergency_add1" id="emergency_add1" maxlength="25" value="{{ $member->emergency_add1 }}">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Emergency Address2</label>
                                        <input type="text" class="form-control" name="emergency_add2" id="emergency_add2" maxlength="25" value="{{ $member->emergency_add2 }}">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom02">Emergency City</label>
                                        <input type="text" class="form-control" name="emergency_city" id="emergency_city" maxlength="20" value="{{ $member->emergency_city }}">
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
                                        <input type="text" class="form-control" name="emergency_zip" id="emergency_zip" maxlength="10" value="{{ $member->emergency_zip }}">
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="Date">Birth Date</label>
                                        <input class="form-control flatpickr" name="DateOfBirth" type="text"
                                        value="{{ $member->DateOfBirth ? \Carbon\Carbon::parse($member->DateOfBirth)->format('m/d/Y') : ''}}" id="DateOfBirth" required>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom03">Place of Birth</label>
                                        <input type="text" class="form-control" name="BirthPlace" id="BirthPlace" maxlength="25"
                                         value="{{ $member->BirthPlace }}">
                                        <div class="invalid-feedback">
                                            Please provide a valid city.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Country of Birth</label>
                                        <input type="text" class="form-control" name="BirthCountry" id="BirthCountry" maxlength="25" value="{{ $member->BirthCountry }}">
                                        <div class="invalid-feedback">
                                            Please provide a valid state.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label" for="USCitizen">US Citizen</label>
                                        <br>
                                        <input type="hidden" name="USCitizen" value="N">
                                        <input type="checkbox" class="form-check-input" name="USCitizen" id="USCitizen" value="Y" {{ $member->USCitizen == 'Y' ? 'checked' :'' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Religion</label>
                                        <input type="text" class="form-control" name="Religion" id="Religion" maxlength="15" value="{{ $member->Religion }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Language Spoken</label>
                                        <input type="text" class="form-control" name="LanguageSpoken" id="LanguageSpoken" maxlength="15" value="{{ $member->LanguageSpoken  }}">
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Comments</label>
                                        <input type="text" class="form-control" name="Comments" id="Comments" maxlength="99" value="{{ $member->Comments }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="insulin_yn">Insulin/Medication</label>
                                        <select name="insulin_yn" id="insulin_yn" class="form-select">
                                            <option value=""> --- Select --- </option>
                                            <option value="Y" {{ old('insulin_yn', trim($member->insulin_yn)) === 'Y' ? 'selected' : '' }}>Yes</option>
                                            <option value="N" {{ old('insulin_yn', trim($member->insulin_yn)) === 'N' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Driver</label>
                                        @php
                                            $selectedDriver = old('driverid', $member->driverid ?? '');
                                        @endphp
                                        <select name="driverid" id="driverid" class="form-select">
                                            <option value="">-- Select Driver --</option>
                                                @foreach ($employees as $e)
                                                    <option value="{{ $e->staffid }}" {{ $selectedDriver == $e->staffid ? 'selected' : '' }}>
                                                        {{ $e->stafflastname }}, {{ $e->stafffirstname }} ({{ $e->staffid }})
                                                    </option>
                                                @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Route</label>
                                        @php
                                            $selectedRoute = old('route', $member->Route ?? '');
                                        @endphp
                                        <select name="Route" id="Route" class="form-select">
                                            <option value="">-- Select Route --</option>
                                                @foreach ($routes as $r)
                                                    <option value="{{ $r->Route_id }}" {{ $selectedRoute == $r->Route_id ? 'selected' : '' }}>
                                                        {{ $r->Route_name }} ({{ $r->Route_id }})
                                                    </option>
                                                @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04"># Days Allowed</label>
                                        <input type="text" class="form-control" name="numberofdays" id="numberofdays" maxlength="2"
                                         value="{{ $member->numberofdays }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Admitted from</label>
                                        <input type="text" class="form-control" name="AdmittedFrom" id="AdmittedFrom"
                                        value="{{ $member->AdmittedFrom }}" maxlength="25">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Refered By</label>
                                        <input type="text" class="form-control" name="ReferedBy" id="ReferedBy"
                                        value="{{ $member->ReferedBy }}" maxlength="25">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Discharged Date</label>
                                        <input class="form-control flatpickr" name="DischargeDate" type="text"
                                         value="{{ $member->DischargeDate ? \Carbon\Carbon::parse($member->DischargeDate)->format('m/d/Y') :'' }}" id="DischargeDate">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Discharged To</label>
                                        <input type="text" class="form-control" name="DischargeTo" id="DischargeTo" value="{{ $member->DischargeTo }}" maxlength="25">
                                    </div>
                                </div>
                           </div>
                            <hr color="blue" size="5" noshade>
                            <h5 class="font-size-20 mb-4 text-warning"><i class="mdi mdi-arrow-right text-primary me-1"></i>Shift & Medicare Information</h5>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Admission Date</label>
                                        <input class="form-control flatpickr" name="AdmissionDate" type="text" id="AdmissionDate"
                                        value="{{ $member->AdmissionDate ? \Carbon\Carbon::parse($member->AdmissionDate)->format('m/d/Y') :'' }}" required>
                                        <div class="invalid-feedback">
                                            Please provide a valid state.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">Shift</label>
                                        <select name="Shift" id="Shift" class="form-select">
                                            <option value=""> --- Select --- </option>
                                            <option value="Morning" {{ old('Shift', trim($member->Shift)) === 'Morning' ? 'selected' : '' }}>Morning</option>
                                            <option value="Afternoon" {{ old('Shift', trim($member->Shift)) === 'Afternoon' ? 'selected' : '' }}>Afternoon</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="Sun">Sun</label>
                                        <br>
                                        <input type="hidden" name="Sun" value="N">
                                        <input type="checkbox" class="form-check-input" name="Sun" id="Sun" value="Y" {{ $member->Sun == 'Y' ? 'checked' :'' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="Mon">Mon</label>
                                        <br>
                                        <input type="hidden" name="Mon" value="N">
                                        <input type="checkbox" class="form-check-input" name="Mon" id="Mon" value="Y" {{ $member->Mon == 'Y' ? 'checked' :'' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="Tue">Tue</label>
                                        <br>
                                        <input type="hidden" name="Tue" value="N">
                                        <input type="checkbox" class="form-check-input" name="Tue" id="Tue" value="Y" {{ $member->Tue == 'Y' ? 'checked' :'' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="Wed">Wed</label>
                                        <br>
                                        <input type="hidden" name="Wed" value="N">
                                        <input type="checkbox" class="form-check-input" name="Wed" id="Wed" value="Y" {{ $member->Wed == 'Y' ? 'checked' :'' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="Thu">Thu</label>
                                        <br>
                                        <input type="hidden" name="Thu" value="N">
                                        <input type="checkbox" class="form-check-input" name="Thu" id="Thu"  value="Y" {{ $member->Thu == 'Y' ? 'checked' :'' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="Fri">Fri</label>
                                        <br>
                                        <input type="hidden" name="Fri" value="N">
                                        <input type="checkbox" class="form-check-input" name="Fri" id="Fri" value="Y" {{ $member->Fri == 'Y' ? 'checked' :'' }}>
                                        <div class="invalid-feedback">
                                                Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="Sat">Sat</label>
                                        <br>
                                        <input type="hidden" name="Sat" value="N">
                                        <input type="checkbox" class="form-check-input" name="Sat" id="Sat" value="Y" {{ $member->Sat == 'Y' ? 'checked' :'' }}>
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
                                        <input type="text" class="form-control" name="medicare_no" id="medicare_no" data-mask="AAA-AA-AAAA-A"
                                        value="{{ $member->medicare_no }}">

                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">New Medicare #</label>
                                        <input type="text" class="form-control" name="new_medicare_no" id="new_medicare_no" data-mask="AAAA-AAA-AAAA"
                                         value="{{ $member->new_medicare_no }}" maxlength="25">

                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="parta_yn">Hospital Part A</label>
                                        <br>
                                        <input type="hidden" name="parta_yn" value="N">
                                        <input type="checkbox" class="form-check-input" name="parta_yn" id="parta_yn"  value="Y" {{ $member->partb_yn == 'Y' ? 'checked' :'' }} >

                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label class="form-label" for="partb_yn">Medical Part B</label>
                                        <br>
                                        <input type="hidden" name="partb_yn" value="N">
                                        <input type="checkbox" class="form-check-input" name="partb_yn" id="partb_yn" value="Y" {{ $member->partb_yn == 'Y' ? 'checked' :'' }}>

                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom03">Supplimentry Insurance</label>
                                        <select name="suppliment_insu" id="suppliment_insu" class="form-select">
                                            <option value=""> --- Select --- </option>
                                            <option value="AARP" {{ old('suppliment_insu', trim($member->suppliment_insu)) === 'AARP' ? 'selected' : ''}} >AARP</option>
                                            <option value="BCBS"{{ old('suppliment_insu', trim($member->suppliment_insu)) === 'BCBS' ? 'selected' : ''}} >BCBS</option>
                                            <option value="AETNA"{{ old('suppliment_insu', trim($member->suppliment_insu)) === 'AETNA' ? 'selected' : ''}} >AETNA</option>
                                            <option value="AMERI GRP"{{ old('suppliment_insu', trim($member->suppliment_insu)) === 'AMERI GRP' ? 'selected' : ''}} >AMERI GRP</option>
                                            <option value="OTHERS"{{ old('suppliment_insu', trim($member->suppliment_insu)) === 'OTHERS' ? 'selected' : ''}} >OTHERS</option>
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
                                        <input type="text" class="form-control" name="AdmittingDiagnosis1" id="AdmittingDiagnosis1"
                                        value="{{ $member->AdmittingDiagnosis1 }}" maxlength="15">

                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Primary Diagnosis (ICD-10)</label>
                                        <select id="PrimaryDiagnosis1" name="PrimaryDiagnosis1" class="form-control icd10-select">
                                        @php $p = old('PrimaryDiagnosis1', $member->PrimaryDiagnosis1 ?? ''); @endphp
                                        @if(!empty($p))
                                            <option value="{{ $p }}" selected>{{ $p }}</option>
                                        @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Secondary Diagnosis (ICD-10)</label>
                                        <select id="SecondaryDiag1" name="SecondaryDiag1" class="form-control icd10-select">
                                        @php $s = old('SecondaryDiag1', $member->SecondaryDiag1 ?? ''); @endphp
                                        @if(!empty($s))
                                            <option value="{{ $s }}" selected>{{ $s }}</option>
                                        @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">Other Diagnosis</label>
                                        <input type="text" class="form-control" name="OtherDiag1" id="OtherDiag1" maxlength="15" value="{{ $member->OtherDiag1 }}">

                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom03">Allergies 1</label>
                                        <input type="text" class="form-control" name="AdmittingDiagnosis2" id="AdmittingDiagnosis2"
                                        value="{{ $member->AdmittingDiagnosis2 }}" maxlength="15">

                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Allergies 2</label>
                                        <input type="text" class="form-control" name="PrimaryDiagnosis2" id="PrimaryDiagnosis2"
                                        value="{{ $member->PrimaryDiagnosis2 }}" maxlength="15">

                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">Allergies 3</label>
                                        <input type="text" class="form-control" name="SecondaryDiag2" id="SecondaryDiag2"
                                        value="{{ $member->SecondaryDiag2 }}" maxlength="15">

                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">Allergies 4</label>
                                        <input type="text" class="form-control" name="OtherDiag2" id="OtherDiag2"
                                         value="{{ $member->OtherDiag2 }}" maxlength="15">

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
                                                        :selected="$member->bloodpressure ?? null" />
                                                        <input type="text" class="form-control w-75 ms-2" name="Allergies1" id="Allergies1"
                                                         value="{{ $member->Allergies1 }}" maxlength="20">
                                                    </div>
                                                    <!-- Blood Sugar -->
                                                    <div class="mb-2 d-flex align-items-center">
                                                        <x-dynamic-dropdown
                                                        name="bloodsugar"
                                                        table="monitordropdown"
                                                        id="bloodsugar"
                                                        label="Blood Sugar"
                                                        :selected="$member->bloodsugar ?? null"/>
                                                        <input type="text" class="form-control w-75 ms-2" name="Allergies2" id="Allergies2"
                                                         value="{{ $member->Allergies2 }}" maxlength="20">
                                                    </div>
                                                    <!-- Weight -->
                                                    <div class="mb-2 d-flex align-items-center">
                                                        <x-dynamic-dropdown
                                                        name="weight"
                                                        table="monitordropdown"
                                                        id="weight"
                                                        label="Weight"
                                                        :selected="$member->weight ?? null"/>
                                                        <input type="text" class="form-control w-75 ms-2" name="Allergies3" id="Allergies3"
                                                         value="{{ $member->Allergies3 }}" maxlength="20">
                                                    </div>

                                                    <!-- Others -->
                                                    <div class="mb-2 d-flex align-items-center">
                                                        <label for="Allergies4" class="me-2" style="min-width: 120px;">Others</label>
                                                        <input type="text" class="form-control w-75" name="others" id="others"
                                                         value="{{ $member->others }}" maxlength="20">
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
                                                <input class="form-check-input" type="checkbox" name="bathing_yn" id="bathing_yn" value="Y" {{ $member->bathing_yn == 'Y' ? 'checked' :'' }}>&nbsp;
                                                <label class="form-check-label" for="bathing_yn" >
                                                    Bathing
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="hidden" name="toilet_yn" value="N">
                                                <input class="form-check-input" type="checkbox" name="toilet_yn" id="toilet_yn" value="Y" {{ $member->toilet_yn == 'Y' ? 'checked' :'' }}>&nbsp;
                                                <label class="form-check-label" for="toilet_yn" >
                                                    Toilet
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="hidden" name="transfer_yn" value="N">
                                                <input class="form-check-input" type="checkbox" name="transfer_yn" id="transfer_yn" value="Y" {{ $member->transfer_yn == 'Y' ? 'checked' :'' }}>&nbsp;
                                                <label class="form-check-label" for="transfer_yn">
                                                    Transfer
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="hidden" name="locomotion_yn" value="N">
                                                <input class="form-check-input" type="checkbox" name="locomotion_yn" id="locomotion_yn" value="Y" {{ $member->locomotion_yn == 'Y' ? 'checked' :'' }}>&nbsp;
                                                <label class="form-check-label" for="locomotion_yn">
                                                    Locomotion
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="hidden" name="bed_mobility_yn" value="N">
                                                <input class="form-check-input" type="checkbox" name="bed_mobility_yn" id="bed_mobility_yn" value="Y" {{ $member->bed_mobility_yn == 'Y' ? 'checked' :'' }}>&nbsp;
                                                <label class="form-check-label" for="bed_mobility_yn">
                                                    Bed Mobility
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="hidden" name="eating_yn" value="N">
                                                <input class="form-check-input" type="checkbox" name="eating_yn" id="eating_yn" value="Y" {{ $member->eating_yn == 'Y' ? 'checked' :'' }}>&nbsp;
                                                <label class="form-check-label" for="eating_yn">
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
                                        <input type="text" class="form-control" name="SpecialNeeds1" id="SpecialNeeds1"
                                         value="{{ $member->SpecialNeeds1 }}" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid city.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">Impairment</label>
                                        <input type="text" class="form-control" name="Impairement1" id="Impairement1"
                                        value="{{ $member->Impairement1 }}" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom03">Nurse</label>
                                        <input type="text" class="form-control" name="Nurse1" id="Nurse1"
                                         value="{{ $member->Nurse1 }}" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid city.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="SpecialNeeds2"id="SpecialNeeds2"
                                         value="{{ $member->SpecialNeeds2 }}" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid state.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="Impairement2"id="Impairement2"
                                         value="{{ $member->Impairement2 }}" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid zip.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="Nurse2" id="Nurse2"
                                         value="{{ $member->Nurse2 }}" maxlength="20">
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
                                        <input type="text" class="form-control" name="DiateryRestriction1" id="DiateryRestriction1"
                                         value="{{ $member->DiateryRestriction1 }}" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid city.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom04">Dietery Restrictions 2</label>
                                        <input type="text" class="form-control" name="DiateryRestriction2" id="DiateryRestriction2"
                                         value="{{ $member->DiateryRestriction2 }}" maxlength="20">
                                        <div class="invalid-feedback">
                                            Please provide a valid state.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="validationCustom05">Dietery Restrictions 3</label>
                                        <input type="text" class="form-control" name="DiateryRestriction3" id="DiateryRestriction3"
                                            value="{{ $member->DiateryRestriction3 }}" maxlength="20">
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
                                        :selected="$member->HMO_Enrolled ?? null"/>
                                        <label class="form-label mt-2 ms-4" for="Date">Date</label>&nbsp;&nbsp;
                                        <input class="form-control flatpickr" name="HMO_Enrolled_date" type="text"
                                        value="{{ $member->HMO_Enrolled_date ? \Carbon\Carbon::parse($member->HMO_Enrolled_date)->format('m/d/Y'):'' }}" id="HMO_Enrolled_date">

                                        <label class="form-label mt-2 ms-4" for="Date">Id#</label>&nbsp;&nbsp;
                                        <input type="text" class="form-control" name="medicaid_HMO_id" id="medicaid_HMO_id"
                                         value="{{ $member->medicaid_HMO_id }}">
                                    </div>

                                    <!-- Blood Sugar -->
                                    <div class="mb-2 d-flex align-items-center">
                                        <x-dynamic-dropdown
                                        name="medicare_HMO_Enrolled"
                                        table="hmodropdown"
                                        id="medicare_HMO_Enrolled"
                                        label="MediCARE HMO"
                                        :selected="$member->medicare_HMO_Enrolled ?? null"/>
                                        <label class="form-label mt-2 ms-4" for="Date">Date</label>&nbsp;&nbsp;
                                        <input class="form-control flatpickr" name="medicare_HMO_En_date" type="text"
                                        value="{{ $member->medicare_HMO_En_date ? \Carbon\Carbon::parse($member->medicare_HMO_En_date)->format('m/d/Y') : '' }}">

                                        <label class="form-label mt-2 ms-4" for="Date">Id#</label>&nbsp;&nbsp;
                                        <input type="text" class="form-control" name="medicare_HMO_id" id="medicare_HMO_id"
                                        value="{{ $member->medicare_HMO_id }}">
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
                                                    :selected="$member->HMO_Enrolled ?? null"/>

                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="state1">ID#</label>
                                                    <input type="text" class="form-control" name="medicaid_HMO_id" id="medicaid_HMO_id"
                                                     value="{{ $member->medicaid_HMO_id }}">
                                                    <div class="invalid-feedback">Please provide a valid state.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="zip1">Group</label>
                                                    <input type="text" class="form-control" name="medicaid_HMO_grp" id="medicaid_HMO_grp"
                                                     value="{{ $member->medicaid_HMO_grp }}">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="zip1">PA#</label>
                                                    <input type="text" class="form-control" name="medicaid_PA_no" id="medicaid_PA_no"
                                                    value="{{ $member->medicaid_PA_no }}">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label" for="phone1">Start Date</label>
                                                        <input class="form-control flatpickr" name="medicaid_PA_Start" type="text"
                                                        value="{{ $member->medicaid_PA_Start ? \Carbon\Carbon::parse($member->medicaid_PA_Start)->format('m/d/Y') :'' }}" id="medicaid_PA_Start">
                                                        <div class="invalid-feedback">Please provide a valid zip.</div>
                                                    </div>


                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label" for="zip1">End Date</label>
                                                        <input class="form-control flatpickr" name="medicaid_PA_End" type="text"
                                                        value="{{ $member->medicaid_PA_End ? \Carbon\Carbon::parse($member->medicaid_PA_End)->format('m/d/Y') :'' }}"  id="medicaid_PA_End">
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
                                                    :selected="$member->renew1_HMO_enrolled ?? null"/>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="state1">ID#</label>
                                                    <input type="text" class="form-control" name="renew1_HMO_id" id="renew1_HMO_id"
                                                    value="{{ $member->renew1_HMO_id }}">
                                                    <div class="invalid-feedback">Please provide a valid state.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="zip1">Group</label>
                                                    <input type="text" class="form-control" name="renew1_HMO_grp" id="renew1_HMO_grp"
                                                    value="{{ $member->renew1_HMO_grp }}">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="zip1">PA#</label>
                                                    <input type="text" class="form-control" name="renew1_PA_no" id="renew1_PA_no"
                                                    value="{{ $member->renew1_PA_no }}">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label" for="phone1">Start Date</label>
                                                        <input class="form-control flatpickr" name="renew1_PA_Start" type="text"
                                                        value="{{ $member->renew1_PA_Start ? \Carbon\Carbon::parse($member->renew1_PA_Start)->format('m/d/Y') :''}}" id="renew1_PA_Start">
                                                        <div class="invalid-feedback">Please provide a valid zip.</div>
                                                    </div>


                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label" for="zip1">End Date</label>
                                                        <input class="form-control flatpickr" name="renew1_PA_End" type="text"
                                                        value="{{ $member->renew1_PA_End ? \Carbon\Carbon::parse($member->renew1_PA_End)->format('m/d/Y') :'' }}" id="renew1_PA_End">
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
                                                    <input class="form-control flatpickr" name="HNP_Expiry_date" type="text"
                                                    value="{{ $member->HNP_Expiry_date ? \Carbon\Carbon::parse($member->HNP_Expiry_date)->format('m/d/Y') :'' }}" id="HNP_Expiry_date">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>

                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="zip1">HNP Renewal Date</label>
                                                    <input class="form-control flatpickr" name="HNP_Renewal_date" type="text"
                                                    value="{{ $member->HNP_Renewal_date ? \Carbon\Carbon::parse($member->HNP_Renewal_date)->format('m/d/Y') :''}}" id="HNP_Renewal_date">
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
                                                    <input type="text" class="form-control" name="primary_doctor_nm" id="primary_doctor_nm"
                                                    value="{{ $member->primary_doctor_nm }}">
                                                    <div class="invalid-feedback">Please provide a valid city.</div>
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="city1">NPI#</label>
                                                    <input type="text" class="form-control" name="primary_doctor_NPI" id="primary_doctor_NPI"
                                                    value="{{ $member->primary_doctor_NPI }}">
                                                    <div class="invalid-feedback">Please provide a valid city.</div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="state1">Address</label>
                                                <input type="text" class="form-control" name="primary_doctor_add" id="primary_doctor_add"
                                                 value="{{ $member->primary_doctor_add }}">
                                                <div class="invalid-feedback">Please provide a valid state.</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="zip1">City/Zip</label>
                                                <input type="text" class="form-control" name="primary_doctor_city" id="primary_doctor_city"
                                                 value="{{ $member->primary_doctor_city }}">
                                                <div class="invalid-feedback">Please provide a valid zip.</div>
                                            </div>
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="phone1">Phone</label>
                                                    <input type="text" class="form-control" name="primary_doctor_ph" id="primary_doctor_ph" data-mask="(000)000-0000"
                                                    value="{{ $member->primary_doctor_ph }}">
                                                    <div class="invalid-feedback">Please provide a valid zip.</div>
                                                </div>


                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="zip1">Fax</label>
                                                    <input type="text" class="form-control" name="primary_doctor_fax" id="primary_doctor_fax" data-mask="(000)000-0000"
                                                     value="{{ $member->primary_doctor_fax }}">
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
                                                :selected="$member->primary_doctor_hmo1 ?? null"/>

                                            </div>
                                            <div class="mb-4 mt-4">
                                                <x-dynamic-dropdown
                                                name="primary_doctor_hmo2"
                                                table="hmodropdown"
                                                id="primary_doctor_hmo2"
                                                label=""
                                                :selected="$member->primary_doctor_hmo2 ?? null"/>
                                            </div>
                                            <div class="mb-4 mt-4">
                                                <x-dynamic-dropdown
                                                name="primary_doctor_hmo3"
                                                table="hmodropdown"
                                                id="primary_doctor_hmo3"
                                                label=""
                                                :selected="$member->primary_doctor_hmo3 ?? null"/>
                                            </div>
                                            <div class="mb-4 mt-4">
                                                <x-dynamic-dropdown
                                                name="primary_doctor_hmo4"
                                                table="hmodropdown"
                                                id="primary_doctor_hmo4"
                                                label=""
                                                :selected="$member->primary_doctor_hmo4 ?? null"/>

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
                                                <input type="text" class="form-control" name="pharmacy_nm" id="pharmacy_nm"
                                                value="{{ $member->pharmacy_nm }}">
                                                <div class="invalid-feedback">Please provide a valid city.</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="state3">Address</label>
                                                <input type="text" class="form-control" name="pharmacy_add" id="pharmacy_add"
                                                value="{{ $member->pharmacy_add }}">
                                                <div class="invalid-feedback">Please provide a valid state.</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="zip3">City/Zip</label>
                                                <input type="text" class="form-control" name="pharmacy_city" id="pharmacy_city"
                                                value="{{ $member->pharmacy_city }}">
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
                                <button class="btn btn-primary" type="submit">Save Changes</button>
                                <input type="hidden" value="{{ str_pad( $member->ParticipantId ,3, '0',STR_PAD_LEFT) }}" name="ParticipantId">
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
@endsection

@section('scripts')

<script>
$(function () {
  function initIcd10(selector) {
    $(selector).each(function () {
      const $el = $(this);
      if ($el.data('select2')) return;

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

    $(document).ready(function() {

               // Apply the mask
        $('.phone-mask').mask('(000)000-0000');

        // On form submit, strip non-digits to get raw format
        $('form').on('submit', function() {
            $('.phone-mask').each(function() {
                const raw = $(this).cleanVal(); // gets the unmasked value
                $(this).val(raw); // replace masked value with raw digits
            });
        });

  });

  document.addEventListener('DOMContentLoaded', function() {
        // Initialize single date picker
        flatpickr(".flatpickr", {
            dateFormat: "m/d/Y",
            allowInput: true,

        });

        // Initialize range picker
        flatpickr(".flatpickr-range", {
            mode: "range",
            dateFormat: "m/d/Y"
        });
    });
</script>
@endsection






