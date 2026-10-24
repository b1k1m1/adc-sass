@php
    function pdfBox($value) {
        return $value === 'Y' ? '☑' : '☐';
    }

    function pdfText($value) {
        return $value ?? '';
    }

    function pdfDate($value) {
        if (empty($value)) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('m/d/Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    function pdfAssessmentType($value) {
        return ucwords(str_replace('_', ' ', $value ?? ''));
    }

    function pdfFunctionalLevel($level) {
        return ucwords(str_replace('_', ' ', $level ?? ''));
    }

    $pmh       = $sections['past_medical_history'] ?? [];
    $vh        = $sections['vital_hospitalization'] ?? [];
    $fa        = $sections['functional_assessment'] ?? [];
    $bb        = $sections['bowel_bladder'] ?? [];
    $mbs       = $sections['mental_behavior_sleep'] ?? [];
    $skilled   = $sections['skilled_needs'] ?? [];
    $skin      = $sections['skin_assessment'] ?? [];
    $devices   = $sections['assistive_devices'] ?? [];
    $nutrition = $sections['nutritional_status'] ?? [];
    $pain      = $sections['pain_assessment'] ?? [];

    $patientName = $assessment->patient_name ?? '';
    $assessmentDate = pdfDate($assessment->assessment_date ?? null);
    $dob = pdfDate($assessment->dob ?? null);
    $skinDiagramPath = public_path('backend/assets/images/skin_body_diagram.png');
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nursing Assessment Summary</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10.5px;
            color: #000;
        }

        .title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .section-title {
            background: #e9ecef;
            border: 1px solid #000;
            padding: 5px;
            font-weight: bold;
            font-size: 12px;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        .sub-title {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 5px;
            margin-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            vertical-align: top;
            padding: 3px;
        }

        .border-table td,
        .border-table th {
            border: 1px solid #000;
            padding: 4px;
        }

        .line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 110px;
            padding-left: 3px;
            padding-right: 3px;
        }

        .small {
            font-size: 9.5px;
        }

        .page-break {
            page-break-before: always;
        }

        .two-col td {
            width: 50%;
        }

        .three-col td {
            width: 33.33%;
        }

        .four-col td {
            width: 25%;
        }

        .center {
            text-align: center;
        }

        .notes {
            white-space: pre-line;
            border: 1px solid #ccc;
            padding: 5px;
            min-height: 30px;
        }
    </style>
</head>

<body>

<div class="title">Nursing Assessment Summary</div>

<table class="border-table">
    <tr>
        <td style="width: 38%;">
            <strong>Patient Name:</strong> {{ $patientName }}
        </td>
        <td style="width: 20%;">
            <strong>DOB:</strong> {{ $dob }}
        </td>
        <td style="width: 22%;">
            <strong>Date:</strong> {{ $assessmentDate }}
        </td>
        <td style="width: 20%;">
            <strong>Shift:</strong> {{ ($assessment->Trans_flag ?? 'A') == 'A' ? 'Morning' : 'Noon' }}
        </td>
    </tr>
    <tr>
        <td>
            <strong>Participant ID:</strong> {{ str_pad($assessment->ParticipantId ?? '', 3, '0', STR_PAD_LEFT) }}
        </td>
        <td colspan="3">
            <strong>Assessment Type:</strong> {{ pdfAssessmentType($assessment->assessment_type ?? '') }}
        </td>
    </tr>
</table>

{{-- PAGE 1 --}}
<div class="section-title">Past Medical History</div>

