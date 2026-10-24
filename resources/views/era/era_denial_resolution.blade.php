@extends('admin.admin_dashboard')

@section('title', 'ERA Denial Resolution')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- ============================================================
             PAGE HEADER
        ============================================================ --}}
        <div class="row mb-3">
            <div class="col-12">

                <div class="page-title-box d-flex align-items-center justify-content-between">

                    <h4 class="mb-0">
                        ERA Denial Resolution
                    </h4>

                </div>

            </div>
        </div>


        {{-- ============================================================
             SUCCESS / ERROR MESSAGES
        ============================================================ --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
                </button>
            </div>
        @endif


        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
                </button>
            </div>
        @endif


        @if($errors->any())
            <div class="alert alert-danger">

                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>
        @endif


        {{-- ============================================================
             MAIN CARD
        ============================================================ --}}
        <div class="card">

            <div class="card-body">

                <form
                    id="resolveForm"
                    method="POST"
                    action="{{ route('era.denial.resolve') }}"
                >

                    @csrf


                    {{-- ====================================================
                         ACTION BUTTONS
                    ==================================================== --}}
                    <div class="row mb-3">

                        <div class="col-md-8">

                            <button
                                type="button"
                                id="selectAllBtn"
                                class="btn btn-sm btn-outline-primary me-2"
                            >
                                Select All
                            </button>


                            <button
                                type="button"
                                id="clearSelectionBtn"
                                class="btn btn-sm btn-outline-secondary"
                            >
                                Clear Selection
                            </button>

                        </div>


                        <div class="col-md-4 text-end">

                            <button
                                type="submit"
                                id="resolveSelectedBtn"
                                class="btn btn-sm btn-success"
                            >
                                Resolve Selected
                            </button>

                        </div>

                    </div>


                    {{-- ====================================================
                         TABLE
                    ==================================================== --}}
                    <div class="table-responsive">

                        <table
                            id="eraDenialResolutionTable"
                            class="table table-bordered table-striped table-hover align-middle w-100"
                        >

                            <thead>

                                <tr>

                                    <th class="text-center">
                                        Select
                                    </th>

                                    <th>
                                        Patient Name
                                    </th>

                                    <th>
                                        Patient Account #
                                    </th>

                                    <th>
                                        Payer Name
                                    </th>

                                    <th>
                                        Insured ID
                                    </th>

                                    <th>
                                        First DOS
                                    </th>

                                    <th>
                                        Last DOS
                                    </th>

                                    <th class="text-end">
                                        Total Charge
                                    </th>

                                    <th class="text-center">
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach($claims as $claim)

                                    @php

                                        /*
                                        |--------------------------------------------------------------------------
                                        | Build detail display
                                        |--------------------------------------------------------------------------
                                        */

                                        $detailHtml = '';


                                        /*
                                        |--------------------------------------------------------------------------
                                        | PAYMENT FOUND
                                        |--------------------------------------------------------------------------
                                        */

                                        if (!empty($claim->payment_found)) {

                                            $payerName = trim(
                                                (string) (
                                                    $claim->payment_payer_name
                                                    ?? $claim->payer_name
                                                    ?? ''
                                                )
                                            );

                                            $traceNumber = trim(
                                                (string) (
                                                    $claim->payment_trace_number
                                                    ?? ''
                                                )
                                            );

                                            $paymentAmount = (float) (
                                                $claim->payment_amount
                                                ?? 0
                                            );


                                            $detailHtml .=
                                                '<div class="era-payment-wrapper">';


                                            if ($payerName !== '') {

                                                $detailHtml .=
                                                    '<div class="era-payment-payer">'
                                                    . e($payerName)
                                                    . '</div>';
                                            }


                                            $detailHtml .=
                                                '<div class="era-payment-line">';


                                            if ($traceNumber !== '') {

                                                $detailHtml .=
                                                    'CHECK #'
                                                    . e($traceNumber)
                                                    . ' ';
                                            }


                                            $detailHtml .=
                                                'PAID $'
                                                . number_format(
                                                    $paymentAmount,
                                                    2
                                                );


                                            $detailHtml .=
                                                '</div>';


                                            $detailHtml .=
                                                '<div class="era-payment-note">'
                                                . 'Payment found in a later ERA. '
                                                . 'Ready for Claim Admin review and resolution.'
                                                . '</div>';


                                            $detailHtml .=
                                                '</div>';
                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | STILL DENIED
                                        |--------------------------------------------------------------------------
                                        */

                                        else {

                                            $denialLines = [];


                                            foreach (
                                                $claim->denials ?? []
                                                as $denial
                                            ) {

                                                $description = trim(
                                                    (string) (
                                                        $denial['description']
                                                        ?? ''
                                                    )
                                                );


                                                if ($description === '') {
                                                    continue;
                                                }


                                                $payerName = trim(
                                                    (string) (
                                                        $claim->payer_name
                                                        ?? ''
                                                    )
                                                );


                                                $line =
                                                    e($payerName)
                                                    . ' - ERA DENIAL - '
                                                    . e($description);


                                                /*
                                                |--------------------------------------------------------------------------
                                                | Add period if description does
                                                | not already end in punctuation
                                                |--------------------------------------------------------------------------
                                                */

                                                if (
                                                    !str_ends_with(
                                                        $description,
                                                        '.'
                                                    )
                                                    &&
                                                    !str_ends_with(
                                                        $description,
                                                        '!'
                                                    )
                                                    &&
                                                    !str_ends_with(
                                                        $description,
                                                        '?'
                                                    )
                                                ) {
                                                    $line .= '.';
                                                }


                                                $denialLines[] = $line;
                                            }


                                            $denialLines = array_values(
                                                array_unique($denialLines)
                                            );


                                            if (!empty($denialLines)) {

                                                $detailHtml .=
                                                    '<div class="era-denial-wrapper">';


                                                foreach (
                                                    $denialLines
                                                    as $line
                                                ) {

                                                    $detailHtml .=
                                                        '<div class="era-denial-line">'
                                                        . $line
                                                        . '</div>';
                                                }


                                                $detailHtml .=
                                                    '</div>';
                                            }
                                        }

                                    @endphp


                                    <tr
                                        data-details="{{ base64_encode($detailHtml) }}"
                                        data-payment-found="{{ !empty($claim->payment_found) ? '1' : '0' }}"
                                    >


                                        {{-- =====================================
                                             SELECT
                                        ====================================== --}}
                                        <td class="text-center">

                                            <input
                                                type="checkbox"
                                                class="form-check-input claim-checkbox"
                                                name="claim_ids[]"
                                                value="{{ $claim->id }}"
                                            >

                                        </td>


                                        {{-- =====================================
                                             PATIENT NAME
                                        ====================================== --}}
                                        <td>

                                            <strong>
                                                {{ strtoupper($claim->patient_name ?? '') }}
                                            </strong>

                                        </td>


                                        {{-- =====================================
                                             ACCOUNT #
                                        ====================================== --}}
                                        <td>

                                            {{ $claim->patient_control_number ?? '' }}

                                        </td>


                                        {{-- =====================================
                                             PAYER
                                        ====================================== --}}
                                        <td>

                                            {{ $claim->payer_name ?? '' }}

                                        </td>


                                        {{-- =====================================
                                             INSURED ID
                                        ====================================== --}}
                                        <td>

                                            {{ $claim->insured_id ?? '' }}

                                        </td>


                                        {{-- =====================================
                                             FIRST DOS
                                        ====================================== --}}
                                        <td
                                            data-order="{{ $claim->service_from ?? '' }}"
                                        >

                                            @if(!empty($claim->service_from))

                                                {{ \Carbon\Carbon::parse(
                                                    $claim->service_from
                                                )->format('m-d-y') }}

                                            @endif

                                        </td>


                                        {{-- =====================================
                                             LAST DOS
                                        ====================================== --}}
                                        <td
                                            data-order="{{ $claim->service_to ?? '' }}"
                                        >

                                            @if(!empty($claim->service_to))

                                                {{ \Carbon\Carbon::parse(
                                                    $claim->service_to
                                                )->format('m-d-y') }}

                                            @endif

                                        </td>


                                        {{-- =====================================
                                             TOTAL CHARGE
                                        ====================================== --}}
                                        <td
                                            class="text-end"
                                            data-order="{{ $claim->total_charge ?? 0 }}"
                                        >

                                            ${{ number_format(
                                                (float) (
                                                    $claim->total_charge
                                                    ?? 0
                                                ),
                                                2
                                            ) }}

                                        </td>


                                        {{-- =====================================
                                             STATUS
                                        ====================================== --}}
                                        <td class="text-center">

                                            @if(!empty($claim->payment_found))

                                                <span class="badge bg-success">
                                                    Payment Found
                                                </span>

                                            @else

                                                <span class="badge bg-danger">
                                                    Denied
                                                </span>

                                            @endif

                                        </td>


                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </form>

            </div>

        </div>

    </div>
</div>

@endsection


@section('scripts')

<style>

    /*
    |--------------------------------------------------------------------------
    | MAIN TABLE
    |--------------------------------------------------------------------------
    */

    #eraDenialResolutionTable {
        font-size: 13px;
    }


    #eraDenialResolutionTable thead th {
        white-space: nowrap;
        vertical-align: middle;
        background-color: #d9edf7;
        color: #005b85;
        font-weight: 600;
        padding: 8px 8px;
    }


    #eraDenialResolutionTable tbody td {
        vertical-align: middle;
        padding: 7px 8px;
        white-space: nowrap;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECKBOX
    |--------------------------------------------------------------------------
    */

    .claim-checkbox {
        cursor: pointer;
        width: 17px;
        height: 17px;
    }


    /*
    |--------------------------------------------------------------------------
    | DATATABLE CHILD ROW
    |--------------------------------------------------------------------------
    */

    #eraDenialResolutionTable tr.child td {
        padding: 0 !important;
        border-top: 0 !important;
        background-color: #ffffff !important;
    }


    /*
    |--------------------------------------------------------------------------
    | DENIAL DISPLAY
    |--------------------------------------------------------------------------
    */

    .era-denial-wrapper {
        padding: 6px 10px 9px 48px;
        background-color: #ffffff;
        border-bottom: 1px solid #d9e2e8;
    }


    .era-denial-line {
        color: #dc3545;
        font-size: 13px;
        line-height: 1.55;
        font-weight: 500;
    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT DISPLAY
    |--------------------------------------------------------------------------
    */

    .era-payment-wrapper {
        padding: 7px 10px 10px 48px;
        background-color: #fff8e1;
        border-bottom: 1px solid #e6d9a7;
    }


    .era-payment-payer {
        color: #8a6400;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.5;
    }


    .era-payment-line {
        color: #b77900;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.6;
    }


    .era-payment-note {
        color: #6c757d;
        font-size: 12px;
        margin-top: 2px;
    }


    /*
    |--------------------------------------------------------------------------
    | DATATABLE CONTROLS
    |--------------------------------------------------------------------------
    */

    .dataTables_wrapper .dataTables_filter input {
        height: 32px;
        padding: 4px 8px;
    }


    .dataTables_wrapper .dataTables_length select {
        height: 32px;
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS BADGE
    |--------------------------------------------------------------------------
    */

    #eraDenialResolutionTable .badge {
        font-size: 11px;
        padding: 5px 8px;
    }

</style>


<script>

$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | DATATABLE
    |--------------------------------------------------------------------------
    */

    var table = $('#eraDenialResolutionTable').DataTable({

        pageLength: 25,

        lengthMenu: [
            [25, 50, 100, -1],
            [25, 50, 100, 'All']
        ],

        ordering: true,

        order: [
            [5, 'desc']
        ],

        autoWidth: false,

        responsive: false,

        columnDefs: [

            /*
            |--------------------------------------------------------------------------
            | Checkbox column
            |--------------------------------------------------------------------------
            */

            {
                targets: 0,
                orderable: false,
                searchable: false,
                width: '55px'
            },

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            {
                targets: 8,
                width: '110px'
            }

        ],

        language: {
            emptyTable: 'No open ERA denials found.'
        },


        /*
        |--------------------------------------------------------------------------
        | REDRAW CHILD ROWS
        |--------------------------------------------------------------------------
        |
        | Sorting / searching / paging must keep the detail row attached
        | to the correct claim.
        |
        */

        drawCallback: function () {

            var api = this.api();

            api.rows({
                page: 'current'
            }).every(function () {

                var row = this;

                var tr = $(row.node());

                var encodedDetails =
                    tr.attr('data-details');


                if (
                    encodedDetails === undefined ||
                    encodedDetails === null ||
                    encodedDetails === ''
                ) {
                    return;
                }


                try {

                    var detailHtml =
                        atob(encodedDetails);


                    if (detailHtml.trim() !== '') {

                        row.child(detailHtml).show();
                    }

                } catch (e) {

                    console.log(
                        'Unable to display ERA detail information.',
                        e
                    );
                }

            });
        }

    });


    /*
    |--------------------------------------------------------------------------
    | INITIAL DRAW
    |--------------------------------------------------------------------------
    */

    table.draw(false);


    /*
    |--------------------------------------------------------------------------
    | SELECT ALL
    |--------------------------------------------------------------------------
    |
    | Selects all currently filtered DataTable rows, not only the visible page.
    |
    */

    $('#selectAllBtn').on('click', function () {

        table
            .rows({
                search: 'applied'
            })
            .nodes()
            .to$()
            .find('.claim-checkbox')
            .prop('checked', true);

    });


    /*
    |--------------------------------------------------------------------------
    | CLEAR SELECTION
    |--------------------------------------------------------------------------
    */

    $('#clearSelectionBtn').on('click', function () {

        table
            .rows()
            .nodes()
            .to$()
            .find('.claim-checkbox')
            .prop('checked', false);

    });


    /*
    |--------------------------------------------------------------------------
    | RESOLVE SELECTED
    |--------------------------------------------------------------------------
    */

    $('#resolveForm').on('submit', function (e) {

        var selectedCount =
            $('.claim-checkbox:checked').length;


        if (selectedCount === 0) {

            e.preventDefault();

            alert(
                'Please select at least one denial to resolve.'
            );

            return false;
        }


        var confirmed = confirm(
            'Resolve and archive '
            + selectedCount
            + ' selected denial(s)?'
        );


        if (!confirmed) {

            e.preventDefault();

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Disable button after submit
        |--------------------------------------------------------------------------
        */

        $('#resolveSelectedBtn')
            .prop('disabled', true)
            .text('Resolving...');

    });

});

</script>

@endsection
