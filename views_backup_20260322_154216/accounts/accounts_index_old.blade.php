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
                <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#home1" role="tab">
                            <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                            <span class="d-none d-sm-block">Data Entry</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#profile1" role="tab">
                            <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                            <span class="d-none d-sm-block">Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#messages1" role="tab">
                            <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                            <span class="d-none d-sm-block">Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#settings1" role="tab">
                            <span class="d-block d-sm-none"><i class="fas fa-cog"></i></span>
                            <span class="d-none d-sm-block">Help</span>
                        </a>
                    </li>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content p-3 text-muted">
                    <div class="tab-pane active" id="home1" role="tabpanel">
                        <div>
                            <a href="{{ route('enter.payment') }}"><button type="button" class="btn btn-soft-light waves-effect">Enter Payment</button></a>
                            <a href="{{ route('enter.deposit') }}"><button type="button" class="btn btn-soft-light waves-effect">Enter Deposit</button></a>
                            <a href="{{ route('journal.entry') }}"><button type="button" class="btn btn-soft-light waves-effect">Journal Entry</button></a>
                            <a href="{{ route('list.payment') }}"><button type="button" class="btn btn-soft-light waves-effect">List/Edit Payment</button></a>
                            <a href="{{ route('list.deposit') }}"><button type="button" class="btn btn-soft-light waves-effect">List/Edit Deposit</button></a>
                            <a href="{{ route('journal.list') }}"><button type="button" class="btn btn-soft-light waves-effect">List/Edit Journal Entry</button></a>
                            <a href="{{ route('bankfeed.import.form') }}"><button type="button" class="btn btn-soft-light waves-effect">Bank Feed Upload</button></a>

                        </div>
                    </div>
                    <div class="tab-pane" id="profile1" role="tabpanel">
                        <div>
                            <a href="{{ route('print.checklist') }}"><button type="button" class="btn btn-soft-light waves-effect">Print Check</button></a>
                            <a href="{{ route('bank.report') }}"><button type="button" class="btn btn-soft-light waves-effect">Bank Report</button></a>
                            <a href="{{ route('genledger.report') }}"><button type="button" class="btn btn-soft-light waves-effect">General Ledger</button></a>
                            <a href="{{ route('trialbalance.report') }}"><button type="button" class="btn btn-soft-light waves-effect">Trial Balance</button></a>
                            <a href="{{ route('trialbalance.monthwise') }}"><button type="button" class="btn btn-soft-light waves-effect">Trial Balance Monthwise</button></a>
                            <a href="{{ route('trialbalance.balance.monthwise') }}"><button type="button" class="btn btn-soft-light waves-effect">Trial Balance Monthwise With Balances</button></a>
                            <a href="{{ route('profitloss') }}"><button type="button" class="btn btn-soft-light waves-effect">Profit & Loss</button></a>
                            <a href="{{ route('profitloss.new') }}"><button type="button" class="btn btn-soft-light waves-effect">Profit & Loss New</button></a>
                            <a href="{{ route('profitloss.monthwise') }}"><button type="button" class="btn btn-soft-light waves-effect">Profit & Loss Comparison</button></a>
                            <a href="{{ route('balancesheet') }}"><button type="button" class="btn btn-soft-light waves-effect">Balance Sheet</button></a>
                        </div>
                    </div>
                    <div class="tab-pane" id="messages1" role="tabpanel">
                        <div>
                            <a href="{{ route('bank-rules.index') }}"><button type="button" class="btn btn-soft-light waves-effect">Bank Feed Rules</button></a>
                        </div>
                    </div>
                    <div class="tab-pane" id="settings1" role="tabpanel">

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
