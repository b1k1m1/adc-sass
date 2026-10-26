@extends('admin.admin_dashboard')

@section('title', 'SaaS Users')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        {{-- Header --}}
        <div class="row mb-3">

            <div class="col-12">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h4 class="mb-1">
                            SaaS User Management
                        </h4>

                        <p class="text-muted mb-0">
                            Manage users across ADC SaaS organizations.
                        </p>

                    </div>

                    <a
                        href="{{ route('saas.users.create') }}"
                        class="btn btn-primary">

                        <i class="mdi mdi-account-plus me-1"></i>

                        Add User

                    </a>

                </div>

            </div>

        </div>


        {{-- Success --}}
        @if (session('success'))

            <div class="alert alert-success alert-dismissible fade show">

                {{ session('success') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
                </button>

            </div>

        @endif


        {{-- Summary --}}
        <div class="row">

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">

                        <div class="text-muted mb-1">
                            Total Users
                        </div>

                        <h4>
                            {{ $summary['total'] }}
                        </h4>

                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">

                        <div class="text-muted mb-1">
                            Active
                        </div>

                        <h4>
                            {{ $summary['active'] }}
                        </h4>

                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">

                        <div class="text-muted mb-1">
                            Inactive
                        </div>

                        <h4>
                            {{ $summary['inactive'] }}
                        </h4>

                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">

                        <div class="text-muted mb-1">
                            Platform Admins
                        </div>

                        <h4>
                            {{ $summary['platform_admins'] }}
                        </h4>

                    </div>
                </div>
            </div>

        </div>


        {{-- Filter --}}
        <div class="card">

            <div class="card-body">

                <form
                    method="GET"
                    action="{{ route('saas.users.index') }}"
                    class="row g-2 align-items-end">

                    <div class="col-md-5">

                        <label class="form-label">
                            Organization
                        </label>

                        <select
                            name="organization_id"
                            class="form-select">

                            <option value="">
                                All Organizations
                            </option>

                            @foreach ($organizations as $organization)

                                <option
                                    value="{{ $organization->id }}"
                                    {{ (int) $organizationId === (int) $organization->id ? 'selected' : '' }}>

                                    {{ $organization->organization_name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-auto">

                        <button
                            type="submit"
                            class="btn btn-primary">

                            Filter

                        </button>

                        <a
                            href="{{ route('saas.users.index') }}"
                            class="btn btn-light">

                            Clear

                        </a>

                    </div>

                </form>

            </div>

        </div>


        {{-- User Table --}}
        <div class="card">

            <div class="card-header">

                <h5 class="card-title mb-0">
                    Users
                </h5>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light">

                            <tr>

                                <th>
                                    Name
                                </th>

                                <th>
                                    Organization
                                </th>

                                <th>
                                    Login
                                </th>

                                <th>
                                    Role
                                </th>

                                <th class="text-center">
                                    Status
                                </th>

                                <th class="text-center">
                                    Platform Admin
                                </th>

                                <th class="text-center">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse ($users as $user)

                                <tr>

                                    <td>

                                        <div class="fw-semibold">
                                            {{ $user->name }}
                                        </div>

                                        @if ($user->phone)

                                            <small class="text-muted">
                                                {{ $user->phone }}
                                            </small>

                                        @endif

                                    </td>

                                    <td>
                                        {{ $user->organization_name }}
                                    </td>

                                    <td>

                                        <div>
                                            {{ $user->email }}
                                        </div>

                                        @if ($user->username)

                                            <small class="text-muted">
                                                {{ $user->username }}
                                            </small>

                                        @endif

                                    </td>

                                    <td>
                                        {{ $user->role_name }}
                                    </td>

                                    <td class="text-center">

                                        @if ($user->status === 'Active')

                                            <span class="badge bg-success">
                                                Active
                                            </span>

                                        @else

                                            <span class="badge bg-danger">
                                                Inactive
                                            </span>

                                        @endif

                                    </td>

                                    <td class="text-center">

                                        @if ((int) $user->is_platform_admin === 1)

                                            <span class="badge bg-primary">
                                                Yes
                                            </span>

                                        @else

                                            <span class="text-muted">
                                                —
                                            </span>

                                        @endif

                                    </td>

                                    <td class="text-center">

                                        {{-- Enable in next stage --}}
                                        <a href="{{ route('saas.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary">

                                            <i class="mdi mdi-pencil-outline"></i>

                                            Edit

                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="7"
                                        class="text-center text-muted py-4">

                                        No users found.

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

@endsection

@section('scripts')

@endsection