<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\EventoAsignacionPersonal;
use App\Models\Jornada;
use App\Models\MovimientoLaboralPersonal;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Institucion\Infraestructura\Persistencia\HistoriaLaboral;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConsolidacionHistoriaLaboralTest extends TestCase
{
    use RefreshDatabase;

    private $migracion;

    private array $huellas;

    private Personal $personal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->freezeTime();
        foreach (['2026_10_26_000001_documentacion_y_estado_laboral',
            '2026_10_26_000002_historia_vinculos_asignaciones', '2026_10_27_000003_campos_aviso_evento_asignacion'] as $archivo) {
            (require database_path('propuestas/gerencia/'.$archivo.'.php'))->up();
        }
        $usuario = User::factory()->create();
        $this->personal = Personal::create(['cod_usuario' => $usuario->getKey(), 'nombres' => 'Persona',
            'apellido_paterno' => 'Sintética', 'numero_documento' => 'HLP-SINTETICO', 'profesion' => 'ADMINISTRACION', 'estado' => 'INACTIVO']);
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Horario sintético', 'hora_inicio' => '08:00', 'hora_cierre' => '16:00', 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => now()->toDateString(), 'estado' => 'ABIERTA']);
        $asignacion = AsignacionPersonal::create(['cod_jornada' => $jornada->getKey(), 'cod_personal' => $this->personal->getKey(),
            'cod_area' => $area->getKey(), 'estado' => 'INACTIVO']);
        $comun = ['estado_anterior' => 'ACTIVO', 'estado_nuevo' => 'INACTIVO', 'fecha_hora_efecto' => now(),
            'fecha_hora_registro' => now(), 'cod_usuario_registro' => $usuario->getKey(), 'motivo' => 'Acto original sintético'];
        DB::table('movimientos_laborales_personal')->insert($comun + [
            'cod_movimiento_laboral' => 'MLP-ORIGINAL', 'cod_personal' => $this->personal->getKey()]);
        DB::table('eventos_asignacion_personal')->insert(array_replace($comun, ['estado_anterior' => null, 'estado_nuevo' => 'ACTIVO']) + [
            'cod_evento_asignacion' => 'EAP-PRIMERO', 'cod_asignacion_personal' => $asignacion->getKey(),
            'numero_evento' => 1, 'cod_evento_anterior' => null, 'tipo_evento' => 'ALTA']);
        DB::table('eventos_asignacion_personal')->insert($comun + [
            'cod_evento_asignacion' => 'EAP-SEGUNDO', 'cod_asignacion_personal' => $asignacion->getKey(),
            'numero_evento' => 2, 'cod_evento_anterior' => 'EAP-PRIMERO', 'tipo_evento' => 'FINALIZACION',
            'cod_usuario_afectado_aviso' => $usuario->getKey(), 'estado_aviso_planificacion' => 'PENDIENTE', 'numero_intentos_aviso' => 0]);
        $this->huellas = $this->originales();
        $this->migracion = require database_path('propuestas/gerencia/2026_11_16_000004_consolidacion_historia_laboral.php');
        $this->migracion->up();
    }

    private function originales(): array
    {
        return [
            DB::table('movimientos_laborales_personal')->orderBy('cod_movimiento_laboral')->get()->toJson(),
            DB::table('eventos_asignacion_personal')->orderBy('cod_evento_asignacion')->get()->toJson(),
        ];
    }

    public function test_copia_reversion_poblada_y_reaplicacion_conservan_identidades_y_avisos(): void
    {
        $this->assertCount(98, Schema::getTableListing());
        $this->assertFalse(Schema::hasTable('movimientos_laborales_personal'));
        $this->assertFalse(Schema::hasTable('eventos_asignacion_personal'));
        $this->assertSame(1, MovimientoLaboralPersonal::count());
        $this->assertSame(2, EventoAsignacionPersonal::count());
        $this->assertNull(MovimientoLaboralPersonal::find('EAP-PRIMERO'));
        $this->assertNull(EventoAsignacionPersonal::find('MLP-ORIGINAL'));
        $this->assertSame($this->personal->getKey(), MovimientoLaboralPersonal::sole()->personal->getKey());
        $this->migracion->down();
        $this->assertSame($this->huellas, $this->originales());
        $this->migracion->up();
        $evento = EventoAsignacionPersonal::findOrFail('EAP-SEGUNDO');
        $this->assertSame('EAP-SEGUNDO', $evento->getKey());
        $this->assertSame('EAP-PRIMERO', $evento->cod_evento_anterior);
        $this->assertSame('PENDIENTE', $evento->estado_aviso_planificacion);
        $nuevo = MovimientoLaboralPersonal::create(['cod_personal' => $this->personal->getKey(),
            'estado_anterior' => 'INACTIVO', 'estado_nuevo' => 'ACTIVO', 'fecha_hora_efecto' => now(), 'fecha_hora_registro' => now(),
            'cod_usuario_registro' => $evento->cod_usuario_registro, 'motivo' => 'Acto posterior sintético']);
        $this->assertStringStartsWith('COD_MLP_', $nuevo->getKey());
        $this->assertSame($nuevo->getKey(), $nuevo->fresh()->getKey());
        $this->assertSame(2, MovimientoLaboralPersonal::count());
        $this->assertSame(2, EventoAsignacionPersonal::count());
    }

    public function test_sql_conserva_actos_y_secuencia_y_solo_permite_progresion_del_aviso(): void
    {
        $laboral = fn () => HistoriaLaboral::consulta('movimientos_laborales_personal');
        $eventos = fn () => HistoriaLaboral::consulta('eventos_asignacion_personal');
        $this->rechazar(fn () => $laboral()->update(['motivo' => 'Sobrescritura']));
        $this->rechazar(fn () => $laboral()->delete());
        $this->rechazar(fn () => $eventos()->delete());
        $this->rechazar(fn () => $laboral()->update(['cod_historial_laboral' => 'IDENTIDAD-ALTERADA']));
        $this->rechazar(fn () => $eventos()->update(['cod_personal' => $this->personal->getKey()]));
        $this->rechazar(fn () => $eventos()->where('cod_evento_asignacion', 'EAP-PRIMERO')
            ->update(['estado_aviso_planificacion' => 'PENDIENTE', 'numero_intentos_aviso' => 0]));
        $evento = EventoAsignacionPersonal::findOrFail('EAP-SEGUNDO');
        $this->rechazar(fn () => $eventos()->where('cod_evento_asignacion', $evento->getKey())->update(['motivo' => 'Sobrescritura']));
        $evento->update(['estado_aviso_planificacion' => 'FALLIDO', 'numero_intentos_aviso' => 1,
            'fecha_hora_ultimo_intento_aviso' => now(), 'categoria_fallo_aviso' => 'FALLO_DISPONIBILIZACION']);
        $this->assertSame('FALLIDO', $evento->fresh()->estado_aviso_planificacion);
        $evento->update(['estado_aviso_planificacion' => 'DISPONIBLE', 'numero_intentos_aviso' => 2,
            'fecha_hora_ultimo_intento_aviso' => now(), 'fecha_hora_disponible_aviso' => now(), 'categoria_fallo_aviso' => null]);
        $this->rechazar(fn () => $eventos()->where('cod_evento_asignacion', $evento->getKey())->update(['numero_intentos_aviso' => 3]));
        $this->assertSame('DISPONIBLE', $evento->fresh()->estado_aviso_planificacion);
        $fila = $evento->fresh()->getAttributes();
        $fila['cod_evento_asignacion'] = 'EAP-SALTO';
        $fila['numero_evento'] = 4;
        $fila['cod_evento_anterior'] = $evento->getKey();
        $this->rechazar(fn () => HistoriaLaboral::insertar('eventos_asignacion_personal', [$fila]));
        $fila['numero_evento'] = 3;
        $fila['cod_personal'] = $this->personal->getKey();
        $this->rechazar(fn () => HistoriaLaboral::insertar('eventos_asignacion_personal', [$fila]));
        $this->assertSame(2, EventoAsignacionPersonal::count());
    }

    public function test_cambio_intermedio_de_esquema_no_deja_una_union_parcial(): void
    {
        $this->migracion->down();
        DB::statement('ALTER TABLE eventos_asignacion_personal ADD COLUMN campo_no_aprobado varchar(10)');
        try {
            $this->migracion->up();
            $this->fail('No se debe migrar una estructura distinta del contrato.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('difiere del contrato', $error->getMessage());
        }
        $this->assertTrue(Schema::hasTable('movimientos_laborales_personal'));
        $this->assertTrue(Schema::hasTable('eventos_asignacion_personal'));
        $this->assertFalse(Schema::hasTable('historial_laboral_personal'));
        $this->assertSame($this->huellas[0], $this->originales()[0]);
        DB::statement('ALTER TABLE eventos_asignacion_personal DROP COLUMN campo_no_aprobado');
        $this->migracion->up();
    }

    private function rechazar(callable $operacion): void
    {
        try {
            DB::transaction($operacion);
            $this->fail('La operación debe ser rechazada.');
        } catch (QueryException $error) {
            $this->assertContains($error->getCode(), ['23000', '23503', '23514', 'P0001']);
        }
    }
}
