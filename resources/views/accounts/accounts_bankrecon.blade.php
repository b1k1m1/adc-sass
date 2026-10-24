@extends('admin.admin_dashboard')
@section('title', 'Bank Reconciliation')
@section('admin')

@section('page-toolbar')
<a id="btnReport"
   class="btn btn-outline-secondary d-none"
   target="_blank"
   href="#">
    Report
</a>

<a id="btnSimplePdf"
   class="btn btn-primary d-none"
   target="_blank"
   href="#">
    Simplified PDF
</a>

<a id="btnSimpleExcel"
   class="btn btn-success d-none"
   href="#">
    Simplified Excel
</a>

<button id="btnClose"
        type="button"
        class="btn btn-outline-danger d-none">
    Close Month
</button>

<a href="{{ url()->previous() }}"
   class="btn btn-primary waves-effect waves-light">
    {{ t('common.back', 'Back') }}
</a>

@endsection

<div class="page-content">
    <div class="container-fluid">

        <style>
            .num {
                text-align: right;
                white-space: nowrap;
            }

            .nowrap {
                white-space: nowrap;
            }

            .summary-box {
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 10px;
                padding: 12px;
            }

            .diff-ok {
                color: #198754;
                font-weight: 700;
            }

            .diff-bad {
                color: #dc3545;
                font-weight: 700;
            }

            #txnTable th,
            #txnTable td {
                padding-top: 0.35rem;
                padding-bottom: 0.35rem;
                vertical-align: middle;
            }

            #txnTable th.col-cleared,
            #txnTable td.col-cleared {
                width: 88px;
                min-width: 88px;
                max-width: 88px;
                white-space: nowrap;
            }

            #txnTable th.col-date,
            #txnTable td.col-date {
                width: 105px;
                white-space: nowrap;
            }

            #txnTable th.col-type,
            #txnTable td.col-type {
                width: 72px;
                white-space: nowrap;
            }

            #txnTable th.col-ref,
            #txnTable td.col-ref {
                width: 150px;
                white-space: nowrap;
            }

            #txnTable th.col-check,
            #txnTable td.col-check {
                width: 105px;
                white-space: nowrap;
            }

            #txnTable th.col-amount,
            #txnTable td.col-amount {
                width: 115px;
                white-space: nowrap;
            }

            .recon-locked {
                background-color: #f8f9fa;
            }
        </style>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0 font-size-18">Bank Reconciliation</h4>
                        <div class="text-muted small mt-1">
                            Older uncleared transactions are carried forward automatically.
                        </div>
                    </div>

                    <div class="page-title-right">
                        @yield('page-toolbar')
                    </div>
                </div>
            </div>
        </div>

        <div id="messageBox" class="alert d-none" role="alert"></div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label for="bank_id" class="form-label">Bank</label>
                        <select id="bank_id" class="form-select">
                            <option value="">-- Select Bank --</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->account_id }}">
                                    {{ $bank->account_name }} ({{ $bank->account_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="period" class="form-label">Statement Period</label>
                        <input id="period"
                               type="month"
                               class="form-control"
                               value="{{ now()->format('Y-m') }}">
                    </div>

                    <div class="col-md-2">
                        <button id="btnLoad"
                                type="button"
                                class="btn btn-primary w-100">
                            Load Month
                        </button>
                    </div>

                    <div class="col-md-2">
                        <div class="small text-muted">
                            Recon ID: <span id="recon_id_txt">-</span>
                        </div>
                        <div class="small text-muted">
                            Status: <span id="recon_status">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="reconDetails" class="d-none">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label for="opening_balance" class="form-label">
                                Opening Balance
                            </label>
                            <input id="opening_balance"
                                   type="number"
                                   step="0.01"
                                   class="form-control num"
                                   value="0.00">
                        </div>

                        <div class="col-md-2">
                            <label for="closing_balance" class="form-label">
                                Statement Balance
                            </label>
                            <input id="closing_balance"
                                   type="number"
                                   step="0.01"
                                   class="form-control num"
                                   value="0.00">
                        </div>

                        <div class="col-md-2">
                            <label for="closing_dt" class="form-label">
                                Statement Ending Date
                            </label>
                            <input id="closing_dt"
                                   type="date"
                                   class="form-control">
                        </div>

                        <div class="col-md-2">
                            <button id="btnSaveHeader"
                                    type="button"
                                    class="btn btn-success w-100">
                                Save Balances
                            </button>
                        </div>

                        <div class="col-md-4">
                            <div class="summary-box">
                                <div class="d-flex justify-content-between">
                                    <span>Cleared Deposits:</span>
                                    <span class="num" id="sum_deps">0.00</span>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span>Cleared Payments:</span>
                                    <span class="num" id="sum_paym">0.00</span>
                                </div>

                                <hr class="my-2">

                                <div class="d-flex justify-content-between">
                                    <span>Cleared Balance:</span>
                                    <span class="num" id="sum_cleared">0.00</span>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span>Difference:</span>
                                    <span class="num diff-bad" id="sum_diff">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="small text-muted mt-2">
                        Checkboxes save immediately. You do not need to press Save Balances
                        after checking or unchecking transactions.
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="txnTable"
                               class="table table-sm table-striped align-middle w-100">
                            <thead>
                                <tr>
                                    <th class="nowrap col-cleared">
                                        <input type="checkbox"
                                               id="br_check_all"
                                               class="form-check-input me-1">
                                        Cleared
                                    </th>
                                    <th class="nowrap col-date">Date</th>
                                    <th class="nowrap col-type">Type</th>
                                    <th>Description</th>
                                    <th class="nowrap col-ref">Ref</th>
                                    <th class="nowrap col-check">Check</th>
                                    <th class="num nowrap col-amount">Payment</th>
                                    <th class="num nowrap col-amount">Deposit</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
let reconId = null;
let table = null;
let reconStatus = null;

function money(value) {
    const number = parseFloat(value || 0);

    return number.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDateMDY(value) {
    const rawDate = String(value || '').substring(0, 10);

    if (!rawDate) {
        return '';
    }

    const parts = rawDate.split('-');

    if (parts.length !== 3) {
        return rawDate;
    }

    return parts[1] + '/' + parts[2] + '/' + parts[0];
}

function showMessage(message, type = 'success') {
    $('#messageBox')
        .removeClass('d-none alert-success alert-danger alert-warning alert-info')
        .addClass('alert-' + type)
        .text(message);
}

function hideMessage() {
    $('#messageBox').addClass('d-none').text('');
}

function setSummary(summary) {
    $('#sum_deps').text(money(summary.cleared_deps));
    $('#sum_paym').text(money(summary.cleared_paym));
    $('#sum_cleared').text(money(summary.cleared_balance));
    $('#sum_diff').text(money(summary.difference));

    const difference = Math.abs(parseFloat(summary.difference || 0));

    $('#sum_diff')
        .toggleClass('diff-ok', difference < 0.005)
        .toggleClass('diff-bad', difference >= 0.005);
}

function checkboxHtml(row, checked) {
    return `
        <input type="checkbox"
               class="form-check-input br-clear"
               data-trans_no="${row.trans_no}"
               data-trans_dt="${row.trans_dt}"
               data-serial_no="${row.serial_no}"
               ${checked ? 'checked' : ''}
               ${reconStatus === 'CLOSED' ? 'disabled' : ''}>
    `;
}

function updateHeaderCheckbox() {
    if (!table) {
        return;
    }

    const rows = table.rows().data().toArray();
    const total = rows.length;
    const cleared = rows.filter(row => parseInt(row.is_cleared) === 1).length;

    $('#br_check_all')
        .prop('checked', total > 0 && cleared === total)
        .prop('indeterminate', cleared > 0 && cleared < total)
        .prop('disabled', reconStatus === 'CLOSED' || total === 0);
}

function applyLockedState() {
    const isClosed = reconStatus === 'CLOSED';

    $('#opening_balance, #closing_balance, #closing_dt')
        .prop('disabled', isClosed)
        .toggleClass('recon-locked', isClosed);

    $('#btnSaveHeader, #btnClose').prop('disabled', isClosed);
    $('#txnTable').find('input.br-clear').prop('disabled', isClosed);

    updateHeaderCheckbox();
}

function buildRow(transaction) {
    const isCleared = parseInt(transaction.is_cleared) === 1;

    return {
        cleared: checkboxHtml(transaction, isCleared),
        is_cleared: isCleared ? 1 : 0,
        date: String(transaction.trans_dt || '').substring(0, 10),
        type: transaction.trans_type || '',
        desc: transaction.payto_name || transaction.remarks || '',
        ref: transaction.ref_no || '',
        check: transaction.check_no || '',
        paym: transaction.trans_type === 'PAYM'
            ? money(transaction.debit_amt)
            : '',
        deps: transaction.trans_type === 'DEPS'
            ? money(transaction.credit_amt)
            : '',

        // Hidden identifiers used by the checkbox and bulk update.
        trans_no: transaction.trans_no,
        trans_dt: transaction.trans_dt,
        serial_no: transaction.serial_no
    };
}

function initializeTable() {
    if (table) {
        return;
    }

    table = $('#txnTable').DataTable({
        pageLength: 100,
        lengthMenu: [
            [100, 150, 200, 250, -1],
            [100, 150, 200, 250, 'All']
        ],
        order: [[1, 'asc']],
        autoWidth: false,
        stateSave: true,
        columns: [
            {
                data: 'cleared',
                orderable: false,
                searchable: false,
                className: 'col-cleared'
            },
            {
                data: 'date',
                className: 'col-date',
                render: function(data, type) {
                    if (type === 'display' || type === 'filter') {
                        return formatDateMDY(data);
                    }

                    return data;
                }
            },
            {
                data: 'type',
                className: 'col-type'
            },
            {
                data: 'desc'
            },
            {
                data: 'ref',
                className: 'col-ref'
            },
            {
                data: 'check',
                className: 'col-check'
            },
            {
                data: 'paym',
                className: 'num col-amount'
            },
            {
                data: 'deps',
                className: 'num col-amount'
            }
        ]
    });
}

function loadTransactions() {
    if (!reconId) {
        return;
    }

    $.get("{{ route('bankrecon.transactions') }}", {
        recon_id: reconId
    })
    .done(function(response) {
        reconStatus = response.recon.status;

        setSummary(response.summary);
        initializeTable();

        table.clear();

        response.rows.forEach(function(transaction) {
            table.row.add(buildRow(transaction));
        });

        table.draw(false);

        $('#reconDetails').removeClass('d-none');
        $('#btnReport, #btnSimplePdf, #btnSimpleExcel, #btnClose').removeClass('d-none');
        $('#btnReport').attr(
            'href',
            "{{ url('bankrecon/report') }}/" + reconId
        );

        $('#btnSimplePdf').attr(
            'href',
            "{{ url('bank-reconciliation') }}/" + reconId + "/simplified/pdf"
        );

        $('#btnSimpleExcel').attr(
            'href',
            "{{ url('bank-reconciliation') }}/" + reconId + "/simplified/excel"
        );

        $('#recon_id_txt').text(reconId);
        $('#recon_status').text(reconStatus);

        updateHeaderCheckbox();
        applyLockedState();
    })
    .fail(function(xhr) {
        showMessage(
            xhr.responseJSON?.message || 'Unable to load transactions.',
            'danger'
        );
    });
}

$(document).on('click', '#btnLoad', function() {
    hideMessage();

    const bankId = $('#bank_id').val();
    const period = $('#period').val();

    if (!bankId || !period) {
        showMessage('Select a bank and statement period.', 'warning');
        return;
    }

    const button = $(this);
    button.prop('disabled', true).text('Loading...');

    $.post("{{ route('bankrecon.loadOrCreate') }}", {
        _token: "{{ csrf_token() }}",
        bank_id: bankId,
        period: period
    })
    .done(function(response) {
        reconId = response.recon.recon_id;
        reconStatus = response.recon.status;

        $('#opening_balance').val(response.recon.opening_balance);
        $('#closing_balance').val(response.recon.closing_balance);
        $('#closing_dt').val(
            String(response.recon.closing_dt || '').substring(0, 10)
        );

        loadTransactions();

        if (response.is_new) {
            showMessage(
                'A new reconciliation month was created. Enter the statement balance and save it.',
                'info'
            );
        }
    })
    .fail(function(xhr) {
        showMessage(
            xhr.responseJSON?.message || 'Unable to load the reconciliation.',
            'danger'
        );
    })
    .always(function() {
        button.prop('disabled', false).text('Load Month');
    });
});

$(document).on('click', '#btnSaveHeader', function() {
    if (!reconId) {
        return;
    }

    hideMessage();

    const button = $(this);
    button.prop('disabled', true).text('Saving...');

    $.post("{{ route('bankrecon.saveHeader') }}", {
        _token: "{{ csrf_token() }}",
        recon_id: reconId,
        opening_balance: $('#opening_balance').val(),
        closing_balance: $('#closing_balance').val(),
        closing_dt: $('#closing_dt').val()
    })
    .done(function(response) {
        setSummary(response.summary);

        /*
         * The closing date may expose additional transactions.
         * Reload only after saving the header, not after every checkbox click.
         */
        loadTransactions();
        showMessage('Balances saved successfully.', 'success');
    })
    .fail(function(xhr) {
        showMessage(
            xhr.responseJSON?.message || 'Unable to save balances.',
            'danger'
        );
    })
    .always(function() {
        button.prop('disabled', false).text('Save Balances');
    });
});

/*
 * Check or uncheck one transaction.
 *
 * The table is NOT reloaded after this request. We only update the row data
 * and summary, so the browser remains at the same scroll position and page.
 */
$(document).on('change', '.br-clear', function() {
    if (!reconId || reconStatus === 'CLOSED') {
        return;
    }

    const checkbox = $(this);
    const checked = checkbox.is(':checked');
    const dataRow = table.row(checkbox.closest('tr'));
    const row = dataRow.data();

    checkbox.prop('disabled', true);

    $.post("{{ route('bankrecon.toggle') }}", {
        _token: "{{ csrf_token() }}",
        recon_id: reconId,
        trans_no: checkbox.data('trans_no'),
        trans_dt: checkbox.data('trans_dt'),
        serial_no: checkbox.data('serial_no'),
        checked: checked ? 1 : 0,
        opening_balance: $('#opening_balance').val(),
        closing_balance: $('#closing_balance').val(),
        closing_dt: $('#closing_dt').val()
    })
    .done(function(response) {
        row.is_cleared = checked ? 1 : 0;
        row.cleared = checkboxHtml(row, checked);

        /*
         * Update DataTables' stored row without drawing the table.
         * This preserves the current page, ordering and scroll position.
         */
        dataRow.data(row);

        setSummary(response.summary);
        updateHeaderCheckbox();
    })
    .fail(function(xhr) {
        checkbox.prop('checked', !checked);

        showMessage(
            xhr.responseJSON?.message || 'Unable to update the transaction.',
            'danger'
        );
    })
    .always(function() {
        checkbox.prop('disabled', reconStatus === 'CLOSED');
    });
});

$(document).on('change', '#br_check_all', function() {
    if (!reconId || !table || reconStatus === 'CLOSED') {
        return;
    }

    const headerCheckbox = $(this);
    const checked = headerCheckbox.is(':checked');
    const rows = table.rows().data().toArray();

    const items = rows.map(function(row) {
        return {
            trans_no: row.trans_no,
            trans_dt: row.trans_dt,
            serial_no: row.serial_no
        };
    });

    headerCheckbox.prop('disabled', true);

    $.post("{{ route('bankrecon.bulkToggle') }}", {
        _token: "{{ csrf_token() }}",
        recon_id: reconId,
        checked: checked ? 1 : 0,
        items: items,
        opening_balance: $('#opening_balance').val(),
        closing_balance: $('#closing_balance').val(),
        closing_dt: $('#closing_dt').val()
    })
    .done(function(response) {
        const page = table.page();
        const scrollTop = $(window).scrollTop();

        table.rows().every(function() {
            const row = this.data();
            row.is_cleared = checked ? 1 : 0;
            row.cleared = checkboxHtml(row, checked);
            this.data(row);
        });

        table.page(page).draw(false);
        $(window).scrollTop(scrollTop);

        setSummary(response.summary);
        updateHeaderCheckbox();
    })
    .fail(function(xhr) {
        headerCheckbox.prop('checked', !checked);

        showMessage(
            xhr.responseJSON?.message || 'Bulk update failed.',
            'danger'
        );
    })
    .always(function() {
        updateHeaderCheckbox();
    });
});

$(document).on('click', '#btnClose', function() {
    if (!reconId || reconStatus === 'CLOSED') {
        return;
    }

    hideMessage();

    if (!confirm(
        'Close this reconciliation month? No additional changes will be allowed.'
    )) {
        return;
    }

    $.post("{{ route('bankrecon.close') }}", {
        _token: "{{ csrf_token() }}",
        recon_id: reconId
    })
    .done(function(response) {
        reconStatus = response.status;
        $('#recon_status').text(reconStatus);

        setSummary(response.summary);
        applyLockedState();

        showMessage('Reconciliation month closed successfully.', 'success');
    })
    .fail(function(xhr) {
        if (xhr.responseJSON?.summary) {
            setSummary(xhr.responseJSON.summary);
        }

        showMessage(
            xhr.responseJSON?.message || 'Unable to close the reconciliation.',
            'danger'
        );
    });
});
</script>
@endsection
