<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TÍTULO:
 * Caracterización cognitiva de un hecho conservado.
 *
 * CAPA:
 * Infrastructure - Eloquent.
 *
 * ¿PARA QUÉ SIRVE?
 * Preserva el alcance del hallazgo y su referencia instrumental original.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Hecho, dominio, subcomponente, descriptor y metadatos de referencia.
 *
 * ¿QUÉ HACE?
 * Mantiene una caracterización por hecho con su justificación exacta.
 *
 * ¿QUÉ DEVUELVE?
 * Referencia al hecho original sin recrearlo.
 *
 * ¿QUÉ NO DEBE HACER?
 * No calcula baremos ni convierte una observación en diagnóstico.
 * Hereda la prohibición de sobrescritura y borrado ordinario de ModeloExperto.
 */
final class EvidenciaCognitivaInstantanea extends ModeloExperto
{
    protected $table = 'evidencias_cognitivas_instantanea';

    protected $primaryKey = 'cod_evidencia_cognitiva_instantanea';

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function hecho(): BelongsTo
    {
        return $this->belongsTo(HechoInstantaneaExperta::class, 'cod_hecho_instantanea', 'cod_hecho_instantanea');
    }
}
