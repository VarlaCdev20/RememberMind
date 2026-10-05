@if($drawerUbicacion && $adultoDrawer)
<x-ui.drawer-livewire
    wire:model="drawerUbicacion"
    title="Ubicación y Ficha del Residente"
    subtitle="Asignación física de cama, entorno habitacional y condiciones asistenciales"
    badge="PANEL LATERAL DE UBICACIÓN"
    icon="ph-map-pin"
    size="xl"
    close-method="cerrarDrawer"
>
    <div class="space-y-4">
        {{-- CARD DEL RESIDENTE (Horizontal Compacta) --}}
        <div class="p-3.5 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] flex items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-3 min-w-0">
                @if($adultoDrawer->foto_url)
                    <img src="{{ $adultoDrawer->foto_url }}" alt="{{ $adultoDrawer->nombre_completo }}" class="h-11 w-11 shrink-0 rounded-xl object-cover border border-[var(--rm-border-soft)] shadow-2xs" />
                @else
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--rm-action-primary)] text-white font-bold text-sm shadow-2xs">
                        {{ substr($adultoDrawer->nombres, 0, 1) }}{{ substr($adultoDrawer->ap_paterno, 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h3 class="font-bold text-[var(--rm-text-primary)] text-sm leading-tight truncate uppercase">
                        {{ $adultoDrawer->ap_paterno }} {{ $adultoDrawer->ap_materno }} {{ $adultoDrawer->nombres }}
                    </h3>
                    <div class="flex items-center gap-2 text-xs text-[var(--rm-text-secondary)] mt-0.5 flex-wrap">
                        <span>{{ $adultoDrawer->edad_texto }}</span>
                        <span>•</span>
                        <span>CI: {{ $adultoDrawer->ci ?: 'Documento S/D' }}</span>
                        <span>•</span>
                        <span class="font-mono text-[11px]">{{ $adultoDrawer->cod_residente }}</span>
                    </div>
                </div>
            </div>

            <div class="text-right shrink-0">
                <span class="inline-block px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-[var(--rm-text-primary)] font-semibold text-xs">
                    {{ $adultoDrawer->ubicacion_texto }}
                </span>
                <div class="mt-1 flex items-center justify-end gap-1.5">
                    <span class="text-[10.5px] text-[var(--rm-text-secondary)]">Estado:</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $adultoDrawer->estado_badge_color }}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        {{ $adultoDrawer->estado_humano }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ASIGNACIÓN DE ENTORNO Y CAMA --}}
        <div class="p-4 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] space-y-3 shadow-2xs">
            <div class="flex items-center justify-between border-b border-[var(--rm-border-soft)] pb-2">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--rm-action-primary)]">
                    <i class="ph-bold ph-bed text-base text-[var(--rm-action-primary)]"></i>
                    <span>Asignación de Entorno y Cama</span>
                </div>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10.5px] font-bold bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/30">
                    <i class="ph-bold ph-check text-xs"></i>
                    Asignación Vigente
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                <div class="p-3 bg-[var(--rm-surface-soft)] rounded-xl border border-[var(--rm-border-soft)] space-y-1">
                    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-secondary)] block">Habitación</span>
                    <p class="font-bold text-[var(--rm-text-primary)] text-sm">
                        {{ $adultoDrawer->habitacion_texto }}
                    </p>
                    @if($adultoDrawer->habitacion?->codigo)
                        <p class="text-[10.5px] text-[var(--rm-text-secondary)] font-mono">Cód. {{ $adultoDrawer->habitacion->codigo }}</p>
                    @endif
                </div>

                <div class="p-3 bg-[var(--rm-surface-soft)] rounded-xl border border-[var(--rm-border-soft)] space-y-1">
                    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-secondary)] block">Cama Clínica</span>
                    <p class="font-bold text-[var(--rm-text-primary)] text-sm">
                        {{ $adultoDrawer->cama_texto }}
                    </p>
                    <p class="text-[10.5px] text-[var(--rm-success)] font-semibold">Posición activa</p>
                </div>

                <div class="p-3 bg-[var(--rm-surface-soft)] rounded-xl border border-[var(--rm-border-soft)] sm:col-span-2 space-y-1">
                    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-secondary)] block">Pabellón / Sector</span>
                    <p class="font-semibold text-[var(--rm-text-primary)] flex items-center gap-1.5 text-xs">
                        <i class="ph-bold ph-compass text-[var(--rm-action-primary)]"></i>
                        {{ $adultoDrawer->habitacion?->ubicacion ?? 'Pabellón Central' }}
                    </p>
                    <p class="text-[10.5px] text-[var(--rm-text-secondary)]">Tipo: {{ $adultoDrawer->habitacion?->tipo_habitacion ?? 'INDIVIDUAL' }}</p>
                </div>
            </div>
        </div>

        {{-- BLOQUE DOBLE: CONDICIONES Y PERSONAL ASIGNADO --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="p-3.5 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] space-y-2 shadow-2xs">
                <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[var(--rm-text-primary)] border-b border-[var(--rm-border-soft)] pb-1.5">
                    <i class="ph-bold ph-shield-warning text-[var(--rm-warning)] text-base"></i>
                    <span>Riesgos Clínicos</span>
                </div>
                <div class="space-y-1.5 text-xs pt-1">
                    <div class="flex justify-between items-center">
                        <span class="text-[var(--rm-text-secondary)]">Riesgo caídas:</span>
                        <span class="text-[var(--rm-warning)] font-bold">Moderado</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[var(--rm-text-secondary)]">Movilidad:</span>
                        <span class="text-[var(--rm-text-primary)] font-medium">{{ $adultoDrawer->movilidad ?: 'Requiere asistencia' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[var(--rm-text-secondary)]">Alergias:</span>
                        <span class="text-[var(--rm-danger)] font-bold">{{ $adultoDrawer->alergias ?: 'Sin alergias' }}</span>
                    </div>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] space-y-2 shadow-2xs">
                <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[var(--rm-text-primary)] border-b border-[var(--rm-border-soft)] pb-1.5">
                    <i class="ph-bold ph-user-gear text-[var(--rm-action-primary)] text-base"></i>
                    <span>Personal Asignado</span>
                </div>
                <div class="space-y-1.5 text-xs pt-1">
                    <div class="flex justify-between items-center">
                        <span class="text-[var(--rm-text-secondary)]">Enfermero/a:</span>
                        <span class="text-[var(--rm-text-primary)] font-medium truncate max-w-[150px]">{{ $adultoDrawer->asignacionTurnoActiva?->enfermero?->name ?? 'Enfermería de Turno' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[var(--rm-text-secondary)]">Médico tratante:</span>
                        <span class="text-[var(--rm-text-primary)] font-medium truncate max-w-[150px]">{{ $adultoDrawer->medico_tratante ?? 'No registrado' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[var(--rm-text-secondary)]">Nivel de cuidado:</span>
                        <span class="text-[var(--rm-text-primary)] font-bold">{{ $adultoDrawer->nivel_cuidado ?? 'Nivel III' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <div class="flex w-full flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <button type="button"
                        wire:click="verGraficos('{{ $adultoDrawer->cod_residente }}')"
                        class="rm-btn rm-btn-secondary rm-btn-sm cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-chart-line-up text-base"></i>
                    <span>Ver evolución y constantes</span>
                </button>

                @if(\Illuminate\Support\Facades\Route::has('admin.cuidados.pacientes.ficha'))
                    <a href="{{ route('admin.cuidados.pacientes.ficha', $adultoDrawer->cod_residente) }}"
                       class="rm-btn rm-btn-ghost rm-btn-sm cursor-pointer">
                        <i class="ph-bold ph-user-circle text-base"></i>
                        <span>Ficha médica</span>
                    </a>
                @endif
            </div>

            <button type="button"
                    wire:click="cerrarDrawer"
                    class="rm-btn rm-btn-ghost rm-btn-sm cursor-pointer">
                Cerrar panel
            </button>
        </div>
    </x-slot:footer>
</x-ui.drawer-livewire>
@endif
