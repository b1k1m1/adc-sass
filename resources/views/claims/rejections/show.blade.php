@extends('admin.admin_dashboard')

@section('title', 'Review Rejected Claim')

@section('admin')

    <style>
        .claim-detail-table th,
        .claim-detail-table td {
            padding: .40rem .55rem !important;
            font-size: 13px;
            vertical-align: middle;
        }

        .claim-detail-table th {
            width: 210px;
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .rejection-message {
            white-space: normal;
            word-break: break-word;
            font-size: 14px;
        }
    </style>


    <div class="page-content">

        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success">

                    <i class="fas fa-check-circle me-1"></i>

                    {{ session('success') }}

                </div>
            @endif


            @if (session('error'))
                <div class="alert alert-danger">

                    <i class="fas fa-exclamation-circle me-1"></i>

                    {{ session('error') }}

                </div>
            @endif

            {{-- Page Title --}}

            <div class="row mb-3">

                <div class="col-12">

                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                        <h4 class="mb-sm-0 font-size-18">
                            Review Rejected Claim
                        </h4>

                        <div class="page-title-right">

                            @if(!empty($claim->claim_no))

                                <form method="POST"
                                    action="{{ route(
                                        'claims.rejections.createResubmission',
                                        $claim->id
                                    ) }}"
                                    class="d-inline">

                                    @csrf

                                    <button type="submit"
                                            class="btn btn-warning btn-sm"
                                            onclick="return confirm(
                                                'Create a new claim for resubmission from Claim #{{ $claim->claim_no }}?'
                                            );">

                                        <i class="fas fa-copy me-1"></i>

                                        Create New Claim for Resubmission

                                    </button>

                                </form>

                            @endif

                            <a href="{{ route('claims.rejections.index') }}" class="btn btn-secondary btn-sm">

                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Queue

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Filing Status --}}

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-body">

                            <div class="d-flex align-items-center gap-3 flex-wrap">

                                <div>

                                    <strong>Filing Status:</strong>

                                </div>


                                <div>

                                    @if ($claim->filing_status === 'expired')
                                        <span class="badge bg-secondary">
                                            FILING EXPIRED
                                        </span>
                                    @elseif($claim->filing_status === 'critical')
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
                                    @else
                                        <span class="badge bg-light text-dark">
                                            UNKNOWN
                                        </span>
                                    @endif

                                </div>


                                <div>

                                    <strong>Deadline:</strong>

                                    {{ $claim->filing_deadline ?? '' }}

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


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

                            <table class="table table-bordered mb-0 claim-detail-table">

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

                                        <th>Member</th>

                                        <td>

                                            {{ $member->LastName ?? '' }}

                                            @if (!empty($member->LastName) && !empty($member->FirstName))
                                                ,
                                            @endif

                                            {{ $member->FirstName ?? '' }}

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

                                        <th>Total Amount</th>

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

                            <table class="table table-bordered mb-0 claim-detail-table">

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

                                        <th>Status</th>

                                        <td>

                                            {{ strtoupper($claim->status ?? '') }}

                                        </td>

                                    </tr>


                                    <tr>

                                        <th>Precheck Result</th>

                                        <td>

                                            @if (strtolower($claim->precheck_result ?? '') === 'invalid')
                                                <span class="badge bg-danger">
                                                    INVALID
                                                </span>
                                            @elseif(strtolower($claim->precheck_result ?? '') === 'valid')
                                                <span class="badge bg-success">
                                                    VALID
                                                </span>
                                            @else
                                                {{ strtoupper($claim->precheck_result ?? '') }}
                                            @endif

                                        </td>

                                    </tr>


                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Rejection Message --}}

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-header">

                            <h5 class="mb-0">
                                Rejection / Validation Reason
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

                                    No rejection message is stored for this claim.

                                </div>
                            @endif

                        </div>

                    </div>
                    {{-- Claim Rejection History --}}

                    <div class="row">

                        <div class="col-12">

                            <div class="card">

                                <div class="card-header">

                                    <h5 class="mb-0">
                                        Claim History
                                    </h5>

                                </div>

                                <div class="card-body">

                                    @if ($history->isEmpty())

                                        <div class="alert alert-info mb-0">

                                            No rejection history has been recorded for this claim.

                                        </div>
                                    @else
                                        <div class="table-responsive">

                                            <table class="table table-bordered table-striped table-sm">

                                                <thead>

                                                    <tr>

                                                        <th>Date / Time</th>

                                                        <th>Action</th>

                                                        <th>Old Status</th>

                                                        <th>New Status</th>

                                                        <th>Source</th>

                                                        <th>Message</th>

                                                        <th>User</th>

                                                    </tr>

                                                </thead>

                                                <tbody>

                                                    @foreach ($history as $row)
                                                        <tr>

                                                            <td>

                                                                @if (!empty($row->action_at))
                                                                    {{ \Carbon\Carbon::parse($row->action_at)->format('m/d/Y h:i A') }}
                                                                @endif

                                                            </td>

                                                            <td>

                                                                @if ($row->action_type === 'CORRECTED')
                                                                    <span class="badge bg-warning text-dark">
                                                                        CORRECTED
                                                                    </span>
                                                                @elseif($row->action_type === 'REVALIDATED')
                                                                    <span class="badge bg-success">
                                                                        REVALIDATED
                                                                    </span>
                                                                @elseif($row->action_type === 'STILL_INVALID')
                                                                    <span class="badge bg-danger">
                                                                        STILL INVALID
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-secondary">
                                                                        {{ $row->action_type }}
                                                                    </span>
                                                                @endif

                                                            </td>

                                                            <td>
                                                                {{ strtoupper($row->old_status ?? '') }}
                                                            </td>

                                                            <td>
                                                                {{ strtoupper($row->new_status ?? '') }}
                                                            </td>

                                                            <td>
                                                                {{ $row->rejection_source ?? '' }}
                                                            </td>

                                                            <td style="white-space: normal; min-width: 300px;">

                                                                {{ $row->rejection_message ?? '' }}

                                                            </td>

                                                            <td>
                                                                {{ $row->action_by ?? '' }}
                                                            </td>

                                                        </tr>
                                                    @endforeach

                                                </tbody>

                                            </table>

                                        </div>

                                    @endif

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Actions --}}

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-body">

                            <div class="d-flex gap-2 flex-wrap">


                                <a href="{{ route('claims.rejections.index') }}" class="btn btn-secondary btn-sm">

                                    <i class="fas fa-arrow-left me-1"></i>
                                    Back to Queue

                                </a>


                                @if ($claim->filing_status === 'expired')
                                    <button type="button" class="btn btn-secondary btn-sm" disabled>

                                        Filing Period Expired

                                    </button>
                                @else
                                    {{-- Next step --}}

                                    <a href="{{ route('claims.rejections.edit', $claim->id) }}"
                                        class="btn btn-primary btn-sm">

                                        <i class="fas fa-edit me-1"></i>
                                        Edit / Correct

                                    </a>

                                    <form method="POST" action="{{ route('claims.rejections.revalidate', $claim->id) }}"
                                        class="d-inline">

                                        @csrf

                                        <button type="submit" class="btn btn-success btn-sm"
                                            onclick="return confirm('Revalidate this claim now?');">

                                            <i class="fas fa-check-circle me-1"></i>
                                            Revalidate

                                        </button>

                                    </form>
                                @endif


                            </div>

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>

@endsection
