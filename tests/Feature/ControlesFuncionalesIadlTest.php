<?php

namespace Tests\Feature;

use App\Models\ControlFuncionalIadl;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarControlIadl;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarEvidenciaIadlC03;
use App\Modulos\Valoraciones\Dominio\Servicios\AnalizadorIadlC03;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ControlesFuncionalesIadlTest extends TestCase
{
    use RefreshDatabase;

    private User $profesional;
    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->freezeTime();
        // Esta suite de contrato no compila recursos frontend; el diseño se valida en CI global.
        $this->withoutVite();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->profesional = User::factory()->create([
            'estado' => 'ACTIVO', 'nombres'=>'Lucía',
            'apellido_paterno'=>'Pruebas',
        ]);
        $this->profesional->assignRole('FISIOTERAPEUTA');
        $this->residente = Residente::factory()->create(['estado'=>'ADMITIDO']);
    }

    private function entrada(array $extras = []): array
    {
        return array_replace([
            'fecha_hora' => now()->subMinutes(10)->format('Y-m-d H:i:s'),
            'actividad' => 'RUTINAS',
            'oportunidad' => 'OBSERVADA',
            'desempeno' => 'INDEPENDIENTE',
            'tipo_ayuda' => 'NINGUNA',
            'causa_ayuda' => 'NINGUNA',
            'nivel_previo' => 'INDEPENDIENTE',
            'fuente' => 'OBSERVACION_DIRECTA',
            'observacion' => 'Sigue su rutina programada.',
        ], $extras);
    }

    private function registrar(array $datos = []): ControlFuncionalIadl
    {
        return app(RegistrarControlIadl::class)->ejecutar(
            $this->profesional, $this->residente, $this->entrada($datos)
        );
    }

    public function test_tabla_e_identidad_fisica_de_la_extension_son_correctas(): void
    {
        $this->assertTrue(Schema::hasTable('controles_funcionales_iadl'));
        $this->assertTrue(Schema::hasColumns('controles_funcionales_iadl', [
            'cod_control_iadl','cod_residente','cod_personal','cod_usuario_registro',
            'cod_control_anterior','fecha_hora','actividad','oportunidad',
            'desempeno','tipo_ayuda','causa_ayuda','nivel_previo','fuente','registrado_en',
        ]));
        $primarias = array_values(array_filter(Schema::getIndexes('controles_funcionales_iadl'), fn ($indice) => $indice['primary']));
        $this->assertCount(1, $primarias);
        $this->assertSame(['cod_control_iadl'], $primarias[0]['columns']);
        $relaciones = Schema::getForeignKeys('controles_funcionales_iadl');
        $this->assertCount(5, $relaciones);
        $anterior = array_values(array_filter($relaciones, fn ($fk) => $fk['columns'] === ['cod_control_anterior']));
        $this->assertCount(1, $anterior);
        $this->assertSame('controles_funcionales_iadl', $anterior[0]['foreign_table']);
        $this->assertSame(['cod_control_iadl'], $anterior[0]['foreign_columns']);
        $this->assertSame('restrict', $anterior[0]['on_delete']);
    }

    public function test_guardar_observacion_atribuye_profesional_y_usuario_sin_diagnostico(): void
    {
        $c = $this->registrar();
        $this->assertSame($this->residente->cod_residente, $c->cod_residente);
        $this->assertSame($this->profesional->cod_usuario, $c->cod_usuario_registro);
        $this->assertSame($this->profesional->personal->cod_personal, $c->cod_personal);
        $this->assertSame('REGISTRADO', $c->estado);
        $this->assertNotNull($c->registrado_en);
        $salida = app(AnalizadorIadlC03::class)->analizar([[
            ...$c->getAttributes(),
            'fecha_hora'=>$c->fecha_hora->format('Y-m-d H:i:s'),
            'registrado_en'=>$c->registrado_en->format('Y-m-d H:i:s'),
        ]]);
        $this->assertSame('U', $salida['criterio_dcl_c03']);
        $this->assertSame(1, $salida['actividades_independientes']);
    }

    public function test_sin_oportunidad_institucional_no_es_dependencia(): void
    {
        $c = $this->registrar([
            'actividad'=>'MEDICACION',
            'oportunidad'=>'SIN_OPORTUNIDAD','desempeno'=>'NO_EVALUABLE',
            'tipo_ayuda'=>'NO_APLICA','causa_ayuda'=>'NO_APLICA',
            'fuente'=>'EQUIPO_CUIDADOS',
            'observacion'=>'El centro administra medicación por protocolo.',
        ]);
        $r = app(AnalizadorIadlC03::class)->analizar([[
            ...$c->getAttributes(),
            'fecha_hora'=>$c->fecha_hora->format('Y-m-d H:i:s'),
            'registrado_en'=>$c->registrado_en->format('Y-m-d H:i:s'),
        ]]);
        $this->assertSame('SIN_COBERTURA_FUNCIONAL', $r['estado_tecnico']);
        $this->assertSame(1, $r['actividades_sin_oportunidad']);
        $this->assertSame(0, $r['ayudas_con_atribucion_cognitiva_o_mixta']);
        $this->assertSame('U', $r['criterio_dcl_c03']);
    }

    public function test_ayuda_fisica_no_se_convierte_a_dependencia_cognitiva(): void
    {
        $this->registrar([
            'desempeno'=>'CON_APOYO', 'tipo_ayuda'=>'SUPERVISION',
            'causa_ayuda'=>'FISICA',
            'observacion'=>'Necesita apoyo físico para manipular materiales; comprende secuencia.',
        ]);
        $r=app(AnalizadorIadlC03::class)->analizar(
            ControlFuncionalIadl::query()->get()->map(fn($v)=>[
                ...$v->getAttributes(),
                'fecha_hora'=>$v->fecha_hora->format('Y-m-d H:i:s'),
                'registrado_en'=>$v->registrado_en->format('Y-m-d H:i:s'),
            ])->all()
        );
        $this->assertSame(1, $r['ayudas_por_otras_causas']);
        $this->assertSame(0, $r['ayudas_con_atribucion_cognitiva_o_mixta']);
        $this->assertSame('U', $r['criterio_dcl_c03']);
    }

    public function test_ayuda_cognitiva_exige_revision_y_no_diagnostica(): void
    {
        $this->registrar([
            'actividad'=>'PERTENENCIAS',
            'desempeno'=>'CON_APOYO', 'tipo_ayuda'=>'RECORDATORIO',
            'causa_ayuda'=>'COGNITIVA',
            'observacion'=>'Refiere dificultades para recordar dónde guardó objetos.',
        ]);
        $datos=ControlFuncionalIadl::query()->get()->map(fn($v)=>[
            ...$v->getAttributes(),
            'fecha_hora'=>$v->fecha_hora->format('Y-m-d H:i:s'),
            'registrado_en'=>$v->registrado_en->format('Y-m-d H:i:s'),
        ])->all();
        $r=app(AnalizadorIadlC03::class)->analizar($datos);
        $this->assertSame('DEPENDENCIA_COGNITIVA_REPORTADA_REQUIERE_REVISION',$r['estado_tecnico']);
        $this->assertSame('U',$r['criterio_dcl_c03']);
        $this->assertTrue($r['requiere_revision_profesional_c03']);
    }

    public function test_combinaciones_incoherentes_y_futuro_se_rechazan(): void
    {
        $invalidos=[
            ['oportunidad'=>'SIN_OPORTUNIDAD'],
            ['desempeno'=>'CON_APOYO'],
            ['desempeno'=>'CON_APOYO','tipo_ayuda'=>'SUPERVISION','causa_ayuda'=>'INSTITUCIONAL','observacion'=>''],
            ['oportunidad'=>'REFERIDA'],
            ['fecha_hora'=>now()->addDay()->format('Y-m-d H:i:s')],
            ['actividad'=>'DIAGNOSTICO_DEMENCIA'],
        ];
        foreach($invalidos as $item) {
            try {
                $this->registrar($item);
                $this->fail('Se permitió entrada IADL incoherente.');
            } catch(ValidationException $e) {
                $this->assertDatabaseCount('controles_funcionales_iadl', 0);
            }
        }
    }

    public function test_rectificacion_agrega_version_y_bloquea_sobrescritura_y_ramificacion(): void
    {
        $v1=$this->registrar();
        $v2=$this->registrar([
            'cod_control_anterior'=>$v1->cod_control_iadl,
            'desempeno'=>'CON_APOYO', 'tipo_ayuda'=>'RECORDATORIO',
            'causa_ayuda'=>'NO_DETERMINADA',
            'observacion'=>'Se rectifica: hubo recordatorio no atribuible.',
        ]);
        $this->assertNotSame($v1->cod_control_iadl,$v2->cod_control_iadl);
        $this->assertSame('RECTIFICACION',$v2->estado);
        $this->assertSame('INDEPENDIENTE',$v1->fresh()->desempeno);
        $this->assertDatabaseCount('controles_funcionales_iadl',2);
        try {
            $v1->update(['observacion'=>'Alteración prohibida']);
            $this->fail('Se modificó un control histórico.');
        } catch (\LogicException) {
            $this->assertSame('Sigue su rutina programada.',$v1->fresh()->observacion);
        }
        try {
            $this->registrar(['cod_control_anterior'=>$v1->cod_control_iadl]);
            $this->fail('Se permitió rectificar dos veces la misma versión.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('controles_funcionales_iadl',2);
        }
    }

    public function test_no_se_puede_rectificar_registro_de_otro_residente(): void
    {
        $original=$this->registrar();
        $otro=Residente::factory()->create(['estado'=>'ADMITIDO']);
        try {
            app(RegistrarControlIadl::class)->ejecutar($this->profesional,$otro,
                $this->entrada(['cod_control_anterior'=>$original->cod_control_iadl]));
            $this->fail('Se permitió corregir datos de otro residente.');
        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException) {
            $this->assertDatabaseCount('controles_funcionales_iadl',1);
        }
    }

    public function test_el_formulario_y_ruta_de_registro_existen_con_permisos(): void
    {
        $this->actingAs($this->profesional)
            ->get(route('admin.adultos-mayores.iadl.index', $this->residente->cod_residente))
            ->assertOk()->assertSee('Actividades instrumentales')->assertSee('PENDIENTE');
        $this->actingAs($this->profesional)
            ->post(route('admin.adultos-mayores.iadl.store', $this->residente->cod_residente),$this->entrada())
            ->assertRedirect();
        $this->assertDatabaseCount('controles_funcionales_iadl',1);
    }

    public function test_familiar_no_puede_consultar_ni_registrar_esta_historia(): void
    {
        $familiar=User::factory()->create();
        $familiar->assignRole('FAMILIAR');
        $this->actingAs($familiar)
            ->get(route('admin.adultos-mayores.iadl.index',$this->residente->cod_residente))
            ->assertForbidden();
        $this->actingAs($familiar)
            ->post(route('admin.adultos-mayores.iadl.store',$this->residente->cod_residente),$this->entrada())
            ->assertForbidden();
        $this->assertDatabaseCount('controles_funcionales_iadl',0);
    }

    public function test_superadministrador_solo_lectura_al_no_ser_autor_clinico(): void
    {
        $admin=User::factory()->create();
        $admin->assignRole('SUPERADMINISTRADOR');
        $this->assertTrue($admin->can('controles_funcionales_iadl.ver'));
        $this->actingAs($admin)
            ->get(route('admin.adultos-mayores.iadl.index',$this->residente->cod_residente))
            ->assertOk();
        $this->actingAs($admin)
            ->post(route('admin.adultos-mayores.iadl.store',$this->residente->cod_residente),$this->entrada())
            ->assertForbidden();
    }

    public function test_base_de_datos_exige_residente_y_profesional_reales_por_fk(): void
    {
        try {
            DB::transaction(fn () => DB::table('controles_funcionales_iadl')->insert([
                'cod_control_iadl'=>'IAD_ORPHAN',
                'cod_residente'=>'RES_INEXISTENTE',
                'cod_personal'=>$this->profesional->personal->cod_personal,
                'cod_usuario_registro'=>$this->profesional->cod_usuario,
                'fecha_hora'=>now(),'actividad'=>'RUTINAS','oportunidad'=>'OBSERVADA',
                'desempeno'=>'INDEPENDIENTE','tipo_ayuda'=>'NINGUNA',
                'causa_ayuda'=>'NINGUNA','nivel_previo'=>'INDEPENDIENTE',
                'fuente'=>'OBSERVACION_DIRECTA','estado'=>'REGISTRADO','registrado_en'=>now(),
            ]));
            $this->fail('La FK no protegió al residente.');
        } catch(\Illuminate\Database\QueryException) {
            $this->assertDatabaseCount('controles_funcionales_iadl',0);
        }
    }

    public function test_fk_de_rectificacion_rechaza_un_registro_anterior_inexistente(): void
    {
        $original = $this->registrar();
        $fila = array_replace($original->getAttributes(), [
            'cod_control_iadl' => 'IAD_RECT_HUERFANA',
            'cod_control_anterior' => 'IAD_INEXISTENTE',
            'estado' => 'RECTIFICACION',
        ]);
        try {
            DB::transaction(fn () => DB::table('controles_funcionales_iadl')->insert($fila));
            $this->fail('La autorreferencia debe rechazar un registro inexistente.');
        } catch (\Illuminate\Database\QueryException) {
            $this->assertDatabaseCount('controles_funcionales_iadl', 1);
            $this->assertDatabaseHas('controles_funcionales_iadl', ['cod_control_iadl' => $original->cod_control_iadl]);
        }
    }

    public function test_apoyo_decision_recibe_solo_evidencia_anterior_al_corte_y_no_emite_c03_s(): void
    {
        $viejo = $this->registrar([
            'actividad'=>'RUTINAS',
            'fecha_hora'=>now()->subDays(3)->format('Y-m-d H:i:s'),
        ]);
        $this->registrar([
            'actividad'=>'PERTENENCIAS',
            'fecha_hora'=>now()->subMinutes(20)->format('Y-m-d H:i:s'),
        ]);
        $r = app(ConsultarEvidenciaIadlC03::class)->ejecutar(
            $this->profesional, $this->residente, now()->subDay()->format('Y-m-d')
        );
        $this->assertSame(1, $r['registros_consultados']);
        $this->assertSame([$viejo->cod_control_iadl], $r['traza_controles']);
        $this->assertSame('U', $r['criterio_dcl_c03']);
        $this->assertFalse($r['puede_emitir_clasificacion_dcl']);
        $this->assertSame($this->residente->cod_residente, $r['cod_residente']);
    }

    public function test_consulta_del_motor_rechaza_actor_sin_permiso_clinico(): void
    {
        $familiar = User::factory()->create();
        $familiar->assignRole('FAMILIAR');
        try {
            app(ConsultarEvidenciaIadlC03::class)->ejecutar(
                $familiar, $this->residente, now()->format('Y-m-d')
            );
            $this->fail('Se leyó evidencia IADL fuera de acceso clínico.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

}
