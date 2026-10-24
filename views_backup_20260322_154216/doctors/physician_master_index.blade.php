@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Physician Master</h5>
                <div class="col-auto ms-auto">
                    <a href="{{ route('phys.create') }}" class="btn btn-primary">New / Add</a>
                    <a href="{{ route('doctors.dashboard') }}#messages1" class="btn btn-primary">{{ t('common.back','Back') }}</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped" id="tbl-phys">
                <thead><tr>
                <th>ID</th><th>Last</th><th>First</th><th>Speciality</th><th>City</th><th>Phone</th><th>Status</th><th class="text-end">Actions</th>
                </tr></thead>
                    <tbody>
                    @foreach($rows as $r)
                        <tr>
                            <td>{{ $r->doctor_id }}</td>
                            <td>{{ $r->lastname }}</td>
                            <td>{{ $r->firstname }}</td>
                            <td>{{ $r->phy_cat_name }}</td>
                            <td>{{ $r->ccity }}</td>
                            <td>{{ $r->cphone1 }}</td>
                            <td>{{ $r->trans_flag }}</td>
                            <td class="text-end">
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('phys.show',$r->doctor_id) }}">View</a>
                                <a class="btn btn-primary btn-sm" href="{{ route('phys.edit',$r->doctor_id) }}">Edit</a>
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
