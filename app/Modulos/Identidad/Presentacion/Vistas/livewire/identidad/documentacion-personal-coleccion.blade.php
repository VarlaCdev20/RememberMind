<section class="rm-card rm-staff-documents__library rm-document-library" x-data="{ modoArchivos: 'carpetas' }" aria-labelledby="archivos-personal-titulo">
    <header class="rm-staff-documents__heading">
        <div><h2 id="archivos-personal-titulo">Archivos del expediente</h2></div>
        <div class="rm-document-library__tools"><span class="rm-badge rm-badge-info" aria-live="polite">{{ $archivos->total() }} {{ $archivos->total() === 1 ? 'archivo' : 'archivos' }}</span><x-ui.collection-view-switch model="modoArchivos" label="Presentación de documentos" :options="[
    ['value'=>'tabla','label'=>'Tabla','icon'=>'ph-table'], ['value'=>'carpetas','label'=>'Carpetas','icon'=>'ph-folders']
]" /></div>
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
        <x-ui.empty-state :title="$hayFiltrosArchivo ? 'No hay archivos con estos filtros' : 'Todavía no hay archivos disponibles'" :description="$hayFiltrosArchivo ? 'Prueba con otra selección o limpia los filtros.' : 'Aquí aparecerán los documentos del personal que puedes consultar.'" icon="ph-folder-open" />
    @else
    <div class="rm-staff-directory rm-document-library__table" x-show="modoArchivos === 'tabla'" x-cloak>
        <div class="rm-staff-directory__table-surface"><table class="rm-staff-directory__table rm-data-table rm-table"><caption class="sr-only">Documentos del personal autorizados para esta cuenta</caption><thead><tr><th scope="col">Documento</th><th scope="col">Trabajador</th><th scope="col">Estado</th><th scope="col">Fecha de carga</th><th scope="col">Acciones</th></tr></thead><tbody>
        @foreach($archivos as $documento)
        @if($permisosArchivos[$documento->getKey()]['ver'] ?? false)
        @php
    $titular = $documento->usuario->personal;
    $imagenDocumento = in_array(mb_strtolower($documento->extension), ['jpg','jpeg','png','webp'], true);
    $estadoVisible = ['CARGADO'=>'Cargado','PENDIENTE'=>'Pendiente de revisión','VALIDADO'=>'Validado','OBSERVADO'=>'Observado','VENCIDO'=>'Vencido registrado','ANULADO'=>'Anulado','ANULADA'=>'Anulada','REEMPLAZADO'=>'Reemplazado'][$documento->estado] ?? 'Estado por revisar';
    $tonoDocumento = match ($documento->estado) { 'VALIDADO'=>'success', 'OBSERVADO','VENCIDO'=>'warning', 'ANULADO','ANULADA','REEMPLAZADO'=>'neutral', default=>'info' };
    [$colorCarpeta,$claseDocumento] = match ($documento->tipo_documento) {
        'CI','FOTO_DOC'=>['sky','IDENTIDAD'], 'CV','EXP_LABORAL'=>['sage','TRAYECTORIA'],
        'TITULO','MATRICULA','CERT_ESP','CAPACITACION'=>['lavender','FORMACIÓN'],
        'DOMICILIO'=>['rose','DOMICILIO'], 'ANTECEDENTES'=>['sand','ANTECEDENTES'], default=>['sage','DOCUMENTO'] };
