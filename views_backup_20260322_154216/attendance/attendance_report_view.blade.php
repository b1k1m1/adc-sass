@extends('admin.admin_dashboard')
@section('admin')

<style>
.table-responsive {
    overflow-x: auto;
    white-space: nowrap;
}

.compact-table tr{

    height:24px !important; /* Adjust row height */

}

.compact-table th, .compact-table td {

    padding: 2px 4px !important; /* reducing padding */
    font-sizing: 12px !important; /* smaller font */
    border: 1px solid #e2e8f0; /* Light borders */

}
</style>

<div class="page-content">
    <div class="container-fluid">

    <!--
    <h1>Attendance Report from {{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }}</h1>
    <div>
        <form action="{{ route('attendance.export.pdf') }}" method="GET" class="d-inline">
            <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
            <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
            <button type="submit" id="pdfExportBtn" class="btn btn-danger">
                Export to PDF
            </button>
        </form>

        <form action="{{ route('attendance.export.excel') }}" method="GET" class="d-inline ms-2">
            <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
            <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
            <button type="submit" id="excelExportBtn" class="btn btn-success">
                Export to Excel
            </button>
        </form>
    </div>
    -->

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">
            @php
                if($type == 'A'){
                   $type1 = 'Morning Shift';
                }elseif($type == 'N') {
                   $type1 = 'Noon Shift';
                }
            @endphp
            {{ $company->comp_nm ?? '' }} - {{ $type1 }} <br>
            Attendance Report from {{ $startDate->format('m/d/Y') }} to {{ $endDate->format('m/d/Y') }}</h3>
    <div>
            <form action="{{ route('attendance.export.pdf', $type ?? 'A') }}" method="GET" class="d-inline">
                <input type="hidden" name="start_date" value="{{ $startDate->format('m/d/Y') }}">
                <input type="hidden" name="end_date" value="{{ $endDate->format('m/d/Y') }}">
                <button type="submit" id="pdfExportBtn" class="btn btn-danger">
                    Export to PDF
                </button>
            </form>

            <form action="{{ route('attendance.export.excel' ,$type ?? 'A') }}" method="GET" class="d-inline ms-2">
                <input type="hidden" name="start_date" value="{{ $startDate->format('m/d/Y') }}">
                <input type="hidden" name="end_date" value="{{ $endDate->format('m/d/Y') }}">
                <button type="submit" id="excelExportBtn" class="btn btn-success">
                    Export to Excel
                </button>
            </form>
            <form action="{{ route('attendance.export.excel.billing', $type ?? 'A') }}" method="GET" class="d-inline ms-2">
                <input type="hidden" name="start_date" value="{{ $startDate->format('m/d/Y') }}">
                <input type="hidden" name="end_date" value="{{ $endDate->format('m/d/Y') }}">
                <button type="submit" id="excelExportbillBtn" class="btn btn-success">
                    Export to Billing Excel
                </button>
            </form>
            <!-- <a href="{{ route('attendance.dashboard') }}" class="btn btn-primary waves-effect waves-light ms-2">{{ t('common.back','Back') }}</a> -->
            <x-back-button class="btn btn-primary waves-effect waves-light ms-2">
                <i class="bi bi-arrow-left"></i> {{ t('common.back','Back') }}
            </x-back-button>
        </div>
    </div>
    <div class="table-responsive">
    <table class="table compact-table table-bordered">
        <thead>
            <tr>
                <th>ID#</th>
                <th>Participant</th>
                @foreach($attendanceData as $participantId => $participant)
                    @foreach($participant['attendance'] as $date => $status)

                        <th style="text-align:center;">{{ \Carbon\Carbon::parse($date)->format('d') }}</th>
                    @endforeach
                @break
            @endforeach
            <th style="text-align:center;">Grand Total</th>

        </tr>
        </thead>
        <tbody>
            @foreach($attendanceData as $participantId => $participant)
            <tr>
                <td>{{ $participant['ParticipantId'] }}</td>
                <td>{{ $participant['LastName'] }}, {{ $participant['FirstName'] }}</td>
                @foreach($participant['attendance'] as $date => $status)
                   <!-- <td>{{ $status == 1 ? '1' : '0' }}</td> -->
                   <td class="text-center {{ $status == 1 ? 'text-success' : 'text-danger' }}">
                    {{ $status }}
                   </td>
                @endforeach
                <td class="text-center">{{ $participant['row_total'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold text-center table-secondary">
                <td colspan="2">Grand Totals</td>
                @foreach ($dateRange as $date)
                    <td>{{ $columnTotals[$date] ?? 0 }}</td>
                @endforeach
                <td>
                    {{ array_sum($columnTotals) }}
                </td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>
</div>

@endsection
