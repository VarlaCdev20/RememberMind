@props(['source' => null, 'name' => '', 'id' => 'medication-media'])

<figure class="rm-medication-media" x-data="{ failed: false }" wire:key="{{ $id }}-{{ $source ?: 'illustration' }}">
    <div class="rm-medication-media__stage">
        @if($source)
            <img src="{{ $source }}" alt="Imagen de catálogo de {{ $name }}" class="rm-medication-media__photo" x-show="!failed" x-on:error="failed = true" decoding="async">
        @endif
        <div class="rm-medication-media__illustration" @if($source) x-show="failed" x-cloak @endif>
            <svg viewBox="0 0 320 176" role="img" aria-label="Ilustración farmacéutica genérica; no es una fotografía del medicamento">
                <defs><linearGradient id="{{ $id }}-foil" x1="0" y1="0" x2="1" y2="1"><stop stop-color="var(--rm-surface)"/><stop offset="1" stop-color="var(--rm-surface-soft)"/></linearGradient></defs>
                <ellipse cx="162" cy="153" rx="111" ry="10" fill="var(--rm-border-soft)" opacity=".45"/>
                <g transform="translate(42 23) rotate(-9 78 65)">
                    <rect width="154" height="126" rx="15" fill="url(#{{ $id }}-foil)" stroke="var(--rm-border-strong)"/>
                    @foreach([29, 77, 125] as $x)
                        @foreach([34, 88] as $y)
                            <ellipse cx="{{ $x }}" cy="{{ $y }}" rx="17" ry="21" fill="var(--rm-surface)" stroke="var(--rm-border-soft)"/>
                            <ellipse cx="{{ $x }}" cy="{{ $y - 2 }}" rx="12" ry="15" fill="var(--rm-surface-soft)" stroke="var(--rm-border-strong)"/>
                            <path d="M{{ $x - 8 }} {{ $y - 2 }}h16" stroke="var(--rm-border-strong)"/>
                        @endforeach
                    @endforeach
                </g>
                <g transform="translate(192 36) rotate(20 31 53)">
                    <rect width="62" height="112" rx="30" fill="var(--rm-surface)" stroke="var(--rm-border-strong)"/>
                    <path d="M1 56V31a30 30 0 0 1 60 0v25Z" fill="var(--rm-action-primary-soft)"/>
                    <path d="M1 56h60" stroke="var(--rm-border-strong)"/>
                    <path d="M15 25c3-8 8-12 14-13" stroke="var(--rm-surface)" stroke-width="5" stroke-linecap="round" fill="none"/>
                </g>
                <g transform="translate(243 125) rotate(-14)"><ellipse rx="26" ry="14" fill="var(--rm-surface-soft)" stroke="var(--rm-border-strong)"/><path d="M-19 0h38" stroke="var(--rm-border-strong)"/></g>
            </svg>
        </div>
    </div>
    <figcaption>
        @if($source)<span x-show="!failed">Imagen asociada al catálogo</span><span x-show="failed" x-cloak>La fotografía no se pudo cargar. Ilustración farmacéutica de apoyo.</span>
        @else Ilustración farmacéutica · fotografía no disponible @endif
    </figcaption>
</figure>
