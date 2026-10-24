@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Shift BP / Pulse / Weight Average Report</h4>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">Back</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('nursing.bp_report.shift') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label d-block">Shift</label>
                            <div class="d-flex gap-2">
                                <input type="hidden" name="shift" id="shift" value="{{ $shift ?? '' }}">

                                <button type="button" class="btn btn-outline-primary shift-btn {{ ($shift ?? '') == 'A' ? 'active' : '' }}" data-value="A">
                                    Morning Shift
                                </button>

                                <button type="button" class="btn btn-outline-primary shift-btn {{ ($shift ?? '') == 'N' ? 'active' : '' }}" data-value="N">
                                    Noon Shift
                                </button>
                            </div>
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

                        <div class="col-md-5 d-flex gap-2">
                            <button type="submit" id="btnLoad" class="btn btn-primary" disabled>Load</button>

                            @if(($shift ?? '') && ($startDate ?? '') && ($endDate ?? ''))
                                <a href="{{ route('nursing.bp_report.shift.pdf', request()->query()) }}" class="btn btn-danger" target="_blank">PDF</a>
                                <a href="{{ route('nursing.bp_report.shift.excel', request()->query()) }}" class="btn btn-success">Excel</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(($shift ?? '') && ($startDate ?? '') && ($endDate ?? ''))
            <div class="card mt-3">
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Shift:</strong> {{ ($shift ?? '') == 'A' ? 'Morning' : 'Noon' }} &nbsp;&nbsp;
                        <strong>From:</strong> {{ $startDate }} &nbsp;&nbsp;
                        <strong>To:</strong> {{ $endDate }}
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>ParticipantId</th>
                                    <th>LastName</th>
                                    <th>FirstName</th>
                                    <th>Avg LowBP</th>
                                    <th>Avg HighBP</th>
                                    <th>Avg Pulse</th>
                                    <th>Avg Weight</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>{{ $row->ParticipantId }}</td>
                                        <td>{{ $row->LastName }}</td>
                                        <td>{{ $row->FirstName }}</td>
                                        <td>{{ $row->avg_lowbp }}</td>
                                        <td>{{ $row->avg_highbp }}</td>
                                        <td>{{ $row->avg_pulse }}</td>
                                        <td>{{ $row->avg_weight !== null ? number_format((float)$row->avg_weight, 1) : '' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No records found.</td>
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

    document.querySelectorAll('.shift-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('shift').value = this.dataset.value;

            document.querySelectorAll('.shift-btn').forEach(x => x.classList.remove('active'));
            this.classList.add('active');

            toggleLoadButton();
        });
    });

    function toggleLoadButton() {
        const shift = document.getElementById('shift').value.trim();
        const startDate = document.getElementById('start_date').value.trim();
        const endDate = document.getElementById('end_date').value.trim();

        document.getElementById('btnLoad').disabled = !(shift && startDate && endDate);
    }

    document.getElementById('start_date').addEventListener('input', toggleLoadButton);
    document.getElementById('end_date').addEventListener('input', toggleLoadButton);

    toggleLoadButton();
});
</script>
@endsection
