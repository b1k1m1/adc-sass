@extends('admin.admin_dashboard')

@section('title', 'Edit Organization')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        <div class="row mb-3">

            <div class="col-12">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h4 class="mb-1">
                            Edit Organization
                        </h4>

                        <p class="text-muted mb-0">
                            Platform Administration
                        </p>

                    </div>


                    <a
                        href="{{ route('saas.organizations.index') }}"
                        class="btn btn-outline-secondary">

                        <i class="mdi mdi-arrow-left me-1"></i>

                        Back

                    </a>

                </div>

            </div>

        </div>


        <div class="row">

            <div class="col-xl-8 col-lg-10">

                <div class="card">

                    <div class="card-header">

                        <h5 class="card-title mb-0">
                            Organization Information
                        </h5>

                    </div>


                    <div class="card-body">

                        @if ($errors->any())

                            <div class="alert alert-danger">

                                <ul class="mb-0">

                                    @foreach ($errors->all() as $error)

                                        <li>{{ $error }}</li>

                                    @endforeach

                                </ul>

                            </div>

                        @endif


                        <form
                            method="POST"
                            action="{{ route('saas.organizations.update', $organization->id) }}">

                            @csrf
                            @method('PUT')


                            <div class="mb-3">

                                <label class="form-label">
                                    Organization ID
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $organization->id }}"
                                    readonly>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Organization Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="organization_name"
                                    class="form-control"
                                    maxlength="50"
                                    value="{{ old('organization_name', $organization->organization_name) }}"
                                    required>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Database Name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $organization->database_name }}"
                                    readonly>

                                <div class="form-text">
                                    Database assignment cannot be changed from this screen.
                                </div>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Media Folder
                                </label>

                                <input
                                    type="text"
                                    name="media_folder"
                                    class="form-control"
                                    maxlength="50"
                                    value="{{ old('media_folder', $organization->media_folder) }}">

                            </div>


                            <div class="row">

                                <div class="col-md-6">

                                    <div class="mb-3">

                                        <label class="form-label">
                                            Status
                                        </label>

                                        <select
                                            name="status"
                                            class="form-select"
                                            required>

                                            <option
                                                value="1"
                                                {{ (string) old('status', $organization->status) === '1' ? 'selected' : '' }}>
                                                Active
                                            </option>

                                            <option
                                                value="0"
                                                {{ (string) old('status', $organization->status) === '0' ? 'selected' : '' }}>
                                                Inactive
                                            </option>

                                        </select>

                                    </div>

                                </div>


                                <div class="col-md-6">

                                    <div class="mb-3">

                                        <label class="form-label">
                                            Shift Support
                                        </label>

                                        <select
                                            name="has_shift"
                                            class="form-select"
                                            required>

                                            <option
                                                value="1"
                                                {{ (string) old('has_shift', $organization->has_shift) === '1' ? 'selected' : '' }}>
                                                Yes
                                            </option>

                                            <option
                                                value="0"
                                                {{ (string) old('has_shift', $organization->has_shift) === '0' ? 'selected' : '' }}>
                                                No
                                            </option>

                                        </select>

                                    </div>

                                </div>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Database Readiness
                                </label>

                                <div>

                                    @if ((int) $organization->is_ready === 1)

                                        <span class="badge bg-success">
                                            Ready
                                        </span>

                                    @else

                                        <span class="badge bg-warning text-dark">
                                            Not Ready
                                        </span>

                                    @endif

                                </div>

                            </div>


                            <div class="alert alert-info">

                                <i class="mdi mdi-information-outline me-1"></i>

                                Database name and readiness are controlled by the provisioning process and cannot be edited manually here.

                            </div>


                            <div class="d-flex gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary">

                                    <i class="mdi mdi-content-save me-1"></i>

                                    Update Organization

                                </button>


                                <a
                                    href="{{ route('saas.organizations.index') }}"
                                    class="btn btn-light">

                                    Cancel

                                </a>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@section('scripts')

@endsection