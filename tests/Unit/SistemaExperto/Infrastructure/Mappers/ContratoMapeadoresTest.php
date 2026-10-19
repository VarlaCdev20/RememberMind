<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Infrastructure\Mappers;

use App\Models;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\InventarioExperto;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorEvidencias;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorRedSemantica;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorReglas;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorResultados;
use DateTimeZone;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;

/**
 * TÍTULO: Contrato de mappers, modelos existentes y ausencia de consultas.
 * CAPA: Infrastructure - Mappers (pruebas unitarias).
 * ¿PARA QUÉ SIRVE? Comprueba estructura, PHPDoc y reutilización de los 23 modelos.
 * ¿QUÉ INFORMACIÓN RECIBE? Clases reales y modelos ficticios en memoria.
 * ¿QUÉ HACE? Inspecciona metadatos y prohíbe resolver conexiones durante la conversión.
 * ¿QUÉ DEVUELVE? Aserciones verificables de tipos y aislamiento de BDD.
 * ¿QUÉ NO DEBE HACER? No acredita tablas instaladas, integración PostgreSQL ni activación clínica.
 */
final class ContratoMapeadoresTest extends TestCase
{
    /** Comprueba los cuatro archivos, tipos y documentación; sin parámetros ni errores de lectura esperados. */
    public function test_cuatro_mappers_tipados_documentados_y_sin_modelos_duplicados(): void
    {
        $tipos = [MapeadorReglas::class, MapeadorRedSemantica::class, MapeadorEvidencias::class, MapeadorResultados::class];
        $archivos = [];
        foreach ($tipos as $tipo) {
            $r = new ReflectionClass($tipo);
            self::assertTrue($r->isFinal());
            self::assertTrue($r->isReadOnly());
            self::assertSame('App\\Modulos\\ApoyoDecision\\Infraestructura\\Persistencia\\Mapeadores', $r->getNamespaceName());
            self::assertStringStartsWith("<?php\n\ndeclare(strict_types=1);", file_get_contents($r->getFileName()));
            $archivos[] = basename($r->getFileName());
            $doc = $r->getDocComment();
            self::assertNotFalse($doc);
            foreach (['TÍTULO:', 'CAPA:', '¿PARA QUÉ SIRVE?', '¿QUÉ INFORMACIÓN RECIBE?',
                '¿QUÉ HACE?', '¿QUÉ DEVUELVE?', '¿QUÉ NO DEBE HACER?'] as $apartado) {
                self::assertStringContainsString($apartado, $doc, $tipo);
            }
            $entrada = $r->getMethod('aDominio')->getParameters()[0]->getType()->getName();
            self::assertTrue(is_subclass_of($entrada, Model::class));
            self::assertStringStartsWith('App\\Models\\', $entrada);
            foreach ($r->getProperties() as $propiedad) {
                self::assertTrue($propiedad->isReadOnly());
                self::assertNotNull($propiedad->getType());
                self::assertNotFalse($propiedad->getDocComment());
            }
            foreach ($r->getMethods() as $metodo) {
                self::assertNotFalse($metodo->getDocComment(), $tipo.'::'.$metodo->getName());
                foreach ($metodo->getParameters() as $parametro) {
                    self::assertNotNull($parametro->getType());
                }
            }
        }
        $reales = array_map('basename', glob(dirname((new ReflectionClass(MapeadorReglas::class))->getFileName()).'/*.php'));
        sort($archivos);
        sort($reales);
        self::assertSame($archivos, $reales);
    }

