@php $puedeGestionar = !$usuario->hasAnyRole(['GERENTE','SUPERADMINISTRADOR']) && auth()->user()->can('personal.gestionar'); @endphp
<div class="rm-staff-directory__actions">
    @if(!$puedeGestionar && ($modoAcciones ?? 'completo') === 'ficha')<span aria-label="Ficha no disponible para esta cuenta">—</span>@endif
    @if($puedeGestionar && ($modoAcciones ?? 'completo') !== 'menu')
        <x-ui.action-button variant="secondary" size="sm" class="rm-staff-directory__record-button" wire:click="abrirFichaPersonal('{{ $usuario->getKey() }}')" icono="ph-identification-card">Ver ficha</x-ui.action-button>
    @endif
    @if(($modoAcciones ?? 'completo') !== 'ficha' && ($puedeGestionar || auth()->user()->can('asignaciones_personal.ver')))
    <x-dropdown align="right" width="60" dropdownClasses="rm-staff-directory__menu" contentClasses="rm-staff-directory__menu-content">
        <x-slot name="trigger">
            <x-ui.action-button variant="ghost" icono="ph-dots-three" x-bind:aria-expanded="open" aria-label="Acciones de {{ $usuario->name }}">Acciones</x-ui.action-button>
        </x-slot>
        <x-slot name="content">
        @if($puedeGestionar)
            <x-ui.action-button variant="ghost" wire:click="abrirModalEdicion('{{ $usuario->getKey() }}')" icono="ph-pencil-simple">Editar ficha</x-ui.action-button>
        @endif
        @can('asignaciones_personal.ver')
            <x-ui.action-button variant="ghost" wire:click="abrirHorariosPersonal('{{ $usuario->getKey() }}')" icono="ph-calendar-blank">Consultar horarios</x-ui.action-button>
        @endcan
        @if($puedeGestionar)
        @if($ampliacionDisponible)
            <x-ui.action-button variant="ghost" wire:click="abrirEstadoLaboral('{{ $usuario->getKey() }}')" icono="ph-user-switch">Cambiar situación laboral</x-ui.action-button>
        @endif
        <x-ui.action-button variant="ghost" wire:click="toggleEstado('{{ $usuario->getKey() }}', '{{ $usuario->estado }}')" icono="ph-lock-key"
            wire:confirm="{{ $usuario->estado === 'ACTIVO' ? '¿Suspender el acceso de esta cuenta? Su ficha laboral y sus asignaciones se conservarán.' : '¿Activar el acceso de esta cuenta? Esto no modifica su estado laboral ni acredita competencia profesional.' }}" wire:loading.attr="disabled">
            {{ $usuario->estado === 'ACTIVO' ? 'Suspender acceso' : 'Activar acceso' }}
        </x-ui.action-button>
        @if($usuario->estado === 'ACTIVO')
            <x-ui.action-button variant="ghost" wire:click="enviarRecuperacion('{{ $usuario->getKey() }}')" icono="ph-envelope-simple"
                wire:confirm="¿Solicitar un enlace de recuperación al correo registrado de {{ $usuario->name }}? La contraseña la elegirá el titular."
                wire:loading.attr="disabled" wire:target="enviarRecuperacion">
                <span wire:loading.remove wire:target="enviarRecuperacion">Enviar enlace de acceso</span>
                <span wire:loading wire:target="enviarRecuperacion">Solicitando enlace…</span>
            </x-ui.action-button>
        @endif
    @endif
        </x-slot>
    </x-dropdown>
    @endif
</div>
