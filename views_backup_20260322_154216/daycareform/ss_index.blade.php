
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
                            <a href="{{ route('ss-quarterly.index') }}"><button type="button" class="btn btn-soft-light waves-effect">Social Service Quarterly Assessment</button></a>
                            <a href="{{ route('pain.index') }}"><button type="button" class="btn btn-soft-light waves-effect">Pain Assessment</button></a>
                            <a href="{{ route('nursing-assessment.index') }}"><button type="button" class="btn btn-soft-light waves-effect">Nursing Assessment</button></a>
                            <a href="{{ route('medicine_assessment.index') }}"><button type="button" class="btn btn-soft-light waves-effect">Medicine Assessment</button></a>
                        </div>
                    </div>

                    <div class="tab-pane" id="profile1" role="tabpanel">
                        <a href="{{ route('ss-quarterly.report') }}"><button type="button" class="btn btn-soft-light waves-effect">Print & View Quarterly Assessment</button></a>
                        <a href="{{ route('pain.report') }}"><button type="button" class="btn btn-soft-light waves-effect">Print & View Pain Assessment</button></a>
                        <a href="{{ route('nursing-assessment.report') }}"><button type="button" class="btn btn-soft-light waves-effect">Print & View Nursing Assessment</button></a>
                    </div>

                    <div class="tab-pane" id="messages1" role="tabpanel">

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
