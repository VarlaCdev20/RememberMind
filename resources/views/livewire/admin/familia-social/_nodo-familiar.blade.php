@php
 $isResponsable = $fam['responsable'];
 $isContacto = $fam['emergencia'];
 $bgColor = $isResponsable ? 'bg-fondo-panel' : ($isContacto ? 'bg-fondo-panel' : 'bg-fondo-panel');
 $borderColor = $isResponsable ? 'border-borde' : ($isContacto ? 'border-borde' : 'border-borde');
 $textColor = $isResponsable ? 'text-parrafo' : ($isContacto ? 'text-parrafo' : 'text-parrafo');
 $badgeBg = $isResponsable ? 'bg-fondo-panel' : ($isContacto ? 'bg-fondo-panel' : 'bg-fondo-panel');
 $badgeText = $isResponsable ? 'text-parrafo' : ($isContacto ? 'text-parrafo' : 'text-parrafo');
 $badgeLabel = $isResponsable ? 'Responsable' : ($isContacto ? 'Contacto' : 'Familiar');
@endphp
<button type="button" class="nodo-familiar flex flex-col items-center group cursor-pointer transition-transform hover:scale-105 relative border-none bg-transparent"
 data-responsable="{{ $isResponsable ? 'true' : 'false' }}"
 data-contacto="{{ $isContacto ? 'true' : 'false' }}"
 wire:click.stop="abrirDetalleVinculo('familiar', '{{ $fam['cod_fam'] }}')"
 :class="{ 'opacity-100 scale-105 z-20': nodoActivo === 'familiar-{{ $fam['cod_fam'] }}', 'opacity-40': nodoActivo && nodoActivo !== 'familiar-{{ $fam['cod_fam'] }}' }"
>
 <div class="flex {{ $isResponsable ? 'h-14 w-14' : 'h-12 w-12' }} items-center justify-center rounded-full {{ $bgColor }} border-[3px] {{ $borderColor }} {{ $textColor }} shadow-sm z-10 relative bg-fondo-card">
 @if($isResponsable)
 <div class="absolute -top-2 -right-2 bg-fondo-card rounded-full p-0.5 shadow-sm">
 <i class="ph-fill ph-star text-parrafo text-sm"></i>
 </div>
 @endif
 <span class="{{ $isResponsable ? 'text-lg' : 'text-base' }} font-black">{{ $fam['iniciales'] }}</span>
 </div>
 <div class="mt-1.5 text-center bg-fondo-card/90 px-2 py-1 rounded-lg backdrop-blur-sm max-w-[100px] shadow-sm border border-slate-100">
 <p class="text-[11px] font-bold text-slate-700 leading-tight truncate w-full">{{ $fam['nombre'] }}</p>
 <p class="mt-0.5 text-[9px] font-bold text-slate-500 truncate w-full">{{ $fam['parentesco'] }}</p>
 <span class="mt-1 inline-block rounded-full {{ $badgeBg }} px-2 py-0.5 text-[8.5px] font-black {{ $badgeText }}">{{ $badgeLabel }}</span>
 </div>
</button>
