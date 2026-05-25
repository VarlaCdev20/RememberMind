<div class="min-h-screen bg-[#E6DDD3]/45 py-8 font-sans antialiased text-[#2F3E5C]">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        {{-- ENCABEZADO Y VOLVER --}}
        <div class="mb-8 flex flex-col justify-between gap-4 rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/78 p-5 shadow-sm backdrop-blur-xl sm:flex-row sm:items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.salud-seguimiento.resumen', $adulto) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#C7B5A3]/55 bg-[#D5C7B9]/65 shadow-sm transition-all hover:bg-terracota hover:text-white">
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

        <div class="flex flex-col items-center justify-center rounded-[1.6rem] border border-dashed border-[#C7B5A3]/70 bg-[#F3ECE4]/72 py-10 text-center shadow-sm backdrop-blur-xl">
            <i class="ph-fill {{ $icono }} text-3xl text-[#C7B5A3]/60 mb-6"></i>
            <h3 class="text-xl font-black text-azul-profundo">Gestión de {{ $titulo }}</h3>
            <p class="mt-2 text-sm font-bold text-[#2F3E5C]/60 max-w-md mx-auto">
                Espacio preparado para la administración de registros de {{ strtolower($titulo) }} del paciente seleccionado.
            </p>
        </div>
    </div>
</div>
