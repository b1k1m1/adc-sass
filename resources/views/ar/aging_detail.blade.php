@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-sm-0">A/R Aging Detail</h4>
                        <div class="text-muted mt-1">
                            Insurance: <strong>{{ $insurance }}</strong>
                            &nbsp; | &nbsp;
                            As of: <strong>{{ $asOfDisplay }}</strong>
                        </div>
                    </div>

                    <div class="page-title-right">
                        <a href="{{ route('ar.aging.summary', ['as_of_date' => $asOfDisplay]) }}"
                           class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Summary
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Open Balance</h6>
                        <h3 class="text-white mb-0">${{ number_format($totals['balance_amount'], 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Open Claims</h6>
                        <h3 class="text-white mb-0">{{ number_format($totals['claim_count']) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h6 class="text-dark-50">Total Billed</h6>
                        <h3 class="text-dark mb-0">${{ number_format($totals['total_amount'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Table --}}
        <div class="card">
            <div class="card-body">

                <h5 class="card-title mb-3">
                    Open Claims
                </h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Claim No</th>
                                <th>Member ID</th>
                                <th>Member Name</th>
                                <th>Service From</th>
                                <th>Service To</th>
                                <th>Submitted</th>
                                <th class="text-end">Aging Days</th>
                                <th class="text-end">Billed</th>
                                <th class="text-end">Allowed</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Adjustment</th>
                                <th class="text-end">Patient Resp</th>
                                <th class="text-end">Deduction</th>
                                <th class="text-end">Write-off</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($claims as $claim)
                                @php
                                    $member = $members[$claim->ParticipantId] ?? null;
                                    $memberName = $member
                                        ? trim(($member->LastName ?? '') . ', ' . ($member->FirstName ?? ''))
                                        : '';
                                @endphp

                                <tr>
                                    <td>
                                        <a href="{{ route('ar.claim.transactions', $claim->id) }}">
                                            {{ $claim->claim_no }}
                                        </a>
                                    </td>
                                    <td>{{ $claim->ParticipantId }}</td>
                                    <td>{{ $memberName }}</td>
                                    <td>
                                        {{ $claim->service_from ? \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') : '' }}
                                    </td>
                                    <td>
                                        {{ $claim->service_to ? \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') : '' }}
                                    </td>
                                    <td>
                                        {{ $claim->submitted_date ? \Carbon\Carbon::parse($claim->submitted_date)->format('m/d/Y') : '' }}
                                    </td>
                                    <td class="text-end">{{ $claim->aging_days }}</td>
                                    <td class="text-end">${{ number_format($claim->total_amount, 2) }}</td>
                                    <td class="text-end">${{ number_format($claim->allowed, 2) }}</td>
                                    <td class="text-end">${{ number_format($claim->ins1_paid, 2) }}</td>
                                    <td class="text-end">${{ number_format($claim->adjustment_amount, 2) }}</td>
                                    <td class="text-end">${{ number_format($claim->patient_responsibility, 2) }}</td>
                                    <td class="text-end">${{ number_format($claim->deduction_amount, 2) }}</td>
                                    <td class="text-end">${{ number_format($claim->writeoff_amount, 2) }}</td>
                                    <td class="text-end fw-bold">${{ number_format($claim->balance_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($claim->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="16" class="text-center text-muted">
                                        No open claims found for this insurance.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        <tfoot class="table-light">
                            <tr>
                                <th colspan="7">Total</th>
                                <th class="text-end">${{ number_format($totals['total_amount'], 2) }}</th>
                                <th class="text-end">${{ number_format($totals['allowed'], 2) }}</th>
                                <th class="text-end">${{ number_format($totals['ins1_paid'], 2) }}</th>
                                <th class="text-end">${{ number_format($totals['adjustment_amount'], 2) }}</th>
                                <th class="text-end">${{ number_format($totals['patient_responsibility'], 2) }}</th>
                                <th class="text-end">${{ number_format($totals['deduction_amount'], 2) }}</th>
                                <th class="text-end">${{ number_format($totals['writeoff_amount'], 2) }}</th>
                                <th class="text-end">${{ number_format($totals['balance_amount'], 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
