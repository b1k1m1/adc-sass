@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">All Employees List</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{route('employee.dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
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

                        <table id="members-table" class="table table-bordered dt-responsive  nowrap w-100">
                            <thead>
                            <tr>
                                <th>Sr#</th>
                                <th>ID</th>
                                <th>Photo</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>Zip Code</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>


                            <tbody>
                            @foreach($employees as $key=> $employee )
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td><a href="#">{{ $employee->staffid }}</a></td>
                                @php
                                    $numericId = (int) trim($employee->staffid); // '005 ' → 5
                                    $imageFilename = $numericId . '.jpg';
                                    $imagePath = 'employees/images/' . $imageFilename;
                                    $fullImagePath = public_path($imagePath);
                                    $displayPath = file_exists($fullImagePath) ? asset($imagePath) : asset('employees/images/no_image.jpg');
                                @endphp
                                <td><img src={{ $displayPath }} width="50" class="img-thumbnail rounded-circle"></td>
                                <td>{{ $employee->stafflastname }}</td>
                                <td>{{ $employee->stafffirstname }}</td>
                                <td>{{ $employee->staffaddress1 }}</td>
                                <td>{{ $employee->staffcity }}</td>
                                <td>{{ $employee->staffzip }}</td>
                                @if($employee->trans_flag == 'A')
                                    <td><span class="btn btn-success btn-sm">Active</span></td>
                                @elseif($employee->trans_flag == 'D')
                                    <td><span class="btn btn-danger btn-sm">Delete</span></td>
                                @elseif($employee->trans_flag == 'G')
                                    <td><span class="btn btn-info btn-sm">Guest</span></td>
                                @elseif($employee->trans_flag == 'H')
                                    <td><span class="btn btn-warning btn-sm">Hold</span></td>
                                    @elseif($employee->trans_flag == 'N')
                                    <td><span class="btn btn-warning btn-sm">Noon</span></td>
                                @elseif($employee->trans_flag == '')
                                    <td><span class="btn btn-secondary btn-sm">No Status</span></td>
                                @endif
                                <td>
                                    <a href="{{ route('edit.employee', $employee->staffid) }}" class="btn btn-primary waves-light waves-effect" aria-expanded="false">Edit
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

    $('#members-table').DataTable({
        pageLength: 100, // ✅ Default entries per page
        lengthMenu: [ [100, 200, 300, 500, -1], [100, 200, 300, 500, "All"] ], // ✅ Dropdown options
    });

    //$(document).on('click', '.edit-medical-btn', function () {

    //alert('Button clicked!');
});

</script>
@endsection

