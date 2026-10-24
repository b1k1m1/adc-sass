@extends('admin.admin_dashboard')

@section('admin')

<style>
    .large-checkbox {
        transform: scale(1.0); /* if you want to increase size then make it 1.5 */
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
</style>

<div class="page-content">
    <div class="container-fluid">

        <h2 class="mb-4">Weekly Attendance Entry</h2>

        <form method="GET"
              action="{{ route('attendance.weekly.entry') }}"
              class="mb-4 d-flex align-items-center w-100">

            <label class="me-2 mb-0">
                Select Week Start Date (Sunday):
            </label>

            <input type="date"
                   name="week_start_date"
                   value="{{ $weekStartDate }}"
                   required
                   class="form-control me-2"
                   style="max-width: 180px;">

            <button type="submit" class="btn btn-primary me-2">
                Load Week
            </button>

            <a href="{{ route('attendance.dashboard') }}"
               class="btn btn-primary ms-auto">
                {{ t('common.back','Back') }}
            </a>
        </form>

        <form method="POST" action="{{ route('attendance.weekly.save') }}">
            @csrf

            <input type="hidden" name="week_start_date" value="{{ $weekStartDate }}">

            @php
                /*
                 * Calculate daily attendance counts for each date.
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
            @endphp

            <div class="table-responsive">
                <table id="weeklyAttendanceTable" class="table table-bordered table-striped nowrap w-100">
                    <thead>
                        <tr>
                            <th>Participant ID</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>PA Expiry Date</th>

                            @foreach($weekDates as $index => $date)
                                <th class="text-center">
                                    {{ \Carbon\Carbon::parse($date)->format('D m/d') }}
                                    <input type="checkbox"
                                           class="select-day-checkbox"
                                           data-day-index="{{ $index }}">
                                    <br>
                                    <small>Count: {{ $dailyCounts[$date] ?? 0 }}</small>
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

                                @foreach($weekDates as $index => $date)

                                    @php
                                        $attendanceData = isset($attendances[$participantIdPadded])
                                            ? collect($attendances[$participantIdPadded])->firstWhere('CalenderDate', $date)
                                            : null;
                                    @endphp

                                    <td class="text-center">
                                        <input type="hidden"
                                               name="attendance[{{ $participantIdPadded }}][{{ $date }}]"
                                               value="N">

                                        <input type="checkbox"
                                               class="large-checkbox day-{{ $index }}"
                                               value="Y"
                                               name="attendance[{{ $participantIdPadded }}][{{ $date }}]"
                                               {{ $attendanceData && $attendanceData->AttendanceYN == 'Y' ? 'checked' : '' }}>
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

    </div>
</div>

@include('partials.sweetalert')

@endsection

@section('scripts')

<script>
$(document).ready(function () {

    /*
     * Initialize DataTable on first page load also.
     * This makes initial screen and Load Week screen look the same.
     */
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
        order: [[0, 'asc']]
    });

    /*
     * Header checkbox checks/unchecks whole date column.
     */
    $(document).on('change', '.select-day-checkbox', function () {
        var dayIndex = $(this).data('day-index');
        var isChecked = $(this).is(':checked');

        $('.day-' + dayIndex).prop('checked', isChecked);
    });

});
</script>

@endsection
