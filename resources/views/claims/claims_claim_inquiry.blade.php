@extends('admin.admin_dashboard')

@section('title', 'Claim Inquiry')

@section('admin')

    <style>
        .claim-inquiry-table th,
        .claim-inquiry-table td {
            padding: .40rem .55rem !important;
            font-size: 13px;
            vertical-align: middle;
        }

        .claim-inquiry-table th {
            width: 190px;
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .claim-status {
            font-weight: 600;
        }

        .claim-message {
            white-space: normal;
            word-break: break-word;
        }
    </style>

    <div class="page-content">

        <div class="container-fluid">

            {{-- Page Title --}}
            <div class="row mb-3">

                <div class="col-12">

                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                        <h4 class="mb-sm-0 font-size-18">
                            Claim Inquiry
                        </h4>

                        <div class="page-title-right">

                            <a href="{{ route('claims.dashboard') }}" class="btn btn-secondary btn-sm">

                                <i class="fas fa-arrow-left me-1"></i>
                                Back

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Search Card --}}
            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-header">

                            <h5 class="mb-0">
                                Search Claim
                            </h5>

                        </div>

                        <div class="card-body">

                            <form method="GET" action="{{ route('claims.inquiry') }}">

                                <div class="row align-items-end">

                                    <div class="col-md-4">

                                        <label class="form-label">
                                            Claim Number
                                        </label>

                                        <input type="text" name="claim_no" id="claim_no"
                                            value="{{ request('claim_no') }}" class="form-control"
                                            placeholder="Enter Claim Number" autocomplete="off" autofocus>

                                    </div>


                                    <div class="col-md-2">

                                        <button type="submit" class="btn btn-primary w-100">

                                            <i class="fas fa-search me-1"></i>
                                            Search

                                        </button>

                                    </div>


                                    <div class="col-md-2">

                                        <a href="{{ route('claims.inquiry') }}" class="btn btn-light w-100">

                                            <i class="fas fa-undo me-1"></i>
                                            Clear

                                        </a>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Search performed but claim not found --}}
            @if (request()->filled('claim_no') && !$claim)
                <div class="row">

                    <div class="col-12">

                        <div class="alert alert-warning">

                            <i class="fas fa-exclamation-triangle me-1"></i>

                            No claim was found for Claim Number:

                            <strong>
                                {{ request('claim_no') }}
                            </strong>

                        </div>

                    </div>

                </div>
            @endif


            {{-- Claim Found --}}
            @if ($claim)

                {{-- Claim Information --}}
                <div class="row">

                    <div class="col-lg-6">

                        <div class="card">

                            <div class="card-header">

                                <h5 class="mb-0">
                                    Claim Information
                                </h5>

                            </div>

                            <div class="card-body p-0">

                                <table class="table table-bordered mb-0 claim-inquiry-table">

                                    <tbody>

                                        <tr>
                                            <th>Claim Number</th>

                                            <td>
                                                <strong>
                                                    {{ $claim->claim_no ?? '' }}
                                                </strong>
                                            </td>
                                        </tr>


                                        <tr>
                                            <th>Participant ID</th>

                                            <td>
                                                {{ $claim->ParticipantId ?? '' }}
                                            </td>
                                        </tr>


                                        <tr>
                                            <th>Service From</th>

                                            <td>
                                                @if (!empty($claim->service_from))
                                                    {{ \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') }}
                                                @endif
                                            </td>
                                        </tr>


                                        <tr>
                                            <th>Service To</th>

                                            <td>
                                                @if (!empty($claim->service_to))
                                                    {{ \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') }}
                                                @endif
                                            </td>
                                        </tr>


                                        <tr>
                                            <th>Units</th>

                                            <td>
                                                {{ $claim->units ?? '' }}
                                            </td>
                                        </tr>


                                        <tr>
                                            <th>Amount Per Unit</th>

                                            <td>
                                                @if (isset($claim->amount_per_unit))
                                                    ${{ number_format((float) $claim->amount_per_unit, 2) }}
                                                @endif
                                            </td>
                                        </tr>


                                        <tr>
                                            <th>Total Claim Amount</th>

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

                    </div>


                    {{-- Diagnosis / PA --}}
                    <div class="col-lg-6">

                        <div class="card">

                            <div class="card-header">

                                <h5 class="mb-0">
                                    Diagnosis / Authorization
                                </h5>

                            </div>

                            <div class="card-body p-0">

                                <table class="table table-bordered mb-0 claim-inquiry-table">

                                    <tbody>

                                        <tr>

                                            <th>Diagnosis 1</th>

                                            <td>
                                                {{ $claim->diag1 ?? '' }}
                                            </td>

                                        </tr>


                                        <tr>

                                            <th>Diagnosis 2</th>

                                            <td>
                                                {{ $claim->diag2 ?? '' }}
                                            </td>

                                        </tr>


                                        <tr>

                                            <th>Prior Authorization</th>

                                            <td>
                                                {{ $claim->prior_auth_no ?? '' }}
                                            </td>

                                        </tr>


                                        <tr>

                                            <th>PA Expiration</th>

                                            <td>

                                                @if (!empty($claim->prior_auth_expiry))
                                                    {{ \Carbon\Carbon::parse($claim->prior_auth_expiry)->format('m/d/Y') }}
                                                @endif

                                            </td>

                                        </tr>


                                        <tr>

                                            <th>Claim Status</th>

                                            <td>

                                                @php

                                                    $status = strtolower($claim->status ?? '');

                                                    $statusClass = match ($status) {
                                                        'ready' => 'text-success',

                                                        'sent' => 'text-primary',

                                                        'paid' => 'text-success',

                                                        'draft' => 'text-warning',

                                                        'rejected' => 'text-danger',

                                                        default => 'text-dark',
                                                    };

                                                @endphp

                                                <span class="claim-status {{ $statusClass }}">

                                                    {{ strtoupper($claim->status ?? '') }}

                                                </span>

                                            </td>

                                        </tr>


                                        <tr>

                                            <th>Precheck Result</th>

                                            <td>

                                                @php

                                                    $precheck = strtolower($claim->precheck_result ?? '');

                                                @endphp


                                                @if ($precheck === 'valid')
                                                    <span class="badge bg-success">
                                                        VALID
                                                    </span>
                                                @elseif($precheck === 'invalid')
                                                    <span class="badge bg-danger">
                                                        INVALID
                                                    </span>
                                                @elseif(!empty($precheck))
                                                    <span class="badge bg-secondary">

                                                        {{ strtoupper($precheck) }}

                                                    </span>
                                                @endif

                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Validation Message --}}
                @if (!empty($claim->precheck_message))

                    <div class="row">

                        <div class="col-12">

                            <div class="card">

                                <div class="card-header">

                                    <h5 class="mb-0">
                                        Validation / Precheck Message
                                    </h5>

                                </div>

                                <div class="card-body">

                                    @if (strtolower($claim->precheck_result ?? '') === 'invalid')
                                        <div class="alert alert-danger mb-0 claim-message">

                                            <i class="fas fa-exclamation-circle me-1"></i>

                                            {{ $claim->precheck_message }}

                                        </div>
                                    @else
                                        <div class="alert alert-info mb-0 claim-message">

                                            {{ $claim->precheck_message }}

                                        </div>
                                    @endif

                                </div>

                            </div>

                        </div>

                    </div>

                @endif


                {{-- Action Buttons --}}
                <div class="row">

                    <div class="col-12">

                        <div class="card">

                            <div class="card-body">

                                <div class="d-flex gap-2 flex-wrap">

                                    {{-- Add your actual Edit Claim route here --}}
                                    {{--
                                <a href="{{ route('claims.edit', $claim->id) }}"
                                   class="btn btn-primary btn-sm">

                                    <i class="fas fa-edit me-1"></i>
                                    Edit Claim

                                </a>
                                --}}


                                    <a href="{{ route('claims.inquiry') }}" class="btn btn-secondary btn-sm">

                                        <i class="fas fa-search me-1"></i>
                                        New Search

                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            @endif

        </div>

    </div>

@endsection
