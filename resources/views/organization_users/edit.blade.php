@extends('admin.admin_dashboard')

@section('title', 'Edit Organization User')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        <div class="row mb-3">

            <div class="col-12">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h4 class="mb-1">
                            Edit Organization User
                        </h4>

                        <p class="text-muted mb-0">

                            {{ session('organization_name') }}

                        </p>

                    </div>


                    <a
                        href="{{ route('organization.users.index') }}"
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
                            User Information
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
                            action="{{ route('organization.users.update', $user->id) }}">

                            @csrf
                            @method('PUT')


                            {{-- User Code --}}
                            <div class="mb-3">

                                <label class="form-label">
                                    User Code
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $user->user_cd }}"
                                    readonly>

                            </div>


                            <div class="row">

                                {{-- First Name --}}
                                <div class="col-md-6">

                                    <div class="mb-3">

                                        <label class="form-label">
                                            First Name
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="firstname"
                                            class="form-control"
                                            value="{{ old('firstname', $user->firstname) }}"
                                            maxlength="45"
                                            required>

                                    </div>

                                </div>


                                {{-- Last Name --}}
                                <div class="col-md-6">

                                    <div class="mb-3">

                                        <label class="form-label">
                                            Last Name
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="lastname"
                                            class="form-control"
                                            value="{{ old('lastname', $user->lastname) }}"
                                            maxlength="45"
                                            required>

                                    </div>

                                </div>

                            </div>


                            {{-- Display Name --}}
                            <div class="mb-3">

                                <label class="form-label">
                                    Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    class="form-control"
                                    value="{{ old('name', $user->name) }}"
                                    required>

                            </div>


                            {{-- Username --}}
                            <div class="mb-3">

                                <label class="form-label">
                                    Username
                                </label>

                                <input
                                    type="text"
                                    name="username"
                                    class="form-control"
                                    value="{{ old('username', $user->username) }}">

                            </div>


                            {{-- Email --}}
                            <div class="mb-3">

                                <label class="form-label">

                                    Email

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="{{ old('email', $user->email) }}"
                                    required>

                            </div>


                            {{-- Phone --}}
                            <div class="mb-3">

                                <label class="form-label">
                                    Phone
                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    class="form-control"
                                    value="{{ old('phone', $user->phone) }}">

                            </div>


                            {{-- Role --}}
                            <div class="mb-3">

                                <label class="form-label">

                                    Role

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>

                                <select
                                    name="role"
                                    class="form-select"
                                    required>

                                    <option value="">
                                        Select Role
                                    </option>

                                    @foreach ($roles as $role)

                                        <option
                                            value="{{ $role->id }}"
                                            {{ (string) old('role', $user->role) === (string) $role->id ? 'selected' : '' }}>

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

                                <select
                                    name="status"
                                    class="form-select">

                                    <option
                                        value="Active"
                                        {{ old('status', $user->status) === 'Active' ? 'selected' : '' }}>

                                        Active

                                    </option>


                                    <option
                                        value="Inactive"
                                        {{ old('status', $user->status) === 'Inactive' ? 'selected' : '' }}>

                                        Inactive

                                    </option>

                                </select>

                            </div>


                            <div class="alert alert-info">

                                <i class="mdi mdi-information-outline me-1"></i>

                                This user belongs to

                                <strong>
                                    {{ session('organization_name') }}
                                </strong>.

                                Organization assignment cannot be changed
                                from this screen.

                            </div>


                            <div class="d-flex gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary">

                                    <i class="mdi mdi-content-save me-1"></i>

                                    Update User

                                </button>


                                <a
                                    href="{{ route('organization.users.password.edit', $user->id) }}"
                                    class="btn btn-outline-secondary">

                                    <i class="mdi mdi-lock-reset me-1"></i>

                                    Change Password

                                </a>


                                <a
                                    href="{{ route('organization.users.index') }}"
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