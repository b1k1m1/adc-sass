@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-head">
                            <h5 class="card-title">Add Permission Information</h5>
                        </div>
                        <form action="{{ route('store.permission') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label" for="name">Permission Name</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="name" name="name" >
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label">Group Name</label>
                                        <div class="form-control-wrap">
                                            <select class="form-select js-select2" name="group_name" id="group_name" data-search="on">
                                                <option selected="" disabled="">Select Group</option>
                                                <option value="members">Members</option>
                                                <option value="staff">Staff</option>
                                                <option value="accounts">Accounts</option>
                                                <option value="drivers">Drivers</option>
                                                <option value="doctors">Doctors</option>
                                                <option value="administrator">Administrator</option>
                                                <option value="nursing">Nursing</option>
                                                <option value="activity">Activity</option>
                                                <option value="social">Assessment</option>
                                                <option value="dietician">Pharmacist & Dietician</option>
                                                <option value="attendance">Attendance</option>
                                                <option value="claims">Claims & ERA</option>
                                                <option value="shift">Shift Accounts</option>
                                                <option value="role">Role & Permission</option>
                                                <option value="sysadmin">System Admin</option>
                                            </select>
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
