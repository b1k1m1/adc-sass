@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">All Members List</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{route('members.dashboard') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-body">

                        <table id="members-table" class="table table-bordered dt-responsive  nowrap w-100">
                            <thead>
                            <tr>
                                <th>Sr#</th>
                                <th>ID</th>
                                <th>Photo</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>Zip Code</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>


                            <tbody>
                            @foreach($members as $key=> $member )
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td><a href="#">{{ str_pad( $member->ParticipantId ,3, '0',STR_PAD_LEFT) }}</a></td>
                                @php
                                    $numericId = (int) trim($member->ParticipantId); // '005 ' → 5
                                    $imageFilename = $numericId . '.jpg';
                                    $imagePath = 'members/images/' . $imageFilename;
                                    $fullImagePath = public_path($imagePath);
                                    $displayPath = file_exists($fullImagePath) ? asset($imagePath) : asset('members/images/no_image.jpg');
                                @endphp
                                <td><img src={{ $displayPath }} width="50" class="img-thumbnail rounded-circle"></td>
                                <td>{{ $member->LastName }}</td>
                                <td>{{ $member->FirstName }}</td>
                                <td>{{ $member->Address1 }}</td>
                                <td>{{ $member->City }}</td>
                                <td>{{ $member->ZipCode }}</td>
                                @if($member->Trans_flag == 'A')
                                    <td><span class="btn btn-success btn-sm">Active</span></td>
                                @elseif($member->Trans_flag == 'D')
                                    <td><span class="btn btn-danger btn-sm">Discharge</span></td>
                                @elseif($member->Trans_flag == 'G')
                                    <td><span class="btn btn-info btn-sm">Guest</span></td>
                                @elseif($member->Trans_flag == 'H')
                                    <td><span class="btn btn-warning btn-sm">Hold</span></td>
                                    @elseif($member->Trans_flag == 'N')
                                    <td><span class="btn btn-warning btn-sm">Noon</span></td>
                                @elseif($member->Trans_flag == '')
                                    <td><span class="btn btn-secondary btn-sm">No Status</span></td>
                                @endif
                                <td>
                                    <a href="{{ route('edit.member', str_pad( $member->ParticipantId ,3, '0',STR_PAD_LEFT)) }}" class="btn btn-primary waves-light waves-effect" aria-expanded="false">Edit
                                    <i class="mdi mdi-dots-vertical ms-2"></i></a>
                                    <button class="btn btn-info waves-light waves-effect edit-medical-btn" aria-expanded="false" data-id="{{ str_pad( $member->ParticipantId ,3, '0',STR_PAD_LEFT) }}">Update PA Info
                                        <i class="mdi mdi-dots-vertical ms-2"></i>
                                    </button>
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
<!-- Add PA update Modal -->
<div class="modal fade" id="updatePAModal" tabindex="-1" aria-labelledby="updatePAModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="updatePAForm">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addCountryModalLabel">Add/Update PA & HNP information</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="country_name">ID#</label>
              <input type="text" name="ParticipantId" id="ParticipantId" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label for="country_name">Last Name</label>
                <input type="text" name="LastName" id="LastName" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="country_name">First Name</label>
                <input type="text" name="FirstName" id="FirstName" class="form-control" required>
              </div>
              <div class="form-group">
                <select id="HMO_Enrolled" name="HMO_Enrolled" class="form-select w-50 mt-2">
                        <option value="">-- Select Insurance -- </option>
                </select>
              </div>
              <div class="form-group">
                <label for="country_name">Insurance ID#</label>
                <input type="text" name="medicaid_HMO_id" id="medicaid_HMO_id" class="form-control" required>
              </div>
              <div class="form-group mt-2">
                <label class="form-label">Primary Diagnosis (ICD-10)</label>
                <select id="PrimaryDiagnosis1_modal" name="PrimaryDiagnosis1" class="form-control icd10-select"></select>
              </div>

              <div class="form-group mt-2">
                <label class="form-label">Secondary Diagnosis (ICD-10)</label>
                <select id="SecondaryDiag1_modal" name="SecondaryDiag1" class="form-control icd10-select"></select>
              </div>
              <div class="form-group">
                <label for="country_name">PA Number</label>
                <input type="text" name="medicaid_PA_no" id="medicaid_PA_no" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="country_name">PA Start Date</label>
                <input type="text" name="medicaid_PA_Start" id="medicaid_PA_Start" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="country_name">PA End Date</label>
                <input type="text" name="medicaid_PA_End" id="medicaid_PA_End" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="HNP_Expiry_date">HNP Expiry Date</label>
                <input type="text" name="HNP_Expiry_date" id="HNP_Expiry_date" class="form-control" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save PA Info</button>
          </div>
        </div>
      </form>
    </div>
  </div>

@endsection

@section('scripts')
<script>


