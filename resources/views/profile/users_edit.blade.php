@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Edit User</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('all.users') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
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
                        <form class="needs-validation" novalidate action="{{ route('user.update') }}" method="POST">
                            @csrf
                            <div class="row mb-0 p-0" style="margin-bottom: 0 !important; padding-bottom: 0 !important;">
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="firstname">First name</label>
                                        <input type="text" class="form-control" id="firstname" name="firstname" placeholder="First name"
                                        value="{{ $user->firstname }}" required>
                                            <div class="valid-tooltip">
                                                Looks good!
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="lastname">Last name</label>
                                        <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Last name"
                                         value="{{ $user->lastname }}" required>
                                            <div class="valid-tooltip">
                                                Looks good!
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="username">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text" id="validationTooltipUsernamePrepend">@</span>
                                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" aria-describedby="validationTooltipUsernamePrepend"
                                                value="{{ $user->username }}" required>
                                                <div class="invalid-tooltip">
                                                    Please choose a unique and valid username.
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex justify-content-center align-items-center mb-0 p-0">
                                    <div>
                                        @php
                                            $numericId = (int) trim($user->id); // '005 ' → 5
                                            $imageFilename = $numericId . '.jpg';
                                            $imagePath = 'users/images/' . $imageFilename;
                                            $fullImagePath = public_path($imagePath);
                                            $displayPath = file_exists($fullImagePath) ? asset($imagePath) : asset('users/images/no_image.jpg');
                                        @endphp
                                    <img src="{{ $displayPath }}" alt="User Photo" width="150" height="150" style="border:1px solid #ccc; padding:5px; margin-bottom: 0 !important; margin-top: 0 !important;">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Please enter Email"
                                            value="{{ $user->email }}" required>
                                            <div class="invalid-tooltip">
                                                Please provide a valid city.
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="phone">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter Phone"
                                            value="{{ $user->phone }}" data-mask="(000)000-0000" required>
                                            <div class="invalid-tooltip">
                                                Please provide a valid phone.
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="address">Address</label>
                                        <input type="text" class="form-control" id="address" name="address" placeholder="Enter Address"
                                            value="{{ $user->address }}" required>
                                            <div class="invalid-tooltip">
                                                Please provide a valid address.
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
                                        :selected="$user->role ?? null" />
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                </div>
                                 <div class="col-md-2">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="status_temp">Status</label>
                                        <select name="status" id="status_temp" class="form-select">
                                            <option value="Active" {{ old('status', trim($user->status)) === 'Active' ? 'selected' : '' }}>Active</option>
                                            <option value="Inactive" {{ old('status', trim($user->status)) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="status_temp">Normal Password</label>
                                        <input type="text" class="form-control" id="normalpassword" name="normalpassword" placeholder="Enter Password" minlength="8"
                                            value="{{ $user->normalpassword }}" required>
                                            <div class="invalid-tooltip">
                                                Please provide a valid password min length 8.
                                            </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3 position-relative">
                                        <label class="form-label" for="status_temp">Encrypted Password</label>
                                        <input type="text" class="form-control" id="password" name="password" disabled="disabled"
                                            value="{{ $user->password }}" required>
                                            <div class="invalid-tooltip">
                                                Please provide a valid status.
                                            </div>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary" type="submit">{{ t("common.save_changes", "Save Changes") }}</button>
                            <input type="hidden" value="{{ $user->id }}" name="id">
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
