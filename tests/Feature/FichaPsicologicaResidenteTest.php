<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Atencion;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\FichaPsicologicaResidente;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarSesionPsicologica;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;

final class FichaPsicologicaResidenteTest extends TestCase
{
    use RefreshDatabase;

    private User $psicologo;

    private User $enfermero;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-21T12:00:00-04:00'));
        $this->seed(RolesAndPermissionsSeeder::class);

        // Crear psicólogo
        $this->psicologo = User::factory()->create(['estado' => 'ACTIVO']);
        $this->psicologo->assignRole('PSICOLOGO/A');
        Personal::create([
            'cod_usuario' => $this->psicologo->getKey(),
            'nombres' => 'Licenciada',
            'apellido_paterno' => 'Psicología',
            'numero_documento' => 'DOC-PSI-'.Str::random(6),
            'profesion' => 'PSICOLOGIA',
            'estado' => 'ACTIVO',
        ]);
        $this->psicologo->givePermissionTo([
            'residentes.ver',
            'valoraciones_psicologicas.ver',
            'aplicaciones_instrumento.ver',
            'aplicaciones_instrumento.crear',
            'instrumentos.ver',
        ]);

        $area = Area::create(['cod_area' => 'ARE_AFE', 'nombre' => 'Afectiva sintética', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $this->psicologo->personal->getKey(), 'cod_jornada' => $jornada->getKey(), 'cod_area' => $area->getKey(), 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);

        // Crear enfermero (sin rol de psicología)
        $this->enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $this->enfermero->assignRole('ENFERMEROS');
        Personal::create([
            'cod_usuario' => $this->enfermero->getKey(),
            'nombres' => 'Licenciado',
            'apellido_paterno' => 'Enfermería',
            'numero_documento' => 'DOC-ENF-'.Str::random(6),
            'profesion' => 'ENFERMERIA',
            'estado' => 'ACTIVO',
        ]);

        $this->residente = Residente::factory()->create([
            'cod_residente' => 'RES_PSI_TEST_01',
            'nombres' => 'Carmelo',
            'apellido_paterno' => 'Quispe',
            'apellido_materno' => 'Vargas',
            'fecha_nacimiento' => '1952-04-15',
            'estado' => 'ADMITIDO',
        ]);
    }

    public function test_psicologo_activo_puede_ver_ficha_psicologica_del_residente(): void
    {
        $this->actingAs($this->psicologo);

        $response = $this->get(route('admin.psicologia.residente.ficha', $this->residente));
        $response->assertOk();
        $response->assertSee('Ficha psicológica del residente');
        $response->assertSee('Carmelo Quispe Vargas');
        $response->assertSee('Informe Apoyo a la decisión');
    }

    public function test_enfermero_o_usuario_no_autorizado_es_rechazado_con_403(): void
    {
        $this->actingAs($this->enfermero);

        $response = $this->get(route('admin.psicologia.residente.ficha', $this->residente));
        $response->assertForbidden();
    }

    public function test_fuentes_restringidas_no_exponen_contactos_ni_metricas_iadl(): void
    {
        $this->psicologo->roles()->first()->revokePermissionTo('controles_funcionales_iadl.ver');
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->assertViewHas('totalIadl', null)
            ->call('cambiarMetricaTendencia', 'iadl')
            ->assertViewHas('datosTendencia', null)
            ->assertSee('No tienes permiso para consultar esta fuente')
            ->call('cambiarTab', 'entorno')
            ->assertViewHas('contactosFamiliares', fn ($contactos) => $contactos->isEmpty())
            ->assertSee('Tu cuenta no tiene permiso para consultar contactos');
    }

