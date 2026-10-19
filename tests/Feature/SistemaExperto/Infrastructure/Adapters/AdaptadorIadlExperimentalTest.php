<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Adapters;

use App\Models\ControlFuncionalIadl;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorIadlExperimental;
use DateTimeZone;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

/**
 * TÍTULO: Fuente IADL aprobada, leída sobre las migraciones reales.
 * CAPA: Infrastructure - Adapters (integración).
 * ¿PARA QUÉ SIRVE? Verifica identidad, corte, NULL, FK e historia sin interpretación clínica.
 * ¿QUÉ INFORMACIÓN RECIBE? Una base desechable y observaciones totalmente sintéticas.
 * ¿QUÉ HACE? Ejecuta adaptador, extractor, mapper y repositorio reales.
 * ¿QUÉ DEVUELVE? Aserciones sobre hechos descriptivos y rechazos sin efectos parciales.
 * ¿QUÉ NO DEBE HACER? No crea residentes como fallback ni acredita C03 desde la causa de ayuda.
 */
final class AdaptadorIadlExperimentalTest extends AdaptadoresTestCase
{
    /** La observación NULL permanece NULL y no se crea acreditación C03 o evidencia cognitiva. */
    public function test_null_y_procedencia_se_conservan_en_la_memoria(): void
    {
        $this->prepararIadl();
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $m = $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
        self::assertNull($m->hechos['SIM-EVID']->valor);
        self::assertSame('SIM-IADL-1', $m->hechos['SIM-EVID']->origenId);
        self::assertSame('SIM-AT', $m->hechos['SIM-EVID']->episodioId);
        self::assertSame([], $m->evidencias);
        self::assertArrayNotHasKey('DCL-C03', $m->hechos);
        $this->exigirSoloLecturas();
    }

    /** @return iterable<string, array{string}> Causas que se transportan sin convertirlas en atribución acreditada. */
    public static function causas(): iterable
    {
        foreach (['FISICA', 'INSTITUCIONAL', 'COGNITIVA'] as $causa) {
            yield $causa => [$causa];
        }
    }

    /** @param string $causa Dato físico original. No asigna desempeño ni independencia desde su texto. */
    #[DataProvider('causas')]
    public function test_causa_es_descriptiva_y_no_acredita_c03(string $causa): void
    {
        $this->prepararIadl(['causa_ayuda' => $causa], 'causa_ayuda');
        $m = $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
        self::assertSame($causa, $m->hechos['SIM-EVID']->valor);
        self::assertSame([], $m->evidencias);
        self::assertCount(1, $m->hechos);
    }

