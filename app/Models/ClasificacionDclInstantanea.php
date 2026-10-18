<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * TÍTULO:
 * Clasificación experimental conservada.
 *
 * CAPA:
 * Infrastructure - Eloquent.
 *
 * ¿PARA QUÉ SIRVE?
 * Guarda un candidato o abstención con contrato, diferencial y justificación.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Evaluación, perfil funcional, salida experimental y huella de contenido.
 *
 * ¿QUÉ HACE?
 * Relaciona la clasificación con sus motivos y reglas aplicadas originales.
 *
 * ¿QUÉ DEVUELVE?
 * Relaciones de una clasificación histórica única por evaluación.
 *
 * ¿QUÉ NO DEBE HACER?
 * No emite diagnóstico ni autoriza decisiones asistenciales.
 * Hereda la prohibición de sobrescritura y borrado ordinario de ModeloExperto.
 */
final class ClasificacionDclInstantanea extends ModeloExperto
{
    protected $table = 'clasificaciones_dcl_instantanea';

    protected $primaryKey = 'cod_clasificacion_dcl_instantanea';

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(EvaluacionExperta::class, 'cod_evaluacion_experta', 'cod_evaluacion_experta');
    }

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function funcionalidad(): BelongsTo
    {
        return $this->belongsTo(FuncionalidadInstantaneaExperta::class, 'cod_funcionalidad_instantanea', 'cod_funcionalidad_instantanea');
    }

    /** Sin parámetros: devuelve la relación Eloquent del contrato aprobado; la consulta diferida puede propagar errores SQL. */
    public function motivos(): HasMany
    {
        return $this->hasMany(MotivoDclInstantanea::class, 'cod_clasificacion_dcl_instantanea', 'cod_clasificacion_dcl_instantanea');
    }

    /** Sin parámetros: devuelve la relación Eloquent del contrato aprobado; la consulta diferida puede propagar errores SQL. */
    public function reglasAplicadas(): HasMany
    {
        return $this->hasMany(ReglaAplicadaDclInstantanea::class, 'cod_clasificacion_dcl_instantanea', 'cod_clasificacion_dcl_instantanea');
    }
}
