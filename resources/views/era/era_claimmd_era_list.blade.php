@extends('admin.admin_dashboard')

@section('title', 'Claim.MD ERA')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        {{-- ============================================================
             Page Header
        ============================================================ --}}

        <div class="row">

            <div class="col-12">

                <div class="page-title-box d-sm-flex
                            align-items-center
                            justify-content-between">

                    <h4 class="mb-sm-0">
                        Claim.MD ERA / 835
                    </h4>

                    <div class="page-title-right">

                        <a href="{{ route('era.index') }}"
                           class="btn btn-secondary">

                            Back to ERA

                        </a>

                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
             Messages
        ============================================================ --}}

        @if(session('success'))

            <div class="alert alert-success alert-dismissible fade show">

                {{ session('success') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>

            </div>

        @endif


        @if(session('error'))

            <div class="alert alert-danger alert-dismissible fade show">

                {{ session('error') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>

            </div>

        @endif


        @if(!empty($errorMessage))

            <div class="alert alert-danger">

                <strong>Claim.MD Error:</strong>

                {{ $errorMessage }}

            </div>

        @endif


        {{-- ============================================================
             Summary
        ============================================================ --}}

        <div class="row mb-3">

            <div class="col-md-4">

                <div class="card">

                    <div class="card-body">

                        <h6 class="text-muted">
                            ERA Available
                        </h6>

                        <h3 class="mb-0">

                            {{ $eras->count() }}

                        </h3>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card">

                    <div class="card-body">

                        <h6 class="text-muted">
                            Total Payment Amount
                        </h6>

                        <h3 class="mb-0">

                            ${{ number_format(
                                $eras->sum(function($era) {
                                    return
                                        (float) $era['paid_amount'];
                                }),
                                2
                            ) }}

                        </h3>

                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
             ERA Table
        ============================================================ --}}

        <div class="row">

            <div class="col-12">

                <div class="card">

                    <div class="card-body">

                        <div class="table-responsive">

                            <table
                                id="claimMdEraTable"
                                class="table table-bordered
                                       table-striped
                                       table-hover
                                       align-middle
                                       nowrap"
                                style="width:100%;">

                                <thead>

                                    <tr>

                                        <th>ERA ID</th>

                                        <th>Received</th>

                                        <th>Payment Date</th>

                                        <th>Payer</th>

                                        <th>Payer ID</th>

                                        <th>Amount</th>

                                        <th>Method</th>

                                        <th>Check / EFT</th>

                                        <th>Provider</th>

                                        <th>Status</th>

                                        <th style="width:150px;">
                                            Action
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                @foreach($eras as $era)

                                    <tr>

                                        <td>
                                            {{ $era['eraid'] }}
                                        </td>


                                        <td>

                                            @if(!empty(
                                                $era['received_time']
                                            ))

                                                {{
                                                    \Carbon\Carbon::createFromFormat(
                                                        'm-d-Y H:i',
                                                        $era['received_time']
                                                    )->format(
                                                        'm/d/Y h:i A'
                                                    )
                                                }}

                                            @endif

                                        </td>


                                        <td>

                                            @if(!empty(
                                                $era['paid_date']
                                            ))

                                                {{
                                                    \Carbon\Carbon::parse(
                                                        $era['paid_date']
                                                    )->format('m/d/Y')
                                                }}

                                            @endif

                                        </td>


                                        <td>

                                            {{ $era['payer_name'] }}

                                        </td>


                                        <td>

                                            {{ $era['payerid'] }}

                                        </td>


                                        <td class="text-end">

                                            ${{
                                                number_format(
                                                    (float)
                                                    $era['paid_amount'],
                                                    2
                                                )
                                            }}

                                        </td>


                                        <td class="text-center">

                                            @if(
                                                strtolower(
                                                    $era['check_type']
                                                ) === 'eft'
                                            )

                                                <span
                                                    class="badge
                                                           bg-success">

                                                    EFT

                                                </span>

                                            @elseif(
                                                strtolower(
                                                    $era['check_type']
                                                ) === 'check'
                                            )

                                                <span
                                                    class="badge
                                                           bg-primary">

                                                    Check

                                                </span>

                                            @else

                                                <span
                                                    class="badge
                                                           bg-secondary">

                                                    {{
                                                        strtoupper(
                                                            $era[
                                                                'check_type'
                                                            ]
                                                        )
                                                    }}

                                                </span>

                                            @endif

                                        </td>


                                        <td>

                                            {{ $era['check_number'] }}

                                        </td>


                                        <td>

                                            {{ $era['provider_name'] }}

                                        </td>


                                        <td class="text-center">

                                            @if(
                                                empty(
                                                    $era[
                                                        'download_time'
                                                    ]
                                                )
                                            )

                                                <span
                                                    class="badge
                                                           bg-warning
                                                           text-dark">

                                                    Not Downloaded

                                                </span>

                                            @else

                                                <span
                                                    class="badge
                                                           bg-success">

                                                    Downloaded

                                                </span>

                                            @endif

                                        </td>


                                        <td class="text-nowrap">

                                            {{-- Preview --}}

                                            <button
                                                type="button"
                                                class="btn
                                                       btn-sm
                                                       btn-info
                                                       btn-preview-era"

                                                data-bs-toggle="modal"

                                                data-bs-target=
                                                    "#eraPreviewModal"

                                                data-eraid=
                                                    "{{ $era['eraid'] }}"

                                                data-payer=
                                                    "{{ $era['payer_name'] }}"

                                                data-payerid=
                                                    "{{ $era['payerid'] }}"

                                                data-received=
                                                    "{{ $era['received_time'] }}"

                                                data-paiddate=
                                                    "{{ $era['paid_date'] }}"

                                                data-amount=
                                                    "{{ $era['paid_amount'] }}"

                                                data-method=
                                                    "{{ $era['check_type'] }}"

                                                data-check=
                                                    "{{ $era['check_number'] }}"

                                                data-provider=
                                                    "{{ $era['provider_name'] }}"

                                                data-claimmd-provider=
                                                    "{{ $era['claimmd_provider'] }}"

                                                data-npi=
                                                    "{{ $era['provider_npi'] }}">

                                                Preview

                                            </button>


                                            {{-- Process --}}

                                            <form
                                                method="POST"

                                                action="{{
                                                    route(
                                                        'era.claimmd.process',
                                                        $era['eraid']
                                                    )
                                                }}"

                                                class="d-inline
                                                       process-era-form">

                                                @csrf

                                                <button
                                                    type="submit"

                                                    class="btn
                                                           btn-sm
                                                           btn-primary">

                                                    Download

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

    </div>

