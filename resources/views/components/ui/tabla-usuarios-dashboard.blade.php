@props(['usuarios' => []])

<section class="dash-anim rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-5 shadow-[0_16px_38px_rgba(47,62,92,0.12)] backdrop-blur-xl">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <span class="text-[11px] font-black uppercase tracking-widest text-terracota">
                Usuarios y roles
            </span>

            <h2 class="text-xl font-black text-azul-profundo">
                Registro general de usuarios
            </h2>

            <p class="text-xs font-bold text-azul-profundo/55">
                Control de acceso, rol asignado y estado de cuenta.
            </p>
        </div>

        <a href="{{ route('admin.usuarios.index') }}" 
           class="rounded-full bg-[#D5C7B9] px-4 py-2 text-xs font-black text-terracota shadow-sm transition-all duration-200 hover:scale-95 hover:bg-terracota hover:text-white active:scale-90">
            Gestionar usuarios
        </a>
    </div>

    <div class="overflow-x-auto rounded-[1.4rem] border border-[#C7B5A3]/70">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-[#D5C7B9] text-[11px] uppercase tracking-widest text-azul-profundo/55">
                <tr>
                    <th class="px-4 py-3 font-black">Usuario</th>
                    <th class="px-4 py-3 font-black">Correo</th>
                    <th class="px-4 py-3 font-black">Rol</th>
                    <th class="px-4 py-3 font-black">Estado</th>
                    <th class="px-4 py-3 font-black text-center">Acción</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-[#C7B5A3]/60 bg-[#E6DDD3]/60">
                @forelse($usuarios as $usuario)
                    <tr class="transition hover:bg-[#D5C7B9]/75">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-azul-profundo text-xs font-black text-white">
                                    {{ strtoupper(substr($usuario['nombre'] ?? 'U', 0, 1)) }}
                                </div>

                                <span class="font-black text-azul-profundo">
                                    {{ $usuario['nombre'] ?? 'Usuario' }}
                                </span>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-xs font-bold text-azul-profundo/60">
                            {{ $usuario['correo'] ?? 'Sin correo' }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="rounded-full bg-terracota/10 px-3 py-1 text-xs font-black text-terracota">
                                {{ $usuario['rol'] ?? 'Sin rol' }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <span class="rounded-full bg-[#8DA280]/15 px-3 py-1 text-xs font-black text-[#63775B]">
                                {{ $usuario['estado'] ?? 'Activo' }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.usuarios.show', $usuario['cod_usu']) }}" 
                               class="inline-block rounded-full bg-[#D5C7B9] px-3 py-1.5 text-xs font-black text-azul-profundo shadow-sm transition-all duration-200 hover:scale-95 hover:bg-azul-profundo hover:text-white active:scale-90">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm font-bold text-azul-profundo/55">
                            No hay usuarios disponibles para mostrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>