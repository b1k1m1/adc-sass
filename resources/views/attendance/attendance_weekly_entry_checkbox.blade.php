
@extends('admin.admin_dashboard')

@section('admin')

<style>
    .large-checkbox {
        transform: scale(2.0); /* if you want to increase size then make it 1.5 */
        margin: 5px;
    }

    #weeklyAttendanceTable th,
    #weeklyAttendanceTable td {
        white-space: nowrap;
        vertical-align: middle;
    }

    #weeklyAttendanceTable thead th {
        background: #ffffff;
        font-weight: 600;
    }

    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 10px;
    }

    .attendance-save-bar {
        margin-top: 15px;
        margin-bottom: 20px;
    }

    .shift-btn.active-shift {
        box-shadow: 0 3px 8px rgba(0,0,0,0.20);
    }

    /*
     * Opposite shift attendance warning.
     * Yellow cell means this member/date already has attendance in the other shift.
     */
    .other-shift-cell {
        background-color: #fff3cd !important;
        border: 2px solid #ffc107 !important;
    }

    .other-shift-warning {
        font-size: 10px;
        color: #856404;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 2px;
    }

    .other-shift-warning i {
        color: #dc3545;
        margin-right: 2px;
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
        margin-bottom: 10px;
        display: inline-block;
    }
</style>

