<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion;

use App\Compartido\Infraestructura\Persistencia\GeneradorCodigo;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\ContextoSimulacionLocal;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\CriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\MapeoFuentes\ExtractorVariableMapeada;
use DomainException;

/**
 * TÍTULO:
 * Vocabulario técnico de conservación ORION-SNAP-2.
 *
 * CAPA:
 * Infrastructure — Persistence.
 *
 * ¿PARA QUÉ SIRVE?
 * Prepara explícitamente los catálogos experimentales aprobados.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Versión y usuario existentes, conexión desechable explícita.
 *
 * ¿QUÉ HACE?
 * Reutiliza nodos, dominios, valores y contratos físicos D-137.
 *
 * ¿QUÉ DEVUELVE?
 * Un catálogo versionado sin activar reglas ni uso clínico.
 *
 * ¿QUÉ NO DEBE HACER?
 * No crea usuarios/versiones de conveniencia ni cambia G09.
 */
final class CatalogoConservacionExperta
{
    public const VERSION = 'ORION-SNAP-2';

    /**
     * Instala el vocabulario explícitamente, nunca como fallback de guardar().
     *
     * @param  string  $conexion  Destino desechable autorizado.
     * @param  string  $version  Versión existente que será propietaria del catálogo.
     * @param  string  $usuario  Actor existente de la carga técnica.
     * @param  ContextoSimulacionLocal|null  $simulacionLocal  Capacidad optativa limitada al paquete local.
     * @return void Catálogo preparado idempotentemente sin activar reglas.
     *
     * @throws DomainException Por versión o actor inexistentes.
     * @throws \RuntimeException Por destino no autorizado o fallo SQL, con rollback.
     */
    public static function preparar(string $conexion, string $version, string $usuario, ?ContextoSimulacionLocal $simulacionLocal = null): void
    {
        $db = $simulacionLocal?->db($conexion) ?? ExtractorVariableMapeada::conexionSegura($conexion);
        $simulacionLocal?->validarVersion($version);
        $db->transaction(function () use ($db, $conexion, $version, $usuario): void {
            if (! $db->table('versiones_modelo_experto')->where('cod_version_modelo', $version)->lockForUpdate()->exists()
                || ! $db->table('usuarios')->where('cod_usuario', $usuario)->exists()) {
                throw new DomainException('El catálogo exige versión y usuario explícitos existentes.');
            }
            $grupos = [
                'DOMINIO' => [DominioCognitivoEnum::cases(), EstadoDominioEnum::cases()],
                'SUBCOMPONENTE' => [SubcomponenteCognitivoEnum::cases(), HallazgoInstrumentalEnum::cases()],
                'DCL' => [CriterioDclEnum::cases(), EstadoCriterioDclEnum::cases()],
            ];
            $g = new GeneradorCodigo;
            $fecha = now()->format('Y-m-d H:i:s');
            foreach ($grupos as $tipo => [$criterios, $valores]) {
                $codigo = 'SNAP:'.$tipo;
                $dominio = $db->table('dominios_valores_expertos')->where('cod_version_modelo', $version)->where('codigo_dominio', $codigo)->first();
                if ($dominio === null) {
                    $id = $g->siguiente('dominios_valores_expertos', $conexion);
                    $db->table('dominios_valores_expertos')->insert(['cod_dominio_valores' => $id, 'cod_version_modelo' => $version,
                        'codigo_dominio' => $codigo, 'nombre' => $codigo, 'descripcion' => self::VERSION,
                        'tipo_dominio' => 'CATEGORICO', 'estado' => 'ACTIVO', 'fecha_hora_creacion' => $fecha, 'cod_usuario_creacion' => $usuario]);
                    foreach ($valores as $valor) {
                        $db->table('valores_semanticos')->insert(['cod_valor_semantico' => $g->siguiente('valores_semanticos', $conexion),
                            'cod_dominio_valores' => $id, 'codigo_valor' => $valor->value, 'nombre' => $valor->value,
                            'definicion_semantica' => self::VERSION.' — conservación experimental sin autorización clínica.',
                            'estado_aprobacion' => 'EXPERIMENTAL', 'estado' => 'ACTIVO', 'fecha_hora_creacion' => $fecha, 'cod_usuario_creacion' => $usuario]);
                    }
                } else {
                    $id = $dominio->cod_dominio_valores;
                }
                foreach ($criterios as $criterio) {
                    $nodo = $db->table('nodos_semanticos')->where('cod_version_modelo', $version)->where('codigo_semantico', $codigo.':'.$criterio->value)->first();
                    if ($nodo !== null) {
                        continue;
                    }
                    $n = $g->siguiente('nodos_semanticos', $conexion);
                    $db->table('nodos_semanticos')->insert(['cod_nodo_semantico' => $n, 'cod_version_modelo' => $version,
                        'codigo_semantico' => $codigo.':'.$criterio->value, 'nombre' => $codigo.':'.$criterio->value,
                        'tipo_nodo' => 'CRITERIO', 'definicion' => self::VERSION, 'estado' => 'ACTIVO',
                        'fecha_hora_creacion' => $fecha, 'cod_usuario_creacion' => $usuario]);
                    $db->table('criterios_dominios_resultado')->insert(['cod_criterio_dominio_resultado' => $g->siguiente('criterios_dominios_resultado', $conexion),
                        'cod_nodo_criterio' => $n, 'cod_dominio_valores' => $id, 'estado' => 'ACTIVO',
                        'fecha_hora_creacion' => $fecha, 'cod_usuario_creacion' => $usuario]);
                }
            }
        });
    }

    /**
     * @param  string  $conexion  Destino desechable autorizado.
     * @param  string  $version  Versión exacta propietaria.
     * @param  ContextoSimulacionLocal|null  $simulacionLocal  Capacidad optativa limitada al paquete local.
     * @return array<string, array{nodo:string, valores:array<string,string>}> Contratos experimentales previamente instalados y habilitados.
     *
     * @throws \RuntimeException Por destino no autorizado o fallo SQL.
     */
    public static function leer(string $conexion, string $version, ?ContextoSimulacionLocal $simulacionLocal = null): array
    {
        $db = $simulacionLocal?->db($conexion) ?? ExtractorVariableMapeada::conexionSegura($conexion);
        $simulacionLocal?->validarVersion($version);
        $filas = $db->table('nodos_semanticos as n')->join('criterios_dominios_resultado as c', 'c.cod_nodo_criterio', '=', 'n.cod_nodo_semantico')
            ->join('dominios_valores_expertos as d', 'd.cod_dominio_valores', '=', 'c.cod_dominio_valores')
            ->join('valores_semanticos as v', 'v.cod_dominio_valores', '=', 'd.cod_dominio_valores')
            ->where('n.cod_version_modelo', $version)->where('d.cod_version_modelo', $version)
            ->where('n.definicion', self::VERSION)->where('d.descripcion', self::VERSION)
            ->where('n.tipo_nodo', 'CRITERIO')->where('n.estado', 'ACTIVO')->where('c.estado', 'ACTIVO')
            ->where('d.estado', 'ACTIVO')->where('v.estado', 'ACTIVO')->where('v.estado_aprobacion', 'EXPERIMENTAL')
            ->select(['n.cod_nodo_semantico', 'n.codigo_semantico', 'v.cod_valor_semantico', 'v.codigo_valor'])->get();
        $r = [];
        foreach ($filas as $f) {
            $r[$f->codigo_semantico]['nodo'] = $f->cod_nodo_semantico;
            $r[$f->codigo_semantico]['valores'][$f->codigo_valor] = $f->cod_valor_semantico;
        }

        return $r;
    }
}
