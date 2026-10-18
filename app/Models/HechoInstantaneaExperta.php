<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * TÍTULO:
 * Hecho original utilizado por una evaluación.
 *
 * CAPA:
 * Infrastructure - Eloquent.
 *
 * ¿PARA QUÉ SIRVE?
 * Conserva el valor tipado, fecha, episodio y procedencia que recibió el motor.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Identidad de evaluación, escalar original y FK de origen cuando corresponde.
 *
 * ¿QUÉ HACE?
 * Relaciona el hecho con su evaluación y evidencia institucional original.
 *
 * ¿QUÉ DEVUELVE?
 * Referencias Eloquent de la misma instantánea.
 *
 * ¿QUÉ NO DEBE HACER?
 * No reextrae valores actuales ni sustituye una ausencia por un valor conocido.
 * Hereda la prohibición de sobrescritura y borrado ordinario de ModeloExperto.
 */
final class HechoInstantaneaExperta extends ModeloExperto
{
    protected $table = 'hechos_instantanea_experta';

    protected $primaryKey = 'cod_hecho_instantanea';

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(EvaluacionExperta::class, 'cod_evaluacion_experta', 'cod_evaluacion_experta');
    }

    /** Sin parámetros: devuelve una relación BelongsTo con la FK original, sin ejecutar SQL ni crear registros. */
    public function evidenciaOriginal(): BelongsTo
    {
        return $this->belongsTo(EvidenciaEvaluacion::class, 'cod_evidencia_evaluacion', 'cod_evidencia_evaluacion');
    }

    public function controlOriginal(): BelongsTo
    {
        return $this->belongsTo(ControlCognitivo::class, 'cod_control_cognitivo', 'cod_control_cognitivo');
    }

    public function autorOriginal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal_autor', 'cod_personal');
    }

    /** Sin parámetros: devuelve la relación Eloquent del contrato aprobado; la consulta diferida puede propagar errores SQL. */
    public function caracterizacionCognitiva(): HasOne
    {
        return $this->hasOne(EvidenciaCognitivaInstantanea::class, 'cod_hecho_instantanea', 'cod_hecho_instantanea');
    }
}
