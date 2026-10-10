<?php

namespace App\Backend\Modulos\Enfermeria\Servicios;

/** Accesos de interfaz a capacidades existentes; no define reglas clínicas. */
final class NavegacionCuidadosService
{
    public static function opciones(): array
    {
        return [
            'signos' => ['label' => 'Signos', 'permission' => 'signos_vitales.ver', 'form' => 'signos', 'create' => 'signos_vitales.crear'],
            'dolor' => ['label' => 'Dolor', 'permission' => 'valoraciones_dolor.ver', 'form' => 'dolor', 'create' => 'valoraciones_dolor.crear'],
            'cognicion' => ['label' => 'Cognición', 'permission' => 'controles_cognitivos.ver', 'route' => 'admin.enfermeria.seguimiento'],
            'conducta' => ['label' => 'Conducta', 'permission' => 'registros_conductuales.ver', 'route' => 'admin.enfermeria.seguimiento'],
            'sueno' => ['label' => 'Sueño', 'permission' => 'registros_sueno.ver', 'route' => 'admin.enfermeria.registros', 'parameters' => ['tipo' => 'SUENO']],
            'ingesta' => ['label' => 'Ingesta', 'permission' => 'registros_ingesta.ver', 'form' => 'alimentacion', 'create' => 'registros_ingesta.crear'],
            'hidratacion' => ['label' => 'Hidratación', 'permission' => 'registros_hidratacion.ver', 'form' => 'hidratacion', 'create' => 'registros_hidratacion.crear'],
            'eliminacion' => ['label' => 'Eliminación', 'permission' => 'registros_eliminacion.ver', 'form' => 'eliminacion', 'create' => 'registros_eliminacion.crear'],
            'movilidad' => ['label' => 'Movilidad', 'permission' => 'registros_movilidad.ver', 'form' => 'movilidad', 'create' => 'registros_movilidad.crear'],
            'heridas' => ['label' => 'Heridas', 'permission' => 'heridas.ver', 'route' => 'admin.enfermeria.registros', 'parameters' => ['seccion' => 'HERIDAS']],
        ];
    }

    public static function opcion(string $clave): ?array
    {
        return self::opciones()[$clave] ?? null;
    }
}
