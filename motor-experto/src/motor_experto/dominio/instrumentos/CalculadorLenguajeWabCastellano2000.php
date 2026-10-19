<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

/**
 * Puntuaciones descriptivas de WAB castellana, Nau Llibres, edición de 2000.
 * Usa la tabla de puntuaciones de la página impresa 18; no reproduce reactivos
 * ni rúbricas. No importa normas WAB-R, no calcula cociente de afasia ni DCL.
 */
final class CalculadorLenguajeWabCastellano2000
{
    public const METODO = 'WAB-ES-2000-ORAL-BRUTO-1';

    public const FUENTE = 'https://talkbank.org/aphasia/protocol/languages/Spanish/WAB.pdf';

    /** La edición castellana y WAB-R requieren identidades distintas. */
    public function metadatos(): array
    {
        return ['instrumento' => 'WAB', 'tipo' => 'CALCULO_DESCRIPTIVO',
            'identidad' => ['instrumento' => 'WAB', 'edicion_protocolo' => 'Nau-Llibres-2000-Castellana', 'idioma' => 'es'],
            'componentes' => ['LEN-S04', 'LEN-S05'], 'fuente' => self::FUENTE,
            'entradas' => ['repeticion_total' => 'ENTERO', 'contenido_informacion' => 'ENTERO', 'fluidez' => 'ENTERO',
                'audicion_compatible' => 'LOGICO', 'muestra_discurso_documentada' => 'LOGICO', 'administracion_estandarizada' => 'LOGICO'],
            'reproduce_reactivos' => false, 'revision_profesional' => 'PENDIENTE'];
    }

    /** Cada subprueba conserva su invalidez propia; no se completa una condición ausente. */
    public function calcular(array $captura): array
    {
        $meta = $this->metadatos();
        $motivos = [];
        foreach ($meta['identidad'] + ['protocolo' => self::METODO] as $campo => $esperado) {
            if (($captura[$campo] ?? null) !== $esperado) {
                $motivos[] = 'IDENTIDAD_NO_VERIFICADA:'.$campo;
            }
        }
        if (($captura['administracion_estandarizada'] ?? null) !== true) {
            $motivos[] = 'ADMINISTRACION_NO_ACREDITADA';
        }
        $repeticion = $motivos;
        $discurso = $motivos;
        if (! $this->enteroEnRango($captura['repeticion_total'] ?? null, 100)) {
            $repeticion[] = 'REPETICION_REQUIERE_TOTAL_ORIGINAL_ENTERO_DE_0_A_100';
        }
        if (($captura['audicion_compatible'] ?? null) !== true) {
            $repeticion[] = 'AUDICION_NO_ACREDITADA';
        }
        foreach (['contenido_informacion', 'fluidez'] as $campo) {
            if (! $this->enteroEnRango($captura[$campo] ?? null, 10)) {
                $discurso[] = 'PUNTUACION_ORIGINAL_AUSENTE_O_INVALIDA:'.$campo;
            }
        }
        if (($captura['muestra_discurso_documentada'] ?? null) !== true) {
            $discurso[] = 'MUESTRA_DE_TAREA_NO_DOCUMENTADA';
        }
        // Son conversiones de escala y suma de rúbricas originales, no normalización poblacional.
        $r = $this->resultado('WAB_ES_2000_REPETICION_ESCALA_0_10', $repeticion,
            $repeticion === [] ? $captura['repeticion_total'] / 10 : null)
            + ['total_original' => $captura['repeticion_total'] ?? null, 'unidad' => 'PUNTOS_0_10'];
        $d = $this->resultado('WAB_ES_2000_HABLA_ESPONTANEA_TOTAL', $discurso,
            $discurso === [] ? $captura['contenido_informacion'] + $captura['fluidez'] : null)
            + ['contenido_informacion' => $captura['contenido_informacion'] ?? null,
                'fluidez' => $captura['fluidez'] ?? null, 'unidad' => 'PUNTOS_0_20'];

        return ['metodo' => self::METODO, 'referencia' => self::FUENTE,
            'referencia_edicion' => 'https://naullibres.com/libro/bateria-de-afasias-western/',
            'localizador' => 'Página impresa 18, tabla de puntuaciones; edición 2000, ISBN 9788476426326',
            'bruto' => array_intersect_key($captura, $meta['entradas']), 'contexto_aplicacion' => $captura,
            'componentes' => ['LEN-S04' => $r, 'LEN-S05' => $d],
            'diagnostico' => null, 'revision_profesional' => 'PENDIENTE',
            'limite' => 'Puntuaciones descriptivas de tareas de lenguaje. No son baremos bolivianos, clasificación de afasia ni resultado de DCL.'];
    }

    /** Valida la puntuación ya corregida por el profesional; no corrige respuestas literales. */
    private function enteroEnRango(mixed $valor, int $maximo): bool
    {
        return is_int($valor) && $valor >= 0 && $valor <= $maximo;
    }

    /** Conserva siempre la ausencia de comparación y de clasificación clínica. */
    private function resultado(string $medida, array $motivos, int|float|null $puntuacion): array
    {
        return ['medida' => $medida, 'estado' => $motivos === [] ? 'CALCULADO_SIN_NORMA' : 'NO_CALCULABLE',
            'motivos' => $motivos, 'puntuacion_bruta' => $puntuacion, 'esperado' => null, 'z' => null,
            'clasificacion_clinica' => null, 'advertencias' => ['SIN_NORMA_BOLIVIANA_VERIFICADA', 'NO_EQUIVALENTE_A_WAB_R']];
    }
}
