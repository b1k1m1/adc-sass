@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
<style>
    tfoot th, tfoot input { font-weight: bold; background: #f8f9fa; }
</style>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Edit Journal Entry </h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('journal.list') }}" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
    <form id="journalEntryForm" action="{{ route('journal.update', [$header->trans_no, $header->trans_dt]) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="trans_no" value="{{ $header->trans_no }}">
    <input type="hidden" name="trans_dt" value="{{ $header->trans_dt }}">
    <div class="row mb-3">
            <div class="col-md-2">
                <label for="journal_type" class="form-label">Journal Type</label>
                <select class="form-select" id="trans_type" name="trans_type">
                    <option value="REGU" {{ old('trans_type', trim($header->trans_type)) === 'REGU' ? 'selected' : '' }}>Regular</option>
                    <option value="ADJU" {{ old('trans_type', trim($header->trans_type)) === 'ADJU' ? 'selected' : '' }}>Adjustment</option>
                    <option value="BBAL" {{ old('trans_type', trim($header->trans_type)) === 'BBAL' ? 'selected' : '' }}>Begining Balance</option>
                    <!-- Add more as needed -->
                </select>
            </div>
            <div class="col-md-2">
                <label for="journal_no" class="form-label">Journal #</label>
                <input type="text" class="form-control" id="trans_no" name="trans_no"
                 value="{{$header->trans_no }}" readonly>
            </div>
            <div class="col-md-2">
                <label for="journal_date" class="form-label">Date</label>
                <input type="string" class="form-control" id="trans_dt" name="trans_dt"
                value="{{ $header->trans_dt ? \Carbon\Carbon::parse($header->trans_dt)->format('m/d/Y') :'' }}" readonly>

            </div>
            <div class="col-md-2">
                <label for="trans_flag" class="form-label">Status</label>
                <select class="form-select" id="trans_flag" name="trans_flag">
                    <option value="A" {{ old('trans_flag', trim($header->trans_flag)) === 'A' ? 'selected' : '' }}>Active</option>
                    <option value="H" {{ old('trans_flag', trim($header->trans_flag)) === 'H' ? 'selected' : '' }}>Hold</option>
                    <option value="D" {{ old('trans_flag', trim($header->trans_flag)) === 'D' ? 'selected' : '' }}>Delete</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="post_status" class="form-label">Post Status</label>
                <select class="form-select" id="post_flag" name="post_flag">
                    <option value="U" {{ old('post_flag', trim($header->post_flag)) === 'U' ? 'selected' : '' }}>Unposted</option>
                    <option value="P" {{ old('post_flag', trim($header->post_flag)) === 'p' ? 'selected' : '' }}>Posted</option>
                </select>
            </div>
        </div>
    <!-- Journal Entry Lines Table -->
    <div class="table-responsive">
    <table class="table table-sm table-bordered table-sm align-middle" id="entryLinesTable">
        <thead class="table-light">
            <tr>
                <th class="text-center">Account</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-center">Description</th>
                <th>
                    <button type="button" class="btn btn-success btn-sm" onclick="addRow()">+</button>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($journalLines as $i => $line)
            <tr>
                <td>
                    <select class="form-select form-select-sm" name="lines[{{ $i }}][account_id]" required>
                        <option value="">Select</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->party_cd }}" {{ $line->account_id == $account->party_cd ? 'selected' : '' }}>
                                {{ $account->party_nm }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm debit text-end" name="lines[{{ $i }}][debit_amt]" value="{{ number_format($line->debit_amt, 2) }}">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm credit text-end" name="lines[{{ $i }}][credit_amt]" value="{{ number_format($line->credit_amt, 2) }}">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="lines[{{ $i }}][description]" value="{{ $line->description }}">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">-</button>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th>Total /Difference </th>
                <th><input type="text" id="totalDebit" class="form-control form-control-sm text-end" readonly></th>
                <th><input type="text" id="totalCredit" class="form-control form-control-sm text-end" readonly></th>
                <th><input type="text" id="totalDifference" class="form-control form-control-sm text-end" readonly></th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>
    </div>
    <button type="submit" class="btn btn-primary">Update Journal Entry</button>
</form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let rowIndex = 1;
function addRow() {
    let table = document.getElementById('entryLinesTable').getElementsByTagName('tbody')[0];
    let newRow = table.rows[0].cloneNode(true);
    // Clear values
    Array.from(newRow.querySelectorAll('input, select')).forEach(el => {
        if (el.type === 'number') el.value = "0.00";
        else el.value = "";
    });
    // Update name attributes
    newRow.querySelectorAll('input, select').forEach(el => {
        el.name = el.name.replace(/\d+/, rowIndex);
    });
    table.appendChild(newRow);
    rowIndex++;
    updateTotals();
    reindexRows();
}
function removeRow(btn) {
    let table = document.getElementById('entryLinesTable').getElementsByTagName('tbody')[0];
    if (table.rows.length > 1) {
        btn.closest('tr').remove();
        updateTotals();
        reindexRows();
    }
}
function updateTotals() {
    let debits = document.querySelectorAll('.debit');
    let credits = document.querySelectorAll('.credit');
    let totalDebit = 0, totalCredit = 0;
    debits.forEach(d => totalDebit += parseFloat(d.value.replace(/,/g, '')) || 0);
    credits.forEach(c => totalCredit += parseFloat(c.value.replace(/,/g, '')) || 0);
    document.getElementById('totalDebit').value = totalDebit.toFixed(2);
    document.getElementById('totalCredit').value = totalCredit.toFixed(2);
    document.getElementById('totalDifference').value = (totalDebit - totalCredit).toFixed(2);
}

function reindexRows() {
    const rows = document.querySelectorAll('#entryLinesTable tbody tr');
    rows.forEach((row, i) => {
        row.querySelectorAll('input, select').forEach(input => {
            // Replace the row index in the name with the current row index
            input.name = input.name.replace(/lines\[\d+\]/, 'lines[' + i + ']');
        });
    });
}
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('debit') || e.target.classList.contains('credit')) {
        updateTotals();
    }
});
window.onload = updateTotals;

document.addEventListener('DOMContentLoaded', function() {
    updateTotals();
    function formatAmountInput(el) {
        let val = el.value.replace(/,/g, '').trim();
        if (val === '') {
            el.value = '0.00';
        } else if (!isNaN(val)) {
            el.value = parseFloat(val).toFixed(2);
        }
    }

    document.body.addEventListener('blur', function(e) {
        if (e.target.classList.contains('debit') || e.target.classList.contains('credit')) {
            formatAmountInput(e.target);
        }
    }, true);

    const form = document.getElementById('journalEntryForm'); // Use your <form id="">
    if (form) {
        form.addEventListener('submit', function(e) {
            let totalDebit = parseFloat(document.getElementById('totalDebit').value) || 0;
            let totalCredit = parseFloat(document.getElementById('totalCredit').value) || 0;
            // Use toFixed for decimal comparison
            if (totalDebit.toFixed(2) !== totalCredit.toFixed(2)) {
                e.preventDefault();
                Swal.fire({
                icon: 'error',
                title: 'Unbalanced Entry',
                text: 'Total Debit and Total Credit must match before saving!',
                confirmButtonColor: '#3085d6'
                });
            }
        });
    }
});
</script>
@endsection