<div class="sub-title">Neurological</div>
<table class="border-table">
    <tr>
        <td colspan="4">
            <strong>Source:</strong>
            {{ pdfBox($pmh['source_neuro_participant'] ?? 'N') }} Participant
            {{ pdfBox($pmh['source_neuro_medical_record'] ?? 'N') }} Medical Record
            {{ pdfBox($pmh['source_neuro_family'] ?? 'N') }} Family
        </td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['head_trauma'] ?? 'N') }} Head Trauma: {{ pdfText($pmh['head_trauma_specify'] ?? '') }}</td>
        <td>{{ pdfBox($pmh['migraine_headache'] ?? 'N') }} Migraine Headache</td>
        <td>{{ pdfBox($pmh['impaired_vision_left'] ?? 'N') }} Impaired Vision Left</td>
        <td>{{ pdfBox($pmh['impaired_vision_right'] ?? 'N') }} Impaired Vision Right</td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['impaired_hearing_left'] ?? 'N') }} Impaired Hearing Left</td>
        <td>{{ pdfBox($pmh['impaired_hearing_right'] ?? 'N') }} Impaired Hearing Right</td>
        <td>{{ pdfBox($pmh['aphasia_motor'] ?? 'N') }} Aphasia Motor</td>
        <td>{{ pdfBox($pmh['aphasia_expressive'] ?? 'N') }} Aphasia Expressive</td>
    </tr>
    <tr>
        <td colspan="2">
            <strong>Hemiparesis:</strong>
            {{ pdfBox($pmh['hemiparesis_left_arm'] ?? 'N') }} Left Arm
            {{ pdfBox($pmh['hemiparesis_left_leg'] ?? 'N') }} Left Leg
            {{ pdfBox($pmh['hemiparesis_right_arm'] ?? 'N') }} Right Arm
            {{ pdfBox($pmh['hemiparesis_right_leg'] ?? 'N') }} Right Leg
        </td>
        <td colspan="2">
            <strong>Paralysis:</strong>
            {{ pdfBox($pmh['paralysis_left_arm'] ?? 'N') }} Left Arm
            {{ pdfBox($pmh['paralysis_left_leg'] ?? 'N') }} Left Leg
            {{ pdfBox($pmh['paralysis_right_arm'] ?? 'N') }} Right Arm
            {{ pdfBox($pmh['paralysis_right_leg'] ?? 'N') }} Right Leg
        </td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['mental_retardation'] ?? 'N') }} Mental Retardation</td>
        <td>{{ pdfBox($pmh['dementia'] ?? 'N') }} Dementia</td>
        <td>{{ pdfBox($pmh['alzheimers_disease'] ?? 'N') }} Alzheimer&apos;s Disease</td>
        <td>{{ pdfBox($pmh['cva'] ?? 'N') }} CVA</td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['parkinsons_disease'] ?? 'N') }} Parkinson&apos;s Disease</td>
        <td>{{ pdfBox($pmh['seizure_disorder'] ?? 'N') }} Seizure Disorder</td>
        <td colspan="2"><strong>Comment:</strong> {{ pdfText($pmh['neuro_comment'] ?? '') }}</td>
    </tr>
</table>

<div class="sub-title">Cardiac</div>
<table class="border-table">
    <tr>
        <td colspan="4">
            <strong>Source:</strong>
            {{ pdfBox($pmh['source_cardiac_participant'] ?? 'N') }} Participant
            {{ pdfBox($pmh['source_cardiac_medical_record'] ?? 'N') }} Medical Record
            {{ pdfBox($pmh['source_cardiac_family'] ?? 'N') }} Family
        </td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['angina'] ?? 'N') }} Angina</td>
        <td>{{ pdfBox($pmh['myocardial_infarction'] ?? 'N') }} Myocardial Infarction</td>
        <td>{{ pdfBox($pmh['irregular_heart_rhythm'] ?? 'N') }} Irregular Heart Rhythm</td>
        <td>{{ pdfBox($pmh['permanent_pacemaker'] ?? 'N') }} Permanent Pacemaker Rate: {{ pdfText($pmh['permanent_pacemaker_rate'] ?? '') }}</td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['peripheral_edema'] ?? 'N') }} Peripheral Edema</td>
        <td>Location: {{ pdfText($pmh['peripheral_edema_location'] ?? '') }}</td>
        <td>{{ pdfBox($pmh['valve_disorder'] ?? 'N') }} Valve Disorder</td>
        <td>{{ pdfBox($pmh['murmur'] ?? 'N') }} Murmur</td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['congestive_heart_failure'] ?? 'N') }} CHF</td>
        <td>{{ pdfBox($pmh['htn'] ?? 'N') }} HTN</td>
        <td colspan="2"><strong>Comment:</strong> {{ pdfText($pmh['cardiac_comment'] ?? '') }}</td>
    </tr>
</table>

<div class="sub-title">Respiratory</div>
<table class="border-table">
    <tr>
        <td colspan="4">
            <strong>Source:</strong>
            {{ pdfBox($pmh['source_respiratory_participant'] ?? 'N') }} Participant
            {{ pdfBox($pmh['source_respiratory_medical_record'] ?? 'N') }} Medical Record
            {{ pdfBox($pmh['source_respiratory_family'] ?? 'N') }} Family
        </td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['smoking'] ?? 'N') }} Smoking: {{ pdfText($pmh['smoking_specify'] ?? '') }}</td>
        <td>{{ pdfBox($pmh['asthma'] ?? 'N') }} Asthma</td>
        <td>{{ pdfBox($pmh['bronchitis'] ?? 'N') }} Bronchitis</td>
        <td>{{ pdfBox($pmh['pneumonia'] ?? 'N') }} Pneumonia</td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['copd'] ?? 'N') }} COPD</td>
        <td>{{ pdfBox($pmh['cancer'] ?? 'N') }} Cancer</td>
        <td>{{ pdfBox($pmh['chronic_cough'] ?? 'N') }} Chronic Cough</td>
        <td>{{ pdfBox($pmh['influenza'] ?? 'N') }} Influenza</td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['tb'] ?? 'N') }} TB</td>
        <td colspan="3"><strong>Comment:</strong> {{ pdfText($pmh['respiratory_comment'] ?? '') }}</td>
    </tr>
