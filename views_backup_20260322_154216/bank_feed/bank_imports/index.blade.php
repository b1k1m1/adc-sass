@extends('admin.admin_dashboard')
@section('title', 'Bank Imports')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Bank Feed – Import & Review </h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('accounts.dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        {{-- Flash messages (for non-AJAX flows) --}}
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
                <form action="{{ route('bankfeed.import.upload') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                    @csrf

                    {{-- Bank selection from fashmsbank --}}
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

        {{-- Staging grid + bulk post --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Imported Transactions (Staging)</strong>

                <div class="d-flex align-items-center gap-3">
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
                    <form method="POST" action="{{ route('bankfeed.applyRules') }}" class="ms-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            Re-apply rules to unposted
                        </button>
                    </form>

                    {{-- Summary --}}
                    <small id="imports-summary" class="text-muted">
                        Page {{ $imports->currentPage() }} of {{ $imports->lastPage() }},
                        showing {{ $imports->count() }} of {{ $imports->total() }} rows
                    </small>
                </div>
            </div>

            <form id="bulkPostForm" action="{{ route('bankfeed.post') }}" method="POST">
                @csrf

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 30px;">
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
                                    <th>Ignore</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                            <tbody>
                                @forelse($imports as $row)
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
                                        {{-- Select checkbox (only for NEW) --}}
                                        <td>
                                            @if($isNew)
                                                <input type="checkbox"
                                                    name="selected_rows[]"
                                                    value="{{ $row->id }}"
                                                    class="row-checkbox">
                                            @endif
                                        </td>

                                        {{-- Date --}}
                                        <td>
                                            @if($row->txn_date)
                                                {{ \Carbon\Carbon::parse($row->txn_date)->format('m/d/Y') }}
                                            @endif
                                        </td>

                                        {{-- Description --}}
                                        <td class="col-description">
                                            <div class="desc-text"
                                                title="{{ $row->description }}">
                                                {{ \Illuminate\Support\Str::limit($row->description, 50) }}
                                            </div>
                                        </td>

                                        {{-- Debit / Credit --}}
                                        <td class="text-end">{{ number_format($row->debit ?? 0, 2) }}</td>
                                        <td class="text-end">{{ number_format($row->credit ?? 0, 2) }}</td>

                                        {{-- Party Code dropdown (only NEW) --}}
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

                                        {{-- Remarks (only NEW) --}}
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

                                        {{-- Rule hits --}}
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

                                        {{-- Ignore column: per-row ignore button (only NEW) --}}
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

                                        {{-- Action column --}}
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
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">No imports yet.</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination + bulk buttons --}}
                    <div class="px-3 py-2 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            {{ $imports->links() }}
                        </div>

                        <div class="ms-auto">
                            <small class="text-muted me-3 d-block d-md-inline">
                                1) Select Party Code and Remarks &nbsp;•&nbsp;
                                2) Select rows and click "Post Selected" or use "Add".
                            </small>

                            {{-- NEW: Ignore Selected --}}
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

        {{-- Modal for single-row categorize & post --}}
        {{-- (UNCHANGED CONTENT BELOW, only JS tweaks later) --}}
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

        {{-- Learn rule modal, Add account modal, styles... (unchanged) --}}
        {{-- NEW: Modal: Learn rule from transaction --}}
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
    {{-- ... keep your existing modals & CSS here exactly as before ... --}}

    {{-- Select2 styling --}}
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

         /* NEW: tighten description + party columns */
        .col-description {
            max-width: 260px;
        }
        .col-description .desc-text {
            max-width: 260px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .col-party {
            max-width: 200px;
            width: 200px;
        }
    </style>

    @endsection

    @section('scripts')

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ------------------ CSRF TOKEN ------------------
        let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        if (!csrfToken) {
            // fallback: look for any hidden _token input
            const anyToken = document.querySelector('input[name="_token"]');
            if (anyToken) {
                csrfToken = anyToken.value;
            }
        }

        // ------------------ SELECT ALL ------------------
        const selectAll = document.getElementById('select-all');
        function syncSelectAllCheckbox() {
            if (!selectAll) return;
            const rowCbs = Array.from(document.querySelectorAll('.row-checkbox'));
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
                document.querySelectorAll('.row-checkbox').forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }

        // ------------------ BOOTSTRAP TOOLTIPS ------------------
        function initTooltips() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        initTooltips();

        // ------------------ SELECT2 ON PARTY DROPDOWNS ------------------
        function initSelect2() {
            if (window.jQuery && jQuery().select2) {
                $('.party-select').select2({
                    width: '100%',
                    placeholder: '-- Select Account --',
                    allowClear: true
                });
            }
        }
        initSelect2();

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

        // Add Account modal
        const addAccountEl    = document.getElementById('addAccountModal');
        const addAccountModal = addAccountEl ? new bootstrap.Modal(addAccountEl) : null;
        const addAccountForm  = document.getElementById('addAccountForm');
        const addError        = document.getElementById('addAccountError');
        const addSuccess      = document.getElementById('addAccountSuccess');
        const newPartyCd      = document.getElementById('new_party_cd');
        const newPartyNm      = document.getElementById('new_party_nm');
        const newAccountType  = document.getElementById('new_account_type');
        const newGlId         = document.getElementById('new_gl_id');
        const btnOpenAddAccount = document.getElementById('btnOpenAddAccount');

        // Learn Rule modal
        const learnRuleEl    = document.getElementById('learnRuleModal');
        const learnModal     = learnRuleEl ? new bootstrap.Modal(learnRuleEl) : null;
        const learnForm      = document.getElementById('learnRuleForm');
        const ruleBankId     = document.getElementById('rule_bank_id');
        const ruleDirection  = document.getElementById('rule_direction');
        const ruleRowTxnId   = document.getElementById('rule_row_txn_id');
        const ruleOrigDesc   = document.getElementById('rule_original_description');
        const rulePatternType= document.getElementById('rule_pattern_type');
        const rulePatternVal = document.getElementById('rule_pattern_value');
        const ruleDirectionDisplay = document.getElementById('rule_direction_display');
        const rulePriority   = document.getElementById('rule_priority');
        const rulePartyCdSel = document.getElementById('rule_party_cd');

        const bulkPostForm   = document.getElementById('bulkPostForm');
        const btnIgnoreSelected = document.getElementById('btnIgnoreSelected');

        // ------------------ OPEN MODAL FOR ROW ------------------
        function openModalForRow(row) {
            if (!postModal || !postForm) return;

            const id          = row.dataset.id;
            const description = row.dataset.description || '';
            const debit       = parseFloat(row.dataset.debit || '0') || 0;
            const credit      = parseFloat(row.dataset.credit || '0') || 0;

            if (accountSel) accountSel.value = '';
            if (descInput)  descInput.value = description;
            if (refInput)   refInput.value = '';
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

        // ------------------ DELEGATED CLICK HANDLERS ------------------
        document.addEventListener('click', function (e) {
            const target = e.target;

            // Row-level "Add" button
            let btnAdd = target.closest('.btn-add-single');
            if (btnAdd) {
                const id  = btnAdd.dataset.id;
                const row = document.querySelector('tr.import-row[data-id="' + id + '"]');
                if (row) {
                    const status = row.dataset.status || 'new';
                    if (status === 'new') {
                        openModalForRow(row);
                    }
                }
                return;
            }

            // Row double-click is handled separately below

            let btnLearn = target.closest('.btn-learn-rule');
            if (btnLearn && learnModal) {
                const rowId = btnLearn.dataset.rowId;
                const row   = document.querySelector('tr.import-row[data-id="' + rowId + '"]');
                if (!row) return;

                const desc   = row.dataset.description || '';
                const debit  = parseFloat(row.dataset.debit || '0') || 0;
                const credit = parseFloat(row.dataset.credit || '0') || 0;
                const bankId = row.dataset.bankId || '';

                const amount    = credit - debit; // >0 credit, <0 debit
                const direction = amount < 0 ? 'D' : (amount > 0 ? 'C' : 'Any');
                const dirText   = direction === 'D'
                    ? 'Debit'
                    : (direction === 'C' ? 'Credit' : 'Any');

                if (ruleBankId)             ruleBankId.value             = bankId;
                if (ruleDirection)          ruleDirection.value          = direction;
                if (ruleRowTxnId)           ruleRowTxnId.value           = rowId;
                if (ruleOrigDesc)           ruleOrigDesc.value           = desc;
                if (rulePatternVal)         rulePatternVal.value         = desc;
                if (ruleDirectionDisplay)   ruleDirectionDisplay.value   = dirText;
                if (rulePriority)           rulePriority.value           = 100;
                if (rulePartyCdSel)         rulePartyCdSel.value         = '';

                learnModal.show();
                return;
            }

            // Row-level "Ignore" button
            let btnIgnoreSingle = target.closest('.btn-ignore-single');
            if (btnIgnoreSingle && csrfToken) {
                const id = btnIgnoreSingle.dataset.id;
                if (!id) return;

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
                    refreshTableBody();
                })
                .catch(err => alert(err.message));

                return;
            }

            // Bulk "Ignore Selected" button
            if (btnIgnoreSelected && target.closest('#btnIgnoreSelected') && csrfToken) {
                const ids = Array.from(document.querySelectorAll('.row-checkbox:checked'))
                    .map(cb => cb.value);

                if (ids.length === 0) {
                    alert('No rows selected to ignore.');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken);
                ids.forEach(id => formData.append('selected_rows[]', id));

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
                    refreshTableBody();
                })
                .catch(err => alert(err.message));

                return;
            }

            // Open "Add New Account" modal
            const btnOpenAddAcc = target.closest('#btnOpenAddAccount');
            if (btnOpenAddAcc && addAccountModal) {
                if (newPartyCd) newPartyCd.value = '';
                if (newPartyNm) newPartyNm.value = '';
                if (newAccountType) newAccountType.value = '';
                if (newGlId) newGlId.value = '';

                if (addError) {
                    addError.classList.add('d-none');
                    addError.textContent = '';
                }
                if (addSuccess) {
                    addSuccess.classList.add('d-none');
                    addSuccess.textContent = '';
                }

                addAccountModal.show();
                return;
            }
        });

        // ------------------ ROW DOUBLE-CLICK TO OPEN MODAL ------------------
        function bindRowDblClick() {
            document.querySelectorAll('tr.import-row').forEach(row => {
                row.addEventListener('dblclick', function () {
                    const status = this.dataset.status || 'new';
                    if (status !== 'new') return;
                    openModalForRow(this);
                });
            });
        }
        bindRowDblClick();

        // ------------------ DESCRIPTION AUTO-FILL IN MODAL ------------------
        if (accountSel && descInput) {
            accountSel.addEventListener('change', function () {
                const opt  = this.options[this.selectedIndex];
                const name = opt ? opt.getAttribute('data-name') : '';
                if (name && !descInput.value) {
                    descInput.value = name;
                }
            });
        }

        // ------------------ AJAX: ADD ACCOUNT ------------------
        if (addAccountForm && csrfToken) {
            addAccountForm.addEventListener('submit', function (e) {
                e.preventDefault();

                if (addError) {
                    addError.classList.add('d-none');
                    addError.textContent = '';
                }
                if (addSuccess) {
                    addSuccess.classList.add('d-none');
                    addSuccess.textContent = '';
                }

                const payload = {
                    _token: csrfToken,
                    party_cd: newPartyCd ? newPartyCd.value.trim() : '',
                    party_nm: newPartyNm ? newPartyNm.value.trim() : '',
                    account_type: newAccountType ? newAccountType.value : '',
                    gl_id: newGlId ? newGlId.value : '',
                };

                fetch('{{ route('accountmasters.quickAdd') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                })
                .then(async (res) => {
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        throw new Error(data.message || 'Error saving account.');
                    }
                    return data;
                })
                .then((data) => {
                    if (addSuccess) {
                        addSuccess.textContent = 'Account added successfully.';
                        addSuccess.classList.remove('d-none');
                    }

                    const acc = data.account;

                    if (accountSel) {
                        const opt = document.createElement('option');
                        opt.value = acc.party_cd;
                        opt.setAttribute('data-name', acc.party_nm);
                        opt.textContent = acc.party_cd + ' - ' + acc.party_nm;
                        accountSel.appendChild(opt);

                        accountSel.value = acc.party_cd;
                        if (descInput && !descInput.value) {
                            descInput.value = acc.party_nm;
                        }
                    }

                    if (addAccountModal) {
                        setTimeout(() => addAccountModal.hide(), 600);
                    }
                })
                .catch((err) => {
                    if (addError) {
                        addError.textContent = err.message;
                        addError.classList.remove('d-none');
                    }
                });
            });
        }

        // ------------------ AJAX: LEARN RULE SAVE ------------------
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
                .then((data) => {
                    if (learnModal) learnModal.hide();
                    refreshTableBody();
                })
                .catch((err) => {
                    alert(err.message || 'Error saving rule.');
                });
            });
        }

        // ------------------ AJAX: SINGLE POST ------------------
        if (postForm && csrfToken) {
            postForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData  = new FormData(postForm);
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
                    refreshTableBody();
                })
                .catch(err => alert(err.message));
            });
        }

        // ------------------ AJAX: BULK POST ------------------
        if (bulkPostForm && csrfToken) {
            bulkPostForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(bulkPostForm);

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
                    refreshTableBody();
                })
                .catch(err => alert(err.message));
            });
        }

        // ------------------ REFRESH TABLE BODY ------------------
        function refreshTableBody() {
            const url = window.location.href;
            fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc    = parser.parseFromString(html, 'text/html');

                const newTbody = doc.querySelector('tbody');
                const oldTbody = document.querySelector('tbody');

                if (newTbody && oldTbody) {
                    oldTbody.innerHTML = newTbody.innerHTML;
                }

                const newSummary = doc.querySelector('#imports-summary');
                const oldSummary = document.querySelector('#imports-summary');
                if (newSummary && oldSummary) {
                    oldSummary.innerHTML = newSummary.innerHTML;
                }

                initSelect2();
                initTooltips();
                bindRowDblClick();
                bindLearnButtons();
                bindIgnoreButtons();
                syncSelectAllCheckbox();
            });
        }
    });
</script>

@endsection
