@extends('admin.admin_dashboard')

@section('title', 'Resubmission 837P Preview')

@section('admin')

<style>

    .resubmission-table th,
    .resubmission-table td {
        padding: .35rem .45rem !important;
        font-size: 12.5px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .resubmission-table th {
        background-color: #f8f9fa;
    }

</style>


<div class="page-content">

    <div class="container-fluid">


        {{-- Header --}}

        <div class="row mb-3">

            <div class="col-12">

                <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">

                        Resubmission 837P Preview

                    </h4>


                    <div class="page-title-right">

                        <a href="{{ route('claims.rejections.ready') }}"
                           class="btn btn-secondary btn-sm">

                            <i class="fas fa-arrow-left me-1"></i>

                            Back to Ready Claims

                        </a>

                    </div>

                </div>

            </div>

        </div>


        {{-- Alerts --}}

        @if(session('message'))

            <div class="alert alert-{{ session('alert-type') === 'error' ? 'danger' : session('alert-type') }}">

                {{ session('message') }}

            </div>

        @endif


        {{-- Summary --}}

        <div class="row">

            <div class="col-md-3">

                <div class="card">

                    <div class="card-body">

                        <div class="text-muted">

                            Service Rows

                        </div>

                        <h4 class="mb-0">

                            {{ $totalRows }}

                        </h4>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

                <div class="card">

                    <div class="card-body">

                        <div class="text-muted">

                            Total Amount

                        </div>

                        <h4 class="mb-0">

                            ${{ number_format(
                                $totalAmount,
                                2
                            ) }}

                        </h4>

                    </div>

                </div>

            </div>

        </div>


        {{-- Form --}}

        <form method="POST"
              action="{{ route('claims.resubmission.generate837') }}">

            @csrf


            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">

                        Claims Ready for Resubmission

                    </h5>

                </div>


                <div class="card-body">


                    <div class="row mb-3">

                        <div class="col-md-6">

                            <label class="form-label">

                                837P File Name

                            </label>


                            <input type="text"
                                   name="edi_filename"
                                   class="form-control"
                                   value="{{ old(
                                       'edi_filename',
                                       $suggestedFilename
                                   ) }}"
                                   required>

                        </div>

                    </div>


                    <div class="table-responsive">

                        <table id="resubmissionTable"
                               class="table table-bordered table-striped resubmission-table">

                            <thead>

                            <tr>

                                <th style="width:40px;">

                                    <input type="checkbox"
                                           id="selectAll">

                                </th>

                                <th>Original Claim #</th>

                                <th>Participant</th>

                                <th>Member</th>

                                <th>DOS</th>

                                <th>Units</th>

                                <th class="text-end">
                                    Amount
                                </th>

                                <th>Claim Group</th>

                            </tr>

                            </thead>


                            <tbody>

                            @foreach($claims as $claim)

                                <tr>

                                    <td>

                                        <input type="checkbox"
                                               name="selected_claims[]"
                                               value="{{ $claim->id }}"
                                               class="claim-checkbox"
                                               checked>

                                    </td>


                                    <td>

                                        <strong>

                                            {{ $claim->original_claim_no ?? '' }}

                                        </strong>

                                    </td>


                                    <td>

                                        {{ $claim->ParticipantId ?? '' }}

                                    </td>


                                    <td>

                                        {{ $claim->member_lastname ?? '' }}

                                        @if(
                                            !empty($claim->member_lastname)
                                            &&
                                            !empty($claim->member_firstname)
                                        )
                                            ,
                                        @endif

                                        {{ $claim->member_firstname ?? '' }}

                                    </td>


                                    <td>

                                        @if(!empty($claim->service_from))

                                            {{ \Carbon\Carbon::parse(
                                                $claim->service_from
                                            )->format('m/d/Y') }}

                                        @endif


                                        @if(
                                            !empty($claim->service_to)
                                            &&
                                            $claim->service_from
                                            !=
                                            $claim->service_to
                                        )

                                            <br>

                                            <small class="text-muted">

                                                to

                                                {{ \Carbon\Carbon::parse(
                                                    $claim->service_to
                                                )->format('m/d/Y') }}

                                            </small>

                                        @endif

                                    </td>


                                    <td>

                                        {{ $claim->units ?? '' }}

                                    </td>


                                    <td class="text-end">

                                        ${{ number_format(
                                            (float)
                                            ($claim->total_amount ?? 0),
                                            2
                                        ) }}

                                    </td>


                                    <td>

                                        {{ $claim->claim_group ?? '' }}

                                    </td>

                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>


                    <hr>


                    <button type="submit"
                            class="btn btn-primary">

                        <i class="fas fa-file-export me-1"></i>

                        Generate Resubmission 837P

                    </button>

                </div>

            </div>

        </form>


    </div>

</div>

@endsection


@section('scripts')

<script>

$(document).ready(function () {

    $('#selectAll').on('change', function () {

        $('.claim-checkbox')
            .prop(
                'checked',
                $(this).prop('checked')
            );

    });


    if ($('#resubmissionTable').length) {

        $('#resubmissionTable').DataTable({

            pageLength: 25,

            order: [],

            responsive: false,

            autoWidth: false

        });

    }

});

</script>

@endsection