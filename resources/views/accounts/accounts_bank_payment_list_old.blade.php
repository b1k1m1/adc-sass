@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- ========================================================= --}}
        {{-- Page title --}}
        {{-- ========================================================= --}}

        <div class="row">
            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Payment List
                    </h4>

                    <div class="page-title-right">
                        <a href="{{ route('accounts.dashboard') }}"
                           class="btn btn-primary waves-effect waves-light">

                            {{ t('common.back', 'Back') }}
                        </a>
                    </div>

                </div>

            </div>
        </div>


        {{-- ========================================================= --}}
        {{-- Validation errors --}}
        {{-- ========================================================= --}}

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show"
                 role="alert">

                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>
            </div>
        @endif


        {{-- ========================================================= --}}
        {{-- Payment DataTable --}}
        {{-- ========================================================= --}}

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-body">

                        <table id="members-table"
                               class="table table-bordered dt-responsive nowrap w-100">

                            <thead>
                                <tr>
                                    <th>Sr#</th>
                                    <th>Transaction #</th>
                                    <th>Transaction Date</th>
                                    <th>Paid To</th>
                                    <th>Check #</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody></tbody>

                        </table>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection


@section('scripts')

<script>
$(document).ready(function () {

    $('#members-table').DataTable({
        processing: true,
        serverSide: true,

        /*
        |--------------------------------------------------------------------------
        | Do not retain a previous user sort after leaving this screen
        |--------------------------------------------------------------------------
        |
        | When the user returns after editing, the default newest-first order
        | will be restored.
        |
        */

        stateSave: false,

        ajax: '{{ route("list.payment") }}',

        pageLength: 100,

        lengthMenu: [
            [100, 200, 300, 500, -1],
            [100, 200, 300, 500, "All"]
        ],

        /*
        |--------------------------------------------------------------------------
        | Initial ordering only
        |--------------------------------------------------------------------------
        |
        | Transaction Date descending, followed by Transaction Number
        | descending. Users remain free to click any sortable heading.
        |
        */

        order: [
            [2, 'desc'],
            [1, 'desc']
        ],

        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'trans_no',
                name: 'trans_no'
            },
            {
                data: 'trans_dt',
                name: 'trans_dt'
            },
            {
                data: 'payto_name',
                name: 'payto_name'
            },
            {
                data: 'check_no',
                name: 'check_no'
            },
            {
                data: 'debit_amt',
                name: 'debit_amt',
                className: 'text-end'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ]
    });

});
</script>

@endsection