$(document).ready(function () {

    $('#members-table').DataTable({
        pageLength: 100, // ✅ Default entries per page
        lengthMenu: [ [100, 200, 300, 500, -1], [100, 200, 300, 500, "All"] ], // ✅ Dropdown options
    });

    //$(document).on('click', '.edit-medical-btn', function () {

    //alert('Button clicked!');

    // Auto-open PA modal if URL has ?open=pa&id=005
    const url = new URL(window.location.href);
    const open = (url.searchParams.get('open') || '').toLowerCase();
    const id   = (url.searchParams.get('id') || '').trim();

    if (open === 'pa' && id) {
        // small delay so bootstrap + datatable fully ready
        setTimeout(() => openPAModal(id), 250);
    }

});


    // Show modal and fetch member info
    /*
    $(document).on('click', '.edit-medical-btn', function () {
        const memberId = $(this).data('id');

        $('#updatePAForm')[0].reset();

        //console.log('Clicked member ID:', memberId);

        // clear select2 (if previously opened)
        $('#PrimaryDiagnosis1_modal').empty().trigger('change');
        $('#SecondaryDiag1_modal').empty().trigger('change');

        $.get('/members/' + memberId + '/medical-info', function (data) {

            //console.log('data.member:', data.member);

           const hmoOptions = [
                     "HORIZON NJ HEALTH",
                     "UNITED HEALTH CARE",
                     "WELLCARE",
                     "AMERI GROUP NJ",
                     "AETNA BETTER HEALTH",
                     "HEALTH FIRST",
                     "PRIVATE PAY",
                    "OTHERS"
                ];

            const rawDatePAStart = data.medicaid_PA_Start; // e.g., '2011-02-07 00:00:00'
            const rawDatePAEnd = data.medicaid_PA_End; // e.g., '2011-02-07 00:00:00'

            $('#ParticipantId').val(data.ParticipantId);
            $('#LastName').val(data.LastName);
            $('#FirstName').val(data.FirstName);

            const selectedHMO = data.HMO_Enrolled ? data.HMO_Enrolled.trim() : '';

            $('#HMO_Enrolled').empty().append('<option value="">-- Select Insurance--</option>');

            hmoOptions.forEach(function (option) {
            const selected = (option === selectedHMO) ? 'selected' : '';
            $('#HMO_Enrolled').append(`<option value="${option}" ${selected}>${option}</option>`);
            });

            $('#medicaid_HMO_id').val(data.medicaid_HMO_id);
            $('#medicaid_PA_no').val(data.medicaid_PA_no);

            //
            if (rawDatePAStart) {
                const date = new Date(rawDatePAStart);
                const formattedPAStart = `${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getDate().toString().padStart(2, '0')}/${date.getFullYear()}`;
            $('#medicaid_PA_Start').val(formattedPAStart);
            }
            //$('#medicaid_PA_Start').val(data.medicaid_PA_Start);

            if (rawDatePAEnd) {
                const date = new Date(rawDatePAEnd);
                const formattedPAEnd = `${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getDate().toString().padStart(2, '0')}/${date.getFullYear()}`;
            $('#medicaid_PA_End').val(formattedPAEnd);
            }
            //$('#medicaid_PA_End').val(data.medicaid_PA_End);

            //$('#medications').val(data.medications);
            $('#updatePAModal').modal('show');

             // after modal is fully visible, init select2 + set selected values
        $('#updatePAModal').one('shown.bs.modal', function () {

            initIcd10InModal('#PrimaryDiagnosis1_modal');
            initIcd10InModal('#SecondaryDiag1_modal');

            // now set saved ICD10 values
            setIcd10Value($('#PrimaryDiagnosis1_modal'), data.PrimaryDiagnosis1);
            setIcd10Value($('#SecondaryDiag1_modal'), data.SecondaryDiag1);
        });

        });
    });
    */

    function openPAModal(memberId){
        memberId = (memberId || '').toString().trim();
        if(!memberId) return;

        $('#updatePAForm')[0].reset();

        // clear select2 (if previously opened)
        $('#PrimaryDiagnosis1_modal').empty().trigger('change');
        $('#SecondaryDiag1_modal').empty().trigger('change');

        $.get('/members/' + encodeURIComponent(memberId) + '/medical-info', function (data) {

            const hmoOptions = [
                "HORIZON NJ HEALTH",
                "UNITED HEALTH CARE",
                "WELLCARE",
                "AMERI GROUP NJ",
                "AETNA BETTER HEALTH",
                "HEALTH FIRST",
                "PRIVATE PAY",
                "OTHERS"
            ];

            const rawDatePAStart = data.medicaid_PA_Start;
            const rawDatePAEnd   = data.medicaid_PA_End;
            const rawDateHNP   = data.HNP_Expiry_date;


            $('#ParticipantId').val(data.ParticipantId);
            $('#LastName').val(data.LastName);
            $('#FirstName').val(data.FirstName);

            const selectedHMO = data.HMO_Enrolled ? data.HMO_Enrolled.trim() : '';
            $('#HMO_Enrolled').empty().append('<option value="">-- Select Insurance--</option>');
            hmoOptions.forEach(function (option) {
                const selected = (option === selectedHMO) ? 'selected' : '';
                $('#HMO_Enrolled').append(`<option value="${option}" ${selected}>${option}</option>`);
            });

            $('#medicaid_HMO_id').val(data.medicaid_HMO_id);
            $('#medicaid_PA_no').val(data.medicaid_PA_no);

            if (rawDatePAStart) {
                const date = new Date(rawDatePAStart);
                const formatted = `${(date.getMonth()+1).toString().padStart(2,'0')}/${date.getDate().toString().padStart(2,'0')}/${date.getFullYear()}`;
                $('#medicaid_PA_Start').val(formatted);
            }

            if (rawDatePAEnd) {
                const date = new Date(rawDatePAEnd);
                const formatted = `${(date.getMonth()+1).toString().padStart(2,'0')}/${date.getDate().toString().padStart(2,'0')}/${date.getFullYear()}`;
                $('#medicaid_PA_End').val(formatted);
            }

            if (rawDateHNP) {
                const date = new Date(rawDateHNP);
                const formatted = `${(date.getMonth()+1).toString().padStart(2,'0')}/${date.getDate().toString().padStart(2,'0')}/${date.getFullYear()}`;
                $('#HNP_Expiry_date').val(formatted);
            }else {
                $('#HNP_Expiry_date').val('');
            }

            $('#updatePAModal').modal('show');

            // after modal is fully visible, init select2 + set selected values
            $('#updatePAModal').one('shown.bs.modal', function () {
                initIcd10InModal('#PrimaryDiagnosis1_modal');
                initIcd10InModal('#SecondaryDiag1_modal');

                setIcd10Value($('#PrimaryDiagnosis1_modal'), data.PrimaryDiagnosis1);
                setIcd10Value($('#SecondaryDiag1_modal'), data.SecondaryDiag1);
            });
        })
        .fail(function(xhr){
            toastr.error('Unable to load PA info for ID ' + memberId);
        });
    }

