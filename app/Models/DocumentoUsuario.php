<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * DocumentoUsuario — Documentos asociados a usuarios del sistema.
 *
 * NOTA: Clase renombrada de DocumentosUsuarios a DocumentoUsuario
 * para cumplir PSR-4 (nombre de clase debe coincidir con nombre de archivo).
 * User::documentos() ya referenciaba DocumentoUsuario::class.
 */
class DocumentoUsuario extends Model
{
    protected $table = 'documentos_usuarios';
    protected $primaryKey = 'cod_doc_usu';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nom_doc',
        'tipo_doc',
        'ruta_archivo',
        'extension',
        'fecha_doc',
        'observaciones',
        'cod_usu'
    ];

    /**
     * Relaciones
     */

    public function usuario()
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }
}