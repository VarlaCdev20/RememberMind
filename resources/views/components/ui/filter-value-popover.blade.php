@props([
    'categories' => [],
    'activeFiltersMap' => [],
])

<div
    class="rm-filter-value-popover"
    role="region"
    aria-label="Valores del filtro"
>
    @foreach($categories as $category)
        @php
            $tone = $category['tone'] ?? 'green';
            $currentActiveValue = $activeFiltersMap[$category['type']] ?? null;
        @endphp
        <div
            x-show="activeType === '{{ $category['type'] }}'"
            x-cloak
            class="rm-filter-value-popover__panel rm-filter-value-popover__panel--{{ $tone }}"
        >
            <div class="rm-filter-value-popover__header">
                <button
                    type="button"
                    @click="activeType = null"
                    class="rm-filter-value-popover__back-btn sm:hidden"
                    aria-label="Volver a categorías"
                >
                    <i class="ph-bold ph-caret-left" aria-hidden="true"></i>
                    <span>Volver</span>
                </button>

                <span class="rm-filter-value-popover__title">
                    Filtrar por {{ mb_strtolower($category['label']) }}
                </span>
            </div>

            <div class="rm-filter-value-popover__options" role="listbox">
                @if(isset($category['options']) && is_array($category['options']))
                    @forelse($category['options'] as $option)
                        @php
                            $isSelected = ($currentActiveValue === $option['value']);
                            $optIcon = $option['icon'] ?? ($category['icon'] ?? null);
                        @endphp
                        <button
                            type="button"
                            role="option"
                            aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                            @click="selectValue('{{ $category['type'] }}', '{{ $option['value'] }}')"
                            class="rm-filter-value-item rm-filter-value-item--{{ $tone }} {{ $isSelected ? 'is-active-value' : '' }}"
                        >
                            <span class="rm-filter-value-item__label">
                                @if($optIcon)
                                    <i class="ph-bold {{ $optIcon }} rm-filter-value-item__icon" aria-hidden="true"></i>
                                @endif
                                <span>{{ $option['label'] }}</span>
                            </span>
                            @if(isset($option['count']) && $option['count'] !== null)
                                <span class="rm-filter-value-item__count">({{ $option['count'] }})</span>
                            @endif
                        </button>
                    @empty
                        <div class="rm-filter-value-popover__empty">
                            <i class="ph-bold ph-info" aria-hidden="true"></i>
                            <span>Sin opciones disponibles</span>
                        </div>
                    @endforelse
                @elseif(isset($category['dynamic']) && $category['dynamic'] === 'habitaciones')
                    @if(isset($category['options_list']) && count($category['options_list']) > 0)
                        @foreach($category['options_list'] as $hab)
                            @php
                                $val = is_array($hab) ? $hab['value'] : $hab;
                                $lbl = is_array($hab) ? $hab['label'] : $hab;
                                $cnt = is_array($hab) ? ($hab['count'] ?? null) : null;
                                $isSelected = ($currentActiveValue === $val);
                            @endphp
                            <button
                                type="button"
                                role="option"
                                aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                                @click="selectValue('{{ $category['type'] }}', '{{ $val }}')"
                                class="rm-filter-value-item rm-filter-value-item--{{ $tone }} {{ $isSelected ? 'is-active-value' : '' }}"
                            >
                                <span class="rm-filter-value-item__label">
                                    <i class="ph-bold ph-bed rm-filter-value-item__icon" aria-hidden="true"></i>
                                    <span>{{ $lbl }}</span>
                                </span>
                                @if($cnt !== null)
                                    <span class="rm-filter-value-item__count">({{ $cnt }})</span>
                                @endif
                            </button>
                        @endforeach
                    @else
                        <div class="rm-filter-value-popover__empty">
                            <i class="ph-bold ph-bed" aria-hidden="true"></i>
                            <span>No hay habitaciones asignadas</span>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endforeach
</div>
