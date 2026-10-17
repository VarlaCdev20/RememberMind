<?php

declare(strict_types=1);

namespace App\SistemaExperto\Infrastructure\Persistence\Mappers;

use App\Models\CondicionReglaExperta;
use App\Models\ConsecuenciaReglaExperta;
use App\Models\CriterioDominioResultado;
use App\Models\DominioValoresExperto;
use App\Models\NodoSemantico;
use App\Models\ReglaExperta as ReglaModelo;
use App\Models\ValorSemantico;
use App\Models\VariableExperta;
use App\Models\VersionModeloExperto;
use App\SistemaExperto\Domain\Entities\Conocimiento\ReglaExperta;
use App\SistemaExperto\Domain\ValueObjects\Conocimiento\OperadorCondicionEnum;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * TÍTULO:
 * Conversión de reglas relacionales a conocimiento declarativo.
 *
 * CAPA:
 * Infrastructure - Persistence - Mappers.
 *
 * ¿PARA QUÉ SIRVE?
 * Traduce condiciones positivas D-130 y una consecuencia a la gramática de Domain.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * ReglaExperta de App\Models con versión, condiciones, consecuencias y sus
 * dependencias ya cargadas; tipo de regla previsto por el contrato versionado.
 *
 * ¿QUÉ HACE?
 * Valida PK/FK, dominios de valores y versiones. Conserva los códigos semánticos
 * y expresa la conjunción mediante Y e IGUAL_A, sin evaluar las condiciones.
 *
 * ¿QUÉ DEVUELVE?
 * Una ReglaExperta inmutable; su fuente identifica la fila física original.
 *
 * ¿QUÉ NO DEBE HACER?
 * No consulta, activa, ejecuta ni edita conocimiento. No inventa operadores:
 * D-130 admite condiciones positivas conjuntivas. Domain solo admite una
 * consecuencia; cero o varias se rechazan sin truncar el contrato físico.
 */
