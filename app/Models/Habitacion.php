<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Habitacion extends ModeloOperativo {
    protected $table='habitaciones'; protected $primaryKey='cod_habitacion';
    public function camas(): HasMany { return $this->hasMany(Cama::class,'cod_habitacion','cod_habitacion'); }
}
