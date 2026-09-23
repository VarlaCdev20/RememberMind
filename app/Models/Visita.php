<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visita extends ModeloOperativo
{
    protected $table = 'visitas';
    protected $primaryKey = 'cod_visita';

    /** Columnas exactas de visitas en la BDD V2. */
    protected $fillable = [
        'cod_visita', 'cod_residente', 'cod_contacto', 'cod_usuario_autorizacion',
        'fecha_hora_programada', 'fecha_hora_ingreso', 'fecha_hora_salida',
        'motivo', 'estado', 'observacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora_programada' => 'datetime',
            'fecha_hora_ingreso' => 'datetime',
            'fecha_hora_salida' => 'datetime',
        ];
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(Contacto::class, 'cod_contacto', 'cod_contacto');
    }

    public function usuarioAutorizacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_autorizacion', 'cod_usuario');
    }
}
