@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <div class="col d-flex gap-2 flex-wrap align-items-end">
            <h4 class="mb-3">Journal Entry</h4>
            <div class="col-auto ms-auto mb-3">
                <a href="{{ route('accounts.dashboard') }}" class="btn btn-primary no-print">
                    {{ t('common.back','Back') }}
                </a>
            </div>
        </div>
        <form action="{{ route('journal_entries.store') }}" method="POST" id="journalEntryForm">
        @csrf
        <div class="row mb-3">
            <div class="col-md-2">
                <label for="journal_type" class="form-label">Journal Type</label>
                <select class="form-select" id="trans_type" name="trans_type">
                    <option value="REGU">Regular</option>
                    <option value="ADJU">Adjustment</option>
                     <option value="BBAL">Begining Balance</option>
                    <!-- Add more as needed -->
                </select>
            </div>
            <div class="col-md-2">
                <label for="journal_no" class="form-label">Journal #</label>
                <input type="text" class="form-control" id="trans_no" name="trans_no"
                 value="{{ old('trans_no', session('fagltrjournal_data.trans_no', $nextid )) }}" required>
            </div>
            <div class="col-md-2">
                <label for="journal_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="trans_dt" name="trans_dt" required>
            </div>
            <div class="col-md-2">
                <label for="trans_flag" class="form-label">Status</label>
                <select class="form-select" id="trans_flag" name="trans_flag">
                    <option value="A">Active</option>
                    <option value="H">Hold</option>
                    <option value="D">Delete</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="post_status" class="form-label">Post Status</label>
                <select class="form-select" id="post_flag" name="post_flag">
                    <option value="U">Unposted</option>
                    <option value="P">Posted</option>
                </select>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab">Description</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="ref-tab" data-bs-toggle="tab" data-bs-target="#ref" type="button" role="tab">Reference</button>
          </li>
        </ul>
        <div class="tab-content mb-3" id="myTabContent">
            <div class="tab-pane fade show active" id="desc" role="tabpanel">
                <!-- Optional: Add a textarea or any field for description -->
                <!--
                <textarea class="form-control" name="description" rows="2" placeholder="Description"></textarea>
                -->
            </div>
            <div class="tab-pane fade" id="ref" role="tabpanel">
                <!-- Optional: Add reference fields here -->
            </div>
        </div>

        <!-- Journal Entry Lines Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle" id="entryLinesTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Account</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-center">Description</th>
                        <th>
                            <button type="button" class="btn btn-sm btn-success" onclick="addRow()">+</button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Initial Row -->
                    <tr>
                        <td>
                            <select class="form-select form-select-sm" name="lines[0][account_id]" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->party_cd }}">{{ $account->party_nm }}</option>
                                @endforeach

                            </select>
                        </td>
                        <td><input type="text" name="lines[0][debit_amt]" class="form-control form-control-sm debit text-end"  value="0.00"></td>
                        <td><input type="text" name="lines[0][credit_amt]" class="form-control form-control-sm credit text-end"  value="0.00"></td>
                        <td><input type="text" name="lines[0][description]" class="form-control form-control-sm"></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">-</button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total / Difference</th>
                        <th><input type="text" id="totalDebit" class="form-control form-control-sm text-end" readonly></th>
                        <th><input type="text" id="totalCredit" class="form-control form-control-sm text-end" readonly></th>
                        <th><input type="text" id="totalDifference" class="form-control form-control-sm text-end" readonly></th>
                        <th colspan="1"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Save Journal Entry</button>
    </form>
    </div>
</div>
@endsection
<!-- Add simple JavaScript for dynamic rows & totals -->

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
}
function removeRow(btn) {
    let table = document.getElementById('entryLinesTable').getElementsByTagName('tbody')[0];
    if (table.rows.length > 1) {
        btn.closest('tr').remove();
        updateTotals();
    }
}
function updateTotals() {
    let debits = document.querySelectorAll('.debit');
    let credits = document.querySelectorAll('.credit');


    let totalDebit = 0, totalCredit = 0;
    debits.forEach(d => totalDebit += parseFloat(d.value) || 0);
    credits.forEach(c => totalCredit += parseFloat(c.value) || 0);
    document.getElementById('totalDebit').value = totalDebit.toFixed(2);
    document.getElementById('totalCredit').value = totalCredit.toFixed(2);
    document.getElementById('totalDifference').value = (totalDebit - totalCredit).toFixed(2);

}
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('debit') || e.target.classList.contains('credit')) {
        updateTotals();
    }
});
window.onload = updateTotals;

document.addEventListener('DOMContentLoaded', function() {
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

