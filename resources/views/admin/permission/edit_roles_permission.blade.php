@extends('admin.admin_dashboard')
@section('admin')

<style type="text/css">
    .form-check-label{
     text-transform: capitalize;
    }
</style>

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-head">
                            <h5 class="card-title">Edit Roles in Permission</h5>
                        </div>
                        <form action="{{ route('update.roles.permission', $role->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-4">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label" for="name">Roles Name</label>&nbsp;&nbsp;
                                        <h3>{{ $role->name }}</h3>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="form-check">
                                        <!--
                                        <input type="checkbox" class="custom-control-input" id="customCheck1">
                                        <label class="custom-control-label" for="customCheck1">Permission All</label>
                                        -->
                                        <input class="form-check-input" type="checkbox" id="checkDefaultmain">
                                            <label class="form-check-label" for="checkDefaultmain">
                                              &nbsp; &nbsp; Permission All
                                            </label>
                                    </div>
                                </div>
                                <hr>
                                @foreach($permission_groups as $group)
                                <div class="row g-1">
                                    <div class="col-3">
                                        @php
                                            $permissions = App\Models\User::getpermissionByGroupName($group->group_name);
                                        @endphp
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="flexCheckDefault"{{
                                                App\Models\User::roleHasPermissions($role, $permissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="flexCheckDefault">
                                              &nbsp; &nbsp; {{ $group->group_name }}
                                            </label>
                                        </div>
                                        <!--
                                        <div class="custom-control custom-checkbox col-12">
                                            <input type="checkbox" class="custom-control-input" id="customCheck">
                                            <label class="custom-control-label" for="customCheck" >{{ $group->group_name }}</label>
                                        </div>
                                    -->
                                    </div>
                                    <div class="col-9">

                                        @foreach($permissions as $permission )
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="CheckDefault {{ $permission->id }}"
                                                         name="permission[]" value="{{ $permission->id }}" {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="CheckDefault{{ $permission->id }}">
                                                &nbsp; &nbsp;  {{ $permission->name }}
                                            </label>

                                        </div>
                                        @endforeach
                                    </div>
                                </div> <!-- end row -->
                                @endforeach
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

@section('scripts')
<script type="text/javascript">
    $('#checkDefaultmain').click(function(){

        if($(this).is(':checked')) {
            $('input[ type= checkbox]').prop('checked',true);
        }else{
            $('input[ type= checkbox]').prop('checked',false);
        }
    });
</script>
@endsection
