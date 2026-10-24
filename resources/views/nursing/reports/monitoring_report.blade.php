@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="row mb-3">
            <div class="col-12 text-center">
                <h4 class="mb-1">{{ $company['name'] ?? '' }}</h4>
                <div>{{ $company['address'] ?? '' }}</div>
                <div>
                    @if(!empty($company['phone'])) Phone: {{ $company['phone'] }} @endif
                    @if(!empty($company['fax'])) &nbsp;&nbsp; Fax: {{ $company['fax'] }} @endif
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Monitoring Report for Members</h4>

            <div class="d-flex gap-2 align-items-center">
                <form method="GET" action="{{ route('nursing.monitoring_report') }}" class="d-flex gap-2 align-items-center mb-0">
                    <input type="hidden" name="shift" id="shift" value="{{ $shift ?? '' }}">
                    <input type="hidden" name="dt_search" id="dt_search" value="{{ request('dt_search', '') }}">
                    <input type="hidden" name="dt_order_col" id="dt_order_col" value="{{ request('dt_order_col', '') }}">
                    <input type="hidden" name="dt_order_dir" id="dt_order_dir" value="{{ request('dt_order_dir', '') }}">

                    <button type="button"
                            class="btn btn-outline-primary shift-btn {{ ($shift ?? '') == 'A' ? 'active' : '' }}"
                            data-value="A">
                        Morning Shift
                    </button>

                    <button type="button"
                            class="btn btn-outline-primary shift-btn {{ ($shift ?? '') == 'N' ? 'active' : '' }}"
                            data-value="N">
                        Noon Shift
                    </button>

                    <button type="submit" id="btnLoad" class="btn btn-primary" disabled>Load</button>

                    @if(($shift ?? '') !== '')
                       <button type="button" id="btnPdf" class="btn btn-danger">PDF</button>
                       <button type="button" id="btnExcel" class="btn btn-success">Excel</button>
                    @endif
                </form>

                <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <strong>Shift:</strong> {{ ($shift ?? '') == 'A' ? 'Morning' : (($shift ?? '') == 'N' ? 'Noon' : '') }}
                </div>

                <div class="table-responsive">
                    <table id="monitoringTable" class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>ParticipantId</th>
                                <th>LastName</th>
                                <th>FirstName</th>
                                <th>Blood Sugar</th>
                                <th>Blood Pressure</th>
                                <th>Weight</th>
                                <th>Others</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td>{{ $row->ParticipantId }}</td>
                                    <td>{{ $row->LastName }}</td>
                                    <td>{{ $row->FirstName }}</td>
                                    <td>{{ $row->bloodsugar }}</td>
                                    <td>{{ $row->bloodpressure }}</td>
                                    <td>{{ $row->weight }}</td>
                                    <td>{{ $row->others }}</td>
                                </tr>
                            @empty
                            <tr>
                                <td class="text-center">No records found.</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const shiftEl = document.getElementById('shift');
    const btnLoad = document.getElementById('btnLoad');
    const dtSearchEl = document.getElementById('dt_search');
    const dtOrderColEl = document.getElementById('dt_order_col');
    const dtOrderDirEl = document.getElementById('dt_order_dir');

    let table = null;

    function toggleLoadButton() {
        btnLoad.disabled = !(shiftEl.value.trim());
    }

    function updateExportState() {
        if (!table) return;

        const searchValue = table.search();
        const order = table.order();

        dtSearchEl.value = searchValue || '';

        if (order.length > 0) {
            dtOrderColEl.value = order[0][0];
            dtOrderDirEl.value = order[0][1];
        } else {
            dtOrderColEl.value = '';
            dtOrderDirEl.value = '';
        }
    }

    function buildExportUrl(baseUrl) {
        updateExportState();

        const params = new URLSearchParams();
        params.set('shift', shiftEl.value || '');
        params.set('dt_search', dtSearchEl.value || '');
        params.set('dt_order_col', dtOrderColEl.value || '');
        params.set('dt_order_dir', dtOrderDirEl.value || '');

        return baseUrl + '?' + params.toString();
    }

    document.querySelectorAll('.shift-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            shiftEl.value = this.dataset.value;
            document.querySelectorAll('.shift-btn').forEach(x => x.classList.remove('active'));
            this.classList.add('active');
            toggleLoadButton();
        });
    });

    toggleLoadButton();

    @if(($shift ?? '') !== '' && isset($rows) && count($rows) > 0)
        table = $('#monitoringTable').DataTable({
            pageLength: 25,
            order: [
                [{{ request('dt_order_col', 1) }}, '{{ request('dt_order_dir', 'asc') }}']
            ]
        });

        @if(request('dt_search'))
            table.search(@json(request('dt_search'))).draw();
        @endif

        table.on('search.dt order.dt', function () {
            updateExportState();
        });

        updateExportState();
    @endif

    const btnPdf = document.getElementById('btnPdf');
    if (btnPdf) {
        btnPdf.addEventListener('click', function () {
            window.open(buildExportUrl(@json(route('nursing.monitoring_report.pdf'))), '_blank');
        });
    }

    const btnExcel = document.getElementById('btnExcel');
    if (btnExcel) {
        btnExcel.addEventListener('click', function () {
            window.location.href = buildExportUrl(@json(route('nursing.monitoring_report.excel')));
        });
    }
});
</script>
@endsection



