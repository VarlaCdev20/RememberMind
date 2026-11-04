<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarEjecucionesExperimentales;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\RecolectorContextoPerfilExperimental as Recolector;
use Database\Seeders\RolesAndPermissionsSeeder;
use DateTimeImmutable;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MotorExperto\aplicacion\casos_uso\EjecutarPerfilExperimental;
use MotorExperto\investigacion\dcl\CasoSinteticoCincoDominios as Caso;
use MotorExperto\investigacion\dcl\IncorporadorContextoExperimental;
use RuntimeException;
use Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios as F;
use Tests\Feature\SistemaExperto\Infrastructure\Repositories\RepositoriosTestCase;

final class AdquisicionPerfilExperimentalTest extends RepositoriosTestCase
{
    private function prepararFuentes(): array
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $autor = User::factory()->create();
        $autor->assignRole('SUPERADMINISTRADOR');
        $control = F::evidencia(['memoria_reciente' => null, 'repite_preguntas' => false]);
        $comun = ['cod_residente' => 'SINTETICO-RES', 'cod_personal' => 'SIM-PER', 'cod_atencion' => 'SIM-AT',
            'fecha_hora' => '2026-10-16 12:00:00'];
        DB::table('valoraciones_psicologicas')->insert($comun + ['cod_valoracion_psicologica' => 'SIM-PSI', 'estado' => 'VIGENTE',
            'comunicacion' => 'Relato artificial sin interpretación automática.', 'impresion_cognitiva' => 'Texto ficticio; no es un criterio computable.']);
        DB::table('valoraciones_funcionales')->insert($comun + ['cod_valoracion_funcional' => 'SIM-FUNC', 'estado' => 'ACTIVA']);
        DB::table('diagnosticos')->insert($comun + ['cod_diagnostico' => 'SIM-DIAG', 'nombre' => 'Contexto médico ficticio', 'estado' => 'VIGENTE']);
        DB::table('antecedentes_clinicos')->insert(['cod_antecedente' => 'SIM-ANT', 'cod_residente' => 'SINTETICO-RES',
            'cod_personal' => 'SIM-PER', 'tipo_antecedente' => 'PRUEBA_TECNICA', 'descripcion' => 'Antecedente ficticio.',
            'fecha_referencia' => '2026-10-15', 'estado' => 'VIGENTE']);
        DB::table('registros_sueno')->insert(['cod_registro_sueno' => 'SIM-SUE', 'cod_residente' => 'SINTETICO-RES',
            'cod_personal' => 'SIM-PER', 'fecha' => '2026-10-16', 'horas_sueno' => '0.00', 'despertares' => 0,
            'insomnio' => false, 'somnolencia_diurna' => null, 'estado' => 'VIGENTE']);
        DB::table('actividades')->insert(['cod_actividad' => 'SIM-ACT', 'cod_area' => 'SIM-AREA', 'cod_personal' => 'SIM-PER',
            'tipo' => 'PRUEBA_TECNICA', 'nombre' => 'Actividad ficticia', 'fecha_hora' => '2026-10-16 12:00:00', 'estado' => 'VIGENTE']);
        DB::table('seguimientos_pedagogicos')->insert(['cod_seguimiento_pedagogico' => 'SIM-SEG', 'cod_residente' => 'SINTETICO-RES',
            'cod_personal' => 'SIM-PER', 'cod_actividad' => 'SIM-ACT', 'fecha_hora' => '2026-10-16 12:00:00', 'estado' => 'VIGENTE', 'atencion' => '0']);
        DB::table('controles_funcionales_iadl')->insert($comun + ['cod_control_iadl' => 'SIM-IADL',
            'cod_usuario_registro' => $autor->getKey(), 'actividad' => 'TELEFONO', 'oportunidad' => 'NO_OBSERVADA',
            'desempeno' => 'NO_DETERMINADO', 'tipo_ayuda' => 'NO_DETERMINADA', 'causa_ayuda' => 'NO_DETERMINADA',
            'nivel_previo' => 'DESCONOCIDO', 'fuente' => 'OBSERVACION', 'estado' => 'REGISTRADO', 'registrado_en' => '2026-10-17 12:00:00']);
        $selecciones = ['controles_cognitivos' => [$control->getKey()], 'valoraciones_psicologicas' => ['SIM-PSI'],
            'valoraciones_funcionales' => ['SIM-FUNC'], 'diagnosticos' => ['SIM-DIAG'], 'antecedentes_clinicos' => ['SIM-ANT'],
            'registros_sueno' => ['SIM-SUE'], 'seguimientos_pedagogicos' => ['SIM-SEG'], 'controles_funcionales_iadl' => ['SIM-IADL']];

