<?php

namespace App\Modulos\Clinica\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DocumentoClinico;
use App\Models\EstudioClinico;
use App\Models\Residente;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Clinica\Aplicacion\Acciones\GestionarEstudiosClinicos;
use App\Modulos\Cuidados\Presentacion\Http\Controllers\FotografiaHeridaController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EstudioClinicoController extends Controller
{
    private const ROLES_SOLICITUD = ['MEDICO GENERAL/GERIATRA'];

    public function __construct(private readonly AutorizacionClinicaService $autorizacion) {}

    public function index(Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);

        return response()->json(EstudioClinico::query()->where('cod_residente', $residente->cod_residente)->with(['tipo.componentes', 'resultados', 'informes'])->latest('fecha_solicitud')->get());
    }

    public function solicitar(Request $request, Residente $residente): JsonResponse
    {
        return response()->json(app(GestionarEstudiosClinicos::class)->solicitar($request->user(), $request->all(), $residente), 201);
    }

    public function resultados(Request $request, EstudioClinico $estudio): JsonResponse
    {
        return response()->json(app(GestionarEstudiosClinicos::class)->resultados($request->user(), $request->all(), $estudio), 201);
    }

    public function informar(Request $request, EstudioClinico $estudio): JsonResponse
    {
        return response()->json(app(GestionarEstudiosClinicos::class)->informar($request->user(), $request->all(), $estudio), 201);
    }

    public function documento(Request $request, Residente $residente): JsonResponse
    {
        return response()->json(app(GestionarEstudiosClinicos::class)->documento($request->user(), $request->all(), $residente), 201);
    }

    public function descargar(DocumentoClinico $documentoClinico): StreamedResponse
    {
        if ($documentoClinico->tipo_documento === 'FOTO_HERIDA') {
            return app(FotografiaHeridaController::class)(request(), $documentoClinico->residente()->firstOrFail(), $documentoClinico);
        }
        $this->authorize('view', $documentoClinico->residente()->firstOrFail());
        abort_unless(request()->user()->can('documentos_clinicos.ver'), 403);
        abort_unless(Storage::disk('local')->exists($documentoClinico->ruta_archivo), 404);

        return Storage::disk('local')->download($documentoClinico->ruta_archivo, $documentoClinico->titulo);
    }

    public function derivar(Request $request, Residente $residente): JsonResponse
    {
        return response()->json(app(GestionarEstudiosClinicos::class)->derivar($request->user(), $request->all(), $residente), 201);
    }
}
