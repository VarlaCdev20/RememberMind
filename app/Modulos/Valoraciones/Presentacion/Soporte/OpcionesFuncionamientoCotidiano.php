<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Presentacion\Soporte;

/** Etiquetas de los catálogos existentes; no agrega opciones ni interpretaciones. */
final class OpcionesFuncionamientoCotidiano
{
    public const CAMPOS = ['actividad' => 'Actividad cotidiana', 'oportunidad' => 'Oportunidad de evaluación',
        'desempeno' => 'Desempeño registrado', 'tipo_ayuda' => 'Ayuda recibida', 'causa_ayuda' => 'Motivo de la ayuda',
        'nivel_previo' => 'Funcionamiento previo', 'fuente' => 'Fuente de información'];

    public const OPCIONES = [
        'actividad' => ['MEDICACION' => 'Manejo de la medicación', 'PERTENENCIAS' => 'Cuidado de pertenencias', 'RUTINAS' => 'Rutinas cotidianas', 'CITAS' => 'Citas y compromisos', 'COMUNICACION' => 'Comunicación', 'FINANZAS' => 'Manejo del dinero', 'COMPRAS' => 'Compras', 'TRANSPORTE' => 'Uso del transporte'],
        'oportunidad' => ['OBSERVADA' => 'Actividad observada', 'REFERIDA' => 'Información referida', 'SIN_OPORTUNIDAD' => 'Sin oportunidad de realizarla', 'NO_HABITUAL' => 'No era una actividad habitual'],
        'desempeno' => ['INDEPENDIENTE' => 'La realiza de forma independiente', 'CON_APOYO' => 'La realiza con apoyo', 'NO_EVALUABLE' => 'No se pudo evaluar'],
        'tipo_ayuda' => ['NINGUNA' => 'Sin ayuda', 'RECORDATORIO' => 'Recordatorio', 'SUPERVISION' => 'Supervisión', 'SUSTITUCION' => 'Otra persona la realiza', 'NO_APLICA' => 'No aplica'],
        'causa_ayuda' => ['NINGUNA' => 'Sin motivo de ayuda registrado', 'COGNITIVA' => 'Dificultad cognitiva referida u observada', 'FISICA' => 'Dificultad física', 'SENSORIAL' => 'Dificultad sensorial', 'INSTITUCIONAL' => 'Organización de la residencia', 'MIXTA' => 'Varios motivos', 'NO_DETERMINADA' => 'Motivo no determinado', 'NO_APLICA' => 'No aplica'],
        'nivel_previo' => ['INDEPENDIENTE' => 'La realizaba de forma independiente', 'CON_APOYO' => 'La realizaba con apoyo', 'NO_HABITUAL' => 'No era habitual', 'DESCONOCIDO' => 'No se conoce'],
        'fuente' => ['OBSERVACION_DIRECTA' => 'Observación directa del profesional', 'RESIDENTE' => 'Relato del residente', 'FAMILIAR' => 'Relato de un familiar', 'EQUIPO_CUIDADOS' => 'Información del equipo de cuidados'],
    ];
}
