@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <form method="post" action="{{ $mode==='create' ? route('phys.store') : ($mode==='edit' ? route('phys.update',$row->doctor_id) : '#') }}">
        @csrf
        @if($mode==='edit') @method('PUT') @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Physician — {{ ucfirst($mode) }}</h5>
                <div>
                    @if($mode!=='view')<button class="btn btn-success">{{ t('common.save', 'Save') }}</button>@endif
                    <a class="btn btn-primary" href="{{ route('phys.index') }}">{{ t('common.back','Back') }}</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Doctor Id</label>
                        <input name="doctor_id" value="{{ old('doctor_id',$row->doctor_id) }}" class="form-control form-control-sm" {{ $mode!=='create'?'readonly':'' }}>
                    </div>
                <div class="col-md-3">
                    <label class="form-label">Speciality</label>
                    <select name="speciality" class="form-select form-select-sm" {{ $mode==='view'?'disabled':'' }}>
                    <option value="">-- select --</option>
                        @foreach($cats as $c)
                        <option value="{{ $c->phy_cat_id }}"
                            @selected(old('speciality', $row->speciality ?? '') == $c->phy_cat_id)>
                            {{ $c->phy_cat_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Last Name</label>
                    <input name="lastname" value="{{ old('lastname',$row->lastname) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
                </div>
                <div class="col-md-3">
                    <label class="form-label">First Name</label>
                    <input name="firstname" value="{{ old('firstname',$row->firstname) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}>
                </div>

                <div class="col-12"><hr><strong>Clinic Address</strong></div>
                <div class="col-md-4"><label class="form-label">Address</label><input name="cadd1" value="{{ old('cadd1',$row->cadd1) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>
                <div class="col-md-4"><label class="form-label">Address 2</label><input name="cadd2" value="{{ old('cadd2',$row->cadd2) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>
                <div class="col-md-3"><label class="form-label">City</label><input name="ccity" value="{{ old('ccity',$row->ccity) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>
                <div class="col-md-1"><label class="form-label">State</label><input name="cstate" value="{{ old('cstate',$row->cstate) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>
                <div class="col-md-2"><label class="form-label">Zip</label><input name="czip" value="{{ old('czip',$row->czip) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>

                <div class="col-md-3"><label class="form-label">Phone #1</label><input name="cphone1" value="{{ old('cphone1',$row->cphone1) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>
                <div class="col-md-2"><label class="form-label">Extn</label><input name="cext1" value="{{ old('cext1',$row->cext1) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>
                <div class="col-md-3"><label class="form-label">Phone #2</label><input name="cphone2" value="{{ old('cphone2',$row->cphone2) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>
                <div class="col-md-2"><label class="form-label">Extn</label><input name="cext2" value="{{ old('cext2',$row->cext2) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>
                <div class="col-md-3"><label class="form-label">Cell #</label><input name="ccell" value="{{ old('ccell',$row->ccell) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>
                <div class="col-md-3"><label class="form-label">Fax #</label><input name="cfax" value="{{ old('cfax',$row->cfax) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>

                <div class="col-12"><hr><strong>Other Address</strong></div>
                <div class="col-md-4"><label class="form-label">Address</label><input name="oadd1" value="{{ old('oadd1',$row->oadd1) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>
                <div class="col-md-4"><label class="form-label">Address 2</label><input name="oadd2" value="{{ old('oadd2',$row->oadd2) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>
                <div class="col-md-3"><label class="form-label">City</label><input name="ocity" value="{{ old('ocity',$row->ocity) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>
                <div class="col-md-1"><label class="form-label">State</label><input name="ostate" value="{{ old('ostate',$row->ostate) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>
                <div class="col-md-2"><label class="form-label">Zip</label><input name="ozip" value="{{ old('ozip',$row->ozip) }}" class="form-control form-control-sm" {{ $mode==='view'?'readonly':'' }}></div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <x-status-select :value="old('trans_flag',$row->trans_flag)" :disabled="$mode==='view'" />
                </div>
                </div>
            </div>
        </div>
        </form>

    </div>
</div>
@endsection