@endphp
        <tr wire:key="documento-tabla-{{ $documento->getKey() }}" data-documento-fila="{{ $documento->getKey() }}">
            <td data-label="Documento"><div class="rm-document-library__file">@include('identidad::livewire.identidad.documentacion-personal-miniatura')<div><strong>{{ $documento->nombre ?: 'Documento sin nombre' }}</strong><small>{{ $claseDocumento }} · {{ mb_strtoupper($documento->extension) ?: 'ARCHIVO' }}</small></div></div></td>
            <td data-label="Trabajador">{{ trim($titular->nombres.' '.$titular->apellido_paterno.' '.$titular->apellido_materno) }}</td>
            <td data-label="Estado"><span class="rm-badge rm-badge-{{ $tonoDocumento }}">{{ $estadoVisible }}</span></td>
            <td data-label="Carga"><time>{{ $documento->fecha_hora_carga?->format('d/m/Y H:i') ?? 'Sin fecha conservada' }}</time></td>
            <td data-label="Acciones">@include('identidad::livewire.identidad.documentacion-personal-acciones')</td>
        </tr>
        @endif
        @endforeach
        </tbody></table></div>
    </div>
    <ul class="rm-document-folders" x-show="modoArchivos === 'carpetas'" aria-label="Carpetas de documentos autorizados">
        @foreach($archivos as $documento)
        @if($permisosArchivos[$documento->getKey()]['ver'] ?? false)
        @php
    $titular = $documento->usuario->personal;
    $imagenDocumento = in_array(mb_strtolower($documento->extension), ['jpg','jpeg','png','webp'], true);
    $estadoVisible = ['CARGADO'=>'Cargado','PENDIENTE'=>'Pendiente de revisión','VALIDADO'=>'Validado','OBSERVADO'=>'Observado','VENCIDO'=>'Vencido registrado','ANULADO'=>'Anulado','ANULADA'=>'Anulada','REEMPLAZADO'=>'Reemplazado'][$documento->estado] ?? 'Estado por revisar';
    $tonoDocumento = match ($documento->estado) { 'VALIDADO'=>'success', 'OBSERVADO','VENCIDO'=>'warning', 'ANULADO','ANULADA','REEMPLAZADO'=>'neutral', default=>'info' };
    [$colorCarpeta,$claseDocumento] = match ($documento->tipo_documento) {
        'CI','FOTO_DOC'=>['sky','IDENTIDAD'], 'CV','EXP_LABORAL'=>['sage','TRAYECTORIA'],
        'TITULO','MATRICULA','CERT_ESP','CAPACITACION'=>['lavender','FORMACIÓN'],
        'DOMICILIO'=>['rose','DOMICILIO'], 'ANTECEDENTES'=>['sand','ANTECEDENTES'], default=>['sage','DOCUMENTO'] };
@endphp
        <li class="rm-document-folder" data-folder-tone="{{ $colorCarpeta }}" wire:key="documento-carpeta-{{ $documento->getKey() }}" data-documento-carpeta="{{ $documento->getKey() }}">
            <span class="rm-document-folder__tab"><i class="ph-bold ph-folder" aria-hidden="true"></i>{{ $claseDocumento }}</span>
            <div class="rm-document-folder__body">
                <div class="rm-document-folder__cover">@include('identidad::livewire.identidad.documentacion-personal-miniatura')<div class="rm-document-folder__identity"><span class="rm-document-folder__format">{{ mb_strtoupper($documento->extension) ?: 'ARCHIVO' }}</span><h3>{{ $documento->nombre ?: 'Documento sin nombre' }}</h3><p><i class="ph-bold ph-user" aria-hidden="true"></i>{{ trim($titular->nombres.' '.$titular->apellido_paterno.' '.$titular->apellido_materno) }}</p></div></div>
                <span class="rm-badge rm-badge-{{ $tonoDocumento }}">{{ $estadoVisible }}</span>
                <dl class="rm-document-folder__dates"><div><dt>Carga</dt><dd>{{ $documento->fecha_hora_carga?->format('d/m/Y H:i') ?? 'Sin fecha conservada' }}</dd></div><div><dt>Emisión</dt><dd>{{ $documento->fecha_emision?->format('d/m/Y') ?? 'No consta' }}</dd></div>@if($documento->fecha_vencimiento)<div><dt>Vencimiento</dt><dd>{{ $documento->fecha_vencimiento->format('d/m/Y') }}</dd></div>@endif</dl>
                @include('identidad::livewire.identidad.documentacion-personal-acciones')
            </div>
        </li>
        @endif
        @endforeach
    </ul>
    @endif
    <div class="rm-document-library__footer"><x-ui.paginacion :paginator="$archivos" mode="livewire" label="archivos" :show-per-page="false" :show-jump="true" /><p class="rm-help">Los colores distinguen tipos de documento. La carga no equivale a su validación; los anulados o reemplazados conservan su historia.</p></div>
</section>
