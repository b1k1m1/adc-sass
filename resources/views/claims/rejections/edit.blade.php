@extends('admin.admin_dashboard')

@section('title', 'Correct Rejected Claim')

@section('admin')

    <style>
        .claim-info-table th,
        .claim-info-table td {
            padding: .40rem .55rem !important;
            font-size: 13px;
            vertical-align: middle;
        }

        .claim-info-table th {
            width: 190px;
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .rejection-message {
            white-space: normal;
            word-break: break-word;
        }
    </style>


    <div class="page-content">

        <div class="container-fluid">


            {{-- Page Header --}}

            <div class="row mb-3">

                <div class="col-12">

                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                        <h4 class="mb-sm-0 font-size-18">

                            Correct Rejected Claim

                        </h4>


                        <div class="page-title-right">

                            <a href="{{ route('claims.rejections.show', $claim->id) }}" class="btn btn-secondary btn-sm">

                                <i class="fas fa-arrow-left me-1"></i>

                                Back to Review

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Validation Errors --}}

            @if ($errors->any())

                <div class="alert alert-danger">

                    <strong>Please correct the following:</strong>

                    <ul class="mb-0 mt-2">

                        @foreach ($errors->all() as $error)
                            <li>
                                {{ $error }}
                            </li>
                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- Filing Information --}}

            <div class="card">

                <div class="card-body">

                    <div class="d-flex gap-4 align-items-center flex-wrap">

                        <div>

                            <strong>
                                Filing Deadline:
                            </strong>

                            {{ $claim->filing_deadline ?? '' }}

                        </div>


                        <div>

                            @if ($claim->filing_status === 'critical')
                                <span class="badge bg-danger">

                                    {{ $claim->days_left }}
                                    DAYS LEFT

                                </span>
                            @elseif($claim->filing_status === 'warning')
                                <span class="badge bg-warning text-dark">

                                    {{ $claim->days_left }}
                                    DAYS LEFT

                                </span>
                            @elseif($claim->filing_status === 'eligible')
                                <span class="badge bg-success">

                                    ELIGIBLE

                                </span>
                            @endif

                        </div>

                    </div>

                </div>

            </div>


            {{-- Rejection Reason --}}

            <div class="card">

                <div class="card-header">

                    <h5 class="mb-0">
                        Current Rejection Reason
                    </h5>

                </div>


                <div class="card-body">

                    @if (!empty($claim->precheck_message))
                        <div class="alert alert-danger mb-0 rejection-message">

                            <i class="fas fa-exclamation-circle me-1"></i>

                            {{ $claim->precheck_message }}

                        </div>
                    @else
                        <div class="alert alert-secondary mb-0">

                            No rejection message is stored.

                        </div>
                    @endif

                </div>

            </div>


            {{-- Claim Basic Information --}}

            <div class="card">

                <div class="card-header">

                    <h5 class="mb-0">
                        Claim Information
                    </h5>

                </div>


                <div class="card-body p-0">

                    <table class="table table-bordered mb-0 claim-info-table">

                        <tbody>


                            <tr>

                                <th>
                                    Claim Number
                                </th>

                                <td>

                                    <strong>
                                        {{ $claim->claim_no ?? '' }}
                                    </strong>

                                </td>

                            </tr>


                            <tr>

                                <th>
                                    Participant ID
                                </th>

                                <td>

                                    {{ $claim->ParticipantId ?? '' }}

                                </td>

                            </tr>


                            <tr>

                                <th>
                                    Member
                                </th>

                                <td>

                                    {{ $member->LastName ?? '' }}

                                    @if (!empty($member->LastName) && !empty($member->FirstName))
                                        ,
                                    @endif

                                    {{ $member->FirstName ?? '' }}

                                </td>

                            </tr>


                            <tr>

                                <th>
                                    Service From
                                </th>

                                <td>

                                    @if (!empty($claim->service_from))
                                        {{ \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') }}
                                    @endif

                                </td>

                            </tr>


                            <tr>

                                <th>
                                    Service To
                                </th>

                                <td>

                                    @if (!empty($claim->service_to))
                                        {{ \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') }}
                                    @endif

                                </td>

                            </tr>


                            <tr>

                                <th>
                                    Units
                                </th>

                                <td>

                                    {{ $claim->units ?? '' }}

                                </td>

                            </tr>


                            <tr>

                                <th>
                                    Total Amount
                                </th>

                                <td>

                                    @if (isset($claim->total_amount))
                                        <strong>

                                            ${{ number_format((float) $claim->total_amount, 2) }}

                                        </strong>
                                    @endif

                                </td>

                            </tr>


                        </tbody>

                    </table>

                </div>

            </div>


            {{-- Correction Form --}}

            <div class="card">

                <div class="card-header">

                    <h5 class="mb-0">
                        Correct Claim
                    </h5>

                </div>


                <div class="card-body">


                    <form method="POST" action="{{ route('claims.rejections.update', $claim->id) }}">


                        @csrf

                        @method('PUT')

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>

                                <input type="text" name="Address1" class="form-control"
                                    value="{{ old('Address1', $member->Address1 ?? '') }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">City</label>

                                <input type="text" name="City" class="form-control"
                                    value="{{ old('City', $member->City ?? '') }}">
                            </div>

                            <div class="col-md-1 mb-3">
                                <label class="form-label">State</label>

                                <input type="text" name="State" class="form-control" maxlength="2"
                                    value="{{ old('State', $member->State ?? '') }}">
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="form-label">ZIP Code</label>

                                <input type="text" name="ZipCode" class="form-control"
                                    value="{{ old('ZipCode', $member->ZipCode ?? '') }}">
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Diagnosis 1
                                </label>

                                <select name="diag1" id="diag1" class="form-control">

                                    @if (old('diag1', $claim->diag1 ?? ''))
                                        <option value="{{ old('diag1', $claim->diag1 ?? '') }}" selected>

                                            {{ old('diag1', $claim->diag1 ?? '') }}

                                        </option>
                                    @endif

                                </select>

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Diagnosis 2
                                </label>

                                <select name="diag2" id="diag2" class="form-control">

                                    @if (old('diag2', $claim->diag2 ?? ''))
                                        <option value="{{ old('diag2', $claim->diag2 ?? '') }}" selected>

                                            {{ old('diag2', $claim->diag2 ?? '') }}

                                        </option>
                                    @endif

                                </select>

                            </div>

                        </div>


                        <div class="row">


                            {{-- Prior Authorization --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Prior Authorization Number

                                </label>


                                <input type="text" name="prior_auth_no" class="form-control" maxlength="100"
                                    value="{{ old('prior_auth_no', $claim->prior_auth_no ?? '') }}"
                                    placeholder="Prior Authorization Number">

                            </div>


                            {{-- PA Expiration --}}

                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    PA Expiration Date

                                </label>


                                <input type="date" name="prior_auth_expiry" class="form-control"
                                    value="{{ old(
                                        'prior_auth_expiry',
                                        !empty($claim->prior_auth_expiry) ? \Carbon\Carbon::parse($claim->prior_auth_expiry)->format('Y-m-d') : '',
                                    ) }}">

                            </div>


                        </div>


                        <hr>


                        <div class="d-flex gap-2">


                            <button type="submit" class="btn btn-primary">

                                <i class="fas fa-save me-1"></i>

                                Save Corrections

                            </button>


                            <a href="{{ route('claims.rejections.show', $claim->id) }}" class="btn btn-secondary">

                                Cancel

                            </a>


                        </div>


                    </form>


                </div>

            </div>


        </div>

    </div>

@endsection

@section('scripts')

    <script>
        $(document).ready(function() {

            /*
            |--------------------------------------------------------------------------
            | ICD-10 Select2
            |--------------------------------------------------------------------------
            */

            function initIcd10Select(selector, placeholder) {

                $(selector).select2({

                    width: '100%',

                    placeholder: placeholder,

                    allowClear: true,

                    minimumInputLength: 2,

                    ajax: {

                        url: "{{ route('lookup.icd10') }}",

                        dataType: 'json',

                        delay: 300,

                        data: function(params) {

                            return {
                                q: params.term
                            };

                        },

                        processResults: function(data) {
                            return data;
                        },

                        cache: true

                    },

                    templateResult: function(item) {

                        if (item.loading) {
                            return item.text;
                        }

                        return item.text;

                    },

                    templateSelection: function(item) {

                        return item.id || item.text;

                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Initialize Diagnosis Fields
            |--------------------------------------------------------------------------
            */

            initIcd10Select(
                '#diag1',
                'Search Diagnosis 1'
            );

            initIcd10Select(
                '#diag2',
                'Search Diagnosis 2'
            );

        });
    </script>

@endsection
