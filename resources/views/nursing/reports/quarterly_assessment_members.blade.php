@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <div>
                        <h4 class="mb-sm-0 font-size-18">
                            Quarterly Assessment Members
                        </h4>
                    </div>

                    <div class="page-title-right">
                        <a href="{{ url()->previous() }}"
                           class="btn btn-primary waves-effect waves-light">
                            {{ t('common.back', 'Back') }}
                        </a>
                    </div>

                </div>
            </div>
        </div>
        {{-- End Page Title --}}

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">

                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>

            </div>
        @endif

        {{-- Search Criteria --}}
        <div class="card">
            <div class="card-body">

                <form method="GET"
                      action="{{ route('nursing.quarterly.assessment.members') }}"
                      id="quarterlyAssessmentForm">

                    <div class="row g-3 align-items-start">

                        {{-- Shift --}}
                        <div class="col-xl-3 col-lg-3 col-md-6">
                            <div class="filter-field-block">

                                <label class="form-label">
                                    Shift
                                </label>

                                <div class="btn-group shift-button-group"
                                    role="group"
                                    aria-label="Shift selection">

                                    <input type="radio"
                                        class="btn-check"
                                        name="shift"
                                        id="shiftMorning"
                                        value="A"
                                        autocomplete="off"
                                        {{ $shift === 'A' ? 'checked' : '' }}>

                                    <label class="btn btn-outline-primary"
                                        for="shiftMorning">
                                        Morning
                                    </label>

                                    <input type="radio"
                                        class="btn-check"
                                        name="shift"
                                        id="shiftNoon"
                                        value="N"
                                        autocomplete="off"
                                        {{ $shift === 'N' ? 'checked' : '' }}>

                                    <label class="btn btn-outline-primary"
                                        for="shiftNoon">
                                        Noon
                                    </label>

                                </div>

                                <div class="filter-help-space"></div>

                            </div>
                        </div>

                        {{-- Assessment Month --}}
                        <div class="col-xl-3 col-lg-3 col-md-6">
                            <div class="filter-field-block">

                                <label for="month" class="form-label">
                                    Assessment Month
                                </label>

                                <input type="text"
                                    name="month"
                                    id="month"
                                    class="form-control text-uppercase"
                                    value="{{ old('month', $selectedMonth) }}"
                                    maxlength="3"
                                    minlength="3"
                                    autocomplete="off"
                                    placeholder="JAN"
                                    required>

                                <div class="filter-help-space text-muted">
                                    Enter three characters, such as JAN, FEB or MAR.
                                </div>

                            </div>
                        </div>

                        {{-- View Report --}}
                        <div class="col-xl-3 col-lg-3 col-md-6">
                            <div class="filter-field-block">

                                <label class="form-label invisible">
                                    View Report
                                </label>

                                <button type="submit"
                                        class="btn btn-primary w-100 report-filter-button">
                                    <i class="mdi mdi-magnify me-1"></i>
                                    View Report
                                </button>

                                <div class="filter-help-space"></div>

                            </div>
                        </div>

                        {{-- Clear --}}
                        <div class="col-xl-3 col-lg-3 col-md-6">
                            <div class="filter-field-block">

                                <label class="form-label invisible">
                                    Clear
                                </label>

                                <a href="{{ route('nursing.quarterly.assessment.members', [
                                        'shift' => $shift
                                    ]) }}"
                                class="btn btn-outline-secondary w-100 report-filter-button">
                                    Clear
                                </a>

                                <div class="filter-help-space"></div>

                            </div>
                        </div>

                    </div>

                </form>

            </div>
        </div>

        @if ($reportGenerated)

            {{-- Report Information and Export Buttons --}}
            <div class="card">
                <div class="card-body">

                    <div class="row align-items-center">

                        <div class="col-lg-8 mb-3 mb-lg-0">

                            <h5 class="mb-2">
                                {{ $shiftLabel }} Shift
                            </h5>

                            <div class="mb-1">
                                Selected assessment month:
                                <strong>{{ $selectedMonthName }}</strong>
                            </div>

                            <div class="mb-1">
                                Admission months included:
                                <strong>
                                    {{ implode(', ', $quarterMonthNames) }}
                                </strong>
                            </div>

                            <div>
                                Total members:
                                <strong>{{ number_format($rows->count()) }}</strong>
                            </div>

                        </div>

                        <div class="col-lg-4 text-lg-end">

                            <a href="{{ route('nursing.quarterly.assessment.members.pdf', [
                                    'shift' => $shift,
                                    'month' => $selectedMonth,
                                ]) }}"
                               id="quarterlyPdfButton"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="btn btn-danger me-1 mb-1">
                                <i class="mdi mdi-file-pdf-box me-1"></i>
                                Export PDF
                            </a>

                            <a href="{{ route('nursing.quarterly.assessment.members.excel', [
                                    'shift' => $shift,
                                    'month' => $selectedMonth,
                                ]) }}"
                               id="quarterlyExcelButton"
                               class="btn btn-success mb-1">
                                <i class="mdi mdi-file-excel me-1"></i>
                                Export Excel
                            </a>

                        </div>

                    </div>

                </div>
            </div>

            {{-- Results Table --}}
            <div class="card">
                <div class="card-body">

                    <div class="table-responsive">

                        <table id="quarterlyAssessmentTable"
                               class="table table-bordered table-striped table-hover align-middle w-100">

                            <thead class="table-light">
                                <tr>
                                    <th>Participant ID</th>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Middle Name</th>
                                    <th>Admission Date</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($rows as $row)
                                    <tr>

                                        <td>
                                            {{ $row->ParticipantId }}
                                        </td>

                                        <td>
                                            {{ $row->LastName }}
                                        </td>

                                        <td>
                                            {{ $row->FirstName }}
                                        </td>

                                        <td>
                                            {{ $row->MiddleName }}
                                        </td>

                                        <td data-order="{{ $row->AdmissionDate }}">
                                            {{ $row->AdmissionDate
                                                ? \Carbon\Carbon::parse($row->AdmissionDate)->format('m/d/Y')
                                                : ''
                                            }}
                                        </td>

                                        <td>
                                            {{-- Remarks intentionally blank --}}
                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>
            </div>

        @else

            <div class="alert alert-info">
                Select the Morning or Noon shift, enter a three-character month,
                and click <strong>View Report</strong>.
            </div>

        @endif

    </div>
