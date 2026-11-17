<?php

require_once __DIR__.'/support/ConsolidacionTipada20261116.php';

return new class extends ConsolidacionTipada20261116
{
    protected const DESTINO = 'documentos';

    protected const CODIGO = 'cod_documento';

    protected const FUENTES = [
        'documentos' => [
            'type' => 'ADMINISTRATIVO',
            'key' => 'cod_documento',
            'columns' => [
                'cod_documento' => ['character varying(20)', false, null],
                'cod_preadmision' => ['character varying(20)', true, null],
                'cod_residente' => ['character varying(20)', true, null],
                'cod_usuario' => ['character varying(20)', true, null],
                'cod_contacto' => ['character varying(20)', true, null],
                'cod_documento_anterior' => ['character varying(20)', true, null],
                'cod_usuario_validacion' => ['character varying(20)', true, null],
                'tipo_documento' => ['character varying(60)', false, null],
                'nombre' => ['character varying(160)', false, null],
                'ruta_archivo' => ['character varying(500)', false, null],
                'tipo_archivo' => ['character varying(80)', false, null],
                'hash_archivo' => ['character varying(128)', false, null],
                'fecha_vencimiento' => ['date', true, null],
                'fecha_validacion' => ['timestamp(0)', true, null],
                'estado' => ['character varying(20)', false, null],
                'observacion' => ['text', true, null],
                'cod_usuario_carga' => ['character varying(20)', true, null],
                'fecha_hora_carga' => ['timestamp(0)', true, null],
                'fecha_emision' => ['date', true, null],
                'cod_usuario_anulacion' => ['character varying(20)', true, null],
                'fecha_hora_anulacion' => ['timestamp(0)', true, null],
                'motivo_anulacion' => ['text', true, null],
            ],
            'foreign' => [['documentos_cod_contacto_foreign', ['cod_contacto'], 'contactos', ['cod_contacto']], ['documentos_cod_documento_anterior_foreign', ['cod_documento_anterior'], 'documentos', ['cod_documento']], ['documentos_cod_preadmision_foreign', ['cod_preadmision'], 'preadmisiones', ['cod_preadmision']], ['documentos_cod_residente_foreign', ['cod_residente'], 'residentes', ['cod_residente']], ['documentos_cod_usuario_foreign', ['cod_usuario'], 'usuarios', ['cod_usuario']], ['documentos_cod_usuario_validacion_foreign', ['cod_usuario_validacion'], 'usuarios', ['cod_usuario']], ['ger_doc_anula_fk', ['cod_usuario_anulacion'], 'usuarios', ['cod_usuario']], ['ger_doc_carga_fk', ['cod_usuario_carga'], 'usuarios', ['cod_usuario']]],
            'checks' => [['ger_doc_carga_chk', 'CHECK ((((cod_usuario_carga IS NULL) AND (fecha_hora_carga IS NULL)) OR ((cod_usuario_carga IS NOT NULL) AND (fecha_hora_carga IS NOT NULL))))'], ['ger_doc_anula_chk', 'CHECK ((((cod_usuario_anulacion IS NULL) AND (fecha_hora_anulacion IS NULL) AND (motivo_anulacion IS NULL)) OR ((cod_usuario_anulacion IS NOT NULL) AND (fecha_hora_anulacion IS NOT NULL) AND (motivo_anulacion IS NOT NULL) AND (length(TRIM(BOTH FROM motivo_anulacion)) > 0) AND ((estado) IN (\'ANULADO\', \'ANULADA\')))))']],
            'indexes' => [['ger_doc_anula_idx', ['cod_usuario_anulacion'], false], ['ger_doc_carga_idx', ['cod_usuario_carga'], false]],
        ],
        'documentos_clinicos' => [
            'type' => 'CLINICO',
            'key' => 'cod_documento_clinico',
            'columns' => [
                'cod_documento_clinico' => ['character varying(20)', false, null],
                'cod_residente' => ['character varying(20)', false, null],
                'cod_estudio' => ['character varying(20)', true, null],
                'cod_atencion' => ['character varying(20)', true, null],
                'cod_personal' => ['character varying(20)', true, null],
                'tipo_documento' => ['character varying(60)', false, null],
                'titulo' => ['character varying(180)', false, null],
                'descripcion' => ['text', true, null],
                'ruta_archivo' => ['character varying(500)', false, null],
                'formato' => ['character varying(30)', false, null],
                'tamano_bytes' => ['bigint', true, null],
                'hash_archivo' => ['character varying(128)', false, null],
                'fecha_hora' => ['timestamp(0)', false, null],
                'origen' => ['character varying(20)', true, null],
                'estado' => ['character varying(20)', false, null],
                'observacion' => ['text', true, null],
                'cod_herida' => ['character varying(20)', true, null],
                'cod_curacion' => ['character varying(20)', true, null],
            ],
            'foreign' => [['documentos_clinicos_cod_atencion_foreign', ['cod_atencion'], 'atenciones', ['cod_atencion']], ['documentos_clinicos_cod_curacion_foreign', ['cod_curacion'], 'curaciones_herida', ['cod_curacion']], ['documentos_clinicos_cod_estudio_foreign', ['cod_estudio'], 'estudios_clinicos', ['cod_estudio']], ['documentos_clinicos_cod_herida_foreign', ['cod_herida'], 'heridas', ['cod_herida']], ['documentos_clinicos_cod_personal_foreign', ['cod_personal'], 'personal', ['cod_personal']], ['documentos_clinicos_cod_residente_foreign', ['cod_residente'], 'residentes', ['cod_residente']], ['fk_documento_clinico_atencion_residente', ['cod_atencion', 'cod_residente'], 'atenciones', ['cod_atencion', 'cod_residente']]],
            'checks' => [],
            'indexes' => [['documentos_clinicos_cod_curacion_index', ['cod_curacion'], false], ['documentos_clinicos_cod_herida_index', ['cod_herida'], false]],
        ],
    ];
};
