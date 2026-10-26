<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Alergia;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\Diagnostico;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PanoramaResidentes;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class PanoramaResidentesTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    private Personal $autor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 22)->setTime(12, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($this->usuario);
        $this->autor = Personal::create(['cod_usuario' => $this->usuario->getKey(), 'nombres' => 'Autora', 'apellido_paterno' => 'Sintética',
            'numero_documento' => 'PANORAMA-TEST', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
    }

    private function alergia(Residente $residente, array $cambios = []): void
    {
        Alergia::create(array_replace(['cod_residente' => $residente->getKey(), 'cod_personal' => $this->autor->getKey(),
            'sustancia' => 'Sustancia sintética', 'fecha_hora' => now()->subDay(), 'estado' => 'ACTIVA'], $cambios));
    }

    public function test_frecuencias_cuentan_personas_una_vez_antes_de_paginar_y_excluyen_inactivos_futuros_y_egresados(): void
    {
        $primero = Residente::factory()->create(['estado' => 'ADMITIDO', 'nombres' => 'Cohorte Uno']);
        $segundo = Residente::factory()->create(['estado' => 'ADMITIDO', 'nombres' => 'Cohorte Dos']);
        $sin = Residente::factory()->create(['estado' => 'ADMITIDO', 'nombres' => 'Cohorte Tres']);
        for ($i = 0; $i < 7; $i++) {
            Residente::factory()->create(['estado' => 'ADMITIDO', 'nombres' => 'Cohorte Más']);
        }
        $egresado = Residente::factory()->create(['estado' => 'EGRESADO']);
        $this->alergia($primero);
        $this->alergia($primero);
        $this->alergia($segundo);
        $this->alergia($sin, ['estado' => 'INACTIVA']);
        $this->alergia($sin, ['fecha_hora' => now()->addDay()]);
        $this->alergia($egresado);
        $antes = DB::table('alergias')->count();
        $consulta = app(PanoramaResidentes::class);
        $dto = $consulta->consultar($this->usuario, '', 'alergias', null);
        self::assertSame(10, $dto['total']);
        self::assertSame(2, $dto['con_datos']);
        self::assertSame(8, $dto['sin_datos']);
        self::assertSame(2, $dto['filas'][0]['cantidad']);
        self::assertSame(20.0, $dto['filas'][0]['porcentaje']);
        self::assertCount(8, $dto['residentes']);
        $filtrado = $consulta->consultar($this->usuario, '', 'alergias', $dto['filas'][0]['id']);
        self::assertSame(2, $filtrado['residentes']->total());
        self::assertSame(10, $filtrado['total']);
        self::assertSame(8, $consulta->consultar($this->usuario, '', 'alergias', 'sin-registro')['residentes']->total());
        self::assertSame(1, $consulta->consultar($this->usuario, 'Cohorte Uno', 'alergias', null)['total']);
        self::assertSame($antes, DB::table('alergias')->count());
    }

    public function test_falta_permiso_de_campo_rechaza_antes_de_leer_datos_clinicos(): void
    {
        Role::findByName('SUPERADMINISTRADOR', 'web')->revokePermissionTo('alergias.ver');
        $consultas = [];
        DB::listen(function ($q) use (&$consultas): void {
            $consultas[] = $q->sql;
        });
        try {
            app(PanoramaResidentes::class)->consultar($this->usuario, '', 'alergias', null);
            self::fail('Se esperaba denegación del campo.');
        } catch (HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
        }
        self::assertFalse(collect($consultas)->contains(fn ($sql) => str_contains($sql, 'from "alergias"')));
        $this->usuario->givePermissionTo('alergias.ver');
        $this->usuario->assignRole('FAMILIAR');
        try {
            app(PanoramaResidentes::class)->consultar($this->usuario, '', 'alergias', null);
            self::fail('Un rol familiar adicional conserva la denegación clínica.');
        } catch (HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
        }
        self::assertFalse(collect($consultas)->contains(fn ($sql) => str_contains($sql, 'from "alergias"')));
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_diagnosticos_requieren_atencion_coherente_y_no_anulada_y_detalle_preserva_autoria(): void
    {
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVO']);
        foreach (['CERRADA', 'ANULADA'] as $estado) {
            $atencion = Atencion::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $this->autor->getKey(),
                'cod_area' => $area->getKey(), 'tipo_atencion' => 'MEDICA', 'fecha_hora' => now()->subDay(), 'estado' => $estado]);
            Diagnostico::create(['cod_residente' => $residente->getKey(), 'cod_atencion' => $atencion->getKey(),
                'cod_personal' => $this->autor->getKey(), 'nombre' => 'Diagnóstico '.$estado, 'estado' => 'ACTIVO', 'fecha_hora' => now()->subDay()]);
            try {
                Diagnostico::create(['cod_residente' => $otro->getKey(), 'cod_atencion' => $atencion->getKey(),
                    'cod_personal' => $this->autor->getKey(), 'nombre' => 'Referencia cruzada', 'estado' => 'ACTIVO', 'fecha_hora' => now()->subDay()]);
                self::fail('La BDD debe rechazar la referencia cruzada.');
            } catch (QueryException $e) {
                self::assertStringContainsString('La atención no corresponde al residente', $e->getMessage());
            }
        }
        $consulta = app(PanoramaResidentes::class);
        $dto = $consulta->consultar($this->usuario, '', 'diagnosticos', null);
        self::assertCount(1, $dto['filas']);
        self::assertSame('Diagnóstico CERRADA', $dto['filas'][0]['etiqueta']);
        self::assertSame(50.0, $dto['filas'][0]['porcentaje']);
        $detalle = $consulta->detalle($this->usuario, $residente, 'diagnosticos');
        self::assertCount(1, $detalle);
        self::assertSame('Autora Sintética', $detalle[0]['datos']['Profesional']);
        $this->assertDatabaseCount('diagnosticos', 2);
    }
}
