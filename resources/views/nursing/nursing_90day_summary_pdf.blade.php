@php
    function boxPdf($value) {
        return $value === 'Y' ? '&#9745;' : '&#9744;';
    }

    function levelBox($actual, $expected) {
        return $actual === $expected ? '&#9745;' : '&#9744;';
    }

    $date = !empty($assessment->assessment_date)
        ? \Carbon\Carbon::parse($assessment->assessment_date)->format('m/d/Y')
        : '';

    $dob = !empty($assessment->dob)
        ? \Carbon\Carbon::parse($assessment->dob)->format('m/d/Y')
        : '';

    $patientName = $assessment->patient_name ?? '';
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>90 Days Nursing Summary</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 8px;
        }

        .sub-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            text-decoration: underline;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            vertical-align: top;
            padding: 3px;
        }

        .line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 100px;
        }

        .full-line {
            border-bottom: 1px solid #000;
            height: 15px;
        }

        .section-line {
            border-top: 2px solid #000;
            margin-top: 4px;
            margin-bottom: 4px;
        }

        .label {
            font-weight: bold;
        }

        .border-table td,
        .border-table th {
            border: 1px solid #000;
            padding: 4px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .notes-line {
            border-bottom: 1px solid #000;
            height: 22px;
        }

        .page-break {
            page-break-before: always;
        }

        .small {
            font-size: 10px;
        }
    </style>
</head>

<body>

<div class="title">90 DAYS NURSING SUMMARY</div>

<table>
    <tr>
        <td style="width: 65%;">
            <span class="label">Patient Name</span>
            <span class="line" style="width: 300px;">{{ $patientName }}</span>
        </td>
        <td style="width: 35%;">
            <span class="label">Date:</span>
            <span class="line" style="width: 130px;">{{ $date }}</span>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <span class="label">DOB</span>
            <span class="line" style="width: 145px;">{{ $dob }}</span>
        </td>
    </tr>
</table>

<div class="section-line"></div>

<div>
    <span class="label">Diagnosis:</span>
    see "Medical Assessment Summary" for detailed description
</div>

<div class="section-line"></div>

<table>
    <tr>
        <td class="label" style="width: 18%;">Mobility Status:</td>
        <td>{!! boxPdf($assessment->mobility_ambulatory ?? 'N') !!} Ambulatory</td>
        <td>{!! boxPdf($assessment->mobility_wheelchair ?? 'N') !!} Wheelchair</td>
        <td>{!! boxPdf($assessment->mobility_cane ?? 'N') !!} Cane</td>
        <td>{!! boxPdf($assessment->mobility_walker ?? 'N') !!} Walker</td>
    </tr>
</table>

<div>
    <span class="label">Comments if necessary:</span>
    {{ $assessment->mobility_comments ?? '' }}
</div>

<div class="section-line"></div>

<table>
    <tr>
        <td class="label" style="width: 18%;">Mental Status:</td>
        <td>{!! boxPdf($assessment->mental_alert ?? 'N') !!} Alert</td>
        <td>{!! boxPdf($assessment->mental_oriented ?? 'N')!!} Oriented</td>
        <td>{!! boxPdf($assessment->mental_confused ?? 'N') !!} Confused</td>
        <td>{!! boxPdf($assessment->mental_disoriented ?? 'N') !!} Disoriented</td>
    </tr>
</table>

<table>
    <tr>
        <td class="label" style="width: 25%;">Changes in mental status:</td>
        <td>{!! boxPdf($assessment->mental_change_same ?? 'N') !!} Same</td>
        <td>{!! boxPdf($assessment->mental_change_increase ?? 'N') !!} Increase</td>
        <td>{!! boxPdf($assessment->mental_change_decrease ?? 'N') !!} Decrease</td>
    </tr>
</table>

<div>
    <span class="label">Comments if necessary:</span>
    {{ $assessment->mental_comments ?? '' }}
</div>

<div class="section-line"></div>

<table>
    <tr>
        <td class="label" style="width: 20%;">Physical Assessment:</td>
        <td>
            <span class="label">Edema</span>
            {!! boxPdf($assessment->edema === 'N' ? 'Y' : 'N') !!} No
            {!! boxPdf($assessment->edema ?? 'N') !!} Yes
            {!! boxPdf($assessment->edema_plus1 ?? 'N') !!} +1
            {!! boxPdf($assessment->edema_plus2 ?? 'N') !!} +2
            {!! boxPdf($assessment->edema_trace ?? 'N') !!} Trace
        </td>
        <td>
            <span class="label">Location:</span>
            {{ $assessment->edema_location ?? '' }}
        </td>
    </tr>

    <tr>
        <td class="label">Shortness of Breath:</td>
        <td>
            {!! boxPdf($assessment->shortness_breath_yes ?? 'N') !!} Yes
            {!! boxPdf($assessment->shortness_breath_no ?? 'N') !!} No
            {!! boxPdf($assessment->shortness_breath_exertion ?? 'N') !!} On Exertion
        </td>
        <td></td>
    </tr>

    <tr>
        <td class="label">Visual Acuity</td>
        <td>{{ $assessment->visual_acuity ?? '' }}</td>
        <td>
            <span class="label">Wears Glasses</span>
            {!! boxPdf($assessment->wears_glasses_yes ?? 'N') !!} Yes
            {!! boxPdf($assessment->wears_glasses_no ?? 'N') !!} No
        </td>
    </tr>

    <tr>
        <td class="label">Urinary Incontinence</td>
        <td>
            Yes {!! boxPdf($assessment->urinary_incontinence_yes ?? 'N') !!}
            No {!! boxPdf($assessment->urinary_incontinence_no ?? 'N') !!}
        </td>
        <td>
            <span class="label">Bowel Incontinence</span>
            Yes {!! boxPdf($assessment->bowel_incontinence_yes ?? 'N') !!}
            No {!! boxPdf($assessment->bowel_incontinence_no ?? 'N') !!}
        </td>
    </tr>

    <tr>
        <td class="label">Skin:</td>
        <td colspan="2">{{ $assessment->skin ?? '' }}</td>
    </tr>

    <tr>
        <td class="label">BP Range</td>
        <td>
            <span class="label">From:</span> {{ $assessment->bp_from ?? '' }}
        </td>
        <td>
            <span class="label">To:</span> {{ $assessment->bp_to ?? '' }}
        </td>
    </tr>

    <tr>
        <td class="label">Diabetic:</td>
        <td>
            {!! boxPdf($assessment->diabetic_yes ?? 'N')  !!} Yes
            {!! boxPdf($assessment->diabetic_no ?? 'N') !!} No
        </td>
        <td>
            <span class="label">Hypoglycemic Episodes</span>
            {!! boxPdf($assessment->hypoglycemic_yes ?? 'N') !!} Yes
            {!! boxPdf($assessment->hypoglycemic_no ?? 'N') !!} No
            &nbsp;&nbsp;
            <span class="label">Hyperglycemic Episodes</span>
            {!! boxPdf($assessment->hyperglycemic_yes ?? 'N') !!} Yes
            {!! boxPdf($assessment->hyperglycemic_no ?? 'N') !!} No
        </td>
    </tr>
</table>

<div class="section-line"></div>

<table>
    <tr>
        <td class="label" style="width: 25%;">Change in medications</td>
        <td>
            {!! boxPdf($assessment->medication_change_no ?? 'N') !!} No
            {!! boxPdf($assessment->medication_change_yes ?? 'N') !!} Yes
            &nbsp;&nbsp;
            <span class="small">See medication profile</span>
        </td>
    </tr>
    <tr>
        <td class="label">Reason:</td>
        <td>
            {!! boxPdf($assessment->medication_reason_not_effective ?? 'N') !!} Not effective
            {!! boxPdf($assessment->medication_reason_does_not_tolerate ?? 'N') !!} Does not tolerate
            {!! boxPdf($assessment->medication_reason_md_order ?? 'N') !!} MD order
            {!! boxPdf($assessment->medication_reason_patient_refused ?? 'N') !!} Patient refused
            {!! boxPdf(!empty($assessment->medication_reason_other) ? 'Y' : 'N') !!} Other:
            {{ $assessment->medication_reason_other ?? '' }}
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="label">Client's medications delivered to Day Care Center & checked by RN</span>
            {!! boxPdf($assessment->meds_delivered_yes ?? 'N') !!} Yes
            {!! boxPdf($assessment->meds_delivered_no ?? 'N') !!} No
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="label">Is patient taking</span>
            {!! boxPdf($assessment->antidepressants ?? 'N') !!} Antidepressants
            {!! boxPdf($assessment->antipsychotic ?? 'N') !!} Antipsychotic
            &nbsp;&nbsp;
            <span class="label">Side Effects</span>
            {!! boxPdf($assessment->side_effects_yes ?? 'N') !!} Yes
            {!! boxPdf($assessment->side_effects_no ?? 'N') !!} No
        </td>
    </tr>
    <tr>
        <td class="label">Compliance with Tx Regimen:</td>
        <td>
            {!! boxPdf($assessment->tx_good ?? 'N') !!} Good
            {!! boxPdf($assessment->tx_fair ?? 'N') !!} Fair
            {!! boxPdf($assessment->tx_poor ?? 'N') !!} Poor
        </td>
    </tr>
    <tr>
        <td class="label">Comments</td>
        <td>{{ $assessment->medication_comments ?? '' }}</td>
    </tr>
</table>

<div class="section-line"></div>

<table>
    <tr>
        <td class="label" style="width: 15%;">Diet:</td>
        <td>
            Standard house diet (low Na, 1800 Cal, Moderate cholesterol)
        </td>
        <td>
            {!! boxPdf($assessment->diet_standard_yes ?? 'N') !!} Yes
            {!! boxPdf($assessment->diet_standard_no ?? 'N') !!} No
        </td>
    </tr>
    <tr>
        <td></td>
        <td>No concentrated sweets</td>
        <td>
            {!! boxPdf($assessment->diet_no_concentrated_sweets_yes ?? 'N') !!} Yes
            {!! boxPdf($assessment->diet_no_concentrated_sweets_no ?? 'N') !!} No
        </td>
    </tr>
    <tr>
        <td></td>
        <td>Other: {{ $assessment->diet_other ?? '' }}</td>
        <td>
            {!! boxPdf($assessment->diet_other_yes ?? 'N') !!} Yes
            {!! boxPdf($assessment->diet_other_no ?? 'N') !!} No
        </td>
    </tr>
    <tr>
        <td class="label">Comments</td>
        <td colspan="2">{{ $assessment->diet_comments ?? '' }}</td>
    </tr>
</table>

<div class="section-line"></div>

<div>
    <span class="label">Pain Assessment:</span>
    {!! boxPdf($assessment->pain_neck ?? 'N') !!} Neck
    {!! boxPdf($assessment->pain_upper_back ?? 'N') !!} Upper Back
    {!! boxPdf($assessment->pain_low_back ?? 'N') !!} Low Back
    {!! boxPdf($assessment->pain_shoulder ?? 'N') !!} Shoulder
    {!! boxPdf($assessment->pain_elbow ?? 'N') !!} Elbow
    {!! boxPdf($assessment->pain_wrist ?? 'N') !!} Wrist
    {!! boxPdf($assessment->pain_hip ?? 'N') !!} Hip
    {!! boxPdf($assessment->pain_knee ?? 'N') !!} Knee
    {!! boxPdf($assessment->pain_ankle ?? 'N') !!} Ankle
    {!! boxPdf($assessment->pain_muscle ?? 'N') !!} Muscle pain
</div>

<div style="margin-top: 5px;">
    <span class="label">Pain Score:</span>
    @for($i = 0; $i <= 10; $i++)
        [{{ $assessment->pain_score !== null && (int)$assessment->pain_score === $i ? 'X' : $i }}]
    @endfor
</div>

<div style="margin-top: 5px;">
    <span class="label">Patient underwent Physical Therapy or Pain Management Treatment</span>
    {!! boxPdf($assessment->pain_treatment_yes ?? 'N') !!} Yes
    {!! boxPdf($assessment->pain_treatment_no ?? 'N') !!} No
</div>

<br>

<table class="border-table">
    <thead>
        <tr>
            <th>ADL</th>
            <th>Independent</th>
            <th>Supervising / Cueing</th>
            <th>Limited or greater assistance</th>
            <th>ADL provided at ADHS</th>
        </tr>
    </thead>
    <tbody>
        @php
            $adlRows = [
                'dressing' => 'Dressing',
                'toilet_use' => 'Toilet use',
                'ambulation' => 'Ambulation',
                'locomotion' => 'Locomotion',
                'transfer' => 'Transfer',
                'eating' => 'Eating',
            ];
        @endphp

        @foreach($adlRows as $key => $label)
            @php
                $levelField = 'adl_' . $key . '_level';
                $yesField = 'adl_' . $key . '_provided_yes';
                $noField = 'adl_' . $key . '_provided_no';
                $levelValue = $assessment->$levelField ?? '';
            @endphp
            <tr>
                <td>{{ $label }}</td>
                <td class="center">{!! levelBox($levelValue, 'independent') !!}</td>
                <td class="center">{!! levelBox($levelValue, 'supervising_cueing') !!}</td>
                <td class="center">{!! levelBox($levelValue, 'limited_or_greater_assistance') !!}</td>
                <td>
                    {!! boxPdf($assessment->$yesField ?? 'N') !!} Yes
                    {!! boxPdf($assessment->$noField ?? 'N') !!} No
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<br>

<div>
    <span class="label">Clinic Visits:</span>
    {!! boxPdf($assessment->clinic_podiatry ?? 'N') !!} Podiatry
    {!! boxPdf($assessment->clinic_optometry ?? 'N') !!} Optometry
    {!! boxPdf($assessment->clinic_pain_clinic ?? 'N') !!} Pain Clinic
    {!! boxPdf($assessment->clinic_psychiatry ?? 'N') !!} Psychiatry
    {!! boxPdf($assessment->clinic_pt ?? 'N') !!} PT
    {!! boxPdf($assessment->clinic_ot ?? 'N') !!} OT
    {!! boxPdf(!empty($assessment->clinic_other) ? 'Y' : 'N') !!} Other:
    {{ $assessment->clinic_other ?? '' }}
</div>

<div class="section-line"></div>

<div>
    <span class="label">Recent Hospitalization:</span>
    {{ $assessment->recent_hospitalization ?? '' }}
</div>

<br>

<div>
    <span class="label">Change in body picture</span>
    <em>(i.e. bruises, fractures, etc.)</em>
    <br>
    {{ $assessment->body_picture_change ?? '' }}
</div>

<br><br>

<table>
    <tr>
        <td style="width: 65%;">
            <span class="label">Signature</span>
            <span class="line" style="width: 300px;">{{ $assessment->signature ?? '' }}</span>
        </td>
    </tr>
</table>

@if(!empty($assessment->long_note))
    <div class="page-break"></div>

    <div class="sub-title">ADULT DAY CARE NOTES</div>

    <div style="margin-bottom: 15px;">
        <span class="label">Patient name</span>
        <span class="line" style="width: 250px;">{{ $patientName }}</span>
    </div>

    <div style="line-height: 1.7; white-space: pre-line;">
        {{ $assessment->long_note }}
    </div>
@endif

</body>
</html>
