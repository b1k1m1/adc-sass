@extends('admin.admin_dashboard')

@section('title', 'Nursing Assessment Summary')

@section('admin')

<style>
    .custom-tab-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    border-bottom: none;
    margin-bottom: 18px;
    }

    .custom-tab-nav .nav-link {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        padding: 10px 14px;
        box-shadow: none;
        transition: all .2s ease-in-out;
    }

    .custom-tab-nav .nav-link i {
        margin-right: 6px;
    }

    .custom-tab-nav .nav-link:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    }

    .custom-tab-nav .nav-link.active {
        color: #fff !important;
        border-color: transparent;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.16);
    }

    /* Active tab colors */
    .custom-tab-nav .nav-link.medical-history.active {
        background: #0d6efd;
    }

    .custom-tab-nav .nav-link.vitals.active {
        background: #198754;
    }

    .custom-tab-nav .nav-link.functional.active {
        background: #6f42c1;
    }

    .custom-tab-nav .nav-link.mental.active {
        background: #dc3545;
    }

    .custom-tab-nav .nav-link.skilled.active {
        background: #fd7e14;
    }

    .custom-tab-nav .nav-link.skin.active {
        background: #20c997;
    }

    .custom-tab-nav .nav-link.signature.active {
        background: #0b5ed7;
    }

    .custom-tab-content {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px;
        background: #ffffff;
    }

    @media (max-width: 768px) {
        .custom-tab-nav .nav-link {
            width: 100%;
            text-align: left;
        }
    }
</style>

@php
    $selectedParticipantId = old('participant_id', request('participant_id', $member->ParticipantId ?? ''));

    $selectedAssessmentDate = old(
        'assessment_date',
        request(
            'assessment_date',
            isset($assessmentDate) ? \Carbon\Carbon::parse($assessmentDate)->format('m/d/Y') : now()->format('m/d/Y')
        )
    );

    $assessmentId = $assessment->id ?? null;

    $sections = $sections ?? [];

    $pmh      = $sections['past_medical_history'] ?? [];
    $vh       = $sections['vital_hospitalization'] ?? [];
    $fa       = $sections['functional_assessment'] ?? [];
    $bb       = $sections['bowel_bladder'] ?? [];
    $mbs      = $sections['mental_behavior_sleep'] ?? [];
    $skilled  = $sections['skilled_needs'] ?? [];
    $skin     = $sections['skin_assessment'] ?? [];
    $devices  = $sections['assistive_devices'] ?? [];
    $nutrition = $sections['nutritional_status'] ?? [];
    $pain     = $sections['pain_assessment'] ?? [];

    function checkedArr($array, $field) {
        return old($field, $array[$field] ?? 'N') === 'Y' ? 'checked' : '';
    }

    function oldArr($array, $field, $default = '') {
        return old($field, $array[$field] ?? $default);
    }

    function selectedArr($array, $field, $value) {
        return old($field, $array[$field] ?? '') === $value ? 'selected' : '';
    }

    function checkedMain($object, $field, $value) {
        return old($field, $object->$field ?? '') === $value ? 'checked' : '';
    }
@endphp

