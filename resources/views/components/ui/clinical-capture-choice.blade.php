@props(['field', 'value', 'label', 'icon' => null])
<button type="button" {{ $attributes->class(['rm-clinical-choice']) }} :aria-pressed="values[@js($field)] === @js($value)" @click="setValue(@js($field), @js($value))">
    @if($icon)<i class="ph-bold {{ $icon }}" aria-hidden="true"></i>@endif
    <span>{{ $label }}</span>
</button>
