<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades;

final class EvaluacionGeriatricaAreaModal extends CapturaInstrumentalModal
{
    protected $listeners = ['evaluacion-geriatrica-area-abrir' => 'abrir'];

    public function abrir(array $data): void
    {
        $this->preparar($data['cod_residente'] ?? null);
    }
}
