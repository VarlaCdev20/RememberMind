<div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm">
    <div class="mb-5 flex items-center justify-between border-b border-[#CBBBAA]/30 pb-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E27D60]/10 text-[#E27D60]">
                <i class="ph-bold ph-identification-card text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-[#2F3E5C]">Identificación Institucional</h2>
                <p class="text-xs font-bold text-[#2F3E5C]/50 uppercase tracking-wide">Datos administrativos y demográficos</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="rounded-xl border border-[#D5C7B9]/60 bg-[#F2EBE3]/50 p-4">
            <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Nombre Completo</span>
            <p class="text-sm font-black text-[#2F3E5C]">{{ $nombreCompleto }}</p>
        </div>
        
        <div class="rounded-xl border border-[#D5C7B9]/60 bg-[#F2EBE3]/50 p-4">
            <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Cédula de Identidad</span>
            <p class="text-sm font-black text-[#2F3E5C]">{{ $ci }}</p>
        </div>

        <div class="rounded-xl border border-[#D5C7B9]/60 bg-[#F2EBE3]/50 p-4">
            <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Fecha de Nacimiento</span>
            <p class="text-sm font-black text-[#2F3E5C]">{{ $fechaNacimientoFormateada }} <span class="text-xs text-[#2F3E5C]/60">({{ $edad ? $edad . ' años' : 'N/D' }})</span></p>
        </div>

        <div class="rounded-xl border border-[#D5C7B9]/60 bg-[#F2EBE3]/50 p-4">
            <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Género</span>
            <p class="text-sm font-black text-[#2F3E5C]">{{ $genero }}</p>
        </div>

        <div class="rounded-xl border border-[#D5C7B9]/60 bg-[#F2EBE3]/50 p-4">
            <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Estado Civil</span>
            <p class="text-sm font-black text-[#2F3E5C]">{{ optional($adultoObj)->estado_civil ?? 'N/D' }}</p>
        </div>

        <div class="rounded-xl border border-[#D5C7B9]/60 bg-[#F2EBE3]/50 p-4">
            <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Nivel Educativo</span>
            <p class="text-sm font-black text-[#2F3E5C]">{{ optional($adultoObj)->nivel_educativo ?? optional($adultoObj)->nivel_educat ?? 'N/D' }}</p>
        </div>

        <div class="rounded-xl border border-[#D5C7B9]/60 bg-[#F2EBE3]/50 p-4">
            <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Fecha de Ingreso</span>
            <p class="text-sm font-black text-[#2F3E5C]">{{ $fechaIngresoFormateada }}</p>
        </div>

        <div class="rounded-xl border border-[#D5C7B9]/60 bg-[#F2EBE3]/50 p-4">
            <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Estado Institucional</span>
            <p class="text-sm font-black {{ $estadoTexto === 'ACTIVO' ? 'text-[#617453]' : 'text-[#7A5C49]' }}">{{ $estadoTexto }}</p>
        </div>
        
        @if(optional($adultoObj)->observaciones)
        <div class="rounded-xl border border-[#D5C7B9]/60 bg-[#F2EBE3]/50 p-4 sm:col-span-2 lg:col-span-3">
            <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Observación Institucional</span>
            <p class="text-sm font-medium text-[#2F3E5C] leading-relaxed">{{ optional($adultoObj)->observaciones }}</p>
        </div>
        @endif
    </div>
</div>
