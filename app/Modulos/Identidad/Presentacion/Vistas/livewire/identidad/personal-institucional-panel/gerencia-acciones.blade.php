<div class="rm-management-actions">
    @if(!$usuario->hasAnyRole(['GERENTE','SUPERADMINISTRADOR']) && auth()->user()->can('personal.gestionar'))
        <button type="button" class="rm-btn-secondary" wire:click="abrirModalEdicion('{{ $usuario->getKey() }}')">Ver ficha y documentos</button>
        <button type="button" class="rm-btn-secondary" wire:click="toggleEstado('{{ $usuario->getKey() }}', '{{ $usuario->estado }}')"
            wire:confirm="{{ $usuario->estado === 'ACTIVO' ? '¿Suspender el acceso de esta cuenta? Su ficha laboral y sus asignaciones se conservarán.' : '¿Activar el acceso de esta cuenta? Esto no modifica su estado laboral ni acredita competencia profesional.' }}" wire:loading.attr="disabled">
            {{ $usuario->estado === 'ACTIVO' ? 'Suspender acceso' : 'Activar acceso' }}
        </button>
        @if($usuario->estado === 'ACTIVO')
            <button type="button" class="rm-btn-secondary" wire:click="enviarRecuperacion('{{ $usuario->getKey() }}')"
                wire:confirm="¿Solicitar un enlace de recuperación al correo registrado de {{ $usuario->name }}? La contraseña la elegirá el titular."
                wire:loading.attr="disabled" wire:target="enviarRecuperacion">
                <span wire:loading.remove wire:target="enviarRecuperacion">Enviar enlace de acceso</span>
                <span wire:loading wire:target="enviarRecuperacion">Solicitando enlace…</span>
            </button>
        @endif
        @if($ampliacionDisponible)
            <button type="button" class="rm-btn-secondary" wire:click="abrirEstadoLaboral('{{ $usuario->getKey() }}')">Cambiar situación laboral</button>
        @endif
    @endif
    @can('asignaciones_personal.ver')
        <button type="button" class="rm-btn-secondary" wire:click="abrirHorariosPersonal('{{ $usuario->getKey() }}')">Consultar horarios</button>
    @endcan
</div>
