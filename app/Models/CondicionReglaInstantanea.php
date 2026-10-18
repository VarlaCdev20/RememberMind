<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * TÍTULO:
 * Nodo ordenado de una condición declarativa.
 *
 * CAPA:
 * Infrastructure - Eloquent.
 *
 * ¿PARA QUÉ SIRVE?
 * Conserva comparaciones y composición lógica del árbol original.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Regla propietaria, operador, literal tipado, padre opcional y orden.
 *
 * ¿QUÉ HACE?
 * Declara la pertenencia a la regla y las relaciones padre/hijas.
 *
 * ¿QUÉ DEVUELVE?
 * Relaciones del árbol declarativo conservado.
 *
 * ¿QUÉ NO DEBE HACER?
 * No evalúa expresiones de texto ni convierte la gramática en código ejecutable.
 * Hereda la prohibición de sobrescritura y borrado ordinario de ModeloExperto.
 */
final class CondicionReglaInstantanea extends ModeloExperto
{
    protected $table = 'condiciones_regla_instantanea';

    protected $primaryKey = 'cod_condicion_instantanea';

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function regla(): BelongsTo
    {
        return $this->belongsTo(ReglaAplicadaDclInstantanea::class, 'cod_regla_aplicada_dcl', 'cod_regla_aplicada_dcl');
    }

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(CondicionReglaInstantanea::class, 'cod_condicion_padre', 'cod_condicion_instantanea');
    }

    /** Sin parámetros: devuelve la relación Eloquent del contrato aprobado; la consulta diferida puede propagar errores SQL. */
    public function hijas(): HasMany
    {
        return $this->hasMany(CondicionReglaInstantanea::class, 'cod_condicion_padre', 'cod_condicion_instantanea');
    }
}
