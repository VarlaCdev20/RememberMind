@props(['title', 'subtitle', 'turno' => null, 'modoConsulta' => false])
<header {{ $attributes->class(['rm-resident-directory__header']) }}>
    <div class="rm-resident-directory__heading">
        <span class="rm-resident-directory__heading-icon" aria-hidden="true"><i class="ph-bold ph-users-three"></i></span>
        <div>
            <h1>{{ $title }}</h1>
            <p>{{ $subtitle }}</p>
        </div>
    </div>
    <div class="rm-resident-directory__header-meta">
        @if($modoConsulta)
            <span class="rm-resident-directory__shift">MODO CONSULTA / SOLO LECTURA</span>
        @elseif($turno)
            <span class="rm-resident-directory__shift">Jornada activa · {{ $turno->nombre ?: 'Turno en curso' }}</span>
        @endif
        <time datetime="{{ now()->toDateString() }}">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</time>
    </div>
</header>
