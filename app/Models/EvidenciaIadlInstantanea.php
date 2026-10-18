<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TÍTULO:
 * Evidencia contextual IADL utilizada en el análisis.
 *
 * CAPA:
 * Infrastructure - Eloquent.
 *
 * ¿PARA QUÉ SIRVE?
 * Preserva desempeño, oportunidad, nivel previo y revisión causal nullable.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Hecho original, actividad, contexto y atribución sintética explícita o ausente.
 *
 * ¿QUÉ HACE?
 * Vincula la evidencia con el perfil funcional y su hecho conservado.
 *
 * ¿QUÉ DEVUELVE?
 * Referencias del contexto funcional de la evaluación.
 *
 * ¿QUÉ NO DEBE HACER?
 * No convierte causa de ayuda en atribución cognitiva ni completa datos ausentes.
 * Hereda la prohibición de sobrescritura y borrado ordinario de ModeloExperto.
 */
final class EvidenciaIadlInstantanea extends ModeloExperto
{
    protected $table = 'evidencias_iadl_instantanea';

    protected $primaryKey = 'cod_evidencia_iadl_instantanea';

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function funcionalidad(): BelongsTo
    {
        return $this->belongsTo(FuncionalidadInstantaneaExperta::class, 'cod_funcionalidad_instantanea', 'cod_funcionalidad_instantanea');
    }

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function hecho(): BelongsTo
    {
        return $this->belongsTo(HechoInstantaneaExperta::class, 'cod_hecho_instantanea', 'cod_hecho_instantanea');
    }
}
