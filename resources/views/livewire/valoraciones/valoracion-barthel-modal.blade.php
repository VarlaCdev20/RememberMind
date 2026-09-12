<div>
@if($mostrar)
@php
    $nombreCompleto = $adulto?->nombre_completo
        ?? trim(($adulto?->nombres ?? '') . ' ' . ($adulto?->ap_paterno ?? '') . ' ' . ($adulto?->ap_materno ?? ''));

    $edadTexto = $adulto?->edad_texto ?? null;
    $estadoHumano = $adulto?->estado_humano ?? 'Sin estado';

    $items = [
        [
            'prop' => 'alimentacion',
            'numero' => '01',
            'icono' => 'ph-fork-knife',
            'titulo' => 'Alimentación',
            'ayuda' => 'Capacidad para comer y manejar los alimentos.',
            'opciones' => [
                [0, 'Dependiente', 'Requiere ayuda completa'],
                [5, 'Necesita ayuda', 'Precisa apoyo parcial'],
                [10, 'Independiente', 'Come sin ayuda'],
            ],
        ],
        [
            'prop' => 'bano',
            'numero' => '02',
            'icono' => 'ph-bathtub',
            'titulo' => 'Baño / ducha',
            'ayuda' => 'Capacidad para realizar el baño personal.',
            'opciones' => [
                [0, 'Dependiente', 'Necesita asistencia'],
                [5, 'Independiente', 'Se baña sin ayuda'],
            ],
        ],
        [
            'prop' => 'aseo_personal',
            'numero' => '03',
            'icono' => 'ph-hand-soap',
            'titulo' => 'Aseo personal',
            'ayuda' => 'Higiene básica, arreglo y cuidado personal.',
            'opciones' => [
                [0, 'Necesita ayuda', 'Precisa asistencia'],
                [5, 'Independiente', 'Realiza su aseo'],
            ],
        ],
        [
            'prop' => 'vestido',
            'numero' => '04',
            'icono' => 'ph-t-shirt',
            'titulo' => 'Vestido / desvestido',
            'ayuda' => 'Capacidad para ponerse y quitarse la ropa.',
            'opciones' => [
                [0, 'Dependiente', 'Requiere ayuda completa'],
                [5, 'Necesita ayuda', 'Realiza parte de la actividad'],
                [10, 'Independiente', 'Se viste sin ayuda'],
            ],
        ],
        [
            'prop' => 'control_intestinal',
            'numero' => '05',
            'icono' => 'ph-activity',
            'titulo' => 'Control intestinal',
            'ayuda' => 'Control de la continencia intestinal.',
            'opciones' => [
                [0, 'Incontinente', 'Sin control'],
                [5, 'Accidente ocasional', 'Control parcial'],
                [10, 'Continente', 'Control conservado'],
            ],
        ],
        [
            'prop' => 'control_vesical',
            'numero' => '06',
            'icono' => 'ph-drop',
            'titulo' => 'Control vesical',
            'ayuda' => 'Control de la continencia urinaria.',
            'opciones' => [
                [0, 'Incontinente', 'Sin control'],
                [5, 'Accidente ocasional', 'Control parcial'],
                [10, 'Continente', 'Control conservado'],
            ],
        ],
        [
            'prop' => 'uso_retrete',
            'numero' => '07',
            'icono' => 'ph-toilet',
            'titulo' => 'Uso del retrete / WC',
            'ayuda' => 'Capacidad para usar el sanitario.',
            'opciones' => [
                [0, 'Dependiente', 'Requiere ayuda completa'],
                [5, 'Necesita ayuda', 'Precisa apoyo parcial'],
                [10, 'Independiente', 'Lo usa sin ayuda'],
            ],
        ],
        [
            'prop' => 'traslados',
            'numero' => '08',
            'icono' => 'ph-arrows-left-right',
            'titulo' => 'Traslados silla-cama',
            'ayuda' => 'Capacidad para realizar transferencias.',
            'opciones' => [
                [0, 'Incapaz', 'Dependencia completa'],
                [5, 'Gran ayuda', 'Requiere asistencia importante'],
                [10, 'Pequeña ayuda', 'Precisa apoyo mínimo'],
                [15, 'Independiente', 'Realiza traslados sin ayuda'],
            ],
        ],
        [
            'prop' => 'deambulacion',
            'numero' => '09',
            'icono' => 'ph-person-simple-walk',
            'titulo' => 'Deambulación',
            'ayuda' => 'Forma habitual de desplazamiento.',
            'opciones' => [
                [0, 'Inmóvil', 'No se desplaza'],
                [5, 'Independiente en silla', 'Se desplaza en silla de ruedas'],
                [10, 'Camina con ayuda', 'Requiere apoyo'],
                [15, 'Independiente', 'Camina sin ayuda'],
            ],
        ],
        [
            'prop' => 'escaleras',
            'numero' => '10',
            'icono' => 'ph-stairs',
            'titulo' => 'Subir y bajar escaleras',
            'ayuda' => 'Capacidad funcional para utilizar escaleras.',
            'opciones' => [
                [0, 'Incapaz', 'No puede realizarlas'],
                [5, 'Necesita ayuda', 'Precisa asistencia'],
                [10, 'Independiente', 'Las realiza sin ayuda'],
            ],
        ],
    ];

    $scoreColor = !$this->evaluacionCompleta
        ? 'text-boton-acento'
        : ($totalBarthel >= 100
            ? 'text-estado-exito'
            : ($totalBarthel >= 61
                ? 'text-estado-info'
                : ($totalBarthel >= 41
                    ? 'text-estado-advertencia'
                    : 'text-estado-error')));

    $scoreBg = !$this->evaluacionCompleta
        ? 'border-boton-acento/20 bg-boton-acento/5'
        : ($totalBarthel >= 100
            ? 'border-estado-exito/20 bg-estado-exitoBg'
            : ($totalBarthel >= 61
                ? 'border-estado-info/20 bg-estado-infoBg'
                : ($totalBarthel >= 41
                    ? 'border-estado-advertencia/20 bg-estado-advertenciaBg'
                    : 'border-estado-error/20 bg-estado-errorBg')));
