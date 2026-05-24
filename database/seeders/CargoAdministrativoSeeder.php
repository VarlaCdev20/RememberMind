<?php

namespace Database\Seeders;

use App\Models\CargoAdministrativo;
use Illuminate\Database\Seeder;

class CargoAdministrativoSeeder extends Seeder
{
    public function run(): void
    {
        $cargos = [
            ['nombre' => 'DIRECCIÓN GENERAL', 'alias' => ['DIRECCION GENERAL'], 'descripcion' => 'Dirección institucional y toma de decisiones.'],
            ['nombre' => 'COORDINACIÓN DE PROGRAMAS Y SERVICIOS', 'alias' => ['COORDINACION DE PROGRAMAS Y SERVICIOS'], 'descripcion' => 'Coordinación operativa de programas y servicios.'],
            ['nombre' => 'ADMINISTRACION', 'descripcion' => 'Gestion administrativa y financiera.'],
            ['nombre' => 'TRABAJO SOCIAL', 'descripcion' => 'Seguimiento social y apoyo familiar.'],
            ['nombre' => 'ASESORAMIENTO LEGAL', 'descripcion' => 'Orientacion legal institucional.'],
            ['nombre' => 'COORDINACIÓN DE VOLUNTARIADO', 'alias' => ['COORDINACION DE VOLUNTARIADO'], 'descripcion' => 'Gestión y seguimiento de voluntariado.'],
            ['nombre' => 'RESPONSABLE DE ACTIVIDADES', 'descripcion' => 'Planificacion y control de actividades.'],
            ['nombre' => 'SECRETARÍA', 'alias' => ['SECRETARIA'], 'descripcion' => 'Apoyo administrativo, recepción y archivo.'],
            ['nombre' => 'APOYO ADMINISTRATIVO', 'descripcion' => 'Apoyo en tareas administrativas generales.'],
        ];

        foreach ($cargos as $cargo) {
            $nombresBusqueda = array_merge([$cargo['nombre']], $cargo['alias'] ?? []);
            $registro = CargoAdministrativo::whereIn('nombre', $nombresBusqueda)->first();

            CargoAdministrativo::updateOrCreate(
                $registro ? ['cod_cargo_admin' => $registro->cod_cargo_admin] : ['nombre' => $cargo['nombre']],
                [
                    'nombre' => $cargo['nombre'],
                    'descripcion' => $cargo['descripcion'],
                    'estado' => 'ACTIVO',
                ]
            );
        }
    }
}
