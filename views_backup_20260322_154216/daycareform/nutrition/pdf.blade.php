@php
$renderForScreen = $renderForScreen ?? false;

// Same checkbox engine as Pain (DejaVu unicode ballot boxes)
function ckBig($bool)
{
    return $bool
        ? '<span class="ucheck">&#x2611;</span>'  // ☑
        : '<span class="ucheck">&#x2610;</span>'; // ☐
}

function fmtDate($d){
    if(!$d) return '';
    try { return \Carbon\Carbon::parse($d)->format('m/d/Y'); }
    catch(\Exception $e){ return $d; }
}

$companyName = config('app.name', 'DAY CARE');

$ln  = $member->LastName ?? $member->lastname ?? '';
$fn  = $member->FirstName ?? $member->firstname ?? '';
$pid = $member->ParticipantId ?? $member->participant_id ?? $assessment->participant_id ?? '';

// Convenience vars
$ws = $assessment->weight_status ?? '';        // 'lost' | 'gain' | 'stable'
$ah = $assessment->appetite_hydration ?? '';   // 'good' | 'fair' | 'poor'

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
  .nob { border:none !important; }

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
  <div class="title">NUTRITION QUARTERLY ASSESSMENT</div>
</div>
<hr>

{{-- Header --}}

<div class="box">
  <table>
    <tr>
      <td><b>Member Name:</b> {{ $ln }}, {{ $fn }}</td>
      <td style="width:220px;"><b>MR#:</b> {{ $assessment->medical_record_no ?? '' }}</td>
      <td style="width:160px;"><b>Assessment Date:</b> {{ fmtDate($assessment->assessment_date ?? null) }}</td>
    </tr>
  </table>
</div>

{{-- Weight --}}

<div class="box">
  <div class="t">Weight</div>
  <table>
    <tr>
      <td style="width:220px;"><b>Weight (LBS):</b> {{ $assessment->weight_lbs ?? '' }}</td>
      <td>
        {!! ckBig($ws === 'lost') !!} Lost &nbsp;&nbsp;
        {!! ckBig($ws === 'gain') !!} Gain &nbsp;&nbsp;
        {!! ckBig($ws === 'stable') !!} Stable
      </td>
    </tr>
  </table>
</div>

{{-- Diagnosis / Med changes --}}

<div class="box">
  <div class="t">New Medical Diagnosis</div>
  {{ $assessment->new_medical_diagnosis ?? '' }}
</div>

<div class="box">
  <div class="t">Medication Changes</div>
  {{ $assessment->medication_changes ?? '' }}
</div>

{{-- Appetite / Hydration + Current Diet Order --}}

<div class="box">
  <div class="t">Appetite / Hydration</div>
  {!! ckBig($ah === 'good') !!} GOOD (&gt;75%) &nbsp;&nbsp;
  {!! ckBig($ah === 'fair') !!} FAIR (&gt;50%) &nbsp;&nbsp;
  {!! ckBig($ah === 'poor') !!} POOR (&lt;50%)
</div>

<div class="box">
  <div class="t">Current Diet Order</div>
  {{ $assessment->current_diet_order ?? '' }}
</div>

{{-- Changes in diet order --}}

<div class="box">
  <div class="t">Changes in Diet Order</div>
  {!! ckBig(!empty($assessment->diet_order_changed)) !!} YES &nbsp;&nbsp;
  {!! ckBig(empty($assessment->diet_order_changed)) !!} NO
</div>

{{-- Labs + Comments --}}

<div class="box">
  <div class="t">Labs</div>
  {{ $assessment->labs ?? '' }}
</div>

<div class="box">
  <div class="t">Comments</div>
  {{ $assessment->comments ?? '' }}
</div>

{{-- Nutrition Care Plan (Goals) --}}

<div class="box">
  <div class="t">Nutrition Care Plan (Goals)</div>
  {!! ckBig(!empty($assessment->cp_tolerate_current_diet)) !!} Client will tolerate current diet &nbsp;&nbsp;
  {!! ckBig(!empty($assessment->cp_bp_bg_within_limits)) !!} Blood Pressure / Blood Glucose within acceptable limits &nbsp;&nbsp;
  {!! ckBig(!empty($assessment->cp_weight_stable_or_change)) !!} Weight stable or will lose/gain &nbsp;&nbsp;
  {!! ckBig(!empty($assessment->cp_understands_diet)) !!} Understands diet and will try to follow at home
</div>

{{-- Recommendations / Interventions --}}

<div class="box">
  <div class="t">Recommendations / Interventions</div>

{!! ckBig(!empty($assessment->rec_decrease_salty_saturated)) !!} Decrease salty foods/snacks & saturated fats   
{!! ckBig(!empty($assessment->rec_encourage_weight_1_2)) !!} Encourage increase/decrease 1–2# per week/month   
{!! ckBig(!empty($assessment->rec_increase_fiber_protein_calcium_omega3)) !!} Increase fiber/protein/calcium/omega-3 foods   
{!! ckBig(!empty($assessment->rec_limit_spicy_greasy_acidic_citrus_caffeine)) !!} Limit spicy/greasy/acidic/citrus/caffeine   
{!! ckBig(!empty($assessment->rec_small_frequent_meals_snack)) !!} Small frequent meals and extra snack   
{!! ckBig(!empty($assessment->rec_softer_foods_chewing)) !!} Provide softer foods to facilitate chewing   
{!! ckBig(!empty($assessment->rec_cho_counseling_timing_consistency)) !!} CHO counseling, meal timing and consistency   
{!! ckBig(!empty($assessment->rec_limit_vitamin_k_foods)) !!} Limit foods high in vitamin K   
{!! ckBig(!empty($assessment->rec_increase_physical_activity)) !!} Encourage physical activity as tolerated

</div>

{{-- Signature --}}

<div class="box">
  <table>
    <tr>
      <td><b>Dietitian Signature:</b> {{ $assessment->dietitian_signature ?? '' }}</td>
      <td style="width:220px;"><b>Date:</b> {{ fmtDate($assessment->signed_date ?? null) }}</td>
    </tr>
  </table>
</div>
