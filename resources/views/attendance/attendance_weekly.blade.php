@extends('admin.admin_dashboard')
@section('admin')
<div class="container">
    <h3 class="mb-4">Weekly Attendance Entry</h3>

    {{-- Form to select Week Start Date (must be Sunday) --}}
    <form method="GET" action="{{ route('attendance.weekly.entry') }}" class="form-inline mb-4">
        <label for="week_start_date" class="mr-2">Select Week Starting Date (Sunday):</label>
        <input type="date" name="week_start_date" id="week_start_date" class="form-control mr-2"
               value="{{ request('week_start_date') }}" required>
        <button type="submit" class="btn btn-primary">Load Weekly</button>
    </form>

    @if(isset($dates) && isset($members))
    <form method="POST" action="{{ route('attendance.weekly.save') }}">
        @csrf
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Participant ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        @foreach ($dates as $date)
                            <th>{{ \Carbon\Carbon::parse($date)->format('D m/d') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $member)
                        <tr>
                            <td>{{ $member->participant_id }}</td>
                            <td>{{ $member->last_name }}</td>
                            <td>{{ $member->first_name }}</td>
                            @foreach ($dates as $date)
                                <td class="text-center">
                                    <input type="checkbox" name="attendance[{{ $member->participant_id }}][{{ $date }}]" value="1">
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-success">Save Weekly Attendance</button>
    </form>
    @endif
</div>

@endsection
