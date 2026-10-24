@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Role All</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{route('add.role') }}" class="btn btn-primary waves-effect waves-light">Add Role</a>
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
                                <th>Role Name</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $key => $item )
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td> <a href="{{ route('edit.role', $item->id)}}" class="btn btn-round btn-sm btn-warning" id="edit"> Edit </a>
                                         <a href="{{ route('delete.role', $item->id) }}" class="btn btn-round btn-sm btn-danger" id="delete"> Delete </a>
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




