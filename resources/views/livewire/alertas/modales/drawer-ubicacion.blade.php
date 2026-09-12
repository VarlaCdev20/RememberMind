@if($drawerUbicacion && $adultoDrawer)
<div class="fixed inset-0 z-50 overflow-hidden font-sans"
     role="dialog"
     aria-modal="true"
     aria-labelledby="drawer-ubicacion-title"
     x-data
     x-on:keydown.escape.window="$wire.cerrarDrawer()">
    {{-- 1. Backdrop oscuro suave (sin blur invasivo) --}}
    <div class="rm-drawer-backdrop" wire:click="cerrarDrawer"></div>

    {{-- 2. Contenedor Deslizante Lateral Nítido (680-760px) --}}
    <div class="pointer-events-none fixed inset-y-0 right-0 z-50 flex max-w-full pl-6 sm:pl-10">
        <div class="pointer-events-auto flex h-full w-screen max-w-[760px] md:w-[740px] transform flex-col overflow-hidden rm-drawer transition duration-300 ease-in-out">
            
            {{-- HEADER FIJO (Badge de Ubicación + Título + Subtítulo + Botón X) --}}
            <header class="rm-drawer-header">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <span class="rm-drawer-badge">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                            PANEL LATERAL DE UBICACIÓN
                        </span>
                        <div class="flex items-center gap-2 pt-0.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-surface)] text-amber-700 border border-[var(--rm-border)] text-sm shadow-2xs">
                                <i class="ph-bold ph-map-pin"></i>
                            </span>
                            <h2 id="drawer-ubicacion-title" class="rm-drawer-title">Ubicación y Ficha del Residente</h2>
                        </div>
                        <p class="rm-drawer-subtitle">
                            Asignación física de cama, entorno habitacional y condiciones asistenciales
                        </p>
                    </div>
                    <button type="button"
                            wire:click="cerrarDrawer"
                            class="rm-btn-icon text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] p-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer"
                            aria-label="Cerrar panel de ubicación">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
            </header>

            {{-- BODY CON SCROLL EXCLUSIVO (100% NÍTIDO, CÁLIDO) --}}
            <div class="rm-drawer-body">
                
                {{-- 3. CARD DEL RESIDENTE (Horizontal Compacta) --}}
                <div class="rm-drawer-resident">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($adultoDrawer->foto_url)
                            <img src="{{ $adultoDrawer->foto_url }}" alt="{{ $adultoDrawer->nombre_completo }}" class="h-11 w-11 flex-shrink-0 rounded-xl object-cover border border-[var(--rm-border)] shadow-xs" />
                        @else
                            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-[#1E3A8A] text-white font-black text-sm shadow-xs">
                                {{ substr($adultoDrawer->nombres, 0, 1) }}{{ substr($adultoDrawer->ap_paterno, 0, 1) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h4 class="font-bold text-[var(--rm-text-title)] text-sm leading-tight truncate uppercase">
                                {{ $adultoDrawer->ap_paterno }} {{ $adultoDrawer->ap_materno }} {{ $adultoDrawer->nombres }}
                            </h4>
                            <div class="flex items-center gap-2 text-xs text-[var(--rm-text-muted)] mt-0.5 flex-wrap">
                                <span>{{ $adultoDrawer->edad_texto }}</span>
                                <span>•</span>
                                <span>CI: {{ $adultoDrawer->ci ?: 'Documento S/D' }}</span>
                                <span>•</span>
                                <span class="font-mono text-[11px]">{{ $adultoDrawer->cod_am }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-right flex-shrink-0">
                        <span class="inline-block px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-title)] font-semibold text-xs">
                            {{ $adultoDrawer->ubicacion_texto }}
                        </span>
                        <div class="mt-1 flex items-center justify-end gap-1.5">
                            <span class="text-[10.5px] text-[var(--rm-text-muted)]">Estado:</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded-full text-[10px] font-bold border {{ $adultoDrawer->estado_badge_color }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ $adultoDrawer->estado_humano }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Ubicación Asistencial Física --}}
                <div class="rm-drawer-card space-y-3">
                    <div class="flex items-center justify-between border-b border-[var(--rm-border)]/60 pb-2">
                        <span class="rm-drawer-section">
                            <i class="ph-bold ph-bed text-blue-600 text-sm"></i>
                            <span>Asignación de Entorno y Cama</span>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10.5px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            <i class="ph-bold ph-check text-xs"></i>
                            Asignación Vigente
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                        <div class="p-2.5 bg-[var(--rm-surface-alt)] rounded-xl border border-[var(--rm-border)] space-y-1">
                            <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Habitación</span>
                            <p class="font-black text-[var(--rm-text-title)] text-sm">
                                {{ $adultoDrawer->habitacion_texto }}
                            </p>
                            @if($adultoDrawer->habitacion?->codigo)
                                <p class="text-[10.5px] text-[var(--rm-text-muted)] font-mono">Cód. {{ $adultoDrawer->habitacion->codigo }}</p>
                            @endif
                        </div>

                        <div class="p-2.5 bg-[var(--rm-surface-alt)] rounded-xl border border-[var(--rm-border)] space-y-1">
                            <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Cama Clínica</span>
                            <p class="font-black text-[var(--rm-text-title)] text-sm">
                                {{ $adultoDrawer->cama_texto }}
                            </p>
                            <p class="text-[10.5px] text-emerald-600 font-bold">Posición activa</p>
                        </div>

                        <div class="p-2.5 bg-[var(--rm-surface-alt)] rounded-xl border border-[var(--rm-border)] sm:col-span-2 space-y-1">
                            <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)] block">Pabellón / Sector</span>
                            <p class="font-bold text-[var(--rm-text-title)] flex items-center gap-1.5 text-xs">
                                <i class="ph-bold ph-compass text-blue-600"></i>
                                {{ $adultoDrawer->habitacion?->ubicacion ?? 'Pabellón Central' }}
                            </p>
                            <p class="text-[10.5px] text-[var(--rm-text-muted)]">Tipo: {{ $adultoDrawer->habitacion?->tipo_habitacion ?? 'INDIVIDUAL' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Bloque Doble: Condiciones y Riesgos Asistenciales --}}
                <div class="rm-drawer-clinical-grid">
                    <div class="rm-drawer-card space-y-2">
                        <span class="rm-drawer-section">
                            <i class="ph-bold ph-shield-warning text-amber-600 text-sm"></i>
                            <span>Riesgos Clínicos</span>
                        </span>
                        <div class="rm-drawer-def-list">
                            <div class="rm-drawer-def-item">
                                <span class="rm-drawer-def-label">Riesgo caídas:</span>
                                <span class="rm-drawer-def-value text-amber-600 font-bold">Moderado</span>
                            </div>
                            <div class="rm-drawer-def-item">
                                <span class="rm-drawer-def-label">Movilidad:</span>
                                <span class="rm-drawer-def-value">{{ $adultoDrawer->movilidad ?: 'Requiere asistencia' }}</span>
                            </div>
                            <div class="rm-drawer-def-item">
                                <span class="rm-drawer-def-label">Alergias:</span>
                                <span class="rm-drawer-def-value text-rose-600 font-bold">{{ $adultoDrawer->alergias ?: 'Sin alergias' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="rm-drawer-card space-y-2">
                        <span class="rm-drawer-section">
                            <i class="ph-bold ph-user-gear text-blue-600 text-sm"></i>
                            <span>Personal Asignado</span>
                        </span>
                        <div class="rm-drawer-def-list">
                            <div class="rm-drawer-def-item">
                                <span class="rm-drawer-def-label">Enfermero/a:</span>
                                <span class="rm-drawer-def-value">{{ $adultoDrawer->asignacionTurnoActiva?->enfermero?->name ?? 'Enfermería de Turno' }}</span>
                            </div>
                            <div class="rm-drawer-def-item">
                                <span class="rm-drawer-def-label">Médico tratante:</span>
                                <span class="rm-drawer-def-value">{{ $adultoDrawer->medico_tratante ?? 'Dr. de Cabecera' }}</span>
                            </div>
                            <div class="rm-drawer-def-item">
                                <span class="rm-drawer-def-label">Nivel de cuidado:</span>
                                <span class="rm-drawer-def-value">{{ $adultoDrawer->nivel_cuidado ?? 'Nivel III' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- FOOTER FIJO (Acciones según contexto) --}}
            <footer class="rm-drawer-footer">
                <div class="flex items-center gap-2">
                    <button type="button"
                            wire:click="verGraficos('{{ $adultoDrawer->cod_am }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] text-xs font-semibold text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer shadow-2xs">
                        <i class="ph-bold ph-chart-line-up text-sm text-[var(--rm-text-muted)]"></i>
                        <span>Ver evolución y constantes</span>
                    </button>

                    @if(\Illuminate\Support\Facades\Route::has('admin.cuidados.pacientes.ficha'))
                        <a href="{{ route('admin.cuidados.pacientes.ficha', $adultoDrawer->cod_am) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] text-xs font-semibold text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer shadow-2xs">
                            <i class="ph-bold ph-user-circle text-sm text-[var(--rm-text-muted)]"></i>
                            <span>Ver ficha médica</span>
                        </a>
                    @endif
                </div>

                <button type="button"
                        wire:click="cerrarDrawer"
                        class="px-3.5 py-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] text-xs font-semibold text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer">
                    Cerrar panel
                </button>
            </footer>
        </div>
    </div>
</div>
@endif
