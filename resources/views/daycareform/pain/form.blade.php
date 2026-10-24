@extends('admin.admin_dashboard')
@section('title', $mode==='edit' ? 'Pain Assessment - Edit' : 'Pain Assessment - Create')

@section('admin')
<div class="page-content">
  <div class="container-fluid">

    @if($errors->any())
      <div class="alert alert-danger alert-sm">
        <ul class="mb-0">
          @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
        </ul>
      </div>
    @endif

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
          {{ $mode==='edit' ? 'Pain Assessment (Edit)' : 'Pain Assessment (Create)' }}
        </h5>

        <div class="d-flex gap-2">
          <a href="{{ route('pain.index', ['type'=>$type, 'participant_id'=>($member->ParticipantId ?? '')]) }}"
             class="btn btn-secondary">{{ t('common.back','Back') }}</a>

          @if($mode==='edit')
            <a href="{{ route('pain.show', ['id'=>$assessment->id, 'type'=>$type]) }}"
               class="btn btn-outline-info">View</a>
            <a href="{{ route('pain.pdf', $assessment->id) }}"
               class="btn btn-outline-danger">PDF</a>
          @endif
        </div>
      </div>

      <div class="card-body">
        @php
          $pid = $member->ParticipantId ?? '';
          $ln  = $member->LastName ?? $member->lastname ?? '';
          $fn  = $member->FirstName ?? $member->firstname ?? '';
        @endphp

        <div class="mb-2">
          <strong>Member:</strong> {{ $pid }} - {{ $ln }}, {{ $fn }}
          &nbsp; | &nbsp; <strong>Shift:</strong> {{ $type==='A'?'Morning':'Noon' }}
        </div>

        <form method="post"
              action="{{ $mode==='edit' ? route('pain.update', $assessment->id) : route('pain.store') }}">
          @csrf

          {{-- Keep context --}}
          <input type="hidden" name="type" value="{{ $type }}">
          <input type="hidden" name="participant_id" value="{{ old('participant_id', $assessment->participant_id ?? $pid) }}">

          <style>
            .sec-title{ font-weight:700; margin-top:14px; }
            .sec-box{ border:1px solid #ddd; border-radius:8px; padding:10px; }
            .chk-grid{ display:flex; flex-wrap:wrap; gap:12px; }
            .chk-item{ min-width:220px; }
            .tiny{ font-size:12px; color:#666; }

            .form-check-input{
                width: 1.25em !important;
                height: 1.25em !important;
                margin-top: 0.2em;
                }
                .form-check-label{
                font-size: 14px;
             }
          </style>

          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Assessment Date</label>
              <input type="date" name="assessment_date" class="form-control"
                    value="{{ old('assessment_date',
                        isset($assessment) && $assessment->assessment_date
                            ? \Carbon\Carbon::parse($assessment->assessment_date)->format('Y-m-d')
                            : date('Y-m-d')
                    ) }}">
            </div>

            <div class="col-md-3">
              <label class="form-label">Medical Record No</label>
              <input type="text" name="medical_record_no" class="form-control"
                     value="{{ old('medical_record_no', $assessment->medical_record_no ?? '') }}">
            </div>

            <div class="col-md-6">
              <label class="form-label">Reason for assessment</label>
              <div class="sec-box chk-grid">
                @php
                  $reasons = [
                    'reason_new_admission' => 'New admission',
                    'reason_readmission' => 'Readmission',
                    'reason_further_assessment' => 'Further Assessment',
                    'reason_change_in_condition' => 'Change in condition',
                    'reason_quarterly' => 'Quarterly',
                  ];
                @endphp
                @foreach($reasons as $k=>$label)
                  <label class="form-check chk-item">
                    <input class="form-check-input" type="checkbox" name="{{ $k }}"
                      {{ old($k, $assessment->$k ?? false) ? 'checked' : '' }}>
                    <span class="form-check-label">{{ $label }}</span>
                  </label>
                @endforeach
              </div>
            </div>

            <div class="col-md-12">
              <div class="sec-title">1. Location of pain</div>
              <div class="sec-box">
                <textarea name="pain_location" class="form-control" rows="3"
                  placeholder="Describe location of pain...">{{ old('pain_location', $assessment->pain_location ?? '') }}</textarea>
              </div>
            </div>

            <div class="col-md-12">
              <div class="sec-title">2. Severity of Pain</div>
              <div class="sec-box">
                <div class="row g-2">
                  <div class="col-md-3">
                    <label class="form-label">Score (0-10)</label>
                    <input type="number" min="0" max="10" name="pain_severity_score" class="form-control"
                      value="{{ old('pain_severity_score', $assessment->pain_severity_score ?? '') }}">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Label</label>
                    <select name="pain_severity_label" class="form-select">
                      @php $lbl = old('pain_severity_label', $assessment->pain_severity_label ?? ''); @endphp
                      <option value="">-- Select --</option>
                      @foreach(['mild','discomforting','distressing','horrible','excruciating'] as $x)
                        <option value="{{ $x }}" {{ $lbl===$x?'selected':'' }}>{{ $x }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-6">
                    <div class="tiny">If no pain is present, complete sections 6 and 7 (per your form).</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-12">
              <div class="sec-title">Questions / Comments</div>
              <div class="sec-box">
                <div class="row g-2">
                  <div class="col-md-6">
                    <label class="form-label">Present level of pain</label>
                    <textarea name="q_present_level" class="form-control" rows="2">{{ old('q_present_level', $assessment->q_present_level ?? '') }}</textarea>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Rate at least (0-10)</label>
                    <input type="number" min="0" max="10" name="q_least_rate" class="form-control"
                      value="{{ old('q_least_rate', $assessment->q_least_rate ?? '') }}">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Rate at worst (0-10)</label>
                    <input type="number" min="0" max="10" name="q_worst_rate" class="form-control"
                      value="{{ old('q_worst_rate', $assessment->q_worst_rate ?? '') }}">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">What makes the pain better?</label>
                    <textarea name="q_better" class="form-control" rows="2">{{ old('q_better', $assessment->q_better ?? '') }}</textarea>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">What makes the pain worse?</label>
                    <textarea name="q_worse" class="form-control" rows="2">{{ old('q_worse', $assessment->q_worse ?? '') }}</textarea>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Continuous or intermittent?</label>
                    @php $c = old('q_continuous', $assessment->q_continuous ?? ''); @endphp
                    <select name="q_continuous" class="form-select">
                      <option value="">-- Select --</option>
                      <option value="continuous" {{ $c==='continuous'?'selected':'' }}>Continuous</option>
                      <option value="intermittent" {{ $c==='intermittent'?'selected':'' }}>Intermittent</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">When did the pain start?</label>
                    <input type="text" name="q_start" class="form-control"
                      value="{{ old('q_start', $assessment->q_start ?? '') }}">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Cause of this pain?</label>
                    <input type="text" name="q_cause" class="form-control"
                      value="{{ old('q_cause', $assessment->q_cause ?? '') }}">
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Satisfied level (0-10)</label>
                    <input type="number" min="0" max="10" name="q_satisfied_level" class="form-control"
                      value="{{ old('q_satisfied_level', $assessment->q_satisfied_level ?? '') }}">
                  </div>
                </div>
              </div>
            </div>

            {{-- 3. Quality --}}
            <div class="col-md-12">
              <div class="sec-title">3. Quality (check words that describe pain)</div>
              <div class="sec-box chk-grid">
                @php
                  $qualities = [
                    'qual_aching'=>'aching','qual_throbbing'=>'throbbing','qual_shooting'=>'shooting','qual_stabbing'=>'stabbing','qual_penetrating'=>'penetrating','qual_sharp'=>'sharp',
                    'qual_burning'=>'burning','qual_tender'=>'tender','qual_exhausting'=>'exhausting','qual_tiring'=>'tiring','qual_unbearable'=>'unbearable','qual_numb'=>'numb',
                    'qual_nagging'=>'nagging','qual_hammering'=>'hammering','qual_pins_needles'=>'pins & needles','qual_gnawing'=>'gnawing','qual_tingling'=>'tingling',
                    'qual_stretching'=>'stretching','qual_pulling'=>'pulling',
                  ];
                @endphp
                @foreach($qualities as $k=>$label)
                  <label class="form-check chk-item">
                    <input class="form-check-input" type="checkbox" name="{{ $k }}"
                      {{ old($k, $assessment->$k ?? false) ? 'checked' : '' }}>
                    <span class="form-check-label">{{ $label }}</span>
                  </label>
                @endforeach

                <div class="w-100"></div>
                <div class="col-12">
                  <input type="text" name="qual_other" class="form-control"
                    placeholder="Other..."
                    value="{{ old('qual_other', $assessment->qual_other ?? '') }}">
                </div>
              </div>
            </div>

            {{-- 4. ADL --}}
            <div class="col-md-12">
              <div class="sec-title">4. Effects of pain on activities of daily living</div>
              <div class="sec-box">
                @php
                  $adlRows = [
                    ['sleep and rest','adl_sleep_yn'],
                    ['social activities','adl_social_yn'],
                    ['appetite','adl_appetite_yn'],
                    ['physical activity & mobility','adl_physical_yn'],
                    ['emotions','adl_emotions_yn'],
                    ['sexuality / intimacy','adl_sexuality_yn'],
                  ];
                @endphp

                <div class="table-responsive">
                  <table class="table table-sm table-bordered">
                    <thead class="table-light">
                      <tr>
                        <th>Activity</th>
                        <th style="width:70px;" class="text-center">Yes</th>
                        <th style="width:70px;" class="text-center">No</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($adlRows as [$label,$field])
                        @php $val = old($field, $assessment->$field ?? ''); @endphp
                        <tr>
                          <td>{{ $label }}</td>
                          <td class="text-center"><input type="radio" name="{{ $field }}" value="Yes" {{ $val==='Yes'?'checked':'' }}></td>
                          <td class="text-center"><input type="radio" name="{{ $field }}" value="No"  {{ $val==='No'?'checked':'' }}></td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                <label class="form-label">Comments</label>
                <textarea name="adl_comments" class="form-control" rows="2">{{ old('adl_comments', $assessment->adl_comments ?? '') }}</textarea>
              </div>
            </div>

            {{-- 5. Quality of life --}}
            <div class="col-md-12">
              <div class="sec-title">5. Effects of pain on quality of life</div>
              <div class="sec-box">
                <textarea name="quality_of_life_text" class="form-control" rows="2"
                  placeholder="What would you like to do now that you can't do because of the pain?">{{ old('quality_of_life_text', $assessment->quality_of_life_text ?? '') }}</textarea>
              </div>
            </div>

            {{-- 6. Symptoms --}}
            <div class="col-md-12">
              <div class="sec-title">6. Symptoms</div>
              <div class="sec-box chk-grid">
                @php
                  $sym = [
                    'sym_constipation'=>'constipation','sym_nausea'=>'nausea','sym_vomiting'=>'vomiting','sym_fatigue'=>'fatigue',
                    'sym_insomnia'=>'insomnia','sym_depression'=>'depression','sym_drowsy'=>'drowsy','sym_sore_mouth'=>'sore mouth',
                    'sym_weakness'=>'weakness','sym_short_breath'=>'short of breath',
                  ];
                @endphp
                @foreach($sym as $k=>$label)
                  <label class="form-check chk-item">
                    <input class="form-check-input" type="checkbox" name="{{ $k }}"
                      {{ old($k, $assessment->$k ?? false) ? 'checked' : '' }}>
                    <span class="form-check-label">{{ $label }}</span>
                  </label>
                @endforeach

                <div class="w-100"></div>
                <input type="text" name="sym_other" class="form-control" placeholder="Other..."
                  value="{{ old('sym_other', $assessment->sym_other ?? '') }}">
              </div>
            </div>

            {{-- 7. Behaviors --}}
            <div class="col-md-12">
              <div class="sec-title">7. Behaviors</div>
              <div class="sec-box chk-grid">
                @php
                  $beh = [
                    'beh_calling_out'=>'calling out','beh_restlessness'=>'restlessness','beh_disorientation'=>'disorientation','beh_not_eating'=>'not eating','beh_pacing'=>'pacing',
                    'beh_not_sleeping'=>'not sleeping','beh_withdrawn'=>'withdrawn','beh_groaning'=>'groaning/moaning','beh_rocking'=>'rocking','beh_new_immobility'=>'new immobility',
                    'beh_tense'=>'tense','beh_distressed'=>'distressed','beh_distracted'=>'distracted','beh_crying'=>'crying','beh_inexpressive'=>'inexpressive',
                    'beh_fists_clenched'=>'fists clenched','beh_striking_out'=>'striking out','beh_knees_pulled_up'=>'knees pulled up','beh_frowning'=>'frowning',
                    'beh_facial_grimacing'=>'facial grimacing','beh_sad'=>'sad','beh_frighten'=>'frighten','beh_resistant_movement'=>'resistant to movement',
                    'beh_pulling_pushing_away'=>'pulling/pushing away',
                  ];
                @endphp
                @foreach($beh as $k=>$label)
                  <label class="form-check chk-item">
                    <input class="form-check-input" type="checkbox" name="{{ $k }}"
                      {{ old($k, $assessment->$k ?? false) ? 'checked' : '' }}>
                    <span class="form-check-label">{{ $label }}</span>
                  </label>
                @endforeach

                <div class="w-100"></div>
                <input type="text" name="beh_other" class="form-control" placeholder="Other..."
                  value="{{ old('beh_other', $assessment->beh_other ?? '') }}">
              </div>
            </div>

            {{-- 8-11 + footer --}}
            <div class="col-md-12">
              <div class="sec-title">8. Past pain management</div>
              <div class="sec-box">
                <label class="form-label">Past pain experienced</label>
                <textarea name="past_pain_experienced" class="form-control" rows="2">{{ old('past_pain_experienced', $assessment->past_pain_experienced ?? '') }}</textarea>

                <label class="form-label mt-2">Past pain management</label>
                <textarea name="past_pain_management" class="form-control" rows="2">{{ old('past_pain_management', $assessment->past_pain_management ?? '') }}</textarea>
              </div>
            </div>

            <div class="col-md-12">
              <div class="sec-title">9. Support system</div>
              <div class="sec-box">
                <textarea name="support_system" class="form-control" rows="2">{{ old('support_system', $assessment->support_system ?? '') }}</textarea>
              </div>
            </div>

            <div class="col-md-12">
              <div class="sec-title">10. Other concerns related to pain</div>
              <div class="sec-box">
                <textarea name="other_concerns" class="form-control" rows="2">{{ old('other_concerns', $assessment->other_concerns ?? '') }}</textarea>
              </div>
            </div>

            <div class="col-md-12">
              <div class="sec-title">11. Nursing pain diagnosis</div>
              <div class="sec-box chk-grid">
                @php
                  $dx = [
                    'dx_visceral'=>'visceral',
                    'dx_somatic'=>'somatic (muscle or bone)',
                    'dx_raised_intracranial'=>'raised intracranial pressure',
                    'dx_naturopathic'=>'naturopathic',
                    'dx_mixed'=>'mixed',
                    'dx_unknown'=>'unknown',
                  ];
                @endphp
                @foreach($dx as $k=>$label)
                  <label class="form-check chk-item">
                    <input class="form-check-input" type="checkbox" name="{{ $k }}"
                      {{ old($k, $assessment->$k ?? false) ? 'checked' : '' }}>
                    <span class="form-check-label">{{ $label }}</span>
                  </label>
                @endforeach
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label">Date Care Plan Updated</label>
              <input type="date" name="care_plan_updated_date" class="form-control"
                     value="{{ old('care_plan_updated_date', $assessment->care_plan_updated_date ?? '') }}">
            </div>

            <div class="col-md-4">
              <label class="form-label">P.T. Evaluation</label>
              <div class="sec-box">
                <label class="form-check">
                  <input class="form-check-input" type="checkbox" name="pt_evaluation_yes"
                    {{ old('pt_evaluation_yes', $assessment->pt_evaluation_yes ?? false) ? 'checked' : '' }}>
                  <span class="form-check-label">Yes</span>
                </label>
                <div class="tiny">(Unchecked = No)</div>
              </div>
            </div>

            <div class="col-md-12">
              <label class="form-label">Plan</label>
              <textarea name="plan_of_care" class="form-control" rows="2">{{ old('plan_of_care', $assessment->plan_of_care ?? '') }}</textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label">Signature</label>
              <input type="text" name="signature" class="form-control"
                     value="{{ old('signature', $assessment->signature ?? '') }}">
            </div>
          </div>

          <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">{{ $mode==='edit' ? 'Update' : 'Save' }}</button>
            <a class="btn btn-light"
               href="{{ route('pain.index', ['type'=>$type, 'participant_id'=>($pid ?? '')]) }}">Cancel</a>
          </div>

        </form>
      </div>
    </div>

  </div>
</div>
@endsection
