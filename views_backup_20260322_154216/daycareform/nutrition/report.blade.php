@extends('admin.admin_dashboard')
@section('title', 'Pain Assessment Report')

@section('admin')
<div class="page-content">
  <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Nutrition Quarterly Assessment - Report</h4>

            <div class="d-flex gap-2">
                <a href="{{ route('nutrition.report', ['type' => 'A', 'participant_id' => $selectedParticipantId]) }}"
                class="btn {{ $type === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Morning Shift
                </a>

                <a href="{{ route('nutrition.report', ['type' => 'N', 'participant_id' => $selectedParticipantId]) }}"
                class="btn {{ $type === 'N' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    Noon Shift
                </a>
            </div>
        </div>

        @if(session('message'))
            <div class="alert alert-success alert-sm">{{ session('message') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-sm">{{ session('error') }}</div>
        @endif

        {{-- Filter --}}
        <div class="card mb-3">
            <div class="card-body">
                <form method="get" action="{{ route('nutrition.report') }}">
                    <input type="hidden" name="type" value="{{ $type }}">

                    <div class="row g-2 align-items-end">
                        <div class="col-md-7">
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

                        <div class="col-md-5 text-end">
                            @if(!empty($selectedParticipantId))
                                <a href="{{ route('nutrition.index', ['type' => $type, 'participant_id' => $selectedParticipantId]) }}"
                                class="btn btn-secondary">
                                    Back to Index
                                </a>
                            @else
                                <button type="button" class="btn btn-secondary" disabled>Back to Index</button>
                            @endif
                        </div>
                    </div>

                </form>
            </div>
        </div>

        @if(empty($selectedParticipantId))
            <div class="alert alert-info">Please select a member.</div>
        @else

            <div class="card">
                <div class="card-header">
                    <strong>Assessments for Selected Member</strong>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:120px;">Date</th>
                                    <th style="width:160px;">MR No</th>
                                    <th style="width:120px;">Weight</th>
                                    <th>Diet Order</th>
                                    <th style="width:160px;">Signed</th>
                                    <th style="width:260px;" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assessments as $a)
                                    <tr>
                                        <td>
                                            {{ !empty($a->assessment_date) ? \Carbon\Carbon::parse($a->assessment_date)->format('m/d/Y') : '' }}
                                        </td>
                                        <td>{{ $a->medical_record_no ?? '' }}</td>
                                        <td>{{ $a->weight_lbs ?? '' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($a->current_diet_order ?? '', 70) }}</td>
                                        <td>
                                            {{ $a->dietitian_signature ?? '' }}
                                            @if(!empty($a->signed_date))
                                                <div class="text-muted small">{{ \Carbon\Carbon::parse($a->signed_date)->format('m/d/Y') }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a class="btn btn-info"
                                            href="{{ route('nutrition.show', ['id' => $a->id, 'type' => $type]) }}">
                                                View / Print
                                            </a>

                                            <a class="btn btn-danger"
                                            href="{{ route('nutrition.pdf', $a->id) }}">
                                                PDF
                                            </a>

                                            <a class="btn btn-primary"
                                            href="{{ route('nutrition.edit', ['id' => $a->id, 'type' => $type]) }}">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center p-3">No assessments found.</td>
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
