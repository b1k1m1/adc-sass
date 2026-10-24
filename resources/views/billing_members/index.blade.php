@extends('admin.admin_dashboard')

@section('title', 'Billing Members')

@section('admin')

<style>
    /*
    |--------------------------------------------------------------------------
    | Billing Members DataTable
    |--------------------------------------------------------------------------
    */

    #billingMembersTable {
        width: 100% !important;
    }

    #billingMembersTable th,
    #billingMembersTable td {
        vertical-align: middle !important;
        white-space: nowrap;
    }

    #billingMembersTable thead th {
        background-color: #f3f4f7 !important;
        color: #000;
        font-weight: 600;
        border-bottom: 1px solid #dee2e6 !important;
    }

    #billingMembersTable tbody tr:hover {
        background-color: #f8f9fa;
    }

    #billingMembersTable td {
        padding: 7px 8px !important;
    }

    /*
    |--------------------------------------------------------------------------
    | DataTables Header Controls
    |--------------------------------------------------------------------------
    */

    div.dataTables_wrapper div.dataTables_length {
        text-align: left;
        margin-bottom: 10px;
    }

    div.dataTables_wrapper div.dataTables_filter {
        text-align: right;
        margin-bottom: 10px;
    }

    div.dataTables_wrapper div.dataTables_filter input {
        margin-left: 6px;
        min-width: 220px;
    }

    div.dataTables_wrapper div.dataTables_info {
        padding-top: 12px;
    }

    div.dataTables_wrapper div.dataTables_paginate {
        padding-top: 8px;
    }

    /*
    |--------------------------------------------------------------------------
    | Action Buttons
    |--------------------------------------------------------------------------
    */

    .billing-action-cell {
        white-space: nowrap;
        min-width: 145px;
    }

    .billing-action-cell .btn {
        margin-right: 3px;
    }

    /*
    |--------------------------------------------------------------------------
    | Filter Card
    |--------------------------------------------------------------------------
    */

    .billing-filter-card .card-body {
        padding-bottom: 14px;
    }

    /*
    |--------------------------------------------------------------------------
    | Prevent Oversized / Broken Table Area
    |--------------------------------------------------------------------------
    */

    .billing-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .dataTables_wrapper {
        width: 100%;
    }
</style>


