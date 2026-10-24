@extends('admin.admin_dashboard')

@section('title', 'Member Statement')

@section('admin')
    <div class="page-content">
        <div class="container-fluid">

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Member Statement / Ledger</h4>
                </div>

                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="GET" id="memberStatementForm">

                        <div class="row g-3 align-items-end">

                            <div class="col-md-5">
                                <label for="participant_id" class="form-label">
                                    Member
                                </label>

                                <select name="participant_id" id="participant_id" class="form-select select2" required>
                                    <option value="">Select Member</option>

                                    @foreach ($members as $member)
                                        <option value="{{ $member->ParticipantId }}"
                                            {{ request('participant_id') == $member->ParticipantId ? 'selected' : '' }}>
                                            {{ $member->ParticipantId }}
                                            -
                                            {{ $member->LastName }},
                                            {{ $member->FirstName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="date_from" class="form-label">
                                    Start Date
                                </label>

                                <input type="date" name="date_from" id="date_from" class="form-control"
                                    value="{{ request('date_from') }}" required>
                            </div>

                            <div class="col-md-2">
                                <label for="date_to" class="form-label">
                                    End Date
                                </label>

                                <input type="date" name="date_to" id="date_to" class="form-control"
                                    value="{{ request('date_to') }}" required>
                            </div>

                            <div class="col-md-3">
                                <div class="d-flex gap-2">

                                    <button type="button" class="btn btn-primary" onclick="submitStatement('view')">
                                        <i class="fas fa-eye me-1"></i>
                                        View
                                    </button>

                                    <button type="button" class="btn btn-danger" onclick="submitStatement('pdf')">
                                        <i class="fas fa-file-pdf me-1"></i>
                                        PDF
                                    </button>

                                    <button type="button" class="btn btn-success" onclick="submitStatement('excel')">
                                        <i class="fas fa-file-excel me-1"></i>
                                        Excel
                                    </button>

                                </div>
                            </div>

                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')

    <script>
        $(document).ready(function() {

            $('#participant_id').select2({
                placeholder: 'Select Member',
                allowClear: true,
                width: '100%'
            });

        });

        function submitStatement(type) {

            let participantId = $('#participant_id').val();
            let dateFrom = $('#date_from').val();
            let dateTo = $('#date_to').val();

            if (!participantId) {
                alert('Please select a member.');
                return;
            }

            if (!dateFrom || !dateTo) {
                alert('Please select start date and end date.');
                return;
            }

            if (dateFrom > dateTo) {
                alert('Start date cannot be greater than end date.');
                return;
            }

            let baseUrl = '';

            if (type === 'view') {
                baseUrl = "{{ route('claims.member.statement.view') }}";
            }

            if (type === 'pdf') {
                baseUrl = "{{ route('claims.member.statement.pdf') }}";
            }

            if (type === 'excel') {
                baseUrl = "{{ route('claims.member.statement.excel') }}";
            }

            let url =
                baseUrl +
                '?participant_id=' + encodeURIComponent(participantId) +
                '&date_from=' + encodeURIComponent(dateFrom) +
                '&date_to=' + encodeURIComponent(dateTo);

            if (type === 'view') {
                window.location.href = url;
            } else {
                window.open(url, '_blank');
            }
        }
    </script>

@endsection
