@php
    $isResponsable = $fam['responsable'];
    $isContacto = $fam['emergencia'];
    $bgColor = $isResponsable ? 'bg-[#FEF2F2]' : ($isContacto ? 'bg-[#FEF3C7]' : 'bg-[#F0FDF4]');
    $borderColor = $isResponsable ? 'border-[#F9735B]' : ($isContacto ? 'border-[#D9A441]' : 'border-[#7A9B76]');
    $textColor = $isResponsable ? 'text-[#991B1B]' : ($isContacto ? 'text-[#92400E]' : 'text-[#166534]');
    $badgeBg = $isResponsable ? 'bg-[#F9735B]/10' : ($isContacto ? 'bg-[#D9A441]/10' : 'bg-[#7A9B76]/10');
    $badgeText = $isResponsable ? 'text-[#F9735B]' : ($isContacto ? 'text-[#D9A441]' : 'text-[#7A9B76]');
    $badgeLabel = $isResponsable ? 'Responsable' : ($isContacto ? 'Contacto' : 'Familiar');
@endphp
<button type="button" class="nodo-familiar flex flex-col items-center group cursor-pointer transition-transform hover:scale-105 relative border-none bg-transparent"
     data-responsable="{{ $isResponsable ? 'true' : 'false' }}"
     data-contacto="{{ $isContacto ? 'true' : 'false' }}"
     wire:click.stop="abrirDetalleVinculo('familiar', '{{ $fam['cod_fam'] }}')"
     :class="{ 'opacity-100 scale-105 z-20': nodoActivo === 'familiar-{{ $fam['cod_fam'] }}', 'opacity-40': nodoActivo && nodoActivo !== 'familiar-{{ $fam['cod_fam'] }}' }"
>
    <div class="flex {{ $isResponsable ? 'h-14 w-14' : 'h-12 w-12' }} items-center justify-center rounded-full {{ $bgColor }} border-[3px] {{ $borderColor }} {{ $textColor }} shadow-sm z-10 relative bg-white">
        @if($isResponsable)
            <div class="absolute -top-2 -right-2 bg-white rounded-full p-0.5 shadow-sm">
                <i class="ph-fill ph-star text-[#F9735B] text-sm"></i>
            </div>
        @endif
        <span class="{{ $isResponsable ? 'text-lg' : 'text-base' }} font-black">{{ $fam['iniciales'] }}</span>
    </div>
    <div class="mt-1.5 text-center bg-white/90 px-2 py-1 rounded-lg backdrop-blur-sm max-w-[100px] shadow-sm border border-slate-100">
        <p class="text-[11px] font-black text-slate-700 leading-tight truncate w-full">{{ $fam['nombre'] }}</p>
        <p class="mt-0.5 text-[9px] font-bold text-slate-500 truncate w-full">{{ $fam['parentesco'] }}</p>
        <span class="mt-1 inline-block rounded-full {{ $badgeBg }} px-2 py-0.5 text-[8.5px] font-black {{ $badgeText }}">{{ $badgeLabel }}</span>
    </div>
</button>
