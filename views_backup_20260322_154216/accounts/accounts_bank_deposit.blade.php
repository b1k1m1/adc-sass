@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <div class="container-fluid">
        <div class="col d-flex gap-2 flex-wrap align-items-end">
            <h4 class="mb-3">Bank Deposit Entry</h4>
            <div class="col-auto ms-auto mb-3">
                <a href="{{ route('accounts.dashboard') }}" class="btn btn-primary no-print">
                    {{ t('common.back','Back') }}
                </a>
            </div>
        </div>
    <form method="POST" action="{{ route('store.deposit') }}">
        @csrf

        <div class="card mb-3 p-3">
            <div class="row mb-2">
                <div class="col-md-3">
                    <label for="bank_id" class="form-label">Bank Account</label>
                    <select name="account_id" id="account_id" class="form-select">
                         <option value="">Select Bank</option>
                        @foreach($fashbanks as $bank)
                            <option value="{{ $bank->account_id }}"
                                {{ old('account_id', '1101') == $bank->account_id ? 'selected' : '' }}>
                                {{ $bank->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="trans_no" class="form-label">Voucher #</label>
                    <input type="text" name="trans_no" id="trans_no" class="form-control" value="{{ $nextId }}">
                </div>
                <div class="col-md-2">
                    <label for="trans_dt" class="form-label">Transaction Date</label>
                    <input type="date" name="trans_dt" id="trans_dt" class="form-control" value="{{ old('trans_dt', date('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
                    <label for="trans_flag" class="form-label">Status</label>
                    <select name="trans_flag" id="trans_flag" class="form-select">
                        <option value="A">Active</option>
                        <option value="I">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="post_flag" class="form-label">Posted</label>
                    <select name="post_flag" id="post_flag" class="form-select">
                        <option value="U">Unposted</option>
                        <option value="P">Posted</option>
                    </select>
                </div>
                <div class="row mt-3">
                    <div class="col-md-2 mb-3">
                        <label class="form-label d-block">Account Type</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="account_flag" id="vendor" value="V">
                                <label class="form-check-label" for="vendor">Vendor</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="account_flag" id="expense" value="E">
                                <label class="form-check-label" for="expense">Expense</label>
                            </div>
                             <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="account_flag" id="customer" value="C">
                                <label class="form-check-label" for="vendor">Customer</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="account_flag" id="income" value="I">
                                <label class="form-check-label" for="expense">Income</label>
                            </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="party_cd" class="form-label">Income/Customer Account</label>
                            <select name="party_cd" id="party_cd" class="form-select">
                               <option value="">Select Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->party_cd }}" data-name="{{ $account->party_nm }}">{{ $account->party_nm }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3">
                    <label for="payto_name" class="form-label">Received From</label>
                    <input type="text" name="payto_name" id="payto_name" class="form-control" value="{{ old('payto_name') }}">
                </div>
                <div class="col-md-2">
                    <label for="check_no" class="form-label">Check #</label>
                    <input type="text" name="check_no" id="check_no" class="form-control" value="{{ old('check_no') }}">
                </div>
                <div class="col-md-2">
                    <label for="check_dt" class="form-label">Check Date</label>
                    <input type="date" name="check_dt" id="check_dt" class="form-control" value="{{ old('check_dt', date('Y-m-d')) }}">
                </div>
                 <div class="col-md-2">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="text" name="amount" id="amount" class="form-control" required value="{{ old('amount') }}">
                </div>

            </div>
            <div class="row mb-2">
                <div class="col-md-7">
                    <label for="remarks" class="form-label">Remarks</label>
                    <input type="text" name="remarks" id="remarks" class="form-control" value="{{ old('remarks') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label d-block">Clear</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="clear_flag" id="clear_yes" value="Y">
                        <label class="form-check-label" for="clear_yes">Yes</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="clear_flag" id="clear_no" value="N" checked>
                        <label class="form-check-label" for="clear_no">No</label>
                    </div>
                </div>


            </div>
            <div class="row mb-2">
                <div class="col-md-3">
                    <label for="ref_no" class="form-label">Ref No</label>
                    <input type="text" name="ref_no" id="ref_no" class="form-control" value="{{ old('ref_no') }}">
                </div>
            </div>
        </div>

        <!-- Hidden fixed fields -->
        <input type="hidden" name="trans_type" value="DEPS">
        <input type="hidden" name="company_id" value="DS03">

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary me-2">Save</button>
            <a href="{{ route('enter.deposit') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function(){

  $('#amount').mask('000,000,000.00', {reverse: true});

  $('#party_cd').on('change', function() {
      var accountName = $(this).find('option:selected').data('name') || '';
      $('#payto_name').val(accountName);

    });

});
</script>
@endsection

