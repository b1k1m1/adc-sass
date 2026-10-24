@extends('admin.admin_dashboard')

@section('admin')

@php
    $mode = $mode ?? 'create';

    $isView = $mode === 'view';
    $isEdit = $mode === 'edit';
    $isCreate = $mode === 'create';

    $pageTitle = 'Initial Client Activity Assessment';

    if ($isView) {
        $pageTitle .= ' - View';
    } elseif ($isEdit) {
        $pageTitle .= ' - Edit';
    } else {
        $pageTitle .= ' - Add New';
    }

    $activities = [
        'cards_games' => 'Cards/Games',
        'crafts_art' => 'Crafts/Art',
        'exercise_sports' => 'Exercise/Sports',
        'music' => 'Music',
        'read_write' => 'Read/Write',
        'spiritual_religious' => 'Spiritual/Religious',
        'trips_shopping' => 'Trips/Shopping',
        'walking_wheeling_outdoors' => 'Walking/Wheeling Outdoors',
        'watch_tv' => 'Watch TV',
        'gardening_plants' => 'Gardening/Plants',
        'talking_conversing' => 'Talking/Conversing (Social skills)',
        'helping_others' => 'Helping Others (resident council volunteering)',
        'mental_educational' => 'Mental/Educational',
        'voting' => 'Voting',
        'recreational' => 'Recreational (movies/puzzles/pets/hobbies)',
        'other' => 'Other',
    ];

    $needs = [
        'vision' => '1. Vision',
        'hearing' => '2. Hearing',
        'communication' => '3. Communication',
        'mobility' => '4. Mobility',
        'fine_motor' => '5. Fine Motor',
        'orientation' => '6. Orientation',
        'psychosocial' => '7. Psychosocial',
        'dietary_allergy' => '8. Dietary/Allergy',
        'precautions' => '9. Precautions',
        'other_needs' => '10. Other Needs',
    ];

    $assessmentActivities = [];

    if (!empty($assessment->activities)) {
        if (is_array($assessment->activities)) {
            $assessmentActivities = $assessment->activities;
        } else {
            $assessmentActivities = json_decode($assessment->activities, true) ?: [];
        }
    }

    $assessmentNeeds = [];

    if (!empty($assessment->functional_needs)) {
        if (is_array($assessment->functional_needs)) {
            $assessmentNeeds = $assessment->functional_needs;
        } else {
            $assessmentNeeds = json_decode($assessment->functional_needs, true) ?: [];
        }
    }

    $assessmentDate = !empty($assessment->assessment_date)
        ? \Carbon\Carbon::parse($assessment->assessment_date)->format('m/d/Y')
        : now()->format('m/d/Y');

    $dob = !empty($assessment->date_of_birth)
        ? \Carbon\Carbon::parse($assessment->date_of_birth)->format('m/d/Y')
        : (!empty($member->DateOfBirth) ? \Carbon\Carbon::parse($member->DateOfBirth)->format('m/d/Y') : '');

    $admissionDate = !empty($assessment->admission_date)
        ? \Carbon\Carbon::parse($assessment->admission_date)->format('m/d/Y')
        : '';

    $completedDate = !empty($assessment->completed_date)
        ? \Carbon\Carbon::parse($assessment->completed_date)->format('m/d/Y')
        : '';

    function activityAssessmentPhoneDisplay($phone)
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (strlen($digits) === 10) {
            return '(' . substr($digits, 0, 3) . ') ' .
                   substr($digits, 3, 3) . ' - ' .
                   substr($digits, 6, 4);
        }

        return $phone;
    }

    $phoneDisplay = activityAssessmentPhoneDisplay($assessment->phone ?? '');
@endphp

