@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">All Permission</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{route('add.permission') }}" class="btn btn-primary waves-effect waves-light">Add Permission</a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-body">

                        <table id="datatable" class="table table-bordered dt-responsive nowrap w-100">
                            <thead>
                            <tr>
                                <th>Sr#</th>
                                <th>Permission Name</th>
                                <th>Permision Group</th>
                                <th>Guard name</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($permissions as $key=> $permission)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $permission->name }}</td>
                                <td>{{ $permission->group_name }}</td>
                                <td>{{ $permission->guard_name }}</td>
                                <td>
                                    <a href="{{ route('edit.permission', $permission->id)}}" class="btn btn-primary waves-light waves-effect" aria-expanded="false">Edit
                                    <i class="mdi mdi-dots-vertical ms-2"></i></a>
                                    <a href="{{ route('delete.permission', $permission->id) }}" class="btn btn-info waves-light waves-effect" aria-expanded="false">Delete
                                    <i class="mdi mdi-dots-vertical ms-2"></i></a>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div> <!-- end col -->
        </div> <!-- end row -->


    </div> <!-- container-fluid -->
</div>
<!-- End Page-content -->
@endsection




