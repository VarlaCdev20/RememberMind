@props([
    'type',
    'label',
    'icon' => null,
    'tone' => 'green',
    'hasValues' => true,
    'isActive' => false,
])

<button
    type="button"
    role="menuitem"
    @click="selectType('{{ $type }}')"
    @mouseenter="selectType('{{ $type }}')"
    class="rm-filter-type-item rm-filter-type-item--{{ $tone }}"
    :class="{ 'is-selected': activeType === '{{ $type }}', 'is-active-filter': {{ $isActive ? 'true' : 'false' }} }"
    aria-haspopup="{{ $hasValues ? 'true' : 'false' }}"
>
    <div class="rm-filter-type-item__left">
        @if($icon)
            <i class="ph-bold {{ $icon }} rm-filter-type-item__icon" aria-hidden="true"></i>
        @endif
        <span class="rm-filter-type-item__label">{{ $label }}</span>
    </div>
    <div class="rm-filter-type-item__right">
        @if($isActive)
            <span class="rm-filter-type-item__dot" title="Filtro activo aplicado"></span>
        @endif
        @if($hasValues)
            <i class="ph-bold ph-caret-right rm-filter-type-item__caret" aria-hidden="true"></i>
        @endif
    </div>
</button>
