<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionResidenteJornada;
use App\Models\Cama;
use App\Models\Contacto;
use App\Models\ControlCognitivo;
use App\Models\Habitacion;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Preadmision;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Admisiones\Aplicacion\Acciones\FormalizarAdmision;
use Database\Seeders\EscenariosCognitivosDesarrolloSeeder as Carga;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

/** Datos ficticios en base desechable: alcance, conservación, repetición y rollback real. */
final class EscenariosCognitivosDesarrolloTest extends TestCase
{
    use RefreshDatabase;

    private array $entrada;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 17)->setTime(12, 0));
    }

    private function preparar(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create(['estado' => 'ACTIVO']);
        $admin->assignRole('ADMINISTRADOR');
        $admin->givePermissionTo('admisiones.formalizar');
        $personas = [];
        foreach (['ENFERMEROS' => 'ENFERMERIA', 'MEDICO GENERAL/GERIATRA' => 'MEDICINA', 'FISIOTERAPEUTA' => 'FISIOTERAPIA'] as $rol => $profesion) {
            $usuario = User::factory()->create(['estado' => 'ACTIVO']);
            $usuario->assignRole($rol);
            $personas[$profesion] = Personal::create(['cod_usuario' => $usuario->getKey(), 'nombres' => 'Profesional sintético',
                'apellido_paterno' => $profesion, 'numero_documento' => 'SIM-'.$profesion, 'profesion' => $profesion, 'estado' => 'ACTIVO']);
        }
        $area = Area::create(['nombre' => 'Medicina de prueba', 'estado' => 'ACTIVA']);
        $areaFuncional = Area::create(['nombre' => 'Fisioterapia de prueba', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Sintético', 'hora_inicio' => '00:00:00', 'hora_cierre' => '23:59:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => now()->toDateString(), 'estado' => 'ABIERTA']);
        $hab = Habitacion::create(['codigo' => 'SIM-ESCENARIOS', 'capacidad' => 5, 'estado' => 'ACTIVA']);
        foreach (array_keys(Carga::CASOS) as $documento) {
            $contacto = Contacto::create(['nombres' => 'Contacto sintético', 'apellido_paterno' => (string) $documento, 'estado' => 'ACTIVO']);
            $cama = Cama::create(['cod_habitacion' => $hab->getKey(), 'codigo' => 'SIM-'.$documento, 'estado' => 'ACTIVA']);
            $pre = Preadmision::create(['cod_contacto' => $contacto->getKey(), 'cod_usuario_registro' => $admin->getKey(),
                'nombres' => 'Caso sintético', 'apellido_paterno' => (string) $documento, 'numero_documento' => (string) $documento,
                'fecha_nacimiento' => '1945-05-05', 'motivo_ingreso' => 'Cuidados ficticios', 'fecha_solicitud' => '2026-01-01', 'estado' => 'APROBADA']);
            $r = app(FormalizarAdmision::class)->ejecutar($pre, ['cod_cama' => $cama->getKey(), 'cod_contacto' => $contacto->getKey(),
                'fecha_hora_admision' => '2026-01-02 10:00:00'], $admin);
            AsignacionResidenteJornada::create(['cod_residente' => $r->getKey(), 'cod_personal' => $personas['ENFERMERIA']->getKey(),
                'cod_jornada' => $jornada->getKey(), 'nivel_supervision' => 'HABITUAL', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        }
        $this->entrada = [DB::connection()->getDatabaseName(), $personas['ENFERMERIA']->getKey(),
            $personas['MEDICINA']->getKey(), $personas['FISIOTERAPIA']->getKey(), $area->getKey(), $areaFuncional->getKey(), $turno->getKey()];
    }

    public function test_carga_completa_preserva_historia_tipos_y_no_activa_el_motor(): void
    {
        $this->preparar();
        $r = Residente::where('numero_documento', '99000001')->sole();
        $previo = ControlCognitivo::create(['cod_residente' => $r->getKey(), 'cod_personal' => $this->entrada[1],
            'fecha_hora' => '2026-08-01 09:40:00', 'memoria_reciente' => 'CONSERVADA', 'estado' => 'VIGENTE', 'observacion' => 'Historia anterior.']);
        $historiaAnterior = $previo->fresh()->getAttributes();
        $resultado = app(Carga::class)->ejecutar(...$this->entrada);
        self::assertSame(4972, $resultado['nuevos']);
        self::assertSame(60, $resultado['jornadas_nuevas']);
        self::assertCount(5, $resultado['casos']);
        self::assertSame('2026-08-18', $resultado['desde']);
        self::assertSame(300, $resultado['tablas']['controles_cognitivos']);
        self::assertSame(600, $resultado['tablas']['signos_vitales']);
        self::assertSame(900, $resultado['tablas']['registros_ingesta']);
        self::assertSame(1200, $resultado['tablas']['registros_hidratacion']);
        self::assertSame(8, $resultado['tablas']['incidentes']);
        self::assertSame($historiaAnterior, $previo->fresh()->getAttributes());
        self::assertSame(15, DB::table('controles_cognitivos')->whereNull('memoria_reciente')->count());
        self::assertSame(300, DB::table('controles_cognitivos')->where('confusion', false)->count());
        self::assertSame(240, DB::table('valoraciones_dolor')->where('intensidad', 0)->count());
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
        self::assertSame(0, DB::table('reglas_expertas')->count());
        self::assertSame([], config('sistema_experto.lectura_clinica'));
        self::assertFalse(DB::table('controles_cognitivos')->where('fecha_hora', '>', now())->exists());
        self::assertFalse(DB::table('notas_clinicas as n')->join('atenciones as a', 'n.cod_atencion', '=', 'a.cod_atencion')
            ->whereColumn('n.cod_residente', '!=', 'a.cod_residente')->exists());
        self::assertFalse(DB::table('valoraciones_funcionales as v')->join('atenciones as a', 'v.cod_atencion', '=', 'a.cod_atencion')
            ->where(fn ($q) => $q->whereColumn('v.cod_residente', '!=', 'a.cod_residente')->orWhereColumn('v.cod_personal', '!=', 'a.cod_personal'))->exists());
        foreach (DB::table('registros_ingesta as i')->join('jornadas as j', 'i.cod_jornada', '=', 'j.cod_jornada')
            ->get(['i.fecha_hora', 'j.fecha_jornada']) as $registro) {
            self::assertSame(substr($registro->fecha_hora, 0, 10), substr($registro->fecha_jornada, 0, 10));
        }
        $secuencias = DB::table('secuencias_codigos')->orderBy('entidad')->get()->toJson();
        $auditoria = DB::table('activity_log')->count();
        $segunda = app(Carga::class)->ejecutar(...$this->entrada);
        self::assertSame(0, $segunda['nuevos']);
        self::assertSame(4972, $segunda['existentes']);
        self::assertSame(0, $segunda['jornadas_nuevas']);
        self::assertSame($secuencias, DB::table('secuencias_codigos')->orderBy('entidad')->get()->toJson());
        self::assertSame($auditoria, DB::table('activity_log')->count());
    }

    public function test_rechaza_produccion_y_destino_no_confirmado_sin_escrituras(): void
    {
        foreach (['production', 'testing'] as $ambiente) {
            $this->app->instance('env', $ambiente);
            try {
                app(Carga::class)->ejecutar('otra_base', '', '', '', '', '', '');
                self::fail('Se permitió un destino no confirmado.');
            } catch (RuntimeException $e) {
                self::assertStringContainsString('prohibida', $e->getMessage());
                self::assertSame(0, DB::table('controles_cognitivos')->count());
                self::assertSame(0, DB::table('usuarios')->count());
            }
        }
    }

    public function test_rechaza_competencia_incorrecta_y_no_busca_un_reemplazo(): void
    {
        $this->preparar();
        $this->entrada[2] = $this->entrada[1];
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('competencia');
        try {
            app(Carga::class)->ejecutar(...$this->entrada);
        } finally {
            self::assertSame(0, DB::table('atenciones')->count());
        }
    }

    public function test_rechaza_residente_fuera_del_alcance_actual(): void
    {
        $this->preparar();
        DB::table('asignaciones_residente_jornada')->where('cod_residente', Residente::where('numero_documento', '99000001')->value('cod_residente'))
            ->update(['estado' => 'INACTIVA']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('fuera del alcance');
        try {
            app(Carga::class)->ejecutar(...$this->entrada);
        } finally {
            self::assertSame(0, DB::table('controles_cognitivos')->count());
        }
    }

    public function test_no_sobrescribe_una_observacion_del_lote_alterada(): void
    {
        $this->preparar();
        app(Carga::class)->ejecutar(...$this->entrada);
        $id = DB::table('controles_cognitivos')->orderBy('cod_control_cognitivo')->value('cod_control_cognitivo');
        DB::table('controles_cognitivos')->where('cod_control_cognitivo', $id)->update(['memoria_reciente' => 'MODIFICADO']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no se sobrescribirá');
        try {
            app(Carga::class)->ejecutar(...$this->entrada);
        } finally {
            self::assertSame('MODIFICADO', DB::table('controles_cognitivos')->where('cod_control_cognitivo', $id)->value('memoria_reciente'));
            self::assertSame(300, DB::table('controles_cognitivos')->count());
        }
    }

    public function test_fallo_intermedio_revierte_registros_y_correlativos(): void
    {
        $this->preparar();
        $antes = DB::table('secuencias_codigos')->orderBy('entidad')->get()->toJson();
        DB::listen(static function (QueryExecuted $query): void {
            if (str_starts_with($query->sql, 'insert into "incidentes"')) {
                throw new RuntimeException('Fallo simulado al final de la carga.');
            }
        });
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fallo simulado');
        try {
            app(Carga::class)->ejecutar(...$this->entrada);
        } finally {
            self::assertSame(0, DB::table('atenciones')->count());
            self::assertSame(0, DB::table('controles_cognitivos')->count());
            self::assertSame(0, DB::table('incidentes')->count());
            self::assertSame($antes, DB::table('secuencias_codigos')->orderBy('entidad')->get()->toJson());
        }
    }

    public function test_rectifica_coincidencia_de_hora_conservando_ambos_originales_y_sucesor(): void
    {
        $this->preparar();
        $residente = Residente::where('numero_documento', '99000001')->sole();
        $previo = ControlCognitivo::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $this->entrada[1],
            'fecha_hora' => '2026-08-20 09:40:00', 'memoria_reciente' => 'CONSERVADA', 'estado' => 'VIGENTE', 'observacion' => 'Historia previa ajena al lote.']);
        $datosPrevios = $previo->fresh()->getAttributes();
        $resultado = app(Carga::class)->ejecutar(...$this->entrada);
        self::assertSame(1, $resultado['rectificaciones_nuevas']);
        self::assertSame($datosPrevios, $previo->fresh()->getAttributes());
        $original = ControlCognitivo::where('estado', 'RECTIFICADO')->sole();
        $nuevo = ControlCognitivo::where('observacion', 'like', '[COR-'.Carga::VERSION.':%')->sole();
        self::assertSame('2026-08-20 09:40:00', $original->fecha_hora->format('Y-m-d H:i:s'));
        self::assertSame('2026-08-20 09:45:00', $nuevo->fecha_hora->format('Y-m-d H:i:s'));
        self::assertStringContainsString($original->getKey(), $nuevo->observacion);
        self::assertStringContainsString('no es otra corroboración independiente', $nuevo->observacion);
        self::assertSame($original->memoria_reciente, $nuevo->memoria_reciente);
        self::assertSame($original->cod_personal, $nuevo->cod_personal);
        self::assertSame(0, app(Carga::class)->ejecutar(...$this->entrada)['rectificaciones_nuevas']);
        $nuevo->update(['memoria_reciente' => 'MODIFICADO']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no se sobrescribirá');
        app(Carga::class)->ejecutar(...$this->entrada);
    }
}
