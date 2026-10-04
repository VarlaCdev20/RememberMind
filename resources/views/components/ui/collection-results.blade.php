@props(['title' => null, 'count' => null, 'label' => null])

<section {{ $attributes->class(['rm-collection-results']) }}>
    @if($title || $count !== null || isset($actions))
        <div class="rm-collection-results__heading">
            <div>
                @if($title)<h2>{{ $title }}</h2>@endif
                @if($count !== null)<p aria-live="polite">{{ $count }} {{ $label ?? 'registros' }}</p>@endif
            </div>
            @if(isset($actions))<div class="rm-collection-results__actions">{{ $actions }}</div>@endif
        </div>
    @endif
    <div class="rm-collection-results__body">{{ $slot }}</div>
</section>
