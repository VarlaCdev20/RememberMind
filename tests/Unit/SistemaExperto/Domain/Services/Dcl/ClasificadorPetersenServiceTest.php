<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Services\Dcl;

use MotorExperto\dominio\orion\Services\Dcl\ClasificadorPetersenService;
use MotorExperto\dominio\orion\Services\Dcl\EvaluadorRequisitosDclService;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum as C;
use MotorExperto\dominio\orion\ValueObjects\Dcl\MotivoAbstencionEnum as M;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoDiferencialEnum as D;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;

/**
 * TÍTULO: Contraste del clasificador con las 62 filas oficiales de la matriz 03A.
 * CAPA: Domain (pruebas).
 * SUBSISTEMA: DCL experimental.
 * RESPONSABILIDAD: Verificar patrones, abstenciones y precedencias con un oráculo externo.
 * ENTRADAS: CSV oficiales congelados, con procedencia en Fixtures/README.md.
 * SALIDAS: Igualdad literal con cada resultado esperado, sin traducirlo por conveniencia.
 * RESTRICCIONES: Solo escenarios ficticios; no equivale a validación clínica.
 * TRAZABILIDAD: DCL-PET-20261017-v0.1 y conciliación CAS-09/CAS-10/CAS-11.
 */
final class ClasificadorPetersenServiceTest extends TestCase
{
    /** @return iterable<string, array{array}> Filas externas de las dos hojas, sin ejecutar red durante tests. */
    public static function casosOficiales(): iterable
    {
        foreach (['05_Casos_Sinteticos', '10_Cobertura_Exhaustiva'] as $hoja) {
            $archivo = fopen(__DIR__.'/Fixtures/'.$hoja.'.csv', 'rb');
            $cabecera = fgetcsv($archivo, 0, ',', '"', '');
            while (($fila = fgetcsv($archivo, 0, ',', '"', '')) !== false) {
                if ($fila === [null]) {
                    continue;
                }
                $dato = array_combine($cabecera, $fila);
                yield $hoja.':'.($dato['Caso'] ?? $dato['Configuración']) => [$dato];
            }
            fclose($archivo);
        }
    }

    /** @param array<string, string> $fila Caso oficial. Comprueba salida y memoria original; no ejecuta persistencia. */
    #[DataProvider('casosOficiales')]
    public function test_coincide_literalmente_con_la_matriz_oficial(array $fila): void
    {
        $estados = array_map(static fn (string $d): string => $fila[$d], ['MEM', 'ATE', 'EJE', 'LEN', 'VIS']);
        $criterios = [];
        for ($i = 1; $i <= 5; $i++) {
            $criterios['DCL-C0'.$i] = match ($fila['C0'.$i] ?? 'S') {
                'S' => C::ACREDITADO, 'N', 'B' => C::NO_ACREDITADO,
                'U' => C::PENDIENTE, 'X' => C::CONTRADICTORIO,
            };
        }
        $diferencial = match ($fila['C04'] ?? 'S') {
            'B', 'N' => D::IMPEDIMENTO_NO_RESUELTO, 'X' => D::CONTRADICTORIO,
            'U' => D::PENDIENTE, default => D::COMPLETADO_SIN_IMPEDIMENTO,
        };
        $perfil = E::perfil($estados, $criterios, $diferencial, $criterios['DCL-C03']);
        $salida = (new ClasificadorPetersenService)->clasificar($perfil);
        $esperado = $fila['Esperado simulación'] ?? $fila['Regla/Salida'];
        self::assertSame($esperado, $salida->patronCandidato?->value ?? $salida->motivos[0]->value);
        self::assertSame($perfil, $salida->perfil);
        self::assertSame([], $salida->reglasAplicadas, 'No se atribuyen reglas de conocimiento no ejecutadas.');
        self::assertCount($salida->patronCandidato === null ? 1 : 0, $salida->motivos);
    }

    /** Conserva detalles de impedimentos concurrentes y un único estado principal contractual. */
    public function test_contradiccion_prevalece_sin_ocultar_otros_impedimentos(): void
    {
        $perfil = E::perfil(['X', 'U', 'P', 'P', 'P'], ['DCL-C01' => C::PENDIENTE,
            'DCL-C04' => C::NO_ACREDITADO], D::IMPEDIMENTO_NO_RESUELTO);
        $revision = (new EvaluadorRequisitosDclService)->evaluar($perfil);
        self::assertSame([M::ABSTENCION_CONTRADICCION], $revision['motivos']);
        self::assertContains(M::ABSTENCION_EVALUACION_DIFERENCIAL, $revision['impedimentos']);
        self::assertContains(M::ABSTENCION_INFORMACION_INSUFICIENTE, $revision['impedimentos']);
        self::assertArrayHasKey('DCL-C01', $revision['razones']);
        self::assertFalse($revision['habilitado']);
    }

    /** Una contradicción funcional original no se degrada a mera falta de información C03. */
    public function test_contradiccion_funcional_se_conserva_aunque_c03_este_pendiente(): void
    {
        $perfil = E::perfil(cambiosCriterios: ['DCL-C03' => C::PENDIENTE], independencia: C::CONTRADICTORIO);
        $salida = (new ClasificadorPetersenService)->clasificar($perfil);
        self::assertSame([M::ABSTENCION_CONTRADICCION], $salida->motivos);
        self::assertNull($salida->patronCandidato);
        self::assertStringContainsString('Revisión artificial', $salida->justificacion);
    }
}
