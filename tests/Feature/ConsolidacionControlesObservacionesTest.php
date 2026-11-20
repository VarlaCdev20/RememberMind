<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\ControlFuncionalIadl;
use App\Models\Personal;
use App\Models\RegistroMovilidad;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GuardarResultadoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarValoracionPetersenOriginal;
use Database\Seeders\PermisoEjecucionInstitucionalSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

class ConsolidacionControlesObservacionesTest extends TestCase
{
    use EsquemaSinTransaccion;

    private $migracion;

    private array $originales;

    private array $cognitivo;

    private array $iadl;

    private ?string $triggersOriginales;

    private int $tablasSeparadas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-17 15:00:00');
        $this->migracion = require database_path('migrations/2026_11_18_000001_separate_clinical_assessments.php');
        $usuario = User::factory()->create();
        $personal = Personal::create(['cod_usuario' => $usuario->getKey(), 'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintético', 'numero_documento' => 'CONTROL-SINTETICO', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        $atencion = Atencion::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey(),
            'cod_area' => $area->getKey(), 'tipo_atencion' => 'CONSULTA', 'fecha_hora' => now(), 'estado' => 'VIGENTE']);
        $base = ['cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey(), 'cod_atencion' => $atencion->getKey(),
            'fecha_hora' => now()->toDateTimeString(), 'observacion' => 'Observación sintética original'];
        RegistroMovilidad::create($base + ['marcha' => 'ASISTIDA', 'estado' => 'VIGENTE']);
        $this->cognitivo = $base + ['cod_control_cognitivo' => 'COG-ORIGINAL-60', 'cod_jornada' => null,
            'orientacion_persona' => 'CONSERVADA', 'orientacion_lugar' => 'CONSERVADA', 'orientacion_tiempo' => 'VARIABLE',
            'memoria_reciente' => 'CAMBIO', 'memoria_remota' => 'CONSERVADA', 'atencion' => 'VARIABLE', 'comprension' => 'CONSERVADA',
            'lenguaje' => 'CONSERVADO', 'sigue_instrucciones' => true, 'repite_preguntas' => true, 'olvida_indicaciones' => false,
            'reconoce_personas' => true, 'reconoce_entorno' => true, 'confusion' => false, 'cambio_cognitivo' => true, 'estado' => 'VIGENTE'];
        DB::table('controles_cognitivos')->insert($this->cognitivo);
        $this->iadl = $base + ['cod_control_iadl' => 'IAD-ORIGINAL-60', 'cod_usuario_registro' => $usuario->getKey(),
            'cod_control_anterior' => null, 'actividad' => 'MEDICACION', 'oportunidad' => 'OBSERVADA', 'desempeno' => 'CON_APOYO',
            'tipo_ayuda' => 'RECORDATORIO', 'causa_ayuda' => 'NO_DETERMINADA', 'nivel_previo' => 'DESCONOCIDO',
            'fuente' => 'OBSERVACION_DIRECTA', 'registrado_en' => now()->addMinute()->toDateTimeString()];
        // Omite estado para comprobar el DEFAULT aprobado de la fuente original.
        DB::table('controles_funcionales_iadl')->insert($this->iadl);
        DB::table('controles_funcionales_iadl')->insert(array_replace($this->iadl, ['cod_control_iadl' => 'IAD-CORRECCION-60',
            'cod_control_anterior' => 'IAD-ORIGINAL-60', 'estado' => 'RECTIFICACION', 'desempeno' => 'NO_EVALUABLE']));
        Schema::create('qa_referencias_control', function ($t): void {
            $t->string('cod_control_cognitivo', 20);
            $t->string('cod_control_iadl', 20);
            $t->foreign('cod_control_cognitivo')->references('cod_control_cognitivo')->on('controles_cognitivos')->restrictOnDelete();
            $t->foreign('cod_control_iadl')->references('cod_control_iadl')->on('controles_funcionales_iadl')->restrictOnDelete();
        });
        DB::table('qa_referencias_control')->insert(['cod_control_cognitivo' => 'COG-ORIGINAL-60', 'cod_control_iadl' => 'IAD-ORIGINAL-60']);
        // Este ensayo verifica el contrato histórico previo a la separación de contexto.
        (require database_path('migrations/2026_11_20_000004_separate_optional_care_residents.php'))->down();
        $this->tablasSeparadas = count(Schema::getTableListing());
        $this->triggersOriginales = DB::getDriverName() === 'sqlite' ? DB::table('sqlite_master')->where('type', 'trigger')->orderBy('name')->get(['name', 'sql'])->toJson() : null;
        $this->originales = $this->capturar();
        $this->migracion->down();
    }

    private function capturar(): array
    {
        $r = [];
        foreach (['registros_observaciones_cuidado' => 'cod_observacion_cuidado', 'controles_cognitivos' => 'cod_control_cognitivo',
            'controles_funcionales_iadl' => 'cod_control_iadl'] as $t => $pk) {
            $r[$t] = DB::table($t)->orderBy($pk)->get()->toJson();
        }

        return $r;
    }

    public function test_conserva_valores_rectificaciones_referencias_y_reversion_poblada(): void
    {
        $this->assertCount($this->tablasSeparadas - 3, Schema::getTableListing());
        $this->migracion->up();
        $this->assertCount($this->tablasSeparadas, Schema::getTableListing());
        $this->assertCount(44, Schema::getColumnListing('registros_observaciones_cuidado'));
        $this->assertSame(1, ControlCognitivo::count());
        $this->assertSame(2, ControlFuncionalIadl::count());
        $this->assertSame(1, DB::table('registros_observaciones_cuidado')->where('tipo_evento', 'MOVILIDAD')->count());
        $control = ControlCognitivo::sole();
        foreach ($this->cognitivo as $campo => $valor) {
            $this->assertEquals($valor, $campo === 'fecha_hora' ? $control->$campo->toDateTimeString() : $control->$campo);
        }
        $correccion = ControlFuncionalIadl::findOrFail('IAD-CORRECCION-60');
        $this->assertSame('IAD-ORIGINAL-60', $correccion->anterior->getKey());
        $this->assertSame('REGISTRADO', $correccion->anterior->estado);
        $this->assertSame($this->iadl['cod_usuario_registro'], $correccion->registrador->getKey());
        $this->assertSame($this->iadl['cod_atencion'], $correccion->atencion->getKey());
        $this->assertFalse(array_key_exists('marcha', $control->toArray()));
        $this->assertNull(ControlCognitivo::find($correccion->getKey()));
        $this->assertSame($this->originales, $this->capturar());
        if ($this->triggersOriginales !== null) {
            $this->assertSame($this->triggersOriginales, DB::table('sqlite_master')->where('type', 'trigger')->orderBy('name')->get(['name', 'sql'])->toJson());
        }
        $this->assertSame('IAD-ORIGINAL-60', DB::table('qa_referencias_control')->value('cod_control_iadl'));
        $this->assertTrue(Schema::hasTable('controles_cognitivos'));
        $this->assertTrue(Schema::hasTable('controles_funcionales_iadl'));
    }

    public function test_sql_rechaza_claves_de_otro_tipo_y_modelo_conserva_inmutabilidad_iadl(): void
    {
        $this->rechazar(fn () => DB::table('qa_referencias_control')->update(['cod_control_iadl' => 'COG-ORIGINAL-60']));
        $this->rechazar(fn () => DB::table('registros_observaciones_cuidado')->where('cod_control_iadl', 'IAD-CORRECCION-60')
            ->update(['cod_control_anterior' => 'COG-ORIGINAL-60']));
        $this->rechazar(fn () => DB::table('registros_observaciones_cuidado')->where('cod_control_cognitivo', 'COG-ORIGINAL-60')
            ->update(['actividad' => 'MEDICACION']));
        foreach (['000004_consolidate_observaciones_cuidado', '000006_consolidate_sueno_en_observaciones'] as $nombre) {
            try {
                (require database_path('migrations/2026_11_14_'.$nombre.'.php'))->down();
                $this->fail('No debe revertir una unión anterior antes de retirar esta extensión.');
            } catch (\LogicException $error) {
                $this->assertStringContainsString('Revierta primero', $error->getMessage());
            }
        }
        $this->migracion->up();
        $otroResidente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->rechazar(fn () => DB::table('controles_funcionales_iadl')->where('cod_control_iadl', 'IAD-ORIGINAL-60')
            ->update(['cod_residente' => $otroResidente->getKey()]));
        try {
            ControlFuncionalIadl::findOrFail('IAD-ORIGINAL-60')->update(['observacion' => 'Sobrescritura indebida']);
            $this->fail('Debe conservar el registro original.');
        } catch (\LogicException $error) {
            $this->assertStringContainsString('inmutable', $error->getMessage());
        }
        $this->assertSame(2, ControlFuncionalIadl::count());
        $this->assertSame($this->iadl['observacion'], ControlFuncionalIadl::findOrFail('IAD-ORIGINAL-60')->observacion);
    }

    public function test_fallo_en_tercera_fuente_no_deja_union_parcial(): void
    {
        $this->migracion->up();
        DB::statement('ALTER TABLE controles_funcionales_iadl ADD COLUMN campo_no_aprobado varchar(10)');
        try {
            $this->migracion->down();
            $this->fail('Debe rechazar una estructura ajena al contrato.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('difiere del contrato', $error->getMessage());
        }
        $this->assertTrue(Schema::hasTable('controles_cognitivos'));
        $this->assertFalse(Schema::hasTable('controles_cognitivos_antes16'));
        $this->assertSame($this->originales['registros_observaciones_cuidado'], $this->capturar()['registros_observaciones_cuidado']);
        DB::statement('ALTER TABLE controles_funcionales_iadl DROP COLUMN campo_no_aprobado');
        $this->assertSame($this->originales, $this->capturar());
    }

    private function rechazar(callable $operacion): void
    {
        try {
            DB::transaction($operacion);
            $this->fail('La combinación debe rechazarse por SQL.');
        } catch (QueryException $error) {
            $this->assertContains($error->getCode(), ['23000', '23503', '23514']);
        }
    }

    public function test_resultado_institucional_conserva_lectura_de_ambos_tipos_sin_cambiar_su_identidad(): void
    {
        $this->migracion->up();
        (require base_path('database/propuestas/experto/2026_11_04_000001_conservar_resultado_institucional.php'))->up();
        $this->seed([RolesAndPermissionsSeeder::class, PermisoEjecucionInstitucionalSeeder::class]);
        $actor = Personal::where('numero_documento', 'CONTROL-SINTETICO')->sole()->usuario;
        $actor->assignRole('PSICOLOGO/A');
        $this->actingAs($actor);
        $this->travel(5)->minutes();
        $this->assertTrue(Gate::forUser($actor)->allows('gestionarReglas', VersionModeloExperto::class), 'Gobierno del conocimiento');
        app(RegistrarConocimientoInstitucional::class)->ejecutar($actor);
        app(RegistrarConocimientoPetersen::class)->ejecutar($actor);
        $residente = Residente::findOrFail($this->iadl['cod_residente']);
        $this->assertTrue(Gate::forUser($actor)->allows('crearOriginal', [ValoracionPetersenOriginal::class, $residente]), 'Captura original autorizada');
        foreach ([['C01', 'HISTORIA_PREVIA', 'cod_control_cognitivo', 'COG-ORIGINAL-60'], ['C03', 'IADL', 'cod_control_iadl', 'IAD-CORRECCION-60']] as [$elemento, $tipo, $fk, $codigo]) {
            app(RegistrarValoracionPetersenOriginal::class)->ejecutar($actor, $residente,
                ['solicitud_uuid' => (string) Str::uuid(), 'elemento' => $elemento, 'estado_original' => 'N',
                    'fecha_hora' => now()->format('Y-m-d H:i:s'), 'justificacion' => 'Juicio sintético que conserva sus fuentes originales.',
                    'tipo_registro' => 'REGISTRO', 'cod_atencion' => $this->iadl['cod_atencion'],
                    'soportes' => [['tipo_soporte' => $tipo, $fk => $codigo]]]);
        }
        $guardado = app(GuardarResultadoInstitucional::class)
            ->ejecutar($actor, $residente, (string) Str::uuid());
        $hechos = collect($guardado['contenido']['hechos']);
        $this->assertTrue($hechos->firstWhere('concepto', 'lectura:cod_control_cognitivo')['valor']);
        $this->assertTrue($hechos->firstWhere('concepto', 'lectura:cod_control_iadl')['valor']);
        $this->assertSame(1, ControlCognitivo::count());
        $this->assertSame(2, ControlFuncionalIadl::count());
    }
}
