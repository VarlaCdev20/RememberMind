@props([
    'variant' => 'info',
    'title' => null,
    'icon' => null,
    'role' => null,
])

@php
    $variantClass = match($variant) {
        'success' => 'rm-alert-success',
        'warning' => 'rm-alert-warning',
        'danger' => 'rm-alert-danger',
        default => 'rm-alert-info',
    };

    $resolvedIcon = $icon ?? match($variant) {
        'success' => 'ph-check-circle',
        'warning' => 'ph-warning',
        'danger' => 'ph-warning-octagon',
        default => 'ph-info',
    };
    $resolvedRole = $role ?? ($variant === 'danger' ? 'alert' : 'status');
@endphp

<div {{ $attributes->class(["rm-alert {$variantClass}"]) }} role="{{ $resolvedRole }}">
    <i class="ph-bold {{ $resolvedIcon }} rm-alert-icon" aria-hidden="true"></i>
    <div class="rm-alert-content">
        @if($title)
            <p class="rm-alert-title">{{ $title }}</p>
        @endif
        <div class="rm-alert-desc">{{ $slot }}</div>
    </div>
</div>
