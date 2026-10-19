<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores;

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
use MotorExperto\dominio\orion\Entities\Hechos\EvidenciaCognitiva;
use MotorExperto\dominio\orion\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Database\Eloquent\Model;

/**
 * TÍTULO:
 * Conversión de evidencias con procedencia operativa verificable.
 *
 * CAPA:
 * Infrastructure - Persistence - Mappers.
 *
 * ¿PARA QUÉ SIRVE?
 * Reconstruye hechos y descripciones cognitivas desde modelos ya materializados,
 * conservando evaluación, fuente, registro, episodio y fecha originales.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * EvidenciaEvaluacion con evaluación y mapeo cargados, modelo operativo original,
 * valor tipado entregado por el extractor autorizado, corte y zona de almacenamiento.
 *
 * ¿QUÉ HACE?
 * Verifica FK/versiones, fuente, sujeto sintético, vigencia y fecha. Usa la atención
 * original como episodio; en seguimientos usa la actividad vinculada, si existe.
 * La ausencia de ese vínculo se rechaza, sin inventar un episodio.
 *
 * ¿QUÉ DEVUELVE?
 * HechoExperto o EvidenciaCognitiva descriptiva con valor intacto, incluido NULL.
 *
 * ¿QUÉ NO DEBE HACER?
 * No extrae columnas configurables, consulta fuentes, interpreta puntuaciones ni
 * genera A/P. D-137 no conserva valor bruto, episodio o fecha clínica: no se usa
 * fecha_hora_incorporacion para sustituirlos. El extractor debe aportar el valor
 * original ya tipado; este mapper lo conserva sin realizar otra extracción.
 */
