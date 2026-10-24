@extends('admin.admin_dashboard')
@section('title', 'Dietary Initial Assessment')

@section('admin')

<div class="page-content">
  <div class="container-fluid">


    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Dietary Initial Assessment</h5>

        <div class="d-flex gap-2">
            <a href="{{ route('dietary.index', ['type'=>'A']) }}"
            class="btn {{ $type==='A' ? 'btn-primary' : 'btn-outline-primary' }}">Morning</a>
            <a href="{{ route('dietary.index', ['type'=>'N']) }}"
            class="btn {{ $type==='N' ? 'btn-secondary' : 'btn-outline-secondary' }}">Noon</a>

            <a href="{{ route('dietary.report', ['type'=>$type]) }}"
            class="btn btn-outline-dark">Report</a>

            <a href="{{ route('nutrition.dashboard') }}" class="btn btn-primary">{{ t('common.back','Back') }}</a>
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

        <form method="get" action="{{ route('dietary.index') }}">
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
            <button type="submit" class="btn btn-info">View</button>

            <a href="{{ route('dietary.create', ['type'=>$type, 'participant_id'=>$selectedParticipantId]) }}"
                class="btn btn-primary {{ empty($selectedParticipantId) ? 'disabled' : '' }}">
                Add New
            </a>
            </div>
        </div>
        </form>

    </div>
    </div>

    @if($selectedParticipantId)
    <div class="card">
        <div class="card-header">
        <strong>Assessments</strong>
        </div>
        <div class="card-body">

        @if($assessments->isEmpty())
            <div class="alert alert-warning mb-0">No assessments found.</div>
        @else
            <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                <tr>
                    <th style="width:80px;">ID</th>
                    <th style="width:140px;">Assessment Date</th>
                    <th style="width:180px;">MR No</th>
                    <th>Diet Order</th>
                    <th style="width:260px;">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($assessments as $a)
                    <tr>
                    <td>{{ $a->id }}</td>
                    <td>{{ !empty($a->assessment_date) ? \Carbon\Carbon::parse($a->assessment_date)->format('m/d/Y') : '' }}</td>
                    <td>{{ $a->medical_record_no ?? '' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($a->diet_order ?? '', 60) }}</td>
                    <td class="d-flex gap-2 flex-wrap">
                        <a class="btn btn-sm btn-outline-info"
                        href="{{ route('dietary.show', ['id'=>$a->id, 'type'=>$type]) }}">View</a>
                        <a class="btn btn-sm btn-outline-primary"
                        href="{{ route('dietary.edit', ['id'=>$a->id, 'type'=>$type]) }}">{{ t('common.edit', 'Edit') }}</a>
                        <a class="btn btn-sm btn-outline-danger"
                        href="{{ route('dietary.pdf', $a->id) }}">PDF</a>
                    </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        @endif

        </div>
    </div>
    @endif


  </div>
</div>
@endsection
