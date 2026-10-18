<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TÍTULO:
 * Soporte sintético de un criterio evaluado.
 *
 * CAPA:
 * Infrastructure - Eloquent.
 *
 * ¿PARA QUÉ SIRVE?
 * Vincula criterios y hechos ficticios sin inventar mapeos institucionales.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Criterio evaluado, hecho original y posición del soporte.
 *
 * ¿QUÉ HACE?
 * Declara las FK del vínculo ordenado conservado.
 *
 * ¿QUÉ DEVUELVE?
 * Relaciones con el criterio y su hecho del mismo caso.
 *
 * ¿QUÉ NO DEBE HACER?
 * No atribuye una referencia sintética a un registro clínico real.
 * Hereda la prohibición de sobrescritura y borrado ordinario de ModeloExperto.
 */
final class SoporteSinteticoCriterio extends ModeloExperto
{
    protected $table = 'soportes_sinteticos_criterio';

    protected $primaryKey = 'cod_soporte_sintetico';

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function criterioEvaluado(): BelongsTo
    {
        return $this->belongsTo(EvaluacionCriterio::class, 'cod_evaluacion_criterio', 'cod_evaluacion_criterio');
    }

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function hecho(): BelongsTo
    {
        return $this->belongsTo(HechoInstantaneaExperta::class, 'cod_hecho_instantanea', 'cod_hecho_instantanea');
    }
}
