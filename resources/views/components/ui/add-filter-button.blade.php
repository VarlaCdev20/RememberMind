@props([
    'label' => 'Añadir filtro',
    'activeCount' => 0,
])

<button
    type="button"
    @click="toggleMenu()"
    :aria-expanded="openMenu.toString()"
    aria-haspopup="true"
    {{ $attributes->class(['rm-add-filter-btn']) }}
    :class="{ 'is-open': openMenu, 'has-active': {{ $activeCount > 0 ? 'true' : 'false' }} }"
>
    <span class="rm-add-filter-btn__icon-box" aria-hidden="true">
        <i class="ph-bold ph-plus rm-add-filter-btn__icon-plus"></i>
    </span>
    <span class="rm-add-filter-btn__label">{{ $label }}</span>
    @if($activeCount > 0)
        <span class="rm-add-filter-btn__badge" title="{{ $activeCount }} {{ $activeCount === 1 ? 'filtro activo' : 'filtros activos' }}">{{ $activeCount }}</span>
    @endif
    <i class="ph-bold ph-caret-down rm-add-filter-btn__caret" :class="{ 'is-rotated': openMenu }" aria-hidden="true"></i>
</button>