<?php

namespace App\Frontend\Livewire\Superadministrador\Identidad;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesPermisosPanel extends Component
{
    public $permisosAgrupados;
    
    public $rolSeleccionadoId;
    public array $permisosSeleccionados = [];
    
    public string $busquedaPermiso = '';
    public string $grupoActivo = 'todos';
    
    public bool $hayCambios = false;
    public bool $mostrarUsuariosRol = false;

    #[Computed]
    public function roles()
    {
        $roles = Role::with('permissions')->get();
        
        $counts = \Illuminate\Support\Facades\DB::table(config('permission.table_names.model_has_roles'))
            ->select('role_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('role_id')
            ->pluck('count', 'role_id');

        foreach($roles as $rol) {
            $rol->setAttribute('users_count', $counts[$rol->id] ?? 0);
        }
        
        return $roles;
    }

    #[Computed]
    public function rolSeleccionado()
    {
        return $this->rolSeleccionadoId ? Role::with('permissions')->find($this->rolSeleccionadoId) : null;
    }

    #[Computed]
    public function usuariosDelRol()
    {
        if (!$this->rolSeleccionadoId) return collect();
        $rol = Role::find($this->rolSeleccionadoId);
        return $rol ? \App\Models\User::role($rol->name)->get() : collect();
    }

    public function mount()
    {
        $this->agruparPermisos();

        if ($this->roles->count() > 0) {
            $adminRole = $this->roles->where('name', 'SUPERADMINISTRADOR')->first();
            if ($adminRole) {
                $this->seleccionarRol($adminRole->id);
            } else {
                $this->seleccionarRol($this->roles->first()->id);
            }
        }
    }

    public function agruparPermisos()
    {
        $allPermissions = Permission::all()->pluck('name')->toArray();
        $grupos = [
            'Administración' => ['usuarios.', 'personal.', 'roles.', 'areas.', 'turnos.', 'jornadas.', 'asignaciones_personal.', 'auditoria.', 'bitacora.'],
            'Residentes y admisiones' => ['residentes.', 'preadmisiones.', 'admisiones.', 'habitaciones.', 'camas.', 'ocupaciones_cama.'],
            'Red de apoyo y documentos' => ['residentes_contactos.', 'contactos.', 'documentos.', 'consentimientos.'],
            'Salud y seguimiento' => ['salud.', 'atenciones.', 'notas_clinicas.', 'antecedentes_clinicos.', 'diagnosticos.', 'alergias.', 'signos_vitales.', 'prescripciones.', 'administraciones_medicacion.', 'estudios_clinicos.', 'resultados_estudio.', 'informes_estudio.'],
            'Cuidados de enfermería' => ['asignaciones_residente_jornada.', 'planes_cuidado.', 'intervenciones_cuidado.', 'programaciones_cuidado.', 'ejecuciones_cuidado.', 'pases_turno.', 'incidentes.', 'heridas.', 'curaciones_herida.', 'registros_'],
            'Valoraciones e instrumentos' => ['instrumentos.', 'preguntas_instrumento.', 'opciones_pregunta.', 'aplicaciones_instrumento.', 'respuestas_instrumento.', 'valoraciones_'],
            'Actividades y visitas' => ['actividades.', 'participantes_actividad.', 'visitas.'],
            'Reportes y alertas' => ['reportes.', 'alertas.', 'eventos_alerta.'],
        ];

        $this->permisosAgrupados = [];
        
        foreach ($allPermissions as $perm) {
            $asignado = false;
            foreach ($grupos as $grupoNombre => $prefijos) {
                foreach ($prefijos as $pref) {
                    if (str_starts_with($perm, $pref)) {
                        $this->permisosAgrupados[$grupoNombre][] = $perm;
                        $asignado = true;
                        break;
                    }
                }
                if ($asignado) break;
            }
            if (!$asignado) {
                $this->permisosAgrupados['Otros'][] = $perm;
            }
        }
    }

    public function seleccionarRol($roleId)
    {
        $this->rolSeleccionadoId = $roleId;
        
        $rol = Role::with('permissions')->find($roleId);
        $this->permisosSeleccionados = $rol->permissions->pluck('name')->toArray();
        $this->hayCambios = false;
        
        $this->mostrarUsuariosRol = false;
        $this->grupoActivo = 'todos';
    }

    public function togglePermiso($permissionName)
    {
        if (!auth()->user()->can('roles.editar_permisos')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para editar accesos.'
            ]);
            return;
        }

