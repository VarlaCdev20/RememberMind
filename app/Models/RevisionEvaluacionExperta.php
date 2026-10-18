<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/** Dictamen original de una disciplina: rectificar crea otra fila, sin modificar el motor. */
final class RevisionEvaluacionExperta extends ModeloOperativo
{
    protected $table = 'revisiones_evaluacion_experta';

    protected $primaryKey = 'cod_revision_experta';

    protected function casts(): array
    {
        return ['fecha_hora_revision' => 'immutable_datetime'];
    }

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(EvaluacionExperta::class, 'cod_evaluacion_experta', 'cod_evaluacion_experta');
    }

    public function anterior(): BelongsTo
    {
        return $this->belongsTo(self::class, 'cod_revision_anterior', 'cod_revision_experta');
    }

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new LogicException('La revisión profesional es inmutable; registrar una rectificación.');
        });
    }
}
