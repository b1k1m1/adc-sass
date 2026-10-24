@extends('admin.admin_dashboard')

@section('title', 'Traditional Bank Reconciliation')

@section('admin')

    <div class="page-content">
        <div class="container-fluid">

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Traditional Bank Reconciliation</h5>
                </div>

                <div class="card-body">

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="GET" action="{{ route('accounts.traditional.bank.reconciliation') }}">

                        <div class="row g-3 align-items-end">

                            <div class="col-md-5">
                                <label class="form-label">
                                    Bank Account
                                </label>

                                <select name="account_id" id="account_id" class="form-select" required>

                                    <option value="">
                                        -- Select Bank --
                                    </option>

                                    @foreach ($fashbanks as $row)
                                        <option value="{{ $row->account_id }}"
                                            {{ (string) $bank_id === (string) $row->account_id ? 'selected' : '' }}>
                                            {{ $row->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">
                                    Reconciliation Date
                                </label>

                                <select name="recon_id" id="recon_id" class="form-select" required>

                                    <option value="">
                                        -- Select Reconciliation Date --
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    Run Reconciliation
                                </button>
                            </div>

                        </div>

                    </form>

                </div>
            </div>

            @if ($recon && $bank)


                <div class="card mb-3">

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-8">
                                <h4 class="mb-1">
                                    Traditional Bank Reconciliation
                                </h4>

                                <div>
                                    <strong>Bank:</strong>
                                    {{ $bank->account_name }}
                                </div>

                                @if (!empty($bank->bank_acct_no))
                                    <div>
                                        <strong>Account:</strong>
                                        {{ $bank->bank_acct_no }}
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-4 text-md-end">

                                <div>
                                    <strong>As of:</strong>
                                    {{ \Carbon\Carbon::parse($recon_date)->format('m/d/Y') }}
                                </div>

                                <a href="{{ route('accounts.traditional.bank.reconciliation.pdf', [
                                    'account_id' => $bank_id,
                                    'recon_id' => $recon_id,
                                ]) }}"
                                    class="btn btn-danger" target="_blank">

                                    <i class="fas fa-file-pdf"></i>
                                    PDF

                                </a>

                                <a href="{{ route('accounts.traditional.bank.reconciliation.excel', [
                                    'account_id' => $bank_id,
                                    'recon_id' => $recon_id,
                                ]) }}"
                                    class="btn btn-success">

                                    <i class="fas fa-file-excel"></i>
                                    Excel

                                </a>

                            </div>

                        </div>

                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body p-0">

                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="fw-bold" style="padding-left: 20px;">
                                    Balance as per Bank Book
                                </td>

                                <td class="text-end fw-bold" style="width: 130px; padding-right: 12px;">
                                    {{ number_format($book_balance, 2) }}
                                </td>
                            </tr>
                        </table>

                    </div>
                </div>

                {{-- ---------------------------------------------------------
                 DEPOSITS IN TRANSIT
            ---------------------------------------------------------- --}}

                <div class="card mb-3">

                    <div class="card-header">
                        <strong>Deposits in Transit</strong>
                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-sm table-bordered mb-0">

                                <thead>
                                    <tr>
                                        <th style="width:110px;">Date</th>
                                        <th style="width:90px;">Trans #</th>
                                        <th style="width:120px;">Reference</th>
                                        <th>Description / Payee</th>
                                        <th>Remarks</th>
                                        <th style="width:130px;" class="text-end">
                                            Amount
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse($unclearedDeposits as $row)
                                        <tr>

                                            <td>
                                                {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
                                            </td>

                                            <td>
                                                {{ $row->trans_no }}
                                            </td>

                                            <td>
                                                {{ $row->ref_no }}
                                            </td>

                                            <td>
                                                {{ $row->payto_name }}
                                            </td>

                                            <td>
                                                {{ $row->remarks }}
                                            </td>

                                            <td class="text-end">
                                                {{ number_format($row->credit_amt, 2) }}
                                            </td>

                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-3">
                                                No deposits in transit.
                                            </td>
                                        </tr>
                                    @endforelse

                                </tbody>

                                <tfoot>

                                    <tr class="fw-bold">

                                        <td colspan="5" class="text-end">
                                            Total Deposits in Transit
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($totalDepositsInTransit, 2) }}
                                        </td>

                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                    </div>

                </div>


                {{-- ---------------------------------------------------------
                 OUTSTANDING PAYMENTS
            ---------------------------------------------------------- --}}

                <div class="card mb-3">

                    <div class="card-header">
                        <strong>Outstanding Payments / Checks</strong>
                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-sm table-bordered mb-0">

                                <thead>

                                    <tr>
                                        <th style="width:110px;">Date</th>
                                        <th style="width:90px;">Trans #</th>
                                        <th style="width:100px;">Check #</th>
                                        <th style="width:120px;">Reference</th>
                                        <th>Payee</th>
                                        <th>Remarks</th>
                                        <th style="width:130px;" class="text-end">
                                            Amount
                                        </th>
                                    </tr>

                                </thead>

                                <tbody>

                                    @forelse($unclearedPayments as $row)
                                        <tr>

                                            <td>
                                                {{ \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') }}
                                            </td>

                                            <td>
                                                {{ $row->trans_no }}
                                            </td>

                                            <td>
                                                {{ $row->check_no }}
                                            </td>

                                            <td>
                                                {{ $row->ref_no }}
                                            </td>

                                            <td>
                                                {{ $row->payto_name }}
                                            </td>

                                            <td>
                                                {{ $row->remarks }}
                                            </td>

                                            <td class="text-end">
                                                {{ number_format($row->debit_amt, 2) }}
                                            </td>

                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-3">
                                                No outstanding payments.
                                            </td>
                                        </tr>
                                    @endforelse

                                </tbody>

                                <tfoot>

                                    <tr class="fw-bold">

                                        <td colspan="6" class="text-end">
                                            Total Outstanding Payments
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($totalOutstandingPayments, 2) }}
                                        </td>

                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                    </div>

                </div>


                {{-- ---------------------------------------------------------
                 FINAL RECONCILIATION
            ---------------------------------------------------------- --}}

                <div class="card">

                    <div class="card-header">
                        <strong>Reconciliation Summary</strong>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-lg-7 col-md-9 ms-auto">

                                <table class="table table-sm">

                                    <tbody>

                                        <tr>

                                            <td>
                                                <strong>
                                                    Balance as per Bank Book
                                                </strong>
                                            </td>

                                            <td class="text-end">
                                                {{ number_format($book_balance, 2) }}
                                            </td>

                                        </tr>

                                        <tr>

                                            <td>
                                                Add:
                                                Outstanding Payments / Checks
                                            </td>

                                            <td class="text-end">
                                                {{ number_format($totalOutstandingPayments, 2) }}
                                            </td>

                                        </tr>

                                        <tr>

                                            <td>
                                                Less:
                                                Deposits in Transit
                                            </td>

                                            <td class="text-end">
                                                ({{ number_format($totalDepositsInTransit, 2) }})
                                            </td>

                                        </tr>

                                        <tr class="fw-bold border-top">

                                            <td>
                                                Calculated Bank Statement Balance
                                            </td>

                                            <td class="text-end">
                                                {{ number_format($calculatedStatementBalance, 2) }}
                                            </td>

                                        </tr>

                                        <tr>

                                            <td>
                                                Actual Bank Statement Closing Balance
                                            </td>

                                            <td class="text-end">
                                                {{ number_format($statement_closing_balance, 2) }}
                                            </td>

                                        </tr>

                                        <tr
                                            class="fw-bold
                                        {{ abs($difference) < 0.005 ? 'table-success' : 'table-danger' }}">

                                            <td>
                                                Difference
                                            </td>

                                            <td class="text-end">
                                                {{ number_format($difference, 2) }}
                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            @endif

        </div>
    </div>

