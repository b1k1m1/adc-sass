@extends('admin.admin_dashboard')

@section('title', 'Provision Organization')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        <div class="row mb-3">

            <div class="col-12">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h4 class="mb-1">
                            Provision Organization
                        </h4>

                        <p class="text-muted mb-0">
                            Create the tenant database from the ADC SaaS template.
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


        @if (session('error'))

            <div class="alert alert-danger">
                {{ session('error') }}
            </div>

        @endif


        <div class="row">

            <div class="col-xl-7 col-lg-9">

                <div class="card">

                    <div class="card-header">

                        <h5 class="card-title mb-0">
                            Confirm Provisioning
                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="alert alert-warning">

                            <i class="mdi mdi-alert-outline me-1"></i>

                            This action will create a new tenant database and
                            load the approved ADC SaaS template.

                        </div>


                        <table class="table table-bordered">

                            <tr>

                                <th style="width:220px;">
                                    Organization
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
                                    Current Status
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

                        </table>


                        <div class="mt-4">

                            <form
                                method="POST"
                                action="{{ route(
                                    'saas.organizations.provision',
                                    $organization->id
                                ) }}"
                                id="provisionForm">

                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-danger"
                                    id="provisionBtn">

                                    <i class="mdi mdi-database-plus me-1"></i>

                                    Provision Database

                                </button>

                                <a
                                    href="{{ route('saas.organizations.index') }}"
                                    class="btn btn-light ms-2">

                                    Cancel

                                </a>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@section('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('provisionForm');
    const button = document.getElementById('provisionBtn');

    if (!form || !button) {
        return;
    }

    form.addEventListener('submit', function () {

        button.disabled = true;

        button.innerHTML = `
            <span
                class="spinner-border spinner-border-sm me-1"
                role="status">
            </span>
            Provisioning...
        `;

    });

});

</script>

@endsection