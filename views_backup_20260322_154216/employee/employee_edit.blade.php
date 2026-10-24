@extends('admin.admin_dashboard')
@section('admin')
<style>
    input[type="text"]{
        text-transform: uppercase;
    }
 </style>

<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Edit Employee</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('all.employee') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <form class="needs-validation" novalidate action="{{ route('employee.update') }}" method="POST">
                            @csrf
                            <div class="row mb-0 p-0" style="margin-bottom: 0 !important; padding-bottom: 0 !important;">
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="firstname">First name</label>
                                        <input type="text" class="form-control" id="stafffirstname" name="stafffirstname" placeholder="First name"
                                        value="{{ $employee->stafffirstname }}" required>
                                            <div class="valid-tooltip">
                                                Looks good!
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="lastname">Last name</label>
                                        <input type="text" class="form-control" id="stafflastname" name="stafflastname" placeholder="Last name"
                                         value="{{ $employee->stafflastname }}" required>
                                            <div class="valid-tooltip">
                                                Looks good!
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="username">SS#</label>
                                        <div class="input-group">

                                            <input type="text" class="form-control" id="staffssno" name="staffssno" placeholder="SS #" aria-describedby="validationTooltipUsernamePrepend"
                                                value="{{ $employee->staffssno}}" data-mask="000-00-0000" required>
                                                <div class="invalid-tooltip">
                                                    Please enter valid SS#.
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex justify-content-center align-items-center mb-0 p-0">
                                    <div>
                                        @php
                                            $numericId = (int) trim($employee->staffid); // '005 ' → 5
                                            $imageFilename = $numericId . '.jpg';
                                            $imagePath = 'employees/images/' . $imageFilename;
                                            $fullImagePath = public_path($imagePath);
                                            $displayPath = file_exists($fullImagePath) ? asset($imagePath) : asset('employees/images/no_image.jpg');
                                        @endphp
                                    <img src="{{ $displayPath }}" alt="User Photo" width="150" height="150" style="border:1px solid #ccc; padding:5px; margin-bottom: 0 !important; margin-top: 0 !important;">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="address">Address1</label>
                                        <input type="text" class="form-control" id="staffaddress1" name="staffaddress1" placeholder="Enter Address1"
                                            value="{{ $employee->staffaddress1 }}" required>
                                            <div class="invalid-tooltip">
                                                Please provide a valid address.
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="address">Address2</label>
                                        <input type="text" class="form-control" id="staffaddress2" name="staffaddress2" placeholder="Enter Address2"
                                            value="{{ $employee->staffaddress2 }}">
                                            <div class="invalid-tooltip">
                                                Please provide a valid address.
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="city">City</label>
                                        <input type="text" class="form-control" id="staffcity" name="staffcity" placeholder="Please enter City"
                                            value="{{ $employee->staffcity }}" required>
                                            <div class="invalid-tooltip">
                                                Please provide a valid city.
                                            </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="email">Zip</label>
                                        <input type="text" class="form-control" id="staffzip" name="staffzip" placeholder="Please enter Zip Code"
                                            value="{{ $employee->staffzip }}" data-mask="00000" required>
                                            <div class="invalid-tooltip">
                                                Please provide a valid city.
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="email">Cell #</label>
                                        <input type="text" class="form-control" id="staffcell" name="staffcell" placeholder="Please enter Cell #"
                                            value="{{ $employee->staffcell }}" data-mask="(000)000-0000">
                                            <div class="invalid-tooltip">
                                                Please provide a valid city.
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="phone">Phone</label>
                                        <input type="text" class="form-control" id="staffphone" name="staffphone" placeholder="Enter Phone"
                                            value="{{ $employee->staffphone }}" data-mask="(000)000-0000">
                                            <div class="invalid-tooltip">
                                                Please provide a valid phone.
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="phone">Emergency Contact #</label>
                                        <input type="text" class="form-control" id="staffemergency" name="staffemergency" placeholder="Enter Emergency Phone #"
                                            value="{{ $employee->staffemergency }}" data-mask="(000)000-0000">
                                            <div class="invalid-tooltip">
                                                Please provide a valid phone.
                                            </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="email">Driver Licence #</label>
                                        <input type="text" class="form-control" id="drivelicense" name="drivelicense" placeholder="Please enter Driver Licence"
                                            value="{{ $employee->drivelicense }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="email">Date of Issue</label>
                                        <input type="text" class="form-control flatpickr" id="dateofissue" name="dateofissue" placeholder="Enter DL date of issue"
                                            value="{{ $employee->dateofissue ? \Carbon\Carbon::parse($employee->dateofissue)->format('m/d/Y') : ''}}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="phone">Expiration Date</label>
                                        <input type="text" class="form-control flatpickr" id="dateofexp" name="dateofexp" placeholder="Enter DL Expiration Date"
                                            value="{{ $employee->dateofexp ? \Carbon\Carbon::parse($employee->dateofexp)->format('m/d/Y') : ''}}">
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="email">Hire Date</label>
                                        <input type="text" class="form-control flatpickr" id="dateofhire" name="dateofhire" placeholder="Please enter Hire Date"
                                            value="{{ $employee->dateofhire ? \Carbon\Carbon::parse($employee->dateofhire)->format('m/d/Y') : ''}}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="email">Edu. Qualifications</label>
                                        <input type="text" class="form-control" id="qualification" name="qualification" placeholder="Please enter Qualifications"
                                            value="{{ $employee->qualification}}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="phone">Temination Date</label>
                                        <input type="text" class="form-control flatpickr" id="dateoftermination" name="dateoftermination" placeholder="Enter Job termination date"
                                            value="{{ $employee->dateoftermination ? \Carbon\Carbon::parse($employee->dateoftermination)->format('m/d/Y') : ''}}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">

                                        <x-dynamic-dropdown
                                        name="trans_flag"
                                        table="gnmsstatus"
                                        id="trans_flag"
                                        label="Status"
                                        :selected="$employee->trans_flag ?? null"
                                        required />
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                            <button class="btn btn-primary" type="submit">Save Changes</button>
                            <input type="hidden" value="{{ $employee->staffid }}" name="id">
                            </div>
                        </form>
                    </div>
                </div>
                <!-- end card -->
            </div> <!-- end col -->
        </div>
        <!-- end row -->
    </div>


    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {

               // Apply the mask
        $('.phone-mask').mask('(000)000-0000');

        // On form submit, strip non-digits to get raw format
        $('form').on('submit', function() {
            $('.phone-mask').each(function() {
                const raw = $(this).cleanVal(); // gets the unmasked value
                $(this).val(raw); // replace masked value with raw digits
            });
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
