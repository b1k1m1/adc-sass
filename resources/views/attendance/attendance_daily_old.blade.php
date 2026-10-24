@extends('admin.admin_dashboard')
@section('admin')
@inject('carbon', 'Carbon\Carbon')

@php
    $type = $type ?? request('type', 'A');
    $type = in_array(strtoupper($type), ['A', 'N'], true) ? strtoupper($type) : 'A';

    $pageTitle = $type === 'A'
        ? 'Morning Shift Attendance'
        : 'Noon Shift Attendance';
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
</style>

<div class="page-content">
    <div class="container-fluid">

 <!-- start page title -->

 <div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">{{ $pageTitle }}</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('attendance.index', ['type' => 'A']) }}"
                class="btn btn-sm {{ $type === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Morning Shift
                </a>

                <a href="{{ route('attendance.index', ['type' => 'N']) }}"
                class="btn btn-sm {{ $type === 'N' ? 'btn-secondary' : 'btn-outline-secondary' }}">
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
                        <label for="attendanceDate" class="col-form-label font-size-18">Select Date:</label>
                    </div>

                    <div class="col-md-6 d-flex align-items-center">
                        <input type="text"
                               name="date"
                               placeholder="mm/dd/yyyy"
                               value="{{ old('date', Carbon\Carbon::parse($date)->format('m/d/Y') ?? '') }}"
                               pattern="(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])\/(19|20)\d{2}"
                               title="Please enter a date in mm/dd/yyyy format"
                               required>

                        &nbsp;&nbsp;&nbsp;&nbsp;

                        <button type="submit" class="btn btn-primary ml-2">Load</button>

                        &nbsp;&nbsp;&nbsp;&nbsp;

                        <input type="checkbox" id="checkDefaultmain" title="Select all">

                        &nbsp;&nbsp;&nbsp;&nbsp;

                        <label for="checkDefaultmain" class="font-size-18 mb-0">Select All</label>

                        &nbsp;&nbsp;&nbsp;&nbsp;

                        <span class="attendance-count-box">
                            Checked:
                            <span id="dailyCheckedCount">0</span>
                        </span>
                    </div>

                </div>
            </form>

            @if(isset($members))
            <form method="POST" action="{{ route('attendance.save') }}">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <input type="hidden" name="type" value="{{ $type }}">

                <table id="attendance_daily_table" class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Participant ID</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>PA Expiry Date</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)

                        <tr class="{{ ($existingAttendance[str_pad( $member->ParticipantId ,3, '0',STR_PAD_LEFT)] ?? 'N') === 'Y' ? 'checked' : '' }}">
                            <td>{{ str_pad( $member->ParticipantId ,3, '0',STR_PAD_LEFT) }}</td>
                            <td>{{ $member->LastName }}</td>
                            <td>{{ $member->FirstName }}</td>

                            @php
                                $medicaidEnd = $member->medicaid_PA_End;

                                $carbonFormattedDate = $medicaidEnd ? \Carbon\Carbon::parse($medicaidEnd) : null;
                                $carbonFrmDate = $date ? \Carbon\Carbon::parse($date) : null;
                                $textColor= null;

                                if ($carbonFormattedDate && $carbonFrmDate) {
                                   $textColor = $carbonFormattedDate->lt($carbonFrmDate) ? 'red' : 'green';
                                }
                            @endphp

                            <td style="color: {{ $textColor }}">
                                {{ $carbonFormattedDate ? $carbonFormattedDate->format('m/d/Y') : '' }}
                            </td>

                            @php
                                $paddedId = str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT);
                                $isChecked = ($existingAttendance[$paddedId] ?? 'N') === 'Y';
                            @endphp

                            <td>
                                <div class="form-check">
                                    <input type="hidden" name="AttendanceYN[{{ $paddedId }}]" value="N">

                                    <input type="checkbox"
                                        class="form-check-input attendanceCheckbox attendance-toggle"
                                        name="AttendanceYN[{{ $paddedId }}]"
                                        value="Y"
                                        {{ $isChecked ? 'checked' : '' }}>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <button type="submit" class="btn btn-primary">Save Attendance</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>

    $(document).ready(function() {

        $('#attendance_daily_table').DataTable({
            paging: true,
            pageLength: 50,
            lengthChange: false,
            searching: false
        });

        function updateDailyCheckedCount() {
            let checkedCount = $('.attendanceCheckbox:checked').length;
            $('#dailyCheckedCount').text(checkedCount);
        }

        $('#checkDefaultmain').on('change', function(){
            let checked = $(this).is(':checked');

            $('.attendanceCheckbox').prop('checked', checked);

            updateDailyCheckedCount();
        });

        $(document).on('change', '.attendanceCheckbox', function () {
            updateDailyCheckedCount();

            let totalCheckboxes = $('.attendanceCheckbox').length;
            let checkedCheckboxes = $('.attendanceCheckbox:checked').length;

            $('#checkDefaultmain').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
        });

        updateDailyCheckedCount();

    });

</script>

@endsection
