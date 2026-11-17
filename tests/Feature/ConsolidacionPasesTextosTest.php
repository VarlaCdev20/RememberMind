<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Atencion;
use App\Models\IndicacionClinica;
use App\Models\Jornada;
use App\Models\NotaClinica;
use App\Models\PaseTurno;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConsolidacionPasesTextosTest extends TestCase
{
    use RefreshDatabase;

    private $migracion;

    private NotaClinica $nota;

    private array $pase;

    private array $originales;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-11-17 16:00:00'));
        $this->migracion = require database_path('migrations/2026_11_17_000001_consolidate_pases_textos.php');
        $this->migracion->down();
        $personas = [];
        foreach (['ENFERMERIA', 'ENFERMERIA', 'MEDICINA'] as $i => $profesion) {
            $usuario = User::factory()->create();
            $personas[] = Personal::create(['cod_usuario' => $usuario->getKey(), 'nombres' => 'Profesional',
                'apellido_paterno' => 'Sintético', 'numero_documento' => 'PASE-SINTETICO-'.$i,
                'profesion' => $profesion, 'estado' => 'ACTIVO']);
        }
        $residente = Residente::factory()->create();
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        $jornadas = [];
        foreach ([['07:00', '15:00'], ['15:00', '23:00']] as $horas) {
            $turno = Turno::create(['nombre' => 'Horario sintético '.$horas[0], 'hora_inicio' => $horas[0], 'hora_cierre' => $horas[1], 'estado' => 'ACTIVO']);
            $jornadas[] = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        }
        $atencion = Atencion::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $personas[2]->getKey(),
            'cod_area' => $area->getKey(), 'tipo_atencion' => 'CONSULTA', 'fecha_hora' => now()->setTime(14, 30), 'estado' => 'VIGENTE']);
        $base = ['cod_residente' => $residente->getKey(), 'cod_personal' => $personas[2]->getKey(),
            'cod_atencion' => $atencion->getKey(), 'fecha_hora' => now()->setTime(14, 30), 'estado' => 'VIGENTE'];
        $this->nota = NotaClinica::create($base + ['tipo_nota' => 'EVOLUCION', 'contenido' => 'Nota original sintética']);
        NotaClinica::create($base + ['tipo_nota' => 'CORRECCION', 'contenido' => 'Corrección sintética',
            'cod_nota_anterior' => $this->nota->getKey(), 'motivo_correccion' => 'Rectificación sintética']);
        IndicacionClinica::create($base + ['tipo_indicacion' => 'CUIDADO', 'descripcion' => 'Indicación sintética']);
        $this->pase = ['cod_pase' => 'PASE-ORIGINAL-17', 'cod_residente' => $residente->getKey(),
            'cod_jornada_saliente' => $jornadas[0]->getKey(), 'cod_jornada_entrante' => $jornadas[1]->getKey(),
            'cod_personal_saliente' => $personas[0]->getKey(), 'cod_personal_entrante' => $personas[1]->getKey(),
            'fecha_hora' => now()->setTime(15, 0)->toDateTimeString(), 'estado_general' => 'Contexto sintético',
            'resumen' => 'Resumen original', 'pendientes' => 'Pendiente sintético', 'vigilancia' => 'Vigilancia sintética',
            'recomendacion' => 'Continuidad sintética', 'estado' => 'RECIBIDO',
            'fecha_hora_recepcion' => now()->setTime(15, 5)->toDateTimeString(), 'observacion_recepcion' => 'Recepción original'];
        DB::table('pases_turno')->insert($this->pase);
        $this->originales = $this->capturar();
        $this->migracion->up();
    }

    private function capturar(): array
    {
        return [DB::table('registros_textuales_clinicos')->orderBy('cod_registro_textual')->get()->toJson(),
            DB::table('pases_turno')->orderBy('cod_pase')->get()->toJson()];
    }

    public function test_ida_vuelta_poblada_conserva_entrega_recepcion_correccion_y_codigos(): void
    {
        $this->assertCount(93, Schema::getTableListing());
        $pase = PaseTurno::sole();
        foreach ($this->pase as $campo => $valor) {
            $this->assertEquals($valor, in_array($campo, ['fecha_hora', 'fecha_hora_recepcion'], true)
                ? $pase->$campo->toDateTimeString() : $pase->$campo);
        }
        $this->assertSame($this->pase['cod_pase'], $pase->getKey());
        $this->assertSame($this->pase['cod_personal_entrante'], $pase->personalEntrante->getKey());
        $this->assertSame($this->pase['cod_jornada_saliente'], $pase->jornadaSaliente->getKey());
        $this->assertNull(DB::table('registros_textuales_clinicos')->where('cod_pase', $pase->getKey())->value('cod_atencion'));
        $this->assertSame(2, NotaClinica::count());
        $this->assertSame(1, IndicacionClinica::count());
        $this->migracion->down();
        $this->assertSame($this->originales, $this->capturar());
        $this->migracion->up();
        $this->assertSame($this->nota->getKey(), NotaClinica::where('tipo_nota', 'CORRECCION')->sole()->anterior->getKey());
        $this->assertSame('Recepción original', PaseTurno::sole()->observacion_recepcion);
        $nuevo = PaseTurno::create(array_replace($this->pase, ['cod_pase' => null,
            'cod_residente' => Residente::factory()->create()->getKey(), 'estado' => 'BORRADOR',
            'cod_personal_entrante' => null, 'fecha_hora_recepcion' => null, 'observacion_recepcion' => null]));
        $this->assertStringStartsWith('COD_PAS_', $nuevo->getKey());
        $this->assertSame($nuevo->getKey(), $nuevo->fresh()->cod_pase);
        $this->assertFalse(Schema::hasTable('pases_turno'));
    }

    public function test_referencias_por_tipo_y_reversion_antigua_no_borran_pases(): void
    {
        $pase = PaseTurno::sole();
        $this->assertNull(PaseTurno::find($this->nota->getKey()));
        $this->assertNull(NotaClinica::find($pase->getKey()));
        $this->rechazar(fn () => DB::table('registros_textuales_clinicos')->where('cod_registro_textual', $this->nota->getKey())
            ->update(['cod_nota_anterior' => $pase->getKey()]));
        $this->rechazar(fn () => DB::table('registros_textuales_clinicos')->where('cod_pase', $pase->getKey())
            ->update(['cod_personal' => $this->nota->cod_personal]));
        $vieja = require database_path('migrations/2026_11_16_000004_consolidate_textos_clinicos.php');
        try {
            $vieja->down();
            $this->fail('La reversión antigua debe rechazar el esquema ampliado.');
        } catch (\LogicException $error) {
            $this->assertStringContainsString('Revierta primero', $error->getMessage());
        }
        $this->assertSame(1, PaseTurno::count());
        $this->assertSame(2, NotaClinica::count());
        $this->assertFalse(Schema::hasTable('notas_clinicas'));
    }

    public function test_fallo_intermedio_restaura_ambas_fuentes(): void
    {
        $this->migracion->down();
        DB::statement('ALTER TABLE pases_turno ADD COLUMN campo_no_aprobado varchar(10)');
        try {
            $this->migracion->up();
            $this->fail('No debe migrar columnas fuera del contrato.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('difiere del contrato', $error->getMessage());
        }
        $this->assertTrue(Schema::hasTable('pases_turno'));
        $this->assertTrue(Schema::hasTable('registros_textuales_clinicos'));
        $this->assertFalse(Schema::hasTable('registros_textuales_clinicos_antes16'));
        $this->assertSame($this->originales[0], $this->capturar()[0]);
        DB::statement('ALTER TABLE pases_turno DROP COLUMN campo_no_aprobado');
        $this->assertSame($this->originales, $this->capturar());
        $this->migracion->up();
    }

    private function rechazar(callable $operacion): void
    {
        try {
            DB::transaction($operacion);
            $this->fail('Una referencia o combinación de tipos inválida debe rechazarse.');
        } catch (QueryException $error) {
            $this->assertContains($error->getCode(), ['23000', '23503', '23514', 'P0001']);
        }
    }
}
