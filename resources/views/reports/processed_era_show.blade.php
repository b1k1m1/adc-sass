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

                            <div class="d-flex justify-content-between align-items-start mb-3">

                                <div>

                                    <h4 class="card-title mb-1">
                                        Processed ERA Report
                                    </h4>


                                    <div class="text-muted">

                                        <div>
                                            <strong>ERA ID:</strong>
                                            {{ $era->id }}
                                        </div>


                                        <div>
                                            <strong>ERA File:</strong>
                                            {{ $era->file_name ?? '' }}
                                        </div>


                                        <div>
                                            <strong>Payer:</strong>
                                            {{ $era->payer_name ?? '' }}
                                        </div>


                                        <div>
                                            <strong>Payment Date:</strong>

                                            @if ($era->payment_date)
                                                {{ \Carbon\Carbon::parse($era->payment_date)->format('m/d/Y') }}
                                            @endif

                                        </div>


                                        <div>
                                            <strong>Trace #:</strong>
                                            {{ $era->trace_number ?? '' }}
                                        </div>


                                        <div>
                                            <strong>ERA Payment Amount:</strong>

                                            ${{ number_format($era->payment_amount ?? 0, 2) }}

                                        </div>

                                    </div>

                                </div>


                                <div class="d-flex gap-2">

                                    <a href="{{ route('reports.processed.era.pdf', $era->id) }}"
                                        class="btn btn-danger" target="_blank">
                                        Download PDF
                                    </a>


                                    <button type="button" class="btn btn-secondary" onclick="window.print();">
                                        Print
                                    </button>


                                    <a href="{{ route('reports.processed.era.index') }}"
                                        class="btn btn-outline-secondary">
                                        Back
                                    </a>

                                </div>

                            </div>


                            <hr>


                            {{-- =========================================================
                             Totals
                        ========================================================== --}}

                            <div class="row mb-4">

                                <div class="col-md-3 mb-2">

                                    <div class="border rounded p-3">

                                        <div class="text-muted">
                                            Claims
                                        </div>

                                        <div class="fs-4 fw-bold">
                                            {{ number_format($claims->count()) }}
                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-3 mb-2">

                                    <div class="border rounded p-3">

                                        <div class="text-muted">
                                            Total Charge
                                        </div>

                                        <div class="fs-4 fw-bold">

                                            ${{ number_format($totalCharge, 2) }}

                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-3 mb-2">

                                    <div class="border rounded p-3">

                                        <div class="text-muted">
                                            Total Paid
                                        </div>

                                        <div class="fs-4 fw-bold text-success">

                                            ${{ number_format($totalPaid, 2) }}

                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-3 mb-2">

                                    <div class="border rounded p-3">

                                        <div class="text-muted">
                                            Patient Responsibility
                                        </div>

                                        <div class="fs-4 fw-bold">

                                            ${{ number_format($totalPatientResponsibility, 2) }}

                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- =========================================================
                             Claims Detail
                        ========================================================== --}}

                            <div class="table-responsive">

                                <table id="processedEraDetailTable" class="table table-bordered table-striped align-middle"
                                    style="width:100%;">

                                    <thead>

                                        <tr>

                                            <th>
                                                Patient Control #
                                            </th>

                                            <th>
                                                Payer Claim #
                                            </th>

                                            <th>
                                                Member ID
                                            </th>

                                            <th>
                                                Member Name
                                            </th>

                                            <th>
                                                Service From
                                            </th>

                                            <th>
                                                Service To
                                            </th>

                                            <th class="text-end">
                                                Charge
                                            </th>

                                            <th class="text-end">
                                                Paid
                                            </th>

                                            <th class="text-end">
                                                Patient Resp.
                                            </th>

                                            <th>
                                                Status
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        @forelse ($claims as $claim)

                                            @php

                                                $memberName = trim(
                                                    ($claim->member_lastname ?? '') .
                                                        ', ' .
                                                        ($claim->member_firstname ?? ''),
                                                );

                                                $memberName = trim($memberName, ', ');
                                            @endphp


                                            <tr>

                                                <td>

                                                    <strong>
                                                        {{ $claim->patient_control_number }}
                                                    </strong>

                                                </td>


                                                <td>
                                                    {{ $claim->payer_claim_number ?? '' }}
                                                </td>


                                                <td>
                                                    {{ $claim->member_id ?? '' }}
                                                </td>


                                                <td>

                                                    @if ($memberName !== '')
                                                        {{ $memberName }}
                                                    @else
                                                        <span class="text-muted">
                                                            Member not found
                                                        </span>
                                                    @endif

                                                </td>


                                                <td data-order="{{ $claim->service_from }}">

                                                    @if ($claim->service_from)
                                                        {{ \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') }}
                                                    @endif

                                                </td>


                                                <td data-order="{{ $claim->service_to }}">

                                                    @if ($claim->service_to)
                                                        {{ \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') }}
                                                    @endif

                                                </td>


                                                <td class="text-end">

                                                    ${{ number_format($claim->total_charge ?? 0, 2) }}

                                                </td>


                                                <td class="text-end">

                                                    ${{ number_format($claim->total_paid ?? 0, 2) }}

                                                </td>


                                                <td class="text-end">

                                                    ${{ number_format($claim->patient_responsibility ?? 0, 2) }}

                                                </td>


                                                <td>

                                                    {{ $claim->status_description }}

                                                    @if ($claim->claim_status_code !== null)
                                                        <small class="text-muted">
                                                            ({{ $claim->claim_status_code }})
                                                        </small>
                                                    @endif

                                                </td>

                                            </tr>


                                        @empty

                                            <tr>

                                                <td colspan="10" class="text-center text-muted">
                                                    No claims found for this ERA.
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

            $('#processedEraDetailTable').DataTable({

                pageLength: 50,

                lengthMenu: [
                    [25, 50, 100, 150, 250, -1],
                    [25, 50, 100, 150, 250, 'All']
                ],

                order: [
                    [4, 'asc']
                ],

                autoWidth: false,

                responsive: false

            });

        });
    </script>

@endsection
