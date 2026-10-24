@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-sm-0">Claim Transaction History</h4>
                        <div class="text-muted mt-1">
                            Claim No:
                            <strong>{{ $claim->claim_no }}</strong>
                            &nbsp; | &nbsp;
                            Member:
                            <strong>
                                {{ $claim->ParticipantId }}
                                @if($member)
                                    - {{ trim(($member->LastName ?? '') . ', ' . ($member->FirstName ?? '')) }}
                                @endif
                            </strong>
                        </div>
                    </div>

                    <div class="page-title-right">
                        <a href="javascript:history.back()" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Claim Summary --}}
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Billed Amount</h6>
                        <h3 class="text-white mb-0">${{ number_format($claim->total_amount, 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Paid</h6>
                        <h3 class="text-white mb-0">${{ number_format($claim->ins1_paid, 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h6 class="text-dark-50">Adjustments</h6>
                        <h3 class="text-dark mb-0">
                            ${{ number_format(($claim->adjustment_amount ?? 0) + ($claim->deduction_amount ?? 0) + ($claim->writeoff_amount ?? 0), 2) }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Open Balance</h6>
                        <h3 class="text-white mb-0">${{ number_format($claim->balance_amount, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Claim Info --}}
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Claim Information</h5>

                <div class="row">
                    <div class="col-md-3">
                        <strong>Insurance:</strong><br>
                        {{ $claim->insurance_name ?? '' }}
                    </div>

                    <div class="col-md-3">
                        <strong>Service Date:</strong><br>
                        {{ $claim->service_from ? \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') : '' }}
                        -
                        {{ $claim->service_to ? \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') : '' }}
                    </div>

                    <div class="col-md-3">
                        <strong>Submitted:</strong><br>
                        {{ $claim->submitted_date ? \Carbon\Carbon::parse($claim->submitted_date)->format('m/d/Y') : '' }}
                    </div>

                    <div class="col-md-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-secondary">{{ ucfirst($claim->status) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transaction Totals --}}
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Transaction Totals</h5>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-end">Charge</th>
                                <th class="text-end">Payment</th>
                                <th class="text-end">Contractual Adj</th>
                                <th class="text-end">Patient Resp</th>
                                <th class="text-end">Deduction</th>
                                <th class="text-end">Write-off</th>
                                <th class="text-end">Manual Adj</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-end">${{ number_format($totals['charge'], 2) }}</td>
                                <td class="text-end">${{ number_format($totals['payment'], 2) }}</td>
                                <td class="text-end">${{ number_format($totals['contractual_adjustment'], 2) }}</td>
                                <td class="text-end">${{ number_format($totals['patient_responsibility'], 2) }}</td>
                                <td class="text-end">${{ number_format($totals['deduction'], 2) }}</td>
                                <td class="text-end">${{ number_format($totals['writeoff'], 2) }}</td>
                                <td class="text-end">${{ number_format($totals['manual_adjustment'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        {{-- Transactions --}}
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Transactions</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th>Group</th>
                                <th>Reason</th>
                                <th>Payer Claim #</th>
                                <th>Created By</th>
                                <th>Description</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($transactions as $tx)
                                <tr>
                                    <td>
                                        {{ $tx->transaction_date ? \Carbon\Carbon::parse($tx->transaction_date)->format('m/d/Y') : '' }}
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = 'bg-secondary';

                                            if ($tx->transaction_type === 'charge') {
                                                $badgeClass = 'bg-primary';
                                            } elseif ($tx->transaction_type === 'payment') {
                                                $badgeClass = 'bg-success';
                                            } elseif (in_array($tx->transaction_type, ['contractual_adjustment', 'deduction', 'writeoff', 'manual_adjustment'])) {
                                                $badgeClass = 'bg-warning text-dark';
                                            } elseif ($tx->transaction_type === 'patient_responsibility') {
                                                $badgeClass = 'bg-info text-dark';
                                            }
                                        @endphp

                                        <span class="badge {{ $badgeClass }}">
                                            {{ ucwords(str_replace('_', ' ', $tx->transaction_type)) }}
                                        </span>
                                    </td>
                                    <td class="text-end">${{ number_format($tx->amount, 2) }}</td>
                                    <td>{{ $tx->group_code }}</td>
                                    <td>{{ $tx->reason_code }}</td>
                                    <td>{{ $tx->payer_claim_number }}</td>
                                    <td>{{ $tx->created_by }}</td>
                                    <td>{{ $tx->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        No transactions found for this claim.
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
