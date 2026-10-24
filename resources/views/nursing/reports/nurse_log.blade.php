@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- ============================================================
             PAGE TITLE / TOOLBAR
             ============================================================ --}}

        <div class="row mb-3">

            <div class="col-12">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <div>
                        <h4 class="mb-0">Nurse Log</h4>
                    </div>

                    <div class="d-flex align-items-center gap-2">

                        {{-- Morning Shift --}}
                        <a href="{{ route('nursing.nurse.log', ['shift' => 'A']) }}"
                           class="btn {{ $shift === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">

                            Morning Shift

                        </a>


                        {{-- Noon Shift --}}
                        <a href="{{ route('nursing.nurse.log', ['shift' => 'N']) }}"
                           class="btn {{ $shift === 'N' ? 'btn-primary' : 'btn-outline-primary' }}">

                            Noon Shift

                        </a>


                        {{-- Export PDF --}}
                        <button type="button"
                                id="btnExportPdf"
                                class="btn btn-danger">

                            Export PDF

                        </button>

                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
             REPORT CARD
             ============================================================ --}}

        <div class="row">

            <div class="col-12">

                <div class="card">

                    <div class="card-body">


                        {{-- =================================================
                             REPORT HEADER
                             ================================================= --}}

                        <div class="report-header mb-3">

                            <div class="row align-items-start">


                                {{-- Company --}}
                                <div class="col-md-4">

                                    <div class="company-name">
                                        {{ $company['name'] ?? '' }}
                                    </div>

                                    @if(!empty($company['address']))
                                        <div>
                                            {{ $company['address'] }}
                                        </div>
                                    @endif

                                    <div>

                                        @if(!empty($company['city']))
                                            {{ $company['city'] }}
                                        @endif

                                        @if(!empty($company['state']))

                                            @if(!empty($company['city']))
                                                ,
                                            @endif

                                            {{ $company['state'] }}

                                        @endif

                                        @if(!empty($company['zip']))
                                            {{ $company['zip'] }}
                                        @endif

                                    </div>

                                </div>


                                {{-- Report Title --}}
                                <div class="col-md-4 text-center">

                                    <div class="report-title">
                                        Nurse Log
                                    </div>

                                    <div class="shift-title">
                                        {{ $shift === 'N' ? 'Noon Shift' : 'Morning Shift' }}
                                    </div>

                                    <div class="report-date mt-2">

                                        <strong>Date:</strong>

                                        <span class="date-line">&nbsp;</span>

                                    </div>

                                </div>


                                {{-- Phone / Fax --}}
                                <div class="col-md-4 text-end">

                                    @if(!empty($company['phone']))

                                        <div>
                                            <strong>TEL:</strong>
                                            {{ $company['phone'] }}
                                        </div>

                                    @endif

                                    @if(!empty($company['fax']))

                                        <div>
                                            <strong>FAX:</strong>
                                            {{ $company['fax'] }}
                                        </div>

                                    @endif

                                </div>

                            </div>

                        </div>



                        {{-- =================================================
                             NURSE LOG TABLE
                             ================================================= --}}

                        <div class="table-responsive">

                            <table id="nurseLogTable"
                                   class="table table-bordered table-striped align-middle w-100">

                                <thead>

                                    <tr>

                                        <th class="text-center">
                                            Nr.
                                        </th>

                                        <th class="text-center">
                                            ID
                                        </th>

                                        <th>
                                            Last
                                        </th>

                                        <th>
                                            First
                                        </th>

                                        <th class="text-center">
                                            Mi
                                        </th>

                                        <th class="text-center">
                                            T
                                        </th>

                                        <th class="text-center">
                                            P
                                        </th>

                                        <th class="text-center">
                                            R
                                        </th>

                                        <th class="text-center">
                                            BP
                                        </th>

                                        <th class="text-center">
                                            Weight
                                        </th>

                                        <th class="text-center">
                                            Blood Sugar
                                        </th>


                                        {{-- New Monitoring Columns --}}

                                        <th class="text-center monitoring-header">
                                            Monitoring<br>
                                            Blood Sugar
                                        </th>

                                        <th class="text-center monitoring-header">
                                            Monitoring<br>
                                            Blood Pressure
                                        </th>

                                        <th class="text-center monitoring-header">
                                            Monitoring<br>
                                            Weight
                                        </th>

                                        <th class="text-center monitoring-header">
                                            Monitoring<br>
                                            Others
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach($members as $member)

                                        <tr>

                                            {{-- Row Number --}}
                                            <td class="text-center">
                                                {{ $loop->iteration }}
                                            </td>


                                            {{-- Participant ID --}}
                                            <td class="text-center">
                                                {{ $member->ParticipantId }}
                                            </td>


                                            {{-- Last Name --}}
                                            <td>
                                                {{ $member->LastName }}
                                            </td>


                                            {{-- First Name --}}
                                            <td>
                                                {{ $member->FirstName }}
                                            </td>


                                            {{-- Middle Initial --}}
                                            <td class="text-center">

                                                @if(!empty($member->MiddleName))
                                                    {{ strtoupper(substr(trim($member->MiddleName), 0, 1)) }}
                                                @endif

                                            </td>


                                            {{-- Manual Nurse Entry Fields --}}
                                            <td>&nbsp;</td> {{-- T --}}
                                            <td>&nbsp;</td> {{-- P --}}
                                            <td>&nbsp;</td> {{-- R --}}
                                            <td>&nbsp;</td> {{-- BP --}}
                                            <td>&nbsp;</td> {{-- Weight --}}
                                            <td>&nbsp;</td> {{-- Blood Sugar --}}


                                            {{-- Monitoring Blood Sugar --}}
                                            <td>
                                                {{ $member->bloodsugar ?? '' }}
                                            </td>


                                            {{-- Monitoring Blood Pressure --}}
                                            <td>
                                                {{ $member->bloodpressure ?? '' }}
                                            </td>


                                            {{-- Monitoring Weight --}}
                                            <td>
                                                {{ $member->weight ?? '' }}
                                            </td>


                                            {{-- Monitoring Others --}}
                                            <td>
                                                {{ $member->others ?? '' }}
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

    </div>
