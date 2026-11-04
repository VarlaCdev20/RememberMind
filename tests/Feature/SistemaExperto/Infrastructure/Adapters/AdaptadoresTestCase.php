<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Adapters;

use App\Models\EvaluacionExperta;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorControlesCognitivos;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorIadlExperimental;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorInstrumentos;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorSeguimientosPedagogicos;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorValoracionesFuncionales;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\MapeoFuentes\ExtractorVariableMapeada;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\MapeoFuentes\ResolutorFuenteExperta;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorEvidencias;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\EvidenciaEloquentRepository;
use Closure;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios;
use Tests\Feature\SistemaExperto\Infrastructure\Repositories\RepositoriosTestCase;

/** Recupera datos brutos autorizados sin interpretación clínica. */
abstract class AdaptadoresTestCase extends RepositoriosTestCase
{
    /**
     * Prepara el control y metadatos físicos autorizados del escenario.
     * @param array<string, mixed> $datos
     * @return void
     */
    protected function prepararControl(array $datos = []): void
    {
        EscenarioRepositorios::evidencia($datos);
        DB::table('fuentes_datos_expertas')->where('cod_fuente_dato_experta', 'SIM-FU')->update([
            'clave_adaptador' => 'COG_MEM_CONTROL', 'campo_personal' => 'cod_personal', 'campo_estado' => 'estado',
        ]);
    }

    /**
     * Configura una fuente operativa del fixture sin modificar su estructura.
     * @param string $tabla
     * @param string $pk
     * @param string $clave
     * @param string $registro
     * @param string $campo
     * @return void
     */
    protected function cambiarFuente(string $tabla, string $pk, string $clave, string $registro, string $campo): void
    {
        DB::table('fuentes_datos_expertas')->where('cod_fuente_dato_experta', 'SIM-FU')->update([
            'tabla_raiz' => $tabla, 'campo_pk_raiz' => $pk, 'clave_adaptador' => $clave,
        ]);
        DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'SIM-EVID')->update(['cod_registro_fuente' => $registro]);
        DB::table('mapeos_variables_fuente')->where('cod_mapeo_variable_fuente', 'SIM-MAP')->update([
            'tipo_extraccion' => 'CAMPO_DIRECTO', 'campo_valor' => $campo, 'clave_selector' => null,
        ]);
    }

    /**
     * Registra únicamente las implementaciones explícitas del escenario.
     * @param AdaptadorInstrumentos|null $instrumentos
     * @return ResolutorFuenteExperta
     */
    protected function resolver(?AdaptadorInstrumentos $instrumentos = null): ResolutorFuenteExperta
    {
        return new ResolutorFuenteExperta($this->conexionPruebas(), [
            'COG_MEM_CONTROL' => new AdaptadorControlesCognitivos,
            'SIM-PEDAGOGIA' => new AdaptadorSeguimientosPedagogicos,
            'SIM-BADL' => new AdaptadorValoracionesFuncionales,
            'SIM-INSTR' => $instrumentos ?? new AdaptadorInstrumentos,
            'SIM-IADL' => new AdaptadorIadlExperimental,
        ]);
    }

    /**
     * Conecta el extractor real mediante el contrato preparado en la etapa C.
     * @param AdaptadorInstrumentos|null $instrumentos
     * @return EvidenciaEloquentRepository
     */
    protected function repositorioAdaptado(?AdaptadorInstrumentos $instrumentos = null): EvidenciaEloquentRepository
    {
        $zona = new DateTimeZone('America/La_Paz');
        $extraer = new ExtractorVariableMapeada($this->resolver($instrumentos), $zona);

        return new EvidenciaEloquentRepository(new MapeadorEvidencias($zona), Closure::fromCallable($extraer), $zona, $this->conexionPruebas());
    }

    /** @return EvaluacionExperta */
    protected function evaluacion(): EvaluacionExperta
    {
        return EvaluacionExperta::on($this->conexionPruebas())->whereKey('SIM-EVAL')->firstOrFail();
    }

    /**
     * Prepara una aplicación y respuesta completamente artificiales.
     * @param array<string, mixed> $datos
     * @param string|null $version
     * @return void
     */
    protected function prepararInstrumento(array $datos = [], ?string $version = 'SIM-ED1'): void
    {
        $this->prepararControl();
        $this->cambiarFuente('aplicaciones_instrumento', 'cod_aplicacion', 'SIM-INSTR', 'SIM-APL', 'puntaje_total');
        DB::table('instrumentos')->insert(['cod_instrumento' => 'SIM-INST', 'codigo' => 'SIM-INST', 'nombre' => 'Instrumento ficticio',
            'tipo' => 'PRUEBA_TECNICA', 'version' => $version, 'estado' => 'ACTIVO']);
        DB::table('preguntas_instrumento')->insert(['cod_pregunta' => 'SIM-PREG', 'cod_instrumento' => 'SIM-INST',
            'codigo' => 'SIM-PREG', 'enunciado' => 'Reactivo artificial de prueba.', 'tipo_respuesta' => 'TEXTO',
            'orden' => 1, 'estado' => 'ACTIVO', 'dominio' => 'NO-USAR-COMO-MAPEO']);
        DB::table('opciones_pregunta')->insert(['cod_opcion' => 'SIM-OPC', 'cod_pregunta' => 'SIM-PREG',
            'nombre' => 'Opción artificial', 'valor' => null, 'puntaje' => null, 'orden' => 1, 'estado' => 'ACTIVO']);
        DB::table('aplicaciones_instrumento')->insert(['cod_aplicacion' => 'SIM-APL', 'cod_instrumento' => 'SIM-INST',
            'cod_residente' => 'SINTETICO-RES', 'cod_personal' => 'SIM-PER', 'cod_atencion' => 'SIM-AT',
            'fecha_hora' => '2026-10-17 12:00:00', 'puntaje_total' => '0.00', 'estado' => 'COMPLETA']);
        DB::table('respuestas_instrumento')->insert(array_replace(['cod_respuesta' => 'SIM-RESP', 'cod_aplicacion' => 'SIM-APL',
            'cod_pregunta' => 'SIM-PREG', 'cod_opcion' => null, 'valor_texto' => null, 'valor_numero' => null, 'valor_logico' => null], $datos));
    }

    /**
     * Vincula un selector artificial sin asignarle interpretación cognitiva.
     * @param string $campo
     * @return AdaptadorInstrumentos
     */
    protected function selector(string $campo = 'valor_texto'): AdaptadorInstrumentos
    {
        DB::table('mapeos_variables_fuente')->where('cod_mapeo_variable_fuente', 'SIM-MAP')->update([
            'tipo_extraccion' => 'SELECTOR_COMPONENTE', 'campo_valor' => null, 'clave_selector' => 'SIM-SELECTOR',
        ]);

        return new AdaptadorInstrumentos(['SIM-V1' => ['SIM-SELECTOR' => [
            'instrumento' => 'SIM-INST', 'version' => 'SIM-ED1', 'pregunta' => 'SIM-PREG', 'campo' => $campo,
        ]]]);
    }
}
