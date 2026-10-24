@extends('admin.admin_dashboard')

@section('title', 'Preview Medisoft Claims')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        {{-- =========================================================
             PAGE TITLE
        ========================================================== --}}
        <div class="row">

            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Preview Medisoft Claims Import
                    </h4>

                    <div>
                        <a href="{{ route('claims.medisoft.import.form') }}"
                           class="btn btn-secondary">
                            Back
                        </a>
                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             FLASH MESSAGES
        ========================================================== --}}

        @if(session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif


        @if(session('error'))

            <div class="alert alert-danger">
                {{ session('error') }}
            </div>

        @endif


        {{-- =========================================================
             SUMMARY
        ========================================================== --}}

        <div class="card shadow-sm mb-3">

            <div class="card-body">

                <div class="d-flex flex-wrap gap-2 align-items-center">

                    <span class="badge bg-primary fs-6">
                        Total:
                        {{ count($previewRows) }}
                    </span>

                    <span class="badge bg-success fs-6">
                        Ready:
                        {{ $readyCount }}
                    </span>

                    <span class="badge bg-danger fs-6">
                        Invalid:
                        {{ $invalidCount }}
                    </span>

                    <span class="badge bg-secondary fs-6">
                        Visible:
                        <span id="visibleRowCount">
                            {{ count($previewRows) }}
                        </span>
                    </span>

                </div>


                <div class="alert alert-info mt-3 mb-0">

                    Review the imported Medisoft claims below.

                    Only rows marked
                    <strong>Ready</strong>
                    may be imported into the ADC claims table.

                    @if($invalidCount > 0)

                        <br>

                        <strong class="text-danger">
                            {{ $invalidCount }}
                            invalid row(s) will not be imported.
                        </strong>

                    @endif

                </div>

            </div>

        </div>


        {{-- =========================================================
             FILTER / REPORT BUTTONS
        ========================================================== --}}

        <div class="card shadow-sm mb-3">

            <div class="card-body">

                <div class="d-flex flex-wrap gap-2 align-items-center">

                    {{-- Show All --}}
                    <button type="button"
                            class="btn btn-outline-primary"
                            id="showAllRows">

                        <i class="fas fa-list me-1"></i>
                        Show All

                    </button>


                    {{-- Show Invalid --}}
                    <button type="button"
                            class="btn btn-outline-danger"
                            id="showInvalidOnly">

                        <i class="fas fa-exclamation-triangle me-1"></i>

                        Show Invalid Only

                        @if($invalidCount > 0)
                            ({{ $invalidCount }})
                        @endif

                    </button>


                    {{-- Show Warnings --}}
                    <button type="button"
                            class="btn btn-outline-warning"
                            id="showWarningsOnly">

                        <i class="fas fa-exclamation-circle me-1"></i>
                        Show Warnings Only

                    </button>


                    {{-- Download Invalid --}}
                    @if($invalidCount > 0)

                        <a href="{{ route(
                                    'claims.medisoft.import.invalid',
                                    ['token' => $token]
                                ) }}"
                           class="btn btn-danger">

                            <i class="fas fa-download me-1"></i>

                            Download Invalid Rows CSV

                        </a>

                    @endif


                    <div class="ms-auto">

                        <span id="filterStatus"
                              class="badge bg-secondary fs-6">

                            Showing All Rows

                        </span>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             IMPORT ALL VALID FORM

             IMPORTANT:
             Separate form prevents thousands of checkbox values
             being posted when importing all valid rows.
        ========================================================== --}}

        <form id="importAllValidForm"
              method="POST"
              action="{{ route('claims.medisoft.import.process') }}">

            @csrf

            <input type="hidden"
                   name="token"
                   value="{{ $token }}">

            <input type="hidden"
                   name="import_all_valid"
                   value="1">

        </form>


        {{-- =========================================================
             IMPORT SELECTED FORM
        ========================================================== --}}

        <form id="importSelectedForm"
              method="POST"
              action="{{ route('claims.medisoft.import.process') }}">

            @csrf

            <input type="hidden"
                   name="token"
                   value="{{ $token }}">


            <div class="card shadow-sm">

                {{-- =================================================
                     CARD HEADER
                ================================================== --}}

                <div class="card-header">

                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">

                        <h5 class="mb-0">
                            Medisoft Claim Rows
                        </h5>


                        <div class="d-flex flex-wrap gap-2">

                            <button type="button"
                                    class="btn btn-sm btn-success"
                                    id="selectAllValid">

                                Select All Valid

                            </button>


                            <button type="button"
                                    class="btn btn-sm btn-secondary"
                                    id="clearAll">

                                Clear Selection

                            </button>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     TABLE
                ================================================== --}}

                <div class="card-body p-2">

                    <div class="table-responsive"
                         style="max-height:650px; overflow:auto;">

                        <table class="table table-sm table-bordered table-hover align-middle"
                               id="medisoftPreviewTable">

                            <thead class="table-light"
                                   style="position:sticky; top:0; z-index:5;">

                                <tr>

                                    <th class="text-center">
                                        Select
                                    </th>

                                    <th>CSV Row</th>

                                    <th>Medisoft ID</th>

                                    <th>Participant</th>

                                    <th>Member</th>

                                    <th>From</th>

                                    <th>To</th>

                                    <th>Proc</th>

                                    <th>Mod</th>

                                    <th>Units</th>

                                    <th class="text-end">
                                        Rate
                                    </th>

                                    <th class="text-end">
                                        Total
                                    </th>

                                    <th class="text-end">
                                        Allowed
                                    </th>

                                    <th>Diag1</th>

                                    <th>Diag2</th>

                                    <th>Claim #</th>

                                    <th class="text-end">
                                        Ins Paid
                                    </th>

                                    <th>Insurance</th>

                                    <th>PA #</th>

                                    <th>PA Expiry</th>

                                    <th>Status</th>

                                    <th>Result</th>

                                    <th>Message</th>

                                </tr>

                            </thead>


                            <tbody>

                                @forelse($previewRows as $row)

                                    @php

                                        $rowClass = '';

                                        if (empty($row['valid'])) {

                                            $rowClass = 'table-danger';

                                        } elseif (!empty($row['warning'])) {

                                            $rowClass = 'table-warning';

                                        }

                                    @endphp


                                    <tr class="medisoft-preview-row {{ $rowClass }}"
                                        data-valid="{{ !empty($row['valid']) ? '1' : '0' }}"
                                        data-warning="{{ !empty($row['warning']) ? '1' : '0' }}">


                                        {{-- SELECT --}}
                                        <td class="text-center">

                                            @if(!empty($row['valid']))

                                                <input type="checkbox"
                                                       class="form-check-input valid-row-checkbox"
                                                       name="selected_rows[]"
                                                       value="{{ $row['key'] }}"
                                                       checked>

                                            @else

                                                <input type="checkbox"
                                                       class="form-check-input"
                                                       disabled>

                                            @endif

                                        </td>


                                        {{-- CSV ROW --}}
                                        <td>
                                            {{ $row['csv_row'] ?? '' }}
                                        </td>


                                        {{-- MEDISOFT ID --}}
                                        <td>
                                            {{ $row['participant_raw'] ?? '' }}
                                        </td>


                                        {{-- PARTICIPANT --}}
                                        <td>

                                            <strong>
                                                {{ $row['ParticipantId'] ?? '' }}
                                            </strong>

                                        </td>


                                        {{-- MEMBER --}}
                                        <td>
                                            {{ $row['member_name'] ?? '' }}
                                        </td>


                                        {{-- FROM --}}
                                        <td>

                                            @if(!empty($row['service_from']))

                                                {{
                                                    \Carbon\Carbon::parse(
                                                        $row['service_from']
                                                    )->format('m/d/Y')
                                                }}

                                            @endif

                                        </td>


                                        {{-- TO --}}
                                        <td>

                                            @if(!empty($row['service_to']))

                                                {{
                                                    \Carbon\Carbon::parse(
                                                        $row['service_to']
                                                    )->format('m/d/Y')
                                                }}

                                            @endif

                                        </td>


                                        {{-- PROC --}}
                                        <td>
                                            {{ $row['proc_code'] ?? '' }}
                                        </td>


                                        {{-- MOD --}}
                                        <td>
                                            {{ $row['m1'] ?? '' }}
                                        </td>


                                        {{-- UNITS --}}
                                        <td class="text-center">
                                            {{ $row['units'] ?? 0 }}
                                        </td>


                                        {{-- RATE --}}
                                        <td class="text-end">

                                            ${{
                                                number_format(
                                                    (float) (
                                                        $row['amount_per_unit']
                                                        ?? 0
                                                    ),
                                                    2
                                                )
                                            }}

                                        </td>


                                        {{-- TOTAL --}}
                                        <td class="text-end">

                                            ${{
                                                number_format(
                                                    (float) (
                                                        $row['total_amount']
                                                        ?? 0
                                                    ),
                                                    2
                                                )
                                            }}

                                        </td>


                                        {{-- ALLOWED --}}
                                        <td class="text-end">

                                            ${{
                                                number_format(
                                                    (float) (
                                                        $row['allowed']
                                                        ?? 0
                                                    ),
                                                    2
                                                )
                                            }}

                                        </td>


                                        {{-- DIAG 1 --}}
                                        <td>
                                            {{ $row['diag1'] ?? '' }}
                                        </td>


                                        {{-- DIAG 2 --}}
                                        <td>
                                            {{ $row['diag2'] ?? '' }}
                                        </td>


                                        {{-- CLAIM NUMBER --}}
                                        <td>

                                            <strong>
                                                {{ $row['claim_no'] ?? '' }}
                                            </strong>

                                        </td>


                                        {{-- INSURANCE PAID --}}
                                        <td class="text-end">

                                            @if(
                                                (
                                                    $row['ins1_paid']
                                                    ?? 0
                                                ) > 0
                                            )

                                                <span class="fw-bold text-success">

                                                    ${{
                                                        number_format(
                                                            (float) (
                                                                $row['ins1_paid']
                                                                ?? 0
                                                            ),
                                                            2
                                                        )
                                                    }}

                                                </span>

                                            @else

                                                $0.00

                                            @endif

                                        </td>


                                        {{-- INSURANCE --}}
                                        <td>
                                            {{ $row['insurance_name'] ?? '' }}
                                        </td>


                                        {{-- PA NUMBER --}}
                                        <td>
                                            {{ $row['prior_auth_no'] ?? '' }}
                                        </td>


                                        {{-- PA EXPIRY --}}
                                        <td>

                                            @if(
                                                !empty(
                                                    $row['prior_auth_expiry']
                                                )
                                            )

                                                {{
                                                    \Carbon\Carbon::parse(
                                                        $row['prior_auth_expiry']
                                                    )->format('m/d/Y')
                                                }}

                                            @endif

                                        </td>


                                        {{-- STATUS --}}
                                        <td>

                                            @if(
                                                (
                                                    $row['status']
                                                    ?? ''
                                                ) === 'paid'
                                            )

                                                <span class="badge bg-success">
                                                    Paid
                                                </span>

                                            @else

                                                <span class="badge bg-primary">
                                                    Sent
                                                </span>

                                            @endif

                                        </td>


                                        {{-- RESULT --}}
                                        <td>

                                            @if(empty($row['valid']))

                                                <span class="badge bg-danger">
                                                    Invalid
                                                </span>

                                            @elseif(!empty($row['warning']))

                                                <span class="badge bg-warning text-dark">
                                                    Warning
                                                </span>

                                            @else

                                                <span class="badge bg-success">
                                                    Ready
                                                </span>

                                            @endif

                                        </td>


                                        {{-- MESSAGE --}}
                                        <td style="min-width:280px;">

                                            @if(empty($row['valid']))

                                                <strong class="text-danger">

                                                    {{ $row['message'] ?? '' }}

                                                </strong>

                                            @else

                                                {{ $row['message'] ?? '' }}

                                            @endif

                                        </td>

                                    </tr>


                                @empty

                                    <tr>

                                        <td colspan="23"
                                            class="text-center text-muted">

                                            No Medisoft claims were found
                                            in this CSV file.

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>


                {{-- =================================================
                     FOOTER
                ================================================== --}}

                <div class="card-footer">

                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">

                        <a href="{{ route('claims.medisoft.import.form') }}"
                           class="btn btn-secondary">

                            Back

                        </a>


                        <div class="d-flex flex-wrap gap-2">

                            {{-- IMPORT SELECTED --}}
                            <button type="submit"
                                    class="btn btn-primary"
                                    @if($readyCount == 0)
                                        disabled
                                    @endif>

                                Import Selected Claims

                            </button>


                            {{-- IMPORT ALL VALID --}}
                            <button type="submit"
                                    form="importAllValidForm"
                                    class="btn btn-success"
                                    onclick="return confirm(
                                        'Import all {{ $readyCount }} valid Medisoft claim rows?'
                                    );"
                                    @if($readyCount == 0)
                                        disabled
                                    @endif>

                                Import All Valid Rows

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </form>

    </div>

