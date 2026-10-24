@extends('admin.admin_dashboard')
@section('title', 'ERA File Details')

@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <div class="mb-3">
            <a href="{{ route('era.index') }}" class="btn btn-secondary btn-sm">&laquo; Back to ERA List</a>
        </div>

        {{-- HEADER --}}
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">ERA File Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-1">
                    <div class="col-md-4">
                        <strong>ERA File:</strong> {{ $file->file_name }}<br>
                        <strong>Payer:</strong> {{ $file->payer_name }}<br>
                        <strong>Trace / EFT #:</strong> {{ $file->trace_number }}
                    </div>
                    <div class="col-md-4">
                        <strong>Payment Date:</strong>
                        @if ($file->payment_date)
                            {{ \Carbon\Carbon::parse($file->payment_date)->format('m/d/Y') }}
                        @endif
                        <br>
                        <strong>Payment Amount:</strong>
                        ${{ number_format($file->payment_amount ?? 0, 2) }}
                    </div>
                    <div class="col-md-4">
                        <strong>Status:</strong>
                        {{ ucfirst($file->status ?? 'ready') }}<br>
                        @if(!empty($file->status_message))
                            <span class="text-muted small">{{ $file->status_message }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mt-2 small">
                    <div class="col-md-4">
                        <strong>Total Charge (CLP):</strong> {{ number_format($totCharge, 2) }}
                    </div>
                    <div class="col-md-4">
                        <strong>Total Paid (CLP):</strong> {{ number_format($totPaid, 2) }}
                    </div>
                    <div class="col-md-4">
                        <strong>Total Patient Resp:</strong> {{ number_format($totPR, 2) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- CLAIMS LIST --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Claims (CLP) in this ERA</h5>
            </div>
            <div class="card-body">
                @if($claims->isEmpty())
                    <p class="text-muted mb-0">No claims were parsed for this ERA file.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Claim No (CLP01)</th>
                                    <th>Payer Claim #</th>
                                    <th>Status</th>
                                    <th>Member ID</th>
                                    <th class="text-end">Charge</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Pt Resp</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($claims as $i => $c)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $c->patient_control_number }}</td>
                                    <td>{{ $c->payer_claim_number }}</td>
                                    <td>{{ $c->claim_status_code }}</td>
                                    <td>{{ $c->member_id }}</td>
                                    <td class="text-end">{{ number_format($c->total_charge ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($c->total_paid ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($c->patient_responsibility ?? 0, 2) }}</td>
                                    <td>
                                        <a href="{{ route('era.claim.show', $c->id) }}"
                                           class="btn btn-sm btn-outline-primary">
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
