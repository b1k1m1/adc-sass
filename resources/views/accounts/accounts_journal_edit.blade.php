@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <style>
            tfoot th,
            tfoot input {
                font-weight: bold;
                background: #f8f9fa;
            }
        </style>

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Page title --}}
        <div class="row">
            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Edit Journal Entry
                    </h4>

                    <div class="page-title-right">

                        <div class="d-flex align-items-center gap-2">

                            {{-- Copy to New Record --}}
                            <form method="POST"
                                  action="{{ route('journal.copy', [
                                      'trans_no' => $header->trans_no,
                                      'trans_dt' => \Carbon\Carbon::parse($header->trans_dt)->format('Y-m-d'),
                                  ]) }}"
                                  id="copyJournalForm"
                                  class="m-0">

                                @csrf

                                <button type="button"
                                        class="btn btn-success waves-effect waves-light"
                                        id="btnCopyJournal">

                                    <i class="mdi mdi-content-copy me-1"></i>

                                    Copy to New Record

                                </button>

                            </form>

                            {{-- Back button --}}
                            <a href="{{ route('journal.list') }}"
                               class="btn btn-primary waves-effect waves-light">

                                {{ t('common.back', 'Back') }}

                            </a>

                        </div>

                    </div>

                </div>

            </div>
        </div>
        {{-- End page title --}}

        {{-- Update Journal form --}}
        <form id="journalEntryForm"
              action="{{ route('journal.update', [
                  'trans_no' => $header->trans_no,
                  'trans_dt' => \Carbon\Carbon::parse($header->trans_dt)->format('Y-m-d'),
              ]) }}"
              method="POST">

            @csrf
            @method('PUT')

            {{-- Journal header --}}
            <div class="row mb-3">

                {{-- Journal Type --}}
                <div class="col-md-2">

                    <label for="trans_type" class="form-label">
                        Journal Type
                    </label>

                    <select class="form-select"
                            id="trans_type"
                            name="trans_type">

                        <option value="REGU"
                            {{ old('trans_type', trim((string) $header->trans_type)) === 'REGU'
                                ? 'selected'
                                : '' }}>
                            Regular
                        </option>

                        <option value="ADJU"
                            {{ old('trans_type', trim((string) $header->trans_type)) === 'ADJU'
                                ? 'selected'
                                : '' }}>
                            Adjustment
                        </option>

                        <option value="BBAL"
                            {{ old('trans_type', trim((string) $header->trans_type)) === 'BBAL'
                                ? 'selected'
                                : '' }}>
                            Beginning Balance
                        </option>

                    </select>

                </div>

                {{-- Journal Number --}}
                <div class="col-md-2">

                    <label for="trans_no" class="form-label">
                        Journal #
                    </label>

                    <input type="text"
                           class="form-control"
                           id="trans_no"
                           name="trans_no"
                           value="{{ old('trans_no', $header->trans_no) }}"
                           readonly>

                </div>

                {{-- Journal Date --}}
                <div class="col-md-2">

                    <label for="trans_dt" class="form-label">
                        Date
                    </label>

                    <input type="text"
                           class="form-control"
                           id="trans_dt"
                           name="trans_dt"
                           value="{{ old(
                               'trans_dt',
                               $header->trans_dt
                                   ? \Carbon\Carbon::parse($header->trans_dt)->format('m/d/Y')
                                   : ''
                           ) }}"
                           placeholder="MM/DD/YYYY"
                           autocomplete="off"
                           required>

                </div>

                {{-- Status --}}
                <div class="col-md-2">

                    <label for="trans_flag" class="form-label">
                        Status
                    </label>

                    <select class="form-select"
                            id="trans_flag"
                            name="trans_flag">

                        <option value="A"
                            {{ old('trans_flag', trim((string) $header->trans_flag)) === 'A'
                                ? 'selected'
                                : '' }}>
                            Active
                        </option>

                        <option value="H"
                            {{ old('trans_flag', trim((string) $header->trans_flag)) === 'H'
                                ? 'selected'
                                : '' }}>
                            Hold
                        </option>

                        <option value="D"
                            {{ old('trans_flag', trim((string) $header->trans_flag)) === 'D'
                                ? 'selected'
                                : '' }}>
                            Delete
                        </option>

                    </select>

                </div>

                {{-- Post Status --}}
                <div class="col-md-2">

                    <label for="post_flag" class="form-label">
                        Post Status
                    </label>

                    <select class="form-select"
                            id="post_flag"
                            name="post_flag">

                        <option value="U"
                            {{ old('post_flag', trim((string) $header->post_flag)) === 'U'
                                ? 'selected'
                                : '' }}>
                            Unposted
                        </option>

                        <option value="P"
                            {{ old('post_flag', trim((string) $header->post_flag)) === 'P'
                                ? 'selected'
                                : '' }}>
                            Posted
                        </option>

                    </select>

                </div>

            </div>

            {{-- Journal Entry Lines --}}
            <div class="table-responsive">

                <table class="table table-sm table-bordered align-middle"
                       id="entryLinesTable">

                    <thead class="table-light">

                        <tr>
                            <th class="text-center">Account</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th class="text-center">Description</th>

                            <th class="text-center" style="width: 60px;">
                                <button type="button"
                                        class="btn btn-success btn-sm"
                                        id="btnAddRow">
                                    <i class="mdi mdi-plus"></i>
                                </button>
                            </th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($journalLines as $i => $line)

                            <tr>

                                {{-- Account --}}
                                <td>
                                    <select class="form-select form-select-sm account-select"
                                            name="lines[{{ $i }}][account_id]"
                                            required>

                                        <option value="">Select Account</option>

                                        @php
                                            $journalAccountId = trim(
                                                (string) old(
                                                    "lines.$i.account_id",
                                                    $line->account_id
                                                )
                                            );
                                        @endphp

                                        @foreach ($accounts as $account)

                                            @php
                                                $masterAccountId = trim(
                                                    (string) $account->party_cd
                                                );

                                                /*
                                                * Compare numeric account codes by their numeric value.
                                                *
                                                * Examples:
                                                * 011 matches 011
                                                * 011 also safely matches 11
                                                * 016 matches 016
                                                */
                                                $isSelected =
                                                    ctype_digit($journalAccountId) &&
                                                    ctype_digit($masterAccountId)
                                                        ? (int) $journalAccountId === (int) $masterAccountId
                                                        : $journalAccountId === $masterAccountId;
                                            @endphp

                                            <option value="{{ $masterAccountId }}"
                                                {{ $isSelected ? 'selected' : '' }}>

                                                {{ $masterAccountId }} - {{ $account->party_nm }}

                                            </option>

                                        @endforeach

                                    </select>
                                </td>

                                {{-- Debit --}}
                                <td>

                                    <input type="text"
                                           class="form-control form-control-sm debit text-end"
                                           name="lines[{{ $i }}][debit_amt]"
                                           value="{{ old(
                                               "lines.$i.debit_amt",
                                               number_format((float) $line->debit_amt, 2)
                                           ) }}">

                                </td>

                                {{-- Credit --}}
                                <td>

                                    <input type="text"
                                           class="form-control form-control-sm credit text-end"
                                           name="lines[{{ $i }}][credit_amt]"
                                           value="{{ old(
                                               "lines.$i.credit_amt",
                                               number_format((float) $line->credit_amt, 2)
                                           ) }}">

                                </td>

                                {{-- Description --}}
                                <td>

                                    <input type="text"
                                           class="form-control form-control-sm"
                                           name="lines[{{ $i }}][description]"
                                           maxlength="40"
                                           value="{{ old(
                                               "lines.$i.description",
                                               $line->description
                                           ) }}">

                                </td>

                                {{-- Remove row --}}
                                <td class="text-center">

                                    <button type="button"
                                            class="btn btn-danger btn-sm btn-remove-row">

                                        <i class="mdi mdi-minus"></i>

                                    </button>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                    <tfoot>

                        <tr>

                            <th>
                                Total / Difference
                            </th>

                            <th>
                                <input type="text"
                                       id="totalDebit"
                                       class="form-control form-control-sm text-end"
                                       readonly>
                            </th>

                            <th>
                                <input type="text"
                                       id="totalCredit"
                                       class="form-control form-control-sm text-end"
                                       readonly>
                            </th>

                            <th>
                                <input type="text"
                                       id="totalDifference"
                                       class="form-control form-control-sm text-end"
                                       readonly>
                            </th>

                            <th></th>

                        </tr>

                    </tfoot>

                </table>

            </div>

            <button type="submit"
                    class="btn btn-primary"
                    id="btnUpdateJournal">

                <i class="mdi mdi-content-save me-1"></i>

                Update Journal Entry

            </button>

        </form>

    </div>
