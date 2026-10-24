@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="col d-flex align-items-end mb-3 flex-wrap">
            <h4 class="mb-0">Frequency Maintenance</h4>
            <!-- Right-side buttons -->
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('frequency.create') }}" class="btn btn-success">Add Frequency</a>

                <a href="{{ route('members.dashboard') }}#messages1" class="btn btn-primary no-print">
                    {{ t('common.back','Back') }}
                </a>
            </div>
        </div>

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <table id="icdTable" class="table table-sm table-striped w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th style="width:90px;">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
    <script>
    $(function () {
        $('#icdTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: "{{ route('frequency.data') }}",
            order: [[0, 'asc']],
            columns: [
                { data: 'Id', name: 'Id' },
                { data: 'name', name: 'name' },
                { data: 'action', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endsection
