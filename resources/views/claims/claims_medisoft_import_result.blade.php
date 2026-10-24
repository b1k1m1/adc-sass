@extends('admin.admin_dashboard')

@section('title', 'Medisoft Claims Import Result')

@section('admin')

<div class="page-content">

    <div class="container-fluid">

        {{-- =========================================================
             PAGE TITLE
        ========================================================== --}}

        <div class="row">

            <div class="col-12">

                <div class="page-title-box
                            d-sm-flex
                            align-items-center
                            justify-content-between">

                    <h4 class="mb-sm-0 font-size-18">

                        Medisoft Claims Import Result

                    </h4>


                    <div>

                        <a
                            href="{{ route('claims.create') }}"
                            class="btn btn-primary"
                        >
                            Claims Data Entry
                        </a>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             SUMMARY
        ========================================================== --}}

        <div class="card shadow-sm mb-3">

            <div class="card-body">

                <div class="d-flex
                            flex-wrap
                            gap-2">

                    <span
                        class="badge bg-primary fs-6"
                    >

                        Selected:
                        {{ $result['total'] }}

                    </span>


                    <span
                        class="badge bg-success fs-6"
                    >

                        Imported:
                        {{ $result['imported'] }}

                    </span>


                    <span
                        class="badge bg-warning text-dark fs-6"
                    >

                        Skipped:
                        {{ $result['skipped'] }}

                    </span>


                    <span
                        class="badge bg-danger fs-6"
                    >

                        Failed:
                        {{ $result['failed'] }}

                    </span>

                </div>


                @if(
                    $result['failed'] == 0
                    &&
                    $result['skipped'] == 0
                )

                    <div
                        class="alert alert-success mt-3 mb-0"
                    >

                        Selected Medisoft claims were
                        imported successfully.

                    </div>

                @elseif(
                    $result['imported'] > 0
                )

                    <div
                        class="alert alert-warning mt-3 mb-0"
                    >

                        Import completed, but some records
                        were skipped or could not be imported.

                    </div>

                @else

                    <div
                        class="alert alert-danger mt-3 mb-0"
                    >

                        No Medisoft claims were imported.

                    </div>

                @endif

            </div>

        </div>


        {{-- =========================================================
             RESULT TABLE
        ========================================================== --}}

        <div class="card shadow-sm">

            <div class="card-header bg-light">

                <h5 class="mb-0">
                    Import Details
                </h5>

            </div>


            <div class="card-body p-2">

                <div class="table-responsive">

                    <table
                        class="table
                               table-sm
                               table-bordered
                               table-hover
                               align-middle"
                    >

                        <thead class="table-light">

                            <tr>

                                <th>
                                    CSV Row
                                </th>

                                <th>
                                    Participant
                                </th>

                                <th>
                                    Claim #
                                </th>

                                <th>
                                    From
                                </th>

                                <th>
                                    To
                                </th>

                                <th class="text-end">
                                    Total
                                </th>

                                <th class="text-end">
                                    Ins Paid
                                </th>

                                <th>
                                    Result
                                </th>

                                <th>
                                    Message
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach(
                                $result['rows']
                                as $row
                            )

                                <tr
                                    class="{{
                                        $row['result']
                                            === 'Failed'
                                                ? 'table-danger'
                                                : (
                                                    $row['result']
                                                    === 'Skipped'
                                                        ? 'table-warning'
                                                        : ''
                                                )
                                    }}"
                                >

                                    <td>

                                        {{ $row['csv_row'] }}

                                    </td>


                                    <td>

                                        <strong>

                                            {{
                                                $row[
                                                    'participant_id'
                                                ]
                                            }}

                                        </strong>

                                    </td>


                                    <td>

                                        {{
                                            $row[
                                                'claim_no'
                                            ]
                                        }}

                                    </td>


                                    <td>

                                        @if(
                                            !empty(
                                                $row[
                                                    'service_from'
                                                ]
                                            )
                                        )

                                            {{
                                                \Carbon\Carbon::parse(
                                                    $row[
                                                        'service_from'
                                                    ]
                                                )->format(
                                                    'm/d/Y'
                                                )
                                            }}

                                        @endif

                                    </td>


                                    <td>

                                        @if(
                                            !empty(
                                                $row[
                                                    'service_to'
                                                ]
                                            )
                                        )

                                            {{
                                                \Carbon\Carbon::parse(
                                                    $row[
                                                        'service_to'
                                                    ]
                                                )->format(
                                                    'm/d/Y'
                                                )
                                            }}

                                        @endif

                                    </td>


                                    <td class="text-end">

                                        ${{
                                            number_format(
                                                $row[
                                                    'total_amount'
                                                ]
                                                ?? 0,
                                                2
                                            )
                                        }}

                                    </td>


                                    <td class="text-end">

                                        ${{
                                            number_format(
                                                $row[
                                                    'ins1_paid'
                                                ]
                                                ?? 0,
                                                2
                                            )
                                        }}

                                    </td>


                                    <td>

                                        @if(
                                            $row['result']
                                            === 'Imported'
                                        )

                                            <span
                                                class="badge bg-success"
                                            >
                                                Imported
                                            </span>

                                        @elseif(
                                            $row['result']
                                            === 'Skipped'
                                        )

                                            <span
                                                class="badge bg-warning text-dark"
                                            >
                                                Skipped
                                            </span>

                                        @else

                                            <span
                                                class="badge bg-danger"
                                            >
                                                Failed
                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        {{
                                            $row[
                                                'message'
                                            ]
                                        }}

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- =====================================================
                 FOOTER
            ====================================================== --}}

            <div class="card-footer">

                <div class="d-flex
                            justify-content-between">

                    <a
                        href="{{
                            route(
                                'claims.medisoft.import.form'
                            )
                        }}"
                        class="btn btn-secondary"
                    >
                        Import Another CSV
                    </a>


                    <a
                        href="{{ route('claims.create') }}"
                        class="btn btn-primary"
                    >
                        View Claims
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection