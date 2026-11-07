<?php

namespace App\Modulos\Admisiones\Presentacion\Livewire\Paginas;

use App\Models\Residente;
use App\Modulos\Admisiones\Aplicacion\Acciones\RegistrarEgresoResidente;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class EgresoResidente extends Component
{
    #[Locked]
    public Residente $residente;

    #[Locked]
    public ?string $codAdmision = null;

    #[Locked]
    public ?string $codOcupacion = null;

    public bool $modalAbierto = false;

    public string $motivo = '';

    public function abrir(RegistrarEgresoResidente $egreso): void
    {
        $egreso->autorizar($this->residente, auth()->user());
        $this->resetValidation();
        $this->reset('motivo', 'codAdmision', 'codOcupacion');
        $admisiones = $this->residente->admisiones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->limit(2)->get();
        if ($admisiones->count() !== 1) {
            throw ValidationException::withMessages(['egreso' => 'El expediente debe tener una única admisión activa para registrar el egreso.']);
        }
        $this->codAdmision = $admisiones->sole()->getKey();
        $this->codOcupacion = $this->residente->ocupacionActiva()->first()?->getKey();
        $this->modalAbierto = true;
    }

    public function confirmar(RegistrarEgresoResidente $egreso): void
    {
        abort_unless($this->modalAbierto && $this->codAdmision, 403);
        $egreso->ejecutar($this->residente, auth()->user(), $this->motivo, $this->codAdmision, $this->codOcupacion);
        session()->flash('egreso_registrado', 'Egreso registrado. La admisión quedó cerrada.');
        $this->redirectRoute('admin.administracion.residentes.show', $this->residente->getKey());
    }

    public function cerrar(): void
    {
        $this->reset('modalAbierto', 'motivo', 'codAdmision', 'codOcupacion');
        $this->resetValidation();
    }

    public function render()
    {
        return view('admisiones::livewire.admisiones.egreso-residente');
    }
}
