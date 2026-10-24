@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Edit Bank Deposit </h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('list.deposit') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

    <form method="POST" action="{{ route('update.deposit') }}">
        @csrf

        <div class="card mb-3 p-3">
            <div class="row mb-2">
                <div class="col-md-3">
                    <label for="bank_id" class="form-label">Bank Account</label>
                    <select name="bank_id" id="bank_id" class="form-select">
                        <option value="">Select Bank</option>
                       <option value="">Select Bank</option>
                        @foreach($fashbanks as $bank)
                            <option value="{{ $bank->account_id }}"
                                {{ old('bank_id', $fashdeposit->bank_id ?? '') == $bank->account_id ? 'selected' : '' }}>
                                {{ $bank->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="trans_no" class="form-label">Voucher #</label>
                    <input type="text" name="trans_no" id="trans_no" class="form-control" value="{{ $fashdeposit->trans_no }}">
                </div>
                <div class="col-md-2">
                    <label for="trans_dt" class="form-label">Transaction Date</label>
                    <input type="text" name="trans_dt" id="trans_dt" class="form-control flatpickr"
                    value="{{ $fashdeposit->trans_dt ? \Carbon\Carbon::parse($fashdeposit->trans_dt)->format('m/d/Y') : '' }}">
                </div>
                <div class="col-md-2">
                    <label for="trans_flag" class="form-label">Status</label>
                    <select name="trans_flag" id="trans_flag" class="form-select">
                        <option value="" {{ old('trans_flag', trim($fashdeposit->trans_flag)) === '' ? 'selected' : '' }}> --- Select --- </option>
                        <option value="A" {{ old('trans_flag', trim($fashdeposit->trans_flag)) === 'A' ? 'selected' : '' }}>Active</option>
                        <option value="I" {{ old('trans_flag', trim($fashdeposit->trans_flag)) === 'I' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="post_flag" class="form-label">Posted</label>
                    <select name="post_flag" id="post_flag" class="form-select">
                        <option value="" {{ old('post_flag', trim($fashdeposit->post_flag)) === '' ? 'selected' : '' }}> --- Select --- </option>
                        <option value="U" {{ old('post_flag', trim($fashdeposit->post_flag)) === 'U' ? 'selected' : '' }}>Unposted</option>
                        <option value="P" {{ old('post_flag', trim($fashdeposit->post_flag)) === 'P' ? 'selected' : '' }}>Posted</option>
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
                                    <option value="{{ $account->party_cd }}" data-name="{{ $account->party_nm }}"
                                        {{ old('party_cd', $fashdeposit->party_cd ?? '') == $account->party_cd ? 'selected' : '' }} >
                                        {{ $account->party_nm }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3">
                    <label for="payto_name" class="form-label">Received From</label>
                    <input type="text" name="payto_name" id="payto_name" class="form-control"
                     value="{{ $fashdeposit->payto_name }}">
                </div>
                <div class="col-md-2">
                    <label for="check_no" class="form-label">Check #</label>
                    <input type="text" name="check_no" id="check_no" class="form-control"
                     value="{{$fashdeposit->check_no }}">
                </div>
                <div class="col-md-2">
                    <label for="check_dt" class="form-label">Check Date</label>
                    <input type="text" name="check_dt" id="check_dt" class="form-control flatpickr"
                     value="{{ $fashdeposit->check_dt ? \Carbon\Carbon::parse($fashdeposit->check_dt)->format('m/d/Y') : ''}}">
                </div>
                 <div class="col-md-2">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="text" name="amount" id="amount" class="form-control" required
                    value="{{ $fashdeposit->credit_amt }}">
                </div>

            </div>
            <div class="row mb-2">
                <div class="col-md-7">
                    <label for="remarks" class="form-label">Remarks</label>
                    <input type="text" name="remarks" id="remarks" class="form-control"
                     value="{{ $fashdeposit->remarks }}">
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
                    <input type="text" name="ref_no" id="ref_no" class="form-control"
                     value="{{ $fashdeposit->ref_no }}">
                </div>
            </div>
        </div>

        <!-- Hidden fixed fields -->
        <input type="hidden" name="trans_type" value="DEPS">
        <input type="hidden" name="company_id" value="DS03">

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary me-2">{{ t('common.save', 'Save') }}</button>
            <a href="{{ route('list.deposit') }}" class="btn btn-secondary">{{ t('common.cancel', 'Cancel') }}</a>
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

document.addEventListener('DOMContentLoaded', function() {
        // Initialize single date picker
        flatpickr(".flatpickr", {
            dateFormat: "m/d/Y",
            allowInput: true,

        });

        // Initialize range picker
        flatpickr(".flatpickr-range", {
            mode: "range",
            dateFormat: "m/d/Y"
        });
    });

</script>
@endsection

