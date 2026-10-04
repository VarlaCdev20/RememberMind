@props(['name', 'photo' => null, 'age' => null, 'code', 'initials' => null, 'mode' => 'card', 'selectMethod' => null, 'primaryHref' => null])
@php
    $visibleName = \Illuminate\Support\Str::title(mb_strtolower($name));
    $nameParts = preg_split('/\s+/u', trim($visibleName)) ?: [];
    $initials ??= mb_strtoupper(mb_substr($nameParts[0] ?? '', 0, 1).mb_substr($nameParts[1] ?? '', 0, 1));
@endphp
<div class="{{ $mode === 'card' ? 'rm-resident-compact-card__identity' : 'rm-resident-directory__identity' }}">
    @if($selectMethod || $primaryHref)
        @if($primaryHref)<a href="{{ $primaryHref }}" class="rm-resident-directory__avatar-button" aria-label="Abrir a {{ $name }}">
        @else<button type="button" wire:click="{{ $selectMethod }}('{{ $code }}')" class="rm-resident-directory__avatar-button" aria-label="Ver resumen de {{ $name }}">@endif
    @else
        <span class="rm-resident-directory__avatar-button">
    @endif
        @if($mode !== 'card')<span class="rm-resident-directory__avatar-ornament" aria-hidden="true"><i class="ph ph-leaf"></i><i class="ph ph-leaf"></i></span>@endif
        @if($photo)
            <img src="{{ asset('storage/'.$photo) }}" alt="Foto de {{ $name }}" loading="lazy" decoding="async" class="rm-resident-directory__avatar">
        @else
            <span class="rm-resident-directory__avatar rm-resident-directory__avatar--initials" aria-hidden="true">{{ $initials ?: 'R' }}</span>
        @endif
    @if($selectMethod || $primaryHref)
        @if($primaryHref)</a>@else</button>@endif
    @else
        </span>
    @endif
    @if($mode === 'card')
        @if($primaryHref)<a href="{{ $primaryHref }}" class="rm-resident-directory__name">{{ $visibleName }}</a>
        @elseif($selectMethod)<button type="button" wire:click="{{ $selectMethod }}('{{ $code }}')" class="rm-resident-directory__name">{{ $visibleName }}</button>
        @else<strong class="rm-resident-directory__name">{{ $visibleName }}</strong>@endif
        <span class="rm-resident-compact-card__age">{{ $age !== null ? $age.' años' : 'Edad no registrada' }}</span>
    @else
        <div class="rm-resident-directory__name-wrap">
            @if($primaryHref)<a href="{{ $primaryHref }}" class="rm-resident-directory__name">{{ $visibleName }}</a>
            @elseif($selectMethod)<button type="button" wire:click="{{ $selectMethod }}('{{ $code }}')" class="rm-resident-directory__name">{{ $visibleName }}</button>
            @else<strong class="rm-resident-directory__name">{{ $visibleName }}</strong>@endif
            <span><i class="ph-bold ph-user" aria-hidden="true"></i>{{ $age !== null ? $age.' años' : 'Edad no registrada' }}</span>
        </div>
    @endif
</div>
