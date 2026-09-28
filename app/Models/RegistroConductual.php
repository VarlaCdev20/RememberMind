<?php
namespace App\Models;
class RegistroConductual extends ModeloOperativo { protected $table='registros_conductuales'; protected $primaryKey='cod_registro_conductual'; protected function casts(): array{return ['fecha_hora'=>'datetime','apatia'=>'boolean','agitacion'=>'boolean','agresividad'=>'boolean','ansiedad'=>'boolean','aislamiento'=>'boolean','deambulacion'=>'boolean','cambio_conducta'=>'boolean'];} }
