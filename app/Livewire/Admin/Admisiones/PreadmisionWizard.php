<?php

namespace App\Livewire\Admin\Admisiones;

use App\Models\AdultoMayor;
use App\Models\EstadoAdulto;
use App\Models\Familiar;
use App\Models\DocumentoAdultoMayor;
use App\Models\User;
use App\Models\AsignacionTurnoAdulto;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PreadmisionWizard extends Component
{
    use WithFileUploads;

    public $paso = 1;
    public $totalPasos = 5;

    // Paso 1: Datos Obligatorios
    public $nombres, $ap_paterno, $ap_materno, $ci, $expedicion_ci, $fecha_nac, $genero;
    
    // Paso 2: Familiar Responsable
    public $contacto_emergencia_nombre, $contacto_emergencia_parentesco, $contacto_emergencia_celular, $contacto_emergencia_direccion;

    // Paso 3: Documentos
    public $doc_ci_adulto;
    public $doc_ci_familiar;
    public $doc_croquis;

    // Paso 4: Enfermero
    public $enfermero_id;

    protected $rules = [
        // Paso 1
        'nombres' => 'required|string|min:2',
        'ap_paterno' => 'required|string|min:2',
        'ci' => 'required|numeric',
        'expedicion_ci' => 'required|string',
        'fecha_nac' => 'required|date|before:today',
        'genero' => 'required|string',
        // Paso 2
        'contacto_emergencia_nombre' => 'required|string|min:3',
        'contacto_emergencia_parentesco' => 'required|string',
        'contacto_emergencia_celular' => 'required|numeric',
        // Paso 3
        'doc_ci_adulto' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png',
        'doc_ci_familiar' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png',
        // Paso 4
        'enfermero_id' => 'required|exists:users,id',
    ];

    public function mount()
    {
        // ...
    }

    public function siguiente()
    {
        if ($this->paso == 1) {
            $this->validate([
                'nombres' => 'required|string|min:2',
                'ap_paterno' => 'required|string|min:2',
                'ci' => 'required|numeric',
                'expedicion_ci' => 'required|string',
                'fecha_nac' => 'required|date|before:today',
                'genero' => 'required|string',
                'contacto_emergencia_nombre' => 'required|string|min:3',
                'contacto_emergencia_parentesco' => 'required|string',
                'contacto_emergencia_celular' => 'required|numeric',
            ]);
        } elseif ($this->paso == 2) {
            $this->validate([
                'doc_ci_adulto' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png',
                'doc_ci_familiar' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png',
            ]);
        } elseif ($this->paso == 3) {
            // Documentos institucionales - no validation needed, just informational or checkboxes
        } elseif ($this->paso == 4) {
            $this->validate([
                'enfermero_id' => 'required|exists:users,cod_usu',
            ]);
        }

        if ($this->paso < $this->totalPasos) {
            $this->paso++;
        }
    }

    public function anterior()
    {
        if ($this->paso > 1) {
            $this->paso--;
        }
    }

    public function confirmarPreadmision()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // 1. Encontrar estado VALORACION_INICIAL o PENDIENTE_VALORACION_INICIAL
            $estadoValoracion = EstadoAdulto::where('estado', 'PENDIENTE_VALORACION_INICIAL')
                                ->orWhere('estado', 'VALORACION_INICIAL')
                                ->first();

            if (!$estadoValoracion) {
                // Fallback a algún estado activo o crear uno
                $estadoValoracion = EstadoAdulto::firstOrCreate(['estado' => 'PENDIENTE_VALORACION_INICIAL']);
            }

            // 2. Crear Adulto Mayor
            $adulto = AdultoMayor::create([
                'nombres' => strtoupper($this->nombres),
                'ap_paterno' => strtoupper($this->ap_paterno),
                'ap_materno' => strtoupper($this->ap_materno ?? ''),
                'ci' => $this->ci,
                'expedicion_ci' => $this->expedicion_ci,
                'fecha_nac' => $this->fecha_nac,
                'genero' => $this->genero,
                'contacto_emergencia_nombre' => strtoupper($this->contacto_emergencia_nombre),
                'contacto_emergencia_parentesco' => strtoupper($this->contacto_emergencia_parentesco),
                'contacto_emergencia_celular' => $this->contacto_emergencia_celular,
                'contacto_emergencia_direccion' => strtoupper($this->contacto_emergencia_direccion ?? ''),
                'cod_est_adul' => $estadoValoracion->cod_est_adul,
                'estado_civil' => 'NO ESPECIFICADO', // default for pre-admission
                'tipo_ing' => 'REGULAR',
                'permanencia' => 'PERMANENTE',
                'grupo_sanguineo' => 'O+', // default o omitido en este paso rápido
                'seguro_salud' => 'NINGUNO',
                'nivel_educat' => 'NO ESPECIFICADO',
            ]);

            // 3. Crear Familiar Responsable
            $familiar = Familiar::create([
                'nombres' => strtoupper($this->contacto_emergencia_nombre),
                'celular' => $this->contacto_emergencia_celular,
            ]);

            $adulto->familiares()->attach($familiar->cod_fam, [
                'parentesco_vinculo' => strtoupper($this->contacto_emergencia_parentesco),
                'es_responsable' => true,
                'estado' => 'ACTIVO',
            ]);

            // 4. Guardar Documentos
            $path_adulto = $this->doc_ci_adulto->store('documentos/' . $adulto->cod_am, 'public');
            DocumentoAdultoMayor::create([
                'cod_am' => $adulto->cod_am,
                'tipo_doc' => 'CI_ADULTO_MAYOR',
                'ruta_archivo' => $path_adulto,
                'nom_doc' => 'CI Adulto Mayor',
                'extension' => $this->doc_ci_adulto->getClientOriginalExtension() ?: 'pdf',
                'fecha_doc' => now()->toDateString(),
                'observaciones' => 'Subido en Wizard Preadmisión'
            ]);

            $path_familiar = $this->doc_ci_familiar->store('documentos/' . $adulto->cod_am, 'public');
            DocumentoAdultoMayor::create([
                'cod_am' => $adulto->cod_am,
                'tipo_doc' => 'CI_FAMILIAR',
                'ruta_archivo' => $path_familiar,
                'nom_doc' => 'CI Familiar Responsable',
                'extension' => $this->doc_ci_familiar->getClientOriginalExtension() ?: 'pdf',
                'fecha_doc' => now()->toDateString(),
                'observaciones' => 'Subido en Wizard Preadmisión'
            ]);

            if ($this->doc_croquis) {
                $path_croquis = $this->doc_croquis->store('documentos/' . $adulto->cod_am, 'public');
                DocumentoAdultoMayor::create([
                    'cod_am' => $adulto->cod_am,
                    'tipo_doc' => 'CROQUIS',
                    'ruta_archivo' => $path_croquis,
                    'nom_doc' => 'Croquis Domicilio',
                    'extension' => $this->doc_croquis->getClientOriginalExtension() ?: 'pdf',
                    'fecha_doc' => now()->toDateString(),
                    'observaciones' => 'Subido en Wizard Preadmisión'
                ]);
            }

            // 5. Asignar Enfermero Valorador
            // We use the same AsignacionTurnoAdulto but just for initial valuation.
            $enfermero = User::where('cod_usu', $this->enfermero_id)->orWhere('id', $this->enfermero_id)->first();
            $turnoDefault = DB::table('turnos_enfermeria')->where('estado', 'ACTIVO')->first();

            AsignacionTurnoAdulto::create([
                'cod_am' => $adulto->cod_am,
                'cod_usu_enfermero' => $enfermero->cod_usu,
                'cod_turno' => $turnoDefault ? $turnoDefault->cod_turno : 1,
                'cod_habitacion' => null, // Default provisorio
                'cod_cama' => null, // Default provisorio
                'fecha_inicio' => now()->toDateString(),
                'motivo_asignacion' => 'VALORACION INICIAL',
                'estado' => 'ACTIVA'
            ]);

            // 6. Log
            activity('Admisiones')
                ->performedOn($adulto)
                ->log("Preadmisión confirmada. Asignado a enfermero: " . $enfermero->nombres);

            DB::commit();

            $this->dispatch('swal', [
                'title' => '¡Preadmisión Completada!',
                'text' => 'El adulto mayor está listo para la valoración inicial.',
                'icon' => 'success'
            ]);

            return redirect()->route('admin.admisiones.preadmisiones');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal', [
                'title' => 'Error',
                'text' => 'Hubo un problema: ' . $e->getMessage(),
                'icon' => 'error'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.admisiones.preadmision-wizard', [
            'enfermeros' => User::role('ENFERMEROS')->get()
        ])->layout('layouts.sistema');
    }
}
