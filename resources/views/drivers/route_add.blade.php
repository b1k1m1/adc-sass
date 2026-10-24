@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Add Route
                    </h4>

                    <div class="page-title-right">

                        <a href="{{ route('route.list') }}"
                           class="btn btn-primary waves-effect waves-light">

                            <i class="mdi mdi-arrow-left me-1"></i>
                            Back
                        </a>

                    </div>

                </div>

            </div>
        </div>
        {{-- End Page Title --}}

        @if (session('message'))
            <div class="alert alert-{{ session('alert-type') === 'error' ? 'danger' : session('alert-type') }} alert-dismissible fade show"
                 role="alert">

                {{ session('message') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>
            </div>
        @endif

        <div class="row justify-content-center">

            <div class="col-xl-8 col-lg-9">

                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            Route Information
                        </h4>
                    </div>

                    <div class="card-body">

                        <form method="POST"
                              action="{{ route('route.store') }}"
                              id="routeForm"
                              autocomplete="off">

                            @csrf

                            <div class="row">

                                {{-- Route ID --}}
                                <div class="col-md-4 mb-3">

                                    <label for="Route_id"
                                           class="form-label">

                                        Route ID

                                    </label>

                                    <input type="text"
                                           id="Route_id"
                                           class="form-control bg-light"
                                           value="{{ $nextRouteId }}"
                                           readonly>

                                    <div class="form-text">
                                        Route ID will be assigned automatically.
                                    </div>

                                </div>

                                {{-- Shift --}}
                                <div class="col-md-4 mb-3">

                                    <label for="shift_code"
                                           class="form-label">

                                        Shift
                                        <span class="text-danger">*</span>

                                    </label>

                                    <select name="shift_code"
                                            id="shift_code"
                                            class="form-select @error('shift_code') is-invalid @enderror"
                                            required>

                                        <option value="">
                                            Select Shift
                                        </option>

                                        <option value="A"
                                            {{ old('shift_code') === 'A' ? 'selected' : '' }}>

                                            Morning

                                        </option>

                                        <option value="N"
                                            {{ old('shift_code') === 'N' ? 'selected' : '' }}>

                                            Noon

                                        </option>

                                    </select>

                                    @error('shift_code')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                                {{-- Status --}}
                                <div class="col-md-4 mb-3">

                                    <label for="trans_flag"
                                           class="form-label">

                                        Status
                                        <span class="text-danger">*</span>

                                    </label>

                                    <select name="trans_flag"
                                            id="trans_flag"
                                            class="form-select @error('trans_flag') is-invalid @enderror"
                                            required>

                                        <option value="A"
                                            {{ old('trans_flag', 'A') === 'A' ? 'selected' : '' }}>

                                            Active

                                        </option>

                                        <option value="H"
                                            {{ old('trans_flag') === 'H' ? 'selected' : '' }}>

                                            Hold

                                        </option>

                                    </select>

                                    @error('trans_flag')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                                {{-- Route Name --}}
                                <div class="col-md-12 mb-3">

                                    <label for="Route_name"
                                           class="form-label">

                                        Route Name
                                        <span class="text-danger">*</span>

                                    </label>

                                    <input type="text"
                                           name="Route_name"
                                           id="Route_name"
                                           maxlength="30"
                                           value="{{ old('Route_name') }}"
                                           class="form-control text-uppercase @error('Route_name') is-invalid @enderror"
                                           placeholder="Enter route name"
                                           required
                                           autofocus>

                                    @error('Route_name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                    <div class="form-text">
                                        Maximum 30 characters.
                                    </div>

                                </div>

                            </div>

                            <hr>

                            <div class="d-flex justify-content-end gap-2">

                                <a href="{{ route('route.list') }}"
                                   class="btn btn-secondary waves-effect">

                                    Cancel

                                </a>

                                <button type="submit"
                                        id="saveRouteBtn"
                                        class="btn btn-success waves-effect waves-light">

                                    <i class="mdi mdi-content-save me-1"></i>
                                    Save Route

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>

@endsection


@push('scripts')

<script>
$(document).ready(function () {

    $('#Route_name').on('input', function () {
        this.value = this.value.toUpperCase();
    });

    $('#routeForm').on('submit', function () {

        const button = $('#saveRouteBtn');

        button.prop('disabled', true);

        button.html(
            '<span class="spinner-border spinner-border-sm me-1" role="status"></span>' +
            'Saving...'
        );

    });

});
</script>

@endpush
