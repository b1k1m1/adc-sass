@extends('admin.admin_dashboard')

@section('title', 'Pending Rebill Claims')

@section('admin')

    <style>
        /*
                                    |--------------------------------------------------------------------------
                                    | Main Table - Compact
                                    |--------------------------------------------------------------------------
                                    */

        .pending-table {
            margin-bottom: 0 !important;
            width: max-content;
            min-width: 100%;
        }

        .pending-table th,
        .pending-table td {
            padding: .18rem .28rem !important;
            font-size: 11.5px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .pending-table th {
            font-size: 11px;
            font-weight: 600;
        }


        /*
                                    |--------------------------------------------------------------------------
                                    | Reason Column
                                    |--------------------------------------------------------------------------
                                    */

        .pending-table .reason-column {
            white-space: normal;
            min-width: 190px;
            width: 220px;
            max-width: 260px;
            line-height: 1.2;
        }


        /*
                                    |--------------------------------------------------------------------------
                                    | Buttons
                                    |--------------------------------------------------------------------------
                                    */

        .pending-table .btn {
            padding: .12rem .30rem;
            font-size: 10.5px;
            line-height: 1.3;
        }


        /*
                                    |--------------------------------------------------------------------------
                                    | Edit Fields
                                    |--------------------------------------------------------------------------
                                    */

        .pending-edit {
            font-size: 11px;
            padding: .10rem .20rem;
            height: 25px;
        }

        .pending-date {
            width: 105px;
            min-width: 105px;
        }

        .pending-proc {
            width: 62px;
            min-width: 62px;
        }

        .pending-mod {
            width: 48px;
            min-width: 48px;
        }

        .pending-units {
            width: 48px;
            min-width: 48px;
        }

        .pending-rate {
            width: 65px;
            min-width: 65px;
        }

        .pending-diag {
            width: 95px;
            min-width: 95px;
        }


        /*
                                    |--------------------------------------------------------------------------
                                    | Select2 Compact Styling
                                    |--------------------------------------------------------------------------
                                    */

        .pending-table .select2-container {
            min-width: 95px !important;
            width: 95px !important;
        }

        .pending-table .select2-container .select2-selection--single {
            height: 25px !important;
            min-height: 25px !important;
            font-size: 11px;
        }

        .pending-table .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 23px !important;
            padding-left: 4px !important;
            padding-right: 18px !important;
        }

        .pending-table .select2-container .select2-selection--single .select2-selection__arrow {
            height: 23px !important;
        }


        /*
                                    |--------------------------------------------------------------------------
                                    | Top Horizontal Scrollbar
                                    |--------------------------------------------------------------------------
                                    */

        .pending-top-scroll {
            overflow-x: auto;
            overflow-y: hidden;
            height: 17px;
            margin-bottom: 3px;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
        }

        .pending-top-scroll-inner {
            height: 1px;
        }


        /*
                                    |--------------------------------------------------------------------------
                                    | Main Table Scroll Area
                                    |--------------------------------------------------------------------------
                                    |
                                    | User scrolls vertically INSIDE this area.
                                    | Horizontal scrollbar therefore remains accessible without going
                                    | through 200-300 rows.
                                    |--------------------------------------------------------------------------
                                    */

        .pending-table-wrapper {
            position: relative;

            width: 100%;

            max-height: calc(100vh - 285px);
            min-height: 350px;

            overflow-x: auto;
            overflow-y: auto;

            border: 1px solid #dee2e6;

            background: #fff;
        }


        /*
                                    |--------------------------------------------------------------------------
                                    | Sticky Table Header
                                    |--------------------------------------------------------------------------
                                    */

        .pending-table thead th {
            position: sticky;
            top: 0;

            z-index: 20;

            background: #f1f3f5 !important;

            border-bottom: 2px solid #adb5bd !important;

            box-shadow:
                0 1px 2px rgba(0, 0, 0, .08);
        }


        /*
                                    |--------------------------------------------------------------------------
                                    | Freeze SELECT Column On Left
                                    |--------------------------------------------------------------------------
                                    */

        .pending-table th:first-child,
        .pending-table td:first-child {
            position: sticky;

            left: 0;

            min-width: 34px;
            width: 34px;
            max-width: 34px;

            text-align: center;

            background: #fff;

            z-index: 12;
        }

        .pending-table thead th:first-child {
            background: #f1f3f5 !important;
            z-index: 30;
        }


        /*
                                    |--------------------------------------------------------------------------
                                    | Freeze ACTION Column On Right
                                    |--------------------------------------------------------------------------
                                    |
                                    | This is important:
                                    | Reprocess / Archive remain visible even when user is viewing columns
                                    | on the far left side of the report.
                                    |--------------------------------------------------------------------------
                                    */

        .pending-table th:last-child,
        .pending-table td:last-child {
            position: sticky;

            right: 0;

            min-width: 125px;

            background: #fff;

            z-index: 12;

            border-left: 2px solid #ced4da !important;

            box-shadow:
                -2px 0 4px rgba(0, 0, 0, .06);
        }

        .pending-table thead th:last-child {
            background: #f1f3f5 !important;
            z-index: 30;
        }


        /*
                                    |--------------------------------------------------------------------------
                                    | Pending Row Background
                                    |--------------------------------------------------------------------------
                                    */

        .pending-table tr.table-warning td:first-child,
        .pending-table tr.table-warning td:last-child {
            background: #fff3cd !important;
        }


        /*
                                    |--------------------------------------------------------------------------
                                    | Compact Card / Header Area
                                    |--------------------------------------------------------------------------
                                    */

        .pending-compact-summary .card-body {
            padding: .45rem .70rem;
        }

        .pending-compact-summary h4 {
            font-size: 17px;
            margin-bottom: 0 !important;
        }

        .pending-compact-summary span {
            font-size: 11px;
        }
    </style>

    <div class="page-content">

        <div class="container-fluid">

            {{-- HEADER --}}

            <div class="d-flex justify-content-between align-items-center mb-2">

                <h4 class="mb-0">
                    Pending Rebill Claims
                </h4>

                <a href="{{ route('claims.dashboard') }}" class="btn btn-secondary btn-sm">

                    Back

                </a>

            </div>


            {{-- MESSAGE --}}

            @if (session('message'))
                <div class="alert alert-{{ session('alert-type') == 'error' ? 'danger' : session('alert-type') }}">

                    {{ session('message') }}

                </div>
            @endif


            {{-- SUMMARY --}}

            <div class="row mb-2 pending-compact-summary">

                <div class="col-md-3">

                    <div class="card">

                        <div class="card-body text-center">

                            <h4 class="mb-1">
                                {{ $summary['pending'] }}
                            </h4>

                            <span class="text-muted">
                                Pending
                            </span>

                        </div>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="card">

                        <div class="card-body text-center">

                            <h4 class="mb-1">
                                {{ $summary['resolved'] }}
                            </h4>

                            <span class="text-muted">
                                Resolved
                            </span>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card">

                        <div class="card-body text-center">

                            <h4 class="mb-1">
                                {{ $summary['archived'] }}
                            </h4>

                            <span class="text-muted">
                                Archived
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            {{-- FILTER --}}

            <div class="card mb-2">

                <div class="card-body">

                    <form method="GET" action="{{ route('claims.pending.rebill.index') }}"
                        class="row g-2 align-items-end">

                        <div class="col-md-3">

                            <label class="form-label">
                                Status
                            </label>

                            <select name="status" class="form-select form-select-sm">

                                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>

                                    Pending

                                </option>

                                <option value="resolved" {{ $status == 'resolved' ? 'selected' : '' }}>

                                    Resolved

                                </option>

                                <option value="archived" {{ $status == 'archived' ? 'selected' : '' }}>

                                    Archived

                                </option>

                                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>

                                    All

                                </option>

                            </select>

                        </div>


                        <div class="col-md-2">

                            <button type="submit" class="btn btn-primary btn-sm">

                                Apply

                            </button>

                        </div>

                    </form>

                </div>

            </div>


            {{-- TABLE --}}

            <div class="card">

                <div class="card-body">

                    {{-- Bulk Reprocess Form --}}

                    <form id="bulkReprocessForm" method="POST"
                        action="{{ route('claims.pending.rebill.reprocess.selected') }}">

                        @csrf

                        <div id="bulkEditInputs"></div>

                    </form>


                    <div class="mb-3">
                        <button type="button" id="reprocessSelectedBtn" class="btn btn-success btn-sm">

                            Reprocess Selected

                        </button>
                    </div>

                    {{-- ========================================================= --}}
                    {{-- TOP HORIZONTAL SCROLLBAR --}}
                    {{-- ========================================================= --}}

                    <div id="pendingTopScroll" class="pending-top-scroll">
                        <div id="pendingTopScrollInner" class="pending-top-scroll-inner"></div>
                    </div>


                    {{-- ========================================================= --}}
                    {{-- SCROLLABLE TABLE AREA --}}
                    {{-- ========================================================= --}}

                    <div id="pendingTableWrapper" class="pending-table-wrapper">

                        <table
                            class="
                                    table
                                    table-bordered
                                    table-hover
                                    pending-table
                                "
                            id="pendingRebillTable">

                            <thead class="table-light">

                                <tr>

                                    <th width="35">

                                        <input type="checkbox" id="selectAll">

                                    </th>

                                    <th>ID</th>

                                    <th>Participant</th>

                                    <th>Member Name</th>

                                    <th>Insurance</th>

                                    <th>From</th>

                                    <th>To</th>

                                    <th>Proc</th>

                                    <th>Mod</th>

                                    <th>Units</th>

                                    <th>Rate</th>

                                    <th>Total</th>

                                    <th>Diag1</th>

                                    <th>Diag2</th>

                                    <th>Old PA</th>

                                    <th>Current PA</th>

                                    <th>PA Start</th>

                                    <th>PA End</th>

                                    <th>Reason</th>

                                    <th>Status</th>

                                    <th>Action</th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach ($rows as $row)
                                    @php

                                        $rowFormId = 'rebill-form-' . $row->id;

                                    @endphp


                                    <tr class="{{ $row->status === 'pending' ? 'table-warning' : '' }}">

                                        {{-- SELECT --}}

                                        <td>

                                            @if ($row->status === 'pending')
                                                <input type="checkbox" class="row-checkbox" name="selected_rows[]"
                                                    value="{{ $row->id }}" form="bulkReprocessForm">
                                            @endif

                                        </td>


                                        {{-- ID --}}

                                        <td>
                                            {{ $row->id }}
                                        </td>


                                        {{-- PARTICIPANT --}}

                                        <td>
                                            {{ $row->ParticipantId }}
                                        </td>


                                        {{-- MEMBER NAME --}}

                                        <td>
                                            {{ $row->member_name }}
                                        </td>


                                        {{-- INSURANCE --}}

                                        <td>
                                            {{ $row->insurance_name }}
                                        </td>


                                        {{-- SERVICE FROM --}}

                                        <td>

                                            @if ($row->status === 'pending')
                                                <input type="date" name="edit[service_from]"
                                                    value="{{ $row->service_from ? \Carbon\Carbon::parse($row->service_from)->format('Y-m-d') : '' }}"
                                                    form="{{ $rowFormId }}"
                                                    class="form-control
                                                          form-control-sm
                                                          pending-edit
                                                          pending-date">
                                            @else
                                                {{ $row->service_from ? \Carbon\Carbon::parse($row->service_from)->format('m/d/Y') : '' }}
                                            @endif

                                        </td>


                                        {{-- SERVICE TO --}}

                                        <td>

                                            @if ($row->status === 'pending')
                                                <input type="date" name="edit[service_to]"
                                                    value="{{ $row->service_to ? \Carbon\Carbon::parse($row->service_to)->format('Y-m-d') : '' }}"
                                                    form="{{ $rowFormId }}"
                                                    class="form-control
                                                          form-control-sm
                                                          pending-edit
                                                          pending-date">
                                            @else
                                                {{ $row->service_to ? \Carbon\Carbon::parse($row->service_to)->format('m/d/Y') : '' }}
                                            @endif

                                        </td>


                                        {{-- PROCEDURE --}}

                                        <td>

                                            @if ($row->status === 'pending')
                                                <input type="text" name="edit[proc_code]" value="{{ $row->proc_code }}"
                                                    form="{{ $rowFormId }}" maxlength="20"
                                                    class="form-control
                                                          form-control-sm
                                                          pending-edit
                                                          pending-proc">
                                            @else
                                                {{ $row->proc_code }}
                                            @endif

                                        </td>


                                        {{-- MODIFIER --}}

                                        <td>

                                            @if ($row->status === 'pending')
                                                <input type="text" name="edit[modifier]" value="{{ $row->modifier }}"
                                                    form="{{ $rowFormId }}" maxlength="10"
                                                    class="form-control
                                                          form-control-sm
                                                          pending-edit
                                                          pending-mod">
                                            @else
                                                {{ $row->modifier }}
                                            @endif

                                        </td>


                                        {{-- UNITS --}}

                                        <td>

                                            @if ($row->status === 'pending')
                                                <input type="number" name="edit[units]" value="{{ $row->units }}"
                                                    form="{{ $rowFormId }}" min="1" step="1"
                                                    class="form-control
                                                          form-control-sm
                                                          pending-edit
                                                          pending-units">
                                            @else
                                                {{ $row->units }}
                                            @endif

                                        </td>


                                        {{-- RATE --}}

                                        <td>

                                            @if ($row->status === 'pending')
                                                <input type="number" name="edit[amount_per_unit]"
                                                    value="{{ $row->amount_per_unit !== null ? number_format($row->amount_per_unit, 2, '.', '') : '' }}"
                                                    form="{{ $rowFormId }}" min="0" step="0.01"
                                                    class="form-control
                                                          form-control-sm
                                                          pending-edit
                                                          pending-rate">
                                            @else
                                                ${{ number_format($row->amount_per_unit ?? 0, 2) }}
                                            @endif

                                        </td>


                                        {{-- TOTAL --}}

                                        <td class="text-end">

                                            ${{ number_format($row->total_amount ?? 0, 2) }}

                                        </td>


                                        {{-- DIAG 1 --}}

                                        <td>

                                            @if ($row->status === 'pending')
                                                <select name="edit[diag1]" form="{{ $rowFormId }}"
                                                    class="form-select
                                                           form-select-sm
                                                           pending-diag
                                                           icd10-select"
                                                    data-placeholder="Primary ICD">

                                                    @if (!empty($row->diag1))
                                                        <option value="{{ $row->diag1 }}" selected>

                                                            {{ $row->diag1 }}

                                                        </option>
                                                    @endif

                                                </select>
                                            @else
                                                {{ $row->diag1 }}
                                            @endif

                                        </td>


                                        {{-- DIAG 2 --}}

                                        <td>

                                            @if ($row->status === 'pending')
                                                <select name="edit[diag2]" form="{{ $rowFormId }}"
                                                    class="form-select
                                                           form-select-sm
                                                           pending-diag
                                                           icd10-select"
                                                    data-placeholder="Secondary ICD">

                                                    @if (!empty($row->diag2))
                                                        <option value="{{ $row->diag2 }}" selected>

                                                            {{ $row->diag2 }}

                                                        </option>
                                                    @endif

                                                </select>
                                            @else
                                                {{ $row->diag2 }}
                                            @endif

                                        </td>


                                        {{-- OLD PA --}}

                                        <td>

                                            {{ $row->prior_auth_no }}

                                            @if ($row->prior_auth_expiry)
                                                <br>

                                                <small class="text-muted">

                                                    Exp:
                                                    {{ \Carbon\Carbon::parse($row->prior_auth_expiry)->format('m/d/Y') }}

                                                </small>
                                            @endif

                                        </td>


                                        {{-- CURRENT PA --}}

                                        <td>

                                            {{ $row->current_pa_no ?? '' }}

                                        </td>


                                        {{-- CURRENT PA START --}}

                                        <td>

                                            @if ($row->current_pa_start)
                                                {{ \Carbon\Carbon::parse($row->current_pa_start)->format('m/d/Y') }}
                                            @endif

                                        </td>


                                        {{-- CURRENT PA END --}}

                                        <td>

                                            @if ($row->current_pa_end)
                                                {{ \Carbon\Carbon::parse($row->current_pa_end)->format('m/d/Y') }}
                                            @endif

                                        </td>


                                        {{-- REASON --}}

                                        <td class="reason-column">

                                            @if (!empty($row->error_type))
                                                <strong>
                                                    {{ $row->error_type }}
                                                </strong>

                                                <br>
                                            @endif

                                            {{ $row->error_message }}

                                        </td>


                                        {{-- STATUS --}}

                                        <td>

                                            @if ($row->status === 'resolved')
                                                <span class="badge bg-success">

                                                    Resolved

                                                </span>
                                            @elseif($row->status === 'archived')
                                                <span class="badge bg-secondary">

                                                    Archived

                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark">

                                                    Pending

                                                </span>
                                            @endif


                                        </td>


                                        {{-- ACTION --}}

                                        <td>

                                            @if ($row->status === 'pending')
                                                <div class="d-flex gap-1">

                                                    {{-- REPROCESS --}}

                                                    <form id="{{ $rowFormId }}" method="POST"
                                                        action="{{ route('claims.pending.rebill.reprocess', $row->id) }}">

                                                        @csrf

                                                        <button type="submit" class="btn btn-success btn-sm"
                                                            onclick="return confirm(
                                                                    'Save these changes, revalidate this pending claim, and create a draft claim if everything is now valid?'
                                                                );">

                                                            Reprocess

                                                        </button>

                                                    </form>


                                                    {{-- ARCHIVE --}}

                                                    <form id="archive-form-{{ $row->id }}" method="POST"
                                                        action="{{ route('claims.pending.rebill.archive', $row->id) }}">

                                                        @csrf

                                                        <input type="hidden" name="archive_reason"
                                                            id="archive-reason-{{ $row->id }}">

                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                            onclick="archivePendingRebill(
                                                                    {{ $row->id }}
                                                                );">

                                                            Archive

                                                        </button>

                                                    </form>

                                                </div>
                                            @elseif($row->status === 'resolved' && $row->resolved_claim_id)
                                                <span class="text-success">

                                                    Claim ID:
                                                    {{ $row->resolved_claim_id }}

                                                </span>
                                            @elseif($row->status === 'archived')
                                                <span class="text-muted">

                                                    Archived

                                                    @if ($row->archived_at)
                                                        <br>

                                                        <small>

                                                            {{ \Carbon\Carbon::parse($row->archived_at)->format('m/d/Y') }}

                                                        </small>
                                                    @endif

                                                </span>
                                            @endif

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