    /** La fila futura no expone valores ni se incorpora a la memoria hasta el corte. */
    public function test_futuro_no_carga_observacion_y_se_excluye(): void
    {
        $this->prepararIadl(['fecha_hora' => '2026-10-18 12:00:00', 'observacion' => 'NO-DEBE-LEERSE']);
        $filas = (new AdaptadorIadlExperimental)->recuperar($this->evaluacion(), ['SIM-IADL-1'], self::corte(),
            $this->conexionPruebas(), new DateTimeZone('America/La_Paz'));
        self::assertArrayNotHasKey('observacion', $filas->first()->getAttributes());
        self::assertSame([], $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte())->hechos);
    }

    /** La atención nullable se conserva en lectura; el mapper no inventa un episodio de Domain. */
    public function test_sin_atencion_se_lee_dato_bruto_pero_no_se_fabrica_episodio(): void
    {
        $this->prepararIadl(['cod_atencion' => null]);
        $original = (new AdaptadorIadlExperimental)->recuperar($this->evaluacion(), ['SIM-IADL-1'], self::corte(),
            $this->conexionPruebas(), new DateTimeZone('America/La_Paz'))->first();
        self::assertNull($original->getRawOriginal('cod_atencion'));
        self::assertArrayHasKey('observacion', $original->getAttributes());
        $this->expectException(DomainException::class);
        $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** Una PK explícita de otro residente no se entrega al consumidor. */
    public function test_no_recupera_registro_de_otro_residente(): void
    {
        $this->prepararIadl();
        DB::table('residentes')->insert(['cod_residente' => 'SINTETICO-OTRO', 'nombres' => 'Otro sintético',
            'apellido_paterno' => 'Prueba', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        DB::table('controles_funcionales_iadl')->where('cod_control_iadl', 'SIM-IADL-1')->update(['cod_residente' => 'SINTETICO-OTRO']);
        $this->expectException(RuntimeException::class);
        $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** Una FK existente no permite mezclar la atención de otro residente. */
    public function test_rechaza_atencion_existente_ajena_al_residente(): void
    {
        $this->prepararIadl();
        DB::table('residentes')->insert(['cod_residente' => 'SINTETICO-OTRO', 'nombres' => 'Otro sintético',
            'apellido_paterno' => 'Prueba', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        DB::table('atenciones')->insert(['cod_atencion' => 'SIM-OTRA-AT', 'cod_residente' => 'SINTETICO-OTRO',
            'cod_personal' => 'SIM-PER', 'cod_area' => 'SIM-AREA', 'tipo_atencion' => 'PRUEBA_TECNICA',
            'fecha_hora' => '2026-10-17 12:00:00', 'estado' => 'VIGENTE']);
        DB::table('controles_funcionales_iadl')->where('cod_control_iadl', 'SIM-IADL-1')->update(['cod_atencion' => 'SIM-OTRA-AT']);
        $this->expectException(DomainException::class);
        $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** La FK física a la cuenta técnica rechaza una referencia inexistente. */
    public function test_fk_de_usuario_registrador_es_real(): void
    {
        $this->prepararIadl();
        $this->expectException(QueryException::class);
        DB::table('controles_funcionales_iadl')->where('cod_control_iadl', 'SIM-IADL-1')->update(['cod_usuario_registro' => 'SIM-INEXISTENTE']);
    }

    /** El modelo conserva la observación cuando se intenta sobrescribirla. */
    public function test_modelo_no_sobrescribe_historia(): void
    {
        $this->prepararIadl();
        $m = ControlFuncionalIadl::on($this->conexionPruebas())->findOrFail('SIM-IADL-1');
        try {
            $m->observacion = 'Intento de sobrescritura';
            $m->save();
            self::fail('Debió rechazar la sobrescritura.');
        } catch (LogicException $error) {
            self::assertStringContainsString('rectificación', $error->getMessage());
        }
        self::assertNull(DB::table('controles_funcionales_iadl')->where('cod_control_iadl', 'SIM-IADL-1')->value('observacion'));
    }

    /** El modelo impide el borrado ordinario y conserva físicamente el original. */
    public function test_modelo_no_borra_historia(): void
    {
        $this->prepararIadl();
        $m = ControlFuncionalIadl::on($this->conexionPruebas())->findOrFail('SIM-IADL-1');
        try {
            $m->delete();
            self::fail('Debió rechazar el borrado.');
        } catch (LogicException $error) {
            self::assertStringContainsString('historia', $error->getMessage());
        }
        self::assertSame(1, DB::table('controles_funcionales_iadl')->count());
    }

    /** @param array $cambios Variantes explícitas sintéticas. @param string $campo Campo directo. Inserta únicamente fixtures sobre la extensión aprobada. */
    private function prepararIadl(array $cambios = [], string $campo = 'observacion'): void
    {
        $this->prepararControl();
        $this->cambiarFuente('controles_funcionales_iadl', 'cod_control_iadl', 'SIM-IADL', 'SIM-IADL-1', $campo);
        DB::table('controles_funcionales_iadl')->insert(array_replace([
            'cod_control_iadl' => 'SIM-IADL-1', 'cod_residente' => 'SINTETICO-RES', 'cod_personal' => 'SIM-PER',
            'cod_usuario_registro' => 'SIM-USU', 'cod_atencion' => 'SIM-AT', 'cod_control_anterior' => null,
            'fecha_hora' => '2026-10-17 12:00:00', 'actividad' => 'RUTINAS', 'oportunidad' => 'SIN_OPORTUNIDAD',
            'desempeno' => 'NO_EVALUABLE', 'tipo_ayuda' => 'NO_APLICA', 'causa_ayuda' => 'NO_APLICA',
            'nivel_previo' => 'DESCONOCIDO', 'fuente' => 'EQUIPO_CUIDADOS', 'estado' => 'REGISTRADO',
            'observacion' => null, 'registrado_en' => '2026-10-17 12:05:00'], $cambios));
    }
}
