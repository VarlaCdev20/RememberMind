<x-ui.section-card title="Base de conocimiento" subtitle="Lectura del paquete experimental o de una versión almacenada. La consulta no activa conocimiento.">
    <div class="rm-lab-grid">
        <div>
            <h3 class="font-semibold mb-3">Versión que desea consultar</h3>
            <button type="button" wire:click="seleccionarVersion('')" class="rm-btn rm-btn-secondary mb-3" aria-pressed="{{ $version === null ? 'true' : 'false' }}">Paquete experimental G09 en memoria</button>
            @forelse($versiones as $v)
                <div class="rm-lab-history mb-3">
                    <div class="min-w-0"><strong>{{ $v->nombre ?? $v->codigo_version }}</strong><p class="text-sm">{{ $v->codigo_version }} · {{ $v->estado }}{{ $v->fecha_hora_retiro ? ' · Retirada' : '' }}</p><p class="text-sm text-rm-secondary">{{ $v->fecha_hora_creacion }} · {{ $v->cod_usuario_creacion }}</p><p class="text-sm">Antecesora: {{ $v->cod_version_anterior ?? 'Sin antecesora declarada' }}</p></div>
                    <button type="button" class="rm-btn rm-btn-secondary" wire:click="seleccionarVersion(@js($v->cod_version_modelo))" aria-pressed="{{ $version === $v->cod_version_modelo ? 'true' : 'false' }}">Consultar</button>
                </div>
            @empty
                <p class="text-rm-secondary">No hay versiones almacenadas. Puede consultar el paquete experimental en memoria; no está publicado ni clínicamente aprobado.</p>
            @endforelse
            {{ $versiones->links() }}
        </div>
        <div class="space-y-3">
            <label for="lab-modo" class="block font-semibold">Vista del conocimiento</label>
            <select id="lab-modo" wire:model.live="modoConocimiento" class="rm-input w-full"><option value="reglas">Catálogo de reglas</option><option value="catalogo">Conceptos, variables, fuentes y mapeos</option><option value="red">Red semántica</option><option value="versiones">Comparación de versiones</option></select>
            <p><strong>Origen:</strong> {{ $version ?? 'Paquete G09 experimental incorporado al motor' }}.</p>
            <p class="text-sm text-rm-secondary">Los estados activos y los literales de aprobación del paquete de ingeniería son sintéticos. No representan autorización clínica. Una versión retirada conserva su contenido histórico para lectura.</p>
            <div class="rm-lab-actions"><button type="button" class="rm-btn rm-btn-secondary" disabled aria-describedby="lab-escritura">Crear regla</button><button type="button" class="rm-btn rm-btn-secondary" disabled aria-describedby="lab-escritura">Crear nueva versión</button><button type="button" class="rm-btn rm-btn-secondary" disabled aria-describedby="lab-escritura">Archivar</button></div>
            <p id="lab-escritura" class="text-sm">Operaciones restringidas: el gestor actual exige consola/testing y base desechable. No existe autorización de escritura web. No se permite editar directamente una regla histórica ni borrarla.</p>
        </div>
    </div>
</x-ui.section-card>
@if($errorConocimiento)
    <div class="rm-alert rm-alert-danger" role="alert"><div><strong>Error de integridad del conocimiento</strong><p>{{ $errorConocimiento }}</p></div></div>
