@extends('admin.admin_dashboard')

@section('admin')
    <div class="page-content">

        <div class="container-fluid">

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <div>
                                    <h4 class="card-title mb-1">
                                        Claim Batch Resubmission
                                    </h4>

                                    <p class="text-muted mb-0">
                                        Select a previously generated 837P batch to review its claims.
                                    </p>
                                </div>

                            </div>


                            <div class="table-responsive">

                                <table id="batchTable" class="table table-bordered table-striped align-middle">

                                    <thead>

                                        <tr>

                                            <th>Batch ID</th>

                                            <th>Batch Date</th>

                                            <th>837P File</th>

                                            <th>PDF File</th>

                                            <th class="text-end">
                                                Total Claims
                                            </th>

                                            <th class="text-end">
                                                Total Amount
                                            </th>

                                            <th>Created By</th>

                                            <th class="text-center" style="width: 130px;">
                                                Action
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        @forelse ($batches as $batch)
                                            <tr>

                                                <td>
                                                    {{ $batch->id }}
                                                </td>


                                                <td data-order="{{ $batch->batch_timestamp }}">

                                                    @if ($batch->batch_timestamp)
                                                        {{ \Carbon\Carbon::parse($batch->batch_timestamp)->format('m/d/Y h:i A') }}
                                                    @endif

                                                </td>


                                                <td>
                                                    {{ $batch->edi_filename }}
                                                </td>


                                                <td>
                                                    {{ $batch->pdf_filename }}
                                                </td>


                                                <td class="text-end">

                                                    {{ number_format($batch->total_claims ?? 0) }}

                                                </td>


                                                <td class="text-end">

                                                    ${{ number_format($batch->total_amount ?? 0, 2) }}

                                                </td>


                                                <td>
                                                    {{ $batch->created_by }}
                                                </td>


                                                <td class="text-center">

                                                    <a href="{{ route('claims.resubmit.batches.show', $batch->id) }}"
                                                        class="btn btn-sm btn-primary">
                                                        View Claims
                                                    </a>

                                                </td>

                                            </tr>

                                        @empty

                                            <tr>

                                                <td colspan="8" class="text-center text-muted">
                                                    No claim batches found.
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


    <script>
        $(document).ready(function() {

            $('#batchTable').DataTable({

                pageLength: 25,

                order: [
                    [0, 'desc']
                ],

                columnDefs: [{
                    targets: 7,
                    orderable: false,
                    searchable: false
                }]

            });

        });
    </script>
@endsection
