<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Presentacion\Soporte\ExploracionClinicaVisible;
use Tests\TestCase;

final class ExploracionClinicaVisibleTest extends TestCase
{
    private function perfil(): array
    {
        return ['petersen_institucional' => ['patron' => 'PET-01', 'estado' => 'PATRON', 'impedimentos' => [],
            'fecha_corte' => '2026-10-22T10:00:00-04:00', 'dominios' => ['COG-MEM' => ['estado' => 'A', 'motivos' => [],
                'valoraciones' => [['estado' => 'A', 'justificacion' => 'Justificación original sintética',
                    'origen' => ['fecha' => '2026-10-21T10:00:00-04:00', 'registro' => 'PRIVADO'], 'soportes' => []]]]],
            'requisitos' => ['C01' => ['estado' => 'S', 'valoraciones' => [], 'motivos' => []]]],
            'capturas_originales' => ['capturas' => [['cod_aplicacion' => 'AP-TEST', 'nombre_instrumento' => 'Captura sintética',
                'fecha_hora' => '2026-10-20T10:00:00-04:00', 'autor' => 'Profesional sintético', 'estado_original' => 'COMPLETA',
                'version_instrumento' => 'V1', 'motivos' => [], 'documento_original' => ['estado' => 'VIGENTE',
                    'fecha_hora' => '2026-10-19T10:00:00-04:00', 'ruta_archivo' => '/privado', 'hash_archivo' => 'SECRETO'],
                'procedencia' => ['estado_documento' => 'VERIFICADO']]]]];
    }

    public function test_distingue_resultado_actual_historia_y_documento_sin_filtrar_traza_o_ruta(): void
    {
        $perfil = $this->perfil();
        $dto = (new ExploracionClinicaVisible)->presentar($perfil, null, ['titulo' => 'Historia distinta', 'resumen' => 'Lectura de otra fecha'],
            ['evaluacion' => ['fecha' => '2026-10-10T10:00:00-04:00']], false, true);
        self::assertSame('Posible DCL amnésico de dominio único', $dto['resultado']['titulo']);
        self::assertSame('Cálculo actual · sin guardar', $dto['resultado']['origen']);
        self::assertSame('Historia distinta', $dto['historia']['titulo']);
        self::assertSame('Alteración sustentada', $dto['areas'][0]['estado']);
        self::assertSame('Original verificado', $dto['fuentes'][0]['documento']['verificacion']);
        self::assertStringContainsString('no acredita participación', $dto['fuentes'][0]['uso']);
        foreach (['SECRETO', '/privado', 'PRIVADO'] as $token) {
            self::assertStringNotContainsString($token, json_encode($dto, JSON_THROW_ON_ERROR));
        }
        self::assertSame($this->perfil(), $perfil);
        self::assertTrue($dto['entrenamiento']);
    }

    public function test_enfermeria_no_recibe_una_inferencia_actual_ni_requisitos_derivados(): void
    {
        $dto = (new ExploracionClinicaVisible)->presentar($this->perfil(), null, null, null, true, false);
        self::assertNull($dto['resultado']);
        self::assertNull($dto['areas'][0]['codigo']);
        self::assertNull($dto['requisitos'][0]['codigo']);
        self::assertSame([], $dto['areas'][0]['valoraciones']);
        self::assertTrue($dto['solo_lectura']);
    }

    public function test_ausencia_no_se_convierte_en_normalidad_y_conserva_cinco_areas(): void
    {
        $dto = (new ExploracionClinicaVisible)->presentar(null, null, null, null, false, false);
        self::assertCount(5, $dto['areas']);
        self::assertCount(5, $dto['requisitos']);
        self::assertSame([], $dto['fuentes']);
        self::assertNull($dto['resultado']);
        self::assertFalse($dto['historia']['disponible']);
        self::assertSame('Resultado no disponible', $dto['areas'][0]['estado']);
    }

    public function test_vinculos_de_documentos_y_aplicaciones_se_muestran_solo_cuando_son_explicitos(): void
    {
        $perfil = $this->perfil();
        $perfil['petersen_institucional']['dominios']['COG-MEM']['valoraciones'][0]['soportes'] = [
            ['tabla' => 'aplicaciones_instrumento', 'registro' => 'AP-TEST', 'tipo' => 'MEDICION'],
            ['tabla' => 'documentos_clinicos', 'registro' => 'DOC-1', 'tipo' => 'HISTORIA_PREVIA', 'fecha' => '2026-10-19',
                'datos_originales' => ['descripcion' => 'Informe externo autorizado']]];
        $perfil['petersen_institucional']['requisitos']['C02'] = ['estado' => 'S', 'derivado_de' => ['COG-MEM'], 'valoraciones' => []];
        $dto = (new ExploracionClinicaVisible)->presentar($perfil, null, null, null, false, false);
        self::assertCount(2, $dto['fuentes']);
        self::assertStringContainsString('Aprendizaje y memoria', $dto['fuentes'][0]['uso']);
        self::assertSame('Informe externo autorizado', $dto['fuentes'][1]['descripcion']);
        self::assertSame(['Aprendizaje y memoria'], $dto['requisitos'][1]['dependencias']);
    }
}
