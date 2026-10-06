@props(['label' => 'Seleccionar', 'id' => null, 'teleport' => 'body'])
@php
    $model = $attributes->wire('model')->value();
    $controlId = $id ?: 'selector-'.str_replace('.', '-', $model);
@endphp
<div class="rm-choice" x-data="rmSelector(@entangle($model))" wire:key="{{ $controlId }}">
    <select x-ref="native" {{ $attributes->except(['class', 'aria-label'])->merge(['id' => $controlId.'-source']) }} tabindex="-1" aria-hidden="true" hidden>{{ $slot }}</select>
    <button type="button" id="{{ $controlId }}" x-ref="trigger" class="rm-choice__trigger" aria-label="{{ $attributes->get('aria-label', $label) }}" aria-haspopup="listbox" :aria-expanded="open" aria-controls="{{ $controlId }}-list" :disabled="$refs.native?.disabled" @click="open ? close() : show()" @keydown.arrow-down.prevent="show()">
        <span x-text="label"></span><i class="ph-bold ph-caret-down" aria-hidden="true" :class="{ 'is-open': open }"></i>
    </button>
    <template x-teleport="{{ $teleport }}">
        <div x-show="open" x-cloak x-transition.opacity.duration.150ms class="rm-control-popover rm-choice__popover" :style="popupStyle" @click.outside="if (!$refs.trigger.contains($event.target)) open = false" @keydown="key($event)" @resize.window="open = false" @scroll.window="if (!$el.contains($event.target)) open = false">
            <div class="rm-choice__search" x-show="hasSearch"><i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i><input x-ref="search" x-model="search" @input="active = 0" aria-label="Buscar {{ mb_strtolower($label) }}" placeholder="Buscar opción…" role="combobox" aria-autocomplete="list" aria-controls="{{ $controlId }}-list" :aria-expanded="open" :aria-activedescendant="filtered.length ? '{{ $controlId }}-op-' + active : null"></div>
            <div id="{{ $controlId }}-list" x-ref="list" class="rm-choice__options" :class="{ 'is-long': options.length > 10 }" role="listbox" aria-label="{{ $label }}" :aria-multiselectable="multiple">
                <template x-for="(option, index) in filtered" :key="option.value">
                    <button type="button" role="option" :id="'{{ $controlId }}-op-' + index" :tabindex="!hasSearch && active === index ? 0 : -1" :aria-selected="selected(option.value)" :disabled="option.disabled" :class="{ 'is-selected': selected(option.value), 'is-highlighted': active === index }" @mouseenter="active = index" @click="choose(option)"><span x-text="option.label"></span><i x-show="selected(option.value)" class="ph-bold ph-check" aria-hidden="true"></i></button>
                </template>
            </div>
            <p x-show="filtered.length === 0" class="rm-control-help" role="status">Sin coincidencias. Prueba otra búsqueda.</p>
            <p class="rm-control-help" x-show="options.length > 10">Desliza para ver más opciones</p>
            <button type="button" x-show="multiple" class="rm-btn-secondary" @click="close()">Listo</button>
        </div>
    </template>
</div>
