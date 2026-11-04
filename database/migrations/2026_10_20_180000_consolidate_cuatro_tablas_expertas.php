<?php

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\TransicionReduccionConservacion;
use Illuminate\Database\Migrations\Migration;

/** Cuatro consolidaciones y 29 columnas aprobadas por la propietaria el 20/10/2026. */
return new class extends Migration
{
    public function up(): void
    {
        (new TransicionReduccionConservacion)->subir();
    }

    public function down(): void
    {
        (new TransicionReduccionConservacion)->bajar();
    }
};
