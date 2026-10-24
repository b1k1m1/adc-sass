@extends('admin.admin_dashboard')

@section('title', 'Import ERA 835')

@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Import ERA 835</h4>
                <a href="{{ route('claims.create') }}" class="btn btn-secondary btn-sm">Back to Claims Entry</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success py-1 mb-2">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-1 mb-2">
                <ul class="mb-0 small">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('era.upload.process') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">ERA 835 File (.txt / .dat / .835)</label>
                        <input type="file" name="era_file" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        Upload &amp; Process
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
