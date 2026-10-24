@extends('admin.admin_dashboard')
@section('title', 'ERA Claim Details')

@section('admin')
    <div class="page-content">
        <div class="container-fluid">

            <div class="mb-3">
                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">&laquo; Back</a>
            </div>

            {{-- CLAIM HEADER --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">ERA Claim Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-1">
                        <div class="col-md-4">
                            <strong>ERA File:</strong> {{ $file->file_name }}<br>
                            <strong>Payer:</strong> {{ $file->payer_name }}
                        </div>
                        <div class="col-md-4">
                            <strong>Claim No (CLP01):</strong> {{ $claim->patient_control_number }}<br>
                            <strong>Payer Claim #:</strong> {{ $claim->payer_claim_number }}
                        </div>
                        <div class="col-md-4">
                            <!--
                                <strong>Status Code:</strong> {{ $claim->claim_status_code }}<br> -->
                            <strong>Status Code:</strong>
                            {{ $claim->claim_status_code }}
                            @if (!empty($statusDesc))
                                <span class="text-muted">({{ $statusDesc }})</span><br>
                            @endif
                            <strong>Member ID:</strong>
                            {{ $claim->member_id }} -
                            {{ $member ? $member->LastName . ', ' . $member->FirstName : 'Member Not Found' }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Total Charge:</strong> {{ number_format($claim->total_charge ?? 0, 2) }}
                        </div>
                        <div class="col-md-4">
                            <strong>Total Paid:</strong> {{ number_format($claim->total_paid ?? 0, 2) }}
                        </div>
                        <div class="col-md-4">
                            <strong>Patient Resp:</strong> {{ number_format($claim->patient_responsibility ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- SERVICE LINES + LINE-LEVEL ADJUSTMENTS --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Service Lines (SVC) &amp; Adjustments (CAS)</h5>
                </div>
                <div class="card-body">
                    @if ($serviceLines->isEmpty())
                        <p class="text-muted mb-0">No service lines parsed for this claim.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Proc Code</th>
                                        <th>Modifiers</th>
                                        <th class="text-end">Charge</th>
                                        <th class="text-end">Paid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($serviceLines as $line)
                                        @php
                                            $lineAdj = $adjBySvc[$line->svc_sequence] ?? [];
                                            // 🔹 NEW: line-level remarks (LQ tied to this SVC)
                                            $lineRemarks = $remarksBySvc[$line->svc_sequence] ?? [];
                                        @endphp
                                        <tr>
                                            <td>{{ $line->svc_sequence }}</td>
                                            <td>{{ $line->procedure_code }}</td>
                                            <td>
                                                {{ $line->modifier1 }}
                                                {{ $line->modifier2 }}
                                                {{ $line->modifier3 }}
                                                {{ $line->modifier4 }}
                                            </td>
                                            <td class="text-end">{{ number_format($line->charge_amount ?? 0, 2) }}</td>
                                            <td class="text-end">{{ number_format($line->paid_amount ?? 0, 2) }}</td>
                                        </tr>

                                        {{-- Line-level CAS under this SVC --}}
                                        @if (!empty($lineAdj))
                                            <tr>
                                                <td></td>
                                                <td colspan="4">
                                                    <strong>Line Adjustments:</strong>
                                                    <table class="table table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Group</th>
                                                                <th>Reason</th>
                                                                <th>Description</th>
                                                                <th class="text-end">Amount</th>
                                                                <th class="text-end">Qty</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($lineAdj as $adj)
                                                                <tr>
                                                                    <td>{{ $adj->group_code }}</td>
                                                                    <td>{{ $adj->reason_code }}</td>
                                                                    <td>{{ $adj->short_desc ?? '' }}</td>
                                                                    <td class="text-end">
                                                                        {{ number_format($adj->amount ?? 0, 2) }}</td>
                                                                    <td class="text-end">{{ $adj->quantity }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- 🔹 NEW: Line-level Remarks (LQ after SVC) --}}
                                        @if (!empty($lineRemarks))
                                            <tr>
                                                <td></td>
                                                <td colspan="4">
                                                    <strong>Line Remarks:</strong>
                                                    <table class="table table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Source</th> {{-- MOA or LQ (line will mostly be LQ) --}}
                                                                <th>Code</th> {{-- N30, M144, etc. --}}
                                                                <th>Description</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($lineRemarks as $rmk)
                                                                <tr>
                                                                    <td>{{ $rmk->source }}</td>
                                                                    <td>{{ $rmk->code }}</td>
                                                                    <td>
                                                                        {{ $rmk->short_desc }}
                                                                        @if ($rmk->long_desc)
                                                                            <div class="small text-muted">
                                                                                {{ $rmk->long_desc }}</div>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- CLAIM-LEVEL ADJUSTMENTS (CAS not tied to any SVC) --}}
            @php
                $claimLevelAdj = $adjBySvc['claim'] ?? [];
            @endphp

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Claim-Level Adjustments (CAS)</h5>
                </div>
                <div class="card-body">
                    @if (empty($claimLevelAdj))
                        <p class="text-muted mb-0">No claim-level adjustments for this claim.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Group</th>
                                        <th>Reason</th>
                                        <th>Description</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($claimLevelAdj as $adj)
                                        <tr>
                                            <td>{{ $adj->group_code }}</td>
                                            <td>{{ $adj->reason_code }}</td>
                                            <td>{{ $adj->short_desc ?? '' }}</td>
                                            <td class="text-end">{{ number_format($adj->amount ?? 0, 2) }}</td>
                                            <td class="text-end">{{ $adj->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 🔹 NEW: CLAIM-LEVEL REMARKS (MOA + claim-level LQ) --}}
            @php
                $claimLevelRemarks = $remarksBySvc['claim'] ?? [];
            @endphp

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Claim-Level Remarks (MOA / LQ)</h5>
                </div>
                <div class="card-body">
                    @if (empty($claimLevelRemarks))
                        <p class="text-muted mb-0">No claim-level remarks for this claim.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Source</th> {{-- MOA or LQ --}}
                                        <th>Code</th> {{-- N30, M144, MA18, etc. --}}
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($claimLevelRemarks as $rmk)
                                        <tr>
                                            <td>{{ $rmk->source }}</td>
                                            <td>{{ $rmk->code }}</td>
                                            <td>
                                                {{ $rmk->short_desc }}
                                                @if ($rmk->long_desc)
                                                    <div class="small text-muted">{{ $rmk->long_desc }}</div>
                                                @endif
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
@endsection
