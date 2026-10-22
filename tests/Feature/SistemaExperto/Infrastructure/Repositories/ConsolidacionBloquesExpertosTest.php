<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Repositories;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\AlmacenInstantaneas;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos as B;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\CatalogoConservacionExperta;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\TransicionBloquesExpertos as T;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MotorExperto\dominio\orion\Services\Dcl\ClasificadorPetersenService;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use RuntimeException;
use Tests\Support\InformeCognitivoFixture;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;

/** Transición de producción probada exclusivamente con datos sintéticos y bases desechables. */
final class ConsolidacionBloquesExpertosTest extends RepositoriosTestCase
{
    private function poblar(): object
    {
        EscenarioRepositorios::evidencia();
        CatalogoConservacionExperta::preparar($this->conexionPruebas(), 'SIM-V1', 'SIM-USU');
        $perfil = E::perfil();
        $resultado = (new ClasificadorPetersenService)->clasificar($perfil);
        $almacen = new AlmacenInstantaneas($this->conexionPruebas());
        $almacen->guardar($perfil->cognicion);
        $almacen->guardarClasificacion($resultado);
        B::insertar('trazas_inferencia', ['cod_traza_inferencia' => 'TR-B4',
            'cod_evaluacion_criterio' => DB::table('evaluacion_criterios')->value('cod_evaluacion_criterio'),
            'estado_inferencia' => 'FINALIZADA', 'fecha_hora_inicio' => '2026-10-17 12:00:00', 'fecha_hora_fin' => null]);

        return $resultado;
    }

    private function bloques(bool $originales = false): array
    {
        $r = [];
        foreach (B::DEFINICIONES as $entidad => $d) {
            $q = $originales ? DB::table($entidad) : B::consulta($entidad);
            $r[$entidad] = $q->orderBy($d['id'])->get()->map(static fn ($f) => (array) $f)->all();
        }

        return $r;
    }