</div>

@endsection


@section('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Preview Rows
        |--------------------------------------------------------------------------
        */

        const rows =
            Array.from(
                document.querySelectorAll(
                    '.medisoft-preview-row'
                )
            );


        const visibleRowCount =
            document.getElementById(
                'visibleRowCount'
            );


        const filterStatus =
            document.getElementById(
                'filterStatus'
            );


        /*
        |--------------------------------------------------------------------------
        | Update Visible Count
        |--------------------------------------------------------------------------
        */

        function updateVisibleCount() {

            let count = 0;


            rows.forEach(
                function (row) {

                    if (
                        row.style.display
                        !==
                        'none'
                    ) {

                        count++;
                    }

                }
            );


            if (visibleRowCount) {

                visibleRowCount.textContent =
                    count;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Show All Rows
        |--------------------------------------------------------------------------
        */

        const showAllRows =
            document.getElementById(
                'showAllRows'
            );


        if (showAllRows) {

            showAllRows.addEventListener(
                'click',
                function () {

                    rows.forEach(
                        function (row) {

                            row.style.display =
                                '';

                        }
                    );


                    if (filterStatus) {

                        filterStatus.textContent =
                            'Showing All Rows';

                        filterStatus.className =
                            'badge bg-secondary fs-6';
                    }


                    updateVisibleCount();

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Show Invalid Only
        |--------------------------------------------------------------------------
        */

        const showInvalidOnly =
            document.getElementById(
                'showInvalidOnly'
            );


        if (showInvalidOnly) {

            showInvalidOnly.addEventListener(
                'click',
                function () {

                    rows.forEach(
                        function (row) {

                            const valid =
                                row.dataset.valid
                                ===
                                '1';


                            row.style.display =
                                valid
                                    ? 'none'
                                    : '';

                        }
                    );


                    if (filterStatus) {

                        filterStatus.textContent =
                            'Showing Invalid Rows';

                        filterStatus.className =
                            'badge bg-danger fs-6';
                    }


                    updateVisibleCount();

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Show Warnings Only
        |--------------------------------------------------------------------------
        */

        const showWarningsOnly =
            document.getElementById(
                'showWarningsOnly'
            );


        if (showWarningsOnly) {

            showWarningsOnly.addEventListener(
                'click',
                function () {

                    rows.forEach(
                        function (row) {

                            const warning =
                                row.dataset.warning
                                ===
                                '1';


                            row.style.display =
                                warning
                                    ? ''
                                    : 'none';

                        }
                    );


                    if (filterStatus) {

                        filterStatus.textContent =
                            'Showing Warning Rows';

                        filterStatus.className =
                            'badge bg-warning text-dark fs-6';
                    }


                    updateVisibleCount();

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Select All Valid
        |--------------------------------------------------------------------------
        */

        const selectAllButton =
            document.getElementById(
                'selectAllValid'
            );


        if (selectAllButton) {

            selectAllButton.addEventListener(
                'click',
                function () {

                    document
                        .querySelectorAll(
                            '.valid-row-checkbox'
                        )
                        .forEach(
                            function (checkbox) {

                                checkbox.checked =
                                    true;

                            }
                        );

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Clear Selection
        |--------------------------------------------------------------------------
        */

        const clearAllButton =
            document.getElementById(
                'clearAll'
            );


        if (clearAllButton) {

            clearAllButton.addEventListener(
                'click',
                function () {

                    document
                        .querySelectorAll(
                            '.valid-row-checkbox'
                        )
                        .forEach(
                            function (checkbox) {

                                checkbox.checked =
                                    false;

                            }
                        );

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Import Selected Confirmation
        |--------------------------------------------------------------------------
        */

        const selectedForm =
            document.getElementById(
                'importSelectedForm'
            );


        if (selectedForm) {

            selectedForm.addEventListener(
                'submit',
                function (event) {

                    const checkedCount =
                        document.querySelectorAll(
                            '.valid-row-checkbox:checked'
                        ).length;


                    if (checkedCount === 0) {

                        event.preventDefault();

                        alert(
                            'Please select at least one valid claim row.'
                        );

                        return false;
                    }


                    if (
                        !confirm(
                            'Import '
                            +
                            checkedCount
                            +
                            ' selected Medisoft claim row(s)?'
                        )
                    ) {

                        event.preventDefault();

                        return false;
                    }


                    return true;

                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Initial Visible Count
        |--------------------------------------------------------------------------
        */

        updateVisibleCount();

    }
);

</script>

@endsection