<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios;

use App\Models\EvaluacionExperta as EvaluacionModelo;
use App\Models\EvidenciaEvaluacion;
use App\Models\FuenteDatoExperta;
use App\Models\MapeoVariableFuente;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use MotorExperto\dominio\orion\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\orion\Entities\Dcl\ResultadoClasificacionDcl;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\Repositories\Evaluaciones\EvaluacionExpertaRepositoryInterface;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\AlmacenInstantaneas;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorResultados;
use Closure;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

/** Conserva evaluaciones y sus lecturas originales mediante el contrato de persistencia. */
final readonly class EvaluacionExpertaEloquentRepository implements EvaluacionExpertaRepositoryInterface
{
    private AlmacenInstantaneas $almacen;

    /**
     * Recibe la recuperación de memoria, sin crear otra ni registrar bindings.
     * @param MapeadorResultados $mapeador
     * @param Closure|null $recuperarMemoria
     * @param string $conexionExperimental
     * @param Closure|null $extraerOriginal
     * @param DateTimeZone|null $zona
     * @throws DomainException
     */
    public function __construct(
        /** Conversor sin inferencia. */ private MapeadorResultados $mapeador,
        /** Recuperador explícito; no representa un almacén nuevo ni un dato inventado. */ private ?Closure $recuperarMemoria,
        /** Destino técnico obligatorio. */ private string $conexionExperimental,
        ?Closure $extraerOriginal = null,
        ?DateTimeZone $zona = null,
    ) {
        self::validarIdentidades($conexionExperimental);
        $this->almacen = new AlmacenInstantaneas($conexionExperimental, $extraerOriginal, $zona ?? new DateTimeZone('UTC'));
    }

    /**
     * Recupera el perfil completo de una evaluación sintética finalizada.
     * @param string $evaluacionId
     * @param string $residenteId
     * @param string $versionConocimiento
     * @param DateTimeImmutable $fechaCorte
     * @return VectorCognitivo|null
     * @throws DomainException
     * @throws RuntimeException
     */
    public function obtener(string $evaluacionId, string $residenteId, string $versionConocimiento, DateTimeImmutable $fechaCorte): ?VectorCognitivo
    {
        self::validarContexto($evaluacionId, $residenteId, $versionConocimiento);
        $cabeceraNueva = $this->almacen->cabecera($evaluacionId);
        if ($cabeceraNueva !== null && $cabeceraNueva->version_conservacion !== null) {
            return $this->almacen->obtener($evaluacionId, $residenteId, $versionConocimiento, $fechaCorte);
        }
        $fila = $this->cabecera($evaluacionId, $residenteId, $versionConocimiento);
        if ($fila === null) {
            return null;
        }
        if ($this->recuperarMemoria === null) {
            throw new RuntimeException('La evaluación anterior exige su recuperador de memoria explícito.');
        }
        $memoria = ($this->recuperarMemoria)($evaluacionId, $residenteId, $versionConocimiento, $fechaCorte);
        if (! $memoria instanceof MemoriaTrabajo) {
            throw new RuntimeException('No se recuperó la memoria original de la evaluación.');
        }
        if ($memoria->evaluacionId !== $evaluacionId || $memoria->residenteId !== $residenteId
            || $memoria->versionConocimiento !== $versionConocimiento || $memoria->fechaCorte != $fechaCorte) {
            throw new DomainException('La instantánea original pertenece a otro contexto o corte.');
        }
        $fila->load([
            'evaluacionCriterios' => static fn (HasMany $q): HasMany => $q->orderBy('cod_evaluacion_criterio'),
            'evaluacionCriterios.criterio.criterioDominioResultado',
            'evaluacionCriterios.resultado.valor.dominio',
            'evaluacionCriterios.evidenciasCriterioEvaluacion' => static fn (HasMany $q): HasMany => $q->orderBy('cod_evidencia_criterio'),
            'evaluacionCriterios.evidenciasCriterioEvaluacion.evidencia.mapeoVariableFuente.fuente',
        ]);
        foreach ($fila->getRelation('evaluacionCriterios') as $criterio) {
            // Reutiliza los modelos originales; evita volver a consultar cada
            // resultado para cargar su misma cabecera y criterio evaluado.
            $criterio->setRelation('evaluacion', $fila);
            $criterio->getRelation('resultado')?->setRelation('evaluacionCriterio', $criterio);
            foreach ($criterio->getRelation('evidenciasCriterioEvaluacion') as $vinculo) {
                $evidencia = $vinculo->getRelation('evidencia');
                if (! $evidencia instanceof EvidenciaEvaluacion) {
                    throw new DomainException('El soporte persistido no tiene su evidencia original.');
                }
                self::validarOrigenSoporte($evidencia, $memoria);
            }
        }

        return $this->mapeador->aVector($fila, $memoria);
    }

    /**
     * Contrasta la procedencia persistida con el hecho original, sin reconstruirlo.
     * @param EvidenciaEvaluacion $fila
     * @param MemoriaTrabajo $memoria
     * @return void
     * @throws DomainException
     */
    private static function validarOrigenSoporte(EvidenciaEvaluacion $fila, MemoriaTrabajo $memoria): void
    {
        $hecho = $memoria->evidencia($fila->getRawOriginal('cod_evidencia_evaluacion'))->hecho;
        $mapeo = $fila->getRelation('mapeoVariableFuente');
        if (! $mapeo instanceof MapeoVariableFuente) {
            throw new DomainException('El soporte carece del mapeo original.');
        }
        $fuente = $mapeo->getRelation('fuente');
        if (! $fuente instanceof FuenteDatoExperta
            || $fila->getRawOriginal('cod_evaluacion_experta') !== $memoria->evaluacionId
            || $fila->getRawOriginal('cod_registro_fuente') !== $hecho->origenId
            || $fila->getRawOriginal('cod_mapeo_variable_fuente') !== $mapeo->getRawOriginal('cod_mapeo_variable_fuente')
            || $mapeo->getRawOriginal('cod_fuente_dato_experta') !== $fuente->getRawOriginal('cod_fuente_dato_experta')
            || $mapeo->getRawOriginal('cod_version_modelo') !== $memoria->versionConocimiento
            || $fuente->getRawOriginal('cod_version_modelo') !== $memoria->versionConocimiento
            || $fuente->getRawOriginal('codigo_fuente') !== $hecho->fuenteId) {
            throw new DomainException('La procedencia persistida del soporte difiere de su memoria original.');
        }
    }

    /**
     * Conserva la memoria y los resultados originales de forma atómica.
     * @param VectorCognitivo $evaluacion
     * @return void
     * @throws RuntimeException
     */
    public function guardar(VectorCognitivo $evaluacion): void
    {
        $this->almacen->guardar($evaluacion);
    }

    /**
     * Conserva clasificación, requisitos y soportes sobre la cognición ya guardada.
     * @param ResultadoClasificacionDcl $resultado
     * @return void
     * @throws RuntimeException
     */
    public function guardarClasificacion(ResultadoClasificacionDcl $resultado): void
    {
        $this->almacen->guardarClasificacion($resultado);
    }

    /**
     * Recupera DCL V2 y distingue el contrato anterior no representable.
     * @param string $evaluacionId
     * @param string $residenteId
     * @param string $versionConocimiento
     * @param DateTimeImmutable $fechaCorte
     * @param string $versionContrato
     * @return ResultadoClasificacionDcl|null
     * @throws DomainException
     * @throws RuntimeException
     */
    public function obtenerClasificacion(string $evaluacionId, string $residenteId, string $versionConocimiento, DateTimeImmutable $fechaCorte, string $versionContrato): ?ResultadoClasificacionDcl
    {
        self::validarContexto($evaluacionId, $residenteId, $versionConocimiento);
        $cabeceraNueva = $this->almacen->cabecera($evaluacionId);
        if ($cabeceraNueva !== null && $cabeceraNueva->version_conservacion !== null) {
            return $this->almacen->obtenerClasificacion($evaluacionId, $residenteId, $versionConocimiento, $fechaCorte, $versionContrato);
        }
        self::validarIdentidades($versionContrato);
        if ($this->cabecera($evaluacionId, $residenteId, $versionConocimiento) === null) {
            return null;
        }

        throw new RuntimeException('Lectura DCL no disponible: D-137 no conserva C01–C05 ni la versión de su contrato.');
    }

    /**
     * Lee una cabecera mínima antes de recuperar memoria o resultados.
     * @param string $evaluacionId
     * @param string $residenteId
     * @param string $versionConocimiento
     * @return EvaluacionModelo|null
     * @throws DomainException
     * @throws RuntimeException
     */
    private function cabecera(string $evaluacionId, string $residenteId, string $versionConocimiento): ?EvaluacionModelo
    {
        $fila = EvaluacionModelo::on($this->conexion())->select([
            'cod_evaluacion_experta', 'cod_residente', 'cod_version_modelo', 'origen_activacion', 'estado_ejecucion',
        ])->whereKey($evaluacionId)->first();
        if ($fila !== null) {
            $datos = $fila->getAttributes();
            if ($datos['cod_residente'] !== $residenteId || $datos['cod_version_modelo'] !== $versionConocimiento
                || $datos['origen_activacion'] !== 'PRUEBA_TECNICA' || $datos['estado_ejecucion'] !== 'PRUEBA_FINALIZADA') {
                throw new DomainException('La evaluación no pertenece al contexto sintético finalizado solicitado.');
            }
        }

        return $fila;
    }

    /**
     * Rechaza solicitudes asistenciales antes de cualquier consulta.
     * @param string $evaluacionId
     * @param string $residenteId
     * @param string $versionConocimiento
     * @return void
     * @throws DomainException
     */
    private static function validarContexto(string $evaluacionId, string $residenteId, string $versionConocimiento): void
    {
        self::validarIdentidades($evaluacionId, $residenteId, $versionConocimiento);
        if (! str_starts_with($evaluacionId, 'SIM-') || strlen($evaluacionId) <= 4
            || ! str_starts_with($residenteId, 'SINTETICO-') || strlen($residenteId) <= 10) {
            throw new DomainException('El repositorio solo admite evaluaciones y casos sintéticos explícitos.');
        }
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
