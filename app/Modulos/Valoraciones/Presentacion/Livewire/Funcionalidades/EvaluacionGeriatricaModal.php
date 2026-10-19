<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades;

final class EvaluacionGeriatricaModal extends CapturaInstrumentalModal
{
    protected $listeners = ['evaluacion-geriatrica-abrir' => 'abrir'];

    public function abrir(string $cod_residente): void
    {
        $this->preparar($cod_residente);
    }
}
