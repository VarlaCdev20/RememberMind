{{--
    Componente: modules/adulto-mini-card
    Tarjeta compacta de adulto mayor para selectores, listas y paneles de salud.

    Uso:
    <x-modules.adulto-mini-card
        :adulto="$adulto"
        :ruta="route('admin.salud-seguimiento.resumen', $adulto->cod_am)"
        boton="Ver resumen"
    />
--}}
@props([
    'adulto',
    'ruta'   => '#',
    'boton'  => 'Ver detalle',
])

@php
    use Illuminate\Support\Facades\Storage;
    $foto = $adulto->foto ?? null;
    $inicial = strtoupper(substr($adulto->nombres ?? 'A', 0, 1));
    $estado  = $adulto->estado?->estado ?? 'ACTIVO';
@endphp

<div class="group relative flex flex-col overflow-hidden rounded-3xl border border-borde-suave bg-fondo-card shadow-sm transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-terracota/10">

    {{-- Banner superior --}}
    <div class="relative h-20 w-full overflow-hidden bg-gradient-to-br from-[#E6DDD3] to-[#D5C7B9]">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#2F3E5C_1px,transparent_1px)] [background-size:14px_14px]"></div>
        <div class="absolute right-3 top-3">
            <x-ui.status-badge :estado="$estado" />
        </div>
    </div>

    {{-- Avatar --}}
    <div class="absolute left-1/2 top-6 -translate-x-1/2">
        <div class="h-20 w-20 overflow-hidden rounded-full border-4 border-white bg-fondo-card shadow-md">
            @if($foto)
                <img src="{{ Storage::url($foto) }}" alt="{{ $adulto->nombres }}" class="h-full w-full object-cover">
            @else
                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#E6DDD3] to-[#C7B5A3]">
                    <span class="text-2xl font-black text-meta">{{ $inicial }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Datos --}}
    <div class="flex flex-1 flex-col items-center px-5 pb-5 pt-10">
        <h3 class="line-clamp-1 text-center text-base font-black leading-tight text-titulo">
            {{ $adulto->nombres }} {{ $adulto->ap_paterno }}
        </h3>
        <p class="mt-0.5 text-[11px] font-bold text-boton-acento">{{ $adulto->cod_am }}</p>

        {{-- Slot de datos contextuales --}}
        <div class="mt-3 w-full flex-1 rounded-2xl bg-fondo-panel px-4 py-3 text-xs">
            {{ $slot }}
        </div>

        <a href="{{ $ruta }}"
           class="mt-4 w-full rounded-xl bg-boton-acento py-2.5 text-center text-[11px] font-black uppercase tracking-wider text-inverso shadow-sm transition-all hover:bg-boton-acento-dark active:scale-95 group-hover:shadow-md group-hover:shadow-terracota/25">
            {{ $boton }}
        </a>
    </div>
</div>
