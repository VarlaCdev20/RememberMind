<?php

declare(strict_types=1);

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\TransicionBloquesExpertos;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new TransicionBloquesExpertos)->subir();
    }

    public function down(): void
    {
        (new TransicionBloquesExpertos)->bajar();
    }
};
