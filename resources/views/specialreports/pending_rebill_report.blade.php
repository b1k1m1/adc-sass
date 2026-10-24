@extends('admin.admin_dashboard')

@section('title', 'Pending Rebill Report')

@section('admin')

    <style>
        .pending-report-table th,
        .pending-report-table td {
            padding: .35rem .45rem !important;
            font-size: 13px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .pending-report-table .reason-column {
            white-space: normal;
            min-width: 250px;
            max-width: 450px;
        }

        .summary-card {
            border: 0;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
        }

        .summary-card .card-body {
            padding: 14px;
        }
    </style>


    <div class="page-content">

        <div class="container-fluid">


            {{-- ========================================================= --}}
            {{-- HEADER --}}
            {{-- ========================================================= --}}

            <div
                class="d-flex
                   justify-content-between
                   align-items-center
                   mb-3">

                <div>

                    <h4 class="mb-1">
                        Pending Rebill Report
                    </h4>

                    <div class="text-muted">
                        Read Only Special Report
                    </div>

                </div>


                <div class="d-flex gap-2">

                    <a href="{{ route('specialreports.pending.rebill.pdf', [
                        'status' => $status,
                    ]) }}"
                        target="_blank" class="btn btn-danger btn-sm">

                        <i class="fas fa-file-pdf me-1"></i>

                        Export to PDF

                    </a>

                </div>

            </div>


            {{-- ========================================================= --}}
            {{-- SUMMARY --}}
            {{-- ========================================================= --}}

            <div class="row mb-3">


                {{-- Pending --}}

                <div class="col-md-3">

                    <div class="card summary-card">

                        <div class="card-body text-center">

                            <h4 class="mb-1">

                                {{ number_format($summary['pending']) }}

                            </h4>

                            <span class="text-muted">
                                Pending
                            </span>

                        </div>

                    </div>

                </div>


                {{-- Resolved --}}

                <div class="col-md-3">

                    <div class="card summary-card">

                        <div class="card-body text-center">

                            <h4 class="mb-1">

                                {{ number_format($summary['resolved']) }}

                            </h4>

                            <span class="text-muted">
                                Resolved
                            </span>

                        </div>

                    </div>

                </div>


                {{-- Archived --}}

                <div class="col-md-3">

                    <div class="card summary-card">

                        <div class="card-body text-center">

                            <h4 class="mb-1">

                                {{ number_format($summary['archived']) }}

                            </h4>

                            <span class="text-muted">
                                Archived
                            </span>

                        </div>

                    </div>

                </div>


                {{-- Displayed --}}

                <div class="col-md-3">

                    <div class="card summary-card">

                        <div class="card-body text-center">

                            <h4 class="mb-1">

                                {{ number_format($rows->count()) }}

                            </h4>

                            <span class="text-muted">
                                Displayed
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ========================================================= --}}
            {{-- FILTER --}}
            {{-- ========================================================= --}}

            <div class="card mb-3">

                <div class="card-body">

                    <form method="GET"
                        action="{{ route('specialreports.pending.rebill') }}"
                        class="row g-2 align-items-end">


                        <div class="col-md-3">

                            <label class="form-label">

                                Status

                            </label>


                            <select name="status" class="form-select form-select-sm">

                                <option value="pending"
                                    {{ $status === 'pending' ? 'selected' : '' }}>
                                    Pending
                                </option>


                                <option value="resolved"
                                    {{ $status === 'resolved' ? 'selected' : '' }}>
                                    Resolved
                                </option>


                                <option value="archived"
                                    {{ $status === 'archived' ? 'selected' : '' }}>
                                    Archived
                                </option>


                                <option value="all"
                                    {{ $status === 'all' ? 'selected' : '' }}>
                                    All
                                </option>

                            </select>

                        </div>


                        <div class="col-md-4">

                            <button type="submit" class="btn btn-primary btn-sm">

                                <i class="fas fa-filter me-1"></i>

                                Apply

                            </button>


                            <a href="{{ route('specialreports.pending.rebill') }}"
                                class="btn btn-light btn-sm">

                                Reset

                            </a>

                        </div>


                    </form>

                </div>

            </div>


            {{-- ========================================================= --}}
            {{-- REPORT TABLE --}}
            {{-- ========================================================= --}}

            <div class="card">

                <div class="card-body">


                    <div class="table-responsive">


                        <table
                            class="
                            table
                            table-bordered
                            table-hover
                            pending-report-table
                        "
                            id="pendingRebillReportTable">


                            <thead class="table-light">

                                <tr>

                                    <th>
                                        ID
                                    </th>

                                    <th>
                                        Participant
                                    </th>

                                    <th>
                                        Member Name
                                    </th>

                                    <th>
                                        Insurance
                                    </th>

                                    <th>
                                        Svc From
                                    </th>

                                    <th>
                                        Svc To
                                    </th>

                                    <th>
                                        Old PA
                                    </th>

                                    <th>
                                        Current PA
                                    </th>

                                    <th>
                                        Current PA Start
                                    </th>

                                    <th>
                                        Current PA End
                                    </th>

                                    <th>
                                        Reason
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                @forelse ($rows as $row)
                                    <tr
                                        class="{{ $row->status === 'pending' ? 'table-warning' : '' }}">


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

                                            @if ($row->service_from)
                                                {{ \Carbon\Carbon::parse($row->service_from)->format('m/d/Y') }}
                                            @endif

                                        </td>


                                        {{-- SERVICE TO --}}

                                        <td>

                                            @if ($row->service_to)
                                                {{ \Carbon\Carbon::parse($row->service_to)->format('m/d/Y') }}
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
                                                <span
                                                    class="
                                                    badge
                                                    bg-success
                                                ">

                                                    Resolved

                                                </span>
                                            @elseif ($row->status === 'archived')
                                                <span
                                                    class="
                                                    badge
                                                    bg-secondary
                                                ">

                                                    Archived

                                                </span>
                                            @else
                                                <span
                                                    class="
                                                    badge
                                                    bg-warning
                                                    text-dark
                                                ">

                                                    Pending

                                                </span>
                                            @endif


                                        </td>


                                    </tr>


                                @empty


                                    <tr>

                                        <td colspan="12"
                                            class="
                                            text-center
                                            text-muted
                                            py-4
                                        ">

                                            No pending rebill records
                                            were found for the selected
                                            status.

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

@endsection


@section('scripts')

    <script>
        $(document).ready(
            function() {

                /*
                |--------------------------------------------------------------------------
                | DataTable
                |--------------------------------------------------------------------------
                */

                if (
                    $.fn.DataTable &&
                    !$.fn.DataTable.isDataTable(
                        '#pendingRebillReportTable'
                    )
                ) {

                    $('#pendingRebillReportTable')
                        .DataTable({

                            pageLength: 25,

                            lengthMenu: [
                                [25, 50, 100, -1],
                                [25, 50, 100, 'All']
                            ],

                            order: [
                                [3, 'asc'],
                                [2, 'asc'],
                                [4, 'asc']
                            ]
                        });
                }

            }
        );
    </script>

@endsection
