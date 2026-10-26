@extends('admin.admin_dashboard')

@section('title', 'Organization Details')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        {{-- Header --}}
        <div class="row mb-3">

            <div class="col-12">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h4 class="mb-1">
                            {{ $organization->organization_name }}
                        </h4>

                        <p class="text-muted mb-0">
                            Organization Details
                        </p>

                    </div>


                    <div class="d-flex gap-2">

                        <a
                            href="{{ route('saas.organizations.edit', $organization->id) }}"
                            class="btn btn-primary">

                            <i class="mdi mdi-pencil-outline me-1"></i>

                            Edit Organization

                        </a>


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


        {{-- Summary Cards --}}
        <div class="row">

            <div class="col-xl-3 col-md-6">

                <div class="card">

                    <div class="card-body">

                        <div class="d-flex align-items-center">

                            <div class="flex-grow-1">

                                <p class="text-muted mb-1">
                                    Total Users
                                </p>

                                <h4 class="mb-0">
                                    {{ $totalUsers }}
                                </h4>

                            </div>

                            <div class="avatar-sm">

                                <span class="avatar-title bg-primary-subtle text-primary rounded">

                                    <i class="mdi mdi-account-group font-size-22"></i>

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-xl-3 col-md-6">

                <div class="card">

                    <div class="card-body">

                        <div class="d-flex align-items-center">

                            <div class="flex-grow-1">

                                <p class="text-muted mb-1">
                                    Active Users
                                </p>

                                <h4 class="mb-0">
                                    {{ $activeUsers }}
                                </h4>

                            </div>

                            <div class="avatar-sm">

                                <span class="avatar-title bg-success-subtle text-success rounded">

                                    <i class="mdi mdi-account-check font-size-22"></i>

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-xl-3 col-md-6">

                <div class="card">

                    <div class="card-body">

                        <div class="d-flex align-items-center">

                            <div class="flex-grow-1">

                                <p class="text-muted mb-1">
                                    Inactive Users
                                </p>

                                <h4 class="mb-0">
                                    {{ $inactiveUsers }}
                                </h4>

                            </div>

                            <div class="avatar-sm">

                                <span class="avatar-title bg-danger-subtle text-danger rounded">

                                    <i class="mdi mdi-account-off font-size-22"></i>

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-xl-3 col-md-6">

                <div class="card">

                    <div class="card-body">

                        <div class="d-flex align-items-center">

                            <div class="flex-grow-1">

                                <p class="text-muted mb-1">
                                    Platform Admins
                                </p>

                                <h4 class="mb-0">
                                    {{ $platformAdmins }}
                                </h4>

                            </div>

                            <div class="avatar-sm">

                                <span class="avatar-title bg-warning-subtle text-warning rounded">

                                    <i class="mdi mdi-shield-account font-size-22"></i>

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Organization Information --}}
        <div class="row">

            <div class="col-xl-6">

                <div class="card">

                    <div class="card-header">

                        <h5 class="card-title mb-0">
                            Organization Information
                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-borderless mb-0">

                                <tbody>

                                    <tr>

                                        <th style="width:210px;">
                                            Organization ID
                                        </th>

                                        <td>
                                            {{ $organization->id }}
                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Organization Name
                                        </th>

                                        <td>
                                            {{ $organization->organization_name }}
                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Database
                                        </th>

                                        <td>
                                            <code>
                                                {{ $organization->database_name }}
                                            </code>
                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Media Folder
                                        </th>

                                        <td>
                                            {{ $organization->media_folder ?: '—' }}
                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Shift Support
                                        </th>

                                        <td>

                                            @if ((int) $organization->has_shift === 1)

                                                <span class="badge bg-info">
                                                    Enabled
                                                </span>

                                            @else

                                                <span class="badge bg-secondary">
                                                    Disabled
                                                </span>

                                            @endif

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-xl-6">

                <div class="card">

                    <div class="card-header">

                        <h5 class="card-title mb-0">
                            Organization Status
                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-borderless mb-0">

                                <tbody>

                                    <tr>

                                        <th style="width:210px;">
                                            Status
                                        </th>

                                        <td>

                                            @if ((int) $organization->status === 1)

                                                <span class="badge bg-success">
                                                    Active
                                                </span>

                                            @else

                                                <span class="badge bg-danger">
                                                    Inactive
                                                </span>

                                            @endif

                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Database Readiness
                                        </th>

                                        <td>

                                            @if ((int) $organization->is_ready === 1)

                                                <span class="badge bg-success">
                                                    Ready
                                                </span>

                                            @else

                                                <span class="badge bg-warning text-dark">
                                                    Not Ready
                                                </span>

                                            @endif

                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Users
                                        </th>

                                        <td>
                                            {{ $totalUsers }}
                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Active Users
                                        </th>

                                        <td>
                                            {{ $activeUsers }}
                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Inactive Users
                                        </th>

                                        <td>
                                            {{ $inactiveUsers }}
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Users --}}
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <h5 class="card-title mb-0">
                    Organization Users
                </h5>

                <a
                    href="{{ route('saas.users.index', ['organization_id' => $organization->id]) }}"
                    class="btn btn-sm btn-outline-primary">

                    <i class="mdi mdi-account-group me-1"></i>

                    Manage SaaS Users

                </a>

            </div>


            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>
                                    User Code
                                </th>

                                <th>
                                    Name
                                </th>

                                <th>
                                    Email / Username
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

                            </tr>

                        </thead>


                        <tbody>

                            @forelse ($users as $user)

                                <tr>

                                    <td>
                                        {{ $user->user_cd }}
                                    </td>


                                    <td>

                                        <div class="fw-semibold">

                                            {{ $user->firstname }}
                                            {{ $user->lastname }}

                                        </div>

                                        <small class="text-muted">

                                            {{ $user->name }}

                                        </small>

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

                                            <span class="badge bg-warning text-dark">
                                                Yes
                                            </span>

                                        @else

                                            <span class="badge bg-secondary">
                                                No
                                            </span>

                                        @endif

                                    </td>

                                </tr>


                            @empty

                                <tr>

                                    <td
                                        colspan="6"
                                        class="text-center text-muted py-4">

                                        No users found for this organization.

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