@extends('admin.admin_dashboard')

@section('admin')

@php
    use Carbon\Carbon;

    $status = $status ?? 'BOTH';
    $participantId = $participantId ?? '';

    $displayStartDate = !empty($startDate)
        ? Carbon::parse($startDate)->format('m/d/Y')
        : request('start_date', now()->startOfMonth()->format('m/d/Y'));

    $displayEndDate = !empty($endDate)
        ? Carbon::parse($endDate)->format('m/d/Y')
        : request('end_date', now()->format('m/d/Y'));

    $totalRecords = isset($records) ? $records->count() : 0;

    $uniqueMembersCount = isset($records)
        ? $records->pluck('ParticipantId')->unique()->count()
        : 0;

    $holdCount = isset($records)
        ? $records->where('member_status', 'H')->pluck('ParticipantId')->unique()->count()
        : 0;

    $dischargedCount = isset($records)
        ? $records->where('member_status', 'D')->pluck('ParticipantId')->unique()->count()
        : 0;

    function vitalsCheckMark($value) {
        return strtoupper(trim((string)$value)) === 'Y' ? '✓' : '';
    }

@endphp



<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-sm-0">Vitals History</h4>
                        <p class="text-muted mb-0">
                            Hold / Discharged Member Vitals Report
                        </p>
                    </div>

                    <div class="page-title-right">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ t('common.back', 'Back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">

                        <form method="GET" action="{{ route('vitals.history') }}" id="vitalsHistoryForm">
                            <div class="row g-3 align-items-end">

                                <div class="col-md-2">
                                    <label for="status" class="form-label">Member Status</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="BOTH" {{ $status === 'BOTH' ? 'selected' : '' }}>
                                            Both
                                        </option>
                                        <option value="H" {{ $status === 'H' ? 'selected' : '' }}>
                                            Hold
                                        </option>
                                        <option value="D" {{ $status === 'D' ? 'selected' : '' }}>
                                            Discharged
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="participant_id" class="form-label">Member</label>
                                    <select name="participant_id"
                                            id="participant_id"
                                            class="form-select select2-member"
                                            style="width: 100%;">
                                        <option value=""></option>

                                        @foreach($dropdownMembers ?? [] as $member)
                                            <option value="{{ $member->ParticipantId }}"
                                                {{ (string)$participantId === (string)$member->ParticipantId ? 'selected' : '' }}>
                                                {{ str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT) }}
                                                -
                                                {{ $member->LastName }},
                                                {{ $member->FirstName }}
                                                -
                                                {{ $member->Trans_flag === 'H' ? 'Hold' : 'Discharged' }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                                <div class="col-md-2">
                                    <label for="start_date" class="form-label">From Date</label>
                                    <input type="text"
                                           name="start_date"
                                           id="start_date"
                                           class="form-control"
                                           value="{{ $displayStartDate }}"
                                           placeholder="mm/dd/yyyy">
                                </div>

                                <div class="col-md-2">
                                    <label for="end_date" class="form-label">To Date</label>
                                    <input type="text"
                                           name="end_date"
                                           id="end_date"
                                           class="form-control"
                                           value="{{ $displayEndDate }}"
                                           placeholder="mm/dd/yyyy">
                                </div>

                                <div class="col-md-2">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search"></i> Load
                                        </button>

                                        <a href="{{ route('vitals.history') }}"
                                           class="btn btn-outline-secondary">
                                            Clear
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-primary border-4">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Vitals Records</p>
                                <h4 class="mb-0">{{ $totalRecords }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                    <i class="fas fa-heartbeat"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Members Found</p>
                                <h4 class="mb-0">{{ $uniqueMembersCount }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle">
                                    <i class="fas fa-users"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-start border-warning border-4">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Hold Members</p>
                                <h4 class="mb-0">{{ $holdCount }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-warning-subtle text-warning rounded-circle">
                                    <i class="fas fa-pause-circle"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-start border-danger border-4">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Discharged Members</p>
                                <h4 class="mb-0">{{ $dischargedCount }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-danger-subtle text-danger rounded-circle">
                                    <i class="fas fa-user-slash"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Report Table --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="card-title mb-1">Vitals History Detail</h5>
                                <p class="text-muted mb-0">
                                    Date Range:
                                    <strong>{{ $displayStartDate }}</strong>
                                    to
                                    <strong>{{ $displayEndDate }}</strong>
                                </p>
                            </div>

                             <a href="{{ route('vitals.history.pdf', request()->query()) }}"
                                class="btn btn-danger btn-sm"
                                target="_blank">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table id="vitalsHistoryTable"
                                class="table table-bordered table-striped table-hover align-middle nowrap w-100">

                                <thead class="table-light">
                                    <tr>
                                        <th rowspan="2" style="width: 90px;">ID</th>
                                        <th rowspan="2">Last Name</th>
                                        <th rowspan="2">First Name</th>
                                        <th rowspan="2" style="width: 120px;">Status</th>
                                        <th rowspan="2" style="width: 120px;">Day/Date</th>

                                        <th colspan="2" class="text-center">Blood Pressure</th>
                                        <th rowspan="2" class="text-center">Sugar</th>
                                        <th rowspan="2" class="text-center">Pulse</th>
                                        <th rowspan="2" class="text-center">Weight</th>
                                        <th rowspan="2" class="text-center">Temp (°F)</th>

                                        <th rowspan="2" class="text-center">Dressing</th>
                                        <th rowspan="2" class="text-center">Toilet</th>
                                        <th rowspan="2" class="text-center">Transfer</th>
                                        <th rowspan="2" class="text-center">Ambulation</th>
                                        <th rowspan="2" class="text-center">Eating</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">Max/SYS</th>
                                        <th class="text-center">Min/DIA</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($records ?? [] as $row)
                                        <tr>
                                            <td>{{ $row->ParticipantId }}</td>
                                            <td>{{ $row->LastName ?? '' }}</td>
                                            <td>{{ $row->FirstName ?? '' }}</td>

                                            <td>
                                                @if(($row->member_status ?? '') === 'H')
                                                    <span class="badge bg-warning text-dark">Hold</span>
                                                @elseif(($row->member_status ?? '') === 'D')
                                                    <span class="badge bg-danger">Discharged</span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        {{ $row->member_status ?? '' }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td data-order="{{ !empty($row->CalenderDate) ? Carbon::parse($row->CalenderDate)->format('Y-m-d') : '' }}">
                                                {{ !empty($row->CalenderDate)
                                                    ? Carbon::parse($row->CalenderDate)->format('D, m/d/Y')
                                                    : '' }}
                                            </td>
                                            <td class="text-center">{{ $row->LowBP ?? '' }}</td>
                                            <td class="text-center">{{ $row->HighBP ?? '' }}</td>
                                            <td class="text-center">{{ $row->Sugar ?? '' }}</td>
                                            <td class="text-center">{{ $row->Pulse ?? '' }}</td>
                                            <td class="text-center">{{ $row->Weight ?? '' }}</td>
                                            <td class="text-center">{{ $row->Temperature ?? '' }}</td>

                                            <td class="text-center fw-bold text-success">{{ vitalsCheckMark($row->BathingYN ?? '') }}</td>
                                            <td class="text-center fw-bold text-success">{{ vitalsCheckMark($row->ToiletYN ?? '') }}</td>
                                            <td class="text-center fw-bold text-success">{{ vitalsCheckMark($row->TransferYN ?? '') }}</td>
                                            <td class="text-center fw-bold text-success">{{ vitalsCheckMark($row->LocomotionYN ?? '') }}</td>
                                            <td class="text-center fw-bold text-success">{{ vitalsCheckMark($row->EatingYN ?? '') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="16" class="text-center text-muted py-4">
                                                <i class="fas fa-info-circle"></i>
                                                No vitals records found for selected criteria.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function () {

        // Searchable member dropdown
        if ($.fn.select2) {
            $('#participant_id').select2({
                width: '100%',
                placeholder: 'All Members',
                allowClear: true
            });

            // When user clicks X, reset dropdown to initial All Members state
            $('#participant_id').on('select2:clear', function () {
                $(this).val('').trigger('change');
            });
        }

        // When Member Status changes, reset selected member to All Members
        $('#status').on('change', function () {
            $('#participant_id').val('').trigger('change');
        });


        if ($('#vitalsHistoryTable tbody tr').length > 0 &&
            !$('#vitalsHistoryTable tbody tr td[colspan]').length) {

            $('#vitalsHistoryTable').DataTable({
                pageLength: 25,
                ordering: true,
                order: [[1, 'asc'], [2, 'asc'], [4, 'asc']],
                responsive: false,
                scrollX: true,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]
            });
        }

        if (typeof flatpickr !== 'undefined') {
            flatpickr("#start_date", {
                dateFormat: "m/d/Y",
                allowInput: true
            });

            flatpickr("#end_date", {
                dateFormat: "m/d/Y",
                allowInput: true
            });
        }

    });
</script>
@endpush
