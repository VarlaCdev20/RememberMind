{{-- ENCABEZADO PRINCIPAL ADMINISTRATIVO --}}
<section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-sm backdrop-blur-xl mb-4">
    <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A27C] to-[#8EA17D]"></div>
    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            @if($fotoUrl)
                <img src="{{ $fotoUrl }}" alt="Foto" class="h-16 w-16 rounded-[16px] border-2 border-white object-cover shadow-sm">
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-[16px] bg-gradient-to-br from-[#4E5D8A] to-[#6873A6] text-2xl font-black text-white shadow-sm">
                    {{ $iniciales ?: 'AM' }}
                </div>
            @endif
            <div>
                <h1 class="text-xl font-black text-[#2F3E5C] leading-tight">{{ $nombreCompleto ?: 'Sin nombre' }}</h1>
                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs font-bold text-[#2F3E5C]/70">
                    <span>{{ $edad ? $edad . ' años' : 'Edad N/D' }}</span>
                    <span>&bull;</span>
                    <span class="{{ $estadoTexto === 'ACTIVO' ? 'text-[#617453]' : 'text-[#7A5C49]' }} uppercase">{{ $estadoTexto }}</span>
                    <span>&bull;</span>
                    <span>Ingreso: {{ $fechaIngresoFormateada }}</span>
                    @if(isset($nombrePrincipal))
                        <span>&bull;</span>
                        <span>Resp: {{ $nombrePrincipal }}</span>
                    @endif
                    <span>&bull;</span>
                    @php
                        $isExpedienteCompleto = $fichasMedicas->isNotEmpty() && $totalEvaluaciones > 0 && $totalDocumentos > 0 && $totalFamiliares > 0;
                    @endphp
                    <span class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-black uppercase tracking-wide {{ $isExpedienteCompleto ? 'bg-[#8EA17D]/20 text-[#617453]' : 'bg-amber-600/20 text-amber-700' }}">
                        Expediente {{ $isExpedienteCompleto ? 'Completo' : 'Pendiente' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.adultos-mayores.index') }}" class="rounded-xl border border-[#C7B5A3] bg-white/50 px-4 py-2 text-xs font-black transition hover:bg-white text-[#2F3E5C]">
                <i class="ph-bold ph-arrow-left"></i> Volver
            </a>
            @if($idAdulto)
                <a href="{{ route('admin.adultos-mayores.edit', $idAdulto) }}" class="rounded-xl bg-white px-4 py-2 text-xs font-black text-[#2F3E5C] border border-[#D5C7B9] shadow-sm transition hover:bg-[#F2EBE3]">
                    <i class="ph-bold ph-pencil-simple"></i> Editar datos
                </a>
            @endif
            <a href="{{ route('admin.adultos-mayores.reporte-individual', $idAdulto) }}" class="rounded-xl bg-[#2F3E5C] px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-[#1F2E4C]">
                <i class="ph-bold ph-printer"></i> Imprimir
            </a>
        </div>
    </div>
</section>