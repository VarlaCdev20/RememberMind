<div>
    @if($mostrar)
        @php
            $usuario = auth()->user();
            $puedeRegistrarSignos = (bool) ($usuario?->can('signos_vitales.crear') ?? false);

            $tipoMeta = match ($tipo_nota) {
                'INGRESO' => ['Nota de ingreso', 'ph-sign-in', 'text-estado-info', 'bg-estado-infoBg'],
                'EGRESO' => ['Nota de egreso', 'ph-sign-out', 'text-apoyo', 'bg-fondo-panel'],
                'INTERCONSULTA' => ['Interconsulta', 'ph-arrows-left-right', 'text-boton-acento', 'bg-boton-acento/10'],
                'URGENCIA' => ['Urgencia / emergencia', 'ph-warning-circle', 'text-estado-error', 'bg-estado-errorBg'],
                'PROCEDIMIENTO' => ['Procedimiento', 'ph-first-aid', 'text-estado-advertencia', 'bg-estado-advertenciaBg'],
                default => ['Consulta / evolución', 'ph-stethoscope', 'text-estado-exito', 'bg-estado-exitoBg'],
            };

            [$tipoLabel, $tipoIcon, $tipoText, $tipoBg] = $tipoMeta;

            $estadoCodigo = strtoupper(trim((string) ($adulto?->estado?->estado ?? $adulto?->estado_operativo ?? '')));
            $estadoBadge = match ($estadoCodigo) {
                'ACTIVO', 'ACTIVA', 'EN_CENTRO', 'EN_SEGUIMIENTO_ACTIVO', 'ADMITIDO'
                => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'HOSPITALIZADO', 'SEGUIMIENTO_ESPECIAL', 'OBSERVADO'
                => 'bg-amber-50 text-amber-700 border-amber-200',
                'SALIDA_TEMPORAL', 'DERIVADO', 'TRASLADADO'
                => 'bg-blue-50 text-blue-700 border-blue-200',
                'FALLECIDO', 'INACTIVO', 'INACTIVA', 'ARCHIVADO', 'EGRESADO', 'RETIRADO'
                => 'bg-slate-100 text-slate-700 border-slate-200',
                default => 'bg-slate-50 text-slate-600 border-slate-200',
            };

            $pasMin = \App\Services\Clinica\ValidacionSignosVitalesService::PAS_MIN;
            $pasMax = \App\Services\Clinica\ValidacionSignosVitalesService::PAS_MAX;
            $padMin = \App\Services\Clinica\ValidacionSignosVitalesService::PAD_MIN;
            $padMax = \App\Services\Clinica\ValidacionSignosVitalesService::PAD_MAX;
            $fcMin = \App\Services\Clinica\ValidacionSignosVitalesService::FC_MIN;
            $fcMax = \App\Services\Clinica\ValidacionSignosVitalesService::FC_MAX;
            $frMin = \App\Services\Clinica\ValidacionSignosVitalesService::FR_MIN;
            $frMax = \App\Services\Clinica\ValidacionSignosVitalesService::FR_MAX;
            $tempMin = \App\Services\Clinica\ValidacionSignosVitalesService::TEMP_MIN;
            $tempMax = \App\Services\Clinica\ValidacionSignosVitalesService::TEMP_MAX;
            $spo2Min = \App\Services\Clinica\ValidacionSignosVitalesService::SPO2_MIN;
            $spo2Max = \App\Services\Clinica\ValidacionSignosVitalesService::SPO2_MAX;
            $glucosaMin = \App\Services\Clinica\ValidacionSignosVitalesService::GLUCOSA_MIN;
            $pesoMin = \App\Services\Clinica\ValidacionSignosVitalesService::PESO_MIN;
            $pesoMax = \App\Services\Clinica\ValidacionSignosVitalesService::PESO_MAX;
        @endphp

        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 px-3 py-3 backdrop-blur-[2px] sm:px-5 sm:py-6"
            x-data="{ abierto: true }" x-show="abierto" x-cloak x-on:keydown.escape.window="$wire.cerrar()"
            wire:click.self="cerrar" role="dialog" aria-modal="true" aria-labelledby="nota-evolucion-titulo">
            <div class="mx-auto flex min-h-full max-w-4xl items-start justify-center sm:items-center">
                <form wire:submit="guardar"
                    class="relative flex max-h-[calc(100vh-1.5rem)] w-full flex-col overflow-hidden rounded-[28px] border border-borde bg-fondo-card shadow-2xl sm:max-h-[calc(100vh-3rem)]"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-3 scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                    {{-- =========================================================
                    CABECERA
                    ========================================================== --}}
                    <header class="shrink-0 border-b border-borde bg-fondo-card px-5 py-4 sm:px-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $tipoBg }} {{ $tipoText }}">
                                        <i class="ph-bold {{ $tipoIcon }} text-xl"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 id="nota-evolucion-titulo"
                                                class="text-base font-black tracking-tight text-titulo sm:text-lg">
                                                Consulta / Evolución médica
                                            </h3>
                                            <span
                                                class="inline-flex rounded-full {{ $tipoBg }} px-2.5 py-1 text-[10px] font-black uppercase tracking-wider {{ $tipoText }}">
                                                {{ $tipoLabel }}
                                            </span>
                                        </div>

                                        @if($adulto)
                                            <div
                                                class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-semibold text-apoyo">
                                                <span class="font-black text-titulo">
                                                    {{ $adulto->nombre_completo ?? trim(($adulto->nombres ?? '') . ' ' . ($adulto->ap_paterno ?? '') . ' ' . ($adulto->ap_materno ?? '')) }}
                                                </span>
                                                @if($adulto->edad !== null)
                                                    <span class="inline-flex items-center gap-1">
                                                        <i class="ph-bold ph-calendar"></i>{{ $adulto->edad }} años
                                                    </span>
                                                @endif
                                                @if($adulto->ci)
                                                    <span class="inline-flex items-center gap-1">
                                                        <i class="ph-bold ph-identification-card"></i>CI {{ $adulto->ci }}
                                                    </span>
                                                @endif
                                                @if($adulto->estado)
                                                    <span
                                                        class="inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $estadoBadge }}">
                                                        {{ $adulto->estado_humano }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <button type="button" wire:click="cerrar" wire:loading.attr="disabled" wire:target="guardar"
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-apoyo transition hover:bg-fondo-panel hover:text-titulo disabled:cursor-not-allowed disabled:opacity-50"
                                aria-label="Cerrar formulario">
                                <i class="ph-bold ph-x text-base"></i>
                            </button>
                        </div>
                    </header>

                    {{-- =========================================================
                    CONTENIDO DESPLAZABLE
                    ========================================================== --}}
                    <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-5 py-5 sm:px-6">
                        <fieldset class="space-y-6" wire:loading.attr="disabled" wire:target="guardar">
                            {{-- Resumen de errores --}}
                            @if($errors->any())
                                <div class="rounded-2xl border border-estado-error/20 bg-estado-errorBg px-4 py-3" role="alert">
                                    <div class="flex items-start gap-2.5">
                                        <i class="ph-bold ph-warning-circle mt-0.5 text-base text-estado-error"></i>
                                        <div>
                                            <p class="text-xs font-black text-estado-error">Revise los campos marcados antes de
                                                guardar.</p>
                                            <p class="mt-0.5 text-[11px] font-medium text-apoyo">La información clínica no se
                                                registrará hasta que las validaciones sean correctas.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- =================================================
                            1. CONTEXTO DE LA NOTA
                            ================================================== --}}
                            <section class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-boton-acento/10 text-xs font-black text-boton-acento">1</span>
                                    <div>
                                        <h4 class="text-sm font-black text-titulo">Contexto de la atención</h4>
                                        <p class="text-[11px] font-medium text-apoyo">Defina el tipo, la fecha y la hora del
                                            registro clínico.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                                    <div class="md:col-span-6">
                                        <label for="tipo_nota"
                                            class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">
                                            Tipo de registro <span class="text-estado-error">*</span>
                                        </label>
                                        <select id="tipo_nota" wire:model="tipo_nota" required
                                            aria-invalid="{{ $errors->has('tipo_nota') ? 'true' : 'false' }}"
                                            class="w-full rounded-xl border bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10
                                                    {{ $errors->has('tipo_nota') ? 'border-estado-error' : 'border-borde' }}">
                                            <option value="EVOLUCION">Consulta / evolución clínica</option>
                                            <option value="INGRESO">Nota de ingreso</option>
                                            <option value="EGRESO">Nota de egreso</option>
                                            <option value="INTERCONSULTA">Interconsulta</option>
                                            <option value="URGENCIA">Urgencia / emergencia</option>
                                            <option value="PROCEDIMIENTO">Procedimiento</option>
                                        </select>
                                        @error('tipo_nota')
                                            <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-3">
                                        <label for="fecha_nota"
                                            class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">
                                            Fecha <span class="text-estado-error">*</span>
                                        </label>
                                        <input id="fecha_nota" wire:model="fecha" type="date"
                                            max="{{ now()->toDateString() }}" required
                                            aria-invalid="{{ $errors->has('fecha') ? 'true' : 'false' }}" class="w-full rounded-xl border bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10
                                                    {{ $errors->has('fecha') ? 'border-estado-error' : 'border-borde' }}">
                                        @error('fecha')
                                            <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="md:col-span-3">
                                        <label for="hora_nota"
                                            class="mb-1.5 block text-xs font-black uppercase tracking-wider text-apoyo">Hora</label>
                                        <input id="hora_nota" wire:model="hora" type="time"
                                            aria-invalid="{{ $errors->has('hora') ? 'true' : 'false' }}" class="w-full rounded-xl border bg-fondo-panel px-3 py-2.5 text-sm font-semibold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10
                                                    {{ $errors->has('hora') ? 'border-estado-error' : 'border-borde' }}">
                                        @error('hora')
                                            <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            <div class="h-px bg-borde"></div>

                            {{-- =================================================
                            2. SOAP
                            ================================================== --}}
                            <section class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-boton-acento/10 text-xs font-black text-boton-acento">2</span>
                                    <div>
                                        <h4 class="text-sm font-black text-titulo">Registro clínico SOAP</h4>
                                        <p class="text-[11px] font-medium text-apoyo">Registre hallazgos concretos. La
                                            valoración y el plan son obligatorios.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                    {{-- S --}}
                                    <div>
                                        <div class="mb-1.5 flex items-center justify-between gap-2">
                                            <label for="subjetivo"
                                                class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-apoyo">
                                                <span
                                                    class="flex h-5 w-5 items-center justify-center rounded-md bg-estado-infoBg text-[10px] font-black text-estado-info">S</span>
                                                Subjetivo
                                            </label>
                                            <span class="text-[9px] font-bold text-apoyo">máx. 2000</span>
                                        </div>
                                        <textarea id="subjetivo" wire:model="subjetivo" rows="4" maxlength="2000"
                                            placeholder="Motivo de consulta, síntomas referidos, cambios percibidos por el residente o cuidador..."
                                            aria-invalid="{{ $errors->has('subjetivo') ? 'true' : 'false' }}"
                                            class="w-full resize-y rounded-xl border bg-fondo-panel px-3 py-2.5 text-sm font-medium leading-relaxed text-titulo outline-none transition placeholder:text-apoyo/50 focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10
                                                    {{ $errors->has('subjetivo') ? 'border-estado-error' : 'border-borde' }}"></textarea>
                                        @error('subjetivo')
                                            <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- O --}}
                                    <div>
                                        <div class="mb-1.5 flex items-center justify-between gap-2">
                                            <label for="objetivo"
                                                class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-apoyo">
                                                <span
                                                    class="flex h-5 w-5 items-center justify-center rounded-md bg-estado-advertenciaBg text-[10px] font-black text-estado-advertencia">O</span>
                                                Objetivo
                                            </label>
                                            <span class="text-[9px] font-bold text-apoyo">máx. 2000</span>
                                        </div>
                                        <textarea id="objetivo" wire:model="objetivo" rows="4" maxlength="2000"
                                            placeholder="Exploración física, hallazgos observables, resultados disponibles y datos clínicos objetivos..."
                                            aria-invalid="{{ $errors->has('objetivo') ? 'true' : 'false' }}"
                                            class="w-full resize-y rounded-xl border bg-fondo-panel px-3 py-2.5 text-sm font-medium leading-relaxed text-titulo outline-none transition placeholder:text-apoyo/50 focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10
                                                    {{ $errors->has('objetivo') ? 'border-estado-error' : 'border-borde' }}"></textarea>
                                        @error('objetivo')
                                            <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- A --}}
                                <div>
                                    <div class="mb-1.5 flex items-center justify-between gap-2">
                                        <label for="valoracion"
                                            class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-apoyo">
                                            <span
                                                class="flex h-5 w-5 items-center justify-center rounded-md bg-boton-acento/10 text-[10px] font-black text-boton-acento">A</span>
                                            Valoración / impresión clínica <span class="text-estado-error">*</span>
                                        </label>
                                        <span class="text-[9px] font-bold text-apoyo">10–3000 caracteres</span>
                                    </div>
                                    <textarea id="valoracion" wire:model="valoracion" rows="4" minlength="10"
                                        maxlength="3000" required
                                        placeholder="Análisis del caso, problemas identificados, diagnóstico o impresión clínica sustentada..."
                                        aria-invalid="{{ $errors->has('valoracion') ? 'true' : 'false' }}"
                                        class="w-full resize-y rounded-xl border bg-fondo-panel px-3 py-2.5 text-sm font-medium leading-relaxed text-titulo outline-none transition placeholder:text-apoyo/50 focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10
                                                {{ $errors->has('valoracion') ? 'border-estado-error' : 'border-borde' }}"></textarea>
                                    @error('valoracion')
                                        <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- P --}}
                                <div>
                                    <div class="mb-1.5 flex items-center justify-between gap-2">
                                        <label for="plan"
                                            class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-apoyo">
                                            <span
                                                class="flex h-5 w-5 items-center justify-center rounded-md bg-estado-exitoBg text-[10px] font-black text-estado-exito">P</span>
                                            Plan <span class="text-estado-error">*</span>
                                        </label>
                                        <span class="text-[9px] font-bold text-apoyo">5–3000 caracteres</span>
                                    </div>
                                    <textarea id="plan" wire:model="plan" rows="4" minlength="5" maxlength="3000" required
                                        placeholder="Conducta, indicaciones, tratamiento, seguimiento, evaluaciones, controles o derivaciones..."
                                        aria-invalid="{{ $errors->has('plan') ? 'true' : 'false' }}"
                                        class="w-full resize-y rounded-xl border bg-fondo-panel px-3 py-2.5 text-sm font-medium leading-relaxed text-titulo outline-none transition placeholder:text-apoyo/50 focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10
                                                {{ $errors->has('plan') ? 'border-estado-error' : 'border-borde' }}"></textarea>
                                    @error('plan')
                                        <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </section>

                            <div class="h-px bg-borde"></div>

                            {{-- =================================================
                            3. SIGNOS VITALES OPCIONALES
                            ================================================== --}}
                            <section class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-boton-acento/10 text-xs font-black text-boton-acento">3</span>
                                    <div>
                                        <h4 class="text-sm font-black text-titulo">Mediciones asociadas a la nota</h4>
                                        <p class="text-[11px] font-medium text-apoyo">Opcional. Estas mediciones quedan como
                                            instantánea de la consulta y no sustituyen el registro formal de signos vitales.
                                        </p>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-borde bg-fondo-panel/70 p-4">
                                    @if($puedeRegistrarSignos)
                                        <button type="button" wire:click="$toggle('incluirSignos')"
                                            class="flex w-full items-center justify-between gap-3 text-left"
                                            aria-expanded="{{ $incluirSignos ? 'true' : 'false' }}">
                                            <span class="flex min-w-0 items-center gap-3">
                                                <span
                                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-fondo-card text-apoyo shadow-sm">
                                                    <i class="ph-bold ph-heartbeat text-lg"></i>
                                                </span>
                                                <span class="min-w-0">
                                                    <span class="block text-sm font-black text-titulo">Incluir signos
                                                        vitales</span>
                                                    <span class="block text-[11px] font-medium text-apoyo">Registre únicamente
                                                        mediciones tomadas durante esta atención.</span>
                                                </span>
                                            </span>

                                            <span
                                                class="relative h-6 w-11 shrink-0 rounded-full transition {{ $incluirSignos ? 'bg-boton-acento' : 'bg-borde' }}">
                                                <span
                                                    class="absolute top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-all {{ $incluirSignos ? 'left-[22px]' : 'left-0.5' }}"></span>
                                            </span>
                                        </button>
                                    @else
                                        <div class="flex items-start gap-3">
                                            <span
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-fondo-card text-apoyo">
                                                <i class="ph-bold ph-lock-key text-lg"></i>
                                            </span>
                                            <div>
                                                <p class="text-sm font-black text-titulo">Signos vitales no disponibles en esta
                                                    nota</p>
                                                <p class="mt-0.5 text-[11px] font-medium text-apoyo">Su usuario no posee el
                                                    permiso <span class="font-bold">signos_vitales.crear</span>. La consulta
                                                    puede registrarse sin mediciones.</p>
                                            </div>
                                        </div>
                                    @endif

                                    @error('incluirSignos')
                                        <p
                                            class="mt-3 rounded-xl bg-estado-errorBg px-3 py-2 text-[10px] font-bold text-estado-error">
                                            {{ $message }}</p>
                                    @enderror

                                    @if($puedeRegistrarSignos && $incluirSignos)
                                        <div class="mt-4 border-t border-borde pt-4">
                                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                                {{-- PAS --}}
                                                <div>
                                                    <label for="pa_sistolica"
                                                        class="mb-1 block text-[10px] font-black uppercase tracking-wider text-apoyo">PA
                                                        sistólica</label>
                                                    <div class="relative">
                                                        <input id="pa_sistolica" wire:model="pa_sistolica" type="number"
                                                            min="{{ $pasMin }}" max="{{ $pasMax }}" step="1" inputmode="numeric"
                                                            placeholder="120"
                                                            aria-invalid="{{ $errors->has('pa_sistolica') ? 'true' : 'false' }}"
                                                            class="w-full rounded-xl border bg-fondo-card px-3 py-2.5 pr-14 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10 {{ $errors->has('pa_sistolica') ? 'border-estado-error' : 'border-borde' }}">
                                                        <span
                                                            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-[9px] font-bold text-apoyo">mmHg</span>
                                                    </div>
                                                    @error('pa_sistolica')
                                                        <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                {{-- PAD --}}
                                                <div>
                                                    <label for="pa_diastolica"
                                                        class="mb-1 block text-[10px] font-black uppercase tracking-wider text-apoyo">PA
                                                        diastólica</label>
                                                    <div class="relative">
                                                        <input id="pa_diastolica" wire:model="pa_diastolica" type="number"
                                                            min="{{ $padMin }}" max="{{ $padMax }}" step="1" inputmode="numeric"
                                                            placeholder="80"
                                                            aria-invalid="{{ $errors->has('pa_diastolica') ? 'true' : 'false' }}"
                                                            class="w-full rounded-xl border bg-fondo-card px-3 py-2.5 pr-14 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10 {{ $errors->has('pa_diastolica') ? 'border-estado-error' : 'border-borde' }}">
                                                        <span
                                                            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-[9px] font-bold text-apoyo">mmHg</span>
                                                    </div>
                                                    @error('pa_diastolica')
                                                        <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                {{-- FC --}}
                                                <div>
                                                    <label for="fc"
                                                        class="mb-1 block text-[10px] font-black uppercase tracking-wider text-apoyo">Frecuencia
                                                        cardíaca</label>
                                                    <div class="relative">
                                                        <input id="fc" wire:model="fc" type="number" min="{{ $fcMin }}"
                                                            max="{{ $fcMax }}" step="1" inputmode="numeric" placeholder="72"
                                                            aria-invalid="{{ $errors->has('fc') ? 'true' : 'false' }}"
                                                            class="w-full rounded-xl border bg-fondo-card px-3 py-2.5 pr-12 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10 {{ $errors->has('fc') ? 'border-estado-error' : 'border-borde' }}">
                                                        <span
                                                            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-[9px] font-bold text-apoyo">bpm</span>
                                                    </div>
                                                    @error('fc')
                                                        <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                {{-- FR --}}
                                                <div>
                                                    <label for="fr"
                                                        class="mb-1 block text-[10px] font-black uppercase tracking-wider text-apoyo">Frecuencia
                                                        respiratoria</label>
                                                    <div class="relative">
                                                        <input id="fr" wire:model="fr" type="number" min="{{ $frMin }}"
                                                            max="{{ $frMax }}" step="1" inputmode="numeric" placeholder="16"
                                                            aria-invalid="{{ $errors->has('fr') ? 'true' : 'false' }}"
                                                            class="w-full rounded-xl border bg-fondo-card px-3 py-2.5 pr-12 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10 {{ $errors->has('fr') ? 'border-estado-error' : 'border-borde' }}">
                                                        <span
                                                            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-[9px] font-bold text-apoyo">rpm</span>
                                                    </div>
                                                    @error('fr')
                                                        <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                {{-- Temperatura --}}
                                                <div>
                                                    <label for="temperatura"
                                                        class="mb-1 block text-[10px] font-black uppercase tracking-wider text-apoyo">Temperatura</label>
                                                    <div class="relative">
                                                        <input id="temperatura" wire:model="temperatura" type="number"
                                                            min="{{ $tempMin }}" max="{{ $tempMax }}" step="0.1"
                                                            inputmode="decimal" placeholder="36.5"
                                                            aria-invalid="{{ $errors->has('temperatura') ? 'true' : 'false' }}"
                                                            class="w-full rounded-xl border bg-fondo-card px-3 py-2.5 pr-10 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10 {{ $errors->has('temperatura') ? 'border-estado-error' : 'border-borde' }}">
                                                        <span
                                                            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-[9px] font-bold text-apoyo">°C</span>
                                                    </div>
                                                    @error('temperatura')
                                                        <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                {{-- SpO2 --}}
                                                <div>
                                                    <label for="saturacion"
                                                        class="mb-1 block text-[10px] font-black uppercase tracking-wider text-apoyo">Saturación
                                                        O₂</label>
                                                    <div class="relative">
                                                        <input id="saturacion" wire:model="saturacion" type="number"
                                                            min="{{ $spo2Min }}" max="{{ $spo2Max }}" step="1"
                                                            inputmode="numeric" placeholder="96"
                                                            aria-invalid="{{ $errors->has('saturacion') ? 'true' : 'false' }}"
                                                            class="w-full rounded-xl border bg-fondo-card px-3 py-2.5 pr-9 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10 {{ $errors->has('saturacion') ? 'border-estado-error' : 'border-borde' }}">
                                                        <span
                                                            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-[9px] font-bold text-apoyo">%</span>
                                                    </div>
                                                    @error('saturacion')
                                                        <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                {{-- Glucosa --}}
                                                <div>
                                                    <label for="glucosa"
                                                        class="mb-1 block text-[10px] font-black uppercase tracking-wider text-apoyo">Glucosa</label>
                                                    <div class="relative">
                                                        <input id="glucosa" wire:model="glucosa" type="number"
                                                            min="{{ $glucosaMin }}" step="0.1" inputmode="decimal"
                                                            placeholder="100"
                                                            aria-invalid="{{ $errors->has('glucosa') ? 'true' : 'false' }}"
                                                            class="w-full rounded-xl border bg-fondo-card px-3 py-2.5 pr-14 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10 {{ $errors->has('glucosa') ? 'border-estado-error' : 'border-borde' }}">
                                                        <span
                                                            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-[9px] font-bold text-apoyo">mg/dL</span>
                                                    </div>
                                                    @error('glucosa')
                                                        <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                {{-- Peso --}}
                                                <div>
                                                    <label for="peso"
                                                        class="mb-1 block text-[10px] font-black uppercase tracking-wider text-apoyo">Peso</label>
                                                    <div class="relative">
                                                        <input id="peso" wire:model="peso" type="number" min="{{ $pesoMin }}"
                                                            max="{{ $pesoMax }}" step="0.1" inputmode="decimal"
                                                            placeholder="65.0"
                                                            aria-invalid="{{ $errors->has('peso') ? 'true' : 'false' }}"
                                                            class="w-full rounded-xl border bg-fondo-card px-3 py-2.5 pr-10 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10 {{ $errors->has('peso') ? 'border-estado-error' : 'border-borde' }}">
                                                        <span
                                                            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-[9px] font-bold text-apoyo">kg</span>
                                                    </div>
                                                    @error('peso')
                                                        <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="mt-3 flex items-start gap-2 rounded-xl bg-estado-infoBg/60 px-3 py-2.5">
                                                <i class="ph-bold ph-info mt-0.5 text-sm text-estado-info"></i>
                                                <p class="text-[10px] font-medium leading-relaxed text-apoyo">
                                                    Los límites del formulario son controles técnicos de captura. No representan
                                                    por sí mismos diagnóstico ni umbrales de alerta clínica.
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </section>

                            <div class="h-px bg-borde"></div>

                            {{-- =================================================
                            4. OBSERVACIONES
                            ================================================== --}}
                            <section class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-boton-acento/10 text-xs font-black text-boton-acento">4</span>
                                    <div>
                                        <h4 class="text-sm font-black text-titulo">Observaciones adicionales</h4>
                                        <p class="text-[11px] font-medium text-apoyo">Use este espacio para información
                                            complementaria que no corresponda a los campos SOAP.</p>
                                    </div>
                                </div>

                                <div>
                                    <div class="mb-1.5 flex items-center justify-between gap-2">
                                        <label for="observaciones"
                                            class="text-xs font-black uppercase tracking-wider text-apoyo">Observaciones</label>
                                        <span class="text-[9px] font-bold text-apoyo">máx. 1000</span>
                                    </div>
                                    <textarea id="observaciones" wire:model="observaciones" rows="3" maxlength="1000"
                                        placeholder="Precauciones, acuerdos, información complementaria o contexto relevante..."
                                        aria-invalid="{{ $errors->has('observaciones') ? 'true' : 'false' }}"
                                        class="w-full resize-y rounded-xl border bg-fondo-panel px-3 py-2.5 text-sm font-medium leading-relaxed text-titulo outline-none transition placeholder:text-apoyo/50 focus:border-borde-focus focus:ring-2 focus:ring-boton-acento/10
                                                {{ $errors->has('observaciones') ? 'border-estado-error' : 'border-borde' }}"></textarea>
                                    @error('observaciones')
                                        <p class="mt-1 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </section>
                        </fieldset>
                    </div>

                    {{-- =========================================================
                    PIE FIJO
                    ========================================================== --}}
                    <footer class="shrink-0 border-t border-borde bg-fondo-card px-5 py-4 sm:px-6">
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-center gap-2 text-[10px] font-medium text-apoyo">
                                <i class="ph-bold ph-shield-check shrink-0 text-sm text-estado-exito"></i>
                                <span>El registro se asociará al usuario autenticado y quedará trazable en la historia
                                    clínica.</span>
                            </div>

                            <div class="flex shrink-0 items-center justify-end gap-2">
                                <button type="button" wire:click="cerrar" wire:loading.attr="disabled" wire:target="guardar"
                                    class="rm-btn-secondary h-10 px-5 disabled:cursor-not-allowed disabled:opacity-50">
                                    Cancelar
                                </button>

                                <button type="submit" wire:loading.attr="disabled" wire:target="guardar"
                                    class="rm-btn-primary flex h-10 min-w-[154px] items-center justify-center gap-2 px-5 disabled:cursor-wait disabled:opacity-60">
                                    <span wire:loading.remove wire:target="guardar" class="flex items-center gap-2">
                                        <i class="ph-bold ph-floppy-disk text-sm"></i>
                                        Guardar registro
                                    </span>
                                    <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"
                                            aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V4a8 8 0 00-8 8h4z"></path>
                                        </svg>
                                        Guardando...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </footer>
                </form>
            </div>
        </div>
    @endif
</div>