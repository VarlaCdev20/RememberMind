<?php

require_once __DIR__.'/support/ConsolidacionTipada20261116.php';

return new class extends ConsolidacionTipada20261116
{
    protected const DESTINO = 'registros_textuales_clinicos';

    protected const CODIGO = 'cod_registro_textual';

    protected const FUENTES = [
        'notas_clinicas' => [
            'type' => 'NOTA',
            'key' => 'cod_nota',
            'columns' => [
                'cod_nota' => ['character varying(20)', false, null],
                'cod_atencion' => ['character varying(20)', false, null],
                'cod_residente' => ['character varying(20)', false, null],
                'cod_personal' => ['character varying(20)', false, null],
                'cod_nota_anterior' => ['character varying(20)', true, null],
                'tipo_nota' => ['character varying(50)', false, null],
                'contenido' => ['text', false, null],
                'fecha_hora' => ['timestamp(0)', false, null],
                'motivo_correccion' => ['text', true, null],
                'estado' => ['character varying(20)', false, null],
            ],
            'foreign' => [['fk_nota_atencion_residente', ['cod_atencion', 'cod_residente'], 'atenciones', ['cod_atencion', 'cod_residente']], ['notas_clinicas_cod_atencion_foreign', ['cod_atencion'], 'atenciones', ['cod_atencion']], ['notas_clinicas_cod_nota_anterior_foreign', ['cod_nota_anterior'], 'notas_clinicas', ['cod_nota']], ['notas_clinicas_cod_personal_foreign', ['cod_personal'], 'personal', ['cod_personal']], ['notas_clinicas_cod_residente_foreign', ['cod_residente'], 'residentes', ['cod_residente']]],
            'checks' => [],
            'indexes' => [],
        ],
        'indicaciones_clinicas' => [
            'type' => 'INDICACION',
            'key' => 'cod_indicacion',
            'columns' => [
                'cod_indicacion' => ['character varying(20)', false, null],
                'cod_residente' => ['character varying(20)', false, null],
                'cod_atencion' => ['character varying(20)', false, null],
                'cod_personal' => ['character varying(20)', false, null],
                'tipo_indicacion' => ['character varying(40)', false, null],
                'descripcion' => ['text', false, null],
                'prioridad' => ['character varying(20)', true, null],
                'fecha_hora' => ['timestamp(0)', false, null],
                'estado' => ['character varying(20)', false, null],
                'observacion' => ['text', true, null],
            ],
            'foreign' => [['fk_indicacion_atencion_residente', ['cod_atencion', 'cod_residente'], 'atenciones', ['cod_atencion', 'cod_residente']], ['indicaciones_clinicas_cod_atencion_foreign', ['cod_atencion'], 'atenciones', ['cod_atencion']], ['indicaciones_clinicas_cod_personal_foreign', ['cod_personal'], 'personal', ['cod_personal']], ['indicaciones_clinicas_cod_residente_foreign', ['cod_residente'], 'residentes', ['cod_residente']]],
            'checks' => [],
            'indexes' => [],
        ],
    ];
};
