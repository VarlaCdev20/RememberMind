<?php

namespace Tests\Feature;

use App\Livewire\Admisiones\DecisionAdmisionModal;
use App\Livewire\Admisiones\HabitacionesPanel;
use App\Livewire\Admisiones\PreadmisionesPanel;
use App\Livewire\Admisiones\PreadmisionWizard;
use App\Models\AdultoMayor;
use App\Models\AsignacionAdultoMayor;
use App\Models\Cama;
use App\Models\EstadoAdulto;
use App\Models\Habitacion;
use App\Models\HistorialEstadoAdulto;
use App\Models\Preadmision;
use App\Models\User;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdmisionesInfraestructuraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);
    }

    public function test_decision_guarda_claves_de_estado_y_autor_del_historial(): void
    {
        $estado = EstadoAdulto::create(['estado' => 'DECISION_ADMISION']);
        $adulto = AdultoMayor::factory()->create(['cod_est_adul' => $estado->cod_est_adul]);
        Livewire::test(DecisionAdmisionModal::class)->call('open', $adulto->cod_am)
            ->set('decision', 'ADMITIDO_NORMAL')->set('motivo_decision', 'Valoración profesional completada.')
            ->call('guardar')->assertHasNoErrors()->assertSet('isOpen', false);
        $this->assertSame('PENDIENTE_ASIGNACION', $adulto->fresh()->estado->estado);
        $historial = HistorialEstadoAdulto::sole();
        $this->assertSame($estado->cod_est_adul, $historial->estado_anterior);
        $this->assertSame($adulto->fresh()->cod_est_adul, $historial->estado_nuevo);
        $this->assertSame(auth()->id(), $historial->cambiado_por);
    }

    public function test_habitacion_y_cama_se_crean_editan_y_respetan_capacidad(): void
    {
        $panel = Livewire::test(HabitacionesPanel::class)->call('abrirCrearHabitacion')
            ->set('codigo', '201')->set('nombre', 'Habitación 201')->set('tipoHabitacion', 'INDIVIDUAL')
            ->set('capacidad', '1')->call('guardarHabitacion')->assertHasNoErrors();
        $habitacion = Habitacion::sole();
        $panel->call('abrirCrearCama', $habitacion->cod_habitacion)->set('codigoCama', '201-A')->call('guardarCama')->assertHasNoErrors();
        $cama = Cama::sole();
        $panel->call('verCamas', $habitacion->cod_habitacion)->assertSee('201-A')
            ->call('editarCama', $cama->cod_cama)->set('estadoCama', 'MANTENIMIENTO')
            ->call('guardarCama')->assertHasNoErrors();
        $this->assertSame('MANTENIMIENTO', $cama->fresh()->estado);
        $panel->call('abrirCrearCama', $habitacion->cod_habitacion)->set('codigoCama', '201-B')
            ->call('guardarCama')->assertHasErrors('codigoCama');
        $this->assertDatabaseCount('camas', 1);
        $panel->call('abrirEditarHabitacion', $habitacion->cod_habitacion)->set('nombre', 'Habitación renovada')
            ->call('guardarHabitacion')->assertHasNoErrors();
        $this->assertSame('Habitación renovada', $habitacion->fresh()->nombre);
    }

    public function test_una_ocupacion_activa_protege_la_cama_y_la_habitacion(): void
    {
        $estado = EstadoAdulto::firstOrCreate(['estado' => 'ADMITIDO']);
        $habitacion = Habitacion::create([
            'codigo' => '401', 'nombre' => 'Habitación 401', 'tipo_habitacion' => 'INDIVIDUAL',
            'capacidad' => 1, 'estado' => 'DISPONIBLE',
        ]);
        $cama = Cama::create(['cod_habitacion' => $habitacion->cod_habitacion, 'codigo' => '401-A', 'estado' => 'DISPONIBLE']);
        $adulto = AdultoMayor::factory()->create(['cod_est_adul' => $estado->cod_est_adul]);
        AsignacionAdultoMayor::create([
            'cod_am' => $adulto->cod_am, 'cod_habitacion' => $habitacion->cod_habitacion,
            'cod_cama' => $cama->cod_cama, 'fecha_asignacion' => today(), 'estado' => 'ACTIVO',
            'registrado_por' => auth()->id(),
        ]);

        Livewire::test(HabitacionesPanel::class)
            ->call('verCamas', $habitacion->cod_habitacion)
            ->assertSee($adulto->nombres)
            ->call('editarCama', $cama->cod_cama)
            ->set('estadoCama', 'MANTENIMIENTO')
            ->call('guardarCama')
            ->assertHasErrors('estadoCama');

        Livewire::test(HabitacionesPanel::class)
            ->call('abrirEditarHabitacion', $habitacion->cod_habitacion)
            ->set('estadoHab', 'BLOQUEADA')
            ->call('guardarHabitacion')
            ->assertHasErrors('estadoHab');

        $this->assertSame('OCUPADA', $cama->fresh()->estado);
        $this->assertSame('OCUPADA', $habitacion->fresh()->estado);
    }

    public function test_aprobar_no_crea_residente_y_la_admision_formal_si_lo_crea_con_cama(): void
    {
        $preadmision = Preadmision::create([
            'estado' => 'VALORACION_MEDICA_FINALIZADA', 'fecha_solicitud' => today(),
            'nombres' => 'ELENA', 'ap_paterno' => 'QUISPE', 'ap_materno' => 'MAMANI',
            'ci' => '4455777', 'expedicion_ci' => 'LP', 'fecha_nac' => '1947-04-08',
            'genero' => 'FEMENINO', 'estado_civil' => 'VIUDA', 'celular' => '70011223',
            'familiar_nombres' => 'ROSA', 'familiar_ap_paterno' => 'QUISPE',
            'familiar_ci' => '9988665', 'familiar_parentesco' => 'HIJA', 'familiar_celular' => '70099887',
            'motivo_ingreso' => 'CUIDADO_INTEGRAL', 'procedencia_ingreso' => 'FAMILIAR',
            'tipo_ingreso' => 'REGULAR', 'permanencia' => 'PERMANENTE', 'prioridad' => 'MEDIA',
            'descripcion_caso' => 'Ingreso validado para demostración administrativa.',
            'documentos_iniciales_completos' => true, 'documentos_institucionales_generados' => true,
            'creado_por' => auth()->id(),
        ]);

        Livewire::test(PreadmisionesPanel::class)->call('aprobar', $preadmision->cod_pre);
        $preadmision->refresh();
        $this->assertSame('APROBADA', $preadmision->estado);
        $this->assertNull($preadmision->cod_am_generado);
        $this->assertDatabaseCount('adulto_mayor', 0);

        $habitacion = Habitacion::create(['codigo' => '301', 'nombre' => 'Habitación 301', 'tipo_habitacion' => 'INDIVIDUAL', 'capacidad' => 1, 'estado' => 'DISPONIBLE']);
        $cama = Cama::create(['cod_habitacion' => $habitacion->cod_habitacion, 'codigo' => '301-A', 'estado' => 'DISPONIBLE']);

        Livewire::test(PreadmisionesPanel::class)
            ->call('abrirAdmision', $preadmision->cod_pre)
            ->assertSet('modalAdmision', true)
            ->set('habitacion_id', $habitacion->cod_habitacion)
            ->set('cama_id', $cama->cod_cama)
            ->set('fecha_ingreso', today()->toDateString())
            ->set('hora_ingreso', '09:30')
            ->set('nivel_educativo', 'PRIMARIA COMPLETA')
            ->set('grupo_sanguineo', 'O')
            ->set('factor_rh', '+')
            ->set('alergias', 'NINGUNA CONOCIDA')
            ->set('seguro_salud', 'CAJA NACIONAL DE SALUD')
            ->set('antecedentes', ['HIPERTENSION', 'PROBLEMAS_VISUALES'])
            ->set('restricciones_alimentarias', 'DIETA HIPOSÓDICA')
            ->set('observacion_medica', 'Requiere control periódico de presión arterial.')
            ->set('autoriza_informacion_medica', true)
            ->set('consentimiento_datos', true)
            ->set('observaciones_admision', 'Ingreso acompañado por su responsable principal.')
            ->call('formalizarAdmision')
            ->assertHasNoErrors()
            ->assertSet('modalAdmision', false);

        $preadmision->refresh();
        $this->assertSame('ADMITIDA', $preadmision->estado);
        $this->assertNotNull($preadmision->cod_am_generado);
        $adulto = AdultoMayor::findOrFail($preadmision->cod_am_generado);

        $adulto->refresh();
        $this->assertSame('ADMITIDO', $adulto->estado->estado);
        $this->assertSame($habitacion->cod_habitacion, $adulto->cod_habitacion);
        $this->assertSame($cama->cod_cama, $adulto->cod_cama);
        $this->assertSame('OCUPADA', $cama->fresh()->estado);
        $this->assertTrue(AsignacionAdultoMayor::where('cod_am', $adulto->cod_am)->where('estado', 'ACTIVO')->exists());
        $this->assertDatabaseHas('ficha_medica_adulto', [
            'cod_am' => $adulto->cod_am,
            'hipertension' => true,
            'problemas_visuales' => true,
            'diabetes' => false,
        ]);
        $this->get(route('admin.adultos-mayores.show', $adulto))->assertOk()->assertSee('ELENA');
    }

    public function test_no_se_puede_formalizar_una_solicitud_pendiente(): void
    {
        $preadmision = Preadmision::create([
            'estado' => 'PENDIENTE', 'fecha_solicitud' => today(),
            'nombres' => 'MARTA', 'ap_paterno' => 'ROJAS', 'ci' => '7788991',
            'expedicion_ci' => 'LP', 'fecha_nac' => '1940-01-01', 'genero' => 'FEMENINO',
            'familiar_nombres' => 'ANA', 'familiar_ap_paterno' => 'ROJAS',
            'familiar_parentesco' => 'HIJA', 'familiar_celular' => '70000001',
        ]);

        Livewire::test(PreadmisionesPanel::class)
            ->call('abrirAdmision', $preadmision->cod_pre)
            ->assertSet('modalAdmision', false);

        $this->assertDatabaseCount('adulto_mayor', 0);
    }

    public function test_el_registro_inicial_crea_solo_una_preadmision_pendiente(): void
    {
        Storage::fake('public');

        Livewire::test(PreadmisionWizard::class)
            ->set('nombres', 'JUANA')
            ->set('ap_paterno', 'MAMANI')
            ->set('ap_materno', 'QUISPE')
            ->set('ci', '6677881')
            ->set('expedicion_ci', 'LP')
            ->set('fecha_nac', '1944-05-10')
            ->set('genero', 'FEMENINO')
            ->set('estado_civil', 'VIUDA')
            ->set('celular', '70012345')
            ->set('departamento_residencia', 'LA PAZ')
            ->set('ciudad_municipio', 'LA PAZ')
            ->set('zona', 'MIRAFLORES')
            ->set('calle', 'AVENIDA SAAVEDRA 120')
            ->set('familiar_nombres', 'MARÍA')
            ->set('familiar_ap_paterno', 'MAMANI')
            ->set('familiar_ci', '8877661')
            ->set('familiar_parentesco', 'HIJA')
            ->set('familiar_celular', '70123456')
            ->set('familiar_correo', 'familiar@example.com')
            ->set('familiar_direccion', 'ZONA MIRAFLORES, LA PAZ')
            ->set('motivo_ingreso', 'CUIDADO_INTEGRAL')
            ->set('procedencia_ingreso', 'DOMICILIO_FAMILIAR')
            ->set('tipo_ingreso', 'REGULAR')
            ->set('permanencia', 'PERMANENTE')
            ->set('prioridad', 'MEDIA')
            ->set('descripcion_caso', 'Solicita ingreso para cuidado integral y acompañamiento permanente.')
            ->set('doc_ci_adulto', UploadedFile::fake()->image('ci-adulto.jpg'))
            ->set('doc_ci_familiar', UploadedFile::fake()->image('ci-familiar.jpg'))
            ->set('doc_solicitud_ingreso', UploadedFile::fake()->create('solicitud.pdf', 100, 'application/pdf'))
            ->call('confirmarPreadmision')
            ->assertHasNoErrors()
            ->assertSet('guardadoExitoso', true);

        $solicitud = Preadmision::sole();
        $this->assertSame('PENDIENTE', $solicitud->estado);
        $this->assertNull($solicitud->cod_am_generado);
        $this->assertNull($solicitud->cod_fam_generado);
        $this->assertDatabaseCount('adulto_mayor', 0);
        $this->assertDatabaseCount('familiares', 0);
    }
}