<div class="page-content">

    <div class="container-fluid">

        {{-- ================================================================
             PAGE TITLE
        ================================================================= --}}

        <div class="row">
            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Billing Members
                    </h4>

                    <a href="{{ route('billing.members.create') }}"
                       class="btn btn-primary">

                        <i class="fas fa-plus me-1"></i>
                        Add Billing Member

                    </a>

                </div>

            </div>
        </div>


        {{-- ================================================================
             SUCCESS MESSAGE
        ================================================================= --}}

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


        {{-- ================================================================
             FILTERS
        ================================================================= --}}

        <div class="card mb-3 billing-filter-card">

            <div class="card-body">

                <form method="GET"
                      action="{{ route('billing.members.index') }}">

                    <div class="row align-items-end">

                        <div class="col-md-3">

                            <label class="form-label">
                                Shift
                            </label>

                            <select name="source_shift"
                                    class="form-select">

                                <option value="">
                                    All Shifts
                                </option>

                                <option value="A"
                                    {{ $sourceShift == 'A' ? 'selected' : '' }}>

                                    Morning

                                </option>

                                <option value="N"
                                    {{ $sourceShift == 'N' ? 'selected' : '' }}>

                                    Noon

                                </option>

                                <option value="P"
                                    {{ $sourceShift == 'P' ? 'selected' : '' }}>

                                    Afternoon

                                </option>

                            </select>

                        </div>


                        <div class="col-md-3">

                            <label class="form-label">
                                Status
                            </label>

                            <select name="status"
                                    class="form-select">

                                <option value="all"
                                    {{ $status == 'all' ? 'selected' : '' }}>

                                    All

                                </option>

                                <option value="active"
                                    {{ $status == 'active' ? 'selected' : '' }}>

                                    Active

                                </option>

                                <option value="inactive"
                                    {{ $status == 'inactive' ? 'selected' : '' }}>

                                    Inactive

                                </option>

                            </select>

                        </div>


                        <div class="col-md-6">

                            <button type="submit"
                                    class="btn btn-primary">

                                <i class="fas fa-filter me-1"></i>
                                Apply Filter

                            </button>

                            <a href="{{ route('billing.members.index') }}"
                               class="btn btn-secondary">

                                Reset

                            </a>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        {{-- ================================================================
             BILLING MEMBERS DATATABLE
        ================================================================= --}}

        <div class="card">

            <div class="card-body">

                <div class="billing-table-wrapper">

                    <table id="billingMembersTable"
                           class="table table-bordered table-hover table-sm align-middle">

                        <thead>

                            <tr>

                                <th>Participant</th>

                                <th>Last Name</th>

                                <th>First Name</th>

                                <th>DOB</th>

                                <th>Shift</th>

                                <th>Insurance</th>

                                <th>Insurance ID</th>

                                <th>PA #</th>

                                <th>PA End</th>

                                <th>Status</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($members as $member)

                                <tr>

                                    {{-- Participant --}}
                                    <td data-order="{{ (int) $member->ParticipantId }}">

                                        <strong>
                                            {{ $member->ParticipantId }}
                                        </strong>

                                    </td>


                                    {{-- Last Name --}}
                                    <td>
                                        {{ $member->LastName }}
                                    </td>


                                    {{-- First Name --}}
                                    <td>
                                        {{ $member->FirstName }}
                                    </td>


                                    {{-- DOB --}}
                                    <td
                                        @if($member->DateOfBirth)
                                            data-order="{{ \Carbon\Carbon::parse($member->DateOfBirth)->format('Ymd') }}"
                                        @endif
                                    >

                                        @if($member->DateOfBirth)

                                            {{ \Carbon\Carbon::parse($member->DateOfBirth)->format('m/d/Y') }}

                                        @endif

                                    </td>


                                    {{-- Shift --}}
                                    <td>

                                        @if($member->source_shift == 'A')

                                            Morning

                                        @elseif($member->source_shift == 'N')

                                            Noon

                                        @elseif($member->source_shift == 'P')

                                            Afternoon

                                        @else

                                            {{ $member->source_shift }}

                                        @endif

                                    </td>


                                    {{-- Insurance --}}
                                    <td>
                                        {{ $member->HMO_Enrolled }}
                                    </td>


                                    {{-- Insurance ID --}}
                                    <td>
                                        {{ $member->medicaid_HMO_id }}
                                    </td>


                                    {{-- PA # --}}
                                    <td>

                                        {{
                                            $member->renew1_PA_no
                                            ?: $member->medicaid_PA_no
                                        }}

                                    </td>


                                    {{-- PA End --}}
                                    @php

                                        $paEnd =
                                            $member->renew1_PA_End
                                            ?: $member->medicaid_PA_End;

                                    @endphp

                                    <td
                                        @if($paEnd)
                                            data-order="{{ \Carbon\Carbon::parse($paEnd)->format('Ymd') }}"
                                        @endif
                                    >

                                        @if($paEnd)

                                            {{ \Carbon\Carbon::parse($paEnd)->format('m/d/Y') }}

                                        @endif

                                    </td>


                                    {{-- Status --}}
                                    <td data-order="{{ $member->active ? 1 : 0 }}">

                                        @if($member->active)

                                            <span class="badge bg-success">
                                                Active
                                            </span>

                                        @else

                                            <span class="badge bg-secondary">
                                                Inactive
                                            </span>

                                        @endif

                                    </td>


                                    {{-- Action --}}
                                    <td class="billing-action-cell">

                                        <a href="{{ route('billing.members.edit', $member->id) }}"
                                           class="btn btn-sm btn-primary">

                                            Edit

                                        </a>


                                        <form method="POST"
                                              action="{{ route('billing.members.toggle', $member->id) }}"
                                              class="d-inline">

                                            @csrf

                                            <button type="submit"
                                                    class="btn btn-sm {{ $member->active ? 'btn-warning' : 'btn-success' }}">

                                                {{
                                                    $member->active
                                                        ? 'Inactive'
                                                        : 'Activate'
                                                }}

                                            </button>

                                        </form>

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


@section('scripts')

<script>

$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | Protect Against Double Initialization
    |--------------------------------------------------------------------------
    */

    if ($.fn.DataTable.isDataTable('#billingMembersTable')) {

        $('#billingMembersTable')
            .DataTable()
            .destroy();

    }


    /*
    |--------------------------------------------------------------------------
    | Initialize Billing Members DataTable
    |--------------------------------------------------------------------------
    */

    $('#billingMembersTable').DataTable({

        processing: false,

        serverSide: false,

        responsive: false,

        autoWidth: false,

        scrollX: true,

        ordering: true,

        searching: true,

        pageLength: 100,

        lengthMenu: [

            [50, 100, 200, 300, 500, -1],

            [50, 100, 200, 300, 500, 'All']

        ],

        /*
        |--------------------------------------------------------------------------
        | Default Sort
        |--------------------------------------------------------------------------
        |
        | Last Name, then First Name
        |--------------------------------------------------------------------------
        */

        order: [

            [1, 'asc'],

            [2, 'asc']

        ],


        /*
        |--------------------------------------------------------------------------
        | Action Column
        |--------------------------------------------------------------------------
        |
        | Everything except Action remains sortable and searchable.
        |--------------------------------------------------------------------------
        */

        columnDefs: [

            {
                targets: 10,
                orderable: false,
                searchable: false
            }

        ],


        language: {

            search: 'Search All Fields:',

            searchPlaceholder:
                'Participant, name, insurance, PA, shift...',

            lengthMenu:
                'Show _MENU_ records',

            info:
                'Showing _START_ to _END_ of _TOTAL_ billing members',

            infoEmpty:
                'No billing members found',

            zeroRecords:
                'No matching billing members found'

        }

    });

});

</script>

@endsection