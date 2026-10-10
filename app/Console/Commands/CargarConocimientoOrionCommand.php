<?php

namespace App\Console\Commands;

use App\Backend\Modulos\SistemaExperto\Acciones\CargarConocimientoOrion;
use App\Models\User;
use Illuminate\Console\Command;

final class CargarConocimientoOrionCommand extends Command
{
    protected $signature = 'sistema-experto:cargar-orion {autor : Código de la cuenta autorizada}';

    protected $description = 'Carga la instantánea documental de O.R.I.O.N. sin activar inferencia clínica';

    public function handle(CargarConocimientoOrion $carga): int
    {
        $resultado = $carga->ejecutar(User::query()->findOrFail($this->argument('autor')));
        $this->info(json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}
