<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores;

use App\Models\NodoSemantico as NodoModelo;
use App\Models\RelacionSemantica as RelacionModelo;
use App\Models\VersionModeloExperto;
use MotorExperto\dominio\orion\Entities\Conocimiento\NodoSemantico;
use MotorExperto\dominio\orion\Entities\Conocimiento\RedSemantica;
use MotorExperto\dominio\orion\Entities\Conocimiento\RelacionSemantica;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * TÍTULO:
 * Conversión de la red semántica persistida.
 *
 * CAPA:
 * Infrastructure - Persistence - Mappers.
 *
 * ¿PARA QUÉ SIRVE?
 * Reconstruye conceptos y aristas de una versión sin cambiar sus PK ni códigos.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Modelos App\Models de versión, nodos y relaciones, con colecciones ya cargadas.
 *
 * ¿QUÉ HACE?
 * Comprueba versión, identidad, estado y referencias; reutiliza los mismos nodos
 * al construir aristas y delega la integridad del grafo en RedSemantica.
 *
 * ¿QUÉ DEVUELVE?
 * NodoSemantico, RelacionSemantica o RedSemantica del nuevo Domain.
 *
 * ¿QUÉ NO DEBE HACER?
 * No consulta ni modifica modelos. No activa conocimiento inactivo ni convierte
 * modalidades de relación en enlaces genéricos: Domain no representa esos estados.
 * Este contrato admite únicamente filas ACTIVO y modalidad null.
 */
final readonly class MapeadorRedSemantica
{
    /**
     * Construye la red de la versión con las relaciones explícitamente cargadas.
     *
     * @param  VersionModeloExperto  $version  Versión con nodosSemanticos y relacionesSemanticas.
     * @return RedSemantica Grafo que conserva identidades físicas y códigos semánticos.
     *
     * @throws DomainException Por carga incompleta, versión/estado ajenos o duplicación.
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
     *
     * @param  NodoModelo  $fila  Nodo físico, sin necesidad de carga diferida.
     * @return NodoSemantico Concepto inmutable con PK y código originales.
     *
     * @throws DomainException Por atributo faltante, estado no representable o datos inválidos.
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
     *
     * @param  RelacionModelo  $fila  Arista física de modalidad null.
     * @param  array<string, NodoSemantico>  $nodos  Nodos previamente convertidos por PK.
     * @return RelacionSemantica Arista dirigida, sin inferir inversas.
     *
     * @throws DomainException Por predicado, modalidad, FK o versión incompatibles.
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
     *
     * @param  Model  $modelo  Registro ya materializado.
     * @param  string  $campo  Columna fija utilizada por este mapper.
     * @return string Texto original no vacío.
     *
     * @throws DomainException Por columna ausente, tipo incompatible o texto vacío.
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
     *
     * @param  Model  $modelo  Fila de conocimiento.
     * @return void No cambia el estado físico.
     *
     * @throws DomainException Cuando el estado no es ACTIVO.
     */
    private static function exigirActivo(Model $modelo): void
    {
        if (self::texto($modelo, 'estado') !== 'ACTIVO') {
            throw new DomainException('El estado del conocimiento no es representable en la red de Domain.');
        }
    }

    /**
     * Exige una colección materializada y evita cualquier consulta implícita.
     *
     * @param  Model  $modelo  Modelo propietario.
     * @param  string  $relacion  Nombre fijo declarado por App\Models.
     * @return Collection Colección original ya cargada.
     *
     * @throws DomainException Por relación ausente o valor de otro tipo.
     */
    private static function coleccion(Model $modelo, string $relacion): Collection
    {
        if (! $modelo->relationLoaded($relacion) || ! $modelo->getRelation($relacion) instanceof Collection) {
            throw new DomainException('Debe precargarse la relación '.$relacion.'.');
        }

        return $modelo->getRelation($relacion);
    }
}
