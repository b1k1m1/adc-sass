@extends('admin.admin_dashboard')

@section('title', 'Legacy 837 Claim Recovery')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">

                <div class="card">

                    <div class="card-header d-flex justify-content-between align-items-center">

                        <div>
                            <h5 class="mb-0">
                                Legacy 837 Claim Recovery
                            </h5>

                            <small class="text-muted">
                                Import affected 837 files with legacy CLM01 numbers
                                0000001 through 0000169.
                            </small>
                        </div>

                    </div>

                    <div class="card-body">

                        {{-- Success --}}
                        @if(session('success'))

                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>

                        @endif


                        {{-- Error --}}
                        @if(session('error'))

                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>

                        @endif


                        {{-- Validation Errors --}}
                        @if($errors->any())

                            <div class="alert alert-danger">

                                <ul class="mb-0">

                                    @foreach($errors->all() as $error)

                                        <li>{{ $error }}</li>

                                    @endforeach

                                </ul>

                            </div>

                        @endif


                        {{-- Upload Form --}}
                        <form
                            method="POST"
                            action="{{ route('claims.legacy837.import.process') }}"
                            enctype="multipart/form-data"
                        >

                            @csrf

                            <div class="row align-items-end">

                                <div class="col-md-7">

                                    <label class="form-label">
                                        Select Affected 837 File
                                    </label>

                                    <input
                                        type="file"
                                        name="837_file"
                                        class="form-control"
                                        accept=".txt,.837"
                                        required
                                    >

                                </div>

                                <div class="col-md-3">

                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >
                                        <i class="fas fa-upload me-1"></i>
                                        Import 837
                                    </button>

                                </div>

                            </div>

                        </form>


                        <hr>


                        {{-- Summary --}}
                        <div class="row mb-3">

                            <div class="col-md-4">

                                <div class="border rounded p-3">

                                    <div class="text-muted">
                                        Total Imported
                                    </div>

                                    <div class="fs-4 fw-bold">
                                        {{ $summary['total'] }}
                                    </div>

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="border rounded p-3">

                                    <div class="text-muted">
                                        Matched
                                    </div>

                                    <div class="fs-4 fw-bold text-success">
                                        {{ $summary['matched'] }}
                                    </div>

                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="border rounded p-3">

                                    <div class="text-muted">
                                        Unmatched
                                    </div>

                                    <div class="fs-4 fw-bold text-danger">
                                        {{ $summary['unmatched'] }}
                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- Imported Mapping --}}
                        <div class="table-responsive">

                            <table
                                id="legacy837Table"
                                class="table table-bordered table-striped table-sm"
                            >

                                <thead>

                                    <tr>

                                        <th>File</th>

                                        <th>CLM01</th>

                                        <th>Participant</th>

                                        <th>Member</th>

                                        <th>Insurance ID</th>

                                        <th>Payer</th>

                                        <th>From</th>

                                        <th>To</th>

                                        <th class="text-end">
                                            Charge
                                        </th>

                                        <th>Status</th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach($rows as $row)

                                        <tr>

                                            <td>
                                                {{ $row->batch_file_name }}
                                            </td>

                                            <td class="fw-bold">
                                                {{ $row->clm01 }}
                                            </td>

                                            <td>

                                                @if($row->ParticipantId)

                                                    <span class="text-success fw-bold">
                                                        {{ $row->ParticipantId }}
                                                    </span>

                                                @else

                                                    <span class="text-danger">
                                                        Not Found
                                                    </span>

                                                @endif

                                            </td>

                                            <td>
                                                {{ $row->last_name }},
                                                {{ $row->first_name }}
                                            </td>

                                            <td>
                                                {{ $row->insurance_member_id }}
                                            </td>

                                            <td>

                                                {{ $row->payer_name }}

                                                @if($row->payer_id)

                                                    <small class="text-muted">
                                                        ({{ $row->payer_id }})
                                                    </small>

                                                @endif

                                            </td>

                                            <td>

                                                @if($row->service_from)

                                                    {{ \Carbon\Carbon::parse($row->service_from)->format('m/d/Y') }}

                                                @endif

                                            </td>

                                            <td>

                                                @if($row->service_to)

                                                    {{ \Carbon\Carbon::parse($row->service_to)->format('m/d/Y') }}

                                                @endif

                                            </td>

                                            <td class="text-end">

                                                ${{ number_format($row->charge_amount, 2) }}

                                            </td>

                                            <td>

                                                @if($row->ParticipantId)

                                                    <span class="badge bg-success">
                                                        Matched
                                                    </span>

                                                @else

                                                    <span class="badge bg-danger">
                                                        Unmatched
                                                    </span>

                                                @endif

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

@endsection


@section('scripts')

<script>

$(document).ready(function () {

    if ($.fn.DataTable) {

        $('#legacy837Table').DataTable({

            pageLength: 25,

            order: [[0, 'asc'], [1, 'asc']],

            scrollX: true

        });

    }

});

</script>

@endsection