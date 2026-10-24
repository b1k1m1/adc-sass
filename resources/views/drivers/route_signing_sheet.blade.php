@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

    <style>
    #route_ids + .select2-container {
        width: 100% !important;
    }

    #route_ids + .select2-container
    .select2-selection--multiple {
        min-height: 38px;
        padding: 2px 6px;
        border: 1px solid #ced4da;
    }

    #route_ids + .select2-container
    .select2-selection__rendered {
        margin: 0;
    }

    #route_ids + .select2-container
    .select2-search__field {
        margin-top: 4px;
    }
</style>

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Route Signing Sheet
                    </h4>

                    <div class="page-title-right">

                        <a href="{{ route('drivers.dashboard') }}#profile1"
                           class="btn btn-primary">

                            {{ t('common.back', 'Back') }}

                        </a>

                    </div>

                </div>

            </div>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())

            <div class="alert alert-danger alert-dismissible fade show"
                 role="alert">

                <strong>Please correct the following:</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>

            </div>

        @endif

        {{-- Session Message --}}
        @if (session('message'))

            <div class="alert alert-{{ session('alert-type') === 'error'
                    ? 'danger'
                    : 'success' }}
                        alert-dismissible fade show"
                 role="alert">

                {{ session('message') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>

            </div>

        @endif

        {{-- Selection Form --}}
        <div class="card mb-3">

            <div class="card-header">

                <h4 class="card-title mb-0">
                    Select Route(s)
                </h4>

            </div>

            <div class="card-body">

                <form method="GET"
                    action="{{ route('route.signing.sheet') }}"
                    autocomplete="off">

                    <div class="row align-items-end g-3">

                        {{-- Signing Sheet Date --}}
                        <div class="col-lg-3 col-md-6">

                            <label for="report_date"
                                class="form-label">

                                Signing Sheet Date
                                <span class="text-danger">*</span>

                            </label>

                            <input type="date"
                                name="report_date"
                                id="report_date"
                                class="form-control"
                                value="{{ old('report_date', $selectedDate) }}"
                                required>

                        </div>

                        {{-- Route Selection --}}
                        <div class="col-lg-5 col-md-6">

                            <label for="route_ids"
                                class="form-label">

                                Route(s)
                                <span class="text-danger">*</span>

                            </label>

                            <select name="route_ids[]"
                                    id="route_ids"
                                    class="form-select"
                                    multiple
                                    required>

                                <optgroup label="Morning Routes">

                                    @foreach ($routes->where('shift_code', 'A') as $route)

                                        <option value="{{ $route->Route_id }}"
                                            {{ in_array(
                                                $route->Route_id,
                                                old('route_ids', $selectedRouteIds),
                                                true
                                            ) ? 'selected' : '' }}>

                                            {{ $route->Route_id }}
                                            -
                                            {{ $route->Route_name }}

                                        </option>

                                    @endforeach

                                </optgroup>

                                <optgroup label="Noon Routes">

                                    @foreach ($routes->where('shift_code', 'N') as $route)

                                        <option value="{{ $route->Route_id }}"
                                            {{ in_array(
                                                $route->Route_id,
                                                old('route_ids', $selectedRouteIds),
                                                true
                                            ) ? 'selected' : '' }}>

                                            {{ $route->Route_id }}
                                            -
                                            {{ $route->Route_name }}

                                        </option>

                                    @endforeach

                                </optgroup>

                            </select>

                        </div>

                        {{-- View Button --}}
                        <div class="col-lg-2 col-md-6">

                            <button type="submit"
                                    class="btn btn-info waves-effect waves-light w-100">

                                <i class="mdi mdi-eye me-1"></i>
                                View Signing Sheets

                            </button>

                        </div>

                        {{-- Export PDF Button --}}
                        <div class="col-lg-2 col-md-6">

                            @if ($pdfUrl)

                                <a href="{{ $pdfUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-secondary waves-effect waves-light w-100">

                                    <i class="mdi mdi-file-pdf-box me-1"></i>
                                    Export PDF

                                </a>

                            @else

                                <button type="button"
                                        class="btn btn-secondary w-100"
                                        disabled>

                                    <i class="mdi mdi-file-pdf-box me-1"></i>
                                    Export PDF

                                </button>

                            @endif

                        </div>

                    </div>

                    {{-- Help text outside the aligned row --}}
                    <div class="row">

                        <div class="col-lg-3"></div>

                        <div class="col-lg-5">
                            <div class="form-text mt-1">
                                Select one or more routes.
                            </div>
                        </div>

                    </div>

                </form>

            </div>
        </div>

        {{-- Selected Route Reports --}}
        @foreach ($routeReports as $routeReport)

            @php
                $selectedRoute = $routeReport['route'];
                $rows = $routeReport['rows'];
            @endphp

            <div class="card mb-4">

                <div class="card-header">

                    <div class="d-flex flex-wrap justify-content-between align-items-center">

                        <div>

                            <h4 class="card-title mb-1">

                                {{ $selectedRoute->Route_id }}
                                -
                                {{ $selectedRoute->Route_name }}

                            </h4>

                            <div class="text-muted">

                                Shift:
                                <strong>
                                    {{ $routeReport['shiftName'] }}
                                </strong>

                                &nbsp; | &nbsp;

                                Date:
                                <strong>
                                    {{ \Carbon\Carbon::parse(
                                        $selectedDate
                                    )->format('m/d/Y') }}
                                </strong>

                            </div>

                        </div>

                        <div class="mt-2 mt-md-0">

                            <span class="badge bg-primary font-size-13">

                                Total Members:
                                {{ $rows->count() }}

                            </span>

                        </div>

                    </div>

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered align-middle">

                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Last</th>
                                    <th>First</th>
                                    <th>Address / City</th>
                                    <th>Insulin</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($rows as $member)

                                    @php
                                        $address = trim(
                                            (string) (
                                                $member->address1 ?? ''
                                            )
                                        );

                                        $city = trim(
                                            (string) (
                                                $member->city ?? ''
                                            )
                                        );

                                        $addressCity = trim(
                                            $address .
                                            (
                                                $address !== '' &&
                                                $city !== ''
                                                    ? ', '
                                                    : ''
                                            ) .
                                            $city
                                        );

                                        $paddedId = str_pad(
                                            trim(
                                                (string) $member->ParticipantId
                                            ),
                                            3,
                                            '0',
                                            STR_PAD_LEFT
                                        );

                                        $rawInsulin = strtoupper(
                                            trim(
                                                (string) (
                                                    $member->insulin_yn ?? ''
                                                )
                                            )
                                        );

                                        $insulinValue = in_array(
                                            $rawInsulin,
                                            ['1', 'Y', 'YES', 'TRUE'],
                                            true
                                        ) ? 'Y' : '';
                                    @endphp

                                    <tr>

                                        <td>
                                            {{ $paddedId }}
                                        </td>

                                        <td>
                                            {{ $member->last }}
                                        </td>

                                        <td>
                                            {{ $member->first }}
                                        </td>

                                        <td>
                                            {{ $addressCity }}
                                        </td>

                                        <td class="text-center">
                                            {{ $insulinValue }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5"
                                            class="text-center text-muted">

                                            No members found for this route.

                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        @endforeach

    </div>
</div>

@endsection


@push('scripts')

<script>
$(document).ready(function () {

    $('#route_ids').select2({
        placeholder: 'Select one or more routes',
        width: '100%',
        closeOnSelect: false
    });

});
</script>

@endpush
