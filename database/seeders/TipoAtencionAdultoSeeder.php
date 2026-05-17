<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoAtencionAdulto;

class TipoAtencionAdultoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['tipo' => 'Médica General', 'descripcion' => 'Consulta de control médico rutinario.'],
            ['tipo' => 'Enfermería', 'descripcion' => 'Curaciones, administración de medicamentos, signos vitales.'],
            ['tipo' => 'Psicología', 'descripcion' => 'Apoyo emocional y evaluación psicológica.'],
            ['tipo' => 'Fisioterapia', 'descripcion' => 'Sesiones de rehabilitación física.'],
            ['tipo' => 'Nutrición', 'descripcion' => 'Control de dieta y estado nutricional.'],
            ['tipo' => 'Odontología', 'descripcion' => 'Revisión y tratamiento dental.'],
        ];

        foreach ($tipos as $tipo) {
            TipoAtencionAdulto::updateOrCreate(['tipo' => $tipo['tipo']], $tipo);
        }
    }
}
