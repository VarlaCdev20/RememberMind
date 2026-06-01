<?php

namespace App\Services\Busqueda;

use App\Models\AdultoMayor;
use App\Models\User;
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
}
