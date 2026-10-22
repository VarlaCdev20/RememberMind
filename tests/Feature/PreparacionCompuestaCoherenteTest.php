<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PrepararEvaluacionInstitucional;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;
use Tests\Support\EsquemaSinTransaccion;

/** Base exclusiva de pruebas; los cambios concurrentes se confirman en otro PDO real. */
final class PreparacionCompuestaCoherenteTest extends TestCase
{
    use EsquemaSinTransaccion;

    private User $actor;

    private Residente $residente;

    private string $versionPetersen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-19T12:00:00-04:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actor = User::factory()->create(['estado' => 'ACTIVO']);
        $this->actor->assignRole('PSICOLOGO/A');
        Personal::create(['cod_usuario' => $this->actor->getKey(), 'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintético', 'numero_documento' => 'SNAPSHOT-TEST', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        app(RegistrarConocimientoInstitucional::class)->ejecutar($this->actor);
        $this->versionPetersen = app(RegistrarConocimientoPetersen::class)->ejecutar($this->actor);
    }

    private function preparar(): object
    {
        return app(PrepararEvaluacionInstitucional::class)->preparar($this->actor, $this->residente, CarbonImmutable::now());
    }

    public function test_prepara_sin_escritura_y_libera_su_transaccion(): void
    {
        $dto = $this->preparar();
        self::assertFalse($dto->contenido['persistido']);
        self::assertFalse($dto->contenido['release_retenido']);
        self::assertSame(0, DB::transactionLevel());
        self::assertFalse(DB::getPdo()->inTransaction());
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
        if (DB::getDriverName() === 'pgsql') {
            self::assertSame('read committed', DB::selectOne('SHOW transaction_isolation')->transaction_isolation);
        }
    }

    public function test_rechaza_transaccion_externa_sin_confirmarla_ni_adquirir(): void
    {
        DB::beginTransaction();
        try {
            try {
                $this->preparar();
                self::fail('No debe elevar un savepoint ni consumir una transacción ajena.');
            } catch (DomainException $e) {
                self::assertStringContainsString('transacción propia', $e->getMessage());
            }
            self::assertSame(1, DB::transactionLevel());
            self::assertTrue(DB::getPdo()->inTransaction());
            $this->assertDatabaseCount('evaluaciones_expertas', 0);
        } finally {
            DB::rollBack();
        }
    }

    private function conexionConcurrente(): PDO
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('La carrera requiere dos conexiones PostgreSQL del runner desechable.');
        }
        $c = config('database.connections.pgsql');

        return new PDO("pgsql:host={$c['host']};port={$c['port']};dbname={$c['database']}", $c['username'], $c['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    /** Inyecta una escritura confirmada cuando la captura instrumental ya inició su lectura. */
    private function entreRamas(callable $mutacion): \Closure
    {
        $ocurrio = false;
        DB::listen(static function (QueryExecuted $evento) use (&$ocurrio, $mutacion): void {
            if (! $ocurrio && str_contains($evento->sql, 'from "aplicaciones_instrumento"')) {
                $ocurrio = true;
                $mutacion();
            }
        });

        return static function () use (&$ocurrio): bool {
            return $ocurrio;
        };
    }

    public function test_ambas_ramas_observan_el_mismo_corte_sql_aunque_ingresen_juicios_y_revisiones(): void
    {
        $pdo = $this->conexionConcurrente();
        $original = app(RegistrarValoracionPetersenOriginal::class)->ejecutar($this->actor, $this->residente,
            ['solicitud_uuid' => (string) Str::uuid(), 'elemento' => 'C04', 'estado_original' => 'N',
                'justificacion' => 'Caso sintético de adquisición concurrente.', 'fecha_hora' => '2026-10-18 10:00:00',
                'tipo_registro' => 'REGISTRO', 'soportes' => []]);
        $fila = (array) DB::table('valoraciones_petersen_originales')->where('cod_valoracion_petersen', $original->getKey())->sole();
        $fila['cod_valoracion_petersen'] = 'VPO-CARRERA';
        $fila['solicitud_uuid'] = (string) Str::uuid();
        $ocurrio = $this->entreRamas(function () use ($pdo, $fila): void {
            self::assertSame('repeatable read', DB::selectOne('SHOW transaction_isolation')->transaction_isolation);
            $s = $pdo->prepare('INSERT INTO valoraciones_petersen_originales ('.implode(',', array_keys($fila)).') VALUES ('.implode(',', array_fill(0, count($fila), '?')).')');
            foreach (array_values($fila) as $i => $valor) {
                $s->bindValue($i + 1, $valor, is_bool($valor) ? PDO::PARAM_BOOL : ($valor === null ? PDO::PARAM_NULL : PDO::PARAM_STR));
            }
            $s->execute();
            $s = $pdo->prepare("UPDATE reglas_expertas SET estado_revision='RECHAZADA', cod_usuario_revision=?, fecha_hora_revision=?, justificacion_revision=? WHERE cod_version_modelo=?");
            $s->execute([$this->actor->getKey(), '2026-10-19 12:00:00', 'Decisión sintética concurrente.', $this->versionPetersen]);
            self::assertSame(4, $s->rowCount());
        });
        $antes = $this->preparar()->contenido['petersen']['contenido'];
        self::assertTrue($ocurrio());
        self::assertSame(1, $antes['adquisicion']['valoraciones_al_corte']);
        self::assertSame(['PENDIENTE'], array_values(array_unique(array_column($antes['gobierno']['conocimiento']['tablas']['reglas_expertas'], 'estado_revision'))));
        $despues = $this->preparar()->contenido['petersen']['contenido'];
        self::assertSame(2, $despues['adquisicion']['valoraciones_al_corte']);
        self::assertSame(['RECHAZADA'], array_values(array_unique(array_column($despues['gobierno']['conocimiento']['tablas']['reglas_expertas'], 'estado_revision'))));
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public static function revocaciones(): array
    {
        return [['CUENTA'], ['PERMISO_DOCUMENTAL']];
    }

    #[DataProvider('revocaciones')]
    public function test_revocacion_confirmada_durante_snapshot_impide_entregar_el_dto(string $tipo): void
    {
        $pdo = $this->conexionConcurrente();
        $this->actor->givePermissionTo('documentos_clinicos.ver');
        $ocurrio = $this->entreRamas(function () use ($pdo, $tipo): void {
            $s = $pdo->prepare($tipo === 'CUENTA'
                ? "UPDATE usuarios SET estado='INACTIVO' WHERE cod_usuario=?"
                : "DELETE FROM model_has_permissions WHERE model_id=? AND permission_id IN (SELECT id FROM permissions WHERE name='documentos_clinicos.ver')");
            $s->execute([$this->actor->getKey()]);
            self::assertSame(1, $s->rowCount());
        });
        try {
            $this->preparar();
            self::fail('La vista RR no debe ocultar la revocación al entregar el DTO.');
        } catch (AuthorizationException|HttpException $e) {
            self::assertTrue($ocurrio());
            self::assertTrue($e instanceof AuthorizationException || $e->getStatusCode() === 403);
            self::assertSame(0, DB::transactionLevel());
            $this->assertDatabaseCount('evaluaciones_expertas', 0);
        }
    }
}
