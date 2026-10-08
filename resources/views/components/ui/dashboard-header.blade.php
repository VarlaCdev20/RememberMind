@props([
    'eyebrow' => 'CENTRO GERIÁTRICO LOS ALMENDROS',
    'title', 'subtitle' => null, 'role' => null, 'date' => null, 'scope' => null,
    'image' => null, 'accent' => null,
    'imageAlt' => 'Cuidado y acompañamiento en Los Almendros',
    'imageLabel' => null,
    'compact' => false,
])
@php
    $assignedRole = app(\App\Backend\Modulos\Identidad\Servicios\RolePreviewService::class)->activeRole(auth()->user())
        ?? (request()->routeIs('admin.enfermeria.*') && auth()->user()?->hasRole('ENFERMEROS')
            ? 'ENFERMEROS'
            : auth()->user()?->getRoleNames()->first());
    $variant = config('dashboard-header.variants.' . $assignedRole, []);
    $allowedTones = ['superadmin', 'manager', 'admin', 'doctor', 'nursing', 'psychology', 'nutrition', 'physio', 'pedagogy', 'family'];
    $accentToken = in_array($variant['tone'] ?? null, $allowedTones, true)
        ? $variant['tone']
        : (in_array($accent, $allowedTones, true) ? $accent : 'superadmin');
    $roleLabel = $variant['label'] ?? $role;
    $subtitle = $subtitle ?? ($variant['subtitle'] ?? null);
    $scope = $scope ?? ($variant['scope'] ?? null);
    $imageLabel = $imageLabel ?? ($variant['image_label'] ?? 'Cuidado centrado en la persona');
@endphp
<header {{ $attributes->class(['rm-dashboard-header', 'rm-dashboard-header--compact' => $compact]) }} data-accent="{{ $accentToken }}">
    <div class="rm-dashboard-header__content">
        @unless($compact)
        <div class="rm-dashboard-header__ornaments" aria-hidden="true">
            <span class="rm-dashboard-header__ornament rm-dashboard-header__ornament--care">
                <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="6" r="2.5" />
                    <path d="m12 10-2 8 6 3 3 7M11 14l7 2 4-3M10 18l-4 9M24 17v11m0-11 3-2" />
                </svg>
            </span>
            <span class="rm-dashboard-header__ornament rm-dashboard-header__ornament--medical">
                <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M7 5v10a7 7 0 0 0 14 0V5M5 5h4m10 0h4M14 22v1a5 5 0 0 0 10 0v-3" />
                    <circle cx="25" cy="17" r="2" />
                </svg>
            </span>
        </div>
        @endunless
        <p class="rm-dashboard-header__eyebrow">{{ $eyebrow }}</p>
        <div class="rm-dashboard-header__heading">
            <h1 class="rm-dashboard-header__title">{{ $title }}</h1>
            @if($roleLabel)<span class="rm-dashboard-header__role"><span aria-hidden="true"></span>{{ $roleLabel }}</span>@endif
        </div>
        @if($subtitle)<p class="rm-dashboard-header__subtitle">{{ $subtitle }}</p>@endif
        <div class="rm-dashboard-header__metadata">
            @if($date)<span class="rm-dashboard-header__date">{{ $date }}</span>@endif
            <span class="rm-dashboard-header__clock" data-time-zone="{{ config('app.timezone') }}"
                x-data="{
                    time: '', timer: null, zone: @js(config('app.timezone')),
                    update() { this.time = new Intl.DateTimeFormat('es-BO', { timeZone: this.zone, hour: '2-digit', minute: '2-digit', hour12: false }).format(new Date()) },
                    schedule() { this.timer = setTimeout(() => { this.update(); this.schedule() }, 60000 - Date.now() % 60000) },
                    init() { this.update(); this.schedule() },
                    destroy() { clearTimeout(this.timer) }
                }">
                <i class="ph-bold ph-clock" aria-hidden="true"></i>
                <span class="sr-only">Hora actual:</span>
                <time x-text="time">{{ now()->timezone(config('app.timezone'))->format('H:i') }}</time>
            </span>
            @if($scope)<span class="rm-dashboard-header__scope">{{ $scope }}</span>@endif
        </div>
    </div>
    @if($image)
        <div class="rm-dashboard-header__visual">
            <img src="{{ $image }}" alt="{{ $imageAlt }}" decoding="async" loading="eager">
            @if($imageLabel)<span class="rm-dashboard-header__image-label">{{ $imageLabel }}</span>@endif
        </div>
    @endif
</header>
