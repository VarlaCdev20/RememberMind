<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Lectura de los miembros ISO; identidad compuesta y escrituras protegidas en la BDD. */
final class DiaPatronSemanal extends Model
{
    protected $table = 'dias_patron_semanal';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return ['dia_semana' => 'integer'];
    }
}
