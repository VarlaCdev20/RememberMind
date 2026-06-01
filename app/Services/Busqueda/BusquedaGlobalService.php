<?php

namespace App\Services\Busqueda;

use App\Models\AdultoMayor;
use App\Models\User;
use App\Models\Familiar;
use App\Models\Voluntario;
use Illuminate\Support\Facades\Route;

class BusquedaGlobalService
{
    public function buscar(string $query, $usuarioActual)
    {
        $resultados = [];
        $query = trim($query);

        if (strlen($query) < 2) {
            return $resultados;
        }

        // 1. Módulos
        $modulos = $this->buscarModulos($query, $usuarioActual);
        if (!empty($modulos)) {
            $resultados[] = [
                'grupo' => 'Módulos',
                'items' => $modulos
            ];
        }

        // 2. Adultos Mayores
        if ($usuarioActual->can('adultos.ver')) {
            $adultos = $this->buscarAdultosMayores($query);
            if (!empty($adultos)) {
                $resultados[] = [
                    'grupo' => 'Adultos Mayores',
                    'items' => $adultos
                ];
            }
        }

        // 3. Usuarios
        if ($usuarioActual->can('usuarios.ver')) {
            $usuarios = $this->buscarUsuarios($query);
            if (!empty($usuarios)) {
                $resultados[] = [
                    'grupo' => 'Usuarios Institucionales',
                    'items' => $usuarios
                ];
            }
        }

        // 4. Familiares / Red de Apoyo
        if ($usuarioActual->can('adultos.ver') || $usuarioActual->can('familiares.ver')) {
            $familiares = $this->buscarFamiliares($query);
            if (!empty($familiares)) {
                $resultados[] = [
                    'grupo' => 'Red de Apoyo',
                    'items' => $familiares
                ];
            }
        }

        // 5. Voluntarios
        if ($usuarioActual->can('voluntarios.ver')) {
            $voluntarios = $this->buscarVoluntarios($query);
            if (!empty($voluntarios)) {
                $resultados[] = [
                    'grupo' => 'Voluntariado',
                    'items' => $voluntarios
                ];
            }
        }

        // 6. Registros de Salud y Seguimiento
        if ($usuarioActual->can('salud.ver')) {
            $saludRegistros = $this->buscarRegistrosSalud($query);
            if (!empty($saludRegistros)) {
                $resultados[] = [
                    'grupo' => 'Salud y Seguimiento',
                    'items' => $saludRegistros
                ];
            }
        }

        // 7. Documentos
        if ($usuarioActual->can('adultos.ver') || $usuarioActual->can('usuarios.ver')) {
            $documentos = $this->buscarDocumentos($query, $usuarioActual);
            if (!empty($documentos)) {
                $resultados[] = [
                    'grupo' => 'Documentos',
                    'items' => $documentos
                ];
            }
        }

        // 8. Reportes
        if ($usuarioActual->can('reportes.ver') || $usuarioActual->can('reportes.institucional') || $usuarioActual->can('usuarios.reportes')) {
            $reportes = $this->buscarReportes($query, $usuarioActual);
            if (!empty($reportes)) {
                $resultados[] = [
                    'grupo' => 'Reportes y Analíticas',
                    'items' => $reportes
                ];
            }
        }

        return $resultados;
    }

