<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Servicios;

use App\Models\VersionModeloExperto;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MotorExperto\dominio\conocimiento\versiones\PaqueteCognitivoInstitucional;

/** Contratos del servidor heredados por linaje; no recibe predicados ni mapeos del cliente. */
final class ContratosConocimiento
{
    /** Selecciona exclusivamente la familia adoptada; una ambigüedad nunca elige una fila arbitraria. */
    public function versionInstitucionalVigente(CarbonImmutable $corte): ?string
    {
        $raices = VersionModeloExperto::query()->where('codigo_version', PaqueteCognitivoInstitucional::CODIGO)->get();
        if ($raices->isEmpty()) {
            return null;
        }
        if ($raices->count() !== 1 || $raices->sole()->observacion !== PaqueteCognitivoInstitucional::FUNDAMENTO) {
            throw new DomainException('La familia institucional no tiene una raíz adoptada inequívoca.');
        }
        $pendientes = $raices;
        $vistos = $vigentes = [];
        while ($pendientes->isNotEmpty()) {
            $ids = [];
            foreach ($pendientes as $v) {
                if (isset($vistos[$v->getKey()])) {
                    throw new DomainException('Ciclo en la familia institucional.');
                }
                $vistos[$v->getKey()] = true;
                $ids[] = $v->getKey();
                if ($v->estado === 'ACTIVO' && $v->fecha_hora_retiro === null && $v->fecha_hora_vigencia !== null
                    && CarbonImmutable::parse($v->getRawOriginal('fecha_hora_vigencia'))->lessThanOrEqualTo($corte)) {
                    $vigentes[] = $v->getKey();
                }
            }
            $pendientes = VersionModeloExperto::query()->whereIn('cod_version_anterior', $ids)->get();
        }
        if (count($vigentes) > 1) {
            throw new DomainException('Hay varias revisiones institucionales vigentes; debe conciliarse su gobierno.');
        }

        return $vigentes[0] ?? null;
    }

    public function porVersion(string $version): array
    {
        $vistos = [];
        $descendientes = [];
        do {
            if (isset($vistos[$version])) {
                throw new DomainException('Ciclo en el linaje del contrato de conocimiento.');
            }
            $vistos[$version] = true;
            $contrato = config('sistema_experto.contratos_conocimiento.'.$version);
            $cabecera = VersionModeloExperto::query()->whereKey($version)->sole();
            if ($contrato === null && $cabecera->codigo_version === PaqueteCognitivoInstitucional::CODIGO
                && $cabecera->observacion === PaqueteCognitivoInstitucional::FUNDAMENTO) {
                $contrato = PaqueteCognitivoInstitucional::contratos();
                $fuentes = app(FuentesObservacionalesCognitivas::class)->vinculos();
                $mapas = DB::table('mapeos_variables_fuente as m')->join('variables_expertas as v', 'v.cod_variable_experta', '=', 'm.cod_variable_experta')
                    ->join('nodos_semanticos as n', 'n.cod_nodo_semantico', '=', 'v.cod_nodo_semantico')
                    ->where('m.cod_version_modelo', $version)->get(['m.cod_mapeo_variable_fuente', 'm.clave_selector', 'n.codigo_semantico']);
                foreach ($mapas as $mapa) {
                    $componente = str_starts_with($mapa->codigo_semantico, 'VAR-OBS-') ? substr($mapa->codigo_semantico, 8) : null;
                    if (isset($fuentes[$componente]) && $fuentes[$componente]['cod_pregunta'] === $mapa->clave_selector) {
                        $contrato['vinculos_instrumentales'][$mapa->cod_mapeo_variable_fuente] = $fuentes[$componente];
                    }
                }
            }
            if (is_array($contrato) && is_array($contrato['relaciones'] ?? null)
                && is_array($contrato['extracciones'] ?? null) && is_string($contrato['tipo_regla_resultado'] ?? null)) {
                foreach (array_reverse($descendientes) as $sucesora) {
                    if (is_array($contrato['vinculos_instrumentales'] ?? null) && $contrato['vinculos_instrumentales'] !== []) {
                        $contrato['vinculos_instrumentales'] = $this->trasladarVinculos($contrato['vinculos_instrumentales'], $version, $sucesora);
                    }
                    $version = $sucesora;
                }
                foreach ($contrato['vinculos_instrumentales'] ?? [] as $id => $vinculo) {
                    $perfil = $vinculo['perfil_interpretativo'] ?? null;
                    if (is_string($perfil)) {
                        $contrato['vinculos_instrumentales'][$id]['perfil_interpretativo'] = config('sistema_experto.perfiles_interpretativos.'.$perfil);
                    }
                }

                return $contrato;
            }
            $descendientes[] = $version;
            $version = $cabecera->cod_version_anterior;
        } while ($version !== null);
        throw new DomainException('Falta el contrato semántico del servidor para esta familia de conocimiento. Puede archivar reglas sin inventar ese contrato.');
    }

    /** Las PK se regeneran en cada revisión; el vínculo conserva toda su identidad semántica. */
    private function trasladarVinculos(array $vinculos, string $anterior, string $sucesora): array
    {
        $originales = $this->mapeos($anterior)->keyBy('cod_mapeo_variable_fuente');
        $destinos = $this->mapeos($sucesora)->groupBy(fn ($m) => $this->identidad($m));
        $traducidos = [];
        foreach ($vinculos as $id => $vinculo) {
            $original = $originales->get($id);
            if (! is_string($id) || ! is_array($vinculo) || $original === null
                || ($vinculo['cod_pregunta'] ?? null) !== $original->clave_selector) {
                throw new DomainException('El vínculo configurado no identifica el mapeo original de su versión.');
            }
            $candidatos = $destinos->get($this->identidad($original));
            if ($candidatos === null || $candidatos->count() !== 1) {
                throw new DomainException('El vínculo heredado no tiene un mapeo semántico único en la sucesora.');
            }
            $traducidos[$candidatos->sole()->cod_mapeo_variable_fuente] = $vinculo;
        }

        return $traducidos;
    }

    private function mapeos(string $version): Collection
    {
        return DB::table('mapeos_variables_fuente as m')
            ->join('variables_expertas as v', 'v.cod_variable_experta', '=', 'm.cod_variable_experta')
            ->join('nodos_semanticos as n', 'n.cod_nodo_semantico', '=', 'v.cod_nodo_semantico')
            ->join('fuentes_datos_expertas as f', 'f.cod_fuente_dato_experta', '=', 'm.cod_fuente_dato_experta')
            ->where('m.cod_version_modelo', $version)->where('v.cod_version_modelo', $version)
            ->where('n.cod_version_modelo', $version)->where('f.cod_version_modelo', $version)
            ->get(['m.cod_mapeo_variable_fuente', 'm.tipo_extraccion', 'm.campo_valor', 'm.clave_selector',
                'n.codigo_semantico', 'f.codigo_fuente']);
    }

    private function identidad(object $m): string
    {
        return serialize([$m->codigo_semantico, $m->codigo_fuente, $m->tipo_extraccion, $m->campo_valor, $m->clave_selector]);
    }
}
