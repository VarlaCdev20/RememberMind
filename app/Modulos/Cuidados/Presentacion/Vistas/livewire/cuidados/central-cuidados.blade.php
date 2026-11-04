<div class="rm-page-layout rm-care-center space-y-5">
    <x-ui.collection-header title="Cuidados" subtitle="Consulta los cuidados guardados y continúa la atención de tus residentes." icon="ph-heartbeat" eyebrow="Enfermería">
        <x-slot:actions>
            @if($opcionesRegistro !== [])
                <button type="button" class="rm-btn-primary" wire:click="abrirRegistro" wire:loading.attr="disabled" wire:target="abrirRegistro"><i class="ph-bold ph-plus" aria-hidden="true"></i> Registrar</button>
            @endif
        </x-slot:actions>
    </x-ui.collection-header>

    <nav class="flex flex-wrap gap-2" aria-label="Vistas de cuidados">
        @foreach(['resumen' => 'Resumen general', 'registros' => 'Registros'] as $clave => $label)
            <button type="button" class="{{ $vista === $clave ? 'rm-btn-primary' : 'rm-btn-secondary' }}" wire:click="$set('vista', '{{ $clave }}')" aria-pressed="{{ $vista === $clave ? 'true' : 'false' }}" wire:loading.attr="disabled">{{ $label }}</button>
        @endforeach
    </nav>

    <x-ui.filter-bar>
        <div class="grid w-full gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div><label for="central-tipo" class="rm-label">Tipo de cuidado</label><select id="central-tipo" class="rm-select w-full" wire:model.live="tipo"><option value="">Todos los cuidados autorizados</option>@foreach($tipos as $clave => $label)<option value="{{ $clave }}">{{ $label }}</option>@endforeach</select></div>
            <div><label for="central-residente" class="rm-label">Residente</label><select id="central-residente" class="rm-select w-full" wire:model.live="residente"><option value="">Todos los residentes autorizados</option>@foreach($residentes as $persona)<option value="{{ $persona->cod_residente }}">{{ $persona->nombres }} {{ $persona->apellido_paterno }} {{ $persona->apellido_materno }}</option>@endforeach</select></div>
            <div><label for="central-desde" class="rm-label">Desde</label><input id="central-desde" type="date" class="rm-input w-full" wire:model.live="desde" max="{{ today()->toDateString() }}" aria-invalid="{{ $errors->has('desde') ? 'true' : 'false' }}" @error('desde') aria-describedby="central-desde-error" @enderror>@error('desde')<p id="central-desde-error" role="alert" class="text-sm text-[var(--rm-danger)]">{{ $message }}</p>@enderror</div>
            <div><label for="central-hasta" class="rm-label">Hasta</label><input id="central-hasta" type="date" class="rm-input w-full" wire:model.live="hasta" max="{{ today()->toDateString() }}" aria-invalid="{{ $errors->has('hasta') ? 'true' : 'false' }}" @error('hasta') aria-describedby="central-hasta-error" @enderror>@error('hasta')<p id="central-hasta-error" role="alert" class="text-sm text-[var(--rm-danger)]">{{ $message }}</p>@enderror</div>
        </div>
        <button type="button" class="rm-btn-secondary" wire:click="limpiarFiltros">Restablecer filtros</button>
    </x-ui.filter-bar>
    <p wire:loading role="status" class="text-sm text-[var(--rm-text-secondary)]">Actualizando cuidados…</p>

    @if($datos === null)
        <x-ui.empty-state icon="ph-calendar-blank" title="Revisa los filtros" description="Corrige el período indicado para consultar los registros." />
    @elseif($residentes->isEmpty())
        <x-ui.empty-state icon="ph-users" title="Sin residentes disponibles para consulta" description="No tienes residentes dentro de tu alcance actual. Revisa la asignación de la jornada con Administración." />
    @elseif($vista === 'resumen')
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
            <x-ui.metric-card :value="$datos['total']" label="Registros guardados" icon="ph-clipboard-text" />
            <x-ui.metric-card :value="$datos['residentes_con_registros']" label="Residentes con registros" icon="ph-users" />
            <x-ui.metric-card :value="count($datos['resumen'])" label="Tipos con registros" icon="ph-squares-four" />
        </div>
        <section aria-labelledby="central-por-cuidado" class="space-y-3">
            <h2 id="central-por-cuidado" class="rm-section-title">Resumen por cuidado</h2>
            <p class="text-sm text-[var(--rm-text-secondary)]">Conteos del período seleccionado. Incluyen la historia guardada; cada registro conserva su estado. Heridas identificadas y curaciones se muestran como registros distintos.</p>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($tipos as $clave => $label)
                    @if($tipo === '' || $tipo === $clave)
                        <article class="rm-card p-4 space-y-3" wire:key="resumen-{{ $clave }}">
                            <div class="flex items-start justify-between gap-3"><h3 class="font-semibold text-[var(--rm-text-primary)]">{{ $label }}</h3><strong class="text-xl tabular-nums">{{ $datos['resumen'][$clave]['total'] ?? 0 }}</strong></div>
                            <p class="text-sm text-[var(--rm-text-secondary)]">{{ isset($datos['resumen'][$clave]) ? 'Último registro: '.$datos['resumen'][$clave]['ultima_fecha'] : 'Sin registros en este período' }}</p>
                            @if(($datos['resumen'][$clave]['anulados'] ?? 0) > 0)<p class="text-sm">{{ $datos['resumen'][$clave]['anulados'] }} anulados incluidos en la historia</p>@endif
                            <button type="button" class="rm-btn-secondary" wire:click="verRegistros('{{ $clave }}')" aria-label="Ver registros de {{ $label }}">Ver registros <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></button>
                        </article>
                    @endif
                @endforeach
            </div>
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
            <x-ui.paginacion :paginator="$datos['registros']" mode="livewire" :showPerPage="false" label="registros de cuidado" />
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
