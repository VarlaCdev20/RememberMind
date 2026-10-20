<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Repositories;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\AlmacenInstantaneas;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\CatalogoConservacionExperta;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\ProyeccionReduccionExperta as P;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\TransicionReduccionConservacion as T;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MotorExperto\dominio\orion\Services\Dcl\ClasificadorPetersenService;
use MotorExperto\soporte\conservacion\CodecInstantanea as Codec;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CorromperConservacionParaPrueba;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;

/** Migración poblada y reversión reales, dentro de las bases sintéticas del runner. */
final class ReduccionFisicaConservacionTest extends RepositoriosTestCase
{
    use CorromperConservacionParaPrueba;

    private function almacen(): AlmacenInstantaneas
    {
        return new AlmacenInstantaneas($this->conexionPruebas());
    }

    private function guardar(): object
    {
        CatalogoConservacionExperta::preparar($this->conexionPruebas(), 'SIM-V1', 'SIM-USU');
        $perfil = E::perfil();
        $r = (new ClasificadorPetersenService)->clasificar($perfil);
        $this->almacen()->guardar($perfil->cognicion);
        $this->almacen()->guardarClasificacion($r);

        return $r;
    }

    private function filas(array $tablas): array
    {
        $r = [];
        foreach ($tablas as $t) {
            $r[$t] = DB::table($t)->get()->map(fn ($f) => (array) $f)->all();
        }

        return $r;
    }

