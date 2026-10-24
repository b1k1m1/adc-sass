@extends('admin.admin_dashboard')
@section('title', 'Pharmacist Review - View')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Pharmacist Review (View)</h4>

            <div class="d-flex gap-2">
                <a href="{{ route('pharmacist_review.index', ['type' => $type, 'participant_id' => $review->participant_id]) }}"
                   class="btn btn-secondary">
                    &laquo; Back
                </a>

                <a href="{{ route('pharmacist_review.pdf', ['id' => $review->id, 'type' => $type]) }}"
                   class="btn btn-danger" target="_blank">
                    PDF
                </a>

                <a href="{{ route('pharmacist_review.edit', ['id' => $review->id, 'type' => $type]) }}"
                   class="btn btn-primary">
                    Edit
                </a>
            </div>
        </div>

        {{-- HEADER --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Header</strong>
                <span class="small text-muted">Shift: {{ $type === 'A' ? 'Morning (A)' : 'Noon (N)' }}</span>
            </div>

            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <strong>Member:</strong>
                        {{ $review->participant_id }}
                        -
                        {{ $member->LastName ?? '' }}, {{ $member->FirstName ?? '' }}
                    </div>

                    <div class="col-md-2">
                        <strong>Medical Record No:</strong>
                        {{ $review->medical_record_no }}
                    </div>

                    <div class="col-md-2">
                        <strong>Review Date:</strong>
                        {{ !empty($review->review_date) ? \Carbon\Carbon::parse($review->review_date)->format('m/d/Y') : '' }}
                    </div>

                    <div class="col-md-2">
                        <strong>Period:</strong>
                        {{ $review->period_type === 'Q' ? 'Quarterly' : 'Monthly' }}
                        ({{ $review->period_key }})
                    </div>

                    <div class="col-md-2">
                        <strong>Status:</strong>
                        {{ ucfirst($review->status ?? 'draft') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- COMMENTS --}}
        <div class="card mb-3">
            <div class="card-header"><strong>Pharmacist Comments</strong></div>
            <div class="card-body">
                <div style="white-space: pre-wrap;">{{ $review->comments }}</div>
            </div>
        </div>

        {{-- MEDS (READ ONLY) --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Medication List (Nurse Maintained)</strong>

                <a href="{{ route('medicine_maint.index', ['type' => $type, 'participant_id' => $review->participant_id]) }}"
                   class="btn btn-outline-primary btn-sm" target="_blank">
                    Open Medication Maintenance
                </a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>Medicine</th>
                                <th style="width:220px;">Physician</th>
                                <th style="width:130px;">Dosage</th>
                                <th style="width:130px;">Frequency</th>
                                <th style="width:220px;">Comments</th>
                                <th style="width:110px;">Status</th>
                                <th style="width:120px;">Status Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nurseMeds as $i => $m)
                                <tr>
                                    <td class="text-center">{{ $i+1 }}</td>
                                    <td>{{ $m->medicine_nm }}</td>
                                    <td>{{ $m->physician_name ?? $m->doctor_id }}</td>
                                    <td>{{ $m->dosage_nm }}</td>
                                    <td>{{ $m->dosage_freq }}</td>
                                    <td>{{ $m->comments }}</td>
                                    <td>{{ ucfirst($m->status ?? 'active') }}</td>
                                    <td>{{ !empty($m->status_date) ? \Carbon\Carbon::parse($m->status_date)->format('m/d/Y') : '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-3">
                                        No medication list found for this member.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="small text-muted mt-2">
                    Note: This list is maintained by nursing staff. Pharmacist cannot edit medications here.
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
