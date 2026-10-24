@extends('admin.admin_dashboard')
@section('title', 'Claims')

@section('admin')

<style>
    /* Compact Table */
    table.check-claims-table th,
    table.check-claims-table td {
        padding: 4px 6px !important;
        font-size: 12px !important;
        white-space: nowrap;
    }

    table.check-claims-table td small {
        font-size: 11px;
    }

    /* Soft colors from precheck result */
    tr.valid-row   { background-color: #d4f3d4 !important; }
    tr.invalid-row { background-color: #f8d0d0 !important; }
</style>

<div class="page-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Draft Claims (Not yet assigned claim number)</h5>

                <div class="d-flex gap-2">
                    {{-- Check Claims --}}
                    <button id="btnCheckClaims" class="btn btn-warning">
                        Check Claims
                    </button>

                    {{-- Send / Generate 837P --}}
                    <a href="{{ route('claims.send.preview') }}" class="btn btn-success">
                        Send / Generate 837P
                    </a>
                </div>
            </div>

            <div class="card-body">

                {{-- Filter Tabs --}}
                <ul class="nav nav-pills mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ ($filter ?? 'all') === 'all' ? 'active' : '' }}"
                           href="{{ route('claims.draft.index', ['filter' => 'all']) }}">
                            All Drafts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ ($filter ?? '') === 'invalid' ? 'active' : '' }}"
                           href="{{ route('claims.draft.index', ['filter' => 'invalid']) }}">
                            Invalid Only
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ ($filter ?? '') === 'valid' ? 'active' : '' }}"
                           href="{{ route('claims.draft.index', ['filter' => 'valid']) }}">
                            Valid Only
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ ($filter ?? '') === 'not_checked' ? 'active' : '' }}"
                           href="{{ route('claims.draft.index', ['filter' => 'not_checked']) }}">
                            Not Checked
                        </a>
                    </li>
                </ul>

                @if($claims->isEmpty())
                    <p class="mb-0">No draft claims found.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered check-claims-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Claim #</th>
                                    <th>Insurance</th>
                                    <th>Participant</th>
                                    <th>Service From</th>
                                    <th>Service To</th>
                                    <th>Units</th>
                                    <th>Amount</th>
                                    <th>Total</th>
                                    <th>Claim Status</th>
                                    <th>Validation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($claims as $claim)
                                    <tr data-claim-id="{{ $claim->id }}"
                                        class="{{ $claim->precheck_result === 'valid' ? 'valid-row' : 'invalid-row' }}">
                                        <td>{{ $claim->id }}</td>
                                        <td>{{ $claim->claim_no }}</td>
                                        <td>{{ $claim->insurance_name }}</td>
                                        <td>{{ $claim->ParticipantId }}</td>
                                        <td>{{ $claim->service_from ? \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') : '' }}</td>
                                        <td>{{ $claim->service_to ? \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') : '' }}</td>
                                        <td class="text-center">{{ $claim->units }}</td>
                                        <td class="text-end">{{ number_format($claim->amount_per_unit, 2) }}</td>
                                        <td class="text-end">{{ number_format($claim->total_amount, 2) }}</td>

                                        {{-- Claim status cell used by JS --}}
                                        <td class="claim-status">
                                            <span class="badge bg-{{ $claim->status === 'ready' ? 'primary' : 'secondary' }}">
                                                {{ ucfirst($claim->status) }}
                                            </span>
                                        </td>

                                        {{-- Validation cell used by JS --}}
                                        <td class="validation-result">
                                            @if($claim->precheck_result)
                                                <span class="badge bg-{{ $claim->precheck_result === 'valid' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($claim->precheck_result) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Not Checked</span>
                                            @endif
                                            @if($claim->precheck_message)
                                                <br>
                                                <small>{{ $claim->precheck_message }}</small>
                                            @endif
                                        </td>

                                        <td class="text-nowrap">
                                            <a href="{{ route('claims.edit', $claim->id) }}"
                                               class="btn btn-sm btn-primary mb-1">
                                                Edit
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('claims.destroy', $claim->id) }}"
                                                  style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-danger mb-1">
                                                    Delete
                                                </button>
                                            </form>

                                            <a href="{{ route('claims.cms1500.dompdf', $claim->id) }}"
                                               class="btn btn-sm btn-outline-secondary">
                                                Preview CMS-1500
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Claim Validation Modal -->
        <div class="modal fade" id="claimsValidationModal" tabindex="-1"
             aria-labelledby="claimsValidationLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="claimsValidationLabel">Claim Validation Results</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Participant</th>
                                    <th>Svc From</th>
                                    <th>Svc To</th>
                                    <th>Units</th>
                                    <th>Amount</th>
                                    <th>Total</th>
                                    <th>Result</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody id="validation-results-body">
                                {{-- filled by JS --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ t('common.close', 'Close') }}</button>
                    </div>
                </div>
            </div>
        </div> <!-- /modal -->

    </div>
</div>
@endsection

{{-- JS --}}
@section('scripts')

<script>

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return (d.getMonth()+1).toString().padStart(2, '0') + '/' +
           d.getDate().toString().padStart(2, '0') + '/' +
           d.getFullYear();
}

document.addEventListener('DOMContentLoaded', function () {
    const btnCheck = document.getElementById('btnCheckClaims');
    if (!btnCheck) {
        console.error('btnCheckClaims not found in DOM');
        return;
    }

    const resultsBody = document.getElementById('validation-results-body');

    btnCheck.addEventListener('click', function () {
        if (!confirm('Run precheck on all draft claims (no claim number)?')) {
            return;
        }

        btnCheck.disabled = true;
        btnCheck.innerText = 'Checking...';

        fetch("{{ route('claims.check') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json",
                "Content-Type": "application/json",
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            btnCheck.disabled = false;
            btnCheck.innerText = 'Check Claims';

            if (!data.success) {
                if (window.toastr) {
                    toastr.warning(data.message || 'Error validating claims.');
                } else {
                    alert(data.message || 'Error validating claims.');
                }
                return;
            }

            const summary = data.summary || {};
            const total   = summary.total ?? data.results.length;
            const valid   = summary.valid ?? 0;
            const invalid = summary.invalid ?? 0;

            const summaryMsg = `Checked ${total} claim(s): ${valid} valid, ${invalid} invalid.`;
            if (window.toastr) {
                toastr.info(summaryMsg);
            } else {
                alert(summaryMsg);
            }

            if (resultsBody) {
                resultsBody.innerHTML = '';
            }

            (data.results || []).forEach(row => {
                // ----- Update main table row -----
                const trMain = document.querySelector(`tr[data-claim-id="${row.id}"]`);
                if (trMain) {
                    // Status column
                    const statusCell = trMain.querySelector('.claim-status');
                    if (statusCell && row.status) {
                        statusCell.innerHTML =
                            `<span class="badge bg-${row.status === 'ready' ? 'primary' : 'secondary'}">
                                ${row.status.charAt(0).toUpperCase() + row.status.slice(1)}
                             </span>`;
                    }

                    // Validation cell
                    const validationCell = trMain.querySelector('.validation-result');
                    if (validationCell) {
                        let badgeClass = 'bg-secondary';
                        let label = 'Not Checked';

                        if (row.result === 'valid') {
                            badgeClass = 'bg-success';
                            label = 'Valid';
                        } else if (row.result === 'invalid') {
                            badgeClass = 'bg-danger';
                            label = 'Invalid';
                        }

                        let html = `<span class="badge ${badgeClass}">${label}</span>`;
                        if (row.message && row.message !== 'OK') {
                            html += `<br><small>${row.message}</small>`;
                        }
                        validationCell.innerHTML = html;
                    }

                    // Row color
                    trMain.classList.remove('valid-row', 'invalid-row');
                    if (row.result === 'valid') {
                        trMain.classList.add('valid-row');
                    } else if (row.result === 'invalid') {
                        trMain.classList.add('invalid-row');
                    }
                }

                // ----- Fill modal row -----
                if (resultsBody) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.id}</td>
                        <td>${row.ParticipantId}</td>
                        <td>${formatDate(row.service_from)}</td>
                        <td>${formatDate(row.service_to)}</td>
                        <td>${row.units}</td>
                        <td>${row.amount_per_unit}</td>
                        <td>${row.total_amount}</td>
                        <td>
                            <span class="badge bg-${row.result === 'valid' ? 'success' : 'danger'}">
                                ${row.result === 'valid' ? 'Valid' : 'Invalid'}
                            </span>
                        </td>
                        <td>${row.message && row.message !== 'OK' ? row.message : ''}</td>
                    `;
                    resultsBody.appendChild(tr);
                }
            });

            // Show modal with detailed results
            const modalEl = document.getElementById('claimsValidationModal');
            if (modalEl && window.bootstrap) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }

        })
        .catch(err => {
            console.error(err);
            btnCheck.disabled = false;
            btnCheck.innerText = 'Check Claims';
            if (window.toastr) {
                toastr.error('Unexpected error while checking claims.');
            } else {
                alert('Unexpected error while checking claims.');
            }
        });
    });
});
</script>
@endsection



