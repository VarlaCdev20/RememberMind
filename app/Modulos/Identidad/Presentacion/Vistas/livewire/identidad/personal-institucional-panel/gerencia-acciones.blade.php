@php $puedeGestionar = !$usuario->hasAnyRole(['GERENTE','SUPERADMINISTRADOR']) && auth()->user()->can('personal.gestionar'); @endphp
<div class="rm-staff-directory__actions">
    @if(!$puedeGestionar && ($modoAcciones ?? 'completo') === 'ficha')<span aria-label="Ficha no disponible para esta cuenta">—</span>@endif
    @if($puedeGestionar && ($modoAcciones ?? 'completo') !== 'menu')
        <x-ui.action-button variant="secondary" size="sm" class="rm-staff-directory__record-button" wire:click="abrirFichaPersonal('{{ $usuario->getKey() }}')" icono="ph-identification-card">Ver ficha</x-ui.action-button>
    @endif
    @if(($modoAcciones ?? 'completo') !== 'ficha' && ($puedeGestionar || auth()->user()->can('asignaciones_personal.ver')))
    <x-dropdown align="right" width="60" dropdownClasses="rm-staff-directory__menu" contentClasses="rm-staff-directory__menu-content">
        <x-slot name="trigger">
            <x-ui.action-button variant="ghost" icono="ph-dots-three" class="rm-staff-directory__menu-trigger" x-bind:aria-expanded="open" aria-label="Acciones de {{ $usuario->name }}"><span class="rm-staff-directory__menu-label">Acciones</span></x-ui.action-button>
        </x-slot>
        <x-slot name="content">
        @if($puedeGestionar)
            <x-ui.action-button variant="ghost" wire:click="abrirModalEdicion('{{ $usuario->getKey() }}')" icono="ph-pencil-simple">Editar registro</x-ui.action-button>
        @endif
        @can('asignaciones_personal.ver')
            <x-ui.action-button variant="ghost" wire:click="abrirHorariosPersonal('{{ $usuario->getKey() }}')" icono="ph-calendar-blank">Consultar horarios</x-ui.action-button>
        @endcan
        @if($puedeGestionar)
        @if($ampliacionDisponible)
            <x-ui.action-button variant="ghost" wire:click="abrirEstadoLaboral('{{ $usuario->getKey() }}')" icono="ph-user-switch">Cambiar situación laboral</x-ui.action-button>
        @endif
        <x-ui.action-button variant="ghost" icono="ph-lock-key" loading="toggleEstado"
            :data-confirmation="\Illuminate\Support\Js::encode(['title'=>$usuario->estado === 'ACTIVO' ? '¿Suspender el acceso?' : '¿Activar el acceso?', 'subject'=>$usuario->name, 'detail'=>$usuario->correo, 'text'=>$usuario->estado === 'ACTIVO' ? 'Esta cuenta no podrá iniciar sesión. Su ficha laboral y sus asignaciones se conservarán.' : 'Se habilitará el inicio de sesión. El estado laboral y la competencia profesional conservan su propio proceso.', 'confirmText'=>$usuario->estado === 'ACTIVO' ? 'Suspender acceso' : 'Activar acceso', 'tone'=>$usuario->estado === 'ACTIVO' ? 'danger' : 'neutral'])"
            :data-usuario="$usuario->getKey()" :data-estado="$usuario->estado"
            @click="if (await rmConfirmar(JSON.parse($el.dataset.confirmation), $el)) $wire.toggleEstado($el.dataset.usuario, $el.dataset.estado)">
            {{ $usuario->estado === 'ACTIVO' ? 'Suspender acceso' : 'Activar acceso' }}
        </x-ui.action-button>
        @if($usuario->estado === 'ACTIVO')
            <x-ui.action-button variant="ghost" icono="ph-envelope-simple" loading="enviarRecuperacion"
                :data-confirmation="\Illuminate\Support\Js::encode(['title'=>'¿Solicitar un enlace de acceso?', 'subject'=>$usuario->name, 'detail'=>$usuario->correo, 'text'=>'Se solicitará un enlace de recuperación al correo registrado. La contraseña la elegirá el titular.', 'confirmText'=>'Solicitar enlace'])" :data-usuario="$usuario->getKey()"
                @click="if (await rmConfirmar(JSON.parse($el.dataset.confirmation), $el)) $wire.enviarRecuperacion($el.dataset.usuario)">
                <span wire:loading.remove wire:target="enviarRecuperacion">Enviar enlace de acceso</span>
                <span wire:loading wire:target="enviarRecuperacion">Solicitando enlace…</span>
            </x-ui.action-button>
        @endif
    @endif
        </x-slot>
    </x-dropdown>
    @endif
</div>
