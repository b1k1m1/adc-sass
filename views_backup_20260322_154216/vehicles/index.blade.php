@extends('admin.admin_dashboard')
@section('title', 'Vehicle Master')
@section('page-toolbar')
<a href="{{ route('vehiclemaster.create') }}" class="btn btn-primary">
        Add Vehicle
</a>
<a href="{{ url()->previous() }}" class="btn btn-primary waves-effect waves-light">
    {{ t('common.back','Back') }}
</a>
@endsection
@section('admin')

<style>
  .rpt-title { font-weight:700; font-size:18px; }
</style>

<div class="page-content">
  <div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <!-- LEFT SIDE -->
                <div>
                    <h4 class="mb-0 font-size-18">Vehicle Master</h4>
                </div>
                <!-- RIGHT SIDE TOOLBAR -->
                <div class="page-title-right">
                    @yield('page-toolbar')
                </div>
            </div>
        </div>
        </div>
    <!-- end page title -->

    @if(session('success'))
      <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <div class="card">
      <div class="card-body">

        <div class="table-responsive">
          <table id="vehicleTable"
                 class="table table-sm table-bordered table-hover align-middle w-100">
            <thead class="table-light">
              <tr>
                <th>Vehicle ID</th>
                <th>Category</th>
                <th>Description</th>
                <th>Manufacturer</th>
                <th>Model</th>
                <th>Year</th>
                <th>Plate No</th>
                <th>Driver</th>
                <th>Status</th>
                <th style="width:90px;">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($rows as $r)
                <tr>
                  <td>{{ $r->vehicle_id }}</td>
                  <td>{{ $r->category }}</td>
                  <td>{{ $r->description }}</td>
                  <td>{{ $r->manufacturer }}</td>
                  <td>{{ $r->model }}</td>
                  <td>{{ $r->make_year }}</td>
                  <td>{{ $r->licence_plate_no }}</td>
                  <td> {{ $drivers[$r->driver_id] ?? $r->driver_id }}</td>
                  <td>{{ $r->trans_flag }}</td>
                  <td class="text-center">
                    <a href="{{ route('vehiclemaster.edit', $r->vehicle_id) }}"
                       class="btn btn-outline-primary">
                      Edit
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

      </div>
    </div>

  </div>
</div>

{{-- Simple DataTable init --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  $('#vehicleTable').DataTable({
    pageLength: 20,
    lengthMenu: [[10, 20, 50, -1], [10, 20, 50, 'All']],
    order: [[0, 'asc']],
    stateSave: true,        // remembers page/search
    autoWidth: false,
    columnDefs: [
      { orderable: false, targets: [9] } // Action column
    ],
    language: {
      search: "Search:",
      lengthMenu: "Show _MENU_ records",
      info: "Showing _START_ to _END_ of _TOTAL_ vehicles",
      infoEmpty: "No vehicles found",
      emptyTable: "No vehicles available"
    }
  });
});
</script>

@endsection
