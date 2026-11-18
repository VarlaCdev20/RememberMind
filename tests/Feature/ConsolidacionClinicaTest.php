<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\Area;
use App\Models\MedicionAntropometrica;
use App\Models\Personal;
use App\Models\RegistroSueno;
use App\Models\RegistroMovilidad;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use App\Models\ValoracionNutricional;
use App\Models\ValoracionPetersenOriginal;
use App\Models\ValoracionPsicologica;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConsolidacionClinicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_reversion_reaplicacion_y_referencias_por_tipo_con_datos(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create();
        $usuario->assignRole('ENFERMEROS');
        $residente = Residente::factory()->create();
        $personal = Personal::create(['cod_usuario' => $usuario->getKey(), 'nombres' => 'Marta', 'apellido_paterno' => 'Ejemplo',
            'numero_documento' => 'TEST-001', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $base = ['cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey()];
        $area = Area::create(['nombre' => 'Área de prueba', 'estado' => 'ACTIVA']);
        $atencion = Atencion::create($base + ['cod_area' => $area->getKey(), 'tipo_atencion' => 'CONSULTA', 'fecha_hora' => now(), 'estado' => 'FINALIZADA']);
        $fecha = ['fecha_hora' => '2026-10-01 10:15:00'];
        $valoracion = $base + $fecha + ['cod_atencion' => $atencion->getKey(), 'estado' => 'VIGENTE'];
        $sueno = RegistroSueno::create($base + ['fecha' => '2026-09-30', 'despertares' => 0, 'insomnio' => false, 'estado' => 'VIGENTE']);
        $movilidad = RegistroMovilidad::create($base + $fecha + ['tolerancia_movilidad' => 'BUENA', 'estado' => 'VIGENTE']);
        $signo = SignoVital::create($base + $fecha + ['temperatura' => 36.8]);
        $antropometria = MedicionAntropometrica::create($base + $fecha + ['talla' => 170.5]);
        $psicologica = ValoracionPsicologica::create($valoracion + ['conclusion' => 'Registro sintético']);
        $nutricional = ValoracionNutricional::create($valoracion + ['cod_medicion' => $antropometria->getKey()]);
        $original = ValoracionPetersenOriginal::create($base + $fecha + [
            'cod_usuario_registro' => $usuario->getKey(), 'solicitud_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'registrado_en' => now(), 'tipo_registro' => 'REGISTRO', 'elemento' => 'C01', 'estado_original' => 'S',
            'justificacion' => 'Contexto sintético',
        ]);
        $soporte = ['cod_soporte_petersen' => 'SVP-SINTETICO', 'cod_valoracion_petersen' => $original->getKey(),
            'tipo_soporte' => 'HISTORIA_PREVIA', 'orden' => 0];
        DB::table('soportes_valoracion_petersen')->insert($soporte + ['cod_valoracion_psicologica' => $psicologica->getKey()]);
        try {
            DB::transaction(fn () => DB::table('soportes_valoracion_petersen')->insert(array_replace($soporte, [
                'cod_soporte_petersen' => 'SVP-INVALIDO', 'orden' => 1, 'cod_valoracion_psicologica' => $nutricional->getKey(),
            ])));
            $this->fail('Se aceptó una valoración nutricional como evidencia psicológica.');
        } catch (QueryException $e) {
            $this->assertContains($e->getCode(), ['23000', '23503']);
        }
        $this->assertNull(SignoVital::find($antropometria->getKey()));
        $this->assertNull(ValoracionPsicologica::find($nutricional->getKey()));
        try {
            DB::transaction(fn () => DB::table('valoraciones_profesionales')->where('cod_valoracion_profesional', $nutricional->getKey())
                ->update(['cod_medicion' => $signo->getKey()]));
            $this->fail('Se aceptó un signo vital como medición antropométrica.');
        } catch (QueryException $e) {
            $this->assertContains($e->getCode(), ['23000', '23503']);
        }

        $reparto = require database_path('migrations/2026_11_18_000003_partition_professional_assessments.php');
        $reparto->down();
        $separacion = require database_path('migrations/2026_11_18_000001_separate_clinical_assessments.php');
        $separacion->down();
        $controles = require database_path('migrations/2026_11_17_000002_consolidate_controles_observaciones.php');
        $controles->down();
        $pases = require database_path('migrations/2026_11_17_000001_consolidate_pases_textos.php');
        $pases->down();
        $posteriores = array_map(static fn ($ruta) => require $ruta, glob(database_path('migrations/2026_11_16_*.php')));
        foreach (array_reverse($posteriores) as $posterior) {
            $posterior->down();
        }
        $migraciones = array_map(static fn ($ruta) => require $ruta, glob(database_path('migrations/2026_11_14_00000[678]*.php')));
        foreach (array_reverse($migraciones) as $migracion) {
            $migracion->down();
        }
        try {
            $this->assertDatabaseHas('registros_sueno', ['cod_registro_sueno' => $sueno->getKey(), 'fecha' => '2026-09-30', 'despertares' => 0]);
            $this->assertDatabaseHas('signos_vitales', ['cod_signo' => $signo->getKey(), 'temperatura' => 36.8]);
            $this->assertDatabaseHas('valoraciones_nutricionales', ['cod_valoracion_nutricional' => $nutricional->getKey(), 'cod_medicion' => $antropometria->getKey()]);
        } finally {
            foreach ($migraciones as $migracion) {
                $migracion->up();
            }
        }
        foreach ($posteriores as $posterior) {
            $posterior->up();
        }
        $pases->up();
        $controles->up();
        $separacion->up();
        $reparto->up();
        $this->assertSame('2026-09-30', $sueno->fresh()->fecha->toDateString());
        $this->assertFalse($sueno->fresh()->insomnio);
        $this->assertSame('BUENA', $movilidad->fresh()->tolerancia_movilidad);
        $this->assertSame('Registro sintético', $psicologica->fresh()->conclusion);
        $this->assertSame($antropometria->getKey(), $nutricional->fresh()->cod_medicion);
    }
}
