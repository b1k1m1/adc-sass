@extends('admin.admin_dashboard')
@section('title', 'SHIFT 2 Receipts & Payments')
@section('admin')

<div class="page-content">
  <div class="container-fluid">

    <h4 class="mb-3">SHIFT 2 - Receipts & Payments Ledger</h4>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('reports.shift2.run') }}">
          @csrf
          <div class="row g-3 align-items-end" style="max-width: 760px;">
            <div class="col-md-3">
                <label class="form-label">From</label>
                <input type="date" name="adt_start_date"
                        class="form-control"
                        value="{{ old('adt_start_date') }}"
                        required>
                @error('adt_start_date') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">To</label>
                <input type="date" name="adt_end_date"
                        class="form-control"
                        value="{{ old('adt_end_date') }}"
                        required>
                @error('adt_end_date') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
              <button class="btn btn-primary w-100">Run Report</button>
            </div>
          </div>

        </form>
      </div>
    </div>

  </div>
</div>
@endsection
