<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PDO;
use Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios;
use Tests\Support\EsquemaSinTransaccion;
use Tests\Support\InformeCognitivoFixture;
use Tests\TestCase;

/** Dos conexiones independientes sobre los bloques reales migrados, sin esquema simulado. */
final class ConsolidacionBloquesConcurrenciaPostgresTest extends TestCase
{
    use EsquemaSinTransaccion;

    public function test_dos_escritores_no_completan_dos_veces_el_mismo_bloque(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            self::markTestSkipped('Requiere dos conexiones PostgreSQL desechables.');
        }
        EscenarioRepositorios::preparar();
        InformeCognitivoFixture::insertar('nodos_semanticos', ['cod_nodo_semantico' => 'B4-NODO', 'cod_version_modelo' => 'SIM-V1',
            'codigo_semantico' => 'B4-SINTETICO', 'tipo_nodo' => 'VARIABLE', 'estado' => 'ACTIVO', 'cod_usuario_creacion' => 'SIM-USU']);
        $cfg = DB::connection()->getConfig();
        $primera = new PDO('pgsql:host='.$cfg['host'].';port='.$cfg['port'].';dbname='.$cfg['database'], $cfg['username'], $cfg['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $segunda = ['cod_variable_experta' => 'B4-SEGUNDA', 'cod_nodo_semantico' => 'B4-NODO', 'cod_version_modelo' => 'SIM-V1',
            'tipo_semantico' => 'OBSERVACION', 'papel_inferencial' => 'CONTEXTUAL', 'estado' => 'ACTIVO',
            'fecha_hora_creacion' => '2026-10-22 10:00:00', 'cod_usuario_creacion' => 'SIM-USU'];
        DB::statement("SET lock_timeout='250ms'");
        try {
            $primera->beginTransaction();
            $primera->exec("UPDATE nodos_semanticos SET cod_variable_experta='B4-PRIMERA',tipo_semantico='OBSERVACION',papel_inferencial='CONTEXTUAL',estado_variable='ACTIVO',fecha_hora_creacion_variable='2026-10-22 10:00:00',cod_usuario_creacion_variable='SIM-USU' WHERE cod_nodo_semantico='B4-NODO' AND cod_variable_experta IS NULL");
            try {
                BloquesExpertos::insertar('variables_expertas', $segunda);
                self::fail('El segundo escritor debe esperar al primero.');
            } catch (QueryException $e) {
                self::assertSame('55P03', $e->errorInfo[0]);
            }
            $primera->commit();
            try {
                BloquesExpertos::insertar('variables_expertas', $segunda);
                self::fail('El reintento no puede sustituir el bloque confirmado.');
            } catch (DomainException $e) {
                self::assertStringContainsString('ya existe', $e->getMessage());
            }
            self::assertSame('B4-PRIMERA', DB::table('nodos_semanticos')->where('cod_nodo_semantico', 'B4-NODO')->value('cod_variable_experta'));
            self::assertSame(1, BloquesExpertos::consulta('variables_expertas')->count());
        } finally {
            if ($primera->inTransaction()) {
                $primera->rollBack();
            }
            DB::statement("SET lock_timeout='0'");
        }
    }
}
