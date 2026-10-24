@extends('admin.admin_dashboard')

@section('title', 'X12 837P Settings')

@section('admin')

@push('styles')
<style>
    .x12-tab-wrapper {
        padding: 10px;
        background: #f4f6f9;
        border: 1px solid #e3e8ef;
        border-radius: 12px;
    }

    .x12-tabs {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }

    .x12-tabs .nav-item {
        width: 100%;
    }

    .x12-tabs .nav-link {
        width: 100%;
        min-height: 74px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        border: 1px solid transparent;
        border-radius: 10px;
        background: #ffffff;
        color: #495057;
        text-align: left;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        transition: all 0.2s ease;
    }

    .x12-tabs .nav-link:hover {
        border-color: #cbd5e1;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
    }

    .x12-tabs .nav-link.active {
        background: #556ee6;
        color: #ffffff;
        border-color: #556ee6;
        box-shadow: 0 6px 14px rgba(85, 110, 230, 0.24);
    }

    .x12-tab-icon {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #eef2ff;
        color: #556ee6;
        font-size: 21px;
    }

    .x12-tabs .nav-link.active .x12-tab-icon {
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff;
    }

    .x12-tab-text {
        min-width: 0;
        display: flex;
        flex-direction: column;
        line-height: 1.15;
    }

    .x12-tab-text strong {
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .x12-tab-text small {
        margin-top: 5px;
        color: #7b8190;
        font-size: 11px;
        line-height: 1.25;
    }

    .x12-tabs .nav-link.active .x12-tab-text small {
        color: rgba(255, 255, 255, 0.8);
    }

    @media (max-width: 1199.98px) {
        .x12-tabs {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .x12-tabs {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush


<div class="page-content">
    <div class="container-fluid">

        {{-- Page title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">X12 837P Settings</h4>
                </div>
            </div>
        </div>

        {{-- Success message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"></button>
            </div>
        @endif

        {{-- Error message --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"></button>
            </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please correct the following errors:</strong>

                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">

                {{-- Professional tab navigation --}}
                <div class="x12-tab-wrapper mb-4">
                    <ul class="nav nav-pills x12-tabs"
                        id="x12SettingsTabs"
                        role="tablist">

                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'clearinghouse' ? 'active' : '' }}"
                                    id="clearinghouse-tab-button"
                                    data-bs-toggle="tab"
                                    data-bs-target="#clearinghouse-tab"
                                    type="button"
                                    role="tab">
                                <span class="x12-tab-icon">
                                    <i class="mdi mdi-bank-transfer"></i>
                                </span>
                                <span class="x12-tab-text">
                                    <strong>Clearinghouse</strong>
                                    <small>ISA, GS and submitter setup</small>
                                </span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'provider' ? 'active' : '' }}"
                                    id="provider-tab-button"
                                    data-bs-toggle="tab"
                                    data-bs-target="#provider-tab"
                                    type="button"
                                    role="tab">
                                <span class="x12-tab-icon">
                                    <i class="mdi mdi-hospital-building"></i>
                                </span>
                                <span class="x12-tab-text">
                                    <strong>Provider</strong>
                                    <small>NPI, taxonomy and address</small>
                                </span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'payer' ? 'active' : '' }}"
                                    id="payer-tab-button"
                                    data-bs-toggle="tab"
                                    data-bs-target="#payer-tab"
                                    type="button"
                                    role="tab">
                                <span class="x12-tab-icon">
                                    <i class="mdi mdi-shield-account"></i>
                                </span>
                                <span class="x12-tab-text">
                                    <strong>Payers</strong>
                                    <small>Insurance and payer IDs</small>
                                </span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'control' ? 'active' : '' }}"
                                    id="control-tab-button"
                                    data-bs-toggle="tab"
                                    data-bs-target="#control-tab"
                                    type="button"
                                    role="tab">
                                <span class="x12-tab-icon">
                                    <i class="mdi mdi-counter"></i>
                                </span>
                                <span class="x12-tab-text">
                                    <strong>Control Numbers</strong>
                                    <small>Automatic ISA and GS sequence</small>
                                </span>
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $activeTab === 'submission' ? 'active' : '' }}"
                                    id="submission-tab-button"
                                    data-bs-toggle="tab"
                                    data-bs-target="#submission-tab"
                                    type="button"
                                    role="tab">
                                <span class="x12-tab-icon">
                                    <i class="mdi mdi-file-document-check-outline"></i>
                                </span>
                                <span class="x12-tab-text">
                                    <strong>Submission History</strong>
                                    <small>Generated 837P batch files</small>
                                </span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content pt-3"
                     id="x12SettingsTabContent">

                    {{-- Clearinghouse tab --}}
                    <div class="tab-pane fade {{ $activeTab === 'clearinghouse' ? 'show active' : '' }}"
                         id="clearinghouse-tab"
                         role="tabpanel">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Clearinghouse Settings</h5>

                            <button type="button"
                                    class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#clearinghouseModal"
                                    onclick="openClearinghouseCreate()">
                                <i class="mdi mdi-plus me-1"></i>
                                Add Clearinghouse
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Setting Name</th>
                                    <th>Sender ID</th>
                                    <th>Receiver ID</th>
                                    <th>GS Sender</th>
                                    <th>GS Receiver</th>
                                    <th>Version</th>
                                    <th>Mode</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 155px;">Action</th>
                                </tr>
                                </thead>

                                <tbody>
                                @forelse($clearinghouses as $row)
                                    <tr>
                                        <td>
                                            {{ $row->setting_name }}

                                            @if((int) $row->is_default === 1)
                                                <span class="badge bg-primary ms-1">
                                                    Default
                                                </span>
                                            @endif
                                        </td>

                                        <td>{{ $row->isa_sender_id }}</td>
                                        <td>{{ $row->isa_receiver_id }}</td>
                                        <td>{{ $row->gs_sender_code }}</td>
                                        <td>{{ $row->gs_receiver_code }}</td>
                                        <td>{{ $row->implementation_version }}</td>

                                        <td>
                                            @if($row->isa_usage_indicator === 'P')
                                                <span class="badge bg-success">
                                                    Production
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    Test
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($row->status === 'A')
                                                <span class="badge bg-success">
                                                    Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-warning"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#clearinghouseModal"
                                                    onclick='openClearinghouseEdit(@json($row))'>
                                                <i class="mdi mdi-pencil"></i>
                                                Edit
                                            </button>

                                            <form method="POST"
                                                  action="{{ route('x12.settings.clearinghouse.destroy', $row->id) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this clearinghouse setting?');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="btn btn-sm btn-danger">
                                                    <i class="mdi mdi-delete"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9"
                                            class="text-center text-muted">
                                            No clearinghouse settings found.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Provider tab --}}
                    <div class="tab-pane fade {{ $activeTab === 'provider' ? 'show active' : '' }}"
                         id="provider-tab"
                         role="tabpanel">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Provider Settings</h5>

                            <button type="button"
                                    class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#providerModal"
                                    onclick="openProviderCreate()">
                                <i class="mdi mdi-plus me-1"></i>
                                Add Provider
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Provider Code</th>
                                    <th>Provider Name</th>
                                    <th>NPI</th>
                                    <th>Taxonomy</th>
                                    <th>Tax ID</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 155px;">Action</th>
                                </tr>
                                </thead>

                                <tbody>
                                @forelse($providers as $row)
                                    <tr>
                                        <td>{{ $row->provider_code }}</td>

                                        <td>
                                            {{ $row->provider_name }}

                                            @if((int) $row->is_default === 1)
                                                <span class="badge bg-primary ms-1">
                                                    Default
                                                </span>
                                            @endif
                                        </td>

                                        <td>{{ $row->npi }}</td>
                                        <td>{{ $row->taxonomy_code }}</td>
                                        <td>{{ $row->tax_id }}</td>

                                        <td>
                                            {{ $row->city }},
                                            {{ $row->state }}
                                            {{ $row->zip_code }}
                                        </td>

                                        <td>
                                            @if($row->status === 'A')
                                                <span class="badge bg-success">
                                                    Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-warning"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#providerModal"
                                                    onclick='openProviderEdit(@json($row))'>
                                                <i class="mdi mdi-pencil"></i>
                                                Edit
                                            </button>

                                            <form method="POST"
                                                  action="{{ route('x12.settings.provider.destroy', $row->id) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this provider setting?');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="btn btn-sm btn-danger">
                                                    <i class="mdi mdi-delete"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8"
                                            class="text-center text-muted">
                                            No provider settings found.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Payer tab --}}
                    <div class="tab-pane fade {{ $activeTab === 'payer' ? 'show active' : '' }}"
                         id="payer-tab"
                         role="tabpanel">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Insurance Payer Settings</h5>

                            <button type="button"
                                    class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#payerModal"
                                    onclick="openPayerCreate()">
                                <i class="mdi mdi-plus me-1"></i>
                                Add Payer
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Insurance / HMO</th>
                                    <th>Payer Code</th>
                                    <th>Payer Name</th>
                                    <th>Electronic Payer ID</th>
                                    <th>Filing Code</th>
                                    <th>POS</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 155px;">Action</th>
                                </tr>
                                </thead>

                                <tbody>
                                @forelse($payers as $row)
                                    <tr>
                                        <td>
                                            {{ $row->hmo_name ?? $row->hmo_id }}
                                        </td>

                                        <td>{{ $row->payer_code }}</td>
                                        <td>{{ $row->payer_name }}</td>
                                        <td>{{ $row->payer_identifier }}</td>
                                        <td>{{ $row->claim_filing_indicator_code }}</td>
                                        <td>{{ $row->place_of_service_code }}</td>

                                        <td>
                                            @if($row->status === 'A')
                                                <span class="badge bg-success">
                                                    Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-warning"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#payerModal"
                                                    onclick='openPayerEdit(@json($row))'>
                                                <i class="mdi mdi-pencil"></i>
                                                Edit
                                            </button>

                                            <form method="POST"
                                                  action="{{ route('x12.settings.payer.destroy', $row->id) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this payer setting?');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="btn btn-sm btn-danger">
                                                    <i class="mdi mdi-delete"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8"
                                            class="text-center text-muted">
                                            No payer settings found.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Control-number tab --}}
                    <div class="tab-pane fade {{ $activeTab === 'control' ? 'show active' : '' }}"
                         id="control-tab"
                         role="tabpanel">

                        <div class="alert alert-info">
                            ISA and GS control numbers are managed automatically when an
                            837P file is generated. They should not be manually edited.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Clearinghouse</th>
                                    <th>Next ISA Number</th>
                                    <th>Next GS Number</th>
                                    <th>Last Updated</th>
                                </tr>
                                </thead>

                                <tbody>
                                @forelse($controlNumbers as $row)
                                    <tr>
                                        <td>{{ $row->setting_name }}</td>

                                        <td>
                                            {{ str_pad(
                                                (string) $row->next_isa_number,
                                                9,
                                                '0',
                                                STR_PAD_LEFT
                                            ) }}
                                        </td>

                                        <td>{{ $row->next_gs_number }}</td>
                                        <td>{{ $row->updated_at }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="text-center text-muted">
                                            No control-number records found.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Submission-history tab --}}
                    <div class="tab-pane fade {{ $activeTab === 'submission' ? 'show active' : '' }}"
                         id="submission-tab"
                         role="tabpanel">

                        <div class="alert alert-info">
                            Submission history is created automatically when an
                            837P file is generated.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Generated At</th>
                                    <th>File Name</th>
                                    <th>Clearinghouse</th>
                                    <th>ISA Number</th>
                                    <th>GS Number</th>
                                    <th class="text-end">Claims</th>
                                    <th class="text-end">Total Amount</th>
                                    <th>Mode</th>
                                    <th>Status</th>
                                </tr>
                                </thead>

                                <tbody>
                                @forelse($submissions as $row)
                                    <tr>
                                        <td>{{ $row->generated_at }}</td>
                                        <td>{{ $row->file_name }}</td>
                                        <td>{{ $row->setting_name }}</td>
                                        <td>{{ $row->isa_control_number }}</td>
                                        <td>{{ $row->gs_control_number }}</td>
                                        <td class="text-end">{{ $row->claim_count }}</td>

                                        <td class="text-end">
                                            {{ number_format(
                                                (float) $row->total_charge_amount,
                                                2
                                            ) }}
                                        </td>

                                        <td>
                                            @if($row->usage_indicator === 'P')
                                                <span class="badge bg-success">
                                                    Production
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    Test
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge bg-info text-dark">
                                                {{ $row->submission_status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9"
                                            class="text-center text-muted">
                                            No submission history found.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal partials --}}
