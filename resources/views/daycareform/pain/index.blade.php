@extends('admin.admin_dashboard')
@section('title', 'Pain Assessment')

@section('admin')
<div class="page-content">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">Pain Assessment</h5>

      {{-- Morning/Noon --}}
      <div class="d-flex gap-2">
        <a href="{{ route('pain.index', ['type' => 'A']) }}"
           class="btn {{ $type==='A' ? 'btn-primary' : 'btn-outline-primary' }}">
          Morning
        </a>
        <a href="{{ route('pain.index', ['type' => 'N']) }}"
           class="btn {{ $type==='N' ? 'btn-secondary' : 'btn-outline-secondary' }}">
          Noon
        </a>
        <a href="{{ route('social.dashboard') }}" class="btn btn-primary">{{ t('common.back','Back') }}</a>
      </div>
    </div>

    @if(session('message'))
      <div class="alert alert-success alert-sm">{{ session('message') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger alert-sm">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
      <div class="card-body">
        <form method="get" action="{{ route('pain.index') }}">
          <input type="hidden" name="type" value="{{ $type }}">

          <div class="row g-2 align-items-end">
            <div class="col-md-6">
              <label class="form-label">Select Member</label>
              <select name="participant_id" class="form-select">
                <option value="">-- Select --</option>
                @foreach($members as $m)
                  @php
                    $pid = $m->ParticipantId ?? '';
                    $ln  = $m->LastName ?? $m->lastname ?? '';
                    $fn  = $m->FirstName ?? $m->firstname ?? '';
                  @endphp
                  <option value="{{ $pid }}" {{ ($selectedParticipantId==$pid) ? 'selected' : '' }}>
                    {{ $pid }} - {{ $ln }}, {{ $fn }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6 d-flex gap-2">
              <button class="btn btn-info">Load</button>

              <a href="{{ route('pain.create', ['type'=>$type, 'participant_id'=>$selectedParticipantId]) }}"
                 class="btn btn-primary {{ empty($selectedParticipantId) ? 'disabled' : '' }}">
                + Add New
              </a>

              <a href="{{ route('pain.report', ['type'=>$type, 'participant_id'=>$selectedParticipantId]) }}"
                 class="btn btn-outline-dark {{ empty($selectedParticipantId) ? 'disabled' : '' }}">
                Report
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>

    {{-- List --}}
    <div class="card">
      <div class="card-header">
        <strong>Assessments</strong>
      </div>

      <div class="card-body">
        @if(empty($selectedParticipantId))
          <div class="alert alert-warning mb-0">Please select a member to view assessments.</div>
        @else
          @if($assessments->isEmpty())
            <div class="alert alert-warning mb-0">No Pain Assessments found for this member.</div>
          @else
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <thead class="table-light">
                  <tr>
                    <th style="width:80px;">ID</th>
                    <th style="width:140px;">Assessment Date</th>
                    <th style="width:160px;">MR No</th>
                    <th style="width:120px;">Severity</th>
                    <th>Location</th>
                    <th style="width:260px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($assessments as $a)
                    <tr>
                      <td>{{ $a->id }}</td>
                      <td>{{ \Carbon\Carbon::parse($a->assessment_date)->format('m/d/Y') }}</td>
                      <td>{{ $a->medical_record_no }}</td>
                      <td>
                        {{ $a->pain_severity_score ?? '' }}
                        @if($a->pain_severity_label) ({{ $a->pain_severity_label }}) @endif
                      </td>
                      <td>{{ \Illuminate\Support\Str::limit($a->pain_location, 60) }}</td>
                      <td class="d-flex gap-2 flex-wrap">
                        <a class="btn btn-sm btn-outline-primary"
                           href="{{ route('pain.edit', ['id'=>$a->id, 'type'=>$type]) }}">{{ t("common.edit", "Edit") }}</a>

                        <a class="btn btn-sm btn-outline-info"
                           href="{{ route('pain.show', ['id'=>$a->id, 'type'=>$type]) }}">View</a>

                        <a class="btn btn-sm btn-outline-danger"
                           href="{{ route('pain.pdf', $a->id) }}">PDF</a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        @endif
      </div>
    </div>

  </div>
</div>
@endsection
