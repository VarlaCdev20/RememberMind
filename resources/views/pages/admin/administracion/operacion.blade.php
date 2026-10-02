<x-sistema-layout>
@inject('visibilidadNavegacion', 'App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion')
@php
    $parametrosListadoResidentes = array_filter([
        'search' => $filtros['search'] ?? null,
        'estado' => $filtros['estado'] ?? null,
        'page' => request()->query('page'),
        'vista' => $vistaResidentes,
    ], fn ($valor) => filled($valor));
    $parametrosVistaResidentes = $parametrosListadoResidentes + array_filter([
        'residente' => $panelResidente?->cod_residente,
        'panel_tab' => $panelResidente ? $panelTab : null,
    ], fn ($valor) => filled($valor));
@endphp
@if($modulo === 'residentes')<div class="rm-admin-residents-workspace {{ $panelResidente ? 'is-open' : '' }}">@endif
<div class="rm-admin-page">
    <x-ui.card class="rm-admin-page__heading">
        <p class="rm-caption">Administración / {{ $definicion['titulo'] }}</p>
        <h1 class="rm-heading-page">{{ $definicion['titulo'] }}</h1>
        <p class="rm-body-sm">{{ $definicion['descripcion'] }}</p>
        @if($modulo === 'residentes')
            <p class="rm-caption">Los residentes se crean exclusivamente mediante admisión formal.</p>
        @endif
    </x-ui.card>

    @if($modulo === 'reportes')
        <form method="GET" class="rm-filter-bar rm-admin-page__filters" aria-label="Periodo de reportes">
            <div class="rm-admin-page__filter-field"><label for="reporte-desde">Desde</label><input id="reporte-desde" class="rm-input" type="date" name="desde" value="{{ $filtros['desde'] ?? now()->startOfMonth()->toDateString() }}"></div>
            <div class="rm-admin-page__filter-field"><label for="reporte-hasta">Hasta</label><input id="reporte-hasta" class="rm-input" type="date" name="hasta" value="{{ $filtros['hasta'] ?? now()->toDateString() }}"></div>
            <div class="rm-admin-page__filter-actions"><button type="submit" class="rm-btn-primary">Aplicar periodo</button></div>
        </form>
        <section class="rm-admin-page__reports" aria-label="Resumen administrativo del periodo">
            @foreach($reportes as $reporte)
                @if($visibilidadNavegacion->puedeVerRuta($reporte['ruta'], $reporte['permiso'] ?? null))
                    <x-ui.metric-card :icon="$reporte['icono']" variant="mint" :value="$reporte['total']" :label="$reporte['titulo']" :description="$reporte['titulo'] === 'Ocupación actual' ? 'Camas ocupadas ahora' : 'Registros del periodo'" :href="route($reporte['ruta'])" />
                @endif
            @endforeach
        </section>
        <section class="rm-admin-page__reports" aria-label="Reportes disponibles">
            @foreach([
                ['Ocupación y residentes', 'admin.reportes.adultos.preview', 'reportes.ver', 'ph-bed'],
                ['Operación institucional', 'admin.reportes.institucional.preview', 'reportes.institucional', 'ph-chart-bar'],
                ['Actividad residencial', 'admin.reportes.actividades.preview', 'reportes.ver', 'ph-calendar-dots'],
            ] as [$titulo, $ruta, $permiso, $icono])
                @if($visibilidadNavegacion->puedeVerRuta($ruta, $permiso))
                    <x-ui.card><x-ui.section-header :title="$titulo" :icon="$icono" level="2" />
                        <a class="rm-admin-page__link" href="{{ route($ruta) }}">Abrir reporte <i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>
                    </x-ui.card>
                @endif
            @endforeach
        </section>
    @else
        @if($modulo === 'admisiones')
            <section class="rm-admin-page__reports rm-admin-page__context-summary" aria-label="Situación de admisiones">
                <x-ui.metric-card icon="ph-clipboard-text" variant="mint" :value="$resumenAdmision['por_formalizar']" label="Por formalizar" description="Preadmisiones aprobadas sin admisión" :href="route('admin.administracion.admisiones', ['tab' => 'preparacion'])" />
                <x-ui.metric-card icon="ph-user-check" variant="mint" :value="$resumenAdmision['admitidos']" label="Admitidos" description="Admisiones activas registradas" :href="route('admin.administracion.admisiones', ['tab' => 'admitidos'])" />
                @if($visibilidadNavegacion->puedeVerRuta('admin.administracion.habitaciones'))
                    <x-ui.metric-card icon="ph-bed" variant="mint" :value="$resumenAdmision['camas_disponibles']" label="Camas disponibles" description="Capacidad libre habilitada" :href="route('admin.administracion.habitaciones')" />
                @endif
            </section>
        @endif
        @if($modulo === 'residentes')
            <section class="rm-admin-page__reports rm-admin-page__context-summary" aria-label="Vínculos administrativos pendientes de revisión">
                <x-ui.metric-card icon="ph-clipboard-text" variant="neutral" :value="$resumenResidentes['sin_admision']" label="Sin admisión vinculada" description="Revisar el registro de ingreso" />
                <x-ui.metric-card icon="ph-bed" variant="neutral" :value="$resumenResidentes['sin_cama']" label="Sin cama vigente" description="Sin ocupación activa registrada" />
                <x-ui.metric-card icon="ph-address-book" variant="neutral" :value="$resumenResidentes['sin_responsable']" label="Sin responsable principal" description="Falta un vínculo activo" />
            </section>
        @endif
        @if($modulo === 'admisiones' && $tab === 'preparacion' && $visibilidadNavegacion->puedeVerRuta('admin.admisiones.preadmisiones'))
            <p class="rm-body-sm">Las preadmisiones aprobadas se formalizan con un contacto responsable y una cama disponible.</p>
            <a class="rm-btn-primary rm-admin-page__action" href="{{ route('admin.admisiones.preadmisiones', ['estado' => 'APROBADA']) }}">Revisar preadmisiones aprobadas</a>
        @endif
        <form method="GET" class="rm-filter-bar rm-admin-page__filters" role="search" aria-label="Filtrar {{ mb_strtolower($definicion['titulo']) }}">
            @if($tabs)
                <div class="rm-admin-page__filter-field rm-admin-page__filter-field--view">
                    <label for="admin-vista-{{ $modulo }}">Vista</label>
                    <select id="admin-vista-{{ $modulo }}" class="rm-input" name="tab" x-on:change="$el.form.requestSubmit()">
                        <option value="" disabled>Selecciona una vista</option>
                        @foreach($tabs as $clave => $etiqueta)
                            <option value="{{ $clave }}" @selected($tab === $clave)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if($modulo === 'residentes')<input type="hidden" name="vista" value="{{ $vistaResidentes }}">@endif
            <div class="rm-admin-page__filter-field rm-admin-page__filter-field--search"><label for="admin-search-{{ $modulo }}">Buscar</label><input id="admin-search-{{ $modulo }}" class="rm-input" type="search" name="search" value="{{ $filtros['search'] ?? '' }}" placeholder="Nombre, código o referencia"></div>
            @if($modulo !== 'ocupacion' && $modulo !== 'admisiones')
                <div class="rm-admin-page__filter-field"><label for="admin-estado-{{ $modulo }}">Estado</label><input id="admin-estado-{{ $modulo }}" class="rm-input" type="text" name="estado" value="{{ $filtros['estado'] ?? '' }}" placeholder="Filtrar por estado"></div>
            @endif
            @if($modulo === 'alertas')
                <div class="rm-admin-page__filter-field"><label for="admin-prioridad">Prioridad</label><select id="admin-prioridad" class="rm-input" name="prioridad">
                    <option value="">Todas</option>
                    @foreach(['CRITICA' => 'Crítica', 'ALTA' => 'Alta', 'MEDIA' => 'Media', 'BAJA' => 'Baja'] as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected(($filtros['prioridad'] ?? '') === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select></div>
            @endif
            @if($modulo === 'visitas')
                <div class="rm-admin-page__filter-field"><label for="admin-fecha">Fecha</label><input id="admin-fecha" class="rm-input" type="date" name="fecha" value="{{ $filtros['fecha'] ?? '' }}"></div>
            @endif
            <div class="rm-admin-page__filter-actions"><button type="submit" class="rm-btn-primary">Aplicar filtros</button><a href="{{ route('admin.administracion.'.$modulo) }}" class="rm-btn-secondary">Limpiar</a></div>
        </form>
        <x-ui.card class="rm-admin-page__table-card">
            @if($modulo === 'residentes')
                <div class="rm-admin-residents-toolbar">
                    <x-ui.section-header :title="$definicion['titulo']" :icon="$definicion['icono']" :count="$registros->total()" level="2" />
                    <nav class="rm-admin-residents-view-switch" aria-label="Tipo de vista de residentes">
                        <a href="{{ route('admin.administracion.residentes', array_replace($parametrosVistaResidentes, ['vista' => 'tarjetas'])) }}" @if($vistaResidentes === 'tarjetas') aria-current="page" @endif><i class="ph-bold ph-squares-four" aria-hidden="true"></i> Tarjetas</a>
                        <a href="{{ route('admin.administracion.residentes', array_replace($parametrosVistaResidentes, ['vista' => 'tabla'])) }}" @if($vistaResidentes === 'tabla') aria-current="page" @endif><i class="ph-bold ph-list-dashes" aria-hidden="true"></i> Tabla</a>
                    </nav>
                </div>
            @else
                <x-ui.section-header :title="$definicion['titulo']" :icon="$definicion['icono']" :count="$registros->total()" level="2" />
            @endif
            @if($registros->count())
                @if($modulo === 'residentes' && $vistaResidentes === 'tarjetas')
                    <section class="rm-admin-residents-cards" role="list" aria-label="Residentes encontrados">
                        @foreach($registros as $registro)
                            @php
                                $partesNombre = preg_split('/\s+/u', trim($registro->titulo)) ?: [];
                                $iniciales = mb_strtoupper(mb_substr($partesNombre[0] ?? 'R', 0, 1).mb_substr($partesNombre[1] ?? '', 0, 1));
                                $edad = $registro->fecha ? \Carbon\Carbon::parse($registro->fecha)->age : null;
                                $urlResumen = route('admin.administracion.residentes', $parametrosListadoResidentes + ['residente' => $registro->codigo]);
                            @endphp
                            <article class="rm-admin-resident-card {{ $panelResidente?->cod_residente === $registro->codigo ? 'is-selected' : '' }}" role="listitem">
                                <div class="rm-admin-resident-card__head">
                                    @if($registro->foto)
                                        <img src="{{ asset('storage/'.$registro->foto) }}" alt="Foto de {{ $registro->titulo }}" loading="lazy" decoding="async">
                                    @else
                                        <span class="rm-admin-resident-card__avatar" aria-hidden="true">{{ $iniciales }}</span>
                                    @endif
                                    <div class="rm-admin-resident-card__identity">
                                        <h3><a href="{{ $urlResumen }}">{{ $registro->titulo }}</a></h3>
                                        <p>{{ $registro->codigo }}</p>
                                    </div>
                                    <x-ui.status-badge :estado="$registro->estado" />
                                </div>
                                <p class="rm-admin-resident-card__meta">{{ $registro->documento ? 'CI '.$registro->documento : 'CI no registrado' }} <span aria-hidden="true">·</span> {{ $edad !== null ? $edad.' años' : 'Edad no registrada' }}</p>
                                <dl class="rm-admin-resident-card__facts">
                                    <div><dt><i class="ph-bold ph-bed" aria-hidden="true"></i> Habitación</dt><dd><strong>{{ $registro->habitacion ?: 'Sin asignar' }}</strong>@if($registro->sector)<small>{{ $registro->sector }}</small>@elseif($registro->cama)<small>{{ $registro->cama }}</small>@endif</dd></div>
                                    <div><dt><i class="ph-bold ph-user-circle" aria-hidden="true"></i> Responsable</dt><dd><strong>{{ $registro->responsable ?: 'No registrado' }}</strong>@if($registro->responsable && $registro->parentesco)<small>{{ $registro->parentesco }}</small>@endif</dd></div>
                                </dl>
                                <div class="rm-admin-resident-card__foot"><span><i class="ph-bold ph-clipboard-text" aria-hidden="true"></i> {{ $registro->admision === 'Registrada' ? 'Admisión registrada' : 'Sin admisión vinculada' }}</span><a href="{{ $urlResumen }}">Ver resumen <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a></div>
                            </article>
                        @endforeach
                    </section>
                @else
                <div class="rm-admin-page__table-scroll">
                    <table class="rm-data-table rm-data-table--actions rm-table">
                        <thead><tr>
                            <th scope="col">Registro</th>
                            @foreach($columnas as $campo => $etiqueta)
                                <th scope="col">{{ $etiqueta }}</th>
                            @endforeach
                            @if(($modulo === 'admisiones' && ($visibilidadNavegacion->puedeVerRuta('admin.admisiones.preadmisiones') || $visibilidadNavegacion->puedeVerRuta('admin.administracion.residentes.show'))) || ($modulo === 'residentes' && $visibilidadNavegacion->puedeVerRuta('admin.administracion.residentes.show')))<th scope="col">Acción</th>@endif
                        </tr></thead>
                        <tbody>
                            @foreach($registros as $registro)
                                <tr @class(['rm-admin-page__row-selected' => $modulo === 'residentes' && $panelResidente?->cod_residente === $registro->codigo])>
                                    <td data-label="Registro"><strong>{{ $registro->titulo }}</strong><small class="rm-admin-page__code">{{ $registro->codigo }}</small></td>
                                    @foreach($columnas as $campo => $etiqueta)
                                        <td data-label="{{ $etiqueta }}">
                                            @if($campo === 'estado')
                                                <x-ui.status-badge :estado="$registro->estado" />
                                            @elseif(in_array($campo, ['principal', 'emergencia', 'requiere_medico', 'requiere_derivacion'], true))
                                                {{ isset($registro->{$campo}) ? ($registro->{$campo} ? 'Sí' : 'No') : '—' }}
                                            @elseif(in_array($campo, ['fecha', 'fecha_fin', 'programada', 'ingreso', 'salida', 'validacion'], true))
                                                {{ $registro->{$campo} ? \Carbon\Carbon::parse($registro->{$campo})->format($modulo === 'residentes' && $campo === 'fecha' ? 'd/m/Y' : 'd/m/Y H:i') : '—' }}
                                            @else
                                                {{ $registro->{$campo} ?? '—' }}
                                            @endif
                                        </td>
                                    @endforeach
                                    @if(($modulo === 'admisiones' && ($tab === 'preparacion' ? $visibilidadNavegacion->puedeVerRuta('admin.admisiones.preadmisiones') : $visibilidadNavegacion->puedeVerRuta('admin.administracion.residentes.show'))) || ($modulo === 'residentes' && $visibilidadNavegacion->puedeVerRuta('admin.administracion.residentes.show')))
                                        <td data-label="Acción">
                                            @if($modulo === 'residentes')
                                                <a class="rm-admin-page__link" href="{{ route('admin.administracion.residentes', $parametrosListadoResidentes + ['residente' => $registro->codigo]) }}">Ver resumen</a>
                                            @elseif($tab === 'preparacion')
                                                <a class="rm-admin-page__link" href="{{ route('admin.admisiones.preadmisiones', ['estado' => 'APROBADA', 'search' => $registro->codigo]) }}">Continuar</a>
                                            @else
                                                <a class="rm-admin-page__link" href="{{ route('admin.administracion.residentes.show', $registro->cod_residente) }}">Ver residente</a>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                <div class="rm-admin-page__pagination">{{ $registros->links() }}</div>
            @else
                @php
                    $hayFiltros = filled($filtros['search'] ?? null) || filled($filtros['estado'] ?? null)
                        || filled($filtros['prioridad'] ?? null) || filled($filtros['fecha'] ?? null);
                    [$tituloVacio, $mensajeVacio, $rutaVacia, $accionVacia] = match (true) {
                        $hayFiltros => ['Sin resultados para estos filtros', 'Ajusta la búsqueda o limpia los filtros para ver los registros disponibles.', null, null],
                        $modulo === 'admisiones' && $tab === 'preparacion' => ['No hay ingresos por formalizar', 'Cuando se apruebe una preadmisión, aparecerá aquí para completar el ingreso.', null, null],
                        $modulo === 'admisiones' && $tab === 'admitidos' => ['No hay admisiones activas', 'Las admisiones formalizadas aparecerán aquí junto con su residente y cama.', 'admin.admisiones.preadmisiones', 'Ver preadmisiones'],
                        $modulo === 'admisiones' && $tab === 'historial' => ['Sin admisiones históricas', 'Las admisiones que dejen de estar activas aparecerán aquí.', null, null],
                        $modulo === 'habitaciones' => ['No hay camas registradas', 'La disponibilidad necesita habitaciones y camas registradas.', null, null],
                        $modulo === 'ocupacion' => ['No hay ocupaciones en esta vista', 'Las asignaciones de cama de las admisiones formalizadas aparecerán aquí.', null, null],
                        $modulo === 'jornadas' => ['No hay jornadas en esta vista', 'La programación y las jornadas abiertas aparecerán aquí.', null, null],
                        $modulo === 'asignaciones' => ['No hay personal asignado', 'Las asignaciones necesitan una jornada, un área y un integrante del personal.', null, null],
                        $modulo === 'contactos' => ['No hay contactos registrados', 'Los responsables se vinculan al residente durante el ingreso o su actualización administrativa.', 'admin.administracion.residentes', 'Ver residentes'],
                        $modulo === 'documentacion' => ['No hay documentos en esta vista', 'Los documentos registrados para residentes, preadmisiones o contactos aparecerán aquí.', null, null],
                        $modulo === 'consentimientos' => ['No hay consentimientos en esta vista', 'Cada consentimiento se vincula a un residente, una admisión y su firmante.', null, null],
                        $modulo === 'actividades' => ['No hay actividades programadas', 'La agenda se llena al registrar actividades institucionales.', 'admin.actividades.index', 'Gestionar actividades'],
                        $modulo === 'visitas' => ['No hay visitas en esta vista', 'Las visitas se vinculan a un residente y a uno de sus contactos.', 'admin.administracion.residentes', 'Ver residentes'],
                        $modulo === 'seguros' => ['No hay seguros registrados', 'Los seguros deben estar vinculados a un residente admitido.', 'admin.administracion.residentes', 'Ver residentes'],
                        $modulo === 'alertas' => ['No hay alertas en esta vista', 'Las alertas registradas para residentes aparecerán aquí según su estado.', null, null],
                        $modulo === 'incidentes' => ['No hay incidentes en esta vista', 'Los incidentes registrados durante la atención aparecerán aquí.', null, null],
                        default => ['Sin registros en esta vista', 'Aún no hay información registrada para esta sección.', null, null],
                    };
                @endphp
                <x-ui.empty-state :icono="$definicion['icono']" :titulo="$tituloVacio" :texto="$mensajeVacio" />
                @if($rutaVacia && $visibilidadNavegacion->puedeVerRuta($rutaVacia))
                    <a class="rm-admin-page__link" href="{{ route($rutaVacia) }}">{{ $accionVacia }} <i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>
                @endif
            @endif
        </x-ui.card>
    @endif
</div>
@if($modulo === 'residentes')
    @if($panelResidente)
        @include('pages.admin.administracion.partials.panel-residente', ['residente' => $panelResidente, 'datosPanel' => $panelDatos])
    @endif
</div>
@endif
</x-sistema-layout>
