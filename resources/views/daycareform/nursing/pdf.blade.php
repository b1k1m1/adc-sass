@php
    $renderForScreen = $renderForScreen ?? false;

    function ckBig($bool)
    {
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
    body{ font-family: dejavusans, sans-serif; font-size:12px; }
    .hdr{
        border:1px solid #000; padding:8px; margin-bottom:8px;
        page-break-inside: avoid; break-inside: avoid;
    }
    .title{ font-size:16px; font-weight:700; text-align:center; margin-bottom:6px; }
    .row{ display:flex; gap:10px; }
    .col{ flex:1; }
    .box{
        border:1px solid #000; padding:8px; margin-bottom:8px;
        page-break-inside: avoid; break-inside: avoid;
    }
    .sec-title{ font-weight:700; margin-bottom:6px; text-transform:uppercase; }
    .ucheck{
        font-size:18px; font-weight:700; display:inline-block; width:18px;
        text-align:center; vertical-align:middle; margin-right:6px;
    }
    .item{ display:inline-block; margin-right:14px; margin-bottom:3px; }
    .lbl{ font-weight:700; }
    table.meta{ width:100%; border-collapse:collapse; }
    table.meta td{ padding:2px 4px; vertical-align:top; }
    .line{ border-bottom:1px solid #000; display:inline-block; min-width:140px; height:14px; }
    .muted{ color:#333; font-size:11px; }

    body{
    font-family: dejavusans, sans-serif;
    font-size: {{ ($renderForScreen ?? false) ? '14px' : '12px' }};
    }
    .ucheck{
    font-size: {{ ($renderForScreen ?? false) ? '20px' : '18px' }};
    }
</style>

<div class="hdr">
    <div class="title"><h4 class="mt-3">{{ $company->comp_nm ?? 'Company Name'  }}</h4><br>NURSING ASSESSMENT</div>

    @php
        $lastName  = $member->LastName  ?? $member->lastname  ?? '';
        $firstName = $member->FirstName ?? $member->firstname ?? '';
        $pid       = $member->ParticipantId ?? $assessment->participant_id ?? '';
    @endphp

    <table class="meta">
        <tr>
            <td><span class="lbl">Member Name:</span> {{ trim($lastName . ', ' . $firstName) }}</td>
            <td><span class="lbl">MR#:</span> {{ $assessment->medical_record_no ?? '' }}</td>
            <td><span class="lbl">D.O.A.:</span> {{ fmtDate($assessment->doa ?? null) }}</td>
        </tr>
        <tr>
            <td><span class="lbl">Date of Assessment:</span> {{ fmtDate($assessment->assessment_date ?? null) }}</td>
            <td><span class="lbl">Shift:</span> {{ $assessment->shift_type ?? '' }}</td>
            <td>
                {!! ckBig(($assessment->assessment_type ?? '') === 'Initial') !!} Initial
                {!! ckBig(($assessment->assessment_type ?? '') === 'Annual') !!} Annual (Within 14 days)
            </td>
        </tr>
        <tr>
            <td><span class="lbl">Height:</span> {{ $assessment->height ?? '' }}</td>
            <td><span class="lbl">Weight:</span> {{ $assessment->weight ?? '' }}</td>
            <td><span class="lbl">Allergies:</span> {{ $assessment->allergies ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="3"><span class="lbl">Diagnosis:</span> {{ $assessment->diagnosis ?? '' }}</td>
        </tr>
    </table>
</div>

<div class="box">
    <div class="sec-title">Cognitive Function <span class="muted">(Please circle responses)</span></div>

    <div>
        <span class="item">{!! ckBig($assessment->cognitive_alert) !!} Alert</span>
        <span class="item">{!! ckBig($assessment->cognitive_oriented_person) !!} Oriented to Person</span>
        <span class="item">{!! ckBig($assessment->cognitive_oriented_place) !!} Place</span>
        <span class="item">{!! ckBig($assessment->cognitive_oriented_time) !!} Time</span>
        <span class="item">{!! ckBig($assessment->cognitive_orientation_fluctuates) !!} Orientation fluctuates</span>
        <span class="item">{!! ckBig($assessment->cognitive_confused) !!} Confused</span>
        <span class="item">{!! ckBig($assessment->cognitive_wanders_unaware) !!} Wanders / Unaware</span>
    </div>

    <div style="margin-top:6px;">
        <span class="lbl">Memory -</span>
        <span class="item"><span class="lbl">Short term:</span> {{ $assessment->memory_short_term ?? '' }}</span>
        <span class="item"><span class="lbl">Long term:</span> {{ $assessment->memory_long_term ?? '' }}</span>
    </div>

    <div style="margin-top:6px;">
        <span class="lbl">Decision making ability:</span> {{ $assessment->decision_making ?? '' }}
    </div>

    <div style="margin-top:6px;">
        <span class="lbl">Understands information:</span> {{ $assessment->understanding_info ?? '' }}
    </div>
</div>

<div class="box">
    <div class="sec-title">Communication / Hearing Patterns / Vision</div>

    <div class="lbl">Mode of expression:</div>
    <div>
        <span class="item">{!! ckBig($assessment->expr_speech) !!} Speech</span>
        <span class="item">{!! ckBig($assessment->expr_writing) !!} Writing</span>
        <span class="item">{!! ckBig($assessment->expr_signs_gestures_sounds) !!} Signs/Gestures/Sounds</span>
        <span class="item">{!! ckBig($assessment->expr_incoherent_speech) !!} Incoherent speech</span>
        <span class="item">{!! ckBig($assessment->communication_board) !!} Communication board</span>
    </div>

    <div style="margin-top:6px;">
        <span class="item">{!! ckBig($assessment->communicates_understood) !!} Communicates and is understood</span>
        <span class="item">{!! ckBig($assessment->communicates_difficult_to_understand) !!} Communicates but difficult to understand</span>
        <span class="item">{!! ckBig($assessment->cannot_communicate_needs) !!} Cannot communicate or convey needs</span>
    </div>

    <div style="margin-top:6px;">
        <span class="lbl">Speech:</span> {{ $assessment->speech_quality ?? '' }}
        &nbsp;&nbsp; <span class="lbl">Primary language spoken:</span> {{ $assessment->primary_language_spoken ?? '' }}
        <!--&nbsp;&nbsp; <span class="item">{!! ckBig($assessment->communication_board) !!} Communication board</span> -->
    </div>

    <hr>

    <div class="lbl">Hearing</div>
    <div>
        <span class="item"><span class="lbl">Right ear:</span> {{ $assessment->right_ear_status ?? '' }}</span>
        <span class="item"><span class="lbl">Left ear:</span> {{ $assessment->left_ear_status ?? '' }}</span>
        <span class="item">{!! ckBig($assessment->hearing_minimal_difficulty_not_quiet) !!} Minimal difficulty when not in quiet conditions</span>
        <span class="item">{!! ckBig($assessment->hearing_highly_impaired_no_useful) !!} Highly impaired, lack of useful hearing</span>
    </div>
    <div style="margin-top:6px;">
        <span class="item"><span class="lbl">Date of last hearing evaluation:</span> {{ fmtDate($assessment->date_last_hearing_eval ?? null) }}</span>
    </div>

    <hr>

    <div class="lbl">Vision</div>
    <div style="margin-bottom:4px;">
        <span class="lbl">Ability to see:</span> {{ $assessment->vision_ability ?? '' }}
    </div>

    <div>
        <span class="item">{!! ckBig($assessment->cataracts_right) !!} Cataracts Right</span>
        <span class="item">{!! ckBig($assessment->cataracts_right_sx_yes) !!} Sx correction Yes</span>
        <span class="item">{!! ckBig($assessment->cataracts_right_sx_no) !!} No</span>

        <span class="item" style="margin-left:18px;">{!! ckBig($assessment->cataracts_left) !!} Cataracts Left</span>
        <span class="item">{!! ckBig($assessment->cataracts_left_sx_yes) !!} Sx correction Yes</span>
        <span class="item">{!! ckBig($assessment->cataracts_left_sx_no) !!} No</span>
    </div>

    <div style="margin-top:6px;">
        <span class="item">{!! ckBig($assessment->can_read_newsprint) !!} Can read newsprint</span>
        <span class="item">{!! ckBig($assessment->can_read_large_print) !!} Can read large print</span>
        <span class="item">{!! ckBig($assessment->macular_degeneration) !!} Macular degeneration</span>
        <span class="item">{!! ckBig($assessment->glaucoma) !!} Glaucoma</span>
        <span class="item">{!! ckBig($assessment->blind_right) !!} Blind - Right</span>
        <span class="item">{!! ckBig($assessment->blind_left) !!} Left</span>
    </div>
    <div style="margin-top:6px;">
        <span class="item"><span class="lbl">Date of last eye exam:</span> {{ fmtDate($assessment->date_last_eye_exam ?? null) }}</span>
    </div>
</div>

<div class="box">
    <div class="sec-title">Physical Functioning</div>

    <div class="lbl">Ambulates:</div>
    <div>
        <span class="item">{!! ckBig($assessment->ambulates_alone) !!} Alone</span>
        <span class="item">{!! ckBig($assessment->ambulates_with_assistance) !!} With assistance</span>
    </div>

    <div style="margin-top:6px;" class="lbl">Uses:</div>
    <div>
        <span class="item">{!! ckBig($assessment->uses_cane) !!} Cane</span>
        <span class="item">{!! ckBig($assessment->uses_walker) !!} Walker</span>
        <span class="item">{!! ckBig($assessment->uses_wheelchair) !!} Wheelchair</span>
    </div>

    <div style="margin-top:6px;" class="lbl">Wheelchair:</div>
    <div>
        <span class="item">{!! ckBig($assessment->wheelchair_independently) !!} Independently</span>
        <span class="item">{!! ckBig($assessment->wheelchair_propel_with_help) !!} Can propel with help</span>
        <span class="item">{!! ckBig($assessment->wheelchair_unable_to_propel) !!} Unable to propel</span>
    </div>

    <div style="margin-top:6px;" class="lbl">Transfers:</div>
    <div>
        <span class="item">{!! ckBig($assessment->transfers_independently) !!} Independently</span>
        <span class="item">{!! ckBig($assessment->transfers_with_assistance_1) !!} With assistance of 1</span>
        <span class="item">{!! ckBig($assessment->transfers_with_assistance_2) !!} With assistance of 2</span>
    </div>
</div>
