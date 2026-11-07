<?php

namespace App\Modulos\Residentes\Presentacion\Livewire\Paginas;

use App\Models\Residente;
use App\Models\Visita;
use App\Modulos\Residentes\Aplicacion\Acciones\GestionarVisitaResidente;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class VisitasResidente extends Component
{
    use WithPagination;

    #[Locked]
    public Residente $residente;

    public string $contacto = '';

    public string $fecha = '';

    public string $motivo = '';

    #[Locked]
    public string $mensaje = '';

    public function programar(GestionarVisitaResidente $gestion): void
    {
        $gestion->autorizar(auth()->user(), $this->residente);
        $this->mensaje = '';
        $datos = $this->validate([
            'contacto' => ['required', 'string'], 'fecha' => ['required', 'date_format:Y-m-d\TH:i'],
            'motivo' => ['nullable', 'string', 'max:160'],
        ], ['contacto.required' => 'Selecciona un contacto.', 'fecha.required' => 'Indica la fecha y hora de la visita.']);
        $gestion->programar(auth()->user(), $this->residente, $datos);
        $this->reset('contacto', 'fecha', 'motivo');
        $this->resetPage();
        $this->mensaje = 'Visita programada.';
    }

    public function cambiar(string $codigo, string $accion, GestionarVisitaResidente $gestion): void
    {
        $this->mensaje = '';
        $this->resetValidation('visita');
        $gestion->cambiar(auth()->user(), $this->residente, $codigo, $accion);
        $this->mensaje = match ($accion) {
            'entrada' => 'Entrada registrada.', 'salida' => 'Salida registrada.', 'cancelar' => 'Visita cancelada.',
        };
    }

    public function render()
    {
        app(GestionarVisitaResidente::class)->autorizar(auth()->user(), $this->residente);

        return view('residentes::livewire.visitas-residente', [
            'contactos' => $this->residente->vinculosContacto()->where('estado', 'ACTIVO')->with('contacto')->get()->pluck('contacto')->unique('cod_contacto'),
            'visitas' => Visita::query()->where('cod_residente', $this->residente->getKey())->with('contacto')
                ->orderByRaw('COALESCE(fecha_hora_ingreso, fecha_hora_programada) DESC')->orderByDesc('cod_visita')->paginate(15),
        ]);
    }
}
