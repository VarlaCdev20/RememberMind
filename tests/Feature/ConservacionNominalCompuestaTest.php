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
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as C;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;
use MotorExperto\soporte\conservacion\ReleaseMotorInstrumental;
use OrionFase02\Artefactos;
use OrionFase02\EntradasCompuestas;
use OrionFase02\VariantesCanonicas;
use OrionNominal\ContratoNominal;
use OrionNominal\EsquemaNominal;
use OrionNominal\ProyeccionNominal;
use OrionNominal\RepositorioNominal;
use PDO;
use PDOException;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

/** Candidato nominal desechable: prueba completa de entradas/replay; no instala ni aprueba DDL. */
final class ConservacionNominalCompuestaTest extends TestCase
{
    use EsquemaSinTransaccion;

    private PDO $db;

    private string $temporal;

    protected function setUp(): void
    {
        parent::setUp();
        $base = base_path('prototipos');
        foreach (['ContratoNominal', 'EsquemaNominal', 'ProyeccionNominal', 'RepositorioNominal'] as $clase) {
            require_once $base.'/orion-conservacion-nominal/'.$clase.'.php';
        }
        foreach (['GuardiaDesechable', 'CasoCanonico', 'EntradasCompuestas', 'VariantesCanonicas', 'Artefactos', 'RuntimeRetenido'] as $clase) {
            require_once $base.'/orion-conservacion-0_2/'.$clase.'.php';
        }
        $usuario = User::factory()->create(['cod_usuario' => 'USUARIO-TEST', 'estado' => 'ACTIVO']);
        Personal::create(['cod_personal' => 'AUTOR-TEST', 'cod_usuario' => $usuario->getKey(), 'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintético', 'numero_documento' => 'NOMINAL-TEST', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        Residente::factory()->create(['cod_residente' => 'RES-TEST', 'estado' => 'ADMITIDO']);
        $this->db = DB::connection()->getPdo();
        RepositorioNominal::verificarDestino($this->db);
        $this->db->exec(EsquemaNominal::ddl(ContratoNominal::mapa(), $this->db->getAttribute(PDO::ATTR_DRIVER_NAME)));
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
            $this->markTestSkipped('El ejecutor binario retenido F02 está implementado únicamente para Windows con ZIP.');
        }
        $release = (new ReleaseMotorInstrumental)->crear(base_path(), $this->temporal.'/motor.zip', str_repeat('a', 40));
        $artefactos = new Artefactos($this->temporal.'/artefactos');
        $registro = $artefactos->retener($this->temporal.'/motor.zip', $release['huella']);
        $repo = new RepositorioNominal($this->db);
        $repo->registrarRelease($registro);
        $fuente = json_decode(file_get_contents(base_path('app/Modulos/ApoyoDecision/Infraestructura/Persistencia/datos/orion-v1-conceptos.json')),
            true, flags: JSON_THROW_ON_ERROR);

        return [$repo, $release, $artefactos, $fuente];
    }

    public function test_diez_casos_recuperan_todas_las_entradas_y_reproducen_el_dto_con_ejecutor_retenido(): void
    {
        [$repo, $release, $artefactos, $fuente] = $this->preparar();
        foreach (VariantesCanonicas::casos() as $nombre => $sinRelease) {
            $dto = $sinRelease->vincularRelease($this->temporal.'/motor.zip', $release['huella']);
            $id = $dto->contenido['instrumental']['contenido']['contexto']['evaluacion'];
            $solicitud = (string) Str::uuid();
            self::assertTrue($repo->guardar($id, $solicitud, $dto, $fuente), $nombre);
            self::assertFalse($repo->guardar($id, $solicitud, $dto, $fuente), 'Idempotencia: '.$nombre);
            $recuperada = $repo->recuperarEntradas($id);
            self::assertSame(C::ordenar(EntradasCompuestas::proyectar($dto, $fuente)), C::ordenar($recuperada['entradas']), $nombre);
            $replay = $repo->reproducir($id, $artefactos, $this->temporal.'/replay-'.$id);
            self::assertSame($dto->contenido, $replay['contenido'], 'DTO íntegro: '.$nombre);
            self::assertSame($dto->huella, $replay['huella'], 'Huella: '.$nombre);
            self::assertSame($dto->contenido['instrumental']['huella'], $replay['contenido']['instrumental']['huella']);
            self::assertSame($dto->contenido['petersen']['huella'], $replay['contenido']['petersen']['huella']);
        }
        self::assertSame(10, (int) $this->db->query('SELECT count(*) FROM evaluaciones_nominales')->fetchColumn());
        self::assertCount(32, EsquemaNominal::tablas(ContratoNominal::mapa()));
    }

    public function test_fallo_parcial_no_deja_filas_y_una_historia_cerrada_es_inmutable(): void
    {
        [$repo, $release, $artefactos, $fuente] = $this->preparar();
        $dto = VariantesCanonicas::casos()['PET01']->vincularRelease($this->temporal.'/motor.zip', $release['huella']);
        $id = $dto->contenido['instrumental']['contenido']['contexto']['evaluacion'];
        try {
            $repo->guardar($id, (string) Str::uuid(), $dto, $fuente, static fn () => throw new RuntimeException('Fallo intermedio sintético.'));
            self::fail('La operación debía fallar.');
        } catch (RuntimeException $e) {
            self::assertSame('Fallo intermedio sintético.', $e->getMessage());
        }
        foreach (array_unique(array_column(ContratoNominal::mapa()['modelos'], 'tabla')) as $tabla) {
            self::assertSame(0, (int) $this->db->query("SELECT count(*) FROM $tabla")->fetchColumn(), $tabla);
        }
        self::assertSame(0, (int) $this->db->query('SELECT count(*) FROM evaluaciones_nominales')->fetchColumn());
        $repo->guardar($id, (string) Str::uuid(), $dto, $fuente);
        foreach (['UPDATE entradas_institucionales SET cod_fila=cod_fila', 'DELETE FROM capturas_adquiridas',
            "UPDATE evaluaciones_nominales SET huella_compuesta='ALTERADA'"] as $sql) {
            $this->db->beginTransaction();
            try {
                $this->db->exec($sql);
                self::fail('No debe aceptar una mutación histórica.');
            } catch (PDOException $e) {
                self::assertTrue(str_contains($e->getMessage(), 'Evaluación cerrada')
                    || str_contains($e->getMessage(), 'Transición histórica inválida'), $e->getMessage());
            } finally {
                $this->db->rollBack();
            }
        }
        self::assertSame($dto->huella, $repo->reproducir($id, $artefactos, $this->temporal.'/replay-cerrada')['huella']);
    }

    public function test_postgres_solapa_cierre_insercion_tardia_y_reintento_idempotente(): void
    {
        if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'pgsql') {
            $this->markTestSkipped('Bloqueos entre tres conexiones PostgreSQL reales.');
        }
        [$repo, $release, $artefactos, $fuente] = $this->preparar();
        $casos = VariantesCanonicas::casos();
        $origen = $casos['PET01']->vincularRelease($this->temporal.'/motor.zip', $release['huella']);
        $origenId = $origen->contenido['instrumental']['contenido']['contexto']['evaluacion'];
        $repo->guardar($origenId, (string) Str::uuid(), $origen, $fuente);
        $dto = $casos['PET02']->vincularRelease($this->temporal.'/motor.zip', $release['huella']);
        $id = $dto->contenido['instrumental']['contenido']['contexto']['evaluacion'];
        $solicitud = (string) Str::uuid();
        $archivo = $this->temporal.'/caso-sintetico.php';
        file_put_contents($archivo, '<?php return '.var_export(['contenido' => $dto->contenido, 'huella' => $dto->huella, 'fuente' => $fuente], true).';');
        $servidor = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
        self::assertNotFalse($servidor);
        $procesos = $sockets = [];
        try {
            $repo->guardar($id, $solicitud, $dto, $fuente, function (PDO $db) use ($id, $solicitud, $origenId, $archivo, $servidor, &$procesos, &$sockets): void {
                $q = $db->prepare('SELECT cod_fila FROM entradas_institucionales WHERE cod_evaluacion_experta=?');
                $q->execute([$id]);
                $propietario = $q->fetchColumn();
                $pids = [];
                foreach (['INSERTAR_HIJA', 'REINTENTAR'] as $modo) {
                    $p = new Process([PHP_BINARY, base_path('prototipos/orion-conservacion-nominal/worker-concurrencia.php'),
                        stream_socket_get_name($servidor, false), $modo, $archivo, $solicitud, $origenId, $propietario], base_path(), timeout: 25);
                    $p->start();
                    $procesos[] = $p;
                    $socket = stream_socket_accept($servidor, 10);
                    self::assertNotFalse($socket, $p->getErrorOutput());
                    stream_set_timeout($socket, 20);
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
                self::assertSame(2, $esperando, 'Inserción y solicitud repetida deben esperar al cierre aún sin confirmar.');
            });
            $resultados = [];
            foreach ($sockets as $i => $socket) {
                $resultados[] = json_decode((string) fgets($socket), true, flags: JSON_THROW_ON_ERROR);
                self::assertSame(0, $procesos[$i]->wait(), $procesos[$i]->getErrorOutput());
            }
            self::assertSame(['rechazo_sqlstate' => '55000'], $resultados[0]);
            self::assertSame(['nuevo' => false], $resultados[1]);
            self::assertSame(2, (int) $this->db->query('SELECT count(*) FROM evaluaciones_nominales')->fetchColumn());
            self::assertSame($dto->huella, $repo->reproducir($id, $artefactos, $this->temporal.'/replay-concurrente')['huella']);
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

    public function test_rechaza_identidad_ajena_propietario_cruzado_y_cierre_incompleto(): void
    {
        [$repo, $release, $artefactos, $fuente] = $this->preparar();
        $dto = VariantesCanonicas::casos()['PET01']->vincularRelease($this->temporal.'/motor.zip', $release['huella']);
        $id = $dto->contenido['instrumental']['contenido']['contexto']['evaluacion'];
        try {
            $repo->guardar('OTRA-EVALUACION', (string) Str::uuid(), $dto, $fuente);
            self::fail('Debe rechazar una identidad distinta a la conservada.');
        } catch (DomainException $e) {
            self::assertStringContainsString('identidad SQL', $e->getMessage());
        }
        self::assertSame(0, (int) $this->db->query('SELECT count(*) FROM evaluaciones_nominales')->fetchColumn());
        $repo->guardar($id, (string) Str::uuid(), $dto, $fuente);
        try {
            $repo->guardar($id, (string) Str::uuid(), $dto, $fuente);
            self::fail('Un identificador existente no puede adquirir otra solicitud.');
        } catch (DomainException $e) {
            self::assertStringContainsString('otra identidad o contenido', $e->getMessage());
        }
        $cabecera = $this->db->query('SELECT * FROM evaluaciones_nominales')->fetch(PDO::FETCH_ASSOC);
        $cabecera['cod_evaluacion_experta'] = 'CONTROL-FK';
        $cabecera['solicitud_uuid'] = (string) Str::uuid();
        $cabecera['estado'] = 'ABIERTA';
        $captura = $this->db->query('SELECT * FROM capturas_adquiridas WHERE cod_entrada_institucional IS NOT NULL LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        $captura['cod_fila'] = bin2hex(random_bytes(10));
        $captura['cod_evaluacion_experta'] = 'CONTROL-FK';
        $captura['posicion_coleccion'] = 999;
        $proyeccion = new ProyeccionNominal($this->db, ContratoNominal::mapa());
        foreach (['FK', 'CIERRE'] as $operacion) {
            $this->db->beginTransaction();
            try {
                $proyeccion->insertar([['tabla' => 'evaluaciones_nominales', 'fila' => $cabecera]]);
                if ($operacion === 'FK') {
                    // La raíz existe, pero es propiedad de otra evaluación.
                    $proyeccion->insertar([['tabla' => 'capturas_adquiridas', 'fila' => $captura]]);
                } else {
                    $this->db->exec("UPDATE evaluaciones_nominales SET estado='CERRADA' WHERE cod_evaluacion_experta='CONTROL-FK'");
                }
                self::fail('No debe aceptar '.$operacion);
            } catch (PDOException $e) {
                self::assertTrue($operacion === 'FK' ? in_array($e->getCode(), ['23503', '23000'], true)
                    : str_contains($e->getMessage(), 'Conservación incompleta'), $e->getMessage());
            } finally {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
            }
        }
        if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $this->db->beginTransaction();
            try {
                $proyeccion->insertar([['tabla' => 'evaluaciones_nominales', 'fila' => $cabecera]]);
                $this->db->commit();
                self::fail('PostgreSQL no debe confirmar una evaluación abierta.');
            } catch (PDOException $e) {
                self::assertSame('23514', $e->getCode());
            } finally {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
            }
            try {
                $this->db->exec('TRUNCATE TABLE evaluaciones_nominales CASCADE');
                self::fail('TRUNCATE no puede borrar la historia.');
            } catch (PDOException $e) {
                self::assertSame('55000', $e->getCode());
            }
        }
        self::assertSame(1, (int) $this->db->query('SELECT count(*) FROM evaluaciones_nominales')->fetchColumn());
        self::assertSame($dto->huella, $repo->reproducir($id, $artefactos, $this->temporal.'/replay-integridad')['huella']);
    }

    public function test_ensayo_poblado_se_retira_y_restauran_las_dos_ramas_sin_cambiar_tablas_instaladas(): void
    {
        [$repo, $release, $artefactos, $fuente] = $this->preparar();
        $motor = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        $tablas = array_merge(array_keys(EsquemaNominal::tablas(ContratoNominal::mapa())), ['evaluaciones_nominales', 'releases_nominales']);
        $listar = fn () => $this->db->query($motor === 'pgsql'
            ? "SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE' ORDER BY table_name"
            : "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
        $instaladas = array_values(array_diff($listar(), $tablas));
        self::assertCount(116, $instaladas, '100 de negocio y 16 técnicas migradas en base desechable.');
        $residente = $this->db->query("SELECT * FROM residentes WHERE cod_residente='RES-TEST'")->fetch(PDO::FETCH_ASSOC);
        $usuario = $this->db->query("SELECT * FROM usuarios WHERE cod_usuario='USUARIO-TEST'")->fetch(PDO::FETCH_ASSOC);
        $dto = VariantesCanonicas::casos()['SEIS_FUENTES_Y_DOCUMENTOS']->vincularRelease($this->temporal.'/motor.zip', $release['huella']);
        $id = $dto->contenido['instrumental']['contenido']['contexto']['evaluacion'];
        $solicitud = (string) Str::uuid();
        $repo->guardar($id, $solicitud, $dto, $fuente);
        $backup = $repo->recuperarEntradas($id);
        $replay = $repo->reproducir($id, $artefactos, $this->temporal.'/replay-antes-reversion');
        $this->db->beginTransaction();
        try {
            $this->db->exec(EsquemaNominal::ddlReversion(ContratoNominal::mapa(), $motor));
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
        self::assertSame($instaladas, $listar());
        self::assertSame($residente, $this->db->query("SELECT * FROM residentes WHERE cod_residente='RES-TEST'")->fetch(PDO::FETCH_ASSOC));
        self::assertSame($usuario, $this->db->query("SELECT * FROM usuarios WHERE cod_usuario='USUARIO-TEST'")->fetch(PDO::FETCH_ASSOC));
        $this->db->exec(EsquemaNominal::ddl(ContratoNominal::mapa(), $motor));
        $repo = new RepositorioNominal($this->db);
        $repo->registrarRelease($backup['release']);
        $desdeSql = InstantaneaEvaluacionInstitucional::verificar($replay['contenido'], $replay['huella']);
        $repo->guardar($id, $solicitud, $desdeSql, $fuente);
        self::assertSame(C::ordenar($backup['entradas']), C::ordenar($repo->recuperarEntradas($id)['entradas']));
        self::assertSame($dto->contenido, $repo->reproducir($id, $artefactos, $this->temporal.'/replay-despues-reversion')['contenido']);
    }
}
