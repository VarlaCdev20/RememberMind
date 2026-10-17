<?php

declare(strict_types=1);

namespace App\SistemaExperto\Infrastructure\Persistence\Repositories;

use App\Models\ReglaExperta as ReglaModelo;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use App\SistemaExperto\Domain\Entities\Conocimiento\ReglaExperta;
use App\SistemaExperto\Domain\Repositories\Conocimiento\ReglaExpertaRepositoryInterface;
use App\SistemaExperto\Infrastructure\Persistence\Mappers\MapeadorReglas;
use DomainException;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

/**
 * TÍTULO:
 * Recuperación Eloquent de reglas versionadas.
 *
 * CAPA:
 * Infrastructure - Persistence - Repositories.
 *
 * ¿PARA QUÉ SIRVE?
 * Implementa el puerto de reglas del nuevo Domain sobre App\Models.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * ID y versión exactos, mapper y conexión experimental explícita.
 *
 * ¿QUÉ HACE?
 * Consulta con parámetros enlazados y precarga condiciones y consecuencias.
 * Conserva reglas inactivas; el mapper valida su habilitación original.
 *
 * ¿QUÉ DEVUELVE?
 * ReglaExperta, ausencia explícita o lista ordenada por ID.
 *
 * ¿QUÉ NO DEBE HACER?
 * No ejecuta reglas, elige otra versión, activa conocimiento ni escribe.
 * Solo opera en testing contra destinos permitidos por BaseTecnicaDesechable.
 */
final readonly class ReglaExpertaEloquentRepository implements ReglaExpertaRepositoryInterface
{
    /**
     * Recibe dependencias sin abrir una conexión ni registrar bindings.
     *
     * @param  MapeadorReglas  $mapeador  Conversor del contrato versionado.
     * @param  string  $conexionExperimental  Nombre de conexión segregada.
     *
     * @throws DomainException Si el nombre de conexión es inválido.
     */
    public function __construct(
        /** Conversión sin inferencia. */ private MapeadorReglas $mapeador,
        /** Destino obligatorio; no hereda la conexión institucional. */ private string $conexionExperimental,
    ) {
        self::validarIdentidades($conexionExperimental);
    }

    /**
     * Recupera una regla únicamente dentro de su versión solicitada.
     *
     * @param  string  $id  PK original.
     * @param  string  $versionConocimiento  Versión propietaria exacta.
     * @return ReglaExperta|null Regla convertida o ausencia dentro de esa versión.
     *
     * @throws DomainException Por identidad inválida o conocimiento incompatible.
     * @throws RuntimeException Por destino no autorizado o fallo de lectura Eloquent.
     */
    public function obtener(string $id, string $versionConocimiento): ?ReglaExperta
    {
        self::validarIdentidades($id, $versionConocimiento);
        $fila = $this->consulta($versionConocimiento)->whereKey($id)->first();

        return $fila === null ? null : $this->mapeador->aDominio($fila);
    }

    /**
     * Entrega las reglas originales, incluidas las no habilitadas.
     *
     * @param  string  $versionConocimiento  Versión exacta, sin fallback.
     * @return list<ReglaExperta> Reglas únicas ordenadas lexicográficamente por PK.
     *
     * @throws DomainException Por versión o dependencia incompatible.
     * @throws RuntimeException Por destino no autorizado o fallo técnico.
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
     *
     * @param  string  $versionConocimiento  Versión ya validada.
     * @return Builder<ReglaModelo> Consulta acotada; no carga usuarios ni datos personales.
     *
     * @throws RuntimeException Por conexión experimental no autorizada.
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
