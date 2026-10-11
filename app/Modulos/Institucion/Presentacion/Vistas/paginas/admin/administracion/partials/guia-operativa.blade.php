@php
    $guia = match ($modulo) {
        'jornadas' => [
            'campos' => [
                'Fecha' => 'Día registrado para la jornada.',
                'Horario' => 'Hora de inicio y cierre del turno vinculado.',
                'Personal asignado' => 'Personas distintas con una asignación activa en esa jornada.',
            ],
            'siguiente' => 'Elige Hoy o Próximas y abre una ficha para revisar el horario y el personal asignado.',
        ],
        'asignaciones' => [
            'campos' => [
                'Día y turno' => 'Día de la jornada y nombre del turno vinculados a la asignación.',
                'Área y función' => 'Área asignada y función registrada para esa persona.',
                'Fecha de asignación' => 'Fecha del registro de asignación; no identifica por sí sola el día de la jornada.',
            ],
            'siguiente' => 'Busca una persona o área y abre su ficha. Por área agrupa los registros de la página actual.',
        ],
        'contactos' => [
            'campos' => [
                'Vínculos activos' => 'Relaciones activas del contacto con residentes; cada contacto se cuenta una sola vez.',
                'Responsable principal' => 'Indica si tiene esa condición en al menos un vínculo activo.',
                'Contacto de emergencia' => 'Indica si tiene esa condición en al menos un vínculo activo.',
            ],
            'siguiente' => 'Busca al contacto y abre su ficha para consultar el teléfono y los vínculos autorizados con cada residente.',
        ],
        'documentacion' => [
            'campos' => [
                'Vencimiento' => 'Fecha de vencimiento registrada para el documento.',
                'Validación' => 'Fecha de validación registrada; abrir la ficha no valida el documento.',
                'Por vencer' => 'Bandeja de documentos cuyo vencimiento está en los próximos 30 días.',
            ],
            'siguiente' => 'Elige una bandeja y abre la ficha para revisar el documento. Descarga el archivo cuando esté disponible.',
        ],
        'consentimientos' => [
            'campos' => [
                'Tipo' => 'Tipo de consentimiento consignado en el registro.',
                'Firmante' => 'Indica si la firma corresponde al residente o a un contacto responsable.',
                'Estado' => 'Situación registrada del consentimiento; la consulta no concede nuevas autorizaciones clínicas.',
            ],
            'siguiente' => 'Elige Activos, Revocados o Anulados y abre la ficha para revisar el residente, la fecha y el firmante.',
        ],
        'seguros' => [
            'campos' => [
                'Entidad' => 'Entidad del seguro registrado para el residente.',
                'Afiliación' => 'Número de afiliación consignado en el registro.',
                'Cobertura registrada' => 'Descripción guardada de la cobertura; no acredita por sí sola una prestación autorizada.',
            ],
            'siguiente' => 'Busca una entidad o residente y abre la ficha para consultar el plan, la cobertura y el teléfono de la entidad.',
        ],
        'actividades' => [
            'campos' => [
                'Fecha' => 'Fecha y hora registradas para la actividad.',
                'Cupo' => 'Cupo consignado en la actividad.',
                'Participantes' => 'Cantidad de registros de participación; el conteo no confirma asistencia realizada.',
            ],
            'siguiente' => 'Busca una actividad o lugar y abre la ficha para revisar horario, responsable, cupo y participantes.',
        ],
        'visitas' => [
            'campos' => [
                'Programada' => 'Fecha y hora previstas para la visita, cuando están registradas.',
                'Entrada' => 'Fecha y hora de ingreso registradas.',
                'Salida' => 'Fecha y hora de salida registradas.',
            ],
            'siguiente' => 'Elige Hoy, Programadas o Dentro y abre la ficha para distinguir la programación de la entrada y salida.',
        ],
        'alertas' => [
            'campos' => [
                'Prioridad' => 'Prioridad consignada en la alerta; esta ventana no calcula riesgo clínico.',
                'Responsable' => 'Persona responsable indicada en el registro, si existe.',
                'Trayectoria registrada' => 'Últimos 12 eventos de seguimiento, con fechas y cambios de estado.',
            ],
            'siguiente' => 'Elige una bandeja o prioridad y abre la ficha para consultar descripción, responsable y seguimiento.',
        ],
        'incidentes' => [
            'campos' => [
                'Gravedad registrada' => 'Gravedad consignada por el profesional; la interfaz no la determina.',
                'Fecha' => 'Fecha y hora consignadas en el registro del incidente.',
                'Medida inmediata' => 'Medida registrada por el profesional; se consulta en la ficha.',
            ],
            'siguiente' => 'Busca el tipo de incidente o lugar y abre la ficha para revisar la descripción, la medida y las observaciones.',
        ],
        'reportes' => [
            'campos' => [
                'Registros del periodo' => 'Conteos de procesos distintos; no se suman como personas únicas.',
                'Visitas' => 'Entradas registradas dentro del periodo; no incluye visitas solo programadas.',
                'Ocupación actual' => 'Alojamiento vigente al consultar; no depende del periodo elegido.',
            ],
            'siguiente' => 'Elige el periodo y selecciona un indicador para conocer cómo se cuenta, sin salir de Reportes. Ocupación muestra la situación actual.',
        ],
        default => null,
    };
@endphp

@if($guia)
<div x-show="ayuda" x-cloak x-transition.opacity class="rm-modal-shell rm-operation-help fixed inset-0 flex items-center justify-center p-4 is-open" x-trap.inert.noscroll="ayuda" @keydown.escape.window="if(ayuda) cerrarAyuda()" role="dialog" aria-modal="true" aria-labelledby="operacion-ayuda-titulo">
    <button class="rm-modal-shell__overlay" type="button" @click="cerrarAyuda()" tabindex="-1" aria-label="Cerrar ayuda"></button>
    <section class="rm-modal-panel relative"><header class="rm-modal-header"><div><p class="rm-residents-eyebrow">Guía de consulta</p><h2 id="operacion-ayuda-titulo">{{ $definicion['titulo'] }}</h2></div><button type="button" x-ref="cerrarAyuda" class="rm-btn-icon" aria-label="Cerrar ayuda" @click="cerrarAyuda()"><i class="ph-bold ph-x" aria-hidden="true"></i></button></header><div class="rm-modal-body"><dl class="rm-operation-guide__fields">@foreach($guia['campos'] as $etiqueta=>$explicacion)<div><dt>{{ $etiqueta }}</dt><dd>{{ $explicacion }}</dd></div>@endforeach</dl><p class="rm-operation-guide__next">{{ $guia['siguiente'] }}</p></div><footer class="rm-modal-footer"><button type="button" class="rm-btn-primary" @click="cerrarAyuda()">Entendido</button></footer></section>
</div>
@endif