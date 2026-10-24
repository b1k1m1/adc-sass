@extends('admin.admin_dashboard')
@section('title', 'Member Wise Attendance Report')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Member Wise Attendance Report</h4>
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
                <form method="GET" action="{{ route('attendance.memberwise') }}" id="filterForm">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-5">
                            <label class="form-label">Member</label>
                            <select name="ParticipantId" id="ParticipantId" class="form-control">
                                @if(!empty($member))
                                    <option value="{{ $member->ParticipantId }}" selected>
                                        {{ $member->ParticipantId }} - {{ $member->LastName }}, {{ $member->FirstName }}
                                    </option>
                                @elseif(old('ParticipantId'))
                                    <option value="{{ old('ParticipantId') }}" selected>{{ old('ParticipantId') }}</option>
                                @endif
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

                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" id="btnLoad" class="btn btn-primary" disabled>Load</button>

                            @if($validated && !empty($member))
                                <a href="{{ route('attendance.memberwise.pdf', request()->query()) }}"
                                   target="_blank"
                                   class="btn btn-outline-danger">
                                    PDF
                                </a>

                                <a href="{{ route('attendance.memberwise.excel', request()->query()) }}"
                                   class="btn btn-outline-success">
                                    Excel
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($validated && !empty($member))
            <div class="card mt-3">
                <div class="card-body">

                    <div class="text-center mb-3">
                        <h5 class="mb-1">{{ config('company.comp_nm', '') }}</h5>
                        <div>{{ config('company.complc_addr1', '') }}</div>
                        <div>{{ config('company.complc_addr2', '') }}</div>
                        <div>{{ config('company.complc_ph', '') }}</div>
                        <h6 class="mt-3 mb-1">Member Wise Attendance Report</h6>
                        <div>
                            <strong>Member:</strong>
                            {{ $member->ParticipantId }} - {{ $member->LastName }}, {{ $member->FirstName }}
                            <div>
                                <strong>Date Range:</strong> {{ $dateRangeText }}
                            </div>
                            <div>
                                <strong>Total Days Present:</strong> {{ $totalDaysPresent }}
                            </div>
                            <div>
                                <strong>Total Days in Range:</strong> {{ $totalDaysInRange }}
                            </div>
                        </div>
                        <div>
                            <strong>Date Range:</strong> {{ $dateRangeText }}
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px;">Date</th>
                                    <th style="width: 90px;">Day</th>
                                    <th style="width: 100px;" class="text-center">Attendance</th>
                                    <th>Insurance Name</th>
                                    <th>Insurance ID#</th>
                                    <th>Prior Authorization No.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>{{ $row->report_date }}</td>
                                        <td>{{ $row->day_name }}</td>
                                        <td class="text-center fs-5">{{ $row->attendance_mark }}</td>
                                        <td>{{ $row->insurance_name }}</td>
                                        <td>{{ $row->insurance_id }}</td>
                                        <td>{{ $row->prior_auth_no }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No attendance records found for the selected date range.</td>
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
    const startEl = document.getElementById('start_date');
    const endEl   = document.getElementById('end_date');
    const loadBtn = document.getElementById('btnLoad');
    const memberEl = document.getElementById('ParticipantId');

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
        const start = parseDate(startEl.value);
        const end   = parseDate(endEl.value);
        const today = todayOnlyDate();
        const member = memberEl.value;

        let valid = !!member && !!start && !!end;

        if (valid && end < start) valid = false;
        if (valid && start > today) valid = false;
        if (valid && end > today) valid = false;

        loadBtn.disabled = !valid;
    }

    $('#ParticipantId').select2({
        placeholder: 'Select member',
        allowClear: true,
        width: '100%',
        ajax: {
            url: "{{ route('attendance.memberwise.member-search') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term || '' };
            },
            processResults: function (data) {
                return data;
            },
            cache: true
        }
    }).on('change', validateForm);

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

    startEl.addEventListener('keyup', validateForm);
    endEl.addEventListener('keyup', validateForm);

    validateForm();
});
</script>
@endsection

