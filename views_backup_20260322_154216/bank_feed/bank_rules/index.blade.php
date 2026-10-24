@extends('admin.admin_dashboard')
@section('title', 'Bank Feed Rules')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Bank Rules Maintenance</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <a href="{{ route('accounts.dashboard') }}#messages1" class="btn btn-primary waves-effect waves-light">{{ t('common.back','Back') }}</a>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        @if(session('message'))
            <div class="alert alert-success alert-sm">
                {{ session('message') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-sm">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filter + Add Form --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Add New Rule</strong>

                <form method="GET" action="{{ route('bank-rules.index') }}" class="d-flex align-items-center gap-2">
                    <label class="small mb-0">Filter by Bank:</label>
                    <select name="bank_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Banks</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->account_id }}"
                                {{ (string)($filterBankId ?? '') === (string)$bank->account_id ? 'selected' : '' }}>
                                {{ $bank->account_name }} ({{ $bank->account_id }})
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="card-body">
                <form action="{{ route('bank-rules.store') }}" method="POST" class="row g-3">
                    @csrf

                    {{-- ROW 1 --}}
                    <div class="col-md-3">
                        <label class="form-label">Bank</label>
                        <select name="bank_id" class="form-select form-select-sm">
                            <option value="">All Banks (generic rule)</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->account_id }}"
                                    {{ old('bank_id') == $bank->account_id ? 'selected' : '' }}>
                                    {{ $bank->account_name }} ({{ $bank->account_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Pattern Type</label>
                        <select name="pattern_type" class="form-select form-select-sm">
                            @php $pt = old('pattern_type', 'contains'); @endphp
                            <option value="starts_with" {{ $pt=='starts_with'?'selected':'' }}>Starts with</option>
                            <option value="contains"    {{ $pt=='contains'?'selected':'' }}>Contains</option>
                            <option value="ends_with"   {{ $pt=='ends_with'?'selected':'' }}>Ends with</option>
                            <option value="equals"      {{ $pt=='equals'?'selected':'' }}>Equals</option>
                            <option value="regex"       {{ $pt=='regex'?'selected':'' }}>Regex</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Pattern Value</label>
                        <input type="text"
                            name="pattern_value"
                            class="form-control form-control-sm"
                            value="{{ old('pattern_value') }}"
                            required>
                        <small class="text-muted d-block">Regex example: <code>/ACH\s+PAYMENT/i</code></small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Bank Category (optional)</label>
                        <input type="text"
                            name="bank_category"
                            class="form-control form-control-sm"
                            placeholder="e.g. Groceries, Shopping"
                            value="{{ old('bank_category') }}">
                        <small class="text-muted d-block">Matches CSV category.</small>
                    </div>

                    {{-- ROW 2 --}}
                    <div class="col-md-3">
                        <label class="form-label">Direction</label>
                        @php $dir = old('direction', 'Any'); @endphp
                        <select name="direction" class="form-select form-select-sm">
                            <option value="Any" {{ $dir=='Any'?'selected':'' }}>Any</option>
                            <option value="D"   {{ $dir=='D'?'selected':'' }}>Debit</option>
                            <option value="C"   {{ $dir=='C'?'selected':'' }}>Credit</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Auto Party Code</label>
                        <select name="party_cd" class="form-select form-select-sm">
                            <option value="">(no auto party code)</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->party_cd }}"
                                    {{ old('party_cd')==$acc->party_cd?'selected':'' }}>
                                    {{ $acc->party_cd }} - {{ $acc->party_nm }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block">Suggested on match.</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Priority</label>
                        <input type="number"
                            name="priority"
                            class="form-control form-control-sm"
                            value="{{ old('priority', 100) }}">
                        <small class="text-muted d-block">Lower = higher priority.</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Active?</label>
                        <div class="form-check mt-1">
                            <input class="form-check-input"
                                type="checkbox"
                                value="1"
                                name="is_active"
                                id="is_active"
                                {{ old('is_active', 1) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="is_active">Rule is active</label>
                        </div>
                    </div>

                    {{-- ROW 3 (Button Row) --}}
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Save New Rule
                        </button>
                    </div>
                </form>

            </div>
        </div>

        {{-- Rules list --}}
        <div class="card">
            <div class="card-header">
                <strong>Existing Rules</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Bank</th>
                                <th>Pattern</th>
                                <th>Category</th> {{-- NEW --}}
                                <th>Direction</th>
                                <th>Party Code</th>
                                <th>Priority</th>
                                <th>Active</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($rules as $rule)
                            <tr>
                                <td>{{ $rule->id }}</td>
                                <td>
                                    @if($rule->bank_id)
                                        {{ $rule->bank_name ?? 'Bank ' . $rule->bank_id }}<br>
                                        <small class="text-muted">{{ $rule->bank_id }}</small>
                                    @else
                                        <span class="badge bg-info text-dark">All Banks</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ strtoupper($rule->pattern_type) }}</strong><br>
                                    <code>{{ $rule->pattern_value }}</code>
                                </td>
                                {{-- NEW: Category column --}}
                                <td>
                                    @if($rule->bank_category)
                                        <span class="badge bg-light text-dark">
                                            {{ $rule->bank_category }}
                                        </span>
                                    @else
                                        <span class="text-muted">Any</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rule->direction === 'D')
                                        Debit
                                    @elseif($rule->direction === 'C')
                                        Credit
                                    @else
                                        Any
                                    @endif
                                </td>
                                <td>
                                    @if($rule->party_cd)
                                        {{ $rule->party_cd }}
                                        @if($rule->party_name)
                                            – <small>{{ $rule->party_name }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </td>
                                <td>{{ $rule->priority }}</td>
                                <td>
                                    @if($rule->is_active)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $rule->created_at }}</td>
                                <td>{{ $rule->updated_at }}</td>
                                <td>
                                    <a href="{{ route('bank-rules.edit', $rule->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No rules defined yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-3 py-2 border-top">
                    {{ $rules->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
