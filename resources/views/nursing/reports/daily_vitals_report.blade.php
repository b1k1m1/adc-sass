@extends('admin.admin_dashboard')

@section('title', 'Daily Vitals Report')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page title --}}
        <div class="row">
            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <div>
                        <h4 class="mb-0 font-size-18">
                            Daily Vitals Report

                            <span class="text-muted ms-1">
                                — {{ $shiftTitle }}
                            </span>
                        </h4>
                    </div>

                </div>

            </div>
        </div>

        {{-- Warning --}}
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show"
                 role="alert">

                <i class="fas fa-exclamation-triangle me-1"></i>
                {{ session('warning') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>
            </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show"
                 role="alert">

                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9 col-md-11">

                <div class="card shadow-sm">

                    <div class="card-header bg-white">

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">

                            <h5 class="card-title mb-0">
                                {{ $shiftTitle }} Daily Vitals Report
                            </h5>

                            {{-- Shift buttons --}}
                            <div class="d-flex flex-wrap gap-2"
                                 role="group"
                                 aria-label="Member category">

                                <a href="{{ route('nursing.reports.daily-vitals', ['type' => 'A']) }}"
                                   class="btn {{ $type === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">

                                    <i class="fas fa-sun me-1"></i>
                                    Morning
                                </a>

                                <a href="{{ route('nursing.reports.daily-vitals', ['type' => 'N']) }}"
                                   class="btn {{ $type === 'N' ? 'btn-primary' : 'btn-outline-primary' }}">

                                    <i class="fas fa-cloud-sun me-1"></i>
                                    Noon
                                </a>

                                <a href="{{ route('nursing.reports.daily-vitals', ['type' => 'G']) }}"
                                   class="btn {{ $type === 'G' ? 'btn-primary' : 'btn-outline-primary' }}">

                                    <i class="fas fa-user-friends me-1"></i>
                                    Guest
                                </a>

                            </div>

                        </div>

                    </div>

                    <div class="card-body">

                        <form id="dailyVitalsReportForm"
                              method="GET"
                              action="{{ route(
                                  'nursing.reports.daily-vitals.pdf',
                                  ['type' => $type]
                              ) }}"
                              target="_blank">

                            <div class="row">

                                {{-- Report date --}}
                                <div class="col-md-6 mb-3">

                                    <label for="date"
                                           class="form-label fw-semibold">

                                        Report Date
                                        <span class="text-danger">*</span>
                                    </label>

                                    <div class="input-group">

                                        <input type="text"
                                               name="date"
                                               id="date"
                                               class="form-control"
                                               value="{{ old(
                                                    'date',
                                                    request(
                                                        'date',
                                                        now()->format('m/d/Y')
                                                    )
                                               ) }}"
                                               placeholder="MM/DD/YYYY"
                                               maxlength="10"
                                               autocomplete="off"
                                               required>

                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>

                                    </div>

                                    <div class="form-text">
                                        Select a date or enter it manually as MM/DD/YYYY.
                                    </div>

                                </div>

                                {{-- Sort by --}}
                                <div class="col-md-4 mb-3">

                                    <label for="sort_by"
                                           class="form-label fw-semibold">

                                        Sort By
                                    </label>

                                    <select name="sort_by"
                                            id="sort_by"
                                            class="form-select">

                                        <option value="ParticipantId"
                                            {{ request('sort_by', 'ParticipantId') === 'ParticipantId'
                                                ? 'selected'
                                                : '' }}>
                                            Participant ID
                                        </option>

                                        <option value="LastName"
                                            {{ request('sort_by') === 'LastName'
                                                ? 'selected'
                                                : '' }}>
                                            Last Name
                                        </option>

                                        <option value="FirstName"
                                            {{ request('sort_by') === 'FirstName'
                                                ? 'selected'
                                                : '' }}>
                                            First Name
                                        </option>

                                    </select>

                                </div>

                                {{-- Sort order --}}
                                <div class="col-md-2 mb-3">

                                    <label for="sort_order"
                                           class="form-label fw-semibold">

                                        Order
                                    </label>

                                    <select name="sort_order"
                                            id="sort_order"
                                            class="form-select">

                                        <option value="asc"
                                            {{ request('sort_order', 'asc') === 'asc'
                                                ? 'selected'
                                                : '' }}>
                                            ASC
                                        </option>

                                        <option value="desc"
                                            {{ request('sort_order') === 'desc'
                                                ? 'selected'
                                                : '' }}>
                                            DESC
                                        </option>

                                    </select>

                                </div>

                            </div>

                            {{-- Report summary --}}
                            <div id="reportSummary"
                                 class="alert alert-info py-2 mb-3"
                                 style="display:none;">

                                <i class="fas fa-info-circle me-1"></i>

                                Daily Vitals Report for

                                <strong>{{ $shiftTitle }}</strong>

                                on

                                <strong id="selectedDateText"></strong>

                            </div>

                            <div class="d-flex justify-content-end gap-2">

                                <button type="button"
                                        id="resetButton"
                                        class="btn btn-light">

                                    <i class="fas fa-undo me-1"></i>
                                    Reset
                                </button>

                                <button type="submit"
                                        class="btn btn-danger">

                                    <i class="fas fa-file-pdf me-1"></i>
                                    Export to PDF
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

@endsection


@push('scripts')


<script>
    $(document).ready(function () {

        const dateInput = $('#date');

        const datePicker = flatpickr('#date', {
            dateFormat: 'm/d/Y',
            allowInput: true,
            clickOpens: true,
            maxDate: 'today',
            disableMobile: true
        });

        function updateReportSummary() {
            const selectedDate = dateInput.val().trim();

            if (selectedDate) {
                $('#selectedDateText').text(selectedDate);
                $('#reportSummary').show();
            } else {
                $('#reportSummary').hide();
            }
        }

        dateInput.on('change input', updateReportSummary);

        $('#resetButton').on('click', function () {
            datePicker.setDate(
                '{{ now()->format('m/d/Y') }}',
                true
            );

            $('#sort_by').val('ParticipantId');
            $('#sort_order').val('asc');

            updateReportSummary();
        });

        $('#dailyVitalsReportForm').on('submit', function (event) {
            const selectedDate = dateInput.val().trim();

            const datePattern =
                /^(0[1-9]|1[0-2])\/(0[1-9]|[12][0-9]|3[01])\/\d{4}$/;

            if (!selectedDate) {
                event.preventDefault();

                alert('Please select a report date.');
                dateInput.focus();

                return false;
            }

            if (!datePattern.test(selectedDate)) {
                event.preventDefault();

                alert('Please enter the date in MM/DD/YYYY format.');
                dateInput.focus();

                return false;
            }
        });

        updateReportSummary();
    });
</script>
@endpush
