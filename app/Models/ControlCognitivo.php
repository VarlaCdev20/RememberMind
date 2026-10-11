<?php
namespace App\Models;
class ControlCognitivo extends ModeloOperativo { protected $table='controles_cognitivos'; protected $primaryKey='cod_control_cognitivo'; protected function casts(): array{return ['fecha_hora'=>'datetime','sigue_instrucciones'=>'boolean','repite_preguntas'=>'boolean','olvida_indicaciones'=>'boolean','reconoce_personas'=>'boolean','reconoce_entorno'=>'boolean','confusion'=>'boolean','cambio_cognitivo'=>'boolean'];} }
