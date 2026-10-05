@props([
    'categories' => [],
    'activeFiltersMap' => [],
])

<div
    class="rm-filter-type-menu__list"
    role="menu"
    aria-label="Categorías de filtro"
>
    @if(isset($slot) && !empty(trim((string)$slot)))
        {{ $slot }}
    @else
        @foreach($categories as $category)
            <x-ui.filter-type-item
                :type="$category['type']"
                :label="$category['label']"
                :icon="$category['icon'] ?? null"
                :tone="$category['tone'] ?? 'green'"
                :is-active="!empty($activeFiltersMap[$category['type']] ?? null)"
            />
        @endforeach
    @endif
</div>
