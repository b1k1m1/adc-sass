<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Social Service Quarterly Assessment</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
        }

        h1, h2, h3 {
            margin: 0;
            padding: 0;
        }

        .company-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .company-header h2 {
            font-size: 16pt;
            margin-bottom: 2px;
        }

        .company-header .small {
            font-size: 10pt;
        }

        .title-section {
            text-align: center;
            margin: 10px 0 15px 0;
        }

        .title-section h3 {
            font-size: 14pt;
            text-decoration: underline;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .info-table td {
            padding: 4px 2px;
            vertical-align: top;
        }

        .section-box {
            border: 1px solid #333;
            padding: 8px;
            margin-bottom: 12px;
            border-radius: 4px;
        }

        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 6px;
            font-size: 12pt;
        }

        .label {
            font-weight: bold;
        }

        ul {
            margin: 2px 0 6px 18px;
            padding: 0;
        }

        .small-text {
            font-size: 9.5pt;
        }

        .pre-wrap {
            white-space: pre-wrap;
        }

        .flex-row {
            width: 100%;
            display: flex;
            justify-content: space-between;
        }

        .col-50 {
            width: 48%;
        }

        .col-33 {
            width: 32%;
        }

        .col-25 {
            width: 24%;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="company-header">
        <h4 class="mt-3">{{ $company->comp_nm ?? 'Company Name'  }}</h4>
    </div>

    {{-- FORM TITLE --}}
    <div class="title-section">
        <h3>SOCIAL SERVICE QUARTERLY ASSESSMENT</h3>
    </div>

    {{-- MEMBER INFO TABLE --}}
        <table class="info-table">
        <tr>
            <td class="label" style="width: 22%;">Member Name:</td>
            <td style="width: 28%;">{{ $lastName }}, {{ $firstName }}</td>

            <td class="label" style="width: 22%;">Member ID:</td>
            <td style="width: 28%;">{{ $pid }}</td>
        </tr>

        <tr>
            <td class="label">Assessment Date:</td>
            <td>{{ \Carbon\Carbon::parse($assessment->assessment_date)->format('m/d/Y') }}</td>

            <td class="label">Social Worker:</td>
            <td>{{ $assessment->social_worker_name }}</td>
        </tr>

        <tr>
            <td class="label">Medical Record #:</td>
            <td>{{ $assessment->medical_record_no }}</td>

            <td class="label">Care Plan Review:</td>
            <td><strong>{{ $assessment->care_plan_reviewed ? 'Yes' : 'No' }}</strong></td>
        </tr>

        <tr>
            <td></td><td></td>

            <td class="label">Discharge Plan Review:</td>
            <td><strong>{{ $assessment->discharge_plan_reviewed ? 'Yes' : 'No' }}</strong></td>
        </tr>
    </table>
    {{-- SECTION: COGNITIVE FUNCTIONING --}}
    <div class="section-box">
        <div class="section-title">Cognitive Functioning</div>

        <div class="mb-1">
            <span class="label">Change Since Last Review:</span>
            {{ $assessment->cognitive_change ? 'Yes' : 'No' }}
        </div>

        <div class="flex-row" style="margin-top:8px;">
            <div class="col-50">
                <div class="label">General:</div>
                <ul>
                    @if($assessment->cognitive_alert) <li>Alert</li> @endif
                    @if($assessment->cognitive_confused) <li>Confused</li> @endif
                    @if($assessment->cognitive_forgetful) <li>Forgetful</li> @endif
                    @if($assessment->cognitive_wanders) <li>Wanders</li> @endif
                    @if($assessment->cognitive_unaware_surroundings) <li>Unaware of Surroundings</li> @endif
                </ul>
            </div>

            <div class="col-50">
                <div class="label">Orientation:</div>
                <ul>
                    @if($assessment->cognitive_oriented_person) <li>Oriented to Person</li> @endif
                    @if($assessment->cognitive_oriented_place) <li>Oriented to Place</li> @endif
                    @if($assessment->cognitive_oriented_time) <li>Oriented to Time</li> @endif
                    @if($assessment->cognitive_orientation_fluctuates) <li>Orientation Fluctuates</li> @endif
                </ul>
            </div>
        </div>

        <div class="flex-row">
            <div class="col-50">
                <span class="label">Short Term Memory:</span>
                {{ $assessment->cognitive_short_term_memory === 'wnl' ? 'WNL' : 'Impaired' }}
            </div>
            <div class="col-50">
                <span class="label">Long Term Memory:</span>
                {{ $assessment->cognitive_long_term_memory === 'wnl' ? 'WNL' : 'Impaired' }}
            </div>
        </div>
    </div>

    {{-- SECTION: SOCIAL FUNCTIONING --}}
    <div class="section-box">
        <div class="section-title">Social Functioning</div>

        <div class="label">Change Since Last Review:</div>
        {{ $assessment->social_change ? 'Yes' : 'No' }}

        <ul>
            @if($assessment->social_socializes_appropriately) <li>Socializes Appropriately</li> @endif
            @if($assessment->social_attends_group_activities) <li>Attends Group Activities</li> @endif
            @if($assessment->social_isolates_self) <li>Isolates Self</li> @endif
            @if($assessment->social_cooperative) <li>Cooperative</li> @endif
            @if($assessment->social_friendly) <li>Friendly</li> @endif
            @if($assessment->social_quiet) <li>Quiet</li> @endif
        </ul>
    </div>

    {{-- SECTION: COMMUNICATION --}}
    <div class="section-box">
        <div class="section-title">Communication</div>

        <div class="label">Change Since Last Review:</div>
        {{ $assessment->communication_change ? 'Yes' : 'No' }}

        <ul>
            @if($assessment->communication_expresses_needs) <li>Expresses Needs</li> @endif
            @if($assessment->communication_does_not_express) <li>Does Not Express Needs</li> @endif
            @if($assessment->communication_requires_encouragement) <li>Requires Encouragement</li> @endif
        </ul>
    </div>

    {{-- SECTION: DECISION MAKING --}}
    <div class="section-box">
        <div class="section-title">Decision Making Ability</div>

        <div class="label">Change Since Last Review:</div>
        {{ $assessment->decision_change ? 'Yes' : 'No' }}

        <ul>
            @if($assessment->decision_independent) <li>Independent</li> @endif
            @if($assessment->decision_minimal_impairment) <li>Minimally Impaired</li> @endif
            @if($assessment->decision_moderate_impairment) <li>Moderately Impaired</li> @endif
            @if($assessment->decision_severe_impairment) <li>Severely Impaired</li> @endif
        </ul>
    </div>

    {{-- SECTION: MOOD / MENTAL HEALTH --}}
    <div class="section-box">
        <div class="section-title">Mood / Mental Health</div>

        <div class="label">Change Since Last Review:</div>
        {{ $assessment->mood_change ? 'Yes' : 'No' }}

        <ul>
            @if($assessment->mood_normal) <li>Normal</li> @endif
            @if($assessment->mood_stable) <li>Stable</li> @endif
            @if($assessment->mood_anxious) <li>Anxious</li> @endif
            @if($assessment->mood_lethargic) <li>Lethargic</li> @endif
            @if($assessment->mood_depressed) <li>Depressed</li> @endif
            @if($assessment->mood_manic) <li>Manic</li> @endif
            @if($assessment->mood_agitated) <li>Agitated</li> @endif
            @if($assessment->mood_combative) <li>Combative</li> @endif
            @if($assessment->mood_pessimistic) <li>Pessimistic</li> @endif
            @if($assessment->mood_optimistic) <li>Optimistic</li> @endif
            @if($assessment->mood_confused) <li>Confused</li> @endif
        </ul>

        @if($assessment->mental_health_diagnosis)
            <div class="label">Diagnosis:</div>
            <div class="pre-wrap mb-2">{{ $assessment->mental_health_diagnosis_type }}</div>
        @endif

        <div class="label">Treatment:</div>
        <ul>
            @if($assessment->treatment_psychiatrist) <li>Psychiatrist</li> @endif
            @if($assessment->treatment_medication_therapy) <li>Medication Therapy</li> @endif
            @if($assessment->treatment_psychotherapy) <li>Psychotherapy</li> @endif
            @if($assessment->treatment_none) <li>None</li> @endif
        </ul>

        @if($assessment->mood_comment)
            <div class="label">Comments:</div>
            <div class="pre-wrap">{{ $assessment->mood_comment }}</div>
        @endif
    </div>

    {{-- SECTION: APPEARANCE --}}
    <div class="section-box">
        <div class="section-title">Appearance / Hygiene</div>

        <div class="label">Change Since Last Review:</div>
        {{ $assessment->appearance_change ? 'Yes' : 'No' }}

        <ul>
            @if($assessment->appearance_neatly_groomed) <li>Neatly Groomed</li> @endif
            @if($assessment->appearance_presentable) <li>Presentable</li> @endif
            @if($assessment->appearance_disheveled) <li>Disheveled</li> @endif
            @if($assessment->appearance_appropriate_hygiene) <li>Appropriate Hygiene</li> @endif
            @if($assessment->appearance_poor_hygiene) <li>Poor Hygiene</li> @endif
        </ul>

        @if($assessment->appearance_comment)
            <div class="label">Comments:</div>
            <div class="pre-wrap">{{ $assessment->appearance_comment }}</div>
        @endif
    </div>

    {{-- SECTION: SERVICES --}}
    <div class="section-box">
        <div class="section-title">Services / Referrals</div>

        <ul>
            @if($assessment->services_home_health) <li>Home Health</li> @endif
            @if($assessment->services_meals_on_wheels) <li>Meals on Wheels</li> @endif
            @if($assessment->services_food_stamp_recert) <li>Food Stamp Recertification</li> @endif
            @if($assessment->services_medicaid_recert) <li>Medicaid Recertification</li> @endif
            @if($assessment->services_geriatric_case_mgmt) <li>Geriatric Case Management</li> @endif
            @if($assessment->services_ssi_advocacy) <li>SSI Advocacy</li> @endif
            @if($assessment->services_no_needs_assessed) <li>No Needs Assessed</li> @endif
        </ul>

        @if($assessment->services_comment)
            <div class="label">Comments:</div>
            <div class="pre-wrap">{{ $assessment->services_comment }}</div>
        @endif
    </div>

</body>
</html>
