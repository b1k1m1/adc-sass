@extends('admin.admin_dashboard')
@section('admin')

<style>
    .large-checkbox {
        transform: scale(1.0);
        margin: 5px;
    }

    .day-toggle {
        transform: scale(1.0);
        margin-left: 4px;
        cursor: pointer;
    }

    .day-count-box {
        display: block;
        margin-top: 3px;
        font-size: 12px;
        color: #555;
        font-weight: 600;
    }

    #weekly_attendance_table th,
    #weekly_attendance_table td {
        vertical-align: middle;
        white-space: nowrap;
    }

    #weekly_attendance_table thead th {
        text-align: center;
        background: #f8f9fa;
    }

    .attendance-table-wrapper {
        width: 100%;
    }

    div.dataTables_scrollBody {
        border-bottom: 1px solid #dee2e6;
    }

    /*
     * Keeps checkbox columns compact
     */
    #weekly_attendance_table .text-center {
        text-align: center;
    }
</style>

<div class="page-content">
    <div class="container-fluid">

        <h2 class="mb-4">Weekly Attendance Entry</h2>

        <form method="GET" action="{{ route('attendance.weekly.entry') }}" class="mb-4">
            <label>Select Week Start Date (Sunday):</label>
            <input type="date" name="week_start_date" value="{{ $weekStartDate }}" required>
            <button type="submit" class="btn btn-primary">Load Week</button>
        </form>

        <form method="POST" action="{{ route('attendance.weekly.save') }}">
            @csrf

            <input type="hidden" name="week_start_date" value="{{ $weekStartDate }}">
        <div class="attendance-table-wrapper">
            <table id="weekly_attendance_table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Participant ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>PA Expiry Date</th>

                        @foreach($weekDates as $date)
                            @php
                                $dateKey = \Carbon\Carbon::parse($date)->format('Y-m-d');
                            @endphp

                            <th class="text-center">
                                {{ \Carbon\Carbon::parse($date)->format('D m/d') }}

                                <input type="checkbox"
                                       class="day-toggle"
                                       data-date="{{ $dateKey }}"
                                       title="Select / Unselect all for {{ \Carbon\Carbon::parse($date)->format('m/d/Y') }}">

                                <span class="day-count-box">
                                    Count:
                                    <span class="day-count" id="count-{{ $dateKey }}">0</span>
                                </span>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach($participants as $participant)

                        @php
                            $participantIdPadded = str_pad($participant->ParticipantId, 3, '0', STR_PAD_LEFT);

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

                            <td style="color: {{ $textColor }}"
                                data-order="{{ $medicaidDate ? $medicaidDate->format('Y-m-d') : '' }}">
                                {{ $medicaidDate ? $medicaidDate->format('m/d/Y') : '' }}
                            </td>

                            @foreach($weekDates as $date)

                                @php
                                    $dateKey = \Carbon\Carbon::parse($date)->format('Y-m-d');

                                    $attendanceData = isset($attendances[$participantIdPadded])
                                        ? collect($attendances[$participantIdPadded])->firstWhere('CalenderDate', $date)
                                        : null;

                                    $isChecked = $attendanceData && $attendanceData->AttendanceYN == 'Y';
                                @endphp

                                <td class="text-center">

                                    <input type="hidden"
                                           name="attendance[{{ $participantIdPadded }}][{{ $dateKey }}]"
                                           value="N">

                                    <input type="checkbox"
                                           class="large-checkbox attendance-checkbox attendance-date-{{ $dateKey }}"
                                           data-date="{{ $dateKey }}"
                                           value="Y"
                                           name="attendance[{{ $participantIdPadded }}][{{ $dateKey }}]"
                                           {{ $isChecked ? 'checked' : '' }}>
                                </td>

                            @endforeach
                        </tr>

                    @endforeach
                </tbody>
            </table>
           </div>
            <button type="submit" class="btn btn-success">Save Weekly Attendance</button>

        </form>

    </div>
</div>

@include('partials.sweetalert')

<script>
    document.addEventListener('DOMContentLoaded', function () {

        /*
        * DataTable sorting:
        * First 4 columns are sortable:
        * Participant ID, Last Name, First Name, PA Expiry Date
        *
        * Date checkbox columns are not sortable.
        */
        if (typeof $ !== 'undefined' && $.fn.DataTable) {

            let totalColumns = $('#weekly_attendance_table thead th').length;
            let dateColumnIndexes = [];

            for (let i = 4; i < totalColumns; i++) {
                dateColumnIndexes.push(i);
            }

            let attendanceTable = $('#weekly_attendance_table').DataTable({
                paging: false,
                lengthChange: false,
                searching: false,
                info: false,

                scrollY: '60vh',
                scrollX: true,
                scrollCollapse: true,

                ordering: true,
                order: [[0, 'asc']],
                autoWidth: false,

                columnDefs: [
                    {
                        targets: dateColumnIndexes,
                        orderable: false
                    }
                ]
            });

            setTimeout(function () {
                attendanceTable.columns.adjust();
            }, 300);
        }

        /*
         * Update one date column count.
         */
        function updateDayCount(date) {
            let count = document.querySelectorAll(
                '.attendance-checkbox[data-date="' + date + '"]:checked'
            ).length;

            let countBox = document.getElementById('count-' + date);

            if (countBox) {
                countBox.textContent = count;
            }
        }

        /*
         * Update all date column counts.
         */
        function updateAllDayCounts() {
            document.querySelectorAll('.day-toggle').forEach(function (headerCheckbox) {
                let date = headerCheckbox.getAttribute('data-date');
                updateDayCount(date);
            });
        }

        /*
         * Header checkbox:
         * Select / unselect all members for selected date.
         */
        document.querySelectorAll('.day-toggle').forEach(function (headerCheckbox) {

            headerCheckbox.addEventListener('click', function (e) {
                e.stopPropagation();
            });

            headerCheckbox.addEventListener('change', function () {

                let date = this.getAttribute('data-date');
                let checked = this.checked;

                document.querySelectorAll('.attendance-checkbox[data-date="' + date + '"]').forEach(function (checkbox) {
                    checkbox.checked = checked;
                });

                updateDayCount(date);
            });

        });

        /*
         * Individual checkbox:
         * Update count when user checks/unchecks one participant.
         */
        document.querySelectorAll('.attendance-checkbox').forEach(function (checkbox) {

            checkbox.addEventListener('change', function () {
                let date = this.getAttribute('data-date');
                updateDayCount(date);

                let totalForDate = document.querySelectorAll(
                    '.attendance-checkbox[data-date="' + date + '"]'
                ).length;

                let checkedForDate = document.querySelectorAll(
                    '.attendance-checkbox[data-date="' + date + '"]:checked'
                ).length;

                let headerCheckbox = document.querySelector(
                    '.day-toggle[data-date="' + date + '"]'
                );

                if (headerCheckbox) {
                    headerCheckbox.checked = totalForDate > 0 && totalForDate === checkedForDate;
                }
            });

        });

        updateAllDayCounts();

    });
</script>

@endsection
