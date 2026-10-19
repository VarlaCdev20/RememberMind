<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\g08;

use DomainException;

/**
 * Inventario G08 documentado, NO un catálogo de instrumentos administrables.
 * Fuentes: G08 auditoría 2026-10-17 y resúmenes PubMed originales.
 * La palabra candidata significa EXISTENCIA BIBLIOGRÁFICA, nunca baremo elegido.
 */
final class CatalogoExpedientesG08Investigacion
{
    public const VERSION = 'G08-AUDIT-20261017-R1';

    private const FUENTES = [
        'BTA' => ['pmid' => '26639928', 'doi' => '10.3233/NRE-151283', 'publicacion' => 2015],
        'STROOP' => ['pmid' => '26639926', 'doi' => '10.3233/NRE-151281', 'publicacion' => 2015],
        'TMT' => ['pmid' => '26639932', 'doi' => '10.3233/NRE-151284', 'publicacion' => 2015],
        'MWCST' => ['pmid' => '26639931', 'doi' => '10.3233/NRE-151280', 'publicacion' => 2015],
        'BNT' => ['pmid' => '26639925', 'doi' => '10.3233/NRE-151278', 'publicacion' => 2015],
        'FLUIDEZ' => ['pmid' => '26639930', 'doi' => '10.3233/NRE-151279', 'publicacion' => 2015],
        'ROCF' => ['pmid' => '26639929', 'doi' => '10.3233/NRE-151285', 'publicacion' => 2015],
    ];

