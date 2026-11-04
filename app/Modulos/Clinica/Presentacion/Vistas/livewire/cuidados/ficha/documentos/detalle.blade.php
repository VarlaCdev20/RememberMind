        {{-- COLUMNA DERECHA (aprox. 35%): VISTA PREVIA Y DETALLE DEL DOCUMENTO     --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-4 space-y-4">
            @if($documentoActivo)
                <div class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-4 sticky top-4">

                    {{-- Cabecera del Panel de Vista Previa --}}
                    <div class="flex items-start justify-between gap-2 border-b border-[var(--rm-border)] pb-3">
                        <div class="min-w-0 flex items-start gap-2.5">
                            <div class="h-9 w-9 rounded-xl flex items-center justify-center shrink-0 border {{ $documentoActivo['icono_bg'] ?? 'bg-[var(--rm-info-soft)]' }} {{ $documentoActivo['badge_border'] ?? 'border-[var(--rm-info-strong)]' }}">
                                <i class="ph-bold {{ $documentoActivo['icono'] ?? 'ph-file-text' }} {{ $documentoActivo['icono_color'] ?? 'text-[var(--rm-info-strong)]' }} text-lg"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)] block">
                                    Vista previa del documento
                                </span>
                                <h3 class="text-xs sm:text-sm font-bold text-[var(--rm-text-title)] leading-snug truncate uppercase mt-0.5" title="{{ $documentoActivo['nombre'] }}">
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
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[var(--rm-success-soft)] border border-[var(--rm-success-strong)] text-[var(--rm-success-strong)] flex items-center gap-1">
                                    <i class="ph-bold ph-check-circle text-xs"></i>
                                    <span class="hidden sm:inline">Documento verificado</span>
                                    <span class="sm:hidden">Verificado</span>
                                </span>
                            @endif

                            {{-- Botón Cerrar selección --}}
                            <button type="button"
                                    wire:click="seleccionarDocumento('')"
                                    title="Cerrar vista previa"
                                    class="p-1 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-danger-soft)] hover:text-[var(--rm-danger-strong)] text-[var(--rm-text-muted)] transition">
                                <i class="ph-bold ph-x text-xs"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Tabs del Documento: [ Vista previa ] [ Información ] [ Historial ] --}}
                    <div class="flex items-center gap-1.5 p-1 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-xs font-bold">
                        <button type="button"
                                wire:click="setTabDetalleDoc('preview')"
                                class="flex-1 py-1.5 px-2 rounded-lg transition text-center {{ $tabDetalleDoc === 'preview' ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-2xs' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]' }}">
                            Vista previa
                        </button>
                        <button type="button"
                                wire:click="setTabDetalleDoc('informacion')"
                                class="flex-1 py-1.5 px-2 rounded-lg transition text-center {{ in_array($tabDetalleDoc, ['informacion', 'info']) ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-2xs' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]' }}">
                            Información
                        </button>
                        <button type="button"
                                wire:click="setTabDetalleDoc('historial')"
                                class="flex-1 py-1.5 px-2 rounded-lg transition text-center {{ $tabDetalleDoc === 'historial' ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-2xs' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]' }}">
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
                            <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-modal-overlay)] dark:bg-[var(--rm-modal-overlay)] p-2 min-h-[300px] flex items-center justify-center overflow-auto max-h-[420px]">
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
                                    <div class="w-full bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] dark:text-[var(--rm-text-muted)] rounded-xl p-5 shadow-sm border border-[var(--rm-border)] dark:border-[var(--rm-border)] font-sans space-y-3"
                                         style="transform: scale({{ $zoomDoc / 100 }}); transform-origin: top center;">

                                        {{-- Membrete institucional del documento --}}
                                        <div class="border-b border-[var(--rm-border)] dark:border-[var(--rm-border)] pb-3 flex items-center justify-between">
                                            <div>
                                                <h4 class="text-xs font-bold uppercase text-[var(--rm-action-primary)]">HOGAR RESIDENCIAL REMEMBERMIND</h4>
                                                <p class="text-[9.5px] text-[var(--rm-text-secondary)]">Dirección Médica Asistencial · Expediente Clínico</p>
                                            </div>
                                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                                ID: {{ $documentoActivo['id'] }}
                                            </span>
                                        </div>

                                        {{-- Título y metadatos clínicos --}}
                                        <div>
                                            <h3 class="text-xs font-bold uppercase tracking-tight text-[var(--rm-text-primary)] dark:text-[var(--rm-text-on-primary)]">
                                                {{ $documentoActivo['nombre'] }}
                                            </h3>
                                            <p class="text-[10px] text-[var(--rm-text-secondary)] mt-0.5">
                                                Residente: {{ $adultoMayor->nombres }} {{ $adultoMayor->ap_paterno }} · Fecha: {{ $documentoActivo['fecha_formateada'] }}
                                            </p>
                                        </div>

                                        {{-- Resumen de contenido simulado --}}
                                        <div class="p-3 rounded-lg bg-[var(--rm-surface)] dark:bg-[var(--rm-modal-overlay)] border border-[var(--rm-border)] dark:border-[var(--rm-border)] text-[10.5px] leading-relaxed text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                            <p class="font-bold text-[var(--rm-text-primary)] dark:text-[var(--rm-text-muted)] mb-1">Descripción del registro:</p>
                                            <p>{{ $documentoActivo['descripcion'] }}</p>
                                            <p class="mt-2 text-[10px] text-[var(--rm-text-muted)] italic">
                                                {{ $documentoActivo['observaciones'] }}
                                            </p>
                                        </div>

                                        {{-- Firma institucional simulada --}}
                                        <div class="pt-3 border-t border-[var(--rm-border)] dark:border-[var(--rm-border)] flex items-center justify-between text-[9.5px]">
                                            <div>
                                                <span class="font-bold block">{{ $documentoActivo['subido_por'] }}</span>
                                                <span class="text-[var(--rm-text-secondary)]">{{ $documentoActivo['area_origen'] }}</span>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-[var(--rm-success-strong)] font-bold flex items-center gap-1">
                                                    <i class="ph-bold ph-seal-check"></i> Firma autorizada
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- Empty State de no previsualizable --}}
                                    <div class="text-center py-8 text-xs text-[var(--rm-text-muted)] space-y-2">
                                        <i class="ph-bold ph-file-dashed text-3xl block text-[var(--rm-text-muted)]"></i>
                                        <p class="font-bold text-[var(--rm-text-title)]">No es posible mostrar una vista previa de este archivo.</p>
                                        <p class="text-[11px]">Puede descargar el documento completo para visualizarlo en su dispositivo.</p>
                                        <a href="{{ $documentoActivo['url_descarga'] }}"
                                           class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] text-xs font-bold shadow-2xs">
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
                                    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Tipo de documento</span>
                                    <span class="font-bold text-[var(--rm-text-title)] mt-0.5 block">{{ $documentoActivo['tipo_etiqueta'] }}</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]">
                                    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Categoría</span>
                                    <span class="font-bold text-[var(--rm-text-title)] mt-0.5 block">{{ $documentoActivo['categoria_nombre'] ?? $documentoActivo['categoria'] }}</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]">
                                    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Fecha del documento</span>
                                    <span class="font-bold text-[var(--rm-text-title)] mt-0.5 block">{{ $documentoActivo['fecha_formateada'] }}</span>
                                </div>
                                <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]">
                                    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Tamaño y formato</span>
                                    <span class="font-bold text-[var(--rm-text-title)] mt-0.5 block font-mono">{{ $documentoActivo['tamano'] }} ({{ strtoupper($documentoActivo['extension']) }})</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-1.5">
                                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Subido por</span>
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-[var(--rm-action-primary)]/10 text-[var(--rm-action-primary)] flex items-center justify-center font-bold text-[10px]">
                                        {{ substr($documentoActivo['subido_por'], 0, 2) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-[var(--rm-text-title)] block">{{ $documentoActivo['subido_por'] }}</span>
                                        <span class="text-[10.5px] text-[var(--rm-text-muted)] block">{{ $documentoActivo['area_origen'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-1">
                                <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Documento relacionado con</span>
                                <span class="font-bold text-[var(--rm-text-title)] block">{{ $documentoActivo['relacionado_con'] }}</span>
                            </div>

                            @if(!empty($documentoActivo['observaciones']))
                                <div class="p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-1">
                                    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Observaciones</span>
                                    <p class="text-[11px] text-[var(--rm-text-body)] leading-relaxed">{{ $documentoActivo['observaciones'] }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- CONTENIDO TAB 3: HISTORIAL Y TRAZABILIDAD DOCUMENTAL --}}
                    @if($tabDetalleDoc === 'historial')
                        <div class="space-y-3">
                            <span class="text-[10.5px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)] block">
                                Trazabilidad documental
                            </span>
                            <div class="relative pl-5 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[var(--rm-border)]">
                                @foreach($documentoActivo['trazabilidad'] as $item)
                                    <div class="relative">
                                        <div class="absolute -left-5 top-1 h-2.5 w-2.5 rounded-full bg-[var(--rm-action-primary)] ring-4 ring-[var(--rm-surface)]"></div>
                                        <div>
                                            <div class="flex items-center justify-between gap-1 text-[10px] text-[var(--rm-text-muted)] font-mono">
                                                <span>{{ $item['fecha'] }}</span>
                                            </div>
                                            <h4 class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5">
                                                {{ $item['titulo'] }}
                                            </h4>
                                            <p class="text-[11px] font-semibold text-[var(--rm-action-primary)] mt-0.5">
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
                           class="flex-1 px-3 py-2 rounded-xl bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] font-bold text-xs hover:bg-[var(--rm-action-primary)]/90 transition text-center flex items-center justify-center gap-1.5 shadow-2xs">
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
                    <div class="h-12 w-12 rounded-2xl bg-[var(--rm-info-soft)] border border-[var(--rm-info-strong)] text-[var(--rm-action-primary)] flex items-center justify-center mx-auto">
                        <i class="ph-bold ph-file-search text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-[var(--rm-text-title)]">Ningún documento seleccionado</h4>
                        <p class="text-xs text-[var(--rm-text-muted)] mt-1">
                            Seleccione una fila de la lista para ver la vista previa, su información técnica y trazabilidad histórica.
                        </p>
                    </div>
                </div>
            @endif
        </div>



    {{-- ========================================================================= --}}
