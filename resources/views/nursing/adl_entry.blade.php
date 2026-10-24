@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

    @php
    $type = isset($type) && in_array(strtoupper($type), ['A', 'N', 'G']) ? strtoupper($type) : 'A';

    $shiftTitle = match ($type) {
            'A' => 'Morning Shift',
            'N' => 'Noon Shift',
            'G' => 'Guest',
            default => 'Morning Shift',
        };
    @endphp


        {{-- Page Header --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-sm-0">
                            Daily ADL Entry - {{ $shiftTitle }}
                        </h4>
                        <small class="text-muted">
                            Select shift and date to load daily ADL entry screen.
                        </small>
                    </div>

                    <div class="page-title-right">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Please correct the following:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Date Load Card --}}
        <div class="card shadow-sm mb-2">
            <div class="card-body py-3">

                <form method="GET"
                    action="{{ route('nursing.adl.entry', ['type' => $type]) }}"
                    class="d-flex align-items-end flex-wrap gap-3">

                    {{-- Shift Buttons --}}
                    <div>
                        <label class="form-label mb-1 d-block">Select Shift</label>

                        <div class="d-flex gap-2" role="group">
                            <a href="{{ route('nursing.adl.entry', ['type' => 'A']) }}"
                            class="btn {{ $type === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
                                <i class="fas fa-sun"></i> Morning Shift
                            </a>

                            <a href="{{ route('nursing.adl.entry', ['type' => 'N']) }}"
                            class="btn {{ $type === 'N' ? 'btn-primary' : 'btn-outline-primary' }}">
                                <i class="fas fa-clock"></i> Noon Shift
                            </a>

                            <a href="{{ route('nursing.adl.entry', ['type' => 'G']) }}"
                            class="btn {{ $type === 'G' ? 'btn-warning' : 'btn-outline-warning' }}">
                                <i class="fas fa-user"></i> Guest
                            </a>
                    </div>
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="form-label mb-1 d-block">Select Date</label>
                        <div class="input-group" style="width: 260px;">
                            <input type="text"
                                name="entry_date"
                                id="entry_date"
                                class="form-control"
                                placeholder="MM/DD/YYYY"
                                value="{{ $selectedDateInput ?? '' }}"
                                autocomplete="off"
                                maxlength="10"
                                required>

                            <span class="input-group-text" id="calendar_icon" style="cursor:pointer;">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-search"></i> Load
                        </button>

                        <a href="{{ route('nursing.adl.entry', ['type' => $type]) }}"
                           class="btn btn-outline-secondary px-4">
                            Clear
                        </a>
                    </div>

                    {{-- Current Shift --}}
                    <div class="ms-auto mb-2 text-muted">
                        Current Shift:
                        <strong>{{ $shiftTitle }}</strong>
                    </div>

                </form>

            </div>
        </div>

        {{-- ADL Table --}}
        @if($selectedDateInput)
            <form method="POST" action="{{ route('nursing.adl.entry.save') }}" id="adlForm">
                @csrf

                <input type="hidden" name="entry_date" value="{{ $selectedDateInput }}">
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        @php
                            $enteredBy = trim((Auth::user()->lastname ?? '') . ', ' . (Auth::user()->firstname ?? ''));

                            if ($enteredBy === ',') {
                                $enteredBy = Auth::user()->name ?? Auth::user()->user_cd ?? 'Nurse';
                            }
                        @endphp

                        <div>
                            <strong>{{ $shiftTitle }} ADL for {{ $selectedDateInput }}</strong>

                            <span class="ms-3 text-muted">
                                Entered By:
                                <strong>{{ $enteredBy }}</strong>
                            </span>

                            <span class="ms-3 text-muted small" id="autoRefreshStatus">
                                Auto refresh: active
                            </span>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-success save-adl-btn">
                                <i class="fas fa-save"></i> Save ADL
                            </button>

                            @if($selectedDateInput && $type !== 'G')
                                <a href="{{ route('nursing.adl.pdf', ['type' => $type, 'date' => $selectedDateInput, 'sort_by' => 'ParticipantId', 'sort_order' => 'asc']) }}"
                                   id="pdfExportLink"
                                   target="_blank"
                                   class="btn btn-danger ms-2">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <table id="adlTable"
                               class="table table-bordered table-striped table-sm align-middle nowrap"
                               style="width:100%;">

                            <thead class="table-light">
                                <tr>
                                    <th rowspan="2">ParticipantId</th>
                                    <th rowspan="2">LastName</th>
                                    <th rowspan="2">FirstName</th>

                                    <th colspan="5" class="text-center bg-info text-white">
                                        Default ADL Needs
                                    </th>

                                    <th colspan="5" class="text-center bg-success text-white">
                                        Today Completed
                                    </th>
                                </tr>

                                <tr>
                                    {{-- Default ADL from members table --}}
                                    <th>{{ $adlBathingLabel }}</th>
                                    <th>Toilet</th>
                                    <th>Transfer</th>
                                    <th>Ambulation</th>
                                    <th>Eating</th>

                                    {{-- Daily ADL entry from partbp table --}}
                                    <th>{{ $adlBathingLabel }}</th>
                                    <th>Toilet</th>
                                    <th>Transfer</th>
                                    <th>Ambulation</th>
                                    <th>Eating</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($members as $member)
                                    @php
                                        $pid = trim($member->ParticipantId);
                                        $displayPid = str_pad($pid, 3, '0', STR_PAD_LEFT);
                                        $v = $existingVitals[$pid] ?? null;

                                        $tooltipText = '';
                                        if (!empty($v->update_dt)) {
                                            $tooltipText = 'Last updated ' . \Carbon\Carbon::parse($v->update_dt)->format('m/d/Y h:i A');
                                            if (!empty($v->user_cd)) {
                                                $tooltipText .= ' by ' . $v->user_cd;
                                            }
                                        }
                                    @endphp

                                    <tr data-participant-id="{{ $pid }}" title="{{ $tooltipText }}">
                                        <td data-order="{{ (int) $pid }}">
                                            <input type="hidden"
                                                name="adl[{{ $pid }}][original_update_dt]"
                                                value="{{ $v->update_dt ?? '' }}"
                                                class="original-update-dt">

                                            {{ $displayPid }}
                                        </td>

                                        <td>{{ $member->LastName }}</td>
                                        <td>{{ $member->FirstName }}</td>

                                        {{-- Default ADL Needs from members table - read only --}}
                                        <td class="text-center default-adl-cell">
                                            @if(($member->bathing_yn ?? 'N') === 'Y')
                                                <span class="badge bg-primary">Y</span>
                                            @else
                                                <span class="badge bg-light text-muted border">N</span>
                                            @endif
                                        </td>

                                        <td class="text-center default-adl-cell">
                                            @if(($member->toilet_yn ?? 'N') === 'Y')
                                                <span class="badge bg-primary">Y</span>
                                            @else
                                                <span class="badge bg-light text-muted border">N</span>
                                            @endif
                                        </td>

                                        <td class="text-center default-adl-cell">
                                            @if(($member->transfer_yn ?? 'N') === 'Y')
                                                <span class="badge bg-primary">Y</span>
                                            @else
                                                <span class="badge bg-light text-muted border">N</span>
                                            @endif
                                        </td>

                                        <td class="text-center default-adl-cell">
                                            @if(($member->locomotion_yn ?? 'N') === 'Y')
                                                <span class="badge bg-primary">Y</span>
                                            @else
                                                <span class="badge bg-light text-muted border">N</span>
                                            @endif
                                        </td>

                                        <td class="text-center default-adl-cell">
                                            @if(($member->eating_yn ?? 'N') === 'Y')
                                                <span class="badge bg-primary">Y</span>
                                            @else
                                                <span class="badge bg-light text-muted border">N</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="adl[{{ $pid }}][BathingYN]"
                                                   value="Y"
                                                   {{ optional($v)->BathingYN === 'Y' ? 'checked' : '' }}>
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="adl[{{ $pid }}][ToiletYN]"
                                                   value="Y"
                                                   {{ optional($v)->ToiletYN === 'Y' ? 'checked' : '' }}>
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="adl[{{ $pid }}][TransferYN]"
                                                   value="Y"
                                                   {{ optional($v)->TransferYN === 'Y' ? 'checked' : '' }}>
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="adl[{{ $pid }}][LocomotionYN]"
                                                   value="Y"
                                                   {{ optional($v)->LocomotionYN === 'Y' ? 'checked' : '' }}>
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="adl[{{ $pid }}][EatingYN]"
                                                   value="Y"
                                                   {{ optional($v)->EatingYN === 'Y' ? 'checked' : '' }}>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center text-muted">
                                            No members found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-success save-adl-btn">
                            <i class="fas fa-save"></i> Save ADL
                        </button>
                    </div>
                </div>
            </form>
        @endif

    </div>
