<div>

    {{-- Encabezado --}}
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#E27D60]">
                <a href="{{ route('dashboard') }}" class="transition hover:text-[#2F3E5C]">Dashboard</a>
                <i class="ph-bold ph-caret-right text-[10px]"></i>
                <span>Usuarios</span>
            </nav>
            <h1 class="text-3xl font-black text-[#2F3E5C] md:text-4xl">
                Gestión de <span class="text-[#E27D60]">Usuarios</span>
            </h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" 
               class="inline-flex items-center gap-2 rounded-full bg-white border border-[#C7B5A3] px-6 py-3 text-sm font-black text-[#2F3E5C] shadow-sm transition-all hover:-translate-y-1 hover:shadow-md active:translate-y-1 active:scale-95">
                <i class="ph-bold ph-layout text-xl"></i>
                Dashboard
            </a>

            <button type="button" wire:click="crearUsuario" 
               class="inline-flex items-center gap-2 rounded-full bg-[#E27D60] px-6 py-3 text-sm font-black text-white shadow-lg transition-all hover:-translate-y-1 hover:shadow-xl active:translate-y-1 active:scale-95">
                <i class="ph-bold ph-plus-circle text-xl"></i>
                Nuevo usuario
            </button>
        </div>
    </header>

    {{-- Filtros y Búsqueda --}}
    <div class="mb-6 grid gap-4 md:grid-cols-3 xl:grid-cols-4 relative">
        <div class="group relative md:col-span-2 xl:col-span-2">
            <div class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none">
                <i class="ph-bold ph-magnifying-glass text-[#2F3E5C]/40 transition-colors group-focus-within:text-[#E27D60] text-lg"></i>
            </div>
            <input type="text" 
                   wire:model.live.debounce.300ms="search"
                   placeholder="Buscar por nombre, código o correo..."
                   class="w-full pl-12 pr-14 py-3 bg-[#E6DDD3]/90 border-2 border-[#C7B5A3]/50 rounded-2xl text-sm font-bold text-[#2F3E5C] placeholder:text-[#2F3E5C]/40 outline-none transition-all focus:border-[#E27D60]/60 focus:bg-white focus:shadow-xl focus:ring-4 focus:ring-[#E27D60]/5">
            
            <div class="absolute inset-y-0 right-0 flex items-center pr-6 pointer-events-none">
                <span class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/30" wire:loading wire:target="search">...</span>
            </div>
        </div>

        <div>
            <select wire:model.live="filtroRol" class="w-full py-3 px-4 bg-[#E6DDD3]/90 border-2 border-[#C7B5A3]/50 rounded-2xl text-sm font-bold text-[#2F3E5C] outline-none transition-all focus:border-[#E27D60]/60 focus:bg-white focus:ring-4 focus:ring-[#E27D60]/5">
                <option value="">Todos los roles</option>
                @foreach($roles as $r)
                    <option value="{{ $r->name }}">{{ strtoupper(str_replace('_', ' ', $r->name)) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <select wire:model.live="filtroEstado" class="w-full py-3 px-4 bg-[#E6DDD3]/90 border-2 border-[#C7B5A3]/50 rounded-2xl text-sm font-bold text-[#2F3E5C] outline-none transition-all focus:border-[#E27D60]/60 focus:bg-white focus:ring-4 focus:ring-[#E27D60]/5">
                <option value="">Todos los estados</option>
                <option value="ACTIVO">Activos</option>
                <option value="INACTIVO">Inactivos</option>
            </select>
        </div>
    </div>

    {{-- Tabla --}}
    <section class="rounded-[2.5rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-6 shadow-[0_16px_38px_rgba(47,62,92,0.12)] backdrop-blur-xl relative">
        <div wire:loading.delay class="absolute inset-0 bg-white/50 backdrop-blur-sm z-10 rounded-[2.5rem] flex items-center justify-center">
            <i class="ph-bold ph-spinner animate-spin text-4xl text-[#E27D60]"></i>
        </div>
        
        <div class="overflow-x-auto rounded-[1.6rem] border border-[#C7B5A3]/70">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-[#D5C7B9] text-[11px] uppercase tracking-widest text-[#2F3E5C]/55">
                    <tr>
                        <th class="px-6 py-4 font-black">Usuario</th>
                        <th class="px-6 py-4 font-black">Correo</th>
                        <th class="px-6 py-4 font-black">Rol</th>
                        <th class="px-6 py-4 font-black">Estado</th>
                        <th class="px-6 py-4 font-black text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#C7B5A3]/60 bg-[#E6DDD3]/60">
                    @forelse($usuarios as $u)
                        @php
                            $roleName = $u->getRoleNames()->first() ?? 'Sin rol';
                            $roleDisplay = match($roleName) {
                                'personal_salud' => 'PERSONAL DE SALUD',
                                'personal_admin' => 'PERSONAL ADMINISTRATIVO',
                                'familiar'       => 'FAMILIAR / RESPONSABLE',
                                'voluntario'     => 'VOLUNTARIO',
                                default          => strtoupper(str_replace('_', ' ', $roleName))
                            };
                        @endphp
                        <tr class="transition-colors hover:bg-[#D5C7B9]/40" wire:key="user-{{ $u->cod_usu }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#2F3E5C] text-xs font-black text-white shadow-sm">
                                        {{ strtoupper(substr($u->nombres, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-black text-[#2F3E5C]">{{ $u->name }}</p>
                                        <p class="text-[10px] font-bold text-[#2F3E5C]/40 uppercase tracking-tighter">{{ $u->cod_usu }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-bold text-[#2F3E5C]/70">{{ $u->correo }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full bg-[#E27D60]/10 px-3 py-1 text-[11px] font-black uppercase text-[#E27D60]">
                                    {{ $roleDisplay }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($u->estado === 'ACTIVO')
                                    <span class="rounded-full bg-[#8DA280]/15 px-3 py-1 text-[10px] font-black text-[#63775B]">ACTIVO</span>
                                @else
                                    <span class="rounded-full bg-[#967B66]/15 px-3 py-1 text-[10px] font-black text-[#967B66]">INACTIVO</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.usuarios.show', $u->cod_usu) }}" 
                                       class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-[#2F3E5C] shadow-sm transition hover:bg-[#2F3E5C] hover:text-white active:scale-90"
                                       title="Ver detalle">
                                        <i class="ph-bold ph-eye"></i>
                                    </a>
                                    <button type="button" wire:click="editarUsuario('{{ $u->cod_usu }}')" 
                                       class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-[#E27D60] shadow-sm transition hover:bg-[#E27D60] hover:text-white active:scale-90"
                                       title="Editar">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </button>
                                    
                                    @if($u->cod_usu !== auth()->id())
                                        <button wire:click="toggleEstado('{{ $u->cod_usu }}')"
                                                wire:confirm="¿Desea cambiar el estado de este usuario?"
                                                class="flex h-9 w-9 items-center justify-center rounded-xl shadow-sm transition active:scale-90 {{ $u->estado === 'ACTIVO' ? 'bg-[#967B66]/10 text-[#967B66] hover:bg-[#967B66] hover:text-white' : 'bg-[#8DA280]/10 text-[#8DA280] hover:bg-[#8DA280] hover:text-white' }}"
                                                title="{{ $u->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}">
                                            <i class="ph-bold {{ $u->estado === 'ACTIVO' ? 'ph-user-minus' : 'ph-user-plus' }}"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-[#2F3E5C]/40 font-bold italic">
                                No se encontraron usuarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($usuarios->hasPages())
        <div class="mt-4">
            {{ $usuarios->links() }}
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
