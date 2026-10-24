@extends('admin.admin_dashboard')
@section('title', 'Claim Batches')

@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Claim Batch History</h5>
                <a href="{{ route('claims.send.preview') }}" class="btn btn-secondary btn-sm">
                    Back to Send / Generate 837P
                </a>
            </div>

            <div class="card-body">
                @if($batches->isEmpty())
                    <div class="alert alert-info mb-0">
                        No batches found yet. Generate 837P + PDF to create the first batch.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Batch Date / Time</th>
                                    <th>Total Claims</th>
                                    <th>Total Amount</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                    <th>Files</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batches as $batch)
                                    <tr>
                                        <td>{{ $batch->id }}</td>
                                        <td>{{ $batch->batch_timestamp->format('m/d/Y h:i A') }}</td>
                                        <td>{{ $batch->total_claims }}</td>
                                        <td>${{ number_format($batch->total_amount, 2) }}</td>
                                        <td>
                                            {{-- optional: show user name if you load relation later --}}
                                            {{ $batch->created_by ?? '' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('claims.batches.show', $batch->id) }}"
                                            class="btn btn-xs btn-primary mb-1">
                                                View
                                            </a>
                                            {{-- existing PDF / 837P links stay here too --}}
                                        </td>
                                        <td>
                                            @if($batch->pdf_filename)
                                                <a href="{{ asset('storage/claims_batches/' . $batch->pdf_filename) }}"
                                                   target="_blank"
                                                   class="btn btn-xs btn-outline-primary mb-1">
                                                    PDF
                                                </a>
                                            @endif

                                            @if($batch->edi_filename)
                                                <a href="{{ asset('storage/claims_batches/' . $batch->edi_filename) }}"
                                                   download
                                                   class="btn btn-xs btn-outline-secondary mb-1">
                                                    837P
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-2">
                        {{ $batches->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
