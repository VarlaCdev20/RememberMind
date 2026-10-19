<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Infrastructure\Mappers;

use App\Models\CondicionReglaExperta;
use App\Models\ConsecuenciaReglaExperta;
use App\Models\ControlCognitivo;
use App\Models\CriterioDominioResultado;
use App\Models\DominioValoresExperto;
use App\Models\EvaluacionCriterio;
use App\Models\EvaluacionExperta;
use App\Models\EvidenciaCriterioEvaluacion;
use App\Models\EvidenciaEvaluacion;
use App\Models\FuenteDatoExperta;
use App\Models\MapeoVariableFuente;
use App\Models\NodoSemantico;
use App\Models\ReglaExperta;
use App\Models\RelacionSemantica;
use App\Models\ResultadoCriterio;
use App\Models\ValorSemantico;
use App\Models\VariableExperta;
use App\Models\VersionModeloExperto;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;

/**
 * TÍTULO: Modelos Eloquent ficticios para conversión en memoria.
 * CAPA: Infrastructure - Mappers (fixtures unitarias).
 * ¿PARA QUÉ SIRVE? Proporciona filas normalizadas con relaciones cargadas explícitamente.
 * ¿QUÉ INFORMACIÓN RECIBE? Valores sintéticos y cambios controlados de los tests.
 * ¿QUÉ HACE? Materializa App\Models mediante atributos físicos sin guardarlos.
 * ¿QUÉ DEVUELVE? Objetos Eloquent reales y memorias reales de Domain.
 * ¿QUÉ NO DEBE HACER? No crea tablas, fuentes institucionales ni catálogos persistidos.
 * Los estados A/P/U/I/X en filas de prueba no acreditan que ese catálogo esté instalado.
 */
final class EscenarioMapeadores
{
    /**
     * Materializa una fila sin fill, casts, eventos de guardado o acceso a BDD.
     *
     * @param  class-string<Model>  $tipo  Tipo Eloquent existente.
     * @param  array<string, mixed>  $atributos  Columnas físicas del escenario.
     * @return Model Modelo nuevo, no persistido.
     *               Sin excepciones esperadas con los tipos suministrados.
     */
    public static function modelo(string $tipo, array $atributos): Model
    {
        return (new $tipo)->setRawAttributes($atributos);
    }

    /**
     * Construye un concepto con sus columnas originales.
     *
     * @param  string  $id  PK de nodo ficticia.
     * @param  string  $codigo  Código semántico.
     * @param  string  $tipo  Tipo documental.
     * @param  string  $version  Versión propietaria.
     * @return NodoSemantico Fila sin persistir.
     *                       Sin excepciones esperadas.
     */
    public static function nodo(string $id, string $codigo, string $tipo = 'CRITERIO', string $version = 'SIM-V1'): NodoSemantico
    {
        return self::modelo(NodoSemantico::class, ['cod_nodo_semantico' => $id, 'cod_version_modelo' => $version,
            'codigo_semantico' => $codigo, 'nombre' => 'Concepto ficticio '.$codigo, 'tipo_nodo' => $tipo,
            'definicion' => 'Definición artificial para probar el contrato.', 'estado' => 'ACTIVO']);
    }

    /** @return VersionModeloExperto Grafo explícito de dos nodos y una arista; sin parámetros ni excepciones. */
    public static function red(): VersionModeloExperto
    {
        $version = self::modelo(VersionModeloExperto::class, ['cod_version_modelo' => 'SIM-V1', 'estado' => 'ACTIVO']);
        $a = self::nodo('SIM-N-SUB', 'MEM-S01', 'SUBCOMPONENTE');
        $b = self::nodo('SIM-N-MEM', 'COG-MEM');
        $relacion = self::modelo(RelacionSemantica::class, ['cod_relacion_semantica' => 'SIM-REL',
            'cod_version_modelo' => 'SIM-V1', 'cod_nodo_origen' => 'SIM-N-SUB', 'cod_nodo_destino' => 'SIM-N-MEM',
            'tipo_relacion' => 'SUBCOMPONENTE_DE', 'modalidad_relacion' => null, 'estado' => 'ACTIVO']);
        $version->setRelation('nodosSemanticos', new Collection([$a, $b]));
        $version->setRelation('relacionesSemanticas', new Collection([$relacion]));

        return $version;
    }