    public function test_permiso_de_fuente_revocado_impide_consultar_la_ficha(): void
    {
        $this->psicologo->revokePermissionTo('aplicaciones_instrumento.ver');
        $this->psicologo->roles()->first()->revokePermissionTo('aplicaciones_instrumento.ver');
        $this->actingAs($this->psicologo);
        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])->assertForbidden();
    }

    public function test_componente_livewire_muestra_pestanas_didacticas_y_permite_navegar_entre_ellas(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->assertSet('tab', 'resumen')
            ->assertSee('Resumen')
            ->assertSee('Cognitiva')
            ->assertSee('Afectiva y emocional')
            ->assertSee('Funcionamiento (IADL)')
            ->assertSee('Entorno y red social')
            ->assertSee('Sesiones y seguimiento')
            ->assertSee('Informes y resultados')
            // Navegar a Cognitiva
            ->call('cambiarTab', 'cognitiva')
            ->assertSet('tab', 'cognitiva')
            ->assertSee('Baterías cognitivas y vinculación con Apoyo a la decisión')
            ->assertSee('Explorar instrumentos cognitivos')
            // Navegar a Afectiva
            ->call('cambiarTab', 'afectiva')
            ->assertSet('tab', 'afectiva')
            ->assertSee('Evaluación afectiva, del ánimo y descarte diferencial')
            ->assertSee('Consultar recursos del área')
            // Navegar a Funcionamiento
            ->call('cambiarTab', 'funcionamiento')
            ->assertSet('tab', 'funcionamiento')
            ->assertSee('Autonomía instrumental y actividades cotidianas (IADL)')
            // Navegar a Entorno
            ->call('cambiarTab', 'entorno')
            ->assertSet('tab', 'entorno')
            ->assertSee('Red social, vinculación familiar y adaptación institucional');
    }

    public function test_disparar_abrir_evaluacion_emite_evento_hacia_modal_de_area(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('abrirEvaluacion', 'ARE_COG')
            ->assertDispatched('evaluacion-geriatrica-area-abrir', [
                'cod_residente' => $this->residente->getKey(),
                'cod_area' => 'ARE_COG',
            ]);
    }

    public function test_psicologo_puede_guardar_valoracion_afectiva_vinculada_a_atencion_y_soporte_c04(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'afectiva')
            ->set('formAfectiva.estado_animo', 'EUTIMICO')
            ->set('formAfectiva.afecto', 'ADECUADO')
            ->set('formAfectiva.ansiedad', 'AUSENTE')
            ->set('formAfectiva.apatia', 'AUSENTE')
            ->set('formAfectiva.conducta', 'Colaboradora y orientada')
            ->set('formAfectiva.conclusion', 'Se descarta sintomatología depresiva mayor o ansiedad que interfiera con pruebas.')
            ->set('formAfectiva.cod_atencion', 'ATE_MANIPULADA')
            ->set('formAfectiva.cod_personal', 'PER_MANIPULADO')
            ->call('guardarValoracionAfectiva')
            ->assertHasNoErrors()
            ->assertSee('Valoración afectiva registrada correctamente');

        $this->assertDatabaseHas('valoraciones_psicologicas', [
            'cod_residente' => $this->residente->getKey(),
            'estado_animo' => 'EUTIMICO',
            'ansiedad' => 'AUSENTE',
            'estado' => 'VIGENTE',
            'cod_personal' => $this->psicologo->personal->getKey(),
        ]);

        $this->assertDatabaseHas('atenciones', [
            'cod_residente' => $this->residente->getKey(),
            'tipo_atencion' => 'VALORACION_AFECTIVA',
            'estado' => 'FINALIZADA',
        ]);
    }

    public function test_psicologia_solo_consulta_iadl_sin_exponer_captura(): void
    {
        $this->actingAs($this->psicologo);
        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'funcionamiento')->assertDontSee('Nueva observación IADL');
        self::assertFalse(method_exists(FichaPsicologicaResidente::class, 'guardarControlIadl'));
        self::assertDatabaseCount('controles_funcionales_iadl', 0);
    }

    public function test_formulario_afectivo_no_precarga_hallazgos_y_conserva_cambios_al_cancelar(): void
    {
        $this->actingAs($this->psicologo);
        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->assertSet('formAfectiva.estado_animo', '')->assertSet('formAfectiva.ansiedad', '')
            ->call('alternarFormularioAfectivo')->set('formAfectiva.conducta', 'Observación sintética pendiente')
            ->call('alternarFormularioAfectivo')->assertSet('confirmarDescarteAfectiva', true)
            ->call('conservarValoracionAfectiva')->assertSet('formAfectiva.conducta', 'Observación sintética pendiente')
            ->call('alternarFormularioAfectivo')->call('descartarValoracionAfectiva')
            ->assertSet('mostrarFormAfectiva', false)->assertSet('formAfectiva.conducta', '');
        self::assertDatabaseCount('valoraciones_psicologicas', 0);
    }

    public function test_entrevista_muestra_referencia_educativa_sin_modificar_el_expediente(): void
    {
        $this->residente->update(['nivel_educativo' => 'Secundaria incompleta']);
        $antes = $this->residente->fresh()->getAttributes();
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'sesiones')->set('mostrarFormSesion', true)
            ->assertSee('Escolaridad general del expediente:')->assertSee('Secundaria incompleta')
            ->assertSee('no indica los años cursados')
            ->assertSee('Idioma y comunicación')->assertSee('Fuente del relato');

        self::assertSame($antes, $this->residente->fresh()->getAttributes());
        self::assertDatabaseCount('atenciones', 0);
    }

    public function test_entrevista_sin_escolaridad_conserva_la_ausencia_y_el_relato_original(): void
    {
        $this->residente->update(['nivel_educativo' => null]);
        $this->actingAs($this->psicologo);
        $relato = 'Idioma referido: español. No se conoce el nivel previo; no participó un informante.';

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'sesiones')->set('mostrarFormSesion', true)->assertSee('No registrada')
            ->set('formSesion.tipo_atencion', 'SEGUIMIENTO')->set('formSesion.motivo', 'Entrevista sintética')
            ->set('formSesion.observacion', $relato)->call('guardarSesion')->assertHasNoErrors();

        self::assertNull($this->residente->fresh()->nivel_educativo);
        self::assertDatabaseHas('atenciones', ['cod_residente' => $this->residente->getKey(), 'observacion' => $relato]);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('valoraciones_petersen_originales', 0);
    }

    public function test_contexto_de_sesion_conserva_cero_no_y_si_sin_cambiar_admision(): void
    {
        $this->actingAs($this->psicologo);
        $this->residente->update(['nivel_educativo' => 'Dato general de admisión']);
        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'sesiones')->set('mostrarFormSesion', true)
            ->set('formSesion.motivo', 'Entrevista sintética con contexto')
            ->set('formSesion.escolaridad_anios', '0')->set('formSesion.puede_leer', '0')->set('formSesion.puede_escribir', '1')
            ->call('guardarSesion')->assertHasNoErrors()->assertSee('0 años')
            ->assertSet('formSesion.escolaridad_anios', '')->assertSet('formSesion.puede_leer', '');

        $atencion = Atencion::sole();
        self::assertSame(0, $atencion->escolaridad_anios);
        self::assertFalse($atencion->puede_leer);
        self::assertTrue($atencion->puede_escribir);
        self::assertSame($this->psicologo->personal->getKey(), $atencion->cod_personal);
        self::assertSame('Dato general de admisión', $this->residente->fresh()->nivel_educativo);
        self::assertDatabaseHas('activity_log', ['event' => 'sesion_psicologica_registrada', 'subject_id' => $atencion->getKey(), 'causer_id' => $this->psicologo->getKey()]);
        self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public static function contextoInvalido(): array
    {
        return [['escolaridad_anios', '-1'], ['escolaridad_anios', '1.5'], ['escolaridad_anios', '32768'],
            ['puede_leer', 'quizás'], ['puede_escribir', 2]];
    }

    #[DataProvider('contextoInvalido')]
    public function test_contexto_invalido_conserva_captura_y_no_escribe(string $campo, mixed $valor): void
    {
        $this->actingAs($this->psicologo);
        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'sesiones')->set('mostrarFormSesion', true)->set('formSesion.motivo', 'Sesión sintética')
            ->set('formSesion.'.$campo, $valor)->call('guardarSesion')
            ->assertHasErrors('formSesion.'.$campo)->assertSet('formSesion.'.$campo, $valor)->assertSet('mostrarFormSesion', true);
        self::assertDatabaseCount('atenciones', 0);
    }

    public function test_backend_no_admite_autoria_manipulada_para_el_contexto(): void
    {
        try {
            app(RegistrarSesionPsicologica::class)->registrar($this->psicologo, $this->residente,
                ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Contexto sintético', 'cod_personal' => $this->enfermero->personal->getKey()]);
            self::fail('Debe rechazar autoría enviada por el cliente.');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('cod_personal', $e->errors());
        }
        self::assertDatabaseCount('atenciones', 0);
    }

    public function test_backend_revalida_cuenta_y_permiso_revocados(): void
    {
        $this->psicologo->roles()->first()->revokePermissionTo('atenciones.crear');
        try {
            app(RegistrarSesionPsicologica::class)->registrar($this->psicologo, $this->residente,
                ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Contexto sintético', 'escolaridad_anios' => 0]);
            self::fail('Debe rechazar el permiso revocado.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
        }
        self::assertDatabaseCount('atenciones', 0);
    }

    public static function actorSinContextoAutorizado(): array
    {
        return [['cuenta'], ['personal'], ['rol'], ['familiar'], ['area'], ['residente']];
    }

    #[DataProvider('actorSinContextoAutorizado')]
    public function test_backend_rechaza_actor_y_contexto_no_autorizados(string $caso): void
    {
        match ($caso) {
            'cuenta' => $this->psicologo->update(['estado' => 'INACTIVO']),
            'personal' => $this->psicologo->personal->update(['estado' => 'INACTIVO']),
            'rol' => $this->psicologo->syncRoles(['ENFERMEROS']),
            'familiar' => $this->psicologo->assignRole('FAMILIAR'),
            'area' => Area::query()->update(['estado' => 'INACTIVA']),
            'residente' => $this->residente->update(['estado' => 'EGRESADO']),
        };
        try {
            app(RegistrarSesionPsicologica::class)->registrar($this->psicologo, $this->residente,
                ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Contexto sintético', 'puede_leer' => false]);
            self::fail('Debe rechazar el actor o contexto no autorizado.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
        }
        self::assertDatabaseCount('atenciones', 0);
    }

    public function test_fallo_de_auditoria_revierte_atencion_y_contexto(): void
    {
        \Spatie\Activitylog\Models\Activity::creating(static function (): void {
            throw new \RuntimeException('Fallo sintético de auditoría');
        });
        try {
            app(RegistrarSesionPsicologica::class)->registrar($this->psicologo, $this->residente,
                ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Contexto sintético', 'escolaridad_anios' => 0]);
            self::fail('No debe anunciar un registro incompleto.');
        } catch (\RuntimeException $e) {
            self::assertSame('Fallo sintético de auditoría', $e->getMessage());
        }
        self::assertDatabaseCount('atenciones', 0);
    }

    public function test_sesion_posterior_no_sobrescribe_contexto_ni_completa_desconocidos(): void
    {
        $accion = app(RegistrarSesionPsicologica::class);
        $primera = $accion->registrar($this->psicologo, $this->residente,
            ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Primera sesión sintética', 'escolaridad_anios' => 0, 'puede_leer' => false]);
        $segunda = $accion->registrar($this->psicologo, $this->residente,
            ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Otra sesión sintética']);
        self::assertSame(0, $primera->fresh()->escolaridad_anios);
        self::assertFalse($primera->fresh()->puede_leer);
        foreach (Atencion::CONTEXTO_ESCOLARIDAD as $campo) self::assertNull($segunda->getAttribute($campo));
        try {
            $primera->update(['escolaridad_anios' => 10]);
            self::fail('No debe sobrescribir el contexto conservado.');
        } catch (\LogicException $e) {
            self::assertStringContainsString('no se sobrescribe', $e->getMessage());
        }
        self::assertSame(0, $primera->fresh()->escolaridad_anios);
        self::assertDatabaseCount('atenciones', 2);
    }

    public function test_ampliacion_y_reversion_vacias_conservan_filas_y_tablas(): void
    {
        $accion = app(RegistrarSesionPsicologica::class);
        $a = $accion->registrar($this->psicologo, $this->residente, ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Historia sintética previa']);
        $originales = array_diff(Schema::getColumnListing('atenciones'), Atencion::CONTEXTO_ESCOLARIDAD);
        $antes = (array) DB::table('atenciones')->where('cod_atencion', $a->getKey())->first($originales);
        $tablas = count(Schema::getTables());
        $migracion = require base_path('database/migrations/2026_10_28_010000_add_contexto_escolaridad_atenciones.php');
        $migracion->down();
        self::assertFalse(Schema::hasColumn('atenciones', 'escolaridad_anios'));
        self::assertSame($antes, (array) DB::table('atenciones')->where('cod_atencion', $a->getKey())->first($originales));
        $migracion->up();
        self::assertSame($tablas, count(Schema::getTables()));
        self::assertSame($antes, (array) DB::table('atenciones')->where('cod_atencion', $a->getKey())->first($originales));
        foreach (Atencion::CONTEXTO_ESCOLARIDAD as $campo) self::assertNull($a->fresh()->getAttribute($campo));
    }

    public function test_rollback_con_datos_se_rechaza_sin_perder_el_contexto(): void
    {
        $a = app(RegistrarSesionPsicologica::class)->registrar($this->psicologo, $this->residente,
            ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Contexto sintético', 'puede_escribir' => false]);
        $migracion = require base_path('database/migrations/2026_10_28_010000_add_contexto_escolaridad_atenciones.php');
        try { $migracion->down(); self::fail('No debe eliminar contexto clínico.'); }
        catch (\LogicException $e) { self::assertStringContainsString('no se elimina', $e->getMessage()); }
        self::assertFalse($a->fresh()->puede_escribir);
        self::assertTrue(Schema::hasColumn('atenciones', 'escolaridad_anios'));
    }

    public function test_sql_directo_rechaza_contexto_invalido(): void
    {
        $a = app(RegistrarSesionPsicologica::class)->registrar($this->psicologo, $this->residente,
            ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Contexto sintético']);
        foreach (['escolaridad_anios' => -1, 'puede_leer' => 2] as $campo => $valor) {
            try {
                DB::transaction(fn () => DB::table('atenciones')->where('cod_atencion', $a->getKey())->update([$campo => $valor]));
                self::fail('SQL debe rechazar el dato inválido.');
            } catch (QueryException $e) { self::assertNotEmpty($e->getCode()); }
            self::assertNull($a->fresh()->getAttribute($campo));
        }
    }

    private function informanteSintetico(?Residente $residente = null): \App\Models\ResidenteContacto
    {
        $contacto = \App\Models\Contacto::create(['nombres' => 'Informante', 'apellido_paterno' => 'Sintético', 'estado' => 'ACTIVO',
            'correo' => 'reservado@example.invalid', 'telefono' => 'NO-PUBLICAR']);

        return \App\Models\ResidenteContacto::create(['cod_residente_contacto' => 'RC_'.Str::random(10),
            'cod_residente' => ($residente ?? $this->residente)->getKey(), 'cod_contacto' => $contacto->getKey(),
            'parentesco' => 'FAMILIAR', 'responsable_principal' => true, 'contacto_emergencia' => false,
            'autoriza_informacion' => false, 'autoriza_salida' => false, 'estado' => 'ACTIVO']);
    }

    public function test_contexto_completo_se_conserva_y_no_elige_responsable_por_defecto(): void
    {
        $vinculo = $this->informanteSintetico();
        $this->psicologo->givePermissionTo(['contactos.ver', 'residentes_contactos.ver']);
        $this->actingAs($this->psicologo);
        $form = Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'seguimiento')->call('alternarFormularioSesion')
            ->assertSet('formSesion.cod_residente_contacto_informante', '')
            ->set('formSesion.motivo', 'Entrevista sintética con fuente identificada')
            ->set('formSesion.idioma_principal_evaluacion', '  Quechua  ')
            ->set('formSesion.cod_residente_contacto_informante', $vinculo->getKey())
            ->set('formSesion.informante_conoce_nivel_previo', '0')
            ->call('guardarSesion')->assertHasNoErrors()->assertSee('Quechua')->assertSee('Informante Sintético')
            ->assertDontSee('reservado@example.invalid')->assertDontSee('NO-PUBLICAR')
            ->assertSet('formSesion.idioma_principal_evaluacion', '');
        $atencion = Atencion::sole();
        self::assertSame('Quechua', $atencion->idioma_principal_evaluacion);
        self::assertSame($vinculo->getKey(), $atencion->cod_residente_contacto_informante);
        self::assertFalse($atencion->informante_conoce_nivel_previo);
        self::assertSame($this->psicologo->personal->getKey(), $atencion->cod_personal);
        $vinculo->update(['estado' => 'INACTIVO']);
        $form->call('$refresh')->assertSee('Informante Sintético')->call('alternarFormularioSesion')
            ->assertViewHas('informantesSesion', fn ($v) => $v->isEmpty());
    }

    public static function contextoIdiomaInvalido(): array
    {
        return [['idioma_principal_evaluacion', str_repeat('a', 81)], ['idioma_principal_evaluacion', ['Español']],
            ['idioma_principal_evaluacion', 'Español, Quechua'], ['informante_conoce_nivel_previo', 'quizás'],
            ['informante_conoce_nivel_previo', false], ['cod_residente_contacto_informante', str_repeat('x', 21)]];
    }

    #[DataProvider('contextoIdiomaInvalido')]
    public function test_contexto_invalido_no_se_guarda_y_conserva_la_entrevista(string $campo, mixed $valor): void
    {
        $this->actingAs($this->psicologo);
        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'seguimiento')->set('mostrarFormSesion', true)->set('formSesion.motivo', 'Relato sintético conservado')
            ->set('formSesion.'.$campo, $valor)->call('guardarSesion')->assertHasErrors('formSesion.'.$campo)
            ->assertSet('mostrarFormSesion', true)->assertSet('formSesion.motivo', 'Relato sintético conservado');
        self::assertDatabaseCount('atenciones', 0);
    }

    public function test_backend_rechaza_informante_ajeno_inactivo_o_inexistente(): void
    {
        $this->psicologo->givePermissionTo(['contactos.ver', 'residentes_contactos.ver']);
        $ajeno = $this->informanteSintetico(Residente::factory()->create(['estado' => 'ADMITIDO']));
        $inactivo = $this->informanteSintetico();
        $inactivo->update(['estado' => 'INACTIVO']);
        $contactoInactivo = $this->informanteSintetico();
        $contactoInactivo->contacto->update(['estado' => 'INACTIVO']);
        foreach ([$ajeno->getKey(), $inactivo->getKey(), $contactoInactivo->getKey(), 'NO_EXISTE'] as $codigo) {
            try {
                app(RegistrarSesionPsicologica::class)->registrar($this->psicologo, $this->residente,
                    ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Entrevista sintética', 'cod_residente_contacto_informante' => $codigo]);
                self::fail('No debe vincular el informante.');
            } catch (ValidationException $e) {
                self::assertArrayHasKey('cod_residente_contacto_informante', $e->errors());
            }
        }
        self::assertDatabaseCount('atenciones', 0);
    }

    public function test_sin_permisos_de_contactos_no_se_consulta_identidad_ni_se_guarda_un_informante(): void
    {
        $vinculo = $this->informanteSintetico();
        $this->psicologo->givePermissionTo(['contactos.ver', 'residentes_contactos.ver']);
        $a = app(RegistrarSesionPsicologica::class)->registrar($this->psicologo, $this->residente,
            ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Relato sintético', 'cod_residente_contacto_informante' => $vinculo->getKey()]);
        $this->psicologo->revokePermissionTo(['contactos.ver', 'residentes_contactos.ver']);
        $this->psicologo->roles()->first()->revokePermissionTo(['contactos.ver', 'residentes_contactos.ver']);
        $this->actingAs($this->psicologo);
        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'seguimiento')->set('mostrarFormSesion', true)
            ->assertDontSee('Informante Sintético')->assertSee('Consulta restringida por permisos de contactos.')
            ->assertViewHas('informantesSesion', fn ($v) => $v->isEmpty());
        try {
            app(RegistrarSesionPsicologica::class)->registrar($this->psicologo, $this->residente,
                ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Relato sintético', 'cod_residente_contacto_informante' => $vinculo->getKey()]);
            self::fail('Requiere permisos de lectura de contactos.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
        }
        self::assertDatabaseCount('atenciones', 1);
        self::assertSame($vinculo->getKey(), $a->fresh()->cod_residente_contacto_informante);
    }

    public function test_cambiar_informante_limpia_su_respuesta_y_cierre_sucio_pide_descarte(): void
    {
        $vinculo = $this->informanteSintetico();
        $this->psicologo->givePermissionTo(['contactos.ver', 'residentes_contactos.ver']);
        $this->actingAs($this->psicologo);
        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'seguimiento')->call('alternarFormularioSesion')
            ->set('formSesion.cod_residente_contacto_informante', $vinculo->getKey())
            ->set('formSesion.informante_conoce_nivel_previo', '1')
            ->set('formSesion.cod_residente_contacto_informante', '')->assertSet('formSesion.informante_conoce_nivel_previo', '')
            ->set('formSesion.escolaridad_anios', '0')->call('alternarFormularioSesion')
            ->assertSet('mostrarFormSesion', true)->assertSet('confirmarDescarteSesion', true)
            ->call('conservarSesion')->assertSet('formSesion.escolaridad_anios', '0')
            ->call('alternarFormularioSesion')->call('descartarSesion')
            ->assertSet('mostrarFormSesion', false)->assertSet('formSesion.escolaridad_anios', '')
            ->call('alternarFormularioSesion')->set('formSesion.tipo_atencion', 'ENTREVISTA_FAMILIAR')
            ->call('alternarFormularioSesion')->assertSet('confirmarDescarteSesion', true)
            ->call('descartarSesion')->assertSet('formSesion.tipo_atencion', 'SEGUIMIENTO');
        self::assertDatabaseCount('atenciones', 0);
    }

    public function test_extension_idioma_revierte_solo_sin_contexto_y_preserva_filas(): void
    {
        $a = app(RegistrarSesionPsicologica::class)->registrar($this->psicologo, $this->residente,
            ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Atención sintética previa']);
        $originales = array_diff(Schema::getColumnListing('atenciones'), Atencion::CONTEXTO_IDIOMA_INFORMANTE);
        $antes = (array) DB::table('atenciones')->where('cod_atencion', $a->getKey())->first($originales);
        $tablas = count(Schema::getTables());
        $m = require base_path('database/migrations/2026_10_29_010000_add_idioma_informante_atenciones.php');
        $m->down();
        self::assertFalse(Schema::hasColumn('atenciones', 'idioma_principal_evaluacion'));
        self::assertSame($antes, (array) DB::table('atenciones')->where('cod_atencion', $a->getKey())->first($originales));
        $m->up();
        self::assertSame($tablas, count(Schema::getTables()));
        self::assertSame($antes, (array) DB::table('atenciones')->where('cod_atencion', $a->getKey())->first($originales));
        foreach (Atencion::CONTEXTO_IDIOMA_INFORMANTE as $campo) self::assertNull($a->fresh()->getAttribute($campo));
        $a->update(['idioma_principal_evaluacion' => null]);
        DB::table('atenciones')->where('cod_atencion', $a->getKey())->update(['idioma_principal_evaluacion' => 'Español']);
        try { $m->down(); self::fail('No elimina contexto existente.'); }
        catch (\LogicException $e) { self::assertStringContainsString('no se elimina', $e->getMessage()); }
        self::assertSame('Español', $a->fresh()->idioma_principal_evaluacion);
        try { $a->fresh()->update(['idioma_principal_evaluacion' => 'Quechua']); self::fail('No sobrescribe.'); }
        catch (\LogicException $e) { self::assertStringContainsString('no se sobrescribe', $e->getMessage()); }
    }

    public function test_sql_rechaza_fk_inexistente_y_conocimiento_sin_informante(): void
    {
        $a = app(RegistrarSesionPsicologica::class)->registrar($this->psicologo, $this->residente,
            ['tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Atención sintética']);
        foreach ([['cod_residente_contacto_informante' => 'NO_EXISTE'], ['informante_conoce_nivel_previo' => false], ['idioma_principal_evaluacion' => ' ']] as $datos) {
            try {
                DB::transaction(fn () => DB::table('atenciones')->where('cod_atencion', $a->getKey())->update($datos));
                self::fail('SQL debe rechazar el contexto inválido.');
            } catch (QueryException $e) { self::assertNotEmpty($e->getCode()); }
        }
        $v = $this->informanteSintetico();
        DB::table('atenciones')->where('cod_atencion', $a->getKey())->update(['cod_residente_contacto_informante' => $v->getKey()]);
        try { DB::transaction(fn () => DB::table('residentes_contactos')->where('cod_residente_contacto', $v->getKey())->delete()); self::fail('FK restringe el borrado.'); }
        catch (QueryException $e) { self::assertNotEmpty($e->getCode()); }
        self::assertSame($v->getKey(), $a->fresh()->cod_residente_contacto_informante);
    }

    public function test_psicologo_puede_registrar_sesion_de_atencion(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'sesiones')
            ->set('formSesion.tipo_atencion', 'SEGUIMIENTO')
            ->set('formSesion.motivo', 'Seguimiento de adaptación residencial')
            ->set('formSesion.observacion', 'Residente refiere buena integración con compañeros de habitación.')
            ->call('guardarSesion')
            ->assertHasNoErrors()
            ->assertSee('Sesión de atención psicológica registrada exitosamente');

        $this->assertDatabaseHas('atenciones', [
            'cod_residente' => $this->residente->getKey(),
            'tipo_atencion' => 'SEGUIMIENTO',
            'motivo' => 'Seguimiento de adaptación residencial',
            'estado' => 'FINALIZADA',
        ]);
    }

    public function test_cambiar_periodo_y_metrica_de_tendencia_longitudinal(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->assertSee('Trayectoria y tendencias longitudinales')
            ->call('cambiarPeriodoTendencia', 12)
            ->assertSet('periodoMeses', 12)
            ->call('cambiarMetricaTendencia', 'afectivo')
            ->assertSet('metricaTendencia', 'afectivo')
            ->call('toggleTablaTendencia')
            ->assertSet('mostrarTablaTendencia', true);
    }

    public function test_tendencia_cognitiva_con_multiples_puntos_calcula_resumen_y_datasets(): void
    {
        $this->actingAs($this->psicologo);

        $inst = Instrumento::firstOrCreate(
            ['cod_instrumento' => 'INS_COG_MMSE_TEST'],
            ['codigo' => 'MMSE_TEST', 'nombre' => 'Minimental Test', 'tipo' => 'COGNITIVO', 'estado' => 'ACTIVO']
        );

        $personal = $this->psicologo->personal;
        $area = Area::firstOrCreate(['cod_area' => 'ARE_COG'], ['nombre' => 'Cognitiva', 'estado' => 'ACTIVA']);

        $atencion1 = Atencion::create([
            'cod_atencion' => 'ATE_TEND_01',
            'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $personal->getKey(),
            'cod_area' => $area->getKey(),
            'tipo_atencion' => 'VALORACION',
            'fecha_hora' => now()->subMonths(2),
            'estado' => 'FINALIZADA',
        ]);

        AplicacionInstrumento::create([
            'cod_aplicacion' => 'APP_TEND_01',
            'cod_residente' => $this->residente->getKey(),
            'cod_instrumento' => $inst->getKey(),
            'cod_personal' => $personal->getKey(),
            'cod_usuario_registro' => $this->psicologo->getKey(),
            'registrado_en' => now()->subMonths(2),
            'cod_atencion' => $atencion1->getKey(),
            'fecha_hora' => now()->subMonths(2),
            'puntaje_total' => 24.0,
            'puntaje_maximo' => 30.0,
            'estado' => 'APLICADO',
        ]);

        $atencion2 = Atencion::create([
            'cod_atencion' => 'ATE_TEND_02',
            'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $personal->getKey(),
            'cod_area' => $area->getKey(),
            'tipo_atencion' => 'VALORACION',
            'fecha_hora' => now()->subMonths(1),
            'estado' => 'FINALIZADA',
        ]);

        AplicacionInstrumento::create([
            'cod_aplicacion' => 'APP_TEND_02',
            'cod_residente' => $this->residente->getKey(),
            'cod_instrumento' => $inst->getKey(),
            'cod_personal' => $personal->getKey(),
            'cod_usuario_registro' => $this->psicologo->getKey(),
            'registrado_en' => now()->subMonths(1),
            'cod_atencion' => $atencion2->getKey(),
            'fecha_hora' => now()->subMonths(1),
            'puntaje_total' => 27.0,
            'puntaje_maximo' => 30.0,
            'estado' => 'APLICADO',
        ]);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->assertSee('Trayectoria y tendencias longitudinales')
            ->assertSee('Minimental Test')
            ->assertSee('Evolución favorable');
    }

    public function test_psicologo_puede_navegar_a_pestana_orion_y_consultar_criterios_petersen(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->assertSee('Resultados Apoyo a la decisión')
            ->call('cambiarTab', 'orion')
            ->assertSet('tab', 'orion')
            ->assertSee('Motor Experto Apoyo a la decisión')
            ->assertSee('Criterios diagnósticos Petersen')
            ->assertSee('C01 · Queja cognitiva confirmada')
            ->assertSee('C02 · Deterioro cognitivo objetivo')
            ->assertSee('C03 · Preservación de actividades funcionales (IADL)')
            ->assertSee('C04 · Ausencia de demencia / Criterio diferencial')
            ->assertSee('C05 · Juicio clínico global interdisciplinario')
            ->assertSee('persistido = false');
    }
}
