<div class="rm-lab rm-knowledge-workspace space-y-5 text-rm-primary" x-data="{ ultimoDetalle: null }" x-on:click.capture="if ($event.target.closest('[data-expert-detail]')) ultimoDetalle = $event.target.closest('[data-expert-detail]')" x-on:expert-detail-closed.window="$nextTick(() => ultimoDetalle?.focus())">
    <header class="rm-card rm-expert-admin-header"><div class="rm-expert-admin-header__title"><span><i class="ph ph-graph" aria-hidden="true"></i></span><div><p class="rm-expert-eyebrow">RememberMind · Superadministración</p><h1>Sistema experto</h1><p>Conocimiento que acompaña. Decisiones que cuidan.</p></div></div><div class="rm-expert-admin-header__route" aria-label="Recorrido del sistema experto"><span><i class="ph ph-books" aria-hidden="true"></i>Conocimiento</span><i class="ph ph-arrow-right" aria-hidden="true"></i><span><i class="ph ph-link" aria-hidden="true"></i>Fuentes</span><i class="ph ph-arrow-right" aria-hidden="true"></i><span><i class="ph ph-users" aria-hidden="true"></i>Residentes</span></div></header>
    <div class="rm-expert-disclosure" role="note"><i class="ph ph-shield-check" aria-hidden="true"></i><div><strong>Consulta de conocimiento · activación clínica pendiente</strong><p>Un estado técnico activo no acredita por sí mismo aprobación ni publicación clínica. La consulta conserva las versiones originales.</p></div></div>
    <nav class="rm-lab-nav" aria-label="Secciones del sistema experto">
        @can('consultarGobierno', \App\Models\VersionModeloExperto::class)<a href="{{ route('admin.sistema-experto.reglas') }}" class="rm-btn rm-btn-secondary">Gobierno de reglas</a>@endcan
        @foreach(['gobierno' => 'Resumen', 'conocimiento' => 'Conocimiento', 'residentes' => 'Residentes y fuentes', 'verificacion' => 'Verificación y auditoría'] as $id => $nombre)
            <button type="button" wire:click="ir('{{ $id }}')" class="rm-btn {{ $seccion === $id ? 'rm-btn-primary' : 'rm-btn-secondary' }}" @if($seccion === $id) aria-current="page" @endif>{{ $nombre }}</button>
        @endforeach
    </nav>
    <div wire:loading.delay role="status" aria-live="polite" class="rm-lab-pending">Procesando la consulta del sistema experto…</div>
    @if($errorOperacion)<div class="rm-alert rm-alert-danger" role="alert"><div><strong>La evaluación no se completó</strong><p>{{ $errorOperacion }}</p></div></div>@endif

    <div class="rm-knowledge-surface space-y-5" wire:key="expert-section-{{ $seccion }}">
    @if($seccion === 'gobierno')
        @include('apoyodecision::livewire.superadministrador.sistema-experto.gobierno')
    @elseif($seccion === 'conocimiento')
        @include('livewire.superadministrador.sistema-experto.explorador')
    @elseif($seccion === 'residentes')
        @livewire(\App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResidentesConocimientoPanel::class, [], key('superadmin-residentes'))
    @elseif($seccion === 'verificacion')
        <x-ui.section-card title="Conservación y revisión" subtitle="Estado del recorrido institucional.">
            <p>Los resultados deben conservar sus fuentes, reglas y versiones. La conservación conjunta instrumental y Petersen continúa pendiente; una consulta en memoria no constituye un resultado histórico guardado.</p>
            <p class="mt-3">Medicina y Psicología revisan el alcance realmente conservado con sus permisos y competencia. Consulta el estado, la versión y los requisitos técnicos del conocimiento.</p>
        </x-ui.section-card>
    @endif
    </div>
    @if($detalleAbierto && ($regla || $detalleRegistro))
        <x-ui.drawer-livewire wire:model="detalleAbierto" :title="$regla ? 'Detalle de regla' : 'Detalle de conocimiento'" subtitle="Consulta de la versión seleccionada" close-method="cerrarDetalle" size="xl" badge="SOLO CONSULTA" icon="ph-book-open">
            @if($fichaDato)
                @include('apoyodecision::livewire.superadministrador.sistema-experto.ficha-conectada')
            @endif
        </x-ui.drawer-livewire>
    @endif
</div>
