@extends('admin.admin_dashboard')
@section('title', $mode === 'edit' ? 'Edit Deposit' : 'Add Deposit')
@section('admin')

@php
  $isEdit = $mode === 'edit';
  $action = $isEdit
    ? route('shift.deposits.update', $row->trans_no)
    : route('shift.deposits.store');

  $transDt = $isEdit && $row->trans_dt ? \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') : old('trans_dt');
@endphp

<div class="page-content">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-2">
      <h4 class="mb-0">{{ $isEdit ? 'Edit Deposit' : 'Add Deposit' }}</h4>
      <a href="{{ route('shift.deposits.index') }}" class="btn btn-outline-secondary">{{ t('common.back','Back') }}</a>
    </div>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ $action }}">
          @csrf

          <div class="row g-3" style="max-width: 720px;">

            <div class="col-md-4">
              <label class="form-label">Deposit Date</label>
              <input type="text" name="trans_dt" class="form-control" placeholder="mm/dd/yyyy"
                     value="{{ $transDt }}" required>
              @error('trans_dt') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-8">
              <label class="form-label">From Name</label>
              <select name="payto_name" class="form-select" required>
                <option value="">-- Select --</option>
                @foreach($fromNames as $nm)
                  <option value="{{ $nm }}"
                    {{ old('payto_name', $isEdit ? $row->payto_name : '') == $nm ? 'selected' : '' }}>
                    {{ $nm }}
                  </option>
                @endforeach
              </select>
              @error('payto_name') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Total Deposit</label>
              <input type="number" step="0.01" min="0" name="credit_amt" id="credit_amt"
                     class="form-control" value="{{ old('credit_amt', $isEdit ? $row->credit_amt : '') }}" required>
              @error('credit_amt') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Deduct Percentage</label>
              <input type="number" step="0.01" min="0" max="100" name="per" id="per"
                     class="form-control" value="{{ old('per', $isEdit ? $row->per : 0) }}">
              @error('per') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Net Deposit</label>
              <input type="number" step="0.01" min="0" name="net_credit" id="net_credit"
                     class="form-control" value="{{ old('net_credit', $isEdit ? $row->net_credit : '') }}">
              <div class="form-text">Auto-calculated, but editable.</div>
              @error('net_credit') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12">
              <label class="form-label">Remarks</label>
              <input type="text" name="remarks" class="form-control" maxlength="30"
                     value="{{ old('remarks', $isEdit ? $row->remarks : '') }}">
              @error('remarks') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <button class="btn btn-primary">{{ $isEdit ? 'Update' : 'Save' }}</button>
              <a href="{{ route('shift.deposits.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>

          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<script>
(function(){
  const credit = document.getElementById('credit_amt');
  const per = document.getElementById('per');
  const net = document.getElementById('net_credit');

  function toNum(v){ const n = parseFloat(v); return isNaN(n) ? 0 : n; }

  function calc(){
    const amt = toNum(credit.value);
    const p = toNum(per.value);
    const result = amt - (amt * p / 100);
    // only auto-fill if empty OR user never typed it
    net.value = (Math.round(result * 100) / 100).toFixed(2);
  }

  credit.addEventListener('input', calc);
  per.addEventListener('input', calc);

  // initial calc if net empty
  if (!net.value) calc();
})();
</script>

@endsection
