<?php

namespace App\Http\Controllers\Admisiones;

use App\Http\Controllers\Controller;
use App\Models\Preadmision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PreadmisionController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $preadmisiones = Preadmision::query()->latest('fecha_solicitud')->paginate(20);
        return $request->expectsJson() ? response()->json($preadmisiones) : view('pages.preadmisiones.index', compact('preadmisiones'));
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'cod_contacto' => ['nullable','exists:contactos,cod_contacto'],
            'nombres' => ['required','string','max:100'], 'apellido_paterno' => ['required','string','max:80'],
            'apellido_materno' => ['nullable','string','max:80'], 'numero_documento' => ['nullable','string','max:30'],
            'expedicion_documento' => ['nullable','string','max:20'], 'fecha_nacimiento' => ['required','date','before:today'],
            'genero' => ['nullable','string','max:20'], 'estado_civil' => ['nullable','string','max:30'],
            'telefono' => ['nullable','string','max:30'], 'direccion' => ['nullable','string','max:255'],
            'motivo_ingreso' => ['required','string'], 'procedencia' => ['nullable','string','max:120'],
            'tipo_ingreso' => ['nullable','string','max:50'], 'permanencia' => ['nullable','string','max:50'],
            'prioridad' => ['nullable','string','max:20'], 'descripcion_caso' => ['nullable','string'],
        ]);
        $preadmision = Preadmision::query()->create([...$datos,
            'cod_preadmision'=>'PRE_'.Str::upper(Str::random(12)),
            'cod_usuario_registro'=>$request->user()->cod_usuario,
            'fecha_solicitud'=>now(),'estado'=>'PENDIENTE',
        ]);
        return response()->json($preadmision, 201);
    }

    public function revisar(Request $request, Preadmision $preadmision): JsonResponse
    {
        $datos = $request->validate(['estado'=>['required',Rule::in(['APROBADA','RECHAZADA'])],'motivo_rechazo'=>['nullable','required_if:estado,RECHAZADA','string']]);
        if ($preadmision->estado !== 'PENDIENTE') { abort(409, 'La preadmisión ya fue revisada.'); }
        $preadmision->update([...$datos,'cod_usuario_revision'=>$request->user()->cod_usuario,'fecha_revision'=>now()]);
        return response()->json($preadmision->fresh());
    }
}
