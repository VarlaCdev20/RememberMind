<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Herida extends ModeloOperativo { protected $table='heridas'; protected $primaryKey='cod_herida'; protected function casts(): array{return ['fecha_hora_identificacion'=>'datetime','fecha_hora_cierre'=>'datetime'];} public function curaciones(): HasMany{return $this->hasMany(CuracionHerida::class,'cod_herida','cod_herida');} }
