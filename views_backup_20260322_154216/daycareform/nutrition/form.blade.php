@extends('admin.admin_dashboard')
@section('title', $mode==='edit' ? 'Nutrition Assessment - Edit' : 'Nutrition Assessment - Create')

@section('admin')
<div class="page-content">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            Nutrition Quarterly Assessment
            <small class="text-muted">({{ $type === 'A' ? 'Morning' : 'Noon' }})</small>
        </h4>

        <div class="d-flex gap-2">
            <a href="{{ route('nutrition.index', ['type' => $type, 'participant_id' => $member->ParticipantId]) }}"
               class="btn btn-secondary">&laquo; Back</a>

            @if(!empty($assessment?->id))
                <a href="{{ route('nutrition.show', ['id' => $assessment->id, 'type' => $type]) }}" class="btn btn-info">View</a>
                <a href="{{ route('nutrition.pdf', $assessment->id) }}" class="btn btn-danger">PDF</a>
            @endif
        </div>
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

    @php
        $isEdit = ($mode ?? 'create') === 'edit';
        $action = $isEdit ? route('nutrition.update', $assessment->id) : route('nutrition.store');
    @endphp

    <form method="post" action="{{ $action }}">
        @csrf

        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="participant_id" value="{{ $member->ParticipantId }}">

        <div class="card mb-3">
            <div class="card-header"><strong>Member</strong></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control"
                               value="{{ trim(($member->LastName ?? '').', '.($member->FirstName ?? '')) }}" readonly>
                    </div>
                     <label class="form-label">Medical Record #</label>
                        <input type="text"
                            name="medical_record_no"
                            class="form-control"
                            maxlength="50"
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
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Assessment</strong></div>
            <div class="card-body">

                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <label class="form-label">Weight (lbs)</label>
                        <input type="number" step="0.01" name="weight_lbs" class="form-control"
                               value="{{ old('weight_lbs', $assessment->weight_lbs ?? '') }}">
                    </div>

                    <div class="col-md-9">
                        <label class="form-label d-block">Weight Status</label>
                        @php $ws = old('weight_status', $assessment->weight_status ?? ''); @endphp
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="weight_status" value="lost" id="ws_lost" {{ $ws==='lost' ? 'checked' : '' }}>
                                <label class="form-check-label" for="ws_lost">Lost</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="weight_status" value="gain" id="ws_gain" {{ $ws==='gain' ? 'checked' : '' }}>
                                <label class="form-check-label" for="ws_gain">Gain</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="weight_status" value="stable" id="ws_stable" {{ $ws==='stable' ? 'checked' : '' }}>
                                <label class="form-check-label" for="ws_stable">Stable</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label">New Medical Diagnosis</label>
                        <textarea name="new_medical_diagnosis" class="form-control" rows="2">{{ old('new_medical_diagnosis', $assessment->new_medical_diagnosis ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Medication Changes</label>
                        <textarea name="medication_changes" class="form-control" rows="2">{{ old('medication_changes', $assessment->medication_changes ?? '') }}</textarea>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label d-block">Appetite / Hydration</label>
                        @php $ah = old('appetite_hydration', $assessment->appetite_hydration ?? ''); @endphp
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="appetite_hydration" value="good" id="ah_good" {{ $ah==='good' ? 'checked' : '' }}>
                                <label class="form-check-label" for="ah_good">Good (&gt; 75%)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="appetite_hydration" value="fair" id="ah_fair" {{ $ah==='fair' ? 'checked' : '' }}>
                                <label class="form-check-label" for="ah_fair">Fair (&gt; 50%)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="appetite_hydration" value="poor" id="ah_poor" {{ $ah==='poor' ? 'checked' : '' }}>
                                <label class="form-check-label" for="ah_poor">Poor (&lt; 50%)</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Current Diet Order</label>
                        <textarea name="current_diet_order" class="form-control" rows="2">{{ old('current_diet_order', $assessment->current_diet_order ?? '') }}</textarea>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label d-block">Changes in Diet Order</label>
                        @php $doc = old('diet_order_changed', ($assessment->diet_order_changed ?? 0) ? '1' : '0'); @endphp
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="diet_order_changed" value="1" id="doc_yes" {{ $doc==='1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="doc_yes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="diet_order_changed" value="0" id="doc_no" {{ $doc==='0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="doc_no">No</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Labs</label>
                        <textarea name="labs" class="form-control" rows="2">{{ old('labs', $assessment->labs ?? '') }}</textarea>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Comments</label>
                    <textarea name="comments" class="form-control" rows="3">{{ old('comments', $assessment->comments ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Nutrition Care Plan (Goals)</strong></div>
            <div class="card-body">
                <div class="row">
                    @foreach([
                        'cp_tolerate_current_diet' => 'Client will tolerate current diet',
                        'cp_bp_bg_within_limits' => 'Blood Pressure / Blood Glucose levels will be within acceptable limits',
                        'cp_weight_stable_or_change' => "Client's weight will be stable or will lose/gain weight",
                        'cp_understands_diet' => 'Client will verbalize knowledge/understanding of the current diet and will try to follow it at home',
                    ] as $f => $lbl)
                        <div class="col-md-6 form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="{{ $f }}" name="{{ $f }}"
                                   {{ old($f, $assessment->$f ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $f }}">{{ $lbl }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Recommendations / Interventions</strong></div>
            <div class="card-body">
                <div class="row">
                    @foreach([
                        'rec_decrease_salty_saturated' => 'Decrease salty foods/snacks & saturated fats',
                        'rec_encourage_weight_1_2' => 'Encourage increase/decrease 1–2# per week/month',
                        'rec_increase_fiber_protein_calcium_omega3' => 'Increase fiber/protein/calcium/omega-3 foods',
                        'rec_limit_spicy_greasy_acidic_citrus_caffeine' => 'Limit spicy/greasy/acidic foods, citrus juices and caffeinated beverages',
                        'rec_small_frequent_meals_snack' => 'Small frequent meals and extra snack between meals as needed',
                        'rec_softer_foods_chewing' => 'Provide softer foods to facilitate chewing',
                        'rec_cho_counseling_timing_consistency' => 'CHO counseling, meal timing and food consistency',
                        'rec_limit_vitamin_k_foods' => 'Limit dark green leafy vegetables and foods high in vitamin K',
                        'rec_increase_physical_activity' => 'Encourage increase physical activity (walking/light exercise) as tolerated',
                    ] as $f => $lbl)
                        <div class="col-md-6 form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="{{ $f }}" name="{{ $f }}"
                                   {{ old($f, $assessment->$f ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $f }}">{{ $lbl }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Dietitian Signature</strong></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-8">
                        <label class="form-label">Signature / Name</label>
                        <input type="text" name="dietitian_signature" class="form-control"
                               value="{{ old('dietitian_signature', $assessment->dietitian_signature ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Signed Date</label>
                        <input type="date" name="signed_date" class="form-control"
                               value="{{ old('signed_date', $assessment->signed_date ?? '') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-success">{{ $isEdit ? 'Update' : 'Save' }}</button>

            <a href="{{ route('nutrition.index', ['type' => $type, 'participant_id' => $member->ParticipantId]) }}"
               class="btn btn-secondary">Cancel</a>
        </div>

    </form>

  </div>
</div>
@endsection
