<div class="rm-knowledge-summary" aria-label="Inventario del conocimiento seleccionado">
    @foreach(['nodos_semanticos' => ['Conceptos', 'ph-circles-three-plus'], 'relaciones_semanticas' => ['Relaciones declaradas', 'ph-git-branch'], 'reglas_expertas' => ['Reglas del paquete', 'ph-tree-structure'], 'fuentes_datos_expertas' => ['Fuentes declaradas', 'ph-books']] as $tabla => [$etiqueta, $icono])
        <button type="button" wire:click="explorarCategoria('{{ $tabla }}')" wire:loading.attr="disabled" class="rm-card rm-knowledge-metric" @disabled(! $conocimiento)><span class="rm-knowledge-metric__icon"><i class="ph {{ $icono }}" aria-hidden="true"></i></span><span><span class="block">{{ $etiqueta }}</span><strong>{{ $conocimiento ? count($conocimiento['tablas'][$tabla] ?? []) : 'No disponible' }}</strong><small>{{ $version === null ? 'Paquete experimental · sin publicación clínica' : 'Versión seleccionada · consulta documental' }}</small><span class="rm-knowledge-metric__action">Explorar {{ mb_strtolower($etiqueta) }} <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></span></button>
    @endforeach
</div>
<div class="rm-knowledge-overview">
    <x-ui.section-card title="Conocimiento bajo control" subtitle="Comprenda qué existe y hasta dónde puede utilizarse." icon="ph-shield-check">
        <ol class="rm-knowledge-steps">
            <li><i class="ph ph-stack" aria-hidden="true"></i><div><strong>Seleccione una versión</strong><p>Separe el conocimiento almacenado del paquete de laboratorio.</p></div></li>
            <li><i class="ph ph-graph" aria-hidden="true"></i><div><strong>Explore conceptos y relaciones</strong><p>Consulte sus tipos, definiciones y fuentes sin ejecutar una evaluación.</p></div></li>
            <li><i class="ph ph-lock-key" aria-hidden="true"></i><div><strong>Respete su alcance</strong><p>Los estados del catálogo no autorizan decisiones asistenciales.</p></div></li>
        </ol>
        <button type="button" class="rm-btn rm-btn-primary mt-4" wire:click="ir('conocimiento')">Explorar conocimiento<i class="ph ph-arrow-right" aria-hidden="true"></i></button>
        <button type="button" class="rm-btn rm-btn-secondary mt-3" wire:click="explorarRed"><i class="ph ph-graph" aria-hidden="true"></i>Abrir grafo semántico</button>
<<<<<<< HEAD
=======
        <button type="button" class="rm-btn rm-btn-secondary mt-3" wire:click="ir('residentes')"><i class="ph ph-users-three" aria-hidden="true"></i>Consultar residentes y sus fuentes</button>
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
    </x-ui.section-card>
    <x-ui.section-card title="Versiones y procedencia" subtitle="Elija el contenido que desea inspeccionar." icon="ph-stack">
        @forelse($versiones as $v)
            <article class="rm-knowledge-version"><div><strong>{{ $v->nombre ?: $v->codigo_version }}</strong><p>{{ $v->estado }} · {{ $v->fecha_hora_creacion }}</p><small>Antecesora: {{ $v->cod_version_anterior ?? 'No declarada' }}</small></div><button type="button" class="rm-btn rm-btn-secondary" wire:click="seleccionarVersion(@js($v->cod_version_modelo))" aria-pressed="{{ $version === $v->cod_version_modelo ? 'true' : 'false' }}">Consultar<span class="sr-only"> {{ $v->codigo_version }}</span></button></article>
        @empty
            <x-ui.empty-state compact title="No hay versiones cargadas" description="Puede explorar el paquete experimental incluido en el motor. No acredita aprobación clínica." icon="ph-stack" />
        @endforelse
        @if($versiones->hasPages()){{ $versiones->links() }}@endif
        <button type="button" class="rm-btn rm-btn-secondary mt-3" wire:click="seleccionarVersion('')" aria-pressed="{{ $version === null ? 'true' : 'false' }}">Consultar paquete experimental</button>
    </x-ui.section-card>
    <aside class="rm-card rm-knowledge-scope"><h2 class="rm-expert-section-title">Alcance de esta consulta</h2><p><i class="ph ph-eye" aria-hidden="true"></i>Superadministración consulta el conocimiento.</p><p><i class="ph ph-flask" aria-hidden="true"></i>Laboratorio separado de los expedientes.</p><p><i class="ph ph-file-lock" aria-hidden="true"></i>No se puede acreditar aprobación clínica desde los contadores.</p><button type="button" wire:click="ir('verificacion')" class="rm-btn rm-btn-secondary">Ver límites y verificación</button></aside>
</div>
@if($errorConocimiento)<div class="rm-alert rm-alert-danger" role="alert"><div><strong>Error de integridad del conocimiento</strong><p>{{ $errorConocimiento }}</p><button type="button" wire:click="ir('conocimiento')" class="rm-btn rm-btn-secondary">Seleccionar otra versión</button></div></div>@endif
