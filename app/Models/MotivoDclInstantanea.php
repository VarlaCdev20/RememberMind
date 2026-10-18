<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TÍTULO:
 * Motivo ordenado de abstención experimental.
 *
 * CAPA:
 * Infrastructure - Eloquent.
 *
 * ¿PARA QUÉ SIRVE?
 * Conserva cada razón de no conclusión como un elemento separado.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Clasificación, código de motivo y orden original.
 *
 * ¿QUÉ HACE?
 * Declara la referencia restrictiva a la clasificación propietaria.
 *
 * ¿QUÉ DEVUELVE?
 * Relación con la clasificación que documenta la abstención.
 *
 * ¿QUÉ NO DEBE HACER?
 * No sustituye motivos por una normalidad implícita ni añade razones clínicas.
 * Hereda la prohibición de sobrescritura y borrado ordinario de ModeloExperto.
 */
final class MotivoDclInstantanea extends ModeloExperto
{
    protected $table = 'motivos_dcl_instantanea';

    protected $primaryKey = 'cod_motivo_dcl_instantanea';

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function clasificacion(): BelongsTo
    {
        return $this->belongsTo(ClasificacionDclInstantanea::class, 'cod_clasificacion_dcl_instantanea', 'cod_clasificacion_dcl_instantanea');
    }
}
