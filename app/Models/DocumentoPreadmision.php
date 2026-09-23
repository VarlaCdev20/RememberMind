<?php

namespace App\Models;

use App\Traits\GeneraCodigo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoPreadmision extends Model
{
    use GeneraCodigo;

    protected $table = 'documentos_preadmision';
    protected $primaryKey = 'cod_doc_pre';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'DPR';
    protected $digitsCode = 5;

    protected $fillable = [
        'cod_doc_pre',
        'cod_pre',
        'tipo_documento',
        'nombre_documento',
        'grupo_documento',
        'archivo_path',
        'nombre_original',
        'es_institucional',
        'es_generado_sistema',
        'obligatorio',
        'bloquea_avance',
        'permite_48h',
        'estado',
        'observaciones',
        'fecha_limite_entrega',
        'fecha_generacion',
    ];

    protected $casts = [
        'es_institucional'     => 'boolean',
        'es_generado_sistema'  => 'boolean',
        'obligatorio'          => 'boolean',
        'bloquea_avance'       => 'boolean',
        'permite_48h'          => 'boolean',
        'fecha_limite_entrega' => 'datetime',
        'fecha_generacion'     => 'datetime',
    ];

    public function preadmision(): BelongsTo
    {
        return $this->belongsTo(Preadmision::class, 'cod_pre', 'cod_pre');
    }
}
