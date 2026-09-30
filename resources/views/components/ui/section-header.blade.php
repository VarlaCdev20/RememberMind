@props(['title', 'subtitle' => null, 'icon' => null, 'count' => null, 'actions' => null, 'level' => 3])
@php $heading = in_array((int) $level, [2, 3, 4], true) ? 'h'.(int) $level : 'h3'; @endphp
<header {{ $attributes->class(['rm-section-header']) }}>
    <div class="rm-section-header__main">
        @if($icon)<span class="rm-section-header__icon" aria-hidden="true"><i class="ph-bold {{ $icon }}"></i></span>@endif
        <div class="rm-section-header__copy">
            <{{ $heading }} class="rm-section-header__title">{{ $title }}</{{ $heading }}>
            @if($subtitle)<p class="rm-section-header__subtitle">{{ $subtitle }}</p>@endif
        </div>
        @if($count !== null)<span class="rm-section-header__count" aria-label="{{ $count }} elementos">{{ $count }}</span>@endif
    </div>
    @if($actions)<div class="rm-section-header__actions">{{ $actions }}</div>@endif
</header>
