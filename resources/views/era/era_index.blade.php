@extends('admin.admin_dashboard')

@section('title', '835 ERA Files')

@section('admin')

    <div class="page-content">

        <div class="container-fluid">

            {{-- FLASH MESSAGE --}}
            @if (session('message'))
                <div class="alert alert-{{ session('alert-type', 'info') }} alert-dismissible fade show">
                    {{ session('message') }}

                    <button type="button" class="btn-close" data-bs-dismiss="alert">
                    </button>
                </div>
            @endif


            {{-- ================== UPLOAD CARD ================== --}}
            <div class="card mb-3">

                <div class="card-header">
                    <h5 class="mb-0">Upload 835 ERA File</h5>
                </div>

                <div class="card-body">

                    <form action="{{ route('era.upload') }}" method="POST" enctype="multipart/form-data" class="row g-2">

                        @csrf

                        <div class="col-md-6">

                            <input type="file" name="era_file" class="form-control" required>

                            @error('era_file')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        <div class="col-auto">

                            <button type="submit" class="btn btn-primary">

                                Upload &amp; Parse

                            </button>

                        </div>

                    </form>

                </div>

            </div>


            {{-- ================== ERA LIST (DATATABLE) ================== --}}
            <div class="card">

                <div class="card-header">

                    <h5 class="mb-0">
                        ERA Payment Batches
                    </h5>

                </div>


                <div class="card-body">

                    <div class="table-responsive">

                        <table id="eraTable" class="table table-bordered table-striped table-hover w-100">

                            <thead class="table-light">

                                <tr>

                                    <th>#</th>

                                    <th>ERA Id</th>

                                    <th>Payment Date</th>

                                    <th>Payer</th>

                                    <th>Trace / EFT #</th>

                                    <th>File Name</th>

                                    <th class="text-end">
                                        Payment Amount
                                    </th>

                                    <th class="text-end">
                                        # Claims
                                    </th>

                                    <th>Status</th>

                                    <th style="width: 220px;">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @forelse($files as $index => $file)
                                    @php

                                        $claimsCount = $claimsCounts[$file->id] ?? 0;

                                        $paymentDate = $file->payment_date
                                            ? \Carbon\Carbon::parse($file->payment_date)->format('m/d/Y')
                                            : '';

                                        $paymentAmount = number_format($file->payment_amount ?? 0, 2);

                                    @endphp


                                    <tr>

                                        {{-- Row # --}}
                                        <td>
                                            {{ $index + 1 }}
                                        </td>


                                        {{-- ERA ID --}}
                                        <td>
                                            {{ $file->id }}
                                        </td>


                                        {{-- Payment Date --}}
                                        <td>
                                            {{ $paymentDate }}
                                        </td>


                                        {{-- Payer --}}
                                        <td>

                                            <strong>
                                                {{ $file->payer_name }}
                                            </strong>

                                        </td>


                                        {{-- Trace / EFT --}}
                                        <td>
                                            {{ $file->trace_number }}
                                        </td>


                                        {{-- File Name --}}
                                        <td>
                                            {{ $file->file_name }}
                                        </td>


                                        {{-- Payment Amount --}}
                                        <td class="text-end">

                                            ${{ $paymentAmount }}

                                        </td>


                                        {{-- # Claims --}}
                                        <td class="text-end">

                                            {{ $claimsCount }}

                                        </td>


                                        {{-- Status --}}
                                        <td>

                                            @if ($file->status === 'processed')
                                                <span class="badge bg-success">
                                                    Processed
                                                </span>
                                            @elseif($file->status === 'error')
                                                <span class="badge bg-danger">
                                                    Error
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    Ready
                                                </span>
                                            @endif


                                            @if (!empty($file->status_message))
                                                <div class="small text-muted">

                                                    {{ \Illuminate\Support\Str::limit($file->status_message, 60) }}

                                                </div>
                                            @endif

                                        </td>


                                        {{-- Actions --}}
                                        <td>

                                            <div class="d-flex gap-2">


                                                {{-- Details --}}
                                                <a href="{{ route('era.file.show', $file->id) }}"
                                                    class="btn btn-sm btn-outline-secondary flex-fill text-center">

                                                    View Details

                                                </a>


                                                {{-- PDF --}}
                                                <a href="{{ route('era.pdf', $file->id) }}"
                                                    class="btn btn-sm btn-outline-secondary flex-fill text-center">

                                                    PDF

                                                </a>


                                                {{-- CSV --}}
                                                <a href="{{ route('era.csv', $file->id) }}"
                                                    class="btn btn-sm btn-outline-success flex-fill text-center">

                                                    CSV

                                                </a>


                                                {{-- Process --}}
                                                <form
                                                    action="{{ route('era.process', $file->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm(
                                                      'Post/process this ERA file?'
                                                  );"
                                                    class="flex-fill">

                                                    @csrf


                                                    <button type="submit" class="btn btn-sm btn-outline-primary w-100"
                                                        @if ($file->status === 'processed') disabled @endif>

                                                        Process

                                                    </button>

                                                </form>


                                            </div>

                                        </td>

                                    </tr>


                                @empty

                                    <tr>

                                        <td colspan="10" class="text-center text-muted">

                                            No ERA files uploaded yet.

                                        </td>

                                    </tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


        </div>
        {{-- container-fluid --}}

    </div>
    {{-- page-content --}}

@endsection


@section('scripts')

    <script>
        $(document).ready(function() {

            /*
            |--------------------------------------------------------------------------
            | ERA Payment Batches DataTable
            |--------------------------------------------------------------------------
            */

            if ($.fn.DataTable.isDataTable('#eraTable')) {

                $('#eraTable')
                    .DataTable()
                    .destroy();

            }


            $('#eraTable').DataTable({

                pageLength: 25,

                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],

                /*
                |--------------------------------------------------------------------------
                | Enable Column Sorting
                |--------------------------------------------------------------------------
                */
                ordering: true,


                /*
                |--------------------------------------------------------------------------
                | Default Sort
                |
                | Column 1 = ERA ID
                | DESC = newest ERA first
                |--------------------------------------------------------------------------
                */
                order: [
                    [1, 'desc']
                ],


                responsive: true,

                autoWidth: false,


                columnDefs: [

                    /*
                    |--------------------------------------------------------------------------
                    | Column 0 - Row #
                    |--------------------------------------------------------------------------
                    */
                    {
                        targets: 0,
                        width: "40px",
                        orderable: false,
                        searchable: false
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Column 1 - ERA ID
                    |--------------------------------------------------------------------------
                    */
                    {
                        targets: 1,
                        width: "70px"
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Column 2 - Payment Date
                    |--------------------------------------------------------------------------
                    */
                    {
                        targets: 2,
                        width: "100px"
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Column 3 - Payer
                    |--------------------------------------------------------------------------
                    */
                    {
                        targets: 3,
                        width: "160px"
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Column 4 - Trace / EFT #
                    |--------------------------------------------------------------------------
                    */
                    {
                        targets: 4,
                        width: "140px"
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Column 5 - File Name
                    |--------------------------------------------------------------------------
                    */
                    {
                        targets: 5,
                        width: "200px"
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Column 6 - Payment Amount
                    |--------------------------------------------------------------------------
                    */
                    {
                        targets: 6,
                        width: "110px"
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Column 7 - # Claims
                    |--------------------------------------------------------------------------
                    */
                    {
                        targets: 7,
                        width: "80px"
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Column 8 - Status
                    |--------------------------------------------------------------------------
                    */
                    {
                        targets: 8,
                        width: "180px"
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Column 9 - Actions
                    |--------------------------------------------------------------------------
                    */
                    {
                        targets: 9,
                        width: "240px",
                        orderable: false,
                        searchable: false
                    }

                ]

            });

        });
    </script>

@endsection
