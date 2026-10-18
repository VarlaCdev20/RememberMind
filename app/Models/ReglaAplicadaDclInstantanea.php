<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * TÍTULO:
 * Copia histórica de una regla aplicada.
 *
 * CAPA:
 * Infrastructure - Eloquent.
 *
 * ¿PARA QUÉ SIRVE?
 * Preserva la regla y consecuencia originales utilizadas por la clasificación.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Clasificación, identidad de regla, fuente, habilitación y consecuencia tipada.
 *
 * ¿QUÉ HACE?
 * Vincula la copia con su origen D-137 cuando existe y con el árbol conservado.
 *
 * ¿QUÉ DEVUELVE?
 * Referencias de la regla original y sus condiciones.
 *
 * ¿QUÉ NO DEBE HACER?
 * No ejecuta reglas ni inventa conocimiento para llenar una colección vacía.
 * Hereda la prohibición de sobrescritura y borrado ordinario de ModeloExperto.
 */
final class ReglaAplicadaDclInstantanea extends ModeloExperto
{
    protected $table = 'reglas_aplicadas_dcl_instantanea';

    protected $primaryKey = 'cod_regla_aplicada_dcl';

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function clasificacion(): BelongsTo
    {
        return $this->belongsTo(ClasificacionDclInstantanea::class, 'cod_clasificacion_dcl_instantanea', 'cod_clasificacion_dcl_instantanea');
    }

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function reglaOriginal(): BelongsTo
    {
        return $this->belongsTo(ReglaExperta::class, 'cod_regla_experta', 'cod_regla_experta');
    }

    /** Sin parámetros: devuelve la relación Eloquent del contrato aprobado; la consulta diferida puede propagar errores SQL. */
    public function condiciones(): HasMany
    {
        return $this->hasMany(CondicionReglaInstantanea::class, 'cod_regla_aplicada_dcl', 'cod_regla_aplicada_dcl');
    }
}
