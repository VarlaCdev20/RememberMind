<x-sistema-layout>
@inject('visibilidadNavegacion', 'App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion')
@php
    $parametros = array_filter(['tab' => $tab, 'search' => $filtros['search'] ?? '', 'vista' => $vistaAdmisiones,
        'orden' => $ordenAdmisiones, 'por_pagina' => $porPagina, 'desde' => $filtros['desde'] ?? '', 'hasta' => $filtros['hasta'] ?? ''], fn ($valor) => filled($valor));
    $etapas = [
        'preparacion' => ['Por formalizar', 'ph-clipboard-text', $resumenAdmision['por_formalizar'], 'Solicitudes aprobadas para preparar el ingreso'],
        'admitidos' => ['Admitidos', 'ph-user-check', $resumenAdmision['admitidos'], 'Admisiones activas registradas'],
        'historial' => ['Historial', 'ph-clock-counter-clockwise', $resumenAdmision['historial'], 'Admisiones anteriores para consulta'],
    ];
    $hayFiltros = filled($filtros['search'] ?? '') || filled($filtros['desde'] ?? '') || filled($filtros['hasta'] ?? '') || $ordenAdmisiones !== 'recientes';
@endphp
<div class="rm-admissions" x-data="rmAdmisiones()" :aria-busy="loading" x-on:livewire:navigated.window="loading = false">
    <x-ui.collection-header title="Admisiones" subtitle="Prepara cada ingreso y consulta su trayectoria en la residencia." icon="ph-clipboard-text" eyebrow="Administración / Ingresos" :date="now()->locale('es')">
        <x-slot:actions>
            @if($visibilidadNavegacion->puedeVerRuta('admin.admisiones.preadmisiones'))
                <a wire:navigate href="{{ route('admin.admisiones.preadmisiones') }}" class="rm-btn-secondary"><i class="ph-bold ph-files" aria-hidden="true"></i> Preadmisiones</a>
            @endif
        </x-slot:actions>
    </x-ui.collection-header>

    <section class="rm-admissions-overview" aria-label="Situación de admisiones">
        @foreach($etapas as $clave => [$titulo, $icono, $cantidad, $descripcion])
            <a wire:navigate href="{{ route('admin.administracion.admisiones', array_replace($parametros, ['tab' => $clave])) }}" class="rm-admissions-metric {{ $tab === $clave ? 'is-active' : '' }}" @if($tab === $clave) aria-current="page" @endif>
                <span class="rm-admissions-metric__icon"><i class="ph-bold {{ $icono }}" aria-hidden="true"></i></span>
                <div><strong>{{ $cantidad }}</strong><h2>{{ $titulo }}</h2><p>{{ $descripcion }}</p></div>
                <i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i>
            </a>
        @endforeach
        @if($visibilidadNavegacion->puedeVerRuta('admin.administracion.habitaciones'))
            <a wire:navigate href="{{ route('admin.administracion.habitaciones') }}" class="rm-admissions-capacity"><i class="ph-bold ph-bed" aria-hidden="true"></i><span><strong>{{ $resumenAdmision['camas_disponibles'] }} camas disponibles</strong><small>Consultar alojamiento habilitado</small></span><i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
        @endif
    </section>

    @if($tab === 'preparacion')
        <div class="rm-admissions-guidance" role="note"><i class="ph-bold ph-info" aria-hidden="true"></i><p>La aprobación permite preparar el ingreso. <strong>El residente se crea al formalizar la admisión con una cama disponible.</strong></p></div>
    @endif

    <form class="rm-filter-bar rm-admissions-filters" x-ref="filtros" action="{{ route('admin.administracion.admisiones') }}" method="GET" role="search" aria-label="Filtrar admisiones" @submit.prevent="filtrar()">
        <input type="hidden" name="tab" value="{{ $tab }}"><input type="hidden" name="vista" value="{{ $vistaAdmisiones }}"><input type="hidden" name="por_pagina" value="{{ $porPagina }}">
        @foreach(['desde', 'hasta'] as $fecha) @if(filled($filtros[$fecha] ?? ''))<input type="hidden" name="{{ $fecha }}" value="{{ $filtros[$fecha] }}">@endif @endforeach
        <div class="rm-admissions-search"><label for="admisiones-buscar">Buscar en {{ mb_strtolower($etapas[$tab][0]) }}</label><div><i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i><input type="search" name="search" id="admisiones-buscar" value="{{ $filtros['search'] ?? '' }}" maxlength="100" placeholder="Nombre, documento, código o habitación…" @input.debounce.500ms="filtrar()"></div></div>
        <div class="rm-admissions-sort"><label for="admisiones-orden">Ordenar</label><x-ui.selector name="orden" id="admisiones-orden" label="Ordenar admisiones" :auto-submit="true"><option value="recientes" @selected($ordenAdmisiones === 'recientes')>Más recientes</option><option value="antiguas" @selected($ordenAdmisiones === 'antiguas')>Más antiguas</option></x-ui.selector></div>
        <button type="submit" class="rm-btn-primary" :disabled="loading"><i class="ph-bold ph-sliders-horizontal" aria-hidden="true"></i><span x-text="loading ? 'Actualizando…' : 'Buscar'">Buscar</span></button>
        @if($hayFiltros)<a wire:navigate href="{{ route('admin.administracion.admisiones', ['tab' => $tab, 'vista' => $vistaAdmisiones, 'por_pagina' => $porPagina]) }}" class="rm-btn-secondary" aria-label="Limpiar filtros de admisiones"><i class="ph-bold ph-arrow-counter-clockwise" aria-hidden="true"></i> Limpiar</a>@endif
    </form>

    <section class="rm-admissions-results" aria-label="Resultados de admisiones">
        <header class="rm-admissions-results__header">
            <div><span class="rm-admissions-results__icon"><i class="ph-bold {{ $etapas[$tab][1] }}" aria-hidden="true"></i></span><div><h2>{{ $etapas[$tab][0] }} <span>{{ $registros->total() }}</span></h2><p>{{ $hayFiltros ? 'Resultados según tus filtros' : $etapas[$tab][3] }}</p></div></div>
            <div class="rm-view-toggle" role="group" aria-label="Vista de admisiones">
                @foreach(['tabla' => ['Tabla','ph-table'], 'lista' => ['Lista','ph-list-dashes'], 'tarjetas' => ['Tarjetas','ph-squares-four']] as $vista => [$nombre, $icono])
                    <a wire:navigate class="rm-view-toggle__btn {{ $vistaAdmisiones === $vista ? 'is-active' : '' }}" href="{{ route('admin.administracion.admisiones', array_replace($parametros, ['vista' => $vista])) }}" @if($vistaAdmisiones === $vista) aria-current="page" @endif aria-label="Vista {{ mb_strtolower($nombre) }}"><i class="ph-bold {{ $icono }}" aria-hidden="true"></i><span>{{ $nombre }}</span></a>
                @endforeach
            </div>
        </header>
        <p x-show="loading" x-cloak class="rm-admissions-loading" role="status"><i class="ph-bold ph-spinner animate-spin" aria-hidden="true"></i> Actualizando resultados…</p>
        @if($registros->count())
            @if($vistaAdmisiones === 'tabla')
                <div class="rm-admissions-table-scroll"><table class="rm-table rm-admissions-table"><caption class="sr-only">{{ $etapas[$tab][0] }}: {{ $registros->total() }} resultados</caption><thead><tr><th scope="col">{{ $tab === 'preparacion' ? 'Postulante' : 'Residente' }}</th><th scope="col">Documento</th><th scope="col">Ubicación</th><th scope="col">{{ $tab === 'preparacion' ? 'Solicitud' : 'Ingreso' }}</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead><tbody>
                    @foreach($registros as $registro)
                        @include('pages.admin.administracion.partials.admision-registro', ['modo' => 'tabla'])
                    @endforeach
                </tbody></table></div>
            @else
                <div class="rm-admissions-records rm-admissions-records--{{ $vistaAdmisiones }}" role="list" aria-label="{{ $etapas[$tab][0] }}">
                    @foreach($registros as $registro)
                        @include('pages.admin.administracion.partials.admision-registro', ['modo' => $vistaAdmisiones])
                    @endforeach
                </div>
            @endif
            <x-ui.paginacion :paginator="$registros" mode="url" :per-page="$porPagina" per-page-name="por_pagina" label="admisiones" />
        @else
            <div class="rm-admissions-empty"><span><i class="ph-bold {{ $hayFiltros ? 'ph-magnifying-glass' : $etapas[$tab][1] }}" aria-hidden="true"></i></span><h3>{{ $hayFiltros ? 'Sin resultados para estos filtros' : ($tab === 'preparacion' ? 'No hay ingresos por formalizar' : 'No hay registros en esta etapa') }}</h3><p>{{ $hayFiltros ? 'Ajusta la búsqueda o limpia los filtros para continuar.' : ($tab === 'preparacion' ? 'Las solicitudes aprobadas aparecerán aquí para preparar su ingreso.' : 'Cuando exista actividad en esta etapa, podrás consultarla aquí.') }}</p>
                @if($hayFiltros)<a wire:navigate href="{{ route('admin.administracion.admisiones', ['tab' => $tab, 'vista' => $vistaAdmisiones]) }}" class="rm-btn-secondary">Limpiar filtros</a>@elseif($tab === 'preparacion' && $visibilidadNavegacion->puedeVerRuta('admin.admisiones.preadmisiones'))<a wire:navigate href="{{ route('admin.admisiones.preadmisiones', ['estado' => 'APROBADA']) }}" class="rm-btn-secondary">Revisar preadmisiones aprobadas <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@endif
            </div>
        @endif
    </section>

    <template x-teleport="body">
        <div x-show="resumen" x-cloak x-trap.inert.noscroll="resumen !== null" class="rm-modal-shell rm-admissions-summary fixed inset-0 flex items-center justify-center p-4" @keydown.escape.prevent.stop="cerrarResumen()">
            <div class="rm-modal-shell__overlay" @click="cerrarResumen()"></div>
            <section class="rm-modal-panel relative" role="dialog" aria-modal="true" aria-labelledby="admision-resumen-title" x-transition.opacity.duration.160ms>
                <header class="rm-modal-header"><div><span class="rm-admissions-eyebrow">Resumen del registro</span><h2 id="admision-resumen-title" x-text="resumen?.nombre"></h2><p x-text="resumen?.codigo"></p></div><button type="button" x-ref="cerrarResumen" class="rm-btn-icon" aria-label="Cerrar resumen de admisión" @click="cerrarResumen()"><i class="ph-bold ph-x" aria-hidden="true"></i></button></header>
                <div class="rm-modal-body"><dl class="rm-admissions-summary__data"><div><dt>Documento</dt><dd x-text="resumen?.documento"></dd></div><div><dt>Etapa</dt><dd x-text="resumen?.etapa"></dd></div><div><dt>Fecha registrada</dt><dd x-text="resumen?.fecha"></dd></div><div><dt>Estado</dt><dd x-text="resumen?.estado"></dd></div><div><dt>Habitación</dt><dd x-text="resumen?.habitacion"></dd></div><div><dt>Cama</dt><dd x-text="resumen?.cama"></dd></div></dl><p class="rm-admissions-summary__note">Este resumen permite consultar el registro. Abre el expediente para continuar el proceso correspondiente.</p></div>
                <footer class="rm-modal-footer"><button type="button" class="rm-btn-secondary" @click="cerrarResumen()">Cerrar</button><a x-show="resumen?.url" :href="resumen?.url" wire:navigate class="rm-btn-primary"><span x-text="resumen?.accion"></span><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a></footer>
            </section>
        </div>
    </template>
</div>
</x-sistema-layout>
