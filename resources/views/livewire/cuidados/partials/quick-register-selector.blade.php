    @if($this->alertasSignosPendientes !== [])
        <div class="rm-signos__evaluation-summary" data-tone="danger" role="alert">
            <strong>Atención pendiente de una lectura crítica</strong>
            <p>Antes de otro control, registra la intervención en la alerta. Puedes repetir signos o documentar la atención y la medicación indicada. El color histórico permanece.</p>
            @foreach($this->alertasSignosPendientes as $pendiente)
                <a class="rm-btn-danger" href="{{ route('admin.enfermeria.alertas', ['alerta' => $pendiente['cod_alerta'], 'adulto' => $detalleResidente['cod_residente']]) }}">Atender {{ $pendiente['cod_alerta'] }}</a>
            @endforeach
        </div>
    @endif
    @error('continuidad_signos')<p class="rm-signos__error" role="alert">{{ $message }}</p>@enderror
@php
    $controles = [
        ['tipo' => 'signos', 'label' => 'Signos vitales', 'icon' => 'ph-heartbeat', 'tone' => 'clinical', 'permiso' => 'signos_vitales.crear', 'disponible' => true],
        ['tipo' => 'dolor', 'label' => 'Dolor', 'icon' => 'ph-thermometer', 'tone' => 'clinical', 'permiso' => 'valoraciones_dolor.crear', 'disponible' => true],
        ['tipo' => 'cognicion', 'label' => 'Cognición', 'icon' => 'ph-brain', 'tone' => 'lavender', 'permiso' => 'controles_cognitivos.ver', 'hint' => 'Seguimiento diario'],
        ['tipo' => 'conducta', 'label' => 'Conducta', 'icon' => 'ph-smiley', 'tone' => 'lavender', 'permiso' => 'registros_conductuales.ver', 'hint' => 'Seguimiento diario'],
        ['tipo' => 'sueno', 'label' => 'Sueño', 'icon' => 'ph-moon', 'tone' => 'lavender', 'permiso' => 'registros_sueno.ver', 'hint' => 'Consultar registros'],
        ['tipo' => 'alimentacion', 'label' => 'Ingesta', 'icon' => 'ph-bowl-food', 'tone' => 'sand', 'permiso' => 'registros_ingesta.crear', 'disponible' => true],
        ['tipo' => 'hidratacion', 'label' => 'Hidratación', 'icon' => 'ph-drop', 'tone' => 'clinical', 'permiso' => 'registros_hidratacion.crear', 'disponible' => true],
        ['tipo' => 'eliminacion', 'label' => 'Eliminación', 'icon' => 'ph-toilet', 'tone' => 'clinical', 'permiso' => 'registros_eliminacion.crear', 'disponible' => true],
        ['tipo' => 'movilidad', 'label' => 'Movilidad', 'icon' => 'ph-person-simple-walk', 'tone' => 'care', 'permiso' => 'registros_movilidad.crear', 'disponible' => true],
        ['tipo' => 'heridas', 'label' => 'Heridas / Curaciones', 'icon' => 'ph-bandaids', 'tone' => 'care', 'permiso' => 'heridas.ver', 'hint' => 'Heridas e historial de curaciones'],
    ];
    $codResidente = $detalleResidente['cod_residente'];
    foreach ($controles as &$control) {
        $opcion = \App\Backend\Modulos\Enfermeria\Servicios\NavegacionCuidadosService::opcion($control['tipo']);
        if (isset($opcion['route'])) {
            $control['href'] = route($opcion['route'], array_merge($opcion['parameters'] ?? [], ['adulto' => $codResidente, 'cuidado' => $control['tipo']]));
            $control['permisos_adicionales'] = ['atenciones.ver'];
        }
    }
    unset($control);
    $acciones = [
        ['tipo' => 'seguimiento', 'label' => 'Seguimiento diario', 'icon' => 'ph-clipboard-text', 'tone' => 'clinical', 'permiso' => 'atenciones.ver', 'href' => route('admin.enfermeria.seguimiento', ['adulto' => $codResidente]), 'hint' => 'Registro completo del turno'],
        ['tipo' => 'medicacion', 'label' => 'Administración de medicación', 'icon' => 'ph-pill', 'tone' => 'care', 'permiso' => 'administraciones_medicacion.crear', 'href' => $tieneMedicacionProgramadaPendiente ? null : route('admin.enfermeria.medicacion', ['adulto' => $codResidente]), 'hint' => $tieneMedicacionProgramadaPendiente ? null : 'Sin dosis pendiente · Ver programación'],
        ['tipo' => 'ejecucion', 'label' => 'Ejecución de cuidado', 'icon' => 'ph-hands-clapping', 'tone' => 'care', 'permiso' => 'ejecuciones_cuidado.ver', 'href' => route('admin.enfermeria.tareas', ['adulto' => $codResidente]), 'hint' => 'Abrir cuidados programados'],
        ['tipo' => 'procedimiento', 'label' => 'Procedimiento', 'icon' => 'ph-first-aid', 'tone' => 'care', 'permiso' => 'atenciones.crear', 'disponible' => true],
        ['tipo' => 'incidente', 'label' => 'Registrar incidente', 'icon' => 'ph-warning-circle', 'tone' => 'danger', 'permiso' => 'incidentes.crear', 'href' => route('admin.enfermeria.incidentes'), 'hint' => 'Abrir módulo de incidentes'],
    ];
    if ($this->alertasSignosPendientes !== []) {
        $atencionUrl = route('admin.enfermeria.alertas', ['alerta' => $this->alertasSignosPendientes[0]['cod_alerta'], 'adulto' => $codResidente]);
        $controles = array_map(function ($control) use ($atencionUrl) {
            if (!in_array($control['tipo'], ['signos', 'sueno', 'heridas'], true)) {
                $control['href'] = $atencionUrl;
                $control['hint'] = 'Atender alerta para habilitar este control';
            }
            return $control;
        }, $controles);
    }
@endphp
@php
    $secciones = [
        ['title' => 'Controles', 'actions' => $controles],
        ['title' => 'Acciones del cuidado', 'columns' => 2, 'actions' => $acciones],
    ];
    if ($puedeConsultarExperto) {
        $secciones[] = ['title' => 'Consulta clínica', 'actions' => [
            ['tipo' => 'experto', 'label' => 'Sistema experto', 'icon' => 'ph-brain', 'tone' => 'lavender', 'permiso' => 'controles_cognitivos.ver', 'href' => route('admin.enfermeria.pacientes.resultados-experto', ['residente' => $codResidente]), 'hint' => 'Resultados, evidencias e historial · Solo lectura'],
        ]];
    }
@endphp
<x-ui.quick-register-grid :sections="$secciones" on-select="abrirFormularioRegistro" />
