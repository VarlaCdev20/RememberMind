<?php

namespace App\Backend\Modulos\SistemaExperto\Trazabilidad;

use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteConocimiento;
use App\Backend\Modulos\SistemaExperto\MemoriaTrabajo\MemoriaTrabajo;
use DomainException;

final class ConstructorTraza
{
    public function construir(PaqueteConocimiento $p, MemoriaTrabajo $m, string $criterio, array $compuerta, array $reglas, ?array $resultado): array
    {
        $rutas = [];
        foreach ($reglas as $r) {
            foreach ($r['condiciones'] as $c) {
                foreach ($c['soportes'] as $id) {
                    $e = $m->evidencia($id);
                    if (! $e->utilizable() || $e->variable !== $c['variable'] || $e->valor !== $c['valor']) {
                        throw new DomainException('La evidencia de soporte no satisface la condición.');
                    }
                    $rutas[] = ['evaluacion' => $m->contexto->evaluacion, 'residente' => $m->contexto->residente,
                        'version' => $p->version, 'criterio' => $criterio, 'regla' => $r['regla'], 'estado_regla' => $r['estado'],
                        'condicion' => $c['condicion'], 'evidencia' => $e->id, 'variable' => $e->variable, 'valor' => $e->valor,
                        'mapeo_variable' => $e->mapeoVariable, 'mapeo_valor' => $e->mapeoValor, 'fuente' => $e->fuente->fuente,
                        'tabla' => $e->fuente->tabla, 'registro' => $e->fuente->registro, 'campo' => $e->fuente->campo,
                        'personal' => $e->fuente->personal, 'fecha_fuente' => $e->fuente->fecha?->format(DATE_ATOM),
                        'valor_original' => $e->fuente->valorOriginal];
                }
            }
        }

        return ['evaluacion' => $m->contexto->evaluacion, 'residente' => $m->contexto->residente,
            'fecha_corte' => $m->contexto->fechaCorte->format(DATE_ATOM), 'version' => $p->version, 'criterio' => $criterio,
            'compuerta' => $compuerta, 'resultado' => $resultado, 'reglas' => $reglas, 'rutas' => $rutas,
            'evidencias' => array_map(fn ($e) => ['id' => $e->id, 'fuente' => $e->fuente->fuente, 'tabla' => $e->fuente->tabla,
                'registro' => $e->fuente->registro, 'campo' => $e->fuente->campo, 'valor_original' => $e->fuente->valorOriginal,
                'mapeo_variable' => $e->mapeoVariable, 'mapeo_valor' => $e->mapeoValor, 'variable' => $e->variable, 'valor' => $e->valor,
                'representacion' => $e->representacion, 'admisibilidad' => $e->admisibilidad, 'motivos' => $e->motivos], $m->paraCriterio($criterio)),
            'relaciones_evidencias' => $m->relaciones(),
            'sin_emision' => array_map(fn ($e) => ['evidencia' => $e->id, 'representacion' => $e->representacion, 'admisibilidad' => $e->admisibilidad, 'motivos' => $e->motivos],
                array_values(array_filter($m->paraCriterio($criterio), fn ($e) => ! $e->utilizable())))];
    }
}
