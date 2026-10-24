@extends('admin.admin_dashboard')

@section('title', 'ERA Denial Exceptions - View Only')

@section('admin')

    <div class="page-content">
        <div class="container-fluid">

            {{-- ============================================================
             Page Header
        ============================================================ --}}
            <div class="row mb-3">
                <div class="col-12">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <div>
                            <h4 class="mb-1">
                                <i class="mdi mdi-eye-outline me-1"></i>
                                ERA Denial / Resolution Exceptions
                                <span class="badge bg-secondary ms-2">
                                    View Only
                                </span>
                            </h4>

                            <div class="text-muted small">
                                Track denied or zero-payment ERA claims and identify claims
                                that were later paid or manually resolved.
                            </div>
                        </div>

                        <div>
                            <a href="{{ route('era.denial.exceptions.view', ['filter' => 'active']) }}"
                                class="btn btn-outline-primary btn-sm">
                                <i class="mdi mdi-refresh me-1"></i>
                                Refresh
                            </a>
                        </div>

                    </div>

                </div>
            </div>


            {{-- ============================================================
             Flash Messages
        ============================================================ --}}
            @if (session('message'))
                @php
                    $alertType = session('alert-type', 'info');

                    $bootstrapAlert = match ($alertType) {
                        'success' => 'success',
                        'error' => 'danger',
                        'warning' => 'warning',
                        default => 'info',
                    };
                @endphp

                <div class="alert alert-{{ $bootstrapAlert }} alert-dismissible fade show py-2" role="alert">

                    {{ session('message') }}

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    </button>

                </div>
            @endif


            {{-- ============================================================
             Summary Cards
        ============================================================ --}}
            <div class="row g-2 mb-3">

                <div class="col-xl col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm mb-0">
                        <div class="card-body py-2 px-3">

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">
                                        Active
                                    </div>

                                    <div class="fs-5 fw-semibold">
                                        {{ $counts['active'] ?? 0 }}
                                    </div>
                                </div>

                                <div class="text-primary fs-4">
                                    <i class="mdi mdi-format-list-bulleted"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <div class="col-xl col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm mb-0">
                        <div class="card-body py-2 px-3">

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">
                                        Open
                                    </div>

                                    <div class="fs-5 fw-semibold text-danger">
                                        {{ $counts['open'] ?? 0 }}
                                    </div>
                                </div>

                                <div class="text-danger fs-4">
                                    <i class="mdi mdi-alert-outline"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <div class="col-xl col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm mb-0">
                        <div class="card-body py-2 px-3">

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">
                                        Partial
                                    </div>

                                    <div class="fs-5 fw-semibold text-warning">
                                        {{ $counts['partial'] ?? 0 }}
                                    </div>
                                </div>

                                <div class="text-warning fs-4">
                                    <i class="mdi mdi-progress-clock"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <div class="col-xl col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm mb-0">
                        <div class="card-body py-2 px-3">

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">
                                        Resolved
                                    </div>

                                    <div class="fs-5 fw-semibold text-success">
                                        {{ $counts['resolved'] ?? 0 }}
                                    </div>
                                </div>

                                <div class="text-success fs-4">
                                    <i class="mdi mdi-check-circle-outline"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <div class="col-xl col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm mb-0">
                        <div class="card-body py-2 px-3">

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">
                                        Archived
                                    </div>

                                    <div class="fs-5 fw-semibold">
                                        {{ $counts['archived'] ?? 0 }}
                                    </div>
                                </div>

                                <div class="text-secondary fs-4">
                                    <i class="mdi mdi-archive-outline"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>


            {{-- ============================================================
             Filters
        ============================================================ --}}
            <div class="card shadow-sm border-0 mb-3">

                <div class="card-body py-2">

                    <div class="d-flex flex-wrap gap-2">

                        <a href="{{ route('era.denial.exceptions.view', ['filter' => 'active']) }}"
                            class="btn btn-sm {{ $filter === 'active' ? 'btn-primary' : 'btn-outline-primary' }}">

                            <i class="mdi mdi-format-list-checks me-1"></i>

                            Active

                            <span class="badge bg-light text-dark ms-1">
                                {{ $counts['active'] ?? 0 }}
                            </span>
                        </a>


                        <a href="{{ route('era.denial.exceptions.view', ['filter' => 'resolved']) }}"
                            class="btn btn-sm {{ $filter === 'resolved' ? 'btn-success' : 'btn-outline-success' }}">

                            <i class="mdi mdi-check-circle-outline me-1"></i>

                            Resolved - Awaiting Archive

                            <span class="badge bg-light text-dark ms-1">
                                {{ $counts['resolved'] ?? 0 }}
                            </span>
                        </a>


                        <a href="{{ route('era.denial.exceptions.view', ['filter' => 'archived']) }}"
                            class="btn btn-sm {{ $filter === 'archived' ? 'btn-secondary' : 'btn-outline-secondary' }}">

                            <i class="mdi mdi-archive-outline me-1"></i>

                            Archived

                            <span class="badge bg-light text-dark ms-1">
                                {{ $counts['archived'] ?? 0 }}
                            </span>
                        </a>


                        <a href="{{ route('era.denial.exceptions.view', ['filter' => 'all']) }}"
                            class="btn btn-sm {{ $filter === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">

                            <i class="mdi mdi-view-list-outline me-1"></i>
                            All
                        </a>

                    </div>

                </div>

            </div>


            {{-- ============================================================
             Exception Table
        ============================================================ --}}
            <div class="card shadow-sm border-0">

                <div class="card-header bg-white py-2">

                    <div class="d-flex justify-content-between align-items-center">

                        <div class="fw-semibold">
                            Exception Work Queue
                        </div>

                        <div class="text-muted small">
                            {{ $exceptions->count() }} record(s)
                        </div>

                    </div>

                </div>


                <div class="card-body p-0">

                    {{-- ============================================================
                    Top Horizontal Scrollbar
                ============================================================ --}}
                    <div class="era-top-scroll">
                        <div class="era-top-scroll-inner"></div>
                    </div>

                    <div class="era-table-wrapper">

                        <table id="eraExceptionTable" class="table table-sm table-hover table-bordered align-middle mb-0">

                            <thead class="table-light">
                                <tr>

                                    <th>Claim #</th>

                                    <th>Participant</th>

                                    <th>Member</th>

                                    <th>DOS</th>

                                    <th class="text-end">Charge</th>

                                    <th>Original ERA</th>

                                    <th>Denial Reason</th>

                                    <th class="text-end">Original Paid</th>

                                    <th>Latest Payment</th>

                                    <th class="text-end">Latest Paid</th>

                                    <th class="text-end">Current Paid</th>

                                    <th class="text-end">Balance</th>

                                    <th>Status</th>

                                    <th>Resolution</th>

                                    <th>Action</th>

                                </tr>
                            </thead>


                            <tbody>

                                @forelse($exceptions as $row)

                                    @php

                                        $serviceFrom = $row->service_from
                                            ? \Illuminate\Support\Carbon::parse($row->service_from)->format('m/d/Y')
                                            : '';

                                        $serviceTo = $row->service_to
                                            ? \Illuminate\Support\Carbon::parse($row->service_to)->format('m/d/Y')
                                            : '';

                                        $dosDisplay = $serviceFrom;

                                        if ($serviceFrom !== '' && $serviceTo !== '' && $serviceFrom !== $serviceTo) {
                                            $dosDisplay .= ' - ' . $serviceTo;
                                        }

                                        $denialDate = $row->denial_date
                                            ? \Illuminate\Support\Carbon::parse($row->denial_date)->format('m/d/Y')
                                            : '';

                                        $latestPaidDate = $row->latest_paid_date
                                            ? \Illuminate\Support\Carbon::parse($row->latest_paid_date)->format('m/d/Y')
                                            : '';

                                        $statusClass = match ($row->report_status) {
                                            'OPEN' => 'danger',
                                            'PARTIAL' => 'warning',
                                            'RESOLVED' => 'success',
                                            'ARCHIVED' => 'secondary',
                                            default => 'info',
                                        };

                                        $rowClass = match ($row->report_status) {
                                            'OPEN' => 'exception-open',
                                            'PARTIAL' => 'exception-partial',
                                            'RESOLVED' => 'exception-resolved',
                                            'ARCHIVED' => 'exception-archived',
                                            default => '',
                                        };

                                    @endphp


                                    <tr class="{{ $rowClass }}">

                                        {{-- Claim Number --}}
                                        <td class="fw-semibold text-nowrap">

                                            {{ $row->claim_no }}

                                            <div class="small text-muted">
                                                ERA Claim #{{ $row->denial_era_claim_id }}
                                            </div>

                                        </td>


                                        {{-- Participant --}}
                                        <td class="text-nowrap">
                                            {{ $row->ParticipantId }}
                                        </td>


                                        {{-- Member --}}
                                        <td class="text-nowrap">
                                            {{ $row->member_name ?: '-' }}
                                        </td>


                                        {{-- DOS --}}
                                        <td class="text-nowrap">
                                            {{ $dosDisplay ?: '-' }}
                                        </td>


                                        {{-- Charge --}}
                                        <td class="text-end text-nowrap">
                                            ${{ number_format((float) $row->claim_total, 2) }}
                                        </td>


                                        {{-- Original ERA --}}
                                        <td class="text-nowrap">

                                            @if ($denialDate)
                                                <div>
                                                    {{ $denialDate }}
                                                </div>
                                            @endif

                                            @if ($row->denial_payer)
                                                <div class="small text-muted">
                                                    {{ $row->denial_payer }}
                                                </div>
                                            @endif

                                            @if ($row->claim_status_code)
                                                <div class="small">
                                                    CLP Status:
                                                    <strong>
                                                        {{ $row->claim_status_code }}
                                                    </strong>
                                                </div>
                                            @endif

                                        </td>


                                        {{-- Denial Reason --}}
                                        <td>

                                            <div class="denial-reason">
                                                {{ $row->denial_reason ?: '-' }}
                                            </div>

                                            @if ($row->denial_trace_number)
                                                <div class="small text-muted mt-1">
                                                    Trace:
                                                    {{ $row->denial_trace_number }}
                                                </div>
                                            @endif

                                        </td>


                                        {{-- Original Paid --}}
                                        <td class="text-end text-nowrap">
                                            ${{ number_format((float) $row->original_paid, 2) }}
                                        </td>


                                        {{-- Latest Payment --}}
                                        <td class="text-nowrap">

                                            @if ($row->latest_paid_era_claim_id)
                                                <div>
                                                    {{ $latestPaidDate ?: '-' }}
                                                </div>

                                                @if ($row->latest_paid_payer)
                                                    <div class="small text-muted">
                                                        {{ $row->latest_paid_payer }}
                                                    </div>
                                                @endif

                                                <div class="small text-muted">
                                                    ERA Claim #{{ $row->latest_paid_era_claim_id }}
                                                </div>
                                            @else
                                                <span class="text-muted">
                                                    No later payment
                                                </span>
                                            @endif

                                        </td>


                                        {{-- Latest Paid --}}
                                        <td class="text-end text-nowrap">

                                            @if ((float) $row->latest_paid_amount > 0)
                                                <span class="text-success fw-semibold">
                                                    ${{ number_format((float) $row->latest_paid_amount, 2) }}
                                                </span>
                                            @else
                                                $0.00
                                            @endif

                                        </td>


                                        {{-- Current Paid --}}
                                        <td class="text-end text-nowrap">
                                            ${{ number_format((float) $row->current_paid, 2) }}
                                        </td>


                                        {{-- Current Balance --}}
                                        <td class="text-end text-nowrap">

                                            @if ((float) $row->current_balance > 0.005)
                                                <span class="text-danger fw-semibold">
                                                    ${{ number_format((float) $row->current_balance, 2) }}
                                                </span>
                                            @else
                                                <span class="text-success fw-semibold">
                                                    $0.00
                                                </span>
                                            @endif

                                        </td>


                                        {{-- Status --}}
                                        <td class="text-center text-nowrap">

                                            <span class="badge bg-{{ $statusClass }}">
                                                {{ $row->report_status }}
                                            </span>

                                        </td>


                                        {{-- Resolution --}}
                                        <td>

                                            @if ($row->resolution_source)
                                                <div class="fw-semibold">
                                                    {{ $row->resolution_source }}
                                                </div>
                                            @else
                                                <span class="text-muted">
                                                    -
                                                </span>
                                            @endif


                                            @if ($row->resolution_note)
                                                <div class="small text-muted mt-1">
                                                    {{ $row->resolution_note }}
                                                </div>
                                            @endif


                                            @if ($row->resolved_manually_at)
                                                <div class="small text-muted mt-1">
                                                    Resolved:
                                                    {{ \Illuminate\Support\Carbon::parse($row->resolved_manually_at)->format('m/d/Y h:i A') }}
                                                </div>
                                            @endif

                                        </td>


                                        {{-- Actions --}}
                                        <td class="text-nowrap">

                                            <button type="button" class="btn btn-sm btn-outline-primary history-btn"
                                                data-claim-no="{{ $row->claim_no }}"
                                                data-history-url="{{ route('era.denial.exceptions.history', $row->denial_era_claim_id) }}"
                                                data-bs-toggle="modal" data-bs-target="#eraHistoryModal">

                                                <i class="mdi mdi-history me-1"></i>

                                                History

                                            </button>

                                        </td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="15" class="text-center text-muted py-4">

                                            <i class="mdi mdi-check-circle-outline fs-3 d-block mb-1"></i>

                                            No ERA denial exceptions found for this filter.

                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>
    </div>

    {{-- ================================================================
     ERA Claim History Modal
================================================================ --}}
    <div class="modal fade" id="eraHistoryModal" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="mdi mdi-history me-1"></i>

                        ERA History -

                        <span id="historyClaimNo">
                            -
                        </span>

                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>

                </div>


                <div class="modal-body p-0">

                    <div id="historyLoading" class="text-center py-5">

                        <div class="spinner-border spinner-border-sm me-2" role="status">
                        </div>

                        Loading ERA history...

                    </div>


                    <div id="historyError" class="alert alert-danger m-3 d-none">
                    </div>


                    <div id="historyTableWrapper" class="table-responsive d-none">

                        <table class="table table-sm table-bordered table-hover mb-0">

                            <thead class="table-light">
                                <tr>

                                    <th>Date</th>

                                    <th>ERA #</th>

                                    <th>Payer</th>

                                    <th>CLP Status</th>

                                    <th class="text-end">
                                        Charge
                                    </th>

                                    <th class="text-end">
                                        Paid
                                    </th>

                                    <th class="text-end">
                                        Patient Resp.
                                    </th>

                                    <th>Reason</th>

                                    <th>Trace</th>

                                </tr>
                            </thead>


                            <tbody id="historyTableBody">
                            </tbody>

                        </table>

                    </div>

                </div>


                <div class="modal-footer">

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">

                        Close

                    </button>

                </div>

            </div>

        </div>

    </div>

    {{-- ================================================================
     Manual Resolution Modal
================================================================ --}}
    <div class="modal fade" id="manualResolveModal" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <form method="POST" id="manualResolveForm" action="">

                    @csrf


                    <div class="modal-header">

                        <h5 class="modal-title">
                            <i class="mdi mdi-check-circle-outline me-1"></i>
                            Mark Exception Resolved
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>

                    </div>


                    <div class="modal-body">

                        <div class="alert alert-light border py-2">

                            Claim:

                            <strong id="resolveClaimNo">
                                -
                            </strong>

                        </div>


                        <div class="mb-2">

                            <label for="resolution_note" class="form-label">

                                Resolution Note
                                <span class="text-danger">*</span>

                            </label>

                            <textarea name="resolution_note" id="resolution_note" class="form-control" rows="4" maxlength="500" required
                                placeholder="Example: Resolved with payer by phone. Reference #12345."></textarea>

                            <div class="form-text">
                                This does not change any claim or ERA financial data.
                            </div>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">

                            Cancel
                        </button>


                        <button type="submit" class="btn btn-success">

                            <i class="mdi mdi-check me-1"></i>

                            Mark Resolved
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection

