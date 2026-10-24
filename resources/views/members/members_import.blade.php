@extends('admin.admin_dashboard')
@section('admin')

@php
    use Illuminate\Support\Facades\DB;

    $batchId = request('batch_id');

    $previewRows = collect();
    $readyCount = 0;
    $errorCount = 0;

    if($batchId){
        $previewRows = DB::table('member_import_previews')
            ->where('batch_id',$batchId)
            ->orderBy('row_no')
            ->get();

        $readyCount = $previewRows->where('import_status','READY')->count();
        $errorCount = $previewRows->where('import_status','ERROR')->count();
    }
@endphp

<div class="page-content">
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            {{-- Page Title --}}
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">

                        <h4 class="mb-0">Import Members From Excel</h4>

                        <div class="d-flex gap-2">
                            <a href="{{ route('members.import.guide') }}"
                            target="_blank"
                            class="btn btn-outline-info" >
                                <i class="fas fa-file-pdf"></i> Excel Import Guide
                            </a>

                            <a href="{{ route('members.dashboard') }}"
                            class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- Upload Screen --}}
    @if(!$batchId)

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">

                <div class="card-header bg-light">
                    <h5 class="mb-0">Upload Member Excel File</h5>
                </div>

                <div class="card-body">

                    <form method="POST"
                          action="{{ route('members.import.preview') }}"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Select Excel File (.xls or .xlsx)
                            </label>

                            <input type="file"
                                   name="excel_file"
                                   class="form-control"
                                   accept=".xls,.xlsx"
                                   required>
                        </div>

                        <div class="mb-4">
                            <div class="alert alert-info mb-0">
                                <strong>Import Process:</strong><br>
                                1. Review the <strong>Excel Import Guide</strong><br>
                                2. Upload Excel file<br>
                                3. Review preview before insert<br>
                                4. Click <strong>Yes Import Members</strong><br>
                                5. Participant IDs are auto generated (max + 1 or starts at 1001)
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i>
                                Upload & Preview
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    @endif


    {{-- Preview Screen --}}
    @if($batchId && $previewRows->count() > 0)

    <div class="row mb-3">
        <div class="col-md-12">

            <div class="card border-success">
                <div class="card-body py-3">
                    <div class="row text-center">

                        <div class="col-md-3">
                            <h5>Total Rows</h5>
                            <span class="badge bg-primary fs-6">
                                {{ $previewRows->count() }}
                            </span>
                        </div>

                        <div class="col-md-3">
                            <h5>Ready</h5>
                            <span class="badge bg-success fs-6">
                                {{ $readyCount }}
                            </span>
                        </div>

                        <div class="col-md-3">
                            <h5>Errors</h5>
                            <span class="badge bg-danger fs-6">
                                {{ $errorCount }}
                            </span>
                        </div>

                        <div class="col-md-3">
                            <h5>Starting ID</h5>
                            <span class="badge bg-info fs-6">
                                {{ optional($previewRows->first())->ParticipantId }}
                            </span>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>


    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <div>
                <h5 class="mb-0">Preview Members To Be Added</h5>
                <small class="text-muted">
                    Review data before importing into members table
                </small>
            </div>

            <div>

                <form action="{{ route('members.import.cancel') }}"
                      method="POST"
                      class="d-inline">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $batchId }}">

                    <button class="btn btn-secondary btn-sm"
                            onclick="return confirm('Cancel this import batch?')">
                        Cancel
                    </button>
                </form>

                @if($readyCount > 0)
                <form action="{{ route('members.import.confirm') }}"
                      method="POST"
                      class="d-inline">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $batchId }}">

                    <button class="btn btn-success btn-sm"
                        onclick="return confirm('Proceed to import members?')">
                        Yes Import Members
                    </button>
                </form>
                @endif

            </div>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-striped table-sm align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Row</th>
                        <th>Status</th>
                        <th>ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>MI</th>
                        <th>DOB</th>
                        <th>Sex</th>
                        <th>Phone</th>
                        <th>Shift</th>
                        <th>Insurance</th>
                        <th>Member ID</th>
                        <th>PA #</th>
                        <th>PA Start</th>
                        <th>PA End</th>
                        <th>Error Message</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($previewRows as $row)

                        <tr class="{{ $row->import_status=='ERROR' ? 'table-danger':'' }}">

                            <td>{{ $row->row_no }}</td>

                            <td>
                            @if($row->import_status=='READY')
                                <span class="badge bg-success">
                                    READY
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    ERROR
                                </span>
                            @endif
                            </td>

                            <td>{{ $row->ParticipantId }}</td>
                            <td>{{ $row->LastName }}</td>
                            <td>{{ $row->FirstName }}</td>
                            <td>{{ $row->MiddleName }}</td>

                            <td>
                            @if($row->DateOfBirth)
                                {{ \Carbon\Carbon::parse($row->DateOfBirth)->format('m/d/Y') }}
                            @endif
                            </td>

                            <td>{{ $row->Sex }}</td>
                            <td>{{ $row->Phone1 }}</td>
                            <td>{{ $row->Shift }}</td>
                            <td>{{ $row->HMO_Enrolled }}</td>
                            <td>{{ $row->medicaid_HMO_id }}</td>
                            <td>{{ $row->medicaid_PA_no }}</td>

                            <td>
                            @if($row->medicaid_PA_Start)
                              {{ \Carbon\Carbon::parse($row->medicaid_PA_Start)->format('m/d/Y') }}
                            @endif
                            </td>

                            <td>
                            @if($row->medicaid_PA_End)
                              {{ \Carbon\Carbon::parse($row->medicaid_PA_End)->format('m/d/Y') }}
                            @endif
                            </td>

                            <td>
                                <small class="text-danger">
                                    {{ $row->error_message }}
                                </small>
                            </td>

                        </tr>

                    @endforeach
                    </tbody>
                </table>

            </div>

            <div class="mt-3 alert alert-warning mb-0">
                <strong>Note:</strong>
                Rows flagged ERROR will not be imported.
                Review all preview data before clicking Yes Import Members.
            </div>

        </div>
    </div>

    @endif

</div>
</div>

@endsection