</div>

@endsection

@push('styles')
<style>
    #adlTable th,
    #adlTable td {
        vertical-align: middle;
        white-space: nowrap;
    }

    #adlTable thead th {
        text-align: center;
        background: #f8f9fa;
    }

    #adlTable input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    div.dataTables_scrollBody {
        border-bottom: 1px solid #dee2e6;
    }

    #adlTable tbody tr.row-refreshed td {
        background-color: #fff3cd !important;
        transition: background-color 1.5s ease;
    }

    #adlTable tbody tr.row-editing td {
        background-color: #e7f1ff !important;
    }

    #autoRefreshStatus {
        font-size: 12px;
    }

    #adlTable .default-adl-cell {
        background-color: #f8fbff;
        font-weight: 600;
    }

    #adlTable .badge {
        font-size: 12px;
        min-width: 28px;
        padding: 5px 7px;
    }

    #adlTable thead tr:first-child th {
        vertical-align: middle;
    }

    #adlTable th,
    #adlTable td {
        vertical-align: middle;
        white-space: nowrap;
    }

    #adlTable thead th,
    table.dataTable thead th {
        text-align: center !important;
        vertical-align: middle !important;
        background: #f8f9fa;
    }

    #adlTable tbody td {
        text-align: center;
        vertical-align: middle;
    }

    #adlTable tbody td:nth-child(2),
    #adlTable tbody td:nth-child(3) {
        text-align: left;
    }

    #adlTable input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    #adlTable .default-adl-cell {
        background-color: #f8fbff;
        font-weight: 600;
    }

    #adlTable .badge {
        font-size: 12px;
        min-width: 28px;
        padding: 5px 7px;
    }
