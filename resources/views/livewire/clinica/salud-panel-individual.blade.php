<div class="min-h-screen bg-fondo-panel py-8 antialiased text-parrafo">
 <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
 {{-- ENCABEZADO Y VOLVER --}}
 <div class="mb-8 flex flex-col justify-between gap-4 rounded-[1.6rem] border border-borde/65 bg-fondo-panel p-5 shadow-sm backdrop-blur-xl sm:flex-row sm:items-center">
 <div class="flex items-center gap-4">
 <a href="{{ route('admin.salud-seguimiento.resumen', $adulto) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-borde/55 bg-fondo-panel shadow-sm transition-all hover:bg-boton-acento hover:text-inverso">
 <i class="ph-bold ph-arrow-left"></i>
 </a>
 <div>
 <h1 class="text-3xl font-black uppercase tracking-tight text-titulo">
 {{ $titulo }}
 </h1>
 <p class="mt-1 text-sm font-bold text-apoyo">
 {{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}
 </p>
 </div>
 </div>
 </div>

 <div class="flex flex-col items-center justify-center rounded-[1.6rem] border border-dashed border-borde/70 bg-fondo-panel py-10 text-center shadow-sm backdrop-blur-xl">
 <i class="ph-fill {{ $icono }} text-3xl text-meta mb-6"></i>
 <h3 class="text-xl font-extrabold text-titulo">Gestión de {{ $titulo }}</h3>
 <p class="mt-2 text-sm font-bold text-apoyo max-w-md mx-auto">
 Espacio preparado para la administración de registros de {{ strtolower($titulo) }} del paciente seleccionado.
 </p>
 </div>
 </div>
</div>
