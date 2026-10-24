@extends('admin.admin_dashboard')
@section('title', 'Pharmacist Review')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                {{ $review->id ? 'Edit Pharmacist Review' : 'Add Pharmacist Review' }}
            </h4>

            <a href="{{ route('pharmacist_review.index', ['type' => $type, 'participant_id' => $review->participant_id]) }}"
            class="btn btn-secondary">
                &laquo; Back
            </a>
        </div>

        @if(session('message'))
            <div class="alert alert-success alert-sm">{{ session('message') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-sm">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $isEdit = !empty($review->id);
            $action = $isEdit
                ? route('pharmacist_review.update', ['id' => $review->id, 'type' => $type])
                : route('pharmacist_review.store', ['type' => $type]);
        @endphp

        <form method="post" action="{{ $action }}">
            @csrf

            {{-- keep shift type --}}
            <input type="hidden" name="type" value="{{ $type }}">

            {{-- HEADER --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Header</strong>
                    <span class="small text-muted">Shift: {{ $type === 'A' ? 'Morning (A)' : 'Noon (N)' }}</span>
                </div>

                <div class="card-body">
                    <div class="row g-2">

                        <div class="col-md-4">
                            <label class="form-label mb-1">Member</label>
                            <select name="participant_id" id="participant_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Member --</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->ParticipantId }}"
                                        {{ (string)old('participant_id', $review->participant_id) === (string)$m->ParticipantId ? 'selected' : '' }}>
                                        {{ $m->ParticipantId }} - {{ $m->LastName }}, {{ $m->FirstName }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Tip: select member first. (This screen is filtered by shift.)
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label mb-1">Medical Record No</label>
                            <input type="text" name="medical_record_no"
                                value="{{ old('medical_record_no', $review->medical_record_no) }}"
                                class="form-control form-control-sm" maxlength="50">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label mb-1">Review Date</label>
                            <input type="date" name="review_date"
                                value="{{ old('review_date', $review->review_date) }}"
                                class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label mb-1">Period Type</label>
                            <select name="period_type" class="form-select form-select-sm" required>
                                <option value="Q" {{ old('period_type', $review->period_type) === 'Q' ? 'selected' : '' }}>Quarterly</option>
                                <option value="M" {{ old('period_type', $review->period_type) === 'M' ? 'selected' : '' }}>Monthly</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="draft" {{ old('status', $review->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="completed" {{ old('status', $review->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>

                    </div>

                    {{-- Member Header Snapshot (basic now) --}}
                    <div class="mt-3 p-2 border rounded bg-light">
                        <div class="row g-2 small">
                            <div class="col-md-4">
                                <strong>Member:</strong>
                                <span id="member_label">
                                    @php
                                        $lbl = '';
                                        $pid = old('participant_id', $review->participant_id);
                                        foreach($members as $mm){
                                            if((string)$mm->ParticipantId === (string)$pid){
                                                $lbl = $mm->ParticipantId.' - '.$mm->LastName.', '.$mm->FirstName;
                                                break;
                                            }
                                        }
                                    @endphp
                                    {{ $lbl }}
                                </span>
                            </div>
                            <div class="col-md-8 text-muted">
                                (When you want DOB/Admission/Vitals in this header, we’ll bind them from `members` exactly like your legacy screen.)
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- TABS --}}
            <div class="card mb-3">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-comments"
                                    data-bs-toggle="tab" data-bs-target="#pane-comments"
                                    type="button" role="tab">
                                Pharmacist Comments
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-meds"
                                    data-bs-toggle="tab" data-bs-target="#pane-meds"
                                    type="button" role="tab">
                                Medications
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body tab-content">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div id="lastReviewInfo" class="small text-muted"></div>

                        <button type="button"
                                id="copyLastReviewBtn"
                                class="btn btn-sm btn-outline-primary d-none">
                            📋 Copy Last Review
                        </button>
                    </div>

                    {{-- TAB 1: COMMENTS --}}
                    <div class="tab-pane fade show active" id="pane-comments" role="tabpanel">
                        <label class="form-label mb-1">Comments</label>
                        <textarea name="comments" rows="10" class="form-control">{{ old('comments', $review->comments) }}</textarea>
                    </div>

                    <div class="tab-pane fade" id="pane-meds" role="tabpanel">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Medication List (Nurse Maintained)</strong>

                            @if(!empty($review->participant_id))
                                <a href="{{ route('medicine_maint.index', ['type' => $type, 'participant_id' => $review->participant_id]) }}"
                                class="btn btn-outline-primary btn-sm" target="_blank">
                                    Open Medication Maintenance
                                </a>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px;">#</th>
                                        <th>Medicine</th>
                                        <th style="width:220px;">Physician</th>
                                        <th style="width:130px;">Dosage</th>
                                        <th style="width:130px;">Frequency</th>
                                        <th style="width:220px;">Comments</th>
                                        <th style="width:110px;">Status</th>
                                        <th style="width:120px;">Status Date</th>
                                    </tr>
                                </thead>
                                <tbody id="nurseMedsBody">
                                    @forelse($nurseMeds as $i => $m)
                                        <tr>
                                            <td class="text-center">{{ $i+1 }}</td>
                                            <td>{{ $m->medicine_nm }}</td>
                                            <td>{{ $m->physician_name ?? $m->doctor_id }}</td>
                                            <td>{{ $m->dosage_nm }}</td>
                                            <td>{{ $m->dosage_freq }}</td>
                                            <td>{{ $m->comments }}</td>
                                            <td>{{ ucfirst($m->status ?? 'active') }}</td>
                                            <td>
                                                {{ !empty($m->status_date) ? \Carbon\Carbon::parse($m->status_date)->format('m/d/Y') : '' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-3">
                                                No medication list found for this member. (Nurse can add it in Medication Maintenance.)
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="small text-muted mt-2">
                            Note: This list is maintained by nursing staff. Pharmacist cannot edit medications here.
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('pharmacist_review.index', ['type' => $type, 'participant_id' => $review->participant_id]) }}"
                class="btn btn-outline-secondary">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    Save
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@section('scripts')
        <script>
        (function(){
            const tbl = document.getElementById('medTable');
            const tbody = tbl ? tbl.querySelector('tbody') : null;
            const tpl = document.getElementById('rowTemplate');
            const addBtn = document.getElementById('addRowBtn');

            // renumber Sr#
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

            // enable/disable manual “Other” fields
            function toggleOther(selectEl){
                const tr = selectEl.closest('tr');
                if(!tr) return;

                if(selectEl.classList.contains('dosageSelect')){
                    const other = tr.querySelector('.dosageOther');
                    if(!other) return;

                    if(selectEl.value === '__OTHER__'){
                        other.removeAttribute('disabled');
                        other.focus();
                    }else{
                        other.value = '';
                        other.setAttribute('disabled', 'disabled');
                    }
                }

                if(selectEl.classList.contains('freqSelect')){
                    const other = tr.querySelector('.freqOther');
                    if(!other) return;

                    if(selectEl.value === '__OTHER__'){
                        other.removeAttribute('disabled');
                        other.focus();
                    }else{
                        other.value = '';
                        other.setAttribute('disabled', 'disabled');
                    }
                }
            }

            function initializeOtherFields(){
                document.querySelectorAll('#medTable tbody tr').forEach(tr => {

                    const dosageOther = tr.querySelector('.dosageOther');
                    const freqOther   = tr.querySelector('.freqOther');

                    if (dosageOther && dosageOther.value.trim() !== '') {
                        dosageOther.removeAttribute('disabled');
                    } else if (dosageOther) {
                        dosageOther.setAttribute('disabled', 'disabled');
                    }

                    if (freqOther && freqOther.value.trim() !== '') {
                        freqOther.removeAttribute('disabled');
                    } else if (freqOther) {
                        freqOther.setAttribute('disabled', 'disabled');
                    }
                });
            }


            // Add row
            if(addBtn && tbody && tpl){
                addBtn.addEventListener('click', function(){
                    const idx = nextIndex();
                    const clone = tpl.content.cloneNode(true);
                    const tr = clone.querySelector('tr');

                    tr.querySelectorAll('input, select').forEach(el => {
                        el.name = el.name.replace('__NAME__', `details[${idx}]`);
                    });

                    tbody.appendChild(tr);
                    //disableAllOther(); // keep new row manual fields disabled initially
                    initializeOtherFields();
                    renumber();
                });
            }

            // Remove row
            document.addEventListener('click', function(e){
                if(e.target && e.target.classList.contains('removeRowBtn')){
                    const tr = e.target.closest('tr');
                    if(tr && tbody){
                        tr.remove();
                        renumber();
                    }
                }
            });

            // Dropdown change event
            document.addEventListener('change', function(e){
                if(e.target && (e.target.classList.contains('dosageSelect') || e.target.classList.contains('freqSelect'))){
                    toggleOther(e.target);
                }
            });

            // Member label update
            const memberSelect = document.getElementById('participant_id');
            const memberLabel = document.getElementById('member_label');
            if(memberSelect && memberLabel){
                memberSelect.addEventListener('change', function(){
                    const txt = memberSelect.options[memberSelect.selectedIndex]?.text || '';
                    memberLabel.textContent = txt;
                });
            }

            //disableAllOther();
            initializeOtherFields();
            renumber();
        })();

        (function(){
        const memberSelect = document.getElementById('participant_id');
        const copyBtn      = document.getElementById('copyLastReviewBtn');
        const infoBox      = document.getElementById('lastReviewInfo');
        const commentsBox  = document.querySelector('textarea[name="comments"]');

        if(!memberSelect || !copyBtn || !infoBox || !commentsBox) return;

        let lastData = null;

        function clearCopyUI(){
            lastData = null;
            infoBox.textContent = '';
            copyBtn.classList.add('d-none');
        }

        // On member change: clear current comments + check if last review exists
        memberSelect.addEventListener('change', function(){
            const pid = this.value;

            // Clear current pharmacist comment so they don't save under wrong member
            commentsBox.value = '';

            clearCopyUI();

            // Also refresh nurse meds list
            refreshNurseMeds(pid);

            if(!pid) return;

            fetch(`{{ url('/daycareform/pharmacist-review/last-review') }}/${pid}`)
                .then(r => r.json())
                .then(data => {
                    if(!data.ok) return;
                    lastData = data;

                    infoBox.textContent = `Last review: ${data.review.review_date} (${data.review.period_key})`;
                    copyBtn.classList.remove('d-none');
                })
                .catch(() => {});
        });

        // Copy last review: copy ONLY comments (no meds)
        copyBtn.addEventListener('click', function(){
            if(!lastData) return;

            const ts = lastData.review.updated_at || '';
            const note = ts
                ? `Copied from prior review (last updated: ${ts})\n--------------------------------------------------------------------------------\n`
                : `Copied from prior review\n--------------------------------------------------------------------------------\n`;

            commentsBox.value = note + (lastData.review.comments || '');
        });


        // ✅ Refresh read-only nurse meds table when member changes
        function refreshNurseMeds(pid){
            const body = document.getElementById('nurseMedsBody');
            if(!body) return;

            if(!pid){
                body.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-3">Select member to view medications.</td></tr>`;
                return;
            }

            body.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-3">Loading...</td></tr>`;

            fetch(`{{ url('/daycareform/pharmacist-review/member') }}/${pid}/nurse-meds`)
                .then(r => r.json())
                .then(data => {
                    if(!data.ok){
                        body.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-3">Error loading medications.</td></tr>`;
                        return;
                    }
                    if(!data.meds || data.meds.length === 0){
                        body.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-3">No medication list found for this member.</td></tr>`;
                        return;
                    }

                    body.innerHTML = data.meds.map((m, i) => `
                        <tr>
                            <td class="text-center">${i+1}</td>
                            <td>${escapeHtml(m.medicine_nm ?? '')}</td>
                            <td>${escapeHtml((m.physician_name ?? m.doctor_id) ?? '')}</td>
                            <td>${escapeHtml(m.dosage_nm ?? '')}</td>
                            <td>${escapeHtml(m.dosage_freq ?? '')}</td>
                            <td>${escapeHtml(m.comments ?? '')}</td>
                            <td>${escapeHtml(capitalize(m.status ?? 'active'))}</td>
                            <td>${escapeHtml(m.status_date ?? '')}</td>
                        </tr>
                    `).join('');
                })
                .catch(() => {
                    body.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-3">Error loading medications.</td></tr>`;
                });
        }

        function capitalize(s){
            s = (s || '').toString();
            return s.length ? s.charAt(0).toUpperCase() + s.slice(1) : s;
        }
        function escapeHtml(str){
            return (str || '').toString()
                .replaceAll('&','&amp;')
                .replaceAll('<','&lt;')
                .replaceAll('>','&gt;')
                .replaceAll('"','&quot;')
                .replaceAll("'","&#039;");
        }

        // Run once on load (for edit page / preselected member)
        refreshNurseMeds(memberSelect.value);

    })();



    </script>

@endsection
