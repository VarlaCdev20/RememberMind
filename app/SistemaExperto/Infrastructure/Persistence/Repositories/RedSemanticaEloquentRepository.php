<?php

declare(strict_types=1);

namespace App\SistemaExperto\Infrastructure\Persistence\Repositories;

use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\ContextoSimulacionLocal;
use App\SistemaExperto\Domain\Entities\Conocimiento\RedSemantica;
use App\SistemaExperto\Domain\Repositories\Conocimiento\RedSemanticaRepositoryInterface;
use App\SistemaExperto\Infrastructure\Persistence\Mappers\MapeadorRedSemantica;
use DomainException;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

/**
 * TÍTULO:
 * Recuperación Eloquent de una red semántica.
 *
 * CAPA:
 * Infrastructure - Persistence - Repositories.
 *
 * ¿PARA QUÉ SIRVE?
 * Implementa el puerto de red utilizando la versión física solicitada.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Versión explícita, mapper y conexión experimental segregada.
 *
 * ¿QUÉ HACE?
 * Precarga nodos y relaciones por sus FK existentes y los pasa al mapper.
 *
 * ¿QUÉ DEVUELVE?
 * RedSemantica o null exclusivamente cuando la versión no existe.
 * Una versión existente sin nodos conserva su red vacía.
 *
 * ¿QUÉ NO DEBE HACER?
 * No filtra estados para ocultar incompatibilidades, completa aristas,
 * interpreta modalidades ni consulta otra versión o una base institucional.
 */
final readonly class RedSemanticaEloquentRepository implements RedSemanticaRepositoryInterface
{
    /**
     * Configura la lectura sin consultar ni registrar servicios.
     *
     * @param  MapeadorRedSemantica  $mapeador  Conversor existente.
     * @param  string  $conexionExperimental  Conexión técnica explícita.
     * @param  ContextoSimulacionLocal|null  $simulacionLocal  Capacidad explícita del lote ficticio autorizado; null conserva el cierre a testing.
     *
     * @throws DomainException Por nombre de conexión inválido.
     */
    public function __construct(
        /** Conversor del grafo. */ private MapeadorRedSemantica $mapeador,
        /** Destino experimental obligatorio. */ private string $conexionExperimental,
        /** Capacidad optativa exclusiva del paquete local autorizado. */ private ?ContextoSimulacionLocal $simulacionLocal = null,
    ) {
        self::validarIdentidades($conexionExperimental);
    }

    /**
     * Recupera una instantánea sin alterar estados físicos.
     *
     * @param  string  $versionConocimiento  PK de la versión exacta.
     * @return RedSemantica|null Grafo validado o versión ausente.
     *
     * @throws DomainException Por versión inválida, modalidades o grafo incompatibles.
     * @throws RuntimeException Por destino no autorizado o fallo técnico de Eloquent.
     */
    public function porVersion(string $versionConocimiento): ?RedSemantica
    {
        $this->simulacionLocal?->validarVersion($versionConocimiento);
        self::validarIdentidades($versionConocimiento);
        $fila = VersionModeloExperto::on($this->conexion())->whereKey($versionConocimiento)->with([
            'nodosSemanticos' => static fn (HasMany $q): HasMany => $q->orderBy('cod_nodo_semantico'),
            'relacionesSemanticas' => static fn (HasMany $q): HasMany => $q->orderBy('cod_relacion_semantica'),
        ])->first();

        return $fila === null ? null : $this->mapeador->aDominio($fila);
    }

    /**
     * Exige identidades explícitas sin normalizarlas ni interpolarlas en SQL.
     *
     * @param  string  ...$ids  Códigos recibidos por el contrato.
     * @return void No consulta la base.
     *
     * @throws DomainException Por código vacío o con separadores/control.
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
     *
     * @return string Nombre de conexión, nunca el predeterminado implícito.
     *
     * @throws RuntimeException Fuera de testing o de las bases desechables autorizadas.
     */
    private function conexion(): string
    {
        if ($this->simulacionLocal !== null) {
            $this->simulacionLocal->db($this->conexionExperimental);

            return $this->conexionExperimental;
        }
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
