@extends('admin.admin_dashboard')

@section('title', 'Initial Assessments')
@section('page-title', 'Initial Assessments')

@section('admin')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="page-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-body">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Initial Assessments</h5>

                    <a href="{{ route('applicants.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Initial Assessment
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:100px;">Form #</th>
                                <th>Participant Name</th>
                                <th style="width:120px;">Birthday</th>
                                <th style="width:120px;">Date</th>
                                <th style="width:180px;">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($applicants as $row)
                                <tr>
                                    <td>{{ $row->form_no }}</td>
                                    <td>{{ $row->participant_name }}</td>
                                    <td>{{ optional($row->birthday)->format('m/d/Y') }}</td>
                                    <td>{{ optional($row->created_at)->format('m/d/Y') }}</td>
                                    <td>
                                        <a href="{{ route('applicants.edit', $row->id) }}"
                                        class="btn btn-primary">
                                            Edit
                                        </a>

                                        <a href="{{ route('applicants.pdf', $row->id) }}"
                                        target="_blank"
                                        class="btn btn-danger">
                                            PDF
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No Initial Assessment found.
                                        <br><br>

                                        <a href="{{ route('applicants.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i> Add First Initial Assessment
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $applicants->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
