@extends('admin.admin_dashboard')

@section('title', 'Change User Password')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        <div class="row mb-3">

            <div class="col-12">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h4 class="mb-1">
                            Change User Password
                        </h4>

                        <p class="text-muted mb-0">

                            {{ $user->firstname }}
                            {{ $user->lastname }}

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

            <div class="col-xl-6 col-lg-8">

                <div class="card">

                    <div class="card-header">

                        <h5 class="card-title mb-0">
                            Reset Password
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


                        <div class="mb-4">

                            <div class="text-muted small">
                                User
                            </div>

                            <div class="fw-semibold">

                                {{ $user->firstname }}
                                {{ $user->lastname }}

                            </div>

                            <div class="text-muted">

                                {{ $user->email }}

                            </div>

                        </div>


                        <form
                            method="POST"
                            action="{{ route('organization.users.password.update', $user->id) }}">

                            @csrf
                            @method('PUT')


                            <div class="mb-3">

                                <label class="form-label">

                                    New Password

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    autocomplete="new-password"
                                    required>

                                <div class="form-text">
                                    Minimum 8 characters.
                                </div>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">

                                    Confirm New Password

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="password"
                                    name="password_confirmation"
                                    class="form-control"
                                    autocomplete="new-password"
                                    required>

                            </div>


                            <div class="alert alert-warning">

                                <i class="mdi mdi-alert-outline me-1"></i>

                                The user's existing password will be replaced.

                            </div>


                            <div class="d-flex gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary">

                                    <i class="mdi mdi-lock-reset me-1"></i>

                                    Change Password

                                </button>


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