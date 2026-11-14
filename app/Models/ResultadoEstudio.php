<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultadoEstudio extends RegistroConsumo
{
    protected const TIPO = 'RESULTADO';
    protected const ENTIDAD = 'resultados_estudio';
    protected $table = 'registros_estudio';
    protected $primaryKey = 'cod_registro_estudio';
    protected $appends = ['cod_resultado_estudio'];
    protected $hidden = ['cod_registro_estudio', 'tipo_evento', 'cod_personal', 'fecha_hora', 'hallazgos',
        'conclusion', 'recomendacion', 'origen', 'profesional_externo', 'estado'];
    protected $fillable = ['cod_resultado_estudio', 'cod_estudio', 'cod_componente', 'valor_numerico', 'valor_texto',
        'unidad', 'rango_referencia', 'clasificacion', 'observacion'];

    public function getCodResultadoEstudioAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodResultadoEstudioAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    public function estudio(): BelongsTo
    {
        return $this->belongsTo(EstudioClinico::class, 'cod_estudio', 'cod_estudio');
    }

    public function componente(): BelongsTo
    {
        return $this->belongsTo(ComponenteEstudio::class, 'cod_componente', 'cod_componente');
    }
}
