@extends('admin.admin_dashboard')

@section('title', 'Submitted Claims Report')

@section('admin')

    <div class="page-content">

        <div class="container-fluid">

            {{-- =========================================================
             Page Header
        ========================================================== --}}

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <div>

                                    <h4 class="card-title mb-1">
                                        Submitted Claims Report
                                    </h4>

                                    <p class="text-muted mb-0">
                                        View submitted 837P claim batches by date range.
                                    </p>

                                </div>

                            </div>


                            {{-- =========================================================
                             Date Filter
                        ========================================================== --}}

                            <form method="GET" action="{{ route('reports.submitted.claims.index') }}"
                                class="row g-3 align-items-end mb-4">

                                <div class="col-md-3">

                                    <label for="from_date" class="form-label">
                                        From Date
                                    </label>

                                    <input type="date" name="from_date" id="from_date" class="form-control"
                                        value="{{ $fromDate }}" required>

                                </div>


                                <div class="col-md-3">

                                    <label for="to_date" class="form-label">
                                        To Date
                                    </label>

                                    <input type="date" name="to_date" id="to_date" class="form-control"
                                        value="{{ $toDate }}" required>

                                </div>


                                <div class="col-md-3">

                                    <button type="submit" class="btn btn-primary">
                                        Search
                                    </button>

                                    <a href="{{ route('reports.submitted.claims.index') }}" class="btn btn-secondary ms-2">
                                        Reset
                                    </a>

                                </div>

                            </form>


                            {{-- =========================================================
                             Summary
                        ========================================================== --}}

                            @php

                                $totalBatches = $batches->count();

                                $totalClaims = $batches->sum(function ($batch) {
                                    return (int) ($batch->total_claims ?? 0);
                                });

                                $totalAmount = $batches->sum(function ($batch) {
                                    return (float) ($batch->total_amount ?? 0);
                                });

                            @endphp


                            <div class="row mb-4">

                                <div class="col-md-3 mb-2">

                                    <div class="border rounded p-3">

                                        <div class="text-muted">
                                            Total Batches
                                        </div>

                                        <div class="fs-4 fw-bold">
                                            {{ number_format($totalBatches) }}
                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-3 mb-2">

                                    <div class="border rounded p-3">

                                        <div class="text-muted">
                                            Total Claims
                                        </div>

                                        <div class="fs-4 fw-bold">
                                            {{ number_format($totalClaims) }}
                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-3 mb-2">

                                    <div class="border rounded p-3">

                                        <div class="text-muted">
                                            Total Submitted Amount
                                        </div>

                                        <div class="fs-4 fw-bold">

                                            ${{ number_format($totalAmount, 2) }}

                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- =========================================================
                             Batch Table
                        ========================================================== --}}

                            <div class="table-responsive">

                                <table id="submittedClaimsTable" class="table table-bordered table-striped align-middle"
                                    style="width:100%;">

                                    <thead>

                                        <tr>

                                            <th>
                                                Batch ID
                                            </th>

                                            <th>
                                                Batch Date
                                            </th>

                                            <th>
                                                837P File
                                            </th>

                                            <th>
                                                PDF File
                                            </th>

                                            <th class="text-end">
                                                Total Claims
                                            </th>

                                            <th class="text-end">
                                                Total Amount
                                            </th>

                                            <th>
                                                Created By
                                            </th>

                                            <th class="text-center" style="width:130px;">
                                                Action
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        @forelse ($batches as $batch)
                                            <tr>

                                                {{-- Batch ID --}}

                                                <td>

                                                    <strong>
                                                        {{ $batch->id }}
                                                    </strong>

                                                </td>


                                                {{-- Batch Date --}}

                                                <td data-order="{{ $batch->batch_timestamp }}">

                                                    @if ($batch->batch_timestamp)
                                                        {{ \Carbon\Carbon::parse($batch->batch_timestamp)->format('m/d/Y h:i A') }}
                                                    @endif

                                                </td>


                                                {{-- EDI File --}}

                                                <td>

                                                    {{ $batch->edi_filename }}

                                                </td>


                                                {{-- PDF File --}}

                                                <td>

                                                    {{ $batch->pdf_filename }}

                                                </td>


                                                {{-- Total Claims --}}

                                                <td class="text-end">

                                                    {{ number_format($batch->total_claims ?? 0) }}

                                                </td>


                                                {{-- Total Amount --}}

                                                <td class="text-end">

                                                    ${{ number_format($batch->total_amount ?? 0, 2) }}

                                                </td>


                                                {{-- Created By --}}

                                                <td>

                                                    {{ $batch->created_by ?? '' }}

                                                </td>


                                                {{-- Action --}}

                                                <td class="text-center">

                                                    <a href="{{ route('reports.submitted.claims.show', $batch->id) }}"
                                                        class="btn btn-sm btn-primary">
                                                        View Report
                                                    </a>

                                                </td>

                                            </tr>


                                        @empty

                                            <tr>

                                                <td colspan="8" class="text-center text-muted">
                                                    No submitted claim batches found
                                                    for the selected date range.
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

        </div>

    </div>

@endsection


@section('scripts')

    <script>
        $(document).ready(function() {

            $('#submittedClaimsTable').DataTable({

                pageLength: 50,

                lengthMenu: [
                    [25, 50, 100, 150, 250, -1],
                    [25, 50, 100, 150, 250, 'All']
                ],

                /*
                 * Default:
                 * Latest batch first
                 */

                order: [
                    [1, 'desc']
                ],

                autoWidth: false,

                responsive: false,

                columnDefs: [

                    /*
                     * Action column should not sort/search.
                     */

                    {
                        targets: 7,
                        orderable: false,
                        searchable: false
                    }

                ]

            });

        });
    </script>

@endsection
