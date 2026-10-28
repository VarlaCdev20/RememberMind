<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\g08;

/**
 * Lista de comprobación G08 orientada a ingeniería y revisión bibliográfica.
 * Incluso una ficha aparentemente completa es SOLO una declaración del autor
 * de la captura: NO se convierte en evidencia clínica o estado DCL A/P.
 */
final class AuditorAplicabilidadG08Investigacion
{
    public const VERSION = 'G08-CHECK-20261017-R1';

    private const REQUISITOS = [
        'edicion_identificada' => 'EDICION_INSTRUMENTAL_NO_VERIFICADA',
        'administracion_identificada' => 'PROTOCOLO_ADMINISTRACION_NO_VERIFICADO',
        'norma_tabla_identificada' => 'TABLA_O_ALGORITMO_NORMATIVO_NO_VERIFICADO',
        'pais_norma_verificado' => 'NORMA_POR_PAIS_NO_VERIFICADA',
        'edad_compatible_verificada' => 'COBERTURA_ETARIA_NO_VERIFICADA',
        'escolaridad_compatible_verificada' => 'COBERTURA_ESCOLARIDAD_NO_VERIFICADA',
        'idioma_equivalente_verificado' => 'EQUIVALENCIA_IDIOMA_NO_VERIFICADA',
        'poblacion_institucional_justificada' => 'POBLACION_GERIATRICA_NO_JUSTIFICADA',
        'licencia_y_derechos_verificados' => 'DERECHOS_DE_USO_NO_VERIFICADOS',
        'metodo_calculo_documentado' => 'INTERPRETACION_Y_CALCULO_NO_DOCUMENTADOS',
        'confusores_revisados' => 'CONFUSORES_NO_REVISADOS',
        'revision_profesional_documentada' => 'REVISION_PROFESIONAL_NO_DOCUMENTADA',
    ];

    private const POR_PRUEBA = [
        'BTA' => ['audicion_verificada' => 'AUDICION_NO_VERIFICADA'],
        'STROOP' => ['lectura_color_verificados' => 'LECTURA_O_COLOR_NO_VERIFICADOS'],
        'TMT' => ['vision_motricidad_verificadas' => 'VISION_O_MOTRICIDAD_NO_VERIFICADAS'],
        'MWCST' => ['atribucion_constructo_verificada' => 'ATRIBUCION_A_CONSTRUCTO_NO_VERIFICADA'],
        'BNT_ESTANDAR' => ['forma_estandar_verificada' => 'FORMA_ESTANDAR_NO_VERIFICADA'],
        'FLUIDEZ_VERBAL' => ['consigna_duracion_verificadas' => 'CONSIGNA_O_DURACION_NO_VERIFICADAS'],
        'REY_OSTERRIETH' => ['fase_copia_verificada' => 'FASE_COPIA_NO_VERIFICADA'],
    ];

    private const TAREAS_POR_INSTRUMENTO = [
        'RUFF_2_7' => ['RUFF_SOSTENIDA', 'RUFF_SELECTIVA'],
        'BTA' => ['BTA_AUDIO'],
        'STROOP' => ['STROOP_INTERFERENCIA'],
        'TMT' => ['TMT_B'],
        'MWCST' => ['MWCST_FLEXIBILIDAD', 'MWCST_RAZONAMIENTO'],
        'WAIS_IV' => ['DIGITOS_INVERSOS', 'DIGITOS_SECUENCIACION'],
        'TORRE_LONDRES' => ['TORRE_PLANIFICACION'],
        'BNT_ESTANDAR' => ['BNT_FORMA_ESTANDAR'],
        'FLUIDEZ_VERBAL' => ['FLUIDEZ_FONEMICA', 'FLUIDEZ_SEMANTICA'],
        'TOKEN_TEST' => ['TOKEN_COMPRENSION'],
        'WAB_R' => ['WAB_REPETICION', 'WAB_DISCURSO'],
        'VOSP' => ['VOSP_ESPACIAL'],
        'JLO' => ['JLO_ORIENTACION'],
        'REY_OSTERRIETH' => ['ROCF_COPIA'],
    ];

    public function auditar(string $componente, array $solicitud): array
    {
        $ficha = CatalogoExpedientesG08Investigacion::obtener($componente);
        $instrumento = $solicitud['instrumento'] ?? null;
        $tarea = $solicitud['tarea'] ?? null;
        $motivos = [];
        if (($solicitud['uso'] ?? null) !== 'REVISION_DOCUMENTAL_TEST_ONLY') {
            $motivos[] = 'USO_FUERA_DEL_EXPEDIENTE_DE_INVESTIGACION';
        }
        if (! is_string($instrumento) || ! in_array($instrumento, $ficha['instrumentos'], true)) {
            $motivos[] = 'INSTRUMENTO_AJENO_A_COMPONENTE';
        }
        if (! is_string($tarea) || ! in_array($tarea, $ficha['tareas'], true)
            || ! in_array($tarea, self::TAREAS_POR_INSTRUMENTO[$instrumento] ?? [], true)) {
            $motivos[] = 'TAREA_NO_CORRESPONDE_A_INSTRUMENTO_Y_COMPONENTE';
        }
        $referencia = $solicitud['pmid_norma'] ?? null;
        $refs = [];
        foreach ($ficha['bibliografia'] as $ref) {
            $refs[] = $ref['pmid'];
        }
        if (! is_string($referencia) || ! in_array($referencia, $refs, true)) {
            $motivos[] = 'PUBLICACION_NO_SELECCIONADA_O_NO_ATRIBUIBLE';
        }
        foreach (self::REQUISITOS as $campo => $motivo) {
            if (($solicitud[$campo] ?? null) !== true) {
                $motivos[] = $motivo;
            }
        }
        foreach (self::POR_PRUEBA[$instrumento] ?? [] as $campo => $motivo) {
            if (($solicitud[$campo] ?? null) !== true) {
                $motivos[] = $motivo;
            }
        }
        $edad = $solicitud['edad_anos'] ?? null;
        if ($instrumento === 'RUFF_2_7' && is_int($edad) && $edad > 70) {
            $motivos[] = 'RUFF_EDAD_MAYOR_70_EXIGE_EVIDENCIA_NORMATIVA_ESPECIFICA';
        }
        if ($componente === 'EJE-S05') {
            $motivos[] = 'M_WCST_NO_ACREDITA_POR_SI_SOLO_RAZONAMIENTO_GENERAL';
        }
        $motivos = array_values(array_unique($motivos));
        sort($motivos, SORT_STRING);

        return [
            'uso' => 'AUDITORIA_G08_NO_CLINICA',
            'version_auditoria' => self::VERSION,
            'version_expediente' => CatalogoExpedientesG08Investigacion::VERSION,
            'componente' => $componente,
            'estado_expediente' => $ficha['estado'],
            'bibliografia_candidata' => $ficha['bibliografia'],
            'alertas_documentales' => $ficha['alertas'],
            'faltantes_declarados' => $motivos,
            'declaracion_completa' => $motivos === [],
            'estado_interpretacion' => 'NO_INTERPRETABLE_POR_G08',
            'puede_emitir_A_o_P' => false,
            'perfil_normativo_activado' => false,
            'evidencia_clinica' => null,
            'razon_bloqueo' => 'SIN_PERFIL_G08_APROBADO: el checklist no verifica por sí mismo manual, baremo, permiso ni dictamen.',
        ];
    }
}
