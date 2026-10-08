<div class="rm-clinical-workspace" x-data="rmClinicalFormFeedback()" @clinical-capture-changed="clinicalFormDirty = $event.detail.dirty">
    <x-ui.toast />
    <x-ui.page-header
        :title="$cuidado !== '' ? 'Cuidados · '.\App\Backend\Modulos\Enfermeria\Servicios\NavegacionCuidadosService::opcion($cuidado)['label'] : 'Seguimiento diario'"
        subtitle="Observación longitudinal y continuidad del cuidado durante el turno."
        overline="Enfermería y cuidados"
        icon="ph-chart-line-up">
        @can('atenciones.crear')
            <x-ui.action-button variant="primary" size="sm" icono="ph-plus-circle" id="resident-register-trigger" @click="feedbackAttempt = null; clinicalFormDirty = false; clinicalDiscardOpen = false" wire:click="abrirCrear">
                Registrar seguimiento
            </x-ui.action-button>
        @endcan
    </x-ui.page-header>
    @if($cuidado !== '')
        <p class="text-sm text-[var(--rm-text-secondary)]">Este cuidado se registra dentro del seguimiento diario completo. {{ $cuidado === 'conducta' ? 'Describe la conducta observada, la intervención realizada y la respuesta del residente.' : 'Las observaciones cognitivas no constituyen un diagnóstico.' }}</p>
    @endif

    <x-ui.filter-bar class="mb-4">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Buscador Principal formato alertas --}}
            <div class="lg:col-span-6 relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input type="text"
                    aria-label="Buscar residente" wire:model.live.debounce.400ms="search"
                    placeholder="Buscar residente..."
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
                @if(!empty($search))
                    <button type="button"
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
                        title="Limpiar búsqueda" aria-label="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>

            {{-- Turno --}}
            <div class="lg:col-span-3">
                <select id="seguimiento-filtroTurno" aria-invalid="{{ $errors->has('filtroTurno') ? 'true' : 'false' }}" @error('filtroTurno') aria-describedby="seguimiento-filtroTurno-error" @enderror aria-label="Filtrar por turno" wire:model.live="filtroTurno"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
                    <option value="">Todos los turnos</option>
                    @foreach($turnos as $turno)
                        <option value="{{ $turno->cod_turno }}">{{ $turno->nombre }} ({{ substr($turno->hora_inicio, 0, 5) }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Fecha --}}
            <div class="lg:col-span-3">
                <input id="seguimiento-filtroFecha" aria-invalid="{{ $errors->has('filtroFecha') ? 'true' : 'false' }}" @error('filtroFecha') aria-describedby="seguimiento-filtroFecha-error" @enderror type="date" wire:model.live="filtroFecha" title="Filtrar por fecha"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
            </div>
        </div>

        {{-- Chips de filtros activos formato alertas --}}
        @php
            $hasFiltrosSeg = !empty($search) || !empty($filtroTurno) || !empty($filtroFecha);
        @endphp
        @if($hasFiltrosSeg)
            <div class="rm-filter-bar__active">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($search))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Búsqueda: "{{ Str::limit($search, 16) }}"</span>
                            <button type="button" wire:click="$set('search', '')" aria-label="Quitar filtro de búsqueda" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs" aria-hidden="true"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroTurno))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-warning-soft)] border border-[var(--rm-warning)] text-[11px] font-bold text-[var(--rm-warning)]">
                            <span>Turno filtrado</span>
                            <button type="button" wire:click="$set('filtroTurno', '')" aria-label="Quitar filtro de turno" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs" aria-hidden="true"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroFecha))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Fecha: {{ $filtroFecha }}</span>
                            <button type="button" wire:click="$set('filtroFecha', null)" aria-label="Quitar filtro de fecha" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs" aria-hidden="true"></i></button>
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button"
                        wire:click="$set('search', ''); $set('filtroTurno', ''); $set('filtroFecha', null);"
                        class="inline-flex items-center gap-1 rounded-xl bg-[var(--rm-primary-soft)] hover:bg-[var(--rm-primary)] hover:text-white text-[var(--rm-primary)] border border-[var(--rm-primary)]/30 py-1 px-2.5 text-xs font-bold transition cursor-pointer">
                        <i class="ph-bold ph-arrow-counter-clockwise"></i>
                        <span>Limpiar filtros</span>
                    </button>
                </div>
            </div>
        @endif
    </x-ui.filter-bar>

    <x-ui.section-card title="Seguimientos del turno" subtitle="Registros guardados, del más reciente al más antiguo." icon="ph-clock-counter-clockwise">
        @if($seguimientos->isNotEmpty())
            <ol class="rm-clinical-workspace__history">
                @foreach($seguimientos as $seg)
                    <li class="rm-clinical-workspace__record">
                        <div class="rm-clinical-workspace__record-heading"><time>{{ $seg->fecha_hora?->format('d/m/Y H:i') ?? 'Fecha no registrada' }}</time><span>Seguimiento del turno</span></div>
                        <h3 class="rm-section-title">{{ $seg->adultoMayor?->nombres }} {{ $seg->adultoMayor?->apellido_paterno }}</h3>
                        <dl class="rm-clinical-workspace__details"><div><dt>Profesional que registró</dt><dd>{{ trim(($seg->personal?->nombres ?? '').' '.($seg->personal?->apellido_paterno ?? '')) ?: 'No registrado' }}</dd></div><div><dt>Estado general registrado</dt><dd>{{ \Illuminate\Support\Str::after((string) $seg->motivo, 'SEGUIMIENTO_DIARIO:') ?: 'No registrado' }}</dd></div></dl>
                        <p class="rm-clinical-form__note">{{ $seg->observacion ?: 'Sin observación registrada' }}</p>
                        @can('atenciones.editar')<button type="button" @click="feedbackAttempt = null; clinicalFormDirty = false; clinicalDiscardOpen = false" wire:click="abrirEditar('{{ $seg->cod_atencion }}')" class="rm-btn-secondary">Corregir seguimiento</button>@endcan
                    </li>
                @endforeach
            </ol>
        @else
            <x-ui.empty-state icon="ph-clipboard-text" title="Sin seguimientos" description="No hay registros para estos filtros. Ajusta la fecha o registra el seguimiento del turno." />
        @endif
    </x-ui.section-card>

    {{ $seguimientos->links() }}

    @if($modalForm)
        <x-ui.drawer-livewire
            wire:model="modalForm"
            size="xl" class="rm-clinical-form-drawer" alpine-close="closeClinicalDrawer()"
            :title="$editandoId ? 'Corregir seguimiento diario' : 'Registrar seguimiento diario'"
            subtitle="Registro observacional del turno con trazabilidad clínica"
            badge=""
            icon="ph-clipboard-text"
            close-method="cerrarModales">
            <div class="rm-clinical-form__context rm-clinical-form__section"><span class="rm-clinical-form__note">Residente · Seguimiento diario completo</span><strong data-clinical-resident>{{ $adultos->firstWhere('cod_residente', $codResidente)?->nombres ?? 'Selecciona un residente' }} {{ $adultos->firstWhere('cod_residente', $codResidente)?->ap_paterno ?? '' }}</strong><span class="rm-clinical-form__note">Profesional que registra</span><strong data-clinical-professional>{{ auth()->user()->name }}</strong></div>
            <div x-show="clinicalDiscardOpen" x-cloak role="alert" tabindex="-1" class="rm-clinical-form__section" x-effect="if (clinicalDiscardOpen) $nextTick(() => $el.focus())"><h3 class="rm-section-title">¿Salir sin guardar?</h3><p class="rm-clinical-form__note">Los datos de este seguimiento se perderán.</p></div>
            <form id="clinical-daily-form" wire:submit.prevent="guardar" @submit.capture="prepareDailyFeedback()" x-show="!clinicalDiscardOpen" class="rm-clinical-form" x-data="rmClinicalCapture()" @input="notifyDirty()" @change="notifyDirty()">
                <x-validation-errors />
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="space-y-1 text-xs font-bold text-apoyo">Residente *
                        <select id="seguimiento-codResidente" aria-invalid="{{ $errors->has('codResidente') ? 'true' : 'false' }}" @error('codResidente') aria-describedby="seguimiento-codResidente-error" @enderror wire:model.live="codResidente" @disabled($editandoId) class="rm-select w-full text-sm"><option value="">Seleccione</option>@foreach($adultos as $adulto)<option value="{{ $adulto->cod_residente }}">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</option>@endforeach</select>
                        @error('codResidente')<span id="seguimiento-codResidente-error" role="alert" class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Turno clínico vigente *
                        <select id="seguimiento-codTurno" aria-invalid="{{ $errors->has('codTurno') ? 'true' : 'false' }}" @error('codTurno') aria-describedby="seguimiento-codTurno-error" @enderror wire:model="codTurno" disabled class="rm-select w-full text-sm disabled:opacity-70"><option value="">Sin turno vigente</option>@foreach($turnos as $turno)<option value="{{ $turno->cod_turno }}">{{ $turno->nombre }}</option>@endforeach</select>
                        @error('codTurno')<span id="seguimiento-codTurno-error" role="alert" class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Fecha *<input id="seguimiento-fecha" type="date" max="{{ today()->format('Y-m-d') }}" wire:model="fecha" class="rm-input w-full text-sm" aria-invalid="{{ $errors->has('fecha') ? 'true' : 'false' }}" @error('fecha') aria-describedby="seguimiento-fecha-error" @enderror>@error('fecha')<span id="seguimiento-fecha-error" role="alert" class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Hora de inicio *<input id="seguimiento-horaInicio" aria-invalid="{{ $errors->has('horaInicio') ? 'true' : 'false' }}" @error('horaInicio') aria-describedby="seguimiento-horaInicio-error" @enderror type="time" wire:model="horaInicio" class="rm-input w-full text-sm">@error('horaInicio')<span id="seguimiento-horaInicio-error" role="alert" class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
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
                        <select id="seguimiento-tipoComida" aria-invalid="{{ $errors->has('tipoComida') ? 'true' : 'false' }}" @error('tipoComida') aria-describedby="seguimiento-tipoComida-error" @enderror wire:model="tipoComida" class="rm-select w-full text-sm">
                            <option value="">Seleccione una opción</option>
                            <option value="DESAYUNO">Desayuno</option><option value="MEDIA_MANANA">Media mañana</option>
                            <option value="ALMUERZO">Almuerzo</option><option value="MERIENDA">Merienda</option>
                            <option value="CENA">Cena</option><option value="COLACION">Colación</option>
                        </select>
                        @error('tipoComida')<span id="seguimiento-tipoComida-error" role="alert" class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    @foreach($selects as $campo => [$etiqueta, $opciones])
                        <label class="space-y-1 text-xs font-bold text-apoyo">{{ $etiqueta }}
                            <select id="seguimiento-{{ $campo }}" aria-invalid="{{ $errors->has($campo) ? 'true' : 'false' }}" @error($campo) aria-describedby="seguimiento-{{ $campo }}-error" @enderror wire:model="{{ $campo }}" class="rm-select w-full text-sm">
                                @unless(array_key_exists('', $opciones))
                                    <option value="">Seleccione una opción</option>
                                @endunless
                                @foreach($opciones as $valor => $texto)<option value="{{ $valor }}">{{ $texto }}</option>@endforeach
                            </select>
                            @error($campo)<span id="seguimiento-{{ $campo }}-error" role="alert" class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                        </label>
                    @endforeach
                    <label class="space-y-1 text-xs font-bold text-apoyo">Alimentación consumida (%) *
                        <input id="seguimiento-porcentajeAlimentacion" aria-invalid="{{ $errors->has('porcentajeAlimentacion') ? 'true' : 'false' }}" @error('porcentajeAlimentacion') aria-describedby="seguimiento-porcentajeAlimentacion-error" @enderror type="number" min="0" max="100" step="1" wire:model="porcentajeAlimentacion" class="rm-input w-full text-sm">
                        @error('porcentajeAlimentacion')<span id="seguimiento-porcentajeAlimentacion-error" role="alert" class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Tipo de líquido *
                        <input id="seguimiento-tipoLiquido" aria-invalid="{{ $errors->has('tipoLiquido') ? 'true' : 'false' }}" @error('tipoLiquido') aria-describedby="seguimiento-tipoLiquido-error" @enderror type="text" maxlength="60" wire:model="tipoLiquido" placeholder="Ej.: agua, infusión" class="rm-input w-full text-sm">
                        @error('tipoLiquido')<span id="seguimiento-tipoLiquido-error" role="alert" class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                    <label class="space-y-1 text-xs font-bold text-apoyo">Cantidad hidratación (ml) *
                        <input id="seguimiento-cantidadHidratacionMl" aria-invalid="{{ $errors->has('cantidadHidratacionMl') ? 'true' : 'false' }}" @error('cantidadHidratacionMl') aria-describedby="seguimiento-cantidadHidratacionMl-error" @enderror type="number" min="1" max="10000" step="1" wire:model="cantidadHidratacionMl" class="rm-input w-full text-sm">
                        @error('cantidadHidratacionMl')<span id="seguimiento-cantidadHidratacionMl-error" role="alert" class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                    </label>
                </div>

                <x-ui.form-section class="rm-clinical-form__section" title="Ingesta e hidratación" description="Registre cantidad, tolerancia y seguridad de la deglución." icon="ph-bowl-food" :columns="2">
                    <x-ui.field label="Tolerancia de la ingesta" for="seguimiento-toleranciaIngesta" error="toleranciaIngesta">
                        <select id="seguimiento-toleranciaIngesta" aria-invalid="{{ $errors->has('toleranciaIngesta') ? 'true' : 'false' }}" @error('toleranciaIngesta') aria-describedby="seguimiento-toleranciaIngesta-error" @enderror wire:model="toleranciaIngesta" class="rm-select">
                            <option value="">Sin novedad observada</option>
                            <option value="BUENA">Buena</option><option value="REGULAR">Regular</option>
                            <option value="MALA">Mala</option><option value="NAUSEAS">Náuseas</option><option value="VOMITO">Vómito</option>
                        </select>
                    </x-ui.field>
                    <label class="rm-clinical-form__check self-end">
                        <input id="seguimiento-dificultadDeglucion" aria-invalid="{{ $errors->has('dificultadDeglucion') ? 'true' : 'false' }}" @error('dificultadDeglucion') aria-describedby="seguimiento-dificultadDeglucion-error" @enderror type="checkbox" wire:model="dificultadDeglucion" class="rm-checkbox">
                        <span><strong>Dificultad para deglutir</strong><small>Marque solo si fue observada durante la ingesta.</small></span>
                    </label>
                </x-ui.form-section>

                <x-ui.form-section class="rm-clinical-form__section" title="Movilidad y riesgo de caída" description="Describa la capacidad funcional observada, apoyos y tolerancia." icon="ph-person-simple-walk" :columns="3">
                    <x-ui.field label="Equilibrio" for="seguimiento-equilibrio" error="equilibrio">
                        <select id="seguimiento-equilibrio" aria-invalid="{{ $errors->has('equilibrio') ? 'true' : 'false' }}" @error('equilibrio') aria-describedby="seguimiento-equilibrio-error" @enderror wire:model="equilibrio" class="rm-select"><option value="">Sin valorar</option><option value="ESTABLE">Estable</option><option value="INESTABLE">Inestable</option><option value="NO_VALORABLE">No valorable</option></select>
                    </x-ui.field>
                    <x-ui.field label="Traslado" for="seguimiento-traslado" error="traslado">
                        <select id="seguimiento-traslado" aria-invalid="{{ $errors->has('traslado') ? 'true' : 'false' }}" @error('traslado') aria-describedby="seguimiento-traslado-error" @enderror wire:model="traslado" class="rm-select"><option value="">Sin registrar</option><option value="INDEPENDIENTE">Independiente</option><option value="SUPERVISION">Con supervisión</option><option value="AYUDA_UNA_PERSONA">Ayuda de una persona</option><option value="AYUDA_DOS_PERSONAS">Ayuda de dos personas</option><option value="GRUA">Grúa</option></select>
                    </x-ui.field>
                    <x-ui.field label="Riesgo de caída" for="seguimiento-riesgoCaida" error="riesgoCaida">
                        <select id="seguimiento-riesgoCaida" aria-invalid="{{ $errors->has('riesgoCaida') ? 'true' : 'false' }}" @error('riesgoCaida') aria-describedby="seguimiento-riesgoCaida-error" @enderror wire:model="riesgoCaida" class="rm-select"><option value="">Sin cambio observado</option><option value="BAJO">Bajo</option><option value="MEDIO">Medio</option><option value="ALTO">Alto</option></select>
                    </x-ui.field>
                    <x-ui.field label="Tipo de apoyo" for="seguimiento-tipoApoyo" error="tipoApoyo"><input id="seguimiento-tipoApoyo" aria-invalid="{{ $errors->has('tipoApoyo') ? 'true' : 'false' }}" @error('tipoApoyo') aria-describedby="seguimiento-tipoApoyo-error" @enderror wire:model="tipoApoyo" maxlength="60" class="rm-input" placeholder="Ej.: asistencia de una persona"></x-ui.field>
                    <x-ui.field label="Dispositivo" for="seguimiento-dispositivo" error="dispositivo"><input id="seguimiento-dispositivo" aria-invalid="{{ $errors->has('dispositivo') ? 'true' : 'false' }}" @error('dispositivo') aria-describedby="seguimiento-dispositivo-error" @enderror wire:model="dispositivo" maxlength="80" class="rm-input" placeholder="Ej.: andador, bastón, silla"></x-ui.field>
                    <x-ui.field label="Fatiga" for="seguimiento-fatiga" error="fatiga"><select id="seguimiento-fatiga" aria-invalid="{{ $errors->has('fatiga') ? 'true' : 'false' }}" @error('fatiga') aria-describedby="seguimiento-fatiga-error" @enderror wire:model="fatiga" class="rm-select"><option value="">Sin registrar</option><option value="SIN_FATIGA">Sin fatiga</option><option value="LEVE">Leve</option><option value="MODERADA">Moderada</option><option value="SEVERA">Severa</option></select></x-ui.field>
                </x-ui.form-section>

                <x-ui.form-section class="rm-clinical-form__section" title="Sueño y eliminación" description="Complete únicamente los datos observados o reportados durante el turno." icon="ph-moon-stars" :columns="3">
                    <x-ui.field label="Horas de sueño" for="seguimiento-horasSueno" error="horasSueno" hint="0–24 h"><input id="seguimiento-horasSueno" aria-invalid="{{ $errors->has('horasSueno') ? 'true' : 'false' }}" @error('horasSueno') aria-describedby="seguimiento-horasSueno-error" @enderror type="number" min="0" max="24" step="0.25" wire:model="horasSueno" class="rm-input"></x-ui.field>
                    <x-ui.field label="Despertares" for="seguimiento-despertares" error="despertares" hint="0–30"><input id="seguimiento-despertares" aria-invalid="{{ $errors->has('despertares') ? 'true' : 'false' }}" @error('despertares') aria-describedby="seguimiento-despertares-error" @enderror type="number" min="0" max="30" step="1" wire:model="despertares" class="rm-input"></x-ui.field>
                    <label class="rm-clinical-form__check self-end"><input id="seguimiento-agitacionNocturna" aria-invalid="{{ $errors->has('agitacionNocturna') ? 'true' : 'false' }}" @error('agitacionNocturna') aria-describedby="seguimiento-agitacionNocturna-error" @enderror type="checkbox" wire:model="agitacionNocturna" class="rm-checkbox"><span><strong>Agitación nocturna</strong><small>Conducta observada durante el descanso.</small></span></label>
                    <x-ui.field label="Tipo de eliminación" for="seguimiento-tipoEliminacion" error="tipoEliminacion"><select id="seguimiento-tipoEliminacion" aria-invalid="{{ $errors->has('tipoEliminacion') ? 'true' : 'false' }}" @error('tipoEliminacion') aria-describedby="seguimiento-tipoEliminacion-error" @enderror wire:model="tipoEliminacion" class="rm-select"><option value="">Sin registro</option><option value="URINARIA">Urinaria</option><option value="INTESTINAL">Intestinal</option><option value="AMBAS">Ambas</option></select></x-ui.field>
                    <x-ui.field label="Cantidad" for="seguimiento-cantidadEliminacion" error="cantidadEliminacion"><input id="seguimiento-cantidadEliminacion" aria-invalid="{{ $errors->has('cantidadEliminacion') ? 'true' : 'false' }}" @error('cantidadEliminacion') aria-describedby="seguimiento-cantidadEliminacion-error" @enderror wire:model="cantidadEliminacion" maxlength="40" class="rm-input" placeholder="Ej.: moderada"></x-ui.field>
                    <x-ui.field label="Continencia" for="seguimiento-continencia" error="continencia"><select id="seguimiento-continencia" aria-invalid="{{ $errors->has('continencia') ? 'true' : 'false' }}" @error('continencia') aria-describedby="seguimiento-continencia-error" @enderror wire:model="continencia" class="rm-select"><option value="">Sin registrar</option><option value="CONTINENTE">Continente</option><option value="INCONTINENCIA_URINARIA">Incontinencia urinaria</option><option value="INCONTINENCIA_FECAL">Incontinencia fecal</option><option value="DOBLE_INCONTINENCIA">Doble incontinencia</option></select></x-ui.field>
                    <x-ui.field class="md:col-span-3" label="Características de la eliminación" for="seguimiento-caracteristicaEliminacion" error="caracteristicaEliminacion"><input id="seguimiento-caracteristicaEliminacion" aria-invalid="{{ $errors->has('caracteristicaEliminacion') ? 'true' : 'false' }}" @error('caracteristicaEliminacion') aria-describedby="seguimiento-caracteristicaEliminacion-error" @enderror wire:model="caracteristicaEliminacion" maxlength="120" class="rm-input" placeholder="Color, consistencia y cambios relevantes"></x-ui.field>
                </x-ui.form-section>

                @php $nivelObservado = [''=>'Sin valorar','CONSERVADA'=>'Conservada','ALTERACION_LEVE'=>'Alteración leve','ALTERADA'=>'Alterada','NO_VALORABLE'=>'No valorable']; @endphp
                <x-ui.form-section class="rm-clinical-form__section" title="Control cognitivo observacional" description="Observación longitudinal de Enfermería. No constituye diagnóstico cognitivo." icon="ph-brain" :columns="3">
                    @foreach(['orientacionLugar'=>'Orientación en lugar','orientacionTiempo'=>'Orientación en tiempo'] as $campo => $etiqueta)
                        <x-ui.field :label="$etiqueta" :for="'seguimiento-'.$campo" :error="$campo"><select id="seguimiento-{{ $campo }}" aria-invalid="{{ $errors->has($campo) ? 'true' : 'false' }}" @error($campo) aria-describedby="seguimiento-{{ $campo }}-error" @enderror wire:model="{{ $campo }}" class="rm-select"><option value="">Sin valorar</option><option value="ORIENTADO">Orientado</option><option value="PARCIALMENTE_ORIENTADO">Parcialmente orientado</option><option value="DESORIENTADO">Desorientado</option></select></x-ui.field>
                    @endforeach
                    @foreach(['memoriaReciente'=>'Memoria reciente','memoriaRemota'=>'Memoria remota'] as $campo => $etiqueta)
                        <x-ui.field :label="$etiqueta" :for="'seguimiento-'.$campo" :error="$campo"><select id="seguimiento-{{ $campo }}" aria-invalid="{{ $errors->has($campo) ? 'true' : 'false' }}" @error($campo) aria-describedby="seguimiento-{{ $campo }}-error" @enderror wire:model="{{ $campo }}" class="rm-select">@foreach($nivelObservado as $valor=>$texto)<option value="{{ $valor }}">{{ $texto }}</option>@endforeach</select></x-ui.field>
                    @endforeach
                    <x-ui.field label="Atención" for="seguimiento-atencionCognitiva" error="atencionCognitiva"><select id="seguimiento-atencionCognitiva" aria-invalid="{{ $errors->has('atencionCognitiva') ? 'true' : 'false' }}" @error('atencionCognitiva') aria-describedby="seguimiento-atencionCognitiva-error" @enderror wire:model="atencionCognitiva" class="rm-select"><option value="">Sin valorar</option><option value="CONSERVADA">Conservada</option><option value="FLUCTUANTE">Fluctuante</option><option value="DISMINUIDA">Disminuida</option><option value="NO_VALORABLE">No valorable</option></select></x-ui.field>
                    <x-ui.field label="Comprensión" for="seguimiento-comprension" error="comprension"><select id="seguimiento-comprension" aria-invalid="{{ $errors->has('comprension') ? 'true' : 'false' }}" @error('comprension') aria-describedby="seguimiento-comprension-error" @enderror wire:model="comprension" class="rm-select"><option value="">Sin valorar</option><option value="CONSERVADA">Conservada</option><option value="PARCIAL">Parcial</option><option value="ALTERADA">Alterada</option><option value="NO_VALORABLE">No valorable</option></select></x-ui.field>
                    <x-ui.field label="Lenguaje" for="seguimiento-lenguaje" error="lenguaje"><select id="seguimiento-lenguaje" aria-invalid="{{ $errors->has('lenguaje') ? 'true' : 'false' }}" @error('lenguaje') aria-describedby="seguimiento-lenguaje-error" @enderror wire:model="lenguaje" class="rm-select"><option value="">Sin valorar</option><option value="CONSERVADO">Conservado</option><option value="LIMITADO">Limitado</option><option value="ALTERADO">Alterado</option><option value="NO_VALORABLE">No valorable</option></select></x-ui.field>
                    <div class="md:col-span-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach(['sigueInstrucciones'=>'Sigue instrucciones','repitePreguntas'=>'Repite preguntas','olvidaIndicaciones'=>'Olvida indicaciones','reconocePersonas'=>'Reconoce personas','reconoceEntorno'=>'Reconoce el entorno','confusionObservable'=>'Confusión observable','cambioCognitivo'=>'Cambio respecto a su línea basal'] as $campo=>$etiqueta)
                            <label class="rm-clinical-form__check"><input id="seguimiento-{{ $campo }}" aria-invalid="{{ $errors->has($campo) ? 'true' : 'false' }}" @error($campo) aria-describedby="seguimiento-{{ $campo }}-error" @enderror type="checkbox" wire:model="{{ $campo }}" class="rm-checkbox"><span><strong>{{ $etiqueta }}</strong></span></label>
                        @endforeach
                    </div>
                </x-ui.form-section>

                <x-ui.form-section class="rm-clinical-form__section" title="Conducta y respuesta al cuidado" description="Registre conductas observables, la intervención realizada y su resultado." icon="ph-users-three" :columns="2">
                    <div class="md:col-span-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach(['apatia'=>'Apatía','agitacion'=>'Agitación','agresividad'=>'Agresividad','ansiedad'=>'Ansiedad','aislamiento'=>'Aislamiento','deambulacion'=>'Deambulación','cambioConducta'=>'Cambio de conducta'] as $campo=>$etiqueta)
                            <label class="rm-clinical-form__check"><input id="seguimiento-{{ $campo }}" aria-invalid="{{ $errors->has($campo) ? 'true' : 'false' }}" @error($campo) aria-describedby="seguimiento-{{ $campo }}-error" @enderror type="checkbox" wire:model="{{ $campo }}" class="rm-checkbox"><span><strong>{{ $etiqueta }}</strong></span></label>
                        @endforeach
                    </div>
                    <x-ui.field label="Intervención realizada" for="seguimiento-intervencionConducta" error="intervencionConducta"><textarea id="seguimiento-intervencionConducta" aria-invalid="{{ $errors->has('intervencionConducta') ? 'true' : 'false' }}" @error('intervencionConducta') aria-describedby="seguimiento-intervencionConducta-error" @enderror wire:model="intervencionConducta" maxlength="2000" class="rm-textarea" placeholder="Contención verbal, acompañamiento, redirección u otra medida"></textarea></x-ui.field>
                    <x-ui.field label="Respuesta observada" for="seguimiento-respuestaConducta" error="respuestaConducta"><textarea id="seguimiento-respuestaConducta" aria-invalid="{{ $errors->has('respuestaConducta') ? 'true' : 'false' }}" @error('respuestaConducta') aria-describedby="seguimiento-respuestaConducta-error" @enderror wire:model="respuestaConducta" maxlength="2000" class="rm-textarea" placeholder="Describa la respuesta posterior a la intervención"></textarea></x-ui.field>
                </x-ui.form-section>

                <div class="grid gap-3 rounded-2xl border border-borde bg-fondo-panel p-4 md:grid-cols-3">
                    @foreach(['incidente'=>'Generar alerta de incidente', 'requiereMedico'=>'Generar solicitud de evaluación médica', 'intentoCaminarSolo'=>'Intentó caminar sin ayuda'] as $campo => $etiqueta)
                        <label class="rm-clinical-form__check"><x-checkbox wire:model="{{ $campo }}" /> {{ $etiqueta }}</label>
                    @endforeach
                </div>

                <label class="block space-y-1 text-xs font-bold text-apoyo">Observación de enfermería *
                    <textarea id="seguimiento-observacion" aria-invalid="{{ $errors->has('observacion') ? 'true' : 'false' }}" @error('observacion') aria-describedby="seguimiento-observacion-error" @enderror wire:model="observacion" rows="4" placeholder="Describa evolución, respuesta a cuidados y novedades del turno." class="rm-textarea w-full text-sm" maxlength="10000"></textarea>
                    <span class="block text-[11px] font-medium text-meta">Si existe incidente o solicitud médica, detalle claramente lo ocurrido y las medidas iniciales.</span>
                    @error('observacion')<span id="seguimiento-observacion-error" role="alert" class="text-xs text-estado-peligro">{{ $message }}</span>@enderror
                </label>
            </form>
            <x-slot:footer>
                <template x-if="clinicalDiscardOpen"><div class="rm-clinical-workspace__actions"><button type="button" class="rm-btn-secondary" @click="clinicalDiscardOpen = false">Seguir editando</button><button type="button" class="rm-btn-danger" @click="discardClinicalDrawer()">Salir sin guardar</button></div></template>
                <template x-if="!clinicalDiscardOpen"><div class="rm-clinical-workspace__actions"><button type="button" class="rm-btn-secondary" @click="closeClinicalDrawer()" wire:loading.attr="disabled" wire:target="guardar">Cancelar</button><button type="submit" form="clinical-daily-form" class="rm-btn-primary" wire:loading.attr="disabled" wire:target="guardar"><span wire:loading.remove wire:target="guardar">{{ $editandoId ? 'Guardar corrección' : 'Confirmar y registrar' }}</span><span wire:loading wire:target="guardar">Registrando…</span></button></div></template>
            </x-slot:footer>
        </x-ui.drawer-livewire>
    @endif
    @if(session('mensaje'))<span hidden x-effect="if (!$wire.modalForm) confirmDailyFeedback()"></span>@endif
    @include('livewire.cuidados.partials.clinical-operation-result', ['clinicalResultResidentCode' => $codResidente])
</div>
