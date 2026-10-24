@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                 <div class="col d-flex gap-2 flex-wrap align-items-end">
                    <h5 class="mb-0">Appointments</h5>

                    <div class="col-auto ms-auto">
                        <a href="{{ route('appt.create') }}" class="btn btn-primary">New / Add</a>
                        <a href="{{ route('doctors.dashboard') }}" class="btn btn-primary">{{ t('common.back','Back') }}</a>
                    </div>
                 </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped" id="tbl-appt">
                <thead><tr>
                <th>Participant</th><th>Appt Date/Time</th><th>Doctor</th><th>Reason</th><th>Status</th><th class="text-end">Actions</th>
                </tr></thead>
                    <tbody>
                    @foreach($rows as $r)
                        <tr>
                        <td>{{ $r->member_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->appdate)->format('m/d/Y H:i') }}</td>
                        <td>{{ $r->doctor_name }}</td>
                        <td>{{ $r->reason }}</td>
                        <td>{{ $r->trans_flag }}</td>
                        <td class="text-end">
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('appt.show',[$r->participantid,$r->appdate]) }}">View</a>
                        <a class="btn btn-primary btn-sm" href="{{ route('appt.edit',[$r->participantid,$r->appdate]) }}">Edit</a>
                        </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

