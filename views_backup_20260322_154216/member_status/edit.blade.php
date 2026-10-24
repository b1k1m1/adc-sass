@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
<div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Edit Member Status</h4>
            <a href="{{ route('member_status.index') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
        </div>


    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('member_status.update', $row->Id) }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Status Id</label>
                        <input type="text"
                               name="Id"
                               class="form-control"
                               maxlength="10"
                               value="{{ old('code', $row->Id) }}"
                               required
                               style="text-transform:uppercase">
                    </div>

                    <div class="col-md-9">
                        <label class="form-label">Description</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               maxlength="10"
                               value="{{ old('short_description', $row->name) }}"
                               required
                               style="text-transform:uppercase">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        Update Member Status
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
</div>

@endsection
