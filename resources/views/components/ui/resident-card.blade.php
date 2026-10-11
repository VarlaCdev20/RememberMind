@props([
    'resident' => null, 'code' => null, 'name' => null, 'photo' => null,
    'age' => null, 'location' => null, 'showLocation' => true,
    'mode' => 'card', 'variant' => 'nursing', 'selected' => false,
    'primaryLabel' => 'Ver resumen', 'selectMethod' => null, 'primaryHref' => null,
    'contextLabel' => null, 'contextValue' => null, 'contextTime' => null,
    'alertCount' => null, 'statusLabel' => null, 'statusTone' => 'slate',
])
@php
    $code ??= $resident?->cod_residente;
    $name ??= $resident?->nombre_completo;
    $photo ??= $resident?->foto;
    $age ??= $resident?->fecha_nacimiento?->age;
    $initials = $resident
        ? mb_strtoupper(mb_substr((string) $resident->nombres, 0, 1).mb_substr((string) $resident->apellido_paterno, 0, 1))
        : null;
    if ($location === null) {
        $room = $resident?->ocupacionActiva?->cama?->habitacion?->codigo;
        $bed = $resident?->ocupacionActiva?->cama?->codigo;
        $location = $room
            ? (str_starts_with(mb_strtolower($room), 'hab') ? $room : 'Hab. '.$room)
                .($bed ? ' · '.(str_starts_with(mb_strtolower($bed), 'cama') ? $bed : 'Cama '.$bed) : '')
            : 'Sin ubicación asignada';
    }
    $alertCount = $alertCount === null ? null : (int) $alertCount;
    $hasMenu = isset($menu) && trim((string) $menu) !== '';
    $roleTone = match ($variant) {
        'medical' => 'doctor', 'administrative' => 'admin', 'management' => 'manager',
        'supervision' => 'superadmin',
        'nursing', 'physio', 'psychology', 'nutrition', 'pedagogy', 'family' => $variant,
        'inherit' => null,
        default => 'superadmin',
    };
@endphp
<x-ui.person-entity-card wire:key="resident-card-{{ $mode }}-{{ $code }}" {{ $attributes->class([
    'rm-resident-directory__item', 'rm-resident-directory__item--'.$mode,
    'rm-resident-compact-card' => $mode === 'card', 'is-selected' => $selected,
]) }} role="listitem" data-role="{{ $roleTone ?? '' }}" data-variant="{{ $variant }}" x-data="{ open: false }" ::class="{ 'has-open-menu': open }">
    @if($mode === 'row' && $statusLabel)
        <span class="rm-resident-directory__status rm-resident-directory__status--{{ $statusTone }}"><i class="ph-bold {{ $statusTone === 'red' ? 'ph-warning-circle' : ($statusTone === 'amber' ? 'ph-heartbeat' : 'ph-shield-check') }}" aria-hidden="true"></i>{{ $statusLabel }}</span>
    @endif
    <x-ui.resident-card-identity :name="$name" :photo="$photo" :age="$age" :code="$code" :initials="$initials" :mode="$mode" :select-method="$selectMethod" :primary-href="$primaryHref" />
    @if(isset($status))
        <div class="rm-resident-card__status">{{ $status }}</div>
    @endif
    @if($mode === 'card')
        <div class="rm-resident-compact-card__facts">
            @if($showLocation)<p><i class="ph-bold ph-bed" aria-hidden="true"></i><span>{{ $location }}</span></p>@endif
            @if($contextLabel)
                <div class="rm-resident-compact-card__next"><span>{{ $contextLabel }}</span><strong>{{ $contextValue ?: 'Sin acciones programadas' }}</strong>@if($contextTime)<time>{{ $contextTime }}</time>@endif</div>
            @endif
            @if($alertCount !== null)<p class="rm-resident-compact-card__alert {{ $alertCount > 0 ? 'has-alerts' : '' }}"><i class="ph-bold {{ $alertCount > 0 ? 'ph-warning-circle' : 'ph-check-circle' }}" aria-hidden="true"></i><span>{{ $alertCount > 0 ? $alertCount.' '.($alertCount === 1 ? 'alerta prioritaria' : 'alertas prioritarias') : 'Sin alertas prioritarias' }}</span></p>@endif
            @if(isset($details))
                {{ $details }}
            @endif
        </div>
        @if(isset($footerNote))
            <div class="rm-resident-card__footer-note">{{ $footerNote }}</div>
        @endif
        <div class="rm-resident-compact-card__footer" @if($hasMenu) @keydown.escape.stop="if (open) { open = false; $refs.more.focus() }" @endif>
            @if($primaryHref)<a href="{{ $primaryHref }}" class="rm-resident-directory__open">{{ $primaryLabel }} <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
            @elseif($selectMethod)<button type="button" wire:click="{{ $selectMethod }}('{{ $code }}')" class="rm-resident-directory__open" aria-label="{{ $primaryLabel }} de {{ $name }}">{{ $primaryLabel }} <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></button>@endif
            @if($hasMenu)
                <button type="button" x-ref="more" class="rm-resident-compact-card__more" :class="{ 'is-open': open }" @click="open = !open; if (open) $nextTick(() => $refs.menu.querySelector('a, button')?.focus())" :aria-expanded="open.toString()" aria-controls="resident-actions-{{ $code }}" aria-label="Más acciones para {{ $name }}" aria-haspopup="true"><i class="ph-bold ph-dots-three" aria-hidden="true"></i></button>
                <div id="resident-actions-{{ $code }}" x-ref="menu" class="rm-resident-compact-card__menu" x-show="open" x-cloak x-transition.opacity.duration.150ms @click.outside="open = false" role="group" aria-label="Acciones para {{ $name }}">{{ $menu }}</div>
            @endif
        </div>
    @else
        <div class="rm-resident-directory__meta">
            @if($showLocation)<div class="rm-resident-directory__location"><i class="ph-bold ph-bed" aria-hidden="true"></i><span><small>Ubicación</small>{{ $location }}</span></div>@endif
            @if($contextLabel)<div class="rm-resident-directory__next"><i class="ph-bold ph-calendar-check" aria-hidden="true"></i><span><small>{{ $contextLabel }}</small>{{ $contextValue ?: 'Sin cuidado programado' }}@if($contextTime)<em>{{ $contextTime }}</em>@endif</span></div>@endif
        </div>
        @if($primaryHref)<a href="{{ $primaryHref }}" class="rm-resident-directory__open">{{ $primaryLabel }} <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
        @elseif($selectMethod)<button type="button" wire:click="{{ $selectMethod }}('{{ $code }}')" class="rm-resident-directory__open" aria-label="{{ $primaryLabel }} de {{ $name }}">{{ $primaryLabel }} <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></button>@endif
    @endif
</x-ui.person-entity-card>
