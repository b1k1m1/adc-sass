@extends('admin.admin_dashboard')
@section('title', 'Member Attendance Crosstab Report')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Member Attendance Crosstab Report</h4>
            </div>
            <div>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('attendance.memberrange') }}" id="filterForm">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-5">
                            <label class="form-label">Member</label>
                            <select name="ParticipantId" id="ParticipantId" class="form-control">
                                @if(!empty($member))
                                    <option value="{{ $member->ParticipantId }}" selected>
                                        {{ $member->ParticipantId }} - {{ $member->LastName }}, {{ $member->FirstName }}
                                    </option>
                                @endif
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Start Year-Month</label>
                            <input type="month"
                                   name="start_month"
                                   id="start_month"
                                   class="form-control"
                                   value="{{ old('start_month', $startYearMonth ?? '') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">End Year-Month</label>
                            <input type="month"
                                   name="end_month"
                                   id="end_month"
                                   class="form-control"
                                   value="{{ old('end_month', $endYearMonth ?? '') }}">
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" id="btnLoad" class="btn btn-primary" disabled>Load</button>

                            @if($validated && !empty($member))
                                <a href="{{ route('attendance.memberrange.pdf', request()->query()) }}"
                                   target="_blank"
                                   class="btn btn-outline-danger">
                                    PDF
                                </a>

                                <a href="{{ route('attendance.memberrange.excel', request()->query()) }}"
                                   class="btn btn-outline-success">
                                    Excel
                                </a>
                            @endif
                        </div>

                    </div>
                </form>
            </div>
        </div>

        @if($validated && !empty($member))
            <div class="card mt-3">
                <div class="card-body">

                    <div class="text-center mb-3">
                        <h5 class="mb-1">{{ $company->comp_nm ?? 'Company Name' }}</h5>
                        <div>{{ config('company.company_address', '') }}</div>
                        <div>{{ config('company.company_city_state_zip', '') }}</div>
                        <div>{{ config('company.company_phone', '') }}</div>
                        <h6 class="mt-3 mb-1">Member Attendance Crosstab Report</h6>
                        <div><strong>Member:</strong> {{ $member->ParticipantId }} - {{ $member->LastName }}, {{ $member->FirstName }}</div>
                        <div><strong>Range:</strong> {{ $startYearMonth }} to {{ $endYearMonth }}</div>
                        <div><strong>Grand Total:</strong> {{ $grandTotal }}</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 100px;">Month</th>
                                    @for($day = 1; $day <= 31; $day++)
                                        <th>{{ $day }}</th>
                                    @endfor
                                    <th style="min-width: 70px;" class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($months as $month)
                                    <tr>
                                        <th class="text-start">{{ $month['month_name'] }}</th>
                                        @for($day = 1; $day <= 31; $day++)
                                            <td>{{ $month['days'][$day] }}</td>
                                        @endfor
                                        <th class="text-end">{{ $month['row_total'] }}</th>
                                    </tr>
                                @endforeach
                                <tr class="table-secondary">
                                    <th class="text-start">Total</th>
                                    @for($day = 1; $day <= 31; $day++)
                                        <th>{{ $dayTotals[$day] }}</th>
                                    @endfor
                                    <th class="text-end">{{ $grandTotal }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    function validateForm() {
        const member = $('#ParticipantId').val();
        const startMonth = $('#start_month').val();
        const endMonth = $('#end_month').val();

        let valid = !!member && !!startMonth && !!endMonth;

        if (valid && endMonth < startMonth) {
            valid = false;
        }

        $('#btnLoad').prop('disabled', !valid);
    }

    $('#ParticipantId').select2({
        placeholder: 'Select member',
        allowClear: true,
        width: '100%',
        ajax: {
            url: "{{ route('attendance.memberrange.member-search') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: data.results || []
                };
            },
            cache: true
        }
    }).on('change', validateForm);

    $('#start_month, #end_month').on('change keyup', validateForm);

    validateForm();
});
</script>

@endsection
