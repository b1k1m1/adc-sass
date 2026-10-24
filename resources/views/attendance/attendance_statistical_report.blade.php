@extends('admin.admin_dashboard')

@section('admin')

<style>
    .stat-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 16px;
        background: #fff;
        height: 100%;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }

    .stat-card .stat-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 6px;
    }

    .stat-card .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #212529;
        line-height: 1.2;
    }

    .stat-card .stat-sub {
        font-size: 12px;
        color: #6c757d;
        margin-top: 4px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .section-subtitle {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 15px;
    }

    .badge-soft-success {
        background-color: #e8f7ef;
        color: #198754;
        border: 1px solid #bce3cc;
    }

    .badge-soft-danger {
        background-color: #fdecec;
        color: #dc3545;
        border: 1px solid #f5bfc4;
    }

    .reason-text {
        font-size: 12px;
        line-height: 1.35;
    }

    .summary-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        background: #f8f9fa;
        color: #495057;
    }
</style>

<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="card mb-3">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="mb-1">Attendance Statistical Report</h4>
                        <small class="text-muted">
                            Attendance count by selected date range, shift, billable status, and member detail.
                        </small>
                    </div>

                    <a href="{{ route('attendance.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ t('common.back','Back') }}
                    </a>
                </div>

            </div>
        </div>

        {{-- Filter Section --}}
        <div class="card mb-3">
            <div class="card-body">

                <form method="GET"
                      action="{{ route('attendance.statistical.report') }}"
                      class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Start Date</label>
                        <input type="date"
                               name="start_date"
                               value="{{ $startDateInput ?? '' }}"
                               class="form-control"
                               required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">End Date</label>
                        <input type="date"
                               name="end_date"
                               value="{{ $endDateInput ?? '' }}"
                               class="form-control"
                               required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Shift</label>
                        <select name="shift" class="form-select" required>
                            <option value="A" {{ ($shift ?? '') == 'A' ? 'selected' : '' }}>
                                Morning Shift
                            </option>
                            <option value="N" {{ ($shift ?? '') == 'N' ? 'selected' : '' }}>
                                Noon Shift
                            </option>
                            <option value="ALL" {{ ($shift ?? '') == 'ALL' ? 'selected' : '' }}>
                                All Shifts
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Generate Report
                        </button>

                        @if(!empty($reportLoaded))
                            <a href="{{ route('attendance.statistical.report.pdf', [
                                    'start_date' => $startDateInput,
                                    'end_date'   => $endDateInput,
                                    'shift'      => $shift,
                                ]) }}"
                               target="_blank"
                               class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        @endif
                    </div>

                </form>

            </div>
        </div>

        @if(!empty($reportLoaded))

            {{-- Main Report Summary --}}
            <div class="card mb-3">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <div>
                            <div class="section-title">Report Summary</div>
                            <div class="section-subtitle">
                                {{ $startDateDisplay }} to {{ $endDateDisplay }} | {{ $shiftLabel }}
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">

                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="summary-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="stat-label">Date Range</div>
                                <div class="fw-bold">
                                    {{ $startDateDisplay }} to {{ $endDateDisplay }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="summary-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-label">Shift</div>
                                <div class="fw-bold">
                                    {{ $shiftLabel }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="summary-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="stat-label">Selected Days</div>
                                <div class="stat-value">
                                    {{ $totalSelectedDays }}
                                </div>
                                <div class="stat-sub">Calendar days selected</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="summary-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-label">Members Present</div>
                                <div class="stat-value">
                                    {{ $totalMembersPresent }}
                                </div>
                                <div class="stat-sub">Unique members with attendance</div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            {{-- Billing Summary --}}
            <div class="card mb-3">
                <div class="card-body">

                    <div class="section-title">Billing Summary</div>
                    <div class="section-subtitle">
                        Members are marked non-billable if HMO, HMO ID, PA No, or valid PA End Date is missing/expired, or if HMO is Private Pay.
                    </div>

                    <div class="row g-3">

                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="summary-icon">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                                <div class="stat-label">Billable Members</div>
                                <div class="stat-value text-success">
                                    {{ $billableMemberCount ?? 0 }}
                                </div>
                                <div class="stat-sub">Unique members</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="summary-icon">
                                    <i class="fas fa-exclamation-triangle text-danger"></i>
                                </div>
                                <div class="stat-label">Non-Billable Members</div>
                                <div class="stat-value text-danger">
                                    {{ $nonBillableMemberCount ?? 0 }}
                                </div>
                                <div class="stat-sub">Need review</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="summary-icon">
                                    <i class="fas fa-file-invoice-dollar text-success"></i>
                                </div>
                                <div class="stat-label">Billable Attendance Days</div>
                                <div class="stat-value text-success">
                                    {{ $billableAttendanceDays ?? 0 }}
                                </div>
                                <div class="stat-sub">Total present days</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="summary-icon">
                                    <i class="fas fa-ban text-danger"></i>
                                </div>
                                <div class="stat-label">Non-Billable Attendance Days</div>
                                <div class="stat-value text-danger">
                                    {{ $nonBillableAttendanceDays ?? 0 }}
                                </div>
                                <div class="stat-sub">Total present days</div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <div class="row">

                {{-- Frequency Summary --}}
                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-body">

                            <div class="section-title">Attendance Frequency</div>
                            <div class="section-subtitle">
                                Count of members by total days present.
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">Days Present</th>
                                            <th class="text-center">Members Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($distribution as $row)
                                            <tr>
                                                <td class="text-center">
                                                    {{ $row['days_present'] }}
                                                    {{ $row['days_present'] == 1 ? 'Day' : 'Days' }}
                                                </td>
                                                <td class="text-center fw-bold">
                                                    {{ $row['member_count'] }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center text-muted">
                                                    No attendance found for selected criteria.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                    @if(!empty($distribution) && count($distribution) > 0)
                                        <tfoot>
                                            <tr class="table-light">
                                                <th class="text-center">Total</th>
                                                <th class="text-center">
                                                    {{ $totalMembersPresent }}
                                                </th>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Attendance Average / Gender Summary --}}
                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-body">

                            <div class="section-title">Attendance Average</div>
                            <div class="section-subtitle">
                                Average attendance and gender count.
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle mb-1">
                                    <tbody>
                                        <tr>
                                            <th>Total Attendance Days</th>
                                            <td class="text-center fw-bold">
                                                {{ $totalAttendanceDays ?? 0 }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Avg Attendance Per Day</th>
                                            <td class="text-center fw-bold">
                                                {{ number_format($averageAttendance ?? 0, 2) }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Male Members Present</th>
                                            <td class="text-center fw-bold">
                                                {{ $maleMemberCount ?? 0 }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Female Members Present</th>
                                            <td class="text-center fw-bold">
                                                {{ $femaleMemberCount ?? 0 }}
                                            </td>
                                        </tr>

                                        @if(($unknownGenderMemberCount ?? 0) > 0)
                                            <tr>
                                                <th>Unknown Gender</th>
                                                <td class="text-center fw-bold text-danger">
                                                    {{ $unknownGenderMemberCount ?? 0 }}
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <small class="text-muted">
                                Average = Total Attendance Days ÷ Selected Days.
                            </small>

                        </div>
                    </div>
                </div>

            </div>

            <div class="row">

                {{-- Member Detail --}}
                <div class="col-lg-12">
                    <div class="card mb-3">
                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                <div>
                                    <div class="section-title">Member Detail</div>
                                    <div class="section-subtitle">
                                        Member-wise attendance days with billing status and non-billable reason.
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="attendanceStatDetailTable"
                                    class="table table-bordered table-striped align-middle nowrap w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Participant ID</th>
                                            <th>Last Name</th>
                                            <th>First Name</th>
                                            <th>Shift</th>
                                            <th class="text-center">Days</th>
                                            <th>Billing Status</th>
                                            <th>Reason</th>
                                            <th>HMO</th>
                                            <th>HMO ID</th>
                                            <th>PA No</th>
                                            <th>PA End</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($details as $row)
                                            <tr>
                                                <td>{{ $row['ParticipantId'] }}</td>
                                                <td>{{ $row['LastName'] }}</td>
                                                <td>{{ $row['FirstName'] }}</td>

                                                <td>
                                                    @if(($row['MemberShift'] ?? '') == 'A')
                                                        Morning
                                                    @elseif(($row['MemberShift'] ?? '') == 'N')
                                                        Noon
                                                    @else
                                                        {{ $row['MemberShift'] ?? '' }}
                                                    @endif
                                                </td>

                                                <td class="text-center fw-bold">
                                                    {{ $row['days_present'] }}
                                                </td>

                                                <td>
                                                    @if(($row['billing_status'] ?? '') === 'Billable')
                                                        <span class="badge badge-soft-success">
                                                            Billable
                                                        </span>
                                                    @else
                                                        <span class="badge badge-soft-danger">
                                                            Non-Billable
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="reason-text">
                                                    {{ $row['non_billable_reasons'] ?? '' }}
                                                </td>

                                                <td>{{ $row['HMO_enrolled'] ?? '' }}</td>
                                                <td>{{ $row['medicaid_HMO_id'] ?? '' }}</td>
                                                <td>{{ $row['medicaid_PA_no'] ?? '' }}</td>
                                                <td>{{ $row['medicaid_PA_End'] ?? '' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center text-muted">
                                                    No attendance found for selected criteria.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            {{-- Non-Billable Review List --}}
            @if(!empty($nonBillableDetails) && count($nonBillableDetails) > 0)
                <div class="card mb-3">
                    <div class="card-body">

                        <div class="section-title text-danger">Non-Billable Review List</div>
                        <div class="section-subtitle">
                            These members need insurance / PA information reviewed before billing.
                        </div>

                        <div class="table-responsive">
                            <table id="nonBillableTable"
                                   class="table table-bordered table-striped align-middle nowrap w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Participant ID</th>
                                        <th>Last Name</th>
                                        <th>First Name</th>
                                        <th class="text-center">Days Present</th>
                                        <th>Reason</th>
                                        <th>HMO</th>
                                        <th>HMO ID</th>
                                        <th>PA No</th>
                                        <th>PA End</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nonBillableDetails as $row)
                                        <tr>
                                            <td>{{ $row['ParticipantId'] }}</td>
                                            <td>{{ $row['LastName'] }}</td>
                                            <td>{{ $row['FirstName'] }}</td>
                                            <td class="text-center fw-bold">{{ $row['days_present'] }}</td>
                                            <td class="reason-text text-danger">
                                                {{ $row['non_billable_reasons'] ?? '' }}
                                            </td>
                                            <td>{{ $row['HMO_enrolled'] ?? '' }}</td>
                                            <td>{{ $row['medicaid_HMO_id'] ?? '' }}</td>
                                            <td>{{ $row['medicaid_PA_no'] ?? '' }}</td>
                                            <td>{{ $row['medicaid_PA_End'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            @endif

        @endif

    </div>
</div>

@include('partials.sweetalert')

@endsection

@section('scripts')

<script>
$(document).ready(function () {

    if ($('#attendanceStatDetailTable').length) {

        if ($.fn.DataTable.isDataTable('#attendanceStatDetailTable')) {
            $('#attendanceStatDetailTable').DataTable().destroy();
        }

        $('#attendanceStatDetailTable').DataTable({
            paging: true,
            pageLength: 25,
            ordering: true,
            searching: true,
            info: true,
            scrollX: true,
            order: [[4, 'desc'], [1, 'asc'], [2, 'asc']],
            columnDefs: [
                { targets: [4], className: 'text-center' }
            ]
        });
    }

    if ($('#nonBillableTable').length) {

        if ($.fn.DataTable.isDataTable('#nonBillableTable')) {
            $('#nonBillableTable').DataTable().destroy();
        }

        $('#nonBillableTable').DataTable({
            paging: true,
            pageLength: 25,
            ordering: true,
            searching: true,
            info: true,
            scrollX: true,
            order: [[3, 'desc'], [1, 'asc'], [2, 'asc']],
            columnDefs: [
                { targets: [3], className: 'text-center' }
            ]
        });
    }

});
</script>

@endsection
