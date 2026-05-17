<?php

namespace App\Livewire\Admin\AdultosMayores\Salud;

use Livewire\Component;
use App\Models\ValoracionFuncionalAdulto;
use Illuminate\Support\Facades\Auth;

class ValoracionFuncionalAdultoModal extends Component
{
    public $showModal = false;
    public $isEditing = false;
    public $cod_val_func = null;
    public $cod_am;

    public $fecha_valoracion = '';
    public $come_solo = false;
    public $se_bana_solo = false;
    public $se_viste_solo = false;
    public $va_bano_solo = false;
    public $camina_solo = false;
    public $usa_baston = false;
    public $usa_andador = false;
    public $usa_silla_ruedas = false;
    public $baja_vision = false;
    public $baja_audicion = false;
    public $dificultad_hablar = false;
    public $molestia_luz = false;
    public $molestia_ruido = false;
    public $se_asusta_facil = false;
    public $necesita_supervision = false;
    public $nivel_dependencia = 'INDEPENDIENTE';
    public $observacion = '';

    protected $listeners = ['abrirModalValoracion'];

    public function rules()
    {
        return [
            'fecha_valoracion' => 'required|date',
            'nivel_dependencia' => 'required|in:INDEPENDIENTE,LEVE,MODERADO,SEVERO',
            'observacion' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'fecha_valoracion.required' => 'La fecha es obligatoria.',
            'nivel_dependencia.required' => 'El nivel de dependencia es obligatorio.',
        ];
    }

    public function abrirModalValoracion($cod_am, $id_val = null)
    {
        $this->resetValidation();
        $this->cod_am = $cod_am;
        
        if ($id_val) {
            $this->isEditing = true;
            $this->cod_val_func = $id_val;
            $this->cargarDatos();
        } else {
            $this->isEditing = false;
            $this->resetCampos();
            $this->fecha_valoracion = now()->format('Y-m-d');
            $this->calcularDependencia();
        }

        $this->showModal = true;
    }

    public function cerrarModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function cargarDatos()
    {
        $val = ValoracionFuncionalAdulto::findOrFail($this->cod_val_func);
        
        $this->fecha_valoracion = $val->fecha_valoracion ? $val->fecha_valoracion->format('Y-m-d') : null;
        $this->come_solo = $val->come_solo;
        $this->se_bana_solo = $val->se_bana_solo;
        $this->se_viste_solo = $val->se_viste_solo;
        $this->va_bano_solo = $val->va_bano_solo;
        $this->camina_solo = $val->camina_solo;
        $this->usa_baston = $val->usa_baston;
        $this->usa_andador = $val->usa_andador;
        $this->usa_silla_ruedas = $val->usa_silla_ruedas;
        $this->baja_vision = $val->baja_vision;
        $this->baja_audicion = $val->baja_audicion;
        $this->dificultad_hablar = $val->dificultad_hablar;
        $this->molestia_luz = $val->molestia_luz;
        $this->molestia_ruido = $val->molestia_ruido;
        $this->se_asusta_facil = $val->se_asusta_facil;
        $this->necesita_supervision = $val->necesita_supervision;
        $this->nivel_dependencia = $val->nivel_dependencia;
        $this->observacion = $val->observacion;
    }

    public function resetCampos()
    {
        $this->cod_val_func = null;
        $this->fecha_valoracion = '';
        $this->come_solo = true;
        $this->se_bana_solo = true;
        $this->se_viste_solo = true;
        $this->va_bano_solo = true;
        $this->camina_solo = true;
        $this->usa_baston = false;
        $this->usa_andador = false;
        $this->usa_silla_ruedas = false;
        $this->baja_vision = false;
        $this->baja_audicion = false;
        $this->dificultad_hablar = false;
        $this->molestia_luz = false;
        $this->molestia_ruido = false;
        $this->se_asusta_facil = false;
        $this->necesita_supervision = false;
        $this->nivel_dependencia = 'INDEPENDIENTE';
        $this->observacion = '';
    }

    public function updated($propertyName)
    {
        $this->calcularDependencia();
    }

    public function calcularDependencia()
    {
        // Simple heuristic for dependency
        $independenciaScore = 0;
        if ($this->come_solo) $independenciaScore++;
        if ($this->se_bana_solo) $independenciaScore++;
        if ($this->se_viste_solo) $independenciaScore++;
        if ($this->va_bano_solo) $independenciaScore++;
        if ($this->camina_solo) $independenciaScore++;

        if ($this->necesita_supervision || $this->usa_silla_ruedas) {
            $this->nivel_dependencia = 'SEVERO';
        } elseif ($independenciaScore <= 2) {
            $this->nivel_dependencia = 'MODERADO';
        } elseif ($independenciaScore < 5 || $this->usa_baston || $this->usa_andador) {
            $this->nivel_dependencia = 'LEVE';
        } else {
            $this->nivel_dependencia = 'INDEPENDIENTE';
        }
    }

    public function guardar()
    {
        $this->validate();

        $datos = [
            'cod_am' => $this->cod_am,
            'fecha_valoracion' => $this->fecha_valoracion,
            'come_solo' => $this->come_solo,
            'se_bana_solo' => $this->se_bana_solo,
            'se_viste_solo' => $this->se_viste_solo,
            'va_bano_solo' => $this->va_bano_solo,
            'camina_solo' => $this->camina_solo,
            'usa_baston' => $this->usa_baston,
            'usa_andador' => $this->usa_andador,
            'usa_silla_ruedas' => $this->usa_silla_ruedas,
            'baja_vision' => $this->baja_vision,
            'baja_audicion' => $this->baja_audicion,
            'dificultad_hablar' => $this->dificultad_hablar,
            'molestia_luz' => $this->molestia_luz,
            'molestia_ruido' => $this->molestia_ruido,
            'se_asusta_facil' => $this->se_asusta_facil,
            'necesita_supervision' => $this->necesita_supervision,
            'nivel_dependencia' => $this->nivel_dependencia,
            'observacion' => $this->observacion,
            'registrado_por' => Auth::id() ?? \App\Models\User::first()->cod_usu,
        ];

        if ($this->isEditing) {
            $val = ValoracionFuncionalAdulto::findOrFail($this->cod_val_func);
            $val->update($datos);
            $mensaje = 'Valoración funcional actualizada correctamente.';
        } else {
            ValoracionFuncionalAdulto::create($datos);
            $mensaje = 'Valoración funcional registrada correctamente.';
        }

        $this->cerrarModal();
        
        $this->dispatch('valoracion-actualizada');
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => $mensaje,
        ]);
    }

    public function render()
    {
        $valoracionList = ValoracionFuncionalAdulto::where('cod_am', $this->cod_am)
            ->latest('fecha_valoracion')
            ->take(5)
            ->get();

        return view('livewire.admin.adultos-mayores.salud.valoracion-funcional-adulto-modal', [
            'valoracionList' => $valoracionList
        ]);
    }
}
