<?php

namespace App\Backend\Modulos\SistemaExperto\Evaluadores;

use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteConocimiento;
use App\Backend\Modulos\SistemaExperto\MemoriaTrabajo\MemoriaTrabajo;

final class EvaluadorEvaluabilidadCOGMEM
{
    public function evaluar(PaqueteConocimiento $p, MemoriaTrabajo $memoria, string $criterio, array $contexto): array
    {
        $ctx = (new EvaluadorContextoCOGMEM)->evaluar($contexto);
        $observacional = $instrumental = false;
        foreach ($memoria->paraCriterio($criterio) as $e) {
            if (! $e->utilizable()) {
                continue;
            }
            $v = $p->fila('variables_expertas', $e->variable);
            $codigo = $p->red->nodo($v['cod_nodo_semantico'])['codigo_semantico'];
            if (in_array($codigo, ['VAR-MEM-OBS-01', 'VAR-MEM-OBS-02', 'VAR-MEM-COR-01', 'VAR-COG-MULTI-01'], true)) {
                $observacional = true;
            }
            if ($codigo === 'VAR-MEM-INST-01' && ($e->fuente->contexto['componente_instrumental_verificado'] ?? false) === true) {
                $instrumental = true;
            }
        }
        $estado = ($observacional || $instrumental) ? 'EV-CM-1' : 'EV-CM-0';
        if ($instrumental && $ctx['suficiente'] && ! $ctx['invalidacion_necesaria']) {
            $estado = 'EV-CM-2';
        }
        if ($ctx['invalidacion_necesaria'] && ! $observacional) {
            $estado = 'EV-CM-0';
        }

        return ['estado' => $estado, 'modificador' => $estado === 'EV-CM-0' ? null : $ctx['modificador'],
            'contexto' => $ctx, 'habilita_resultado' => $estado === 'EV-CM-2'];
    }
}
