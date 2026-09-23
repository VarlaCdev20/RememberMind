{{--
 Componente: ui/action-button
 Variantes: primary, secondary, navy, danger, icon
 Uso: <x-ui.action-button variant="primary" wire:click="guardar">Guardar</x-ui.action-button>
--}}
@props([
    'variant' => 'primary',
    'icono' => null,
    'tipo' => 'button',
    'loading' => null,
])

@php
$baseClasses = "inline-flex items-center justify-center font-semibold text-xs sm:text-[13px] rounded-xl transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed";

$variantClasses = match($variant) {
    'primary' => "bg-[#A35A44] hover:bg-[#8D4B37] text-[#FFF8F1] px-3.5 py-2 shadow-xs border border-[#A35A44]/20",
    'secondary' => "bg-[#F0E8DE] dark:bg-[#2A2622] hover:bg-[#E0D5C9] dark:hover:bg-[#38332E] text-[#304060] dark:text-[#E9DFD3] border border-[#D5CABE] dark:border-[#51483F] px-3.5 py-2 shadow-xs",
    'navy' => "bg-[#304060] hover:bg-[#24324D] text-[#FFF8F1] px-3.5 py-2 shadow-xs border border-[#304060]/20",
    'danger' => "bg-[#A7443B] hover:bg-[#8E3A32] text-[#FFF8F1] px-3.5 py-2 shadow-xs border border-[#A7443B]/20",
    'icon' => "h-9 w-9 bg-[#F0E8DE] dark:bg-[#2A2622] hover:bg-[#E0D5C9] dark:hover:bg-[#38332E] text-[#304060] dark:text-[#E9DFD3] border border-[#D5CABE] dark:border-[#51483F] shadow-xs",
    default => "bg-[#A35A44] hover:bg-[#8D4B37] text-[#FFF8F1] px-3.5 py-2 shadow-xs border border-[#A35A44]/20"
};
@endphp

<button type="{{ $tipo }}" {{ $attributes->merge(['class' => "$baseClasses $variantClasses"]) }} @if($loading) wire:loading.attr="disabled" @endif>
    @if($icono)
        <i class="ph-bold {{ $icono }} {{ $variant === 'icon' ? 'text-base' : 'mr-1.5 text-sm' }}"></i>
    @endif
    {{ $slot }}
</button>