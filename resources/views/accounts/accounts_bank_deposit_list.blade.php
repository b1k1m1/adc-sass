@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page title --}}
        <div class="row">
            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Deposit List
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


        {{-- Deposit listing --}}
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
                                    <th>Received From</th>
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
         * Do not remember an earlier manual sort after leaving this page.
         * Returning after an update will use the default newest-first order.
         */
        stateSave: false,

        ajax: '{{ route("list.deposit") }}',

        pageLength: 100,

        lengthMenu: [
            [100, 200, 300, 500, -1],
            [100, 200, 300, 500, "All"]
        ],

        /*
         * Initial order:
         *
         * Transaction Date descending.
         * For the same date, Transaction Number descending.
         *
         * Users can still click any sortable column afterward.
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
                data: 'credit_amt',
                name: 'credit_amt',
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