    /** @return ReglaExperta Regla de dos condiciones y una consecuencia; sin parámetros ni excepciones. */
    public static function regla(): ReglaExperta
    {
        $regla = self::modelo(ReglaExperta::class, ['cod_regla_experta' => 'SIM-R', 'cod_version_modelo' => 'SIM-V1',
            'tipo_regla' => 'RESULTADO', 'estado' => 'ACTIVO']);
        $regla->setRelation('version', self::modelo(VersionModeloExperto::class, ['cod_version_modelo' => 'SIM-V1', 'estado' => 'ACTIVO']));
        $dominio = self::modelo(DominioValoresExperto::class, ['cod_dominio_valores' => 'SIM-DV-VAR', 'cod_version_modelo' => 'SIM-V1', 'estado' => 'ACTIVO']);
        $valor = self::modelo(ValorSemantico::class, ['cod_valor_semantico' => 'SIM-VS', 'cod_dominio_valores' => 'SIM-DV-VAR',
            'codigo_valor' => '0', 'estado' => 'ACTIVO', 'estado_aprobacion' => 'APROBADO']);
        $valor->setRelation('dominio', $dominio);
        $condiciones = [];
        foreach ([1, 2] as $numero) {
            $variable = self::modelo(VariableExperta::class, ['cod_variable_experta' => 'SIM-VAR-'.$numero,
                'cod_version_modelo' => 'SIM-V1', 'cod_nodo_semantico' => 'SIM-N-VAR-'.$numero,
                'cod_dominio_valores' => 'SIM-DV-VAR', 'estado' => 'ACTIVO']);
            $variable->setRelation('nodo', self::nodo('SIM-N-VAR-'.$numero, 'VAR-SIM-'.$numero, 'VARIABLE'));
            $condicion = self::modelo(CondicionReglaExperta::class, ['cod_condicion_regla' => 'SIM-C-'.$numero,
                'cod_regla_experta' => 'SIM-R', 'cod_variable_experta' => 'SIM-VAR-'.$numero, 'cod_valor_semantico' => 'SIM-VS']);
            $condicion->setRelation('variable', $variable)->setRelation('valor', $valor);
            $condiciones[] = $condicion;
        }
        $criterio = self::nodo('SIM-N-MEM', 'COG-MEM');
        $dominioSalida = self::modelo(DominioValoresExperto::class, ['cod_dominio_valores' => 'SIM-DV-RES', 'cod_version_modelo' => 'SIM-V1', 'estado' => 'ACTIVO']);
        $valorSalida = self::modelo(ValorSemantico::class, ['cod_valor_semantico' => 'SIM-VS-RES', 'cod_dominio_valores' => 'SIM-DV-RES',
            'codigo_valor' => 'DIFICULTAD_EVIDENCIADA', 'estado' => 'ACTIVO', 'estado_aprobacion' => 'APROBADO']);
        $valorSalida->setRelation('dominio', $dominioSalida);
        $criterioDominio = self::modelo(CriterioDominioResultado::class, ['cod_criterio_dominio_resultado' => 'SIM-CDR',
            'cod_nodo_criterio' => 'SIM-N-MEM', 'cod_dominio_valores' => 'SIM-DV-RES', 'estado' => 'ACTIVO']);
        $criterioDominio->setRelation('criterio', $criterio);
        $consecuencia = self::modelo(ConsecuenciaReglaExperta::class, ['cod_consecuencia_regla' => 'SIM-CONS',
            'cod_regla_experta' => 'SIM-R', 'cod_criterio_dominio_resultado' => 'SIM-CDR', 'cod_valor_semantico' => 'SIM-VS-RES']);
        $consecuencia->setRelation('criterioDominioResultado', $criterioDominio)->setRelation('valor', $valorSalida);
        $regla->setRelation('condicionesReglaExperta', new Collection($condiciones));
        $regla->setRelation('consecuenciasReglaExperta', new Collection([$consecuencia]));

        return $regla;
    }

