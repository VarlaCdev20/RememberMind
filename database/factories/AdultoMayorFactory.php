<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AdultoMayorFactory extends Factory
{
    public function definition(): array
    {
        $personas = [
            ['nombre' => 'Juan', 'genero' => 'MASCULINO'],
            ['nombre' => 'José', 'genero' => 'MASCULINO'],
            ['nombre' => 'Luis', 'genero' => 'MASCULINO'],
            ['nombre' => 'Pedro', 'genero' => 'MASCULINO'],
            ['nombre' => 'Carlos', 'genero' => 'MASCULINO'],
            ['nombre' => 'Mario', 'genero' => 'MASCULINO'],

            ['nombre' => 'María', 'genero' => 'FEMENINO'],
            ['nombre' => 'Rosa', 'genero' => 'FEMENINO'],
            ['nombre' => 'Carmen', 'genero' => 'FEMENINO'],
            ['nombre' => 'Ana', 'genero' => 'FEMENINO'],
            ['nombre' => 'Elena', 'genero' => 'FEMENINO'],
            ['nombre' => 'Juana', 'genero' => 'FEMENINO'],
        ];

        $persona = $this->faker->randomElement($personas);

        $apellidos = [
            'Quispe',
            'Mamani',
            'Choque',
            'Condori',
            'Flores',
            'Gutiérrez',
            'Vargas',
            'Rojas',
            'Apaza',
            'Laura',
            'Calle',
            'Paredes',
            'Alarcón',
            'Mendoza',
        ];

        $zonas = [
            'Miraflores',
            'Sopocachi',
            'Villa Fátima',
            'Achumani',
            'San Pedro',
            'Obrajes',
            'Villa Copacabana',
            'El Tejar',
            'Villa Armonía',
            'Centro',
        ];

        $calles = [
            'Av. Camacho',
            'Av. Busch',
            'Av. Arce',
            'Av. 6 de Agosto',
            'Calle Comercio',
            'Calle Jaén',
            'Av. Saavedra',
            'Calle Sagárnaga',
        ];

        return [
            'nombres' => $persona['nombre'],
            'ap_paterno' => $this->faker->randomElement($apellidos),
            'ap_materno' => $this->faker->randomElement($apellidos),
            'ci' => (string) $this->faker->unique()->numberBetween(1000000, 9999999),
            'fecha_nac' => $this->faker->dateTimeBetween('-90 years', '-60 years')->format('Y-m-d'),
            'genero' => $persona['genero'],
            'estado_civil' => $this->faker->randomElement([
                'SOLTERO/A',
                'CASADO/A',
                'VIUDO/A',
                'DIVORCIADO/A',
            ]),
            'telefono' => '7' . $this->faker->numberBetween(1000000, 9999999),
            'zona' => $this->faker->randomElement($zonas),
            'calle' => $this->faker->randomElement($calles),
            'fecha_ing' => now()->format('Y-m-d'),
            'tipo_ing' => $this->faker->randomElement([
                'REGULAR',
                'DERIVADO',
                'VOLUNTARIO',
                'EMERGENCIA',
            ]),
            'permanencia' => $this->faker->randomElement([
                'PERMANENTE',
                'TEMPORAL',
                'EVENTUAL',
            ]),
            'nivel_educat' => $this->faker->randomElement([
                'SIN EDUCACIÓN FORMAL',
                'PRIMARIA',
                'SECUNDARIA',
                'TÉCNICO',
                'SUPERIOR',
                'NO ESPECIFICADO',
            ]),
            'observaciones' => $this->faker->randomElement([
                'Paciente estable en seguimiento.',
                'Participa regularmente en actividades institucionales.',
                'Requiere seguimiento periódico.',
                'Presenta buena integración social.',
                'Registro inicial sin observaciones críticas.',
                'Se recomienda control institucional continuo.',
            ]),
            'cod_est_adul' => 1,
        ];
    }
}