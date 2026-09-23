<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Personal extends ModeloOperativo {
    protected $table='personal'; protected $primaryKey='cod_personal';
    protected function casts(): array { return ['fecha_nacimiento'=>'date','fecha_ingreso'=>'date']; }
    public function usuario(): BelongsTo { return $this->belongsTo(User::class,'cod_usuario','cod_usuario'); }
    public function atenciones(): HasMany { return $this->hasMany(Atencion::class,'cod_personal','cod_personal'); }
    public function asignaciones(): HasMany { return $this->hasMany(AsignacionPersonal::class,'cod_personal','cod_personal'); }
}
