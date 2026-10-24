@extends('admin.admin_dashboard')
@section('admin')

<style>
.table-responsive {
    overflow-x: auto;
    white-space: nowrap;
}

.compact-table tr {
    height: 24px !important;
}

.compact-table th,
.compact-table td {
    padding: 2px 4px !important;
    font-size: 12px !important;
    border: 1px solid #e2e8f0;
    vertical-align: middle;
}

.compact-table th {
    background-color: #f8f9fa;
}

#attendance_report_table th {
    cursor: pointer;
}
</style>

<div class="page-content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">
                @php
                    if ($type == 'A') {
                        $type1 = 'Morning Shift';
                    } elseif ($type == 'N') {
                        $type1 = 'Noon Shift';
                    } else {
                        $type1 = '';
                    }
                @endphp

                {{ $company->comp_nm ?? '' }} - {{ $type1 }} <br>
                Attendance Report from {{ $startDate->format('m/d/Y') }} to {{ $endDate->format('m/d/Y') }}
            </h3>

            <div>
               <form action="{{ route('attendance.export.pdf.checked', $type ?? 'A') }}" method="GET" class="d-inline">
                    <input type="hidden" name="start_date" value="{{ $startDate->format('m/d/Y') }}">
                    <input type="hidden" name="end_date" value="{{ $endDate->format('m/d/Y') }}">

                    <input type="hidden" name="sort_column" id="pdf_sort_column" value="ParticipantId">
                    <input type="hidden" name="sort_dir" id="pdf_sort_dir" value="asc">

                    <button type="submit" id="pdfExportBtn" class="btn btn-danger">
                        Export to PDF
                    </button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table id="attendance_report_table" class="table compact-table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID#</th>
                        <th>Last Name</th>
                        <th>First Name</th>

                        @foreach($dateRange as $date)
                            <th style="text-align:center;">
                                {{ \Carbon\Carbon::parse($date)->format('d') }}
                            </th>
                        @endforeach

                        <th style="text-align:center;">Grand Total</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($attendanceData as $participantId => $participant)
                        <tr>
                            <td>{{ $participant['ParticipantId'] }}</td>
                            <td>{{ $participant['LastName'] }}</td>
                            <td>{{ $participant['FirstName'] }}</td>

                            @foreach($dateRange as $date)
                                <td class="text-center">
                                    {{ ($participant['attendance'][$date] ?? 0) == 1 ? '✔️' : '' }}
                                </td>
                            @endforeach

                            <td class="text-center">
                                {{ $participant['row_total'] ?? 0 }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-muted">No attendance found</td>
                            <td></td>
                            <td></td>

                            @foreach($dateRange as $date)
                                <td class="text-center"></td>
                            @endforeach

                            <td class="text-center">0</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold text-center table-secondary">
                        <td colspan="3">Grand Totals</td>

                        @foreach ($dateRange as $date)
                            <td class="text-center">{{ $columnTotals[$date] ?? 0 }}</td>
                        @endforeach

                        <td class="text-center">
                            {{ array_sum($columnTotals) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function () {

    if ($.fn.DataTable) {

        let totalColumns = $('#attendance_report_table thead th').length;

        /*
         * Date columns start from index 3 and end before Grand Total.
         */
        let dateColumnIndexes = [];

        for (let i = 3; i < totalColumns - 1; i++) {
            dateColumnIndexes.push(i);
        }

        let table = $('#attendance_report_table').DataTable({
            paging: false,
            lengthChange: false,
            searching: false,
            info: false,
            ordering: true,
            order: [[0, 'asc']],
            columnDefs: [
                {
                    targets: dateColumnIndexes,
                    orderable: false
                },
                {
                    targets: totalColumns - 1,
                    orderable: true
                }
            ]
        });

        table.on('order.dt', function () {

            let order = table.order();

            if (order.length > 0) {

                let columnIndex = order[0][0];
                let direction = order[0][1];

                let sortColumn = 'ParticipantId';

                if (columnIndex === 0) {
                    sortColumn = 'ParticipantId';
                } else if (columnIndex === 1) {
                    sortColumn = 'LastName';
                } else if (columnIndex === 2) {
                    sortColumn = 'FirstName';
                } else if (columnIndex === totalColumns - 1) {
                    sortColumn = 'row_total';
                }

                $('#pdf_sort_column').val(sortColumn);
                $('#pdf_sort_dir').val(direction);
            }

        });
    }

});
</script>
@endsection
