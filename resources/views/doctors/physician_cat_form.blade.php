@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <form method="post" action="{{ $mode==='create' ? route('physcat.store') : ($mode==='edit' ? route('physcat.update',$row->phy_cat_id) : '#') }}">
            @csrf
            @if($mode==='edit') @method('PUT') @endif
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Physician Category — {{ ucfirst($mode) }}</h5>
                    <div>
                        @if($mode!=='view')
                        <button class="btn btn-success">{{ t('common.save', 'Save') }}</button>
                        @endif
                        <a class="btn btn-primary" href="{{ route('physcat.index') }}">{{ t('common.back','Back') }}</a>
                    </div>
                </div>
                <div class="card-body row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Category ID</label>
                        <input name="phy_cat_id" value="{{ old('phy_cat_id',$row->phy_cat_id) }}" class="form-control form-control-sm" {{ $mode!=='create'?'readonly':'' }}>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <input name="phy_cat_name" value="{{ old('phy_cat_name',$row->phy_cat_name) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
                    </div>
                        <div class="col-md-3">
                        <label class="form-label">Short Name</label>
                        <input name="phy_name" value="{{ old('phy_name',$row->phy_name) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
                    </div>
                        <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <x-status-select :value="old('trans_flag',$row->trans_flag)" :disabled="$mode==='view'" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
