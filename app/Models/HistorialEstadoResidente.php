<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HistorialEstadoResidente extends ModeloOperativo
{
    protected $table = 'historial_estados_residente';
    protected $primaryKey = 'cod_historial_estado';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'cod_historial_estado',
        'cod_residente',
        'cod_usuario_registro',
        'estado_anterior',
        'estado_nuevo',
        'fecha_hora',
        'motivo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
        ];
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_registro', 'cod_usuario');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->cod_historial_estado)) {
                $model->cod_historial_estado = 'HER_' . strtoupper(Str::random(10));
            }
            if (empty($model->fecha_hora)) {
                $model->fecha_hora = now();
            }
        });
    }
}
