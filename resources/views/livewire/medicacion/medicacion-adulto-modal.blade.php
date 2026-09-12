<div>
@if($showModal)
@php
    $nombreCompleto = $adultoSeleccionado?->nombre_completo
        ?? trim(
            ($adultoSeleccionado?->nombres ?? '') . ' ' .
            ($adultoSeleccionado?->ap_paterno ?? '') . ' ' .
            ($adultoSeleccionado?->ap_materno ?? '')
        );

    $edadTexto = $adultoSeleccionado?->edad_texto ?? null;
    $estadoHumano = $adultoSeleccionado?->estado_humano ?? null;

    $estadoColor = match ($estado) {
        'ACTIVO' => 'bg-estado-exitoBg text-estado-exito border-estado-exito/20',
        'PAUSADO' => 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertencia/20',
        'EN REVISION' => 'bg-estado-infoBg text-estado-info border-estado-info/20',
        'SUSPENDIDO', 'FINALIZADO' => 'bg-estado-errorBg text-estado-error border-estado-error/20',
        default => 'bg-fondo-panel text-apoyo border-borde',
    };

    $vias = [
        'ORAL' => ['Oral', 'ph-pill'],
        'SUBLINGUAL' => ['Sublingual', 'ph-mouth'],
        'TOPICA' => ['Tópica', 'ph-hand-soap'],
        'OFTALMICA' => ['Oftálmica', 'ph-eye'],
        'OTICA' => ['Ótica', 'ph-ear'],
        'NASAL' => ['Nasal', 'ph-nose'],
        'INHALATORIA' => ['Inhalatoria', 'ph-wind'],
        'INTRAVENOSA' => ['Intravenosa', 'ph-drop'],
        'INTRAMUSCULAR' => ['Intramuscular', 'ph-syringe'],
        'SUBCUTANEA' => ['Subcutánea', 'ph-syringe'],
        'RECTAL' => ['Rectal', 'ph-first-aid'],
        'OTRA' => ['Otra', 'ph-dots-three-circle'],
    ];

    $camposClaveCompletos = collect([
        trim($nombre_medicamento),
        trim($dosis),
        trim($frecuencia),
        trim($via_administracion),
        trim($fecha_inicio),
    ])->filter(fn ($valor) => $valor !== '')->count();

    $totalCamposClave = 5;
@endphp

<div
    class="fixed inset-0 z-[2147483646] flex items-center justify-center overflow-y-auto bg-slate-950/60 p-3 backdrop-blur-md sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="medicacion-modal-title"
