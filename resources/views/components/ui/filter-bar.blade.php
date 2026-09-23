{{--
 Componente: ui/filter-bar
 Uso: <x-ui.filter-bar> ... filtros ... </x-ui.filter-bar>
--}}
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-[#D5CABE] dark:border-[#51483F] bg-[#F0E8DE] dark:bg-[#201E1C] p-3 sm:p-4 shadow-[0_6px_18px_rgba(70,55,45,0.05)] transition-all duration-200']) }}>
    <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
        {{ $slot }}
    </div>
</div>