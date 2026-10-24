@extends('admin.admin_dashboard')
@section('title', 'Generate Claims')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

        {{-- Header + Back button --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Generate Claims from Attendance</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{route('dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        {{-- SHIFT BUTTONS (consistent with attendance) --}}
        <div class="d-flex gap-2 mb-3">
            <a href="{{ route('claims.attendance.form', 'A') }}"
            class="btn {{ ($type ?? 'A') === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
                Morning Shift
            </a>

            <a href="{{ route('claims.attendance.form', 'N') }}"
            class="btn {{ ($type ?? 'A') === 'N' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                Noon Shift
            </a>
        </div>

        {{-- DUPLICATE WARNING --}}
        @if(session('confirm_generate'))
            <div class="alert alert-warning">
                <strong>Warning:</strong> {{ session('message') }}
                <br>
                <small>Generating again may create duplicates.</small>

                <form action="{{ route('claims.attendance.generate', $type) }}" method="GET">
                    <input type="hidden" name="start_date" value="{{ session('start_date') }}">
                    <input type="hidden" name="end_date" value="{{ session('end_date') }}">
                    <input type="hidden" name="force" value="1">
                    <button class="btn btn-danger btn-sm mt-2">Yes, Generate Again</button>
                </form>
            </div>
        @endif

        {{-- FORM (consistent with attendance) --}}
        <form action="{{ route('claims.attendance.generate', $type) }}" method="POST">
            @csrf
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label">Start Date:</label>
                    <input type="date" name="start_date"
                        value="{{ $startDate }}"
                        class="form-control @error('start_date') is-invalid @enderror">
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">End Date:</label>
                    <input type="date" name="end_date"
                        value="{{ $endDate }}"
                        class="form-control @error('end_date') is-invalid @enderror">
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <button class="btn btn-primary w-100">
                        Generate Claims
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection
