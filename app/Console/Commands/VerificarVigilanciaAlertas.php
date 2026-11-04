<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/** Diagnóstico sin cuenta suplantada, datos clínicos ni efectos de detección. */
final class VerificarVigilanciaAlertas extends Command
{
    protected $signature = 'alertas:verificar-vigilancia';

    protected $description = 'Verifica la habilitación de la vigilancia sin sesión y declara el bloqueo de autoría.';

    public function handle(): int
    {
        $this->error('Vigilancia no habilitada: falta aprobar la autoría institucional de eventos automáticos sin sesión.');
        $this->line('No se crearon alertas ni se utilizó la cuenta de un profesional. La detección manual autorizada permanece disponible.');
        Log::warning('Vigilancia de alertas pendiente de autoría de ejecución institucional.', [
            'proceso' => 'vigilancia-asistencial',
            'estado' => 'BLOQUEADO_AUTORIA',
        ]);

        return self::FAILURE;
    }
}