final readonly class MapeadorReglas
{
    /**
     * Recibe el tipo soportado desde el contrato de conocimiento, sin elegirlo.
     *
     * @param  string  $tipoReglaResultado  Tipo exacto definido por el paquete versionado.
     *
     * @throws DomainException Si el contrato no identifica el tipo de regla.
     */
    public function __construct(
        /** Tipo técnico explícito; no constituye autorización clínica. */ private string $tipoReglaResultado,
    ) {
        if ($tipoReglaResultado === '' || preg_match('/[\x00-\x20\x7F]/', $tipoReglaResultado) === 1) {
            throw new DomainException('Se requiere el tipo de regla del contrato versionado.');
        }
    }

    /**
     * Reconstruye la regla sin consultar relaciones que no hayan sido cargadas.
     *
     * @param  ReglaModelo  $fila  Regla con su versión y dependencias completas.
     * @return ReglaExperta Condiciones declarativas y consecuencia exacta.
     *
     * @throws DomainException Por tipo, estado, FK, versión, carga o cardinalidad incompatibles.
     */
    public function aDominio(ReglaModelo $fila): ReglaExperta
    {
        $id = self::texto($fila, 'cod_regla_experta');
        $version = self::texto($fila, 'cod_version_modelo');
        $modeloVersion = self::relacion($fila, 'version', VersionModeloExperto::class);
        $habilitada = match (self::texto($fila, 'estado')) {
            'ACTIVO' => true,
            'INACTIVO' => false,
            default => throw new DomainException('Estado de regla sin representación exacta en Domain.'),
        };
        if (self::texto($modeloVersion, 'cod_version_modelo') !== $version
            || self::texto($fila, 'tipo_regla') !== $this->tipoReglaResultado
            || ($habilitada && self::texto($modeloVersion, 'estado') !== 'ACTIVO')) {
            throw new DomainException('Tipo o versión de regla incompatibles con el contrato.');
        }
        $condiciones = $ids = $atomos = [];
        foreach (self::coleccion($fila, 'condicionesReglaExperta') as $condicion) {
            if (! $condicion instanceof CondicionReglaExperta || self::texto($condicion, 'cod_regla_experta') !== $id) {
                throw new DomainException('Condición mal tipada o perteneciente a otra regla.');
            }
            $condicionId = self::texto($condicion, 'cod_condicion_regla');
            $variable = self::relacion($condicion, 'variable', VariableExperta::class);
            $valor = self::relacion($condicion, 'valor', ValorSemantico::class);
            $nodo = self::relacion($variable, 'nodo', NodoSemantico::class);
            if (self::texto($variable, 'cod_variable_experta') !== self::texto($condicion, 'cod_variable_experta')
                || self::texto($valor, 'cod_valor_semantico') !== self::texto($condicion, 'cod_valor_semantico')
                || self::texto($variable, 'cod_version_modelo') !== $version
                || self::texto($variable, 'cod_nodo_semantico') !== self::texto($nodo, 'cod_nodo_semantico')
                || self::texto($nodo, 'cod_version_modelo') !== $version
                || self::texto($nodo, 'tipo_nodo') !== 'VARIABLE'
                || self::texto($variable, 'cod_dominio_valores') !== self::texto($valor, 'cod_dominio_valores')) {
                throw new DomainException('FK, versión o dominio semántico de condición incompatible.');
            }
            self::validarValor($valor, $version, $habilitada);
            if ($habilitada && (self::texto($variable, 'estado') !== 'ACTIVO' || self::texto($nodo, 'estado') !== 'ACTIVO')) {
                throw new DomainException('Una regla activa no puede usar dependencias inactivas.');
            }
            $atomo = serialize([self::texto($condicion, 'cod_variable_experta'), self::texto($condicion, 'cod_valor_semantico')]);
            if (isset($ids[$condicionId]) || isset($atomos[$atomo])) {
                throw new DomainException('Condición física o atómica duplicada.');
            }
            $ids[$condicionId] = $atomos[$atomo] = true;
            $condiciones[] = ['operador' => OperadorCondicionEnum::IGUAL_A,
                'variable' => self::texto($nodo, 'codigo_semantico'), 'valor' => self::texto($valor, 'codigo_valor')];
        }
        $consecuencias = self::coleccion($fila, 'consecuenciasReglaExperta');
        if ($condiciones === [] || $consecuencias->count() !== 1) {
            throw new DomainException('Domain requiere condiciones explícitas y exactamente una consecuencia.');
        }
        $consecuencia = $consecuencias->first();
        if (! $consecuencia instanceof ConsecuenciaReglaExperta || self::texto($consecuencia, 'cod_regla_experta') !== $id) {
            throw new DomainException('Consecuencia mal tipada o ajena a la regla.');
        }
        $criterioDominio = self::relacion($consecuencia, 'criterioDominioResultado', CriterioDominioResultado::class);
        $criterio = self::relacion($criterioDominio, 'criterio', NodoSemantico::class);
        $salida = self::relacion($consecuencia, 'valor', ValorSemantico::class);
        if (self::texto($consecuencia, 'cod_criterio_dominio_resultado') !== self::texto($criterioDominio, 'cod_criterio_dominio_resultado')
            || self::texto($consecuencia, 'cod_valor_semantico') !== self::texto($salida, 'cod_valor_semantico')
            || self::texto($criterioDominio, 'cod_nodo_criterio') !== self::texto($criterio, 'cod_nodo_semantico')
            || self::texto($criterio, 'cod_version_modelo') !== $version || self::texto($criterio, 'tipo_nodo') !== 'CRITERIO'
            || self::texto($criterioDominio, 'cod_dominio_valores') !== self::texto($salida, 'cod_dominio_valores')) {
            throw new DomainException('Consecuencia incompatible con su criterio, dominio o versión.');
        }
        self::validarValor($salida, $version, $habilitada);
        if (self::texto($salida, 'codigo_valor') === 'HALLAZGOS_MIXTOS') {
            throw new DomainException('D-130 reserva HALLAZGOS_MIXTOS al resolvedor, no a una consecuencia de regla.');
        }
        if ($habilitada && (self::texto($criterioDominio, 'estado') !== 'ACTIVO' || self::texto($criterio, 'estado') !== 'ACTIVO')) {
            throw new DomainException('El criterio de consecuencia no está activo.');
        }

        return new ReglaExperta($id, $version, ['operador' => OperadorCondicionEnum::Y, 'condiciones' => $condiciones],
            ['concepto' => self::texto($criterio, 'codigo_semantico'), 'valor' => self::texto($salida, 'codigo_valor')],
            $habilitada, $fila->getTable().':'.$id);
    }

    /**
     * Comprueba el dominio y, cuando corresponde, la habilitación persistida.
     *
     * @param  ValorSemantico  $valor  Valor ya cargado con su dominio.
     * @param  string  $version  Versión propietaria de la regla.
     * @param  bool  $activa  Habilitación declarada en la fila original.
     * @return void No acredita valores pendientes ni altera sus estados.
     *
     * @throws DomainException Por FK, versión o dependencia no habilitada.
     */
    private static function validarValor(ValorSemantico $valor, string $version, bool $activa): void
    {
        $dominio = self::relacion($valor, 'dominio', DominioValoresExperto::class);
        if (self::texto($dominio, 'cod_dominio_valores') !== self::texto($valor, 'cod_dominio_valores')
            || self::texto($dominio, 'cod_version_modelo') !== $version
            || ($activa && (self::texto($dominio, 'estado') !== 'ACTIVO'
                || self::texto($valor, 'estado') !== 'ACTIVO' || self::texto($valor, 'estado_aprobacion') !== 'APROBADO'))) {
            throw new DomainException('Valor de otra versión o sin habilitación persistida.');
        }
    }

    /**
     * Lee columnas concretas sin coerción ni accessors.
     *
     * @param  Model  $modelo  Fila materializada.
     * @param  string  $campo  Columna física fija.
     * @return string Texto original no vacío.
     *
     * @throws DomainException Por campo ausente o tipo incompatible.
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
     * Recupera una dependencia cargada sin disparar consultas.
     *
     * @param  Model  $modelo  Fila propietaria.
     * @param  string  $nombre  Relación fija de App\Models.
     * @param  class-string<Model>  $tipo  Clase esperada.
     * @return Model Dependencia original.
     *
     * @throws DomainException Por relación ausente, NULL o tipo incorrecto.
     */
    private static function relacion(Model $modelo, string $nombre, string $tipo): Model
    {
        if (! $modelo->relationLoaded($nombre) || ! $modelo->getRelation($nombre) instanceof $tipo) {
            throw new DomainException('Debe precargarse la relación '.$nombre.' del tipo esperado.');
        }

        return $modelo->getRelation($nombre);
    }

    /**
     * Exige una colección física cargada por el futuro repositorio.
     *
     * @param  Model  $modelo  Fila propietaria.
     * @param  string  $nombre  Nombre fijo de la colección.
     * @return Collection Colección original.
     *
     * @throws DomainException Por relación no cargada o mal tipada.
     */
    private static function coleccion(Model $modelo, string $nombre): Collection
    {
        if (! $modelo->relationLoaded($nombre) || ! $modelo->getRelation($nombre) instanceof Collection) {
            throw new DomainException('Debe precargarse la colección '.$nombre.'.');
        }

        return $modelo->getRelation($nombre);
    }
}
