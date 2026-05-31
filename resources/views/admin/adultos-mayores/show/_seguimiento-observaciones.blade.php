{{-- TAB PARTICIPACIÓN Y ACTIVIDADES (RESUMEN) --}}
<section
    x-show="tab === 'seguimiento'"
    style="display: none;"
    x-transition.opacity.duration.250ms
    class="space-y-6"
>
    <!-- HEADER BLOCK -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#CBBBAA]/30 pb-5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#6873A6]/10 text-[#6873A6]">
                <i class="ph-fill ph-notebook text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-[#2F3E5C]">Notas Relevantes</h2>
                <p class="text-sm font-semibold text-[#2F3E5C]/60">Resumen de observaciones institucionales y seguimiento diario.</p>
            </div>
        </div>

    </div>

    <!-- METRICS GRID -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-center text-center">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/50">Total Notas Generadas</p>
            <p class="mt-2 text-3xl font-black text-[#6873A6]">{{ $observacionesActivas->count() }}</p>
        </div>

        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-center text-center">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/50">Atenciones Médicas</p>
            <p class="mt-2 text-3xl font-black text-[#9A7B60]">{{ $atencionesActivas->count() }}</p>
        </div>

        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-center text-center">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/50">Última Nota / Registro</p>
            <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                @if($observacionesActivas->count() > 0)
                    @php $ultimaObs = $observacionesActivas->first(); @endphp
                    {{ $ultimaObs->tipo_obs ?? 'Nota' }}
                    <span class="block mt-1 text-xs text-[#2F3E5C]/50 font-normal">{{ \Carbon\Carbon::parse($ultimaObs->fecha)->format('d/m/Y') }}</span>
                @else
                    --
                @endif
            </p>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm">
            <h3 class="text-lg font-black text-[#2F3E5C] border-b border-[#CBBBAA]/30 pb-4 mb-4">Notas Observacionales Recientes</h3>
            
            @if($observacionesActivas->count() > 0)
                <div class="space-y-4">
                    @foreach($observacionesActivas->take(3) as $obs)
                        <div class="flex items-start gap-3 pb-3 border-b border-[#CBBBAA]/20 last:border-0 last:pb-0">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#6873A6]/10 text-[#6873A6] shrink-0">
                                <i class="ph-bold ph-notebook"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-[#2F3E5C]">{{ $obs->tipo_obs }}</p>
                                <p class="text-xs text-[#2F3E5C]/70 mt-1 line-clamp-2">"{{ $obs->descripcion }}"</p>
                                <p class="text-[10px] font-bold text-[#2F3E5C]/40 mt-1">{{ \Carbon\Carbon::parse($obs->fecha)->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

            @else
                <div class="py-6 text-center">
                    <p class="text-sm font-bold text-[#2F3E5C]/60">Sin observaciones de seguimiento.</p>
                </div>
            @endif
        </div>
    </div>
</section>