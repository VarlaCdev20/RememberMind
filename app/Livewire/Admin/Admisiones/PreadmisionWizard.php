<?php

namespace App\Livewire\Admin\Admisiones;

use App\Models\DocumentoPreadmision;
use App\Models\Preadmision;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class PreadmisionWizard extends Component
{
    use WithFileUploads;

    public int $paso = 1;
    public int $totalPasos = 6;

    public string $nombres = '';
    public string $ap_paterno = '';
    public string $ap_materno = '';
    public string $ci = '';
    public string $expedicion_ci = '';
    public string $fecha_nac = '';
    public string $genero = '';
    public string $estado_civil = 'NO ESPECIFICADO';
    public string $telefono = '';
    public string $celular = '';

    public string $departamento_residencia = '';
    public string $ciudad_municipio = '';
    public string $zona = '';
    public string $calle = '';
    public string $direccion_referencia = '';

    public string $familiar_nombres = '';
    public string $familiar_ap_paterno = '';
    public string $familiar_ap_materno = '';
    public string $familiar_ci = '';
    public string $familiar_parentesco = '';
    public string $familiar_celular = '';
    public string $familiar_correo = '';
    public string $familiar_direccion = '';

    public string $motivo_ingreso = '';
    public string $procedencia_ingreso = '';
    public string $tipo_ingreso = 'REGULAR';
    public string $permanencia = 'PERMANENTE';
    public string $prioridad = 'MEDIA';
    public string $descripcion_caso = '';

    public $doc_ci_adulto;
    public $doc_ci_familiar;
    public $doc_solicitud_ingreso;

    public string $enfermero_id = '';

    public function siguiente(): void
    {
        $this->validarPasoActual();

        if ($this->paso < $this->totalPasos) {
            $this->paso++;
        }
    }

    public function anterior(): void
    {
        if ($this->paso > 1) {
            $this->paso--;
        }
    }

    public function confirmarPreadmision()
    {
        $this->validate($this->rules());

        try {
            DB::beginTransaction();

            $preadmision = Preadmision::create([
                'estado' => 'PREADMISION_ASIGNADA',
                'fecha_solicitud' => today()->toDateString(),
                'fecha_asignacion' => now(),
                'nombres' => $this->normalizar($this->nombres),
                'ap_paterno' => $this->normalizar($this->ap_paterno),
                'ap_materno' => $this->normalizar($this->ap_materno),
                'ci' => trim($this->ci),
                'expedicion_ci' => $this->expedicion_ci,
                'fecha_nac' => $this->fecha_nac,
                'genero' => $this->genero,
                'estado_civil' => $this->estado_civil,
                'telefono' => trim($this->telefono) ?: null,
                'celular' => trim($this->celular) ?: null,
                'departamento_residencia' => $this->departamento_residencia,
                'ciudad_municipio' => $this->normalizar($this->ciudad_municipio),
                'zona' => $this->normalizar($this->zona),
                'calle' => $this->normalizar($this->calle),
                'direccion_referencia' => $this->normalizar($this->direccion_referencia),
                'familiar_nombres' => $this->normalizar($this->familiar_nombres),
                'familiar_ap_paterno' => $this->normalizar($this->familiar_ap_paterno),
                'familiar_ap_materno' => $this->normalizar($this->familiar_ap_materno),
                'familiar_ci' => trim($this->familiar_ci) ?: null,
                'familiar_parentesco' => $this->familiar_parentesco,
                'familiar_celular' => trim($this->familiar_celular),
                'familiar_correo' => trim($this->familiar_correo) ?: null,
                'familiar_direccion' => $this->normalizar($this->familiar_direccion),
                'motivo_ingreso' => $this->motivo_ingreso,
                'procedencia_ingreso' => $this->procedencia_ingreso,
                'tipo_ingreso' => $this->tipo_ingreso,
                'permanencia' => $this->permanencia,
                'prioridad' => $this->prioridad,
                'descripcion_caso' => $this->normalizar($this->descripcion_caso),
                'documentos_iniciales_completos' => true,
                'documentos_institucionales_generados' => true,
                'enfermero_asignado' => $this->enfermero_id,
                'creado_por' => auth()->user()?->cod_usu,
                'observaciones' => 'Preadmision registrada y asignada para valoracion inicial.',
            ]);

            $this->guardarDocumentoSubido($preadmision, 'CI_ADULTO', 'CI del adulto mayor', $this->doc_ci_adulto, true);
            $this->guardarDocumentoSubido($preadmision, 'CI_FAMILIAR', 'CI del familiar responsable', $this->doc_ci_familiar, true);
            $this->guardarDocumentoSubido($preadmision, 'SOLICITUD_INGRESO', 'Solicitud inicial de ingreso', $this->doc_solicitud_ingreso, true);

            foreach ($this->documentosInstitucionales() as $tipo => $nombre) {
                DocumentoPreadmision::create([
                    'cod_pre' => $preadmision->cod_pre,
                    'tipo_documento' => $tipo,
                    'nombre_documento' => $nombre,
                    'es_institucional' => true,
                    'obligatorio' => true,
                    'estado' => 'GENERADO',
                    'observaciones' => 'Generado por el sistema al confirmar la preadmision.',
                ]);
            }

            activity('Admisiones')
                ->causedBy(auth()->user())
                ->performedOn($preadmision)
                ->log("Preadmision {$preadmision->cod_pre} asignada para valoracion inicial.");

            DB::commit();

            $this->dispatch('swal', [
                'title' => 'Preadmision asignada',
                'text' => 'El caso quedo preparado para valoracion inicial. No se creo adulto mayor activo.',
                'icon' => 'success',
            ]);

            return redirect()->route('admin.admisiones.preadmisiones');
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('swal', [
                'title' => 'Error',
                'text' => 'No se pudo registrar la preadmision: ' . $e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.admisiones.preadmision-wizard', [
            'enfermeros' => User::role('ENFERMEROS')
                ->where('estado', 'ACTIVO')
                ->orderBy('ap_paterno')
                ->get(['cod_usu', 'nombres', 'ap_paterno', 'ap_materno']),
            'documentosInstitucionales' => $this->documentosInstitucionales(),
        ])->layout('layouts.sistema');
    }

    private function validarPasoActual(): void
    {
        $this->validate(match ($this->paso) {
            1 => [
                'nombres' => ['required', 'string', 'min:2', 'max:100'],
                'ap_paterno' => ['required', 'string', 'min:2', 'max:80'],
                'ci' => ['required', 'string', 'max:20', 'unique:preadmisiones,ci'],
                'expedicion_ci' => ['required', 'string', 'max:10'],
                'fecha_nac' => ['required', 'date', 'before_or_equal:' . now()->subYears(60)->format('Y-m-d')],
                'genero' => ['required', 'string'],
            ],
            2 => [
                'departamento_residencia' => ['required', 'string'],
                'ciudad_municipio' => ['required', 'string', 'min:2'],
                'zona' => ['required', 'string', 'min:2'],
                'calle' => ['required', 'string', 'min:2'],
            ],
            3 => [
                'familiar_nombres' => ['required', 'string', 'min:2'],
                'familiar_parentesco' => ['required', 'string'],
                'familiar_celular' => ['required', 'string', 'max:30'],
            ],
            4 => [
                'motivo_ingreso' => ['required', 'string'],
                'procedencia_ingreso' => ['required', 'string'],
                'tipo_ingreso' => ['required', 'string'],
                'permanencia' => ['required', 'string'],
                'prioridad' => ['required', 'string'],
            ],
            5 => [
                'doc_ci_adulto' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                'doc_ci_familiar' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
                'doc_solicitud_ingreso' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            ],
            6 => [
                'enfermero_id' => ['required', 'exists:users,cod_usu'],
            ],
            default => [],
        }, $this->mensajesValidacion());
    }

    private function mensajesValidacion(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'string' => 'El formato ingresado no es válido.',
            'min' => 'Debe contener al menos :min caracteres.',
            'max' => 'No debe exceder los :max caracteres.',
            'unique' => 'Este valor ya se encuentra registrado.',
            'date' => 'Debe ser una fecha válida.',
            'before_or_equal' => 'La fecha no cumple con el requisito (mayor a 60 años).',
            'file' => 'Debe seleccionar un archivo válido.',
            'mimes' => 'El archivo debe ser de tipo: :values.',
            'exists' => 'El registro seleccionado no es válido.',
        ];
    }

    private function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'min:2', 'max:100'],
            'ap_paterno' => ['required', 'string', 'min:2', 'max:80'],
            'ci' => ['required', 'string', 'max:20', 'unique:preadmisiones,ci'],
            'expedicion_ci' => ['required', 'string', 'max:10'],
            'fecha_nac' => ['required', 'date', 'before_or_equal:' . now()->subYears(60)->format('Y-m-d')],
            'genero' => ['required', 'string'],
            'departamento_residencia' => ['required', 'string'],
            'ciudad_municipio' => ['required', 'string'],
            'zona' => ['required', 'string'],
            'calle' => ['required', 'string'],
            'familiar_nombres' => ['required', 'string', 'min:2'],
            'familiar_parentesco' => ['required', 'string'],
            'familiar_celular' => ['required', 'string', 'max:30'],
            'motivo_ingreso' => ['required', 'string'],
            'procedencia_ingreso' => ['required', 'string'],
            'tipo_ingreso' => ['required', 'string'],
            'permanencia' => ['required', 'string'],
            'prioridad' => ['required', 'string'],
            'doc_ci_adulto' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'doc_ci_familiar' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'doc_solicitud_ingreso' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'enfermero_id' => ['required', 'exists:users,cod_usu'],
        ];
    }

    private function guardarDocumentoSubido(Preadmision $preadmision, string $tipo, string $nombre, $file, bool $obligatorio): void
    {
        $path = $file->store('preadmisiones/' . $preadmision->cod_pre, 'public');

        DocumentoPreadmision::create([
            'cod_pre' => $preadmision->cod_pre,
            'tipo_documento' => $tipo,
            'nombre_documento' => $nombre,
            'archivo_path' => $path,
            'nombre_original' => $file->getClientOriginalName(),
            'es_institucional' => false,
            'obligatorio' => $obligatorio,
            'estado' => 'RECIBIDO',
        ]);
    }

    private function documentosInstitucionales(): array
    {
        return [
            'FICHA_PREADMISION' => 'Ficha institucional de preadmision',
            'AUTORIZACION_VALORACION' => 'Autorizacion de valoracion inicial',
            'CONSENTIMIENTO_DATOS' => 'Consentimiento de tratamiento de datos',
            'ACTA_RECEPCION_DOCUMENTOS' => 'Acta de recepcion de documentos',
        ];
    }

    private function normalizar(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtoupper($value, 'UTF-8');
    }
}
