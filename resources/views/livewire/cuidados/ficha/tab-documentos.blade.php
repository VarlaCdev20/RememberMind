@php
    $documentos = $this->documentosPaginados['items'];
    $paginacion = $this->documentosPaginados;
    $metricas = $this->metricasDocumentos;
    $documentoActivo = $this->documentoActivo;
    $tabDetalleDoc = $this->tabDetalleDoc;
    $zoomDoc = $this->zoomDoc;
@endphp

<div class="space-y-5"
     x-data="{
         tabDocActivo: @entangle('tabDetalleDoc'),
         zoomNivel: @entangle('zoomDoc'),
         modalSubir: @entangle('modalSubirDoc'),
         menuAccionDoc: null
     }">

    {{-- ========================================================================= --}}
    {{-- 1. HEADER DEL MÓDULO                                                      --}}
    {{-- ========================================================================= --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[var(--rm-border)] pb-4">
        <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-2xl bg-[#1E3A8A]/10 text-[#1E3A8A] border border-[#1E3A8A]/20 flex items-center justify-center shrink-0">
                <i class="ph-bold ph-files text-2xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-lg font-black text-[var(--rm-text-title)] tracking-tight">
                        Documentación
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-[#1E3A8A]/10 text-[#1E3A8A] border border-[#1E3A8A]/20 uppercase">
                        Expediente
                    </span>
                </div>
                <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                    Gestión y consulta de documentos clínicos, administrativos y personales del residente.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            {{-- Botón Subir Documento (permiso-gated) --}}
            <button type="button"
                    wire:click="abrirModalSubirDoc"
                    class="px-4 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-[#1E3A8A]/90 text-white font-black text-xs transition-all shadow-sm hover:shadow flex items-center gap-2 cursor-pointer active:scale-95">
                <i class="ph-bold ph-upload-simple text-sm"></i>
                <span>+ Subir documento</span>
            </button>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. KPIS SUPERIORES (5 CARDS COMPACTAS SEGÚN GOLDEN REFERENCE)             --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">

        {{-- Card 1: Total de documentos (Azul) --}}
        <div class="rounded-2xl border border-blue-200/80 bg-blue-50/40 p-3.5 flex items-center justify-between gap-3 shadow-2xs hover:shadow-sm transition">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-800/80 block truncate">
                    Total de documentos
                </span>
                <span class="text-2xl font-black text-blue-900 tracking-tight mt-0.5 block">
                    {{ $metricas['total'] }}
                </span>
                <span class="text-[10.5px] font-medium text-blue-700/80 block mt-0.5">
                    Todos los registros
                </span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-blue-100 border border-blue-200 text-blue-700 flex items-center justify-center shrink-0">
                <i class="ph-bold ph-folder text-xl"></i>
            </div>
        </div>

        {{-- Card 2: Clínicos (Verde) --}}
        <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/40 p-3.5 flex items-center justify-between gap-3 shadow-2xs hover:shadow-sm transition">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800/80 block truncate">
                    Clínicos
                </span>
                <span class="text-2xl font-black text-emerald-900 tracking-tight mt-0.5 block">
                    {{ $metricas['clinicos'] }}
                </span>
                <span class="text-[10.5px] font-medium text-emerald-700/80 block mt-0.5">
                    Informes y resultados
                </span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-emerald-100 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0">
                <i class="ph-bold ph-heartbeat text-xl"></i>
            </div>
        </div>

        {{-- Card 3: Administrativos (Violeta) --}}
        <div class="rounded-2xl border border-purple-200/80 bg-purple-50/40 p-3.5 flex items-center justify-between gap-3 shadow-2xs hover:shadow-sm transition">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-purple-800/80 block truncate">
                    Administrativos
                </span>
                <span class="text-2xl font-black text-purple-900 tracking-tight mt-0.5 block">
                    {{ $metricas['administrativos'] }}
                </span>
                <span class="text-[10.5px] font-medium text-purple-700/80 block mt-0.5">
                    Formularios y autorizaciones
                </span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-purple-100 border border-purple-200 text-purple-700 flex items-center justify-center shrink-0">
                <i class="ph-bold ph-file-text text-xl"></i>
            </div>
        </div>

        {{-- Card 4: Imágenes (Ámbar) --}}
        <div class="rounded-2xl border border-amber-200/80 bg-amber-50/40 p-3.5 flex items-center justify-between gap-3 shadow-2xs hover:shadow-sm transition">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800/80 block truncate">
                    Imágenes
                </span>
                <span class="text-2xl font-black text-amber-900 tracking-tight mt-0.5 block">
                    {{ $metricas['imagenes'] }}
                </span>
                <span class="text-[10.5px] font-medium text-amber-700/80 block mt-0.5">
                    Estudios y fotografías
                </span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-amber-100 border border-amber-200 text-amber-700 flex items-center justify-center shrink-0">
                <i class="ph-bold ph-camera text-xl"></i>
            </div>
        </div>

        {{-- Card 5: Legales (Azul Institucional) --}}
        <div class="rounded-2xl border border-indigo-200/80 bg-indigo-50/40 p-3.5 flex items-center justify-between gap-3 shadow-2xs hover:shadow-sm transition col-span-2 sm:col-span-1">
            <div class="min-w-0">
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-800/80 block truncate">
                    Legales
                </span>
                <span class="text-2xl font-black text-indigo-900 tracking-tight mt-0.5 block">
                    {{ $metricas['legales'] }}
                </span>
                <span class="text-[10.5px] font-medium text-indigo-700/80 block mt-0.5">
                    Consentimientos
                </span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-indigo-100 border border-indigo-200 text-indigo-700 flex items-center justify-center shrink-0">
                <i class="ph-bold ph-scales text-xl"></i>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. BARRA DE FILTROS                                                       --}}
    {{-- ========================================================================= --}}
    <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        
        {{-- Buscador reactivo --}}
        <div class="relative flex-1 min-w-[200px]">
            <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)] text-sm"></i>
            <input type="text"
                   wire:model.live.debounce.300ms="filtroBusquedaDoc"
                   placeholder="Buscar documentos..."
                   class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] text-xs font-semibold placeholder-[var(--rm-text-muted)] focus:ring-1 focus:ring-[#1E3A8A] focus:border-[#1E3A8A] outline-hidden transition">
        </div>

        {{-- Controles de selección --}}
        <div class="flex flex-wrap items-center gap-2">
            
            {{-- Selector de Tipo --}}
            <select wire:model.live="filtroTipoDoc"
                    class="px-2.5 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] text-xs font-semibold focus:ring-1 focus:ring-[#1E3A8A] outline-hidden cursor-pointer">
                <option value="TODOS">Todos los tipos</option>
                <option value="Informe">Informe médico</option>
                <option value="Radiografía">Radiografía</option>
                <option value="Laboratorio">Laboratorio</option>
                <option value="Consentimiento">Consentimiento informado</option>
                <option value="Plan">Plan de cuidados</option>
                <option value="Cardiológico">Cardiológico</option>
                <option value="Caída">Informe de caída</option>
                <option value="Identificación">Documento de identidad</option>
                <option value="Formulario">Formulario administrativo</option>
            </select>

            {{-- Selector de Categoría --}}
            <select wire:model.live="filtroCategoriaDoc"
                    class="px-2.5 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] text-xs font-semibold focus:ring-1 focus:ring-[#1E3A8A] outline-hidden cursor-pointer">
                <option value="TODAS">Todas las categorías</option>
                <option value="CLINICO">Clínicos</option>
                <option value="ADMINISTRATIVO">Administrativos</option>
                <option value="IMAGEN">Imágenes</option>
                <option value="LEGAL">Legales</option>
                <option value="PERSONAL">Personal</option>
            </select>

            {{-- Selector de Orden --}}
            <select wire:model.live="ordenDoc"
                    class="px-2.5 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] text-xs font-semibold focus:ring-1 focus:ring-[#1E3A8A] outline-hidden cursor-pointer">
                <option value="recientes">Más recientes primero</option>
                <option value="antiguos">Más antiguos primero</option>
                <option value="nombre_asc">Nombre (A - Z)</option>
                <option value="nombre_desc">Nombre (Z - A)</option>
                <option value="tamano_desc">Mayor tamaño</option>
            </select>

            {{-- Botón Filtrar --}}
            <button type="button"
                    wire:click="$refresh"
                    class="px-3.5 py-2 rounded-xl bg-[#1E3A8A] text-white font-bold text-xs hover:bg-[#1E3A8A]/90 transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <i class="ph-bold ph-funnel text-xs"></i>
                <span>Filtrar</span>
            </button>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 4. ESTRUCTURA PRINCIPAL — DOS COLUMNAS (65% IZQUIERDA / 35% DERECHA)     --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- ===================================================================== --}}
        {{-- COLUMNA IZQUIERDA (aprox. 65%): LISTA DE DOCUMENTOS                   --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-8 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-list-dashes text-[#1E3A8A] text-base"></i>
                    <h3 class="text-xs sm:text-sm font-black text-[var(--rm-text-title)] uppercase tracking-wider">
                        Lista de documentos
                    </h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-muted)]">
                        {{ $paginacion['total'] }} registros
                    </span>
                </div>
            </div>

            @if(count($documentos) === 0)
                <div class="py-14 text-center text-xs text-[var(--rm-text-muted)] space-y-2">
                    <i class="ph-bold ph-folder-open text-3xl text-slate-300 dark:text-slate-600 block"></i>
                    <p class="font-bold text-[var(--rm-text-title)]">No encontramos documentos con estos filtros.</p>
                    <p>Modifique los criterios de búsqueda o suba un nuevo documento al expediente.</p>
                    <button type="button"
                            wire:click="abrirModalSubirDoc"
                            class="mt-2 px-3.5 py-1.5 rounded-xl bg-[#1E3A8A] text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs">
                        <i class="ph-bold ph-upload-simple"></i>
                        <span>Subir documento</span>
                    </button>
                </div>
            @else
                <div class="overflow-x-auto border border-[var(--rm-border)]/60 rounded-2xl">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] font-black text-[10.5px] uppercase tracking-wider border-b border-[var(--rm-border)]">
                            <tr>
                                <th class="py-3 px-3.5">Nombre del documento</th>
                                <th class="py-3 px-3">Tipo</th>
                                <th class="py-3 px-3">Categoría</th>
                                <th class="py-3 px-3">Fecha</th>
                                <th class="py-3 px-3">Tamaño</th>
                                <th class="py-3 px-3.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--rm-border)]/50 font-medium">
                            @foreach($documentos as $doc)
                                @php
                                    $esSeleccionado = ($documentoActivo['id'] ?? '') === $doc['id'];
                                @endphp
                                <tr wire:click="seleccionarDocumento('{{ $doc['id'] }}')"
                                    class="transition cursor-pointer {{ $esSeleccionado ? 'bg-blue-50/80 dark:bg-blue-950/40 text-[var(--rm-text-title)] ring-1 ring-[#1E3A8A]/30' : 'hover:bg-[var(--rm-surface-alt)]/60 text-[var(--rm-text-body)]' }}">
                                    
                                    {{-- Nombre del documento + Icono según tipo + Descripción corta --}}
                                    <td class="py-3 px-3.5 min-w-[220px]">
                                        <div class="flex items-start gap-2.5">
                                            <div class="h-8 w-8 rounded-xl flex items-center justify-center shrink-0 border {{ $doc['icono_bg'] ?? 'bg-blue-50' }} {{ $doc['badge_border'] ?? 'border-blue-200' }}">
                                                <i class="ph-bold {{ $doc['icono'] ?? 'ph-file-text' }} {{ $doc['icono_color'] ?? 'text-blue-600' }} text-base"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <span class="font-black text-[var(--rm-text-title)] block leading-snug truncate" title="{{ $doc['nombre'] }}">
                                                    {{ $doc['nombre'] }}
                                                </span>
                                                <p class="text-[10.5px] text-[var(--rm-text-muted)] truncate max-w-[280px] mt-0.5">
                                                    {{ $doc['descripcion'] }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Tipo (Badge semántico) --}}
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold border {{ $doc['badge_bg'] ?? 'bg-blue-50' }} {{ $doc['badge_color'] ?? 'text-blue-700' }} {{ $doc['badge_border'] ?? 'border-blue-200' }}">
                                            {{ $doc['tipo_etiqueta'] ?? $doc['tipo'] }}
                                        </span>
                                    </td>

                                    {{-- Categoría --}}
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <span class="text-[11px] font-bold text-[var(--rm-text-title)]">
                                            {{ $doc['categoria_nombre'] ?? $doc['categoria'] }}
                                        </span>
                                    </td>

                                    {{-- Fecha / Hora --}}
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <span class="font-bold text-[var(--rm-text-title)] block">
                                            {{ $doc['fecha_formateada'] }}
                                        </span>
                                        <span class="text-[10px] text-[var(--rm-text-muted)] block">
                                            {{ $doc['hora_formateada'] }}
                                        </span>
                                    </td>

                                    {{-- Tamaño --}}
                                    <td class="py-3 px-3 whitespace-nowrap font-mono text-[11px] text-[var(--rm-text-muted)]">
                                        {{ $doc['tamano'] }}
                                    </td>

                                    {{-- Acciones (Máximo [ojo] Ver y [...]) --}}
                                    <td class="py-3 px-3.5 text-right whitespace-nowrap"
                                        @click.stop>
                                        <div class="inline-flex items-center gap-1.5 relative">
                                            {{-- Botón Ver --}}
                                            <button type="button"
                                                    wire:click="seleccionarDocumento('{{ $doc['id'] }}')"
                                                    title="Ver documento en panel"
                                                    class="p-1.5 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[#1E3A8A] hover:text-white text-[var(--rm-text-title)] transition shadow-2xs">
                                                <i class="ph-bold ph-eye text-sm"></i>
                                            </button>

                                            {{-- Botón Menú secundario [...] --}}
                                            <div class="relative" x-data="{ abierto: false }">
                                                <button type="button"
                                                        @click="abierto = !abierto"
                                                        title="Más opciones"
                                                        class="p-1.5 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] transition shadow-2xs">
                                                    <i class="ph-bold ph-dots-three-vertical text-sm"></i>
                                                </button>

                                                {{-- Dropdown Menú Secundario --}}
                                                <div x-show="abierto"
                                                     @click.away="abierto = false"
                                                     x-cloak
                                                     class="absolute right-0 mt-1 w-44 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-lg z-30 py-1 text-left text-xs font-semibold divide-y divide-[var(--rm-border)]/40">
                                                    <div class="py-0.5">
                                                        <a href="{{ $doc['url_descarga'] }}"
                                                           download
                                                           class="px-3 py-1.5 flex items-center gap-2 hover:bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] transition">
                                                            <i class="ph-bold ph-download-simple text-[#1E3A8A]"></i>
                                                            <span>Descargar</span>
                                                        </a>
                                                        <button type="button"
                                                                wire:click="seleccionarDocumento('{{ $doc['id'] }}'); setTabDetalleDoc('informacion')"
                                                                @click="abierto = false"
                                                                class="w-full px-3 py-1.5 flex items-center gap-2 hover:bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] transition text-left">
                                                            <i class="ph-bold ph-info text-blue-600"></i>
                                                            <span>Ver información</span>
                                                        </button>
                                                        <button type="button"
                                                                wire:click="seleccionarDocumento('{{ $doc['id'] }}'); setTabDetalleDoc('historial')"
                                                                @click="abierto = false"
                                                                class="w-full px-3 py-1.5 flex items-center gap-2 hover:bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] transition text-left">
                                                            <i class="ph-bold ph-clock-counter-clockwise text-purple-600"></i>
                                                            <span>Ver historial</span>
                                                        </button>
                                                    </div>
                                                    <div class="py-0.5">
                                                        <button type="button"
                                                                onclick="window.print()"
                                                                @click="abierto = false"
                                                                class="w-full px-3 py-1.5 flex items-center gap-2 hover:bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] transition text-left">
                                                            <i class="ph-bold ph-printer text-emerald-600"></i>
                                                            <span>Imprimir</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ============================================================= --}}
                {{-- 5. PAGINACIÓN COMPACTA (Mostrando X de Y, < 1 2 3 >, Selector) --}}
                {{-- ============================================================= --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 text-xs font-semibold text-[var(--rm-text-muted)]">
                    <div>
                        <span>Mostrando {{ $paginacion['hasta'] - $paginacion['desde'] + ($paginacion['total'] > 0 ? 1 : 0) }} de {{ $paginacion['total'] }} documentos</span>
                    </div>

                    <div class="flex items-center gap-4">
                        {{-- Paginador numérico compacto --}}
                        <div class="inline-flex items-center gap-1">
                            <button type="button"
                                    wire:click="cambiarPaginaDoc({{ max(1, $paginacion['pagina_actual'] - 1) }})"
                                    @disabled($paginacion['pagina_actual'] <= 1)
                                    class="p-1 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] disabled:opacity-40 disabled:cursor-not-allowed">
                                <i class="ph-bold ph-caret-left text-xs"></i>
                            </button>

                            @for($p = 1; $p <= $paginacion['total_paginas']; $p++)
                                <button type="button"
                                        wire:click="cambiarPaginaDoc({{ $p }})"
                                        class="h-7 w-7 rounded-lg text-xs font-bold transition {{ $p === $paginacion['pagina_actual'] ? 'bg-[#1E3A8A] text-white' : 'border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)]' }}">
                                    {{ $p }}
                                </button>
                            @endfor

                            <button type="button"
                                    wire:click="cambiarPaginaDoc({{ min($paginacion['total_paginas'], $paginacion['pagina_actual'] + 1) }})"
                                    @disabled($paginacion['pagina_actual'] >= $paginacion['total_paginas'])
                                    class="p-1 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] disabled:opacity-40 disabled:cursor-not-allowed">
                                <i class="ph-bold ph-caret-right text-xs"></i>
                            </button>
                        </div>

                        {{-- Selector registros por página --}}
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px]">Registros por página:</span>
                            <select wire:model.live="porPaginaDoc"
                                    class="py-1 px-2 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[#1E3A8A] outline-hidden">
                                <option value="8">8</option>
                                <option value="15">15</option>
                                <option value="25">25</option>
                            </select>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ===================================================================== --}}
        {{-- COLUMNA DERECHA (aprox. 35%): VISTA PREVIA Y DETALLE DEL DOCUMENTO     --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-4 space-y-4">
            @if($documentoActivo)
                <div class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-4 sticky top-4">
                    
                    {{-- Cabecera del Panel de Vista Previa --}}
                    <div class="flex items-start justify-between gap-2 border-b border-[var(--rm-border)] pb-3">
                        <div class="min-w-0 flex items-start gap-2.5">
                            <div class="h-9 w-9 rounded-xl flex items-center justify-center shrink-0 border {{ $documentoActivo['icono_bg'] ?? 'bg-blue-50' }} {{ $documentoActivo['badge_border'] ?? 'border-blue-200' }}">
                                <i class="ph-bold {{ $documentoActivo['icono'] ?? 'ph-file-text' }} {{ $documentoActivo['icono_color'] ?? 'text-blue-600' }} text-lg"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-[10px] font-black uppercase tracking-wider text-[var(--rm-text-muted)] block">
                                    Vista previa del documento
                                </span>
                                <h3 class="text-xs sm:text-sm font-black text-[var(--rm-text-title)] leading-snug truncate uppercase mt-0.5" title="{{ $documentoActivo['nombre'] }}">
                                    {{ $documentoActivo['nombre'] }}
                                </h3>
                                <p class="text-[10.5px] text-[var(--rm-text-muted)] line-clamp-1 mt-0.5">
                                    {{ $documentoActivo['descripcion'] }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            {{-- Badge Verificado --}}
                            @if($documentoActivo['es_verificado'])
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center gap-1">
                                    <i class="ph-bold ph-check-circle text-xs"></i>
                                    <span class="hidden sm:inline">Documento verificado</span>
                                    <span class="sm:hidden">Verificado</span>
                                </span>
                            @endif

                            {{-- Botón Cerrar selección --}}
                            <button type="button"
                                    wire:click="seleccionarDocumento('')"
                                    title="Cerrar vista previa"
                                    class="p-1 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-rose-50 hover:text-rose-600 text-[var(--rm-text-muted)] transition">
                                <i class="ph-bold ph-x text-xs"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Tabs del Documento: [ Vista previa ] [ Información ] [ Historial ] --}}
                    <div class="flex items-center gap-1.5 p-1 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-xs font-bold">
                        <button type="button"
                                wire:click="setTabDetalleDoc('preview')"
                                class="flex-1 py-1.5 px-2 rounded-lg transition text-center {{ $tabDetalleDoc === 'preview' ? 'bg-[#1E3A8A] text-white shadow-2xs' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]' }}">
                            Vista previa
                        </button>
                        <button type="button"
                                wire:click="setTabDetalleDoc('informacion')"
                                class="flex-1 py-1.5 px-2 rounded-lg transition text-center {{ in_array($tabDetalleDoc, ['informacion', 'info']) ? 'bg-[#1E3A8A] text-white shadow-2xs' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]' }}">
                            Información
                        </button>
                        <button type="button"
                                wire:click="setTabDetalleDoc('historial')"
                                class="flex-1 py-1.5 px-2 rounded-lg transition text-center {{ $tabDetalleDoc === 'historial' ? 'bg-[#1E3A8A] text-white shadow-2xs' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]' }}">
                            Historial
                        </button>
                    </div>

                    {{-- CONTENIDO TAB 1: VISTA PREVIA (PDF / IMAGEN VISOR) --}}
                    @if($tabDetalleDoc === 'preview')
                        <div class="space-y-3">
                            {{-- Barra de Herramientas del Visor (Zoom, Páginas, Controles) --}}
                            <div class="p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] flex items-center justify-between text-xs font-bold text-[var(--rm-text-title)]">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] text-[var(--rm-text-muted)] font-mono">Pág. 1 de 2</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button"
                                            wire:click="ajustarZoomDoc(-25)"
                                            title="Reducir zoom"
                                            class="h-6 w-6 rounded-md border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] flex items-center justify-center text-xs">
                                        <i class="ph-bold ph-minus"></i>
                                    </button>
                                    <span class="text-[11px] font-mono w-10 text-center">{{ $zoomDoc }}%</span>
                                    <button type="button"
                                            wire:click="ajustarZoomDoc(25)"
                                            title="Aumentar zoom"
                                            class="h-6 w-6 rounded-md border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] flex items-center justify-center text-xs">
                                        <i class="ph-bold ph-plus"></i>
                                    </button>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button"
                                            onclick="window.print()"
                                            title="Imprimir"
                                            class="p-1 rounded-md hover:bg-[var(--rm-surface)] text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]">
                                        <i class="ph-bold ph-printer text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Visor Embebido (PDF o Imagen) --}}
                            <div class="rounded-2xl border border-[var(--rm-border)] bg-slate-900/5 dark:bg-slate-950/40 p-2 min-h-[300px] flex items-center justify-center overflow-auto max-h-[420px]">
                                @if($documentoActivo['es_imagen'])
                                    {{-- Visor de Imágenes --}}
                                    <div class="relative w-full flex items-center justify-center">
                                        <img src="{{ $documentoActivo['url_preview'] ?: asset('images/default-avatar.png') }}"
                                             alt="{{ $documentoActivo['nombre'] }}"
                                             style="transform: scale({{ $zoomDoc / 100 }}); transform-origin: center center;"
                                             class="max-h-[360px] w-auto object-contain rounded-xl shadow-xs transition-transform duration-200">
                                    </div>
                                @elseif($documentoActivo['es_pdf'])
                                    {{-- Simulación fiel de primera página de PDF con alta resolución --}}
                                    <div class="w-full bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-800 font-sans space-y-3"
                                         style="transform: scale({{ $zoomDoc / 100 }}); transform-origin: top center;">
                                        
                                        {{-- Membrete institucional del documento --}}
                                        <div class="border-b border-slate-200 dark:border-slate-800 pb-3 flex items-center justify-between">
                                            <div>
                                                <h4 class="text-xs font-black uppercase text-[#1E3A8A]">HOGAR RESIDENCIAL REMEMBERMIND</h4>
                                                <p class="text-[9.5px] text-slate-500">Dirección Médica Asistencial · Expediente Clínico</p>
                                            </div>
                                            <span class="text-[9px] font-mono px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                                ID: {{ $documentoActivo['id'] }}
                                            </span>
                                        </div>

                                        {{-- Título y metadatos clínicos --}}
                                        <div>
                                            <h3 class="text-xs font-black uppercase tracking-tight text-slate-900 dark:text-white">
                                                {{ $documentoActivo['nombre'] }}
                                            </h3>
                                            <p class="text-[10px] text-slate-500 mt-0.5">
                                                Residente: {{ $adultoMayor->nombres }} {{ $adultoMayor->ap_paterno }} · Fecha: {{ $documentoActivo['fecha_formateada'] }}
                                            </p>
                                        </div>

                                        {{-- Resumen de contenido simulado --}}
                                        <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-800 text-[10.5px] leading-relaxed text-slate-600 dark:text-slate-300">
                                            <p class="font-bold text-slate-700 dark:text-slate-200 mb-1">Descripción del registro:</p>
                                            <p>{{ $documentoActivo['descripcion'] }}</p>
                                            <p class="mt-2 text-[10px] text-slate-400 italic">
                                                {{ $documentoActivo['observaciones'] }}
                                            </p>
                                        </div>

                                        {{-- Firma institucional simulada --}}
                                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-[9.5px]">
                                            <div>
                                                <span class="font-bold block">{{ $documentoActivo['subido_por'] }}</span>
                                                <span class="text-slate-500">{{ $documentoActivo['area_origen'] }}</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-emerald-600 font-bold flex items-center gap-1">
                                                    <i class="ph-bold ph-seal-check"></i> Firma autorizada
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- Empty State de no previsualizable --}}
                                    <div class="text-center py-8 text-xs text-[var(--rm-text-muted)] space-y-2">
                                        <i class="ph-bold ph-file-dashed text-3xl block text-slate-400"></i>
                                        <p class="font-bold text-[var(--rm-text-title)]">No es posible mostrar una vista previa de este archivo.</p>
                                        <p class="text-[11px]">Puede descargar el documento completo para visualizarlo en su dispositivo.</p>
                                        <a href="{{ $documentoActivo['url_descarga'] }}"
                                           class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#1E3A8A] text-white text-xs font-bold shadow-2xs">
                                            <i class="ph-bold ph-download-simple"></i>
                                            <span>Descargar documento</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- CONTENIDO TAB 2: INFORMACIÓN Y METADATOS --}}
                    @if(in_array($tabDetalleDoc, ['informacion', 'info']))
                        <div class="space-y-3 text-xs">
                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]">
                                    <span class="text-[10px] font-black uppercase text-[var(--rm-text-muted)] block">Tipo de documento</span>
                                    <span class="font-bold text-[var(--rm-text-title)] mt-0.5 block">{{ $documentoActivo['tipo_etiqueta'] }}</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]">
                                    <span class="text-[10px] font-black uppercase text-[var(--rm-text-muted)] block">Categoría</span>
                                    <span class="font-bold text-[var(--rm-text-title)] mt-0.5 block">{{ $documentoActivo['categoria_nombre'] ?? $documentoActivo['categoria'] }}</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]">
                                    <span class="text-[10px] font-black uppercase text-[var(--rm-text-muted)] block">Fecha del documento</span>
                                    <span class="font-bold text-[var(--rm-text-title)] mt-0.5 block">{{ $documentoActivo['fecha_formateada'] }}</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]">
                                    <span class="text-[10px] font-black uppercase text-[var(--rm-text-muted)] block">Tamaño y formato</span>
                                    <span class="font-bold text-[var(--rm-text-title)] mt-0.5 block font-mono">{{ $documentoActivo['tamano'] }} ({{ strtoupper($documentoActivo['extension']) }})</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-1.5">
                                <span class="text-[10px] font-black uppercase text-[var(--rm-text-muted)] block">Subido por</span>
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-[#1E3A8A]/10 text-[#1E3A8A] flex items-center justify-center font-black text-[10px]">
                                        {{ substr($documentoActivo['subido_por'], 0, 2) }}
                                    </div>
                                    <div>
                                        <span class="font-black text-[var(--rm-text-title)] block">{{ $documentoActivo['subido_por'] }}</span>
                                        <span class="text-[10.5px] text-[var(--rm-text-muted)] block">{{ $documentoActivo['area_origen'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-1">
                                <span class="text-[10px] font-black uppercase text-[var(--rm-text-muted)] block">Documento relacionado con</span>
                                <span class="font-bold text-[var(--rm-text-title)] block">{{ $documentoActivo['relacionado_con'] }}</span>
                            </div>

                            @if(!empty($documentoActivo['observaciones']))
                                <div class="p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-1">
                                    <span class="text-[10px] font-black uppercase text-[var(--rm-text-muted)] block">Observaciones</span>
                                    <p class="text-[11px] text-[var(--rm-text-body)] leading-relaxed">{{ $documentoActivo['observaciones'] }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- CONTENIDO TAB 3: HISTORIAL Y TRAZABILIDAD DOCUMENTAL --}}
                    @if($tabDetalleDoc === 'historial')
                        <div class="space-y-3">
                            <span class="text-[10.5px] font-black uppercase tracking-wider text-[var(--rm-text-muted)] block">
                                Trazabilidad documental
                            </span>
                            <div class="relative pl-5 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[var(--rm-border)]">
                                @foreach($documentoActivo['trazabilidad'] as $item)
                                    <div class="relative">
                                        <div class="absolute -left-5 top-1 h-2.5 w-2.5 rounded-full bg-[#1E3A8A] ring-4 ring-[var(--rm-surface)]"></div>
                                        <div>
                                            <div class="flex items-center justify-between gap-1 text-[10px] text-[var(--rm-text-muted)] font-mono">
                                                <span>{{ $item['fecha'] }}</span>
                                            </div>
                                            <h4 class="text-xs font-black text-[var(--rm-text-title)] mt-0.5">
                                                {{ $item['titulo'] }}
                                            </h4>
                                            <p class="text-[11px] font-semibold text-[#1E3A8A] mt-0.5">
                                                {{ $item['usuario'] }}
                                            </p>
                                            <p class="text-[10.5px] text-[var(--rm-text-muted)] mt-0.5">
                                                {{ $item['descripcion'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ========================================================= --}}
                    {{-- 6. ACCIONES INFERIORES DEL PANEL (Descargar, Compartir, Imprimir) --}}
                    {{-- ========================================================= --}}
                    <div class="pt-3 border-t border-[var(--rm-border)] flex items-center justify-between gap-2">
                        <a href="{{ $documentoActivo['url_descarga'] }}"
                           download
                           class="flex-1 px-3 py-2 rounded-xl bg-[#1E3A8A] text-white font-bold text-xs hover:bg-[#1E3A8A]/90 transition text-center flex items-center justify-center gap-1.5 shadow-2xs">
                            <i class="ph-bold ph-download-simple"></i>
                            <span>Descargar</span>
                        </a>

                        <button type="button"
                                @click="alert('Enlace de consulta seguro generado para personal acreditado.')"
                                class="px-3 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:bg-[var(--rm-surface)] text-[var(--rm-text-title)] font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
                            <i class="ph-bold ph-share-network"></i>
                            <span>Compartir</span>
                        </button>

                        <button type="button"
                                onclick="window.print()"
                                class="px-3 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:bg-[var(--rm-surface)] text-[var(--rm-text-title)] font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
                            <i class="ph-bold ph-printer"></i>
                            <span>Imprimir</span>
                        </button>
                    </div>

                </div>
            @else
                {{-- Empty state si no hay ningún documento seleccionado --}}
                <div class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-8 text-center space-y-3 shadow-sm">
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 border border-blue-200 text-[#1E3A8A] flex items-center justify-center mx-auto">
                        <i class="ph-bold ph-file-search text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-[var(--rm-text-title)]">Ningún documento seleccionado</h4>
                        <p class="text-xs text-[var(--rm-text-muted)] mt-1">
                            Seleccione una fila de la lista para ver la vista previa, su información técnica y trazabilidad histórica.
                        </p>
                    </div>
                </div>
            @endif
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- 7. MODAL CENTRAL: + SUBIR DOCUMENTO                                       --}}
    {{-- ========================================================================= --}}
    @if($modalSubirDoc)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-xl rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                {{-- Cabecera del modal --}}
                <div class="p-4 sm:p-5 border-b border-[var(--rm-border)] flex items-center justify-between bg-[var(--rm-surface-alt)]">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-2xl bg-[#1E3A8A]/10 text-[#1E3A8A] border border-[#1E3A8A]/20 flex items-center justify-center shrink-0">
                            <i class="ph-bold ph-upload-simple text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-[var(--rm-text-title)]">
                                Subir documento al expediente
                            </h3>
                            <p class="text-xs text-[var(--rm-text-muted)]">
                                Complete los metadatos y seleccione el archivo digitalizado.
                            </p>
                        </div>
                    </div>
                    <button type="button"
                            wire:click="cerrarModalSubirDoc"
                            class="p-1.5 rounded-xl border border-[var(--rm-border)] text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] transition">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>

                {{-- Formulario --}}
                <form wire:submit="guardarNuevoDocumento" class="p-4 sm:p-5 space-y-4 max-h-[80vh] overflow-y-auto">
                    
                    {{-- Dropzone / File input --}}
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] mb-1.5">
                            Archivo digital (PDF o Imagen) *
                        </label>
                        <div class="p-4 rounded-2xl border-2 border-dashed border-[var(--rm-border)] hover:border-[#1E3A8A] bg-[var(--rm-surface-alt)] text-center transition cursor-pointer relative">
                            <input type="file"
                                   wire:model="nuevoDocArchivo"
                                   accept=".pdf,.jpg,.jpeg,.png,.webp"
                                   class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                            <div class="space-y-1">
                                <i class="ph-bold ph-cloud-arrow-up text-3xl text-[#1E3A8A] mx-auto block"></i>
                                <p class="text-xs font-bold text-[var(--rm-text-title)]">
                                    Haga clic o arrastre el archivo aquí
                                </p>
                                <p class="text-[11px] text-[var(--rm-text-muted)]">
                                    Formatos permitidos: PDF, JPG, PNG, WebP (Máx. 15 MB)
                                </p>
                            </div>
                        </div>
                        @if($nuevoDocArchivo)
                            <div class="mt-2 p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
                                <i class="ph-bold ph-check-circle text-emerald-600"></i>
                                <span class="truncate">Archivo listo para cargar: {{ $nuevoDocArchivo->getClientOriginalName() }}</span>
                            </div>
                        @endif
                        @error('nuevoDocArchivo')
                            <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Nombre del Documento --}}
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                            Nombre del documento *
                        </label>
                        <input type="text"
                               wire:model="nuevoDocNombre"
                               placeholder="Ej. Evaluación médica geriátrica semestral"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[#1E3A8A] outline-hidden">
                        @error('nuevoDocNombre')
                            <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Grid Tipo y Categoría --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                                Tipo de documento *
                            </label>
                            <input type="text"
                                   wire:model="nuevoDocTipo"
                                   placeholder="Ej. Informe, Estudio, Consentimiento"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[#1E3A8A] outline-hidden">
                            @error('nuevoDocTipo')
                                <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                                Categoría *
                            </label>
                            <select wire:model="nuevoDocCategoria"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[#1E3A8A] outline-hidden">
                                <option value="CLINICO">Clínico</option>
                                <option value="ADMINISTRATIVO">Administrativo</option>
                                <option value="IMAGEN">Imágenes</option>
                                <option value="LEGAL">Legal</option>
                                <option value="PERSONAL">Personal</option>
                            </select>
                            @error('nuevoDocCategoria')
                                <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Grid Fecha y Módulo relacionado --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                                Fecha del documento *
                            </label>
                            <input type="date"
                                   wire:model="nuevoDocFecha"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[#1E3A8A] outline-hidden">
                            @error('nuevoDocFecha')
                                <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                                Módulo relacionado
                            </label>
                            <input type="text"
                                   wire:model="nuevoDocModulo"
                                   placeholder="Ej. Ficha Médica / Enfermería"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[#1E3A8A] outline-hidden">
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                            Descripción corta
                        </label>
                        <input type="text"
                               wire:model="nuevoDocDescripcion"
                               placeholder="Resumen del contenido del documento"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[#1E3A8A] outline-hidden">
                    </div>

                    {{-- Observaciones --}}
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                            Observaciones adicionales
                        </label>
                        <textarea wire:model="nuevoDocObservaciones"
                                  rows="2"
                                  placeholder="Notas sobre validez, autorizaciones o profesional emisor"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[#1E3A8A] outline-hidden resize-none"></textarea>
                    </div>

                    {{-- Botones de acción del modal --}}
                    <div class="pt-3 border-t border-[var(--rm-border)] flex items-center justify-end gap-2.5">
                        <button type="button"
                                wire:click="cerrarModalSubirDoc"
                                class="px-4 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:bg-[var(--rm-surface)] text-xs font-bold text-[var(--rm-text-title)] transition">
                            Cancelar
                        </button>

                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="px-5 py-2 rounded-xl bg-[#1E3A8A] hover:bg-[#1E3A8A]/90 text-white text-xs font-black transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                            <span wire:loading.remove wire:target="guardarNuevoDocumento">Registrar documento</span>
                            <span wire:loading wire:target="guardarNuevoDocumento">Subiendo archivo...</span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    @endif

</div>
