@extends('admin.admin_dashboard')

@section('admin')

@section('page-toolbar')

<a href="{{ url()->previous() }}"
   class="btn btn-primary waves-effect waves-light">

    {{ t('common.back','Back') }}

</a>

@endsection


<div class="page-content">

    <div class="container-fluid">

        {{-- ============================================================
             Page Title
        ============================================================ --}}

        <div class="row">

            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <div>

                        <h4 class="mb-0 font-size-18">
                            Participant Activity Assessment Report
                        </h4>

                    </div>

                    <div class="page-title-right">

                        @yield('page-toolbar')

                    </div>

                </div>

            </div>

        </div>


        <div class="card shadow-sm">

            <div class="card-body">


                {{-- ====================================================
                     Current Selected Shift
                ==================================================== --}}

                @php

                    $selectedShift = $prefill['trans_flag'] ?? 'A';

                @endphp


                {{-- ====================================================
                     Shift Selection
                ==================================================== --}}

                <div class="card mb-3">

                    <div class="card-body py-3">

                        <div class="d-flex align-items-center gap-3 flex-wrap">

                            <strong>
                                Select Shift:
                            </strong>


                            {{-- Morning --}}

                            <a href="{{ route('paa.report.view', [

                                    'trans_flag' => 'A',

                                    'assessedyear' =>
                                        $prefill['assessedyear']
                                        ?? now()->year,

                                    'quarter' =>
                                        $prefill['quarter']
                                        ?? (string)ceil(now()->month / 3),

                                ]) }}"

                               class="btn
                               {{ $selectedShift === 'A'
                                    ? 'btn-primary'
                                    : 'btn-outline-primary' }}">

                                Morning

                            </a>


                            {{-- Noon --}}

                            <a href="{{ route('paa.report.view', [

                                    'trans_flag' => 'N',

                                    'assessedyear' =>
                                        $prefill['assessedyear']
                                        ?? now()->year,

                                    'quarter' =>
                                        $prefill['quarter']
                                        ?? (string)ceil(now()->month / 3),

                                ]) }}"

                               class="btn
                               {{ $selectedShift === 'N'
                                    ? 'btn-primary'
                                    : 'btn-outline-primary' }}">

                                Noon

                            </a>


                            <span class="badge bg-secondary">

                                Current Shift:

                                {{ $selectedShift === 'A'
                                    ? 'Morning'
                                    : 'Noon' }}

                            </span>

                        </div>

                    </div>

                </div>


                {{-- ====================================================
                     Filter Form
                ==================================================== --}}

                <form method="get"
                      action="{{ route('paa.report.view') }}"
                      class="border rounded p-3 mb-4 bg-light">


                    {{-- Preserve Selected Shift --}}

                    <input type="hidden"
                           name="trans_flag"
                           value="{{ $selectedShift }}">


                    <div class="row g-2 align-items-end">


                        {{-- Participant --}}

                        <div class="col-md-4">

                            <label class="form-label">
                                Participant
                            </label>

                            <select name="participantid"
                                    class="form-select"
                                    required>

                                <option value="">
                                    -- Select --
                                </option>


                                @foreach($participants as $p)

                                    <option value="{{ $p->id }}"

                                        {{
                                            ($prefill['participantid'] ?? '') == $p->id
                                                ? 'selected'
                                                : ''
                                        }}>

                                        {{ $p->id }} — {{ $p->name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Assessed Year --}}

                        <div class="col-md-3">

                            <label class="form-label">
                                Assessed Year
                            </label>

                            <input type="number"
                                   name="assessedyear"
                                   class="form-control"
                                   min="1900"
                                   max="2099"

                                   value="{{ $prefill['assessedyear']
                                        ?? now()->year }}"

                                   required>

                        </div>


                        {{-- Quarter --}}

                        <div class="col-md-2">

                            <label class="form-label">
                                Quarter
                            </label>

                            <select name="quarter"
                                    class="form-select"
                                    required>

                                <option value="">
                                    -- Q --
                                </option>


                                @foreach([1,2,3,4] as $q)

                                    <option value="{{ $q }}"

                                        {{
                                            (string)$q ===
                                            (string)($prefill['quarter'] ?? '')
                                                ? 'selected'
                                                : ''
                                        }}>

                                        Q{{ $q }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Buttons --}}

                        <div class="col-md-3 d-flex gap-2">

                            <button class="btn btn-dark mt-3"
                                    type="submit">

                                Show Report

                            </button>


                            <a href="{{ route('paa.report.view', [
                                    'trans_flag' => $selectedShift
                                ]) }}"
                               class="btn btn-outline-secondary mt-3">

                                Clear

                            </a>

                        </div>

                    </div>

                </form>


                {{-- ====================================================
                     Report Output
                ==================================================== --}}

                @if($record)

                    @php

                        $mark = fn($v) =>
                            $v === 'Y'
                                ? '✓'
                                : '';

                        $date =
                            \Illuminate\Support\Carbon::parse(
                                $record->assessmentdate
                            )->format('m/d/Y');

                        $recordShift =
                            ($record->trans_flag ?? 'A') === 'N'
                                ? 'Noon'
                                : 'Morning';

                    @endphp


                    {{-- ================================================
                         PDF Buttons
                    ================================================ --}}

                    <div class="d-flex justify-content-end gap-2 mb-2">


                        {{-- Export PDF --}}

                        <a class="btn btn-primary btn-sm"

                           href="{{ route('paa.pdf', [

                                'participantid' =>
                                    $record->participantid,

                                'assessedyear' =>
                                    $record->assessedyear,

                                'quarter' =>
                                    $record->quarter,

                                'trans_flag' =>
                                    $record->trans_flag
                                        ?? $selectedShift,

                           ]) }}"

                           target="_blank"
                           rel="noopener">

                            Export to PDF

                        </a>


                        {{-- Download PDF --}}

                        <a class="btn btn-outline-dark btn-sm"

                           href="{{ route('paa.pdf', [

                                'participantid' =>
                                    $record->participantid,

                                'assessedyear' =>
                                    $record->assessedyear,

                                'quarter' =>
                                    $record->quarter,

                                'trans_flag' =>
                                    $record->trans_flag
                                        ?? $selectedShift,

                                'dl' => 1,

                           ]) }}"

                           target="_blank"
                           rel="noopener">

                            Download PDF

                        </a>

                    </div>


                    {{-- ================================================
                         Styles
                    ================================================ --}}

                    <style>

                        .section-title {
                            font-weight: 700;
                            color: #7a0c0c;
                            margin-top: 15px;
                        }

                        table.report {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 6px;
                            font-size: 11px;
                        }

                        table.report th,
                        table.report td {
                            border: 1px solid #444;
                            padding: 4px;
                        }

                        table.report th {
                            background: #f5f5f5;
                            text-align: left;
                        }

                        .check {
                            text-align: center;
                            width: 50px;
                            font-size: 14px;
                            font-weight: bold;
                        }

                    </style>


                    {{-- ================================================
                         Report
                    ================================================ --}}

                    <div class="border rounded p-3 bg-white">


                        <h6>

                            Participant ID:
                            {{ $record->participantid }}


                            @if(isset($member) && $member)

                                | {{ $member->fullname }}

                            @endif

                        </h6>


                        <p class="mb-1">

                            Assessment Date:
                            {{ $date }}

                            |

                            Year:
                            {{ $record->assessedyear }}

                            |

                            Quarter:
                            Q{{ $record->quarter }}

                            |

                            Shift:
                            <strong>{{ $recordShift }}</strong>

                        </p>


                        {{-- ============================================
                             Report Section Helper
                        ============================================ --}}

                        @php

                            $render = function(
                                $title,
                                $rows
                            ) use ($mark) {

                                $out =
                                    '<div class="section-title">'
                                    . e($title)
                                    . '</div>';

                                $out .=
                                    '<table class="report">
                                        <tbody>';

                                foreach ($rows as [$label, $val]) {

                                    $out .=
                                        '<tr>';

                                    $out .=
                                        '<td>'
                                        . e($label)
                                        . '</td>';

                                    $out .=
                                        '<td class="check">'
                                        . $mark($val)
                                        . '</td>';

                                    $out .=
                                        '</tr>';
                                }

                                $out .=
                                    '</tbody>
                                    </table>';

                                return $out;
                            };

                        @endphp


                        {{-- ============================================
                             Member's Interaction
                        ============================================ --}}

                        {!! $render(
                            "Member's Interaction",
                            [

                                [
                                    'Interested in individual activities',
                                    $record->Interestedinindividualactivity
                                        ?? null
                                ],

                                [
                                    'Socializes with other members',
                                    $record->Socializeswithothermembers
                                        ?? null
                                ],

                                [
                                    'Socialize in groups',
                                    $record->Socializeingroups
                                        ?? null
                                ],

                            ]
                        ) !!}


                        {{-- ============================================
                             Member's Participation
                        ============================================ --}}

                        {!! $render(
                            "Member's Participation",
                            [

                                [
                                    'Active Participant',
                                    $record->MemberisActiveParticipant
                                        ?? null
                                ],

                                [
                                    'Passive Participant',
                                    $record->MemberisPassiveParticipant
                                        ?? null
                                ],

                                [
                                    'Seldom Participates (Spectator)',
                                    $record->MemberSeldomParticipates
                                        ?? null
                                ],

                            ]
                        ) !!}


                        {{-- ============================================
                             Member's Sitting Preference
                        ============================================ --}}

                        {!! $render(
                            "Member's Sitting Preference",
                            [

                                [
                                    'Individual',
                                    $record->Individual ?? null
                                ],

                                [
                                    'With other member',
                                    $record->WithOtherMember ?? null
                                ],

                                [
                                    'Small Group',
                                    $record->SmallGroup ?? null
                                ],

                                [
                                    'Large Group',
                                    $record->LargeGroup ?? null
                                ],

                            ]
                        ) !!}


                        {{-- ============================================
                             Member's Emotional Status
                        ============================================ --}}

                        {!! $render(
                            "Member's Emotional Status",
                            [

                                [
                                    'Happy, Cheerful',
                                    $record->Happy_Cheerful ?? null
                                ],

                                [
                                    'Depressed, Sad',
                                    $record->Depressed_Sad ?? null
                                ],

                                [
                                    'Content, Satisfied',
                                    $record->Content_Satisfied ?? null
                                ],

                                [
                                    'Worried, Anxious',
                                    $record->Worried_Anxious ?? null
                                ],

                                [
                                    'Angry, Mad',
                                    $record->Angry_Mad ?? null
                                ],

                                [
                                    'Serious',
                                    $record->Serious ?? null
                                ],

                            ]
                        ) !!}


                        {{-- ============================================
                             Member's Cognitive Status
                        ============================================ --}}

                        {!! $render(
                            "Member's Cognitive Status",
                            [

                                [
                                    'Alert, Oriented',
                                    $record->Alert_Oriented ?? null
                                ],

                                [
                                    "Confused (Member's need help)",
                                    $record->Confused_MembersNeedHelp
                                        ?? null
                                ],

                                [
                                    'Forgetful',
                                    $record->Forgetful ?? null
                                ],

                                [
                                    'Cannot Express Needs',
                                    $record->CannotExpressNeeds
                                        ?? null
                                ],

                            ]
                        ) !!}


                        {{-- ============================================
                             Member's Behavioral Status
                        ============================================ --}}

                        {!! $render(
                            "Member's Behavioral Status",
                            [

                                [
                                    'Calm',
                                    $record->Calm ?? null
                                ],

                                [
                                    'Anxious',
                                    $record->Anxious ?? null
                                ],

                                [
                                    'Tearful',
                                    $record->Tearful ?? null
                                ],

                                [
                                    'Disruptive',
                                    $record->Disruptive ?? null
                                ],

                                [
                                    'Agitated',
                                    $record->Agitated ?? null
                                ],

                            ]
                        ) !!}


                        {{-- ============================================
                             Recommended Program
                        ============================================ --}}

                        {!! $render(
                            "Recommended Program",
                            [

                                [
                                    'Empowerment',
                                    $record->Empowerment ?? null
                                ],

                                [
                                    'Maintenance',
                                    $record->Maintenance ?? null
                                ],

                            ]
                        ) !!}


                        {{-- ============================================
                             Therapeutic Programs
                        ============================================ --}}

                        {!! $render(
                            "Therapeutic Programs",
                            [

                                [
                                    'Musical Activities',
                                    $record->MusicalActivities ?? null
                                ],

                                [
                                    'Educational Talks',
                                    $record->EducationalTalks ?? null
                                ],

                                [
                                    'Trips and Group Outings',
                                    $record->TripsAndGroupOutings
                                        ?? null
                                ],

                                [
                                    'Games',
                                    $record->Games ?? null
                                ],

                                [
                                    'ESL Classes',
                                    $record->ESLClasses ?? null
                                ],

                                [
                                    'Exercise and other physical activities',
                                    $record->ExerciseandOtherPhysical
                                        ?? null
                                ],

                                [
                                    'Arts and Crafts',
                                    $record->ArtsandCrafts ?? null
                                ],

                                [
                                    'Spiritual and Religious Activities',
                                    $record->SpiritualandReligiousActivity
                                        ?? null
                                ],

                            ]
                        ) !!}


                        {{-- ============================================
                             Therapeutic Intervention Needs
                        ============================================ --}}

                        {!! $render(
                            "Therapeutic Intervention Needs",
                            [

                                [
                                    'Needs Direct Assistance with Activity',
                                    $record->NeedsDirectAssistanceWith
                                        ?? null
                                ],

                                [
                                    'Provide Step by Step Task Demonstration',
                                    $record->ProvideStepByStepTaskDemo
                                        ?? null
                                ],

                                [
                                    'Structured Seating to Optimize Therapeutic Interventions',
                                    $record->StructuredSeatingToOptimize
                                        ?? null
                                ],

                            ]
                        ) !!}


                        {{-- ============================================
                             Assessed / Prepared By
                        ============================================ --}}

                        <div class="mt-3">

                            <strong>
                                Assessed By:
                            </strong>

                            {{ $record->AssessedBy ?? '' }}

                            <br>


                            <strong>
                                Prepared By:
                            </strong>

                            {{ $record->PreparedBy ?? '' }}

                        </div>


                    </div>


                {{-- ====================================================
                     Record Not Found
                ==================================================== --}}

                @elseif($prefill['participantid'] ?? null)

                    <div class="alert alert-warning">

                        No record found for the selected participant,
                        year, and quarter.

                    </div>

                @endif


            </div>

        </div>

    </div>

</div>

@endsection