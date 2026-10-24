@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">A/R Aging Summary</h4>

                    <div class="page-title-right">
                        <a href="{{ route('claims.dashboard') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ t('common.back', 'Back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Card --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('ar.aging.summary') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">As of Date</label>
                            <input type="text"
                                   name="as_of_date"
                                   id="as_of_date"
                                   class="form-control"
                                   value="{{ $asOfDisplay }}"
                                   placeholder="mm/dd/yyyy">
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Load
                            </button>

                            <a href="{{ route('ar.aging.summary') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total A/R Balance</h6>
                        <h3 class="text-white mb-0">${{ number_format($totals['total_balance'], 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Open Claims</h6>
                        <h3 class="text-white mb-0">{{ number_format($totals['claim_count']) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aging Table --}}
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    Aging as of {{ $asOfDisplay }}
                </h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Insurance</th>
                                <th class="text-end">0-30</th>
                                <th class="text-end">31-60</th>
                                <th class="text-end">61-90</th>
                                <th class="text-end">91-120</th>
                                <th class="text-end">120+</th>
                                <th class="text-end">Total Balance</th>
                                <th class="text-end">Claims</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td>
                                        <a href="{{ route('ar.aging.detail', [
                                            'insurance' => $row->insurance_name,
                                            'as_of_date' => $asOfDisplay
                                        ]) }}">
                                            {{ $row->insurance_name }}
                                        </a>
                                    </td>
                                    <td class="text-end">${{ number_format($row->bucket_0_30, 2) }}</td>
                                    <td class="text-end">${{ number_format($row->bucket_31_60, 2) }}</td>
                                    <td class="text-end">${{ number_format($row->bucket_61_90, 2) }}</td>
                                    <td class="text-end">${{ number_format($row->bucket_91_120, 2) }}</td>
                                    <td class="text-end">${{ number_format($row->bucket_120_plus, 2) }}</td>
                                    <td class="text-end fw-bold">${{ number_format($row->total_balance, 2) }}</td>
                                    <td class="text-end">{{ number_format($row->claim_count) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        No open A/R balance found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        <tfoot class="table-light">
                            <tr>
                                <th>Total</th>
                                <th class="text-end">${{ number_format($totals['bucket_0_30'], 2) }}</th>
                                <th class="text-end">${{ number_format($totals['bucket_31_60'], 2) }}</th>
                                <th class="text-end">${{ number_format($totals['bucket_61_90'], 2) }}</th>
                                <th class="text-end">${{ number_format($totals['bucket_91_120'], 2) }}</th>
                                <th class="text-end">${{ number_format($totals['bucket_120_plus'], 2) }}</th>
                                <th class="text-end">${{ number_format($totals['total_balance'], 2) }}</th>
                                <th class="text-end">{{ number_format($totals['claim_count']) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
