@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Journal Entry List</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('accounts.dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-body">

                        <table id="members-table" class="table table-bordered dt-responsive  nowrap w-100">
                            <thead>
                            <tr>
                                <th>Sr#</th>
                                <th>Transaction #</th>
                                <th>Transaction Date</th>
                                <th>Transaction Type</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($journalLists as $key=> $journalList)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td><a href="#">{{ $journalList->trans_no }}</a></td>
                                <td>{{ $journalList->trans_dt }}</td>
                                <td>{{ $journalList ->trans_type }}</td>
                                <td>
                                    <a href="{{ route('journal.edit', [$journalList->trans_no, $journalList->trans_dt]) }}" class="btn btn-primary waves-light waves-effect" aria-expanded="false">Edit
                                    <i class="mdi mdi-dots-vertical ms-2"></i></a>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div> <!-- end col -->
        </div> <!-- end row -->

    </div>
</div>
@endsection
