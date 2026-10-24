@extends('admin.admin_dashboard')

@push('styles')

    <style>
        #bankCategorizationTable td {
            vertical-align: middle;
        }

        #bankCategorizationTable .select2-container {
            min-width: 260px;
            width: 100% !important;
        }

        #bankCategorizationTable .remarks-input {
            min-width: 220px;
        }

        .select2-container--default .select2-selection--single {
            height: 32px;
            padding-top: 2px;
            border: 1px solid #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 30px;
        }
    </style>
@endpush

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Bank Categorization</h4>

                    <div class="page-title-right">
                        <a href="{{ url()->previous() }}" class="btn btn-primary waves-effect waves-light">
                            {{ t('common.back', 'Back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="card">
            <div class="card-body">

                <div class="row g-2 align-items-end">

                    <div class="col-md-2">
                        <label class="form-label">Bank</label>
                        <select id="bank_id" class="form-select">
                            <option value="">All Banks</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->account_id }}">
                                    {{ $bank->account_name ?? $bank->account_id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select id="trans_type" class="form-select">
                            <option value="PAYM">Payments</option>
                            <option value="DEPS">Deposits</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Date Range</label>
                        <select id="date_range" class="form-select">
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                            <option value="last_year">Last Year</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="text" id="from_date" class="form-control datepicker" autocomplete="off">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="text" id="to_date" class="form-control datepicker" autocomplete="off">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select id="status" class="form-select">
                            <option value="all">All</option>
                            <option value="uncategorized">Uncategorized</option>
                            <option value="categorized">Categorized</option>
                        </select>
                    </div>

                </div>

                <div class="row mt-3">
                    <div class="col-md-12 text-end">
                        <button type="button" id="btnLoad" class="btn btn-primary">
                            Load
                        </button>

                        <button type="button" id="btnApplyRules" class="btn btn-warning">
                            Apply Rules to Selected
                        </button>

                        <button type="button" id="btnSaveSelected" class="btn btn-success">
                            Save Selected
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- DataTable Card --}}
        <div class="card">
            <div class="card-body">

                <table id="bankCategorizationTable" class="table table-bordered table-striped nowrap w-100">
                    <thead>
                        <tr>
                            <th style="width: 30px;">
                                <input type="checkbox" id="checkAll">
                            </th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Party Code</th>
                            <th>Account Name</th>
                            <th>Remarks</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="mt-2 text-muted">
                    1) Select Bank, Type and Date Range.
                    2) Apply Rules or manually select Party Code.
                    3) Save Selected.
                </div>

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')

