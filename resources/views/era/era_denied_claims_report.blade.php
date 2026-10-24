@extends('admin.admin_dashboard')

@section('title', 'ERA Denied Claims Report')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- ============================================================
             PAGE TITLE
        ============================================================ --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">
                        ERA Denied Claims
                    </h4>
                </div>
            </div>
        </div>


        {{-- ============================================================
             MAIN CARD
        ============================================================ --}}
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">

                    <table
                        id="eraDeniedTable"
                        class="table table-bordered table-striped table-hover align-middle w-100"
                    >

                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Patient Account #</th>
                                <th>Payer Name</th>
                                <th>Insured ID</th>
                                <th>First Date of Service</th>
                                <th>Last Date of Service</th>
                                <th class="text-end">Total Charge</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($claims as $claim)

                                @php

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Build denial lines
                                    |--------------------------------------------------------------------------
                                    */

                                    $denialLines = [];

                                    foreach ($claim->denials ?? [] as $denial) {

                                        $description = trim(
                                            (string) ($denial['description'] ?? '')
                                        );

                                        if ($description !== '') {

                                            $payerName = trim(
                                                (string) ($claim->payer_name ?? '')
                                            );

                                            $denialLines[] =
                                                e($payerName)
                                                . ' - ERA DENIAL - '
                                                . e($description)
                                                . '.';
                                        }
                                    }

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Remove duplicate lines
                                    |--------------------------------------------------------------------------
                                    */

                                    $denialLines = array_values(
                                        array_unique($denialLines)
                                    );

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Prepare child-row HTML
                                    |--------------------------------------------------------------------------
                                    */

                                    $denialHtml = '';

                                    if (!empty($denialLines)) {

                                        $denialHtml .=
                                            '<div class="era-denial-wrapper">';

                                        foreach ($denialLines as $line) {

                                            $denialHtml .=
                                                '<div class="era-denial-line">'
                                                . $line
                                                . '</div>';
                                        }

                                        $denialHtml .= '</div>';
                                    }

                                @endphp


                                <tr
                                    data-denials="{{ base64_encode($denialHtml) }}"
                                >

                                    {{-- Patient Name --}}
                                    <td>
                                        <strong>
                                            {{ strtoupper($claim->patient_name ?? '') }}
                                        </strong>
                                    </td>


                                    {{-- Patient Account # --}}
                                    <td>
                                        {{ $claim->patient_control_number ?? '' }}
                                    </td>


                                    {{-- Payer Name --}}
                                    <td>
                                        {{ $claim->payer_name ?? '' }}
                                    </td>


                                    {{-- Insured ID --}}
                                    <td>
                                        {{ $claim->insured_id ?? '' }}
                                    </td>


                                    {{-- First DOS --}}
                                    <td
                                        data-order="{{ $claim->service_from ?? '' }}"
                                    >
                                        @if(!empty($claim->service_from))
                                            {{ \Carbon\Carbon::parse($claim->service_from)->format('m-d-y') }}
                                        @endif
                                    </td>


                                    {{-- Last DOS --}}
                                    <td
                                        data-order="{{ $claim->service_to ?? '' }}"
                                    >
                                        @if(!empty($claim->service_to))
                                            {{ \Carbon\Carbon::parse($claim->service_to)->format('m-d-y') }}
                                        @endif
                                    </td>


                                    {{-- Total Charge --}}
                                    <td
                                        class="text-end"
                                        data-order="{{ $claim->total_charge ?? 0 }}"
                                    >
                                        ${{ number_format((float) ($claim->total_charge ?? 0), 2) }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>
        </div>

    </div>
</div>

@endsection


@section('scripts')

<style>

    /*
    |--------------------------------------------------------------------------
    | Main table
    |--------------------------------------------------------------------------
    */

    #eraDeniedTable {
        font-size: 13px;
    }

    #eraDeniedTable thead th {
        white-space: nowrap;
        vertical-align: middle;
        background-color: #d9edf7;
        color: #005b85;
        font-weight: 600;
        padding: 8px 8px;
    }

    #eraDeniedTable tbody td {
        vertical-align: middle;
        padding: 7px 8px;
        white-space: nowrap;
    }


    /*
    |--------------------------------------------------------------------------
    | ERA denial child row
    |--------------------------------------------------------------------------
    */

    #eraDeniedTable tr.child td {
        padding: 0 !important;
        border-top: 0 !important;
        background-color: #ffffff !important;
    }

    .era-denial-wrapper {
        padding: 5px 8px 8px 8px;
        background-color: #ffffff;
        border-bottom: 1px solid #d9e2e8;
    }

    .era-denial-line {
        color: red;
        font-size: 13px;
        line-height: 1.55;
        font-weight: 400;
    }


    /*
    |--------------------------------------------------------------------------
    | DataTables controls
    |--------------------------------------------------------------------------
    */

    .dataTables_wrapper .dataTables_filter input {
        height: 32px;
        padding: 4px 8px;
    }

    .dataTables_wrapper .dataTables_length select {
        height: 32px;
    }

</style>


<script>

    $(document).ready(function () {

        /*
        |--------------------------------------------------------------------------
        | Initialize DataTable
        |--------------------------------------------------------------------------
        */

        var table = $('#eraDeniedTable').DataTable({

            pageLength: 25,

            lengthMenu: [
                [25, 50, 100, -1],
                [25, 50, 100, 'All']
            ],

            ordering: true,

            order: [
                [4, 'desc']
            ],

            autoWidth: false,

            responsive: false,

            language: {
                emptyTable: 'No denied ERA claims found.'
            },

            /*
            |--------------------------------------------------------------------------
            | Draw callback
            |--------------------------------------------------------------------------
            |
            | Every time DataTables sorts, searches or changes page,
            | recreate the denial child row underneath the correct claim.
            |
            */

            drawCallback: function () {

                var api = this.api();

                api.rows({
                    page: 'current'
                }).every(function () {

                    var row = this;

                    var tr = $(row.node());

                    var encodedDenials = tr.attr('data-denials');

                    if (
                        encodedDenials !== undefined &&
                        encodedDenials !== null &&
                        encodedDenials !== ''
                    ) {

                        try {

                            var denialHtml = atob(encodedDenials);

                            if (denialHtml.trim() !== '') {

                                row.child(denialHtml).show();
                            }

                        } catch (e) {

                            console.log(
                                'Unable to display ERA denial information.',
                                e
                            );
                        }
                    }

                });
            }

        });


        /*
        |--------------------------------------------------------------------------
        | Initial denial rows
        |--------------------------------------------------------------------------
        */

        table.draw(false);

    });

</script>

@endsection
