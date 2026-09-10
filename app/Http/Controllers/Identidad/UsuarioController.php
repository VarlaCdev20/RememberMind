<?php

namespace App\Http\Controllers\Identidad;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Identidad\StoreUsuarioRequest;
use App\Http\Requests\Identidad\UpdateUsuarioRequest;
use App\Services\Reportes\DashboardService;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('nombres')->get();
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
            DB::beginTransaction();

            // Generar contraseña automática basada en iniciales + documento
            $passwordLimpia = $this->generarPasswordInicial(
                $request->nombres,
                $request->ap_paterno,
                $request->ap_materno,
                $request->numero_documento
            );

            $data = [
                'nombres'           => $request->nombres,
                'ap_paterno'        => $request->ap_paterno,
                'ap_materno'        => $request->ap_materno,
                'pais_documento'    => $request->pais_documento,
                'tipo_documento'    => $request->tipo_documento,
                'numero_documento'  => $request->numero_documento,
                'expedido'          => $request->expedido,
                'correo'            => $request->correo,
                'password'          => Hash::make($passwordLimpia),
                'telefono'          => $request->telefono,
                'pais_telefono'     => $request->pais_telefono,
                'codigo_telefono'   => $request->codigo_telefono,
                'genero'            => $request->genero,
                'fecha_nacimiento'  => $request->fecha_nacimiento,
                'estado'            => 'ACTIVO',
                'acceso_sistema'    => 'HABILITADO',
                'observaciones'     => Str::upper($request->observaciones),
            ];

            if ($request->hasFile('foto_perfil')) {
                $path = $request->file('foto_perfil')->store('perfiles', 'public');
                $data['foto_de_perfil'] = $path;
            }

            $usuario = User::create($data);
            $usuario->assignRole($request->rol);

            // Registro en tablas especializadas según rol (Removido por limpieza de base de datos)

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event('registro')
                ->log("Se registró un nuevo usuario institucional: {$usuario->name} con el rol {$request->rol}.");

            app(DashboardService::class)->limpiarCache(auth()->id());

            DB::commit();

            return redirect()->route('admin.usuarios.index')
                ->with('success', "Usuario registrado correctamente. Contraseña inicial: {$passwordLimpia}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error crítico al registrar usuario: ' . $e->getMessage())->withInput();
        }
    }

    private function generarPasswordInicial($nombres, $paterno, $materno, $documento)
    {
        $partes = explode(' ', trim($nombres));
        if ($paterno) $partes[] = $paterno;
        if ($materno) $partes[] = $materno;

        $iniciales = '';
        foreach ($partes as $parte) {
            if (!empty($parte)) {
                $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
            }
        }

        $docLimpio = preg_replace('/[^A-Za-z0-9]/', '', $documento);

        return $iniciales . $docLimpio;
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
            DB::beginTransaction();

            $data = $request->only([
                'nombres', 'ap_paterno', 'ap_materno', 'correo', 'telefono', 'estado',
                'pais_documento', 'tipo_documento', 'numero_documento', 'expedido',
                'pais_telefono', 'codigo_telefono', 'genero', 'fecha_nacimiento', 'acceso_sistema', 'observaciones'
            ]);
            
            if ($request->hasFile('foto_perfil')) {
                $path = $request->file('foto_perfil')->store('perfiles', 'public');
                $data['foto_de_perfil'] = $path;
            }

            if ($usuario->cod_usu === 'USU_0001') {
                unset($data['estado'], $data['acceso_sistema']);
            }

            $usuario->update($data);

            if ($usuario->cod_usu !== 'USU_0001') {
                $usuario->syncRoles([$request->rol]);
            }

            // Actualizar o crear registros vinculados (Removido por limpieza de base de datos)

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event('edicion')
                ->log("Se actualizó la información institucional del usuario {$usuario->name}.");

            app(DashboardService::class)->limpiarCache(auth()->id());

            DB::commit();

            return redirect()->route('admin.usuarios.index')
                ->with('success', "Usuario {$usuario->name} actualizado correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage())->withInput();
        }
    }

    public function desactivar(User $usuario)
    {
        $usuario->update(['estado' => 'INACTIVO']);
        app(DashboardService::class)->limpiarCache(auth()->id());
        return back()->with('success', "Usuario {$usuario->name} desactivado.");
    }

    public function activar(User $usuario)
    {
        $usuario->update(['estado' => 'ACTIVO']);
        app(DashboardService::class)->limpiarCache(auth()->id());
        return back()->with('success', "Usuario {$usuario->name} activado.");
    }

    public function destroy(User $usuario)
    {
        return abort(403, 'No se permite la eliminación física de usuarios.');
    }

    public function fichaPdf(User $usuario)
    {
        try {
            $fichaService = app(\App\Services\Identidad\UsuarioFichaService::class);
            $docService = app(\App\Services\Documentos\DocumentacionUsuarioService::class);

            $expediente = $fichaService->obtenerExpedienteCompleto($usuario);
            $checklist = $docService->obtenerChecklistUsuario($usuario);

            $viewData = [
                'usuario' => $usuario,
                'rol' => $expediente['rol'],
                'nombre_rol' => $expediente['nombre_rol'],
                'area' => $expediente['area'],
                'horarios' => $expediente['horarios'],
                'avance_documental' => $expediente['avance_documental'],
                'checklist' => $checklist,
                'fecha' => now()->format('d/m/Y H:i'),
                'usuario_solicitante' => auth()->user()->name,
            ];

            $fileNameService = app(\App\Services\Reportes\ReportFileNameService::class);
            $filename = $fileNameService->generate('expediente_' . $usuario->cod_usu, 'pdf');

            $exportService = app(\App\Services\Reportes\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event('reportes')
                ->log("Descargó la ficha institucional completa en PDF para el usuario: {$usuario->name}.");

            return $exportService->exportPdf('pdf.exports.usuarios.expediente_ficha', $viewData, $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar la ficha PDF: ' . $e->getMessage());
        }
    }

    public function documentacionPdf(User $usuario)
    {
        try {
            $fichaService = app(\App\Services\Identidad\UsuarioFichaService::class);
            $docService = app(\App\Services\Documentos\DocumentacionUsuarioService::class);

            $expediente = $fichaService->obtenerExpedienteCompleto($usuario);
            $checklist = $docService->obtenerChecklistUsuario($usuario);

            $viewData = [
                'usuario' => $usuario,
                'rol' => $expediente['rol'],
                'nombre_rol' => $expediente['nombre_rol'],
                'area' => $expediente['area'],
                'avance_documental' => $expediente['avance_documental'],
                'checklist' => $checklist,
                'fecha' => now()->format('d/m/Y H:i'),
                'usuario_solicitante' => auth()->user()->name,
            ];

            $fileNameService = app(\App\Services\Reportes\ReportFileNameService::class);
            $filename = $fileNameService->generate('checklist_documentacion_' . $usuario->cod_usu, 'pdf');

            $exportService = app(\App\Services\Reportes\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event('reportes')
                ->log("Descargó el expediente documental en PDF para el usuario: {$usuario->name}.");

            return $exportService->exportPdf('pdf.exports.usuarios.documentacion_pdf', $viewData, $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar la documentación en PDF: ' . $e->getMessage());
        }
    }

    public function horariosPdf(User $usuario)
    {
        try {
            $fichaService = app(\App\Services\Identidad\UsuarioFichaService::class);

            $expediente = $fichaService->obtenerExpedienteCompleto($usuario);

            $viewData = [
                'usuario' => $usuario,
                'rol' => $expediente['rol'],
                'nombre_rol' => $expediente['nombre_rol'],
                'area' => $expediente['area'],
                'horarios' => $expediente['horarios'],
                'historial_horarios' => $expediente['historial_horarios'],
                'fecha' => now()->format('d/m/Y H:i'),
                'usuario_solicitante' => auth()->user()->name,
            ];

            $fileNameService = app(\App\Services\Reportes\ReportFileNameService::class);
            $filename = $fileNameService->generate('control_horarios_' . $usuario->cod_usu, 'pdf');

            $exportService = app(\App\Services\Reportes\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event('reportes')
                ->log("Descargó el reporte de horarios y turnos en PDF para el usuario: {$usuario->name}.");

            return $exportService->exportPdf('pdf.exports.usuarios.horarios_pdf', $viewData, $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar los horarios en PDF: ' . $e->getMessage());
        }
    }

    public function enviarFichaCorreo(User $usuario)
    {
        try {
            if (empty($usuario->correo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no tiene un correo electrónico institucional configurado.'
                ], 422);
            }

            // Despachar el Job en segundo plano para no congelar la interfaz
            \App\Jobs\EnviarFichaUsuarioJob::dispatch($usuario, auth()->user());

            return response()->json([
                'success' => true,
                'message' => 'La solicitud de envío de ficha ha sido encolada correctamente. Se enviará a ' . $usuario->correo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al despachar el envío de ficha: ' . $e->getMessage()
            ], 500);
        }
    }

    public function solicitudDocumentalPdf(User $usuario)
    {
        try {
            $rol = $usuario->roles->first()?->name;
            
            $usuariosPanel = new \App\Livewire\Identidad\UsuariosPanel();
            $documentos = app(\App\Services\Documentos\DocumentosUsuarioService::class)->documentosRequeridosPorRol($rol);
            $rolLegible = $usuariosPanel->obtenerNombreRolLegible($rol);
            
            $fechaRegistro = $usuario->created_at ? $usuario->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i');
            $fechaLimite = $usuario->created_at ? $usuario->created_at->addHours(48)->format('d/m/Y H:i') : now()->addHours(48)->format('d/m/Y H:i');

            $viewData = [
                'usuario_reg' => $usuario,
                'rol_legible' => $rolLegible,
                'fecha_registro' => $fechaRegistro,
                'fecha_limite' => $fechaLimite,
                'documentos' => $documentos,
                'fecha' => now()->format('d/m/Y H:i'),
                'usuario' => auth()->check() ? auth()->user()->name : 'Sistema',
            ];

            $fileNameService = app(\App\Services\Reportes\ReportFileNameService::class);
            $filename = $fileNameService->generate('solicitud_documental_' . $usuario->cod_usu, 'pdf');

            $exportService = app(\App\Services\Reportes\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event('reportes')
                ->log("Descargó la solicitud de documentación institucional en PDF para el usuario: {$usuario->name}.");

            return $exportService->exportPdf('pdf.exports.usuarios.solicitud_documental_pdf', $viewData, $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar la solicitud de documentación en PDF: ' . $e->getMessage());
        }
    }

}