</table>

<div class="sub-title">GI / GU</div>
<table class="border-table">
    <tr>
        <td colspan="4">
            <strong>Source:</strong>
            {{ pdfBox($pmh['source_gigu_participant'] ?? 'N') }} Participant
            {{ pdfBox($pmh['source_gigu_medical_record'] ?? 'N') }} Medical Record
            {{ pdfBox($pmh['source_gigu_family'] ?? 'N') }} Family
        </td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['ulcer'] ?? 'N') }} Ulcer: {{ pdfText($pmh['ulcer_specify'] ?? '') }}</td>
        <td>{{ pdfBox($pmh['gerd'] ?? 'N') }} GERD</td>
        <td>{{ pdfBox($pmh['hepatitis'] ?? 'N') }} Hepatitis: {{ pdfText($pmh['hepatitis_specify'] ?? '') }}</td>
        <td>{{ pdfBox($pmh['gi_bleeding'] ?? 'N') }} GI Bleeding</td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['pancreatitis'] ?? 'N') }} Pancreatitis</td>
        <td>{{ pdfBox($pmh['cirrhosis'] ?? 'N') }} Cirrhosis</td>
        <td>{{ pdfBox($pmh['gallbladder_disease'] ?? 'N') }} Gallbladder Disease</td>
        <td>{{ pdfBox($pmh['gallstones'] ?? 'N') }} Gallstones</td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['kidney_stones'] ?? 'N') }} Kidney Stones</td>
        <td>{{ pdfBox($pmh['esrd_dialysis'] ?? 'N') }} ESRD / Dialysis</td>
        <td colspan="2"><strong>Comment:</strong> {{ pdfText($pmh['gigu_comment'] ?? '') }}</td>
    </tr>
</table>

<div class="sub-title">Endocrine / Current Complaint</div>
<table class="border-table">
    <tr>
        <td colspan="4">
            <strong>Source:</strong>
            {{ pdfBox($pmh['source_endocrine_participant'] ?? 'N') }} Participant
            {{ pdfBox($pmh['source_endocrine_medical_record'] ?? 'N') }} Medical Record
            {{ pdfBox($pmh['source_endocrine_family'] ?? 'N') }} Family
        </td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['non_insulin_diabetes'] ?? 'N') }} Non-Insulin Diabetes</td>
        <td>{{ pdfBox($pmh['insulin_diabetes'] ?? 'N') }} Insulin Diabetes</td>
        <td>{{ pdfBox($pmh['dka'] ?? 'N') }} DKA</td>
        <td>{{ pdfBox($pmh['hhnk'] ?? 'N') }} HHNK</td>
    </tr>
    <tr>
        <td>{{ pdfBox($pmh['hypothyroidism'] ?? 'N') }} Hypothyroidism</td>
        <td>{{ pdfBox($pmh['hyperthyroidism'] ?? 'N') }} Hyperthyroidism</td>
        <td colspan="2"><strong>Allergies:</strong> {{ pdfText($pmh['allergies'] ?? '') }}</td>
    </tr>
    <tr>
        <td colspan="4"><strong>Endocrine Comment:</strong> {{ pdfText($pmh['endocrine_comment'] ?? '') }}</td>
    </tr>
    <tr>
        <td colspan="4">
            <strong>Current Complaint Source:</strong>
            {{ pdfBox($pmh['source_current_complaint_participant'] ?? 'N') }} Participant
            {{ pdfBox($pmh['source_current_complaint_medical_record'] ?? 'N') }} Medical Record
            {{ pdfBox($pmh['source_current_complaint_family'] ?? 'N') }} Family
        </td>
    </tr>
    <tr>
        <td colspan="4"><strong>Current Complaint:</strong> {{ pdfText($pmh['current_complaint'] ?? '') }}</td>
    </tr>
</table>

<div class="section-title">Vitals / Pacemaker / Hospitalization</div>