>
    <div
        class="relative flex max-h-[95vh] w-full max-w-6xl flex-col overflow-hidden rounded-[30px] border border-borde bg-fondo-card shadow-2xl"
        x-data
        x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="translate-y-8 scale-[0.95] opacity-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
        x-transition:leave="transition duration-200 ease-in"
        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
        x-transition:leave-end="translate-y-5 scale-[0.98] opacity-0"
    >
        {{-- Barra superior de carga --}}
        <div
            wire:loading.flex
            wire:target="guardar"
            class="absolute inset-x-0 top-0 z-50 h-1 overflow-hidden bg-boton-principal/15"
        >
            <div class="h-full w-1/3 animate-pulse rounded-full bg-boton-principal"></div>
        </div>

        {{-- CABECERA --}}
        <header class="shrink-0 border-b border-borde bg-fondo-card px-5 py-4 sm:px-7">
            <div class="flex items-start justify-between gap-4">
                <div class="flex min-w-0 items-start gap-4">
                    <div class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-boton-principal/10 text-boton-principal shadow-sm">
                        <i class="ph-fill ph-prescription text-2xl"></i>
                        <span class="absolute -right-1 -top-1 h-3 w-3 animate-pulse rounded-full border-2 border-fondo-card bg-estado-exito"></span>
                    </div>

                    <div class="min-w-0">
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-black uppercase tracking-[0.18em] text-boton-principal">
                                Orden médica
                            </span>

                            <span class="rounded-full bg-estado-exitoBg px-2 py-0.5 text-[10px] font-black text-estado-exito">
                                Acceso validado
                            </span>

                            <span class="rounded-full border px-2 py-0.5 text-[10px] font-black {{ $es_prn
                                ? 'border-estado-advertencia/20 bg-estado-advertenciaBg text-estado-advertencia'
                                : 'border-estado-info/20 bg-estado-infoBg text-estado-info' }}">
                                {{ $this->tipoOrden }}
                            </span>
                        </div>

                        <h2 id="medicacion-modal-title" class="text-xl font-black leading-tight text-titulo sm:text-2xl">
                            {{ $isEditing ? 'Editar prescripción' : 'Nueva prescripción' }}
                        </h2>

                        <p class="mt-1 text-xs font-semibold text-apoyo">
                            Complete medicamento, dosis, vía, pauta y duración antes de guardar.
                        </p>
                    </div>
                </div>

                @if($this->tieneCambios)
                <button
                    type="button"
                    x-on:click="if (confirm('Hay cambios sin guardar. ¿Desea cerrar y descartarlos?')) { $wire.cerrarModal() }"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-borde bg-fondo-panel text-apoyo transition duration-200 hover:rotate-90 hover:border-estado-error/40 hover:bg-estado-errorBg hover:text-estado-error active:scale-90"
                    aria-label="Cerrar y descartar cambios"
                >
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
                @else
                <button
                    type="button"
                    wire:click="cerrarModal"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-borde bg-fondo-panel text-apoyo transition duration-200 hover:rotate-90 hover:border-estado-error/40 hover:bg-estado-errorBg hover:text-estado-error active:scale-90"
                    aria-label="Cerrar"
                >
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
                @endif
            </div>

            {{-- Residente fijado --}}
            @if($pacienteFijado && $adultoSeleccionado)
            <div class="mt-4 grid gap-3 rounded-2xl border border-borde bg-fondo-panel/70 p-3 sm:grid-cols-[1fr_auto] sm:items-center sm:p-4">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-boton-principal/10 text-sm font-black text-boton-principal">
                        {{ mb_strtoupper(
                            mb_substr($adultoSeleccionado->nombres ?? 'R', 0, 1) .
                            mb_substr($adultoSeleccionado->ap_paterno ?? '', 0, 1)
                        ) }}
                    </div>

                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-titulo">
                            {{ $nombreCompleto ?: 'Residente' }}
                        </p>

                        <div class="mt-0.5 flex flex-wrap gap-x-3 gap-y-1 text-[11px] font-semibold text-apoyo">
                            @if($adultoSeleccionado->ci)
                                <span>CI {{ $adultoSeleccionado->ci }}</span>
                            @endif
                            @if($edadTexto)
                                <span>{{ $edadTexto }}</span>
                            @endif
                            @if($estadoHumano)
                                <span>{{ $estadoHumano }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 rounded-xl border border-borde bg-fondo-card px-3 py-2 text-[11px] font-bold text-apoyo">
                    <i class="ph-bold ph-lock-key text-boton-principal"></i>
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

                    {{-- ESTADO GENERAL --}}
                    <div class="grid gap-2 sm:grid-cols-3">
                        <div class="flex items-center gap-2 rounded-xl border border-estado-exito/20 bg-estado-exitoBg px-3 py-2.5">
                            <i class="ph-fill ph-shield-check text-lg text-estado-exito"></i>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider text-estado-exito">Acceso</p>
                                <p class="text-[11px] font-bold text-titulo">Médico autorizado</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 rounded-xl border border-borde bg-fondo-panel px-3 py-2.5">
                            <i class="ph-fill {{ $es_prn ? 'ph-first-aid-kit text-estado-advertencia' : 'ph-clock text-estado-info' }} text-lg"></i>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider text-apoyo">Modalidad</p>
                                <p class="text-[11px] font-bold text-titulo">{{ $this->tipoOrden }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 rounded-xl border px-3 py-2.5 transition
                            {{ $this->puedeGuardar
                                ? 'border-estado-exito/20 bg-estado-exitoBg'
                                : 'border-estado-advertencia/20 bg-estado-advertenciaBg' }}">
                            <i class="ph-fill {{ $this->puedeGuardar ? 'ph-check-fat text-estado-exito' : 'ph-hourglass-medium text-estado-advertencia' }} text-lg"></i>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider {{ $this->puedeGuardar ? 'text-estado-exito' : 'text-estado-advertencia' }}">
                                    Estado
                                </p>
                                <p class="text-[11px] font-bold text-titulo">
                                    {{ $this->puedeGuardar ? 'Listo para guardar' : 'Complete datos pendientes' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ERRORES GENERALES --}}
                    @error('general')
                    <div
                        class="flex items-start gap-3 rounded-2xl border border-estado-error/30 bg-estado-errorBg p-4"
                        x-transition:enter="transition duration-300"
                        x-transition:enter-start="-translate-y-2 opacity-0"
                        x-transition:enter-end="translate-y-0 opacity-100"
                    >
                        <i class="ph-fill ph-warning-octagon mt-0.5 text-xl text-estado-error"></i>
                        <div>
                            <p class="text-sm font-black text-titulo">No se pudo guardar la prescripción</p>
                            <p class="mt-1 text-xs font-semibold text-estado-error">{{ $message }}</p>
                        </div>
                    </div>
                    @enderror

                    @if($errors->any() && !$errors->has('general'))
                    <div class="flex items-start gap-3 rounded-2xl border border-estado-advertencia/25 bg-estado-advertenciaBg p-4">
                        <i class="ph-fill ph-warning-circle mt-0.5 text-xl text-estado-advertencia"></i>
                        <div>
                            <p class="text-sm font-black text-titulo">Revise los datos marcados</p>
                            <p class="mt-1 text-xs font-semibold text-apoyo">
                                Las validaciones se actualizan mientras completa la orden.
                            </p>
                        </div>
                    </div>
                    @endif

                    {{-- RESIDENTE SELECTOR solo si no viene fijado --}}
                    @if(!$pacienteFijado)
                    <section class="rounded-2xl border border-borde bg-fondo-panel/50 p-4 sm:p-5">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-principal/10 text-boton-principal">
                                <i class="ph-fill ph-user-circle text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-titulo">
                                    Residente <span class="text-estado-error">*</span>
                                </h3>
                                <p class="text-[10px] font-semibold text-apoyo">
                                    Seleccione a quién corresponde esta prescripción.
                                </p>
                            </div>
                        </div>

                        <select
                            wire:model.live="cod_am"
                            aria-invalid="{{ $errors->has('cod_am') ? 'true' : 'false' }}"
                            class="w-full rounded-xl border bg-fondo-card px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:ring-2
                                @error('cod_am')
                                    border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                @else
                                    border-borde focus:border-borde-focus focus:ring-boton-principal/10
                                @enderror"
                        >
                            <option value="">Seleccione un residente...</option>

                            @foreach($adultosDisponibles as $ad)
                            <option value="{{ $ad->cod_am }}">
                                {{ trim($ad->nombres . ' ' . $ad->ap_paterno . ' ' . ($ad->ap_materno ?? '')) }}
                                @if($ad->ci) · CI {{ $ad->ci }} @endif
                            </option>
                            @endforeach
                        </select>

                        @error('cod_am')
                        <p class="mt-1.5 flex items-center gap-1 text-[10px] font-bold text-estado-error">
                            <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                        </p>
                        @enderror
                    </section>
                    @endif

                    {{-- Contexto de alergias: visible antes de prescribir --}}
                    @if($adultoSeleccionado && filled($adultoSeleccionado->alergias))
                    <div
                        class="flex items-start gap-3 rounded-2xl border border-estado-error/25 bg-estado-errorBg p-4"
                        x-transition:enter="transition duration-300 ease-out"
                        x-transition:enter-start="-translate-y-2 opacity-0"
                        x-transition:enter-end="translate-y-0 opacity-100"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-fondo-card text-estado-error shadow-sm">
                            <i class="ph-fill ph-warning-octagon animate-pulse text-xl"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-black text-titulo">Alergias registradas</p>
                            <p class="mt-1 break-words text-xs font-semibold leading-relaxed text-estado-error">
                                {{ $adultoSeleccionado->alergias }}
                            </p>
                            <p class="mt-2 text-[10px] font-semibold leading-relaxed text-apoyo">
                                Revise este antecedente antes de indicar el medicamento. El sistema no sustituye la verificación clínica de alergias e interacciones.
                            </p>
                        </div>
                    </div>
                    @endif

                    {{-- 1. TIPO DE ORDEN --}}
                    <section class="rounded-2xl border border-borde bg-fondo-card p-4 sm:p-5">
                        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-boton-principal/10 text-[11px] font-black text-boton-principal">1</span>
                                    <h3 class="text-sm font-black text-titulo">Modalidad de administración</h3>
                                </div>
                                <p class="mt-1 pl-9 text-[10px] font-semibold text-apoyo">
                                    Defina primero si la orden tiene horario fijo o se administra PRN.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <button
                                type="button"
                                wire:click="$set('es_prn', false)"
                                aria-pressed="{{ !$es_prn ? 'true' : 'false' }}"
                                class="group rounded-2xl border p-4 text-left transition duration-200 hover:-translate-y-0.5 active:scale-[0.98]
                                    {{ !$es_prn
                                        ? 'border-estado-info bg-estado-infoBg shadow-sm'
                                        : 'border-borde bg-fondo-panel hover:border-estado-info/30' }}"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                                            {{ !$es_prn ? 'bg-estado-info text-white' : 'bg-fondo-card text-apoyo' }}">
                                            <i class="ph-fill ph-clock text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black {{ !$es_prn ? 'text-estado-info' : 'text-titulo' }}">
                                                Programada
                                            </p>
                                            <p class="mt-1 text-[10px] font-semibold leading-relaxed text-apoyo">
                                                Tiene una hora programada de administración.
                                            </p>
                                        </div>
                                    </div>
                                    <i class="ph-fill {{ !$es_prn ? 'ph-check-circle text-estado-info' : 'ph-circle text-apoyo' }} text-lg"></i>
                                </div>
                            </button>

                            <button
                                type="button"
                                wire:click="$set('es_prn', true)"
                                aria-pressed="{{ $es_prn ? 'true' : 'false' }}"
                                class="group rounded-2xl border p-4 text-left transition duration-200 hover:-translate-y-0.5 active:scale-[0.98]
                                    {{ $es_prn
                                        ? 'border-estado-advertencia bg-estado-advertenciaBg shadow-sm'
                                        : 'border-borde bg-fondo-panel hover:border-estado-advertencia/30' }}"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                                            {{ $es_prn ? 'bg-estado-advertencia text-white' : 'bg-fondo-card text-apoyo' }}">
                                            <i class="ph-fill ph-first-aid-kit text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black {{ $es_prn ? 'text-estado-advertencia' : 'text-titulo' }}">
                                                PRN / según necesidad
                                            </p>
                                            <p class="mt-1 text-[10px] font-semibold leading-relaxed text-apoyo">
                                                Solo se administra cuando se cumple una condición clínica explícita.
                                            </p>
                                        </div>
                                    </div>
                                    <i class="ph-fill {{ $es_prn ? 'ph-check-circle text-estado-advertencia' : 'ph-circle text-apoyo' }} text-lg"></i>
                                </div>
                            </button>
                        </div>
                    </section>

                    {{-- 2. MEDICAMENTO --}}
                    <section class="overflow-hidden rounded-2xl border border-boton-principal/15 bg-fondo-card">
                        <div class="flex items-center gap-3 border-b border-boton-principal/10 bg-boton-principal/5 px-4 py-3 sm:px-5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-principal/10 text-boton-principal">
                                <i class="ph-fill ph-pill text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-titulo">2. Medicamento y dosis</h3>
                                <p class="text-[10px] font-semibold text-apoyo">
                                    Registre el principio activo o nombre del medicamento y la dosis indicada.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-3">
                            <div class="sm:col-span-2 lg:col-span-2">
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Medicamento / principio activo <span class="text-estado-error">*</span>
                                </label>

                                <div class="relative">
                                    <i class="ph-bold ph-pill absolute left-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.450ms="nombre_medicamento"
                                        maxlength="100"
                                        autocomplete="off"
                                        placeholder="Ej.: Enalapril"
                                        aria-invalid="{{ $errors->has('nombre_medicamento') ? 'true' : 'false' }}"
                                        class="w-full rounded-xl border bg-fondo-panel py-2.5 pl-10 pr-3.5 text-sm font-bold text-titulo outline-none transition focus:ring-2
                                            @error('nombre_medicamento')
                                                border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                            @else
                                                border-borde focus:border-borde-focus focus:ring-boton-principal/10
                                            @enderror"
                                    >
                                </div>

                                @error('nombre_medicamento')
                                <p class="mt-1.5 flex items-center gap-1 text-[10px] font-bold text-estado-error">
                                    <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                </p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Dosis <span class="text-estado-error">*</span>
                                </label>

                                <input
                                    type="text"
                                    wire:model.live.debounce.450ms="dosis"
                                    maxlength="50"
                                    placeholder="Ej.: 50 mg"
                                    aria-invalid="{{ $errors->has('dosis') ? 'true' : 'false' }}"
                                    class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:ring-2
                                        @error('dosis')
                                            border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                        @else
                                            border-borde focus:border-borde-focus focus:ring-boton-principal/10
                                        @enderror"
                                >

                                @error('dosis')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2 lg:col-span-3">
                                <label class="mb-2 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Vía de administración <span class="text-estado-error">*</span>
                                </label>

                                <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-4">
                                    @foreach($vias as $codigo => [$label, $icono])
                                    <button
                                        type="button"
                                        wire:click="$set('via_administracion', '{{ $codigo }}')"
                                        aria-pressed="{{ $via_administracion === $codigo ? 'true' : 'false' }}"
                                        class="flex items-center justify-between gap-2 rounded-xl border px-3 py-2.5 text-left transition duration-200 hover:-translate-y-0.5 active:scale-[0.98]
                                            {{ $via_administracion === $codigo
                                                ? 'border-boton-principal bg-boton-principal text-white shadow-sm'
                                                : 'border-borde bg-fondo-panel text-titulo hover:border-boton-principal/30' }}"
                                    >
                                        <span class="flex items-center gap-2 text-[10px] font-black">
                                            <i class="ph-bold {{ $icono }}"></i>{{ $label }}
                                        </span>

                                        <i class="ph-bold {{ $via_administracion === $codigo ? 'ph-check-circle' : 'ph-circle' }}"></i>
                                    </button>
                                    @endforeach
                                </div>

                                @error('via_administracion')
                                <p class="mt-2 flex items-center gap-1 text-[10px] font-bold text-estado-error">
                                    <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- DUPLICIDAD --}}
                    @if($posibleDuplicado)
                    <section
                        class="rounded-2xl border border-estado-advertencia/30 bg-estado-advertenciaBg p-4 sm:p-5"
                        x-transition:enter="transition duration-300 ease-out"
                        x-transition:enter-start="-translate-y-3 scale-[0.98] opacity-0"
                        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-fondo-card text-estado-advertencia shadow-sm">
                                <i class="ph-fill ph-warning-diamond animate-pulse text-xl"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-black text-titulo">Posible medicación duplicada</p>
                                <p class="mt-1 text-xs font-semibold leading-relaxed text-apoyo">
                                    Ya existe una orden activa, pausada o en revisión con el mismo nombre.
                                </p>

                                @if($medicacionDuplicadaResumen)
                                <div class="mt-3 rounded-xl border border-estado-advertencia/20 bg-fondo-card p-3 text-xs font-bold text-titulo">
                                    {{ $medicacionDuplicadaResumen }}
                                </div>
                                @endif

                                <label class="mt-3 flex cursor-pointer items-start gap-3 rounded-xl border border-estado-advertencia/20 bg-fondo-card p-3 transition hover:border-estado-advertencia/40">
                                    <input
                                        type="checkbox"
                                        wire:model.live="confirmar_duplicado"
                                        class="mt-0.5 rounded border-borde text-boton-principal focus:ring-boton-principal"
                                    >
                                    <span class="text-xs font-bold text-titulo">
                                        Revisé la orden existente y confirmo que esta prescripción adicional o modificación es intencional.
                                    </span>
                                </label>

                                @error('confirmar_duplicado')
                                <p class="mt-2 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>
                    @endif

                    {{-- 3. PAUTA --}}
                    <section class="overflow-hidden rounded-2xl border border-estado-info/15 bg-fondo-card">
                        <div class="flex items-center gap-3 border-b border-estado-info/10 bg-estado-infoBg/40 px-4 py-3 sm:px-5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                                <i class="ph-fill ph-clock-clockwise text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-titulo">3. Frecuencia y pauta</h3>
                                <p class="text-[10px] font-semibold text-apoyo">
                                    Describa una pauta clara que pueda ser ejecutada sin ambigüedad.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 p-4 sm:p-5 lg:grid-cols-3">
                            <div class="lg:col-span-2">
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Frecuencia <span class="text-estado-error">*</span>
                                </label>

                                <input
                                    type="text"
                                    wire:model.live.debounce.450ms="frecuencia"
                                    maxlength="100"
                                    placeholder="{{ $es_prn ? 'Ej.: Según necesidad ante dolor' : 'Ej.: Cada 12 horas' }}"
                                    aria-invalid="{{ $errors->has('frecuencia') ? 'true' : 'false' }}"
                                    class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:ring-2
                                        @error('frecuencia')
                                            border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                        @else
                                            border-borde focus:border-borde-focus focus:ring-boton-principal/10
                                        @enderror"
                                >

                                @error('frecuencia')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>

                            @if(!$es_prn)
                            <div
                                x-transition:enter="transition duration-300 ease-out"
                                x-transition:enter-start="translate-x-3 opacity-0"
                                x-transition:enter-end="translate-x-0 opacity-100"
                            >
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Hora programada <span class="text-estado-error">*</span>
                                </label>

                                <input
                                    type="time"
                                    wire:model.live="hora_programada"
                                    aria-invalid="{{ $errors->has('hora_programada') ? 'true' : 'false' }}"
                                    class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:ring-2
                                        @error('hora_programada')
                                            border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                        @else
                                            border-borde focus:border-borde-focus focus:ring-boton-principal/10
                                        @enderror"
                                >

                                @error('hora_programada')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>
                            @endif

                            @if($es_prn)
                            <div
                                class="lg:col-span-2"
                                x-transition:enter="transition duration-300 ease-out"
                                x-transition:enter-start="-translate-y-2 opacity-0"
                                x-transition:enter-end="translate-y-0 opacity-100"
                            >
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Condición clínica para administrar PRN <span class="text-estado-error">*</span>
                                </label>

                                <textarea
                                    wire:model.live.debounce.500ms="condicion_prn"
                                    rows="3"
                                    maxlength="500"
                                    placeholder="Ej.: Administrar si presenta dolor ≥ 6/10 luego de valoración clínica."
                                    aria-invalid="{{ $errors->has('condicion_prn') ? 'true' : 'false' }}"
                                    class="w-full resize-none rounded-xl border bg-fondo-panel px-3.5 py-3 text-sm font-semibold leading-relaxed text-titulo outline-none transition focus:ring-2
                                        @error('condicion_prn')
                                            border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                        @else
                                            border-borde focus:border-borde-focus focus:ring-boton-principal/10
                                        @enderror"
                                ></textarea>

                                <div class="mt-1 flex items-start justify-between gap-3">
                                    <div>
                                        @error('condicion_prn')
                                        <p class="text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <span class="text-[9px] font-semibold text-apoyo">{{ mb_strlen($condicion_prn) }}/500</span>
                                </div>
                            </div>
                            @endif

                            <div>
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Intervalo mínimo
                                    @if($es_prn)<span class="text-estado-error">*</span>@endif
                                </label>

                                <div class="relative">
                                    <input
                                        type="number"
                                        min="1"
                                        max="24"
                                        step="1"
                                        wire:model.live.debounce.350ms="intervalo_horas"
                                        placeholder="{{ $es_prn ? 'Ej.: 8' : 'Opcional' }}"
                                        aria-invalid="{{ $errors->has('intervalo_horas') ? 'true' : 'false' }}"
                                        class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 pr-12 text-sm font-bold text-titulo outline-none transition focus:ring-2
                                            @error('intervalo_horas')
                                                border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                            @else
                                                border-borde focus:border-borde-focus focus:ring-boton-principal/10
                                            @enderror"
                                    >
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-apoyo">
                                        horas
                                    </span>
                                </div>

                                @error('intervalo_horas')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @else
                                <p class="mt-1.5 text-[9px] font-semibold text-apoyo">
                                    {{ $es_prn ? 'Tiempo mínimo entre administraciones PRN.' : 'Opcional para complementar la frecuencia.' }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- 4. DURACIÓN Y RESPONSABLE --}}
                    <section class="overflow-hidden rounded-2xl border border-estado-exito/15 bg-fondo-card">
                        <div class="flex items-center gap-3 border-b border-estado-exito/10 bg-estado-exitoBg/40 px-4 py-3 sm:px-5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                                <i class="ph-fill ph-calendar-check text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-titulo">4. Vigencia de la orden</h3>
                                <p class="text-[10px] font-semibold text-apoyo">
                                    Defina el inicio y, si corresponde, la fecha de finalización prevista.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-3">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Fecha de inicio <span class="text-estado-error">*</span>
                                </label>

                                <input
                                    type="date"
                                    wire:model.live="fecha_inicio"
                                    aria-invalid="{{ $errors->has('fecha_inicio') ? 'true' : 'false' }}"
                                    class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:ring-2
                                        @error('fecha_inicio')
                                            border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                        @else
                                            border-borde focus:border-borde-focus focus:ring-boton-principal/10
                                        @enderror"
                                >

                                @error('fecha_inicio')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Fecha de fin
                                    <span class="font-semibold normal-case tracking-normal text-apoyo/70">(opcional)</span>
                                </label>

                                <input
                                    type="date"
                                    wire:model.live="fecha_fin"
                                    @if($fecha_inicio) min="{{ $fecha_inicio }}" @endif
                                    aria-invalid="{{ $errors->has('fecha_fin') ? 'true' : 'false' }}"
                                    class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:ring-2
                                        @error('fecha_fin')
                                            border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                        @else
                                            border-borde focus:border-borde-focus focus:ring-boton-principal/10
                                        @enderror"
                                >

                                @error('fecha_fin')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Médico prescriptor / referencia
                                </label>

                                <input
                                    type="text"
                                    wire:model.live.debounce.500ms="medico_indica"
                                    maxlength="100"
                                    placeholder="Ej.: Dra. Ana Pérez"
                                    aria-invalid="{{ $errors->has('medico_indica') ? 'true' : 'false' }}"
                                    class="w-full rounded-xl border bg-fondo-panel px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:ring-2
                                        @error('medico_indica')
                                            border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                        @else
                                            border-borde focus:border-borde-focus focus:ring-boton-principal/10
                                        @enderror"
                                >

                                @error('medico_indica')
                                <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @else
                                <p class="mt-1.5 text-[9px] font-semibold text-apoyo">
                                    El usuario autenticado queda registrado automáticamente en la auditoría.
                                </p>
                                @enderror
                            </div>

                            @if($isEditing)
                            <div class="sm:col-span-2 lg:col-span-3">
                                <label class="mb-2 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Estado del tratamiento
                                </label>

                                <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-5">
                                    @foreach([
                                        ['ACTIVO', 'ph-play-circle', 'Activo'],
                                        ['PAUSADO', 'ph-pause-circle', 'Pausado'],
                                        ['EN REVISION', 'ph-magnifying-glass', 'En revisión'],
                                    ] as [$valor, $icono, $label])
                                    <button
                                        type="button"
                                        wire:click="$set('estado', '{{ $valor }}')"
                                        class="flex items-center justify-between gap-2 rounded-xl border px-3 py-2.5 text-left transition duration-200
                                            {{ $estado === $valor
                                                ? 'border-boton-principal bg-boton-principal text-white shadow-sm'
                                                : 'border-borde bg-fondo-panel text-titulo hover:border-boton-principal/30' }}"
                                    >
                                        <span class="flex items-center gap-2 text-[10px] font-black">
                                            <i class="ph-bold {{ $icono }}"></i>{{ $label }}
                                        </span>
                                        <i class="ph-bold {{ $estado === $valor ? 'ph-check-circle' : 'ph-circle' }}"></i>
                                    </button>
                                    @endforeach

                                    @if(auth()->user()?->can('medicacion.suspender'))
                                    @foreach([
                                        ['SUSPENDIDO', 'ph-stop-circle', 'Suspendido'],
                                        ['FINALIZADO', 'ph-check-square', 'Finalizado'],
                                    ] as [$valor, $icono, $label])
                                    <button
                                        type="button"
                                        wire:click="$set('estado', '{{ $valor }}')"
                                        class="flex items-center justify-between gap-2 rounded-xl border px-3 py-2.5 text-left transition duration-200
                                            {{ $estado === $valor
                                                ? 'border-estado-error bg-estado-error text-white shadow-sm'
                                                : 'border-borde bg-fondo-panel text-titulo hover:border-estado-error/30' }}"
                                    >
                                        <span class="flex items-center gap-2 text-[10px] font-black">
                                            <i class="ph-bold {{ $icono }}"></i>{{ $label }}
                                        </span>
                                        <i class="ph-bold {{ $estado === $valor ? 'ph-check-circle' : 'ph-circle' }}"></i>
                                    </button>
                                    @endforeach
                                    @endif
                                </div>

                                @if(in_array($estado, ['SUSPENDIDO', 'FINALIZADO'], true))
                                <div class="mt-3 flex items-start gap-2 rounded-xl border border-estado-error/20 bg-estado-errorBg p-3 text-[10px] font-semibold text-apoyo">
                                    <i class="ph-fill ph-warning-circle mt-0.5 text-estado-error"></i>
                                    <span>
                                        Está seleccionando un estado terminal. La operación será revalidada con el permiso
                                        <strong>medicacion.suspender</strong> al guardar.
                                    </span>
                                </div>
                                @endif

                                @error('estado')
                                <p class="mt-2 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                                @enderror
                            </div>
                            @else
                            <div class="sm:col-span-2 lg:col-span-3">
                                <div class="flex items-center gap-3 rounded-xl border border-estado-exito/20 bg-estado-exitoBg p-3">
                                    <i class="ph-fill ph-check-circle text-lg text-estado-exito"></i>
                                    <div>
                                        <p class="text-xs font-black text-titulo">Nueva orden: ACTIVO</p>
                                        <p class="mt-0.5 text-[10px] font-semibold text-apoyo">
                                            Las nuevas prescripciones se registran inicialmente como activas.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </section>

                    {{-- 5. OBSERVACIONES --}}
                    <section class="rounded-2xl border border-borde bg-fondo-panel/50 p-4 sm:p-5">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-boton-principal/10 text-[11px] font-black text-boton-principal">5</span>
                                    <h3 class="text-sm font-black text-titulo">Indicaciones especiales</h3>
                                </div>
                                <p class="mt-1 pl-9 text-[10px] font-semibold text-apoyo">
                                    Añada únicamente instrucciones relevantes para la administración o seguimiento.
                                </p>
                            </div>

                            <span class="shrink-0 text-[9px] font-bold text-apoyo">{{ mb_strlen($observacion) }}/255</span>
                        </div>

                        <textarea
                            wire:model.live.debounce.600ms="observacion"
                            rows="3"
                            maxlength="255"
                            placeholder="Ej.: administrar después de alimentos; vigilar tolerancia; no triturar..."
                            aria-invalid="{{ $errors->has('observacion') ? 'true' : 'false' }}"
                            class="w-full resize-none rounded-xl border bg-fondo-card px-3.5 py-3 text-sm font-semibold leading-relaxed text-titulo outline-none transition focus:ring-2
                                @error('observacion')
                                    border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                @else
                                    border-borde focus:border-borde-focus focus:ring-boton-principal/10
                                @enderror"
                        ></textarea>

                        @error('observacion')
                        <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                        @enderror
                    </section>

                    {{-- RESUMEN VISUAL DE PRESCRIPCIÓN --}}
                    <section class="overflow-hidden rounded-2xl border border-borde bg-fondo-card">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-borde bg-fondo-panel/60 px-4 py-3 sm:px-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-principal/10 text-boton-principal">
                                    <i class="ph-fill ph-flow-arrow text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-titulo">Resumen de la orden</h3>
                                    <p class="text-[10px] font-semibold text-apoyo">
                                        Revise la secuencia antes de confirmar.
                                    </p>
                                </div>
                            </div>

                            <span class="rounded-full border px-3 py-1 text-[10px] font-black {{ $estadoColor }}">
                                {{ $isEditing ? $estado : 'ACTIVO' }}
                            </span>
                        </div>

                        <div class="grid gap-3 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-4">
                            {{-- Nodo 1 --}}
                            <div class="relative rounded-2xl border border-borde bg-fondo-panel p-4">
                                <span class="absolute right-3 top-3 text-[9px] font-black text-apoyo">01</span>
                                <i class="ph-fill ph-pill text-xl text-boton-principal"></i>
                                <p class="mt-3 text-[9px] font-black uppercase tracking-wider text-apoyo">Medicamento</p>
                                <p class="mt-1 break-words text-sm font-black text-titulo">
                                    {{ trim($nombre_medicamento) !== '' ? $nombre_medicamento : 'Pendiente' }}
                                </p>
                                <p class="mt-0.5 text-[10px] font-semibold text-apoyo">
                                    {{ trim($dosis) !== '' ? $dosis : 'Dosis pendiente' }}
                                </p>
                            </div>

                            {{-- Nodo 2 --}}
                            <div class="relative rounded-2xl border border-borde bg-fondo-panel p-4">
                                <span class="absolute right-3 top-3 text-[9px] font-black text-apoyo">02</span>
                                <i class="ph-fill {{ $es_prn ? 'ph-first-aid-kit text-estado-advertencia' : 'ph-clock text-estado-info' }} text-xl"></i>
                                <p class="mt-3 text-[9px] font-black uppercase tracking-wider text-apoyo">Pauta</p>
                                <p class="mt-1 text-sm font-black text-titulo">
                                    {{ trim($frecuencia) !== '' ? $frecuencia : 'Frecuencia pendiente' }}
                                </p>
                                <p class="mt-0.5 text-[10px] font-semibold text-apoyo">{{ $this->resumenHorario }}</p>
                            </div>

                            {{-- Nodo 3 --}}
                            <div class="relative rounded-2xl border border-borde bg-fondo-panel p-4">
                                <span class="absolute right-3 top-3 text-[9px] font-black text-apoyo">03</span>
                                <i class="ph-fill ph-calendar-range text-xl text-estado-exito"></i>
                                <p class="mt-3 text-[9px] font-black uppercase tracking-wider text-apoyo">Vigencia</p>
                                <p class="mt-1 text-sm font-black text-titulo">
                                    {{ $fecha_inicio ? \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') : 'Inicio pendiente' }}
                                </p>
                                <p class="mt-0.5 text-[10px] font-semibold text-apoyo">
                                    @if($fecha_fin)
                                        hasta {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
                                    @else
                                        sin fecha de fin definida
                                    @endif
                                </p>
                            </div>

                            {{-- Nodo 4 --}}
                            <div class="relative rounded-2xl border border-borde bg-fondo-panel p-4">
                                <span class="absolute right-3 top-3 text-[9px] font-black text-apoyo">04</span>
                                <i class="ph-fill ph-route text-xl text-boton-principal"></i>
                                <p class="mt-3 text-[9px] font-black uppercase tracking-wider text-apoyo">Vía / modalidad</p>
                                <p class="mt-1 text-sm font-black text-titulo">
                                    {{ $via_administracion ?: 'Vía pendiente' }}
                                </p>
                                <p class="mt-0.5 text-[10px] font-semibold text-apoyo">
                                    {{ $this->tipoOrden }}
                                </p>
                            </div>
                        </div>

                        @if($es_prn && trim($condicion_prn) !== '')
                        <div class="mx-4 mb-4 rounded-xl border border-estado-advertencia/20 bg-estado-advertenciaBg p-3 sm:mx-5 sm:mb-5">
                            <div class="flex items-start gap-2">
                                <i class="ph-fill ph-first-aid-kit mt-0.5 text-estado-advertencia"></i>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-estado-advertencia">Condición PRN</p>
                                    <p class="mt-1 text-xs font-semibold leading-relaxed text-titulo">{{ $condicion_prn }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </section>

                    {{-- Nota de seguridad --}}
                    <div class="flex items-start gap-3 rounded-2xl border border-estado-info/20 bg-estado-infoBg p-4">
                        <i class="ph-fill ph-shield-check mt-0.5 text-lg text-estado-info"></i>
                        <div>
                            <p class="text-xs font-black text-titulo">Trazabilidad y seguridad</p>
                            <p class="mt-1 text-[10px] font-semibold leading-relaxed text-apoyo">
                                El sistema revalida rol, permisos, residente, estado del expediente y pertenencia de la orden al guardar.
                                La administración posterior del medicamento pertenece al flujo de Enfermería.
                            </p>
                        </div>
                    </div>
                </div>
            </fieldset>

            {{-- FOOTER --}}
            <footer class="shrink-0 border-t border-borde bg-fondo-card px-5 py-4 sm:px-7">
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="hidden h-9 w-9 items-center justify-center rounded-xl bg-fondo-panel text-boton-principal sm:flex">
                            <i class="ph-bold ph-list-checks"></i>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-wider text-apoyo">
                                Datos principales
                            </p>
                            <p class="text-xs font-bold text-titulo">
                                {{ $camposClaveCompletos }}/{{ $totalCamposClave }} completos
                                @if($posibleDuplicado && !$confirmar_duplicado)
                                    · duplicidad pendiente de confirmar
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        @if($this->tieneCambios)
                        <button
                            type="button"
                            x-on:click="if (confirm('Hay cambios sin guardar. ¿Desea cancelar y descartarlos?')) { $wire.cerrarModal() }"
                            wire:loading.attr="disabled"
                            wire:target="guardar"
                            class="rm-btn-secondary h-11 px-5 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                        @else
                        <button
                            type="button"
                            wire:click="cerrarModal"
                            wire:loading.attr="disabled"
                            wire:target="guardar"
                            class="rm-btn-secondary h-11 px-5 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                        @endif

                        <button
                            type="submit"
                            @disabled(!$this->puedeGuardar)
                            wire:loading.attr="disabled"
                            wire:target="guardar"
                            class="rm-btn-primary flex h-11 min-w-[210px] items-center justify-center gap-2 px-6 transition duration-200 disabled:cursor-not-allowed disabled:opacity-45 disabled:shadow-none"
                        >
                            <span wire:loading.remove wire:target="guardar" class="flex items-center gap-2">
                                <i class="ph-bold {{ $this->puedeGuardar ? 'ph-check-circle' : 'ph-lock-key' }} text-base"></i>
                                {{ $this->puedeGuardar
                                    ? ($isEditing ? 'Actualizar prescripción' : 'Guardar prescripción')
                                    : 'Complete la orden' }}
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
