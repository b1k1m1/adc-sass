@extends('admin.admin_dashboard')

@section('title', 'Claim Batches')

@section('admin')

    <div class="page-content">

        <div class="container-fluid">

            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">

                        Claim Batch History

                    </h5>

                    <a href="{{ route('claims.send.preview') }}" class="btn btn-secondary btn-sm">

                        Back to Send / Generate 837P

                    </a>

                </div>



                <div class="card-body">

                    {{-- Success / Warning / Error Messages --}}

                    @if (session('message'))
                        <div
                            class="alert alert-{{ session('alert-type') == 'error' ? 'danger' : session('alert-type', 'info') }} alert-dismissible fade show">

                            {{ session('message') }}

                            <button type="button" class="btn-close" data-bs-dismiss="alert">

                            </button>

                        </div>
                    @endif



                    @if ($batches->isEmpty())

                        <div class="alert alert-info mb-0">

                            No batches found yet.

                            Generate 837P + PDF to create the first batch.

                        </div>
                    @else
                        <div class="table-responsive">

                            <table id="claimBatchTable" class="table table-bordered table-striped align-middle"
                                style="width:100%;">

                                <thead class="table-light">

                                    <tr>

                                        <th>#</th>

                                        <th>

                                            Batch Date / Time

                                        </th>

                                        <th class="text-end">

                                            Total Claims

                                        </th>

                                        <th class="text-end">

                                            Total Amount

                                        </th>

                                        <th>

                                            Created By

                                        </th>

                                        <th class="text-center">

                                            View

                                        </th>

                                        <th class="text-center">

                                            Claim.MD

                                        </th>

                                        <th class="text-center">

                                            Files

                                        </th>

                                        <th class="text-center">
                                            Biller Report
                                        </th>

                                    </tr>

                                </thead>



                                <tbody>

                                    @foreach ($batches as $batch)
                                        <tr>

                                            {{-- Batch ID --}}

                                            <td>

                                                {{ $batch->id }}

                                            </td>



                                            {{-- Batch Date / Time --}}

                                            <td
                                                data-order="{{ $batch->batch_timestamp ? $batch->batch_timestamp->format('YmdHis') : '' }}">

                                                @if ($batch->batch_timestamp)
                                                    {{ $batch->batch_timestamp->format('m/d/Y h\:i A') }}
                                                @endif

                                            </td>



                                            {{-- Total Claims --}}

                                            <td class="text-end">

                                                {{ number_format($batch->total_claims ?? 0) }}

                                            </td>



                                            {{-- Total Amount --}}

                                            <td class="text-end" data-order="{{ $batch->total_amount ?? 0 }}">

                                                ${{ number_format($batch->total_amount ?? 0, 2) }}

                                            </td>



                                            {{-- Created By --}}

                                            <td>

                                                {{ $batch->created_by ?? '' }}

                                            </td>



                                            {{-- View Batch --}}

                                            <td class="text-center">

                                                <a href="{{ route(
                                                    'claims.batches.show',
                                                
                                                    $batch->id,
                                                ) }}"
                                                    class="btn btn-sm btn-primary">

                                                    View

                                                </a>

                                            </td>

                                            {{-- Claim Md --}}

                                            <td class="text-center">

                                                @if ($batch->edi_filename)
                                                    <form method="POST"
                                                        action="{{ route(
                                                            'claims.batches.claimmd.upload',
                                                        
                                                            $batch->id,
                                                        ) }}"
                                                        class="d-inline claimmd-upload-form">

                                                        @csrf

                                                        <button type="submit" class="btn btn-sm btn-success">

                                                            Upload

                                                        </button>

                                                    </form>
                                                @endif

                                            </td>



                                            {{-- Files --}}

                                            <td class="text-center text-nowrap">

                                                @if ($batch->pdf_filename)
                                                    <a href="{{ route('claims.batches.pdf', $batch->id) }}" target="_blank"
                                                        class="btn btn-sm btn-outline-primary">

                                                        <i class="fas fa-file-pdf"></i> Open PDF

                                                    </a>
                                                @endif



                                                @if ($batch->edi_filename)
                                                    <a href="{{ route('claims.batches.edi', $batch->id) }}"
                                                        class="btn btn-sm btn-outline-secondary">

                                                        <i class="fas fa-download"></i> Download 837P

                                                    </a>
                                                @endif

                                            </td>

                                            <td class="text-center text-nowrap">
                                                <a href="{{ route('claims.biller.batch.report', $batch->id) }}"
                                                    class="btn btn-info btn-sm">

                                                    Biller Report

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

{{-- DataTable --}}

@if (!$batches->isEmpty())
    @section('scripts')

        <script>
            $(document).ready(function() {

                $('#claimBatchTable').DataTable({

                    pageLength: 25,

                    lengthMenu: [

                        [25, 50, 100, -1],

                        [25, 50, 100, 'All']

                    ],

                    order: [

                        [1, 'desc']

                    ],

                    columnDefs: [

                        {

                            targets: [5, 6, 7, 8],

                            orderable: false,

                            searchable: false

                        },

                        {

                            targets: [0, 2, 3],

                            className: 'text-end'

                        },

                        {

                            targets: [5, 6, 7, 8],

                            className: 'text-center'

                        }

                    ],

                    responsive: false,

                    scrollX: true,

                    autoWidth: false,

                    language: {

                        search: "Search Batches:",

                        lengthMenu:

                            "Show _MENU_ batches",

                        info:

                            "Showing _START_ to _END_ of _TOTAL_ batches",

                        infoEmpty:

                            "No batches available",

                        zeroRecords:

                            "No matching batches found"

                    }

                });

                $(document). ** on ** (

                    'submit',

                    '.claimmd-upload-form',

                    function(e) {

                        let confirmed = confirm(

                            'Upload this 837P batch to Claim.MD?'

                        );

                        if (!confirmed) {

                            e.preventDefault();

                        }

                    }

                );

            });
        </script>
    @endif

@endsection
