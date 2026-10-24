@extends('admin.admin_dashboard')

@section('title', 'Claim Rejection Work Queue')

@section('admin')

    <style>
        .rejection-table th,
        .rejection-table td {
            padding: .35rem .45rem !important;
            font-size: 12.5px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .rejection-table th {
            background-color: #f8f9fa;
        }

        .rejection-reason {
            white-space: normal !important;
            min-width: 280px;
            max-width: 420px;
        }

        .summary-card {
            border-left: 4px solid #6c757d;
        }

        .summary-card .summary-number {
            font-size: 24px;
            font-weight: 700;
        }
    </style>


    <div class="page-content">

        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success">

                    <i class="fas fa-check-circle me-1"></i>

                    {{ session('success') }}

                </div>
            @endif


            {{-- Page Title --}}

            <div class="row mb-3">

                <div class="col-12">

                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                        <h4 class="mb-sm-0 font-size-18">
                            Claim Rejection Work Queue
                        </h4>

                        <a href="{{ route('claims.rejections.ready') }}"
                            class="btn btn-success btn-sm">

                                <i class="fas fa-check-circle me-1"></i>

                                Ready for Resubmission

                            </a>

                    </div>

                </div>

            </div>


            {{-- Summary Cards --}}

            <div class="row">


                {{-- Total Rejected --}}

                <div class="col-xl col-md-4 col-sm-6">

                    <div class="card summary-card">

                        <div class="card-body">

                            <div class="text-muted">
                                Rejected Claims
                            </div>

                            <div class="summary-number">

                                {{ $summary['total'] }}

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Eligible --}}

                <div class="col-xl col-md-4 col-sm-6">

                    <div class="card summary-card">

                        <div class="card-body">

                            <div class="text-muted">
                                Eligible
                            </div>

                            <div class="summary-number text-success">

                                {{ $summary['eligible'] }}

                            </div>

                        </div>

                    </div>

                </div>


                {{-- 30 Days --}}

                <div class="col-xl col-md-4 col-sm-6">

                    <div class="card summary-card">

                        <div class="card-body">

                            <div class="text-muted">
                                ≤ 30 Days
                            </div>

                            <div class="summary-number text-warning">

                                {{ $summary['days30'] }}

                            </div>

                        </div>

                    </div>

                </div>


                {{-- 15 Days --}}

                <div class="col-xl col-md-4 col-sm-6">

                    <div class="card summary-card">

                        <div class="card-body">

                            <div class="text-muted">
                                ≤ 15 Days
                            </div>

                            <div class="summary-number text-danger">

                                {{ $summary['days15'] }}

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Expired --}}

                <div class="col-xl col-md-4 col-sm-6">

                    <div class="card summary-card">

                        <div class="card-body">

                            <div class="text-muted">
                                Filing Expired
                            </div>

                            <div class="summary-number text-secondary">

                                {{ $summary['expired'] }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Filters --}}

            <div class="card">

                <div class="card-body">

                    <div class="d-flex flex-wrap gap-2">


                        <a href="{{ route('claims.rejections.index', ['filter' => 'all']) }}"
                            class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">

                            All Rejections

                        </a>


                        <a href="{{ route('claims.rejections.index', ['filter' => 'eligible']) }}"
                            class="btn btn-sm {{ $filter === 'eligible' ? 'btn-success' : 'btn-outline-success' }}">

                            Eligible

                        </a>


                        <a href="{{ route('claims.rejections.index', ['filter' => '30days']) }}"
                            class="btn btn-sm {{ $filter === '30days' ? 'btn-warning' : 'btn-outline-warning' }}">

                            ≤ 30 Days

                        </a>


                        <a href="{{ route('claims.rejections.index', ['filter' => '15days']) }}"
                            class="btn btn-sm {{ $filter === '15days' ? 'btn-danger' : 'btn-outline-danger' }}">

                            ≤ 15 Days

                        </a>


                        <a href="{{ route('claims.rejections.index', ['filter' => 'expired']) }}"
                            class="btn btn-sm {{ $filter === 'expired' ? 'btn-secondary' : 'btn-outline-secondary' }}">

                            Filing Expired

                        </a>


                    </div>

                </div>

            </div>


            {{-- Rejection Queue --}}

            <div class="card">

                <div class="card-header">

                    <h5 class="mb-0">
                        Rejected / Invalid Claims
                    </h5>

                </div>


                <div class="card-body">

                    @if ($claims->isEmpty())

                        <div class="alert alert-info mb-0">

                            No rejected claims were found for this filter.

                        </div>
                    @else
                        <div class="table-responsive">


                            <table id="rejectionTable" class="table table-bordered table-striped rejection-table">


                                <thead>

                                    <tr>

                                        <th>
                                            Claim #
                                        </th>

                                        <th>
                                            Participant
                                        </th>

                                        <th>
                                            Member
                                        </th>

                                        <th>
                                            DOS
                                        </th>

                                        <th class="text-end">
                                            Amount
                                        </th>

                                        <th>
                                            Rejection Reason
                                        </th>

                                        <th>
                                            Filing Deadline
                                        </th>

                                        <th>
                                            Filing Status
                                        </th>

                                        <th>
                                            Claim Status
                                        </th>

                                        <th>
                                            Action
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    @foreach ($claims as $claim)
                                        <tr>


                                            {{-- Claim Number --}}

                                            <td>

                                                <strong>

                                                    {{ $claim->claim_no ?? '' }}

                                                </strong>

                                            </td>


                                            {{-- Participant --}}

                                            <td>

                                                {{ $claim->ParticipantId ?? '' }}

                                            </td>


                                            {{-- Member Name --}}

                                            <td>

                                                {{ $claim->LastName ?? '' }}

                                                @if (!empty($claim->LastName) && !empty($claim->FirstName))
                                                    ,
                                                @endif

                                                {{ $claim->FirstName ?? '' }}

                                            </td>


                                            {{-- DOS --}}

                                            <td>

                                                @if (!empty($claim->service_from))
                                                    {{ \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') }}
                                                @endif


                                                @if (!empty($claim->service_from) && !empty($claim->service_to) && $claim->service_from != $claim->service_to)
                                                    <br>

                                                    <small class="text-muted">

                                                        to

                                                        {{ \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') }}

                                                    </small>
                                                @endif

                                            </td>


                                            {{-- Amount --}}

                                            <td class="text-end">

                                                @if (isset($claim->total_amount))
                                                    ${{ number_format((float) $claim->total_amount, 2) }}
                                                @endif

                                            </td>


                                            {{-- Rejection Reason --}}

                                            <td class="rejection-reason">

                                                @if (!empty($claim->precheck_message))
                                                    {{ $claim->precheck_message }}
                                                @else
                                                    <span class="text-muted">

                                                        No rejection message stored

                                                    </span>
                                                @endif

                                            </td>


                                            {{-- Filing Deadline --}}

                                            <td>

                                                {{ $claim->filing_deadline ?? '' }}

                                            </td>


                                            {{-- Filing Status --}}

                                            <td>


                                                @if ($claim->filing_status === 'expired')
                                                    <span class="badge bg-secondary">

                                                        FILING EXPIRED

                                                    </span>
                                                @elseif($claim->filing_status === 'critical')
                                                    <span class="badge bg-danger">

                                                        {{ $claim->days_left }}
                                                        DAYS LEFT

                                                    </span>
                                                @elseif($claim->filing_status === 'warning')
                                                    <span class="badge bg-warning text-dark">

                                                        {{ $claim->days_left }}
                                                        DAYS LEFT

                                                    </span>
                                                @elseif($claim->filing_status === 'eligible')
                                                    <span class="badge bg-success">

                                                        ELIGIBLE

                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-dark">

                                                        UNKNOWN

                                                    </span>
                                                @endif


                                            </td>


                                            {{-- Current Claim Status --}}

                                            <td>

                                                @if (!empty($claim->status))
                                                    {{ strtoupper($claim->status) }}
                                                @endif

                                            </td>


                                            {{-- Actions --}}

                                            <td>


                                                @if ($claim->filing_status === 'expired')
                                                    <button type="button" class="btn btn-secondary btn-sm" disabled>

                                                        Expired

                                                    </button>
                                                @else
                                                    <a href="{{ route('claims.rejections.show', $claim->id) }}"
                                                        class="btn btn-primary btn-sm">

                                                        <i class="fas fa-eye me-1"></i>
                                                        Review

                                                    </a>
                                                @endif


                                            </td>


                                        </tr>
                                    @endforeach


                                </tbody>


                            </table>


                        </div>


                    @endif


                </div>

            </div>


        </div>

    </div>

@endsection



@section('scripts')

    <script>
        $(document).ready(function() {

            if ($('#rejectionTable').length) {

                $('#rejectionTable').DataTable({

                    pageLength: 25,

                    order: [],

                    responsive: false,

                    autoWidth: false

                });

            }

        });
    </script>

@endsection
