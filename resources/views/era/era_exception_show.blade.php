@extends('admin.admin_dashboard')

@section('title', 'ERA Exception Details')

@section('admin')

    <style>
        .exception-table th,
        .exception-table td {
            padding: .35rem .50rem !important;
            font-size: 13px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .exception-reason {
            white-space: normal;
            line-height: 1.5;
        }

        .comparison-label {
            font-weight: 600;
            width: 180px;
        }
    </style>


    <div class="page-content">

        <div class="container-fluid">


            {{-- BACK BUTTON --}}

            <div class="mb-3">

                <a href="{{ route('era.exceptions') }}" class="btn btn-secondary btn-sm">

                    &laquo; Back to ERA Exceptions

                </a>

            </div>


            {{-- EXCEPTION HEADER --}}

            <div class="card mb-3">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">

                        ERA Exception Details

                    </h5>


                    @if ($exception->resolved_yn === 'Y')
                        <span class="badge bg-success">
                            Resolved
                        </span>
                    @else
                        <span class="badge bg-warning text-dark">
                            Open
                        </span>
                    @endif

                </div>


                <div class="card-body">


                    <div class="row">

                        <div class="col-md-4">

                            <strong>ERA File ID:</strong>

                            {{ $exception->era_file_id }}

                            <br>


                            <strong>ERA Claim ID:</strong>

                            {{ $exception->era_claim_id }}

                            <br>


                            <strong>ERA File:</strong>

                            {{ $exception->file_name ?? '' }}

                        </div>


                        <div class="col-md-4">

                            <strong>Payer:</strong>

                            {{ $exception->payer_name ?? '' }}

                            <br>


                            <strong>Payment Date:</strong>

                            @if (!empty($exception->payment_date))
                                {{ \Carbon\Carbon::parse($exception->payment_date)->format('m/d/Y') }}
                            @endif

                            <br>


                            <strong>Trace / EFT #:</strong>

                            {{ $exception->trace_number ?? '' }}

                        </div>


                        <div class="col-md-4">

                            <strong>ERA Claim #:</strong>

                            {{ $exception->patient_control_number }}

                            <br>


                            <strong>ADC Claim #:</strong>

                            {{ $exception->normalized_claim_no }}

                            <br>


                            <strong>Payer Claim #:</strong>

                            {{ $exception->payer_claim_number ?? '' }}

                        </div>

                    </div>

                </div>

            </div>


            {{-- EXCEPTION REASON --}}

            <div class="card mb-3">

                <div class="card-header">

                    <h6 class="mb-0">

                        Exception Reason

                    </h6>

                </div>


                <div class="card-body">

                    <div class="alert alert-warning mb-0 exception-reason">

                        {{ $exception->reason }}

                    </div>

                </div>

            </div>


            {{-- ERA CLAIM SUMMARY --}}

            <div class="card mb-3">

                <div class="card-header">

                    <h6 class="mb-0">

                        ERA Claim Summary

                    </h6>

                </div>


                <div class="card-body">

                    <div class="row">

                        <div class="col-md-4">

                            <strong>Member ID:</strong>

                            {{ $exception->member_id ?? '' }}

                            <br>


                            <strong>Claim Status:</strong>

                            {{ $exception->claim_status_code ?? '' }}

                        </div>


                        <div class="col-md-4">

                            <strong>Service From:</strong>

                            @if (!empty($exception->service_from))
                                {{ \Carbon\Carbon::parse($exception->service_from)->format('m/d/Y') }}
                            @endif

                            <br>


                            <strong>Service To:</strong>

                            @if (!empty($exception->service_to))
                                {{ \Carbon\Carbon::parse($exception->service_to)->format('m/d/Y') }}
                            @endif

                        </div>


                        <div class="col-md-4">

                            <strong>Total Charge:</strong>

                            ${{ number_format($exception->total_charge ?? 0, 2) }}

                            <br>


                            <strong>Total Paid:</strong>

                            ${{ number_format($exception->total_paid ?? 0, 2) }}

                            <br>


                            <strong>Patient Resp:</strong>

                            ${{ number_format($exception->patient_responsibility ?? 0, 2) }}

                        </div>

                    </div>

                </div>

            </div>


            {{-- ERA SERVICE LINES --}}

            <div class="card mb-3">

                <div class="card-header">

                    <h6 class="mb-0">

                        ERA Service Lines

                    </h6>

                </div>


                <div class="card-body">


                    @if ($serviceLines->isEmpty())

                        <div class="alert alert-info mb-0">

                            No ERA service lines found.

                        </div>
                    @else
                        <div class="table-responsive">

                            <table class="table table-bordered table-hover table-sm exception-table">

                                <thead class="table-light">

                                    <tr>

                                        <th>Seq</th>

                                        <th>Procedure</th>

                                        <th>Modifier 1</th>

                                        <th>Modifier 2</th>

                                        <th>Service From</th>

                                        <th>Service To</th>

                                        <th class="text-end">
                                            Charge
                                        </th>

                                        <th class="text-end">
                                            Paid
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach ($serviceLines as $svc)
                                        <tr>

                                            <td>

                                                {{ $svc->svc_sequence }}

                                            </td>


                                            <td>

                                                {{ $svc->procedure_code }}

                                            </td>


                                            <td>

                                                {{ $svc->modifier1 }}

                                            </td>


                                            <td>

                                                {{ $svc->modifier2 }}

                                            </td>


                                            <td>

                                                @if ($svc->service_from)
                                                    {{ \Carbon\Carbon::parse($svc->service_from)->format('m/d/Y') }}
                                                @endif

                                            </td>


                                            <td>

                                                @if ($svc->service_to)
                                                    {{ \Carbon\Carbon::parse($svc->service_to)->format('m/d/Y') }}
                                                @endif

                                            </td>


                                            <td class="text-end">

                                                {{ number_format($svc->charge_amount ?? 0, 2) }}

                                            </td>


                                            <td class="text-end">

                                                {{ number_format($svc->paid_amount ?? 0, 2) }}

                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @endif

                </div>

            </div>

            @if (session('cross_match_results'))

                <div class="card mb-3">

                    <div class="card-header d-flex justify-content-between align-items-center">

                        <span>
                            Cross-Claim Match Diagnostic
                        </span>

                        <span class="badge bg-info text-dark">
                            Diagnostic Only
                        </span>

                    </div>

                    <div class="card-body">

                        <div class="alert alert-info mb-3">

                            No claims or ERA payments were changed.
                            This screen only shows possible cross-claim matches.

                        </div>

                        <div class="table-responsive">

                            <table class="table table-bordered table-sm align-middle mb-0">

                                <thead class="table-light">

                                    <tr>

                                        <th>ERA SVC</th>

                                        <th>Procedure</th>

                                        <th>Modifier</th>

                                        <th>Service From</th>

                                        <th>Service To</th>

                                        <th class="text-end">
                                            Charge
                                        </th>

                                        <th>Result</th>

                                        <th>ADC Claim #</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach (session('cross_match_results') as $result)
                                        <tr>

                                            <td>
                                                {{ $result['svc_sequence'] }}
                                            </td>

                                            <td>
                                                {{ $result['procedure_code'] ?? '' }}
                                            </td>

                                            <td>
                                                {{ $result['modifier1'] ?? '' }}
                                            </td>

                                            <td>

                                                @if (!empty($result['service_from']))
                                                    {{ \Carbon\Carbon::parse($result['service_from'])->format('m/d/Y') }}
                                                @endif

                                            </td>

                                            <td>

                                                @if (!empty($result['service_to']))
                                                    {{ \Carbon\Carbon::parse($result['service_to'])->format('m/d/Y') }}
                                                @endif

                                            </td>

                                            <td class="text-end">

                                                {{ number_format($result['charge_amount'] ?? 0, 2) }}

                                            </td>

                                            <td>

                                                @if ($result['status'] === 'CURRENT')
                                                    <span class="badge bg-success">
                                                        Current Claim
                                                    </span>
                                                @elseif($result['status'] === 'CROSS_MATCH')
                                                    <span class="badge bg-primary">
                                                        Cross Match
                                                    </span>
                                                @elseif($result['status'] === 'MULTIPLE')
                                                    <span class="badge bg-warning text-dark">
                                                        Multiple Matches
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        No Match
                                                    </span>
                                                @endif

                                                <br>

                                                <small class="text-muted">

                                                    {{ $result['message'] }}

                                                </small>

                                            </td>

                                            <td>

                                                @if (!empty($result['claim_no']))
                                                    <strong>
                                                        {{ $result['claim_no'] }}
                                                    </strong>
                                                @else
                                                    -
                                                @endif

                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </div>
                        @php

                                $crossResults =
                                    session('cross_match_results', []);

                                $hasCrossMatch =
                                    collect($crossResults)
                                    ->contains(
                                        fn ($result) =>
                                            ($result['status'] ?? '')
                                            ===
                                            'CROSS_MATCH'
                                    );

                                $hasUnsafeMatch =
                                    collect($crossResults)
                                    ->contains(
                                        fn ($result) =>
                                            in_array(
                                                $result['status'] ?? '',
                                                ['MULTIPLE', 'NONE'],
                                                true
                                            )
                                    );

                            @endphp


                            @if(
                                $hasCrossMatch
                                &&
                                !$hasUnsafeMatch
                                &&
                                $exception->resolved_yn !== 'Y'
                            )

                                <div class="mt-3 text-end">

                                    <form method="POST"
                                        action="{{ route(
                                            'era.exception.cross-match.post',
                                            $exception->id
                                        ) }}"
                                        class="d-inline">

                                        @csrf

                                        <button type="submit"
                                                class="btn btn-success"
                                                onclick="return confirm(
                                                    'Apply this cross-claim match and post the ERA payments to the matched ADC claims?'
                                                );">

                                            Apply Cross Match

                                        </button>

                                    </form>

                                </div>

                            @endif

                    </div>

                </div>

            @endif


            {{-- ADC CLAIM ROWS --}}

            <div class="card mb-3">

                <div class="card-header">

                    <h6 class="mb-0">

                        ADC Claim Rows

                    </h6>

                </div>


                <div class="card-body">


                    @if ($claimRows->isEmpty())

                        <div class="alert alert-danger mb-0">

                            ADC claim

                            <strong>
                                {{ $exception->normalized_claim_no }}
                            </strong>

                            was not found in the claims table.

                        </div>
                    @else
                        <div class="table-responsive">

                            <table class="table table-bordered table-hover table-sm exception-table">

                                <thead class="table-light">

                                    <tr>

                                        <th>Line</th>

                                        <th>Member ID</th>

                                        <th>Procedure</th>

                                        <th>Modifier 1</th>

                                        <th>Service From</th>

                                        <th>Service To</th>

                                        <th class="text-end">
                                            Units
                                        </th>

                                        <th class="text-end">
                                            Amount / Unit
                                        </th>

                                        <th class="text-end">
                                            Total
                                        </th>

                                        <th>Status</th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach ($claimRows as $claim)
                                        <tr>

                                            <td>

                                                {{ $claim->line_no }}

                                            </td>


                                            <td>

                                                {{ $claim->ParticipantId }}

                                            </td>


                                            <td>

                                                {{ $claim->proc_code }}

                                            </td>


                                            <td>

                                                {{ $claim->m1 }}

                                            </td>


                                            <td>

                                                @if ($claim->service_from)
                                                    {{ \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') }}
                                                @endif

                                            </td>


                                            <td>

                                                @if ($claim->service_to)
                                                    {{ \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') }}
                                                @endif

                                            </td>


                                            <td class="text-end">

                                                {{ $claim->units }}

                                            </td>


                                            <td class="text-end">

                                                {{ number_format($claim->amount_per_unit ?? 0, 2) }}

                                            </td>


                                            <td class="text-end">

                                                {{ number_format($claim->total_amount ?? 0, 2) }}

                                            </td>


                                            <td>

                                                {{ ucfirst($claim->status ?? '') }}

                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @endif

                </div>

            </div>

            {{-- LEGACY 837 RECOVERY VALIDATION --}}

            <div class="card mb-3">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h6 class="mb-0">
                        Legacy 837 Recovery Validation
                    </h6>

                    @if ($legacyValidation['available'])

                        @if ($legacyValidation['safe'])
                            <span class="badge bg-success">
                                SAFE MATCH
                            </span>
                        @else
                            <span class="badge bg-danger">
                                NOT SAFE
                            </span>
                        @endif
                    @else
                        <span class="badge bg-secondary">
                            Not Applicable
                        </span>

                    @endif

                </div>


                <div class="card-body">

                    @if (!$legacyValidation['available'])

                        <div class="alert alert-secondary mb-0">

                            No matching legacy 837 mapping was found for ERA claim number

                            <strong>
                                {{ $exception->patient_control_number }}
                            </strong>.

                        </div>
                    @else
                        {{-- VALIDATION RESULT --}}

                        <div
                            class="alert
                {{ $legacyValidation['safe'] ? 'alert-success' : 'alert-danger' }}">

                            <strong>
                                Validation Result:
                            </strong>

                            {{ $legacyValidation['message'] }}

                        </div>


                        @if ($legacyMap)

                            <div class="row mb-3">

                                <div class="col-md-4">

                                    <strong>Legacy CLM01:</strong>

                                    {{ $legacyMap->clm01 }}

                                    <br>


                                    <strong>Participant ID:</strong>

                                    {{ $legacyMap->ParticipantId ?? '' }}

                                    <br>


                                    <strong>Insurance ID:</strong>

                                    {{ $legacyMap->insurance_member_id ?? '' }}

                                </div>


                                <div class="col-md-4">

                                    <strong>Member:</strong>

                                    @if ($legacyMember)
                                        {{ $legacyMember->LastName ?? '' }},
                                        {{ $legacyMember->FirstName ?? '' }}
                                    @else
                                        {{ $legacyMap->last_name ?? '' }},
                                        {{ $legacyMap->first_name ?? '' }}
                                    @endif

                                    <br>


                                    <strong>Service From:</strong>

                                    @if ($legacyMap->service_from)
                                        {{ \Carbon\Carbon::parse($legacyMap->service_from)->format('m/d/Y') }}
                                    @endif

                                    <br>


                                    <strong>Service To:</strong>

                                    @if ($legacyMap->service_to)
                                        {{ \Carbon\Carbon::parse($legacyMap->service_to)->format('m/d/Y') }}
                                    @endif

                                </div>


                                <div class="col-md-4">

                                    <strong>Legacy Charge:</strong>

                                    ${{ number_format($legacyMap->charge_amount ?? 0, 2) }}

                                    <br>


                                    <strong>Payer:</strong>

                                    {{ $legacyMap->payer_name ?? '' }}

                                    @if ($legacyMap->payer_id)
                                        ({{ $legacyMap->payer_id }})
                                    @endif

                                    <br>


                                    <strong>Source File:</strong>

                                    {{ $legacyMap->batch_file_name ?? '' }}

                                </div>

                            </div>


                            {{-- LEGACY ADC CLAIM CANDIDATES --}}

                            <h6 class="mt-3">
                                Proposed ADC Claim Candidate
                            </h6>


                            @if ($legacyClaimRows->isEmpty())

                                <div class="alert alert-warning mb-0">

                                    No ADC claim candidate was found using the
                                    mapped ParticipantId and legacy service dates.

                                </div>
                            @else
                                <div class="table-responsive">

                                    <table class="table table-bordered table-hover table-sm exception-table">

                                        <thead class="table-light">

                                            <tr>

                                                <th>Claim #</th>

                                                <th>Line</th>

                                                <th>Participant</th>

                                                <th>Procedure</th>

                                                <th>Modifier</th>

                                                <th>Service From</th>

                                                <th>Service To</th>

                                                <th class="text-end">
                                                    Units
                                                </th>

                                                <th class="text-end">
                                                    Charge
                                                </th>

                                                <th>Status</th>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            @foreach ($legacyClaimRows as $claim)
                                                <tr>

                                                    <td>
                                                        {{ $claim->claim_no }}
                                                    </td>

                                                    <td>
                                                        {{ $claim->line_no }}
                                                    </td>

                                                    <td>
                                                        {{ $claim->ParticipantId }}
                                                    </td>

                                                    <td>
                                                        {{ $claim->proc_code }}
                                                    </td>

                                                    <td>
                                                        {{ $claim->m1 }}
                                                    </td>

                                                    <td>

                                                        @if ($claim->service_from)
                                                            {{ \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') }}
                                                        @endif

                                                    </td>

                                                    <td>

                                                        @if ($claim->service_to)
                                                            {{ \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') }}
                                                        @endif

                                                    </td>

                                                    <td class="text-end">

                                                        {{ $claim->units }}

                                                    </td>

                                                    <td class="text-end">

                                                        {{ number_format($claim->total_amount ?? 0, 2) }}

                                                    </td>

                                                    <td>

                                                        {{ ucfirst($claim->status ?? '') }}

                                                    </td>

                                                </tr>
                                            @endforeach

                                        </tbody>

                                    </table>

                                </div>

                            @endif

                        @endif

                    @endif

                </div>

            </div>

            {{-- ACTIONS --}}

            <div class="card">

                <div class="card-body d-flex justify-content-between align-items-center">


                    <div>

                        @if ($exception->resolved_yn === 'Y')

                            <span class="text-success fw-bold">

                                This exception was resolved.

                            </span>

                            @if (!empty($exception->resolved_at))
                                <br>

                                <small class="text-muted">

                                    Resolved:

                                    {{ \Carbon\Carbon::parse($exception->resolved_at)->format('m/d/Y h:i A') }}

                                </small>
                            @endif
                        @else
                            <span class="text-muted">

                                Review the ERA and ADC claim information above before resolving.

                            </span>

                        @endif

                    </div>


                    <div>


                        @if ($exception->resolved_yn !== 'Y')
                            <form method="POST" action="{{ route('era.exception.resolve', $exception->id) }}"
                                class="d-inline">

                                @csrf

                                @if ($exception->resolved_yn !== 'Y' && !empty($legacyValidation['available']) && !empty($legacyValidation['safe']))
                                    <form method="POST"
                                        action="{{ route('era.exception.legacy.recover', $exception->id) }}"
                                        class="d-inline">

                                        @csrf

                                        <button type="submit" class="btn btn-primary btn-sm"
                                            onclick="return confirm(
                                                    'Post this legacy ERA payment to the matched ADC claim?'
                                                );">

                                            Recover / Post

                                        </button>

                                    </form>
                                @endif

                                <button type="submit" class="btn btn-success btn-sm"
                                    onclick="return confirm('Mark this ERA exception as resolved?');">

                                    Mark Resolved

                                </button>

                            </form>
                        @endif


                        <a href="{{ route('era.exceptions') }}" class="btn btn-secondary btn-sm">

                            Back

                        </a>

                    </div>

                </div>

            </div>


        </div>

    </div>

@endsection
