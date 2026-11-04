<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores;

use App\Models\NodoSemantico as NodoModelo;
use App\Models\RelacionSemantica as RelacionModelo;
use App\Models\VersionModeloExperto;
use MotorExperto\dominio\Entities\Conocimiento\NodoSemantico;
use MotorExperto\dominio\Entities\Conocimiento\RedSemantica;
use MotorExperto\dominio\Entities\Conocimiento\RelacionSemantica;
use MotorExperto\dominio\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/** Reconstruye nodos y relaciones de una versión sin cambiar sus identificadores. */
final readonly class MapeadorRedSemantica
{
    /**
     * Construye la red de la versión con las relaciones explícitamente cargadas.
     * @param VersionModeloExperto $version
     * @return RedSemantica
     * @throws DomainException
     */
    public function aDominio(VersionModeloExperto $version): RedSemantica
    {
        $id = self::texto($version, 'cod_version_modelo');
        self::exigirActivo($version);
        $nodos = [];
        foreach (self::coleccion($version, 'nodosSemanticos') as $fila) {
            if (! $fila instanceof NodoModelo) {
                throw new DomainException('La colección de nodos contiene otro tipo de modelo.');
            }
            $nodo = $this->aNodo($fila);
            if ($nodo->versionConocimiento !== $id || isset($nodos[$nodo->id])) {
                throw new DomainException('Nodo duplicado o de otra versión.');
            }
            $nodos[$nodo->id] = $nodo;
        }
        $relaciones = [];
        foreach (self::coleccion($version, 'relacionesSemanticas') as $fila) {
            if (! $fila instanceof RelacionModelo || self::texto($fila, 'cod_version_modelo') !== $id) {
                throw new DomainException('Relación mal tipada o de otra versión.');
            }
            $relaciones[] = $this->aRelacion($fila, $nodos);
        }

        return new RedSemantica($id, array_values($nodos), $relaciones);
    }

    /**
     * Proyecta un concepto sin normalizar sus identificadores ni su definición.
     * @param NodoModelo $fila
     * @return NodoSemantico
     * @throws DomainException
     */
    public function aNodo(NodoModelo $fila): NodoSemantico
    {
        self::exigirActivo($fila);

        return new NodoSemantico(
            self::texto($fila, 'cod_nodo_semantico'),
            self::texto($fila, 'cod_version_modelo'),
            self::texto($fila, 'codigo_semantico'),
            self::texto($fila, 'nombre'),
            self::texto($fila, 'tipo_nodo'),
            self::texto($fila, 'definicion'),
        );
    }

    /**
     * Construye una arista utilizando las instancias originales del grafo.
     * @param RelacionModelo $fila
     * @param array<string, NodoSemantico> $nodos
     * @return RelacionSemantica
     * @throws DomainException
     */
    public function aRelacion(RelacionModelo $fila, array $nodos): RelacionSemantica
    {
        self::exigirActivo($fila);
        $atributos = $fila->getAttributes();
        if (! array_key_exists('modalidad_relacion', $atributos) || $atributos['modalidad_relacion'] !== null) {
            throw new DomainException('Domain no representa la modalidad de esta relación; no se puede omitir.');
        }
        $tipo = TipoRelacionSemanticaEnum::tryFrom(self::texto($fila, 'tipo_relacion'))
            ?? throw new DomainException('Predicado sin correspondencia exacta en Domain.');
        $origen = $nodos[self::texto($fila, 'cod_nodo_origen')] ?? null;
        $destino = $nodos[self::texto($fila, 'cod_nodo_destino')] ?? null;
        $version = self::texto($fila, 'cod_version_modelo');
        if (! $origen instanceof NodoSemantico || ! $destino instanceof NodoSemantico
            || $origen->versionConocimiento !== $version || $destino->versionConocimiento !== $version) {
            throw new DomainException('Los extremos de la arista deben existir en la misma versión.');
        }

        return new RelacionSemantica(self::texto($fila, 'cod_relacion_semantica'), $origen, $tipo, $destino);
    }

    /**
     * Lee texto físico sin accessors, casts ni sustitución de NULL.
     * @param Model $modelo
     * @param string $campo
     * @return string
     * @throws DomainException
     */
    private static function texto(Model $modelo, string $campo): string
    {
        $datos = $modelo->getAttributes();
        if (! array_key_exists($campo, $datos) || ! is_string($datos[$campo]) || trim($datos[$campo]) === '') {
            throw new DomainException('Atributo textual requerido: '.$campo.'.');
        }

        return $datos[$campo];
    }

    /**
     * Evita promover estados que la entidad de Domain no puede conservar.
     * @param Model $modelo
     * @return void
     * @throws DomainException
     */
    private static function exigirActivo(Model $modelo): void
    {
        if (self::texto($modelo, 'estado') !== 'ACTIVO') {
            throw new DomainException('El estado del conocimiento no es representable en la red de Domain.');
        }
    }

    /**
     * Exige una colección materializada y evita cualquier consulta implícita.
     * @param Model $modelo
     * @param string $relacion
     * @return Collection
     * @throws DomainException
     */
    private static function coleccion(Model $modelo, string $relacion): Collection
    {
        if (! $modelo->relationLoaded($relacion) || ! $modelo->getRelation($relacion) instanceof Collection) {
            throw new DomainException('Debe precargarse la relación '.$relacion.'.');
        }

        return $modelo->getRelation($relacion);
    }
}
