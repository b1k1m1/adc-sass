@extends('admin.admin_dashboard')

@section('title', 'Add SaaS Organization')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        <div class="row mb-3">

            <div class="col-12">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h4 class="mb-1">
                            Add Organization
                        </h4>

                        <p class="text-muted mb-0">
                            Register a new organization with the ADC SaaS platform.
                        </p>

                    </div>

                    <div>

                        <a
                            href="{{ route('saas.organizations.index') }}"
                            class="btn btn-outline-secondary">

                            <i class="mdi mdi-arrow-left me-1"></i>

                            Back

                        </a>

                    </div>

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

                                        <li>
                                            {{ $error }}
                                        </li>

                                    @endforeach

                                </ul>

                            </div>

                        @endif


                        <form
                            method="POST"
                            action="{{ route('saas.organizations.store') }}">

                            @csrf


                            {{-- Organization Name --}}
                            <div class="mb-3">

                                <label
                                    for="organization_name"
                                    class="form-label">

                                    Organization Name
                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    type="text"
                                    class="form-control @error('organization_name') is-invalid @enderror"
                                    id="organization_name"
                                    name="organization_name"
                                    value="{{ old('organization_name') }}"
                                    maxlength="50"
                                    required>

                                @error('organization_name')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            {{-- Database Name --}}
                            <div class="mb-3">

                                <label
                                    for="database_name"
                                    class="form-label">

                                    Database Name
                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    type="text"
                                    class="form-control @error('database_name') is-invalid @enderror"
                                    id="database_name"
                                    name="database_name"
                                    value="{{ old('database_name') }}"
                                    maxlength="30"
                                    placeholder="Example: adc_sunrise"
                                    required>

                                <div class="form-text">

                                    Internal use only. Letters, numbers and underscore only.

                                </div>

                                @error('database_name')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            {{-- Media Folder --}}
                            <div class="mb-3">

                                <label
                                    for="media_folder"
                                    class="form-label">

                                    Media Folder

                                </label>

                                <input
                                    type="text"
                                    class="form-control @error('media_folder') is-invalid @enderror"
                                    id="media_folder"
                                    name="media_folder"
                                    value="{{ old('media_folder') }}"
                                    maxlength="50"
                                    placeholder="Example: sunrise">

                                <div class="form-text">

                                    Optional tenant-specific media folder.

                                </div>

                                @error('media_folder')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            {{-- Shift --}}
                            <div class="mb-4">

                                <label
                                    for="has_shift"
                                    class="form-label">

                                    Uses Morning / Noon Shift?

                                </label>

                                <select
                                    class="form-select @error('has_shift') is-invalid @enderror"
                                    id="has_shift"
                                    name="has_shift"
                                    required>

                                    <option
                                        value="1"
                                        {{ old('has_shift', '1') == '1' ? 'selected' : '' }}>

                                        Yes

                                    </option>

                                    <option
                                        value="0"
                                        {{ old('has_shift') == '0' ? 'selected' : '' }}>

                                        No

                                    </option>

                                </select>

                                @error('has_shift')

                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            <div class="alert alert-info">

                                <i class="mdi mdi-information-outline me-1"></i>

                                Creating the organization does not create its database yet.
                                The organization will remain
                                <strong>Not Ready</strong>
                                until database provisioning is completed.

                            </div>


                            <div class="d-flex gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary">

                                    <i class="mdi mdi-content-save me-1"></i>

                                    Create Organization

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