@props(['usuarios' => []])

<section class="dash-anim rm-card p-5">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <span class="text-[11px] font-black uppercase tracking-widest text-boton-acento">
                Usuarios y roles
            </span>

            <h2 class="text-xl font-black text-titulo">
                Registro general de usuarios
            </h2>

            <p class="text-xs font-bold text-apoyo">
                Control de acceso, rol asignado y estado de cuenta.
            </p>
        </div>

        <a href="{{ route('admin.usuarios.index') }}" 
           class="rm-btn rm-btn-secondary text-xs">
            Gestionar usuarios
        </a>
    </div>

    <div class="overflow-x-auto rounded-[1.4rem] border border-borde-suave">
        <table class="min-w-full text-left text-sm rm-table">
            <thead class="rm-table-header">
                <tr>
                    <th class="px-4 py-3 font-black">Usuario</th>
                    <th class="px-4 py-3 font-black">Correo</th>
                    <th class="px-4 py-3 font-black">Rol</th>
                    <th class="px-4 py-3 font-black">Estado</th>
                    <th class="px-4 py-3 font-black text-center">Acción</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-borde-suave bg-fondo-tabla">
                @forelse($usuarios as $usuario)
                    <tr class="rm-table-row">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-boton-principal text-xs font-black text-boton-principalTexto">
                                    {{ strtoupper(substr($usuario['nombre'] ?? 'U', 0, 1)) }}
                                </div>

                                <span class="font-black text-titulo">
                                    {{ $usuario['nombre'] ?? 'Usuario' }}
                                </span>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-xs font-bold text-apoyo">
                            {{ $usuario['correo'] ?? 'Sin correo' }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="rm-badge rm-badge-warning">
                                {{ $usuario['rol'] ?? 'Sin rol' }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <span class="rm-badge rm-badge-success">
                                {{ $usuario['estado'] ?? 'Activo' }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.usuarios.show', $usuario['cod_usu']) }}" 
                               class="rm-btn rm-btn-secondary text-xs py-1.5 px-3">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm font-bold text-apoyo">
                            No hay usuarios disponibles para mostrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>