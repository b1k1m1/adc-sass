@extends('admin.admin_dashboard')

@section('title', 'Bank Payment Register')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Bank Payment Register</h5>

                @if(request()->filled('bank_id') && $rows->count() > 0)
                    <a href="{{ route('bank.payment.register.pdf', request()->query()) }}"
                       target="_blank"
                       class="btn btn-danger btn-sm">
                        Export PDF
                    </a>
                @endif
            </div>

            <div class="card-body">

                <form method="GET" action="{{ route('bank.payment.register') }}">
                    <div class="row mb-3">

                        <div class="col-md-4">
                            <label class="form-label">Bank</label>
                            <select name="bank_id" class="form-select" required>
                                <option value="">Select Bank</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->account_id }}"
                                        {{ $bankId == $bank->account_id ? 'selected' : '' }}>
                                        {{ $bank->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="text"
                                   name="start_date"
                                   id="start_date"
                                   class="form-control datepicker"
                                   value="{{ $startDate }}"
                                   placeholder="mm/dd/yyyy"
                                   autocomplete="off"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="text"
                                   name="end_date"
                                   id="end_date"
                                   class="form-control datepicker"
                                   value="{{ $endDate }}"
                                   placeholder="mm/dd/yyyy"
                                   autocomplete="off"
                                   required>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                Load
                            </button>
                        </div>

                    </div>
                </form>

                @if(request()->filled('bank_id'))

                    <hr>

                    <div class="mb-2">
                        <strong>Total Records:</strong> {{ $rows->count() }}

                        <span class="ms-4">
                            <strong>Total Payment:</strong>
                            {{ number_format($rows->sum('debit_amt'), 2) }}
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table id="bankPaymentTable" class="table table-bordered table-striped table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Trans No</th>
                                    <th>Trans Date</th>
                                    <th>Party Code</th>
                                    <th>Party Name</th>
                                    <th>Pay To Name</th>
                                    <th>Check No</th>
                                    <th>Check Date</th>
                                    <th>Ref No</th>
                                    <th>Remarks</th>
                                    <th class="text-end">Debit Amount</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>{{ $row->trans_no }}</td>

                                        <td data-order="{{ $row->trans_dt ? \Carbon\Carbon::parse($row->trans_dt)->format('Y-m-d') : '' }}">
                                            {{ $row->trans_dt ? \Carbon\Carbon::parse($row->trans_dt)->format('m/d/Y') : '' }}
                                        </td>

                                        <td>{{ $row->party_cd }}</td>
                                        <td>{{ $row->party_nm }}</td>
                                        <td>{{ $row->payto_name }}</td>
                                        <td>{{ $row->check_no }}</td>

                                        <td data-order="{{ $row->check_dt ? \Carbon\Carbon::parse($row->check_dt)->format('Y-m-d') : '' }}">
                                            {{ $row->check_dt ? \Carbon\Carbon::parse($row->check_dt)->format('m/d/Y') : '' }}
                                        </td>

                                        <td>{{ $row->ref_no }}</td>
                                        <td>{{ $row->remarks }}</td>

                                        <td class="text-end" data-order="{{ $row->debit_amt }}">
                                            {{ number_format($row->debit_amt, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            No payment transactions found for selected criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if($rows->count() > 0)
                                <tfoot>
                                    <tr class="fw-bold table-light">
                                        <td colspan="9" class="text-end">Total</td>
                                        <td class="text-end">
                                            {{ number_format($rows->sum('debit_amt'), 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif

                        </table>
                    </div>

                @endif

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function () {

        flatpickr(".datepicker", {
            dateFormat: "m/d/Y",
            allowInput: true
        });

        $('#bankPaymentTable').DataTable({
            pageLength: 50,
            lengthMenu: [
                [50, 100, 200, 300, 400, -1],
                [50, 100, 200, 300, 400, 'All']
            ],
            ordering: true,
            order: [[1, 'asc']],
            scrollX: true
        });

    });
</script>
@endpush
