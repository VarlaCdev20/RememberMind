<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Atencion extends ModeloOperativo {
    protected $table='atenciones'; protected $primaryKey='cod_atencion';
    protected function casts(): array { return ['fecha_hora'=>'datetime']; }
    public function residente(): BelongsTo { return $this->belongsTo(Residente::class,'cod_residente','cod_residente'); }
    public function personal(): BelongsTo { return $this->belongsTo(Personal::class,'cod_personal','cod_personal'); }
    public function area(): BelongsTo { return $this->belongsTo(Area::class,'cod_area','cod_area'); }
    public function notas(): HasMany { return $this->hasMany(NotaClinica::class,'cod_atencion','cod_atencion'); }
}
