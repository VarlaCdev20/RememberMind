<section class="rm-card rm-staff-documents__library" aria-labelledby="archivos-personal-titulo">
    <header class="rm-staff-documents__heading">
        <div><h2 id="archivos-personal-titulo">Archivos del expediente</h2></div>
        <span class="rm-badge rm-badge-info" aria-live="polite">{{ $archivos->total() }} {{ $archivos->total() === 1 ? 'archivo' : 'archivos' }}</span>
    </header>
    <div class="rm-staff-documents__filters">
        <x-ui.field label="Buscar por documento o persona" for="documentacion-buscar"><div class="rm-staff-documents__search"><i class="ph ph-magnifying-glass" aria-hidden="true"></i><input id="documentacion-buscar" class="rm-input" type="search" wire:model.live.debounce.300ms="busqueda" maxlength="160" placeholder="Buscar documento o persona" autocomplete="off"></div></x-ui.field>
        <x-ui.field label="Estado del documento" for="documentacion-estado"><select id="documentacion-estado" class="rm-select" wire:model.live="estadoArchivo"><option value="">Estado: todos</option><option value="CARGADO">Cargado</option><option value="PENDIENTE">Pendiente de revisión</option><option value="VALIDADO">Validado</option><option value="OBSERVADO">Observado</option><option value="VENCIDO">Vencido registrado</option><option value="ANULADO">Anulado</option><option value="ANULADA">Anulada</option><option value="REEMPLAZADO">Reemplazado</option></select></x-ui.field>
        @if($gestor)
        <x-ui.field label="Rol del personal" for="documentacion-rol"><select id="documentacion-rol" class="rm-select" wire:model.live="rolArchivo"><option value="">Rol: todos</option>@foreach($rolesArchivo as $nombreRol)<option value="{{ $nombreRol }}">{{ $nombreRol }}</option>@endforeach</select></x-ui.field>
        <x-ui.field label="Trabajador" for="documentacion-persona"><select id="documentacion-persona" class="rm-select" wire:model.live="personaArchivo"><option value="">Personal: todos</option>@foreach($personasArchivo as $personaOpcion)<option value="{{ $personaOpcion->getKey() }}">{{ trim($personaOpcion->personal->nombres.' '.$personaOpcion->personal->apellido_paterno.' '.$personaOpcion->personal->apellido_materno) }}</option>@endforeach</select></x-ui.field>
        @endif
        <x-ui.field label="Formato del archivo" for="documentacion-formato"><select id="documentacion-formato" class="rm-select" wire:model.live="formatoArchivo"><option value="">Formato: todos</option><option value="PDF">PDF</option><option value="IMAGEN">Imágenes (JPG, PNG, WebP)</option><option value="OTROS">Otros formatos</option></select></x-ui.field>
        @if($hayFiltrosArchivo)<button type="button" class="rm-btn rm-btn-secondary" wire:click="limpiarFiltros" wire:loading.attr="disabled">Limpiar filtros</button>@endif
    </div>
    <p class="rm-staff-documents__loading" wire:loading wire:target="busqueda,estadoArchivo,rolArchivo,personaArchivo,formatoArchivo,cambiarVista,limpiarFiltros,gotoPage" role="status">Consultando archivos…</p>
    @if($archivos->isEmpty())
        <x-ui.empty-state :title="$hayFiltrosArchivo ? 'No hay archivos con estos filtros' : 'Todavía no hay archivos disponibles'" :description="$hayFiltrosArchivo ? 'Prueba con otra selección o limpia los filtros.' : 'Aquí aparecerán los documentos del personal que puedes consultar. Las solicitudes pendientes se encuentran en Solicitudes y entregas.'" icon="ph-folder-open" />
    @else
        <ul class="rm-staff-documents__list" aria-label="Archivos autorizados del personal">
            @foreach($archivos as $documento)
                @php
                    $extension = mb_strtoupper($documento->extension);
                    $titular = $documento->usuario->personal;
                    $estadoVisible = ['CARGADO' => 'Cargado', 'PENDIENTE' => 'Pendiente de revisión', 'VALIDADO' => 'Validado', 'OBSERVADO' => 'Observado', 'VENCIDO' => 'Vencido registrado', 'ANULADO' => 'Anulado', 'ANULADA' => 'Anulada', 'REEMPLAZADO' => 'Reemplazado'][$documento->estado] ?? 'Estado por revisar';
                    $tonoDocumento = match ($documento->estado) { 'VALIDADO' => 'success', 'OBSERVADO', 'VENCIDO' => 'warning', 'ANULADO', 'ANULADA', 'REEMPLAZADO' => 'neutral', default => 'info' };
                @endphp
                @if($permisosArchivos[$documento->getKey()]['ver'] ?? false)
                <li wire:key="personal-archivo-{{ $documento->getKey() }}" class="rm-staff-documents__row">
                    <span class="rm-staff-documents__file-icon" aria-hidden="true"><i class="ph {{ in_array($documento->extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? 'ph-image' : 'ph-file-text' }}"></i><small>{{ $extension ?: 'ARCHIVO' }}</small></span>
                    <div class="rm-staff-documents__identity"><h3>{{ $documento->nombre ?: 'Documento sin nombre' }}</h3><p><i class="ph ph-user" aria-hidden="true"></i>{{ trim($titular->nombres.' '.$titular->apellido_paterno.' '.$titular->apellido_materno) }}</p><span class="rm-badge rm-badge-{{ $tonoDocumento }}">{{ $estadoVisible }}</span></div>
                    <dl class="rm-staff-documents__dates"><div><dt>Carga</dt><dd>{{ $documento->fecha_hora_carga?->format('d/m/Y H:i') ?? 'Sin fecha conservada' }}</dd></div><div><dt>Emisión</dt><dd>{{ $documento->fecha_emision?->format('d/m/Y') ?? 'No consta' }}</dd></div>@if($documento->fecha_vencimiento)<div><dt>Vencimiento</dt><dd>{{ $documento->fecha_vencimiento->format('d/m/Y') }}</dd></div>@endif</dl>
                    <div class="rm-staff-documents__actions"><button type="button" class="rm-btn rm-btn-primary" wire:click="verArchivo(@js($documento->getKey()))" wire:loading.attr="disabled" wire:target="verArchivo" data-documento-trigger="{{ $documento->getKey() }}" aria-haspopup="dialog"><i class="ph ph-eye" aria-hidden="true"></i>Ver documento<span class="sr-only"> {{ $documento->nombre }} de {{ $titular->nombres }}</span></button><a class="rm-staff-documents__open" href="{{ route('admin.personal.documento.archivo', $documento) }}" target="_blank" rel="noopener"><i class="ph ph-arrow-square-out" aria-hidden="true"></i>Abrir original<span class="sr-only"> {{ $documento->nombre }} en otra pestaña</span></a></div>
                </li>
                @endif
            @endforeach
        </ul>
    @endif
    <footer class="rm-staff-documents__footer"><p>Los archivos anulados o reemplazados conservan su historia. Un archivo cargado todavía puede estar pendiente de revisión.</p>{{ $archivos->links() }}</footer>
</section>
