<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once __DIR__.'/support/RepartoValoraciones20261118.php';

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            (require __DIR__.'/2026_11_16_000001_consolidate_pedagogia.php')->down();
            (require __DIR__.'/2026_11_14_000008_consolidate_valoraciones_profesionales.php')->down();
            $this->psicopedagogia()->up();
            $this->funcionalNutricional()->up();
            foreach (['valoraciones_profesionales', 'valoraciones_psicopedagogicas'] as $tabla) {
                DB::statement('CREATE INDEX '.$tabla.'_residente_fecha_i ON '.$tabla.' (tipo_evento, cod_residente, fecha_hora)');
            }
            if (count(Schema::getColumnListing('valoraciones_profesionales')) !== 30
                || count(Schema::getColumnListing('valoraciones_psicopedagogicas')) !== 30) {
                throw new RuntimeException('El reparto debe conservar exactamente 30 atributos por tabla.');
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $this->funcionalNutricional()->down();
            $this->psicopedagogia()->down();
            (require __DIR__.'/2026_11_14_000008_consolidate_valoraciones_profesionales.php')->up();
            (require __DIR__.'/2026_11_16_000001_consolidate_pedagogia.php')->up();
        });
    }

    private function psicopedagogia(): RepartoValoraciones20261118
    {
        return new class extends RepartoValoraciones20261118
        {
            protected const DESTINO = 'valoraciones_psicopedagogicas';

            protected const ESPECIALIDADES = ['valoraciones_psicologicas', 'seguimientos_pedagogicos'];
        };
    }

    private function funcionalNutricional(): RepartoValoraciones20261118
    {
        return new class extends RepartoValoraciones20261118
        {
            protected const DESTINO = 'valoraciones_profesionales';

            protected const IDENTIDADES_TIPADAS = false;

            protected const ESPECIALIDADES = ['valoraciones_funcionales', 'valoraciones_nutricionales'];
        };
    }
};
