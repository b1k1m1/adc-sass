@extends('admin.admin_dashboard')

@section('title', 'Preview Medisoft Payments')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">
                        Preview Medisoft Payment Import
                    </h4>


                    <a href="{{ route('era.medisoft.payments.form') }}"
                       class="btn btn-secondary">

                        Back

                    </a>

                </div>

            </div>

        </div>


        {{-- SUMMARY --}}

        <div class="card shadow-sm mb-3">

            <div class="card-body">

                <div class="d-flex flex-wrap gap-2">

                    <span class="badge bg-primary fs-6">

                        Total:
                        {{ count($previewRows) }}

                    </span>


                    <span class="badge bg-success fs-6">

                        Ready:
                        {{ $readyCount }}

                    </span>


                    <span class="badge bg-danger fs-6">

                        Invalid:
                        {{ $invalidCount }}

                    </span>


                    <span class="badge bg-warning text-dark fs-6">

                        Warning:
                        {{ $warningCount }}

                    </span>


                    <span class="badge bg-secondary fs-6">

                        Visible:
                        <span id="visibleRowCount">
                            {{ count($previewRows) }}
                        </span>

                    </span>

                </div>

            </div>

        </div>


        {{-- FILTERS --}}

        <div class="card shadow-sm mb-3">

            <div class="card-body">

                <div class="d-flex flex-wrap gap-2">

                    <button type="button"
                            id="showAllRows"
                            class="btn btn-outline-primary">

                        Show All

                    </button>


                    <button type="button"
                            id="showInvalidOnly"
                            class="btn btn-outline-danger">

                        Show Invalid Only

                        @if($invalidCount > 0)

                            ({{ $invalidCount }})

                        @endif

                    </button>


                    <button type="button"
                            id="showWarningsOnly"
                            class="btn btn-outline-warning">

                        Show Warnings Only

                    </button>


                    @if($invalidCount > 0)

                        <a href="{{ route(
                                    'era.medisoft.payments.invalid',
                                    ['token' => $token]
                                ) }}"
                           class="btn btn-danger">

                            Download Invalid Rows CSV

                        </a>

                    @endif

                </div>

            </div>

        </div>


        {{-- IMPORT ALL VALID FORM --}}

        <form id="importAllValidForm"
              method="POST"
              action="{{ route('era.medisoft.payments.process') }}">

            @csrf

            <input type="hidden"
                   name="token"
                   value="{{ $token }}">

            <input type="hidden"
                   name="import_all_valid"
                   value="1">

        </form>


        {{-- SELECTED FORM --}}

        <form id="importSelectedForm"
              method="POST"
              action="{{ route('era.medisoft.payments.process') }}">

            @csrf

            <input type="hidden"
                   name="token"
                   value="{{ $token }}">


            <div class="card shadow-sm">

                <div class="card-header">

                    <div class="d-flex justify-content-between align-items-center">

                        <h5 class="mb-0">
                            Medisoft Payment Transactions
                        </h5>


                        <div class="d-flex gap-2">

                            <button type="button"
                                    id="selectAllValid"
                                    class="btn btn-sm btn-success">

                                Select All Valid

                            </button>


                            <button type="button"
                                    id="clearAll"
                                    class="btn btn-sm btn-secondary">

                                Clear Selection

                            </button>

                        </div>

                    </div>

                </div>


                <div class="card-body p-2">

                    <div class="table-responsive"
                         style="max-height:650px; overflow:auto;">


                        <table class="table table-sm table-bordered table-hover align-middle">


                            <thead class="table-light"
                                   style="position:sticky;top:0;z-index:5;">

                                <tr>

                                    <th>Select</th>

                                    <th>CSV Row</th>

                                    <th>Item</th>

                                    <th>Participant</th>

                                    <th>Member</th>

                                    <th>Date</th>

                                    <th>Type</th>

                                    <th>Description</th>

                                    <th>Provider</th>

                                    <th class="text-end">
                                        Original
                                    </th>

                                    <th class="text-end">
                                        Payment
                                    </th>

                                    <th>Check #</th>

                                    <th class="text-end">
                                        Unapplied
                                    </th>

                                    <th>Result</th>

                                    <th>Message</th>

                                </tr>

                            </thead>


                            <tbody>


                                @forelse($previewRows as $row)


                                    @php

                                        $class = '';

                                        if (
                                            empty(
                                                $row['valid']
                                            )
                                        ) {

                                            $class =
                                                'table-danger';

                                        } elseif (
                                            !empty(
                                                $row['warning']
                                            )
                                        ) {

                                            $class =
                                                'table-warning';
                                        }

                                    @endphp


                                    <tr class="payment-preview-row {{ $class }}"
                                        data-valid="{{ !empty($row['valid']) ? '1' : '0' }}"
                                        data-warning="{{ !empty($row['warning']) ? '1' : '0' }}">


                                        <td>

                                            @if(!empty($row['valid']))

                                                <input type="checkbox"
                                                       name="selected_rows[]"
                                                       value="{{ $row['key'] }}"
                                                       class="form-check-input valid-row-checkbox"
                                                       checked>

                                            @else

                                                <input type="checkbox"
                                                       class="form-check-input"
                                                       disabled>

                                            @endif

                                        </td>


                                        <td>
                                            {{ $row['csv_row'] ?? '' }}
                                        </td>


                                        <td>
                                            {{ $row['medisoft_item_no'] ?? '' }}
                                        </td>


                                        <td>
                                            <strong>
                                                {{ $row['ParticipantId'] ?? '' }}
                                            </strong>
                                        </td>


                                        <td>
                                            {{ $row['member_name'] ?? '' }}
                                        </td>


                                        <td>

                                            @if(!empty($row['payment_date']))

                                                {{
                                                    \Carbon\Carbon::parse(
                                                        $row['payment_date']
                                                    )->format('m/d/Y')
                                                }}

                                            @endif

                                        </td>


                                        <td>

                                            @if(($row['transaction_type'] ?? '') === 'IP')

                                                <span class="badge bg-success">
                                                    IP
                                                </span>

                                            @elseif(($row['transaction_type'] ?? '') === 'IT')

                                                <span class="badge bg-info text-dark">
                                                    IT
                                                </span>

                                            @else

                                                {{ $row['transaction_type'] ?? '' }}

                                            @endif

                                        </td>


                                        <td style="min-width:260px;">

                                            {{ $row['description'] ?? '' }}

                                        </td>


                                        <td>
                                            {{ $row['attending_provider'] ?? '' }}
                                        </td>


                                        <td class="text-end">

                                            ${{
                                                number_format(
                                                    (float) (
                                                        $row['original_amount']
                                                        ?? 0
                                                    ),
                                                    2
                                                )
                                            }}

                                        </td>


                                        <td class="text-end">

                                            <strong>

                                                ${{
                                                    number_format(
                                                        (float) (
                                                            $row['payment_amount']
                                                            ?? 0
                                                        ),
                                                        2
                                                    )
                                                }}

                                            </strong>

                                        </td>


                                        <td>
                                            {{ $row['check_number'] ?? '' }}
                                        </td>


                                        <td class="text-end">

                                            ${{
                                                number_format(
                                                    (float) (
                                                        $row['unapplied_amount']
                                                        ?? 0
                                                    ),
                                                    2
                                                )
                                            }}

                                        </td>


                                        <td>

                                            @if(empty($row['valid']))

                                                <span class="badge bg-danger">
                                                    Invalid
                                                </span>

                                            @elseif(!empty($row['warning']))

                                                <span class="badge bg-warning text-dark">
                                                    Warning
                                                </span>

                                            @else

                                                <span class="badge bg-success">
                                                    Ready
                                                </span>

                                            @endif

                                        </td>


                                        <td style="min-width:280px;">

                                            {{ $row['message'] ?? '' }}

                                        </td>

                                    </tr>


                                @empty


                                    <tr>

                                        <td colspan="15"
                                            class="text-center text-muted">

                                            No Medisoft payment rows found.

                                        </td>

                                    </tr>


                                @endforelse


                            </tbody>

                        </table>

                    </div>

                </div>


                <div class="card-footer">

                    <div class="d-flex justify-content-between">

                        <a href="{{ route('era.medisoft.payments.form') }}"
                           class="btn btn-secondary">

                            Back

                        </a>


                        <div class="d-flex gap-2">

                            <button type="submit"
                                    class="btn btn-primary"
                                    @if($readyCount == 0)
                                        disabled
                                    @endif>

                                Import Selected Payments

                            </button>


                            <button type="submit"
                                    form="importAllValidForm"
                                    class="btn btn-success"
                                    onclick="return confirm(
                                        'Import all {{ $readyCount }} valid payment rows?'
                                    );"
                                    @if($readyCount == 0)
                                        disabled
                                    @endif>

                                Import All Valid Rows

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </form>

    </div>

