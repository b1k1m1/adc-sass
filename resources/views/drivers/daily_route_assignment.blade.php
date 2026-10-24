@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Daily Temporary Route Assignment
                    </h4>

                    <div class="page-title-right">

                        <a href="{{ route('route.list') }}"
                           class="btn btn-primary waves-effect waves-light">

                            <i class="mdi mdi-arrow-left me-1"></i>
                            Back

                        </a>

                    </div>

                </div>
            </div>
        </div>
        {{-- End Page Title --}}

        {{-- Validation Errors --}}
        @if ($errors->any())

            <div class="alert alert-danger alert-dismissible fade show" role="alert">

                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>

            </div>

        @endif

        {{-- Session Message --}}
        @if (session('message'))

            @php
                $alertClass = match (session('alert-type')) {
                    'error'   => 'danger',
                    'warning' => 'warning',
                    'info'    => 'info',
                    default   => 'success',
                };
            @endphp

            <div class="alert alert-{{ $alertClass }} alert-dismissible fade show"
                 role="alert">

                {{ session('message') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>

            </div>

        @endif

        {{-- Assignment Form --}}
        <div class="row">
            <div class="col-12">

                <div class="card">

                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            Assign Members to Another Route
                        </h4>
                    </div>

                    <div class="card-body">

                        <form method="POST"
                              action="{{ route('route.daily.assignment.store') }}"
                              id="dailyRouteAssignmentForm"
                              autocomplete="off">

                            @csrf

                            <div class="row align-items-end">

                                {{-- Assignment Date --}}
                                <div class="col-xl-2 col-lg-3 col-md-4 mb-3">

                                    <label for="assignment_date"
                                           class="form-label">

                                        Assignment Date
                                        <span class="text-danger">*</span>

                                    </label>

                                    <input type="date"
                                           name="assignment_date"
                                           id="assignment_date"
                                           class="form-control @error('assignment_date') is-invalid @enderror"
                                           value="{{ old('assignment_date', $selectedDate) }}"
                                           required>

                                    @error('assignment_date')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                                {{-- Shift --}}
                                <div class="col-xl-2 col-lg-3 col-md-4 mb-3">

                                    <label for="shift"
                                           class="form-label">

                                        Shift
                                        <span class="text-danger">*</span>

                                    </label>

                                    <select name="shift"
                                            id="shift"
                                            class="form-select @error('shift') is-invalid @enderror"
                                            required>

                                        <option value="">
                                            Select Shift
                                        </option>

                                        <option value="A"
                                            {{ old('shift') === 'A' ? 'selected' : '' }}>
                                            Morning
                                        </option>

                                        <option value="N"
                                            {{ old('shift') === 'N' ? 'selected' : '' }}>
                                            Noon
                                        </option>

                                    </select>

                                    @error('shift')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                                {{-- Original Route --}}
                                <div class="col-xl-3 col-lg-3 col-md-4 mb-3">

                                    <label for="original_route_id"
                                           class="form-label">

                                        Original Route
                                        <span class="text-danger">*</span>

                                    </label>

                                    <select name="original_route_id"
                                            id="original_route_id"
                                            class="form-select @error('original_route_id') is-invalid @enderror"
                                            required
                                            disabled>

                                        <option value="">
                                            Select Original Route
                                        </option>

                                    </select>

                                    @error('original_route_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                                {{-- Destination Route --}}
                                <div class="col-xl-3 col-lg-3 col-md-4 mb-3">

                                    <label for="assigned_route_id"
                                           class="form-label">

                                        Destination Route
                                        <span class="text-danger">*</span>

                                    </label>

                                    <select name="assigned_route_id"
                                            id="assigned_route_id"
                                            class="form-select @error('assigned_route_id') is-invalid @enderror"
                                            required
                                            disabled>

                                        <option value="">
                                            Select Destination Route
                                        </option>

                                    </select>

                                    @error('assigned_route_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                                {{-- Load Members --}}
                                <div class="col-xl-2 col-lg-3 col-md-4 mb-3">

                                    <button type="button"
                                            id="loadMembersBtn"
                                            class="btn btn-info waves-effect waves-light w-100"
                                            disabled>

                                        <i class="mdi mdi-account-search me-1"></i>
                                        Load Members

                                    </button>

                                </div>

                            </div>

                            <div class="row">

                                {{-- Reason --}}
                                <div class="col-md-12 mb-3">

                                    <label for="reason"
                                           class="form-label">

                                        Reason

                                    </label>

                                    <input type="text"
                                           name="reason"
                                           id="reason"
                                           maxlength="100"
                                           class="form-control @error('reason') is-invalid @enderror"
                                           value="{{ old('reason') }}"
                                           placeholder="Example: Regular driver absent, vehicle issue, temporary route coverage">

                                    @error('reason')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                            </div>

                            {{-- Member Selection Section --}}
                            <div id="memberSelectionSection"
                                 class="d-none">

                                <hr>

                                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">

                                    <div>
                                        <h5 class="mb-1">
                                            Members on Selected Route
                                        </h5>

                                        <div class="text-muted">
                                            Select the members who should be moved temporarily.
                                        </div>
                                    </div>

                                    <div class="mt-2 mt-md-0">

                                        <button type="button"
                                                id="selectAllMembersBtn"
                                                class="btn btn-sm btn-outline-primary me-1">

                                            <i class="mdi mdi-checkbox-multiple-marked-outline me-1"></i>
                                            Select All

                                        </button>

                                        <button type="button"
                                                id="clearAllMembersBtn"
                                                class="btn btn-sm btn-outline-secondary">

                                            <i class="mdi mdi-checkbox-multiple-blank-outline me-1"></i>
                                            Clear All

                                        </button>

                                    </div>

                                </div>

                                <div id="memberLoadingMessage"
                                     class="alert alert-info d-none">

                                    <span class="spinner-border spinner-border-sm me-2"
                                          role="status">
                                    </span>

                                    Loading route members...

                                </div>

                                <div id="memberAjaxError"
                                     class="alert alert-danger d-none">
                                </div>

                                <div class="table-responsive">

                                    <table id="routeMembersTable"
                                           class="table table-bordered table-striped table-hover align-middle w-100">

                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center"
                                                    style="width: 70px;">
                                                    Select
                                                </th>

                                                <th style="width: 120px;">
                                                    Participant ID
                                                </th>

                                                <th>
                                                    Member Name
                                                </th>

                                                <th style="width: 150px;">
                                                    Permanent Route
                                                </th>

                                                <th style="width: 180px;">
                                                    Existing Temporary Route
                                                </th>

                                                <th style="width: 130px;">
                                                    Status
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody id="routeMembersBody">
                                        </tbody>

                                    </table>

                                </div>

                                <div id="noMembersMessage"
                                     class="alert alert-warning d-none">

                                    No members were found for the selected shift and route.

                                </div>

                                <div class="d-flex justify-content-end mt-3">

                                    <button type="submit"
                                            id="saveAssignmentsBtn"
                                            class="btn btn-success waves-effect waves-light"
                                            disabled>

                                        <i class="mdi mdi-content-save me-1"></i>
                                        Move Selected Temporarily

                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>

        {{-- Existing Assignments --}}
        <div class="row">
            <div class="col-12">

                <div class="card">

                    <div class="card-header">

                        <div class="d-flex flex-wrap align-items-center justify-content-between">

                            <div>
                                <h4 class="card-title mb-1">
                                    Existing Temporary Assignments
                                </h4>

                                <div class="text-muted">
                                    Assignment Date:
                                    {{ \Carbon\Carbon::parse($selectedDate)->format('m/d/Y') }}
                                </div>
                            </div>

                            <form method="GET"
                                  action="{{ route('route.daily.assignment') }}"
                                  class="d-flex align-items-end gap-2 mt-2 mt-md-0">

                                <div>
                                    <label for="view_assignment_date"
                                           class="form-label mb-1">
                                        View Date
                                    </label>

                                    <input type="date"
                                           name="assignment_date"
                                           id="view_assignment_date"
                                           class="form-control"
                                           value="{{ $selectedDate }}">
                                </div>

                                <button type="submit"
                                        class="btn btn-primary">

                                    <i class="mdi mdi-magnify me-1"></i>
                                    View

                                </button>

                            </form>

                        </div>

                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table id="existingAssignmentsTable"
                                   class="table table-bordered table-striped table-hover align-middle nowrap w-100">

                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Participant ID</th>
                                        <th>Member Name</th>
                                        <th>Shift</th>
                                        <th>Original Route</th>
                                        <th>Temporary Route</th>
                                        <th>Reason</th>
                                        <th>Assigned By</th>
                                        <th>Updated Date</th>
                                        <th class="text-center"
                                            style="width: 100px;">
                                            Action
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach ($assignments as $assignment)

                                        <tr>

                                            <td></td>

                                            <td>
                                                {{ $assignment->ParticipantId }}
                                            </td>

                                            <td>
                                                {{ trim(
                                                    ($assignment->LastName ?? '') . ', ' .
                                                    ($assignment->FirstName ?? '') . ' ' .
                                                    ($assignment->MiddleName ?? '')
                                                ) }}
                                            </td>

                                            <td data-order="{{ $assignment->member_shift }}">

                                                @if ($assignment->member_shift === 'A')

                                                    <span class="badge bg-primary">
                                                        Morning
                                                    </span>

                                                @elseif ($assignment->member_shift === 'N')

                                                    <span class="badge bg-info text-dark">
                                                        Noon
                                                    </span>

                                                @else

                                                    <span class="badge bg-secondary">
                                                        {{ $assignment->member_shift }}
                                                    </span>

                                                @endif

                                            </td>

                                            <td>

                                                <strong>
                                                    {{ $assignment->original_route_id }}
                                                </strong>

                                                @if (!empty($assignment->original_route_name))
                                                    -
                                                    {{ $assignment->original_route_name }}
                                                @endif

                                            </td>

                                            <td>

                                                <strong>
                                                    {{ $assignment->assigned_route_id }}
                                                </strong>

                                                @if (!empty($assignment->assigned_route_name))
                                                    -
                                                    {{ $assignment->assigned_route_name }}
                                                @endif

                                            </td>

                                            <td>
                                                {{ $assignment->reason ?? '' }}
                                            </td>

                                            <td>
                                                {{ $assignment->user_cd ?? '' }}
                                            </td>

                                            <td data-order="{{ $assignment->updated_at ?? '' }}">

                                                @if (!empty($assignment->updated_at))

                                                    {{ \Carbon\Carbon::parse($assignment->updated_at)
                                                        ->format('m/d/Y h:i A') }}

                                                @endif

                                            </td>

                                            <td class="text-center">

                                                <form method="POST"
                                                      action="{{ route(
                                                          'route.daily.assignment.remove',
                                                          $assignment->id
                                                      ) }}"
                                                      class="remove-assignment-form d-inline">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="btn btn-sm btn-danger"
                                                            data-member="{{ $assignment->ParticipantId }}"
                                                            data-name="{{ trim(
                                                                ($assignment->FirstName ?? '') . ' ' .
                                                                ($assignment->LastName ?? '')
                                                            ) }}">

                                                        <i class="mdi mdi-delete me-1"></i>
                                                        Remove

                                                    </button>

                                                </form>

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

    </div>