<div class="page-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Nursing Assessment Summary</h5>

                @if($assessmentId)
                    <a href="{{ route('nursing.assessment.summary.pdf', $assessmentId) }}"
                       target="_blank"
                       class="btn btn-danger">
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
                      action="{{ route('nursing.assessment.summary.load', $type) }}"
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
                                class="form-control datepicker"
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

                {{-- Previous Assessments --}}
                @if(isset($member) && isset($previousAssessments) && $previousAssessments->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            Previous Nursing Assessments for
                            {{ str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT) }}
                            - {{ $member->LastName }}, {{ $member->FirstName }}
                        </div>

                        <div class="card-body table-responsive">
                            <table class="table table-bordered table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 130px;">Assessment Date</th>
                                        <th style="width: 100px;">Shift</th>
                                        <th style="width: 160px;">Type</th>
                                        <th>Patient Name</th>
                                        <th style="width: 160px;">Signature</th>
                                        <th style="width: 170px;">Last Updated</th>
                                        <th style="width: 180px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($previousAssessments as $row)
                                        <tr class="{{ isset($assessment) && $assessment && $assessment->id == $row->id ? 'table-warning' : '' }}">
                                            <td>
                                                {{ !empty($row->assessment_date) ? \Carbon\Carbon::parse($row->assessment_date)->format('m/d/Y') : '' }}
                                            </td>

                                            <td>{{ $row->Trans_flag == 'A' ? 'Morning' : 'Noon' }}</td>

                                            <td>{{ ucwords(str_replace('_', ' ', $row->assessment_type ?? '')) }}</td>

                                            <td>{{ $row->patient_name }}</td>

                                            <td>{{ $row->signature }}</td>

                                            <td>
                                                {{ !empty($row->updated_at) ? \Carbon\Carbon::parse($row->updated_at)->format('m/d/Y h:i A') : '' }}
                                            </td>

                                            <td>
                                                <a href="{{ route('nursing.assessment.summary.load', [
                                                        'type' => $row->Trans_flag,
                                                        'participant_id' => $row->ParticipantId,
                                                        'assessment_date' => \Carbon\Carbon::parse($row->assessment_date)->format('m/d/Y'),
                                                    ]) }}"
                                                   class="btn btn-primary">
                                                    Edit
                                                </a>

                                                <a href="{{ route('nursing.assessment.summary.pdf', $row->id) }}"
                                                   target="_blank"
                                                   class="btn btn-danger">
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

                @if(isset($member))

                    <form method="POST" action="{{ route('nursing.assessment.summary.save') }}" id="assessmentForm">
                        @csrf

                        <input type="hidden" name="participant_id" value="{{ $member->ParticipantId }}">
                        <input type="hidden" name="Trans_flag" value="{{ $type }}">
                        <input type="hidden" name="assessment_date" value="{{ $selectedAssessmentDate }}">

                        {{-- Member Header --}}
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

                        {{-- Tabs --}}
                        <ul class="nav custom-tab-nav" id="assessmentTabs" role="tablist">

                            <li class="nav-item" role="presentation">
                                <button class="nav-link medical-history active"
                                        id="tab-pmh"
                                        data-bs-toggle="tab"
                                        data-bs-target="#pane-pmh"
                                        type="button"
                                        role="tab">
                                    <i class="fas fa-notes-medical"></i> Medical History
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link vitals"
                                        id="tab-vitals"
                                        data-bs-toggle="tab"
                                        data-bs-target="#pane-vitals"
                                        type="button"
                                        role="tab">
                                    <i class="fas fa-heartbeat"></i> Vitals / Hospital
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link functional"
                                        id="tab-functional"
                                        data-bs-toggle="tab"
                                        data-bs-target="#pane-functional"
                                        type="button"
                                        role="tab">
                                    <i class="fas fa-walking"></i> Functional
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link mental"
                                        id="tab-mental"
                                        data-bs-toggle="tab"
                                        data-bs-target="#pane-mental"
                                        type="button"
                                        role="tab">
                                    <i class="fas fa-brain"></i> Bowel / Mental
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link skilled"
                                        id="tab-skilled"
                                        data-bs-toggle="tab"
                                        data-bs-target="#pane-skilled"
                                        type="button"
                                        role="tab">
                                    <i class="fas fa-user-nurse"></i> Skilled Needs
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link skin"
                                        id="tab-skin"
                                        data-bs-toggle="tab"
                                        data-bs-target="#pane-skin"
                                        type="button"
                                        role="tab">
                                    <i class="fas fa-clipboard-check"></i> Skin / Nutrition
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link signature"
                                        id="tab-signature"
                                        data-bs-toggle="tab"
                                        data-bs-target="#pane-signature"
                                        type="button"
                                        role="tab">
                                    <i class="fas fa-signature"></i> Signature
                                </button>
                            </li>

                        </ul>
                        <div class="tab-content custom-tab-content mb-4" id="assessmentTabsContent">

                            {{-- TAB 1 --}}
                            <div class="tab-pane fade show active" id="pane-pmh" role="tabpanel">

                                <h5 class="mb-3">Medical History</h5>

                                {{-- Assessment Type --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Assessment Type
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            @php
                                                $assessmentTypes = [
                                                    'admission' => 'Admission',
                                                    'quarterly' => 'Quarterly',
                                                    'annual' => 'Annual',
                                                    'significant_change' => 'Significant Change',
                                                    'readmission' => 'Readmission',
                                                ];
                                            @endphp

                                            @foreach($assessmentTypes as $value => $label)
                                                <div class="col-md-2 mb-2">
                                                    <label>
                                                        <input type="radio"
                                                            name="assessment_type"
                                                            value="{{ $value }}"
                                                            {{ old('assessment_type', $assessment->assessment_type ?? '') == $value ? 'checked' : '' }}>
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Neurological --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Neurological
                                    </div>

                                    <div class="card-body">

                                        <div class="mb-3">
                                            <strong>Source:</strong>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_neuro_participant" value="Y" {{ checkedArr($pmh, 'source_neuro_participant') }}>
                                                Participant
                                            </label>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_neuro_medical_record" value="Y" {{ checkedArr($pmh, 'source_neuro_medical_record') }}>
                                                Medical Record
                                            </label>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_neuro_family" value="Y" {{ checkedArr($pmh, 'source_neuro_family') }}>
                                                Family
                                            </label>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="head_trauma" value="Y" {{ checkedArr($pmh, 'head_trauma') }}>
                                                    Head Trauma
                                                </label>
                                                <input type="text"
                                                    name="head_trauma_specify"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Specify"
                                                    value="{{ oldArr($pmh, 'head_trauma_specify') }}">
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="migraine_headache" value="Y" {{ checkedArr($pmh, 'migraine_headache') }}>
                                                    Migraine Headache
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="impaired_vision_left" value="Y" {{ checkedArr($pmh, 'impaired_vision_left') }}>
                                                    Impaired Vision Left
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="impaired_vision_right" value="Y" {{ checkedArr($pmh, 'impaired_vision_right') }}>
                                                    Impaired Vision Right
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="impaired_hearing_left" value="Y" {{ checkedArr($pmh, 'impaired_hearing_left') }}>
                                                    Impaired Hearing Left
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="impaired_hearing_right" value="Y" {{ checkedArr($pmh, 'impaired_hearing_right') }}>
                                                    Impaired Hearing Right
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <label class="form-label fw-bold">Hemiparesis</label>
                                        <div class="row mb-3">
                                            @foreach([
                                                'hemiparesis_left_arm' => 'Left Arm',
                                                'hemiparesis_left_leg' => 'Left Leg',
                                                'hemiparesis_right_arm' => 'Right Arm',
                                                'hemiparesis_right_leg' => 'Right Leg',
                                            ] as $field => $label)
                                                <div class="col-md-3">
                                                    <label>
                                                        <input type="checkbox" name="{{ $field }}" value="Y" {{ checkedArr($pmh, $field) }}>
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <label class="form-label fw-bold">Paralysis</label>
                                        <div class="row mb-3">
                                            @foreach([
                                                'paralysis_left_arm' => 'Left Arm',
                                                'paralysis_left_leg' => 'Left Leg',
                                                'paralysis_right_arm' => 'Right Arm',
                                                'paralysis_right_leg' => 'Right Leg',
                                            ] as $field => $label)
                                                <div class="col-md-3">
                                                    <label>
                                                        <input type="checkbox" name="{{ $field }}" value="Y" {{ checkedArr($pmh, $field) }}>
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="row mb-3">
                                            @foreach([
                                                'aphasia_motor' => 'Aphasia Motor',
                                                'aphasia_expressive' => 'Aphasia Expressive',
                                                'mental_retardation' => 'Mental Retardation',
                                                'dementia' => 'Dementia',
                                                'alzheimers_disease' => "Alzheimer's Disease",
                                                'cva' => 'CVA',
                                                'parkinsons_disease' => "Parkinson's Disease",
                                                'seizure_disorder' => 'Seizure Disorder',
                                            ] as $field => $label)
                                                <div class="col-md-3 mb-2">
                                                    <label>
                                                        <input type="checkbox" name="{{ $field }}" value="Y" {{ checkedArr($pmh, $field) }}>
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <label class="form-label">Neurological Comment</label>
                                        <textarea name="neuro_comment" class="form-control" rows="2">{{ oldArr($pmh, 'neuro_comment') }}</textarea>

                                    </div>
                                </div>

                                {{-- Cardiac --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Cardiac
                                    </div>

                                    <div class="card-body">

                                        <div class="mb-3">
                                            <strong>Source:</strong>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_cardiac_participant" value="Y" {{ checkedArr($pmh, 'source_cardiac_participant') }}>
                                                Participant
                                            </label>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_cardiac_medical_record" value="Y" {{ checkedArr($pmh, 'source_cardiac_medical_record') }}>
                                                Medical Record
                                            </label>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_cardiac_family" value="Y" {{ checkedArr($pmh, 'source_cardiac_family') }}>
                                                Family
                                            </label>
                                        </div>

                                        <div class="row">
                                            @foreach([
                                                'angina' => 'Angina',
                                                'myocardial_infarction' => 'Myocardial Infarction',
                                                'irregular_heart_rhythm' => 'Irregular Heart Rhythm',
                                                'peripheral_edema' => 'Peripheral Edema',
                                                'valve_disorder' => 'Valve Disorder',
                                                'murmur' => 'Murmur',
                                                'congestive_heart_failure' => 'Congestive Heart Failure',
                                                'htn' => 'HTN',
                                            ] as $field => $label)
                                                <div class="col-md-3 mb-2">
                                                    <label>
                                                        <input type="checkbox" name="{{ $field }}" value="Y" {{ checkedArr($pmh, $field) }}>
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="permanent_pacemaker" value="Y" {{ checkedArr($pmh, 'permanent_pacemaker') }}>
                                                    Permanent Pacemaker
                                                </label>
                                                <input type="text"
                                                    name="permanent_pacemaker_rate"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Rate"
                                                    value="{{ oldArr($pmh, 'permanent_pacemaker_rate') }}">
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label class="form-label">Edema Location</label>
                                                <input type="text"
                                                    name="peripheral_edema_location"
                                                    class="form-control form-control-sm"
                                                    value="{{ oldArr($pmh, 'peripheral_edema_location') }}">
                                            </div>
                                        </div>

                                        <label class="form-label">Cardiac Comment</label>
                                        <textarea name="cardiac_comment" class="form-control" rows="2">{{ oldArr($pmh, 'cardiac_comment') }}</textarea>

                                    </div>
                                </div>

                                {{-- Respiratory --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Respiratory
                                    </div>

                                    <div class="card-body">

                                        <div class="mb-3">
                                            <strong>Source:</strong>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_respiratory_participant" value="Y" {{ checkedArr($pmh, 'source_respiratory_participant') }}>
                                                Participant
                                            </label>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_respiratory_medical_record" value="Y" {{ checkedArr($pmh, 'source_respiratory_medical_record') }}>
                                                Medical Record
                                            </label>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_respiratory_family" value="Y" {{ checkedArr($pmh, 'source_respiratory_family') }}>
                                                Family
                                            </label>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="smoking" value="Y" {{ checkedArr($pmh, 'smoking') }}>
                                                    Smoking
                                                </label>
                                                <input type="text"
                                                    name="smoking_specify"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Specify"
                                                    value="{{ oldArr($pmh, 'smoking_specify') }}">
                                            </div>

                                            @foreach([
                                                'asthma' => 'Asthma',
                                                'bronchitis' => 'Bronchitis',
                                                'pneumonia' => 'Pneumonia',
                                                'copd' => 'COPD',
                                                'cancer' => 'Cancer',
                                                'chronic_cough' => 'Chronic Cough',
                                                'influenza' => 'Influenza',
                                                'tb' => 'TB',
                                            ] as $field => $label)
                                                <div class="col-md-3 mb-2">
                                                    <label>
                                                        <input type="checkbox" name="{{ $field }}" value="Y" {{ checkedArr($pmh, $field) }}>
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <label class="form-label">Respiratory Comment</label>
                                        <textarea name="respiratory_comment" class="form-control" rows="2">{{ oldArr($pmh, 'respiratory_comment') }}</textarea>

                                    </div>
                                </div>

                                {{-- GI / GU --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        GI / GU
                                    </div>

                                    <div class="card-body">

                                        <div class="mb-3">
                                            <strong>Source:</strong>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_gigu_participant" value="Y" {{ checkedArr($pmh, 'source_gigu_participant') }}>
                                                Participant
                                            </label>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_gigu_medical_record" value="Y" {{ checkedArr($pmh, 'source_gigu_medical_record') }}>
                                                Medical Record
                                            </label>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_gigu_family" value="Y" {{ checkedArr($pmh, 'source_gigu_family') }}>
                                                Family
                                            </label>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="ulcer" value="Y" {{ checkedArr($pmh, 'ulcer') }}>
                                                    Ulcer
                                                </label>
                                                <input type="text"
                                                    name="ulcer_specify"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Specify"
                                                    value="{{ oldArr($pmh, 'ulcer_specify') }}">
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="hepatitis" value="Y" {{ checkedArr($pmh, 'hepatitis') }}>
                                                    Hepatitis
                                                </label>
                                                <input type="text"
                                                    name="hepatitis_specify"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Specify"
                                                    value="{{ oldArr($pmh, 'hepatitis_specify') }}">
                                            </div>

                                            @foreach([
                                                'gerd' => 'GERD',
                                                'gi_bleeding' => 'GI Bleeding',
                                                'pancreatitis' => 'Pancreatitis',
                                                'gigu_aphasia' => 'Aphasia',
                                                'cirrhosis' => 'Cirrhosis',
                                                'gallbladder_disease' => 'Gallbladder Disease',
                                                'gallstones' => 'Gallstones',
                                                'kidney_stones' => 'Kidney Stones',
                                                'esrd_dialysis' => 'ESRD / Dialysis',
                                            ] as $field => $label)
                                                <div class="col-md-3 mb-2">
                                                    <label>
                                                        <input type="checkbox" name="{{ $field }}" value="Y" {{ checkedArr($pmh, $field) }}>
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <label class="form-label">GI / GU Comment</label>
                                        <textarea name="gigu_comment" class="form-control" rows="2">{{ oldArr($pmh, 'gigu_comment') }}</textarea>

                                    </div>
                                </div>

                                {{-- Endocrine --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Endocrine / Allergies / Current Complaint
                                    </div>

                                    <div class="card-body">

                                        <div class="mb-3">
                                            <strong>Source:</strong>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_endocrine_participant" value="Y" {{ checkedArr($pmh, 'source_endocrine_participant') }}>
                                                Participant
                                            </label>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_endocrine_medical_record" value="Y" {{ checkedArr($pmh, 'source_endocrine_medical_record') }}>
                                                Medical Record
                                            </label>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_endocrine_family" value="Y" {{ checkedArr($pmh, 'source_endocrine_family') }}>
                                                Family
                                            </label>
                                        </div>

                                        <div class="row mb-3">
                                            @foreach([
                                                'non_insulin_diabetes' => 'Non-Insulin Diabetes',
                                                'insulin_diabetes' => 'Insulin Diabetes',
                                                'dka' => 'DKA',
                                                'hhnk' => 'HHNK',
                                                'hypothyroidism' => 'Hypothyroidism',
                                                'hyperthyroidism' => 'Hyperthyroidism',
                                            ] as $field => $label)
                                                <div class="col-md-3 mb-2">
                                                    <label>
                                                        <input type="checkbox" name="{{ $field }}" value="Y" {{ checkedArr($pmh, $field) }}>
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Allergies</label>
                                            <textarea name="allergies" class="form-control" rows="2">{{ oldArr($pmh, 'allergies') }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Endocrine Comment</label>
                                            <textarea name="endocrine_comment" class="form-control" rows="2">{{ oldArr($pmh, 'endocrine_comment') }}</textarea>
                                        </div>

                                        <hr>

                                        <div class="mb-3">
                                            <strong>Current Complaint Source:</strong>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_current_complaint_participant" value="Y" {{ checkedArr($pmh, 'source_current_complaint_participant') }}>
                                                Participant
                                            </label>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_current_complaint_medical_record" value="Y" {{ checkedArr($pmh, 'source_current_complaint_medical_record') }}>
                                                Medical Record
                                            </label>
                                            <label class="ms-3">
                                                <input type="checkbox" name="source_current_complaint_family" value="Y" {{ checkedArr($pmh, 'source_current_complaint_family') }}>
                                                Family
                                            </label>
                                        </div>

                                        <label class="form-label">Current Complaint</label>
                                        <textarea name="current_complaint" class="form-control" rows="3">{{ oldArr($pmh, 'current_complaint') }}</textarea>

                                    </div>
                                </div>

                            </div>

                            {{-- TAB 2 --}}
                            <div class="tab-pane fade" id="pane-vitals" role="tabpanel">

                                <h5 class="mb-3">Vitals / Pacemaker / Hospitalization</h5>

                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Vitals
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Temp</label>
                                                <input type="text"
                                                    name="temp"
                                                    class="form-control"
                                                    value="{{ oldArr($vh, 'temp') }}">
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">BP</label>
                                                <input type="text"
                                                    name="bp"
                                                    class="form-control"
                                                    value="{{ oldArr($vh, 'bp') }}">
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Respiratory Rate</label>
                                                <input type="text"
                                                    name="respiratory_rate"
                                                    class="form-control"
                                                    value="{{ oldArr($vh, 'respiratory_rate') }}">
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Pulse</label>
                                                <input type="text"
                                                    name="pulse"
                                                    class="form-control"
                                                    value="{{ oldArr($vh, 'pulse') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Pacemaker / Defibrillator
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label>
                                                    <input type="checkbox" name="pacemaker" value="Y" {{ checkedArr($vh, 'pacemaker') }}>
                                                    Pacemaker
                                                </label>

                                                <label class="form-label mt-2">Last Checked Date</label>
                                                <input type="text"
                                                    name="pacemaker_last_checked_date"
                                                    class="form-control"
                                                    placeholder="mm/dd/yyyy"
                                                    maxlength="10"
                                                    value="{{ oldArr($vh, 'pacemaker_last_checked_date') }}">
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label>
                                                    <input type="checkbox" name="implantable_defibrillator" value="Y" {{ checkedArr($vh, 'implantable_defibrillator') }}>
                                                    Implantable Defibrillator
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Hospitalization
                                    </div>

                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label>
                                                <input type="checkbox" name="no_hospitalization" value="Y" {{ checkedArr($vh, 'no_hospitalization') }}>
                                                No Hospitalization
                                            </label>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Dates and Duration</label>
                                            <input type="text"
                                                name="hospitalization_dates"
                                                class="form-control"
                                                value="{{ oldArr($vh, 'hospitalization_dates') }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Primary Diagnosis / Hospitalization Reason</label>
                                            <textarea name="primary_diagnosis_hospitalization"
                                                    class="form-control"
                                                    rows="4">{{ oldArr($vh, 'primary_diagnosis_hospitalization') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- TAB 3 --}}
                            <div class="tab-pane fade" id="pane-functional" role="tabpanel">

                                <h5 class="mb-3">Functional Assessment</h5>

                                <div class="alert alert-info">
                                    Resident Performance and Staff Support:
                                    Independent, Set Up Help, Supervision, Limited Assistance, Extensive Assistance.
                                </div>

                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Activities of Daily Living
                                    </div>

                                    <div class="card-body table-responsive">

                                        @php
                                            $functionalRows = [
                                                'bed_recliner_mobility' => 'Bed/Recliner Mobility - moves to/from lying position/turning side to side',
                                                'transfers'             => 'Transfers - moves to/from bed/recliner, chair, standing position',
                                                'locomotion'            => 'Locomotion - ambulates, wheelchair movement, walker, cane',
                                                'eating'                => 'Eating - eats/drinks by mouth, tube feeding, or TPN',
                                                'dressing'              => 'Dressing - puts on, fastens and takes off clothing',
                                                'toileting'             => 'Toileting - uses toilet transfers on/off toilet, changes pad, cleanses and adjusts clothes',
                                                'hygiene'               => 'Hygiene - combs hair, brushes teeth, shaves, and washes face and hands',
                                                'bathing'               => 'Bathing - bathes, showers, and transfers to tub/shower',
                                            ];

                                            $functionalLevels = [
                                                'independent'          => 'Independent',
                                                'set_up_help'          => 'Set Up Help',
                                                'supervision'          => 'Supervision',
                                                'limited_assistance'   => 'Limited Assist',
                                                'extensive_assistance' => 'Extensive Assist',
                                            ];
                                        @endphp

                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 30%;">Activity</th>
                                                    <th style="width: 28%;">Resident Performance / Staff Support</th>
                                                    <th>Comment</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($functionalRows as $rowKey => $rowLabel)
                                                    @php
                                                        $levelField = $rowKey . '_level';
                                                        $commentField = $rowKey . '_comment';
                                                        $rowData = $fa[$rowKey] ?? [];
                                                    @endphp

                                                    <tr>
                                                        <td>
                                                            <strong>{{ $rowLabel }}</strong>
                                                        </td>

                                                        <td>
                                                            <select name="{{ $levelField }}" class="form-select">
                                                                <option value="">Select</option>

                                                                @foreach($functionalLevels as $levelValue => $levelLabel)
                                                                    <option value="{{ $levelValue }}"
                                                                        {{ old($levelField, $rowData['level'] ?? '') == $levelValue ? 'selected' : '' }}>
                                                                        {{ $levelLabel }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>

                                                        <td>
                                                            <textarea name="{{ $commentField }}"
                                                                    class="form-control"
                                                                    rows="2"
                                                                    placeholder="Comment">{{ old($commentField, $rowData['comment'] ?? '') }}</textarea>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                    </div>
                                </div>

                            </div>

                            {{-- TAB 4 --}}
                            <div class="tab-pane fade" id="pane-mental" role="tabpanel">

                                <h5 class="mb-3">Bowel / Bladder / Mental / Behavior / Sleep</h5>

                                {{-- Bowel and Bladder Function --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Bowel and Bladder Function
                                    </div>

                                    <div class="card-body">

                                        @php
                                            $continenceLevels = [
                                                'continent' => 'Continent',
                                                'usually_continent' => 'Usually Continent',
                                                'occasionally_incontinent' => 'Occasionally Incontinent',
                                                'frequently_incontinent' => 'Frequently Incontinent',
                                                'incontinent' => 'Incontinent',
                                                'external_indwelling_catheter_ostomy' => 'External / Indwelling Catheter / Ostomy',
                                            ];
                                        @endphp

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Bowel</label>
                                                <select name="bowel_level" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach($continenceLevels as $value => $label)
                                                        <option value="{{ $value }}" {{ selectedArr($bb, 'bowel_level', $value) }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Bladder</label>
                                                <select name="bladder_level" class="form-select">
                                                    <option value="">Select</option>
                                                    @foreach($continenceLevels as $value => $label)
                                                        <option value="{{ $value }}" {{ selectedArr($bb, 'bladder_level', $value) }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <hr>

                                        <h6 class="fw-bold">Toileting Program</h6>

                                        <div class="row mb-3">
                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="toileting_assisted_request"
                                                        value="Y"
                                                        {{ checkedArr($bb, 'toileting_assisted_request') }}>
                                                    Assisted with Toileting Upon Request
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="toileting_scheduled_program"
                                                        value="Y"
                                                        {{ checkedArr($bb, 'toileting_scheduled_program') }}>
                                                    Individualized Scheduled Toileting Program
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="toileting_pads_briefs_pullups"
                                                        value="Y"
                                                        {{ checkedArr($bb, 'toileting_pads_briefs_pullups') }}>
                                                    Pads / Briefs / Pull-ups
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="toileting_change_continent_status"
                                                        value="Y"
                                                        {{ checkedArr($bb, 'toileting_change_continent_status') }}>
                                                    Change in Continent Status
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <h6 class="fw-bold">Bowel Pattern</h6>

                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="bowel_regular"
                                                        value="Y"
                                                        {{ checkedArr($bb, 'bowel_regular') }}>
                                                    Regular
                                                </label>
                                                <small class="text-muted d-block">At least once every three days</small>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="bowel_loose_stool"
                                                        value="Y"
                                                        {{ checkedArr($bb, 'bowel_loose_stool') }}>
                                                    Loose Stool
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="bowel_constipation"
                                                        value="Y"
                                                        {{ checkedArr($bb, 'bowel_constipation') }}>
                                                    Constipation
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="bowel_diarrhea"
                                                        value="Y"
                                                        {{ checkedArr($bb, 'bowel_diarrhea') }}>
                                                    Diarrhea
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="bowel_enema"
                                                        value="Y"
                                                        {{ checkedArr($bb, 'bowel_enema') }}>
                                                    Enema
                                                </label>

                                                <input type="text"
                                                    name="bowel_enema_date"
                                                    class="form-control form-control-sm mt-1 datepicker"
                                                    placeholder="Date"
                                                    maxlength="10"
                                                    autocomplete="off"
                                                    value="{{ oldArr($bb, 'bowel_enema_date') }}">
                                            </div>

                                            <div class="col-md-5 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="bowel_laxative"
                                                        value="Y"
                                                        {{ checkedArr($bb, 'bowel_laxative') }}>
                                                    Laxative / Suppositories
                                                </label>

                                                <input type="text"
                                                    name="bowel_laxative_med"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Medication"
                                                    value="{{ oldArr($bb, 'bowel_laxative_med') }}">
                                            </div>
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
                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="alert_oriented"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'alert_oriented') }}>
                                                    Alert / Oriented
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="oriented_person"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'oriented_person') }}>
                                                    Person
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="oriented_place"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'oriented_place') }}>
                                                    Place
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="oriented_time"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'oriented_time') }}>
                                                    Time
                                                </label>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="confused"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'confused') }}>
                                                    Confused
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="confused_sometimes"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'confused_sometimes') }}>
                                                    Sometimes
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="confused_always"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'confused_always') }}>
                                                    Always
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="semi_comatose"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'semi_comatose') }}>
                                                    Semi-Comatose
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="comatose"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'comatose') }}>
                                                    Comatose
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <h6 class="fw-bold">Memory</h6>

                                        <div class="row mb-3">
                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="memory_loss"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'memory_loss') }}>
                                                    Memory Loss
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="memory_short_term"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'memory_short_term') }}>
                                                    Short Term
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="memory_long_term"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'memory_long_term') }}>
                                                    Long Term
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="no_memory_loss"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'no_memory_loss') }}>
                                                    No Memory Loss
                                                </label>
                                            </div>

                                            <div class="col-md-4 mt-2">
                                                <label class="form-label">Mini Mental Status Exam MMSE Score</label>
                                                <input type="text"
                                                    name="mmse_score"
                                                    class="form-control"
                                                    value="{{ oldArr($mbs, 'mmse_score') }}">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                {{-- Behavior Assessment --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Behavior Assessment
                                    </div>

                                    <div class="card-body">

                                        <div class="row mb-3">
                                            @foreach([
                                                'agitation' => 'Agitation',
                                                'physically_aggressive' => 'Physically Aggressive',
                                                'verbally_abusive' => 'Verbally Abusive',
                                                'hoards' => 'Hoards',
                                                'places_self_at_risk' => 'Places Self at Risk for Injury',
                                                'resists_care' => 'Resists Care',
                                                'wandering' => 'Wandering',
                                                'socially_inappropriate_behavior' => 'Socially Inappropriate Behavior',
                                            ] as $field => $label)
                                                <div class="col-md-4 mb-2">
                                                    <label>
                                                        <input type="checkbox"
                                                            name="{{ $field }}"
                                                            value="Y"
                                                            {{ checkedArr($mbs, $field) }}>
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach

                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Other</label>
                                                <input type="text"
                                                    name="behavior_other"
                                                    class="form-control"
                                                    value="{{ oldArr($mbs, 'behavior_other') }}">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Behavior Management Program</label>
                                            <textarea name="behavior_management_program"
                                                    class="form-control"
                                                    rows="3">{{ oldArr($mbs, 'behavior_management_program') }}</textarea>
                                        </div>

                                    </div>
                                </div>

                                {{-- Sleep / Rest Pattern --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Sleep / Rest Pattern
                                    </div>

                                    <div class="card-body">

                                        <div class="row mb-3">
                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="sleeps_through_night"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'sleeps_through_night') }}>
                                                    Sleeps through night
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Poor due to</label>
                                                <input type="text"
                                                    name="poor_due_to"
                                                    class="form-control"
                                                    value="{{ oldArr($mbs, 'poor_due_to') }}">
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="awakes_frequently"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'awakes_frequently') }}>
                                                    Awakes frequently
                                                </label>

                                                <input type="text"
                                                    name="awakes_reason"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Reason"
                                                    value="{{ oldArr($mbs, 'awakes_reason') }}">
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="naps_during_day"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'naps_during_day') }}>
                                                    Naps during the day
                                                </label>

                                                <input type="text"
                                                    name="naps_time"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Time"
                                                    value="{{ oldArr($mbs, 'naps_time') }}">
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="requires_hypnotic"
                                                        value="Y"
                                                        {{ checkedArr($mbs, 'requires_hypnotic') }}>
                                                    Requires Hypnotic
                                                </label>

                                                <input type="text"
                                                    name="hypnotic_indication"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Indication"
                                                    value="{{ oldArr($mbs, 'hypnotic_indication') }}">
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Medication</label>
                                                <input type="text"
                                                    name="sleep_med"
                                                    class="form-control"
                                                    value="{{ oldArr($mbs, 'sleep_med') }}">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>

                            {{-- TAB 5 --}}
                            <div class="tab-pane fade" id="pane-skilled" role="tabpanel">

                                <h5 class="mb-3">Skilled Needs / Hemodialysis</h5>

                                {{-- Rehab --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Rehab
                                    </div>

                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-md-4 mb-3">
                                                <label>
                                                    <input type="checkbox"
                                                        name="rehab_pt"
                                                        value="Y"
                                                        {{ checkedArr($skilled, 'rehab_pt') }}>
                                                    PT
                                                </label>

                                                <input type="text"
                                                    name="rehab_pt_days"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Days per week"
                                                    value="{{ oldArr($skilled, 'rehab_pt_days') }}">
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label>
                                                    <input type="checkbox"
                                                        name="rehab_ot"
                                                        value="Y"
                                                        {{ checkedArr($skilled, 'rehab_ot') }}>
                                                    OT
                                                </label>

                                                <input type="text"
                                                    name="rehab_ot_days"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Days per week"
                                                    value="{{ oldArr($skilled, 'rehab_ot_days') }}">
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label>
                                                    <input type="checkbox"
                                                        name="rehab_speech"
                                                        value="Y"
                                                        {{ checkedArr($skilled, 'rehab_speech') }}>
                                                    Speech
                                                </label>

                                                <input type="text"
                                                    name="rehab_speech_days"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Days per week"
                                                    value="{{ oldArr($skilled, 'rehab_speech_days') }}">
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                {{-- Skilled Nursing Needs --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Skilled Nursing Needs - Current and within last 30 days
                                    </div>

                                    <div class="card-body">

                                        <div class="row">

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="daily_oxygen" value="Y" {{ checkedArr($skilled, 'daily_oxygen') }}>
                                                    Daily Oxygen
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="hemodialysis" value="Y" {{ checkedArr($skilled, 'hemodialysis') }}>
                                                    Hemodialysis
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="suctioning" value="Y" {{ checkedArr($skilled, 'suctioning') }}>
                                                    Suctioning
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="tube_feeding" value="Y" {{ checkedArr($skilled, 'tube_feeding') }}>
                                                    Tube Feeding
                                                </label>

                                                <div class="ms-3 mt-1">
                                                    <label class="me-3">
                                                        <input type="checkbox" name="tube_gtt" value="Y" {{ checkedArr($skilled, 'tube_gtt') }}>
                                                        GTT
                                                    </label>

                                                    <label>
                                                        <input type="checkbox" name="tube_ngt" value="Y" {{ checkedArr($skilled, 'tube_ngt') }}>
                                                        NGT
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="iv_fluids" value="Y" {{ checkedArr($skilled, 'iv_fluids') }}>
                                                    IV Fluids
                                                </label>

                                                <input type="text"
                                                    name="iv_fluids_dates"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Date(s)"
                                                    value="{{ oldArr($skilled, 'iv_fluids_dates') }}">
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="tpn" value="Y" {{ checkedArr($skilled, 'tpn') }}>
                                                    TPN
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="blood_transfusion" value="Y" {{ checkedArr($skilled, 'blood_transfusion') }}>
                                                    Blood Transfusion
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="iv_medication" value="Y" {{ checkedArr($skilled, 'iv_medication') }}>
                                                    IV Medication
                                                </label>

                                                <input type="text"
                                                    name="iv_medication_dates"
                                                    class="form-control form-control-sm mt-1"
                                                    placeholder="Date(s)"
                                                    value="{{ oldArr($skilled, 'iv_medication_dates') }}">
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="wound_care" value="Y" {{ checkedArr($skilled, 'wound_care') }}>
                                                    Wound Care
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="tracheostomy_care" value="Y" {{ checkedArr($skilled, 'tracheostomy_care') }}>
                                                    Tracheostomy Care
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="respiratory_therapy" value="Y" {{ checkedArr($skilled, 'respiratory_therapy') }}>
                                                    Respiratory Therapy
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="uti" value="Y" {{ checkedArr($skilled, 'uti') }}>
                                                    UTI
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="ostomy_care" value="Y" {{ checkedArr($skilled, 'ostomy_care') }}>
                                                    Ostomy Care
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="internal_bleeding" value="Y" {{ checkedArr($skilled, 'internal_bleeding') }}>
                                                    Internal Bleeding
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="dehydration" value="Y" {{ checkedArr($skilled, 'dehydration') }}>
                                                    Dehydration
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="abt_therapy" value="Y" {{ checkedArr($skilled, 'abt_therapy') }}>
                                                    ABT Therapy
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="chemotherapy_radiation" value="Y" {{ checkedArr($skilled, 'chemotherapy_radiation') }}>
                                                    Chemotherapy / Radiation
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="hip_fracture" value="Y" {{ checkedArr($skilled, 'hip_fracture') }}>
                                                    Hip Fracture
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Other Fracture</label>
                                                <input type="text"
                                                    name="other_fracture"
                                                    class="form-control form-control-sm"
                                                    value="{{ oldArr($skilled, 'other_fracture') }}">
                                            </div>

                                            <div class="col-md-8 mb-2">
                                                <label class="form-label">Other Skilled Need</label>
                                                <input type="text"
                                                    name="skilled_other"
                                                    class="form-control form-control-sm"
                                                    value="{{ oldArr($skilled, 'skilled_other') }}">
                                            </div>

                                        </div>

                                    </div>
                                </div>

                                {{-- Hemodialysis --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Hemodialysis
                                    </div>

                                    <div class="card-body">

                                        <div class="row">

                                            <div class="col-md-3 mb-3">
                                                <label>
                                                    <input type="checkbox"
                                                        name="hemo_not_applicable"
                                                        value="Y"
                                                        {{ checkedArr($skilled, 'hemo_not_applicable') }}>
                                                    Not Applicable
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Treatment Orders</label>
                                                <input type="text"
                                                    name="treatment_orders"
                                                    class="form-control"
                                                    value="{{ oldArr($skilled, 'treatment_orders') }}">
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Times per week</label>
                                                <input type="text"
                                                    name="times_per_week"
                                                    class="form-control"
                                                    value="{{ oldArr($skilled, 'times_per_week') }}">
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Active Shunt Location</label>
                                                <input type="text"
                                                    name="active_shunt_location"
                                                    class="form-control"
                                                    value="{{ oldArr($skilled, 'active_shunt_location') }}">
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label>Bruit Present</label><br>

                                                <label class="me-3">
                                                    <input type="checkbox"
                                                        name="bruit_yes"
                                                        value="Y"
                                                        {{ checkedArr($skilled, 'bruit_yes') }}>
                                                    Yes
                                                </label>

                                                <label>
                                                    <input type="checkbox"
                                                        name="bruit_no"
                                                        value="Y"
                                                        {{ checkedArr($skilled, 'bruit_no') }}>
                                                    No
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label>Thrill Present</label><br>

                                                <label class="me-3">
                                                    <input type="checkbox"
                                                        name="thrill_yes"
                                                        value="Y"
                                                        {{ checkedArr($skilled, 'thrill_yes') }}>
                                                    Yes
                                                </label>

                                                <label>
                                                    <input type="checkbox"
                                                        name="thrill_no"
                                                        value="Y"
                                                        {{ checkedArr($skilled, 'thrill_no') }}>
                                                    No
                                                </label>
                                            </div>

                                        </div>

                                    </div>
                                </div>

                            </div>

                            {{-- TAB 6 --}}
                            <div class="tab-pane fade" id="pane-skin" role="tabpanel">

                                <h5 class="mb-3">Skin / Devices / Nutrition / Pain</h5>

                                {{-- Skin Assessment --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Skin Assessment
                                    </div>

                                    <div class="card-body">

                                        <div class="alert alert-info">
                                            Skin Problems / Lesions: describe appearance and location using numbered assessment.
                                        </div>
                                        <div class="text-center mb-3">
                                            <img src="{{ asset('backend/assets/images/skin_body_diagram.png') }}"
                                                alt="Skin Body Diagram"
                                                style="max-width: 100%; max-height: 220px; border: 1px solid #ddd; padding: 5px;">
                                        </div>

                                        <div class="row">

                                            @foreach([
                                                'skin_pressure_ulcer' => '1. Pressure Ulcer',
                                                'skin_stasis_ulcer' => '2. Stasis Ulcer',
                                                'skin_rash' => '3. Rash',
                                                'skin_ecchymosis' => '4. Ecchymosis',
                                                'skin_burn' => '5. Burn',
                                                'skin_abrasion' => '6. Abrasion',
                                                'skin_tear' => '7. Skin Tear',
                                                'skin_cellulitis' => '8. Cellulitis',
                                                'skin_blister' => '9. Blister',
                                                'skin_hematoma' => '10. Hematoma',
                                                'skin_surgical_wound' => '11. Surgical Wound',
                                                'skin_scar' => '12. Scar',
                                                'skin_edema' => '13. Edema',
                                            ] as $field => $label)
                                                <div class="col-md-3 mb-2">
                                                    <label>
                                                        <input type="checkbox"
                                                            name="{{ $field }}"
                                                            value="Y"
                                                            {{ checkedArr($skin, $field) }}>
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach

                                        </div>

                                        <div class="mb-3 mt-3">
                                            <label class="form-label">Body / Location Notes</label>
                                            <textarea name="skin_body_notes"
                                                    class="form-control"
                                                    rows="3"
                                                    placeholder="Example: 1 - left heel, 3 - right arm, etc.">{{ oldArr($skin, 'skin_body_notes') }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Note Significant Findings</label>
                                            <textarea name="skin_significant_findings"
                                                    class="form-control"
                                                    rows="3">{{ oldArr($skin, 'skin_significant_findings') }}</textarea>
                                        </div>

                                    </div>
                                </div>

                                {{-- Assistive / Adaptive Devices --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Assistive / Adaptive Devices
                                    </div>

                                    <div class="card-body">

                                        <div class="row">

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="device_none" value="Y" {{ checkedArr($devices, 'device_none') }}>
                                                    None
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="wc_cushion" value="Y" {{ checkedArr($devices, 'wc_cushion') }}>
                                                    W/C Cushion
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="specialty_wc" value="Y" {{ checkedArr($devices, 'specialty_wc') }}>
                                                    Specialty W/C
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="specialty_wc_accessories" value="Y" {{ checkedArr($devices, 'specialty_wc_accessories') }}>
                                                    Specialty W/C Accessories
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="knee_separator" value="Y" {{ checkedArr($devices, 'knee_separator') }}>
                                                    Knee Separator
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="handroll_left" value="Y" {{ checkedArr($devices, 'handroll_left') }}>
                                                    Handroll Left
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="handroll_right" value="Y" {{ checkedArr($devices, 'handroll_right') }}>
                                                    Handroll Right
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="hand_splint_left" value="Y" {{ checkedArr($devices, 'hand_splint_left') }}>
                                                    Hand Splint Left
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="hand_splint_right" value="Y" {{ checkedArr($devices, 'hand_splint_right') }}>
                                                    Hand Splint Right
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="prosthesis_left" value="Y" {{ checkedArr($devices, 'prosthesis_left') }}>
                                                    Prosthesis Left
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="prosthesis_right" value="Y" {{ checkedArr($devices, 'prosthesis_right') }}>
                                                    Prosthesis Right
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="brace_splint_left" value="Y" {{ checkedArr($devices, 'brace_splint_left') }}>
                                                    Brace / Splint Left
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="brace_splint_right" value="Y" {{ checkedArr($devices, 'brace_splint_right') }}>
                                                    Brace / Splint Right
                                                </label>
                                            </div>

                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Other</label>
                                                <input type="text"
                                                    name="device_other"
                                                    class="form-control"
                                                    value="{{ oldArr($devices, 'device_other') }}">
                                            </div>

                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Instruction / Wear Schedule</label>
                                                <input type="text"
                                                    name="instruction_wear_schedule"
                                                    class="form-control"
                                                    value="{{ oldArr($devices, 'instruction_wear_schedule') }}">
                                            </div>

                                        </div>

                                        <hr>

                                        <h6 class="fw-bold">Dentures</h6>

                                        <div class="row mb-3">
                                            <div class="col-md-2 mb-2">
                                                <label>
                                                    <input type="checkbox" name="dentures_none" value="Y" {{ checkedArr($devices, 'dentures_none') }}>
                                                    None
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="dentures_full_upper" value="Y" {{ checkedArr($devices, 'dentures_full_upper') }}>
                                                    Full Upper
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="dentures_full_lower" value="Y" {{ checkedArr($devices, 'dentures_full_lower') }}>
                                                    Full Lower
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="dentures_partial_upper" value="Y" {{ checkedArr($devices, 'dentures_partial_upper') }}>
                                                    Partial Upper
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="dentures_partial_lower" value="Y" {{ checkedArr($devices, 'dentures_partial_lower') }}>
                                                    Partial Lower
                                                </label>
                                            </div>
                                        </div>

                                        <h6 class="fw-bold">Hearing Aid / Glasses</h6>

                                        <div class="row">
                                            <div class="col-md-2 mb-2">
                                                <label>
                                                    <input type="checkbox" name="hearing_aid_none" value="Y" {{ checkedArr($devices, 'hearing_aid_none') }}>
                                                    Hearing Aid None
                                                </label>
                                            </div>

                                            <div class="col-md-2 mb-2">
                                                <label>
                                                    <input type="checkbox" name="hearing_aid_left" value="Y" {{ checkedArr($devices, 'hearing_aid_left') }}>
                                                    Left
                                                </label>
                                            </div>

                                            <div class="col-md-2 mb-2">
                                                <label>
                                                    <input type="checkbox" name="hearing_aid_right" value="Y" {{ checkedArr($devices, 'hearing_aid_right') }}>
                                                    Right
                                                </label>
                                            </div>

                                            <div class="col-md-2 mb-2">
                                                <label>
                                                    <input type="checkbox" name="glasses" value="Y" {{ checkedArr($devices, 'glasses') }}>
                                                    Glasses
                                                </label>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                {{-- Nutritional Status --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Nutritional Status
                                    </div>

                                    <div class="card-body">

                                        <div class="row">

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="regular_diet" value="Y" {{ checkedArr($nutrition, 'regular_diet') }}>
                                                    Regular Diet
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="ncs" value="Y" {{ checkedArr($nutrition, 'ncs') }}>
                                                    No Concentrated Sweets - NCS
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox" name="nas" value="Y" {{ checkedArr($nutrition, 'nas') }}>
                                                    No Added Salt - NAS
                                                </label>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Supplement / Type</label>
                                                <input type="text"
                                                    name="supplement_type"
                                                    class="form-control"
                                                    value="{{ oldArr($nutrition, 'supplement_type') }}">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Therapeutic Diet</label>
                                                <input type="text"
                                                    name="therapeutic_diet"
                                                    class="form-control"
                                                    value="{{ oldArr($nutrition, 'therapeutic_diet') }}">
                                            </div>

                                            <div class="col-md-12">
                                                <h6 class="fw-bold">Appetite</h6>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="appetite_good" value="Y" {{ checkedArr($nutrition, 'appetite_good') }}>
                                                    Good - 100% to 75%
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="appetite_fair" value="Y" {{ checkedArr($nutrition, 'appetite_fair') }}>
                                                    Fair - 75% to 50%
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="appetite_poor" value="Y" {{ checkedArr($nutrition, 'appetite_poor') }}>
                                                    Poor - 50% or less
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="chewing_problem" value="Y" {{ checkedArr($nutrition, 'chewing_problem') }}>
                                                    Chewing Problem
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="swallowing_problem" value="Y" {{ checkedArr($nutrition, 'swallowing_problem') }}>
                                                    Swallowing Problem
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="mechanical_soft" value="Y" {{ checkedArr($nutrition, 'mechanical_soft') }}>
                                                    Mechanical Soft
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="puree" value="Y" {{ checkedArr($nutrition, 'puree') }}>
                                                    Puree
                                                </label>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Fluid Restriction</label>
                                                <input type="text"
                                                    name="fluid_restriction"
                                                    class="form-control"
                                                    value="{{ oldArr($nutrition, 'fluid_restriction') }}">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Weight lbs.</label>
                                                <input type="text"
                                                    name="weight_lbs"
                                                    class="form-control"
                                                    value="{{ oldArr($nutrition, 'weight_lbs') }}">
                                            </div>

                                            <div class="col-md-12">
                                                <h6 class="fw-bold">Weight</h6>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="weight_stable" value="Y" {{ checkedArr($nutrition, 'weight_stable') }}>
                                                    Stable
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="weight_change" value="Y" {{ checkedArr($nutrition, 'weight_change') }}>
                                                    Weight Change
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="weight_gain" value="Y" {{ checkedArr($nutrition, 'weight_gain') }}>
                                                    Gain
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="weight_loss" value="Y" {{ checkedArr($nutrition, 'weight_loss') }}>
                                                    Loss
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="planned" value="Y" {{ checkedArr($nutrition, 'planned') }}>
                                                    Planned
                                                </label>
                                            </div>

                                            <div class="col-md-3 mb-2">
                                                <label>
                                                    <input type="checkbox" name="unplanned" value="Y" {{ checkedArr($nutrition, 'unplanned') }}>
                                                    Unplanned
                                                </label>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label>
                                                    <input type="checkbox"
                                                        name="md_nutritionist_informed"
                                                        value="Y"
                                                        {{ checkedArr($nutrition, 'md_nutritionist_informed') }}>
                                                    MD & Nutritionist Informed
                                                </label>
                                            </div>

                                        </div>

                                    </div>
                                </div>

                                {{-- Pain --}}
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        Pain
                                    </div>

                                    <div class="card-body">

                                        <div class="row">

                                            <div class="col-md-6 mb-3">
                                                <label>
                                                    Resident expresses pain/discomfort part or all of the time
                                                </label>
                                                <br>

                                                <label class="me-3">
                                                    <input type="checkbox"
                                                        name="pain_no"
                                                        value="Y"
                                                        {{ checkedArr($pain, 'pain_no') }}>
                                                    No
                                                </label>

                                                <label>
                                                    <input type="checkbox"
                                                        name="pain_yes"
                                                        value="Y"
                                                        {{ checkedArr($pain, 'pain_yes') }}>
                                                    Yes
                                                </label>
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Pain Notes</label>
                                                <textarea name="pain_notes"
                                                        class="form-control"
                                                        rows="3"
                                                        placeholder="If yes, complete one pain assessment tool for each location of pain.">{{ oldArr($pain, 'pain_notes') }}</textarea>
                                            </div>

                                        </div>

                                    </div>
                                </div>

                            </div>

                            {{-- TAB 7 --}}
                            <div class="tab-pane fade" id="pane-signature" role="tabpanel">
                                <h5>Signature</h5>

                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Signature</label>
                                        <input type="text"
                                               name="signature"
                                               class="form-control"
                                               value="{{ old('signature', $assessment->signature ?? '') }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Signature Date</label>
                                        <input type="text"
                                            name="signature_date"
                                            id="signature_date"
                                            class="form-control datepicker"
                                            placeholder="mm/dd/yyyy"
                                            maxlength="10"
                                            autocomplete="off"
                                            value="{{ old('signature_date', !empty($assessment->signature_date) ? \Carbon\Carbon::parse($assessment->signature_date)->format('m/d/Y') : '') }}">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-between mb-5">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" id="prevTabBtn">
                                    Previous
                                </button>

                                <button type="button" class="btn btn-outline-primary" id="nextTabBtn">
                                    Next
                                </button>
                            </div>

                            <div>
                                <a href="{{ route('nursing.assessment.summary', $type) }}" class="btn btn-secondary">
                                    Cancel
                                </a>

                                <button type="submit" class="btn btn-success">
                                    {{ $assessmentId ? 'Update Assessment' : 'Save Assessment' }}
                                </button>
                            </div>
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    $(document).ready(function () {

        if (typeof flatpickr !== 'undefined') {
            flatpickr('.datepicker', {
                dateFormat: 'm/d/Y',
                allowInput: true
            });
        }

        if ($.fn.select2) {
            $('#participant_id').select2({
                placeholder: 'Select Member',
                allowClear: true,
                width: '100%'
            });
        }

        $('#shiftSelect').on('change', function () {
            let selectedShift = $(this).val();
            let url = "{{ route('nursing.assessment.summary', ':type') }}";
            url = url.replace(':type', selectedShift);
            window.location.href = url;
        });

        function isValidUSDate(dateText, allowBlank = false) {
            if (allowBlank && (!dateText || dateText.trim() === '')) {
                return true;
            }

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
            let assessmentDate = $('#assessment_date').val();

            if ($('#assessment_date').length && !isValidUSDate(assessmentDate, false)) {
                e.preventDefault();
                alert('Please enter a valid Assessment Date in mm/dd/yyyy format.');
                $('#assessment_date').focus();
                return false;
            }

            let signatureDate = $('#signature_date').val();

            if ($('#signature_date').length && !isValidUSDate(signatureDate, true)) {
                e.preventDefault();
                alert('Please enter a valid Signature Date in mm/dd/yyyy format.');
                $('#signature_date').focus();
                return false;
            }
        });

        function getActiveTabIndex() {
            return $('#assessmentTabs button.nav-link').index($('#assessmentTabs button.nav-link.active'));
        }

        function showTabByIndex(index) {
            let tabs = $('#assessmentTabs button.nav-link');

            if (index < 0 || index >= tabs.length) {
                return;
            }

            let tab = new bootstrap.Tab(tabs[index]);
            tab.show();
        }

        $('#prevTabBtn').on('click', function () {
            showTabByIndex(getActiveTabIndex() - 1);
        });

        $('#nextTabBtn').on('click', function () {
            showTabByIndex(getActiveTabIndex() + 1);
        });

    });
</script>
@endpush
