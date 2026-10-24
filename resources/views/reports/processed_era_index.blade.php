@extends('admin.admin_dashboard')

@section('title', 'Processed ERA Report')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-12">

                <div class="card">

                    <div class="card-body">

                        {{-- =========================================================
                             Header
                        ========================================================== --}}

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div>

                                <h4 class="card-title mb-1">
                                    Processed ERA Report
                                </h4>

                                <p class="text-muted mb-0">
                                    View processed ERA files by payment date range.
                                </p>

                            </div>

                        </div>


                        {{-- =========================================================
                             Date Filter
                        ========================================================== --}}

                        <form
                            method="GET"
                            action="{{ route('reports.processed.era.index') }}"
                            class="row g-3 align-items-end mb-4"
                        >

                            <div class="col-md-3">

                                <label
                                    for="from_date"
                                    class="form-label"
                                >
                                    From Date
                                </label>

                                <input
                                    type="date"
                                    name="from_date"
                                    id="from_date"
                                    class="form-control"
                                    value="{{ $fromDate }}"
                                    required
                                >

                            </div>


                            <div class="col-md-3">

                                <label
                                    for="to_date"
                                    class="form-label"
                                >
                                    To Date
                                </label>

                                <input
                                    type="date"
                                    name="to_date"
                                    id="to_date"
                                    class="form-control"
                                    value="{{ $toDate }}"
                                    required
                                >

                            </div>


                            <div class="col-md-3">

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Search
                                </button>


                                <a
                                    href="{{ route('reports.processed.era.index') }}"
                                    class="btn btn-secondary ms-2"
                                >
                                    Reset
                                </a>

                            </div>

                        </form>


                        {{-- =========================================================
                             Summary
                        ========================================================== --}}

                        @php

                            $totalEraFiles =
                                $eras->count();

                            $totalClaims =
                                $eras->sum(
                                    function ($era) {
                                        return (int) ($era->claim_count ?? 0);
                                    }
                                );

                            $totalPaid =
                                $eras->sum(
                                    function ($era) {
                                        return (float) ($era->payment_amount ?? 0);
                                    }
                                );

                        @endphp


                        <div class="row mb-4">

                            <div class="col-md-3 mb-2">

                                <div class="border rounded p-3">

                                    <div class="text-muted">
                                        Total ERA Files
                                    </div>

                                    <div class="fs-4 fw-bold">
                                        {{ number_format($totalEraFiles) }}
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
                                        Total ERA Amount
                                    </div>

                                    <div class="fs-4 fw-bold">

                                        ${{ number_format(
                                            $totalPaid,
                                            2
                                        ) }}

                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- =========================================================
                            ERA Table
                        ========================================================== --}}

                        @if ($eras->isNotEmpty())

                            <div class="table-responsive">

                                <table
                                    id="processedEraTable"
                                    class="table table-bordered table-striped align-middle"
                                    style="width:100%;"
                                >

                                    <thead>

                                        <tr>

                                            <th>
                                                ERA ID
                                            </th>

                                            <th>
                                                Payment Date
                                            </th>

                                            <th>
                                                ERA File
                                            </th>

                                            <th>
                                                Payer
                                            </th>

                                            <th>
                                                Trace #
                                            </th>

                                            <th class="text-end">
                                                Claims
                                            </th>

                                            <th class="text-end">
                                                Payment Amount
                                            </th>

                                            <th
                                                class="text-center"
                                                style="width:130px;"
                                            >
                                                Action
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        @foreach ($eras as $era)

                                            <tr>

                                                <td>
                                                    <strong>
                                                        {{ $era->id }}
                                                    </strong>
                                                </td>


                                                <td data-order="{{ $era->payment_date }}">

                                                    @if ($era->payment_date)

                                                        {{ \Carbon\Carbon::parse(
                                                            $era->payment_date
                                                        )->format('m/d/Y') }}

                                                    @endif

                                                </td>


                                                <td>
                                                    {{ $era->file_name ?? '' }}
                                                </td>


                                                <td>
                                                    {{ $era->payer_name ?? '' }}
                                                </td>


                                                <td>
                                                    {{ $era->trace_number ?? '' }}
                                                </td>


                                                <td class="text-end">

                                                    {{ number_format(
                                                        $era->claim_count ?? 0
                                                    ) }}

                                                </td>


                                                <td class="text-end">

                                                    ${{ number_format(
                                                        $era->payment_amount ?? 0,
                                                        2
                                                    ) }}

                                                </td>


                                                <td class="text-center">

                                                    <a
                                                        href="{{ route(
                                                            'reports.processed.era.show',
                                                            $era->id
                                                        ) }}"
                                                        class="btn btn-sm btn-primary"
                                                    >
                                                        View Report
                                                    </a>

                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        @else

                            <div class="alert alert-info">

                                No processed ERA records found for the selected date range.

                            </div>

                        @endif

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

    if ($('#processedEraTable').length) {

        $('#processedEraTable').DataTable({

            pageLength: 50,

            lengthMenu: [
                [25, 50, 100, 150, 250, -1],
                [25, 50, 100, 150, 250, 'All']
            ],

            order: [
                [1, 'desc']
            ],

            autoWidth: false,

            responsive: false,

            columnDefs: [

                {
                    targets: 7,
                    orderable: false,
                    searchable: false
                }

            ]

        });

    }

});

</script>

@endsection