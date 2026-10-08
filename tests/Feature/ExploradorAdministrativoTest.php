<?php

namespace Tests\Feature;

use App\Backend\Modulos\Administracion\Servicios\ExploradorAdministrativoService;
use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Models\Actividad;
use App\Models\Alerta;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Cama;
use App\Models\Contacto;
use App\Models\Documento;
use App\Models\Habitacion;
use App\Models\Incidente;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Preadmision;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\SeguroResidente;
use App\Models\Turno;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExploradorAdministrativoTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 6)->setTime(12, 0));
        $permisos = ['admisiones.ver_dashboard', 'admisiones.formalizar', 'residentes.ver', 'jornadas.ver', 'asignaciones_personal.ver', 'contactos.ver', 'documentos.ver', 'consentimientos.ver', 'seguros_residente.ver', 'actividades.ver', 'visitas.ver', 'alertas.ver', 'incidentes.ver', 'reportes.ver'];
        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }
        Role::findOrCreate('ADMINISTRADOR', 'web');
        Role::findOrCreate('FAMILIAR', 'web');
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario->assignRole('ADMINISTRADOR');
        $this->usuario->givePermissionTo($permisos);
        $this->actingAs($this->usuario);
        Storage::fake('local');
        $this->crearDatos();
    }

    public function test_cada_modulo_renderiza_sus_vistas_y_detalles_con_conteos_reales(): void
    {
        foreach (ExploradorAdministrativoService::MODULOS as $modulo) {
            $datos = $this->datos($modulo);
            $this->assertSame(1, $datos['registros']->total(), $modulo);
            $this->assertSame(1, $datos['totalContexto'], $modulo);
            $this->assertSame(1, (int) $datos['categorias']->sum('cantidad'));
            foreach ($datos['presentacion']['vistas'] as $vista) {
                $this->get(route('admin.administracion.'.$modulo, ['vista' => $vista]))->assertOk()->assertSee('operacion-graficos')->assertViewHas('vista', $vista);
            }
            $this->get(route('admin.administracion.'.$modulo, ['detalle' => $datos['registros']->first()->codigo]))->assertOk()->assertSee('role="dialog"', false)->assertSee('Volver a la bandeja');
        }
        $this->assertDatabaseCount('residentes', 1);
        $this->assertDatabaseCount('ocupaciones_cama', 1);
    }

    public function test_busqueda_filtros_paginacion_y_resumen_conservan_contexto(): void
    {
        for ($i = 0; $i < 15; $i++) {
            Contacto::create(['cod_contacto' => 'C_EXTRA_'.$i, 'nombres' => 'Contacto sintético '.$i, 'apellido_paterno' => 'Prueba', 'estado' => $i % 2 ? 'INACTIVO' : 'ACTIVO']);
        }
        $datos = $this->datos('contactos', ['estado' => 'INACTIVO', 'por_pagina' => 10]);
        $this->assertSame(7, $datos['registros']->total());
        $this->assertSame(16, $datos['totalContexto']);
        $this->assertSame(16, (int) $datos['distribucion']->sum('cantidad'));
        $this->assertSame(1, $this->datos('contactos', ['search' => 'FAMILIA SINTETICA'])['registros']->total());
        $this->assertSame(0, $this->datos('contactos', ['search' => 'inexistente'])['registros']->total());
        $this->assertSame(10, $this->datos('contactos', ['por_pagina' => 10])['registros']->count());
        $this->get(route('admin.administracion.contactos', ['por_pagina' => 100, 'estado' => 'INVENTADO']))->assertSessionHasErrors('por_pagina');
        $this->get(route('admin.administracion.contactos', ['estado' => 'INVENTADO']))->assertSessionHasErrors('estado');
        $this->get(route('admin.administracion.contactos', ['vista' => 'agenda']))->assertSessionHasErrors('vista');
        $this->get(route('admin.administracion.visitas', ['tab' => 'inventada']))->assertNotFound();
    }

    public function test_contacto_no_se_duplica_por_varios_vinculos_y_detalle_reautoriza(): void
    {
        $otro = $this->crearResidente('OTRO');
        ResidenteContacto::where('cod_residente', $otro->getKey())->where('cod_contacto', 'CTO_OP')->update(['contacto_emergencia' => true]);
        $datos = $this->datos('contactos', ['detalle' => 'CTO_OP']);
        $this->assertSame(1, $datos['registros']->total());
        $this->assertSame(2, (int) $datos['detalle']->vinculos);
        $this->assertCount(2, $datos['vinculos']);
        Gate::policy(Residente::class, ExploradorResidenteDenegadoPolicy::class);
        $this->assertEmpty($this->datos('contactos', ['detalle' => 'CTO_OP'])['vinculos']);
        $this->get(route('admin.administracion.seguros', ['detalle' => 'SEG_OP']))->assertForbidden();
    }

    public function test_cuenta_familiar_y_permisos_no_exponen_bandejas_o_identidades(): void
    {
        $this->usuario->revokePermissionTo('residentes.ver');
        foreach (['contactos', 'documentacion', 'consentimientos', 'seguros', 'visitas', 'alertas', 'incidentes'] as $modulo) {
            $this->get(route('admin.administracion.'.$modulo))->assertForbidden();
        }
        $this->get(route('admin.administracion.jornadas'))->assertOk();
        $this->usuario->givePermissionTo('residentes.ver');
        $this->usuario->revokePermissionTo('visitas.ver');
        $this->get(route('admin.administracion.visitas'))->assertForbidden();
        $this->usuario->givePermissionTo('visitas.ver');
        $this->usuario->syncRoles(['FAMILIAR']);
        $this->get(route('admin.administracion.visitas'))->assertForbidden();
        $this->usuario->syncRoles(['ADMINISTRADOR']);
        $this->usuario->update(['estado' => 'INACTIVO']);
        $this->get(route('admin.administracion.reportes'))->assertForbidden();
        $this->assertDatabaseCount('visitas', 1);
    }

    public function test_documento_usa_descarga_privada_y_no_envia_ruta_o_hash(): void
    {
        $url = route('admin.administracion.documentacion', ['detalle' => 'DOC_OP']);
        $this->get($url)->assertOk()->assertSee(route('admin.documentos.descargar', ['documento' => 'DOC_OP']))->assertDontSee('documentos/sintetico.pdf')->assertDontSee(str_repeat('a', 64));
        Storage::disk('local')->delete('documentos/sintetico.pdf');
        $this->get($url)->assertOk()->assertSee('El archivo no está disponible')->assertDontSee('Descargar archivo');
        $this->get(route('admin.administracion.documentacion', ['detalle' => 'DOC_NO_EXISTE']))->assertNotFound();
    }

    public function test_fechas_y_bandejas_distinguen_programacion_historia_y_vencimiento(): void
    {
        $this->assertSame(0, $this->datos('visitas', ['tab' => 'dentro'])['registros']->total());
        $this->assertSame(1, $this->datos('visitas', ['tab' => 'programadas'])['registros']->total());
        $this->assertSame(1, $this->datos('documentacion', ['tab' => 'por_vencer'])['registros']->total());
        $this->assertSame(0, $this->datos('documentacion', ['tab' => 'vencidos'])['registros']->total());
        $this->assertSame(0, $this->datos('actividades', ['desde' => '2026-11-01'])['totalContexto']);
        $this->get(route('admin.administracion.actividades', ['hasta' => '2026-10-07']))->assertOk();
        $this->get(route('admin.administracion.actividades', ['desde' => '2026-10-09', 'hasta' => '2026-10-01']))->assertSessionHasErrors('hasta');
        $this->assertSame('2026-10', $this->datos('actividades')['historia']->first()->mes);
    }

    public function test_consultas_de_bandeja_no_crecen_con_las_filas(): void
    {
        $this->datos('contactos');
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->datos('contactos');
        $cantidad = count(DB::getQueryLog());
        for ($i = 0; $i < 15; $i++) {
            Contacto::create(['cod_contacto' => 'CTO_MAS_'.$i, 'nombres' => 'Persona sintética '.$i, 'apellido_paterno' => 'Prueba', 'estado' => 'ACTIVO']);
        }
        DB::flushQueryLog();
        $this->datos('contactos');
        $this->assertSame($cantidad, count(DB::getQueryLog()));
        $this->assertLessThanOrEqual(7, $cantidad);
        DB::disableQueryLog();
    }

    public function test_detalle_de_alerta_conserva_eventos_y_campos_sin_inventar_intervenciones(): void
    {
        for ($i = 1; $i <= 14; $i++) {
            DB::table('eventos_alerta')->insert([
                'cod_evento_alerta' => 'EVT_OP_'.$i, 'cod_alerta' => 'ALE_OP', 'cod_usuario' => $this->usuario->getKey(),
                'tipo_evento' => 'COMENTARIO', 'estado_anterior' => 'ABIERTA', 'estado_nuevo' => 'ABIERTA',
                'fecha_hora' => now()->addSeconds($i), 'descripcion' => 'Evento sintético '.$i,
            ]);
        }
        $datos = $this->datos('alertas', ['detalle' => 'ALE_OP']);
        $this->assertCount(12, $datos['historialAlerta']);
        $this->assertSame('Evento sintético 14', $datos['historialAlerta']->first()->descripcion);
        $this->assertSame('Prueba', $datos['camposDetalle']['Descripción registrada']);
        $this->get(route('admin.administracion.alertas', ['detalle' => 'ALE_OP']))->assertOk()->assertSee('Trayectoria registrada')->assertSee('Evento sintético 14')->assertDontSee('Evento sintético 1<');
        $this->assertDatabaseCount('eventos_alerta', 14);
        $this->assertDatabaseHas('alertas', ['cod_alerta' => 'ALE_OP', 'estado' => 'ABIERTA']);
    }

    public function test_calendario_get_no_depende_de_livewire_y_conserva_fecha_inicial(): void
    {
        $html = Blade::render('<x-ui.calendario id="fecha-prueba" name="desde" label="Fecha desde" value="2026-10-06" />');
        $this->assertStringContainsString('2026-10-06', $html);
        $this->assertStringContainsString('name="desde"', $html);
        $this->assertStringNotContainsString('entangle', $html);
        $this->assertStringNotContainsString('wire:key=', $html);
    }

    public function test_graficos_filtran_categorias_reales_sin_perder_el_contexto(): void
    {
        Contacto::create(['cod_contacto' => 'CTO_SIN_VINCULO', 'nombres' => 'Contacto', 'apellido_paterno' => 'Sintetico', 'estado' => 'ACTIVO']);
        $datos = $this->datos('contactos', ['categoria' => '0']);
        $this->assertSame(1, $datos['registros']->total());
        $this->assertSame('CTO_SIN_VINCULO', $datos['registros']->first()->codigo);
        $this->assertSame(2, $datos['totalContexto']);
        $this->get(route('admin.administracion.contactos', ['categoria' => '1']))->assertOk()->assertSee('Área o grupo: 1');
        $this->get(route('admin.administracion.contactos', ['categoria' => 'INVENTADA']))->assertSessionHasErrors('categoria');
        $this->get(route('admin.administracion.jornadas', ['search' => 'inexistente']))->assertOk()->assertSee('Ver todos los registros');
    }

    public function test_filtro_invalido_permite_corregir_fechas_sin_cambiar_resultados(): void
    {
        foreach (['jornadas', 'reportes'] as $modulo) {
            $url = route('admin.administracion.'.$modulo);
            $this->from($url)->get($url.'?desde=2026-10-09&hasta=2026-10-01')->assertRedirect($url)->assertSessionHasErrors('hasta');
            $this->withCookie(config('session.cookie'), session()->getId())
                ->get($url)->assertOk()->assertSee('no se aplicó')->assertSee('2026-10-09')->assertSee('2026-10-01')->assertSee('aria-invalid="true"', false);
        }
        $html = Blade::render('<x-ui.calendario id="fecha-invalida" name="desde" label="Fecha desde" value="2026-02-31" />');
        $this->assertStringNotContainsString('value: "2026-02-31"', $html);
    }

    public function test_graficos_y_vistas_corresponden_al_proceso_y_conservan_cantidades(): void
    {
        $tipos = ['jornadas' => 'horarios', 'asignaciones' => 'mosaico', 'contactos' => 'anillo', 'documentacion' => 'barras', 'consentimientos' => 'anillo', 'seguros' => 'anillo', 'actividades' => 'comparacion', 'visitas' => 'estaciones', 'alertas' => 'estaciones', 'incidentes' => 'columnas'];
        foreach ($tipos as $modulo => $tipo) {
            $this->get(route('admin.administracion.'.$modulo))->assertOk()->assertSee('data-grafico="'.$tipo.'"', false)->assertDontSee('NaN')->assertDontSee('Infinity');
        }
        $this->get(route('admin.administracion.alertas', ['vista' => 'tablero']))->assertOk()->assertSee('Seguimiento por estado')->assertSee('Alerta sintética');
        $this->get(route('admin.administracion.asignaciones', ['vista' => 'cobertura']))->assertOk()->assertSee('Cobertura por área')->assertSee('Área de prueba');
        $this->get(route('admin.administracion.incidentes', ['vista' => 'tablero']))->assertSessionHasErrors('vista');
        $this->assertDatabaseCount('alertas', 1);
        $this->assertDatabaseCount('asignaciones_personal', 1);
        $series = [['etiqueta' => 'Grupo A', 'cantidad' => 3, 'url' => '/consulta?a=1'], ['etiqueta' => 'Grupo B', 'cantidad' => 1, 'url' => '/consulta?a=2']];
        $html = Blade::render('<x-ui.grafico-operativo tipo="anillo" :datos="$series" etiqueta="Distribución sintética" />', compact('series'));
        $this->assertStringContainsString('75%', $html);
        $this->assertStringContainsString('25%', $html);
        $this->assertStringContainsString('/consulta?a=1', $html);
        $this->assertStringContainsString('>4</strong>', $html);
        $vacio = Blade::render('<x-ui.grafico-operativo tipo="anillo" :datos="[]" />');
        $this->assertStringContainsString('Sin datos en este contexto', $vacio);
        $this->assertStringNotContainsString('stroke-dasharray', $vacio);
    }

    public function test_tarjetas_muestran_campos_utiles_y_nombres_inequivocos(): void
    {
        $this->get(route('admin.administracion.actividades'))->assertOk()->assertSee('Cupo registrado')->assertSee('Participantes registrados')->assertSee('Profesional Sintético')->assertSee('Ayuda de actividades');
        $this->get(route('admin.administracion.alertas'))->assertOk()->assertSee('Responsable registrado')->assertSee($this->residente->nombres.' '.$this->residente->apellido_paterno);
        $this->get(route('admin.administracion.incidentes'))->assertOk()->assertSee('Gravedad registrada')->assertSee('Requiere médico según registro')->assertSee('Requiere derivación según registro');
        $this->get(route('admin.administracion.asignaciones'))->assertOk()->assertSee('Día de la jornada')->assertSee('Fecha de asignación');
        $this->get(route('admin.administracion.contactos', ['categoria' => '1']))->assertOk()->assertSee('aria-current="true"', false);
        $this->assertSame(1, $this->datos('alertas', ['search' => $this->residente->apellido_paterno])['registros']->total());
        $this->assertSame(1, $this->datos('actividades', ['search' => 'Profesional Sintético'])['registros']->total());
    }

    public function test_reportes_conservan_periodo_y_visitas_filtran_la_entrada_real(): void
    {
        Visita::whereKey('VIS_OP')->update(['fecha_hora_programada' => '2026-09-20 10:00:00', 'fecha_hora_ingreso' => '2026-10-06 10:00:00', 'fecha_hora_salida' => '2026-10-06 11:00:00', 'estado' => 'FINALIZADA']);
        $periodo = ['desde' => '2026-10-01', 'hasta' => '2026-10-31'];
        $this->assertSame(0, $this->datos('visitas', $periodo + ['tab' => 'todas'])['registros']->total());
        $entradas = $this->datos('visitas', $periodo + ['tab' => 'todas', 'fecha_visita' => 'ingreso']);
        $this->assertSame(1, $entradas['registros']->total());
        $this->assertSame('ingreso', $entradas['presentacion']['campoFecha']);
        $this->assertSame('2026-10', $entradas['historia']->first()->mes);
        $this->get(route('admin.administracion.reportes', $periodo))->assertOk()
            ->assertSee('abrirIndicador')->assertSee('Periodo:')->assertSee('Entrada registrada')
            ->assertSee('<button type="button" class="rm-operation-report"', false)
            ->assertDontSee('wire:navigate class="rm-operation-report"', false)
            ->assertSee('no depende del periodo elegido');
        $this->get(route('admin.administracion.visitas', $periodo + ['tab' => 'todas', 'fecha_visita' => 'ingreso']))->assertOk()->assertSee('Entrada registrada')->assertSee('06 de octubre');
        $this->get(route('admin.administracion.visitas', ['fecha_visita' => 'inventada']))->assertSessionHasErrors('fecha_visita');
        $this->get(route('admin.administracion.alertas', ['fecha_visita' => 'ingreso']))->assertSessionHasErrors('fecha_visita');
        $this->assertDatabaseCount('visitas', 1);
    }

    public function test_calendario_completo_no_se_trunca_por_paginacion_y_respeta_el_mes_y_dia(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            Actividad::create(['cod_actividad' => 'ACT_CAL_'.$i, 'cod_area' => 'ARE_OP', 'cod_personal' => 'PER_OP', 'tipo' => 'INSTITUCIONAL', 'nombre' => 'Actividad calendario '.$i, 'fecha_hora' => '2026-10-20 10:00:00', 'estado' => 'PROGRAMADA']);
        }
        Actividad::create(['cod_actividad' => 'ACT_OTRO_MES', 'cod_area' => 'ARE_OP', 'cod_personal' => 'PER_OP', 'tipo' => 'INSTITUCIONAL', 'nombre' => 'Otro mes', 'fecha_hora' => '2026-11-01 10:00:00', 'estado' => 'PROGRAMADA']);
        $datos = $this->datos('actividades', ['vista' => 'calendario', 'mes' => '2026-10', 'por_pagina' => 10]);
        $this->assertSame(26, $datos['totalCalendario']);
        $this->assertSame(25, (int) $datos['diasCalendario']->firstWhere('dia', '2026-10-20')->cantidad);
        $this->assertCount(4, $datos['eventosCalendario']);
        $this->assertCount(10, $datos['registros']);
        $this->get(route('admin.administracion.actividades', ['vista' => 'calendario', 'mes' => '2026-10', 'page' => 99]))->assertOk()->assertSee('26 actividades con fecha en este mes')->assertSee('25 registros')->assertDontSee('Otro mes');
        $this->get(route('admin.administracion.actividades', ['vista' => 'calendario', 'mes' => '2026-10', 'dia' => '2026-10-20', 'page' => 2]))->assertOk()->assertViewHas('registros', fn ($filas) => $filas->total() === 25 && $filas->count() === 10);
        $this->get(route('admin.administracion.actividades', ['vista' => 'calendario', 'mes' => '2026-10', 'dia' => '2026-11-01']))->assertSessionHasErrors('dia');
        $this->get(route('admin.administracion.actividades', ['mes' => '2026-13']))->assertSessionHasErrors('mes');
        $this->get(route('admin.administracion.actividades', ['vista' => 'calendario', 'mes' => '2024-02']))->assertOk()->assertSee('datetime="2024-02-29"', false);
    }

    public function test_calendario_y_grafico_de_funciones_respetan_filtros_validos(): void
    {
        $datos = $this->datos('actividades', ['vista' => 'calendario', 'mes' => '2026-10', 'estado' => 'PROGRAMADA', 'categoria' => 'Área de prueba', 'search' => 'sintética']);
        $this->assertSame(1, $datos['totalCalendario']);
        $this->assertSame(0, $this->datos('actividades', ['vista' => 'calendario', 'mes' => '2026-10', 'search' => 'inexistente'])['totalCalendario']);
        $this->assertSame(1, $this->datos('asignaciones', ['funcion' => 'Coordinación'])['registros']->total());
        $this->get(route('admin.administracion.asignaciones', ['funcion' => 'Inventada']))->assertSessionHasErrors('funcion');
        $this->get(route('admin.administracion.contactos', ['vista' => 'calendario']))->assertSessionHasErrors('vista');
    }

    public function test_fragmento_de_ficha_es_privado_reautoriza_y_no_incluye_otra_ventana(): void
    {
        $url = route('admin.administracion.seguros', ['detalle' => 'SEG_OP']);
        $this->get($url, ['X-RM-Ficha' => '1'])->assertOk()->assertHeader('X-RM-Ficha', '1')->assertSee('role="dialog"', false)->assertDontSee('<html', false)->assertDontSee('id="sidebar"', false)->assertDontSee('Ver residente');
        $this->get(route('admin.administracion.seguros', ['detalle' => 'SEG_NO_EXISTE']), ['X-RM-Ficha' => '1'])->assertNotFound();
        Gate::policy(Residente::class, ExploradorResidenteDenegadoPolicy::class);
        $this->get($url, ['X-RM-Ficha' => '1'])->assertForbidden();
        $this->assertDatabaseCount('seguros_residente', 1);
        $this->usuario->update(['estado' => 'INACTIVO']);
        $this->get($url, ['X-RM-Ficha' => '1'])->assertForbidden();
    }

    public function test_graficos_de_actividad_reflejan_area_estado_y_dia_en_todas_las_vistas(): void
    {
        Area::create(['cod_area' => 'ARE_OTRA', 'nombre' => 'Otra área sintética', 'estado' => 'ACTIVA']);
        foreach ([['ACT_AREA', 'ARE_OTRA', '2026-10-06 14:00:00', 'PROGRAMADA'], ['ACT_ESTADO', 'ARE_OP', '2026-10-06 15:00:00', 'FINALIZADA'], ['ACT_DIA', 'ARE_OP', '2026-10-07 12:00:00', 'PROGRAMADA']] as [$codigo, $area, $fecha, $estado]) {
            Actividad::create(['cod_actividad' => $codigo, 'cod_area' => $area, 'cod_personal' => 'PER_OP', 'tipo' => 'INSTITUCIONAL', 'nombre' => 'Actividad fuera de selección '.$codigo, 'fecha_hora' => $fecha, 'estado' => $estado]);
        }
        foreach (['calendario', 'agenda', 'tarjetas', 'tabla'] as $vista) {
            $datos = $this->datos('actividades', ['vista' => $vista, 'mes' => '2026-10', 'dia' => '2026-10-06', 'categoria' => 'Área de prueba', 'estado' => 'PROGRAMADA']);
            $this->assertSame(1, $datos['registros']->total(), $vista);
            $this->assertSame(1, $datos['totalAnalisis']);
            $this->assertSame(1, (int) $datos['categorias']->sum('cantidad'));
            $this->assertSame(['ACT_OP'], $datos['comparacionOperativa']->pluck('codigo')->all());
            $this->assertSame(1, (int) $datos['historia']->sum('cantidad'));
            $this->assertSame(['PROGRAMADA'], $datos['estadosAnalisis']->pluck('estado')->all());
        }
        $this->get(route('admin.administracion.actividades', ['vista' => 'tabla', 'mes' => '2026-10', 'dia' => '2026-11-01']))->assertSessionHasErrors('dia');
    }

    public function test_cambio_de_vista_conserva_bandeja_y_ficha_no_contamina_paginacion(): void
    {
        $response = $this->get(route('admin.administracion.jornadas', ['vista' => 'calendario', 'dia' => '2026-10-06']));
        $response->assertOk()->assertSee('vista=tabla&amp;tab=todas', false)->assertSee('dia=2026-10-06', false);
        $response = $this->get(route('admin.administracion.actividades', ['detalle' => 'ACT_OP', 'mes' => '2026-10', 'dia' => '2026-10-06']));
        $response->assertOk();
        $this->assertStringNotContainsString('detalle=', $response->viewData('registros')->url(2));
        $this->assertStringContainsString('dia=2026-10-06', $response->viewData('registros')->url(2));
    }

    public function test_formulario_recupera_area_y_fecha_de_visitas_tras_periodo_invalido(): void
    {
        $url = route('admin.administracion.actividades');
        $this->from($url)->get($url.'?categoria='.urlencode('Área de prueba').'&desde=2026-10-09&hasta=2026-10-01')->assertRedirect($url)->assertSessionHasErrors('hasta');
        $html = $this->withCookie(config('session.cookie'), session()->getId())->get($url)->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<option\s+value="Área de prueba"\s+selected(?:="selected")?[^>]*>/u', $html);
        $this->get(route('admin.administracion.visitas', ['fecha_visita' => 'programacion']))->assertOk()->assertSee('Periodo de visitas: Programación');
    }

    public function test_comparacion_de_participantes_usa_escala_comun(): void
    {
        $series = [['etiqueta' => 'Actividad A', 'cantidad' => 5, 'cupo' => 10, 'url' => '/actividad?detalle=A', 'ficha' => 'A'], ['etiqueta' => 'Actividad B', 'cantidad' => 10, 'cupo' => 20, 'url' => '/actividad?detalle=B', 'ficha' => 'B']];
        $html = Blade::render('<x-ui.grafico-operativo tipo="comparacion" :datos="$series" />', compact('series'));
        $this->assertStringContainsString('--rm-data-size:25%', $html);
        $this->assertStringContainsString('--rm-data-size:50%', $html);
        $this->assertStringContainsString('--rm-data-size:100%', $html);
    }

    private function datos(string $modulo, array $filtros = []): array
    {
        return app(ExploradorAdministrativoService::class)->datos($modulo, $filtros, $this->usuario);
    }

    private function crearResidente(string $sufijo): Residente
    {
        Habitacion::create(['cod_habitacion' => 'H_'.$sufijo, 'codigo' => 'H-'.$sufijo, 'estado' => 'ACTIVA']);
        Cama::create(['cod_cama' => 'C_'.$sufijo, 'codigo' => 'C-'.$sufijo, 'cod_habitacion' => 'H_'.$sufijo, 'estado' => 'ACTIVA']);
        $pre = Preadmision::create(['cod_preadmision' => 'PRE_'.$sufijo, 'cod_contacto' => 'CTO_OP', 'cod_usuario_registro' => $this->usuario->getKey(), 'nombres' => 'Persona sintética', 'apellido_paterno' => $sufijo, 'fecha_nacimiento' => '1945-05-05', 'fecha_solicitud' => now()->subDay(), 'motivo_ingreso' => 'Acompañamiento', 'estado' => 'APROBADA']);

        return app(FormalizarAdmision::class)->ejecutar($pre, ['cod_cama' => 'C_'.$sufijo, 'fecha_hora_admision' => now()->subDay()], $this->usuario);
    }

    private function crearDatos(): void
    {
        Contacto::create(['cod_contacto' => 'CTO_OP', 'nombres' => 'Familia', 'apellido_paterno' => 'Sintetica', 'celular' => '70000000', 'estado' => 'ACTIVO']);
        $this->residente = $this->crearResidente('OP');
        Area::create(['cod_area' => 'ARE_OP', 'nombre' => 'Área de prueba', 'estado' => 'ACTIVA']);
        Personal::create(['cod_personal' => 'PER_OP', 'cod_usuario' => $this->usuario->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético', 'numero_documento' => 'SINTETICO-1', 'profesion' => 'ENFERMERÍA', 'estado' => 'ACTIVO']);
        Turno::create(['cod_turno' => 'TUR_OP', 'nombre' => 'Mañana', 'hora_inicio' => '08:00', 'hora_cierre' => '16:00', 'orden' => 1, 'estado' => 'ACTIVO']);
        Jornada::create(['cod_jornada' => 'JOR_OP', 'cod_turno' => 'TUR_OP', 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_asignacion_personal' => 'ASP_OP', 'cod_jornada' => 'JOR_OP', 'cod_personal' => 'PER_OP', 'cod_area' => 'ARE_OP', 'funcion' => 'Coordinación', 'tipo_asignacion' => 'TURNO', 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        Storage::disk('local')->put('documentos/sintetico.pdf', 'Contenido sintético');
        Documento::create(['cod_documento' => 'DOC_OP', 'cod_residente' => $this->residente->getKey(), 'nombre' => 'Documento sintético', 'tipo_documento' => 'ADMINISTRATIVO', 'ruta_archivo' => 'documentos/sintetico.pdf', 'tipo_archivo' => 'application/pdf', 'hash_archivo' => str_repeat('a', 64), 'fecha_vencimiento' => today()->addDays(10), 'estado' => 'PENDIENTE']);
        SeguroResidente::create(['cod_seguro' => 'SEG_OP', 'cod_residente' => $this->residente->getKey(), 'entidad' => 'Entidad sintética', 'plan' => 'Plan de prueba', 'estado' => 'ACTIVO']);
        Actividad::create(['cod_actividad' => 'ACT_OP', 'cod_area' => 'ARE_OP', 'cod_personal' => 'PER_OP', 'tipo' => 'INSTITUCIONAL', 'nombre' => 'Actividad sintética', 'fecha_hora' => now(), 'cupo' => 10, 'lugar' => 'Sala de prueba', 'estado' => 'PROGRAMADA']);
        Visita::create(['cod_visita' => 'VIS_OP', 'cod_residente' => $this->residente->getKey(), 'cod_contacto' => 'CTO_OP', 'cod_usuario_autorizacion' => $this->usuario->getKey(), 'fecha_hora_programada' => now(), 'estado' => 'PROGRAMADA']);
        Alerta::create(['cod_alerta' => 'ALE_OP', 'cod_residente' => $this->residente->getKey(), 'tipo' => 'INSTITUCIONAL', 'prioridad' => 'ALTA', 'titulo' => 'Alerta sintética', 'descripcion' => 'Prueba', 'fecha_hora' => now(), 'generacion' => 'MANUAL', 'estado' => 'ABIERTA']);
        Incidente::create(['cod_incidente' => 'INC_OP', 'cod_residente' => $this->residente->getKey(), 'cod_personal' => 'PER_OP', 'tipo_incidente' => 'Prueba', 'gravedad' => 'Registrada', 'fecha_hora' => now(), 'descripcion' => 'Registro sintético', 'requiere_medico' => false, 'requiere_derivacion' => false, 'estado' => 'REGISTRADO']);
    }
}

class ExploradorResidenteDenegadoPolicy
{
    public function viewAny(User $usuario): bool
    {
        return true;
    }

    public function view(User $usuario, Residente $residente): bool
    {
        return false;
    }
}
