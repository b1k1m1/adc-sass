@props([
    'label' => '',
    'name',
    'id' => null,
    'options' => [],
    'selected' => null,
    'widthClass' => 'form-select w-50',
    'labelWidth' => 'min-width: 120px;',
])

@php
    if (!$options) {
        switch ($name) {
            case 'bloodpressure':
                $options = [
                    '- -' => '- -',
                    'Monthly' => 'Monthly',
                    'Weekly' => 'Weekly',
                    'Daily' => 'Daily',
                    'BiD' => 'BiD',
                    '2x/Wk' => '2x/Wk',
                    '3x/Wk' => '3x/Wk',
                    '4x/Wk' => '4x/Wk',
                    '2x/Month' => '2x/Month',
                    'PRN' => 'PRN',
                    'QOD' => 'QOD',
                    'TID' => 'TID',
                ];
                break;
            case 'gender':
                $options = ['M' => 'Male', 'F' => 'Female'];
                break;
            // Add more cases as needed
            default:
                $options = [];
        }
    }
@endphp


<label for="{{ $id ?? $name }}" class="me-2" style="{{ $labelWidth }}">{{ $label }}</label>
<select
    name="{{ $name }}"
    id="{{ $id ?? $name }}"
    class="{{ $widthClass }}"
>
    @foreach($options as $value => $text)
        <option value="{{ $value }}" {{ old($name, trim($selected)) === $value ? 'selected' : '' }}>
            {{ $text }}
        </option>
    @endforeach
</select>