</div>

@endsection


@push('styles')

<style>
    /*
    |--------------------------------------------------------------------------
    | Search Criteria Alignment
    |--------------------------------------------------------------------------
    */

    .report-filter-button {
        min-height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .shift-button-group {
        min-height: 38px;
    }

    .shift-button-group .btn {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 80px;
    }

    /*
    |--------------------------------------------------------------------------
    | DataTable Formatting
    |--------------------------------------------------------------------------
    */

    #quarterlyAssessmentTable th {
        white-space: nowrap;
        vertical-align: middle;
    }

    #quarterlyAssessmentTable td {
        vertical-align: middle;
    }

    #quarterlyAssessmentTable th:nth-child(1),
    #quarterlyAssessmentTable td:nth-child(1) {
        width: 110px;
        white-space: nowrap;
    }

    #quarterlyAssessmentTable th:nth-child(2),
    #quarterlyAssessmentTable td:nth-child(2) {
        min-width: 140px;
    }

    #quarterlyAssessmentTable th:nth-child(3),
    #quarterlyAssessmentTable td:nth-child(3) {
        min-width: 140px;
    }

    #quarterlyAssessmentTable th:nth-child(4),
    #quarterlyAssessmentTable td:nth-child(4) {
        min-width: 120px;
    }

    #quarterlyAssessmentTable th:nth-child(5),
    #quarterlyAssessmentTable td:nth-child(5) {
        width: 130px;
        white-space: nowrap;
    }

    #quarterlyAssessmentTable th:nth-child(6),
    #quarterlyAssessmentTable td:nth-child(6) {
        min-width: 250px;
    }

    .filter-field-block {
        display: flex;
        flex-direction: column;
    }

    .filter-field-block .form-label {
        min-height: 21px;
        margin-bottom: 8px;
    }

    .report-filter-button,
    .shift-button-group,
    #month {
        height: 39px;
    }

    .report-filter-button {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .shift-button-group .btn {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 80px;
    }

    .filter-help-space
        min-height: 20px;
        margin-top: 4px;
        font-size: 11px;
        line-height: 16px;
    }

    /*
    |--------------------------------------------------------------------------
    | Mobile Alignment
    |--------------------------------------------------------------------------
    */

    @media (max-width: 767.98px) {
        .shift-button-group {
            width: 100%;
        }

        .shift-button-group .btn {
            width: 50%;
        }
    }
</style>

@endpush
@push('scripts')

