@extends('admin.admin_dashboard')

@section('title', 'Correct / Replace Claim')

@section('admin')

    <div class="page-content">

        <div class="container-fluid">

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">

                    <h5 class="mb-0">

                        Correct / Replace Claim
                        {{ $claimNo }}

                    </h5>

                </div>


                <div class="card-body">

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif


                    @if ($errors->any())

                        <div class="alert alert-danger">

                            <ul class="mb-0">

                                @foreach ($errors->all() as $error)
                                    <li>
                                        {{ $error }}
                                    </li>
                                @endforeach

                            </ul>

                        </div>

                    @endif


                    <div class="row mb-3">

                        <div class="col-md-3">

                            <strong>
                                Claim #:
                            </strong>

                            {{ $claimNo }}

                        </div>


                        <div class="col-md-3">

                            <strong>
                                Member:
                            </strong>

                            {{ $member->LastName ?? '' }},
                            {{ $member->FirstName ?? '' }}

                        </div>


                        <div class="col-md-3">

                            <strong>
                                Participant ID:
                            </strong>

                            {{ $firstClaim->ParticipantId }}

                        </div>


                        <div class="col-md-3">

                            <strong>
                                Payer:
                            </strong>

                            {{ $firstClaim->payer_fullname }}

                        </div>

                    </div>


                    <div class="alert alert-warning">

                        The original submitted claim will remain unchanged.

                        The corrected copy will retain the same claim number

                        <strong>{{ $claimNo }}</strong>.

                    </div>


                    <form method="POST" action="{{ route('claims.corrections.prepare', $claimNo) }}" id="correctForm">

                        @csrf


                        <div class="row mb-4">

                            <div class="col-md-4">

                                <label class="form-label">
                                    Payer ICN / Claim Control Number
                                </label>


                                <input type="text" name="payer_icn" class="form-control" required
                                    value="{{ old('payer_icn', $firstClaim->payer_claim_number) }}">

                            </div>

                        </div>


                        <div class="table-responsive">

                            <table class="table table-bordered" id="serviceTable">

                                <thead class="table-dark">

                                    <tr>

                                        <th>
                                            From
                                        </th>

                                        <th>
                                            To
                                        </th>

                                        <th>
                                            Units
                                        </th>

                                        <th>
                                            Amount / Unit
                                        </th>

                                        <th>
                                            Diagnosis 1
                                        </th>

                                        <th>
                                            Diagnosis 2
                                        </th>

                                        <th style="width:80px;">
                                            Action
                                        </th>

                                    </tr>

                                </thead>


                                <tbody id="serviceRows">

                                    @foreach ($claims as $index => $claim)
                                        <tr>

                                            <input type="hidden" name="service_lines[{{ $index }}][source_id]"
                                                value="{{ $claim->id }}">


                                            <td>

                                                <input type="date" class="form-control"
                                                    name="service_lines[{{ $index }}][service_from]"
                                                    value="{{ $claim->service_from }}" required>

                                            </td>


                                            <td>

                                                <input type="date" class="form-control"
                                                    name="service_lines[{{ $index }}][service_to]"
                                                    value="{{ $claim->service_to }}" required>

                                            </td>


                                            <td>

                                                <input type="number" step="1" min="1" class="form-control"
                                                    name="service_lines[{{ $index }}][units]"
                                                    value="{{ $claim->units }}" required>

                                            </td>


                                            <td>

                                                <input type="number" step="0.01" min="0" class="form-control"
                                                    name="service_lines[{{ $index }}][amount_per_unit]"
                                                    value="{{ $claim->amount_per_unit }}" required>

                                            </td>


                                            <td>

                                                <input type="text" class="form-control"
                                                    name="service_lines[{{ $index }}][diag1]"
                                                    value="{{ $claim->diag1 }}">

                                            </td>


                                            <td>

                                                <input type="text" class="form-control"
                                                    name="service_lines[{{ $index }}][diag2]"
                                                    value="{{ $claim->diag2 }}">

                                            </td>


                                            <td class="text-center">

                                                <button type="button" class="btn btn-sm btn-danger removeRow">

                                                    Remove

                                                </button>

                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </div>


                        <div class="mb-4">

                            <button type="button" class="btn btn-outline-primary" id="addLine">

                                + Add Service Line

                            </button>

                        </div>


                        <div class="d-flex justify-content-between">

                            <a href="{{ route('claims.corrections.index') }}" class="btn btn-secondary">

                                Cancel

                            </a>


                            <button type="submit" class="btn btn-primary">

                                Prepare Corrected Claim

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

@endsection


@section('scripts')

    <script>
        $(document).ready(function() {

            let rowIndex =
                {{ $claims->count() }};


            const sourceId =
                '{{ $firstClaim->id }}';


            const amountPerUnit =
                '{{ $firstClaim->amount_per_unit }}';


            const diag1 =
                @json($firstClaim->diag1);


            const diag2 =
                @json($firstClaim->diag2);


            $('#addLine').on('click', function() {

                const row = `

            <tr>

                <input type="hidden"
                       name="service_lines[${rowIndex}][source_id]"
                       value="${sourceId}">


                <td>

                    <input type="date"
                           class="form-control"
                           name="service_lines[${rowIndex}][service_from]"
                           required>

                </td>


                <td>

                    <input type="date"
                           class="form-control"
                           name="service_lines[${rowIndex}][service_to]"
                           required>

                </td>


                <td>

                    <input type="number"
                           min="1"
                           step="1"
                           class="form-control"
                           name="service_lines[${rowIndex}][units]"
                           value="1"
                           required>

                </td>


                <td>

                    <input type="number"
                           min="0"
                           step="0.01"
                           class="form-control"
                           name="service_lines[${rowIndex}][amount_per_unit]"
                           value="${amountPerUnit}"
                           required>

                </td>


                <td>

                    <input type="text"
                           class="form-control"
                           name="service_lines[${rowIndex}][diag1]"
                           value="${diag1 ?? ''}">

                </td>


                <td>

                    <input type="text"
                           class="form-control"
                           name="service_lines[${rowIndex}][diag2]"
                           value="${diag2 ?? ''}">

                </td>


                <td class="text-center">

                    <button type="button"
                            class="btn btn-sm btn-danger removeRow">

                        Remove

                    </button>

                </td>

            </tr>
        `;


                $('#serviceRows').append(row);

                rowIndex++;

            });


            $(document).on(
                'click',
                '.removeRow',
                function() {

                    if ($('#serviceRows tr').length <= 1) {

                        alert(
                            'A corrected claim must contain at least one service line.'
                        );

                        return;
                    }

                    $(this)
                        .closest('tr')
                        .remove();

                }
            );


            $('#correctForm').on(
                'submit',
                function() {

                    return confirm(
                        'Prepare corrected claim {{ $claimNo }} using the same claim number?'
                    );

                }
            );

        });
    </script>

@endsection
