<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\VersionModeloExperto;
use Illuminate\Support\Facades\Gate;
use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental;
use MotorExperto\investigacion\g09\PaqueteSemanticoG09Experimental;

/** Proyección del paquete real, sin inferencias ni escrituras. */
final class ExplorarConocimientoExperto
{
    public function obtener(?string $version): array
    {
        Gate::authorize('consultar', VersionModeloExperto::class);
        $tablas = $version === null ? PaqueteSemanticoG09Experimental::paraConservacion()->declaracion()['tablas']
            : app(ConsultarConocimientoAdministrativo::class)->instantanea($version);
        $nodos = array_column($tablas['nodos_semanticos'], null, 'cod_nodo_semantico');
        $variables = array_column($tablas['variables_expertas'], null, 'cod_variable_experta');
        $valores = array_column($tablas['valores_semanticos'], null, 'cod_valor_semantico');
        $criterios = array_column($tablas['criterios_dominios_resultado'], null, 'cod_criterio_dominio_resultado');
        $reglas = [];
        foreach ($tablas['reglas_expertas'] as $r) {
            $id = $r['cod_regla_experta'];
            $meta = $version === null ? (CatalogoReglasG09Experimental::todas()[$id] ?? []) : [];
            $condiciones = array_values(array_filter($tablas['condiciones_regla_experta'], static fn ($c) => $c['cod_regla_experta'] === $id));
            foreach ($condiciones as &$c) {
                $v = $variables[$c['cod_variable_experta']] ?? [];
                $c['variable_nombre'] = $v['nombre'] ?? $nodos[$v['cod_nodo_semantico'] ?? '']['codigo_semantico'] ?? $c['cod_variable_experta'];
                $val = $valores[$c['cod_valor_semantico']] ?? [];
                $c['valor_nombre'] = $val['nombre'] ?? $val['codigo_valor'] ?? $c['cod_valor_semantico'];
            }
            unset($c);
            $consecuencias = array_values(array_filter($tablas['consecuencias_regla_experta'], static fn ($c) => $c['cod_regla_experta'] === $id));
            $criterioId = $criterios[$consecuencias[0]['cod_criterio_dominio_resultado'] ?? '']['cod_nodo_criterio'] ?? null;
            $dominio = $meta['criterio'] ?? ($nodos[$criterioId]['codigo_semantico'] ?? 'No disponible');
            $reglas[$id] = $r + ['id' => $id, 'codigo' => $r['codigo_regla'] ?? $id, 'dominio' => $dominio, 'fundamento' => $r['observacion'] ?? null,
                'subcomponente' => $meta['componente'] ?? 'No disponible', 'condiciones' => $condiciones,
                'consecuencias' => $consecuencias, 'version' => $version ?? CatalogoReglasG09Experimental::VERSION,
                'restricciones' => $meta['confusores'] ?? ($r['observacion'] ?? 'Sin restricción declarada en este contrato.')];
        }

        return ['tablas' => $tablas, 'reglas' => $reglas, 'nodos' => $nodos,
            'relaciones' => $tablas['relaciones_semanticas'], 'experimental' => $version === null];
    }

    /** Diferencias por código semántico, sin equiparar PK entre revisiones. */
    public function comparar(?string $versionA, string $versionB): array
    {
        $normalizar = static function (array $conocimiento): array {
            $t = $conocimiento['tablas'];
            $variables = array_column($t['variables_expertas'], 'cod_nodo_semantico', 'cod_variable_experta');
            $valores = array_column($t['valores_semanticos'], null, 'cod_valor_semantico');
            $dominios = array_column($t['dominios_valores_expertos'], 'codigo_dominio', 'cod_dominio_valores');
            $nodos = array_column($t['nodos_semanticos'], 'codigo_semantico', 'cod_nodo_semantico');
            $criterios = array_column($t['criterios_dominios_resultado'], 'cod_nodo_criterio', 'cod_criterio_dominio_resultado');
            $salida = [];
            foreach ($conocimiento['reglas'] as $r) {
                $condiciones = array_map(static fn ($c) => [
                    'variable' => $nodos[$variables[$c['cod_variable_experta']] ?? ''] ?? $c['cod_variable_experta'],
                    'dominio' => $dominios[$valores[$c['cod_valor_semantico']]['cod_dominio_valores']] ?? $valores[$c['cod_valor_semantico']]['cod_dominio_valores'],
                    'valor' => $valores[$c['cod_valor_semantico']]['codigo_valor'],
                ], $r['condiciones']);
                $consecuencias = array_map(static fn ($c) => [
                    'criterio' => $nodos[$criterios[$c['cod_criterio_dominio_resultado']] ?? ''] ?? $c['cod_criterio_dominio_resultado'],
                    'dominio' => $dominios[$valores[$c['cod_valor_semantico']]['cod_dominio_valores']] ?? $valores[$c['cod_valor_semantico']]['cod_dominio_valores'],
                    'valor' => $valores[$c['cod_valor_semantico']]['codigo_valor'],
                ], $r['consecuencias']);
                sort($condiciones);
                sort($consecuencias);
                if (isset($salida[$r['codigo']])) {
                    throw new \DomainException('Código de regla ambiguo.');
                }
                $salida[$r['codigo']] = ['nombre' => $r['nombre'] ?? null, 'estado' => $r['estado'], 'tipo_regla' => $r['tipo_regla'], 'descripcion' => $r['descripcion'] ?? null,
                    'fundamento' => $r['fundamento'] ?? null, 'condiciones' => $condiciones, 'consecuencias' => $consecuencias];
            }

            return $salida;
        };
        $antes = $normalizar($this->obtener($versionA));
        $despues = $normalizar($this->obtener($versionB));
        $cambios = [];
        foreach (array_unique([...array_keys($antes), ...array_keys($despues)]) as $codigo) {
            $cambios[$codigo] = ['estado' => ! isset($antes[$codigo]) ? 'Añadida' : (! isset($despues[$codigo]) ? 'Retirada' : ($antes[$codigo] === $despues[$codigo] ? 'Sin cambios' : 'Modificada')),
                'antes' => $antes[$codigo] ?? [], 'despues' => $despues[$codigo] ?? []];
        }

        return $cambios;
    }
}
