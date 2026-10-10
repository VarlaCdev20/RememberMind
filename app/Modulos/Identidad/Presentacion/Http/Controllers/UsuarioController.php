<?php

namespace App\Modulos\Identidad\Presentacion\Http\Controllers;

use App\Compartido\Infraestructura\Documentos\DocumentacionUsuarioService;
use App\Compartido\Infraestructura\Documentos\DocumentosUsuarioService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\GestionarUsuariosHttp;
use App\Modulos\Identidad\Aplicacion\Consultas\UsuarioFichaService;
use App\Modulos\Identidad\Infraestructura\Jobs\EnviarFichaUsuarioJob;
use App\Modulos\Identidad\Presentacion\Http\Requests\StoreUsuarioRequest;
use App\Modulos\Identidad\Presentacion\Http\Requests\UpdateUsuarioRequest;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuariosPanel;
use App\Modulos\Reportes\Infraestructura\Exportaciones\ReportExportService;
use App\Modulos\Reportes\Infraestructura\Exportaciones\ReportFileNameService;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::query()->leftJoin('personal', 'usuarios.cod_usuario', '=', 'personal.cod_usuario')->with(['personal', 'roles'])->orderBy('personal.nombres')->select('usuarios.*')->get();

        return view('pages.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $roles = Role::all();
        $especialidades = collect();
        $cargosAdmin = collect();

        return view('pages.usuarios.create', compact('roles', 'especialidades', 'cargosAdmin'));
    }

    public function store(StoreUsuarioRequest $request)
    {
        try {
            $passwordLimpia = app(GestionarUsuariosHttp::class)->store(auth()->user(), $request->validated());

            return redirect()->route('admin.usuarios.index')->with('success', "Usuario registrado correctamente. Contraseña inicial: {$passwordLimpia}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error crítico al registrar usuario: '.$e->getMessage())->withInput();
        }
    }

    public function show(User $usuario)
    {
        return view('pages.usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario)
    {
        $roles = Role::all();
        $especialidades = collect();
        $cargosAdmin = collect();
        $personalSalud = $usuario->personalSalud;
        $personalAdmin = $usuario->personalAdmin;

        return view('pages.usuarios.edit', compact('usuario', 'roles', 'especialidades', 'cargosAdmin', 'personalSalud', 'personalAdmin'));
    }

    public function update(UpdateUsuarioRequest $request, User $usuario)
    {
        try {
            app(GestionarUsuariosHttp::class)->update(auth()->user(), $request->validated(), $usuario);

            return redirect()->route('admin.usuarios.index')->with('success', "Usuario {$usuario->name} actualizado correctamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar: '.$e->getMessage())->withInput();
        }
    }

    public function desactivar(User $usuario)
    {
        app(GestionarUsuariosHttp::class)->desactivar(auth()->user(), [], $usuario);

        return back()->with('success', "Usuario {$usuario->name} desactivado.");
    }

    public function activar(User $usuario)
    {
        app(GestionarUsuariosHttp::class)->activar(auth()->user(), [], $usuario);

        return back()->with('success', "Usuario {$usuario->name} activado.");
    }

    public function destroy(User $usuario)
    {
        return abort(403, 'No se permite la eliminación física de usuarios.');
    }

    public function fichaPdf(User $usuario)
    {
        try {
            $fichaService = app(UsuarioFichaService::class);
            $docService = app(DocumentacionUsuarioService::class);
            $expediente = $fichaService->obtenerExpedienteCompleto($usuario);
            $checklist = $docService->obtenerChecklistUsuario($usuario);
            $viewData = ['usuario' => $usuario, 'rol' => $expediente['rol'], 'nombre_rol' => $expediente['nombre_rol'], 'area' => $expediente['area'], 'horarios' => $expediente['horarios'], 'avance_documental' => $expediente['avance_documental'], 'checklist' => $checklist, 'fecha' => now()->format('d/m/Y H:i'), 'usuario_solicitante' => auth()->user()->name];
            $fileNameService = app(ReportFileNameService::class);
            $filename = $fileNameService->generate('expediente_'.$usuario->cod_usuario, 'pdf');
            $exportService = app(ReportExportService::class);
            activity('Usuarios')->causedBy(auth()->user())->performedOn($usuario)->event('reportes')->log("Descargó la ficha institucional completa en PDF para el usuario: {$usuario->name}.");

            return $exportService->exportPdf('pdf.exports.usuarios.expediente_ficha', $viewData, $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar la ficha PDF: '.$e->getMessage());
        }
    }

    public function documentacionPdf(User $usuario)
    {
        try {
            $fichaService = app(UsuarioFichaService::class);
            $docService = app(DocumentacionUsuarioService::class);
            $expediente = $fichaService->obtenerExpedienteCompleto($usuario);
            $checklist = $docService->obtenerChecklistUsuario($usuario);
            $viewData = ['usuario' => $usuario, 'rol' => $expediente['rol'], 'nombre_rol' => $expediente['nombre_rol'], 'area' => $expediente['area'], 'avance_documental' => $expediente['avance_documental'], 'checklist' => $checklist, 'fecha' => now()->format('d/m/Y H:i'), 'usuario_solicitante' => auth()->user()->name];
            $fileNameService = app(ReportFileNameService::class);
            $filename = $fileNameService->generate('checklist_documentacion_'.$usuario->cod_usuario, 'pdf');
            $exportService = app(ReportExportService::class);
            activity('Usuarios')->causedBy(auth()->user())->performedOn($usuario)->event('reportes')->log("Descargó el expediente documental en PDF para el usuario: {$usuario->name}.");

            return $exportService->exportPdf('pdf.exports.usuarios.documentacion_pdf', $viewData, $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar la documentación en PDF: '.$e->getMessage());
        }
    }

    public function horariosPdf(User $usuario)
    {
        try {
            $fichaService = app(UsuarioFichaService::class);
            $expediente = $fichaService->obtenerExpedienteCompleto($usuario);
            $viewData = ['usuario' => $usuario, 'rol' => $expediente['rol'], 'nombre_rol' => $expediente['nombre_rol'], 'area' => $expediente['area'], 'horarios' => $expediente['horarios'], 'historial_horarios' => $expediente['historial_horarios'], 'fecha' => now()->format('d/m/Y H:i'), 'usuario_solicitante' => auth()->user()->name];
            $fileNameService = app(ReportFileNameService::class);
            $filename = $fileNameService->generate('control_horarios_'.$usuario->cod_usuario, 'pdf');
            $exportService = app(ReportExportService::class);
            activity('Usuarios')->causedBy(auth()->user())->performedOn($usuario)->event('reportes')->log("Descargó el reporte de horarios y turnos en PDF para el usuario: {$usuario->name}.");

            return $exportService->exportPdf('pdf.exports.usuarios.horarios_pdf', $viewData, $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar los horarios en PDF: '.$e->getMessage());
        }
    }

    public function enviarFichaCorreo(User $usuario)
    {
        try {
            if (empty($usuario->correo)) {
                return response()->json(['success' => false, 'message' => 'El usuario no tiene un correo electrónico institucional configurado.'], 422);
            }
            // Despachar el Job en segundo plano para no congelar la interfaz
            EnviarFichaUsuarioJob::dispatch($usuario, auth()->user());

            return response()->json(['success' => true, 'message' => 'La solicitud de envío de ficha ha sido encolada correctamente. Se enviará a '.$usuario->correo]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al despachar el envío de ficha: '.$e->getMessage()], 500);
        }
    }

    public function solicitudDocumentalPdf(User $usuario)
    {
        try {
            $rol = $usuario->roles->first()?->name;
            $usuariosPanel = new UsuariosPanel;
            $documentos = app(DocumentosUsuarioService::class)->documentosRequeridosPorRol($rol);
            $rolLegible = $usuariosPanel->obtenerNombreRolLegible($rol);
            $fechaRegistro = $usuario->created_at ? $usuario->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i');
            $fechaLimite = $usuario->created_at ? $usuario->created_at->addHours(48)->format('d/m/Y H:i') : now()->addHours(48)->format('d/m/Y H:i');
            $viewData = ['usuario_reg' => $usuario, 'rol_legible' => $rolLegible, 'fecha_registro' => $fechaRegistro, 'fecha_limite' => $fechaLimite, 'documentos' => $documentos, 'fecha' => now()->format('d/m/Y H:i'), 'usuario' => auth()->check() ? auth()->user()->name : 'Sistema'];
            $fileNameService = app(ReportFileNameService::class);
            $filename = $fileNameService->generate('solicitud_documental_'.$usuario->cod_usuario, 'pdf');
            $exportService = app(ReportExportService::class);
            activity('Usuarios')->causedBy(auth()->user())->performedOn($usuario)->event('reportes')->log("Descargó la solicitud de documentación institucional en PDF para el usuario: {$usuario->name}.");

            return $exportService->exportPdf('pdf.exports.usuarios.solicitud_documental_pdf', $viewData, $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar la solicitud de documentación en PDF: '.$e->getMessage());
        }
    }
}