<script>
    $(document).ready(function () {

        const validMonths = [
            'JAN', 'FEB', 'MAR', 'APR',
            'MAY', 'JUN', 'JUL', 'AUG',
            'SEP', 'OCT', 'NOV', 'DEC'
        ];

        /*
        |--------------------------------------------------------------------------
        | Convert Month Entry to Uppercase
        |--------------------------------------------------------------------------
        */
        $('#month').on('input', function () {

            let monthValue = $(this)
                .val()
                .toUpperCase()
                .replace(/[^A-Z]/g, '')
                .substring(0, 3);

            $(this).val(monthValue);
        });

        /*
        |--------------------------------------------------------------------------
        | Client-Side Month Validation
        |--------------------------------------------------------------------------
        */
        $('#quarterlyAssessmentForm').on('submit', function (event) {

            const month = $('#month')
                .val()
                .trim()
                .toUpperCase();

            if (
                month.length !== 3 ||
                !validMonths.includes(month)
            ) {
                event.preventDefault();

                alert(
                    'Please enter a valid three-character month such as JAN, FEB or MAR.'
                );

                $('#month').focus().select();

                return false;
            }

            $('#month').val(month);
        });

        @if ($reportGenerated)

            @php
                $pdfBaseUrl = route(
                    'nursing.quarterly.assessment.members.pdf',
                    [
                        'shift' => $shift,
                        'month' => $selectedMonth,
                    ]
                );

                $excelBaseUrl = route(
                    'nursing.quarterly.assessment.members.excel',
                    [
                        'shift' => $shift,
                        'month' => $selectedMonth,
                    ]
                );
            @endphp

            /*
            |--------------------------------------------------------------------------
            | Base Export URLs
            |--------------------------------------------------------------------------
            */
            const pdfBaseUrl = @json($pdfBaseUrl);
            const excelBaseUrl = @json($excelBaseUrl);

            /*
            |--------------------------------------------------------------------------
            | Initialize DataTable
            |--------------------------------------------------------------------------
            */
            const quarterlyTable = $('#quarterlyAssessmentTable').DataTable({
                processing: true,

                pageLength: 100,

                lengthMenu: [
                    [100, 200, 300, 500, -1],
                    [100, 200, 300, 500, 'All']
                ],

                order: [
                    [1, 'asc'],
                    [2, 'asc']
                ],

                columnDefs: [
                    {
                        targets: 5,
                        orderable: false,
                        searchable: false
                    }
                ],

                language: {
                    emptyTable: 'No members were found for the selected shift and admission-month group.',
                    zeroRecords: 'No matching members were found.',
                    lengthMenu: 'Show _MENU_ members',
                    info: 'Showing _START_ to _END_ of _TOTAL_ members',
                    infoEmpty: 'Showing 0 to 0 of 0 members',
                    search: 'Search:'
                }
            });

            /*
            |--------------------------------------------------------------------------
            | DataTable Column Index to Controller Sort Key
            |--------------------------------------------------------------------------
            */
            const sortableColumns = {
                0: 'participant_id',
                1: 'last_name',
                2: 'first_name',
                3: 'middle_name',
                4: 'admission_date'
            };

            /*
            |--------------------------------------------------------------------------
            | Update Export URLs with Current Table Sorting
            |--------------------------------------------------------------------------
            */
            function updateExportLinks() {

                const currentOrder = quarterlyTable.order();

                let sortColumn = 'last_name';
                let sortDirection = 'asc';

                if (currentOrder.length > 0) {

                    const columnIndex = currentOrder[0][0];
                    const direction = currentOrder[0][1];

                    if (
                        Object.prototype.hasOwnProperty.call(
                            sortableColumns,
                            columnIndex
                        )
                    ) {
                        sortColumn = sortableColumns[columnIndex];
                    }

                    if (
                        direction === 'asc' ||
                        direction === 'desc'
                    ) {
                        sortDirection = direction;
                    }
                }

                const queryParameters = new URLSearchParams({
                    sort: sortColumn,
                    direction: sortDirection
                });

                $('#quarterlyPdfButton').attr(
                    'href',
                    pdfBaseUrl + '&' + queryParameters.toString()
                );

                $('#quarterlyExcelButton').attr(
                    'href',
                    excelBaseUrl + '&' + queryParameters.toString()
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Set Initial Export Sorting
            |--------------------------------------------------------------------------
            */
            updateExportLinks();

            /*
            |--------------------------------------------------------------------------
            | Update Export Sorting When User Sorts the Table
            |--------------------------------------------------------------------------
            */
            quarterlyTable.on('order.dt', function () {
                updateExportLinks();
            });

        @endif

    });
</script>

@endpush
