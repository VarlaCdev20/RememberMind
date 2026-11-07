<section class="rm-card rm-staff-documents__library rm-document-library" x-data="{ modoArchivos: 'carpetas' }"
    x-on:personal-carpeta-abierta.window="$nextTick(() => $refs.tituloExpediente?.focus())"
    x-on:personal-carpeta-cerrada.window="$nextTick(() => { const origen = Array.from($el.querySelectorAll('[data-carpeta-usuario]')).find(el => el.dataset.carpetaUsuario === $event.detail.usuario); (origen ?? $refs.tituloExpediente)?.focus(); })" aria-labelledby="archivos-personal-titulo">
    <header @class(['rm-staff-documents__heading', 'rm-document-library__heading--expediente' => $carpetaActual])>
        <div class="rm-document-library__identity">
            @if($carpetaActual)<span class="rm-document-library__identity-icon" aria-hidden="true"><i class="ph-bold ph-folder-user"></i></span>@endif
            <div>
                @if($carpetaActual)<span class="rm-document-library__eyebrow">EXPEDIENTE PERSONAL</span>@endif
                <h2 id="archivos-personal-titulo" x-ref="tituloExpediente" tabindex="-1">{{ $carpetaActual ? trim($carpetaActual->personal->nombres.' '.$carpetaActual->personal->apellido_paterno.' '.$carpetaActual->personal->apellido_materno) : 'Archivos del expediente' }}</h2>
            </div>
        </div>
        @if($carpetaActual)
            <button type="button" class="rm-btn rm-btn-secondary rm-document-library__back" wire:click="cerrarCarpetaUsuario"><i class="ph-bold ph-arrow-left" aria-hidden="true"></i>Volver a carpetas</button>
        @else
            <div class="rm-document-library__tools"><span class="rm-badge rm-badge-info" aria-live="polite">{{ $archivos->total() }} {{ $archivos->total() === 1 ? 'archivo' : 'archivos' }}</span>
                <x-ui.collection-view-switch model="modoArchivos" label="Presentación de documentos" :options="[
                    ['value'=>'tabla','label'=>'Tabla','icon'=>'ph-table'], ['value'=>'carpetas','label'=>'Carpetas','icon'=>'ph-folders']
                ]" />
            </div>
        @endif
    </header>
    <div @class(['rm-document-library__contents', 'rm-document-library__contents--expediente' => $carpetaActual])>
    @if($carpetaActual)
        @include('identidad::livewire.identidad.documentacion-personal-pendientes')
    @endif
    <div class="rm-document-library__files">
    @if($carpetaActual)<h3 class="rm-document-library__files-title"><i class="ph-bold ph-files" aria-hidden="true"></i>Archivos entregados <span class="rm-badge rm-badge-neutral">{{ $archivos->total() }}</span></h3>@endif
    <div @class(['rm-staff-documents__filters', 'rm-staff-documents__filters--compact' => $carpetaActual])>
        <x-ui.field label="Buscar por documento o persona" for="documentacion-buscar"><div class="rm-staff-documents__search"><i class="ph ph-magnifying-glass" aria-hidden="true"></i><input id="documentacion-buscar" class="rm-input" type="search" wire:model.live.debounce.300ms="busqueda" maxlength="160" placeholder="{{ $carpetaActual ? 'Buscar archivo' : 'Buscar documento o persona' }}" autocomplete="off"></div></x-ui.field>
        <x-ui.field label="Estado del documento" for="documentacion-estado"><select id="documentacion-estado" class="rm-select" wire:model.live="estadoArchivo"><option value="">Estado: todos</option><option value="CARGADO">Cargado</option><option value="PENDIENTE">Pendiente de revisión</option><option value="VALIDADO">Validado</option><option value="OBSERVADO">Observado</option><option value="VENCIDO">Vencido registrado</option><option value="ANULADO">Anulado</option><option value="ANULADA">Anulada</option><option value="REEMPLAZADO">Reemplazado</option></select></x-ui.field>
        @if($gestor && ! $carpetaActual)
        <x-ui.field label="Rol del personal" for="documentacion-rol"><select id="documentacion-rol" class="rm-select" wire:model.live="rolArchivo"><option value="">Rol: todos</option>@foreach($rolesArchivo as $nombreRol)<option value="{{ $nombreRol }}">{{ $nombreRol }}</option>@endforeach</select></x-ui.field>
        <x-ui.field label="Trabajador" for="documentacion-persona"><select id="documentacion-persona" class="rm-select" wire:model.live="personaArchivo"><option value="">Personal: todos</option>@foreach($personasArchivo as $personaOpcion)<option value="{{ $personaOpcion->getKey() }}">{{ trim($personaOpcion->personal->nombres.' '.$personaOpcion->personal->apellido_paterno.' '.$personaOpcion->personal->apellido_materno) }}</option>@endforeach</select></x-ui.field>
        @endif
        <x-ui.field label="Formato del archivo" for="documentacion-formato"><select id="documentacion-formato" class="rm-select" wire:model.live="formatoArchivo"><option value="">Formato: todos</option><option value="PDF">PDF</option><option value="IMAGEN">Imágenes (JPG, PNG, WebP)</option><option value="OTROS">Otros formatos</option></select></x-ui.field>
        @if($hayFiltrosArchivo)<button type="button" class="rm-btn rm-btn-secondary" wire:click="limpiarFiltros" wire:loading.attr="disabled">{{ $carpetaActual ? 'Limpiar' : 'Limpiar filtros' }}</button>@endif
    </div>
    <p class="rm-staff-documents__loading" wire:loading wire:target="busqueda,estadoArchivo,rolArchivo,personaArchivo,formatoArchivo,cambiarVista,limpiarFiltros,gotoPage" role="status">Consultando archivos…</p>
    <div x-show="modoArchivos === 'tabla' || $wire.carpetaUsuario !== null" x-cloak>
    @if($archivos->isEmpty())
        <x-ui.empty-state :title="$hayFiltrosArchivo ? 'No hay archivos con estos filtros' : 'Todavía no hay archivos disponibles'" :description="$hayFiltrosArchivo ? 'Prueba con otra selección o limpia los filtros.' : 'Aquí aparecerán los documentos del personal que puedes consultar.'" icon="ph-folder-open" />
    @else
    <div class="rm-staff-directory rm-document-library__table">
        <div class="rm-staff-directory__table-surface"><table @class(['rm-staff-directory__table rm-data-table rm-table', 'rm-document-library__personal-table' => $carpetaActual])><caption class="sr-only">Documentos del personal autorizados para esta cuenta</caption><thead><tr><th scope="col">Documento</th>@unless($carpetaActual)<th scope="col">Trabajador</th>@endunless<th scope="col">Estado</th><th scope="col">{{ $carpetaActual ? 'Fecha' : 'Fecha de carga' }}</th><th scope="col">@if($carpetaActual)<span class="sr-only">Acciones del documento</span>@else Acciones @endif</th></tr></thead><tbody>
        @foreach($archivos as $documento)
        @if($permisosArchivos[$documento->getKey()]['ver'] ?? false)
        @php
    $titular = $documento->usuario->personal;
    $imagenDocumento = in_array(mb_strtolower($documento->extension), ['jpg','jpeg','png','webp'], true);
    $estadoVisible = ['CARGADO'=>'Cargado','PENDIENTE'=>'Pendiente de revisión','VALIDADO'=>'Validado','OBSERVADO'=>'Observado','VENCIDO'=>'Vencido registrado','ANULADO'=>'Anulado','ANULADA'=>'Anulada','REEMPLAZADO'=>'Reemplazado'][$documento->estado] ?? 'Estado por revisar';
    $tonoDocumento = match ($documento->estado) { 'VALIDADO'=>'success', 'OBSERVADO','VENCIDO'=>'warning', 'ANULADO','ANULADA','REEMPLAZADO'=>'neutral', default=>'info' };
    $claseDocumento = match ($documento->tipo_documento) {
        'CI','FOTO_DOC'=>'IDENTIDAD', 'CV','EXP_LABORAL'=>'TRAYECTORIA',
        'TITULO','MATRICULA','CERT_ESP','CAPACITACION'=>'FORMACIÓN',
        'DOMICILIO'=>'DOMICILIO', 'ANTECEDENTES'=>'ANTECEDENTES', default=>'DOCUMENTO' };