<div class="page-content">
    <div class="container-fluid">

        <div class="row mb-3">
            <div class="col-md-6">
                <h4 class="mb-0">{{ $pageTitle }}</h4>
            </div>

            <div class="col-md-6 text-end">
                @if(!empty($assessment->id))
                    <a href="{{ route('participant.activity.assessment.pdf', $assessment->id) }}"
                       target="_blank"
                       class="btn btn-danger">
                        PDF
                    </a>
                @endif

                <a href="{{ route('participant.activity.assessment.index', ['shift' => $shift]) }}"
                   class="btn btn-secondary">
                    Back
                </a>
            </div>
        </div>

        <form id="activityAssessmentForm">
            @csrf

            <input type="hidden" name="assessment_id" id="assessment_id" value="{{ $assessment->id ?? '' }}">
            <input type="hidden" name="shift" id="shift" value="{{ $shift }}">
            <input type="hidden" name="ParticipantId" id="ParticipantId" value="{{ $participantId }}">

            <div class="card">
                <div class="card-body">

                    {{-- Header --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Shift</label>
                            <input type="text"
                                   class="form-control"
                                   value="{{ $shift === 'N' ? 'Noon' : 'Morning' }}"
                                   readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Participant</label>
                            <input type="text"
                                   class="form-control"
                                   value="{{ str_pad($participantId, 3, '0', STR_PAD_LEFT) }} - {{ $member->LastName ?? '' }}, {{ $member->FirstName ?? '' }}"
                                   readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Assessment Date</label>
                            <input type="text"
                                   name="assessment_date"
                                   id="assessment_date"
                                   class="form-control"
                                   value="{{ $assessmentDate }}"
                                   placeholder="MM/DD/YYYY"
                                   {{ $isView ? 'readonly' : '' }}>
                        </div>
                    </div>

                    <hr>

                    {{-- Client Information --}}
                    <h5>Client Information</h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Reason for Assessment</label>
                            <select name="assessment_type"
                                    id="assessment_type"
                                    class="form-select"
                                    {{ $isView ? 'disabled' : '' }}>
                                <option value="">Select</option>
                                <option value="initial_admission"
                                    {{ ($assessment->assessment_type ?? '') === 'initial_admission' ? 'selected' : '' }}>
                                    Initial Admission Assessment
                                </option>
                                <option value="annual"
                                    {{ ($assessment->assessment_type ?? '') === 'annual' ? 'selected' : '' }}>
                                    Annual Assessment
                                </option>
                                <option value="update"
                                    {{ ($assessment->assessment_type ?? '') === 'update' ? 'selected' : '' }}>
                                    Update
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="text"
                                   name="date_of_birth"
                                   id="date_of_birth"
                                   class="form-control date-field"
                                   value="{{ $dob }}"
                                   placeholder="MM/DD/YYYY"
                                   {{ $isView ? 'readonly' : '' }}>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Admission Date</label>
                            <input type="text"
                                   name="admission_date"
                                   id="admission_date"
                                   class="form-control date-field"
                                   value="{{ $admissionDate }}"
                                   placeholder="MM/DD/YYYY"
                                   {{ $isView ? 'readonly' : '' }}>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Diagnosis</label>
                            <textarea name="diagnosis"
                                      id="diagnosis"
                                      class="form-control"
                                      rows="2"
                                      {{ $isView ? 'readonly' : '' }}>{{ $assessment->diagnosis ?? '' }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Responsible Party</label>
                            <input type="text"
                                   name="responsible_party"
                                   id="responsible_party"
                                   class="form-control"
                                   value="{{ $assessment->responsible_party ?? '' }}"
                                   {{ $isView ? 'readonly' : '' }}>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone #</label>
                            <input type="text"
                                   name="phone"
                                   id="phone"
                                   class="form-control"
                                   maxlength="16"
                                   placeholder="(XXX) XXX - XXXX"
                                   value="{{ $phoneDisplay }}"
                                   {{ $isView ? 'readonly' : '' }}>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">
                                Activity Preferences
                            </label>
                            <textarea name="activity_preferences"
                                      id="activity_preferences"
                                      class="form-control"
                                      rows="3"
                                      {{ $isView ? 'readonly' : '' }}>{{ $assessment->activity_preferences ?? '' }}</textarea>
                        </div>
                    </div>

                    {{-- Activities --}}
                    <h5>Activities</h5>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Current</th>
                                    <th style="width: 100px;">Past</th>
                                    <th style="width: 120px;">No Interest</th>
                                    <th>Activities</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activities as $key => $label)
                                    @php
                                        $row = $assessmentActivities[$key] ?? [];
                                    @endphp

                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="activities[{{ $key }}][current]"
                                                   value="1"
                                                   class="form-check-input activity-check"
                                                   {{ !empty($row['current']) ? 'checked' : '' }}
                                                   {{ $isView ? 'disabled' : '' }}>
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="activities[{{ $key }}][past]"
                                                   value="1"
                                                   class="form-check-input activity-check"
                                                   {{ !empty($row['past']) ? 'checked' : '' }}
                                                   {{ $isView ? 'disabled' : '' }}>
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="activities[{{ $key }}][no_interest]"
                                                   value="1"
                                                   class="form-check-input activity-check"
                                                   {{ !empty($row['no_interest']) ? 'checked' : '' }}
                                                   {{ $isView ? 'disabled' : '' }}>
                                        </td>

                                        <td>{{ $label }}</td>

                                        <td>
                                            <input type="text"
                                                   name="activities[{{ $key }}][notes]"
                                                   class="form-control form-control-sm activity-note"
                                                   value="{{ $row['notes'] ?? '' }}"
                                                   {{ $isView ? 'readonly' : '' }}>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Functional Needs --}}
                    <h5>Functional Needs</h5>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 220px;">Area</th>
                                    <th style="width: 180px;">Status</th>
                                    <th>Needs / Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($needs as $key => $label)
                                    @php
                                        $need = $assessmentNeeds[$key] ?? [];
                                    @endphp

                                    <tr>
                                        <td>{{ $label }}</td>

                                        <td>
                                            <select name="functional_needs[{{ $key }}][status]"
                                                    class="form-select form-select-sm need-status"
                                                    {{ $isView ? 'disabled' : '' }}>
                                                <option value="">Select</option>
                                                <option value="adequate"
                                                    {{ ($need['status'] ?? '') === 'adequate' ? 'selected' : '' }}>
                                                    Adequate
                                                </option>
                                                <option value="needs"
                                                    {{ ($need['status'] ?? '') === 'needs' ? 'selected' : '' }}>
                                                    Needs
                                                </option>
                                                <option value="none"
                                                    {{ ($need['status'] ?? '') === 'none' ? 'selected' : '' }}>
                                                    None
                                                </option>
                                            </select>
                                        </td>

                                        <td>
                                            <input type="text"
                                                   name="functional_needs[{{ $key }}][notes]"
                                                   class="form-control form-control-sm need-notes"
                                                   value="{{ $need['notes'] ?? '' }}"
                                                   {{ $isView ? 'readonly' : '' }}>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Doctor Orders --}}
                    <h5>Activity-Related Doctor Orders</h5>

                    <div class="row g-2 mb-4">
                        <div class="col-md-6">
                            <label>
                                <input type="checkbox"
                                       name="may_participate_activities"
                                       id="may_participate_activities"
                                       value="1"
                                       {{ !empty($assessment->may_participate_activities) ? 'checked' : '' }}
                                       {{ $isView ? 'disabled' : '' }}>
                                May participate in activities as tolerated
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label>
                                <input type="checkbox"
                                       name="may_have_alcoholic_beverages"
                                       id="may_have_alcoholic_beverages"
                                       value="1"
                                       {{ !empty($assessment->may_have_alcoholic_beverages) ? 'checked' : '' }}
                                       {{ $isView ? 'disabled' : '' }}>
                                May have alcoholic beverages
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label>
                                <input type="checkbox"
                                       name="may_leave_facility_trips"
                                       id="may_leave_facility_trips"
                                       value="1"
                                       {{ !empty($assessment->may_leave_facility_trips) ? 'checked' : '' }}
                                       {{ $isView ? 'disabled' : '' }}>
                                May leave the facility to go on field trips
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label>
                                <input type="checkbox"
                                       name="may_participate_volunteer_work"
                                       id="may_participate_volunteer_work"
                                       value="1"
                                       {{ !empty($assessment->may_participate_volunteer_work) ? 'checked' : '' }}
                                       {{ $isView ? 'disabled' : '' }}>
                                May participate in client volunteer or work
                            </label>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Other</label>
                            <input type="text"
                                   name="doctor_orders_other"
                                   id="doctor_orders_other"
                                   class="form-control"
                                   value="{{ $assessment->doctor_orders_other ?? '' }}"
                                   {{ $isView ? 'readonly' : '' }}>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <h5>Summary</h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <textarea name="summary"
                                      id="summary"
                                      class="form-control"
                                      rows="4"
                                      {{ $isView ? 'readonly' : '' }}>{{ $assessment->summary ?? '' }}</textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Completed By</label>
                            <input type="text"
                                   name="completed_by"
                                   id="completed_by"
                                   class="form-control"
                                   value="{{ $assessment->completed_by ?? '' }}"
                                   {{ $isView ? 'readonly' : '' }}>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Title</label>
                            <input type="text"
                                   name="completed_title"
                                   id="completed_title"
                                   class="form-control"
                                   value="{{ $assessment->completed_title ?? '' }}"
                                   {{ $isView ? 'readonly' : '' }}>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Completed Date</label>
                            <input type="text"
                                   name="completed_date"
                                   id="completed_date"
                                   class="form-control date-field"
                                   value="{{ $completedDate }}"
                                   placeholder="MM/DD/YYYY"
                                   {{ $isView ? 'readonly' : '' }}>
                        </div>
                    </div>

                    <div class="text-end">
                        @if(!$isView)
                            <button type="submit" class="btn btn-primary">
                                Save / Update Assessment
                            </button>
                        @endif

                        @if(!empty($assessment->id))
                            <a href="{{ route('participant.activity.assessment.pdf', $assessment->id) }}"
                               target="_blank"
                               class="btn btn-danger">
                                PDF
                            </a>
                        @endif

                        <a href="{{ route('participant.activity.assessment.index', ['shift' => $shift]) }}"
                           class="btn btn-secondary">
                            Back
                        </a>
                    </div>

                </div>
            </div>
        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    let isViewMode = "{{ $isView ? 'Y' : 'N' }}";

    flatpickr("#assessment_date", {
        dateFormat: "m/d/Y",
        allowInput: true
    });

    flatpickr(".date-field", {
        dateFormat: "m/d/Y",
        allowInput: true
    });

    function convertDateToMysql(value) {
        if (!value) {
            return '';
        }

        let parts = value.split('/');

        if (parts.length !== 3) {
            return value;
        }

        return parts[2] + '-' +
            parts[0].padStart(2, '0') + '-' +
            parts[1].padStart(2, '0');
    }

    function formatPhoneDisplay(value) {
        if (!value) {
            return '';
        }

        let digits = String(value).replace(/\D/g, '').substring(0, 10);

        if (digits.length <= 3) {
            return digits;
        }

        if (digits.length <= 6) {
            return '(' + digits.substring(0, 3) + ') ' +
                digits.substring(3);
        }

        return '(' + digits.substring(0, 3) + ') ' +
            digits.substring(3, 6) + ' - ' +
            digits.substring(6, 10);
    }

    function cleanPhoneDigits(value) {
        if (!value) {
            return '';
        }

        return String(value).replace(/\D/g, '').substring(0, 10);
    }

    $('#phone').on('input', function () {
        $(this).val(formatPhoneDisplay($(this).val()));
    });

    $('#activityAssessmentForm').on('submit', function (e) {
        e.preventDefault();

        if (isViewMode === 'Y') {
            return false;
        }

        $('#phone').val(cleanPhoneDigits($('#phone').val()));

        let formData = $(this).serializeArray();

        formData.push({
            name: 'assessment_date',
            value: convertDateToMysql($('#assessment_date').val())
        });

        formData.push({
            name: 'date_of_birth',
            value: convertDateToMysql($('#date_of_birth').val())
        });

        formData.push({
            name: 'admission_date',
            value: convertDateToMysql($('#admission_date').val())
        });

        formData.push({
            name: 'completed_date',
            value: convertDateToMysql($('#completed_date').val())
        });

        $.ajax({
            url: "{{ route('participant.activity.assessment.store') }}",
            type: "POST",
            data: $.param(formData),
            success: function (response) {
                $('#phone').val(formatPhoneDisplay($('#phone').val()));

                Swal.fire('Saved', response.message, 'success').then(function () {
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                    }
                });
            },
            error: function (xhr) {
                $('#phone').val(formatPhoneDisplay($('#phone').val()));

                let message = 'Unable to save assessment.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire('Error', message, 'error');
            }
        });
    });

});
</script>
@endpush
