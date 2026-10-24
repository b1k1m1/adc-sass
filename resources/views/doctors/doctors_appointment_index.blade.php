@extends('admin.admin_dashboard')
@section('title', 'Appointments')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-1">Appointments</h5>
                        <div class="text-muted small">Calendar View and Table View for member appointments</div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('appt.create') }}" class="btn btn-primary btn-sm">
                            New / Add
                        </a>
                        <a href="{{ route('doctors.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            {{ t('common.back','Back') }}
                        </a>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" id="btnToday" class="btn btn-outline-secondary btn-sm">Today</button>
                        <button type="button" id="btnRefresh" class="btn btn-outline-secondary btn-sm">Refresh</button>
                    </div>

                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <select id="statusFilter" class="form-select form-select-sm" style="width: 170px;">
                            <option value="active_hold" selected>Active + Hold</option>
                            <option value="all">All</option>
                            <option value="deleted">Deleted Only</option>
                        </select>

                        <select id="doctorFilter" class="form-select form-select-sm" style="width: 220px;">
                            <option value="">All Physicians</option>
                            @foreach($physicians as $doc)
                                <option value="{{ $doc->doctor_id }}">{{ $doc->doctor_name }}</option>
                            @endforeach
                        </select>

                        <input type="date" id="rangeStart" class="form-control form-control-sm" style="width: 155px;">
                        <input type="date" id="rangeEnd" class="form-control form-control-sm" style="width: 155px;">
                        <button type="button" id="btnApplyRange" class="btn btn-outline-primary btn-sm">Date Range</button>
                    </div>
                </div>
                <div class="row g-3 mt-1 mb-3">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100 summary-card summary-primary">
                            <div class="card-body">
                                <div class="text-muted small mb-1">Appointments in Range</div>
                                <div class="fs-3 fw-bold" id="summaryAppointments">{{ $summary['today'] ?? 0 }}</div>
                                <div class="small text-muted">Based on selected filters</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100 summary-card summary-warning">
                            <div class="card-body">
                                <div class="text-muted small mb-1">Hold Appointments</div>
                                <div class="fs-3 fw-bold" id="summaryHold">{{ $summary['hold'] ?? 0 }}</div>
                                <div class="small text-muted">Within selected range</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100 summary-card summary-success">
                            <div class="card-body">
                                <div class="text-muted small mb-1">Active Appointments</div>
                                <div class="fs-3 fw-bold" id="summaryActive">{{ $summary['week'] ?? 0 }}</div>
                                <div class="small text-muted">Within selected range</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100 summary-card summary-info">
                            <div class="card-body">
                                <div class="text-muted small mb-1">Distinct Physicians</div>
                                <div class="fs-3 fw-bold" id="summaryPhysicians">{{ $summary['physicians_today'] ?? 0 }}</div>
                                <div class="small text-muted">Filtered physician count</div>
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs nav-tabs-custom nav-justified mt-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-calendar" role="tab">
                            Calendar View
                        </a>

                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-table" role="tab">
                            Table View
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body pt-4">
                <div class="tab-content">

                    {{-- Calendar Tab --}}
                    <div class="tab-pane active" id="tab-calendar" role="tabpanel">
                        <div class="calendar-shell">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-light btn-sm cal-view-btn active-view" data-view="timeGridDay">Day</button>
                                    <button type="button" class="btn btn-light btn-sm cal-view-btn" data-view="timeGridWeek">Week</button>
                                    <button type="button" class="btn btn-light btn-sm cal-view-btn" data-view="dayGridMonth">Month</button>
                                </div>
                                <div id="calendarTitle" class="fw-semibold text-dark"></div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 align-items-center small text-muted mb-3">
                                    <span><span class="legend-dot legend-active"></span> Active</span>
                                    <span><span class="legend-dot legend-hold"></span> Hold</span>
                                    <span><span class="legend-dot legend-deleted"></span> Deleted</span>
                                </div>

                            <div id="appointmentCalendar"></div>
                        </div>
                    </div>

                    {{-- Table Tab --}}
                    <div class="tab-pane" id="tab-table" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-bordered align-middle" id="tbl-appt" style="width:100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Participant</th>
                                        <th>Appt Date/Time</th>
                                        <th>Doctor</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

