@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
  <div class="container-fluid">

    <form method="post"
      action="{{ $mode==='create'
        ? route('appt.store')
        : route('appt.update', [$row->participantid, $row->appdate_iso]) }}"
    >
      @csrf
      @if($mode==='edit') @method('PUT') @endif

      @if($mode==='edit')
        <input type="hidden" name="original_participantid" value="{{ $row->participantid }}">
        <input type="hidden" name="original_appdate" value="{{ $row->appdate_iso }}">
      @endif

      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Appointment — {{ ucfirst($mode) }}</h5>

          <div class="no-print">
            @php
              $mapAddress = ($physicianFullAddress ?? null)
                ?: collect([$row->appadd1 ?? null, $row->appadd2 ?? null, $row->appcity ?? null, $row->appzip ?? null])
                   ->filter(fn($v)=>!empty($v))
                   ->implode(', ');
            @endphp

            @if($mode==='view')
              @if(!empty(trim($mapAddress)))
                <a class="btn btn-outline-secondary btn-sm"
                   target="_blank" rel="noopener nofollow"
                   href="https://www.google.com/maps/search/?api=1&query={{ urlencode($mapAddress) }}">
                   <i class="bx bx-map"></i> Open in Google Maps
                </a>
              @endif
              <button type="button" class="btn btn-primary" onclick="window.print()">{{ t('common.print', 'Print') }}</button>
            @else
              <button class="btn btn-success">{{ t('common.save', 'Save') }}</button>
            @endif

            <a class="btn btn-primary" href="{{ route('appt.index') }}">{{ t('common.back','Back') }}</a>
          </div>
        </div>

        <div class="card-body row g-3">
          <div class="col-md-6">
            <label class="form-label">Name of Participant</label>
            <select name="participantid" class="form-select form-select-sm" {{ $mode==='view'?'disabled':'' }}>
              <option value="">-- select --</option>
              @foreach($members as $m)
                <option value="{{ $m->ParticipantId }}" @selected(old('participantid',$row->participantid)==$m->ParticipantId)>
                  {{ $m->ParticipantId }} | {{ $m->LastName }}, {{ $m->FirstName }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Dr's Name</label>
            <select name="appwith" id="appwith" class="form-select form-select-sm" {{ $mode==='view'?'disabled':'' }}>
              <option value="">-- select --</option>
              @foreach($docs as $d)
                <option
                  value="{{ $d->doctor_id }}"
                  @selected(old('appwith', $row->appwith) == $d->doctor_id)
                  data-add1="{{ $d->cadd1 }}"
                  data-add2="{{ $d->cadd2 }}"
                  data-city="{{ $d->ccity }}"
                  data-zip="{{ $d->czip }}"
                  data-tel1="{{ $d->cphone1 }}"
                  data-tel2="{{ $d->cphone2 }}"
                  data-fax="{{ $d->cfax }}"
                  data-cell="{{ $d->ccell }}"
                >
                  {{ $d->lastname }}, {{ $d->firstname }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Date & Time of Appointment</label>
            <input name="appdate" id="appdate"
              value="{{ old('appdate', $row->appdate_display ?? $row->appdate) }}"
              class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
          </div>

          <div class="col-md-8">
            <label class="form-label">Reason for Appointment</label>
            <input name="reason" value="{{ old('reason',$row->reason) }}"
              class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
          </div>

          <div class="col-12"><hr class="my-2"></div>

          <div class="col-md-6">
            <label class="form-label">Address</label>
            <input name="appadd1" value="{{ old('appadd1',$row->appadd1) }}"
              class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
          </div>

          <div class="col-md-6">
            <label class="form-label">Address 2</label>
            <input name="appadd2" value="{{ old('appadd2',$row->appadd2) }}"
              class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
          </div>

          <div class="col-md-4">
            <label class="form-label">City</label>
            <input name="appcity" value="{{ old('appcity',$row->appcity) }}"
              class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
          </div>

          <div class="col-md-2">
            <label class="form-label">Zip Code</label>
            <input name="appzip" value="{{ old('appzip',$row->appzip) }}"
              class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
          </div>

          <div class="col-md-4">
            <label class="form-label">Phone #1</label>
            <input name="apptel1" value="{{ old('apptel1',$row->apptel1) }}"
              class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
          </div>

          <div class="col-md-4">
            <label class="form-label">Phone #2</label>
            <input name="apptel2" value="{{ old('apptel2',$row->apptel2) }}"
              class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
          </div>

          <div class="col-md-4">
            <label class="form-label">Fax #</label>
            <input name="appfax" value="{{ old('appfax',$row->appfax) }}"
              class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
          </div>

          <div class="col-md-4">
            <label class="form-label">Cell #</label>
            <input name="appcell" value="{{ old('appcell',$row->appcell) }}"
              class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
          </div>

          <div class="col-md-4">
            <label class="form-label">Emergency #</label>
            <input name="appemer" value="{{ old('appemer',$row->appemer) }}"
              class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
          </div>

          <div class="col-12">
            <label class="form-label">{{ t('common.remarks', 'Remarks') }}</label>
            <input name="remarks1" value="{{ old('remarks1',$row->remarks1) }}"
              class="form-control form-control-sm mb-1" {{ $mode==='view'?'readonly':'' }}>
            <input name="remarks2" value="{{ old('remarks2',$row->remarks2) }}"
              class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
          </div>

          <div class="col-md-2">
            <label class="form-label">Status</label>
            <x-status-select :value="old('trans_flag',$row->trans_flag)" :disabled="$mode==='view'" />
          </div>
        </div>
      </div>
    </form>

    @if($mode==='view')
      <div class="text-center mb-3">
        <h4 class="fw-bold mb-0">{{ $company->comp_nm ?? '' }}</h4>
        <div class="fw-semibold mt-1">Appointment Slip</div>
        <hr class="my-2">
      </div>

      <div class="card mt-3">
        <div class="card-body">
          <h5 class="mb-3">Appointment Sheet</h5>

          @php
            $allergies = collect([
                $member->AdmittingDiagnosis2 ?? null,
                $member->PrimaryDiagnosis2 ?? null,
                $member->SecondaryDiag2 ?? null,
            ])->filter(fn($v) => filled(trim((string) $v)))->values();

            $specialNeeds = collect([
                $member->SpecialNeeds1 ?? null,
                $member->SpecialNeed2 ?? null,
            ])->filter(fn($v) => filled(trim((string) $v)))->values();

            $dietaryRestrictions = collect([
                $member->DiateryRestriction1 ?? null,
                $member->DiateryRestriction2 ?? null,
                $member->DiateryRestriction3 ?? null,
            ])->filter(fn($v) => filled(trim((string) $v)))->values();
            @endphp

          <div class="row g-3">
            <div class="col-md-6">
              <strong>Participant:</strong>
              {{ ($member->ParticipantId ?? $row->participantid) }}
              @if(!empty($member))
                | {{ $member->LastName }}, {{ $member->FirstName }}
              @endif
            </div>

            <div class="col-md-6">
              <strong>Doctor:</strong>
              @if(!empty($physician))
                Dr. {{ $physician->lastname }}, {{ $physician->firstname }}
              @else
                {{ $row->appwith }}
              @endif
            </div>

            <div class="col-md-6">
              <strong>Date & Time:</strong>
              {{ \Carbon\Carbon::parse($row->appdate)->format('m/d/Y h:i A') }}
            </div>

            <div class="col-md-6">
              <strong>Clinic Phone:</strong> {{ $physician->cphone1 ?? ($row->apptel1 ?? '') }}
            </div>

            <div class="col-12">
              <strong>Clinic Address:</strong> {{ $mapAddress }}
            </div>

            @if(!empty($row->reason))
              <div class="col-12">
                <strong>Reason:</strong> {{ $row->reason }}
              </div>
            @endif

            @if(!empty($row->remarks1) || !empty($row->remarks2))
              <div class="col-12">
                <strong>Remarks:</strong>
                {{ trim(($row->remarks1 ?? '').' '.($row->remarks2 ?? '')) }}
              </div>
            @endif

           @if($allergies->count() > 0 || $specialNeeds->count() > 0 || $dietaryRestrictions->count() > 0)
            <div class="col-12">
                <strong>Health / Care Summary:</strong>
                <div class="mt-1 appointment-summary-box">
                <div class="row g-3">
                    <div class="col-md-4">
                    <div class="summary-label">Allergies</div>
                    <div class="summary-value">
                        {{ $allergies->count() ? $allergies->implode(', ') : 'N/A' }}
                    </div>
                    </div>

                    <div class="col-md-4">
                    <div class="summary-label">Special Needs</div>
                    <div class="summary-value">
                        {{ $specialNeeds->count() ? $specialNeeds->implode(', ') : 'N/A' }}
                    </div>
                    </div>

                    <div class="col-md-4">
                    <div class="summary-label">Dietary Restrictions</div>
                    <div class="summary-value">
                        {{ $dietaryRestrictions->count() ? $dietaryRestrictions->implode(', ') : 'N/A' }}
                    </div>
                    </div>
                </div>
                </div>
            </div>
            @endif

            @if(!empty($medications) && $medications->count() > 0)
              <div class="col-12">
                <strong>Medication Summary:</strong>
                <div class="table-responsive mt-1">
                  <table class="table table-sm table-bordered align-middle mb-0 appointment-meds-table">
                    <thead>
                      <tr>
                        <th>Medication</th>
                        <th>Dosage</th>
                        <th>Frequency</th>
                        <th>Comments</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($medications as $med)
                        @php $medStatus = strtolower(trim($med->status ?? '')); @endphp
                        <tr>
                          <td>{{ $med->medicine_nm }}</td>
                          <td>{{ $med->dosage_nm }}</td>
                          <td>{{ $med->dosage_freq }}</td>
                          <td>{{ $med->comments }}</td>
                          <td>
                            @if($medStatus === 'active')
                              <span class="badge bg-success-subtle text-success">Active</span>
                            @elseif($medStatus === 'stopped' || $medStatus === 'stop')
                              <span class="badge bg-danger-subtle text-danger">Stopped</span>
                            @else
                              <span class="badge bg-secondary-subtle text-secondary">{{ $med->status }}</span>
                            @endif
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            @endif

            @if(!empty($mapAddress))
              <div class="col-12">
                <small>
                  Map link:
                  https://www.google.com/maps/search/?api=1&query={{ urlencode($mapAddress) }}
                </small>
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Physician Location</h5>
          <div class="no-print">
            @if(!empty($mapAddress))
              <a class="btn btn-outline-secondary btn-sm"
                 target="_blank" rel="noopener"
                 href="https://www.google.com/maps/search/?api=1&query={{ urlencode($mapAddress) }}">
                Open in Google Maps
              </a>
            @endif
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">{{ t('common.print', 'Print') }}</button>
          </div>
        </div>
        <div class="card-body">
          @if(!empty($mapAddress))
            <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:.75rem;">
              <iframe
                title="Physician Location"
                src="https://www.google.com/maps?q={{ urlencode($mapAddress) }}&output=embed"
                style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen>
              </iframe>
            </div>
            <small class="text-muted d-block mt-2">{{ $mapAddress }}</small>
          @else
            <div class="text-muted">No physician address on file.</div>
          @endif
        </div>
      </div>

      <div class="card mt-3 no-print">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Send Email to Driver</h5>
          <small class="text-muted">Uses your Gmail SMTP setup</small>
        </div>

        <div class="card-body">
          <form method="post"
                action="{{ route('appt.emailDriver', [$row->participantid, $row->appdate_iso]) }}"
                class="row g-2 align-items-end no-print">
            @csrf
            <div class="col-sm-4">
              <label class="form-label">Driver Email</label>
              <input type="email"
                     name="driver_email"
                     class="form-control form-control-sm"
                     placeholder="driver@example.com"
                     required>
            </div>
            <div class="col-auto">
              <button class="btn btn-sm btn-primary">Send Email</button>
            </div>
            <div class="col-12 mt-1">
              <small class="text-muted">Includes member, time, doctor, address, and Google Maps link.</small>
            </div>
          </form>

          <hr class="my-3">

          <form method="post"
                action="{{ route('appt.smsDriver', [$row->participantid, $row->appdate_iso]) }}"
                class="row g-2 align-items-end no-print">
            @csrf
            <div class="col-sm-4">
              <label class="form-label">Driver Phone</label>
              <input type="tel"
                     name="driver_phone"
                     class="form-control form-control-sm"
                     placeholder="e.g. 9089061085"
                     required>
            </div>

            <div class="col-sm-4 d-flex align-items-center" style="margin-top: 8px;">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="include_map" id="include_map" value="1">
                <label class="form-check-label" for="include_map">
                  Include Google Map link
                </label>
                <br>
                <small class="text-muted">(May use 2 SMS segments)</small>
              </div>
            </div>

            <div class="col-auto">
              <button class="btn btn-sm btn-success mt-4">
                Send SMS
              </button>
            </div>

            <div class="col-12">
              <small class="text-muted">
                SMS uses Infobip (U.S. registered sender).
              </small>
            </div>
          </form>
        </div>
      </div>
    @endif

    @if($mode!=='view')
      <script>
        document.addEventListener('DOMContentLoaded', ()=>{ bindDateTimePicker('#appdate'); });
      </script>

      <script>
        document.addEventListener('DOMContentLoaded', function () {
          const sel = document.getElementById('appwith');
          if (!sel) return;

          sel.addEventListener('change', function () {
            const opt = sel.options[sel.selectedIndex];
            if (!opt || !opt.dataset) return;

            setVal('appadd1', opt.dataset.add1);
            setVal('appadd2', opt.dataset.add2);
            setVal('appcity', opt.dataset.city);
            setVal('appzip',  opt.dataset.zip);
            setVal('apptel1', opt.dataset.tel1);
            setVal('apptel2', opt.dataset.tel2);
            setVal('appfax',  opt.dataset.fax);
            setVal('appcell', opt.dataset.cell);

            function setVal(name, val){
              const el = document.querySelector(`[name="${name}"]`);
              if (el) el.value = val ?? '';
            }
          });
        });
      </script>
    @endif

    <style>
      @media print {
        .no-print,
        .navbar, .sidebar, .footer,
        .btn:not(.force-print) {
          display: none !important;
        }
        .container, .container-fluid {
          width: 100% !important;
          max-width: 100% !important;
        }
        .card { box-shadow: none !important; border: 1px solid #000 !important; }
      }

      .appointment-summary-box {
        padding: 8px 10px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background: #fafafa;
        font-size: 0.92rem;
        line-height: 1.45;
      }

      .appointment-meds-table th,
      .appointment-meds-table td {
        font-size: 0.88rem;
        vertical-align: top;
      }

      .summary-label {
            font-weight: 700;
            border-bottom: 1px solid #ced4da;
            padding-bottom: 3px;
            margin-bottom: 6px;
            font-size: 0.92rem;
        }

        .summary-value {
            font-size: 0.92rem;
            line-height: 1.45;
            min-height: 22px;
            word-break: break-word;
        }
    </style>

@endsection
