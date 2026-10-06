@props(['label' => 'Fecha', 'min' => '', 'max' => '', 'id' => null, 'teleport' => 'body'])
@php
    $model = $attributes->wire('model')->value();
    $controlId = $id ?: 'calendario-'.str_replace('.', '-', $model);
@endphp
<div class="rm-choice" x-data="rmCalendario({ min: @js($min), max: @js($max), value: @entangle($model) })" wire:key="{{ $controlId }}">
    <input type="text" x-ref="native" data-min="{{ $min }}" data-max="{{ $max }}" {{ $attributes->except(['class', 'aria-label'])->merge(['id' => $controlId.'-source']) }} hidden tabindex="-1" aria-hidden="true">
    <button type="button" id="{{ $controlId }}" x-ref="trigger" class="rm-choice__trigger" aria-label="{{ $label }}" aria-haspopup="dialog" :aria-expanded="open" @click="open ? close() : show()"><i class="ph-bold ph-calendar-blank" aria-hidden="true"></i><span x-text="label"></span><i class="ph-bold ph-caret-down" aria-hidden="true"></i></button>
    <template x-teleport="{{ $teleport }}">
        <section x-show="open" x-cloak x-transition.opacity.duration.150ms :style="popupStyle" class="rm-control-popover rm-date-picker" role="dialog" aria-label="Elegir {{ mb_strtolower($label) }}" @click.outside="if (!$refs.trigger.contains($event.target)) open = false" @keydown.escape.prevent.stop="close()" @resize.window="open = false" @scroll.window="if (!$el.contains($event.target)) open = false">
            <header class="rm-date-picker__header"><button type="button" aria-label="Mes anterior" @click="changeMonth(-1)"><i class="ph-bold ph-caret-left" aria-hidden="true"></i></button><strong x-text="monthLabel"></strong><input x-ref="year" type="number" min="1" max="9999" :value="year" @input="if ($event.target.value.length === 4) changeYear($event.target.value)" @change="changeYear($event.target.value)" aria-label="Año del calendario"><button type="button" aria-label="Mes siguiente" @click="changeMonth(1)"><i class="ph-bold ph-caret-right" aria-hidden="true"></i></button></header>
            <div class="rm-date-picker__week" aria-hidden="true">@foreach(['L','M','M','J','V','S','D'] as $day)<span>{{ $day }}</span>@endforeach</div>
            <div class="rm-date-picker__grid" x-ref="grid">
                <template x-for="(day, index) in days" :key="day || 'blank-' + index"><button type="button" :data-date="day" :disabled="!allowed(day)" :aria-hidden="!day" :aria-label="day ? new Date(day + 'T12:00:00').toLocaleDateString('es-BO', { dateStyle: 'full' }) : null" :aria-pressed="value === day" :class="{ 'is-selected': value === day, 'is-today': today() === day, 'is-blank': !day }" @click="choose(day)" @keydown="dayKey($event, day)"><span x-text="day ? Number(day.slice(-2)) : ''"></span></button></template>
            </div>
            <footer><button type="button" @click="choose(today())" :disabled="!allowed(today())">Hoy</button><button type="button" @click="choose('')">Borrar fecha</button><button type="button" @click="close()">Cerrar</button></footer>
        </section>
    </template>
</div>
