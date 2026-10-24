@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">All Users List</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{route('sysadmin.dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
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

                        <table id="users-table" class="table table-bordered dt-responsive  nowrap w-100">
                            <thead>
                            <tr>
                                <th>Sr#</th>
                                <th>ID</th>
                                <th>Photo</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>User Name</th>
                                <th>Email Address</th>
                                <th>Address</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($users as $key=> $user )
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td><a href="#">{{ $user->id}}</a></td>
                                @php
                                    $numericId = (int) trim($user->id); // '005 ' → 5
                                    $imageFilename = $numericId . '.jpg';
                                    $imagePath = 'users/images/' . $imageFilename;
                                    $fullImagePath = public_path($imagePath);
                                    $displayPath = file_exists($fullImagePath) ? asset($imagePath) : asset('users/images/no_image.jpg');
                                @endphp
                                <td><img src={{ $displayPath }} width="50" class="img-thumbnail rounded-circle"></td>
                                <td>{{ $user->lastname}}</td>
                                <td>{{ $user->firstname }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->address }}</td>
                                <td>{{ '('.substr($user->phone, 0, 3).') '.substr($user->phone, 3, 3).'-'.substr($user->phone, 6) }}</td>
                                <td>{{ $user->role_name ?? 'N/A' }}</td> <!-- Display Role Name -->
                                <td>
                                    <a href="{{ route('edit.user', $user->id) }}" class="btn btn-primary waves-light waves-effect" aria-expanded="false">Edit
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

@section('scripts')
<script>

$(document).ready(function () {

    $('#users-table').DataTable({
        pageLength: 10, // ✅ Default entries per page
        lengthMenu: [ [10, 20, 30, 50, -1], [10, 20, 30, 50, "All"] ], // ✅ Dropdown options
    });

});

</script>
@endsection