</div>

@endsection


@section('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const tableBody = document.querySelector(
        '#entryLinesTable tbody'
    );

    const journalForm = document.getElementById(
        'journalEntryForm'
    );

    const addRowButton = document.getElementById(
        'btnAddRow'
    );

    const copyButton = document.getElementById(
        'btnCopyJournal'
    );

    const copyForm = document.getElementById(
        'copyJournalForm'
    );

    /*
    |--------------------------------------------------------------------------
    | Parse an amount
    |--------------------------------------------------------------------------
    */

    function parseAmount(value) {

        const cleanedValue = String(value || '')
            .replace(/,/g, '')
            .trim();

        const amount = parseFloat(cleanedValue);

        return Number.isFinite(amount) ? amount : 0;

    }

    /*
    |--------------------------------------------------------------------------
    | Format an amount field
    |--------------------------------------------------------------------------
    */

    function formatAmountInput(element) {

        const amount = parseAmount(element.value);

        element.value = amount.toFixed(2);

    }

    /*
    |--------------------------------------------------------------------------
    | Update totals
    |--------------------------------------------------------------------------
    */

    function updateTotals() {

        let totalDebit = 0;
        let totalCredit = 0;

        document.querySelectorAll(
            '#entryLinesTable .debit'
        ).forEach(function (input) {

            totalDebit += parseAmount(input.value);

        });

        document.querySelectorAll(
            '#entryLinesTable .credit'
        ).forEach(function (input) {

            totalCredit += parseAmount(input.value);

        });

        document.getElementById(
            'totalDebit'
        ).value = totalDebit.toFixed(2);

        document.getElementById(
            'totalCredit'
        ).value = totalCredit.toFixed(2);

        document.getElementById(
            'totalDifference'
        ).value = (totalDebit - totalCredit).toFixed(2);

    }

    /*
    |--------------------------------------------------------------------------
    | Reindex all journal line fields
    |--------------------------------------------------------------------------
    */

    function reindexRows() {

        const rows = tableBody.querySelectorAll('tr');

        rows.forEach(function (row, index) {

            row.querySelectorAll(
                'input, select'
            ).forEach(function (element) {

                if (!element.name) {
                    return;
                }

                element.name = element.name.replace(
                    /lines\[\d+\]/,
                    'lines[' + index + ']'
                );

            });

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Add a new journal line
    |--------------------------------------------------------------------------
    */

    function addRow() {

        const firstRow = tableBody.querySelector('tr');

        if (!firstRow) {
            return;
        }

        const newRow = firstRow.cloneNode(true);

        const accountSelect = newRow.querySelector(
            '.account-select'
        );

        const debitInput = newRow.querySelector(
            '.debit'
        );

        const creditInput = newRow.querySelector(
            '.credit'
        );

        const descriptionInput = newRow.querySelector(
            'input[name*="[description]"]'
        );

        if (accountSelect) {
            accountSelect.value = '';
        }

        if (debitInput) {
            debitInput.value = '0.00';
        }

        if (creditInput) {
            creditInput.value = '0.00';
        }

        if (descriptionInput) {
            descriptionInput.value = '';
        }

        tableBody.appendChild(newRow);

        reindexRows();
        updateTotals();

    }

    /*
    |--------------------------------------------------------------------------
    | Remove a journal line
    |--------------------------------------------------------------------------
    */

    function removeRow(button) {

        const rows = tableBody.querySelectorAll('tr');

        if (rows.length <= 1) {

            Swal.fire({
                icon: 'warning',
                title: 'Line Required',
                text: 'At least one journal line is required.',
                confirmButtonColor: '#3085d6'
            });

            return;

        }

        const row = button.closest('tr');

        if (row) {
            row.remove();
        }

        reindexRows();
        updateTotals();

    }

    /*
    |--------------------------------------------------------------------------
    | Add-row button
    |--------------------------------------------------------------------------
    */

    if (addRowButton) {

        addRowButton.addEventListener(
            'click',
            addRow
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Remove-row buttons using event delegation
    |--------------------------------------------------------------------------
    */

    tableBody.addEventListener(
        'click',
        function (event) {

            const removeButton = event.target.closest(
                '.btn-remove-row'
            );

            if (removeButton) {
                removeRow(removeButton);
            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Recalculate totals while typing
    |--------------------------------------------------------------------------
    */

    tableBody.addEventListener(
        'input',
        function (event) {

            if (
                event.target.classList.contains('debit') ||
                event.target.classList.contains('credit')
            ) {
                updateTotals();
            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Format debit and credit when leaving the field
    |--------------------------------------------------------------------------
    */

    tableBody.addEventListener(
        'blur',
        function (event) {

            if (
                event.target.classList.contains('debit') ||
                event.target.classList.contains('credit')
            ) {
                formatAmountInput(event.target);
                updateTotals();
            }

        },
        true
    );

    /*
    |--------------------------------------------------------------------------
    | Copy journal confirmation
    |--------------------------------------------------------------------------
    */

    if (copyButton && copyForm) {

        copyButton.addEventListener(
            'click',
            function () {

                Swal.fire({
                    title: 'Copy Journal Entry?',
                    text: 'A new journal number will be created using this journal entry.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Copy to New',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d'
                }).then(function (result) {

                    if (!result.isConfirmed) {
                        return;
                    }

                    copyButton.disabled = true;

                    copyButton.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1"></span>' +
                        'Copying...';

                    copyForm.submit();

                });

            }
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Validate journal balance before update
    |--------------------------------------------------------------------------
    */

    if (journalForm) {

        journalForm.addEventListener(
            'submit',
            function (event) {

                updateTotals();

                const totalDebit = parseAmount(
                    document.getElementById('totalDebit').value
                );

                const totalCredit = parseAmount(
                    document.getElementById('totalCredit').value
                );

                /*
                 * Convert to cents to avoid floating-point comparison issues.
                 */
                const debitCents = Math.round(totalDebit * 100);
                const creditCents = Math.round(totalCredit * 100);

                if (debitCents !== creditCents) {

                    event.preventDefault();

                    Swal.fire({
                        icon: 'error',
                        title: 'Unbalanced Entry',
                        text: 'Total Debit and Total Credit must match before saving.',
                        confirmButtonColor: '#3085d6'
                    });

                    return;

                }

                const updateButton = document.getElementById(
                    'btnUpdateJournal'
                );

                if (updateButton) {

                    updateButton.disabled = true;

                    updateButton.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1"></span>' +
                        'Updating...';

                }

            }
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Initial setup
    |--------------------------------------------------------------------------
    */

    reindexRows();
    updateTotals();

    /*
    |--------------------------------------------------------------------------
    | Optional Flatpickr
    |--------------------------------------------------------------------------
    |
    | This assumes Flatpickr is already loaded in your admin dashboard.
    |
    */

    if (
        typeof flatpickr !== 'undefined'
    ) {

        flatpickr('#trans_dt', {
            dateFormat: 'm/d/Y',
            allowInput: true
        });

    }

});

</script>

@endsection