{{-- Appointment Modal --}}
<div class="modal fade" id="apptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body pt-3">
                <div class="mb-2">
                    <div class="small text-muted">Member</div>
                    <div id="modalMember" class="fw-semibold"></div>
                </div>

                <div class="mb-2">
                    <div class="small text-muted">Doctor</div>
                    <div id="modalDoctor" class="fw-semibold"></div>
                </div>

                <div class="mb-2">
                    <div class="small text-muted">Appointment Date / Time</div>
                    <div id="modalAppDate" class="fw-semibold"></div>
                </div>

                <div class="mb-2">
                    <div class="small text-muted">Reason</div>
                    <div id="modalReason" class="fw-semibold"></div>
                </div>

                <div class="mb-3">
                    <div class="small text-muted">Status</div>
                    <div id="modalStatus" class="fw-semibold"></div>
                </div>

                <input type="hidden" id="modalParticipantId">
                <input type="hidden" id="modalAppDateIso">
                <input type="hidden" id="modalEditUrl">
                <input type="hidden" id="modalViewUrl">
            </div>

            <div class="modal-footer border-0 pt-0 d-flex justify-content-between flex-wrap gap-2">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-warning btn-sm" id="btnHoldAppt">Hold</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btnDeleteAppt">Delete</button>
                </div>

                <div class="d-flex gap-2">
                    <a href="#" id="btnViewAppt" class="btn btn-outline-secondary btn-sm">View</a>
                    <a href="#" id="btnEditAppt" class="btn btn-primary btn-sm">Edit</a>
                </div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">

