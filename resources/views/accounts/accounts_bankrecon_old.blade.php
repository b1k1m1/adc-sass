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

<button id="btnClose" class="btn btn-outline-danger d-none">
    Close Month
</button>

<a href="{{ url()->previous() }}" class="btn btn-primary waves-effect waves-light">
    {{ t('common.back','Back') }}
</a>

@endsection

<div class="page-content">
    <div class="container-fluid">

        <style>
        .br-title { font-weight: 700; font-size: 18px; }
        .br-sub { font-size: 13px; color:#6c757d; }
        .num { text-align:right; white-space:nowrap; }
        .nowrap { white-space:nowrap; }
        .summary-box { background:#f8f9fa; border:1px solid #e9ecef; border-radius:10px; padding:12px; }
        .diff-ok { font-weight:700; }
        .diff-bad { font-weight:700; color:#dc3545; }
        </style>

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <!-- LEFT SIDE -->
                        <div>
                            <h4 class="mb-0 font-size-18">Bank Reconciliation</h4>

                            <div class="text-muted small mt-1">
                                Select a bank and statement period, then check cleared transactions.
                            </div>
                        </div>
                        <!-- RIGHT SIDE TOOLBAR -->
                        <div class="page-title-right">
                            @yield('page-toolbar')
                        </div>
                    </div>
                </div>
             </div>
            <!-- end page title -->


            <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Bank</label>
                    <select id="bank_id" class="form-select">
                    <option value="">-- Select Bank --</option>
                    @foreach($banks as $b)
                        <option value="{{ $b->account_id }}">{{ $b->account_name }} ({{ $b->account_id }})</option>
                    @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Statement Period</label>
                    <input id="period" type="month" class="form-control" value="{{ now()->format('Y-m') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Opening Balance</label>
                    <input id="opening_balance" type="number" step="0.01" class="form-control num" value="0.00">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Opening Date</label>
                    <input id="opening_dt" type="date" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                </div>

                <div class="col-md-2">
                    <button id="btnLoad" class="btn btn-primary w-100">Load</button>
                </div>
                </div>

                <hr>

                <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Ending Balance</label>
                    <input id="closing_balance" type="number" step="0.01" class="form-control num" value="0.00">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Ending Date</label>
                    <input id="closing_dt" type="date" class="form-control" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                </div>

                <div class="col-md-3">
                    <div class="summary-box">
                    <div class="d-flex justify-content-between"><div>Cleared Deposits:</div><div class="num" id="sum_deps">0.00</div></div>
                    <div class="d-flex justify-content-between"><div>Cleared Payments:</div><div class="num" id="sum_paym">0.00</div></div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between"><div>Cleared Balance:</div><div class="num" id="sum_cleared">0.00</div></div>
                    <div class="d-flex justify-content-between">
                        <div>Difference:</div>
                        <div class="num diff-bad" id="sum_diff">0.00</div>
                    </div>
                    </div>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button id="btnSaveHeader" class="btn btn-outline-success w-100 d-none">{{ t('common.save', 'Save') }}</button>
                    <button id="btnRefresh" class="btn btn-outline-primary w-100 d-none">Refresh</button>
                </div>

                <div class="col-md-2">
                    <div class="small text-muted">Recon ID: <span id="recon_id_txt">-</span></div>
                    <div class="small text-muted">Status: <span id="recon_status">-</span></div>
                </div>
                </div>

            </div>
            </div>

            <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                <table id="txnTable" class="table table-sm table-striped align-middle">
                    <thead>
                    <tr>
                        <th class="nowrap">
                            <input type="checkbox" id="br_check_all" class="form-check-input me-1">
                            Cleared
                        </th>
                        <th class="nowrap">Date</th>
                        <th class="nowrap">Type</th>
                        <th>Description</th>
                        <th class="nowrap">Ref/Check</th>
                        <th class="num nowrap">Payment</th>
                        <th class="num nowrap">Deposit</th>
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
</div>
@endsection

@section('scripts')
<script>
let reconId = null;
let table = null;

function money(x){
  let n = parseFloat(x || 0);
  return n.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
}
function setSummary(s){
  $('#sum_deps').text(money(s.cleared_deps));
  $('#sum_paym').text(money(s.cleared_paym));
  $('#sum_cleared').text(money(s.cleared_balance));
  $('#sum_diff').text(money(s.difference));
  $('#sum_diff').toggleClass('diff-ok', Math.abs(parseFloat(s.difference)) < 0.005);
  $('#sum_diff').toggleClass('diff-bad', Math.abs(parseFloat(s.difference)) >= 0.005);
}

function loadTransactions(){
  if(!reconId) return;

  $.get("{{ route('bankrecon.transactions') }}", {recon_id: reconId})
   .done(res => {
      setSummary(res.summary);

      if(!table){
        table = $('#txnTable').DataTable({
          pageLength: 50,
          order: [[1,'asc']],
          columns: [
            {data:'cleared', orderable:false},
            {data:'date'},
            {data:'type'},
            {data:'desc'},
            {data:'ref'},
            {data:'paym', className:'num'},
            {data:'deps', className:'num'},
          ]
        });
      }

      table.clear();

      res.rows.forEach(r => {
        const checked = parseInt(r.is_cleared) === 1 ? 'checked' : '';
        const cb = `
          <input type="checkbox" class="form-check-input br-clear"
            data-trans_no="${r.trans_no}"
            data-trans_dt="${r.trans_dt}"
            data-serial_no="${r.serial_no}"
            ${checked}>
        `;

        table.row.add({
          cleared: cb,
          date: (r.trans_dt || '').substring(0,10),
          type: r.trans_type,
          desc: (r.payto_name || r.remarks || ''),
          ref: (r.ref_no || '') + (r.check_no ? (' / ' + r.check_no) : ''),
          paym: r.trans_type === 'PAYM' ? money(r.debit_amt) : '',
          deps: r.trans_type === 'DEPS' ? money(r.credit_amt) : '',

          // hidden identifiers for bulk ops
            trans_no: r.trans_no,
            trans_dt: r.trans_dt,
            serial_no: r.serial_no
        });
      });

      table.draw();

        /* ===== ADD THIS BLOCK RIGHT HERE ===== */
    const total   = res.rows.length;
    const cleared = res.rows.filter(r => parseInt(r.is_cleared) === 1).length;

    $('#br_check_all').prop('checked', total > 0 && cleared === total);
    $('#br_check_all').prop('indeterminate', cleared > 0 && cleared < total);
    /* ===================================== */


      $('#btnSaveHeader, #btnRefresh, #btnReport, #btnClose').removeClass('d-none');
      $('#btnReport').attr('href', "{{ url('bankrecon/report') }}/" + reconId);

      $('#recon_id_txt').text(reconId);
      $('#recon_status').text(res.recon.status);

      const isClosed = (res.recon.status === 'CLOSED');
      $('#btnClose').prop('disabled', isClosed);
      $('#btnSaveHeader').prop('disabled', isClosed);
      $('#txnTable').find('input.br-clear').prop('disabled', isClosed);
   });
}

$(document).on('click', '#btnLoad', function(){
  const bank_id = $('#bank_id').val();
  const period  = $('#period').val();
  if(!bank_id || !period){ alert('Select bank and period.'); return; }

  $.post("{{ route('bankrecon.loadOrCreate') }}", {
    _token: "{{ csrf_token() }}",
    bank_id,
    period,
    opening_balance: $('#opening_balance').val(),
    opening_dt: $('#opening_dt').val(),
    closing_balance: $('#closing_balance').val(),
    closing_dt: $('#closing_dt').val(),
  }).done(res => {
    reconId = res.recon.recon_id;

    // fill header from recon
    $('#opening_balance').val(res.recon.opening_balance);
    $('#opening_dt').val((res.recon.opening_dt || '').substring(0,10));
    $('#closing_balance').val(res.recon.closing_balance);
    $('#closing_dt').val((res.recon.closing_dt || '').substring(0,10));

    loadTransactions();
  });
});

$(document).on('click', '#btnRefresh', function(){
  loadTransactions();
});

$(document).on('click', '#btnSaveHeader', function(){
  if(!reconId) return;

  $.post("{{ route('bankrecon.saveHeader') }}", {
    _token: "{{ csrf_token() }}",
    recon_id: reconId,
    opening_balance: $('#opening_balance').val(),
    opening_dt: $('#opening_dt').val(),
    closing_balance: $('#closing_balance').val(),
    closing_dt: $('#closing_dt').val(),
  }).done(() => loadTransactions());
});

// checkbox toggle
$(document).on('change', '.br-clear', function(){
  if(!reconId) return;

  const checked = $(this).is(':checked');
  const payload = {
    _token: "{{ csrf_token() }}",
    recon_id: reconId,
    trans_no: $(this).data('trans_no'),
    trans_dt: $(this).data('trans_dt'),
    serial_no: $(this).data('serial_no'),
    checked: checked ? 1 : 0
  };

  $.post("{{ route('bankrecon.toggle') }}", payload)
   .done(() => loadTransactions())
   .fail(xhr => {
     alert(xhr.responseJSON?.message || 'Error');
     $(this).prop('checked', !checked);
   });
});

$(document).on('click', '#btnClose', function(){
  if(!reconId) return;
  if(!confirm('Close this month reconciliation? No more changes allowed.')) return;

  $.post("{{ route('bankrecon.close') }}", {
    _token: "{{ csrf_token() }}",
    recon_id: reconId
  }).done(() => loadTransactions());
});

$(document).on('change', '#br_check_all', function(){
  if(!reconId) return;

  const checked = $(this).is(':checked');

  // collect all rows currently loaded in DataTable (all pages)
  const items = [];
  table.rows().every(function(){
    const d = this.data();
    items.push({
      trans_no: d.trans_no,
      trans_dt: d.trans_dt,
      serial_no: d.serial_no
    });
  });

  $.post("{{ route('bankrecon.bulkToggle') }}", {
    _token: "{{ csrf_token() }}",
    recon_id: reconId,
    checked: checked ? 1 : 0,
    items: items
  }).done(() => loadTransactions())
    .fail(xhr => {
      console.log(xhr.responseText);
      alert(xhr.responseJSON?.message || 'Bulk update failed');
    });
});

</script>

@endsection
