@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-head">
                            <h5 class="card-title">Edit Permission Information</h5>
                        </div>
                        <form action="{{ route('update.permission') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $permission->id }}">
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label" for="name">Permission Name</label>
                                        <div class="form-control-wrap">
                                            <input type="text" class="form-control" id="name" name="name" value="{{ $permission->name }}" >
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label">Group Name</label>
                                        <div class="form-control-wrap">
                                            <select class="form-select js-select2" name="group_name" id="group_name" data-search="on">
                                                    <option selected="" disabled="">Select Group</option>
                                                    <option value="members" {{ $permission->group_name == 'members' ? 'selected' : '' }}>Members</option>
                                                    <option value="staff" {{ $permission->group_name == 'staff' ? 'selected' : '' }}>Staff</option>
                                                    <option value="accounts" {{ $permission->group_name == 'accounts' ? 'selected' : '' }}>Accounts</option>
                                                    <option value="drivers" {{ $permission->group_name == 'drivers' ? 'selected' : '' }}>Drivers</option>
                                                    <option value="doctors" {{ $permission->group_name == 'doctors' ? 'selected' : '' }}>Doctors</option>
                                                    <option value="administrator" {{ $permission->group_name == 'administrator' ? 'selected' : '' }}>Administrator</option>
                                                    <option value="nursing" {{ $permission->group_name == 'nursing' ? 'selected' : '' }}>Nursing</option>
                                                    <option value="activity" {{ $permission->group_name == 'activity' ? 'selected' : '' }}>Activity</option>
                                                    <option value="attendance" {{ $permission->group_name == 'attendance' ? 'selected' : '' }}>Attendance</option>
                                                    <option value="shift" {{ $permission->group_name == 'shift' ? 'selected' : '' }}>Shift Accounts</option>
                                                    <option value="role" {{ $permission->group_name == 'role' ? 'selected' : '' }}>Role & Permission</option>
                                                    <option value="sysadmin" {{ $permission->group_name == 'sysadmin' ? 'selected' : '' }}>System Admin</option>
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
