@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Daily Vitals Entry</h4>

                    <div class="page-title-right">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
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
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="GET"
                      action="{{ route('nursing.vitals.entry') }}"
                      class="row g-2 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label">Select Date</label>

                        <div class="input-group">
                            <input type="text"
                                   name="entry_date"
                                   id="entry_date"
                                   class="form-control"
                                   placeholder="MM/DD/YYYY"
                                   value="{{ $selectedDateInput }}"
                                   autocomplete="off"
                                   maxlength="10"
                                   required>

                            <span class="input-group-text" id="calendar_icon" style="cursor:pointer;">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                        </div>

                        <small class="text-muted">Example: 05/06/2026</small>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Load
                        </button>

                        <a href="{{ route('nursing.vitals.entry') }}" class="btn btn-light">
                            Clear
                        </a>
                    </div>

                </form>
            </div>
        </div>

        {{-- Vitals Table --}}
        @if($selectedDateInput)

            <form method="POST"
                  action="{{ route('nursing.vitals.entry.save') }}"
                  id="vitalsForm">
                @csrf

                <input type="hidden" name="entry_date" value="{{ $selectedDateInput }}">

                <div class="card shadow-sm">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Vitals for {{ $selectedDateInput }}</strong>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-save"></i> Save Vitals
                            </button>
                        </div>
                    </div>

                    <div class="card-body table-responsive">

                        <table id="vitalsTable"
                               class="table table-bordered table-striped table-sm align-middle nowrap"
                               style="width:100%;">

                            {{--
                                IMPORTANT:
                                Single header row is used here.
                                This fixes DataTables sorting issue.
                            --}}
                            <thead class="table-light">
                                <tr>
                                    <th>ParticipantId</th>
                                    <th>LastName</th>
                                    <th>FirstName</th>
                                    <th>BP Max/SYS</th>
                                    <th>BP Min/DIA</th>
                                    <th>Sugar</th>
                                    <th>Pulse</th>
                                    <th>Weight</th>
                                    <th>Temp (&deg;F)</th>
                                    <th>Toilet</th>
                                    <th>Transfer</th>
                                    <th>Locomotion</th>
                                    <th>Eating</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($members as $member)
                                    @php
                                        $pid = trim($member->ParticipantId);
                                        $displayPid = str_pad($pid, 3, '0', STR_PAD_LEFT);
                                        $v = $existingVitals[$pid] ?? null;
                                    @endphp

                                    <tr>
                                        <td data-order="{{ (int) $pid }}">{{ $displayPid }}</td>
                                        <td>{{ $member->LastName }}</td>
                                        <td>{{ $member->FirstName }}</td>

                                        {{-- BP Max/SYS = HighBP --}}
                                        <td>
                                            <input type="text"
                                                   name="vitals[{{ $pid }}][HighBP]"
                                                   class="form-control form-control-sm vital-input"
                                                   maxlength="5"
                                                   value="{{ $v->HighBP ?? '' }}">
                                        </td>

                                        {{-- BP Min/DIA = LowBP --}}
                                        <td>
                                            <input type="text"
                                                   name="vitals[{{ $pid }}][LowBP]"
                                                   class="form-control form-control-sm vital-input"
                                                   maxlength="5"
                                                   value="{{ $v->LowBP ?? '' }}">
                                        </td>

                                        <td>
                                            <input type="text"
                                                   name="vitals[{{ $pid }}][Sugar]"
                                                   class="form-control form-control-sm vital-input"
                                                   maxlength="5"
                                                   value="{{ $v->Sugar ?? '' }}">
                                        </td>

                                        <td>
                                            <input type="text"
                                                   name="vitals[{{ $pid }}][Pulse]"
                                                   class="form-control form-control-sm vital-input"
                                                   maxlength="5"
                                                   value="{{ $v->Pulse ?? '' }}">
                                        </td>

                                        <td>
                                            <input type="text"
                                                   name="vitals[{{ $pid }}][Weight]"
                                                   class="form-control form-control-sm vital-input"
                                                   maxlength="5"
                                                   value="{{ $v->Weight ?? '' }}">
                                        </td>

                                        <td>
                                            <input type="text"
                                                   name="vitals[{{ $pid }}][Temperature]"
                                                   class="form-control form-control-sm vital-input"
                                                   maxlength="5"
                                                   value="{{ $v->Temperature ?? '' }}">
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="vitals[{{ $pid }}][ToiletYN]"
                                                   value="Y"
                                                   {{ optional($v)->ToiletYN === 'Y' ? 'checked' : '' }}>
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="vitals[{{ $pid }}][TransferYN]"
                                                   value="Y"
                                                   {{ optional($v)->TransferYN === 'Y' ? 'checked' : '' }}>
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="vitals[{{ $pid }}][LocomotionYN]"
                                                   value="Y"
                                                   {{ optional($v)->LocomotionYN === 'Y' ? 'checked' : '' }}>
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="vitals[{{ $pid }}][EatingYN]"
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
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Vitals
                        </button>
                    </div>

                </div>
            </form>

        @endif

    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">

<style>
    #vitalsTable th,
    #vitalsTable td {
        vertical-align: middle;
        white-space: nowrap;
    }

    #vitalsTable thead th {
        text-align: center;
    }

    .vital-input {
        width: 95px;
        text-align: center;
    }

    #vitalsTable input[type="checkbox"] {
        width: 22px;
        height: 22px;
        cursor: pointer;
    }

    div.dataTables_wrapper div.dataTables_filter {
        text-align: right;
    }

    div.dataTables_wrapper div.dataTables_length {
        text-align: left;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>

<script>
$(document).ready(function () {

    /*
     * Datepicker.
     */
    if ($.fn.datepicker) {
        $('#entry_date').datepicker({
            format: 'mm/dd/yyyy',
            autoclose: true,
            todayHighlight: true,
            endDate: new Date()
        });

        $('#calendar_icon').on('click', function () {
            $('#entry_date').datepicker('show');
        });
    }

    /*
     * Manual date formatter.
     * 05062026 becomes 05/06/2026.
     */
    $('#entry_date').on('input', function () {
        let value = $(this).val().replace(/\D/g, '');

        if (value.length > 8) {
            value = value.substring(0, 8);
        }

        if (value.length >= 5) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4) + '/' + value.substring(4);
        } else if (value.length >= 3) {
            value = value.substring(0, 2) + '/' + value.substring(2);
        }

        $(this).val(value);
    });

    /*
     * DataTable.
     * Only first 3 columns are sortable/searchable.
     */
    if ($('#vitalsTable').length && $.fn.DataTable) {

        if ($.fn.DataTable.isDataTable('#vitalsTable')) {
            $('#vitalsTable').DataTable().destroy();
        }

        $('#vitalsTable').DataTable({
            pageLength: 25,
            lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
            scrollX: true,
            ordering: true,
            searching: true,
            autoWidth: false,
            columnDefs: [
                {
                    targets: [0, 1, 2],
                    orderable: true,
                    searchable: true
                },
                {
                    targets: [3,4,5,6,7,8,9,10,11,12],
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[0, 'asc']]
        });
    }

    $('#vitalsForm').on('submit', function () {
        return confirm('Do you want to save vitals for selected date?');
    });

});
</script>
@endpush
