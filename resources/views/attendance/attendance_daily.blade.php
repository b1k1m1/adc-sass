@extends('admin.admin_dashboard')
@section('admin')
@inject('carbon', 'Carbon\Carbon')

@php
    $type = $type ?? request('type', 'A');
    $type = in_array(strtoupper($type), ['A', 'N'], true) ? strtoupper($type) : 'A';

    $pageTitle = $type === 'A'
        ? 'Morning Shift Attendance'
        : 'Noon Shift Attendance';

    $oppositeType = $oppositeType ?? ($type === 'A' ? 'N' : 'A');

    $oppositeShiftName = $oppositeType === 'A'
        ? 'Morning Shift'
        : 'Noon Shift';
@endphp

<style>
    input[type="checkbox"] {
        height: 25px;
        width: 25px;
        accent-color: gray;
    }

    .attendance-count-box {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        white-space: nowrap;
    }

    /*
     * Opposite shift attendance warning.
     * Yellow cell/row means this member/date already has attendance in the other shift.
     */
    .other-shift-row td {
        background-color: #fff3cd !important;
    }

    .other-shift-cell {
        background-color: #fff3cd !important;
        border: 2px solid #ffc107 !important;
        text-align: center;
        vertical-align: middle;
    }

    .other-shift-warning {
        font-size: 11px;
        color: #856404;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 3px;
    }

    .other-shift-warning i {
        color: #dc3545;
        margin-right: 3px;
    }

    .other-shift-checkbox {
        cursor: not-allowed;
        opacity: 0.75;
    }

    .other-shift-legend {
        font-size: 13px;
        background: #fff3cd;
        border: 1px solid #ffc107;
        color: #856404;
        padding: 8px 12px;
        border-radius: 5px;
        margin-bottom: 12px;
        display: inline-block;
    }

    .form-check {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
    }

    #attendance_daily_table th,
    #attendance_daily_table td {
        white-space: nowrap;
        vertical-align: middle;
    }


        /* Compact attendance table */
    #attendance_daily_table {
        font-size: 13px;
    }

    #attendance_daily_table th,
    #attendance_daily_table td {
        padding: 4px 8px !important;
        white-space: nowrap;
        vertical-align: middle;
    }

    #attendance_daily_table input[type="checkbox"] {
        height: 18px;
        width: 18px;
    }

    /* Reduce checkbox wrapper height */
    #attendance_daily_table .form-check {
        min-height: 20px;
        margin-bottom: 0;
        padding-left: 0;
    }

    /* Compact warning inside locked other-shift cell */
    #attendance_daily_table .other-shift-warning {
        font-size: 10px;
        margin-bottom: 1px;
    }

    /* Make DataTables controls compact */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        font-size: 13px;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        height: 30px;
        padding: 3px 6px;
    }

        .sticky-save-bar {
        position: sticky;
        bottom: 0;
        background: #fff;
        padding: 10px 0;
        border-top: 1px solid #ddd;
        z-index: 20;
    }