<div class="page-content">
    <div class="container-fluid">

        <h2 class="mb-4">
            Weekly Attendance Entry

            @if(!empty($type))
                <span class="badge bg-primary ms-2">
                    {{ $type == 'A' ? 'Morning Shift' : 'Noon Shift' }}
                </span>
            @endif
        </h2>

        {{-- Load Week Form --}}
        <form method="GET"
              action="{{ route('attendance.weekly.entry_mark') }}"
              id="weeklyLoadForm"
              class="mb-4 d-flex align-items-center flex-wrap gap-2 w-100">

            <input type="hidden"
                   name="type"
                   id="selectedShift"
                   value="{{ $type ?? '' }}">

            <label class="me-2 mb-0 fw-bold">
                Select Week Start Date (Sunday):
            </label>

            <input type="date"
                   name="week_start_date"
                   id="week_start_date"
                   value="{{ $weekStartDate }}"
                   required
                   class="form-control"
                   style="max-width: 180px;">

            <button type="button"
                    id="morningShiftBtn"
                    class="btn shift-btn {{ ($type ?? '') == 'A' ? 'btn-primary active-shift' : 'btn-outline-primary' }}">
                Morning Shift
            </button>

            <button type="button"
                    id="noonShiftBtn"
                    class="btn shift-btn {{ ($type ?? '') == 'N' ? 'btn-primary active-shift' : 'btn-outline-primary' }}">
                Noon Shift
            </button>

            <button type="submit"
                    id="loadWeekBtn"
                    class="btn btn-primary"
                    {{ empty($type) ? 'disabled' : '' }}>
                Load Week
            </button>

            <a href="{{ route('attendance.dashboard') }}"
               class="btn btn-primary ms-auto">
                {{ t('common.back','Back') }}
            </a>
        </form>

        @if(empty($type))
            <div class="alert alert-warning py-2">
                Please select <strong>Morning Shift</strong> or <strong>Noon Shift</strong>, then click <strong>Load Week</strong>.
            </div>
        @endif

        {{-- Add Temporary Member Form --}}
        @if(!empty($type))
            <form method="GET"
                  action="{{ route('attendance.weekly.entry_mark') }}"
                  class="mb-3 d-flex align-items-center flex-wrap gap-2">

                <input type="hidden" name="week_start_date" value="{{ $weekStartDate }}">
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

                    @foreach($temporaryMembers as $member)
                        @php
                            $memberId = str_pad(trim($member->ParticipantId), 3, '0', STR_PAD_LEFT);
                        @endphp

                        <option value="{{ $memberId }}">
                            {{ $memberId }} - {{ $member->LastName }}, {{ $member->FirstName }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-warning">
                    Add to This Week
                </button>
            </form>
        @endif

        @if(!empty($type))

            <form method="POST" action="{{ route('attendance.weekly.save') }}">
                @csrf

                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="week_start_date" value="{{ $weekStartDate }}">

                @foreach(($tempMemberIds ?? []) as $tempId)
                    <input type="hidden" name="temp_members[]" value="{{ $tempId }}">
                @endforeach

                @php
                    /*
                     * Calculate daily attendance counts for each date.
                     * Count only the selected/current shift attendance.
                     * Opposite shift warning records are not included in this count.
                     */
                    $dailyCounts = [];

                    foreach ($weekDates as $date) {
                        $dailyCounts[$date] = 0;
                    }

                    foreach ($participants as $participant) {
                        $participantIdPadded = str_pad(trim($participant->ParticipantId), 3, '0', STR_PAD_LEFT);

                        foreach ($weekDates as $date) {
                            $attendanceData = isset($attendances[$participantIdPadded])
                                ? collect($attendances[$participantIdPadded])->firstWhere('CalenderDate', $date)
                                : null;

                            if ($attendanceData && $attendanceData->AttendanceYN == 'Y') {
                                $dailyCounts[$date]++;
                            }
                        }
                    }

                    $oppositeShiftName = ($oppositeType ?? '') == 'A'
                        ? 'Morning Shift'
                        : 'Noon Shift';
                @endphp

                <div class="other-shift-legend">
                    <i class="fas fa-exclamation-triangle"></i>
                    Yellow cells mean attendance already exists in
                    <strong>{{ $oppositeShiftName }}</strong>.
                    These boxes are locked to avoid accidental overwrite.
                </div>

                <div class="table-responsive">
                    <table id="weeklyAttendanceTable" class="table table-bordered table-striped nowrap w-100">
                        <thead>
                            <tr>
                                <th>Participant ID</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>PA Expiry Date</th>
                                <th class="text-center no-sort">
                                    Mon-Fri<br>
                                    <small>All</small>
                                </th>
                                @foreach($weekDates as $index => $date)
                                    <th class="text-center">
                                        {{ \Carbon\Carbon::parse($date)->format('D m/d') }}
                                        <input type="checkbox"
                                               class="select-day-checkbox"
                                               data-day-index="{{ $index }}">
                                        <br>
                                        <small>
                                            Count:
                                            <span class="day-count" id="day-count-{{ $index }}">
                                                {{ $dailyCounts[$date] ?? 0 }}
                                            </span>
                                        </small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($participants as $participant)

                                @php
                                    $participantIdPadded = str_pad(trim($participant->ParticipantId), 3, '0', STR_PAD_LEFT);

                                    $medicaidEnd = $participant->medicaid_PA_End;
                                    $medicaidDate = $medicaidEnd ? \Carbon\Carbon::parse($medicaidEnd) : null;

                                    $textColor = null;

                                    if ($medicaidDate && count($weekDates)) {
                                        $start = \Carbon\Carbon::parse($weekDates[0]);
                                        $end = \Carbon\Carbon::parse(end($weekDates));

                                        if ($medicaidDate->lt($start)) {
                                            $textColor = 'red';
                                        } elseif ($medicaidDate->between($start, $end)) {
                                            $textColor = 'blue';
                                        } elseif ($medicaidDate->gt($end)) {
                                            $textColor = 'green';
                                        }
                                    }
                                @endphp

                                <tr>
                                    <td>{{ $participantIdPadded }}</td>
                                    <td>{{ $participant->LastName }}</td>
                                    <td>{{ $participant->FirstName }}</td>
                                    <td style="color: {{ $textColor }}">
                                        {{ $medicaidDate ? $medicaidDate->format('m/d/Y') : '' }}
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox"
                                            class="large-checkbox select-row-weekdays-checkbox"
                                            title="Check / uncheck Monday to Friday for this member">
                                    </td>
                                    @foreach($weekDates as $index => $date)

                                        @php
                                            $attendanceData = isset($attendances[$participantIdPadded])
                                                ? collect($attendances[$participantIdPadded])->firstWhere('CalenderDate', $date)
                                                : null;

                                            /*
                                             * Check if this participant already has attendance
                                             * in the opposite shift for this same date.
                                             */
                                            $alreadyInOtherShift = isset($otherShiftAttendance[$participantIdPadded][$date]);

                                            $otherShiftName = ($oppositeType ?? '') == 'A'
                                                ? 'Morning Shift'
                                                : 'Noon Shift';
                                        @endphp

                                        <td class="text-center {{ $alreadyInOtherShift ? 'other-shift-cell' : '' }}"
                                            title="{{ $alreadyInOtherShift ? 'Already marked present in ' . $otherShiftName : '' }}">

                                            <input type="hidden"
                                                   name="attendance[{{ $participantIdPadded }}][{{ $date }}]"
                                                   value="N">

                                            @if($alreadyInOtherShift)

                                                <div class="other-shift-warning">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Other Shift
                                                </div>

                                                <input type="checkbox"
                                                       class="large-checkbox day-{{ $index }} other-shift-checkbox"
                                                       value="Y"
                                                       checked
                                                       disabled>

                                            @else

                                                <input type="checkbox"
                                                       class="large-checkbox day-{{ $index }}"
                                                       value="Y"
                                                       name="attendance[{{ $participantIdPadded }}][{{ $date }}]"
                                                       {{ $attendanceData && $attendanceData->AttendanceYN == 'Y' ? 'checked' : '' }}>

                                            @endif

                                        </td>

                                    @endforeach
                                </tr>

                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="attendance-save-bar">
                    <button type="submit" class="btn btn-success">
                        Save Weekly Attendance
                    </button>
                </div>

            </form>

        @endif

    </div>
</div>

@include('partials.sweetalert')

@endsection

@section('scripts')

<script>
$(document).ready(function () {

    /*
     * Morning / Noon shift selection.
     * Load Week button stays disabled until one shift is selected.
     */
    const selectedShift = $('#selectedShift');
    const morningBtn = $('#morningShiftBtn');
    const noonBtn = $('#noonShiftBtn');
    const loadBtn = $('#loadWeekBtn');

    function selectShift(type) {
        selectedShift.val(type);

        if (type === 'A') {
            morningBtn.removeClass('btn-outline-primary').addClass('btn-primary active-shift');
            noonBtn.removeClass('btn-primary active-shift').addClass('btn-outline-primary');
        }

        if (type === 'N') {
            noonBtn.removeClass('btn-outline-primary').addClass('btn-primary active-shift');
            morningBtn.removeClass('btn-primary active-shift').addClass('btn-outline-primary');
        }

        loadBtn.prop('disabled', false);
    }

    morningBtn.on('click', function () {
        selectShift('A');
    });

    noonBtn.on('click', function () {
        selectShift('N');
    });

    /*
    * Row checkbox:
    * Check/uncheck Monday to Friday only for this participant row.
    *
    * Day indexes:
    * 0 = Sunday
    * 1 = Monday
    * 2 = Tuesday
    * 3 = Wednesday
    * 4 = Thursday
    * 5 = Friday
    * 6 = Saturday
    *
    * Disabled other-shift boxes are skipped.
    */
    $(document).on('change', '.select-row-weekdays-checkbox', function () {
        var isChecked = $(this).is(':checked');
        var row = $(this).closest('tr');

        for (var dayIndex = 1; dayIndex <= 5; dayIndex++) {
            row.find('.day-' + dayIndex).not(':disabled').prop('checked', isChecked);
            updateDayCount(dayIndex);
        }
    });

    /*
     * Initialize DataTable only when attendance table exists.
     * On first screen before selecting shift, table will not exist.
     */
    if ($('#weeklyAttendanceTable').length) {

        if ($.fn.DataTable.isDataTable('#weeklyAttendanceTable')) {
            $('#weeklyAttendanceTable').DataTable().destroy();
        }

        $('#weeklyAttendanceTable').DataTable({
            scrollY: '65vh',
            scrollX: true,
            scrollCollapse: true,
            paging: false,
            ordering: true,
            searching: true,
            info: false,
            fixedHeader: true,
            order: [[0, 'asc']],
            columnDefs: [
                {
                    targets: 4, // Row-wise Mon-Fri checkbox column
                    orderable: false,
                    searchable: false
                }
            ]
        });
    }


    /*
     * Recalculate daily counts whenever any attendance checkbox changes.
     * Count only current shift editable checkboxes.
     * Locked other-shift checkboxes are excluded from the count.
     */
    function updateDayCount(dayIndex) {
        var count = $('.day-' + dayIndex + ':checked').not(':disabled').length;
        $('#day-count-' + dayIndex).text(count);
    }

    /*
     * Recalculate all day counts.
     */
    function updateAllDayCounts() {
        $('.select-day-checkbox').each(function () {
            var dayIndex = $(this).data('day-index');
            updateDayCount(dayIndex);
        });
    }

    //

    $(document).on('change', '.large-checkbox', function () {

    if ($(this).hasClass('select-row-weekdays-checkbox')) {
        return;
    }

    var classList = $(this).attr('class').split(/\s+/);

    classList.forEach(function (className) {
        if (className.startsWith('day-')) {
            var dayIndex = className.replace('day-', '');
            updateDayCount(dayIndex);
        }
    });

    /*
     * Sync row Mon-Fri checkbox.
     * It becomes checked only when all editable Mon-Fri boxes are checked.
     */
        var row = $(this).closest('tr');
        var weekdayBoxes = row.find('.day-1, .day-2, .day-3, .day-4, .day-5').not(':disabled');
        var checkedWeekdayBoxes = weekdayBoxes.filter(':checked');

        row.find('.select-row-weekdays-checkbox').prop(
            'checked',
            weekdayBoxes.length > 0 && weekdayBoxes.length === checkedWeekdayBoxes.length
        );
    });

    /*
     * Header checkbox checks/unchecks whole date column
     * and refreshes that day's count immediately.
     *
     * Important:
     * Do not check/uncheck disabled other-shift warning boxes.
     */
    $(document).on('change', '.select-day-checkbox', function () {
        var dayIndex = $(this).data('day-index');
        var isChecked = $(this).is(':checked');

        $('.day-' + dayIndex).not(':disabled').prop('checked', isChecked);

        updateDayCount(dayIndex);
    });

    /*
     * Make sure counts are correct on page load.
     */
    updateAllDayCounts();

});
</script>

@endsection
