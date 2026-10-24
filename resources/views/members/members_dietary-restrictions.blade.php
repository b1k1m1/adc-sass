@extends('admin.admin_dashboard')

@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">

                <div
                    class="page-title-box d-sm-flex
                           align-items-center justify-content-between"
                >
                    <div>
                        <h4 class="mb-1">
                            Dietary Restriction Report
                        </h4>

                        <div class="text-muted">
                            {{ $companyName }}
                            -
                            {{ $shiftName }}
                        </div>
                    </div>

                    <div>
                        <a
                            href="{{ route(
                                'members.reports.dietary-restrictions.pdf',
                                [
                                    'type'  => $type,
                                    'month' => $month,
                                    'year'  => $year
                                ]
                            ) }}"
                            target="_blank"
                            class="btn btn-danger"
                        >
                            <i class="fas fa-file-pdf me-1"></i>
                            Export to PDF
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <div class="card shadow-sm">

            <div class="card-header bg-white">

                <form
                    method="GET"
                    action="{{ route(
                        'members.reports.dietary-restrictions',
                        ['type' => $type]
                    ) }}"
                >
                    <div class="row align-items-end g-2">

                        <div class="col-md-auto">

                            <label class="form-label">
                                Shift
                            </label>

                            <div class="btn-group d-flex">

                                <a
                                    href="{{ route(
                                        'members.reports.dietary-restrictions',
                                        [
                                            'type'  => 'A',
                                            'month' => $month,
                                            'year'  => $year
                                        ]
                                    ) }}"
                                    class="btn {{ $type === 'A'
                                        ? 'btn-primary'
                                        : 'btn-outline-primary'
                                    }}"
                                >
                                    Morning
                                </a>

                                <a
                                    href="{{ route(
                                        'members.reports.dietary-restrictions',
                                        [
                                            'type'  => 'N',
                                            'month' => $month,
                                            'year'  => $year
                                        ]
                                    ) }}"
                                    class="btn {{ $type === 'N'
                                        ? 'btn-primary'
                                        : 'btn-outline-primary'
                                    }}"
                                >
                                    Noon
                                </a>

                            </div>
                        </div>

                        <div class="col-md-2">

                            <label for="month" class="form-label">
                                Month
                            </label>

                            <select
                                name="month"
                                id="month"
                                class="form-select"
                            >
                                @foreach(range(1, 12) as $monthNumber)

                                    <option
                                        value="{{ $monthNumber }}"
                                        {{ (int) $month === $monthNumber
                                            ? 'selected'
                                            : ''
                                        }}
                                    >
                                        {{ \Carbon\Carbon::create()
                                            ->month($monthNumber)
                                            ->format('F') }}
                                    </option>

                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">

                            <label for="year" class="form-label">
                                Year
                            </label>

                            <select
                                name="year"
                                id="year"
                                class="form-select"
                            >
                                @for(
                                    $yearOption = now()->year - 5;
                                    $yearOption <= now()->year + 5;
                                    $yearOption++
                                )

                                    <option
                                        value="{{ $yearOption }}"
                                        {{ (int) $year === $yearOption
                                            ? 'selected'
                                            : ''
                                        }}
                                    >
                                        {{ $yearOption }}
                                    </option>

                                @endfor
                            </select>
                        </div>

                        <div class="col-md-auto">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Display Report
                            </button>

                        </div>

                    </div>
                </form>

            </div>

            <div class="card-body">

                <div class="text-center mb-3">
                    <h4 class="mb-1">
                        {{ $companyName }}
                    </h4>

                    <h5 class="mb-1">
                        Dietary Restriction Report for the month of
                        {{ $reportMonthYear }}
                    </h5>

                    <strong>
                        {{ $shiftName }}
                    </strong>
                </div>

                <div class="table-responsive">

                    <table
                        id="dietaryRestrictionTable"
                        class="table table-bordered table-striped
                               table-hover align-middle w-100"
                    >
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Participant ID</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>MI</th>
                                <th>Date of Birth</th>
                                <th>Diet Order</th>
                                <th>Food Preference</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($members as $member)

                                @php
                                    $middleInitial = !empty(
                                        $member->MiddleName
                                    )
                                        ? strtoupper(
                                            substr(
                                                trim(
                                                    $member->MiddleName
                                                ),
                                                0,
                                                1
                                            )
                                        )
                                        : '';

                                    $dateOfBirth = '';

                                    if (!empty($member->DateOfBirth)) {
                                        try {
                                            $dateOfBirth =
                                                \Carbon\Carbon::parse(
                                                    $member->DateOfBirth
                                                )->format('m/d/Y');
                                        } catch (\Throwable $e) {
                                            $dateOfBirth = '';
                                        }
                                    }

                                    $participantId = str_pad(
                                        (string) $member->ParticipantId,
                                        3,
                                        '0',
                                        STR_PAD_LEFT
                                    );
                                @endphp

                                <tr>
                                    <td></td>

                                    <td
                                        data-order="{{
                                            $member->ParticipantId
                                        }}"
                                    >
                                        {{ $participantId }}
                                    </td>

                                    <td>
                                        {{ $member->LastName }}
                                    </td>

                                    <td>
                                        {{ $member->FirstName }}
                                    </td>

                                    <td class="text-center">
                                        {{ $middleInitial }}
                                    </td>

                                    <td
                                        data-order="{{
                                            $member->DateOfBirth
                                        }}"
                                    >
                                        {{ $dateOfBirth }}
                                    </td>

                                    <td>
                                        {{
                                            $member
                                                ->dietary_restriction_1
                                        }}
                                    </td>

                                    <td>
                                        {{
                                            $member
                                                ->dietary_restriction_3
                                        }}
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

@endsection

@push('scripts')

<script>
$(document).ready(function () {

    if (
        $.fn.DataTable.isDataTable(
            '#dietaryRestrictionTable'
        )
    ) {
        $('#dietaryRestrictionTable')
            .DataTable()
            .destroy();
    }

    $('#dietaryRestrictionTable').DataTable({

        pageLength: 100,

        lengthMenu: [
            [100, 200, 300, 400, -1],
            [100, 200, 300, 400, 'All']
        ],

        order: [
            [1, 'asc'],
            [2, 'asc'],
            [3, 'asc']
        ],

        columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                targets: [1, 2, 3, 4, 5, 6, 7],
                orderable: true,
                searchable: true
            }
        ],

        drawCallback: function () {

            const api = this.api();
            const start = api.page.info().start;

            api.column(0, {
                page: 'current'
            }).nodes().each(function (cell, index) {
                cell.innerHTML = start + index + 1;
            });
        },

        language: {
            search: '',
            searchPlaceholder: 'Search members...',
            lengthMenu: 'Show _MENU_ records',
            info: 'Showing _START_ to _END_ of _TOTAL_ members',
            zeroRecords: 'No matching members found',
            emptyTable: 'No members found for this shift'
        }
    });

});
</script>

@endpush
