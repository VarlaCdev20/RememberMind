<div class="rm-lab rm-knowledge-workspace space-y-5 text-rm-primary" x-data="{ ultimoDetalle: null }" x-on:click.capture="if ($event.target.closest('[data-expert-detail]')) ultimoDetalle = $event.target.closest('[data-expert-detail]')" x-on:expert-detail-closed.window="$nextTick(() => ultimoDetalle?.focus())">
    <header class="rm-card rm-expert-admin-header"><div class="rm-expert-admin-header__title"><span><i class="ph ph-graph" aria-hidden="true"></i></span><div><p class="rm-expert-eyebrow">RememberMind · Superadministración</p><h1>Sistema experto</h1><p>Conocimiento que acompaña. Decisiones que cuidan.</p></div></div><div class="rm-expert-admin-header__route" aria-label="Recorrido del sistema experto"><span><i class="ph ph-books" aria-hidden="true"></i>Conocimiento</span><i class="ph ph-arrow-right" aria-hidden="true"></i><span><i class="ph ph-link" aria-hidden="true"></i>Fuentes</span><i class="ph ph-arrow-right" aria-hidden="true"></i><span><i class="ph ph-users" aria-hidden="true"></i>Residentes</span></div></header>
    <div class="rm-expert-disclosure" role="note"><i class="ph ph-shield-check" aria-hidden="true"></i><div><strong>Consulta de conocimiento · activación clínica pendiente</strong><p>Un estado técnico activo no acredita por sí mismo aprobación ni publicación clínica. La consulta conserva las versiones originales.</p></div></div>
    @if(in_array($seccion, ['laboratorio', 'casos', 'resultados'], true))
        <div class="rm-alert rm-alert-info" role="note"><div><strong>SIMULACIÓN TÉCNICA — NO VÁLIDA PARA DECISIONES ASISTENCIALES</strong><p>Los casos son ficticios. Los hallazgos descriptivos y los estados experimentales Petersen son contratos diferentes; no representan un diagnóstico.</p></div></div>
    @endif
    <nav class="rm-lab-nav" aria-label="Secciones del sistema experto">
        @can('consultarGobierno', \App\Models\VersionModeloExperto::class)<a href="{{ route('admin.sistema-experto.reglas') }}" class="rm-btn rm-btn-secondary">Gobierno de reglas</a>@endcan
        @foreach(['gobierno' => 'Resumen', 'conocimiento' => 'Conocimiento', 'residentes' => 'Residentes y fuentes', 'laboratorio' => 'Laboratorio', 'casos' => 'Casos sintéticos', 'resultados' => 'Resultados', 'verificacion' => 'Verificación y auditoría'] as $id => $nombre)
            <button type="button" wire:click="ir('{{ $id }}')" class="rm-btn {{ $seccion === $id ? 'rm-btn-primary' : 'rm-btn-secondary' }}" @if($seccion === $id) aria-current="page" @endif>{{ $nombre }}</button>
        @endforeach
    </nav>
    <div wire:loading.delay role="status" aria-live="polite" class="rm-lab-pending">Procesando la consulta del sistema experto…</div>
    @if($errorOperacion)<div class="rm-alert rm-alert-danger" role="alert"><div><strong>La evaluación no se completó</strong><p>{{ $errorOperacion }}</p></div></div>@endif
    @if(!$laboratorioDisponible)
        <div class="rm-alert rm-alert-warning" role="status"><div><strong>Ejecución experimental deshabilitada en este entorno.</strong><p>La ejecución web de casos artificiales solo está disponible en local y testing. La consulta autorizada del conocimiento sigue disponible.</p></div></div>
    @endif

    <div class="rm-knowledge-surface space-y-5" wire:key="expert-section-{{ $seccion }}">
    @if($seccion === 'gobierno')
        @include('apoyodecision::livewire.superadministrador.sistema-experto.gobierno')
    @elseif($seccion === 'laboratorio')
        <h2 class="rm-expert-section-title">Laboratorio del sistema experto</h2>
        <div class="rm-lab-grid">
            <x-ui.section-card title="Ejecutar un caso sintético" subtitle="La evaluación usa las versiones declaradas por el motor experimental.">
                @include('livewire.superadministrador.sistema-experto.ejecutar')
            </x-ui.section-card>
            <x-ui.section-card title="Cobertura y restricciones">
                <dl class="rm-lab-data">
                    <div><dt>Dominios conceptuales</dt><dd>{{ count($dominios) }} · {{ implode(', ', $dominios) }}</dd></div>
                    <div><dt>Reglas instrumentales experimentales G09</dt><dd>{{ count(\MotorExperto\investigacion\g09\CatalogoReglasG09Experimental::todas()) }} disponibles en el paquete técnico.</dd></div>
                    <div><dt>Versión de ejecución G09</dt><dd>{{ \MotorExperto\investigacion\g09\CatalogoReglasG09Experimental::VERSION }}</dd></div>
                    <div><dt>Pruebas automatizadas publicadas</dt><dd>No disponible. No hay un contrato web de ingestión de resultados de CI.</dd></div>
                </dl>
                <p class="mt-4 text-rm-secondary">Memoria integra su contrato descriptivo D-131. Sus cuatro subcomponentes no disponen de reglas de tarea independientes. Las referencias Petersen son supuestos sintéticos explícitos.</p>
                <button type="button" class="rm-btn rm-btn-secondary mt-4" wire:click="ir('conocimiento')">Consultar reglas y fundamento</button>
            </x-ui.section-card>
        </div>
        @include('livewire.superadministrador.sistema-experto.historial-sesion')
    @elseif($seccion === 'casos')
        @include('livewire.superadministrador.sistema-experto.casos')
    @elseif($seccion === 'resultados')
        @if($informe)
            @include('livewire.superadministrador.sistema-experto.resultado-integral')
        @else
            <x-ui.empty-state title="Aún no hay una evaluación seleccionada" description="Seleccione un caso sintético y ejecute el motor para consultar sus cinco dominios, evidencias y trazabilidad." icon="ph-flask" />
            <button type="button" class="rm-btn rm-btn-primary" wire:click="ir('casos')">Seleccionar un caso sintético</button>
        @endif
    @elseif($seccion === 'conocimiento')
        @include('livewire.superadministrador.sistema-experto.explorador')
    @elseif($seccion === 'residentes')
        @livewire(\App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResidentesConocimientoPanel::class, [], key('superadmin-residentes'))
    @elseif($seccion === 'verificacion')
        <x-ui.section-card title="Pruebas y verificación técnica" subtitle="Las ejecuciones de laboratorio no son una validación profesional independiente.">
            <x-ui.empty-state compact title="No hay informes de pruebas publicados" description="El backend no ofrece resultados de suites, commit, fecha ni fallos de CI para esta pantalla. No se calculan porcentajes a partir de demostraciones." />
            <p class="mt-4">La consulta del conocimiento valida la integridad de la cadena de versiones. Si falla, se suspende la presentación de ese contenido. Las reglas experimentales conservan sus condiciones y soportes reales.</p>
        </x-ui.section-card>
        <x-ui.section-card title="Conservación y auditoría" subtitle="Alcance de los contratos disponibles.">
            <ul class="list-disc pl-5 space-y-3">
                <li>Las seis ejecuciones más recientes de este usuario se conservan temporalmente en esta sesión. Caducan con ella y no constituyen un archivo institucional.</li>
                <li>El archivo durable, la reproducción histórica y la auditoría de modificaciones están disponibles mediante los contratos técnicos de consola/testing y una base desechable.</li>
                <li>El gobierno institucional de reglas está en su pantalla propia: Medicina y Psicología gestionan y revisan; Superadministración consulta. El laboratorio conserva su contrato técnico.</li>
                <li>Comparar contenido de versiones no ejecuta casos ni acredita aprobación clínica. El impacto de una versión sobre casos requiere el contrato técnico de consola.</li>
            </ul>
        </x-ui.section-card>
        @include('livewire.superadministrador.sistema-experto.historial-sesion')
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