@endphp
        <tr wire:key="documento-tabla-{{ $documento->getKey() }}" data-documento-fila="{{ $documento->getKey() }}">
            <td data-label="Documento"><div class="rm-document-library__file">@include('identidad::livewire.identidad.documentacion-personal-miniatura')<div><strong>{{ $documento->nombre ?: 'Documento sin nombre' }}</strong><small>{{ $claseDocumento }} · {{ mb_strtoupper($documento->extension) ?: 'ARCHIVO' }}</small></div></div></td>
            @unless($carpetaActual)<td data-label="Trabajador">{{ trim($titular->nombres.' '.$titular->apellido_paterno.' '.$titular->apellido_materno) }}</td>@endunless
            <td data-label="Estado"><span class="rm-badge rm-badge-{{ $tonoDocumento }}">{{ $estadoVisible }}</span></td>
            <td data-label="Carga"><time aria-label="Fecha de carga: {{ $documento->fecha_hora_carga?->format('d/m/Y H:i') ?? 'Sin fecha conservada' }}">{{ $documento->fecha_hora_carga?->format('d/m/Y H:i') ?? 'Sin fecha conservada' }}</time></td>
            <td data-label="Acciones">@include('identidad::livewire.identidad.documentacion-personal-acciones')</td>
        </tr>
        @endif
        @endforeach
        </tbody></table></div>
    </div>
    @endif
    </div>
    @unless($carpetaActual)
    @if($carpetasUsuarios->isEmpty())
        <div x-show="modoArchivos === 'carpetas'"><x-ui.empty-state title="Sin expedientes en esta consulta" description="Aquí aparecerán los trabajadores con archivos o solicitudes documentales registradas." icon="ph-folder-open" /></div>
    @else
    <ul class="rm-document-folders rm-document-user-folders" x-show="modoArchivos === 'carpetas'" aria-label="Expedientes documentales por trabajador">
        @foreach($carpetasUsuarios as $personaCarpeta)
        <li class="rm-document-folder rm-document-user-folder" wire:key="carpeta-persona-{{ $personaCarpeta->getKey() }}">
            <span class="rm-document-folder__tab"><i class="ph-bold ph-folder-user" aria-hidden="true"></i>EXPEDIENTE PERSONAL</span>
            <button type="button" class="rm-document-folder__body rm-document-user-folder__open" wire:click="abrirCarpetaUsuario(@js($personaCarpeta->getKey()))" wire:loading.attr="disabled" data-carpeta-usuario="{{ $personaCarpeta->getKey() }}" aria-label="Abrir expediente de {{ trim($personaCarpeta->personal->nombres.' '.$personaCarpeta->personal->apellido_paterno.' '.$personaCarpeta->personal->apellido_materno) }}">
                <span class="rm-document-user-folder__icon" aria-hidden="true"><i class="ph-bold ph-folder-simple"></i></span>
                <span class="rm-document-user-folder__identity"><strong>{{ trim($personaCarpeta->personal->nombres.' '.$personaCarpeta->personal->apellido_paterno.' '.$personaCarpeta->personal->apellido_materno) }}</strong><span>{{ $personaCarpeta->total_documentos }} {{ $personaCarpeta->total_documentos == 1 ? 'archivo' : 'archivos' }}{{ $hayFiltrosArchivo ? ' en esta consulta' : '' }}</span></span>
                <span class="rm-document-user-folder__arrow" aria-hidden="true"><i class="ph-bold ph-caret-right"></i></span>
            </button>
        </li>
        @endforeach
    </ul>
    @endif
    @endunless
    <div class="rm-document-library__footer">@if($carpetaActual)<x-ui.paginacion :paginator="$archivos" mode="livewire" label="archivos" :show-per-page="false" :show-jump="true" />@else<div x-show="modoArchivos === 'tabla'"><x-ui.paginacion :paginator="$archivos" mode="livewire" label="archivos" :show-per-page="false" :show-jump="true" /></div><div x-show="modoArchivos === 'carpetas'"><x-ui.paginacion :paginator="$carpetasUsuarios" mode="livewire" label="expedientes" :show-per-page="false" :show-jump="true" /></div>@endif@unless($carpetaActual)<p class="rm-help">La carga no equivale a su validación; los anulados o reemplazados conservan su historia.</p>@endunless</div>
    </div>
    </div>
</section>
