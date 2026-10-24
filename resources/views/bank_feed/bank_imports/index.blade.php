@extends('admin.admin_dashboard')

@section('title', 'Bank Imports')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Bank Feed – Import & Review</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('accounts.dashboard') }}"
                               class="btn btn-primary waves-effect waves-light">
                                {{ t('common.back','Back') }}
                            </a>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('message'))
            <div class="alert alert-success alert-sm">
                {{ session('message') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-sm">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Upload form --}}
        <div class="card mb-4">
            <div class="card-header">
                <strong>Upload Bank CSV</strong>
            </div>

            <div class="card-body">
                <form action="{{ route('bankfeed.import.upload') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="row g-3">
                    @csrf

                    <div class="col-md-4">
                        <label class="form-label">Bank / Account</label>
                        <select name="bank_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Bank --</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->account_id }}">
                                    {{ $bank->account_name }} ({{ $bank->account_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">CSV File</label>
                        <input type="file"
                               name="file"
                               class="form-control form-control-sm"
                               accept=".csv,text/csv"
                               required>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Upload &amp; Import
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Staging grid --}}
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <strong>Imported Transactions (Staging)</strong>

                <div class="d-flex flex-wrap align-items-center gap-3">

                    {{-- Bank filter --}}
                    <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center">
                        <label class="me-2 mb-0 small text-muted">Bank / Account:</label>

                        <select name="filter_bank_id"
                                class="form-select form-select-sm"
                                onchange="this.form.submit()">
                            <option value="">All Banks</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->account_id }}"
                                    {{ request('filter_bank_id') == $bank->account_id ? 'selected' : '' }}>
                                    {{ $bank->account_name }} ({{ $bank->account_id }})
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <form method="POST" action="{{ route('bankfeed.applyRules') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            Re-apply rules to unposted
                        </button>
                    </form>

                    <small id="imports-summary" class="text-muted">
                        Showing {{ $imports->count() }} unposted row{{ $imports->count() == 1 ? '' : 's' }}
                    </small>
                </div>
            </div>

            <form id="bulkPostForm" action="{{ route('bankfeed.post') }}" method="POST">
                @csrf

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="bankImportsTable"
                               class="table table-sm table-striped table-hover mb-0 align-middle w-100">
                            <thead>
                                <tr>
                                    <th style="width: 30px;" data-orderable="false">
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Credit</th>
                                    <th>Party Code</th>
                                    <th>Remarks</th>
                                    <th>Rule</th>
                                    <th>Status</th>
                                    <th data-orderable="false">Ignore</th>
                                    <th data-orderable="false">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($imports as $row)
                                    @php
                                        $status    = $row->status ?? 'new';
                                        $isNew     = ($status === 'new');
                                        $isPosted  = ($status === 'posted');
                                        $isIgnored = ($status === 'ignored');
                                    @endphp

                                    <tr class="import-row"
                                        data-id="{{ $row->id }}"
                                        data-description="{{ $row->description }}"
                                        data-debit="{{ $row->debit }}"
                                        data-credit="{{ $row->credit }}"
                                        data-date="{{ $row->txn_date }}"
                                        data-bank-id="{{ $row->bank_id ?? '' }}"
                                        data-status="{{ $status }}">

                                        {{-- Select checkbox --}}
                                        <td>
                                            @if($isNew)
                                                <input type="checkbox"
                                                       name="selected_rows[]"
                                                       value="{{ $row->id }}"
                                                       class="row-checkbox">
                                            @endif
                                        </td>

                                        {{-- Date --}}
                                        <td data-order="{{ $row->txn_date ? \Carbon\Carbon::parse($row->txn_date)->format('Y-m-d') : '' }}">
                                            @if($row->txn_date)
                                                {{ \Carbon\Carbon::parse($row->txn_date)->format('m/d/Y') }}
                                            @endif
                                        </td>

                                        {{-- Description --}}
                                        <td class="col-description">
                                            <div class="desc-text" title="{{ $row->description }}">
                                                {{ \Illuminate\Support\Str::limit($row->description, 50) }}
                                            </div>
                                        </td>

                                        {{-- Debit --}}
                                        <td class="text-end" data-order="{{ $row->debit ?? 0 }}">
                                            {{ number_format($row->debit ?? 0, 2) }}
                                        </td>

                                        {{-- Credit --}}
                                        <td class="text-end" data-order="{{ $row->credit ?? 0 }}">
                                            {{ number_format($row->credit ?? 0, 2) }}
                                        </td>

                                        {{-- Party Code --}}
                                        <td class="col-party">
                                            @if($isNew)
                                                @php
                                                    $liveSuggested = $row->ui_suggested_party_cd ?? null;
                                                    $dbSuggested   = $row->suggested_party_cd ?? null;
                                                    $suggested     = $dbSuggested ?: $liveSuggested;
                                                    $current       = old('party_cd.' . $row->id, $suggested);
                                                @endphp

                                                <select name="party_cd[{{ $row->id }}]"
                                                        class="form-select form-select-sm party-select"
                                                        data-row="{{ $row->id }}">
                                                    <option value="">-- Select Account --</option>
                                                    @foreach($accounts as $acc)
                                                        <option value="{{ $acc->party_cd }}"
                                                                @selected($current == $acc->party_cd)>
                                                            {{ $acc->party_nm }} - {{ $acc->party_cd }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                @if($suggested)
                                                    <small class="text-success">
                                                        Suggested: {{ $suggested }}
                                                    </small>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        {{-- Remarks --}}
                                        <td>
                                            @if($isNew)
                                                <input type="text"
                                                       name="remarks[{{ $row->id }}]"
                                                       value="{{ old('remarks.' . $row->id) }}"
                                                       class="form-control form-control-sm remarks-input"
                                                       placeholder="Optional remarks">
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        {{-- Rule --}}
                                        <td>
                                            @php
                                                $hitCount  = $row->rule_hits_count ?? 0;
                                                $hitLabels = collect($row->rule_hits ?? [])->pluck('label')->implode(' | ');
                                            @endphp

                                            @if($hitCount > 0)
                                                <span class="badge bg-success"
                                                      data-bs-toggle="tooltip"
                                                      title="{{ $hitLabels }}">
                                                    {{ $hitCount }} rule{{ $hitCount > 1 ? 's' : '' }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">None</span>
                                            @endif
                                        </td>

                                        {{-- Status --}}
                                        <td class="col-posted">
                                            @if($isPosted)
                                                <span class="badge bg-success">Posted</span>
                                            @elseif($isIgnored)
                                                <span class="badge bg-secondary">Ignored</span>
                                            @else
                                                <span class="badge bg-warning text-dark">New</span>
                                            @endif
                                        </td>

                                        {{-- Ignore --}}
                                        <td>
                                            @if($isNew)
                                                <button type="button"
                                                        class="btn btn-outline-danger btn-sm btn-ignore-single"
                                                        data-id="{{ $row->id }}">
                                                    Ignore
                                                </button>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        {{-- Action --}}
                                        <td>
                                            @if($isNew)
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button"
                                                            class="btn btn-outline-primary btn-add-single"
                                                            data-id="{{ $row->id }}">
                                                        Add
                                                    </button>

                                                    <button type="button"
                                                            class="btn btn-outline-secondary btn-learn-rule"
                                                            data-row-id="{{ $row->id }}">
                                                        Learn
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>

                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Bulk buttons --}}
                    <div class="px-3 py-2 border-top d-flex flex-wrap justify-content-end align-items-center gap-2">
                        <div class="ms-auto">
                            <small class="text-muted me-3 d-block d-md-inline">
                                1) Select Party Code and Remarks &nbsp;•&nbsp;
                                2) Select rows and click "Post Selected" or use "Add".
                            </small>

                            <button type="button"
                                    class="btn btn-outline-danger btn-sm me-1"
                                    id="btnIgnoreSelected">
                                Ignore Selected
                            </button>

                            <button type="submit" class="btn btn-success btn-sm">
                                Post Selected to fashtrans
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Modal: Single row post --}}
        <div class="modal fade" id="singlePostModal" tabindex="-1" aria-labelledby="singlePostLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" id="singlePostForm">
                    @csrf

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="singlePostLabel">Post Transaction</h5>
                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                        </div>

                        <div class="modal-body">

                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label mb-0">Account (party_cd)</label>

                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            id="btnOpenAddAccount">
                                        + New Account
                                    </button>
                                </div>

                                <select name="party_cd"
                                        id="modal_party_cd"
                                        class="form-select form-select-sm">
                                    <option value="">-- Select Account --</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->party_cd }}"
                                                data-name="{{ $acc->party_nm }}">
                                            {{ $acc->party_cd }} - {{ $acc->party_nm }}
                                        </option>
                                    @endforeach
                                </select>

                                <small class="text-muted">
                                    This will be used as <strong>party_cd</strong>.
                                </small>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Description</label>
                                <input type="text"
                                       name="description"
                                       id="modal_description"
                                       class="form-control form-control-sm"
                                       placeholder="Description / Payee / Customer">
                            </div>

                            <div class="row mb-2">
                                <div class="col">
                                    <label class="form-label">Debit</label>
                                    <input type="text"
                                           name="debit"
                                           id="modal_debit"
                                           class="form-control form-control-sm">
                                </div>

                                <div class="col">
                                    <label class="form-label">Credit</label>
                                    <input type="text"
                                           name="credit"
                                           id="modal_credit"
                                           class="form-control form-control-sm">
                                </div>
                            </div>

                            <small class="text-muted d-block mb-2">
                                If imported row has Debit &gt; 0, Debit is editable and Credit is locked, and vice versa.
                            </small>

                            <div class="mb-2">
                                <label class="form-label">Reference #</label>
                                <input type="text"
                                       name="ref_no"
                                       id="modal_ref_no"
                                       class="form-control form-control-sm"
                                       placeholder="Invoice #, EOB #, etc.">
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Remarks</label>
                                <input type="text"
                                       name="remarks"
                                       id="modal_remarks"
                                       class="form-control form-control-sm"
                                       placeholder="Optional remarks for fashtrans.remarks">
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-secondary btn-sm"
                                    data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit"
                                    class="btn btn-primary btn-sm">
                                Post This Transaction
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Learn rule --}}
        <div class="modal fade" id="learnRuleModal" tabindex="-1" aria-labelledby="learnRuleLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form id="learnRuleForm">
                    @csrf

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="learnRuleLabel">Create Rule from Transaction</h5>
                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" name="bank_id" id="rule_bank_id">
                            <input type="hidden" name="direction" id="rule_direction">
                            <input type="hidden" id="rule_row_txn_id">

                            <div class="mb-3">
                                <label class="form-label">Original Description</label>
                                <input type="text"
                                       class="form-control form-control-sm"
                                       id="rule_original_description"
                                       readonly>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Pattern Type</label>
                                    <select name="pattern_type"
                                            id="rule_pattern_type"
                                            class="form-select form-select-sm">
                                        <option value="starts_with">Starts with</option>
                                        <option value="contains" selected>Contains</option>
                                        <option value="ends_with">Ends with</option>
                                        <option value="equals">Equals</option>
                                        <option value="regex">Regex</option>
                                    </select>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">Pattern Value</label>
                                    <input type="text"
                                           name="pattern_value"
                                           id="rule_pattern_value"
                                           class="form-control form-control-sm">

                                    <small class="text-muted">
                                        For regex, enter a pattern like <code>/ACH\s+PAYMENT/i</code>.
                                    </small>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">Auto Party Code</label>
                                    <select name="party_cd"
                                            id="rule_party_cd"
                                            class="form-select form-select-sm">
                                        <option value="">-- No auto party code --</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->party_cd }}">
                                                {{ $acc->party_cd }} - {{ $acc->party_nm }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Direction</label>
                                    <input type="text"
                                           id="rule_direction_display"
                                           class="form-control form-control-sm"
                                           readonly>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Priority</label>
                                    <input type="number"
                                           name="priority"
                                           id="rule_priority"
                                           class="form-control form-control-sm"
                                           value="100">
                                </div>
                            </div>

                            <small class="text-muted">
                                Rules are checked in ascending priority. First match wins.
                            </small>
                        </div>

                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-secondary btn-sm"
                                    data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit"
                                    class="btn btn-primary btn-sm">
                                Save Rule
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-container .select2-selection--single {
        height: 31px !important;
        padding: 3px 6px !important;
        line-height: 25px !important;
    }

    .select2-selection__rendered {
        line-height: 25px !important;
    }

    .select2-selection__arrow {
        height: 28px !important;
    }

    .col-description {
        max-width: 300px;
    }

    .col-description .desc-text {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .col-party {
        min-width: 230px;
        width: 230px;
    }

    .remarks-input {
        min-width: 180px;
    }

    #bankImportsTable th,
    #bankImportsTable td {
        white-space: nowrap;
    }
</style>

@endsection

@section('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ------------------ CSRF TOKEN ------------------
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    if (!csrfToken) {
        const anyToken = document.querySelector('input[name="_token"]');
        if (anyToken) {
            csrfToken = anyToken.value;
        }
    }

    let bankImportsTable = null;

    // ------------------ BOOTSTRAP TOOLTIPS ------------------
    function initTooltips() {
        if (typeof bootstrap === 'undefined') return;

        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));

        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // ------------------ SELECT2 ------------------
    function initSelect2() {
        if (window.jQuery && jQuery().select2) {
            $('.party-select').select2({
                width: '100%',
                placeholder: '-- Select Account --',
                allowClear: true
            });
        }
    }

    initTooltips();
    initSelect2();

    // ------------------ DATATABLE ------------------
    if (window.jQuery && $.fn.DataTable) {
        bankImportsTable = $('#bankImportsTable').DataTable({
            pageLength: -1,
            lengthMenu: [
                [50, 100, 200, 300, 400, -1],
                [50, 100, 200, 300, 400, 'All']
            ],
            ordering: true,
            order: [[1, 'asc']],
            scrollX: true,
            language: {
                emptyTable: "No imports yet."
            },
            columnDefs: [
                { orderable: false, targets: [0, 9, 10] }
            ]
        });
    }

    // ------------------ SELECT ALL ------------------
    const selectAll = document.getElementById('select-all');

    function getAppliedCheckboxes() {
        if (bankImportsTable) {
            return bankImportsTable.rows({ search: 'applied' }).nodes().to$()
                .find('.row-checkbox')
                .toArray();
        }

        return Array.from(document.querySelectorAll('.row-checkbox'));
    }

    function syncSelectAllCheckbox() {
        if (!selectAll) return;

        const rowCbs = getAppliedCheckboxes();

        if (rowCbs.length === 0) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
            return;
        }

        const checkedCount = rowCbs.filter(cb => cb.checked).length;

        selectAll.checked = (checkedCount === rowCbs.length);
        selectAll.indeterminate = (checkedCount > 0 && checkedCount < rowCbs.length);
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            const rowCbs = getAppliedCheckboxes();

            rowCbs.forEach(cb => {
                cb.checked = selectAll.checked;
            });

            syncSelectAllCheckbox();
        });
    }

    $(document).on('change', '.row-checkbox', function () {
        syncSelectAllCheckbox();
    });

    // ------------------ MODAL REFS ------------------
    const postModalEl   = document.getElementById('singlePostModal');
    const postForm      = document.getElementById('singlePostForm');
    const postModal     = postModalEl ? new bootstrap.Modal(postModalEl) : null;

    const accountSel    = document.getElementById('modal_party_cd');
    const descInput     = document.getElementById('modal_description');
    const debitInput    = document.getElementById('modal_debit');
    const creditInput   = document.getElementById('modal_credit');
    const refInput      = document.getElementById('modal_ref_no');
    const remarksInput  = document.getElementById('modal_remarks');

    const learnRuleEl    = document.getElementById('learnRuleModal');
    const learnModal     = learnRuleEl ? new bootstrap.Modal(learnRuleEl) : null;
    const learnForm      = document.getElementById('learnRuleForm');
    const ruleBankId     = document.getElementById('rule_bank_id');
    const ruleDirection  = document.getElementById('rule_direction');
    const ruleRowTxnId   = document.getElementById('rule_row_txn_id');
    const ruleOrigDesc   = document.getElementById('rule_original_description');
    const rulePatternVal = document.getElementById('rule_pattern_value');
    const ruleDirectionDisplay = document.getElementById('rule_direction_display');
    const rulePriority   = document.getElementById('rule_priority');
    const rulePartyCdSel = document.getElementById('rule_party_cd');

    const bulkPostForm   = document.getElementById('bulkPostForm');
    const btnIgnoreSelected = document.getElementById('btnIgnoreSelected');

    // ------------------ OPEN SINGLE POST MODAL ------------------
    function openModalForRow(row) {
        if (!postModal || !postForm) return;

        const id          = row.dataset.id;
        const description = row.dataset.description || '';
        const debit       = parseFloat(row.dataset.debit || '0') || 0;
        const credit      = parseFloat(row.dataset.credit || '0') || 0;

        if (accountSel) accountSel.value = '';
        if (descInput) descInput.value = description;
        if (refInput) refInput.value = '';
        if (remarksInput) remarksInput.value = '';

        if (debitInput && creditInput) {
            if (debit > 0 && credit === 0) {
                debitInput.value     = debit.toFixed(2);
                creditInput.value    = '0.00';
                debitInput.readOnly  = false;
                creditInput.readOnly = true;
            } else if (credit > 0 && debit === 0) {
                creditInput.value    = credit.toFixed(2);
                debitInput.value     = '0.00';
                creditInput.readOnly = false;
                debitInput.readOnly  = true;
            } else {
                debitInput.value     = debit ? debit.toFixed(2) : '';
                creditInput.value    = credit ? credit.toFixed(2) : '';
                debitInput.readOnly  = false;
                creditInput.readOnly = false;
            }
        }

        postForm.action = '{{ url('/bank-feed/post-one') }}/' + id;
        postModal.show();
    }

    // ------------------ DESCRIPTION AUTO-FILL ------------------
    if (accountSel && descInput) {
        accountSel.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const name = opt ? opt.getAttribute('data-name') : '';

            if (name && !descInput.value) {
                descInput.value = name;
            }
        });
    }

    // ------------------ DELEGATED CLICK HANDLERS ------------------
    document.addEventListener('click', function (e) {
        const target = e.target;

        // Single Add
        const btnAdd = target.closest('.btn-add-single');
        if (btnAdd) {
            const id  = btnAdd.dataset.id;
            const row = document.querySelector('tr.import-row[data-id="' + id + '"]');

            if (row && (row.dataset.status || 'new') === 'new') {
                openModalForRow(row);
            }

            return;
        }

        // Learn Rule
        const btnLearn = target.closest('.btn-learn-rule');
        if (btnLearn && learnModal) {
            const rowId = btnLearn.dataset.rowId;
            const row = document.querySelector('tr.import-row[data-id="' + rowId + '"]');

            if (!row) return;

            const desc   = row.dataset.description || '';
            const debit  = parseFloat(row.dataset.debit || '0') || 0;
            const credit = parseFloat(row.dataset.credit || '0') || 0;
            const bankId = row.dataset.bankId || '';

            const amount = credit - debit;
            const direction = amount < 0 ? 'D' : (amount > 0 ? 'C' : 'Any');
            const dirText = direction === 'D' ? 'Debit' : (direction === 'C' ? 'Credit' : 'Any');

            if (ruleBankId) ruleBankId.value = bankId;
            if (ruleDirection) ruleDirection.value = direction;
            if (ruleRowTxnId) ruleRowTxnId.value = rowId;
            if (ruleOrigDesc) ruleOrigDesc.value = desc;
            if (rulePatternVal) rulePatternVal.value = desc;
            if (ruleDirectionDisplay) ruleDirectionDisplay.value = dirText;
            if (rulePriority) rulePriority.value = 100;
            if (rulePartyCdSel) rulePartyCdSel.value = '';

            learnModal.show();
            return;
        }

        // Ignore Single
        const btnIgnoreSingle = target.closest('.btn-ignore-single');
        if (btnIgnoreSingle && csrfToken) {
            const id = btnIgnoreSingle.dataset.id;
            if (!id) return;

            if (!confirm('Ignore this transaction?')) {
                return;
            }

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('selected_rows[]', id);

            fetch('{{ route('bankfeed.ignore') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Error ignoring row.');
                    return;
                }

                window.location.reload();
            })
            .catch(err => alert(err.message));

            return;
        }

        // Ignore Selected
        if (btnIgnoreSelected && target.closest('#btnIgnoreSelected') && csrfToken) {
            let ids = [];

            if (bankImportsTable) {
                ids = bankImportsTable.rows().nodes().to$()
                    .find('.row-checkbox:checked')
                    .map(function () {
                        return this.value;
                    })
                    .get();
            } else {
                ids = Array.from(document.querySelectorAll('.row-checkbox:checked'))
                    .map(cb => cb.value);
            }

            if (ids.length === 0) {
                alert('No rows selected to ignore.');
                return;
            }

            if (!confirm('Ignore selected transaction(s)?')) {
                return;
            }

            const formData = new FormData();
            formData.append('_token', csrfToken);

            ids.forEach(id => {
                formData.append('selected_rows[]', id);
            });

            fetch('{{ route('bankfeed.ignore') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Error ignoring selected rows.');
                    return;
                }

                window.location.reload();
            })
            .catch(err => alert(err.message));

            return;
        }
    });

    // ------------------ DOUBLE CLICK ROW TO ADD ------------------
    $(document).on('dblclick', 'tr.import-row', function () {
        const status = this.dataset.status || 'new';

        if (status !== 'new') {
            return;
        }

        openModalForRow(this);
    });

    // ------------------ LEARN RULE SAVE ------------------
    if (learnForm && csrfToken) {
        learnForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(learnForm);

            fetch('{{ route('bank-rules.store-from-transaction') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));

                if (!res.ok || data.status !== 'ok') {
                    throw new Error(data.message || 'Error saving rule.');
                }

                return data;
            })
            .then(() => {
                if (learnModal) learnModal.hide();
                window.location.reload();
            })
            .catch((err) => {
                alert(err.message || 'Error saving rule.');
            });
        });
    }

    // ------------------ SINGLE POST ------------------
    if (postForm && csrfToken) {
        postForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(postForm);
            const actionUrl = postForm.action;

            fetch(actionUrl, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Error posting transaction.');
                    return;
                }

                if (postModal) postModal.hide();
                window.location.reload();
            })
            .catch(err => alert(err.message));
        });
    }

    // ------------------ BULK POST ------------------
    if (bulkPostForm && csrfToken) {
        bulkPostForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('_token', csrfToken);

            if (bankImportsTable) {
                bankImportsTable.rows().nodes().to$().find('input, select, textarea').each(function () {
                    if (!this.name) return;

                    if ((this.type === 'checkbox' || this.type === 'radio') && !this.checked) {
                        return;
                    }

                    formData.append(this.name, this.value);
                });
            } else {
                new FormData(bulkPostForm).forEach((value, key) => {
                    formData.append(key, value);
                });
            }

            const selectedCount = formData.getAll('selected_rows[]').length;

            if (selectedCount === 0) {
                alert('No rows selected to post.');
                return;
            }

            fetch(bulkPostForm.action, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Error posting selected rows.');
                    return;
                }

                window.location.reload();
            })
            .catch(err => alert(err.message));
        });
    }

});
</script>

@endsection
