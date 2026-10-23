<div class="space-y-6 text-rm-primary">
    <x-ui.page-header title="Conocimiento y reglas" subtitle="Versiones, autoría, archivo, sustitución y revisión del conocimiento." icon="ph-graph" />
    @can('consultar', \App\Models\VersionModeloExperto::class)
        <nav class="rm-expert-local-tabs" aria-label="Consulta conectada del sistema experto"><a href="{{ route('admin.sistema-experto.index') }}"><i class="ph ph-graph" aria-hidden="true"></i>Abrir explorador conectado</a><a href="{{ route('admin.sistema-experto.residentes') }}"><i class="ph ph-users-three" aria-hidden="true"></i>Consultar residentes</a><span class="rm-expert-origin">Superadministración · consulta completa</span></nav>
    @endcan
    @if($mensaje)<div class="rm-alert rm-alert-success" role="status">{{ $mensaje }}</div>@endif
    @error('gobierno')<div class="rm-alert rm-alert-danger" role="alert">{{ $message }}</div>@enderror
    <x-ui.section-card title="Versión del conocimiento">
        @if($puedeRegistrar)
            <div class="mb-4 space-y-2">
                <button type="button" class="rm-btn rm-btn-secondary" wire:click="registrarInstitucional" wire:confirm="Registrar el contrato institucional A–D adoptado, sin modificar los registros de residentes ni firmar revisiones profesionales. Si ya existe, se abrirá su versión original." wire:loading.attr="disabled">Registrar base institucional adoptada</button>
                <button type="button" class="rm-btn rm-btn-secondary" wire:click="registrarPetersen" wire:loading.attr="disabled">Registrar patrones de Petersen adoptados</button>
                <p class="text-sm text-rm-secondary">Las observaciones se conservan como tales. Las reglas instrumentales requieren sus antecedentes originales; no se completan puntuaciones ni resultados.</p>
            </div>
        @endif
        <label for="gob-version" class="block font-semibold mb-2">Versión que desea consultar</label>
        <select id="gob-version" wire:model.live="versionSeleccionada" class="rm-select w-full !leading-normal !min-h-11">
            <option value="">Seleccione una versión</option>
            @foreach($versiones as $v)<option value="{{ $v->getKey() }}">{{ $v->codigo_version }} · {{ $v->fecha_hora_retiro ? 'Retirada' : $v->estado }}</option>@endforeach
        </select>
        @if($seleccionada)<p class="text-sm mt-3">{{ $seleccionada->motivo_cambio }} · Autor: {{ $seleccionada->cod_usuario_creacion }} · {{ $seleccionada->fecha_hora_creacion }}</p>@endif
        <p class="text-sm text-rm-secondary mt-3">Una regla pendiente puede probarse cuando sus entradas y método estén definidos. La revisión del conocimiento se registra por separado de la revisión del resultado de un residente.</p>
    </x-ui.section-card>

    <x-ui.section-card title="Guía interdisciplinaria de adquisición" subtitle="Propuesta del 20/10/2026 para completar fuentes originales; no registra una aplicación ni habilita una regla.">
        <div class="space-y-4 text-sm">
            <p>Las observaciones clínicas ya registradas siguen siendo hechos observacionales. Barthel describe actividades básicas; los controles IADL requieren revisar desempeño anterior y actual, oportunidad y motivo de la ayuda. Ninguna de estas fuentes se convierte por su nombre en una puntuación cognitiva.</p>
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div><h3 class="font-semibold">Psicología</h3><p>Identificar instrumento, edición, forma, idioma, derechos de uso, profesional y condiciones. Conservar respuestas y mediciones originales por tarea. Interpretar cada componente solo con un método y una referencia aplicables.</p></div>
                <div><h3 class="font-semibold">Medicina</h3><p>Revisar cronología, cambios cognitivos, antecedentes, medicamentos y posibles causas alternativas. Distinguir factores presentes, ausentes evaluados y pendientes; registrar su juicio profesional posterior.</p></div>
                <div><h3 class="font-semibold">Enfermería</h3><p>Conservar episodios con fecha, autor, turno, oportunidad y contexto. Distinguir lo observado, lo que no ocurrió durante una observación suficiente y lo no evaluado, sin inventar mediciones.</p></div>
                <div><h3 class="font-semibold">Funcionalidad</h3><p>Comparar autonomía previa y actual en cada actividad instrumental, oportunidad real de realizarla y causa de la ayuda. La rutina de la residencia no demuestra por sí misma pérdida de capacidad.</p></div>
            </div>
            <p>Perfiles de captura instrumental configurados: <strong>{{ $perfilesInstrumentalesConfigurados }}</strong>. Perfiles interpretativos configurados: <strong>{{ $perfilesInterpretativosConfigurados }}</strong>. Estos conteos no prueban que exista una aplicación admisible para un residente.</p>
            <p>MoCA Full 8.1 en español, SPMSQ-VE y MMSE-2 son opciones de tamizaje a verificar, no equivalencias automáticas con los 19 componentes. La referencia publicada de SPMSQ-VE estudió personas desde los 65 años; su pertinencia para residentes de 60–64 requiere otra fuente. No se reproducen reactivos en esta pantalla. La <a class="underline" href="https://www.mocacognition.com/paper" target="_blank" rel="noopener noreferrer">política oficial de MoCA<span class="sr-only"> (abre en otra pestaña)</span></a> exige certificación para interpretar tareas o dominios; integrar contenido en el software requiere verificar los <a class="underline" href="https://mocacognition.com/terms-of-use" target="_blank" rel="noopener noreferrer">derechos de uso<span class="sr-only"> (abre en otra pestaña)</span></a>.</p>
            @if($puedeAbrirResidentes)
                <a class="rm-btn rm-btn-secondary inline-flex" href="{{ route('admin.sistema-experto.residentes') }}">Abrir residentes y fuentes autorizadas</a>
            @endif
            <details>
                <summary class="cursor-pointer font-semibold">Consultar los 19 componentes y métodos candidatos</summary>
                <p class="mt-2 text-rm-secondary">Cada método procede del registro técnico versionado; su presencia aquí no acredita licencia, adopción institucional, aplicación, norma transferible ni resultado clínico. Deben cotejarse la forma original, edad, escolaridad, idioma, condiciones y fuente antes de cualquier interpretación.</p>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-sm"><caption class="sr-only">Métodos de adquisición candidatos por componente cognitivo</caption>
                        <thead><tr><th scope="col" class="text-left p-3">Componente</th><th scope="col" class="text-left p-3">Método candidato</th><th scope="col" class="text-left p-3">Identidad y entradas a cotejar</th><th scope="col" class="text-left p-3">Referencia</th></tr></thead>
                        <tbody>
                            @foreach($guiaAdquisicion as $fila)
                                <tr class="border-t" wire:key="guia-{{ $fila['componente'] }}">
                                    <th scope="row" class="p-3 text-left align-top">{{ $fila['componente'] }}</th>
                                    <td class="p-3 align-top"><strong>{{ $fila['instrumento'] }}</strong><br><span class="break-all">{{ $fila['metodo'] }}</span></td>
                                    <td class="p-3 align-top">
                                        @foreach($fila['identidad'] as $nombre => $valor)@if(is_scalar($valor))<span class="block">{{ str_replace('_', ' ', $nombre) }}: {{ $valor }}</span>@endif @endforeach
                                        <span class="block mt-1">Entradas: {{ implode(', ', $fila['entradas']) ?: 'Según protocolo original' }}</span>
                                        @if($fila['edad_minima'] !== null || $fila['edad_maxima'] !== null)<span class="block">Edad de la referencia: {{ $fila['edad_minima'] ?? 'sin mínimo declarado' }}–{{ $fila['edad_maxima'] ?? 'sin máximo declarado' }} años</span>@endif
                                        @if($fila['escolaridad_minima'] !== null || $fila['escolaridad_maxima'] !== null)<span class="block">Escolaridad de la referencia: {{ $fila['escolaridad_minima'] ?? 'sin mínimo declarado' }}–{{ $fila['escolaridad_maxima'] ?? 'sin máximo declarado' }} años</span>@endif
                                        @if($fila['edades_excluidas'] !== [])<span class="block">Hay edades excluidas por conflicto en la referencia; revisar el registro técnico.</span>@endif
                                    </td>
                                    <td class="p-3 align-top"><a class="underline break-all" href="{{ $fila['fuente'] }}" target="_blank" rel="noopener noreferrer">Fuente del método<span class="sr-only"> {{ $fila['metodo'] }} (abre en otra pestaña)</span></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
    </x-ui.section-card>

    <x-ui.section-card title="Reglas de la versión" subtitle="El archivo y la sustitución conservan una versión nueva; las definiciones anteriores siguen consultables.">
        <div class="rm-expert-scopebar"><div><label for="expert-government-search">Buscar regla por código o nombre</label><input id="expert-government-search" type="search" maxlength="100" wire:model.live.debounce.300ms="buscarRegla" class="rm-input w-full"></div><p role="status">{{ $reglas->total() }} reglas encontradas</p></div>
        <div class="overflow-x-auto">
            <table class="rm-lab-table"><caption class="sr-only">Reglas, decisiones y sucesoras</caption>
                <thead><tr><th scope="col" class="text-left p-3">Regla</th><th scope="col" class="text-left p-3">Autoría y revisión</th><th scope="col" class="text-left p-3">Estado e historia</th><th scope="col" class="text-left p-3">Acciones</th></tr></thead>
                <tbody>@forelse($reglas as $r)
                    <tr class="border-t"><td class="p-3" data-label="Regla"><strong>{{ $r->nombre }}</strong><p>{{ $r->codigo_regla }}</p><p>{{ $r->descripcion }}</p></td>
                        <td class="p-3" data-label="Autoría y revisión">Propuesta: {{ $r->usuarioCreacion?->correo ?? $r->cod_usuario_creacion }}<p>{{ $r->fecha_hora_creacion }}</p><p>Revisión: {{ $r->estado_revision ?? 'Sin revisión registrada' }}</p>
                            @if($r->cod_usuario_revision)<p>{{ $r->usuarioRevision?->correo ?? $r->cod_usuario_revision }} · {{ $r->fecha_hora_revision }}</p><p>{{ $r->justificacion_revision }}</p>@endif
                        </td>
                        <td class="p-3" data-label="Estado e historia">{{ $r->estado }}<p>Anterior: {{ $r->reglaAnterior?->codigo_regla ?? 'Sin antecesora' }}</p>@foreach($r->reglasSucesoras as $s)<p>Sucesora: {{ $s->codigo_regla }} · {{ $s->estado }}</p>@endforeach</td>
                        <td class="p-3 space-y-2" data-label="Acciones">
                            @can('consultar', \App\Models\VersionModeloExperto::class)<a class="rm-btn rm-btn-secondary" href="{{ route('admin.sistema-experto.index', ['version' => $versionSeleccionada, 'categoria' => 'reglas_expertas', 'dato' => $r->getKey()]) }}">Ver definición y conexiones<span class="sr-only"> de {{ $r->codigo_regla }}</span></a>@endcan
                            @if($puedeGestionar)<button type="button" class="rm-btn rm-btn-secondary" wire:click="prepararPrueba(@js($r->getKey()))">Probar condiciones<span class="sr-only"> {{ $r->codigo_regla }}</span></button>@endif
                            @if($puedeGestionar && $r->estado === 'ACTIVO')<button type="button" class="rm-btn rm-btn-secondary" wire:click="prepararArchivo(@js($r->getKey()))">{{ $contratoPetersen ? 'Archivar regla' : 'Archivar o sustituir' }}<span class="sr-only"> {{ $r->codigo_regla }}</span></button>@endif
                            @if($puedeRevisar && $r->estado === 'ACTIVO' && !in_array($r->estado_revision, ['APROBADA','RECHAZADA'], true))<button type="button" class="rm-btn rm-btn-secondary" wire:click="prepararRevision(@js($r->getKey()))">Revisar regla<span class="sr-only"> {{ $r->codigo_regla }}</span></button>@endif
                        </td>
                    </tr>
                @empty<tr><td colspan="4" class="p-4">Seleccione una versión con reglas para consultar su definición e historia.</td></tr>@endforelse</tbody>
            </table>
        </div>
        {{ $reglas->links() }}
    </x-ui.section-card>

    @if($puedeGestionar && $reglaPrueba)
        <x-ui.section-card title="Prueba de condiciones" subtitle="Las entradas de esta prueba no se guardan como datos de un residente ni aprueban la regla." x-data="{ entradaCambiada: false }" x-on:change="entradaCambiada = true" x-on:reglas-prueba-actualizada.window="entradaCambiada = false">
            <form wire:submit="probarCondiciones" class="space-y-3">
                @foreach($variablesPrueba as $v)
                    <div><label for="gob-prueba-{{ $v['cod_variable_experta'] }}">{{ $nombresNodos[$v['cod_nodo_semantico']] ?? $v['cod_variable_experta'] }}</label>
                        <select id="gob-prueba-{{ $v['cod_variable_experta'] }}" wire:model="entradaPrueba.{{ $v['cod_variable_experta'] }}" class="rm-select w-full !leading-normal !min-h-11">
                            <option value="">Sin dato</option>
                            @foreach($tablas['valores_semanticos'] ?? [] as $valor)@if($valor['cod_dominio_valores'] === $v['cod_dominio_valores'])<option value="{{ $valor['cod_valor_semantico'] }}">{{ $valor['nombre'] }}</option>@endif @endforeach
                        </select>
                    </div>
                @endforeach
                <button type="submit" class="rm-btn rm-btn-primary" wire:loading.attr="disabled">Evaluar condiciones</button>
                <button type="button" wire:click="cancelarSeleccion" class="rm-btn rm-btn-secondary">Cerrar prueba</button>
            </form>
            @if($resultadoPrueba)
                <div role="status" class="mt-4" x-show="!entradaCambiada"><strong>{{ $resultadoPrueba['estado'] === 'NO_EVALUABLE' ? 'Faltan datos para evaluar estas condiciones.' : ($resultadoPrueba['cumple'] ? 'Se cumplen todas las condiciones con estas entradas.' : 'Estas entradas no cumplen todas las condiciones.') }}</strong>
                    <ul class="list-disc pl-5 mt-2">@foreach($resultadoPrueba['condiciones'] as $c)<li>{{ $c['condicion'] }}: {{ ['CUMPLE' => 'Cumple', 'NO_CUMPLE' => 'Sin soporte coincidente', 'NO_EVALUABLE' => 'Sin dato'] [$c['estado']] }}</li>@endforeach</ul>
                    <p class="text-sm mt-2">No se ha creado una evaluación ni una decisión de revisión.</p>
                </div>
            @endif
        </x-ui.section-card>
    @endif

    @if($puedeGestionar)
        <x-ui.section-card title="Crear versión sucesora" subtitle="La versión anterior se retirará de nuevas ejecuciones y conservará su historia.">
            <form wire:submit="crearSucesora" class="space-y-4">
                @if($reglaArchivo)<p role="status">Regla seleccionada para archivo: {{ $reglaArchivo }}. {{ $contratoPetersen ? 'Su definición se conservará en la historia.' : 'Puede incorporar su sustituta en esta misma revisión.' }}</p><button type="button" wire:click="cancelarSeleccion" class="rm-btn rm-btn-secondary">Cancelar selección</button>@endif
                <div><label for="gob-codigo" class="block font-semibold">Código de nueva versión</label><input id="gob-codigo" wire:model="codigoVersion" class="rm-input w-full" maxlength="30" placeholder="ORION-REVISION-2" required>@error('codigoVersion')<p role="alert">{{ $message }}</p>@enderror</div>
                <div><label for="gob-motivo" class="block font-semibold">Motivo de cambio o archivo</label><textarea id="gob-motivo" wire:model="motivoCambio" class="rm-textarea w-full" rows="3" minlength="10" maxlength="5000" required></textarea>@error('motivoCambio')<p role="alert">{{ $message }}</p>@enderror</div>
                @if($contratoPetersen)<p class="text-sm">Esta familia conserva las cuatro definiciones del contrato Petersen adoptado. Puede probarlas, revisarlas, crear una sucesora o archivarlas. Cambiar los criterios requiere una decisión institucional.</p>@else<label class="flex items-center gap-2"><input type="checkbox" wire:model.live="agregarRegla">Incorporar una regla nueva o sustituta</label>@endif
                @if($agregarRegla && !$contratoPetersen)
                    @if($contratoObservacional)<p class="text-sm">Este ejecutor admite un indicador afirmativo por regla y conserva su consecuencia descriptiva. Seleccione el valor «SI» y el criterio correspondiente al indicador.</p>@endif
                    <fieldset class="space-y-3"><legend class="font-semibold">Definición de regla</legend>
                        @foreach(['codigo' => 'Código de regla', 'nombre' => 'Nombre', 'descripcion' => 'Descripción', 'fundamento' => 'Fundamento y fuente'] as $key => $label)
                            <div><label for="gob-nueva-{{ $key }}">{{ $label }}</label><input id="gob-nueva-{{ $key }}" wire:model="nueva.{{ $key }}" class="rm-input w-full" required></div>
                        @endforeach
                        @foreach($nueva['condiciones'] as $i => $c)
                            <div class="grid gap-3 md:grid-cols-2" wire:key="gob-condicion-{{ $i }}">
                                <div><label for="gob-var-{{ $i }}">Variable de condición {{ $i+1 }}</label><select id="gob-var-{{ $i }}" wire:model="nueva.condiciones.{{ $i }}.variable" class="rm-select w-full !leading-normal !min-h-11" required><option value="">Seleccione una variable</option>@foreach($tablas['variables_expertas'] ?? [] as $v)<option value="{{ $v['cod_variable_experta'] }}">{{ $nombresNodos[$v['cod_nodo_semantico']] ?? $v['cod_variable_experta'] }}</option>@endforeach</select></div>
                                <div><label for="gob-val-{{ $i }}">Valor esperado {{ $i+1 }}</label><select id="gob-val-{{ $i }}" wire:model="nueva.condiciones.{{ $i }}.valor" class="rm-select w-full !leading-normal !min-h-11" required><option value="">Seleccione un valor</option>@foreach($tablas['valores_semanticos'] ?? [] as $v)<option value="{{ $v['cod_valor_semantico'] }}">{{ $v['nombre'] }}</option>@endforeach</select></div>
                            </div>
                            @if(count($nueva['condiciones']) > 1)<button type="button" wire:click="quitarCondicion({{ $i }})" class="rm-btn rm-btn-secondary">Quitar condición {{ $i+1 }}</button>@endif
                        @endforeach
                        @unless($contratoObservacional)<button type="button" wire:click="agregarCondicion" class="rm-btn rm-btn-secondary">Añadir condición</button>@endunless
                        <div><label for="gob-criterio">Criterio de la consecuencia</label><select id="gob-criterio" wire:model="nueva.criterio_dominio" class="rm-select w-full !leading-normal !min-h-11" required><option value="">Seleccione un criterio</option>@foreach($tablas['criterios_dominios_resultado'] ?? [] as $c)<option value="{{ $c['cod_criterio_dominio_resultado'] }}">{{ $nombresNodos[$c['cod_nodo_criterio']] ?? $c['cod_nodo_criterio'] }}</option>@endforeach</select></div>
                        <div><label for="gob-consecuencia">Valor de la consecuencia</label><select id="gob-consecuencia" wire:model="nueva.valor_consecuencia" class="rm-select w-full !leading-normal !min-h-11" required><option value="">Seleccione un valor</option>@foreach($tablas['valores_semanticos'] ?? [] as $v)<option value="{{ $v['cod_valor_semantico'] }}">{{ $v['nombre'] }}</option>@endforeach</select></div>
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
                <div><label for="gob-decision">Decisión</label><select id="gob-decision" wire:model="decision" class="rm-select w-full !leading-normal !min-h-11"><option value="APROBADA">Aprobar regla</option><option value="RECHAZADA">Rechazar regla</option></select></div>
                <div><label for="gob-justificacion">Justificación de la revisión</label><textarea id="gob-justificacion" wire:model="justificacion" class="rm-textarea w-full" rows="3" maxlength="5000" required></textarea>@error('justificacion')<p role="alert">{{ $message }}</p>@enderror</div>
                <button type="submit" class="rm-btn rm-btn-primary" wire:loading.attr="disabled">Registrar decisión</button>
                <button type="button" wire:click="cancelarSeleccion" class="rm-btn rm-btn-secondary">Cancelar revisión</button>
            </form>
        </x-ui.section-card>
    @endif
    <div wire:loading.delay role="status" aria-live="polite">Procesando la operación…</div>
</div>
