@extends('admin.admin_dashboard')
@section('admin')

<style>
    .large-checkbox {
        transform: scale(1.0); /* if u want to increase szie than make it 1.5 */
        margin: 5px; /* optional, for spacing */
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

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Participant</th>
                    <th>PA Expiry Date</th>
                    @foreach($weekDates as $date)
                        <th>{{ \Carbon\Carbon::parse($date)->format('D m/d') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($participants as $participant)

                    <tr>
                        <td>{{ str_pad( $participant->ParticipantId ,3, '0',STR_PAD_LEFT) }}   {{ $participant->FirstName }}   {{ $participant->LastName }}</td>
                        @php
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
                        <td style="color: {{ $textColor }}">
                            {{ $medicaidDate ? $medicaidDate->format('m/d/Y') : '' }}
                        </td>
                        <!--<td>{{ $participant->medicaid_PA_End ? \Carbon\Carbon::parse($participant->medicaid_PA_End)->format('m/d/Y'):'' }}</td> -->

                        @foreach($weekDates as $date)

                            @php
                                //$attendanceData = $attendances[str_pad( $participant->ParticipantId ,3, '0',STR_PAD_LEFT) ][$loop->index] ?? null;
                                //dd($attendanceData);

                                $participantIdPadded = str_pad($participant->ParticipantId, 3, '0', STR_PAD_LEFT);

                                 $attendanceData = isset($attendances[$participantIdPadded])
                                    ? collect($attendances[$participantIdPadded])
                                    ->firstWhere('CalenderDate', $date)
                                    : null;

                                 //$isChecked = $attendanceData && $attendanceData->AttendanceYN == 'Y';
                            @endphp
                            <td class="text-center">

                                <!-- Add hidden input BEFORE checkbox -->
                                <input type="hidden"
                                name="attendance[{{ $participant->ParticipantId }}][{{ $date }}]"
                                value="N">

                                <input type="checkbox"
                                class="large-checkbox"
                                value="Y"
                                name="attendance[{{ $participant->ParticipantId }}][{{ $date }}]"
                                    {{ $attendanceData && $attendanceData->AttendanceYN == 'Y' ? 'checked' : '' }}>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">Save Weekly Attendance</button>
    </form>
</div>
</div>
@include('partials.sweetalert')
@endsection