    /** @return array{EvidenciaEvaluacion, ControlCognitivo} Evidencia y fuente originales; sin parámetros ni excepciones. */
    public static function evidencia(): array
    {
        $evaluacion = self::modelo(EvaluacionExperta::class, ['cod_evaluacion_experta' => 'SIM-EVAL',
            'cod_residente' => 'SINTETICO-RES', 'cod_version_modelo' => 'SIM-V1', 'origen_activacion' => 'PRUEBA_TECNICA']);
        $fuente = self::modelo(FuenteDatoExperta::class, ['cod_fuente_dato_experta' => 'SIM-FU', 'cod_version_modelo' => 'SIM-V1',
            'codigo_fuente' => 'SIM-CONTROLES', 'tabla_raiz' => 'controles_cognitivos', 'campo_pk_raiz' => 'cod_control_cognitivo',
            'campo_residente' => 'cod_residente', 'campo_temporal' => 'fecha_hora', 'estado' => 'ACTIVO']);
        $variable = self::modelo(VariableExperta::class, ['cod_variable_experta' => 'SIM-VAR', 'cod_version_modelo' => 'SIM-V1',
            'cod_nodo_semantico' => 'SIM-N-VAR', 'cod_nodo_propietario_primario' => 'SIM-N-MEM', 'estado' => 'ACTIVO']);
        $variable->setRelation('nodo', self::nodo('SIM-N-VAR', 'VAR-MEM-OBS-01', 'VARIABLE'));
        $variable->setRelation('propietarioPrimario', self::nodo('SIM-N-MEM', 'COG-MEM'));
        $mapeo = self::modelo(MapeoVariableFuente::class, ['cod_mapeo_variable_fuente' => 'SIM-MAP', 'cod_version_modelo' => 'SIM-V1',
            'cod_fuente_dato_experta' => 'SIM-FU', 'cod_variable_experta' => 'SIM-VAR', 'estado' => 'ACTIVO']);
        $mapeo->setRelation('fuente', $fuente)->setRelation('variable', $variable);
        $fila = self::modelo(EvidenciaEvaluacion::class, ['cod_evidencia_evaluacion' => 'SIM-EVID',
            'cod_evaluacion_experta' => 'SIM-EVAL', 'cod_mapeo_variable_fuente' => 'SIM-MAP',
            'cod_registro_fuente' => 'SIM-CTRL', 'estado_admisibilidad' => 'ADMISIBLE',
            'fecha_hora_incorporacion' => '2026-10-18 12:00:00']);
        $fila->setRelation('evaluacion', $evaluacion)->setRelation('mapeoVariableFuente', $mapeo);
        $original = self::modelo(ControlCognitivo::class, ['cod_control_cognitivo' => 'SIM-CTRL', 'cod_residente' => 'SINTETICO-RES',
            'cod_atencion' => 'SIM-AT', 'fecha_hora' => '2026-10-17 12:00:00', 'estado' => 'VIGENTE']);

        return [$fila, $original];
    }

