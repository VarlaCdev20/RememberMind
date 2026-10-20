<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\InventarioExperto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class InventarioReutilizacionExpertaTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventario_identifica_fk_y_conteos_sin_escribir_ni_consultar_payloads(): void
    {
        $antes = Schema::getTableListing();
        $consultas = [];
        DB::listen(static function ($consulta) use (&$consultas): void {
            $consultas[] = $consulta->sql;
        });
        $sinConteos = InventarioExperto::diagnosticar(DB::connection());
        $conConteos = InventarioExperto::diagnosticar(DB::connection(), true);
        self::assertSame(33, $conConteos['expertas_presentes']);
        self::assertSame([], $conConteos['expertas_ausentes']);
        self::assertContains('residentes', $conConteos['otras_tablas_fisicas']);
        self::assertContains('migrations', $conConteos['otras_tablas_fisicas']);
        self::assertNull($sinConteos['detalle'][0]['filas']);
        $cabecera = collect($conConteos['detalle'])->firstWhere('tabla', 'evaluaciones_expertas');
        self::assertSame(0, $cabecera['filas']);
        self::assertContains('revisiones_evaluacion_experta', array_column($cabecera['referencias_entrantes'], 'tabla'));
        self::assertSame($antes, Schema::getTableListing());
        foreach ($consultas as $sql) {
            self::assertDoesNotMatchRegularExpression('/^\s*(insert|update|delete|alter|create|drop|truncate)\b/i', $sql);
            self::assertDoesNotMatchRegularExpression('/^\s*select\s+\*\s+from\s+["`]?('.implode('|', InventarioExperto::TABLAS).')\b/i', $sql);
        }
    }

    public function test_comando_existente_informa_el_alcance_sin_declarar_tablas_prescindibles(): void
    {
        $this->artisan('app:auditar-base-datos-remember-mind', ['--contar-filas' => true])
            ->expectsOutputToContain('Expertas presentes: 33 / 33')
            ->expectsOutputToContain('Un conteo cero no demuestra desuso.')
            ->assertSuccessful();
    }
}
