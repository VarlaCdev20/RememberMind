<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\AlmacenReleasesMotor;
use DomainException;
use MotorExperto\soporte\conservacion\ReleaseMotorInstrumental;
use PHPUnit\Framework\TestCase;
use ZipArchive;

final class AlmacenReleasesMotorTest extends TestCase
{
    private string $raiz;

    protected function setUp(): void
    {
        $this->raiz = sys_get_temp_dir().'/orion-retencion-'.bin2hex(random_bytes(8));
        self::assertTrue(mkdir($this->raiz, 0700));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->raiz.'/privado/*') ?: [] as $ruta) {
            if (is_file($ruta)) {
                unlink($ruta);
            }
        }
        if (is_file($this->raiz.'/privado/.retencion.lock')) {
            unlink($this->raiz.'/privado/.retencion.lock');
        }
        if (is_dir($this->raiz.'/privado')) {
            rmdir($this->raiz.'/privado');
        }
        if (is_file($this->raiz.'/origen.zip')) {
            unlink($this->raiz.'/origen.zip');
        }
        rmdir($this->raiz);
    }

    private function crear(): array
    {
        return (new ReleaseMotorInstrumental)->crear(dirname(__DIR__, 2), $this->raiz.'/origen.zip', str_repeat('a', 40));
    }

    public function test_retiene_una_sola_copia_privada_y_verifica_sus_bytes(): void
    {
        $manifiesto = $this->crear();
        $almacen = new AlmacenReleasesMotor($this->raiz.'/privado');
        $ruta = $almacen->retener($this->raiz.'/origen.zip', $manifiesto['huella']);

        self::assertSame($ruta, $almacen->retener($this->raiz.'/origen.zip', $manifiesto['huella']));
        self::assertSame($manifiesto, $almacen->verificarRetenido($manifiesto['huella']));
        self::assertSame(1, count(glob($this->raiz.'/privado/*.zip')));
    }

    public function test_detecta_alteracion_del_release_retenido_sin_reemplazarlo(): void
    {
        $manifiesto = $this->crear();
        $almacen = new AlmacenReleasesMotor($this->raiz.'/privado');
        $ruta = $almacen->retener($this->raiz.'/origen.zip', $manifiesto['huella']);
        $zip = new ZipArchive;
        self::assertTrue($zip->open($ruta));
        self::assertTrue($zip->addFromString('motor-experto/tests/independencia.php', '<?php // alterado'));
        self::assertTrue($zip->close());

        $this->expectException(DomainException::class);
        $almacen->retener($this->raiz.'/origen.zip', $manifiesto['huella']);
    }

    public function test_consultar_un_release_ausente_no_crea_un_directorio_ni_un_sustituto(): void
    {
        $almacen = new AlmacenReleasesMotor($this->raiz.'/privado');

        try {
            $almacen->verificarRetenido(str_repeat('a', 64));
            self::fail('La consulta de un release ausente debe fallar.');
        } catch (DomainException $e) {
            self::assertDirectoryDoesNotExist($this->raiz.'/privado');
        }
    }
}
