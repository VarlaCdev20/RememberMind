<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdultoMayorSeeder extends Seeder
{
    public function run(): void
    {
        $estadoId = DB::table('estado_adulto')
            ->whereRaw('UPPER(estado) = ?', ['ACTIVO'])
            ->value('cod_est_adul');

        if (!$estadoId) {
            $estadoId = DB::table('estado_adulto')->insertGetId([
                'estado' => 'ACTIVO',
                'fecha_in' => now(),
                'fecha_fin' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'cod_est_adul');
        }

        DB::table('adulto_mayor')->updateOrInsert(
            ['cod_am' => 'AM_001'],
            [
                'nombres' => 'MARÍA',
                'ap_paterno' => 'QUISPE',
                'ap_materno' => 'LOPEZ',
                'ci' => '1234567',
                'complemento_ci' => null,
                'expedicion_ci' => 'LP',
                'fecha_nac' => '1945-05-12',
                'genero' => 'FEMENINO',
                'estado_civil' => 'VIUDO/A',
                'telefono' => '77777777',
                'tiene_celular' => true,
                'celular' => '77777777',
                'sabe_usar_whatsapp' => false,
                'telefono_fijo' => null,
                'departamento_residencia' => 'LA PAZ',
                'ciudad_municipio' => 'LA PAZ',
                'zona' => 'MIRAFLORES',
                'calle' => 'AV. BUSCH',
                'fecha_ing' => now()->toDateString(),
                'hora_ing' => now()->format('H:i'),
                'tipo_ing' => 'REGULAR',
                'permanencia' => 'PERMANENTE',
                'nivel_educat' => 'PRIMARIA',
                'grupo_sanguineo' => 'O+',
                'factor_rh' => '+',
                'alergias' => 'NINGUNA',
                'seguro_salud' => 'SUS',
                'contacto_emergencia_nombre' => 'JUAN QUISPE',
                'contacto_emergencia_parentesco' => 'HIJO/A',
                'contacto_emergencia_celular' => '71111111',
                'contacto_emergencia_direccion' => 'MIRAFLORES',
                'responsable_principal' => true,
                'autorizado_informacion_medica' => true,
                'consentimiento_datos' => true,
                'observaciones' => 'PACIENTE ESTABLE REGISTRADA COMO DATO DE PRUEBA.',
                'cod_est_adul' => $estadoId,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
