@props(['location', 'href' => null])
@if($href)<a href="{{ $href }}" class="rm-location-cell rm-location-cell--{{ $location['status'] }}" aria-label="Habitación {{ $location['label'] }}, cama {{ $location['bed'] }}, {{ $location['patient'] }}">@else<div class="rm-location-cell rm-location-cell--{{ $location['status'] }}">@endif
    <i class="ph-bold ph-bed" aria-hidden="true"></i>
    <strong>Hab. {{ $location['label'] }}</strong>
    <span>Cama {{ $location['bed'] }}</span>
    <small>{{ $location['patient'] }}</small>
    @if($location['alerts'])<small class="rm-location-cell__alert">{{ $location['alerts'] }} {{ $location['alerts'] === 1 ? 'alerta activa' : 'alertas activas' }}</small>@endif
@if($href)</a>@else</div>@endif