</div>


{{-- ================================================================
     CSS
     ================================================================ --}}

<style>

    .company-name {
        font-size: 16px;
        font-weight: 700;
    }

    .report-title {
        font-size: 20px;
        font-weight: 700;
    }

    .shift-title {
        font-size: 14px;
        font-weight: 600;
        margin-top: 2px;
    }

    .report-header {
        font-size: 13px;
        color: #000;
    }

    .report-date {
        font-size: 13px;
    }

    .date-line {
        display: inline-block;
        width: 130px;
        border-bottom: 1px solid #000;
        margin-left: 5px;
    }


    /*
    |--------------------------------------------------------------------------
    | Nurse Table
    |--------------------------------------------------------------------------
    */

    #nurseLogTable {
        font-size: 13px;
        font-weight: 600;
    }

    #nurseLogTable thead th {
        background-color: #f1f3f5;
        color: #000;
        font-weight: 600;
        vertical-align: middle;
        white-space: nowrap;
    }

    #nurseLogTable thead th.monitoring-header {
        white-space: normal;
        line-height: 1.2;
        min-width: 105px;
    }

    #nurseLogTable tbody td {
        vertical-align: middle;
        height: 32px;
    }


    /*
    |--------------------------------------------------------------------------
    | Column Widths
    |--------------------------------------------------------------------------
    */

    #nurseLogTable th:nth-child(1),
    #nurseLogTable td:nth-child(1) {
        width: 40px;
    }

    #nurseLogTable th:nth-child(2),
    #nurseLogTable td:nth-child(2) {
        width: 55px;
    }

    #nurseLogTable th:nth-child(3),
    #nurseLogTable td:nth-child(3) {
        min-width: 110px;
    }

    #nurseLogTable th:nth-child(4),
    #nurseLogTable td:nth-child(4) {
        min-width: 100px;
    }

    #nurseLogTable th:nth-child(5),
    #nurseLogTable td:nth-child(5) {
        width: 40px;
    }

    #nurseLogTable th:nth-child(6),
    #nurseLogTable td:nth-child(6),

    #nurseLogTable th:nth-child(7),
    #nurseLogTable td:nth-child(7),

    #nurseLogTable th:nth-child(8),
    #nurseLogTable td:nth-child(8) {
        width: 45px;
    }

    #nurseLogTable th:nth-child(9),
    #nurseLogTable td:nth-child(9) {
        width: 70px;
    }

    #nurseLogTable th:nth-child(10),
    #nurseLogTable td:nth-child(10) {
        width: 70px;
    }

    #nurseLogTable th:nth-child(11),
    #nurseLogTable td:nth-child(11) {
        width: 85px;
    }

    #nurseLogTable th:nth-child(12),
    #nurseLogTable td:nth-child(12) {
        min-width: 105px;
    }

    #nurseLogTable th:nth-child(13),
    #nurseLogTable td:nth-child(13) {
        min-width: 115px;
    }

    #nurseLogTable th:nth-child(14),
    #nurseLogTable td:nth-child(14) {
        min-width: 95px;
    }

    #nurseLogTable th:nth-child(15),
    #nurseLogTable td:nth-child(15) {
        min-width: 120px;
    }


    /*
    |--------------------------------------------------------------------------
    | DataTables
    |--------------------------------------------------------------------------
    */

    div.dataTables_wrapper div.dataTables_length,
    div.dataTables_wrapper div.dataTables_filter {
        margin-bottom: 10px;
    }

    div.dataTables_wrapper div.dataTables_filter {
        text-align: right;
    }

