@extends('admin.admin_dashboard')
@section('title', 'Nursing')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        @php
            $isEdit = ($mode ?? '') === 'edit' && isset($assessment);
            $action = $isEdit
                ? route('ss-quarterly.update', $assessment->id)
                : route('ss-quarterly.store');
        @endphp

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">
                    Social Service Quarterly Assessment
                    <small class="text-muted">
                        – {{ $member->LastName ?? '' }}, {{ $member->FirstName ?? '' }}
                        (ID: {{ $member->ParticipantId ?? '' }})
                    </small>
                </h4>
            </div>
            <div>
                <a href="{{ route('ss-quarterly.index', [
                        'type'          => $type ?? 'A',
                        'participant_id'=> $member->ParticipantId,
                    ]) }}"
                   class="btn btn-secondary">
                    &laquo; Back
                </a>
            </div>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-sm">
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $action }}">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <input type="hidden" name="participant_id" value="{{ $member->ParticipantId }}">
            <input type="hidden" name="type" value="{{ $type ?? 'A' }}">

            {{-- TOP INFO --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Assessment Date</label>
                            <input type="date"
                                   name="assessment_date"
                                   class="form-control form-control-sm"
                                   value="{{ old('assessment_date', optional($assessment)->assessment_date ?? now()->toDateString()) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Medical Record #</label>
                            <input type="text"
                                   name="medical_record_no"
                                   class="form-control form-control-sm"
                                   value="{{ old('medical_record_no', optional($assessment)->medical_record_no) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Social Worker</label>
                            <input type="text"
                                   name="social_worker_name"
                                   class="form-control form-control-sm"
                                   value="{{ old('social_worker_name', optional($assessment)->social_worker_name) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label d-block">Plan Review</label>
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="care_plan_reviewed"
                                       value="1"
                                       {{ old('care_plan_reviewed', optional($assessment)->care_plan_reviewed) ? 'checked' : '' }}>
                                <label class="form-check-label">Care Plan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="discharge_plan_reviewed"
                                       value="1"
                                       {{ old('discharge_plan_reviewed', optional($assessment)->discharge_plan_reviewed) ? 'checked' : '' }}>
                                <label class="form-check-label">Discharge Plan</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- COGNITIVE FUNCTIONING --}}
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Cognitive Functioning</strong>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="cognitive_change"
                                   value="1"
                                   {{ old('cognitive_change', optional($assessment)->cognitive_change) ? 'checked' : '' }}>
                            <label class="form-check-label">Change Since Last Review</label>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6">
                            @php
                                $cognitiveLeft = [
                                    'cognitive_alert'                => 'Alert',
                                    'cognitive_confused'             => 'Confused',
                                    'cognitive_forgetful'            => 'Forgetful',
                                    'cognitive_wanders'              => 'Wanders',
                                    'cognitive_unaware_surroundings' => 'Unaware of Surroundings',
                                ];
                            @endphp
                            @foreach($cognitiveLeft as $field => $label)
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="{{ $field }}" value="1"
                                           {{ old($field, optional($assessment)->$field) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Orientation</label>
                            @php
                                $orientationFields = [
                                    'cognitive_oriented_person'       => 'Oriented to Person',
                                    'cognitive_oriented_place'        => 'Oriented to Place',
                                    'cognitive_oriented_time'         => 'Oriented to Time',
                                    'cognitive_orientation_fluctuates'=> 'Orientation Fluctuates',
                                ];
                            @endphp
                            @foreach($orientationFields as $field => $label)
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="{{ $field }}" value="1"
                                           {{ old($field, optional($assessment)->$field) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label d-block">Short Term Memory</label>
                            @php
                                $stm = old('cognitive_short_term_memory', optional($assessment)->cognitive_short_term_memory);
                            @endphp
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input"
                                       name="cognitive_short_term_memory" value="wnl"
                                       {{ $stm === 'wnl' ? 'checked' : '' }}>
                                <label class="form-check-label">WNL</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input"
                                       name="cognitive_short_term_memory" value="impaired"
                                       {{ $stm === 'impaired' ? 'checked' : '' }}>
                                <label class="form-check-label">Impaired</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-block">Long Term Memory</label>
                            @php
                                $ltm = old('cognitive_long_term_memory', optional($assessment)->cognitive_long_term_memory);
                            @endphp
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input"
                                       name="cognitive_long_term_memory" value="wnl"
                                       {{ $ltm === 'wnl' ? 'checked' : '' }}>
                                <label class="form-check-label">WNL</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input"
                                       name="cognitive_long_term_memory" value="impaired"
                                       {{ $ltm === 'impaired' ? 'checked' : '' }}>
                                <label class="form-check-label">Impaired</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SOCIAL FUNCTIONING --}}
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Social Functioning</strong>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox"
                                   name="social_change" value="1"
                                   {{ old('social_change', optional($assessment)->social_change) ? 'checked' : '' }}>
                            <label class="form-check-label">Change Since Last Review</label>
                        </div>
                    </div>

                    @php
                        $socialFields = [
                            'social_socializes_appropriately' => 'Socializes Appropriately',
                            'social_attends_group_activities' => 'Attends Group Activities',
                            'social_isolates_self'            => 'Isolates Self',
                            'social_cooperative'              => 'Cooperative',
                            'social_friendly'                 => 'Friendly',
                            'social_quiet'                    => 'Quiet',
                        ];
                    @endphp
                    <div class="row">
                        <div class="col-md-6">
                            @foreach($socialFields as $field => $label)
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="{{ $field }}" value="1"
                                           {{ old($field, optional($assessment)->$field) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- COMMUNICATION --}}
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Communication</strong>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox"
                                   name="communication_change" value="1"
                                   {{ old('communication_change', optional($assessment)->communication_change) ? 'checked' : '' }}>
                            <label class="form-check-label">Change Since Last Review</label>
                        </div>
                    </div>

                    @php
                        $communicationFields = [
                            'communication_expresses_needs'        => 'Expresses Needs',
                            'communication_does_not_express'       => 'Does Not Express Needs',
                            'communication_requires_encouragement' => 'Requires Encouragement',
                        ];
                    @endphp
                    <div class="row">
                        <div class="col-md-6">
                            @foreach($communicationFields as $field => $label)
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="{{ $field }}" value="1"
                                           {{ old($field, optional($assessment)->$field) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- DECISION MAKING --}}
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Decision Making Ability</strong>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox"
                                   name="decision_change" value="1"
                                   {{ old('decision_change', optional($assessment)->decision_change) ? 'checked' : '' }}>
                            <label class="form-check-label">Change Since Last Review</label>
                        </div>
                    </div>

                    @php
                        $decisionFields = [
                            'decision_independent'         => 'Independent',
                            'decision_minimal_impairment'  => 'Minimally Impaired',
                            'decision_moderate_impairment' => 'Moderately Impaired',
                            'decision_severe_impairment'   => 'Severely Impaired',
                        ];
                    @endphp
                    <div class="row">
                        <div class="col-md-6">
                            @foreach($decisionFields as $field => $label)
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="{{ $field }}" value="1"
                                           {{ old($field, optional($assessment)->$field) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- MOOD / MENTAL HEALTH --}}
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Mood / Mental Health</strong>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox"
                                   name="mood_change" value="1"
                                   {{ old('mood_change', optional($assessment)->mood_change) ? 'checked' : '' }}>
                            <label class="form-check-label">Change Since Last Review</label>
                        </div>
                    </div>

                    @php
                        $moodFields = [
                            'mood_normal'      => 'Normal',
                            'mood_stable'      => 'Stable',
                            'mood_anxious'     => 'Anxious',
                            'mood_lethargic'   => 'Lethargic',
                            'mood_depressed'   => 'Depressed',
                            'mood_manic'       => 'Manic',
                            'mood_agitated'    => 'Agitated',
                            'mood_combative'   => 'Combative',
                            'mood_pessimistic' => 'Pessimistic',
                            'mood_optimistic'  => 'Optimistic',
                            'mood_confused'    => 'Confused',
                        ];
                    @endphp

                    <div class="row mb-2">
                        <div class="col-md-6">
                            @foreach($moodFields as $field => $label)
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="{{ $field }}" value="1"
                                           {{ old($field, optional($assessment)->$field) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label class="form-label d-block">Mental Health Diagnosis</label>
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input"
                                       name="mental_health_diagnosis" value="1"
                                       {{ old('mental_health_diagnosis', optional($assessment)->mental_health_diagnosis) ? 'checked' : '' }}>
                                <label class="form-check-label">Yes</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Diagnosis / Comments</label>
                            <input type="text"
                                   name="mental_health_diagnosis_type"
                                   class="form-control form-control-sm"
                                   value="{{ old('mental_health_diagnosis_type', optional($assessment)->mental_health_diagnosis_type) }}">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="form-label d-block">Treatment</label>
                            @php
                                $treatmentFields = [
                                    'treatment_psychiatrist'       => 'Psychiatrist',
                                    'treatment_medication_therapy' => 'Medication Therapy',
                                    'treatment_psychotherapy'      => 'Psychotherapy',
                                    'treatment_none'               => 'None',
                                ];
                            @endphp
                            @foreach($treatmentFields as $field => $label)
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="{{ $field }}" value="1"
                                           {{ old($field, optional($assessment)->$field) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Comments</label>
                        <textarea name="mood_comment"
                                  class="form-control form-control-sm"
                                  rows="2">{{ old('mood_comment', optional($assessment)->mood_comment) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- APPEARANCE / HYGIENE --}}
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Appearance / Hygiene</strong>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox"
                                   name="appearance_change" value="1"
                                   {{ old('appearance_change', optional($assessment)->appearance_change) ? 'checked' : '' }}>
                            <label class="form-check-label">Change Since Last Review</label>
                        </div>
                    </div>

                    @php
                        $appearanceFields = [
                            'appearance_neatly_groomed'      => 'Neatly Groomed',
                            'appearance_presentable'         => 'Presentable',
                            'appearance_disheveled'          => 'Disheveled',
                            'appearance_appropriate_hygiene' => 'Appropriate Hygiene',
                            'appearance_poor_hygiene'        => 'Poor Hygiene',
                        ];
                    @endphp

                    <div class="row mb-2">
                        <div class="col-md-6">
                            @foreach($appearanceFields as $field => $label)
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="{{ $field }}" value="1"
                                           {{ old($field, optional($assessment)->$field) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Comments</label>
                        <textarea name="appearance_comment"
                                  class="form-control form-control-sm"
                                  rows="2">{{ old('appearance_comment', optional($assessment)->appearance_comment) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- SERVICES / REFERRALS --}}
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Services / Referrals</strong>
                </div>
                <div class="card-body">
                    @php
                        $serviceFields = [
                            'services_home_health'         => 'Home Health',
                            'services_meals_on_wheels'     => 'Meals on Wheels',
                            'services_food_stamp_recert'   => 'Food Stamp Recertification',
                            'services_medicaid_recert'     => 'Medicaid Recertification',
                            'services_geriatric_case_mgmt' => 'Geriatric Case Management',
                            'services_ssi_advocacy'        => 'SSI Advocacy',
                            'services_no_needs_assessed'   => 'No Needs Assessed',
                        ];
                    @endphp

                    <div class="row mb-2">
                        <div class="col-md-6">
                            @foreach($serviceFields as $field => $label)
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="{{ $field }}" value="1"
                                           {{ old($field, optional($assessment)->$field) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Comments</label>
                        <textarea name="services_comment"
                                  class="form-control form-control-sm"
                                  rows="2">{{ old('services_comment', optional($assessment)->services_comment) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- SAVE / CANCEL --}}
            <div class="mb-4">
                <button type="submit" class="btn btn-success">
                    {{ $isEdit ? 'Update Assessment' : 'Save Assessment' }}
                </button>
                <a href="{{ route('ss-quarterly.index', [
                        'type'          => $type ?? 'A',
                        'participant_id'=> $member->ParticipantId,
                    ]) }}"
                   class="btn btn-secondary">
                    Cancel
                </a>
            </div>

        </form>

    </div>
</div>

@endsection
