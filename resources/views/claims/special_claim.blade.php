@extends('admin.admin_dashboard')

@section('title', 'Special / Resubmit Claim')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    Special / Resubmit Claim
                </h5>
            </div>

            <div class="card-body">

                <div class="alert alert-warning py-2">
                    Use this screen only for special claim handling such as
                    resubmission, demographic correction, or expired PA exception.
                </div>

                <div class="row g-2 align-items-end mb-3">

                    <div class="col-md-3">
                        <label class="form-label mb-1">Claim Number</label>
                        <input type="text"
                            id="specialClaimNo"
                            class="form-control form-control-sm"
                            maxlength="6"
                            placeholder="e.g. 015111">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label mb-1">Participant ID</label>
                        <input type="text"
                            id="specialParticipantId"
                            class="form-control form-control-sm"
                            placeholder="Participant ID">
                    </div>

                    <div class="col-md-2">
                        <button type="button"
                                id="searchSpecialClaim"
                                class="btn btn-primary btn-sm w-100">
                            Search
                        </button>
                    </div>

                    <div class="col-md-2">
                        <button type="button"
                                id="clearSpecialSearch"
                                class="btn btn-secondary btn-sm w-100">
                            Clear
                        </button>
                    </div>

                </div>

                <div id="specialSearchMessage"></div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Claim #</th>
                                <th>Participant</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Procedure</th>
                                <th class="text-end">Units</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th style="width:90px;">Action</th>
                            </tr>
                        </thead>

                        <tbody id="specialClaimResults">
                            <tr>
                                <td colspan="9"
                                    class="text-center text-muted">
                                    Search for an existing claim.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <a href="{{ route('claims.create') }}"
                class="btn btn-secondary btn-sm">
                    Back to Claims
                </a>

            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')

<script>

$(document).ready(function () {

    function formatDate(dateStr)
    {
        if (!dateStr) {
            return '';
        }

        const parts = dateStr.split('-');

        if (parts.length !== 3) {
            return dateStr;
        }

        return parts[1] + '/' + parts[2] + '/' + parts[0];
    }


    $('#searchSpecialClaim').on('click', function () {

        const claimNo =
            $('#specialClaimNo').val().trim();

        const participantId =
            $('#specialParticipantId').val().trim();

        $('#specialSearchMessage').html('');

        $('#specialClaimResults').html(`
            <tr>
                <td colspan="10"
                    class="text-center text-muted">
                    Searching...
                </td>
            </tr>
        `);

        $.ajax({

            url: "{{ route('claims.special.search') }}",

            type: "GET",

            data: {
                claim_no: claimNo,
                participant_id: participantId
            },

            success: function (response) {

                if (!response.success) {

                    $('#specialClaimResults').html(`
                        <tr>
                            <td colspan="10"
                                class="text-center text-danger">
                                ${response.message}
                            </td>
                        </tr>
                    `);

                    return;
                }

                if (!response.claims.length) {

                    $('#specialClaimResults').html(`
                        <tr>
                            <td colspan="10"
                                class="text-center text-muted">
                                No claims found.
                            </td>
                        </tr>
                    `);

                    return;
                }

                let html = '';

                response.claims.forEach(function (claim) {

                    const amount =
                        parseFloat(claim.total_amount || 0)
                            .toFixed(2);

                    html += `
                        <tr>

                            <td>${claim.claim_no ?? ''}</td>

                            <td>${claim.ParticipantId ?? ''}</td>

                            <td>${formatDate(claim.service_from)}</td>

                            <td>${formatDate(claim.service_to)}</td>

                            <td>${claim.proc_code ?? ''}</td>

                            <td class="text-end">
                                ${claim.units ?? ''}
                            </td>

                            <td class="text-end">
                                ${amount}
                            </td>

                            <td>
                                ${claim.status ?? ''}
                            </td>

                            <td>
                                <a href="/claims/special/load/${claim.id}"
                                   class="btn btn-sm btn-warning">
                                    Select
                                </a>
                            </td>

                        </tr>
                    `;
                });

                $('#specialClaimResults').html(html);
            },

            error: function () {

                $('#specialClaimResults').html(`
                    <tr>
                        <td colspan="10"
                            class="text-center text-danger">
                            Error searching claims.
                        </td>
                    </tr>
                `);
            }
        });
    });


    $('#clearSpecialSearch').on('click', function () {

        $('#specialClaimNo').val('');
        $('#specialParticipantId').val('');

        $('#specialSearchMessage').html('');

        $('#specialClaimResults').html(`
            <tr>
                <td colspan="10"
                    class="text-center text-muted">
                    Search for an existing claim.
                </td>
            </tr>
        `);
    });

});

</script>

@endsection
