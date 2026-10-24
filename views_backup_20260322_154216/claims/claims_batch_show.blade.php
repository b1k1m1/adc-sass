@extends('admin.admin_dashboard')
@section('title', 'Claim Batch Details')

@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Batch #{{ $batch->id }} – Details</h5>
                    <small class="text-muted">
                        Generated: {{ $batch->batch_timestamp->format('m/d/Y h:i A') }}
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('claims.batches.index') }}" class="btn btn-secondary btn-sm">
                        Back to Batch History
                    </a>

                    @if($batch->pdf_filename)
                        <a href="{{ asset('storage/claims_batches/' . $batch->pdf_filename) }}"
                           target="_blank"
                           class="btn btn-outline-primary btn-sm">
                            Open PDF Summary
                        </a>
                    @endif

                    @if($batch->edi_filename)
                        <a href="{{ asset('storage/claims_batches/' . $batch->edi_filename) }}"
                           download
                           class="btn btn-outline-secondary btn-sm">
                            Download 837P
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-body">

                <div class="mb-2">
                    <strong>Total claims:</strong> {{ $totalClaims }} &nbsp; | &nbsp;
                    <strong>Total amount:</strong> ${{ number_format($totalAmount, 2) }}
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Claim ID</th>
                                <th>Claim No</th>
                                <th>ParticipantId</th>
                                <th>Member Name</th>
                                <th>Insurance</th>
                                <th>Svc From</th>
                                <th>Svc To</th>
                                <th>Units</th>
                                <th>Total Amount</th>
                                <th>Pre-check</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($claims as $claim)
                            <tr>
                                <td>{{ $claim->id }}</td>
                                <td>{{ $claim->claim_no ?? '' }}</td>
                                <td>{{ $claim->ParticipantId }}</td>
                                <td>
                                    {{ trim(($claim->member_lastname ?? '') . ', ' . ($claim->member_firstname ?? ''), ', ') }}
                                </td>
                                <td>{{ $claim->insurance_name ?? '' }}</td>
                                <td>{{ \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') }}</td>
                                <td>{{ $claim->units }}</td>
                                <td>${{ number_format($claim->total_amount, 2) }}</td>
                                <td>{{ $claim->precheck_result ?? '' }}</td>
                                <td>{{ $claim->status ?? '' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
