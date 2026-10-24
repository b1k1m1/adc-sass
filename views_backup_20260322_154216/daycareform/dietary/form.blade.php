@extends('admin.admin_dashboard')
@section('title', 'Dietary Initial Assessment')

@section('admin')

    <div class="page-content">
    <div class="container-fluid">

            @php
            $ln  = $member->LastName ?? $member->lastname ?? '';
            $fn  = $member->FirstName ?? $member->firstname ?? '';
            $pid = $member->ParticipantId ?? '';
            $dob = $member->DateOfBirth ?? $member->dateofbirth ?? null;
            $adm = $member->AdmissionDate ?? $member->admission_date ?? null;
            @endphp

            <div class="d-flex justify-content-between align-items-center mb-2">
            <a href="{{ route('dietary.index', ['type'=>$type, 'participant_id'=>$pid]) }}"
                class="btn btn-secondary">&laquo; Back</a>
            <h5 class="mb-0">
                {{ $mode === 'edit' ? 'Edit' : 'Add' }} Dietary Initial Assessment
            </h5>
            </div>

            @if(session('error'))
            <div class="alert alert-danger alert-sm">{{ session('error') }}</div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-sm">
                <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
                </ul>
            </div>
            @endif

        <div class="card">
        <div class="card-body">

                    <form method="post"
                        action="{{ $mode==='edit'
                                    ? route('dietary.update', ['id'=>$assessment->id])
                                    : route('dietary.store') }}">
                    @csrf

            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="participant_id" value="{{ $pid }}">

            {{-- Header info --}}
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                <label class="form-label">Participant</label>
                <input type="text" class="form-control" value="{{ $pid }} - {{ $ln }}, {{ $fn }}" readonly>
                </div>

                <div class="col-md-3">
                <label class="form-label">Admission Date</label>
                <input type="text" class="form-control"
                        value="{{ !empty($adm) ? \Carbon\Carbon::parse($adm)->format('m/d/Y') : '' }}" readonly>
                </div>

                <div class="col-md-3">
                <label class="form-label">DOB</label>
                <input type="text" class="form-control"
                        value="{{ !empty($dob) ? \Carbon\Carbon::parse($dob)->format('m/d/Y') : '' }}" readonly>
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-3">
                <label class="form-label">MR# (Manual)</label>
                <input type="text" name="medical_record_no" maxlength="50"
                        class="form-control"
                        value="{{ old('medical_record_no', $assessment->medical_record_no ?? '') }}">
                </div>

                <div class="col-md-3">
                <label class="form-label">Assessment Date</label>
                    <input type="date" name="assessment_date" class="form-control"
                        value="{{ old('assessment_date',
                            isset($assessment) && $assessment->assessment_date
                                ? \Carbon\Carbon::parse($assessment->assessment_date)->format('Y-m-d')
                                : date('Y-m-d')
                        ) }}">
                </div>

                <div class="col-md-2">
                <label class="form-label">Height</label>
                <input type="text" name="height" class="form-control"
                        value="{{ old('height', $assessment->height ?? '') }}">
                </div>

                <div class="col-md-2">
                <label class="form-label">Weight</label>
                <input type="text" name="weight" class="form-control"
                        value="{{ old('weight', $assessment->weight ?? '') }}">
                </div>

                <div class="col-md-2">
                <label class="form-label">IBW</label>
                <input type="text" name="ibw" class="form-control"
                        value="{{ old('ibw', $assessment->ibw ?? '') }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Weight Changes</label>
                <input type="text" name="weight_changes" class="form-control"
                    value="{{ old('weight_changes', $assessment->weight_changes ?? '') }}">
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                <label class="form-label">Eating Problems / Devices</label>
                <input type="text" name="eating_problems_devices" class="form-control"
                        value="{{ old('eating_problems_devices', $assessment->eating_problems_devices ?? '') }}">
                </div>
                <div class="col-md-6">
                <label class="form-label">Appetite</label>
                <input type="text" name="appetite" class="form-control"
                        value="{{ old('appetite', $assessment->appetite ?? '') }}">
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                <label class="form-label">Religious Restrictions</label>
                <input type="text" name="religious_restrictions" class="form-control"
                        value="{{ old('religious_restrictions', $assessment->religious_restrictions ?? '') }}">
                </div>
                <div class="col-md-3">
                <label class="form-label">Teeth</label>
                <input type="text" name="teeth" class="form-control"
                        value="{{ old('teeth', $assessment->teeth ?? '') }}">
                </div>
                <div class="col-md-3">
                <label class="form-label">Dentures</label>
                <input type="text" name="dentures" class="form-control"
                        value="{{ old('dentures', $assessment->dentures ?? '') }}">
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                <label class="form-label">Diet Order</label>
                <input type="text" name="diet_order" class="form-control"
                        value="{{ old('diet_order', $assessment->diet_order ?? '') }}">
                </div>
                <div class="col-md-6">
                <label class="form-label">Food Allergy</label>
                <input type="text" name="food_allergy" class="form-control"
                        value="{{ old('food_allergy', $assessment->food_allergy ?? '') }}">
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                <label class="form-label">Diagnosis</label>
                <input type="text" name="diagnosis" class="form-control"
                        value="{{ old('diagnosis', $assessment->diagnosis ?? '') }}">
                </div>
                <div class="col-md-6">
                <label class="form-label">Medications</label>
                <input type="text" name="medications" class="form-control"
                        value="{{ old('medications', $assessment->medications ?? '') }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Labs</label>
                <textarea name="labs" class="form-control" rows="2">{{ old('labs', $assessment->labs ?? '') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Food Preferences / Dislikes</label>
                <textarea name="food_preferences_dislikes" class="form-control" rows="2">{{ old('food_preferences_dislikes', $assessment->food_preferences_dislikes ?? '') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Initial Assessment</label>
                <textarea name="initial_assessment" class="form-control" rows="3">{{ old('initial_assessment', $assessment->initial_assessment ?? '') }}</textarea>
            </div>

            {{-- Goals --}}
            <div class="card mb-3">
                <div class="card-header"><strong>Nutrition Care Plan (Goals)</strong></div>
                <div class="card-body">
                @foreach([
                    'goal_tolerate_current_diet' => 'Client will tolerate current diet',
                    'goal_bp_bg_within_limits' => 'Blood Pressure / Blood Glucose within acceptable limits',
                    'goal_weight_stable_or_lose_gain' => 'Weight stable or will lose/gain',
                    'goal_select_healthy_choices' => 'Select healthy food choices',
                    'goal_verbalize_knowledge_follow_home' => 'Verbalize knowledge and will follow diet at home',
                ] as $f=>$lbl)
                    <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="{{ $f }}"
                            {{ old($f, $assessment->$f ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label">{{ $lbl }}</label>
                    </div>
                @endforeach
                </div>
            </div>

            {{-- Interventions --}}
            <div class="card mb-3">
                <div class="card-header"><strong>Interventions</strong></div>
                <div class="card-body">
                @foreach([
                    'int_educate_decrease_salty_alternates' => 'Educate to decrease salty foods and alternate',
                    'int_encourage_change_1_2_per_week_month' => 'Encourage increase/decrease 1–2# per week/month',
                    'int_encourage_consume_over_75_each_meal' => 'Encourage to consume >75% each meal',
                    'int_encourage_increase_dietary_fiber' => 'Encourage to increase dietary fiber',
                    'int_educate_avoid_dark_green_vit_k' => 'Educate to avoid dark green vegetables (Vit K)',
                    'int_educate_cho_counting_timing_exercise' => 'Educate on CHO counting, meal timing, exercise',
                    'int_educate_increase_decrease_fluids' => 'Educate increase/decrease fluids',
                    'int_monitor_alt_food_consistency' => 'Monitor / alternate food consistency',
                    'int_encourage_increase_protein' => 'Encourage to increase protein',
                    'int_monitor_monthly_weights' => 'Monitor monthly weights',
                    'int_encourage_physical_activity' => 'Encourage physical activity',
                ] as $f=>$lbl)
                    <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="{{ $f }}"
                            {{ old($f, $assessment->$f ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label">{{ $lbl }}</label>
                    </div>
                @endforeach
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-8">
                <label class="form-label">Dietitian Signature/Title</label>
                <input type="text" name="dietitian_signature_title" class="form-control"
                        value="{{ old('dietitian_signature_title', $assessment->dietitian_signature_title ?? '') }}">
                </div>
                <div class="col-md-4">
                <label class="form-label">Date</label>
                <input type="date" name="signed_date" class="form-control"
                        value="{{ old('signed_date', $assessment->signed_date ?? '') }}">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                {{ $mode==='edit' ? 'Update' : 'Save' }}
                </button>
                <a href="{{ route('dietary.index', ['type'=>$type, 'participant_id'=>$pid]) }}"
                class="btn btn-secondary">Cancel</a>
            </div>

            </form>
        </div>
    </div>

  </div>
</div>
@endsection
