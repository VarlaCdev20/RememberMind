<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Atencion;
use App\Models\Documento;
use App\Models\DocumentoClinico;
use App\Models\IndicacionClinica;
use App\Models\NotaClinica;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\SeguimientoPedagogico;
use App\Models\SignoVital;
use App\Models\User;
use App\Models\ValoracionDolor;
use App\Models\ValoracionPetersenOriginal;
use App\Models\ValoracionPsicologica;
use App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConsolidacionDocumentosValoracionesTextosTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_cambio_de_esquema_intermedio_revierte_la_transicion_completa(): void
    {
        $contextoClinico = require database_path('migrations/2026_11_20_000006_separate_remaining_clinical_residents.php');
        $contextoClinico->down();
        $contexto = require database_path('migrations/2026_11_18_000004_derive_professional_assessment_resident.php');
        $contexto->down();
        $reparto = require database_path('migrations/2026_11_18_000003_partition_professional_assessments.php');
        $reparto->down();
        $migracion = require database_path('migrations/2026_11_16_000001_consolidate_pedagogia.php');
        $migracion->down();
        DB::statement('ALTER TABLE seguimientos_pedagogicos ADD COLUMN campo_no_aprobado varchar(10)');
        try {
            $migracion->up();
            $this->fail('Una estructura diferente debe impedir la transición.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('difiere del contrato', $error->getMessage());
        }
        $this->assertTrue(Schema::hasTable('valoraciones_profesionales'));
        $this->assertTrue(Schema::hasTable('seguimientos_pedagogicos'));
        $this->assertFalse(Schema::hasTable('valoraciones_profesionales_antes16'));
        $this->assertTrue(Schema::hasColumn('seguimientos_pedagogicos', 'campo_no_aprobado'));
        DB::statement('ALTER TABLE seguimientos_pedagogicos DROP COLUMN campo_no_aprobado');
        $migracion->up();
        $reparto->up();
        $contexto->up();
        $contextoClinico->up();
    }

    public function test_conserva_tipos_historia_referencias_y_reversion_poblada(): void
    {
        $usuario = User::factory()->create();
        $personal = Personal::create(['cod_usuario' => $usuario->getKey(), 'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintético', 'numero_documento' => 'SINTETICO-16', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $residente = Residente::factory()->create();
        $otro = Residente::factory()->create();
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        $base = ['cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey(),
            'fecha_hora' => '2026-11-15 09:00:00', 'estado' => 'VIGENTE'];
        $atencion = Atencion::create($base + ['cod_area' => $area->getKey(), 'tipo_atencion' => 'CONSULTA']);
        $peda = SeguimientoPedagogico::create($base + ['atencion' => 'CONSERVADA', 'cambio_desempeno' => false,
            'observacion' => 'Seguimiento sintético sin atención clínica']);
        $psicologia = ValoracionPsicologica::create($base + ['cod_atencion' => $atencion->getKey(), 'conclusion' => 'Contexto sintético']);
        $signo = SignoVital::create($base + ['temperatura' => 36.8]);
        $dolor = ValoracionDolor::create($base + ['intensidad' => 4, 'ubicacion' => 'BRAZO', 'frecuencia' => 'OCASIONAL']);
        $reevaluacion = ValoracionDolor::create(array_replace($base, ['fecha_hora' => '2026-11-15 10:00:00']) + [
            'cod_valoracion_origen' => $dolor->getKey(), 'intensidad' => 2, 'respuesta' => 'Respuesta sintética',
        ]);
        $admin = Documento::create(['tipo_documento' => 'IDENTIDAD', 'nombre' => 'Archivo sintético', 'ruta_archivo' => 'privado/identidad.pdf',
            'tipo_archivo' => 'application/pdf', 'hash_archivo' => str_repeat('a', 64), 'estado' => 'VIGENTE']);
        $clinico = DocumentoClinico::create($base + ['tipo_documento' => 'INFORME', 'titulo' => 'Informe sintético',
            'ruta_archivo' => 'privado/clinico.pdf', 'formato' => 'application/pdf', 'hash_archivo' => str_repeat('b', 64)]);
        RegistrosConsumo::insertar('documentos', [[
            'cod_documento' => 'DOC-IMPORTADO-16', 'tipo_documento' => 'IDENTIDAD', 'nombre' => 'Importación sintética',
            'ruta_archivo' => 'privado/importado.pdf', 'tipo_archivo' => 'application/pdf',
            'hash_archivo' => str_repeat('c', 64), 'estado' => 'VIGENTE',
        ]]);
        $this->assertNotNull(Documento::find('DOC-IMPORTADO-16'));
        $this->assertSame($clinico->getKey(), DocumentoClinico::query()->select('cod_documento_clinico', 'titulo')
            ->whereKey($clinico->getKey())->sole()->getKey());
        $nota = NotaClinica::create($base + ['cod_atencion' => $atencion->getKey(), 'tipo_nota' => 'EVOLUCION', 'contenido' => 'Nota sintética']);
        $indicacion = IndicacionClinica::create($base + ['cod_atencion' => $atencion->getKey(), 'tipo_indicacion' => 'CUIDADO',
            'descripcion' => 'Indicación sintética', 'prioridad' => 'NORMAL']);
        $correccion = NotaClinica::create($base + ['cod_atencion' => $atencion->getKey(), 'tipo_nota' => 'CORRECCION',
            'contenido' => 'Corrección sintética', 'cod_nota_anterior' => $nota->getKey(), 'motivo_correccion' => 'Rectificación sintética']);

        $this->assertNull(Documento::find($clinico->getKey()));
        $this->assertNull(DocumentoClinico::find($admin->getKey()));
        $this->assertNull(ValoracionDolor::find($signo->getKey()));
        $this->assertNull(ValoracionPsicologica::find($peda->getKey()));
        $this->assertNull(NotaClinica::find($indicacion->getKey()));
        $this->rechazar(fn () => DB::table('mediciones_clinicas')->where('cod_registro_medicion', $reevaluacion->getKey())
            ->update(['cod_valoracion_origen' => $signo->getKey()]));
        $this->rechazar(fn () => DB::table('residentes_mediciones_clinicas')->where('cod_registro_medicion', $reevaluacion->getKey())
            ->update(['cod_residente' => $otro->getKey()]));
        $this->rechazar(fn () => DB::table('valoraciones_psicopedagogicas')->where('cod_valoracion_profesional', $psicologia->getKey())
            ->update(['cod_atencion' => null]));
        $this->rechazar(fn () => DB::table('registros_textuales_clinicos')->where('cod_registro_textual', $correccion->getKey())
            ->update(['cod_nota_anterior' => $indicacion->getKey()]));
        $this->rechazar(fn () => DB::table('documentos')->where('cod_documento', $admin->getKey())
            ->update(['cod_documento_anterior' => $clinico->getKey()]));

        $original = ValoracionPetersenOriginal::create(array_diff_key($base, ['estado' => true]) + [
            'cod_usuario_registro' => $usuario->getKey(), 'solicitud_uuid' => (string) Str::uuid(), 'registrado_en' => now(),
            'tipo_registro' => 'REGISTRO', 'elemento' => 'C01', 'estado_original' => 'S', 'justificacion' => 'Contexto sintético',
        ]);
        $soporte = ['cod_soporte_petersen' => 'SVP-DOC-16', 'cod_valoracion_petersen' => $original->getKey(),
            'tipo_soporte' => 'HISTORIA_PREVIA', 'orden' => 0, 'cod_documento_clinico' => $clinico->getKey()];
        DB::table('soportes_valoracion_petersen')->insert($soporte);
        $this->rechazar(fn () => DB::table('soportes_valoracion_petersen')->where('cod_soporte_petersen', 'SVP-DOC-16')
            ->update(['cod_documento_clinico' => $admin->getKey()]));

        $contextoClinico = require database_path('migrations/2026_11_20_000006_separate_remaining_clinical_residents.php');
        $contextoClinico->down();
        $contextoCognitivo = require database_path('migrations/2026_11_20_000005_separate_optional_cognitive_residents.php');
        $contextoCognitivo->down();
        $contextoCuidados = require database_path('migrations/2026_11_20_000004_separate_optional_care_residents.php');
        $contextoCuidados->down();
        $contexto = require database_path('migrations/2026_11_18_000004_derive_professional_assessment_resident.php');
        $contexto->down();
        $reparto = require database_path('migrations/2026_11_18_000003_partition_professional_assessments.php');
        $reparto->down();
        $pases = require database_path('migrations/2026_11_17_000001_consolidate_pases_textos.php');
        $pases->down();
        $migraciones = array_map(static fn ($ruta) => require $ruta, glob(database_path('migrations/2026_11_16_*.php')));
        foreach (array_reverse($migraciones) as $migracion) {
            $migracion->down();
        }
        try {
            $this->assertDatabaseHas('seguimientos_pedagogicos', ['cod_seguimiento_pedagogico' => $peda->getKey(), 'cambio_desempeno' => false]);
            $this->assertDatabaseHas('documentos_clinicos', ['cod_documento_clinico' => $clinico->getKey(), 'titulo' => 'Informe sintético']);
            $this->assertDatabaseHas('valoraciones_dolor', ['cod_valoracion_dolor' => $reevaluacion->getKey(), 'cod_valoracion_origen' => $dolor->getKey()]);
            $this->assertDatabaseHas('notas_clinicas', ['cod_nota' => $correccion->getKey(), 'cod_nota_anterior' => $nota->getKey()]);
            $this->assertDatabaseHas('indicaciones_clinicas', ['cod_indicacion' => $indicacion->getKey(), 'prioridad' => 'NORMAL']);
        } finally {
            foreach ($migraciones as $migracion) {
                $migracion->up();
            }
        }
        $pases->up();
        $reparto->up();
        $contexto->up();
        $contextoCuidados->up();
        $contextoCognitivo->up();
        $contextoClinico->up();
        $this->assertSame($dolor->getKey(), $reevaluacion->fresh()->origen->getKey());
        $this->assertSame($reevaluacion->getKey(), $dolor->fresh()->reevaluaciones->sole()->getKey());
        $this->assertSame($nota->getKey(), $correccion->fresh()->anterior->getKey());
        $this->assertSame('Informe sintético', $clinico->fresh()->titulo);
        $this->assertDatabaseHas('soportes_valoracion_petersen', $soporte);
        $this->assertFalse(Schema::hasTable('documentos_clinicos'));
    }

    private function rechazar(callable $operacion): void
    {
        try {
            DB::transaction($operacion);
            $this->fail('Se aceptó una referencia o combinación de tipos inválida.');
        } catch (QueryException $error) {
            // P0001: las restricciones institucionales existentes usan RAISE EXCEPTION en PostgreSQL.
            $this->assertContains($error->getCode(), ['23000', '23503', '23514', 'P0001']);
        }
    }
}
