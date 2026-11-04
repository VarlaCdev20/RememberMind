<?php

namespace App\Modulos\Cuidados\Dominio;

/** Vocabulario ya utilizado por Seguimiento diario; no define una escala ni un diagnóstico. */
final class ObservacionCognitiva
{
    public const TEXTOS = ['orientacion_persona', 'orientacion_lugar', 'orientacion_tiempo', 'memoria_reciente', 'memoria_remota', 'atencion', 'comprension', 'lenguaje'];
    public const SENALES = ['sigue_instrucciones', 'repite_preguntas', 'olvida_indicaciones', 'reconoce_personas', 'reconoce_entorno', 'confusion', 'cambio_cognitivo'];
    public const DOMINIOS = ['orientacion' => 'Orientación', 'memoria' => 'Memoria', 'atencion' => 'Atención', 'comprension' => 'Comprensión', 'lenguaje' => 'Lenguaje', 'senales' => 'Señales observadas'];

    public static function campos(): array
    {
        return [
            'orientacion_persona' => ['Orientación en persona', 'ph-user', 'Reconoce su identidad y personas conocidas.', 'orientacion'],
            'orientacion_lugar' => ['Orientación en lugar', 'ph-map-pin', 'Reconoce el lugar donde se encuentra.', 'orientacion'],
            'orientacion_tiempo' => ['Orientación en tiempo', 'ph-clock', 'Reconoce referencias temporales durante la interacción.', 'orientacion'],
            'memoria_reciente' => ['Memoria reciente', 'ph-brain', 'Recuerdo observable de información o acontecimientos recientes.', 'memoria'],
            'memoria_remota' => ['Memoria remota', 'ph-clock-counter-clockwise', 'Recuerdo de información personal o acontecimientos del pasado.', 'memoria'],
            'atencion' => ['Atención', 'ph-target', 'Mantiene la atención durante una interacción.', 'atencion'],
            'comprension' => ['Comprensión', 'ph-book-open', 'Comprende indicaciones sencillas.', 'comprension'],
            'lenguaje' => ['Lenguaje', 'ph-chat-circle', 'Comunicación y expresión observadas.', 'lenguaje'],
            'sigue_instrucciones' => ['Sigue instrucciones', 'ph-list-checks', '', 'senales'],
            'repite_preguntas' => ['Repite preguntas', 'ph-question', '', 'senales'],
            'olvida_indicaciones' => ['Olvida indicaciones', 'ph-note', '', 'senales'],
            'reconoce_personas' => ['Reconoce personas habituales', 'ph-users', '', 'senales'],
            'reconoce_entorno' => ['Reconoce su entorno', 'ph-house', '', 'senales'],
            'confusion' => ['Presenta confusión', 'ph-puzzle-piece', '', 'senales'],
            'cambio_cognitivo' => ['Se observa un cambio cognitivo', 'ph-arrows-left-right', '', 'senales'],
        ];
    }

    public static function opciones(string $campo): array
    {
        return match ($campo) {
            'orientacion_persona', 'orientacion_lugar', 'orientacion_tiempo' => ['ORIENTADO' => 'Orientado', 'PARCIALMENTE_ORIENTADO' => 'Parcialmente orientado', 'DESORIENTADO' => 'Desorientado'],
            'memoria_reciente', 'memoria_remota' => ['CONSERVADA' => 'Conservada', 'ALTERACION_LEVE' => 'Alteración leve', 'ALTERADA' => 'Alterada', 'NO_VALORABLE' => 'No valorable'],
            'atencion' => ['CONSERVADA' => 'Conservada', 'FLUCTUANTE' => 'Fluctuante', 'DISMINUIDA' => 'Disminuida', 'NO_VALORABLE' => 'No valorable'],
            'comprension' => ['CONSERVADA' => 'Conservada', 'PARCIAL' => 'Parcial', 'ALTERADA' => 'Alterada', 'NO_VALORABLE' => 'No valorable'],
            'lenguaje' => ['CONSERVADO' => 'Conservado', 'LIMITADO' => 'Limitado', 'ALTERADO' => 'Alterado', 'NO_VALORABLE' => 'No valorable'],
            default => [],
        };
    }

    public static function etiqueta(string $campo, mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return 'No observado';
        }
        if (in_array($campo, self::SENALES, true)) {
            return $valor ? 'Sí' : 'No';
        }

        return self::opciones($campo)[$valor] ?? (string) $valor; // Historia literal desconocida: no reinterpretar.
    }
}
