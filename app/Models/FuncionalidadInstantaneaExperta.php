<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * TÍTULO:
 * Perfil funcional declarado en una evaluación.
 *
 * CAPA:
 * Infrastructure - Eloquent.
 *
 * ¿PARA QUÉ SIRVE?
 * Conserva independencia relativa, revisión y justificación originales.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Evaluación, estado declarado, revisor sintético y fundamento.
 *
 * ¿QUÉ HACE?
 * Relaciona el perfil con su evaluación y las evidencias IADL conservadas.
 *
 * ¿QUÉ DEVUELVE?
 * Relaciones del perfil funcional original.
 *
 * ¿QUÉ NO DEBE HACER?
 * No deduce independencia desde una lista vacía ni sustituye IADL por BADL.
 * Hereda la prohibición de sobrescritura y borrado ordinario de ModeloExperto.
 */
final class FuncionalidadInstantaneaExperta extends ModeloExperto
{
    protected $table = 'funcionalidad_instantanea_experta';

    protected $primaryKey = 'cod_funcionalidad_instantanea';

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(EvaluacionExperta::class, 'cod_evaluacion_experta', 'cod_evaluacion_experta');
    }

    /** Sin parámetros: devuelve la relación Eloquent del contrato aprobado; la consulta diferida puede propagar errores SQL. */
    public function evidenciasIadl(): HasMany
    {
        return $this->hasMany(EvidenciaIadlInstantanea::class, 'cod_funcionalidad_instantanea', 'cod_funcionalidad_instantanea');
    }
}
