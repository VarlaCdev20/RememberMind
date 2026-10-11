<?php

namespace Tests\Feature;

use App\Models\Residente;
use App\Modulos\Reportes\Aplicacion\Consultas\ReporteDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportesPortabilidadTest extends TestCase
{
    use RefreshDatabase;

    public function test_listas_conservan_nombres_orden_y_limite_sin_depender_de_concat(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::connection()->getPdo()->sqliteCreateFunction('concat', static function () {
                throw new \RuntimeException('Esta prueba reproduce SQLite sin CONCAT.');
            });
        }

        $primero = Residente::factory()->create([
            'nombres' => 'Ana', 'apellido_paterno' => 'Alvarez', 'apellido_materno' => 'Rojas',
        ]);
        $segundo = Residente::factory()->create([
            'nombres' => 'Luis', 'apellido_paterno' => 'Perez',
        ]);
        DB::table('residentes')->where('cod_residente', $segundo->cod_residente)
            ->update(['apellido_materno' => null]);

        $reportes = app(ReporteDataService::class);
        foreach (['adultosLista', 'adultosListaCompleta'] as $consulta) {
            $lista = $reportes->$consulta();
            $this->assertSame([$primero->cod_residente, $segundo->cod_residente], $lista->pluck('cod_residente')->all());
            $this->assertSame(['Ana Alvarez Rojas', 'Luis Perez'], $lista->pluck('nombre_completo')->all());
            $this->assertSame([$primero->cod_residente], $reportes->$consulta(1)->pluck('cod_residente')->all());
        }
    }
}
