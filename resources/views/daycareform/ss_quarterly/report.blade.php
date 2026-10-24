@extends('admin.admin_dashboard')
@section('title', 'Nursing')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <h4 class="mb-2">Social Service Quarterly Assessment – Report</h4>

        {{-- Morning / Noon Tabs --}}
        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link {{ ($type ?? 'A') === 'A' ? 'active' : '' }}"
                   href="{{ route('ss-quarterly.report', [
                        'type'          => 'A',
                        'participant_id'=> $selectedParticipantId,
                   ]) }}">
                    Morning Shift
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($type ?? 'A') === 'N' ? 'active' : '' }}"
                   href="{{ route('ss-quarterly.report', [
                        'type'          => 'N',
                        'participant_id'=> $selectedParticipantId,
                   ]) }}">
                    Noon Shift
                </a>
            </li>
        </ul>

        @if(session('message'))
            <div class="alert alert-success alert-sm">{{ session('message') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-sm">{{ session('error') }}</div>
        @endif

        {{-- Filter Form --}}
        <form method="GET" class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label mb-1">Select Member</label>
                        <select name="participant_id" class="form-select form-select-sm">
                            <option value="">-- Choose Member --</option>
                            @foreach($members as $m)
                                @php
                                    $pid  = $m->ParticipantId ?? $m->participant_id;
                                    $ln   = $m->LastName  ?? $m->lastname  ?? '';
                                    $fn   = $m->FirstName ?? $m->firstname ?? '';
                                @endphp
                                <option value="{{ $pid }}"
                                    {{ (string)$selectedParticipantId === (string)$pid ? 'selected' : '' }}>
                                    {{ $ln }}, {{ $fn }} ({{ str_pad($pid, 3, '0', STR_PAD_LEFT) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="type" value="{{ $type }}">

                    <div class="col-md-2">
                        <label class="form-label mb-1">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            View
                        </button>
                    </div>
                </div>
            </div>
        </form>

        @if($selectedParticipantId && !$member)
            <div class="alert alert-warning">Selected member not found.</div>
        @endif

        @if($member)
            @php
                $ln  = $member->LastName  ?? $member->lastname  ?? '';
                $fn  = $member->FirstName ?? $member->firstname ?? '';
                $pid = $member->ParticipantId ?? $member->participant_id ?? '';
            @endphp

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Member:</strong> {{ $ln }}, {{ $fn }}
                        (ID: {{ str_pad($pid, 3, '0', STR_PAD_LEFT) }})
                    </div>
                    <div>
                        @if($assessments->isNotEmpty())
                            <small>Showing {{ $assessments->count() }} assessment(s), latest on top.</small>
                        @else
                            <small>No assessments found for this member.</small>
                        @endif
                    </div>
                </div>

                <div class="card-body p-2">
                    @if($assessments->isEmpty())
                        <div class="alert alert-info mb-0">
                            No Social Service Quarterly Assessment found for this member.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 12%;">Date</th>
                                        <th style="width: 35%;">Social Worker</th>
                                        <th style="width: 20%;">Care Plan / Discharge Review</th>
                                        <th style="width: 15%;">Created By</th>
                                        <th style="width: 18%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assessments as $a)
                                        <tr>
                                            <td>
                                                {{ \Carbon\Carbon::parse($a->assessment_date)->format('m/d/Y') }}
                                            </td>
                                            <td>{{ $a->social_worker_name }}</td>
                                            <td>
                                                <div>
                                                    <strong>Care:</strong>
                                                    {{ $a->care_plan_reviewed ? 'Yes' : 'No' }}
                                                </div>
                                                <div>
                                                    <strong>Discharge:</strong>
                                                    {{ $a->discharge_plan_reviewed ? 'Yes' : 'No' }}
                                                </div>
                                            </td>
                                            <td>
                                                {{ $a->created_by ?? '' }}
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    {{-- View --}}
                                                    <a href="{{ route('ss-quarterly.show', [
                                                            'id'   => $a->id,
                                                            'type' => $type,
                                                        ]) }}"
                                                       class="btn btn-outline-secondary"
                                                       target="_blank">
                                                        View
                                                    </a>

                                                    {{-- Print --}}
                                                    <a href="{{ route('ss-quarterly.show', [
                                                            'id'   => $a->id,
                                                            'type' => $type,
                                                            'auto_print' => 1,
                                                        ]) }}"
                                                       class="btn btn-outline-dark"
                                                       target="_blank">
                                                        Print
                                                    </a>

                                                    {{-- PDF --}}
                                                    <a href="{{ route('ss-quarterly.pdf', $a->id) }}"
                                                       class="btn btn-outline-info"
                                                       target="_blank">
                                                        PDF
                                                    </a>
                                                </div>
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
