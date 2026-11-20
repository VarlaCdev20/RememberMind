<?php

require_once __DIR__.'/support/SepararSujetosClinicos20261120.php';

$esquemas = require __DIR__.'/support/EsquemasContextoCognitivo20261120.php';
$reemplazados = [];
foreach (['aplicaciones_instrumento', 'controles_funcionales_iadl'] as $tabla) {
    foreach ($esquemas['sqlite'][$tabla]['triggers'] as $trigger) {
        if (SeparacionContextoResidente20261120::usaResidente($trigger['sql'])) {
            $reemplazados[$trigger['name']] = $trigger['sql'];
        }
    }
}
$reemplazados['iadl_atencion_residente_padre'] = <<<'SQL'
CREATE TRIGGER iadl_atencion_residente_padre BEFORE UPDATE OF cod_residente ON atenciones
FOR EACH ROW WHEN EXISTS (
    SELECT 1 FROM controles_funcionales_iadl WHERE cod_atencion = OLD.cod_atencion AND cod_residente <> NEW.cod_residente
) BEGIN SELECT RAISE(ABORT, 'La atención ya pertenece a un registro IADL del residente'); END
SQL;

return new SepararSujetosClinicos20261120([
    'aplicaciones_instrumento' => ['cod_aplicacion', 'residentes_aplicaciones_instrumento'],
    'controles_cognitivos' => ['cod_control_cognitivo', 'residentes_controles_cognitivos'],
    'controles_funcionales_iadl' => ['cod_control_iadl', 'residentes_controles_funcionales_iadl'],
], $esquemas, $reemplazados);
