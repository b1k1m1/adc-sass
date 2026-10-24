@extends('admin.admin_dashboard')
@section('admin')

@inject('carbon', 'Carbon\Carbon')

<style>

    input[type="checkbox"] {
        height: 25px;
        width: 25px;
        accent-color: gray; /* Default */
    }
</style>
<p>$CalenderDate</p>
<div class="page-content">
    <div class="container-fluid">

 <!-- start page title -->

 <div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Active Members List</h4>

               <div class="mt-3 font-size-18">
                    <label for="monthYear">Daily Attendance for : {{ Carbon\Carbon::parse($date)->format('m-d-Y') }} </label>

                </div>

               <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <a href="{{ route('attendance.dates','A') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                </ol>
            </div>

        </div>
    </div>
</div>

@if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
@endif

<div class="col-xl-12">
    <div class="card">
         <div class="card-body">
            <!--
            <form method="GET" action="{{ route('attendance.index') }}" class="mb-4">
                <div class="form-group row align-items-center">
                    <div class="col-md-1">
                        <label for="attendanceDate" class="col-form-label font-size-18">Select Date:</label>
                    </div>
                    <div class="col-md-3 d-flex align-items-center">
                        <input type="text"
                               name="date"
                               placeholder="mm/dd/yyyy"
                               value="{{ old('date', $date ?? '') }}"
                               pattern="(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])\/(19|20)\d{2}"
                               title="Please enter a date in mm/dd/yyyy format"
                               required> &nbsp;&nbsp;&nbsp;&nbsp;
                        <button type="submit" class="btn btn-primary ml-2">Load</button>&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="checkbox" id="checkDefaultmain" title="Select all">&nbsp;&nbsp;&nbsp;&nbsp;
                        <label for="checkDefaultmain" class="font-size-18">Select All</label>
                    </div>

                </div>
            </form>
        -->
            @if(isset($members))
            <form method="POST" action="{{ route('attendance.save') }}">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">

                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Participant ID</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)
                        <!--
                        @php
                            $isPresent = $existingAttendance[$member->ParticipantId] ?? false;
                        @endphp

                        <tr class="{{ $isPresent ? 'present' : 'absent' }}">
                        -->
                        <tr class="{{ ($existingAttendance[str_pad( $member->ParticipantId ,3, '0',STR_PAD_LEFT)] ?? 'N') === 'Y' ? 'checked' : '' }}">
                            <td>{{ str_pad( $member->ParticipantId ,3, '0',STR_PAD_LEFT) }}</td>
                            <td>{{ $member->LastName }}</td>
                            <td>{{ $member->FirstName }}</td>
                            @php
                                $paddedId = str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT);
                                $isChecked = ($existingAttendance[$paddedId] ?? 'N') === 'Y';
                            @endphp
                            <td>
                                <div class="form-check">
                                    <input type="hidden" name="AttendanceYN[{{ $paddedId }}]" value="N">
                                    <input type="checkbox"
                                        class="form-check-input attendanceCheckbox attendance-toggle "
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
    document.getElementById('attendanceDate').addEventListener('input', function(e) {
    // Auto-add slashes for better UX
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 2) value = value.slice(0, 2) + '/' + value.slice(2);
    if (value.length > 5) value = value.slice(0, 5) + '/' + value.slice(5, 9);
    e.target.value = value.slice(0, 10);
    })

  });

  $('#checkDefaultmain').click(function(){

    if($(this).is(':checked')) {
        $('input[ type= checkbox]').prop('checked',true);
    }else{
        $('input[ type= checkbox]').prop('checked',false);
    }
  });

</script>

@endsection
