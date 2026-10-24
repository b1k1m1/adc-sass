@extends('admin.admin_dashboard')
@section('title', 'Accounts')
@section('admin')

<div class="page-content">
    <div class="container-fluid">


<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <!-- Nav tabs -->
                <!-- Nav tabs -->
                 @include('admin.partials.tabs_3', ['active' => 'data'])
                <!-- Tab panes -->
                <div class="tab-content p-3 text-muted">
                    <div class="tab-pane fade show active" id="tab-data" role="tabpanel">
                        <div>
                            <a href="{{ route('enter.payment') }}"><button type="button" class="btn btn-soft-light waves-effect">Enter Payment</button></a>
                            <a href="{{ route('enter.deposit') }}"><button type="button" class="btn btn-soft-light waves-effect">Enter Deposit</button></a>
                            <a href="{{ route('journal.entry') }}"><button type="button" class="btn btn-soft-light waves-effect">Journal Entry</button></a>
                            <a href="{{ route('list.payment') }}"><button type="button" class="btn btn-soft-light waves-effect">List/Edit Payment</button></a>
                            <a href="{{ route('list.deposit') }}"><button type="button" class="btn btn-soft-light waves-effect">List/Edit Deposit</button></a>
                            <a href="{{ route('journal.list') }}"><button type="button" class="btn btn-soft-light waves-effect">List/Edit Journal Entry</button></a>
                            <a href="{{ route('bankrecon') }}"><button type="button" class="btn btn-soft-light waves-effect">Bank Reconciliation</button></a>
                            <a href="{{ route('bankfeed.import.form') }}"><button type="button" class="btn btn-soft-light waves-effect">Bank Feed Upload</button></a>
                            <a href="{{ route('mpp.index') }}"><button type="button" class="btn btn-soft-light waves-effect">Payment Plan</button></a>

                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-reports" role="tabpanel">
                        {{-- QuickBooks-like simple report menu (no DB) --}}
                        @php
                            $reports = [
                                ['Print Check', route('print.checklist')],
                                ['Bank Report', route('bank.report')],
                                ['General Ledger', route('genledger.report')],
                                ['Trial Balance', route('trialbalance.report')],
                                ['Trial Balance Monthwise', route('trialbalance.monthwise')],
                                ['Trial Balance Monthwise With Balances', route('trialbalance.balance.monthwise')],
                                ['Profit & Loss', route('profitloss')],
                                ['Profit & Loss New', route('profitloss.new')],
                                ['Profit & Loss Comparison', route('profitloss.monthwise')],
                                ['Balance Sheet', route('balancesheet')],
                                ['Payment Plan', route('reports.payment_plan')],

                            ];

                            $half = (int) ceil(count($reports) / 2);
                            $left = array_slice($reports, 0, $half);
                            $right = array_slice($reports, $half);
                        @endphp

                        <div class="row g-0 border rounded">
                            <div class="col-md-6">
                                @foreach($left as [$name, $url])
                                    <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
                                        <i data-feather="file-text"></i>
                                        <a href="{{ $url }}" class="text-decoration-none">{{ $name }}</a>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-6 border-start">
                                @foreach($right as [$name, $url])
                                    <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
                                        <i data-feather="file-text"></i>
                                        <a href="{{ $url }}" class="text-decoration-none">{{ $name }}</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                    </div>
                    <div class="tab-pane fade" id="tab-settings" role="tabpanel">
                        <div>
                            <a href="{{ route('bank-rules.index') }}" class="btn btn-soft-light waves-effect">Bank Feed Rules</a>
                        </div>
                    </div>

                </div>
            </div><!-- end card-body -->
        </div><!-- end card -->
    </div><!-- end col -->
</div><!-- end row -->


</div>
<!-- container-fluid -->
</div>
<!-- End Page-content -->
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.feather) feather.replace();
  });
</script>
@endsection
