@extends('admin.admin_dashboard')

@section('title', 'Medisoft Payment Import Result')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-12">

                <div class="page-title-box">

                    <h4 class="mb-sm-0 font-size-18">
                        Medisoft Payment Import Result
                    </h4>

                </div>

            </div>

        </div>


        <div class="card shadow-sm mb-3">

            <div class="card-body">

                <div class="d-flex flex-wrap gap-3">

                    <span class="badge bg-primary fs-6">

                        Processed:
                        {{ $result['total'] ?? 0 }}

                    </span>


                    <span class="badge bg-success fs-6">

                        Imported:
                        {{ $result['imported'] ?? 0 }}

                    </span>


                    <span class="badge bg-warning text-dark fs-6">

                        Skipped:
                        {{ $result['skipped'] ?? 0 }}

                    </span>


                    <span class="badge bg-danger fs-6">

                        Failed:
                        {{ $result['failed'] ?? 0 }}

                    </span>

                </div>


                <div class="mt-3">

                    Completed:

                    <strong>
                        {{ $result['completed_at'] ?? '' }}
                    </strong>

                </div>

            </div>

        </div>


        @if(!empty($result['problems']))

            <div class="card shadow-sm">

                <div class="card-header">

                    <h5 class="mb-0">
                        Skipped / Failed Rows
                    </h5>

                </div>


                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-sm table-bordered">

                            <thead>

                                <tr>

                                    <th>CSV Row</th>

                                    <th>Item</th>

                                    <th>Participant</th>

                                    <th>Date</th>

                                    <th class="text-end">
                                        Amount
                                    </th>

                                    <th>Result</th>

                                    <th>Message</th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach(
                                    $result['problems']
                                    as $row
                                )

                                    <tr>

                                        <td>
                                            {{ $row['csv_row'] ?? '' }}
                                        </td>

                                        <td>
                                            {{ $row['item'] ?? '' }}
                                        </td>

                                        <td>
                                            {{ $row['participant_id'] ?? '' }}
                                        </td>

                                        <td>
                                            {{ $row['payment_date'] ?? '' }}
                                        </td>

                                        <td class="text-end">

                                            ${{
                                                number_format(
                                                    (float) (
                                                        $row['amount']
                                                        ?? 0
                                                    ),
                                                    2
                                                )
                                            }}

                                        </td>

                                        <td>
                                            {{ $row['result'] ?? '' }}
                                        </td>

                                        <td>
                                            {{ $row['message'] ?? '' }}
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        @endif


        <div class="mt-3">

            <a href="{{ route('era.medisoft.payments.form') }}"
               class="btn btn-primary">

                Import Another File

            </a>

        </div>

    </div>

</div>

@endsection