<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios;

use App\Models\ReglaExperta as ReglaModelo;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use MotorExperto\dominio\orion\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\orion\Repositories\Conocimiento\ReglaExpertaRepositoryInterface;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorReglas;
use DomainException;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

/** Recupera las reglas registradas y sus condiciones por versión. */
final readonly class ReglaExpertaEloquentRepository implements ReglaExpertaRepositoryInterface
{
    /**
     * Recibe dependencias sin abrir una conexión ni registrar bindings.
     * @param MapeadorReglas $mapeador
     * @param string $conexionExperimental
     * @throws DomainException
     */
    public function __construct(
        /** Conversión sin inferencia. */ private MapeadorReglas $mapeador,
        /** Destino obligatorio; no hereda la conexión institucional. */ private string $conexionExperimental,
    ) {
        self::validarIdentidades($conexionExperimental);
    }

    /**
     * Recupera una regla únicamente dentro de su versión solicitada.
     * @param string $id
     * @param string $versionConocimiento
     * @return ReglaExperta|null
     * @throws DomainException
     * @throws RuntimeException
     */
    public function obtener(string $id, string $versionConocimiento): ?ReglaExperta
    {
        self::validarIdentidades($id, $versionConocimiento);
        $fila = $this->consulta($versionConocimiento)->whereKey($id)->first();

        return $fila === null ? null : $this->mapeador->aDominio($fila);
    }

    /**
     * Entrega las reglas originales, incluidas las no habilitadas.
     * @param string $versionConocimiento
     * @return list<ReglaExperta>
     * @throws DomainException
     * @throws RuntimeException
     */
    public function porVersion(string $versionConocimiento): array
    {
        self::validarIdentidades($versionConocimiento);
        $reglas = [];
        foreach ($this->consulta($versionConocimiento)->orderBy('cod_regla_experta')->get() as $fila) {
            $reglas[] = $this->mapeador->aDominio($fila);
        }
        // strcmp fija el orden del contrato también con collations distintas.
        usort($reglas, static fn (ReglaExperta $a, ReglaExperta $b): int => strcmp($a->id, $b->id));

        return $reglas;
    }

    /**
     * Prepara una lectura con todas las dependencias requeridas por el mapper.
     * @param string $versionConocimiento
     * @return Builder<ReglaModelo>
     * @throws RuntimeException
     */
    private function consulta(string $versionConocimiento): Builder
    {
        return ReglaModelo::on($this->conexion())->where('cod_version_modelo', $versionConocimiento)->with([
            'version',
            'condicionesReglaExperta' => static fn (HasMany $q): HasMany => $q->orderBy('cod_condicion_regla'),
            'condicionesReglaExperta.variable.nodo',
            'condicionesReglaExperta.valor.dominio',
            'consecuenciasReglaExperta' => static fn (HasMany $q): HasMany => $q->orderBy('cod_consecuencia_regla'),
            'consecuenciasReglaExperta.criterioDominioResultado.criterio',
            'consecuenciasReglaExperta.valor.dominio',
        ]);
    }

    /**
     * Exige identidades explícitas sin normalizarlas ni interpolarlas en SQL.
     * @param string ...$ids
     * @return void
     * @throws DomainException
     */
    private static function validarIdentidades(string ...$ids): void
    {
        foreach ($ids as $id) {
            if ($id === '' || preg_match('/[\x00-\x20\x7F]/', $id) === 1) {
                throw new DomainException('Se requieren identidades y versiones explícitas.');
            }
        }
    }

    /**
     * Resuelve únicamente el destino experimental nombrado por el llamador.
     * @return string
     * @throws RuntimeException
     */
    private function conexion(): string
    {
        $contenedor = Container::getInstance();
        if (! $contenedor->bound('env') || $contenedor->make('env') !== 'testing') {
            throw new RuntimeException('El repositorio experimental solo está habilitado en testing.');
        }
        $conexion = Model::resolveConnection($this->conexionExperimental);
        if (! BaseTecnicaDesechable::permite($conexion->getDriverName(), $conexion->getDatabaseName(),
            $conexion->getConfig('username'), $conexion->getConfig('host'))) {
            throw new RuntimeException('La conexión no corresponde a una base técnica desechable autorizada.');
        }

        return $this->conexionExperimental;
    }
}
