@extends('admin.admin_dashboard')
@section('title', 'System Admin')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <style>
            :root{
                --dash-border: #e9edf3;
                --dash-border-soft: #f1f4f8;
                --dash-shadow: 0 2px 10px rgba(15, 23, 42, 0.05);
                --dash-shadow-hover: 0 8px 24px rgba(15, 23, 42, 0.08);
                --dash-text: #1f2937;
                --dash-muted: #6b7280;
                --dash-soft-muted: #8a94a6;
                --dash-bg-soft: #f8fafc;
                --dash-primary: #6d66ff;
                --dash-success: #22c55e;
                --dash-warning: #f59e0b;
                --dash-danger: #ef4444;
            }

            .dashboard-card {
                border: 1px solid var(--dash-border) !important;
                border-radius: 16px;
                box-shadow: var(--dash-shadow);
                transition: all .2s ease;
                background: #fff;
            }

            .dashboard-card:hover {
                box-shadow: var(--dash-shadow-hover);
            }

            .dashboard-card .card-header {
                background: #fff;
                border-bottom: 1px solid var(--dash-border-soft) !important;
                padding: .85rem 1rem;
            }

            .dashboard-section-title {
                font-weight: 700;
                color: var(--dash-text);
                letter-spacing: .01em;
            }

            .dashboard-soft-muted {
                color: var(--dash-soft-muted);
            }

            .dashboard-muted {
                color: var(--dash-muted);
            }

            .dashboard-stat {
                position: relative;
                background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
            }

            .dashboard-stat::before {
                content: "";
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 4px;
                background: linear-gradient(90deg, var(--dash-primary), #9e9bff);
            }

            .dashboard-stat .stat-wrap {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }

            .dashboard-stat .stat-year {
                font-size: 12px;
                color: var(--dash-soft-muted);
                letter-spacing: .05em;
            }

            .dashboard-stat .stat-value {
                font-size: 2rem;
                font-weight: 800;
                color: var(--dash-text);
                line-height: 1.05;
                margin-top: 2px;
            }

            .dashboard-stat .stat-label {
                color: var(--dash-muted);
                font-size: .95rem;
                margin-top: 2px;
            }

            .stat-icon {
                width: 46px;
                height: 46px;
                border-radius: 12px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                font-weight: 700;
                flex-shrink: 0;
            }

            .stat-icon.members {
                background: rgba(109, 102, 255, .12);
                color: var(--dash-primary);
            }

            .stat-icon.male {
                background: rgba(59, 130, 246, .12);
                color: #2563eb;
            }

            .stat-icon.female {
                background: rgba(245, 158, 11, .15);
                color: #d97706;
            }

            .weekly-alerts-header {
                background: linear-gradient(180deg, #fafbff 0%, #ffffff 100%);
            }

            .alert-mini-badge {
                border-radius: 999px;
                font-size: 12px;
                font-weight: 700;
                padding: 5px 10px;
                letter-spacing: .01em;
                box-shadow: inset 0 0 0 1px rgba(255,255,255,.15);
            }

            .weekly-alert-box {
                border: 1px solid var(--dash-border);
                border-radius: 14px;
                padding: 12px;
                background: #fcfcfd;
                min-height: 100%;
                position: relative;
            }

            .weekly-alert-box::before {
                content: "";
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 4px;
                border-top-left-radius: 14px;
                border-bottom-left-radius: 14px;
            }

            .weekly-alert-box.appointments {
                background: linear-gradient(180deg, #fbfbff 0%, #ffffff 100%);
            }
            .weekly-alert-box.appointments::before {
                background: var(--dash-primary);
            }

            .weekly-alert-box.birthdays {
                background: linear-gradient(180deg, #f8fff9 0%, #ffffff 100%);
            }
            .weekly-alert-box.birthdays::before {
                background: var(--dash-success);
            }

            .weekly-alert-box.pa {
                background: linear-gradient(180deg, #fffaf2 0%, #ffffff 100%);
            }
            .weekly-alert-box.pa::before {
                background: var(--dash-warning);
            }

            .weekly-alert-box.hnp {
                background: linear-gradient(180deg, #fff8f8 0%, #ffffff 100%);
            }
            .weekly-alert-box.hnp::before {
                background: var(--dash-danger);
            }

            .weekly-alert-box-title {
                font-size: .98rem;
                font-weight: 700;
                color: var(--dash-text);
                margin-bottom: .65rem;
                padding-left: 4px;
            }

            .weekly-alert-item {
                padding: 0 0 8px 4px;
                margin-bottom: 8px;
                border-bottom: 1px dashed #e8edf3;
            }

            .weekly-alert-item:last-child {
                border-bottom: 0;
                margin-bottom: 0;
                padding-bottom: 0;
            }

            .weekly-alert-name {
                font-weight: 700;
                color: var(--dash-text);
                line-height: 1.25;
            }

            .weekly-alert-sub {
                color: var(--dash-muted);
                font-size: 12px;
                line-height: 1.35;
                margin-top: 2px;
            }

            .weekly-alert-empty {
                color: var(--dash-soft-muted);
                font-size: 13px;
                padding-left: 4px;
            }

                        /* Safe compact calendar card only */
            .card .calendar-shell {
                border-radius: 14px;
            }

            .card-header .dashboard-section-title.small {
                font-size: .9rem;
                font-weight: 700;
            }

            #calendar .fc-toolbar {
                margin-bottom: .5rem !important;
            }

            #calendar .fc-toolbar-title {
                font-size: 1rem !important;
                font-weight: 800;
            }

            #calendar .fc-button {
                padding: .32rem .65rem !important;
                font-size: .8rem !important;
                border-radius: 8px !important;
            }

            .fc .fc-daygrid-event {
                background: transparent !important;
                border: 0 !important;
                padding: 2px 0 !important;
                height: auto !important;
            }

            .fc .fc-daygrid-event .fc-event-title,
            .fc .fc-daygrid-event .fc-event-time {
                display: none !important;
            }

            .bdot {
                display: inline-block;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #ffd36e;
            }

            .fc .fc-daygrid-event {
                padding: 2px 0 !important;
            }

            .ap-pill {
                display: inline-block;
                padding: 2px 7px;
                border-radius: 999px;
                font-size: 11px;
                line-height: 1.2;
                color: #111;
                background: #fff;
                border: 1px solid rgba(0,0,0,.08);
                box-shadow: 0 1px 2px rgba(0,0,0,.03);
            }

            .ap-time {
                font-weight: 700;
                margin-right: 4px;
            }

            .btn-view-switch .btn {
                border-color: var(--dash-border);
                color: #5b6472;
                background: #fff;
            }

            .btn-view-switch .btn:hover,
            .btn-view-switch .btn.active {
                background: #f3f4ff;
                color: var(--dash-primary);
                border-color: #dcdfff;
            }

            @media (max-width: 991.98px) {
                .dashboard-stat .stat-value {
                    font-size: 1.7rem;
                }
            }

            .shift-summary-card {
            border: 1px solid var(--dash-border) !important;
            border-radius: 16px;
            box-shadow: var(--dash-shadow);
            overflow: hidden;
            height: 100%;
            background: #fff;
        }

        .shift-summary-card .shift-head {
            padding: .85rem 1rem;
            color: #fff;
        }

        .shift-summary-card .shift-title {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .shift-summary-card .shift-subtitle {
            font-size: 12px;
            opacity: .9;
            margin-top: 2px;
        }

        .shift-summary-card .shift-body {
            padding: .9rem;
            background: #fff;
        }

        .shift-summary-card .shift-stats {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .shift-summary-card .shift-stat-box {
            flex: 1 1 30%;
            min-width: 120px;
            border: 1px solid var(--dash-border-soft);
            border-radius: 12px;
            padding: .75rem .85rem;
            background: #f8fafc;
        }

        .shift-summary-card .shift-stat-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--dash-text);
            line-height: 1.05;
            margin-bottom: 2px;
        }

        .shift-summary-card .shift-stat-label {
            font-size: .92rem;
            color: var(--dash-muted);
        }

        .shift-summary-card.morning .shift-head {
            background: linear-gradient(90deg, #6d66ff, #8e89ff);
        }

        .shift-summary-card.morning .shift-stat-box {
            background: #f6f5ff;
        }

        .shift-summary-card.noon .shift-head {
            background: linear-gradient(90deg, #0ea5a4, #2dd4bf);
        }

        .shift-summary-card.noon .shift-stat-box {
            background: #f0fdfa;
        }

        @media (max-width: 767.98px) {
            .shift-summary-card .shift-stats {
                flex-direction: column;
            }

            .shift-summary-card .shift-stat-box {
                min-width: 100%;
            }
        }

        .shift-stat-box {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .shift-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .morning .shift-stat-icon {
            background: rgba(109, 102, 255, .13);
            color: #6d66ff;
        }

        .noon .shift-stat-icon {
            background: rgba(14, 165, 164, .13);
            color: #0ea5a4;
        }

        .shift-stat-content {
            min-width: 0;
        }

        .shift-head-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .shift-main-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .18);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        </style>

        {{-- Compact Stat cards by shift --}}
        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="shift-summary-card morning">
                    <div class="shift-head">
                        <div class="shift-head-inner">
                            <div>
                                <div class="shift-title">Morning Shift Members</div>
                                <div class="shift-subtitle">{{ now()->format('Y') }}</div>
                            </div>
                            <div class="shift-main-icon">
                                <i class="fas fa-sun"></i>
                            </div>
                        </div>
                    </div>

                    <div class="shift-body">
                        <div class="shift-stats">
                            <div class="shift-stat-box">
                                <div class="shift-stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="shift-stat-content">
                                    <div class="shift-stat-value">{{ number_format($stats['morning']['total'] ?? 0) }}</div>
                                    <div class="shift-stat-label">Members</div>
                                </div>
                            </div>

                            <div class="shift-stat-box">
                                <div class="shift-stat-icon">
                                    <i class="fas fa-male"></i>
                                </div>
                                <div class="shift-stat-content">
                                    <div class="shift-stat-value">{{ number_format($stats['morning']['male'] ?? 0) }}</div>
                                    <div class="shift-stat-label">Male</div>
                                </div>
                            </div>

                            <div class="shift-stat-box">
                                <div class="shift-stat-icon">
                                    <i class="fas fa-female"></i>
                                </div>
                                <div class="shift-stat-content">
                                    <div class="shift-stat-value">{{ number_format($stats['morning']['female'] ?? 0) }}</div>
                                    <div class="shift-stat-label">Female</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="shift-summary-card noon">
                    <div class="shift-head">
                        <div class="shift-head-inner">
                            <div>
                                <div class="shift-title">Noon Shift Members</div>
                                <div class="shift-subtitle">{{ now()->format('Y') }}</div>
                            </div>
                            <div class="shift-main-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>

                    <div class="shift-body">
                        <div class="shift-stats">
                            <div class="shift-stat-box">
                                <div class="shift-stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="shift-stat-content">
                                    <div class="shift-stat-value">{{ number_format($stats['noon']['total'] ?? 0) }}</div>
                                    <div class="shift-stat-label">Members</div>
                                </div>
                            </div>

                            <div class="shift-stat-box">
                                <div class="shift-stat-icon">
                                    <i class="fas fa-male"></i>
                                </div>
                                <div class="shift-stat-content">
                                    <div class="shift-stat-value">{{ number_format($stats['noon']['male'] ?? 0) }}</div>
                                    <div class="shift-stat-label">Male</div>
                                </div>
                            </div>

                            <div class="shift-stat-box">
                                <div class="shift-stat-icon">
                                    <i class="fas fa-female"></i>
                                </div>
                                <div class="shift-stat-content">
                                    <div class="shift-stat-value">{{ number_format($stats['noon']['female'] ?? 0) }}</div>
                                    <div class="shift-stat-label">Female</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(
            auth()->check() &&
            method_exists(auth()->user(), 'hasAnyRole') &&
            auth()->user()->hasAnyRole(['superadmin', 'admin', 'genadmin'])
        )
            <div class="row g-2 mb-2">
                <div class="col-12">
                    <div class="card dashboard-card border-0">
                        <div class="card-header weekly-alerts-header border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <div class="dashboard-section-title">Weekly Alerts</div>
                                <div class="small dashboard-soft-muted">
                                    Week:
                                    {{ \Carbon\Carbon::parse($weeklyAlerts['week_start'])->format('m/d/Y') }}
                                    -
                                    {{ \Carbon\Carbon::parse($weeklyAlerts['week_end'])->format('m/d/Y') }}
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                <span class="badge bg-primary alert-mini-badge">
                                    Appointments: {{ $weeklyAlerts['appointments_count'] ?? 0 }}
                                </span>
                                <span class="badge bg-success alert-mini-badge">
                                    Birthdays: {{ $weeklyAlerts['birthday_count'] ?? 0 }}
                                </span>
                                <span class="badge bg-warning text-dark alert-mini-badge">
                                    PA: {{ $weeklyAlerts['pa_count'] ?? 0 }}
                                </span>
                                <span class="badge bg-danger alert-mini-badge">
                                    HNP: {{ $weeklyAlerts['hnp_count'] ?? 0 }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body py-2 px-3">
                            <div class="row g-2">

                                <div class="col-lg-3">
                                    <div class="weekly-alert-box appointments">
                                        <div class="weekly-alert-box-title">Doctor's Appointments This Week</div>

                                        @if(($weeklyAlerts['appointments_count'] ?? 0) > 0)
                                            @foreach(($weeklyAlerts['appointments_preview'] ?? []) as $row)
                                                <div class="small weekly-alert-item">
                                                    <div class="weekly-alert-name">
                                                        {{ $row['ParticipantId'] }} - {{ $row['name'] }}
                                                    </div>
                                                    <div class="weekly-alert-sub">
                                                        @if(!empty($row['appointment_date']))
                                                            {{ \Carbon\Carbon::parse($row['appointment_date'])->format('m/d/Y') }}
                                                        @endif
                                                        {{ $row['appointment_time'] ?? '' }}
                                                        @if(!empty($row['physician_name']))
                                                            - {{ $row['physician_name'] }}
                                                        @endif
                                                    </div>
                                                    @if(!empty($row['reason']))
                                                        <div class="weekly-alert-sub">Reason: {{ $row['reason'] }}</div>
                                                    @endif
                                                </div>
                                            @endforeach

                                            @if(($weeklyAlerts['appointments_count'] ?? 0) > count($weeklyAlerts['appointments_preview'] ?? []))
                                                <div class="small dashboard-soft-muted ps-1">
                                                    ... and {{ ($weeklyAlerts['appointments_count'] ?? 0) - count($weeklyAlerts['appointments_preview'] ?? []) }} more
                                                </div>
                                            @endif
                                        @else
                                            <div class="weekly-alert-empty">No appointments for this week.</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="weekly-alert-box birthdays">
                                        <div class="weekly-alert-box-title">Birthdays This Week</div>

                                        @if(($weeklyAlerts['birthday_count'] ?? 0) > 0)
                                            @foreach(($weeklyAlerts['birthday_preview'] ?? []) as $row)
                                                <div class="small weekly-alert-item">
                                                    <div class="weekly-alert-name">
                                                        {{ $row['ParticipantId'] }} - {{ $row['name'] }}
                                                    </div>
                                                    <div class="weekly-alert-sub">
                                                        @if(!empty($row['birthday_this_year']))
                                                            {{ \Carbon\Carbon::parse($row['birthday_this_year'])->format('m/d/Y') }}
                                                        @endif
                                                        @if(!empty($row['birthday_age_text']))
                                                            ({{ $row['birthday_age_text'] }})
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if(($weeklyAlerts['birthday_count'] ?? 0) > count($weeklyAlerts['birthday_preview'] ?? []))
                                                <div class="small dashboard-soft-muted ps-1">
                                                    ... and {{ ($weeklyAlerts['birthday_count'] ?? 0) - count($weeklyAlerts['birthday_preview'] ?? []) }} more
                                                </div>
                                            @endif
                                        @else
                                            <div class="weekly-alert-empty">No birthdays this week.</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="weekly-alert-box pa">
                                        <div class="weekly-alert-box-title">PA Expiring This Week</div>

                                        @if(($weeklyAlerts['pa_count'] ?? 0) > 0)
                                            @foreach(($weeklyAlerts['pa_preview'] ?? []) as $row)
                                                <div class="small weekly-alert-item">
                                                    <div class="weekly-alert-name">
                                                        {{ $row['ParticipantId'] }} - {{ $row['name'] }}
                                                    </div>
                                                    <div class="weekly-alert-sub">
                                                        @if(!empty($row['expire_date']))
                                                            Expires: {{ \Carbon\Carbon::parse($row['expire_date'])->format('m/d/Y') }}
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if(($weeklyAlerts['pa_count'] ?? 0) > count($weeklyAlerts['pa_preview'] ?? []))
                                                <div class="small dashboard-soft-muted ps-1">
                                                    ... and {{ ($weeklyAlerts['pa_count'] ?? 0) - count($weeklyAlerts['pa_preview'] ?? []) }} more
                                                </div>
                                            @endif
                                        @else
                                            <div class="weekly-alert-empty">No PA expiring this week.</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="weekly-alert-box hnp">
                                        <div class="weekly-alert-box-title">HNP Expiring This Week</div>

                                        @if(($weeklyAlerts['hnp_count'] ?? 0) > 0)
                                            @foreach(($weeklyAlerts['hnp_preview'] ?? []) as $row)
                                                <div class="small weekly-alert-item">
                                                    <div class="weekly-alert-name">
                                                        {{ $row['ParticipantId'] }} - {{ $row['name'] }}
                                                    </div>
                                                    <div class="weekly-alert-sub">
                                                        @if(!empty($row['expire_date']))
                                                            Expires: {{ \Carbon\Carbon::parse($row['expire_date'])->format('m/d/Y') }}
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if(($weeklyAlerts['hnp_count'] ?? 0) > count($weeklyAlerts['hnp_preview'] ?? []))
                                                <div class="small dashboard-soft-muted ps-1">
                                                    ... and {{ ($weeklyAlerts['hnp_count'] ?? 0) - count($weeklyAlerts['hnp_preview'] ?? []) }} more
                                                </div>
                                            @endif
                                        @else
                                            <div class="weekly-alert-empty">No HNP expiring this week.</div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-2">
            <div class="col-lg-8">
                <div class="card dashboard-card border-0 mb-2" style="max-height: 320px;">
                    <div class="card-header border-0">
                        <span class="dashboard-section-title">Members</span>
                    </div>
                    <div class="card-body p-2 text-center">
                        <div style="height:200px; width:200px; margin:auto;">
                            <canvas id="genderDonut"></canvas>
                        </div>
                        <div class="d-flex justify-content-around mt-3 small dashboard-soft-muted">
                            <div>Male: {{ number_format($stats['male']) }}</div>
                            <div>Female: {{ number_format($stats['female']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="card dashboard-card border-0">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="dashboard-section-title">Appointments</span>
                        <div class="btn-group btn-group-sm btn-view-switch" role="group">
                            <button class="btn btn-outline-secondary" data-view="dayGridMonth">Month</button>
                            <button class="btn btn-outline-secondary" data-view="timeGridWeek">Week</button>
                            <button class="btn btn-outline-secondary" data-view="timeGridDay">Day</button>
                            <button class="btn btn-outline-secondary" data-view="listWeek">List</button>
                        </div>
                    </div>
                    <div class="card-body p-2" style="overflow: visible;">
                        <div id="apptCalendar" class="calendar-shell" style="min-height:420px;"></div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <div id="apptEventsPanel" class="small dashboard-soft-muted">
                            Click a date to see that day’s appointments.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card dashboard-card border-0 mb-2">

                    <div class="card-body p-2" style="overflow: visible;">
                        <div id="calendar" class="calendar-shell" style="min-height:430px;"></div>
                    </div>
                </div>

                <div class="card dashboard-card border-0">
                    <div class="card-header border-0">
                        <span class="dashboard-section-title">Events</span>
                    </div>
                    <div class="card-body py-2 px-3" id="dayEvents">
                        <div class="small dashboard-soft-muted">Select a date to see events.</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="modal fade" id="apptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
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
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#appt_date', {
            enableTime: true,
            time_24hr: false,
            dateFormat: 'm/d/Y H:i'
        });
    }

    const donutCtx = document.getElementById('genderDonut');
    if (donutCtx && window.Chart) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [{{ $stats['male'] }}, {{ $stats['female'] }}],
                    backgroundColor: ['#6d66ff', '#ffd36e'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '67%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    const calMonthEl = document.getElementById('calendar');
    if (calMonthEl && window.FullCalendar && FullCalendar.Calendar) {
        const monthCalendar = new FullCalendar.Calendar(calMonthEl, {
            initialView: 'dayGridMonth',
            height: 430,
            dayMaxEventRows: true,
            dayMaxEvents: 4,

            events: async (info, success) => {
                const url = "{{ route('dash.calendarEvents') }}" + `?start=${info.startStr}&end=${info.endStr}`;
                try {
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                    const data = res.ok ? await res.json() : [];
                    success(Array.isArray(data) ? data : []);
                } catch {
                    success([]);
                }
            },

            eventContent(arg) {
                const dot = document.createElement('span');
                dot.className = 'bdot';
                dot.title = arg.event.title || '';
                return { domNodes: [dot] };
            },

            dateClick: async (info) => {
                const box = document.getElementById('dayEvents');
                box.innerHTML = '<div class="small dashboard-soft-muted">Loading...</div>';

                const url = "{{ route('dash.eventsByDay') }}" + "?date=" + encodeURIComponent(info.dateStr);

                try {
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                    if (!res.ok) {
                        box.innerHTML = '<div class="small dashboard-soft-muted">No events.</div>';
                        return;
                    }

                    const items = await res.json();

                    box.innerHTML = (items && items.length)
                        ? items.map(e => `
                            <div class="mb-2">
                                <div class="fw-semibold">${e.title ?? 'Event'}</div>
                                <div class="small dashboard-soft-muted">${e.note ?? ''}</div>
                            </div>
                        `).join('<hr class="my-2">')
                        : '<div class="small dashboard-soft-muted">No events.</div>';
                } catch (err) {
                    box.innerHTML = '<div class="small dashboard-soft-muted">No events.</div>';
                }
            }
        });

        monthCalendar.render();
    }

    const apptCalEl = document.getElementById('apptCalendar');
    if (apptCalEl && window.FullCalendar && FullCalendar.Calendar) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const CSRF = csrfMeta ? csrfMeta.getAttribute('content') : '';

        function toMMDDYYYY_HHMM(date) {
            const d = (date instanceof Date) ? date : new Date(date);
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            const yyyy = d.getFullYear();
            const HH = String(d.getHours()).padStart(2, '0');
            const ii = String(d.getMinutes()).padStart(2, '0');
            return `${mm}/${dd}/${yyyy} ${HH}:${ii}`;
        }

        function fmtDisplay(dt) {
            return toMMDDYYYY_HHMM(dt);
        }

        function openApptModal(s) {
            const modal = document.getElementById('apptModal');
            if (!modal) return;

            document.getElementById('appt_mode').value = s.mode;
            document.getElementById('appt_participantid').value = s.participantid || '';
            document.getElementById('appt_doctor').value = s.doctor_id || '';
            document.getElementById('appt_date').value = s.appdate_display || '';
            document.getElementById('appt_reason').value = s.reason || '';
            document.getElementById('appt_original_pid').value = s.participantid || '';
            document.getElementById('appt_original_dt').value = s.appdate_iso || '';
            document.getElementById('btnApptCancel').style.display = (s.mode === 'edit') ? '' : 'none';

            new bootstrap.Modal(modal).show();
        }

        const apptCalendar = new FullCalendar.Calendar(apptCalEl, {
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
                } catch {
                    success([]);
                }
            },

            eventContent(arg) {
                if (arg.view.type !== 'dayGridMonth') return;
                const p = arg.event.extendedProps || {};
                if ((p.type || 'appointment') !== 'appointment') return;
                const time = p.time || '';
                const doc = (p.doctor || '').replace(/^Dr\.?\s*/, '').trim();
                const di = doc ? doc.split(/\s+/).map(s => s[0]).join('').toUpperCase() : 'DR';
                return { html: `<span class="ap-pill"><span class="ap-time">${time}</span>${di}</span>` };
            },

            dateClick(info) {
                openApptModal({
                    mode: 'create',
                    participantid: '',
                    doctor_id: '',
                    appdate_display: toMMDDYYYY_HHMM(info.date),
                    reason: ''
                });
            },

            eventClick(info) {
                const e = info.event, p = e.extendedProps || {};
                openApptModal({
                    mode: 'edit',
                    participantid: p.participantid,
                    doctor_id: p.doctor_id || '',
                    appdate_iso: p.appdate_iso,
                    appdate_display: fmtDisplay(e.start),
                    reason: p.reason || ''
                });
            },

            eventDrop(info) {
                const p = info.event.extendedProps || {};
                fetch('{{ route('cal.appt.update') }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        mode: 'drag',
                        original_participantid: p.participantid,
                        original_appdate: p.appdate_iso,
                        participantid: p.participantid,
                        appwith: p.doctor_id,
                        reason: p.reason,
                        appdate: info.event.start.toISOString(),
                    })
                }).then(async r => {
                    if (!r.ok) throw new Error(await r.text());
                    return r.json();
                }).then(() => apptCalendar.refetchEvents())
                .catch(() => {
                    info.revert();
                    alert('Move failed');
                });
            }
        });

        apptCalendar.render();

        const headerCard = apptCalEl.closest('.card');
        const viewBtns = headerCard ? headerCard.querySelectorAll('.card-header [data-view]') : [];
        const viewMap = { Month: 'dayGridMonth', Week: 'timeGridWeek', Day: 'timeGridDay', List: 'listWeek' };

        viewBtns.forEach(btn => {
            if (!btn.hasAttribute('type')) btn.setAttribute('type', 'button');
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                let view = btn.getAttribute('data-view');
                view = viewMap[view] || view;
                if (view) {
                    apptCalendar.changeView(view);
                    viewBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                }
            });
        });

        const btnSave = document.getElementById('btnApptSave');
        if (btnSave) {
            btnSave.addEventListener('click', function () {
                const mode = document.getElementById('appt_mode').value;
                const payload = {
                    participantid: document.getElementById('appt_participantid').value,
                    appwith: document.getElementById('appt_doctor').value,
                    reason: document.getElementById('appt_reason').value,
                    appdate: document.getElementById('appt_date').value,
                };

                let url = '{{ route('cal.appt.store') }}';
                let method = 'POST';

                if (mode === 'edit') {
                    url = '{{ route('cal.appt.update') }}';
                    method = 'PUT';
                    payload.original_participantid = document.getElementById('appt_original_pid').value;
                    payload.original_appdate = document.getElementById('appt_original_dt').value;
                    payload.mode = 'modal';
                }

                fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                }).then(async r => {
                    if (!r.ok) throw new Error(await r.text());
                    return r.json();
                }).then(() => {
                    const m = bootstrap.Modal.getInstance(document.getElementById('apptModal'));
                    if (m) m.hide();
                    apptCalendar.refetchEvents();
                }).catch(() => {
                    alert('Save failed');
                });
            });
        }

        const btnCancel = document.getElementById('btnApptCancel');
        if (btnCancel) {
            btnCancel.addEventListener('click', function () {
                const pid = document.getElementById('appt_original_pid').value;
                const iso = document.getElementById('appt_original_dt').value;
                if (!pid || !iso) return;

                fetch('{{ route('cal.appt.cancel') }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ participantid: pid, appdate: iso })
                }).then(async r => {
                    if (!r.ok) throw new Error(await r.text());
                    return r.json();
                }).then(() => {
                    const m = bootstrap.Modal.getInstance(document.getElementById('apptModal'));
                    if (m) m.hide();
                    apptCalendar.refetchEvents();
                }).catch(() => {
                    alert('Cancel failed');
                });
            });
        }
    }
});
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

@endsection
