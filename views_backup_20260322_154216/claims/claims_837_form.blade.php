@extends('admin.admin_dashboard')
@section('title', 'Generate 837P')
@section('admin')

<div class="page-content">
    <div class="container-fluid">
        {{-- Header + Back button --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Generate 837P from Claims</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{route('dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        {{-- Shift buttons just like Attendance / Claims generate --}}
        <div class="d-flex gap-2 mb-3">
            <a href="{{ route('claims.837.form', 'A') }}"
            class="btn {{ ($type ?? 'A') === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
                Morning Shift
            </a>
            <a href="{{ route('claims.837.form', 'N') }}"
            class="btn {{ ($type ?? 'A') === 'N' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                Noon Shift
            </a>
        </div>
        <form action="{{ route('claims.837.download', $type) }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date"
                        name="start_date"
                        value="{{ $startDate }}"
                        class="form-control @error('start_date') is-invalid @enderror">
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date"
                        name="end_date"
                        value="{{ $endDate }}"
                        class="form-control @error('end_date') is-invalid @enderror">
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <button class="btn btn-primary w-100">
                        Generate 837P File
                    </button>
                </div>
            </div>
        </form>
        <div class="alert alert-info mt-3">
        File will include all <strong>claims</strong> whose <strong>service_from</strong> is in the
        selected date range, for
        <strong>{{ $type === 'N' ? 'Inactive (Noon Shift)' : 'Active (Morning Shift)' }}</strong> members.
        <br>
        Later we can refine this to “only validated claims” or “only claims without a batch id”.
    </div>
    </div>
</div>
@endsection
