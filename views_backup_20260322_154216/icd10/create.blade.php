@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
<div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Add ICD-10 Code</h4>
            <a href="{{ route('icd10.index') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
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

            <form method="POST" action="{{ route('icd10.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">ICD-10 Code</label>
                        <input type="text"
                               name="code"
                               class="form-control"
                               maxlength="10"
                               value="{{ old('code') }}"
                               required
                               style="text-transform:uppercase">
                    </div>

                    <div class="col-md-9">
                        <label class="form-label">Short Description</label>
                        <input type="text"
                               name="short_description"
                               class="form-control"
                               maxlength="255"
                               value="{{ old('short_description') }}"
                               required
                               style="text-transform:uppercase">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        Save ICD-10 Code
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
</div>

@endsection
