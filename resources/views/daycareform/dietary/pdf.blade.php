@php
$renderForScreen = $renderForScreen ?? false;

// Same checkbox engine as Pain (DejaVu unicode ballot boxes)
function ckBig($bool)
{
return $bool
? '<span class="ucheck">☑</span>'  // ☑
: '<span class="ucheck">☐</span>'; // ☐
}

function fmtDate($d){
if(!$d) return '';
try { return \Carbon\Carbon::parse($d)->format('m/d/Y'); } catch(\Exception $e){ return $d; }
}

$ln  = $member->LastName ?? $member->lastname ?? '';
$fn  = $member->FirstName ?? $member->firstname ?? '';
$pid = $member->ParticipantId ?? $member->participant_id ?? $assessment->participant_id ?? '';

$dob = $member->DateOfBirth ?? $member->dateofbirth ?? null;
$adm = $member->AdmissionDate ?? $member->admission_date ?? null;

$companyName = config('app.name', 'DAY CARE');
@endphp

<style>
  body { font-family: dejavusans, sans-serif; font-size: 12px; }
  .hdr { text-align:center; line-height: 1.2; }
  .hdr .cname { font-size: 18px; font-weight: 700; }
  .hdr .title { font-size: 16px; font-weight: 800; text-decoration: underline; margin-top: 6px; }
  .box { border:1px solid #000; padding:6px; margin-bottom:6px; }
  .t { font-weight: 700; margin-bottom: 4px; }
  table { width:100%; border-collapse: collapse; }
  td, th { border:1px solid #000; padding:4px; vertical-align: top; }

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
  .box{ page-break-inside: avoid; break-inside: avoid; }
  table{ page-break-inside: avoid; break-inside: avoid; }
  tr, td, th{ page-break-inside: avoid; break-inside: avoid; }
</style>

<div class="hdr">
  <div class="cname">{{ $company->comp_nm ?? 'Company Name' }}</div>
  <div class="title">DIETARY INITIAL ASSESSMENT &amp; CARE PLAN</div>
</div>
<hr>

{{-- Header line --}}

<div class="box">
  <table>
    <tr>
      <td><b>Participant:</b> {{ $pid }} - {{ $ln }}, {{ $fn }}</td>
      <td style="width:180px;"><b>Admission Date:</b> {{ fmtDate($adm) }}</td>
      <td style="width:160px;"><b>DOB:</b> {{ fmtDate($dob) }}</td>
      <td style="width:200px;"><b>MR#:</b> {{ $assessment->medical_record_no ?? '' }}</td>
    </tr>
  </table>
</div>

{{-- Top fields --}}

<div class="box">
  <table>
    <tr>
      <td style="width:160px;"><b>Date:</b> {{ fmtDate($assessment->assessment_date ?? null) }}</td>
      <td style="width:140px;"><b>Height:</b> {{ $assessment->height ?? '' }}</td>
      <td style="width:140px;"><b>Weight:</b> {{ $assessment->weight ?? '' }}</td>
      <td style="width:140px;"><b>IBW:</b> {{ $assessment->ibw ?? '' }}</td>
    </tr>
    <tr>
      <td colspan="4"><b>Weight Changes:</b> {{ $assessment->weight_changes ?? '' }}</td>
    </tr>
  </table>
</div>

<div class="box">
  <table>
    <tr>
      <td><b>Eating Problems / Devices:</b> {{ $assessment->eating_problems_devices ?? '' }}</td>
      <td style="width:200px;"><b>Appetite:</b> {{ $assessment->appetite ?? '' }}</td>
    </tr>
    <tr>
      <td><b>Religious Restrictions:</b> {{ $assessment->religious_restrictions ?? '' }}</td>
      <td><b>Teeth:</b> {{ $assessment->teeth ?? '' }} &nbsp;&nbsp; <b>Dentures:</b> {{ $assessment->dentures ?? '' }}</td>
    </tr>
  </table>
</div>

<div class="box">
  <table>
    <tr>
      <td><b>Diet Order:</b> {{ $assessment->diet_order ?? '' }}</td>
      <td style="width:260px;"><b>Food Allergy:</b> {{ $assessment->food_allergy ?? '' }}</td>
    </tr>
    <tr>
      <td><b>Diagnosis:</b> {{ $assessment->diagnosis ?? '' }}</td>
      <td><b>Medications:</b> {{ $assessment->medications ?? '' }}</td>
    </tr>
  </table>
</div>

<div class="box">
  <div class="t">Labs</div>
  {{ $assessment->labs ?? '' }}
</div>

<div class="box">
  <div class="t">Food Preferences / Dislikes</div>
  {{ $assessment->food_preferences_dislikes ?? '' }}
</div>

<div class="box">
  <div class="t">Initial Assessment</div>
  {{ $assessment->initial_assessment ?? '' }}
</div>

<div class="box">
  <div class="t">Nutrition Care Plan (Goals)</div>
  {!! ckBig($assessment->goal_tolerate_current_diet ?? false) !!} Client will tolerate current diet &nbsp;&nbsp;
  {!! ckBig($assessment->goal_bp_bg_within_limits ?? false) !!} Blood Pressure / Blood Glucose within acceptable limits &nbsp;&nbsp;
  {!! ckBig($assessment->goal_weight_stable_or_lose_gain ?? false) !!} Weight stable or will lose/gain &nbsp;&nbsp;
  {!! ckBig($assessment->goal_select_healthy_choices ?? false) !!} Select healthy food choices &nbsp;&nbsp;
  {!! ckBig($assessment->goal_verbalize_knowledge_follow_home ?? false) !!} Verbalize knowledge and will follow diet at home
</div>

<div class="box">
  <div class="t">Interventions</div>
  {!! ckBig($assessment->int_educate_decrease_salty_alternates ?? false) !!} Educate to decrease salty foods and alternate &nbsp;&nbsp;
  {!! ckBig($assessment->int_encourage_change_1_2_per_week_month ?? false) !!} Encourage increase/decrease 1–2# per week/month &nbsp;&nbsp;
  {!! ckBig($assessment->int_encourage_consume_over_75_each_meal ?? false) !!} Encourage to consume &gt;75% each meal &nbsp;&nbsp;
  {!! ckBig($assessment->int_encourage_increase_dietary_fiber ?? false) !!} Encourage to increase dietary fiber &nbsp;&nbsp;
  {!! ckBig($assessment->int_educate_avoid_dark_green_vit_k ?? false) !!} Educate to avoid dark green vegetables (Vit K) &nbsp;&nbsp;
  {!! ckBig($assessment->int_educate_cho_counting_timing_exercise ?? false) !!} Educate on CHO counting, meal timing, exercise &nbsp;&nbsp;
  {!! ckBig($assessment->int_educate_increase_decrease_fluids ?? false) !!} Educate increase/decrease fluids &nbsp;&nbsp;
  {!! ckBig($assessment->int_monitor_alt_food_consistency ?? false) !!} Monitor / alternate food consistency &nbsp;&nbsp;
  {!! ckBig($assessment->int_encourage_increase_protein ?? false) !!} Encourage to increase protein &nbsp;&nbsp;
  {!! ckBig($assessment->int_monitor_monthly_weights ?? false) !!} Monitor monthly weights &nbsp;&nbsp;
  {!! ckBig($assessment->int_encourage_physical_activity ?? false) !!} Encourage physical activity
</div>

<div class="box">
  <table>
    <tr>
      <td><b>Dietitian Signature/Title:</b> {{ $assessment->dietitian_signature_title ?? '' }}</td>
      <td style="width:220px;"><b>Date:</b> {{ fmtDate($assessment->signed_date ?? null) }}</td>
    </tr>
  </table>
</div>
