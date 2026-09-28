<div class="rm-clinical-workspace">
    <x-ui.toast />
    <x-ui.page-header
        title="Seguimiento diario"
        subtitle="Observación longitudinal y continuidad del cuidado durante el turno."
        overline="Enfermería y cuidados"
        icon="ph-chart-line-up">
        @can('atenciones.crear')
            <x-ui.action-button variant="primary" size="sm" icono="ph-plus-circle" wire:click="abrirCrear">
                Registrar seguimiento
            </x-ui.action-button>
        @endcan
    </x-ui.page-header>

    <div class="rm-filter-bar">
        <div class="grid gap-3 md:grid-cols-3">
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar adulto mayor" class="rounded-xl border border-borde bg-fondo-panel px-4 py-2 text-sm text-parrafo">
            <select wire:model.live="filtroTurno" class="rounded-xl border border-borde bg-fondo-panel px-4 py-2 text-sm text-parrafo">
                <option value="">Todos los turnos</option>
                @foreach($turnos as $turno)
                    <option value="{{ $turno->cod_turno }}">{{ $turno->nombre }} ({{ substr($turno->hora_inicio, 0, 5) }})</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="filtroFecha" class="rounded-xl border border-borde bg-fondo-panel px-4 py-2 text-sm text-parrafo">
        </div>
    </div>

    <div class="rm-table-container overflow-x-auto">
        <table class="rm-table min-w-[900px]">
            <thead class="bg-fondo-panel text-xs font-black uppercase tracking-wider text-meta">
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Adulto mayor</th>
                    <th class="px-4 py-3">Profesional</th>
                    <th class="px-4 py-3">Estado general</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Observación</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-borde/60">
                @forelse($seguimientos as $seg)
                    <tr class="hover:bg-fondo-hover/60">
                        <td class="px-4 py-3 font-bold text-parrafo">{{ $seg->fecha_hora?->format('d/m/Y') }}<br><span class="text-xs text-apoyo">{{ $seg->fecha_hora?->format('H:i') }}</span></td>
                        <td class="px-4 py-3 font-black text-titulo">{{ $seg->adultoMayor?->nombres }} {{ $seg->adultoMayor?->apellido_paterno }}</td>
                        <td class="px-4 py-3 text-parrafo">{{ $seg->personal?->nombres }} {{ $seg->personal?->apellido_paterno }}</td>
                        <td class="px-4 py-3 text-parrafo">{{ \Illuminate\Support\Str::after((string) $seg->motivo, 'SEGUIMIENTO_DIARIO:') ?: 'No registrado' }}</td>
                        <td class="px-4 py-3 text-parrafo">Seguimiento V2.1</td>
                        <td class="max-w-xs px-4 py-3 text-xs font-medium text-apoyo">{{ \Illuminate\Support\Str::limit($seg->observacion, 120) }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('atenciones.editar')
                                <button type="button" wire:click="abrirEditar('{{ $seg->cod_seg_diario }}')" class="rm-btn-secondary px-3 py-1.5 text-xs font-bold">
                                    Corregir
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-ui.empty-state icono="ph-clipboard-text" titulo="Sin seguimientos" texto="No hay registros para los filtros seleccionados. Ajuste la fecha o registre la observación del turno." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $seguimientos->links() }}

    @if($modalForm)
        <x-ui.drawer-livewire
            wire:model="modalForm"
            size="lg"
            :title="$editandoId ? 'Corregir seguimiento diario' : 'Registrar seguimiento diario'"
            subtitle="Registro observacional del turno con trazabilidad clínica"
            badge="ENFERMERÍA · OBSERVACIÓN LONGITUDINAL"
            icon="ph-clipboard-text"
            close-method="cerrarModales">
            <form wire:submit.prevent="guardar" class="rm-clinical-drawer-form space-y-5">
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="space-y-1 text-xs font-bold text-apoyo">Adulto mayor *
                        <select wire:model.live="codResidente" @disabled($editandoId) class="rm-select w-full text-sm"><option value="">Seleccione</option>@foreach($adultos as $adulto)<option value="{{ $adulto->cod_residente }}">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</option>@endforeach</select>
                        @error('codResidente')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Turno clínico vigente *
                        <select wire:model="codTurno" disabled class="rm-select w-full text-sm disabled:opacity-70"><option value="">Sin turno vigente</option>@foreach($turnos as $turno)<option value="{{ $turno->cod_turno }}">{{ $turno->nombre }}</option>@endforeach</select>
                        @error('codTurno')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Fecha *<input type="date" max="{{ today()->format('Y-m-d') }}" wire:model="fecha" class="rm-input w-full text-sm">@error('fecha')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Hora de inicio *<input type="time" wire:model="horaInicio" class="rm-input w-full text-sm">@error('horaInicio')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                </div>

                @php
                    $selects = [
                        'estadoGeneral' => ['Estado general *', ['ESTABLE'=>'Estable','VIGILANCIA'=>'En vigilancia','DELICADO'=>'Delicado','CRITICO'=>'Crítico']],
                        'alimentacion' => ['Alimentación *', ['COMPLETA'=>'Completa','PARCIAL'=>'Parcial','RECHAZADA'=>'Rechazada','AYUNO'=>'Ayuno indicado']],
                        'hidratacion' => ['Hidratación *', ['ADECUADA'=>'Adecuada','PARCIAL'=>'Parcial','INSUFICIENTE'=>'Insuficiente','RECHAZADA'=>'Rechazada']],
                        'movilidad' => ['Movilidad *', ['INDEPENDIENTE'=>'Independiente','ASISTIDA'=>'Asistida','SILLA_RUEDAS'=>'Silla de ruedas','ENCAMADO'=>'Encamado']],
                        'higiene' => ['Higiene', [''=>'Sin registrar','COMPLETA'=>'Completa','PARCIAL'=>'Parcial','PENDIENTE'=>'Pendiente','RECHAZADA'=>'Rechazada']],
                        'sueno' => ['Sueño *', ['NORMAL'=>'Normal','INTERRUMPIDO'=>'Interrumpido','INSOMNIO'=>'Insomnio','SOMNOLENCIA'=>'Somnolencia']],
                        'orientacion' => ['Orientación', [''=>'Sin registrar','ORIENTADO'=>'Orientado','PARCIALMENTE_ORIENTADO'=>'Parcialmente orientado','DESORIENTADO'=>'Desorientado']],
                        'conducta' => ['Conducta', [''=>'Sin registrar','TRANQUILO'=>'Tranquilo','ANSIOSO'=>'Ansioso','AGITADO'=>'Agitado','APATICO'=>'Apático']],
                        'participacion' => ['Participación', [''=>'Sin registrar','ACTIVA'=>'Activa','PARCIAL'=>'Parcial','NO_PARTICIPA'=>'No participa']],
                    ];
                @endphp
                <div class="grid gap-3 md:grid-cols-3">
                    <label class="space-y-1 text-xs font-bold text-apoyo">Comida observada *
                        <select wire:model="tipoComida" class="rm-select w-full text-sm">
                            <option value="">Seleccione una opción</option>
                            <option value="DESAYUNO">Desayuno</option><option value="MEDIA_MANANA">Media mañana</option>
                            <option value="ALMUERZO">Almuerzo</option><option value="MERIENDA">Merienda</option>
                            <option value="CENA">Cena</option><option value="COLACION">Colación</option>
                        </select>
                        @error('tipoComida')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    @foreach($selects as $campo => [$etiqueta, $opciones])
                        <label class="space-y-1 text-xs font-bold text-apoyo">{{ $etiqueta }}
                            <select wire:model="{{ $campo }}" class="rm-select w-full text-sm">
                                @unless(array_key_exists('', $opciones))
                                    <option value="">Seleccione una opción</option>
                                @endunless
                                @foreach($opciones as $valor => $texto)<option value="{{ $valor }}">{{ $texto }}</option>@endforeach
                            </select>
                            @error($campo)<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                        </label>
                    @endforeach
                    <label class="space-y-1 text-xs font-bold text-apoyo">Alimentación consumida (%) *
                        <input type="number" min="0" max="100" step="1" wire:model="porcentajeAlimentacion" class="rm-input w-full text-sm">
                        @error('porcentajeAlimentacion')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Tipo de líquido *
                        <input type="text" maxlength="60" wire:model="tipoLiquido" placeholder="Ej.: agua, infusión" class="rm-input w-full text-sm">
                        @error('tipoLiquido')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Cantidad hidratación (ml) *
                        <input type="number" min="1" max="10000" step="1" wire:model="cantidadHidratacionMl" class="rm-input w-full text-sm">
                        @error('cantidadHidratacionMl')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                </div>

                <x-ui.form-section title="Ingesta e hidratación" description="Registre cantidad, tolerancia y seguridad de la deglución." icon="ph-bowl-food" :columns="2">
                    <x-ui.field label="Tolerancia de la ingesta" error="toleranciaIngesta">
                        <select wire:model="toleranciaIngesta" class="rm-select">
                            <option value="">Sin novedad observada</option>
                            <option value="BUENA">Buena</option><option value="REGULAR">Regular</option>
                            <option value="MALA">Mala</option><option value="NAUSEAS">Náuseas</option><option value="VOMITO">Vómito</option>
                        </select>
                    </x-ui.field>
                    <label class="rm-choice-card self-end">
                        <input type="checkbox" wire:model="dificultadDeglucion" class="rm-checkbox">
                        <span><strong>Dificultad para deglutir</strong><small>Marque solo si fue observada durante la ingesta.</small></span>
                    </label>
                </x-ui.form-section>

                <x-ui.form-section title="Movilidad y riesgo de caída" description="Describa la capacidad funcional observada, apoyos y tolerancia." icon="ph-person-simple-walk" :columns="3">
                    <x-ui.field label="Equilibrio" error="equilibrio">
                        <select wire:model="equilibrio" class="rm-select"><option value="">Sin valorar</option><option value="ESTABLE">Estable</option><option value="INESTABLE">Inestable</option><option value="NO_VALORABLE">No valorable</option></select>
                    </x-ui.field>
                    <x-ui.field label="Traslado" error="traslado">
                        <select wire:model="traslado" class="rm-select"><option value="">Sin registrar</option><option value="INDEPENDIENTE">Independiente</option><option value="SUPERVISION">Con supervisión</option><option value="AYUDA_UNA_PERSONA">Ayuda de una persona</option><option value="AYUDA_DOS_PERSONAS">Ayuda de dos personas</option><option value="GRUA">Grúa</option></select>
                    </x-ui.field>
                    <x-ui.field label="Riesgo de caída" error="riesgoCaida">
                        <select wire:model="riesgoCaida" class="rm-select"><option value="">Sin cambio observado</option><option value="BAJO">Bajo</option><option value="MEDIO">Medio</option><option value="ALTO">Alto</option></select>
                    </x-ui.field>
                    <x-ui.field label="Tipo de apoyo" error="tipoApoyo"><input wire:model="tipoApoyo" maxlength="60" class="rm-input" placeholder="Ej.: asistencia de una persona"></x-ui.field>
                    <x-ui.field label="Dispositivo" error="dispositivo"><input wire:model="dispositivo" maxlength="80" class="rm-input" placeholder="Ej.: andador, bastón, silla"></x-ui.field>
                    <x-ui.field label="Fatiga" error="fatiga"><select wire:model="fatiga" class="rm-select"><option value="">Sin registrar</option><option value="SIN_FATIGA">Sin fatiga</option><option value="LEVE">Leve</option><option value="MODERADA">Moderada</option><option value="SEVERA">Severa</option></select></x-ui.field>
                </x-ui.form-section>

                <x-ui.form-section title="Sueño y eliminación" description="Complete únicamente los datos observados o reportados durante el turno." icon="ph-moon-stars" :columns="3">
                    <x-ui.field label="Horas de sueño" error="horasSueno" hint="0–24 h"><input type="number" min="0" max="24" step="0.25" wire:model="horasSueno" class="rm-input"></x-ui.field>
                    <x-ui.field label="Despertares" error="despertares" hint="0–30"><input type="number" min="0" max="30" step="1" wire:model="despertares" class="rm-input"></x-ui.field>
                    <label class="rm-choice-card self-end"><input type="checkbox" wire:model="agitacionNocturna" class="rm-checkbox"><span><strong>Agitación nocturna</strong><small>Conducta observada durante el descanso.</small></span></label>
                    <x-ui.field label="Tipo de eliminación" error="tipoEliminacion"><select wire:model="tipoEliminacion" class="rm-select"><option value="">Sin registro</option><option value="URINARIA">Urinaria</option><option value="INTESTINAL">Intestinal</option><option value="AMBAS">Ambas</option></select></x-ui.field>
                    <x-ui.field label="Cantidad" error="cantidadEliminacion"><input wire:model="cantidadEliminacion" maxlength="40" class="rm-input" placeholder="Ej.: moderada"></x-ui.field>
                    <x-ui.field label="Continencia" error="continencia"><select wire:model="continencia" class="rm-select"><option value="">Sin registrar</option><option value="CONTINENTE">Continente</option><option value="INCONTINENCIA_URINARIA">Incontinencia urinaria</option><option value="INCONTINENCIA_FECAL">Incontinencia fecal</option><option value="DOBLE_INCONTINENCIA">Doble incontinencia</option></select></x-ui.field>
                    <x-ui.field class="md:col-span-3" label="Características de la eliminación" error="caracteristicaEliminacion"><input wire:model="caracteristicaEliminacion" maxlength="120" class="rm-input" placeholder="Color, consistencia y cambios relevantes"></x-ui.field>
                </x-ui.form-section>

                @php $nivelObservado = [''=>'Sin valorar','CONSERVADA'=>'Conservada','ALTERACION_LEVE'=>'Alteración leve','ALTERADA'=>'Alterada','NO_VALORABLE'=>'No valorable']; @endphp
                <x-ui.form-section title="Control cognitivo observacional" description="Observación longitudinal de Enfermería. No constituye diagnóstico cognitivo." icon="ph-brain" :columns="3">
                    @foreach(['orientacionLugar'=>'Orientación en lugar','orientacionTiempo'=>'Orientación en tiempo'] as $campo => $etiqueta)
                        <x-ui.field :label="$etiqueta" :error="$campo"><select wire:model="{{ $campo }}" class="rm-select"><option value="">Sin valorar</option><option value="ORIENTADO">Orientado</option><option value="PARCIALMENTE_ORIENTADO">Parcialmente orientado</option><option value="DESORIENTADO">Desorientado</option></select></x-ui.field>
                    @endforeach
                    @foreach(['memoriaReciente'=>'Memoria reciente','memoriaRemota'=>'Memoria remota'] as $campo => $etiqueta)
                        <x-ui.field :label="$etiqueta" :error="$campo"><select wire:model="{{ $campo }}" class="rm-select">@foreach($nivelObservado as $valor=>$texto)<option value="{{ $valor }}">{{ $texto }}</option>@endforeach</select></x-ui.field>
                    @endforeach
                    <x-ui.field label="Atención" error="atencionCognitiva"><select wire:model="atencionCognitiva" class="rm-select"><option value="">Sin valorar</option><option value="CONSERVADA">Conservada</option><option value="FLUCTUANTE">Fluctuante</option><option value="DISMINUIDA">Disminuida</option><option value="NO_VALORABLE">No valorable</option></select></x-ui.field>
                    <x-ui.field label="Comprensión" error="comprension"><select wire:model="comprension" class="rm-select"><option value="">Sin valorar</option><option value="CONSERVADA">Conservada</option><option value="PARCIAL">Parcial</option><option value="ALTERADA">Alterada</option><option value="NO_VALORABLE">No valorable</option></select></x-ui.field>
                    <x-ui.field label="Lenguaje" error="lenguaje"><select wire:model="lenguaje" class="rm-select"><option value="">Sin valorar</option><option value="CONSERVADO">Conservado</option><option value="LIMITADO">Limitado</option><option value="ALTERADO">Alterado</option><option value="NO_VALORABLE">No valorable</option></select></x-ui.field>
                    <div class="md:col-span-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach(['sigueInstrucciones'=>'Sigue instrucciones','repitePreguntas'=>'Repite preguntas','olvidaIndicaciones'=>'Olvida indicaciones','reconocePersonas'=>'Reconoce personas','reconoceEntorno'=>'Reconoce el entorno','confusionObservable'=>'Confusión observable','cambioCognitivo'=>'Cambio respecto a su línea basal'] as $campo=>$etiqueta)
                            <label class="rm-choice-card"><input type="checkbox" wire:model="{{ $campo }}" class="rm-checkbox"><span><strong>{{ $etiqueta }}</strong></span></label>
                        @endforeach
                    </div>
                </x-ui.form-section>

                <x-ui.form-section title="Conducta y respuesta al cuidado" description="Registre conductas observables, la intervención realizada y su resultado." icon="ph-users-three" :columns="2">
                    <div class="md:col-span-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach(['apatia'=>'Apatía','agitacion'=>'Agitación','agresividad'=>'Agresividad','ansiedad'=>'Ansiedad','aislamiento'=>'Aislamiento','deambulacion'=>'Deambulación','cambioConducta'=>'Cambio de conducta'] as $campo=>$etiqueta)
                            <label class="rm-choice-card"><input type="checkbox" wire:model="{{ $campo }}" class="rm-checkbox"><span><strong>{{ $etiqueta }}</strong></span></label>
                        @endforeach
                    </div>
                    <x-ui.field label="Intervención realizada" error="intervencionConducta"><textarea wire:model="intervencionConducta" maxlength="2000" class="rm-textarea" placeholder="Contención verbal, acompañamiento, redirección u otra medida"></textarea></x-ui.field>
                    <x-ui.field label="Respuesta observada" error="respuestaConducta"><textarea wire:model="respuestaConducta" maxlength="2000" class="rm-textarea" placeholder="Describa la respuesta posterior a la intervención"></textarea></x-ui.field>
                </x-ui.form-section>

                <div class="grid gap-3 rounded-2xl border border-borde bg-fondo-panel p-4 md:grid-cols-3">
                    @foreach(['incidente'=>'Generar alerta de incidente', 'requiereMedico'=>'Generar solicitud de evaluación médica', 'intentoCaminarSolo'=>'Intentó caminar sin ayuda'] as $campo => $etiqueta)
                        <label class="flex items-center gap-2 text-sm font-bold text-parrafo"><x-checkbox wire:model="{{ $campo }}" /> {{ $etiqueta }}</label>
                    @endforeach
                </div>

                <label class="block space-y-1 text-xs font-bold text-apoyo">Observación de enfermería *
                    <textarea wire:model="observacion" rows="4" placeholder="Describa evolución, respuesta a cuidados y novedades del turno." class="rm-input w-full text-sm"></textarea>
                    <span class="block text-[11px] font-medium text-meta">Si existe incidente o solicitud médica, detalle claramente lo ocurrido y las medidas iniciales.</span>
                    @error('observacion')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                </label>
                <div class="rm-form-actions">
                    <button type="button" wire:click="cerrarModales" class="rm-btn-secondary px-4 py-2 text-xs font-bold">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="guardar" class="rm-btn-primary px-4 py-2 text-xs font-black disabled:opacity-50">
                        <span wire:loading.remove wire:target="guardar">{{ $editandoId ? 'Guardar corrección' : 'Registrar seguimiento' }}</span>
                        <span wire:loading wire:target="guardar" class="rm-clinical-loading">Guardando…</span>
                    </button>
                </div>
            </form>
        </x-ui.drawer-livewire>
    @endif
</div>
