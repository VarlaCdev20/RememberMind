<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use MotorExperto\dominio\conocimiento\EditorPaqueteExperimental;
use MotorExperto\investigacion\g09\PaqueteSemanticoG09Experimental;
use Tests\TestCase;

final class ConsolaPerfilExperimentalTest extends TestCase
{
    use RefreshDatabase;

    public function test_comando_calcula_cinco_dominios_y_petersen_sin_escrituras_institucionales(): void
    {
        $escrituras = [];
        DB::listen(static function ($q) use (&$escrituras): void {
            if (preg_match('/^\s*(insert|update|delete|alter|create|drop)\b/i', $q->sql)) {
                $escrituras[] = $q->sql;
            }
        });
        $this->artisan('experto:probar-perfil', ['--escenario' => 'memoria'])->expectsOutputToContain('PET-01')->assertSuccessful();
        $this->artisan('experto:probar-perfil', ['--escenario' => 'sin-referencias'])
            ->expectsOutputToContain('ABSTENCION_INFORMACION_INSUFICIENTE')->assertSuccessful();
        self::assertSame([], $escrituras);
    }

    public function test_produccion_e_identificador_de_residente_no_habilitan_la_ejecucion(): void
    {
        $this->artisan('experto:probar-perfil', ['--escenario' => 'COD_RES_000030'])->assertExitCode(2);
        $this->app->instance('env', 'production');
        $this->artisan('experto:probar-perfil')->assertFailed();
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
    }

    public function test_escenarios_incompletos_y_conflictivos_se_calculan_y_exportan_con_procedencia(): void
    {
        $salidas = [];
        foreach (['incompleto', 'contradictorio', 'diferencial', 'iadl-incompleta'] as $escenario) {
            self::assertSame(0, Artisan::call('experto:probar-perfil', ['--escenario' => $escenario, '--json' => true]));
            $salidas[$escenario] = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
            self::assertCount(19, $salidas[$escenario]['subcomponentes']);
            self::assertSame('SIMULACIÓN TÉCNICA / NO VÁLIDO PARA DECISIONES ASISTENCIALES', $salidas[$escenario]['advertencia']);
            self::assertFalse($salidas[$escenario]['persistido']);
        }
        self::assertSame('EV-CM-1', $salidas['incompleto']['perfil_cinco_dominios']['COG-MEM']['salida']['evaluabilidad']['estado']);
        self::assertSame('null', $salidas['incompleto']['hechos_instrumentales'][0]['valores_originales'][0]['tipo']);
        self::assertSame(['COG-ATE'], $salidas['contradictorio']['contradicciones']);
        self::assertSame('ABSTENCION_EVALUACION_DIFERENCIAL', $salidas['diferencial']['petersen']['estado']);
        self::assertSame('U', $salidas['iadl-incompleta']['requisitos_dcl']['DCL-C03']);
        self::assertNull($salidas['iadl-incompleta']['petersen']['patron_candidato']);
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
        $this->artisan('experto:probar-perfil', ['--escenario' => 'incompleto'])->expectsOutputToContain('EV-CM-1')->assertSuccessful();
    }

    public function test_consola_ejecuta_una_revision_g09_de_archivo_y_rechaza_contrato_incompleto(): void
    {
        $p = PaqueteSemanticoG09Experimental::crear();
        $p = (new EditorPaqueteExperimental)->revisar($p, 'SIM-CLI-REV-2', [], ['R-ATE-S01-P01']);
        $archivo = tempnam(sys_get_temp_dir(), 'rm-perfil-');
        self::assertNotFalse($archivo);
        try {
            file_put_contents($archivo, json_encode($p->declaracion(), JSON_THROW_ON_ERROR));
            self::assertSame(0, Artisan::call('experto:probar-perfil', ['--conocimiento-g09' => $archivo, '--json' => true]));
            $r = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
            self::assertSame('SIM-CLI-REV-2', $r['conocimiento_integrado']['fuentes']['G09']['version']);
            self::assertSame($r['manifiesto_versiones']['COG-MEM'], $r['manifiesto_versiones']['G09']);
            self::assertSame('SIN_REGLA_ELEGIBLE', $r['subcomponentes']['ATE-S01']['motivo_no_emision']);
            self::assertArrayNotHasKey('R-ATE-S01-P01', $r['reglas_examinadas']['g09']);
            file_put_contents($archivo, '{"solo_pruebas_tecnicas":true}');
            $this->artisan('experto:probar-perfil', ['--conocimiento-g09' => $archivo])->assertFailed();
        } finally {
            unlink($archivo);
        }
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
    }
}
