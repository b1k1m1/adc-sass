<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Client Activity Assessment</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        h3 {
            text-align: center;
            margin-bottom: 18px;
            font-size: 16px;
            letter-spacing: 0.5px;
        }

        h4 {
            margin: 10px 0 6px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px 4px;
            vertical-align: top;
        }

        .border-table th,
        .border-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        .center {
            text-align: center;
        }

        .line {
            border-bottom: 1px solid #000;
            min-height: 16px;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        .small {
            font-size: 10px;
        }

        .summary-box {
            border-bottom: 1px solid #000;
            min-height: 80px;
            white-space: pre-line;
        }
    </style>
</head>

<body>

@php
    $activitiesList = [
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

    $needsList = [
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

    $activities = $assessment->activities ?? [];
    $functionalNeeds = $assessment->functional_needs ?? [];

    $clientName = trim(($member->LastName ?? '') . ', ' . ($member->FirstName ?? '') . ' ' . ($member->MiddleName ?? ''));
@endphp

<h3>CLIENT ACTIVITY ASSESSMENT</h3>

<h4>CLIENT INFORMATION</h4>

<table class="info-table mb-10">
    <tr>
        <td style="width: 25%;">Client Name:</td>
        <td style="width: 45%;" class="line">{{ $clientName }}</td>
        <td style="width: 12%;">Shift:</td>
        <td style="width: 18%;" class="line">
            {{ $assessment->shift === 'N' ? 'Noon' : 'Morning' }}
        </td>
    </tr>

    <tr>
        <td>Assessment Date:</td>
        <td class="line">
            {{ optional($assessment->assessment_date)->format('m/d/Y') }}
        </td>
        <td>Participant ID:</td>
        <td class="line">
            {{ str_pad($assessment->ParticipantId, 3, '0', STR_PAD_LEFT) }}
        </td>
    </tr>

    <tr>
        <td>Reason for Assessment:</td>
        <td colspan="3">
            ___ Initial Admission Assessment
            &nbsp;&nbsp;&nbsp;
            ___ Annual Assessment
            &nbsp;&nbsp;&nbsp;
            ___ Update
            <br>
            <strong>
                @if($assessment->assessment_type === 'initial_admission')
                    Initial Admission Assessment
                @elseif($assessment->assessment_type === 'annual')
                    Annual Assessment
                @elseif($assessment->assessment_type === 'update')
                    Update
                @endif
            </strong>
        </td>
    </tr>

    <tr>
        <td>Diagnosis:</td>
        <td colspan="3" class="line">{{ $assessment->diagnosis }}</td>
    </tr>

    <tr>

        @php
            function formatAssessmentPhone($phone)
            {
                $digits = preg_replace('/\D/', '', (string) $phone);

                if (strlen($digits) === 10) {
                    return '(' . substr($digits, 0, 3) . ') ' .
                        substr($digits, 3, 3) . ' - ' .
                        substr($digits, 6, 4);
                }

                return $phone;
            }
        @endphp
        <tr>
            <td style="width: 14%;">Date of Birth:</td>
            <td style="width: 18%;" class="line">
                {{ optional($assessment->date_of_birth)->format('m/d/Y') }}
            </td>

            <td style="width: 18%;">Responsible Party:</td>
            <td style="width: 25%;" class="line">
                {{ $assessment->responsible_party }}
            </td>

            <td style="width: 8%;">Phone #:</td>
            <td style="width: 17%;" class="line">
                {{ formatAssessmentPhone($assessment->phone) }}
            </td>
        </tr>
    </tr>
</table>

<p>
    <strong>Activity Preferences</strong>
    <br>
    {{ $assessment->activity_preferences }}
</p>

<table class="border-table">
    <thead>
        <tr>
            <th style="width: 12%;">Current</th>
            <th style="width: 12%;">Past</th>
            <th style="width: 14%;">No Interest</th>
            <th style="width: 32%;">Activities</th>
            <th style="width: 30%;">Notes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($activitiesList as $key => $label)
            @php
                $row = $activities[$key] ?? [];
            @endphp
            <tr>
                <td class="center">{{ !empty($row['current']) ? 'X' : '' }}</td>
                <td class="center">{{ !empty($row['past']) ? 'X' : '' }}</td>
                <td class="center">{{ !empty($row['no_interest']) ? 'X' : '' }}</td>
                <td>{{ $label }}</td>
                <td>{{ $row['notes'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="page-break"></div>

<table class="info-table mb-10">
    <tr>
        <td style="width: 18%;">Client Name:</td>
        <td style="width: 52%;" class="line">{{ $clientName }}</td>
        <td style="width: 15%;">Admission Date:</td>
        <td style="width: 15%;" class="line">
            {{ optional($assessment->admission_date)->format('m/d/Y') }}
        </td>
    </tr>
</table>

<table class="border-table">
    <tbody>
        @foreach($needsList as $key => $label)
            @php
                $need = $functionalNeeds[$key] ?? [];
                $status = $need['status'] ?? '';
                $notes = $need['notes'] ?? '';
            @endphp
            <tr>
                <td style="width: 25%;">{{ $label }}</td>
                <td style="width: 20%;">
                    @if($status === 'adequate')
                        Adequate
                    @elseif($status === 'needs')
                        Needs
                    @elseif($status === 'none')
                        None
                    @endif
                </td>
                <td style="width: 55%;">{{ $notes }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<br>

<h4>11. Activity-Related Doctor Orders</h4>

<table class="info-table">
    <tr>
        <td style="width: 8%;" class="center">{{ $assessment->may_participate_activities ? 'X' : '' }}</td>
        <td>May participate in activities as tolerated</td>
    </tr>
    <tr>
        <td class="center">{{ $assessment->may_have_alcoholic_beverages ? 'X' : '' }}</td>
        <td>May have alcoholic beverages</td>
    </tr>
    <tr>
        <td class="center">{{ $assessment->may_leave_facility_trips ? 'X' : '' }}</td>
        <td>May leave the facility to go on field trips</td>
    </tr>
    <tr>
        <td class="center">{{ $assessment->may_participate_volunteer_work ? 'X' : '' }}</td>
        <td>May participate in client volunteer or work</td>
    </tr>
    <tr>
        <td>Other:</td>
        <td class="line">{{ $assessment->doctor_orders_other }}</td>
    </tr>
</table>

<br>

<h4>SUMMARY:</h4>
<div class="summary-box">
    {{ $assessment->summary }}
</div>

<br><br>

<table class="info-table">
    <tr>
        <td style="width: 18%;">Completed by:</td>
        <td style="width: 32%;" class="line">{{ $assessment->completed_by }}</td>
        <td style="width: 10%;">Title:</td>
        <td style="width: 20%;" class="line">{{ $assessment->completed_title }}</td>
        <td style="width: 8%;">Date:</td>
        <td style="width: 12%;" class="line">
            {{ optional($assessment->completed_date)->format('m/d/Y') }}
        </td>
    </tr>
</table>

</body>
</html>