    public function test_transicion_poblada_y_reversion_conservan_aliases_dtos_y_tipos(): void
    {
        $r = $this->guardar();
        $p = new P;
        $antes = $p->restaurar($this->filas([P::HECHOS, P::CLASIFICACIONES, P::VINCULOS]));
        $triggers = DB::getDriverName() === 'sqlite' ? DB::table('sqlite_master')->where('type', 'trigger')->where('tbl_name', P::HECHOS)->where('name', 'not like', 'red_%')->pluck('sql', 'name')->all() : [];
        (new T)->bajar();
        foreach (T::RETIRADAS as $tabla) {
            self::assertTrue(Schema::hasTable($tabla));
        }
        self::assertSame($p->canonizar($antes), $p->canonizar($this->filas(P::TABLAS)));
        self::assertFalse(Schema::hasColumn(P::HECHOS, 'cognitiva_dominio'));
        (new T)->subir();
        foreach (T::RETIRADAS as $tabla) {
            self::assertFalse(Schema::hasTable($tabla));
        }
        self::assertSame($p->canonizar($antes), $p->canonizar($p->restaurar($this->filas([P::HECHOS, P::CLASIFICACIONES, P::VINCULOS]))));
        $consultas = [];
        DB::listen(static function ($q) use (&$consultas) {
            $consultas[] = $q->sql;
        });
        $recuperada = $this->almacen()->obtenerClasificacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha(), $r->perfil->versionContrato);
        self::assertSame(Codec::huella($r), Codec::huella($recuperada));
        foreach ($consultas as $sql) {
            foreach (T::RETIRADAS as $tabla) {
                self::assertStringNotContainsString($tabla, $sql);
            }
        }
        if (DB::getDriverName() === 'sqlite') {
            foreach ($triggers as $nombre => $sql) {
                self::assertSame($sql, DB::table('sqlite_master')->where('name', $nombre)->value('sql'));
            }
            self::assertSame([], DB::select('PRAGMA foreign_key_check'));
        }
    }

    public function test_cognitiva_e_iadl_coexisten_y_se_reconstruyen_sin_traducir_vocabulario(): void
    {
        $this->guardar();
        (new T)->bajar();
        $iad = DB::table(P::IADL)->first();
        $cog = (array) DB::table(P::COGNITIVAS)->orderByDesc('orden')->first();
        $cog['cod_evidencia_cognitiva_instantanea'] = 'COG-DUAL-TEST';
        $cog['cod_hecho_instantanea'] = $iad->cod_hecho_instantanea;
        $cog['orden']++;
        DB::table(P::COGNITIVAS)->insert($cog);
        $antes = (new P)->canonizar($this->filas(P::TABLAS));
        (new T)->subir();
        $h = DB::table(P::HECHOS)->where('cod_hecho_instantanea', $iad->cod_hecho_instantanea)->first();
        self::assertSame('COG-DUAL-TEST', $h->cod_evidencia_cognitiva_instantanea);
        self::assertSame($iad->cod_evidencia_iadl_instantanea, $h->cod_evidencia_iadl_instantanea);
        self::assertSame($iad->estado, $h->iadl_estado);
        self::assertSame($antes, (new P)->canonizar((new P)->restaurar($this->filas([P::HECHOS, P::CLASIFICACIONES, P::VINCULOS]))));
    }

    public function test_fk_entrante_no_prevista_bloquea_forward_sin_mutar(): void
    {
        $this->guardar();
        (new T)->bajar();
        Schema::create('prueba_dependencia_reduccion', function (Blueprint $t): void {
            $t->string('cod_cog', 20)->nullable();
            $t->foreign('cod_cog')->references('cod_evidencia_cognitiva_instantanea')->on(P::COGNITIVAS);
        });
        try {
            (new T)->subir();
            self::fail('La dependencia no prevista debe bloquear la transición.');
        } catch (\DomainException $e) {
            self::assertStringContainsString('FK entrante', $e->getMessage());
            self::assertTrue(Schema::hasTable(P::COGNITIVAS));
            self::assertFalse(Schema::hasColumn(P::HECHOS, 'cognitiva_dominio'));
        } finally {
            Schema::drop('prueba_dependencia_reduccion');
        }
    }

    public function test_fallo_antes_de_retirar_la_primera_tabla_revierte_copia_columnas_y_secuencias(): void
    {
        $this->guardar();
        (new T)->bajar();
        $p = new P;
        $antes = $p->canonizar($this->filas(P::TABLAS));
        $secuencias = DB::table('secuencias_codigos')->orderBy('entidad')->get()->all();
        $disparado = false;
        DB::listen(static function ($q) use (&$disparado): void {
            if (! $disparado && preg_match('/^drop table ["`]?evidencias_iadl_instantanea/i', $q->sql)) {
                $disparado = true;
                throw new \RuntimeException('Fallo de transición sintético.');
            }
        });
        try {
            (new T)->subir();
            self::fail('Debe fallar la transición.');
        } catch (\RuntimeException $e) {
            self::assertSame('Fallo de transición sintético.', $e->getMessage());
        }
        self::assertTrue($disparado);
        self::assertSame($antes, $p->canonizar($this->filas(P::TABLAS)));
        self::assertEquals($secuencias, DB::table('secuencias_codigos')->orderBy('entidad')->get()->all());
        self::assertFalse(Schema::hasColumn(P::HECHOS, 'cognitiva_dominio'));
    }

    public static function mutacionesInvalidas(): array
    {
        return [['valor'], ['cognitiva'], ['iadl'], ['delete'], ['rama'], ['orden'], ['mismo_hecho']];
    }

    public function test_reversion_rechaza_dependencia_nueva_sin_eliminar_datos(): void
    {
        $this->guardar();
        Schema::create('prueba_alias_reduccion', function (Blueprint $t): void {
            $t->string('alias', 20)->nullable();
            $t->foreign('alias')->references('cod_funcionalidad_instantanea')->on(P::CLASIFICACIONES);
        });
        try {
            (new T)->bajar();
            self::fail('La dependencia del alias debe impedir la reversión.');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('FK entrante', $e->getMessage());
            self::assertTrue(Schema::hasColumn(P::HECHOS, 'cognitiva_dominio'));
            self::assertSame(1, DB::table(P::CLASIFICACIONES)->count());
        } finally {
            Schema::drop('prueba_alias_reduccion');
        }
    }

    public static function bloquesInvalidos(): array
    {
        return [['cognitiva_sin_alias'], ['cognitiva_sin_orden'], ['iadl_sin_alias'], ['iadl_sin_actividad']];
    }

    public static function cambiosContexto(): array
    {
        return [['criterio_evaluacion'], ['criterio_version'], ['criterio_tipo'], ['evidencia_evaluacion']];
    }

    #[DataProvider('cambiosContexto')]
    public function test_sql_impide_romper_el_contexto_desde_un_padre_referenciado(string $caso): void
    {
        EscenarioRepositorios::evidencia();
        $this->guardar();
        $cab = (array) DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', 'SIM-EVAL')->first();
        $cab['cod_evaluacion_experta'] = 'SIM-EVAL-OTRA';
        foreach (['id_evaluacion_dominio', 'version_conservacion', 'huella_memoria_cognicion', 'fecha_corte_utc', 'fecha_corte_zona', 'fecha_corte_offset'] as $c) {
            $cab[$c] = null;
        }
        DB::table('evaluaciones_expertas')->insert($cab);
        $criterio = DB::table(P::VINCULOS)->value('cod_evaluacion_criterio');
        if ($caso === 'evidencia_evaluacion') {
            $h = DB::table(P::HECHOS)->where('id_hecho_dominio', 'SIM-IADL')->first();
            $this->corromperConservacion(fn () => DB::table(P::HECHOS)->where('cod_hecho_instantanea', $h->cod_hecho_instantanea)
                ->update(['cod_evidencia_evaluacion' => 'SIM-EVID', 'tipo_origen' => 'D137', 'cod_atencion' => 'SIM-AT']));
            DB::table(P::VINCULOS)->insert(['cod_evidencia_criterio' => 'V-CONTEXTO', 'cod_evaluacion_criterio' => $criterio,
                'cod_evidencia_evaluacion' => 'SIM-EVID', 'rol_en_criterio' => 'SOPORTE_EXPERIMENTAL', 'estado_participacion' => 'PRUEBA_TECNICA',
                'fecha_hora_vinculacion' => '2026-10-20 10:00:00', 'orden_conservacion' => 999]);
        }
        try {
            DB::transaction(function () use ($caso, $criterio): void {
                if ($caso === 'evidencia_evaluacion') {
                    DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'SIM-EVID')->update(['cod_evaluacion_experta' => 'SIM-EVAL-OTRA']);
                } else {
                    $cambios = match ($caso) {
                        'criterio_evaluacion' => ['cod_evaluacion_experta' => 'SIM-EVAL-OTRA'],
                        'criterio_version' => ['version_conservacion' => 'OBS-SNAP-1'],
                        default => ['tipo_registro' => 'D137'],
                    };
                    DB::table('evaluacion_criterios')->where('cod_evaluacion_criterio', $criterio)->update($cambios);
                }
            });
            self::fail('El padre referenciado no puede cambiar su contexto.');
        } catch (QueryException $e) {
            self::assertStringContainsString('red_contexto_padre_inmutable', $e->getMessage());
        }
        self::assertSame('SIM-EVAL', DB::table('evaluacion_criterios')->where('cod_evaluacion_criterio', $criterio)->value('cod_evaluacion_experta'));
        self::assertSame('SIM-EVAL', DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'SIM-EVID')->value('cod_evaluacion_experta'));
    }

    #[DataProvider('bloquesInvalidos')]
    public function test_sql_rechaza_bloques_incompletos_al_insertar(string $caso): void
    {
        $this->guardar();
        $cognitiva = str_starts_with($caso, 'cognitiva');
        $h = (array) DB::table(P::HECHOS)->whereNotNull($cognitiva ? 'cod_evidencia_cognitiva_instantanea' : 'cod_evidencia_iadl_instantanea')->first();
        $h['cod_hecho_instantanea'] = 'HECHO-INCOMPLETO';
        $h['id_hecho_dominio'] = 'SIM-INCOMPLETO';
        $h['orden'] = 999;
        if ($cognitiva) {
            $h['cod_evidencia_cognitiva_instantanea'] = $caso === 'cognitiva_sin_alias' ? null : 'COG-INCOMPLETA';
            $h['cognitiva_orden'] = $caso === 'cognitiva_sin_orden' ? null : 999;
        } else {
            $h['cod_evidencia_iadl_instantanea'] = $caso === 'iadl_sin_alias' ? null : 'IADL-INCOMPLETA';
            $h['iadl_orden'] = 999;
            if ($caso === 'iadl_sin_actividad') {
                $h['iadl_actividad_id_dominio'] = null;
            }
        }
        $this->expectException(QueryException::class);
        DB::transaction(fn () => DB::table(P::HECHOS)->insert($h));
    }

    public function test_postgres_rechaza_snapshot_de_escritura_no_serializable_por_la_cabecera(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('El aislamiento de la transacción se verifica en PostgreSQL.');
        }
        // RefreshDatabase abrió una transacción; un SAVEPOINT no puede cambiar su aislamiento.
        // Se cambia antes del primer acceso a datos del caso mediante el parámetro de sesión
        // en un segundo PDO que no participa en esa transacción.
        $cfg = DB::connection()->getConfig();
        $pdo = new \PDO('pgsql:host='.$cfg['host'].';port='.$cfg['port'].';dbname='.$cfg['database'], $cfg['username'], $cfg['password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        $pdo->beginTransaction();
        try {
            $pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            try {
                $pdo->exec("INSERT INTO hechos_instantanea_experta(cod_hecho_instantanea) VALUES ('RR-RECHAZADO')");
                self::fail('Las guardas deben rechazar el snapshot de escritura.');
            } catch (\PDOException $e) {
                self::assertStringContainsString('red_hecho_requiere_read_committed', $e->getMessage());
            }
        } finally {
            $pdo->rollBack();
        }
    }

    #[DataProvider('mutacionesInvalidas')]
    public function test_sql_rechaza_sustitucion_y_soportes_ambiguos(string $caso): void
    {
        $this->guardar();
        $h = DB::table(P::HECHOS)->whereNotNull('cod_evidencia_iadl_instantanea')->first();
        $v = (array) DB::table(P::VINCULOS)->whereNotNull('cod_hecho_instantanea')->first();
        $this->expectException(QueryException::class);
        DB::transaction(function () use ($caso, $h, $v): void {
            if ($caso === 'valor') {
                DB::table(P::HECHOS)->where('cod_hecho_instantanea', $h->cod_hecho_instantanea)->update(['valor_texto' => 'Alteración']);
            } elseif ($caso === 'cognitiva') {
                DB::table(P::HECHOS)->where('cod_hecho_instantanea', $h->cod_hecho_instantanea)->update(['cognitiva_hallazgo' => 'OTRO']);
            } elseif ($caso === 'iadl') {
                DB::table(P::HECHOS)->where('cod_hecho_instantanea', $h->cod_hecho_instantanea)->update(['iadl_estado' => 'OTRO']);
            } elseif ($caso === 'delete') {
                DB::table(P::HECHOS)->where('cod_hecho_instantanea', $h->cod_hecho_instantanea)->delete();
            } else {
                $v['cod_evidencia_criterio'] = 'VINCULO-INVALIDO';
                $v['cod_soporte_sintetico'] = 'SOPORTE-INVALIDO';
                if ($caso === 'rama') {
                    $v['fecha_hora_vinculacion'] = '2026-10-20 10:00:00';
                } elseif ($caso === 'orden') {
                    $v['cod_hecho_instantanea'] = DB::table(P::HECHOS)->where('cod_hecho_instantanea', '<>', $v['cod_hecho_instantanea'])->value('cod_hecho_instantanea');
                } else {
                    $v['orden_conservacion'] = 999;
                }
                DB::table(P::VINCULOS)->insert($v);
            }
        });
    }
}
