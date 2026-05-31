<div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm">
    <div class="mb-5 flex items-center justify-between border-b border-[#CBBBAA]/30 pb-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#A86B3C]/10 text-[#A86B3C]">
                <i class="ph-bold ph-handshake text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-[#2F3E5C]">Participación Institucional</h2>
                <p class="text-xs font-bold text-[#2F3E5C]/50 uppercase tracking-wide">Actividades, terapias y eventos</p>
            </div>
        </div>
    </div>

    @if($actividadesLista->count() > 0)
        <div class="space-y-6">
            
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-center">
                <div class="rounded-xl border border-[#D5C7B9]/60 bg-[#F2EBE3]/50 p-3">
                    <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Actividades Asignadas</span>
                    <p class="text-xl font-black text-[#A86B3C]">{{ $actividadesLista->count() }}</p>
                </div>
                <div class="rounded-xl border border-[#D5C7B9]/60 bg-[#F2EBE3]/50 p-3">
                    <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Actividades Asistidas</span>
                    <p class="text-xl font-black text-[#617453]">
                        {{ $actividadesLista->whereIn('estado', ['COMPLETADA', 'REALIZADA'])->count() }}
                    </p>
                </div>
                <div class="rounded-xl border border-[#D5C7B9]/60 bg-[#F2EBE3]/50 p-3 sm:col-span-1 col-span-2">
                    <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Última Participación</span>
                    <p class="text-xs font-black text-[#2F3E5C] mt-2">
                        @php $ultimaAct = $actividadesLista->first(); @endphp
                        {{ optional($ultimaAct->tipoActividad)->tipo ?? 'Actividad' }}
                        <span class="block mt-0.5 text-xs text-[#2F3E5C]/50 font-normal">{{ \Carbon\Carbon::parse($ultimaAct->fecha)->format('d/m/Y') }}</span>
                    </p>
                </div>
            </div>

            <div>
                <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-3">Participaciones Recientes</span>
                <div class="space-y-3">
                    @foreach($actividadesLista->take(5) as $act)
                        <div class="flex items-center gap-4 rounded-xl border border-[#D5C7B9]/50 bg-white p-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#A86B3C]/10 text-[#A86B3C] shrink-0">
                                <i class="ph-bold ph-calendar-check"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-black text-[#2F3E5C] truncate">{{ optional($act->tipoActividad)->tipo ?? 'Actividad' }}</p>
                                <p class="text-xs font-bold text-[#2F3E5C]/50 mt-0.5">{{ \Carbon\Carbon::parse($act->fecha)->format('d/m/Y') }} {{ $act->hora ? ' • '.substr($act->hora, 0, 5) : '' }}</p>
                            </div>
                            <span class="shrink-0 inline-block px-2 py-0.5 rounded text-xs font-black uppercase tracking-wide {{ in_array($act->estado, ['COMPLETADA', 'REALIZADA']) ? 'bg-[#8EA17D]/15 text-[#617453]' : 'bg-amber-100 text-amber-700' }}">
                                {{ $act->estado ?? 'PROGRAMADA' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-xs font-bold text-[#2F3E5C]/50 uppercase tracking-wide">Para actualizar esta información, utilice el módulo correspondiente.</p>
            </div>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#F2EBE3]/50 text-[#2F3E5C]/30 mb-4">
                <i class="ph-bold ph-handshake text-3xl"></i>
            </div>
            <p class="text-sm font-bold text-[#2F3E5C]/60">No hay participación registrada.</p>
            <p class="mt-2 text-xs font-bold text-[#2F3E5C]/40 uppercase tracking-wide">Gestione desde el módulo correspondiente.</p>
        </div>
    @endif
</div>
