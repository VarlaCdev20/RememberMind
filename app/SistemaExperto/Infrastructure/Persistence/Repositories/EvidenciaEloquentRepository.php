<?php

declare(strict_types=1);

namespace App\SistemaExperto\Infrastructure\Persistence\Repositories;

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
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\Repositories\Hechos\EvidenciaRepositoryInterface;
use App\SistemaExperto\Infrastructure\Persistence\Mappers\MapeadorEvidencias;
use Closure;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

/**
 * TÍTULO:
 * Recuperación Eloquent de evidencias con sus fuentes originales.
 *
 * CAPA:
 * Infrastructure - Persistence - Repositories.
 *
 * ¿PARA QUÉ SIRVE?
 * Implementa la memoria de lectura del caso experimental sin inferencia.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Evaluación, residente sintético, versión, corte y una extracción autorizada
 * por lotes. Cada origen aporta el modelo, el valor bruto tipado y el destino
 * técnico HECHO o EVIDENCIA_COGNITIVA; no es una interpretación clínica.
 *
 * ¿QUÉ HACE?
 * Acota la cabecera, precarga mapeos activos coherentes, solicita los registros
 * originales al colaborador inyectado y conserva los anteriores al corte.
 * Excluye filas no admitidas sin convertir esa ausencia en normalidad.
 *
 * ¿QUÉ DEVUELVE?
 * MemoriaTrabajo con hechos/evidencias originales o memoria vacía contextual.
 *
 * ¿QUÉ NO DEBE HACER?
 * No implementa anticipadamente los adaptadores de la etapa D, usa nombres
 * configurables como SQL, inventa valores/procedencia ni acredita A/P.
 * La extracción debe validar tipos/selectores y claves de adaptador autorizados.
 * Si falta un origen solicitado, falla explícitamente; no disimula la falta.
 */
final readonly class EvidenciaEloquentRepository implements EvidenciaRepositoryInterface
{
    /**
     * Recibe la extracción autorizada; no la elige ni registra globalmente.
     *
     * @param  MapeadorEvidencias  $mapeador  Conversor existente.
     * @param  Closure(Collection<int, EvidenciaEvaluacion>, DateTimeImmutable, string): array<string, array{origen: ControlCognitivo|SeguimientoPedagogico|ValoracionFuncional|AplicacionInstrumento, valor: string|int|float|bool|null, tipo: 'HECHO'|'EVIDENCIA_COGNITIVA'}>  $extraerOrigenes  Extracción por lote sobre la misma conexión, sin efectos de escritura.
     * @param  DateTimeZone  $zonaAlmacenamiento  Misma zona SQL configurada para el mapper.
     * @param  string  $conexionExperimental  Conexión segregada explícita.
     *
     * @throws DomainException Por nombre de conexión inválido.
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
     *
     * @param  string  $evaluacionId  Evaluación artificial propietaria.
     * @param  string  $residenteId  Caso sintético propietario.
     * @param  string  $versionConocimiento  Versión exacta.
     * @param  DateTimeImmutable  $fechaCorte  Último instante clínico admitido.
     * @return MemoriaTrabajo Instantánea con procedencia sin normalización clínica.
     *
     * @throws DomainException Por contexto, origen, representación, fecha o duplicación incompatibles.
     * @throws RuntimeException Por destino no autorizado, origen no disponible o fallo técnico.
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
     *
     * @param  EvidenciaEvaluacion  $fila  Evidencia con dependencias precargadas.
     * @param  string  $version  Versión propietaria.
     * @return void No consulta tablas operativas ni interpreta selectores.
     *
     * @throws DomainException Por FK, versión, fuente o estado no autorizados.
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
     *
     * @param  Model  $origen  Modelo operativo materializado y autorizado.
     * @return DateTimeImmutable Instante clínico original en la zona explícita.
     *
     * @throws DomainException Por fecha ausente, inválida o normalizada silenciosamente.
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
