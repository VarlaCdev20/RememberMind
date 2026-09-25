<?php

namespace App\Frontend\Livewire\Medico\Medicacion;

use App\Models\Residente;
use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class MedicacionAdultoModal extends Component
{
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?string $cod_med_adulto = null;
    public string $cod_residente = '';
    public string $nombre_medicamento = '';
    public string $dosis = '';
    public string $frecuencia = '';
    public bool $es_prn = false;
    public string $condicion_prn = '';
    public ?int $intervalo_horas = null;
    public string $via_administracion = '';
    public string $hora_programada = '';
    public string $fecha_inicio = '';
    public string $fecha_fin = '';
    public string $medico_indica = '';
    public string $observacion = '';
    public string $estado = 'ACTIVO';

    protected $listeners = ['abrirModalMedicacion'];

    public function rules(): array
    {
        return [
            'nombre_medicamento' => 'required|string|max:120',
            'dosis' => 'required|string|max:60',
            'frecuencia' => 'required|string|max:80',
            'via_administracion' => 'required|string|max:60',
            'hora_programada' => 'required_unless:es_prn,true|nullable|date_format:H:i',
            'es_prn' => 'boolean',
            'condicion_prn' => 'required_if:es_prn,true|nullable|string|min:5|max:500',
            'intervalo_horas' => 'nullable|integer|min:1|max:24',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'observacion' => 'nullable|string|max:1000',
            'estado' => ['required', Rule::in(['ACTIVO', 'ACTIVA', 'SUSPENDIDO', 'SUSPENDIDA', 'FINALIZADO', 'FINALIZADA'])],
            'cod_residente' => 'required|string|exists:residentes,cod_residente',
        ];
    }

    public function abrirModalMedicacion($cod_residente, $id_med = null): void
    {
        $this->autorizarGestionOrden($id_med ? 'prescripciones.editar' : 'prescripciones.crear');
        $this->resetValidation();
        $this->cod_residente = (string) $cod_residente;
        $this->isEditing = filled($id_med);
        $this->cod_med_adulto = $id_med ? (string) $id_med : null;
        if ($this->isEditing) {
            $this->cargarDatos();
        } else {
            $this->resetCampos();
            $this->cod_residente = (string) $cod_residente;
            $this->fecha_inicio = now()->format('Y-m-d');
        }
        $this->showModal = true;
    }

    public function cerrarModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->resetCampos();
    }

    public function cargarDatos(): void
    {
        $prescripcion = Prescripcion::query()->with(['medicamento', 'horarios', 'personal'])
            ->where('cod_residente', $this->cod_residente)->findOrFail($this->cod_med_adulto);
        $this->nombre_medicamento = $prescripcion->nombre_medicamento;
        $this->dosis = trim($prescripcion->dosis.' '.$prescripcion->unidad_dosis);
        $this->frecuencia = (string) $prescripcion->frecuencia;
        $this->es_prn = (bool) $prescripcion->segun_necesidad;
        $this->condicion_prn = (string) $prescripcion->indicacion;
        $this->via_administracion = (string) $prescripcion->via_administracion;
        $this->hora_programada = $prescripcion->hora_programada
            ? Carbon::parse($prescripcion->hora_programada)->format('H:i') : '';
        $this->fecha_inicio = $prescripcion->fecha_hora_prescripcion->format('Y-m-d');
        $this->fecha_fin = '';
        $this->medico_indica = (string) $prescripcion->medico_indica;
        $this->observacion = (string) $prescripcion->observacion;
        $this->estado = $prescripcion->estado;
        $this->cod_residente = $prescripcion->cod_residente;
        $this->cod_residente = $prescripcion->cod_residente;
    }

    public function resetCampos(): void
    {
        $this->cod_med_adulto = null;
        $this->nombre_medicamento = '';
        $this->dosis = '';
        $this->frecuencia = '';
        $this->es_prn = false;
        $this->condicion_prn = '';
        $this->intervalo_horas = null;
        $this->via_administracion = '';
        $this->hora_programada = '';
        $this->fecha_inicio = '';
        $this->fecha_fin = '';
        $this->medico_indica = '';
        $this->observacion = '';
        $this->estado = 'ACTIVO';
    }

    public function guardar(): void
    {
        $this->autorizarGestionOrden($this->isEditing ? 'prescripciones.editar' : 'prescripciones.crear');
        $this->validate();
        $personal = auth()->user()?->personal()->where('estado', 'ACTIVO')->first();
        if (! $personal) {
            throw ValidationException::withMessages(['medico_indica' => 'El usuario médico no tiene un registro de personal activo.']);
        }
        [$cantidad, $unidad] = $this->separarDosis($this->dosis);

        DB::transaction(function () use ($personal, $cantidad, $unidad): void {
            $medicamento = Medicamento::query()->firstOrCreate(
                ['nombre_generico' => Str::upper(trim($this->nombre_medicamento))],
                [
                    'cod_medicamento' => $this->codigo('MED'),
                    'via_predeterminada' => Str::upper($this->via_administracion),
                    'control_especial' => false,
                    'estado' => 'ACTIVO',
                ],
            );
            $observacion = trim($this->observacion);
            if (filled($this->fecha_fin)) {
                $observacion = trim($observacion."\nFecha de finalización prevista: {$this->fecha_fin}");
            }
            if ($this->es_prn && $this->intervalo_horas) {
                $observacion = trim($observacion."\nIntervalo PRN indicado: {$this->intervalo_horas} horas.");
            }

            if ($this->isEditing) {
                $prescripcion = Prescripcion::query()->where('cod_residente', $this->cod_residente)
                    ->findOrFail($this->cod_med_adulto);
            } else {
                $atencion = Atencion::query()->where('cod_residente', $this->cod_residente)
                    ->where('cod_personal', $personal->cod_personal)
                    ->whereNotIn('estado', ['ANULADA', 'CANCELADA'])
                    ->latest('fecha_hora')->first();
                if (! $atencion) {
                    throw ValidationException::withMessages([
                        'cod_residente' => 'Registre primero una atención médica; la prescripción V2 debe pertenecer a una atención.',
                    ]);
                }
                $prescripcion = new Prescripcion([
                    'cod_prescripcion' => $this->codigo('PRE'),
                    'cod_residente' => $this->cod_residente,
                    'cod_atencion' => $atencion->cod_atencion,
                    'cod_personal' => $personal->cod_personal,
                ]);
            }

            $prescripcion->fill([
                'cod_medicamento' => $medicamento->cod_medicamento,
                'dosis' => $cantidad,
                'unidad_dosis' => $unidad,
                'via_administracion' => Str::upper(trim($this->via_administracion)),
                'frecuencia' => trim($this->frecuencia),
                'indicacion' => $this->es_prn ? trim($this->condicion_prn) : null,
                'segun_necesidad' => $this->es_prn,
                'fecha_hora_prescripcion' => Carbon::parse($this->fecha_inicio.' '.now()->format('H:i:s')),
                'estado' => $this->estadoV2($this->estado),
                'observacion' => $observacion !== '' ? $observacion : null,
            ])->save();

            $horario = $prescripcion->horarios()->first();
            if ($this->es_prn) {
                $horario?->update(['estado' => 'INACTIVO']);
            } else {
                $datosHorario = [
                    'hora_programada' => $this->hora_programada,
                    'dosis_programada' => $cantidad,
                    'estado' => 'ACTIVO',
                ];
                $horario
                    ? $horario->update($datosHorario)
                    : HorarioPrescripcion::query()->create([
                        'cod_horario_prescripcion' => $this->codigo('HPR'),
                        'cod_prescripcion' => $prescripcion->cod_prescripcion,
                        ...$datosHorario,
                    ]);
            }
        });

        $mensaje = $this->isEditing ? 'Medicación actualizada correctamente.' : 'Medicación registrada correctamente.';
        $this->cerrarModal();
        $this->dispatch('medicacion-actualizada');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Éxito', 'text' => $mensaje]);
    }

    public function cambiarEstado($id, $estado): void
    {
        $this->autorizarGestionOrden('prescripciones.editar');
        validator(['estado' => $estado], ['estado' => ['required', Rule::in(['ACTIVO', 'ACTIVA', 'SUSPENDIDO', 'SUSPENDIDA', 'FINALIZADO', 'FINALIZADA'])]])->validate();
        Prescripcion::query()->where('cod_residente', $this->cod_residente)->findOrFail($id)
            ->update(['estado' => $this->estadoV2($estado)]);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Éxito', 'text' => "Estado cambiado a {$estado}."]);
    }

    public function render()
    {
        return view('livewire.medicacion.medicacion-adulto-modal', [
            'adultosDisponibles' => Residente::query()->whereIn('estado', ['ADMITIDO', 'ACTIVO'])->orderBy('nombres')->get(),
            'adultoSeleccionado' => ($this->cod_residente) ? Residente::query()->find($this->cod_residente) : null,
        ]);
    }

    private function autorizarGestionOrden(string $permiso): void
    {
        abort_unless(auth()->user()?->hasRole('MEDICO GENERAL/GERIATRA') && auth()->user()?->can($permiso), 403,
            'Las órdenes médicas solo pueden ser creadas o modificadas por personal médico autorizado.');
    }

    private function separarDosis(string $texto): array
    {
        preg_match('/(\d+(?:[.,]\d+)?)/', $texto, $coincidencia);
        $cantidad = isset($coincidencia[1]) ? (float) str_replace(',', '.', $coincidencia[1]) : 1;
        $unidad = trim(preg_replace('/^\s*'.preg_quote($coincidencia[1] ?? '', '/').'\s*/', '', $texto));

        return [$cantidad, Str::limit($unidad !== '' ? $unidad : 'DOSIS', 30, '')];
    }

    private function estadoV2(string $estado): string
    {
        return match ($estado) {
            'ACTIVO' => 'ACTIVA',
            'SUSPENDIDO' => 'SUSPENDIDA',
            'FINALIZADO' => 'FINALIZADA',
            default => $estado,
        };
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }
}
