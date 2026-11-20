<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once __DIR__.'/support/SepararSujetosClinicos20261120.php';

$esquemas = require __DIR__.'/support/EsquemasContextoRestante20261120.php';
if (DB::getDriverName() === 'pgsql' && Schema::hasColumn('documentos', 'cod_usuario_carga')) {
    $esquemas['pgsql']['documentos'] = $esquemas['pgsql_documentos_instalado'];
}
$reemplazados = [];
foreach ($esquemas['sqlite']['derivaciones']['triggers'] as $trigger) {
    if (SeparacionContextoResidente20261120::usaResidente($trigger['sql'])) {
        $reemplazados[$trigger['name']] = $trigger['sql'];
    }
}

return new SepararSujetosClinicos20261120([
    'derivaciones' => ['cod_derivacion', 'residentes_derivaciones'],
    'documentos' => ['cod_documento', 'residentes_documentos'],
    'registros_clinicos' => ['cod_registro_clinico', 'residentes_registros_clinicos'],
    'registros_textuales_clinicos' => ['cod_registro_textual', 'residentes_registros_textuales_clinicos'],
    'valoraciones_psicopedagogicas' => ['cod_valoracion_profesional', 'residentes_valoraciones_psicopedagogicas'],
    'valoraciones_petersen_originales' => ['cod_valoracion_petersen', 'residentes_valoraciones_petersen'],
], $esquemas, $reemplazados, ['vpo_inmutable' => 'valoraciones_petersen_originales']);
