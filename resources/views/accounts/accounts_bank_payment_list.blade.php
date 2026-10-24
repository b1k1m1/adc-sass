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
        {{-- Date range filters --}}
        {{-- ========================================================= --}}

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-body py-3">

                        <div class="row g-2 align-items-end">

                            {{-- Date period dropdown --}}
                            <div class="col-xl-3 col-lg-4 col-md-5 col-sm-12">

                                <label for="date_period"
                                       class="form-label mb-1">
                                    Date Period
                                </label>

                                <select id="date_period"
                                        class="form-select">

                                    <option value="today">Today</option>
                                    <option value="this_week">This Week</option>
                                    <option value="last_week">Last Week</option>

                                    <option value="this_month" selected>
                                        This Month
                                    </option>

                                    <option value="last_month">
                                        Last Month
                                    </option>

                                    <option value="this_quarter">
                                        This Quarter
                                    </option>

                                    <option value="last_quarter">
                                        Last Quarter
                                    </option>

                                    <option value="this_year">
                                        This Year
                                    </option>

                                    <option value="last_year">
                                        Last Year
                                    </option>

                                    <option value="custom">
                                        Custom Date
                                    </option>

                                    <option value="all_dates">
                                        All Dates
                                    </option>

                                </select>

                            </div>


                            {{-- Start date --}}
                            <div class="col-xl-2 col-lg-3 col-md-3 col-sm-6">

                                <label for="start_date"
                                       class="form-label mb-1">
                                    Start Date
                                </label>

                                <input type="text"
                                       id="start_date"
                                       class="form-control"
                                       value="{{ now()->startOfMonth()->format('m/d/Y') }}"
                                       placeholder="MM/DD/YYYY"
                                       autocomplete="off"
                                       readonly>

                            </div>


                            {{-- End date --}}
                            <div class="col-xl-2 col-lg-3 col-md-3 col-sm-6">

                                <label for="end_date"
                                       class="form-label mb-1">
                                    End Date
                                </label>

                                <input type="text"
                                       id="end_date"
                                       class="form-control"
                                       value="{{ now()->endOfMonth()->format('m/d/Y') }}"
                                       placeholder="MM/DD/YYYY"
                                       autocomplete="off"
                                       readonly>

                            </div>


                            {{-- Apply button --}}
                            <div class="col-xl-auto col-lg-auto col-md-auto col-sm-12">

                                <button type="button"
                                        id="apply_date_filter"
                                        class="btn btn-primary waves-effect waves-light">

                                    <i class="mdi mdi-filter-outline me-1"></i>
                                    Apply
                                </button>

                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>


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
                                    <th>Account Head</th>
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

    /*
    |--------------------------------------------------------------------------
    | Date helper functions
    |--------------------------------------------------------------------------
    */

    function padNumber(value) {
        return String(value).padStart(2, '0');
    }

    function formatDisplayDate(date) {
        return padNumber(date.getMonth() + 1)
            + '/'
            + padNumber(date.getDate())
            + '/'
            + date.getFullYear();
    }

    function formatDatabaseDate(date) {
        return date.getFullYear()
            + '-'
            + padNumber(date.getMonth() + 1)
            + '-'
            + padNumber(date.getDate());
    }

    function parseDisplayDate(value) {
        if (!value) {
            return null;
        }

        const parts = value.split('/');

        if (parts.length !== 3) {
            return null;
        }

        return new Date(
            parseInt(parts[2], 10),
            parseInt(parts[0], 10) - 1,
            parseInt(parts[1], 10)
        );
    }

    function setDateRange(startDate, endDate) {
        $('#start_date').val(formatDisplayDate(startDate));
        $('#end_date').val(formatDisplayDate(endDate));
    }

    function enableCustomDates(enabled) {
        $('#start_date').prop('readonly', !enabled);
        $('#end_date').prop('readonly', !enabled);
    }


    /*
    |--------------------------------------------------------------------------
    | Flatpickr date inputs
    |--------------------------------------------------------------------------
    |
    | The inputs are enabled only when Custom Date is selected.
    |
    */

    const startDatePicker = flatpickr('#start_date', {
        dateFormat: 'm/d/Y',
        allowInput: true,
        clickOpens: false
    });

    const endDatePicker = flatpickr('#end_date', {
        dateFormat: 'm/d/Y',
        allowInput: true,
        clickOpens: false
    });


    /*
    |--------------------------------------------------------------------------
    | Set range based on selected period
    |--------------------------------------------------------------------------
    */

    function updateDatePeriod(period) {

        const today = new Date();

        let startDate = new Date(
            today.getFullYear(),
            today.getMonth(),
            today.getDate()
        );

        let endDate = new Date(
            today.getFullYear(),
            today.getMonth(),
            today.getDate()
        );

        enableCustomDates(false);

        startDatePicker.set('clickOpens', false);
        endDatePicker.set('clickOpens', false);

        switch (period) {

            case 'today':

                break;


            case 'this_week': {

                const dayNumber = today.getDay();

                /*
                | Monday is treated as the first day of the week.
                */

                const daysFromMonday = dayNumber === 0
                    ? 6
                    : dayNumber - 1;

                startDate.setDate(today.getDate() - daysFromMonday);

                endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);

                break;
            }


            case 'last_week': {

                const dayNumber = today.getDay();

                const daysFromMonday = dayNumber === 0
                    ? 6
                    : dayNumber - 1;

                startDate.setDate(
                    today.getDate() - daysFromMonday - 7
                );

                endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);

                break;
            }


            case 'this_month':

                startDate = new Date(
                    today.getFullYear(),
                    today.getMonth(),
                    1
                );

                endDate = new Date(
                    today.getFullYear(),
                    today.getMonth() + 1,
                    0
                );

                break;


            case 'last_month':

                startDate = new Date(
                    today.getFullYear(),
                    today.getMonth() - 1,
                    1
                );

                endDate = new Date(
                    today.getFullYear(),
                    today.getMonth(),
                    0
                );

                break;


            case 'this_quarter': {

                const quarterStartMonth =
                    Math.floor(today.getMonth() / 3) * 3;

                startDate = new Date(
                    today.getFullYear(),
                    quarterStartMonth,
                    1
                );

                endDate = new Date(
                    today.getFullYear(),
                    quarterStartMonth + 3,
                    0
                );

                break;
            }


            case 'last_quarter': {

                const currentQuarterStartMonth =
                    Math.floor(today.getMonth() / 3) * 3;

                startDate = new Date(
                    today.getFullYear(),
                    currentQuarterStartMonth - 3,
                    1
                );

                endDate = new Date(
                    today.getFullYear(),
                    currentQuarterStartMonth,
                    0
                );

                break;
            }


            case 'this_year':

                startDate = new Date(
                    today.getFullYear(),
                    0,
                    1
                );

                endDate = new Date(
                    today.getFullYear(),
                    11,
                    31
                );

                break;


            case 'last_year':

                startDate = new Date(
                    today.getFullYear() - 1,
                    0,
                    1
                );

                endDate = new Date(
                    today.getFullYear() - 1,
                    11,
                    31
                );

                break;


            case 'custom':

                enableCustomDates(true);

                startDatePicker.set('clickOpens', true);
                endDatePicker.set('clickOpens', true);

                return;


            case 'all_dates':

                $('#start_date').val('');
                $('#end_date').val('');

                return;
        }

        setDateRange(startDate, endDate);

        startDatePicker.setDate(
            formatDisplayDate(startDate),
            false
        );

        endDatePicker.setDate(
            formatDisplayDate(endDate),
            false
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Payment DataTable
    |--------------------------------------------------------------------------
    */

    const paymentTable = $('#members-table').DataTable({
        processing: true,
        serverSide: true,

        /*
        |--------------------------------------------------------------------------
        | Do not retain a previous user sort after leaving this screen
        |--------------------------------------------------------------------------
        */

        stateSave: false,

        ajax: {
            url: '{{ route("list.payment") }}',
            data: function (data) {

                const selectedPeriod = $('#date_period').val();

                data.date_period = selectedPeriod;

                if (selectedPeriod === 'all_dates') {
                    data.start_date = '';
                    data.end_date = '';
                    return;
                }

                const startDate =
                    parseDisplayDate($('#start_date').val());

                const endDate =
                    parseDisplayDate($('#end_date').val());

                data.start_date = startDate
                    ? formatDatabaseDate(startDate)
                    : '';

                data.end_date = endDate
                    ? formatDatabaseDate(endDate)
                    : '';
            }
        },

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
        | descending.
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
                data: 'account_head',
                name: 'account_head'
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


    /*
    |--------------------------------------------------------------------------
    | Date period dropdown
    |--------------------------------------------------------------------------
    */

    $('#date_period').on('change', function () {

        const selectedPeriod = $(this).val();

        updateDatePeriod(selectedPeriod);

        /*
        | Automatically refresh for predefined periods.
        | Custom Date waits until the user selects dates and clicks Apply.
        */

        if (selectedPeriod !== 'custom') {
            paymentTable.ajax.reload();
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Apply selected date range
    |--------------------------------------------------------------------------
    */

    $('#apply_date_filter').on('click', function () {

        const selectedPeriod = $('#date_period').val();

        if (selectedPeriod !== 'all_dates') {

            const startDate =
                parseDisplayDate($('#start_date').val());

            const endDate =
                parseDisplayDate($('#end_date').val());

            if (!startDate || !endDate) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Date Required',
                    text: 'Please enter both the start date and end date.'
                });

                return;
            }

            if (startDate > endDate) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Date Range',
                    text: 'The start date cannot be later than the end date.'
                });

                return;
            }
        }

        paymentTable.ajax.reload();
    });


    /*
    |--------------------------------------------------------------------------
    | Initialize with current month
    |--------------------------------------------------------------------------
    */

    updateDatePeriod('this_month');

});
</script>

@endsection
