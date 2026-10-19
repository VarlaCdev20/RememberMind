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

/**
 * TÍTULO:
 * Conservación Eloquent de perfiles experimentales.
 *
 * CAPA:
 * Infrastructure - Persistence - Repositories.
 *
 * ¿PARA QUÉ SIRVE?
 * Implementa el puerto completo y preserva lecturas anteriores.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Contexto exacto, conexión segregada y extractores explícitos.
 *
 * ¿QUÉ HACE?
 * Delega instantáneas V2 al almacén y las anteriores al mapper D-137.
 *
 * ¿QUÉ DEVUELVE?
 * Memoria, perfil y clasificación originales o ausencia explícita.
 *
 * ¿QUÉ NO DEBE HACER?
 * No reinterpreta G09, crea fuentes, sobrescribe historia ni
 * oculta una instantánea nueva incompleta mediante el recuperador anterior.
 */
final readonly class EvaluacionExpertaEloquentRepository implements EvaluacionExpertaRepositoryInterface
{
    private AlmacenInstantaneas $almacen;

    /**
     * Recibe la recuperación de memoria, sin crear otra ni registrar bindings.
     *
     * @param  MapeadorResultados  $mapeador  Conversor de resultados existentes.
     * @param  Closure|null  $recuperarMemoria  Instantánea original del contexto y corte exactos.
     * @param  string  $conexionExperimental  Nombre de base técnica segregada.
     * @param  Closure|null  $extraerOriginal  Extracción por lote para contrastar fuentes institucionales al guardar.
     * @param  DateTimeZone|null  $zona  Zona explícita de las columnas operativas originales.
     *
     * @throws DomainException Por nombre de conexión inválido.
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
     *
     * @param  string  $evaluacionId  Identidad artificial exacta.
     * @param  string  $residenteId  Caso sintético exacto.
     * @param  string  $versionConocimiento  Versión propietaria.
     * @param  DateTimeImmutable  $fechaCorte  Corte original, no fecha de ejecución.
     * @return VectorCognitivo|null Perfil convertido o evaluación inexistente.
     *
     * @throws DomainException Por contexto, corte, conocimiento, perfil o soporte incompatibles.
     * @throws RuntimeException Por base no autorizada, memoria no disponible o fallo de lectura.
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
     *
     * @param  EvidenciaEvaluacion  $fila  Soporte con mapeo y fuente cargados.
     * @param  MemoriaTrabajo  $memoria  Instantánea original de la misma evaluación.
     * @return void No acredita independencia ni interpreta un valor.
     *
     * @throws DomainException Por registro, fuente, FK o versión distintos del original.
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
     *
     * @param  VectorCognitivo  $evaluacion  Instantánea original que no será modificada.
     * @return void Conserva una vez; un reintento idéntico no escribe.
     *
     * @throws RuntimeException Por fallo técnico de escritura; la transacción revierte sus efectos.
     */
    public function guardar(VectorCognitivo $evaluacion): void
    {
        $this->almacen->guardar($evaluacion);
    }

    /**
     * Conserva clasificación, requisitos y soportes sobre la cognición ya guardada.
     *
     * @param  ResultadoClasificacionDcl  $resultado  Clasificación experimental original.
     * @return void No escribe diagnósticos ni resultados parciales.
     *
     * @throws RuntimeException Por fallo técnico; no confirma una clasificación parcial.
     */
    public function guardarClasificacion(ResultadoClasificacionDcl $resultado): void
    {
        $this->almacen->guardarClasificacion($resultado);
    }

    /**
     * Recupera DCL V2 y distingue el contrato anterior no representable.
     *
     * @param  string  $evaluacionId  Identidad artificial exacta.
     * @param  string  $residenteId  Sujeto sintético exacto.
     * @param  string  $versionConocimiento  Versión propietaria.
     * @param  DateTimeImmutable  $fechaCorte  Corte original solicitado.
     * @param  string  $versionContrato  Contrato DCL solicitado, sin interpretación.
     * @return ResultadoClasificacionDcl|null Resultado conservado, evaluación ausente o clasificación V2 aún no registrada.
     *
     * @throws DomainException Por identidad o contexto incompatibles.
     * @throws RuntimeException Por fallo técnico o por una evaluación anterior D-137 sin representación íntegra de C01–C05.
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
     *
     * @param  string  $evaluacionId  PK solicitada.
     * @param  string  $residenteId  Caso propietario.
     * @param  string  $versionConocimiento  Versión exacta.
     * @return EvaluacionModelo|null Cabecera del contexto o ausencia física.
     *
     * @throws DomainException Por residente/versión ajenos o ejecución no técnica finalizada.
     * @throws RuntimeException Por conexión no autorizada o fallo de lectura.
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
     *
     * @param  string  $evaluacionId  Evaluación artificial con prefijo SIM-.
     * @param  string  $residenteId  Caso artificial con prefijo SINTETICO-.
     * @param  string  $versionConocimiento  Versión exacta.
     * @return void No selecciona otro sujeto ni otra versión.
     *
     * @throws DomainException Por identidad inválida o solicitud no sintética.
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
