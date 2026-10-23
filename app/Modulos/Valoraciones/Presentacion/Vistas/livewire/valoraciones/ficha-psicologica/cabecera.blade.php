<header class="rm-resident-file__heading">
    <div>
        <nav class="rm-resident-file__breadcrumb" aria-label="Ruta de navegación">
            <a href="{{ route('admin.psicologia.dashboard') }}">Psicología</a>
            <i class="ph ph-caret-right" aria-hidden="true"></i>
            <a href="{{ route('admin.psicologia.evaluaciones') }}">Residentes</a>
            <i class="ph ph-caret-right" aria-hidden="true"></i>
            <span aria-current="page">Ficha psicológica</span>
        </nav>
        <h1>Ficha psicológica del residente</h1>
        <p>Valoraciones, autonomía y seguimiento en un mismo lugar.</p>
    </div>
    <a href="{{ route('admin.psicologia.evaluaciones') }}" class="rm-btn rm-btn-secondary">
        <i class="ph ph-arrow-left" aria-hidden="true"></i>Volver a residentes
    </a>
</header>

@if($mensajeExito)
    <div class="rm-resident-file__notice" role="status">
        <i class="ph ph-check-circle" aria-hidden="true"></i>
        <p>{{ $mensajeExito }}</p>
        <button type="button" wire:click="$set('mensajeExito', null)" class="rm-btn rm-btn-ghost" aria-label="Cerrar confirmación">
            <i class="ph ph-x" aria-hidden="true"></i>
        </button>
    </div>
@endif

<section class="rm-resident-banner rm-resident-file__identity" aria-labelledby="ficha-residente-nombre">
    <div class="rm-resident-file__person">
        <span class="rm-resident-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($residente->nombres, 0, 1).mb_substr($residente->apellido_paterno ?? '', 0, 1)) }}</span>
        <div class="rm-resident-file__person-copy">
            <p class="rm-resident-file__eyebrow">Expediente de Psicología</p>
            <h2 id="ficha-residente-nombre">{{ $residente->nombre_completo }}</h2>
            <ul class="rm-resident-file__meta" aria-label="Datos del residente">
                <li><i class="ph ph-cake" aria-hidden="true"></i>{{ $residente->edad !== null ? $residente->edad.' años' : 'Edad no registrada' }}</li>
                <li><i class="ph ph-bed" aria-hidden="true"></i>{{ $residente->ubicacion_formateada }}</li>
                <li><i class="ph ph-user-circle-check" aria-hidden="true"></i>{{ \Illuminate\Support\Str::headline(\Illuminate\Support\Str::lower($residente->estado)) }}</li>
            </ul>
        </div>
    </div>
    <div class="rm-resident-file__actions">
        <a href="{{ route('admin.psicologia.residente.resultados-experto', $residente) }}" class="rm-btn rm-btn-primary">
            <i class="ph ph-brain" aria-hidden="true"></i>Ver resultados cognitivos
        </a>
        @if($puedeValorarPetersen)
            <a href="{{ route('admin.sistema-experto.petersen-original', $residente) }}" class="rm-btn rm-btn-secondary">
                <i class="ph ph-clipboard-text" aria-hidden="true"></i>Valoración cognitiva y funcional
            </a>
        @endif
    </div>
</section>

@php
    $pestanas = [
        ['id' => 'resumen', 'label' => 'Resumen', 'icono' => 'ph-squares-four'],
        ['id' => 'cognitiva', 'label' => 'Cognitiva', 'icono' => 'ph-brain'],
        ['id' => 'afectiva', 'label' => 'Afectiva y emocional', 'icono' => 'ph-heart'],
        ['id' => 'funcionamiento', 'label' => 'Autonomía cotidiana', 'icono' => 'ph-person-simple-walk'],
        ['id' => 'entorno', 'label' => 'Entorno y red social', 'icono' => 'ph-users-four'],
        ['id' => 'seguimiento', 'label' => 'Sesiones y seguimiento', 'icono' => 'ph-calendar-check'],
        ['id' => 'informes', 'label' => 'Informes y resultados', 'icono' => 'ph-file-text'],
        ['id' => 'orion', 'label' => 'Resultados cognitivos', 'icono' => 'ph-sparkle'],
    ];
@endphp
<nav class="rm-tabs rm-resident-file__navigation" aria-label="Secciones de la ficha psicológica">
    @foreach($pestanas as $p)
        <button type="button" wire:key="ficha-tab-{{ $p['id'] }}" wire:click="cambiarTab('{{ $p['id'] }}')"
            wire:loading.attr="disabled" wire:target="cambiarTab" aria-controls="ficha-psicologica-contenido"
            aria-pressed="{{ $tab === $p['id'] ? 'true' : 'false' }}"
            class="rm-tab-item {{ $tab === $p['id'] ? 'is-active' : '' }}">
            <i class="ph {{ $p['icono'] }}" aria-hidden="true"></i><span>{{ $p['label'] }}</span>
        </button>
    @endforeach
</nav>
