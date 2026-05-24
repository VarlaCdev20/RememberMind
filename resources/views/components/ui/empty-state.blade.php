{{--
    Componente: ui/empty-state
    Uso: <x-ui.empty-state
             icono="ph-users"
             titulo="Sin registros"
             texto="No hay datos disponibles aún."
         />
    También acepta slot para botones de acción.
--}}
@props([
    'icono'  => 'ph-folder-open',
    'titulo' => 'Sin resultados',
    'texto'  => 'No hay registros que mostrar en este momento.',
    'color'  => 'text-[#C7B5A3]',
])

<div class="rm-empty-state {{ $attributes->get('class') }}">
    <div class="rm-empty-state-icon">
        <i class="ph-bold {{ $icono }} text-3xl {{ $color }}"></i>
    </div>
    <h3 class="rm-empty-state-title">{{ $titulo }}</h3>
    <p class="rm-empty-state-text">{{ $texto }}</p>

    @if($slot->isNotEmpty())
        <div class="mt-5">
            {{ $slot }}
        </div>
    @endif
</div>
