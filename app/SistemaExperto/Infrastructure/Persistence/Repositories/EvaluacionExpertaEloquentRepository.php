<?php

declare(strict_types=1);

namespace App\SistemaExperto\Infrastructure\Persistence\Repositories;

use App\Models\EvaluacionExperta as EvaluacionModelo;
use App\Models\EvidenciaEvaluacion;
use App\Models\FuenteDatoExperta;
use App\Models\MapeoVariableFuente;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use App\SistemaExperto\Domain\Entities\Cognicion\VectorCognitivo;
use App\SistemaExperto\Domain\Entities\Dcl\ResultadoClasificacionDcl;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\Repositories\Evaluaciones\EvaluacionExpertaRepositoryInterface;
use App\SistemaExperto\Infrastructure\Persistence\Mappers\MapeadorResultados;
use Closure;
use DateTimeImmutable;
use DomainException;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

/**
 * TÍTULO:
 * Lectura Eloquent de perfiles experimentales con memoria original.
 *
 * CAPA:
 * Infrastructure - Persistence - Repositories.
 *
 * ¿PARA QUÉ SIRVE?
 * Implementa el puerto de evaluaciones en el alcance de lectura permitido.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Contexto exacto, mapper, conexión segregada y recuperador de la instantánea
 * original. D-137 no almacena su corte, valores brutos ni todas sus declaraciones.
 *
 * ¿QUÉ HACE?
 * Consulta cabecera, criterios, resultados y soportes con relaciones precargadas.
 * Contrasta la memoria original y delega la conversión en MapeadorResultados.
 *
 * ¿QUÉ DEVUELVE?
 * VectorCognitivo o null si la evaluación no existe.
 *
 * ¿QUÉ NO DEBE HACER?
 * No sustituye la memoria original por una extracción actual, interpreta G09
 * como A/P, crea C01–C05 ni inventa persistencia del contrato DCL.
 * Las escrituras del puerto quedan expresamente deshabilitadas: el esquema
 * no conserva la instantánea completa y esta etapa no autoriza modificarlo.
 * Una clasificación existente no representable provoca error, nunca falso null.
 */
final readonly class EvaluacionExpertaEloquentRepository implements EvaluacionExpertaRepositoryInterface
{
    /**
     * Recibe la recuperación de memoria, sin crear otra ni registrar bindings.
     *
     * @param  MapeadorResultados  $mapeador  Conversor de resultados existentes.
     * @param  Closure(string, string, string, DateTimeImmutable): MemoriaTrabajo  $recuperarMemoria  Instantánea original del contexto y corte exactos.
     * @param  string  $conexionExperimental  Nombre de base técnica segregada.
     *
     * @throws DomainException Por nombre de conexión inválido.
     */
    public function __construct(
        /** Conversor sin inferencia. */ private MapeadorResultados $mapeador,
        /** Recuperador explícito; no representa un almacén nuevo ni un dato inventado. */ private Closure $recuperarMemoria,
        /** Destino técnico obligatorio. */ private string $conexionExperimental,
    ) {
        self::validarIdentidades($conexionExperimental);
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
        $fila = $this->cabecera($evaluacionId, $residenteId, $versionConocimiento);
        if ($fila === null) {
            return null;
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
     * Rechaza la escritura que el contrato físico actual no puede preservar.
     *
     * @param  VectorCognitivo  $evaluacion  Instantánea original que no será modificada.
     * @return void No persiste ni confirma una conservación inexistente.
     *
     * @throws RuntimeException Siempre: escritura completa no habilitada en esta etapa.
     */
    public function guardar(VectorCognitivo $evaluacion): void
    {
        throw new RuntimeException('Escritura no habilitada: D-137 no conserva la instantánea completa de Domain.');
    }

    /**
     * Rechaza guardar una clasificación cuyo contrato no tiene representación física.
     *
     * @param  ResultadoClasificacionDcl  $resultado  Clasificación experimental original.
     * @return void No escribe diagnósticos ni resultados parciales.
     *
     * @throws RuntimeException Siempre: conservación DCL no habilitada.
     */
    public function guardarClasificacion(ResultadoClasificacionDcl $resultado): void
    {
        throw new RuntimeException('Escritura DCL no habilitada: falta representación íntegra de C01–C05 y su contrato.');
    }

    /**
     * Distingue evaluación ausente de clasificación no recuperable con D-137.
     *
     * @param  string  $evaluacionId  Identidad artificial exacta.
     * @param  string  $residenteId  Sujeto sintético exacto.
     * @param  string  $versionConocimiento  Versión propietaria.
     * @param  DateTimeImmutable  $fechaCorte  Corte original solicitado.
     * @param  string  $versionContrato  Contrato DCL solicitado, sin interpretación.
     * @return ResultadoClasificacionDcl|null Null solo si la evaluación no existe.
     *
     * @throws DomainException Por identidad o contexto incompatibles.
     * @throws RuntimeException Si existe la evaluación: el esquema no permite reconstruir C01–C05 y contrato; también por fallos técnicos.
     */
    public function obtenerClasificacion(string $evaluacionId, string $residenteId, string $versionConocimiento, DateTimeImmutable $fechaCorte, string $versionContrato): ?ResultadoClasificacionDcl
    {
        self::validarContexto($evaluacionId, $residenteId, $versionConocimiento);
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