<table class="border-table">
    <tr>
        <td><strong>Temp:</strong> {{ pdfText($vh['temp'] ?? '') }}</td>
        <td><strong>BP:</strong> {{ pdfText($vh['bp'] ?? '') }}</td>
        <td><strong>Respiratory Rate:</strong> {{ pdfText($vh['respiratory_rate'] ?? '') }}</td>
        <td><strong>Pulse:</strong> {{ pdfText($vh['pulse'] ?? '') }}</td>
    </tr>
    <tr>
        <td colspan="2">
            {{ pdfBox($vh['pacemaker'] ?? 'N') }} Pacemaker
            &nbsp; Last Checked: {{ pdfText($vh['pacemaker_last_checked_date'] ?? '') }}
        </td>
        <td colspan="2">
            {{ pdfBox($vh['implantable_defibrillator'] ?? 'N') }} Implantable Defibrillator
        </td>
    </tr>
    <tr>
        <td>{{ pdfBox($vh['no_hospitalization'] ?? 'N') }} No Hospitalization</td>
        <td colspan="3"><strong>Dates / Duration:</strong> {{ pdfText($vh['hospitalization_dates'] ?? '') }}</td>
    </tr>
    <tr>
        <td colspan="4"><strong>Primary Diagnosis / Reason:</strong> {{ pdfText($vh['primary_diagnosis_hospitalization'] ?? '') }}</td>
    </tr>
</table>

{{-- PAGE 2 --}}
<div class="page-break"></div>

<div class="title">Nursing Assessment Summary</div>

<table class="border-table">
    <tr>
        <td><strong>Patient:</strong> {{ $patientName }}</td>
        <td><strong>Date:</strong> {{ $assessmentDate }}</td>
        <td><strong>Assessment Type:</strong> {{ pdfAssessmentType($assessment->assessment_type ?? '') }}</td>
    </tr>
</table>

<div class="section-title">Functional Assessment</div>

