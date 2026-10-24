@extends('admin.admin_dashboard')
@section('title', 'System Admin')
@section('admin')

<div class="page-content">
  <div class="container-fluid">

    <style>
        /* make dayGrid events dot-only */
        .fc .fc-daygrid-event {
            background: transparent !important;
            border: 0 !important;
            padding: 2px 0 !important;
            height: auto !important;
        }
        .fc .fc-daygrid-event .fc-event-title,
        .fc .fc-daygrid-event .fc-event-time { display: none !important; }

        /* the dot itself */
        .bdot {
            display:inline-block;
            width:8px; height:8px;
            border-radius:50%;
            background:#ffd36e;   /* birthday color */
        }

    </style>

    <style>
  /* Cleaner month pills; timeGrid keeps default styles */
  .fc .fc-daygrid-event { padding: 2px 0 !important; }
  .ap-pill { display:inline-block; padding:2px 6px; border-radius:999px; font-size:11px; line-height:1.2; color:#111; background:#fff; border:1px solid rgba(0,0,0,.08); }
  .ap-time { font-weight:600; margin-right:4px; }
</style>

    {{-- Stat cards --}}
    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <div class="card shadow-sm border-0"><div class="card-body">
          <div class="text-muted small">{{ now()->format('Y') }}</div>
          <div class="fs-3 fw-semibold">{{ number_format($stats['total']) }}</div>
          <div class="text-muted">Members</div>
        </div></div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm border-0"><div class="card-body">
          <div class="text-muted small">{{ now()->format('Y') }}</div>
          <div class="fs-3 fw-semibold">{{ number_format($stats['male']) }}</div>
          <div class="text-muted">Male</div>
        </div></div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm border-0"><div class="card-body">
          <div class="text-muted small">{{ now()->format('Y') }}</div>
          <div class="fs-3 fw-semibold">{{ number_format($stats['female']) }}</div>
          <div class="text-muted">Female</div>
        </div></div>
      </div>
    </div>

    <div class="row g-3">
      {{-- Left --}}
      <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-3" style="max-height: 320px;">
          <div class="card-header bg-white border-0 fw-semibold py-2">Members</div>
          <div class="card-body p-2 text-center">
            <div style="height:200px; width:200px; margin:auto;">
                <canvas id="genderDonut"></canvas>
            </div>
            <div class="d-flex justify-content-around mt-3 small text-muted">
              <div>Male: {{ number_format($stats['male']) }}</div>
              <div>Female: {{ number_format($stats['female']) }}</div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 fw-semibold d-flex justify-content-between align-items-center">
                <span>Appointments</span>
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-outline-secondary" data-view="dayGridMonth">Month</button>
                    <button class="btn btn-outline-secondary" data-view="timeGridWeek">Week</button>
                    <button class="btn btn-outline-secondary" data-view="timeGridDay">Day</button>
                    <button class="btn btn-outline-secondary" data-view="listWeek">List</button>
                </div>
            </div>
            <div class="card-body p-2">
                <div id="apptCalendar" style="min-height:420px; border:1px solid #e4e6ef;"></div>
            </div>
            <div class="card-footer bg-white">
                <div id="apptEventsPanel" class="small text-muted">Click a date to see that day’s appointments.</div>
            </div>
        </div>
      </div>

      {{-- Right --}}
      <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-3">
          <div class="card-header bg-white border-0 fw-semibold d-flex justify-content-between align-items-center">
            <span>{{ $monthLabel ?? now()->format('F Y') }}</span>
          </div>
          <div class="card-body p-2">
            <div id="calendar" style="min-height:430px; border:1px solid #e4e6ef;"></div>
          </div>
        </div>

        <div class="card shadow-sm border-0">
          <div class="card-header bg-white border-0 fw-semibold">Events</div>
          <div class="card-body" id="dayEvents">
            <div class="text-muted small">Select a date to see events.</div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="modal fade" id="apptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h6 class="modal-title">Appointment</h6>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="appt_mode" value="create">
      <input type="hidden" id="appt_original_pid">
      <input type="hidden" id="appt_original_dt">

      <div class="mb-2">
        <label class="form-label">Participant</label>
        <select id="appt_participantid" class="form-select form-select-sm">
          @foreach(DB::table('members')->select('ParticipantId','LastName','FirstName')->where('Trans_flag', 'A')->orderBy('LastName')->get() as $m)
            <option value="{{ $m->ParticipantId }}">{{ $m->ParticipantId }} | {{ $m->LastName }}, {{ $m->FirstName }}</option>
          @endforeach
        </select>
      </div>

      <div class="mb-2">
        <label class="form-label">Doctor</label>
        <select id="appt_doctor" class="form-select form-select-sm">
          @foreach(DB::table('physicianmaster as p')
                  ->leftJoin('physiciancategory as c','c.phy_cat_id','=','p.speciality')
                  ->where('p.trans_flag','A')
                  ->orderBy('p.lastname')
                  ->select('p.doctor_id','p.lastname','p.firstname','p.ccity','c.phy_cat_name')
                  ->get() as $d)
            <option value="{{ $d->doctor_id }}">
                {{ $d->lastname }}, {{ $d->firstname }}
                — {{ $d->phy_cat_name ?? 'No Speciality' }}
                ({{ $d->ccity }})
            </option>
          @endforeach
        </select>
      </div>

      <div class="mb-2">
        <label class="form-label">Date & Time</label>
        <input id="appt_date" class="form-control form-control-sm">
      </div>

      <div>
        <label class="form-label">Reason</label>
        <input id="appt_reason" class="form-control form-control-sm">
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" id="btnApptCancel" class="btn btn-outline-danger me-auto">Cancel Appointment</button>
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      <button type="button" id="btnApptSave" class="btn btn-primary">Save</button>
    </div>
  </div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', ()=> {
  if (typeof flatpickr !== 'undefined') {
    flatpickr('#appt_date', { enableTime:true, time_24hr:false, dateFormat:'m/d/Y H:i' });
  }
});
</script>


