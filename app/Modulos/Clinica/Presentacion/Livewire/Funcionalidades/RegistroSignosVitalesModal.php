<?php

namespace App\Modulos\Clinica\Presentacion\Livewire\Funcionalidades;

use App\Models\Residente;
use App\Models\SignoVital;
use App\Modulos\Clinica\Aplicacion\Acciones\SignosVitalesService;
use App\Modulos\Clinica\Aplicacion\Consultas\AnalisisSignosPanel;
use Livewire\Attributes\Locked;
use App\Modulos\Clinica\Aplicacion\Validacion\ValidacionSignosVitalesService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class RegistroSignosVitalesModal extends Component
{
    public bool $mostrar = false;

    #[Locked]
    public ?string $cod_residente = null;

    public $adulto = null;

    public string $fecha = '';

    public string $hora = '';

    public ?string $pa_sistolica = null;

    public ?string $pa_diastolica = null;

    public ?string $fc = null;

    public ?string $fr = null;

    public ?string $temperatura = null;

    public ?string $saturacion = null;

    public ?string $glucosa = null;

    public ?string $peso = null;

    public ?string $talla = null;

    public ?string $imc = null;

    public ?string $dolor = null;

    public string $observacion = '';

    public bool $confirmar_presion_atipica = false;

    protected $listeners = ['abrir-signos-vitales-medico' => 'abrir'];

    public function mount(): void
    {
        $this->fecha = today()->toDateString();
        $this->hora = now()->format('H:i');
    }

    public function abrir(string $cod_residente): void
    {
        abort_unless(Auth::check(), 401);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($cod_residente, Auth::user());

        $this->resetForm();

        $this->cod_residente = $cod_residente;
        $this->adulto = Residente::findOrFail($cod_residente);

        $this->mostrar = true;
    }

    public function cerrar(): void
    {
        $this->mostrar = false;
        $this->resetForm();
    }

    public function updatedPeso(): void
    {
        $this->calcularImc();
    }

    public function updatedTalla(): void
    {
        $this->calcularImc();
    }

    private function calcularImc(): void
    {
        $p = $this->peso !== null && $this->peso !== '' ? (float) $this->peso : null;
        $t = $this->talla !== null && $this->talla !== '' ? (float) $this->talla : null;
        $this->imc = ValidacionSignosVitalesService::calcularImc($p, $t);
    }

    public function guardar(): void
    {
        try {
            app(SignosVitalesService::class)->registrar($this->cod_residente, [
                'fecha' => $this->fecha, 'hora' => $this->hora,
                'presion_sistolica' => $this->pa_sistolica, 'presion_diastolica' => $this->pa_diastolica,
                'frecuencia_cardiaca' => $this->fc, 'frecuencia_respiratoria' => $this->fr,
                'temperatura' => $this->temperatura, 'saturacion' => $this->saturacion, 'glucosa' => $this->glucosa,
                'peso' => $this->peso, 'talla' => $this->talla, 'dolor' => $this->dolor,
                'valor_atipico_confirmado' => $this->confirmar_presion_atipica, 'observacion' => $this->observacion,
            ], Auth::user());

            $this->cerrar();
            $this->dispatch('signos-guardados');
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Signos vitales registrados',
                'text' => 'Control guardado correctamente.',
            ]);
        } catch (ValidationException $e) {
            $campos = [
                'presion_arterial' => 'pa_sistolica',
                'presion_sistolica' => 'pa_sistolica',
                'presion_diastolica' => 'pa_sistolica',
                'frecuencia_cardiaca' => 'fc',
                'frecuencia_respiratoria' => 'fr',
            ];
            foreach ($e->errors() as $campo => $mensajes) {
                foreach ($mensajes as $mensaje) {
                    $this->addError($campos[$campo] ?? $campo, $mensaje);
                }
            }

            return;
        } catch (\Throwable $e) {
            if ($e instanceof HttpExceptionInterface) {
                throw $e;
            }
            report($e);
            $this->addError('general', 'No se pudieron registrar los signos vitales. Inténtelo nuevamente.');
        }
    }

    private function resetForm(): void
    {
        $this->cod_residente = null;

        $this->adulto = null;
        $this->fecha = today()->toDateString();
        $this->hora = now()->format('H:i');
        $this->pa_sistolica = null;
        $this->pa_diastolica = null;
        $this->fc = null;
        $this->fr = null;
        $this->temperatura = null;
        $this->saturacion = null;
        $this->glucosa = null;
        $this->peso = null;
        $this->talla = null;
        $this->imc = null;
        $this->dolor = null;
        $this->observacion = '';
        $this->confirmar_presion_atipica = false;
        $this->resetValidation();
    }

    public function render()
    {
        $avisosClinicos = [];
        if ($this->mostrar && $this->cod_residente) {
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->cod_residente, Auth::user());
            $fechaValida = ! Validator::make(
                ['fecha' => $this->fecha, 'hora' => $this->hora],
                ['fecha' => 'required|date_format:Y-m-d', 'hora' => 'required|date_format:H:i']
            )->fails();
            $avisosClinicos = app(AnalisisSignosPanel::class)->detectarAlertas([
                'presion_sistolica' => $this->pa_sistolica, 'presion_diastolica' => $this->pa_diastolica,
                'frecuencia_cardiaca' => $this->fc, 'frecuencia_respiratoria' => $this->fr,
                'temperatura' => $this->temperatura, 'saturacion' => $this->saturacion, 'glucosa' => $this->glucosa,
                'fecha_hora' => $fechaValida ? $this->fecha.' '.$this->hora : null,
            ], $fechaValida ? $this->cod_residente : null);
        }
        return view('livewire.clinica.registro-signos-vitales-modal', compact('avisosClinicos'));
    }
}
