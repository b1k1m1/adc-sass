@extends('admin.admin_dashboard') {{-- or your layout --}}
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
                            <h4 class="mb-0 font-size-18">Activity Entry</h4>
                        </div>
                        <!-- RIGHT SIDE TOOLBAR -->
                        <div class="page-title-right">
                            @yield('page-toolbar')
                        </div>
                    </div>
                </div>
             </div>
            <!-- end page title -->

    {{-- Flash messages --}}
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- ONE FORM: Load (GET) and Update (POST) --}}
    <form action="{{ route('paa.form') }}" method="get" class="border rounded p-3 bg-light">
      @csrf {{-- ignored on GET; used by Update button via formmethod="post" --}}

      {{-- ======= Keys row (Participant / Year / Quarter) ======= --}}
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Participant</label>
          <select name="participantid" class="form-select" required>
            <option value="">-- Select --</option>
            @foreach($participants as $p)
              <option value="{{ $p->id }}"
                {{ old('participantid', $prefill['participantid'] ?? '') == $p->id ? 'selected' : '' }}>
                {{ $p->id }} — {{ $p->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Year</label>
          <input type="number" name="assessedyear" class="form-control" min="1900" max="2099"
                 value="{{ old('assessedyear', $prefill['assessedyear'] ?? now()->year) }}" required>
        </div>

        <div class="col-md-2">
          <label class="form-label">Quarter</label>
          <select name="quarter" class="form-select" required>
            @foreach([1,2,3,4] as $q)
              <option value="{{ $q }}"
                {{ (string)$q === (string)old('quarter', $prefill['quarter'] ?? '') ? 'selected' : '' }}>
                Q{{ $q }}
              </option>
            @endforeach
          </select>
        </div>

       <div class="d-flex gap-2 mt-4">
        {{-- Load (GET) --}}
        <button type="submit" class="btn btn-secondary"
                formaction="{{ route('paa.form') }}" formmethod="get">
            Load
        </button>

        @if(isset($assessment) && $assessment)
            {{-- Update (POST) when row exists --}}
            <button type="submit" class="btn btn-primary"
                    formaction="{{ route('paa.update') }}" formmethod="post">
            Update
            </button>
        @else
            {{-- Insert (POST) when no row exists --}}
            <button type="submit" class="btn btn-success"
                    formaction="{{ route('paa.insert') }}" formmethod="post">
            Insert
            </button>
        @endif
        </div>

      </div>

      {{-- ======= Assessment Date (MM/DD/YYYY) ======= --}}
      @php
        $displayDate = old('assessmentdate');
        if(!$displayDate && isset($assessment?->assessmentdate)) {
            $displayDate = \Illuminate\Support\Carbon::parse($assessment->assessmentdate)->format('m/d/Y');
        } elseif(!$displayDate) {
            $displayDate = now()->format('m/d/Y');
        }
      @endphp

      <div class="row mt-3">
        <div class="col-md-3">
          <label class="form-label">Assessment Date</label>
          <input type="text" name="assessmentdate" class="form-control" value="{{ $displayDate }}" required>
        </div>
      </div>

      {{-- ======= Styles for sections ======= --}}
      <style>
        .section-title { font-weight: 700; color:#7a0c0c }
        .boxed { border:1px solid #e5e7eb; border-radius:10px; padding:16px; background:#fff; margin-top:18px }
        .grid-2 { display:grid; grid-template-columns: 1fr 1fr; gap:10px }
      </style>

      {{-- ======= Checkbox helper (works with stdClass) ======= --}}
      @php
      function yn($name, $label, $row) {
          $val = old($name, $row?->{$name} ?? null);
          $isChecked = $val === 'Y' ? 'checked' : '';
          return <<<HTML
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="$name" name="$name" value="1" $isChecked>
              <label class="form-check-label" for="$name">$label</label>
            </div>
          HTML;
      }
      @endphp

      {{-- ======= Sections (match your screenshots/columns) ======= --}}

      {{-- Member's Interaction --}}
      <div class="boxed">
        <div class="section-title mb-2">Member's Interaction</div>
        <div class="grid-2">
          {!! yn('Interestedinindividualactivity','Interested in individual activities', $assessment ?? null) !!}
          {!! yn('Socializeswithothermembers','Socializes with other members', $assessment ?? null) !!}
          {!! yn('Socializeingroups','Socialize in groups', $assessment ?? null) !!}
        </div>
      </div>

      {{-- Member's Participation --}}
      <div class="boxed">
        <div class="section-title mb-2">Member's Participation</div>
        <div class="grid-2">
          {!! yn('MemberisActiveParticipant','Member is Active Participant', $assessment ?? null) !!}
          {!! yn('MemberisPassiveParticipant','Member is Passive Participant', $assessment ?? null) !!}
          {!! yn('MemberSeldomParticipates','Member Seldom Participates (Spectator)', $assessment ?? null) !!}
        </div>
      </div>

      {{-- Member's Sitting Preference --}}
      <div class="boxed">
        <div class="section-title mb-2">Member's Sitting Preference</div>
        <div class="grid-2">
          {!! yn('Individual','Individual', $assessment ?? null) !!}
          {!! yn('WithOtherMember','With other member', $assessment ?? null) !!}
          {!! yn('SmallGroup','Small Group', $assessment ?? null) !!}
          {!! yn('LargeGroup','Large Group', $assessment ?? null) !!}
        </div>
      </div>

      {{-- Member's Emotional Status --}}
      <div class="boxed">
        <div class="section-title mb-2">Member's Emotional Status</div>
        <div class="grid-2">
          {!! yn('Happy_Cheerful','Happy, Cheerful', $assessment ?? null) !!}
          {!! yn('Depressed_Sad','Depressed, Sad', $assessment ?? null) !!}
          {!! yn('Content_Satisfied','Content, Satisfied', $assessment ?? null) !!}
          {!! yn('Worried_Anxious','Worried, Anxious', $assessment ?? null) !!}
          {!! yn('Angry_Mad','Angry, Mad', $assessment ?? null) !!}
          {!! yn('Serious','Serious', $assessment ?? null) !!}
        </div>
      </div>

      {{-- Member's Cognitive Status --}}
      <div class="boxed">
        <div class="section-title mb-2">Member's Cognitive Status</div>
        <div class="grid-2">
          {!! yn('Alert_Oriented','Alert, Oriented', $assessment ?? null) !!}
          {!! yn('Confused_MembersNeedHelp',"Confused (Member's need help)", $assessment ?? null) !!}
          {!! yn('Forgetful','Forgetful', $assessment ?? null) !!}
          {!! yn('CannotExpressNeeds','Cannot Express Needs', $assessment ?? null) !!}
        </div>
      </div>

      {{-- Member's Behavioral Status --}}
      <div class="boxed">
        <div class="section-title mb-2">Member's Behavioral Status</div>
        <div class="grid-2">
          {!! yn('Calm','Calm', $assessment ?? null) !!}
          {!! yn('Anxious','Anxious', $assessment ?? null) !!}
          {!! yn('Tearful','Tearful', $assessment ?? null) !!}
          {!! yn('Disruptive','Disruptive', $assessment ?? null) !!}
          {!! yn('Agitated','Agitated', $assessment ?? null) !!}
        </div>
      </div>

      {{-- Recommended Program --}}
      <div class="boxed">
        <div class="section-title mb-2">Recommended Program</div>
        <div class="grid-2">
          {!! yn('Empowerment','Empowerment', $assessment ?? null) !!}
          {!! yn('Maintenance','Maintenance', $assessment ?? null) !!}
        </div>
      </div>

      {{-- Therapeutic Programs --}}
      <div class="boxed">
        <div class="section-title mb-2">Therapeutic Programs</div>
        <div class="grid-2">
          {!! yn('MusicalActivities','Musical Activities', $assessment ?? null) !!}
          {!! yn('EducationalTalks','Educational Talks', $assessment ?? null) !!}
          {!! yn('TripsAndGroupOutings','Trips and Group Outings', $assessment ?? null) !!}
          {!! yn('Games','Games', $assessment ?? null) !!}
          {!! yn('ESLClasses','ESL Classes', $assessment ?? null) !!}
          {!! yn('ExerciseandOtherPhysical','Exercise and other physical activities', $assessment ?? null) !!}
          {!! yn('ArtsandCrafts','Arts and Crafts', $assessment ?? null) !!}
          {!! yn('SpiritualandReligiousActivity','Spiritual and Religious Activities', $assessment ?? null) !!}
        </div>
      </div>

      {{-- Therapeutic Intervention Needs --}}
      <div class="boxed">
        <div class="section-title mb-2">Therapeutic Intervention Needs</div>
        <div class="grid-2">
          {!! yn('NeedsDirectAssistanceWith','Needs Direct Assistance with Activity', $assessment ?? null) !!}
          {!! yn('ProvideStepByStepTaskDemo','Provide Step by Step Task Demonstration', $assessment ?? null) !!}
          {!! yn('StructuredSeatingToOptimize','Structured Seating to Optimize Therapeutic Interventions', $assessment ?? null) !!}
        </div>
      </div>

      {{-- Assessed / Prepared By --}}
      <div class="row mt-3">
        <div class="col-md-4">
          <label class="form-label">Assessed By</label>
          <input type="text" name="AssessedBy" class="form-control"
                 value="{{ old('AssessedBy', $assessment->AssessedBy ?? '') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Prepared By</label>
          <input type="text" name="PreparedBy" class="form-control"
                 value="{{ old('PreparedBy', $assessment->PreparedBy ?? '') }}">
        </div>
      </div>

      {{-- Bottom action row mirrors top buttons for convenience (optional) --}}

      <div class="d-flex gap-2 mt-4">
            {{-- Load (GET) --}}
            <button type="submit" class="btn btn-secondary"
                    formaction="{{ route('paa.form') }}" formmethod="get">
                Load
            </button>

            @if(isset($assessment) && $assessment)
                {{-- Update (POST) when row exists --}}
                <button type="submit" class="btn btn-primary"
                        formaction="{{ route('paa.update') }}" formmethod="post">
                Update
                </button>
            @else
                {{-- Insert (POST) when no row exists --}}
                <button type="submit" class="btn btn-success"
                        formaction="{{ route('paa.insert') }}" formmethod="post">
                Insert
                </button>
            @endif
       </div>

    </form>

  </div>
</div>
@endsection
