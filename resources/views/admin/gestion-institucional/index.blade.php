<x-sistema-layout>
    <div class="relative mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="text-2xl font-black text-titulo">Gestión Institucional</h2>
            <p class="text-sm font-medium text-apoyo">Configuración central del Centro Geriátrico y organigrama.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Áreas Institucionales (Módulo Real) --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-buildings text-4xl text-boton-acento"></i>
                        <h3 class="font-bold text-lg text-titulo">Áreas institucionales</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">Gestión de espacios, áreas internas y unidades operativas.</p>
                </div>
                <a href="{{ route('admin.areas-institucionales.index') }}" class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-hover px-4 py-2 text-sm font-bold text-boton-acento transition-colors hover:bg-boton-acento hover:text-white">
                    Ir a áreas institucionales <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>

            {{-- Turnos y Asignaciones (Módulo Real) --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-clock text-4xl text-boton-acento"></i>
                        <h3 class="font-bold text-lg text-titulo">Turnos y asignaciones</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">Horarios, responsables, asignaciones y organización operativa.</p>
                </div>
                <a href="{{ route('admin.turnos-asignaciones.index') }}" class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-hover px-4 py-2 text-sm font-bold text-boton-acento transition-colors hover:bg-boton-acento hover:text-white">
                    Ir a turnos y asignaciones <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>

            {{-- Cargos administrativos --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card opacity-80">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-briefcase text-4xl text-meta"></i>
                        <h3 class="font-bold text-lg text-titulo">Cargos administrativos</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">El modelo de cargos ya existe. Gestión en desarrollo.</p>
                </div>
                <div class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-bloqueado px-4 py-2 text-sm font-bold text-apoyo cursor-not-allowed">
                    <i class="ph-bold ph-lock"></i> Próximamente
                </div>
            </div>

            {{-- Personal administrativo --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card opacity-80">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-users text-4xl text-meta"></i>
                        <h3 class="font-bold text-lg text-titulo">Personal administrativo</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">Se gestiona directamente desde Usuarios mediante el rol administrativo.</p>
                </div>
                <a href="{{ route('admin.usuarios.index') }}" class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-hover px-4 py-2 text-sm font-bold text-boton-acento transition-colors hover:bg-boton-acento hover:text-white">
                    Ir a Usuarios <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>

            {{-- Personal de salud --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card opacity-80">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-user-circle-plus text-4xl text-meta"></i>
                        <h3 class="font-bold text-lg text-titulo">Personal de salud</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">Se gestiona desde Usuarios mediante el rol de personal de salud.</p>
                </div>
                <a href="{{ route('admin.usuarios.index') }}" class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-hover px-4 py-2 text-sm font-bold text-boton-acento transition-colors hover:bg-boton-acento hover:text-white">
                    Ir a Usuarios <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>

            {{-- Especialidades --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card opacity-80">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-certificate text-4xl text-meta"></i>
                        <h3 class="font-bold text-lg text-titulo">Especialidades</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">El catálogo de especialidades médicas. Gestión en desarrollo.</p>
                </div>
                <div class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-bloqueado px-4 py-2 text-sm font-bold text-apoyo cursor-not-allowed">
                    <i class="ph-bold ph-lock"></i> Próximamente
                </div>
            </div>

            {{-- Estados y Catálogos (Placeholder) --}}
            <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-card opacity-70">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-list-dashes text-4xl text-meta"></i>
                        <h3 class="font-bold text-lg text-titulo">Estados y catálogos</h3>
                    </div>
                    <p class="text-sm text-apoyo mb-6">Estados del adulto mayor, tipos de observación y evaluación.</p>
                </div>
                <div class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-fondo-bloqueado px-4 py-2 text-sm font-bold text-apoyo cursor-not-allowed">
                    <i class="ph-bold ph-lock"></i> Próximamente
                </div>
            </div>
        </div>
    </div>
</x-sistema-layout>