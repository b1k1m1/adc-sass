<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Initial Assessment</title>

    <style>
        body {
            font-family: dejavusans, sans-serif;
            font-size: 10.5pt;
            color: #000;
        }

        h2 {
            text-align: center;
            margin: 0 0 10px 0;
            font-size: 15pt;
            letter-spacing: 1px;
        }

        .form-no {
            text-align: right;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .section-title {
            font-weight: bold;
            font-size: 12pt;
            margin-top: 8px;
            border-top: 1px dashed #000;
            padding-top: 5px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding: 3px 4px;
        }

        .line {
            border-bottom: 1px solid #000;
            min-height: 16px;
        }

        .label {
            font-weight: bold;
        }

        .checkbox {
            font-family: dejavusans, sans-serif;
            font-size: 11pt;
        }

        .small {
            font-size: 9pt;
        }

        .page-break {
            page-break-after: always;
        }

        .text-box {
            border-bottom: 1px solid #000;
            min-height: 40px;
            padding: 3px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 22px;
        }
    </style>
</head>
<body>

@php
    function yesBox($array, $value) {
        return is_array($array) && in_array($value, $array) ? '☑' : '☐';
    }

    function safeText($value) {
        return e($value ?? '');
    }

    $interestValues = $applicant->interests_activities ?? [];
@endphp

<div class="form-no">Form #: {{ $applicant->form_no }}</div>

<h2>INITIAL ASSESSMENT</h2>

<table>
    <tr>
        <td width="20%">Participant's Name:</td>
        <td width="40%" class="line">{{ $applicant->participant_name }}</td>
        <td width="10%">Birthday:</td>
        <td width="15%" class="line">{{ optional($applicant->birthday)->format('m/d/Y') }}</td>
        <td width="5%">Age:</td>
        <td width="10%" class="line">{{ $applicant->age }}</td>
    </tr>
</table>

<table>
    <tr>
        <td width="15%">Religion:</td>
        <td width="30%" class="line">{{ $applicant->religion }}</td>
        <td width="20%">No. of Children:</td>
        <td width="10%" class="line">{{ $applicant->no_of_children }}</td>
        <td width="20%">No. of Grandchildren:</td>
        <td width="10%" class="line">{{ $applicant->no_of_grandchildren }}</td>
    </tr>
</table>

<table>
    <tr>
        <td width="20%">Past Occupation:</td>
        <td width="35%" class="line">{{ $applicant->past_occupation }}</td>
        <td width="15%">No. of Years:</td>
        <td width="30%" class="line">{{ $applicant->no_of_years }}</td>
    </tr>
</table>

<table>
    <tr>
        <td width="25%">Current Living Conditions:</td>
        <td width="35%" class="line">{{ $applicant->current_living_conditions }}</td>
        <td width="15%">HHA Services:</td>
        <td width="25%" class="line">{{ $applicant->hha_services }}</td>
    </tr>
</table>

<div class="section-title">Physical Skills</div>

<table>
    <tr>
        <td width="25%">Physical limitations:</td>
        <td class="line">{{ $applicant->physical_limitations }}</td>
    </tr>
    <tr>
        <td>Effect on participation:</td>
        <td class="line">{{ $applicant->effect_on_participation }}</td>
    </tr>
</table>

<table>
    <tr>
        <td width="33%">
            <strong>MOBILITY:</strong><br>
            @foreach($options['mobility'] as $item)
                {{ yesBox($applicant->mobility, $item) }} {{ $item }}<br>
            @endforeach
        </td>

        <td width="33%">
            <strong>MOTOR:</strong><br>
            @foreach($options['motor'] as $item)
                {{ yesBox($applicant->motor, $item) }} {{ $item }}<br>
            @endforeach
        </td>

        <td width="34%">
            <strong>SENSORY:</strong><br>
            {{ yesBox($applicant->sensory, 'Vision problems') }} Vision problems:
            {{ $applicant->vision_problem_specify }}<br>

            {{ yesBox($applicant->sensory, 'Hearing problems') }} Hearing problems:
            {{ $applicant->hearing_problem_specify }}
        </td>
    </tr>
</table>

<div class="section-title">Cognitive Status</div>

<p>
    <strong>Attention Span:</strong>
    @foreach($options['attention_span'] as $span)
        @if($applicant->attention_span == $span)
            <strong>{{ $span }}</strong>
        @else
            {{ $span }}
        @endif
        &nbsp;
    @endforeach
    minutes
</p>

<table>
    <tr>
        @foreach(array_chunk($options['cognitive_status'], 5) as $chunk)
            <td width="33%">
                @foreach($chunk as $item)
                    {{ yesBox($applicant->cognitive_status, $item) }} {{ $item }}<br>
                @endforeach
            </td>
        @endforeach
    </tr>
</table>

<p><strong>Other:</strong> {{ $applicant->cognitive_other }}</p>
<p><strong>How Often Attends Program:</strong> {{ $applicant->how_often_attends_program }}</p>

<div class="page-break"></div>

<h2>INITIAL ASSESSMENT</h2>

<div class="section-title">Emotional Reactions</div>
<table>
    <tr>
        @foreach(array_chunk($options['emotional_reactions'], 5) as $chunk)
            <td width="33%">
                @foreach($chunk as $item)
                    {{ yesBox($applicant->emotional_reactions, $item) }} {{ $item }}<br>
                @endforeach
            </td>
        @endforeach
    </tr>
</table>
<p><strong>Other:</strong> {{ $applicant->emotional_other }}</p>

<div class="section-title">Task / Technical Skills</div>
<table>
    <tr>
        @foreach(array_chunk($options['task_technical_skills'], 6) as $chunk)
            <td width="33%">
                @foreach($chunk as $item)
                    {{ yesBox($applicant->task_technical_skills, $item) }} {{ $item }}<br>
                @endforeach
            </td>
        @endforeach
    </tr>
</table>
<p><strong>Other:</strong> {{ $applicant->task_other }}</p>

<div class="section-title">Interpersonal / Group Skills</div>
<table>
    <tr>
        @foreach(array_chunk($options['interpersonal_group_skills'], 4) as $chunk)
            <td width="33%">
                @foreach($chunk as $item)
                    {{ yesBox($applicant->interpersonal_group_skills, $item) }} {{ $item }}<br>
                @endforeach
            </td>
        @endforeach
    </tr>
</table>

<p><strong>Other:</strong> {{ $applicant->interpersonal_other }}</p>
<p><strong>Comments:</strong> {{ $applicant->interpersonal_comments }}</p>
<p><strong>Level of interaction:</strong> {{ $applicant->level_of_interaction }}</p>

<div class="section-title">
    Interests / Activities <span class="small">(P = present; X = past interest)</span>
</div>

<table>
    <tr>
        @foreach(array_chunk($options['interests_activities'], 9) as $chunk)
            <td width="33%">
                @foreach($chunk as $item)
                    @php $val = $interestValues[$item] ?? ''; @endphp
                    <strong>{{ $val ?: '___' }}</strong> {{ $item }}<br>
                @endforeach
            </td>
        @endforeach
    </tr>
</table>

<p><strong>Other:</strong> {{ $applicant->interests_other }}</p>

<div class="page-break"></div>

<h2>INITIAL ASSESSMENT</h2>

<div class="section-title">Objectives</div>

@foreach($options['objectives'] as $item)
    <p>{{ yesBox($applicant->objectives, $item) }} {{ $item }}</p>
@endforeach

<p>
    <strong>How participant would like to benefit from Activities Program:</strong>
</p>
<div class="text-box">
    {{ $applicant->benefit_from_activities }}
</div>

<p>
    <strong>Activities related restrictions:</strong>
</p>
<div class="text-box">
    {{ $applicant->activities_restrictions }}
</div>

<p>
    <strong>Comments / Plan:</strong>
</p>
<div class="text-box">
    {{ $applicant->comments_plan }}
</div>

<br>

<table>
    <tr>
        <td width="15%">Signature:</td>
        <td width="45%" class="signature-line">
            {{ $applicant->staff_signature }}
        </td>
        <td width="10%">Date:</td>
        <td width="30%" class="signature-line">
            {{ optional($applicant->signature_date)->format('m/d/Y') }}
        </td>
    </tr>
    <tr>
        <td></td>
        <td class="small">(Activities staff)</td>
        <td></td>
        <td></td>
    </tr>
</table>

</body>
</html>
