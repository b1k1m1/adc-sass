@extends('admin.admin_dashboard')
@section('title', $mode === 'edit' ? 'Edit Payment' : 'Add Payment')
@section('admin')

@php
  $isEdit = $mode === 'edit';
  $action = $isEdit
    ? route('shift.payments.update', $row->trans_no)
    : route('shift.payments.store');

  $transDt = $isEdit && $row->trans_dt ? \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') : old('trans_dt');
@endphp

<div class="page-content">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-2">
      <h4 class="mb-0">{{ $isEdit ? 'Edit Payment' : 'Add Payment' }}</h4>
      <a href="{{ route('shift.payments.index') }}" class="btn btn-outline-secondary">{{ t('common.back','Back') }}</a>
    </div>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ $action }}">
          @csrf

          <div class="row g-3" style="max-width: 820px;">

            <div class="col-md-4">
              <label class="form-label">Trans Dt</label>
              <input type="text" name="trans_dt" class="form-control" placeholder="mm/dd/yyyy"
                     value="{{ $transDt }}" required>
              @error('trans_dt') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-8">
              <label class="form-label">Party (Code - Name)</label>
              <select name="party_cd" id="party_cd"  class="form-select js-accountmaster" style="width:100%" required>
                    @if($isEdit && !empty($row))
                        {{-- preload selected value for edit --}}
                        <option value="{{ $row->party_cd }}" selected>
                            {{ $row->party_cd }} - {{ $row->payto_name }}
                        </option>
                    @endif
                </select>
              @error('party_cd') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-8">
                <label class="form-label">Pay To Name</label>
                <input type="text" id="payto_name" name="payto_name" class="form-control" value="{{ old('payto_name', $isEdit ? $row->payto_name : '') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">Paid Amt</label>
              <input type="number" step="0.01" min="0" name="debit_amt" id="debit_amt"
                     class="form-control" value="{{ old('debit_amt', $isEdit ? $row->debit_amt : '') }}" required>
              @error('debit_amt') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Payment Type</label>
              <select name="pay_type" class="form-select" required>
                <option value="">-- Select --</option>
                @foreach($payTypes as $k => $v)
                  <option value="{{ $k }}"
                    {{ old('pay_type', $isEdit ? $row->pay_type : '') == $k ? 'selected' : '' }}>
                    {{ $k }} - {{ $v }}
                  </option>
                @endforeach
              </select>
              @error('pay_type') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Check / Card No</label>
              <input type="number" name="check_no" class="form-control"
                     value="{{ old('check_no', $isEdit ? $row->check_no : '') }}">
              @error('check_no') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Deductable Percentage</label>
              <input type="number" step="0.01" min="0" max="100" name="per" id="per"
                     class="form-control" value="{{ old('per', $isEdit ? $row->per : 0) }}">
              @error('per') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label">Net Payable</label>
              <input type="number" step="0.01" min="0" name="net_debit" id="net_debit"
                     class="form-control" value="{{ old('net_debit', $isEdit ? $row->net_debit : '') }}">
              <div class="form-text">Auto-calculated, but editable.</div>
              @error('net_debit') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12">
              <label class="form-label">Remarks</label>
              <input type="text" name="remarks" class="form-control" maxlength="30"
                     value="{{ old('remarks', $isEdit ? $row->remarks : '') }}">
              @error('remarks') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <button class="btn btn-primary">{{ $isEdit ? 'Update' : 'Save' }}</button>
              <a href="{{ route('shift.payments.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>

          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<script>
(function(){
  const debit = document.getElementById('debit_amt');
  const per = document.getElementById('per');
  const net = document.getElementById('net_debit');

  function toNum(v){ const n = parseFloat(v); return isNaN(n) ? 0 : n; }

  function calc(){
    const amt = toNum(debit.value);
    const p = toNum(per.value);
    const result = amt - (amt * p / 100);
    net.value = (Math.round(result * 100) / 100).toFixed(2);
  }

  debit.addEventListener('input', calc);
  per.addEventListener('input', calc);

  if (!net.value) calc();
})();

document.addEventListener('DOMContentLoaded', function () {

  // When user selects an account from Select2
  $('#party_cd').on('select2:select', function (e) {
    const data = e.params.data;
    document.getElementById('payto_name').value = data.party_nm || '';
  });

  // When cleared
  $('#party_cd').on('select2:clear', function () {
    document.getElementById('payto_name').value = '';
  });

});

</script>

@endsection
