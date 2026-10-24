@extends('admin.admin_dashboard')
@section('title', 'Nursing Assessment')

@section('admin')
<div class="page-content">
  <div class="container-fluid">

     <h4 class="mb-3">{{ $mode==='create'?'Add':'Edit' }} Nursing Assessment</h4>

<style>
    .sec-title{ font-weight:700; margin:10px 0 6px; text-transform:uppercase; }
    .sec-box{ border:1px solid #d9d9d9; padding:14px; border-radius:6px; margin-bottom:12px; }
    .form-check { padding-left: 1.8rem; } /* pushes label in */
    .form-check-input { margin-left: -1.4rem; } /* keeps checkbox inside nicely */

</style>

<form method="post"
      action="{{ $mode==='create'
        ? route('nursing-assessment.store')
        : route('nursing-assessment.update', $assessment->id) }}">
@csrf

<input type="hidden" name="participant_id" value="{{ $member->ParticipantId }}">
<input type="hidden" name="type" value="{{ $type }}">
<input type="hidden" name="shift_type" value="{{ $type }}">

<div class="alert alert-light border mb-3">
  <b>Member:</b> {{ $member->LastName }}, {{ $member->FirstName }}
  &nbsp; <b>Participant ID:</b> {{ $member->ParticipantId }}
  &nbsp; <b>Shift:</b> {{ $type }}
</div>

<div class="sec-title">Header</div>
<div class="sec-box">
    <div class="row g-2">
        <div class="col-md-4">
            <label class="form-label">Date of Assessment</label>
            <input type="date" name="assessment_date" class="form-control"
                value="{{ old('assessment_date',
                    isset($assessment->assessment_date)
                        ? \Carbon\Carbon::parse($assessment->assessment_date)->format('Y-m-d')
                        : now()->format('Y-m-d')
                ) }}" required>
        </div>

        <div class="col-md-4">
            <label class="form-label">D.O.A.</label>
            <input type="date" name="doa" class="form-control"
                value="{{ old('doa', $assessment->doa ?? '') }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">Medical Record No</label>
            <input type="text" name="medical_record_no" class="form-control"
                value="{{ old('medical_record_no', $assessment->medical_record_no ?? '') }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">Assessment Type</label>
            @php $atype = old('assessment_type', $assessment->assessment_type ?? 'Initial'); @endphp
            <select name="assessment_type" class="form-select" required>
                <option value="Initial" {{ $atype==='Initial'?'selected':'' }}>Initial</option>
                <option value="Annual" {{ $atype==='Annual'?'selected':'' }}>Annual (Within 14 days)</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Height</label>
            <input type="text" name="height" class="form-control"
                value="{{ old('height', $assessment->height ?? '') }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">Weight</label>
            <input type="text" name="weight" class="form-control"
                value="{{ old('weight', $assessment->weight ?? '') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">Allergies</label>
            <input type="text" name="allergies" class="form-control"
                value="{{ old('allergies', $assessment->allergies ?? '') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">Diagnosis</label>
            <input type="text" name="diagnosis" class="form-control"
                value="{{ old('diagnosis', $assessment->diagnosis ?? '') }}">
        </div>
    </div>
</div>

