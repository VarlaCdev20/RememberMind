@props(['estadisticas' => []])

<section class="dash-anim rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-5 shadow-[0_16px_38px_rgba(47,62,92,0.13)] backdrop-blur-xl">
    <div class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr] xl:items-stretch">

        <div class="flex flex-col justify-between">
            <div>
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-terracota/10 px-3 py-1 text-[11px] font-black uppercase tracking-widest text-terracota">
                        Dashboard general
                    </span>

                    <span class="rounded-full bg-[#8DA280]/15 px-3 py-1 text-[11px] font-black text-[#63775B]">
                        Administración institucional
                    </span>
                </div>

                <h1 class="max-w-4xl text-xl font-black leading-tight text-azul-profundo md:text-2xl">
                    Panel administrativo de <span class="text-terracota">Casa Amandita</span>
                </h1>

                <p class="mt-3 max-w-3xl text-sm font-bold leading-6 text-azul-profundo/65">
                    Control centralizado de usuarios, roles, adultos mayores, actividades, alertas, reportes y bitácora institucional.
                </p>
            </div>

            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('admin.adultos-mayores.create') }}" 
                   class="rounded-full bg-terracota px-4 py-2 text-xs font-black text-white shadow-[0_6px_14px_rgba(233,122,95,0.28)] transition-all duration-200 hover:scale-95 active:scale-90">
                    <i class="ph-bold ph-plus-circle mr-1"></i>
                    Nuevo registro
                </a>

                <button 
                    disabled
                    title="Próximamente"
                    class="rounded-full bg-azul-profundo px-4 py-2 text-xs font-black text-white shadow-[0_6px_14px_rgba(47,62,92,0.24)] opacity-50 cursor-not-allowed transition-all duration-200">
                    <i class="ph-bold ph-file-arrow-down mr-1"></i>
                    Exportar reporte
                </button>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <a href="{{ route('admin.adultos-mayores.index') }}" 
               class="group rounded-[1.6rem] border border-[#C7B5A3] bg-[#D5C7B9]/75 p-4 shadow-sm transition-all duration-200 hover:scale-95 hover:bg-[#E6DDD3] hover:shadow-lg active:scale-90">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-terracota/10 text-terracota transition-colors group-hover:bg-terracota group-hover:text-white">
                    <i class="ph-fill ph-users-three text-xl"></i>
                </div>
                <p class="text-[11px] font-black uppercase tracking-widest text-azul-profundo/50">Adultos mayores</p>
                <p class="mt-1 text-2xl font-black text-azul-profundo">{{ $estadisticas['adultos_mayores'] ?? 0 }}</p>
                <p class="text-xs font-bold text-azul-profundo/55">Registrados</p>
            </a>

            @can('usuarios.ver')
            <a href="{{ route('admin.usuarios.index') }}"
               class="group rounded-[1.6rem] border border-[#C7B5A3] bg-[#D5C7B9]/75 p-4 shadow-sm transition-all duration-200 hover:scale-95 hover:bg-[#E6DDD3] hover:shadow-lg active:scale-90">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-azul-profundo/10 text-azul-profundo transition-colors group-hover:bg-azul-profundo group-hover:text-white">
                    <i class="ph-fill ph-user-gear text-xl"></i>
                </div>
                <p class="text-[11px] font-black uppercase tracking-widest text-azul-profundo/50">Usuarios activos</p>
                <p class="mt-1 text-2xl font-black text-azul-profundo">{{ $estadisticas['usuarios_activos'] ?? 0 }}</p>
                <p class="text-xs font-bold text-azul-profundo/55">Con acceso</p>
            </a>
            @else
            <div class="group rounded-[1.6rem] border border-[#C7B5A3] bg-[#D5C7B9]/75 p-4 shadow-sm opacity-50">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-azul-profundo/10 text-azul-profundo">
                    <i class="ph-fill ph-lock text-xl"></i>
                </div>
                <p class="text-[11px] font-black uppercase tracking-widest text-azul-profundo/50">Restringido</p>
                <p class="mt-1 text-2xl font-black text-azul-profundo">--</p>
                <p class="text-xs font-bold text-azul-profundo/55">Solo administradores</p>
            </div>
            @endcan

            <div class="group rounded-[1.6rem] border border-[#C7B5A3] bg-[#D5C7B9]/75 p-4 shadow-sm transition-all duration-200 hover:scale-95 hover:bg-[#E6DDD3] hover:shadow-lg sm:col-span-2 xl:col-span-1 active:scale-90">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-[#8DA280]/15 text-[#8DA280] transition-colors group-hover:bg-[#8DA280] group-hover:text-white">
                    <i class="ph-fill ph-hand-heart text-xl"></i>
                </div>
                <p class="text-[11px] font-black uppercase tracking-widest text-azul-profundo/50">Voluntarios</p>
                <p class="mt-1 text-2xl font-black text-azul-profundo">{{ $estadisticas['voluntarios'] ?? 0 }}</p>
                <p class="text-xs font-bold text-azul-profundo/55">Registrados</p>
            </div>
        </div>
    </div>
</section>