<div class="rm-expert-results text-rm-primary" x-data="{}"
    x-on:expert-trace-opened.window="$nextTick(() => document.getElementById('drawer-trazabilidadabierta-title')?.focus())"
    x-on:expert-trace-closed.window="$nextTick(() => $refs.traceTrigger?.focus())">
    <header class="rm-expert-heading">
        <div class="min-w-0"><h1>Evaluación neurocognitiva</h1></div>
        <a class="rm-expert-back" href="{{ $psicologia ? route('admin.psicologia.dashboard') : ($enfermeria ? route('admin.enfermeria.pacientes', ['residente' => $codResidente]) : route('admin.medico.residente.ficha', ['adulto' => $codResidente])) }}"><i class="ph ph-arrow-left" aria-hidden="true"></i>{{ $psicologia ? 'Volver a Psicología' : ($enfermeria ? 'Volver a Mis residentes' : 'Volver a la ficha') }}</a>
    </header>
    @can('consultarGobierno', \App\Models\VersionModeloExperto::class)<a href="{{ route('admin.sistema-experto.reglas') }}" class="rm-btn rm-btn-secondary">Consultar reglas</a>@endcan
    @can('consultarOriginales', [\App\Models\ValoracionPetersenOriginal::class, $residente])<a href="{{ route('admin.sistema-experto.petersen-original', $residente) }}" class="rm-btn rm-btn-secondary">Valoraciones originales de Petersen</a>@endcan
    @can('aplicaciones_instrumento.crear')
        <button type="button" wire:click="abrirCapturaInstrumental" wire:loading.attr="disabled" class="rm-btn rm-btn-primary">Registrar mediciones</button>
        @livewire('valoraciones.evaluacion-geriatrica-modal', ['cod_residente' => $codResidente], key('captura-resultados-'.$codResidente))
    @endcan
    <p wire:loading role="status" class="text-sm">Consultando la valoración y sus fuentes…</p>
    <section class="rm-card rm-expert-context" aria-label="Contexto del residente">
        <div class="rm-expert-context-grid">
            <div class="min-w-0"><h2 class="rm-expert-resident-name">{{ $residente->nombre_completo }}</h2><p class="text-sm">{{ $residente->edad !== null ? $residente->edad.' años' : 'Edad no disponible' }}</p></div>
            <dl><dt>Fecha de evaluación</dt><dd>{{ $datos['evaluacion']['fecha'] ?? ($errorLectura ? 'No disponible' : 'Sin evaluación disponible') }}</dd></dl>
            <dl><dt>Profesional solicitante</dt><dd>{{ $datos['evaluacion']['solicitante'] ?? 'No disponible' }}</dd></dl>
            <dl><dt>{{ $simulacion ? 'Estado del resultado' : 'Revisión profesional' }}</dt><dd>{{ $estadoRevision ?? 'Sin valoración cognitiva disponible para revisión' }}</dd></dl>
        </div>
    </section>
    @if($errorObservacional)
        <x-ui.section-card title="No se pudieron consultar los hallazgos observacionales" icon="ph-warning-circle"><p>Vuelva a intentar la consulta.</p><button type="button" wire:click="reintentar" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary mt-3">Reintentar</button></x-ui.section-card>
    @elseif($hallazgosObservacionales)
        @include('apoyodecision::livewire.medico.resultados-experto.hallazgos-observacionales')
        @if($revisionesObservacionales)
            @include('apoyodecision::livewire.medico.resultados-experto.revision-profesional', ['revisiones' => $revisionesObservacionales, 'esRevisionObservacional' => true])
        @endif
    @endif
    @if($errorInstrumental)
        <x-ui.section-card title="No se pudieron consultar las mediciones instrumentales" icon="ph-warning-circle"><p>Vuelva a intentar la consulta.</p><button type="button" wire:click="reintentar" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary mt-3">Reintentar</button></x-ui.section-card>
    @elseif($capturaInstrumental && $capturaInstrumental['disponible'])
        @include('apoyodecision::livewire.medico.resultados-experto.perfil-instrumental')
        @include('apoyodecision::livewire.medico.resultados-experto.captura-instrumental')
    @endif
    @if($errorLectura)
        <x-ui.section-card title="No se pudo consultar la evaluación" icon="ph-warning-circle">
            <p>La información no está disponible en este momento. Vuelva a intentar la consulta.</p>
            <button type="button" wire:click="reintentar" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary mt-4">Reintentar</button>
        </x-ui.section-card>
    @elseif($datos && $informe)
        @php($narrativa = $informe['narrativa'])
        @include('apoyodecision::livewire.medico.resultados-experto.informe-narrativo')
        @include('livewire.medico.resultados-experto.historial')
        @if(isset($revisiones['PSICOLOGIA']['estado'], $revisiones['MEDICINA']['estado']))
            @include('apoyodecision::livewire.medico.resultados-experto.revision-profesional', ['esRevisionObservacional' => false])
        @endif
        @if($puedeAuditar && $datos['evaluacion'])<div><button type="button" x-ref="traceTrigger" wire:click="verTrazabilidad" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary">Consultar auditoría técnica</button></div>@endif
        <p class="text-sm text-rm-secondary">{{ $informe['limite_global'] }} {{ $informe['limite_revision'] }}</p>
        @if($simulacion)
            <p class="text-sm text-rm-secondary" role="note">{{ $informe['procedencia_visible'] }}</p>
        @endif
    @endif
    @if($puedeAuditar && $datos && $trazabilidadAbierta)
        <x-ui.drawer-livewire wire:model="trazabilidadAbierta" title="Auditoría técnica de la evaluación" size="xl" closeMethod="cerrarTrazabilidad">
            <p class="text-sm break-words">Evaluación {{ $datos['evaluacion']['id'] ?? 'No disponible' }} · Versión {{ $datos['evaluacion']['version'] ?? 'No disponible' }}</p>
            @php($detalle = $datos['detalle'])
            @if($simulacion)
                @include('apoyodecision::livewire.medico.resultados-experto.traza-simulacion', ['abierto' => true])
                @include('apoyodecision::livewire.medico.resultados-experto.instantanea-simulacion')
            @else
                @include('livewire.medico.resultados-experto.fundamento', ['abierto' => true])
            @endif
        </x-ui.drawer-livewire>
    @endif
</div>