        $rol = Role::find($this->rolSeleccionadoId);
        if ($rol && $rol->name === 'SUPERADMINISTRADOR' && $permissionName === 'roles.editar_permisos') {
            if (in_array($permissionName, $this->permisosSeleccionados)) {
                $this->dispatch('swal', [
                    'icon' => 'warning',
                    'title' => 'Acción bloqueada',
                    'text' => 'No puedes quitar este permiso crítico al rol de superadministrador.'
                ]);
                return;
            }
        }

        if (in_array($permissionName, $this->permisosSeleccionados)) {
            $this->permisosSeleccionados = array_diff($this->permisosSeleccionados, [$permissionName]);
        } else {
            $this->permisosSeleccionados[] = $permissionName;
        }

        $this->hayCambios = true;
    }

    public function guardarPermisos()
    {
        if (!auth()->user()->can('roles.editar_permisos')) {
            return;
        }

        $rol = Role::find($this->rolSeleccionadoId);
        if ($rol) {
            $rol->syncPermissions($this->permisosSeleccionados);
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $this->hayCambios = false;
            unset($this->roles); // force refresh

            if (function_exists('activity')) {
                activity('Roles y Permisos')
                    ->performedOn($rol)
                    ->log("El usuario " . auth()->user()->nombres . " actualizó los permisos del rol " . strtoupper($rol->name));
            }

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Permisos actualizados',
                'text' => 'Los cambios se han aplicado correctamente.'
            ]);
        }
    }

    public function restaurarCambios()
    {
        if ($this->rolSeleccionadoId) {
            $rol = Role::with('permissions')->find($this->rolSeleccionadoId);
            $this->permisosSeleccionados = $rol->permissions->pluck('name')->toArray();
            $this->hayCambios = false;
        }
    }

    public function abrirUsuariosRol()
    {
        $this->mostrarUsuariosRol = true;
    }

    public function cerrarUsuariosRol()
    {
        $this->mostrarUsuariosRol = false;
    }

    // Helpers Visuales
    public function obtenerNombreVisualRol($roleName)
    {
        return strtoupper($roleName); // Todos los roles ya están en mayúsculas y nombrados correctamente
    }

    public function obtenerDescripcionRol($roleName)
    {
        return match ($roleName) {
            'SUPERADMINISTRADOR' => 'Acceso total absoluto al sistema y configuraciones críticas.',
            'ADMINISTRADOR' => 'Gestión institucional, administrativa, financiera y de reportes.',
            'ENFERMEROS' => 'Control de pacientes, administración de medicación, signos vitales y pase de turno.',
            'MEDICO GENERAL/GERIATRA' => 'Fichas clínicas completas, prescripciones, diagnósticos y altas médicas.',
            'PSICOLOGO/A' => 'Evaluaciones cognitivas, historial conductual y apoyo emocional.',
            'PEDAGOGO' => 'Diseño y control de actividades recreativas y estimulación cognitiva.',
            'NUTRICIONISTA' => 'Dietas, suplementación y control de peso de los residentes.',
            'FISIOTERAPEUTA' => 'Valoración funcional, rutinas físicas y terapias de rehabilitación.',
            'FAMILIAR' => 'Acceso limitado para ver el expediente y estado del adulto mayor vinculado.',
            default => 'Rol institucional general.'
        };
    }

    public function obtenerColorRol($roleName)
    {
        return match ($roleName) {
            'SUPERADMINISTRADOR' => 'bg-[#2F3E5C] text-white border border-[#2F3E5C]',
            'ADMINISTRADOR' => 'bg-[#E27D60] text-white border border-[#E27D60]',
            'ENFERMEROS' => 'bg-boton-acento text-white border border-boton-acento',
            'MEDICO GENERAL/GERIATRA' => 'bg-[#6A8CAF] text-white border border-[#6A8CAF]',
            'PSICOLOGO/A' => 'bg-[#A88CBA] text-white border border-[#A88CBA]',
            'PEDAGOGO' => 'bg-[#E5A86B] text-white border border-[#E5A86B]',
            'NUTRICIONISTA' => 'bg-[#8DA280] text-white border border-[#8DA280]',
            'FISIOTERAPEUTA' => 'bg-[#C78283] text-white border border-[#C78283]',
            'FAMILIAR' => 'bg-[#967B66] text-white border border-[#967B66]',
            default => 'bg-gray-200 text-gray-800'
        };
    }

    public function obtenerNombreVisualPermiso($permiso)
    {
        $diccionario = [
            'usuarios.ver' => 'Ver usuarios',
            'usuarios.crear' => 'Registrar usuarios',
            'usuarios.editar' => 'Editar usuarios',
            'usuarios.cambiar_estado' => 'Activar/Inactivar usuarios',
            'roles.ver' => 'Ver roles',
            'roles.editar_permisos' => 'Editar permisos',
            'areas.ver' => 'Ver áreas',
            'areas.crear' => 'Crear áreas',
            'areas.editar' => 'Editar áreas',
            'areas.cambiar_estado' => 'Estado de áreas',
            'turnos.ver' => 'Ver turnos',
            'turnos.crear' => 'Crear turnos',
            'turnos.editar' => 'Editar turnos',
            'turnos.cambiar_estado' => 'Estado de turnos',
            'bitacora.ver' => 'Ver bitácora',
            
            'residentes.ver' => 'Ver residentes',
            'residentes.gestionar' => 'Gestionar residentes',
            
            'residentes_contactos.ver' => 'Ver red de apoyo',
            'residentes_contactos.gestionar' => 'Gestionar red de apoyo',
            
            'documentos.ver' => 'Ver documentos',
            'documentos.subir' => 'Subir documentos',
            'documentos.gestionar' => 'Gestionar documentos',
            'documentos.archivar' => 'Archivar docs',
            
            'salud.ver' => 'Ver ficha de salud',
            'atenciones.ver' => 'Ver atenciones',
            'atenciones.crear' => 'Registrar atenciones',
            'atenciones.editar' => 'Editar atenciones',
            'atenciones.anular' => 'Anular atenciones',
            
            'observaciones.ver' => 'Ver observaciones',
            'observaciones.crear' => 'Añadir observaciones',
            'observaciones.editar' => 'Editar observaciones',
            'observaciones.anular' => 'Anular observaciones',
            
            'signos_vitales.ver' => 'Ver signos vitales',
            'signos_vitales.crear' => 'Registrar signos',
            'signos_vitales.editar' => 'Editar signos',
            
            'prescripciones.ver' => 'Ver medicación',
            'prescripciones.crear' => 'Recetar medicación',
            'prescripciones.editar' => 'Editar medicación',
            'prescripciones.suspender' => 'Suspender medicación',
            
            'aplicaciones_instrumento.ver' => 'Ver evaluaciones',
            'aplicaciones_instrumento.crear' => 'Hacer evaluaciones',
            'aplicaciones_instrumento.editar' => 'Editar evaluaciones',
            'aplicaciones_instrumento.anular' => 'Anular evaluaciones',
            
            'actividades.ver' => 'Ver actividades',
            'actividades.gestionar' => 'Gestionar actividades',
            
            'asignaciones.ver' => 'Ver asignaciones',
            'asignaciones.crear' => 'Crear asignaciones',
            'asignaciones.editar' => 'Editar asignaciones',
            
            'asistencia.ver' => 'Ver asistencias',
            'asistencia.registrar' => 'Tomar asistencia',
            
            'reportes.ver' => 'Ver reportes',
            'reportes.individual' => 'Reporte individual',
            'reportes.institucional' => 'Reporte institucional',
            'reportes.bienestar' => 'Reporte de bienestar',
            'reportes.exportar_pdf' => 'Exportar a PDF',
            
            'alertas.ver' => 'Ver alertas médicas',
            'alertas.gestionar' => 'Gestionar alertas',
        ];

        return $diccionario[$permiso] ?? ucfirst(str_replace(['.', '_'], ' ', $permiso));
    }

    public function render()
    {
        return view('livewire.identidad.roles-permisos-panel', [
            'roles' => $this->roles,
            'rolSeleccionado' => $this->rolSeleccionado,
            'usuariosDelRol' => $this->usuariosDelRol,
        ]);
    }
}
