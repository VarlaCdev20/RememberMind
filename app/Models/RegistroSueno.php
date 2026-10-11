<?php
namespace App\Models;
class RegistroSueno extends ModeloOperativo { protected $table='registros_sueno'; protected $primaryKey='cod_registro_sueno'; protected function casts(): array{return ['fecha'=>'date','insomnio'=>'boolean','somnolencia_diurna'=>'boolean','agitacion_nocturna'=>'boolean'];} }
