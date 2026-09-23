<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class NotaClinica extends ModeloOperativo { protected $table='notas_clinicas'; protected $primaryKey='cod_nota'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} public function atencion(): BelongsTo{return $this->belongsTo(Atencion::class,'cod_atencion','cod_atencion');} public function anterior(): BelongsTo{return $this->belongsTo(self::class,'cod_nota_anterior','cod_nota');} }
