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

    #[Locked]
    public ?string $instrumentoSolicitado = null;

    public function abrir(array $data): void
    {
        abort_unless(in_array($data['cod_area'] ?? null, ['ARE_COG', 'ARE_AFE', 'ARE_FUN', 'ARE_NUT', 'ARE_SOC'], true), 422);
        $area = $data['cod_area'];
        $codigo = $data['cod_instrumento'] ?? null;
        if ($codigo !== null) {
            abort_unless(is_string($codigo) && app(InstrumentosDelArea::class)->activos($area)->whereKey($codigo)
                ->whereHas('preguntas', fn ($q) => $q->where('estado', 'ACTIVA'))->exists(), 422, 'El instrumento no está habilitado para esta área.');
        }
        if (! $this->preparar($data['cod_residente'] ?? null)) {
            return;
        }
        $this->codArea = $area;
        $this->instrumentoSolicitado = $codigo;
        if ($codigo !== null && $this->cod_residente !== null) {
            $this->cod_instrumento = $codigo;
            $this->updatedCodInstrumento($codigo);
        }
    }

    public function updatedCodResidente(?string $value): void
    {
        parent::updatedCodResidente($value);
        if ($value !== null && $this->cod_residente === $value && ! $this->getErrorBag()->has('cod_residente') && $this->instrumentoSolicitado !== null) {
            $this->cod_instrumento = $this->instrumentoSolicitado;
            $this->updatedCodInstrumento($this->instrumentoSolicitado);
        }
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
