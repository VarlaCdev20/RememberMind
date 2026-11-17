<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Schema;

/** Instala en la base desechable el conjunto de extensiones laborales vigente. */
final class PrepararHistoriaLaboral
{
    public static function instalar(): void
    {
        if (Schema::hasTable('historial_laboral_personal')) {
            return;
        }
        if (! Schema::hasTable('movimientos_laborales_personal')) {
            (require database_path('propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php'))->up();
        }
        if (! Schema::hasTable('eventos_asignacion_personal')) {
            (require database_path('propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->up();
        }
        if (! Schema::hasColumn('eventos_asignacion_personal', 'cod_usuario_afectado_aviso')) {
            (require database_path('propuestas/gerencia/2026_10_27_000003_campos_aviso_evento_asignacion.php'))->up();
        }
        (require database_path('propuestas/gerencia/2026_11_16_000004_consolidacion_historia_laboral.php'))->up();
    }
}