        return [$autor, $selecciones];
    }

    public function test_models_hasta_perfil_preservan_contexto_null_autor_original_y_replay_sin_nlp(): void
    {
        [$autor, $selecciones] = $this->prepararFuentes();
        DB::table('personal')->where('cod_personal', 'SIM-PER')->update(['estado' => 'INACTIVO']);
        $fuentes = (new Recolector)->recoger($autor, 'SINTETICO-RES', 'SIM-INTEGRAL-01',
            new DateTimeImmutable('2026-10-17T23:59:59-04:00'), $selecciones);
        self::assertCount(8, $fuentes);
        self::assertNull($fuentes[0]['valores']['memoria_reciente']['original']);
        self::assertFalse($fuentes[0]['valores']['repite_preguntas']['tipado']);
        self::assertSame('SIM-PER', $fuentes[0]['autor_original']);
        $caso = Caso::crear();
        $caso['contexto']['residente_ficticio'] = 'SINTETICO-RES';
        foreach ($caso['capturas']['aplicaciones_instrumento'] as &$aplicacion) {
            $aplicacion['cod_residente'] = 'SINTETICO-RES';
        }
        unset($aplicacion);
        $caso['fuentesComplementarias'] = $fuentes;
        $r = (new EjecutarPerfilExperimental)->ejecutar(...$caso);
        self::assertCount(8, $r['base_contextual']['fuentes']);
        self::assertNotEmpty($r['base_contextual']['relaciones']);
        self::assertSame([null], array_values(array_unique(array_column($r['base_contextual']['hechos'], 'valor_semantico'), SORT_REGULAR)));
        self::assertNull($r['petersen']['patron_candidato']);
        self::assertSame($r, (new EjecutarPerfilExperimental)->ejecutar(...$r['instantanea_entrada']));
        Storage::fake('local');
        $archivo = app(GestionarEjecucionesExperimentales::class);
        $conservado = $archivo->ejecutar($autor, $caso);
        DB::table('controles_cognitivos')->where('cod_control_cognitivo', $selecciones['controles_cognitivos'][0])
            ->update(['repite_preguntas' => true]);
        $historico = $archivo->consultar($autor, 'SINTETICO-RES', 'SIM-INTEGRAL-01');
        self::assertSame($conservado, $historico);
        self::assertFalse($historico['resultado']['base_contextual']['fuentes'][0]['valores']['repite_preguntas']['tipado']);
        self::assertSame($r, $archivo->reproducir($autor, 'SINTETICO-RES', 'SIM-INTEGRAL-01'));
        self::assertSame(1, DB::table('evaluaciones_expertas')->count()); // Solo la cabecera previa de F::evidencia.
        $deduplicada = (new IncorporadorContextoExperimental)->incorporar($caso['contexto'], [...$fuentes, $fuentes[0]]);
        self::assertCount(8, $deduplicada['fuentes']);
        self::assertCount(1, $deduplicada['duplicados_no_recontados']);
    }

    public function test_futuro_y_fecha_ausente_no_exponen_valores_ni_fabrican_un_instante(): void
    {
        [$autor, $selecciones] = $this->prepararFuentes();
        DB::table('valoraciones_psicologicas')->where('cod_valoracion_psicologica', 'SIM-PSI')->update(['fecha_hora' => '2026-10-18 12:00:00']);
        DB::table('antecedentes_clinicos')->where('cod_antecedente', 'SIM-ANT')->update(['fecha_referencia' => null]);
        $fuentes = (new Recolector)->recoger($autor, 'SINTETICO-RES', 'SIM-INTEGRAL-01',
            new DateTimeImmutable('2026-10-17T23:59:59-04:00'), array_intersect_key($selecciones, ['valoraciones_psicologicas' => true, 'antecedentes_clinicos' => true]));
        self::assertSame([], $fuentes[0]['valores']);
        self::assertContains('FUENTE_POSTERIOR_AL_CORTE', $fuentes[0]['motivos']);
        self::assertNull($fuentes[1]['fecha_original']);
        self::assertContains('FECHA_ORIGINAL_NO_DISPONIBLE', $fuentes[1]['motivos']);
    }

    public function test_autor_sin_permiso_no_puede_extraer_y_origen_ajeno_no_se_reemplaza(): void
    {
        [$autor, $selecciones] = $this->prepararFuentes();
        $autor->syncRoles(['MEDICO GENERAL/GERIATRA']);
        $this->expectException(AuthorizationException::class);
        (new Recolector)->recoger($autor, 'SINTETICO-RES', 'SIM-INTEGRAL-01', new DateTimeImmutable('2026-10-17'), $selecciones);
    }

    public function test_fuente_inexistente_y_tabla_no_permitida_fallan_expresamente(): void
    {
        [$autor] = $this->prepararFuentes();
        try {
            (new Recolector)->recoger($autor, 'SINTETICO-RES', 'SIM-INTEGRAL-01', new DateTimeImmutable('2026-10-17'), ['usuarios' => ['SIM-USU']]);
            self::fail('Se aceptó otra tabla.');
        } catch (DomainException) {
            self::assertSame(1, DB::table('evaluaciones_expertas')->count());
        }
        $this->expectException(RuntimeException::class);
        (new Recolector)->recoger($autor, 'SINTETICO-OTRO', 'SIM-INTEGRAL-01', new DateTimeImmutable('2026-10-17'), ['valoraciones_psicologicas' => ['SIM-PSI']]);
    }

    public function test_incorporador_rechaza_fecha_inventada_futuro_valores_no_finitos_y_metadatos_omitidos(): void
    {
        [$autor, $selecciones] = $this->prepararFuentes();
        $fuente = (new Recolector)->recoger($autor, 'SINTETICO-RES', 'SIM-INTEGRAL-01',
            new DateTimeImmutable('2026-10-17T23:59:59-04:00'), ['controles_cognitivos' => $selecciones['controles_cognitivos']])[0];
        foreach (['invalida', 'futura', 'ausente', 'no_finito', 'metadato'] as $mutacion) {
            $f = $fuente;
            match ($mutacion) {
                'invalida' => $f['fecha_original'] = '2026-02-30 12:00:00',
                'futura' => $f['fecha_original'] = '2026-10-18 12:00:00',
                'ausente' => $f['fecha_original'] = null,
                'no_finito' => $f['valores']['memoria_reciente'] = ['original' => INF, 'tipado' => INF, 'tipo_original' => 'float'],
                'metadato' => $f['precision_fecha'] = 'DESCONOCIDA',
            };
            try {
                (new IncorporadorContextoExperimental)->incorporar(['residente_ficticio' => 'SINTETICO-RES',
                    'evaluacion' => 'SIM-INTEGRAL-01', 'fecha_corte' => '2026-10-17'], [$f]);
                self::fail('Se admitió una captura contextual inválida: '.$mutacion);
            } catch (DomainException) {
                self::assertSame(1, DB::table('evaluaciones_expertas')->count());
            }
        }
    }
}
