<?php

namespace App\Http\Controllers\Residentes;

use App\Http\Controllers\Controller;
use App\Models\Consentimiento;
use App\Models\Contacto;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\Visita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RelacionResidenteController extends Controller
{
    public function index(Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);

        return response()->json([
            'contactos' => $residente->vinculosContacto()->with('contacto')->get(),
            'consentimientos' => Consentimiento::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_consentimiento')->get(),
            'visitas' => Visita::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora_programada')->get(),
            'ocupacion' => $residente->ocupacionActiva()->with('cama.habitacion')->first(),
        ]);
    }

    public function vincularContacto(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('contactos.gestionar'), 403);
        $datos = $request->validate([
            'cod_contacto' => ['nullable', 'exists:contactos,cod_contacto'], 'contacto' => ['nullable', 'array'],
            'contacto.cod_usuario' => ['nullable', 'exists:usuarios,cod_usuario', 'unique:contactos,cod_usuario'],
            'contacto.nombres' => ['required_without:cod_contacto', 'string', 'max:100'],
            'contacto.apellido_paterno' => ['required_without:cod_contacto', 'string', 'max:80'],
            'contacto.apellido_materno' => ['nullable', 'string', 'max:80'], 'contacto.numero_documento' => ['nullable', 'string', 'max:30'],
            'contacto.telefono' => ['nullable', 'string', 'max:30'], 'contacto.celular' => ['nullable', 'string', 'max:30'],
            'contacto.correo' => ['nullable', 'email', 'max:120'], 'contacto.direccion' => ['nullable', 'string', 'max:255'],
            'parentesco' => ['required', 'string', 'max:40'], 'responsable_principal' => ['required', 'boolean'],
            'contacto_emergencia' => ['required', 'boolean'], 'autoriza_informacion' => ['required', 'boolean'],
            'autoriza_salida' => ['required', 'boolean'], 'observacion' => ['nullable', 'string'],
        ]);

        $vinculo = DB::transaction(function () use ($datos, $residente): ResidenteContacto {
            $contacto = isset($datos['cod_contacto'])
                ? Contacto::query()->findOrFail($datos['cod_contacto'])
                : Contacto::query()->create([
                    'cod_contacto' => $this->codigo('CTO'), ...$datos['contacto'], 'estado' => 'ACTIVO',
                ]);
            if ($datos['responsable_principal']) {
                ResidenteContacto::query()->where('cod_residente', $residente->cod_residente)
                    ->where('estado', 'ACTIVO')->update(['responsable_principal' => false]);
            }
            return ResidenteContacto::query()->create([
                'cod_residente_contacto' => $this->codigo('RCO'), 'cod_residente' => $residente->cod_residente,
                'cod_contacto' => $contacto->cod_contacto, 'parentesco' => $datos['parentesco'],
                'responsable_principal' => $datos['responsable_principal'], 'contacto_emergencia' => $datos['contacto_emergencia'],
                'autoriza_informacion' => $datos['autoriza_informacion'], 'autoriza_salida' => $datos['autoriza_salida'],
                'estado' => 'ACTIVO', 'observacion' => $datos['observacion'] ?? null,
            ]);
        });

        return response()->json($vinculo->load('contacto'), 201);
    }

    public function registrarVisita(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('visitas.gestionar'), 403);
        $datos = $request->validate([
            'cod_contacto' => ['required', 'exists:contactos,cod_contacto'], 'fecha_hora_programada' => ['nullable', 'date'],
            'fecha_hora_ingreso' => ['nullable', 'date'], 'fecha_hora_salida' => ['nullable', 'date', 'after_or_equal:fecha_hora_ingreso'],
            'motivo' => ['nullable', 'string', 'max:160'], 'estado' => ['required', 'in:PROGRAMADA,AUTORIZADA,EN_CURSO,FINALIZADA,CANCELADA'],
            'observacion' => ['nullable', 'string'],
        ]);
        abort_unless(ResidenteContacto::query()->where('cod_residente', $residente->cod_residente)
            ->where('cod_contacto', $datos['cod_contacto'])->where('estado', 'ACTIVO')->exists(), 422, 'El contacto no está vinculado al residente.');

        return response()->json(Visita::query()->create([
            'cod_visita' => $this->codigo('VIS'), 'cod_residente' => $residente->cod_residente,
            'cod_usuario_autorizacion' => $request->user()->cod_usuario, ...$datos,
        ]), 201);
    }

    public function registrarConsentimiento(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('consentimientos.gestionar'), 403);
        $admision = $residente->admisiones()->where('estado', 'ACTIVA')->latest('fecha_hora_admision')->firstOrFail();
        $datos = $request->validate([
            'cod_residente_contacto' => ['nullable', 'exists:residentes_contactos,cod_residente_contacto'],
            'cod_documento' => ['nullable', 'exists:documentos,cod_documento'], 'tipo_consentimiento' => ['required', 'string', 'max:80'],
            'firma_residente' => ['required', 'boolean'], 'fecha_consentimiento' => ['nullable', 'date'],
            'observacion' => ['nullable', 'string'],
        ]);
        if (isset($datos['cod_residente_contacto'])) {
            abort_unless(ResidenteContacto::query()->whereKey($datos['cod_residente_contacto'])
                ->where('cod_residente', $residente->cod_residente)->where('estado', 'ACTIVO')->exists(), 422, 'El firmante no está vinculado al residente.');
        }

        return response()->json(Consentimiento::query()->create([
            'cod_consentimiento' => $this->codigo('CON'), 'cod_residente' => $residente->cod_residente,
            'cod_admision' => $admision->cod_admision, 'cod_usuario_registro' => $request->user()->cod_usuario,
            ...$datos, 'fecha_consentimiento' => $datos['fecha_consentimiento'] ?? now(), 'estado' => 'VIGENTE',
        ]), 201);
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }
}