@include('claims.settings.x12.partials.clearinghouse-modal')
@include('claims.settings.x12.partials.provider-modal')
@include('claims.settings.x12.partials.payer-modal')
@endsection

@push('scripts')
<script>
    const clearinghouseStoreUrl =
        @json(route('x12.settings.clearinghouse.store'));

    const providerStoreUrl =
        @json(route('x12.settings.provider.store'));

    const payerStoreUrl =
        @json(route('x12.settings.payer.store'));

    const clearinghouseBaseUrl =
        @json(url('/settings/x12/clearinghouse'));

    const providerBaseUrl =
        @json(url('/settings/x12/provider'));

    const payerBaseUrl =
        @json(url('/settings/x12/payer'));

    /**
     * Populate either a normal field or a checkbox.
     */
    function setX12FormValue(form, name, value) {
        const element = form.elements[name];

        if (!element) {
            return;
        }

        if (element.type === 'checkbox') {
            element.checked =
                Number(value) === 1 ||
                value === true ||
                value === '1';

            return;
        }

        element.value = value ?? '';
    }

    /*
    |--------------------------------------------------------------------------
    | Clearinghouse Modal
    |--------------------------------------------------------------------------
    */

    function openClearinghouseCreate() {
        const form = document.getElementById('clearinghouseForm');

        form.reset();
        form.action = clearinghouseStoreUrl;

        document.getElementById('clearinghouseMethod').value = 'POST';
        document.getElementById('clearinghouseModalLabel').textContent =
            'Add Clearinghouse';

        const defaults = {
            isa_authorization_qualifier: '00',
            isa_authorization_information: '',
            isa_security_qualifier: '00',
            isa_security_information: '',
            isa_sender_qualifier: 'ZZ',
            isa_receiver_qualifier: 'ZZ',
            isa_repetition_separator: '^',
            isa_version: '00501',
            isa_ack_requested: '0',
            isa_usage_indicator: 'T',
            isa_component_separator: ':',

            gs_functional_code: 'HC',
            gs_responsible_agency_code: 'X',
            implementation_version: '005010X222A1',

            bht_structure_code: '0019',
            bht_purpose_code: '00',
            bht_transaction_type: 'CH',

            submitter_entity_type: '2',
            submitter_id_qualifier: '46',

            receiver_entity_type: '2',
            receiver_id_qualifier: '46',

            element_separator: '*',
            segment_terminator: '~',
            line_break_after_segment: 1,
            is_default: 0,
            status: 'A'
        };

        Object.entries(defaults).forEach(([name, value]) => {
            setX12FormValue(form, name, value);
        });
    }

    function openClearinghouseEdit(row) {
        const form = document.getElementById('clearinghouseForm');

        form.reset();
        form.action = `${clearinghouseBaseUrl}/${row.id}`;

        document.getElementById('clearinghouseMethod').value = 'PUT';
        document.getElementById('clearinghouseModalLabel').textContent =
            'Edit Clearinghouse';

        Object.entries(row).forEach(([name, value]) => {
            setX12FormValue(form, name, value);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Provider Modal
    |--------------------------------------------------------------------------
    */

    function openProviderCreate() {
        const form = document.getElementById('providerForm');

        form.reset();
        form.action = providerStoreUrl;

        document.getElementById('providerMethod').value = 'POST';
        document.getElementById('providerModalLabel').textContent =
            'Add Provider';

        const defaults = {
            entity_type_qualifier: '2',
            tax_id_qualifier: 'EI',
            use_as_billing_provider: 1,
            use_as_rendering_provider: 1,
            use_as_service_facility: 1,
            is_default: 0,
            status: 'A'
        };

        Object.entries(defaults).forEach(([name, value]) => {
            setX12FormValue(form, name, value);
        });
    }

    function openProviderEdit(row) {
        const form = document.getElementById('providerForm');

        form.reset();
        form.action = `${providerBaseUrl}/${row.id}`;

        document.getElementById('providerMethod').value = 'PUT';
        document.getElementById('providerModalLabel').textContent =
            'Edit Provider';

        Object.entries(row).forEach(([name, value]) => {
            setX12FormValue(form, name, value);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Payer Modal
    |--------------------------------------------------------------------------
    */

    function openPayerCreate() {
        const form = document.getElementById('payerForm');

        form.reset();
        form.action = payerStoreUrl;

        document.getElementById('payerMethod').value = 'POST';
        document.getElementById('payerModalLabel').textContent =
            'Add Payer';

        const defaults = {
            payer_identifier_qualifier: 'PI',
            claim_filing_indicator_code: 'MC',
            subscriber_relationship_code: '18',
            place_of_service_code: '99',
            claim_frequency_code: '1',
            provider_signature_indicator: 'Y',
            assignment_participation_code: 'A',
            benefits_assignment_indicator: 'Y',
            release_information_code: 'Y',
            include_payer_address: 1,
            include_payer_taxonomy_ref: 0,
            include_rendering_provider: 1,
            include_service_facility: 1,
            status: 'A'
        };

        Object.entries(defaults).forEach(([name, value]) => {
            setX12FormValue(form, name, value);
        });
    }

    function openPayerEdit(row) {
        const form = document.getElementById('payerForm');

        form.reset();
        form.action = `${payerBaseUrl}/${row.id}`;

        document.getElementById('payerMethod').value = 'PUT';
        document.getElementById('payerModalLabel').textContent =
            'Edit Payer';

        Object.entries(row).forEach(([name, value]) => {
            setX12FormValue(form, name, value);
        });
    }
</script>
@endpush
