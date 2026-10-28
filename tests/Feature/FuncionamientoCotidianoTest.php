<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\{Area, AsignacionPersonal, Atencion, ControlFuncionalIadl as Iadl, Jornada, Personal, Residente, Turno, User};
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarAutonomiaDescriptiva;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarControlFuncionalIadl as Registrar;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\FuncionamientoCotidianoPanel as Panel;
use App\Modulos\Valoraciones\Presentacion\Soporte\OpcionesFuncionamientoCotidiano as Opciones;
use Database\Seeders\{RolesAndPermissionsSeeder, PermisosCapturaFuncionamientoCotidianoSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Event, Gate};
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class FuncionamientoCotidianoTest extends TestCase
{
    use RefreshDatabase;

    private User $psicologo;
    private User $medico;
    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 28)->setTime(12, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
        $area = Area::create(['cod_area' => 'ARE_FUN', 'nombre' => 'Área sintética funcional', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        foreach (['PSICOLOGO/A' => 'PSICOLOGIA', 'MEDICO GENERAL/GERIATRA' => 'MEDICINA'] as $rol => $profesion) {
            $user = User::factory()->create(['estado' => 'ACTIVO']);
            $user->assignRole($rol);
            $personal = Personal::create(['cod_usuario' => $user->getKey(), 'nombres' => 'Profesional sintético', 'apellido_paterno' => $profesion, 'numero_documento' => 'DOC-'. $profesion, 'profesion' => $profesion, 'estado' => 'ACTIVO']);
            AsignacionPersonal::create(['cod_personal' => $personal->getKey(), 'cod_jornada' => $jornada->getKey(), 'cod_area' => $area->getKey(), 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
            if ($profesion === 'PSICOLOGIA') { $this->psicologo = $user; } else { $this->medico = $user; }
        }
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
    }

    private function entrada(array $cambios = []): array
    {
        return array_replace(['fecha_hora' => '2026-10-28T10:00', 'actividad' => 'RUTINAS', 'oportunidad' => 'SIN_OPORTUNIDAD',
            'desempeno' => 'NO_EVALUABLE', 'tipo_ayuda' => 'NO_APLICA', 'causa_ayuda' => 'INSTITUCIONAL',
            'nivel_previo' => 'DESCONOCIDO', 'fuente' => 'EQUIPO_CUIDADOS', 'cod_atencion' => '', 'observacion' => 'Relato sintético con oportunidad limitada.'], $cambios);
    }

    private function registrar(string $id = 'IAD_QA_001', array $cambios = [], ?string $anterior = null, ?User $usuario = null): Iadl
    {
        return app(Registrar::class)->registrar($usuario ?? $this->psicologo, $this->residente, $this->entrada($cambios), $id, $anterior);
    }

    public function test_medicina_y_psicologia_guardan_con_autoria_y_sin_inferir_normalidad(): void
    {
        foreach ([$this->medico, $this->psicologo] as $i => $usuario) {
            $registro = $this->registrar('IAD_QA_00'.$i, ['observacion' => '0'], usuario: $usuario);
            self::assertSame($usuario->personal->getKey(), $registro->cod_personal);
            self::assertSame($usuario->getKey(), $registro->cod_usuario_registro);
            self::assertSame('0', $registro->observacion);
            self::assertSame('NO_EVALUABLE', $registro->desempeno);
            self::assertNull($registro->cod_atencion);
            self::assertSame('2026-10-28 10:00:00', $registro->fecha_hora->format('Y-m-d H:i:s'));
        }
        self::assertDatabaseCount('controles_funcionales_iadl', 2);
        self::assertDatabaseCount('atenciones', 0);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
        $resultado = app(ConsultarAutonomiaDescriptiva::class)->capturar($this->psicologo, $this->residente, now()->toImmutable());
        self::assertFalse($resultado['acredita_independencia']);
        self::assertCount(2, $resultado['registros']);
    }

    public function test_reintento_identico_no_duplica_y_reintento_divergente_no_sobrescribe(): void
    {
        $uno = $this->registrar();
        self::assertSame($uno->getKey(), $this->registrar()->getKey());
        try { $this->registrar(cambios: ['observacion' => 'Otra captura sintética.']); self::fail('Debe rechazar el reintento divergente.'); }
        catch (ValidationException $e) { self::assertArrayHasKey('registro', $e->errors()); }
        self::assertDatabaseCount('controles_funcionales_iadl', 1);
        self::assertSame('Relato sintético con oportunidad limitada.', $uno->fresh()->observacion);
    }

    public function test_rectificar_conserva_original_y_el_lector_excluye_la_version_superada(): void
    {
        $original = $this->registrar();
        $correccion = $this->registrar('IAD_QA_002', ['observacion' => 'Se rectifica la oportunidad tras revisar el relato sintético.', 'oportunidad' => 'REFERIDA'], $original->getKey(), $this->medico);
        self::assertSame('RECTIFICACION', $correccion->estado);
        self::assertSame($original->getKey(), $correccion->cod_control_anterior);
        self::assertSame('SIN_OPORTUNIDAD', $original->fresh()->oportunidad);
        self::assertSame($this->psicologo->personal->getKey(), $original->fresh()->cod_personal);
        $lectura = app(ConsultarAutonomiaDescriptiva::class)->capturar($this->medico, $this->residente, now()->toImmutable());
        self::assertContains('ORIGINAL_RECTIFICADO_AL_CORTE', $lectura['registros'][0]['motivos']);
        self::assertTrue($lectura['registros'][1]['admisible_como_fuente_descriptiva']);
        try { $this->registrar('IAD_QA_003', anterior: $original->getKey()); self::fail('No puede bifurcar la rectificación.'); }
        catch (ValidationException $e) { self::assertArrayHasKey('registro', $e->errors()); }
        self::assertDatabaseCount('controles_funcionales_iadl', 2);
    }

    public function test_rechaza_atencion_de_otro_residente_sin_efectos(): void
    {
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $atencion = Atencion::create(['cod_residente' => $otro->getKey(), 'cod_personal' => $this->medico->personal->getKey(), 'cod_area' => 'ARE_FUN', 'tipo_atencion' => 'SEGUIMIENTO', 'fecha_hora' => now(), 'estado' => 'FINALIZADA']);
        $this->expectException(ValidationException::class);
        try { $this->registrar(cambios: ['cod_atencion' => $atencion->getKey()]); }
        finally { self::assertDatabaseCount('controles_funcionales_iadl', 0); }
    }

    #[DataProvider('entradasInvalidas')]
    public function test_rechaza_valores_invalidos_y_atribucion_manipulada(string $campo, mixed $valor): void
    {
        $this->expectException(ValidationException::class);
        try { $this->registrar(cambios: [$campo => $valor]); }
        finally { self::assertDatabaseCount('controles_funcionales_iadl', 0); }
    }

    public static function entradasInvalidas(): array
    {
        return [['actividad', 'OTRA_NO_APROBADA'], ['oportunidad', ''], ['fecha_hora', '2026-10-29T10:00'],
            ['fecha_hora', 'ayer'], ['cod_personal', 'OTRO_AUTOR'], ['cod_usuario_registro', 'OTRA_CUENTA'],
            ['estado', 'ANULADO'], ['cod_residente', 'OTRO_RESIDENTE'], ['registrado_en', '2026-01-01']];
    }

    public function test_campos_protegidos_vacios_no_sobrescriben_la_identidad_derivada(): void
    {
        $r = $this->registrar(cambios: ['cod_personal' => null, 'cod_usuario_registro' => '', 'estado' => null]);
        self::assertSame($this->psicologo->personal->getKey(), $r->cod_personal);
        self::assertSame('REGISTRADO', $r->estado);
    }

    public function test_sin_permiso_o_contexto_profesional_no_hay_escritura(): void
    {
        $this->psicologo->roles->first()->revokePermissionTo('controles_funcionales_iadl.crear');
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        try { $this->registrar(); } finally { self::assertDatabaseCount('controles_funcionales_iadl', 0); }
    }

    #[DataProvider('rolesAjenos')]
    public function test_otros_roles_no_escriben_aun_con_permisos_manipulados(string $rol): void
    {
        $this->psicologo->syncRoles([$rol]);
        $this->psicologo->givePermissionTo(['controles_funcionales_iadl.ver', 'controles_funcionales_iadl.crear', 'controles_funcionales_iadl.rectificar']);
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        try { $this->registrar(); } finally { self::assertDatabaseCount('controles_funcionales_iadl', 0); }
    }

    public static function rolesAjenos(): array
    {
        return [['SUPERADMINISTRADOR'], ['ADMINISTRADOR'], ['GERENTE'], ['ENFERMEROS'], ['FISIOTERAPEUTA'], ['FAMILIAR']];
    }

    #[DataProvider('contextosInvalidos')]
    public function test_cuenta_personal_residente_o_preview_invalidos_sin_escritura(string $tipo): void
    {
        match ($tipo) {
            'cuenta' => $this->psicologo->update(['estado' => 'INACTIVO']),
            'personal' => $this->psicologo->personal->update(['estado' => 'INACTIVO']),
            'profesion' => $this->psicologo->personal->update(['profesion' => 'MEDICINA']),
            'jornada' => DB::table('asignaciones_personal')->update(['estado' => 'INACTIVA']),
            'residente' => $this->residente->update(['estado' => 'EGRESADO']),
            'preview' => session()->put(RolePreviewService::SESSION_KEY, 'PSICOLOGO/A'),
        };
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        try { $this->registrar(); } finally { self::assertDatabaseCount('controles_funcionales_iadl', 0); }
    }

    public static function contextosInvalidos(): array
    {
        return [['cuenta'], ['personal'], ['profesion'], ['jornada'], ['residente'], ['preview']];
    }

    public function test_error_de_auditoria_revierte_la_observacion(): void
    {
        $evento = 'eloquent.creating: Spatie\\Activitylog\\Models\\Activity';
        Event::listen($evento, static fn () => throw new \RuntimeException('Fallo sintético de auditoría'));
        $this->expectException(\RuntimeException::class);
        try { $this->registrar(); }
        finally { Event::forget($evento); self::assertDatabaseCount('controles_funcionales_iadl', 0); }
    }

    public function test_livewire_revisa_conserva_corrige_y_no_guarda_sin_confirmar(): void
    {
        $this->actingAs($this->psicologo);
        $ui = Livewire::test(Panel::class, ['residente' => $this->residente])->call('nuevo');
        self::assertSame('', $ui->get('form')['desempeno']);
        $ui->set('form', $this->entrada())->call('guardar')->assertHasErrors('registro');
        self::assertDatabaseCount('controles_funcionales_iadl', 0);
        $ui->call('revisar')->assertSet('revision', true)->assertSee('No se pudo evaluar');
        self::assertDatabaseCount('controles_funcionales_iadl', 0);
        $ui->call('volver')->assertSet('revision', false)->assertSet('form.nivel_previo', 'DESCONOCIDO');
        $ui->call('revisar')->call('guardar')->assertHasNoErrors()->assertSee('Observación de funcionamiento cotidiano registrada.');
        $id = Iadl::sole()->getKey();
        $ui->call('rectificar', $id)->call('revisar')->assertHasErrors('form.observacion');
        $ui->set('form.observacion', 'Motivo sintético de la rectificación.')->call('revisar')->call('guardar')->assertHasNoErrors();
        self::assertDatabaseCount('controles_funcionales_iadl', 2);
        self::assertSame('SIN_OPORTUNIDAD', Iadl::findOrFail($id)->oportunidad);
    }

    public function test_cancelar_solicita_descarte_y_conserva_valores_hasta_confirmar(): void
    {
        $this->actingAs($this->psicologo);
        Livewire::test(Panel::class, ['residente' => $this->residente])->call('nuevo')->set('form', $this->entrada())
            ->call('cerrar')->assertSet('confirmarDescarte', true)->assertSet('form.fuente', 'EQUIPO_CUIDADOS')
            ->call('volver')->assertSet('confirmarDescarte', false)->call('descartar')->assertSet('abierto', false)->assertSet('form', []);
        self::assertDatabaseCount('controles_funcionales_iadl', 0);
    }

    public function test_ruta_autorizada_y_rectificacion_de_otro_residente_rechazada(): void
    {
        $this->actingAs($this->medico)->get(route('admin.funcionamiento-cotidiano', $this->residente))->assertOk()->assertSee('FUNCIONAMIENTO COTIDIANO');
        $r = $this->registrar();
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->actingAs($this->psicologo);
        Livewire::test(Panel::class, ['residente' => $otro])->call('rectificar', $r->getKey())->assertNotFound();
        self::assertDatabaseCount('controles_funcionales_iadl', 1);
    }

    public function test_instalador_acotado_es_idempotente_y_preserva_roles_ajenos(): void
    {
        $antes = Role::findByName('ENFERMEROS')->permissions()->pluck('name')->sort()->values()->all();
        $this->seed(PermisosCapturaFuncionamientoCotidianoSeeder::class);
        $this->seed(PermisosCapturaFuncionamientoCotidianoSeeder::class);
        self::assertSame($antes, Role::findByName('ENFERMEROS')->permissions()->pluck('name')->sort()->values()->all());
        self::assertFalse(Role::findByName('FISIOTERAPEUTA')->hasPermissionTo('controles_funcionales_iadl.crear'));
    }

    public function test_instalador_desde_permisos_ausentes_no_concede_escritura_a_superadmin(): void
    {
        \Spatie\Permission\Models\Permission::query()->whereIn('name', ['controles_funcionales_iadl.crear', 'controles_funcionales_iadl.rectificar'])->get()->each->delete();
        $this->seed(PermisosCapturaFuncionamientoCotidianoSeeder::class);
        foreach (['controles_funcionales_iadl.crear', 'controles_funcionales_iadl.rectificar'] as $nombre) {
            self::assertTrue(Role::findByName('MEDICO GENERAL/GERIATRA')->hasPermissionTo($nombre));
            self::assertTrue(Role::findByName('PSICOLOGO/A')->hasPermissionTo($nombre));
            self::assertFalse(Role::findByName('SUPERADMINISTRADOR')->hasPermissionTo($nombre));
        }
    }

    public function test_editor_de_roles_no_amplia_competencia_iadl_ni_la_concede_a_superadmin(): void
    {
        $actor = User::factory()->create(['estado' => 'ACTIVO']);
        $actor->assignRole('SUPERADMINISTRADOR');
        $servicio = app(\App\Modulos\Identidad\Aplicacion\Acciones\ActualizarPermisosRol::class);
        $rol = Role::findByName('ENFERMEROS');
        $antes = $rol->permissions()->pluck('name')->sort()->values()->all();
        try { $servicio->guardar($actor, $rol, [...$antes, 'controles_funcionales_iadl.crear']); self::fail('No concede captura por otro rol.'); }
        catch (HttpException $e) { self::assertSame(403, $e->getStatusCode()); }
        self::assertSame($antes, $rol->fresh()->permissions()->pluck('name')->sort()->values()->all());
        $super = Role::findByName('SUPERADMINISTRADOR');
        $servicio->guardar($actor, $super, []);
        self::assertFalse($super->fresh()->hasPermissionTo('controles_funcionales_iadl.crear'));
        self::assertFalse($super->fresh()->hasPermissionTo('controles_funcionales_iadl.rectificar'));
    }

    public function test_superadministracion_no_recibe_permisos_nuevos_de_captura(): void
    {
        self::assertFalse(Role::findByName('SUPERADMINISTRADOR')->hasPermissionTo('controles_funcionales_iadl.crear'));
        self::assertFalse(Role::findByName('SUPERADMINISTRADOR')->hasPermissionTo('controles_funcionales_iadl.rectificar'));
    }

    public function test_permiso_revocado_tras_revision_impide_guardar_y_mantiene_captura(): void
    {
        $this->actingAs($this->psicologo);
        $ui = Livewire::test(Panel::class, ['residente' => $this->residente])->call('nuevo')->set('form', $this->entrada())->call('revisar');
        Role::findByName('PSICOLOGO/A')->revokePermissionTo('controles_funcionales_iadl.crear');
        $ui->call('guardar')->assertForbidden();
        self::assertDatabaseCount('controles_funcionales_iadl', 0);
    }

    public function test_cambiar_datos_despues_de_revisar_requiere_nueva_confirmacion(): void
    {
        $this->actingAs($this->psicologo);
        Livewire::test(Panel::class, ['residente' => $this->residente])->call('nuevo')->set('form', $this->entrada())->call('revisar')
            ->set('form.observacion', 'Otro relato sintético.')->assertSet('revision', false)->call('guardar')->assertHasErrors('registro');
        self::assertDatabaseCount('controles_funcionales_iadl', 0);
    }

    public function test_etiquetas_cubren_exactamente_el_catalogo_existente(): void
    {
        foreach (['actividad' => Iadl::ACTIVIDADES, 'oportunidad' => Iadl::OPORTUNIDADES, 'desempeno' => Iadl::DESEMPENOS,
            'tipo_ayuda' => Iadl::AYUDAS, 'causa_ayuda' => Iadl::CAUSAS, 'nivel_previo' => Iadl::NIVELES_PREVIOS, 'fuente' => Iadl::FUENTES] as $campo => $valores) {
            self::assertSame($valores, array_keys(Opciones::OPCIONES[$campo]));
        }
    }
}