final readonly class MapeadorEvidencias
{
    /**
     * Recibe la zona explícita de las fechas SQL sin zona horaria.
     *
     * @param  DateTimeZone  $zonaAlmacenamiento  Zona del entorno segregado.
     *                                            No consulta configuración global ni se esperan excepciones.
     */
    public function __construct(
        /** Zona de interpretación de columnas datetime físicas. */ private DateTimeZone $zonaAlmacenamiento,
    ) {}

    /**
     * Reconstruye un hecho con el registro operativo que acredita su procedencia.
     *
     * @param  EvidenciaEvaluacion  $fila  Evidencia con evaluacion y mapeoVariableFuente precargados.
     * @param  ControlCognitivo|SeguimientoPedagogico|ValoracionFuncional|AplicacionInstrumento  $origen  Registro fuente original.
     * @param  DateTimeImmutable  $corte  Última fecha clínica admitida, inclusiva.
     * @param  string|int|float|bool|null  $valorExtraido  Dato original ya tipado por el extractor autorizado.
     * @return HechoExperto Hecho sin normalización ni interpretación clínica.
     *
     * @throws DomainException Por contexto, fuente, versión, episodio o fecha incompatibles.
     */
    public function aHecho(
        EvidenciaEvaluacion $fila,
        ControlCognitivo|SeguimientoPedagogico|ValoracionFuncional|AplicacionInstrumento|ControlFuncionalIadl $origen,
        DateTimeImmutable $corte,
        string|int|float|bool|null $valorExtraido,
    ): HechoExperto {
        $evaluacion = self::relacion($fila, 'evaluacion', EvaluacionExperta::class);
        $mapeo = self::relacion($fila, 'mapeoVariableFuente', MapeoVariableFuente::class);
        $fuente = self::relacion($mapeo, 'fuente', FuenteDatoExperta::class);
        $variable = self::relacion($mapeo, 'variable', VariableExperta::class);
        $nodo = self::relacion($variable, 'nodo', NodoSemantico::class);
        $evaluacionId = self::texto($evaluacion, 'cod_evaluacion_experta');
        $residenteId = self::texto($evaluacion, 'cod_residente');
        $version = self::texto($evaluacion, 'cod_version_modelo');
        if (! str_starts_with($evaluacionId, 'SIM-') || strlen($evaluacionId) <= 4
            || ! str_starts_with($residenteId, 'SINTETICO-') || strlen($residenteId) <= 10
            || self::texto($evaluacion, 'origen_activacion') !== 'PRUEBA_TECNICA') {
            throw new DomainException('El mapper de evidencias solo admite contextos sintéticos explícitos.');
        }
        if (self::texto($fila, 'cod_evaluacion_experta') !== $evaluacionId
            || self::texto($fila, 'cod_mapeo_variable_fuente') !== self::texto($mapeo, 'cod_mapeo_variable_fuente')
            || self::texto($mapeo, 'cod_fuente_dato_experta') !== self::texto($fuente, 'cod_fuente_dato_experta')
            || self::texto($mapeo, 'cod_variable_experta') !== self::texto($variable, 'cod_variable_experta')
            || self::texto($variable, 'cod_nodo_semantico') !== self::texto($nodo, 'cod_nodo_semantico')
            || self::texto($nodo, 'tipo_nodo') !== 'VARIABLE') {
            throw new DomainException('La evidencia contiene referencias físicas incoherentes.');
        }
        foreach ([$mapeo, $fuente, $variable, $nodo] as $dependencia) {
            if (self::texto($dependencia, 'cod_version_modelo') !== $version || self::texto($dependencia, 'estado') !== 'ACTIVO') {
                throw new DomainException('Fuente, variable o mapeo de otra versión o no habilitados.');
            }
        }
        // Los nombres de tabla/PK solo se contrastan. Nunca se usan en consultas
        // ni para leer una columna escogida por el cliente.
        if (self::texto($fuente, 'tabla_raiz') !== $origen->getTable()
            || self::texto($fuente, 'campo_pk_raiz') !== $origen->getKeyName()
            || self::texto($fuente, 'campo_residente') !== 'cod_residente'
            || self::texto($fuente, 'campo_temporal') !== 'fecha_hora'
            || self::texto($fila, 'cod_registro_fuente') !== self::texto($origen, $origen->getKeyName())
            || self::texto($origen, 'cod_residente') !== $residenteId
            || ! in_array(self::texto($origen, 'estado'), match (true) {
                $origen instanceof AplicacionInstrumento => ['COMPLETA'],
                $origen instanceof ControlFuncionalIadl => ['REGISTRADO', 'RECTIFICACION'],
                default => ['VIGENTE', 'ACTIVA'],
            }, true)
            || ! in_array(self::texto($fila, 'estado_admisibilidad'), ['ADMISIBLE', 'ADMISIBLE_CON_ADVERTENCIA'], true)) {
            throw new DomainException('Origen, sujeto o admisibilidad incompatibles con la fuente autorizada.');
        }
        $episodio = self::texto($origen, $origen instanceof SeguimientoPedagogico ? 'cod_actividad' : 'cod_atencion');
        $fecha = $this->fecha(self::texto($origen, 'fecha_hora'));
        $hecho = new HechoExperto(
            self::texto($fila, 'cod_evidencia_evaluacion'), $evaluacionId, $residenteId,
            $episodio, self::texto($fuente, 'codigo_fuente'), self::texto($fila, 'cod_registro_fuente'),
            $version, self::texto($nodo, 'codigo_semantico'), $fecha, $valorExtraido,
        );
        $hecho->exigirContexto($evaluacionId, $residenteId, $version, $corte);

        return $hecho;
    }

    /**
     * Sitúa una observación en el propietario primario documentado de su variable.
     *
     * @param  EvidenciaEvaluacion  $fila  Evidencia y dependencias precargadas.
     * @param  ControlCognitivo|SeguimientoPedagogico|ValoracionFuncional|AplicacionInstrumento  $origen  Fuente original.
     * @param  DateTimeImmutable  $corte  Corte inclusivo del caso.
     * @param  string|int|float|bool|null  $valorExtraido  Valor bruto ya tipado, sin baremación.
     * @return EvidenciaCognitiva Descripción del dominio/subcomponente explícitamente vinculado.
     *
     * @throws DomainException Por propietario no documentado o incumplimiento de aHecho.
     */
    public function aDominio(
        EvidenciaEvaluacion $fila,
        ControlCognitivo|SeguimientoPedagogico|ValoracionFuncional|AplicacionInstrumento|ControlFuncionalIadl $origen,
        DateTimeImmutable $corte,
        string|int|float|bool|null $valorExtraido,
    ): EvidenciaCognitiva {
        if ($origen instanceof ControlFuncionalIadl) {
            throw new DomainException('IADL solo aporta hechos descriptivos; no se transforma en evidencia cognitiva.');
        }
        $hecho = $this->aHecho($fila, $origen, $corte, $valorExtraido);
        $mapeo = self::relacion($fila, 'mapeoVariableFuente', MapeoVariableFuente::class);
        $variable = self::relacion($mapeo, 'variable', VariableExperta::class);
        $propietario = self::relacion($variable, 'propietarioPrimario', NodoSemantico::class);
        if (self::texto($variable, 'cod_nodo_propietario_primario') !== self::texto($propietario, 'cod_nodo_semantico')
            || self::texto($propietario, 'cod_version_modelo') !== $hecho->versionConocimiento
            || self::texto($propietario, 'estado') !== 'ACTIVO') {
            throw new DomainException('Propietario primario de otro nodo, versión o estado.');
        }
        $codigo = self::texto($propietario, 'codigo_semantico');
        $subcomponente = SubcomponenteCognitivoEnum::tryFrom($codigo);
        $dominio = $subcomponente?->dominio() ?? DominioCognitivoEnum::tryFrom($codigo)
            ?? throw new DomainException('La fuente no tiene un dominio cognitivo explícitamente representable.');
        $instrumento = $origen instanceof AplicacionInstrumento ? self::texto($origen, 'cod_instrumento') : null;

        return new EvidenciaCognitiva(
            $hecho, $dominio, $subcomponente,
            $valorExtraido === null ? HallazgoInstrumentalEnum::DESCONOCIDO : HallazgoInstrumentalEnum::HALLAZGO_DESCRIPTIVO,
            $instrumento, 'Registro original '.$hecho->origenId.' transportado sin interpretación clínica.',
        );
    }

    /**
     * Analiza el formato SQL exacto sin aceptar fechas normalizadas o relativas.
     *
     * @param  string  $valor  Fecha física con precisión de segundos.
     * @return DateTimeImmutable Instante en la zona explícitamente recibida.
     *
     * @throws DomainException Por formato o fecha de calendario inválidos.
     */
    private function fecha(string $valor): DateTimeImmutable
    {
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $valor, $this->zonaAlmacenamiento);
        $errores = DateTimeImmutable::getLastErrors();
        if ($fecha === false || ($errores !== false && ($errores['warning_count'] > 0 || $errores['error_count'] > 0))
            || $fecha->format('Y-m-d H:i:s') !== $valor) {
            throw new DomainException('Fecha clínica inválida; no se sustituye por la fecha de incorporación.');
        }

        return $fecha;
    }

    /**
     * Lee una columna concreta sin activar accessors que completen datos ausentes.
     *
     * @param  Model  $modelo  Registro materializado.
     * @param  string  $campo  Columna física del contrato.
     * @return string Texto original no vacío.
     *
     * @throws DomainException Por columna ausente, NULL o tipo incompatible.
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
     * Recupera dependencias ya cargadas, sin resolverlas desde la base de datos.
     *
     * @param  Model  $modelo  Propietario de la relación.
     * @param  string  $nombre  Relación fija de App\Models.
     * @param  class-string<Model>  $tipo  Tipo esperado.
     * @return Model Objeto relacionado original.
     *
     * @throws DomainException Por relación ausente o incorrectamente tipada.
     */
    private static function relacion(Model $modelo, string $nombre, string $tipo): Model
    {
        if (! $modelo->relationLoaded($nombre) || ! $modelo->getRelation($nombre) instanceof $tipo) {
            throw new DomainException('Debe precargarse la relación '.$nombre.' del tipo esperado.');
        }

        return $modelo->getRelation($nombre);
    }
}
