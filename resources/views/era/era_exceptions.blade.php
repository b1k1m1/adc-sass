@extends('admin.admin_dashboard')

@section('title', 'ERA Exceptions')

@section('admin')

    <div class="page-content">

        <div class="container-fluid">

            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">
                        ERA Exceptions
                    </h5>

                    <a href="{{ route('era.index') }}" class="btn btn-secondary btn-sm">

                        Back to ERA List

                    </a>

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-sm align-middle">

                            <thead class="table-light">

                                <tr>

                                    <th>ERA</th>
                                    <th>Claim #</th>
                                    <th>Member</th>
                                    <th>Service From</th>
                                    <th>Service To</th>
                                    <th class="text-end">Charge</th>
                                    <th class="text-end">Paid</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th width="140">Action</th>

                                </tr>

                            </thead>

                            <tbody>

                                @forelse($exceptions as $row)
                                    <tr>

                                        <td>
                                            {{ $row->era_file_id }}
                                        </td>

                                        <td>
                                            {{ $row->normalized_claim_no }}
                                        </td>

                                        <td>
                                            {{ $row->member_id }}

                                            @if ($row->LastName || $row->FirstName)
                                                <br>
                                                <small class="text-muted">
                                                    {{ $row->LastName }},
                                                    {{ $row->FirstName }}
                                                </small>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($row->service_from)
                                                {{ \Carbon\Carbon::parse($row->service_from)->format('m/d/Y') }}
                                            @endif
                                        </td>

                                        <td>
                                            @if ($row->service_to)
                                                {{ \Carbon\Carbon::parse($row->service_to)->format('m/d/Y') }}
                                            @endif
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($row->total_charge ?? 0, 2) }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($row->total_paid ?? 0, 2) }}
                                        </td>

                                        <td style="white-space:normal; min-width:300px;">
                                            {{ $row->reason }}
                                        </td>

                                        <td>

                                            @if ($row->resolved_yn === 'Y')
                                                <span class="badge bg-success">
                                                    Resolved
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    Open
                                                </span>
                                            @endif

                                        </td>

                                        <td>

                                            <a href="{{ route('era.exception.show', $row->id) }}"
                                                class="btn btn-sm btn-outline-primary">

                                                View

                                            </a>

                                            @if ($row->resolved_yn !== 'Y')
                                                <form method="POST"
                                                    action="{{ route('era.exception.cross-match', $row->id) }}"
                                                    class="d-inline">

                                                    @csrf

                                                    <button type="submit" class="btn btn-sm btn-outline-warning"
                                                        onclick="return confirm('Try to match unmatched ERA service lines to other ADC claims?');">

                                                        Cross Match

                                                    </button>

                                                </form>
                                            @endif

                                            @if ($row->resolved_yn !== 'Y')
                                                <form method="POST"
                                                    action="{{ route('era.exception.resolve', $row->id) }}"
                                                    class="d-inline">

                                                    @csrf

                                                    <button type="submit" class="btn btn-sm btn-outline-success">

                                                        Resolve

                                                    </button>

                                                </form>
                                            @endif

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="10" class="text-center text-muted">

                                            No ERA exceptions found.

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

@endsection
