<div class="rm-management-workspace rm-staff-directory space-y-4">
    <x-ui.collection-header title="Personal" subtitle="Equipo institucional, documentación y acceso" icon="ph-identification-badge" class="rm-staff-directory__heading">
        <x-slot:actions>
            <a class="rm-btn rm-btn-secondary" href="{{ route('admin.personal.documentacion') }}"><i class="ph-bold ph-files" aria-hidden="true"></i> Documentación laboral</a>
            @can('personal.gestionar')<x-ui.action-button wire:click="abrirModalNuevo" icono="ph-user-plus" loading="abrirModalNuevo">Registrar trabajador</x-ui.action-button>@endcan
        </x-slot:actions>
    </x-ui.collection-header>
    @error('recuperacion')<p role="alert" class="rm-alert rm-alert-danger">{{ $message }}</p>@enderror
    <x-ui.filter-bar class="rm-staff-directory__filters" aria-label="Filtrar personal institucional">
        <x-ui.field label="Buscar trabajador" for="gerencia-personal-buscar"><x-ui.filter-search model="busquedaResumen" input-id="gerencia-personal-buscar" label="" placeholder="Nombre o correo" :show-clear="false" /></x-ui.field>
        <x-ui.field label="Estado de acceso" for="gerencia-personal-estado"><select id="gerencia-personal-estado" class="rm-input" wire:model.live="estadoResumen"><option value="">Todos los accesos</option><option value="activo">Activo</option><option value="inactivo">Inactivo</option><option value="suspendido">Suspendido</option></select></x-ui.field>
        <x-ui.action-button variant="ghost" wire:click="limpiarFiltrosResumen" icono="ph-arrow-counter-clockwise">Limpiar</x-ui.action-button>
        <x-ui.collection-view-switch mode="livewire" change-method="cambiarVistaListadoResumen" :value="$vistaListadoResumen" label="Presentación del personal"
            :options="[['value'=>'tabla','label'=>'Tabla','icon'=>'ph-table'],['value'=>'tarjetas','label'=>'Tarjetas','icon'=>'ph-squares-four']]" />
    </x-ui.filter-bar>
    <section class="rm-staff-directory__results" aria-label="Equipo institucional">
        <div wire:loading.delay wire:target="busquedaResumen,estadoResumen,cambiarVistaListadoResumen,limpiarFiltrosResumen" class="rm-help" role="status">Actualizando personal…</div>
        @if($personalResumen->isEmpty())
            <x-ui.empty-state titulo="Sin coincidencias" texto="Prueba otra búsqueda o limpia los filtros para ver el equipo." icono="ph-users" />
        @elseif($vistaListadoResumen === 'tarjetas')
            <div class="rm-staff-directory__cards" role="list">
                @foreach($personalResumen as $usuario)
                    @php
                        $nombre = mb_convert_case(mb_strtolower($usuario->name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                        $funcion = $usuario->personal?->profesion;
                    @endphp
                    <x-ui.resident-card entity="personal" :name="$nombre" :code="$usuario->getKey()" :secondary="$usuario->correo" :show-location="false" variant="inherit" class="rm-staff-directory__card">
                        <x-slot:status>
                        <p class="rm-staff-directory__profession">{{ $funcion ? \Illuminate\Support\Str::headline(mb_strtolower($funcion, 'UTF-8')) : 'Sin ficha laboral' }}</p>
                        <dl class="rm-staff-directory__states">
                            <div><dt>Estado laboral</dt><dd><x-ui.status-badge :estado="$usuario->personal?->estado ?? ''" :label="$usuario->personal ? null : 'Sin registrar'" /></dd></div>
                            <div><dt>Acceso</dt><dd><x-ui.status-badge :estado="$usuario->estado" /></dd></div>
                        </dl>
                        </x-slot:status>
                        <x-slot:actions>@include('identidad::livewire.identidad.personal-institucional-panel.gerencia-acciones')</x-slot:actions>
                    </x-ui.resident-card>
                @endforeach
            </div>
        @else
            <x-ui.table-surface class="rm-staff-directory__table-surface">
                <table class="rm-table rm-staff-directory__table" role="table">
                    <caption class="sr-only">Personal institucional y acceso</caption>
                    <thead><tr><th scope="col">Trabajador</th><th scope="col">Función</th><th scope="col">Estado laboral</th><th scope="col">Acceso</th><th scope="col">Ficha</th><th scope="col">Acciones</th></tr></thead>
                    <tbody>
                        @foreach($personalResumen as $usuario)
                            @php
                                $nombre = mb_convert_case(mb_strtolower($usuario->name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                                $funcion = $usuario->personal?->profesion;
                            @endphp
                            <tr wire:key="personal-row-{{ $usuario->getKey() }}">
                                <td data-label="Trabajador"><x-ui.resident-card-identity :name="$nombre" :code="$usuario->getKey()" :secondary="$usuario->correo" mode="row" /></td>
                                <td data-label="Función"><span class="rm-staff-directory__role"><i class="ph ph-identification-badge" aria-hidden="true"></i>{{ $funcion ? \Illuminate\Support\Str::headline(mb_strtolower($funcion, 'UTF-8')) : 'Sin ficha laboral' }}</span></td>
                                <td data-label="Estado laboral"><x-ui.status-badge :estado="$usuario->personal?->estado ?? ''" :label="$usuario->personal ? null : 'Sin registrar'" /></td>
                                <td data-label="Acceso"><x-ui.status-badge :estado="$usuario->estado" /></td>
                                <td data-label="Ficha">@include('identidad::livewire.identidad.personal-institucional-panel.gerencia-acciones', ['modoAcciones' => 'ficha'])</td>
                                <td data-label="Acciones">@include('identidad::livewire.identidad.personal-institucional-panel.gerencia-acciones', ['modoAcciones' => 'menu'])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <x-slot:footer><x-ui.paginacion class="rm-pagination--compact rm-staff-directory__pagination" :paginator="$personalResumen" mode="livewire" label="trabajadores" :show-per-page="false" :show-jump="false" /></x-slot:footer>
            </x-ui.table-surface>
        @endif
        @if($personalResumen->isNotEmpty() && $vistaListadoResumen === 'tarjetas')<x-ui.paginacion class="rm-pagination--compact rm-staff-directory__pagination" :paginator="$personalResumen" mode="livewire" label="trabajadores" :show-per-page="false" :show-jump="false" />@endif
    </section>
    @if($fichaPersonal)
        @include('identidad::livewire.identidad.personal-institucional-panel.modales.ficha-resumen')
    @endif
    @include('livewire.identidad.personal-institucional-panel.modales.gestion')
    @if($modalLaboral)
    <x-ui.modal-livewire id="gerencia-estado-laboral" wire:model="modalLaboral" title="Cambiar situación laboral" icon="ph-user-switch" max-width="2xl" class="rm-staff-action-dialog" surface="reading" close-method="cerrarEstadoLaboral" :dismiss-on-escape="false" :show-validation="false">
        <div class="rm-staff-action__context">
            <i class="ph-bold ph-identification-badge" aria-hidden="true"></i>
            <div><p class="rm-help">Trabajador</p><strong>{{ $nombreLaboral }}</strong></div>
            <x-ui.status-badge :estado="$estadoLaboralEsperado" />
        </div>
        @error('estado_esperado')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
        <form id="gerencia-laboral-form" @submit.prevent="if (await rmConfirmar(@js(['title'=>'¿Guardar el cambio laboral?', 'subject'=>$nombreLaboral, 'text'=>'Se registrará la nueva situación laboral. El acceso a la cuenta y las asignaciones existentes se conservarán.', 'confirmText'=>'Guardar cambio']), $el)) $wire.guardarEstadoLaboral()" class="rm-staff-action__form" novalidate>
            <div class="rm-staff-action__grid">
                <x-ui.field label="Nuevo estado laboral" for="laboral-estado" error="estado_nuevo" required>
                    <x-ui.selector id="laboral-estado" label="Nuevo estado laboral" wire:model="nuevoEstadoLaboral" teleport="#gerencia-estado-laboral" aria-invalid="{{ $errors->has('estado_nuevo') ? 'true' : 'false' }}" :aria-describedby="$errors->has('estado_nuevo') ? 'laboral-estado-error' : null"><option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option></x-ui.selector>
                </x-ui.field>
                <x-ui.field label="Fecha y hora de efecto" for="laboral-fecha" error="fecha_hora_efecto" required>
                    <input id="laboral-fecha" class="rm-input" type="datetime-local" wire:model="fechaEfectoLaboral" max="{{ now()->format('Y-m-d\TH:i') }}" aria-invalid="{{ $errors->has('fecha_hora_efecto') ? 'true' : 'false' }}" @if($errors->has('fecha_hora_efecto')) aria-describedby="laboral-fecha-error" @endif required>
                </x-ui.field>
            </div>
            <x-ui.field label="Motivo del cambio" for="laboral-motivo" error="motivo" help="De 5 a 4000 caracteres." required>
                <textarea id="laboral-motivo" class="rm-input" wire:model="motivoLaboral" placeholder="Describe el motivo de la nueva situación laboral" rows="3" minlength="5" maxlength="4000" aria-invalid="{{ $errors->has('motivo') ? 'true' : 'false' }}" aria-describedby="laboral-motivo-help{{ $errors->has('motivo') ? ' laboral-motivo-error' : '' }}" required></textarea>
            </x-ui.field>
            <p class="rm-staff-action__note"><i class="ph-bold ph-info" aria-hidden="true"></i><span>El acceso a la cuenta se conserva. Administración revisará las asignaciones afectadas; los turnos y actuaciones anteriores permanecen en el historial.</span></p>
        </form>
        <x-slot:footer>
            <x-ui.action-button variant="secondary" :data-confirmation="\Illuminate\Support\Js::encode(['title'=>'¿Descartar el cambio laboral?', 'subject'=>$nombreLaboral, 'text'=>'Los datos introducidos en este formulario no se guardarán.', 'confirmText'=>'Descartar cambio', 'tone'=>'danger'])" @click="if (await rmConfirmar(JSON.parse($el.dataset.confirmation), $el)) $wire.cerrarEstadoLaboral()">Cancelar</x-ui.action-button>
            <x-ui.action-button tipo="submit" form="gerencia-laboral-form" loading="guardarEstadoLaboral" icono="ph-check">Guardar cambio</x-ui.action-button>
        </x-slot:footer>
    </x-ui.modal-livewire>
    @endif
</div>
