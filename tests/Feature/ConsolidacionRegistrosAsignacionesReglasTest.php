<?php

namespace Tests\Feature;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos;

use App\Models\Alergia;
use App\Models\AntecedenteClinico;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\Diagnostico;
use App\Models\DispositivoClinico;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios;
use Tests\TestCase;
use Tests\Unit\SistemaExperto\Infrastructure\Mappers\EscenarioMapeadores;

class ConsolidacionRegistrosAsignacionesReglasTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_tipos_y_la_reversion_conservan_registros_y_referencias(): void
    {
        $usuario = User::factory()->create();
        $persona = Personal::create(['cod_usuario' => $usuario->getKey(), 'nombres' => 'Persona', 'apellido_paterno' => 'Sintética',
            'numero_documento' => 'FUSION-01', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $residente = Residente::factory()->create();
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '07:00', 'hora_cierre' => '15:00', 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'cod_usuario_apertura' => $usuario->getKey(),
            'fecha_jornada' => '2026-11-13', 'estado' => 'ABIERTA']);
        $comun = ['cod_residente' => $residente->getKey(), 'cod_personal' => $persona->getKey(), 'estado' => 'ACTIVO'];
        $fecha = ['fecha_hora' => '2026-11-13 09:15:00'];
        $atencion = Atencion::create($comun + $fecha + ['cod_area' => $area->getKey(), 'tipo_atencion' => 'CONSULTA']);
        $antecedente = AntecedenteClinico::create($comun + ['tipo_antecedente' => 'PERSONAL',
            'descripcion' => 'Contexto sintético', 'fecha_referencia' => '2026-11-12', 'fuente_informacion' => 'ENTREVISTA']);
        $diagnostico = Diagnostico::create($comun + $fecha + ['cod_atencion' => $atencion->getKey(), 'codigo_clinico' => 'SINTETICO',
            'nombre' => 'Registro sintético', 'certeza' => 'PENDIENTE', 'tipo' => 'DIFERENCIAL']);
        $alergia = Alergia::create($comun + $fecha + ['sustancia' => 'Sustancia sintética', 'tipo' => 'OTRA',
            'reaccion' => 'Reacción ficticia', 'gravedad' => 'LEVE']);
        $dispositivo = DispositivoClinico::create($comun + ['tipo' => 'APOYO', 'descripcion' => 'Dispositivo sintético',
            'ubicacion' => 'Registro ficticio', 'fecha_colocacion' => '2026-11-11 10:00:00', 'fecha_retiro' => '2026-11-13 10:10:00']);

        $jornadaPersonal = ['cod_jornada' => $jornada->getKey(), 'cod_personal' => $persona->getKey(), 'estado' => 'ACTIVA'];
        $asignacionPersonal = AsignacionPersonal::create($jornadaPersonal + ['cod_area' => $area->getKey(), 'funcion' => 'CUIDADO']);
        $asignacionResidente = AsignacionResidenteJornada::create($jornadaPersonal + $fecha + [
            'cod_residente' => $residente->getKey(), 'nivel_supervision' => 'MINIMO',
        ]);
        $original = ValoracionPetersenOriginal::create(array_diff_key($comun, ['estado' => true]) + $fecha + [
            'cod_usuario_registro' => $usuario->getKey(), 'solicitud_uuid' => (string) Str::uuid(),
            'registrado_en' => now(), 'tipo_registro' => 'REGISTRO', 'elemento' => 'C01', 'estado_original' => 'S',
            'justificacion' => 'Contexto sintético',
        ]);
        $soporte = ['cod_valoracion_petersen' => $original->getKey(), 'tipo_soporte' => 'HISTORIA_PREVIA'];
        DB::table('soportes_valoracion_petersen')->insert($soporte + [
            'cod_soporte_petersen' => 'SVP-ANT', 'orden' => 0, 'cod_antecedente' => $antecedente->getKey(),
        ]);
        DB::table('soportes_valoracion_petersen')->insert($soporte + [
            'cod_soporte_petersen' => 'SVP-DIA', 'orden' => 1, 'cod_diagnostico' => $diagnostico->getKey(),
        ]);
        $this->rechazar(fn () => DB::table('soportes_valoracion_petersen')->insert($soporte + [
            'cod_soporte_petersen' => 'SVP-MAL', 'orden' => 2, 'cod_diagnostico' => $alergia->getKey(),
        ]));
        $this->rechazar(fn () => DB::table('registros_clinicos')->where('cod_registro_clinico', $alergia->getKey())
            ->update(['cod_atencion' => $atencion->getKey()]));
        $this->rechazar(fn () => AsignacionPersonal::create($jornadaPersonal + ['cod_area' => $area->getKey()]));
        $this->rechazar(fn () => AsignacionResidenteJornada::create($jornadaPersonal + ['cod_residente' => $residente->getKey()]));
        $this->assertNull(AsignacionResidenteJornada::find($asignacionPersonal->getKey()));
        $this->assertNull(AntecedenteClinico::find($diagnostico->getKey()));

        $historia = require database_path('propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php');
        $historia->up();
        $evento = ['cod_evento_asignacion' => 'EV-SINTETICO', 'numero_evento' => 1, 'tipo_evento' => 'ALTA',
            'estado_nuevo' => 'ACTIVA', 'fecha_hora_efecto' => now(), 'fecha_hora_registro' => now(),
            'cod_usuario_registro' => $usuario->getKey(), 'motivo' => 'Alta sintética'];
        $this->rechazar(fn () => DB::table('eventos_asignacion_personal')->insert($evento + [
            'cod_asignacion_personal' => $asignacionResidente->getKey(),
        ]));
        DB::table('eventos_asignacion_personal')->insert($evento + ['cod_asignacion_personal' => $asignacionPersonal->getKey()]);
        EscenarioRepositorios::preparar();
        EscenarioRepositorios::guardarGrafo(EscenarioMapeadores::regla());
        DB::table('evaluaciones_expertas')->insert([
            'cod_evaluacion_experta' => 'SIM-EVAL', 'cod_residente' => 'SINTETICO-RES', 'cod_version_modelo' => 'SIM-V1',
            'origen_activacion' => 'PRUEBA_TECNICA', 'fecha_hora_inicio' => $fecha['fecha_hora'], 'estado_ejecucion' => 'PRUEBA_FINALIZADA',
        ]);
        DB::table('evaluacion_criterios')->insert([
            'cod_evaluacion_criterio' => 'FUSION-CRITERIO', 'cod_evaluacion_experta' => 'SIM-EVAL',
            'cod_nodo_criterio' => DB::table('nodos_semanticos')->whereNotNull('cod_criterio_dominio_resultado')->value('cod_nodo_semantico'),
            'estado_evaluabilidad' => 'EVALUABLE', 'fecha_hora_determinacion' => $fecha['fecha_hora'],
            'cod_traza_inferencia' => 'FUSION-TRAZA', 'traza_estado_inferencia' => 'FINALIZADA',
            'traza_fecha_hora_inicio' => $fecha['fecha_hora'],
        ]);
        BloquesExpertos::insertar('evaluaciones_reglas', [
            'cod_evaluacion_regla' => 'FUSION-EVAL-REGLA', 'cod_traza_inferencia' => 'FUSION-TRAZA', 'cod_regla_experta' => 'SIM-R',
            'estado_regla' => 'ACTIVA', 'fecha_hora_evaluacion' => $fecha['fecha_hora'],
        ]);
        $condicion = DB::table('componentes_regla_experta')->where('tipo_evento', 'CONDICION')->orderBy('cod_componente_regla')->value('cod_componente_regla');
        $evaluacionCondicion = [
            'cod_evaluacion_condicion' => 'FUSION-EVAL-COND', 'cod_evaluacion_regla' => 'FUSION-EVAL-REGLA',
            'estado_condicion' => 'CUMPLE', 'fecha_hora_evaluacion' => $fecha['fecha_hora'],
        ];
        $this->rechazar(fn () => BloquesExpertos::insertar('evaluaciones_condiciones_regla', $evaluacionCondicion + ['cod_condicion_regla' => 'SIM-CONS']));
        BloquesExpertos::insertar('evaluaciones_condiciones_regla', $evaluacionCondicion + ['cod_condicion_regla' => $condicion]);

        $regla = \App\Models\EvaluacionRegla::query()->with('evaluacionesCondicionesRegla')->findOrFail('FUSION-EVAL-REGLA');
        $condicionEvaluada = $regla->evaluacionesCondicionesRegla->sole();
        $this->assertSame('FUSION-EVAL-COND', $condicionEvaluada->getKey());
        $this->assertSame($regla->getKey(), $condicionEvaluada->evaluacionRegla->getKey());
        $this->assertNull(\App\Models\EvaluacionRegla::find($condicionEvaluada->getKey()));
        $this->rechazar(fn () => DB::table('evaluaciones_reglas')->where('cod_evaluacion_condicion', $condicionEvaluada->getKey())
            ->update(['cod_evaluacion_regla_padre' => $condicionEvaluada->getKey()]));
        $this->rechazar(fn () => DB::table('evaluaciones_reglas')->where('cod_evaluacion_condicion', $condicionEvaluada->getKey())
            ->update(['estado_regla' => 'CUMPLE']));

        $evaluaciones = require database_path('migrations/2026_11_17_000003_consolidate_evaluaciones_reglas.php');
        $evaluaciones->down();
        $migraciones = array_map(static fn ($ruta) => require $ruta, glob(database_path('migrations/2026_11_14_00001[123]*.php')));
        foreach (array_reverse($migraciones) as $migracion) {
            $migracion->down();
        }
        try {
            $this->assertDatabaseHas('antecedentes_clinicos', ['cod_antecedente' => $antecedente->getKey(), 'fuente_informacion' => 'ENTREVISTA']);
            $this->assertDatabaseHas('diagnosticos', ['cod_diagnostico' => $diagnostico->getKey(), 'certeza' => 'PENDIENTE']);
            $this->assertDatabaseHas('alergias', ['cod_alergia' => $alergia->getKey(), 'reaccion' => 'Reacción ficticia']);
            $this->assertDatabaseHas('dispositivos_clinicos', ['cod_dispositivo' => $dispositivo->getKey(), 'ubicacion' => 'Registro ficticio']);
            $this->assertDatabaseHas('asignaciones_personal', ['cod_asignacion_personal' => $asignacionPersonal->getKey(), 'funcion' => 'CUIDADO']);
            $this->assertDatabaseHas('asignaciones_residente_jornada', ['cod_asignacion' => $asignacionResidente->getKey(), 'nivel_supervision' => 'MINIMO']);
        } finally {
            foreach ($migraciones as $migracion) {
                $migracion->up();
            }
        }

        $evaluaciones->up();
        $this->assertSame('PENDIENTE', $diagnostico->fresh()->certeza);
        $this->assertSame('MINIMO', $asignacionResidente->fresh()->nivel_supervision);
        $this->assertDatabaseHas('eventos_asignacion_personal', ['cod_evento_asignacion' => 'EV-SINTETICO',
            'cod_asignacion_personal' => $asignacionPersonal->getKey()]);
        $this->assertDatabaseHas('soportes_valoracion_petersen', ['cod_soporte_petersen' => 'SVP-DIA', 'cod_diagnostico' => $diagnostico->getKey()]);
        $this->assertTrue(BloquesExpertos::consulta('evaluaciones_condiciones_regla')->where($evaluacionCondicion + ['cod_condicion_regla' => $condicion])->exists());
        $this->assertFalse(Schema::hasTable('diagnosticos'));
    }

    private function rechazar(callable $operacion): void
    {
        try {
            DB::transaction($operacion);
            $this->fail('La restricción aceptó un registro inválido.');
        } catch (QueryException $error) {
            $this->assertContains($error->getCode(), ['23000', '23503', '23505', '23514']);
        }
    }
}
