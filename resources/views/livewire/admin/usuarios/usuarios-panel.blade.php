<div x-data="{ vista: 'cards' }">

    {{-- ENCABEZADO LIMPIO Y MÁS DELGADO --}}
    <header class="mb-5 rounded-[1.6rem] border border-[#C7B5A3]/55 bg-[#E6DDD3]/72 px-6 py-5 shadow-[0_10px_26px_rgba(47,62,92,0.07)] backdrop-blur-xl">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <div class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#2F3E5C] text-white shadow-lg shadow-[#2F3E5C]/15 sm:flex">
                    <i class="ph-bold ph-users-three text-xl"></i>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.28em] text-[#E27D60]">
                        Gestión institucional
                    </p>

                    <h1 class="mt-1 text-3xl font-black tracking-tight text-[#2F3E5C] md:text-[2.3rem]">
                        Usuarios del <span class="text-[#E27D60]">sistema</span>
                    </h1>

                    <p class="mt-1 max-w-2xl text-sm font-semibold leading-6 text-[#2F3E5C]/58">
                        Administra cuentas, accesos y perfiles autorizados dentro de Casa Amandita.
                    </p>
                </div>
            </div>

            <button type="button"
                    wire:click="crearUsuario"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-[#E27D60] px-6 py-3 text-sm font-black text-white shadow-lg shadow-[#E27D60]/20 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl active:translate-y-0 active:scale-95">
                <i class="ph-bold ph-plus-circle text-lg"></i>
                Nuevo usuario
            </button>
        </div>
    </header>

    {{-- MÉTRICAS PRINCIPALES --}}
    <section class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-[1.35rem] border border-[#C7B5A3]/60 bg-[#E6DDD3]/68 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#E6DDD3]/90 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#2F3E5C]/42">
                        Usuarios registrados
                    </p>
                    <p class="mt-2 text-2xl font-black text-[#2F3E5C]">
                        {{ method_exists($usuarios, 'total') ? $usuarios->total() : $usuarios->count() }}
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#2F3E5C]/10 text-[#2F3E5C]">
                    <i class="ph-bold ph-users text-xl"></i>
                </div>
            </div>
        </article>

        <article class="rounded-[1.35rem] border border-[#C7B5A3]/60 bg-[#E6DDD3]/68 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#E6DDD3]/90 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#2F3E5C]/42">
                        Activos
                    </p>
                    <p class="mt-2 text-2xl font-black text-[#63775B]">
                        {{ $usuarios->filter(fn($u) => $u->estado === 'ACTIVO')->count() }}
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#8DA280]/18 text-[#63775B]">
                    <i class="ph-bold ph-user-check text-xl"></i>
                </div>
            </div>
        </article>

        <article class="rounded-[1.35rem] border border-[#C7B5A3]/60 bg-[#E6DDD3]/68 p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#E6DDD3]/90 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#2F3E5C]/42">
                        Inactivos
                    </p>
                    <p class="mt-2 text-2xl font-black text-[#967B66]">
                        {{ $usuarios->filter(fn($u) => $u->estado !== 'ACTIVO')->count() }}
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#967B66]/14 text-[#967B66]">
                    <i class="ph-bold ph-user-minus text-xl"></i>
                </div>
            </div>
        </article>
    </section>

    {{-- VISTAS --}}
    <section class="mb-5 rounded-[1.35rem] border border-[#C7B5A3]/60 bg-[#E6DDD3]/62 p-2 shadow-sm">
        <div class="flex flex-wrap gap-2">
            <button type="button"
                    @click="vista = 'cards'"
                    :class="vista === 'cards'
                        ? 'bg-[#2F3E5C] text-white shadow-md'
                        : 'text-[#2F3E5C]/70 hover:bg-white/65 hover:text-[#2F3E5C]'"
                    class="inline-flex items-center gap-2 rounded-2xl px-5 py-2.5 text-xs font-black transition-all duration-300 active:scale-95">
                <i class="ph-bold ph-identification-card"></i>
                Vista tarjetas
            </button>

            <button type="button"
                    @click="vista = 'table'"
                    :class="vista === 'table'
                        ? 'bg-[#2F3E5C] text-white shadow-md'
                        : 'text-[#2F3E5C]/70 hover:bg-white/65 hover:text-[#2F3E5C]'"
                    class="inline-flex items-center gap-2 rounded-2xl px-5 py-2.5 text-xs font-black transition-all duration-300 active:scale-95">
                <i class="ph-bold ph-table"></i>
                Tabla compacta
            </button>
        </div>
    </section>

    {{-- FILTROS CON BOTÓN BUSCAR --}}
    <section class="mb-6 rounded-[1.55rem] border border-[#C7B5A3]/60 bg-[#E6DDD3]/66 p-4 shadow-sm backdrop-blur-xl">
        <div class="grid gap-4 xl:grid-cols-12">
            <div class="relative xl:col-span-5">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5">
                    <i class="ph-bold ph-magnifying-glass text-lg text-[#2F3E5C]/35"></i>
                </div>

                <input type="text"
                       wire:model.defer="search"
                       placeholder="Buscar por nombre o correo..."
                       class="h-12 w-full rounded-2xl border-2 border-[#C7B5A3]/40 bg-white/55 py-3 pl-12 pr-4 text-sm font-bold text-[#2F3E5C] outline-none transition-all placeholder:text-[#2F3E5C]/35 focus:border-[#E27D60]/60 focus:bg-white focus:ring-4 focus:ring-[#E27D60]/10">
            </div>

            <div class="xl:col-span-3">
                <select wire:model.defer="filtroRol"
                        class="h-12 w-full rounded-2xl border-2 border-[#C7B5A3]/40 bg-white/55 px-4 text-sm font-black text-[#2F3E5C] outline-none transition-all focus:border-[#E27D60]/60 focus:bg-white focus:ring-4 focus:ring-[#E27D60]/10">
                    <option value="">Todos los roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}">{{ strtoupper(str_replace('_', ' ', $r->name)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="xl:col-span-2">
                <select wire:model.defer="filtroEstado"
                        class="h-12 w-full rounded-2xl border-2 border-[#C7B5A3]/40 bg-white/55 px-4 text-sm font-black text-[#2F3E5C] outline-none transition-all focus:border-[#E27D60]/60 focus:bg-white focus:ring-4 focus:ring-[#E27D60]/10">
                    <option value="">Todos los estados</option>
                    <option value="ACTIVO">Activos</option>
                    <option value="INACTIVO">Inactivos</option>
                </select>
            </div>

            <div class="flex gap-2 xl:col-span-2">
                <button type="button"
                        wire:click="$refresh"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-[#2F3E5C] px-4 py-3 text-xs font-black text-white shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg active:scale-95">
                    <i class="ph-bold ph-funnel"></i>
                    Buscar
                </button>

                <button type="button"
                        wire:click="limpiarFiltros"
                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#D5C7B9]/70 text-[#2F3E5C] transition-all duration-300 hover:bg-[#E27D60] hover:text-white active:scale-95"
                        title="Limpiar filtros">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>
        </div>
    </section>

    {{-- CONTENIDO PRINCIPAL --}}

<section class="relative">
    <div wire:loading.delay class="absolute inset-0 z-20 flex items-center justify-center rounded-[2rem] bg-white/45 backdrop-blur-sm">
        <div class="flex items-center gap-3 rounded-full bg-white px-5 py-3 shadow-lg">
            <i class="ph-bold ph-spinner animate-spin text-2xl text-[#E27D60]"></i>
            <span class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]">
                Cargando
            </span>
        </div>
    </div>

        {{-- VISTA TARJETAS --}}
<div x-show="vista === 'cards'" x-transition.opacity.duration.200ms>
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($usuarios as $u)
            @php
                $roleName = $u->getRoleNames()->first() ?? 'sin_rol';
                $roleKey = strtolower($roleName);

                $nombreCompleto = trim(($u->nombres ?? '') . ' ' . ($u->ap_paterno ?? '') . ' ' . ($u->ap_materno ?? ''));
                $nombreCompleto = $nombreCompleto !== '' ? $nombreCompleto : ($u->correo ?? 'Usuario sin nombre');

                $inicial = mb_substr(trim($u->nombres ?? $nombreCompleto), 0, 1);

                $areaDisplay = match($roleKey) {
                    'super_admin', 'superadministrador', 'admin', 'administrador' => 'Administración del sistema',
                    'personal_salud' => 'Área de salud',
                    'personal_admin' => 'Área administrativa',
                    'familiar' => 'Familiar / Responsable',
                    'voluntario' => 'Voluntariado',
                    default => 'Sin área asignada'
                };

                $perfilDetalle = match($roleKey) {
                    'personal_salud' => data_get($u, 'personalSalud.especialidad.nombre')
                        ?? data_get($u, 'personalSalud.especialidad')
                        ?? 'Personal de salud',
                    'personal_admin' => data_get($u, 'personalAdmin.cargoAdministrativo.nombre')
                        ?? data_get($u, 'personalAdmin.cargo.nombre')
                        ?? 'Personal administrativo',
                    'super_admin', 'superadministrador', 'admin', 'administrador' => 'Administrador del sistema',
                    'voluntario' => 'Voluntario institucional',
                    'familiar' => 'Familiar / Responsable',
                    default => strtoupper(str_replace('_', ' ', $roleName))
                };
                

                $estaInactivo = $u->estado !== 'ACTIVO';

$areaClass = match($roleKey) {
    'super_admin', 'superadministrador', 'admin', 'administrador' => 'bg-[#2F3E5C]/10 text-[#2F3E5C] border-[#2F3E5C]/15',
    'personal_salud' => 'bg-[#8DA280]/16 text-[#63775B] border-[#8DA280]/25',
    'personal_admin' => 'bg-[#E27D60]/14 text-[#E27D60] border-[#E27D60]/20',
    'voluntario' => 'bg-[#7C83B8]/14 text-[#5E6599] border-[#7C83B8]/20',
    'familiar' => 'bg-[#967B66]/14 text-[#7B624F] border-[#967B66]/20',
    default => 'bg-[#C7B5A3]/22 text-[#2F3E5C]/55 border-[#C7B5A3]/40'
};

$perfilClass = match($roleKey) {
    'super_admin', 'superadministrador', 'admin', 'administrador' => 'bg-[#2F3E5C]/12 text-[#2F3E5C] border-[#2F3E5C]/15',
    'personal_salud' => 'bg-[#8DA280]/18 text-[#63775B] border-[#8DA280]/25',
    'personal_admin' => 'bg-[#E27D60]/16 text-[#E27D60] border-[#E27D60]/20',
    'voluntario' => 'bg-[#7C83B8]/16 text-[#5E6599] border-[#7C83B8]/20',
    'familiar' => 'bg-[#967B66]/16 text-[#7B624F] border-[#967B66]/20',
    default => 'bg-[#C7B5A3]/22 text-[#2F3E5C]/55 border-[#C7B5A3]/40'
};

$ultimoAcceso = $u->ultimo_acceso ?? null;

                $fotoUsuario = null;
                if (!empty($u->foto_de_perfil)) {
                    $fotoUsuario = \Illuminate\Support\Facades\Storage::url($u->foto_de_perfil);
                } elseif (!empty($u->profile_photo_path)) {
                    $fotoUsuario = \Illuminate\Support\Facades\Storage::url($u->profile_photo_path);
                } elseif (!empty($u->profile_photo_url)) {
                    $fotoUsuario = $u->profile_photo_url;
                }
            @endphp

           <article wire:key="card-user-{{ $u->cod_usu }}"
    class="group overflow-hidden rounded-[1.45rem] border shadow-sm transition-all duration-300
    {{ $estaInactivo
        ? 'border-[#C7B5A3]/45 bg-[#E5DED6]/62 grayscale opacity-70 hover:opacity-85'
        : 'border-[#C7B5A3]/55 bg-white/78 hover:-translate-y-1 hover:shadow-[0_16px_30px_rgba(47,62,92,0.10)]' }}">

    <div class="h-1.5 w-full {{ $estaInactivo ? 'bg-[#9B8B7E]/45' : 'bg-gradient-to-r from-[#E27D60] via-[#F0A08B] to-[#E6DDD3]' }}"></div>

    <div class="p-4">
        {{-- Estado --}}
        <div class="mb-3 flex justify-end">
            @if($u->estado === 'ACTIVO')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#8DA280]/18 px-2.5 py-1 text-[9px] font-black uppercase text-[#63775B]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#8DA280]"></span>
                    Activo
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#9B8B7E]/18 px-2.5 py-1 text-[9px] font-black uppercase text-[#7C7168]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#9B8B7E]"></span>
                    Inactivo
                </span>
            @endif
        </div>

        {{-- Foto centrada compacta --}}
        <div class="flex flex-col items-center text-center">
            <div class="relative">
                @if($fotoUsuario)
                    <img
                        src="{{ $fotoUsuario }}"
                        alt="Foto de {{ $nombreCompleto }}"
                        class="h-20 w-20 rounded-[1.35rem] object-cover ring-4 ring-white shadow-[0_10px_20px_rgba(47,62,92,0.12)] transition-all duration-300 {{ $estaInactivo ? '' : 'group-hover:scale-[1.03]' }}"
                    >
                @else
                    <div class="flex h-20 w-20 items-center justify-center rounded-[1.35rem] bg-[#2F3E5C] text-2xl font-black text-white ring-4 ring-white shadow-[0_10px_20px_rgba(47,62,92,0.12)]">
                        {{ strtoupper($inicial) }}
                    </div>
                @endif

                <span class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full border-[3px] border-white {{ $u->estado === 'ACTIVO' ? 'bg-[#8DA280]' : 'bg-[#9B8B7E]' }}"></span>
            </div>

            <h3 class="mt-3 line-clamp-2 text-sm font-black uppercase leading-5 text-[#2F3E5C]">
                {{ $nombreCompleto }}
            </h3>

            <p class="mt-0.5 max-w-full truncate text-xs font-bold lowercase text-[#2F3E5C]/55">
                {{ $u->correo ?: 'Sin correo registrado' }}
            </p>
        </div>

        {{-- Info compacta --}}
        <div class="mt-4 space-y-2.5">
            <div class="rounded-2xl border px-3 py-2.5 {{ $areaClass }}">
                <p class="text-[9px] font-black uppercase tracking-[0.16em] opacity-60">
                    Área
                </p>
                <p class="mt-0.5 truncate text-xs font-black">
                    {{ $areaDisplay }}
                </p>
            </div>

            <div class="rounded-2xl border px-3 py-2.5 {{ $perfilClass }}">
                <p class="text-[9px] font-black uppercase tracking-[0.16em] opacity-60">
                    Perfil institucional
                </p>
                <p class="mt-0.5 truncate text-xs font-black uppercase">
                    {{ $perfilDetalle }}
                </p>
            </div>

            <div class="rounded-2xl border border-[#E8DED2] bg-[#FAF7F3]/80 px-3 py-2.5">
                <p class="text-[9px] font-black uppercase tracking-[0.16em] text-[#2F3E5C]/35">
                    Último acceso
                </p>
                @if($ultimoAcceso)
                    <p class="mt-0.5 text-xs font-bold text-[#2F3E5C]">
                        {{ \Carbon\Carbon::parse($ultimoAcceso)->format('d/m/Y H:i') }}
                    </p>
                @else
                    <p class="mt-0.5 text-xs font-bold text-[#2F3E5C]/45">
                        Sin registro
                    </p>
                @endif
            </div>
        </div>

        {{-- Acciones --}}
        <div class="mt-4 flex items-center justify-center gap-2">
            <a href="{{ route('admin.usuarios.show', $u->cod_usu) }}"
               class="inline-flex h-9 items-center gap-1.5 rounded-full bg-[#2F3E5C] px-3 text-[10px] font-black text-white shadow-sm transition hover:bg-[#24314A] active:scale-95">
                <i class="ph-bold ph-eye"></i>
                Ver
            </a>

            @if($estaInactivo)
                <button type="button"
                        disabled
                        title="Active el usuario para poder editarlo"
                        class="inline-flex h-9 cursor-not-allowed items-center gap-1.5 rounded-full bg-[#C7B5A3]/55 px-3 text-[10px] font-black text-[#7C7168]/65">
                    <i class="ph-bold ph-lock"></i>
                    Editar
                </button>
            @else
                <button type="button"
                        wire:click="editarUsuario('{{ $u->cod_usu }}')"
                        class="inline-flex h-9 items-center gap-1.5 rounded-full bg-[#E27D60] px-3 text-[10px] font-black text-white shadow-sm transition hover:bg-[#d86c50] active:scale-95">
                    <i class="ph-bold ph-pencil-simple"></i>
                    Editar
                </button>
            @endif

            @if($u->cod_usu !== auth()->id())
                <button wire:click="toggleEstado('{{ $u->cod_usu }}')"
                        wire:confirm="¿Desea cambiar el estado de este usuario?"
                        class="inline-flex h-9 items-center gap-1.5 rounded-full px-3 text-[10px] font-black shadow-sm transition active:scale-95
                        {{ $u->estado === 'ACTIVO'
                            ? 'bg-[#D9CCBD] text-[#2F3E5C] hover:bg-[#967B66] hover:text-white'
                            : 'bg-[#8DA280]/20 text-[#63775B] hover:bg-[#8DA280] hover:text-white' }}">
                    <i class="ph-bold {{ $u->estado === 'ACTIVO' ? 'ph-user-minus' : 'ph-user-plus' }}"></i>
                    {{ $u->estado === 'ACTIVO' ? 'Inactivar' : 'Activar' }}
                </button>
            @endif
        </div>
    </div>
</article>
        @empty
            <div class="col-span-full py-16 text-center">
                <div class="mx-auto flex max-w-md flex-col items-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-[#2F3E5C]/8 text-[#2F3E5C]/35">
                        <i class="ph-bold ph-users-three text-3xl"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-black text-[#2F3E5C]">
                        No se encontraron usuarios
                    </h3>
                    <p class="mt-2 text-sm font-semibold text-[#2F3E5C]/45">
                        Ajusta los filtros o registra un nuevo usuario institucional.
                    </p>
                </div>
            </div>
        @endforelse
    </div>
</div>

        {{-- VISTA TABLA COMPACTA --}}
<div x-show="vista === 'table'" x-transition.opacity.duration.200ms>
    <div class="overflow-hidden rounded-[1.8rem] border border-[#D8CBBB]/70 bg-white/75 shadow-[0_10px_24px_rgba(47,62,92,0.06)]">
        <table class="w-full table-fixed text-left text-sm">
            <thead class="bg-[#F4EEE7] text-[10px] uppercase tracking-[0.18em] text-[#2F3E5C]/55">
                <tr>
                    <th class="w-[30%] px-5 py-4 font-black">Usuario</th>
                    <th class="w-[28%] px-5 py-4 font-black">Perfil institucional</th>
                    <th class="w-[14%] px-5 py-4 font-black">Estado</th>
                    <th class="w-[14%] px-4 py-4 font-black">Último acceso</th>
                    <th class="w-[14%] px-4 py-4 text-center font-black">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-[#E7DDD1] bg-white/85">
                @forelse($usuarios as $u)
                    @php
                        $roleName = $u->getRoleNames()->first() ?? 'sin_rol';
                        $roleKey = strtolower($roleName);

                        $nombreCompleto = trim(($u->nombres ?? '') . ' ' . ($u->ap_paterno ?? '') . ' ' . ($u->ap_materno ?? ''));
                        $nombreCompleto = $nombreCompleto !== '' ? $nombreCompleto : ($u->correo ?? 'Usuario sin nombre');

                        $inicial = mb_substr(trim($u->nombres ?? $nombreCompleto), 0, 1);

                        $areaDisplay = match($roleKey) {
                            'super_admin', 'superadministrador', 'admin', 'administrador' => 'Administración del sistema',
                            'personal_salud' => 'Área de salud',
                            'personal_admin' => 'Área administrativa',
                            'familiar' => 'Familiar / Responsable',
                            'voluntario' => 'Voluntariado',
                            default => 'Sin área asignada'
                        };

                        $perfilDetalle = match($roleKey) {
                            'personal_salud' => data_get($u, 'personalSalud.especialidad.nombre')
                                ?? data_get($u, 'personalSalud.especialidad')
                                ?? 'Personal de salud',
                            'personal_admin' => data_get($u, 'personalAdmin.cargoAdministrativo.nombre')
                                ?? data_get($u, 'personalAdmin.cargo.nombre')
                                ?? 'Personal administrativo',
                            'super_admin', 'superadministrador', 'admin', 'administrador' => 'Administrador del sistema',
                            'voluntario' => 'Voluntario institucional',
                            'familiar' => 'Familiar / Responsable',
                            default => strtoupper(str_replace('_', ' ', $roleName))
                        };
                        $estaInactivo = $u->estado !== 'ACTIVO';

                        $areaClass = match($roleKey) {
                            'super_admin', 'superadministrador', 'admin', 'administrador' => 'bg-[#2F3E5C]/10 text-[#2F3E5C] border-[#2F3E5C]/15',
                            'personal_salud' => 'bg-[#8DA280]/16 text-[#63775B] border-[#8DA280]/25',
                            'personal_admin' => 'bg-[#E27D60]/14 text-[#E27D60] border-[#E27D60]/20',
                            'voluntario' => 'bg-[#7C83B8]/14 text-[#5E6599] border-[#7C83B8]/20',
                            'familiar' => 'bg-[#967B66]/14 text-[#7B624F] border-[#967B66]/20',
                            default => 'bg-[#C7B5A3]/22 text-[#2F3E5C]/55 border-[#C7B5A3]/40'
                        };
                      $ultimoAcceso = $u->ultimo_acceso ?? null;
                    @endphp

                   <tr class="transition-all duration-300 {{ $estaInactivo ? 'bg-[#E6DED5]/65 grayscale opacity-70' : 'bg-white/75 hover:bg-[#FCF9F6]' }}"
    wire:key="tabla-user-{{ $u->cod_usu }}">
                        <td class="px-5 py-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#2F3E5C] text-sm font-black text-white shadow-sm">
                                    {{ strtoupper($inicial) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-black text-[#2F3E5C]">
                                        {{ $nombreCompleto }}
                                    </p>
                                    <p class="truncate text-xs font-semibold text-[#2F3E5C]/50">
                                        {{ $u->correo ?: 'Sin correo registrado' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-4">
    <div class="space-y-1">
        <span class="inline-flex max-w-full rounded-full border px-3 py-1 text-[10px] font-black uppercase {{ $areaClass }}">
            {{ $areaDisplay }}
        </span>

        <p class="truncate text-xs font-bold text-[#2F3E5C]/60">
            {{ $perfilDetalle }}
        </p>
    </div>
</td>

                        <td class="px-5 py-4">
                            @if($u->estado === 'ACTIVO')
                                <span class="rounded-full bg-[#8DA280]/18 px-3 py-1 text-[10px] font-black uppercase text-[#63775B]">
                                    Activo
                                </span>
                            @else
                                <span class="rounded-full bg-[#967B66]/15 px-3 py-1 text-[10px] font-black uppercase text-[#967B66]">
                                    Inactivo
                                </span>
                            @endif
                        </td>

                        <td class="px-5 py-4">
                            @if($ultimoAcceso)
                                <div class="leading-4">
                                    <p class="font-black text-[#2F3E5C]">
                                        {{ \Carbon\Carbon::parse($ultimoAcceso)->format('d/m/Y') }}
                                    </p>
                                    <p class="text-[10px] font-semibold text-[#2F3E5C]/45">
                                        {{ \Carbon\Carbon::parse($ultimoAcceso)->format('H:i') }}
                                    </p>
                                </div>
                            @else
                                <p class="text-[11px] font-semibold text-[#2F3E5C]/45">
                                    Sin registro
                                </p>
                            @endif
                        </td>
                <td class="px-4 py-4">
                    <div class="flex items-center justify-center gap-1.5">
                        <a href="{{ route('admin.usuarios.show', $u->cod_usu) }}"
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#F6F2EC] text-[#2F3E5C] shadow-sm transition hover:bg-[#2F3E5C] hover:text-white active:scale-90"
                        title="Ver detalle">
                            <i class="ph-bold ph-eye"></i>
                        </a>

                        @if($estaInactivo)
                            <button type="button"
                                    disabled
                                    class="flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-xl bg-[#C7B5A3]/45 text-[#7C7168]/55 shadow-sm"
                                    title="Active el usuario para poder editarlo">
                                <i class="ph-bold ph-lock"></i>
                            </button>
                        @else
                            <button type="button"
                                    wire:click="editarUsuario('{{ $u->cod_usu }}')"
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#FDF1ED] text-[#E27D60] shadow-sm transition hover:bg-[#E27D60] hover:text-white active:scale-90"
                                    title="Editar">
                                <i class="ph-bold ph-pencil-simple"></i>
                            </button>
                        @endif

                        @if($u->cod_usu !== auth()->id())
                            <button wire:click="toggleEstado('{{ $u->cod_usu }}')"
                                    wire:confirm="¿Desea cambiar el estado de este usuario?"
                                    class="flex h-9 w-9 items-center justify-center rounded-xl shadow-sm transition active:scale-90
                                    {{ $u->estado === 'ACTIVO'
                                        ? 'bg-[#F3EEE8] text-[#967B66] hover:bg-[#967B66] hover:text-white'
                                        : 'bg-[#8DA280]/18 text-[#63775B] hover:bg-[#8DA280] hover:text-white' }}"
                                    title="{{ $u->estado === 'ACTIVO' ? 'Inactivar usuario' : 'Activar usuario' }}">
                                <i class="ph-bold {{ $u->estado === 'ACTIVO' ? 'ph-user-minus' : 'ph-user-plus' }}"></i>
                            </button>
                        @endif
                    </div>
                </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-14 text-center text-[#2F3E5C]/45 font-bold">
                            No se encontraron usuarios.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($usuarios->hasPages())
    <div class="mt-5 flex justify-center">
        <div class="rounded-2xl border border-[#D8CBBB]/60 bg-white/75 px-4 py-3 shadow-sm">
            {{ $usuarios->links() }}
        </div>
    </div>
@endif
    </section>

    
    {{-- MODAL FUERA DEL CONTENEDOR DEL PANEL --}}
    @if($mostrarFormulario)
    <div class="fixed inset-0 z-[2147483646] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm px-3 sm:px-4 transition-all duration-300">
        <div class="relative z-[2147483647] w-full max-w-3xl max-h-[92vh] overflow-hidden rounded-[24px] border border-[#C7B5A3]/30 bg-[#E6DDD3] shadow-[0_20px_50px_rgba(0,0,0,0.5)] animate-in fade-in zoom-in duration-300 flex flex-col">
            
            {{-- HEADER CON PROGRESO --}}
            <header class="relative border-b border-[#C7B5A3]/30 bg-[#E6DDD3]/50 px-4 py-3 backdrop-blur-xl shrink-0">
                <div class="flex items-center justify-between gap-4 mb-2">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#2F3E5C] text-white shadow-lg">
                            <i class="ph-bold {{ $isEdit ? 'ph-pencil-simple' : 'ph-user-plus' }} text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">
                                {{ $isEdit ? 'Actualizar' : 'Registro de' }} <span class="text-[#E27D60]">Personal</span>
                            </h2>
                        </div>
                    </div>
                    <button wire:click="cerrarFormulario" class="group flex h-8 w-8 items-center justify-center rounded-xl bg-[#D5C7B9] text-[#2F3E5C] transition-all hover:bg-[#E27D60] hover:text-white active:scale-90 shadow-sm">
                        <i class="ph-bold ph-x text-base transition group-hover:rotate-90"></i>
                    </button>
                </div>
                {{-- BARRA DE PASOS VISUAL MEJORADA --}}
                <div class="relative px-4 py-4 sm:px-8 bg-[#D5C7B9]/20 border-b border-[#C7B5A3]/10 hidden sm:block">
                    <div class="relative flex items-center justify-between max-w-3xl mx-auto">
                        {{-- Línea de fondo --}}
                        <div class="absolute top-1/2 left-0 w-full h-1 bg-[#C7B5A3]/30 -translate-y-1/2 rounded-full"></div>
                        {{-- Línea de progreso activa --}}
                        <div class="absolute top-1/2 left-0 h-1 bg-[#E27D60] -translate-y-1/2 rounded-full transition-all duration-700 ease-out shadow-[0_0_8px_rgba(226,125,96,0.5)]" 
                             style="width: {{ (($pasoFormulario - 1) / 4) * 100 }}%"></div>
                        
                        {{-- Pasos --}}
                        @php
                            $pasosUsu = [
                                1 => ['i' => 'ph-user-circle', 'l' => 'Identidad'],
                                2 => ['i' => 'ph-phone-call', 'l' => 'Contacto'],
                                3 => ['i' => 'ph-briefcase', 'l' => 'Perfil'],
                                4 => ['i' => 'ph-shield-check', 'l' => 'Seguridad'],
                                5 => ['i' => 'ph-check-square', 'l' => 'Finalizar']
                            ];
                        @endphp

                        @foreach($pasosUsu as $s => $p)
                            <div class="relative flex flex-col items-center group">
                                <div class="relative z-10 flex h-9 w-9 items-center justify-center rounded-xl border-2 transition-all duration-500
                                    {{ $pasoFormulario > $s ? 'bg-[#8DA280] border-[#8DA280] text-white' : 
                                       ($pasoFormulario == $s ? 'bg-white border-[#E27D60] text-[#E27D60] shadow-lg scale-110' : 
                                       'bg-[#D5C7B9] border-[#C7B5A3] text-[#2F3E5C]/30') }}">
                                    
                                    <i class="ph-bold {{ $p['i'] }} text-base transition-all duration-500 {{ $pasoFormulario == $s ? 'scale-110' : '' }}"></i>
                                    
                                    @if($pasoFormulario > $s)
                                        <div class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#2F3E5C] text-white shadow-sm border border-[#E6DDD3]">
                                            <i class="ph-bold ph-check text-[8px]"></i>
                                        </div>
                                    @endif
                                </div>
                                <span class="absolute -bottom-7 whitespace-nowrap text-[8px] font-black uppercase tracking-tighter transition-all duration-500 
                                    {{ $pasoFormulario >= $s ? 'text-[#2F3E5C] opacity-100' : 'text-[#2F3E5C]/30 opacity-60' }} {{ $pasoFormulario == $s ? 'text-[#E27D60] -translate-y-0.5' : '' }}">
                                    {{ $p['l'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </header>

            {{-- CONTENIDO --}}
            <div class="flex-1 max-h-[62vh] overflow-y-auto custom-scrollbar px-4 py-3">
                
                {{-- PASO 1: IDENTIDAD --}}
                @if($pasoFormulario === 1)
                <div class="space-y-4 animate-in slide-in-from-right-4 duration-300">
                    <div class="flex items-center gap-3 border-b border-[#C7B5A3]/30 pb-2">
                        <i class="ph-fill ph-identification-card text-xl text-[#E27D60]"></i>
                        <h3 class="text-xs font-black text-[#2F3E5C] uppercase tracking-widest">Información Personal</h3>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Nombres *</label>
                            <input type="text" wire:model="nombres" placeholder="Ej. Carla Valeria"
                                   class="w-full h-10 rounded-xl border {{ $errors->has('nombres') ? 'border-[#E27D60] ring-4 ring-[#E27D60]/10' : 'border-[#C7B5A3] focus:border-[#2F3E5C]' }} bg-white px-4 py-2 text-sm font-bold uppercase text-[#2F3E5C] outline-none transition">
                            @error('nombres') <span class="mt-1 block text-[9px] font-black text-[#E27D60] uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Apellido Paterno *</label>
                            <input type="text" wire:model="ap_paterno" placeholder="Paterno"
                                   class="w-full h-10 rounded-xl border {{ $errors->has('ap_paterno') ? 'border-[#E27D60]' : 'border-[#C7B5A3]' }} focus:border-[#2F3E5C] bg-white px-4 py-2 text-sm font-bold uppercase text-[#2F3E5C] outline-none transition">
                            @error('ap_paterno') <span class="mt-1 block text-[9px] font-black text-[#E27D60] uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Apellido Materno</label>
                            <input type="text" wire:model="ap_materno" placeholder="Materno"
                                   class="w-full h-10 rounded-xl border border-[#C7B5A3] focus:border-[#2F3E5C] bg-white px-4 py-2 text-sm font-bold uppercase text-[#2F3E5C] outline-none transition">
                        </div>
                        <div>
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Género *</label>
                            <select wire:model="genero" class="w-full h-10 rounded-xl border {{ $errors->has('genero') ? 'border-[#E27D60]' : 'border-[#C7B5A3]' }} bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] outline-none transition">
                                <option value="">SELECCIONE...</option>
                                <option value="FEMENINO">FEMENINO</option>
                                <option value="MASCULINO">MASCULINO</option>
                                <option value="OTRO">OTRO</option>
                            </select>
                            @error('genero') <span class="mt-1 block text-[9px] font-black text-[#E27D60] uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Fecha Nacimiento *</label>
                            <input type="date" wire:model="fecha_nacimiento"
                                   class="w-full h-10 rounded-xl border {{ $errors->has('fecha_nacimiento') ? 'border-[#E27D60]' : 'border-[#C7B5A3]' }} bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] outline-none transition">
                            @error('fecha_nacimiento') <span class="mt-1 block text-[9px] font-black text-[#E27D60] uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">País Emisor *</label>
                            <select wire:model.live="pais_documento" class="w-full h-10 rounded-xl border border-[#C7B5A3] bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] outline-none transition">
                                <option value="Bolivia">Bolivia</option>
                                <option value="Brasil">Brasil</option>
                                <option value="Argentina">Argentina</option>
                                <option value="Perú">Perú</option>
                                <option value="Chile">Chile</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="col-span-1">
                                <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Tipo *</label>
                                <select wire:model.live="tipo_documento" class="w-full h-10 rounded-xl border border-[#C7B5A3] bg-white px-1 py-2 text-[10px] font-black text-[#2F3E5C] outline-none">
                                    <option value="CI">CI</option>
                                    <option value="PAS">PAS</option>
                                    <option value="DNI">DNI</option>
                                </select>
                            </div>
                            <div class="col-span-2 relative">
                                <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Número Doc *</label>
                                <input type="text" wire:model="numero_documento" placeholder="Ej. 1234567"
                                       class="w-full h-10 rounded-xl border {{ $errors->has('numero_documento') ? 'border-[#E27D60]' : 'border-[#C7B5A3]' }} bg-white px-4 py-2 text-sm font-bold uppercase text-[#2F3E5C] outline-none transition">
                                @if($pais_documento === 'Bolivia' && $tipo_documento === 'CI')
                                <div class="absolute right-1 top-6">
                                    <select wire:model="expedido" class="h-7 rounded-lg bg-[#D5C7B9]/50 border-none text-[8px] font-black text-[#2F3E5C] outline-none focus:ring-0">
                                        <option value="">EXP</option>
                                        @foreach(['LP','CB','SC','OR','PT','CH','TJ','BN','PD'] as $e)
                                            <option value="{{ $e }}">{{ $e }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                            @error('numero_documento') <div class="col-span-3"><span class="mt-1 text-[9px] font-black text-[#E27D60] uppercase">{{ $message }}</span></div> @enderror
                        </div>
                    </div>
                </div>
                @endif

                {{-- PASO 2: CONTACTO --}}
                @if($pasoFormulario === 2)
                <div class="space-y-4 animate-in slide-in-from-right-4 duration-300">
                    <div class="flex items-center gap-3 border-b border-[#C7B5A3]/30 pb-2">
                        <i class="ph-fill ph-phone-call text-xl text-[#E27D60]"></i>
                        <h3 class="text-xs font-black text-[#2F3E5C] uppercase tracking-widest">Canales de Contacto</h3>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 max-w-2xl">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Correo Institucional *</label>
                            <input type="email" wire:model="correo" placeholder="ejemplo@casaamandita.com"
                                   class="w-full h-10 rounded-xl border {{ $errors->has('correo') ? 'border-[#E27D60]' : 'border-[#C7B5A3]' }} bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#2F3E5C]">
                            @error('correo') <span class="mt-1 block text-[9px] font-black text-[#E27D60] uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="col-span-1">
                                <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">País</label>
                                <select wire:model.live="pais_telefono" class="w-full h-10 rounded-xl border border-[#C7B5A3] bg-white px-1 py-2 text-[9px] font-black text-[#2F3E5C] outline-none transition">
                                    <option value="Bolivia">BOL (+591)</option>
                                    <option value="Brasil">BRA (+55)</option>
                                    <option value="Argentina">ARG (+54)</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Celular *</label>
                                <input type="text" wire:model="telefono" placeholder="70012345"
                                       class="w-full h-10 rounded-xl border {{ $errors->has('telefono') ? 'border-[#E27D60]' : 'border-[#C7B5A3]' }} bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#2F3E5C]">
                                @error('telefono') <span class="mt-1 block text-[9px] font-black text-[#E27D60] uppercase">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- PASO 3: PERFIL INSTITUCIONAL --}}
                @if($pasoFormulario === 3)
                <div class="space-y-4 animate-in slide-in-from-right-4 duration-300">
                    <div class="flex items-center gap-3 border-b border-[#C7B5A3]/30 pb-2">
                        <i class="ph-fill ph-briefcase text-xl text-[#E27D60]"></i>
                        <h3 class="text-xs font-black text-[#2F3E5C] uppercase tracking-widest">Vinculación y Rol</h3>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 max-w-3xl">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Rol en el Sistema *</label>
                            <select wire:model.live="rol" class="w-full h-10 rounded-xl border {{ $errors->has('rol') ? 'border-[#E27D60]' : 'border-[#C7B5A3]' }} bg-white px-4 py-2 text-sm font-black text-[#2F3E5C] outline-none transition focus:border-[#2F3E5C]">
                                <option value="">SELECCIONE ROL...</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ strtoupper(str_replace('_', ' ', $r->name)) }}</option>
                                @endforeach
                            </select>
                            @error('rol') <span class="mt-1 block text-[9px] font-black text-[#E27D60] uppercase">{{ $message }}</span> @enderror
                        </div>

                        @if($rol === 'personal_salud')
                        <div class="md:col-span-2 animate-in fade-in duration-300">
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#E27D60]">Especialidad Médica *</label>
                            <select wire:model="especialidad_salud" class="w-full h-10 rounded-xl border border-[#C7B5A3] bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60]">
                                <option value="">SELECCIONE ESPECIALIDAD...</option>
                                @foreach($especialidades as $esp)
                                    <option value="{{ $esp->cod_esp }}">{{ $esp->nombre }}</option>
                                @endforeach
                            </select>
                            @error('especialidad_salud') <span class="mt-1 block text-[9px] font-black text-[#E27D60] uppercase">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        @if($rol === 'personal_admin')
                        <div class="md:col-span-2 animate-in fade-in duration-300">
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]">Cargo Administrativo *</label>
                            <select wire:model="cargo_administrativo" class="w-full h-10 rounded-xl border border-[#C7B5A3] bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#2F3E5C]">
                                <option value="">SELECCIONE CARGO...</option>
                                @foreach($cargosAdmin as $cargo)
                                    <option value="{{ $cargo->cod_cargo_admin }}">{{ $cargo->nombre }}</option>
                                @endforeach
                            </select>
                            @error('cargo_administrativo') <span class="mt-1 block text-[9px] font-black text-[#E27D60] uppercase">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        @if($usuarioId)
                        <div>
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Estado Perfil *</label>
                            <select wire:model="estado" class="w-full h-10 rounded-xl border border-[#C7B5A3] bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#2F3E5C]">
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                                <option value="ARCHIVADO">ARCHIVADO</option>
                            </select>
                        </div>
                        @endif
                        <div>
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Acceso Sistema *</label>
                            <select wire:model="acceso_sistema" class="w-full h-10 rounded-xl border border-[#C7B5A3] bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#2F3E5C]">
                                <option value="HABILITADO">HABILITADO</option>
                                <option value="BLOQUEADO">BLOQUEADO</option>
                            </select>
                        </div>
                    </div>
                </div>
                @endif

                {{-- PASO 4: SEGURIDAD --}}
                @if($pasoFormulario === 4)
                <div class="space-y-4 animate-in slide-in-from-right-4 duration-300">
                    <div class="flex items-center gap-3 border-b border-[#C7B5A3]/30 pb-2">
                        <i class="ph-fill ph-shield-check text-xl text-[#E27D60]"></i>
                        <h3 class="text-xs font-black text-[#2F3E5C] uppercase tracking-widest">Seguridad de Acceso</h3>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 max-w-2xl">
                        <div class="md:col-span-2">
                            @if(!$isEdit)
                                <div class="bg-[#2F3E5C]/5 border-l-4 border-[#2F3E5C] p-4 rounded-r-xl">
                                    <div class="flex items-center gap-2 mb-1">
                                        <i class="ph-bold ph-magic-wand text-[#2F3E5C] text-lg"></i>
                                        <h4 class="text-[10px] font-black text-[#2F3E5C] uppercase tracking-widest">Generación Automática</h4>
                                    </div>
                                    <p class="text-[10px] font-bold text-[#2F3E5C]/70 leading-relaxed">
                                        La contraseña temporal se generará automáticamente con los datos del usuario y su documento.
                                    </p>
                                </div>
                            @else
                                <div class="bg-[#E27D60]/5 border-l-4 border-[#E27D60] p-4 rounded-r-xl">
                                    <p class="text-[10px] font-bold text-[#E27D60] leading-relaxed">
                                        Deje en blanco si no desea cambiar la contraseña actual.
                                    </p>
                                </div>
                            @endif
                        </div>

                        @if($isEdit)
                        <div>
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Nueva Contraseña</label>
                            <input type="password" wire:model="password" placeholder="••••••••"
                                   class="w-full h-10 rounded-xl border {{ $errors->has('password') ? 'border-[#E27D60]' : 'border-[#C7B5A3]' }} bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#2F3E5C]">
                            @error('password') <span class="mt-1 block text-[9px] font-black text-[#E27D60] uppercase">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Confirmar</label>
                            <input type="password" wire:model="password_confirmation" placeholder="••••••••"
                                   class="w-full h-10 rounded-xl border border-[#C7B5A3] bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#2F3E5C]">
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- PASO 5: CONFIRMACIÓN --}}
                @if($pasoFormulario === 5)
                <div class="space-y-4 animate-in zoom-in duration-300">
                    <div class="flex items-center gap-3 border-b border-[#C7B5A3]/30 pb-2">
                        <i class="ph-fill ph-check-square text-xl text-[#E27D60]"></i>
                        <h3 class="text-xs font-black text-[#2F3E5C] uppercase tracking-widest">Resumen Final</h3>
                    </div>
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="rounded-xl bg-white/50 p-4 border border-[#C7B5A3]/40">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="h-10 w-10 rounded-xl bg-[#2F3E5C] flex items-center justify-center text-white text-lg font-black shadow-lg">
                                    {{ mb_substr($nombres, 0, 1) }}{{ mb_substr($ap_paterno ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-[#2F3E5C] uppercase leading-tight">{{ $nombres }} {{ $ap_paterno }}</h4>
                                    <p class="text-[8px] font-black text-[#E27D60] uppercase tracking-widest">{{ str_replace('_', ' ', $rol) }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-0.5">
                                    <p class="text-[8px] font-black text-[#2F3E5C]/40 uppercase">Documento</p>
                                    <p class="text-[10px] font-black text-[#2F3E5C] uppercase">{{ $numero_documento }} {{ $expedido }}</p>
                                </div>
                                <div class="space-y-0.5">
                                    <p class="text-[8px] font-black text-[#2F3E5C]/40 uppercase">Celular</p>
                                    <p class="text-[10px] font-black text-[#2F3E5C]">{{ $codigo_telefono }} {{ $telefono }}</p>
                                </div>
                                <div class="col-span-2 space-y-0.5">
                                    <p class="text-[8px] font-black text-[#2F3E5C]/40 uppercase">Correo</p>
                                    <p class="text-[10px] font-black text-[#2F3E5C] lowercase">{{ $correo }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-xl bg-white/50 p-4 border border-[#C7B5A3]/40 flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-[9px] font-black uppercase tracking-widest">
                                    <span class="text-[#2F3E5C]/50">Estado Inicial:</span>
                                    <span class="text-[#63775B] font-black bg-[#63775B]/10 px-2 py-0.5 rounded">{{ $usuarioId ? $estado : 'ACTIVO' }}</span>
                                </div>
                                <div class="flex justify-between items-center text-[9px] font-black uppercase tracking-widest">
                                    <span class="text-[#2F3E5C]/50">Acceso Sistema:</span>
                                    <span class="text-[#2F3E5C]">{{ $acceso_sistema }}</span>
                                </div>
                                @if(!$usuarioId)
                                <div class="mt-2 p-3 bg-[#2F3E5C] rounded-xl">
                                    <div class="flex items-center gap-2 mb-1">
                                        <i class="ph-bold ph-lock-key text-white text-base"></i>
                                        <span class="text-[8px] font-black text-white/70 uppercase">Credenciales</span>
                                    </div>
                                    <p class="text-[9px] font-bold text-white leading-relaxed">
                                        Se generará una contraseña temporal institucional.
                                    </p>
                                </div>
                                @endif
                            </div>
                            <p class="mt-3 text-[9px] font-bold text-[#2F3E5C]/40 text-center leading-relaxed italic">
                                Al confirmar, se {{ $usuarioId ? 'actualizarán los datos' : 'creará el registro' }} institucional.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- FOOTER FIJO --}}
            <footer class="border-t border-[#C7B5A3]/30 bg-[#D5C7B9]/30 px-4 py-2.5 backdrop-blur-xl shrink-0 flex flex-col-reverse sm:flex-row items-center justify-between gap-2">
                <button type="button" wire:click="cerrarFormulario" 
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl border-2 border-[#2F3E5C]/10 text-[#2F3E5C]/40 text-[9px] font-black uppercase tracking-widest transition hover:bg-[#2F3E5C] hover:text-white active:scale-95 shadow-sm">
                    Cancelar
                </button>
                
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    @if($pasoFormulario > 1)
                    <button type="button" wire:click="anteriorPaso" 
                            class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl border-2 border-[#2F3E5C] text-[#2F3E5C] text-[9px] font-black uppercase tracking-widest transition hover:bg-[#2F3E5C] hover:text-white active:scale-95">
                        Anterior
                    </button>
                    @endif

                    @if($pasoFormulario < 5)
                    <button type="button" wire:click="siguientePaso" 
                            class="flex-1 sm:flex-none px-10 py-2.5 rounded-xl bg-[#2F3E5C] text-white text-[9px] font-black uppercase tracking-widest shadow-xl shadow-[#2F3E5C]/20 transition hover:bg-[#E27D60] active:scale-95">
                        Continuar <i class="ph-bold ph-arrow-right ml-1"></i>
                    </button>
                    @else
                    <button type="button" wire:click="guardarUsuario" wire:loading.attr="disabled"
                            class="flex-1 sm:flex-none px-12 py-2.5 rounded-xl bg-[#E27D60] text-white text-[9px] font-black uppercase tracking-widest shadow-xl shadow-[#E27D60]/20 transition hover:bg-[#2F3E5C] active:scale-95 disabled:opacity-50">
                        <span wire:loading.remove>{{ $usuarioId ? 'Actualizar' : 'Confirmar' }}</span>
                        <span wire:loading><i class="ph-bold ph-circle-notch animate-spin mr-1"></i>...</span>
                    </button>
                    @endif
                </div>
            </footer>
        </div>
    </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(209, 184, 157, 0.2);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #C7B5A3;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #2F3E5C;
        }
    </style>
</div>
