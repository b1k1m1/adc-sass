@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <h4>Transaction Details: {{ $account->party_nm ?? $account->party_cd }}</h4>
    <h6>From {{ \Carbon\Carbon::parse($startDate)->format('m/d/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('m/d/Y') }}</h6>
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Reference</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->trans_dt }}</td>
                    <td>{{ $row->trans_type }}</td>
                    <td>{{ $row->ref_no }}</td>
                    <td class="text-end">{{ number_format($row->debit_amt, 2) }}</td>
                    <td class="text-end">{{ number_format($row->credit_amt, 2) }}</td>
                    <td>{{ $row->payto_name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No transactions found.</td>
                </tr>
            @endforelse
             <tr class="fw-bold table-secondary">
                <td colspan="3" class="text-end">Total</td>
                <td class="text-end">{{ number_format($totalDebit, 2) }}</td>
                <td class="text-end">{{ number_format($totalCredit, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
