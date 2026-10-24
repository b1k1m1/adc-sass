@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <div class="container-fluid">
        <div class="col d-flex gap-2 flex-wrap align-items-end">
            <h4 class="mb-3">Bank Payment Entry</h4>
            <div class="col-auto ms-auto mb-3">
                <a href="{{ route('accounts.dashboard') }}" class="btn btn-primary no-print">
                    {{ t('common.back','Back') }}
                </a>
            </div>
        </div>
    <form method="POST" action="{{ route('store.payment') }}">
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
                    <input type="text"
                        name="trans_dt"
                        id="trans_dt"
                        class="form-control flatpickr-date"
                        value="{{ old('trans_dt', date('m/d/Y')) }}"
                        placeholder="MM/DD/YYYY"
                        autocomplete="off">

                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" id="reuse_trans_dt" checked>
                        <label class="form-check-label small" for="reuse_trans_dt">
                            Reuse
                        </label>
                    </div>
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
                            <label for="party_cd" class="form-label">Expense/Vendor Account</label>
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
                    <label for="payto_name" class="form-label">Pay To</label>
                    <input type="text" name="payto_name" id="payto_name" class="form-control" value="{{ old('payto_name') }}">
                </div>
                <div class="col-md-2">
                    <label for="check_no" class="form-label">Check #</label>
                    <input type="text" name="check_no" id="check_no" class="form-control" value="{{ old('check_no') }}">
                </div>
                <div class="col-md-2">
                    <label for="check_dt" class="form-label">Check Date</label>
                    <input type="text"
                        name="check_dt"
                        id="check_dt"
                        class="form-control flatpickr-date"
                        value="{{ old('check_dt', date('m/d/Y')) }}"
                        placeholder="MM/DD/YYYY"
                        autocomplete="off">

                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" id="reuse_check_dt" checked>
                        <label class="form-check-label small" for="reuse_check_dt">
                            Reuse
                        </label>
                    </div>
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
        <input type="hidden" name="trans_type" value="PAYM">
        <input type="hidden" name="company_id" value="DS03">
        <!--
        <input type="hidden" name="user_cd" value="{{ auth()->user()->id ?? '' }}">
        -->
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary me-2">{{ t('common.save', 'Save') }}</button>
            <a href="{{ route('enter.payment') }}" class="btn btn-secondary">{{ t('common.cancel', 'Cancel') }}</a>
        </div>
    </form>

    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function(){

    /*
    |--------------------------------------------------------------------------
    | Amount Mask
    |--------------------------------------------------------------------------
    */
    $('#amount').mask('000,000,000.00', {reverse: true});


    /*
    |--------------------------------------------------------------------------
    | Account Name Auto Fill
    |--------------------------------------------------------------------------
    */
    $('#party_cd').on('change', function() {
        var accountName = $(this).find('option:selected').data('name') || '';
        $('#payto_name').val(accountName);
    });


    /*
    |--------------------------------------------------------------------------
    | Flatpickr Dates
    |--------------------------------------------------------------------------
    | Allows:
    | - manual typing
    | - calendar dropdown selection
    | - copy/paste
    | - MM/DD/YYYY display
    |--------------------------------------------------------------------------
    */
    flatpickr("#trans_dt", {
        dateFormat: "m/d/Y",
        allowInput: true,
        clickOpens: true
    });

    flatpickr("#check_dt", {
        dateFormat: "m/d/Y",
        allowInput: true,
        clickOpens: true
    });


    /*
    |--------------------------------------------------------------------------
    | Reuse Transaction Date and Check Date
    |--------------------------------------------------------------------------
    | Very useful for bi-weekly payroll entry.
    | User enters the date once and system keeps it for next payment entries.
    |--------------------------------------------------------------------------
    */
    const transDateInput = $('#trans_dt');
    const checkDateInput = $('#check_dt');

    const reuseTransDate = $('#reuse_trans_dt');
    const reuseCheckDate = $('#reuse_check_dt');

    const savedTransDate = localStorage.getItem('payment_trans_dt');
    const savedCheckDate = localStorage.getItem('payment_check_dt');

    /*
    |--------------------------------------------------------------------------
    | Load Saved Dates
    |--------------------------------------------------------------------------
    */
    if (savedTransDate && transDateInput.length) {
        transDateInput.val(savedTransDate);
    }

    if (savedCheckDate && checkDateInput.length) {
        checkDateInput.val(savedCheckDate);
    }


    /*
    |--------------------------------------------------------------------------
    | Save Transaction Date When Changed
    |--------------------------------------------------------------------------
    */
    transDateInput.on('change blur input', function () {
        let selectedDate = $(this).val();

        if (reuseTransDate.is(':checked') && selectedDate) {
            localStorage.setItem('payment_trans_dt', selectedDate);
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Save Check Date When Changed
    |--------------------------------------------------------------------------
    */
    checkDateInput.on('change blur input', function () {
        let selectedDate = $(this).val();

        if (reuseCheckDate.is(':checked') && selectedDate) {
            localStorage.setItem('payment_check_dt', selectedDate);
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Reuse Transaction Date Checkbox
    |--------------------------------------------------------------------------
    */
    reuseTransDate.on('change', function () {
        if ($(this).is(':checked')) {
            let selectedDate = transDateInput.val();

            if (selectedDate) {
                localStorage.setItem('payment_trans_dt', selectedDate);
            }
        } else {
            localStorage.removeItem('payment_trans_dt');
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Reuse Check Date Checkbox
    |--------------------------------------------------------------------------
    */
    reuseCheckDate.on('change', function () {
        if ($(this).is(':checked')) {
            let selectedDate = checkDateInput.val();

            if (selectedDate) {
                localStorage.setItem('payment_check_dt', selectedDate);
            }
        } else {
            localStorage.removeItem('payment_check_dt');
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Before Submit, Store Current Dates
    |--------------------------------------------------------------------------
    | This is important because your form is normal POST, not AJAX.
    | After save and redirect back to entry screen, dates will load again.
    |--------------------------------------------------------------------------
    */
    $('form').on('submit', function () {
        let currentTransDate = transDateInput.val();
        let currentCheckDate = checkDateInput.val();

        if (reuseTransDate.is(':checked') && currentTransDate) {
            localStorage.setItem('payment_trans_dt', currentTransDate);
        }

        if (reuseCheckDate.is(':checked') && currentCheckDate) {
            localStorage.setItem('payment_check_dt', currentCheckDate);
        }
    });

});
</script>
@endsection

