<div>
    @if($mostrar)
        @php
            $tema = match($cod_area) {
                'ARE_COG' => [
                    'icono' => 'ph-brain',
                    'fondo' => 'bg-estado-infoBg',
                    'texto' => 'text-estado-info',
                    'borde' => 'border-estado-info/30',
                    'chip'  => 'bg-estado-infoBg text-estado-info',
                    'titulo'=> 'Evaluación cognitiva',
                ],
                'ARE_AFE' => [
                    'icono' => 'ph-smiley-sad',
                    'fondo' => 'bg-estado-advertenciaBg',
                    'texto' => 'text-estado-advertencia',
                    'borde' => 'border-estado-advertencia/30',
                    'chip'  => 'bg-estado-advertenciaBg text-estado-advertencia',
                    'titulo'=> 'Evaluación afectiva',
                ],
                'ARE_FUN' => [
                    'icono' => 'ph-person-arms-spread',
                    'fondo' => 'bg-estado-exitoBg',
                    'texto' => 'text-estado-exito',
                    'borde' => 'border-estado-exito/30',
                    'chip'  => 'bg-estado-exitoBg text-estado-exito',
                    'titulo'=> 'Evaluación funcional',
                ],
                'ARE_NUT' => [
                    'icono' => 'ph-bowl-food',
                    'fondo' => 'bg-boton-acento/10',
                    'texto' => 'text-boton-acento',
                    'borde' => 'border-boton-acento/30',
                    'chip'  => 'bg-boton-acento/10 text-boton-acento',
                    'titulo'=> 'Evaluación nutricional',
                ],
                'ARE_SOC' => [
                    'icono' => 'ph-users-three',
                    'fondo' => 'bg-estado-errorBg',
                    'texto' => 'text-estado-error',
                    'borde' => 'border-estado-error/30',
                    'chip'  => 'bg-estado-errorBg text-estado-error',
                    'titulo'=> 'Evaluación social',
                ],
                default => [
                    'icono' => 'ph-clipboard-text',
                    'fondo' => 'bg-boton-acento/10',
                    'texto' => 'text-boton-acento',
                    'borde' => 'border-boton-acento/30',
                    'chip'  => 'bg-boton-acento/10 text-boton-acento',
                    'titulo'=> 'Evaluación geriátrica',
                ],
            };

            $tipoResultado = strtoupper((string) ($instrumentoSeleccionado?->tipo_resultado ?? ''));
            $esTiempo = $tipoResultado === 'TIEMPO';
            $esCualitativo = in_array($tipoResultado, ['CUALITATIVO', 'FRACCION_VISUAL'], true);
        @endphp

        <div
            class="fixed inset-0 z-[2147483646] flex items-center justify-center overflow-y-auto bg-slate-950/65 p-3 backdrop-blur-md sm:p-6"
            x-data="{
                visible: false,
                cerrarModal() {
                    this.visible = false;
                    window.setTimeout(() => $wire.cerrar(), 180);
                }
            }"
            x-init="$nextTick(() => visible = true); document.body.style.overflow = 'hidden'"
            x-on:destroy="document.body.style.overflow = ''"
            x-on:keydown.escape.window="cerrarModal()"
            x-on:click.self="cerrarModal()"
            role="dialog"
            aria-modal="true"
            aria-labelledby="evaluacion-geriatrica-title"
        >
            <div
                x-show="visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-10 scale-90"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-180"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-6 scale-95"
                class="relative flex max-h-[94vh] w-full max-w-4xl flex-col overflow-hidden rounded-[30px] border border-borde bg-fondo-card shadow-[0_32px_90px_-20px_rgba(0,0,0,.65)]"
            >
                {{-- CABECERA --}}
                <div class="relative shrink-0 overflow-hidden border-b border-borde bg-fondo-card px-5 py-5 sm:px-7">
                    <div class="pointer-events-none absolute -right-16 -top-16 h-44 w-44 rounded-full {{ $tema['fondo'] }} opacity-70 blur-3xl"></div>
                    <div class="pointer-events-none absolute -left-12 bottom-0 h-24 w-24 rounded-full {{ $tema['fondo'] }} opacity-40 blur-2xl"></div>

                    <div class="relative flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border {{ $tema['borde'] }} {{ $tema['fondo'] }} {{ $tema['texto'] }} shadow-sm">
                                <span class="absolute inset-0 rounded-2xl animate-ping {{ $tema['fondo'] }} opacity-20"></span>
                                <i class="ph-fill {{ $tema['icono'] }} relative text-3xl"></i>
                            </div>

                            <div class="min-w-0">
                                <div class="mb-1 flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[.18em] {{ $tema['chip'] }}">
                                        Evaluación geriátrica
                                    </span>
                                    @if($nombreArea)
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-apoyo">
                                            {{ $nombreArea }}
                                        </span>
                                    @endif
                                </div>
                                <h2 id="evaluacion-geriatrica-title" class="truncate text-xl font-black tracking-tight text-titulo sm:text-2xl">
                                    {{ $tema['titulo'] }}
                                </h2>
                                <p class="mt-1 max-w-2xl text-xs font-medium leading-relaxed text-apoyo sm:text-sm">
                                    Registre el instrumento aplicado y su resultado. Los campos obligatorios se adaptan automáticamente al tipo de instrumento.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            x-on:click="cerrarModal()"
                            class="group flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-borde bg-fondo-panel text-apoyo shadow-sm transition duration-200 hover:-rotate-6 hover:scale-110 hover:border-estado-error/30 hover:bg-estado-errorBg hover:text-estado-error focus:outline-none focus:ring-2 focus:ring-boton-acento/30"
                            aria-label="Cerrar evaluación"
                        >
                            <i class="ph-bold ph-x text-lg transition-transform duration-200 group-hover:rotate-90"></i>
                        </button>
                    </div>
                </div>

                <form wire:submit="guardar" class="flex min-h-0 flex-1 flex-col">
                    <fieldset wire:loading.attr="disabled" wire:target="guardar" class="min-h-0 flex-1 overflow-y-auto custom-scrollbar">
                        <div class="space-y-6 p-5 sm:p-7">

                            {{-- RESUMEN DE ERRORES --}}
                            @if($errors->any())
                                <div
                                    x-data="{show:false}"
                                    x-init="$nextTick(() => show = true)"
                                    x-show="show"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    class="rounded-2xl border border-estado-error/30 bg-estado-errorBg p-4 shadow-sm"
                                    role="alert"
                                >
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-fondo-card text-estado-error shadow-sm">
                                            <i class="ph-fill ph-warning-circle animate-pulse text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-estado-error">Revise los datos antes de guardar</p>
                                            <p class="mt-1 text-xs font-medium text-titulo/80">
                                                Hay campos incompletos o con valores no válidos. Corrija los elementos marcados en rojo.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- 1. CONTEXTO CLÍNICO --}}
                            <section class="rounded-[22px] border border-borde bg-fondo-panel/70 p-4 shadow-sm sm:p-5">
                                <div class="mb-4 flex items-center gap-3 border-b border-borde/70 pb-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                                        <i class="ph-bold ph-user-focus text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-black uppercase tracking-[.16em] text-titulo">Contexto clínico</h3>
                                        <p class="mt-0.5 text-[11px] font-medium text-apoyo">Confirme residente, área y fecha de evaluación.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                    {{-- Residente --}}
                                    <div class="lg:col-span-2">
                                        @if($pacienteFijado && $adultoSeleccionado)
                                            <div class="relative overflow-hidden rounded-2xl border border-borde bg-fondo-card p-4 shadow-sm">
                                                <div class="absolute right-0 top-0 h-full w-1.5 {{ $tema['fondo'] }}"></div>
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $tema['fondo'] }} {{ $tema['texto'] }} text-base font-black">
                                                        {{ mb_substr((string) $adultoSeleccionado->nombres, 0, 1) }}{{ mb_substr((string) $adultoSeleccionado->ap_paterno, 0, 1) }}
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <p class="truncate text-sm font-black text-titulo">
                                                                {{ $adultoSeleccionado->nombre_completo }}
                                                            </p>
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-fondo-panel px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-apoyo">
                                                                <i class="ph-bold ph-lock-key"></i> Residente fijado
                                                            </span>
                                                        </div>
                                                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-[11px] font-semibold text-apoyo">
                                                            <span><i class="ph-bold ph-identification-card mr-1"></i>CI: {{ $adultoSeleccionado->ci ?: 'No registrado' }}</span>
                                                            <span><i class="ph-bold ph-calendar mr-1"></i>{{ $adultoSeleccionado->edad_texto }}</span>
                                                            <span><i class="ph-bold ph-activity mr-1"></i>{{ $adultoSeleccionado->estado_humano }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @error('cod_am')
                                                <p class="mt-2 flex items-center gap-1 text-[11px] font-bold text-estado-error">
                                                    <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                                </p>
                                            @enderror
                                        @else
                                            <label for="eval-residente" class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-apoyo">
                                                Residente <span class="text-estado-error">*</span>
                                            </label>
                                            <div class="relative">
                                                <i class="ph-bold ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                                <select
                                                    id="eval-residente"
                                                    wire:model.live="cod_am"
                                                    aria-invalid="{{ $errors->has('cod_am') ? 'true' : 'false' }}"
                                                    class="w-full appearance-none rounded-xl border bg-fondo-card py-3 pl-10 pr-10 text-sm font-bold text-titulo outline-none transition duration-200 focus:ring-4 focus:ring-boton-acento/10 @error('cod_am') border-estado-error focus:border-estado-error @else border-borde focus:border-borde-focus @enderror"
                                                >
                                                    <option value="">Seleccione un residente...</option>
                                                    @foreach($pacientes as $pac)
                                                        <option value="{{ $pac->cod_am }}">
                                                            {{ $pac->nombre_completo ?? trim($pac->nombres.' '.$pac->ap_paterno.' '.$pac->ap_materno) }}{{ $pac->ci ? ' — CI: '.$pac->ci : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <i class="ph-bold ph-caret-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                            </div>
                                            @error('cod_am')
                                                <p class="mt-1.5 flex items-center gap-1 text-[11px] font-bold text-estado-error">
                                                    <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                                </p>
                                            @enderror
                                        @endif
                                    </div>

                                    {{-- Área fijada --}}
                                    <div>
                                        <span class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-apoyo">Área geriátrica</span>
                                        <div class="flex min-h-[46px] items-center gap-3 rounded-xl border {{ $tema['borde'] }} {{ $tema['fondo'] }} px-3.5 py-2.5">
                                            <i class="ph-bold {{ $tema['icono'] }} {{ $tema['texto'] }}"></i>
                                            <span class="min-w-0 flex-1 truncate text-sm font-black text-titulo">{{ $nombreArea ?: 'Área no disponible' }}</span>
                                            <i class="ph-bold ph-lock-key text-xs {{ $tema['texto'] }}"></i>
                                        </div>
                                        @error('cod_area')
                                            <p class="mt-1.5 flex items-center gap-1 text-[11px] font-bold text-estado-error">
                                                <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    {{-- Fecha --}}
                                    <div>
                                        <label for="eval-fecha" class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-apoyo">
                                            Fecha de evaluación <span class="text-estado-error">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="ph-bold ph-calendar-blank absolute left-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                            <input
                                                id="eval-fecha"
                                                type="date"
                                                wire:model="fecha_eval"
                                                max="{{ today()->toDateString() }}"
                                                aria-invalid="{{ $errors->has('fecha_eval') ? 'true' : 'false' }}"
                                                class="w-full rounded-xl border bg-fondo-card py-3 pl-10 pr-3 text-sm font-bold text-titulo outline-none transition duration-200 focus:ring-4 focus:ring-boton-acento/10 @error('fecha_eval') border-estado-error focus:border-estado-error @else border-borde focus:border-borde-focus @enderror"
                                            >
                                        </div>
                                        @error('fecha_eval')
                                            <p class="mt-1.5 flex items-center gap-1 text-[11px] font-bold text-estado-error">
                                                <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            {{-- 2. INSTRUMENTO --}}
                            <section class="rounded-[22px] border border-borde bg-fondo-panel/70 p-4 shadow-sm sm:p-5">
                                <div class="mb-4 flex items-center gap-3 border-b border-borde/70 pb-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $tema['fondo'] }} {{ $tema['texto'] }}">
                                        <i class="ph-bold ph-ruler text-lg"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-xs font-black uppercase tracking-[.16em] text-titulo">Instrumento aplicado</h3>
                                        <p class="mt-0.5 text-[11px] font-medium text-apoyo">Elija la escala o prueba utilizada. El formulario se adapta a su tipo de resultado.</p>
                                    </div>
                                </div>

                                <label for="eval-instrumento" class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-apoyo">
                                    Instrumento / escala <span class="text-estado-error">*</span>
                                </label>
                                <div class="relative">
                                    <i class="ph-bold ph-list-checks absolute left-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                    <select
                                        id="eval-instrumento"
                                        wire:model.live="cod_instrumento"
                                        @disabled(empty($cod_area))
                                        aria-invalid="{{ $errors->has('cod_instrumento') ? 'true' : 'false' }}"
                                        class="w-full appearance-none rounded-xl border bg-fondo-card py-3 pl-10 pr-10 text-sm font-bold text-titulo outline-none transition duration-200 focus:ring-4 focus:ring-boton-acento/10 disabled:cursor-not-allowed disabled:opacity-50 @error('cod_instrumento') border-estado-error focus:border-estado-error @else border-borde focus:border-borde-focus @enderror"
                                    >
                                        <option value="">Seleccione un instrumento...</option>
                                        @foreach($instrumentos as $inst)
                                            <option value="{{ $inst->cod_instrumento }}">
                                                {{ $inst->nombre }}{{ $inst->siglas ? ' ('.$inst->siglas.')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="ph-bold ph-caret-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                </div>
                                @error('cod_instrumento')
                                    <p class="mt-1.5 flex items-center gap-1 text-[11px] font-bold text-estado-error">
                                        <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                    </p>
                                @enderror

                                @if($instrumentoSeleccionado)
                                    <div
                                        wire:key="instrumento-info-{{ $instrumentoSeleccionado->cod_instrumento }}"
                                        x-data="{show:false}"
                                        x-init="$nextTick(() => show = true)"
                                        x-show="show"
                                        x-transition:enter="transition ease-out duration-500"
                                        x-transition:enter-start="opacity-0 translate-y-6 scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                        class="mt-4 overflow-hidden rounded-2xl border {{ $tema['borde'] }} bg-fondo-card shadow-sm"
                                    >
                                        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h4 class="text-sm font-black text-titulo">{{ $instrumentoSeleccionado->nombre }}</h4>
                                                    @if($instrumentoSeleccionado->siglas)
                                                        <span class="rounded-full px-2 py-0.5 text-[9px] font-black uppercase {{ $tema['chip'] }}">{{ $instrumentoSeleccionado->siglas }}</span>
                                                    @endif
                                                </div>
                                                @if($instrumentoSeleccionado->descripcion)
                                                    <p class="mt-1.5 max-w-2xl text-[11px] font-medium leading-relaxed text-apoyo">
                                                        {{ $instrumentoSeleccionado->descripcion }}
                                                    </p>
                                                @endif
                                            </div>
                                            <span class="shrink-0 rounded-xl bg-fondo-panel px-3 py-1.5 text-[9px] font-black uppercase tracking-wider text-apoyo">
                                                {{ str_replace('_', ' ', $tipoResultado ?: 'Resultado') }}
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-1 border-t border-borde/70 sm:grid-cols-3">
                                            <div class="p-3.5 sm:border-r sm:border-borde/70">
                                                <p class="text-[9px] font-black uppercase tracking-wider text-apoyo">Puntaje máximo</p>
                                                <p class="mt-1 text-sm font-black text-titulo">
                                                    {{ $instrumentoSeleccionado->puntaje_maximo !== null ? number_format((float) $instrumentoSeleccionado->puntaje_maximo, 2, ',', '.') : 'No definido' }}
                                                </p>
                                            </div>
                                            <div class="border-t border-borde/70 p-3.5 sm:border-r sm:border-t-0 sm:border-borde/70">
                                                <p class="text-[9px] font-black uppercase tracking-wider text-apoyo">Corte normal</p>
                                                <p class="mt-1 text-sm font-black text-estado-exito">
                                                    {{ $instrumentoSeleccionado->punto_corte_normal !== null ? number_format((float) $instrumentoSeleccionado->punto_corte_normal, 2, ',', '.') : 'No definido' }}
                                                </p>
                                            </div>
                                            <div class="border-t border-borde/70 p-3.5 sm:border-t-0">
                                                <p class="text-[9px] font-black uppercase tracking-wider text-apoyo">Corte de riesgo</p>
                                                <p class="mt-1 text-sm font-black text-estado-advertencia">
                                                    {{ $instrumentoSeleccionado->punto_corte_riesgo !== null ? number_format((float) $instrumentoSeleccionado->punto_corte_riesgo, 2, ',', '.') : 'No definido' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-4 rounded-2xl border border-dashed border-borde bg-fondo-card/50 px-4 py-5 text-center">
                                        <i class="ph-bold ph-cursor-click text-2xl text-apoyo/60"></i>
                                        <p class="mt-2 text-xs font-bold text-apoyo">Seleccione un instrumento para habilitar la captura del resultado.</p>
                                    </div>
                                @endif
                            </section>

                            {{-- 3. RESULTADO --}}
                            <section class="rounded-[22px] border border-borde bg-fondo-panel/70 p-4 shadow-sm sm:p-5">
                                <div class="mb-4 flex items-center gap-3 border-b border-borde/70 pb-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                                        <i class="ph-bold ph-chart-line-up text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-black uppercase tracking-[.16em] text-titulo">Resultado obtenido</h3>
                                        <p class="mt-0.5 text-[11px] font-medium text-apoyo">Capture solo los datos que corresponden al instrumento seleccionado.</p>
                                    </div>
                                </div>

                                @if(!$instrumentoSeleccionado)
                                    <div class="rounded-2xl border border-dashed border-borde bg-fondo-card/50 p-6 text-center">
                                        <i class="ph-bold ph-lock-key text-2xl text-apoyo/60"></i>
                                        <p class="mt-2 text-sm font-black text-titulo">Resultado bloqueado temporalmente</p>
                                        <p class="mt-1 text-xs text-apoyo">Primero seleccione el instrumento aplicado.</p>
                                    </div>
                                @else
                                    <div
                                        wire:key="resultado-{{ $instrumentoSeleccionado->cod_instrumento }}"
                                        x-data="{show:false}"
                                        x-init="$nextTick(() => show = true)"
                                        x-show="show"
                                        x-transition:enter="transition ease-out duration-500"
                                        x-transition:enter-start="opacity-0 translate-x-8"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        class="space-y-5"
                                    >
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            @if($this->permitePuntaje)
                                                <div>
                                                    <label for="eval-puntaje" class="mb-1.5 flex items-center justify-between gap-3 text-[10px] font-black uppercase tracking-wider text-apoyo">
                                                        <span>
                                                            {{ $esTiempo ? 'Tiempo obtenido' : 'Puntaje / valor numérico' }}
                                                            @if($this->requierePuntaje)<span class="text-estado-error">*</span>@endif
                                                        </span>
                                                        @if($this->puntajeMaximo !== null && !$esTiempo)
                                                            <span class="rounded-full bg-fondo-card px-2 py-0.5 text-[9px] text-boton-acento">Máx. {{ number_format($this->puntajeMaximo, 2, ',', '.') }}</span>
                                                        @endif
                                                    </label>
                                                    <div class="relative">
                                                        <i class="ph-bold {{ $esTiempo ? 'ph-timer' : 'ph-hash' }} absolute left-3.5 top-1/2 -translate-y-1/2 {{ $tema['texto'] }}"></i>
                                                        <input
                                                            id="eval-puntaje"
                                                            type="number"
                                                            step="0.01"
                                                            min="0"
                                                            @if($this->puntajeMaximo !== null && !$esTiempo) max="{{ $this->puntajeMaximo }}" @endif
                                                            wire:model="puntaje_total"
                                                            aria-invalid="{{ $errors->has('puntaje_total') ? 'true' : 'false' }}"
                                                            placeholder="{{ $esTiempo ? 'Ej.: 18.5 segundos' : 'Ej.: 24' }}"
                                                            class="w-full rounded-xl border bg-fondo-card py-3 pl-10 pr-3 text-sm font-black text-titulo outline-none transition duration-200 focus:ring-4 focus:ring-boton-acento/10 @error('puntaje_total') border-estado-error focus:border-estado-error @else border-borde focus:border-borde-focus @enderror"
                                                        >
                                                    </div>
                                                    @error('puntaje_total')
                                                        <p class="mt-1.5 flex items-center gap-1 text-[11px] font-bold text-estado-error">
                                                            <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                                        </p>
                                                    @enderror
                                                    <p class="mt-1 text-[10px] font-medium text-apoyo">
                                                        {{ $esTiempo ? 'Registre el tiempo en segundos según el instrumento aplicado.' : 'Ingrese exactamente el puntaje obtenido, sin interpretar el resultado aquí.' }}
                                                    </p>
                                                </div>
                                            @endif

                                            <div class="{{ $this->permitePuntaje ? '' : 'md:col-span-2' }}">
                                                <label for="eval-categoria" class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-apoyo">
                                                    Resultado / categoría clínica
                                                    @if($this->requiereResultadoCualitativo)<span class="text-estado-error">*</span>@endif
                                                </label>
                                                <div class="relative">
                                                    <i class="ph-bold ph-text-aa absolute left-3.5 top-3.5 text-apoyo"></i>
                                                    <input
                                                        id="eval-categoria"
                                                        type="text"
                                                        maxlength="150"
                                                        wire:model="categoria_resultado"
                                                        aria-invalid="{{ $errors->has('categoria_resultado') ? 'true' : 'false' }}"
                                                        placeholder="Ej.: normal, deterioro leve, riesgo identificado..."
                                                        class="w-full rounded-xl border bg-fondo-card py-3 pl-10 pr-3 text-sm font-bold text-titulo outline-none transition duration-200 focus:ring-4 focus:ring-boton-acento/10 @error('categoria_resultado') border-estado-error focus:border-estado-error @else border-borde focus:border-borde-focus @enderror"
                                                    >
                                                </div>
                                                @error('categoria_resultado')
                                                    <p class="mt-1.5 flex items-center gap-1 text-[11px] font-bold text-estado-error">
                                                        <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                                    </p>
                                                @enderror
                                                <p class="mt-1 text-[10px] font-medium text-apoyo">Máximo 150 caracteres. Use la denominación clínica correspondiente al instrumento.</p>
                                            </div>
                                        </div>

                                        {{-- ALERTA VISUAL --}}
                                        <div>
                                            <div class="mb-2 flex items-center justify-between gap-3">
                                                <label class="text-[10px] font-black uppercase tracking-wider text-apoyo">
                                                    Nivel de alerta <span class="text-estado-error">*</span>
                                                </label>
                                                <span class="text-[9px] font-bold text-apoyo">Seleccione una opción</span>
                                            </div>

                                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                                <label class="group cursor-pointer">
                                                    <input type="radio" wire:model.live="nivel_alerta" value="NORMAL" class="peer sr-only">
                                                    <div class="rounded-2xl border border-borde bg-fondo-card p-3.5 transition duration-200 group-hover:-translate-y-0.5 group-hover:shadow-md peer-checked:scale-[1.02] peer-checked:border-estado-exito/50 peer-checked:bg-estado-exitoBg peer-checked:shadow-md">
                                                        <div class="flex items-center gap-3">
                                                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito peer-checked:bg-fondo-card">
                                                                <i class="ph-bold ph-check-circle text-lg"></i>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs font-black text-titulo">Normal</p>
                                                                <p class="text-[10px] font-medium text-apoyo">Sin señal de alerta</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>

                                                <label class="group cursor-pointer">
                                                    <input type="radio" wire:model.live="nivel_alerta" value="PREVENTIVO" class="peer sr-only">
                                                    <div class="rounded-2xl border border-borde bg-fondo-card p-3.5 transition duration-200 group-hover:-translate-y-0.5 group-hover:shadow-md peer-checked:scale-[1.02] peer-checked:border-estado-advertencia/50 peer-checked:bg-estado-advertenciaBg peer-checked:shadow-md">
                                                        <div class="flex items-center gap-3">
                                                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia">
                                                                <i class="ph-bold ph-warning text-lg"></i>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs font-black text-titulo">Preventivo</p>
                                                                <p class="text-[10px] font-medium text-apoyo">Requiere seguimiento</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>

                                                <label class="group cursor-pointer">
                                                    <input type="radio" wire:model.live="nivel_alerta" value="CRITICO" class="peer sr-only">
                                                    <div class="rounded-2xl border border-borde bg-fondo-card p-3.5 transition duration-200 group-hover:-translate-y-0.5 group-hover:shadow-md peer-checked:scale-[1.02] peer-checked:border-estado-error/50 peer-checked:bg-estado-errorBg peer-checked:shadow-md">
                                                        <div class="flex items-center gap-3">
                                                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-errorBg text-estado-error">
                                                                <i class="ph-bold ph-siren text-lg"></i>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs font-black text-titulo">Crítico</p>
                                                                <p class="text-[10px] font-medium text-apoyo">Atención prioritaria</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                            @error('nivel_alerta')
                                                <p class="mt-1.5 flex items-center gap-1 text-[11px] font-bold text-estado-error">
                                                    <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <div>
                                                <label for="eval-riesgo" class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-apoyo">
                                                    Nivel de riesgo <span class="font-semibold normal-case text-apoyo/70">(opcional)</span>
                                                </label>
                                                <div class="relative">
                                                    <i class="ph-bold ph-shield-warning absolute left-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                                    <input
                                                        id="eval-riesgo"
                                                        type="text"
                                                        maxlength="30"
                                                        wire:model="nivel_riesgo"
                                                        aria-invalid="{{ $errors->has('nivel_riesgo') ? 'true' : 'false' }}"
                                                        placeholder="Ej.: bajo, moderado, alto"
                                                        class="w-full rounded-xl border bg-fondo-card py-3 pl-10 pr-3 text-sm font-bold text-titulo outline-none transition duration-200 focus:ring-4 focus:ring-boton-acento/10 @error('nivel_riesgo') border-estado-error focus:border-estado-error @else border-borde focus:border-borde-focus @enderror"
                                                    >
                                                </div>
                                                @error('nivel_riesgo')
                                                    <p class="mt-1.5 flex items-center gap-1 text-[11px] font-bold text-estado-error">
                                                        <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                                    </p>
                                                @enderror
                                            </div>

                                            <div class="rounded-2xl border border-borde bg-fondo-card p-3.5">
                                                <p class="text-[9px] font-black uppercase tracking-wider text-apoyo">Estado de captura</p>
                                                <div class="mt-2 flex items-center gap-2">
                                                    @if($this->puedeGuardar)
                                                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                                                            <i class="ph-bold ph-check-circle"></i>
                                                        </span>
                                                        <div>
                                                            <p class="text-xs font-black text-titulo">Datos mínimos completos</p>
                                                            <p class="text-[10px] text-apoyo">Puede continuar con observaciones o guardar.</p>
                                                        </div>
                                                    @else
                                                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia">
                                                            <i class="ph-bold ph-hourglass-medium animate-pulse"></i>
                                                        </span>
                                                        <div>
                                                            <p class="text-xs font-black text-titulo">Faltan datos requeridos</p>
                                                            <p class="text-[10px] text-apoyo">Complete los campos marcados con *.</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </section>

                            {{-- 4. OBSERVACIONES --}}
                            <section class="rounded-[22px] border border-borde bg-fondo-panel/70 p-4 shadow-sm sm:p-5">
                                <div class="mb-4 flex items-center gap-3 border-b border-borde/70 pb-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                                        <i class="ph-bold ph-note-pencil text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-black uppercase tracking-[.16em] text-titulo">Observaciones del evaluador</h3>
                                        <p class="mt-0.5 text-[11px] font-medium text-apoyo">Añada contexto relevante que no quede representado por el puntaje o la categoría.</p>
                                    </div>
                                </div>

                                <label for="eval-observaciones" class="sr-only">Observaciones del evaluador</label>
                                <textarea
                                    id="eval-observaciones"
                                    wire:model="observaciones"
                                    rows="4"
                                    maxlength="1000"
                                    aria-invalid="{{ $errors->has('observaciones') ? 'true' : 'false' }}"
                                    placeholder="Ej.: condiciones durante la aplicación, dificultades de comprensión, colaboración, hallazgos relevantes o recomendaciones..."
                                    class="w-full resize-y rounded-2xl border bg-fondo-card px-4 py-3 text-sm font-medium leading-relaxed text-titulo placeholder:text-apoyo/50 outline-none transition duration-200 focus:ring-4 focus:ring-boton-acento/10 @error('observaciones') border-estado-error focus:border-estado-error @else border-borde focus:border-borde-focus @enderror"
                                ></textarea>
                                <div class="mt-1.5 flex items-start justify-between gap-3">
                                    @error('observaciones')
                                        <p class="flex items-center gap-1 text-[11px] font-bold text-estado-error">
                                            <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                        </p>
                                    @else
                                        <p class="text-[10px] font-medium text-apoyo">Máximo 1000 caracteres.</p>
                                    @enderror
                                    <span class="text-[10px] font-bold text-apoyo/70">Opcional</span>
                                </div>
                            </section>
                        </div>
                    </fieldset>

                    {{-- PIE FIJO --}}
                    <div class="shrink-0 border-t border-borde bg-fondo-card/95 px-5 py-4 backdrop-blur sm:px-7">
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-2 text-[10px] font-medium text-apoyo">
                                <i class="ph-bold ph-shield-check text-boton-acento"></i>
                                La evaluación será registrada con su usuario y fecha de captura.
                            </div>

                            <div class="flex items-center justify-end gap-2.5">
                                <button
                                    type="button"
                                    x-on:click="cerrarModal()"
                                    wire:loading.attr="disabled"
                                    wire:target="guardar"
                                    class="h-11 rounded-xl border border-borde bg-fondo-panel px-5 text-xs font-black text-titulo shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-fondo-card hover:shadow-md active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Cancelar
                                </button>

                                <button
                                    type="submit"
                                    @disabled(!$this->puedeGuardar)
                                    wire:loading.attr="disabled"
                                    wire:target="guardar"
                                    class="group flex h-11 min-w-[180px] items-center justify-center gap-2 rounded-xl bg-boton-principal px-6 text-xs font-black uppercase tracking-wider text-inverso shadow-lg shadow-black/10 transition duration-200 enabled:hover:-translate-y-0.5 enabled:hover:shadow-xl enabled:active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-45"
                                >
                                    <span wire:loading.remove wire:target="guardar" class="flex items-center gap-2">
                                        <i class="ph-bold ph-floppy-disk text-base transition-transform duration-200 group-enabled:group-hover:scale-110"></i>
                                        Guardar evaluación
                                    </span>
                                    <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                                        <i class="ph-bold ph-circle-notch animate-spin text-base"></i>
                                        Guardando...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- CARGA GLOBAL DEL GUARDADO --}}
                <div wire:loading.flex wire:target="guardar" class="pointer-events-none absolute inset-x-0 top-0 z-20 h-1 overflow-hidden bg-boton-acento/10">
                    <div class="h-full w-1/3 animate-pulse rounded-full bg-boton-acento"></div>
                </div>
            </div>
        </div>
    @endif
</div>
