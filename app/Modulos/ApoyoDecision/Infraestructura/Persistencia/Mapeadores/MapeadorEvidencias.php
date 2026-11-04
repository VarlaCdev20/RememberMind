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

/** Reconstruye hechos y evidencias conservando sus fuentes, episodio y fecha originales. */
final readonly class MapeadorEvidencias
{
    /**
     * Recibe la zona explícita de las fechas SQL sin zona horaria.
     * @param DateTimeZone $zonaAlmacenamiento
     */
    public function __construct(
        /** Zona de interpretación de columnas datetime físicas. */ private DateTimeZone $zonaAlmacenamiento,
    ) {}

    /**
     * Reconstruye un hecho con el registro operativo que acredita su procedencia.
     * @param EvidenciaEvaluacion $fila
     * @param ControlCognitivo|SeguimientoPedagogico|ValoracionFuncional|AplicacionInstrumento $origen
     * @param DateTimeImmutable $corte
     * @param string|int|float|bool|null $valorExtraido
     * @return HechoExperto
     * @throws DomainException
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
     * @param EvidenciaEvaluacion $fila
     * @param ControlCognitivo|SeguimientoPedagogico|ValoracionFuncional|AplicacionInstrumento $origen
     * @param DateTimeImmutable $corte
     * @param string|int|float|bool|null $valorExtraido
     * @return EvidenciaCognitiva
     * @throws DomainException
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
     * @param string $valor
     * @return DateTimeImmutable
     * @throws DomainException
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
     * @param Model $modelo
     * @param string $campo
     * @return string
     * @throws DomainException
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
     * @param Model $modelo
     * @param string $nombre
     * @param class-string<Model> $tipo
     * @return Model
     * @throws DomainException
     */
    private static function relacion(Model $modelo, string $nombre, string $tipo): Model
    {
        if (! $modelo->relationLoaded($nombre) || ! $modelo->getRelation($nombre) instanceof $tipo) {
            throw new DomainException('Debe precargarse la relación '.$nombre.' del tipo esperado.');
        }

        return $modelo->getRelation($nombre);
    }
}
