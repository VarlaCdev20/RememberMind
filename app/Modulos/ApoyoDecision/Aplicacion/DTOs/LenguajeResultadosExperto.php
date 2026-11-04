<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

use MotorExperto\conservacion\IdentificadoresHistoricos;

/** Traduce códigos conservados; las etiquetas nunca autorizan publicación ni ejecutan reglas. */
final class LenguajeResultadosExperto
{
    /** Presenta versiones antiguas sin cambiar sus identificadores guardados. */
    public static function version(?string $codigo): string
    {
        if ($codigo === null || $codigo === '') {
            return 'No disponible';
        }
        $prefijo = explode('-', IdentificadoresHistoricos::PERFIL_COGNITIVO, 2)[0].'-';

        return match ($codigo) {
            IdentificadoresHistoricos::PERFIL_COGNITIVO => 'Perfil cognitivo inicial',
            IdentificadoresHistoricos::PETERSEN => 'Patrones de Petersen',
            IdentificadoresHistoricos::DOCUMENTAL_INICIAL => 'Base documental anterior',
            IdentificadoresHistoricos::CONSERVACION => 'Formato de conservación',
            default => str_starts_with($codigo, $prefijo) ? 'EXPERTO-'.substr($codigo, strlen($prefijo)) : $codigo,
        };
    }

    /** Actualiza la etiqueta de conceptos antiguos; conserva el registro original. */
    public static function referenciaDocumental(string $texto): string
    {
        return preg_match('/^Concepto de la red documental .+; su pertenencia no acredita un hallazgo\.$/u', $texto)
            ? 'Concepto de la base cognitiva; su pertenencia no acredita un hallazgo.' : $texto;
    }

    /** Explica faltantes del cálculo sin adjudicar valores ni significado clínico. */
    public static function condicionInstrumental(string $codigo): string
    {
        [$motivo, $entrada] = array_pad(explode(':', $codigo, 2), 2, null);
        $campo = $entrada !== null ? ' ('.str_replace('_', ' ', $entrada).')' : '';

        return match ($motivo) {
            'SIN_PERFIL_EXPLICITO' => 'El instrumento y su versión todavía no tienen una vinculación con un método de cálculo.',
            'SIN_CAPTURA_VINCULADA_AL_COMPONENTE' => 'No hay una aplicación original vinculada a este componente.',
            'SIN_CONTRATO_DE_INTERPRETACION_CLINICA' => 'La interpretación clínica conjunta de este componente sigue pendiente de un método definido.',
            'SIN_SELECTOR' => 'Falta identificar la pregunta original que aporta esta entrada'.$campo.'.',
            'RESPUESTA_AUSENTE_O_INCOMPATIBLE' => 'Falta la respuesta original o su referencia es incompatible'.$campo.'.',
            'MEDICION_AUSENTE_O_TIPO_INVALIDO' => 'El dato no está registrado en el tipo requerido'.$campo.'.',
            'APLICABILIDAD_NO_ACREDITADA' => 'Falta documentar una condición de aplicación, o la registrada no es compatible'.$campo.'.',
            'IDENTIDAD_NO_VERIFICADA' => 'No se ha verificado la identidad requerida por el método'.$campo.'.',
            'EDAD_FUERA_DE_MUESTRA_O_DESCONOCIDA' => 'La edad no está documentada o queda fuera del rango de la referencia.',
            'ESCOLARIDAD_FUERA_DE_MUESTRA_O_DESCONOCIDA' => 'La escolaridad no está documentada o queda fuera del rango de la referencia.',
            'APLICACION_NO_COMPLETA' => 'El estado de la aplicación no permite usarla para el cálculo.',
            'CONDICION_REQUIERE_45_SEGUNDOS' => 'La condición necesita una duración documentada de 45 segundos'.$campo.'.',
            'CONDICION_REQUIERE_60_SEGUNDOS' => 'La condición necesita una duración documentada de 60 segundos'.$campo.'.',
            'TAREA_NO_FINALIZADA_NO_EQUIVALE_A_TIEMPO_EXACTO' => 'La tarea no terminó; su tiempo se conserva sin presentarlo como finalización exacta.',
            default => 'Revisa la medición y el protocolo de aplicación: '.str_replace('_', ' ', mb_strtolower($codigo)).'.',
        };
    }

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
