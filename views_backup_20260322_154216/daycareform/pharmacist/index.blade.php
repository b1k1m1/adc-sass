@extends('admin.admin_dashboard')
@section('title', 'Pharmacist Review')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Pharmacist Review</h4>

            <div class="d-flex gap-2">

                <a href="{{ route('pharmacist_review.index', ['type' => 'A', 'participant_id' => $selectedParticipantId]) }}"
                    class="btn {{ $type === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Morning
                </a>

                <a href="{{ route('pharmacist_review.index', ['type' => 'N', 'participant_id' => $selectedParticipantId]) }}"
                    class="btn {{ $type === 'N' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                        Noon
                </a>

                <a href="{{ route('pharmacist_review.create', ['type' => $type, 'participant_id' => $selectedParticipantId]) }}"
                    class="btn btn-success">
                        + New Review
                </a>

                <a href="{{ route('nutrition.dashboard') }}" class="btn btn-primary">{{ t('common.back','Back') }}</a>
            </div>
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

        {{-- Filter Bar --}}
        <form method="get" action="{{ route('pharmacist_review.index') }}" class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label mb-1">Select Member</label>
                        <select name="participant_id" class="form-select">
                            <option value="">-- All Members --</option>
                            @foreach($members as $m)
                                <option value="{{ $m->ParticipantId }}"
                                    {{ (string)$selectedParticipantId === (string)$m->ParticipantId ? 'selected' : '' }}>
                                    {{ $m->ParticipantId }} - {{ $m->LastName }}, {{ $m->FirstName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <button type="submit" class="btn btn-primary w-100">Load</button>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('pharmacist_review.index', ['type' => $type]) }}"
                        class="btn btn-outline-secondary w-100">
                            Reset
                        </a>
                    </div>

                    <div class="col-md-4 text-end small text-muted">
                        Showing {{ $reviews->total() }} record(s)
                    </div>
                </div>
            </div>
        </form>

        {{-- List --}}
        <div class="card">
            <div class="card-header">
                <strong>Reviews</strong>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:90px;">ID</th>
                                <th style="width:140px;">Review Date</th>
                                <th style="width:120px;">Period</th>
                                <th>Member</th>
                                <th style="width:120px;">Status</th>
                                <th style="width:140px;" class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $r)
                                @php
                                    $memberLabel = (string)$r->participant_id;
                                    foreach($members as $mm){
                                        if((string)$mm->ParticipantId === (string)$r->participant_id){
                                            $memberLabel = $mm->ParticipantId.' - '.$mm->LastName.', '.$mm->FirstName;
                                            break;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $r->id }}</td>
                                    <td>{{ \Carbon\Carbon::parse($r->review_date)->format('m/d/Y') }}</td>
                                    <td>{{ $r->period_key }}</td>
                                    <td>{{ $memberLabel }}</td>
                                    <td>{{ $r->status ?? 'draft' }}</td>
                                    <td class="text-end">

                                        <a href="{{ route('pharmacist_review.show', [
                                                'id' => $r->id,
                                                'type' => $type
                                            ]) }}"
                                        class="btn btn-info">
                                            View
                                        </a>

                                        <a href="{{ route('pharmacist_review.edit', [
                                                'id' => $r->id,
                                                'type' => $type
                                            ]) }}"
                                        class="btn btn-primary">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No reviews found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($reviews->hasPages())
                <div class="card-footer">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

@endsection
