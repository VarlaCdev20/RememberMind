<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoAtencionAdulto;

class TipoAtencionAdultoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Médica General', 'descripcion' => 'Consulta de control médico rutinario.'],
            ['nombre' => 'Enfermería', 'descripcion' => 'Curaciones, administración de medicamentos, signos vitales.'],
            ['nombre' => 'Psicología', 'descripcion' => 'Apoyo emocional y evaluación psicológica.'],
            ['nombre' => 'Fisioterapia', 'descripcion' => 'Sesiones de rehabilitación física.'],
            ['nombre' => 'Nutrición', 'descripcion' => 'Control de dieta y estado nutricional.'],
            ['nombre' => 'Odontología', 'descripcion' => 'Revisión y tratamiento dental.'],
        ];

        foreach ($tipos as $tipo) {
            TipoAtencionAdulto::updateOrCreate(['nombre' => $tipo['nombre']], $tipo);
        }
    }
}
