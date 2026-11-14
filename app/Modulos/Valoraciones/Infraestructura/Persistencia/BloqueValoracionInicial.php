<?php

namespace App\Modulos\Valoraciones\Infraestructura\Persistencia;

/** Correspondencia física del bloque singular de valoración inicial. */
final class BloqueValoracionInicial
{
    public const ORIGEN = 'valoraciones_enfermeria_preadmision';

    public const TABLA = 'preadmisiones';

    public const CODIGO = 'cod_valoracion_enfermeria';

    public const TIPOS = [
        'cod_valoracion_enfermeria' => 'varchar(20)',
        'cod_usuario_registro' => 'varchar(20)',
        'fecha_hora' => 'timestamp(0)',
        'estado_general' => 'varchar(40)',
        'nivel_conciencia' => 'varchar(40)',
        'orientacion_persona' => 'varchar(40)',
        'orientacion_tiempo' => 'varchar(40)',
        'orientacion_espacio' => 'varchar(40)',
        'comunicacion' => 'varchar(80)',
        'hay_dolor' => 'boolean',
        'intensidad_dolor' => 'smallint',
        'ubicacion_dolor' => 'varchar(255)',
        'movilidad' => 'varchar(80)',
        'apoyo_movilidad' => 'varchar(160)',
        'riesgo_caida' => 'varchar(40)',
        'piel_estado' => 'varchar(80)',
        'hay_heridas' => 'boolean',
        'ubicacion_heridas' => 'varchar(255)',
        'higiene_ingreso' => 'varchar(80)',
        'continencia_basica' => 'varchar(80)',
        'alimentacion_aparente' => 'varchar(80)',
        'pa_sistolica' => 'decimal(6, 2)',
        'pa_diastolica' => 'decimal(6, 2)',
        'frecuencia_cardiaca' => 'decimal(6, 2)',
        'frecuencia_respiratoria' => 'decimal(6, 2)',
        'temperatura' => 'decimal(5, 2)',
        'saturacion_oxigeno' => 'decimal(5, 2)',
        'peso' => 'decimal(6, 2)',
        'talla' => 'decimal(6, 2)',
        'antecedentes_relevantes' => 'text',
        'medicacion_referida' => 'text',
        'alergias_referidas' => 'text',
        'dependencia_funcional' => 'varchar(60)',
        'riesgo_nutricional' => 'varchar(60)',
        'riesgo_cognitivo' => 'varchar(60)',
        'tipo_evaluacion_cognitiva' => 'varchar(80)',
        'necesidad_apoyo_inmediato' => 'text',
        'prioridad_sugerida' => 'varchar(20)',
        'confirmacion_documentacion' => 'boolean',
        'comentarios_adicionales' => 'text',
        'recomendacion_enfermeria' => 'text',
        'cod_personal_valorador' => 'varchar(20)',
    ];

    public static function columna(string $campo): string
    {
        return $campo === self::CODIGO ? $campo : 'valoracion_'.$campo;
    }

    public static function columnas(): array
    {
        return array_map(self::columna(...), array_keys(self::TIPOS));
    }

    public static function atributos(array $datos): array
    {
        $atributos = [];
        foreach (array_intersect_key($datos, self::TIPOS) as $campo => $valor) {
            $atributos[self::columna($campo)] = $valor;
        }

        return $atributos;
    }
}
