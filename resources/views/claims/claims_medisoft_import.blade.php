@extends('admin.admin_dashboard')

@section('title', 'Import Medisoft Claims')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        {{-- =========================================================
             PAGE TITLE
        ========================================================== --}}

        <div class="row">
            <div class="col-12">

                <div class="page-title-box d-sm-flex
                            align-items-center
                            justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Import Medisoft Claims CSV
                    </h4>

                    <div class="page-title-right">

                        <a href="{{ route('claims.create') }}"
                           class="btn btn-secondary">
                            Back to Claims
                        </a>

                    </div>

                </div>

            </div>
        </div>


        {{-- =========================================================
             SUCCESS MESSAGE
        ========================================================== --}}

        @if(session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif


        {{-- =========================================================
             ERROR MESSAGE
        ========================================================== --}}

        @if(session('error'))

            <div class="alert alert-danger">
                {{ session('error') }}
            </div>

        @endif


        @if($errors->any())

            <div class="alert alert-danger">

                <strong>
                    Please correct the following:
                </strong>

                <ul class="mb-0 mt-2">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- =========================================================
             IMPORT INSTRUCTIONS
        ========================================================== --}}

        <div class="card shadow-sm mb-3">

            <div class="card-header bg-light">

                <h5 class="mb-0">
                    Medisoft CSV File Requirements
                </h5>

            </div>

            <div class="card-body">

                <p class="mb-2">

                    Upload the CSV file exported from Medisoft.

                    The file will first be
                    <strong>previewed and validated</strong>.

                    No claims will be created during this step.

                </p>


                <div class="alert alert-info mb-3">

                    <strong>Important:</strong>

                    Participant IDs from Medisoft will be matched
                    to the ADC member master.

                    For example:

                    <strong>5</strong>
                    will match
                    <strong>005</strong>.

                </div>


                {{-- =================================================
                     EXPECTED CSV COLUMNS
                ================================================== --}}

                <h6 class="fw-bold">
                    Expected Medisoft Columns
                </h6>

                <div class="table-responsive">

                    <table class="table table-sm
                                  table-bordered
                                  align-middle">

                        <thead class="table-light">

                            <tr>

                                <th>
                                    Medisoft CSV Column
                                </th>

                                <th>
                                    ADC Claims Field
                                </th>

                                <th>
                                    Import Rule
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr>

                                <td>
                                    id
                                </td>

                                <td>
                                    ParticipantId
                                </td>

                                <td>
                                    Example:
                                    5 → 005
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    start_date
                                </td>

                                <td>
                                    service_from
                                </td>

                                <td>
                                    Required
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    end_date
                                </td>

                                <td>
                                    service_to
                                </td>

                                <td>
                                    Required
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    procedure_cod
                                </td>

                                <td>
                                    —
                                </td>

                                <td>
                                    Ignored during initial import
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    modifier_1
                                </td>

                                <td>
                                    m1
                                </td>

                                <td>
                                    Normally U1
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    —
                                </td>

                                <td>
                                    proc_code
                                </td>

                                <td>
                                    S5102
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    units
                                </td>

                                <td>
                                    units
                                </td>

                                <td>
                                    Required
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    total
                                </td>

                                <td>
                                    total_amount
                                </td>

                                <td>
                                    Required
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    diag1
                                </td>

                                <td>
                                    diag1
                                </td>

                                <td>
                                    Primary diagnosis
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    diag2
                                </td>

                                <td>
                                    diag2
                                </td>

                                <td>
                                    Optional
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    allowed
                                </td>

                                <td>
                                    allowed
                                </td>

                                <td>
                                    Allowed amount
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    claim_number
                                </td>

                                <td>
                                    claim_no
                                </td>

                                <td>
                                    Will be padded to 6 digits
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    insurace_paid
                                </td>

                                <td>
                                    ins1_paid
                                </td>

                                <td>
                                    Negative Medisoft values will
                                    be converted to positive values
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <div class="alert alert-warning mb-0">

                    <strong>Note:</strong>

                    Insurance, payer information,
                    member information and prior authorization
                    information will be obtained from the
                    ADC database wherever required.

                </div>

            </div>

        </div>


        {{-- =========================================================
             EXAMPLE
        ========================================================== --}}

        <div class="card shadow-sm mb-3">

            <div class="card-header bg-light">

                <h5 class="mb-0">
                    Example Conversion
                </h5>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-sm table-bordered">

                        <thead class="table-light">

                            <tr>

                                <th>
                                    Medisoft
                                </th>

                                <th>
                                    ADC
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr>

                                <td>
                                    Participant ID = 5
                                </td>

                                <td>
                                    ParticipantId = 005
                                </td>

                            </tr>

                            <tr>

                                <td>
                                    Claim Number = 392
                                </td>

                                <td>
                                    claim_no = 000392
                                </td>

                            </tr>

                            <tr>

                                <td>
                                    Insurance Paid = -473.30
                                </td>

                                <td>
                                    ins1_paid = 473.30
                                </td>

                            </tr>

                            <tr>

                                <td>
                                    Modifier = U1
                                </td>

                                <td>
                                    m1 = U1
                                </td>

                            </tr>

                            <tr>

                                <td>
                                    —
                                </td>

                                <td>
                                    proc_code = S5102
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- =========================================================
             FILE UPLOAD
        ========================================================== --}}

        <div class="card shadow-sm">

            <div class="card-header bg-primary text-white">

                <h5 class="mb-0 text-white">
                    Upload Medisoft Claims CSV
                </h5>

            </div>

            <div class="card-body">

                <form
                    method="POST"
                    action="{{ route('claims.medisoft.import.preview') }}"
                    enctype="multipart/form-data"
                >

                    @csrf


                    <div class="row">

                        <div class="col-md-8">

                            <div class="mb-3">

                                <label
                                    for="csv_file"
                                    class="form-label fw-bold"
                                >
                                    Medisoft CSV File
                                </label>

                                <input
                                    type="file"
                                    name="csv_file"
                                    id="csv_file"
                                    class="form-control"
                                    accept=".csv,text/csv"
                                    required
                                >

                                <small class="text-muted">

                                    Select the .csv file exported
                                    from Medisoft.

                                </small>

                            </div>

                        </div>


                        <div class="col-md-4">

                            <div class="mb-3">

                                <label class="form-label fw-bold">
                                    Import Mode
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="Preview / Validate First"
                                    readonly
                                >

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         TEST MODE MESSAGE
                    ================================================== --}}

                    <div class="alert alert-secondary">

                        <strong>Testing Recommendation:</strong>

                        For the initial test, upload only the
                        Medisoft claims for the two participants
                        you selected.

                        We will compare those claims with the
                        existing ADC records before performing
                        the full historical import.

                    </div>


                    {{-- =================================================
                         BUTTONS
                    ================================================== --}}

                    <div class="d-flex
                                justify-content-between
                                align-items-center">

                        <a
                            href="{{ route('claims.create') }}"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </a>


                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="fas fa-search me-1"></i>

                            Upload & Preview Claims

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection
