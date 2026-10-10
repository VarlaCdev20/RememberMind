<?php

namespace App\Console\Commands;

use App\Backend\Modulos\SistemaExperto\Acciones\CargarPaqueteInvestigadoCOGMEM;
use App\Models\User;
use Illuminate\Console\Command;

final class CargarPaqueteInvestigadoCOGMEMCommand extends Command
{
    protected $signature = 'sistema-experto:cargar-paquete-memoria {autor : Código de la cuenta autorizada}';

    protected $description = 'Registra la propuesta investigada COG-MEM sin activar inferencia clínica';

    public function handle(CargarPaqueteInvestigadoCOGMEM $carga): int
    {
        $resultado = $carga->ejecutar(User::query()->findOrFail($this->argument('autor')));
        $this->info(json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}
