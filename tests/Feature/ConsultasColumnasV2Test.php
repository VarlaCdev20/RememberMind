<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\PaseTurno;
use App\Models\Residente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConsultasColumnasV2Test extends TestCase
{
    use RefreshDatabase;

    public function test_pases_de_turno_se_consultan_por_codigos_de_personal_v2(): void
    {
        $this->crearUsuarioPersonal('USU_SALIENTE', 'PER_SALIENTE');
        $this->crearUsuarioPersonal('USU_ENTRANTE', 'PER_ENTRANTE');
        $this->crearResidente('RES_PASE', 'Zuluaga');

        DB::table('turnos')->insert([
            ['cod_turno' => 'TUR_SALIENTE', 'nombre' => 'Saliente', 'hora_inicio' => '07:00', 'hora_cierre' => '15:00', 'orden' => 1, 'estado' => 'ACTIVO'],
            ['cod_turno' => 'TUR_ENTRANTE', 'nombre' => 'Entrante', 'hora_inicio' => '15:00', 'hora_cierre' => '23:00', 'orden' => 2, 'estado' => 'ACTIVO'],
        ]);
        DB::table('jornadas')->insert([
            ['cod_jornada' => 'JOR_SALIENTE', 'cod_turno' => 'TUR_SALIENTE', 'cod_usuario_apertura' => 'USU_SALIENTE', 'fecha_jornada' => today(), 'estado' => 'CERRADA'],
            ['cod_jornada' => 'JOR_ENTRANTE', 'cod_turno' => 'TUR_ENTRANTE', 'cod_usuario_apertura' => 'USU_ENTRANTE', 'fecha_jornada' => today(), 'estado' => 'ABIERTA'],
        ]);
        DB::table('pases_turno')->insert([
            'cod_pase' => 'PAS_REGRESION',
            'cod_residente' => 'RES_PASE',
            'cod_jornada_saliente' => 'JOR_SALIENTE',
            'cod_jornada_entrante' => 'JOR_ENTRANTE',
            'cod_personal_saliente' => 'PER_SALIENTE',
            'cod_personal_entrante' => 'PER_ENTRANTE',
            'fecha_hora' => now(),
            'resumen' => 'Entrega de turno',
            'estado' => 'GENERADO',
        ]);

        $porSaliente = PaseTurno::query()->where('cod_personal_saliente', 'PER_SALIENTE')->first();
        $porEntrante = PaseTurno::query()->where('cod_personal_entrante', 'PER_ENTRANTE')->first();

        $this->assertSame('PAS_REGRESION', $porSaliente?->cod_pase);
        $this->assertSame('PAS_REGRESION', $porEntrante?->cod_pase);
    }

    public function test_alertas_criticas_se_consultan_por_prioridad(): void
    {
        $this->crearResidente('RES_ALERTA', 'Mamani');
        DB::table('alertas')->insert([
            'cod_alerta' => 'ALA_REGRESION',
            'cod_residente' => 'RES_ALERTA',
            'tipo' => 'CLINICA',
            'prioridad' => 'CRITICO',
            'titulo' => 'Alerta crítica',
            'descripcion' => 'Prueba de consulta física V2',
            'fecha_hora' => now(),
            'generacion' => 'MANUAL',
            'estado' => 'ABIERTA',
        ]);

        $this->assertSame('ALA_REGRESION', Alerta::query()->criticas()->value('cod_alerta'));
    }

    public function test_residentes_se_ordenan_por_apellido_paterno(): void
    {
        $this->crearResidente('RES_Z', 'Zuluaga');
        $this->crearResidente('RES_A', 'Achá');

        $this->assertSame(
            ['RES_A', 'RES_Z'],
            Residente::query()->orderBy('apellido_paterno')->pluck('cod_residente')->all(),
        );
    }

    private function crearUsuarioPersonal(string $codUsuario, string $codPersonal): void
    {
        DB::table('usuarios')->insert([
            'cod_usuario' => $codUsuario,
            'correo' => strtolower($codUsuario).'@example.test',
            'contrasena' => bcrypt('password'),
            'estado' => 'ACTIVO',
        ]);
        DB::table('personal')->insert([
            'cod_personal' => $codPersonal,
            'cod_usuario' => $codUsuario,
            'nombres' => 'Prueba',
            'apellido_paterno' => 'Enfermería',
            'numero_documento' => $codPersonal,
            'profesion' => 'ENFERMERO',
            'estado' => 'ACTIVO',
        ]);
    }

    private function crearResidente(string $codigo, string $apellido): void
    {
        DB::table('residentes')->insert([
            'cod_residente' => $codigo,
            'nombres' => 'Residente',
            'apellido_paterno' => $apellido,
            'fecha_nacimiento' => '1940-01-01',
            'estado' => 'ADMITIDO',
        ]);
    }
}
