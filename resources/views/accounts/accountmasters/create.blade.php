@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Add Account Master</h4>

                    <div class="page-title-right">
                        <a href="{{ route('accountmasters.index') }}"
                           class="btn btn-light">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to List
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <form method="POST"
              action="{{ route('accountmasters.store') }}"
              autocomplete="off">

            @csrf

            @include('accounts.accountmasters.form')

        </form>

    </div>
</div>

@endsection
