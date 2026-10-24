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
                    <h4 class="mb-sm-0 font-size-18">All Members List</h4>

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

                                        <th data-column-key="action">Action</th>
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
                                                        <a href="{{ route('edit.member', str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT)) }}">
                                                            {{ str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT) }}
                                                        </a>

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

                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('edit.member', str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT)) }}"
                                                       class="btn btn-primary btn-sm waves-light waves-effect">
                                                        Edit
                                                        <i class="mdi mdi-dots-vertical ms-1"></i>
                                                    </a>

                                                    <button type="button"
                                                            class="btn btn-info btn-sm waves-light waves-effect edit-medical-btn"
                                                            data-id="{{ str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT) }}">
                                                        Update PA Info
                                                        <i class="mdi mdi-dots-vertical ms-1"></i>
                                                    </button>
                                                </div>
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
<!-- End Page-content -->
<!-- Add PA update Modal -->
<div class="modal fade" id="updatePAModal" tabindex="-1" aria-labelledby="updatePAModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="updatePAForm">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addCountryModalLabel">Add/Update PA & HNP information</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="country_name">ID#</label>
              <input type="text" name="ParticipantId" id="ParticipantId" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label for="country_name">Last Name</label>
                <input type="text" name="LastName" id="LastName" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="country_name">First Name</label>
                <input type="text" name="FirstName" id="FirstName" class="form-control" required>
              </div>
              <div class="form-group">
                <select id="HMO_Enrolled" name="HMO_Enrolled" class="form-select w-50 mt-2">
                        <option value="">-- Select Insurance -- </option>
                </select>
              </div>
              <div class="form-group">
                <label for="country_name">Insurance ID#</label>
                <input type="text" name="medicaid_HMO_id" id="medicaid_HMO_id" class="form-control" required>
              </div>
              <div class="form-group mt-2">
                <label class="form-label">Primary Diagnosis (ICD-10)</label>
                <select id="PrimaryDiagnosis1_modal" name="PrimaryDiagnosis1" class="form-control icd10-select"></select>
              </div>

              <div class="form-group mt-2">
                <label class="form-label">Secondary Diagnosis (ICD-10)</label>
                <select id="SecondaryDiag1_modal" name="SecondaryDiag1" class="form-control icd10-select"></select>
              </div>
              <div class="form-group">
                <label for="country_name">PA Number</label>
                <input type="text" name="medicaid_PA_no" id="medicaid_PA_no" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="country_name">PA Start Date</label>
                <input type="text" name="medicaid_PA_Start" id="medicaid_PA_Start" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="country_name">PA End Date</label>
                <input type="text" name="medicaid_PA_End" id="medicaid_PA_End" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="HNP_Expiry_date">HNP Expiry Date</label>
                <input type="text" name="HNP_Expiry_date" id="HNP_Expiry_date" class="form-control" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save PA Info</button>
          </div>
        </div>
      </form>
    </div>
  </div>

@endsection

@section('scripts')
<script>


