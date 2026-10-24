@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-12">

                <div class="card">

                    <div class="card-body">

                        {{-- =========================================================
                             Batch Header
                        ========================================================== --}}

                        <div class="d-flex justify-content-between align-items-start mb-3">

                            <div>

                                <h4 class="card-title mb-2">
                                    Batch Claims
                                </h4>

                                <div class="text-muted">

                                    <div>
                                        <strong>Batch ID:</strong>
                                        {{ $batch->id }}
                                    </div>

                                    <div>
                                        <strong>837P File:</strong>
                                        {{ $batch->edi_filename }}
                                    </div>

                                    <div>
                                        <strong>Batch Date:</strong>

                                        @if ($batch->batch_timestamp)

                                            {{ \Carbon\Carbon::parse(
                                                $batch->batch_timestamp
                                            )->format('m/d/Y h:i A') }}

                                        @endif

                                    </div>

                                    <div>
                                        <strong>Total Claims:</strong>

                                        {{ number_format(
                                            $batch->total_claims ?? 0
                                        ) }}

                                    </div>

                                    <div>
                                        <strong>Total Amount:</strong>

                                        ${{ number_format(
                                            $batch->total_amount ?? 0,
                                            2
                                        ) }}

                                    </div>

                                </div>

                            </div>


                            <div>

                                <a
                                    href="{{ route(
                                        'claims.resubmit.batches.index'
                                    ) }}"
                                    class="btn btn-secondary"
                                >
                                    Back to Batches
                                </a>

                            </div>

                        </div>


                        <hr>


                        {{-- =========================================================
                             Selection Buttons
                        ========================================================== --}}

                        @if ($claims->isNotEmpty())

                            <div class="d-flex flex-wrap gap-2 mb-3">

                                <button
                                    type="button"
                                    id="selectAllClaims"
                                    class="btn btn-outline-primary"
                                >
                                    Select All
                                </button>


                                <button
                                    type="button"
                                    id="clearSelection"
                                    class="btn btn-outline-secondary"
                                >
                                    Clear Selection
                                </button>


                                <span
                                    class="align-self-center ms-2"
                                    id="selectedCount"
                                >
                                    0 claim(s) selected
                                </span>

                            </div>

                        @endif


                        {{-- =========================================================
                             Selected Claims Form
                        ========================================================== --}}

                        <form
                            id="selectedClaimsForm"
                            method="POST"
                            action="{{ route(
                                'claims.resubmit.batches.selected',
                                $batch->id
                            ) }}"
                        >

                            @csrf


                            <div class="table-responsive">

                                <table
                                    id="claimsTable"
                                    class="table table-bordered table-striped align-middle"
                                    style="width:100%;"
                                >

                                    <thead>

                                        <tr>

                                            <th
                                                class="text-center"
                                                style="width:45px;"
                                            >
                                                <input
                                                    type="checkbox"
                                                    id="headerSelectAll"
                                                    class="form-check-input"
                                                    title="Select All"
                                                >
                                            </th>


                                            <th style="width:45px;">
                                                #
                                            </th>


                                            <th>
                                                Claim #
                                            </th>


                                            <th>
                                                Member ID
                                            </th>


                                            <th>
                                                Member Name
                                            </th>


                                            <th>
                                                Service From
                                            </th>


                                            <th>
                                                Service To
                                            </th>


                                            <th class="text-end">
                                                Units
                                            </th>


                                            <th class="text-end">
                                                Amount
                                            </th>


                                            <th>
                                                Status
                                            </th>


                                            <th>
                                                Precheck
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        @forelse ($claims as $claim)

                                            <tr>

                                                {{-- Checkbox --}}

                                                <td class="text-center">

                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input claim-select"
                                                        value="{{ $claim->id }}"
                                                    >

                                                </td>


                                                {{-- Row Number --}}

                                                <td>
                                                    {{ $loop->iteration }}
                                                </td>


                                                {{-- Claim Number --}}

                                                <td>

                                                    <strong>
                                                        {{ $claim->claim_no }}
                                                    </strong>

                                                </td>


                                                {{-- Member ID --}}

                                                <td>
                                                    {{ $claim->ParticipantId }}
                                                </td>


                                                {{-- Member Name --}}

                                                <td>

                                                    @php

                                                        $memberName = trim(
                                                            ($claim->member_lastname ?? '') .
                                                            ', ' .
                                                            ($claim->member_firstname ?? '')
                                                        );

                                                        $memberName = trim(
                                                            $memberName,
                                                            ', '
                                                        );

                                                    @endphp


                                                    @if ($memberName !== '')

                                                        {{ $memberName }}

                                                    @else

                                                        <span class="text-muted">
                                                            Member not found
                                                        </span>

                                                    @endif

                                                </td>


                                                {{-- Service From --}}

                                                <td
                                                    data-order="{{ $claim->service_from }}"
                                                >

                                                    @if ($claim->service_from)

                                                        {{ \Carbon\Carbon::parse(
                                                            $claim->service_from
                                                        )->format('m/d/Y') }}

                                                    @endif

                                                </td>


                                                {{-- Service To --}}

                                                <td
                                                    data-order="{{ $claim->service_to }}"
                                                >

                                                    @if ($claim->service_to)

                                                        {{ \Carbon\Carbon::parse(
                                                            $claim->service_to
                                                        )->format('m/d/Y') }}

                                                    @endif

                                                </td>


                                                {{-- Units --}}

                                                <td class="text-end">

                                                    {{ $claim->units ?? '' }}

                                                </td>


                                                {{-- Amount --}}

                                                <td class="text-end">

                                                    ${{ number_format(
                                                        $claim->total_amount ?? 0,
                                                        2
                                                    ) }}

                                                </td>


                                                {{-- Status --}}

                                                <td>

                                                    @php

                                                        $status = strtolower(
                                                            $claim->status ?? ''
                                                        );

                                                    @endphp


                                                    @if ($status === 'sent')

                                                        <span class="badge bg-success">
                                                            Sent
                                                        </span>

                                                    @elseif ($status === 'rejected')

                                                        <span class="badge bg-danger">
                                                            Rejected
                                                        </span>

                                                    @elseif ($status === 'ready')

                                                        <span class="badge bg-primary">
                                                            Ready
                                                        </span>

                                                    @elseif ($status === 'draft')

                                                        <span class="badge bg-warning text-dark">
                                                            Draft
                                                        </span>

                                                    @else

                                                        <span class="badge bg-secondary">

                                                            {{ $claim->status ?? 'N/A' }}

                                                        </span>

                                                    @endif

                                                </td>


                                                {{-- Precheck --}}

                                                <td>

                                                    @php

                                                        $precheck = strtolower(
                                                            $claim->precheck_result ?? ''
                                                        );

                                                    @endphp


                                                    @if ($precheck === 'valid')

                                                        <span class="badge bg-success">
                                                            Valid
                                                        </span>

                                                    @elseif ($precheck === 'invalid')

                                                        <span class="badge bg-danger">
                                                            Invalid
                                                        </span>

                                                    @else

                                                        <span class="badge bg-secondary">

                                                            {{ $claim->precheck_result ?? 'N/A' }}

                                                        </span>

                                                    @endif

                                                </td>

                                            </tr>

                                        @empty

                                            <tr>

                                                <td
                                                    colspan="11"
                                                    class="text-center text-muted"
                                                >
                                                    No claims were found for this batch.
                                                </td>

                                            </tr>

                                        @endforelse

                                    </tbody>

                                </table>

                            </div>


                            @if ($claims->isNotEmpty())

                                <div class="d-flex align-items-center gap-2 mt-3">

                                    {{-- Selected Claims Button --}}

                                    <button
                                        type="submit"
                                        id="resubmitSelectedButton"
                                        class="btn btn-primary"
                                    >
                                        Resubmit Selected Claims
                                    </button>

                            </form>


                                    {{-- Entire Batch Form --}}

                                    <form
                                        id="entireBatchForm"
                                        method="POST"
                                        action="{{ route(
                                            'claims.resubmit.batches.all',
                                            $batch->id
                                        ) }}"
                                        class="m-0"
                                    >

                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-danger"
                                        >
                                            Resubmit Entire Batch
                                        </button>

                                    </form>

                                </div>

                            @else

                            </form>

                            @endif
                    </div>

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
    | Initialize DataTable
    |--------------------------------------------------------------------------
    */

    const table = $('#claimsTable').DataTable({

        pageLength: 50,

        lengthMenu: [
            [50, 100, 150, 200, 250, -1],
            [50, 100, 150, 200, 250, 'All']
        ],

        /*
         * Service From is now column 5 because we added
         * the checkbox column before the existing columns.
         */

        order: [
            [5, 'asc']
        ],

        columnDefs: [

            {
                targets: 0,
                orderable: false,
                searchable: false
            },

            {
                targets: 1,
                orderable: false,
                searchable: false
            }

        ],

        autoWidth: false,

        responsive: false

    });


    /*
    |--------------------------------------------------------------------------
    | Update Selected Count
    |--------------------------------------------------------------------------
    */

    function updateSelectedCount() {

        const checked =
            table.$('.claim-select:checked').length;

        $('#selectedCount').text(
            checked + ' claim(s) selected'
        );


        /*
         * Determine whether every claim is selected.
         */

        const total =
            table.$('.claim-select').length;


        $('#headerSelectAll').prop(
            'checked',
            total > 0 && checked === total
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Select All Button
    |--------------------------------------------------------------------------
    |
    | DataTables may have rows on multiple pages.
    |
    | table.$() allows us to select checkboxes across ALL DataTable pages.
    |
    */

    $('#selectAllClaims').on('click', function () {

        table
            .$('.claim-select')
            .prop('checked', true);

        $('#headerSelectAll').prop(
            'checked',
            true
        );

        updateSelectedCount();

    });


    /*
    |--------------------------------------------------------------------------
    | Header Select All Checkbox
    |--------------------------------------------------------------------------
    */

    $('#headerSelectAll').on('change', function () {

        const checked =
            $(this).is(':checked');

        table
            .$('.claim-select')
            .prop('checked', checked);

        updateSelectedCount();

    });


    /*
    |--------------------------------------------------------------------------
    | Clear Selection
    |--------------------------------------------------------------------------
    */

    $('#clearSelection').on('click', function () {

        table
            .$('.claim-select')
            .prop('checked', false);

        $('#headerSelectAll').prop(
            'checked',
            false
        );

        updateSelectedCount();

    });


    /*
    |--------------------------------------------------------------------------
    | Individual Checkbox
    |--------------------------------------------------------------------------
    */

    $('#claimsTable').on(
        'change',
        '.claim-select',
        function () {

            updateSelectedCount();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Submit Selected Claims
    |--------------------------------------------------------------------------
    |
    | Important:
    |
    | DataTables can move rows outside the normal DOM while paging.
    |
    | Therefore, before POSTing, we manually create hidden
    | selected_claims[] fields for every checked claim.
    |
    */

    $('#selectedClaimsForm').on(
        'submit',
        function (e) {

            const form = $(this);

            /*
             * Remove previously generated hidden inputs.
             */

            form
                .find('.generated-selected-claim')
                .remove();


            const checkedClaims =
                table.$('.claim-select:checked');


            if (checkedClaims.length === 0) {

                e.preventDefault();

                alert(
                    'Please select at least one claim.'
                );

                return false;
            }


            /*
             * Add selected claim IDs as hidden inputs.
             */

            checkedClaims.each(function () {

                $('<input>')
                    .attr({
                        type: 'hidden',
                        name: 'selected_claims[]',
                        value: $(this).val()
                    })
                    .addClass(
                        'generated-selected-claim'
                    )
                    .appendTo(form);

            });


            /*
             * Confirmation only.
             *
             * At this stage the controller does NOT assign
             * new claim numbers yet.
             */

            const confirmed = confirm(
                'You selected ' +
                checkedClaims.length +
                ' claim(s) from Batch {{ $batch->id }}.' +
                '\n\nContinue?'
            );


            if (!confirmed) {

                e.preventDefault();

                return false;
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Entire Batch Confirmation
    |--------------------------------------------------------------------------
    */

    $('#entireBatchForm').on(
        'submit',
        function (e) {

            const confirmed = confirm(
                'You are selecting the ENTIRE Batch {{ $batch->id }} for resubmission.' +
                '\n\nTotal Claims: {{ $claims->count() }}' +
                '\n\nContinue?'
            );


            if (!confirmed) {

                e.preventDefault();

                return false;
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Initial Count
    |--------------------------------------------------------------------------
    */

    updateSelectedCount();

});

</script>

@endsection