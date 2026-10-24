@extends('admin.admin_dashboard')
@section('admin')

<style>
    .column-selector-panel {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        background: #f8f9fa;
        padding: 14px 16px;
    }

    .column-checkbox-grid {
        max-height: 230px;
        overflow-y: auto;
        padding-right: 6px;
    }

    .column-checkbox-item {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 8px 10px;
        height: 100%;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .column-checkbox-item:hover {
        border-color: #86b7fe;
        box-shadow: 0 2px 8px rgba(13, 110, 253, .08);
    }

    .column-checkbox-item .form-check {
        margin-bottom: 0;
    }

    .column-checkbox-item .form-check-label {
        cursor: pointer;
        width: 100%;
        font-weight: 500;
    }

    #members-table td,
    #members-table th {
        vertical-align: middle;
    }

    .member-photo {
        width: 50px;
        height: 50px;
        object-fit: cover;
    }

    .column-selector-help {
        font-size: 12px;
    }

    .member-column-dropdown {
        width: 320px;
        max-height: 460px;
        overflow: hidden;
    }

    .member-column-list {
        max-height: 340px;
        overflow-y: auto;
        padding-right: 6px;
    }

    .member-column-list .form-check {
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        padding: 7px 10px 7px 34px;
        background: #ffffff;
    }

    .member-column-list .form-check:hover {
        background: #f8f9fa;
    }

    .member-column-list .form-check-label {
        width: 100%;
        cursor: pointer;
    }

    .member-toolbar-control,
    .member-toolbar-btn {
        height: 38px !important;
        min-height: 38px !important;
    }

    .member-toolbar-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding-top: 0;
        padding-bottom: 0;
    }

    #memberColumnDropdown {
        height: 38px !important;
        min-height: 38px !important;
        display: flex;
        align-items: center;
    }


    @media (max-width: 768px) {
        .member-column-dropdown {
            width: 290px;
        }
    }

