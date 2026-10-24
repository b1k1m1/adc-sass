@extends('admin.admin_dashboard')

@section('title', 'Claims')

@section('admin')

    <style>
        #claim-lines-table td,
        #claim-lines-table th {
            padding: 2px 4px !important;
        }

        /* Sticky header for the line items */
        #claim-lines-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #f8f9fa !important;
        }

        #claim-lines-table input.form-control-sm {
            padding: 2px 4px !important;
            font-size: 12px !important;
            height: 24px !important;
        }

        .proc-code {
            width: 65px !important;
        }

        .m1 {
            width: 45px !important;
            text-align: center;
        }

        .units {
            width: 50px !important;
            text-align: center;
        }

        .amount-per-unit,
        .total-amount,
        .allowed-field,
        .ins1-paid-field {
            width: 80px !important;
            text-align: right !important;
        }

        .diag1-field,
        .diag2-field {
            width: 80px !important;
        }

        .provider-field {
            width: 70px !important;
        }

        .pos-field {
            width: 55px !important;
        }

        .delete-line-row {
            padding: 0 6px !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Special Claim Badge
        |--------------------------------------------------------------------------
        */

        .claim-no-cell {
            min-width: 105px;
        }

        .claim-no-wrap {
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .claim-no-wrap .claim-no-field {
            width: 80px !important;
            min-width: 80px;
        }

        .special-claim-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            min-width: 18px;
            border-radius: 4px;
            background-color: #6f42c1 !important;
            color: #ffffff !important;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
            cursor: help;
        }


        /*
        |--------------------------------------------------------------------------
        | Claim Status Colors
        |--------------------------------------------------------------------------
        */

        /*
    |--------------------------------------------------------------------------
    | Claim Status Colors
    |--------------------------------------------------------------------------
    */

        /* New / unpaid claim */
        .claim-unpaid-td {
            background-color: #ffffff !important;
            color: #000000 !important;
        }

        .claim-unpaid-td input,
        .claim-unpaid-td .form-control {
            background-color: #ffffff !important;
        }


        /* Claim generated / sent, but no payment yet */
        .claim-generated-td {
            background-color: #e2e3e5 !important;
            /* Gray */
            color: #41464b !important;
        }

        .claim-generated-td input,
        .claim-generated-td .form-control {
            background-color: #e2e3e5 !important;
            color: #41464b !important;
        }


        /* Insurance paid LESS than claim total */
        .claim-partial-td {
            background-color: #ffe5b4 !important;
            /* Orange */
            color: #664d03 !important;
        }

        .claim-partial-td input,
        .claim-partial-td .form-control {
            background-color: #ffe5b4 !important;
            color: #664d03 !important;
        }


        /* Insurance paid EXACTLY claim total */
        .claim-paid-td {
            background-color: #d4edda !important;
            /* Green */
            color: #155724 !important;
        }

        .claim-paid-td input,
        .claim-paid-td .form-control {
            background-color: #d4edda !important;
            color: #155724 !important;
        }


        /* Insurance paid MORE than claim total */
        .claim-overpaid-td {
            background-color: #cfe2ff !important;
            /* Blue */
            color: #084298 !important;
        }

        .claim-overpaid-td input,
        .claim-overpaid-td .form-control {
            background-color: #cfe2ff !important;
            color: #084298 !important;
        }


        /* Rejected claim */
        .claim-rejected-td {
            background-color: #f8d7da !important;
            /* Pink / Red */
            color: #842029 !important;
        }

        .claim-rejected-td input,
        .claim-rejected-td .form-control {
            background-color: #f8d7da !important;
            color: #842029 !important;
        }


        /* Denied claim */
        .claim-denied-td {
            background-color: #f5c2c7 !important;
            /* Stronger Red */
            color: #842029 !important;
        }

        .claim-denied-td input,
        .claim-denied-td .form-control {
            background-color: #f5c2c7 !important;
            color: #842029 !important;
        }
    </style>


    <div class="page-content">

        <div class="container-fluid">


            {{-- ================================================================
             PAGE HEADER
             ================================================================ --}}

            <div class="row mb-2">

                <div class="col-12">

                    <div class="page-title-box d-flex align-items-center justify-content-between p-0 mb-2">

                        <h4 class="mb-0 font-size-18">
                            Claims Data Entry
                        </h4>

                        <div class="page-title-right">

                            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm waves-effect waves-light">

                                {{ t('common.back', 'Back') }}

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            {{-- SUCCESS --}}

            @if (session('success'))
                <div class="alert alert-success py-1 mb-2 small">

                    {{ session('success') }}

                </div>
            @endif


            {{-- ERRORS --}}

            @if ($errors->any())

                <div class="alert alert-danger py-1 mb-2">

                    <ul class="mb-0 small">

                        @foreach ($errors->all() as $e)
                            <li>
                                {{ $e }}
                            </li>
                        @endforeach

                    </ul>

                </div>

            @endif



            {{-- ================================================================
             TOP CARD: CLAIM SERVICE ENTRY
             ================================================================ --}}

            <div class="card shadow-sm mb-3">

                <div class="card-body p-3">

                    <form id="claimForm" method="POST" action="{{ route('claims.store') }}">

                        @csrf


                        {{-- ====================================================
                         SINGLE COMPACT HEADER: SHIFT + MEMBER + SUMMARY
                         ==================================================== --}}

                        <div class="d-flex align-items-center flex-wrap gap-3 mb-2">

                            {{-- SHIFT --}}
                            <div class="d-flex align-items-center">

                                <label for="claimShift" class="me-2 mb-0 small fw-bold text-nowrap">

                                    Shift:

                                </label>

                                <select id="claimShift" class="form-select form-select-sm" style="width:115px;">

                                    <option value="A" {{ $type === 'A' ? 'selected' : '' }}>

                                        Morning

                                    </option>

                                    <option value="N" {{ $type === 'N' ? 'selected' : '' }}>

                                        Noon

                                    </option>

                                    <option value="P" {{ $type === 'P' ? 'selected' : '' }}>

                                        Afternoon

                                    </option>

                                </select>

                            </div>


                            {{-- MEMBER SELECTION --}}
                            <div class="d-flex align-items-center flex-grow-1" style="min-width: 280px; max-width: 460px;">

                                <label class="form-label mb-0 small fw-bold me-2 text-nowrap">

                                    Member:

                                </label>

                                <div class="flex-grow-1">

                                    <select name="ParticipantId" id="ParticipantId" class="form-select form-select-sm"
                                        style="width:100%;">

                                        <option value="">
                                            -- Select Member --
                                        </option>

                                        @foreach ($members as $m)
                                            <option value="{{ $m->ParticipantId }}"
                                                data-diag1="{{ $m->PrimaryDiagnosis1 ?? '' }}"
                                                data-diag2="{{ $m->SecondaryDiag1 ?? '' }}"
                                                data-source-system="{{ $m->source_system ?? '' }}"
                                                {{ (string) $participantId === (string) $m->ParticipantId ? 'selected' : '' }}>

                                                {{ $m->ParticipantId }}
                                                -
                                                {{ $m->LastName }},
                                                {{ $m->FirstName }}

                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                            </div>


                            {{-- INLINE SUMMARY --}}
                            <div class="d-flex align-items-center ms-auto">

                                <div id="memberSummary"
                                    class="border rounded px-3 py-1 bg-light d-flex align-items-center gap-3 small"
                                    style="visibility: hidden; min-height:31px;">

                                    <div>
                                        <span class="text-muted fw-semibold">Total Billed:</span>
                                        <span id="sum_billed" class="text-primary fw-bold ms-1">0.00</span>
                                    </div>

                                    <div class="border-start ps-3">
                                        <span class="text-muted fw-semibold">Total Paid:</span>
                                        <span id="sum_paid" class="text-success fw-bold ms-1">0.00</span>
                                    </div>

                                    <div class="border-start ps-3">
                                        <span class="text-muted fw-semibold">Balance:</span>
                                        <span id="sum_balance" class="text-danger fw-bold ms-1">0.00</span>
                                    </div>

                                </div>

                            </div>

                        </div>



                        {{-- ====================================================
                         SERVICE LINE GRID (ONLY THIS PART SCROLLS)
                         ==================================================== --}}

                        <div class="table-responsive border rounded" id="claim-lines-container"
                            style="max-height: 250px; overflow-y: auto; overflow-x: auto;">

                            <table class="table table-sm table-bordered align-middle mb-0" id="claim-lines-table">

                                <thead class="table-light small text-nowrap">

                                    <tr>

                                        <th style="width:110px;">
                                            From
                                        </th>

                                        <th style="width:110px;">
                                            To
                                        </th>

                                        <th style="width:70px;">
                                            Proc
                                        </th>

                                        <th style="width:55px;">
                                            M1
                                        </th>

                                        <th style="width:55px;" class="text-center">

                                            Units

                                        </th>

                                        <th style="width:80px;" class="text-end">

                                            {{ t('common.amount', 'Amount') }}

                                        </th>

                                        <th style="width:80px;" class="text-end">

                                            Total

                                        </th>

                                        <th style="width:80px;">
                                            Diag1
                                        </th>

                                        <th style="width:80px;">
                                            Diag2
                                        </th>

                                        <th style="width:35px;" class="text-center">

                                            1

                                        </th>

                                        <th style="width:35px;" class="text-center">

                                            2

                                        </th>

                                        <th style="width:70px;">
                                            Prov
                                        </th>

                                        <th style="width:55px;">
                                            POS
                                        </th>

                                        <th style="width:80px;" class="text-end">

                                            Allowed

                                        </th>

                                        <th style="width:105px;">
                                            Claim #
                                        </th>

                                        <th style="width:100px;" class="text-end">

                                            Ins 1 Paid

                                        </th>

                                        <th style="width:40px;"></th>

                                    </tr>

                                </thead>


                                <tbody id="claim-lines-body" class="small">


                                    <tr class="claim-line-row" data-index="0">


                                        <td>

                                            <input type="date" name="lines[0][service_from]"
                                                class="form-control form-control-sm service-from">

                                        </td>


                                        <td>

                                            <input type="date" name="lines[0][service_to]"
                                                class="form-control form-control-sm service-to">

                                        </td>


                                        <td>

                                            <input type="text" name="lines[0][proc_code]"
                                                class="form-control form-control-sm proc-code" value="S5102"
                                                maxlength="6">

                                        </td>


                                        <td>

                                            <input type="text" name="lines[0][m1]"
                                                class="form-control form-control-sm m1" value="U1" maxlength="3">

                                        </td>


                                        <td>

                                            <input type="number" name="lines[0][units]"
                                                class="form-control form-control-sm units text-center" min="0"
                                                max="7">

                                        </td>


                                        <td>

                                            <input type="number" step="0.01" name="lines[0][amount_per_unit]"
                                                class="form-control form-control-sm amount-per-unit text-end"
                                                value="" readonly>

                                        </td>


                                        <td>

                                            <input type="number" step="0.01" name="lines[0][total_amount]"
                                                class="form-control form-control-sm total-amount text-end" readonly>

                                        </td>


                                        <td>

                                            <input type="text" name="lines[0][diag1]"
                                                class="form-control form-control-sm diag1-field" readonly>

                                        </td>


                                        <td>

                                            <input type="text" name="lines[0][diag2]"
                                                class="form-control form-control-sm diag2-field" readonly>

                                        </td>


                                        <td class="text-center">

                                            <input type="checkbox" class="form-check-input diag-box-1" disabled>

                                        </td>


                                        <td class="text-center">

                                            <input type="checkbox" class="form-check-input diag-box-2" disabled>

                                        </td>


                                        <td>

                                            <input type="text" name="lines[0][provider]"
                                                class="form-control form-control-sm provider-field" value="DM"
                                                readonly>

                                        </td>


                                        <td>

                                            <input type="text" name="lines[0][pos]"
                                                class="form-control form-control-sm pos-field" value="99" readonly>

                                        </td>


                                        <td>

                                            <input type="number" step="0.01" name="lines[0][allowed]"
                                                class="form-control form-control-sm allowed-field text-end" readonly>

                                        </td>


                                        <td class="claim-no-cell">

                                            <div class="claim-no-wrap">

                                                <input type="text" name="lines[0][claim_no]"
                                                    class="form-control form-control-sm claim-no-field" maxlength="6">

                                            </div>

                                        </td>


                                        <td>

                                            <input type="number" step="0.01" name="lines[0][ins1_paid]"
                                                class="form-control form-control-sm ins1-paid-field text-end"
                                                value="0.00">

                                        </td>


                                        <td class="text-center">

                                            <button type="button" class="btn btn-sm btn-outline-danger delete-line-row"
                                                title="Delete row">

                                                &times;

                                            </button>

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>



                        {{-- ====================================================
                         ACTIONS: ADD ROW + SAVE + CLEAR
                         ==================================================== --}}

                        <div class="d-flex justify-content-end gap-2 mt-2">

                            <button type="button" class="btn btn-success btn-sm px-3" id="add-line-row">

                                + Add Row

                            </button>

                            <button type="submit" class="btn btn-primary btn-sm px-3">

                                {{ t('common.save', 'Save') }}

                            </button>

                            <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="reset-form">

                                Clear

                            </button>

                        </div>

                    </form>

                </div>

            </div>



            {{-- ================================================================
             BOTTOM CARD: PAYMENT / ADJUSTMENT HISTORY
             ================================================================ --}}

            <div class="card shadow-sm">

                <div class="card-body p-3">

                    <div class="d-flex justify-content-between align-items-center mb-2">

                        <h6 class="mb-0 font-size-14">

                            Insurance Payment / Adjustment History

                        </h6>

                        <div class="small text-muted">
                            Historical Medisoft + ERA Transactions
                        </div>

                    </div>


                    <div class="table-responsive border rounded" style="max-height: 260px; overflow-y: auto;">

                        <table class="table table-sm table-hover table-striped mb-0" id="payments-table">

                            <thead class="table-light sticky-top">

                                <tr>

                                    <th class="small">
                                        Date
                                    </th>

                                    <th class="small">
                                        Pay/Adj Code
                                    </th>

                                    <th class="small">
                                        Who Paid / Payer
                                    </th>

                                    <th class="small">
                                        Description
                                    </th>

                                    <th class="small">
                                        Provider
                                    </th>

                                    <th class="small text-end">
                                        Amount
                                    </th>

                                    <th class="small">
                                        Check Number
                                    </th>

                                    <th class="small text-end">
                                        Unapplied
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <tr>

                                    <td colspan="8" class="text-center small text-muted py-3">

                                        Select a member to see payment history.

                                    </td>

                                </tr>

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
        /*
        |--------------------------------------------------------------------------
        | Date Formatting
        |--------------------------------------------------------------------------
        */

        function formatDateMDY(dateStr) {
            if (!dateStr) {
                return '';
            }

            const parts =
                dateStr.split('-');

            if (parts.length !== 3) {
                return dateStr;
            }

            return parts[1] +
                '/' +
                parts[2] +
                '/' +
                parts[0];
        }



        /*
        |--------------------------------------------------------------------------
        | Line Counter
        |--------------------------------------------------------------------------
        */

        let lineIndex = 1;



        /*
        |--------------------------------------------------------------------------
        | Selected Member Option
        |--------------------------------------------------------------------------
        */

        function getSelectedMemberOption() {
            const select =
                document.getElementById(
                    'ParticipantId'
                );

            if (
                !select ||
                select.selectedIndex < 0
            ) {
                return null;
            }

            return select.options[
                select.selectedIndex
            ];
        }



        /*
        |--------------------------------------------------------------------------
        | Recalculate Row
        |--------------------------------------------------------------------------
        */

        function recalcRow($row) {
            let units =
                parseInt(
                    $row.find('.units').val() || 0,
                    10
                );

            let amount =
                parseFloat(
                    $row.find('.amount-per-unit').val() ||
                    0
                );


            if (units < 0) {
                units = 0;
            }

            if (units > 7) {
                units = 7;
            }


            $row.find('.units').val(
                isNaN(units) ?
                '' :
                units
            );


            const total =
                units * amount;


            if (
                !isNaN(total) &&
                amount > 0
            ) {

                $row
                    .find('.total-amount')
                    .val(
                        total.toFixed(2)
                    );

                $row
                    .find('.allowed-field')
                    .val(
                        total.toFixed(2)
                    );

            } else {

                $row
                    .find('.total-amount')
                    .val('');

                $row
                    .find('.allowed-field')
                    .val('');
            }


            updatePaymentColorForRow(
                $row
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Load Service Rate
        |--------------------------------------------------------------------------
        */

        function loadServiceRate($row) {
            const serviceDate =
                $row
                .find('.service-from')
                .val();


            if (!serviceDate) {

                $row
                    .find('.amount-per-unit')
                    .val('');

                recalcRow($row);

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Show Loading State
            |--------------------------------------------------------------------------
            */

            $row
                .find('.amount-per-unit')
                .val('');


            $.ajax({

                url: "{{ route('claims.service.rate') }}",

                type: 'GET',

                data: {
                    service_date: serviceDate
                },


                success: function(data) {
                    if (
                        data.success &&
                        data.rate !== null
                    ) {

                        $row
                            .find('.amount-per-unit')
                            .val(
                                data.rate
                            );

                        recalcRow(
                            $row
                        );

                    } else {

                        $row
                            .find('.amount-per-unit')
                            .val('');

                        $row
                            .find('.total-amount')
                            .val('');

                        $row
                            .find('.allowed-field')
                            .val('');

                        alert(
                            data.message ||
                            'No service rate found for the selected date.'
                        );
                    }
                },


                error: function() {
                    $row
                        .find('.amount-per-unit')
                        .val('');

                    $row
                        .find('.total-amount')
                        .val('');

                    $row
                        .find('.allowed-field')
                        .val('');

                    alert(
                        'Unable to retrieve the claim service rate.'
                    );
                }

            });
        }



        /*
        |--------------------------------------------------------------------------
        | Diagnosis Checkboxes
        |--------------------------------------------------------------------------
        */

        function updateDiagCheckboxesForRow($row) {
            const d1 =
                (
                    $row
                    .find('.diag1-field')
                    .val() ||
                    ''
                ).trim();


            const d2 =
                (
                    $row
                    .find('.diag2-field')
                    .val() ||
                    ''
                ).trim();


            const box1 =
                $row.find('.diag-box-1');

            const box2 =
                $row.find('.diag-box-2');


            if (
                d1 &&
                !d2
            ) {

                box1.prop(
                    'checked',
                    true
                );

                box2.prop(
                    'checked',
                    false
                );

            } else if (
                d1 &&
                d2
            ) {

                box1.prop(
                    'checked',
                    true
                );

                box2.prop(
                    'checked',
                    true
                );

            } else {

                box1.prop(
                    'checked',
                    false
                );

                box2.prop(
                    'checked',
                    false
                );
            }
        }



        /*
        |--------------------------------------------------------------------------
        | Apply Diagnosis To Rows
        |--------------------------------------------------------------------------
        */

        function applyDiagsToAllRows(
            diag1,
            diag2
        ) {
            $('#claim-lines-body .claim-line-row')
                .each(function() {
                    const $row =
                        $(this);

                    $row
                        .find('.diag1-field')
                        .val(
                            diag1 || ''
                        );

                    $row
                        .find('.diag2-field')
                        .val(
                            diag2 || ''
                        );

                    updateDiagCheckboxesForRow(
                        $row
                    );
                });
        }



        /*
        |--------------------------------------------------------------------------
        | Claim Row Color
        |--------------------------------------------------------------------------
        */

        function updatePaymentColorForRow($row) {


            const total = parseFloat(
                $row.find('.total-amount').val()
            ) || 0;

            const paid = parseFloat(
                $row.find('.ins1-paid-field').val()
            ) || 0;

            const claimNo = (
                $row.find('.claim-no-field').val() || ''
            ).trim();



            const $cells = $row.find('td');

            $cells.removeClass(
                'claim-unpaid-td ' +
                'claim-generated-td ' +
                'claim-partial-td ' +
                'claim-paid-td ' +
                'claim-overpaid-td'
            );

            if (total > 0 && paid > total + 0.01) {
                $cells.addClass('claim-overpaid-td');

            } else if (
                total > 0 &&
                paid > 0 &&
                paid < total - 0.01
            ) {
                $cells.addClass('claim-partial-td');

            } else if (
                total > 0 &&
                paid > 0 &&
                Math.abs(paid - total) <= 0.01
            ) {
                $cells.addClass('claim-paid-td');

            } else if (
                claimNo !== '' &&
                paid <= 0
            ) {
                $cells.addClass('claim-generated-td');

            } else {
                $cells.addClass('claim-unpaid-td');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Create Blank Service Line
        |--------------------------------------------------------------------------
        */

        function createNewRow(index) {
            return `

            <tr class="claim-line-row"
                data-index="${index}">

                <td>

                    <input type="date"
                           name="lines[${index}][service_from]"
                           class="form-control form-control-sm service-from">

                </td>


                <td>

                    <input type="date"
                           name="lines[${index}][service_to]"
                           class="form-control form-control-sm service-to">

                </td>


                <td>

                    <input type="text"
                           name="lines[${index}][proc_code]"
                           class="form-control form-control-sm proc-code"
                           value="S5102"
                           maxlength="6">

                </td>


                <td>

                    <input type="text"
                           name="lines[${index}][m1]"
                           class="form-control form-control-sm m1"
                           value="U1"
                           maxlength="3">

                </td>


                <td>

                    <input type="number"
                           name="lines[${index}][units]"
                           class="form-control form-control-sm units text-center"
                           min="0"
                           max="7">

                </td>


                <td>

                    <input type="number"
                           step="0.01"
                           name="lines[${index}][amount_per_unit]"
                           class="form-control form-control-sm amount-per-unit text-end"
                           value=""
                           readonly>

                </td>


                <td>

                    <input type="number"
                           step="0.01"
                           name="lines[${index}][total_amount]"
                           class="form-control form-control-sm total-amount text-end"
                           readonly>

                </td>


                <td>

                    <input type="text"
                           name="lines[${index}][diag1]"
                           class="form-control form-control-sm diag1-field"
                           readonly>

                </td>


                <td>

                    <input type="text"
                           name="lines[${index}][diag2]"
                           class="form-control form-control-sm diag2-field"
                           readonly>

                </td>


                <td class="text-center">

                    <input type="checkbox"
                           class="form-check-input diag-box-1"
                           disabled>

                </td>


                <td class="text-center">

                    <input type="checkbox"
                           class="form-check-input diag-box-2"
                           disabled>

                </td>


                <td>

                    <input type="text"
                           name="lines[${index}][provider]"
                           class="form-control form-control-sm provider-field"
                           value="DM"
                           readonly>

                </td>


                <td>

                    <input type="text"
                           name="lines[${index}][pos]"
                           class="form-control form-control-sm pos-field"
                           value="99"
                           readonly>

                </td>


                <td>

                    <input type="number"
                           step="0.01"
                           name="lines[${index}][allowed]"
                           class="form-control form-control-sm allowed-field text-end"
                           readonly>

                </td>


                <td class="claim-no-cell">

                    <div class="claim-no-wrap">

                        <input type="text"
                               name="lines[${index}][claim_no]"
                               class="form-control form-control-sm claim-no-field"
                               maxlength="6">

                    </div>

                </td>


                <td>

                    <input type="number"
                           step="0.01"
                           name="lines[${index}][ins1_paid]"
                           class="form-control form-control-sm ins1-paid-field text-end"
                           value="0.00">

                </td>


                <td class="text-center">

                    <button type="button"
                            class="btn btn-sm btn-outline-danger delete-line-row"
                            title="Delete row">

                        &times;

                    </button>

                </td>

            </tr>

        `;
        }



        /*
        |--------------------------------------------------------------------------
        | Reset Grid
        |--------------------------------------------------------------------------
        */

        function resetGridToSingleEmptyRow(
            diag1,
            diag2
        ) {
            const $tbody =
                $('#claim-lines-body');


            $tbody.empty();

            lineIndex = 0;


            const $newRow =
                $(createNewRow(
                    lineIndex++
                ));

            $tbody.append($newRow);


            applyDiagsToAllRows(
                diag1 || '',
                diag2 || ''
            );


            const todayStr =
                new Date()
                .toISOString()
                .split('T')[0];


            $tbody
                .find(
                    '.service-from, .service-to'
                )
                .attr(
                    'max',
                    todayStr
                );
        }



        /*
        |--------------------------------------------------------------------------
        | Escape HTML Attribute
        |--------------------------------------------------------------------------
        */

        function escapeHtmlAttribute(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }



        /*
        |--------------------------------------------------------------------------
        | Existing Claim Lines
        |--------------------------------------------------------------------------
        */

        function loadExistingLines(
            participantId,
            defaultDiag1,
            defaultDiag2
        ) {
            const $tbody =
                $('#claim-lines-body');


            $tbody.html(`

            <tr>

                <td colspan="17"
                    class="text-center small text-muted">

                    Loading...

                </td>

            </tr>

        `);


            $.get(
                    "{{ url('/claims/member-lines') }}/" +
                    encodeURIComponent(
                        participantId
                    )
                )

                .done(function(rows) {
                    $tbody.empty();

                    lineIndex = 0;


                    /*
                    |--------------------------------------------------------------------------
                    | No Existing Claims
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !rows ||
                        !rows.length
                    ) {

                        $tbody.append(
                            createNewRow(
                                lineIndex++
                            )
                        );


                        applyDiagsToAllRows(
                            defaultDiag1 || '',
                            defaultDiag2 || ''
                        );


                        const todayStr =
                            new Date()
                            .toISOString()
                            .split('T')[0];


                        $tbody
                            .find(
                                '.service-from, .service-to'
                            )
                            .attr(
                                'max',
                                todayStr
                            );


                        return;
                    }



                    /*
                    |--------------------------------------------------------------------------
                    | Existing Claims
                    |--------------------------------------------------------------------------
                    */

                    rows.forEach(function(r) {
                        const i =
                            lineIndex++;


                        /*
                        |--------------------------------------------------------------------------
                        | Special Claim Indicator
                        |--------------------------------------------------------------------------
                        */

                        const isSpecialClaim =
                            parseInt(
                                r.is_special_claim ?? 0,
                                10
                            ) === 1;


                        const originalClaimDisplay =
                            r.original_claim_no ??
                            r.original_claim_id ??
                            '';


                        const specialType =
                            (r.special_claim_type ?? '')
                            .replace(/_/g, ' ');


                        const specialReason =
                            r.special_claim_reason ?? '';


                        let specialTitle =
                            'Special Claim';


                        if (originalClaimDisplay !== '') {
                            specialTitle +=
                                ' | Original Claim: ' +
                                originalClaimDisplay;
                        }


                        if (specialType !== '') {
                            specialTitle +=
                                ' | Type: ' +
                                specialType;
                        }


                        if (specialReason !== '') {
                            specialTitle +=
                                ' | Reason: ' +
                                specialReason;
                        }


                        const specialBadge =
                            isSpecialClaim ?
                            `
                            <span class="special-claim-badge"
                                  title="${escapeHtmlAttribute(specialTitle)}">
                                S
                            </span>
                          ` :
                            '';


                        const html = `

                    <tr class="claim-line-row"
                        data-index="${i}"
                        data-claim-status="${(r.status ?? '').toLowerCase()}">


                        <td>

                            <input type="hidden"
                                   name="lines[${i}][id]"
                                   value="${r.id}">


                            <input type="date"
                                   name="lines[${i}][service_from]"
                                   class="form-control form-control-sm service-from"
                                   value="${r.service_from ?? ''}">

                        </td>


                        <td>

                            <input type="date"
                                   name="lines[${i}][service_to]"
                                   class="form-control form-control-sm service-to"
                                   value="${r.service_to ?? ''}">

                        </td>


                        <td>

                            <input type="text"
                                   name="lines[${i}][proc_code]"
                                   class="form-control form-control-sm proc-code"
                                   value="${r.proc_code ?? 'S5102'}"
                                   maxlength="6">

                        </td>


                        <td>

                            <input type="text"
                                   name="lines[${i}][m1]"
                                   class="form-control form-control-sm m1"
                                   value="${r.m1 ?? 'U1'}"
                                   maxlength="3">

                        </td>


                        <td>

                            <input type="number"
                                   name="lines[${i}][units]"
                                   class="form-control form-control-sm units text-center"
                                   min="0"
                                   max="7"
                                   value="${r.units ?? ''}">

                        </td>


                        <td>

                            <input type="number"
                                   step="0.01"
                                   name="lines[${i}][amount_per_unit]"
                                   class="form-control form-control-sm amount-per-unit text-end"
                                   value="${r.amount_per_unit ?? ''}"
                                   readonly>

                        </td>


                        <td>

                            <input type="number"
                                   step="0.01"
                                   name="lines[${i}][total_amount]"
                                   class="form-control form-control-sm total-amount text-end"
                                   value="${r.total_amount ?? ''}"
                                   readonly>

                        </td>


                        <td>

                            <input type="text"
                                   name="lines[${i}][diag1]"
                                   class="form-control form-control-sm diag1-field"
                                   value="${r.diag1 ?? ''}"
                                   readonly>

                        </td>


                        <td>

                            <input type="text"
                                   name="lines[${i}][diag2]"
                                   class="form-control form-control-sm diag2-field"
                                   value="${r.diag2 ?? ''}"
                                   readonly>

                        </td>


                        <td class="text-center">

                            <input type="checkbox"
                                   class="form-check-input diag-box-1"
                                   disabled>

                        </td>


                        <td class="text-center">

                            <input type="checkbox"
                                   class="form-check-input diag-box-2"
                                   disabled>

                        </td>


                        <td>

                            <input type="text"
                                   name="lines[${i}][provider]"
                                   class="form-control form-control-sm provider-field"
                                   value="${r.provider ?? 'DM'}"
                                   readonly>

                        </td>


                        <td>

                            <input type="text"
                                   name="lines[${i}][pos]"
                                   class="form-control form-control-sm pos-field"
                                   value="${r.pos ?? '99'}"
                                   readonly>

                        </td>


                        <td>

                            <input type="number"
                                   step="0.01"
                                   name="lines[${i}][allowed]"
                                   class="form-control form-control-sm allowed-field text-end"
                                   value="${r.allowed ?? ''}"
                                   readonly>

                        </td>


                        <td class="claim-no-cell">

                            <div class="claim-no-wrap">



                                <input type="text"
                                       name="lines[${i}][claim_no]"
                                       class="form-control form-control-sm claim-no-field"
                                       value="${r.claim_no ?? ''}"
                                       maxlength="6">

                                ${specialBadge}

                            </div>

                        </td>


                        <td>

                            <input type="number"
                                   step="0.01"
                                   name="lines[${i}][ins1_paid]"
                                   class="form-control form-control-sm ins1-paid-field text-end"
                                   value="${r.ins1_paid ?? '0.00'}">

                        </td>


                        <td class="text-center">

                            <button type="button"
                                    class="btn btn-sm btn-outline-danger delete-line-row"
                                    title="Delete row">

                                &times;

                            </button>

                        </td>

                    </tr>

                `;


                        $tbody.append(
                            html
                        );

                    });



                    const todayStr =
                        new Date()
                        .toISOString()
                        .split('T')[0];


                    $tbody
                        .find(
                            '.service-from, .service-to'
                        )
                        .attr(
                            'max',
                            todayStr
                        );


                    $('#claim-lines-body .claim-line-row')
                        .each(function() {
                            const $row =
                                $(this);


                            updateDiagCheckboxesForRow(
                                $row
                            );


                            updatePaymentColorForRow(
                                $row
                            );
                        });

                })

                .fail(function() {
                    $tbody.html(`

                <tr>

                    <td colspan="17"
                        class="text-center small text-danger">

                        Error loading previous claims for this member.

                    </td>

                </tr>

            `);
                });
        }



        /*
        |--------------------------------------------------------------------------
        | Financial Summary
        |--------------------------------------------------------------------------
        */

        function loadMemberSummary(
            participantId
        ) {
            if (!participantId) {

                $('#memberSummary')
                    .css('visibility', 'hidden');

                return;
            }


            $.ajax({

                url: "{{ url('/claims/member-summary') }}/" +
                    encodeURIComponent(
                        participantId
                    ),

                type: 'GET',


                success: function(data) {
                    $('#sum_billed')
                        .text(
                            data.billed
                        );

                    $('#sum_paid')
                        .text(
                            data.paid
                        );

                    $('#sum_balance')
                        .text(
                            data.balance
                        );

                    $('#memberSummary')
                        .css('visibility', 'visible');
                },


                error: function() {
                    $('#memberSummary')
                        .css('visibility', 'hidden');
                }

            });
        }



        /*
        |--------------------------------------------------------------------------
        | Clear Payments
        |--------------------------------------------------------------------------
        */

        function clearPaymentsTable() {
            $('#payments-table tbody')
                .html(`

                <tr>

                    <td colspan="8"
                        class="text-center small text-muted py-3">

                        Select a member to see payment history.

                    </td>

                </tr>

            `);
        }



        /*
        |--------------------------------------------------------------------------
        | Load Payments
        |--------------------------------------------------------------------------
        */

        function loadPayments(
            participantId
        ) {
            const $tbody =
                $('#payments-table tbody');


            if (!participantId) {

                clearPaymentsTable();

                return;
            }


            $tbody.html(`

            <tr>

                <td colspan="8"
                    class="text-center small text-muted">

                    Loading payment history...

                </td>

            </tr>

        `);


            $.get(
                    "{{ url('/claims/payments') }}/" +
                    encodeURIComponent(
                        participantId
                    )
                )

                .done(function(rows) {
                    $tbody.empty();


                    if (
                        !rows ||
                        !rows.length
                    ) {

                        $tbody.html(`

                    <tr>

                        <td colspan="8"
                            class="text-center small text-muted py-3">

                            No payment history found for this member.

                        </td>

                    </tr>

                `);

                        return;
                    }


                    rows.forEach(function(p) {
                        const paymentDate =
                            formatDateMDY(
                                p.payment_date
                            );


                        const code =
                            p.transaction_type ||
                            p.pay_adj_code ||
                            '';


                        const source =
                            p.source ||
                            '';


                        const whoPaid =
                            p.payer_name ||
                            p.who_paid ||
                            '';


                        const description =
                            p.description ||
                            '';


                        const provider =
                            p.provider ||
                            '';


                        const checkNumber =
                            p.trace_number ||
                            p.check_number ||
                            '';


                        const rawAmount =
                            p.amount !== undefined && p.amount !== null ?
                            p.amount :
                            (
                                p.original_amount !== undefined && p.original_amount !== null ?
                                p.original_amount :
                                (
                                    p.total_paid !== undefined && p.total_paid !== null ?
                                    p.total_paid :
                                    0
                                )
                            );


                        const amount =
                            parseFloat(
                                rawAmount
                            );


                        const unapplied =
                            parseFloat(
                                p.unapplied_amount ||
                                0
                            );


                        const amountStr =
                            isNaN(amount) ?
                            '' :
                            amount.toFixed(2);


                        const unappliedStr =
                            isNaN(unapplied) ?
                            '' :
                            unapplied.toFixed(2);


                        const codeDescriptions = {
                            'IP': 'Ins Payment',
                            'IA': 'Ins Adjustment',
                            'IW': 'Insurance Withhold',
                            'DD': 'Insurance Deductible',
                            'IT': 'Insurance Take Back',
                            'ERA': 'ERA Payment'
                        };


                        const codeDescription =
                            p.code_description ||
                            codeDescriptions[code] ||
                            '';


                        let badgeClass =
                            'bg-secondary';


                        if (code === 'IP') {

                            badgeClass =
                                'bg-success';

                        } else if (code === 'IT') {

                            badgeClass =
                                'bg-danger';

                        } else if (
                            code === 'IA' ||
                            code === 'IW' ||
                            code === 'DD'
                        ) {

                            badgeClass =
                                'bg-warning text-dark';

                        } else if (
                            source === 'ERA' &&
                            code === 'ERA'
                        ) {

                            badgeClass =
                                'bg-primary';

                        } else if (
                            source === 'ERA' &&
                            code.startsWith('CO')
                        ) {

                            badgeClass =
                                'bg-warning text-dark';

                        } else if (
                            source === 'ERA' &&
                            code.startsWith('PR')
                        ) {

                            badgeClass =
                                'bg-info text-dark';

                        } else if (
                            source === 'ERA'
                        ) {

                            badgeClass =
                                'bg-secondary';
                        }


                        const descriptionHtml =
                            codeDescription &&
                            codeDescription !== code ?
                            `
                            <span class="ms-1">
                                ${codeDescription}
                            </span>
                        ` :
                            '';


                        const codeHtml = `
                    <span class="badge ${badgeClass}">
                        ${code}
                    </span>
                    ${descriptionHtml}
                `;


                        let amountClass =
                            'text-end';


                        if (amount < 0) {

                            amountClass +=
                                ' text-success';

                        } else if (
                            code === 'IT' ||
                            (
                                source === 'ERA' &&
                                code !== 'ERA'
                            )
                        ) {

                            amountClass +=
                                ' text-danger';
                        }


                        $tbody.append(`

                    <tr>

                        <td class="small">
                            ${paymentDate}
                        </td>

                        <td class="small text-nowrap">
                            ${codeHtml}
                        </td>

                        <td class="small">
                            ${whoPaid}
                        </td>

                        <td class="small">
                            ${description}
                        </td>

                        <td class="small">
                            ${provider}
                        </td>

                        <td class="small ${amountClass}">
                            ${amountStr}
                        </td>

                        <td class="small">
                            ${checkNumber}
                        </td>

                        <td class="small text-end">
                            ${unappliedStr}
                        </td>

                    </tr>

                `);

                    });

                })

                .fail(function() {
                    $tbody.html(`

                <tr>

                    <td colspan="8"
                        class="text-center small text-danger py-3">

                        Error loading payment history for this member.

                    </td>

                </tr>

            `);
                });
        }



        /*
        |--------------------------------------------------------------------------
        | Document Ready
        |--------------------------------------------------------------------------
        */

        $(document).ready(function() {
            const todayStr =
                new Date()
                .toISOString()
                .split('T')[0];


            $('.service-from, .service-to')
                .attr(
                    'max',
                    todayStr
                );


            /*
            |--------------------------------------------------------------------------
            | Shift Select2
            |--------------------------------------------------------------------------
            */

            $('#claimShift').select2({

                width: '115px',

                minimumResultsForSearch: Infinity

            });



            /*
            |--------------------------------------------------------------------------
            | Member Select2
            |--------------------------------------------------------------------------
            */

            $('#ParticipantId').select2({

                width: '100%',

                placeholder: '-- Search Member --',

                allowClear: true

            });



            /*
            |--------------------------------------------------------------------------
            | Shift Change
            |--------------------------------------------------------------------------
            */

            $('#claimShift').on(
                'change',
                function() {
                    const shift =
                        $(this).val() ||
                        'A';


                    window.location.href =
                        "{{ route('claims.create') }}" +
                        '?type=' +
                        encodeURIComponent(
                            shift
                        );
                }
            );



            /*
            |--------------------------------------------------------------------------
            | Member Change
            |--------------------------------------------------------------------------
            */

            $('#ParticipantId').on(
                'change',
                function() {
                    const participantId =
                        $(this).val();


                    const opt =
                        getSelectedMemberOption();


                    const diag1 =
                        opt ?
                        (
                            opt.getAttribute(
                                'data-diag1'
                            ) ||
                            ''
                        ) :
                        '';


                    const diag2 =
                        opt ?
                        (
                            opt.getAttribute(
                                'data-diag2'
                            ) ||
                            ''
                        ) :
                        '';


                    const msg =
                        document.querySelector(
                            '.alert.alert-success'
                        );


                    if (msg) {
                        msg.style.display =
                            'none';
                    }


                    if (participantId) {

                        loadExistingLines(
                            participantId,
                            diag1,
                            diag2
                        );


                        loadPayments(
                            participantId
                        );


                        loadMemberSummary(
                            participantId
                        );

                    } else {

                        resetGridToSingleEmptyRow(
                            '',
                            ''
                        );


                        clearPaymentsTable();


                        $('#memberSummary')
                            .css('visibility', 'hidden');
                    }
                }
            );



            /*
            |--------------------------------------------------------------------------
            | Automatically Load Selected Member
            |--------------------------------------------------------------------------
            */

            if (
                $('#ParticipantId').val()
            ) {

                $('#ParticipantId')
                    .trigger(
                        'change'
                    );
            }



            /*
            |--------------------------------------------------------------------------
            | Service From Changed
            |--------------------------------------------------------------------------
            |
            | THIS is what retrieves 94.66 / 95.96 etc. from claim_service_rates.
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'change',
                '.service-from',
                function() {
                    const $row =
                        $(this)
                        .closest(
                            '.claim-line-row'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | If To Date Is Blank, Default It To From Date
                    |--------------------------------------------------------------------------
                    */

                    const serviceFrom =
                        $(this).val();


                    const $serviceTo =
                        $row.find(
                            '.service-to'
                        );


                    if (
                        serviceFrom &&
                        !$serviceTo.val()
                    ) {

                        $serviceTo.val(
                            serviceFrom
                        );
                    }


                    loadServiceRate(
                        $row
                    );
                }
            );



            /*
            |--------------------------------------------------------------------------
            | Service To Changed
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'change',
                '.service-to',
                function() {
                    const $row =
                        $(this)
                        .closest(
                            '.claim-line-row'
                        );


                    recalcRow(
                        $row
                    );
                }
            );



            /*
            |--------------------------------------------------------------------------
            | Units Changed
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'input',
                '.units',
                function() {
                    const $row =
                        $(this)
                        .closest(
                            '.claim-line-row'
                        );


                    recalcRow(
                        $row
                    );
                }
            );



            /*
            |--------------------------------------------------------------------------
            | Insurance Paid Changed
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'input change',
                '.ins1-paid-field',
                function() {
                    updatePaymentColorForRow(
                        $(this)
                        .closest(
                            '.claim-line-row'
                        )
                    );
                }
            );



            /*
            |--------------------------------------------------------------------------
            | Claim Number Changed
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'input change',
                '.claim-no-field',
                function() {
                    updatePaymentColorForRow(
                        $(this)
                        .closest(
                            '.claim-line-row'
                        )
                    );
                }
            );



            /*
            |--------------------------------------------------------------------------
            | Add Row
            |--------------------------------------------------------------------------
            */

            $('#add-line-row').on(
                'click',
                function() {
                    const $tbody =
                        $('#claim-lines-body');

                    const $newRow =
                        $(createNewRow(
                            lineIndex++
                        ));


                    $tbody.append($newRow);


                    $newRow
                        .find(
                            '.service-from, .service-to'
                        )
                        .attr(
                            'max',
                            todayStr
                        );


                    const opt =
                        getSelectedMemberOption();


                    if (
                        opt &&
                        opt.value
                    ) {

                        const diag1 =
                            opt.getAttribute(
                                'data-diag1'
                            ) ||
                            '';


                        const diag2 =
                            opt.getAttribute(
                                'data-diag2'
                            ) ||
                            '';


                        applyDiagsToAllRows(
                            diag1,
                            diag2
                        );
                    }


                    /* Scroll to the bottom and focus on the From date field */
                    const container =
                        document.getElementById('claim-lines-container');

                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }

                    $newRow
                        .find('.service-from')
                        .trigger('focus');
                }
            );



            /*
            |--------------------------------------------------------------------------
            | Delete Row
            |--------------------------------------------------------------------------
            */

            $(document).on(
                'click',
                '.delete-line-row',
                function() {
                    const $rows =
                        $(
                            '#claim-lines-body .claim-line-row'
                        );


                    if (
                        $rows.length <= 1
                    ) {
                        return;
                    }


                    $(this)
                        .closest('tr')
                        .remove();
                }
            );



            /*
            |--------------------------------------------------------------------------
            | Clear
            |--------------------------------------------------------------------------
            */

            $('#reset-form').on(
                'click',
                function() {
                    $('#ParticipantId')
                        .val(null)
                        .trigger(
                            'change'
                        );


                    resetGridToSingleEmptyRow(
                        '',
                        ''
                    );


                    clearPaymentsTable();


                    $('#memberSummary')
                        .css('visibility', 'hidden');
                }
            );



            /*
            |--------------------------------------------------------------------------
            | Initial Status
            |--------------------------------------------------------------------------
            */

            $('#claim-lines-body .claim-line-row')
                .each(function() {
                    const $row =
                        $(this);


                    updateDiagCheckboxesForRow(
                        $row
                    );


                    updatePaymentColorForRow(
                        $row
                    );
                });

        });
    </script>

@endsection
