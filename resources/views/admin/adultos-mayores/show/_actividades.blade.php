{{-- TAB ACTIVIDADES MEJORADO --}}
<section
    x-show="tab === 'actividades'"
    x-transition.opacity.duration.250ms
    class="space-y-4"
>
    {{-- Encabezado --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
        <div class="flex flex-col gap-4 border-b border-[#D5C7B9] px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#D9A27C]">
                    Participación institucional
                </span>

                <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">
                    Actividades y apoyo voluntario
                </h2>

                <p class="mt-1 text-xs font-bold leading-5 text-[#2F3E5C]/55">
                    Controla actividades realizadas, participación del adulto mayor y asignaciones de apoyo.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <button type="button"
                        @click="abrir('actividad')"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#D9A27C] px-4 py-2.5 text-xs font-black text-white shadow-[0_10px_20px_rgba(217,162,124,0.20)] transition hover:-translate-y-0.5 hover:bg-[#C98F67] active:scale-[0.98]">
                    <i class="ph-bold ph-calendar-plus"></i>
                    Registrar actividad
                </button>

                <button type="button"
                        @click="abrir('voluntario')"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#8EA17D] px-4 py-2.5 text-xs font-black text-white shadow-[0_10px_20px_rgba(142,161,125,0.18)] transition hover:-translate-y-0.5 hover:bg-[#7C916A] active:scale-[0.98]">
                    <i class="ph-bold ph-hand-heart"></i>
                    Asignar voluntario
                </button>
            </div>
        </div>

        {{-- Métricas --}}
        <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Actividades
                </p>
                <p class="mt-2 text-2xl font-black text-[#9B6D4C]">
                    {{ $totalActividades }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Voluntarios
                </p>
                <p class="mt-2 text-2xl font-black text-[#617453]">
                    {{ $totalAsignaciones }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Estado de participación
                </p>
                <p class="mt-2 text-sm font-black {{ $totalActividades > 0 ? 'text-[#617453]' : 'text-[#D96F58]' }}">
                    {{ $totalActividades > 0 ? 'Con actividad registrada' : 'Sin actividad' }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Acción sugerida
                </p>
                <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                    {{ $totalActividades > 0 ? 'Revisar participación' : 'Registrar actividad' }}
                </p>
            </div>
        </div>
    </section>

    {{-- Contenido --}}
    <div class="grid gap-6">
        {{-- Tabla de Actividades --}}
        <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
            <div class="flex items-center justify-between gap-3 border-b border-[#D5C7B9] px-6 py-4 bg-[#F2EBE3]/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#D9A27C]/15 text-[#9B6D4C]">
                        <i class="ph-bold ph-calendar-check text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-[#2F3E5C]">Registro de Actividades</h3>
                        <p class="text-[10px] font-bold text-[#2F3E5C]/50 uppercase tracking-widest">Participación y eventos institucionales</p>
                    </div>
                </div>
                <button type="button" @click="abrir('actividad')" class="rounded-xl bg-[#D9A27C] px-4 py-2 text-xs font-black text-white hover:bg-[#C98F67] transition">
                    <i class="ph-bold ph-plus mr-1"></i> Nueva
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#D5C7B9]/40 text-[10px] uppercase tracking-widest text-[#2F3E5C]/60">
                        <tr>
                            <th class="px-6 py-3">Fecha / Hora</th>
                            <th class="px-6 py-3">Tipo Actividad</th>
                            <th class="px-6 py-3">Observación</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D5C7B9]/30">
                        @forelse($actividadesActivas as $actividad)
                            @php
                                $actividadObj = is_object($actividad) ? $actividad : null;
                                $estadoAct = strtoupper(optional($actividadObj)->estado ?? 'PROGRAMADA');
                                $colorAct = match($estadoAct) {
                                    'COMPLETADA', 'REALIZADA' => 'bg-[#8EA17D]/15 text-[#617453]',
                                    'CANCELADA' => 'bg-terracota/15 text-terracota',
                                    default => 'bg-[#D9A27C]/15 text-[#9B6D4C]',
                                };
                            @endphp
                            <tr class="group transition hover:bg-[#F2EBE3]/40">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-xs font-black text-[#2F3E5C]">{{ optional($actividadObj)->fecha ? Carbon::parse($actividadObj->fecha)->format('d/m/Y') : 'N/D' }}</p>
                                    <p class="text-[10px] font-bold text-[#2F3E5C]/50">{{ optional($actividadObj)->hora ? substr($actividadObj->hora, 0, 5) : '--:--' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-black text-[#2F3E5C]">{{ optional($actividadObj)->tipoActividad->tipo ?? 'Actividad' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-[11px] font-semibold text-[#2F3E5C]/65 line-clamp-1">{{ optional($actividadObj)->obs ?? 'Sin detalle' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[9px] font-black uppercase {{ $colorAct }}">
                                        {{ $estadoAct }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        <button type="button" @click="abrir('actividad', @js($actividad), false, true)" class="rounded-lg bg-[#2F3E5C]/5 p-2 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition">
                                            <i class="ph-bold ph-eye"></i>
                                        </button>
                                        <button type="button" @click="abrir('actividad', @js($actividad), true, false)" class="rounded-lg bg-[#2F3E5C]/5 p-2 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition">
                                            <i class="ph-bold ph-pencil-simple"></i>
                                        </button>
                                        <form action="{{ route('admin.adultos-mayores.actividades.destroy', [$idAdulto, $actividadObj->cod_act_adul ?? '0']) }}" method="POST" onsubmit="confirmarAccion(event, 'Anular registro de actividad', 'La participación en esta actividad será anulada del expediente activo.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Anular actividad" class="rounded-lg bg-terracota/5 p-2 text-terracota hover:bg-terracota hover:text-white transition">
                                                <i class="ph-bold ph-x-circle"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-xs font-bold text-[#2F3E5C]/40">Sin actividades registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Actividades Anuladas --}}
        @if(count($actividadesAnuladas) > 0)
        <div class="mt-8 border-t border-[#D5C7B9]/30 pt-6">
            <h4 class="mb-4 text-xs font-black uppercase tracking-widest text-terracota/60 flex items-center gap-2 px-4">
                <i class="ph-bold ph-x-circle"></i> Historial de Actividades Anuladas
            </h4>
            <div class="overflow-hidden rounded-[24px] border border-[#D5C7B9]/50 opacity-60 grayscale-[50%] transition-all hover:grayscale-0 hover:opacity-100 bg-[#E7DDD2]/40 backdrop-blur-sm shadow-sm mx-4">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#D5C7B9]/30 text-[9px] uppercase tracking-widest text-[#2F3E5C]/50">
                        <tr>
                            <th class="px-6 py-3">Fecha Anul.</th>
                            <th class="px-6 py-3">Actividad / Estado Prev.</th>
                            <th class="px-6 py-3 text-right">Trazabilidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D5C7B9]/20">
                        @foreach($actividadesAnuladas as $actAnu)
                            <tr class="hover:bg-white/10 transition">
                                <td class="px-6 py-3 text-xs font-black text-[#2F3E5C]/60">
                                    {{ $actAnu->deleted_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-3">
                                    <p class="text-[11px] font-black text-[#2F3E5C]/70 uppercase">{{ $actAnu->tipoActividad->tipo ?? 'Actividad' }}</p>
                                    <p class="text-[10px] font-semibold text-[#2F3E5C]/50">Estado anterior: {{ $actAnu->estado }}</p>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <form action="{{ route('admin.adultos-mayores.actividades.restore', [$adulto->cod_am, $actAnu->cod_act_adul]) }}" method="POST" onsubmit="confirmarAccion(event, 'Restaurar actividad', 'El registro de participación volverá a la vista activa del expediente.')">
                                        @csrf @method('PATCH')
                                        <button type="submit" title="Restaurar" class="rounded-lg bg-[#8EA17D]/10 p-1.5 text-[#8EA17D] hover:bg-[#8EA17D] hover:text-white transition">
                                            <i class="ph-bold ph-arrow-counter-clockwise"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Tabla de Voluntarios --}}
        <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
            <div class="flex items-center justify-between gap-3 border-b border-[#D5C7B9] px-6 py-4 bg-[#F2EBE3]/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#8EA17D]/15 text-[#617453]">
                        <i class="ph-bold ph-hand-heart text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-[#2F3E5C]">Voluntarios Asignados</h3>
                        <p class="text-[10px] font-bold text-[#2F3E5C]/50 uppercase tracking-widest">Apoyo y acompañamiento externo</p>
                    </div>
                </div>
                <button type="button" @click="abrir('voluntario')" class="rounded-xl bg-[#8EA17D] px-4 py-2 text-xs font-black text-white hover:bg-[#7C916A] transition">
                    <i class="ph-bold ph-user-plus mr-1"></i> Asignar
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#D5C7B9]/40 text-[10px] uppercase tracking-widest text-[#2F3E5C]/60">
                        <tr>
                            <th class="px-6 py-3">Voluntario</th>
                            <th class="px-6 py-3">Periodo</th>
                            <th class="px-6 py-3">Observación</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D5C7B9]/30">
                        @forelse($asignacionesLista ?? collect() as $asignacion)
                            @php
                                $asignacionObj = is_object($asignacion) ? $asignacion : null;
                                $nombreVol = optional($asignacionObj)->voluntario->persona->nombre_completo 
                                    ?? optional($asignacionObj)->voluntario->name 
                                    ?? 'Voluntario';
                                $estadoAsig = strtoupper(optional($asignacionObj)->estado ?? 'ACTIVO');
                                $colorAsig = match($estadoAsig) {
                                    'ACTIVO' => 'bg-[#8EA17D]/15 text-[#617453]',
                                    'FINALIZADO' => 'bg-[#2F3E5C]/15 text-[#2F3E5C]',
                                    default => 'bg-[#D9A27C]/15 text-[#9B6D4C]',
                                };
                            @endphp
                            <tr class="group transition hover:bg-[#F2EBE3]/40">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#8EA17D]/10 text-xs font-black text-[#617453]">
                                            {{ strtoupper(substr($nombreVol, 0, 1)) }}
                                        </div>
                                        <span class="text-xs font-black text-[#2F3E5C]">{{ $nombreVol }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-[10px] font-black text-[#2F3E5C]/60 uppercase tracking-tighter">
                                        {{ optional($asignacionObj)->fecha_asig ? Carbon::parse($asignacionObj->fecha_asig)->format('d/m/Y') : 'INICIO N/D' }}
                                        —
                                        {{ optional($asignacionObj)->fecha_fin ? Carbon::parse($asignacionObj->fecha_fin)->format('d/m/Y') : 'PRESENTE' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-[11px] font-semibold text-[#2F3E5C]/65 line-clamp-1">{{ optional($asignacionObj)->obser ?? 'Sin observación' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[9px] font-black uppercase {{ $colorAsig }}">
                                        {{ $estadoAsig }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        <button type="button" @click="abrir('voluntario', @js($asignacion), false, true)" class="rounded-lg bg-[#2F3E5C]/5 p-2 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition">
                                            <i class="ph-bold ph-eye"></i>
                                        </button>
                                        <button type="button" @click="abrir('voluntario', @js($asignacion), true, false)" class="rounded-lg bg-[#2F3E5C]/5 p-2 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition">
                                            <i class="ph-bold ph-pencil-simple"></i>
                                        </button>
                                        <button type="button" class="rounded-lg bg-terracota/5 p-2 text-terracota hover:bg-terracota hover:text-white transition">
                                            <i class="ph-bold ph-trash-simple"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-xs font-bold text-[#2F3E5C]/40">Sin voluntarios asignados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</section>

            