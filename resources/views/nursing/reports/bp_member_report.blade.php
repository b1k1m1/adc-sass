@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Member BP / Pulse / Weight Report</h4>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">Back</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('nursing.bp_report.member') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Member</label>
                            <select name="participant_id" id="participant_id" class="form-select">
                                <option value="">Select Member</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->ParticipantId }}"
                                        {{ ($participantId ?? '') == $member->ParticipantId ? 'selected' : '' }}>
                                        {{ $member->ParticipantId }} - {{ $member->LastName }}, {{ $member->FirstName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Start Date</label>
                            <input type="text" name="start_date" id="start_date" class="form-control"
                                   value="{{ $startDate ?? '' }}" placeholder="mm/dd/yyyy">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">End Date</label>
                            <input type="text" name="end_date" id="end_date" class="form-control"
                                   value="{{ $endDate ?? '' }}" placeholder="mm/dd/yyyy">
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" id="btnLoad" class="btn btn-primary" disabled>Load</button>

                            @if(!empty($summary))
                                <a href="{{ route('nursing.bp_report.member.pdf', request()->query()) }}" class="btn btn-danger" target="_blank">PDF</a>
                                <a href="{{ route('nursing.bp_report.member.excel', request()->query()) }}" class="btn btn-success">Excel</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(!empty($summary))
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="mb-3">
                        {{ $summary['member']->ParticipantId ?? '' }} -
                        {{ $summary['member']->LastName ?? '' }},
                        {{ $summary['member']->FirstName ?? '' }}
                    </h5>

                    <div class="row mb-3">
                        <div class="col-md-2"><strong>From:</strong> {{ $summary['start_date'] }}</div>
                        <div class="col-md-2"><strong>To:</strong> {{ $summary['end_date'] }}</div>
                        <div class="col-md-2"><strong>Avg LowBP:</strong> {{ $summary['avg_lowbp'] ?? '' }}</div>
                        <div class="col-md-2"><strong>Avg HighBP:</strong> {{ $summary['avg_highbp'] ?? '' }}</div>
                        <div class="col-md-2"><strong>Avg Pulse:</strong> {{ $summary['avg_pulse'] ?? '' }}</div>
                        <div class="col-md-2"><strong>Avg Weight:</strong> {{ isset($summary['avg_weight']) ? number_format($summary['avg_weight'], 1) : '' }}</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>LowBP</th>
                                    <th>HighBP</th>
                                    <th>Pulse</th>
                                    <th>Weight</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row->CalenderDate)->format('m/d/Y') }}</td>
                                        <td>{{ $row->LowBP }}</td>
                                        <td>{{ $row->HighBP }}</td>
                                        <td>{{ $row->Pulse }}</td>
                                        <td>{{ $row->Weight !== null ? number_format((float)$row->Weight, 1) : '' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No records found.</td>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#participant_id').select2({
        width: '100%'
    });

    flatpickr("#start_date", {
        dateFormat: "m/d/Y",
        allowInput: true,
        maxDate: "today",
        onChange: toggleLoadButton,
        onClose: toggleLoadButton,
        onValueUpdate: toggleLoadButton
    });

    flatpickr("#end_date", {
        dateFormat: "m/d/Y",
        allowInput: true,
        maxDate: "today",
        onChange: toggleLoadButton,
        onClose: toggleLoadButton,
        onValueUpdate: toggleLoadButton
    });

    document.getElementById('start_date').addEventListener('keyup', toggleLoadButton);
    document.getElementById('end_date').addEventListener('keyup', toggleLoadButton);
    document.getElementById('start_date').addEventListener('blur', toggleLoadButton);
    document.getElementById('end_date').addEventListener('blur', toggleLoadButton);

    function toggleLoadButton() {
        const participant = document.getElementById('participant_id').value.trim();
        const startDate = document.getElementById('start_date').value.trim();
        const endDate = document.getElementById('end_date').value.trim();

        document.getElementById('btnLoad').disabled = !(participant && startDate && endDate);
    }

    $('#participant_id').on('change', toggleLoadButton);
    document.getElementById('start_date').addEventListener('input', toggleLoadButton);
    document.getElementById('end_date').addEventListener('input', toggleLoadButton);

    toggleLoadButton();
});
</script>
@endsection
