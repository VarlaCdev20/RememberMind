<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas;

use App\Models\EvaluacionExperta;
use App\Models\MapeoVariableFuente;
use App\Models\ValoracionFuncional;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\MapeoFuentes\ExtractorVariableMapeada;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/**
 * TÍTULO:
 * Adaptador de funcionalidad básica.
 *
 * CAPA:
 * Infrastructure - Adapters.
 *
 * ¿PARA QUÉ SIRVE?
 * Recupera datos brutos autorizados sin interpretación clínica.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Evaluación sintética, IDs originales, corte inclusivo y mapeo persistido.
 *
 * ¿QUÉ HACE?
 * Valida campos de una lista cerrada y mantiene NULL; preserva fecha, autor y episodio.
 *
 * ¿QUÉ DEVUELVE?
 * Modelos originales y valores tipados sin baremación. Su destino siempre es HECHO (BADL).
 *
 * ¿QUÉ NO DEBE HACER?
 * No sustituye IADL, acredita DCL-C03 ni usa accessors que inventan valores por defecto.
 */
final readonly class AdaptadorValoracionesFuncionales
{
    /** Modelo existente; nunca se obtiene de una cadena suministrada por el cliente. */
    public const MODELO = ValoracionFuncional::class;

    /** Tabla física aprobada. */
    public const TABLA = 'valoraciones_funcionales';

    /** PK física aprobada. */
    public const PK = 'cod_valoracion_funcional';

    /** Vínculo original empleado como episodio, sin fallback. */
    public const EPISODIO = 'cod_atencion';

    /** Estados operativos admitidos sin transformarlos. */
    public const ESTADOS = ['VIGENTE', 'ACTIVA'];

    /** @var array<string, string> Columnas físicas autorizadas y sus tipos de transporte. */
    public const CAMPOS = ['marcha' => 'texto', 'equilibrio' => 'texto', 'traslado' => 'texto', 'fuerza_funcional' => 'texto', 'resistencia' => 'texto', 'alimentacion_autonoma' => 'texto', 'bano_autonomo' => 'texto', 'vestido_autonomo' => 'texto', 'higiene_autonoma' => 'texto', 'continencia' => 'texto', 'movilidad_autonoma' => 'texto', 'necesita_supervision' => 'booleano', 'nivel_dependencia' => 'texto', 'conclusion' => 'texto', 'recomendacion' => 'texto'];

    /**
     * Recupera un lote acotado y solo carga valores hasta el corte.
     *
     * @param  EvaluacionExperta  $evaluacion  Cabecera sintética que se contrasta con la BDD.
     * @param  list<string>  $ids  PK originales solicitadas.
     * @param  DateTimeImmutable  $corte  Corte clínico inclusivo.
     * @param  string  $conexion  Conexión técnica explícita.
     * @param  DateTimeZone  $zona  Zona SQL de los registros.
     * @return Collection<int, ValoracionFuncional> Originales; posteriores al corte solo conservan metadatos.
     *
     * @throws DomainException Por contexto, fecha, episodio o estado incompatibles.
     * @throws RuntimeException Por base no autorizada u origen no disponible.
     */
    public function recuperar(EvaluacionExperta $evaluacion, array $ids, DateTimeImmutable $corte, string $conexion, DateTimeZone $zona): Collection
    {
        return ExtractorVariableMapeada::recuperarOperativos($this, $evaluacion, $ids, $corte, $conexion, $zona);
    }

    /**
     * Comprueba el campo fijo antes de leer cualquier registro operativo.
     *
     * @param  MapeoVariableFuente  $mapeo  Contrato persistido de extracción.
     * @return void No consulta ni interpreta el dato.
     *
     * @throws DomainException Por modo, campo o selector no soportados.
     */
    public function validarMapeo(MapeoVariableFuente $mapeo): void
    {
        ExtractorVariableMapeada::campoMapeado($mapeo, self::CAMPOS);
    }

    /**
     * Transporta la columna explícita, incluido NULL, sin accessors de presentación.
     *
     * @param  ValoracionFuncional  $origen  Registro original ya recuperado.
     * @param  MapeoVariableFuente  $mapeo  Campo directo validado.
     * @return string|int|float|bool|null Valor original con su tipo físico documentado.
     *
     * @throws DomainException Por columna no recuperada o valor de tipo incompatible.
     */
    public function extraer(ValoracionFuncional $origen, MapeoVariableFuente $mapeo): string|int|float|bool|null
    {
        $campo = ExtractorVariableMapeada::campoMapeado($mapeo, self::CAMPOS);

        return ExtractorVariableMapeada::valorFisico($origen, $campo, self::CAMPOS[$campo]);
    }

    /**
     * Declara el destino técnico sin calificar el estado cognitivo.
     *
     * @param  MapeoVariableFuente  $mapeo  Variable y propietario primario cargados.
     * @return string HECHO; BADL no acredita independencia instrumental.
     *
     * @throws DomainException Por propietario primario ajeno o incompatible.
     */
    public function tipo(MapeoVariableFuente $mapeo): string
    {
        return 'HECHO';
    }
}
