<?php

require_once __DIR__.'/../../migrations/support/ConsolidacionTipada20261116.php';
require_once __DIR__.'/../../migrations/support/ProteccionHistoriaLaboral20261116.php';

return new class extends ConsolidacionTipada20261116
{
    use ProteccionHistoriaLaboral20261116;

    protected const DESTINO = 'historial_laboral_personal';

    protected const CODIGO = 'cod_historial_laboral';

    protected const DISCRIMINADOR = 'clase_acto';

    protected const FUENTES = [
        'movimientos_laborales_personal' => [
            'type' => 'LABORAL',
            'key' => 'cod_movimiento_laboral',
            'columns' => [
                'cod_movimiento_laboral' => ['character varying(20)', false, null],
                'cod_personal' => ['character varying(20)', false, null],
                'estado_anterior' => ['character varying(20)', false, null],
                'estado_nuevo' => ['character varying(20)', false, null],
                'fecha_hora_efecto' => ['timestamp without time zone', false, null],
                'fecha_hora_registro' => ['timestamp without time zone', false, null],
                'cod_usuario_registro' => ['character varying(20)', false, null],
                'motivo' => ['text', false, null],
            ],
            'foreign' => [['movimientos_laborales_personal_cod_personal_fkey', ['cod_personal'], 'personal', ['cod_personal']], ['movimientos_laborales_personal_cod_usuario_registro_fkey', ['cod_usuario_registro'], 'usuarios', ['cod_usuario']]],
            'checks' => [['movimientos_laborales_personal_check', 'CHECK ((((estado_nuevo) IN (\'ACTIVO\', \'INACTIVO\')) AND ((estado_nuevo) <> (estado_anterior))))'], ['movimientos_laborales_personal_check1', 'CHECK ((fecha_hora_efecto <= fecha_hora_registro))'], ['movimientos_laborales_personal_estado_anterior_check', 'CHECK (((estado_anterior) IN (\'ACTIVO\', \'INACTIVO\')))'], ['movimientos_laborales_personal_motivo_check', 'CHECK ((length(trim(motivo)) > 0))']],
            'indexes' => [['ger_mov_autor_idx', ['cod_usuario_registro'], false], ['ger_mov_per_idx', ['cod_personal', 'fecha_hora_efecto', 'cod_movimiento_laboral'], false]],
        ],
        'eventos_asignacion_personal' => [
            'type' => 'ASIGNACION',
            'key' => 'cod_evento_asignacion',
            'columns' => [
                'cod_evento_asignacion' => ['character varying(20)', false, null],
                'cod_asignacion_personal' => ['character varying(20)', false, null],
                'numero_evento' => ['integer', false, null],
                'cod_evento_anterior' => ['character varying(20)', true, null],
                'tipo_evento' => ['character varying(20)', false, null],
                'estado_anterior' => ['character varying(20)', true, null],
                'estado_nuevo' => ['character varying(20)', false, null],
                'fecha_hora_efecto' => ['timestamp without time zone', false, null],
                'fecha_hora_registro' => ['timestamp without time zone', false, null],
                'cod_usuario_registro' => ['character varying(20)', false, null],
                'motivo' => ['text', false, null],
                'cod_usuario_afectado_aviso' => ['character varying(20)', true, null],
                'estado_aviso_planificacion' => ['character varying(16)', true, null],
                'numero_intentos_aviso' => ['integer', true, null],
                'fecha_hora_ultimo_intento_aviso' => ['timestamp without time zone', true, null],
                'fecha_hora_disponible_aviso' => ['timestamp without time zone', true, null],
                'categoria_fallo_aviso' => ['character varying(40)', true, null],
            ],
            'foreign' => [['eventos_asignacion_personal_cod_asignacion_personal_cod_ev_fkey', ['cod_asignacion_personal', 'cod_evento_anterior'], 'eventos_asignacion_personal', ['cod_asignacion_personal', 'cod_evento_asignacion']], ['eventos_asignacion_personal_cod_asignacion_personal_fkey', ['cod_asignacion_personal'], 'asignaciones_jornada', ['cod_asignacion_personal']], ['eventos_asignacion_personal_cod_usuario_afectado_aviso_fkey', ['cod_usuario_afectado_aviso'], 'usuarios', ['cod_usuario']], ['eventos_asignacion_personal_cod_usuario_registro_fkey', ['cod_usuario_registro'], 'usuarios', ['cod_usuario']]],
            'checks' => [['eap_aviso_coherencia', 'CHECK ((((estado_aviso_planificacion IS NULL) AND (cod_usuario_afectado_aviso IS NULL) AND (numero_intentos_aviso IS NULL) AND (fecha_hora_ultimo_intento_aviso IS NULL) AND (fecha_hora_disponible_aviso IS NULL) AND (categoria_fallo_aviso IS NULL)) OR ((estado_aviso_planificacion IS NOT NULL) AND (numero_intentos_aviso IS NOT NULL) AND ((((estado_aviso_planificacion) = \'PENDIENTE\') AND (numero_intentos_aviso = 0) AND (fecha_hora_ultimo_intento_aviso IS NULL) AND (fecha_hora_disponible_aviso IS NULL) AND (categoria_fallo_aviso IS NULL)) OR (((estado_aviso_planificacion) = \'DISPONIBLE\') AND (numero_intentos_aviso > 0) AND (fecha_hora_ultimo_intento_aviso IS NOT NULL) AND (fecha_hora_disponible_aviso IS NOT NULL) AND (fecha_hora_disponible_aviso = fecha_hora_ultimo_intento_aviso) AND (categoria_fallo_aviso IS NULL)) OR (((estado_aviso_planificacion) = \'FALLIDO\') AND (numero_intentos_aviso > 0) AND (fecha_hora_ultimo_intento_aviso IS NOT NULL) AND (fecha_hora_disponible_aviso IS NULL) AND (categoria_fallo_aviso IS NOT NULL) AND ((categoria_fallo_aviso) = \'FALLO_DISPONIBILIZACION\'))))))'], ['eap_aviso_fecha', 'CHECK (((fecha_hora_ultimo_intento_aviso IS NULL) OR (fecha_hora_ultimo_intento_aviso >= fecha_hora_registro)))'], ['eventos_asignacion_personal_check', 'CHECK ((((numero_evento = 1) AND (cod_evento_anterior IS NULL)) OR ((numero_evento > 1) AND (cod_evento_anterior IS NOT NULL))))'], ['eventos_asignacion_personal_motivo_check', 'CHECK ((length(trim(motivo)) > 0))'], ['eventos_asignacion_personal_numero_evento_check', 'CHECK ((numero_evento > 0))'], ['eventos_asignacion_personal_tipo_evento_check', 'CHECK (((tipo_evento) IN (\'ALTA\', \'FINALIZACION\', \'CANCELACION\', \'RECTIFICACION\')))']],
            'indexes' => [['eap_aviso_afectado_idx', ['cod_usuario_afectado_aviso', 'fecha_hora_registro'], false], ['eap_aviso_estado_idx', ['estado_aviso_planificacion', 'fecha_hora_registro'], false], ['eventos_asignacion_personal_cod_asignacion_personal_cod_eve_key', ['cod_asignacion_personal', 'cod_evento_asignacion'], true], ['eventos_asignacion_personal_cod_asignacion_personal_numero__key', ['cod_asignacion_personal', 'numero_evento'], true], ['ger_asig_evento_actor_idx', ['cod_usuario_registro', 'fecha_hora_registro'], false]],
        ],
    ];
};
