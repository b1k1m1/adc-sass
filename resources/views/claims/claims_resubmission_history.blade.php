@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-12">

                <div class="card">

                    <div class="card-body">

                        {{-- =========================================================
                             Header
                        ========================================================== --}}

                        <div class="d-flex justify-content-between align-items-start mb-3">

                            <div>

                                <h4 class="card-title mb-1">
                                    Claim Resubmission History
                                </h4>

                                <p class="text-muted mb-0">
                                    History of original and resubmitted claim numbers and batches.
                                </p>

                            </div>


                            <div class="d-flex gap-2">

                                <a
                                    href="{{ route('claims.resubmission.ready') }}"
                                    class="btn btn-primary"
                                >
                                    Resubmission 837P Preview
                                </a>


                                <a
                                    href="{{ route('claims.resubmit.batches.index') }}"
                                    class="btn btn-secondary"
                                >
                                    Batch Resubmission
                                </a>

                            </div>

                        </div>


                        <hr>


                        {{-- =========================================================
                             Summary
                        ========================================================== --}}

                        @php

                            $totalHistory =
                                $history->count();

                            $completedHistory =
                                $history
                                    ->whereNotNull('new_batch_id')
                                    ->count();

                            $pendingHistory =
                                $history
                                    ->whereNull('new_batch_id')
                                    ->count();

                        @endphp


                        <div class="row mb-4">

                            <div class="col-md-3 mb-2">

                                <div class="border rounded p-3">

                                    <div class="text-muted">
                                        Total Resubmissions
                                    </div>

                                    <div class="fs-4 fw-bold">
                                        {{ number_format($totalHistory) }}
                                    </div>

                                </div>

                            </div>


                            <div class="col-md-3 mb-2">

                                <div class="border rounded p-3">

                                    <div class="text-muted">
                                        Completed
                                    </div>

                                    <div class="fs-4 fw-bold text-success">
                                        {{ number_format($completedHistory) }}
                                    </div>

                                </div>

                            </div>


                            <div class="col-md-3 mb-2">

                                <div class="border rounded p-3">

                                    <div class="text-muted">
                                        Pending 837P
                                    </div>

                                    <div class="fs-4 fw-bold text-warning">
                                        {{ number_format($pendingHistory) }}
                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- =========================================================
                             History Table
                        ========================================================== --}}

                        <div class="table-responsive">

                            <table
                                id="resubmissionHistoryTable"
                                class="table table-bordered table-striped align-middle"
                                style="width:100%;"
                            >

                                <thead>

                                    <tr>

                                        <th>
                                            Date
                                        </th>

                                        <th>
                                            Member ID
                                        </th>

                                        <th>
                                            Member Name
                                        </th>

                                        <th>
                                            Service From
                                        </th>

                                        <th>
                                            Service To
                                        </th>

                                        <th>
                                            Old Claim #
                                        </th>

                                        <th>
                                            New Claim #
                                        </th>

                                        <th>
                                            Old Batch
                                        </th>

                                        <th>
                                            New Batch
                                        </th>

                                        <th>
                                            Type
                                        </th>

                                        <th>
                                            Reason
                                        </th>

                                        <th>
                                            Current Status
                                        </th>

                                        <th>
                                            Created By
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @forelse ($history as $row)

                                        <tr>

                                            {{-- Date --}}

                                            <td
                                                data-order="{{ $row->created_at }}"
                                            >

                                                @if ($row->created_at)

                                                    {{ \Carbon\Carbon::parse(
                                                        $row->created_at
                                                    )->format('m/d/Y h:i A') }}

                                                @endif

                                            </td>


                                            {{-- Member ID --}}

                                            <td>

                                                {{ $row->ParticipantId ?? '' }}

                                            </td>


                                            {{-- Member Name --}}

                                            <td>

                                                @php

                                                    $memberName = trim(
                                                        ($row->member_lastname ?? '') .
                                                        ', ' .
                                                        ($row->member_firstname ?? '')
                                                    );

                                                    $memberName = trim(
                                                        $memberName,
                                                        ', '
                                                    );

                                                @endphp


                                                @if ($memberName !== '')

                                                    {{ $memberName }}

                                                @else

                                                    <span class="text-muted">
                                                        Member not found
                                                    </span>

                                                @endif

                                            </td>


                                            {{-- Service From --}}

                                            <td
                                                data-order="{{ $row->service_from }}"
                                            >

                                                @if ($row->service_from)

                                                    {{ \Carbon\Carbon::parse(
                                                        $row->service_from
                                                    )->format('m/d/Y') }}

                                                @endif

                                            </td>


                                            {{-- Service To --}}

                                            <td
                                                data-order="{{ $row->service_to }}"
                                            >

                                                @if ($row->service_to)

                                                    {{ \Carbon\Carbon::parse(
                                                        $row->service_to
                                                    )->format('m/d/Y') }}

                                                @endif

                                            </td>


                                            {{-- Old Claim Number --}}

                                            <td>

                                                <strong>
                                                    {{ $row->old_claim_no }}
                                                </strong>

                                            </td>


                                            {{-- New Claim Number --}}

                                            <td>

                                                <strong class="text-primary">
                                                    {{ $row->new_claim_no }}
                                                </strong>

                                            </td>


                                            {{-- Old Batch --}}

                                            <td>

                                                @if ($row->old_batch_id)

                                                    <div>

                                                        <strong>
                                                            Batch {{ $row->old_batch_id }}
                                                        </strong>

                                                    </div>


                                                    @if ($row->old_batch_filename)

                                                        <small class="text-muted">

                                                            {{ $row->old_batch_filename }}

                                                        </small>

                                                    @endif

                                                @else

                                                    <span class="text-muted">
                                                        N/A
                                                    </span>

                                                @endif

                                            </td>


                                            {{-- New Batch --}}

                                            <td>

                                                @if ($row->new_batch_id)

                                                    <div>

                                                        <strong class="text-success">
                                                            Batch {{ $row->new_batch_id }}
                                                        </strong>

                                                    </div>


                                                    @if ($row->new_batch_filename)

                                                        <small class="text-muted">

                                                            {{ $row->new_batch_filename }}

                                                        </small>

                                                    @endif

                                                @else

                                                    <span class="badge bg-warning text-dark">
                                                        Pending
                                                    </span>

                                                @endif

                                            </td>


                                            {{-- Resubmission Type --}}

                                            <td>

                                                @if ($row->resubmit_type === 'full_batch')

                                                    <span class="badge bg-danger">
                                                        Full Batch
                                                    </span>

                                                @elseif ($row->resubmit_type === 'selected')

                                                    <span class="badge bg-primary">
                                                        Selected
                                                    </span>

                                                @else

                                                    <span class="badge bg-secondary">

                                                        {{ $row->resubmit_type ?? 'N/A' }}

                                                    </span>

                                                @endif

                                            </td>


                                            {{-- Reason --}}

                                            <td>

                                                {{ $row->reason ?? '' }}

                                            </td>


                                            {{-- Current Claim Status --}}

                                            <td>

                                                @php

                                                    $status = strtolower(
                                                        $row->current_status ?? ''
                                                    );

                                                @endphp


                                                @if ($status === 'sent')

                                                    <span class="badge bg-success">
                                                        Sent
                                                    </span>

                                                @elseif ($status === 'ready')

                                                    <span class="badge bg-primary">
                                                        Ready
                                                    </span>

                                                @elseif ($status === 'rejected')

                                                    <span class="badge bg-danger">
                                                        Rejected
                                                    </span>

                                                @elseif ($status === 'paid')

                                                    <span class="badge bg-success">
                                                        Paid
                                                    </span>

                                                @elseif ($status === 'draft')

                                                    <span class="badge bg-warning text-dark">
                                                        Draft
                                                    </span>

                                                @else

                                                    <span class="badge bg-secondary">

                                                        {{ $row->current_status ?? 'N/A' }}

                                                    </span>

                                                @endif

                                            </td>


                                            {{-- Created By --}}

                                            <td>

                                                {{ $row->created_by ?? '' }}

                                            </td>

                                        </tr>


                                    @empty

                                        <tr>

                                            <td
                                                colspan="13"
                                                class="text-center text-muted"
                                            >
                                                No claim resubmission history found.
                                            </td>

                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@section('scripts')

<script>

$(document).ready(function () {

    $('#resubmissionHistoryTable').DataTable({

        pageLength: 50,

        lengthMenu: [
            [25, 50, 100, 150, 250, -1],
            [25, 50, 100, 150, 250, 'All']
        ],

        /*
         * Default:
         * newest resubmission first
         */

        order: [
            [0, 'desc']
        ],

        autoWidth: false,

        responsive: false,

        columnDefs: [

            /*
             * Reason column can become long,
             * so keep it from forcing excessive width.
             */

            {
                targets: 10,
                width: '180px'
            },

            /*
             * Batch columns need a little extra room
             * because they contain filename underneath.
             */

            {
                targets: [7, 8],
                width: '220px'
            }

        ]

    });

});

</script>

@endsection