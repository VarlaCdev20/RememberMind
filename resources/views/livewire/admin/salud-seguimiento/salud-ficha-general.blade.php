<div class="space-y-5 animate-in fade-in duration-300">
    {{-- CABECERA CLÍNICA GENERAL --}}
    <section class="overflow-hidden rounded-[1.6rem] border border-borde/65 bg-fondo-panel shadow-sm backdrop-blur-xl">
        <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
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
                <span class="inline-flex items-center gap-2 rounded-xl border border-borde/45 bg-fondo-card/60 px-4 py-2 text-[10px] font-black uppercase tracking-wider text-parrafo">
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
                <p class="text-[9px] font-black uppercase tracking-widest text-meta">Total Residentes</p>
                <p class="text-xl font-black text-parrafo">{{ $stats['total'] }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-estado-exitoBorde bg-estado-exitoBg p-4 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <i class="ph-bold ph-file-text text-2xl text-estado-exito"></i>
                <span class="rounded-full bg-estado-exitoBg px-2 py-0.5 text-[8px] font-black uppercase tracking-wider text-estado-exito">Registradas</span>
            </div>
            <div class="mt-3">
                <p class="text-[9px] font-black uppercase tracking-widest text-estado-exito">Fichas Activas</p>
                <p class="text-xl font-black text-estado-exito">{{ $stats['con_ficha'] }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <i class="ph-bold ph-file-dashed text-2xl text-parrafo"></i>
                <span class="rounded-full bg-fondo-panel px-2 py-0.5 text-[8px] font-black uppercase tracking-wider text-parrafo">Pendientes</span>
            </div>
            <div class="mt-3">
                <p class="text-[9px] font-black uppercase tracking-widest text-apoyo">Sin Ficha Base</p>
                <p class="text-xl font-black text-parrafo">{{ $stats['sin_ficha'] }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-borde-focus bg-estado-peligroBg p-4 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <i class="ph-bold ph-warning-circle text-2xl text-boton-acento"></i>
            </div>
            <div class="mt-3">
                <p class="text-[9px] font-black uppercase tracking-widest text-boton-acento">Alertas / Alergias</p>
                <p class="text-xl font-black text-boton-acento">{{ $stats['alergias'] }} reportes</p>
            </div>
        </div>

        <div class="rounded-2xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-4 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <i class="ph-bold ph-fork-knife text-2xl text-estado-advertencia"></i>
            </div>
            <div class="mt-3">
                <p class="text-[9px] font-black uppercase tracking-widest text-estado-advertencia">Dietas / Restr.</p>
                <p class="text-xl font-black text-estado-advertencia">{{ $stats['cuidados'] }} activas</p>
            </div>
        </div>
    </div>

    {{-- FILTROS Y BÚSQUEDA --}}
    <section class="rounded-[1.6rem] border border-borde/65 bg-fondo-panel p-4 shadow-sm backdrop-blur-xl">
        <div class="grid gap-4 md:grid-cols-[1fr_auto]">
            <div class="relative">
                <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                <input type="text" wire:model.live.debounce.300ms="searchGeneral" placeholder="Buscar residente por nombre, apellido o CI..." class="w-full rounded-xl border border-borde/70 bg-fondo-card py-2.5 pl-10 pr-4 text-xs font-bold text-parrafo outline-none transition placeholder:text-meta focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
            </div>
            <div class="flex gap-2 overflow-x-auto pb-1 md:pb-0">
                <button wire:click="$set('filtroEstado', 'todas')" class="whitespace-nowrap rounded-xl border {{ $filtroEstado === 'todas' ? 'border-borde-fuerte bg-boton-principal text-inverso shadow-sm' : 'border-borde/70 bg-fondo-card text-parrafo' }} px-4 py-2 text-[10px] font-black uppercase tracking-wider transition hover:bg-fondo-app">
                    Todas
                </button>
                <button wire:click="$set('filtroEstado', 'con_ficha')" class="whitespace-nowrap rounded-xl border {{ $filtroEstado === 'con_ficha' ? 'border-estado-exitoBorde bg-estado-exitoBg text-inverso shadow-sm' : 'border-borde/70 bg-fondo-card text-estado-exito' }} px-4 py-2 text-[10px] font-black uppercase tracking-wider transition hover:bg-fondo-app">
                    Registradas
                </button>
                <button wire:click="$set('filtroEstado', 'sin_ficha')" class="whitespace-nowrap rounded-xl border {{ $filtroEstado === 'sin_ficha' ? 'border-borde bg-fondo-panel text-inverso shadow-sm' : 'border-borde/70 bg-fondo-card text-parrafo' }} px-4 py-2 text-[10px] font-black uppercase tracking-wider transition hover:bg-fondo-app">
                    Sin Ficha
                </button>
            </div>
        </div>
    </section>

    {{-- LISTADO DE FICHAS MÉDICAS --}}
    <section class="overflow-hidden rounded-[1.6rem] border border-borde/65 bg-fondo-card shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-parrafo">
                <thead class="border-b border-borde/45 bg-fondo-panel text-[9px] font-black uppercase tracking-wider text-apoyo">
                    <tr>
                        <th class="px-5 py-4">Adulto Mayor</th>
                        <th class="px-5 py-4">Estado Ficha</th>
                        <th class="px-5 py-4">Alergias</th>
                        <th class="px-5 py-4">Restricciones</th>
                        <th class="px-5 py-4">Última Act.</th>
                        <th class="px-5 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#C7B5A3]/25">
                    @forelse($pacientes as $paciente)
                        @php
                            $ficha = $paciente->fichasMedicas->first();
                        @endphp
                        <tr class="hover:bg-fondo-panel transition">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 shrink-0 overflow-hidden rounded-xl border-[2px] border-borde bg-boton-principal shadow-sm">
                                        @if($paciente->foto)
                                            <img src="{{ Storage::url($paciente->foto) }}" alt="{{ $paciente->nombres }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#2F3E5C] to-[#5B5F97] text-[10px] font-black text-inverso">
                                                {{ substr($paciente->nombres, 0, 1) }}{{ substr($paciente->ap_paterno, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-parrafo">{{ $paciente->nombres }} {{ $paciente->ap_paterno }}</p>
                                        <p class="text-[10px] font-bold text-apoyo">{{ $paciente->cod_am }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                @if($ficha)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-estado-exitoBg px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-estado-exito">
                                        <i class="ph-bold ph-check-circle"></i> Registrada
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-fondo-panel px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-parrafo">
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
                                <a href="{{ route('admin.salud-seguimiento.ficha', $paciente->cod_am) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-panel px-4 py-2 text-[10px] font-black uppercase tracking-wider text-parrafo shadow-sm transition hover:bg-boton-acento hover:text-inverso active:scale-95">
                                    <i class="ph-bold ph-folder-open"></i> Ver Ficha
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <i class="ph-bold ph-users-three text-4xl text-parrafo/20 mb-3 block"></i>
                                <h3 class="text-sm font-black text-parrafo">No se encontraron pacientes</h3>
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
