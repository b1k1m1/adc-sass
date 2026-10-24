@extends('admin.admin_dashboard')
@section('title', 'HMO Attendance Crosstab Report')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">HMO Attendance Crosstab Report</h4>
            </div>
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('attendance.hmocrosstab') }}" id="filterForm">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-4">
                            <label class="form-label">HMO</label>
                            <select name="hmo_enrolled" id="hmo_enrolled" class="form-select">
                                <option value="">Select HMO</option>
                                @foreach($hmoOptions as $hmo)
                                    <option value="{{ $hmo->name }}"
                                        {{ old('hmo_enrolled', $selectedHmo ?? '') == $hmo->name ? 'selected' : '' }}>
                                        {{ $hmo->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Start Date</label>
                            <input type="text"
                                   name="start_date"
                                   id="start_date"
                                   class="form-control"
                                   placeholder="mm/dd/yyyy"
                                   value="{{ old('start_date', $startDateInput ?? '') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">End Date</label>
                            <input type="text"
                                   name="end_date"
                                   id="end_date"
                                   class="form-control"
                                   placeholder="mm/dd/yyyy"
                                   value="{{ old('end_date', $endDateInput ?? '') }}">
                        </div>

                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" id="btnLoad" class="btn btn-primary" disabled>Load</button>

                            @if($validated)
                                <a href="{{ route('attendance.hmocrosstab.pdf', request()->query()) }}"
                                   target="_blank"
                                   class="btn btn-outline-danger">
                                    PDF
                                </a>

                                <a href="{{ route('attendance.hmocrosstab.excel', request()->query()) }}"
                                   class="btn btn-outline-success">
                                    Excel
                                </a>
                            @endif
                        </div>

                    </div>
                </form>
            </div>
        </div>

        @if($validated)
            <div class="card mt-3">
                <div class="card-body">

                    <div class="text-center mb-3">
                        <h5 class="mb-1">{{ config('company.comp_nm', 'Company Name') }}</h5>
                        <div>{{ config('company.company_address', '') }}</div>
                        <div>{{ config('company.company_city_state_zip', '') }}</div>
                        <div>{{ config('company.company_phone', '') }}</div>
                        <h6 class="mt-3 mb-1">HMO Attendance Crosstab Report</h6>
                        <div><strong>HMO:</strong> {{ $selectedHmo }}</div>
                        <div><strong>Date Range:</strong> {{ $startDateInput }} - {{ $endDateInput }}</div>
                        <div><strong>Grand Total:</strong> {{ $grandTotal }}</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 90px;">ParticipantId</th>
                                    <th style="min-width: 140px;">LastName</th>
                                    <th style="min-width: 140px;">FirstName</th>
                                    @foreach($dateHeaders as $hdr)
                                        <th>{{ $hdr['day_no'] }}</th>
                                    @endforeach
                                    <th style="min-width: 70px;" class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td class="text-start">{{ $row['ParticipantId'] }}</td>
                                        <td class="text-start">{{ $row['LastName'] }}</td>
                                        <td class="text-start">{{ $row['FirstName'] }}</td>
                                        @foreach($row['days'] as $val)
                                            <td>{{ $val }}</td>
                                        @endforeach
                                        <th class="text-end">{{ $row['row_total'] }}</th>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 4 + count($dateHeaders) }}" class="text-center text-muted">
                                            No records found for selected HMO and date range.
                                        </td>
                                    </tr>
                                @endforelse
                                <tr class="table-secondary">
                                    <th colspan="3" class="text-end">Total</th>
                                    @foreach($columnTotals as $total)
                                        <th>{{ $total }}</th>
                                    @endforeach
                                    <th class="text-end">{{ $grandTotal }}</th>
                                </tr>
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
$(document).ready(function () {
    const startEl = document.getElementById('start_date');
    const endEl   = document.getElementById('end_date');
    const hmoEl   = document.getElementById('hmo_enrolled');
    const loadBtn = document.getElementById('btnLoad');

    function parseDate(value) {
        if (!value) return null;
        const parts = value.split('/');
        if (parts.length !== 3) return null;

        const month = parseInt(parts[0], 10);
        const day   = parseInt(parts[1], 10);
        const year  = parseInt(parts[2], 10);

        if (!month || !day || !year) return null;

        const dt = new Date(year, month - 1, day);
        if (dt.getFullYear() !== year || dt.getMonth() !== month - 1 || dt.getDate() !== day) {
            return null;
        }
        return dt;
    }

    function todayOnlyDate() {
        const t = new Date();
        return new Date(t.getFullYear(), t.getMonth(), t.getDate());
    }

    function validateForm() {
        const hmo   = hmoEl.value;
        const start = parseDate(startEl.value);
        const end   = parseDate(endEl.value);
        const today = todayOnlyDate();

        let valid = !!hmo && !!start && !!end;

        if (valid && end < start) valid = false;
        if (valid && start > today) valid = false;
        if (valid && end > today) valid = false;

        if (valid) {
            const diffDays = Math.floor((end - start) / (1000 * 60 * 60 * 24));
            if (diffDays > 30) valid = false;
        }

        loadBtn.disabled = !valid;
    }

    flatpickr("#start_date", {
        dateFormat: "m/d/Y",
        allowInput: true,
        maxDate: "today",
        onChange: function(selectedDates, dateStr) {
            if (dateStr) {
                endPicker.set('minDate', dateStr);
            } else {
                endPicker.set('minDate', null);
            }
            validateForm();
        }
    });

    const endPicker = flatpickr("#end_date", {
        dateFormat: "m/d/Y",
        allowInput: true,
        maxDate: "today",
        onChange: function() {
            validateForm();
        }
    });

    $('#hmo_enrolled, #start_date, #end_date').on('change keyup', function () {
        validateForm();
    });

    validateForm();
});
</script>

@endsection
