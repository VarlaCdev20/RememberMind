<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores;

use App\Models\CriterioDominioResultado;
use App\Models\DominioValoresExperto;
use App\Models\EvaluacionCriterio;
use App\Models\EvaluacionExperta;
use App\Models\EvidenciaCriterioEvaluacion;
use App\Models\EvidenciaEvaluacion;
use App\Models\NodoSemantico;
use App\Models\ResultadoCriterio;
use App\Models\ValorSemantico;
use MotorExperto\dominio\Entities\Cognicion\ResultadoDominio;
use MotorExperto\dominio\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\EvaluabilidadEnum;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/** Reconstruye resultados y sus soportes desde los registros originales. */
final readonly class MapeadorResultados
{
    /** @var list<string> Copia independiente de los roles autorizados por el contrato recibido. */
    private array $rolesSoporte;

    /**
     * Recibe la zona explícita de las fechas técnicas del almacenamiento SQL.
     * @param DateTimeZone $zonaAlmacenamiento
     * @param list<string> $rolesSoporte
     * @throws DomainException
     */
    public function __construct(
        /** Zona de columnas datetime sin información horaria propia. */ private DateTimeZone $zonaAlmacenamiento,
        array $rolesSoporte = [],
    ) {
        if (! array_is_list($rolesSoporte)) {
            throw new DomainException('El contrato requiere una lista explícita de roles de soporte.');
        }
        $copia = $usados = [];
        foreach ($rolesSoporte as $rol) {
            if (! is_string($rol) || $rol === '' || preg_match('/[\x00-\x20\x7F]/', $rol) === 1
                || $rol === 'CONTEXTUAL' || isset($usados[$rol])) {
                throw new DomainException('Rol de soporte inválido o reservado al contexto.');
            }
            $usados[$rol] = true;
            $copia[] = $rol;
        }
        $this->rolesSoporte = $copia;
    }

    /**
     * Reconstruye un dominio con su estado literal y referencias originales.
     * @param ResultadoCriterio $fila
     * @param MemoriaTrabajo $memoria
     * @return ResultadoDominio
     * @throws DomainException
     */
    public function aDominio(ResultadoCriterio $fila, MemoriaTrabajo $memoria): ResultadoDominio
    {
        $id = self::texto($fila, 'cod_resultado_criterio');
        $criterioEvaluado = self::relacion($fila, 'evaluacionCriterio', EvaluacionCriterio::class);
        $evaluacion = self::relacion($criterioEvaluado, 'evaluacion', EvaluacionExperta::class);
        self::validarEvaluacion($evaluacion, $memoria);
        $criterio = self::relacion($criterioEvaluado, 'criterio', NodoSemantico::class);
        $contrato = self::relacion($criterio, 'criterioDominioResultado', CriterioDominioResultado::class);
        $valor = self::relacion($fila, 'valor', ValorSemantico::class);
        $dominioValores = self::relacion($valor, 'dominio', DominioValoresExperto::class);
        if (self::texto($fila, 'cod_evaluacion_criterio') !== self::texto($criterioEvaluado, 'cod_evaluacion_criterio')
            || self::texto($criterioEvaluado, 'cod_evaluacion_experta') !== self::texto($evaluacion, 'cod_evaluacion_experta')
            || self::texto($fila, 'cod_valor_semantico') !== self::texto($valor, 'cod_valor_semantico')
            || self::texto($criterioEvaluado, 'cod_nodo_criterio') !== self::texto($criterio, 'cod_nodo_semantico')
            || self::texto($criterio, 'cod_version_modelo') !== $memoria->versionConocimiento
            || self::texto($criterio, 'tipo_nodo') !== 'CRITERIO' || self::texto($criterio, 'estado') !== 'ACTIVO'
            || self::texto($contrato, 'cod_nodo_criterio') !== self::texto($criterio, 'cod_nodo_semantico')
            || self::texto($contrato, 'cod_dominio_valores') !== self::texto($valor, 'cod_dominio_valores')
            || self::texto($dominioValores, 'cod_dominio_valores') !== self::texto($valor, 'cod_dominio_valores')
            || self::texto($dominioValores, 'cod_version_modelo') !== $memoria->versionConocimiento
            || self::texto($dominioValores, 'estado') !== 'ACTIVO' || self::texto($contrato, 'estado') !== 'ACTIVO'
            || self::texto($valor, 'estado') !== 'ACTIVO' || self::texto($valor, 'estado_aprobacion') !== 'APROBADO') {
            throw new DomainException('Resultado incompatible con su criterio, catálogo o versión.');
        }
        $atributos = $criterioEvaluado->getAttributes();
        if (! array_key_exists('motivo_evaluabilidad', $atributos)
            || ! array_key_exists('codigo_modificador_interpretacion', $atributos)
            || $atributos['codigo_modificador_interpretacion'] !== null) {
            throw new DomainException('Evaluabilidad incompleta o modificador no representable en Domain.');
        }
        $dominio = DominioCognitivoEnum::tryFrom(self::texto($criterio, 'codigo_semantico'))
            ?? throw new DomainException('El criterio no es uno de los cinco dominios cognitivos.');
        $estado = EstadoDominioEnum::tryFrom(self::texto($valor, 'codigo_valor'))
            ?? throw new DomainException('El resultado semántico no tiene correspondencia literal A/P/U/I/X.');
        $codigoEvaluabilidad = self::texto($criterioEvaluado, 'estado_evaluabilidad');
        $evaluabilidad = null;
        foreach (EvaluabilidadEnum::cases() as $nivel) {
            if ($nivel->codigo($dominio) === $codigoEvaluabilidad) {
                $evaluabilidad = $nivel;
            }
        }
        if ($evaluabilidad === null) {
            throw new DomainException('Evaluabilidad de otro dominio o sin correspondencia exacta.');
        }
        // Son fechas técnicas, no fechas de evidencia: no sustituyen el corte
        // clínico ni el instante original de los hechos contenidos en la memoria.
        $fecha = $this->fecha(self::texto($fila, 'fecha_hora_determinacion'));
        $this->fecha(self::texto($criterioEvaluado, 'fecha_hora_determinacion'));
        $soportes = $ids = $vinculos = [];
        foreach (self::coleccion($criterioEvaluado, 'evidenciasCriterioEvaluacion') as $vinculo) {
            if (! $vinculo instanceof EvidenciaCriterioEvaluacion
                || self::texto($vinculo, 'cod_evaluacion_criterio') !== self::texto($criterioEvaluado, 'cod_evaluacion_criterio')
                || self::texto($vinculo, 'estado_participacion') !== 'PRUEBA_TECNICA') {
                throw new DomainException('Vínculo de soporte mal tipado, ajeno o fuera del modo experimental.');
            }
            $vinculoId = self::texto($vinculo, 'cod_evidencia_criterio');
            $evidencia = self::relacion($vinculo, 'evidencia', EvidenciaEvaluacion::class);
            $evidenciaId = self::texto($evidencia, 'cod_evidencia_evaluacion');
            if (self::texto($vinculo, 'cod_evidencia_evaluacion') !== $evidenciaId
                || self::texto($evidencia, 'cod_evaluacion_experta') !== $memoria->evaluacionId
                || ! in_array(self::texto($evidencia, 'estado_admisibilidad'), ['ADMISIBLE', 'ADMISIBLE_CON_ADVERTENCIA'], true)
                || isset($ids[$evidenciaId]) || isset($vinculos[$vinculoId])) {
                throw new DomainException('Soporte duplicado, ajeno o no admisible.');
            }
            $memoria->evidencia($evidenciaId);
            $ids[$evidenciaId] = true;
            $vinculos[$vinculoId] = true;
            $rol = self::texto($vinculo, 'rol_en_criterio');
            if ($rol === 'CONTEXTUAL') {
                // Permanece en la memoria original, pero no acredita el resultado.
                continue;
            }
            if (! in_array($rol, $this->rolesSoporte, true)) {
                throw new DomainException('Rol de soporte sin contrato versionado explícito.');
            }
            $soportes[] = $evidenciaId;
        }
        $motivo = $atributos['motivo_evaluabilidad'] ?? null;
        if ($motivo !== null && (! is_string($motivo) || trim($motivo) === '')) {
            throw new DomainException('Motivo de evaluabilidad inválido.');
        }
        $justificacion = 'Resultado persistido '.$id.'; determinación técnica '.$fecha->format(DATE_ATOM).'.';
        if ($motivo !== null) {
            $justificacion .= ' '.$motivo;
        }

        return new ResultadoDominio($dominio, $estado, $evaluabilidad, $memoria, [], $soportes, $justificacion);
    }

    /**
     * Recupera los cinco resultados explícitos sin crear dominios faltantes.
     * @param EvaluacionExperta $fila
     * @param MemoriaTrabajo $memoria
     * @return VectorCognitivo
     * @throws DomainException
     */
    public function aVector(EvaluacionExperta $fila, MemoriaTrabajo $memoria): VectorCognitivo
    {
        self::validarEvaluacion($fila, $memoria);
        $resultados = [];
        foreach (self::coleccion($fila, 'evaluacionCriterios') as $criterio) {
            if (! $criterio instanceof EvaluacionCriterio
                || self::texto($criterio, 'cod_evaluacion_experta') !== $memoria->evaluacionId) {
                throw new DomainException('Criterio evaluado mal tipado o de otra evaluación.');
            }
            $resultado = self::relacion($criterio, 'resultado', ResultadoCriterio::class);
            $origen = self::relacion($resultado, 'evaluacionCriterio', EvaluacionCriterio::class);
            if (self::texto($resultado, 'cod_evaluacion_criterio') !== self::texto($criterio, 'cod_evaluacion_criterio')
                || self::texto($origen, 'cod_nodo_criterio') !== self::texto($criterio, 'cod_nodo_criterio')
                || self::texto($origen, 'estado_evaluabilidad') !== self::texto($criterio, 'estado_evaluabilidad')) {
                throw new DomainException('El resultado no corresponde al criterio de la colección.');
            }
            $resultados[] = $this->aDominio($resultado, $memoria);
        }

        return new VectorCognitivo($resultados);
    }

    /**
     * Verifica el ámbito sintético sin consultar al residente ni deducir el corte.
     * @param EvaluacionExperta $fila
     * @param MemoriaTrabajo $memoria
     * @return void
     * @throws DomainException
     */
    private static function validarEvaluacion(EvaluacionExperta $fila, MemoriaTrabajo $memoria): void
    {
        if (self::texto($fila, 'cod_evaluacion_experta') !== $memoria->evaluacionId
            || self::texto($fila, 'cod_residente') !== $memoria->residenteId
            || self::texto($fila, 'cod_version_modelo') !== $memoria->versionConocimiento
            || ! str_starts_with($memoria->evaluacionId, 'SIM-') || strlen($memoria->evaluacionId) <= 4
            || ! str_starts_with($memoria->residenteId, 'SINTETICO-') || strlen($memoria->residenteId) <= 10
            || self::texto($fila, 'origen_activacion') !== 'PRUEBA_TECNICA'
            || self::texto($fila, 'estado_ejecucion') !== 'PRUEBA_FINALIZADA') {
            throw new DomainException('La evaluación debe ser sintética, finalizada y de la misma memoria.');
        }
    }

    /**
     * Valida una fecha SQL técnica sin conversiones de calendario silenciosas.
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
            throw new DomainException('Fecha de determinación técnica inválida.');
        }

        return $fecha;
    }

    /**
     * Lee una columna concreta sin casts ni accessors.
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
     * Exige una dependencia materializada sin acceso diferido a la base de datos.
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

    /**
     * Obtiene una colección precargada y conserva su contenido.
     * @param Model $modelo
     * @param string $nombre
     * @return Collection
     * @throws DomainException
     */
    private static function coleccion(Model $modelo, string $nombre): Collection
    {
        if (! $modelo->relationLoaded($nombre) || ! $modelo->getRelation($nombre) instanceof Collection) {
            throw new DomainException('Debe precargarse la colección '.$nombre.'.');
        }

        return $modelo->getRelation($nombre);
    }
}
