<?php

namespace Tests\Feature;

use App\Models\DestinatarioSolicitudDocumental;
use App\Models\Personal;
use App\Models\SolicitudDocumentalPersonal;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GerenciaDocumentacionEstructuraTest extends TestCase
{
    use RefreshDatabase;

    private $transicion;

    private User $anterior;

    protected function setUp(): void
    {
        parent::setUp();
        $this->anterior = User::factory()->create();
        $this->transicion = require base_path('database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php');
        $this->transicion->up();
    }

    public function test_transicion_poblada_no_inventa_alta_ni_cambio_de_password_y_reversion_vacia_conserva_datos(): void
    {
        $this->assertNull($this->anterior->fresh()->fecha_hora_alta);
        $this->assertNull($this->anterior->fresh()->cambio_password_pendiente);
        foreach (['solicitudes_documentales_personal', 'destinatarios_solicitud_documental', 'entregas_solicitud_documental', 'movimientos_laborales_personal'] as $tabla) {
            $this->assertTrue(Schema::hasTable($tabla));
        }
        $this->transicion->down();
        $this->assertFalse(Schema::hasColumn('usuarios', 'fecha_hora_alta'));
        $this->assertFalse(Schema::hasColumn('documentos', 'cod_usuario_carga'));
        $this->assertDatabaseHas('usuarios', ['cod_usuario' => $this->anterior->getKey()]);
    }

    public function test_extension_agrega_solo_cuatro_tablas_y_conserva_las_dieciséis_tecnicas(): void
    {
        $tecnicas = ['migrations', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches',
            'failed_jobs', 'personal_access_tokens', 'activity_log', 'permissions', 'roles', 'model_has_permissions',
            'model_has_roles', 'role_has_permissions', 'secuencias_codigos'];
        $tablas = array_map(fn (string $tabla) => (string) str($tabla)->afterLast('.'), Schema::getTableListing());
        $this->assertCount(16, array_intersect($tablas, $tecnicas));
        $this->assertCount(104, array_diff($tablas, $tecnicas));
        $this->transicion->down();
        $anteriores = array_map(fn (string $tabla) => (string) str($tabla)->afterLast('.'), Schema::getTableListing());
        $this->assertCount(100, array_diff($anteriores, $tecnicas));
        $this->assertCount(16, array_intersect($anteriores, $tecnicas));
    }

    public function test_pk_fk_unicidad_y_prorroga_de_siete_dias_se_exigen_en_sql(): void
    {
        $rol = Role::findOrCreate('ADMINISTRADOR', 'web');
        $solicitud = SolicitudDocumentalPersonal::create(['rol_id' => $rol->id, 'tipo_documento' => 'OTROS', 'nombre_documento' => 'Respaldo solicitado',
            'motivo' => 'Verificación laboral', 'cod_usuario_solicitante' => $this->anterior->getKey(), 'fecha_hora_emision' => now(), 'clave_solicitud' => (string) Str::uuid()]);
        $persona = Personal::create(['cod_usuario' => $this->anterior->getKey(), 'nombres' => 'Persona', 'apellido_paterno' => 'Sintética', 'numero_documento' => '98761234', 'profesion' => 'ADMINISTRADOR', 'estado' => 'ACTIVO']);
        $destino = DestinatarioSolicitudDocumental::create(['cod_solicitud_documental' => $solicitud->getKey(), 'cod_personal' => $persona->getKey(), 'origen_plazo' => 'ALTA', 'fecha_limite_original' => '2026-11-02']);
        $this->rechazar(fn () => DB::table('destinatarios_solicitud_documental')->insert(['cod_destinatario_documental' => 'QA_DUPLICADO', 'cod_solicitud_documental' => $solicitud->getKey(), 'cod_personal' => $persona->getKey(), 'origen_plazo' => 'ALTA', 'fecha_limite_original' => '2026-11-02']));
        $this->rechazar(fn () => DB::table('destinatarios_solicitud_documental')->where('cod_destinatario_documental', $destino->getKey())->update(['fecha_limite_prorrogada' => '2026-11-09']));
        $prorroga = ['cod_usuario_prorroga' => $this->anterior->getKey(), 'fecha_hora_prorroga' => now(), 'motivo_prorroga' => 'Entrega complementaria'];
        $this->rechazar(fn () => DB::table('destinatarios_solicitud_documental')->where('cod_destinatario_documental', $destino->getKey())->update($prorroga + ['fecha_limite_prorrogada' => '2026-11-10']));
        $destino->update($prorroga + ['fecha_limite_prorrogada' => '2026-11-09']);
        $this->assertSame('2026-11-09', $destino->fresh()->fecha_limite_prorrogada->toDateString());
        $this->rechazar(fn () => DB::table('usuarios')->where('cod_usuario', $this->anterior->getKey())->delete());
        $this->assertSame($persona->getKey(), $solicitud->fresh()->destinatarios->sole()->personal->getKey());
        $this->assertDatabaseCount('destinatarios_solicitud_documental', 1);
    }

    public function test_reversion_poblada_se_rechaza_y_conserva_todo(): void
    {
        $this->anterior->forceFill(['fecha_hora_alta' => now(), 'cambio_password_pendiente' => true])->save();
        try {
            $this->transicion->down();
            $this->fail('No debe eliminar datos nuevos.');
        } catch (\LogicException $error) {
            $this->assertStringContainsString('conservan datos', $error->getMessage());
        }
        $this->assertTrue(Schema::hasTable('entregas_solicitud_documental'));
        $this->assertTrue($this->anterior->fresh()->cambio_password_pendiente);
    }

    private function rechazar(callable $operacion): void
    {
        try {
            DB::transaction($operacion);
            $this->fail('SQL debe rechazar la violación de integridad.');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }
    }
}
