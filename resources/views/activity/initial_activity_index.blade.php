@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="row mb-3">
            <div class="col-md-6">
                <h4 class="mb-0">Initial Client Activity Assessment</h4>
            </div>

            <div class="col-md-6 text-end">
                <a href="{{ route('participant.activity.assessment.index', ['shift' => 'A']) }}"
                   class="btn {{ $shift === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Morning
                </a>

                <a href="{{ route('participant.activity.assessment.index', ['shift' => 'N']) }}"
                   class="btn {{ $shift === 'N' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Noon
                </a>

                <a href="{{ url()->previous() }}" class="btn btn-primary">
                    Back
                </a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">

                <input type="hidden" id="shift" value="{{ $shift }}">

                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Select Member</label>
                        <select id="ParticipantId" class="form-select">
                            <option value="">Select Member</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <button type="button" id="loadAssessments" class="btn btn-info">
                            Load
                        </button>

                        <button type="button" id="addNewAssessment" class="btn btn-primary">
                            + Add New
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <strong>Assessments</strong>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th style="width: 160px;">Assessment Date</th>
                                <th style="width: 180px;">Reason</th>
                                <th>Diagnosis</th>
                                <th style="width: 150px;">Completed By</th>
                                <th style="width: 180px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="assessmentTableBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Select member and click Load.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    let selectedShift = $('#shift').val();

    function formatDateDisplay(value) {
        if (!value) {
            return '';
        }

        let dateOnly = String(value).substring(0, 10);
        let parts = dateOnly.split('-');

        if (parts.length !== 3) {
            return value;
        }

        return parts[1] + '/' + parts[2] + '/' + parts[0];
    }

    function assessmentTypeDisplay(value) {
        if (value === 'initial_admission') {
            return 'Initial Admission';
        }

        if (value === 'annual') {
            return 'Annual';
        }

        if (value === 'update') {
            return 'Update';
        }

        return '';
    }

    function loadMembers() {
        $('#ParticipantId').html('<option value="">Loading...</option>');

        $.ajax({
            url: "{{ route('participant.activity.assessment.members') }}",
            type: "GET",
            data: {
                shift: selectedShift
            },
            success: function (response) {
                let options = '<option value="">Select Member</option>';

                response.forEach(function (member) {
                    let pid = String(member.ParticipantId).padStart(3, '0');
                    let name = pid + ' - ' + member.LastName + ', ' + member.FirstName;

                    options += '<option value="' + member.ParticipantId + '">' + name + '</option>';
                });

                $('#ParticipantId').html(options);
            },
            error: function () {
                $('#ParticipantId').html('<option value="">Unable to load members</option>');
            }
        });
    }

    function loadAssessments() {
        let participantId = $('#ParticipantId').val();

        if (!participantId) {
            Swal.fire('Missing Member', 'Please select member first.', 'warning');
            return;
        }

        $('#assessmentTableBody').html(
            '<tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>'
        );

        $.ajax({
            url: "{{ route('participant.activity.assessment.list') }}",
            type: "GET",
            data: {
                ParticipantId: participantId,
                shift: selectedShift
            },
            success: function (response) {
                let rows = '';

                if (!response.assessments || response.assessments.length === 0) {
                    rows = `
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No assessment found for this member. Please click + Add New.
                            </td>
                        </tr>
                    `;

                    $('#assessmentTableBody').html(rows);
                    return;
                }

                response.assessments.forEach(function (item) {

                    let editUrl = "{{ route('participant.activity.assessment.edit', ':id') }}".replace(':id', item.id);
                    let viewUrl = "{{ route('participant.activity.assessment.view', ':id') }}".replace(':id', item.id);
                    let pdfUrl  = "{{ route('participant.activity.assessment.pdf', ':id') }}".replace(':id', item.id);


                    rows += `
                        <tr>
                            <td>${item.id}</td>
                            <td>${formatDateDisplay(item.assessment_date)}</td>
                            <td>${assessmentTypeDisplay(item.assessment_type)}</td>
                            <td>${item.diagnosis ?? ''}</td>
                            <td>${item.completed_by ?? ''}</td>
                            <td>
                                <a href="${editUrl}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="${viewUrl}" class="btn btn-sm btn-outline-info">View</a>
                                <a href="${pdfUrl}" target="_blank" class="btn btn-sm btn-outline-danger">PDF</a>
                            </td>
                        </tr>
                    `;
                });

                $('#assessmentTableBody').html(rows);
            },
            error: function () {
                $('#assessmentTableBody').html(
                    '<tr><td colspan="6" class="text-center text-danger">Unable to load assessments.</td></tr>'
                );
            }
        });
    }

    $('#loadAssessments').on('click', function () {
        loadAssessments();
    });

    $('#addNewAssessment').on('click', function () {
        let participantId = $('#ParticipantId').val();

        if (!participantId) {
            Swal.fire('Missing Member', 'Please select member first.', 'warning');
            return;
        }

        let createUrl = "{{ route('participant.activity.assessment.create') }}"
            + "?shift=" + encodeURIComponent(selectedShift)
            + "&ParticipantId=" + encodeURIComponent(participantId);

        window.location.href = createUrl;
    });

    loadMembers();

});
</script>
@endpush
