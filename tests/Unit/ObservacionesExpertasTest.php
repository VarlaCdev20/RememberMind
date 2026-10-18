<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use DomainException;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ObservacionesExpertasTest extends TestCase
{
    private function registro(array $cambios = []): array
    {
        return array_replace(['id' => 'CONTROL-1', 'residente' => 'RES-1', 'autor' => 'PER-1',
            'fecha' => new DateTimeImmutable('2026-10-17T09:00:00-04:00'), 'estado' => 'VIGENTE',
            'episodio' => 'control:CONTROL-1', 'contexto_coherente' => true,
            'valores' => ['repite_preguntas' => true, 'olvida_indicaciones' => false, 'cambio_cognitivo' => null]], $cambios);
    }

    public function test_tres_reglas_distinguen_verdadero_falso_y_desconocido_sin_mutar_la_fuente(): void
    {
        $r = $this->registro();
        $antes = serialize($r);
        $motor = new EvaluadorObservaciones;
        $corte = new DateTimeImmutable('2026-10-17T12:00:00-04:00');
        $salida = $motor->evaluar('RES-1', $corte, [$r]);
        self::assertSame($salida, $motor->evaluar('RES-1', $corte, [$r]));
        self::assertSame($antes, serialize($r));
        self::assertSame(['OBS-MEM-01', 'OBS-MEM-02', 'OBS-COG-01'], array_column($salida['resultados'], 'regla'));
        self::assertSame(['CUMPLE', 'NO_CUMPLE', 'NO_EVALUABLE'], array_column($salida['resultados'], 'estado'));
        self::assertSame([true, false, null], array_column(array_column($salida['resultados'], 'condicion'), 'observado'));
        self::assertSame('Se documentó un episodio de repetición de preguntas.', $salida['resultados'][0]['hallazgo']);
        self::assertNull($salida['resultados'][1]['hallazgo']);
        self::assertNull($salida['resultados'][2]['hallazgo']);
        self::assertFalse($salida['persistido']);
        self::assertSame('PER-1', $salida['resultados'][0]['fuente']['autor']);
        self::assertSame('control:CONTROL-1', $salida['resultados'][0]['fuente']['episodio']);
    }

    public static function invalidos(): array
    {
        return ['anulado' => [['estado' => 'ANULADO']], 'sin autor' => [['autor' => null]],
            'sin fecha' => [['fecha' => null]], 'fecha invalida' => [['fecha' => 'incorrecta']],
            'fecha futura' => [['fecha' => new DateTimeImmutable('2026-10-18T09:00:00-04:00')]],
            'atencion ajena' => [['contexto_coherente' => false]],
            'valor textual' => [['valores' => ['repite_preguntas' => 'true', 'olvida_indicaciones' => 1]]]];
    }

    #[DataProvider('invalidos')]
    public function test_registros_invalidos_no_generan_hallazgos(array $cambios): void
    {
        $salida = (new EvaluadorObservaciones)->evaluar('RES-1', new DateTimeImmutable('2026-10-17T12:00:00-04:00'), [$this->registro($cambios)]);
        self::assertSame(['NO_EVALUABLE', 'NO_EVALUABLE', 'NO_EVALUABLE'], array_column($salida['resultados'], 'estado'));
        self::assertSame([null, null, null], array_column($salida['resultados'], 'hallazgo'));
    }

    public function test_fuente_duplicada_no_duplica_hallazgos_ni_convierte_frecuencia_en_recurrencia(): void
    {
        $r = $this->registro();
        $salida = (new EvaluadorObservaciones)->evaluar('RES-1', new DateTimeImmutable('2026-10-17T12:00:00-04:00'), [$r, $r]);
        self::assertCount(3, $salida['resultados']);
        self::assertStringContainsString('No acreditan', $salida['limite']);
    }

    public function test_fuente_de_otro_residente_se_rechaza(): void
    {
        $this->expectException(DomainException::class);
        (new EvaluadorObservaciones)->evaluar('RES-2', new DateTimeImmutable, [$this->registro()]);
    }

    public function test_fuente_duplicada_con_valores_distintos_es_error_tecnico_y_no_no_cumplio(): void
    {
        $this->expectException(DomainException::class);
        (new EvaluadorObservaciones)->evaluar('RES-1', new DateTimeImmutable,
            [$this->registro(), $this->registro(['valores' => ['repite_preguntas' => false]])]);
    }
}
