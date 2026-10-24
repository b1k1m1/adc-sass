@extends('admin.admin_dashboard')

@section('title', 'Generate Claims From Excel')

@section('admin')

    <div class="page-content">

        <div class="container-fluid">

            <div class="card">

                <div class="card-header">
                    <h5 class="mb-0">
                        Generate Claims From Excel
                    </h5>
                </div>

                <form method="POST" action="{{ route('claims.excel.preview') }}" enctype="multipart/form-data">

                    @csrf

                    <div class="card-body">

                        {{-- =========================================================
                         Required Excel Format
                    ========================================================== --}}

                        <div class="alert alert-info">

                            <h5 class="alert-heading mb-3">
                                Required Excel File Format
                            </h5>

                            <p class="mb-2">
                                Before uploading the billing file, please make sure
                                the Excel worksheet follows the required format below.
                            </p>

                            <div class="table-responsive">

                                <table class="table table-sm table-bordered bg-white mb-3">

                                    <thead class="table-light">

                                        <tr>
                                            <th>Column</th>
                                            <th>Required</th>
                                            <th>Description</th>
                                            <th>Example</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>
                                            <td>
                                                <strong>ID #</strong>
                                            </td>

                                            <td>
                                                <span class="badge bg-danger">
                                                    Yes
                                                </span>
                                            </td>

                                            <td>
                                                Participant ID. The system will match
                                                this value against the members table.
                                            </td>

                                            <td>
                                                1075
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <strong>Primary ICD</strong>
                                            </td>

                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    Recommended
                                                </span>
                                            </td>

                                            <td>
                                                Primary diagnosis code. If blank,
                                                the system may use the member's
                                                Primary Diagnosis.
                                            </td>

                                            <td>
                                                I10
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <strong>Secondary ICD</strong>
                                                <br>
                                                <small>
                                                    "Scondary ICD" is also accepted
                                                </small>
                                            </td>

                                            <td>
                                                <span class="badge bg-secondary">
                                                    Optional
                                                </span>
                                            </td>

                                            <td>
                                                Secondary diagnosis code. If blank,
                                                the system may use the member's
                                                Secondary Diagnosis.
                                            </td>

                                            <td>
                                                I25.10
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <strong>Daily Attendance Columns</strong>
                                            </td>

                                            <td>
                                                <span class="badge bg-danger">
                                                    Yes
                                                </span>
                                            </td>

                                            <td>
                                                One column for each service date,
                                                using the day number only.
                                                Any nonblank value means attended.
                                            </td>

                                            <td>
                                                31, 01, 02, 03, 04
                                            </td>
                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                            <div class="mb-2">

                                <strong>Example:</strong>

                            </div>

                            <div class="table-responsive">

                                <table class="table table-sm table-bordered bg-white mb-3">

                                    <thead class="table-light">

                                        <tr>
                                            <th>ID #</th>
                                            <th>Primary ICD</th>
                                            <th>Secondary ICD</th>
                                            <th>31</th>
                                            <th>01</th>
                                            <th>02</th>
                                            <th>03</th>
                                            <th>04</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>
                                            <td>1075</td>
                                            <td>I10</td>
                                            <td>I25.10</td>
                                            <td>1</td>
                                            <td>1</td>
                                            <td>1</td>
                                            <td>1</td>
                                            <td>1</td>
                                        </tr>

                                        <tr>
                                            <td>1078</td>
                                            <td>I10</td>
                                            <td>M19.90</td>
                                            <td>1</td>
                                            <td></td>
                                            <td>1</td>
                                            <td>1</td>
                                            <td>1</td>
                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                            <ul class="mb-0">
                                <li>
                                    The <strong>Start Date</strong> must be the
                                    <strong>Sunday</strong> of the attendance week, and the
                                    <strong>End Date</strong> must be the following
                                    <strong>Saturday</strong>.
                                </li>

                                <li>
                                    The Excel file must contain at least
                                    <strong>5 service-date columns</strong> within the selected
                                    Sunday–Saturday week. Service dates do not have to be consecutive.
                                </li>

                                <li>
                                    Example: if the facility is closed on Wednesday and provides
                                    replacement service on Saturday, the Excel date columns may be
                                    <strong>14, 15, 17, 18, 19</strong>.
                                </li>

                                <li>
                                    Blank attendance cells are treated as
                                    <strong>not attended</strong>.
                                </li>

                                <li>
                                    Attendance gaps automatically create separate claim segments.
                                </li>

                                <li>
                                    Claims may also be split automatically when the
                                    <strong>PA, procedure/modifier, or service rate</strong> changes.
                                </li>

                                <li>
                                    If an Excel Participant ID is not found in the members table,
                                    that row will be shown as <strong>Invalid</strong> and no claim
                                    will be generated for it.
                                </li>

                                <li>
                                    The file must be an <strong>.xlsx</strong> or
                                    <strong>.xls</strong> file.
                                </li>
                            </ul>

                        </div>


                        {{-- =========================================================
                         Upload Area
                    ========================================================== --}}

                        <div class="row">

                            <div class="col-md-5 mb-3">

                                <label class="form-label">
                                    Billing Excel File
                                </label>

                                <input type="file" name="billing_file" class="form-control" accept=".xlsx,.xls" required>

                                @error('billing_file')
                                    <div class="text-danger mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    Start Date
                                </label>

                                <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}"
                                    required>

                                @error('start_date')
                                    <div class="text-danger mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    End Date
                                </label>

                                <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}"
                                    required>

                                @error('end_date')
                                    <div class="text-danger mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        <div class="alert alert-warning mb-0">

                            <strong>Important:</strong>

                            No claims will be created immediately after upload.
                            The system will first display a preview showing
                            valid and invalid claim rows. You can then select
                            which valid rows you want to generate.

                        </div>

                    </div>


                    <div class="card-footer text-end">

                        <a href="{{ route('claims.draft.index') }}" class="btn btn-secondary">
                            {{ t('common.back', 'Back') }}
                        </a>

                        <button type="submit" class="btn btn-primary">
                            Preview Claims
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection
