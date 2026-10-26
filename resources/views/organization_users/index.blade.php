@extends('admin.admin_dashboard')

@section('title', 'Organization Users')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        <div class="row mb-3">

            <div class="col-12">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h4 class="mb-1">
                            Organization Users
                        </h4>

                        <p class="text-muted mb-0">

                            Manage users for

                            <strong>
                                {{ session('organization_name') }}
                            </strong>

                        </p>

                    </div>


                    <a
                        href="{{ route('organization.users.create') }}"
                        class="btn btn-primary">

                        <i class="mdi mdi-account-plus me-1"></i>

                        Add User

                    </a>

                </div>

            </div>

        </div>


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


        <div class="card">

            <div class="card-header">

                <h5 class="card-title mb-0">
                    Users
                </h5>

            </div>


            <div class="card-body">

                <div class="table-responsive">

                    <table
                        class="table table-bordered table-hover align-middle mb-0">

                        <thead class="table-light">

                            <tr>

                                <th style="width:100px;">
                                    User Code
                                </th>

                                <th>
                                    Name
                                </th>

                                <th>
                                    Login
                                </th>

                                <th>
                                    Phone
                                </th>

                                <th>
                                    Role
                                </th>

                                <th class="text-center">
                                    Status
                                </th>

                                <th
                                    class="text-center"
                                    style="width:180px;">

                                    Action

                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse ($users as $user)

                                <tr>

                                    <td class="fw-semibold">

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

                                        {{ $user->phone ?: '—' }}

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

                                        <div class="d-flex justify-content-center gap-1">

                                            <a
                                                href="{{ route('organization.users.edit', $user->id) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Edit User">

                                                <i class="mdi mdi-pencil-outline"></i>

                                                Edit

                                            </a>


                                            <a
                                                href="{{ route('organization.users.password.edit', $user->id) }}"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="Change Password">

                                                <i class="mdi mdi-lock-reset"></i>

                                                Password

                                            </a>

                                        </div>

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