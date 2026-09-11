<x-sistema-layout>
    <div class="space-y-6">
        <!-- HEADER -->
        <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento">
                    <i class="ph-fill ph-user-plus text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-titulo">
                        Admisiones
                    </h2>
                    <p class="text-sm font-semibold text-apoyo">
                        Registro y gestión de nuevos ingresos.
                    </p>
                </div>
            </div>
            
            <a href="{{ route('admin.admisiones.preadmision') }}" class="inline-flex items-center gap-2 rounded-xl bg-boton-principal px-4 py-2.5 text-sm font-bold text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95 shrink-0">
                <i class="ph-bold ph-plus-circle text-lg"></i>
                Iniciar Admisión
            </a>
        </div>

        <!-- MÓDULO EN DESARROLLO -->
        <div class="relative overflow-hidden rounded-[24px] border border-borde bg-fondo-card/95 p-8 shadow-sm">
            <div class="flex flex-col items-center justify-center text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-fondo-app text-parrafo border border-borde-suave">
                    <i class="ph-bold ph-users text-3xl"></i>
                </div>
                <h3 class="text-xl font-extrabold text-titulo mb-2">Módulo de Admisiones</h3>
                <p class="text-sm font-semibold text-apoyo max-w-md mx-auto">
                    Aquí se integrará el flujo completo de admisiones y nuevos ingresos para la institución.
                </p>
            </div>
        </div>
    </div>
</x-sistema-layout>
