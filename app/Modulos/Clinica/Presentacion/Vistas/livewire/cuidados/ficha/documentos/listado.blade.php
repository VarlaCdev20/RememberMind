        {{-- COLUMNA IZQUIERDA (aprox. 65%): LISTA DE DOCUMENTOS                   --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-8 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-list-dashes text-[var(--rm-action-primary)] text-base"></i>
                    <h3 class="text-xs sm:text-sm font-bold text-[var(--rm-text-title)] uppercase tracking-wider">
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
                    <i class="ph-bold ph-folder-open text-3xl text-[var(--rm-text-muted)] dark:text-[var(--rm-text-secondary)] block"></i>
                    <p class="font-bold text-[var(--rm-text-title)]">No encontramos documentos con estos filtros.</p>
                    <p>Modifique los criterios de búsqueda o suba un nuevo documento al expediente.</p>
                    @if(!$consultaEnfermeria)
<button type="button"
                            wire:click="abrirModalSubirDoc"
                            class="mt-2 px-3.5 py-1.5 rounded-xl bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs">
                        <i class="ph-bold ph-upload-simple"></i>
                        <span>Subir documento</span>
                    </button>
@endif
                </div>
            @else
                <div class="overflow-x-auto border border-[var(--rm-border)]/60 rounded-2xl">
                    <table class="rm-data-table rm-data-table--actions w-full text-left text-xs border-collapse">
                        <thead class="bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] font-bold text-[10.5px] uppercase tracking-wider border-b border-[var(--rm-border)]">
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
                                    class="transition cursor-pointer {{ $esSeleccionado ? 'bg-[var(--rm-info-soft)] dark:bg-[var(--rm-info-soft)] text-[var(--rm-text-title)] ring-1 ring-[var(--rm-action-primary)]/30' : 'hover:bg-[var(--rm-surface-alt)]/60 text-[var(--rm-text-body)]' }}">

                                    {{-- Nombre del documento + Icono según tipo + Descripción corta --}}
                                    <td class="py-3 px-3.5 min-w-[220px]">
                                        <div class="flex items-start gap-2.5">
                                            <div class="h-8 w-8 rounded-xl flex items-center justify-center shrink-0 border {{ $doc['icono_bg'] ?? 'bg-[var(--rm-info-soft)]' }} {{ $doc['badge_border'] ?? 'border-[var(--rm-info-strong)]' }}">
                                                <i class="ph-bold {{ $doc['icono'] ?? 'ph-file-text' }} {{ $doc['icono_color'] ?? 'text-[var(--rm-info-strong)]' }} text-base"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <span class="font-bold text-[var(--rm-text-title)] block leading-snug truncate" title="{{ $doc['nombre'] }}">
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
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold border {{ $doc['badge_bg'] ?? 'bg-[var(--rm-info-soft)]' }} {{ $doc['badge_color'] ?? 'text-[var(--rm-info-strong)]' }} {{ $doc['badge_border'] ?? 'border-[var(--rm-info-strong)]' }}">
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
                                                    class="p-1.5 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-action-primary)] hover:text-[var(--rm-text-on-primary)] text-[var(--rm-text-title)] transition shadow-2xs">
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
                                                            <i class="ph-bold ph-download-simple text-[var(--rm-action-primary)]"></i>
                                                            <span>Descargar</span>
                                                        </a>
                                                        <button type="button"
                                                                wire:click="seleccionarDocumento('{{ $doc['id'] }}'); setTabDetalleDoc('informacion')"
                                                                @click="abierto = false"
                                                                class="w-full px-3 py-1.5 flex items-center gap-2 hover:bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] transition text-left">
                                                            <i class="ph-bold ph-info text-[var(--rm-info-strong)]"></i>
                                                            <span>Ver información</span>
                                                        </button>
                                                        <button type="button"
                                                                wire:click="seleccionarDocumento('{{ $doc['id'] }}'); setTabDetalleDoc('historial')"
                                                                @click="abierto = false"
                                                                class="w-full px-3 py-1.5 flex items-center gap-2 hover:bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] transition text-left">
                                                            <i class="ph-bold ph-clock-counter-clockwise text-[var(--rm-action-primary)]"></i>
                                                            <span>Ver historial</span>
                                                        </button>
                                                    </div>
                                                    <div class="py-0.5">
                                                        <button type="button"
                                                                onclick="window.print()"
                                                                @click="abierto = false"
                                                                class="w-full px-3 py-1.5 flex items-center gap-2 hover:bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] transition text-left">
                                                            <i class="ph-bold ph-printer text-[var(--rm-success-strong)]"></i>
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
                                        class="h-7 w-7 rounded-lg text-xs font-bold transition {{ $p === $paginacion['pagina_actual'] ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)]' : 'border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)]' }}">
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
                                    class="py-1 px-2 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[var(--rm-action-primary)] outline-hidden">
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
