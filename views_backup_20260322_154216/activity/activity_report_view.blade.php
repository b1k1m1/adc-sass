@extends('admin.admin_dashboard')
@section('admin')
@section('page-toolbar')
<a href="{{ url()->previous() }}" class="btn btn-primary waves-effect waves-light">
    {{ t('common.back','Back') }}
</a>
@endsection

<div class="page-content">
  <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <!-- LEFT SIDE -->
                        <div>
                            <h4 class="mb-0 font-size-18">Participant Activity Assessment Report</h4>
                        </div>
                        <!-- RIGHT SIDE TOOLBAR -->
                        <div class="page-title-right">
                            @yield('page-toolbar')
                        </div>
                    </div>
                </div>
             </div>
            <!-- end page title -->


    <div class="card shadow-sm">
      <div class="card-body">

        {{-- Filter form --}}
        <form method="get" action="{{ route('paa.report.view') }}" class="border rounded p-3 mb-4 bg-light">
          <div class="row g-2 align-items-end">
            <div class="col-md-4">
              <label class="form-label">Participant</label>
              <select name="participantid" class="form-select" required>
                <option value="">-- Select --</option>
                @foreach($participants as $p)
                  <option value="{{ $p->id }}"
                    {{ ($prefill['participantid'] ?? '') == $p->id ? 'selected' : '' }}>
                    {{ $p->id }} — {{ $p->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Assessed Year</label>
              <input type="number" name="assessedyear" class="form-control"
                     value="{{ $prefill['assessedyear'] ?? now()->year }}" required>
            </div>

            <div class="col-md-2">
              <label class="form-label">Quarter</label>
              <select name="quarter" class="form-select" required>
                <option value="">-- Q --</option>
                @foreach([1,2,3,4] as $q)
                  <option value="{{ $q }}"
                    {{ (string)$q === (string)($prefill['quarter'] ?? '') ? 'selected' : '' }}>
                    Q{{ $q }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
              <button class="btn btn-dark mt-3" type="submit">Show Report</button>
              <a href="{{ route('paa.report.view') }}" class="btn btn-outline-secondary mt-3">Clear</a>
            </div>
          </div>
        </form>

        {{-- Report output --}}
        @if($record)
          @php
            $mark = fn($v) => $v === 'Y' ? '✓' : '';
            $date = \Illuminate\Support\Carbon::parse($record->assessmentdate)->format('m/d/Y');
          @endphp

          <div class="d-flex justify-content-end gap-2 mb-2">
            {{-- Export to PDF (inline, opens new tab) --}}
            <a class="btn btn-primary btn-sm"
               href="{{ route('paa.pdf', ['participantid' => $record->participantid, 'assessedyear' => $record->assessedyear, 'quarter' => $record->quarter]) }}"
               target="_blank" rel="noopener">
              Export to PDF
            </a>

            {{-- Optional: force download version (requires tiny controller tweak below) --}}
            <a class="btn btn-outline-dark btn-sm"
               href="{{ route('paa.pdf', ['participantid' => $record->participantid, 'assessedyear' => $record->assessedyear, 'quarter' => $record->quarter, 'dl' => 1]) }}"
               target="_blank" rel="noopener">
              Download PDF
            </a>
          </div>

          <style>
            .section-title { font-weight:700; color:#7a0c0c; margin-top:15px; }
            table.report { width:100%; border-collapse:collapse; margin-top:6px; font-size:11px; }
            table.report th, table.report td { border:1px solid #444; padding:4px; }
            table.report th { background:#f5f5f5; text-align:left; }


            .check { text-align:center; width:50px; }
          </style>

          <div class="border rounded p-3 bg-white">
            <h6>
                Participant ID: {{ $record->participantid }}
                 @if(isset($member))
                    | {{ $member->fullname }}
                @endif
            </h6>

            <p>Assessment Date: {{ $date }} | Year: {{ $record->assessedyear }} | Quarter: Q{{ $record->quarter }}</p>

            @php
              $render = function($title, $rows) use ($mark) {
                $out = '<div class="section-title">'.$title.'</div>';
                $out .= '<table class="report"><tbody>';
                foreach ($rows as [$label, $val]) {
                  $out .= '<tr><td>'.e($label).'</td><td class="check">'.$mark($val).'</td></tr>';
                }
                $out .= '</tbody></table>';
                return $out;
              };
            @endphp

            {!! $render("Member's Interaction", [
              ['Interested in individual activities', $record->Interestedinindividualactivity ?? null],
              ['Socializes with other members', $record->Socializeswithothermembers ?? null],
              ['Socialize in groups', $record->Socializeingroups ?? null],
            ]) !!}

            {!! $render("Member's Participation", [
              ['Active Participant', $record->MemberisActiveParticipant ?? null],
              ['Passive Participant', $record->MemberisPassiveParticipant ?? null],
              ['Seldom Participates (Spectator)', $record->MemberSeldomParticipates ?? null],
            ]) !!}

            {!! $render("Member's Sitting Preference", [
              ['Individual', $record->Individual ?? null],
              ['With other member', $record->WithOtherMember ?? null],
              ['Small Group', $record->SmallGroup ?? null],
              ['Large Group', $record->LargeGroup ?? null],
            ]) !!}

            {!! $render("Member's Emotional Status", [
              ['Happy, Cheerful', $record->Happy_Cheerful ?? null],
              ['Depressed, Sad', $record->Depressed_Sad ?? null],
              ['Content, Satisfied', $record->Content_Satisfied ?? null],
              ['Worried, Anxious', $record->Worried_Anxious ?? null],
              ['Angry, Mad', $record->Angry_Mad ?? null],
              ['Serious', $record->Serious ?? null],
            ]) !!}

            {!! $render("Member's Cognitive Status", [
              ['Alert, Oriented', $record->Alert_Oriented ?? null],
              ["Confused (Member's need help)", $record->Confused_MembersNeedHelp ?? null],
              ['Forgetful', $record->Forgetful ?? null],
              ['Cannot Express Needs', $record->CannotExpressNeeds ?? null],
            ]) !!}

            {!! $render("Member's Behavioral Status", [
              ['Calm', $record->Calm ?? null],
              ['Anxious', $record->Anxious ?? null],
              ['Tearful', $record->Tearful ?? null],
              ['Disruptive', $record->Disruptive ?? null],
              ['Agitated', $record->Agitated ?? null],
            ]) !!}

            {!! $render("Recommended Program", [
              ['Empowerment', $record->Empowerment ?? null],
              ['Maintenance', $record->Maintenance ?? null],
            ]) !!}

            {!! $render("Therapeutic Programs", [
              ['Musical Activities', $record->MusicalActivities ?? null],
              ['Educational Talks', $record->EducationalTalks ?? null],
              ['Trips and Group Outings', $record->TripsAndGroupOutings ?? null],
              ['Games', $record->Games ?? null],
              ['ESL Classes', $record->ESLClasses ?? null],
              ['Exercise and other physical activities', $record->ExerciseandOtherPhysical ?? null],
              ['Arts and Crafts', $record->ArtsandCrafts ?? null],
              ['Spiritual and Religious Activities', $record->SpiritualandReligiousActivity ?? null],
            ]) !!}

            {!! $render("Therapeutic Intervention Needs", [
              ['Needs Direct Assistance with Activity', $record->NeedsDirectAssistanceWith ?? null],
              ['Provide Step by Step Task Demonstration', $record->ProvideStepByStepTaskDemo ?? null],
              ['Structured Seating to Optimize Therapeutic Interventions', $record->StructuredSeatingToOptimize ?? null],
            ]) !!}

            <div class="mt-3">
              <strong>Assessed By:</strong> {{ $record->AssessedBy ?? '' }}<br>
              <strong>Prepared By:</strong> {{ $record->PreparedBy ?? '' }}
            </div>
          </div>

        @elseif($prefill['participantid'] ?? null)
          <div class="alert alert-warning">No record found for the selected participant, year, and quarter.</div>
        @endif

      </div>
    </div>

  </div>
</div>
@endsection