</style>

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Members List</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('members.dashboard') }}"
                               class="btn btn-primary waves-effect waves-light">
                                {{ t('common.back', 'Back') }}
                            </a>
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

                        <div class="row align-items-end mb-3 g-2">

                            {{-- Status Filter --}}
                            <div class="col-md-4 col-lg-3">
                                <form method="GET"
                                      action="{{ url()->current() }}"
                                      id="memberStatusForm">

                                    <label for="member_status_filter"
                                           class="form-label fw-semibold">
                                        Select Status
                                    </label>

                                    <div class="d-flex gap-2">
                                        <select name="member_status_filter"
                                                id="member_status_filter"
                                                class="form-select member-toolbar-control">

                                            <option value=""
                                                {{ ($selectedStatus ?? 'A') === '' ? 'selected' : '' }}>
                                                All Status
                                            </option>

                                            @foreach($statuses as $status)
                                                <option value="{{ $status->Id }}"
                                                    {{ ($selectedStatus ?? 'A') == $status->Id ? 'selected' : '' }}>
                                                    {{ $status->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit"
                                                class="btn btn-primary member-toolbar-btn">
                                            <i class="fas fa-search me-1"></i>
                                            Load
                                        </button>

                                        <a href="{{ url()->current() }}"
                                           class="btn btn-secondary member-toolbar-btn">
                                            Clear
                                        </a>
                                    </div>
                                </form>
                            </div>

                            {{-- Column Dropdown --}}
                            <div class="col-md-4 col-lg-3">
                                <label class="form-label fw-semibold">Columns</label>

                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle w-100 text-start member-toolbar-btn"
                                            type="button"
                                            id="memberColumnDropdown"
                                            data-bs-toggle="dropdown"
                                            data-bs-auto-close="outside"
                                            aria-expanded="false">
                                        <i class="fas fa-columns me-1"></i>
                                        Choose Columns
                                    </button>

                                    <div class="dropdown-menu p-3 shadow member-column-dropdown"
                                         aria-labelledby="memberColumnDropdown">

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong>Select Columns</strong>

                                            <span id="columnSaveMessage"
                                                  class="text-success small"
                                                  style="display:none;">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Saved
                                            </span>
                                        </div>

                                        <div class="member-column-list">
                                            @foreach($availableColumns as $field => $label)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input member-column-checkbox"
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
                                                    id="selectAllMemberColumns">
                                                <i class="fas fa-check-double me-1"></i>
                                                Select All
                                            </button>

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    id="resetMemberColumns">
                                                <i class="fas fa-undo me-1"></i>
                                                Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- PDF Export and Browser Print --}}
                            <div class="col-md-4 col-lg-4">
                                <label class="form-label fw-semibold">Report</label>

                                <div class="d-flex gap-2">
                                    <form id="pdfExportForm"
                                        action="{{ route('members.export.pdf') }}"
                                        method="GET"
                                        target="_blank">

                                        <input type="hidden"
                                            name="status"
                                            id="pdfStatusInput"
                                            value="{{ $selectedStatus }}">

                                        <div id="pdfColumnInputs"></div>

                                        <button type="submit"
                                                class="btn btn-danger member-toolbar-btn">
                                            <i class="fas fa-file-pdf me-1"></i>
                                            Export to PDF
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>

                        <div class="table-responsive">
                            <table id="members-table"
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

                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($members as $key => $member)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>

                                            @php
                                                $mediaFolder = session('media_folder');

                                                if (empty($mediaFolder)) {
                                                    abort(403, 'Organization media folder is not configured.');
                                                }

                                                $numericId = (int) trim($member->ParticipantId);
                                                $imageFilename = $numericId . '.jpg';
                                                $imagePath = "uploads/{$mediaFolder}/members/images/{$imageFilename}";
                                                $fullImagePath = public_path($imagePath);

                                                $displayPath = file_exists($fullImagePath)
                                                    ? asset($imagePath)
                                                    : asset('members/images/no_image.jpg');
                                            @endphp

                                            <td>
                                                <img src="{{ $displayPath }}"
                                                     alt="Member Photo"
                                                     class="img-thumbnail rounded-circle member-photo">
                                            </td>

                                            @foreach($availableColumns as $field => $label)
                                                @php
                                                    $value = $member->{$field} ?? null;
                                                @endphp

                                                <td>
                                                    @if($field === 'ParticipantId')
                                                        {{ str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT) }}

                                                    @elseif($field === 'Trans_flag')
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

                                                    @elseif(in_array($field, [
                                                        'DateOfBirth',
                                                        'AdmissionDate',
                                                        'DischargeDate',
                                                        'medicaid_PA_Start',
                                                        'medicaid_PA_End',
                                                        'HNP_Expiry_date'
                                                    ], true))
                                                        @if(!empty($value))
                                                            {{ \Carbon\Carbon::parse($value)->format('m/d/Y') }}
                                                        @endif

                                                    @elseif(in_array($field, ['Phone1', 'Phone2'], true))
                                                        @php
                                                            $phone = preg_replace('/\D/', '', $value ?? '');
                                                        @endphp

                                                        @if(strlen($phone) === 10)
                                                            {{ '(' . substr($phone, 0, 3) . ')' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4) }}
                                                        @else
                                                            {{ $value }}
                                                        @endif

                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </td>
                                            @endforeach

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
<!-- End Page-content -->

@endsection

@section('scripts')
<script>


