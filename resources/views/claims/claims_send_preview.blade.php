@extends('admin.admin_dashboard')
@section('title', 'Send Claims / Generate 837P')

@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Ready Claims – Send / Generate 837P</h5>
                <div>
                    <a href="{{ route('claims.draft.index', ['filter' => 'valid']) }}"
                       class="btn btn-secondary">
                        Back to Draft Claims
                    </a>
                </div>
            </div>
            {{-- LINKS TO LAST GENERATED FILES --}}
            @if(session('pdf_file') || session('edi_file'))
                <div class="alert alert-secondary mt-3">
                    <div class="d-flex flex-wrap gap-2 align-items-center">

                        @if(session('pdf_file'))
                            <a href="{{ asset('storage/claims_batches/' . session('pdf_file')) }}"
                            target="_blank"
                            class="btn btn-sm btn-outline-primary">
                                📄 Open PDF Summary
                            </a>
                        @endif

                        @if(session('edi_file'))
                            <a href="{{ asset('storage/claims_batches/' . session('edi_file')) }}"
                            download
                            class="btn btn-sm btn-outline-secondary">
                                📥 Download 837P File (.txt)
                            </a>
                        @endif

                    </div>

                    <small class="text-muted">
                        Links shown for the most recent generated batch.
                    </small>
                </div>
            @endif

            <div class="card-body">

                <p>
                    These claims have:
                    <ul>
                        <li><strong>status = ready</strong></li>
                        <li><strong>precheck_result = valid</strong></li>
                        <li><strong>claim_no is NULL</strong> (not yet sent)</li>
                    </ul>
                </p>

                <div class="alert alert-info">
                    <strong>Summary:</strong>
                    {{ $totalClaims }} claim(s), Grand total amount:
                    <strong>${{ number_format($grandTotalAmount, 2) }}</strong>
                </div>

                <form action="{{ route('claims.send.generate') }}" method="POST" id="sendClaimsForm">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-8 col-lg-6">

                            <label for="edi_filename" class="form-label">
                                <strong>837P File Name</strong>
                            </label>

                            <div class="input-group">
                                <input type="text"
                                    name="edi_filename"
                                    id="edi_filename"
                                    class="form-control"
                                    value="{{ old('edi_filename', $suggestedFilename ?? '') }}"
                                    placeholder="Morning_08.31.26_to_09.04.26">

                                <span class="input-group-text">.txt</span>
                            </div>

                            <small class="text-muted">
                                You may change the name before generating the batch.
                                Example: Morning_08.31.26_to_09.04.26
                            </small>

                        </div>
                    </div>

                    @php
                        $grouped = $claims->groupBy('insurance_name');

                    @endphp

                    @foreach($grouped as $insuranceName => $group)
                        @php
                            $insName = $insuranceName ?: 'Unknown Insurance';
                            $count = $group->count();
                            $sum   = $group->sum('total_amount');
                        @endphp

                        <div class="card mb-3 border-primary">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $insName }}</strong>
                                    <span class="badge bg-primary ms-2">{{ $count }} claim(s)</span>
                                </div>
                                <div>
                                    Total: <strong>${{ number_format($sum, 2) }}</strong>
                                </div>
                            </div>

                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 30px;">
                                                    <input type="checkbox" class="select-insurance-group"
                                                        data-insurance="{{ md5($insName) }}">
                                                </th>
                                                <th>Claim ID</th>
                                                <th>Participant</th>
                                                <th>Member Name</th>
                                                <th>Service From</th>
                                                <th>Service To</th>
                                                <th>Units</th>
                                                <th>Total Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($group as $claim)
                                                @php
                                                    $groupKey = md5($insName);
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <input type="checkbox"
                                                               name="selected_claims[]"
                                                               value="{{ $claim->id }}"
                                                               class="claim-checkbox group-{{ $groupKey }}"
                                                               checked>
                                                    </td>
                                                    <td>{{ $claim->id }}</td>
                                                    <td>{{ $claim->ParticipantId }}</td>
                                                    <td>
                                                        {{ trim($claim->member_lastname . ', ' . $claim->member_firstname, ', ') }}
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($claim->service_from)->format('m/d/Y') }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($claim->service_to)->format('m/d/Y') }}</td>
                                                    <td>{{ $claim->units }}</td>
                                                    <td>${{ number_format($claim->total_amount, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!--
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-outline-secondary"
                                onclick="window.location='{{ route('claims.draft.index', ['filter' => 'valid']) }}'">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            Generate 837P &amp; Mark as Sent
                        </button>
                    </div>
                    -->

                    <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary"
                            onclick="window.location='{{ route('claims.draft.index', ['filter' => 'valid']) }}'">
                        Cancel
                    </button>

                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        {{-- Existing button – keeps current behavior
                        <button type="submit"
                                class="btn btn-success"
                                id="btnGenerate837">
                            Generate 837P &amp; Mark as Sent
                        </button>

                        {{-- NEW button – will call a different route to generate 837P + PDF 
                        <button type="button"
                                class="btn btn-primary"
                                id="btnGenerate837Pdf">
                            Generate 837P + PDF &amp; Mark as Sent
                        </button> --}}

                        {{-- NEW button – will call a different route to generate 837P + PDF from Table--}}
                        <button type="button"
                                class="btn btn-success"
                                id="btnGenerate837PdfDb">
                            Generate 837P + PDF &amp; Mark as Sent DB
                        </button>

                    </div>
                </div>

                </form>

            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    // group checkbox logic stays as you already had it
    document.querySelectorAll('.select-insurance-group').forEach(function (chk) {
        chk.addEventListener('change', function () {
            const key = this.dataset.insurance;
            const checked = this.checked;
            document.querySelectorAll('.group-' + key).forEach(function (c) {
                c.checked = checked;
            });
        });
    });

    const form = document.getElementById('sendClaimsForm');

    function validateAndConfirm(message) {
        const anyChecked = !!document.querySelector('.claim-checkbox:checked');
        if (!anyChecked) {
            alert('Please select at least one claim to send.');
            return false;
        }
        return confirm(message);
    }

    // Green button → old route, old behavior
    const btnGenerate837 = document.getElementById('btnGenerate837');
    if (btnGenerate837) {
        btnGenerate837.addEventListener('click', function (e) {
            if (!validateAndConfirm('Generate 837P and mark selected claims as SENT?')) {
                e.preventDefault();
            }
        });
    }

    // Blue button → new route, new controller method
    const btnGenerate837Pdf = document.getElementById('btnGenerate837Pdf');
    if (btnGenerate837Pdf) {
        btnGenerate837Pdf.addEventListener('click', function (e) {
            e.preventDefault();

            if (!validateAndConfirm('Generate 837P + PDF and mark selected claims as SENT?')) {
                return;
            }

            // For this click only, switch to the new route
            form.action = "{{ route('claims.send.generatePdf') }}";
            form.submit();
        });
    }

    // Blue button → new route, new controller method
    const btnGenerate837PdfDb = document.getElementById('btnGenerate837PdfDb');

    if (btnGenerate837PdfDb) {

        btnGenerate837PdfDb.addEventListener('click', function (e) {

            e.preventDefault();

            const filename =
                document.getElementById('edi_filename').value.trim();

            if (filename === '') {
                alert('Please enter an 837P file name.');
                document.getElementById('edi_filename').focus();
                return;
            }

            if (!validateAndConfirm(
                'Generate 837P + PDF using file name "' +
                filename +
                '" and mark selected claims as SENT?'
            )) {
                return;
            }

            form.action =
                "{{ route('claims.send.generatePdf.db') }}";

            form.submit();
        });
    }


    // IMPORTANT: do NOT keep a generic form.addEventListener('submit', ...) here,
    // validation is now per button.
});
</script>
@endsection
