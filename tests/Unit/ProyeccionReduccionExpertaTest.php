<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\ProyeccionReduccionExperta as P;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProyeccionReduccionExpertaTest extends TestCase
{
    private function originales(): array
    {
        $t = array_fill_keys(P::TABLAS, []);
        $t[P::HECHOS] = [['cod_hecho_instantanea' => 'H1', 'cod_evaluacion_experta' => 'E1', 'valor_tipo' => 'BOOL',
            'valor_logico' => false, 'valor_texto' => '', 'valor_entero' => 0, 'valor_real_ieee754' => '8000000000000000',
            'fecha_hecho_utc' => '2026-10-20 10:00:00.123456', 'fecha_hecho_offset' => -14400, 'cod_evidencia_evaluacion' => null],
            ['cod_hecho_instantanea' => 'H2', 'cod_evaluacion_experta' => 'E1', 'valor_tipo' => 'NULL', 'cod_evidencia_evaluacion' => 'EV1']];
        $t[P::COGNITIVAS] = [array_replace(array_fill_keys(array_keys(P::COG), null), ['cod_evidencia_cognitiva_instantanea' => 'COG1',
            'cod_hecho_instantanea' => 'H1', 'dominio' => 'COG-MEM', 'hallazgo' => 'DIFICULTAD', 'justificacion' => 'Caso sintético',
            'orden' => 0, 'referencia_norma' => 'Ingeniería'])];
        $t[P::IADL] = [array_replace(array_fill_keys(array_keys(P::IAD), null), ['cod_evidencia_iadl_instantanea' => 'I1',
            'cod_hecho_instantanea' => 'H1', 'cod_funcionalidad_instantanea' => 'F1', 'actividad_id_dominio' => 'SIM-IADL',
            'estado' => 'INDEPENDIENTE', 'oportunidad' => 'REAL_Y_ADECUADA', 'atribucion_cognitiva_simulada' => false, 'orden' => 0])];
        $t[P::FUNCIONALIDAD] = [['cod_funcionalidad_instantanea' => 'F1', 'cod_evaluacion_experta' => 'E1',
            'independencia_relativa' => 'CONSERVADA', 'revisor_sintetico_id' => null, 'justificacion' => 'Funcionalidad original']];
        $t[P::CLASIFICACIONES] = [['cod_clasificacion_dcl_instantanea' => 'D1', 'cod_funcionalidad_instantanea' => 'F1',
            'cod_evaluacion_experta' => 'E1', 'patron_candidato' => null, 'justificacion' => 'Abstención original', 'huella_clasificacion' => str_repeat('a', 64)]];
        $t[P::SINTETICOS] = [['cod_soporte_sintetico' => 'S1', 'cod_evaluacion_criterio' => 'C1', 'cod_hecho_instantanea' => 'H1', 'orden' => 1]];
        $t[P::VINCULOS] = [['cod_evidencia_criterio' => 'V1', 'cod_evaluacion_criterio' => 'C1', 'cod_evidencia_evaluacion' => 'EV1',
            'rol_en_criterio' => 'SOPORTE_EXPERIMENTAL', 'estado_participacion' => 'PRUEBA_TECNICA', 'justificacion' => null,
            'fecha_hora_vinculacion' => '2026-10-20 10:00:00', 'orden_conservacion' => 0]];

        return $t;
    }

    public function test_restaura_todas_las_filas_y_ambos_bloques_del_mismo_hecho_sin_perder_tipos(): void
    {
        $p = new P;
        $origen = $this->originales();
        $d = $p->proyectar($origen);
        self::assertCount(3, $d);
        self::assertSame('COG1', $d[P::HECHOS][0]['cod_evidencia_cognitiva_instantanea']);
        self::assertSame('I1', $d[P::HECHOS][0]['cod_evidencia_iadl_instantanea']);
        self::assertFalse($d[P::HECHOS][0]['iadl_atribucion_cognitiva_simulada']);
        self::assertNull($d[P::VINCULOS][1]['cod_evidencia_criterio']);
        self::assertNull($d[P::VINCULOS][1]['fecha_hora_vinculacion']);
        self::assertSame('S1', $d[P::VINCULOS][1]['cod_soporte_sintetico']);
        self::assertSame($p->canonizar($origen), $p->canonizar($p->restaurar($d)));
    }

    public function test_hechos_sin_caracterizacion_y_tablas_vacias_no_crean_resultados_ficticios(): void
    {
        $p = new P;
        $origen = array_fill_keys(P::TABLAS, []);
        self::assertSame($p->canonizar($origen), $p->canonizar($p->restaurar($p->proyectar($origen))));
        $origen[P::HECHOS] = $this->originales()[P::HECHOS];
        $d = $p->proyectar($origen);
        self::assertNull($d[P::HECHOS][0]['cod_evidencia_cognitiva_instantanea']);
        self::assertNull($d[P::HECHOS][0]['cod_evidencia_iadl_instantanea']);
        self::assertSame($p->canonizar($origen), $p->canonizar($p->restaurar($d)));
    }

    public static function conflictos(): array
    {
        return array_map(static fn ($c) => [$c], ['cognitiva_duplicada', 'iadl_duplicada', 'iadl_ajena', 'f_sin_c', 'c_sin_f',
            'c_ajena', 'identidad_duplicada', 'columna_inesperada', 'colision', 'soporte_sin_h', 'vinculo_sin_evidencia']);
    }

    #[DataProvider('conflictos')]
    public function test_bloquea_incoherencia_sin_reparar_o_descartar_filas(string $conflicto): void
    {
        $t = $this->originales();
        match ($conflicto) {
            'cognitiva_duplicada' => $t[P::COGNITIVAS][] = array_replace($t[P::COGNITIVAS][0], ['cod_evidencia_cognitiva_instantanea' => 'COG2']),
            'iadl_duplicada' => $t[P::IADL][] = array_replace($t[P::IADL][0], ['cod_evidencia_iadl_instantanea' => 'I2']),
            'iadl_ajena' => $t[P::HECHOS][0]['cod_evaluacion_experta'] = 'E2',
            'f_sin_c' => $t[P::CLASIFICACIONES] = [],
            'c_sin_f' => $t[P::FUNCIONALIDAD] = [],
            'c_ajena' => $t[P::CLASIFICACIONES][0]['cod_evaluacion_experta'] = 'E2',
            'identidad_duplicada' => $t[P::SINTETICOS][] = $t[P::SINTETICOS][0],
            'columna_inesperada' => $t[P::COGNITIVAS][0]['extra'] = 'No se pierde silenciosamente',
            'colision' => $t[P::HECHOS][0]['cognitiva_orden'] = 3,
            'soporte_sin_h' => $t[P::SINTETICOS][0]['cod_hecho_instantanea'] = 'H-INEXISTENTE',
            'vinculo_sin_evidencia' => $t[P::VINCULOS][0]['cod_evidencia_evaluacion'] = null,
        };
        $this->expectException(DomainException::class);
        (new P)->proyectar($t);
    }
}
