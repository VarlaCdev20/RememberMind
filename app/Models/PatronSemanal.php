<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo cerrado de conjuntos semanales. Su inmutabilidad se exige también en SQL. */
final class PatronSemanal extends Model
{
    protected $table = 'patrones_semanales';

    protected $primaryKey = 'cod_patron_semanal';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return ['todos_los_dias' => 'boolean'];
    }

    public function dias(): HasMany
    {
        return $this->hasMany(DiaPatronSemanal::class, 'cod_patron_semanal', 'cod_patron_semanal')->orderBy('dia_semana');
    }

    public function representacion(): ?string
    {
        $nombres = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];

        return $this->todos_los_dias ? null : ($this->dias->isEmpty() ? 'NINGUNO'
            : $this->dias->map(fn ($dia) => $nombres[$dia->dia_semana])->implode(','));
    }
}
