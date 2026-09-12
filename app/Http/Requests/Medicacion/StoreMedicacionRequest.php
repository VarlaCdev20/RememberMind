<?php

namespace App\Http\Requests\Medicacion;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = $this->user();
        $permiso = $this->isMethod('post')
            ? ['medicacion.crear', 'salud.medicacion.crear']
            : ['medicacion.editar', 'salud.medicacion.editar'];
        return $usuario && ! $usuario->hasRole('ENFERMEROS') && $usuario->canAny($permiso);
    }

    protected function prepareForValidation(): void
    {
        if ($adulto = $this->route('adulto_mayor')) {
            $this->merge(['cod_am' => $adulto->cod_am]);
        }
    }

    public function rules(): array
    {
        return [
            'cod_am'               => 'required|string|exists:adulto_mayor,cod_am',
            'nombre_medicamento'   => 'required|string|max:200',
            'dosis'                => 'required|string|max:100',
            'frecuencia'           => 'required|string|max:100',
            'via_administracion'   => 'required|string|max:80',
            'hora_programada'      => 'nullable|date_format:H:i',
            'fecha_inicio'         => 'required|date',
            'fecha_fin'            => 'nullable|date|after_or_equal:fecha_inicio',
            'medico_indica'        => 'nullable|string|max:200',
            'documento_receta'     => 'nullable|string|exists:documentos_adulto_mayor,cod_doc_am',
            'estado'               => 'required|in:ACTIVO,SUSPENDIDO,FINALIZADO,ARCHIVADO',
            'observacion'          => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'cod_am.required'             => 'El adulto mayor es obligatorio.',
            'cod_am.exists'               => 'El adulto mayor seleccionado no existe.',
            'nombre_medicamento.required' => 'El nombre del medicamento es obligatorio.',
            'nombre_medicamento.max'      => 'El nombre del medicamento no puede exceder 200 caracteres.',
            'dosis.required'              => 'La dosis es obligatoria.',
            'frecuencia.required'         => 'La frecuencia es obligatoria.',
            'hora_programada.date_format' => 'La hora programada debe tener formato HH:MM.',
            'fecha_inicio.required'       => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date'           => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.after_or_equal'    => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'documento_receta.exists'     => 'El documento de receta seleccionado no existe.',
            'estado.required'             => 'El estado de la medicación es obligatorio.',
            'estado.in'                   => 'El estado debe ser ACTIVO, SUSPENDIDO, FINALIZADO o ARCHIVADO.',
        ];
    }
}
