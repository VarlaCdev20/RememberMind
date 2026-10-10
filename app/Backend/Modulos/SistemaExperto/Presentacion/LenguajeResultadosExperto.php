<?php

namespace App\Backend\Modulos\SistemaExperto\Presentacion;

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

    public static function evaluabilidad(?string $codigo): string
    {
        return match ($codigo) {
            'EV-CM-0' => 'Información insuficiente para evaluar el área',
            'EV-CM-1' => 'Información preliminar que requiere ampliación',
            'EV-CM-2' => 'Información suficiente para evaluación integrada',
            default => 'Estado de evaluabilidad sin contrato de presentación',
        };
    }

    public static function presentacion(?string $codigo): array
    {
        return match ($codigo) {
            'DIFICULTAD_EVIDENCIADA' => ['tono' => 'difficulty', 'resumen' => 'Se identifican hallazgos compatibles con dificultad.'],
            'SIN_DIFICULTAD_EVIDENCIADA' => ['tono' => 'success', 'resumen' => 'No se identificaron hallazgos de dificultad en los aspectos evaluados.'],
            'HALLAZGOS_MIXTOS' => ['tono' => 'warning', 'resumen' => 'Se identificaron hallazgos diferentes que requieren interpretación clínica.'],
            default => ['tono' => 'neutral', 'resumen' => null],
        };
    }

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
