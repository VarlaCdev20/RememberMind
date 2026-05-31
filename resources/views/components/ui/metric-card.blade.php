{{--
    Componente: ui/metric-card
    Uso: <x-ui.metric-card etiqueta="Total Adultos" :valor="$total" icono="ph-users" />
         <x-ui.metric-card etiqueta="Alertas" :valor="$alertas" icono="ph-warning" color-valor="text-rose-600" />
--}}
@props([
    'etiqueta'    => '',
    'valor'       => '—',
    'icono'       => null,
    'colorValor'  => 'text-terracota',
    'colorFondo'  => 'bg-[#F2EBE3]/80',
    'colorBorde'  => 'border-[#D5C7B9]',
])

<div class="rounded-[18px] border {{ $colorBorde }} {{ $colorFondo }} p-4 shadow-sm">
    @if($icono)
        <div class="flex items-center gap-2 mb-2">
            <i class="ph-bold {{ $icono }} text-base text-azul-profundo/40"></i>
            <p class="rm-metric-label">{{ $etiqueta }}</p>
        </div>
    @else
        <p class="rm-metric-label">{{ $etiqueta }}</p>
    @endif
    <p class="mt-1 text-xl font-black {{ $colorValor }}">{{ $valor }}</p>

    @if($slot->isNotEmpty())
        <div class="mt-2 text-xs font-bold text-azul-profundo/50">
            {{ $slot }}
        </div>
    @endif
</div>
