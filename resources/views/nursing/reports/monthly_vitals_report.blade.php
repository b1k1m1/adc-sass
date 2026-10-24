@extends('admin.admin_dashboard')

@section('title', 'Monthly Vitals Report')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <div>
                        <h4 class="mb-0 font-size-18">
                            Monthly Vitals Report
                            <span class="text-muted ms-1">
                                — {{ $typeName }}
                            </span>
                        </h4>
                    </div>

                </div>
            </div>
        </div>

        {{-- Warning message --}}
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
            <div class="col-xl-9 col-lg-10 col-md-12">

                <div class="card shadow-sm">

                    <div class="card-header bg-white">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">

                            <h5 class="card-title mb-0">
                                {{ $typeName }} Monthly Vitals Report
                            </h5>

                            {{-- Member category buttons --}}
                            <div class="d-flex flex-wrap gap-2"
                                 role="group"
                                 aria-label="Member category">

                                <a href="{{ route('nursing.reports.monthly-vitals', ['type' => 'A']) }}"
                                   class="btn {{ $type === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">

                                    <i class="fas fa-sun me-1"></i>
                                    Morning
                                </a>

                                <a href="{{ route('nursing.reports.monthly-vitals', ['type' => 'N']) }}"
                                   class="btn {{ $type === 'N' ? 'btn-primary' : 'btn-outline-primary' }}">

                                    <i class="fas fa-cloud-sun me-1"></i>
                                    Noon
                                </a>

                                <a href="{{ route('nursing.reports.monthly-vitals', ['type' => 'G']) }}"
                                   class="btn {{ $type === 'G' ? 'btn-primary' : 'btn-outline-primary' }}">

                                    <i class="fas fa-user-friends me-1"></i>
                                    Guest
                                </a>

                            </div>

                        </div>
                    </div>

                    <div class="card-body">

                        <form id="monthlyVitalsForm"
                              method="GET"
                              action="{{ route('nursing.reports.monthly-vitals.pdf') }}"
                              target="_blank">

                            {{-- Submit selected category to PDF method --}}
                            <input type="hidden"
                                   name="type"
                                   value="{{ $type }}">

                            <div class="row">

                                {{-- Member --}}
                                <div class="col-md-7 mb-3">

                                    <label for="ParticipantId"
                                           class="form-label fw-semibold">

                                        {{ $typeName }} Member
                                        <span class="text-danger">*</span>
                                    </label>

                                    <select name="ParticipantId"
                                            id="ParticipantId"
                                            class="form-select select2"
                                            required>

                                        <option value="">
                                            Select {{ $typeName }} Member
                                        </option>

                                        @foreach($members as $member)
                                            @php
                                                $rawParticipantId = trim(
                                                    (string) $member->ParticipantId
                                                );

                                                $formattedParticipantId =
                                                    ctype_digit($rawParticipantId)
                                                        ? str_pad(
                                                            $rawParticipantId,
                                                            3,
                                                            '0',
                                                            STR_PAD_LEFT
                                                        )
                                                        : $rawParticipantId;
                                            @endphp

                                            <option
                                                value="{{ $formattedParticipantId }}"
                                                {{ old(
                                                    'ParticipantId',
                                                    request('ParticipantId')
                                                ) == $formattedParticipantId
                                                    ? 'selected'
                                                    : '' }}
                                            >
                                                {{ $formattedParticipantId }}
                                                -
                                                {{ $member->LastName }},
                                                {{ $member->FirstName }}
                                            </option>
                                        @endforeach

                                    </select>

                                    @if($members->isEmpty())
                                        <div class="text-danger small mt-2">
                                            No {{ strtolower($typeName) }} members were found.
                                        </div>
                                    @endif

                                </div>

                                {{-- Month and year --}}
                                <div class="col-md-5 mb-3">

                                    <label for="monthYear"
                                           class="form-label fw-semibold">

                                        Month and Year
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input type="month"
                                           name="monthYear"
                                           id="monthYear"
                                           class="form-control"
                                           value="{{ old(
                                                'monthYear',
                                                request(
                                                    'monthYear',
                                                    now()->format('Y-m')
                                                )
                                           ) }}"
                                           max="{{ now()->format('Y-m') }}"
                                           required>

                                </div>

                            </div>

                            {{-- Selected report details --}}
                            <div id="reportSummary"
                                 class="alert alert-info py-2 mb-3"
                                 style="display:none;">

                                <i class="fas fa-info-circle me-1"></i>

                                <strong>{{ $typeName }}</strong>
                                Monthly Vitals Report for:

                                <strong id="selectedMemberText"></strong>

                                <span class="mx-1">—</span>

                                <strong id="selectedMonthText"></strong>

                            </div>

                            <div class="d-flex justify-content-end gap-2">

                                <button type="button"
                                        id="resetButton"
                                        class="btn btn-light">

                                    <i class="fas fa-undo me-1"></i>
                                    Reset
                                </button>

                                <button type="submit"
                                        class="btn btn-danger"
                                        {{ $members->isEmpty() ? 'disabled' : '' }}>

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

@push('styles')
<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
    }

    .select2-container
    .select2-selection--single
    .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
    }

    .select2-container
    .select2-selection--single
    .select2-selection__arrow {
        height: 36px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function () {

        const memberSelect = $('#ParticipantId');
        const monthInput = $('#monthYear');

        memberSelect.select2({
            placeholder: 'Search by member ID, last name or first name',
            allowClear: true,
            width: '100%'
        });

        function updateReportSummary() {
            const participantId = memberSelect.val();
            const selectedOption = memberSelect.find('option:selected');
            const memberText = selectedOption.text().trim();
            const monthValue = monthInput.val();

            if (participantId && monthValue) {
                const monthDate = new Date(
                    monthValue + '-01T00:00:00'
                );

                const formattedMonth = monthDate.toLocaleDateString(
                    'en-US',
                    {
                        month: 'long',
                        year: 'numeric'
                    }
                );

                $('#selectedMemberText').text(memberText);
                $('#selectedMonthText').text(formattedMonth);
                $('#reportSummary').show();
            } else {
                $('#reportSummary').hide();
            }
        }

        memberSelect.on('change', updateReportSummary);
        monthInput.on('change', updateReportSummary);

        $('#resetButton').on('click', function () {
            memberSelect.val(null).trigger('change');
            monthInput.val('{{ now()->format('Y-m') }}');
            $('#reportSummary').hide();
        });

        $('#monthlyVitalsForm').on('submit', function (event) {
            const participantId = memberSelect.val();
            const monthYear = monthInput.val();

            if (!participantId) {
                event.preventDefault();

                alert('Please select a {{ strtolower($typeName) }} member.');
                memberSelect.select2('open');

                return false;
            }

            if (!monthYear) {
                event.preventDefault();

                alert('Please select a month and year.');
                monthInput.focus();

                return false;
            }
        });

        updateReportSummary();
    });
</script>
@endpush
