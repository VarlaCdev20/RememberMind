<?php

namespace App\Modulos\Institucion\Infraestructura\Jobs;

use App\Modulos\Institucion\Aplicacion\Acciones\GestionarAvisosPlanificacion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PublicarAvisoPlanificacion implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public readonly string $evento)
    {
        $this->onQueue('planificacion');
    }

    public function handle(GestionarAvisosPlanificacion $avisos): void
    {
        $avisos->publicar($this->evento);
    }
}
