<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Services\Inferencia;

use MotorExperto\dominio\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\ValueObjects\Conocimiento\EstadoReglaEnum;
use MotorExperto\dominio\ValueObjects\Conocimiento\OperadorCondicionEnum;
use DomainException;

/** Evalúa condiciones tipadas; conserva los datos desconocidos y sus soportes. */
final readonly class EvaluadorReglasService
{
    /**
     * Evalúa todas las ramas para conservar su explicación, incluso si una decide la composición.
     * @param ReglaExperta $regla
     * @param MemoriaTrabajo $memoria
     * @param list<HechoExperto> $hechosAdmitidos
     * @return array{regla: ReglaExperta, memoria: MemoriaTrabajo, estado: EstadoReglaEnum, condiciones: ?array, soportes: list<HechoExperto>, consecuencia: ?array}
     * @throws DomainException
     */
    public function evaluar(ReglaExperta $regla, MemoriaTrabajo $memoria, array $hechosAdmitidos): array
    {
        if ($regla->versionConocimiento !== $memoria->versionConocimiento || ! array_is_list($hechosAdmitidos)) {
            throw new DomainException('Regla o selección de hechos incompatible con la memoria.');
        }
        $porConcepto = $ids = [];
        foreach ($hechosAdmitidos as $hecho) {
            if (! $hecho instanceof HechoExperto || ($memoria->hechos[$hecho->id] ?? null) !== $hecho || isset($ids[$hecho->id])) {
                throw new DomainException('La regla solo recibe hechos originales únicos de su memoria.');
            }
            $ids[$hecho->id] = true;
            $porConcepto[$hecho->concepto][] = $hecho;
        }
        $arbol = $regla->habilitada ? $this->evaluarNodo($regla->condiciones, $porConcepto) : null;
        $estado = $arbol === null ? EstadoReglaEnum::BLOQUEADA : match ($arbol['valor']) {
            true => EstadoReglaEnum::CUMPLE,
            false => EstadoReglaEnum::NO_CUMPLE,
            null => EstadoReglaEnum::DESCONOCIDO,
        };

        return ['regla' => $regla, 'memoria' => $memoria, 'estado' => $estado,
            'condiciones' => $arbol, 'soportes' => $arbol['soportes'] ?? [],
            'consecuencia' => $estado === EstadoReglaEnum::CUMPLE ? $regla->consecuencia : null];
    }

    /**
     * Aplica comparación estricta y tablas trivalentes: NO desconocido sigue desconocido.
     * @param array<string, mixed> $nodo
     * @param array<string, list<HechoExperto>> $hechos
     * @return array{operador: OperadorCondicionEnum, valor: ?bool, motivo: string, soportes: list<HechoExperto>, ramas: list<array>, variable: ?string, esperado: string|int|float|bool|null}
     */
    private function evaluarNodo(array $nodo, array $hechos): array
    {
        $operador = $nodo['operador'];
        $ramas = [];
        $soportes = [];
        $motivo = 'COMPOSICION_LOGICA';
        if (in_array($operador, [OperadorCondicionEnum::IGUAL_A, OperadorCondicionEnum::DISTINTO_DE], true)) {
            $soportes = $hechos[$nodo['variable']] ?? [];
            usort($soportes, static fn (HechoExperto $a, HechoExperto $b): int => strcmp($a->id, $b->id));
            $valores = [];
            $ausente = $soportes === [];
            foreach ($soportes as $hecho) {
                $ausente = $ausente || $hecho->valor === null;
                if ($hecho->valor !== null && ! in_array($hecho->valor, $valores, true)) {
                    $valores[] = $hecho->valor;
                }
            }
            $motivo = count($valores) > 1 ? 'DECLARACIONES_CONTRADICTORIAS' : ($ausente ? 'DATO_DESCONOCIDO' : 'COMPARACION_ESTRICTA');
            $valor = $ausente || count($valores) !== 1 ? null : ($valores[0] === $nodo['valor']);
            if ($operador === OperadorCondicionEnum::DISTINTO_DE && $valor !== null) {
                $valor = ! $valor;
            }
        } else {
            foreach ($operador === OperadorCondicionEnum::NO ? [$nodo['condicion']] : $nodo['condiciones'] as $hija) {
                $ramas[] = $this->evaluarNodo($hija, $hechos);
            }
            $valores = array_column($ramas, 'valor');
            $valor = match ($operador) {
                OperadorCondicionEnum::NO => $valores[0] === null ? null : ! $valores[0],
                OperadorCondicionEnum::Y => in_array(false, $valores, true) ? false : (in_array(null, $valores, true) ? null : true),
                OperadorCondicionEnum::O => in_array(true, $valores, true) ? true : (in_array(null, $valores, true) ? null : false),
            };
            // Solo las ramas decisivas justifican una conclusión. Las demás permanecen en la traza.
            $unicos = [];
            foreach ($ramas as $rama) {
                $decisiva = $operador === OperadorCondicionEnum::NO || $valor === null || $rama['valor'] === $valor;
                if ($decisiva) {
                    foreach ($rama['soportes'] as $hecho) {
                        $unicos[$hecho->id] = $hecho;
                    }
                }
            }
            ksort($unicos, SORT_STRING);
            $soportes = array_values($unicos);
        }

        return ['operador' => $operador, 'valor' => $valor, 'motivo' => $motivo, 'soportes' => $soportes,
            'ramas' => $ramas, 'variable' => $nodo['variable'] ?? null, 'esperado' => $nodo['valor'] ?? null];
    }
}
