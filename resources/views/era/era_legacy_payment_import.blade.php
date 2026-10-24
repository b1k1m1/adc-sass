@extends('admin.admin_dashboard')

@section('title', 'Legacy ERA Payment Import')

@section('admin')

    <div class="page-content">

        <div class="container-fluid">

            <div class="card mb-3">

                <div class="card-header">

                    <h5 class="mb-0">
                        Legacy ERA Payment Import
                    </h5>

                </div>


                <div class="card-body">

                    @if (session('message'))
                        <div
                            class="alert alert-{{ session('alert-type') == 'danger' ? 'danger' : (session('alert-type') == 'warning' ? 'warning' : 'success') }}">

                            {{ session('message') }}

                        </div>
                    @endif


                    <form method="POST" action="{{ route('era.legacy.payment.import.process') }}"
                        enctype="multipart/form-data">

                        @csrf


                        <div class="row align-items-end">

                            <div class="col-md-6">

                                <label class="form-label">
                                    Clearinghouse ERA CSV
                                </label>

                                <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>

                            </div>


                            <div class="col-md-3">

                                <button type="submit" class="btn btn-primary">

                                    Import & Match Claims

                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>


            <div class="row mb-3">

                <div class="col-md-4">

                    <div class="card">

                        <div class="card-body">

                            <div class="text-muted">
                                Total
                            </div>

                            <h4>
                                {{ $total }}
                            </h4>

                        </div>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="card">

                        <div class="card-body">

                            <div class="text-muted">
                                Matched
                            </div>

                            <h4 class="text-success">
                                {{ $matched }}
                            </h4>

                        </div>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="card">

                        <div class="card-body">

                            <div class="text-muted">
                                Unmatched
                            </div>

                            <h4 class="text-danger">
                                {{ $unmatched }}
                            </h4>

                        </div>

                    </div>

                </div>

            </div>

            <form method="POST" action="{{ route('era.legacy.payment.process.selected') }}"
                onsubmit="return confirm(
                'Post the selected legacy ERA payments to the matched ADC claims?'
            );">

                @csrf


                <div class="row mb-3 align-items-end">

                    <div class="col-md-3">

                        <label class="form-label">
                            Payment / EFT Date
                        </label>

                        <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date') }}"
                            required>

                    </div>


                    <div class="col-md-4">

                        <button type="submit" class="btn btn-success">

                            <i class="fas fa-dollar-sign"></i>

                            Process Selected Payments

                        </button>

                    </div>

                </div>

                <div class="card">

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-bordered table-sm align-middle">

                                <thead>

                                    <tr>
                                        <th style="width:40px;">
                                            <input type="checkbox" id="selectAllPayments">
                                        </th>

                                        <th>PCN</th>
                                        <th>Member</th>
                                        <th>Insurance #</th>
                                        <th>Participant</th>
                                        <th>Service Dates</th>
                                        <th>Procedure</th>
                                        <th>Charge</th>
                                        <th>Paid</th>
                                        <th>Claim #</th>
                                        <th>Payer ICN</th>
                                        <th>Status</th>
                                        <th>Message</th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach ($rows as $row)
                                        <tr>
                                            <td class="text-center">

                                                @if (in_array($row->match_status, ['MATCHED', 'MULTI_MATCHED'], true) && $row->processed_yn !== 'Y')
                                                    <input type="checkbox" name="payment_ids[]" value="{{ $row->id }}"
                                                        class="payment-checkbox">
                                                @endif

                                            </td>

                                            <td>
                                                {{ $row->pcn }}
                                            </td>

                                            <td>
                                                {{ $row->last_name }},
                                                {{ $row->first_name }}
                                            </td>

                                            <td>
                                                {{ $row->insurance_no }}
                                            </td>

                                            <td>
                                                {{ $row->ParticipantId }}
                                            </td>

                                            <td>
                                                {{ $row->service_from }}
                                                -
                                                {{ $row->service_to }}
                                            </td>

                                            <td>
                                                {{ $row->proc_code }}
                                            </td>

                                            <td>
                                                ${{ number_format($row->total_charge, 2) }}
                                            </td>

                                            <td>
                                                ${{ number_format($row->amount_paid, 2) }}
                                            </td>

                                            <td class="fw-bold">
                                                {{ $row->claim_no }}
                                            </td>

                                            <td>
                                                {{ $row->payer_icn }}
                                            </td>

                                            <td>
                                                @if ($row->match_status === 'MATCHED')
                                                    <span class="badge bg-success">
                                                        MATCHED
                                                    </span>
                                                @elseif($row->match_status === 'MULTI_MATCHED')
                                                    <span class="badge bg-info">
                                                        MULTI MATCHED
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        UNMATCHED
                                                    </span>
                                                @endif

                                            </td>

                                            <td>
                                                {{ $row->match_message }}
                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>

            </form>

        </div>

    </div>

    </div>

    </div>

    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const selectAll =
                document.getElementById(
                    'selectAllPayments'
                );

            if (!selectAll) {
                return;
            }


            selectAll.addEventListener(
                'change',
                function() {

                    document
                        .querySelectorAll(
                            '.payment-checkbox'
                        )
                        .forEach(
                            function(checkbox) {

                                checkbox.checked =
                                    selectAll.checked;
                            }
                        );
                }
            );

        });
    </script>
@endsection
