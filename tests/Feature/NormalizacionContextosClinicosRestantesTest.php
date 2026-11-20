<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas;
use App\Compartido\Infraestructura\Persistencia\ContextosResidente;
use App\Models\AntecedenteClinico;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\Derivacion;
use App\Models\Diagnostico;
use App\Models\Documento;
use App\Models\DocumentoClinico;
use App\Models\IndicacionClinica;
use App\Models\NotaClinica;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\SeguimientoPedagogico;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Models\ValoracionPsicologica;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

final class NormalizacionContextosClinicosRestantesTest extends TestCase
{
    use EsquemaSinTransaccion;

    private const TABLAS = ['derivaciones', 'documentos', 'registros_clinicos', 'registros_textuales_clinicos', 'valoraciones_psicopedagogicas', 'valoraciones_petersen_originales'];

    private function escenario(): array
    {
        $u = User::factory()->create();
        $p = Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Autor', 'apellido_paterno' => 'Sintético', 'numero_documento' => 'FN5-CLINICO', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $r = Residente::factory()->create();
        $otro = Residente::factory()->create();
        $area = Area::create(['nombre' => 'Atención sintética', 'estado' => 'ACTIVA']);
        $at = Atencion::create(['cod_residente' => $r->getKey(), 'cod_personal' => $p->getKey(), 'cod_area' => $area->getKey(), 'fecha_hora' => now(), 'tipo_atencion' => 'VALORACION', 'estado' => 'VIGENTE']);
        $base = ['cod_residente' => $r->getKey(), 'cod_personal' => $p->getKey(), 'fecha_hora' => now(), 'estado' => 'VIGENTE'];
        $documento = Documento::create(['tipo_documento' => 'IDENTIDAD', 'nombre' => 'Documento sintético', 'ruta_archivo' => 'privado/fn5.pdf', 'tipo_archivo' => 'application/pdf', 'hash_archivo' => str_repeat('a', 64), 'estado' => 'VIGENTE']);
        $juicio = ['cod_usuario_registro' => $u->getKey(), 'registrado_en' => now(), 'solicitud_uuid' => (string) Str::uuid(), 'tipo_registro' => 'REGISTRO', 'elemento' => 'C01', 'estado_original' => 'S', 'justificacion' => 'Juicio sintético original'];
        $petersen = ValoracionPetersenOriginal::create(array_diff_key($base, ['estado' => true]) + $juicio);
        $registros = [
            Derivacion::create(['cod_residente' => $r->getKey(), 'cod_area_solicitante' => $area->getKey(), 'cod_area_receptora' => $area->getKey(), 'cod_personal_solicitante' => $p->getKey(), 'motivo' => 'Solicitud sintética', 'fecha_hora' => now(), 'estado' => 'PENDIENTE']),
            Derivacion::create(['cod_residente' => $r->getKey(), 'cod_atencion' => $at->getKey(), 'cod_area_solicitante' => $area->getKey(), 'cod_area_receptora' => $area->getKey(), 'cod_personal_solicitante' => $p->getKey(), 'motivo' => 'Solicitud con atención', 'fecha_hora' => now(), 'estado' => 'PENDIENTE']),
            DocumentoClinico::create($base + ['tipo_documento' => 'INFORME', 'titulo' => 'Informe sintético', 'ruta_archivo' => 'privado/clinico.pdf', 'formato' => 'application/pdf', 'hash_archivo' => str_repeat('b', 64)]),
            DocumentoClinico::create($base + ['cod_atencion' => $at->getKey(), 'tipo_documento' => 'INFORME', 'titulo' => 'Informe con atención', 'ruta_archivo' => 'privado/atencion.pdf', 'formato' => 'application/pdf', 'hash_archivo' => str_repeat('c', 64)]),
            AntecedenteClinico::create(array_diff_key($base, ['fecha_hora' => true]) + ['tipo_antecedente' => 'PERSONAL', 'descripcion' => 'Historia sintética', 'fecha_referencia' => today()]),
            Diagnostico::create($base + ['cod_atencion' => $at->getKey(), 'nombre' => 'Contexto sintético']),
            NotaClinica::create($base + ['cod_atencion' => $at->getKey(), 'tipo_nota' => 'EVOLUCION', 'contenido' => 'Nota original sintética']),
            IndicacionClinica::create($base + ['cod_atencion' => $at->getKey(), 'tipo_indicacion' => 'CUIDADO', 'descripcion' => 'Indicación sintética', 'prioridad' => 'NORMAL']),
            SeguimientoPedagogico::create($base + ['observacion' => 'Seguimiento sintético']),
            ValoracionPsicologica::create($base + ['cod_atencion' => $at->getKey(), 'conclusion' => 'Valoración sintética']),
            $petersen,
            ValoracionPetersenOriginal::create(array_diff_key($base, ['estado' => true]) + array_replace($juicio, ['solicitud_uuid' => (string) Str::uuid(), 'tipo_registro' => 'RECTIFICACION', 'cod_valoracion_anterior' => $petersen->getKey(), 'cod_atencion' => $at->getKey(), 'justificacion' => 'Rectificación sintética conservada'])),
        ];

        return compact('u', 'p', 'r', 'otro', 'at', 'base', 'documento', 'registros');
    }

