<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Area extends ModeloOperativo {
    protected $table='areas'; protected $primaryKey='cod_area';
    public function atenciones(): HasMany { return $this->hasMany(Atencion::class,'cod_area','cod_area'); }
    public function planesCuidado(): HasMany { return $this->hasMany(PlanCuidado::class,'cod_area','cod_area'); }
}
