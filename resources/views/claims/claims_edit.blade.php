@extends('admin.admin_dashboard')
@section('title', 'Claims')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5>Edit Claim #{{ $claim->id }}</h5>
            </div>

            <form method="POST" action="{{ route('claims.update', $claim->id) }}">
                @csrf

                <div class="card-body">
                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label">Participant ID</label>
                        <div class="col-sm-9">
                            <input type="text" name="ParticipantId" class="form-control"
                                value="{{ $claim->ParticipantId }}" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label">Service From</label>
                        <div class="col-sm-9">
                            <input type="date" name="service_from" class="form-control"
                                value="{{ $claim->service_from }}" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label">Service To</label>
                        <div class="col-sm-9">
                            <input type="date" name="service_to" class="form-control"
                                value="{{ $claim->service_to }}" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label">Units</label>
                        <div class="col-sm-9">
                            <input type="number" name="units" class="form-control"
                                value="{{ $claim->units }}" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label">Amount / Unit</label>
                        <div class="col-sm-9">
                            <input type="number" step="0.01" name="amount_per_unit" class="form-control"
                                value="{{ $claim->amount_per_unit }}" required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label">Diag1</label>
                        <div class="col-sm-9">
                            <select name="diag1"
                                    id="Diag1"
                                    class="form-control icd10-select">
                                @if(!empty($claim->diag1))
                                    <option value="{{ $claim->diag1 }}" selected>
                                        {{ $claim->diag1 }}
                                    </option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label">Diag2</label>
                        <div class="col-sm-9">
                            <select name="diag2"
                                    id="Diag2"
                                    class="form-control icd10-select">
                                @if(!empty($claim->diag2))
                                    <option value="{{ $claim->diag2 }}" selected>
                                        {{ $claim->diag2 }}
                                    </option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-end">
                    <a href="{{ route('claims.draft.index') }}" class="btn btn-secondary">{{ t('common.back','Back') }}</a>
                    <button class="btn btn-success">{{ t('common.save', 'Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {

    function initIcd10(selector) {

        $(selector).each(function () {

            const $el = $(this);

            if ($el.data('select2')) {
                return;
            }

            const $parent = $el
                .closest('.modal, .offcanvas, .card, .card-body, .page-content')
                .first();

            $el.select2({
                width: '100%',
                placeholder: 'Type ICD-10 code or description...',
                allowClear: true,
                minimumInputLength: 2,

                dropdownParent: (
                    $parent.length
                        ? $parent
                        : $(document.body)
                ),

                ajax: {
                    url: "{{ route('lookup.icd10') }}",
                    dataType: 'json',
                    delay: 300,

                    data: function (params) {
                        return {
                            q: params.term || '',
                            limit: 25
                        };
                    },

                    processResults: function (data) {
                        return data;
                    },

                    cache: true
                }
            });

        });
    }

    initIcd10('#Diag1');
    initIcd10('#Diag2');

});
</script>
@endsection
