<?php

require_once __DIR__.'/support/ConsolidacionTipada20261116.php';

return new class extends ConsolidacionTipada20261116
{
    protected const DESTINO = 'valoraciones_profesionales';

    protected const CODIGO = 'cod_valoracion_profesional';

    protected const FUENTES = [
        'valoraciones_profesionales' => [
            'type' => null,
            'key' => 'cod_valoracion_profesional',
            'columns' => [
                'cod_valoracion_profesional' => ['character varying(20)', false, null],
                'cod_residente' => ['character varying(20)', false, null],
                'cod_personal' => ['character varying(20)', false, null],
                'cod_atencion' => ['character varying(20)', false, null],
                'fecha_hora' => ['timestamp(0)', false, null],
                'estado_animo' => ['character varying(50)', true, null],
                'afecto' => ['character varying(50)', true, null],
                'ansiedad' => ['character varying(50)', true, null],
                'apatia' => ['character varying(50)', true, null],
                'percepcion' => ['text', true, null],
                'conducta' => ['text', true, null],
                'comunicacion' => ['text', true, null],
                'interaccion_social' => ['text', true, null],
                'impresion_cognitiva' => ['text', true, null],
                'conclusion' => ['text', true, null],
                'recomendacion' => ['text', true, null],
                'estado' => ['character varying(20)', false, null],
                'cod_medicion' => ['character varying(20)', true, null],
                'estado_nutricional' => ['character varying(60)', true, null],
                'apetito' => ['character varying(40)', true, null],
                'deglucion' => ['character varying(40)', true, null],
                'riesgo_desnutricion' => ['character varying(40)', true, null],
                'necesidad_asistencia' => ['character varying(40)', true, null],
                'requerimiento_hidrico' => ['numeric(10,2)', true, null],
                'restricciones_alimentarias' => ['text', true, null],
                'marcha' => ['character varying(40)', true, null],
                'equilibrio' => ['character varying(40)', true, null],
                'traslado' => ['character varying(40)', true, null],
                'fuerza_funcional' => ['character varying(40)', true, null],
                'resistencia' => ['character varying(40)', true, null],
                'alimentacion_autonoma' => ['character varying(30)', true, null],
                'bano_autonomo' => ['character varying(30)', true, null],
                'vestido_autonomo' => ['character varying(30)', true, null],
                'higiene_autonoma' => ['character varying(30)', true, null],
                'continencia' => ['character varying(30)', true, null],
                'movilidad_autonoma' => ['character varying(30)', true, null],
                'necesita_supervision' => ['boolean', true, null],
                'nivel_dependencia' => ['character varying(40)', true, null],
                'tipo_evento' => ['character varying(15)', false, null],
                'cod_valoracion_psicologica_ref' => ['character varying(20)', true, '
CASE
    WHEN ((tipo_evento) = \'PSICOLOGICA\') THEN cod_valoracion_profesional
    ELSE NULL
END'],
            ],
            'foreign' => [['fk_valoracion_atencion_residente', ['cod_atencion', 'cod_residente'], 'atenciones', ['cod_atencion', 'cod_residente']], ['valoraciones_profesionales_copia_cod_atencion_fkey', ['cod_atencion'], 'atenciones', ['cod_atencion']], ['valoraciones_profesionales_copia_cod_medicion_fkey', ['cod_medicion'], 'mediciones_clinicas', ['cod_medicion_antropometrica']], ['valoraciones_profesionales_copia_cod_personal_fkey', ['cod_personal'], 'personal', ['cod_personal']], ['valoraciones_profesionales_copia_cod_residente_fkey', ['cod_residente'], 'residentes', ['cod_residente']]],
            'checks' => [['ck_valoracion_tipo', 'CHECK (((((tipo_evento) = \'PSICOLOGICA\') AND (cod_medicion IS NULL) AND (estado_nutricional IS NULL) AND (apetito IS NULL) AND (deglucion IS NULL) AND (riesgo_desnutricion IS NULL) AND (necesidad_asistencia IS NULL) AND (requerimiento_hidrico IS NULL) AND (restricciones_alimentarias IS NULL) AND (marcha IS NULL) AND (equilibrio IS NULL) AND (traslado IS NULL) AND (fuerza_funcional IS NULL) AND (resistencia IS NULL) AND (alimentacion_autonoma IS NULL) AND (bano_autonomo IS NULL) AND (vestido_autonomo IS NULL) AND (higiene_autonoma IS NULL) AND (continencia IS NULL) AND (movilidad_autonoma IS NULL) AND (necesita_supervision IS NULL) AND (nivel_dependencia IS NULL)) OR (((tipo_evento) = \'NUTRICIONAL\') AND (estado_animo IS NULL) AND (afecto IS NULL) AND (ansiedad IS NULL) AND (apatia IS NULL) AND (percepcion IS NULL) AND (conducta IS NULL) AND (comunicacion IS NULL) AND (interaccion_social IS NULL) AND (impresion_cognitiva IS NULL) AND (marcha IS NULL) AND (equilibrio IS NULL) AND (traslado IS NULL) AND (fuerza_funcional IS NULL) AND (resistencia IS NULL) AND (alimentacion_autonoma IS NULL) AND (bano_autonomo IS NULL) AND (vestido_autonomo IS NULL) AND (higiene_autonoma IS NULL) AND (continencia IS NULL) AND (movilidad_autonoma IS NULL) AND (necesita_supervision IS NULL) AND (nivel_dependencia IS NULL)) OR (((tipo_evento) = \'FUNCIONAL\') AND (estado_animo IS NULL) AND (afecto IS NULL) AND (ansiedad IS NULL) AND (apatia IS NULL) AND (percepcion IS NULL) AND (conducta IS NULL) AND (comunicacion IS NULL) AND (interaccion_social IS NULL) AND (impresion_cognitiva IS NULL) AND (cod_medicion IS NULL) AND (estado_nutricional IS NULL) AND (apetito IS NULL) AND (deglucion IS NULL) AND (riesgo_desnutricion IS NULL) AND (necesidad_asistencia IS NULL) AND (requerimiento_hidrico IS NULL) AND (restricciones_alimentarias IS NULL))))']],
            'indexes' => [['valoraciones_atencion_i', ['cod_atencion', 'cod_residente'], false], ['valoraciones_medicion_i', ['cod_medicion'], false], ['valoraciones_personal_i', ['cod_personal'], false], ['valoraciones_profesionales_co_cod_valoracion_psicologica_re_key', ['cod_valoracion_psicologica_ref'], true], ['valoraciones_tipo_residente_fecha_i', ['tipo_evento', 'cod_residente', 'fecha_hora'], false]],
        ],
        'seguimientos_pedagogicos' => [
            'type' => 'PEDAGOGICA',
            'key' => 'cod_seguimiento_pedagogico',
            'columns' => [
                'cod_seguimiento_pedagogico' => ['character varying(20)', false, null],
                'cod_residente' => ['character varying(20)', false, null],
                'cod_personal' => ['character varying(20)', false, null],
                'cod_actividad' => ['character varying(20)', true, null],
                'fecha_hora' => ['timestamp(0)', false, null],
                'atencion' => ['character varying(40)', true, null],
                'comprension_instrucciones' => ['character varying(40)', true, null],
                'ejecucion_tarea' => ['character varying(40)', true, null],
                'reconocimiento' => ['character varying(40)', true, null],
                'orientacion' => ['character varying(40)', true, null],
                'participacion' => ['character varying(40)', true, null],
                'interaccion' => ['character varying(40)', true, null],
                'cambio_desempeno' => ['boolean', true, null],
                'observacion' => ['text', true, null],
                'estado' => ['character varying(20)', false, null],
            ],
            'foreign' => [['seguimientos_pedagogicos_cod_actividad_foreign', ['cod_actividad'], 'actividades', ['cod_actividad']], ['seguimientos_pedagogicos_cod_personal_foreign', ['cod_personal'], 'personal', ['cod_personal']], ['seguimientos_pedagogicos_cod_residente_foreign', ['cod_residente'], 'residentes', ['cod_residente']]],
            'checks' => [],
            'indexes' => [],
        ],
    ];
};
