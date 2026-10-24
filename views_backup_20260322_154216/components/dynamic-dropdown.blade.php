@props(['widthClass' => 'form-select w-50', 'labelWidth' => 'min-width: 120px;'])


    @if($label)
        <label for="{{ $id }}" class="me-2" style="{{ $labelWidth }}">{{ $label }}</label>
    @endif
    <select name="{{ $name }}" id="{{ $id }}" class="{{ $widthClass }}" {{ $attributes }}>
        <option value="">Select</option>
        @foreach($options as $value => $text)
            <option value="{{ $value }}" {{ old($name, $selected) == $value ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>

