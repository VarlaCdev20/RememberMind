<div class="space-y-6">
    <x-validation-errors />
    @if(session('mensaje'))<p role="status">{{ session('mensaje') }}</p>@endif
    <div class="flex flex-col gap-4 rounded-3xl border border-borde bg-fondo-card p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-widest text-meta">Enfermería</p>
            <h1 class="text-2xl font-black text-titulo">Seguimiento diario</h1>
            <p class="text-sm font-semibold text-apoyo">Registro operativo por adulto mayor, turno, plan de cuidado e incidencias.</p>
        </div>
        @can('atenciones.crear')
        <button type="button" wire:click="abrirCrear" class="rounded-xl bg-boton-principal px-4 py-2 text-xs font-black uppercase tracking-wider text-inverso shadow-sm">
            Registrar seguimiento
        </button>
        @endcan
    </div>

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

    <div class="overflow-hidden rounded-3xl border border-borde bg-fondo-card shadow-sm">
        <table class="w-full min-w-[900px] text-left text-sm">
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
                    <tr><td colspan="7" class="px-4 py-10 text-center text-sm font-bold text-apoyo">No hay seguimientos registrados para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $seguimientos->links() }}

    @if($modalForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <form wire:submit.prevent="guardar" class="max-h-[92vh] w-full max-w-4xl space-y-5 overflow-y-auto rounded-3xl border border-borde bg-fondo-card p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-widest text-meta">Registro clínico del turno</p>
                        <h2 class="text-lg font-black text-titulo">{{ $editandoId ? 'Corregir seguimiento diario' : 'Registrar seguimiento diario' }}</h2>
                    </div>
                    <button type="button" wire:click="cerrarModales" class="text-sm font-bold text-apoyo">Cerrar</button>
                </div>
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

                <div class="grid gap-3 rounded-2xl border border-borde bg-fondo-panel p-4 md:grid-cols-3">
                    @foreach(['incidente'=>'Generar alerta de incidente', 'requiereMedico'=>'Generar solicitud de evaluación médica', 'confusionObservable'=>'Confusión observable', 'repitePreguntas'=>'Repite preguntas', 'intentoCaminarSolo'=>'Intentó caminar sin ayuda'] as $campo => $etiqueta)
                        <label class="flex items-center gap-2 text-sm font-bold text-parrafo"><x-checkbox wire:model="{{ $campo }}" /> {{ $etiqueta }}</label>
                    @endforeach
                </div>

                <label class="block space-y-1 text-xs font-bold text-apoyo">Observación de enfermería *
                    <textarea wire:model="observacion" rows="4" placeholder="Describa evolución, respuesta a cuidados y novedades del turno." class="rm-input w-full text-sm"></textarea>
                    <span class="block text-[11px] font-medium text-meta">Si existe incidente o solicitud médica, detalle claramente lo ocurrido y las medidas iniciales.</span>
                    @error('observacion')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                </label>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="cerrarModales" class="rm-btn-secondary px-4 py-2 text-xs font-bold">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="guardar" class="rm-btn-primary px-4 py-2 text-xs font-black disabled:opacity-50">
                        <span wire:loading.remove wire:target="guardar">{{ $editandoId ? 'Guardar corrección' : 'Registrar seguimiento' }}</span>
                        <span wire:loading wire:target="guardar">Guardando…</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
