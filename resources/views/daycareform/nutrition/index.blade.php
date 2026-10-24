@extends('admin.admin_dashboard')
@section('title', 'Nutrition Assessment')

@section('admin')
<div class="page-content">
  <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Nutrition Quarterly Assessment</h4>

            <div class="d-flex gap-2">
                <a href="{{ route('nutrition.index', ['type' => 'A']) }}"
                class="btn {{ $type === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Morning
                </a>

                <a href="{{ route('nutrition.index', ['type' => 'N']) }}"
                class="btn {{ $type === 'N' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    Noon
                </a>

                 <a href="{{ route('nutrition.dashboard') }}" class="btn btn-primary">{{ t('common.back','Back') }}</a>
            </div>
        </div>

        @if(session('message'))
            <div class="alert alert-success alert-sm">{{ session('message') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-sm">{{ session('error') }}</div>
        @endif

        {{-- Filter row --}}
        <div class="card mb-3">
            <div class="card-body">
                <form method="get" action="{{ route('nutrition.index') }}">
                    <input type="hidden" name="type" value="{{ $type }}">

                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Select Member</label>
                            <select name="participant_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Select --</option>
                                @foreach($members as $m)
                                    @php
                                        $mLast = $m->LastName ?? $m->lastname ?? '';
                                        $mFirst = $m->FirstName ?? $m->firstname ?? '';
                                        $mName = trim($mLast . ', ' . $mFirst);
                                    @endphp
                                    <option value="{{ $m->ParticipantId }}"
                                        {{ (string)$selectedParticipantId === (string)$m->ParticipantId ? 'selected' : '' }}>
                                        {{ $m->ParticipantId }} - {{ $mName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 text-end">
                            @if(!empty($selectedParticipantId))
                                <a href="{{ route('nutrition.create', ['type' => $type, 'participant_id' => $selectedParticipantId]) }}"
                                class="btn btn-success">
                                    + Add New Assessment
                                </a>

                                <a href="{{ route('nutrition.report', ['type' => $type, 'participant_id' => $selectedParticipantId]) }}"
                                class="btn btn-dark">
                                    Report
                                </a>
                            @else
                                <button type="button" class="btn btn-success" disabled>+ Add New Assessment</button>
                                <button type="button" class="btn btn-dark" disabled>Report</button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Results --}}
        @if(empty($selectedParticipantId))
            <div class="alert alert-info">
                Please select a member to view Nutrition Quarterly Assessments.
            </div>
        @else

            <div class="card">
                <div class="card-header">
                    <strong>Assessments</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:120px;">Date</th>
                                    <th style="width:120px;">Weight</th>
                                    <th>Diet Order</th>
                                    <th style="width:160px;">Signed</th>
                                    <th style="width:220px;" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assessments as $a)
                                    <tr>
                                        <td>
                                            {{ !empty($a->assessment_date) ? \Carbon\Carbon::parse($a->assessment_date)->format('m/d/Y') : '' }}
                                        </td>
                                        <td>{{ $a->weight_lbs ?? '' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($a->current_diet_order ?? '', 60) }}</td>
                                        <td>
                                            {{ $a->dietitian_signature ?? '' }}
                                            @if(!empty($a->signed_date))
                                                <div class="text-muted small">{{ \Carbon\Carbon::parse($a->signed_date)->format('m/d/Y') }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a class="btn btn-info btn-sm"
                                            href="{{ route('nutrition.show', ['id' => $a->id, 'type' => $type]) }}">
                                                View
                                            </a>

                                            <a class="btn btn-primary btn-sm"
                                            href="{{ route('nutrition.edit', ['id' => $a->id, 'type' => $type]) }}">
                                                Edit
                                            </a>

                                            <a class="btn btn-danger btn-sm"
                                            href="{{ route('nutrition.pdf', $a->id) }}">
                                                PDF
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center p-3">
                                            No assessments found for this member.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        @endif
  </div>
</div>
@endsection
