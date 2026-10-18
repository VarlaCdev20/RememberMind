<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

/** Traduce códigos conservados; las etiquetas nunca autorizan publicación ni ejecutan reglas. */
final class LenguajeResultadosExperto
{
    public const CRITERIOS = [
        'COG-MEM' => ['nombre' => 'Aprendizaje y memoria', 'icono' => 'ph-brain'],
        'COG-ATE' => ['nombre' => 'Atención', 'icono' => 'ph-target'],
        'COG-EJE' => ['nombre' => 'Funciones ejecutivas', 'icono' => 'ph-gear'],
        'COG-LEN' => ['nombre' => 'Lenguaje', 'icono' => 'ph-chat-circle-text'],
        'COG-VIS' => ['nombre' => 'Habilidades visuoespaciales y visuoconstructivas', 'icono' => 'ph-eye'],
    ];

    public const RESULTADOS = [
        'DIFICULTAD_EVIDENCIADA' => ['titulo' => 'Dificultad evidenciada', 'interpretacion' => 'La evaluación integrada aportó evidencia válida e interpretable compatible con dificultad en aprendizaje y memoria, dentro del alcance examinado. Es información de apoyo para la valoración profesional y no establece por sí sola un diagnóstico clínico.'],
        'SIN_DIFICULTAD_EVIDENCIADA' => ['titulo' => 'Sin dificultad evidenciada', 'interpretacion' => 'No se identificaron hallazgos de dificultad en los componentes efectivamente evaluados. Este resultado no equivale a normalidad cognitiva global ni descarta alteraciones en áreas no examinadas.'],
        'HALLAZGOS_MIXTOS' => ['titulo' => 'Hallazgos mixtos', 'interpretacion' => 'La evaluación identificó evidencias válidas que sustentan hallazgos diferentes. Estas diferencias requieren considerar el alcance de cada fuente y su contexto clínico.'],
    ];

    /** Describe la evaluabilidad original de memoria sin convertirla en un resultado. */
    public static function evaluabilidad(?string $codigo): string
    {
        return match ($codigo) {
            'EV-CM-0' => 'Información insuficiente para evaluar el área',
            'EV-CM-1' => 'Información preliminar que requiere ampliación',
            'EV-CM-2' => 'Información suficiente para evaluación integrada',
            default => 'Estado de evaluabilidad sin contrato de presentación',
        };
    }

    /** Ofrece semántica visual del resultado ya validado por el lector. */
    public static function presentacion(?string $codigo): array
    {
        return match ($codigo) {
            'DIFICULTAD_EVIDENCIADA' => ['tono' => 'difficulty', 'resumen' => 'Se identifican hallazgos compatibles con dificultad.'],
            'SIN_DIFICULTAD_EVIDENCIADA' => ['tono' => 'success', 'resumen' => 'No se identificaron hallazgos de dificultad en los aspectos evaluados.'],
            'HALLAZGOS_MIXTOS' => ['tono' => 'warning', 'resumen' => 'Se identificaron hallazgos diferentes que requieren interpretación clínica.'],
            default => ['tono' => 'neutral', 'resumen' => null],
        };
    }

    /** Frases médicas para COG-MEM; solo el presentador decide si el resultado es publicable. */
    public static function conclusionMemoria(?string $codigo): ?string
    {
        return match ($codigo) {
            'DIFICULTAD_EVIDENCIADA' => 'Se identificaron dificultades en los componentes evaluados de memoria.',
            'SIN_DIFICULTAD_EVIDENCIADA' => 'No se encontraron dificultades en los componentes efectivamente evaluados.',
            'HALLAZGOS_MIXTOS' => 'Se encontraron resultados diferentes entre las tareas evaluadas, por lo que se requiere interpretar cada hallazgo en su contexto.',
            default => null,
        };
    }

    /**
     * Catálogo descriptivo G09-EXP consultado el 17/10/2026 en pr15-ci-utf8.
     * No acredita soporte, evaluabilidad o autorización clínica. No se integra
     * con resultados D-137 ni se convierte en los estados objetivos A/P de Petersen.
     */
    public static function estadoG09(string $codigo): ?array
    {
        return match ($codigo) {
            'SIN_EVIDENCIA_ADMISIBLE' => ['estado' => 'Sin evidencia admisible', 'alcance' => 'La prueba experimental no dispone de evidencia admisible para emitir un hallazgo.'],
            'SOLO_SENAL_CONTEXTO' => ['estado' => 'Solo señal de contexto', 'alcance' => 'La prueba conserva información preliminar de contexto; no acredita un hallazgo del área.'],
            'EVALUABLE_SIN_SOPORTE' => ['estado' => 'Evaluable sin soporte positivo', 'alcance' => 'La tarea experimental es interpretable, pero no conserva soporte de una regla positiva. No equivale a preservación cognitiva.'],
            'HALLAZGO_ACOTADO' => ['estado' => 'Hallazgo experimental acotado', 'alcance' => 'El hallazgo se limita al componente y a los soportes de la prueba técnica. No constituye un diagnóstico.'],
            'MULTIPLES_HALLAZGOS_PENDIENTES_DE_RESOLUCION' => ['estado' => 'Múltiples hallazgos pendientes de resolución', 'alcance' => 'Se conservan varias rutas experimentales; ninguna se prioriza automáticamente ni se resume como un resultado unitario.'],
            'EVIDENCIA_CONTRADICTORIA' => ['estado' => 'Evidencia contradictoria', 'alcance' => 'Las fuentes de la prueba experimental no permiten una conclusión consistente. Se conserva la contradicción sin elegir un resultado.'],
            default => null,
        };
    }

    /** Traduce metadatos técnicos conocidos y conserva literales desconocidos solo en el detalle de fuente. */
    public static function etiqueta(string $codigo): string
    {
        return match ($codigo) {
            'ADMISIBLE' => 'Admisible',
            'ADMISIBLE_CON_ADVERTENCIA' => 'Admisible con advertencia',
            'NO_ADMISIBLE' => 'No admisible',
            'MAPEADO' => 'Mapeada',
            'INSTRUMENTAL' => 'Instrumental',
            'CONTEXTUAL' => 'Contextual',
            'CORROBORATIVA' => 'Corroborativa',
            'PRUEBA_TECNICA' => 'Prueba técnica',
            default => $codigo,
        };
    }

    /** Describe la procedencia de una evidencia sin promoverla a soporte directo. */
    public static function evidencia(?string $codigo, ?string $nombre): string
    {
        return match ($codigo) {
            'EV-MEM-01', 'VAR-MEM-OBS-01' => 'Observación de memoria reciente',
            'EV-MEM-02', 'VAR-MEM-OBS-02' => 'Observación de memoria remota',
            'EV-MEM-03', 'VAR-MEM-COR-01' => 'Registro de repetición de preguntas',
            'EV-MEM-04', 'VAR-COG-MULTI-01' => 'Registro de olvido de indicaciones',
            'EV-MEM-05', 'VAR-LON-META-01' => 'Cambio cognitivo reportado',
            'EV-MEM-06', 'VAR-MEM-INST-01' => 'Evaluación instrumental de aprendizaje y memoria',
            default => $nombre ?: 'Evidencia sin descripción disponible',
        };
    }
}
