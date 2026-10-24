@extends('admin.admin_dashboard')
@section('title', 'Nursing Assessment')

@section('admin')
<div class="page-content">
  <div class="container-fluid">

<h4 class="mb-3">Nursing Assessment Report</h4>

<div class="d-flex gap-2 mb-3">
    <a href="{{ route('nursing-assessment.report', ['type'=>'A']) }}"
       class="btn {{ $type==='A'?'btn-primary':'btn-outline-primary' }}">Morning</a>

    <a href="{{ route('nursing-assessment.report', ['type'=>'N']) }}"
       class="btn {{ $type==='N'?'btn-primary':'btn-outline-primary' }}">Noon</a>

    <a href="{{ route('nursing-assessment.index', ['type'=>$type]) }}"
       class="btn btn-outline-dark ms-auto">Back to List</a>
</div>

<form method="get" class="row g-2 mb-3">
    <input type="hidden" name="type" value="{{ $type }}">

    <div class="col-md-5">
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
</form>

@if($member)
    <div class="alert alert-light border">
        <b>Member:</b> {{ $member->LastName }}, {{ $member->FirstName }}
        &nbsp;&nbsp; <b>Shift:</b> {{ $type }}
    </div>
@endif

@if($assessments->count())
<table class="table table-sm table-bordered">
    <thead class="table-light">
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th width="220">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($assessments as $a)
        <tr>
            <td>{{ $a->assessment_date ? \Carbon\Carbon::parse($a->assessment_date)->format('m/d/Y') : '' }}</td>
            <td>{{ $a->assessment_type }}</td>
            <td class="d-flex gap-1">
                <a class="btn btn-info"
                   href="{{ route('nursing-assessment.show', ['id'=>$a->id,'type'=>$type]) }}">View</a>
                <a class="btn btn-secondary"
                   href="{{ route('nursing-assessment.pdf', $a->id) }}">PDF</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

  </div>
</div>
@endsection