<script>
$(document).ready(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Date Helpers
    |--------------------------------------------------------------------------
    */
    function formatDate(date) {
        let month = String(date.getMonth() + 1).padStart(2, '0');
        let day   = String(date.getDate()).padStart(2, '0');
        let year  = date.getFullYear();

        return month + '/' + day + '/' + year;
    }

    function setDateRange() {
        let range = $('#date_range').val();
        let today = new Date();

        let fromDate = new Date(today);
        let toDate   = new Date(today);

        if (range === 'this_month') {
            fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
            toDate   = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        }

        if (range === 'last_month') {
            fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            toDate   = new Date(today.getFullYear(), today.getMonth(), 0);
        }

        if (range === 'this_year') {
            fromDate = new Date(today.getFullYear(), 0, 1);
            toDate   = new Date(today.getFullYear(), 11, 31);
        }

        if (range === 'last_year') {
            fromDate = new Date(today.getFullYear() - 1, 0, 1);
            toDate   = new Date(today.getFullYear() - 1, 11, 31);
        }

        if (range !== 'custom') {
            $('#from_date').val(formatDate(fromDate));
            $('#to_date').val(formatDate(toDate));
        }
    }

    $('#date_range').on('change', function () {
        setDateRange();

        if ($(this).val() !== 'custom') {
            table.ajax.reload();
        }
    });

    setDateRange();

    /*
    |--------------------------------------------------------------------------
    | Flatpickr / Datepicker
    |--------------------------------------------------------------------------
    | If you are already using flatpickr globally, this will initialize it.
    | If flatpickr is not loaded, the text box will still allow manual date entry.
    |--------------------------------------------------------------------------
    */
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.datepicker', {
            dateFormat: 'm/d/Y',
            allowInput: true
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Select2 for Party Code
    |--------------------------------------------------------------------------
    */
    function initPartySelect2() {
        $('.select2-party').each(function () {
            let $select = $(this);

            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }

            $select.select2({
                width: '100%',
                placeholder: 'Search Account',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: "{{ route('bank.categorization.accounts.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term || ''
                        };
                    },
                    processResults: function (data) {
                        return data;
                    },
                    cache: true
                }
            });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | DataTable
    |--------------------------------------------------------------------------
    */
    let table = $('#bankCategorizationTable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        pageLength: 50,
        lengthMenu: [[50, 100, 200, 300, -1], [50, 100, 200, 300, 'All']],
        ajax: {
            url: "{{ route('bank.categorization.data') }}",
            data: function (d) {
                d.bank_id    = $('#bank_id').val();
                d.trans_type = $('#trans_type').val();
                d.from_date  = $('#from_date').val();
                d.to_date    = $('#to_date').val();
                d.status     = $('#status').val();
            }
        },
        columns: [
            {
                data: 'select_box',
                name: 'select_box',
                orderable: false,
                searchable: false
            },
            {
                data: 'trans_dt',
                name: 'f.trans_dt'
            },
            {
                data: 'description',
                name: 'f.payto_name'
            },
            {
                data: 'debit_amt',
                name: 'f.debit_amt',
                className: 'text-end'
            },
            {
                data: 'credit_amt',
                name: 'f.credit_amt',
                className: 'text-end'
            },
            {
                data: 'party_select',
                name: 'f.party_cd',
                orderable: false,
                searchable: false
            },
            {
                data: 'account_name',
                name: 'a.party_nm'
            },
            {
                data: 'remarks_input',
                name: 'f.remarks',
                orderable: false
            },
            {
                data: 'rule_status',
                name: 'f.party_cd',
                orderable: false,
                searchable: false
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ],
        order: [[1, 'desc']],
        drawCallback: function () {
            $('#checkAll').prop('checked', false);
            initPartySelect2();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Filter Actions
    |--------------------------------------------------------------------------
    */
    $('#btnLoad').on('click', function () {
        table.ajax.reload();
    });

    $('#bank_id, #trans_type, #status').on('change', function () {
        table.ajax.reload();
    });

    $('#from_date, #to_date').on('change', function () {
        $('#date_range').val('custom');
    });

    /*
    |--------------------------------------------------------------------------
    | Check All
    |--------------------------------------------------------------------------
    */
    $('#checkAll').on('change', function () {
        $('.row-check').prop('checked', $(this).prop('checked'));
    });

    $(document).on('change', '.row-check', function () {
        let totalChecks = $('.row-check').length;
        let checkedRows = $('.row-check:checked').length;

        $('#checkAll').prop('checked', totalChecks > 0 && totalChecks === checkedRows);
    });

    /*
    |--------------------------------------------------------------------------
    | Collect Selected Rows
    |--------------------------------------------------------------------------
    */
    function collectSelectedRows() {
        let rows = [];

        $('.row-check:checked').each(function () {
            let transNo = $(this).val();

            rows.push({
                trans_no: transNo,
                party_cd: $('.party-cd[data-trans-no="' + transNo + '"]').val(),
                remarks: $('.remarks-input[data-trans-no="' + transNo + '"]').val()
            });
        });

        return rows;
    }

    function collectSelectedTransNos() {
        let transNos = [];

        $('.row-check:checked').each(function () {
            transNos.push($(this).val());
        });

        return transNos;
    }

    /*
    |--------------------------------------------------------------------------
    | Save Selected
    |--------------------------------------------------------------------------
    */
    $('#btnSaveSelected').on('click', function () {
        let rows = collectSelectedRows();

        if (rows.length === 0) {
            alert('Please select at least one row.');
            return;
        }

        $.ajax({
            url: "{{ route('bank.categorization.save.selected') }}",
            type: "POST",
            data: {
                rows: rows
            },
            success: function (response) {
                alert(response.message);
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Something went wrong while saving.');
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Save One Row
    |--------------------------------------------------------------------------
    */
    $(document).on('click', '.save-one', function () {
        let transNo = $(this).data('trans-no');

        let rows = [{
            trans_no: transNo,
            party_cd: $('.party-cd[data-trans-no="' + transNo + '"]').val(),
            remarks: $('.remarks-input[data-trans-no="' + transNo + '"]').val()
        }];

        $.ajax({
            url: "{{ route('bank.categorization.save.selected') }}",
            type: "POST",
            data: {
                rows: rows
            },
            success: function (response) {
                alert(response.message);
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Something went wrong while saving.');
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Apply Existing Bank Rules
    |--------------------------------------------------------------------------
    */
    $('#btnApplyRules').on('click', function () {
        let transNos = collectSelectedTransNos();

        if (transNos.length === 0) {
            alert('Please select at least one row.');
            return;
        }

        $.ajax({
            url: "{{ route('bank.categorization.apply.rules') }}",
            type: "POST",
            data: {
                trans_nos: transNos,
                bank_id: $('#bank_id').val(),
                trans_type: $('#trans_type').val()
            },
            success: function (response) {
                alert(response.message);
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Something went wrong while applying rules.');
            }
        });
    });

});
</script>

@endpush