$(document).ready(function () {

    // =========================================================
    // Member column settings from controller
    // =========================================================
    @php
        $availableColumnKeysJson = json_encode(array_keys($availableColumns));
        $defaultColumnKeysJson = json_encode($defaultColumns);
        $selectedColumnKeysJson = json_encode($selectedColumns);
    @endphp

    const availableColumnKeys = {!! $availableColumnKeysJson !!};
    const defaultColumnKeys = {!! $defaultColumnKeysJson !!};
    let selectedColumnKeys = {!! $selectedColumnKeysJson !!};

    // Capture the original table-column positions before DataTables changes the DOM.
    const columnIndexMap = {};

    $('#members-table thead th').each(function (index) {
        const columnKey = $(this).attr('data-column-key');

        if (columnKey) {
            columnIndexMap[columnKey] = index;
        }
    });

    const membersTable = $('#members-table').DataTable({
        pageLength: 200,
        lengthMenu: [
            [100, 200, 300, 500, -1],
            [100, 200, 300, 500, 'All']
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
            }
        ]
    });

    function applyMemberColumnVisibility(columns) {
        availableColumnKeys.forEach(function (columnKey) {
            const columnIndex = columnIndexMap[columnKey];

            if (typeof columnIndex !== 'undefined') {
                membersTable
                    .column(columnIndex)
                    .visible(columns.includes(columnKey), false);
            }
        });

        // Sr# and Photo always remain visible.
        membersTable.column(0).visible(true, false);
        membersTable.column(1).visible(true, false);

        membersTable.columns.adjust().draw(false);
    }

    function saveMemberColumnPreferences(columns) {
        $.ajax({
            url: "{{ route('members.table-preferences.save') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                table_name: 'members_list_all',
                columns: columns
            },
            success: function () {
                $('#columnSaveMessage')
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

    function getCheckedMemberColumns() {
        return $('.member-column-checkbox:checked')
            .map(function () {
                return this.value;
            })
            .get();
    }

    function syncColumnCheckboxes(columns) {
        $('.member-column-checkbox').each(function () {
            $(this).prop('checked', columns.includes(this.value));
        });
    }

    // Restore the saved user selection when the page loads.
    syncColumnCheckboxes(selectedColumnKeys);
    applyMemberColumnVisibility(selectedColumnKeys);

    // Delegated handler keeps working inside the Bootstrap dropdown.
    $(document).on('change', '.member-column-checkbox', function () {
        selectedColumnKeys = getCheckedMemberColumns();

        if (selectedColumnKeys.length === 0) {
            toastr.warning('At least one member column must remain selected.');
            $(this).prop('checked', true);
            selectedColumnKeys = getCheckedMemberColumns();
            return;
        }

        applyMemberColumnVisibility(selectedColumnKeys);
        updatePdfColumnInputs();
        saveMemberColumnPreferences(selectedColumnKeys);
    });

    $('#selectAllMemberColumns').on('click', function () {
        $('.member-column-checkbox').prop('checked', true);
        selectedColumnKeys = getCheckedMemberColumns();
        applyMemberColumnVisibility(selectedColumnKeys);
        updatePdfColumnInputs();
        saveMemberColumnPreferences(selectedColumnKeys);
    });

    $('#resetMemberColumns').on('click', function () {
        selectedColumnKeys = [...defaultColumnKeys];
        syncColumnCheckboxes(selectedColumnKeys);
        applyMemberColumnVisibility(selectedColumnKeys);
        updatePdfColumnInputs();
        saveMemberColumnPreferences(selectedColumnKeys);
    });

    // =========================================================
    // Keep PDF request synchronized with current column selection
    // =========================================================
    function updatePdfColumnInputs() {
        const $container = $('#pdfColumnInputs');

        $container.empty();

        selectedColumnKeys.forEach(function (columnKey) {
            $('<input>', {
                type: 'hidden',
                name: 'columns[]',
                value: columnKey
            }).appendTo($container);
        });

        $('#pdfStatusInput').val(
            $('#member_status_filter').val()
        );
    }

    // Initial PDF values.
    updatePdfColumnInputs();

    $('#pdfExportForm').on('submit', function (event) {
        if (selectedColumnKeys.length === 0) {
            event.preventDefault();
            toastr.warning('Please select at least one report column.');
            return;
        }

        updatePdfColumnInputs();
    });

    // Keep PDF inputs current when the status dropdown changes.
    $('#member_status_filter').on('change', function () {
        updatePdfColumnInputs();
    });


    // Close document.ready
    });


 </script>
@endsection