@endphp

<div
    class="fixed inset-0 z-[2147483646] flex items-center justify-center overflow-y-auto bg-slate-950/60 p-3 backdrop-blur-md sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="barthel-modal-title"
>
    {{-- No se cierra al tocar el fondo para evitar pérdida accidental de datos. --}}

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
        <div wire:loading.flex wire:target="guardar" class="absolute inset-x-0 top-0 z-50 h-1 overflow-hidden bg-boton-acento/15">
            <div class="h-full w-1/3 animate-pulse rounded-full bg-boton-acento"></div>
        </div>

        {{-- Header --}}
        <header class="shrink-0 border-b border-borde bg-fondo-card px-5 py-4 sm:px-7">
            <div class="flex items-start justify-between gap-4">
                <div class="flex min-w-0 items-start gap-4">
                    <div class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-estado-exitoBg text-estado-exito shadow-sm">
                        <i class="ph-fill ph-person-simple-walk text-2xl"></i>
                        <span class="absolute -right-1 -top-1 h-3 w-3 animate-pulse rounded-full border-2 border-fondo-card bg-boton-acento"></span>
                    </div>

                    <div class="min-w-0">
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-black uppercase tracking-[0.18em] text-boton-acento">
                                Valoración funcional
                            </span>
                            <span class="rounded-full bg-estado-exitoBg px-2 py-0.5 text-[10px] font-black text-estado-exito">
                                Acceso validado
                            </span>
                        </div>

                        <h2 id="barthel-modal-title" class="text-xl font-black leading-tight text-titulo sm:text-2xl">
                            Índice de Barthel
                        </h2>

                        <p class="mt-1 text-xs font-semibold text-apoyo">
                            Complete las 10 actividades. Un valor de 0 es una respuesta válida y ya no significa “sin responder”.
                        </p>
                    </div>
                </div>

                @if($this->tieneCambios)
                <button
                    type="button"
                    x-on:click="if (confirm('Hay cambios sin guardar. ¿Desea cerrar y descartarlos?')) { $wire.cerrar() }"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-borde bg-fondo-panel text-apoyo transition duration-200 hover:rotate-90 hover:border-estado-error/40 hover:bg-estado-errorBg hover:text-estado-error active:scale-90"
                    aria-label="Cerrar y descartar cambios"
                >
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
                @else
                <button
                    type="button"
                    wire:click="cerrar"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-borde bg-fondo-panel text-apoyo transition duration-200 hover:rotate-90 hover:border-estado-error/40 hover:bg-estado-errorBg hover:text-estado-error active:scale-90"
                    aria-label="Cerrar"
                >
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
                @endif
            </div>

            @if($adulto)
            <div class="mt-4 grid gap-3 rounded-2xl border border-borde bg-fondo-panel/70 p-3 sm:grid-cols-[1fr_auto] sm:items-center sm:p-4">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-boton-acento/10 text-sm font-black text-boton-acento">
                        {{ mb_strtoupper(mb_substr($adulto->nombres ?? 'R', 0, 1) . mb_substr($adulto->ap_paterno ?? '', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-titulo">{{ $nombreCompleto ?: 'Residente' }}</p>
                        <div class="mt-0.5 flex flex-wrap gap-x-3 gap-y-1 text-[11px] font-semibold text-apoyo">
                            @if($adulto->ci)<span>CI {{ $adulto->ci }}</span>@endif
                            @if($edadTexto)<span>{{ $edadTexto }}</span>@endif
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

                    {{-- Estado / progreso --}}
                    <section class="grid gap-4 lg:grid-cols-[1fr_270px]">
                        <div class="rounded-2xl border border-borde bg-fondo-panel/55 p-4 sm:p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-apoyo">Progreso de evaluación</p>
                                    <div class="mt-1 flex items-baseline gap-2">
                                        <span class="text-2xl font-black text-titulo">{{ $this->itemsRespondidos }}/{{ $this->totalItems }}</span>
                                        <span class="text-xs font-bold text-apoyo">actividades respondidas</span>
                                    </div>
                                </div>

                                <span class="rounded-full px-3 py-1.5 text-xs font-black
                                    {{ $this->evaluacionCompleta
                                        ? 'bg-estado-exitoBg text-estado-exito'
                                        : 'bg-estado-advertenciaBg text-estado-advertencia' }}">
                                    {{ $this->evaluacionCompleta ? 'Completa' : 'En progreso' }}
                                </span>
                            </div>

                            <div class="mt-4 h-3 overflow-hidden rounded-full bg-fondo-card">
                                <div
                                    class="h-full rounded-full bg-boton-acento transition-all duration-500 ease-out"
                                    style="width: {{ $this->progresoEvaluacion }}%"
                                ></div>
                            </div>

                            <div class="mt-2 flex items-center justify-between text-[10px] font-bold text-apoyo">
                                <span>{{ $this->progresoEvaluacion }}% completado</span>
                                @if(!$this->evaluacionCompleta)
                                <span>{{ count($this->itemsPendientes) }} pendientes</span>
                                @else
                                <span class="text-estado-exito">Todos los ítems revisados</span>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl border p-4 transition-all duration-300 {{ $scoreBg }}">
                            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-apoyo">
                                {{ $this->evaluacionCompleta ? 'Resultado' : 'Puntaje parcial' }}
                            </p>
                            <div class="mt-1 flex items-end gap-1">
                                <span class="text-4xl font-black leading-none {{ $scoreColor }}">{{ $totalBarthel }}</span>
                                <span class="pb-1 text-sm font-black text-apoyo">/100</span>
                            </div>

                            @if($this->evaluacionCompleta)
                            <p class="mt-2 text-xs font-black {{ $scoreColor }}">{{ $clasificacionBarthel }}</p>
                            @else
                            <p class="mt-2 text-[10px] font-semibold leading-relaxed text-apoyo">
                                No se interpreta el puntaje hasta completar las 10 actividades.
                            </p>
                            @endif
                        </div>
                    </section>

                    {{-- Error general --}}
                    @error('general')
                    <div class="flex items-start gap-3 rounded-2xl border border-estado-error/30 bg-estado-errorBg p-4">
                        <i class="ph-fill ph-warning-octagon mt-0.5 text-xl text-estado-error"></i>
                        <div>
                            <p class="text-sm font-black text-titulo">No se pudo guardar la valoración</p>
                            <p class="mt-1 text-xs font-semibold text-estado-error">{{ $message }}</p>
                        </div>
                    </div>
                    @enderror

                    {{-- Fecha --}}
                    <section class="rounded-2xl border border-borde bg-fondo-panel/50 p-4 sm:p-5">
                        <div class="grid gap-4 md:grid-cols-[260px_1fr] md:items-end">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-black uppercase tracking-wider text-apoyo">
                                    Fecha de valoración <span class="text-estado-error">*</span>
                                </label>
                                <input
                                    type="date"
                                    wire:model.live="fecha_valoracion"
                                    max="{{ today()->toDateString() }}"
                                    aria-invalid="{{ $errors->has('fecha_valoracion') ? 'true' : 'false' }}"
                                    class="w-full rounded-xl border bg-fondo-card px-3.5 py-2.5 text-sm font-bold text-titulo outline-none transition focus:ring-2
                                        @error('fecha_valoracion')
                                            border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                        @else
                                            border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                        @enderror"
                                >
                                @error('fecha_valoracion')
                                <p class="mt-1.5 flex items-center gap-1 text-[10px] font-bold text-estado-error">
                                    <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                                </p>
                                @enderror
                            </div>

                            <div class="flex items-start gap-3 rounded-xl border border-estado-info/20 bg-estado-infoBg p-3">
                                <i class="ph-fill ph-info mt-0.5 text-lg text-estado-info"></i>
                                <p class="text-[10px] font-semibold leading-relaxed text-apoyo">
                                    Seleccione la opción que mejor represente la capacidad observada del residente. No deje actividades sin evaluar.
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- 10 ítems --}}
                    <section class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-black text-titulo">Actividades de la vida diaria</h3>
                                <p class="text-[10px] font-semibold text-apoyo">Seleccione exactamente una opción por actividad.</p>
                            </div>
                            <span class="rounded-full bg-boton-acento/10 px-3 py-1 text-[10px] font-black text-boton-acento">
                                10 ítems
                            </span>
                        </div>

                        @foreach($items as $item)
                        @php
                            $valorActual = $this->{$item['prop']};
                            $respondido = $valorActual !== null;
                            $hayError = $errors->has($item['prop']);
                        @endphp

                        <article class="overflow-hidden rounded-2xl border transition-all duration-300
                            {{ $hayError
                                ? 'border-estado-error/40 bg-estado-errorBg/30'
                                : ($respondido
                                    ? 'border-estado-exito/20 bg-fondo-card'
                                    : 'border-borde bg-fondo-card') }}">
                            <div class="grid gap-4 p-4 lg:grid-cols-[240px_1fr] lg:items-center sm:p-5">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                                        {{ $respondido ? 'bg-estado-exitoBg text-estado-exito' : 'bg-fondo-panel text-apoyo' }}">
                                        @if($respondido)
                                            <i class="ph-fill ph-check-circle text-xl"></i>
                                        @else
                                            <i class="ph-bold {{ $item['icono'] }} text-lg"></i>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[9px] font-black text-apoyo">{{ $item['numero'] }}</span>
                                            <h4 class="text-sm font-black text-titulo">{{ $item['titulo'] }}</h4>
                                        </div>
                                        <p class="mt-1 text-[10px] font-semibold leading-relaxed text-apoyo">{{ $item['ayuda'] }}</p>

                                        @if($respondido)
                                        <button
                                            type="button"
                                            wire:click="limpiarItem('{{ $item['prop'] }}')"
                                            class="mt-2 text-[9px] font-black text-boton-acento hover:underline"
                                        >
                                            Cambiar / limpiar respuesta
                                        </button>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <div class="grid gap-2
                                        {{ count($item['opciones']) === 2 ? 'sm:grid-cols-2' : (count($item['opciones']) === 4 ? 'sm:grid-cols-2 xl:grid-cols-4' : 'sm:grid-cols-3') }}">
                                        @foreach($item['opciones'] as [$valor, $titulo, $descripcion])
                                        <button
                                            type="button"
                                            wire:click="seleccionarItem('{{ $item['prop'] }}', {{ $valor }})"
                                            aria-pressed="{{ $valorActual === $valor ? 'true' : 'false' }}"
                                            class="group relative rounded-xl border p-3 text-left transition duration-200 hover:-translate-y-0.5 active:scale-[0.98]
                                                {{ $valorActual === $valor
                                                    ? 'border-boton-acento bg-boton-acento text-white shadow-md'
                                                    : 'border-borde bg-fondo-panel text-titulo hover:border-boton-acento/35' }}"
                                        >
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <p class="text-xs font-black">{{ $titulo }}</p>
                                                    <p class="mt-0.5 text-[9px] font-semibold leading-snug
                                                        {{ $valorActual === $valor ? 'text-white/80' : 'text-apoyo' }}">
                                                        {{ $descripcion }}
                                                    </p>
                                                </div>

                                                <span class="flex h-7 min-w-7 items-center justify-center rounded-lg px-1.5 text-xs font-black
                                                    {{ $valorActual === $valor
                                                        ? 'bg-white/15 text-white'
                                                        : 'bg-fondo-card text-boton-acento' }}">
                                                    {{ $valor }}
                                                </span>
                                            </div>
                                        </button>
                                        @endforeach
                                    </div>

                                    @if($hayError)
                                    <p class="mt-2 flex items-center gap-1 text-[10px] font-bold text-estado-error">
                                        <i class="ph-bold ph-warning-circle"></i>
                                        {{ $errors->first($item['prop']) }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </article>
                        @endforeach
                    </section>

                    {{-- Aviso de pendientes --}}
                    @if(!$this->evaluacionCompleta)
                    <div
                        class="rounded-2xl border border-estado-advertencia/25 bg-estado-advertenciaBg p-4"
                        x-transition:enter="transition duration-300"
                        x-transition:enter-start="-translate-y-2 opacity-0"
                        x-transition:enter-end="translate-y-0 opacity-100"
                    >
                        <div class="flex items-start gap-3">
                            <i class="ph-fill ph-list-checks mt-0.5 text-xl text-estado-advertencia"></i>
                            <div>
                                <p class="text-xs font-black text-titulo">Faltan {{ count($this->itemsPendientes) }} actividades</p>
                                <p class="mt-1 text-[10px] font-semibold leading-relaxed text-apoyo">
                                    {{ implode(' · ', $this->itemsPendientes) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Complementarios --}}
                    <section class="grid gap-4 lg:grid-cols-2">
                        <div class="rounded-2xl border border-borde bg-fondo-panel/50 p-4 sm:p-5">
                            <div class="mb-4 flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                                    <i class="ph-fill ph-wheelchair text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-titulo">Ayudas para movilidad</h3>
                                    <p class="text-[10px] font-semibold text-apoyo">Información complementaria independiente del puntaje.</p>
                                </div>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                                @foreach([
                                    ['usa_baston', 'ph-cane', 'Bastón'],
                                    ['usa_andador', 'ph-person-simple-walk', 'Andador'],
                                    ['usa_silla_ruedas', 'ph-wheelchair', 'Silla de ruedas'],
                                ] as [$prop, $icono, $label])
                                <button
                                    type="button"
                                    wire:click="$toggle('{{ $prop }}')"
                                    aria-pressed="{{ $this->{$prop} ? 'true' : 'false' }}"
                                    class="flex items-center justify-between gap-2 rounded-xl border px-3 py-3 text-left transition duration-200
                                        {{ $this->{$prop}
                                            ? 'border-estado-info/35 bg-estado-infoBg text-estado-info'
                                            : 'border-borde bg-fondo-card text-titulo hover:border-estado-info/25' }}"
                                >
                                    <span class="flex items-center gap-2 text-[11px] font-black">
                                        <i class="ph-bold {{ $icono }}"></i>{{ $label }}
                                    </span>
                                    <i class="ph-bold {{ $this->{$prop} ? 'ph-check-circle' : 'ph-circle' }}"></i>
                                </button>
                                @endforeach
                            </div>

                            @error('deambulacion')
                            <p class="mt-3 flex items-start gap-1.5 rounded-xl bg-estado-errorBg p-3 text-[10px] font-bold text-estado-error">
                                <i class="ph-bold ph-warning-circle mt-0.5"></i>{{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div class="rounded-2xl border border-borde bg-fondo-panel/50 p-4 sm:p-5">
                            <div class="mb-4 flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia">
                                    <i class="ph-fill ph-eye text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-titulo">Limitaciones y supervisión</h3>
                                    <p class="text-[10px] font-semibold text-apoyo">Marque solamente lo observado o documentado.</p>
                                </div>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach([
                                    ['baja_vision', 'ph-eye-slash', 'Baja visión'],
                                    ['baja_audicion', 'ph-ear-slash', 'Baja audición'],
                                    ['dificultad_hablar', 'ph-chat-slash', 'Dificultad para hablar'],
                                    ['necesita_supervision', 'ph-user-focus', 'Necesita supervisión'],
                                ] as [$prop, $icono, $label])
                                <button
                                    type="button"
                                    wire:click="$toggle('{{ $prop }}')"
                                    aria-pressed="{{ $this->{$prop} ? 'true' : 'false' }}"
                                    class="flex items-center justify-between gap-2 rounded-xl border px-3 py-3 text-left transition duration-200
                                        {{ $this->{$prop}
                                            ? 'border-estado-advertencia/35 bg-estado-advertenciaBg text-estado-advertencia'
                                            : 'border-borde bg-fondo-card text-titulo hover:border-estado-advertencia/25' }}"
                                >
                                    <span class="flex items-center gap-2 text-[11px] font-black">
                                        <i class="ph-bold {{ $icono }}"></i>{{ $label }}
                                    </span>
                                    <i class="ph-bold {{ $this->{$prop} ? 'ph-check-circle' : 'ph-circle' }}"></i>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    {{-- Riesgo de caída --}}
                    <section class="rounded-2xl border border-borde bg-fondo-card p-4 sm:p-5">
                        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <i class="ph-fill ph-warning-diamond text-lg text-estado-advertencia"></i>
                                    <h3 class="text-sm font-black text-titulo">
                                        Riesgo de caída <span class="text-estado-error">*</span>
                                    </h3>
                                </div>
                                <p class="mt-1 text-[10px] font-semibold text-apoyo">
                                    Es una valoración clínica independiente; no se calcula automáticamente a partir del Barthel.
                                </p>
                            </div>

                            @if($riesgo_caida)
                            <span class="rounded-full px-3 py-1 text-[10px] font-black
                                {{ $riesgo_caida === 'ALTO'
                                    ? 'bg-estado-errorBg text-estado-error'
                                    : ($riesgo_caida === 'MODERADO'
                                        ? 'bg-estado-advertenciaBg text-estado-advertencia'
                                        : 'bg-estado-exitoBg text-estado-exito') }}">
                                {{ $riesgo_caida }}
                            </span>
                            @endif
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach([
                                ['BAJO', 'ph-check-circle', 'Bajo', 'Sin indicadores relevantes registrados.', 'border-estado-exito bg-estado-exitoBg text-estado-exito'],
                                ['MODERADO', 'ph-warning-circle', 'Moderado', 'Requiere medidas preventivas y seguimiento.', 'border-estado-advertencia bg-estado-advertenciaBg text-estado-advertencia'],
                                ['ALTO', 'ph-warning-octagon', 'Alto', 'Requiere atención preventiva prioritaria.', 'border-estado-error bg-estado-errorBg text-estado-error'],
                            ] as [$valor, $icono, $titulo, $texto, $activo])
                            <button
                                type="button"
                                wire:click="$set('riesgo_caida', '{{ $valor }}')"
                                aria-pressed="{{ $riesgo_caida === $valor ? 'true' : 'false' }}"
                                class="rounded-2xl border p-4 text-left transition duration-200 hover:-translate-y-0.5 active:scale-[0.98]
                                    {{ $riesgo_caida === $valor
                                        ? $activo . ' shadow-sm'
                                        : 'border-borde bg-fondo-panel text-titulo hover:border-boton-acento/25' }}"
                            >
                                <div class="flex items-center gap-2">
                                    <i class="ph-fill {{ $icono }} text-lg"></i>
                                    <span class="text-xs font-black">{{ $titulo }}</span>
                                </div>
                                <p class="mt-2 text-[10px] font-semibold leading-relaxed {{ $riesgo_caida === $valor ? 'opacity-80' : 'text-apoyo' }}">
                                    {{ $texto }}
                                </p>
                            </button>
                            @endforeach
                        </div>

                        @error('riesgo_caida')
                        <p class="mt-2 flex items-center gap-1 text-[10px] font-bold text-estado-error">
                            <i class="ph-bold ph-warning-circle"></i>{{ $message }}
                        </p>
                        @enderror
                    </section>

                    {{-- Tabla de referencia coherente con el PHP --}}
                    <details class="group rounded-2xl border border-borde bg-fondo-panel/40 p-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <i class="ph-bold ph-chart-bar text-boton-acento"></i>
                                <span class="text-xs font-black text-titulo">Referencia de clasificación configurada</span>
                            </div>
                            <i class="ph-bold ph-caret-down text-apoyo transition group-open:rotate-180"></i>
                        </summary>

                        <div class="mt-4 grid gap-2 sm:grid-cols-3 xl:grid-cols-6">
                            @foreach([
                                ['100', 'Independiente', 'bg-estado-exitoBg text-estado-exito'],
                                ['91–99', 'Dep. escasa', 'bg-estado-exitoBg text-estado-exito'],
                                ['61–90', 'Dep. leve', 'bg-estado-infoBg text-estado-info'],
                                ['41–60', 'Dep. moderada', 'bg-estado-advertenciaBg text-estado-advertencia'],
                                ['21–40', 'Dep. severa', 'bg-boton-acento/10 text-boton-acento'],
                                ['0–20', 'Dep. total', 'bg-estado-errorBg text-estado-error'],
                            ] as [$rango, $clasificacion, $color])
                            <div class="rounded-xl {{ $color }} p-3 text-center">
                                <p class="text-sm font-black">{{ $rango }}</p>
                                <p class="mt-0.5 text-[9px] font-black">{{ $clasificacion }}</p>
                            </div>
                            @endforeach
                        </div>
                    </details>

                    {{-- Observaciones --}}
                    <section class="rounded-2xl border border-borde bg-fondo-panel/50 p-4 sm:p-5">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-black text-titulo">Observaciones</h3>
                                <p class="text-[10px] font-semibold text-apoyo">Registre contexto relevante que ayude a interpretar la valoración funcional.</p>
                            </div>
                            <span class="text-[9px] font-bold text-apoyo">{{ mb_strlen($observacion) }}/1000</span>
                        </div>

                        <textarea
                            wire:model.live.debounce.600ms="observacion"
                            rows="3"
                            maxlength="1000"
                            placeholder="Ej.: requiere estímulo verbal, utiliza dispositivo solo en exteriores, cambios respecto a valoración previa..."
                            class="w-full resize-none rounded-xl border bg-fondo-card px-3.5 py-3 text-sm font-semibold leading-relaxed text-titulo outline-none transition focus:ring-2
                                @error('observacion')
                                    border-estado-error focus:border-estado-error focus:ring-estado-error/15
                                @else
                                    border-borde focus:border-borde-focus focus:ring-boton-acento/10
                                @enderror"
                        ></textarea>

                        @error('observacion')
                        <p class="mt-1.5 text-[10px] font-bold text-estado-error">{{ $message }}</p>
                        @enderror
                    </section>
                </div>
            </fieldset>

            {{-- Footer fijo --}}
            <footer class="shrink-0 border-t border-borde bg-fondo-card px-5 py-4 sm:px-7">
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="text-sm">
                            <span class="font-black {{ $scoreColor }}">{{ $totalBarthel }}/100</span>
                            <span class="ml-2 text-xs font-semibold text-apoyo">{{ $clasificacionBarthel }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        @if($this->tieneCambios)
                        <button
                            type="button"
                            x-on:click="if (confirm('Hay cambios sin guardar. ¿Desea cancelar y descartarlos?')) { $wire.cerrar() }"
                            wire:loading.attr="disabled"
                            wire:target="guardar"
                            class="rm-btn-secondary h-11 px-5 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                        @else
                        <button
                            type="button"
                            wire:click="cerrar"
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
                                {{ $this->puedeGuardar ? 'Guardar valoración' : 'Complete la evaluación' }}
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
