<?php
declare(strict_types=1);

namespace MotorExperto\investigacion\g08;

/**
 * Especifica la captura de MEDIDAS CRUDAS de 12 tareas incluidas en siete
 * artículos normativos de Bolivia. NO calcula percentiles ni interpreta casos.
 * Una tarea en este mapa NO significa autorización de aplicación.
 */
final class ContratoCapturaCrudaG08Investigacion
{
    public const VERSION = 'G08-CAPTURA-BO-20261017-01';

    private const METODOS = [
        'BTA_AUDIO' => [
            'unidad' => 'RESPUESTAS_CORRECTAS',
            'campos' => ['forma_N_aciertos'=>'ENTERO_NO_NEGATIVO','forma_L_aciertos'=>'ENTERO_NO_NEGATIVO'],
            'control' => 'SUMA_N_Y_L_SOLO_CONSISTENCIA_DE_CAPTURA',
            'pendiente' => 'EDICION_Y_ESTIMULOS_AUDITIVOS_INSTITUCIONALES',
        ],
        'STROOP_INTERFERENCIA' => [
            'unidad' => 'INDICE_INTERFERENCIA',
            'campos' => ['palabras_correctas'=>'ENTERO_NO_NEGATIVO','colores_correctos'=>'ENTERO_NO_NEGATIVO','palabra_color_correctas'=>'ENTERO_NO_NEGATIVO'],
            'control' => 'FORMULA_INDICE_INTERFERENCIA_PENDIENTE_DE_ADOPCION',
            'pendiente' => 'VERSION_Y_FORMULA_INTERFERENCIA_IDENTICAS_AL_ESTUDIO',
        ],
        'TMT_B' => [
            'unidad' => 'SEGUNDOS',
            'campos' => ['segundos_parte_B'=>'NUMERO_NO_NEGATIVO','errores_observados'=>'ENTERO_NO_NEGATIVO'],
            'control' => 'POLITICA_DE_ERRORES_Y_TIEMPO_LIMITE_PENDIENTE',
            'pendiente' => 'PROTOCOLO_EXACTO_DE_INSTRUCCIONES_Y_CORRECCION_DE_ERRORES',
        ],
        'MWCST_CATEGORIAS' => [
            'unidad' => 'CATEGORIAS',
            'campos' => ['categorias_completadas'=>'ENTERO_NO_NEGATIVO'],
            'control' => 'CATEGORIAS_NO_EQUIVALEN_A_ERRORES',
            'pendiente' => 'EDICION_MWCST_Y_REGLAS_DE_COMPLETITUD',
        ],
        'MWCST_PERSEVERACIONES' => [
            'unidad' => 'ERRORES_PERSEVERATIVOS',
            'campos' => ['errores_perseverativos'=>'ENTERO_NO_NEGATIVO'],
            'control' => 'PERSEVERACIONES_NO_EQUIVALEN_A_TOTAL_ERRORES',
            'pendiente' => 'EDICION_Y_CODIFICACION_DE_PERSEVERACIONES',
        ],
        'MWCST_ERRORES_TOTALES' => [
            'unidad' => 'ERRORES_TOTALES',
            'campos' => ['errores_totales'=>'ENTERO_NO_NEGATIVO'],
            'control' => 'ERRORES_TOTALES_NO_EQUIVALEN_A_CATEGORIAS',
            'pendiente' => 'EDICION_MWCST_Y_PROCEDIMIENTO_DE_RECUENTO',
        ],
        'BNT_FORMA_ESTANDAR' => [
            'unidad' => 'DENOMINACIONES_CORRECTAS',
            'campos' => ['denominaciones_correctas'=>'ENTERO_NO_NEGATIVO'],
            'control' => 'FORMA_ESTANDAR_NO_EQUIVALE_A_BNT_CORTO',
            'pendiente' => 'EDICION_E_ESTIMULOS_COMPLETOS_Y_CRITERIOS_DE_AYUDA',
        ],
        'FLUIDEZ_LETRA_F' => [
            'unidad' => 'RESPUESTAS_VALIDAS',
            'campos' => ['respuestas_validas'=>'ENTERO_NO_NEGATIVO','segundos_administrados'=>'NUMERO_POSITIVO'],
            'control' => 'LETRA_F_NO_INTERCAMBIABLE',
            'pendiente' => 'PROTOCOLO_TIEMPO_Y_REGLAS_DE_EXCLUSION_DE_RESPUESTAS',
        ],
        'FLUIDEZ_LETRA_A' => [
            'unidad' => 'RESPUESTAS_VALIDAS',
            'campos' => ['respuestas_validas'=>'ENTERO_NO_NEGATIVO','segundos_administrados'=>'NUMERO_POSITIVO'],
            'control' => 'LETRA_A_NO_INTERCAMBIABLE',
            'pendiente' => 'PROTOCOLO_TIEMPO_Y_REGLAS_DE_EXCLUSION_DE_RESPUESTAS',
        ],
        'FLUIDEZ_LETRA_S' => [
            'unidad' => 'RESPUESTAS_VALIDAS',
            'campos' => ['respuestas_validas'=>'ENTERO_NO_NEGATIVO','segundos_administrados'=>'NUMERO_POSITIVO'],
            'control' => 'LETRA_S_NO_INTERCAMBIABLE',
            'pendiente' => 'PROTOCOLO_TIEMPO_Y_REGLAS_DE_EXCLUSION_DE_RESPUESTAS',
        ],
        'FLUIDEZ_ANIMALES' => [
            'unidad' => 'RESPUESTAS_VALIDAS',
            'campos' => ['respuestas_validas'=>'ENTERO_NO_NEGATIVO','segundos_administrados'=>'NUMERO_POSITIVO'],
            'control' => 'ANIMALES_NO_EQUIVALE_A_FLUIDEZ_FONEMICA',
            'pendiente' => 'PROTOCOLO_CONSIGNA_TIEMPO_Y_REGLAS_DE_EXCLUSION',
        ],
        'ROCF_COPIA' => [
            'unidad' => 'PUNTAJE_COPIA_CRUDO',
            'campos' => ['puntaje_copia'=>'NUMERO_NO_NEGATIVO','elementos_revisados'=>'ENTERO_NO_NEGATIVO'],
            'control' => 'COPIA_NO_EQUIVALE_A_RECUERDO',
            'pendiente' => 'EDICION_FIGURA_A_RUBRICA_18_ELEMENTOS_Y_DERECHOS',
        ],
    ];

    public static function todos(): array
    {
        $localizadores = LocalizadoresAnexosBoliviaG08Investigacion::todos();
        $resultado = [];
        foreach ($localizadores as $componente=>$tareas) {
            foreach ($tareas as $tarea=>$meta) {
                $resultado[$componente][$tarea] = [
                    'componente'=>$componente,
                    'tarea'=>$tarea,
                    'instrumento'=>$meta['instrumento'],
                    'metrica'=>$meta['metrica'],
                    'unidad_esperada'=>self::METODOS[$tarea]['unidad'],
                    'campos_crudos'=>self::METODOS[$tarea]['campos'],
                    'verificacion'=>self::METODOS[$tarea]['control'],
                    'protocolo_pendiente'=>self::METODOS[$tarea]['pendiente'],
                    'tabla_bolivia'=>$meta['tabla'],
                    'doi'=>$meta['doi'],
                    'version'=>self::VERSION,
                    'algoritmo_normativo'=>'NO_IMPLEMENTADO',
                    'perfil_clinico_aprobado'=>false,
                ];
            }
        }
        return $resultado;
    }
}
