<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Services\Semantica;

use MotorExperto\dominio\orion\Entities\Conocimiento\RedSemantica;
use MotorExperto\dominio\orion\Entities\Conocimiento\RelacionSemantica;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\EstadoReglaEnum;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use DomainException;

/** Comprueba relaciones directas y su habilitación; no inventa vínculos ni caminos. */
final readonly class EvaluadorRedSemanticaService
{
    /**
     * Valida cada arista contra un contrato único de versión, predicado y tipos.
     * @param RedSemantica $red
     * @param list<array{versionConocimiento: string, tipo: TipoRelacionSemanticaEnum, tipoOrigen: string, tipoDestino: string, habilitaParticipacion: bool}> $contratos
     * @return array<string, array{relacion: RelacionSemantica, contrato: array}>
     * @throws DomainException
     */
    public function validarRed(RedSemantica $red, array $contratos): array
    {
        $indice = $this->indexarContratos($red, $contratos);
        $validas = [];
        foreach ($red->relaciones as $r) {
            $clave = serialize([$r->tipo->value, $r->origen->tipo, $r->destino->tipo]);
            if (! isset($indice[$clave])) {
                throw new DomainException('Relación sin contrato tipado único de su versión.');
            }
            $validas[$r->id] = ['relacion' => $r, 'contrato' => $indice[$clave]];
        }
        ksort($validas, SORT_STRING);

        return $validas;
    }

    /**
     * Consulta una arista dirigida exacta sin usar transitividad ni inversión.
     * @param RedSemantica $red
     * @param string $origenId
     * @param TipoRelacionSemanticaEnum $tipo
     * @param string $destinoId
     * @param list<array{versionConocimiento: string, tipo: TipoRelacionSemanticaEnum, tipoOrigen: string, tipoDestino: string, habilitaParticipacion: bool}> $contratos
     * @param array<string, ?bool> $habilitaciones
     * @return array{versionConocimiento: string, estado: EstadoReglaEnum, relacion: ?RelacionSemantica, habilitada: ?bool, autorizaParticipacion: bool, motivo: string}
     * @throws DomainException
     */
    public function evaluarRelacion(RedSemantica $red, string $origenId, TipoRelacionSemanticaEnum $tipo, string $destinoId,
        array $contratos, array $habilitaciones = []): array
    {
        $red->nodo($origenId);
        $red->nodo($destinoId);
        $validadas = $this->validarRed($red, $contratos);
        foreach ($habilitaciones as $id => $valor) {
            if (! array_key_exists($id, $red->relaciones) || ($valor !== null && ! is_bool($valor))) {
                throw new DomainException('Habilitación no booleana o de una arista inexistente.');
            }
        }
        $relacion = null;
        $contrato = null;
        foreach ($validadas as $validada) {
            $r = $validada['relacion'];
            if ($r->origen->id === $origenId && $r->destino->id === $destinoId && $r->tipo === $tipo) {
                $relacion = $r;
                $contrato = $validada['contrato'];
                break;
            }
        }
        $habilitada = $relacion === null ? null : ($habilitaciones[$relacion->id] ?? null);
        $estado = match (true) {
            $relacion === null => EstadoReglaEnum::NO_CUMPLE,
            $habilitada === null => EstadoReglaEnum::DESCONOCIDO,
            $habilitada === false => EstadoReglaEnum::BLOQUEADA,
            default => EstadoReglaEnum::CUMPLE,
        };
        $autoriza = $estado === EstadoReglaEnum::CUMPLE && $contrato['habilitaParticipacion'] === true;
        $motivo = match ($estado) {
            EstadoReglaEnum::NO_CUMPLE => 'ARISTA_INEXISTENTE',
            EstadoReglaEnum::DESCONOCIDO => 'HABILITACION_DESCONOCIDA',
            EstadoReglaEnum::BLOQUEADA => 'ARISTA_NO_HABILITADA',
            EstadoReglaEnum::CUMPLE => $autoriza ? 'PARTICIPACION_TECNICA_DECLARADA' : 'VINCULO_SIN_PARTICIPACION_INFERENCIAL',
        };

        return ['versionConocimiento' => $red->versionConocimiento, 'estado' => $estado, 'relacion' => $relacion,
            'habilitada' => $habilitada, 'autorizaParticipacion' => $autoriza, 'motivo' => $motivo];
    }

    /**
     * Normaliza contratos y rechaza duplicación exacta o formas abiertas ambiguas.
     * @param RedSemantica $red
     * @param list<array<string, mixed>> $contratos
     * @return array<string, array{versionConocimiento: string, tipo: TipoRelacionSemanticaEnum, tipoOrigen: string, tipoDestino: string, habilitaParticipacion: bool}>
     * @throws DomainException
     */
    private function indexarContratos(RedSemantica $red, array $contratos): array
    {
        if (! array_is_list($contratos)) {
            throw new DomainException('Los contratos de relaciones deben ser una lista.');
        }
        $indice = [];
        $esperadas = ['versionConocimiento', 'tipo', 'tipoOrigen', 'tipoDestino', 'habilitaParticipacion'];
        sort($esperadas);
        foreach ($contratos as $c) {
            if (! is_array($c)) {
                throw new DomainException('Contrato de relación mal tipado.');
            }
            $claves = array_keys($c);
            sort($claves);
            if ($claves !== $esperadas || $c['versionConocimiento'] !== $red->versionConocimiento
                || ! $c['tipo'] instanceof TipoRelacionSemanticaEnum || ! is_bool($c['habilitaParticipacion'])) {
                throw new DomainException('Forma o versión de contrato semántico incompatible.');
            }
            foreach (['tipoOrigen', 'tipoDestino'] as $campo) {
                if (! is_string($c[$campo]) || $c[$campo] === '' || preg_match('/[\x00-\x20\x7F]/', $c[$campo]) === 1) {
                    throw new DomainException('Tipo de extremo semántico inválido.');
                }
            }
            $clave = serialize([$c['tipo']->value, $c['tipoOrigen'], $c['tipoDestino']]);
            if (isset($indice[$clave])) {
                throw new DomainException('Contrato de relación ambiguo o duplicado.');
            }
            $indice[$clave] = ['versionConocimiento' => $c['versionConocimiento'], 'tipo' => $c['tipo'],
                'tipoOrigen' => $c['tipoOrigen'], 'tipoDestino' => $c['tipoDestino'], 'habilitaParticipacion' => $c['habilitaParticipacion']];
        }

        return $indice;
    }
}
