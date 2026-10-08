@props(['icon', 'label', 'tone' => 'clinical', 'disabled' => false, 'hint' => null, 'href' => null])
@if($href && !$disabled)
<a href="{{ $href }}" {{ $attributes->class(['rm-quick-register__action', 'rm-quick-register__action--'.$tone]) }}>
    <i class="ph-bold {{ $icon }}" aria-hidden="true"></i>
    <span>{{ $label }}</span>
    @if($hint)<small>{{ $hint }}</small>@endif
</a>
@else
<button type="button" {{ $attributes->class(['rm-quick-register__action', 'rm-quick-register__action--'.$tone])->merge(['disabled' => $disabled, 'title' => $disabled ? $hint : null]) }}>
    <i class="ph-bold {{ $icon }}" aria-hidden="true"></i>
    <span>{{ $label }}</span>
    @if($hint)<small>{{ $hint }}</small>@endif
</button>
@endif
