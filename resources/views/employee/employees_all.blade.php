@extends('admin.admin_dashboard')

@section('admin')

<style>
    #employees-table td,
    #employees-table th {
        vertical-align: middle;
    }

    .employee-photo {
        width: 50px;
        height: 50px;
        object-fit: cover;
    }

    .employee-column-dropdown {
        width: 320px;
        max-height: 460px;
        overflow: hidden;
    }

    .employee-column-list {
        max-height: 340px;
        overflow-y: auto;
        padding-right: 6px;
    }

    .employee-column-list .form-check {
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        padding: 7px 10px 7px 34px;
        background: #ffffff;
    }

    .employee-column-list .form-check:hover {
        background: #f8f9fa;
    }

    .employee-column-list .form-check-label {
        width: 100%;
        cursor: pointer;
    }

    .employee-toolbar-control,
    .employee-toolbar-btn {
        height: 38px !important;
        min-height: 38px !important;
    }

    .employee-toolbar-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding-top: 0;
        padding-bottom: 0;
    }

    #employeeColumnDropdown {
        height: 38px !important;
        min-height: 38px !important;
        display: flex;
        align-items: center;
    }

    @media (max-width: 768px) {
        .employee-column-dropdown {
            width: 290px;
        }
    }
</style>

<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">All Employees List</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('employee.dashboard') }}"
                               class="btn btn-primary waves-effect waves-light">
                                {{ t('common.back','Back') }}
                            </a>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row align-items-end mb-3 g-2">

                            {{-- Status Filter --}}
                            <div class="col-md-4 col-lg-3">
                                <form method="GET"
                                      action="{{ url()->current() }}"
                                      id="employeeStatusForm">

                                    <label for="employee_status_filter"
                                           class="form-label fw-semibold">
                                        Select Status
                                    </label>

                                    <div class="d-flex gap-2">
                                        <select name="employee_status_filter"
                                                id="employee_status_filter"
                                                class="form-select employee-toolbar-control">
                                            @foreach($statuses as $status)
                                                <option value="{{ $status->Id }}"
                                                    {{ ($selectedStatus ?? 'A') == $status->Id ? 'selected' : '' }}>
                                                    {{ $status->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit"
                                                class="btn btn-primary employee-toolbar-btn">
                                            <i class="fas fa-search me-1"></i>
                                            Load
                                        </button>

                                        <a href="{{ url()->current() }}"
                                           class="btn btn-secondary employee-toolbar-btn">
                                            Clear
                                        </a>
                                    </div>
                                </form>
                            </div>

                            {{-- Column Dropdown --}}
                            <div class="col-md-4 col-lg-3">
                                <label class="form-label fw-semibold">Columns</label>

                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle w-100 text-start employee-toolbar-btn"
                                            type="button"
                                            id="employeeColumnDropdown"
                                            data-bs-toggle="dropdown"
                                            data-bs-auto-close="outside"
                                            aria-expanded="false">
                                        <i class="fas fa-columns me-1"></i>
                                        Choose Columns
                                    </button>

                                    <div class="dropdown-menu p-3 shadow employee-column-dropdown"
                                         aria-labelledby="employeeColumnDropdown">

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong>Select Columns</strong>

                                            <span id="employeeColumnSaveMessage"
                                                  class="text-success small"
                                                  style="display:none;">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Saved
                                            </span>
                                        </div>

                                        <div class="employee-column-list">
                                            @foreach($availableColumns as $field => $label)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input employee-column-checkbox"
                                                           type="checkbox"
                                                           value="{{ $field }}"
                                                           id="column_{{ $field }}"
                                                           {{ in_array($field, $selectedColumns) ? 'checked' : '' }}>

                                                    <label class="form-check-label"
                                                           for="column_{{ $field }}">
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <hr class="my-2">

                                        <div class="d-flex gap-2">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    id="selectAllEmployeeColumns">
                                                <i class="fas fa-check-double me-1"></i>
                                                Select All
                                            </button>

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    id="resetEmployeeColumns">
                                                <i class="fas fa-undo me-1"></i>
                                                Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="table-responsive">
                            <table id="employees-table"
                                   class="table table-bordered table-striped nowrap w-100">
                                <thead>
                                    <tr>
                                        <th data-column-key="row_number">Sr#</th>
                                        <th data-column-key="photo">Photo</th>

                                        @foreach($availableColumns as $field => $label)
                                            <th data-column-key="{{ $field }}">
                                                {{ $label }}
                                            </th>
                                        @endforeach

                                        <th data-column-key="action">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($employees as $key => $employee)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>

                                            @php
                                                $numericId = (int) trim($employee->staffid);
                                                $imageFilename = $numericId . '.jpg';
                                                $imagePath = 'employees/images/' . $imageFilename;
                                                $fullImagePath = public_path($imagePath);

                                                $displayPath = file_exists($fullImagePath)
                                                    ? asset($imagePath)
                                                    : asset('employees/images/no_image.jpg');
                                            @endphp

                                            <td>
                                                <img src="{{ $displayPath }}"
                                                     alt="Employee Photo"
                                                     class="img-thumbnail rounded-circle employee-photo">
                                            </td>

                                            @foreach($availableColumns as $field => $label)
                                                @php
                                                    $value = $employee->{$field} ?? null;
                                                @endphp

                                                <td>
                                                    @if($field === 'staffid')
                                                        <a href="{{ route('edit.employee', $employee->staffid) }}">
                                                            {{ str_pad($employee->staffid, 3, '0', STR_PAD_LEFT) }}
                                                        </a>

                                                    @elseif($field === 'trans_flag')
                                                        @switch($value)
                                                            @case('A')
                                                                <span class="badge bg-success">Active</span>
                                                                @break

                                                            @case('D')
                                                                <span class="badge bg-danger">Discharged</span>
                                                                @break

                                                            @case('G')
                                                                <span class="badge bg-info">Guest</span>
                                                                @break

                                                            @case('H')
                                                                <span class="badge bg-warning text-dark">Hold</span>
                                                                @break

                                                            @case('N')
                                                                <span class="badge bg-warning text-dark">Noon</span>
                                                                @break

                                                            @default
                                                                <span class="badge bg-secondary">No Status</span>
                                                        @endswitch

                                                    @elseif($field === 'payroll_status')
                                                        @switch($value)
                                                            @case('A')
                                                                <span class="badge bg-success">Active</span>
                                                                @break

                                                            @case('I')
                                                                <span class="badge bg-secondary">Inactive</span>
                                                                @break

                                                            @case('T')
                                                                <span class="badge bg-danger">Terminated</span>
                                                                @break

                                                            @default
                                                                <span class="badge bg-secondary">N/A</span>
                                                        @endswitch

                                                    @elseif($field === 'overtime_eligible')
                                                        @if($value === 'Y')
                                                            <span class="badge bg-success">Yes</span>
                                                        @elseif($value === 'N')
                                                            <span class="badge bg-secondary">No</span>
                                                        @else
                                                            <span class="badge bg-secondary">N/A</span>
                                                        @endif

                                                    @elseif(in_array($field, [
                                                        'dateofissue',
                                                        'dateofexp',
                                                        'dateofhire',
                                                        'dateoftermination'
                                                    ], true))
                                                        @if(!empty($value))
                                                            {{ \Carbon\Carbon::parse($value)->format('m/d/Y') }}
                                                        @endif

                                                    @elseif(in_array($field, [
                                                        'staffcell',
                                                        'staffphone',
                                                        'staffemergency'
                                                    ], true))
                                                        @php
                                                            $phone = preg_replace('/\D/', '', $value ?? '');
                                                        @endphp

                                                        @if(strlen($phone) === 10)
                                                            {{ '(' . substr($phone, 0, 3) . ')' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4) }}
                                                        @else
                                                            {{ $value }}
                                                        @endif

                                                    @elseif($field === 'pay_rate')
                                                        @if($value !== null && $value !== '')
                                                            {{ number_format((float) $value, 2) }}
                                                        @endif

                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </td>
                                            @endforeach

                                            <td>
                                                <a href="{{ route('edit.employee', str_pad($employee->staffid, 3, '0', STR_PAD_LEFT)) }}"
                                                   class="btn btn-primary btn-sm waves-light waves-effect">
                                                    Edit
                                                    <i class="mdi mdi-dots-vertical ms-1"></i>
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

    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function () {

    // =========================================================
    // Employee column settings from controller
    // =========================================================
    @php
        $availableColumnKeysJson = json_encode(array_keys($availableColumns));

        $defaultColumnKeysJson = json_encode([
            'staffid',
            'stafflastname',
            'stafffirstname',
            'staffcell',
            'employee_type',
            'pay_period',
            'pay_rate_type',
            'pay_rate',
            'trans_flag'
        ]);

        $selectedColumnKeysJson = json_encode($selectedColumns);
    @endphp

    const availableColumnKeys = {!! $availableColumnKeysJson !!};
    const defaultColumnKeys = {!! $defaultColumnKeysJson !!};
    let selectedColumnKeys = {!! $selectedColumnKeysJson !!};

    // Capture original table-column positions before DataTables changes the DOM.
    const columnIndexMap = {};

    $('#employees-table thead th').each(function (index) {
        const columnKey = $(this).attr('data-column-key');

        if (columnKey) {
            columnIndexMap[columnKey] = index;
        }
    });

    const employeesTable = $('#employees-table').DataTable({
        pageLength: 25,
        lengthMenu: [
            [25, 50, 100, -1],
            [25, 50, 100, 'All']
        ],
        order: [],
        autoWidth: false,
        scrollX: true,
        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false
            },
            {
                targets: 1,
                orderable: false,
                searchable: false
            },
            {
                targets: -1,
                orderable: false,
                searchable: false
            }
        ]
    });

    function applyEmployeeColumnVisibility(columns) {
        availableColumnKeys.forEach(function (columnKey) {
            const columnIndex = columnIndexMap[columnKey];

            if (typeof columnIndex !== 'undefined') {
                employeesTable
                    .column(columnIndex)
                    .visible(columns.includes(columnKey), false);
            }
        });

        // Sr#, Photo and Action always remain visible.
        employeesTable.column(0).visible(true, false);
        employeesTable.column(1).visible(true, false);
        employeesTable.column(-1).visible(true, false);

        employeesTable.columns.adjust().draw(false);
    }

    function saveEmployeeColumnPreferences(columns) {
        $.ajax({
            url: "{{ route('employees.table-preferences.save') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                columns: columns
            },
            success: function () {
                $('#employeeColumnSaveMessage')
                    .stop(true, true)
                    .fadeIn(200)
                    .delay(1200)
                    .fadeOut(400);
            },
            error: function (xhr) {
                const message = xhr.responseJSON?.message
                    || 'Unable to save column preferences.';

                toastr.error(message);
            }
        });
    }
    function getCheckedEmployeeColumns() {
        return $('.employee-column-checkbox:checked')
            .map(function () {
                return this.value;
            })
            .get();
    }

    function syncEmployeeColumnCheckboxes(columns) {
        $('.employee-column-checkbox').each(function () {
            $(this).prop('checked', columns.includes(this.value));
        });
    }

    // Restore saved user selection when page loads.
    syncEmployeeColumnCheckboxes(selectedColumnKeys);
    applyEmployeeColumnVisibility(selectedColumnKeys);

    $(document).on('change', '.employee-column-checkbox', function () {
        selectedColumnKeys = getCheckedEmployeeColumns();
        applyEmployeeColumnVisibility(selectedColumnKeys);
        saveEmployeeColumnPreferences(selectedColumnKeys);
    });

    $('#selectAllEmployeeColumns').on('click', function () {
        $('.employee-column-checkbox').prop('checked', true);
        selectedColumnKeys = getCheckedEmployeeColumns();
        applyEmployeeColumnVisibility(selectedColumnKeys);
        saveEmployeeColumnPreferences(selectedColumnKeys);
    });

    $('#resetEmployeeColumns').on('click', function () {
        selectedColumnKeys = [...defaultColumnKeys];
        syncEmployeeColumnCheckboxes(selectedColumnKeys);
        applyEmployeeColumnVisibility(selectedColumnKeys);
        saveEmployeeColumnPreferences(selectedColumnKeys);
    });

});
</script>
@endsection
