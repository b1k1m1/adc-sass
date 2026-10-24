@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Route Master
                    </h4>

                    <div class="page-title-right">
                        <a href="{{ route('route.add') }}"
                           class="btn btn-primary waves-effect waves-light">

                            <i class="mdi mdi-plus me-1"></i>
                            Add Route
                        </a>
                    </div>

                </div>
            </div>
        </div>
        {{-- End Page Title --}}

        {{-- Success / Error Messages --}}
        @if (session('message'))
            <div class="alert alert-{{ session('alert-type') === 'error' ? 'danger' : session('alert-type') }} alert-dismissible fade show"
                 role="alert">

                {{ session('message') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">

                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            Route List
                        </h4>
                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table id="routeTable"
                                   class="table table-bordered table-striped table-hover align-middle nowrap w-100">

                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Route ID</th>
                                        <th>Route Name</th>
                                        <th>Shift</th>
                                        <th>Status</th>
                                        <th>Updated By</th>
                                        <th>Updated Date</th>
                                        <th class="text-center" style="width: 180px;">
                                            Action
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse ($routes as $route)

                                        <tr>

                                            <td></td>

                                            <td>
                                                <strong>
                                                    {{ $route->Route_id }}
                                                </strong>
                                            </td>

                                            <td>
                                                {{ $route->Route_name }}
                                            </td>

                                            <td data-order="{{ $route->shift_code }}">

                                                @if ($route->shift_code === 'A')

                                                    <span class="badge bg-primary">
                                                        Morning
                                                    </span>

                                                @elseif ($route->shift_code === 'N')

                                                    <span class="badge bg-info text-dark">
                                                        Noon
                                                    </span>

                                                @else

                                                    <span class="badge bg-secondary">
                                                        Not Assigned
                                                    </span>

                                                @endif

                                            </td>

                                            <td data-order="{{ $route->trans_flag }}">

                                                @if ($route->trans_flag === 'A')

                                                    <span class="badge bg-success">
                                                        Active
                                                    </span>

                                                @elseif ($route->trans_flag === 'H')

                                                    <span class="badge bg-warning text-dark">
                                                        Hold
                                                    </span>

                                                @else

                                                    <span class="badge bg-secondary">
                                                        {{ $route->trans_flag ?? 'Unknown' }}
                                                    </span>

                                                @endif

                                            </td>

                                            <td>
                                                {{ $route->user_cd ?? '' }}
                                            </td>

                                            <td data-order="{{ $route->update_dt ?? '' }}">

                                                @if (!empty($route->update_dt))
                                                    {{ \Carbon\Carbon::parse($route->update_dt)->format('m/d/Y h:i A') }}
                                                @endif

                                            </td>

                                            <td class="text-center">

                                                <a href="{{ route('route.edit', $route->Route_id) }}"
                                                   class="btn btn-sm btn-primary waves-effect waves-light"
                                                   title="Edit Route">

                                                    <i class="mdi mdi-pencil"></i>
                                                    Edit
                                                </a>

                                                @if ($route->trans_flag === 'A')

                                                    <a href="{{ route('route.status', $route->Route_id) }}"
                                                       class="btn btn-sm btn-warning waves-effect waves-light route-status-btn"
                                                       data-route="{{ $route->Route_id }}"
                                                       data-action="place this route on hold"
                                                       title="Place Route on Hold">

                                                        <i class="mdi mdi-pause-circle"></i>
                                                        Hold
                                                    </a>

                                                @else

                                                    <a href="{{ route('route.status', $route->Route_id) }}"
                                                       class="btn btn-sm btn-success waves-effect waves-light route-status-btn"
                                                       data-route="{{ $route->Route_id }}"
                                                       data-action="activate this route"
                                                       title="Activate Route">

                                                        <i class="mdi mdi-check-circle"></i>
                                                        Activate
                                                    </a>

                                                @endif

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="8"
                                                class="text-center text-muted py-4">

                                                No route records found.

                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection


@push('scripts')

<script>
$(document).ready(function () {

    const table = $('#routeTable').DataTable({
        responsive: true,
        processing: true,
        pageLength: 25,

        lengthMenu: [
            [25, 50, 100, -1],
            [25, 50, 100, 'All']
        ],

        order: [
            [1, 'asc']
        ],

        columnDefs: [
            {
                targets: [0, 7],
                orderable: false,
                searchable: false
            }
        ],

        language: {
            emptyTable: 'No route records found.'
        }
    });

    table.on('order.dt search.dt draw.dt', function () {
        const info = table.page.info();

        table.column(0, {
            search: 'applied',
            order: 'applied',
            page: 'current'
        }).nodes().each(function (cell, index) {
            cell.innerHTML = info.start + index + 1;
        });
    }).draw();

    $(document).on('click', '.route-status-btn', function (event) {

        event.preventDefault();

        const url = $(this).attr('href');
        const routeId = $(this).data('route');
        const actionText = $(this).data('action');

        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to ' + actionText + ' (' + routeId + ')?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Cancel'
        }).then(function (result) {

            if (result.isConfirmed) {
                window.location.href = url;
            }

        });

    });

});
</script>

@endpush
