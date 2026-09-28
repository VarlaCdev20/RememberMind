<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Admision extends ModeloOperativo {
    protected $table='admisiones'; protected $primaryKey='cod_admision';
    protected function casts(): array { return ['fecha_hora_admision'=>'datetime']; }
    public function preadmision(): BelongsTo { return $this->belongsTo(Preadmision::class,'cod_preadmision','cod_preadmision'); }
    public function residente(): BelongsTo { return $this->belongsTo(Residente::class,'cod_residente','cod_residente'); }
    public function ocupacionesCama(): HasMany { return $this->hasMany(OcupacionCama::class,'cod_admision','cod_admision'); }
    public function consentimientos(): HasMany { return $this->hasMany(Consentimiento::class,'cod_admision','cod_admision'); }
}