$(document).ready(function () {

    // =========================================================
    // Member column settings from controller
    // =========================================================
    @php
        $availableColumnKeysJson = json_encode(array_keys($availableColumns));
        $defaultColumnKeysJson = json_encode([
            'ParticipantId',
            'LastName',
            'FirstName',
            'Address1',
            'City',
            'ZipCode',
            'Trans_flag'
        ]);
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
            },
            {
                targets: -1,
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

        // Sr#, Photo and Action always remain visible.
        membersTable.column(0).visible(true, false);
        membersTable.column(1).visible(true, false);
        membersTable.column(-1).visible(true, false);

        membersTable.columns.adjust().draw(false);
    }

    function saveMemberColumnPreferences(columns) {
        $.ajax({
            url: "{{ route('members.table-preferences.save') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                table_name: 'members',
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
        applyMemberColumnVisibility(selectedColumnKeys);
        saveMemberColumnPreferences(selectedColumnKeys);
    });

    $('#selectAllMemberColumns').on('click', function () {
        $('.member-column-checkbox').prop('checked', true);
        selectedColumnKeys = getCheckedMemberColumns();
        applyMemberColumnVisibility(selectedColumnKeys);
        saveMemberColumnPreferences(selectedColumnKeys);
    });

    $('#resetMemberColumns').on('click', function () {
        selectedColumnKeys = [...defaultColumnKeys];
        syncColumnCheckboxes(selectedColumnKeys);
        applyMemberColumnVisibility(selectedColumnKeys);
        saveMemberColumnPreferences(selectedColumnKeys);
    });

    // Auto-open PA modal if URL has ?open=pa&id=005
    const url = new URL(window.location.href);
    const open = (url.searchParams.get('open') || '').toLowerCase();
    const id   = (url.searchParams.get('id') || '').trim();

    if (open === 'pa' && id) {
        // small delay so bootstrap + datatable fully ready
        setTimeout(() => openPAModal(id), 250);
    }

});


    // Show modal and fetch member info
    /*
    $(document).on('click', '.edit-medical-btn', function () {
        const memberId = $(this).data('id');

        $('#updatePAForm')[0].reset();

        //console.log('Clicked member ID:', memberId);

        // clear select2 (if previously opened)
        $('#PrimaryDiagnosis1_modal').empty().trigger('change');
        $('#SecondaryDiag1_modal').empty().trigger('change');

        $.get('/members/' + memberId + '/medical-info', function (data) {

            //console.log('data.member:', data.member);

           const hmoOptions = [
                     "HORIZON NJ HEALTH",
                     "UNITED HEALTH CARE",
                     "WELLCARE",
                     "AMERI GROUP NJ",
                     "AETNA BETTER HEALTH",
                     "HEALTH FIRST",
                     "PRIVATE PAY",
                    "OTHERS"
                ];

            const rawDatePAStart = data.medicaid_PA_Start; // e.g., '2011-02-07 00:00:00'
            const rawDatePAEnd = data.medicaid_PA_End; // e.g., '2011-02-07 00:00:00'

            $('#ParticipantId').val(data.ParticipantId);
            $('#LastName').val(data.LastName);
            $('#FirstName').val(data.FirstName);

            const selectedHMO = data.HMO_Enrolled ? data.HMO_Enrolled.trim() : '';

            $('#HMO_Enrolled').empty().append('<option value="">-- Select Insurance--</option>');

            hmoOptions.forEach(function (option) {
            const selected = (option === selectedHMO) ? 'selected' : '';
            $('#HMO_Enrolled').append(`<option value="${option}" ${selected}>${option}</option>`);
            });

            $('#medicaid_HMO_id').val(data.medicaid_HMO_id);
            $('#medicaid_PA_no').val(data.medicaid_PA_no);

            //
            if (rawDatePAStart) {
                const date = new Date(rawDatePAStart);
                const formattedPAStart = `${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getDate().toString().padStart(2, '0')}/${date.getFullYear()}`;
            $('#medicaid_PA_Start').val(formattedPAStart);
            }
            //$('#medicaid_PA_Start').val(data.medicaid_PA_Start);

            if (rawDatePAEnd) {
                const date = new Date(rawDatePAEnd);
                const formattedPAEnd = `${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getDate().toString().padStart(2, '0')}/${date.getFullYear()}`;
            $('#medicaid_PA_End').val(formattedPAEnd);
            }
            //$('#medicaid_PA_End').val(data.medicaid_PA_End);

            //$('#medications').val(data.medications);
            $('#updatePAModal').modal('show');

             // after modal is fully visible, init select2 + set selected values
        $('#updatePAModal').one('shown.bs.modal', function () {

            initIcd10InModal('#PrimaryDiagnosis1_modal');
            initIcd10InModal('#SecondaryDiag1_modal');

            // now set saved ICD10 values
            setIcd10Value($('#PrimaryDiagnosis1_modal'), data.PrimaryDiagnosis1);
            setIcd10Value($('#SecondaryDiag1_modal'), data.SecondaryDiag1);
        });

        });
    });
    */

    function openPAModal(memberId){
        memberId = (memberId || '').toString().trim();
        if(!memberId) return;

        $('#updatePAForm')[0].reset();

        // clear select2 (if previously opened)
        $('#PrimaryDiagnosis1_modal').empty().trigger('change');
        $('#SecondaryDiag1_modal').empty().trigger('change');

        $.get('/members/' + encodeURIComponent(memberId) + '/medical-info', function (data) {

            const hmoOptions = [
                "HORIZON NJ HEALTH",
                "UNITED HEALTH CARE",
                "WELLCARE",
                "AMERI GROUP NJ",
                "AETNA BETTER HEALTH",
                "HEALTH FIRST",
                "PRIVATE PAY",
                "OTHERS"
            ];

            const rawDatePAStart = data.medicaid_PA_Start;
            const rawDatePAEnd   = data.medicaid_PA_End;
            const rawDateHNP   = data.HNP_Expiry_date;


            $('#ParticipantId').val(data.ParticipantId);
            $('#LastName').val(data.LastName);
            $('#FirstName').val(data.FirstName);

            const selectedHMO = data.HMO_Enrolled ? data.HMO_Enrolled.trim() : '';
            $('#HMO_Enrolled').empty().append('<option value="">-- Select Insurance--</option>');
            hmoOptions.forEach(function (option) {
                const selected = (option === selectedHMO) ? 'selected' : '';
                $('#HMO_Enrolled').append(`<option value="${option}" ${selected}>${option}</option>`);
            });

            $('#medicaid_HMO_id').val(data.medicaid_HMO_id);
            $('#medicaid_PA_no').val(data.medicaid_PA_no);

            if (rawDatePAStart) {
                const date = new Date(rawDatePAStart);
                const formatted = `${(date.getMonth()+1).toString().padStart(2,'0')}/${date.getDate().toString().padStart(2,'0')}/${date.getFullYear()}`;
                $('#medicaid_PA_Start').val(formatted);
            }

            if (rawDatePAEnd) {
                const date = new Date(rawDatePAEnd);
                const formatted = `${(date.getMonth()+1).toString().padStart(2,'0')}/${date.getDate().toString().padStart(2,'0')}/${date.getFullYear()}`;
                $('#medicaid_PA_End').val(formatted);
            }

            if (rawDateHNP) {
                const date = new Date(rawDateHNP);
                const formatted = `${(date.getMonth()+1).toString().padStart(2,'0')}/${date.getDate().toString().padStart(2,'0')}/${date.getFullYear()}`;
                $('#HNP_Expiry_date').val(formatted);
            }else {
                $('#HNP_Expiry_date').val('');
            }

            $('#updatePAModal').modal('show');

            // after modal is fully visible, init select2 + set selected values
            $('#updatePAModal').one('shown.bs.modal', function () {
                initIcd10InModal('#PrimaryDiagnosis1_modal');
                initIcd10InModal('#SecondaryDiag1_modal');

                setIcd10Value($('#PrimaryDiagnosis1_modal'), data.PrimaryDiagnosis1);
                setIcd10Value($('#SecondaryDiag1_modal'), data.SecondaryDiag1);
            });
        })
        .fail(function(xhr){
            toastr.error('Unable to load PA info for ID ' + memberId);
        });
    }

