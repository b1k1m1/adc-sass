@extends('admin.admin_dashboard')

@section('title', '90 Days Nursing Summary')

@section('admin')

@php
    $selectedParticipantId = old('participant_id', request('participant_id', $member->ParticipantId ?? ''));
    $selectedAssessmentDate = old('assessment_date', request('assessment_date', isset($assessmentDate) ? \Carbon\Carbon::parse($assessmentDate)->format('m/d/Y') : now()->format('m/d/Y')));

    $assessmentId = $assessment->id ?? null;

    function checkedYN($assessment, $field) {
        return old($field, $assessment->$field ?? 'N') === 'Y' ? 'checked' : '';
    }

    function oldValue($assessment, $field, $default = '') {
        return old($field, $assessment->$field ?? $default);
    }

    function selectedValue($assessment, $field, $value) {
        return old($field, $assessment->$field ?? '') === $value ? 'selected' : '';
    }
@endphp

<div class="page-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">90 Days Nursing Summary</h5>

                @if($assessmentId)
                    <a href="{{ route('nursing.90day.summary.pdf', $assessmentId) }}"
                       target="_blank"
                       class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                    </a>
                @endif
            </div>

            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Please fix the following:</strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Load Form --}}
                <form method="GET"
                      action="{{ route('nursing.90day.summary.load', $type) }}"
                      class="border rounded p-3 mb-4 bg-light">

                    <div class="row align-items-end">

                        <div class="col-md-2">
                            <label class="form-label">Shift</label>
                            <select id="shiftSelect" class="form-select">
                                <option value="A" {{ $type == 'A' ? 'selected' : '' }}>Morning</option>
                                <option value="N" {{ $type == 'N' ? 'selected' : '' }}>Noon</option>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Member</label>
                            <select name="participant_id" id="participant_id" class="form-select select2" required>
                                <option value="">Select Member</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->ParticipantId }}"
                                        {{ $selectedParticipantId == $m->ParticipantId ? 'selected' : '' }}>
                                        {{ str_pad($m->ParticipantId, 3, '0', STR_PAD_LEFT) }}
                                        - {{ $m->LastName }}, {{ $m->FirstName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Assessment Date</label>
                            <input type="text"
                                   name="assessment_date"
                                    id="assessment_date"
                                    class="form-control"
                                    value="{{ $selectedAssessmentDate }}"
                                    placeholder="mm/dd/yyyy"
                                    maxlength="10"
                                    autocomplete="off"
                                    required>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                Load
                            </button>
                        </div>

                    </div>
                </form>

                @if(isset($member) && isset($previousSummaries) && $previousSummaries->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            Previous 90-Day Summaries for
                            {{ str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT) }}
                            - {{ $member->LastName }}, {{ $member->FirstName }}
                        </div>

                        <div class="card-body table-responsive">
                            <table class="table table-bordered table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 120px;">Assessment Date</th>
                                        <th style="width: 100px;">Shift</th>
                                        <th>Patient Name</th>
                                        <th style="width: 160px;">Signature</th>
                                        <th style="width: 160px;">Last Updated</th>
                                        <th style="width: 180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($previousSummaries as $row)
                                        <tr class="{{ isset($assessment) && $assessment && $assessment->id == $row->id ? 'table-warning' : '' }}">
                                            <td>
                                                {{ !empty($row->assessment_date) ? \Carbon\Carbon::parse($row->assessment_date)->format('m/d/Y') : '' }}
                                            </td>

                                            <td>
                                                {{ $row->Trans_flag == 'A' ? 'Morning' : 'Noon' }}
                                            </td>

                                            <td>
                                                {{ $row->patient_name }}
                                            </td>

                                            <td>
                                                {{ $row->signature }}
                                            </td>

                                            <td>
                                                {{ !empty($row->updated_at) ? \Carbon\Carbon::parse($row->updated_at)->format('m/d/Y h:i A') : '' }}
                                            </td>

                                            <td>
                                                <a href="{{ route('nursing.90day.summary.load', [
                                                        'type' => $row->Trans_flag,
                                                        'participant_id' => $row->ParticipantId,
                                                        'assessment_date' => \Carbon\Carbon::parse($row->assessment_date)->format('m/d/Y'),
                                                    ]) }}"
                                                class="btn btn-sm btn-primary">
                                                    Edit
                                                </a>

                                                <a href="{{ route('nursing.90day.summary.pdf', $row->id) }}"
                                                target="_blank"
                                                class="btn btn-sm btn-danger">
                                                    PDF
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if(isset($member) && isset($previousSummaries) && $previousSummaries->count() == 0)
                    <div class="alert alert-secondary">
                        No previous 90-Day Nursing Summary found for this member and shift.
                    </div>
                @endif

                @if(isset($member))

                    <form method="POST" action="{{ route('nursing.90day.summary.save') }}">
                        @csrf

                        <input type="hidden" name="participant_id" value="{{ $member->ParticipantId }}">
                        <input type="hidden" name="Trans_flag" value="{{ $type }}">
                        <input type="hidden" name="assessment_date" value="{{ $selectedAssessmentDate }}">

                        {{-- Header --}}
                        <div class="border rounded p-3 mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Patient Name</label>
                                    <input type="text"
                                           class="form-control"
                                           value="{{ $member->LastName }}, {{ $member->FirstName }}"
                                           readonly>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">DOB</label>
                                    <input type="text"
                                           class="form-control"
                                           value="{{ !empty($member->DateOfBirth) ? \Carbon\Carbon::parse($member->DateOfBirth)->format('m/d/Y') : '' }}"
                                           readonly>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Date</label>
                                    <input type="text"
                                           class="form-control"
                                           value="{{ $selectedAssessmentDate }}"
                                           readonly>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Shift</label>
                                    <input type="text"
                                           class="form-control"
                                           value="{{ $type == 'A' ? 'Morning' : 'Noon' }}"
                                           readonly>
                                </div>
                            </div>
                        </div>

                        {{-- Mobility --}}
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                Mobility Status
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="mobility_ambulatory" value="Y" {{ checkedYN($assessment ?? null, 'mobility_ambulatory') }}>
                                            Ambulatory
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="mobility_wheelchair" value="Y" {{ checkedYN($assessment ?? null, 'mobility_wheelchair') }}>
                                            Wheelchair
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="mobility_cane" value="Y" {{ checkedYN($assessment ?? null, 'mobility_cane') }}>
                                            Cane
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="mobility_walker" value="Y" {{ checkedYN($assessment ?? null, 'mobility_walker') }}>
                                            Walker
                                        </label>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label class="form-label">Comments if necessary</label>
                                    <textarea name="mobility_comments" class="form-control" rows="2">{{ oldValue($assessment ?? null, 'mobility_comments') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Mental Status --}}
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                Mental Status
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label>
                                            <input type="checkbox" name="mental_alert" value="Y" {{ checkedYN($assessment ?? null, 'mental_alert') }}>
                                            Alert
                                        </label>
                                    </div>
                                    <div class="col-md-3">
                                        <label>
                                            <input type="checkbox" name="mental_oriented" value="Y" {{ checkedYN($assessment ?? null, 'mental_oriented') }}>
                                            Oriented
                                        </label>
                                    </div>
                                    <div class="col-md-3">
                                        <label>
                                            <input type="checkbox" name="mental_confused" value="Y" {{ checkedYN($assessment ?? null, 'mental_confused') }}>
                                            Confused
                                        </label>
                                    </div>
                                    <div class="col-md-3">
                                        <label>
                                            <input type="checkbox" name="mental_disoriented" value="Y" {{ checkedYN($assessment ?? null, 'mental_disoriented') }}>
                                            Disoriented
                                        </label>
                                    </div>
                                </div>

                                <label class="form-label">Changes in mental status</label>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label>
                                            <input type="checkbox" name="mental_change_same" value="Y" {{ checkedYN($assessment ?? null, 'mental_change_same') }}>
                                            Same
                                        </label>
                                    </div>
                                    <div class="col-md-3">
                                        <label>
                                            <input type="checkbox" name="mental_change_increase" value="Y" {{ checkedYN($assessment ?? null, 'mental_change_increase') }}>
                                            Increase
                                        </label>
                                    </div>
                                    <div class="col-md-3">
                                        <label>
                                            <input type="checkbox" name="mental_change_decrease" value="Y" {{ checkedYN($assessment ?? null, 'mental_change_decrease') }}>
                                            Decrease
                                        </label>
                                    </div>
                                </div>

                                <label class="form-label">Comments if necessary</label>
                                <textarea name="mental_comments" class="form-control" rows="2">{{ oldValue($assessment ?? null, 'mental_comments') }}</textarea>
                            </div>
                        </div>

                        {{-- Physical Assessment --}}
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                Physical Assessment
                            </div>
                            <div class="card-body">

                                <div class="row mb-3">
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="edema" value="Y" {{ checkedYN($assessment ?? null, 'edema') }}>
                                            Edema
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="edema_plus1" value="Y" {{ checkedYN($assessment ?? null, 'edema_plus1') }}>
                                            +1
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="edema_plus2" value="Y" {{ checkedYN($assessment ?? null, 'edema_plus2') }}>
                                            +2
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="edema_trace" value="Y" {{ checkedYN($assessment ?? null, 'edema_trace') }}>
                                            Trace
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text"
                                               name="edema_location"
                                               class="form-control"
                                               placeholder="Location"
                                               value="{{ oldValue($assessment ?? null, 'edema_location') }}">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label>Shortness of Breath</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="shortness_breath_yes" value="Y" {{ checkedYN($assessment ?? null, 'shortness_breath_yes') }}>
                                            Yes
                                        </label>
                                        <label class="me-3">
                                            <input type="checkbox" name="shortness_breath_no" value="Y" {{ checkedYN($assessment ?? null, 'shortness_breath_no') }}>
                                            No
                                        </label>
                                        <label>
                                            <input type="checkbox" name="shortness_breath_exertion" value="Y" {{ checkedYN($assessment ?? null, 'shortness_breath_exertion') }}>
                                            On Exertion
                                        </label>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Visual Acuity</label>
                                        <input type="text" name="visual_acuity" class="form-control" value="{{ oldValue($assessment ?? null, 'visual_acuity') }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label>Wears Glasses</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="wears_glasses_yes" value="Y" {{ checkedYN($assessment ?? null, 'wears_glasses_yes') }}>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="checkbox" name="wears_glasses_no" value="Y" {{ checkedYN($assessment ?? null, 'wears_glasses_no') }}>
                                            No
                                        </label>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label>Urinary Incontinence</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="urinary_incontinence_yes" value="Y" {{ checkedYN($assessment ?? null, 'urinary_incontinence_yes') }}>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="checkbox" name="urinary_incontinence_no" value="Y" {{ checkedYN($assessment ?? null, 'urinary_incontinence_no') }}>
                                            No
                                        </label>
                                    </div>

                                    <div class="col-md-3">
                                        <label>Bowel Incontinence</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="bowel_incontinence_yes" value="Y" {{ checkedYN($assessment ?? null, 'bowel_incontinence_yes') }}>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="checkbox" name="bowel_incontinence_no" value="Y" {{ checkedYN($assessment ?? null, 'bowel_incontinence_no') }}>
                                            No
                                        </label>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">BP Range From</label>
                                        <input type="text" name="bp_from" class="form-control" value="{{ oldValue($assessment ?? null, 'bp_from') }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">BP Range To</label>
                                        <input type="text" name="bp_to" class="form-control" value="{{ oldValue($assessment ?? null, 'bp_to') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Skin</label>
                                    <textarea name="skin" class="form-control" rows="2">{{ oldValue($assessment ?? null, 'skin') }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Diabetic</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="diabetic_yes" value="Y" {{ checkedYN($assessment ?? null, 'diabetic_yes') }}>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="checkbox" name="diabetic_no" value="Y" {{ checkedYN($assessment ?? null, 'diabetic_no') }}>
                                            No
                                        </label>
                                    </div>

                                    <div class="col-md-4">
                                        <label>Hypoglycemic Episodes</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="hypoglycemic_yes" value="Y" {{ checkedYN($assessment ?? null, 'hypoglycemic_yes') }}>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="checkbox" name="hypoglycemic_no" value="Y" {{ checkedYN($assessment ?? null, 'hypoglycemic_no') }}>
                                            No
                                        </label>
                                    </div>

                                    <div class="col-md-4">
                                        <label>Hyperglycemic Episodes</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="hyperglycemic_yes" value="Y" {{ checkedYN($assessment ?? null, 'hyperglycemic_yes') }}>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="checkbox" name="hyperglycemic_no" value="Y" {{ checkedYN($assessment ?? null, 'hyperglycemic_no') }}>
                                            No
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Medication --}}
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                Medication
                            </div>
                            <div class="card-body">

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label>Change in medications</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="medication_change_yes" value="Y" {{ checkedYN($assessment ?? null, 'medication_change_yes') }}>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="checkbox" name="medication_change_no" value="Y" {{ checkedYN($assessment ?? null, 'medication_change_no') }}>
                                            No
                                        </label>
                                    </div>
                                </div>

                                <label class="form-label">Reason</label>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label>
                                            <input type="checkbox" name="medication_reason_not_effective" value="Y" {{ checkedYN($assessment ?? null, 'medication_reason_not_effective') }}>
                                            Not effective
                                        </label>
                                    </div>
                                    <div class="col-md-3">
                                        <label>
                                            <input type="checkbox" name="medication_reason_does_not_tolerate" value="Y" {{ checkedYN($assessment ?? null, 'medication_reason_does_not_tolerate') }}>
                                            Does not tolerate
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="medication_reason_md_order" value="Y" {{ checkedYN($assessment ?? null, 'medication_reason_md_order') }}>
                                            MD order
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="medication_reason_patient_refused" value="Y" {{ checkedYN($assessment ?? null, 'medication_reason_patient_refused') }}>
                                            Patient refused
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text"
                                               name="medication_reason_other"
                                               class="form-control"
                                               placeholder="Other"
                                               value="{{ oldValue($assessment ?? null, 'medication_reason_other') }}">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label>Client's medications delivered to Day Care Center & checked by RN</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="meds_delivered_yes" value="Y" {{ checkedYN($assessment ?? null, 'meds_delivered_yes') }}>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="checkbox" name="meds_delivered_no" value="Y" {{ checkedYN($assessment ?? null, 'meds_delivered_no') }}>
                                            No
                                        </label>
                                    </div>

                                    <div class="col-md-3">
                                        <label>Is patient taking</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="antidepressants" value="Y" {{ checkedYN($assessment ?? null, 'antidepressants') }}>
                                            Antidepressants
                                        </label>
                                        <label>
                                            <input type="checkbox" name="antipsychotic" value="Y" {{ checkedYN($assessment ?? null, 'antipsychotic') }}>
                                            Antipsychotic
                                        </label>
                                    </div>

                                    <div class="col-md-2">
                                        <label>Side Effects</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="side_effects_yes" value="Y" {{ checkedYN($assessment ?? null, 'side_effects_yes') }}>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="checkbox" name="side_effects_no" value="Y" {{ checkedYN($assessment ?? null, 'side_effects_no') }}>
                                            No
                                        </label>
                                    </div>

                                    <div class="col-md-3">
                                        <label>Compliance with Tx Regimen</label><br>
                                        <label class="me-2">
                                            <input type="checkbox" name="tx_good" value="Y" {{ checkedYN($assessment ?? null, 'tx_good') }}>
                                            Good
                                        </label>
                                        <label class="me-2">
                                            <input type="checkbox" name="tx_fair" value="Y" {{ checkedYN($assessment ?? null, 'tx_fair') }}>
                                            Fair
                                        </label>
                                        <label>
                                            <input type="checkbox" name="tx_poor" value="Y" {{ checkedYN($assessment ?? null, 'tx_poor') }}>
                                            Poor
                                        </label>
                                    </div>
                                </div>

                                <label class="form-label">Medication Comments</label>
                                <textarea name="medication_comments" class="form-control" rows="2">{{ oldValue($assessment ?? null, 'medication_comments') }}</textarea>

                            </div>
                        </div>

                        {{-- Diet --}}
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                Diet
                            </div>
                            <div class="card-body">

                                <div class="row mb-3">
                                    <div class="col-md-5">
                                        <label>Standard house diet - low Na, 1800 Cal, Moderate cholesterol</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="diet_standard_yes" value="Y" {{ checkedYN($assessment ?? null, 'diet_standard_yes') }}>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="checkbox" name="diet_standard_no" value="Y" {{ checkedYN($assessment ?? null, 'diet_standard_no') }}>
                                            No
                                        </label>
                                    </div>

                                    <div class="col-md-3">
                                        <label>No concentrated sweets</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="diet_no_concentrated_sweets_yes" value="Y" {{ checkedYN($assessment ?? null, 'diet_no_concentrated_sweets_yes') }}>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="checkbox" name="diet_no_concentrated_sweets_no" value="Y" {{ checkedYN($assessment ?? null, 'diet_no_concentrated_sweets_no') }}>
                                            No
                                        </label>
                                    </div>

                                    <div class="col-md-4">
                                        <label>Other</label>
                                        <div class="input-group">
                                            <input type="text" name="diet_other" class="form-control" value="{{ oldValue($assessment ?? null, 'diet_other') }}">
                                            <span class="input-group-text">
                                                <input type="checkbox" name="diet_other_yes" value="Y" {{ checkedYN($assessment ?? null, 'diet_other_yes') }}> Y
                                            </span>
                                            <span class="input-group-text">
                                                <input type="checkbox" name="diet_other_no" value="Y" {{ checkedYN($assessment ?? null, 'diet_other_no') }}> N
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <label class="form-label">Diet Comments</label>
                                <textarea name="diet_comments" class="form-control" rows="2">{{ oldValue($assessment ?? null, 'diet_comments') }}</textarea>

                            </div>
                        </div>

                        {{-- Pain --}}
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                Pain Assessment
                            </div>
                            <div class="card-body">

                                <div class="row mb-3">
                                    @foreach([
                                        'pain_neck' => 'Neck',
                                        'pain_upper_back' => 'Upper Back',
                                        'pain_low_back' => 'Low Back',
                                        'pain_shoulder' => 'Shoulder',
                                        'pain_elbow' => 'Elbow',
                                        'pain_wrist' => 'Wrist',
                                        'pain_hip' => 'Hip',
                                        'pain_knee' => 'Knee',
                                        'pain_ankle' => 'Ankle',
                                        'pain_muscle' => 'Muscle pain',
                                    ] as $field => $label)
                                        <div class="col-md-2 mb-2">
                                            <label>
                                                <input type="checkbox" name="{{ $field }}" value="Y" {{ checkedYN($assessment ?? null, $field) }}>
                                                {{ $label }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Pain Score</label>
                                        <select name="pain_score" class="form-select">
                                            <option value="">Select</option>
                                            @for($i = 0; $i <= 10; $i++)
                                                <option value="{{ $i }}" {{ (string)oldValue($assessment ?? null, 'pain_score') === (string)$i ? 'selected' : '' }}>
                                                    {{ $i }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div class="col-md-5">
                                        <label>Patient underwent Physical Therapy or Pain Management Treatment</label><br>
                                        <label class="me-3">
                                            <input type="checkbox" name="pain_treatment_yes" value="Y" {{ checkedYN($assessment ?? null, 'pain_treatment_yes') }}>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="checkbox" name="pain_treatment_no" value="Y" {{ checkedYN($assessment ?? null, 'pain_treatment_no') }}>
                                            No
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- ADL --}}
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                ADL
                            </div>
                            <div class="card-body table-responsive">

                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 20%;">ADL</th>
                                            <th style="width: 25%;">Level</th>
                                            <th style="width: 25%;">ADL provided at ADHS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $adlRows = [
                                                'dressing' => 'Dressing',
                                                'toilet_use' => 'Toilet Use',
                                                'ambulation' => 'Ambulation',
                                                'locomotion' => 'Locomotion',
                                                'transfer' => 'Transfer',
                                                'eating' => 'Eating',
                                            ];
                                        @endphp

                                        @foreach($adlRows as $key => $label)
                                            @php
                                                $levelField = 'adl_' . $key . '_level';
                                                $yesField = 'adl_' . $key . '_provided_yes';
                                                $noField = 'adl_' . $key . '_provided_no';
                                            @endphp

                                            <tr>
                                                <td>{{ $label }}</td>
                                                <td>
                                                    <select name="{{ $levelField }}" class="form-select">
                                                        <option value="">Select</option>
                                                        <option value="independent" {{ selectedValue($assessment ?? null, $levelField, 'independent') }}>Independent</option>
                                                        <option value="supervising_cueing" {{ selectedValue($assessment ?? null, $levelField, 'supervising_cueing') }}>Supervising / Cueing</option>
                                                        <option value="limited_or_greater_assistance" {{ selectedValue($assessment ?? null, $levelField, 'limited_or_greater_assistance') }}>Limited or greater assistance</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <label class="me-3">
                                                        <input type="checkbox" name="{{ $yesField }}" value="Y" {{ checkedYN($assessment ?? null, $yesField) }}>
                                                        Yes
                                                    </label>
                                                    <label>
                                                        <input type="checkbox" name="{{ $noField }}" value="Y" {{ checkedYN($assessment ?? null, $noField) }}>
                                                        No
                                                    </label>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                        </div>

                        {{-- Clinic Visits --}}
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                Clinic Visits / Hospitalization / Other Notes
                            </div>
                            <div class="card-body">

                                <label class="form-label">Clinic Visits</label>
                                <div class="row mb-3">
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="clinic_podiatry" value="Y" {{ checkedYN($assessment ?? null, 'clinic_podiatry') }}>
                                            Podiatry
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="clinic_optometry" value="Y" {{ checkedYN($assessment ?? null, 'clinic_optometry') }}>
                                            Optometry
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="clinic_pain_clinic" value="Y" {{ checkedYN($assessment ?? null, 'clinic_pain_clinic') }}>
                                            Pain Clinic
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <label>
                                            <input type="checkbox" name="clinic_psychiatry" value="Y" {{ checkedYN($assessment ?? null, 'clinic_psychiatry') }}>
                                            Psychiatry
                                        </label>
                                    </div>
                                    <div class="col-md-1">
                                        <label>
                                            <input type="checkbox" name="clinic_pt" value="Y" {{ checkedYN($assessment ?? null, 'clinic_pt') }}>
                                            PT
                                        </label>
                                    </div>
                                    <div class="col-md-1">
                                        <label>
                                            <input type="checkbox" name="clinic_ot" value="Y" {{ checkedYN($assessment ?? null, 'clinic_ot') }}>
                                            OT
                                        </label>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="clinic_other" class="form-control" placeholder="Other" value="{{ oldValue($assessment ?? null, 'clinic_other') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Recent Hospitalization</label>
                                    <textarea name="recent_hospitalization" class="form-control" rows="2">{{ oldValue($assessment ?? null, 'recent_hospitalization') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Change in body picture, bruises, fractures, etc.</label>
                                    <textarea name="body_picture_change" class="form-control" rows="2">{{ oldValue($assessment ?? null, 'body_picture_change') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Adult Day Care Notes</label>
                                    <textarea name="long_note" class="form-control" rows="10">{{ oldValue($assessment ?? null, 'long_note') }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Signature</label>
                                        <input type="text" name="signature" class="form-control" value="{{ oldValue($assessment ?? null, 'signature') }}">
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="text-end mb-5">
                            <a href="{{ route('nursing.90day.summary', $type) }}" class="btn btn-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-success">
                                {{ $assessmentId ? 'Update Summary' : 'Save Summary' }}
                            </button>
                        </div>

                    </form>

                @else
                    <div class="alert alert-info">
                        Please select Shift, Member, Assessment Date, then click Load.
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function () {

        if ($.fn.select2) {
            $('#participant_id').select2({
                placeholder: 'Select Member',
                allowClear: true,
                width: '100%'
            });
        }

        $('#shiftSelect').on('change', function () {
            let selectedShift = $(this).val();
            let url = "{{ route('nursing.90day.summary', ':type') }}";
            url = url.replace(':type', selectedShift);
            window.location.href = url;
        });

        function isValidUSDate(dateText) {
            // Must be mm/dd/yyyy
            var regex = /^(0?[1-9]|1[0-2])\/(0?[1-9]|[12][0-9]|3[01])\/\d{4}$/;

            if (!regex.test(dateText)) {
                return false;
            }

            var parts = dateText.split('/');
            var month = parseInt(parts[0], 10);
            var day   = parseInt(parts[1], 10);
            var year  = parseInt(parts[2], 10);

            if (year < 1900 || year > 2100) {
                return false;
            }

            var testDate = new Date(year, month - 1, day);

            return testDate.getFullYear() === year &&
                testDate.getMonth() === month - 1 &&
                testDate.getDate() === day;
        }

        $('form').on('submit', function (e) {
            var dateValue = $('#assessment_date').val();

            if (!isValidUSDate(dateValue)) {
                e.preventDefault();

                alert('Please enter a valid Assessment Date in mm/dd/yyyy format.');

                $('#assessment_date').focus();
                return false;
            }
        });

    });
</script>
@endpush
