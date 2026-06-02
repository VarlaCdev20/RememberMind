<x-sistema-layout>
    <div class="relative mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        {{-- Encabezado --}}
        <div class="mb-6">
            <h2 class="text-2xl font-black text-titulo">Roles y Permisos</h2>
            <p class="text-sm font-medium text-apoyo">Gestión de accesos y seguridad del sistema.</p>
        </div>

        {{-- Contenido --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col items-center text-center transition-all hover:-translate-y-1 hover:shadow-card">
                <i class="ph-bold ph-shield-check text-4xl text-boton-acento mb-4"></i>
                <h3 class="text-lg font-bold text-titulo mb-2">Roles del sistema</h3>
                <p class="text-sm text-apoyo">Configuración de niveles jerárquicos.</p>
            </div>
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col items-center text-center transition-all hover:-translate-y-1 hover:shadow-card">
                <i class="ph-bold ph-key text-4xl text-boton-acento mb-4"></i>
                <h3 class="text-lg font-bold text-titulo mb-2">Permisos por módulo</h3>
                <p class="text-sm text-apoyo">Restricción detallada de acciones.</p>
            </div>
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col items-center text-center transition-all hover:-translate-y-1 hover:shadow-card">
                <i class="ph-bold ph-users text-4xl text-boton-acento mb-4"></i>
                <h3 class="text-lg font-bold text-titulo mb-2">Accesos por usuario</h3>
                <p class="text-sm text-apoyo">Asignación directa a personal.</p>
            </div>
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col items-center text-center transition-all hover:-translate-y-1 hover:shadow-card">
                <i class="ph-bold ph-table text-4xl text-boton-acento mb-4"></i>
                <h3 class="text-lg font-bold text-titulo mb-2">Matriz de permisos</h3>
                <p class="text-sm text-apoyo">Visualización global de seguridad.</p>
            </div>
        </div>

        <div class="mt-8 rounded-xl border border-borde bg-fondo-card-calido p-4 text-center">
            <p class="text-sm font-bold text-boton-acento">Estado: Panel preparado para conexión con Spatie Permission.</p>
        </div>
    </div>
</x-sistema-layout>