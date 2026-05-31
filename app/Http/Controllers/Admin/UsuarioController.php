<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Admin\StoreUsuarioRequest;
use App\Http\Requests\Admin\UpdateUsuarioRequest;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('nombres')->get();
        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $roles = Role::all();
        $especialidades = \App\Models\Especialidad::orderBy('nombre')->get();
        $cargosAdmin = \App\Models\CargoAdministrativo::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        
        return view('admin.usuarios.create', compact('roles', 'especialidades', 'cargosAdmin'));
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

            // Registro en tablas especializadas según rol
            if ($request->rol === 'personal_salud') {
                \App\Models\PersonalSalud::create([
                    'cod_usu'        => $usuario->cod_usu,
                    'cod_esp'        => $request->especialidad_salud,
                    'fecha_ing'      => $request->fecha_ingreso ?? now(),
                    'estado_laboral' => 'ACTIVO',
                ]);
            } elseif ($request->rol === 'personal_admin') {
                \App\Models\PersonalAdmin::create([
                    'cod_usu'          => $usuario->cod_usu,
                    'cod_cargo_admin'  => $request->cargo_administrativo,
                    'cargo'            => \App\Models\CargoAdministrativo::find($request->cargo_administrativo)?->nombre ?? 'ADMINISTRATIVO',
                    'fecha_ingreso'    => $request->fecha_ingreso ?? now(),
                    'area_admin'       => 'GENERAL',
                    'estado_laboral'   => 'ACTIVO',
                ]);
            }

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
        return view('admin.usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario)
    {
        $roles = Role::all();
        $especialidades = \App\Models\Especialidad::orderBy('nombre')->get();
        $cargosAdmin = \App\Models\CargoAdministrativo::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        
        $personalSalud = $usuario->personalSalud->first();
        $personalAdmin = $usuario->personalAdmin->first();

        return view('admin.usuarios.edit', compact('usuario', 'roles', 'especialidades', 'cargosAdmin', 'personalSalud', 'personalAdmin'));
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

            // Actualizar o crear registros vinculados
            if ($request->rol === 'personal_salud') {
                \App\Models\PersonalSalud::updateOrCreate(
                    ['cod_usu' => $usuario->cod_usu],
                    [
                        'cod_esp'        => $request->especialidad_salud,
                        'fecha_ing'      => $request->fecha_ingreso ?? ($usuario->personalSalud->first()?->fecha_ing ?? now()),
                        'estado_laboral' => 'ACTIVO',
                    ]
                );
            } elseif ($request->rol === 'personal_admin') {
                \App\Models\PersonalAdmin::updateOrCreate(
                    ['cod_usu' => $usuario->cod_usu],
                    [
                        'cod_cargo_admin'  => $request->cargo_administrativo,
                        'cargo'            => \App\Models\CargoAdministrativo::find($request->cargo_administrativo)?->nombre ?? 'ADMINISTRATIVO',
                        'fecha_ingreso'    => $request->fecha_ingreso ?? ($usuario->personalAdmin->first()?->fecha_ingreso ?? now()),
                        'area_admin'       => 'GENERAL',
                        'estado_laboral'   => 'ACTIVO',
                    ]
                );
            }

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
            $fichaService = app(\App\Services\Usuarios\UsuarioFichaService::class);
            $docService = app(\App\Services\Usuarios\DocumentacionUsuarioService::class);

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

            $fileNameService = app(\App\Services\Reports\ReportFileNameService::class);
            $filename = $fileNameService->generate('expediente_' . $usuario->cod_usu, 'pdf');

            $exportService = app(\App\Services\Reports\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event('reportes')
                ->log("Descargó la ficha institucional completa en PDF para el usuario: {$usuario->name}.");

            return $exportService->exportPdf('reports.usuarios.expediente_ficha', $viewData, $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar la ficha PDF: ' . $e->getMessage());
        }
    }

    public function documentacionPdf(User $usuario)
    {
        try {
            $fichaService = app(\App\Services\Usuarios\UsuarioFichaService::class);
            $docService = app(\App\Services\Usuarios\DocumentacionUsuarioService::class);

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

            $fileNameService = app(\App\Services\Reports\ReportFileNameService::class);
            $filename = $fileNameService->generate('checklist_documentacion_' . $usuario->cod_usu, 'pdf');

            $exportService = app(\App\Services\Reports\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event('reportes')
                ->log("Descargó el expediente documental en PDF para el usuario: {$usuario->name}.");

            return $exportService->exportPdf('reports.usuarios.documentacion_pdf', $viewData, $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar la documentación en PDF: ' . $e->getMessage());
        }
    }

    public function horariosPdf(User $usuario)
    {
        try {
            $fichaService = app(\App\Services\Usuarios\UsuarioFichaService::class);

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

            $fileNameService = app(\App\Services\Reports\ReportFileNameService::class);
            $filename = $fileNameService->generate('control_horarios_' . $usuario->cod_usu, 'pdf');

            $exportService = app(\App\Services\Reports\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event('reportes')
                ->log("Descargó el reporte de horarios y turnos en PDF para el usuario: {$usuario->name}.");

            return $exportService->exportPdf('reports.usuarios.horarios_pdf', $viewData, $filename);
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
}
