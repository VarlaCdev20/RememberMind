<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AreaInstitucional;

class AreasInstitucionalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            [
                'cod_area' => 'ARE_0001',
                'nombre' => 'Dirección General',
                'slug' => 'direccion-general',
                'tipo_area' => 'Administrativa',
                'descripcion' => 'Supervisión institucional, consulta de reportes generales, revisión de indicadores, control de áreas y toma de decisiones.',
                'roles_sugeridos' => ['admin', 'personal_admin'],
                'modulos_relacionados' => ['reportes', 'bitacora', 'areas', 'usuarios'],
                'color' => '#2F3E5C', // Azul profundo
                'icono' => 'ph-crown',
                'orden' => 1,
            ],
            [
                'cod_area' => 'ARE_0002',
                'nombre' => 'Coordinación de Programas y Servicios',
                'slug' => 'coordinacion-programas-servicios',
                'tipo_area' => 'Administrativa',
                'descripcion' => 'Coordinar programas, servicios, actividades, atenciones y articulación entre áreas.',
                'roles_sugeridos' => ['personal_admin', 'personal_salud'],
                'modulos_relacionados' => ['adultos_mayores', 'actividades', 'atenciones', 'voluntariado', 'reportes'],
                'color' => '#E27D60', // Terracota
                'icono' => 'ph-flow-arrow',
                'orden' => 2,
            ],
            [
                'cod_area' => 'ARE_0003',
                'nombre' => 'Área Administrativa y Registro Institucional',
                'slug' => 'area-administrativa-registro',
                'tipo_area' => 'Administrativa',
                'descripcion' => 'Gestionar registros institucionales, adultos mayores, documentos, familiares y actualización de datos.',
                'roles_sugeridos' => ['personal_admin', 'admin'],
                'modulos_relacionados' => ['adultos_mayores', 'familiares', 'documentos', 'usuarios', 'reportes'],
                'color' => '#9B8B7E', // Gris azulado
                'icono' => 'ph-files',
                'orden' => 3,
            ],
            [
                'cod_area' => 'ARE_0004',
                'nombre' => 'Área de Atención Médica',
                'slug' => 'area-atencion-medica',
                'tipo_area' => 'Salud',
                'descripcion' => 'Registrar atenciones médicas, signos vitales, ficha médica, medicación y seguimiento clínico general.',
                'roles_sugeridos' => ['personal_salud'],
                'modulos_relacionados' => ['ficha_medica', 'signos_vitales', 'medicacion', 'atenciones', 'reportes'],
                'color' => '#63775B', // Verde médico
                'icono' => 'ph-stethoscope',
                'orden' => 4,
            ],
            [
                'cod_area' => 'ARE_0005',
                'nombre' => 'Área de Psicología y Seguimiento Cognitivo',
                'slug' => 'area-psicologia-cognitivo',
                'tipo_area' => 'Salud',
                'descripcion' => 'Registrar evaluaciones cognitivas, observaciones cognitivas/conductuales, historial cognitivo, alertas tempranas y reportes de evolución.',
                'roles_sugeridos' => ['personal_salud'],
                'modulos_relacionados' => ['evaluaciones', 'observaciones', 'historial', 'alertas', 'reportes'],
                'color' => '#7C83B8', // Lila/azul suave
                'icono' => 'ph-brain',
                'orden' => 5,
            ],
            [
                'cod_area' => 'ARE_0006',
                'nombre' => 'Área de Trabajo Social y Asesoramiento Legal',
                'slug' => 'area-trabajo-social-legal',
                'tipo_area' => 'Social',
                'descripcion' => 'Gestionar información social, contacto con familiares, documentación social/legal y seguimiento de casos vulnerables.',
                'roles_sugeridos' => ['personal_admin', 'personal_salud'],
                'modulos_relacionados' => ['familiares', 'documentos', 'adultos_mayores', 'observaciones', 'reportes'],
                'color' => '#967B66', // Marrón cálido
                'icono' => 'ph-hand-heart',
                'orden' => 6,
            ],
            [
                'cod_area' => 'ARE_0007',
                'nombre' => 'Área de Actividades Recreativas y Educación',
                'slug' => 'area-actividades-recreativas-educacion',
                'tipo_area' => 'Social',
                'descripcion' => 'Registrar actividades recreativas, cognitivas, físicas, educativas y controlar participación de adultos mayores.',
                'roles_sugeridos' => ['personal_admin', 'voluntario', 'personal_salud'],
                'modulos_relacionados' => ['actividades', 'asistencia', 'voluntariado', 'adultos_mayores', 'reportes'],
                'color' => '#D5C7B9', // Coral/amarillo suave
                'icono' => 'ph-puzzle-piece',
                'orden' => 7,
            ],
            [
                'cod_area' => 'ARE_0008',
                'nombre' => 'Voluntariado y Relaciones Institucionales',
                'slug' => 'voluntariado-relaciones-institucionales',
                'tipo_area' => 'Social',
                'descripcion' => 'Gestionar voluntarios, disponibilidad, apoyo institucional, participación y relaciones externas.',
                'roles_sugeridos' => ['personal_admin', 'voluntario'],
                'modulos_relacionados' => ['voluntarios', 'disponibilidad', 'asignaciones', 'asistencia', 'actividades', 'reportes'],
                'color' => '#8DA280', // Verde esperanza
                'icono' => 'ph-hands-helping',
                'orden' => 8,
            ],
            [
                'cod_area' => 'ARE_0009',
                'nombre' => 'Administración del Sistema',
                'slug' => 'administracion-sistema',
                'tipo_area' => 'Soporte',
                'descripcion' => 'Gestionar usuarios, roles, permisos, bitácora, seguridad y configuración del sistema.',
                'roles_sugeridos' => ['admin'],
                'modulos_relacionados' => ['usuarios', 'roles_permisos', 'bitacora', 'configuracion', 'areas'],
                'color' => '#5E6599', // Azul grisáceo
                'icono' => 'ph-shield-check',
                'orden' => 9,
            ],
        ];

        foreach ($areas as $area) {
            AreaInstitucional::updateOrCreate(
                ['cod_area' => $area['cod_area']],
                [
                    'nombre' => $area['nombre'],
                    'slug' => $area['slug'],
                    'tipo_area' => $area['tipo_area'],
                    'descripcion' => $area['descripcion'],
                    'roles_sugeridos' => $area['roles_sugeridos'],
                    'modulos_relacionados' => $area['modulos_relacionados'],
                    'color' => $area['color'],
                    'icono' => $area['icono'],
                    'orden' => $area['orden'],
                    'estado' => 'ACTIVA',
                ]
            );
        }
    }
}
