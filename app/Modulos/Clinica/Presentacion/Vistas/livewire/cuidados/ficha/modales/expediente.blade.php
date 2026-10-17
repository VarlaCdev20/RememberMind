{{-- 9. VENTANA LATERAL FLOTANTE: EXPEDIENTE CLÍNICO INTEGRAL (SLIDE-OVER DRAWER) --}}
{{-- ========================================================================= --}}
<div x-show="drawerExpediente"
     x-cloak
     class="fixed inset-0 z-50 overflow-hidden font-sans"
     role="dialog"
     aria-modal="true"
     @keydown.escape.window="drawerExpediente = false">

    {{-- Backdrop --}}
    <div class="rm-drawer-backdrop"
         x-show="drawerExpediente"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="drawerExpediente = false"></div>

    {{-- Panel Deslizante Lateral --}}
    <div class="pointer-events-none fixed inset-y-0 right-0 z-50 flex max-w-full pl-6 sm:pl-10">
        <aside x-show="drawerExpediente"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               class="pointer-events-auto rm-drawer flex h-full w-screen max-w-[620px] flex-col bg-[var(--rm-surface)] shadow-2xl border-l border-[var(--rm-border)] overflow-hidden">

            {{-- Header Fijo --}}
            <header class="rm-drawer-header p-5 border-b border-[var(--rm-border)] bg-[var(--rm-surface)] flex items-start justify-between gap-3 shrink-0">
                <div class="space-y-1">
                    <span class="rm-drawer-badge">
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--rm-info-action)] animate-pulse"></span>
                        EXPEDIENTE CLÍNICO INTEGRAL
                    </span>
                    <div class="flex items-center gap-2 pt-0.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-info-strong)] text-base shadow-2xs">
                            <i class="ph-bold ph-identification-card"></i>
                        </span>
                        <h2 class="text-lg font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->nombres }} {{ $adultoMayor->ap_paterno }} {{ $adultoMayor->ap_materno }}</h2>
                    </div>
                    <p class="text-xs text-[var(--rm-text-muted)]">CI: {{ $adultoMayor->ci }}</p>
                </div>
                <button type="button"
                        @click="drawerExpediente = false"
                        class="p-2 rounded-xl text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </header>

            {{-- Body Scrollable --}}
            <div class="rm-drawer-body flex-1 overflow-y-auto p-5 space-y-4 text-xs">
                {{-- Bloque Ubicación Habitacional --}}
                <div class="p-4 rounded-2xl bg-[var(--rm-warning-soft)] dark:bg-[var(--rm-warning-soft)] border border-[var(--rm-warning-strong)] dark:border-[var(--rm-warning-strong)] space-y-2">
                    <span class="text-[10px] font-bold uppercase text-[var(--rm-warning-strong)] dark:text-[var(--rm-warning-strong)] tracking-wider block">Ubicación y Censo Activo</span>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Habitación</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->habitacion?->nombre ?? 'Sin asignar' }}</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Cama</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->cama?->codigo ?? 'Sin asignar' }}</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Estado Residente</span>
                            <span class="font-bold text-[var(--rm-success-strong)]">{{ $adultoMayor->estado ?: 'No registrado' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Datos Demográficos y Civiles --}}
                <div class="p-4 rounded-2xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-3">
                    <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] tracking-wider block">Filiación y Datos Personales</span>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Fecha de Nacimiento</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->fecha_nacimiento ? \Carbon\Carbon::parse($adultoMayor->fecha_nacimiento)->format('d/m/Y') : 'No registrado' }} ({{ $adultoMayor->edad }} años)</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Género</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->genero ?? 'No especificado' }}</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Lugar de Nacimiento</span>
                            <span class="font-bold text-[var(--rm-text-title)]">No registrado</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Estado Civil</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->estado_civil ?? 'No declarado' }}</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Ocupación Anterior</span>
                            <span class="font-bold text-[var(--rm-text-title)]">No registrado</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Fecha de Ingreso</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $admisionInicial?->fecha_hora_admision?->format('d/m/Y') ?: 'No registrada' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Datos de Salud y Cobertura --}}
                <div class="p-4 rounded-2xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-3">
                    <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] tracking-wider block">Perfil de Salud de Base</span>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Grupo Sanguíneo</span>
                            <span class="font-bold text-[var(--rm-danger-strong)]">{{ $grupoSanguineo ?: 'No registrado' }}</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Seguro de Salud</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $seguroActivo?->entidad ?: 'No registrado' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Alergias Conocidas</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $alergiasConocidas->isNotEmpty() ? $alergiasTexto : 'Sin alergias medicamentosas o alimentarias reportadas.' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Dieta y Restricciones</span>
                            <span class="font-bold text-[var(--rm-text-title)]">Sin registro dietético estructurado</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Fijo --}}
            <footer class="p-4 border-t border-[var(--rm-border)] bg-[var(--rm-surface)] flex items-center justify-between gap-3 shrink-0 text-xs">
                <span class="text-[10.5px] text-[var(--rm-text-muted)]">Ficha institucional RememberMind</span>
                <button type="button"
                        @click="drawerExpediente = false"
                        class="px-4 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] font-bold text-[var(--rm-text-title)] hover:bg-[var(--rm-border)] transition cursor-pointer">
                    Cerrar Panel
                </button>
            </footer>
        </aside>
    </div>
</div>


{{-- ========================================================================= --}}
