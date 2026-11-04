<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios;

use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use MotorExperto\dominio\orion\Entities\Conocimiento\RedSemantica;
use MotorExperto\dominio\orion\Repositories\Conocimiento\RedSemanticaRepositoryInterface;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorRedSemantica;
use DomainException;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

/** Recupera la red semántica de la versión solicitada. */
final readonly class RedSemanticaEloquentRepository implements RedSemanticaRepositoryInterface
{
    /**
     * Configura la lectura sin consultar ni registrar servicios.
     * @param MapeadorRedSemantica $mapeador
     * @param string $conexionExperimental
     * @throws DomainException
     */
    public function __construct(
        /** Conversor del grafo. */ private MapeadorRedSemantica $mapeador,
        /** Destino experimental obligatorio. */ private string $conexionExperimental,
    ) {
        self::validarIdentidades($conexionExperimental);
    }

    /**
     * Recupera una instantánea sin alterar estados físicos.
     * @param string $versionConocimiento
     * @return RedSemantica|null
     * @throws DomainException
     * @throws RuntimeException
     */
    public function porVersion(string $versionConocimiento): ?RedSemantica
    {
        self::validarIdentidades($versionConocimiento);
        $fila = VersionModeloExperto::on($this->conexion())->whereKey($versionConocimiento)->with([
            'nodosSemanticos' => static fn (HasMany $q): HasMany => $q->orderBy('cod_nodo_semantico'),
            'relacionesSemanticas' => static fn (HasMany $q): HasMany => $q->orderBy('cod_relacion_semantica'),
        ])->first();

        return $fila === null ? null : $this->mapeador->aDominio($fila);
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