    public function test_transicion_poblada_reversible_conserva_campos_identidades_y_clasificacion(): void
    {
        $resultado = $this->poblar();
        $antes = $this->bloques();
        foreach ($antes as $filas) {
            self::assertNotEmpty($filas);
        }
        self::assertCount(116, Schema::getTables(DB::getDriverName() === 'sqlite' ? 'main' : null));
        $triggers = DB::getDriverName() === 'sqlite'
            ? DB::table('sqlite_master')->where('type', 'trigger')->where('name', 'not like', 'b4_%')->pluck('sql', 'name')->all() : [];
        (new T)->bajar();
        self::assertCount(120, Schema::getTables(DB::getDriverName() === 'sqlite' ? 'main' : null));
        self::assertEquals($antes, $this->bloques(true));
        (new T)->subir();
        self::assertSame($antes, $this->bloques());
        self::assertCount(116, Schema::getTables(DB::getDriverName() === 'sqlite' ? 'main' : null));
        foreach (B::DEFINICIONES as $entidad => $d) {
            self::assertFalse(Schema::hasTable($entidad));
        }
        foreach (T::REFERENCIAS as [$tabla, $columnas, $entidad, $destinos]) {
            $fk = array_values(array_filter(Schema::getForeignKeys($tabla), static fn ($f) => $f['columns'] === $columnas));
            self::assertCount(1, $fk);
            self::assertSame(B::DEFINICIONES[$entidad]['tabla'], $fk[0]['foreign_table']);
            self::assertSame($destinos, $fk[0]['foreign_columns']);
        }
        $recuperado = (new AlmacenInstantaneas($this->conexionPruebas()))->obtenerClasificacion(
            'SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha(), $resultado->perfil->versionContrato);
        self::assertSame(CodecInstantanea::huella($resultado), CodecInstantanea::huella($recuperado));
        if (DB::getDriverName() === 'sqlite') {
            foreach ($triggers as $nombre => $sql) {
                self::assertSame($sql, DB::table('sqlite_master')->where('name', $nombre)->value('sql'));
            }
            self::assertSame([], DB::select('PRAGMA foreign_key_check'));
        }
    }

    public function test_fallo_intermedio_revierte_ddl_y_copia_sin_dejar_tablas_parciales(): void
    {
        $this->poblar();
        (new T)->bajar();
        $antes = $this->bloques(true);
        try {
            (new T)->subir(static fn () => throw new RuntimeException('Fallo sintético B4'));
            self::fail('La transición debe fallar y revertirse.');
        } catch (RuntimeException $e) {
            self::assertSame('Fallo sintético B4', $e->getMessage());
        }
        self::assertSame($antes, $this->bloques(true));
        self::assertFalse(Schema::hasColumn('nodos_semanticos', 'cod_variable_experta'));
        (new T)->subir();
    }

    public function test_resultado_y_traza_conservan_su_ausencia_independientemente(): void
    {
        CatalogoConservacionExperta::preparar($this->conexionPruebas(), 'SIM-V1', 'SIM-USU');
        $memoria = CatalogoConservacionExperta::leer($this->conexionPruebas(), 'SIM-V1')['SNAP:DOMINIO:COG-MEM'];
        foreach (range(0, 3) as $mascara) {
            $evaluacion = 'B4-E-'.$mascara;
            $criterio = 'B4-C-'.$mascara;
            InformeCognitivoFixture::insertar('evaluaciones_expertas', ['cod_evaluacion_experta' => $evaluacion,
                'cod_residente' => 'SINTETICO-RES', 'cod_version_modelo' => 'SIM-V1', 'estado_ejecucion' => 'PRUEBA_FINALIZADA']);
            InformeCognitivoFixture::insertar('evaluacion_criterios', ['cod_evaluacion_criterio' => $criterio,
                'cod_evaluacion_experta' => $evaluacion, 'cod_nodo_criterio' => $memoria['nodo'], 'tipo_registro' => 'D137', 'estado_evaluabilidad' => 'EV-CM-0']);
            if ($mascara & 1) {
                B::insertar('resultados_criterio', ['cod_resultado_criterio' => 'B4-R-'.$mascara, 'cod_evaluacion_criterio' => $criterio,
                    'cod_valor_semantico' => $memoria['valores']['U'], 'fecha_hora_determinacion' => '2026-10-22 10:00:00', 'justificacion_original' => null]);
            }
            if ($mascara & 2) {
                B::insertar('trazas_inferencia', ['cod_traza_inferencia' => 'B4-T-'.$mascara, 'cod_evaluacion_criterio' => $criterio,
                    'estado_inferencia' => 'FINALIZADA', 'fecha_hora_inicio' => '2026-10-22 10:00:00', 'fecha_hora_fin' => null]);
            }
        }
        $antes = $this->bloques();
        self::assertCount(2, $antes['resultados_criterio']);
        self::assertCount(2, $antes['trazas_inferencia']);
        (new T)->bajar();
        self::assertSame($antes, $this->bloques(true));
        (new T)->subir();
        self::assertSame($antes, $this->bloques());
        foreach (range(0, 3) as $mascara) {
            $fila = DB::table('evaluacion_criterios')->where('cod_evaluacion_criterio', 'B4-C-'.$mascara)->sole();
            self::assertSame((bool) ($mascara & 1), $fila->cod_resultado_criterio !== null);
            self::assertSame((bool) ($mascara & 2), $fila->cod_traza_inferencia !== null);
        }
    }

    public function test_fk_entrante_no_prevista_impide_retiro_sin_perder_datos(): void
    {
        $this->poblar();
        (new T)->bajar();
        $antes = $this->bloques(true);
        Schema::create('b4_dependencia_prueba', function (Blueprint $t): void {
            $t->string('cod_variable_experta', 20)->nullable();
            $t->foreign('cod_variable_experta')->references('cod_variable_experta')->on('variables_expertas');
        });
        try {
            (new T)->subir();
            self::fail('Una FK no contemplada debe bloquear la transición.');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('referencia externa no prevista', $e->getMessage());
            self::assertSame($antes, $this->bloques(true));
        } finally {
            Schema::drop('b4_dependencia_prueba');
        }
        (new T)->subir();
    }

    public function test_variable_rechaza_dominio_de_otra_version_aunque_la_identidad_exista(): void
    {
        $this->poblar();
        EscenarioRepositorios::version('SIM-V2');
        $nodo = (array) DB::table('nodos_semanticos')->whereNull('cod_variable_experta')->first();
        $nodo = B::nodoOriginal($nodo);
        $nodo['cod_nodo_semantico'] = 'N-B4-V2';
        $nodo['cod_version_modelo'] = 'SIM-V2';
        DB::table('nodos_semanticos')->insert($nodo);
        $this->expectException(QueryException::class);
        DB::transaction(fn () => B::insertar('variables_expertas', ['cod_variable_experta' => 'V-B4-V2',
            'cod_nodo_semantico' => 'N-B4-V2', 'cod_version_modelo' => 'SIM-V2',
            'cod_dominio_valores' => DB::table('dominios_valores_expertos')->where('cod_version_modelo', 'SIM-V1')->value('cod_dominio_valores'),
            'tipo_semantico' => 'OBSERVACION', 'papel_inferencial' => 'CONTEXTUAL', 'estado' => 'ACTIVO',
            'fecha_hora_creacion' => '2026-10-17 12:00:00', 'cod_usuario_creacion' => 'SIM-USU']));
    }

    public function test_columna_no_prevista_impide_retirar_incluso_una_tabla_vacia(): void
    {
        (new T)->bajar();
        Schema::table('resultados_criterio', fn (Blueprint $t) => $t->string('dato_no_contratado')->nullable());
        try {
            (new T)->subir();
            self::fail('La estructura desconocida debe conservarse para una transición explícita.');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('estructura de origen distinta', $e->getMessage());
            self::assertTrue(Schema::hasColumn('resultados_criterio', 'dato_no_contratado'));
            self::assertFalse(Schema::hasColumn('evaluacion_criterios', 'cod_resultado_criterio'));
        }
        Schema::table('resultados_criterio', fn (Blueprint $t) => $t->dropColumn('dato_no_contratado'));
        (new T)->subir();
    }

    public function test_sql_rechaza_bloque_parcial_y_sobrescritura_de_resultado(): void
    {
        $this->poblar();
        foreach ([['resultado_cod_valor_semantico' => null], ['resultado_justificacion_original' => 'Reescritura indebida']] as $cambio) {
            try {
                DB::transaction(fn () => DB::table('evaluacion_criterios')->whereNotNull('cod_resultado_criterio')->update($cambio));
                self::fail('El bloque histórico debe permanecer completo e inmutable.');
            } catch (QueryException $e) {
                self::assertStringContainsString('bloque_experto', $e->getMessage());
            }
        }
    }
}
