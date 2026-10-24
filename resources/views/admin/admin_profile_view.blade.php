@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Profile View</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{route('dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Profile Information</h5>
                        <form action="{{ route('admin.profile.store') }}" method="POST">
                            @csrf
                            `

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label" for="first-name">First Name</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="firstname" name="firstname" value="{{ $profileData->firstname }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label" for="email-address-1">Last Name</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="lastname" name="lastname" value="{{ $profileData->lastname }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label" for="email-address-1">Email Address</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="email" name="email" value="{{ $profileData->email }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label" for="email-address-1">Phone No</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="phone" name="phone" value="{{ $profileData->phone }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label" for="phone-no-1">Address</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="address" name="address" value="{{ $profileData->address }}">
                                        </div>
                                    </div>
                                </div>


                                <div class="col-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-lg btn-primary">Save Information</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
