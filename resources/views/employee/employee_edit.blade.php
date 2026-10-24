@extends('admin.admin_dashboard')

@section('admin')

<style>
    input[type="text"] {
        text-transform: uppercase;
    }

    .employee-section-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        margin-bottom: 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .employee-section-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 18px;
        border-radius: 10px 10px 0 0;
        font-weight: 600;
        font-size: 15px;
        color: #333;
    }

    .employee-section-body {
        padding: 18px;
    }

    .employee-photo-box {
        border: 1px solid #d1d5db;
        border-radius: 10px;
        padding: 10px;
        background: #fff;
        text-align: center;
        max-width: 180px;
        margin: 0 auto;
    }

    .employee-photo-box img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #ccc;
    }

    .required-label::after {
        content: " *";
        color: #dc3545;
        font-weight: bold;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 4px;
    }

    .form-control,
    .form-select {
        min-height: 38px;
    }

    .section-icon {
        margin-right: 6px;
        color: #556ee6;
    }

    .employee-id-badge {
        background: #eef2ff;
        color: #3949ab;
        font-weight: 600;
        border-radius: 20px;
        padding: 5px 12px;
        display: inline-block;
        font-size: 13px;
    }

    <style>
    .legacy-record-nav {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-right: 8px;
    }

    .legacy-record-nav a {
        width: 34px;
        height: 34px;
        border-radius: 6px;
        background: #556ee6;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 18px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.18);
        transition: all 0.15s ease-in-out;
    }

    .legacy-record-nav a:hover {
        background: #3445b4;
        color: #ffffff;
    }

    .legacy-record-nav a.disabled {
        background: #adb5bd;
        color: #ffffff;
        pointer-events: none;
        opacity: 0.55;
        cursor: not-allowed;
    }

</style>

</style>

