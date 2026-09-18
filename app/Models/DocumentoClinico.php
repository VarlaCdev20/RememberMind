<?php
namespace App\Models;
class DocumentoClinico extends ModeloOperativo { protected $table='documentos_clinicos'; protected $primaryKey='cod_documento_clinico'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
