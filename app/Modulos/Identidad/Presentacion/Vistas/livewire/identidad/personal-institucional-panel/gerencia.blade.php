<div class="rm-management-workspace space-y-4">
    <x-ui.collection-header title="Personal" subtitle="Trabajadores, datos laborales y cuentas de acceso autorizadas" icon="ph-identification-badge"><x-slot:actions><a class="rm-btn-secondary" href="{{ route('admin.personal.documentacion') }}">Documentación laboral</a>@can('personal.gestionar')<button class="rm-btn-primary" type="button" wire:click="abrirModalNuevo" wire:loading.attr="disabled"><i class="ph-bold ph-user-plus" aria-hidden="true"></i> Registrar trabajador</button>@endcan</x-slot:actions></x-ui.collection-header>
    <x-ui.filter-bar>
        <div class="rm-management-tabs">
            <x-ui.field label="Buscar trabajador" for="gerencia-personal-buscar"><input id="gerencia-personal-buscar" class="rm-input" type="search" wire:model.live.debounce.300ms="busquedaResumen" placeholder="Nombre o correo"></x-ui.field>
            <x-ui.field label="Estado de acceso" for="gerencia-personal-estado"><select id="gerencia-personal-estado" class="rm-input" wire:model.live="estadoResumen"><option value="">Todos</option><option value="activo">Activo</option><option value="inactivo">Inactivo</option><option value="suspendido">Suspendido</option></select></x-ui.field>
            <button type="button" class="rm-btn-secondary" wire:click="limpiarFiltrosResumen">Limpiar</button>
        </div>
    </x-ui.filter-bar>
    <p class="rm-help">{{ $personalResumenTotal }} registros. Estado laboral y acceso a la cuenta se conservan por separado.</p>
    @error('recuperacion')<p role="alert" class="rm-alert rm-alert-danger">{{ $message }}</p>@enderror
    <nav class="rm-management-tabs" aria-label="Presentación del personal">
        @foreach(['tabla'=>'Tabla','tarjetas'=>'Tarjetas'] as $modo=>$etiqueta)<button type="button" class="{{ $vistaListadoResumen === $modo ? 'rm-btn-primary' : 'rm-btn-secondary' }}" wire:click="cambiarVistaListadoResumen('{{ $modo }}')" aria-pressed="{{ $vistaListadoResumen === $modo ? 'true' : 'false' }}">{{ $etiqueta }}</button>@endforeach
    </nav>
    <x-ui.section-card title="Equipo institucional" icon="ph-users-three">
        @if($personalResumen->isEmpty())<x-ui.empty-state titulo="Sin coincidencias" texto="Prueba otra búsqueda o filtro." icono="ph-users" />@else
        @if($vistaListadoResumen === 'tarjetas')
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($personalResumen as $usuario)
            <x-ui.person-entity-card entity="personal" class="rm-card p-4 space-y-3">
                <h3 class="rm-section-title">{{ mb_convert_case(mb_strtolower($usuario->name), MB_CASE_TITLE, 'UTF-8') }}</h3><p class="rm-help break-words">{{ $usuario->correo }}</p>
                <dl class="rm-management-context"><div><dt>Función</dt><dd>{{ $usuario->personal?->profesion ?: 'Sin ficha laboral' }}</dd></div><div><dt>Estado laboral</dt><dd>{{ $usuario->personal?->estado ?: 'Sin registrar' }}</dd></div><div><dt>Acceso a la cuenta</dt><dd>{{ $usuario->estado }}</dd></div></dl>
                @include('identidad::livewire.identidad.personal-institucional-panel.gerencia-acciones')
            </x-ui.person-entity-card>
        @endforeach
        </div>
        @else
        <div class="rm-table-scroll"><table class="rm-table"><caption class="sr-only">Personal institucional y acceso</caption><thead><tr><th scope="col">Trabajador</th><th scope="col">Función</th><th scope="col">Estado laboral</th><th scope="col">Acceso</th><th scope="col">Acciones</th></tr></thead><tbody>@foreach($personalResumen as $usuario)<tr><th scope="row"><strong>{{ mb_convert_case(mb_strtolower($usuario->name), MB_CASE_TITLE, 'UTF-8') }}</strong><small class="block">{{ $usuario->correo }}</small></th><td>{{ $usuario->personal?->profesion ?: 'Sin ficha laboral' }}</td><td>{{ $usuario->personal?->estado ?: 'Sin registrar' }}</td><td>{{ $usuario->estado }}</td><td>@include('identidad::livewire.identidad.personal-institucional-panel.gerencia-acciones')</td></tr>@endforeach</tbody></table></div>
        @endif
        <x-ui.paginacion :paginator="$personalResumen" mode="livewire" label="trabajadores" />
        @endif
    </x-ui.section-card>
    @include('livewire.identidad.personal-institucional-panel.modales.gestion')
    @if($modalLaboral)
    <x-ui.modal-livewire id="gerencia-estado-laboral" wire:model="modalLaboral" title="Cambiar situación laboral" :subtitle="$nombreLaboral" close-method="cerrarEstadoLaboral" :dismiss-on-escape="false">
        <form wire:submit="guardarEstadoLaboral" class="space-y-4">
            <p>Estado laboral actual: <strong>{{ $estadoLaboralEsperado }}</strong>. Esta operación conserva el acceso de la cuenta y las asignaciones existentes.</p>
            <x-ui.field label="Nuevo estado laboral" for="laboral-estado"><select id="laboral-estado" class="rm-input" wire:model="nuevoEstadoLaboral"><option value="ACTIVO">Activo</option><option value="INACTIVO">Inactivo</option></select></x-ui.field>
            <x-ui.field label="Fecha y hora de efecto" for="laboral-fecha"><input id="laboral-fecha" class="rm-input" type="datetime-local" wire:model="fechaEfectoLaboral" max="{{ now()->format('Y-m-d\TH:i') }}" required></x-ui.field>
            <x-ui.field label="Motivo" for="laboral-motivo"><textarea id="laboral-motivo" class="rm-input" wire:model="motivoLaboral" minlength="5" maxlength="4000" required></textarea></x-ui.field>
            <p class="rm-help">Administración revisará las asignaciones afectadas. No se borran turnos ni actuaciones anteriores.</p>
            <div class="rm-management-actions"><button class="rm-btn-primary" type="submit" wire:loading.attr="disabled" wire:confirm="¿Confirmar la transición laboral sin cambiar el acceso de la cuenta?">Confirmar situación laboral</button><button class="rm-btn-secondary" type="button" wire:click="cerrarEstadoLaboral" wire:confirm="¿Descartar el movimiento sin guardar?">Cancelar</button></div>
        </form>
    </x-ui.modal-livewire>
    @endif
</div>
