@extends('admin.admin_dashboard')

@section('title', 'Add SaaS User')

@section('admin')

    <div class="page-content">

        <div class="container-fluid">

            <div class="row mb-3">

                <div class="col-12">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h4 class="mb-1">
                                Add SaaS User
                            </h4>

                            <p class="text-muted mb-0">
                                Create a login for an ADC SaaS organization.
                            </p>

                        </div>

                        <a href="{{ route('saas.users.index') }}" class="btn btn-outline-secondary">

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
                                User Information
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


                            <form method="POST" action="{{ route('saas.users.store') }}">

                                @csrf


                                {{-- Organization --}}
                                <div class="mb-3">

                                    <label class="form-label">
                                        Organization
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select name="organization_id" class="form-select" required>

                                        <option value="">
                                            Select Organization
                                        </option>

                                        @foreach ($organizations as $organization)
                                            <option value="{{ $organization->id }}"
                                                {{ old('organization_id') == $organization->id ? 'selected' : '' }}>

                                                {{ $organization->organization_name }}

                                            </option>
                                        @endforeach

                                    </select>

                                </div>
                                <div class="row">

                                    <div class="col-md-6">

                                        <div class="mb-3">

                                            <label class="form-label">
                                                First Name
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="text" name="firstname"
                                                class="form-control @error('firstname') is-invalid @enderror"
                                                value="{{ old('firstname') }}" maxlength="45" required>

                                            @error('firstname')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror

                                        </div>

                                    </div>


                                    <div class="col-md-6">

                                        <div class="mb-3">

                                            <label class="form-label">
                                                Last Name
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="text" name="lastname"
                                                class="form-control @error('lastname') is-invalid @enderror"
                                                value="{{ old('lastname') }}" maxlength="45" required>

                                            @error('lastname')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror

                                        </div>

                                    </div>

                                </div>

                                {{-- Name --}}
                                <div class="mb-3">

                                    <label class="form-label">
                                        Name
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                        required>

                                </div>


                                {{-- Username --}}
                                <div class="mb-3">

                                    <label class="form-label">
                                        Username
                                    </label>

                                    <input type="text" name="username" class="form-control"
                                        value="{{ old('username') }}">

                                    <div class="form-text">
                                        Optional, but if entered it must be globally unique.
                                    </div>

                                </div>


                                {{-- Email --}}
                                <div class="mb-3">

                                    <label class="form-label">
                                        Email
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                        required>

                                </div>


                                {{-- Phone --}}
                                <div class="mb-3">

                                    <label class="form-label">
                                        Phone
                                    </label>

                                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">

                                    <div class="form-text">
                                        If used for login, it must be globally unique.
                                    </div>

                                </div>


                                {{-- Role --}}
                                <div class="mb-3">

                                    <label class="form-label">
                                        Role
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select name="role" class="form-select" required>

                                        <option value="">
                                            Select Role
                                        </option>

                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}"
                                                {{ old('role') == $role->id ? 'selected' : '' }}>

                                                {{ $role->name }}

                                            </option>
                                        @endforeach

                                    </select>

                                </div>


                                {{-- Status --}}
                                <div class="mb-3">

                                    <label class="form-label">
                                        Status
                                    </label>

                                    <select name="status" class="form-select">

                                        <option value="Active"
                                            {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>

                                            Active

                                        </option>

                                        <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>

                                            Inactive

                                        </option>

                                    </select>

                                </div>


                                {{-- Platform Admin --}}
                                <div class="mb-4">

                                    <div class="form-check">

                                        <input class="form-check-input" type="checkbox" name="is_platform_admin"
                                            value="1" id="is_platform_admin"
                                            {{ old('is_platform_admin') ? 'checked' : '' }}>

                                        <label class="form-check-label" for="is_platform_admin">

                                            SaaS Platform Administrator

                                        </label>

                                    </div>

                                    <div class="form-text text-warning">

                                        Platform administrators can access all SaaS
                                        organization and user management screens.

                                    </div>

                                </div>


                                {{-- Password --}}
                                <div class="row">

                                    <div class="col-md-6">

                                        <div class="mb-3">

                                            <label class="form-label">
                                                Password
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="password" name="password" class="form-control"
                                                autocomplete="new-password" required>

                                        </div>

                                    </div>


                                    <div class="col-md-6">

                                        <div class="mb-3">

                                            <label class="form-label">
                                                Confirm Password
                                                <span class="text-danger">*</span>
                                            </label>

                                            <input type="password" name="password_confirmation" class="form-control"
                                                autocomplete="new-password" required>

                                        </div>

                                    </div>

                                </div>


                                <div class="d-flex gap-2">

                                    <button type="submit" class="btn btn-primary">

                                        <i class="mdi mdi-account-plus me-1"></i>

                                        Create User

                                    </button>

                                    <a href="{{ route('saas.users.index') }}" class="btn btn-light">

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
