@php
    $controles = [
        ['tipo' => 'signos', 'label' => 'Signos vitales', 'icon' => 'ph-heartbeat', 'tone' => 'clinical', 'permiso' => 'signos_vitales.crear', 'disponible' => true],
        ['tipo' => 'dolor', 'label' => 'Dolor', 'icon' => 'ph-thermometer', 'tone' => 'clinical', 'permiso' => 'valoraciones_dolor.crear', 'disponible' => true],
        ['tipo' => 'cognicion', 'label' => 'Cognición', 'icon' => 'ph-brain', 'tone' => 'lavender', 'permiso' => 'controles_cognitivos.crear', 'disponible' => false],
        ['tipo' => 'conducta', 'label' => 'Conducta', 'icon' => 'ph-smiley', 'tone' => 'lavender', 'permiso' => 'registros_conductuales.crear', 'disponible' => false],
        ['tipo' => 'sueno', 'label' => 'Sueño', 'icon' => 'ph-moon', 'tone' => 'lavender', 'permiso' => 'registros_sueno.crear', 'disponible' => false],
        ['tipo' => 'alimentacion', 'label' => 'Ingesta', 'icon' => 'ph-bowl-food', 'tone' => 'sand', 'permiso' => 'registros_ingesta.crear', 'disponible' => true],
        ['tipo' => 'hidratacion', 'label' => 'Hidratación', 'icon' => 'ph-drop', 'tone' => 'clinical', 'permiso' => 'registros_hidratacion.crear', 'disponible' => false],
        ['tipo' => 'eliminacion', 'label' => 'Eliminación', 'icon' => 'ph-toilet', 'tone' => 'clinical', 'permiso' => 'registros_eliminacion.crear', 'disponible' => true],
        ['tipo' => 'movilidad', 'label' => 'Movilidad', 'icon' => 'ph-person-simple-walk', 'tone' => 'care', 'permiso' => 'registros_movilidad.crear', 'disponible' => true],
    ];
    $acciones = [
        ['tipo' => 'medicacion', 'label' => 'Administración de medicación', 'icon' => 'ph-pill', 'tone' => 'care', 'permiso' => 'administraciones_medicacion.crear', 'disponible' => $tieneMedicacionProgramadaPendiente, 'hint' => 'Sin dosis pendiente'],
        ['tipo' => 'ejecucion', 'label' => 'Ejecución de cuidado', 'icon' => 'ph-hands-clapping', 'tone' => 'care', 'permiso' => 'ejecuciones_cuidado.crear', 'disponible' => false],
        ['tipo' => 'procedimiento', 'label' => 'Curación', 'icon' => 'ph-first-aid', 'tone' => 'care', 'permiso' => 'atenciones.crear', 'disponible' => true],
        ['tipo' => 'incidente', 'label' => 'Registrar incidente', 'icon' => 'ph-warning-circle', 'tone' => 'danger', 'permiso' => 'incidentes.crear', 'disponible' => false],
    ];
@endphp
<x-ui.quick-register-grid :sections="[
    ['title' => 'Controles', 'actions' => $controles],
    ['title' => 'Acciones del cuidado', 'columns' => 2, 'actions' => $acciones],
]" on-select="abrirFormularioRegistro" />
