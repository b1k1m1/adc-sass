@extends('admin.admin_dashboard')
@section('title', 'Nursing Assessment')

@section('admin')
<div class="page-content">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-3">Nursing Assessment</h4>

        <div class="d-flex gap-2 mb-3">
            <a href="{{ route('nursing-assessment.index', ['type'=>'A']) }}"
            class="btn {{ $type==='A'?'btn-primary':'btn-outline-primary' }}">Morning</a>

            <a href="{{ route('nursing-assessment.index', ['type'=>'N']) }}"
            class="btn {{ $type==='N'?'btn-secondary':'btn-outline-secondary' }}">Noon</a>

            <a href="{{ route('social.dashboard') }}" class="btn btn-primary">{{ t('common.back','Back') }}</a>

            <a href="{{ route('nursing-assessment.report', ['type'=>$type]) }}"
            class="btn btn-outline-dark">Report</a>
        </div>
    </div>
    <form method="get" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="type" value="{{ $type }}">

        <div class="col-md-5">
            <label class="form-label">Member</label>
            <select name="participant_id" class="form-select" onchange="this.form.submit()">
                <option value="">-- Select Member --</option>
                @foreach($members as $m)
                    <option value="{{ $m->ParticipantId }}"
                        {{ $selectedParticipantId==$m->ParticipantId?'selected':'' }}>
                        {{ $m->LastName }}, {{ $m->FirstName }} ({{ $m->ParticipantId }})
                    </option>
                @endforeach
            </select>
        </div>

        @if($selectedParticipantId)
        <div class="col-md-3">
            <a href="{{ route('nursing-assessment.create', ['participant_id'=>$selectedParticipantId,'type'=>$type]) }}"
            class="btn btn-success w-100">+ Add Assessment</a>
        </div>
        @endif
    </form>

    @if($assessments->count())
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Shift</th>
                        <th width="220">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($assessments as $a)
                    <tr>
                        <td>{{ $a->assessment_date ? \Carbon\Carbon::parse($a->assessment_date)->format('m/d/Y') : '' }}</td>
                        <td>{{ $a->assessment_type }}</td>
                        <td>{{ $a->shift_type }}</td>
                        <td class="d-flex gap-1">
                            <a href="{{ route('nursing-assessment.show', ['id'=>$a->id,'type'=>$type]) }}" class="btn btn-info">View</a>
                            <a href="{{ route('nursing-assessment.edit', ['id'=>$a->id,'type'=>$type]) }}" class="btn btn-warning">{{ t("common.edit", "Edit") }}</a>
                            <a href="{{ route('nursing-assessment.pdf', $a->id) }}" class="btn btn-secondary">PDF</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

  </div>
</div>
@endsection

