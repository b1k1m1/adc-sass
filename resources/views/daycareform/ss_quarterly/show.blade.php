@extends('admin.admin_dashboard')
@section('title', 'Nursing')
@section('admin')

@if(request()->boolean('auto_print'))
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
@endif

<div class="page-content">
    <div class="container-fluid">

        {{-- Visible on screen only (buttons) --}}
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">

            <div>
                <h4 class="mb-0">

                </h4>
                {{-- Name will be shown separately for print --}}
            </div>

            <div class="btn-group">
                <a href="{{ route('ss-quarterly.index', [
                        'type'          => $type,
                        'participant_id'=> $assessment->participant_id,
                    ]) }}"
                class="btn btn-secondary">
                    &laquo; Back
                </a>

                <a href="{{ route('ss-quarterly.edit', [
                        'id'   => $assessment->id,
                        'type' => $type,
                    ]) }}"
                class="btn btn-primary">
                    Edit
                </a>

                <button type="button" class="btn btn-outline-dark" onclick="window.print()">
                    Print
                </button>

                <a href="{{ route('ss-quarterly.pdf', $assessment->id) }}"
                    class="btn btn-outline-info"
                    target="_blank">
                      Export to PDF
                </a>
            </div>
        </div>

        {{-- Header for print + screen --}}
        @php
            $lastName  = $member->LastName  ?? $member->lastname  ?? '';
            $firstName = $member->FirstName ?? $member->firstname ?? '';
            $pid       = $member->ParticipantId ?? $member->participant_id ?? '';
        @endphp

        <div class="mb-3">
            <h4 class="mb-1">Social Service Quarterly Assessment</h4>
            <div><strong>Member:</strong> {{ $lastName }}, {{ $firstName }} (ID: {{ $pid }})</div>
        </div>
        {{-- Print styles --}}
        <style>
            @media print {
                .no-print {
                    display: none !important;
                }
                .card {
                    border: none;
                    box-shadow: none;
                }
            }
            .section-title {
                font-weight: 600;
                text-decoration: underline;
                margin-bottom: 4px;
            }
            .field-label {
                font-weight: 600;
            }
        </style>

        {{-- TOP INFO --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-3">
                        <span class="field-label">Assessment Date:</span><br>
                        {{ \Carbon\Carbon::parse($assessment->assessment_date)->format('m/d/Y') }}
                    </div>
                    <div class="col-md-3">
                        <span class="field-label">Medical Record #:</span><br>
                        {{ $assessment->medical_record_no }}
                    </div>
                    <div class="col-md-4">
                        <span class="field-label">Social Worker:</span><br>
                        {{ $assessment->social_worker_name }}
                    </div>
                    <div class="col-md-2">
                        <span class="field-label">Plan Review:</span><br>
                        Care: {{ $assessment->care_plan_reviewed ? 'Yes' : 'No' }}<br>
                        Discharge: {{ $assessment->discharge_plan_reviewed ? 'Yes' : 'No' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- COGNITIVE FUNCTIONING --}}
        <div class="card mb-3">
            <div class="card-header">
                <span class="section-title">Cognitive Functioning</span>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Change Since Last Review:</strong>
                    {{ $assessment->cognitive_change ? 'Yes' : 'No' }}
                </div>

                <div class="row mb-2">
                    <div class="col-md-6">
                        <strong>General:</strong>
                        <ul class="mb-0">
                            @if($assessment->cognitive_alert) <li>Alert</li> @endif
                            @if($assessment->cognitive_confused) <li>Confused</li> @endif
                            @if($assessment->cognitive_forgetful) <li>Forgetful</li> @endif
                            @if($assessment->cognitive_wanders) <li>Wanders</li> @endif
                            @if($assessment->cognitive_unaware_surroundings) <li>Unaware of Surroundings</li> @endif
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <strong>Orientation:</strong>
                        <ul class="mb-0">
                            @if($assessment->cognitive_oriented_person) <li>Oriented to Person</li> @endif
                            @if($assessment->cognitive_oriented_place) <li>Oriented to Place</li> @endif
                            @if($assessment->cognitive_oriented_time) <li>Oriented to Time</li> @endif
                            @if($assessment->cognitive_orientation_fluctuates) <li>Orientation Fluctuates</li> @endif
                        </ul>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <strong>Short Term Memory:</strong><br>
                        @if($assessment->cognitive_short_term_memory === 'wnl') WNL @endif
                        @if($assessment->cognitive_short_term_memory === 'impaired') Impaired @endif
                    </div>
                    <div class="col-md-3">
                        <strong>Long Term Memory:</strong><br>
                        @if($assessment->cognitive_long_term_memory === 'wnl') WNL @endif
                        @if($assessment->cognitive_long_term_memory === 'impaired') Impaired @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- SOCIAL FUNCTIONING --}}
        <div class="card mb-3">
            <div class="card-header">
                <span class="section-title">Social Functioning</span>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Change Since Last Review:</strong>
                    {{ $assessment->social_change ? 'Yes' : 'No' }}
                </div>

                <ul class="mb-0">
                    @if($assessment->social_socializes_appropriately) <li>Socializes Appropriately</li> @endif
                    @if($assessment->social_attends_group_activities) <li>Attends Group Activities</li> @endif
                    @if($assessment->social_isolates_self) <li>Isolates Self</li> @endif
                    @if($assessment->social_cooperative) <li>Cooperative</li> @endif
                    @if($assessment->social_friendly) <li>Friendly</li> @endif
                    @if($assessment->social_quiet) <li>Quiet</li> @endif
                </ul>
            </div>
        </div>

        {{-- COMMUNICATION --}}
        <div class="card mb-3">
            <div class="card-header">
                <span class="section-title">Communication</span>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Change Since Last Review:</strong>
                    {{ $assessment->communication_change ? 'Yes' : 'No' }}
                </div>

                <ul class="mb-0">
                    @if($assessment->communication_expresses_needs) <li>Expresses Needs</li> @endif
                    @if($assessment->communication_does_not_express) <li>Does Not Express Needs</li> @endif
                    @if($assessment->communication_requires_encouragement) <li>Requires Encouragement</li> @endif
                </ul>
            </div>
        </div>

        {{-- DECISION MAKING --}}
        <div class="card mb-3">
            <div class="card-header">
                <span class="section-title">Decision Making Ability</span>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Change Since Last Review:</strong>
                    {{ $assessment->decision_change ? 'Yes' : 'No' }}
                </div>

                <ul class="mb-0">
                    @if($assessment->decision_independent) <li>Independent</li> @endif
                    @if($assessment->decision_minimal_impairment) <li>Minimally Impaired</li> @endif
                    @if($assessment->decision_moderate_impairment) <li>Moderately Impaired</li> @endif
                    @if($assessment->decision_severe_impairment) <li>Severely Impaired</li> @endif
                </ul>
            </div>
        </div>

        {{-- MOOD / MENTAL HEALTH --}}
        <div class="card mb-3">
            <div class="card-header">
                <span class="section-title">Mood / Mental Health</span>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Change Since Last Review:</strong>
                    {{ $assessment->mood_change ? 'Yes' : 'No' }}
                </div>

                <div class="row mb-2">
                    <div class="col-md-6">
                        <strong>Mood / Behavior:</strong>
                        <ul class="mb-0">
                            @if($assessment->mood_normal) <li>Normal</li> @endif
                            @if($assessment->mood_stable) <li>Stable</li> @endif
                            @if($assessment->mood_anxious) <li>Anxious</li> @endif
                            @if($assessment->mood_lethargic) <li>Lethargic</li> @endif
                            @if($assessment->mood_depressed) <li>Depressed</li> @endif
                            @if($assessment->mood_manic) <li>Manic</li> @endif
                            @if($assessment->mood_agitated) <li>Agitated</li> @endif
                            @if($assessment->mood_combative) <li>Combative</li> @endif
                            @if($assessment->mood_pessimistic) <li>Pessimistic</li> @endif
                            @if($assessment->mood_optimistic) <li>Optimistic</li> @endif
                            @if($assessment->mood_confused) <li>Confused</li> @endif
                        </ul>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-4">
                        <strong>Mental Health Diagnosis:</strong><br>
                        {{ $assessment->mental_health_diagnosis ? 'Yes' : 'No' }}
                    </div>
                    <div class="col-md-8">
                        <strong>Diagnosis / Comments:</strong><br>
                        {{ $assessment->mental_health_diagnosis_type }}
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-6">
                        <strong>Treatment:</strong>
                        <ul class="mb-0">
                            @if($assessment->treatment_psychiatrist) <li>Psychiatrist</li> @endif
                            @if($assessment->treatment_medication_therapy) <li>Medication Therapy</li> @endif
                            @if($assessment->treatment_psychotherapy) <li>Psychotherapy</li> @endif
                            @if($assessment->treatment_none) <li>None</li> @endif
                        </ul>
                    </div>
                </div>

                @if($assessment->mood_comment)
                    <div class="mt-2">
                        <strong>Comments:</strong><br>
                        <span style="white-space: pre-wrap;">{{ $assessment->mood_comment }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- APPEARANCE / HYGIENE --}}
        <div class="card mb-3">
            <div class="card-header">
                <span class="section-title">Appearance / Hygiene</span>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Change Since Last Review:</strong>
                    {{ $assessment->appearance_change ? 'Yes' : 'No' }}
                </div>

                <ul class="mb-2">
                    @if($assessment->appearance_neatly_groomed) <li>Neatly Groomed</li> @endif
                    @if($assessment->appearance_presentable) <li>Presentable</li> @endif
                    @if($assessment->appearance_disheveled) <li>Disheveled</li> @endif
                    @if($assessment->appearance_appropriate_hygiene) <li>Appropriate Hygiene</li> @endif
                    @if($assessment->appearance_poor_hygiene) <li>Poor Hygiene</li> @endif
                </ul>

                @if($assessment->appearance_comment)
                    <div>
                        <strong>Comments:</strong><br>
                        <span style="white-space: pre-wrap;">{{ $assessment->appearance_comment }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- SERVICES / REFERRALS --}}
        <div class="card mb-3">
            <div class="card-header">
                <span class="section-title">Services / Referrals</span>
            </div>
            <div class="card-body">
                <ul class="mb-2">
                    @if($assessment->services_home_health) <li>Home Health</li> @endif
                    @if($assessment->services_meals_on_wheels) <li>Meals on Wheels</li> @endif
                    @if($assessment->services_food_stamp_recert) <li>Food Stamp Recertification</li> @endif
                    @if($assessment->services_medicaid_recert) <li>Medicaid Recertification</li> @endif
                    @if($assessment->services_geriatric_case_mgmt) <li>Geriatric Case Management</li> @endif
                    @if($assessment->services_ssi_advocacy) <li>SSI Advocacy</li> @endif
                    @if($assessment->services_no_needs_assessed) <li>No Needs Assessed</li> @endif
                </ul>

                @if($assessment->services_comment)
                    <div>
                        <strong>Comments:</strong><br>
                        <span style="white-space: pre-wrap;">{{ $assessment->services_comment }}</span>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