    private function buscarModulos(string $query, $usuarioActual)
    {
        $modulos = [];
        $queryLower = strtolower($query);

        $listaModulos = [
            [
                'titulo' => 'Dashboard',
                'subtitulo' => 'Panel de control principal',
                'icono' => 'ph-squares-four',
                'ruta' => route('dashboard'),
                'permiso' => null // Todos los logueados
            ],
            [
                'titulo' => 'Adultos Mayores',
                'subtitulo' => 'Gestión de expedientes y residentes',
                'icono' => 'ph-users',
                'ruta' => route('admin.adultos-mayores.index'),
                'permiso' => 'adultos.ver'
            ],
            [
                'titulo' => 'Alertas y Pendientes',
                'subtitulo' => 'Monitoreo activo de registros',
                'icono' => 'ph-bell-ringing',
                'ruta' => route('admin.adultos-mayores.alertas-pendientes'),
                'permiso' => 'adultos.ver'
            ],
            [
                'titulo' => 'Usuarios Institucionales',
                'subtitulo' => 'Personal, médicos y voluntarios',
                'icono' => 'ph-identification-card',
                'ruta' => route('admin.usuarios.index'),
                'permiso' => 'usuarios.ver'
            ],
            [
                'titulo' => 'Roles y Permisos',
                'subtitulo' => 'Gestión de accesos',
                'icono' => 'ph-shield-check',
                'ruta' => route('admin.roles-permisos.index'),
                'permiso' => 'roles.ver'
            ],
            [
                'titulo' => 'Áreas Institucionales',
                'subtitulo' => 'Departamentos y secciones',
                'icono' => 'ph-buildings',
                'ruta' => route('admin.areas-institucionales.index'),
                'permiso' => 'areas.ver'
            ],
            [
                'titulo' => 'Turnos y Asignaciones',
                'subtitulo' => 'Planificación de personal',
                'icono' => 'ph-calendar-check',
                'ruta' => route('admin.turnos-asignaciones.index'),
                'permiso' => 'turnos.ver'
            ],
            // FASE 2.3: Salud y Seguimiento (Módulos)
            [
                'titulo' => 'Salud y Seguimiento',
                'subtitulo' => 'Panel general de salud geriátrica',
                'icono' => 'ph-heartbeat',
                'ruta' => route('admin.salud-seguimiento.index'),
                'permiso' => 'salud.ver'
            ],
            [
                'titulo' => 'Fichas Médicas',
                'subtitulo' => 'Módulo de salud',
                'icono' => 'ph-file-text',
                'ruta' => route('admin.salud-seguimiento.ficha.index'),
                'permiso' => 'salud.ver'
            ],
            [
                'titulo' => 'Signos Vitales',
                'subtitulo' => 'Módulo de salud',
                'icono' => 'ph-activity',
                'ruta' => route('admin.salud-seguimiento.signos.index'),
                'permiso' => 'salud.ver'
            ],
            [
                'titulo' => 'Medicación',
                'subtitulo' => 'Módulo de salud',
                'icono' => 'ph-pill',
                'ruta' => route('admin.salud-seguimiento.medicacion.index'),
                'permiso' => 'salud.ver'
            ],
            [
                'titulo' => 'Valoración Funcional',
                'subtitulo' => 'Módulo de salud',
                'icono' => 'ph-person-arms-spread',
                'ruta' => route('admin.salud-seguimiento.valoracion.index'),
                'permiso' => 'salud.ver'
            ],
            [
                'titulo' => 'Administración de Medicación',
                'subtitulo' => 'Módulo de salud',
                'icono' => 'ph-syringe',
                'ruta' => route('admin.salud-seguimiento.administracion.index'),
                'permiso' => 'salud.ver'
            ],
        ];

        foreach ($listaModulos as $modulo) {
            if ($modulo['permiso'] === null || $usuarioActual->can($modulo['permiso'])) {
                if (str_contains(strtolower($modulo['titulo']), $queryLower) || str_contains(strtolower($modulo['subtitulo']), $queryLower)) {
                    $modulos[] = [
                        'tipo' => 'modulo',
                        'titulo' => $modulo['titulo'],
                        'subtitulo' => 'Módulo del sistema',
                        'detalle' => $modulo['subtitulo'],
                        'etiqueta' => 'Módulo',
                        'icono' => $modulo['icono'],
                        'url' => $modulo['ruta']
                    ];
                }
            }
        }

        return array_slice($modulos, 0, 5);
    }

