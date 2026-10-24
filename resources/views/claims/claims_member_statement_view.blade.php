@extends('admin.admin_dashboard')

@section('title', 'Member Statement')

@section('admin')
    <div class="page-content">
        <div class="container-fluid">

            <div class="card shadow-sm">

                <div class="card-header bg-white d-flex justify-content-between align-items-center">

                    <div>
                        <h4 class="mb-0">Member Statement / Ledger</h4>
                    </div>

                    <div class="d-flex gap-2">

                        <a href="{{ route('claims.member.statement') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back
                        </a>

                        <a href="{{ route('claims.member.statement.pdf', [
                            'participant_id' => $member->ParticipantId,
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo,
                        ]) }}"
                            target="_blank" class="btn btn-danger">
                            <i class="fas fa-file-pdf me-1"></i>
                            PDF
                        </a>

                        <a href="{{ route('claims.member.statement.excel', [
                            'participant_id' => $member->ParticipantId,
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo,
                        ]) }}"
                            class="btn btn-success">
                            <i class="fas fa-file-excel me-1"></i>
                            Excel
                        </a>

                    </div>

                </div>

                <div class="card-body">

                    <div class="row mb-3">

                        <div class="col-md-6">

                            <table class="table table-sm table-borderless mb-0">

                                <tr>
                                    <th style="width: 170px;">Member:</th>
                                    <td>
                                        {{ $member->LastName ?? '' }},
                                        {{ $member->FirstName ?? '' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Participant ID:</th>
                                    <td>
                                        {{ $member->ParticipantId }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Medicaid #:</th>
                                    <td>
                                        {{ $member->MedicaidNo ?? '' }}
                                    </td>
                                </tr>

                            </table>

                        </div>

                        <div class="col-md-6">

                            <table class="table table-sm table-borderless mb-0">

                                <tr>
                                    <th style="width: 170px;">Statement Period:</th>
                                    <td>
                                        {{ $dateFromFormatted }}
                                        -
                                        {{ $dateToFormatted }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Opening Balance:</th>
                                    <td class="fw-bold">
                                        ${{ number_format($openingBalance, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Closing Balance:</th>
                                    <td class="fw-bold">
                                        ${{ number_format($closingBalance, 2) }}
                                    </td>
                                </tr>

                            </table>

                        </div>

                    </div>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover table-sm align-middle">

                            <thead class="table-light">

                                <tr>
                                    <th style="width: 90px;">Date</th>
                                    <th style="width: 90px;">Claim #</th>
                                    <th style="width: 170px;">Service Period</th>
                                    <th>Insurance Company</th>
                                    <th class="text-end" style="width: 115px;">Charge</th>
                                    <th class="text-end" style="width: 115px;">Payment</th>
                                    <th class="text-end" style="width: 125px;">Patient Resp.</th>
                                    <th class="text-end" style="width: 115px;">Balance</th>
                                </tr>

                            </thead>

                            <tbody>

                                @forelse ($transactions as $transaction)
                                    <tr>

                                        <td>
                                            {{ \Carbon\Carbon::parse($transaction->service_from)->format('m/d/Y') }}
                                        </td>

                                        <td>
                                            {{ $transaction->claim_no }}
                                        </td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($transaction->service_from)->format('m/d/Y') }}
                                            -
                                            {{ \Carbon\Carbon::parse($transaction->service_to)->format('m/d/Y') }}
                                        </td>

                                        <td>
                                            {{ $transaction->insurance_name }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($transaction->charge_amount, 2) }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($transaction->paid_amount, 2) }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($transaction->patient_responsibility, 2) }}
                                        </td>

                                        <td class="text-end fw-semibold">
                                            {{ number_format($transaction->balance, 2) }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No claim transactions found for this member and date range.
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>

                            <tfoot>

                                <tr class="table-secondary fw-bold">

                                    <td colspan="4" class="text-end">
                                        TOTAL
                                    </td>

                                    <td class="text-end">
                                        {{ number_format($totalCharge, 2) }}
                                    </td>

                                    <td class="text-end">
                                        {{ number_format($totalPayment, 2) }}
                                    </td>

                                    <td class="text-end">
                                        {{ number_format($totalPatientResp, 2) }}
                                    </td>

                                    <td class="text-end">
                                        {{ number_format($closingBalance, 2) }}
                                    </td>

                                </tr>

                            </tfoot>

                        </table>

                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')

    <script>
        $(document).ready(function() {
            // Reserved for future enhancements such as DataTables.
        });
    </script>

@endsection
