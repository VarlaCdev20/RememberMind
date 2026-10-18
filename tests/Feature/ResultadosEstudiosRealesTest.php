<?php

namespace Tests\Feature;

use App\Models\DocumentoClinico;
use App\Models\Residente;
use App\Modulos\Clinica\Aplicacion\Consultas\ResultadosEstudiosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultadosEstudiosRealesTest extends TestCase
{
    use RefreshDatabase;

    public function test_documentos_con_titulos_en_mayusculas_o_minusculas_se_consultan_solo_para_su_residente(): void
    {
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_DOCUMENTOS', 'nombres' => 'Persona',
            'apellido_paterno' => 'Prueba', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO',
        ]);
        $otro = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_OTRO_DOCUMENTO', 'nombres' => 'Otra Persona',
            'apellido_paterno' => 'Prueba', 'fecha_nacimiento' => '1941-01-01', 'estado' => 'ADMITIDO',
        ]);
        $crear = static fn (Residente $persona, string $titulo) => DocumentoClinico::query()->create([
            'cod_residente' => $persona->getKey(), 'tipo_documento' => 'INFORME', 'titulo' => $titulo,
            'ruta_archivo' => 'pruebas/documento-sintetico.pdf', 'formato' => 'application/pdf',
            'hash_archivo' => hash('sha256', $titulo), 'fecha_hora' => '2026-10-18 10:00:00',
            'estado' => 'VIGENTE',
        ]);
        $documentos = collect(['Informe documental', 'INFORME documental', 'informe documental',
            'Estudio documental', 'LABORATORIO documental'])->map(fn ($titulo) => $crear($residente, $titulo));
        $ajeno = $crear($otro, 'INFORME de otro residente');
        $excluido = $crear($residente, 'Adjunto administrativo');

        $filas = app(ResultadosEstudiosService::class)->obtenerEstudios($residente);

        self::assertEqualsCanonicalizing($documentos->pluck('cod_documento_clinico')->all(), $filas->pluck('cod_doc')->all());
        self::assertNotContains($ajeno->getKey(), $filas->pluck('cod_doc')->all());
        self::assertNotContains($excluido->getKey(), $filas->pluck('cod_doc')->all());
        foreach ($filas as $fila) {
            self::assertSame('Documento adjunto', $fila['resultado_valor']);
            self::assertNull($fila['parametro_clave']);
            self::assertSame('No registrado', $fila['profesional_nombre']);
        }
    }

    public function test_residente_sin_estudios_no_recibe_resultados_profesionales_ni_tendencias_ficticias(): void
    {
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_SIN_ESTUDIOS',
            'nombres' => 'Paciente',
            'apellido_paterno' => 'Sin Estudios',
            'fecha_nacimiento' => '1940-01-01',
            'estado' => 'ADMITIDO',
        ]);

        $servicio = app(ResultadosEstudiosService::class);

        $this->assertCount(0, $servicio->obtenerEstudios($residente));
        $this->assertSame([], $servicio->obtenerDatosGrafico($residente, 'glucosa')['data']);
        $this->assertSame('No registrado', $servicio->obtenerDatosGrafico($residente, 'glucosa')['texto_rango']);
        $this->assertSame([], $servicio->obtenerRangosReferencia($residente, 'glucosa')['clasificaciones']);
    }
}
