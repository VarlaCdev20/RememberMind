<?php

require_once __DIR__.'/support/ConsolidacionTipada20261116.php';

return new class extends ConsolidacionTipada20261116
{
    protected const DESTINO = 'mediciones_clinicas';

    protected const CODIGO = 'cod_registro_medicion';

    protected const FUENTES = [
        'mediciones_clinicas' => [
            'type' => null,
            'key' => 'cod_registro_medicion',
            'columns' => [
                'cod_registro_medicion' => ['character varying(20)', false, null],
                'cod_residente' => ['character varying(20)', false, null],
                'cod_personal' => ['character varying(20)', false, null],
                'cod_jornada' => ['character varying(20)', true, null],
                'cod_atencion' => ['character varying(20)', true, null],
                'fecha_hora' => ['timestamp(0)', false, null],
                'presion_sistolica' => ['numeric(5,2)', true, null],
                'presion_diastolica' => ['numeric(5,2)', true, null],
                'frecuencia_cardiaca' => ['numeric(6,2)', true, null],
                'frecuencia_respiratoria' => ['numeric(6,2)', true, null],
                'temperatura' => ['numeric(4,1)', true, null],
                'saturacion_oxigeno' => ['numeric(5,2)', true, null],
                'glucemia' => ['numeric(8,2)', true, null],
                'estado' => ['character varying(20)', true, null],
                'observacion' => ['text', true, null],
                'peso' => ['numeric(6,2)', true, null],
                'talla' => ['numeric(5,2)', true, null],
                'imc' => ['numeric(5,2)', true, null],
                'perimetro_braquial' => ['numeric(6,2)', true, null],
                'perimetro_pantorrilla' => ['numeric(6,2)', true, null],
                'tipo_evento' => ['character varying(15)', false, null],
                'cod_medicion_antropometrica' => ['character varying(20)', true, '
CASE
    WHEN ((tipo_evento) = \'ANTROPOMETRIA\') THEN cod_registro_medicion
    ELSE NULL
END'],
            ],
            'foreign' => [['fk_medicion_atencion_residente', ['cod_atencion', 'cod_residente'], 'atenciones', ['cod_atencion', 'cod_residente']], ['mediciones_clinicas_copia_cod_atencion_fkey', ['cod_atencion'], 'atenciones', ['cod_atencion']], ['mediciones_clinicas_copia_cod_jornada_fkey', ['cod_jornada'], 'jornadas', ['cod_jornada']], ['mediciones_clinicas_copia_cod_personal_fkey', ['cod_personal'], 'personal', ['cod_personal']], ['mediciones_clinicas_copia_cod_residente_fkey', ['cod_residente'], 'residentes', ['cod_residente']]],
            'checks' => [['ck_medicion_tipo', 'CHECK (((((tipo_evento) = \'SIGNOS\') AND (estado IS NOT NULL) AND (peso IS NULL) AND (talla IS NULL) AND (imc IS NULL) AND (perimetro_braquial IS NULL) AND (perimetro_pantorrilla IS NULL)) OR (((tipo_evento) = \'ANTROPOMETRIA\') AND (cod_jornada IS NULL) AND (cod_atencion IS NULL) AND (presion_sistolica IS NULL) AND (presion_diastolica IS NULL) AND (frecuencia_cardiaca IS NULL) AND (frecuencia_respiratoria IS NULL) AND (temperatura IS NULL) AND (saturacion_oxigeno IS NULL) AND (glucemia IS NULL) AND (estado IS NULL))))'], ['ck_med_ant_talla', 'CHECK (((talla IS NULL) OR ((talla >= 50.0) AND (talla <= 240.0))))']],
            'indexes' => [['mediciones_atencion_i', ['cod_atencion', 'cod_residente'], false], ['mediciones_clinicas_copia_cod_medicion_antropometrica_key', ['cod_medicion_antropometrica'], true], ['mediciones_jornada_i', ['cod_jornada'], false], ['mediciones_personal_i', ['cod_personal'], false], ['mediciones_tipo_residente_fecha_i', ['tipo_evento', 'cod_residente', 'fecha_hora'], false]],
        ],
        'valoraciones_dolor' => [
            'type' => 'DOLOR',
            'key' => 'cod_valoracion_dolor',
            'columns' => [
                'cod_valoracion_dolor' => ['character varying(20)', false, null],
                'cod_residente' => ['character varying(20)', false, null],
                'cod_personal' => ['character varying(20)', false, null],
                'cod_atencion' => ['character varying(20)', true, null],
                'fecha_hora' => ['timestamp(0)', false, null],
                'intensidad' => ['smallint', true, null],
                'ubicacion' => ['character varying(120)', true, null],
                'tipo_dolor' => ['character varying(60)', true, null],
                'duracion' => ['character varying(80)', true, null],
                'desencadenante' => ['text', true, null],
                'intervencion' => ['text', true, null],
                'respuesta' => ['text', true, null],
                'estado' => ['character varying(20)', false, null],
                'cod_valoracion_origen' => ['character varying(20)', true, null],
                'frecuencia' => ['character varying(40)', true, null],
                'factores_alivio' => ['text', true, null],
            ],
            'foreign' => [['fk_dolor_atencion_residente', ['cod_atencion', 'cod_residente'], 'atenciones', ['cod_atencion', 'cod_residente']], ['fk_dolor_origen_residente_v2', ['cod_valoracion_origen', 'cod_residente'], 'valoraciones_dolor', ['cod_valoracion_dolor', 'cod_residente']], ['fk_dolor_origen_v2', ['cod_valoracion_origen'], 'valoraciones_dolor', ['cod_valoracion_dolor']], ['valoraciones_dolor_cod_atencion_foreign', ['cod_atencion'], 'atenciones', ['cod_atencion']], ['valoraciones_dolor_cod_personal_foreign', ['cod_personal'], 'personal', ['cod_personal']], ['valoraciones_dolor_cod_residente_foreign', ['cod_residente'], 'residentes', ['cod_residente']]],
            'checks' => [],
            'indexes' => [['idx_dolor_origen_v2', ['cod_valoracion_origen'], false], ['idx_dolor_residente_fecha_v2', ['cod_residente', 'fecha_hora'], false], ['uq_dolor_codigo_residente_v2', ['cod_valoracion_dolor', 'cod_residente'], true]],
        ],
    ];
};
