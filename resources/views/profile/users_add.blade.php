@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Add User</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('sysadmin.dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
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
                        <form class="needs-validation" novalidate action="{{ route('user.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="firstname">First name</label>
                                        <input type="text" class="form-control" id="firstname" name="firstname" placeholder="First name"
                                        value="{{ old('firtsname')}}" required>
                                            <div class="invalid-tooltip">
                                                Please provide First Name.
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="lastname">Last name</label>
                                        <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Last name"
                                         value="{{ old('lastname') }}" required>
                                            <div class="invalid-tooltip">
                                                Please provide Last name.
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="username">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text" id="validationTooltipUsernamePrepend">@</span>
                                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" aria-describedby="validationTooltipUsernamePrepend"
                                                value="" required>
                                                <div class="invalid-tooltip">
                                                    Please provide unique and valid username.
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex justify-content-center align-items-center">
                                    <div>
                                        @php
                                            $numericId = (int) trim($nextid); // '005 ' → 5
                                            $imageFilename = $numericId . '.jpg';
                                            $imagePath = 'users/images/' . $imageFilename;
                                            $fullImagePath = public_path($imagePath);
                                            $displayPath = file_exists($fullImagePath) ? asset($imagePath) : asset('users/images/no_image.jpg');
                                        @endphp
                                    <img src="{{ $displayPath }}" alt="User Photo" width="150" height="150" style="border:1px solid #ccc; padding:5px;">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Please enter Email"
                                            value="" required>
                                            <div class="invalid-tooltip">
                                                Please provide valid Email Address.
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="phone">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter Phone"
                                            value="" data-mask="(000)000-0000" required>
                                            <div class="invalid-tooltip">
                                                Please provide valid phone number.
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="address">Address</label>
                                        <input type="text" class="form-control" id="address" name="address" placeholder="Enter Address"
                                            value="" required>
                                            <div class="invalid-tooltip">
                                                Please provide a address.
                                            </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <x-dynamic-dropdown
                                        name="role"
                                        table="roles"
                                        id="role"
                                        label="Role"
                                        :selected="old('role')?? null"/>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                 <div class="col-md-2">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="status_temp">Status</label>
                                        <select name="status" id="status_temp" class="form-select" >
                                            <option value="">Select</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="status_temp">Password</label>
                                        <input type="text" class="form-control" id="normalpassword" name="normalpassword" placeholder="Enter Password" minlength="8"
                                            value="" required>
                                            <div class="invalid-tooltip">
                                                Please provide a valid password min length 8.
                                            </div>
                                    </div>
                                </div>

                            </div>
                            <button class="btn btn-primary" type="submit">Save Information</button>

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
