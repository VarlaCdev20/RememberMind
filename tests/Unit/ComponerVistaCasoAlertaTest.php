<?php

namespace Tests\Unit;

use App\Modulos\Alertas\Presentacion\Soporte\ComponerVistaCasoAlerta;
use PHPUnit\Framework\TestCase;

final class ComponerVistaCasoAlertaTest extends TestCase
{
    public function test_las_nueve_tareas_tienen_tipos_distintos_y_no_fabrican_registros_al_faltar_caso(): void
    {
        $compositor = new ComponerVistaCasoAlerta;
        $tipos = [];
        foreach (['turno', 'clinico', 'psicologia', 'nutricion', 'funcional', 'pedagogia', 'coordinacion', 'supervision', 'auditoria'] as $foco) {
            $vista = $compositor->componer(null, $foco);
            $tipos[] = $vista['tipo'];
            self::assertFalse($vista['conCaso']);
            foreach (['origen', 'eventos', 'secciones', 'especialidades'] as $lista) {
                self::assertSame([], $vista[$lista]);
            }
            foreach (['caso', 'responsable', 'estado', 'contexto', 'autor', 'atencion'] as $dato) {
                self::assertNull($vista[$dato]);
            }
        }
        self::assertCount(9, array_unique($tipos));
    }

    public function test_no_reinterpreta_estados_historicos_ni_presenta_pasado_como_pendiente(): void
    {
        $compositor = new ComponerVistaCasoAlerta;
        foreach (['ABIERTA' => 0, 'PENDIENTE' => 0, 'EN_ATENCION' => 1, 'ATENDIDA' => 2, 'CERRADA' => 3, 'ANULADA' => 3, 'ACTIVO' => 4, 'DESCONOCIDO' => 4] as $estado => $columna) {
            self::assertSame($columna, $compositor->componer(['estadoCodigo' => $estado], 'coordinacion')['columna']);
        }
    }

    public function test_los_bloques_conservan_su_seccion_y_solo_los_datos_autorizados_recibidos(): void
    {
        $seccion = ['id' => 'campo-final', 'grupo' => 'profesional', 'seccion' => 2,
            'detalles' => [['etiqueta' => 'Necesidad de supervisión', 'valor' => 'No']]];
        $traza = ['estadoCodigo' => 'ABIERTA', 'nodos' => [
            ['id' => 'alerta', 'grupo' => 'caso'],
            ['id' => 'origen-operativo', 'grupo' => 'origen', 'texto' => 'Hecho documentado'],
            ['id' => 'contexto-profesional', 'grupo' => 'profesional', 'texto' => 'Contexto diferente de la fuente'],
            $seccion,
        ]];
        $original = $traza;
        $vista = (new ComponerVistaCasoAlerta)->componer($traza, 'funcional');
        self::assertTrue($vista['conCaso']);
        self::assertArrayNotHasKey(0, $vista['secciones']);
        self::assertSame($seccion, $vista['secciones'][2]);
        self::assertSame('origen-operativo', $vista['origen'][0]['id']);
        self::assertSame('contexto-profesional', $vista['contexto']['id']);
        self::assertNull($vista['autor']);
        self::assertSame($original, $traza);
    }
}
