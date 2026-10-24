@extends('admin.admin_dashboard')
@section('title', 'Medication Review - View')
@section('admin')

        <div class="page-content">
        <div class="container-fluid">


        @php
            // Generic value (shows '—' if null/empty)
            $v = function ($value, $dash = '—') {
                $value = is_string($value) ? trim($value) : $value;
                return ($value === null || $value === '') ? $dash : $value;
            };

            // Yes/No/NULL display (tri-state)
            $yn = function ($value, $dash = '—') {
                if ($value === null || $value === '') return $dash;

                $val = is_string($value) ? strtoupper(trim($value)) : $value;

                if ($val === 1 || $val === true || $val === 'Y' || $val === 'YES') return 'Yes';
                if ($val === 0 || $val === false || $val === 'N' || $val === 'NO') return 'No';

                // If someone stored "Yes"/"No" already
                if (is_string($value)) {
                    $t = ucfirst(strtolower(trim($value)));
                    if (in_array($t, ['Yes', 'No'])) return $t;
                }

                return $dash;
            };

            // Checkbox marker: Y / N / —
            $chk = function ($value, $dash = '—') {
                if ($value === null || $value === '') return $dash;

                $val = is_string($value) ? strtoupper(trim($value)) : $value;

                if ($val === 1 || $val === true || $val === 'Y' || $val === 'YES') return 'Y';
                if ($val === 0 || $val === false || $val === 'N' || $val === 'NO') return 'N';

                return $dash;
            };

            $yesno = fn($val) => in_array($val, ['YES','NO']) ? $val : '—';

        @endphp

        <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Medication Review (View)</h4>

        <div class="d-flex gap-2">
            <a href="{{ route('medicine_assessment.index', ['type'=>$type, 'participant_id'=>$assessment->participant_id]) }}"
            class="btn btn-secondary">&laquo; Back</a>

            <a href="{{ route('medicine_assessment.pdf', ['id'=>$assessment->id, 'type'=>$type]) }}"
            class="btn btn-danger" target="_blank">PDF</a>

            <a href="{{ route('medicine_assessment.edit', ['id'=>$assessment->id, 'type'=>$type]) }}"
            class="btn btn-primary">Edit</a>
        </div>
        </div>

        {{-- HEADER --}}
        <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Header</strong>
            <span class="small text-muted">Shift: {{ $type === 'A' ? 'Morning (A)' : 'Noon (N)' }}</span>
        </div>

        <div class="card-body">
            <div class="row g-2">
            <div class="col-md-4">
                <strong>Member:</strong>
                {{ $assessment->participant_id }} - {{ $member->LastName ?? '' }}, {{ $member->FirstName ?? '' }}
            </div>

            <div class="col-md-2">
                <strong>MR#:</strong> {{ $v($assessment->medical_record_no) }}
            </div>

            <div class="col-md-2">
                <strong>Date:</strong>
                {{ !empty($assessment->assessment_date) ? \Carbon\Carbon::parse($assessment->assessment_date)->format('m/d/Y') : '—' }}
            </div>

            <div class="col-md-4">
                <strong>Nurse:</strong> {{ $v($assessment->nurse_name) }}
            </div>
            </div>
        </div>
        </div>

        {{-- DETAILS --}}
<div class="card mb-3">
  <div class="card-header"><strong>Assessment Details</strong></div>
  <div class="card-body">

    <div class="row g-2 align-items-start mb-2">
      <div class="col-md-3 col-sm-6">
        <div class="d-flex gap-2">
          <strong class="text-nowrap">Use of Splints:</strong>
          <span>{{ $yesno($assessment->use_splints) }}</span>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="d-flex gap-2">
          <strong class="text-nowrap">Braces:</strong>
          <span>{{ $yesno($assessment->use_braces) }}</span>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="d-flex gap-2">
          <strong class="text-nowrap">Home Glucose Monitoring:</strong>
          <span>{{ $yesno($assessment->home_glucose) }}</span>
        </div>
      </div>

      <div class="col-md-3 col-sm-6">
        <div class="d-flex gap-2">
          <strong class="text-nowrap">Frequency:</strong>
          <span>{{ $v($assessment->home_glucose_freq) }}</span>
        </div>
      </div>
    </div>

    <div class="row g-2 mb-2">
      <div class="col-md-4 col-sm-6">
        <div class="d-flex gap-2">
          <strong class="text-nowrap">Physical Therapy:</strong>
          <span>{{ $v($assessment->physical_therapy) }}</span>
        </div>
      </div>
    </div>

    <div class="mb-2">
      <strong>OTC Meds:</strong> {{ $v($assessment->otc_meds) }}
    </div>

    {{-- Reminder Method: show each as Y/N/— --}}
    <div class="mb-2">
      <strong>Reminder Method:</strong>
      <div class="table-responsive mt-1">
        <table class="table table-sm table-bordered mb-0">
          <tbody>
            <tr>
              <td style="width:70%;">Takes independently</td>
              <td class="text-center" style="width:30%;">{{ $chk($assessment->rem_take_independently) }}</td>
            </tr>
            <tr>
              <td>Caregiver gives</td>
              <td class="text-center">{{ $chk($assessment->rem_caregiver_gives) }}</td>
            </tr>
            <tr>
              <td>Follows label directions</td>
              <td class="text-center">{{ $chk($assessment->rem_follows_label) }}</td>
            </tr>
            <tr>
              <td>Uses pill minder</td>
              <td class="text-center">{{ $chk($assessment->rem_pill_minder) }}</td>
            </tr>
            <tr>
              <td>Uses schedule/calendar</td>
              <td class="text-center">{{ $chk($assessment->rem_schedule_calendar) }}</td>
            </tr>
            <tr>
              <td>Other</td>
              <td>{{ $v($assessment->rem_other) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="row g-2 mb-2">
      <div class="col-md-6">
        <strong>Pharmacy:</strong> {{ $v($assessment->pharmacy_provider) }}
      </div>
      <div class="col-md-6">
        <strong>Phone:</strong> {{ $v($assessment->pharmacy_phone) }}
      </div>
    </div>

    <div class="row g-2 mb-2">
      <div class="col-md-6">
        <strong>Takes as ordered?</strong> {{ $yn($assessment->takes_as_ordered) }}
      </div>
      <div class="col-md-6">
        <strong>Takes while at center?</strong> {{ $yn($assessment->takes_while_center) }}
      </div>
    </div>

    <div>
      <strong>Notes:</strong>
      <div style="white-space: pre-wrap;">{{ $v($assessment->notes) }}</div>
    </div>

  </div>
</div>


        {{-- MEDS --}}
        <div class="card mb-3">
        <div class="card-header"><strong>Current Medication List (Nurse Maintained)</strong></div>
        <div class="card-body table-responsive">
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
            <tbody>
                @forelse($nurseMeds as $i=>$m)
                <tr>
                    <td class="text-center">{{ $i+1 }}</td>
                    <td>{{ $v($m->medicine_nm) }}</td>
                    <td>{{ $v($m->physician_name ?? $m->doctor_id) }}</td>
                    <td>{{ $v($m->dosage_nm) }}</td>
                    <td>{{ $v($m->dosage_freq) }}</td>
                    <td>{{ $v($m->comments) }}</td>
                    <td>{{ $v(ucfirst($m->status ?? 'active')) }}</td>
                    <td>{{ !empty($m->status_date) ? \Carbon\Carbon::parse($m->status_date)->format('m/d/Y') : '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-3">No medication list found.</td>
                </tr>
                @endforelse
            </tbody>
            </table>
        </div>
        </div>


  </div>
</div>
@endsection

