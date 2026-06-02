<x-sistema-layout>
    <div class="relative mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        {{-- Encabezado --}}
        <div class="mb-6">
            <h2 class="text-2xl font-black text-titulo">Seguimiento de voluntariado</h2>
            <p class="text-sm font-medium text-apoyo">Evaluación de participación y horas de servicio.</p>
        </div>

        {{-- Tarjeta Informativa / Placeholder --}}
        <div class="rounded-[24px] border border-borde bg-fondo-card p-8 text-center shadow-sm">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-fondo-card-calido">
                <i class="ph-bold ph-cone text-4xl text-boton-acento"></i>
            </div>
            <h3 class="mb-2 text-xl font-bold text-titulo">Módulo en Desarrollo</h3>
            <p class="mb-6 text-sm text-apoyo max-w-md mx-auto">
                Estamos trabajando en la integración de esta funcionalidad. 
                Pronto podrás acceder a todas las herramientas de <strong>Seguimiento de voluntariado</strong>.
            </p>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-boton-principal px-6 py-2.5 text-sm font-bold text-boton-principalTexto transition-all hover:bg-boton-principalHover active:scale-95">
                <i class="ph-bold ph-arrow-left"></i>
                Volver al Inicio
            </a>
        </div>
    </div>
</x-sistema-layout>
