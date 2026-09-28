<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LegacyEliminadoV2Test extends TestCase
{
    public function test_todos_los_modelos_persistentes_apuntan_a_tablas_del_esquema_vigente(): void
    {
        $migraciones = collect(File::files(database_path('migrations')))
            ->map(fn ($archivo) => File::get($archivo->getPathname()))
            ->implode("\n");

        foreach (File::files(app_path('Models')) as $archivo) {
            $contenido = File::get($archivo->getPathname());
            if (! preg_match('/protected\s+\$table\s*=\s*[\'\"]([^\'\"]+)[\'\"]/', $contenido, $coincidencia)) {
                continue;
            }

            $this->assertStringContainsString(
                "Schema::create('{$coincidencia[1]}'",
                $migraciones,
                "{$archivo->getFilename()} apunta a una tabla inexistente: {$coincidencia[1]}",
            );
        }
    }

    public function test_el_codigo_ejecutable_no_consulta_tablas_legacy_retiradas(): void
    {
        $tablasLegacy = [
            'acciones_alerta', 'actividades_adulto', 'areas_geriatricas', 'asignaciones_plazas_enfermeria',
            'atenciones_adulto', 'documentos_adulto_mayor', 'documentos_preadmision', 'documentos_usuarios',
            'estado_adulto', 'evaluaciones_geriatricas', 'familiar_adulto', 'horarios_personal_admin',
            'horarios_personal_salud', 'historial_estado_adulto', 'instrumentos_geriatricos',
            'notas_evolucion_medica', 'obs_adulto', 'tipo_atenciones_adulto', 'tipo_actividades_adulto',
            'tipos_documentos_usuario', 'valoracion_enfermeria_admision', 'valoracion_funcional_adulto',
        ];

        $archivos = collect(File::allFiles(app_path()))->filter(fn ($archivo) => $archivo->getExtension() === 'php');

        foreach ($archivos as $archivo) {
            $contenido = File::get($archivo->getPathname());
            foreach ($tablasLegacy as $tabla) {
                $this->assertDoesNotMatchRegularExpression(
                    '/(?:DB::table\(|exists:|unique:|protected\s+\$table\s*=\s*)[\'\"]?'.preg_quote($tabla, '/').'\b/',
                    $contenido,
                    "{$archivo->getRelativePathname()} todavía consulta {$tabla}",
                );
            }
        }
    }

    public function test_los_modelos_legacy_retirados_no_vuelven_al_dominio(): void
    {
        $modelos = [
            'AccionAlerta', 'ActividadAdulto', 'AreaGeriatrica', 'AsignacionPlazaEnfermeria', 'AtencionAdulto',
            'DocumentoAdultoMayor', 'DocumentoPreadmision', 'DocumentoUsuario', 'EstadoAdulto',
            'EvaluacionGeriatrica', 'Familiar', 'FamiliarAdulto', 'HistorialEstadoAdulto',
            'TipoDocumentoUsuario', 'ValoracionEnfermeriaAdmision', 'ValoracionFuncionalAdulto',
        ];

        foreach ($modelos as $modelo) {
            $this->assertFileDoesNotExist(app_path("Models/{$modelo}.php"));
        }
    }
}
