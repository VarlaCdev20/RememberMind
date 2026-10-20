<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades;

use App\Modulos\Valoraciones\Aplicacion\Consultas\InstrumentosDelArea;
use Livewire\Attributes\Locked;

final class EvaluacionGeriatricaAreaModal extends CapturaInstrumentalModal
{
    protected $listeners = ['evaluacion-geriatrica-area-abrir' => 'abrir'];

    #[Locked]
    public ?string $codArea = null;

    public function abrir(array $data): void
    {
        abort_unless(in_array($data['cod_area'] ?? null, ['ARE_COG', 'ARE_AFE', 'ARE_FUN', 'ARE_NUT', 'ARE_SOC'], true), 422);
        $this->codArea = $data['cod_area'];
        $this->preparar($data['cod_residente'] ?? null);
    }

    public function updatedCodInstrumento(?string $value): void
    {
        if ($value !== null) {
            $this->comprobarInstrumentoDelArea($value);
        }
        parent::updatedCodInstrumento($value);
    }

    public function revisar(): void
    {
        if ($this->cod_instrumento !== null) {
            $this->comprobarInstrumentoDelArea($this->cod_instrumento);
        }
        parent::revisar();
    }

    public function guardar(): void
    {
        if ($this->cod_instrumento !== null) {
            $this->comprobarInstrumentoDelArea($this->cod_instrumento);
        }
        parent::guardar();
    }

    private function comprobarInstrumentoDelArea(?string $codigo): void
    {
        abort_unless($codigo !== null && $this->codArea !== null
            && app(InstrumentosDelArea::class)->activos($this->codArea)->whereKey($codigo)->exists(), 422,
            'El instrumento no está habilitado para esta área.');
    }

    public function render()
    {
        $vista = parent::render();
        if ($this->codArea !== null) {
            $codigos = app(InstrumentosDelArea::class)->activos($this->codArea)->pluck('cod_instrumento')->all();
            $vista->with('instrumentos', $vista->getData()['instrumentos']->whereIn('cod_instrumento', $codigos));
        }

        return $vista;
    }
}
