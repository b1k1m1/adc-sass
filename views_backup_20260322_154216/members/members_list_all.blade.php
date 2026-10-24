@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">All Members List</h4>
                    <!--
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{route('members.dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>
                    -->
                    <a href="{{ route('members.dashboard') }}#profile1"
                        class="btn btn-primary waves-effect waves-light">
                        {{ t('common.back','Back') }}
                    </a>
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
                                    <option value="N">Noon</option>
                                    <option value="G">Guest</option>
                                    <option value="D">Discharge</option>
                                    <option value="H">Hold</option>
                                </select>
                                <div class="d-flex gap-2">
                                    <form id="pdfExportForm" action="{{ route('members.export.pdf') }}" method="GET" target="_blank">
                                        <input type="hidden" name="status" id="statusInput">
                                        <button type="submit" class="btn btn-danger">Export to PDF</button>
                                    </form>
                                    <button id="printTable" class="btn btn-secondary">Print Table</button>
                                </div>
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
                            @foreach($members as $key=> $member )
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td><a href="#">{{ str_pad( $member->ParticipantId ,3, '0',STR_PAD_LEFT) }}</a></td>
                                @php
                                    $numericId = (int) trim($member->ParticipantId); // '005 ' → 5
                                    $imageFilename = $numericId . '.jpg';
                                    $imagePath = 'members/images/' . $imageFilename;
                                    $fullImagePath = public_path($imagePath);
                                    $displayPath = file_exists($fullImagePath) ? asset($imagePath) : asset('members/images/no_image.jpg');
                                @endphp
                                <td><img src={{ $displayPath }} width="50" class="img-thumbnail rounded-circle"></td>
                                <td>{{ $member->LastName }}</td>
                                <td>{{ $member->FirstName }}</td>
                                <td>{{ $member->Address1 }}</td>
                                <td>{{ $member->City }}</td>
                                <td>{{ $member->ZipCode }}</td>

                                <!-- Status with data-status attribute -->
                                <td data-status="{{ $member->Trans_flag }}">
                                @switch($member->Trans_flag)
                                    @case('A') <span class="btn btn-success btn-sm">Active</span> @break
                                    @case('D') <span class="btn btn-danger btn-sm">Discharge</span> @break
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


// Print button handler
    $('#printTable').click(function() {
        var tableHtml = $('#members-table').clone();

        // Remove the last column if you have action buttons (not in your current code)
        // tableHtml.find('th:last-child, td:last-child').remove();

        // Use a popup window for print preview
        var newWin = window.open('', '', 'width=1000,height=700');
        newWin.document.write(`
            <html>
    <head>
        <title>Print Members Table</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 30px; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #000; padding: 6px; text-align: left; }
            img { max-width: 50px; border-radius: 50%; }
            .header {
                text-align: center;
                margin-bottom: 20px;
            }
            .report-title {
                font-size: 18px;
                margin-bottom: 5px;
            }
            .compname {
                font-size: 22px;
                font-weight: bold;
                margin-bottom: 2px;
            }
            .address {
                font-size: 14px;
                margin-bottom: 2px;
            }


        </style>
    </head>
    <body>
        <div class="header">
            <div class="compname">{{ $company->comp_nm ?? ''  }}</div>
            <!--
            <div class="address">123 Main St, City, State ZIP</div>
            -->
            <div class="report-title">All Members List - Status </div>
            <!--
            <div class="date">Date: ${new Date().toLocaleDateString()}</div>
            -->
        </div>
        ${tableHtml.prop('outerHTML')}
    </body>
    </html>
        `);
        newWin.document.close();

        setTimeout(function() {
            newWin.print();
            // newWin.close(); // Uncomment if you want window to close after print dialog
        }, 500);
    });

</script>
@endsection

