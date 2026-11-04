<?php

namespace App\Modulos\Identidad\Presentacion\Http\Controllers;

use App\Compartido\Infraestructura\Documentos\DocumentosUsuarioService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modulos\Identidad\Infraestructura\Notificaciones\EnviarPaqueteDocumental;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class DocumentosUsuarioController extends Controller
{
    protected DocumentosUsuarioService $docService;

    public function __construct(DocumentosUsuarioService $docService)
    {
        $this->docService = $docService;
    }

    public function preview(User $user)
    {
        if (! auth()->user()->can('usuarios.editar') && ! auth()->user()->can('usuarios.crear')) {
            abort(403, 'No autorizado.');
        }

        $datos = $this->docService->prepararPaquete($user);

        return view('pages.usuarios.documentos.paquete-documental', $datos);
    }

    public function paquetePdf(User $user)
    {
        if (! auth()->user()->can('usuarios.editar') && ! auth()->user()->can('usuarios.crear')) {
            abort(403, 'No autorizado.');
        }

        try {
            $datos = $this->docService->prepararPaquete($user);
            $filename = $this->docService->nombreArchivoPaquete($user);

            $pdf = Pdf::loadView('pages.usuarios.documentos.paquete-documental', $datos)
                ->setPaper('a4', 'portrait')
                ->setWarnings(false);

            if (function_exists('activity')) {
                activity('Usuarios')
                    ->causedBy(auth()->user())
                    ->performedOn($user)
                    ->event('reportes')
                    ->log("Descargó paquete documental PDF para el usuario: {$user->name}.");
            }

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error al generar PDF del paquete documental: '.$e->getMessage());

            return back()->with('error', 'No se pudo generar el PDF. Revise la configuración o intente nuevamente.');
        }
    }

    public function verDocumento(User $user, string $documento)
    {
        if (! auth()->user()->can('usuarios.editar') && ! auth()->user()->can('usuarios.crear')) {
            abort(403, 'No autorizado.');
        }

        $rol = $user->roles->first()?->name ?? 'sin_rol';

        if (! $this->docService->documentoDisponibleParaRol($rol, $documento)) {
            abort(404, 'El documento no está disponible para este rol.');
        }

        $datos = $this->docService->datosDocumentoIndividual($user, $documento);
        $vista = $this->docService->vistaDocumentoIndividual($documento, $rol);

        return view($vista, $datos);
    }

    public function pdfDocumento(User $user, string $documento)
    {
        if (! auth()->user()->can('usuarios.editar') && ! auth()->user()->can('usuarios.crear')) {
            abort(403, 'No autorizado.');
        }

        $rol = $user->roles->first()?->name ?? 'sin_rol';

        if (! $this->docService->documentoDisponibleParaRol($rol, $documento)) {
            abort(404, 'El documento no está disponible para este rol.');
        }

        try {
            $datos = $this->docService->datosDocumentoIndividual($user, $documento);
            $vista = $this->docService->vistaDocumentoIndividual($documento, $rol);
            $filename = $this->docService->nombreArchivoDocumento($user, $documento);

            $pdf = Pdf::loadView($vista, $datos)
                ->setPaper('a4', 'portrait')
                ->setWarnings(false);

            if (function_exists('activity')) {
                activity('Usuarios')
                    ->causedBy(auth()->user())
                    ->performedOn($user)
                    ->event('reportes')
                    ->log("Descargó documento individual ({$documento}) en PDF para el usuario: {$user->name}.");
            }

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error al generar PDF del documento individual: '.$e->getMessage());

            return back()->with('error', 'No se pudo generar el PDF. Revise la configuración o intente nuevamente.');
        }
    }

    public function imprimirDocumento(User $user, string $documento)
    {
        if (! auth()->user()->can('usuarios.editar') && ! auth()->user()->can('usuarios.crear')) {
            abort(403, 'No autorizado.');
        }

        $rol = $user->roles->first()?->name ?? 'sin_rol';

        if (! $this->docService->documentoDisponibleParaRol($rol, $documento)) {
            abort(404, 'El documento no está disponible para este rol.');
        }

        try {
            $datos = $this->docService->datosDocumentoIndividual($user, $documento);
            $vista = $this->docService->vistaDocumentoIndividual($documento, $rol);

            $pdf = Pdf::loadView($vista, $datos)
                ->setPaper('a4', 'portrait')
                ->setWarnings(false);

            return $pdf->stream($this->docService->nombreArchivoDocumento($user, $documento));
        } catch (\Exception $e) {
            Log::error('Error al generar vista de impresión: '.$e->getMessage());

            return back()->with('error', 'No se pudo generar la vista de impresión. Revise la configuración o intente nuevamente.');
        }
    }

    public function enviarPaqueteCorreo(User $user)
    {
        if (! auth()->user()->can('usuarios.editar') && ! auth()->user()->can('usuarios.crear')) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado.',
            ], 403);
        }

        try {
            if (empty($user->correo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no tiene un correo electrónico configurado.',
                ], 422);
            }

            $estado = app(EnviarPaqueteDocumental::class)->ejecutar(auth()->user(), $user);

            return response()->json([
                'success' => true,
                ...$estado,
                'estado_url' => route('admin.usuarios.correo.estado', [$user, $estado['solicitud']]),
            ], 202);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el paquete documental. Revise la configuración o intente nuevamente.',
            ], 500);
        }
    }
}