{{-- Libraries (use defer so they’re ready for our init code) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

<script>
window.addEventListener('load', async function () {
  /* 1) Gender donut (explicit colors so it’s visible) */
  const donutCtx = document.getElementById('genderDonut');
  if (donutCtx && window.Chart) {
    new Chart(donutCtx, {
      type: 'doughnut',
      data: {
        labels: ['Male','Female'],
        datasets: [{
          data: [{{ $stats['male'] }}, {{ $stats['female'] }}],
          backgroundColor: ['#6d66ff', '#ffd36e'],
          borderWidth: 0
        }]
      },
      options: { cutout: '67%', plugins: { legend: { position: 'bottom' } } }
    });
  }

  /* 2) Weekly attendance (fetch safe) */
  const barCtx = document.getElementById('attendanceBar');
  if (barCtx && window.Chart) {
    let weekly = { labels: @json($weekly['labels'] ?? []), present: [], absent: [] };
    try {
      const res = await fetch(`{{ url('/dash/attendance/weekly') }}`);
      if (res.ok) weekly = await res.json();
    } catch (e) { /* keep defaults */ }

    new Chart(barCtx, {
      type: 'bar',
      data: {
        labels: weekly.labels || [],
        datasets: [
          { label: 'Present', data: weekly.present || [], backgroundColor: '#4cc38a' },
          { label: 'Absent',  data: weekly.absent  || [], backgroundColor: '#ef5b5b' }
        ]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } },
        plugins: { legend: { position: 'bottom' } }
      }
    });
  }

  /* 3) FullCalendar (always render the grid) */

    const calEl = document.getElementById('calendar');
if (calEl && window.FullCalendar && FullCalendar.Calendar) {
  const calendar = new FullCalendar.Calendar(calEl, {
    initialView: 'dayGridMonth',
    height: 430,
    dayMaxEventRows: true,
    dayMaxEvents: 4,

    // fetch events (birthdays) for the visible range
    events: async (info, success) => {
      const url = "{{ route('dash.calendarEvents') }}" + `?start=${info.startStr}&end=${info.endStr}`;
      try {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        const data = res.ok ? await res.json() : [];
        success(Array.isArray(data) ? data : []);
      } catch { success([]); }
    },

    // render a single dot (title used as tooltip)
    eventContent(arg) {
      const dot = document.createElement('span');
      dot.className = 'bdot';
      dot.title = arg.event.title || '';
      return { domNodes: [dot] };
    },


    dateClick: async (info) => {
    console.log('dateClick ->', info.dateStr); // should appear in console

    const box = document.getElementById('dayEvents');
    box.innerHTML = '<div class="small text-muted">Loading...</div>';

    const url = "{{ route('dash.eventsByDay') }}" + "?date=" + encodeURIComponent(info.dateStr);

    try {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        if (!res.ok) {
        console.warn('eventsByDay HTTP', res.status, url);
        box.innerHTML = '<div class="small text-muted">No events.</div>';
        return;
        }
        const items = await res.json();
        box.innerHTML = (items && items.length)
        ? items.map(e => `
            <div class="mb-2">
                <div class="fw-semibold">${e.title ?? 'Event'}</div>
                <div class="text-muted small">${e.note ?? ''}</div>
            </div>
            `).join('<hr class="my-2">')
        : '<div class="small text-muted">No events.</div>';
    } catch (err) {
        console.error('eventsByDay fetch error', err);
        box.innerHTML = '<div class="small text-muted">No events.</div>';
    }
    }
        });
        calendar.render();
        console.log('FullCalendar rendered');
    } else {
        console.error('FullCalendar not loaded or #calendar missing');
    }
});

