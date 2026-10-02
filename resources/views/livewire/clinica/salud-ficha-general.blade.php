<div class="space-y-5 animate-in fade-in duration-300">
 {{-- CABECERA CLÍNICA GENERAL --}}
 <section class="overflow-hidden rounded-[1.6rem] border border-borde/65 bg-fondo-panel shadow-sm backdrop-blur-xl">
 <div class="h-1.5 w-full bg-gradient-to-r from-[var(--rm-accent-terracotta)] via-[var(--rm-warning)] to-[var(--rm-action-primary)]"></div>
 <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
 <div class="flex items-center gap-4">
 <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-borde/55 bg-fondo-panel text-boton-acento shadow-sm">
 <i class="ph-bold ph-stethoscope text-3xl"></i>
 </span>
 <div>
 <h2 class="text-2xl font-black tracking-tight text-parrafo">Fichas Médicas</h2>
 <p class="mt-1 text-sm font-bold leading-relaxed text-parrafo/62">
 Registro clínico general, expedientes médicos, alergias y condiciones institucionales.
 </p>
 </div>
 </div>

 <div class="flex items-center gap-3">
 <span class="inline-flex items-center gap-2 rounded-xl border border-borde/45 bg-fondo-card/60 px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-parrafo">
 <i class="ph-bold ph-calendar-check text-boton-acento"></i>
 {{ now()->format('d/m/Y') }}
 </span>
 </div>
 </div>
 </section>

 {{-- CARDS GENERALES DE ESTADISTICAS --}}
 <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
 <div class="rounded-2xl border border-borde/45 bg-fondo-panel p-4 shadow-sm flex flex-col justify-between">
 <div class="flex items-start justify-between">
 <i class="ph-bold ph-users text-2xl text-parrafo"></i>
 </div>
 <div class="mt-3">
 <p class="text-[9px] font-bold uppercase tracking-widest text-meta">Total Residentes</p>
 <p class="text-xl font-extrabold text-parrafo">{{ $stats['total'] }}</p>
 </div>
 </div>

 <div class="rounded-2xl border border-estado-exitoBorde bg-estado-exitoBg p-4 shadow-sm flex flex-col justify-between">
 <div class="flex items-start justify-between">
 <i class="ph-bold ph-file-text text-2xl text-estado-exito"></i>
 <span class="rounded-full bg-estado-exitoBg px-2 py-0.5 text-[8px] font-black uppercase tracking-wider text-estado-exito">Registradas</span>
 </div>
 <div class="mt-3">
 <p class="text-[9px] font-bold uppercase tracking-widest text-estado-exito">Fichas Activas</p>
 <p class="text-xl font-extrabold text-estado-exito">{{ $stats['con_ficha'] }}</p>
 </div>
 </div>

 <div class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm flex flex-col justify-between">
 <div class="flex items-start justify-between">
 <i class="ph-bold ph-file-dashed text-2xl text-parrafo"></i>
 <span class="rounded-full bg-fondo-panel px-2 py-0.5 text-[8px] font-black uppercase tracking-wider text-parrafo">Pendientes</span>
 </div>
 <div class="mt-3">
 <p class="text-[9px] font-bold uppercase tracking-widest text-apoyo">Sin Ficha Base</p>
 <p class="text-xl font-extrabold text-parrafo">{{ $stats['sin_ficha'] }}</p>
 </div>
 </div>

 <div class="rounded-2xl border border-borde-focus bg-estado-peligroBg p-4 shadow-sm flex flex-col justify-between">
 <div class="flex items-start justify-between">
 <i class="ph-bold ph-warning-circle text-2xl text-boton-acento"></i>
 </div>
 <div class="mt-3">
 <p class="text-[9px] font-bold uppercase tracking-widest text-boton-acento">Alertas / Alergias</p>
 <p class="text-xl font-extrabold text-boton-acento">{{ $stats['alergias'] }} reportes</p>
 </div>
 </div>

 <div class="rounded-2xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-4 shadow-sm flex flex-col justify-between">
 <div class="flex items-start justify-between">
 <i class="ph-bold ph-fork-knife text-2xl text-estado-advertencia"></i>
 </div>
 <div class="mt-3">
 <p class="text-[9px] font-bold uppercase tracking-widest text-estado-advertencia">Dietas / Restr.</p>
 <p class="text-xl font-extrabold text-estado-advertencia">{{ $stats['cuidados'] }} activas</p>
 </div>
 </div>
 </div>

     {{-- FILTROS Y BÚSQUEDA FORMATO ALERTAS --}}
    <x-ui.filter-bar class="mb-4">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Búsqueda textual --}}
            <div class="lg:col-span-8 relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input type="text"
                    wire:model.live.debounce.300ms="searchGeneral"
                    placeholder="Buscar residente por nombre, apellido o CI..."
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                @if(!empty($searchGeneral))
                    <button type="button"
                        wire:click="$set('searchGeneral', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>

            {{-- Selector Estado Ficha --}}
            <div class="lg:col-span-4">
                <select wire:model.live="filtroEstado"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="todas">Todas las fichas</option>
                    <option value="con_ficha">Registradas</option>
                    <option value="sin_ficha">Sin Ficha</option>
                </select>
            </div>
        </div>

        @php
            $hasFiltrosActivos = !empty($searchGeneral) || ($filtroEstado !== 'todas');
        @endphp
        @if($hasFiltrosActivos)
            <div class="rm-filter-bar__active">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($searchGeneral))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Búsqueda: "{{ Str::limit($searchGeneral, 16) }}"</span>
                            <button type="button" wire:click="$set('searchGeneral', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if($filtroEstado !== 'todas')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Estado: {{ $filtroEstado === 'con_ficha' ? 'Registradas' : 'Sin Ficha' }}</span>
                            <button type="button" wire:click="$set('filtroEstado', 'todas')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <button type="button" wire:click="$set('searchGeneral', ''); $set('filtroEstado', 'todas')" class="rm-filter-bar__clear-btn">
                    <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
                    Limpiar filtros
                </button>
            </div>
        @endif
    </x-ui.filter-bar>

 {{-- LISTADO DE FICHAS MÉDICAS --}}
 <section class="overflow-hidden rounded-[1.6rem] border border-borde/65 bg-fondo-card shadow-sm">
 <div class="overflow-x-auto">
 <table class="rm-data-table rm-data-table--actions w-full text-left text-sm text-parrafo">
 <thead class="border-b border-borde/45 bg-fondo-panel text-[9px] font-bold uppercase tracking-wider text-apoyo">
 <tr>
 <th class="px-5 py-4">Adulto Mayor</th>
 <th class="px-5 py-4">Estado Ficha</th>
 <th class="px-5 py-4">Alergias</th>
 <th class="px-5 py-4">Restricciones</th>
 <th class="px-5 py-4">Última Act.</th>
 <th class="px-5 py-4 text-right">Acciones</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[var(--rm-border)]/25">
 @forelse($pacientes as $paciente)
 @php
 $ficha = $paciente->fichaResumen;
 @endphp
 <tr class="hover:bg-fondo-panel transition">
 <td class="px-5 py-3">
 <div class="flex items-center gap-3">
 <div class="h-9 w-9 shrink-0 overflow-hidden rounded-xl border-[2px] border-borde bg-boton-principal shadow-sm">
 @if($paciente->foto)
 <img src="{{ Storage::url($paciente->foto) }}" alt="{{ $paciente->nombres }}" class="h-full w-full object-cover">
 @else
 <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[var(--rm-clinical)] to-[var(--rm-violet)] text-[10px] font-bold text-inverso">
 {{ substr($paciente->nombres, 0, 1) }}{{ substr($paciente->ap_paterno, 0, 1) }}
 </div>
 @endif
 </div>
 <div>
 <p class="text-xs font-bold text-parrafo">{{ $paciente->nombres }} {{ $paciente->ap_paterno }}</p>
 <p class="text-[10px] font-bold text-apoyo">{{ $paciente->ci ? 'CI '.$paciente->ci : 'Documento no registrado' }}</p>
 </div>
 </div>
 </td>
 <td class="px-5 py-3">
 @if($ficha)
 <span class="inline-flex items-center gap-1.5 rounded-full bg-estado-exitoBg px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-estado-exito">
 <i class="ph-bold ph-check-circle"></i> Registrada
 </span>
 @else
 <span class="inline-flex items-center gap-1.5 rounded-full bg-fondo-panel px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-parrafo">
 <i class="ph-bold ph-warning-circle"></i> Pendiente
 </span>
 @endif
 </td>
 <td class="px-5 py-3">
 @if($ficha && !empty($ficha->alergias))
 <span class="inline-flex items-center gap-1 text-[10px] font-bold text-parrafo">
 <i class="ph-bold ph-warning"></i> Registradas
 </span>
 @else
 <span class="text-[10px] font-bold text-meta">Ninguna</span>
 @endif
 </td>
 <td class="px-5 py-3">
 @if($ficha && !empty($ficha->restricciones_alimentarias))
 <span class="inline-flex items-center gap-1 text-[10px] font-bold text-estado-advertencia">
 <i class="ph-bold ph-fork-knife"></i> Dieta Especial
 </span>
 @else
 <span class="text-[10px] font-bold text-meta">Normal</span>
 @endif
 </td>
 <td class="px-5 py-3 text-[10px] font-bold text-apoyo">
 {{ $ficha ? $ficha->updated_at?->format('d/m/Y') : 'N/A' }}
 </td>
 <td class="px-5 py-3 text-right">
 <a href="{{ route('admin.salud-seguimiento.ficha', $paciente->cod_residente) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-panel px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-parrafo shadow-sm transition hover:bg-boton-acento hover:text-inverso active:scale-95">
 <i class="ph-bold ph-folder-open"></i> Ver Ficha
 </a>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" class="px-5 py-12 text-center">
 <i class="ph-bold ph-users-three text-4xl text-parrafo/20 mb-3 block"></i>
 <h3 class="text-sm font-bold text-parrafo">No se encontraron pacientes</h3>
 <p class="text-xs font-bold text-meta mt-1">Prueba cambiando el filtro o término de búsqueda.</p>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 @if($pacientes->hasPages())
 <div class="border-t border-borde-suave bg-fondo-panel p-4">
 {{ $pacientes->links() }}
 </div>
 @endif
 </section>
</div>
