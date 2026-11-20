<?php

require_once __DIR__.'/support/SepararSujetosClinicos20261120.php';

return new SepararSujetosClinicos20261120([
    'mediciones_clinicas' => ['cod_registro_medicion', 'residentes_mediciones_clinicas'],
    'registros_observaciones_cuidado' => ['cod_observacion_cuidado', 'residentes_observaciones_cuidado'],
], require __DIR__.'/support/EsquemasContextoClinico20261120.php');
