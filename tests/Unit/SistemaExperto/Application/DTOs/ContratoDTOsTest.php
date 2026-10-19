<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Application\DTOs;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\EvidenciasEntradaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\InformeExpertoDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoDclDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoInferenciaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use Error;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;

/**
 * TÍTULO: Manifiesto y límites de los cinco DTOs.
 * CAPA: Application (pruebas unitarias).
 * ¿PARA QUÉ SIRVE? Verifica estructura, documentación y dirección de dependencias.
 * ¿QUÉ RECIBE? Los cinco tipos autorizados y su código local.
 * ¿QUÉ HACE? Inspecciona sus contratos y comprueba inmutabilidad observable.
 * ¿QUÉ DEVUELVE? Aserciones reproducibles de arquitectura y documentación.
 * ¿QUÉ NO DEBE HACER? No modifica fuentes, inicia Laravel ni prueba ejecución clínica.
 * RELACIÓN CON O.R.I.O.N.: Reglamento de Application, etapas B y revisión proporcional.
 */
final class ContratoDTOsTest extends TestCase
{
    /** Verifica exactamente cinco archivos y sus PHPDoc; sin parámetros ni errores de lectura esperados. @return void */
    public function test_cinco_dtos_final_readonly_tipados_y_documentados(): void
    {
        $raiz = realpath(dirname(__DIR__, 5).'/app/Modulos/ApoyoDecision/Aplicacion/DTOs');
        $tipos = [SolicitudInferenciaDTO::class, EvidenciasEntradaDTO::class, ResultadoInferenciaDTO::class,
            ResultadoDclDTO::class, InformeExpertoDTO::class];
        $esperados = [];
        foreach ($tipos as $tipo) {
            $r = new ReflectionClass($tipo);
            self::assertTrue($r->isFinal());
            self::assertTrue($r->isReadOnly());
            self::assertSame('App\\Modulos\\ApoyoDecision\\Aplicacion\\DTOs', $r->getNamespaceName());
            $archivo = $r->getFileName();
            $esperados[] = realpath($archivo);
            self::assertStringStartsWith("<?php\n\ndeclare(strict_types=1);", file_get_contents($archivo));
            $doc = $r->getDocComment();
            self::assertNotFalse($doc);
            foreach (['TÍTULO:', 'CAPA:', '¿PARA QUÉ SIRVE?', '¿QUÉ RECIBE?', '¿QUÉ HACE?',
                '¿QUÉ DEVUELVE?', '¿QUÉ NO DEBE HACER?', 'RELACIÓN CON O.R.I.O.N.:'] as $apartado) {
                self::assertStringContainsString($apartado, $doc, $tipo);
            }
            foreach ($r->getProperties() as $propiedad) {
                self::assertTrue($propiedad->isReadOnly());
                self::assertNotNull($propiedad->getType());
                self::assertNotFalse($propiedad->getDocComment());
            }
            foreach ([...$r->getMethods(), ...$r->getReflectionConstants()] as $miembro) {
                self::assertNotFalse($miembro->getDocComment(), $tipo.'::'.$miembro->getName());
            }
        }
        $actuales = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($raiz)) as $archivo) {
            if ($archivo->isFile() && $archivo->getExtension() === 'php' && in_array(realpath($archivo->getPathname()), $esperados, true)) {
                $actuales[] = realpath($archivo->getPathname());
            }
        }
        sort($esperados);
        sort($actuales);
        self::assertCount(5, $actuales);
        self::assertSame($esperados, $actuales);
    }

    /** Revisa dependencias ejecutables y la dirección Domain/Application; sin parámetros ni errores esperados. @return void */
    public function test_dependencias_sin_laravel_ni_inversion_de_capas(): void
    {
        $raiz = dirname(__DIR__, 5);
        foreach (['DTOs' => '/app/Modulos/ApoyoDecision/Aplicacion/DTOs', 'Domain' => '/motor-experto/src/motor_experto/dominio/orion'] as $capa => $ruta) {
            $archivos = $capa === 'DTOs' ? array_map(fn ($clase) => (new ReflectionClass($clase))->getFileName(), [EvidenciasEntradaDTO::class, SolicitudInferenciaDTO::class, ResultadoInferenciaDTO::class, ResultadoDclDTO::class, InformeExpertoDTO::class]) : null;
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($raiz.$ruta)) as $archivo) {
                if (! $archivo->isFile() || $archivo->getExtension() !== 'php') {
                    continue;
                }
                if ($archivos !== null && ! in_array($archivo->getRealPath(), array_map('realpath', $archivos), true)) {
                    continue;
                }
                foreach (token_get_all(file_get_contents($archivo->getPathname())) as $token) {
                    if (! is_array($token) || ! in_array($token[0], [T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
                        continue;
                    }
                    self::assertDoesNotMatchRegularExpression('/^\\\\?(Illuminate|Laravel|Livewire)\\\\/', $token[1]);
                    $nombre = ltrim($token[1], '\\');
                    if ($capa === 'Domain') {
                        self::assertFalse(str_starts_with($nombre, 'App\\'), $nombre);
                    } elseif (str_starts_with($nombre, 'App\\')) {
                        self::assertTrue($nombre === 'App\\Modulos\\ApoyoDecision\\Aplicacion\\DTOs'
                            || str_starts_with($nombre, 'App\\Modulos\\ApoyoDecision\\Aplicacion\\DTOs\\'), $nombre);
                    }
                }
            }
        }
    }

    /** @return void @throws Error Rechazo esperado al sustituir la versión de la solicitud. */
    public function test_solicitud_no_permite_modificar_su_version(): void
    {
        $dto = new SolicitudInferenciaDTO('SIM-SOL', 'SIM-EVAL', 'SINTETICO-RES', F::fecha(), 'SIM-V1');
        $this->expectException(Error::class);
        $dto->versionConocimiento = 'SIM-V2';
    }

    /** @return void @throws Error Rechazo esperado al borrar una evidencia transportada. */
    public function test_coleccion_de_evidencias_no_puede_mutarse(): void
    {
        $e = F::evidencia();
        $dto = new EvidenciasEntradaDTO(new SolicitudInferenciaDTO('SIM-SOL', 'SIM-EVAL', 'SINTETICO-RES', F::fecha(), 'SIM-V1'), F::memoria([$e]));
        $this->expectException(Error::class);
        unset($dto->evidencias[$e->hecho->id]);
    }
}
