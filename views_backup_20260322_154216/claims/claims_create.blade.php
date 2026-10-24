@extends('admin.admin_dashboard')
@section('title', 'Claims')
@section('admin')
<style>
        #claim-lines-table td,
        #claim-lines-table th {
            padding: 2px 4px !important;
        }

        #claim-lines-table input.form-control-sm {
            padding: 2px 4px !important;
            font-size: 12px !important;
            height: 24px !important;
        }

        .proc-code { width: 65px !important; }
        .m1        { width: 45px !important; text-align: center; }
        .units     { width: 50px !important; text-align: center; }
        .amount-per-unit,
        .total-amount,
        .allowed-field,
        .ins1-paid-field { width: 80px !important; text-align: right !important; }
        .diag1-field,
        .diag2-field { width: 80px !important; }
        .provider-field { width: 70px !important; }
        .pos-field      { width: 55px !important; }
        .delete-line-row { padding: 0 6px !important; }

        /* Payment status coloring */
        /* Existing statuses */

        .claim-unpaid-td {
            background-color: #ffffff !important;
            color: #000000 !important;
        }
        .claim-partial-td {
            background-color: #fff3cd !important; /* light yellow */
        }
        .claim-paid-td {
            background-color: #d4edda !important; /* light green */
        }

        /* NEW: Overpaid (insurance paid more than expected) */
        .claim-overpaid-td {
            background-color: #cfe8ff !important; /* light blue */
        }

        /* Inputs follow the row color */
        .claim-unpaid-td input,
        .claim-unpaid-td .form-control {
            background-color: #ffffff !important;
        }
        .claim-partial-td input,
        .claim-partial-td .form-control {
            background-color: #fff3cd !important;
        }
        .claim-paid-td input,
        .claim-paid-td .form-control {
            background-color: #d4edda !important;
        }
        .claim-overpaid-td input,
        .claim-overpaid-td .form-control {
            background-color: #cfe8ff !important;
        }

