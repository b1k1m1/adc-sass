@extends('admin.admin_dashboard')

@section('title', 'Ready for Resubmission')

@section('admin')

    <style>
        .ready-table th,
        .ready-table td {
            padding: .35rem .45rem !important;
            font-size: 12.5px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .ready-table th {
            background-color: #f8f9fa;
        }

        .summary-number {
            font-size: 23px;
            font-weight: 700;
        }
    </style>


    <div class="page-content">

        <div class="container-fluid">


            {{-- Page Header --}}

            <div class="row mb-3">

                <div class="col-12">

                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                        <h4 class="mb-sm-0 font-size-18">

                            Ready for Resubmission

                        </h4>


                        <div class="page-title-right">

                            <a href="{{ route('claims.resubmission.preview') }}"
                            class="btn btn-primary btn-sm">

                                <i class="fas fa-file-export me-1"></i>

                                Generate Resubmission 837P

                            </a>

                            <a href="{{ route('claims.rejections.index') }}" class="btn btn-secondary btn-sm">

                                <i class="fas fa-arrow-left me-1"></i>

                                Rejection Queue

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Success Message --}}

            @if (session('success'))
                <div class="alert alert-success">

                    <i class="fas fa-check-circle me-1"></i>

                    {{ session('success') }}

                </div>
            @endif


            {{-- Summary --}}

            <div class="row">


                <div class="col-md-4">

                    <div class="card">

                        <div class="card-body">

                            <div class="text-muted">

                                Ready Claims

                            </div>

                            <div class="summary-number text-success">

                                {{ $summary['total'] }}

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="card">

                        <div class="card-body">

                            <div class="text-muted">

                                Total Amount

                            </div>

                            <div class="summary-number">

                                ${{ number_format($summary['amount'], 2) }}

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-md-2">

                    <div class="card">

                        <div class="card-body">

                            <div class="text-muted">

                                ≤ 30 Days

                            </div>

                            <div class="summary-number text-warning">

                                {{ $summary['warning'] }}

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-md-2">

                    <div class="card">

                        <div class="card-body">

                            <div class="text-muted">

                                ≤ 15 Days

                            </div>

                            <div class="summary-number text-danger">

                                {{ $summary['critical'] }}

                            </div>

                        </div>

                    </div>

                </div>


            </div>


            {{-- Ready Claims --}}

            <div class="card">

                <div class="card-header">

                    <h5 class="mb-0">

                        Claims Ready for Next 837P Batch

                    </h5>

                </div>


                <div class="card-body">


                    @if ($claims->isEmpty())

                        <div class="alert alert-info mb-0">

                            No claims are currently ready for resubmission.

                        </div>
                    @else
                        <div class="table-responsive">


                            <table id="readyClaimsTable" class="table table-bordered table-striped ready-table">


                                <thead>

                                    <tr>

                                        <th>Claim #</th>

                                        <th>Participant</th>

                                        <th>Member</th>

                                        <th>DOS</th>

                                        <th>Units</th>

                                        <th class="text-end">
                                            Amount
                                        </th>

                                        <th>Filing Deadline</th>

                                        <th>Filing Status</th>

                                        <th>Status</th>

                                        <th>Action</th>

                                    </tr>

                                </thead>


                                <tbody>


                                    @foreach ($claims as $claim)
                                        <tr>


                                            <td>

                                                <strong>

                                                    {{ $claim->claim_no ?? '' }}

                                                </strong>

                                            </td>


                                            <td>

                                                {{ $claim->ParticipantId ?? '' }}

                                            </td>


                                            <td>

                                                {{ $claim->LastName ?? '' }}

                                                @if (!empty($claim->LastName) && !empty($claim->FirstName))
                                                    ,
                                                @endif

                                                {{ $claim->FirstName ?? '' }}

                                            </td>


                                            <td>

                                                @if (!empty($claim->service_from))
                                                    {{ \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') }}
                                                @endif


                                                @if (!empty($claim->service_to) && $claim->service_from != $claim->service_to)
                                                    <br>

                                                    <small class="text-muted">

                                                        to

                                                        {{ \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') }}

                                                    </small>
                                                @endif

                                            </td>


                                            <td>

                                                {{ $claim->units ?? '' }}

                                            </td>


                                            <td class="text-end">

                                                ${{ number_format((float) ($claim->total_amount ?? 0), 2) }}

                                            </td>


                                            <td>

                                                {{ $claim->filing_deadline ?? '' }}

                                            </td>


                                            <td>

                                                @if ($claim->filing_status === 'expired')
                                                    <span class="badge bg-secondary">

                                                        EXPIRED

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
                                                @else
                                                    <span class="badge bg-success">

                                                        ELIGIBLE

                                                    </span>
                                                @endif

                                            </td>


                                            <td>

                                                <span class="badge bg-success">

                                                    READY

                                                </span>

                                            </td>


                                            <td>

                                                <a href="{{ route('claims.rejections.show', $claim->id) }}"
                                                    class="btn btn-primary btn-sm">

                                                    <i class="fas fa-eye me-1"></i>

                                                    View

                                                </a>

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

            if ($('#readyClaimsTable').length) {

                $('#readyClaimsTable').DataTable({

                    pageLength: 25,

                    order: [],

                    responsive: false,

                    autoWidth: false

                });

            }

        });
    </script>

@endsection
