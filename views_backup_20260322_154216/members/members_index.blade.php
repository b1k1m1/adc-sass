
@extends('admin.admin_dashboard')
@section('title', 'Members')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

<!-- end page title -->

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
               <!-- Nav tabs -->
                @include('admin.partials.tabs_3', ['active' => 'data'])

                <!-- Tab panes -->
                <div class="tab-content p-3 text-muted">
                    <div class="tab-pane active" id="tab-data" role="tabpanel">

                        <div>
                            <a href="{{ route('add.member') }}"><button type="button" class="btn btn-soft-light waves-effect">Add new member</button></a>

                            <a href="{{ route('all.members') }}"><button type="button" class="btn btn-soft-light waves-effect">Edit member</button></a>
                        </div>

                    </div>

                    <div class="tab-pane" id="tab-reports" role="tabpanel">
                        <div>
                            <a href="{{ route('all.member.list') }}"><button type="button" class="btn btn-soft-light waves-effect">Members List</button></a>
                        </div>
                    </div>

                    <div class="tab-pane" id="tab-settings" role="tabpanel">
                        <div class="btn-group dropend">
                            <button type="button" class="btn btn-soft-light waves-effect waves-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Dropdown Setup <i class="mdi mdi-chevron-right"></i>
                            </button>
                            <div class="dropdown-menu" style="">
                                <a class="dropdown-item" href="{{ route('hmo.index') }}">HMO dropdown</a>
                                <a class="dropdown-item" href="{{ route('frequency.index') }}">Frequency Monitor dropdown</a>
                                <a class="dropdown-item" href="{{ route('member_status.index') }}">Members Status dropdown</a>
                            </div>
                        </div>
                        <a href="{{ route('uploadimage.member') }}"><button type="button" class="btn btn-soft-light waves-effect">Upload member's Photo</button></a>
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
