@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <style>
            .text-end {
                text-align: right;
            }

            #checks-table td:last-child {
                white-space: nowrap;
            }

            #checks-table td:last-child .btn {
                margin-right: 4px;
            }
        </style>

        <!-- Start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Checks List
                    </h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('accounts.dashboard') }}#profile1"
                               class="btn btn-primary waves-effect waves-light">
                                {{ t('common.back', 'Back') }}
                            </a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- End page title -->

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-body">

                        <table id="checks-table"
                               class="table table-bordered dt-responsive nowrap w-100">

                            <thead>
                                <tr>
                                    <th>Sr#</th>
                                    <th>Transaction #</th>
                                    <th>Transaction Date</th>
                                    <th>PayTo Name</th>
                                    <th>Check #</th>
                                    <th class="text-end">Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                            </tbody>

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

    $('#checks-table').DataTable({
        processing: true,
        serverSide: true,

        ajax: '{{ route('print.checklist.data') }}',

        pageLength: 100,

        lengthMenu: [
            [100, 200, 300, 500, -1],
            [100, 200, 300, 500, 'All']
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
                searchable: false,
                className: 'text-nowrap'
            }
        ],

        order: [
            [1, 'desc']
        ]
    });

});
</script>
@endsection