</style>

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">
                        {{ $pageTitle }}

                        <span class="badge {{ $type === 'A' ? 'bg-primary' : 'bg-secondary' }} ms-2">
                            {{ $type === 'A' ? 'Morning Shift' : 'Noon Shift' }}
                        </span>
                    </h4>

                    <div class="d-flex gap-2">
                        <a href="{{ route('attendance.index', ['type' => 'A', 'date' => \Carbon\Carbon::parse($date)->format('m/d/Y')]) }}"
                           class="btn {{ $type === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
                            Morning Shift
                        </a>

                        <a href="{{ route('attendance.index', ['type' => 'N', 'date' => \Carbon\Carbon::parse($date)->format('m/d/Y')]) }}"
                           class="btn {{ $type === 'N' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                            Noon Shift
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(session('message'))
            <div class="alert alert-{{ session('alert-type', 'success') }}">
                {{ session('message') }}
            </div>
        @endif

        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">

                    <form method="GET" action="{{ route('attendance.index', ['type' => $type]) }}" class="mb-4">
                        <input type="hidden" name="type" value="{{ $type }}">

                        <div class="form-group row align-items-center">
                            <div class="col-md-1">
                                <label for="attendanceDate" class="col-form-label font-size-18">
                                    Select Date:
                                </label>
                            </div>

                            <div class="col-md-9 d-flex align-items-center flex-wrap gap-2">
                                <input type="text"
                                       id="attendanceDate"
                                       name="date"
                                       placeholder="mm/dd/yyyy"
                                       value="{{ old('date', $date ? \Carbon\Carbon::parse($date)->format('m/d/Y') : '') }}"
                                       pattern="(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])\/(19|20)\d{2}"
                                       title="Please enter a date in mm/dd/yyyy format"
                                       required
                                       class="form-control"
                                       style="max-width: 150px;">

                                <button type="submit" class="btn btn-primary">
                                    Load
                                </button>

                                <input type="checkbox" id="checkDefaultmain" title="Select all">

                                <label for="checkDefaultmain" class="font-size-18 mb-0">
                                    Select All
                                </label>

                                <span class="attendance-count-box ms-2">
                                    Checked:
                                    <span id="dailyCheckedCount">0</span>
                                </span>
                            </div>
                        </div>
                    </form>
                    {{-- Add Temporary Member Form --}}
                    @if(!empty($type))
                        <form method="GET"
                            action="{{ route('attendance.index', ['type' => $type]) }}"
                            class="mb-3 d-flex align-items-center flex-wrap gap-2">

                            <input type="hidden" name="date" value="{{ \Carbon\Carbon::parse($date)->format('m/d/Y') }}">
                            <input type="hidden" name="type" value="{{ $type }}">


                            @foreach(($tempMemberIds ?? []) as $tempId)
                                <input type="hidden" name="temp_members[]" value="{{ $tempId }}">
                            @endforeach

                            <label class="fw-bold mb-0">
                                Add Temporary Member:
                            </label>

                            <select name="temp_member"
                                    class="form-select"
                                    style="max-width: 360px;"
                                    required>
                                <option value="">
                                    -- Select Member From {{ $type == 'A' ? 'Noon' : 'Morning' }} Shift --
                                </option>

                                @foreach(($temporaryMembers ?? []) as $member)
                                    @php
                                        $memberId = str_pad(trim($member->ParticipantId), 3, '0', STR_PAD_LEFT);
                                    @endphp

                                    <option value="{{ $memberId }}">
                                        {{ $memberId }} - {{ $member->LastName }}, {{ $member->FirstName }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-warning">
                                Add to This Date
                            </button>
                        </form>
                    @endif

                    @if(isset($members))

                        <div class="other-shift-legend">
                            <i class="fas fa-exclamation-triangle"></i>
                            Yellow rows/cells mean attendance already exists in
                            <strong>{{ $oppositeShiftName }}</strong>.
                            These boxes are locked to avoid accidental overwrite.
                        </div>

                        <form method="POST" action="{{ route('attendance.save') }}">
                            @csrf

                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="type" value="{{ $type }}">

                            <table id="attendance_daily_table" class="table table-bordered table-striped nowrap w-100">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Participant ID</th>
                                        <th>Last Name</th>
                                        <th>First Name</th>
                                        <th>PA Expiry Date</th>
                                        <th class="text-center">Attendance</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($members as $member)

                                        @php
                                            $paddedId = str_pad(trim((string) $member->ParticipantId), 3, '0', STR_PAD_LEFT);

                                            $isChecked = ($existingAttendance[$paddedId] ?? 'N') === 'Y';

                                            /*
                                             * Check if this participant already has Y attendance
                                             * in the opposite shift for this selected date.
                                             */
                                            $alreadyInOtherShift = isset($otherShiftAttendance[$paddedId]);

                                            $medicaidEnd = $member->medicaid_PA_End;

                                            $carbonFormattedDate = $medicaidEnd ? \Carbon\Carbon::parse($medicaidEnd) : null;
                                            $carbonFrmDate = $date ? \Carbon\Carbon::parse($date) : null;
                                            $textColor = null;

                                            if ($carbonFormattedDate && $carbonFrmDate) {
                                                $textColor = $carbonFormattedDate->lt($carbonFrmDate) ? 'red' : 'green';
                                            }
                                        @endphp

                                        <tr class="
                                            {{ $isChecked ? 'checked' : '' }}
                                            {{ $alreadyInOtherShift ? 'other-shift-row' : '' }}
                                        ">
                                            <td>{{ $paddedId }}</td>
                                            <td>{{ $member->LastName }}</td>
                                            <td>{{ $member->FirstName }}</td>

                                            <td style="color: {{ $textColor }}">
                                                {{ $carbonFormattedDate ? $carbonFormattedDate->format('m/d/Y') : '' }}
                                            </td>

                                            <td class="{{ $alreadyInOtherShift ? 'other-shift-cell' : 'text-center' }}"
                                                title="{{ $alreadyInOtherShift ? 'Already marked present in ' . $oppositeShiftName : '' }}">

                                                <input type="hidden"
                                                       name="AttendanceYN[{{ $paddedId }}]"
                                                       value="N">

                                                @if($alreadyInOtherShift)

                                                    <div class="other-shift-warning">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Other Shift
                                                    </div>

                                                    <div class="form-check">
                                                        <input type="checkbox"
                                                               class="form-check-input attendanceCheckbox attendance-toggle other-shift-checkbox"
                                                               value="Y"
                                                               checked
                                                               disabled>
                                                    </div>

                                                @else

                                                    <div class="form-check">
                                                        <input type="checkbox"
                                                               class="form-check-input attendanceCheckbox attendance-toggle"
                                                               name="AttendanceYN[{{ $paddedId }}]"
                                                               value="Y"
                                                               {{ $isChecked ? 'checked' : '' }}>
                                                    </div>

                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="sticky-save-bar">
                                <button type="submit" class="btn btn-primary">
                                    Save Attendance
                                </button>
                            </div>
                        </form>
                    @endif

                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {

        let attendanceTable = null;

        if ($('#attendance_daily_table').length) {
            attendanceTable = $('#attendance_daily_table').DataTable({
                paging: true,

                // Default page size
                pageLength: 100,

                // Show dropdown
                lengthChange: true,

                // User can select 100, 200, 300, 400, or All
                lengthMenu: [
                    [100, 200, 300, 400, -1],
                    [100, 200, 300, 400, "All"]
                ],

                searching: true,
                ordering: true,
                info: true,
                scrollX: true,
                order: [[1, 'asc'], [2, 'asc']]
            });
        }

        /*
         * Helper: get all editable attendance checkboxes from all DataTable pages,
         * not only the visible/current page.
         */
        function getEditableCheckboxes() {
            if (attendanceTable) {
                return attendanceTable.$('.attendanceCheckbox').not(':disabled');
            }

            return $('.attendanceCheckbox').not(':disabled');
        }

        /*
         * Count all checked editable/current-shift checkboxes from all pages.
         * Locked other-shift checkboxes are excluded.
         */
        function updateDailyCheckedCount() {
            let checkedCount = getEditableCheckboxes().filter(':checked').length;
            $('#dailyCheckedCount').text(checkedCount);
        }

        /*
         * Update Select All checkbox based on all pages.
         */
        function updateSelectAllStatus() {
            let totalCheckboxes = getEditableCheckboxes().length;
            let checkedCheckboxes = getEditableCheckboxes().filter(':checked').length;

            $('#checkDefaultmain').prop(
                'checked',
                totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes
            );
        }

        /*
         * Select All should affect all editable/current-shift checkboxes
         * across all pages, not only the current page.
         */
        $('#checkDefaultmain').on('change', function() {
            let checked = $(this).is(':checked');

            getEditableCheckboxes().prop('checked', checked);

            updateDailyCheckedCount();
            updateSelectAllStatus();
        });

        /*
         * Checkbox change event.
         * Because DataTables redraws pages, use delegated event.
         */
        $(document).on('change', '.attendanceCheckbox', function () {
            updateDailyCheckedCount();
            updateSelectAllStatus();
        });

        /*
         * Recalculate when user changes page, search, sort, or page length.
         */
        if (attendanceTable) {
            attendanceTable.on('draw', function () {
                updateDailyCheckedCount();
                updateSelectAllStatus();
            });
        }

        updateDailyCheckedCount();
        updateSelectAllStatus();

    });
</script>
@endsection
