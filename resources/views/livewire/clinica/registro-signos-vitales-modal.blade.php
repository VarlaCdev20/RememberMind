<div>
@if($mostrar)
@php

    $nombreCompleto = $adulto?->nombre_completo
        ?? trim(($adulto?->nombres ?? '') . ' ' . ($adulto?->ap_paterno ?? '') . ' ' . ($adulto?->ap_materno ?? ''));

    $estadoHumano = $adulto?->estado_humano ?? 'Sin estado';
    $edadTexto = $adulto?->edad_texto ?? null;
@endphp

<div
    class="fixed inset-0 z-[2147483646] flex items-center justify-center overflow-y-auto bg-slate-950/60 p-3 backdrop-blur-md sm:p-6"
    x-data
    x-on:keydown.escape.window="$wire.cerrar()"
    role="dialog"
    aria-modal="true"
    aria-labelledby="registro-signos-title"
>
    {{-- Fondo clicable --}}
    <button
        type="button"
        class="fixed inset-0 cursor-default"
        wire:click="cerrar"
        aria-label="Cerrar ventana"
        tabindex="-1"
    ></button>

    {{-- Modal --}}
    <div
        class="relative z-10 flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-[30px] border border-borde bg-fondo-card shadow-2xl"
        x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="translate-y-8 scale-[0.94] opacity-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
        x-transition:leave="transition duration-200 ease-in"
        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
        x-transition:leave-end="translate-y-5 scale-[0.97] opacity-0"
    >
        {{-- Barra de progreso durante guardado --}}
        <div
            wire:loading.flex
            wire:target="guardar"
            class="absolute inset-x-0 top-0 z-50 h-1 overflow-hidden bg-boton-acento/15"
        >
            <div class="h-full w-1/3 animate-pulse rounded-full bg-boton-acento"></div>
        </div>

        {{-- Header --}}
        <header class="shrink-0 border-b border-borde bg-fondo-card px-5 py-4 sm:px-7">
            <div class="flex items-start justify-between gap-4">
                <div class="flex min-w-0 items-start gap-4">
                    <div class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-estado-errorBg text-estado-error shadow-sm">
                        <i class="ph-fill ph-heartbeat text-2xl"></i>
                        <span class="absolute -right-1 -top-1 h-3 w-3 animate-pulse rounded-full border-2 border-fondo-card bg-estado-exito"></span>
                    </div>

                    <div class="min-w-0">
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-black uppercase tracking-[0.18em] text-boton-acento">
                                Registro clínico
                            </span>
                            <span class="rounded-full bg-estado-exitoBg px-2 py-0.5 text-[10px] font-black text-estado-exito">
                                Acceso validado
                            </span>
                        </div>

                        <h2 id="registro-signos-title" class="text-xl font-black leading-tight text-titulo sm:text-2xl">
                            Registrar signos vitales
                        </h2>

                        <p class="mt-1 text-xs font-semibold text-apoyo">
                            Registre únicamente mediciones obtenidas en este control.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="cerrar"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-borde bg-fondo-panel text-apoyo transition duration-200 hover:rotate-90 hover:border-estado-error/40 hover:bg-estado-errorBg hover:text-estado-error active:scale-90"
                    aria-label="Cerrar"
                >
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            {{-- Contexto del residente --}}
            @if($adulto)
            <div class="mt-4 grid gap-3 rounded-2xl border border-borde bg-fondo-panel/70 p-3 sm:grid-cols-[1fr_auto] sm:items-center sm:p-4">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-boton-acento/10 text-sm font-black text-boton-acento">
                        {{ mb_strtoupper(mb_substr($adulto->nombres ?? 'R', 0, 1) . mb_substr($adulto->ap_paterno ?? '', 0, 1)) }}
                    </div>

                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-titulo">{{ $nombreCompleto ?: 'Residente' }}</p>
                        <div class="mt-0.5 flex flex-wrap gap-x-3 gap-y-1 text-[11px] font-semibold text-apoyo">
                            @if($adulto->ci)
                                <span>CI {{ $adulto->ci }}</span>
                            @endif
                            @if($edadTexto)
                                <span>{{ $edadTexto }}</span>
                            @endif
                            <span>{{ $estadoHumano }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 rounded-xl border border-borde bg-fondo-card px-3 py-2 text-[11px] font-bold text-apoyo">
                    <i class="ph-bold ph-lock-key text-boton-acento"></i>
                    Residente fijado
                </div>
            </div>
            @endif
        </header>

        <form wire:submit="guardar" class="flex min-h-0 flex-1 flex-col">
            <fieldset
                class="min-h-0 flex-1 overflow-y-auto"
                wire:loading.attr="disabled"
                wire:target="guardar"
            >
                <div class="space-y-5 p-5 sm:p-7">

                    {{-- Estado general del formulario --}}
                    <div class="grid gap-2 sm:grid-cols-3">
                        <div class="flex items-center gap-2 rounded-xl border border-estado-exito/20 bg-estado-exitoBg px-3 py-2.5">
                            <i class="ph-fill ph-shield-check text-lg text-estado-exito"></i>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider text-estado-exito">Acceso</p>
                                <p class="text-[11px] font-bold text-titulo">Autorizado</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 rounded-xl border px-3 py-2.5 transition
                            {{ $tieneMedicion ? 'border-estado-exito/20 bg-estado-exitoBg' : 'border-borde bg-fondo-panel' }}">
                            <i class="ph-fill {{ $tieneMedicion ? 'ph-check-circle text-estado-exito' : 'ph-gauge text-apoyo' }} text-lg"></i>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider {{ $tieneMedicion ? 'text-estado-exito' : 'text-apoyo' }}">
                                    Mediciones
                                </p>
                                <p class="text-[11px] font-bold text-titulo">
                                    {{ $tieneMedicion ? 'Dato clínico registrado' : 'Falta al menos una' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 rounded-xl border px-3 py-2.5 transition
                            {{ $puedeGuardar ? 'border-estado-exito/20 bg-estado-exitoBg' : 'border-estado-advertencia/20 bg-estado-advertenciaBg' }}">
                            <i class="ph-fill {{ $puedeGuardar ? 'ph-check-fat text-estado-exito' : 'ph-hourglass-medium text-estado-advertencia' }} text-lg"></i>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider {{ $puedeGuardar ? 'text-estado-exito' : 'text-estado-advertencia' }}">
                                    Estado
                                </p>
                                <p class="text-[11px] font-bold text-titulo">
                                    {{ $puedeGuardar ? 'Listo para guardar' : 'Revise datos pendientes' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Errores globales --}}
                    @error('general')
                    <div
                        class="flex items-start gap-3 rounded-2xl border border-estado-error/30 bg-estado-errorBg p-4 text-estado-error"
                        x-transition:enter="transition duration-300"
                        x-transition:enter-start="-translate-y-2 opacity-0"
                        x-transition:enter-end="translate-y-0 opacity-100"
                    >
                        <i class="ph-fill ph-warning-octagon mt-0.5 text-xl"></i>
                        <div>
                            <p class="text-sm font-black">No se pudo completar el registro</p>
                            <p class="mt-1 text-xs font-semibold leading-relaxed">{{ $message }}</p>
                        </div>
                    </div>
                    @enderror

                    @if($errors->any() && !$errors->has('general'))
                    <div class="flex items-start gap-3 rounded-2xl border border-estado-advertencia/25 bg-estado-advertenciaBg p-4">
                        <i class="ph-fill ph-warning-circle mt-0.5 text-xl text-estado-advertencia"></i>
                        <div>
                            <p class="text-sm font-black text-titulo">Revise los campos marcados</p>
                            <p class="mt-1 text-xs font-semibold text-apoyo">
                                Las validaciones se actualizan mientras completa el formulario.
                            </p>
                        </div>
                    </div>
                    @endif

                    {{-- Fecha / hora / posición --}}
                    <section class="rounded-2xl border border-borde bg-fondo-panel/50 p-4 sm:p-5">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                                <i class="ph-bold ph-clock-clockwise text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-titulo">Contexto de la medición</h3>
                                <p class="text-[11px] font-semibold text-apoyo">Fecha, hora y posición en que se realizó el control.</p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Fecha <span class="text-estado-error">*</span>
                                </label>
                                <input
                                    type="date"
                                    wire:model.live="fecha"
                                    max="{{ today()->toDateString() }}"
                                    aria-invalid="{{ $errors->has('fecha') ? 'true' : 'false' }}"
                                    class="w-full rounded-xl border bg-fondo-card px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition duration-200 focus:ring-2
                                        @error('fecha')
                                            border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                        @else
                                            border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                        @enderror"
                                >
                                @error('fecha')
                                <p class="mt-1.5 flex items-center gap-1 text-[10px] font-bold text-estado-error">
                                    <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                </p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Hora <span class="text-estado-error">*</span>
                                </label>
                                <input
                                    type="time"
                                    wire:model.live="hora"
                                    aria-invalid="{{ $errors->has('hora') ? 'true' : 'false' }}"
                                    class="w-full rounded-xl border bg-fondo-card px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition duration-200 focus:ring-2
                                        @error('hora')
                                            border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                        @else
                                            border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                        @enderror"
                                >
                                @error('hora')
                                <p class="mt-1.5 flex items-center gap-1 text-[10px] font-bold text-estado-error">
                                    <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                </p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Posición
                                </label>
                                <select
                                    wire:model.live="posicion"
                                    class="w-full rounded-xl border bg-fondo-card px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition duration-200 focus:ring-2
                                        @error('posicion')
                                            border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                        @else
                                            border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                        @enderror"
                                >
                                    <option value="">No registrada</option>
                                    <option value="SENTADO">Sentado</option>
                                    <option value="ACOSTADO">Acostado</option>
                                    <option value="DE_PIE">De pie</option>
                                </select>
                                @error('posicion')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- Cardiovascular --}}
                    <section class="overflow-hidden rounded-2xl border border-estado-error/15 bg-fondo-card">
                        <div class="flex items-center justify-between gap-3 border-b border-estado-error/10 bg-estado-errorBg/40 px-4 py-3 sm:px-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-errorBg text-estado-error">
                                    <i class="ph-fill ph-heart text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-titulo">Cardiovascular</h3>
                                    <p class="text-[10px] font-semibold text-apoyo">Presión arterial y frecuencia cardíaca.</p>
                                </div>
                            </div>

                            <span class="rounded-full border border-borde bg-fondo-card px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-apoyo">
                                PA en mmHg
                            </span>
                        </div>

                        <div class="grid gap-4 p-4 sm:grid-cols-3 sm:p-5">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-black text-apoyo">Sistólica</label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        wire:model.live.debounce.400ms="pa_sistolica"
                                        min="{{ VSV::PAS_MIN }}"
                                        max="{{ VSV::PAS_MAX }}"
                                        inputmode="numeric"
                                        placeholder="Ej. 120"
                                        aria-invalid="{{ $errors->has('pa_sistolica') ? 'true' : 'false' }}"
                                        class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 pr-14 text-sm font-black text-titulo outline-none transition duration-200 focus:ring-2
                                            @error('pa_sistolica')
                                                border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                            @else
                                                border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                            @enderror"
                                    >
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-apoyo">mmHg</span>
                                </div>
                                @error('pa_sistolica')
                                <p class="mt-1.5 text-[10px] font-bold leading-snug text-estado-error">{{ $message }}</p>
                                @else
                                    @if($pa_sistolica !== null && $pa_sistolica !== '')
                                    <p class="mt-1.5 flex items-center gap-1 text-[10px] font-bold text-estado-exito">
                                        <i class="ph-bold ph-check-circle"></i>Valor aceptado
                                    </p>
                                    @endif
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-black text-apoyo">Diastólica</label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        wire:model.live.debounce.400ms="pa_diastolica"
                                        min="{{ VSV::PAD_MIN }}"
                                        max="{{ VSV::PAD_MAX }}"
                                        inputmode="numeric"
                                        placeholder="Ej. 80"
                                        aria-invalid="{{ $errors->has('pa_diastolica') ? 'true' : 'false' }}"
                                        class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 pr-14 text-sm font-black text-titulo outline-none transition duration-200 focus:ring-2
                                            @error('pa_diastolica')
                                                border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                            @else
                                                border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                            @enderror"
                                    >
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-apoyo">mmHg</span>
                                </div>
                                @error('pa_diastolica')
                                <p class="mt-1.5 text-[10px] font-bold leading-snug text-estado-error">{{ $message }}</p>
                                @else
                                    @if($pa_diastolica !== null && $pa_diastolica !== '')
                                    <p class="mt-1.5 flex items-center gap-1 text-[10px] font-bold text-estado-exito">
                                        <i class="ph-bold ph-check-circle"></i>Valor aceptado
                                    </p>
                                    @endif
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-black text-apoyo">Frecuencia cardíaca</label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        wire:model.live.debounce.400ms="fc"
                                        min="{{ VSV::FC_MIN }}"
                                        max="{{ VSV::FC_MAX }}"
                                        inputmode="numeric"
                                        placeholder="Ej. 72"
                                        aria-invalid="{{ $errors->has('fc') ? 'true' : 'false' }}"
                                        class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 pr-12 text-sm font-black text-titulo outline-none transition duration-200 focus:ring-2
                                            @error('fc')
                                                border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                            @else
                                                border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                            @enderror"
                                    >
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-apoyo">bpm</span>
                                </div>
                                @error('fc')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Integridad de PA --}}
                        <div
                            x-show="$wire.presion_incompleta"
                            x-cloak
                            x-transition:enter="transition duration-300 ease-out"
                            x-transition:enter-start="-translate-y-2 opacity-0"
                            x-transition:enter-end="translate-y-0 opacity-100"
                            class="mx-4 mb-4 flex items-start gap-3 rounded-xl border border-estado-advertencia/25 bg-estado-advertenciaBg p-3 sm:mx-5 sm:mb-5"
                        >
                            <i class="ph-fill ph-info text-lg text-estado-advertencia"></i>
                            <div>
                                <p class="text-xs font-black text-titulo">Complete ambos valores de presión</p>
                                <p class="mt-0.5 text-[10px] font-semibold text-apoyo">
                                    La presión arterial requiere sistólica y diastólica para considerarse una medición válida.
                                </p>
                            </div>
                        </div>

                        <div
                            x-show="$wire.presion_atipica"
                            x-cloak
                            x-transition:enter="transition duration-300 ease-out"
                            x-transition:enter-start="scale-[0.96] opacity-0"
                            x-transition:enter-end="scale-100 opacity-100"
                            class="mx-4 mb-4 rounded-2xl border border-estado-error/25 bg-estado-errorBg p-4 sm:mx-5 sm:mb-5"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-fondo-card text-estado-error shadow-sm">
                                    <i class="ph-fill ph-warning-octagon animate-pulse text-xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-black text-titulo">Verifique la presión arterial</p>
                                    <p class="mt-1 text-xs font-semibold leading-relaxed text-apoyo">
                                        La sistólica es menor o igual a la diastólica. Repita la medición y confirme el dato solamente si el valor obtenido es correcto.
                                    </p>

                                    <label class="mt-3 flex cursor-pointer items-start gap-3 rounded-xl border border-estado-error/20 bg-fondo-card p-3 transition hover:border-estado-error/40">
                                        <input
                                            type="checkbox"
                                            wire:model.live="confirmar_presion_atipica"
                                            class="mt-0.5 rounded border-borde text-boton-acento focus:ring-boton-acento"
                                        >
                                        <span class="text-xs font-bold text-titulo">
                                            Confirmo que repetí la medición y que estos valores corresponden al control realizado.
                                        </span>
                                    </label>

                                    @error('confirmar_presion_atipica')
                                    <p class="mt-2 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Respiratorio --}}
                    <section class="overflow-hidden rounded-2xl border border-estado-info/15 bg-fondo-card">
                        <div class="flex items-center justify-between gap-3 border-b border-estado-info/10 bg-estado-infoBg/40 px-4 py-3 sm:px-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                                    <i class="ph-fill ph-wind text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-titulo">Respiratorio</h3>
                                    <p class="text-[10px] font-semibold text-apoyo">Frecuencia respiratoria, saturación y soporte de oxígeno.</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 p-4 sm:grid-cols-3 sm:p-5">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-black text-apoyo">Frecuencia respiratoria</label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        wire:model.live.debounce.400ms="fr"
                                        min="{{ VSV::FR_MIN }}"
                                        max="{{ VSV::FR_MAX }}"
                                        placeholder="Ej. 16"
                                        class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 pr-12 text-sm font-black text-titulo outline-none transition duration-200 focus:ring-2
                                            @error('fr')
                                                border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                            @else
                                                border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                            @enderror"
                                    >
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-apoyo">rpm</span>
                                </div>
                                @error('fr')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-black text-apoyo">Saturación O₂</label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        wire:model.live.debounce.400ms="saturacion"
                                        min="{{ VSV::SPO2_MIN }}"
                                        max="{{ VSV::SPO2_MAX }}"
                                        placeholder="Ej. 96"
                                        class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 pr-10 text-sm font-black text-titulo outline-none transition duration-200 focus:ring-2
                                            @error('saturacion')
                                                border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                            @else
                                                border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                            @enderror"
                                    >
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-apoyo">%</span>
                                </div>
                                @error('saturacion')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-black text-apoyo">Oxígeno suplementario</label>
                                <button
                                    type="button"
                                    wire:click="$toggle('usa_oxigeno')"
                                    class="flex w-full items-center justify-between rounded-xl border px-3.5 py-2.5 text-left transition duration-200
                                        {{ $usa_oxigeno
                                            ? 'border-estado-info/35 bg-estado-infoBg text-estado-info'
                                            : 'border-borde bg-fondo-panel text-apoyo' }}"
                                >
                                    <span class="flex items-center gap-2 text-xs font-black">
                                        <i class="ph-fill ph-first-aid-kit"></i>
                                        {{ $usa_oxigeno ? 'Sí utiliza' : 'No utiliza' }}
                                    </span>
                                    <span class="relative h-5 w-9 rounded-full transition {{ $usa_oxigeno ? 'bg-estado-info' : 'bg-borde' }}">
                                        <span class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow transition-all {{ $usa_oxigeno ? 'left-[18px]' : 'left-0.5' }}"></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </section>

                    {{-- Temperatura y glucosa --}}
                    <section class="overflow-hidden rounded-2xl border border-estado-advertencia/15 bg-fondo-card">
                        <div class="flex items-center gap-3 border-b border-estado-advertencia/10 bg-estado-advertenciaBg/40 px-4 py-3 sm:px-5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia">
                                <i class="ph-fill ph-thermometer-hot text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-titulo">Temperatura y glucosa</h3>
                                <p class="text-[10px] font-semibold text-apoyo">Valores obtenidos durante este control.</p>
                            </div>
                        </div>

                        <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-black text-apoyo">Temperatura</label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        step="0.1"
                                        wire:model.live.debounce.400ms="temperatura"
                                        min="{{ VSV::TEMP_MIN }}"
                                        max="{{ VSV::TEMP_MAX }}"
                                        placeholder="Ej. 36.5"
                                        class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 pr-10 text-sm font-black text-titulo outline-none transition duration-200 focus:ring-2
                                            @error('temperatura')
                                                border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                            @else
                                                border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                            @enderror"
                                    >
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-apoyo">°C</span>
                                </div>
                                @error('temperatura')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-black text-apoyo">Glucosa</label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        step="0.1"
                                        wire:model.live.debounce.400ms="glucosa"
                                        min="{{ VSV::GLUCOSA_MIN }}"
                                        placeholder="Ej. 100"
                                        class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 pr-16 text-sm font-black text-titulo outline-none transition duration-200 focus:ring-2
                                            @error('glucosa')
                                                border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                            @else
                                                border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                            @enderror"
                                    >
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-apoyo">mg/dL</span>
                                </div>
                                @error('glucosa')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- Antropometría --}}
                    <section class="overflow-hidden rounded-2xl border border-estado-exito/15 bg-fondo-card">
                        <div class="flex items-center gap-3 border-b border-estado-exito/10 bg-estado-exitoBg/40 px-4 py-3 sm:px-5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                                <i class="ph-fill ph-ruler text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-titulo">Antropometría</h3>
                                <p class="text-[10px] font-semibold text-apoyo">
                                    El peso debe corresponder a una medición actual; la talla puede usar la referencia previa.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 p-4 sm:grid-cols-3 sm:p-5">
                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                    <label class="text-[11px] font-black text-apoyo">Peso</label>
                                    @if($ultimo_peso_conocido)
                                    <span class="text-[9px] font-bold text-apoyo" title="Referencia, no se copia automáticamente">
                                        Anterior: {{ number_format((float) $ultimo_peso_conocido, 1) }} kg
                                    </span>
                                    @endif
                                </div>
                                <div class="relative">
                                    <input
                                        type="number"
                                        step="0.1"
                                        wire:model.live.debounce.400ms="peso"
                                        min="{{ VSV::PESO_MIN }}"
                                        max="{{ VSV::PESO_MAX }}"
                                        placeholder="Medición actual"
                                        class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 pr-10 text-sm font-black text-titulo outline-none transition duration-200 focus:ring-2
                                            @error('peso')
                                                border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                            @else
                                                border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                            @enderror"
                                    >
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-apoyo">kg</span>
                                </div>
                                @error('peso')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                                @if($fecha_ultimo_peso)
                                <p class="mt-1 text-[9px] font-semibold text-apoyo">Última referencia: {{ \Carbon\Carbon::parse($fecha_ultimo_peso)->format('d/m/Y') }}</p>
                                @endif
                            </div>

                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                    <label class="text-[11px] font-black text-apoyo">Talla</label>
                                    @if($ultima_talla_conocida)
                                    <span class="text-[9px] font-bold text-estado-exito">Referencia cargada</span>
                                    @endif
                                </div>
                                <div class="relative">
                                    <input
                                        type="number"
                                        step="0.1"
                                        wire:model.live.debounce.400ms="talla"
                                        min="0.5"
                                        max="{{ VSV::TALLA_CM_MAX }}"
                                        placeholder="cm o m"
                                        class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 pr-12 text-sm font-black text-titulo outline-none transition duration-200 focus:ring-2
                                            @error('talla')
                                                border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                            @else
                                                border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                            @enderror"
                                    >
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-apoyo">cm/m</span>
                                </div>
                                @error('talla')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                                @if($fecha_ultima_talla)
                                <p class="mt-1 text-[9px] font-semibold text-apoyo">Referencia: {{ \Carbon\Carbon::parse($fecha_ultima_talla)->format('d/m/Y') }}</p>
                                @endif
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-black text-apoyo">IMC calculado</label>
                                <div class="flex min-h-[42px] items-center justify-between rounded-xl border border-borde bg-fondo-panel px-3.5 py-2.5">
                                    <div>
                                        <p class="text-lg font-black text-titulo">
                                            {{ $imc !== null ? number_format((float) $imc, 1) : '—' }}
                                        </p>
                                        <p class="text-[9px] font-semibold text-apoyo">
                                            {{ $imc !== null ? 'Calculado en servidor' : 'Requiere peso y talla válidos' }}
                                        </p>
                                    </div>
                                    <i class="ph-bold ph-calculator text-xl text-estado-exito"></i>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Dolor --}}
                    <section class="rounded-2xl border border-boton-acento/15 bg-fondo-card p-4 sm:p-5">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                                <i class="ph-fill ph-wave-sine text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-titulo">Dolor</h3>
                                <p class="text-[10px] font-semibold text-apoyo">Escala numérica de 0 a 10.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-6 gap-2 sm:grid-cols-11">
                            @for($i = 0; $i <= 10; $i++)
                            <button
                                type="button"
                                wire:click="$set('dolor', '{{ $i }}')"
                                class="flex h-10 items-center justify-center rounded-xl border text-xs font-black transition duration-200 hover:-translate-y-0.5 active:scale-95
                                    {{ (string) $dolor === (string) $i
                                        ? 'border-boton-acento bg-boton-acento text-white shadow-md'
                                        : 'border-borde bg-fondo-panel text-apoyo hover:border-boton-acento/40 hover:text-titulo' }}"
                                aria-label="Dolor {{ $i }} de 10"
                            >
                                {{ $i }}
                            </button>
                            @endfor
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3">
                            <p class="text-[10px] font-semibold text-apoyo">0 = ausencia de dolor · 10 = máxima intensidad referida.</p>
                            @if($dolor !== null && $dolor !== '')
                            <button
                                type="button"
                                wire:click="$set('dolor', null)"
                                class="text-[10px] font-black text-boton-acento hover:underline"
                            >
                                Limpiar
                            </button>
                            @endif
                        </div>

                        @error('dolor')
                        <p class="mt-2 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                        @enderror
                    </section>

                    {{-- Observaciones --}}
                    <section class="rounded-2xl border border-borde bg-fondo-panel/50 p-4 sm:p-5">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-black text-titulo">Observación clínica</h3>
                                <p class="text-[10px] font-semibold text-apoyo">Contexto relevante de la medición, si corresponde.</p>
                            </div>
                            <i class="ph-bold ph-note-pencil text-xl text-apoyo"></i>
                        </div>

                        <textarea
                            wire:model.live.debounce.600ms="observacion"
                            rows="3"
                            maxlength="5000"
                            placeholder="Ej.: medición realizada después de reposo, dificultad técnica, condición observada..."
                            class="w-full resize-none rounded-xl border bg-fondo-card px-3.5 py-3 text-sm font-semibold leading-relaxed text-titulo outline-none transition duration-200 focus:ring-2
                                @error('observacion')
                                    border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                @else
                                    border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                @enderror"
                        ></textarea>

                        <div class="mt-1.5 flex items-start justify-between gap-3">
                            <div>
                                @error('observacion')
                                <p class="text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <span class="text-[9px] font-semibold text-apoyo">Máx. 5000 caracteres</span>
                        </div>
                    </section>

                    {{-- Nota de seguridad --}}
                    <div class="flex items-start gap-3 rounded-2xl border border-estado-info/20 bg-estado-infoBg p-4">
                        <i class="ph-fill ph-info mt-0.5 text-lg text-estado-info"></i>
                        <div>
                            <p class="text-xs font-black text-titulo">Validación técnica de captura</p>
                            <p class="mt-1 text-[10px] font-semibold leading-relaxed text-apoyo">
                                Los límites del formulario controlan consistencia y formato de registro. La interpretación clínica y las alertas institucionales se gestionan en sus módulos correspondientes.
                            </p>
                        </div>
                    </div>
                </div>
            </fieldset>

            {{-- Footer --}}
            <footer class="shrink-0 border-t border-borde bg-fondo-card px-5 py-4 sm:px-7">
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2 text-[10px] font-semibold text-apoyo">
                        <i class="ph-bold ph-shield-check text-boton-acento"></i>
                        Permisos, residente y estado del expediente se revalidan al guardar.
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            wire:click="cerrar"
                            wire:loading.attr="disabled"
                            wire:target="guardar"
                            class="rm-btn-secondary h-11 px-5 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            @disabled(!$puedeGuardar)
                            wire:loading.attr="disabled"
                            wire:target="guardar"
                            class="rm-btn-primary flex h-11 min-w-[190px] items-center justify-center gap-2 px-6 transition duration-200
                                disabled:cursor-not-allowed disabled:opacity-45 disabled:shadow-none"
                        >
                            <span wire:loading.remove wire:target="guardar" class="flex items-center gap-2">
                                <i class="ph-bold {{ $puedeGuardar ? 'ph-check-circle' : 'ph-lock-key' }} text-base"></i>
                                {{ $puedeGuardar ? 'Registrar control' : 'Complete los datos' }}
                            </span>

                            <span wire:loading.flex wire:target="guardar" class="items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
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
