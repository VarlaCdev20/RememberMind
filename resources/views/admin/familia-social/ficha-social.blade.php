<x-sistema-layout>
    <div class="relative mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-titulo">Ficha Social Global</h2>
                <p class="text-sm font-medium text-apoyo">Panel de consulta global.</p>
            </div>
            <a href="{{ route('admin.adultos-mayores.index') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-boton-principal px-5 py-2.5 text-sm font-bold text-boton-principalTexto transition-all hover:bg-boton-principalHover active:scale-95">
                <i class="ph-bold ph-users-four text-lg"></i>
                Ir a Registro Integral
            </a>
        </div>

        <div class="rounded-[24px] border border-borde bg-fondo-card p-8 text-center shadow-sm">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-fondo-card-calido">
                <i class="ph-bold ph-info text-4xl text-boton-acento"></i>
            </div>
            <h3 class="mb-2 text-xl font-bold text-titulo">Información Importante</h3>
            <p class="mb-6 text-sm text-apoyo max-w-2xl mx-auto leading-relaxed">
                El panel global de Ficha Social sirve para consulta y acceso rápido. La red de apoyo real y los datos detallados se gestionan desde la pestaña Familiares dentro del expediente individual de cada adulto mayor.
            </p>
        </div>
    </div>
</x-sistema-layout>