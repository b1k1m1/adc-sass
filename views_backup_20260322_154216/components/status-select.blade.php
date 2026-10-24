@props(['name' => 'trans_flag', 'value' => 'A', 'disabled' => false])
<select name="{{ $name }}" class="form-select form-select-sm" @disabled($disabled)>
<option value="A" {{ $value==='A'?'selected':'' }}>Active</option>
<option value="H" {{ $value==='H'?'selected':'' }}>Hold</option>
<option value="D" {{ $value==='D'?'selected':'' }}>Delete</option>
</select>
