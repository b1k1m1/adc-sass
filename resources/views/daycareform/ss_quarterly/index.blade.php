@extends('admin.admin_dashboard')
@section('title', 'Nursing')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        @if(session('message'))
            <div class="alert alert-success alert-sm">
                {{ session('message') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Social Service Quarterly Assessments</h4>

        {{-- Filter bar: Morning/Noon + Member dropdown --}}


                {{-- Morning / Noon buttons --}}
                <div class="mb-3 d-flex gap-2">
                    @php
                        $currentParticipant = $selectedParticipantId;
                    @endphp

                    <a href="{{ route('ss-quarterly.index', ['type' => 'A', 'participant_id' => $currentParticipant]) }}"
                       class="btn {{ $type === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Morning
                    </a>

                    <a href="{{ route('ss-quarterly.index', ['type' => 'N', 'participant_id' => $currentParticipant]) }}"
                       class="btn {{ $type === 'N' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                        Noon
                    </a>
                    <a href="{{ route('social.dashboard') }}" class="btn btn-primary">{{ t('common.back','Back') }}</a>
                </div>
        </div>
                {{-- Member dropdown --}}
                <form method="GET" action="{{ route('ss-quarterly.index') }}" class="row g-2 align-items-end">

                    <input type="hidden" name="type" value="{{ $type }}">

                    <div class="col-md-6">
                        <label class="form-label">Member</label>
                        <select name="participant_id" class="form-select">
                            <option value="">-- Select Member --</option>
                            @foreach($members as $m)
                                <option value="{{ $m->ParticipantId }}"
                                    {{ $selectedParticipantId == $m->ParticipantId ? 'selected' : '' }}>
                                    {{ $m->LastName }}, {{ $m->FirstName }} ({{ $m->ParticipantId }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Show
                        </button>
                    </div>

                    <div class="col-md-2 ms-auto text-end">
                        {{-- New Assessment button --}}
                        <a href="{{ route('ss-quarterly.create', [
                                'type'          => $type,
                                'participant_id'=> $selectedParticipantId,
                            ]) }}"
                           class="btn btn-success w-100">
                            + New Assessment
                        </a>
                    </div>
                </form>

            </div>
        </div>

        {{-- List for selected member --}}
        @if($selectedParticipantId)
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Assessments for:</strong>
                        @php
                            $selectedMember = $members->firstWhere('ParticipantId', $selectedParticipantId);
                        @endphp
                        @if($selectedMember)
                            {{ $selectedMember->LastName }}, {{ $selectedMember->FirstName }}
                            ({{ $selectedMember->ParticipantId }})
                        @endif
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 140px;">Assessment Date</th>
                                <th>Medical Record #</th>
                                <th>Social Worker</th>
                                <th>Care Plan</th>
                                <th>Discharge Plan</th>
                                <th style="width: 140px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($assessments as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->assessment_date)->format('m/d/Y') }}</td>
                                <td>{{ $row->medical_record_no }}</td>
                                <td>{{ $row->social_worker_name }}</td>
                                <td>{{ $row->care_plan_reviewed ? 'Yes' : 'No' }}</td>
                                <td>{{ $row->discharge_plan_reviewed ? 'Yes' : 'No' }}</td>
                                <td>
                                   <td>
                                        <a href="{{ route('ss-quarterly.show', [
                                                'id'   => $row->id,
                                                'type' => $type,
                                            ]) }}"
                                        class="btn btn-outline-secondary">
                                            View
                                        </a>

                                        <a href="{{ route('ss-quarterly.edit', [
                                                'id'   => $row->id,
                                                'type' => $type,
                                            ]) }}"
                                        class="btn btn-outline-primary mt-1 mt-md-0">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No assessments found for this member.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-info alert-sm">
                Please select a member and click <strong>Show</strong> to see assessments.
            </div>
        @endif

    </div>
</div>

@endsection