</style>


{{-- ================================================================
     JAVASCRIPT
     ================================================================ --}}

<script>

window.addEventListener('load', function () {

    /*
    |--------------------------------------------------------------------------
    | Make Sure jQuery/DataTables Are Loaded
    |--------------------------------------------------------------------------
    */

    if (typeof window.jQuery === 'undefined') {

        console.error('Nurse Log: jQuery is not loaded.');

        return;
    }


    const $ = window.jQuery;


    if (typeof $.fn.DataTable === 'undefined') {

        console.error('Nurse Log: DataTables is not loaded.');

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Current Values
    |--------------------------------------------------------------------------
    */

    let currentSort  = @json($sort ?? 'LastName');
    let currentDir   = @json($dir ?? 'asc');
    let currentShift = @json($shift ?? 'A');


    /*
    |--------------------------------------------------------------------------
    | Sort Mapping
    |--------------------------------------------------------------------------
    */

    const sortColumns = {

        1: 'ParticipantId',
        2: 'LastName',
        3: 'FirstName'

    };


    /*
    |--------------------------------------------------------------------------
    | Initial Sort
    |--------------------------------------------------------------------------
    */

    let initialColumn = 2;


    if (currentSort === 'ParticipantId') {

        initialColumn = 1;

    } else if (currentSort === 'FirstName') {

        initialColumn = 3;

    } else {

        initialColumn = 2;

    }


    /*
    |--------------------------------------------------------------------------
    | Destroy Existing Instance
    |--------------------------------------------------------------------------
    */

    if ($.fn.DataTable.isDataTable('#nurseLogTable')) {

        $('#nurseLogTable').DataTable().destroy();

    }


    /*
    |--------------------------------------------------------------------------
    | DataTable
    |--------------------------------------------------------------------------
    */

    const table = $('#nurseLogTable').DataTable({

        processing: false,

        serverSide: false,

        responsive: false,

        autoWidth: false,

        scrollX: true,

        pageLength: 100,

        lengthMenu: [

            [50, 100, 200, 300, 500, -1],

            [50, 100, 200, 300, 500, 'All']

        ],


        /*
        |--------------------------------------------------------------------------
        | Only ID, Last and First are sortable
        |--------------------------------------------------------------------------
        */

        order: [
            [initialColumn, currentDir]
        ],


        columnDefs: [

            {
                targets: [
                    0,      // Nr.
                    4,      // Mi
                    5,      // T
                    6,      // P
                    7,      // R
                    8,      // BP
                    9,      // Weight
                    10,     // Blood Sugar
                    11,     // Monitoring Blood Sugar
                    12,     // Monitoring Blood Pressure
                    13,     // Monitoring Weight
                    14      // Monitoring Others
                ],

                orderable: false
            },

            {
                targets: [
                    1,      // ParticipantId
                    2,      // LastName
                    3       // FirstName
                ],

                orderable: true
            }

        ]

    });


    /*
    |--------------------------------------------------------------------------
    | Capture Current Sorting
    |--------------------------------------------------------------------------
    */

    table.on('order.dt', function () {

        const order = table.order();


        if (!order || order.length === 0) {
            return;
        }


        const columnIndex = parseInt(order[0][0]);

        const direction = order[0][1];


        if (sortColumns[columnIndex]) {

            currentSort = sortColumns[columnIndex];

            currentDir = direction;

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Export PDF
    |--------------------------------------------------------------------------
    */

    const exportButton = document.getElementById('btnExportPdf');


    if (exportButton) {

        exportButton.addEventListener('click', function () {

            const order = table.order();


            let sort = currentSort;

            let dir = currentDir;


            if (order && order.length > 0) {

                const columnIndex = parseInt(order[0][0]);


                if (sortColumns[columnIndex]) {

                    sort = sortColumns[columnIndex];

                    dir = order[0][1];

                }

            }


            const baseUrl =
                @json(route('nursing.nurse.log.pdf'));


            const params = new URLSearchParams({

                shift: currentShift,

                sort: sort,

                dir: dir

            });


            window.open(

                baseUrl + '?' + params.toString(),

                '_blank'

            );

        });

    }

});

</script>

@endsection