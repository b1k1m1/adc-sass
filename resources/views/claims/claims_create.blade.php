@extends('admin.admin_dashboard')

@section('title', 'Claims')

@section('admin')

<style>

    #claim-lines-table td,
    #claim-lines-table th {
        padding: 2px 4px !important;
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
    | Claim Status Colors
    |--------------------------------------------------------------------------
    */

    .claim-generated-td {
        background-color: #d9edf7 !important;
    }

    .claim-generated-td input,
    .claim-generated-td .form-control {
        background-color: #d9edf7 !important;
    }


    .claim-unpaid-td {
        background-color: #ffffff !important;
        color: #000000 !important;
    }

    .claim-unpaid-td input,
    .claim-unpaid-td .form-control {
        background-color: #ffffff !important;
    }


    .claim-partial-td {
        background-color: #fff3cd !important;
    }

    .claim-partial-td input,
    .claim-partial-td .form-control {
        background-color: #fff3cd !important;
    }


    .claim-paid-td {
        background-color: #d4edda !important;
    }

    .claim-paid-td input,
    .claim-paid-td .form-control {
        background-color: #d4edda !important;
    }


    .claim-overpaid-td {
        background-color: #cfe8ff !important;
    }

    .claim-overpaid-td input,
    .claim-overpaid-td .form-control {
        background-color: #cfe8ff !important;
    }

</style>


