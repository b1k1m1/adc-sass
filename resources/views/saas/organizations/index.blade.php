@extends('admin.admin_dashboard')

@section('title', 'SaaS Organizations')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

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

        @if (session('error'))

            <div class="alert alert-danger alert-dismissible fade show">

                {{ session('error') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
                </button>

            </div>

        @endif

        {{-- ================================================================
             Page Header
        ================================================================= --}}

        <div class="row mb-3">

            <div class="col-12">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h4 class="mb-1">
                            SaaS Organization Management
                        </h4>

                        <p class="text-muted mb-0">
                            Manage organizations registered with the ADC SaaS platform.
                        </p>

                    </div>

                    <div>

                        <span class="badge bg-primary fs-6">
                            Platform Administration
                        </span>

                    </div>

                </div>

            </div>

        </div>


        {{-- ================================================================
             Summary Cards
        ================================================================= --}}

        <div class="row">

            {{-- Total Organizations --}}
            <div class="col-xl-3 col-md-6">

                <div class="card">

                    <div class="card-body">

                        <div class="d-flex align-items-center">

                            <div class="flex-grow-1">

                                <p class="text-muted mb-2">
                                    Organizations
                                </p>

                                <h4 class="mb-0">
                                    {{ $summary['total_organizations'] }}
                                </h4>

                            </div>

                            <div class="flex-shrink-0">

                                <div class="avatar-sm">

                                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-4">

                                        <i class="mdi mdi-domain"></i>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Active Organizations --}}
            <div class="col-xl-3 col-md-6">

                <div class="card">

                    <div class="card-body">

                        <div class="d-flex align-items-center">

                            <div class="flex-grow-1">

                                <p class="text-muted mb-2">
                                    Active Organizations
                                </p>

                                <h4 class="mb-0">
                                    {{ $summary['active_organizations'] }}
                                </h4>

                            </div>

                            <div class="flex-shrink-0">

                                <div class="avatar-sm">

                                    <span class="avatar-title bg-success-subtle text-success rounded-circle fs-4">

                                        <i class="mdi mdi-check-circle-outline"></i>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Ready Organizations --}}
            <div class="col-xl-3 col-md-6">

                <div class="card">

                    <div class="card-body">

                        <div class="d-flex align-items-center">

                            <div class="flex-grow-1">

                                <p class="text-muted mb-2">
                                    Ready
                                </p>

                                <h4 class="mb-0">
                                    {{ $summary['ready_organizations'] }}
                                </h4>

                            </div>

                            <div class="flex-shrink-0">

                                <div class="avatar-sm">

                                    <span class="avatar-title bg-info-subtle text-info rounded-circle fs-4">

                                        <i class="mdi mdi-server-network"></i>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- SaaS Users --}}
            <div class="col-xl-3 col-md-6">

                <div class="card">

                    <div class="card-body">

                        <div class="d-flex align-items-center">

                            <div class="flex-grow-1">

                                <p class="text-muted mb-2">
                                    SaaS Users
                                </p>

                                <h4 class="mb-0">

                                    {{ $summary['active_users'] }}

                                    <small class="text-muted fs-6">
                                        / {{ $summary['total_users'] }}
                                    </small>

                                </h4>

                            </div>

                            <div class="flex-shrink-0">

                                <div class="avatar-sm">

                                    <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-4">

                                        <i class="mdi mdi-account-group-outline"></i>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ================================================================
             Organization Table
        ================================================================= --}}

        <div class="row">

            <div class="col-12">

                <div class="card">

                    <div class="card-header">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <h5 class="card-title mb-0">
                                    Organizations
                                </h5>

                            </div>

                            <div>

                                <a href="{{ route('saas.organizations.create') }}"  class="btn btn-primary">

                                    <i class="mdi mdi-plus me-1"></i>

                                    Add Organization

                                </a>

                            </div>

                        </div>

                    </div>


                    <div class="card-body">

                        <div class="table-responsive">

                            <table
                                class="table table-hover table-bordered align-middle mb-0">

                                <thead class="table-light">

                                    <tr>

                                        <th style="width:70px;">
                                            ID
                                        </th>

                                        <th>
                                            Organization
                                        </th>

                                        <th class="text-center" style="width:120px;">
                                            Status
                                        </th>

                                        <th class="text-center" style="width:120px;">
                                            Ready
                                        </th>

                                        <th class="text-center" style="width:120px;">
                                            Shift
                                        </th>

                                        <th class="text-center" style="width:120px;">
                                            Users
                                        </th>

                                        <th class="text-center" style="width:130px;">
                                            Active Users
                                        </th>

                                        <th class="text-center" style="width:130px;">
                                            Actions
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @forelse ($organizations as $organization)

                                        <tr>

                                            {{-- ID --}}
                                            <td>
                                                {{ $organization->id }}
                                            </td>


                                            {{-- Organization --}}
                                            <td>

                                                <div class="fw-semibold">

                                                    {{ $organization->organization_name }}

                                                </div>

                                                @if (!empty($organization->media_folder))

                                                    <small class="text-muted">

                                                        Media:
                                                        {{ $organization->media_folder }}

                                                    </small>

                                                @endif

                                            </td>


                                            {{-- Status --}}
                                            <td class="text-center">

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


                                            {{-- Ready --}}
                                            <td class="text-center">

                                                @if ((int) $organization->is_ready === 1)

                                                    <span class="badge bg-success-subtle text-success">
                                                        Ready
                                                    </span>

                                                @else

                                                    <span class="badge bg-warning-subtle text-warning">
                                                        Not Ready
                                                    </span>

                                                @endif

                                            </td>


                                            {{-- Shift --}}
                                            <td class="text-center">

                                                @if ((int) $organization->has_shift === 1)

                                                    <span class="badge bg-info-subtle text-info">
                                                        Yes
                                                    </span>

                                                @else

                                                    <span class="badge bg-secondary-subtle text-secondary">
                                                        No
                                                    </span>

                                                @endif

                                            </td>


                                            {{-- Total Users --}}
                                            <td class="text-center">

                                                <span class="fw-semibold">

                                                    {{ $organization->total_users }}

                                                </span>

                                            </td>


                                            {{-- Active Users --}}
                                            <td class="text-center">

                                                <span class="fw-semibold">

                                                    {{ $organization->active_users }}

                                                </span>

                                            </td>
                                            {{-- Actions --}}
                                            <td class="text-center">

                                                <div class="d-flex justify-content-center gap-1">

                                                    {{-- View --}}
                                                    <a
                                                        href="{{ route('saas.organizations.show', $organization->id) }}"
                                                        class="btn btn-sm btn-outline-secondary">

                                                        <i class="mdi mdi-eye-outline me-1"></i>

                                                        View

                                                    </a>

                                                    {{-- Edit Organization --}}
                                                    <a
                                                        href="{{ route('saas.organizations.edit', $organization->id) }}"
                                                        class="btn btn-sm btn-outline-primary">

                                                        <i class="mdi mdi-pencil-outline me-1"></i>

                                                        Edit

                                                    </a>


                                                    {{-- Provision Database --}}
                                                    @if ((int) $organization->is_ready === 0)

                                                        <a
                                                            href="{{ route(
                                                                'saas.organizations.provision.confirm',
                                                                $organization->id
                                                            ) }}"
                                                            class="btn btn-sm btn-warning">

                                                            <i class="mdi mdi-database-plus me-1"></i>

                                                            Provision

                                                        </a>

                                                    @else

                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-success"
                                                            disabled>

                                                            <i class="mdi mdi-check-circle-outline me-1"></i>

                                                            Ready

                                                        </button>

                                                    @endif

                                                </div>

                                            </td>                                            

                                        </tr>

                                    @empty

                                        <tr>

                                            <td
                                                colspan="8"
                                                class="text-center text-muted py-4">

                                                No organizations found.

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

    </div>

</div>

@endsection


@section('scripts')

@endsection