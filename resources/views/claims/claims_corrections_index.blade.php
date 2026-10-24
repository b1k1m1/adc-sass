@extends('admin.admin_dashboard')

@section('title', 'Correct / Void Submitted Claims')

@section('admin')

    <div class="page-content">

        <div class="container-fluid">

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h5 class="mb-0">
                                Correct / Void Submitted Claims
                            </h5>

                            <small>
                                Select a previously submitted claim for correction
                                or complete void.
                            </small>

                        </div>

                        <a href="{{ route('claims.resubmission.ready') }}" class="btn btn-light btn-sm">

                            Resubmission Ready

                        </a>

                    </div>

                </div>


                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif


                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif


                    <div class="alert alert-info">

                        <strong>Correct / Replace:</strong>
                        Use when a previously accepted payer claim needs to be changed.

                        <br>

                        <strong>Void:</strong>
                        Use when the complete previously accepted payer claim must be cancelled.

                        <br>

                        <strong>Note:</strong>
                        The original claim number will be retained.

                    </div>


                    <div class="table-responsive">

                        <table id="claimsTable" class="table table-bordered table-striped table-sm align-middle">

                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        Claim #
                                    </th>

                                    <th>
                                        Member
                                    </th>

                                    <th>
                                        Participant ID
                                    </th>

                                    <th>
                                        Payer
                                    </th>

                                    <th>
                                        DOS From
                                    </th>

                                    <th>
                                        DOS To
                                    </th>

                                    <th class="text-center">
                                        Lines
                                    </th>

                                    <th class="text-end">
                                        Amount
                                    </th>

                                    <th>
                                        Existing Payer ICN
                                    </th>

                                    <th class="text-center">
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach ($claims as $claim)
                                    @php

                                        $member = $members[$claim->ParticipantId] ?? null;

                                        $memberName = $member
                                            ? trim(($member->LastName ?? '') . ', ' . ($member->FirstName ?? ''))
                                            : 'Member Not Found';

                                        $isPending = in_array($claim->claim_no, $pending);
                                        $isVoided = in_array($claim->claim_no, $voided);

                                    @endphp


                                    <tr>

                                        <td>
                                            <strong>
                                                {{ $claim->claim_no }}
                                            </strong>
                                        </td>


                                        <td>
                                            {{ $memberName }}
                                        </td>


                                        <td>
                                            {{ $claim->ParticipantId }}
                                        </td>


                                        <td>

                                            {{ $claim->payer_fullname }}

                                            @if ($claim->payer_id)
                                                <div class="small text-muted">
                                                    {{ $claim->payer_id }}
                                                </div>
                                            @endif

                                        </td>


                                        <td>
                                            {{ $claim->service_from }}
                                        </td>


                                        <td>
                                            {{ $claim->service_to }}
                                        </td>


                                        <td class="text-center">
                                            {{ $claim->line_count }}
                                        </td>


                                        <td class="text-end">

                                            ${{ number_format($claim->total_amount, 2) }}

                                        </td>


                                        <td>
                                            {{ $claim->payer_claim_number ?? '' }}
                                        </td>


                                        <td class="text-center" style="white-space: nowrap;">

                                            @if ($isPending)
                                                <span class="badge bg-warning text-dark">
                                                    Pending
                                                </span>
                                            @elseif($isVoided)
                                                <span class="badge bg-danger">
                                                    Voided
                                                </span>
                                            @else
                                                <a href="{{ route('claims.corrections.edit', $claim->claim_no) }}"
                                                    class="btn btn-sm btn-primary">

                                                    Correct

                                                </a>

                                                <a href="{{ route('claims.void.edit', $claim->claim_no) }}"
                                                    class="btn btn-sm btn-danger">

                                                    Void

                                                </a>
                                            @endif
                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection


@section('scripts')

    <script>
        $(document).ready(function() {

            $('#claimsTable').DataTable({

                pageLength: 25,

                lengthMenu: [
                    [25, 50, 100, -1],
                    [25, 50, 100, 'All']
                ],

                order: [
                    [5, 'desc']
                ],

                columnDefs: [{
                    targets: 9,
                    orderable: false,
                    searchable: false
                }]

            });

        });
    </script>

@endsection
