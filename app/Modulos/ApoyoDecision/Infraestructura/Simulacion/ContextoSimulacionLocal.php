<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Simulacion;

use App\Models\Residente;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Integración local autorizada.
 * TÍTULO: Contexto cerrado de simulación.
 * CAPA: Infraestructura de ApoyoDecision.
 * ¿PARA QUÉ SIRVE? Limita entorno, destino, versión y cohorte antes de cada operación.
 * ¿QUÉ INFORMACIÓN RECIBE? Conexión explícita, identidades físicas y alias sintéticos.
 * ¿QUÉ HACE? Revalida la capacidad y mantiene la FK del residente original.
 * ¿QUÉ DEVUELVE? Conexión autorizada o identidad comprobada, sin crear referencias.
 * ¿QUÉ NO DEBE HACER? No cambia APP_ENV, permisos o contratos de publicación clínica.
 */
final readonly class ContextoSimulacionLocal
{
    public const VERSION = 'SIM-ORION-LOCAL-1';

    /** Referencias del oráculo sintético 03A, independientes de los antecedentes descriptivos. */
    public const CASOS = [
        '99000001' => ['caso' => 'CAS-01', 'dominios' => ['A', 'P', 'P', 'P', 'P'], 'c02' => 'ACREDITADO', 'c03' => 'ACREDITADO'],
        '99000010' => ['caso' => 'CAS-02', 'dominios' => ['A', 'A', 'P', 'P', 'P'], 'c02' => 'ACREDITADO', 'c03' => 'ACREDITADO'],
        '99000005' => ['caso' => 'CAS-08', 'dominios' => ['A', 'A', 'A', 'A', 'A'], 'c02' => 'ACREDITADO', 'c03' => 'ACREDITADO'],
        '99000009' => ['caso' => 'CAS-10', 'dominios' => ['P', 'P', 'P', 'P', 'P'], 'c02' => 'NO_ACREDITADO', 'c03' => 'ACREDITADO'],
        '99000003' => ['caso' => 'CAS-20', 'dominios' => ['A', 'A', 'P', 'P', 'P'], 'c02' => 'ACREDITADO', 'c03' => 'PENDIENTE'],
    ];

    public function __construct(public string $conexion) {}

    /** Revalida entorno, destino y habilitación en cada operación; no modifica APP_ENV. */
    public function db(?string $conexionEsperada = null): Connection
    {
        if ($conexionEsperada !== null && $conexionEsperada !== $this->conexion) {
            throw new RuntimeException('Capacidad y conexión solicitada diferentes.');
        }
        if (config('sistema_experto.simulacion_local.habilitada') !== true) {
            throw new RuntimeException('La simulación local no está habilitada explícitamente.');
        }
        $db = DB::connection($this->conexion);
        $testing = app()->environment('testing') && BaseTecnicaDesechable::permite(
            $db->getDriverName(), $db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'));
        $local = app()->environment('local') && $db->getDriverName() === 'pgsql'
            && in_array($db->getConfig('host'), ['127.0.0.1', 'localhost', '::1'], true)
            && $db->getDatabaseName() === 'remembermind_dev'
            && config('sistema_experto.simulacion_local.base_permitida') === 'remembermind_dev';
        if (! $testing && ! $local) {
            throw new RuntimeException('Destino de simulación no autorizado; producción permanece cerrada.');
        }

        return $db;
    }

    /** La cuenta y la Policy se comprueban en la entrada; esta capa limita la cohorte física. */
    public function residente(string $id): Residente
    {
        $this->db();
        $r = Residente::on($this->conexion)->whereKey($id)->first();
        if (! $r || ! preg_match('/^COD_RES_([0-9]{6})$/D', $id)
            || $r->estado !== 'ADMITIDO' || ! isset(self::CASOS[$r->numero_documento])) {
            throw new RuntimeException('Residente fuera de la cohorte ficticia autorizada.');
        }

        return $r;
    }

    public function alias(string $fisico): string
    {
        $this->residente($fisico);

        return 'SINTETICO-'.substr($fisico, 8);
    }

    public function fisico(string $alias): string
    {
        if (! preg_match('/^SINTETICO-([0-9]{6})$/D', $alias, $m)) {
            throw new RuntimeException('Alias de simulación inválido.');
        }
        $id = 'COD_RES_'.$m[1];
        $this->residente($id);

        return $id;
    }

    public function validarVersion(string $id): void
    {
        if (! $this->db()->table('versiones_modelo_experto')->where('cod_version_modelo', $id)
            ->where('codigo_version', self::VERSION)->where('estado', 'ACTIVO')->exists()) {
            throw new RuntimeException('La capacidad local solo admite su paquete de simulación versionado.');
        }
    }
}
