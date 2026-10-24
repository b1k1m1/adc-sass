@extends('admin.admin_dashboard')

@section('title', $isEdit ? 'Edit Initial Assessment' : 'Add Initial Assessment')
@section('page-title', $isEdit ? 'Edit Initial Assessment' : 'Add Initial Assessment')

@section('page-actions')
<a href="{{ route('applicants.index') }}" class="btn btn-secondary">
    Back
</a>

@if($isEdit)
    <a href="{{ route('applicants.pdf', $applicant->id) }}" target="_blank" class="btn btn-danger">
        Export PDF
    </a>
@endif
@endsection

@section('admin')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the following:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    function checkedArray($array, $value) {
        return is_array($array) && in_array($value, $array) ? 'checked' : '';
    }

    function interestValue($array, $key, $value) {
        return is_array($array) && (($array[$key] ?? '') === $value) ? 'checked' : '';
    }
@endphp

<div class="page-content">
    <div class="container-fluid">
        <form method="POST"
            action="{{ $isEdit ? route('applicants.update', $applicant->id) : route('applicants.store') }}">
            @csrf

            @if($isEdit)
                @method('PUT')
            @endif

                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        Initial Assessment
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-2">
                                <label class="form-label">Form #</label>
                                <input type="number"
                                    name="form_no"
                                    class="form-control"
                                    value="{{ old('form_no', $applicant->form_no) }}"
                                    required>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Participant's Name</label>
                                <input type="text"
                                    name="participant_name"
                                    class="form-control"
                                    value="{{ old('participant_name', $applicant->participant_name) }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Birthday</label>
                                <input type="text"
                                    name="birthday"
                                    class="form-control"
                                    placeholder="mm/dd/yyyy"
                                    value="{{ old('birthday', optional($applicant->birthday)->format('m/d/Y')) }}">
                            </div>

                            <div class="col-md-1">
                                <label class="form-label">Age</label>
                                <input type="text"
                                    name="age"
                                    class="form-control"
                                    value="{{ old('age', $applicant->age) }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Religion</label>
                                <input type="text"
                                    name="religion"
                                    class="form-control"
                                    value="{{ old('religion', $applicant->religion) }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">No. of Children</label>
                                <input type="text"
                                    name="no_of_children"
                                    class="form-control"
                                    value="{{ old('no_of_children', $applicant->no_of_children) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">No. of Grandchildren</label>
                                <input type="text"
                                    name="no_of_grandchildren"
                                    class="form-control"
                                    value="{{ old('no_of_grandchildren', $applicant->no_of_grandchildren) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Past Occupation</label>
                                <input type="text"
                                    name="past_occupation"
                                    class="form-control"
                                    value="{{ old('past_occupation', $applicant->past_occupation) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">No. of Years</label>
                                <input type="text"
                                    name="no_of_years"
                                    class="form-control"
                                    value="{{ old('no_of_years', $applicant->no_of_years) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Current Living Conditions</label>
                                <input type="text"
                                    name="current_living_conditions"
                                    class="form-control"
                                    value="{{ old('current_living_conditions', $applicant->current_living_conditions) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">HHA Services</label>
                                <input type="text"
                                    name="hha_services"
                                    class="form-control"
                                    value="{{ old('hha_services', $applicant->hha_services) }}">
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        Physical Skills
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Physical limitations</label>
                            <textarea name="physical_limitations" class="form-control" rows="2">{{ old('physical_limitations', $applicant->physical_limitations) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Effect on participation</label>
                            <textarea name="effect_on_participation" class="form-control" rows="2">{{ old('effect_on_participation', $applicant->effect_on_participation) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <h6>Mobility</h6>
                                @foreach($options['mobility'] as $item)
                                    <div class="form-check">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            name="mobility[]"
                                            value="{{ $item }}"
                                            {{ checkedArray(old('mobility', $applicant->mobility), $item) }}>
                                        <label class="form-check-label">{{ $item }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-4">
                                <h6>Motor</h6>
                                @foreach($options['motor'] as $item)
                                    <div class="form-check">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            name="motor[]"
                                            value="{{ $item }}"
                                            {{ checkedArray(old('motor', $applicant->motor), $item) }}>
                                        <label class="form-check-label">{{ $item }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-4">
                                <h6>Sensory</h6>

                                @foreach($options['sensory'] as $item)
                                    <div class="form-check">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            name="sensory[]"
                                            value="{{ $item }}"
                                            {{ checkedArray(old('sensory', $applicant->sensory), $item) }}>
                                        <label class="form-check-label">{{ $item }}</label>
                                    </div>
                                @endforeach

                                <label class="form-label mt-2">Vision problems specify</label>
                                <input type="text"
                                    name="vision_problem_specify"
                                    class="form-control"
                                    value="{{ old('vision_problem_specify', $applicant->vision_problem_specify) }}">

                                <label class="form-label mt-2">Hearing problems specify</label>
                                <input type="text"
                                    name="hearing_problem_specify"
                                    class="form-control"
                                    value="{{ old('hearing_problem_specify', $applicant->hearing_problem_specify) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        Cognitive Status
                    </div>

                    <div class="card-body">
                        <label class="form-label">Attention Span</label>
                        <div class="mb-3">
                            @foreach($options['attention_span'] as $span)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                        type="radio"
                                        name="attention_span"
                                        value="{{ $span }}"
                                        {{ old('attention_span', $applicant->attention_span) == $span ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $span }} minutes</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="row">
                            @foreach($options['cognitive_status'] as $item)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            name="cognitive_status[]"
                                            value="{{ $item }}"
                                            {{ checkedArray(old('cognitive_status', $applicant->cognitive_status), $item) }}>
                                        <label class="form-check-label">{{ $item }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Other</label>
                                <input type="text"
                                    name="cognitive_other"
                                    class="form-control"
                                    value="{{ old('cognitive_other', $applicant->cognitive_other) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">How Often Attends Program</label>
                                <input type="text"
                                    name="how_often_attends_program"
                                    class="form-control"
                                    value="{{ old('how_often_attends_program', $applicant->how_often_attends_program) }}">
                            </div>
                        </div>
                    </div>
                </div>

                @include('applicants.partials.checkbox-section', [
                    'title' => 'Emotional Reactions',
                    'field' => 'emotional_reactions',
                    'items' => $options['emotional_reactions'],
                    'values' => old('emotional_reactions', $applicant->emotional_reactions),
                    'otherField' => 'emotional_other',
                    'otherValue' => old('emotional_other', $applicant->emotional_other),
                ])

                @include('applicants.partials.checkbox-section', [
                    'title' => 'Task / Technical Skills',
                    'field' => 'task_technical_skills',
                    'items' => $options['task_technical_skills'],
                    'values' => old('task_technical_skills', $applicant->task_technical_skills),
                    'otherField' => 'task_other',
                    'otherValue' => old('task_other', $applicant->task_other),
                ])

                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        Interpersonal / Group Skills
                    </div>

                    <div class="card-body">
                        <div class="row">
                            @foreach($options['interpersonal_group_skills'] as $item)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            name="interpersonal_group_skills[]"
                                            value="{{ $item }}"
                                            {{ checkedArray(old('interpersonal_group_skills', $applicant->interpersonal_group_skills), $item) }}>
                                        <label class="form-check-label">{{ $item }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Other</label>
                                <input type="text"
                                    name="interpersonal_other"
                                    class="form-control"
                                    value="{{ old('interpersonal_other', $applicant->interpersonal_other) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Level of interaction</label>
                                <select name="level_of_interaction" class="form-select">
                                    <option value="">Select</option>
                                    @foreach($options['level_of_interaction'] as $item)
                                        <option value="{{ $item }}"
                                            {{ old('level_of_interaction', $applicant->level_of_interaction) == $item ? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 mt-3">
                                <label class="form-label">Comments</label>
                                <textarea name="interpersonal_comments" class="form-control" rows="2">{{ old('interpersonal_comments', $applicant->interpersonal_comments) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        Interests / Activities
                        <small class="ms-2">(P = present, X = past interest)</small>
                    </div>

                    <div class="card-body">

                        @php
                            $interestOldValues = old('interests_activities', $applicant->interests_activities ?? []);
                            $interestRows = array_chunk($options['interests_activities'], 3);
                        @endphp

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 22%;">Activity</th>
                                        <th class="text-center" style="width: 4%;">P</th>
                                        <th class="text-center" style="width: 4%;">X</th>
                                        <th class="text-center" style="width: 8%;">Blank</th>

                                        <th style="width: 22%;">Activity</th>
                                        <th class="text-center" style="width: 4%;">P</th>
                                        <th class="text-center" style="width: 4%;">X</th>
                                        <th class="text-center" style="width: 8%;">Blank</th>

                                        <th style="width: 22%;">Activity</th>
                                        <th class="text-center" style="width: 4%;">P</th>
                                        <th class="text-center" style="width: 4%;">X</th>
                                        <th class="text-center" style="width: 8%;">Blank</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($interestRows as $rowItems)
                                        <tr>
                                            @foreach($rowItems as $item)
                                                @php
                                                    $currentValue = $interestOldValues[$item] ?? '';
                                                @endphp

                                                <td>
                                                    <strong>{{ $item }}</strong>
                                                </td>

                                                <td class="text-center">
                                                    <input type="radio"
                                                        name="interests_activities[{{ $item }}]"
                                                        value="P"
                                                        {{ $currentValue === 'P' ? 'checked' : '' }}>
                                                </td>

                                                <td class="text-center">
                                                    <input type="radio"
                                                        name="interests_activities[{{ $item }}]"
                                                        value="X"
                                                        {{ $currentValue === 'X' ? 'checked' : '' }}>
                                                </td>

                                                <td class="text-center">
                                                    <input type="radio"
                                                        name="interests_activities[{{ $item }}]"
                                                        value=""
                                                        {{ $currentValue === '' ? 'checked' : '' }}>
                                                </td>
                                            @endforeach

                                            @for($i = count($rowItems); $i < 3; $i++)
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            @endfor
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Other</label>
                            <input type="text"
                                name="interests_other"
                                class="form-control"
                                value="{{ old('interests_other', $applicant->interests_other) }}">
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        Objectives
                    </div>

                    <div class="card-body">
                        @foreach($options['objectives'] as $item)
                            <div class="form-check">
                                <input class="form-check-input"
                                    type="checkbox"
                                    name="objectives[]"
                                    value="{{ $item }}"
                                    {{ checkedArray(old('objectives', $applicant->objectives), $item) }}>
                                <label class="form-check-label">{{ $item }}</label>
                            </div>
                        @endforeach

                        <div class="mt-3">
                            <label class="form-label">
                                How participant would like to benefit from Activities Program
                            </label>
                            <textarea name="benefit_from_activities" class="form-control" rows="3">{{ old('benefit_from_activities', $applicant->benefit_from_activities) }}</textarea>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Activities related restrictions</label>
                            <textarea name="activities_restrictions" class="form-control" rows="3">{{ old('activities_restrictions', $applicant->activities_restrictions) }}</textarea>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Comments / Plan</label>
                            <textarea name="comments_plan" class="form-control" rows="3">{{ old('comments_plan', $applicant->comments_plan) }}</textarea>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Signature - Activities Staff</label>
                                <input type="text"
                                    name="staff_signature"
                                    class="form-control"
                                    value="{{ old('staff_signature', $applicant->staff_signature) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Date</label>
                                <input type="text"
                                    name="signature_date"
                                    class="form-control"
                                    placeholder="mm/dd/yyyy"
                                    value="{{ old('signature_date', optional($applicant->signature_date)->format('m/d/Y')) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mb-5">
                    <button type="submit" class="btn btn-success">
                        {{ $isEdit ? 'Update' : 'Save' }}
                    </button>

                    <a href="{{ route('applicants.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
        </form>
    </div>
</div>

@endsection