@section('scripts')

    <style>
        /*
                        |--------------------------------------------------------------------------
                        | Compact Exception Table
                        |--------------------------------------------------------------------------
                        */

        #eraExceptionTable {
            font-size: 12px;
            min-width: 1750px;
            width: max-content;
            margin-bottom: 0 !important;
        }


        #eraExceptionTable th,
        #eraExceptionTable td {
            padding: 0.40rem 0.45rem;
            vertical-align: middle;
        }


        /*
                        |--------------------------------------------------------------------------
                        | Main Scrollable Table Area
                        |--------------------------------------------------------------------------
                        */

        .era-table-wrapper {
            position: relative;

            overflow-x: auto;
            overflow-y: auto;

            max-height: calc(100vh - 335px);
            min-height: 350px;

            border-top: 1px solid #dee2e6;
        }


        /*
                        |--------------------------------------------------------------------------
                        | Sticky Column Headers
                        |--------------------------------------------------------------------------
                        */

        #eraExceptionTable thead th {
            position: sticky;

            top: 0;

            z-index: 20;

            white-space: nowrap;

            background: #f8f9fa !important;

            border-top: 0;

            border-bottom: 2px solid #dee2e6;

            box-shadow:
                0 1px 0 rgba(0, 0, 0, 0.08);
        }


        /*
                        |--------------------------------------------------------------------------
                        | Top Horizontal Scrollbar
                        |--------------------------------------------------------------------------
                        */

        .era-top-scroll {
            overflow-x: auto;
            overflow-y: hidden;

            height: 17px;

            border-top: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;

            background: #f8f9fa;
        }


        .era-top-scroll-inner {
            height: 1px;
        }


        /*
                        |--------------------------------------------------------------------------
                        | Sticky Claim Number - Left
                        |--------------------------------------------------------------------------
                        */

        #eraExceptionTable th:first-child,
        #eraExceptionTable td:first-child {
            position: sticky;

            left: 0;

            min-width: 105px;

            background: #ffffff;

            z-index: 15;

            box-shadow:
                2px 0 3px rgba(0, 0, 0, 0.05);
        }


        #eraExceptionTable thead th:first-child {
            background: #f8f9fa !important;
            z-index: 30;
        }


        /*
                        |--------------------------------------------------------------------------
                        | Sticky Action Column - Right
                        |--------------------------------------------------------------------------
                        */

        #eraExceptionTable th:last-child,
        #eraExceptionTable td:last-child {
            position: sticky;

            right: 0;

            min-width: 170px;

            background: #ffffff;

            z-index: 15;

            box-shadow:
                -2px 0 4px rgba(0, 0, 0, 0.08);
        }


        #eraExceptionTable thead th:last-child {
            background: #f8f9fa !important;
            z-index: 30;
        }


        /*
                        |--------------------------------------------------------------------------
                        | Preserve Sticky Column Backgrounds
                        |--------------------------------------------------------------------------
                        */

        #eraExceptionTable tbody tr:hover td:first-child,
        #eraExceptionTable tbody tr:hover td:last-child {
            background: #f5f7fa;
        }


        tr.exception-archived td:first-child,
        tr.exception-archived td:last-child {
            background: #f7f7f7;
        }


        /*
                        |--------------------------------------------------------------------------
                        | Status Indicators
                        |--------------------------------------------------------------------------
                        */

        tr.exception-open>td:first-child {
            border-left: 4px solid #dc3545;
        }


        tr.exception-partial>td:first-child {
            border-left: 4px solid #ffc107;
        }


        tr.exception-resolved>td:first-child {
            border-left: 4px solid #198754;
        }


        tr.exception-archived {
            opacity: 0.82;
        }


        tr.exception-archived>td:first-child {
            border-left: 4px solid #6c757d;
        }


        /*
                        |--------------------------------------------------------------------------
                        | Denial Reason
                        |--------------------------------------------------------------------------
                        */

        .denial-reason {
            min-width: 150px;
            max-width: 260px;
            white-space: normal;
        }


        /*
                        |--------------------------------------------------------------------------
                        | Compact Important Fields
                        |--------------------------------------------------------------------------
                        */

        #eraExceptionTable td:nth-child(1),
        #eraExceptionTable td:nth-child(2),
        #eraExceptionTable td:nth-child(4),
        #eraExceptionTable td:nth-child(5),
        #eraExceptionTable td:nth-child(8),
        #eraExceptionTable td:nth-child(10),
        #eraExceptionTable td:nth-child(11),
        #eraExceptionTable td:nth-child(12),
        #eraExceptionTable td:nth-child(13),
        #eraExceptionTable td:nth-child(15) {
            white-space: nowrap;
        }


        /*
                        |--------------------------------------------------------------------------
                        | Action Buttons
                        |--------------------------------------------------------------------------
                        */

        #eraExceptionTable td:last-child .btn {
            white-space: nowrap;
        }


        /*
                        |--------------------------------------------------------------------------
                        | DataTables Controls
                        |--------------------------------------------------------------------------
                        */

        .dataTables_wrapper .dataTables_filter {
            margin: 8px 10px 6px 0;
        }


        .dataTables_wrapper .dataTables_length {
            margin: 8px 0 6px 10px;
        }


        .dataTables_wrapper .dataTables_info {
            padding-left: 10px;
        }


        .dataTables_wrapper .dataTables_paginate {
            padding-right: 10px;
        }


        /*
                        |--------------------------------------------------------------------------
                        | History Modal
                        |--------------------------------------------------------------------------
                        */

        #eraHistoryModal .modal-dialog {
            max-width: 1150px;
        }


        #eraHistoryModal table {
            font-size: 12px;
        }


        #eraHistoryModal th,
        #eraHistoryModal td {
            vertical-align: middle;
        }


        #eraHistoryModal thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #f8f9fa;
        }


        /*
                        |--------------------------------------------------------------------------
                        | Smaller Screens
                        |--------------------------------------------------------------------------
                        */

        @media (max-width: 1200px) {

            .era-table-wrapper {
                max-height: calc(100vh - 310px);
            }

        }
    </style>


    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function() {

                /*
                |--------------------------------------------------------------------------
                | DataTable
                |--------------------------------------------------------------------------
                */

                let eraExceptionDataTable = null;


                if (
                    window.jQuery &&
                    $.fn.DataTable &&
                    !$.fn.DataTable.isDataTable(
                        '#eraExceptionTable'
                    )
                ) {

                    eraExceptionDataTable =
                        $('#eraExceptionTable')
                        .DataTable({

                            pageLength: 50,

                            lengthMenu: [
                                [25, 50, 100, 200, -1],
                                [25, 50, 100, 200, 'All']
                            ],

                            order: [],

                            autoWidth: false,

                            /*
                            |--------------------------------------------------------------------------
                            | Important
                            |--------------------------------------------------------------------------
                            |
                            | We handle horizontal scrolling ourselves so the sticky
                            | Action column continues to work properly.
                            |--------------------------------------------------------------------------
                            */

                            scrollX: false,

                            language: {
                                search: 'Search:'
                            }

                        });

                }


                /*
                |--------------------------------------------------------------------------
                | Top Horizontal Scrollbar
                |--------------------------------------------------------------------------
                */

                const tableWrapper =
                    document.querySelector(
                        '.era-table-wrapper'
                    );


                const topScroll =
                    document.querySelector(
                        '.era-top-scroll'
                    );


                const topScrollInner =
                    document.querySelector(
                        '.era-top-scroll-inner'
                    );


                const exceptionTable =
                    document.getElementById(
                        'eraExceptionTable'
                    );


                let syncingFromTop =
                    false;


                let syncingFromBottom =
                    false;


                function updateTopScrollbarWidth() {
                    if (
                        !tableWrapper ||
                        !topScrollInner
                    ) {
                        return;
                    }


                    topScrollInner.style.width =
                        tableWrapper.scrollWidth +
                        'px';
                }


                /*
                |--------------------------------------------------------------------------
                | Top Scroll -> Table
                |--------------------------------------------------------------------------
                */

                if (
                    topScroll &&
                    tableWrapper
                ) {

                    topScroll.addEventListener(
                        'scroll',
                        function() {

                            if (syncingFromBottom) {
                                return;
                            }


                            syncingFromTop =
                                true;


                            tableWrapper.scrollLeft =
                                topScroll.scrollLeft;


                            syncingFromTop =
                                false;
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Table Scroll -> Top Scroll
                    |--------------------------------------------------------------------------
                    */

                    tableWrapper.addEventListener(
                        'scroll',
                        function() {

                            if (syncingFromTop) {
                                return;
                            }


                            syncingFromBottom =
                                true;


                            topScroll.scrollLeft =
                                tableWrapper.scrollLeft;


                            syncingFromBottom =
                                false;
                        }
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Initial Top Scrollbar Width
                |--------------------------------------------------------------------------
                */

                setTimeout(
                    updateTopScrollbarWidth,
                    150
                );


                /*
                |--------------------------------------------------------------------------
                | Window Resize
                |--------------------------------------------------------------------------
                */

                window.addEventListener(
                    'resize',
                    updateTopScrollbarWidth
                );


                /*
                |--------------------------------------------------------------------------
                | DataTable Redraw
                |--------------------------------------------------------------------------
                |
                | Search, paging and page-length changes can alter table width.
                |--------------------------------------------------------------------------
                */

                if (
                    window.jQuery &&
                    $.fn.DataTable
                ) {

                    $('#eraExceptionTable')
                        .on(
                            'draw.dt',
                            function() {

                                setTimeout(
                                    updateTopScrollbarWidth,
                                    50
                                );
                            }
                        );

                }


                /*
                |--------------------------------------------------------------------------
                | Manual Resolve Modal
                |--------------------------------------------------------------------------
                |
                | Use Bootstrap modal event instead of direct button listeners.
                | This continues working even after DataTables redraws rows.
                |--------------------------------------------------------------------------
                */

                const manualResolveModal =
                    document.getElementById(
                        'manualResolveModal'
                    );


                if (manualResolveModal) {

                    manualResolveModal.addEventListener(
                        'show.bs.modal',
                        function(event) {

                            const button =
                                event.relatedTarget;


                            if (!button) {
                                return;
                            }


                            const action =
                                button.getAttribute(
                                    'data-action'
                                );


                            const claimNo =
                                button.getAttribute(
                                    'data-claim-no'
                                );


                            const resolveForm =
                                document.getElementById(
                                    'manualResolveForm'
                                );


                            const resolveClaimNo =
                                document.getElementById(
                                    'resolveClaimNo'
                                );


                            const resolutionNote =
                                document.getElementById(
                                    'resolution_note'
                                );


                            if (resolveForm) {

                                resolveForm.action =
                                    action ||
                                    '';
                            }


                            if (resolveClaimNo) {

                                resolveClaimNo.textContent =
                                    claimNo ||
                                    '-';
                            }


                            if (resolutionNote) {

                                resolutionNote.value =
                                    '';
                            }

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Reset Manual Resolve Modal
                    |--------------------------------------------------------------------------
                    */

                    manualResolveModal.addEventListener(
                        'hidden.bs.modal',
                        function() {

                            const resolveForm =
                                document.getElementById(
                                    'manualResolveForm'
                                );


                            const resolveClaimNo =
                                document.getElementById(
                                    'resolveClaimNo'
                                );


                            const resolutionNote =
                                document.getElementById(
                                    'resolution_note'
                                );


                            if (resolveForm) {

                                resolveForm.action =
                                    '';
                            }


                            if (resolveClaimNo) {

                                resolveClaimNo.textContent =
                                    '-';
                            }


                            if (resolutionNote) {

                                resolutionNote.value =
                                    '';
                            }

                        }
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Archive Confirmation
                |--------------------------------------------------------------------------
                |
                | Event delegation keeps this working after DataTables redraws.
                |--------------------------------------------------------------------------
                */

                document.addEventListener(
                    'submit',
                    function(event) {

                        const form =
                            event.target;


                        if (
                            form &&
                            form.classList &&
                            form.classList.contains(
                                'archive-form'
                            )
                        ) {

                            const confirmed =
                                confirm(
                                    'Archive this resolved ERA exception?'
                                );


                            if (!confirmed) {

                                event.preventDefault();
                            }

                        }

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | Restore Confirmation
                |--------------------------------------------------------------------------
                */

                document.addEventListener(
                    'submit',
                    function(event) {

                        const form =
                            event.target;


                        if (
                            form &&
                            form.classList &&
                            form.classList.contains(
                                'restore-form'
                            )
                        ) {

                            const confirmed =
                                confirm(
                                    'Restore this ERA exception to the active list?'
                                );


                            if (!confirmed) {

                                event.preventDefault();
                            }

                        }

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | ERA History Modal
                |--------------------------------------------------------------------------
                |
                | Bootstrap supplies the exact button that opened the modal through
                | event.relatedTarget.
                |
                | This is more reliable than attaching click listeners to each History
                | button because DataTables can replace table rows during redraws.
                |--------------------------------------------------------------------------
                */

                const eraHistoryModal =
                    document.getElementById(
                        'eraHistoryModal'
                    );


                if (eraHistoryModal) {

                    /*
                    |--------------------------------------------------------------------------
                    | Current Request Controller
                    |--------------------------------------------------------------------------
                    |
                    | If a user clicks another History button quickly, abort the
                    | previous request so an older response cannot overwrite the
                    | current modal.
                    |--------------------------------------------------------------------------
                    */

                    let historyAbortController =
                        null;


                    eraHistoryModal.addEventListener(
                        'show.bs.modal',
                        function(event) {

                            /*
                            |--------------------------------------------------------------------------
                            | Abort Previous Request
                            |--------------------------------------------------------------------------
                            */

                            if (historyAbortController) {

                                historyAbortController.abort();
                            }


                            historyAbortController =
                                new AbortController();


                            /*
                            |--------------------------------------------------------------------------
                            | Button That Opened Modal
                            |--------------------------------------------------------------------------
                            */

                            const button =
                                event.relatedTarget;


                            /*
                            |--------------------------------------------------------------------------
                            | Modal Elements
                            |--------------------------------------------------------------------------
                            */

                            const title =
                                document.getElementById(
                                    'historyClaimNo'
                                );


                            const loading =
                                document.getElementById(
                                    'historyLoading'
                                );


                            const error =
                                document.getElementById(
                                    'historyError'
                                );


                            const wrapper =
                                document.getElementById(
                                    'historyTableWrapper'
                                );


                            const body =
                                document.getElementById(
                                    'historyTableBody'
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | Reset Modal Immediately
                            |--------------------------------------------------------------------------
                            |
                            | Prevent previous claim's history from remaining visible.
                            |--------------------------------------------------------------------------
                            */

                            if (title) {

                                title.textContent =
                                    '-';
                            }


                            if (body) {

                                body.innerHTML =
                                    '';
                            }


                            if (wrapper) {

                                wrapper.classList.add(
                                    'd-none'
                                );
                            }


                            if (error) {

                                error.textContent =
                                    '';

                                error.classList.add(
                                    'd-none'
                                );
                            }


                            if (loading) {

                                loading.classList.remove(
                                    'd-none'
                                );
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Missing Trigger Button
                            |--------------------------------------------------------------------------
                            */

                            if (!button) {

                                if (loading) {

                                    loading.classList.add(
                                        'd-none'
                                    );
                                }


                                if (error) {

                                    error.textContent =
                                        'Unable to determine which claim history to load.';

                                    error.classList.remove(
                                        'd-none'
                                    );
                                }


                                return;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Button Data
                            |--------------------------------------------------------------------------
                            */

                            const url =
                                button.getAttribute(
                                    'data-history-url'
                                );


                            const claimNo =
                                button.getAttribute(
                                    'data-claim-no'
                                ) ||
                                '-';


                            if (title) {

                                title.textContent =
                                    claimNo;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Missing History URL
                            |--------------------------------------------------------------------------
                            */

                            if (!url) {

                                if (loading) {

                                    loading.classList.add(
                                        'd-none'
                                    );
                                }


                                if (error) {

                                    error.textContent =
                                        'History URL was not found.';

                                    error.classList.remove(
                                        'd-none'
                                    );
                                }


                                return;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Fetch History
                            |--------------------------------------------------------------------------
                            */

                            fetch(
                                    url, {
                                        method: 'GET',

                                        headers: {

                                            'X-Requested-With': 'XMLHttpRequest',

                                            'Accept': 'application/json'
                                        },

                                        signal: historyAbortController.signal
                                    }
                                )

                                .then(
                                    function(response) {

                                        if (!response.ok) {

                                            throw new Error(
                                                'Unable to load ERA history. HTTP ' +
                                                response.status
                                            );
                                        }


                                        return response.json();
                                    }
                                )

                                .then(
                                    function(data) {

                                        /*
                                        |--------------------------------------------------------------------------
                                        | Stop Loading
                                        |--------------------------------------------------------------------------
                                        */

                                        if (loading) {

                                            loading.classList.add(
                                                'd-none'
                                            );
                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | Validate Response
                                        |--------------------------------------------------------------------------
                                        */

                                        if (
                                            !data ||
                                            data.success !== true
                                        ) {

                                            throw new Error(
                                                data?.message ||
                                                'Unable to load ERA history.'
                                            );
                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | Claim Number Returned By Controller
                                        |--------------------------------------------------------------------------
                                        */

                                        if (
                                            title &&
                                            data.claim_no
                                        ) {

                                            title.textContent =
                                                data.claim_no;
                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | No History
                                        |--------------------------------------------------------------------------
                                        */

                                        if (
                                            !Array.isArray(
                                                data.history
                                            ) ||
                                            data.history.length === 0
                                        ) {

                                            if (error) {

                                                error.textContent =
                                                    'No ERA history found for this claim.';

                                                error.classList.remove(
                                                    'd-none'
                                                );
                                            }


                                            return;
                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | Build History Table
                                        |--------------------------------------------------------------------------
                                        */

                                        data.history.forEach(
                                            function(row) {

                                                const tr =
                                                    document.createElement(
                                                        'tr'
                                                    );


                                                /*
                                                |--------------------------------------------------------------------------
                                                | Status Code
                                                |--------------------------------------------------------------------------
                                                */

                                                const statusCode =
                                                    String(
                                                        row.status_code ??
                                                        ''
                                                    );


                                                /*
                                                |--------------------------------------------------------------------------
                                                | Status Badge
                                                |--------------------------------------------------------------------------
                                                */

                                                let badgeClass =
                                                    'bg-secondary';


                                                if (
                                                    statusCode ===
                                                    '4'
                                                ) {

                                                    badgeClass =
                                                        'bg-danger';

                                                } else if (
                                                    statusCode ===
                                                    '22'
                                                ) {

                                                    badgeClass =
                                                        'bg-warning text-dark';

                                                } else if (
                                                    Number(
                                                        row.paid ??
                                                        0
                                                    ) >
                                                    0
                                                ) {

                                                    badgeClass =
                                                        'bg-success';
                                                }


                                                /*
                                                |--------------------------------------------------------------------------
                                                | Paid Display
                                                |--------------------------------------------------------------------------
                                                */

                                                let paidDisplay =
                                                    '$0.00';


                                                if (
                                                    Number(
                                                        row.paid ??
                                                        0
                                                    ) >
                                                    0
                                                ) {

                                                    paidDisplay =
                                                        '<span class="text-success fw-semibold">' +
                                                        '$' +
                                                        Number(
                                                            row.paid
                                                        ).toFixed(2) +
                                                        '</span>';
                                                }


                                                /*
                                                |--------------------------------------------------------------------------
                                                | History Row
                                                |--------------------------------------------------------------------------
                                                */

                                                tr.innerHTML = `

                                            <td class="text-nowrap">

                                                ${escapeHtml(
                                                    formatDateMMDDYYYY(
                                                        row.date
                                                    )
                                                )}



                                            </td>


                                            <td class="text-nowrap">

                                                #${escapeHtml(
                                                    row.era_claim_id
                                                    || ''
                                                )}

                                            </td>


                                            <td>

                                                ${escapeHtml(
                                                    row.payer
                                                    || '-'
                                                )}

                                            </td>


                                            <td class="text-nowrap">

                                                <span class="badge ${badgeClass}">

                                                    ${escapeHtml(
                                                        statusCode
                                                        || '-'
                                                    )}

                                                </span>

                                                <div class="small text-muted mt-1">

                                                    ${escapeHtml(
                                                        row.status_text
                                                        || ''
                                                    )}

                                                </div>

                                            </td>


                                            <td class="text-end text-nowrap">

                                                $${Number(
                                                    row.charge
                                                    || 0
                                                ).toFixed(2)}

                                            </td>


                                            <td class="text-end text-nowrap">

                                                ${paidDisplay}

                                            </td>


                                            <td class="text-end text-nowrap">

                                                $${Number(
                                                    row.patient_responsibility
                                                    || 0
                                                ).toFixed(2)}

                                            </td>


                                            <td>

                                                ${escapeHtml(
                                                    row.reason
                                                    || '-'
                                                )}

                                            </td>


                                            <td class="text-nowrap">

                                                ${escapeHtml(
                                                    row.trace_number
                                                    || '-'
                                                )}

                                            </td>
                                        `;


                                                if (body) {

                                                    body.appendChild(
                                                        tr
                                                    );
                                                }

                                            }
                                        );

                                        function formatDateMMDDYYYY(value) {
                                            if (!value) {
                                                return '-';
                                            }

                                            const parts =
                                                String(value)
                                                .split('-');

                                            if (parts.length !== 3) {
                                                return value;
                                            }

                                            const year = parts[0];
                                            const month = parts[1];
                                            const day = parts[2];

                                            return month +
                                                '/' +
                                                day +
                                                '/' +
                                                year;
                                        }
                                        /*
                                        |--------------------------------------------------------------------------
                                        | Show History Table
                                        |--------------------------------------------------------------------------
                                        */

                                        if (wrapper) {

                                            wrapper.classList.remove(
                                                'd-none'
                                            );
                                        }

                                    }
                                )

                                .catch(
                                    function(err) {

                                        /*
                                        |--------------------------------------------------------------------------
                                        | Ignore Deliberate Abort
                                        |--------------------------------------------------------------------------
                                        */

                                        if (
                                            err.name ===
                                            'AbortError'
                                        ) {
                                            return;
                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | Stop Loading
                                        |--------------------------------------------------------------------------
                                        */

                                        if (loading) {

                                            loading.classList.add(
                                                'd-none'
                                            );
                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | Clear Stale Data
                                        |--------------------------------------------------------------------------
                                        */

                                        if (body) {

                                            body.innerHTML =
                                                '';
                                        }


                                        if (wrapper) {

                                            wrapper.classList.add(
                                                'd-none'
                                            );
                                        }


                                        /*
                                        |--------------------------------------------------------------------------
                                        | Show Error
                                        |--------------------------------------------------------------------------
                                        */

                                        if (error) {

                                            error.textContent =
                                                err.message ||
                                                'Unable to load ERA history.';

                                            error.classList.remove(
                                                'd-none'
                                            );
                                        }


                                        console.error(
                                            'ERA History Error:',
                                            err
                                        );

                                    }
                                );

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Completely Reset History Modal When Closed
                    |--------------------------------------------------------------------------
                    */

                    eraHistoryModal.addEventListener(
                        'hidden.bs.modal',
                        function() {

                            /*
                            |--------------------------------------------------------------------------
                            | Abort Outstanding Request
                            |--------------------------------------------------------------------------
                            */

                            if (historyAbortController) {

                                historyAbortController.abort();

                                historyAbortController =
                                    null;
                            }


                            const title =
                                document.getElementById(
                                    'historyClaimNo'
                                );


                            const body =
                                document.getElementById(
                                    'historyTableBody'
                                );


                            const loading =
                                document.getElementById(
                                    'historyLoading'
                                );


                            const error =
                                document.getElementById(
                                    'historyError'
                                );


                            const wrapper =
                                document.getElementById(
                                    'historyTableWrapper'
                                );


                            if (title) {

                                title.textContent =
                                    '-';
                            }


                            if (body) {

                                body.innerHTML =
                                    '';
                            }


                            if (loading) {

                                loading.classList.remove(
                                    'd-none'
                                );
                            }


                            if (error) {

                                error.textContent =
                                    '';

                                error.classList.add(
                                    'd-none'
                                );
                            }


                            if (wrapper) {

                                wrapper.classList.add(
                                    'd-none'
                                );
                            }

                        }
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | HTML Escape Helper
                |--------------------------------------------------------------------------
                */

                function escapeHtml(value) {
                    return String(
                            value ??
                            ''
                        )

                        .replaceAll(
                            '&',
                            '&amp;'
                        )

                        .replaceAll(
                            '<',
                            '&lt;'
                        )

                        .replaceAll(
                            '>',
                            '&gt;'
                        )

                        .replaceAll(
                            '"',
                            '&quot;'
                        )

                        .replaceAll(
                            "'",
                            '&#039;'
                        );
                }

            }
        );
    </script>

@endsection
