@php
    $inicioMes = $mesCalendario->copy()->startOfMonth();
    $finMes = $inicioMes->copy()->endOfMonth();
    $diasConDatos = $diasCalendario->keyBy('dia');
    $eventosPorDia = $eventosCalendario->groupBy('dia_calendario');
    $diaInicial = $filtros['dia'] ?? $diasCalendario->first()?->dia ?? $inicioMes->toDateString();
    $hoyCalendario = today()->toDateString();
    $diasVaciosInicio = $inicioMes->dayOfWeekIso - 1;
    $diasVaciosFin = 7 - $finMes->dayOfWeekIso;
    $camposCalendario = match ($modulo) {
        'jornadas' => ['horario', 'asignados'],
        'actividades' => ['detalle', 'area', 'responsable', 'cupo', 'participantes'],
        'visitas' => ['detalle', 'programada', 'ingreso', 'salida'],
    };
@endphp
<section class="rm-operation-calendar" x-data="{ diaSeleccionado: @js($diaInicial) }" aria-label="Calendario mensual de {{ mb_strtolower($definicion['titulo']) }}">
    <header class="rm-operation-calendar__header">
        <div>
            <h3 id="calendario-mes-{{ $modulo }}">{{ ucfirst($inicioMes->copy()->locale('es')->translatedFormat('F Y')) }}</h3>
            <p>{{ $totalCalendario }} {{ $presentacion['unidad'] }} con fecha en este mes, según los filtros aplicados.</p>
        </div>
        <nav class="rm-operation-calendar__navigation" aria-label="Cambiar mes">
            <a wire:navigate class="rm-btn-icon" href="{{ $enlace(['mes' => $inicioMes->copy()->subMonth()->format('Y-m'), 'dia' => null, 'page' => null]) }}" aria-label="Mes anterior"><i class="ph-bold ph-caret-left" aria-hidden="true"></i></a>
            <a wire:navigate class="rm-btn-secondary" href="{{ $enlace(['mes' => today()->format('Y-m'), 'dia' => null, 'page' => null]) }}" @if($inicioMes->format('Y-m') === today()->format('Y-m') && empty($filtros['dia'])) @click.prevent="diaSeleccionado = @js($hoyCalendario)" @endif>Hoy</a>
            <a wire:navigate class="rm-btn-icon" href="{{ $enlace(['mes' => $inicioMes->copy()->addMonth()->format('Y-m'), 'dia' => null, 'page' => null]) }}" aria-label="Mes siguiente"><i class="ph-bold ph-caret-right" aria-hidden="true"></i></a>
        </nav>
    </header>
    <p class="rm-operation-calendar__help" id="calendario-ayuda-{{ $modulo }}">Selecciona un día para consultar sus registros. El calendario muestra el mes completo; los detalles se abren en una ficha.</p>
    <div class="rm-operation-calendar__scroll" tabindex="0" role="region" aria-label="Días del mes" aria-describedby="calendario-ayuda-{{ $modulo }}">
        <div class="rm-operation-calendar__weekdays" aria-hidden="true">
            @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $nombreDia)<span>{{ $nombreDia }}</span>@endforeach
        </div>
        <div class="rm-operation-calendar__grid" aria-labelledby="calendario-mes-{{ $modulo }}">
            @for($relleno = 0; $relleno < $diasVaciosInicio; $relleno++)<span class="rm-operation-calendar__blank" aria-hidden="true"></span>@endfor
            @for($numeroDia = 1; $numeroDia <= $finMes->day; $numeroDia++)
                @php
                    $fechaCelda = $inicioMes->copy()->day($numeroDia);
                    $claveDia = $fechaCelda->toDateString();
                    $cantidadDia = (int) ($diasConDatos->get($claveDia)?->cantidad ?? 0);
                    $eventosDia = $eventosPorDia->get($claveDia, collect());
                    $esHoy = $claveDia === $hoyCalendario;
                @endphp
                <button type="button" class="rm-operation-calendar__day {{ $esHoy ? 'is-today' : '' }} {{ $cantidadDia ? 'has-events' : '' }}" :class="{ 'is-selected': diaSeleccionado === @js($claveDia) }" :aria-pressed="diaSeleccionado === @js($claveDia)" @click="diaSeleccionado = @js($claveDia)" aria-controls="calendario-panel-{{ $modulo }}-{{ $claveDia }}" aria-label="{{ $fechaCelda->locale('es')->translatedFormat('l, d \d\e F') }}{{ $esHoy ? ', hoy' : '' }}: {{ $cantidadDia }} {{ $presentacion['unidad'] }}">
                    <span class="rm-operation-calendar__day-top"><time datetime="{{ $claveDia }}">{{ $numeroDia }}</time>@if($esHoy)<small>Hoy</small>@endif</span>
                    <span class="rm-operation-calendar__count">{{ $cantidadDia }} <span>{{ $cantidadDia === 1 ? 'registro' : 'registros' }}</span></span>
                    @if($eventosDia->isNotEmpty())<span class="rm-operation-calendar__preview" aria-hidden="true">{{ $eventosDia->first()->titulo }}</span>@endif
                </button>
            @endfor
            @for($relleno = 0; $relleno < $diasVaciosFin; $relleno++)<span class="rm-operation-calendar__blank" aria-hidden="true"></span>@endfor
        </div>
    </div>
    @if($totalCalendario === 0)<p class="rm-operation-calendar__notice"><i class="ph-bold ph-calendar-blank" aria-hidden="true"></i> No hay registros con fecha en este mes para los filtros aplicados. @if($estados->sum('cantidad') > 0)<a wire:navigate class="rm-btn-secondary" href="{{ $enlace(['vista'=>'tabla','tab'=>$bandejaGeneral,'search'=>null,'categoria'=>null,'estado'=>null,'funcion'=>null,'desde'=>null,'hasta'=>null,'dia'=>null]) }}">Ver todos los registros</a>@endif</p>@endif
    @if($calendarioSinFecha > 0)<p class="rm-operation-calendar__notice"><i class="ph-bold ph-info" aria-hidden="true"></i> {{ $calendarioSinFecha }} {{ $presentacion['unidad'] }} {{ $modulo === 'visitas' ? ($presentacion['campoFecha'] === 'ingreso' ? 'sin entrada registrada' : 'sin programación registrada') : 'sin fecha registrada' }} no se ubican en este calendario.</p>@endif
    <div class="rm-operation-calendar__panels">
        @for($numeroDia = 1; $numeroDia <= $finMes->day; $numeroDia++)
            @php
                $fechaPanel = $inicioMes->copy()->day($numeroDia);
                $clavePanel = $fechaPanel->toDateString();
                $cantidadPanel = (int) ($diasConDatos->get($clavePanel)?->cantidad ?? 0);
                $diaFiltrado = ($filtros['dia'] ?? null) === $clavePanel;
                $registrosPanel = $diaFiltrado ? $registros->getCollection() : $eventosPorDia->get($clavePanel, collect());
            @endphp
            <section class="rm-operation-calendar__panel" id="calendario-panel-{{ $modulo }}-{{ $clavePanel }}" x-show="diaSeleccionado === @js($clavePanel)" @if($diaInicial !== $clavePanel) x-cloak @endif aria-labelledby="calendario-dia-{{ $modulo }}-{{ $clavePanel }}">
                <header class="rm-operation-calendar__panel-header">
                    <div><h4 id="calendario-dia-{{ $modulo }}-{{ $clavePanel }}">{{ ucfirst($fechaPanel->locale('es')->translatedFormat('l, d \d\e F')) }}</h4><p>{{ $cantidadPanel }} registros de {{ $presentacion['unidad'] }} en este día.</p></div>
                    @if($cantidadPanel > 0 && ! $diaFiltrado)<a wire:navigate class="rm-btn-secondary" href="{{ $enlace(['mes' => $inicioMes->format('Y-m'), 'dia' => $clavePanel, 'page' => null]) }}">Ver todos los registros de este día <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@endif
                </header>
                @if(! $diaFiltrado && $cantidadPanel > $registrosPanel->count())<p class="rm-operation-calendar__help">Vista previa de {{ $registrosPanel->count() }} de {{ $cantidadPanel }} registros. Abre la lista del día para consultar todos.</p>@endif
                <div class="rm-operation-calendar__events">
                    @forelse($registrosPanel as $registro)
                        @php($fechaEvento = $registro->{$presentacion['campoFecha']} ?? null)
                        <article class="rm-operation-calendar__event">
                            <header><span class="rm-operation-calendar__event-icon"><i class="ph-bold {{ $definicion['icono'] }}" aria-hidden="true"></i></span><div><h5>{{ $registro->titulo }}</h5><small>{{ $registro->codigo }}</small></div><x-ui.status-badge :estado="$registro->estado" /></header>
                            <p class="rm-operation-calendar__time"><i class="ph-bold ph-clock" aria-hidden="true"></i>@if($modulo === 'jornadas'){{ $registro->horario ?? 'Horario sin registrar' }}@else{{ $fechaEvento ? \Carbon\Carbon::parse($fechaEvento)->format('d/m/Y H:i') : 'Fecha sin registrar' }}@endif</p>
                            <dl>@foreach($camposCalendario as $campo)<div><dt>{{ $columnas[$campo] }}</dt><dd>@include('pages.admin.administracion.partials.campo-operativo', ['valor' => $registro->{$campo} ?? null])</dd></div>@endforeach</dl>
                            <a class="rm-btn-secondary" href="{{ $enlace(['detalle' => $registro->codigo]) }}" data-registro="{{ $registro->codigo }}" @click.prevent="abrirFicha(@js($enlace(['detalle' => $registro->codigo])), @js($registro->codigo), $event)" aria-label="Abrir ficha de {{ $registro->titulo }}"><i class="ph-bold ph-eye" aria-hidden="true"></i> Abrir ficha</a>
                        </article>
                    @empty
                        <p class="rm-operation-calendar__empty">{{ $cantidadPanel > 0 ? 'Esta página no contiene registros del día. Revisa la paginación.' : 'No hay registros para este día con los filtros aplicados.' }}</p>
                    @endforelse
                </div>
                @if($diaFiltrado)<x-ui.paginacion :exclude-query="['detalle']" :paginator="$registros" mode="url" :per-page="$porPagina" :label="$presentacion['unidad']" />@endif
            </section>
        @endfor
    </div>
</section>
