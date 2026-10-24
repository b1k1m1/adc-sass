@extends('admin.admin_dashboard')
@section('title', 'Shift Deposits')
@section('admin')

<div class="page-content">
  <div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-2">
      <h4 class="mb-0">Shift Deposits</h4>
      <a href="{{ route('shift.deposits.create') }}" class="btn btn-primary">
        + Add Deposit
      </a>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table id="depsTable" class="table table-bordered table-striped w-100">
            <thead>
              <tr>
                <th>Trans#</th>
                <th>Date</th>
                <th>From Name</th>
                <th>Credit</th>
                <th>%</th>
                <th>Net Credit</th>
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
  $('#depsTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ route('shift.deposits.data') }}",
    order: [[0, 'desc']],
    columns: [
      { data: 'trans_no', name: 'trans_no' },
      { data: 'trans_dt', name: 'trans_dt' },
      { data: 'payto_name', name: 'payto_name' },
      { data: 'credit_amt', name: 'credit_amt' },
      { data: 'per', name: 'per' },
      { data: 'net_credit', name: 'net_credit' },
      { data: 'remarks', name: 'remarks' },
      { data: 'action', name: 'action', orderable: false, searchable: false },
    ]
  });
});
</script>

@endsection
