@props([
    'eyebrow' => 'CENTRO GERIÁTRICO LOS ALMENDROS',
    'title',
    'highlight' => null,
    'description' => null,
    'image',
    'imageAlt' => 'Acompañamiento a residentes del centro geriátrico',
    'quote' => 'Historias que siguen floreciendo',
    'meta' => [],
])

<section {{ $attributes->class(['rm-role-hero']) }}>
    <div class="rm-role-hero__content">
        <div class="rm-role-hero__eyebrow">
            <img src="{{ asset('storage/imagenes/LOGO.png') }}" alt="" class="rm-role-hero__logo">
            <span>{{ $eyebrow }}</span>
            <span class="rm-role-hero__rule" aria-hidden="true"></span>
        </div>

        <h1 class="rm-role-hero__title">
            <span>{{ $title }}</span>
            @if($highlight)
                <span class="rm-role-hero__highlight">{{ $highlight }}</span>
            @endif
        </h1>

        @if($description)
            <p class="rm-role-hero__description">{{ $description }}</p>
        @endif

        @if(count($meta))
            <div class="rm-role-hero__meta" aria-label="Resumen de la jornada">
                @foreach($meta as $item)
                    <span class="rm-role-hero__chip">
                        <i class="ph-bold {{ $item['icon'] ?? 'ph-check-circle' }}"></i>
                        <span>{{ $item['label'] ?? '' }}</span>
                    </span>
                @endforeach
            </div>
        @endif

        @if(trim((string) $slot) !== '')
            <div class="rm-role-hero__actions">{{ $slot }}</div>
        @endif
    </div>

    <div class="rm-role-hero__visual">
        <img src="{{ $image }}" alt="{{ $imageAlt }}" class="rm-role-hero__image" loading="eager">
        <div class="rm-role-hero__veil" aria-hidden="true"></div>
        <div class="rm-role-hero__quote">
            <i class="ph-bold ph-heartbeat"></i>
            <span>{{ $quote }}</span>
        </div>
    </div>

    <i class="ph-bold ph-first-aid rm-role-hero__leaf rm-role-hero__leaf--one" aria-hidden="true"></i>
    <i class="ph-bold ph-heartbeat rm-role-hero__leaf rm-role-hero__leaf--two" aria-hidden="true"></i>
</section>
