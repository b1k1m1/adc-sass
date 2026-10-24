
@extends('admin.admin_dashboard')
@section('title', 'Roles')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

<!-- end page title -->

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
               <!-- Nav tabs -->
               <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#home1" role="tab">
                        <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                        <span class="d-none d-sm-block">Data Entry</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#profile1" role="tab">
                        <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                        <span class="d-none d-sm-block">Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#messages1" role="tab">
                        <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                        <span class="d-none d-sm-block">Settings</span>
                    </a>
                </li>

            </ul>


                <!-- Tab panes -->
                <div class="tab-content p-3 text-muted">
                    <div class="tab-pane active" id="home1" role="tabpanel">

                        <div>
                            <a href="{{ route('add.user') }}"><button type="button" class="btn btn-soft-light waves-effect">Add new user</button></a>

                            <a href="{{ route('all.users') }}"><button type="button" class="btn btn-soft-light waves-effect">Edit user</button></a>
                        </div>

                    </div>

                    <div class="tab-pane" id="profile1" role="tabpanel">
                        <div>
                            <a href="#"><button type="button" class="btn btn-soft-light waves-effect">Users List</button></a>
                        </div>
                    </div>

                    <div class="tab-pane" id="messages1" role="tabpanel">

                        <a href="{{ route('uploadimage.user') }}"><button type="button" class="btn btn-soft-light waves-effect">Upload user's Photo</button></a>
                    </div>

                   

                </div>
            </div><!-- end card-body -->
        </div><!-- end card -->
    </div><!-- end col -->
</div><!-- end row -->


</div>
<!-- container-fluid -->
</div>
<!-- End Page-content -->

@endsection
