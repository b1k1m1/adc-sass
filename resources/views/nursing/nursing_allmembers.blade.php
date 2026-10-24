@extends('admin.admin_dashboard')
@section('admin')
@inject('carbon', 'Carbon\Carbon')


<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">
                        {{ $shiftTitle ?? 'Morning Shift' }} Members List
                    </h4>

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
                                    <i class="fas fa-sun"></i> Morning Shift
                                </a>

                                <a href="{{ route('nursing.members', 'N') }}"
                                class="btn {{ $type === 'N' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                                    <i class="fas fa-clock"></i> Noon Shift
                                </a>

                                <a href="{{ route('nursing.members', 'G') }}"
                                class="btn {{ $type === 'G' ? 'btn-warning' : 'btn-outline-warning' }}">
                                    <i class="fas fa-user"></i> Guest
                                </a>
                            </div>

                            <div class="col-auto ms-auto">
                                <a href="{{ route('doctors.dashboard') }}" class="btn btn-primary no-print">
                                    Back
                                </a>
                            </div>
                        </div>

                        <table id="members-table" class="table table-bordered dt-responsive  nowrap w-100">
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
                                @php
                                    $hmoName = trim($member->HMO_Enrolled ?? '');
                                    $hmo = $hmoColors[$hmoName] ?? null;

                                    $shortCode = $hmo->short_code ?? 'No Provider';
                                    $bgColor = $hmo->color_hex ?? '#6c757d';

                                    // Make text white for darker colors, black for light colors
                                    $textColor = '#ffffff';

                                    if (in_array(strtoupper($bgColor), ['#FFFF00', '#00FFFF', '#F0F0F0', '#FFFFFF'])) {
                                        $textColor = '#000000';
                                    }
                                @endphp

                                <td class="text-center align-middle">
                                    @if($hmoName === '')
                                        <span class="btn btn-secondary btn-sm">No Provider</span>
                                    @else
                                        <span class="btn btn-sm"
                                            style="background-color: {{ $bgColor }}; color: {{ $textColor }}; border: 1px solid #999;">
                                            {{ $shortCode }}
                                        </span>
                                    @endif
                                </td>
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
                                <td class="text-nowrap">

                                <a href="#" class="btn btn-primary add-vitals-btn"
                                data-id="{{ str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT) }}">
                                    Add Vitals
                                </a>

                                {{-- Show Add ADL only for Noon Shift --}}
                                @if(($type ?? 'A') === 'N')
                                    <a href="#" class="btn btn-info add-adl-btn ms-1"
                                    data-id="{{ str_pad($member->ParticipantId, 3, '0', STR_PAD_LEFT) }}">
                                        Add ADL
                                    </a>
                                @endif

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

     $('#members-table').DataTable({
        pageLength: 200, // ✅ Default entries per page
        lengthMenu: [ [ 200, 300, 500, -1], [ 200, 300, 500, "All"] ], // ✅ Dropdown options
    });

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

   /*
    document.addEventListener('DOMContentLoaded', function () {
    const monthPicker = document.getElementById('monthPicker');

    function updateButtons() {
        const selectedMonth = monthPicker.value; // "2021-10"
        const selectedType = "{{ $type }}";   // A = Morning, N = Noon, G = Guest
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
*/

document.addEventListener('DOMContentLoaded', function () {

    const monthPicker = document.getElementById('monthPicker');

    const vitalsBaseUrl = @json(url('nursing/editbpdata'));
    const adlBaseUrl = @json(url('nursing/editadldata'));
    const selectedType = @json($type);

    /*
     * Add Vitals
     */
    document.addEventListener('click', function (event) {

        const vitalsButton = event.target.closest('.add-vitals-btn');

        if (!vitalsButton) {
            return;
        }

        event.preventDefault();

        const participantId = vitalsButton.dataset.id;
        const selectedMonth = monthPicker.value;

        if (!selectedMonth) {
            alert('Please select a month and year.');
            monthPicker.focus();
            return;
        }

        window.location.href =
            `${vitalsBaseUrl}/${encodeURIComponent(participantId)}/${encodeURIComponent(selectedMonth)}/${encodeURIComponent(selectedType)}`;
    });

    /*
     * Add ADL
     */
    document.addEventListener('click', function (event) {

        const adlButton = event.target.closest('.add-adl-btn');

        if (!adlButton) {
            return;
        }

        event.preventDefault();

        const participantId = adlButton.dataset.id;
        const selectedMonth = monthPicker.value;

        if (!selectedMonth) {
            alert('Please select a month and year.');
            monthPicker.focus();
            return;
        }

        window.location.href =
            `${adlBaseUrl}/${encodeURIComponent(participantId)}/${encodeURIComponent(selectedMonth)}/${encodeURIComponent(selectedType)}`;
    });

});

</script>

@endsection