    /** Contrasta metadatos físicos, sin afirmar instalación de tablas; sin parámetros ni excepciones esperadas. */
    public function test_los_23_modelos_existentes_conservan_tablas_y_pk_d137(): void
    {
        $modelos = [
            'versiones_modelo_experto' => [Models\VersionModeloExperto::class, 'cod_version_modelo'],
            'nodos_semanticos' => [Models\NodoSemantico::class, 'cod_nodo_semantico'],
            'relaciones_semanticas' => [Models\RelacionSemantica::class, 'cod_relacion_semantica'],
            'dominios_valores_expertos' => [Models\DominioValoresExperto::class, 'cod_dominio_valores'],
            'valores_semanticos' => [Models\ValorSemantico::class, 'cod_valor_semantico'],
            'variables_expertas' => [Models\VariableExperta::class, 'cod_variable_experta'],
            'fuentes_datos_expertas' => [Models\FuenteDatoExperta::class, 'cod_fuente_dato_experta'],
            'mapeos_variables_fuente' => [Models\MapeoVariableFuente::class, 'cod_mapeo_variable_fuente'],
            'mapeos_valores_fuente' => [Models\MapeoValorFuente::class, 'cod_mapeo_valor_fuente'],
            'criterios_dominios_resultado' => [Models\CriterioDominioResultado::class, 'cod_criterio_dominio_resultado'],
            'reglas_expertas' => [Models\ReglaExperta::class, 'cod_regla_experta'],
            'condiciones_regla_experta' => [Models\CondicionReglaExperta::class, 'cod_condicion_regla'],
            'consecuencias_regla_experta' => [Models\ConsecuenciaReglaExperta::class, 'cod_consecuencia_regla'],
            'evaluaciones_expertas' => [Models\EvaluacionExperta::class, 'cod_evaluacion_experta'],
            'evidencias_evaluacion' => [Models\EvidenciaEvaluacion::class, 'cod_evidencia_evaluacion'],
            'relaciones_evidencias_evaluacion' => [Models\RelacionEvidenciaEvaluacion::class, 'cod_relacion_evidencia'],
            'evaluacion_criterios' => [Models\EvaluacionCriterio::class, 'cod_evaluacion_criterio'],
            'evidencias_criterio_evaluacion' => [Models\EvidenciaCriterioEvaluacion::class, 'cod_evidencia_criterio'],
            'resultados_criterio' => [Models\ResultadoCriterio::class, 'cod_resultado_criterio'],
            'trazas_inferencia' => [Models\TrazaInferencia::class, 'cod_traza_inferencia'],
            'evaluaciones_reglas' => [Models\EvaluacionRegla::class, 'cod_evaluacion_regla'],
            'evaluaciones_condiciones_regla' => [Models\EvaluacionCondicionRegla::class, 'cod_evaluacion_condicion'],
            'evidencias_soporte_condicion' => [Models\EvidenciaSoporteCondicion::class, 'cod_evidencia_soporte_condicion'],
        ];
        self::assertSame(InventarioExperto::TABLAS, array_keys($modelos));
        foreach ($modelos as $tabla => [$tipo, $pk]) {
            $modelo = new $tipo;
            self::assertSame($tabla, $modelo->getTable());
            self::assertSame($pk, $modelo->getKeyName());
            self::assertSame('string', $modelo->getKeyType());
            self::assertFalse($modelo->getIncrementing());
            self::assertFalse($modelo->timestamps);
            self::assertFalse($modelo->exists);
        }
    }

    /** Demuestra conversión sin resolver conexiones ni guardar filas; sin parámetros ni excepciones esperadas. */
    public function test_los_cuatro_mappers_funcionan_sin_consultas_ni_conexion(): void
    {
        $anterior = Model::getConnectionResolver();
        $resolver = $this->createMock(ConnectionResolverInterface::class);
        $resolver->expects(self::never())->method('connection');
        Model::setConnectionResolver($resolver);
        try {
            $regla = (new MapeadorReglas('RESULTADO'))->aDominio(EscenarioMapeadores::regla());
            $red = (new MapeadorRedSemantica)->aDominio(EscenarioMapeadores::red());
            [$fila, $original] = EscenarioMapeadores::evidencia();
            $evidencia = (new MapeadorEvidencias(new DateTimeZone('America/La_Paz')))
                ->aDominio($fila, $original, F::fecha(), false);
            [$evaluacion, $memoria] = EscenarioMapeadores::resultados();
            $vector = (new MapeadorResultados(new DateTimeZone('America/La_Paz'), ['SIM-SOPORTE']))
                ->aVector($evaluacion, $memoria);

            self::assertSame('SIM-R', $regla->id);
            self::assertCount(2, $red->nodos);
            self::assertFalse($evidencia->hecho->valor);
            self::assertCount(5, $vector->resultados);
            self::assertFalse($fila->exists);
            self::assertFalse($evaluacion->exists);
        } finally {
            // La prueba restaura el estado global incluso cuando detecta un fallo.
            if ($anterior === null) {
                Model::unsetConnectionResolver();
            } else {
                Model::setConnectionResolver($anterior);
            }
        }
    }
}
