@extends('admin.admin_dashboard')

@section('title', 'Preview Excel Claims')

@section('admin')

    <div class="page-content">

        <div class="container-fluid">

            <div class="card">

                <div class="card-header">

                    <div class="d-flex justify-content-between align-items-center">

                        <h5 class="mb-0">
                            Preview Excel Claims
                        </h5>

                        <div>

                            <span class="badge bg-primary">
                                Total: {{ count($previewRows) }}
                            </span>

                            <span class="badge bg-success">
                                Ready: {{ $readyCount }}
                            </span>

                            <span class="badge bg-danger">
                                Invalid: {{ $invalidCount }}
                            </span>

                        </div>

                    </div>

                </div>


                <form method="POST" action="{{ route('claims.excel.generate') }}">

                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="card-body">

                        <div class="mb-3">

                            <button type="button" class="btn btn-sm btn-success" id="selectAllValid">
                                Select All Valid
                            </button>

                            <button type="button" class="btn btn-sm btn-secondary" id="clearAll">
                                Clear Selection
                            </button>

                        </div>


                        <div class="table-responsive">

                            <table class="table table-bordered table-striped align-middle">

                                <thead>

                                    <tr>

                                        <th>Select</th>

                                        <th>Excel Row</th>

                                        <th>Participant</th>

                                        <th>From</th>

                                        <th>To</th>

                                        <th>Units</th>

                                        <th>Proc</th>

                                        <th>Mod</th>

                                        <th>Rate</th>

                                        <th>Total</th>

                                        <th>PA #</th>

                                        <th>PA Expiry</th>

                                        <th>Diag1</th>

                                        <th>Diag2</th>

                                        <th>Result</th>

                                        <th>Message</th>

                                        <th>Fix</th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @forelse($previewRows as $row)
                                        <tr class="{{ $row['valid'] ? '' : 'table-danger' }}">

                                            <td class="text-center">

                                                @if ($row['valid'])
                                                    <input type="checkbox" class="form-check-input valid-row-checkbox"
                                                        name="selected_rows[]" value="{{ $row['key'] }}">
                                                @else
                                                    <input type="checkbox" class="form-check-input" disabled>
                                                @endif

                                            </td>


                                            <td>
                                                {{ $row['excel_row'] }}
                                            </td>


                                            <td>
                                                {{ $row['participant_id'] }}
                                            </td>


                                            <td>

                                                @if ($row['service_from'])
                                                    {{ \Carbon\Carbon::parse($row['service_from'])->format('m/d/Y') }}
                                                @endif

                                            </td>


                                            <td>

                                                @if ($row['service_to'])
                                                    {{ \Carbon\Carbon::parse($row['service_to'])->format('m/d/Y') }}
                                                @endif

                                            </td>


                                            <td>
                                                {{ $row['units'] }}
                                            </td>


                                            <td>
                                                {{ $row['proc_code'] ?? '' }}
                                            </td>


                                            <td>
                                                {{ $row['modifier'] ?? '' }}
                                            </td>


                                            <td>

                                                @if ($row['amount_per_unit'] !== null)
                                                    ${{ number_format($row['amount_per_unit'], 2) }}
                                                @endif

                                            </td>


                                            <td>

                                                @if ($row['total_amount'] !== null)
                                                    ${{ number_format($row['total_amount'], 2) }}
                                                @endif

                                            </td>


                                            <td>
                                                {{ $row['prior_auth_no'] ?? '' }}
                                            </td>


                                            <td>

                                                @if (!empty($row['prior_auth_expiry']))
                                                    {{ \Carbon\Carbon::parse($row['prior_auth_expiry'])->format('m/d/Y') }}
                                                @endif

                                            </td>


                                            <td>
                                                {{ $row['diag1'] ?? '' }}
                                            </td>


                                            <td>
                                                {{ $row['diag2'] ?? '' }}
                                            </td>


                                            <td>

                                                @if ($row['valid'])
                                                    <span class="badge bg-success">
                                                        Ready
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        Invalid
                                                    </span>
                                                @endif

                                            </td>


                                            <td>
                                                {{ $row['message'] }}
                                            </td>


                                            <td style="white-space:nowrap;">

                                                @if ($row['member_found'])
                                                    <button type="button" class="btn btn-sm btn-primary btn-fix-icd"
                                                        data-bs-toggle="modal" data-bs-target="#fixIcdModal"
                                                        data-row-key="{{ $row['excel_row_key'] }}"
                                                        data-participant="{{ $row['participant_id'] }}"
                                                        data-diag1="{{ $row['diag1'] ?? '' }}"
                                                        data-diag2="{{ $row['diag2'] ?? '' }}">
                                                        Fix ICD
                                                    </button>


                                                    <button type="button" class="btn btn-sm btn-warning btn-fix-pa"
                                                        data-bs-toggle="modal" data-bs-target="#fixPaModal"
                                                        data-participant="{{ $row['participant_id'] }}"
                                                        data-medicaid-pa-no="{{ $row['medicaid_PA_no'] ?? '' }}"
                                                        data-medicaid-pa-start="{{ $row['medicaid_PA_Start'] ?? '' }}"
                                                        data-medicaid-pa-end="{{ $row['medicaid_PA_End'] ?? '' }}"
                                                        data-renew1-pa-no="{{ $row['renew1_PA_no'] ?? '' }}"
                                                        data-renew1-pa-start="{{ $row['renew1_PA_Start'] ?? '' }}"
                                                        data-renew1-pa-end="{{ $row['renew1_PA_End'] ?? '' }}">
                                                        Fix PA
                                                    </button>
                                                @endif

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="17" class="text-center">
                                                No claim rows found.
                                            </td>

                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>


                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">

                            {{-- Left --}}
                            <a href="{{ route('claims.excel.form') }}" class="btn btn-secondary">
                                Back
                            </a>

                            {{-- Right --}}
                            <div class="d-flex gap-2">

                                <button type="button" class="btn btn-success" id="selectAllValidBottom">
                                    Select All Valid
                                </button>

                                <button type="button" class="btn btn-secondary" id="clearAllBottom">
                                    Clear Selection
                                </button>

                                <button type="submit" class="btn btn-success">
                                    Generate Selected Claims
                                </button>

                            </div>

                        </div>
                    </div>
                </form>

            </div>

        </div>

    </div>



    {{-- =========================================================
     FIX ICD MODAL
========================================================== --}}

    <div class="modal fade" id="fixIcdModal" tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">

                <form method="POST" action="{{ route('claims.excel.fix.icd') }}">

                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <input type="hidden" name="excel_row_key" id="icdExcelRowKey">


                    <div class="modal-header">

                        <h5 class="modal-title">
                            Fix Diagnosis Codes
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>


                    <div class="modal-body">

                        <div class="mb-3">

                            <label class="form-label">
                                Participant
                            </label>

                            <input type="text" id="icdParticipant" class="form-control" readonly>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Primary ICD
                            </label>

                            <select name="diag1" id="fixDiag1" class="form-control" required></select>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Secondary ICD
                            </label>

                            <select name="diag2" id="fixDiag2" class="form-control"></select>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-success">
                            Save & Revalidate
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>



    {{-- =========================================================
     FIX PA MODAL
========================================================== --}}

    <div class="modal fade" id="fixPaModal" tabindex="-1">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <form method="POST" action="{{ route('claims.excel.fix.pa') }}">

                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">


                    <div class="modal-header">

                        <h5 class="modal-title">
                            Fix Prior Authorization
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                    </div>


                    <div class="modal-body">

                        <div class="mb-3">

                            <label class="form-label">
                                Participant
                            </label>

                            <input type="text" name="participant_id" id="paParticipant" class="form-control"
                                readonly>

                        </div>


                        <hr>

                        <h6>
                            Current Medicaid PA
                        </h6>


                        <div class="row">

                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    PA Number
                                </label>

                                <input type="text" name="medicaid_PA_no" id="medicaidPaNo" class="form-control">

                            </div>


                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    Start Date
                                </label>

                                <input type="date" name="medicaid_PA_Start" id="medicaidPaStart"
                                    class="form-control">

                            </div>


                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    End Date
                                </label>

                                <input type="date" name="medicaid_PA_End" id="medicaidPaEnd" class="form-control">

                            </div>

                        </div>


                        <hr>

                        <h6>
                            Renewal PA
                        </h6>


                        <div class="row">

                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    PA Number
                                </label>

                                <input type="text" name="renew1_PA_no" id="renew1PaNo" class="form-control">

                            </div>


                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    Start Date
                                </label>

                                <input type="date" name="renew1_PA_Start" id="renew1PaStart" class="form-control">

                            </div>


                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    End Date
                                </label>

                                <input type="date" name="renew1_PA_End" id="renew1PaEnd" class="form-control">

                            </div>

                        </div>

                    </div>


                    <div class="modal-footer">



                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-success">
                            Save & Revalidate
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /*
            |--------------------------------------------------------------------------
            | FIX ICD MODAL
            |--------------------------------------------------------------------------
            */

            const fixIcdModal = document.getElementById('fixIcdModal');

            if (fixIcdModal) {

                fixIcdModal.addEventListener('show.bs.modal', function(event) {

                    const button = event.relatedTarget;

                    if (!button) {
                        return;
                    }

                    const rowKey =
                        button.getAttribute('data-row-key') || '';

                    const participant =
                        button.getAttribute('data-participant') || '';

                    const diag1 =
                        button.getAttribute('data-diag1') || '';

                    const diag2 =
                        button.getAttribute('data-diag2') || '';

                    /*
                    |--------------------------------------------------------------------------
                    | Populate Participant
                    |--------------------------------------------------------------------------
                    */

                    document.getElementById('icdExcelRowKey').value = rowKey;

                    document.getElementById('icdParticipant').value = participant;

                    /*
                    |--------------------------------------------------------------------------
                    | Initialize / Reset ICD Select2
                    |--------------------------------------------------------------------------
                    */

                    if (
                        typeof window.jQuery !== 'undefined' &&
                        typeof jQuery.fn.select2 !== 'undefined'
                    ) {

                        const $diag1 = jQuery('#fixDiag1');
                        const $diag2 = jQuery('#fixDiag2');

                        /*
                        | Initialize only once
                        */

                        if (!$diag1.hasClass('select2-hidden-accessible')) {

                            $diag1.select2({
                                width: '100%',
                                placeholder: 'Type ICD-10 code or description...',
                                allowClear: true,
                                minimumInputLength: 2,
                                dropdownParent: jQuery('#fixIcdModal'),

                                ajax: {
                                    url: "{{ route('lookup.icd10') }}",
                                    dataType: 'json',
                                    delay: 300,

                                    data: function(params) {
                                        return {
                                            q: params.term || '',
                                            limit: 25
                                        };
                                    },

                                    processResults: function(data) {
                                        return data;
                                    },

                                    cache: true
                                }
                            });
                        }


                        if (!$diag2.hasClass('select2-hidden-accessible')) {

                            $diag2.select2({
                                width: '100%',
                                placeholder: 'Type ICD-10 code or description...',
                                allowClear: true,
                                minimumInputLength: 2,
                                dropdownParent: jQuery('#fixIcdModal'),

                                ajax: {
                                    url: "{{ route('lookup.icd10') }}",
                                    dataType: 'json',
                                    delay: 300,

                                    data: function(params) {
                                        return {
                                            q: params.term || '',
                                            limit: 25
                                        };
                                    },

                                    processResults: function(data) {
                                        return data;
                                    },

                                    cache: true
                                }
                            });
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Clear Previous Modal Values
                        |--------------------------------------------------------------------------
                        */

                        $diag1.empty();
                        $diag2.empty();


                        /*
                        |--------------------------------------------------------------------------
                        | Current Primary ICD
                        |--------------------------------------------------------------------------
                        */

                        if (diag1 !== '') {

                            const option1 = new Option(
                                diag1,
                                diag1,
                                true,
                                true
                            );

                            $diag1
                                .append(option1)
                                .trigger('change');
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Current Secondary ICD
                        |--------------------------------------------------------------------------
                        */

                        if (diag2 !== '') {

                            const option2 = new Option(
                                diag2,
                                diag2,
                                true,
                                true
                            );

                            $diag2
                                .append(option2)
                                .trigger('change');
                        }

                    } else {

                        console.error(
                            'jQuery or Select2 is not loaded on this page.'
                        );
                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | FIX PA MODAL
            |--------------------------------------------------------------------------
            */

            const fixPaModal = document.getElementById('fixPaModal');

            if (fixPaModal) {

                fixPaModal.addEventListener('show.bs.modal', function(event) {

                    const button = event.relatedTarget;

                    if (!button) {
                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Participant
                    |--------------------------------------------------------------------------
                    */

                    document.getElementById('paParticipant').value =
                        button.getAttribute('data-participant') || '';


                    /*
                    |--------------------------------------------------------------------------
                    | Current Medicaid PA
                    |--------------------------------------------------------------------------
                    */

                    document.getElementById('medicaidPaNo').value =
                        button.getAttribute('data-medicaid-pa-no') || '';

                    document.getElementById('medicaidPaStart').value =
                        button.getAttribute('data-medicaid-pa-start') || '';

                    document.getElementById('medicaidPaEnd').value =
                        button.getAttribute('data-medicaid-pa-end') || '';


                    /*
                    |--------------------------------------------------------------------------
                    | Renewal PA
                    |--------------------------------------------------------------------------
                    */

                    document.getElementById('renew1PaNo').value =
                        button.getAttribute('data-renew1-pa-no') || '';

                    document.getElementById('renew1PaStart').value =
                        button.getAttribute('data-renew1-pa-start') || '';

                    document.getElementById('renew1PaEnd').value =
                        button.getAttribute('data-renew1-pa-end') || '';

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Select All Valid
            |--------------------------------------------------------------------------
            */

            const selectAllValid =
                document.getElementById('selectAllValid');

            if (selectAllValid) {

                selectAllValid.addEventListener('click', function() {

                    document
                        .querySelectorAll('.valid-row-checkbox')
                        .forEach(function(checkbox) {

                            checkbox.checked = true;

                        });

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Clear Selection
            |--------------------------------------------------------------------------
            */

            const clearAll =
                document.getElementById('clearAll');

            if (clearAll) {

                clearAll.addEventListener('click', function() {

                    document
                        .querySelectorAll('.valid-row-checkbox')
                        .forEach(function(checkbox) {

                            checkbox.checked = false;

                        });

                });

            }

            /*
            |--------------------------------------------------------------------------
            | Bottom Select All Valid
            |--------------------------------------------------------------------------
            */

            const selectAllValidBottom =
                document.getElementById('selectAllValidBottom');

            if (selectAllValidBottom) {

                selectAllValidBottom.addEventListener('click', function() {

                    document
                        .querySelectorAll('.valid-row-checkbox')
                        .forEach(function(checkbox) {

                            checkbox.checked = true;

                        });

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Bottom Clear Selection
            |--------------------------------------------------------------------------
            */

            const clearAllBottom =
                document.getElementById('clearAllBottom');

            if (clearAllBottom) {

                clearAllBottom.addEventListener('click', function() {

                    document
                        .querySelectorAll('.valid-row-checkbox')
                        .forEach(function(checkbox) {

                            checkbox.checked = false;

                        });

                });

            }

        });
    </script>
@endsection
