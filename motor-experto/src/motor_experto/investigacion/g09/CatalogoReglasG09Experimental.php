<?php
declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use DomainException;

/**
 * Catálogo experimental de las 15 propuestas G09.
 * NUNCA representa conocimiento autorizado para uso clínico.
 */
final class CatalogoReglasG09Experimental
{
    public const VERSION = 'G09-EXP-20261017';
    public const SOLO_INVESTIGACION = true;

    public static function todas(): array
    {
        return [
            'R-ATE-S01-P01' => ['criterio' => 'COG-ATE', 'componente' => 'ATE-S01', 'consecuencia' => 'ATE-R-SOSTENIMIENTO', 'descripcion' => 'Atención sostenida', 'instrumentos_candidatos' => 'BTA no específico sostenido; Ruff 2&7 (límite edad editorial)', 'confusores' => 'sueño/fatiga; estado agudo; audición/visión'],
            'R-ATE-S02-P01' => ['criterio' => 'COG-ATE', 'componente' => 'ATE-S02', 'consecuencia' => 'ATE-R-SELECCION', 'descripcion' => 'Atención selectiva', 'instrumentos_candidatos' => 'Ruff 2&7 (edición/normas y población por comprobar)', 'confusores' => 'distractores; agudeza sensorial; control ejecutivo'],
            'R-ATE-S03-P01' => ['criterio' => 'COG-ATE', 'componente' => 'ATE-S03', 'consecuencia' => 'ATE-R-DISTRIBUCION', 'descripcion' => 'Atención dividida', 'instrumentos_candidatos' => 'Brief Test of Attention, interpretación de concurrencia por protocolo', 'confusores' => 'audición; concurrente versus alternancia ejecutiva'],
            'R-EJE-S01-P01' => ['criterio' => 'COG-EJE', 'componente' => 'EJE-S01', 'consecuencia' => 'EJE-R-INHIBICION', 'descripcion' => 'Control inhibitorio', 'instrumentos_candidatos' => 'Stroop (condición de interferencia)', 'confusores' => 'lectura; color; velocidad; visión'],
            'R-EJE-S02-P01' => ['criterio' => 'COG-EJE', 'componente' => 'EJE-S02', 'consecuencia' => 'EJE-R-FLEXIBILIDAD', 'descripcion' => 'Flexibilidad cognitiva', 'instrumentos_candidatos' => 'TMT-B / M-WCST', 'confusores' => 'rastreo visual; motor; velocidad; nivel educativo'],
            'R-EJE-S03-P01' => ['criterio' => 'COG-EJE', 'componente' => 'EJE-S03', 'consecuencia' => 'EJE-R-ACTUALIZACION', 'descripcion' => 'Manipulación de memoria de trabajo', 'instrumentos_candidatos' => 'WAIS-IV dígitos inversos/secuenciación', 'confusores' => 'audición; memoria simple versus manipulación'],
            'R-EJE-S04-P01' => ['criterio' => 'COG-EJE', 'componente' => 'EJE-S04', 'consecuencia' => 'EJE-R-PLANIFICACION', 'descripcion' => 'Planificación', 'instrumentos_candidatos' => 'Torre de Londres (edición pendiente)', 'confusores' => 'demanda motora; familiaridad tarea; planificación'],
            'R-EJE-S05-P01' => ['criterio' => 'COG-EJE', 'componente' => 'EJE-S05', 'consecuencia' => 'EJE-R-RAZONAMIENTO', 'descripcion' => 'Razonamiento/abstracción', 'instrumentos_candidatos' => 'M-WCST, cobertura conceptual incompleta', 'confusores' => 'flexibilidad versus abstracción; educación'],
            'R-LEN-S01-P01' => ['criterio' => 'COG-LEN', 'componente' => 'LEN-S01', 'consecuencia' => 'LEN-R-DENOMINACION', 'descripcion' => 'Denominación', 'instrumentos_candidatos' => 'Boston Naming Test estándar', 'confusores' => 'idioma; visión; escolaridad'],
            'R-LEN-S02-P01' => ['criterio' => 'COG-LEN', 'componente' => 'LEN-S02', 'consecuencia' => 'LEN-R-FLUIDEZ', 'descripcion' => 'Fluidez verbal', 'instrumentos_candidatos' => 'Prueba de fluidez semántica o fonológica versionada', 'confusores' => 'ejecutiva; velocidad; idioma/escolaridad'],
            'R-LEN-S03-P01' => ['criterio' => 'COG-LEN', 'componente' => 'LEN-S03', 'consecuencia' => 'LEN-R-COMPRENSION', 'descripcion' => 'Comprensión lingüística', 'instrumentos_candidatos' => 'Token Test', 'confusores' => 'audición; memoria de trabajo; idioma'],
            'R-LEN-S04-P01' => ['criterio' => 'COG-LEN', 'componente' => 'LEN-S04', 'consecuencia' => 'LEN-R-REPETICION', 'descripcion' => 'Repetición verbal', 'instrumentos_candidatos' => 'WAB-R (edición/licencia pendientes)', 'confusores' => 'audición; articulación; memoria inmediata'],
            'R-LEN-S05-P01' => ['criterio' => 'COG-LEN', 'componente' => 'LEN-S05', 'consecuencia' => 'LEN-R-DISCURSO', 'descripcion' => 'Producción verbal/discurso', 'instrumentos_candidatos' => 'WAB-R habla espontánea', 'confusores' => 'articulación; iniciativa; estado psicológico'],
            'R-VIS-S01-P01' => ['criterio' => 'COG-VIS', 'componente' => 'VIS-S01', 'consecuencia' => 'VIS-R-PERCEPCION', 'descripcion' => 'Relaciones espaciales', 'instrumentos_candidatos' => 'VOSP espacial / JLO', 'confusores' => 'agudeza visual; orientación general no sustituye'],
            'R-VIS-S02-P01' => ['criterio' => 'COG-VIS', 'componente' => 'VIS-S02', 'consecuencia' => 'VIS-R-CONSTRUCCION', 'descripcion' => 'Visuoconstrucción', 'instrumentos_candidatos' => 'Rey–Osterrieth fase de copia', 'confusores' => 'motricidad; praxia; planificación; no usar recuerdo'],
        ];
    }

    public static function obtener(string $id): array
    {
        return self::todas()[$id] ?? throw new DomainException('Regla G09 inexistente.');
    }
}