</div>

@endsection


@push('scripts')

<script>
$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | Route Data Supplied by Controller
    |--------------------------------------------------------------------------
    */
    const routes = @json($routes);

    const oldShift = @json(old('shift'));
    const oldOriginalRoute = @json(old('original_route_id'));
    const oldAssignedRoute = @json(old('assigned_route_id'));


    /*
    |--------------------------------------------------------------------------
    | Existing Assignments DataTable
    |--------------------------------------------------------------------------
    */
    const existingAssignmentsTable = $('#existingAssignmentsTable').DataTable({
        responsive: true,
        processing: true,
        pageLength: 25,

        lengthMenu: [
            [25, 50, 100, -1],
            [25, 50, 100, 'All']
        ],

        order: [
            [3, 'asc'],
            [5, 'asc'],
            [2, 'asc']
        ],

        columnDefs: [
            {
                targets: [0, 9],
                orderable: false,
                searchable: false
            }
        ],

        language: {
            emptyTable: 'No temporary route assignments found for this date.',
            zeroRecords: 'No matching assignments found.'
        }
    });

    existingAssignmentsTable.on(
        'order.dt search.dt draw.dt',
        function () {

            const pageInfo = existingAssignmentsTable.page.info();

            existingAssignmentsTable
                .column(0, {
                    search: 'applied',
                    order: 'applied',
                    page: 'current'
                })
                .nodes()
                .each(function (cell, index) {
                    cell.innerHTML = pageInfo.start + index + 1;
                });

        }
    ).draw();


    /*
    |--------------------------------------------------------------------------
    | Populate Route Dropdowns by Shift
    |--------------------------------------------------------------------------
    */
    function populateRouteDropdowns(
        shift,
        selectedOriginalRoute = '',
        selectedAssignedRoute = ''
    ) {
        const originalRouteSelect = $('#original_route_id');
        const assignedRouteSelect = $('#assigned_route_id');

        originalRouteSelect.empty().append(
            '<option value="">Select Original Route</option>'
        );

        assignedRouteSelect.empty().append(
            '<option value="">Select Destination Route</option>'
        );

        if (!shift) {
            originalRouteSelect.prop('disabled', true);
            assignedRouteSelect.prop('disabled', true);
            $('#loadMembersBtn').prop('disabled', true);
            return;
        }

        const filteredRoutes = routes.filter(function (route) {
            return route.shift_code === shift &&
                   route.trans_flag === 'A';
        });

        filteredRoutes.forEach(function (route) {

            const label =
                route.Route_id + ' - ' + (route.Route_name ?? '');

            originalRouteSelect.append(
                $('<option>', {
                    value: route.Route_id,
                    text: label,
                    selected:
                        route.Route_id === selectedOriginalRoute
                })
            );

            assignedRouteSelect.append(
                $('<option>', {
                    value: route.Route_id,
                    text: label,
                    selected:
                        route.Route_id === selectedAssignedRoute
                })
            );

        });

        originalRouteSelect.prop('disabled', false);
        assignedRouteSelect.prop('disabled', false);

        refreshDestinationRoutes();
        updateLoadButtonState();
    }


    /*
    |--------------------------------------------------------------------------
    | Exclude Original Route from Destination Dropdown
    |--------------------------------------------------------------------------
    */
    function refreshDestinationRoutes() {

        const shift = $('#shift').val();
        const originalRouteId = $('#original_route_id').val();
        const currentDestination = $('#assigned_route_id').val();

        const assignedRouteSelect = $('#assigned_route_id');

        assignedRouteSelect.empty().append(
            '<option value="">Select Destination Route</option>'
        );

        if (!shift) {
            assignedRouteSelect.prop('disabled', true);
            return;
        }

        const filteredRoutes = routes.filter(function (route) {

            return route.shift_code === shift &&
                   route.trans_flag === 'A' &&
                   route.Route_id !== originalRouteId;

        });

        filteredRoutes.forEach(function (route) {

            const label =
                route.Route_id + ' - ' + (route.Route_name ?? '');

            assignedRouteSelect.append(
                $('<option>', {
                    value: route.Route_id,
                    text: label,
                    selected:
                        route.Route_id === currentDestination
                })
            );

        });

        assignedRouteSelect.prop('disabled', false);
    }


    /*
    |--------------------------------------------------------------------------
    | Enable or Disable Load Members Button
    |--------------------------------------------------------------------------
    */
    function updateLoadButtonState() {

        const assignmentDate = $('#assignment_date').val();
        const shift = $('#shift').val();
        const originalRouteId = $('#original_route_id').val();

        const canLoad =
            assignmentDate !== '' &&
            shift !== '' &&
            originalRouteId !== '';

        $('#loadMembersBtn').prop('disabled', !canLoad);
    }


    /*
    |--------------------------------------------------------------------------
    | Reset Member Selection
    |--------------------------------------------------------------------------
    */
    function resetMemberSelection() {

        $('#routeMembersBody').empty();

        $('#memberSelectionSection').addClass('d-none');
        $('#memberLoadingMessage').addClass('d-none');
        $('#memberAjaxError').addClass('d-none').text('');
        $('#noMembersMessage').addClass('d-none');

        $('#saveAssignmentsBtn').prop('disabled', true);
    }


    /*
    |--------------------------------------------------------------------------
    | Shift Change
    |--------------------------------------------------------------------------
    */
    $('#shift').on('change', function () {

        const shift = $(this).val();

        populateRouteDropdowns(shift);

        resetMemberSelection();

    });


    /*
    |--------------------------------------------------------------------------
    | Original Route Change
    |--------------------------------------------------------------------------
    */
    $('#original_route_id').on('change', function () {

        refreshDestinationRoutes();
        updateLoadButtonState();
        resetMemberSelection();

    });


    /*
    |--------------------------------------------------------------------------
    | Destination Route Change
    |--------------------------------------------------------------------------
    */
    $('#assigned_route_id').on('change', function () {

        updateSaveButtonState();

    });


    /*
    |--------------------------------------------------------------------------
    | Assignment Date Change
    |--------------------------------------------------------------------------
    */
    $('#assignment_date').on('change', function () {

        updateLoadButtonState();
        resetMemberSelection();

    });


    /*
    |--------------------------------------------------------------------------
    | Load Members through AJAX
    |--------------------------------------------------------------------------
    */
    $('#loadMembersBtn').on('click', function () {

        const assignmentDate = $('#assignment_date').val();
        const shift = $('#shift').val();
        const originalRouteId = $('#original_route_id').val();

        if (!assignmentDate || !shift || !originalRouteId) {
            alert(
                'Please select the assignment date, shift, and original route.'
            );
            return;
        }

        $('#memberSelectionSection').removeClass('d-none');
        $('#memberLoadingMessage').removeClass('d-none');
        $('#memberAjaxError').addClass('d-none').text('');
        $('#noMembersMessage').addClass('d-none');
        $('#routeMembersBody').empty();
        $('#saveAssignmentsBtn').prop('disabled', true);

        $('#loadMembersBtn')
            .prop('disabled', true)
            .html(
                '<span class="spinner-border spinner-border-sm me-1"></span>' +
                'Loading...'
            );

        $.ajax({
            url: "{{ route('route.daily.members') }}",
            type: "GET",

            data: {
                assignment_date: assignmentDate,
                shift: shift,
                route_id: originalRouteId
            },

            success: function (response) {

                $('#memberLoadingMessage').addClass('d-none');

                if (
                    !response.success ||
                    !Array.isArray(response.members)
                ) {
                    showMemberAjaxError(
                        response.message ||
                        'Members could not be loaded.'
                    );
                    return;
                }

                renderMembers(response.members);
            },

            error: function (xhr) {

                $('#memberLoadingMessage').addClass('d-none');

                let errorMessage =
                    'Members could not be loaded.';

                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {
                    errorMessage = xhr.responseJSON.message;
                }

                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.errors
                ) {
                    const errors = Object.values(
                        xhr.responseJSON.errors
                    ).flat();

                    errorMessage = errors.join('<br>');
                }

                showMemberAjaxError(errorMessage);
            },

            complete: function () {

                $('#loadMembersBtn')
                    .prop('disabled', false)
                    .html(
                        '<i class="mdi mdi-account-search me-1"></i>' +
                        'Load Members'
                    );

                updateLoadButtonState();
            }
        });

    });


    /*
    |--------------------------------------------------------------------------
    | Render Member Rows
    |--------------------------------------------------------------------------
    */
    function renderMembers(members) {

        const tableBody = $('#routeMembersBody');

        tableBody.empty();

        if (members.length === 0) {

            $('#noMembersMessage').removeClass('d-none');
            $('#saveAssignmentsBtn').prop('disabled', true);

            return;
        }

        $('#noMembersMessage').addClass('d-none');

        members.forEach(function (member) {

            const participantId =
                escapeHtml(member.ParticipantId ?? '');

            const memberName = escapeHtml(
                buildMemberName(member)
            );

            const permanentRoute =
                escapeHtml(member.permanent_route ?? '');

            const assignedRoute =
                escapeHtml(member.assigned_route_id ?? '');

            const assignedRouteName =
                escapeHtml(member.assigned_route_name ?? '');

            const alreadyAssigned =
                Number(member.already_assigned) === 1;

            let existingRouteDisplay =
                '<span class="text-muted">None</span>';

            let statusDisplay =
                '<span class="badge bg-success">Available</span>';

            if (alreadyAssigned) {

                existingRouteDisplay =
                    '<strong>' + assignedRoute + '</strong>';

                if (assignedRouteName !== '') {
                    existingRouteDisplay +=
                        ' - ' + assignedRouteName;
                }

                statusDisplay =
                    '<span class="badge bg-warning text-dark">' +
                    'Already Assigned' +
                    '</span>';
            }

            const row = `
                <tr>
                    <td class="text-center">
                        <input type="checkbox"
                               name="member_ids[]"
                               value="${participantId}"
                               class="form-check-input member-checkbox">
                    </td>

                    <td>
                        <strong>${participantId}</strong>
                    </td>

                    <td>
                        ${memberName}
                    </td>

                    <td>
                        ${permanentRoute}
                    </td>

                    <td>
                        ${existingRouteDisplay}
                    </td>

                    <td>
                        ${statusDisplay}
                    </td>
                </tr>
            `;

            tableBody.append(row);

        });

        updateSaveButtonState();
    }


    /*
    |--------------------------------------------------------------------------
    | Member Name
    |--------------------------------------------------------------------------
    */
    function buildMemberName(member) {

        const lastName =
            String(member.LastName ?? '').trim();

        const firstName =
            String(member.FirstName ?? '').trim();

        const middleName =
            String(member.MiddleName ?? '').trim();

        let name = '';

        if (lastName !== '') {
            name += lastName;
        }

        if (firstName !== '') {
            name +=
                (name !== '' ? ', ' : '') +
                firstName;
        }

        if (middleName !== '') {
            name += ' ' + middleName;
        }

        return name;
    }


    /*
    |--------------------------------------------------------------------------
    | Select All / Clear All
    |--------------------------------------------------------------------------
    */
    $('#selectAllMembersBtn').on('click', function () {

        $('.member-checkbox').prop('checked', true);

        updateSaveButtonState();

    });

    $('#clearAllMembersBtn').on('click', function () {

        $('.member-checkbox').prop('checked', false);

        updateSaveButtonState();

    });

    $(document).on(
        'change',
        '.member-checkbox',
        function () {
            updateSaveButtonState();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Enable Save Button
    |--------------------------------------------------------------------------
    */
    function updateSaveButtonState() {

        const selectedMembers =
            $('.member-checkbox:checked').length;

        const destinationRoute =
            $('#assigned_route_id').val();

        const shouldDisable =
            selectedMembers === 0 ||
            !destinationRoute;

        $('#saveAssignmentsBtn')
            .prop('disabled', shouldDisable);
    }


    /*
    |--------------------------------------------------------------------------
    | Form Submit Confirmation
    |--------------------------------------------------------------------------
    */
    $('#dailyRouteAssignmentForm').on(
        'submit',
        function (event) {

            const selectedMembers =
                $('.member-checkbox:checked').length;

            const destinationText =
                $('#assigned_route_id option:selected').text();

            if (selectedMembers === 0) {
                event.preventDefault();
                alert('Please select at least one member.');
                return;
            }

            if (!$('#assigned_route_id').val()) {
                event.preventDefault();
                alert('Please select the destination route.');
                return;
            }

            const confirmed = confirm(
                'Move ' +
                selectedMembers +
                ' selected member(s) temporarily to ' +
                destinationText +
                '?'
            );

            if (!confirmed) {
                event.preventDefault();
                return;
            }

            $('#saveAssignmentsBtn')
                .prop('disabled', true)
                .html(
                    '<span class="spinner-border spinner-border-sm me-1"></span>' +
                    'Saving...'
                );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Remove Assignment Confirmation
    |--------------------------------------------------------------------------
    */
    $(document).on(
        'submit',
        '.remove-assignment-form',
        function (event) {

            const button = $(this).find('button[type="submit"]');

            const participantId =
                button.data('member');

            const memberName =
                button.data('name');

            const confirmed = confirm(
                'Remove the temporary route assignment for:\n\n' +
                participantId +
                ' - ' +
                memberName +
                '?\n\n' +
                'The member will return to the permanent route.'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | AJAX Error
    |--------------------------------------------------------------------------
    */
    function showMemberAjaxError(message) {

        $('#memberAjaxError')
            .html(message)
            .removeClass('d-none');

        $('#routeMembersBody').empty();
        $('#saveAssignmentsBtn').prop('disabled', true);
    }


    /*
    |--------------------------------------------------------------------------
    | Escape HTML
    |--------------------------------------------------------------------------
    */
    function escapeHtml(value) {

        return $('<div>')
            .text(String(value ?? ''))
            .html();
    }


    /*
    |--------------------------------------------------------------------------
    | Restore Old Values after Validation Error
    |--------------------------------------------------------------------------
    */
    if (oldShift) {

        $('#shift').val(oldShift);

        populateRouteDropdowns(
            oldShift,
            oldOriginalRoute,
            oldAssignedRoute
        );

        $('#original_route_id').val(oldOriginalRoute);

        refreshDestinationRoutes();

        $('#assigned_route_id').val(oldAssignedRoute);

        updateLoadButtonState();

    } else {

        $('#original_route_id').prop('disabled', true);
        $('#assigned_route_id').prop('disabled', true);
        $('#loadMembersBtn').prop('disabled', true);

    }

});
</script>

@endpush
