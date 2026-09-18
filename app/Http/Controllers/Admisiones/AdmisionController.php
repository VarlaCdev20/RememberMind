<?php

namespace App\Http\Controllers\Admisiones;

use App\Actions\Admisiones\FormalizarAdmision;
use App\Http\Controllers\Controller;
use App\Models\Preadmision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdmisionController extends Controller
{
    public function store(Request $request, Preadmision $preadmision, FormalizarAdmision $formalizar): JsonResponse
    {
        $datos = $request->validate([
            'cod_cama'=>['required','exists:camas,cod_cama'], 'cod_contacto'=>['nullable','exists:contactos,cod_contacto'],
            'contacto'=>['nullable','array'], 'parentesco'=>['nullable','string','max:40'],
            'fecha_hora_admision'=>['nullable','date'], 'firma_residente'=>['nullable','boolean'],
            'tipo_consentimiento'=>['nullable','string','max:80'], 'cod_documento_consentimiento'=>['nullable','exists:documentos,cod_documento'],
            'nivel_educativo'=>['nullable','string','max:80'], 'grupo_sanguineo'=>['nullable','string','max:5'],
            'factor_rh'=>['nullable','string','max:5'], 'observacion'=>['nullable','string'],
        ]);
        $residente = $formalizar->ejecutar($preadmision, $datos, $request->user());
        return response()->json($residente, 201);
    }
}