    private const COMPONENTES = [
        'ATE-S01' => ['constructo' => 'atencion_sostenida', 'instrumentos' => ['RUFF_2_7'], 'tareas' => ['RUFF_SOSTENIDA'],
            'fuentes' => [], 'estado' => 'NO_RESUELTO', 'alertas' => ['COBERTURA_ETARIA_GERIATRICA_NO_ACREDITADA', 'PROTOCOLO_Y_EDICION_PENDIENTES']],
        'ATE-S02' => ['constructo' => 'atencion_selectiva', 'instrumentos' => ['RUFF_2_7'], 'tareas' => ['RUFF_SELECTIVA'],
            'fuentes' => [], 'estado' => 'NO_RESUELTO', 'alertas' => ['CONTROL_DISTRACTORES_PENDIENTE', 'EDICION_Y_NORMA_PENDIENTES']],
        'ATE-S03' => ['constructo' => 'atencion_dividida_auditiva', 'instrumentos' => ['BTA'], 'tareas' => ['BTA_AUDIO'],
            'fuentes' => ['BTA'], 'estado' => 'FUENTE_NORMATIVA_CANDIDATA', 'alertas' => ['EDICION_Y_ESTIMULOS_AUDITIVOS_PENDIENTES', 'APLICABILIDAD_INSTITUCIONAL_NO_ACREDITADA']],
        'EJE-S01' => ['constructo' => 'inhibicion', 'instrumentos' => ['STROOP'], 'tareas' => ['STROOP_INTERFERENCIA'],
            'fuentes' => ['STROOP'], 'estado' => 'FUENTE_NORMATIVA_CANDIDATA', 'alertas' => ['CONFUNDENTES_LECTURA_COLOR_PENDIENTES', 'VERSION_Y_CONDICION_INTERFERENCIA_PENDIENTES']],
        'EJE-S02' => ['constructo' => 'flexibilidad_cognitiva', 'instrumentos' => ['TMT', 'MWCST'], 'tareas' => ['TMT_B', 'MWCST_FLEXIBILIDAD'],
            'fuentes' => ['TMT', 'MWCST'], 'estado' => 'FUENTE_NORMATIVA_CANDIDATA', 'alertas' => ['TMT_A_NO_SUSTITUYE_TMT_B', 'TMT_B_Y_MWCST_NO_EQUIVALENTES', 'VELOCIDAD_MOTRICIDAD_VISION_PENDIENTES']],
        'EJE-S03' => ['constructo' => 'manipulacion_actualizacion', 'instrumentos' => ['WAIS_IV'], 'tareas' => ['DIGITOS_INVERSOS', 'DIGITOS_SECUENCIACION'],
            'fuentes' => [], 'estado' => 'NO_RESUELTO', 'alertas' => ['INVERTIR_NO_EQUIVALE_AUTOMATICAMENTE_A_ACTUALIZAR', 'EDICION_Y_NORMA_PENDIENTES']],
        'EJE-S04' => ['constructo' => 'planificacion', 'instrumentos' => ['TORRE_LONDRES'], 'tareas' => ['TORRE_PLANIFICACION'],
            'fuentes' => [], 'estado' => 'NO_RESUELTO', 'alertas' => ['VARIANTES_TORRE_NO_EQUIVALENTES', 'NORMAS_PENDIENTES']],
        'EJE-S05' => ['constructo' => 'razonamiento_abstraccion', 'instrumentos' => ['MWCST'], 'tareas' => ['MWCST_RAZONAMIENTO'],
            'fuentes' => ['MWCST'], 'estado' => 'NO_RESUELTO', 'alertas' => ['ATRIBUCION_A_RAZONAMIENTO_GENERAL_NO_ACREDITADA']],
        'LEN-S01' => ['constructo' => 'denominacion', 'instrumentos' => ['BNT_ESTANDAR'], 'tareas' => ['BNT_FORMA_ESTANDAR'],
            'fuentes' => ['BNT'], 'estado' => 'FUENTE_NORMATIVA_CANDIDATA', 'alertas' => ['BNT_15_ITEMS_O_BNT2_NO_EQUIVALENTES', 'EDICION_ESTANDAR_PENDIENTE']],
        'LEN-S02' => ['constructo' => 'fluidez_verbal', 'instrumentos' => ['FLUIDEZ_VERBAL'], 'tareas' => ['FLUIDEZ_FONEMICA', 'FLUIDEZ_SEMANTICA'],
            'fuentes' => ['FLUIDEZ'], 'estado' => 'FUENTE_NORMATIVA_CANDIDATA', 'alertas' => ['LETRA_CATEGORIA_DURACION_DISTINTAS', 'IDIOMA_ESCOLARIDAD_PENDIENTES']],
        'LEN-S03' => ['constructo' => 'comprension_oral', 'instrumentos' => ['TOKEN_TEST'], 'tareas' => ['TOKEN_COMPRENSION'],
            'fuentes' => [], 'estado' => 'NO_RESUELTO', 'alertas' => ['NORMAS_ESPANOLAS_NO_HOMOLOGADAS_BOLIVIA', 'AUDICION_PENDIENTE']],
        'LEN-S04' => ['constructo' => 'repeticion', 'instrumentos' => ['WAB_R'], 'tareas' => ['WAB_REPETICION'],
            'fuentes' => [], 'estado' => 'NO_RESUELTO', 'alertas' => ['DERECHOS_Y_ADAPTACION_PENDIENTES', 'AUDICION_HABLA_PENDIENTES']],
        'LEN-S05' => ['constructo' => 'discurso', 'instrumentos' => ['WAB_R'], 'tareas' => ['WAB_DISCURSO'],
            'fuentes' => [], 'estado' => 'NO_RESUELTO', 'alertas' => ['TAREA_DISCURSO_PENDIENTE', 'NLP_LIBRE_NO_AUTORIZADO']],
        'VIS-S01' => ['constructo' => 'relaciones_espaciales', 'instrumentos' => ['VOSP', 'JLO'], 'tareas' => ['VOSP_ESPACIAL', 'JLO_ORIENTACION'],
            'fuentes' => [], 'estado' => 'NO_RESUELTO', 'alertas' => ['NORMAS_ESPAÑA_NO_HOMOLOGADAS', 'EDICION_Y_VISION_PENDIENTES']],
        'VIS-S02' => ['constructo' => 'visuoconstruccion', 'instrumentos' => ['REY_OSTERRIETH'], 'tareas' => ['ROCF_COPIA'],
            'fuentes' => ['ROCF'], 'estado' => 'FUENTE_NORMATIVA_CANDIDATA', 'alertas' => ['ROCF_RECUERDO_NO_MIDE_COPIA', 'NORMAS_Y_EDICION_PENDIENTES']],
    ];

    public static function todos(): array
    {
        $resultado = [];
        foreach (self::COMPONENTES as $id => $fila) {
            $bibliografia = [];
            foreach ($fila['fuentes'] as $ref) {
                $bibliografia[$ref] = self::FUENTES[$ref];
            }
            $resultado[$id] = ['codigo' => $id, 'bibliografia' => $bibliografia,
                'uso_autorizado' => false, 'perfiles_normativos_aprobados' => 0, ...$fila];
        }

        return $resultado;
    }

    public static function obtener(string $codigo): array
    {
        return self::todos()[$codigo] ?? throw new DomainException('G08: componente inexistente en el expediente: '.$codigo);
    }
}
