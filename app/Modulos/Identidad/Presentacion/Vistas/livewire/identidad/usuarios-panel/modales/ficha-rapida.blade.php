@if($usuarioFicha)
<x-ui.drawer-livewire
    wire:model="mostrarFichaRapida"
    title="Ficha Rápida"
    subtitle="Resumen institucional de cuenta y perfil"
    badge="PANEL LATERAL DE USUARIO"
    icon="ph-user"
    size="lg"
    close-method="cerrarFichaRapida"
    :dismiss-on-backdrop="true">

    @php
        $fichaRoleName = $usuarioFicha->getRoleNames()->first() ?? 'sin_rol';
        $fichaRoleKey = strtolower($fichaRoleName);
        $fichaNombreCompleto = trim(($usuarioFicha->nombres ?? '') . ' ' . ($usuarioFicha->ap_paterno ?? '') . ' ' . ($usuarioFicha->ap_materno ?? ''));
        $fichaInicial = mb_substr(trim($usuarioFicha->nombres ?? 'U'), 0, 1);

        $fichaAreaDisplay = $usuarioFicha->areaInstitucional?->nombre ?? match($fichaRoleKey) {
            'super_admin', 'admin' => 'Administración del sistema',
            'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => 'Área de salud',
            'superadministrador', 'administrador' => 'Área administrativa',
            'FAMILIAR' => 'Familiar autorizado',
            default => 'Sin área asignada'
        };

        $fichaPerfilDetalle = match($fichaRoleKey) {
            'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => $usuarioFicha->personalSalud?->especialidad?->nombre ?? 'Personal de salud',
            'superadministrador', 'administrador' => $usuarioFicha->personalAdmin?->cargoAdmin?->nombre ?? $usuarioFicha->personalAdmin?->cargo ?? 'Personal administrativo',
            'super_admin', 'admin' => 'Administrador del sistema',
            'FAMILIAR' => 'Familiar autorizado',
            default => strtoupper(str_replace('_', ' ', $fichaRoleName))
        };

        $fichaFoto = null;
        if (!empty($usuarioFicha->foto_de_perfil)) {
            $fichaFoto = \Illuminate\Support\Facades\Storage::url($usuarioFicha->foto_de_perfil);
        } elseif (!empty($usuarioFicha->profile_photo_url)) {
            $fichaFoto = $usuarioFicha->profile_photo_url;
        }
    @endphp

    <div class="space-y-5">
        {{-- Avatar y nombre --}}
        <div class="flex flex-col items-center text-center p-4 rounded-2xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)]">
            <div class="relative">
                @if($fichaFoto)
                    <img src="{{ $fichaFoto }}" alt="{{ $fichaNombreCompleto }}"
                         class="h-16 w-16 rounded-2xl object-cover ring-2 ring-[var(--rm-border)] shadow-md">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[var(--rm-action-primary)] text-2xl font-black text-white ring-2 ring-[var(--rm-border)] shadow-md">
                        {{ strtoupper($fichaInicial) }}
                    </div>
                @endif
                <span class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full border-2 border-[var(--rm-surface)] {{ $usuarioFicha->estado === 'ACTIVO' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
            </div>

            <h3 class="mt-3 text-lg font-extrabold uppercase text-[var(--rm-text-primary)] leading-tight">{{ $fichaNombreCompleto }}</h3>
            <p class="mt-0.5 text-xs font-medium text-[var(--rm-text-secondary)] lowercase">{{ $usuarioFicha->correo ?: 'Sin correo' }}</p>

            <div class="mt-2.5 flex flex-wrap justify-center gap-2">
                <span class="rm-badge {{ $usuarioFicha->estado === 'ACTIVO' ? 'rm-badge-success' : 'rm-badge-neutral' }} text-[10px] font-bold uppercase">
                    {{ $usuarioFicha->estado }}
                </span>
                <span class="rm-badge rm-badge-info text-[10px] font-bold uppercase">
                    {{ match($fichaRoleKey) {
                        'superadministrador', 'administrador' => 'PERSONAL ADMINISTRATIVO',
                        'enfermeros', 'medico general/geriatra', 'psicologo/a', 'pedagogo', 'nutricionista', 'fisioterapeuta' => 'PERSONAL DE SALUD',
                        'FAMILIAR' => 'FAMILIAR AUTORIZADO',
                        default => strtoupper(str_replace('_', ' ', $fichaRoleName)),
                    } }}
                </span>
            </div>
        </div>

        {{-- Datos en bloques --}}
        <div class="space-y-3">
            <div class="rounded-2xl border border-[var(--rm-border-soft)] bg-[var(--rm-surface)] p-4 space-y-2.5 shadow-2xs">
                <h4 class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-[var(--rm-action-primary)]">
                    <i class="ph-bold ph-buildings text-base"></i> Perfil Institucional
                </h4>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Área</p>
                        <p class="font-bold text-[var(--rm-text-primary)] mt-0.5">{{ $fichaAreaDisplay }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Perfil</p>
                        <p class="font-bold text-[var(--rm-text-primary)] uppercase mt-0.5">{{ $fichaPerfilDetalle }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-[var(--rm-border-soft)] bg-[var(--rm-surface)] p-4 space-y-2.5 shadow-2xs">
                <h4 class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-[var(--rm-action-primary)]">
                    <i class="ph-bold ph-phone-call text-base"></i> Contacto
                </h4>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Teléfono</p>
                        <p class="font-bold text-[var(--rm-text-primary)] mt-0.5">{{ $usuarioFicha->codigo_telefono }} {{ $usuarioFicha->telefono ?? 'Sin registrar' }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Correo</p>
                        <p class="font-bold text-[var(--rm-text-primary)] lowercase break-all mt-0.5">{{ $usuarioFicha->correo ?: 'Sin registrar' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-[var(--rm-border-soft)] bg-[var(--rm-surface)] p-4 space-y-2.5 shadow-2xs">
                <h4 class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-[var(--rm-action-primary)]">
                    <i class="ph-bold ph-clock text-base"></i> Acceso y Registro
                </h4>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Último acceso</p>
                        <p class="font-bold text-[var(--rm-text-primary)] mt-0.5">
                            {{ $usuarioFicha->ultimo_acceso ? $usuarioFicha->ultimo_acceso->format('d/m/Y H:i') : 'Sin registro' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Creación</p>
                        <p class="font-bold text-[var(--rm-text-primary)] mt-0.5">
                            {{ $usuarioFicha->created_at ? $usuarioFicha->created_at->format('d/m/Y') : 'Sin dato' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Acceso</p>
                        <p class="font-bold mt-0.5 {{ $usuarioFicha->acceso_sistema === 'HABILITADO' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $usuarioFicha->acceso_sistema }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer acciones rápidas --}}
    <x-slot:footer>
        <div class="flex flex-wrap items-center justify-end gap-2 w-full">
            <button type="button"
                    wire:click="abrirVistaCompleta('{{ $usuarioFicha->cod_usuario }}')"
                    class="rm-btn rm-btn-secondary rm-btn-sm">
                <i class="ph-bold ph-eye text-base"></i>
                <span>Ver completo</span>
            </button>

            @can('usuarios.editar')
                @if($usuarioFicha->estado === 'ACTIVO')
                    <button type="button"
                            wire:click="editarUsuario('{{ $usuarioFicha->cod_usuario }}')"
                            onclick="@this.cerrarFichaRapida()"
                            class="rm-btn rm-btn-primary rm-btn-sm">
                        <i class="ph-bold ph-pencil-simple text-base"></i>
                        <span>Editar</span>
                    </button>
                @endif
            @endcan

            @can('usuarios.cambiar_estado')
                @if($usuarioFicha->cod_usuario !== auth()->id())
                    <button type="button"
                            wire:click="toggleEstado('{{ $usuarioFicha->cod_usuario }}')"
                            wire:confirm="¿Desea cambiar el estado de este usuario?"
                            class="rm-btn rm-btn-sm {{ $usuarioFicha->estado === 'ACTIVO' ? 'rm-btn-secondary text-[var(--rm-danger)]' : 'rm-btn-success' }}">
                        <i class="ph-bold {{ $usuarioFicha->estado === 'ACTIVO' ? 'ph-user-minus' : 'ph-user-plus' }} text-base"></i>
                        <span>{{ $usuarioFicha->estado === 'ACTIVO' ? 'Inactivar' : 'Activar' }}</span>
                    </button>
                @endif
            @endcan
        </div>
    </x-slot:footer>
</x-ui.drawer-livewire>
@endif