    private function buscarAdultosMayores(string $query)
    {
        $adultos = AdultoMayor::query()
            ->where('nombres', 'like', "%{$query}%")
            ->orWhere('ap_paterno', 'like', "%{$query}%")
            ->orWhere('ap_materno', 'like', "%{$query}%")
            ->orWhere('ci', 'like', "%{$query}%")
            ->orWhere('cod_am', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        return $adultos->map(function ($adulto) {
            $nombreCompleto = trim("{$adulto->nombres} {$adulto->ap_paterno} {$adulto->ap_materno}");
            $estado = $adulto->estado_texto ?? 'Activo';
            
            // Masking el cod_am para mostrar visualmente (Ej. AM-****23)
            $maskedCod = strlen($adulto->cod_am) > 4 
                ? substr($adulto->cod_am, 0, 3) . '****' . substr($adulto->cod_am, -2)
                : '****';

            return [
                'tipo' => 'adulto_mayor',
                'titulo' => $nombreCompleto,
                'subtitulo' => 'Adulto mayor registrado',
                'detalle' => "Estado: {$estado} · Ficha: {$maskedCod}",
                'etiqueta' => 'Adulto Mayor',
                'icono' => 'ph-user-focus',
                'url' => route('admin.adultos-mayores.show', $adulto)
            ];
        })->toArray();
    }

    private function buscarUsuarios(string $query)
    {
        $usuarios = User::query()
            ->with('roles')
            ->where('nombres', 'like', "%{$query}%")
            ->orWhere('ap_paterno', 'like', "%{$query}%")
            ->orWhere('ap_materno', 'like', "%{$query}%")
            ->orWhere('correo', 'like', "%{$query}%")
            ->orWhere('cod_usu', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        return $usuarios->map(function ($usuario) {
            $nombreCompleto = $usuario->name;
            $rol = $usuario->roles->first()?->name ?? 'Sin rol';
            $estado = $usuario->estado == 1 ? 'Acceso habilitado' : 'Acceso restringido';
            
            // Masking email (Ej: carl**@admin.com)
            $partesCorreo = explode('@', $usuario->correo);
            $correoMasked = count($partesCorreo) == 2 && strlen($partesCorreo[0]) > 3 
                ? substr($partesCorreo[0], 0, 3) . '***@' . $partesCorreo[1] 
                : '***';

            return [
                'tipo' => 'usuario',
                'titulo' => $nombreCompleto,
                'subtitulo' => 'Usuario institucional',
                'detalle' => "Rol: {$rol} · {$estado} · {$correoMasked}",
                'etiqueta' => 'Usuario',
                'icono' => 'ph-user-circle',
                'url' => route('admin.usuarios.show', $usuario)
            ];
        })->toArray();
    }

    private function buscarFamiliares(string $query)
    {
        $familiares = Familiar::query()
            ->with(['usuario', 'adultosMayores'])
            ->whereHas('usuario', function ($q) use ($query) {
                $q->where('nombres', 'like', "%{$query}%")
                  ->orWhere('ap_paterno', 'like', "%{$query}%")
                  ->orWhere('ap_materno', 'like', "%{$query}%")
                  ->orWhere('telefono', 'like', "%{$query}%")
                  ->orWhere('numero_documento', 'like', "%{$query}%");
            })
            ->orWhere('parentesco', 'like', "%{$query}%")
            ->orWhereHas('adultosMayores', function ($q) use ($query) {
                $q->where('nombres', 'like', "%{$query}%")
                  ->orWhere('ap_paterno', 'like', "%{$query}%")
                  ->orWhere('ap_materno', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        return $familiares->map(function ($familiar) {
            $nombreCompleto = $familiar->usuario ? trim("{$familiar->usuario->nombres} {$familiar->usuario->ap_paterno} {$familiar->usuario->ap_materno}") : 'Familiar sin nombre';
            
            $adultoRelacionado = $familiar->adultosMayores->first();
            $nombreAdulto = $adultoRelacionado ? trim("{$adultoRelacionado->nombres} {$adultoRelacionado->ap_paterno}") : 'Sin adulto mayor vinculado';
            
            $url = $adultoRelacionado 
                ? route('admin.adultos-mayores.show', $adultoRelacionado) 
                : '#';

            return [
                'tipo' => 'familiar',
                'titulo' => $nombreCompleto,
                'subtitulo' => 'Red de apoyo / Familiar responsable',
                'detalle' => "Parentesco: {$familiar->parentesco} · Vinculado a: {$nombreAdulto}",
                'etiqueta' => 'Red de Apoyo',
                'icono' => 'ph-users-three',
                'url' => $url
            ];
        })->filter(function ($item) {
            return $item['url'] !== '#';
        })->values()->toArray();
    }

    private function buscarVoluntarios(string $query)
    {
        $voluntarios = Voluntario::query()
            ->with(['usuario', 'adultosMayores'])
            ->whereHas('usuario', function ($q) use ($query) {
                $q->where('nombres', 'like', "%{$query}%")
                  ->orWhere('ap_paterno', 'like', "%{$query}%")
                  ->orWhere('ap_materno', 'like', "%{$query}%")
                  ->orWhere('correo', 'like', "%{$query}%")
                  ->orWhere('telefono', 'like', "%{$query}%");
            })
            ->orWhere('cod_vol', 'like', "%{$query}%")
            ->orWhere('area_apoyo', 'like', "%{$query}%")
            ->orWhere('area_apoyo_preferente', 'like', "%{$query}%")
            ->orWhereHas('adultosMayores', function ($q) use ($query) {
                $q->where('nombres', 'like', "%{$query}%")
                  ->orWhere('ap_paterno', 'like', "%{$query}%")
                  ->orWhere('ap_materno', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        return $voluntarios->map(function ($voluntario) {
            $nombreCompleto = $voluntario->usuario ? trim("{$voluntario->usuario->nombres} {$voluntario->usuario->ap_paterno} {$voluntario->usuario->ap_materno}") : 'Voluntario sin nombre';
            
            $area = $voluntario->area_apoyo ?? $voluntario->area_apoyo_preferente ?? 'Área general';
            
            $adultoAsociado = $voluntario->adultosMayores->first();
            $vinculo = $adultoAsociado ? " · Asignado a: {$adultoAsociado->nombres}" : '';

            $url = $voluntario->usuario 
                ? route('admin.usuarios.show', $voluntario->usuario) 
                : route('admin.voluntariado.voluntarios.index');

            return [
                'tipo' => 'voluntario',
                'titulo' => $nombreCompleto,
                'subtitulo' => 'Voluntario institucional',
                'detalle' => "Apoyo: {$area}{$vinculo}",
                'etiqueta' => 'Voluntariado',
                'icono' => 'ph-hand-heart',
                'url' => $url
            ];
        })->toArray();
    }

    private function buscarRegistrosSalud(string $query)
    {
        $adultos = AdultoMayor::query()
            ->where(function ($q) use ($query) {
                $q->where('nombres', 'like', "%{$query}%")
                  ->orWhere('ap_paterno', 'like', "%{$query}%")
                  ->orWhere('ap_materno', 'like', "%{$query}%")
                  ->orWhere('cod_am', 'like', "%{$query}%");
            })
            // Solo buscar adultos que tengan AL MENOS un registro en los submódulos de salud
            ->where(function ($q) {
                $q->whereHas('fichasMedicas')
                  ->orWhereHas('signosVitales')
                  ->orWhereHas('valoracionesFuncionales')
                  ->orWhereHas('medicaciones');
            })
            ->limit(5)
            ->get();

        return $adultos->map(function ($adulto) {
            $nombreCompleto = trim("{$adulto->nombres} {$adulto->ap_paterno} {$adulto->ap_materno}");
            
            return [
                'tipo' => 'salud_registro',
                'titulo' => $nombreCompleto,
                'subtitulo' => 'Registro de salud disponible',
                'detalle' => 'Ficha clínica / Seguimiento activo · Revisar módulo de salud',
                'etiqueta' => 'Salud',
                'icono' => 'ph-heartbeat',
                'url' => route('admin.salud-seguimiento.resumen', $adulto)
            ];
        })->toArray();
    }

    private function buscarDocumentos(string $query, $usuarioActual)
    {
        $resultadosDocumentos = [];

        // Documentos de Adulto Mayor
        if ($usuarioActual->can('adultos.ver')) {
            $adultosConDoc = AdultoMayor::query()
                ->whereHas('documentos')
                ->where(function ($q) use ($query) {
                    $q->where('nombres', 'like', "%{$query}%")
                      ->orWhere('ap_paterno', 'like', "%{$query}%")
                      ->orWhere('ap_materno', 'like', "%{$query}%")
                      ->orWhereHas('documentos', function ($q2) use ($query) {
                          $q2->where('nom_doc', 'like', "%{$query}%")
                             ->orWhere('tipo_doc', 'like', "%{$query}%");
                      });
                })
                ->limit(3)
                ->get();

            foreach ($adultosConDoc as $adulto) {
                $nombreCompleto = trim("{$adulto->nombres} {$adulto->ap_paterno} {$adulto->ap_materno}");
                $resultadosDocumentos[] = [
                    'tipo' => 'documento_adulto',
                    'titulo' => $nombreCompleto,
                    'subtitulo' => 'Documentación institucional disponible',
                    'detalle' => 'Revisar documentos en ficha institucional',
                    'etiqueta' => 'Documentos',
                    'icono' => 'ph-file-text',
                    'url' => route('admin.adultos-mayores.documentos.index', $adulto)
                ];
            }
        }

        // Documentos de Usuario
        if ($usuarioActual->can('usuarios.ver')) {
            $usuariosConDoc = User::query()
                ->whereHas('documentos')
                ->where(function ($q) use ($query) {
                    $q->where('nombres', 'like', "%{$query}%")
                      ->orWhere('ap_paterno', 'like', "%{$query}%")
                      ->orWhere('ap_materno', 'like', "%{$query}%")
                      ->orWhereHas('documentos', function ($q2) use ($query) {
                          $q2->where('nombre_documento', 'like', "%{$query}%")
                             ->orWhere('tipo_documento', 'like', "%{$query}%")
                             ->orWhere('estado', 'like', "%{$query}%");
                      });
                })
                ->limit(3)
                ->get();

            foreach ($usuariosConDoc as $usuario) {
                $resultadosDocumentos[] = [
                    'tipo' => 'documento_usuario',
                    'titulo' => $usuario->name,
                    'subtitulo' => 'Documentación de usuario disponible',
                    'detalle' => 'Revisar documentación institucional',
                    'etiqueta' => 'Documentos',
                    'icono' => 'ph-folder-open',
                    'url' => route('admin.usuarios.documentos.preview', $usuario)
                ];
            }
        }

        return array_slice($resultadosDocumentos, 0, 5);
    }

    private function buscarReportes(string $query, $usuarioActual)
    {
        $reportes = [];
        $queryLower = strtolower(trim($query));

        $listaReportes = [
            [
                'titulo' => 'Reportes Institucionales',
                'subtitulo' => 'Panel de reportes globales',
                'detalle' => 'Consultar evidencia y métricas del centro',
                'icono' => 'ph-buildings',
                'ruta' => route('admin.reportes.institucional.preview'),
                'permiso' => 'reportes.institucional',
                'keywords' => ['reporte', 'reportes', 'institucional', 'global', 'centro', 'estadistica']
            ],
            [
                'titulo' => 'Reportes de Adultos Mayores',
                'subtitulo' => 'Panel de reportes demográficos',
                'detalle' => 'Consultar censos y expedientes por estado',
                'icono' => 'ph-users',
                'ruta' => route('admin.reportes.adultos.preview'),
                'permiso' => 'reportes.ver',
                'keywords' => ['reporte', 'reportes', 'adultos', 'mayores', 'residentes', 'censo']
            ],
            [
                'titulo' => 'Reportes de Salud y Seguimiento',
                'subtitulo' => 'Panel de reportes clínicos',
                'detalle' => 'Consultar estadísticas médicas y signos vitales',
                'icono' => 'ph-heartbeat',
                'ruta' => route('admin.reportes.salud.preview'),
                'permiso' => 'salud.ver',
                'keywords' => ['reporte', 'reportes', 'salud', 'clinico', 'medico', 'seguimiento']
            ],
            [
                'titulo' => 'Reportes de Red de Apoyo',
                'subtitulo' => 'Panel de reportes familiares',
                'detalle' => 'Consultar contactos y familiares responsables',
                'icono' => 'ph-users-three',
                'ruta' => route('admin.reportes.familiares.preview'),
                'permiso' => 'reportes.ver',
                'keywords' => ['reporte', 'reportes', 'familiares', 'red de apoyo', 'contactos']
            ],
            [
                'titulo' => 'Reportes de Equipo y Personal',
                'subtitulo' => 'Panel de reportes de RRHH',
                'detalle' => 'Consultar estadísticas de usuarios institucionales',
                'icono' => 'ph-identification-card',
                'ruta' => route('admin.reportes.equipo.preview'),
                'permiso' => 'usuarios.reportes',
                'keywords' => ['reporte', 'reportes', 'equipo', 'personal', 'usuarios', 'rrhh']
            ],
            [
                'titulo' => 'Reportes de Actividades',
                'subtitulo' => 'Panel de reportes de participación',
                'detalle' => 'Consultar asistencia y participación',
                'icono' => 'ph-calendar-star',
                'ruta' => route('admin.reportes.actividades.preview'),
                'permiso' => 'reportes.ver',
                'keywords' => ['reporte', 'reportes', 'actividades', 'talleres', 'participacion']
            ],
            [
                'titulo' => 'Bitácora y Auditoría',
                'subtitulo' => 'Panel de auditoría del sistema',
                'detalle' => 'Consultar registros de actividad del sistema',
                'icono' => 'ph-shield-check',
                'ruta' => route('admin.reportes.bitacora.preview'),
                'permiso' => 'reportes.ver',
                'keywords' => ['reporte', 'reportes', 'bitacora', 'auditoria', 'logs', 'seguridad']
            ],
        ];

        foreach ($listaReportes as $rep) {
            // Revisar permisos: se requiere el permiso específico o el global 'reportes.ver'
            if ($usuarioActual->can($rep['permiso']) || $usuarioActual->can('reportes.ver')) {
                
                $match = false;
                foreach ($rep['keywords'] as $kw) {
                    if (str_contains($queryLower, $kw) || str_contains($kw, $queryLower)) {
                        $match = true;
                        break;
                    }
                }

                if ($match) {
                    $reportes[] = [
                        'tipo' => 'reporte',
                        'titulo' => $rep['titulo'],
                        'subtitulo' => $rep['subtitulo'],
                        'detalle' => $rep['detalle'],
                        'etiqueta' => 'Reportes',
                        'icono' => $rep['icono'],
                        'url' => $rep['ruta']
                    ];
                }
            }
        }

        return array_slice($reportes, 0, 5);
    }
}
