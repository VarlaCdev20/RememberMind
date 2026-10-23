<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use DomainException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;
use MotorExperto\soporte\conservacion\ReleaseMotorInstrumental;
use OrionFase02\Artefactos;
use OrionFase02\VariantesCanonicas;
use OrionNominal\CasosCompletos;
use OrionNominal\EsquemaNominal;
use OrionNominal\RepositorioNominal;
use PDO;
use PDOException;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

/** Referencia SQL completa del corpus cerrado. No acredita un diseño instalable bajo 110. */
final class ConservacionCompletaSQLTest extends TestCase
{
    use EsquemaSinTransaccion;

    private PDO $db;

    private array $mapa;

    private string $temporal;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['ContratoNominal', 'EsquemaNominal', 'ProyeccionNominal', 'ContenidoCompleto', 'CasosCompletos', 'RepositorioNominal'] as $clase) {
            require_once base_path('prototipos/orion-conservacion-nominal/'.$clase.'.php');
        }
        foreach (['GuardiaDesechable', 'CasoCanonico', 'VariantesCanonicas', 'Artefactos', 'RuntimeRetenido'] as $clase) {
            require_once base_path('prototipos/orion-conservacion-0_2/'.$clase.'.php');
        }
        User::factory()->create(['cod_usuario' => 'USUARIO-TEST', 'estado' => 'ACTIVO']);
        Personal::create(['cod_personal' => 'AUTOR-TEST', 'cod_usuario' => 'USUARIO-TEST', 'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintético', 'numero_documento' => 'COMPLETO-TEST', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        Residente::factory()->create(['cod_residente' => 'RES-TEST', 'estado' => 'ADMITIDO']);
        $this->db = DB::connection()->getPdo();
        RepositorioNominal::verificarDestino($this->db);
        $this->mapa = require base_path('prototipos/orion-conservacion-nominal/mapa-completo.php');
        $this->db->exec(EsquemaNominal::ddl($this->mapa, $this->db->getAttribute(PDO::ATTR_DRIVER_NAME)));
        $baseTemporal = PHP_OS_FAMILY === 'Windows' ? getenv('LOCALAPPDATA').'/Temp' : sys_get_temp_dir();
        $this->temporal = $baseTemporal.'/remembermind-nominal-'.bin2hex(random_bytes(10));
        mkdir($this->temporal, 0700, true);
    }

    protected function tearDown(): void
    {
        if (isset($this->temporal) && is_dir($this->temporal)) {
            $real = realpath($this->temporal);
            $padre = realpath(dirname($this->temporal));
            if ($real !== false && $padre !== false && dirname($real) === $padre
                && preg_match('/^remembermind-nominal-[a-f0-9]{20}$/D', basename($real))) {
                (new Filesystem)->deleteDirectory($real);
            }
        }
        parent::tearDown();
    }

    private function preparar(): array
    {
        if (PHP_OS_FAMILY !== 'Windows' || ! extension_loaded('zip')) {
            $this->markTestSkipped('El runtime retenido F02 requiere Windows y ZIP.');
        }
        $release = (new ReleaseMotorInstrumental)->crear(base_path(), $this->temporal.'/motor.zip', str_repeat('a', 40));
        $artefactos = new Artefactos($this->temporal.'/artefactos');
        $registro = $artefactos->retener($this->temporal.'/motor.zip', $release['huella']);
        $repo = new RepositorioNominal($this->db, $this->mapa);
        $repo->registrarRelease($registro);

        return [$repo, $release, $artefactos];
    }

    public function test_sql_recupera_el_dto_completo_y_replay_se_comprueba_por_separado(): void
    {
        [$repo, $release, $artefactos] = $this->preparar();
        foreach (CasosCompletos::casos() as $nombre => $sinRelease) {
            $dto = $sinRelease->vincularRelease($this->temporal.'/motor.zip', $release['huella']);
            $id = $dto->contenido['instrumental']['contenido']['contexto']['evaluacion'];
            $solicitud = (string) Str::uuid();
            self::assertTrue($repo->guardar($id, $solicitud, $dto, []), $nombre);
            self::assertFalse($repo->guardar($id, $solicitud, $dto, []), $nombre);
            $recuperada = $repo->recuperarCompleta($id);
            self::assertSame($dto->contenido, $recuperada->contenido, 'Lectura SQL íntegra: '.$nombre);
            self::assertSame($dto->huella, $recuperada->huella);
            self::assertSame($dto->contenido['instrumental']['huella'], $recuperada->contenido['instrumental']['huella']);
            self::assertSame($dto->contenido['petersen']['huella'], $recuperada->contenido['petersen']['huella']);
            if ($nombre === 'CONTRADICCION_PETERSEN') {
                self::assertSame('ABSTENCION_CONTRADICCION', $recuperada->contenido['petersen']['contenido']['salida']['estado']);
            } elseif ($nombre === 'DIFERENCIAL_PENDIENTE') {
                self::assertSame('ABSTENCION_EVALUACION_DIFERENCIAL', $recuperada->contenido['petersen']['contenido']['salida']['estado']);
            } elseif ($nombre === 'CINCO_DOMINIOS_P') {
                self::assertNull($recuperada->contenido['petersen']['contenido']['salida']['patron']);
            }
            $replay = $repo->reproducir($id, $artefactos, $this->temporal.'/replay-'.$id);
            self::assertSame($dto->contenido, $replay['contenido'], 'Replay separado: '.$nombre);
            self::assertSame($dto->huella, $replay['huella']);
        }
        self::assertSame(13, (int) $this->db->query('SELECT count(*) FROM evaluaciones_nominales')->fetchColumn());
        self::assertFalse($this->mapa['instalable']);
        self::assertGreaterThan(10, count(EsquemaNominal::tablas($this->mapa)) + 2);
    }

    public function test_fallo_parcial_revierte_tambien_salidas_y_membresias_y_el_cierre_impide_mutaciones(): void
    {
        [$repo, $release] = $this->preparar();
        $dto = VariantesCanonicas::casos()['PET01']->vincularRelease($this->temporal.'/motor.zip', $release['huella']);
        $id = $dto->contenido['instrumental']['contenido']['contexto']['evaluacion'];
        $solicitud = (string) Str::uuid();
        try {
            $repo->guardar($id, $solicitud, $dto, [], static fn () => throw new RuntimeException('Fallo parcial completo.'));
            self::fail('Debe revertirse la transacción.');
        } catch (RuntimeException $e) {
            self::assertSame('Fallo parcial completo.', $e->getMessage());
        }
        foreach (array_column($this->mapa['modelos'], 'tabla') as $tabla) {
            self::assertSame(0, (int) $this->db->query("SELECT count(*) FROM $tabla")->fetchColumn(), $tabla);
        }
        self::assertSame(0, (int) $this->db->query('SELECT count(*) FROM evaluaciones_nominales')->fetchColumn());
        self::assertTrue($repo->guardar($id, $solicitud, $dto, []));
        foreach (['UPDATE entradas_institucionales SET cod_fila=cod_fila',
            "DELETE FROM evaluaciones_nominales WHERE cod_evaluacion_experta='$id'"] as $sql) {
            $this->db->beginTransaction();
            try {
                $this->db->exec($sql);
                self::fail('No debe aceptar una mutación histórica.');
            } catch (PDOException) {
                self::assertTrue(true);
            } finally {
                $this->db->rollBack();
            }
        }
        self::assertSame($dto->contenido, $repo->recuperarCompleta($id)->contenido);
        $this->expectException(DomainException::class);
        $repo->guardar($id, (string) Str::uuid(), $dto, []);
    }

    public function test_postgres_lee_sql_en_otro_proceso_con_el_motor_inaccesible(): void
    {
        if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'pgsql') {
            $this->markTestSkipped('Lectura SQL PostgreSQL desde un proceso independiente.');
        }
        [$repo, $release] = $this->preparar();
        $dto = VariantesCanonicas::casos()['PET01']->vincularRelease($this->temporal.'/motor.zip', $release['huella']);
        $id = $dto->contenido['instrumental']['contenido']['contexto']['evaluacion'];
        $repo->guardar($id, (string) Str::uuid(), $dto, []);
        $proceso = new Process([PHP_BINARY, base_path('prototipos/orion-conservacion-nominal/worker-lectura-completa.php'), $id], base_path(), timeout: 25);
        $proceso->mustRun();
        $lectura = json_decode($proceso->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([], $lectura['intentos_inferencia']);
        self::assertSame($dto->huella, $lectura['huella']);
        self::assertSame($dto->contenido, $lectura['contenido']);
        self::assertSame($dto->contenido, InstantaneaEvaluacionInstitucional::verificarIntegridad($lectura['contenido'], $lectura['huella'])->contenido);
    }

    public function test_reversion_poblada_del_candidato_conserva_la_base_y_permite_restaurar_el_dto(): void
    {
        [$repo, $release] = $this->preparar();
        $dto = VariantesCanonicas::casos()['AUSENCIAS_NULL_ORDEN']->vincularRelease($this->temporal.'/motor.zip', $release['huella']);
        $id = $dto->contenido['instrumental']['contenido']['contexto']['evaluacion'];
        $solicitud = (string) Str::uuid();
        $repo->guardar($id, $solicitud, $dto, []);
        $registro = $repo->recuperarEntradas($id)['release'];
        $candidato = array_merge(['releases_nominales', 'evaluaciones_nominales'], array_column($this->mapa['modelos'], 'tabla'));
        $nombres = static fn () => array_column(DB::connection()->getSchemaBuilder()->getTables(), 'name');
        $base = array_values(array_diff($nombres(), $candidato));
        sort($base);
        $motor = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        $this->db->exec(EsquemaNominal::ddlReversion($this->mapa, $motor));
        $actuales = $nombres();
        sort($actuales);
        self::assertSame($base, $actuales);
        self::assertTrue(Residente::query()->whereKey('RES-TEST')->exists());
        self::assertTrue(User::query()->whereKey('USUARIO-TEST')->exists());
        self::assertTrue(Personal::query()->whereKey('AUTOR-TEST')->exists());
        $this->db->exec(EsquemaNominal::ddl($this->mapa, $motor));
        $restaurado = new RepositorioNominal($this->db, $this->mapa);
        $restaurado->registrarRelease($registro);
        self::assertTrue($restaurado->guardar($id, $solicitud, $dto, []));
        self::assertSame($dto->contenido, $restaurado->recuperarCompleta($id)->contenido);
        self::assertSame($dto->huella, $restaurado->recuperarCompleta($id)->huella);
    }

    public function test_postgres_cierra_ambas_salidas_con_reintento_e_insercion_tardia_reales(): void
    {
        if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'pgsql') {
            $this->markTestSkipped('Tres conexiones PostgreSQL y bloqueos comprobados.');
        }
        [$repo, $release] = $this->preparar();
        $casos = VariantesCanonicas::casos();
        $origen = $casos['PET01']->vincularRelease($this->temporal.'/motor.zip', $release['huella']);
        $origenId = $origen->contenido['instrumental']['contenido']['contexto']['evaluacion'];
        $repo->guardar($origenId, (string) Str::uuid(), $origen, []);
        $dto = $casos['PET02']->vincularRelease($this->temporal.'/motor.zip', $release['huella']);
        $id = $dto->contenido['instrumental']['contenido']['contexto']['evaluacion'];
        $solicitud = (string) Str::uuid();
        $archivo = $this->temporal.'/caso-sintetico.php';
        file_put_contents($archivo, '<?php return '.var_export(['contenido' => $dto->contenido, 'huella' => $dto->huella, 'fuente' => []], true).';');
        $servidor = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
        self::assertNotFalse($servidor);
        $procesos = $sockets = [];
        try {
            $repo->guardar($id, $solicitud, $dto, [], function (PDO $db) use ($id, $solicitud, $origenId, $archivo, $servidor, &$procesos, &$sockets): void {
                $q = $db->prepare('SELECT cod_fila FROM entradas_institucionales WHERE cod_evaluacion_experta=?');
                $q->execute([$id]);
                $propietario = $q->fetchColumn();
                $pids = [];
                foreach (['INSERTAR_HIJA', 'REINTENTAR'] as $modo) {
                    $p = new Process([PHP_BINARY, base_path('prototipos/orion-conservacion-nominal/worker-concurrencia.php'),
                        stream_socket_get_name($servidor, false), $modo, $archivo, $solicitud, $origenId, $propietario, 'COMPLETO'], base_path(), timeout: 30);
                    $p->start();
                    $procesos[] = $p;
                    $socket = stream_socket_accept($servidor, 10);
                    self::assertNotFalse($socket, $p->getErrorOutput());
                    stream_set_timeout($socket, 25);
                    $sockets[] = $socket;
                    $pids[] = json_decode((string) fgets($socket), true, flags: JSON_THROW_ON_ERROR)['pid'];
                }
                self::assertNotSame($pids[0], $pids[1]);
                foreach ($sockets as $socket) {
                    fwrite($socket, "GO\n");
                }
                $q = $db->prepare("SELECT count(*) FROM pg_stat_activity WHERE pid IN (?,?) AND wait_event_type='Lock' AND cardinality(pg_blocking_pids(pid))>0");
                $limite = hrtime(true) + 8_000_000_000;
                do {
                    $db->query('SELECT pg_stat_clear_snapshot()');
                    $q->execute($pids);
                    $esperando = (int) $q->fetchColumn();
                } while ($esperando !== 2 && hrtime(true) < $limite);
                self::assertSame(2, $esperando, 'Ambas conexiones deben esperar al cierre no confirmado.');
            });
            $resultados = [];
            foreach ($sockets as $i => $socket) {
                $resultados[] = json_decode((string) fgets($socket), true, flags: JSON_THROW_ON_ERROR);
                self::assertSame(0, $procesos[$i]->wait(), $procesos[$i]->getErrorOutput());
            }
            self::assertSame(['rechazo_sqlstate' => '55000'], $resultados[0]);
            self::assertSame(['nuevo' => false], $resultados[1]);
            self::assertSame(2, (int) $this->db->query('SELECT count(*) FROM evaluaciones_nominales')->fetchColumn());
            self::assertSame($dto->contenido, $repo->recuperarCompleta($id)->contenido);
        } finally {
            foreach ($sockets as $socket) {
                fclose($socket);
            }
            fclose($servidor);
            foreach ($procesos as $p) {
                if ($p->isRunning()) {
                    $p->stop();
                }
            }
        }
    }
}
