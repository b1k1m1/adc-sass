@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Physician Categories</h5>
                <div class="col-auto ms-auto">
                    <a href="{{ route('physcat.create') }}" class="btn btn-primary">New / Add</a>
                    <a href="{{ route('doctors.dashboard') }}#messages1" class="btn btn-primary">{{ t('common.back','Back') }}</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped align-middle" id="tbl-physcat">
                    <thead><tr>
                    <th>ID</th><th>Category</th><th>Short Name</th><th>Status</th><th class="text-end">Actions</th>
                    </tr></thead>
                        <tbody>
                        @foreach($rows as $r)
                            <tr>
                            <td>{{ $r->phy_cat_id }}</td>
                            <td>{{ $r->phy_cat_name }}</td>
                            <td>{{ $r->phy_name }}</td>
                            <td>{{ $r->trans_flag }}</td>
                            <td class="text-end">
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('physcat.show',$r->phy_cat_id) }}">{{ t('common.view', 'View') }}</a>
                            <a class="btn btn-primary btn-sm" href="{{ route('physcat.edit',$r->phy_cat_id) }}">{{ t('common.edit', 'Edit') }}</a>
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