    public function test_documento_administrativo_sin_residente_conserva_null_al_crear_y_actualizar(): void
    {
        $d = $this->escenario();
        $doc = $d['documento'];
        self::assertNull($doc->fresh()->cod_residente);
        self::assertSame(0, DB::table('residentes_documentos')->where('cod_documento', $doc->getKey())->count());
        $doc->update(['estado' => 'VALIDADO', 'fecha_validacion' => now(), 'cod_usuario_validacion' => $d['u']->getKey()]);
        self::assertNull($doc->fresh()->cod_residente);
        self::assertSame('VALIDADO', $doc->fresh()->estado);
        try {
            $doc->update(['cod_residente' => $d['r']->getKey()]);
            self::fail('No se reasigna el sujeto original.');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('cod_residente', $e->errors());
        }
        self::assertSame(0, DB::table('residentes_documentos')->where('cod_documento', $doc->getKey())->count());
    }

    public function test_los_tipos_preservan_sujeto_y_cadenas_y_reversion_poblada_sin_perdidas(): void
    {
        $d = $this->escenario();
        foreach ($d['registros'] as $registro) {
            self::assertSame($d['r']->getKey(), $registro->fresh()->cod_residente);
            self::assertSame($d['r']->getKey(), $registro::whereKey($registro->getKey())->sole()->getRawOriginal('cod_residente'));
            self::assertFalse($registro::whereKey($registro->getKey())->where('cod_residente', $d['otro']->getKey())->exists());
        }
        $antes = [];
        $columnas = [];
        foreach (self::TABLAS as $tabla) {
            [$pk] = ContextosResidente::CONTRATOS[$tabla];
            $columnas[$tabla] = array_values(array_diff(Schema::getColumnListing($tabla), ['cod_contexto_directo']));
            $antes[$tabla] = ColumnasRelacionadas::consulta(DB::connection(), $tabla)->select([...$columnas[$tabla], 'cod_residente'])->orderBy($pk)->get()->all();
        }
        $m = require database_path('migrations/2026_11_20_000006_separate_remaining_clinical_residents.php');
        $m->down();
        foreach (self::TABLAS as $tabla) {
            [$pk,$directa] = ContextosResidente::CONTRATOS[$tabla];
            self::assertFalse(Schema::hasTable($directa));
            self::assertEquals($antes[$tabla], DB::table($tabla)->select([...$columnas[$tabla], 'cod_residente'])->orderBy($pk)->get()->all());
        }
        $m->up();
        foreach (self::TABLAS as $tabla) {
            [$pk] = ContextosResidente::CONTRATOS[$tabla];
            self::assertEquals($antes[$tabla], ColumnasRelacionadas::consulta(DB::connection(), $tabla)->select([...$columnas[$tabla], 'cod_residente'])->orderBy($pk)->get()->all());
        }
        self::assertNull($d['documento']->fresh()->cod_residente);
        self::assertSame(2, ValoracionPetersenOriginal::count());
    }

    public function test_sql_no_cruza_residente_o_elemento_de_una_rectificacion_petersen(): void
    {
        $d = $this->escenario();
        $original = $d['registros'][10];
        foreach (['residente', 'elemento'] as $caso) {
            $codigo = 'VPO_CRUCE_'.$caso;
            try {
                DB::transaction(function () use ($d, $original, $codigo, $caso): void {
                    $datos = array_diff_key($original->fresh()->getAttributes(), array_fill_keys(['cod_contexto_directo', 'cod_residente', 'cod_valoracion_petersen'], true));
                    $datos = array_replace($datos, ['cod_valoracion_petersen' => $codigo, 'solicitud_uuid' => (string) Str::uuid(), 'tipo_registro' => 'RECTIFICACION', 'cod_valoracion_anterior' => $original->getKey()]);
                    if ($caso === 'elemento') {
                        $datos['elemento'] = 'C04';
                    }
                    ContextosResidente::insertar(DB::connection(), 'valoraciones_petersen_originales', [$datos + ['cod_residente' => ($caso === 'residente' ? $d['otro'] : $d['r'])->getKey()]]);
                });
                self::fail('La cadena debe conservar residente y elemento.');
            } catch (QueryException $e) {
                self::assertStringContainsString('contrato original', $e->getMessage());
            }
            self::assertSame(0, DB::table('valoraciones_petersen_originales')->where('cod_valoracion_petersen', $codigo)->count());
            self::assertSame(0, DB::table('residentes_valoraciones_petersen')->where('cod_valoracion_petersen',$codigo)->count());
        }
        self::assertSame(2,ValoracionPetersenOriginal::count());
    }
}