</style>
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Claims Data Entry</h4>

                    <div class="d-flex align-items-center mb-3">

                        <div class="btn-group" role="group">
                            {{-- When switching A/N, we usually reset member selection --}}
                            <a href="{{ route('claims.create', ['type' => 'A']) }}"
                            class="btn {{ $type === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Morning Shift
                            </a>
                            <a href="{{ route('claims.create', ['type' => 'N']) }}"
                            class="btn {{ $type === 'N' ? 'btn-warning' : 'btn-outline-warning' }}">
                                Noon Shift
                            </a>
                        </div>
                    </div>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{route('dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
         @if(session('success'))
            <div class="alert alert-success py-1 mb-2">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-1 mb-2">
                <ul class="mb-0 small">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    <!-- ==================== TOP HALF: MEMBER + GRID ==================== -->
    <div class="card shadow-sm mb-2" style="height: 48vh; overflow-y: auto;">
        <div class="card-body p-2">

            {{-- For now, no real POST. You can change action to route later. --}}
            <form id="claimForm" method="POST" action="{{ route('claims.store') }}">
                @csrf

                {{-- MEMBER (Participant) --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label mb-0 small">Member (Participant)</label>
                        <select name="ParticipantId" id="ParticipantId"
                                class="form-select form-select-sm">
                            <option value="">-- Select Member --</option>
                            {{-- Example: later you will pass $members from controller --}}
                            @isset($members)
                                @foreach($members as $m)
                                    <option value="{{ $m->ParticipantId }}"
                                            data-diag1="{{ $m->PrimaryDiagnosis1 }}"
                                            data-diag2="{{ $m->SecondaryDiag1 }}">
                                        {{ $m->ParticipantId }} - {{ $m->LastName ?? $m->last }}, {{ $m->FirstName ?? $m->first }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                    <div class="col-md-8">
                        {{-- Financial Summary --}}
                        <div id="memberSummary" class="border rounded p-2 bg-light" style="display:none;">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Total Billed:</strong>
                                    <div id="sum_billed" class="text-primary fw-bold">0.00</div>
                                </div>
                                <div class="col-md-4">
                                    <strong>Total Paid:</strong>
                                    <div id="sum_paid" class="text-success fw-bold">0.00</div>
                                </div>
                                <div class="col-md-4">
                                    <strong>Balance:</strong>
                                    <div id="sum_balance" class="text-danger fw-bold">0.00</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- GRID HEADER + ADD ROW BUTTON --}}
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold">Service Lines</span>
                    <button type="button" class="btn btn-sm btn-success" id="add-line-row">
                        + Add Row
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-1" id="claim-lines-table">
                        <thead class="table-light small text-nowrap">
                            <tr>
                                <th style="width:110px;">From</th>
                                <th style="width:110px;">To</th>
                                <th style="width:70px;">Proc</th>
                                <th style="width:55px;">M1</th>
                                <th style="width:55px;" class="text-center">Units</th>
                                <th style="width:80px;" class="text-end">Amount</th>
                                <th style="width:80px;" class="text-end">Total</th>
                                <th style="width:80px;">Diag1</th>
                                <th style="width:80px;">Diag2</th>
                                <th style="width:35px;" class="text-center">1</th>
                                <th style="width:35px;" class="text-center">2</th>
                                <th style="width:70px;">Prov</th>
                                <th style="width:55px;">POS</th>
                                <th style="width:80px;" class="text-end">Allowed</th>
                                <th style="width:80px;">Claim #</th>
                                <th style="width:100px;" class="text-end">Ins 1 Paid</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="claim-lines-body" class="small">
                            <tr class="claim-line-row" data-index="0">
                                {{-- From --}}
                                <td>
                                    <input type="date"
                                        name="lines[0][service_from]"
                                        class="form-control form-control-sm service-from">
                                </td>

                                {{-- To --}}
                                <td>
                                    <input type="date"
                                        name="lines[0][service_to]"
                                        class="form-control form-control-sm service-to">
                                </td>

                                {{-- Procedure (varchar 6, default S5102) --}}
                                <td>
                                    <input type="text"
                                        name="lines[0][proc_code]"
                                        class="form-control form-control-sm proc-code"
                                        value="S5102"
                                        maxlength="6">
                                </td>

                                {{-- M1 (varchar 3, default U1) --}}
                                <td>
                                    <input type="text"
                                        name="lines[0][m1]"
                                        class="form-control form-control-sm m1"
                                        value="U1"
                                        maxlength="3">
                                </td>

                                {{-- Units (manual 0–7, 1 digit) --}}
                                <td>
                                    <input type="number"
                                        name="lines[0][units]"
                                        class="form-control form-control-sm units text-center"
                                        min="0" max="7"
                                        maxlength="1">
                                </td>

                                {{-- Amount (decimal 3,2, default 94.66) --}}
                                <td>
                                    <input type="number" step="0.01"
                                        name="lines[0][amount_per_unit]"
                                        class="form-control form-control-sm amount-per-unit text-end"
                                        value="94.66">
                                </td>

                                {{-- Total (Units * Amount) --}}
                                <td>
                                    <input type="number" step="0.01"
                                        name="lines[0][total_amount]"
                                        class="form-control form-control-sm total-amount text-end"
                                        readonly>
                                </td>

                                {{-- Diag1 / Diag2 (from member, readonly) --}}
                                <td>
                                    <input type="text"
                                        name="lines[0][diag1]"
                                        class="form-control form-control-sm diag1-field"
                                        readonly>
                                </td>
                                <td>
                                    <input type="text"
                                        name="lines[0][diag2]"
                                        class="form-control form-control-sm diag2-field"
                                        readonly>
                                </td>

                                {{-- Checkbox 1 / 2 (auto based on diag1/2) --}}
                                <td class="text-center">
                                    <input type="checkbox"
                                        class="form-check-input diag-box-1"
                                        disabled>
                                </td>
                                <td class="text-center">
                                    <input type="checkbox"
                                        class="form-check-input diag-box-2"
                                        disabled>
                                </td>

                                {{-- Provider (varchar 3, default DM, readonly) --}}
                                <td>
                                    <input type="text"
                                        name="lines[0][provider]"
                                        class="form-control form-control-sm provider-field"
                                        value="DM"
                                        readonly>
                                </td>

                                {{-- POS (varchar 2, default 99, readonly) --}}
                                <td>
                                    <input type="text"
                                        name="lines[0][pos]"
                                        class="form-control form-control-sm pos-field"
                                        value="99"
                                        readonly>
                                </td>

                                {{-- Allowed (same as Total at entry) --}}
                                <td>
                                    <input type="number" step="0.01"
                                        name="lines[0][allowed]"
                                        class="form-control form-control-sm allowed-field text-end"
                                        readonly>
                                </td>

                                {{-- Claim Number (varchar 6, empty for now) --}}
                                <td>
                                    <input type="text"
                                        name="lines[0][claim_no]"
                                        class="form-control form-control-sm claim-no-field"
                                        maxlength="6">
                                </td>

                                {{-- Insurance 1 Amount Paid (default 0.00 for now) --}}
                                <td>
                                    <input type="number" step="0.01"
                                        name="lines[0][ins1_paid]"
                                        class="form-control form-control-sm ins1-paid-field text-end"
                                        value="0.00">
                                </td>

                                {{-- Delete row --}}
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger delete-line-row"
                                            title="Delete row">
                                        &times;
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- BUTTONS (UI only for now) --}}
                <div class="d-flex justify-content-end gap-2 mt-2">
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    <button type="reset" class="btn btn-outline-secondary btn-sm" id="reset-form">
                        Clear
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- ==================== BOTTOM HALF: PAYMENTS PLACEHOLDER ==================== -->
    <div class="card shadow-sm" style="height: 48vh;">
        <div class="card-body p-2">

            <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="mb-0">Payments Received from Insurance</h6>
            </div>

            <div class="table-responsive" style="max-height: 90%; overflow-y:auto;">
                <table class="table table-sm table-hover table-striped mb-0" id="payments-table">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Payment Date</th>
                            <th class="small">Claim #</th>
                            <th class="small">Payer</th>
                            <th class="small text-end">Total Amount</th>
                            <th class="small text-end">Total Paid</th>
                            <th class="small text-end">Amt Difference</th>
                            <th class="small">Trace Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center small text-muted">
                                Select a member to see payments.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    </div>
</div>
@endsection

@section('scripts')
<script>

    function formatDateMDY(dateStr) {
        if (!dateStr) return '';
        // Expecting YYYY-MM-DD
        const parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr; // fallback
        return parts[1] + '/' + parts[2] + '/' + parts[0];
    }

    let lineIndex = 1; // next index after initial row

    /* ===== Recalculate a single row (units & total) ===== */
    function recalcRow($row) {
            let units = parseInt($row.find('.units').val() || 0, 10);
            let amt   = parseFloat($row.find('.amount-per-unit').val() || 0);

            if (units < 0) units = 0;
            if (units > 7) units = 7;
            $row.find('.units').val(isNaN(units) ? '' : units);

            const total = units * amt;
            if (!isNaN(total)) {
                $row.find('.total-amount').val(total.toFixed(2));
                $row.find('.allowed-field').val(total.toFixed(2));  // Allowed = Total
            } else {
                $row.find('.total-amount').val('');
                $row.find('.allowed-field').val('');
            }
        }

    /* ===== Apply diag1/diag2 to all rows when member changes ===== */
    function applyDiagsToAllRows(diag1, diag2) {
        $('#claim-lines-body .diag1-field').val(diag1 || '');
        $('#claim-lines-body .diag2-field').val(diag2 || '');
    }

    function createNewRow(index) {
    return `
        <tr class="claim-line-row" data-index="${index}">
            <td>
                <input type="date"
                       name="lines[${index}][service_from]"
                       class="form-control form-control-sm service-from">
            </td>
            <td>
                <input type="date"
                       name="lines[${index}][service_to]"
                       class="form-control form-control-sm service-to">
            </td>
            <td>
                <input type="text"
                       name="lines[${index}][proc_code]"
                       class="form-control form-control-sm proc-code"
                       value="S5102"
                       maxlength="6">
            </td>
            <td>
                <input type="text"
                       name="lines[${index}][m1]"
                       class="form-control form-control-sm m1"
                       value="U1"
                       maxlength="3">
            </td>
            <td>
                <input type="number"
                       name="lines[${index}][units]"
                       class="form-control form-control-sm units text-center"
                       min="0" max="7"
                       maxlength="1">
            </td>
            <td>
                <input type="number" step="0.01"
                       name="lines[${index}][amount_per_unit]"
                       class="form-control form-control-sm amount-per-unit text-end"
                       value="94.66">
            </td>
            <td>
                <input type="number" step="0.01"
                       name="lines[${index}][total_amount]"
                       class="form-control form-control-sm total-amount text-end"
                       readonly>
            </td>
            <td>
                <input type="text"
                       name="lines[${index}][diag1]"
                       class="form-control form-control-sm diag1-field"
                       readonly>
            </td>
            <td>
                <input type="text"
                       name="lines[${index}][diag2]"
                       class="form-control form-control-sm diag2-field"
                       readonly>
            </td>
            <td class="text-center">
                <input type="checkbox"
                       class="form-check-input diag-box-1"
                       disabled>
            </td>
            <td class="text-center">
                <input type="checkbox"
                       class="form-check-input diag-box-2"
                       disabled>
            </td>
            <td>
                <input type="text"
                       name="lines[${index}][provider]"
                       class="form-control form-control-sm provider-field"
                       value="DM"
                       readonly>
            </td>
            <td>
                <input type="text"
                       name="lines[${index}][pos]"
                       class="form-control form-control-sm pos-field"
                       value="99"
                       readonly>
            </td>
            <td>
                <input type="number" step="0.01"
                       name="lines[${index}][allowed]"
                       class="form-control form-control-sm allowed-field text-end"
                       readonly>
            </td>
            <td>
                <input type="text"
                       name="lines[${index}][claim_no]"
                       class="form-control form-control-sm claim-no-field"
                       maxlength="6">
            </td>
            <td>
                <input type="number" step="0.01"
                       name="lines[${index}][ins1_paid]"
                       class="form-control form-control-sm ins1-paid-field text-end"
                       value="0.00">
            </td>
            <td class="text-center">
                <button type="button"
                        class="btn btn-sm btn-outline-danger delete-line-row"
                        title="Delete row">
                    &times;
                </button>
            </td>
        </tr>
    `;
    }

    document.addEventListener('DOMContentLoaded', function () {


        const todayStr = new Date().toISOString().split('T')[0];

        // Set max="today" for existing date inputs
        $('#claim-lines-body').on('DOMNodeInserted', function () {
            $('.service-from, .service-to').attr('max', todayStr);
        });

        // When insurance paid amount changes, update row color
        $(document).on('input change', '.ins1-paid-field', function () {
            const $row = $(this).closest('.claim-line-row');
            updatePaymentColorForRow($row);
        });

        /* --- Member change: fill diag1/diag2 on all rows --- */
        const memberSelect = document.getElementById('ParticipantId');
        if (memberSelect) {

            // Member change
        memberSelect.addEventListener('change', function () {
            const opt   = memberSelect.options[memberSelect.selectedIndex];
            const diag1 = opt ? (opt.getAttribute('data-diag1') || '') : '';
            const diag2 = opt ? (opt.getAttribute('data-diag2') || '') : '';
            const msg = document.querySelector('.alert.alert-success');
            if (msg) {
                msg.style.display = 'none';
            }

             // still push diags to new rows
            applyDiagsToAllRows(diag1, diag2);

            // NEW: load existing claim lines from DB
                const pid = this.value;
                if (pid) {
                    loadExistingLines(pid, diag1, diag2);
                    loadPayments(pid);   // 👈 NEW
                } else {
                    resetGridToSingleEmptyRow(diag1, diag2);
                    clearPaymentsTable();
                }

        });

        /* --- Member financial summary (Total Billed / Paid / Balance) --- */
        $('#ParticipantId').on('change', function () {
            let memberId = $(this).val();

            if (!memberId) {
                $('#memberSummary').hide();
                return;
            }

            $.ajax({
                url: '/claims/member-summary/' + memberId,
                type: 'GET',
                success: function (data) {
                    $('#sum_billed').text(data.billed);
                    $('#sum_paid').text(data.paid);
                    $('#sum_balance').text(data.balance);

                    $('#memberSummary').slideDown();
                }
            });
        });

        // Units / Amount events
        $(document).on('input', '.units, .amount-per-unit', function () {
            const $row = $(this).closest('.claim-line-row');
            recalcRow($row);
        });

        }

        function resetGridToSingleEmptyRow(diag1, diag2) {
            const $tbody = $('#claim-lines-body');
            $tbody.empty();
            lineIndex = 0;
            const html = createNewRow(lineIndex++);
            $tbody.append(html);

            applyDiagsToAllRows(diag1 || '', diag2 || '');
        }

        function loadExistingLines(participantId, defaultDiag1, defaultDiag2) {
            const $tbody = $('#claim-lines-body');
            $tbody.html(`
                <tr><td colspan="17" class="text-center small text-muted">Loading...</td></tr>
            `);

            $.get("{{ url('/claims/member-lines') }}/" + encodeURIComponent(participantId))
                .done(function(rows) {

                    $tbody.empty();
                    lineIndex = 0;

                    if (!rows || !rows.length) {
                        // no old data → single blank row, diags from member
                        const html = createNewRow(lineIndex++);
                        $tbody.append(html);
                        applyDiagsToAllRows(defaultDiag1 || '', defaultDiag2 || '');
                        return;
                    }

                    rows.forEach(function(r) {
                        const i = lineIndex++;
                        const html = `
                            <tr class="claim-line-row" data-index="${i}">
                                <td>
                                     <!-- hidden id for existing row -->
                                    <input type="hidden"
                                        name="lines[${i}][id]"
                                        value="${r.id}">

                                    <input type="date"
                                        name="lines[${i}][service_from]"
                                        class="form-control form-control-sm service-from"
                                        value="${r.service_from ?? ''}">
                                </td>
                                <td>
                                    <input type="date"
                                        name="lines[${i}][service_to]"
                                        class="form-control form-control-sm service-to"
                                        value="${r.service_to ?? ''}">
                                </td>
                                <td>
                                    <input type="text"
                                        name="lines[${i}][proc_code]"
                                        class="form-control form-control-sm proc-code"
                                        value="${r.proc_code ?? 'S5102'}"
                                        maxlength="6">
                                </td>
                                <td>
                                    <input type="text"
                                        name="lines[${i}][m1]"
                                        class="form-control form-control-sm m1"
                                        value="${r.m1 ?? 'U1'}"
                                        maxlength="3">
                                </td>
                                <td>
                                    <input type="number"
                                        name="lines[${i}][units]"
                                        class="form-control form-control-sm units text-center"
                                        min="0" max="7"
                                        value="${r.units ?? ''}">
                                </td>
                                <td>
                                    <input type="number" step="0.01"
                                        name="lines[${i}][amount_per_unit]"
                                        class="form-control form-control-sm amount-per-unit text-end"
                                        value="${r.amount_per_unit ?? '94.66'}">
                                </td>
                                <td>
                                    <input type="number" step="0.01"
                                        name="lines[${i}][total_amount]"
                                        class="form-control form-control-sm total-amount text-end"
                                        value="${r.total_amount ?? ''}"
                                        readonly>
                                </td>
                                <td>
                                    <input type="text"
                                        name="lines[${i}][diag1]"
                                        class="form-control form-control-sm diag1-field"
                                        value="${r.diag1 ?? ''}"
                                        readonly>
                                </td>
                                <td>
                                    <input type="text"
                                        name="lines[${i}][diag2]"
                                        class="form-control form-control-sm diag2-field"
                                        value="${r.diag2 ?? ''}"
                                        readonly>
                                </td>
                                <td class="text-center">
                                    <input type="checkbox"
                                        class="form-check-input diag-box-1"
                                        disabled>
                                </td>
                                <td class="text-center">
                                    <input type="checkbox"
                                        class="form-check-input diag-box-2"
                                        disabled>
                                </td>
                                <td>
                                    <input type="text"
                                        name="lines[${i}][provider]"
                                        class="form-control form-control-sm provider-field"
                                        value="${r.provider ?? 'DM'}"
                                        readonly>
                                </td>
                                <td>
                                    <input type="text"
                                        name="lines[${i}][pos]"
                                        class="form-control form-control-sm pos-field"
                                        value="${r.pos ?? '99'}"
                                        readonly>
                                </td>
                                <td>
                                    <input type="number" step="0.01"
                                        name="lines[${i}][allowed]"
                                        class="form-control form-control-sm allowed-field text-end"
                                        value="${r.allowed ?? ''}"
                                        readonly>
                                </td>
                                <td>
                                    <input type="text"
                                        name="lines[${i}][claim_no]"
                                        class="form-control form-control-sm claim-no-field"
                                        value="${r.claim_no ?? ''}"
                                        maxlength="6">
                                </td>
                                <td>
                                    <input type="number" step="0.01"
                                        name="lines[${i}][ins1_paid]"
                                        class="form-control form-control-sm ins1-paid-field text-end"
                                        value="${r.ins1_paid ?? '0.00'}">
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger delete-line-row"
                                            title="Delete row">
                                        &times;
                                    </button>
                                </td>
                            </tr>
                        `;
                        $tbody.append(html);
                    });

                    // After building from DB, set diag checkboxes per row
                    $('#claim-lines-body .claim-line-row').each(function () {
                        const $row = $(this);
                        updateDiagCheckboxesForRow($row);
                        updatePaymentColorForRow($row);
                    });
                })
                .fail(function () {
                    $tbody.html(`
                        <tr><td colspan="17" class="text-center small text-danger">
                            Error loading previous claims for this member.
                        </td></tr>
                    `);
                });
        }

        /* --- Add Row button --- */
        $('#add-line-row').on('click', function () {
            const $tbody = $('#claim-lines-body');
            const newRowHtml = createNewRow(lineIndex++);
            $tbody.append(newRowHtml);

            // Set max date for the new row’s date inputs
            const todayStr = new Date().toISOString().split('T')[0];
            $tbody.find('.service-from, .service-to').last().attr('max', todayStr);

            // If member selected, apply diags to new rows too
            const opt = memberSelect ? memberSelect.options[memberSelect.selectedIndex] : null;
            if (opt && opt.value) {
                const diag1 = opt.getAttribute('data-diag1') || '';
                const diag2 = opt.getAttribute('data-diag2') || '';
                applyDiagsToAllRows(diag1, diag2);
            }
        });

        /* --- Delete row (keep at least 1) --- */
        $(document).on('click', '.delete-line-row', function () {
            const $rows = $('#claim-lines-body .claim-line-row');
            if ($rows.length <= 1) {
                return; // don't delete the last row
            }
            $(this).closest('tr').remove();
        });

        /* --- Recalculate when dates or amount change --- */
        $(document).on('change', '.service-from, .service-to', function () {
            const $row = $(this).closest('.claim-line-row');
            recalcRow($row);
        });

        $(document).on('input', '.amount-per-unit', function () {
            const $row = $(this).closest('.claim-line-row');
            recalcRow($row);
        });

        /* --- Reset: clear rows and keep one empty row --- */
        $('#reset-form').on('click', function () {
            setTimeout(function () {
                const $tbody = $('#claim-lines-body');
                $tbody.find('.claim-line-row').not(':first').remove();
                $tbody.find('input').val('');
                lineIndex = 1;
            }, 0);
        });

    });

    // Set diag checkboxes based on diag1/diag2
    function updateDiagCheckboxesForRow($row) {
        const d1 = ($row.find('.diag1-field').val() || '').trim();
        const d2 = ($row.find('.diag2-field').val() || '').trim();
        const box1 = $row.find('.diag-box-1');
        const box2 = $row.find('.diag-box-2');

        if (d1 && !d2) {
            box1.prop('checked', true);
            box2.prop('checked', false);
        } else if (d1 && d2) {
            box1.prop('checked', true);
            box2.prop('checked', true);
        } else {
            box1.prop('checked', false);
            box2.prop('checked', false);
        }
    }

    // When member changes, push diag1/diag2 into all rows and update checkboxes
    function applyDiagsToAllRows(diag1, diag2) {
        $('#claim-lines-body .claim-line-row').each(function () {
            const $row = $(this);
            $row.find('.diag1-field').val(diag1 || '');
            $row.find('.diag2-field').val(diag2 || '');
            updateDiagCheckboxesForRow($row);
        });
    }

    // Set row background based on payment info
    function updatePaymentColorForRow($row) {

        const allowed = parseFloat(($row.find('.allowed-field').val() || '').replace(/,/g, '')) || 0;
        const total   = parseFloat(($row.find('.total-amount').val() || '').replace(/,/g, '')) || 0;
        const paid    = parseFloat(($row.find('.ins1-paid-field').val() || '').replace(/,/g, '')) || 0;

        // Expected basis = Allowed if present, otherwise Total
        const basis = allowed > 0 ? allowed : total;

        const $cells = $row.find('td');

        // Clear all previous status classes
        $cells.removeClass('claim-unpaid-td claim-partial-td claim-paid-td claim-overpaid-td');

        // If we don't have any expected amount, just treat as unpaid/neutral
        if (basis <= 0 && paid <= 0) {
            $cells.addClass('claim-unpaid-td');
            return;
        }

        // Tolerance to avoid float issues
        const epsilon = 0.01;

        if (paid >= basis + epsilon) {
            // OVERPAID (insurance paid more than expected)
            $cells.addClass('claim-overpaid-td');
        } else if (paid > 0 && paid < basis - epsilon) {
            // PARTIAL / UNDERPAID
            $cells.addClass('claim-partial-td');
        } else if (Math.abs(paid - basis) <= epsilon) {
            // FULLY PAID (within a few cents)
            $cells.addClass('claim-paid-td');
        } else {
            // UNPAID or essentially zero
            $cells.addClass('claim-unpaid-td');
        }
    }

    function clearPaymentsTable() {
        const $tbody = $('#payments-table tbody');
        $tbody.html(`
            <tr>
                <td colspan="6" class="text-center small text-muted">
                    Select a member to see payments.
                </td>
            </tr>
        `);
    }

    function loadPayments(participantId) {
    const $tbody = $('#payments-table tbody');

    if (!participantId) {
        clearPaymentsTable();
        return;
    }

    $tbody.html(`
        <tr>
            <td colspan="6" class="text-center small text-muted">
                Loading payments...
            </td>
        </tr>
    `);

    $.get("{{ url('/claims/payments') }}/" + encodeURIComponent(participantId))
        .done(function(rows) {
            $tbody.empty();

            if (!rows || !rows.length) {
                $tbody.html(`
                    <tr>
                        <td colspan="6" class="text-center small text-muted">
                            No ERA payments found for this member.
                        </td>
                    </tr>
                `);
                return;
            }

            rows.forEach(function(p) {

                const totalCharge = parseFloat(p.total_charge || 0);
                const totalPaid   = parseFloat(p.total_paid || 0);
                const diff        = (totalCharge - totalPaid).toFixed(2);

                const paymentDate = formatDateMDY(p.payment_date);
                const claimNo     = p.claim_no || '';
                const payer       = p.payer_name || '';
                const trace       = p.trace_number || '';

                const totalChargeStr = isNaN(totalCharge) ? '' : totalCharge.toFixed(2);
                const totalPaidStr   = isNaN(totalPaid)   ? '' : totalPaid.toFixed(2);

                const rowHtml = `
                    <tr>
                        <td class="small">${paymentDate}</td>
                        <td class="small">${claimNo}</td>
                        <td class="small">${payer}</td>
                        <td class="small text-end">${totalChargeStr}</td>
                        <td class="small text-end">${totalPaidStr}</td>
                        <td class="small text-end">${diff}</td>
                        <td class="small">${trace}</td>
                    </tr>
                `;

                $tbody.append(rowHtml);
            });
        })
        .fail(function() {
            $tbody.html(`
                <tr>
                    <td colspan="6" class="text-center small text-danger">
                        Error loading payments for this member.
                    </td>
                </tr>
            `);
        });
    }

</script>
@endsection
