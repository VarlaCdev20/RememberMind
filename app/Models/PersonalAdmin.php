<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalAdmin extends Model
{
    protected $table = 'personal_admin';
    protected $primaryKey = 'cod_per_adm';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'cod_cargo_admin',
        'cargo',
        'fecha_ingreso',
        'area_admin',
        'estado_laboral',
        'observaciones',
        'cod_usu',
    ];

    /**
     * Relaciones
     */

    public function usuario()
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function cargoAdmin()
    {
        return $this->belongsTo(CargoAdministrativo::class, 'cod_cargo_admin', 'cod_cargo_admin');
    }

    public function horarios()
    {
        return $this->hasMany(HorarioPersonalAdmin::class, 'cod_per_adm', 'cod_per_adm');
    }
}