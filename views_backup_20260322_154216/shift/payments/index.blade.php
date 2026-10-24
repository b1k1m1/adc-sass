@extends('admin.admin_dashboard')
@section('title', 'Shift Payments')
@section('admin')

<div class="page-content">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-2">
      <h4 class="mb-0">Shift Payments</h4>
      <a href="{{ route('shift.payments.create') }}" class="btn btn-primary">
        + Add Payment
      </a>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table id="paymTable" class="table table-bordered table-striped w-100">
            <thead>
              <tr>
                <th>Trans#</th>
                <th>Date</th>
                <th>Party</th>
                <th>Paid To Name</th>
                <th>Debit</th>
                <th>%</th>
                <th>Net Debit</th>
                <th>Pay Type</th>
                <th>Check/Card #</th>
                <th>Remarks</th>
                <th style="width:90px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  $('#paymTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ route('shift.payments.data') }}",
    order: [[0, 'desc']],
    columns: [
      { data: 'trans_no', name: 'trans_no' },
      { data: 'trans_dt', name: 'trans_dt' },
      { data: 'party_cd', name: 'party_cd' },
      { data: 'payto_name', name: 'payto_name' },
      { data: 'debit_amt', name: 'debit_amt' },
      { data: 'per', name: 'per' },
      { data: 'net_debit', name: 'net_debit' },
      { data: 'pay_type', name: 'pay_type' },
      { data: 'check_no', name: 'check_no' },
      { data: 'remarks', name: 'remarks' },
      { data: 'action', name: 'action', orderable: false, searchable: false },
    ]
  });
});
</script>

@endsection
