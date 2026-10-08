@props(['paginator', 'mode' => 'url', 'perPage' => 10, 'perPageName' => null, 'label' => 'registros', 'showPerPage' => true, 'scrollTo' => false, 'excludeQuery' => []])
@php
    $livewire = $mode === 'livewire';
    $cursor = method_exists($paginator, 'getCursorName');
    $pageName = $cursor ? $paginator->getCursorName() : $paginator->getPageName();
    $perPageName ??= $livewire ? 'porPagina' : 'por_pagina';
    $controlId = 'paginacion-'.$pageName.'-'.$perPageName;
    $total = method_exists($paginator, 'total') ? $paginator->total() : null;
    $current = $cursor ? null : $paginator->currentPage();
    $last = method_exists($paginator, 'lastPage') ? $paginator->lastPage() : null;
    $windowCurrent = $last ? min($current, $last) : $current;
    $pages = $last ? collect([1, ...range(max(1, $windowCurrent - 1), min($last, $windowCurrent + 1)), $last])->unique()->sort()->values()->all() : [];
    // URL paginators retain filters, including named paginators on the same page.
    if (! $livewire) $paginator->appends(request()->except([$pageName, ...$excludeQuery]));
    $query = request()->except([$pageName, $perPageName, ...$excludeQuery]);
    $hiddenFields = [];
    $flattenQuery = function ($values, $prefix = '') use (&$flattenQuery, &$hiddenFields) {
        foreach ($values as $key => $value) {
            $name = $prefix === '' ? $key : $prefix.'['.$key.']';
            if (is_array($value)) $flattenQuery($value, $name);
            elseif ($value !== null) $hiddenFields[$name] = $value;
        }
    };
    $flattenQuery($query);
    $scrollExpression = $scrollTo === false ? '' : '(($el.closest('.json_encode($scrollTo).') || document.querySelector('.json_encode($scrollTo).'))?.scrollIntoView({behavior: "instant"}))';
@endphp
<footer {{ $attributes->class(['rm-pagination']) }} @if($livewire) wire:key="paginacion-{{ $pageName }}" @endif>
    <p class="rm-pagination__range" @if($livewire) aria-live="polite" @endif>
        @if($cursor)
            <strong>{{ $paginator->count() }}</strong> {{ $label }} en esta página
        @else
            <strong>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}</strong>
            @if($total !== null) de <strong>{{ $total }}</strong> @endif {{ $label }}
        @endif
    </p>
    @if($showPerPage)
        @if($livewire)
            <div class="rm-pagination__size">
                <label for="{{ $controlId }}">Por página</label>
                <x-ui.selector :id="$controlId" :label="ucfirst($label).' por página'" wire:model.live="{{ $perPageName }}">
                    @foreach([10, 20, 50] as $size)<option value="{{ $size }}">{{ $size }}</option>@endforeach
                </x-ui.selector>
            </div>
        @else
            <form method="get" action="{{ request()->url() }}" class="rm-pagination__size" x-data="{}" @submit.prevent="const target = $el.action + '?' + new URLSearchParams(new FormData($el)).toString(); window.Livewire?.navigate ? window.Livewire.navigate(target) : window.location.assign(target)">
                @foreach($hiddenFields as $name => $value)<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endforeach
                <label for="{{ $controlId }}">Por página</label>
                <x-ui.selector :id="$controlId" :label="ucfirst($label).' por página'" :name="$perPageName" :value="(string) $perPage" :auto-submit="true">
                    @foreach([10, 20, 50] as $size)<option value="{{ $size }}" @selected((int) $perPage === $size)>{{ $size }}</option>@endforeach
                </x-ui.selector>
            </form>
        @endif
    @endif
    <nav class="rm-pagination__pages" aria-label="Páginas de {{ $label }}">
        @foreach(['previous' => ['Página anterior', 'ph-caret-left', $paginator->onFirstPage()], 'next' => ['Página siguiente', 'ph-caret-right', !$paginator->hasMorePages()]] as $direction => [$title, $icon, $disabled])
            @if($direction === 'next')
                @php $previousNumber = null; @endphp
                @foreach($pages as $number)
                    @if($previousNumber !== null && $number - $previousNumber > 1)<span class="rm-pagination__ellipsis" aria-hidden="true">…</span>@endif
                    @if($number === $current)<span class="rm-pagination__page is-current" aria-current="page" aria-label="Página {{ $number }}, actual">{{ $number }}</span>
                    @elseif($livewire)<button type="button" class="rm-pagination__page" wire:key="{{ $pageName }}-pagina-{{ $number }}" wire:click="gotoPage({{ $number }}, '{{ $pageName }}')" wire:loading.attr="disabled" @if($scrollExpression) x-on:click="{{ $scrollExpression }}" @endif aria-label="Ir a la página {{ $number }}">{{ $number }}</button>
                    @else<a class="rm-pagination__page" href="{{ $paginator->url($number) }}" wire:navigate aria-label="Ir a la página {{ $number }}">{{ $number }}</a>@endif
                    @php $previousNumber = $number; @endphp
                @endforeach
            @endif
            @if($disabled)<button type="button" class="rm-pagination__page" disabled aria-label="{{ $title }}"><i class="ph-bold {{ $icon }}" aria-hidden="true"></i></button>
            @elseif($livewire)
                @php $action = $cursor ? "setPage('".$paginator->{$direction.'Cursor'}()->encode()."', '".$pageName."')" : $direction."Page('".$pageName."')"; @endphp
                <button type="button" class="rm-pagination__page" wire:click="{{ $action }}" wire:loading.attr="disabled" @if($scrollExpression) x-on:click="{{ $scrollExpression }}" @endif aria-label="{{ $title }}"><i class="ph-bold {{ $icon }}" aria-hidden="true"></i></button>
            @else<a class="rm-pagination__page" rel="{{ $direction === 'previous' ? 'prev' : 'next' }}" href="{{ $paginator->{$direction.'PageUrl'}() }}" wire:navigate aria-label="{{ $title }}"><i class="ph-bold {{ $icon }}" aria-hidden="true"></i></a>@endif
        @endforeach
    </nav>
</footer>
