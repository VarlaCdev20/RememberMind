@props(['icon', 'label', 'tone' => 'clinical', 'disabled' => false, 'hint' => null])
<button type="button" {{ $attributes->class(['rm-quick-register__action', 'rm-quick-register__action--'.$tone])->merge(['disabled' => $disabled, 'title' => $disabled ? $hint : null]) }}>
    <i class="ph-bold {{ $icon }}" aria-hidden="true"></i>
    <span>{{ $label }}</span>
    @if($disabled && $hint)<small>{{ $hint }}</small>@endif
</button>