<div class="page-content">

    <div class="container-fluid">


        {{-- ================================================================
             PAGE HEADER
             ================================================================ --}}

        <div class="row">

            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Claims Data Entry
                    </h4>


                    {{-- SHIFT --}}

                    <div class="d-flex align-items-center mb-3">

                        <label for="claimShift"
                               class="me-2 mb-0 fw-semibold">

                            Shift:

                        </label>


                        <select id="claimShift"
                                class="form-select form-select-sm"
                                style="width:180px;">

                            <option value="A"
                                {{ $type === 'A' ? 'selected' : '' }}>

                                Morning

                            </option>


                            <option value="N"
                                {{ $type === 'N' ? 'selected' : '' }}>

                                Noon

                            </option>


                            <option value="P"
                                {{ $type === 'P' ? 'selected' : '' }}>

                                Afternoon

                            </option>

                        </select>

                    </div>


                    <div class="page-title-right">

                        <ol class="breadcrumb m-0">

                            <a href="{{ route('dashboard') }}"
                               class="btn btn-primary waves-effect waves-light">

                                {{ t('common.back','Back') }}

                            </a>

                        </ol>

                    </div>

                </div>

            </div>

        </div>


        {{-- SUCCESS --}}

        @if(session('success'))

            <div class="alert alert-success py-1 mb-2">

                {{ session('success') }}

            </div>

        @endif


        {{-- ERRORS --}}

        @if($errors->any())

            <div class="alert alert-danger py-1 mb-2">

                <ul class="mb-0 small">

                    @foreach($errors->all() as $e)

                        <li>
                            {{ $e }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif



        {{-- ================================================================
             TOP HALF
             ================================================================ --}}

        <div class="card shadow-sm mb-2"
             style="height:48vh; overflow-y:auto;">

            <div class="card-body p-2">


                <form id="claimForm"
                      method="POST"
                      action="{{ route('claims.store') }}">

                    @csrf


                    {{-- ====================================================
                         MEMBER + SUMMARY
                         ==================================================== --}}

                    <div class="row mb-3">


                        {{-- MEMBER --}}

                        <div class="col-md-5">

                            <label class="form-label mb-0 small">

                                Member (Participant)

                            </label>


                            <select name="ParticipantId"
                                    id="ParticipantId"
                                    class="form-select form-select-sm"
                                    style="width:100%;">

                                <option value="">
                                    -- Select Member --
                                </option>


                                @foreach($members as $m)

                                    <option
                                        value="{{ $m->ParticipantId }}"

                                        data-diag1="{{ $m->PrimaryDiagnosis1 ?? '' }}"

                                        data-diag2="{{ $m->SecondaryDiag1 ?? '' }}"

                                        data-source-system="{{ $m->source_system ?? '' }}"

                                        {{
                                            (string) $participantId
                                            ===
                                            (string) $m->ParticipantId
                                                ? 'selected'
                                                : ''
                                        }}>

                                        {{ $m->ParticipantId }}
                                        -
                                        {{ $m->LastName }},
                                        {{ $m->FirstName }}

                                    </option>

                                @endforeach

                            </select>

                        </div>



                        {{-- FINANCIAL SUMMARY --}}

                        <div class="col-md-7">

                            <div id="memberSummary"
                                 class="border rounded p-2 bg-light"
                                 style="display:none;">

                                <div class="row">


                                    <div class="col-md-4">

                                        <strong>
                                            Total Billed:
                                        </strong>

                                        <div id="sum_billed"
                                             class="text-primary fw-bold">

                                            0.00

                                        </div>

                                    </div>


                                    <div class="col-md-4">

                                        <strong>
                                            Total Paid:
                                        </strong>

                                        <div id="sum_paid"
                                             class="text-success fw-bold">

                                            0.00

                                        </div>

                                    </div>


                                    <div class="col-md-4">

                                        <strong>
                                            Balance:
                                        </strong>

                                        <div id="sum_balance"
                                             class="text-danger fw-bold">

                                            0.00

                                        </div>

                                    </div>


                                </div>

                            </div>

                        </div>

                    </div>



                    {{-- ====================================================
                         SERVICE LINE HEADER
                         ==================================================== --}}

                    <div class="d-flex justify-content-between align-items-center mb-1">

                        <span class="small fw-semibold">

                            Service Lines

                        </span>


                        <button type="button"
                                class="btn btn-sm btn-success"
                                id="add-line-row">

                            + Add Row

                        </button>

                    </div>



                    {{-- ====================================================
                         SERVICE LINE GRID
                         ==================================================== --}}

                    <div class="table-responsive">

                        <table class="table table-sm table-bordered align-middle mb-1"
                               id="claim-lines-table">

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

                                    <th style="width:55px;"
                                        class="text-center">

                                        Units

                                    </th>

                                    <th style="width:80px;"
                                        class="text-end">

                                        {{ t('common.amount', 'Amount') }}

                                    </th>

                                    <th style="width:80px;"
                                        class="text-end">

                                        Total

                                    </th>

                                    <th style="width:80px;">
                                        Diag1
                                    </th>

                                    <th style="width:80px;">
                                        Diag2
                                    </th>

                                    <th style="width:35px;"
                                        class="text-center">

                                        1

                                    </th>

                                    <th style="width:35px;"
                                        class="text-center">

                                        2

                                    </th>

                                    <th style="width:70px;">
                                        Prov
                                    </th>

                                    <th style="width:55px;">
                                        POS
                                    </th>

                                    <th style="width:80px;"
                                        class="text-end">

                                        Allowed

                                    </th>

                                    <th style="width:80px;">
                                        Claim #
                                    </th>

                                    <th style="width:100px;"
                                        class="text-end">

                                        Ins 1 Paid

                                    </th>

                                    <th style="width:40px;"></th>

                                </tr>

                            </thead>


                            <tbody id="claim-lines-body"
                                   class="small">


                                <tr class="claim-line-row"
                                    data-index="0">


                                    <td>

                                        <input type="date"
                                               name="lines[0][service_from]"
                                               class="form-control form-control-sm service-from">

                                    </td>


                                    <td>

                                        <input type="date"
                                               name="lines[0][service_to]"
                                               class="form-control form-control-sm service-to">

                                    </td>


                                    <td>

                                        <input type="text"
                                               name="lines[0][proc_code]"
                                               class="form-control form-control-sm proc-code"
                                               value="S5102"
                                               maxlength="6">

                                    </td>


                                    <td>

                                        <input type="text"
                                               name="lines[0][m1]"
                                               class="form-control form-control-sm m1"
                                               value="U1"
                                               maxlength="3">

                                    </td>


                                    <td>

                                        <input type="number"
                                               name="lines[0][units]"
                                               class="form-control form-control-sm units text-center"
                                               min="0"
                                               max="7">

                                    </td>


                                    <td>

                                        <input type="number"
                                               step="0.01"
                                               name="lines[0][amount_per_unit]"
                                               class="form-control form-control-sm amount-per-unit text-end"
                                               value=""
                                               readonly>

                                    </td>


                                    <td>

                                        <input type="number"
                                               step="0.01"
                                               name="lines[0][total_amount]"
                                               class="form-control form-control-sm total-amount text-end"
                                               readonly>

                                    </td>


                                    <td>

                                        <input type="text"
                                               name="lines[0][diag1]"
                                               class="form-control form-control-sm diag1-field"
                                               readonly>

                                    </td>


                                    <td>

                                        <input type="text"
                                               name="lines[0][diag2]"
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
                                               name="lines[0][provider]"
                                               class="form-control form-control-sm provider-field"
                                               value="DM"
                                               readonly>

                                    </td>


                                    <td>

                                        <input type="text"
                                               name="lines[0][pos]"
                                               class="form-control form-control-sm pos-field"
                                               value="99"
                                               readonly>

                                    </td>


                                    <td>

                                        <input type="number"
                                               step="0.01"
                                               name="lines[0][allowed]"
                                               class="form-control form-control-sm allowed-field text-end"
                                               readonly>

                                    </td>


                                    <td>

                                        <input type="text"
                                               name="lines[0][claim_no]"
                                               class="form-control form-control-sm claim-no-field"
                                               maxlength="6">

                                    </td>


                                    <td>

                                        <input type="number"
                                               step="0.01"
                                               name="lines[0][ins1_paid]"
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

                            </tbody>

                        </table>

                    </div>



                    {{-- ====================================================
                         SAVE / CLEAR
                         ==================================================== --}}

                    <div class="d-flex justify-content-end gap-2 mt-2">

                        <button type="submit"
                                class="btn btn-primary btn-sm">

                            {{ t('common.save', 'Save') }}

                        </button>


                        <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                id="reset-form">

                            Clear

                        </button>

                    </div>

                </form>

            </div>

        </div>



        {{-- ================================================================
             PAYMENTS
             ================================================================ --}}

        <div class="card shadow-sm"
             style="height:48vh;">

            <div class="card-body p-2">


                <div class="d-flex justify-content-between align-items-center mb-1">

                    <h6 class="mb-0">

                        Payments Received from Insurance

                    </h6>

                </div>


                <div class="table-responsive"
                     style="max-height:90%; overflow-y:auto;">

                    <table class="table table-sm table-hover table-striped mb-0"
                           id="payments-table">

                        <thead class="table-light">

                            <tr>

                                <th class="small">
                                    Payment Date
                                </th>

                                <th class="small">
                                    Claim #
                                </th>

                                <th class="small">
                                    Payer
                                </th>

                                <th class="small text-end">
                                    Total Amount
                                </th>

                                <th class="small text-end">
                                    Total Paid
                                </th>

                                <th class="small text-end">
                                    Amt Difference
                                </th>

                                <th class="small">
                                    Trace Number
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr>

                                <td colspan="7"
                                    class="text-center small text-muted">

                                    Select a member to see payments.

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

    function formatDateMDY(dateStr)
    {
        if (!dateStr) {
            return '';
        }

        const parts =
            dateStr.split('-');

        if (parts.length !== 3) {
            return dateStr;
        }

        return parts[1]
            + '/'
            + parts[2]
            + '/'
            + parts[0];
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

    function getSelectedMemberOption()
    {
        const select =
            document.getElementById(
                'ParticipantId'
            );

        if (
            !select
            ||
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

    function recalcRow($row)
    {
        let units =
            parseInt(
                $row.find('.units').val() || 0,
                10
            );

        let amount =
            parseFloat(
                $row.find('.amount-per-unit').val()
                || 0
            );


        if (units < 0) {
            units = 0;
        }

        if (units > 7) {
            units = 7;
        }


        $row.find('.units').val(
            isNaN(units)
                ? ''
                : units
        );


        const total =
            units * amount;


        if (
            !isNaN(total)
            &&
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

    function loadServiceRate($row)
    {
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

            url:
                "{{ route('claims.service.rate') }}",

            type:
                'GET',

            data:
            {
                service_date:
                    serviceDate
            },


            success:
                function(data)
                {
                    if (
                        data.success
                        &&
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
                            data.message
                            ||
                            'No service rate found for the selected date.'
                        );
                    }
                },


            error:
                function()
                {
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

    function updateDiagCheckboxesForRow($row)
    {
        const d1 =
            (
                $row
                    .find('.diag1-field')
                    .val()
                || ''
            ).trim();


        const d2 =
            (
                $row
                    .find('.diag2-field')
                    .val()
                || ''
            ).trim();


        const box1 =
            $row.find('.diag-box-1');

        const box2 =
            $row.find('.diag-box-2');


        if (
            d1
            &&
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
            d1
            &&
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
    )
    {
        $('#claim-lines-body .claim-line-row')
            .each(function()
            {
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
    function updatePaymentColorForRow($row)
{
    alert('Color function is running');
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

    console.log(
        'Claim:',
        claimNo,
        'Total:',
        total,
        'Paid:',
        paid
    );

    // Overpaid
    if (total > 0 && paid > total + 0.01) {

        $cells.addClass('claim-overpaid-td');

    // Underpaid / Partial
    } else if (total > 0 && paid > 0 && paid < total - 0.01) {

        $cells.addClass('claim-partial-td');

    // Exact payment
    } else if (
        total > 0 &&
        paid > 0 &&
        Math.abs(paid - total) <= 0.01
    ) {

        $cells.addClass('claim-paid-td');

    // Claim exists but nothing paid
    } else if (claimNo !== '' && paid <= 0) {

        $cells.addClass('claim-generated-td');

    // New/unpaid
    } else {

        $cells.addClass('claim-unpaid-td');
    }
}
    


    /*
    |--------------------------------------------------------------------------
    | Create Blank Service Line
    |--------------------------------------------------------------------------
    */

    function createNewRow(index)
    {
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


                <td>

                    <input type="text"
                           name="lines[${index}][claim_no]"
                           class="form-control form-control-sm claim-no-field"
                           maxlength="6">

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
    )
    {
        const $tbody =
            $('#claim-lines-body');


        $tbody.empty();

        lineIndex = 0;


        $tbody.append(
            createNewRow(
                lineIndex++
            )
        );


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
    | Existing Claim Lines
    |--------------------------------------------------------------------------
    */

    function loadExistingLines(
        participantId,
        defaultDiag1,
        defaultDiag2
    )
    {
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
            "{{ url('/claims/member-lines') }}/"
            +
            encodeURIComponent(
                participantId
            )
        )

        .done(function(rows)
        {
            $tbody.empty();

            lineIndex = 0;


            /*
            |--------------------------------------------------------------------------
            | No Existing Claims
            |--------------------------------------------------------------------------
            */

            if (
                !rows
                ||
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

            rows.forEach(function(r)
            {
                const i =
                    lineIndex++;


                const html = `

                    <tr class="claim-line-row"
                        data-index="${i}">


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


                        <td>

                            <input type="text"
                                   name="lines[${i}][claim_no]"
                                   class="form-control form-control-sm claim-no-field"
                                   value="${r.claim_no ?? ''}"
                                   maxlength="6">

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
                .each(function()
                {
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

        .fail(function()
        {
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
    )
    {
        if (!participantId) {

            $('#memberSummary')
                .hide();

            return;
        }


        $.ajax({

            url:
                "{{ url('/claims/member-summary') }}/"
                +
                encodeURIComponent(
                    participantId
                ),

            type:
                'GET',


            success:
                function(data)
                {
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
                        .slideDown();
                },


            error:
                function()
                {
                    $('#memberSummary')
                        .hide();
                }

        });
    }



    /*
    |--------------------------------------------------------------------------
    | Clear Payments
    |--------------------------------------------------------------------------
    */

    function clearPaymentsTable()
    {
        $('#payments-table tbody')
            .html(`

                <tr>

                    <td colspan="7"
                        class="text-center small text-muted">

                        Select a member to see payments.

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
    )
    {
        const $tbody =
            $('#payments-table tbody');


        if (!participantId) {

            clearPaymentsTable();

            return;
        }


        $tbody.html(`

            <tr>

                <td colspan="7"
                    class="text-center small text-muted">

                    Loading payments...

                </td>

            </tr>

        `);


        $.get(
            "{{ url('/claims/payments') }}/"
            +
            encodeURIComponent(
                participantId
            )
        )

        .done(function(rows)
        {
            $tbody.empty();


            if (
                !rows
                ||
                !rows.length
            ) {

                $tbody.html(`

                    <tr>

                        <td colspan="7"
                            class="text-center small text-muted">

                            No ERA payments found for this member.

                        </td>

                    </tr>

                `);


                return;
            }


            rows.forEach(function(p)
            {
                const totalCharge =
                    parseFloat(
                        p.total_charge
                        || 0
                    );


                const totalPaid =
                    parseFloat(
                        p.total_paid
                        || 0
                    );


                const diff =
                    (
                        totalCharge
                        -
                        totalPaid
                    ).toFixed(2);


                const paymentDate =
                    formatDateMDY(
                        p.payment_date
                    );


                const totalChargeStr =
                    isNaN(totalCharge)
                        ? ''
                        : totalCharge.toFixed(2);


                const totalPaidStr =
                    isNaN(totalPaid)
                        ? ''
                        : totalPaid.toFixed(2);


                $tbody.append(`

                    <tr>

                        <td class="small">
                            ${paymentDate}
                        </td>

                        <td class="small">
                            ${p.claim_no || ''}
                        </td>

                        <td class="small">
                            ${p.payer_name || ''}
                        </td>

                        <td class="small text-end">
                            ${totalChargeStr}
                        </td>

                        <td class="small text-end">
                            ${totalPaidStr}
                        </td>

                        <td class="small text-end">
                            ${diff}
                        </td>

                        <td class="small">
                            ${p.trace_number || ''}
                        </td>

                    </tr>

                `);

            });

        })

        .fail(function()
        {
            $tbody.html(`

                <tr>

                    <td colspan="7"
                        class="text-center small text-danger">

                        Error loading payments for this member.

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

    $(document).ready(function()
    {
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

            width:
                '180px',

            minimumResultsForSearch:
                Infinity

        });



        /*
        |--------------------------------------------------------------------------
        | Member Select2
        |--------------------------------------------------------------------------
        */

        $('#ParticipantId').select2({

            width:
                '100%',

            placeholder:
                '-- Search Member --',

            allowClear:
                true

        });



        /*
        |--------------------------------------------------------------------------
        | Shift Change
        |--------------------------------------------------------------------------
        */

        $('#claimShift').on(
            'change',
            function()
            {
                const shift =
                    $(this).val()
                    || 'A';


                window.location.href =
                    "{{ route('claims.create') }}"
                    +
                    '?type='
                    +
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
            function()
            {
                const participantId =
                    $(this).val();


                const opt =
                    getSelectedMemberOption();


                const diag1 =
                    opt
                        ? (
                            opt.getAttribute(
                                'data-diag1'
                            )
                            || ''
                        )
                        : '';


                const diag2 =
                    opt
                        ? (
                            opt.getAttribute(
                                'data-diag2'
                            )
                            || ''
                        )
                        : '';


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
                        .hide();
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
            function()
            {
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
                    serviceFrom
                    &&
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
            function()
            {
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
            function()
            {
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
            function()
            {
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
            function()
            {
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
            function()
            {
                const $tbody =
                    $('#claim-lines-body');


                $tbody.append(
                    createNewRow(
                        lineIndex++
                    )
                );


                $tbody
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
                    opt
                    &&
                    opt.value
                ) {

                    const diag1 =
                        opt.getAttribute(
                            'data-diag1'
                        )
                        || '';


                    const diag2 =
                        opt.getAttribute(
                            'data-diag2'
                        )
                        || '';


                    applyDiagsToAllRows(
                        diag1,
                        diag2
                    );
                }
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
            function()
            {
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
            function()
            {
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
                    .hide();
            }
        );



        /*
        |--------------------------------------------------------------------------
        | Initial Status
        |--------------------------------------------------------------------------
        */

        $('#claim-lines-body .claim-line-row')
            .each(function()
            {
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