@endsection


@section('scripts')

    <script>
        $(document).ready(function() {

            const bankSelect = $('#account_id');
            const reconSelect = $('#recon_id');

            const selectedReconId =
                @json($recon_id);

            function loadReconciliationDates(
                bankId,
                selectedId = null
            ) {

                reconSelect.empty();

                reconSelect.append(
                    '<option value="">-- Select Reconciliation Date --</option>'
                );

                if (!bankId) {
                    return;
                }

                reconSelect.prop('disabled', true);

                $.ajax({

                    url: "{{ route('accounts.traditional.bank.reconciliation.dates') }}",

                    type: "GET",

                    data: {
                        bank_id: bankId
                    },

                    success: function(data) {

                        $.each(data, function(index, row) {

                            let selected = '';

                            if (
                                selectedId &&
                                String(selectedId) === String(row.recon_id)
                            ) {
                                selected = 'selected';
                            }

                            let label =
                                row.display_date +
                                ' - Statement Balance $' +
                                Number(
                                    row.closing_balance
                                ).toLocaleString(
                                    'en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }
                                );

                            reconSelect.append(
                                '<option value="' +
                                row.recon_id +
                                '" ' +
                                selected +
                                '>' +
                                label +
                                '</option>'
                            );
                        });

                    },

                    error: function() {

                        alert(
                            'Unable to load reconciliation dates.'
                        );

                    },

                    complete: function() {

                        reconSelect.prop(
                            'disabled',
                            false
                        );

                    }

                });
            }


            bankSelect.on('change', function() {

                loadReconciliationDates(
                    $(this).val()
                );

            });


            if (bankSelect.val()) {

                loadReconciliationDates(
                    bankSelect.val(),
                    selectedReconId
                );

            }

        });
    </script>

@endsection
