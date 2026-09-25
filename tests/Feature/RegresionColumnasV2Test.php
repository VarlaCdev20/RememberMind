<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\PaseTurno;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Jornada;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegresionColumnasV2Test extends TestCase
{
    use RefreshDatabase;

    public function test_pase_turno_consulta_por_cod_personal_saliente_y_entrante_sin_columnas_legacy(): void
    {
        $turno = Turno::create([
            'cod_turno' => 'TUR_TEST',
            'nombre' => 'Turno Prueba',
            'hora_inicio' => '08:00:00',
            'hora_cierre' => '16:00:00',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);

        $jornada = Jornada::create([
            'cod_jornada' => 'JOR_TEST',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ACTIVA',
        ]);

        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_TEST',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'López',
            'fecha_nacimiento' => '1950-01-01',
            'estado' => 'ACTIVO',
        ]);

        $user1 = User::factory()->create();
        $personalSaliente = Personal::create([
            'cod_personal' => 'PER_SAL',
            'cod_usuario' => $user1->cod_usuario,
            'nombres' => 'Saliente',
            'apellido_paterno' => 'Uno',
            'numero_documento' => '11223344',
            'profesion' => 'ENFERMERO',
            'estado' => 'ACTIVO',
        ]);

        $user2 = User::factory()->create();
        $personalEntrante = Personal::create([
            'cod_personal' => 'PER_ENT',
            'cod_usuario' => $user2->cod_usuario,
            'nombres' => 'Entrante',
            'apellido_paterno' => 'Dos',
            'numero_documento' => '55667788',
            'profesion' => 'ENFERMERO',
            'estado' => 'ACTIVO',
        ]);

        $pase = PaseTurno::create([
            'cod_pase' => 'PAS_001',
            'cod_residente' => $residente->cod_residente,
            'cod_jornada_saliente' => $jornada->cod_jornada,
            'cod_jornada_entrante' => $jornada->cod_jornada,
            'cod_personal_saliente' => $personalSaliente->cod_personal,
            'cod_personal_entrante' => $personalEntrante->cod_personal,
            'fecha_hora' => now(),
            'resumen' => 'Relevo sin novedades',
            'estado' => 'GENERADO',
        ]);

        // Consulta real por personal saliente
        $pasesSaliente = PaseTurno::query()
            ->where('cod_personal_saliente', $personalSaliente->cod_personal)
            ->get();
        $this->assertTrue($pasesSaliente->contains('cod_pase', 'PAS_001'));

        // Consulta real por personal entrante
        $pasesEntrante = PaseTurno::query()
            ->where('cod_personal_entrante', $personalEntrante->cod_personal)
            ->get();
        $this->assertTrue($pasesEntrante->contains('cod_pase', 'PAS_001'));

        // Consulta combinada usando orWhere sin columnas legacy
        $pasesFiltro = PaseTurno::query()
            ->where(function ($q) use ($personalSaliente) {
                $q->where('cod_personal_saliente', $personalSaliente->cod_personal)
                  ->orWhere('cod_personal_entrante', $personalSaliente->cod_personal);
            })
            ->get();
        $this->assertCount(1, $pasesFiltro);
    }

    public function test_alertas_consulta_por_prioridad_sin_columna_nivel(): void
    {
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_ALA',
            'nombres' => 'Alerta',
            'apellido_paterno' => 'Gómez',
            'fecha_nacimiento' => '1955-05-05',
            'estado' => 'ACTIVO',
        ]);

        $alertaCritica = Alerta::create([
            'cod_alerta' => 'ALA_CRITICA',
            'cod_residente' => $residente->cod_residente,
            'tipo' => 'CLINICA',
            'prioridad' => 'CRITICO',
            'modulo' => 'ENFERMERIA',
            'titulo' => 'Signo vital elevado',
            'descripcion' => 'Presión sistólica superior a 180',
            'fecha_hora' => now(),
            'generacion' => 'MANUAL',
            'estado' => 'ABIERTA',
        ]);

        $alertaMedia = Alerta::create([
            'cod_alerta' => 'ALA_MEDIA',
            'cod_residente' => $residente->cod_residente,
            'tipo' => 'CLINICA',
            'prioridad' => 'MEDIO',
            'modulo' => 'ENFERMERIA',
            'titulo' => 'Observación general',
            'descripcion' => 'Apetito levemente disminuido',
            'fecha_hora' => now(),
            'generacion' => 'MANUAL',
            'estado' => 'ABIERTA',
        ]);

        // Consulta SQL real por prioridad CRITICO
        $criticas = Alerta::whereIn('prioridad', ['CRITICO', 'CRITICA'])
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
            ->get();

        $this->assertCount(1, $criticas);
        $this->assertEquals('ALA_CRITICA', $criticas->first()->cod_alerta);

        // Agrupación y conteo por prioridad
        $conteo = Alerta::select('prioridad')
            ->groupBy('prioridad')
            ->pluck('prioridad')
            ->toArray();

        $this->assertContains('CRITICO', $conteo);
        $this->assertContains('MEDIO', $conteo);
    }

    public function test_residentes_ordenamiento_por_apellido_paterno_y_materno(): void
    {
        Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_ORD_B',
            'nombres' => 'Beatriz',
            'apellido_paterno' => 'Zeballos',
            'apellido_materno' => 'Alarcón',
            'fecha_nacimiento' => '1945-02-10',
            'estado' => 'ACTIVO',
        ]);

        Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_ORD_A',
            'nombres' => 'Alberto',
            'apellido_paterno' => 'Alvarez',
            'apellido_materno' => 'Barrientos',
            'fecha_nacimiento' => '1942-03-15',
            'estado' => 'ACTIVO',
        ]);

        // Consulta real ordenada por apellido_paterno
        $ordenados = Residente::query()
            ->whereIn('cod_residente', ['RES_ORD_A', 'RES_ORD_B'])
            ->orderBy('apellido_paterno')
            ->pluck('cod_residente')
            ->all();

        $this->assertEquals('RES_ORD_A', $ordenados[0]);
        $this->assertEquals('RES_ORD_B', $ordenados[1]);
    }
}