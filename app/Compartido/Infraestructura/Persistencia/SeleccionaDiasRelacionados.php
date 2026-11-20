<?php

namespace App\Compartido\Infraestructura\Persistencia;

use App\Compartido\Dominio\Calendario\SeleccionSemanal;
use App\Models\PatronSemanal;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

/** Proyección escalar para los consumidores actuales; la selección se almacena relacionalmente. */
trait SeleccionaDiasRelacionados
{
    public function initializeSeleccionaDiasRelacionados(): void
    {
        $this->with[] = 'patronSemanal.dias';
        $this->appends[] = 'dias_semana';
    }

    public function patronSemanal(): BelongsTo
    {
        return $this->belongsTo(PatronSemanal::class, 'cod_patron_semanal', 'cod_patron_semanal');
    }

    public function setDiasSemanaAttribute(?string $valor): void
    {
        try {
            $this->cod_patron_semanal = SeleccionSemanal::codigo($valor, $this->getTable() === 'horarios_prescripcion');
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages(['dias_semana' => $e->getMessage()]);
        }
    }

    public function setCodPatronSemanalAttribute(string $codigo): void
    {
        $this->attributes['cod_patron_semanal'] = $codigo;
        $this->unsetRelation('patronSemanal');
    }

    public function getDiasSemanaAttribute(): ?string
    {
        // Es el mismo valor por defecto declarado por la FK del esquema, no una fila arbitraria.
        if (! isset($this->attributes['cod_patron_semanal'])) {
            return null;
        }
        $patron = $this->patronSemanal;
        if (! $patron) {
            throw new \LogicException('El patrón semanal del registro no existe.');
        }

        return $patron->representacion();
    }
}
