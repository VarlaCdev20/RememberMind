<x-app-layout>
    <div class="relative min-h-screen bg-[#D5C7B9] font-outfit text-azul-profundo">
        <div class="dash-noise pointer-events-none fixed inset-0 z-[60] opacity-[0.14] mix-blend-overlay"></div>
        
        <main class="relative z-10 mx-auto max-w-5xl px-4 pb-12 pt-8 sm:px-6 lg:px-8">
            <header class="mb-8">
                <nav class="mb-2 flex items-center gap-2 text-xs font-black uppercase tracking-widest text-terracota">
                    <a href="{{ route('admin.usuarios.index') }}" class="transition hover:text-azul-profundo">Usuarios</a>
                    <i class="ph-bold ph-caret-right text-[10px]"></i>
                    <span>Expediente de Usuario</span>
                </nav>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-3xl font-black text-azul-profundo">
                        Ficha <span class="text-terracota">Institucional</span>
                    </h1>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.usuarios.index') }}" 
                           class="inline-flex items-center gap-2 rounded-full border border-[#C7B5A3] px-5 py-2 text-xs font-black text-azul-profundo transition hover:bg-[#C7B5A3] active:scale-95">
                            <i class="ph-bold ph-arrow-left"></i>
                            Volver
                        </a>
                        <a href="{{ route('admin.usuarios.edit', $usuario->cod_usu) }}" 
                           class="inline-flex items-center gap-2 rounded-full bg-azul-profundo px-6 py-2 text-xs font-black text-white transition hover:bg-terracota active:scale-95 shadow-lg">
                            <i class="ph-bold ph-pencil-simple"></i>
                            Editar Perfil
                        </a>
                    </div>
                </div>
            </header>

            <section class="rounded-[2.5rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-8 shadow-[0_16px_38px_rgba(47,62,92,0.12)] backdrop-blur-xl">
                {{-- Encabezado de Perfil --}}
                <div class="mb-8 flex flex-col items-center gap-4 sm:flex-row sm:gap-8">
                    <div class="relative">
                        @if($usuario->foto_de_perfil)
                            <img src="{{ asset('storage/'.$usuario->foto_de_perfil) }}" alt="{{ $usuario->name }}" class="h-28 w-28 rounded-[2rem] object-cover border-4 border-white shadow-xl">
                        @else
                            <div class="flex h-28 w-28 items-center justify-center rounded-[2rem] bg-azul-profundo text-4xl font-black text-white shadow-xl border-4 border-white">
                                {{ strtoupper(substr($usuario->nombres, 0, 1)) }}{{ strtoupper(substr($usuario->ap_paterno ?? '', 0, 1)) }}
                            </div>
                        @endif
                        <div class="absolute -bottom-1 -right-1 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white {{ $usuario->estado === 'ACTIVO' ? 'bg-[#8DA280]' : 'bg-[#967B66]' }} text-white shadow-md">
                            <i class="ph-bold {{ $usuario->estado === 'ACTIVO' ? 'ph-check' : 'ph-x' }} text-xs"></i>
                        </div>
                    </div>
                    <div class="text-center sm:text-left">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-terracota">{{ $usuario->cod_usu }}</p>
                        <h2 class="text-3xl font-black text-azul-profundo leading-none mt-1">{{ $usuario->name }}</h2>
                        <div class="mt-3 flex flex-wrap justify-center gap-2 sm:justify-start">
                            <span class="rounded-full bg-azul-profundo/10 px-4 py-1 text-[10px] font-black uppercase text-azul-profundo">
                                @php
                                    $roleName = $usuario->getRoleNames()->first() ?? 'Sin rol';
                                    $roleDisplay = match($roleName) {
                                        'personal_salud' => 'PERSONAL DE SALUD',
                                        'personal_admin' => 'PERSONAL ADMINISTRATIVO',
                                        default => strtoupper(str_replace('_', ' ', $roleName))
                                    };
                                @endphp
                                <i class="ph-bold ph-shield-star mr-1"></i> {{ $roleDisplay }}
                            </span>
                            <span class="rounded-full {{ $usuario->acceso_sistema === 'HABILITADO' ? 'bg-[#8DA280]/15 text-[#63775B]' : 'bg-terracota/15 text-terracota' }} px-4 py-1 text-[10px] font-black">
                                <i class="ph-bold {{ $usuario->acceso_sistema === 'HABILITADO' ? 'ph-lock-open' : 'ph-lock' }} mr-1"></i> ACCESO {{ $usuario->acceso_sistema }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-10 border-t border-[#C7B5A3]/40 pt-8 md:grid-cols-2 lg:grid-cols-3">
                    {{-- Datos de Identidad --}}
                    <div class="space-y-6">
                        <div>
                            <h3 class="mb-4 flex items-center gap-2 text-xs font-black uppercase tracking-widest text-terracota">
                                <i class="ph-bold ph-identification-badge text-lg"></i> Identidad
                            </h3>
                            <div class="space-y-4 bg-white/30 rounded-2xl p-5 border border-[#C7B5A3]/30 shadow-inner">
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-tighter text-azul-profundo/40">Documento de Identidad</p>
                                    <p class="text-sm font-bold text-azul-profundo">
                                        {{ $usuario->tipo_documento ?? 'CI' }}: {{ $usuario->numero_documento ?? '---' }}
                                        @if($usuario->expedido) <span class="ml-1 text-[10px] text-terracota font-black">({{ $usuario->expedido }})</span> @endif
                                    </p>
                                    <p class="text-[10px] font-bold text-azul-profundo/40 uppercase">{{ $usuario->pais_documento ?? 'Bolivia' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-tighter text-azul-profundo/40">Sexo / Nacimiento</p>
                                    <p class="text-sm font-bold text-azul-profundo uppercase">{{ $usuario->genero ?? 'Sin registrar' }}</p>
                                    @if($usuario->fecha_nacimiento)
                                        <div class="flex items-center gap-2">
                                            <p class="text-[10px] font-bold text-azul-profundo/40">{{ \Carbon\Carbon::parse($usuario->fecha_nacimiento)->format('d/m/Y') }}</p>
                                            <span class="text-[9px] font-black bg-terracota/10 text-terracota px-2 py-0.5 rounded-md shadow-sm">
                                                {{ \Carbon\Carbon::parse($usuario->fecha_nacimiento)->age }} AÑOS
                                            </span>
                                        </div>
                                    @else
                                        <p class="text-[10px] font-bold text-azul-profundo/40 italic">Fecha sin registrar</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Contacto --}}
                    <div class="space-y-6">
                        <div>
                            <h3 class="mb-4 flex items-center gap-2 text-xs font-black uppercase tracking-widest text-terracota">
                                <i class="ph-bold ph-phone-call text-lg"></i> Contacto
                            </h3>
                            <div class="space-y-4 bg-white/30 rounded-2xl p-5 border border-[#C7B5A3]/30 shadow-inner">
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-tighter text-azul-profundo/40">Correo Institucional</p>
                                    <p class="text-sm font-bold text-azul-profundo break-all lowercase">{{ $usuario->correo }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-tighter text-azul-profundo/40">Número de Celular</p>
                                    <p class="text-sm font-bold text-azul-profundo">
                                        @if($usuario->codigo_telefono) <span class="text-terracota font-black">{{ $usuario->codigo_telefono }}</span> @endif
                                        {{ $usuario->telefono ?? 'Sin registrar' }}
                                    </p>
                                    @if($usuario->pais_telefono) <p class="text-[10px] font-bold text-azul-profundo/40 uppercase">{{ $usuario->pais_telefono }}</p> @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Institucional --}}
                    <div class="space-y-6">
                        <div>
                            <h3 class="mb-4 flex items-center gap-2 text-xs font-black uppercase tracking-widest text-terracota">
                                <i class="ph-bold ph-buildings text-lg"></i> Ficha Institucional
                            </h3>
                            <div class="space-y-4 bg-white/30 rounded-2xl p-5 border border-[#C7B5A3]/30 shadow-inner">
                                @if($usuario->hasRole('personal_salud'))
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-tighter text-azul-profundo/40">Especialidad Médica</p>
                                        <p class="text-sm font-black text-azul-profundo uppercase">{{ $usuario->personalSalud->first()?->especialidad?->nombre ?? 'Sin especialidad' }}</p>
                                    </div>
                                    @if($usuario->personalSalud->first()?->fecha_ing)
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-tighter text-azul-profundo/40">Fecha de Ingreso</p>
                                        <p class="text-sm font-bold text-azul-profundo">{{ \Carbon\Carbon::parse($usuario->personalSalud->first()->fecha_ing)->format('d/m/Y') }}</p>
                                    </div>
                                    @endif
                                @elseif($usuario->hasRole('personal_admin'))
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-tighter text-azul-profundo/40">Función Administrativa</p>
                                        <p class="text-sm font-black text-azul-profundo uppercase">{{ $usuario->personalAdmin->first()?->cargoAdmin?->nombre ?? ($usuario->personalAdmin->first()?->cargo ?? 'Sin cargo') }}</p>
                                    </div>
                                    @if($usuario->personalAdmin->first()?->fecha_ingreso)
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-tighter text-azul-profundo/40">Fecha de Ingreso</p>
                                        <p class="text-sm font-bold text-azul-profundo">{{ \Carbon\Carbon::parse($usuario->personalAdmin->first()->fecha_ingreso)->format('d/m/Y') }}</p>
                                    </div>
                                    @endif
                                @else
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-tighter text-azul-profundo/40">Rol del Sistema</p>
                                        <p class="text-sm font-black text-azul-profundo uppercase">{{ $roleDisplay }}</p>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-tighter text-azul-profundo/40">Estado de Perfil</p>
                                    <span class="inline-flex rounded-full {{ $usuario->estado === 'ACTIVO' ? 'bg-[#8DA280]/15 text-[#63775B]' : 'bg-terracota/15 text-terracota' }} px-3 py-0.5 text-[9px] font-black shadow-sm">{{ $usuario->estado }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($usuario->observaciones)
                <div class="mt-8 border-t border-[#C7B5A3]/40 pt-6">
                    <p class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/40 mb-2">Observaciones Institucionales</p>
                    <p class="text-sm font-bold text-azul-profundo/70 italic bg-white/20 p-4 rounded-2xl uppercase border border-[#C7B5A3]/20 shadow-inner leading-relaxed">{{ $usuario->observaciones }}</p>
                </div>
                @endif
                
                <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-[#C7B5A3]/40 pt-8 sm:flex-row">
                    <div class="flex items-center gap-2 text-[10px] font-bold text-azul-profundo/40">
                        <i class="ph-bold ph-calendar"></i> Registro: {{ $usuario->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex items-center gap-2 text-[10px] font-bold text-azul-profundo/40">
                        <i class="ph-bold ph-pencil-simple"></i> Actualización: {{ $usuario->updated_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex items-center gap-2 text-[10px] font-bold text-azul-profundo/40">
                        <i class="ph-bold ph-clock"></i> Último Acceso: {{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->format('d/m/Y H:i') : 'Sin registros' }}
                    </div>
                </div>
            </section>
        </main>
    </div>
</x-app-layout>
