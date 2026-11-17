<?php

require_once __DIR__.'/support/ConsolidacionTipada20261116.php';

return new class extends ConsolidacionTipada20261116
{
    protected const DESTINO = 'registros_textuales_clinicos';

    protected const CODIGO = 'cod_registro_textual';

    protected const FUENTES = [
        'registros_textuales_clinicos' => [
            'type' => null,
            'key' => 'cod_registro_textual',
            'columns' => [
                'cod_registro_textual' => ['character varying(20)', false, null],
                'tipo_evento' => ['character varying(20)', false, null],
                'cod_atencion' => ['character varying(20)', true, null],
                'cod_residente' => ['character varying(20)', true, null],
                'cod_personal' => ['character varying(20)', true, null],
                'cod_nota_anterior' => ['character varying(20)', true, null],
                'tipo_nota' => ['character varying(50)', true, null],
                'contenido' => ['text', true, null],
                'fecha_hora' => ['timestamp(0)', true, null],
                'motivo_correccion' => ['text', true, null],
                'estado' => ['character varying(20)', true, null],
                'tipo_indicacion' => ['character varying(40)', true, null],
                'descripcion' => ['text', true, null],
                'prioridad' => ['character varying(20)', true, null],
                'observacion' => ['text', true, null],
                'cod_nota' => ['character varying(20)', true, 'CASE
    WHEN ((tipo_evento) = \'NOTA\') THEN cod_registro_textual
    ELSE NULL
END'],
                'cod_indicacion' => ['character varying(20)', true, 'CASE
    WHEN ((tipo_evento) = \'INDICACION\') THEN cod_registro_textual
    ELSE NULL
END'],
            ],
            'foreign' => [['registros_textuales_clinicos_cod_atencion_cod_residente_fkey', ['cod_atencion', 'cod_residente'], 'atenciones', ['cod_atencion', 'cod_residente']], ['registros_textuales_clinicos_cod_atencion_fkey', ['cod_atencion'], 'atenciones', ['cod_atencion']], ['registros_textuales_clinicos_cod_nota_anterior_fkey', ['cod_nota_anterior'], 'registros_textuales_clinicos', ['cod_nota']], ['registros_textuales_clinicos_cod_personal_fkey', ['cod_personal'], 'personal', ['cod_personal']], ['registros_textuales_clinicos_cod_residente_fkey', ['cod_residente'], 'residentes', ['cod_residente']]],
            'checks' => [['registros_textuales_clinicos_check', 'CHECK ((((((tipo_evento) = \'NOTA\') AND (cod_atencion IS NOT NULL) AND ((cod_atencion IS NULL) OR (length((cod_atencion)) <= 20)) AND (cod_residente IS NOT NULL) AND ((cod_residente IS NULL) OR (length((cod_residente)) <= 20)) AND (cod_personal IS NOT NULL) AND ((cod_personal IS NULL) OR (length((cod_personal)) <= 20)) AND ((cod_nota_anterior IS NULL) OR (length((cod_nota_anterior)) <= 20)) AND (tipo_nota IS NOT NULL) AND ((tipo_nota IS NULL) OR (length((tipo_nota)) <= 50)) AND (contenido IS NOT NULL) AND (fecha_hora IS NOT NULL) AND (estado IS NOT NULL) AND ((estado IS NULL) OR (length((estado)) <= 20)) AND (tipo_indicacion IS NULL) AND (descripcion IS NULL) AND (prioridad IS NULL) AND (observacion IS NULL)) OR (((tipo_evento) = \'INDICACION\') AND (cod_residente IS NOT NULL) AND ((cod_residente IS NULL) OR (length((cod_residente)) <= 20)) AND (cod_atencion IS NOT NULL) AND ((cod_atencion IS NULL) OR (length((cod_atencion)) <= 20)) AND (cod_personal IS NOT NULL) AND ((cod_personal IS NULL) OR (length((cod_personal)) <= 20)) AND (tipo_indicacion IS NOT NULL) AND ((tipo_indicacion IS NULL) OR (length((tipo_indicacion)) <= 40)) AND (descripcion IS NOT NULL) AND ((prioridad IS NULL) OR (length((prioridad)) <= 20)) AND (fecha_hora IS NOT NULL) AND (estado IS NOT NULL) AND ((estado IS NULL) OR (length((estado)) <= 20)) AND (cod_nota_anterior IS NULL) AND (tipo_nota IS NULL) AND (contenido IS NULL) AND (motivo_correccion IS NULL))) IS TRUE))']],
            'indexes' => [['registros_textuales_clinicos_cod_indicacion_key', ['cod_indicacion'], true], ['registros_textuales_clinicos_cod_nota_key', ['cod_nota'], true], ['u16_0a1723f7548aa6f30a12', ['cod_atencion', 'cod_residente'], false], ['u16_0e8ee9c486a4552a9735', ['cod_residente'], false], ['u16_99842ddd41e68e77cd80', ['cod_residente', 'tipo_evento'], false], ['u16_9b16c414cedefbbee12a', ['cod_atencion'], false], ['u16_bd1018fddfa24b159630', ['cod_personal', 'tipo_evento'], false], ['u16_c1975cdfe0910d069188', ['cod_personal'], false], ['u16_d8b376a392ccf8be68de', ['cod_nota_anterior'], false]],
        ],
        'pases_turno' => [
            'type' => 'PASE',
            'key' => 'cod_pase',
            'columns' => [
                'cod_pase' => ['character varying(20)', false, null],
                'cod_residente' => ['character varying(20)', false, null],
                'cod_jornada_saliente' => ['character varying(20)', false, null],
                'cod_jornada_entrante' => ['character varying(20)', false, null],
                'cod_personal_saliente' => ['character varying(20)', false, null],
                'cod_personal_entrante' => ['character varying(20)', true, null],
                'fecha_hora' => ['timestamp(0)', false, null],
                'estado_general' => ['text', true, null],
                'resumen' => ['text', false, null],
                'pendientes' => ['text', true, null],
                'vigilancia' => ['text', true, null],
                'recomendacion' => ['text', true, null],
                'estado' => ['character varying(20)', false, null],
                'fecha_hora_recepcion' => ['timestamp(0)', true, null],
                'observacion_recepcion' => ['text', true, null],
            ],
            'foreign' => [['pases_turno_cod_jornada_entrante_foreign', ['cod_jornada_entrante'], 'jornadas', ['cod_jornada']], ['pases_turno_cod_jornada_saliente_foreign', ['cod_jornada_saliente'], 'jornadas', ['cod_jornada']], ['pases_turno_cod_personal_entrante_foreign', ['cod_personal_entrante'], 'personal', ['cod_personal']], ['pases_turno_cod_personal_saliente_foreign', ['cod_personal_saliente'], 'personal', ['cod_personal']], ['pases_turno_cod_residente_foreign', ['cod_residente'], 'residentes', ['cod_residente']]],
            'checks' => [],
            'indexes' => [],
        ],
    ];
};
