<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\ValueObjects;

use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EvaluabilidadEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\EstadoReglaEnum;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\OperadorCondicionEnum;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\CriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\MotivoAbstencionEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\SubtipoDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoIadlEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\OportunidadIadlEnum;
use BackedEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionEnum;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use PHPUnit\Framework\TestCase;

/** Verificar los vocabularios y límites de la Fase A sin implementar inferencias. */
final class ContratoValueObjectsTest extends TestCase
{
    /**
     * Comprueba que cada literal declarado reconstruye la misma identidad.
     * @param class-string<BackedEnum> $enum
     * @return void
     */
    #[DataProvider('catalogos')]
    public function test_construccion_conserva_identidad_y_rechaza_valores_ajenos(string $enum): void
    {
        foreach ($enum::cases() as $case) {
            self::assertSame($case, $enum::from($case->value));
        }

        $invalid = (new ReflectionEnum($enum))->getBackingType()->getName() === 'int'
            ? -1 : 'FUERA_DEL_CONTRATO';
        self::assertNull($enum::tryFrom($invalid));
    }

    /**
     * Enumera exactamente los quince archivos autorizados de producción.
     * @return iterable<string, array{class-string<BackedEnum>}>
     */
    public static function catalogos(): iterable
    {
        foreach ([
            TipoRelacionSemanticaEnum::class,
            OperadorCondicionEnum::class,
            EstadoReglaEnum::class,
            DominioCognitivoEnum::class,
            SubcomponenteCognitivoEnum::class,
            EvaluabilidadEnum::class,
            HallazgoInstrumentalEnum::class,
            EstadoDominioEnum::class,
            EstadoIadlEnum::class,
            OportunidadIadlEnum::class,
            EstadoDiferencialEnum::class,
            CriterioDclEnum::class,
            EstadoCriterioDclEnum::class,
            SubtipoDclEnum::class,
            MotivoAbstencionEnum::class,
        ] as $enum) {
            yield $enum => [$enum];
        }
    }

    /**
     * Verifica el manifiesto, namespaces, strict_types y los PHPDoc requeridos.
     * @return void
     */
    public function test_estructura_y_documentacion_completas(): void
    {
        $root = dirname(__DIR__, 5).'/motor-experto/dominio/orion/ValueObjects';
        $actual = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $actual[] = realpath($file->getPathname());
            }
        }

        $expected = [];
        foreach (self::catalogos() as [$enum]) {
            $reflection = new ReflectionEnum($enum);
            $path = $reflection->getFileName();
            $expected[] = realpath($path);
            $relative = str_replace('\\', '/', substr($path, strlen(realpath($root)) + 1));
            self::assertSame(
                'MotorExperto\\dominio\\orion\\ValueObjects\\'.str_replace('/', '\\', substr($relative, 0, -4)),
                $reflection->getName(),
            );
            self::assertStringStartsWith("<?php\n\ndeclare(strict_types=1);", file_get_contents($path));
            $doc = $reflection->getDocComment();
            self::assertNotFalse($doc, $enum);

            self::assertIsString($doc);
            self::assertMatchesRegularExpression('/\p{L}{3}/u', $doc);
            self::assertLessThanOrEqual(240, mb_strlen($doc));
            foreach ($reflection->getCases() as $case) {
                self::assertNotFalse($case->getDocComment(), $enum.'::'.$case->getName());
            }
            foreach ($reflection->getMethods() as $method) {
                if (! $method->isInternal()) {
                    self::assertNotFalse($method->getDocComment(), $enum.'::'.$method->getName());
                }
            }
        }
        sort($expected);
        sort($actual);
        self::assertCount(15, $actual);
        self::assertSame($expected, $actual);
    }

    /**
     * Busca dependencias Laravel en tokens ejecutables de todo el Domain.
     * @return void
     */
    public function test_domain_no_depende_de_laravel(): void
    {
        $root = dirname(__DIR__, 5).'/motor-experto/dominio/orion';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            foreach (token_get_all(file_get_contents($file->getPathname())) as $token) {
                if (is_array($token) && in_array($token[0], [T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
                    self::assertDoesNotMatchRegularExpression(
                        '/^\\\\?(Illuminate|Laravel|Livewire)\\\\/',
                        $token[1],
                        $file->getPathname(),
                    );
                }
            }
        }
    }

}