// New enhacement

document.addEventListener('DOMContentLoaded', function () {
  const calEl = document.getElementById('apptCalendar');
  if (!calEl || !window.FullCalendar) return;

  // ---- CSRF (safe) ----
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const CSRF = csrfMeta ? csrfMeta.getAttribute('content') : '';

  // ---- Helpers ----
  // MM/DD/YYYY HH:mm (24h)
  function toMMDDYYYY_HHMM(date) {
    const d = (date instanceof Date) ? date : new Date(date);
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    const yyyy = d.getFullYear();
    const HH = String(d.getHours()).padStart(2, '0');
    const ii = String(d.getMinutes()).padStart(2, '0');
    return `${mm}/${dd}/${yyyy} ${HH}:${ii}`;
  }

  // Fallback display (used for edit modal too)
  function fmtDisplay(dt) { return toMMDDYYYY_HHMM(dt); }

  // ---- Calendar ----
  const cal = new FullCalendar.Calendar(calEl, {
    initialView: 'timeGridWeek',
    height: 420,
    headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
    dayMaxEventRows: true,
    dayMaxEvents: 4,
    editable: true,
    eventDurationEditable: false,
    slotEventOverlap: true,
    eventOverlap: true,

    events: async (info, success) => {
      const url = "{{ route('dash.apptEvents') }}" + `?start=${info.startStr}&end=${info.endStr}`;
      try {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        const data = res.ok ? await res.json() : [];
        success(Array.isArray(data) ? data : []);
      } catch (err) {
        console.error('events feed error', err);
        success([]);
      }
    },

    // compact pills in month view
    eventContent(arg) {
      if (arg.view.type !== 'dayGridMonth') return;
      const p = arg.event.extendedProps || {};
      if ((p.type || 'appointment') !== 'appointment') return;
      const time = p.time || '';
      const doc  = (p.doctor || '').replace(/^Dr\.?\s*/,'').trim();
      const di   = doc ? doc.split(/\s+/).map(s=>s[0]).join('').toUpperCase() : 'DR';
      return { html: `<span class="ap-pill"><span class="ap-time">${time}</span>${di}</span>` };
    },

    // Create from empty date (use strict 24h string)
    dateClick(info) {
      openApptModal({
        mode: 'create',
        participantid: '',
        doctor_id: '',
        appdate_display: toMMDDYYYY_HHMM(info.date),
        reason: ''
      });
    },

    // Edit existing
    eventClick(info) {
      const e = info.event, p = e.extendedProps || {};
      openApptModal({
        mode: 'edit',
        participantid: p.participantid,
        doctor_id:    p.doctor_id || '',
        appdate_iso:  p.appdate_iso,              // Y-m-d H:i:s from feed
        appdate_display: fmtDisplay(e.start),     // editable in modal
        reason: p.reason || ''
      });
    },

    // Drag to reschedule
    eventDrop(info) {
      const p = info.event.extendedProps || {};
      fetch('{{ route('cal.appt.update') }}', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' },
        body: JSON.stringify({
          mode: 'drag',
          original_participantid: p.participantid,
          original_appdate: p.appdate_iso,
          participantid: p.participantid,
          appwith: p.doctor_id,
          reason: p.reason,
          appdate: info.event.start.toISOString(),  // ISO for drag
        })
      }).then(async r => {
          if (!r.ok) { console.error('drag error', r.status, await r.text()); throw new Error(); }
          return r.json();
        })
        .then(()=> cal.refetchEvents())
        .catch((err)=> { console.error(err); info.revert(); alert('Move failed'); });
    }
  });

  cal.render();

  // ---- View buttons (Month/Week/Day/List) ----
  const headerCard = calEl.closest('.card');
  const viewBtns = headerCard ? headerCard.querySelectorAll('.card-header [data-view]') : [];
  const viewMap = { Month:'dayGridMonth', Week:'timeGridWeek', Day:'timeGridDay', List:'listWeek' };
  viewBtns.forEach(btn => {
    if (!btn.hasAttribute('type')) btn.setAttribute('type', 'button');
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      let view = btn.getAttribute('data-view');
      view = viewMap[view] || view; // allow labels or FC view names
      if (view) {
        cal.changeView(view);
        viewBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
      }
    });
  });

  // ---- Modal glue ----
  function openApptModal(s){
    const modal = document.getElementById('apptModal');
    if (!modal) { console.warn('apptModal not found'); return; }
    document.getElementById('appt_mode').value = s.mode;
    document.getElementById('appt_participantid').value = s.participantid || '';
    document.getElementById('appt_doctor').value = s.doctor_id || '';
    document.getElementById('appt_date').value = s.appdate_display || '';
    document.getElementById('appt_reason').value = s.reason || '';
    document.getElementById('appt_original_pid').value = s.participantid || '';
    document.getElementById('appt_original_dt').value  = s.appdate_iso || '';
    document.getElementById('btnApptCancel').style.display = (s.mode === 'edit') ? '' : 'none';
    new bootstrap.Modal(modal).show();
  }

  // Save (create/update)
  const btnSave = document.getElementById('btnApptSave');
  if (btnSave) btnSave.addEventListener('click', function(){
    const mode = document.getElementById('appt_mode').value;
    const payload = {
      participantid: document.getElementById('appt_participantid').value,
      appwith:       document.getElementById('appt_doctor').value,
      reason:        document.getElementById('appt_reason').value,
      appdate:       document.getElementById('appt_date').value, // MM/DD/YYYY HH:mm
    };
    let url = '{{ route('cal.appt.store') }}', method = 'POST';

    if (mode === 'edit') {
      url = '{{ route('cal.appt.update') }}';
      method = 'PUT';
      payload.original_participantid = document.getElementById('appt_original_pid').value;
      payload.original_appdate       = document.getElementById('appt_original_dt').value; // Y-m-d H:i:s
      payload.mode = 'modal';
    }

    fetch(url, {
      method,
      headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' },
      body: JSON.stringify(payload)
    }).then(async r => {
        if (!r.ok) { console.error('save error', r.status, await r.text()); throw new Error(); }
        return r.json();
      })
      .then(()=> {
        const m = bootstrap.Modal.getInstance(document.getElementById('apptModal'));
        if (m) m.hide();
        cal.refetchEvents();
      })
      .catch(()=> alert('Save failed'));
  });

  // Cancel (soft delete)
  const btnCancel = document.getElementById('btnApptCancel');
  if (btnCancel) btnCancel.addEventListener('click', function(){
    const pid = document.getElementById('appt_original_pid').value;
    const iso = document.getElementById('appt_original_dt').value;
    if (!pid || !iso) return;

    fetch('{{ route('cal.appt.cancel') }}', {
      method: 'PUT',
      headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' },
      body: JSON.stringify({ participantid: pid, appdate: iso })
    }).then(async r => {
        if (!r.ok) { console.error('cancel error', r.status, await r.text()); throw new Error(); }
        return r.json();
      })
      .then(()=> {
        const m = bootstrap.Modal.getInstance(document.getElementById('apptModal'));
        if (m) m.hide();
        cal.refetchEvents();
      })
      .catch(()=> alert('Cancel failed'));
  });
});

</script>
@endsection
