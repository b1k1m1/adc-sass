@extends('admin.admin_dashboard')
@section('title', 'Medication Maintenance')
@section('admin')
<div class="page-content">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Medication Maintenance (Nurse)</h4>

        <div class="d-flex gap-2">
            <a href="{{ route('medicine_maint.index', ['type' => 'A']) }}"
               class="btn {{ $type==='A' ? 'btn-primary' : 'btn-outline-primary' }}">Morning (A)</a>
            <a href="{{ route('medicine_maint.index', ['type' => 'N']) }}"
               class="btn {{ $type==='N' ? 'btn-secondary' : 'btn-outline-secondary' }}">Noon (N)</a>
        </div>
    </div>

    @if(session('message'))
      <div class="alert alert-success alert-sm">{{ session('message') }}</div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger alert-sm">
        <ul class="mb-0">
          @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="get" class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Select Member</strong>
        <span class="small text-muted">Shift: {{ $type==='A' ? 'Morning (A)' : 'Noon (N)' }}</span>
      </div>
      <div class="card-body">
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="row g-2 align-items-end">
          <div class="col-md-6">
            <label class="form-label mb-1">Member</label>
            <select name="participant_id" class="form-select" required>
              <option value="">-- Select Member --</option>
              @foreach($members as $m)
                <option value="{{ $m->ParticipantId }}" {{ (string)$selectedParticipantId===(string)$m->ParticipantId ? 'selected' : '' }}>
                  {{ $m->ParticipantId }} - {{ $m->LastName }}, {{ $m->FirstName }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <button class="btn btn-primary">Load</button>
          </div>
        </div>
      </div>
    </form>

    @if(!empty($selectedParticipantId))
      @php
        // options once for template
        $physOptions = '<option value="">-- Select --</option>';
        foreach($physicians as $p){
          $physOptions .= '<option value="'.e($p->doctor_id).'">'.e($p->lastname.', '.$p->firstname).'</option>';
        }

        $dosageOptions = '<option value="">-- Select --</option><option value="__OTHER__">Other...</option>';
        foreach($dosages as $d){
          $dosageOptions .= '<option value="'.e($d->dosage_id).'">'.e($d->dosage_nm).'</option>';
        }

        $freqOptions = '<option value="">-- Select --</option><option value="__OTHER__">Other...</option>';
        foreach($freqs as $f){
          $freqOptions .= '<option value="'.e($f->dosagefreq_id).'">'.e($f->dosagefreq_nm).'</option>';
        }

        $rowsForGrid = $rows;
        if($rowsForGrid->count()===0){
          $rowsForGrid = collect([(object)[
            'medicine_nm'=>'','doctor_id'=>'','dosage_nm'=>'','dosage_freq'=>'','comments'=>'','status'=>'active','status_date'=>null
          ]]);
        }
      @endphp

      <form method="post" action="{{ route('medicine_maint.store') }}">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="participant_id" value="{{ $selectedParticipantId }}">

        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Medication List</strong>
            <button type="button" id="addRowBtn" class="btn btn-outline-primary">+ Add Row</button>
          </div>

          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm table-bordered align-middle" id="medTable">
                <thead class="table-light">
                  <tr>
                    <th style="width:55px;">#</th>
                    <th style="width:220px;">Medicine</th>
                    <th style="width:210px;">Physician</th>
                    <th style="width:210px;">Dosage</th>
                    <th style="width:210px;">Frequency</th>
                    <th style="width:220px;">Comments</th>
                    <th style="width:110px;">Status</th>
                    <th style="width:130px;">Status Date</th>
                    <th style="width:70px;"></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($rowsForGrid as $i => $r)
                    @php
                      $med = old("details.$i.medicine_nm", $r->medicine_nm ?? '');
                      $doc = old("details.$i.doctor_id", $r->doctor_id ?? '');

                      // store text in "other" fields so it never wipes
                      $dosOther = old("details.$i.dosage_other", $r->dosage_nm ?? '');
                      $frqOther = old("details.$i.freq_other", $r->dosage_freq ?? '');

                      $cmt = old("details.$i.comments", $r->comments ?? '');
                      $st  = old("details.$i.status", $r->status ?? 'active');
                      $sd  = old("details.$i.status_date", $r->status_date ?? '');
                    @endphp

                    <tr>
                      <td class="text-center"><span class="lineNo">{{ $i+1 }}</span></td>

                      <td><input class="form-control form-control-sm" name="details[{{ $i }}][medicine_nm]" value="{{ $med }}" maxlength="150"></td>

                      <td>
                        <select class="form-select form-select-sm" name="details[{{ $i }}][doctor_id]">
                          {!! str_replace("value=\"{$doc}\"", "value=\"{$doc}\" selected", $physOptions) !!}
                        </select>
                      </td>

                      <td>
                        <select class="form-select form-select-sm dosageSelect" name="details[{{ $i }}][dosage_id]">
                          {!! $dosageOptions !!}
                        </select>
                        <input class="form-control form-control-sm mt-1 dosageOther"
                               name="details[{{ $i }}][dosage_other]" value="{{ $dosOther }}"
                               maxlength="50" placeholder="Type dosage if not in list">
                      </td>

                      <td>
                        <select class="form-select form-select-sm freqSelect" name="details[{{ $i }}][dosagefreq_id]">
                          {!! $freqOptions !!}
                        </select>
                        <input class="form-control form-control-sm mt-1 freqOther"
                               name="details[{{ $i }}][freq_other]" value="{{ $frqOther }}"
                               maxlength="50" placeholder="Type frequency if not in list">
                      </td>

                      <td><input class="form-control form-control-sm" name="details[{{ $i }}][comments]" value="{{ $cmt }}" maxlength="150"></td>

                      <td>
                        <select class="form-select form-select-sm" name="details[{{ $i }}][status]">
                          @foreach(['active'=>'Active','stopped'=>'Stopped','discontinued'=>'Discontinued'] as $k=>$v)
                            <option value="{{ $k }}" {{ $st===$k ? 'selected' : '' }}>{{ $v }}</option>
                          @endforeach
                        </select>
                      </td>

                      <td><input type="date" class="form-control form-control-sm" name="details[{{ $i }}][status_date]" value="{{ $sd }}"></td>

                      <td class="text-center">
                        <button type="button" class="btn btn-outline-danger removeRowBtn">X</button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
              <button class="btn btn-primary">Save</button>
            </div>
          </div>
        </div>

        <template id="rowTemplate">
          <tr>
            <td class="text-center"><span class="lineNo">1</span></td>
            <td><input class="form-control form-control-sm" name="__NAME__[medicine_nm]" maxlength="150"></td>
            <td>
              <select class="form-select form-select-sm" name="__NAME__[doctor_id]">{!! $physOptions !!}</select>
            </td>
            <td>
              <select class="form-select form-select-sm dosageSelect" name="__NAME__[dosage_id]">{!! $dosageOptions !!}</select>
              <input class="form-control form-control-sm mt-1 dosageOther" name="__NAME__[dosage_other]" maxlength="50" placeholder="Type dosage if not in list">
            </td>
            <td>
              <select class="form-select form-select-sm freqSelect" name="__NAME__[dosagefreq_id]">{!! $freqOptions !!}</select>
              <input class="form-control form-control-sm mt-1 freqOther" name="__NAME__[freq_other]" maxlength="50" placeholder="Type frequency if not in list">
            </td>
            <td><input class="form-control form-control-sm" name="__NAME__[comments]" maxlength="150"></td>
            <td>
              <select class="form-select form-select-sm" name="__NAME__[status]">
                <option value="active">Active</option>
                <option value="stopped">Stopped</option>
                <option value="discontinued">Discontinued</option>
              </select>
            </td>
            <td><input type="date" class="form-control form-control-sm" name="__NAME__[status_date]"></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger removeRowBtn">X</button></td>
          </tr>
        </template>

      </form>

      @endsection

      @section('scripts')
      
      <script>
      (function(){
        const tbl = document.getElementById('medTable');
        const tbody = tbl ? tbl.querySelector('tbody') : null;
        const tpl = document.getElementById('rowTemplate');
        const addBtn = document.getElementById('addRowBtn');

        function renumber(){
          if(!tbody) return;
          [...tbody.querySelectorAll('tr')].forEach((tr, idx) => {
            const ln = tr.querySelector('.lineNo');
            if(ln) ln.textContent = (idx + 1);
          });
        }

        function nextIndex(){
          if(!tbody) return 0;
          return tbody.querySelectorAll('tr').length;
        }

        function initOtherFields(){
          if(!tbody) return;
          tbody.querySelectorAll('tr').forEach(tr => {
            const dOther = tr.querySelector('.dosageOther');
            const fOther = tr.querySelector('.freqOther');

            if(dOther){
              if(dOther.value.trim() !== '') dOther.removeAttribute('disabled');
              else dOther.setAttribute('disabled','disabled');
            }
            if(fOther){
              if(fOther.value.trim() !== '') fOther.removeAttribute('disabled');
              else fOther.setAttribute('disabled','disabled');
            }
          });
        }

        function toggleOther(selectEl){
          const tr = selectEl.closest('tr');
          if(!tr) return;

          if(selectEl.classList.contains('dosageSelect')){
            const other = tr.querySelector('.dosageOther');
            if(!other) return;
            if(selectEl.value === '__OTHER__'){
              other.removeAttribute('disabled');
              other.focus();
            } else {
              other.value = '';
              other.setAttribute('disabled','disabled');
            }
          }

          if(selectEl.classList.contains('freqSelect')){
            const other = tr.querySelector('.freqOther');
            if(!other) return;
            if(selectEl.value === '__OTHER__'){
              other.removeAttribute('disabled');
              other.focus();
            } else {
              other.value = '';
              other.setAttribute('disabled','disabled');
            }
          }
        }

        if(addBtn && tbody && tpl){
          addBtn.addEventListener('click', function(){
            const idx = nextIndex();
            const clone = tpl.content.cloneNode(true);
            const tr = clone.querySelector('tr');
            tr.querySelectorAll('input, select').forEach(el => {
              el.name = el.name.replace('__NAME__', `details[${idx}]`);
            });
            tbody.appendChild(tr);
            renumber();
            initOtherFields();
          });
        }

        document.addEventListener('click', function(e){
          if(e.target && e.target.classList.contains('removeRowBtn')){
            const tr = e.target.closest('tr');
            if(tr && tbody){
              tr.remove();
              renumber();
            }
          }
        });

        document.addEventListener('change', function(e){
          if(e.target && (e.target.classList.contains('dosageSelect') || e.target.classList.contains('freqSelect'))){
            toggleOther(e.target);
          }
        });

        renumber();
        initOtherFields();
      })();
      </script>
    @endif

  </div>
</div>
@endsection