</div>


{{-- ================================================================
     Preview Modal
================================================================ --}}

<div class="modal fade"
     id="eraPreviewModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Claim.MD ERA Preview
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>


            <div class="modal-body">

                <table class="table table-bordered">

                    <tr>
                        <th width="30%">ERA ID</th>
                        <td id="previewEraId"></td>
                    </tr>

                    <tr>
                        <th>Payer</th>
                        <td id="previewPayer"></td>
                    </tr>

                    <tr>
                        <th>Payer ID</th>
                        <td id="previewPayerId"></td>
                    </tr>

                    <tr>
                        <th>Received</th>
                        <td id="previewReceived"></td>
                    </tr>

                    <tr>
                        <th>Payment Date</th>
                        <td id="previewPaidDate"></td>
                    </tr>

                    <tr>
                        <th>Paid Amount</th>
                        <td id="previewAmount"></td>
                    </tr>

                    <tr>
                        <th>Payment Method</th>
                        <td id="previewMethod"></td>
                    </tr>

                    <tr>
                        <th>Check / EFT #</th>
                        <td id="previewCheck"></td>
                    </tr>

                    <tr>
                        <th>Provider</th>
                        <td id="previewProvider"></td>
                    </tr>

                    <tr>
                        <th>Claim.MD Provider</th>
                        <td id="previewClaimMdProvider"></td>
                    </tr>

                    <tr>
                        <th>Provider NPI</th>
                        <td id="previewNpi"></td>
                    </tr>

                </table>

            </div>


            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                    Close

                </button>

            </div>

        </div>

    </div>

</div>


@endsection


@push('scripts')

<script>

$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | DataTable
    |--------------------------------------------------------------------------
    */

    $('#claimMdEraTable').DataTable({

        pageLength: 25,

        lengthMenu: [
            [25, 50, 100, -1],
            [25, 50, 100, 'All']
        ],

        order: [
            [1, 'desc']
        ],

        scrollX: true,

        columnDefs: [
            {
                targets: [10],
                orderable: false,
                searchable: false
            }
        ]
    });


    /*
    |--------------------------------------------------------------------------
    | Preview Modal
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '.btn-preview-era',
        function () {

            let button = $(this);

            $('#previewEraId')
                .text(button.data('eraid'));

            $('#previewPayer')
                .text(button.data('payer'));

            $('#previewPayerId')
                .text(button.data('payerid'));

            $('#previewReceived')
                .text(button.data('received'));

            $('#previewPaidDate')
                .text(button.data('paiddate'));

            $('#previewAmount')
                .text(
                    '$'
                    + parseFloat(
                        button.data('amount') || 0
                    ).toFixed(2)
                );

            $('#previewMethod')
                .text(
                    (
                        button.data('method') || ''
                    ).toUpperCase()
                );

            $('#previewCheck')
                .text(button.data('check'));

            $('#previewProvider')
                .text(button.data('provider'));

            $('#previewClaimMdProvider')
                .text(
                    button.data(
                        'claimmd-provider'
                    )
                );

            $('#previewNpi')
                .text(button.data('npi'));

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Process Confirmation
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'submit',
        '.process-era-form',
        function (e) {

            let confirmed = confirm(
                'Download this 835 ERA from Claim.MD '
                 + 'and add it to the ERA processing screen?'
            );

            if (!confirmed) {
                e.preventDefault();
            }

        }
    );

});

</script>

@endpush