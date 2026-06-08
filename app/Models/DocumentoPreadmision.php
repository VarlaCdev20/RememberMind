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
        'archivo_path',
        'nombre_original',
        'es_institucional',
        'obligatorio',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'es_institucional' => 'boolean',
        'obligatorio' => 'boolean',
    ];

    public function preadmision(): BelongsTo
    {
        return $this->belongsTo(Preadmision::class, 'cod_pre', 'cod_pre');
    }
}
