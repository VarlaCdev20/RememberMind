@props([
    'eyebrow' => 'CENTRO GERIÁTRICO LOS ALMENDROS',
    'title',
    'highlight' => null,
    'role' => null,
    'date' => null,
    'scope' => null,
    'description' => null,
    'image',
    'rotationContext' => null,
    'personalGreeting' => false,
    'imageAlt' => 'Acompañamiento a residentes del centro geriátrico',
    'quote' => 'Historias que siguen floreciendo',
    'meta' => [],
])

@php
    $heroImage = $rotationContext
        ? app(\App\Backend\Modulos\Reportes\Servicios\DashboardPhotoRotation::class)->heroImage($rotationContext, $image)
        : $image;
@endphp

<x-ui.dashboard-header
    :eyebrow="$eyebrow"
    :title="$title"
    :subtitle="$description"
    :role="$role ?? ($meta[1]['label'] ?? '')"
    :date="$date ?? ($meta[0]['label'] ?? '')"
    :scope="$scope ?? ($meta[2]['label'] ?? '')"
    :image="$heroImage"
    :image-alt="$rotationContext ? 'Actividades y acompañamiento de residentes en Los Almendros' : $imageAlt"
    {{ $attributes }}
/>
@if($highlight || trim((string) $slot) !== '')
    <div class="rm-dashboard-header-context">
        @if($highlight)<p class="rm-caption">{{ $highlight }}</p>@endif
        @if(trim((string) $slot) !== '')<div class="rm-dashboard-header-actions">{{ $slot }}</div>@endif
    </div>
@endif