<style>
    .calendar-shell{
        background: #fff;
        border: 1px solid #eef1f7;
        border-radius: 18px;
        padding: 16px;
    }

    #appointmentCalendar{
        min-height: 760px;
    }

    .fc .fc-toolbar-title{
        font-size: 1.05rem;
        font-weight: 600;
    }

    .fc-theme-standard td,
    .fc-theme-standard th,
    .fc-theme-standard .fc-scrollgrid{
        border-color: #edf0f5;
    }

    .fc .fc-timegrid-slot{
        height: 3rem;
    }

    .fc .fc-daygrid-day-number,
    .fc .fc-col-header-cell-cushion{
        color: #344054;
        text-decoration: none;
        font-weight: 600;
    }

    .fc .fc-button{
        border-radius: 10px !important;
    }

    .fc-event{
        border: 0 !important;
        border-radius: 12px !important;
        padding: 3px 4px !important;
        box-shadow: 0 4px 12px rgba(16,24,40,.08);
        cursor: pointer;
    }

    .fc-appt-active{
        background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
        color: #fff !important;
    }

    .fc-appt-hold{
        background: linear-gradient(135deg, #f59e0b, #fbbf24) !important;
        color: #fff !important;
    }

    .fc-appt-deleted{
        background: #e5e7eb !important;
        color: #6b7280 !important;
        text-decoration: line-through;
    }

    .fc .fc-timegrid-now-indicator-line{
        border-color: #ef4444;
    }

    .fc .fc-day-today{
        background: rgba(79,70,229,.05) !important;
    }

    .active-view{
        background-color: #e9ecef !important;
        border-color: #dee2e6 !important;
        font-weight: 600;
    }

    .summary-card {
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(16,24,40,.08) !important;
    }

    .summary-primary {
        border-left: 4px solid #4f46e5;
    }

    .summary-warning {
        border-left: 4px solid #f59e0b;
    }

    .summary-success {
        border-left: 4px solid #10b981;
    }

    .summary-info {
        border-left: 4px solid #0ea5e9;
    }

    .fc-event-custom {
        line-height: 1.15;
        padding: 2px 1px;
    }

    .fc-event-time-label {
        font-size: 11px;
        font-weight: 700;
        opacity: .95;
        margin-bottom: 2px;
    }

    .fc-event-member {
        font-size: 12px;
        font-weight: 700;
        white-space: normal;
    }

    .fc-event-doctor {
        font-size: 11px;
        opacity: .95;
        white-space: normal;
        margin-top: 2px;
    }

    .fc-event-badge {
        display: inline-block;
        margin-top: 4px;
        padding: 1px 6px;
        border-radius: 999px;
        background: rgba(255,255,255,.22);
        font-size: 10px;
        font-weight: 700;
    }

    .legend-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 6px;
    }

    .legend-active { background: #6366f1; }
    .legend-hold { background: #f59e0b; }
    .legend-deleted { background: #9ca3af; }

</style>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('appointmentCalendar');
    const modalEl = document.getElementById('apptModal');
    const apptModal = new bootstrap.Modal(modalEl);

    let apptTable = null;
    let calendar = null;

    function saveAppointmentDashboardState() {
        if (!calendar) return;

        const activeTab = document.querySelector('.nav-tabs .nav-link.active');

        const state = {
            status: $('#statusFilter').val(),
            doctor_id: $('#doctorFilter').val(),
            range_start: $('#rangeStart').val(),
            range_end: $('#rangeEnd').val(),
            active_tab: activeTab ? activeTab.getAttribute('href') : '#tab-calendar',
            calendar_view: calendar.view.type,
            calendar_date: calendar.getDate().toISOString().slice(0, 10)
        };

        localStorage.setItem('apptDashboardState', JSON.stringify(state));
    }

    function loadAppointmentDashboardState() {
        const raw = localStorage.getItem('apptDashboardState');
        if (!raw) return null;

        try {
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    }

    function refreshSummaryCards() {
        $.get("{{ route('appt.summary.data') }}", {
            status: $('#statusFilter').val(),
            doctor_id: $('#doctorFilter').val(),
            range_start: $('#rangeStart').val(),
            range_end: $('#rangeEnd').val()
        }, function(resp) {
            $('#summaryAppointments').text(resp.appointments_in_range ?? 0);
            $('#summaryHold').text(resp.hold ?? 0);
            $('#summaryActive').text(resp.active ?? 0);
            $('#summaryPhysicians').text(resp.physicians ?? 0);
        });
    }

    function refreshAllViews(keepPaging = false) {
        if (calendar) {
            calendar.refetchEvents();
        }

        if (apptTable) {
            apptTable.ajax.reload(null, !keepPaging);
        }

        refreshSummaryCards();
    }

    function formatDateTime(dateObj) {
        const yyyy = dateObj.getFullYear();
        const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
        const dd = String(dateObj.getDate()).padStart(2, '0');
        const hh = String(dateObj.getHours()).padStart(2, '0');
        const mi = String(dateObj.getMinutes()).padStart(2, '0');
        const ss = String(dateObj.getSeconds()).padStart(2, '0');

        return `${yyyy}-${mm}-${dd} ${hh}:${mi}:${ss}`;
    }

    function formatStatus(status) {
        if (status === 'A') return 'Active';
        if (status === 'H') return 'Hold';
        if (status === 'D') return 'Deleted';
        return status;
    }

    const savedState = loadAppointmentDashboardState();

    // Restore saved filters FIRST
    if (savedState) {
        if (savedState.status !== undefined) {
            $('#statusFilter').val(savedState.status);
        }

        if (savedState.doctor_id !== undefined) {
            $('#doctorFilter').val(savedState.doctor_id);
        }

        if (savedState.range_start !== undefined) {
            $('#rangeStart').val(savedState.range_start);
        }

        if (savedState.range_end !== undefined) {
            $('#rangeEnd').val(savedState.range_end);
        }
    }

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridDay',
        initialDate: new Date(),
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: ''
        },
        firstDay: 0,
        slotMinTime: '07:00:00',
        slotMaxTime: '19:30:00',
        allDaySlot: false,
        expandRows: true,
        nowIndicator: true,
        editable: true,
        selectable: false,
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },
        eventSources: [{
            url: "{{ route('appt.calendar.events') }}",
            method: 'GET',
            extraParams: function() {
                return {
                    status: $('#statusFilter').val(),
                    doctor_id: $('#doctorFilter').val(),
                    range_start: $('#rangeStart').val(),
                    range_end: $('#rangeEnd').val()
                };
            }
        }],
        eventDidMount: function(info) {
            info.el.setAttribute('title', info.event.title);
        },
        eventClick: function(info) {
            const props = info.event.extendedProps;

            $.get("{{ route('appt.calendar.details') }}", {
                participantid: props.participantid,
                appdate: props.appdate
            }, function(resp) {
                if (!resp.ok) return;

                $('#modalMember').text(resp.row.member_name ?? '');
                $('#modalDoctor').text(resp.row.doctor_name ?? '');
                $('#modalAppDate').text(resp.row.appdate ?? '');
                $('#modalReason').text(resp.row.reason ?? '');
                $('#modalStatus').text(formatStatus(resp.row.status ?? ''));

                $('#modalParticipantId').val(resp.row.participantid);
                $('#modalAppDateIso').val(resp.row.appdate_iso);
                $('#modalEditUrl').val(resp.row.edit_url);
                $('#modalViewUrl').val(resp.row.view_url);

                $('#btnEditAppt').attr('href', resp.row.edit_url);
                $('#btnViewAppt').attr('href', resp.row.view_url);

                apptModal.show();
            });
        },
        eventContent: function(arg) {
            const props = arg.event.extendedProps || {};

            const wrap = document.createElement('div');
            wrap.className = 'fc-event-custom';

            const time = document.createElement('div');
            time.className = 'fc-event-time-label';
            time.textContent = props.time_label || '';

            const member = document.createElement('div');
            member.className = 'fc-event-member';
            member.textContent = props.member_name || arg.event.title || '';

            wrap.appendChild(time);
            wrap.appendChild(member);

            if (props.doctor_name) {
                const doctor = document.createElement('div');
                doctor.className = 'fc-event-doctor';
                doctor.textContent = 'Dr. ' + props.doctor_name;
                wrap.appendChild(doctor);
            }

            if (props.status === 'H') {
                const badge = document.createElement('div');
                badge.className = 'fc-event-badge';
                badge.textContent = 'Hold';
                wrap.appendChild(badge);
            }

            return { domNodes: [wrap] };
        },
        eventDrop: function(info) {
            const props = info.event.extendedProps;

            Swal.fire({
                title: 'Reschedule appointment?',
                text: 'Do you want to move this appointment to the new time?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, reschedule'
            }).then((result) => {
                if (!result.isConfirmed) {
                    info.revert();
                    return;
                }

                $.ajax({
                    url: "{{ route('appt.calendar.move') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        participantid: props.participantid,
                        old_appdate: props.appdate,
                        new_appdate: formatDateTime(info.event.start)
                    },
                    success: function(resp) {
                        Swal.fire('Updated', resp.message, 'success');
                        refreshAllViews(true);
                    },
                    error: function(xhr) {
                        info.revert();
                        Swal.fire('Error', xhr.responseJSON?.message ?? 'Unable to reschedule appointment.', 'error');
                    }
                });
            });
        },
        datesSet: function(info) {
            $('#calendarTitle').text(info.view.title);
        }
    });

    apptTable = $('#tbl-appt').DataTable({
        processing: true,
        ajax: {
            url: "{{ route('appt.table.data') }}",
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.doctor_id = $('#doctorFilter').val();
                d.range_start = $('#rangeStart').val();
                d.range_end = $('#rangeEnd').val();
            }
        },
        order: [[1, 'asc']],
        pageLength: 25,
        columns: [
            { data: 'member_name' },
            { data: 'appdate_display' },
            { data: 'doctor_name' },
            { data: 'reason', defaultContent: '' },
            {
                data: 'trans_flag',
                render: function(data) {
                    if (data === 'A') {
                        return '<span class="badge rounded-pill bg-success-subtle text-success">Active</span>';
                    }
                    if (data === 'H') {
                        return '<span class="badge rounded-pill bg-warning-subtle text-warning">Hold</span>';
                    }
                    return '<span class="badge rounded-pill bg-danger-subtle text-danger">Deleted</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-end',
                render: function(data, type, row) {
                    return `
                        <a class="btn btn-outline-secondary btn-sm" href="${row.view_url}">View</a>
                        <a class="btn btn-primary btn-sm" href="${row.edit_url}">Edit</a>
                    `;
                }
            }
        ]
    });

    calendar.render();

    // Restore saved calendar state AFTER render
    if (savedState) {
        if (savedState.calendar_date) {
            calendar.gotoDate(savedState.calendar_date);
        }

        if (savedState.calendar_view) {
            calendar.changeView(savedState.calendar_view, savedState.calendar_date || undefined);
        }

        if (savedState.active_tab) {
            const triggerEl = document.querySelector(`.nav-tabs .nav-link[href="${savedState.active_tab}"]`);
            if (triggerEl) {
                new bootstrap.Tab(triggerEl).show();
            }
        }

        $('.cal-view-btn').removeClass('active-view');
        $(`.cal-view-btn[data-view="${savedState.calendar_view || 'timeGridDay'}"]`).addClass('active-view');
    } else {
        $('.cal-view-btn').removeClass('active-view');
        $('.cal-view-btn[data-view="timeGridDay"]').addClass('active-view');
    }

    refreshAllViews();

    $('.cal-view-btn').on('click', function() {
        $('.cal-view-btn').removeClass('active-view');
        $(this).addClass('active-view');

        const view = $(this).data('view');
        calendar.changeView(view);
        saveAppointmentDashboardState();
    });

    $('#btnToday').on('click', function() {
        $('#rangeStart').val('');
        $('#rangeEnd').val('');
        $('#statusFilter').val('active_hold');
        $('#doctorFilter').val('');

        calendar.today();
        calendar.changeView('timeGridDay');

        $('.cal-view-btn').removeClass('active-view');
        $('.cal-view-btn[data-view="timeGridDay"]').addClass('active-view');

        refreshAllViews();
        saveAppointmentDashboardState();
    });

    $('#btnRefresh').on('click', function() {
        refreshAllViews(true);
        saveAppointmentDashboardState();
    });

    $('#statusFilter, #doctorFilter').on('change', function() {
        refreshAllViews();
        saveAppointmentDashboardState();
    });

    $('#btnApplyRange').on('click', function() {
        const start = $('#rangeStart').val();
        const end = $('#rangeEnd').val();

        if (!start || !end) {
            Swal.fire('Missing dates', 'Please select both start and end dates.', 'warning');
            return;
        }

        calendar.gotoDate(start);
        refreshAllViews();
        saveAppointmentDashboardState();
    });

    $('#btnHoldAppt').on('click', function() {
        updateStatus('H', 'Put this appointment on hold?');
    });

    $('#btnDeleteAppt').on('click', function() {
        updateStatus('D', 'Delete this appointment?');
    });

    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        saveAppointmentDashboardState();
    });

    function updateStatus(flag, title) {
        const participantid = $('#modalParticipantId').val();
        const appdate = $('#modalAppDateIso').val();

        Swal.fire({
            title: title,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: "{{ route('appt.calendar.status') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    participantid: participantid,
                    appdate: appdate,
                    trans_flag: flag
                },
                success: function(resp) {
                    apptModal.hide();
                    Swal.fire('Updated', resp.message, 'success');
                    refreshAllViews(true);
                    saveAppointmentDashboardState();
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message ?? 'Unable to update status.', 'error');
                }
            });
        });
    }
});
</script>
@endsection