</style>
@endpush

@push('scripts')
<script>
    var currentSortBy = 'ParticipantId';
    var currentSortOrder = 'asc';

    window.addEventListener('load', function() {
        console.log('=== ADL ENTRY PAGE LOADED ===');

        // ==========================================
        // 1. FLATPICKR DATEPICKER
        // ==========================================
        (function() {
            var entryDateEl = document.getElementById('entry_date');

            if (!entryDateEl || typeof flatpickr !== 'function') {
                return;
            }

            var fp = flatpickr(entryDateEl, {
                dateFormat: 'm/d/Y',
                allowInput: true,
                maxDate: 'today',
                defaultDate: '{{ $selectedDateInput }}',
                disableMobile: true
            });

            var calendarIcon = document.getElementById('calendar_icon');

            if (calendarIcon) {
                calendarIcon.addEventListener('click', function(e) {
                    e.preventDefault();
                    fp.open();
                });
            }

            entryDateEl.addEventListener('focus', function() {
                fp.open();
            });
        })();

        // ==========================================
        // 2. DATE FORMATTING 05062026 -> 05/06/2026
        // ==========================================
        (function() {
            var entryDateEl = document.getElementById('entry_date');

            if (!entryDateEl) {
                return;
            }

            entryDateEl.addEventListener('input', function() {
                var value = this.value.replace(/\D/g, '');

                if (value.length > 8) {
                    value = value.substring(0, 8);
                }

                if (value.length >= 5) {
                    this.value = value.substring(0, 2) + '/' + value.substring(2, 4) + '/' + value.substring(4);
                } else if (value.length >= 3) {
                    this.value = value.substring(0, 2) + '/' + value.substring(2);
                } else {
                    this.value = value;
                }
            });
        })();

        // ==========================================
        // 3. DATATABLES + SORT TRACKING
        // ==========================================
        (function() {
            if (typeof $.fn.DataTable !== 'function') {
                console.error('DataTables not loaded');
                return;
            }

            if (!document.getElementById('adlTable')) {
                return;
            }

            if ($.fn.DataTable.isDataTable('#adlTable')) {
                $('#adlTable').DataTable().destroy();
            }

            var table = $('#adlTable').DataTable({
                paging: false,
                scrollY: '60vh',
                scrollX: true,
                scrollCollapse: true,
                ordering: true,
                searching: true,
                autoWidth: false,

                columnDefs: [
                    { targets: [0, 1, 2], orderable: true, searchable: true },
                    { targets: [3, 4, 5, 6, 7], orderable: false, searchable: false },
                    { targets: [8, 9, 10, 11, 12], orderable: false, searchable: false }
                ],

                order: [[0, 'asc']],

                language: {
                    search: "Search:",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "No entries found",
                    zeroRecords: "No matching records found"
                }
            });

            table.on('order.dt', function() {
                var order = table.order();

                if (order && order.length > 0) {
                    var colIndex = parseInt(order[0][0]);
                    var dir = order[0][1];

                    if (colIndex === 0) {
                        currentSortBy = 'ParticipantId';
                    } else if (colIndex === 1) {
                        currentSortBy = 'LastName';
                    } else if (colIndex === 2) {
                        currentSortBy = 'FirstName';
                    }

                    currentSortOrder = dir;
                }
            });

            console.log('ADL DataTable initialized without pagination');
        })();

        // ==========================================
        // 4. PDF EXPORT - USE TRACKED SORT + SHIFT TYPE
        // ==========================================
        (function() {
            var pdfLink = document.getElementById('pdfExportLink');

            if (!pdfLink) {
                return;
            }

            pdfLink.addEventListener('click', function(e) {
                e.preventDefault();

                var dateInput = document.getElementById('entry_date');
                var date = dateInput ? dateInput.value : '';

                if (!date) {
                    alert('Please select a date first.');
                    return;
                }

                try {
                    var dt = $('#adlTable').DataTable();
                    var order = dt.order();

                    if (order && order.length > 0) {
                        var colIndex = parseInt(order[0][0]);
                        var dir = order[0][1];

                        if (colIndex === 0) {
                            currentSortBy = 'ParticipantId';
                        } else if (colIndex === 1) {
                            currentSortBy = 'LastName';
                        } else if (colIndex === 2) {
                            currentSortBy = 'FirstName';
                        }

                        currentSortOrder = dir;
                    }
                } catch(ex) {}

                var url = '{{ route("nursing.adl.pdf", ["type" => $type]) }}'
                        + '?date=' + encodeURIComponent(date)
                        + '&sort_by=' + encodeURIComponent(currentSortBy)
                        + '&sort_order=' + encodeURIComponent(currentSortOrder);

                window.open(url, '_blank', 'noopener');
            });
        })();

        // ==========================================
        // 5. FORM SUBMISSION - DOUBLE SUBMIT PROTECTION
        // ==========================================
        (function() {
            var form = document.getElementById('adlForm');

            if (!form) {
                return;
            }

            form.addEventListener('submit', function() {
                var buttons = form.querySelectorAll('button[type="submit"]');

                buttons.forEach(function(btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                });

                return true;
            });
        })();

        // ==========================================
        // 6. AUTO REFRESH ADL ONLY
        // ==========================================
        (function() {
            var form = document.getElementById('adlForm');
            var entryDateInput = document.querySelector('input[name="entry_date"]');
            var autoRefreshStatus = document.getElementById('autoRefreshStatus');

            if (!form || !entryDateInput || !document.getElementById('adlTable')) {
                return;
            }

            var latestUrl = '{{ route("nursing.adl.entry.latest") }}';
            var refreshIntervalMs = 5000;
            var editingRows = {};
            var lastKnownRowState = {};

            function setStatus(text) {
                if (autoRefreshStatus) {
                    autoRefreshStatus.textContent = text;
                }
            }

            function getRowState(row) {
                var state = {};

                row.querySelectorAll('input').forEach(function(input) {
                    var name = input.getAttribute('name') || '';

                    if (!name) {
                        return;
                    }

                    if (input.type === 'checkbox') {
                        state[name] = input.checked ? 'Y' : 'N';
                    } else {
                        state[name] = input.value || '';
                    }
                });

                return JSON.stringify(state);
            }

            function markEditing(row) {
                var pid = row.getAttribute('data-participant-id');

                if (!pid) {
                    return;
                }

                editingRows[pid] = Date.now();
                row.classList.add('row-editing');
            }

            function clearOldEditingFlags() {
                var now = Date.now();

                Object.keys(editingRows).forEach(function(pid) {
                    if (now - editingRows[pid] > 15000) {
                        delete editingRows[pid];

                        var row = document.querySelector('tr[data-participant-id="' + pid + '"]');
                        if (row) {
                            row.classList.remove('row-editing');
                        }
                    }
                });
            }

            function setCheckboxValue(row, fieldName, value) {
                var pid = row.getAttribute('data-participant-id');
                var input = row.querySelector('[name="adl[' + pid + '][' + fieldName + ']"]');

                if (input) {
                    input.checked = (value === 'Y');
                }
            }

            function setOriginalUpdateDt(row, value) {
                var input = row.querySelector('.original-update-dt');

                if (input) {
                    input.value = value || '';
                }
            }

            function formatDateTime(value) {
                if (!value) {
                    return '-';
                }

                var d = new Date(String(value).replace(' ', 'T'));

                if (isNaN(d.getTime())) {
                    return value;
                }

                var month = String(d.getMonth() + 1).padStart(2, '0');
                var day = String(d.getDate()).padStart(2, '0');
                var year = d.getFullYear();

                var hours = d.getHours();
                var minutes = String(d.getMinutes()).padStart(2, '0');
                var ampm = hours >= 12 ? 'PM' : 'AM';

                hours = hours % 12;
                hours = hours ? hours : 12;

                return month + '/' + day + '/' + year + ' ' + String(hours).padStart(2, '0') + ':' + minutes + ' ' + ampm;
            }

            function updateRowTooltip(row, record) {
                var tooltipText = '';

                if (record.update_dt) {
                    tooltipText = 'Last updated ' + formatDateTime(record.update_dt);

                    if (record.user_cd) {
                        tooltipText += ' by ' + record.user_cd;
                    }
                }

                row.setAttribute('title', tooltipText);
            }

            function updateRowFromRecord(pid, record) {
                var row = document.querySelector('tr[data-participant-id="' + pid + '"]');

                if (!row) {
                    return;
                }

                if (editingRows[pid]) {
                    return;
                }

                var beforeState = getRowState(row);

                setCheckboxValue(row, 'BathingYN', record.BathingYN);
                setCheckboxValue(row, 'ToiletYN', record.ToiletYN);
                setCheckboxValue(row, 'TransferYN', record.TransferYN);
                setCheckboxValue(row, 'LocomotionYN', record.LocomotionYN);
                setCheckboxValue(row, 'EatingYN', record.EatingYN);

                setOriginalUpdateDt(row, record.update_dt);

                updateRowTooltip(row, record);

                var afterState = getRowState(row);

                if (beforeState !== afterState && lastKnownRowState[pid] !== afterState) {
                    row.classList.add('row-refreshed');

                    setTimeout(function() {
                        row.classList.remove('row-refreshed');
                    }, 2500);
                }

                lastKnownRowState[pid] = afterState;
            }

            function refreshAdlFromServer() {
                clearOldEditingFlags();

                var entryDate = entryDateInput.value;

                if (!entryDate) {
                    return;
                }

                fetch(latestUrl + '?entry_date=' + encodeURIComponent(entryDate) + '&type={{ $type }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (!data.success || !data.records) {
                        setStatus('Auto refresh: waiting');
                        return;
                    }

                    Object.keys(data.records).forEach(function(pid) {
                        updateRowFromRecord(pid, data.records[pid]);
                    });

                    setStatus('Auto refresh: active');
                })
                .catch(function(error) {
                    console.log('ADL auto-refresh skipped:', error);
                    setStatus('Auto refresh: retrying');
                });
            }

            form.querySelectorAll('input').forEach(function(input) {
                input.addEventListener('input', function() {
                    var row = input.closest('tr[data-participant-id]');
                    if (row) {
                        markEditing(row);
                    }
                });

                input.addEventListener('change', function() {
                    var row = input.closest('tr[data-participant-id]');
                    if (row) {
                        markEditing(row);
                    }
                });

                input.addEventListener('blur', function() {
                    var row = input.closest('tr[data-participant-id]');

                    if (!row) {
                        return;
                    }

                    var pid = row.getAttribute('data-participant-id');

                    setTimeout(function() {
                        delete editingRows[pid];
                        row.classList.remove('row-editing');
                    }, 5000);
                });
            });

            document.querySelectorAll('tr[data-participant-id]').forEach(function(row) {
                var pid = row.getAttribute('data-participant-id');
                lastKnownRowState[pid] = getRowState(row);
            });

            setInterval(refreshAdlFromServer, refreshIntervalMs);

            console.log('ADL auto-refresh enabled every 5 seconds');
        })();

        console.log('=== ADL ENTRY PAGE READY ===');
    });
</script>
@endpush
