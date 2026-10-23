<?php

declare(strict_types=1);

namespace Tests\Prototipos;

use Illuminate\Filesystem\Filesystem;
use MotorExperto\soporte\conservacion\ReleaseMotorInstrumental;
use OrionFase02\Artefactos;
use OrionFase02\CasoCanonico;
use OrionFase02\EntradasCompuestas;
use OrionFase02\VariantesCanonicas;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Recuperación binaria desde copia de software, sin arrancar Laravel ni consultar SQL. */
final class ArtefactoAutonomoTest extends TestCase
{
    public function test_restaura_salidas_null_y_vinculo_correcto_solo_con_software_retenido(): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            $this->markTestSkipped('El runtime retenido del laboratorio es Windows.');
        }
        $prototipo = __DIR__;
        foreach (['RuntimeRetenido', 'Artefactos', 'EntradasCompuestas', 'CasoCanonico', 'VariantesCanonicas'] as $clase) {
            require_once $prototipo.'/'.$clase.'.php';
        }
        $raiz = dirname(__DIR__, 2);
        $temporal = sys_get_temp_dir().'/remembermind-offline-'.bin2hex(random_bytes(8));
        mkdir($temporal, 0700, true);
        try {
            $zip = $temporal.'/motor.zip';
            $m = (new ReleaseMotorInstrumental)->crear($raiz, $zip, str_repeat('a', 40));
            $artefactos = new Artefactos($temporal.'/artefactos');
            $registro = $artefactos->retener($zip, $m['huella']);
            $archivoRegistro = $temporal.'/registro-software.json';
            file_put_contents($archivoRegistro, json_encode($registro, JSON_THROW_ON_ERROR));
            $bootstrap = $temporal.'/restaurar-offline.ps1';
            copy($prototipo.'/restaurar-offline.ps1', $bootstrap);
            $fuente = json_decode(file_get_contents($raiz.'/app/Modulos/ApoyoDecision/Infraestructura/Persistencia/datos/orion-v1-conceptos.json'), true, flags: JSON_THROW_ON_ERROR);
            $variantes = VariantesCanonicas::casos();
            $casos = [
                'PET01' => (new CasoCanonico)->crear(['COG-MEM']),
                'PET02' => (new CasoCanonico)->crear(['COG-MEM', 'COG-ATE']),
                'PET03' => (new CasoCanonico)->crear(['COG-ATE']),
                'PET04' => (new CasoCanonico)->crear(['COG-ATE', 'COG-LEN']),
                'ABSTENCION' => (new CasoCanonico)->crear(sinC03: true),
                'INTERPRETATIVO' => $variantes['VINCULO_INTERPRETATIVO'],
                'AUSENCIAS_NULL_ORDEN' => $variantes['AUSENCIAS_NULL_ORDEN'],
                'SIN_GOBIERNO' => $variantes['SIN_GOBIERNO'],
            ];
            $ejecutar = function (string $nombre, object $dto, array $cambios = []) use ($temporal, $bootstrap, $archivoRegistro, $fuente): Process {
                $args = array_replace([
                    'Copia' => $temporal.'/artefactos/respaldo', 'Registro' => $archivoRegistro,
                    'HuellaRegistro' => hash_file('sha256', $archivoRegistro), 'Destino' => $temporal.'/restaurado-'.$nombre,
                    'HuellaCompuesta' => $dto->huella, 'HuellaInstrumental' => $dto->contenido['instrumental']['huella'],
                    'HuellaPetersen' => $dto->contenido['petersen']['huella'],
                ], $cambios);
                $comando = [getenv('SystemRoot').'/System32/WindowsPowerShell/v1.0/powershell.exe', '-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', $bootstrap];
                foreach ($args as $k => $valor) {
                    array_push($comando, '-'.$k, $valor);
                }
                $p = new Process($comando, $temporal);
                $p->setInput(serialize(EntradasCompuestas::proyectar($dto, $fuente)))->setTimeout(90)->run();
                return $p;
            };
            foreach ($casos as $nombre => $caso) {
                $dto = $caso->vincularRelease($zip, $m['huella']);
                $p = $ejecutar($nombre, $dto);
                self::assertSame(0, $p->getExitCode(), $p->getErrorOutput());
                $r = unserialize($p->getOutput(), ['allowed_classes' => false]);
                self::assertSame($dto->huella, $r['huella']);
                self::assertSame($dto->contenido, $r['contenido']);
                self::assertFileExists($temporal.'/restaurado-'.$nombre.'/runtime/php.exe');
                self::assertFileExists($temporal.'/restaurado-'.$nombre.'/release/replay-aislado.php');
                if ($nombre === 'INTERPRETATIVO') {
                    $resultado = $r['contenido']['instrumental']['contenido']['salida']['resultado']['perfil'];
                    self::assertCount(1, $resultado['COG-ATE']['traza']['evidencias']);
                    self::assertCount(0, $resultado['COG-MEM']['traza']['evidencias']);
                    self::assertSame('ATE-S01', $resultado['COG-ATE']['traza']['evidencias'][0]['procedencia_instrumental']['perfil_interpretativo']['componente']);
                    self::assertSame('MAP-TEST', $resultado['COG-ATE']['traza']['evidencias'][0]['mapeo_variable']);
                }
            }
            $dto = $casos['PET01']->vincularRelease($zip, $m['huella']);
            foreach (['HuellaRegistro', 'HuellaCompuesta', 'HuellaInstrumental', 'HuellaPetersen'] as $campo) {
                $p = $ejecutar('invalido-'.$campo, $dto, [$campo => str_repeat('0', 64)]);
                self::assertNotSame(0, $p->getExitCode());
                self::assertSame('', $p->getOutput());
            }
            $runtime = $temporal.'/artefactos/respaldo/runtime.zip';
            $z = new \ZipArchive;
            self::assertTrue($z->open($runtime) === true);
            $lanzador = $z->getFromName('replay-aislado.php');
            self::assertIsString($lanzador);
            self::assertTrue($z->addFromString('replay-aislado.php', $lanzador."\n// Alteración sintética del lanzador.\n"));
            $z->close();
            // Incluso actualizando la huella externa, la comprobación por archivo debe rechazarlo.
            $registro['huella_runtime_zip'] = hash_file('sha256', $runtime);
            file_put_contents($archivoRegistro, json_encode($registro, JSON_THROW_ON_ERROR));
            $p = $ejecutar('lanzador-alterado', $dto);
            self::assertNotSame(0, $p->getExitCode());
            self::assertSame('', $p->getOutput());
            self::assertTrue(copy($temporal.'/artefactos/primario/runtime.zip', $runtime));
            self::assertTrue($z->open($runtime) === true);
            self::assertTrue($z->deleteName('replay-aislado.php'));
            $z->close();
            $registro['huella_runtime_zip'] = hash_file('sha256', $runtime);
            file_put_contents($archivoRegistro, json_encode($registro, JSON_THROW_ON_ERROR));
            $p = $ejecutar('sin-lanzador', $dto);
            self::assertNotSame(0, $p->getExitCode());
            self::assertSame('', $p->getOutput());
        } finally {
            $real = realpath($temporal);
            if ($real !== false && dirname($real) === realpath(sys_get_temp_dir()) && ! is_link($real)
                && preg_match('/^remembermind-offline-[a-f0-9]{16}$/D', basename($real))) {
                (new Filesystem)->deleteDirectory($real);
            }
        }
    }
}
