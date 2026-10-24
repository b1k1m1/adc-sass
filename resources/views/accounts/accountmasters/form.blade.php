@php
    $isEdit = isset($account);

    $selectedPartyType = old(
        'party_type',
        $isEdit ? $account->party_type : ''
    );

    $selectedStatus = old(
        'trans_flag',
        $isEdit ? $account->trans_flag : 'A'
    );

    $selectedGlId = old(
        'gl_id',
        $isEdit ? $account->gl_id : ''
    );

    $selected1099 = old(
        'yn_1099',
        $isEdit ? ($account->yn_1099 ?: 'N') : 'N'
    );
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Please correct the following errors:</strong>

        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">

    <div class="col-lg-8">

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Account Information</h5>
            </div>

            <div class="card-body">

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">
                        Party ID
                    </label>

                    <div class="col-md-4">
                        <input type="text"
                               class="form-control bg-light"
                               value="{{ old('party_cd', $isEdit ? $account->party_cd : $partyCd) }}"
                               readonly>
                    </div>

                    <label class="col-md-2 col-form-label">
                        Status <span class="text-danger">*</span>
                    </label>

                    <div class="col-md-3">
                        <select name="trans_flag"
                                class="form-select @error('trans_flag') is-invalid @enderror"
                                required>

                            <option value="A" {{ $selectedStatus === 'A' ? 'selected' : '' }}>
                                A - Active
                            </option>

                            <option value="H" {{ $selectedStatus === 'H' ? 'selected' : '' }}>
                                H - Hold
                            </option>

                            <option value="D" {{ $selectedStatus === 'D' ? 'selected' : '' }}>
                                D - Delete
                            </option>
                        </select>

                        @error('trans_flag')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">
                        Name <span class="text-danger">*</span>
                    </label>

                    <div class="col-md-9">
                        <input type="text"
                               name="party_nm"
                               maxlength="45"
                               value="{{ old('party_nm', $isEdit ? $account->party_nm : '') }}"
                               class="form-control @error('party_nm') is-invalid @enderror"
                               required>

                        @error('party_nm')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">
                        Contact Name
                    </label>

                    <div class="col-md-9">
                        <input type="text"
                               name="party_contact"
                               maxlength="35"
                               value="{{ old('party_contact', $isEdit ? $account->party_contact : '') }}"
                               class="form-control @error('party_contact') is-invalid @enderror">

                        @error('party_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">
                        Address
                    </label>

                    <div class="col-md-9">
                        <input type="text"
                               name="party_add1"
                               maxlength="35"
                               value="{{ old('party_add1', $isEdit ? $account->party_add1 : '') }}"
                               class="form-control @error('party_add1') is-invalid @enderror">

                        @error('party_add1')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">
                        Address 2
                    </label>

                    <div class="col-md-9">
                        <input type="text"
                               name="party_add2"
                               maxlength="35"
                               value="{{ old('party_add2', $isEdit ? $account->party_add2 : '') }}"
                               class="form-control @error('party_add2') is-invalid @enderror">

                        @error('party_add2')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">
                        City
                    </label>

                    <div class="col-md-4">
                        <input type="text"
                               name="party_city"
                               maxlength="25"
                               value="{{ old('party_city', $isEdit ? $account->party_city : '') }}"
                               class="form-control @error('party_city') is-invalid @enderror">

                        @error('party_city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <label class="col-md-1 col-form-label">
                        State
                    </label>

                    <div class="col-md-1">
                        <input type="text"
                               name="party_state"
                               maxlength="2"
                               value="{{ old('party_state', $isEdit ? $account->party_state : '') }}"
                               class="form-control text-uppercase @error('party_state') is-invalid @enderror">

                        @error('party_state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <label class="col-md-1 col-form-label">
                        Zip
                    </label>

                    <div class="col-md-2">
                        <input type="text"
                               name="party_zip"
                               maxlength="10"
                               value="{{ old('party_zip', $isEdit ? $account->party_zip : '') }}"
                               class="form-control @error('party_zip') is-invalid @enderror">

                        @error('party_zip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">
                        Phone #
                    </label>

                    <div class="col-md-4">
                        <input type="text"
                               name="party_phone1"
                               maxlength="14"
                               value="{{ old('party_phone1', $isEdit ? $account->party_phone1 : '') }}"
                               class="form-control phone-input @error('party_phone1') is-invalid @enderror">

                        @error('party_phone1')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <label class="col-md-2 col-form-label">
                        Extension
                    </label>

                    <div class="col-md-3">
                        <input type="text"
                               name="party_phone2"
                               maxlength="10"
                               value="{{ old('party_phone2', $isEdit ? $account->party_phone2 : '') }}"
                               class="form-control @error('party_phone2') is-invalid @enderror">

                        @error('party_phone2')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">
                        Fax #
                    </label>

                    <div class="col-md-4">
                        <input type="text"
                               name="party_fax"
                               maxlength="14"
                               value="{{ old('party_fax', $isEdit ? $account->party_fax : '') }}"
                               class="form-control phone-input @error('party_fax') is-invalid @enderror">

                        @error('party_fax')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <label class="col-md-2 col-form-label">
                        Cell #
                    </label>

                    <div class="col-md-3">
                        <input type="text"
                               name="party_cell"
                               maxlength="14"
                               value="{{ old('party_cell', $isEdit ? $account->party_cell : '') }}"
                               class="form-control phone-input @error('party_cell') is-invalid @enderror">

                        @error('party_cell')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">
                        Email
                    </label>

                    <div class="col-md-9">
                        <input type="email"
                               name="party_email"
                               maxlength="35"
                               value="{{ old('party_email', $isEdit ? $account->party_email : '') }}"
                               class="form-control @error('party_email') is-invalid @enderror">

                        @error('party_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">
                        Account Type <span class="text-danger">*</span>
                    </label>

                    <div class="col-md-4">
                        <select name="party_type"
                                class="form-select @error('party_type') is-invalid @enderror"
                                required>

                            <option value="">Select Account Type</option>

                            {{-- Modify these values according to the legacy system --}}
                            <option value="E" {{ $selectedPartyType === 'E' ? 'selected' : '' }}>
                                E - Expenses
                            </option>

                            <option value="I" {{ $selectedPartyType === 'I' ? 'selected' : '' }}>
                                I - Income
                            </option>

                            <option value="A" {{ $selectedPartyType === 'A' ? 'selected' : '' }}>
                                A - Asset
                            </option>

                            <option value="L" {{ $selectedPartyType === 'L' ? 'selected' : '' }}>
                                L - Liability
                            </option>

                            <option value="V" {{ $selectedPartyType === 'V' ? 'selected' : '' }}>
                                V - Vendor
                            </option>

                            <option value="C" {{ $selectedPartyType === 'C' ? 'selected' : '' }}>
                                C - Customer
                            </option>
                        </select>

                        @error('party_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <label class="col-md-2 col-form-label">
                        A/C #
                    </label>

                    <div class="col-md-3">
                        <input type="text"
                               name="account_no"
                               maxlength="25"
                               value="{{ old('account_no', $isEdit ? $account->account_no : '') }}"
                               class="form-control @error('account_no') is-invalid @enderror">

                        @error('account_no')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">
                        G/L Account <span class="text-danger">*</span>
                    </label>

                    <div class="col-md-9">
                        <select name="gl_id"
                                id="gl_id"
                                class="form-select @error('gl_id') is-invalid @enderror"
                                required>

                            <option value="">Select G/L Account</option>

                            @foreach($glAccounts as $glAccount)
                                <option value="{{ $glAccount->id }}"
                                    {{ $selectedGlId === $glAccount->id ? 'selected' : '' }}>

                                    {{ $glAccount->id }} - {{ $glAccount->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('gl_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-md-3 col-form-label">
                        1099 Y/N
                    </label>

                    <div class="col-md-2">
                        <select name="yn_1099"
                                id="yn_1099"
                                class="form-select @error('yn_1099') is-invalid @enderror">

                            <option value="N" {{ $selected1099 === 'N' ? 'selected' : '' }}>
                                N - No
                            </option>

                            <option value="Y" {{ $selected1099 === 'Y' ? 'selected' : '' }}>
                                Y - Yes
                            </option>
                        </select>

                        @error('yn_1099')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <label class="col-md-3 col-form-label text-md-end">
                        SSN/EIN for 1099
                    </label>

                    <div class="col-md-4">
                        <input type="text"
                               name="ssn_1099"
                               id="ssn_1099"
                               maxlength="10"
                               value="{{ old('ssn_1099', $isEdit ? $account->ssn_1099 : '') }}"
                               class="form-control @error('ssn_1099') is-invalid @enderror">

                        @error('ssn_1099')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>
                {{ $isEdit ? 'Update Account' : 'Save Account' }}
            </button>

            <a href="{{ route('accountmasters.index') }}"
               class="btn btn-secondary">
                <i class="fas fa-times me-1"></i>
                Cancel
            </a>
        </div>

    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const yn1099 = document.getElementById('yn_1099');
    const ssn1099 = document.getElementById('ssn_1099');

    function update1099Field() {
        if (!yn1099 || !ssn1099) {
            return;
        }

        const enabled = yn1099.value === 'Y';

        ssn1099.readOnly = !enabled;

        if (!enabled) {
            ssn1099.classList.add('bg-light');
        } else {
            ssn1099.classList.remove('bg-light');
        }
    }

    if (yn1099) {
        yn1099.addEventListener('change', update1099Field);
        update1099Field();
    }

    document.querySelectorAll('.phone-input').forEach(function (input) {
        input.addEventListener('input', function () {
            let digits = this.value.replace(/\D/g, '').substring(0, 10);

            if (digits.length >= 7) {
                this.value =
                    '(' + digits.substring(0, 3) + ') ' +
                    digits.substring(3, 6) + '-' +
                    digits.substring(6, 10);
            } else if (digits.length >= 4) {
                this.value =
                    '(' + digits.substring(0, 3) + ') ' +
                    digits.substring(3);
            } else if (digits.length > 0) {
                this.value = '(' + digits;
            } else {
                this.value = '';
            }
        });
    });

});
</script>
@endpush
