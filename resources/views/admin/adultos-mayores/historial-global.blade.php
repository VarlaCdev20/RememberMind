<x-sistema-layout>
    <div class="relative mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-titulo">Historial individual</h2>
                <p class="text-sm font-medium text-apoyo">Consulte el expediente específico de cada residente.</p>
            </div>
            <a href="{{ route('admin.adultos-mayores.index') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-boton-principal px-5 py-2.5 text-sm font-bold text-boton-principalTexto transition-all hover:bg-boton-principalHover active:scale-95">
                <i class="ph-bold ph-users-four text-lg"></i>
                Ir a Registro Integral
            </a>
        </div>

        <div class="rounded-[24px] border border-borde bg-fondo-card p-8 text-center shadow-sm mb-6">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-fondo-card-calido">
                <i class="ph-bold ph-folder-open text-4xl text-boton-acento"></i>
            </div>
            <h3 class="mb-2 text-xl font-bold text-titulo">Acceso a Expedientes</h3>
            <p class="mb-6 text-sm text-apoyo max-w-xl mx-auto leading-relaxed">
                El historial real y completo se consulta ingresando a la ficha de cada adulto mayor desde el listado general. A continuación encontrará accesos rápidos a las funciones de historial.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="rounded-[20px] border border-borde bg-fondo-card p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-card">
                <div class="flex items-center gap-4 mb-2">
                    <div class="p-2 rounded-lg bg-fondo-card-calido"><i class="ph-bold ph-magnifying-glass text-2xl text-boton-acento"></i></div>
                    <h4 class="font-bold text-titulo">Buscar adulto mayor</h4>
                </div>
            </div>
            <div class="rounded-[20px] border border-borde bg-fondo-card p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-card">
                <div class="flex items-center gap-4 mb-2">
                    <div class="p-2 rounded-lg bg-fondo-card-calido"><i class="ph-bold ph-eye text-2xl text-boton-acento"></i></div>
                    <h4 class="font-bold text-titulo">Últimas observaciones</h4>
                </div>
            </div>
            <div class="rounded-[20px] border border-borde bg-fondo-card p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-card">
                <div class="flex items-center gap-4 mb-2">
                    <div class="p-2 rounded-lg bg-fondo-card-calido"><i class="ph-bold ph-file-plus text-2xl text-boton-acento"></i></div>
                    <h4 class="font-bold text-titulo">Evaluaciones geriátricas</h4>
                </div>
            </div>
            <div class="rounded-[20px] border border-borde bg-fondo-card p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-card">
                <div class="flex items-center gap-4 mb-2">
                    <div class="p-2 rounded-lg bg-fondo-card-calido"><i class="ph-bold ph-arrows-left-right text-2xl text-boton-acento"></i></div>
                    <h4 class="font-bold text-titulo">Cambios de estado</h4>
                </div>
            </div>
            <div class="rounded-[20px] border border-borde bg-fondo-card p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-card">
                <div class="flex items-center gap-4 mb-2">
                    <div class="p-2 rounded-lg bg-fondo-card-calido"><i class="ph-bold ph-bell-ringing text-2xl text-boton-acento"></i></div>
                    <h4 class="font-bold text-titulo">Alertas futuras</h4>
                </div>
            </div>
            <div class="rounded-[20px] border border-borde bg-fondo-card p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-card">
                <div class="flex items-center gap-4 mb-2">
                    <div class="p-2 rounded-lg bg-fondo-card-calido"><i class="ph-bold ph-chart-line-up text-2xl text-boton-acento"></i></div>
                    <h4 class="font-bold text-titulo">Reportes individuales</h4>
                </div>
            </div>
        </div>
    </div>
</x-sistema-layout>