<table class="border-table">
    <thead>
        <tr>
            <th style="width: 35%;">Activity</th>
            <th style="width: 25%;">Resident Performance / Staff Support</th>
            <th>Comment</th>
        </tr>
    </thead>
    <tbody>
        @php
            $functionalRows = [
                'bed_recliner_mobility' => 'Bed/Recliner Mobility',
                'transfers'             => 'Transfers',
                'locomotion'            => 'Locomotion',
                'eating'                => 'Eating',
                'dressing'              => 'Dressing',
                'toileting'             => 'Toileting',
                'hygiene'               => 'Hygiene',
                'bathing'               => 'Bathing',
            ];
        @endphp

        @foreach($functionalRows as $key => $label)
            @php
                $row = $fa[$key] ?? [];
            @endphp
            <tr>
                <td>{{ $label }}</td>
                <td>{{ pdfFunctionalLevel($row['level'] ?? '') }}</td>
                <td>{{ pdfText($row['comment'] ?? '') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="section-title">Bowel and Bladder Function</div>

<table class="border-table">
    <tr>
        <td><strong>Bowel:</strong> {{ pdfFunctionalLevel($bb['bowel_level'] ?? '') }}</td>
        <td><strong>Bladder:</strong> {{ pdfFunctionalLevel($bb['bladder_level'] ?? '') }}</td>
    </tr>
    <tr>
        <td colspan="2">
            {{ pdfBox($bb['toileting_assisted_request'] ?? 'N') }} Assisted with Toileting Upon Request
            &nbsp;
            {{ pdfBox($bb['toileting_scheduled_program'] ?? 'N') }} Individualized Scheduled Toileting Program
            &nbsp;
            {{ pdfBox($bb['toileting_pads_briefs_pullups'] ?? 'N') }} Pads / Briefs / Pull-ups
            &nbsp;
            {{ pdfBox($bb['toileting_change_continent_status'] ?? 'N') }} Change in Continent Status
        </td>
    </tr>
    <tr>
        <td colspan="2">
            {{ pdfBox($bb['bowel_regular'] ?? 'N') }} Regular
            {{ pdfBox($bb['bowel_loose_stool'] ?? 'N') }} Loose Stool
            {{ pdfBox($bb['bowel_constipation'] ?? 'N') }} Constipation
            {{ pdfBox($bb['bowel_diarrhea'] ?? 'N') }} Diarrhea
            {{ pdfBox($bb['bowel_enema'] ?? 'N') }} Enema Date: {{ pdfText($bb['bowel_enema_date'] ?? '') }}
            {{ pdfBox($bb['bowel_laxative'] ?? 'N') }} Laxative / Suppositories: {{ pdfText($bb['bowel_laxative_med'] ?? '') }}
        </td>
    </tr>
</table>

<div class="section-title">Mental Status</div>

<table class="border-table">
    <tr>
        <td>{{ pdfBox($mbs['alert_oriented'] ?? 'N') }} Alert / Oriented</td>
        <td>{{ pdfBox($mbs['oriented_person'] ?? 'N') }} Person</td>
        <td>{{ pdfBox($mbs['oriented_place'] ?? 'N') }} Place</td>
        <td>{{ pdfBox($mbs['oriented_time'] ?? 'N') }} Time</td>
    </tr>
    <tr>
        <td>{{ pdfBox($mbs['confused'] ?? 'N') }} Confused</td>
        <td>{{ pdfBox($mbs['confused_sometimes'] ?? 'N') }} Sometimes</td>
        <td>{{ pdfBox($mbs['confused_always'] ?? 'N') }} Always</td>
        <td>{{ pdfBox($mbs['semi_comatose'] ?? 'N') }} Semi-Comatose</td>
    </tr>
    <tr>
        <td>{{ pdfBox($mbs['comatose'] ?? 'N') }} Comatose</td>
        <td>{{ pdfBox($mbs['memory_loss'] ?? 'N') }} Memory Loss</td>
        <td>{{ pdfBox($mbs['memory_short_term'] ?? 'N') }} Short Term</td>
        <td>{{ pdfBox($mbs['memory_long_term'] ?? 'N') }} Long Term</td>
    </tr>
    <tr>
        <td>{{ pdfBox($mbs['no_memory_loss'] ?? 'N') }} No Memory Loss</td>
        <td colspan="3"><strong>MMSE Score:</strong> {{ pdfText($mbs['mmse_score'] ?? '') }}</td>
    </tr>
</table>

<div class="section-title">Behavior Assessment / Sleep Pattern</div>

<table class="border-table">
    <tr>
        <td>{{ pdfBox($mbs['agitation'] ?? 'N') }} Agitation</td>
        <td>{{ pdfBox($mbs['physically_aggressive'] ?? 'N') }} Physically Aggressive</td>
        <td>{{ pdfBox($mbs['verbally_abusive'] ?? 'N') }} Verbally Abusive</td>
        <td>{{ pdfBox($mbs['hoards'] ?? 'N') }} Hoards</td>
    </tr>
    <tr>
        <td>{{ pdfBox($mbs['places_self_at_risk'] ?? 'N') }} Places Self at Risk</td>
        <td>{{ pdfBox($mbs['resists_care'] ?? 'N') }} Resists Care</td>
        <td>{{ pdfBox($mbs['wandering'] ?? 'N') }} Wandering</td>
        <td>{{ pdfBox($mbs['socially_inappropriate_behavior'] ?? 'N') }} Socially Inappropriate</td>
    </tr>
    <tr>
        <td colspan="4"><strong>Other:</strong> {{ pdfText($mbs['behavior_other'] ?? '') }}</td>
    </tr>
    <tr>
        <td colspan="4"><strong>Behavior Management Program:</strong> {{ pdfText($mbs['behavior_management_program'] ?? '') }}</td>
    </tr>
    <tr>
        <td>{{ pdfBox($mbs['sleeps_through_night'] ?? 'N') }} Sleeps through night</td>
        <td><strong>Poor due to:</strong> {{ pdfText($mbs['poor_due_to'] ?? '') }}</td>
        <td>{{ pdfBox($mbs['awakes_frequently'] ?? 'N') }} Awakes frequently</td>
        <td><strong>Reason:</strong> {{ pdfText($mbs['awakes_reason'] ?? '') }}</td>
    </tr>
    <tr>
        <td>{{ pdfBox($mbs['naps_during_day'] ?? 'N') }} Naps during day</td>
        <td><strong>Time:</strong> {{ pdfText($mbs['naps_time'] ?? '') }}</td>
        <td>{{ pdfBox($mbs['requires_hypnotic'] ?? 'N') }} Requires Hypnotic</td>
        <td><strong>Medication:</strong> {{ pdfText($mbs['sleep_med'] ?? '') }}</td>
    </tr>
    <tr>
        <td colspan="4"><strong>Hypnotic Indication:</strong> {{ pdfText($mbs['hypnotic_indication'] ?? '') }}</td>
    </tr>
</table>

{{-- PAGE 3 --}}
<div class="page-break"></div>

<div class="title">Nursing Assessment Summary</div>

<table class="border-table">
    <tr>
        <td><strong>Patient:</strong> {{ $patientName }}</td>
        <td><strong>Date:</strong> {{ $assessmentDate }}</td>
        <td><strong>Assessment Type:</strong> {{ pdfAssessmentType($assessment->assessment_type ?? '') }}</td>
    </tr>
</table>

<div class="section-title">Skilled Needs / Rehab</div>

<table class="border-table">
    <tr>
        <td>{{ pdfBox($skilled['rehab_pt'] ?? 'N') }} PT Days: {{ pdfText($skilled['rehab_pt_days'] ?? '') }}</td>
        <td>{{ pdfBox($skilled['rehab_ot'] ?? 'N') }} OT Days: {{ pdfText($skilled['rehab_ot_days'] ?? '') }}</td>
        <td>{{ pdfBox($skilled['rehab_speech'] ?? 'N') }} Speech Days: {{ pdfText($skilled['rehab_speech_days'] ?? '') }}</td>
    </tr>
</table>

<div class="section-title">Skilled Nursing Needs - Current and within last 30 days</div>

<table class="border-table">
    <tr>
        <td>{{ pdfBox($skilled['daily_oxygen'] ?? 'N') }} Daily Oxygen</td>
        <td>{{ pdfBox($skilled['hemodialysis'] ?? 'N') }} Hemodialysis</td>
        <td>{{ pdfBox($skilled['suctioning'] ?? 'N') }} Suctioning</td>
        <td>{{ pdfBox($skilled['tube_feeding'] ?? 'N') }} Tube Feeding</td>
    </tr>
    <tr>
        <td>{{ pdfBox($skilled['tube_gtt'] ?? 'N') }} GTT</td>
        <td>{{ pdfBox($skilled['tube_ngt'] ?? 'N') }} NGT</td>
        <td>{{ pdfBox($skilled['iv_fluids'] ?? 'N') }} IV Fluids: {{ pdfText($skilled['iv_fluids_dates'] ?? '') }}</td>
        <td>{{ pdfBox($skilled['tpn'] ?? 'N') }} TPN</td>
    </tr>
    <tr>
        <td>{{ pdfBox($skilled['blood_transfusion'] ?? 'N') }} Blood Transfusion</td>
        <td>{{ pdfBox($skilled['iv_medication'] ?? 'N') }} IV Medication: {{ pdfText($skilled['iv_medication_dates'] ?? '') }}</td>
        <td>{{ pdfBox($skilled['wound_care'] ?? 'N') }} Wound Care</td>
        <td>{{ pdfBox($skilled['tracheostomy_care'] ?? 'N') }} Tracheostomy Care</td>
    </tr>
    <tr>
        <td>{{ pdfBox($skilled['respiratory_therapy'] ?? 'N') }} Respiratory Therapy</td>
        <td>{{ pdfBox($skilled['uti'] ?? 'N') }} UTI</td>
        <td>{{ pdfBox($skilled['ostomy_care'] ?? 'N') }} Ostomy Care</td>
        <td>{{ pdfBox($skilled['internal_bleeding'] ?? 'N') }} Internal Bleeding</td>
    </tr>
    <tr>
        <td>{{ pdfBox($skilled['dehydration'] ?? 'N') }} Dehydration</td>
        <td>{{ pdfBox($skilled['abt_therapy'] ?? 'N') }} ABT Therapy</td>
        <td>{{ pdfBox($skilled['chemotherapy_radiation'] ?? 'N') }} Chemo / Radiation</td>
        <td>{{ pdfBox($skilled['hip_fracture'] ?? 'N') }} Hip Fracture</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Other Fracture:</strong> {{ pdfText($skilled['other_fracture'] ?? '') }}</td>
        <td colspan="2"><strong>Other Skilled Need:</strong> {{ pdfText($skilled['skilled_other'] ?? '') }}</td>
    </tr>
</table>

<div class="section-title">Hemodialysis</div>

<table class="border-table">
    <tr>
        <td>{{ pdfBox($skilled['hemo_not_applicable'] ?? 'N') }} Not Applicable</td>
        <td><strong>Treatment Orders:</strong> {{ pdfText($skilled['treatment_orders'] ?? '') }}</td>
        <td><strong>Times per week:</strong> {{ pdfText($skilled['times_per_week'] ?? '') }}</td>
    </tr>
    <tr>
        <td><strong>Active Shunt Location:</strong> {{ pdfText($skilled['active_shunt_location'] ?? '') }}</td>
        <td>
            <strong>Bruit Present:</strong>
            {{ pdfBox($skilled['bruit_yes'] ?? 'N') }} Yes
            {{ pdfBox($skilled['bruit_no'] ?? 'N') }} No
        </td>
        <td>
            <strong>Thrill Present:</strong>
            {{ pdfBox($skilled['thrill_yes'] ?? 'N') }} Yes
            {{ pdfBox($skilled['thrill_no'] ?? 'N') }} No
        </td>
    </tr>
</table>

{{-- PAGE 4 --}}
<div class="page-break"></div>

<div class="title">Nursing Assessment Summary</div>

<table class="border-table">
    <tr>
        <td><strong>Patient:</strong> {{ $patientName }}</td>
        <td><strong>Date:</strong> {{ $assessmentDate }}</td>
        <td><strong>Assessment Type:</strong> {{ pdfAssessmentType($assessment->assessment_type ?? '') }}</td>
    </tr>
</table>

<div class="section-title">Skin Assessment</div>

<table class="border-table">
    <tr>
        <td style="width: 34%; vertical-align: top;">
            <strong>Skin Problems / Lesions</strong><br><br>

            {{ pdfBox($skin['skin_pressure_ulcer'] ?? 'N') }} 1. Pressure Ulcer<br>
            {{ pdfBox($skin['skin_stasis_ulcer'] ?? 'N') }} 2. Stasis Ulcer<br>
            {{ pdfBox($skin['skin_rash'] ?? 'N') }} 3. Rash<br>
            {{ pdfBox($skin['skin_ecchymosis'] ?? 'N') }} 4. Ecchymosis<br>
            {{ pdfBox($skin['skin_burn'] ?? 'N') }} 5. Burn<br>
            {{ pdfBox($skin['skin_abrasion'] ?? 'N') }} 6. Abrasion<br>
            {{ pdfBox($skin['skin_tear'] ?? 'N') }} 7. Skin Tear<br>
            {{ pdfBox($skin['skin_cellulitis'] ?? 'N') }} 8. Cellulitis<br>
            {{ pdfBox($skin['skin_blister'] ?? 'N') }} 9. Blister<br>
            {{ pdfBox($skin['skin_hematoma'] ?? 'N') }} 10. Hematoma<br>
            {{ pdfBox($skin['skin_surgical_wound'] ?? 'N') }} 11. Surgical Wound<br>
            {{ pdfBox($skin['skin_scar'] ?? 'N') }} 12. Scar<br>
            {{ pdfBox($skin['skin_edema'] ?? 'N') }} 13. Edema
        </td>

        <td style="width: 66%; vertical-align: top; text-align: center;">
            @if(file_exists($skinDiagramPath))
                <img src="{{ $skinDiagramPath }}" style="width: 100%; max-height: 190px;">
            @else
                <div style="border: 1px solid #999; height: 170px; padding-top: 70px;">
                    Skin body diagram image not found.
                </div>
            @endif
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <strong>Body / Location Notes:</strong><br>
            <div style="min-height: 35px; white-space: pre-line;">
                {{ pdfText($skin['skin_body_notes'] ?? '') }}
            </div>
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <strong>Note Significant Findings:</strong><br>
            <div style="min-height: 35px; white-space: pre-line;">
                {{ pdfText($skin['skin_significant_findings'] ?? '') }}
            </div>
        </td>
    </tr>
</table>
<div class="section-title">Assistive / Adaptive Devices</div>

<table class="border-table">
    <tr>
        <td>{{ pdfBox($devices['device_none'] ?? 'N') }} None</td>
        <td>{{ pdfBox($devices['wc_cushion'] ?? 'N') }} W/C Cushion</td>
        <td>{{ pdfBox($devices['specialty_wc'] ?? 'N') }} Specialty W/C</td>
        <td>{{ pdfBox($devices['specialty_wc_accessories'] ?? 'N') }} Specialty W/C Accessories</td>
    </tr>
    <tr>
        <td>{{ pdfBox($devices['knee_separator'] ?? 'N') }} Knee Separator</td>
        <td>{{ pdfBox($devices['handroll_left'] ?? 'N') }} Handroll Left</td>
        <td>{{ pdfBox($devices['handroll_right'] ?? 'N') }} Handroll Right</td>
        <td>{{ pdfBox($devices['hand_splint_left'] ?? 'N') }} Hand Splint Left</td>
    </tr>
    <tr>
        <td>{{ pdfBox($devices['hand_splint_right'] ?? 'N') }} Hand Splint Right</td>
        <td>{{ pdfBox($devices['prosthesis_left'] ?? 'N') }} Prosthesis Left</td>
        <td>{{ pdfBox($devices['prosthesis_right'] ?? 'N') }} Prosthesis Right</td>
        <td>{{ pdfBox($devices['brace_splint_left'] ?? 'N') }} Brace / Splint Left</td>
    </tr>
    <tr>
        <td>{{ pdfBox($devices['brace_splint_right'] ?? 'N') }} Brace / Splint Right</td>
        <td colspan="3"><strong>Other:</strong> {{ pdfText($devices['device_other'] ?? '') }}</td>
    </tr>
    <tr>
        <td colspan="4"><strong>Instruction / Wear Schedule:</strong> {{ pdfText($devices['instruction_wear_schedule'] ?? '') }}</td>
    </tr>
</table>

<div class="section-title">Dentures / Hearing Aid / Glasses</div>

<table class="border-table">
    <tr>
        <td>{{ pdfBox($devices['dentures_none'] ?? 'N') }} Dentures None</td>
        <td>{{ pdfBox($devices['dentures_full_upper'] ?? 'N') }} Full Upper</td>
        <td>{{ pdfBox($devices['dentures_full_lower'] ?? 'N') }} Full Lower</td>
        <td>{{ pdfBox($devices['dentures_partial_upper'] ?? 'N') }} Partial Upper</td>
    </tr>
    <tr>
        <td>{{ pdfBox($devices['dentures_partial_lower'] ?? 'N') }} Partial Lower</td>
        <td>{{ pdfBox($devices['hearing_aid_none'] ?? 'N') }} Hearing Aid None</td>
        <td>{{ pdfBox($devices['hearing_aid_left'] ?? 'N') }} Hearing Aid Left</td>
        <td>{{ pdfBox($devices['hearing_aid_right'] ?? 'N') }} Hearing Aid Right</td>
    </tr>
    <tr>
        <td>{{ pdfBox($devices['glasses'] ?? 'N') }} Glasses</td>
        <td colspan="3"></td>
    </tr>
</table>

<div class="section-title">Nutritional Status</div>

<table class="border-table">
    <tr>
        <td>{{ pdfBox($nutrition['regular_diet'] ?? 'N') }} Regular Diet</td>
        <td>{{ pdfBox($nutrition['ncs'] ?? 'N') }} NCS</td>
        <td>{{ pdfBox($nutrition['nas'] ?? 'N') }} NAS</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Supplement / Type:</strong> {{ pdfText($nutrition['supplement_type'] ?? '') }}</td>
        <td><strong>Therapeutic Diet:</strong> {{ pdfText($nutrition['therapeutic_diet'] ?? '') }}</td>
    </tr>
    <tr>
        <td>{{ pdfBox($nutrition['appetite_good'] ?? 'N') }} Appetite Good</td>
        <td>{{ pdfBox($nutrition['appetite_fair'] ?? 'N') }} Appetite Fair</td>
        <td>{{ pdfBox($nutrition['appetite_poor'] ?? 'N') }} Appetite Poor</td>
    </tr>
    <tr>
        <td>{{ pdfBox($nutrition['chewing_problem'] ?? 'N') }} Chewing Problem</td>
        <td>{{ pdfBox($nutrition['swallowing_problem'] ?? 'N') }} Swallowing Problem</td>
        <td>{{ pdfBox($nutrition['mechanical_soft'] ?? 'N') }} Mechanical Soft</td>
    </tr>
    <tr>
        <td>{{ pdfBox($nutrition['puree'] ?? 'N') }} Puree</td>
        <td><strong>Fluid Restriction:</strong> {{ pdfText($nutrition['fluid_restriction'] ?? '') }}</td>
        <td><strong>Weight lbs:</strong> {{ pdfText($nutrition['weight_lbs'] ?? '') }}</td>
    </tr>
    <tr>
        <td>{{ pdfBox($nutrition['weight_stable'] ?? 'N') }} Stable</td>
        <td>{{ pdfBox($nutrition['weight_change'] ?? 'N') }} Weight Change</td>
        <td>
            {{ pdfBox($nutrition['weight_gain'] ?? 'N') }} Gain
            {{ pdfBox($nutrition['weight_loss'] ?? 'N') }} Loss
        </td>
    </tr>
    <tr>
        <td>{{ pdfBox($nutrition['planned'] ?? 'N') }} Planned</td>
        <td>{{ pdfBox($nutrition['unplanned'] ?? 'N') }} Unplanned</td>
        <td>{{ pdfBox($nutrition['md_nutritionist_informed'] ?? 'N') }} MD & Nutritionist Informed</td>
    </tr>
</table>

<div class="section-title">Pain</div>

<table class="border-table">
    <tr>
        <td>
            <strong>Resident expresses pain/discomfort part or all of the time:</strong>
            {{ pdfBox($pain['pain_no'] ?? 'N') }} No
            {{ pdfBox($pain['pain_yes'] ?? 'N') }} Yes
        </td>
    </tr>
    <tr>
        <td><strong>Pain Notes:</strong> {{ pdfText($pain['pain_notes'] ?? '') }}</td>
    </tr>
</table>

<br><br>

<table>
    <tr>
        <td style="width: 60%;">
            <strong>Signature:</strong>
            <span class="line" style="width: 300px;">{{ $assessment->signature ?? '' }}</span>
        </td>
        <td style="width: 40%;">
            <strong>Date:</strong>
            <span class="line" style="width: 150px;">{{ pdfDate($assessment->signature_date ?? null) }}</span>
        </td>
    </tr>
</table>

</body>
</html>