// click button uses the same function
$(document).on('click', '.edit-medical-btn', function () {
    openPAModal($(this).data('id'));
});

    function setIcd10Value($select, code) {
    code = (code || '').toString().trim();

    if (!code) {
        $select.val(null).trigger('change');
        return;
    }

    // If already exists, select it
    if ($select.find("option[value='" + code.replace(/'/g, "\\'") + "']").length) {
        $select.val(code).trigger('change');
        return;
    }

    // Use your existing route: /lookup/icd10/{code}
    $.get("{{ url('/lookup/icd10') }}/" + encodeURIComponent(code), function (item) {
        const id   = item.id || item.code || code;
        const text = item.text || (item.code ? (item.code + ' - ' + (item.short_description || '')) : code);

        const opt = new Option(text, id, true, true);
        $select.append(opt).trigger('change');
    }).fail(function () {
        const opt = new Option(code, code, true, true);
        $select.append(opt).trigger('change');
    });
}

    // Submit medical info
    $('#updatePAForm').on('submit', function (e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.ajax({
        url: '/members/update-medical-info', // 👈 your route
        method: 'POST',
        data: formData,
        success: function (response) {
            $('#updatePAModal').modal('hide');
            toastr.success(response.message || 'Saved successfully!');

             // ✅ 1) BroadcastChannel (best)
            try {
                if ('BroadcastChannel' in window) {
                const bc = new BroadcastChannel('adc-chat');
                bc.postMessage({ type: 'refresh_chat' });
                bc.close();
                }
            } catch (e) {}

            // ✅ 2) Fallback to opener (if available)
            try {
                if (window.opener && !window.opener.closed) {
                window.opener.postMessage({ type: 'refresh_chat' }, window.location.origin);
                }
            } catch (e) {}
            },

        error: function (xhr) {
            const msg = xhr.responseJSON?.message || 'Something went wrong!';
            toastr.error(msg);
            }
        });
    });

    function initIcd10InModal(selector) {
    const $el = $(selector);
    const $modal = $('#updatePAModal');

    // if already initialized, destroy and re-init
    if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
    }

    $el.select2({
        width: '100%',
        placeholder: 'Type ICD-10 code or description...',
        allowClear: true,
        minimumInputLength: 2,
        dropdownParent: $modal, // ✅ REQUIRED
        ajax: {
        url: "{{ route('lookup.icd10') }}",
        dataType: 'json',
        delay: 300,
        data: function (params) {
            return { q: params.term || '', limit: 25 };
        },
        processResults: function (data) {
            // your endpoint already returns Select2 format
            return data;
        },
        cache: true
        }
    });
    }

 </script>
@endsection

