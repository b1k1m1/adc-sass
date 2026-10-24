@extends('admin.admin_dashboard')
@section('title', 'Member Payment Plan')
@section('admin')
@section('page-toolbar')

<button type="button" class="btn btn-outline-primary flag-btn active" id="btnMorning" data-flag="A">
    Morning
</button>
<button type="button" class="btn btn-outline-primary flag-btn" id="btnNoon" data-flag="N">
     Noon
</button>

<a href="{{ url()->previous() }}" class="btn btn-primary waves-effect waves-light">
      {{ t('common.back','Back') }}
</a>
@endsection


<style>
  .dt-input {
    width: 100%;
    border: 1px solid #d9dee3;
    border-radius: 6px;
    padding: 6px 8px;
    font-size: 13px;
  }
  .dt-input:focus { outline: none; box-shadow: 0 0 0 2px rgba(13,110,253,.15); border-color:#0d6efd; }
  .flag-btn.active { border-color:#0d6efd; background:#0d6efd; color:#fff; }

  .member-photo {
      width: 90px;
      height: 90px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid #ccc;
      background: #f8f9fa;
  }

  .member-photo:hover {
    transform: scale(1.25);
    transition: 0.2s ease-in-out;
    z-index: 10;
}
</style>

<div class="page-content">
  <div class="container-fluid">
    <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <!-- LEFT SIDE -->
                        <div>
                            <h4 class="mb-0 font-size-18">Member Payment Plan</h4>
                        </div>
                        <!-- RIGHT SIDE TOOLBAR -->
                        <div class="page-title-right">
                            @yield('page-toolbar')
                        </div>
                    </div>
                </div>
             </div>
            <!-- end page title -->

    <div class="card">
      <div class="card-body">


        <div class="table-responsive">
          <table id="mppTable" class="table table-sm table-bordered align-middle w-100">
            <thead class="table-light">
              <tr>
                <th class="text-center" style="width:60px;">Pic</th>
                <th style="width:90px;">ParticipantId</th>
                <th>Name</th>
                <th style="width:180px;">City</th>
                <th style="width:180px;">Payment Plan</th>
                <th>PP Comments</th>
              </tr>
            </thead>
          </table>
        </div>

        <div class="d-flex justify-content-end mt-3">
          <button class="btn btn-success" id="btnUpdate">
            <i class="fas fa-save me-1"></i> Update
          </button>
        </div>

      </div>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  let currentFlag = 'A';
  let dt = null;

  function buildTable() {
    if (dt) dt.destroy();

    dt = $('#mppTable').DataTable({
      processing: true,
      serverSide: true,
      pageLength: 25,
      ajax: {
        url: "{{ route('mpp.data') }}",
        data: function (d) {
          d.trans_flag = currentFlag;
        }
      },
      columns: [
        { data: 'photo', name: 'photo', orderable: false, searchable: false, className: 'text-center' },
        { data: 'ParticipantId', name: 'ParticipantId' },
        { data: 'full_name', name: 'LastName', orderable: true },
        { data: 'City', name: 'City' },

        // Editable: payment_plan
        {
          data: 'payment_plan',
          name: 'payment_plan',
          orderable: false,
          render: function (data, type, row) {
            const val = data ?? '';
            return `<input class="dt-input payment_plan" data-pid="${row.ParticipantId}" value="${escapeHtml(val)}">`;
          }
        },

        // Editable: pp_comments
        {
          data: 'pp_comments',
          name: 'pp_comments',
          orderable: false,
          render: function (data, type, row) {
            const val = data ?? '';
            return `<input class="dt-input pp_comments" data-pid="${row.ParticipantId}" value="${escapeHtml(val)}">`;
          }
        }
      ],
      order: [[1, 'asc']],
    });
  }

  function escapeHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  // Initial load
  buildTable();

  // Morning / Noon buttons
  document.querySelectorAll('.flag-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.flag-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');

      currentFlag = this.dataset.flag; // 'A' or 'N'
      buildTable();
    });
  });

  // Bottom Update button
  document.getElementById('btnUpdate').addEventListener('click', function () {

    // collect values from CURRENT page (visible rows)
    // If you want ALL rows across pages, we can do a different approach.
    let rows = [];
    let seen = new Set();

    $('#mppTable tbody tr').each(function () {
      const pid = $(this).find('input.payment_plan').data('pid');
      if (!pid) return;

      if (!seen.has(pid)) {
        seen.add(pid);
        const payment_plan = $(this).find('input.payment_plan').val();
        const pp_comments  = $(this).find('input.pp_comments').val();

        rows.push({
          ParticipantId: pid,
          payment_plan: payment_plan,
          pp_comments: pp_comments
        });
      }
    });

    if (rows.length === 0) {
      toastr.warning('No rows found to update.');
      return;
    }

    $.ajax({
      url: "{{ route('mpp.update') }}",
      method: "POST",
      data: {
        _token: "{{ csrf_token() }}",
        trans_flag: currentFlag,
        rows: rows
      },
      success: function (res) {
        if (res.ok) {
          toastr.success(res.message || 'Updated');
          dt.ajax.reload(null, false);
        } else {
          toastr.error(res.message || 'Update failed');
        }
      },
      error: function (xhr) {
        toastr.error(xhr.responseJSON?.message || 'Server error');
      }
    });
  });

});
</script>

@endsection
