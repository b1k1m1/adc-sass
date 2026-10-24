@extends('admin.admin_dashboard')
@section('admin')
@inject('carbon', 'Carbon\Carbon')


<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Active Members List</h4>



                    <!--
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('add.member') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>
                -->

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-body">

                       <div class="col d-flex gap-2 mb-3 flex-wrap align-items-end">
                            <!-- Month Picker -->
                            <div>
                                <label for="monthPicker" class="form-label mb-0 me-2">Select Month & Year:</label>
                                <input type="month" id="monthPicker" class="form-control d-inline-block"
                                    style="width: 180px;"
                                    value="{{ date('Y-m') }}" min="2000-01" max="2099-12">
                            </div>

                            <!-- Buttons -->
                            <div class="d-flex gap-2">
                                <a href="{{ route('nursing.members', 'A') }}"
                                class="btn {{ $type === 'A' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Morning Shift
                                </a>
                                <a href="{{ route('nursing.members', 'N') }}"
                                class="btn {{ $type === 'N' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                                    Noon Shift
                                </a>
                            </div>

                            <div class="col-auto ms-auto">
                                <a href="{{ route('doctors.dashboard') }}" class="btn btn-primary no-print">
                                    Back
                                </a>
                            </div>
                        </div>

                        <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                            <thead>
                            <tr>
                                <th>Sr#</th>
                                <th>ID</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>Provider</th>
                                <th>PA End</th>
                                <th># Days Passed</th>
                                <th>Action</th>
                            </tr>
                            </thead>


                            <tbody>
                            @foreach($members as $key=> $member )
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td><a href="#">{{ str_pad( $member->ParticipantId ,3, '0',STR_PAD_LEFT) }}</a></td>
                                <td>{{ $member->LastName }}</td>
                                <td>{{ $member->FirstName }}</td>
                                @if( trim($member->HMO_Enrolled) === 'HORIZON NJ HEALTH')
                                    <td><span class="btn btn-success btn-sm">HZ</span></td>
                                @elseif(trim($member->HMO_Enrolled) === 'UNITED HEALTH CARE')
                                    <td><span class="btn btn-danger btn-sm">UH</span></td>
                                @elseif(trim($member->HMO_Enrolled) === 'WELLCARE')
                                    <td><span class="btn btn-info btn-sm">WE</span></td>
                                @elseif(trim($member->HMO_Enrolled) === 'AMERI GROUP NJ')
                                    <td><span class="btn btn-warning btn-sm">AG</span></td>
                                @elseif(trim($member->HMO_Enrolled) === 'AETNA BETTER HEALTH')
                                    <td><span class="btn btn-warning btn-sm">AE</span></td>
                                @elseif(trim($member->HMO_Enrolled) === '')
                                    <td><span class="btn btn-secondary btn-sm">No Provider</span></td>
                                @endif
                                @php
                                $medicaidEnd = $member->medicaid_PA_End;
                                $formattedDate = $medicaidEnd ? \Carbon\Carbon::parse($medicaidEnd)->format('m/d/Y') : '';
                                $dateObj = $medicaidEnd ? \Carbon\Carbon::parse($medicaidEnd) : null;
                                $now = \Carbon\Carbon::now();

                                $label = '-';
                                $badgeClass = 'secondary';

                                if ($dateObj) {
                                    $isPast = $dateObj->isPast();
                                    $daysDiff = round($now->diffInDays($dateObj)); // round off here

                                     if ($isPast) {
                                        $label = "Expired {$daysDiff} day" . ($daysDiff !== 1 ? 's' : '') . " ago";
                                        $badgeClass = 'danger';
                                    } else {
                                        $label = "Due in {$daysDiff} day" . ($daysDiff !== 1 ? 's' : '');
                                        $badgeClass = $daysDiff <= 30 ? 'warning' : 'success';
                                    }
                                 }
                                @endphp

                                <td>{{ $formattedDate }}</td>
                                <td>
                                        <span class="btn btn-{{ $badgeClass }} btn-sm font-weight-bold">
                                            {{ $label }}
                                        </span>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-primary add-vitals-btn"
                                        data-id="{{ str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT) }}">
                                    Add Vitals
                                    </a>
                                    <!--
                                    <a href="#" class="btn btn-info add-adl-btn"
                                        data-id="{{ str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT) }}">
                                    Add ADL
                                    </a>
                                    -->
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div> <!-- end col -->
        </div> <!-- end row -->


    </div> <!-- container-fluid -->
</div>
<!-- End Page-content -->
@endsection
<!-- JavaScript Libraries -->
<!-- Load jQuery first -->
<!-- Then load datepicker -->
@section('scripts')
<script>
$(document).ready(function() {

      // Handle enter data button click
     // $('.enter-data').click(function() {
       // const ParticipantId = $(this).data('student-id');
       // const monthYear = $('#monthYear').val();

        //if (!monthYear) {
         //   alert('Please select a month and year');
         //   return;
       // }

       // window.location.href = `/nursing/editbpdata/${ParticipantId}/${monthYear}`;
    //});

});

/*
document.addEventListener('DOMContentLoaded', function() {

    flatpickr("#monthPicker", {
    plugins: [
    new monthSelectPlugin({
      shorthand: true, // show 3-letter months
      dateFormat: "M-Y", // output format
      altFormat: "F Y",  // alternative display format if needed
      theme: "light"
    })
  ]
});
    }); */


    document.addEventListener('DOMContentLoaded', function () {
    const monthPicker = document.getElementById('monthPicker');

    function updateButtons() {
        const selectedMonth = monthPicker.value; // "2021-10"
        const selectedType = "{{ $type }}";   // either "A" or "N"
        const baseUrl = "{{ url('nursing/editbpdata') }}";
        const baseUrl_ADL = "{{ url('nursing/editadldata') }}";

        document.querySelectorAll('.add-vitals-btn').forEach(function (btn) {
            const pid = btn.dataset.id;
            const newUrl = `${baseUrl}/${pid}/${selectedMonth}/${selectedType}`;
            btn.setAttribute('href', newUrl);
        });


         document.querySelectorAll('.add-adl-btn').forEach(function (btn) {
            const pid = btn.dataset.id;
            const newUrl = `${baseUrl_ADL}/${pid}/${selectedMonth}/${selectedType}`;
            btn.setAttribute('href', newUrl);
        });
    }

    // Run once on page load
    updateButtons();

    // Re-run whenever the month is changed
    monthPicker.addEventListener('change', updateButtons);
});
</script>

@endsection



