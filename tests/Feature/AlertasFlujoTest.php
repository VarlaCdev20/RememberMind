<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\EventoAlerta;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Acciones\DeteccionAlertasService;
use App\Modulos\Alertas\Aplicacion\Acciones\GestionarAtencionAlerta;
use App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades\CampanaNotificaciones;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AlertasFlujoTest extends TestCase
{
    use RefreshDatabase;

    private function preparar(array $permisos): array
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create(['nombres' => 'Ana', 'ap_paterno' => 'Profesional']);
        $user->assignRole('ADMINISTRADOR');
        $user->roles->each(fn ($role) => $role->syncPermissions([]));
        foreach ($permisos as $p) {
            $user->givePermissionTo(Permission::findOrCreate($p, 'web'));
        }
        $this->actingAs($user);

        return [$user, Residente::factory()->create(['cod_est_adul' => 'EST_001'])];
    }

    public function test_referencias_internas_no_son_texto_visible_y_se_conservan_en_la_historia(): void
    {
        [$usuario, $residente] = $this->preparar(['alertas.ver', 'alertas.gestionar']);
        $descripcion = '[heridas:COD_HER_000004] Herida cortante en Pierna derecha [observación de enfermería]';
        $alerta = Alerta::create([
            'cod_residente' => $residente->cod_residente,
            'modulo' => 'HERIDAS',
            'tipo' => 'LESIÓN DETECTADA',
            'cod_registro' => 'COD_HER_000004',
            'descripcion' => $descripcion,
            'estado' => 'ABIERTA',
        ]);
        $evento = $alerta->eventos()->create([
            'cod_usuario' => $usuario->cod_usuario,
            'tipo_evento' => 'SEGUIMIENTO', 'estado_anterior' => 'ABIERTA', 'estado_nuevo' => 'ABIERTA',
            'descripcion' => 'Derivación creada (COD_DER_000001) hacia área receptora.',
            'fecha_hora' => now(),
        ]);

        $panel = Livewire::test(AlertasPanel::class)
            ->assertSeeText('Herida cortante en Pierna derecha [observación de enfermería]')
            ->assertDontSeeText('[heridas:')->assertDontSeeText('COD_HER_000004')
            ->call('verDetalle', $alerta->getKey())
            ->assertSeeText('Derivación creada hacia área receptora.')
            ->assertDontSeeText('COD_DER_000001')->assertDontSeeText('COD_HER_000004');
        $panel->call('atenderAlerta', $alerta->getKey())->assertDontSeeText('COD_HER_000004')
            ->call('cerrarAlerta', $alerta->getKey())->assertDontSeeText('COD_HER_000004');

        $this->assertSame($descripcion, $alerta->fresh()->descripcion);
        $this->assertSame('COD_HER_000004', $alerta->fresh()->cod_registro);
        $this->assertSame('Derivación creada (COD_DER_000001) hacia área receptora.', $evento->fresh()->descripcion);
        $this->assertSame('ABIERTA', $alerta->fresh()->estado);
    }

    public function test_registrar_asignar_atender_agregar_accion_cerrar_y_consultar_historial(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver', 'alertas.gestionar']);
        $this->get(route('admin.enfermeria.alertas'))->assertOk()->assertSee('Detectar pendientes');
        $panel = Livewire::test(AlertasPanel::class)->call('abrirCrear')
            ->set('codResidente', $adulto->cod_residente)->set('tipoAlerta', 'Revisión requerida')
            ->set('motivo', 'Se solicita seguimiento del residente.')->call('guardarAlerta')->assertHasNoErrors();
        $alerta = Alerta::sole();
        $this->get(route('admin.enfermeria.alertas', ['adulto' => $adulto->cod_residente, 'alerta' => $alerta->cod_alerta]))
            ->assertOk()->assertSee('Detalle de la alerta');
        $panel->call('verDetalle', $alerta->cod_alerta)->set('responsableId', $user->cod_usuario)
            ->call('asignarResponsable')->assertHasNoErrors()
            ->call('atenderAlerta', $alerta->cod_alerta)->set('accionTomada', 'Se inicia revisión presencial.')
            ->call('guardarAtencion')->assertHasNoErrors();
        $this->assertSame('EN_ATENCION', $alerta->fresh()->estado);
        $panel->call('verDetalle', $alerta->cod_alerta)->set('accion', 'Se informa al equipo responsable.')
            ->call('guardarAccion')->assertHasNoErrors()
            ->call('cerrarAlerta', $alerta->cod_alerta)->set('observacionCierre', 'Seguimiento concluido por el equipo.')
            ->call('confirmarCierre')->assertHasNoErrors()
            ->assertSet('modalResultadoCierre', true)->assertSee('Alerta cerrada')
            ->set('filtroEstado', 'CERRADA')
            ->call('verDetalle', $alerta->cod_alerta)->assertSee('Se informa al equipo responsable.');
        $this->assertSame('CERRADA', $alerta->fresh()->estado);
        $this->assertSame(5, $alerta->eventos()->count());
        $this->assertEqualsCanonicalizing(
            ['CREACION', 'ASIGNACION', 'INTERVENCION', 'SEGUIMIENTO', 'CIERRE'],
            $alerta->eventos()->pluck('tipo_evento')->all(),
        );
        $this->assertSame($user->cod_usuario, $alerta->eventos()->latest('fecha_hora')->value('cod_usuario'));
        $panel->set('accion', 'Intento de cambiar el historial.')->call('guardarAccion')->assertStatus(409);
        $this->assertSame(5, $alerta->eventos()->count());
    }

    public function test_lectura_no_permite_mutar_y_los_filtros_no_mezclan_estados(): void
    {
        [, $adulto] = $this->preparar(['alertas.ver']);
        Alerta::create(['cod_residente' => $adulto->cod_residente, 'modulo' => 'MANUAL', 'tipo' => 'CERRADA ESPECIAL', 'descripcion' => 'Seguimiento concluido', 'estado' => 'CERRADA']);
        Livewire::test(AlertasPanel::class)->set('search', $adulto->nombres)->assertDontSee('CERRADA ESPECIAL')
            ->call('abrirCrear')->assertForbidden();
    }

    public function test_primera_nota_abierta_registra_intervencion_y_el_cierre_exige_resultado(): void
    {
        [, $residente] = $this->preparar(['alertas.ver', 'alertas.seguimiento', 'alertas.cerrar']);
        $alerta = Alerta::create([
            'cod_residente' => $residente->cod_residente,
            'modulo' => 'SIGNOS',
            'tipo' => 'SIGNOS VITALES CRITICOS',
            'descripcion' => 'Lectura crítica registrada para seguimiento.',
            'estado' => 'ABIERTA',
        ]);

        $panel = Livewire::test(AlertasPanel::class)->call('verDetalle', $alerta->cod_alerta)
            ->assertSee('La alerta todavía no tiene una intervención registrada.')
            ->set('accion', 'Se realizó revisión presencial y nueva toma.')
            ->call('guardarAccion')->assertHasNoErrors();
        $this->assertSame('EN_ATENCION', $alerta->fresh()->estado);
        $this->assertDatabaseHas('eventos_alerta', [
            'cod_alerta' => $alerta->cod_alerta,
            'tipo_evento' => 'INTERVENCION',
        ]);

        $panel->call('cerrarAlerta', $alerta->cod_alerta)
            ->call('confirmarCierre')->assertHasErrors('observacionCierre');
        $this->assertSame('EN_ATENCION', $alerta->fresh()->estado);
    }

    public function test_no_se_puede_asignar_una_alerta_a_usuario_inactivo(): void
    {
        [, $residente] = $this->preparar(['alertas.ver', 'alertas.asignar']);
        $alerta = Alerta::create([
            'cod_residente' => $residente->cod_residente,
            'modulo' => 'MANUAL',
            'tipo' => 'SEGUIMIENTO',
            'descripcion' => 'Se requiere revisión asistencial.',
            'estado' => 'ABIERTA',
        ]);
        $inactivo = User::factory()->create(['estado' => 'INACTIVO']);

        Livewire::test(AlertasPanel::class)->call('verDetalle', $alerta->cod_alerta)
            ->set('responsableId', $inactivo->cod_usuario)
            ->call('asignarResponsable')->assertStatus(422);

        $this->assertNull($alerta->fresh()->cod_personal_responsable);
        $this->assertSame(0, $alerta->eventos()->count());
    }

    public function test_detector_general_no_reclasifica_signos_historicos_sin_contexto(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver', 'alertas.gestionar']);
        SignoVital::create([
            'cod_residente' => $adulto->cod_residente,
            'cod_personal' => $user->personal->cod_personal,
            'fecha_hora' => today()->setTime(10, 0),
            'saturacion_oxigeno' => 85,
            'estado' => 'VIGENTE',
        ]);
        $this->assertSame(0, app(DeteccionAlertasService::class)->detectar());
        $this->assertDatabaseMissing('alertas', ['cod_residente' => $adulto->cod_residente, 'modulo' => 'SIGNOS']);
        $this->assertSame(0, app(DeteccionAlertasService::class)->detectar());
        $this->assertSame(0, Alerta::count());
    }

    public function test_control_sin_mediciones_no_genera_alerta_clinica(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver', 'alertas.gestionar']);
        SignoVital::create([
            'cod_residente' => $adulto->cod_residente,
            'cod_personal' => $user->personal->cod_personal,
            'fecha_hora' => today()->setTime(10, 0),
            'estado' => 'VIGENTE',
        ]);

        $this->assertSame(0, app(DeteccionAlertasService::class)->detectar());
        $this->assertDatabaseMissing('alertas', ['cod_residente' => $adulto->cod_residente, 'modulo' => 'SIGNOS']);
    }

    public function test_la_alerta_preventiva_no_infiere_riesgo_por_saturacion_aislada(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver', 'alertas.gestionar']);
        SignoVital::create([
            'cod_residente' => $adulto->cod_residente,
            'cod_personal' => $user->personal->cod_personal,
            'fecha_hora' => today()->setTime(10, 0),
            'saturacion_oxigeno' => 91.5,
            'estado' => 'VIGENTE',
        ]);

        app(DeteccionAlertasService::class)->detectarPreventivas($adulto->cod_residente);

        $this->assertDatabaseMissing('alertas', [
            'cod_residente' => $adulto->cod_residente,
            'modulo' => 'SIGNOS',
        ]);
    }

    public function test_usuario_no_autorizado_no_puede_atender_ni_cerrar(): void
    {
        [, $adulto] = $this->preparar(['alertas.ver']);
        $alerta = Alerta::create([
            'cod_residente' => $adulto->cod_residente,
            'modulo' => 'MANUAL',
            'tipo' => 'REVISION',
            'descripcion' => 'Monitoreo de prueba',
            'estado' => 'ABIERTA',
        ]);

        Livewire::test(AlertasPanel::class)
            ->call('atenderAlerta', $alerta->cod_alerta)
            ->assertForbidden();

        Livewire::test(AlertasPanel::class)
            ->call('cerrarAlerta', $alerta->cod_alerta)
            ->assertForbidden();
    }

    public function test_cerrar_exige_observacion_valida_y_rechaza_textos_triviales(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver', 'alertas.gestionar']);
        $alerta = Alerta::create([
            'cod_residente' => $adulto->cod_residente,
            'modulo' => 'MANUAL',
            'tipo' => 'EVALUACION',
            'descripcion' => 'Monitoreo para cierre asistencial',
            'estado' => 'ABIERTA',
        ]);

        // 1. Rechaza vacio
        Livewire::test(AlertasPanel::class)
            ->call('cerrarAlerta', $alerta->cod_alerta)
            ->set('observacionCierre', '')
            ->call('confirmarCierre')
            ->assertHasErrors(['observacionCierre']);

        // 2. Rechaza texto trivial 'ok'
        Livewire::test(AlertasPanel::class)
            ->call('cerrarAlerta', $alerta->cod_alerta)
            ->set('observacionCierre', 'ok')
            ->call('confirmarCierre')
            ->assertHasErrors(['observacionCierre']);

        // 3. Rechaza texto trivial '-'
        Livewire::test(AlertasPanel::class)
            ->call('cerrarAlerta', $alerta->cod_alerta)
            ->set('observacionCierre', '-')
            ->call('confirmarCierre')
            ->assertHasErrors(['observacionCierre']);

        // 4. Acepta justificacion clinica valida
        Livewire::test(AlertasPanel::class)
            ->call('cerrarAlerta', $alerta->cod_alerta)
            ->set('observacionCierre', 'Condicion estabilizada tras control y reposo asistencial.')
            ->call('confirmarCierre')
            ->assertHasNoErrors();

        // 5. Verifica que la alerta se conserva intacta en BD como CERRADA (nunca borrada)
        $this->assertDatabaseHas('alertas', [
            'cod_alerta' => $alerta->cod_alerta,
            'estado' => 'CERRADA',
        ]);
        $this->assertSame($user->cod_usuario, $alerta->eventos()->latest('fecha_hora')->value('cod_usuario'));
        $this->assertSame('CERRADA', $alerta->fresh()->estado);
    }

    public function test_drawers_de_graficos_y_ubicacion_no_modifican_datos(): void
    {
        [, $adulto] = $this->preparar(['alertas.ver']);
        $nombreOriginal = $adulto->nombres;
        $estadoOriginal = $adulto->cod_est_adul;

        Livewire::test(AlertasPanel::class)
            ->call('verGraficos', $adulto->cod_residente)
            ->assertSet('drawerGrafico', true)
            ->assertSet('adultoDrawerId', $adulto->cod_residente)
            ->call('verUbicacion', $adulto->cod_residente)
            ->assertSet('drawerUbicacion', true)
            ->assertSet('drawerGrafico', false)
            ->call('cerrarDrawer')
            ->assertSet('drawerGrafico', false)
            ->assertSet('drawerUbicacion', false);

        $this->assertSame($nombreOriginal, $adulto->fresh()->nombres);
        $this->assertSame($estadoOriginal, $adulto->fresh()->cod_est_adul);
    }

    public function test_campana_conserva_atencion_cierre_e_historial(): void
    {
        [$usuario, $residente] = $this->preparar(['alertas.ver', 'alertas.seguimiento', 'alertas.cerrar']);
        $alerta = Alerta::create(['cod_residente' => $residente->cod_residente, 'modulo' => 'MANUAL', 'tipo' => 'Seguimiento sintético', 'descripcion' => 'Revisión sintética', 'estado' => 'ABIERTA']);
        Livewire::test(CampanaNotificaciones::class)
            ->call('atenderAlerta', $alerta->cod_alerta, 'Atención sintética')
            ->assertDispatched('alerta-atendida')
            ->call('cerrarAlerta', $alerta->cod_alerta, 'Cierre sintético')
            ->assertDispatched('alerta-cerrada');
        $this->assertSame('CERRADA', $alerta->fresh()->estado);
        $this->assertSame(['INTERVENCION', 'CIERRE'], $alerta->eventos()->orderBy('created_at')->pluck('tipo_evento')->all());
        $this->assertSame([$usuario->cod_usuario], $alerta->eventos()->pluck('cod_usuario')->unique()->values()->all());
        try {
            app(GestionarAtencionAlerta::class)->cerrarAlerta($usuario, $alerta->cod_alerta, 'Reintento');
            $this->fail('El cierre repetido debe rechazarse.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
        $this->assertSame(2, $alerta->eventos()->count());
    }

    public function test_campana_sin_permiso_no_muta_al_invocar_el_caso_directamente(): void
    {
        [$usuario, $residente] = $this->preparar(['alertas.ver']);
        $alerta = Alerta::create(['cod_residente' => $residente->cod_residente, 'modulo' => 'MANUAL', 'tipo' => 'Seguimiento sintético', 'descripcion' => 'Revisión sintética', 'estado' => 'ABIERTA']);
        try {
            app(GestionarAtencionAlerta::class)->atenderAlerta($usuario, $alerta->cod_alerta, 'Intento no autorizado');
            $this->fail('El caso exige el permiso original de atención.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertSame('ABIERTA', $alerta->fresh()->estado);
        $this->assertSame(0, $alerta->eventos()->count());
    }

    public function test_campana_revierte_estado_si_falla_el_evento(): void
    {
        [$usuario, $residente] = $this->preparar(['alertas.seguimiento']);
        $alerta = Alerta::create(['cod_residente' => $residente->cod_residente, 'modulo' => 'MANUAL', 'tipo' => 'Seguimiento sintético', 'descripcion' => 'Revisión sintética', 'estado' => 'ABIERTA']);
        EventoAlerta::creating(fn () => throw new \RuntimeException('Fallo sintético de historial'));
        try {
            app(GestionarAtencionAlerta::class)->atenderAlerta($usuario, $alerta->cod_alerta, 'Atención sintética');
            $this->fail('El fallo del historial debe propagarse.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Fallo sintético de historial', $exception->getMessage());
        }
        $this->assertSame('ABIERTA', $alerta->fresh()->estado);
        $this->assertSame(0, $alerta->eventos()->count());
    }

    public function test_critica_antigua_aparece_antes_de_bajas_nuevas_y_fuera_del_filtro(): void
    {
        [, $residente] = $this->preparar(['alertas.ver']);
        $critica = Alerta::create(['cod_residente'=>$residente->cod_residente,'modulo'=>'MANUAL','tipo'=>'Crítica sintética','descripcion'=>'Revisión prioritaria de prueba','estado'=>'EN_ATENCION','prioridad'=>'CRITICA','fecha_hora'=>now()->subDays(2)]);
        for ($i=0;$i<18;$i++) { Alerta::create(['cod_residente'=>$residente->cod_residente,'modulo'=>'MANUAL','tipo'=>'Baja sintética '.$i,'descripcion'=>'Aviso de prueba','estado'=>'ABIERTA','prioridad'=>'BAJO','fecha_hora'=>now()]); }
        Livewire::test(AlertasPanel::class)->set('filtroEstado','')->assertViewHas('alertas',fn($items)=>$items->first()->getKey()===$critica->getKey())
            ->set('filtroEstado','CERRADA')->assertViewHas('prioritarias',fn($items)=>$items->contains('cod_alerta',$critica->cod_alerta));
    }

    public function test_primera_novedad_desde_lista_vacia_y_varias_alertas_se_anuncian_agrupadas(): void
    {
        [, $residente] = $this->preparar(['alertas.ver']);
        $campana = Livewire::test(CampanaNotificaciones::class)->assertNotDispatched('alerta-nueva');
        foreach (['BAJO','CRITICO'] as $nivel) { Alerta::create(['cod_residente'=>$residente->cod_residente,'modulo'=>'MANUAL','tipo'=>'Revisión sintética','descripcion'=>'Aviso de prueba','estado'=>'ABIERTA','prioridad'=>$nivel,'fecha_hora'=>now()]); }
        $campana->call('verificarAlertas')->assertSet('conteoAbiertas',2)->assertDispatched('alerta-nueva',fn($name,$params)=>$params[0]['nivel']==='CRITICO' && str_contains($params[0]['titulo'],'2 aviso'));
        $campana->call('verificarAlertas')->assertCount('alertaIdsConocidos',2)->assertNotDispatched('alerta-nueva');
    }

    public function test_campana_no_expone_residentes_a_familiar_con_permiso_generico(): void
    {
        [$usuario,$residente] = $this->preparar(['alertas.ver','administraciones_medicacion.ver']);
        $usuario->syncRoles(['FAMILIAR']);
        Alerta::create(['cod_residente'=>$residente->cod_residente,'modulo'=>'MANUAL','tipo'=>'Aviso reservado de prueba','descripcion'=>'No debe verse','estado'=>'ABIERTA','prioridad'=>'CRITICO']);
        Livewire::test(CampanaNotificaciones::class)->assertSet('conteoAbiertas',0)->assertDontSee('Aviso reservado de prueba');
    }

    public function test_accion_de_campana_rechaza_cuenta_inactiva_sin_eventos(): void
    {
        [$usuario,$residente] = $this->preparar(['alertas.gestionar']);
        $alerta=Alerta::create(['cod_residente'=>$residente->cod_residente,'modulo'=>'MANUAL','tipo'=>'Revisión','descripcion'=>'Prueba','estado'=>'ABIERTA']);
        User::whereKey($usuario->getKey())->update(['estado'=>'INACTIVO']);
        try { app(GestionarAtencionAlerta::class)->atenderAlerta($usuario,$alerta->cod_alerta,'Revisión documentada'); $this->fail('Debe rechazar cuenta revocada'); }
        catch (HttpException $e) { $this->assertSame(403,$e->getStatusCode()); }
        $this->assertSame('ABIERTA',$alerta->fresh()->estado); $this->assertSame(0,$alerta->eventos()->count());
    }
}
