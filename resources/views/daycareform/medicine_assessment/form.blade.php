@extends('admin.admin_dashboard')
@section('title', 'Medication Review Report')
@section('admin')

<div class="page-content">
  <div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">{{ $assessment->id ? 'Edit Medication Assessment' : 'Add Medication Assessment' }}</h4>

  <a href="{{ route('medicine_assessment.index', ['type'=>$type, 'participant_id'=>$assessment->participant_id]) }}"
     class="btn btn-secondary">
    &laquo; Back
  </a>
</div>

@if(session('message'))
  <div class="alert alert-success alert-sm">{{ session('message') }}</div>
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
  $isEdit = !empty($assessment->id);
  $action = $isEdit
    ? route('medicine_assessment.update', ['id'=>$assessment->id, 'type'=>$type])
    : route('medicine_assessment.store', ['type'=>$type]);
@endphp

<form method="post" action="{{ $action }}">
  @csrf

  <input type="hidden" name="type" value="{{ $type }}">

  {{-- HEADER --}}
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Header</strong>
      <span class="small text-muted">Shift: {{ $type === 'A' ? 'Morning (A)' : 'Noon (N)' }}</span>
    </div>

    <div class="card-body">
      <div class="row g-2">

        <div class="col-md-4">
          <label class="form-label mb-1">Member</label>
          <select name="participant_id" id="participant_id" class="form-select form-select-sm" required>
            <option value="">-- Select Member --</option>
            @foreach($members as $m)
              <option value="{{ $m->ParticipantId }}"
                {{ (string)old('participant_id', $assessment->participant_id) === (string)$m->ParticipantId ? 'selected' : '' }}>
                {{ $m->ParticipantId }} - {{ $m->LastName }}, {{ $m->FirstName }}
              </option>
            @endforeach
          </select>
          <div class="form-text">Tip: select member first (filtered by shift).</div>
        </div>

        <div class="col-md-2">
          <label class="form-label mb-1">Medical Record No</label>
          <input type="text" name="medical_record_no"
                 value="{{ old('medical_record_no', $assessment->medical_record_no) }}"
                 class="form-control form-control-sm" maxlength="50">
        </div>

        <div class="col-md-2">
          <label class="form-label mb-1">Assessment Date</label>
          <input type="date" name="assessment_date"
                 value="{{ old('assessment_date', $assessment->assessment_date) }}"
                 class="form-control form-control-sm">
        </div>

        <div class="col-md-4">
          <label class="form-label mb-1">Nurse Name</label>
          <input type="text" name="nurse_name"
                 value="{{ old('nurse_name', $assessment->nurse_name) }}"
                 class="form-control form-control-sm" maxlength="80">
        </div>

      </div>

      {{-- optional snapshot label --}}
      <div class="mt-3 p-2 border rounded bg-light">
        <div class="row g-2 small">
          <div class="col-md-4">
            <strong>Member:</strong> <span id="member_label"></span>
          </div>
          <div class="col-md-8 text-muted">
            (We can bind DOB/Admission/Vitals later from `members` same as other forms.)
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- BODY FIELDS --}}
  <div class="card mb-3">
    <div class="card-header"><strong>Assessment Details</strong></div>
    <div class="card-body">

        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label mb-1">Use of Splints</label>
                <select name="use_splints" class="form-select form-select-sm">
                <option value="">—</option>
                <option value="YES" {{ old('use_splints',$assessment->use_splints) === 'YES' ? 'selected' : '' }}>YES</option>
                <option value="NO"  {{ old('use_splints',$assessment->use_splints) === 'NO'  ? 'selected' : '' }}>NO</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label mb-1">Braces</label>
                <select name="use_braces" class="form-select form-select-sm">
                <option value="">—</option>
                <option value="YES" {{ old('use_braces',$assessment->use_braces) === 'YES' ? 'selected' : '' }}>YES</option>
                <option value="NO"  {{ old('use_braces',$assessment->use_braces) === 'NO'  ? 'selected' : '' }}>NO</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label mb-1">Home Glucose Monitoring</label>
                <select name="home_glucose" class="form-select form-select-sm" id="home_glucose">
                <option value="">—</option>
                <option value="YES" {{ old('home_glucose',$assessment->home_glucose) === 'YES' ? 'selected' : '' }}>YES</option>
                <option value="NO"  {{ old('home_glucose',$assessment->home_glucose) === 'NO'  ? 'selected' : '' }}>NO</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label mb-1">Frequency</label>
                <input type="text" name="home_glucose_freq"
                    value="{{ old('home_glucose_freq',$assessment->home_glucose_freq) }}"
                    class="form-control form-control-sm"
                    maxlength="50"
                    placeholder="e.g. 2 times a day">
            </div>
        </div>


        <div class="col-md-4">
          <label class="form-label mb-1">Physical Therapy</label>
          <input type="text" name="physical_therapy"
                 value="{{ old('physical_therapy', $assessment->physical_therapy) }}"
                 class="form-control form-control-sm" maxlength="50">
        </div>
      </div>

      <div class="mb-2">
        <label class="form-label mb-1">OTC Medications (Tylenol/Advil/etc.)</label>
        <input type="text" name="otc_meds"
               value="{{ old('otc_meds', $assessment->otc_meds) }}"
               class="form-control form-control-sm" maxlength="200">
      </div>

      <div class="row g-2 mb-2">
        <div class="col-md-6">
          <label class="form-label mb-1">Reminder Method (check all that apply)</label>

          <div class="row">
            @php
              $checks = [
                'rem_take_independently' => 'Takes independently',
                'rem_caregiver_gives' => 'Caregiver gives',
                'rem_follows_label' => 'Follows label directions',
                'rem_pill_minder' => 'Uses pill minder',
                'rem_schedule_calendar' => 'Uses schedule/calendar',
              ];
            @endphp

            @foreach($checks as $f=>$lbl)
              <div class="col-md-6 form-check mb-1">
                <input class="form-check-input" type="checkbox" name="{{ $f }}" value="1"
                  {{ old($f, $assessment->$f ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">{{ $lbl }}</label>
              </div>
            @endforeach
          </div>

          <div class="mt-1">
            <input type="text" name="rem_other"
                   value="{{ old('rem_other', $assessment->rem_other) }}"
                   class="form-control form-control-sm"
                   maxlength="80" placeholder="Other (optional)">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label mb-1">Pharmacy</label>
          <div class="row g-2">
            <div class="col-md-7">
              <input type="text" name="pharmacy_provider"
                     value="{{ old('pharmacy_provider', $assessment->pharmacy_provider) }}"
                     class="form-control form-control-sm" maxlength="80" placeholder="Provider">
            </div>
            <div class="col-md-5">
              <input type="text" name="pharmacy_phone"
                     value="{{ old('pharmacy_phone', $assessment->pharmacy_phone) }}"
                     class="form-control form-control-sm" maxlength="30" placeholder="Phone">
            </div>
          </div>

          <div class="row g-2 mt-2">
            <div class="col-md-6">
              <label class="form-label mb-1">Takes meds as ordered?</label>
              <select name="takes_as_ordered" class="form-select form-select-sm">
                <option value="">-- Select --</option>
                <option value="Yes" {{ old('takes_as_ordered', $assessment->takes_as_ordered) === 'Yes' ? 'selected':'' }}>Yes</option>
                <option value="No"  {{ old('takes_as_ordered', $assessment->takes_as_ordered) === 'No' ? 'selected':'' }}>No</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label mb-1">Takes meds while at center?</label>
              <select name="takes_while_center" class="form-select form-select-sm">
                <option value="">-- Select --</option>
                <option value="Yes" {{ old('takes_while_center', $assessment->takes_while_center) === 'Yes' ? 'selected':'' }}>Yes</option>
                <option value="No"  {{ old('takes_while_center', $assessment->takes_while_center) === 'No' ? 'selected':'' }}>No</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="mb-2">
        <label class="form-label mb-1">Notes</label>
        <textarea name="notes" rows="4" class="form-control">{{ old('notes', $assessment->notes) }}</textarea>
      </div>

    </div>
  </div>

  {{-- MEDS (READ-ONLY) --}}
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Current Medication List (Nurse Maintained)</strong>

      @if(!empty(old('participant_id', $assessment->participant_id)))
        <a href="{{ route('medicine_maint.index', ['type'=>$type, 'participant_id'=>old('participant_id', $assessment->participant_id)]) }}"
           class="btn btn-outline-primary btn-sm" target="_blank">
          Open Medication Maintenance
        </a>
      @endif
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th style="width:50px;">#</th>
              <th>Medicine</th>
              <th style="width:220px;">Physician</th>
              <th style="width:130px;">Dosage</th>
              <th style="width:130px;">Frequency</th>
              <th style="width:220px;">Comments</th>
              <th style="width:110px;">Status</th>
              <th style="width:120px;">Status Date</th>
            </tr>
          </thead>
          <tbody id="nurseMedsBody">
            @forelse($nurseMeds as $i=>$m)
              <tr>
                <td class="text-center">{{ $i+1 }}</td>
                <td>{{ $m->medicine_nm }}</td>
                <td>{{ $m->physician_name ?? $m->doctor_id }}</td>
                <td>{{ $m->dosage_nm }}</td>
                <td>{{ $m->dosage_freq }}</td>
                <td>{{ $m->comments }}</td>
                <td>{{ ucfirst($m->status ?? 'active') }}</td>
                <td>{{ !empty($m->status_date) ? \Carbon\Carbon::parse($m->status_date)->format('m/d/Y') : '' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-3">
                  No medication list found for this member.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="small text-muted mt-2">
        Note: The medication list is maintained by nursing staff. Update medications using Medication Maintenance.
      </div>
    </div>
  </div>

  {{-- ACTIONS --}}
  <div class="d-flex justify-content-end gap-2">
    <a href="{{ route('medicine_assessment.index', ['type'=>$type, 'participant_id'=>old('participant_id', $assessment->participant_id)]) }}"
       class="btn btn-outline-secondary">Cancel</a>

    <button type="submit" class="btn btn-primary">Save</button>
  </div>

</form>
  </div>
</div>
@endsection

@section('scripts')

<script>
(function(){
  const memberSelect = document.getElementById('participant_id');
  const memberLabel  = document.getElementById('member_label');
  const medsBody     = document.getElementById('nurseMedsBody');

  function updateMemberLabel(){
    if(!memberSelect || !memberLabel) return;
    const txt = memberSelect.options[memberSelect.selectedIndex]?.text || '';
    memberLabel.textContent = txt;
  }

  // Optional: refresh meds when member changes (requires your nurse-meds JSON endpoint)
  function refreshNurseMeds(pid){
    if(!medsBody) return;

    if(!pid){
      medsBody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-3">Select member to view medications.</td></tr>`;
      return;
    }

    medsBody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-3">Loading...</td></tr>`;

    // You can reuse the SAME endpoint you added for pharmacist:
    // /daycareform/pharmacist-review/member/{participantId}/nurse-meds
    fetch(`{{ url('/daycareform/pharmacist-review/member') }}/${pid}/nurse-meds`)
      .then(r => r.json())
      .then(data => {
        if(!data.ok){
          medsBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-3">Error loading medications.</td></tr>`;
          return;
        }
        if(!data.meds || data.meds.length === 0){
          medsBody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-3">No medication list found for this member.</td></tr>`;
          return;
        }

        medsBody.innerHTML = data.meds.map((m, i) => `
          <tr>
            <td class="text-center">${i+1}</td>
            <td>${esc(m.medicine_nm ?? '')}</td>
            <td>${esc((m.physician_name ?? m.doctor_id) ?? '')}</td>
            <td>${esc(m.dosage_nm ?? '')}</td>
            <td>${esc(m.dosage_freq ?? '')}</td>
            <td>${esc(m.comments ?? '')}</td>
            <td>${esc(cap(m.status ?? 'active'))}</td>
            <td>${esc(fmtDate(m.status_date))}</td>
          </tr>
        `).join('');
      })
      .catch(() => medsBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-3">Error loading medications.</td></tr>`);
  }

  function cap(s){ s=(s||'').toString(); return s ? s[0].toUpperCase()+s.slice(1) : ''; }
  function esc(str){
    return (str||'').toString()
      .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
      .replaceAll('"','&quot;').replaceAll("'","&#039;");
  }

  if(memberSelect){
    memberSelect.addEventListener('change', function(){
      updateMemberLabel();
      refreshNurseMeds(this.value);
    });
    updateMemberLabel();
    refreshNurseMeds(memberSelect.value);
  }

    function fmtDate(d){
    if(!d) return '';
    // d is "YYYY-MM-DD"
    const parts = d.split('-');
    if(parts.length !== 3) return d;
    return `${parts[1]}/${parts[2]}/${parts[0]}`;
    }

})();
(function(){
  const hg = document.getElementById('home_glucose');
  const freq = document.querySelector('input[name="home_glucose_freq"]');
  if(!hg || !freq) return;

  function sync(){
    const yes = hg.value === 'YES';
    freq.disabled = !yes;
    if(!yes) freq.value = '';
  }
  hg.addEventListener('change', sync);
  sync();
})();
</script>

@endsection
