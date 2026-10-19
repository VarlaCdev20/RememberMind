<div class="space-y-6 text-rm-primary">
    <x-ui.page-header title="Conocimiento y reglas" subtitle="Versiones, autoría, archivo, sustitución y revisión del conocimiento." icon="ph-graph" />
    @if($mensaje)<div class="rm-alert rm-alert-success" role="status">{{ $mensaje }}</div>@endif
    @error('gobierno')<div class="rm-alert rm-alert-danger" role="alert">{{ $message }}</div>@enderror
    <x-ui.section-card title="Versión del conocimiento">
        @if($puedeRegistrar)
            <div class="mb-4 space-y-2">
                <button type="button" class="rm-btn rm-btn-secondary" wire:click="registrarInstitucional" wire:confirm="Registrar el contrato institucional A–D adoptado, sin modificar los registros de residentes ni firmar revisiones profesionales. Si ya existe, se abrirá su versión original." wire:loading.attr="disabled">Registrar base institucional adoptada</button>
                <p class="text-sm text-rm-secondary">Las observaciones se conservan como tales. Las reglas instrumentales requieren sus antecedentes originales; no se completan puntuaciones ni resultados.</p>
            </div>
        @endif
        <label for="gob-version" class="block font-semibold mb-2">Versión que desea consultar</label>
        <select id="gob-version" wire:model.live="versionSeleccionada" class="rm-input w-full">
            <option value="">Seleccione una versión</option>
            @foreach($versiones as $v)<option value="{{ $v->getKey() }}">{{ $v->codigo_version }} · {{ $v->fecha_hora_retiro ? 'Retirada' : $v->estado }}</option>@endforeach
        </select>
        @if($seleccionada)<p class="text-sm mt-3">{{ $seleccionada->motivo_cambio }} · Autor: {{ $seleccionada->cod_usuario_creacion }} · {{ $seleccionada->fecha_hora_creacion }}</p>@endif
        <p class="text-sm text-rm-secondary mt-3">Una regla pendiente puede probarse cuando sus entradas y método estén definidos. La revisión del conocimiento se registra por separado de la revisión del resultado de un residente.</p>
    </x-ui.section-card>

    <x-ui.section-card title="Reglas de la versión" subtitle="El archivo y la sustitución conservan una versión nueva; las definiciones anteriores siguen consultables.">
        <div class="overflow-x-auto">
            <table class="w-full text-sm"><caption class="sr-only">Reglas, decisiones y sucesoras</caption>
                <thead><tr><th scope="col" class="text-left p-3">Regla</th><th scope="col" class="text-left p-3">Autoría y revisión</th><th scope="col" class="text-left p-3">Estado e historia</th><th scope="col" class="text-left p-3">Acciones</th></tr></thead>
                <tbody>@forelse($reglas as $r)
                    <tr class="border-t"><td class="p-3"><strong>{{ $r->nombre }}</strong><p>{{ $r->codigo_regla }}</p><p>{{ $r->descripcion }}</p></td>
                        <td class="p-3">Propuesta: {{ $r->usuarioCreacion?->correo ?? $r->cod_usuario_creacion }}<p>{{ $r->fecha_hora_creacion }}</p><p>Revisión: {{ $r->estado_revision ?? 'Sin revisión registrada' }}</p>
                            @if($r->cod_usuario_revision)<p>{{ $r->usuarioRevision?->correo ?? $r->cod_usuario_revision }} · {{ $r->fecha_hora_revision }}</p><p>{{ $r->justificacion_revision }}</p>@endif
                        </td>
                        <td class="p-3">{{ $r->estado }}<p>Anterior: {{ $r->reglaAnterior?->codigo_regla ?? 'Sin antecesora' }}</p>@foreach($r->reglasSucesoras as $s)<p>Sucesora: {{ $s->codigo_regla }} · {{ $s->estado }}</p>@endforeach</td>
                        <td class="p-3 space-y-2">
                            @if($puedeGestionar)<button type="button" class="rm-btn rm-btn-secondary" wire:click="prepararPrueba(@js($r->getKey()))">Probar condiciones<span class="sr-only"> {{ $r->codigo_regla }}</span></button>@endif
                            @if($puedeGestionar && $r->estado === 'ACTIVO')<button type="button" class="rm-btn rm-btn-secondary" wire:click="prepararArchivo(@js($r->getKey()))">Archivar o sustituir<span class="sr-only"> {{ $r->codigo_regla }}</span></button>@endif
                            @if($puedeRevisar && $r->estado === 'ACTIVO' && !in_array($r->estado_revision, ['APROBADA','RECHAZADA'], true))<button type="button" class="rm-btn rm-btn-secondary" wire:click="prepararRevision(@js($r->getKey()))">Revisar regla<span class="sr-only"> {{ $r->codigo_regla }}</span></button>@endif
                        </td>
                    </tr>
                @empty<tr><td colspan="4" class="p-4">Seleccione una versión con reglas para consultar su definición e historia.</td></tr>@endforelse</tbody>
            </table>
        </div>
        {{ $reglas->links() }}
    </x-ui.section-card>

    @if($puedeGestionar && $reglaPrueba)
        <x-ui.section-card title="Prueba de condiciones" subtitle="Las entradas de esta prueba no se guardan como datos de un residente ni aprueban la regla.">
            <form wire:submit="probarCondiciones" class="space-y-3">
                @foreach($variablesPrueba as $v)
                    <div><label for="gob-prueba-{{ $v['cod_variable_experta'] }}">{{ $nombresNodos[$v['cod_nodo_semantico']] ?? $v['cod_variable_experta'] }}</label>
                        <select id="gob-prueba-{{ $v['cod_variable_experta'] }}" wire:model.live="entradaPrueba.{{ $v['cod_variable_experta'] }}" class="rm-input w-full">
                            <option value="">Sin dato</option>
                            @foreach($tablas['valores_semanticos'] ?? [] as $valor)@if($valor['cod_dominio_valores'] === $v['cod_dominio_valores'])<option value="{{ $valor['cod_valor_semantico'] }}">{{ $valor['nombre'] }}</option>@endif @endforeach
                        </select>
                    </div>
                @endforeach
                <button type="submit" class="rm-btn rm-btn-primary" wire:loading.attr="disabled">Evaluar condiciones</button>
                <button type="button" wire:click="cancelarSeleccion" class="rm-btn rm-btn-secondary">Cerrar prueba</button>
            </form>
            @if($resultadoPrueba)
                <div role="status" class="mt-4"><strong>{{ $resultadoPrueba['estado'] === 'NO_EVALUABLE' ? 'Faltan datos para evaluar estas condiciones.' : ($resultadoPrueba['cumple'] ? 'Se cumplen todas las condiciones con estas entradas.' : 'Estas entradas no cumplen todas las condiciones.') }}</strong>
                    <ul class="list-disc pl-5 mt-2">@foreach($resultadoPrueba['condiciones'] as $c)<li>{{ $c['condicion'] }}: {{ ['CUMPLE' => 'Cumple', 'NO_CUMPLE' => 'Sin soporte coincidente', 'NO_EVALUABLE' => 'Sin dato'] [$c['estado']] }}</li>@endforeach</ul>
                    <p class="text-sm mt-2">No se ha creado una evaluación ni una decisión de revisión.</p>
                </div>
            @endif
        </x-ui.section-card>
    @endif

    @if($puedeGestionar)
        <x-ui.section-card title="Crear versión sucesora" subtitle="La versión anterior se retirará de nuevas ejecuciones y conservará su historia.">
            <form wire:submit="crearSucesora" class="space-y-4">
                @if($reglaArchivo)<p role="status">Regla seleccionada para archivo: {{ $reglaArchivo }}. Puede incorporar su sustituta en esta misma revisión.</p><button type="button" wire:click="cancelarSeleccion" class="rm-btn rm-btn-secondary">Cancelar selección</button>@endif
                <div><label for="gob-codigo" class="block font-semibold">Código de nueva versión</label><input id="gob-codigo" wire:model="codigoVersion" class="rm-input w-full" maxlength="30" placeholder="ORION-REVISION-2" required>@error('codigoVersion')<p role="alert">{{ $message }}</p>@enderror</div>
                <div><label for="gob-motivo" class="block font-semibold">Motivo de cambio o archivo</label><textarea id="gob-motivo" wire:model="motivoCambio" class="rm-input w-full" rows="3" minlength="10" maxlength="5000" required></textarea>@error('motivoCambio')<p role="alert">{{ $message }}</p>@enderror</div>
                <label class="flex items-center gap-2"><input type="checkbox" wire:model.live="agregarRegla">Incorporar una regla nueva o sustituta</label>
                @if($agregarRegla)
                    @if($contratoObservacional)<p class="text-sm">Este ejecutor admite un indicador afirmativo por regla y conserva su consecuencia descriptiva. Seleccione el valor «SI» y el criterio correspondiente al indicador.</p>@endif
                    <fieldset class="space-y-3"><legend class="font-semibold">Definición de regla</legend>
                        @foreach(['codigo' => 'Código de regla', 'nombre' => 'Nombre', 'descripcion' => 'Descripción', 'fundamento' => 'Fundamento y fuente'] as $key => $label)
                            <div><label for="gob-nueva-{{ $key }}">{{ $label }}</label><input id="gob-nueva-{{ $key }}" wire:model="nueva.{{ $key }}" class="rm-input w-full" required></div>
                        @endforeach
                        @foreach($nueva['condiciones'] as $i => $c)
                            <div class="grid gap-3 md:grid-cols-2" wire:key="gob-condicion-{{ $i }}">
                                <div><label for="gob-var-{{ $i }}">Variable de condición {{ $i+1 }}</label><select id="gob-var-{{ $i }}" wire:model="nueva.condiciones.{{ $i }}.variable" class="rm-input w-full" required><option value="">Seleccione una variable</option>@foreach($tablas['variables_expertas'] ?? [] as $v)<option value="{{ $v['cod_variable_experta'] }}">{{ $nombresNodos[$v['cod_nodo_semantico']] ?? $v['cod_variable_experta'] }}</option>@endforeach</select></div>
                                <div><label for="gob-val-{{ $i }}">Valor esperado {{ $i+1 }}</label><select id="gob-val-{{ $i }}" wire:model="nueva.condiciones.{{ $i }}.valor" class="rm-input w-full" required><option value="">Seleccione un valor</option>@foreach($tablas['valores_semanticos'] ?? [] as $v)<option value="{{ $v['cod_valor_semantico'] }}">{{ $v['nombre'] }}</option>@endforeach</select></div>
                            </div>
                            @if(count($nueva['condiciones']) > 1)<button type="button" wire:click="quitarCondicion({{ $i }})" class="rm-btn rm-btn-secondary">Quitar condición {{ $i+1 }}</button>@endif
                        @endforeach
                        @unless($contratoObservacional)<button type="button" wire:click="agregarCondicion" class="rm-btn rm-btn-secondary">Añadir condición</button>@endunless
                        <div><label for="gob-criterio">Criterio de la consecuencia</label><select id="gob-criterio" wire:model="nueva.criterio_dominio" class="rm-input w-full" required><option value="">Seleccione un criterio</option>@foreach($tablas['criterios_dominios_resultado'] ?? [] as $c)<option value="{{ $c['cod_criterio_dominio_resultado'] }}">{{ $nombresNodos[$c['cod_nodo_criterio']] ?? $c['cod_nodo_criterio'] }}</option>@endforeach</select></div>
                        <div><label for="gob-consecuencia">Valor de la consecuencia</label><select id="gob-consecuencia" wire:model="nueva.valor_consecuencia" class="rm-input w-full" required><option value="">Seleccione un valor</option>@foreach($tablas['valores_semanticos'] ?? [] as $v)<option value="{{ $v['cod_valor_semantico'] }}">{{ $v['nombre'] }}</option>@endforeach</select></div>
                    </fieldset>
                @endif
                <button type="submit" class="rm-btn rm-btn-primary" wire:loading.attr="disabled">Guardar versión sucesora</button>
            </form>
        </x-ui.section-card>
    @endif

    @if($puedeRevisar && $reglaRevision)
        <x-ui.section-card title="Registrar revisión de regla">
            <form wire:submit="guardarRevision" class="space-y-3">
                <p>Regla: {{ $reglaRevision }}. La decisión quedará vinculada a su cuenta.</p>
                <div><label for="gob-decision">Decisión</label><select id="gob-decision" wire:model="decision" class="rm-input w-full"><option value="APROBADA">Aprobar regla</option><option value="RECHAZADA">Rechazar regla</option></select></div>
                <div><label for="gob-justificacion">Justificación de la revisión</label><textarea id="gob-justificacion" wire:model="justificacion" class="rm-input w-full" rows="3" maxlength="5000" required></textarea>@error('justificacion')<p role="alert">{{ $message }}</p>@enderror</div>
                <button type="submit" class="rm-btn rm-btn-primary" wire:loading.attr="disabled">Registrar decisión</button>
                <button type="button" wire:click="cancelarSeleccion" class="rm-btn rm-btn-secondary">Cancelar revisión</button>
            </form>
        </x-ui.section-card>
    @endif
    <div wire:loading.delay role="status" aria-live="polite">Procesando la operación…</div>
</div>
