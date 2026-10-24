
<!-- resources/views/attendance_report.blade.php -->
@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="container-fluid">
<!--
<form action="{{ route('attendance.report') }}" method="GET">
    <label>Start Date: <input type="date" name="start_date" required></label>
    <label>End Date: <input type="date" name="end_date" required></label>
    <button type="submit">Generate Report</button>
</form>
-->

    <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Generate Attendance Report</h4>

                    <div class="page-title-right">
                        <a href="{{ route('attendance.dashboard') }}#profile1" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                    </div>

                </div>
            </div>
    </div>
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('attendance.report', 'A') }}"
        class="btn {{ ($type ?? 'A') === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
            Morning Shift
        </a>
        <a href="{{ route('attendance.report', 'N') }}"
        class="btn {{ ($type ?? 'A') === 'N' ? 'btn-secondary' : 'btn-outline-secondary' }}">
            Noon Shift
        </a>
    </div>

    <form action="{{ route('attendance.report', $type ?? 'A') }}" method="GET">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date:</label>
                <input type="date" id="start_date" name="start_date" class="form-control"
                    value="{{ request('start_date') }}">
            </div>

            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date:</label>
                <input type="date" id="end_date" name="end_date" class="form-control"
                    value="{{ request('end_date') }}">
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
            </div>
        </div>
    </form>
</div>
</div>
</div>
@endsection


