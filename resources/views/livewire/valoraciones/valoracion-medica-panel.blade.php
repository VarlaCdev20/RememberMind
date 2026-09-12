<div
    class="space-y-5 pb-8"
    x-data="{
        previewId: @entangle('seleccionadoId').live,
        previewTab: 'resumen',
        filtrosAbiertos: false
    }"
    x-effect="if (!previewId) previewTab = 'resumen'"
>
    @php
        $estadoLabel = fn ($estado) => match (strtoupper((string) $estado)) {
            'ACTIVO' => 'Completada',
            'BORRADOR' => 'Borrador',
            default => filled($estado) ? $estado : 'Sin estado',
        };

        $estadoClase = fn ($estado) => match (strtoupper((string) $estado)) {
            'ACTIVO' => 'border-estado-exitoBorde bg-estado-exitoBg text-estado-exito',
            'BORRADOR' => 'border-estado-infoBorde bg-estado-infoBg text-estado-info',
            default => 'border-estado-neutralBorde bg-estado-neutralBg text-estado-neutral',
        };

        $hayFiltros = trim($search) !== ''
            || $filtroEstado !== ''
            || $filtroAlergias !== ''
            || $filtroAntecedente !== ''
            || $filtroPeriodo !== ''
            || $orden !== 'RECIENTES';

        $sintesisMap = collect($seleccionadoSintesis ?? [])
            ->mapWithKeys(fn ($item) => [$item['titulo'] => $item['valor']]);

        $previewAdulto = $seleccionado?->adultoMayor;
        $previewRegistrador = $seleccionado?->registrador;

        $previewNombre = $previewAdulto
            ? trim(
                ($previewAdulto->nombres ?? '') . ' ' .
                ($previewAdulto->ap_paterno ?? '') . ' ' .
                ($previewAdulto->ap_materno ?? '')
            )
            : '';

        $previewIniciales = $previewAdulto
            ? mb_strtoupper(
                mb_substr($previewAdulto->nombres ?? 'R', 0, 1) .
                mb_substr($previewAdulto->ap_paterno ?? '', 0, 1)
            )
            : '—';

        $previewRegistradorNombre = $previewRegistrador
            ? trim(
                ($previewRegistrador->nombres ?? $previewRegistrador->name ?? '') . ' ' .
                ($previewRegistrador->ap_paterno ?? '')
            )
            : 'No identificado';
    @endphp

    {{-- ============================================================
         ENCABEZADO
    ============================================================ --}}
    <section class="overflow-hidden rounded-[28px] border border-borde bg-fondo-card shadow-card">
        <div class="flex flex-col gap-5 p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-modulo-saludFondo text-modulo-salud shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-cardHover">
                    <i class="ph-fill ph-stethoscope text-2xl"></i>
                </div>

                <div class="min-w-0">
                    <div class="mb-1 flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-[0.18em] text-boton-acento">
                            Medicina
                        </span>

                        <span class="rounded-full border border-borde bg-fondo-panel px-2.5 py-1 text-[10px] font-black text-apoyo">
                            Admisión
                        </span>
                    </div>

                    <h1 class="text-2xl font-black tracking-tight text-titulo sm:text-3xl">
                        Valoraciones médicas de admisión
                    </h1>

                    <p class="mt-1 max-w-3xl text-sm font-semibold leading-relaxed text-apoyo">
                        Revise, filtre y trabaje las valoraciones desde una sola bandeja. La vista lateral reemplaza el detalle en un segundo modal.
                    </p>
                </div>
            </div>

            @can('valoracion_medica.crear')
                @can('ficha_medica.crear')
                    <button
                        type="button"
                        wire:click="abrirCrear"
                        wire:loading.attr="disabled"
                        wire:target="abrirCrear"
                        class="rm-btn-primary h-11 px-5"
                    >
                        <span wire:loading.remove wire:target="abrirCrear" class="inline-flex items-center gap-2">
                            <i class="ph-bold ph-plus-circle text-base"></i>
                            Nueva valoración
                        </span>

                        <span wire:loading.flex wire:target="abrirCrear" class="items-center gap-2">
                            <i class="ph-bold ph-spinner animate-spin"></i>
                            Abriendo...
                        </span>
                    </button>
                @endcan
            @endcan
        </div>
    </section>

    {{-- ============================================================
         KPI FUNCIONALES
    ============================================================ --}}
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <button
            type="button"
            wire:click="filtrarEstado('')"
            class="rounded-2xl border border-borde bg-fondo-card p-4 text-left shadow-card transition duration-200 hover:-translate-y-0.5 hover:shadow-cardHover"
        >
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-wider text-apoyo">Registros encontrados</p>
                    <p class="mt-1 text-2xl font-black text-titulo">{{ $metricas['total'] }}</p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-kpi-residentesBg text-kpi-residentes">
                    <i class="ph-bold ph-clipboard-text text-lg"></i>
                </span>
            </div>
        </button>

        <button
            type="button"
            wire:click="filtrarEstado('BORRADOR')"
            class="rounded-2xl border p-4 text-left shadow-card transition duration-200 hover:-translate-y-0.5 hover:shadow-cardHover
                {{ $filtroEstado === 'BORRADOR'
                    ? 'border-estado-infoBorde bg-estado-infoBg'
                    : 'border-borde bg-fondo-card' }}"
        >
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-wider text-apoyo">Borradores</p>
                    <p class="mt-1 text-2xl font-black text-estado-info">{{ $metricas['borradores'] }}</p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                    <i class="ph-bold ph-file-dashed text-lg"></i>
                </span>
            </div>
        </button>

        <button
            type="button"
            wire:click="filtrarEstado('ACTIVO')"
            class="rounded-2xl border p-4 text-left shadow-card transition duration-200 hover:-translate-y-0.5 hover:shadow-cardHover
                {{ $filtroEstado === 'ACTIVO'
                    ? 'border-estado-exitoBorde bg-estado-exitoBg'
                    : 'border-borde bg-fondo-card' }}"
        >
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-wider text-apoyo">Completadas</p>
                    <p class="mt-1 text-2xl font-black text-estado-exito">{{ $metricas['completadas'] }}</p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                    <i class="ph-bold ph-check-circle text-lg"></i>
                </span>
            </div>
        </button>

        <button
            type="button"
            wire:click="filtrarAlergias('CON_DATO')"
            class="rounded-2xl border p-4 text-left shadow-card transition duration-200 hover:-translate-y-0.5 hover:shadow-cardHover
                {{ $filtroAlergias === 'CON_DATO'
                    ? 'border-estado-advertenciaBorde bg-estado-advertenciaBg'
                    : 'border-borde bg-fondo-card' }}"
        >
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-wider text-apoyo">Con dato de alergias</p>
                    <p class="mt-1 text-2xl font-black text-estado-advertencia">{{ $metricas['alergiasConDato'] }}</p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia">
                    <i class="ph-bold ph-warning-octagon text-lg"></i>
                </span>
            </div>
        </button>
    </section>

    {{-- ============================================================
         BUSCADOR + FILTROS AVANZADOS
    ============================================================ --}}
    <section class="overflow-hidden rounded-2xl border border-borde bg-fondo-card shadow-card">
        <div class="grid gap-3 p-4 sm:p-5 lg:grid-cols-[minmax(0,1fr)_210px_auto_auto] lg:items-end">
            <label class="block">
                <span class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-apoyo">Buscar residente</span>

                <div class="relative">
                    <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>

                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        maxlength="120"
                        autocomplete="off"
                        placeholder="Nombre, apellido, C.I. o código..."
                        class="w-full rounded-xl border border-input-borde bg-input-bg py-2.5 pl-10 pr-10 text-sm font-semibold text-input-texto outline-none transition placeholder:text-input-placeholder focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                    >

                    <i
                        wire:loading
                        wire:target="search"
                        class="ph-bold ph-spinner absolute right-3.5 top-1/2 -translate-y-1/2 animate-spin text-boton-acento"
                    ></i>
                </div>
            </label>

            <label class="block">
                <span class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-apoyo">Estado</span>
                <select
                    wire:model.live="filtroEstado"
                    class="w-full rounded-xl border border-input-borde bg-input-bg px-3.5 py-2.5 text-xs font-bold text-input-texto outline-none transition focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                >
                    <option value="">Todos</option>
                    <option value="BORRADOR">Borrador</option>
                    <option value="ACTIVO">Completada</option>
                </select>
            </label>

            <button
                type="button"
                x-on:click="filtrosAbiertos = !filtrosAbiertos"
                class="rm-btn-secondary h-10 px-4"
            >
                <i class="ph-bold ph-funnel"></i>
                Más filtros
                <i
                    class="ph-bold ph-caret-down transition duration-200"
                    x-bind:class="filtrosAbiertos ? 'rotate-180' : ''"
                ></i>
            </button>

            <button
                type="button"
                wire:click="$refresh"
                class="rm-btn-secondary h-10 px-4"
            >
                <i
                    wire:loading.remove
                    wire:target="$refresh"
                    class="ph-bold ph-arrows-clockwise"
                ></i>
                <i
                    wire:loading
                    wire:target="$refresh"
                    class="ph-bold ph-spinner animate-spin"
                ></i>
                Actualizar
            </button>
        </div>

        <div
            x-show="filtrosAbiertos"
            x-cloak
            x-transition:enter="transition duration-200 ease-out"
            x-transition:enter-start="-translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition duration-150 ease-in"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="-translate-y-2 opacity-0"
            class="border-t border-borde bg-fondo-panel/50 p-4 sm:p-5"
        >
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <label>
                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-apoyo">Información de alergias</span>
                    <select
                        wire:model.live="filtroAlergias"
                        class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-xs font-bold text-input-texto outline-none focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                    >
                        <option value="">Todas</option>
                        <option value="CON_DATO">Con información registrada</option>
                        <option value="SIN_DATO">Sin información registrada</option>
                    </select>
                </label>

                <label>
                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-apoyo">Antecedente estructurado</span>
                    <select
                        wire:model.live="filtroAntecedente"
                        class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-xs font-bold text-input-texto outline-none focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                    >
                        <option value="">Todos</option>
                        @foreach($antecedentesDisponibles as $campo => $label)
                            <option value="{{ $campo }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-apoyo">Periodo de registro</span>
                    <select
                        wire:model.live="filtroPeriodo"
                        class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-xs font-bold text-input-texto outline-none focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                    >
                        <option value="">Cualquier fecha</option>
                        <option value="HOY">Hoy</option>
                        <option value="7_DIAS">Últimos 7 días</option>
                        <option value="30_DIAS">Últimos 30 días</option>
                        <option value="90_DIAS">Últimos 90 días</option>
                    </select>
                </label>

                <label>
                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-apoyo">Orden</span>
                    <select
                        wire:model.live="orden"
                        class="w-full rounded-xl border border-input-borde bg-input-bg px-3 py-2.5 text-xs font-bold text-input-texto outline-none focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                    >
                        <option value="RECIENTES">Más recientes</option>
                        <option value="ANTIGUOS">Más antiguos</option>
                        <option value="NOMBRE">Nombre A–Z</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 border-t border-borde bg-fondo-panel/40 px-4 py-3 sm:px-5">
            @if(!$hayFiltros)
                <span class="text-[10px] font-semibold text-apoyo">Sin filtros adicionales.</span>
            @else
                @if(trim($search) !== '')
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-borde bg-fondo-card px-3 py-1.5 text-[10px] font-bold text-titulo">
                        <i class="ph-bold ph-magnifying-glass text-boton-acento"></i>
                        {{ \Illuminate\Support\Str::limit($search, 26) }}
                    </span>
                @endif

                @if($filtroEstado !== '')
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-borde bg-fondo-card px-3 py-1.5 text-[10px] font-bold text-titulo">
                        Estado: {{ $estadoLabel($filtroEstado) }}
                        <button type="button" wire:click="limpiarFiltro('estado')" class="text-apoyo hover:text-boton-acento">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </span>
                @endif

                @if($filtroAlergias !== '')
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-borde bg-fondo-card px-3 py-1.5 text-[10px] font-bold text-titulo">
                        Alergias: {{ $filtroAlergias === 'CON_DATO' ? 'Con información' : 'Sin información' }}
                        <button type="button" wire:click="limpiarFiltro('alergias')" class="text-apoyo hover:text-boton-acento">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </span>
                @endif

                @if($filtroAntecedente !== '')
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-borde bg-fondo-card px-3 py-1.5 text-[10px] font-bold text-titulo">
                        {{ $antecedentesDisponibles[$filtroAntecedente] ?? $filtroAntecedente }}
                        <button type="button" wire:click="limpiarFiltro('antecedente')" class="text-apoyo hover:text-boton-acento">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </span>
                @endif

                @if($filtroPeriodo !== '')
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-borde bg-fondo-card px-3 py-1.5 text-[10px] font-bold text-titulo">
                        Periodo:
                        {{ match($filtroPeriodo) {
                            'HOY' => 'Hoy',
                            '7_DIAS' => '7 días',
                            '30_DIAS' => '30 días',
                            '90_DIAS' => '90 días',
                            default => $filtroPeriodo
                        } }}
                        <button type="button" wire:click="limpiarFiltro('periodo')" class="text-apoyo hover:text-boton-acento">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </span>
                @endif

                <button
                    type="button"
                    wire:click="limpiarFiltros"
                    class="ml-auto inline-flex items-center gap-1.5 text-[10px] font-black text-boton-acento hover:underline"
                >
                    <i class="ph-bold ph-trash"></i>
                    Limpiar filtros
                </button>
            @endif
        </div>
    </section>

    {{-- ============================================================
         MASTER / DETAIL CON TRANSICIÓN
    ============================================================ --}}
    <div
        class="grid min-w-0 gap-4 transition-[grid-template-columns] duration-500 ease-out"
        x-bind:class="previewId
            ? 'xl:grid-cols-[minmax(0,1fr)_420px]'
            : 'xl:grid-cols-[minmax(0,1fr)_0px]'"
    >
        {{-- TABLA --}}
        <section class="min-w-0 overflow-hidden rounded-[24px] border border-borde bg-fondo-card shadow-card">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-borde px-4 py-3 sm:px-5">
                <div>
                    <h2 class="text-xs font-black uppercase tracking-wider text-titulo">Registros de valoración</h2>
                    <p class="mt-0.5 text-[10px] font-semibold text-apoyo">
                        {{ $valoraciones->total() }} registro{{ $valoraciones->total() === 1 ? '' : 's' }}
                        @if($seleccionado)
                            · Vista clínica abierta
                        @endif
                    </p>
                </div>

                <div
                    wire:loading.flex
                    wire:target="search,filtroEstado,filtroAlergias,filtroAntecedente,filtroPeriodo,orden,seleccionarValoracion,cerrarVistaPrevia"
                    class="items-center gap-2 text-[10px] font-bold text-boton-acento"
                >
                    <i class="ph-bold ph-spinner animate-spin"></i>
                    Actualizando
                </div>
            </div>

            @if($valoraciones->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left {{ $seleccionado ? 'min-w-[650px]' : 'min-w-[980px]' }}">
                        <thead class="border-b border-tabla-headerBorde bg-tabla-header text-[9px] font-black uppercase tracking-widest text-tabla-headerTexto">
                            <tr>
                                <th class="px-4 py-3 sm:px-5">Residente</th>
                                <th class="px-4 py-3 sm:px-5">Registro</th>
                                <th class="px-4 py-3 sm:px-5">Estado</th>

                                @if(!$seleccionado)
                                    <th class="px-4 py-3 sm:px-5">Responsable</th>
                                    <th class="px-4 py-3 sm:px-5">Síntesis</th>
                                @endif

                                <th class="px-4 py-3 text-right sm:px-5">Acción</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-tabla-rowBorde">
                            @foreach($valoraciones as $val)
                                @php
                                    $adulto = $val->adultoMayor;

                                    $nombreCompleto = $adulto
                                        ? trim(
                                            ($adulto->nombres ?? '') . ' ' .
                                            ($adulto->ap_paterno ?? '') . ' ' .
                                            ($adulto->ap_materno ?? '')
                                        )
                                        : 'Residente no disponible';

                                    $iniciales = $adulto
                                        ? mb_strtoupper(
                                            mb_substr($adulto->nombres ?? 'R', 0, 1) .
                                            mb_substr($adulto->ap_paterno ?? '', 0, 1)
                                        )
                                        : 'R';

                                    $registrador = $val->registrador;
                                    $nombreRegistrador = $registrador
                                        ? trim(
                                            ($registrador->nombres ?? $registrador->name ?? '') . ' ' .
                                            ($registrador->ap_paterno ?? '')
                                        )
                                        : 'No identificado';

                                    $estaSeleccionado = (string) $seleccionadoId === (string) $val->cod_ficha_medica;
                                @endphp

                                <tr
                                    wire:key="valoracion-{{ $val->cod_ficha_medica }}"
                                    wire:click="seleccionarValoracion('{{ $val->cod_ficha_medica }}')"
                                    class="group cursor-pointer transition-all duration-200
                                        {{ $estaSeleccionado
                                            ? 'bg-fondo-panelFuerte'
                                            : 'hover:bg-tabla-rowHover' }}"
                                >
                                    <td class="px-4 py-4 align-top sm:px-5">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-black transition duration-200
                                                    {{ $estaSeleccionado
                                                        ? 'bg-boton-acento text-boton-acentoTexto shadow-sm'
                                                        : 'bg-kpi-residentesBg text-kpi-residentes' }}"
                                            >
                                                {{ $iniciales }}
                                            </div>

                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <p class="max-w-[220px] truncate text-sm font-black text-titulo">
                                                        {{ $nombreCompleto }}
                                                    </p>

                                                    @if($estaSeleccionado)
                                                        <i class="ph-fill ph-caret-right text-boton-acento"></i>
                                                    @endif
                                                </div>

                                                <p class="mt-1 text-[10px] font-semibold text-apoyo">
                                                    {{ $adulto?->ci ? 'CI '.$adulto->ci : 'Expediente clínico' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 align-top sm:px-5">
                                        <p class="text-xs font-black text-titulo">
                                            {{ $val->created_at?->format('d/m/Y') ?? 'S/D' }}
                                        </p>
                                        <p class="mt-1 text-[9px] font-semibold text-meta">
                                            {{ $val->created_at?->format('H:i') ?? '' }}
                                        </p>
                                    </td>

                                    <td class="px-4 py-4 align-top sm:px-5">
                                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $estadoClase($val->estado) }}">
                                            <i class="ph-bold {{ strtoupper((string) $val->estado) === 'ACTIVO' ? 'ph-check-circle' : 'ph-file-dashed' }}"></i>
                                            {{ $estadoLabel($val->estado) }}
                                        </span>
                                    </td>

                                    @if(!$seleccionado)
                                        <td class="px-4 py-4 align-top sm:px-5">
                                            <p class="max-w-[180px] truncate text-[10px] font-black text-titulo">
                                                {{ $nombreRegistrador }}
                                            </p>
                                            <p class="mt-1 text-[9px] font-semibold text-apoyo">Usuario registrador</p>
                                        </td>

                                        <td class="px-4 py-4 align-top sm:px-5">
                                            <p class="max-w-[340px] line-clamp-2 text-[10px] font-semibold leading-relaxed text-apoyo">
                                                {{ $val->observacion_medica ?: 'Sin síntesis médica registrada.' }}
                                            </p>

                                            @if($val->alergias)
                                                <span class="mt-2 inline-flex items-center gap-1 rounded-md border border-estado-advertenciaBorde bg-estado-advertenciaBg px-2 py-0.5 text-[9px] font-black text-estado-advertencia">
                                                    <i class="ph-bold ph-warning-octagon"></i>
                                                    Dato de alergias
                                                </span>
                                            @endif
                                        </td>
                                    @endif

                                    <td class="px-4 py-4 align-top sm:px-5" wire:click.stop>
                                        <div class="flex justify-end">
                                            @if(strtoupper((string) $val->estado) === 'BORRADOR')
                                                @can('ficha_medica.editar')
                                                    <button
                                                        type="button"
                                                        wire:click="abrirEditar('{{ $val->cod_ficha_medica }}')"
                                                        class="rm-btn-primary h-9 px-3"
                                                    >
                                                        <i class="ph-bold ph-pencil-simple"></i>
                                                        Continuar
                                                    </button>
                                                @endcan
                                            @else
                                                <button
                                                    type="button"
                                                    wire:click="seleccionarValoracion('{{ $val->cod_ficha_medica }}')"
                                                    class="rm-btn-secondary h-9 px-3"
                                                >
                                                    <i class="ph-bold {{ $estaSeleccionado ? 'ph-check' : 'ph-eye' }}"></i>
                                                    {{ $estaSeleccionado ? 'Seleccionado' : 'Revisar' }}
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($valoraciones->hasPages())
                    <div class="border-t border-borde bg-fondo-panel/50 px-4 py-3 sm:px-5">
                        {{ $valoraciones->links() }}
                    </div>
                @endif
            @else
                <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-fondo-panel text-apoyo">
                        <i class="ph-bold ph-clipboard-text text-3xl"></i>
                    </div>

                    <h3 class="mt-4 text-base font-black text-titulo">No hay valoraciones para mostrar</h3>

                    <p class="mt-1 max-w-md text-sm font-semibold text-apoyo">
                        {{ $hayFiltros
                            ? 'No se encontraron registros con los criterios actuales.'
                            : 'Aún no existen valoraciones médicas de admisión registradas.' }}
                    </p>

                    @if($hayFiltros)
                        <button type="button" wire:click="limpiarFiltros" class="rm-btn-secondary mt-4">
                            <i class="ph-bold ph-trash"></i>
                            Limpiar filtros
                        </button>
                    @endif
                </div>
            @endif
        </section>

        {{-- ========================================================
             DETALLE LATERAL COMPLETO
        ======================================================== --}}
        <aside
            x-show="previewId"
            x-cloak
            x-transition:enter="transition duration-350 ease-out"
            x-transition:enter-start="translate-x-8 opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition duration-200 ease-in"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-8 opacity-0"
            class="min-w-0 overflow-hidden rounded-[24px] border border-borde bg-fondo-card shadow-panel"
        >
            <div class="flex items-start justify-between gap-3 border-b border-borde bg-fondo-panelFuerte p-4">
                <div class="flex min-w-0 items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-kpi-residentesBg text-xs font-black text-kpi-residentes">
                        {{ $previewIniciales }}
                    </div>

                    <div class="min-w-0">
                        <p class="text-[9px] font-black uppercase tracking-wider text-boton-acento">Valoración seleccionada</p>
                        <h3 class="mt-0.5 truncate text-sm font-black text-titulo">{{ $previewNombre ?: 'Cargando...' }}</h3>
                        <p class="mt-1 text-[10px] font-semibold text-apoyo">
                            {{ $previewAdulto?->ci ? 'CI '.$previewAdulto->ci : 'Expediente clínico' }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="cerrarVistaPrevia"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-borde bg-fondo-card text-apoyo transition hover:border-estado-peligroBorde hover:bg-estado-peligroBg hover:text-estado-peligro active:scale-90"
                    title="Cerrar detalle"
                >
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>

            @if($seleccionado)
                <div class="border-b border-borde px-3 pt-2">
                    <div class="grid grid-cols-3 gap-1">
                        @foreach([
                            'resumen' => ['Resumen', 'ph-clipboard-text'],
                            'antecedentes' => ['Antecedentes', 'ph-files'],
                            'contexto' => ['Contexto', 'ph-user-circle'],
                        ] as $tab => [$label, $icono])
                            <button
                                type="button"
                                x-on:click="previewTab = '{{ $tab }}'"
                                class="relative rounded-t-xl px-2 py-2.5 text-[9px] font-black uppercase tracking-wider transition"
                                x-bind:class="previewTab === '{{ $tab }}'
                                    ? 'bg-fondo-panel text-boton-acento'
                                    : 'text-apoyo hover:bg-fondo-hover hover:text-titulo'"
                            >
                                <i class="ph-bold {{ $icono }} mr-1"></i>
                                {{ $label }}

                                <span
                                    x-show="previewTab === '{{ $tab }}'"
                                    class="absolute inset-x-2 bottom-0 h-0.5 rounded-full bg-boton-acento"
                                ></span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="max-h-[calc(100vh-250px)] overflow-y-auto p-4">
                    {{-- RESUMEN --}}
                    <section
                        x-show="previewTab === 'resumen'"
                        x-transition.opacity.duration.180ms
                        class="space-y-4"
                    >
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $estadoClase($seleccionado->estado) }}">
                                <i class="ph-bold {{ strtoupper((string) $seleccionado->estado) === 'ACTIVO' ? 'ph-check-circle' : 'ph-file-dashed' }}"></i>
                                {{ $estadoLabel($seleccionado->estado) }}
                            </span>

                            <span class="text-[9px] font-semibold text-meta">
                                {{ $seleccionado->created_at?->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                                <p class="text-[8px] font-black uppercase tracking-wider text-apoyo">Expediente</p>
                                <p class="mt-1 truncate text-[10px] font-black text-titulo">{{ $seleccionado->cod_ficha_medica }}</p>
                            </div>

                            <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                                <p class="text-[8px] font-black uppercase tracking-wider text-apoyo">Responsable</p>
                                <p class="mt-1 truncate text-[10px] font-black text-titulo">{{ $previewRegistradorNombre }}</p>
                            </div>
                        </div>

                        @if($seleccionadoAlergia)
                            <div class="rounded-xl border p-3
                                {{ $seleccionadoAlergia['tipo'] === 'REGISTRADA'
                                    ? 'border-estado-peligroBorde bg-estado-peligroBg'
                                    : ($seleccionadoAlergia['tipo'] === 'SIN_CONOCIDAS'
                                        ? 'border-estado-exitoBorde bg-estado-exitoBg'
                                        : 'border-estado-neutralBorde bg-estado-neutralBg') }}"
                            >
                                <div class="flex items-start gap-2">
                                    <i class="ph-bold ph-warning-octagon mt-0.5
                                        {{ $seleccionadoAlergia['tipo'] === 'REGISTRADA'
                                            ? 'text-estado-peligro'
                                            : 'text-apoyo' }}"
                                    ></i>

                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-wider text-titulo">
                                            {{ $seleccionadoAlergia['label'] }}
                                        </p>

                                        @if($seleccionadoAlergia['texto'])
                                            <p class="mt-1 text-[10px] font-semibold leading-relaxed text-parrafo">
                                                {{ $seleccionadoAlergia['texto'] }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="grid gap-2">
                            @foreach([
                                'Condición general' => ['ph-heartbeat', 'modulo-salud'],
                                'Estado neurológico' => ['ph-brain', 'modulo-cognitivoTexto'],
                                'Dependencia sugerida' => ['ph-person-simple-walk', 'estado-info'],
                                'Resultado de valoración' => ['ph-check-square-offset', 'boton-acento'],
                            ] as $titulo => [$icono, $color])
                                @if($sintesisMap->has($titulo))
                                    <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                                        <div class="flex items-center gap-2">
                                            <i class="ph-bold {{ $icono }} text-{{ $color }}"></i>
                                            <p class="text-[8px] font-black uppercase tracking-wider text-apoyo">{{ $titulo }}</p>
                                        </div>

                                        <p class="mt-1.5 text-[10px] font-black leading-relaxed text-titulo">
                                            {{ str_replace('_', ' ', $sintesisMap->get($titulo)) }}
                                        </p>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @foreach([
                            'Diagnósticos referidos' => 'Diagnósticos referidos',
                            'Medicación actual' => 'Medicación actual',
                            'Fundamento' => 'Fundamento clínico',
                            'Recomendación' => 'Recomendación médica',
                        ] as $clave => $label)
                            @if($sintesisMap->has($clave))
                                <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                                    <p class="text-[8px] font-black uppercase tracking-wider text-apoyo">{{ $label }}</p>
                                    <p class="mt-1.5 whitespace-pre-line text-[10px] font-semibold leading-relaxed text-parrafo">
                                        {{ $sintesisMap->get($clave) }}
                                    </p>
                                </div>
                            @endif
                        @endforeach

                        @if(strtoupper((string) $seleccionado->estado) === 'BORRADOR')
                            @can('ficha_medica.editar')
                                <button
                                    type="button"
                                    wire:click="abrirEditar('{{ $seleccionado->cod_ficha_medica }}')"
                                    class="rm-btn-primary h-10 w-full"
                                >
                                    <i class="ph-bold ph-pencil-simple"></i>
                                    Continuar borrador
                                </button>
                            @endcan
                        @endif
                    </section>

                    {{-- ANTECEDENTES --}}
                    <section
                        x-show="previewTab === 'antecedentes'"
                        x-cloak
                        x-transition.opacity.duration.180ms
                        class="space-y-4"
                    >
                        <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                            <p class="text-[9px] font-black uppercase tracking-wider text-titulo">Antecedentes estructurados</p>

                            @if(count($seleccionadoAntecedentes) > 0)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($seleccionadoAntecedentes as $antecedente)
                                        <span class="rounded-full border border-estado-infoBorde bg-estado-infoBg px-2.5 py-1 text-[9px] font-black text-estado-info">
                                            {{ $antecedente }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-[10px] font-semibold text-apoyo">
                                    No hay antecedentes estructurados marcados.
                                </p>
                            @endif
                        </div>

                        @foreach([
                            'restricciones_alimentarias' => 'Restricciones alimentarias',
                            'hospitalizaciones' => 'Hospitalizaciones',
                            'cirugias' => 'Cirugías',
                        ] as $campo => $label)
                            <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                                <p class="text-[8px] font-black uppercase tracking-wider text-apoyo">{{ $label }}</p>
                                <p class="mt-1.5 whitespace-pre-line text-[10px] font-semibold leading-relaxed text-parrafo">
                                    {{ $seleccionado->{$campo} ?: 'Sin información registrada.' }}
                                </p>
                            </div>
                        @endforeach

                        @if($sintesisMap->has('Antecedentes adicionales') || $sintesisMap->has('Antecedentes'))
                            <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                                <p class="text-[8px] font-black uppercase tracking-wider text-apoyo">Antecedentes adicionales</p>
                                <p class="mt-1.5 whitespace-pre-line text-[10px] font-semibold leading-relaxed text-parrafo">
                                    {{ $sintesisMap->get('Antecedentes adicionales') ?? $sintesisMap->get('Antecedentes') }}
                                </p>
                            </div>
                        @endif
                    </section>

                    {{-- CONTEXTO --}}
                    <section
                        x-show="previewTab === 'contexto'"
                        x-cloak
                        x-transition.opacity.duration.180ms
                        class="space-y-4"
                    >
                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                                <p class="text-[8px] font-black uppercase tracking-wider text-apoyo">Edad</p>
                                <p class="mt-1 text-[10px] font-black text-titulo">{{ $previewAdulto?->edad_texto ?? 'No registrada' }}</p>
                            </div>

                            <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                                <p class="text-[8px] font-black uppercase tracking-wider text-apoyo">Género</p>
                                <p class="mt-1 text-[10px] font-black text-titulo">{{ $previewAdulto?->genero ?: 'No registrado' }}</p>
                            </div>

                            <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                                <p class="text-[8px] font-black uppercase tracking-wider text-apoyo">Grupo sanguíneo</p>
                                <p class="mt-1 text-[10px] font-black text-titulo">
                                    {{ trim(($previewAdulto?->grupo_sanguineo ?? '') . ' ' . ($previewAdulto?->factor_rh ?? '')) ?: 'No registrado' }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                                <p class="text-[8px] font-black uppercase tracking-wider text-apoyo">Estado institucional</p>
                                <p class="mt-1 text-[10px] font-black text-titulo">{{ $previewAdulto?->estado_humano ?? 'No registrado' }}</p>
                            </div>
                        </div>

                        @foreach([
                            'seguro_salud' => 'Seguro de salud',
                            'motivo_ingreso' => 'Motivo de ingreso',
                            'procedencia_ingreso' => 'Procedencia',
                        ] as $campo => $label)
                            <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                                <p class="text-[8px] font-black uppercase tracking-wider text-apoyo">{{ $label }}</p>
                                <p class="mt-1.5 text-[10px] font-semibold leading-relaxed text-parrafo">
                                    {{ $previewAdulto?->{$campo} ?: 'No registrado' }}
                                </p>
                            </div>
                        @endforeach

                        <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                            <p class="text-[8px] font-black uppercase tracking-wider text-apoyo">Ubicación actual</p>
                            <p class="mt-1.5 text-[10px] font-semibold text-parrafo">
                                {{ $previewAdulto?->ubicacion_texto ?? 'Sin ubicación asignada' }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                            <p class="text-[8px] font-black uppercase tracking-wider text-apoyo">Registro de valoración</p>
                            <p class="mt-1.5 text-[10px] font-semibold text-parrafo">
                                Creado {{ $seleccionado->created_at?->format('d/m/Y H:i') ?? 'S/D' }}
                                @if($seleccionado->updated_at && !$seleccionado->updated_at->equalTo($seleccionado->created_at))
                                    · Actualizado {{ $seleccionado->updated_at->format('d/m/Y H:i') }}
                                @endif
                            </p>
                        </div>
                    </section>
                </div>
            @else
                <div class="p-6 text-center">
                    <i class="ph-bold ph-spinner animate-spin text-xl text-boton-acento"></i>
                    <p class="mt-2 text-[10px] font-semibold text-apoyo">Cargando valoración...</p>
                </div>
            @endif
        </aside>
    </div>

    {{-- ============================================================
         MODAL DE CAPTURA / CONTINUAR BORRADOR
    ============================================================ --}}
    @if($modalForm)
        <div
            class="fixed inset-0 z-[2147483646] flex items-center justify-center overflow-y-auto bg-modal-overlay p-3 sm:p-5"
            x-data="{ paso: 1 }"
            x-on:keydown.escape.window="$wire.cerrarModalFormulario()"
        >
            <div
                class="relative flex max-h-[94vh] w-full max-w-4xl flex-col overflow-hidden rounded-[28px] border border-modal-borde bg-modal-bg shadow-modal"
                x-transition:enter="transition duration-250 ease-out"
                x-transition:enter-start="translate-y-4 scale-[0.985] opacity-0"
                x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            >
                <header class="shrink-0 border-b border-modal-headerBorde bg-fondo-panelFuerte px-5 py-4 sm:px-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-modulo-saludFondo text-modulo-salud">
                                <i class="ph-fill ph-stethoscope text-lg"></i>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[9px] font-black uppercase tracking-[0.18em] text-boton-acento">
                                    {{ $editandoId ? 'Continuar borrador' : 'Valoración médica de admisión' }}
                                </p>

                                <h2 class="mt-0.5 text-lg font-black text-modal-titulo">
                                    {{ $editandoId ? 'Completar registro clínico' : 'Nuevo registro clínico' }}
                                </h2>

                                <p class="mt-1 text-[10px] font-semibold text-apoyo">
                                    Puede guardar el trabajo como borrador en cualquier momento.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="cerrarModalFormulario"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-borde bg-fondo-card text-apoyo transition hover:border-estado-peligroBorde hover:bg-estado-peligroBg hover:text-estado-peligro active:scale-90"
                        >
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>

                    <div class="mt-4">
                        <div class="relative mb-3 h-1 overflow-hidden rounded-full bg-fondo-panel">
                            <div
                                class="absolute inset-y-0 left-0 rounded-full bg-boton-acento transition-all duration-300"
                                x-bind:style="`width:${((paso - 1) / 3) * 100}%`"
                            ></div>
                        </div>

                        <div class="grid grid-cols-4 gap-2">
                            @foreach([
                                1 => ['Contexto', 'ph-user-circle'],
                                2 => ['Antecedentes', 'ph-files'],
                                3 => ['Evaluación', 'ph-stethoscope'],
                                4 => ['Conclusión', 'ph-check-square-offset'],
                            ] as $numero => [$label, $icono])
                                <button
                                    type="button"
                                    x-on:click="if ({{ $numero }} <= paso) paso = {{ $numero }}"
                                    class="flex min-w-0 items-center justify-center gap-2 rounded-xl border px-2.5 py-2 transition"
                                    x-bind:class="paso === {{ $numero }}
                                        ? 'border-boton-acento bg-boton-acento/10'
                                        : ({{ $numero }} < paso
                                            ? 'border-estado-exitoBorde bg-estado-exitoBg'
                                            : 'border-borde bg-fondo-card')"
                                >
                                    <span
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-black transition"
                                        x-bind:class="paso === {{ $numero }}
                                            ? 'bg-boton-acento text-boton-acentoTexto'
                                            : ({{ $numero }} < paso
                                                ? 'bg-estado-exito text-inverso'
                                                : 'bg-fondo-panel text-apoyo')"
                                    >
                                        <i
                                            x-show="{{ $numero }} < paso"
                                            class="ph-bold ph-check"
                                        ></i>
                                        <span x-show="{{ $numero }} >= paso">{{ $numero }}</span>
                                    </span>

                                    <span
                                        class="hidden truncate text-[9px] font-black uppercase tracking-wider sm:block"
                                        x-bind:class="paso === {{ $numero }} ? 'text-boton-acento' : 'text-apoyo'"
                                    >
                                        {{ $label }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </header>

                <form class="min-h-0 flex-1 overflow-y-auto">
                    <div class="p-5 sm:p-6">
                        @error('general')
                            <div class="mb-4 flex items-start gap-3 rounded-xl border border-estado-peligroBorde bg-estado-peligroBg p-3">
                                <i class="ph-fill ph-warning-octagon mt-0.5 text-lg text-estado-peligro"></i>
                                <p class="text-[10px] font-bold leading-relaxed text-estado-peligro">{{ $message }}</p>
                            </div>
                        @enderror

                        {{-- PASO 1 --}}
                        <section
                            x-show="paso === 1"
                            x-transition:enter="transition duration-200 ease-out"
                            x-transition:enter-start="translate-x-3 opacity-0"
                            x-transition:enter-end="translate-x-0 opacity-100"
                            class="space-y-4"
                        >
                            <div>
                                <h3 class="text-sm font-black text-titulo">Paciente y contexto</h3>
                                <p class="mt-1 text-[10px] font-semibold text-apoyo">Identifique al residente y confirme el contexto institucional antes de continuar.</p>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-[1fr_280px]">
                                <div class="space-y-4">
                                    <label class="block">
                                        <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">
                                            Residente <span class="text-estado-peligro">*</span>
                                        </span>

                                        @if($editandoId)
                                            <div class="rounded-xl border border-borde bg-input-disabled px-3.5 py-2.5 text-xs font-black text-input-texto">
                                                {{ $adultoFormSeleccionado?->nombre_completo ?? $codAm }}
                                            </div>
                                        @else
                                            <select
                                                wire:model.live="codAm"
                                                class="w-full rounded-xl border bg-input-bg px-3.5 py-2.5 text-xs font-bold text-input-texto outline-none transition focus:ring-2
                                                    @error('codAm')
                                                        border-estado-peligroBorde focus:border-estado-peligroBorde focus:ring-estado-peligro/15
                                                    @else
                                                        border-input-borde focus:border-input-bordeFocus focus:ring-input-ringFocus
                                                    @enderror"
                                            >
                                                <option value="">Seleccione un residente...</option>
                                                @foreach($adultos as $adulto)
                                                    <option value="{{ $adulto->cod_am }}">
                                                        {{ trim(
                                                            $adulto->nombres . ' ' .
                                                            $adulto->ap_paterno . ' ' .
                                                            ($adulto->ap_materno ?? '')
                                                        ) }}
                                                        @if($adulto->ci)
                                                            · CI {{ $adulto->ci }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif

                                        @error('codAm')
                                            <p class="mt-1 text-[9px] font-bold text-estado-peligro">{{ $message }}</p>
                                        @enderror
                                    </label>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <label>
                                            <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">
                                                Fecha <span class="text-estado-peligro">*</span>
                                            </span>
                                            <input
                                                type="date"
                                                wire:model.blur="fecha"
                                                class="w-full rounded-xl border border-input-borde bg-input-bg px-3.5 py-2.5 text-xs font-bold text-input-texto outline-none focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                                            >
                                            @error('fecha')
                                                <p class="mt-1 text-[9px] font-bold text-estado-peligro">{{ $message }}</p>
                                            @enderror
                                        </label>

                                        <label>
                                            <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">
                                                Hora <span class="text-estado-peligro">*</span>
                                            </span>
                                            <input
                                                type="time"
                                                wire:model.blur="hora"
                                                class="w-full rounded-xl border border-input-borde bg-input-bg px-3.5 py-2.5 text-xs font-bold text-input-texto outline-none focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                                            >
                                            @error('hora')
                                                <p class="mt-1 text-[9px] font-bold text-estado-peligro">{{ $message }}</p>
                                            @enderror
                                        </label>
                                    </div>
                                </div>

                                <aside class="rounded-2xl border border-borde bg-fondo-panel p-4">
                                    @if($adultoFormSeleccionado)
                                        <p class="text-[9px] font-black uppercase tracking-wider text-boton-acento">Contexto institucional</p>

                                        <h4 class="mt-2 text-sm font-black text-titulo">
                                            {{ $adultoFormSeleccionado->nombre_completo }}
                                        </h4>

                                        <div class="mt-3 space-y-2 text-[10px] font-semibold text-apoyo">
                                            <p>
                                                <i class="ph-bold ph-identification-card mr-1"></i>
                                                {{ $adultoFormSeleccionado->ci ?: 'CI no registrado' }}
                                            </p>
                                            <p>
                                                <i class="ph-bold ph-cake mr-1"></i>
                                                {{ $adultoFormSeleccionado->edad_texto }}
                                            </p>
                                            <p>
                                                <i class="ph-bold ph-bed mr-1"></i>
                                                {{ $adultoFormSeleccionado->ubicacion_texto }}
                                            </p>
                                            <p>
                                                <i class="ph-bold ph-activity mr-1"></i>
                                                {{ $adultoFormSeleccionado->estado_humano }}
                                            </p>
                                        </div>

                                        @if($adultoFormSeleccionado->alergias)
                                            <div class="mt-3 rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3">
                                                <p class="text-[8px] font-black uppercase tracking-wider text-estado-advertencia">
                                                    Alergias del expediente
                                                </p>
                                                <p class="mt-1 text-[9px] font-semibold text-titulo">
                                                    {{ $adultoFormSeleccionado->alergias }}
                                                </p>
                                            </div>
                                        @endif
                                    @else
                                        <div class="flex min-h-[150px] flex-col items-center justify-center text-center">
                                            <i class="ph-bold ph-user-focus text-2xl text-apoyo"></i>
                                            <p class="mt-2 text-[10px] font-semibold text-apoyo">
                                                Seleccione un residente para ver su contexto.
                                            </p>
                                        </div>
                                    @endif
                                </aside>
                            </div>
                        </section>

                        {{-- PASO 2 --}}
                        <section
                            x-show="paso === 2"
                            x-cloak
                            x-transition:enter="transition duration-200 ease-out"
                            x-transition:enter-start="translate-x-3 opacity-0"
                            x-transition:enter-end="translate-x-0 opacity-100"
                            class="space-y-5"
                        >
                            <div>
                                <h3 class="text-sm font-black text-titulo">Antecedentes clínicos</h3>
                                <p class="mt-1 text-[10px] font-semibold text-apoyo">
                                    Marque los antecedentes estructurados existentes y complemente solo cuando corresponda.
                                </p>
                            </div>

                            <div>
                                <p class="mb-2 text-[9px] font-black uppercase tracking-wider text-label">Antecedentes conocidos</p>

                                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach([
                                        'hipertension' => ['Hipertensión', 'ph-heartbeat'],
                                        'diabetes' => ['Diabetes', 'ph-drop'],
                                        'problemasCardiacos' => ['Problemas cardíacos', 'ph-heart'],
                                        'acv' => ['ACV', 'ph-brain'],
                                        'parkinson' => ['Parkinson', 'ph-person-simple-walk'],
                                        'epilepsia' => ['Epilepsia', 'ph-wave-sine'],
                                        'alzheimerDiagnosticado' => ['Alzheimer diagnosticado', 'ph-brain'],
                                        'depresion' => ['Depresión', 'ph-cloud-rain'],
                                        'ansiedad' => ['Ansiedad', 'ph-pulse'],
                                        'problemasSueno' => ['Problemas del sueño', 'ph-moon-stars'],
                                        'problemasVisuales' => ['Problemas visuales', 'ph-eye'],
                                        'problemasAuditivos' => ['Problemas auditivos', 'ph-ear'],
                                        'dolorCronico' => ['Dolor crónico', 'ph-thermometer'],
                                    ] as $propiedad => [$label, $icono])
                                        <label class="group flex cursor-pointer items-center gap-3 rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 transition hover:border-borde-fuerte hover:bg-fondo-hover">
                                            <input
                                                type="checkbox"
                                                wire:model.live="{{ $propiedad }}"
                                                class="h-4 w-4 rounded border-input-borde text-boton-acento focus:ring-boton-acento"
                                            >
                                            <i class="ph-bold {{ $icono }} text-apoyo transition group-hover:text-boton-acento"></i>
                                            <span class="text-[10px] font-bold text-titulo">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <label>
                                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">Diagnósticos referidos</span>
                                    <textarea
                                        wire:model.blur="diagnosticosReferidos"
                                        rows="3"
                                        maxlength="1500"
                                        placeholder="Diagnósticos informados al ingreso..."
                                        class="w-full resize-none rounded-xl border border-input-borde bg-input-bg px-3.5 py-3 text-xs font-semibold text-input-texto outline-none placeholder:text-input-placeholder focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                                    ></textarea>
                                </label>

                                <label>
                                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">Antecedentes adicionales</span>
                                    <textarea
                                        wire:model.blur="antecedentesRelevantes"
                                        rows="3"
                                        maxlength="1500"
                                        placeholder="Información relevante no cubierta por los campos estructurados..."
                                        class="w-full resize-none rounded-xl border border-input-borde bg-input-bg px-3.5 py-3 text-xs font-semibold text-input-texto outline-none placeholder:text-input-placeholder focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                                    ></textarea>
                                </label>

                                <label>
                                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">Medicación actual referida</span>
                                    <textarea
                                        wire:model.blur="medicacionActualResumen"
                                        rows="3"
                                        maxlength="1500"
                                        placeholder="Medicamentos referidos al ingreso..."
                                        class="w-full resize-none rounded-xl border border-input-borde bg-input-bg px-3.5 py-3 text-xs font-semibold text-input-texto outline-none placeholder:text-input-placeholder focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                                    ></textarea>
                                </label>

                                <label>
                                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">Alergias referidas</span>
                                    <textarea
                                        wire:model.blur="alergiasReferidas"
                                        rows="3"
                                        maxlength="1000"
                                        placeholder="Alergias conocidas o constancia de ausencia..."
                                        class="w-full resize-none rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg/40 px-3.5 py-3 text-xs font-semibold text-input-texto outline-none placeholder:text-input-placeholder focus:border-estado-advertenciaBorde focus:ring-2 focus:ring-estado-advertencia/10"
                                    ></textarea>
                                </label>

                                <label>
                                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">Restricciones alimentarias</span>
                                    <textarea
                                        wire:model.blur="restriccionesAlimentarias"
                                        rows="3"
                                        maxlength="1500"
                                        placeholder="Dieta, restricciones o indicaciones alimentarias..."
                                        class="w-full resize-none rounded-xl border border-input-borde bg-input-bg px-3.5 py-3 text-xs font-semibold text-input-texto outline-none placeholder:text-input-placeholder focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                                    ></textarea>
                                </label>

                                <label>
                                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">Hospitalizaciones</span>
                                    <textarea
                                        wire:model.blur="hospitalizaciones"
                                        rows="3"
                                        maxlength="2000"
                                        placeholder="Hospitalizaciones relevantes..."
                                        class="w-full resize-none rounded-xl border border-input-borde bg-input-bg px-3.5 py-3 text-xs font-semibold text-input-texto outline-none placeholder:text-input-placeholder focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                                    ></textarea>
                                </label>

                                <label class="sm:col-span-2">
                                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">Cirugías</span>
                                    <textarea
                                        wire:model.blur="cirugias"
                                        rows="2"
                                        maxlength="2000"
                                        placeholder="Cirugías relevantes..."
                                        class="w-full resize-none rounded-xl border border-input-borde bg-input-bg px-3.5 py-3 text-xs font-semibold text-input-texto outline-none placeholder:text-input-placeholder focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                                    ></textarea>
                                </label>
                            </div>
                        </section>

                        {{-- PASO 3 --}}
                        <section
                            x-show="paso === 3"
                            x-cloak
                            x-transition:enter="transition duration-200 ease-out"
                            x-transition:enter-start="translate-x-3 opacity-0"
                            x-transition:enter-end="translate-x-0 opacity-100"
                            class="space-y-4"
                        >
                            <div>
                                <h3 class="text-sm font-black text-titulo">Evaluación clínica</h3>
                                <p class="mt-1 text-[10px] font-semibold text-apoyo">
                                    Registre la apreciación médica general, neurológica y funcional.
                                </p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <label>
                                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">Condición médica general</span>
                                    <select
                                        wire:model.live="condicionMedicaGeneral"
                                        class="w-full rounded-xl border border-input-borde bg-input-bg px-3.5 py-2.5 text-xs font-bold text-input-texto outline-none focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                                    >
                                        <option value="">Seleccione...</option>
                                        <option value="ESTABLE">Estable</option>
                                        <option value="REQUIERE_OBSERVACION">Requiere observación</option>
                                        <option value="DELICADA">Delicada</option>
                                        <option value="NO_APTA">No apta para ingreso</option>
                                    </select>
                                    @error('condicionMedicaGeneral')
                                        <p class="mt-1 text-[9px] font-bold text-estado-peligro">{{ $message }}</p>
                                    @enderror
                                </label>

                                <label>
                                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">Estado neurológico básico</span>
                                    <select
                                        wire:model.live="estadoNeurologicoBasico"
                                        class="w-full rounded-xl border border-input-borde bg-input-bg px-3.5 py-2.5 text-xs font-bold text-input-texto outline-none focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                                    >
                                        <option value="">Seleccione...</option>
                                        <option value="NORMAL">Normal / íntegro</option>
                                        <option value="CONFUSION_LEVE">Confusión leve</option>
                                        <option value="DESORIENTACION">Desorientación</option>
                                        <option value="ALTERADO">Alterado</option>
                                        <option value="NO_EVALUABLE">No evaluable</option>
                                    </select>
                                    @error('estadoNeurologicoBasico')
                                        <p class="mt-1 text-[9px] font-bold text-estado-peligro">{{ $message }}</p>
                                    @enderror
                                </label>

                                <label>
                                    <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">Nivel de dependencia sugerido</span>
                                    <select
                                        wire:model.live="nivelDependenciaSugerido"
                                        class="w-full rounded-xl border border-input-borde bg-input-bg px-3.5 py-2.5 text-xs font-bold text-input-texto outline-none focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                                    >
                                        <option value="">Seleccione...</option>
                                        <option value="INDEPENDIENTE">Independiente</option>
                                        <option value="DEPENDENCIA_LEVE">Dependencia leve</option>
                                        <option value="DEPENDENCIA_MODERADA">Dependencia moderada</option>
                                        <option value="DEPENDENCIA_SEVERA">Dependencia severa</option>
                                        <option value="DEPENDENCIA_TOTAL">Dependencia total</option>
                                        <option value="NO_EVALUABLE">No evaluable</option>
                                    </select>
                                    @error('nivelDependenciaSugerido')
                                        <p class="mt-1 text-[9px] font-bold text-estado-peligro">{{ $message }}</p>
                                    @enderror
                                </label>

                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-borde bg-fondo-panel p-4 transition hover:bg-fondo-hover">
                                    <input
                                        type="checkbox"
                                        wire:model.live="requiereSeguimientoEsp"
                                        class="mt-0.5 h-4 w-4 rounded border-input-borde text-boton-acento focus:ring-boton-acento"
                                    >
                                    <span>
                                        <span class="block text-xs font-black text-titulo">Requiere seguimiento especial</span>
                                        <span class="mt-1 block text-[9px] font-semibold leading-relaxed text-apoyo">
                                            Marque cuando el caso requiera continuidad clínica diferenciada.
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </section>

                        {{-- PASO 4 --}}
                        <section
                            x-show="paso === 4"
                            x-cloak
                            x-transition:enter="transition duration-200 ease-out"
                            x-transition:enter-start="translate-x-3 opacity-0"
                            x-transition:enter-end="translate-x-0 opacity-100"
                            class="space-y-4"
                        >
                            <div>
                                <h3 class="text-sm font-black text-titulo">Conclusión médica</h3>
                                <p class="mt-1 text-[10px] font-semibold text-apoyo">
                                    Emita la recomendación clínica y fundamente la decisión.
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-[0.9fr_1.1fr]">
                                <fieldset>
                                    <legend class="mb-2 text-[9px] font-black uppercase tracking-wider text-label">Resultado / recomendación</legend>

                                    <div class="space-y-2">
                                        @foreach([
                                            'ADMITIDO' => ['Favorable para admisión', 'ph-check-circle'],
                                            'NO_ADMITIDO' => ['No favorable para admisión', 'ph-x-circle'],
                                            'DERIVADO' => ['Requiere derivación', 'ph-arrow-square-out'],
                                            'OBSERVADO' => ['Requiere observación', 'ph-eye'],
                                            'CANCELADO' => ['Cancelado', 'ph-prohibit'],
                                        ] as $valor => [$label, $icono])
                                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-borde bg-fondo-panel px-3 py-2.5 transition hover:bg-fondo-hover">
                                                <input
                                                    type="radio"
                                                    wire:model.live="resultadoAdmision"
                                                    value="{{ $valor }}"
                                                    class="border-input-borde text-boton-acento focus:ring-boton-acento"
                                                >
                                                <i class="ph-bold {{ $icono }} text-boton-acento"></i>
                                                <span class="text-[10px] font-bold text-titulo">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                    @error('resultadoAdmision')
                                        <p class="mt-2 text-[9px] font-bold text-estado-peligro">{{ $message }}</p>
                                    @enderror
                                </fieldset>

                                <div class="space-y-4">
                                    <label>
                                        <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">Fundamento clínico</span>
                                        <textarea
                                            wire:model.blur="motivoDecision"
                                            rows="5"
                                            maxlength="2000"
                                            placeholder="Fundamente clínicamente la recomendación..."
                                            class="w-full resize-none rounded-xl border border-input-borde bg-input-bg px-3.5 py-3 text-xs font-semibold text-input-texto outline-none placeholder:text-input-placeholder focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                                        ></textarea>
                                        @error('motivoDecision')
                                            <p class="mt-1 text-[9px] font-bold text-estado-peligro">{{ $message }}</p>
                                        @enderror
                                    </label>

                                    <label>
                                        <span class="mb-1.5 block text-[9px] font-black uppercase tracking-wider text-label">Recomendaciones médicas</span>
                                        <textarea
                                            wire:model.blur="recomendacionMedica"
                                            rows="4"
                                            maxlength="2000"
                                            placeholder="Seguimiento, controles, derivaciones o cuidados..."
                                            class="w-full resize-none rounded-xl border border-input-borde bg-input-bg px-3.5 py-3 text-xs font-semibold text-input-texto outline-none placeholder:text-input-placeholder focus:border-input-bordeFocus focus:ring-2 focus:ring-input-ringFocus"
                                        ></textarea>
                                    </label>
                                </div>
                            </div>

                            <div class="rounded-xl border border-estado-infoBorde bg-estado-infoBg p-3">
                                <div class="flex items-start gap-2">
                                    <i class="ph-fill ph-info mt-0.5 text-estado-info"></i>
                                    <p class="text-[9px] font-semibold leading-relaxed text-apoyo">
                                        Finalizar exige los campos clínicos obligatorios. Guardar borrador permite conservar el avance sin marcar la valoración como completada.
                                    </p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <footer class="sticky bottom-0 border-t border-modal-footerBorde bg-modal-bg/95 px-5 py-4 backdrop-blur sm:px-6">
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="text-[9px] font-bold text-apoyo">
                                    Paso <span class="font-black text-titulo" x-text="paso"></span> de 4
                                </span>

                                <button
                                    type="button"
                                    wire:click="guardarBorrador"
                                    wire:loading.attr="disabled"
                                    wire:target="guardarBorrador"
                                    class="rm-btn-secondary h-10 px-4"
                                >
                                    <span wire:loading.remove wire:target="guardarBorrador" class="inline-flex items-center gap-2">
                                        <i class="ph-bold ph-floppy-disk"></i>
                                        Guardar borrador
                                    </span>
                                    <span wire:loading.flex wire:target="guardarBorrador" class="items-center gap-2">
                                        <i class="ph-bold ph-spinner animate-spin"></i>
                                        Guardando...
                                    </span>
                                </button>
                            </div>

                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    x-show="paso > 1"
                                    x-on:click="paso--"
                                    class="rm-btn-secondary h-10 px-4"
                                >
                                    <i class="ph-bold ph-arrow-left"></i>
                                    Anterior
                                </button>

                                <button
                                    type="button"
                                    x-show="paso < 4"
                                    x-on:click="$wire.validarPaso(paso).then(ok => { if (ok) paso++ })"
                                    class="rm-btn-primary h-10 px-5"
                                >
                                    Siguiente
                                    <i class="ph-bold ph-arrow-right"></i>
                                </button>

                                <button
                                    type="button"
                                    x-show="paso === 4"
                                    wire:click="finalizarValoracion"
                                    wire:loading.attr="disabled"
                                    wire:target="finalizarValoracion"
                                    class="rm-btn-primary h-10 min-w-[180px] px-5"
                                >
                                    <span wire:loading.remove wire:target="finalizarValoracion" class="inline-flex items-center gap-2">
                                        <i class="ph-bold ph-check-circle"></i>
                                        Finalizar valoración
                                    </span>
                                    <span wire:loading.flex wire:target="finalizarValoracion" class="items-center gap-2">
                                        <i class="ph-bold ph-spinner animate-spin"></i>
                                        Finalizando...
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
