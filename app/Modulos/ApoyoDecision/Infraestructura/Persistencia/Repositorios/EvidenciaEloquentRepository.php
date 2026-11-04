<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios;

use App\Models\AplicacionInstrumento;
use App\Models\ControlCognitivo;
use App\Models\ControlFuncionalIadl;
use App\Models\EvaluacionExperta;
use App\Models\EvidenciaEvaluacion;
use App\Models\FuenteDatoExperta;
use App\Models\MapeoVariableFuente;
use App\Models\NodoSemantico;
use App\Models\SeguimientoPedagogico;
use App\Models\ValoracionFuncional;
use App\Models\VariableExperta;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\Repositories\Hechos\EvidenciaRepositoryInterface;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorEvidencias;
use Closure;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

/** Recupera la memoria original del caso sin inferir resultados. */
final readonly class EvidenciaEloquentRepository implements EvidenciaRepositoryInterface
{
    /**
     * Recibe la extracción autorizada; no la elige ni registra globalmente.
     * @param MapeadorEvidencias $mapeador
     * @param Closure(Collection<int, EvidenciaEvaluacion>, DateTimeImmutable, string): array<string, array{origen: ControlCognitivo|SeguimientoPedagogico|ValoracionFuncional|AplicacionInstrumento, valor: string|int|float|bool|null, tipo: 'HECHO'|'EVIDENCIA_COGNITIVA'}> $extraerOrigenes
     * @param DateTimeZone $zonaAlmacenamiento
     * @param string $conexionExperimental
     * @throws DomainException
     */
    public function __construct(
        /** Conversor sin lógica clínica. */ private MapeadorEvidencias $mapeador,
        /** Capacidad de extracción autorizada, preparada para la etapa D. */ private Closure $extraerOrigenes,
        /** Zona explícita de las fechas SQL de las fuentes. */ private DateTimeZone $zonaAlmacenamiento,
        /** Destino técnico obligatorio. */ private string $conexionExperimental,
    ) {
        self::validarIdentidades($conexionExperimental);
    }

    /**
     * Recupera hechos inclusivos del contexto exacto solicitado.
     * @param string $evaluacionId
     * @param string $residenteId
     * @param string $versionConocimiento
     * @param DateTimeImmutable $fechaCorte
     * @return MemoriaTrabajo
     * @throws DomainException
     * @throws RuntimeException
     */
    public function paraEvaluacion(string $evaluacionId, string $residenteId, string $versionConocimiento, DateTimeImmutable $fechaCorte): MemoriaTrabajo
    {
        self::validarContexto($evaluacionId, $residenteId, $versionConocimiento);
        $conexion = $this->conexion();
        $evaluacion = EvaluacionExperta::on($conexion)->select([
            'cod_evaluacion_experta', 'cod_residente', 'cod_version_modelo', 'origen_activacion',
        ])->whereKey($evaluacionId)->first()
            ?? throw new DomainException('La evaluación solicitada no existe; no se fabrica una memoria.');
        $datos = $evaluacion->getAttributes();
        if ($datos['cod_residente'] !== $residenteId || $datos['cod_version_modelo'] !== $versionConocimiento
            || $datos['origen_activacion'] !== 'PRUEBA_TECNICA') {
            throw new DomainException('La evaluación no pertenece al contexto experimental solicitado.');
        }
        $evaluacion->load([
            'evidenciasEvaluacion' => static fn (HasMany $q): HasMany => $q
                ->whereIn('estado_admisibilidad', ['ADMISIBLE', 'ADMISIBLE_CON_ADVERTENCIA'])->orderBy('cod_evidencia_evaluacion'),
            'evidenciasEvaluacion.mapeoVariableFuente.fuente',
            'evidenciasEvaluacion.mapeoVariableFuente.variable.nodo',
            'evidenciasEvaluacion.mapeoVariableFuente.variable.propietarioPrimario',
        ]);
        $filas = $evaluacion->getRelation('evidenciasEvaluacion');
        $ids = [];
        foreach ($filas as $fila) {
            $fila->setRelation('evaluacion', $evaluacion);
            self::validarMapeo($fila, $versionConocimiento);
            $ids[] = $fila->getRawOriginal('cod_evidencia_evaluacion');
        }
        $hechos = $evidencias = [];
        if ($filas->isNotEmpty()) {
            // Una llamada por lote permite que los adaptadores agrupen sus lecturas;
            // no hay una consulta operativa por cada evidencia desde este repositorio.
            $origenes = ($this->extraerOrigenes)($filas, $fechaCorte, $conexion);
            if (! is_array($origenes) || count($origenes) !== count($ids)
                || array_diff($ids, array_keys($origenes)) !== [] || array_diff(array_keys($origenes), $ids) !== []) {
                throw new RuntimeException('La extracción no entregó exactamente los orígenes solicitados.');
            }
            foreach ($filas as $fila) {
                $id = $fila->getRawOriginal('cod_evidencia_evaluacion');
                $original = $origenes[$id];
                if (! is_array($original) || count($original) !== 3
                    || ! array_key_exists('origen', $original) || ! array_key_exists('valor', $original)
                    || ! in_array($original['tipo'] ?? null, ['HECHO', 'EVIDENCIA_COGNITIVA'], true)
                    || (! is_scalar($original['valor']) && $original['valor'] !== null)) {
                    throw new DomainException('La extracción requiere origen, valor tipado y representación técnica explícitos.');
                }
                $origen = $original['origen'];
                if ((! $origen instanceof ControlCognitivo && ! $origen instanceof SeguimientoPedagogico
                    && ! $origen instanceof ValoracionFuncional && ! $origen instanceof AplicacionInstrumento && ! $origen instanceof ControlFuncionalIadl)
                    || ! $origen->exists || $origen->getConnectionName() !== $conexion
                    || $origen->getRawOriginal('cod_residente') !== $residenteId
                    || $origen->getRawOriginal($origen->getKeyName()) !== $fila->getRawOriginal('cod_registro_fuente')
                    || ! in_array($origen->getRawOriginal('estado'), match (true) {
                        $origen instanceof AplicacionInstrumento => ['COMPLETA'],
                        $origen instanceof ControlFuncionalIadl => ['REGISTRADO', 'RECTIFICACION'],
                        default => ['VIGENTE', 'ACTIVA'],
                    }, true)) {
                    throw new DomainException('El registro original debe existir en la misma conexión y pertenecer al caso.');
                }
                // El instante es el del registro original. La incorporación técnica
                // puede ser posterior y nunca se usa como corte clínico.
                if ($this->fechaOriginal($origen) > $fechaCorte) {
                    continue;
                }
                if ($original['tipo'] === 'EVIDENCIA_COGNITIVA') {
                    $evidencia = $this->mapeador->aDominio($fila, $origen, $fechaCorte, $original['valor']);
                    $evidencias[] = $evidencia;
                    $hechos[] = $evidencia->hecho;
                } else {
                    $hechos[] = $this->mapeador->aHecho($fila, $origen, $fechaCorte, $original['valor']);
                }
            }
        }

        return new MemoriaTrabajo($evaluacionId, $residenteId, $versionConocimiento, $fechaCorte, $hechos, $evidencias);
    }

    /**
     * Protege el contexto antes de invocar al colaborador de extracción.
     * @param EvidenciaEvaluacion $fila
     * @param string $version
     * @return void
     * @throws DomainException
     */
    private static function validarMapeo(EvidenciaEvaluacion $fila, string $version): void
    {
        $mapeo = $fila->getRelation('mapeoVariableFuente');
        if (! $mapeo instanceof MapeoVariableFuente) {
            throw new DomainException('La evidencia carece de un mapeo existente.');
        }
        $fuente = $mapeo->getRelation('fuente');
        $variable = $mapeo->getRelation('variable');
        if (! $fuente instanceof FuenteDatoExperta || ! $variable instanceof VariableExperta) {
            throw new DomainException('Fuente o variable inexistentes.');
        }
        $nodo = $variable->getRelation('nodo');
        if (! $nodo instanceof NodoSemantico
            || $mapeo->getRawOriginal('cod_mapeo_variable_fuente') !== $fila->getRawOriginal('cod_mapeo_variable_fuente')
            || $fuente->getRawOriginal('cod_fuente_dato_experta') !== $mapeo->getRawOriginal('cod_fuente_dato_experta')
            || $variable->getRawOriginal('cod_variable_experta') !== $mapeo->getRawOriginal('cod_variable_experta')
            || $nodo->getRawOriginal('cod_nodo_semantico') !== $variable->getRawOriginal('cod_nodo_semantico')
            || $nodo->getRawOriginal('tipo_nodo') !== 'VARIABLE') {
            throw new DomainException('Las FK del mapeo no representan una variable de la fuente.');
        }
        foreach ([$mapeo, $fuente, $variable, $nodo] as $dependencia) {
            if ($dependencia->getRawOriginal('cod_version_modelo') !== $version || $dependencia->getRawOriginal('estado') !== 'ACTIVO') {
                throw new DomainException('No se extrae desde dependencias inactivas o de otra versión.');
            }
        }
        $tablas = ['controles_cognitivos' => 'cod_control_cognitivo', 'seguimientos_pedagogicos' => 'cod_seguimiento_pedagogico',
            'valoraciones_funcionales' => 'cod_valoracion_funcional', 'aplicaciones_instrumento' => 'cod_aplicacion',
            'controles_funcionales_iadl' => 'cod_control_iadl'];
        $tabla = $fuente->getRawOriginal('tabla_raiz');
        if (! is_string($tabla) || ! isset($tablas[$tabla])
            || $fuente->getRawOriginal('campo_pk_raiz') !== $tablas[$tabla]
            || $fuente->getRawOriginal('campo_residente') !== 'cod_residente'
            || $fuente->getRawOriginal('campo_temporal') !== 'fecha_hora') {
            throw new DomainException('La fuente física no pertenece al contrato soportado por el mapper.');
        }
    }

    /**
     * Analiza la fecha física para excluir fuentes posteriores antes del mapeo.
     * @param Model $origen
     * @return DateTimeImmutable
     * @throws DomainException
     */
    private function fechaOriginal(Model $origen): DateTimeImmutable
    {
        $valor = $origen->getRawOriginal('fecha_hora');
        if (! is_string($valor)) {
            throw new DomainException('El registro original requiere fecha clínica explícita.');
        }
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $valor, $this->zonaAlmacenamiento);
        $errores = DateTimeImmutable::getLastErrors();
        if ($fecha === false || ($errores !== false && ($errores['warning_count'] > 0 || $errores['error_count'] > 0))
            || $fecha->format('Y-m-d H:i:s') !== $valor) {
            throw new DomainException('Fecha clínica original inválida.');
        }

        return $fecha;
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