</div>

@endsection


@section('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const rows =
            Array.from(
                document.querySelectorAll(
                    '.payment-preview-row'
                )
            );


        const visible =
            document.getElementById(
                'visibleRowCount'
            );


        function updateCount() {

            let count = 0;


            rows.forEach(
                function (row) {

                    if (
                        row.style.display
                        !==
                        'none'
                    ) {

                        count++;
                    }
                }
            );


            if (visible) {

                visible.textContent =
                    count;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Show All
        |--------------------------------------------------------------------------
        */

        document
            .getElementById(
                'showAllRows'
            )
            ?.addEventListener(
                'click',
                function () {

                    rows.forEach(
                        row =>
                            row.style.display = ''
                    );


                    updateCount();
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Invalid Only
        |--------------------------------------------------------------------------
        */

        document
            .getElementById(
                'showInvalidOnly'
            )
            ?.addEventListener(
                'click',
                function () {

                    rows.forEach(
                        function (row) {

                            row.style.display =
                                row.dataset.valid
                                === '0'
                                    ? ''
                                    : 'none';
                        }
                    );


                    updateCount();
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Warning Only
        |--------------------------------------------------------------------------
        */

        document
            .getElementById(
                'showWarningsOnly'
            )
            ?.addEventListener(
                'click',
                function () {

                    rows.forEach(
                        function (row) {

                            row.style.display =
                                row.dataset.warning
                                === '1'
                                    ? ''
                                    : 'none';
                        }
                    );


                    updateCount();
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Select All
        |--------------------------------------------------------------------------
        */

        document
            .getElementById(
                'selectAllValid'
            )
            ?.addEventListener(
                'click',
                function () {

                    document
                        .querySelectorAll(
                            '.valid-row-checkbox'
                        )
                        .forEach(
                            checkbox =>
                                checkbox.checked = true
                        );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Clear
        |--------------------------------------------------------------------------
        */

        document
            .getElementById(
                'clearAll'
            )
            ?.addEventListener(
                'click',
                function () {

                    document
                        .querySelectorAll(
                            '.valid-row-checkbox'
                        )
                        .forEach(
                            checkbox =>
                                checkbox.checked = false
                        );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Selected Import Confirmation
        |--------------------------------------------------------------------------
        */

        document
            .getElementById(
                'importSelectedForm'
            )
            ?.addEventListener(
                'submit',
                function (event) {

                    const selected =
                        document.querySelectorAll(
                            '.valid-row-checkbox:checked'
                        ).length;


                    if (selected === 0) {

                        event.preventDefault();

                        alert(
                            'Please select at least one valid payment row.'
                        );

                        return;
                    }


                    if (
                        !confirm(
                            'Import '
                            +
                            selected
                            +
                            ' selected payment row(s)?'
                        )
                    ) {

                        event.preventDefault();
                    }
                }
            );


        updateCount();

    }
);

</script>

@endsection