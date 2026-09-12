@if($drawerUbicacion && $adultoDrawer)
<div class="fixed inset-0 z-50 overflow-hidden font-sans"
     role="dialog"
     aria-modal="true"
     aria-labelledby="drawer-ubicacion-title"
     x-data
     x-on:keydown.escape.window="$wire.cerrarDrawer()">
    <!-- Backdrop oscuro con blur suave -->
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity"
         wire:click="cerrarDrawer"></div>

    <!-- Contenedor Deslizante Lateral (Barra Derecha) -->
    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
        <div class="pointer-events-auto flex h-full w-screen max-w-xl transform flex-col overflow-hidden rm-drawer transition duration-300 ease-in-out">
            
            <!-- Encabezado del Drawer (#F7F0E9) -->
            <div class="rm-drawer-header">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--rm-surface-alt)] text-[var(--rm-accent)] border border-[var(--rm-border)] shadow-xs">
                        <i class="ph-bold ph-map-pin text-xl"></i>
                    </span>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200 shadow-xs flex items-center gap-1">
                                <i class="ph-bold ph-sidebar"></i> Panel Lateral de Ubicación
                            </span>
                        </div>
                        <h3 id="drawer-ubicacion-title" class="rm-modal-title text-base font-bold text-[var(--rm-text-title)]">
                            Ubicación y Ficha del Residente
                        </h3>
                        <p class="text-xs text-[var(--rm-text-muted)]">Cama asignada, riesgos clínicos y datos asistenciales</p>
                    </div>
                </div>
                <button type="button"
                    wire:click="cerrarDrawer"
                    aria-label="Cerrar panel de ubicación"
                    class="rm-btn-icon text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            <!-- Cuerpo del Drawer con Scroll (#FBF7F2) -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3.5 text-xs">
                
                <!-- 1. Perfil del Residente -->
                <div class="p-3.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] shadow-xs">
                    <div class="flex items-center gap-3">
                        @if($adultoDrawer->foto_url)
                            <img src="{{ $adultoDrawer->foto_url }}" alt="{{ $adultoDrawer->nombre_completo }}" class="h-12 w-12 rounded-xl object-cover border border-[var(--rm-border)] shadow-xs" />
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[var(--rm-accent-action)] text-white font-bold text-base shadow-xs">
                                {{ substr($adultoDrawer->nombres, 0, 1) }}{{ substr($adultoDrawer->ap_paterno, 0, 1) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-[var(--rm-text-title)] text-sm leading-tight truncate">
                                    {{ $adultoDrawer->ap_paterno }} {{ $adultoDrawer->ap_materno }} {{ $adultoDrawer->nombres }}
                                </h4>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold border {{ $adultoDrawer->estado_badge_color }}">{{ $adultoDrawer->estado_humano }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-[11px] text-[var(--rm-text-muted)] mt-0.5 flex-wrap">
                                <span>{{ $adultoDrawer->edad_texto }}</span>
                                <span>•</span>
                                <span>{{ $adultoDrawer->ci ? 'CI: '.$adultoDrawer->ci : 'Documento no registrado' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Ubicación Asistencial Física -->
                <div class="p-3.5 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)] shadow-xs space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-1.5">
                            <i class="ph-bold ph-bed text-base text-[var(--rm-primary)]"></i>
                            Asignación de Entorno y Cama
                        </span>
                        <span class="rm-badge rm-badge-info text-[10px]">
                            Cama Asignada
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 bg-[var(--rm-surface-alt)] rounded-xl border border-[var(--rm-border)]">
                            <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block mb-0.5">Habitación</span>
                            <p class="font-bold text-[var(--rm-text-title)] text-xs">
                                {{ $adultoDrawer->habitacion_texto }}
                            </p>
                            @if($adultoDrawer->habitacion?->codigo)
                                <p class="text-[10.5px] text-[var(--rm-text-muted)] font-mono mt-0.5">Cód. {{ $adultoDrawer->habitacion->codigo }}</p>
                            @endif
                        </div>

                        <div class="p-2.5 bg-[var(--rm-surface-alt)] rounded-xl border border-[var(--rm-border)]">
                            <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block mb-0.5">Cama Clínica</span>
                            <p class="font-bold text-[var(--rm-text-title)] text-xs">
                                {{ $adultoDrawer->cama_texto }}
                            </p>
                            <p class="text-[10.5px] text-[var(--rm-success-action)] font-bold mt-0.5">Asignación vigente</p>
                        </div>

                        <div class="p-2.5 bg-[var(--rm-surface-alt)] rounded-xl border border-[var(--rm-border)] col-span-2">
                            <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block mb-0.5">Pabellón / Sector</span>
                            <p class="font-bold text-[var(--rm-text-title)] flex items-center gap-1.5 text-xs">
                                <i class="ph ph-compass text-[var(--rm-primary)]"></i>
                                {{ $adultoDrawer->habitacion?->ubicacion ?? 'Pabellón Central' }}
                            </p>
                            <p class="text-[10.5px] text-[var(--rm-text-muted)] mt-0.5">Tipo: {{ $adultoDrawer->habitacion?->tipo_habitacion ?? 'INDIVIDUAL' }}</p>
                        </div>
                    </div>
                </div>

                <!-- 3. Condición Clínica, Alergias y Riesgos -->
                <div class="p-3.5 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)] shadow-xs space-y-2.5">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-1.5">
                        <i class="ph-bold ph-shield-warning text-base text-[var(--rm-accent)]"></i>
                        Condición Asistencial y Riesgos
                    </span>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]">
                            <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)]">Nivel de Cuidado</span>
                            <p class="font-bold text-[var(--rm-text-title)] mt-0.5">{{ $adultoDrawer->nivel_dependencia ?? 'Moderada' }}</p>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]">
                            <span class="text-[10px] font-bold uppercase text-[var(--rm-danger-action)]">Riesgo de Caídas</span>
                            <p class="font-bold text-[var(--rm-danger-action)] mt-0.5">{{ $adultoDrawer->riesgo_caida ?? 'Medio' }}</p>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]">
                            <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)]">Dieta Prescrita</span>
                            <p class="font-bold text-[var(--rm-text-title)] mt-0.5">{{ $adultoDrawer->tipo_dieta ?? 'General normosódica' }}</p>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]">
                            <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)]">Alergias</span>
                            <p class="font-bold text-[var(--rm-text-title)] mt-0.5">{{ $adultoDrawer->alergias ?? 'Ninguna registrada' }}</p>
                        </div>
                    </div>
                </div>

                <!-- 4. Alertas Clínicas Activas del Residente -->
                @if($adultoDrawer->alertas && $adultoDrawer->alertas->isNotEmpty())
                    <div class="space-y-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] block">
                            Alertas Clínicas Activas ({{ $adultoDrawer->alertas->count() }})
                        </span>
                        <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                            @foreach($adultoDrawer->alertas as $alt)
                                @php
                                    $altBadgeClass = match($alt->nivel) {
                                        'CRITICO' => 'rm-badge-danger',
                                        'ALTO' => 'rm-badge-warning',
                                        default => 'rm-badge-info'
                                    };
                                @endphp
                                <div class="p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-xs flex items-center justify-between shadow-xs">
                                    <div class="min-w-0 pr-2">
                                        <p class="font-bold text-[var(--rm-text-title)] truncate">{{ $alt->tipo_alerta }}</p>
                                        <p class="text-[10.5px] text-[var(--rm-text-muted)] truncate">{{ $alt->motivo }}</p>
                                    </div>
                                    <span class="rm-badge {{ $altBadgeClass }} text-[10px] shrink-0">
                                        {{ $alt->nivel }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pie del Drawer (#F7F0E9) con Botón Ficha 360 -->
            <div class="rm-drawer-footer flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button type="button"
                        wire:click="verGraficos('{{ $adultoDrawer->cod_am }}')"
                        class="rm-btn rm-btn-sm rm-btn-secondary cursor-pointer">
                        <i class="ph ph-chart-line-up text-base"></i>
                        <span>Ver Gráficos</span>
                    </button>

                    @canany(['enfermeria.ver_ficha_paciente', 'adultos.ver'])
                        <a href="{{ route('admin.enfermeria.pacientes.ficha', $adultoDrawer->cod_am) }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           title="Abrir expediente clínico integral en nueva pestaña"
                           class="rm-btn rm-btn-sm rm-btn-accent inline-flex items-center gap-1.5">
                            <i class="ph-bold ph-arrow-square-out text-base"></i>
                            <span>Abrir Ficha 360</span>
                        </a>
                    @endcanany
                </div>

                <button type="button"
                    wire:click="cerrarDrawer"
                    class="rm-btn rm-btn-sm rm-btn-ghost cursor-pointer">
                    Cerrar Panel
                </button>
            </div>
        </div>
    </div>
</div>
@endif
