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
use App\Modulos\Valoraciones\Aplicacion\Consultas\PrepararCapturaInstrumental;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\EvaluacionGeriatricaAreaModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\EvaluacionGeriatricaModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\EvaluacionesAreaPanel;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\SaludEvaluacionesGeriatricasPanel;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Tests\Support\EsquemaSinTransaccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use MotorExperto\instrumentos\NormalizadorHvltBolivia2015;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class CapturaInstrumentalFormularioTest extends TestCase
{
    use EsquemaSinTransaccion;

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
        $this->instrumento = Instrumento::create(['codigo' => 'FORM-TEST', 'nombre' => 'Instrumento sintético', 'tipo' => 'COGNITIVO', 'version' => '1.0', 'estado' => 'ACTIVO']);
        foreach (['NUMERICO', 'LOGICO', 'OPCION', 'TEXTO'] as $i => $tipo) {
            $this->preguntas[$tipo] = PreguntaInstrumento::create(['cod_instrumento' => $this->instrumento->getKey(), 'codigo' => 'Q-'.$i,
                'enunciado' => 'Pregunta sintética '.$tipo, 'tipo_respuesta' => $tipo, 'orden' => $i, 'estado' => 'ACTIVA']);
        }
        $this->actingAs($this->usuario);
    }

    private function abrir(bool $area = false)
    {
        return $area ? Livewire::test(EvaluacionGeriatricaAreaModal::class)->call('abrir', ['cod_residente' => $this->residente->getKey(), 'cod_area' => 'ARE_COG'])
            : Livewire::test(EvaluacionGeriatricaModal::class, ['cod_residente' => $this->residente->getKey()])->call('abrir', $this->residente->getKey());
    }

    public function test_contexto_muestra_escolaridad_de_referencia_sin_inferir_anios_ni_guardar_registros(): void
    {
        $this->residente->update(['nivel_educativo' => 'Primaria incompleta']);
        $antes = $this->residente->fresh()->getAttributes();

        foreach ([false, true] as $area) {
            $this->abrir($area)->set('cod_instrumento', $this->instrumento->getKey())
                ->assertSet('contexto.nivel_educativo', 'Primaria incompleta')
                ->assertSee('Escolaridad general del expediente:')
                ->assertSee('Primaria incompleta')
                ->assertSee('no indica los años cursados')
                ->assertSee('Idioma y comunicación')->assertSee('Fuente del relato');
        }

        self::assertSame($antes, $this->residente->fresh()->getAttributes());
        self::assertDatabaseCount('atenciones', 0);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public function test_escolaridad_ausente_se_muestra_desconocida_en_la_captura(): void
    {
        $this->residente->update(['nivel_educativo' => null]);

        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())
            ->assertSet('contexto.nivel_educativo', null)->assertSee('No registrada');
        self::assertNull($this->residente->fresh()->nivel_educativo);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    private function atencionCaptura(?Residente $residente = null, array $cambios = []): Atencion
    {
        return Atencion::create(array_replace(['cod_residente' => ($residente ?? $this->residente)->getKey(),
            'cod_personal' => $this->personal->getKey(), 'cod_area' => AsignacionPersonal::sole()->cod_area,
            'fecha_hora' => now()->subHour(), 'tipo_atencion' => 'SEGUIMIENTO', 'motivo' => 'Atención sintética de referencia',
            'observacion' => 'Nota clínica que el selector no debe cargar', 'estado' => 'FINALIZADA',
            'escolaridad_anios' => 0, 'puede_leer' => false, 'puede_escribir' => null], $cambios));
    }

    public function test_fuentes_de_atencion_se_filtran_por_residente_y_fecha_sin_exponer_notas(): void
    {
        $propia = $this->atencionCaptura();
        $antigua = $this->atencionCaptura(cambios: ['fecha_hora' => '2023-11-15 08:20:10']);
        $ajena = $this->atencionCaptura(Residente::factory()->create(['estado' => 'ADMITIDO']));
        $consulta = app(PrepararCapturaInstrumental::class);
        $lista = $consulta->atenciones($this->usuario, $this->residente->getKey(), '2023-11-15');
        self::assertSame([$antigua->getKey()], $lista->modelKeys());
        self::assertNull($lista->first()->getAttribute('observacion'));
        self::assertNull($consulta->atencion($this->usuario, $this->residente->getKey(), $ajena->getKey()));
        $this->abrir()->assertSet('cod_atencion', null)->assertViewHas('atencionVista', null)
            ->assertDontSee('Nota clínica que el selector no debe cargar');
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public function test_ambas_entradas_vinculan_atencion_sin_copiar_contexto_a_respuestas(): void
    {
        $atencion = $this->atencionCaptura();
        $original = $atencion->fresh()->getAttributes();
        foreach ([false, true] as $area) {
            $this->abrir($area)->set('cod_atencion', $atencion->getKey())
                ->set('cod_instrumento', $this->instrumento->getKey())->assertHasNoErrors()
                ->set('valores.'.$this->preguntas['NUMERICO']->getKey(), '1')
                ->assertSee('Atención vinculada')->assertSee('0 años')->assertSee('no copia estos datos')
                ->call('revisar')->assertSet('confirmando', true)->call('guardar')->assertHasNoErrors();
        }
        self::assertSame($original, $atencion->fresh()->getAttributes());
        self::assertDatabaseCount('aplicaciones_instrumento', 2);
        self::assertDatabaseCount('respuestas_instrumento', 2);
        foreach (AplicacionInstrumento::all() as $a) self::assertSame($atencion->getKey(), $a->cod_atencion);
        self::assertDatabaseHas('respuestas_instrumento', ['valor_numero' => 1]);
        self::assertDatabaseCount('valoraciones_petersen_originales', 0);
    }

    public function test_referencia_contextual_muestra_idioma_e_informante_solo_con_permisos(): void
    {
        $contacto = \App\Models\Contacto::create(['nombres' => 'Fuente', 'apellido_paterno' => 'Sintética', 'estado' => 'ACTIVO', 'correo' => 'no-exponer@example.invalid']);
        $v = \App\Models\ResidenteContacto::create(['cod_residente_contacto' => 'RC_CONTEXT_TEST', 'cod_residente' => $this->residente->getKey(),
            'cod_contacto' => $contacto->getKey(), 'parentesco' => 'FAMILIAR', 'responsable_principal' => false,
            'contacto_emergencia' => false, 'autoriza_informacion' => false, 'autoriza_salida' => false, 'estado' => 'ACTIVO']);
        $a = $this->atencionCaptura(cambios: ['idioma_principal_evaluacion' => 'Aymara',
            'cod_residente_contacto_informante' => $v->getKey(), 'informante_conoce_nivel_previo' => false]);
        $this->usuario->givePermissionTo(['contactos.ver', 'residentes_contactos.ver']);
        $c = $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('cod_atencion', $a->getKey())
            ->assertSee('Aymara')->assertSee('Fuente Sintética')->assertDontSee('no-exponer@example.invalid')
            ->set('valores.'.$this->preguntas['NUMERICO']->getKey(), '0')->call('revisar')
            ->assertSee('Fuente Sintética')->assertSee('no acredita por sí sola las condiciones');
        $this->usuario->revokePermissionTo(['contactos.ver', 'residentes_contactos.ver']);
        $this->usuario->roles()->first()->revokePermissionTo(['contactos.ver', 'residentes_contactos.ver']);
        $c->call('$refresh')->assertSee('Aymara')->assertDontSee('Fuente Sintética')
            ->assertSee('Consulta restringida por permisos de contactos.');
        $fuente = app(PrepararCapturaInstrumental::class)->atencion($this->usuario, $this->residente->getKey(), $a->getKey());
        self::assertArrayNotHasKey('cod_residente_contacto_informante', $fuente->getAttributes());
        self::assertFalse($fuente->relationLoaded('informantePrincipal'));
        self::assertDatabaseCount('evaluaciones_expertas', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_vinculo_ajeno_se_rechaza_en_formulario_y_backend_sin_escribir(): void
    {
        $ajena = $this->atencionCaptura(Residente::factory()->create(['estado' => 'ADMITIDO']));
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('cod_atencion', $ajena->getKey())
            ->set('valores.'.$this->preguntas['NUMERICO']->getKey(), '1')->call('revisar')->assertHasErrors('cod_atencion')
            ->assertSet('confirmando', false)->assertViewHas('atencionVista', null);
        try {
            app(AplicarInstrumento::class)->aplicar($this->usuario, ['fecha_hora' => now()->format('Y-m-d H:i:s'),
                'cod_atencion' => $ajena->getKey(), 'respuestas' => [['cod_pregunta' => $this->preguntas['NUMERICO']->getKey(), 'valor_numero' => 1]]],
                $this->instrumento, $this->residente);
            self::fail('Debe rechazar una atención de otro residente.');
        } catch (ValidationException $e) { self::assertArrayHasKey('cod_atencion', $e->errors()); }
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_sin_permiso_de_atenciones_se_puede_aplicar_sin_vinculo(): void
    {
        $atencion = $this->atencionCaptura();
        $this->usuario->roles()->first()->revokePermissionTo('atenciones.ver');
        $this->usuario = $this->usuario->fresh();
        $this->actingAs($this->usuario);
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())
            ->assertViewHas('atenciones', fn ($lista) => $lista->isEmpty())->assertViewHas('atencionVista', null)
            ->assertSee('Tu cuenta no tiene permiso para consultar atenciones')
            ->set('valores.'.$this->preguntas['NUMERICO']->getKey(), '0')->call('revisar')->call('guardar')->assertHasNoErrors();
        self::assertNull(AplicacionInstrumento::sole()->cod_atencion);
        self::assertDatabaseCount('atenciones', 1);
        self::assertSame(0, $atencion->fresh()->escolaridad_anios);
    }

    public function test_backend_rechaza_vincular_sin_permiso_de_lectura_de_atenciones(): void
    {
        $a = $this->atencionCaptura();
        $this->usuario->roles()->first()->revokePermissionTo('atenciones.ver');
        try {
            app(AplicarInstrumento::class)->aplicar($this->usuario, ['fecha_hora' => now()->format('Y-m-d H:i:s'),
                'cod_atencion' => $a->getKey(), 'respuestas' => [['cod_pregunta' => $this->preguntas['NUMERICO']->getKey(), 'valor_numero' => 0]]],
                $this->instrumento, $this->residente);
            self::fail('El backend debe comprobar la lectura de la atención.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { self::assertSame(403, $e->getStatusCode()); }
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_filtro_de_atenciones_invalido_muestra_error_sin_perder_captura(): void
    {
        $a = $this->atencionCaptura();
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('cod_atencion', $a->getKey())
            ->set('valores.'.$this->preguntas['NUMERICO']->getKey(), '0')->set('fechaAtencion', 'fecha inválida')
            ->assertHasErrors('fechaAtencion')->assertSet('cod_atencion', $a->getKey())
            ->assertSet('valores.'.$this->preguntas['NUMERICO']->getKey(), '0')
            ->assertViewHas('atenciones', fn ($lista) => $lista->isEmpty());
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public function test_permiso_revocado_despues_de_revisar_impide_guardar_el_vinculo(): void
    {
        $atencion = $this->atencionCaptura();
        $c = $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('cod_atencion', $atencion->getKey())
            ->set('valores.'.$this->preguntas['NUMERICO']->getKey(), 1)->call('revisar')->assertSet('confirmando', true);
        $this->usuario->roles()->first()->revokePermissionTo('atenciones.ver');
        $c->call('guardar')->assertForbidden();
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
    }

    public function test_cambiar_vinculo_invalida_revision_y_no_se_pierde_al_filtrar(): void
    {
        $a = $this->atencionCaptura(cambios: ['fecha_hora' => '2023-11-15 08:20:10']);
        $otra = $this->atencionCaptura();
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())->set('cod_atencion', $a->getKey())
            ->set('valores.'.$this->preguntas['NUMERICO']->getKey(), 1)->call('revisar')->assertSet('confirmando', true)
            ->set('cod_atencion', $otra->getKey())->assertSet('confirmando', false)
            ->set('fechaAtencion', '2023-11-15')->assertSet('cod_atencion', $otra->getKey())
            ->assertViewHas('atencionVista', fn ($atencion) => $atencion->getKey() === $otra->getKey())
            ->call('cerrar')->assertSet('confirmarDescarte', true);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public function test_areas_no_mezclan_catalogos_y_un_instrumento_historico_no_se_ofrece_para_aplicar(): void
    {
        Instrumento::create(['codigo' => 'BARTHEL_100', 'nombre' => 'Barthel sintético', 'tipo' => 'FUNCIONAL', 'estado' => 'ACTIVO']);

        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])
            ->assertSee('FORM-TEST')->assertDontSee('BARTHEL_100');
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_FUN'])
            ->assertSee('BARTHEL_100')->assertDontSee('FORM-TEST');
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_AFE'])
            ->assertDontSee('BARTHEL_100')->assertDontSee('Nueva Evaluación');

        $this->instrumento->update(['estado' => 'INACTIVO']);
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])
            ->assertSee('FORM-TEST')->assertSee('Histórico')->assertDontSee('Nueva Evaluación');
    }

    public function test_modal_de_area_rechaza_un_instrumento_activo_de_otra_area(): void
    {
        $otro = Instrumento::create(['codigo' => 'BARTHEL_100', 'nombre' => 'Barthel sintético', 'tipo' => 'FUNCIONAL', 'estado' => 'ACTIVO']);

        Livewire::test(EvaluacionGeriatricaAreaModal::class)
            ->call('abrir', ['cod_residente' => $this->residente->getKey(), 'cod_area' => 'ARE_COG'])
            ->set('cod_instrumento', $otro->getKey())
            ->assertStatus(422);
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

    public function test_resultados_es_solo_consulta_y_area_cognitiva_conserva_captura_instrumental(): void
    {
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertDontSee('Registrar mediciones');
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])
            ->assertSee('Nueva Evaluación')
            ->call('nuevaEvaluacion', $this->residente->getKey())
            ->assertDispatched('evaluacion-geriatrica-area-abrir');
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
            ->call('revisar')->assertSet('estadoConciliacion', 'NUEVA')->assertSet('confirmando', true)
            ->assertSee('Documento cotejado')->assertSee('Original sintético')->assertSee('Página 1 / ficha 1')
            ->assertSee('Etiqueta original sintética')->assertSee('Texto original sintético, no conclusión del motor.')
            ->assertSee('Nota original sintética de la respuesta.')->call('guardar')->assertHasNoErrors();
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
        Livewire::test(EvaluacionGeriatricaAreaModal::class)->call('abrir', ['cod_area' => 'ARE_COG'])
            ->set('transcripcion', true)->set('cod_residente', $this->residente->getKey())
            ->assertHasNoErrors('cod_residente')->assertSet('cod_residente', $this->residente->getKey())
            ->set('cod_residente', $otro->getKey())->assertHasErrors('cod_residente')->assertSet('cod_residente', $this->residente->getKey());
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public function test_formulario_distingue_observacion_y_muestra_tareas_de_un_metodo_vinculado_por_identidad_exacta(): void
    {
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())
            ->assertSee('Sin método de interpretación listo');

        $selectores = [];
        foreach (NormalizadorHvltBolivia2015::ENTRADAS as $entrada => $tipo) {
            $pregunta = PreguntaInstrumento::create(['cod_instrumento' => $this->instrumento->getKey(),
                'codigo' => 'PROTOCOLO-'.count($selectores), 'enunciado' => 'Campo sintético '.$entrada,
                'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO',
                'dominio' => 'MEMORIA', 'orden' => count($selectores) + 10, 'estado' => 'ACTIVA']);
            $selectores[$entrada] = ['cod_pregunta' => $pregunta->getKey(),
                'campo' => $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero'];
        }
        config(['sistema_experto.perfiles_instrumentales' => [[
            'id' => 'PERFIL-SINTETICO', 'cod_instrumento' => $this->instrumento->getKey(),
            'codigo_instrumento' => $this->instrumento->codigo, 'version_instrumento' => $this->instrumento->version,
            'metodo' => NormalizadorHvltBolivia2015::METODO,
            'identidad_instrumento' => ['instrumento' => 'HVLT-R', 'forma' => 5, 'idioma' => 'es'],
            'selectores' => $selectores,
        ]]]);
        $catalogo = app(PrepararCapturaInstrumental::class)->catalogo($this->usuario, $this->residente->getKey(), $this->instrumento->getKey());
        self::assertSame('VINCULADO', $catalogo['vinculo_metodo']['estado']);
        self::assertCount(count(NormalizadorHvltBolivia2015::ENTRADAS), $catalogo['vinculo_metodo']['entradas']);
        $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())
            ->assertSee('Método vinculado')->assertSee('Campo sintético')->assertSee('MEMORIA')
            ->assertDontSee('Tarea PROTOCOLO-0');

        $incompleto = config('sistema_experto.perfiles_instrumentales.0');
        unset($incompleto['selectores']['escolaridad_anios']);
        config(['sistema_experto.perfiles_instrumentales' => [$incompleto]]);
        self::assertSame('VINCULO_INCOMPLETO', app(PrepararCapturaInstrumental::class)
            ->catalogo($this->usuario, $this->residente->getKey(), $this->instrumento->getKey())['vinculo_metodo']['estado']);

        $observacional = Instrumento::create(['codigo' => 'OBS-SINTETICO', 'nombre' => 'Observación sintética',
            'tipo' => 'OBSERVACIONAL', 'version' => '1', 'estado' => 'ACTIVO']);
        $this->abrir()->set('cod_instrumento', $observacional->getKey())
            ->assertSee('Registro observacional')->assertDontSee('Método vinculado');
    }

    public function test_revision_presenta_cero_no_y_ausencia_sin_inventar_puntuacion(): void
    {
        $componente = $this->abrir()->set('cod_instrumento', $this->instrumento->getKey())
            ->set('valores.'.$this->preguntas['NUMERICO']->getKey(), '0')
            ->set('valores.'.$this->preguntas['LOGICO']->getKey(), '0')
            ->call('revisar')->assertSet('confirmando', true)
            ->assertSee('Revisión de la captura')->assertSee('2 de 4')->assertSee('2 sin registrar')
            ->assertSee('Sin registrar')->assertSee('No')->assertDontSee('Pregunta sintética NUMERICO · 0 puntos');

        $resumen = $componente->instance()->render()->getData()['resumenCaptura'];
        self::assertSame(2, $resumen['respondidas']);
        self::assertSame('0', $resumen['respuestas'][0]['valor']);
        self::assertSame('No', $resumen['respuestas'][1]['valor']);
        self::assertSame('Sin registrar', $resumen['respuestas'][2]['valor']);
        self::assertNull($resumen['respuestas'][0]['puntaje']);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);

        $componente->call('volver')->assertSet('confirmando', false)
            ->assertSet('valores.'.$this->preguntas['NUMERICO']->getKey(), '0')
            ->assertSet('valores.'.$this->preguntas['LOGICO']->getKey(), '0');
    }

    public function test_revision_muestra_nombre_de_opcion_y_conserva_texto_original(): void
    {
        $opcion = OpcionPregunta::create(['cod_pregunta' => $this->preguntas['OPCION']->getKey(),
            'nombre' => 'Respuesta elegida sintética', 'puntaje' => 2, 'orden' => 1, 'estado' => 'ACTIVO']);
        $componente = $this->abrir(true)->set('cod_instrumento', $this->instrumento->getKey())
            ->set('valores.'.$this->preguntas['OPCION']->getKey(), $opcion->getKey())
            ->set('valores.'.$this->preguntas['TEXTO']->getKey(), 'Relato original del informante sintético.')
            ->call('revisar')->assertSet('confirmando', true)->assertSee('Respuesta elegida sintética')
            ->assertSee('Relato original del informante sintético.');

        $resumen = $componente->instance()->render()->getData()['resumenCaptura'];
        self::assertSame('Respuesta elegida sintética', $resumen['respuestas'][2]['valor']);
        self::assertSame('Relato original del informante sintético.', $resumen['respuestas'][3]['valor']);
        self::assertDatabaseCount('respuestas_instrumento', 0);
        $componente->call('guardar')->assertHasNoErrors()->assertSet('mostrar', false);
        self::assertDatabaseHas('respuestas_instrumento', ['cod_pregunta' => $this->preguntas['TEXTO']->getKey(),
            'valor_texto' => 'Relato original del informante sintético.']);
    }

    public static function entradasLoteTecnico(): array
    {
        return array_combine(['contexto', 'catalogo', 'aplicar', 'conciliar', 'transcribir'],
            array_map(static fn ($operacion) => [$operacion], ['contexto', 'catalogo', 'aplicar', 'conciliar', 'transcribir']));
    }

    private function registrarMarcaAdmision(?string $observacion): void
    {
        \App\Models\Admision::create(['cod_residente' => $this->residente->getKey(),
            'cod_usuario_registro' => $this->usuario->getKey(), 'fecha_hora_admision' => now(),
            'motivo_ingreso' => 'Fixture de validación de origen.', 'estado' => 'VIGENTE', 'observacion' => $observacion]);
    }

    #[DataProvider('entradasLoteTecnico')]
    public function test_lote_tecnico_no_admite_contexto_ni_captura_directa(string $operacion): void
    {
        $this->residente->update(['numero_documento' => 'COH30-S01']);
        $this->registrarMarcaAdmision('COHORTE-INFERENCIA-30-V1 · DATOS SINTÉTICOS, USO EXCLUSIVO DE PRUEBAS.');
        $entrada = ['fecha_hora' => now()->subMinute()->format('Y-m-d H:i:s'),
            'respuestas' => [['cod_pregunta' => $this->preguntas['NUMERICO']->getKey(), 'valor_numero' => 0]]];
        $auditoriaAntes = DB::table('activity_log')->count();
        try {
            match ($operacion) {
                'contexto' => app(PrepararCapturaInstrumental::class)->contexto($this->usuario, $this->residente->getKey()),
                'catalogo' => app(PrepararCapturaInstrumental::class)->catalogo($this->usuario, $this->residente->getKey(), $this->instrumento->getKey()),
                default => app(AplicarInstrumento::class)->{$operacion}($this->usuario, $entrada, $this->instrumento, $this->residente),
            };
            self::fail('El lote retirado no debe admitir captura ni exposición por petición directa.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            self::assertDatabaseCount('aplicaciones_instrumento', 0);
            self::assertDatabaseCount('respuestas_instrumento', 0);
            self::assertSame($auditoriaAntes, DB::table('activity_log')->count());
            self::assertDatabaseCount('residentes', 1);
            self::assertDatabaseCount('admisiones', 1);
        }
    }

    public static function marcasNoConcluyentes(): array
    {
        return ['solo_documento' => ['COH30-S02', null],
            'solo_observacion' => ['DOCUMENTO-ORDINARIO', 'COHORTE-INFERENCIA-30-V1 · DATOS SINTÉTICOS, USO EXCLUSIVO DE PRUEBAS.'],
            'ninguna' => ['DOCUMENTO-ORDINARIO', null]];
    }

    #[DataProvider('marcasNoConcluyentes')]
    public function test_un_registro_original_valido_no_se_rechaza_por_una_marca_aislada(string $documento, ?string $observacion): void
    {
        $this->residente->update(['numero_documento' => $documento]);
        $this->registrarMarcaAdmision($observacion);
        $preparador = app(PrepararCapturaInstrumental::class);
        self::assertNotEmpty($preparador->contexto($this->usuario, $this->residente->getKey())['profesional']);
        self::assertSame('SIN_VINCULO', $preparador->catalogo($this->usuario, $this->residente->getKey(), $this->instrumento->getKey())['vinculo_metodo']['estado']);
        $a = app(AplicarInstrumento::class)->aplicar($this->usuario,
            ['fecha_hora' => now()->subMinute()->format('Y-m-d H:i:s'), 'respuestas' => [
                ['cod_pregunta' => $this->preguntas['NUMERICO']->getKey(), 'valor_numero' => 0],
                ['cod_pregunta' => $this->preguntas['LOGICO']->getKey(), 'valor_logico' => false],
            ]], $this->instrumento, $this->residente);
        self::assertSame($this->personal->getKey(), $a->cod_personal);
        self::assertNull($a->puntaje_total);
        self::assertEquals(0, $a->respuestas->firstWhere('cod_pregunta', $this->preguntas['NUMERICO']->getKey())->valor_numero);
        self::assertFalse($a->respuestas->firstWhere('cod_pregunta', $this->preguntas['LOGICO']->getKey())->valor_logico);
        self::assertDatabaseCount('aplicaciones_instrumento', 1);
        self::assertDatabaseCount('respuestas_instrumento', 2);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

}
