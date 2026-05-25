<div class="space-y-5 animate-in fade-in duration-300">
    {{-- CABECERA CLÍNICA GENERAL --}}
    <section class="overflow-hidden rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/78 shadow-sm backdrop-blur-xl">
        <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-[#C7B5A3]/55 bg-[#E6DDD3]/70 text-[#E27D60] shadow-sm">
                    <i class="ph-bold ph-stethoscope text-3xl"></i>
                </span>
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-[#2F3E5C]">Fichas Médicas</h2>
                    <p class="mt-1 text-sm font-bold leading-relaxed text-[#2F3E5C]/62">
                        Registro clínico general, expedientes médicos, alergias y condiciones institucionales.
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-2 rounded-xl border border-[#C7B5A3]/45 bg-white/60 px-4 py-2 text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]">
                    <i class="ph-bold ph-calendar-check text-[#E27D60]"></i>
                    {{ now()->format('d/m/Y') }}
                </span>
            </div>
        </div>
    </section>

    {{-- CARDS GENERALES DE ESTADISTICAS --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <div class="rounded-2xl border border-[#C7B5A3]/45 bg-[#E6DDD3]/50 p-4 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <i class="ph-bold ph-users text-2xl text-[#2F3E5C]"></i>
            </div>
            <div class="mt-3">
                <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Total Residentes</p>
                <p class="text-xl font-black text-[#2F3E5C]">{{ $stats['total'] }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-[#8DA280]/40 bg-[#8DA280]/10 p-4 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <i class="ph-bold ph-file-text text-2xl text-[#63775B]"></i>
                <span class="rounded-full bg-[#8DA280]/20 px-2 py-0.5 text-[8px] font-black uppercase tracking-wider text-[#63775B]">Registradas</span>
            </div>
            <div class="mt-3">
                <p class="text-[9px] font-black uppercase tracking-widest text-[#63775B]/70">Fichas Activas</p>
                <p class="text-xl font-black text-[#63775B]">{{ $stats['con_ficha'] }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-[#C45F4B]/30 bg-[#C45F4B]/5 p-4 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <i class="ph-bold ph-file-dashed text-2xl text-[#C45F4B]"></i>
                <span class="rounded-full bg-[#C45F4B]/10 px-2 py-0.5 text-[8px] font-black uppercase tracking-wider text-[#C45F4B]">Pendientes</span>
            </div>
            <div class="mt-3">
                <p class="text-[9px] font-black uppercase tracking-widest text-[#C45F4B]/70">Sin Ficha Base</p>
                <p class="text-xl font-black text-[#C45F4B]">{{ $stats['sin_ficha'] }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-[#E27D60]/30 bg-[#E27D60]/5 p-4 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <i class="ph-bold ph-warning-circle text-2xl text-[#E27D60]"></i>
            </div>
            <div class="mt-3">
                <p class="text-[9px] font-black uppercase tracking-widest text-[#E27D60]/70">Alertas / Alergias</p>
                <p class="text-xl font-black text-[#E27D60]">{{ $stats['alergias'] }} reportes</p>
            </div>
        </div>

        <div class="rounded-2xl border border-[#D9A05B]/30 bg-[#D9A05B]/5 p-4 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <i class="ph-bold ph-fork-knife text-2xl text-[#D9A05B]"></i>
            </div>
            <div class="mt-3">
                <p class="text-[9px] font-black uppercase tracking-widest text-[#D9A05B]/80">Dietas / Restr.</p>
                <p class="text-xl font-black text-[#D9A05B]">{{ $stats['cuidados'] }} activas</p>
            </div>
        </div>
    </div>

    {{-- FILTROS Y BÚSQUEDA --}}
    <section class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-4 shadow-sm backdrop-blur-xl">
        <div class="grid gap-4 md:grid-cols-[1fr_auto]">
            <div class="relative">
                <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[#2F3E5C]/40"></i>
                <input type="text" wire:model.live.debounce.300ms="searchGeneral" placeholder="Buscar residente por nombre, apellido o CI..." class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white py-2.5 pl-10 pr-4 text-xs font-bold text-[#2F3E5C] outline-none transition placeholder:text-[#2F3E5C]/40 focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
            </div>
            <div class="flex gap-2 overflow-x-auto pb-1 md:pb-0">
                <button wire:click="$set('filtroEstado', 'todas')" class="whitespace-nowrap rounded-xl border {{ $filtroEstado === 'todas' ? 'border-[#2F3E5C] bg-[#2F3E5C] text-white shadow-sm' : 'border-[#C7B5A3]/70 bg-white text-[#2F3E5C]' }} px-4 py-2 text-[10px] font-black uppercase tracking-wider transition hover:bg-[#D5C7B9]">
                    Todas
                </button>
                <button wire:click="$set('filtroEstado', 'con_ficha')" class="whitespace-nowrap rounded-xl border {{ $filtroEstado === 'con_ficha' ? 'border-[#8DA280] bg-[#8DA280] text-white shadow-sm' : 'border-[#C7B5A3]/70 bg-white text-[#63775B]' }} px-4 py-2 text-[10px] font-black uppercase tracking-wider transition hover:bg-[#D5C7B9]">
                    Registradas
                </button>
                <button wire:click="$set('filtroEstado', 'sin_ficha')" class="whitespace-nowrap rounded-xl border {{ $filtroEstado === 'sin_ficha' ? 'border-[#C45F4B] bg-[#C45F4B] text-white shadow-sm' : 'border-[#C7B5A3]/70 bg-white text-[#C45F4B]' }} px-4 py-2 text-[10px] font-black uppercase tracking-wider transition hover:bg-[#D5C7B9]">
                    Sin Ficha
                </button>
            </div>
        </div>
    </section>

    {{-- LISTADO DE FICHAS MÉDICAS --}}
    <section class="overflow-hidden rounded-[1.6rem] border border-[#C7B5A3]/65 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#2F3E5C]">
                <thead class="border-b border-[#C7B5A3]/45 bg-[#E6DDD3]/40 text-[9px] font-black uppercase tracking-wider text-[#2F3E5C]/60">
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
                        <tr class="hover:bg-[#F8F3ED]/60 transition">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 shrink-0 overflow-hidden rounded-xl border-[2px] border-[#F8F3ED]/80 bg-[#2F3E5C] shadow-sm">
                                        @if($paciente->foto)
                                            <img src="{{ Storage::url($paciente->foto) }}" alt="{{ $paciente->nombres }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#2F3E5C] to-[#5B5F97] text-[10px] font-black text-white">
                                                {{ substr($paciente->nombres, 0, 1) }}{{ substr($paciente->ap_paterno, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-[#2F3E5C]">{{ $paciente->nombres }} {{ $paciente->ap_paterno }}</p>
                                        <p class="text-[10px] font-bold text-[#2F3E5C]/60">{{ $paciente->cod_am }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                @if($ficha)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#8DA280]/15 px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-[#63775B]">
                                        <i class="ph-bold ph-check-circle"></i> Registrada
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#C45F4B]/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-[#C45F4B]">
                                        <i class="ph-bold ph-warning-circle"></i> Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if($ficha && !empty($ficha->alergias))
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#C45F4B]">
                                        <i class="ph-bold ph-warning"></i> Registradas
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-[#2F3E5C]/40">Ninguna</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if($ficha && !empty($ficha->restricciones_alimentarias))
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#D9A05B]">
                                        <i class="ph-bold ph-fork-knife"></i> Dieta Especial
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-[#2F3E5C]/40">Normal</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-[10px] font-bold text-[#2F3E5C]/60">
                                {{ $ficha ? $ficha->updated_at?->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.salud-seguimiento.ficha', $paciente->cod_am) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#E6DDD3]/80 px-4 py-2 text-[10px] font-black uppercase tracking-wider text-[#2F3E5C] shadow-sm transition hover:bg-[#E27D60] hover:text-white active:scale-95">
                                    <i class="ph-bold ph-folder-open"></i> Ver Ficha
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <i class="ph-bold ph-users-three text-4xl text-[#2F3E5C]/20 mb-3 block"></i>
                                <h3 class="text-sm font-black text-[#2F3E5C]">No se encontraron pacientes</h3>
                                <p class="text-xs font-bold text-[#2F3E5C]/50 mt-1">Prueba cambiando el filtro o término de búsqueda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pacientes->hasPages())
            <div class="border-t border-[#C7B5A3]/40 bg-[#F8F3ED]/40 p-4">
                {{ $pacientes->links() }}
            </div>
        @endif
    </section>
</div>
