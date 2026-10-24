@extends('admin.admin_dashboard')
@section('title', 'Medication Review Report')
@section('admin')

<div class="page-content">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Medication Review Report</h4>

        <div class="d-flex gap-2">


            {{-- SHIFT BUTTONS (same look as Pharmacist Review) --}}
            <a href="{{ route('medicine_assessment.index', ['type' => 'A', 'participant_id' => $selectedParticipantId]) }}"
            class="btn {{ $type === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
                Morning Shift
            </a>

            <a href="{{ route('medicine_assessment.index', ['type' => 'N', 'participant_id' => $selectedParticipantId]) }}"
            class="btn {{ $type === 'N' ? 'btn-secondary' : 'btn-outline-secondary' }}"> Noon Shift </a>

            <a href="{{ route('medicine_assessment.create', ['type' => $type, 'participant_id' => $selectedParticipantId]) }}"
                id="btnNewAssessment"
                class="btn btn-success {{ empty($selectedParticipantId) ? 'disabled' : '' }}"
                aria-disabled="{{ empty($selectedParticipantId) ? 'true' : 'false' }}">
                    + New Assessment
            </a>

            <a href="{{ route('social.dashboard') }}" class="btn btn-primary">{{ t('common.back','Back') }}</a>
        </div>

    </div>

    @if(session('message'))
    <div class="alert alert-success alert-sm">{{ session('message') }}</div>
    @endif

    {{-- Filters --}}
    <form method="get" class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">

            {{-- FILTER CARD (Member dropdown + Load/Reset) --}}

            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label mb-1">Select Member</label>
                    <select name="participant_id" id="participant_id" class="form-select">
                        <option value="">-- All Members --</option>
                        @foreach($members as $m)
                            <option value="{{ $m->ParticipantId }}"
                                {{ (string)$selectedParticipantId === (string)$m->ParticipantId ? 'selected' : '' }}>
                                {{ $m->ParticipantId }} - {{ $m->LastName }}, {{ $m->FirstName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Load</button>
                </div>

                <div class="col-md-3">
                    <a href="{{ route('medicine_assessment.index', ['type' => $type]) }}"
                    class="btn btn-outline-secondary w-100">
                        Reset
                    </a>
                </div>
            </div>
    </div>


        </div>
    </div>
    </form>

    {{-- List --}}
    <div class="card">
    <div class="card-body table-responsive">
        <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
            <th style="width:70px;">ID</th>
            <th style="width:120px;">Date</th>
            <th>Member</th>
            <th style="width:180px;">Nurse</th>
            <th style="width:120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assessments as $a)
            @php
                $m = $members->firstWhere('ParticipantId', $a->participant_id);
                $memberLabel = $a->participant_id;
                if($m){
                $memberLabel = $m->ParticipantId . ' - ' . $m->LastName . ', ' . $m->FirstName;
                }
            @endphp
            <tr>
                <td class="text-center">{{ $a->id }}</td>
                <td>{{ !empty($a->assessment_date) ? \Carbon\Carbon::parse($a->assessment_date)->format('m/d/Y') : '' }}</td>
                <td>{{ $memberLabel }}</td>
                <td>{{ $a->nurse_name }}</td>
                <td class="d-flex gap-1">
                <a class="btn btn-outline-primary"
                    href="{{ route('medicine_assessment.show', ['id'=>$a->id, 'type'=>$type]) }}">View</a>
                <a class="btn btn-outline-secondary"
                    href="{{ route('medicine_assessment.edit', ['id'=>$a->id, 'type'=>$type]) }}">Edit</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-3">No assessments found.</td>
            </tr>
            @endforelse
        </tbody>
        </table>

        <div class="mt-2">
        {{ $assessments->links() }}
        </div>
    </div>
    </div>

  </div>
</div>
@endsection

@section('scripts')

<script>
(function(){
  const member = document.getElementById('participant_id');
  const btnNew = document.getElementById('btnNew');


  function toggleNew(){
    // If you want strict behavior: disable New unless member selected.
    // Uncomment below.

    // if(btnNew){
    //   btnNew.classList.toggle('disabled', !member.value);
    //   btnNew.setAttribute('aria-disabled', !member.value ? 'true' : 'false');
    //   btnNew.addEventListener('click', (e)=>{ if(!member.value) e.preventDefault(); });
    // }
  }

  if(member){ member.addEventListener('change', toggleNew); }
  toggleNew();
})();

(function () {
    const memberSelect = document.getElementById('participant_id');
    const btnNew = document.getElementById('btnNewAssessment');

    if (!memberSelect || !btnNew) return;

    function toggleButton() {
        const hasMember = memberSelect.value !== '';

        btnNew.classList.toggle('disabled', !hasMember);
        btnNew.setAttribute('aria-disabled', hasMember ? 'false' : 'true');

        if (!hasMember) {
            btnNew.addEventListener('click', preventClick);
        } else {
            btnNew.removeEventListener('click', preventClick);
        }
    }

    function preventClick(e) {
        e.preventDefault();
    }

    memberSelect.addEventListener('change', toggleButton);
    toggleButton(); // run on load
})();
</script>

@endsection
