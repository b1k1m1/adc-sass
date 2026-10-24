@extends('admin.admin_dashboard')
@section('admin')

@php
    // Fallbacks if controller didn't pass them
    $type  = $type ?? 'A'; // 'A' or 'N'
    $title = $title ?? ($type === 'N' ? 'Non-Active Members Attendance Dates' : 'Active Members Attendance Dates');
@endphp

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-xl-12">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">{{ $title }}</h4>

                            <div class="page-title-right d-flex gap-2">
                                {{-- Toggle buttons --}}


                                <div class="d-flex gap-2" role="group" aria-label="Attendance type">
                                    <a href="{{ route('attendance.dates', 'A') }}"
                                    class="btn btn-sm {{ $type === 'A' ? 'btn-primary' : 'btn-outline-primary' }} waves-effect waves-light">
                                        Morning Shift
                                    </a>
                                    <a href="{{ route('attendance.dates', 'N') }}"
                                    class="btn btn-sm {{ $type === 'N' ? 'btn-secondary' : 'btn-outline-secondary' }} waves-effect waves-light">
                                        Noon Shift
                                    </a>
                                </div>

                                <ol class="breadcrumb m-0">
                                    <a href="{{ route('attendance.index', ['type' => $type ?? 'A']) }}" class="btn btn-primary waves-effect waves-light">
                                        Enter New Attendance
                                    </a>
                                </ol>

                                <ol class="breadcrumb m-0">
                                    <a href="{{ route('attendance.dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="datatable" class="table table-bordered dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Sr#</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($allDates as $key => $allDate)
                                                @php
                                                    // Normalize date for display & URLs
                                                    $dt   = $allDate->CalenderDate instanceof \Carbon\Carbon
                                                            ? $allDate->CalenderDate
                                                            : \Carbon\Carbon::parse($allDate->CalenderDate);
                                                    $disp = $dt->format('m/d/Y');
                                                    $raw  = $dt->toDateString(); //  YYYY-MM-DD
                                                @endphp
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>

                                                    {{-- For better DataTables sort, include data-order as raw date --}}
                                                    <td data-order="{{ $raw }}">{{ $disp }}</td>

                                                    <td class="d-flex gap-2">
                                                        {{-- Keep your existing routes; pass ?type= to preserve which list user returns to --}}
                                                        <a href="{{ route('attendance.index', ['type' => $type, 'date' => $raw]) }}"
                                                            class="btn btn-primary waves-effect waves-light" aria-expanded="false">
                                                            Edit <i class="mdi mdi-dots-vertical ms-2"></i>
                                                        </a>
                                                    </td>
                                                </tr>

                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- end card body -->

                        </div>
                        <!-- end card -->
                    </div>
                </div>

            </div>
        </div>
        <!-- end page title -->

    </div>
</div>

@endsection
