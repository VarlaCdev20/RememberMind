<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\PersonalInstitucionalPanel;
use App\Modulos\Institucion\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalHorarios;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PersonalInstitucionalResponsiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_con_asignacion_consulta_el_turno_mediante_su_jornada_sin_mutar_datos(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('SUPERADMINISTRADOR');
        $personal = User::factory()->create(['nombres' => 'Profesional de prueba', 'apellido_paterno' => 'Institucional']);
        $personal->assignRole('ENFERMEROS');
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno de prueba con jornada', 'hora_inicio' => '07:00:00', 'hora_cierre' => '15:00:00', 'estado' => 'ACTIVO', 'orden' => 1]);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        $asignacion = AsignacionPersonal::create(['cod_personal' => $personal->personal->getKey(), 'cod_area' => $area->getKey(), 'cod_jornada' => $jornada->getKey(), 'estado' => 'ACTIVA']);
        $antes = $asignacion->fresh()->getAttributes();
        $this->actingAs($actor)->get('/admin/personal-institucional')->assertOk()->assertSeeText($turno->nombre);
        Livewire::test(PersonalInstitucionalPanel::class)->assertSee($personal->correo)->assertSee($turno->nombre)
            ->call('cambiarVistaListadoResumen', 'tarjetas')->assertSee($turno->nombre);
        $this->assertSame($antes, $asignacion->fresh()->getAttributes());
        $this->assertDatabaseCount('asignaciones_personal', 1);
    }

    public function test_secciones_de_superadministracion_conservan_filtros_y_acciones_al_renderizar(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $gerente = User::factory()->create(['nombres' => 'Alicia', 'ap_paterno' => 'Prueba']);
        $gerente->assignRole('SUPERADMINISTRADOR');
        $personal = User::factory()->create([
            'nombres' => 'Elena Patricia de los Ángeles', 'ap_paterno' => 'Prueba de Nombre Extenso',
            'ap_materno' => 'Ejemplo', 'correo' => 'nombre.muy.extenso.de.personal@remembermind.test',
        ]);
        $personal->assignRole('ENFERMEROS');
        $this->actingAs($gerente);

        $panel = Livewire::test(PersonalInstitucionalPanel::class)
            ->assertSet('vistaListadoResumen', 'tabla')
            ->assertSee('Buscar personal')->assertSee('Tipo de personal')
            ->assertSee('staff-search', false)->assertSee($personal->correo);
        $this->exportar('resumen', $panel->html());

        $panel->set('rubroResumen', 'salud')->set('estadoResumen', 'activo')
            ->assertSet('rubroResumen', 'salud')->assertSet('estadoResumen', 'activo')
            ->assertSee($personal->correo)->assertSee('Filtros activos:');
        $this->exportar('filtros', $panel->html());
        $panel->call('limpiarFiltrosResumen')->assertSet('rubroResumen', 'institucional')
            ->assertSet('estadoResumen', '')->assertSet('turnoResumen', '')->assertSet('busquedaResumen', '');

        $panel->call('cambiarVistaListadoResumen', 'tabla')
            ->assertSee('Directorio de personal')->assertSee('Horarios de')
            ->assertSee($personal->correo);
        $this->exportar('lista', $panel->html());
        $panel->call('cambiarVistaListadoResumen', 'tarjetas')->assertSee($personal->correo);
        $this->exportar('tarjetas', $panel->html());
        $panel->call('cambiarVistaListadoResumen', 'tabla');

        foreach (['salud', 'admin', 'disponibilidad', 'reportes'] as $tab) {
            $panel->call('setTab', $tab)->assertSet('tabActiva', $tab);
            if ($tab === 'salud') {
                $panel->assertSee($personal->correo)->assertSee('data-label="Área / Turno"', false);
            }
            $this->exportar($tab, $panel->html());
        }

        $panel->call('setTab', 'resumen')->set('busquedaResumen', 'Sin coincidencia sintética')
            ->assertSee('No se encontró personal');
        $this->exportar('vacio', $panel->html());

        $panel->set('busquedaResumen', '')->call('abrirHorariosPersonal', $personal->getKey())
            ->assertSet('modalGestionAbierto', true)->assertSee('Cerrar ficha de personal');
        $this->exportar('horarios', $panel->html());

        $horarios = Livewire::test(PersonalInstitucionalHorarios::class, ['usuarioId' => $personal->getKey()])
            ->call('abrirFormNuevo')->assertSet('formAbierto', true)
            ->assertSee('staff-schedule-f_cod_area', false)->assertSee('Días laborales');
        $this->exportar('formulario-horario', $horarios->html());

        // La colección está bloqueada contra payloads del cliente: poblarla desde SQL sintético.
        $area = Area::create(['nombre' => 'Área institucional sintética de acompañamiento', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Turno institucional de prueba con nombre extenso', 'hora_inicio' => '08:00:00', 'hora_cierre' => '16:00:00', 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2026-10-17', 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $personal->personal->getKey(), 'cod_area' => $area->getKey(), 'cod_jornada' => $jornada->getKey(), 'tipo_asignacion' => 'ROTATIVO', 'estado' => 'ACTIVA']);
        $horarios->call('cancelarFormulario')->call('cargarDatos')
            ->assertSee('data-label="Días"', false)->assertSee('Ver detalle');
        $this->exportar('asignaciones', $horarios->html());

        $panel->call('cerrarModal')->assertSet('modalGestionAbierto', false);
        $this->assertDatabaseCount('asignaciones_personal', 1);
        $this->assertTrue($gerente->can('usuarios.crear'));
    }

    public function test_directorio_permite_recorrer_todo_el_personal_y_reinicia_pagina_al_filtrar(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $gerente = User::factory()->create(['nombres' => 'Gerente', 'ap_paterno' => 'Prueba', 'correo' => 'direccion@remembermind.test']);
        $gerente->assignRole('GERENTE');
        for ($index = 0; $index < 15; $index++) {
            $persona = User::factory()->create(['nombres' => 'Personal', 'ap_paterno' => 'Sintético', 'correo' => sprintf('staff%02d@remembermind.test', $index)]);
            $persona->assignRole('ENFERMEROS');
        }
        $this->actingAs($gerente);
        $panel = Livewire::test(PersonalInstitucionalPanel::class)
            ->assertSee('staff00@remembermind.test')->assertDontSee('staff14@remembermind.test')
            ->assertSee('Página siguiente');
        $this->exportar('paginado', $panel->html());
        $panel->call('nextPage', 'personalPage')->assertSee('staff14@remembermind.test')
            ->assertDontSee('staff00@remembermind.test');
        $panel->set('busquedaResumen', 'staff00')->assertSet('paginators.personalPage', 1)
            ->assertSee('staff00@remembermind.test')->assertDontSee('staff14@remembermind.test');
        $panel->call('limpiarFiltrosResumen')->call('nextPage', 'personalPage')
            ->call('cambiarVistaListadoResumen', 'tarjetas')->assertSee('staff14@remembermind.test');
        $panel->set('estadoResumen', 'activo')->assertSet('paginators.personalPage', 1);
        $this->assertDatabaseCount('asignaciones_personal', 0);
        $this->assertFalse($gerente->can('usuarios.crear'));
    }

    private function exportar(string $estado, string $html): void
    {
        // Optional offline fixtures contain only the synthetic records created above.
        $directory = getenv('RM_STAFF_QA_DIR');
        if (! $directory) {
            return;
        }
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($directory.'/staff-'.$estado.'.html', $html);
    }
}
