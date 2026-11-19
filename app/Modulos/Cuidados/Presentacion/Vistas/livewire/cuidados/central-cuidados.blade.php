<div class="rm-page-layout rm-care-center">
    <x-ui.collection-header title="Cuidados" :subtitle="$opcionesRegistro === [] ? 'Consulta los cuidados por residente' : 'Consulta y registra cuidados por residente'" icon="ph-heartbeat" eyebrow="Enfermería">
        <x-slot:actions>
            <x-ui.nursing-report-link :area="$tipo" :residente="$residente" :desde="$desde" :hasta="$hasta" />
            @if($opcionesRegistro !== [])
                <x-ui.action-button icono="ph-plus" aria-label="Registrar cuidado" wire:click="abrirRegistro" loading="abrirRegistro">Registrar</x-ui.action-button>
            @endif
        </x-slot:actions>
    </x-ui.collection-header>

    <nav class="flex flex-wrap gap-2" aria-label="Vistas de cuidados">
        @foreach(['resumen' => 'Resumen general', 'registros' => 'Registros'] as $clave => $label)
            <button type="button" class="{{ $vista === $clave ? 'rm-btn-primary' : 'rm-btn-secondary' }}" wire:click="$set('vista', '{{ $clave }}')" aria-pressed="{{ $vista === $clave ? 'true' : 'false' }}" wire:loading.attr="disabled">{{ $label }}</button>
        @endforeach
    </nav>

    @php
        $periodoCuidado = $desde !== today()->subDays(6)->toDateString() || $hasta !== today()->toDateString();
        $errorPeriodo = $errors->has('desde') || $errors->has('hasta');
    @endphp
    <x-ui.filter-bar class="rm-nursing-filters" aria-label="Filtros de cuidados" :x-data="'{ masFiltros: '.($errorPeriodo ? 'true' : 'false').' }'">
        <div class="rm-nursing-filters__row">
            <x-ui.field label="Tipo de cuidado" for="central-tipo">
                <select id="central-tipo" class="rm-select" wire:model.live="tipo">
                    <option value="">Todos los cuidados</option>
                    @foreach($tipos as $clave => $label)<option value="{{ $clave }}">{{ $label }}</option>@endforeach
                </select>
            </x-ui.field>
            <x-ui.field label="Residente" for="central-residente">
                <select id="central-residente" class="rm-select" wire:model.live="residente">
                    <option value="">Todos</option>
                    @foreach($residentes as $persona)<option value="{{ $persona->cod_residente }}">{{ $persona->nombre_completo }}</option>@endforeach
                </select>
            </x-ui.field>
            <div class="rm-nursing-filters__actions">
                <x-ui.action-button variant="secondary" icono="ph-calendar-blank" @click="masFiltros = !masFiltros"
                    aria-controls="central-periodo" x-bind:aria-expanded="masFiltros">
                    {{ $periodoCuidado ? 'Período personalizado' : 'Últimos 7 días' }}
                </x-ui.action-button>
                @if($tipo || $residente || $periodoCuidado)
                    <x-ui.action-button variant="ghost" icono="ph-arrow-counter-clockwise" wire:click="limpiarFiltros" loading="limpiarFiltros"
                        data-rm-action-icon aria-label="Limpiar filtros" />
                @endif
            </div>
        </div>
        <div id="central-periodo" class="rm-nursing-filters__extra" x-show="masFiltros" x-cloak>
            @foreach(['desde' => 'Desde', 'hasta' => 'Hasta'] as $campo => $label)
                <x-ui.field :label="$label" :for="'central-'.$campo" :error="$campo">
                    <input id="central-{{ $campo }}" class="rm-input" type="date" wire:model.live="{{ $campo }}" max="{{ today()->toDateString() }}"
                        aria-invalid="{{ $errors->has($campo) ? 'true' : 'false' }}" @error($campo) aria-describedby="central-{{ $campo }}-error" @enderror>
                </x-ui.field>
            @endforeach
        </div>
        @if($errorPeriodo)<p class="rm-field-error" x-show="!masFiltros" role="alert">Revisa las fechas del período.</p>@endif
    </x-ui.filter-bar>
    <p wire:loading role="status" class="text-sm text-[var(--rm-text-secondary)]">Actualizando cuidados…</p>

    @if($datos === null)
        <x-ui.empty-state icon="ph-calendar-blank" title="Revisa los filtros" description="Corrige el período indicado para consultar los registros." />
    @elseif($residentes->isEmpty())
        <x-ui.empty-state icon="ph-users" title="Sin residentes disponibles para consulta" description="No tienes residentes dentro de tu alcance actual. Revisa la asignación de la jornada con Administración." />
    @elseif($vista === 'resumen')
        <x-ui.card class="rm-care-center__summary">
            <dl class="rm-care-center__totals">
                <div><dt>Registros guardados</dt><dd>{{ $datos['total'] }}</dd></div>
                <div><dt>Residentes con registros</dt><dd>{{ $datos['residentes_con_registros'] }}</dd></div>
                <div><dt>Tipos con registros</dt><dd>{{ count($datos['resumen']) }}</dd></div>
            </dl>
        </x-ui.card>
        <section aria-labelledby="central-por-cuidado" class="space-y-2">
            <h2 id="central-por-cuidado" class="rm-section-title">Resumen por cuidado</h2>
            <x-ui.table-surface>
                <ul class="rm-care-center__overview">
                    @foreach($tipos as $clave => $label)
                        @if($tipo === '' || $tipo === $clave)
                            <li wire:key="resumen-{{ $clave }}">
                                <div>
                                    <strong>{{ $label }}</strong>
                                    @if(isset($datos['resumen'][$clave]))
                                        <p class="rm-help">Último registro · {{ $datos['resumen'][$clave]['ultima_fecha'] }}</p>
                                    @else
                                        <p class="rm-help">Sin registros en este período</p>
                                    @endif
                                </div>
                                <p class="rm-care-center__count"><strong>{{ $datos['resumen'][$clave]['total'] ?? 0 }}</strong> {{ ($datos['resumen'][$clave]['total'] ?? 0) === 1 ? 'registro' : 'registros' }}
                                    @if(($datos['resumen'][$clave]['anulados'] ?? 0) > 0)
                                        <small class="block">{{ $datos['resumen'][$clave]['anulados'] }} anulados incluidos</small>
                                    @endif
                                </p>
                                <x-ui.action-button variant="secondary" icono="ph-list-bullets" wire:click="verRegistros('{{ $clave }}')"
                                    loading="verRegistros" data-rm-action-icon aria-label="Ver registros de {{ $label }}" />
                            </li>
                        @endif
                    @endforeach
                </ul>
            </x-ui.table-surface>
        </section>
    @else
        <section aria-labelledby="central-registros" class="space-y-3" wire:loading.class="opacity-60">
            <div><h2 id="central-registros" class="rm-section-title">Registros guardados</h2><p class="text-sm text-[var(--rm-text-secondary)]">Del más reciente al más antiguo. Abre el detalle para consultar los valores y su autoría.</p></div>
            @forelse($datos['registros'] as $registro)
                <article class="rm-card p-4 flex flex-col gap-3 sm:flex-row sm:items-start" wire:key="registro-{{ $registro['origen'] }}-{{ $registro['codigo'] }}">
                    <div class="min-w-0 flex-1 space-y-1">
                        <h3 class="font-semibold break-words text-[var(--rm-text-primary)]">{{ $registro['residente'] }}</h3>
                        <p class="text-sm">{{ $registro['label'] }} · {{ $registro['fecha'] }}</p>
                        <p class="text-sm text-[var(--rm-text-secondary)]">{{ $registro['profesional'] }}@if($registro['estado']) · {{ str_replace('_', ' ', $registro['estado']) }}@endif</p>
                        @if($registro['campos'] !== [])<p class="text-sm break-words">{{ $registro['campos'][0]['nombre'] }}: {{ \Illuminate\Support\Str::limit($registro['campos'][0]['valor'], 110) }}</p>@endif
                    </div>
                    <button type="button" class="rm-btn-secondary shrink-0" wire:click="abrirDetalle('{{ $registro['origen'] }}', '{{ $registro['codigo'] }}')" wire:loading.attr="disabled" aria-label="Ver detalle de {{ $registro['label'] }} de {{ $registro['residente'] }} del {{ $registro['fecha'] }}">Ver detalle</button>
                </article>
            @empty
                <x-ui.empty-state icon="ph-clipboard-text" title="Sin registros para estos filtros" description="Prueba otro período, residente o tipo de cuidado." />
            @endforelse
            <x-ui.paginacion class="rm-pagination--compact" :paginator="$datos['registros']" mode="livewire" :showPerPage="false" label="registros de cuidado" />
        </section>
    @endif

    @if($mostrarDetalle && $detalle !== [])
        <x-ui.modal-livewire wire:model="mostrarDetalle" :title="$detalle['label']" :subtitle="$detalle['residente'].' · '.$detalle['fecha']" closeMethod="cerrarDetalle" maxWidth="lg">
            <div class="space-y-4">
                <p class="text-sm text-[var(--rm-text-secondary)]">{{ $detalle['profesional'] }}@if($detalle['estado']) · {{ str_replace('_', ' ', $detalle['estado']) }}@endif</p>
                <dl class="grid gap-4 sm:grid-cols-2">@forelse($detalle['campos'] as $campo)<div class="min-w-0"><dt class="text-sm text-[var(--rm-text-secondary)]">{{ $campo['nombre'] }}</dt><dd class="font-medium whitespace-pre-wrap break-words">{{ $campo['valor'] }}</dd></div>@empty<div><dt>Contenido</dt><dd>No hay valores adicionales informados en este registro.</dd></div>@endforelse</dl>
                <p class="text-xs text-[var(--rm-text-secondary)]">Consulta de solo lectura</p>
            </div>
            <x-slot:footer><button type="button" class="rm-btn-secondary" wire:click="cerrarDetalle">Cerrar detalle</button></x-slot:footer>
        </x-ui.modal-livewire>
    @endif

    @if($mostrarRegistro)
        <x-ui.modal-livewire wire:model="mostrarRegistro" title="Registrar cuidado" subtitle="Selecciona el tipo de cuidado y al residente para continuar." closeMethod="cerrarRegistro">
            <div class="space-y-4">
                <div><label for="central-registro-tipo" class="rm-label">Tipo de cuidado</label><select id="central-registro-tipo" class="rm-select w-full" wire:model="registroTipo"><option value="">Selecciona un cuidado</option>@foreach($opcionesRegistro as $clave => $opcion)<option value="{{ $clave }}">{{ $opcion['label'] }}</option>@endforeach</select>@error('registroTipo')<p role="alert" class="text-sm text-[var(--rm-danger)]">{{ $message }}</p>@enderror</div>
                <div><label for="central-registro-residente" class="rm-label">Residente</label><select id="central-registro-residente" class="rm-select w-full" wire:model="registroResidente"><option value="">Selecciona un residente</option>@foreach($residentes as $persona)<option value="{{ $persona->cod_residente }}">{{ $persona->nombres }} {{ $persona->apellido_paterno }}</option>@endforeach</select>@error('registroResidente')<p role="alert" class="text-sm text-[var(--rm-danger)]">{{ $message }}</p>@enderror</div>
                <p class="text-sm text-[var(--rm-text-secondary)]">Cognición, conducta y sueño se registran dentro del seguimiento diario completo.</p>
            </div>
            <x-slot:footer><button type="button" class="rm-btn-secondary" wire:click="cerrarRegistro">Cancelar</button><button type="button" class="rm-btn-primary" wire:click="continuarRegistro" wire:loading.attr="disabled" wire:target="continuarRegistro">Continuar al registro</button></x-slot:footer>
        </x-ui.modal-livewire>
    @endif
</div>
