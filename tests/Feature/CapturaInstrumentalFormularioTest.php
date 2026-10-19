<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Atencion;
use App\Models\DocumentoClinico;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\OpcionPregunta;
use App\Models\Personal;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\CapturasInstrumentalesResidente;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AplicarInstrumento;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\EvaluacionGeriatricaAreaModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\EvaluacionGeriatricaModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\EvaluacionesAreaPanel;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\SaludEvaluacionesGeriatricasPanel;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class CapturaInstrumentalFormularioTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    private Personal $personal;

    private Residente $residente;

    private Instrumento $instrumento;

    private array $preguntas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-19 12:00:00'));
        config(['remembermind.superadmin_clinical_write' => false]);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario->assignRole('PSICOLOGO/A');
        $this->personal = Personal::create(['cod_usuario' => $this->usuario->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético',
            'numero_documento' => 'FORM-TEST', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $this->personal->getKey(), 'cod_jornada' => $jornada->getKey(),
            'cod_area' => $area->getKey(), 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->instrumento = Instrumento::create(['codigo' => 'FORM-TEST', 'nombre' => 'Instrumento sintético', 'tipo' => 'COGNITIVO', 'version' => 'TEST-1', 'estado' => 'ACTIVO']);
        foreach (['NUMERICO', 'LOGICO', 'OPCION', 'TEXTO'] as $i => $tipo) {
            $this->preguntas[$tipo] = PreguntaInstrumento::create(['cod_instrumento' => $this->instrumento->getKey(), 'codigo' => 'Q-'.$i,
                'enunciado' => 'Pregunta sintética '.$tipo, 'tipo_respuesta' => $tipo, 'orden' => $i, 'estado' => 'ACTIVA']);
        }
        $this->actingAs($this->usuario);
    }

    private function abrir(bool $area = false)
    {
        return $area ? Livewire::test(EvaluacionGeriatricaAreaModal::class)->call('abrir', ['cod_residente' => $this->residente->getKey()])
            : Livewire::test(EvaluacionGeriatricaModal::class, ['cod_residente' => $this->residente->getKey()])->call('abrir', $this->residente->getKey());
    }

    public function test_ambas_entradas_registran_respuestas_con_null_distinto_de_cero_y_autoria_propia(): void
    {
        foreach ([false, true] as $area) {
            $this->abrir($area)->set('cod_instrumento', $this->instrumento->getKey())
                ->set('fecha_eval', '2023-11-15')->set('hora_eval', '08:20:10')
                ->set('valores.'.$this->preguntas['NUMERICO']->getKey(), '0')
                ->set('valores.'.$this->preguntas['LOGICO']->getKey(), '0')
                ->call('revisar')->assertSet('confirmando', true)->call('guardar')->assertHasNoErrors()->assertSet('mostrar', false)
                ->assertDispatched('evaluacion-geriatrica-guardada');
        }
        self::assertDatabaseCount('aplicaciones_instrumento', 2);
        self::assertDatabaseCount('respuestas_instrumento', 4);
        foreach (AplicacionInstrumento::all() as $a) {
            self::assertSame($this->personal->getKey(), $a->cod_personal);
            self::assertSame('2023-11-15 08:20:10', $a->fecha_hora->format('Y-m-d H:i:s'));
            self::assertNull($a->puntaje_total);
            self::assertNull($a->clasificacion);
        }
        self::assertDatabaseHas('respuestas_instrumento', ['cod_pregunta' => $this->preguntas['NUMERICO']->getKey(), 'valor_numero' => 0]);
        self::assertDatabaseHas('respuestas_instrumento', ['cod_pregunta' => $this->preguntas['LOGICO']->getKey(), 'valor_logico' => false]);
        $fuentes = app(CapturasInstrumentalesResidente::class)->consultar($this->usuario, $this->residente, CarbonImmutable::now());
        self::assertCount(2, $fuentes['capturas']);
        self::assertSame('SIN_PERFIL_EXPLICITO', $fuentes['capturas'][0]['motivos'][0]);
    }

    public static function invalidos(): array
    {
        return [['hora_eval', ''], ['hora_eval', '23:30'], ['fecha_eval', '2026-10-20'], ['NUMERICO', '0.12345'], ['NUMERICO', 'sin dato'], ['LOGICO', 'Sí']];
    }

    #[DataProvider('invalidos')]
    public function test_error_de_captura_no_escribe_y_conserva_valores(string $campo, string $valor): void
    {
        $error = isset($this->preguntas[$campo]) ? 'valores.'.$this->preguntas[$campo]->getKey() : $campo;
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())
            ->set('valores.'.$this->preguntas['NUMERICO']->getKey(), '1')->set($error, $valor)->call('revisar')
            ->assertHasErrors($error)->assertSet('mostrar', true)->assertSet($error, $valor);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_no_acepta_pregunta_u_opcion_ajena_y_una_opcion_correcta_se_puntua_en_backend(): void
    {
        $ajena = PreguntaInstrumento::create(['cod_instrumento' => Instrumento::create(['codigo' => 'OTRO', 'nombre' => 'Otro sintético', 'tipo' => 'COGNITIVO', 'estado' => 'ACTIVO'])->getKey(),
            'codigo' => 'OTRA', 'enunciado' => 'Otra sintética', 'tipo_respuesta' => 'NUMERICO', 'orden' => 1, 'estado' => 'ACTIVA']);
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('valores.'.$ajena->getKey(), 10)->call('revisar')->assertHasErrors('valores.'.$ajena->getKey());
        $opcion = OpcionPregunta::create(['cod_pregunta' => $this->preguntas['OPCION']->getKey(), 'nombre' => 'Opción sintética', 'puntaje' => 2, 'orden' => 1, 'estado' => 'ACTIVO']);
        $otra = OpcionPregunta::create(['cod_pregunta' => $ajena->getKey(), 'nombre' => 'Otra opción', 'orden' => 1, 'estado' => 'ACTIVO']);
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('valores.'.$this->preguntas['OPCION']->getKey(), $otra->getKey())
            ->call('revisar')->assertHasErrors('valores.'.$this->preguntas['OPCION']->getKey());
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('valores.'.$this->preguntas['OPCION']->getKey(), $opcion->getKey())
            ->call('revisar')->call('guardar')->assertHasNoErrors();
        self::assertDatabaseHas('respuestas_instrumento', ['cod_opcion' => $opcion->getKey(), 'puntaje' => 2]);
    }

    public function test_confirmacion_reenvio_y_cancelacion_no_duplican_ni_pierden_captura(): void
    {
        $c = $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('valores.'.$this->preguntas['NUMERICO']->getKey(), 3);
        $c->call('guardar')->assertHasErrors('registro');
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        $c->call('cerrar')->assertSet('confirmarDescarte', true)->assertSet('mostrar', true)
            ->call('volver')->assertSet('valores.'.$this->preguntas['NUMERICO']->getKey(), 3)
            ->call('revisar')->call('guardar')->assertHasNoErrors()->call('guardar');
        self::assertDatabaseCount('aplicaciones_instrumento', 1);
        self::assertDatabaseCount('respuestas_instrumento', 1);
        $this->abrir()->set('hora_eval', '08:00')->call('cerrar')->assertSet('confirmarDescarte', true)->call('descartar')->assertSet('mostrar', false);
    }

    public function test_cambio_de_residente_no_descarta_respuestas_y_residente_fijo_rechaza_manipulacion(): void
    {
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->abrir(true)->set('cod_instrumento', $this->instrumento->getKey())->set('valores.'.$this->preguntas['NUMERICO']->getKey(), 3)
            ->set('cod_residente', $otro->getKey())->assertHasErrors('cod_residente')->assertSet('cod_residente', $this->residente->getKey());
        $this->abrir()->set('cod_residente', $otro->getKey())->assertForbidden();
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public function test_cambiar_array_completo_de_respuestas_invalida_confirmacion(): void
    {
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('valores.'.$this->preguntas['NUMERICO']->getKey(), 3)
            ->call('revisar')->assertSet('confirmando', true)->set('valores', [$this->preguntas['NUMERICO']->getKey() => 9])
            ->assertSet('confirmando', false)->call('guardar')->assertHasErrors('registro');
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_revalida_catalogo_original_antes_y_dentro_del_guardado(): void
    {
        $c = $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('valores.'.$this->preguntas['NUMERICO']->getKey(), 3)->call('revisar');
        $huella = $c->get('huellaCatalogo');
        $codigo = $c->get('codigoReservado');
        $this->preguntas['NUMERICO']->update(['enunciado' => 'Pregunta sintética modificada']);
        $c->call('guardar')->assertHasErrors('cod_instrumento')->assertSet('mostrar', true)->assertSet('valores.'.$this->preguntas['NUMERICO']->getKey(), 3);
        try {
            app(AplicarInstrumento::class)->aplicar($this->usuario, ['fecha_hora' => '2026-10-19 09:00:00', 'respuestas' => [['cod_pregunta' => $this->preguntas['NUMERICO']->getKey(), 'valor_numero' => 3]]],
                $this->instrumento, $this->residente, $codigo, $huella);
            self::fail('Debe rechazar un catálogo distinto.');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('catalogo', $e->errors());
        }
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_fallo_al_escribir_respuestas_revierte_la_cabecera_y_muestra_error_seguro(): void
    {
        RespuestaInstrumento::creating(static fn () => throw new QueryException('sqlite', 'SQL PRIVADO', [], new \RuntimeException('DETALLE PRIVADO')));
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('valores.'.$this->preguntas['NUMERICO']->getKey(), 3)
            ->call('revisar')->call('guardar')->assertHasErrors('registro')->assertSee('Conservamos tus respuestas')
            ->assertDontSee('SQL PRIVADO')->assertDontSee('DETALLE PRIVADO')->assertSet('mostrar', true);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public static function restricciones(): array
    {
        return [['cuenta'], ['permiso'], ['personal'], ['asignacion'], ['residente']];
    }

    #[DataProvider('restricciones')]
    public function test_revocacion_posterior_a_confirmar_denegada_sin_escrituras(string $restriccion): void
    {
        $c = $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('valores.'.$this->preguntas['NUMERICO']->getKey(), 3)->call('revisar');
        match ($restriccion) {
            'cuenta' => DB::table('usuarios')->where('cod_usuario', $this->usuario->getKey())->update(['estado' => 'INACTIVO']),
            'permiso' => Role::findByName('PSICOLOGO/A')->revokePermissionTo('aplicaciones_instrumento.crear'),
            'personal' => $this->personal->update(['estado' => 'INACTIVO']),
            'asignacion' => AsignacionPersonal::query()->update(['estado' => 'INACTIVA']),
            'residente' => $this->residente->update(['estado' => 'EGRESADO']),
        };
        $c->call('guardar')->assertForbidden();
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_roles_administrativos_y_cuenta_sin_permiso_de_fuente_no_abren_formulario(): void
    {
        foreach (['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR', 'FAMILIAR'] as $rol) {
            $this->usuario->syncRoles([$rol]);
            $this->abrir()->assertForbidden();
        }
        $this->usuario->syncRoles(['PSICOLOGO/A']);
        Role::findByName('PSICOLOGO/A')->revokePermissionTo('preguntas_instrumento.ver');
        $this->abrir()->assertForbidden();
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public function test_resultados_abre_captura_para_residente_actual_sin_cambiar_evaluaciones(): void
    {
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Registrar mediciones')->call('abrirCapturaInstrumental')
            ->assertDispatched('evaluacion-geriatrica-abrir', cod_residente: $this->residente->getKey());
        self::assertDatabaseCount('evaluaciones_expertas', 0);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public function test_historial_actualiza_fuentes_y_no_declara_normal_una_captura_sin_clasificacion(): void
    {
        $historial = Livewire::test(SaludEvaluacionesGeriatricasPanel::class, ['adulto' => $this->residente])->assertSee('No existen evaluaciones');
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('valores.'.$this->preguntas['NUMERICO']->getKey(), 3)
            ->call('revisar')->call('guardar')->assertHasNoErrors();
        $historial->dispatch('evaluacion-geriatrica-guardada')->assertSee('Instrumento sintético')->assertSee('Sin clasificación')->assertSee('Nueva captura');
        $this->usuario->syncRoles(['FAMILIAR']);
        $historial->call('actualizarCapturas')->assertForbidden();
    }

    public function test_historial_sin_residente_muestra_seleccion_y_no_expone_aplicaciones(): void
    {
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('valores.'.$this->preguntas['NUMERICO']->getKey(), 3)
            ->call('revisar')->call('guardar')->assertHasNoErrors();
        Livewire::test(SaludEvaluacionesGeriatricasPanel::class)->assertSee('Seleccione un residente')->assertDontSee('Instrumento sintético');
        $this->usuario->syncRoles(['FAMILIAR']);
        Livewire::test(SaludEvaluacionesGeriatricasPanel::class)->assertForbidden();
    }

    public function test_listado_de_area_revalida_cuenta_rol_y_permisos_tras_abrir(): void
    {
        $listado = Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])->assertOk();
        $this->usuario->update(['estado' => 'INACTIVO']);
        $listado->call('$refresh')->assertForbidden();
        $this->usuario->update(['estado' => 'ACTIVO']);
        $listado = Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])->assertOk();
        Role::findByName('PSICOLOGO/A')->revokePermissionTo('aplicaciones_instrumento.ver');
        $listado->call('$refresh')->assertForbidden();
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public function test_lecturas_revalidan_personal_activo_y_superadministracion_conserva_consulta(): void
    {
        $area = Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])->assertOk();
        $historial = Livewire::test(SaludEvaluacionesGeriatricasPanel::class, ['adulto' => $this->residente])->assertOk();
        $this->personal->update(['estado' => 'INACTIVO']);
        $area->call('$refresh')->assertForbidden();
        $historial->call('$refresh')->assertForbidden();
        $this->usuario->syncRoles(['SUPERADMINISTRADOR']);
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])->assertOk();
        Livewire::test(SaludEvaluacionesGeriatricasPanel::class, ['adulto' => $this->residente])->assertOk();
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    private function abrirTranscripcion()
    {
        Storage::fake('local');
        Storage::disk('local')->put('original.txt', 'Original sintético de captura completa.');
        $doc = DocumentoClinico::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
            'tipo_documento' => 'INFORME', 'titulo' => 'Original sintético', 'ruta_archivo' => 'original.txt', 'formato' => 'text/plain',
            'hash_archivo' => hash('sha256', 'Original sintético de captura completa.'), 'fecha_hora' => now(), 'estado' => 'VIGENTE']);

        return $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('transcripcion', true)
            ->set('cod_documento_clinico', $doc->getKey())->set('cod_personal_original', $this->personal->getKey())
            ->set('localizador_fuente', 'Página 1 / ficha 1')->set('original_cotejado', true)
            ->set('fecha_eval', '2023-11-15')->set('hora_eval', '08:20:10');
    }

    public function test_transcripcion_completa_conserva_puntajes_cero_notas_y_cabecera_original_desde_formulario(): void
    {
        $this->preguntas['NUMERICO']->update(['puntaje_maximo' => 5]);
        $this->preguntas['OPCION']->update(['puntaje_maximo' => 2]);
        $opcion = OpcionPregunta::create(['cod_pregunta' => $this->preguntas['OPCION']->getKey(), 'nombre' => 'Opción sintética', 'puntaje' => 2, 'orden' => 1, 'estado' => 'ACTIVO']);
        $atencion = Atencion::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
            'cod_area' => AsignacionPersonal::sole()->cod_area, 'fecha_hora' => '2023-11-15 08:20:10', 'tipo_atencion' => 'SEGUIMIENTO_DIARIO', 'motivo' => 'Atención original sintética', 'estado' => 'FINALIZADA']);
        $this->abrirTranscripcion()->assertSee('Datos adicionales que figuran en el original')
            ->set('cod_atencion', $atencion->getKey())->set('clasificacion_original', 'Etiqueta original sintética')
            ->set('interpretacion_original', 'Texto original sintético, no conclusión del motor.')
            ->set('valores.'.$this->preguntas['NUMERICO']->getKey(), 0)->set('puntajesOriginales.'.$this->preguntas['NUMERICO']->getKey(), '0')
            ->set('observacionesRespuesta.'.$this->preguntas['NUMERICO']->getKey(), 'Nota original sintética de la respuesta.')
            ->set('valores.'.$this->preguntas['OPCION']->getKey(), $opcion->getKey())->set('puntajesOriginales.'.$this->preguntas['OPCION']->getKey(), '2')
            ->call('revisar')->assertSet('estadoConciliacion', 'NUEVA')->assertSet('confirmando', true)->call('guardar')->assertHasNoErrors();
        $a = AplicacionInstrumento::sole();
        self::assertSame($atencion->getKey(), $a->cod_atencion);
        self::assertSame('Etiqueta original sintética', $a->clasificacion);
        self::assertSame('Texto original sintético, no conclusión del motor.', $a->interpretacion);
        self::assertNull($a->puntaje_total);
        self::assertDatabaseHas('respuestas_instrumento', ['cod_aplicacion' => $a->getKey(), 'cod_pregunta' => $this->preguntas['NUMERICO']->getKey(), 'valor_numero' => 0, 'puntaje' => 0, 'observacion' => 'Nota original sintética de la respuesta.']);
        self::assertDatabaseHas('respuestas_instrumento', ['cod_aplicacion' => $a->getKey(), 'cod_opcion' => $opcion->getKey(), 'puntaje' => 2]);
    }

    public function test_cotejo_rechaza_puntuacion_incompatible_en_formulario_y_conserva_la_entrada(): void
    {
        $opcion = OpcionPregunta::create(['cod_pregunta' => $this->preguntas['OPCION']->getKey(), 'nombre' => 'Opción sintética', 'puntaje' => 2, 'orden' => 1, 'estado' => 'ACTIVO']);
        $this->abrirTranscripcion()->set('valores.'.$this->preguntas['OPCION']->getKey(), $opcion->getKey())
            ->set('puntajesOriginales.'.$this->preguntas['OPCION']->getKey(), 1)->call('revisar')
            ->assertHasErrors('registro')->assertSet('confirmando', false)->assertSet('puntajesOriginales.'.$this->preguntas['OPCION']->getKey(), 1)
            ->assertSee('La puntuación original no coincide');
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_puntuacion_ausente_sigue_null_y_cambiarla_invalida_confirmacion(): void
    {
        $this->preguntas['NUMERICO']->update(['puntaje_maximo' => 5]);
        $c = $this->abrirTranscripcion()->set('valores.'.$this->preguntas['NUMERICO']->getKey(), 0)->call('revisar')
            ->assertSet('confirmando', true)->set('puntajesOriginales', [$this->preguntas['NUMERICO']->getKey() => 0])
            ->assertSet('confirmando', false)->call('guardar')->assertHasErrors('registro');
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        $c->set('puntajesOriginales', [])->call('revisar')->call('guardar')->assertHasNoErrors();
        self::assertNull(RespuestaInstrumento::sole()->puntaje);
    }

    public function test_metadatos_sin_respuesta_se_conservan_al_cerrar_y_no_permiten_puntuar_una_pregunta_ajena(): void
    {
        $c = $this->abrirTranscripcion()->set('observacionesRespuesta.'.$this->preguntas['NUMERICO']->getKey(), 'Nota sintética')
            ->call('cerrar')->assertSet('confirmarDescarte', true)->call('volver')
            ->set('valores.'.$this->preguntas['LOGICO']->getKey(), false)->call('revisar')
            ->assertHasErrors('observacionesRespuesta.'.$this->preguntas['NUMERICO']->getKey());
        $c->set('observacionesRespuesta', [])->set('puntajesOriginales.PREGUNTA_AJENA', 0)->call('revisar')->assertHasErrors('puntajesOriginales.PREGUNTA_AJENA');
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public function test_datos_de_transcripcion_ocultos_no_se_incorporan_a_una_aplicacion_propia(): void
    {
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('valores.'.$this->preguntas['NUMERICO']->getKey(), 0)
            ->set('puntajesOriginales.'.$this->preguntas['NUMERICO']->getKey(), 999)->set('clasificacion_original', 'No pertenece a captura propia')
            ->set('observacionesRespuesta.'.$this->preguntas['NUMERICO']->getKey(), 'Tampoco pertenece a captura propia')
            ->call('revisar')->call('guardar')->assertHasNoErrors();
        self::assertNull(AplicacionInstrumento::sole()->clasificacion);
        self::assertNull(RespuestaInstrumento::sole()->puntaje);
        self::assertNull(RespuestaInstrumento::sole()->observacion);
    }

    public function test_se_puede_elegir_transcripcion_antes_del_primer_residente_sin_transferir_los_datos_a_otro(): void
    {
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        Livewire::test(EvaluacionGeriatricaAreaModal::class)->call('abrir', [])
            ->set('transcripcion', true)->set('cod_residente', $this->residente->getKey())
            ->assertHasNoErrors('cod_residente')->assertSet('cod_residente', $this->residente->getKey())
            ->set('cod_residente', $otro->getKey())->assertHasErrors('cod_residente')->assertSet('cod_residente', $this->residente->getKey());
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }
}
