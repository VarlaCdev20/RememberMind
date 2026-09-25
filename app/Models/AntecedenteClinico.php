<?php
namespace App\Models;
class AntecedenteClinico extends ModeloOperativo { protected $table='antecedentes_clinicos'; protected $primaryKey='cod_antecedente'; protected function casts(): array{return ['fecha_referencia'=>'date'];} }
