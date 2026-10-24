@extends('admin.admin_dashboard')
@section('admin')
@section('page-toolbar')
<a href="{{ route('icd10.create') }}" class="btn btn-success">Add ICD-10</a>

<a href="{{ url()->previous() }}" class="btn btn-primary waves-effect waves-light">
    {{ t('common.back','Back') }}
</a>
@endsection

<div class="page-content">
    <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <!-- LEFT SIDE -->
                        <div>
                            <h4 class="mb-0 font-size-18">ICD-10 Code Maintenance</h4>
                        </div>
                        <!-- RIGHT SIDE TOOLBAR -->
                        <div class="page-title-right">
                            @yield('page-toolbar')
                        </div>
                    </div>
                </div>
             </div>
            <!-- end page title -->

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <table id="icdTable" class="table table-sm table-striped w-100">
                    <thead>
                        <tr>
                            <th>Code</th>
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
            ajax: "{{ route('icd10.data') }}",
            order: [[0, 'asc']],
            columns: [
                { data: 'code', name: 'code' },
                { data: 'short_description', name: 'short_description' },
                { data: 'action', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endsection
