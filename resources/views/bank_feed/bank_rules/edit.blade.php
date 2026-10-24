@extends('admin.admin_dashboard')
@section('title', 'Bank Feed Rules')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <h3 class="mb-3">Edit Bank Rule #{{ $rule->id }}</h3>

        @if($errors->any())
            <div class="alert alert-danger alert-sm">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-3">
            <a href="{{ route('bank-rules.index') }}" class="btn btn-secondary btn-sm">&laquo; Back to Rules</a>
        </div>

        <div class="card">
            <div class="card-header">
                <strong>Rule Details</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('bank-rules.update', $rule->id) }}" method="POST" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-md-4">
                        <label class="form-label">Bank</label>
                        <select name="bank_id" class="form-select form-select-sm">
                            <option value="">All Banks (generic rule)</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->account_id }}"
                                    {{ (old('bank_id', $rule->bank_id) == $bank->account_id) ? 'selected' : '' }}>
                                    {{ $bank->account_name }} ({{ $bank->account_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Pattern Type</label>
                        @php $pt = old('pattern_type', $rule->pattern_type); @endphp
                        <select name="pattern_type" class="form-select form-select-sm" required>
                            <option value="starts_with" {{ $pt == 'starts_with' ? 'selected' : '' }}>Starts with</option>
                            <option value="contains" {{ $pt == 'contains' ? 'selected' : '' }}>Contains</option>
                            <option value="ends_with" {{ $pt == 'ends_with' ? 'selected' : '' }}>Ends with</option>
                            <option value="equals" {{ $pt == 'equals' ? 'selected' : '' }}>Equals</option>
                            <option value="regex" {{ $pt == 'regex' ? 'selected' : '' }}>Regex</option>
                        </select>
                    </div>

                    {{-- Pattern Value + Bank Category aligned side-by-side --}}
                    <div class="col-md-6">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Pattern Value</label>
                                <input type="text"
                                    name="pattern_value"
                                    value="{{ old('pattern_value', $rule->pattern_value) }}"
                                    class="form-control form-control-sm"
                                    required>
                                <small class="text-muted">
                                    For regex, use PHP syntax like <code>/ACH\s+PAYMENT/i</code>.
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Bank Category (optional)</label>
                                <input type="text"
                                    name="bank_category"
                                    value="{{ old('bank_category',  $rule->bank_category) }}"
                                    class="form-control form-control-sm"
                                    placeholder="e.g. Groceries, Shopping">
                                <small class="text-muted">
                                    If set, rule only applies when bank's CSV category matches this.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Direction</label>
                        @php $dir = old('direction', $rule->direction); @endphp
                        <select name="direction" class="form-select form-select-sm" required>
                            <option value="Any"  {{ $dir == 'Any' ? 'selected' : '' }}>Any</option>
                            <option value="D"    {{ $dir == 'D' ? 'selected' : '' }}>Debit</option>
                            <option value="C"    {{ $dir == 'C' ? 'selected' : '' }}>Credit</option>
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Auto Party Code</label>
                        <select name="party_cd" class="form-select form-select-sm">
                            <option value="">(no auto party code)</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->party_cd }}"
                                    {{ old('party_cd', $rule->party_cd) == $acc->party_cd ? 'selected' : '' }}>
                                    {{ $acc->party_cd }} - {{ $acc->party_nm }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            If set, this party code will be suggested on matches.
                        </small>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Priority</label>
                        <input type="number"
                            name="priority"
                            value="{{ old('priority', $rule->priority) }}"
                            class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Active?</label>
                        <div class="form-check mt-1">
                            <input class="form-check-input"
                                type="checkbox"
                                value="1"
                                id="is_active"
                                name="is_active"
                                {{ old('is_active', $rule->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="is_active">
                                Rule is active
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Update Rule
                        </button>
                        <a href="{{ route('bank-rules.index') }}" class="btn btn-secondary btn-sm">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
