@php
    $renderForScreen = $renderForScreen ?? false;

    $imgPainLocation = $renderForScreen
        ? asset('images/pain_location.jpg')
        : public_path('images/pain_location.jpg');

    $imgPainSeverity = $renderForScreen
        ? asset('images/pain_severity.png')
        : public_path('images/pain_severity.png');

    function ckBig($bool)
    {
        // Unicode ballot boxes (render cleanly in mPDF with DejaVu)
        return $bool
            ? '<span class="ucheck">&#x2611;</span>'  // ☑
            : '<span class="ucheck">&#x2610;</span>'; // ☐
    }

  function fmtDate($d){
    if(!$d) return '';
    try { return \Carbon\Carbon::parse($d)->format('m/d/Y'); } catch(\Exception $e){ return $d; }
  }

  $companyName = config('app.name', 'DAY CARE');
@endphp

<style>
  body { font-family: sans-serif; font-size: 12px; }
  .hdr { text-align:center; line-height: 1.2; }
  .hdr .cname { font-size: 18px; font-weight: 700; }
  .hdr .title { font-size: 16px; font-weight: 800; text-decoration: underline; margin-top: 6px; }
  .box { border:1px solid #000; padding:6px; margin-bottom:6px; }
  .t { font-weight: 700; margin-bottom: 4px; }
  table { width:100%; border-collapse: collapse; }
  td, th { border:1px solid #000; padding:4px; vertical-align: top; }
  .nob { border:none !important; }
  .cb{
    display:inline-block;
    width:16px;
    height:16px;
    border:1px solid #000;
    line-height:16px;
    text-align:center;
    font-size:14px;
    font-weight:700;
    margin-right:6px;
    vertical-align:middle;
    }
    .cb-yes{ background:#fff; }
    .cb-no{ background:#fff; }

    body { font-family: dejavusans, sans-serif; }

    .ucheck{
    font-size: 18px;
    font-weight: 700;
    display:inline-block;
    width: 18px;
    text-align:center;
    vertical-align: middle;
    margin-right: 6px;
    }

    /* Prevent section boxes from splitting across pages */
    .box{
    page-break-inside: avoid;
    break-inside: avoid;
    }
    table{
    page-break-inside: avoid;
    break-inside: avoid;
    }
    tr, td, th{
    page-break-inside: avoid;
    break-inside: avoid;
    }

</style>

<div class="hdr">
  <div class="cname">{{ $company->comp_nm ?? 'Company Name' }}</div>
  <div class="title">PAIN ASSESSMENT TOOL</div>
</div>
<hr>

@php
  $ln  = $member->LastName ?? $member->lastname ?? '';
  $fn  = $member->FirstName ?? $member->firstname ?? '';
  $pid = $member->ParticipantId ?? $member->participant_id ?? $assessment->participant_id ?? '';
@endphp

<div class="box">
  <table>
    <tr>
      <td><b>Member Name:</b> {{ $ln }}, {{ $fn }}</td>
      <td style="width:220px;"><b>MR#:</b> {{ $assessment->medical_record_no ?? '' }}</td>
      <td style="width:160px;"><b>Assessment Date:</b> {{ fmtDate($assessment->assessment_date) }}</td>
    </tr>
  </table>
</div>

<div class="box">
  <div class="t">Reason for assessment:</div>
  {!! ckBig($assessment->reason_new_admission) !!} New admission &nbsp;&nbsp;
  {!! ckBig($assessment->reason_readmission) !!} Readmission &nbsp;&nbsp;
  {!! ckBig($assessment->reason_further_assessment) !!} Further Assessment &nbsp;&nbsp;
  {!! ckBig($assessment->reason_change_in_condition) !!} Change in condition &nbsp;&nbsp;
  {!! ckBig($assessment->reason_quarterly) !!} Quarterly
</div>

<div class="box">
  <div class="t">1. Location of pain</div>

  <table style="width:100%;">
    <tr>
      <td style="width:60%; text-align:center;">
        <img
          src="{{ $imgPainLocation }}"
          style="max-width:100%; height:auto; max-height:220px;">
      </td>
      <td style="vertical-align:top; padding-left:10px;">
        {{ $assessment->pain_location ?? '' }}
      </td>
    </tr>
  </table>
</div>

<div class="box">
  <div class="t">2. Severity of pain</div>

  <table style="width:100%;">
    <tr>
      <td style="width:60%; text-align:center;">
        <img
          src="{{ $imgPainSeverity }}"
          style="max-width:100%; height:auto; max-height:140px;">
      </td>
      <td style="vertical-align:top; padding-left:10px;">
        <b>Score:</b> {{ $assessment->pain_severity_score ?? '' }}<br>
        <b>Label:</b> {{ $assessment->pain_severity_label ?? '' }}
      </td>
    </tr>
  </table>
</div>

<div class="box">
  <div class="t">Questions / Comments</div>
  <table>
    <tr><td style="width:40%;">Present level of pain</td><td>{{ $assessment->q_present_level ?? '' }}</td></tr>
    <tr><td>Rate when pain is at its least</td><td>{{ $assessment->q_least_rate ?? '' }}</td></tr>
    <tr><td>What makes the pain better?</td><td>{{ $assessment->q_better ?? '' }}</td></tr>
    <tr><td>Rate when pain is at its worst</td><td>{{ $assessment->q_worst_rate ?? '' }}</td></tr>
    <tr><td>What makes the pain worse?</td><td>{{ $assessment->q_worse ?? '' }}</td></tr>
    <tr><td>Continuous or intermittent?</td><td>{{ $assessment->q_continuous ?? '' }}</td></tr>
    <tr><td>When did the pain start?</td><td>{{ $assessment->q_start ?? '' }}</td></tr>
    <tr><td>Cause of this pain?</td><td>{{ $assessment->q_cause ?? '' }}</td></tr>
    <tr><td>Satisfied pain level</td><td>{{ $assessment->q_satisfied_level ?? '' }}</td></tr>
  </table>
</div>

<div class="box">
  <div class="t">3. Quality</div>

  @php
    $qualities = [
      'qual_aching'        => 'aching',
      'qual_throbbing'     => 'throbbing',
      'qual_shooting'      => 'shooting',
      'qual_stabbing'      => 'stabbing',
      'qual_penetrating'   => 'penetrating',
      'qual_sharp'         => 'sharp',
      'qual_burning'       => 'burning',
      'qual_tender'        => 'tender',
      'qual_exhausting'    => 'exhausting',
      'qual_tiring'        => 'tiring',
      'qual_unbearable'    => 'unbearable',
      'qual_numb'          => 'numb',
      'qual_nagging'       => 'nagging',
      'qual_hammering'     => 'hammering',
      'qual_pins_needles'  => 'pins & needles',
      'qual_gnawing'       => 'gnawing',
      'qual_tingling'      => 'tingling',
      'qual_stretching'    => 'stretching',
      'qual_pulling'       => 'pulling',
    ];
  @endphp

  @foreach($qualities as $field => $label)
    {!! ckBig($assessment->$field ?? false) !!} {{ $label }}&nbsp;&nbsp;
  @endforeach

  <div style="margin-top:6px;"><b>Other:</b> {{ $assessment->qual_other ?? '' }}</div>
</div>

<div class="box">
  <div class="t">4. Effects on activities of daily living</div>
  <table>
    <thead>
      <tr><th>Activity</th><th style="width:120px;">Yes / No</th><th>Comments</th></tr>
    </thead>
    <tbody>
      <tr><td>sleep and rest</td><td>{{ $assessment->adl_sleep_yn ?? '' }}</td><td rowspan="6">{{ $assessment->adl_comments ?? '' }}</td></tr>
      <tr><td>social activities</td><td>{{ $assessment->adl_social_yn ?? '' }}</td></tr>
      <tr><td>appetite</td><td>{{ $assessment->adl_appetite_yn ?? '' }}</td></tr>
      <tr><td>physical activity & mobility</td><td>{{ $assessment->adl_physical_yn ?? '' }}</td></tr>
      <tr><td>emotions</td><td>{{ $assessment->adl_emotions_yn ?? '' }}</td></tr>
      <tr><td>sexuality / intimacy</td><td>{{ $assessment->adl_sexuality_yn ?? '' }}</td></tr>
    </tbody>
  </table>
</div>

<div class="box">
  <div class="t">5. Effects of pain on quality of life</div>
  {{ $assessment->quality_of_life_text ?? '' }}
</div>

<div class="box">
  <div class="t">6. Symptoms</div>

  @php
    $symptoms = [
      'sym_constipation'  => 'constipation',
      'sym_nausea'        => 'nausea',
      'sym_vomiting'      => 'vomiting',
      'sym_fatigue'       => 'fatigue',
      'sym_insomnia'      => 'insomnia',
      'sym_depression'    => 'depression',
      'sym_drowsy'        => 'drowsy',
      'sym_sore_mouth'    => 'sore mouth',
      'sym_weakness'      => 'weakness',
      'sym_short_breath'  => 'short of breath',
    ];
  @endphp

  @foreach($symptoms as $field => $label)
    {!! ckBig($assessment->$field ?? false) !!} {{ $label }}&nbsp;&nbsp;
  @endforeach

  <div style="margin-top:6px;"><b>Other:</b> {{ $assessment->sym_other ?? '' }}</div>
</div>

<div class="box">
  <div class="t">7. Behaviors</div>

  @php
    $behaviors = [
      'beh_calling_out' => 'calling out',
      'beh_restlessness' => 'restlessness',
      'beh_disorientation' => 'disorientation',
      'beh_not_eating' => 'not eating',
      'beh_pacing' => 'pacing',
      'beh_not_sleeping' => 'not sleeping',
      'beh_withdrawn' => 'withdrawn',
      'beh_groaning' => 'groaning/moaning',
      'beh_rocking' => 'rocking',
      'beh_new_immobility' => 'new immobility',
      'beh_tense' => 'tense',
      'beh_distressed' => 'distressed',
      'beh_distracted' => 'distracted',
      'beh_crying' => 'crying',
      'beh_inexpressive' => 'inexpressive',
      'beh_fists_clenched' => 'fists clenched',
      'beh_striking_out' => 'striking out',
      'beh_knees_pulled_up' => 'knees pulled up',
      'beh_frowning' => 'frowning',
      'beh_facial_grimacing' => 'facial grimacing',
      'beh_sad' => 'sad',
      'beh_frighten' => 'frighten',
      'beh_resistant_movement' => 'resistant to movement',
      'beh_pulling_pushing_away' => 'pulling/pushing away',
    ];
  @endphp

  @foreach($behaviors as $field => $label)
    {!! ckBig($assessment->$field ?? false) !!} {{ $label }}&nbsp;&nbsp;
  @endforeach

  <div style="margin-top:6px;"><b>Other:</b> {{ $assessment->beh_other ?? '' }}</div>
</div>

<div class="box">
  <div class="t">8. Past pain management</div>
  <b>Past pain experienced:</b> {{ $assessment->past_pain_experienced ?? '' }}<br>
  <b>Past management:</b> {{ $assessment->past_pain_management ?? '' }}
</div>

<div class="box">
  <div class="t">9. Support system</div>
  {{ $assessment->support_system ?? '' }}
</div>

<div class="box">
  <div class="t">10. Other concerns related to pain</div>
  {{ $assessment->other_concerns ?? '' }}
</div>

<div class="box">
  <div class="t">11. Nursing pain diagnosis</div>
  {!! ckBig($assessment->dx_visceral) !!} visceral
  {!! ckBig($assessment->dx_somatic) !!}somatic (muscle or bone)
  {!! ckBig($assessment->dx_raised_intracranial) !!} raised intracranial pressure
  {!! ckBig($assessment->dx_naturopathic) !!} naturopathic
  {!! ckBig($assessment->dx_mixed) !!} mixed
  {!! ckBig($assessment->dx_unknown) !!} unknown
</div>

<div class="box">
  <table>
    <tr>
      <td><b>Date Care Plan updated:</b> {{ fmtDate($assessment->care_plan_updated_date) }}</td>
      <td style="width:180px;"><b>P.T. Evaluation:</b> {{ $assessment->pt_evaluation_yes ? 'Yes' : 'No' }}</td>
      <td style="width:220px;"><b>Signature:</b> {{ $assessment->signature ?? '' }}</td>
    </tr>
  </table>
  <div style="margin-top:6px;"><b>Plan:</b> {{ $assessment->plan_of_care ?? '' }}</div>
</div>
