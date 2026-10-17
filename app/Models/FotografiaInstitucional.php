<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotografiaInstitucional extends ModeloOperativo
{
    protected $table = 'fotografias_institucionales';
    protected $primaryKey = 'cod_fotografia';
    protected $fillable = ['cod_usuario', 'titulo', 'descripcion', 'fecha_fotografia', 'texto_alternativo',
        'ruta_archivo', 'ruta_miniatura', 'tipo_archivo', 'tamanio_bytes', 'ancho', 'alto', 'hash_archivo',
        'estado', 'usar_en_bienvenida', 'fecha_registro', 'fecha_actualizacion'];

    protected function casts(): array
    {
        return ['fecha_fotografia' => 'date', 'fecha_registro' => 'datetime', 'fecha_actualizacion' => 'datetime',
            'usar_en_bienvenida' => 'boolean', 'tamanio_bytes' => 'integer', 'ancho' => 'integer', 'alto' => 'integer'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario', 'cod_usuario');
    }

    protected static function booted(): void
    {
        parent::booted();
        static::updating(function (self $foto): void {
            if ($foto->isDirty(['cod_usuario', 'ruta_archivo', 'ruta_miniatura', 'tipo_archivo', 'tamanio_bytes', 'ancho', 'alto', 'hash_archivo', 'fecha_registro'])) {
                throw new \LogicException('La identidad y el contenido de una fotografía guardada no se sobrescriben.');
            }
        });
    }
}
