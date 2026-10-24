@extends('admin.admin_dashboard')
@section('title', 'Backup Management')

@section('admin')

@section('page-toolbar')
<a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">Back</a>
@endsection

<div class="page-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-body">

                <h5 class="mb-3">Manual Backup</h5>

                <form method="POST" action="{{ route('backup.run') }}">
                    @csrf
                    <button class="btn btn-danger">
                        Run Backup Now
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection
