@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-12">

                <div class="card">

                    <div class="card-body">

                        {{-- =========================================================
                             Header
                        ========================================================== --}}

                        <div class="d-flex justify-content-between align-items-start mb-3">

                            <div>

                                <h4 class="card-title mb-1">
                                    Resubmission 837P Preview
                                </h4>

                                <p class="text-muted mb-0">
                                    Claims prepared and waiting for a new 837P resubmission batch.
                                </p>

                            </div>

                            <div>

                                <a
                                    href="{{ route('claims.resubmit.batches.index') }}"
                                    class="btn btn-secondary"
                                >
                                    Back to Batch Resubmission
                                </a>

                            </div>

                        </div>


                        <hr>


                        {{-- =========================================================
                             Summary
                        ========================================================== --}}

                        @php

                            $readyCount = $claims->count();

                            $readyAmount = $claims->sum(
                                function ($claim) {
                                    return (float) ($claim->total_amount ?? 0);
                                }
                            );

                        @endphp


                        <div class="row mb-3">

                            <div class="col-md-3">

                                <div class="border rounded p-3">

                                    <div class="text-muted">
                                        Ready Claims
                                    </div>

                                    <div class="fs-4 fw-bold">
                                        {{ number_format($readyCount) }}
                                    </div>

                                </div>

                            </div>


                            <div class="col-md-3">

                                <div class="border rounded p-3">

                                    <div class="text-muted">
                                        Total Amount
                                    </div>

                                    <div class="fs-4 fw-bold">
                                        ${{ number_format($readyAmount, 2) }}
                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- =========================================================
                             No Claims
                        ========================================================== --}}

                        @if ($claims->isEmpty())

                            <div class="alert alert-info mb-0">

                                There are currently no claims waiting for
                                resubmission 837P generation.

                            </div>

                        @else


                            {{-- =====================================================
                                 Generate Form
                            ====================================================== --}}

                            <form
                                id="resubmissionGenerateForm"
                                method="POST"
                                action="{{ route('claims.resubmission.generate') }}"
                            >

                                @csrf


                                {{-- =================================================
                                     File Name
                                ================================================== --}}

                                <div class="row mb-3">

                                    <div class="col-md-6">

                                        <label
                                            for="edi_filename"
                                            class="form-label"
                                        >
                                            837P File Name
                                        </label>


                                        <input
                                            type="text"
                                            name="edi_filename"
                                            id="edi_filename"
                                            class="form-control"
                                            value="{{ old('edi_filename') }}"
                                            placeholder="Example: Resubmission_09.01.26_to_09.04.26"
                                            required
                                        >


                                        <small class="text-muted">

                                            You may enter the name with or without
                                            <strong>.txt</strong>.

                                        </small>

                                    </div>

                                </div>


                                {{-- =================================================
                                     Selection Controls
                                ================================================== --}}

                                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">

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
                                        id="selectedCount"
                                        class="ms-2 fw-semibold"
                                    >
                                        0 claim(s) selected
                                    </span>

                                </div>


                                {{-- =================================================
                                     Claims Table
                                ================================================== --}}

                                <div class="table-responsive">

                                    <table
                                        id="resubmissionTable"
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


                                                <th>
                                                    Old Claim #
                                                </th>


                                                <th>
                                                    New Claim #
                                                </th>


                                                <th>
                                                    Old Batch
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
                                                    Type
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

                                            @foreach ($claims as $claim)

                                                <tr>

                                                    {{-- Checkbox --}}

                                                    <td class="text-center">

                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input claim-select"
                                                            value="{{ $claim->id }}"
                                                        >

                                                    </td>


                                                    {{-- Old Claim Number --}}

                                                    <td>

                                                        <strong>
                                                            {{ $claim->old_claim_no }}
                                                        </strong>

                                                    </td>


                                                    {{-- New Claim Number --}}

                                                    <td>

                                                        <strong class="text-primary">
                                                            {{ $claim->new_claim_no }}
                                                        </strong>

                                                    </td>


                                                    {{-- Old Batch --}}

                                                    <td>

                                                        {{ $claim->old_batch_id }}

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


                                                    {{-- Resubmission Type --}}

                                                    <td>

                                                        @if ($claim->resubmit_type === 'full_batch')

                                                            <span class="badge bg-danger">
                                                                Full Batch
                                                            </span>

                                                        @elseif ($claim->resubmit_type === 'selected')

                                                            <span class="badge bg-primary">
                                                                Selected
                                                            </span>

                                                        @else

                                                            <span class="badge bg-secondary">

                                                                {{ $claim->resubmit_type ?? 'N/A' }}

                                                            </span>

                                                        @endif

                                                    </td>


                                                    {{-- Status --}}

                                                    <td>

                                                        @php

                                                            $status = strtolower(
                                                                $claim->status ?? ''
                                                            );

                                                        @endphp


                                                        @if ($status === 'ready')

                                                            <span class="badge bg-primary">
                                                                Ready
                                                            </span>

                                                        @elseif ($status === 'sent')

                                                            <span class="badge bg-success">
                                                                Sent
                                                            </span>

                                                        @elseif ($status === 'rejected')

                                                            <span class="badge bg-danger">
                                                                Rejected
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

                                            @endforeach

                                        </tbody>

                                    </table>

                                </div>


                                {{-- =================================================
                                     Generate Button
                                ================================================== --}}

                                <div class="mt-3">

                                    <button
                                        type="submit"
                                        id="generateResubmissionButton"
                                        class="btn btn-success"
                                    >
                                        Generate Resubmission 837P
                                    </button>

                                </div>

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
    | DataTable
    |--------------------------------------------------------------------------
    */

    const table = $('#resubmissionTable').DataTable({

        pageLength: 50,

        lengthMenu: [
            [50, 100, 150, 200, 250, -1],
            [50, 100, 150, 200, 250, 'All']
        ],

        /*
         * Service From = column 6
         *
         * 0 checkbox
         * 1 old claim
         * 2 new claim
         * 3 old batch
         * 4 member ID
         * 5 member name
         * 6 service from
         */

        order: [
            [6, 'asc']
        ],

        columnDefs: [

            {
                targets: 0,
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

        const selected =
            table.$('.claim-select:checked').length;

        const total =
            table.$('.claim-select').length;


        $('#selectedCount').text(
            selected + ' claim(s) selected'
        );


        $('#headerSelectAll').prop(
            'checked',
            total > 0 && selected === total
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Select All Button
    |--------------------------------------------------------------------------
    */

    $('#selectAllClaims').on(
        'click',
        function () {

            table
                .$('.claim-select')
                .prop('checked', true);


            $('#headerSelectAll').prop(
                'checked',
                true
            );


            updateSelectedCount();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Clear Selection
    |--------------------------------------------------------------------------
    */

    $('#clearSelection').on(
        'click',
        function () {

            table
                .$('.claim-select')
                .prop('checked', false);


            $('#headerSelectAll').prop(
                'checked',
                false
            );


            updateSelectedCount();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Header Checkbox
    |--------------------------------------------------------------------------
    */

    $('#headerSelectAll').on(
        'change',
        function () {

            const checked =
                $(this).is(':checked');


            table
                .$('.claim-select')
                .prop(
                    'checked',
                    checked
                );


            updateSelectedCount();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Individual Checkbox
    |--------------------------------------------------------------------------
    */

    $('#resubmissionTable').on(
        'change',
        '.claim-select',
        function () {

            updateSelectedCount();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Submit Generate Form
    |--------------------------------------------------------------------------
    |
    | DataTables can move rows outside the normal DOM when paging.
    |
    | Therefore we manually create selected_claims[] hidden inputs
    | before the form is submitted.
    |--------------------------------------------------------------------------
    */

    $('#resubmissionGenerateForm').on(
        'submit',
        function (e) {

            const form = $(this);


            /*
             * Remove previously generated hidden fields.
             */

            form
                .find('.generated-selected-claim')
                .remove();


            const selectedClaims =
                table.$('.claim-select:checked');


            /*
             * Require At Least One Claim
             */

            if (selectedClaims.length === 0) {

                e.preventDefault();

                alert(
                    'Please select at least one claim to generate the resubmission 837P.'
                );

                return false;
            }


            /*
             * Require File Name
             */

            const filename =
                $('#edi_filename')
                .val()
                .trim();


            if (filename === '') {

                e.preventDefault();

                alert(
                    'Please enter the 837P file name.'
                );

                $('#edi_filename').focus();

                return false;
            }


            /*
             * Add Hidden selected_claims[] Fields
             */

            selectedClaims.each(
                function () {

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

                }
            );


            /*
             * Final Confirmation
             */

            const confirmed = confirm(
                'Generate a new resubmission 837P for ' +
                selectedClaims.length +
                ' selected claim(s)?' +
                '\n\nFile: ' +
                filename
            );


            if (!confirmed) {

                e.preventDefault();

                return false;
            }


            /*
             * Prevent Double Click
             */

            $('#generateResubmissionButton')
                .prop('disabled', true)
                .text('Generating...');

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