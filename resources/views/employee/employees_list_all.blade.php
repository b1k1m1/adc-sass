@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">All Employee List</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{route('employee.dashboard') }}#profile1" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
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

                        <div class="d-flex justify-content-between mb-3">

                                <select id="statusFilter" class="form-select" style="width: 200px; display: inline-block;">
                                    <option value="">-- All Status --</option>
                                    <option value="A">Active</option>
                                    <option value="H">Hold</option>
                                    <option value="D">Delete</option>
                                </select>

                                <form id="pdfExportForm" action="{{ route('employee.export.pdf') }}" method="GET" target="_blank">
                                    <input type="hidden" name="status" id="statusInput">
                                    <button type="submit" class="btn btn-danger">{{ t('report.export_pdf', 'Export to PDF') }}</button>
                                </form>
                        </div>

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
                                <td>{{ $employee->stafflastname}}</td>
                                <td>{{ $employee->stafffirstname }}</td>
                                <td>{{ $employee->staffaddress1 }}</td>
                                <td>{{ $employee->staffcity }}</td>
                                <td>{{ $employee->staffcity }}</td>

                                <!-- Status with data-status attribute -->
                                <td data-status="{{ $employee->trans_flag }}">
                                @switch($employee->trans_flag)
                                    @case('A') <span class="btn btn-success btn-sm">Active</span> @break
                                    @case('D') <span class="btn btn-danger btn-sm">'Discharged'</span> @break
                                    @case('G') <span class="btn btn-info btn-sm">Guest</span> @break
                                    @case('H') <span class="btn btn-warning btn-sm">Hold</span> @break
                                    @case('N') <span class="btn btn-warning btn-sm">Noon</span> @break
                                    @default <span class="btn btn-secondary btn-sm">No Status</span>
                                @endswitch
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
@endsection

@section('scripts')
<script>
$(document).ready(function () {

// ✅ Add custom filter BEFORE DataTable is initialized

$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
var selectedStatus = $('#statusFilter').val();
var rowStatus = $('#members-table tbody tr').eq(dataIndex).find('td:eq(8)').data('status');
return selectedStatus === '' || rowStatus === selectedStatus;
});

// ✅ Now initialize the DataTable
var table = $('#members-table').DataTable({
    pageLength: 100,
    lengthMenu: [[100, 200, 300, 500, -1], [100, 200, 300, 500, "All"]],
});


 // ✅ Redraw table on filter change
 $('#statusFilter').on('change', function () {
    // Show all entries before applying filter
//table.page.len(-1).draw(); // -1 = "All"

$('#statusInput').val($(this).val()); // Set hidden field for PDF export

// Apply filter
table.draw();
});


//$(document).on('click', '.edit-medical-btn', function () {

//alert('Button clicked!');
});


</script>
@endsection

