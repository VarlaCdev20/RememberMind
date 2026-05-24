<div class="min-h-screen py-8 font-sans antialiased text-[#2F3E5C]">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        {{-- ENCABEZADO Y VOLVER --}}
        <div class="mb-8 flex flex-col justify-between gap-4 border-b border-[#C7B5A3]/50 pb-6 sm:flex-row sm:items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.salud-seguimiento.resumen', $adulto) }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm transition-all hover:bg-terracota hover:text-white border border-[#C7B5A3]/40">
                    <i class="ph-bold ph-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tight text-azul-profundo">
                        {{ $titulo }}
                    </h1>
                    <p class="mt-1 text-sm font-bold text-azul-profundo/60">
                        {{ $adulto->nombres }} {{ $adulto->ap_paterno }} • {{ $adulto->cod_am }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-[#C7B5A3]/60 bg-white py-20 text-center shadow-sm">
            <i class="ph-fill {{ $icono }} text-6xl text-[#C7B5A3]/60 mb-6"></i>
            <h3 class="text-xl font-black text-azul-profundo">Gestión de {{ $titulo }}</h3>
            <p class="mt-2 text-sm font-bold text-[#2F3E5C]/60 max-w-md mx-auto">
                Espacio preparado para la administración de registros de {{ strtolower($titulo) }} del paciente seleccionado.
            </p>
        </div>
    </div>
</div>
