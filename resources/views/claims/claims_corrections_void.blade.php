@extends('admin.admin_dashboard')

@section('title', 'Void Claim')

@section('admin')

    <div class="page-content">

        <div class="container-fluid">

            <div class="card shadow-sm">

                <div class="card-header bg-danger text-white">

                    <h5 class="mb-0">

                        Void Entire Claim

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


                    <div class="alert alert-danger">

                        <strong>
                            Warning:
                        </strong>

                        This will prepare the complete claim for payer void.

                        The original submitted rows will remain unchanged.

                        The void transaction will use the same claim number

                        <strong>{{ $claimNo }}</strong>.

                    </div>


                    <table class="table table-bordered">

                        <tr>

                            <th style="width:250px;">
                                Claim #
                            </th>

                            <td>
                                {{ $claimNo }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Member
                            </th>

                            <td>

                                {{ $member->LastName ?? '' }},
                                {{ $member->FirstName ?? '' }}

                            </td>

                        </tr>


                        <tr>

                            <th>
                                Participant ID
                            </th>

                            <td>
                                {{ $firstClaim->ParticipantId }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Payer
                            </th>

                            <td>
                                {{ $firstClaim->payer_fullname }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Total Service Lines
                            </th>

                            <td>
                                {{ $claims->count() }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Original Amount
                            </th>

                            <td>

                                ${{ number_format($totalAmount, 2) }}

                            </td>

                        </tr>

                    </table>


                    <form method="POST"
                        action="{{ route('claims.void.prepare', $claimNo) }}">

                        @csrf


                        <div class="row mb-4">

                            <div class="col-md-5">

                                <label class="form-label">
                                    Payer ICN / Claim Control Number
                                </label>


                                <input type="text" name="payer_icn" class="form-control" required
                                    value="{{ old('payer_icn', $firstClaim->payer_claim_number) }}">

                            </div>

                        </div>


                        <div class="d-flex justify-content-between">

                            <a href="{{ route('claims.corrections.index') }}"
                                class="btn btn-secondary">

                                Cancel

                            </a>


                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm(
                                    'Are you sure you want to prepare claim {{ $claimNo }} for COMPLETE VOID?'
                                );">

                                Prepare Entire Claim for Void

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

@endsection
