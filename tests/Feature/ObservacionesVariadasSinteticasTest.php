<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarHallazgosObservacionales;
use App\Modulos\Clinica\Aplicacion\Acciones\RegistrarExpedienteClinico;
use App\Modulos\Cuidados\Aplicacion\Acciones\RegistrarObservacionCognitiva;
use App\Modulos\Cuidados\Dominio\ObservacionCognitiva as Contrato;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\ObservacionesVariadasSinteticas as Casos;
use Tests\TestCase;

final class ObservacionesVariadasSinteticasTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_veinte_episodios_ficticios_con_fechas_autoria_y_hallazgos_reales_del_motor(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $u->assignRole('MEDICO GENERAL/GERIATRA');
        $p = Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional sintético', 'apellido_paterno' => 'OBS-20',
            'numero_documento' => 'OBS-20-FICTICIO', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $r = Residente::factory()->create(['nombres' => 'Residente ficticio', 'apellido_paterno' => 'OBS-20', 'estado' => 'ADMITIDO']);
        $area = Area::create(['nombre' => 'Área sintética OBS-20', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno de ensayo OBS-20', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'estado' => 'ACTIVO']);
        $this->actingAs($u);
        foreach (Casos::registros() as $caso) {
            self::assertTrue($caso['sintetico']);
            self::assertStringNotContainsString('COD_RES_', $caso['alias']);
            $this->travelTo(CarbonImmutable::parse($caso['fecha_hora']));
            $j = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
            AsignacionPersonal::create(['cod_personal' => $p->getKey(), 'cod_jornada' => $j->getKey(), 'cod_area' => $area->getKey(),
                'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
            $atencion = app(RegistrarExpedienteClinico::class)->crearAtencion($u,
                ['cod_area' => $area->getKey(), 'tipo_atencion' => 'SEGUIMIENTO', 'motivo' => Casos::MARCA], $r);
            $registro = app(RegistrarObservacionCognitiva::class)->ejecutar($u, $r, $caso['datos'] + ['cod_atencion' => $atencion->getKey()]);
            self::assertSame($caso['fecha_hora'], $registro->fecha_hora->format('Y-m-d H:i:s'));
            self::assertSame($p->getKey(), $registro->cod_personal);
            self::assertSame($j->getKey(), $registro->cod_jornada);
            self::assertSame($r->getKey(), $registro->cod_residente);
            foreach ($caso['datos'] as $campo => $valor) {
                self::assertSame($valor, $registro->$campo, $campo);
                if (in_array($campo, Contrato::TEXTOS, true) && $valor !== null) {
                    self::assertArrayHasKey($valor, Contrato::opciones($campo));
                }
            }
        }
        self::assertDatabaseCount('controles_cognitivos', 20);
        self::assertSame(20, ControlCognitivo::query()->distinct()->count('fecha_hora'));
        self::assertSame(20, Atencion::query()->count());
        $escrituras = [];
        DB::listen(static function ($q) use (&$escrituras): void {
            if (preg_match('/^\s*(insert|update|delete|replace|alter|create|drop)\b/i', $q->sql)) {
                $escrituras[] = $q->sql;
            }
        });
        $informe = app(ConsultarHallazgosObservacionales::class)->instantaneaCompleta($u, $r, CarbonImmutable::now());
        self::assertCount(20, $informe['instantanea']);
        self::assertCount(60, $informe['resultados']);
        $repeticion = array_values(array_filter($informe['resultados'], fn ($d) => $d['regla'] === 'OBS-MEM-01'));
        self::assertSame(4, count(array_filter($repeticion, fn ($d) => $d['estado'] === 'CUMPLE')));
        self::assertSame(2, count(array_filter($repeticion, fn ($d) => $d['estado'] === 'NO_CUMPLE')));
        self::assertSame(14, count(array_filter($repeticion, fn ($d) => $d['estado'] === 'NO_EVALUABLE')));
        $olvido = array_filter($informe['resultados'], fn ($d) => $d['regla'] === 'OBS-MEM-02' && $d['estado'] === 'CUMPLE');
        self::assertCount(5, $olvido);
        self::assertSame([], $escrituras);
        self::assertFalse($informe['persistido']);
        self::assertArrayNotHasKey('patron', $informe);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
        self::assertDatabaseCount('diagnosticos', 0);
        foreach ($informe['resultados'] as $salida) {
            self::assertSame($p->getKey(), $salida['fuente']['autor']);
            self::assertSame($r->getKey(), $salida['fuente']['residente']);
            self::assertNotNull($salida['fuente']['episodio']);
        }
    }
}