<div class="sec-title">Cognitive Function</div>
<div class="sec-box">
    <div class="row">
        @foreach([
            'cognitive_alert' => 'Alert',
            'cognitive_oriented_person' => 'Oriented to Person',
            'cognitive_oriented_place' => 'Place',
            'cognitive_oriented_time' => 'Time',
            'cognitive_orientation_fluctuates' => 'Orientation fluctuates',
            'cognitive_confused' => 'Confused',
            'cognitive_wanders_unaware' => 'Wanders / Unaware',
        ] as $f=>$lbl)
        <div class="col-md-4 form-check mb-1">
            <input class="form-check-input" type="checkbox" name="{{ $f }}"
                {{ old($f, $assessment->$f ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">{{ $lbl }}</label>
        </div>
        @endforeach
    </div>

    <div class="row g-2 mt-2">
        <div class="col-md-3">
            <label class="form-label">Memory - Short term</label>
            @php $v = old('memory_short_term', $assessment->memory_short_term ?? ''); @endphp
            <select name="memory_short_term" class="form-select">
                <option value="">-- Select --</option>
                <option value="OK" {{ $v==='OK'?'selected':'' }}>OK</option>
                <option value="Problem" {{ $v==='Problem'?'selected':'' }}>Problem</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Memory - Long term</label>
            @php $v = old('memory_long_term', $assessment->memory_long_term ?? ''); @endphp
            <select name="memory_long_term" class="form-select">
                <option value="">-- Select --</option>
                <option value="OK" {{ $v==='OK'?'selected':'' }}>OK</option>
                <option value="Problem" {{ $v==='Problem'?'selected':'' }}>Problem</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Decision making ability</label>
            @php $v = old('decision_making', $assessment->decision_making ?? ''); @endphp
            <select name="decision_making" class="form-select">
                <option value="">-- Select --</option>
                <option value="Independent" {{ $v==='Independent'?'selected':'' }}>Independent</option>
                <option value="Impaired" {{ $v==='Impaired'?'selected':'' }}>Impaired</option>
                <option value="Severely impaired" {{ $v==='Severely impaired'?'selected':'' }}>Severely impaired</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Understands information</label>
            @php $v = old('understanding_info', $assessment->understanding_info ?? ''); @endphp
            <select name="understanding_info" class="form-select">
                <option value="">-- Select --</option>
                <option value="Understands without difficulty" {{ $v==='Understands without difficulty'?'selected':'' }}>Understands without difficulty</option>
                <option value="Understands but has difficulty" {{ $v==='Understands but has difficulty'?'selected':'' }}>Understands but has difficulty</option>
                <option value="Cannot understand" {{ $v==='Cannot understand'?'selected':'' }}>Cannot understand</option>
            </select>
        </div>
    </div>
</div>

<div class="sec-title">Communication</div>
<div class="sec-box">
    <div class="row">
        @foreach([
            'expr_speech' => 'Speech',
            'expr_writing' => 'Writing',
            'expr_signs_gestures_sounds' => 'Signs/Gestures/Sounds',
            'expr_incoherent_speech' => 'Incoherent speech',
            'communication_board' => 'Communication board',
        ] as $f => $lbl)
            <div class="col-md-3 form-check mb-1 ps-4">
                <input class="form-check-input" type="checkbox" name="{{ $f }}"
                    {{ old($f, $assessment->$f ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">{{ $lbl }}</label>
            </div>
        @endforeach
    </div>
    <div class="row mt-4">
        @foreach([
            'communicates_understood' => 'Communicates and is understood',
            'communicates_difficult_to_understand' => 'Communicates but difficult to understand',
            'cannot_communicate_needs' => 'Cannot communicate or convey needs',
        ] as $f=>$lbl)
        <div class="col-md-4 form-check mb-1">
            <input class="form-check-input" type="checkbox" name="{{ $f }}"
                {{ old($f, $assessment->$f ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">{{ $lbl }}</label>
        </div>
        @endforeach
    </div>

    <div class="row g-2 mt-2">
        <div class="col-md-3">
            <label class="form-label">Speech</label>
            @php $v = old('speech_quality', $assessment->speech_quality ?? ''); @endphp
            <select name="speech_quality" class="form-select">
                <option value="">-- Select --</option>
                <option value="Clear" {{ $v==='Clear'?'selected':'' }}>Clear</option>
                <option value="Slurred" {{ $v==='Slurred'?'selected':'' }}>Slurred</option>
                <option value="Aphasia" {{ $v==='Aphasia'?'selected':'' }}>Aphasia</option>
            </select>
        </div>

        <div class="col-md-5">
            <label class="form-label">Primary language spoken</label>
            <input type="text" name="primary_language_spoken" class="form-control"
                value="{{ old('primary_language_spoken', $assessment->primary_language_spoken ?? '') }}">
        </div>

    </div>
</div>

<div class="sec-title">Hearing</div>
<div class="sec-box">
    <div class="row g-2">
        <div class="col-md-3">
            <label class="form-label">Right ear</label>
            @php $v = old('right_ear_status', $assessment->right_ear_status ?? ''); @endphp
            <select name="right_ear_status" class="form-select">
                <option value="">-- Select --</option>
                @foreach(['Adequate','Impaired','Hearing Aide','Deaf'] as $x)
                    <option value="{{ $x }}" {{ $v===$x?'selected':'' }}>{{ $x }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Left ear</label>
            @php $v = old('left_ear_status', $assessment->left_ear_status ?? ''); @endphp
            <select name="left_ear_status" class="form-select">
                <option value="">-- Select --</option>
                @foreach(['Adequate','Impaired','Hearing Aide','Deaf'] as $x)
                    <option value="{{ $x }}" {{ $v===$x?'selected':'' }}>{{ $x }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Date of last hearing evaluation</label>
            <input type="date" name="date_last_hearing_eval" class="form-control"
                value="{{ old('date_last_hearing_eval', $assessment->date_last_hearing_eval ?? '') }}">
        </div>

        <div class="col-md-3"></div>
    </div>

    <div class="row mt-2">
        <div class="col-md-6 form-check">
            <input class="form-check-input" type="checkbox" name="hearing_minimal_difficulty_not_quiet"
                {{ old('hearing_minimal_difficulty_not_quiet', $assessment->hearing_minimal_difficulty_not_quiet ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">Minimal difficulty when not in quiet listening conditions</label>
        </div>
        <div class="col-md-6 form-check">
            <input class="form-check-input" type="checkbox" name="hearing_highly_impaired_no_useful"
                {{ old('hearing_highly_impaired_no_useful', $assessment->hearing_highly_impaired_no_useful ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">Highly impaired, lack of useful hearing</label>
        </div>
    </div>
</div>

<div class="sec-title">Vision</div>
<div class="sec-box">
    <div class="row g-2">
        <div class="col-md-4">
            <label class="form-label">Ability to see</label>
            @php $v = old('vision_ability', $assessment->vision_ability ?? ''); @endphp
            <select name="vision_ability" class="form-select">
                <option value="">-- Select --</option>
                <option value="Adequate" {{ $v==='Adequate'?'selected':'' }}>Adequate</option>
                <option value="Adequate with glasses" {{ $v==='Adequate with glasses'?'selected':'' }}>Adequate with glasses</option>
                <option value="Impaired, uncorrected" {{ $v==='Impaired, uncorrected'?'selected':'' }}>Impaired, uncorrected</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Date of last eye exam</label>
            <input type="date" name="date_last_eye_exam" class="form-control"
                value="{{ old('date_last_eye_exam', $assessment->date_last_eye_exam ?? '') }}">
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-6">
            <div class="fw-bold mb-1">Cataracts - Right</div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="cataracts_right"
                    {{ old('cataracts_right', $assessment->cataracts_right ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">Cataracts Right</label>
            </div>
            <div class="d-flex gap-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="cataracts_right_sx_yes"
                        {{ old('cataracts_right_sx_yes', $assessment->cataracts_right_sx_yes ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label">Sx correction Yes</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="cataracts_right_sx_no"
                        {{ old('cataracts_right_sx_no', $assessment->cataracts_right_sx_no ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label">No</label>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="fw-bold mb-1">Cataracts - Left</div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="cataracts_left"
                    {{ old('cataracts_left', $assessment->cataracts_left ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">Cataracts Left</label>
            </div>
            <div class="d-flex gap-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="cataracts_left_sx_yes"
                        {{ old('cataracts_left_sx_yes', $assessment->cataracts_left_sx_yes ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label">Sx correction Yes</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="cataracts_left_sx_no"
                        {{ old('cataracts_left_sx_no', $assessment->cataracts_left_sx_no ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label">No</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        @foreach([
            'can_read_newsprint' => 'Can read newsprint',
            'can_read_large_print' => 'Can read large print',
            'macular_degeneration' => 'Macular degeneration',
            'glaucoma' => 'Glaucoma',
            'blind_right' => 'Blind - Right',
            'blind_left' => 'Blind - Left',
        ] as $f=>$lbl)
        <div class="col-md-4 form-check mb-1">
            <input class="form-check-input" type="checkbox" name="{{ $f }}"
                {{ old($f, $assessment->$f ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">{{ $lbl }}</label>
        </div>
        @endforeach
    </div>
</div>

<div class="sec-title">Physical Functioning</div>
<div class="sec-box">
    <div class="row">
        <div class="col-md-6">
            <div class="fw-bold">Ambulates</div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="ambulates_alone"
                    {{ old('ambulates_alone', $assessment->ambulates_alone ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">Alone</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="ambulates_with_assistance"
                    {{ old('ambulates_with_assistance', $assessment->ambulates_with_assistance ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">With assistance</label>
            </div>
        </div>

        <div class="col-md-6">
            <div class="fw-bold">Uses</div>
            @foreach([
                'uses_cane'=>'Cane',
                'uses_walker'=>'Walker',
                'uses_wheelchair'=>'Wheelchair'
            ] as $f=>$lbl)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="{{ $f }}"
                    {{ old($f, $assessment->$f ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">{{ $lbl }}</label>
            </div>
            @endforeach
        </div>
    </div>

    <hr>

    <div class="fw-bold">Wheelchair</div>
    <div class="row">
        @foreach([
            'wheelchair_independently'=>'Independently',
            'wheelchair_propel_with_help'=>'Can propel with help',
            'wheelchair_unable_to_propel'=>'Unable to propel',
        ] as $f=>$lbl)
        <div class="col-md-4 form-check mb-1">
            <input class="form-check-input" type="checkbox" name="{{ $f }}"
                {{ old($f, $assessment->$f ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">{{ $lbl }}</label>
        </div>
        @endforeach
    </div>

    <hr>

    <div class="fw-bold">Transfers</div>
    <div class="row">
        @foreach([
            'transfers_independently'=>'Independently',
            'transfers_with_assistance_1'=>'With assistance of 1',
            'transfers_with_assistance_2'=>'With assistance of 2',
        ] as $f=>$lbl)
        <div class="col-md-4 form-check mb-1">
            <input class="form-check-input" type="checkbox" name="{{ $f }}"
                {{ old($f, $assessment->$f ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">{{ $lbl }}</label>
        </div>
        @endforeach
    </div>
</div>

<button class="btn btn-primary">Save</button>
<a href="{{ route('nursing-assessment.index', ['type'=>$type]) }}" class="btn btn-secondary">Cancel</a>
</form>

  </div>
</div>
@endsection
