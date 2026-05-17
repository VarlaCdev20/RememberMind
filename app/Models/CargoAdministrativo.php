<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CargoAdministrativo extends Model
{
    use HasFactory;

    protected $table = 'cargos_administrativos';
    protected $primaryKey = 'cod_cargo_admin';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function personalAdmin()
    {
        return $this->hasMany(PersonalAdmin::class, 'cod_cargo_admin', 'cod_cargo_admin');
    }
}
