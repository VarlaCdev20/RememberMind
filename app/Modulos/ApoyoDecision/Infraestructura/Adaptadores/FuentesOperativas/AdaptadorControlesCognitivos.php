<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas;

use App\Models\ControlCognitivo;
use App\Models\EvaluacionExperta;
use App\Models\MapeoVariableFuente;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\MapeoFuentes\ExtractorVariableMapeada;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/** Recupera controles originales con fecha, autor y episodio, sin interpretar sus valores. */
final readonly class AdaptadorControlesCognitivos
{
    /** Modelo existente; nunca se obtiene de una cadena suministrada por el cliente. */
    public const MODELO = ControlCognitivo::class;

    /** Tabla física aprobada. */
    public const TABLA = 'controles_cognitivos';

    /** PK física aprobada. */
    public const PK = 'cod_control_cognitivo';

    /** Vínculo original empleado como episodio, sin fallback. */
    public const EPISODIO = 'cod_atencion';

    /** Estados operativos admitidos sin transformarlos. */
    public const ESTADOS = ['VIGENTE', 'ACTIVA'];

    /** @var array<string, string> Columnas físicas autorizadas y sus tipos de transporte. */
    public const CAMPOS = ['orientacion_persona' => 'texto', 'orientacion_lugar' => 'texto', 'orientacion_tiempo' => 'texto', 'memoria_reciente' => 'texto', 'memoria_remota' => 'texto', 'atencion' => 'texto', 'comprension' => 'texto', 'lenguaje' => 'texto', 'sigue_instrucciones' => 'booleano', 'repite_preguntas' => 'booleano', 'olvida_indicaciones' => 'booleano', 'reconoce_personas' => 'booleano', 'reconoce_entorno' => 'booleano', 'confusion' => 'booleano', 'cambio_cognitivo' => 'booleano', 'observacion' => 'texto'];

    /**
     * Recupera un lote acotado y solo carga valores hasta el corte.
     * @param EvaluacionExperta $evaluacion
     * @param list<string> $ids
     * @param DateTimeImmutable $corte
     * @param string $conexion
     * @param DateTimeZone $zona
     * @return Collection<int, ControlCognitivo>
     * @throws DomainException
     * @throws RuntimeException
     */
    public function recuperar(EvaluacionExperta $evaluacion, array $ids, DateTimeImmutable $corte, string $conexion, DateTimeZone $zona): Collection
    {
        return ExtractorVariableMapeada::recuperarOperativos($this, $evaluacion, $ids, $corte, $conexion, $zona);
    }

    /**
     * Comprueba el campo fijo antes de leer cualquier registro operativo.
     * @param MapeoVariableFuente $mapeo
     * @return void
     * @throws DomainException
     */
    public function validarMapeo(MapeoVariableFuente $mapeo): void
    {
        ExtractorVariableMapeada::campoMapeado($mapeo, self::CAMPOS);
    }

    /**
     * Transporta la columna explícita, incluido NULL, sin accessors de presentación.
     * @param ControlCognitivo $origen
     * @param MapeoVariableFuente $mapeo
     * @return string|int|float|bool|null
     * @throws DomainException
     */
    public function extraer(ControlCognitivo $origen, MapeoVariableFuente $mapeo): string|int|float|bool|null
    {
        $campo = ExtractorVariableMapeada::campoMapeado($mapeo, self::CAMPOS);

        return ExtractorVariableMapeada::valorFisico($origen, $campo, self::CAMPOS[$campo]);
    }

    /**
     * Declara el destino técnico sin calificar el estado cognitivo.
     * @param MapeoVariableFuente $mapeo
     * @return string
     * @throws DomainException
     */
    public function tipo(MapeoVariableFuente $mapeo): string
    {
        return ExtractorVariableMapeada::tipoDeclarado($mapeo);
    }
}
