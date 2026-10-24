<div class="card mb-3">
    <div class="card-header bg-secondary text-white">
        {{ $title }}
    </div>

    <div class="card-body">
        <div class="row">
            @foreach($items as $item)
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               name="{{ $field }}[]"
                               value="{{ $item }}"
                               {{ is_array($values) && in_array($item, $values) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ $item }}</label>
                    </div>
                </div>
            @endforeach
        </div>

        <label class="form-label mt-3">Other</label>
        <input type="text"
               name="{{ $otherField }}"
               class="form-control"
               value="{{ $otherValue }}">
    </div>
</div>