@endsection

@section('scripts')

    <script>
        /*
            |--------------------------------------------------------------------------
            | Archive Pending Rebill
            |--------------------------------------------------------------------------
            */

        function archivePendingRebill(
            rowId
        ) {

            const reason =
                prompt(
                    'Please enter the reason for archiving this pending claim:'
                );


            /*
            |--------------------------------------------------------------------------
            | User Pressed Cancel
            |--------------------------------------------------------------------------
            */

            if (reason === null) {

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Reason Required
            |--------------------------------------------------------------------------
            */

            if (
                reason.trim() === ''
            ) {

                alert(
                    'Archive reason is required.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Confirmation
            |--------------------------------------------------------------------------
            */

            if (
                !confirm(
                    'Archive this pending claim? It will no longer appear in the Pending list.'
                )
            ) {

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Store Reason
            |--------------------------------------------------------------------------
            */

            document
                .getElementById(
                    'archive-reason-' + rowId
                )
                .value =
                reason.trim();


            /*
            |--------------------------------------------------------------------------
            | Submit
            |--------------------------------------------------------------------------
            */

            document
                .getElementById(
                    'archive-form-' + rowId
                )
                .submit();

        }
        document.addEventListener(
            'DOMContentLoaded',
            function() {

                /*
            |--------------------------------------------------------------------------
            | Synchronized Horizontal Scrollbars
            |--------------------------------------------------------------------------
            |
            | The top scrollbar and actual table scrollbar always stay together.
            |--------------------------------------------------------------------------
            */

                const pendingTopScroll =
                    document.getElementById(
                        'pendingTopScroll'
                    );

                const pendingTopScrollInner =
                    document.getElementById(
                        'pendingTopScrollInner'
                    );

                const pendingTableWrapper =
                    document.getElementById(
                        'pendingTableWrapper'
                    );


                if (
                    pendingTopScroll &&
                    pendingTopScrollInner &&
                    pendingTableWrapper
                ) {

                    let syncingFromTop =
                        false;

                    let syncingFromTable =
                        false;


                    /*
                    |--------------------------------------------------------------------------
                    | Match Top Scrollbar Width To Table
                    |--------------------------------------------------------------------------
                    */

                    function syncPendingScrollWidth() {

                        pendingTopScrollInner.style.width =
                            pendingTableWrapper.scrollWidth +
                            'px';
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Top Scroll -> Table
                    |--------------------------------------------------------------------------
                    */

                    pendingTopScroll.addEventListener(
                        'scroll',
                        function() {

                            if (syncingFromTable) {

                                return;
                            }

                            syncingFromTop =
                                true;

                            pendingTableWrapper.scrollLeft =
                                pendingTopScroll.scrollLeft;

                            syncingFromTop =
                                false;
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Table -> Top Scroll
                    |--------------------------------------------------------------------------
                    */

                    pendingTableWrapper.addEventListener(
                        'scroll',
                        function() {

                            if (syncingFromTop) {

                                return;
                            }

                            syncingFromTable =
                                true;

                            pendingTopScroll.scrollLeft =
                                pendingTableWrapper.scrollLeft;

                            syncingFromTable =
                                false;
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Initial Size
                    |--------------------------------------------------------------------------
                    */

                    syncPendingScrollWidth();


                    /*
                    |--------------------------------------------------------------------------
                    | Browser Resize
                    |--------------------------------------------------------------------------
                    */

                    window.addEventListener(
                        'resize',
                        syncPendingScrollWidth
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Recheck After Select2 Finishes Sizing Fields
                    |--------------------------------------------------------------------------
                    */

                    setTimeout(
                        syncPendingScrollWidth,
                        500
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Select All
                |--------------------------------------------------------------------------
                */

                const selectAll =
                    document.getElementById(
                        'selectAll'
                    );


                if (selectAll) {

                    selectAll.addEventListener(
                        'change',
                        function() {

                            document
                                .querySelectorAll(
                                    '.row-checkbox'
                                )
                                .forEach(
                                    function(checkbox) {

                                        checkbox.checked =
                                            selectAll.checked;

                                    }
                                );

                        }
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | ICD10 Select2
                |--------------------------------------------------------------------------
                */

                function initIcd10Select(
                    selector,
                    placeholder
                ) {

                    $(selector).select2({

                        width: '100%',

                        placeholder: placeholder,

                        allowClear: true,

                        minimumInputLength: 2,

                        ajax: {

                            url: "{{ route('lookup.icd10') }}",

                            dataType: 'json',

                            delay: 300,

                            data: function(params) {

                                return {

                                    q: params.term

                                };

                            },

                            processResults: function(data) {

                                return data;

                            },

                            cache: true

                        },

                        templateResult: function(item) {

                            if (item.loading) {

                                return item.text;

                            }

                            return item.text;

                        },

                        templateSelection: function(item) {

                            return item.id || item.text;

                        }

                    });

                }


                /*
                |--------------------------------------------------------------------------
                | Initialize ICD10 Select2 Fields
                |--------------------------------------------------------------------------
                */

                $('.icd10-select').each(
                    function() {

                        const placeholder =
                            $(this).data('placeholder') ||
                            'Search ICD10';


                        initIcd10Select(
                            this,
                            placeholder
                        );

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | Helper - Add Hidden Input To Bulk Form
                |--------------------------------------------------------------------------
                */

                function addBulkHiddenInput(
                    name,
                    value
                ) {

                    const input =
                        document.createElement(
                            'input'
                        );


                    input.type =
                        'hidden';


                    input.name =
                        name;


                    input.value =
                        value ?? '';


                    document
                        .getElementById(
                            'bulkEditInputs'
                        )
                        .appendChild(
                            input
                        );

                }


                /*
                |--------------------------------------------------------------------------
                | Reprocess Selected
                |--------------------------------------------------------------------------
                */

                const reprocessSelectedBtn =
                    document.getElementById(
                        'reprocessSelectedBtn'
                    );


                if (reprocessSelectedBtn) {

                    reprocessSelectedBtn.addEventListener(
                        'click',
                        function() {

                            /*
                            |--------------------------------------------------------------------------
                            | Selected Rows
                            |--------------------------------------------------------------------------
                            */

                            const selectedRows =
                                document.querySelectorAll(
                                    '.row-checkbox:checked'
                                );


                            if (
                                selectedRows.length === 0
                            ) {

                                alert(
                                    'Please select at least one pending claim.'
                                );

                                return;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Confirmation
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !confirm(
                                    'Save current changes, revalidate and reprocess the selected pending claims?'
                                )
                            ) {

                                return;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Remove Previous Hidden Edit Inputs
                            |--------------------------------------------------------------------------
                            */

                            const bulkEditInputs =
                                document.getElementById(
                                    'bulkEditInputs'
                                );


                            bulkEditInputs.innerHTML =
                                '';


                            /*
                            |--------------------------------------------------------------------------
                            | Copy Edited Values From Each Selected Row
                            |--------------------------------------------------------------------------
                            */

                            selectedRows.forEach(
                                function(checkbox) {

                                    const rowId =
                                        checkbox.value;


                                    const rowFormId =
                                        'rebill-form-' +
                                        rowId;


                                    const fields = [

                                        'service_from',

                                        'service_to',

                                        'proc_code',

                                        'modifier',

                                        'units',

                                        'amount_per_unit',

                                        'diag1',

                                        'diag2'

                                    ];


                                    fields.forEach(
                                        function(fieldName) {

                                            /*
                                            |--------------------------------------------------------------------------
                                            | Locate Field Belonging To This Row
                                            |--------------------------------------------------------------------------
                                            */

                                            const selector =
                                                '[form="' +
                                                rowFormId +
                                                '"][name="edit[' +
                                                fieldName +
                                                ']"]';


                                            const field =
                                                document.querySelector(
                                                    selector
                                                );


                                            if (!field) {

                                                return;
                                            }


                                            /*
                                            |--------------------------------------------------------------------------
                                            | Add Value To Bulk Form
                                            |--------------------------------------------------------------------------
                                            */

                                            addBulkHiddenInput(

                                                'edits[' +
                                                rowId +
                                                '][' +
                                                fieldName +
                                                ']',

                                                field.value

                                            );

                                        }
                                    );

                                }
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | Submit Bulk Form
                            |--------------------------------------------------------------------------
                            */

                            document
                                .getElementById(
                                    'bulkReprocessForm'
                                )
                                .submit();

                        }
                    );
                }

            }
        );
    </script>

@endsection