    /**
     * Prepara cinco filas de resultados con soportes originales ya validados.
     *
     * @param  list<string>  $estados  Estados manuales del contrato de Domain.
     * @return array{EvaluacionExperta, MemoriaTrabajo} Cabecera Eloquent y memoria original.
     *                                                  Sin excepciones esperadas con los perfiles sintéticos solicitados.
     */
    public static function resultados(array $estados = ['A', 'P', 'U', 'I', 'X']): array
    {
        $perfil = F::perfil($estados);
        $memoria = $perfil->cognicion->memoria;
        $evaluacion = self::modelo(EvaluacionExperta::class, ['cod_evaluacion_experta' => 'SIM-EVAL',
            'cod_residente' => 'SINTETICO-RES', 'cod_version_modelo' => 'SIM-V1', 'origen_activacion' => 'PRUEBA_TECNICA',
            'estado_ejecucion' => 'PRUEBA_FINALIZADA']);
        $criterios = [];
        foreach ($perfil->cognicion->resultados as $codigo => $resultado) {
            $dominioId = 'SIM-DV-'.$codigo;
            $nodo = self::nodo('SIM-N-'.$codigo, $codigo);
            $dominio = self::modelo(DominioValoresExperto::class, ['cod_dominio_valores' => $dominioId, 'cod_version_modelo' => 'SIM-V1', 'estado' => 'ACTIVO']);
            $contrato = self::modelo(CriterioDominioResultado::class, ['cod_criterio_dominio_resultado' => 'SIM-CDR-'.$codigo,
                'cod_nodo_criterio' => 'SIM-N-'.$codigo, 'cod_dominio_valores' => $dominioId, 'estado' => 'ACTIVO']);
            $nodo->setRelation('criterioDominioResultado', $contrato);
            $valor = self::modelo(ValorSemantico::class, ['cod_valor_semantico' => 'SIM-VS-'.$codigo,
                'cod_dominio_valores' => $dominioId, 'codigo_valor' => $resultado->estado->value,
                'estado' => 'ACTIVO', 'estado_aprobacion' => 'APROBADO']);
            $valor->setRelation('dominio', $dominio);
            $ec = self::modelo(EvaluacionCriterio::class, ['cod_evaluacion_criterio' => 'SIM-EC-'.$codigo,
                'cod_evaluacion_experta' => 'SIM-EVAL', 'cod_nodo_criterio' => 'SIM-N-'.$codigo,
                'estado_evaluabilidad' => $resultado->evaluabilidad->codigo($resultado->dominio),
                'codigo_modificador_interpretacion' => null, 'motivo_evaluabilidad' => null,
                'fecha_hora_determinacion' => '2026-10-18 12:00:00']);
            $ec->setRelation('evaluacion', $evaluacion)->setRelation('criterio', $nodo);
            $vinculos = [];
            foreach ($resultado->evidencias as $indice => $soporte) {
                $evidencia = self::modelo(EvidenciaEvaluacion::class, ['cod_evidencia_evaluacion' => $soporte->hecho->id,
                    'cod_evaluacion_experta' => 'SIM-EVAL', 'estado_admisibilidad' => 'ADMISIBLE']);
                $vinculo = self::modelo(EvidenciaCriterioEvaluacion::class, ['cod_evidencia_criterio' => 'SIM-V-'.$codigo.'-'.$indice,
                    'cod_evaluacion_criterio' => 'SIM-EC-'.$codigo, 'cod_evidencia_evaluacion' => $soporte->hecho->id,
                    'rol_en_criterio' => 'SIM-SOPORTE', 'estado_participacion' => 'PRUEBA_TECNICA']);
                $vinculo->setRelation('evidencia', $evidencia);
                $vinculos[] = $vinculo;
            }
            $ec->setRelation('evidenciasCriterioEvaluacion', new Collection($vinculos));
            $fila = self::modelo(ResultadoCriterio::class, ['cod_resultado_criterio' => 'SIM-RES-'.$codigo,
                'cod_evaluacion_criterio' => 'SIM-EC-'.$codigo, 'cod_valor_semantico' => 'SIM-VS-'.$codigo,
                'fecha_hora_determinacion' => '2026-10-18 12:00:00']);
            $fila->setRelation('evaluacionCriterio', $ec)->setRelation('valor', $valor);
            $ec->setRelation('resultado', $fila);
            $criterios[] = $ec;
        }
        $evaluacion->setRelation('evaluacionCriterios', new Collection($criterios));

        return [$evaluacion, $memoria];
    }
}