// click button uses the same function
$(document).on('click', '.edit-medical-btn', function () {
    openPAModal($(this).data('id'));
});

    function setIcd10Value($select, code) {
    code = (code || '').toString().trim();

    if (!code) {
        $select.val(null).trigger('change');
        return;
    }

    // If already exists, select it
    if ($select.find("option[value='" + code.replace(/'/g, "\\'") + "']").length) {
        $select.val(code).trigger('change');
        return;
    }

    // Use your existing route: /lookup/icd10/{code}
    $.get("{{ url('/lookup/icd10') }}/" + encodeURIComponent(code), function (item) {
        const id   = item.id || item.code || code;
        const text = item.text || (item.code ? (item.code + ' - ' + (item.short_description || '')) : code);

        const opt = new Option(text, id, true, true);
        $select.append(opt).trigger('change');
    }).fail(function () {
        const opt = new Option(code, code, true, true);
        $select.append(opt).trigger('change');
    });
}

    // Submit medical info
    $('#updatePAForm').on('submit', function (e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.ajax({
        url: '/members/update-medical-info', // 👈 your route
        method: 'POST',
        data: formData,
        success: function (response) {
            $('#updatePAModal').modal('hide');
            toastr.success(response.message || 'Saved successfully!');

             // ✅ 1) BroadcastChannel (best)
            try {
                if ('BroadcastChannel' in window) {
                const bc = new BroadcastChannel('adc-chat');
                bc.postMessage({ type: 'refresh_chat' });
                bc.close();
                }
            } catch (e) {}

            // ✅ 2) Fallback to opener (if available)
            try {
                if (window.opener && !window.opener.closed) {
                window.opener.postMessage({ type: 'refresh_chat' }, window.location.origin);
                }
            } catch (e) {}
            },

        error: function (xhr) {
            const msg = xhr.responseJSON?.message || 'Something went wrong!';
            toastr.error(msg);
            }
        });
    });

    function initIcd10InModal(selector) {
    const $el = $(selector);
    const $modal = $('#updatePAModal');

    // if already initialized, destroy and re-init
    if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
    }

    $el.select2({
        width: '100%',
        placeholder: 'Type ICD-10 code or description...',
        allowClear: true,
        minimumInputLength: 2,
        dropdownParent: $modal, // ✅ REQUIRED
        ajax: {
        url: "{{ route('lookup.icd10') }}",
        dataType: 'json',
        delay: 300,
        data: function (params) {
            return { q: params.term || '', limit: 25 };
        },
        processResults: function (data) {
            // your endpoint already returns Select2 format
            return data;
        },
        cache: true
        }
    });
    }

 </script>
@endsection

