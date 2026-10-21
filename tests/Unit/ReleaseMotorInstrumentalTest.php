<?php

declare(strict_types=1);

namespace Tests\Unit;

use DomainException;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use MotorExperto\soporte\conservacion\ReleaseMotorInstrumental;
use PHPUnit\Framework\TestCase;
use ZipArchive;

final class ReleaseMotorInstrumentalTest extends TestCase
{
    private string $archivo;

    protected function setUp(): void
    {
        $this->archivo = sys_get_temp_dir().'/orion-release-unit-'.bin2hex(random_bytes(8)).'.zip';
    }

    protected function tearDown(): void
    {
        if (is_file($this->archivo)) {
            unlink($this->archivo);
        }
    }

    private function crear(): array
    {
        return (new ReleaseMotorInstrumental)->crear(dirname(__DIR__, 2), $this->archivo, str_repeat('a', 40));
    }

    public function test_archivo_recuperable_contiene_codigo_exacto_y_excluye_fuentes_clinicas(): void
    {
        $m = $this->crear();
        self::assertSame($m, (new ReleaseMotorInstrumental)->verificar($this->archivo, $m['huella']));
        self::assertFalse($m['incluye_datos_clinicos']);
        self::assertSame('ARTEFACTO-MOTOR-2', $m['contrato']);
        self::assertArrayHasKey('app/Modulos/ApoyoDecision/Infraestructura/Persistencia/datos/orion-v1-conceptos.json', $m['archivos']);
        self::assertArrayHasKey('motor-experto/src/motor_experto/soporte/conservacion/InstantaneaInstrumental.php', $m['archivos']);
        foreach (array_keys($m['archivos']) as $nombre) {
            self::assertDoesNotMatchRegularExpression('~(^|/)(\.env|storage|work|attachments|config)(/|$)~', $nombre);
        }
    }

    public function test_cambiar_un_archivo_de_codigo_se_detecta_sin_ejecutarlo(): void
    {
        $m = $this->crear();
        $zip = new ZipArchive;
        self::assertTrue($zip->open($this->archivo));
        self::assertTrue($zip->addFromString('motor-experto/tests/independencia.php', '<?php /* Código alterado sintético */'));
        self::assertTrue($zip->close());
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('alterado');
        (new ReleaseMotorInstrumental)->verificar($this->archivo, $m['huella']);
    }

    public function test_release_reempaquetado_con_dependencias_distintas_no_coincide_con_el_ejecutor_actual(): void
    {
        $manifiesto = $this->crear();
        $nombre = 'composer.lock';
        $contenido = '{"paquetes":"alterados para la prueba"}';
        $manifiesto['archivos'][$nombre] = hash('sha256', $contenido);
        unset($manifiesto['huella']);
        $manifiesto['huella'] = CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($manifiesto));
        $zip = new ZipArchive;
        self::assertTrue($zip->open($this->archivo));
        self::assertTrue($zip->addFromString($nombre, $contenido));
        self::assertTrue($zip->addFromString('MANIFIESTO-MOTOR.json', json_encode($manifiesto, JSON_THROW_ON_ERROR)));
        self::assertTrue($zip->close());

        $release = new ReleaseMotorInstrumental;
        self::assertSame($manifiesto, $release->verificar($this->archivo, $manifiesto['huella']));
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('mismo ejecutor completo');
        $release->verificarEjecutorActual($this->archivo, $manifiesto['huella']);
    }

    public function test_no_reemplaza_un_release_existente(): void
    {
        $this->crear();
        $this->expectException(DomainException::class);
        $this->crear();
    }

    public function test_rechaza_un_archivo_ajeno_aunque_el_manifiesto_tenga_huellas_consistentes(): void
    {
        $manifiesto = $this->crear();
        $zip = new ZipArchive;
        self::assertTrue($zip->open($this->archivo));
        $manifiesto['archivos']['datos-clinicos.txt'] = hash('sha256', 'dato sintético');
        unset($manifiesto['huella']);
        $manifiesto['huella'] = CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($manifiesto));
        self::assertTrue($zip->addFromString('datos-clinicos.txt', 'dato sintético'));
        self::assertTrue($zip->addFromString('MANIFIESTO-MOTOR.json', json_encode($manifiesto, JSON_THROW_ON_ERROR)));
        self::assertTrue($zip->close());

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('fuera del contrato');
        (new ReleaseMotorInstrumental)->verificar($this->archivo, $manifiesto['huella']);
    }

    public function test_un_manifiesto_ilegible_produce_un_error_de_dominio(): void
    {
        $manifiesto = $this->crear();
        $zip = new ZipArchive;
        self::assertTrue($zip->open($this->archivo));
        self::assertTrue($zip->addFromString('MANIFIESTO-MOTOR.json', '{incompleto'));
        self::assertTrue($zip->close());

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('ilegible');
        (new ReleaseMotorInstrumental)->verificar($this->archivo, $manifiesto['huella']);
    }

    public function test_release_ausente_no_se_sustituye_por_codigo_actual(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('ausente');
        (new ReleaseMotorInstrumental)->verificar($this->archivo, str_repeat('a', 64));
    }
}