@elseif($conocimiento)
    @if($modoConocimiento === 'reglas')
        <x-ui.section-card title="Catálogo de reglas" subtitle="Condiciones y consecuencias del paquete seleccionado, sin duplicar reglas en la interfaz.">
            <div class="rm-lab-filters">
                <div><label for="lab-buscar-reglas">Buscar código, nombre o fundamento</label><input id="lab-buscar-reglas" wire:model.live.debounce.300ms="buscarReglas" type="search" class="rm-input w-full" maxlength="120"></div>
                <div><label for="lab-dominio">Dominio</label><select id="lab-dominio" wire:model.live="dominio" class="rm-input w-full"><option value="">Todos</option>@foreach($dominios as $id => $nombre)<option value="{{ $id }}">{{ $nombre }}</option>@endforeach</select></div>
                <div><label for="lab-estado-regla">Estado</label><select id="lab-estado-regla" wire:model.live="estadoRegla" class="rm-input w-full"><option value="">Todos</option><option value="ACTIVO">Activo</option><option value="INACTIVO">Inactivo</option></select></div>
                <div><label for="lab-subcomponente">Subcomponente</label><select id="lab-subcomponente" wire:model.live="subcomponente" class="rm-input w-full"><option value="">Todos</option>@foreach(array_unique(array_column($conocimiento['reglas'], 'subcomponente')) as $sub)<option value="{{ $sub }}">{{ $sub }}</option>@endforeach</select></div>
            </div>
            <table class="rm-lab-table mt-4"><caption class="sr-only">Reglas de la versión seleccionada</caption><thead><tr><th scope="col">Código y nombre</th><th scope="col">Dominio y subcomponente</th><th scope="col">Versión y estado</th><th scope="col">Fundamento y modificación</th><th scope="col">Acciones</th></tr></thead><tbody>
                @forelse($reglas as $r)
                    <tr><td data-label="Código y nombre"><strong>{{ $r['nombre'] ?? $r['codigo'] }}</strong><p class="text-sm">{{ $r['codigo'] }}</p></td><td data-label="Dominio y subcomponente">{{ $dominios[$r['dominio']] ?? $r['dominio'] }}<p class="text-sm">{{ $r['subcomponente'] }}</p></td><td data-label="Versión y estado">{{ $r['version'] }}<p>{{ $r['estado'] }}</p></td><td data-label="Fundamento y modificación">{{ $r['fundamento'] ?? 'Fundamento no disponible en este contrato' }}<p class="text-sm text-rm-secondary">Última modificación: {{ $r['updated_at'] ?? 'No disponible' }}</p></td><td data-label="Acciones"><button type="button" wire:click="abrirRegla(@js($r['id']))" class="rm-btn rm-btn-secondary">Consultar detalle<span class="sr-only"> de {{ $r['codigo'] }}</span></button></td></tr>
                @empty
                    <tr><td colspan="5"><x-ui.empty-state compact title="Sin reglas que coincidan" description="Revise los filtros o seleccione otra versión del conocimiento." /></td></tr>
                @endforelse
            </tbody></table>
            {{ $reglas->links() }}
        </x-ui.section-card>
        @if($regla && isset($conocimiento['reglas'][$regla]))
            <x-ui.section-card title="Detalle de regla" :subtitle="$conocimiento['reglas'][$regla]['codigo']">
                @include('livewire.superadministrador.sistema-experto.datos', ['datos' => $conocimiento['reglas'][$regla]])
                <p class="mt-4">Pruebas, dependencias históricas y sucesora: no disponibles como consulta web en este contrato. Las condiciones se consultan en su versión original.</p>
            </x-ui.section-card>
        @endif
    @elseif($modoConocimiento === 'catalogo')
        <x-ui.section-card title="Catálogo del conocimiento" subtitle="Filas originales de conceptos, dominios, variables, fuentes, relaciones y mapeos de esta versión.">
            @foreach($conocimiento['tablas'] as $tabla => $filas)
                <details class="rm-expert-source mb-3"><summary>{{ ucfirst(str_replace('_', ' ', $tabla)) }} · {{ count($filas) }} registros</summary>
                    @include('livewire.superadministrador.sistema-experto.datos', ['datos' => $filas])
                </details>
            @endforeach
        </x-ui.section-card>
    @elseif($modoConocimiento === 'red')
        @include('livewire.superadministrador.sistema-experto.red-semantica')
    @elseif($modoConocimiento === 'versiones')
        <x-ui.section-card title="Comparación técnica de versiones" subtitle="Cambios de reglas por código semántico. No ejecuta casos ni acredita validación clínica.">
            <label for="lab-comparada" class="block font-semibold mb-2">Comparar la versión consultada con</label>
            <select id="lab-comparada" wire:model.live="versionComparada" class="rm-input w-full"><option value="">Seleccione una versión almacenada</option>@foreach($versiones as $v)<option value="{{ $v->cod_version_modelo }}">{{ $v->codigo_version }} · {{ $v->estado }}</option>@endforeach</select>
            @forelse($comparacion as $id => $c)
                <details class="rm-expert-source mt-3"><summary>{{ $id }} · {{ $c['estado'] }}</summary><div class="rm-lab-grid mt-3"><div><h3 class="font-semibold">Versión consultada</h3>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $c['antes']])</div><div><h3 class="font-semibold">Versión comparada</h3>@include('livewire.superadministrador.sistema-experto.datos', ['datos' => $c['despues']])</div></div></details>
            @empty
                <p class="mt-4 text-rm-secondary">Seleccione dos versiones disponibles para consultar diferencias. El impacto sobre resultados de casos sigue restringido a las pruebas técnicas de consola.</p>
            @endforelse
        </x-ui.section-card>
    @endif
@endif
