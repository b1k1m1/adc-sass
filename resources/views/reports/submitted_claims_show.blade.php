@extends('admin.admin_dashboard')

@section('title', 'Submitted Claims Report')

@section('admin')

    <div class="page-content">

        <div class="container-fluid">

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-body">

                            {{-- Header --}}

                            <div class="d-flex justify-content-between align-items-start mb-3">

                                <div>

                                    <h4 class="card-title mb-1">
                                        Submitted Claims Report
                                    </h4>

                                    <div class="text-muted">

                                        <div>
                                            <strong>Batch ID:</strong>
                                            {{ $batch->id }}
                                        </div>

                                        <div>
                                            <strong>Batch Date:</strong>

                                            @if ($batch->batch_timestamp)
                                                {{ \Carbon\Carbon::parse($batch->batch_timestamp)->format('m/d/Y h:i A') }}
                                            @endif

                                        </div>

                                        <div>
                                            <strong>837P File:</strong>
                                            {{ $batch->edi_filename }}
                                        </div>

                                        <div>
                                            <strong>Total Claims:</strong>
                                            {{ number_format($batch->total_claims ?? 0) }}
                                        </div>

                                        <div>
                                            <strong>Total Amount:</strong>

                                            ${{ number_format($batch->total_amount ?? 0, 2) }}

                                        </div>

                                    </div>

                                </div>


                                <div class="d-flex gap-2">

                                    <a href="{{ route('reports.submitted.claims.pdf', $batch->id) }}"
                                        class="btn btn-danger" target="_blank">
                                        Download PDF
                                    </a>


                                    <button type="button" class="btn btn-secondary" onclick="window.print();">
                                        Print
                                    </button>


                                    <a href="{{ route('reports.submitted.claims.index') }}"
                                        class="btn btn-outline-secondary">
                                        Back
                                    </a>

                                </div>

                            </div>


                            <hr>


                            {{-- Claims Table --}}

                            <div class="table-responsive">

                                <table id="submittedClaimsDetailTable"
                                    class="table table-bordered table-striped align-middle" style="width:100%;">

                                    <thead>

                                        <tr>

                                            <th>
                                                Claim #
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

                                            <th class="text-end">
                                                Units
                                            </th>

                                            <th class="text-end">
                                                Amount
                                            </th>

                                            <th>
                                                Payer
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        @forelse ($claims as $claim)

                                            <tr>

                                                <td>

                                                    <strong>
                                                        {{ $claim->submitted_claim_no }}
                                                    </strong>

                                                </td>


                                                <td>
                                                    {{ $claim->ParticipantId }}
                                                </td>


                                                <td>

                                                    @php

                                                        $memberName = trim(
                                                            ($claim->member_lastname ?? '') .
                                                                ', ' .
                                                                ($claim->member_firstname ?? ''),
                                                        );

                                                        $memberName = trim($memberName, ', ');
                                                    @endphp


                                                    {{ $memberName }}

                                                </td>


                                                <td data-order="{{ $claim->service_from }}">

                                                    @if ($claim->service_from)
                                                        {{ \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') }}
                                                    @endif

                                                </td>


                                                <td data-order="{{ $claim->service_to }}">

                                                    @if ($claim->service_to)
                                                        {{ \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') }}
                                                    @endif

                                                </td>


                                                <td class="text-end">

                                                    {{ $claim->units ?? '' }}

                                                </td>


                                                <td class="text-end">

                                                    ${{ number_format($claim->total_amount ?? 0, 2) }}

                                                </td>


                                                <td>

                                                    {{ $claim->payer_fullname ?? ($claim->insurance_name ?? '') }}

                                                </td>

                                            </tr>


                                        @empty

                                            <tr>

                                                <td colspan="8" class="text-center text-muted">
                                                    No claims found for this batch.
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
        $(document).ready(function() {

            $('#submittedClaimsDetailTable').DataTable({

                pageLength: 50,

                lengthMenu: [
                    [25, 50, 100, 150, 250, -1],
                    [25, 50, 100, 150, 250, 'All']
                ],

                order: [
                    [3, 'asc']
                ],

                autoWidth: false,

                responsive: false

            });

        });
    </script>

@endsection
