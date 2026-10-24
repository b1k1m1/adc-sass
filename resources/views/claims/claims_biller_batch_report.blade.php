@extends('admin.admin_dashboard')

@section('title', 'Biller Claim Submission Report')

@section('admin')

    <style>
        .biller-report-table th,
        .biller-report-table td {
            padding: .30rem .45rem !important;
            font-size: 13px;
            vertical-align: middle;
        }

        .biller-report-table th {
            white-space: nowrap;
        }

        .insurance-heading {
            background: #f3f4f6;
            border-left: 4px solid #495057;
            padding: 8px 12px;
            font-weight: 700;
            margin-bottom: 0;
        }

        .summary-box {
            font-size: 13px;
        }

        @media print {

            .page-content {
                margin: 0 !important;
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .insurance-section {
                page-break-inside: avoid;
            }

            body {
                background: white !important;
            }
        }
    </style>


    <div class="page-content">

        <div class="container-fluid">


            {{-- PAGE HEADER --}}

            <div class="d-flex justify-content-between align-items-center mb-3 no-print">

                <div>

                    <h4 class="mb-0">
                        Biller Claim Submission Report
                    </h4>

                </div>


                <div>

                    <button type="button" class="btn btn-primary btn-sm" onclick="window.print();">

                        <i class="fas fa-print"></i>
                        Print

                    </button>


                    <a href="{{ route('claims.batches.index') }}" class="btn btn-secondary btn-sm">

                        Back

                    </a>

                </div>

            </div>


            {{-- REPORT HEADER --}}

            <div class="card mb-3">

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-3">

                            <strong>Batch ID:</strong>

                            {{ $batch->id }}

                        </div>


                        <div class="col-md-3">

                            <strong>Batch Date:</strong>

                            @if (!empty($batch->batch_timestamp))
                                {{ \Carbon\Carbon::parse($batch->batch_timestamp)->format('m/d/Y h:i A') }}
                            @endif

                        </div>


                        <div class="col-md-3">

                            <strong>Submitted By:</strong>

                            {{ $batch->created_by ?? '' }}

                        </div>


                        <div class="col-md-3">

                            <strong>837 Claims:</strong>

                            {{ $batch->total_claims ?? 0 }}

                        </div>

                    </div>

                </div>

            </div>


            {{-- OVERALL SUMMARY --}}

            <div class="card mb-4">

                <div class="card-header">

                    <strong>
                        Batch Summary
                    </strong>

                </div>


                <div class="card-body summary-box">

                    <div class="row text-center">

                        <div class="col-md-3">

                            <div class="fw-bold fs-5">

                                {{ number_format($summary['biller_claims']) }}

                            </div>

                            <div class="text-muted">

                                Biller Claims

                            </div>

                        </div>


                        <div class="col-md-3">

                            <div class="fw-bold fs-5">

                                {{ number_format($summary['actual_claims']) }}

                            </div>

                            <div class="text-muted">

                                Actual Claims Submitted

                            </div>

                        </div>


                        <div class="col-md-3">

                            <div class="fw-bold fs-5">

                                {{ number_format($summary['units']) }}

                            </div>

                            <div class="text-muted">

                                Total Units

                            </div>

                        </div>


                        <div class="col-md-3">

                            <div class="fw-bold fs-5">

                                ${{ number_format($summary['amount'], 2) }}

                            </div>

                            <div class="text-muted">

                                Total Amount

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- INSURANCE GROUPS --}}

            @forelse($insuranceGroups as $insuranceName => $rows)


                @php

                    $insuranceBillerClaims = $rows->count();

                    $insuranceActualClaims = $rows->sum(function ($row) {
                        return (int) ($row->actual_claim_count ?? 0);
                    });

                    $insuranceUnits = $rows->sum(function ($row) {
                        return (int) ($row->total_units ?? 0);
                    });

                    $insuranceAmount = $rows->sum(function ($row) {
                        return (float) ($row->total_amount ?? 0);
                    });
                @endphp


                <div class="card mb-4 insurance-section">


                    {{-- INSURANCE HEADER --}}

                    <div class="insurance-heading">

                        Insurance:

                        {{ $insuranceName }}

                    </div>


                    <div class="card-body p-0">


                        <div class="table-responsive">

                            <table class="table table-bordered table-hover mb-0 biller-report-table">


                                <thead class="table-light">

                                    <tr>

                                        <th>
                                            ParticipantId
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

                                        <th class="text-center">
                                            Units
                                        </th>

                                        <th class="text-end">
                                            Total Amount
                                        </th>

                                        <th class="text-center">
                                            Actual Claims
                                        </th>

                                        <th>
                                            Claim #
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    @foreach ($rows as $row)
                                        <tr>

                                            <td>

                                                {{ $row->ParticipantId }}

                                            </td>


                                            <td>

                                                {{ $row->member_name }}

                                            </td>


                                            <td>

                                                {{ $row->insurance_name }}

                                            </td>


                                            <td>

                                                @if ($row->billing_from)
                                                    {{ \Carbon\Carbon::parse($row->billing_from)->format('m/d/Y') }}
                                                @endif

                                            </td>


                                            <td>

                                                @if ($row->billing_to)
                                                    {{ \Carbon\Carbon::parse($row->billing_to)->format('m/d/Y') }}
                                                @endif

                                            </td>


                                            <td class="text-center">

                                                {{ number_format($row->total_units ?? 0) }}

                                            </td>


                                            <td class="text-end">

                                                ${{ number_format($row->total_amount ?? 0, 2) }}

                                            </td>


                                            <td class="text-center">

                                                {{ $row->actual_claim_count }}

                                            </td>


                                            <td>

                                                {{ $row->claim_numbers }}

                                            </td>

                                        </tr>
                                    @endforeach


                                </tbody>


                                {{-- INSURANCE TOTAL --}}

                                <tfoot class="table-light">

                                    <tr>

                                        <th colspan="5" class="text-end">

                                            Insurance Total:

                                        </th>


                                        <th class="text-center">

                                            {{ number_format($insuranceUnits) }}

                                        </th>


                                        <th class="text-end">

                                            ${{ number_format($insuranceAmount, 2) }}

                                        </th>


                                        <th class="text-center">

                                            {{ number_format($insuranceActualClaims) }}

                                        </th>


                                        <th>

                                            Biller Claims:
                                            {{ number_format($insuranceBillerClaims) }}

                                        </th>

                                    </tr>

                                </tfoot>


                            </table>

                        </div>

                    </div>

                </div>


            @empty


                <div class="alert alert-info">

                    No billing groups were found for this batch.

                </div>
            @endforelse


            {{-- FINAL TOTAL --}}

            <div class="card">

                <div class="card-header">

                    <strong>
                        Final Batch Total
                    </strong>

                </div>


                <div class="card-body">

                    <table class="table table-bordered table-sm mb-0">

                        <tr>

                            <th width="25%">
                                Biller Claims
                            </th>

                            <td width="25%">
                                {{ number_format($summary['biller_claims']) }}
                            </td>

                            <th width="25%">
                                Actual Claims Submitted
                            </th>

                            <td width="25%">
                                {{ number_format($summary['actual_claims']) }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Total Units
                            </th>

                            <td>
                                {{ number_format($summary['units']) }}
                            </td>

                            <th>
                                Total Amount
                            </th>

                            <td>
                                ${{ number_format($summary['amount'], 2) }}
                            </td>

                        </tr>

                    </table>

                </div>

            </div>


        </div>

    </div>

@endsection