<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-sm-0 font-size-18">Edit Employee</h4>
                        <div class="mt-1">
                            <span class="employee-id-badge">
                                Employee ID: {{ str_pad($employee->staffid, 3, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                    </div>

                    <div class="page-title-right">
                        <div class="d-flex align-items-center">

                            <!-- Legacy Record Navigation -->
                            <div class="legacy-record-nav">

                                <!-- First -->
                                <a href="{{ $firstId ? route('edit.employee', str_pad($firstId, 3, '0', STR_PAD_LEFT)) : '#' }}"
                                class="{{ !$firstId || (int)$employee->staffid === (int)$firstId ? 'disabled' : '' }}"
                                title="First Employee">
                                    <i class="mdi mdi-skip-backward"></i>
                                </a>

                                <!-- Previous -->
                                <a href="{{ $previousId ? route('edit.employee', str_pad($previousId, 3, '0', STR_PAD_LEFT)) : '#' }}"
                                class="{{ !$previousId ? 'disabled' : '' }}"
                                title="Previous Employee">
                                    <i class="mdi mdi-step-backward"></i>
                                </a>

                                <!-- Next -->
                                <a href="{{ $nextId ? route('edit.employee', str_pad($nextId, 3, '0', STR_PAD_LEFT)) : '#' }}"
                                class="{{ !$nextId ? 'disabled' : '' }}"
                                title="Next Employee">
                                    <i class="mdi mdi-step-forward"></i>
                                </a>

                                <!-- Last -->
                                <a href="{{ $lastId ? route('edit.employee', str_pad($lastId, 3, '0', STR_PAD_LEFT)) : '#' }}"
                                class="{{ !$lastId || (int)$employee->staffid === (int)$lastId ? 'disabled' : '' }}"
                                title="Last Employee">
                                    <i class="mdi mdi-skip-forward"></i>
                                </a>

                            </div>
                            <!-- End Legacy Record Navigation -->
                            <a href="{{ route('all.employee') }}" class="btn btn-primary waves-effect waves-light">
                                {{ t('common.back','Back') }}
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Title -->

        <form class="needs-validation" novalidate action="{{ route('employee.update') }}" method="POST">
            @csrf

            <input type="hidden" value="{{ $employee->staffid }}" name="id">

            <div class="row">

                <!-- Left Main Column -->
                <div class="col-xl-9 col-lg-8">

                    <!-- Personal Information -->
                    <div class="card employee-section-card">
                        <div class="employee-section-header">
                            <i class="mdi mdi-account section-icon"></i>
                            Personal Information
                        </div>

                        <div class="employee-section-body">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label required-label" for="stafffirstname">First Name</label>
                                        <input type="text"
                                               class="form-control"
                                               id="stafffirstname"
                                               name="stafffirstname"
                                               placeholder="First Name"
                                               value="{{ old('stafffirstname', $employee->stafffirstname) }}"
                                               required>
                                        <div class="invalid-tooltip">
                                            Please enter first name.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label required-label" for="stafflastname">Last Name</label>
                                        <input type="text"
                                               class="form-control"
                                               id="stafflastname"
                                               name="stafflastname"
                                               placeholder="Last Name"
                                               value="{{ old('stafflastname', $employee->stafflastname) }}"
                                               required>
                                        <div class="invalid-tooltip">
                                            Please enter last name.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label required-label" for="staffssno">SS#</label>
                                        <input type="text"
                                               class="form-control"
                                               id="staffssno"
                                               name="staffssno"
                                               placeholder="SS #"
                                               value="{{ old('staffssno', $employee->staffssno) }}"
                                               data-mask="000-00-0000"
                                               required>
                                        <div class="invalid-tooltip">
                                            Please enter valid SS#.
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Address / Contact Information -->
                    <div class="card employee-section-card">
                        <div class="employee-section-header">
                            <i class="mdi mdi-map-marker section-icon"></i>
                            Address & Contact Information
                        </div>

                        <div class="employee-section-body">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label required-label" for="staffaddress1">Address 1</label>
                                        <input type="text"
                                               class="form-control"
                                               id="staffaddress1"
                                               name="staffaddress1"
                                               placeholder="Enter Address 1"
                                               value="{{ old('staffaddress1', $employee->staffaddress1) }}"
                                               required>
                                        <div class="invalid-tooltip">
                                            Please provide address.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="staffaddress2">Address 2</label>
                                        <input type="text"
                                               class="form-control"
                                               id="staffaddress2"
                                               name="staffaddress2"
                                               placeholder="Enter Address 2"
                                               value="{{ old('staffaddress2', $employee->staffaddress2) }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label required-label" for="staffcity">City</label>
                                        <input type="text"
                                               class="form-control"
                                               id="staffcity"
                                               name="staffcity"
                                               placeholder="Enter City"
                                               value="{{ old('staffcity', $employee->staffcity) }}"
                                               required>
                                        <div class="invalid-tooltip">
                                            Please provide city.
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label required-label" for="staffzip">Zip</label>
                                        <input type="text"
                                               class="form-control"
                                               id="staffzip"
                                               name="staffzip"
                                               placeholder="Zip Code"
                                               value="{{ old('staffzip', $employee->staffzip) }}"
                                               data-mask="00000"
                                               required>
                                        <div class="invalid-tooltip">
                                            Please provide zip code.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="staffcell">Cell #</label>
                                        <input type="text"
                                               class="form-control phone-mask"
                                               id="staffcell"
                                               name="staffcell"
                                               placeholder="Cell #"
                                               value="{{ old('staffcell', $employee->staffcell) }}"
                                               data-mask="(000)000-0000">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="staffphone">Phone</label>
                                        <input type="text"
                                               class="form-control phone-mask"
                                               id="staffphone"
                                               name="staffphone"
                                               placeholder="Phone"
                                               value="{{ old('staffphone', $employee->staffphone) }}"
                                               data-mask="(000)000-0000">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="staffemergency">Emergency Contact #</label>
                                        <input type="text"
                                               class="form-control phone-mask"
                                               id="staffemergency"
                                               name="staffemergency"
                                               placeholder="Emergency Phone #"
                                               value="{{ old('staffemergency', $employee->staffemergency) }}"
                                               data-mask="(000)000-0000">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Driver License Information -->
                    <div class="card employee-section-card">
                        <div class="employee-section-header">
                            <i class="mdi mdi-card-account-details section-icon"></i>
                            Driver License Information
                        </div>

                        <div class="employee-section-body">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="drivelicense">Driver License #</label>
                                        <input type="text"
                                               class="form-control"
                                               id="drivelicense"
                                               name="drivelicense"
                                               placeholder="Driver License #"
                                               value="{{ old('drivelicense', $employee->drivelicense) }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="dateofissue">Date of Issue</label>
                                        <input type="text"
                                               class="form-control flatpickr"
                                               id="dateofissue"
                                               name="dateofissue"
                                               placeholder="MM/DD/YYYY"
                                               value="{{ old('dateofissue', $employee->dateofissue ? \Carbon\Carbon::parse($employee->dateofissue)->format('m/d/Y') : '') }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="dateofexp">Expiration Date</label>
                                        <input type="text"
                                               class="form-control flatpickr"
                                               id="dateofexp"
                                               name="dateofexp"
                                               placeholder="MM/DD/YYYY"
                                               value="{{ old('dateofexp', $employee->dateofexp ? \Carbon\Carbon::parse($employee->dateofexp)->format('m/d/Y') : '') }}">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Employment Information -->
                    <div class="card employee-section-card">
                        <div class="employee-section-header">
                            <i class="mdi mdi-briefcase section-icon"></i>
                            Employment Information
                        </div>

                        <div class="employee-section-body">
                            <div class="row">
                                <!-- Staff Category -->
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">

                                        <label class="form-label required-label"
                                            for="staffcategoryid">
                                            Staff Category
                                        </label>

                                        <select class="form-select"
                                                id="staffcategoryid"
                                                name="staffcategoryid"
                                                required>

                                            <option value="">Select Staff Category</option>

                                            @foreach($staffCategories as $category)

                                                <option value="{{ $category->staffcategoryid }}"
                                                    {{ old('staffcategoryid', $employee->staffcategoryid) == $category->staffcategoryid
                                                        ? 'selected'
                                                        : '' }}>

                                                    {{ $category->staffcategoryid }}
                                                    - {{ $category->staffcategory }}

                                                </option>

                                            @endforeach

                                        </select>

                                        <div class="invalid-tooltip">
                                            Please select staff category.
                                        </div>

                                        @error('staffcategoryid')
                                            <div class="text-danger small mt-1">
                                                {{ $message }}
                                            </div>
                                        @enderror

                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="dateofhire">Hire Date</label>
                                        <input type="text"
                                               class="form-control flatpickr"
                                               id="dateofhire"
                                               name="dateofhire"
                                               placeholder="MM/DD/YYYY"
                                               value="{{ old('dateofhire', $employee->dateofhire ? \Carbon\Carbon::parse($employee->dateofhire)->format('m/d/Y') : '') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="qualification">Educational Qualifications</label>
                                        <input type="text"
                                               class="form-control"
                                               id="qualification"
                                               name="qualification"
                                               placeholder="Qualifications"
                                               value="{{ old('qualification', $employee->qualification) }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="dateoftermination">Termination Date</label>
                                        <input type="text"
                                               class="form-control flatpickr"
                                               id="dateoftermination"
                                               name="dateoftermination"
                                               placeholder="MM/DD/YYYY"
                                               value="{{ old('dateoftermination', $employee->dateoftermination ? \Carbon\Carbon::parse($employee->dateoftermination)->format('m/d/Y') : '') }}">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Payroll Information -->
                    <div class="card employee-section-card">
                        <div class="employee-section-header">
                            <i class="mdi mdi-cash-multiple section-icon"></i>
                            Payroll Information
                        </div>

                        <div class="employee-section-body">
                            <div class="row">

                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="employee_type">Employee Type</label>
                                        <select class="form-select" id="employee_type" name="employee_type">
                                            <option value="">Select Type</option>

                                            <option value="DRIVER" {{ old('employee_type', $employee->employee_type ?? '') == 'DRIVER' ? 'selected' : '' }}>
                                                Driver
                                            </option>

                                            <option value="MANAGER" {{ old('employee_type', $employee->employee_type ?? '') == 'MANAGER' ? 'selected' : '' }}>
                                                Manager
                                            </option>

                                            <option value="NURSE" {{ old('employee_type', $employee->employee_type ?? '') == 'NURSE' ? 'selected' : '' }}>
                                                Nurse
                                            </option>

                                            <option value="AIDE" {{ old('employee_type', $employee->employee_type ?? '') == 'AIDE' ? 'selected' : '' }}>
                                                Aide
                                            </option>

                                            <option value="ADMIN" {{ old('employee_type', $employee->employee_type ?? '') == 'ADMIN' ? 'selected' : '' }}>
                                                Admin
                                            </option>

                                            <option value="COOK" {{ old('employee_type', $employee->employee_type ?? '') == 'COOK' ? 'selected' : '' }}>
                                                Kitchen Staff
                                            </option>

                                            <option value="SOCIAL" {{ old('employee_type', $employee->employee_type ?? '') == 'SOCIAL' ? 'selected' : '' }}>
                                                Social Worker
                                            </option>

                                            <option value="OFFICE" {{ old('employee_type', $employee->employee_type ?? '') == 'OFFICE' ? 'selected' : '' }}>
                                                Office Staff
                                            </option>

                                            <option value="OTHER" {{ old('employee_type', $employee->employee_type ?? '') == 'OTHER' ? 'selected' : '' }}>
                                                Other
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="pay_period">Pay Period</label>
                                        <select class="form-select" id="pay_period" name="pay_period">
                                            <option value="">Select Pay Period</option>
                                            <option value="DAILY" {{ old('pay_period', $employee->pay_period ?? '') == 'DAILY' ? 'selected' : '' }}>Daily</option>
                                            <option value="WEEKLY" {{ old('pay_period', $employee->pay_period ?? '') == 'WEEKLY' ? 'selected' : '' }}>Weekly</option>
                                            <option value="BI-WEEKLY" {{ old('pay_period', $employee->pay_period ?? '') == 'BI-WEEKLY' ? 'selected' : '' }}>Bi-Weekly</option>
                                            <option value="SEMI-MONTHLY" {{ old('pay_period', $employee->pay_period ?? '') == 'SEMI-MONTHLY' ? 'selected' : '' }}>Semi-Monthly</option>
                                            <option value="MONTHLY" {{ old('pay_period', $employee->pay_period ?? '') == 'MONTHLY' ? 'selected' : '' }}>Monthly</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="pay_rate_type">Pay Rate Type</label>
                                        <select class="form-select" id="pay_rate_type" name="pay_rate_type">
                                            <option value="">Select Rate Type</option>
                                            <option value="HOURLY" {{ old('pay_rate_type', $employee->pay_rate_type ?? '') == 'HOURLY' ? 'selected' : '' }}>Hourly</option>
                                            <option value="DAILY" {{ old('pay_rate_type', $employee->pay_rate_type ?? '') == 'DAILY' ? 'selected' : '' }}>Daily</option>
                                            <option value="WEEKLY" {{ old('pay_rate_type', $employee->pay_rate_type ?? '') == 'WEEKLY' ? 'selected' : '' }}>Weekly</option>
                                            <option value="MONTHLY" {{ old('pay_rate_type', $employee->pay_rate_type ?? '') == 'MONTHLY' ? 'selected' : '' }}>Monthly</option>
                                            <option value="YEARLY" {{ old('pay_rate_type', $employee->pay_rate_type ?? '') == 'YEARLY' ? 'selected' : '' }}>Yearly</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="pay_rate">Pay Rate</label>
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               class="form-control"
                                               id="pay_rate"
                                               name="pay_rate"
                                               placeholder="Pay Rate"
                                               value="{{ old('pay_rate', $employee->pay_rate ?? '') }}">
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="overtime_eligible">Overtime Eligible</label>
                                        <select class="form-select" id="overtime_eligible" name="overtime_eligible">
                                            <option value="N" {{ old('overtime_eligible', $employee->overtime_eligible ?? 'N') == 'N' ? 'selected' : '' }}>No</option>
                                            <option value="Y" {{ old('overtime_eligible', $employee->overtime_eligible ?? '') == 'Y' ? 'selected' : '' }}>Yes</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="payroll_status">Payroll Status</label>
                                        <select class="form-select" id="payroll_status" name="payroll_status">
                                            <option value="A" {{ old('payroll_status', $employee->payroll_status ?? 'A') == 'A' ? 'selected' : '' }}>Active</option>
                                            <option value="I" {{ old('payroll_status', $employee->payroll_status ?? '') == 'I' ? 'selected' : '' }}>Inactive</option>
                                            <option value="T" {{ old('payroll_status', $employee->payroll_status ?? '') == 'T' ? 'selected' : '' }}>Terminated</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
                <!-- End Left Main Column -->

                <!-- Right Side Column -->
                <div class="col-xl-3 col-lg-4">

                    <!-- Employee Photo -->
                    <div class="card employee-section-card">
                        <div class="employee-section-header">
                            <i class="mdi mdi-image section-icon"></i>
                            Employee Photo
                        </div>

                        <div class="employee-section-body text-center">
                            @php
                                $numericId = (int) trim($employee->staffid);
                                $imageFilename = $numericId . '.jpg';
                                $imagePath = 'employees/images/' . $imageFilename;
                                $fullImagePath = public_path($imagePath);
                                $displayPath = file_exists($fullImagePath)
                                    ? asset($imagePath)
                                    : asset('employees/images/no_image.jpg');
                            @endphp

                            <div class="employee-photo-box">
                                <img src="{{ $displayPath }}" alt="Employee Photo">
                                <div class="mt-2 text-muted small">
                                    Employee ID: {{ str_pad($employee->staffid, 3, '0', STR_PAD_LEFT) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="card employee-section-card">
                        <div class="employee-section-header">
                            <i class="mdi mdi-check-circle section-icon"></i>
                            Employee Status
                        </div>

                        <div class="employee-section-body">
                            <div class="mb-3">
                                <x-dynamic-dropdown
                                    name="trans_flag"
                                    table="gnmsstatus"
                                    id="trans_flag"
                                    label="Status"
                                    :selected="$employee->trans_flag ?? null"
                                    required />

                                <div class="invalid-tooltip">
                                    Please select valid status.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="card employee-section-card">
                        <div class="employee-section-body">
                            <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">
                                <i class="mdi mdi-content-save me-1"></i>
                                {{ t("common.save_changes", "Save Changes") }}
                            </button>

                            <a href="{{ route('all.employee') }}" class="btn btn-light w-100 mt-2">
                                Cancel
                            </a>
                        </div>
                    </div>

                </div>
                <!-- End Right Side Column -->

            </div>
        </form>

    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function () {

        // Apply phone mask if jquery mask plugin is loaded
        if ($.fn.mask) {
            $('.phone-mask').mask('(000)000-0000');
        }

        // On form submit, strip non-digits from phone fields
        $('form').on('submit', function () {
            $('.phone-mask').each(function () {
                let rawValue = $(this).val().replace(/\D/g, '');
                $(this).val(rawValue);
            });
        });

    });

    document.addEventListener('DOMContentLoaded', function () {

        flatpickr(".flatpickr", {
            dateFormat: "m/d/Y",
            allowInput: true
        });

        flatpickr(".flatpickr-range", {
            mode: "range",
            dateFormat: "m/d/Y"
        });

    });
</script>

@endsection
