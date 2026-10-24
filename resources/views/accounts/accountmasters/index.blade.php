@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page title --}}
        <div class="row">
            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0">Account Master</h4>

                    <div class="page-title-right">
                        <a href="{{ route('accountmasters.create') }}"
                           class="btn btn-primary">

                            <i class="fas fa-plus me-1"></i>
                            Add Account
                        </a>
                    </div>

                </div>

            </div>
        </div>

        {{-- Success message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show"
                 role="alert">

                {{ session('success') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>
            </div>
        @endif

        <div class="card">

            <div class="card-body">

                {{-- Status filter --}}
                <form method="GET"
                    action="{{ route('accountmasters.index') }}"
                    class="row g-2 mb-3 align-items-end">

                    <div class="col-md-3">

                        <label for="account_master_status" class="form-label">
                            Status
                        </label>

                        <select name="status"
                                id="account_master_status"
                                class="form-select">

                            <option value="A"
                                {{ $status === 'A' ? 'selected' : '' }}>
                                A - Active
                            </option>

                            <option value="H"
                                {{ $status === 'H' ? 'selected' : '' }}>
                                H - Hold
                            </option>

                            <option value="D"
                                {{ $status === 'D' ? 'selected' : '' }}>
                                D - Deleted
                            </option>

                            <option value="ALL"
                                {{ $status === 'ALL' ? 'selected' : '' }}>
                                All
                            </option>

                        </select>

                    </div>

                    <div class="col-md-4">

                        <div class="d-flex gap-2">

                            <button type="submit"
                                    class="btn btn-primary">

                                <i class="fas fa-filter me-1"></i>
                                Apply
                            </button>

                            <a href="{{ route('accountmasters.index') }}"
                            class="btn btn-light">

                                Reset
                            </a>

                        </div>

                    </div>

                </form>
                <div class="table-responsive">

                    <table id="accountMasterTable"
                           class="table table-bordered table-striped table-hover align-middle nowrap"
                           style="width:100%;">

                        <thead class="table-light">

                            <tr>
                                <th>Party ID</th>
                                <th>Party Name</th>
                                <th>Party Type</th>
                                <th>Contact</th>
                                <th>Phone</th>
                                <th>G/L Account</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach($accounts as $account)

                                <tr>

                                    {{-- Numeric sorting for varchar party_cd --}}
                                    <td data-order="{{ (int) $account->party_cd }}">
                                        {{ $account->party_cd }}
                                    </td>

                                    <td>
                                        {{ $account->party_nm }}
                                    </td>

                                    <td data-order="{{ $account->party_type }}">

                                        @switch($account->party_type)

                                            @case('E')
                                                E - Expenses
                                                @break

                                            @case('I')
                                                I - Income
                                                @break

                                            @case('A')
                                                A - Asset
                                                @break

                                            @case('L')
                                                L - Liability
                                                @break

                                            @default
                                                {{ $account->party_type }}

                                        @endswitch

                                    </td>

                                    <td>

                                        {{ $account->party_contact }}

                                        @if($account->party_email)
                                            <div class="small text-muted">
                                                {{ $account->party_email }}
                                            </div>
                                        @endif

                                    </td>

                                    <td>
                                        {{ $account->party_phone1 }}
                                    </td>

                                    <td data-order="{{ $account->gl_id }}">

                                        {{ $account->gl_id }}

                                        @if($account->gl_name)
                                            <div class="small text-muted">
                                                {{ $account->gl_name }}
                                            </div>
                                        @endif

                                    </td>

                                    <td class="text-center">

                                        @if($account->trans_flag === 'A')

                                            <span class="badge bg-success">
                                                Active
                                            </span>

                                        @elseif($account->trans_flag === 'H')

                                            <span class="badge bg-warning text-dark">
                                                Hold
                                            </span>

                                        @elseif($account->trans_flag === 'D')

                                            <span class="badge bg-danger">
                                                Deleted
                                            </span>

                                        @else

                                            <span class="badge bg-secondary">
                                                {{ $account->trans_flag }}
                                            </span>

                                        @endif

                                    </td>

                                    <td class="text-center text-nowrap">

                                        <a href="{{ route('accountmasters.edit', $account->party_cd) }}"
                                           class="btn btn-sm btn-soft-primary"
                                           title="Edit">

                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @if($account->trans_flag !== 'D')

                                            <form method="POST"
                                                  action="{{ route('accountmasters.destroy', $account->party_cd) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Mark this Account Master record as deleted?');">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="btn btn-sm btn-soft-danger"
                                                        title="Delete">

                                                    <i class="fas fa-trash"></i>
                                                </button>

                                            </form>

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
    #account_master_status {
        display: block;
        width: 100%;
        min-height: 38px;
        visibility: visible;
        opacity: 1;
    }
</style>
@endpush

@push('scripts')

<script>
$(document).ready(function () {

    if ($.fn.DataTable.isDataTable('#accountMasterTable')) {
        $('#accountMasterTable').DataTable().destroy();
    }

    $('#accountMasterTable').DataTable({

        pageLength: 100,

        lengthMenu: [
            [100, 200, 300, 400, -1],
            [100, 200, 300, 400, 'All']
        ],

        order: [
            [0, 'asc']
        ],

        columnDefs: [
            {
                // Party ID
                targets: 0,
                orderable: true,
                type: 'num'
            },
            {
                // Party Name
                targets: 1,
                orderable: true
            },
            {
                // Party Type
                targets: 2,
                orderable: true
            },
            {
                // Contact
                targets: 3,
                orderable: false
            },
            {
                // Phone
                targets: 4,
                orderable: false
            },
            {
                // G/L Account
                targets: 5,
                orderable: true
            },
            {
                // Status
                targets: 6,
                orderable: false
            },
            {
                // Action
                targets: 7,
                orderable: false,
                searchable: false
            }
        ],

        autoWidth: false,

        language: {
            lengthMenu: 'Show _MENU_ records',
            search: 'Search:',
            searchPlaceholder: 'Search account master...',
            emptyTable: 'No Account Master records found.',
            zeroRecords: 'No matching Account Master records found.',
            info: 'Showing _START_ to _END_ of _TOTAL_ records',
            infoEmpty: 'Showing 0 to 0 of 0 records'
        }

    });

});
</script>

@endpush
