<?php

namespace App\Backend\Modulos\SistemaExperto\MemoriaTrabajo;

use App\Backend\Modulos\SistemaExperto\DTO\ContextoEjecucion;
use App\Backend\Modulos\SistemaExperto\DTO\EvidenciaExperta;
use DomainException;

final class MemoriaTrabajo
{
    private array $evidencias = [];

    private array $atomicas = [];

    private array $participaciones = [];

    private array $relaciones = [];

    public function __construct(public readonly ContextoEjecucion $contexto) {}

    public function incorporar(EvidenciaExperta $e): EvidenciaExperta
    {
        if ($e->ejecucion->evaluacion !== $this->contexto->evaluacion || $e->ejecucion->residente !== $this->contexto->residente
            || $e->ejecucion->version !== $this->contexto->version || $e->fuente->residente !== $this->contexto->residente
            || $e->ejecucion->fechaCorte != $this->contexto->fechaCorte) {
            throw new DomainException('Evidencia ajena al residente, evaluación o versión.');
        }
        $clave = $e->claveAtomica();
        if (isset($this->atomicas[$clave])) {
            $original = $this->evidencias[$this->atomicas[$clave]];
            if (serialize($original) !== serialize($e)) {
                throw new DomainException('Relectura incompatible de una evidencia atómica; conserva la instantánea original.');
            }

            return $original;
        }
        if (isset($this->evidencias[$e->id])) {
            throw new DomainException('Identificador de evidencia duplicado.');
        }
        $this->atomicas[$clave] = $e->id;
        $this->evidencias[$e->id] = $e;

        return $e;
    }

    public function evidencia(string $id): EvidenciaExperta
    {
        return $this->evidencias[$id] ?? throw new DomainException('Evidencia ausente de esta memoria.');
    }

    public function vincular(string $criterio, string $evidencia, string $rol): void
    {
        $this->evidencia($evidencia);
        if (isset($this->participaciones[$criterio][$evidencia]) && $this->participaciones[$criterio][$evidencia] !== $rol) {
            throw new DomainException('Participación incompatible para una evidencia del criterio.');
        }
        $this->participaciones[$criterio][$evidencia] = $rol;
    }

    public function paraCriterio(string $criterio): array
    {
        $ids = array_keys($this->participaciones[$criterio] ?? []);
        sort($ids, SORT_STRING);

        return array_map(fn ($id) => $this->evidencia($id), $ids);
    }

    public function todas(): array
    {
        return array_values($this->evidencias);
    }

    public function relacionar(string $origen, string $destino, string $tipo, ?string $justificacion = null): void
    {
        $this->evidencia($origen);
        $this->evidencia($destino);
        if ($origen === $destino || ! in_array($tipo, ['MISMO_EPISODIO_CLINICO', 'CORROBORACION_CLINICA', 'CONFUSOR_CLINICO'], true)) {
            throw new DomainException('Relación entre evidencias inválida.');
        }
        if ($tipo === 'MISMO_EPISODIO_CLINICO' && strcmp($origen, $destino) > 0) {
            [$origen, $destino] = [$destino, $origen];
        }
        $key = serialize([$origen, $destino, $tipo]);
        $fila = ['origen' => $origen, 'destino' => $destino, 'tipo' => $tipo, 'justificacion' => $justificacion];
        if (isset($this->relaciones[$key]) && $this->relaciones[$key] !== $fila) {
            throw new DomainException('Relación repetida con otra justificación.');
        }
        $this->relaciones[$key] = $fila;
    }

    public function relaciones(): array
    {
        $relaciones = array_values($this->relaciones);
        usort($relaciones, fn ($a, $b) => strcmp(serialize([$a['origen'], $a['destino'], $a['tipo']]), serialize([$b['origen'], $b['destino'], $b['tipo']])));

        return $relaciones;
    }

    public function participaciones(): array
    {
        return $this->participaciones;
    }

    /** Convergencia de un mismo hecho/episodio; los IDs técnicos no prueban independencia. */
    public function soportesEfectivos(array $ids): array
    {
        $grupos = [];
        foreach ($this->evidencias as $id => $e) {
            $grupos[$id] = serialize([$e->fuente->tabla, $e->fuente->registro, $e->fuente->campo]);
        }
        foreach ($this->relaciones as $r) {
            if ($r['tipo'] !== 'MISMO_EPISODIO_CLINICO') {
                continue;
            }
            $a = $grupos[$r['origen']];
            $b = $grupos[$r['destino']];
            $canonico = strcmp($a, $b) <= 0 ? $a : $b;
            foreach ($grupos as &$g) {
                if ($g === $a || $g === $b) {
                    $g = $canonico;
                }
            }
            unset($g);
        }
        $efectivos = [];
        foreach ($ids as $id) {
            $this->evidencia($id);
            $efectivos[] = $grupos[$id];
        }
        $efectivos = array_values(array_unique($efectivos));
        sort($efectivos, SORT_STRING);

        return $efectivos;
    }
}
